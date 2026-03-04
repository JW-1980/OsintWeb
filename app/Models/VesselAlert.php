<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

/**
 * VesselAlert Model
 *
 * Represents an alert configuration for maritime monitoring.
 *
 * @property int $id
 * @property string $uuid
 * @property int $user_id
 * @property string $alert_type Type of alert trigger
 * @property string $name Alert name
 * @property string|null $description Alert description
 * @property int|null $vessel_id Specific vessel to monitor
 * @property \Illuminate\Database\Query\Expression|null $zone_polygon Geographic zone
 * @property array|null $conditions Additional conditions
 * @property array|null $notification_channels Channels to notify
 * @property bool $is_active Whether alert is active
 * @property \Carbon\Carbon|null $last_triggered_at Last trigger time
 * @property int $trigger_count Number of times triggered
 * @property int $cooldown_minutes Cooldown between triggers
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 *
 * @property-read User $user
 * @property-read TrackedVessel|null $vessel
 * @property-read \Illuminate\Database\Eloquent\Collection|VesselAlertTrigger[] $triggers
 */
class VesselAlert extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Alert type constants.
     */
    public const TYPE_VESSEL_SPOTTED = 'vessel_spotted';
    public const TYPE_ENTERED_ZONE = 'entered_zone';
    public const TYPE_EXITED_ZONE = 'exited_zone';
    public const TYPE_PORT_CALL = 'port_call';
    public const TYPE_DARK_ACTIVITY = 'dark_activity';
    public const TYPE_STS_TRANSFER = 'sts_transfer';
    public const TYPE_SPEED_ANOMALY = 'speed_anomaly';
    public const TYPE_ROUTE_DEVIATION = 'route_deviation';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'uuid',
        'user_id',
        'alert_type',
        'name',
        'description',
        'vessel_id',
        'zone_polygon',
        'conditions',
        'notification_channels',
        'is_active',
        'last_triggered_at',
        'trigger_count',
        'cooldown_minutes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'conditions' => 'array',
        'notification_channels' => 'array',
        'is_active' => 'boolean',
        'last_triggered_at' => 'datetime',
        'trigger_count' => 'integer',
        'cooldown_minutes' => 'integer',
    ];

    /**
     * Get the user who created this alert.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the specific vessel this alert monitors.
     */
    public function vessel(): BelongsTo
    {
        return $this->belongsTo(TrackedVessel::class, 'vessel_id');
    }

    /**
     * Get all trigger history for this alert.
     */
    public function triggers(): HasMany
    {
        return $this->hasMany(VesselAlertTrigger::class, 'alert_id');
    }

    /**
     * Get zone polygon as GeoJSON.
     *
     * @return array|null
     */
    public function getZoneAsGeoJson(): ?array
    {
        if (!$this->zone_polygon) {
            return null;
        }

        $result = DB::selectOne(
            "SELECT ST_AsGeoJSON(zone_polygon) as geojson FROM vessel_alerts WHERE id = ?",
            [$this->id]
        );

        if (!$result || !$result->geojson) {
            return null;
        }

        return json_decode($result->geojson, true);
    }

    /**
     * Set zone from GeoJSON polygon.
     *
     * @param array $geoJson
     */
    public function setZoneFromGeoJson(array $geoJson): void
    {
        $json = json_encode($geoJson, JSON_HEX_APOS);
        $this->zone_polygon = DB::raw(
            sprintf("ST_GeomFromGeoJSON('%s')", $json)
        );
    }

    /**
     * Set zone from WKT polygon.
     *
     * @param string $wkt
     */
    public function setZoneFromWkt(string $wkt): void
    {
        $this->zone_polygon = DB::raw(
            sprintf("ST_GeomFromText('%s', 4326)", $wkt)
        );
    }

    /**
     * Check if alert can be triggered (respects cooldown).
     */
    public function canTrigger(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if (!$this->last_triggered_at) {
            return true;
        }

        return $this->last_triggered_at->addMinutes($this->cooldown_minutes)->isPast();
    }

    /**
     * Record a trigger event.
     *
     * @param array $data Trigger data
     * @return VesselAlertTrigger
     */
    public function recordTrigger(array $data = []): VesselAlertTrigger
    {
        $trigger = new VesselAlertTrigger();
        $trigger->uuid = \Illuminate\Support\Str::uuid()->toString();
        $trigger->alert_id = $this->id;
        $trigger->vessel_id = $data['vessel_id'] ?? null;
        $trigger->position_id = $data['position_id'] ?? null;
        $trigger->trigger_data = $data['trigger_data'] ?? null;
        $trigger->message = $data['message'] ?? null;
        $trigger->triggered_at = now();
        $trigger->was_notified = false;

        if (isset($data['latitude']) && isset($data['longitude'])) {
            $trigger->trigger_position = DB::raw(
                sprintf(
                    "ST_GeomFromText('POINT(%f %f)', 4326)",
                    $data['longitude'],
                    $data['latitude']
                )
            );
        }

        $trigger->save();

        $this->update([
            'last_triggered_at' => now(),
            'trigger_count' => $this->trigger_count + 1,
        ]);

        return $trigger;
    }

    /**
     * Scope to filter active alerts.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by user.
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to filter by alert type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('alert_type', $type);
    }

    /**
     * Scope to filter zone-based alerts.
     */
    public function scopeZoneBased($query)
    {
        return $query->whereNotNull('zone_polygon');
    }

    /**
     * Scope to filter vessel-specific alerts.
     */
    public function scopeVesselSpecific($query)
    {
        return $query->whereNotNull('vessel_id');
    }

    /**
     * Scope to filter alerts that can currently trigger (cooldown passed).
     */
    public function scopeCanTrigger($query)
    {
        return $query->active()
            ->where(function ($q) {
                $q->whereNull('last_triggered_at')
                    ->orWhereRaw('last_triggered_at < DATE_SUB(NOW(), INTERVAL cooldown_minutes MINUTE)');
            });
    }

    /**
     * Get all alert types.
     *
     * @return array<string, string>
     */
    public static function getAlertTypes(): array
    {
        return [
            self::TYPE_VESSEL_SPOTTED => 'Vessel Spotted',
            self::TYPE_ENTERED_ZONE => 'Entered Zone',
            self::TYPE_EXITED_ZONE => 'Exited Zone',
            self::TYPE_PORT_CALL => 'Port Call',
            self::TYPE_DARK_ACTIVITY => 'Dark Activity (AIS Gap)',
            self::TYPE_STS_TRANSFER => 'Ship-to-Ship Transfer',
            self::TYPE_SPEED_ANOMALY => 'Speed Anomaly',
            self::TYPE_ROUTE_DEVIATION => 'Route Deviation',
        ];
    }

    /**
     * Get display name for alert type.
     */
    public function getAlertTypeDisplayAttribute(): string
    {
        return self::getAlertTypes()[$this->alert_type] ?? 'Unknown';
    }

    /**
     * Enable the alert.
     */
    public function enable(): void
    {
        $this->update(['is_active' => true]);
    }

    /**
     * Disable the alert.
     */
    public function disable(): void
    {
        $this->update(['is_active' => false]);
    }

    /**
     * Toggle alert active state.
     */
    public function toggle(): void
    {
        $this->update(['is_active' => !$this->is_active]);
    }
}
