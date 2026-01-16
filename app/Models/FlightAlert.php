<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Flight Alert Model
 *
 * Represents a user-defined alert for flight tracking events.
 *
 * @property int $id
 * @property string $uuid
 * @property int $user_id
 * @property string $name Alert name
 * @property string|null $description
 * @property string $alert_type Type of alert
 * @property int|null $aircraft_id Specific aircraft to monitor
 * @property \Illuminate\Database\Query\Expression|null $zone_polygon Geographic zone (POLYGON)
 * @property int|null $altitude_min Minimum altitude threshold (feet)
 * @property int|null $altitude_max Maximum altitude threshold (feet)
 * @property array|null $squawk_codes Squawk codes to alert on
 * @property array|null $aircraft_types Aircraft types to include
 * @property bool $military_only Only alert on military aircraft
 * @property bool $is_active Whether alert is active
 * @property string $notification_channels Notification delivery method
 * @property int $cooldown_minutes Minimum time between alerts
 * @property \Carbon\Carbon|null $last_triggered_at
 * @property int $trigger_count
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 *
 * @property-read User $user
 * @property-read TrackedAircraft|null $aircraft
 * @property-read \Illuminate\Database\Eloquent\Collection<FlightAlertTrigger> $triggers
 */
class FlightAlert extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'flight_alerts';

    /**
     * Alert type constants
     */
    public const TYPE_AIRCRAFT_SPOTTED = 'aircraft_spotted';
    public const TYPE_ENTERED_ZONE = 'entered_zone';
    public const TYPE_EXITED_ZONE = 'exited_zone';
    public const TYPE_UNUSUAL_ACTIVITY = 'unusual_activity';
    public const TYPE_SQUAWK_CHANGE = 'squawk_change';
    public const TYPE_ALTITUDE_THRESHOLD = 'altitude_threshold';
    public const TYPE_MILITARY_ACTIVITY = 'military_activity';
    public const TYPE_NEW_AIRCRAFT = 'new_aircraft';

    /**
     * Notification channel constants
     */
    public const CHANNEL_DATABASE = 'database';
    public const CHANNEL_EMAIL = 'email';
    public const CHANNEL_BOTH = 'both';

    protected $fillable = [
        'uuid',
        'user_id',
        'name',
        'description',
        'alert_type',
        'aircraft_id',
        'zone_polygon',
        'altitude_min',
        'altitude_max',
        'squawk_codes',
        'aircraft_types',
        'military_only',
        'is_active',
        'notification_channels',
        'cooldown_minutes',
        'last_triggered_at',
        'trigger_count',
    ];

    protected $casts = [
        'squawk_codes' => 'array',
        'aircraft_types' => 'array',
        'military_only' => 'boolean',
        'is_active' => 'boolean',
        'cooldown_minutes' => 'integer',
        'altitude_min' => 'integer',
        'altitude_max' => 'integer',
        'last_triggered_at' => 'datetime',
        'trigger_count' => 'integer',
    ];

    /**
     * Boot the model
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $alert) {
            if (empty($alert->uuid)) {
                $alert->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the user who created this alert
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the specific aircraft being monitored (if any)
     */
    public function aircraft(): BelongsTo
    {
        return $this->belongsTo(TrackedAircraft::class, 'aircraft_id');
    }

    /**
     * Get trigger history for this alert
     */
    public function triggers(): HasMany
    {
        return $this->hasMany(FlightAlertTrigger::class, 'alert_id')
            ->orderBy('triggered_at', 'desc');
    }

    /**
     * Create alert with zone polygon
     */
    public static function createWithZone(
        int $userId,
        string $name,
        string $alertType,
        array $coordinates,
        array $data = []
    ): self {
        // Build polygon WKT from coordinates
        $points = array_map(fn($coord) => "{$coord['lng']} {$coord['lat']}", $coordinates);
        // Close the polygon
        $points[] = $points[0];
        $polygonWkt = 'POLYGON((' . implode(', ', $points) . '))';

        return self::create(array_merge($data, [
            'user_id' => $userId,
            'name' => $name,
            'alert_type' => $alertType,
            'zone_polygon' => DB::raw("ST_GeomFromText('{$polygonWkt}', 4326)"),
        ]));
    }

    /**
     * Get zone polygon as array of coordinates
     */
    public function getZoneCoordinates(): array
    {
        if (!$this->zone_polygon) {
            return [];
        }

        $result = DB::selectOne(
            "SELECT ST_AsText(zone_polygon) as polygon FROM flight_alerts WHERE id = ?",
            [$this->id]
        );

        if (!$result || !$result->polygon) {
            return [];
        }

        // Parse POLYGON
        preg_match('/POLYGON\(\((.+)\)\)/', $result->polygon, $matches);
        if (!isset($matches[1])) {
            return [];
        }

        $points = explode(', ', $matches[1]);
        $coordinates = [];

        foreach ($points as $point) {
            [$lng, $lat] = explode(' ', trim($point));
            $coordinates[] = [
                'lat' => (float) $lat,
                'lng' => (float) $lng,
            ];
        }

        // Remove the closing point (duplicate of first)
        if (count($coordinates) > 1) {
            array_pop($coordinates);
        }

        return $coordinates;
    }

    /**
     * Check if a position triggers this alert
     */
    public function matchesPosition(FlightPosition $position): bool
    {
        // Check aircraft-specific alerts
        if ($this->aircraft_id && $position->aircraft_id !== $this->aircraft_id) {
            return false;
        }

        // Check military only
        if ($this->military_only && !$position->aircraft->is_military) {
            return false;
        }

        // Check aircraft types
        if (!empty($this->aircraft_types)) {
            $aircraftType = $position->aircraft->aircraft_type;
            if (!in_array($aircraftType, $this->aircraft_types)) {
                return false;
            }
        }

        // Check altitude thresholds
        if ($this->altitude_min !== null && $position->altitude_feet < $this->altitude_min) {
            return false;
        }
        if ($this->altitude_max !== null && $position->altitude_feet > $this->altitude_max) {
            return false;
        }

        // Check squawk codes
        if (!empty($this->squawk_codes)) {
            if (!in_array($position->squawk, $this->squawk_codes)) {
                return false;
            }
        }

        // Check zone (spatial query)
        if ($this->zone_polygon) {
            $inZone = DB::selectOne(
                "SELECT ST_Contains(
                    (SELECT zone_polygon FROM flight_alerts WHERE id = ?),
                    ST_GeomFromText('POINT(? ?)', 4326)
                ) as inside",
                [$this->id, $position->longitude, $position->latitude]
            );

            if (!$inZone || !$inZone->inside) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if cooldown period has passed
     */
    public function isCooldownExpired(): bool
    {
        if (!$this->last_triggered_at) {
            return true;
        }

        return $this->last_triggered_at->addMinutes($this->cooldown_minutes)->isPast();
    }

    /**
     * Record a trigger
     */
    public function recordTrigger(
        ?TrackedAircraft $aircraft = null,
        ?FlightPosition $position = null,
        ?FlightTrack $track = null,
        array $data = []
    ): FlightAlertTrigger {
        $trigger = FlightAlertTrigger::create([
            'alert_id' => $this->id,
            'aircraft_id' => $aircraft?->id,
            'position_id' => $position?->id,
            'track_id' => $track?->id,
            'trigger_data' => $data,
            'triggered_at' => now(),
        ]);

        $this->update([
            'last_triggered_at' => now(),
            'trigger_count' => $this->trigger_count + 1,
        ]);

        return $trigger;
    }

    /**
     * Toggle alert active status
     */
    public function toggle(): void
    {
        $this->update(['is_active' => !$this->is_active]);
    }

    /**
     * Activate alert
     */
    public function activate(): void
    {
        $this->update(['is_active' => true]);
    }

    /**
     * Deactivate alert
     */
    public function deactivate(): void
    {
        $this->update(['is_active' => false]);
    }

    /**
     * Get unread trigger count
     */
    public function getUnreadTriggerCount(): int
    {
        return $this->triggers()->where('is_read', false)->count();
    }

    /**
     * Mark all triggers as read
     */
    public function markAllTriggersRead(): void
    {
        $this->triggers()->update(['is_read' => true]);
    }

    /**
     * Scope to filter active alerts
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by alert type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('alert_type', $type);
    }

    /**
     * Scope to filter by user
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to filter alerts with zone
     */
    public function scopeWithZone($query)
    {
        return $query->whereNotNull('zone_polygon');
    }

    /**
     * Scope to filter aircraft-specific alerts
     */
    public function scopeForAircraft($query, int $aircraftId)
    {
        return $query->where('aircraft_id', $aircraftId);
    }

    /**
     * Scope to filter alerts that are ready to trigger (cooldown expired)
     */
    public function scopeReadyToTrigger($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('last_triggered_at')
                    ->orWhereRaw('DATE_ADD(last_triggered_at, INTERVAL cooldown_minutes MINUTE) <= NOW()');
            });
    }

    /**
     * Scope to filter military-only alerts
     */
    public function scopeMilitaryOnly($query)
    {
        return $query->where('military_only', true);
    }

    /**
     * Get all available alert types
     */
    public static function getAlertTypes(): array
    {
        return [
            self::TYPE_AIRCRAFT_SPOTTED => 'Aircraft Spotted',
            self::TYPE_ENTERED_ZONE => 'Entered Zone',
            self::TYPE_EXITED_ZONE => 'Exited Zone',
            self::TYPE_UNUSUAL_ACTIVITY => 'Unusual Activity',
            self::TYPE_SQUAWK_CHANGE => 'Squawk Code Change',
            self::TYPE_ALTITUDE_THRESHOLD => 'Altitude Threshold',
            self::TYPE_MILITARY_ACTIVITY => 'Military Activity',
            self::TYPE_NEW_AIRCRAFT => 'New Aircraft',
        ];
    }

    /**
     * Get all notification channels
     */
    public static function getNotificationChannels(): array
    {
        return [
            self::CHANNEL_DATABASE => 'In-App Only',
            self::CHANNEL_EMAIL => 'Email Only',
            self::CHANNEL_BOTH => 'Both',
        ];
    }

    /**
     * Get alert type label
     */
    public function getAlertTypeLabel(): string
    {
        return self::getAlertTypes()[$this->alert_type] ?? 'Unknown';
    }

    /**
     * Get notification channel label
     */
    public function getNotificationChannelLabel(): string
    {
        return self::getNotificationChannels()[$this->notification_channels] ?? 'Unknown';
    }
}
