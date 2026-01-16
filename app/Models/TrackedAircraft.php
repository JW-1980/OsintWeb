<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * Tracked Aircraft Model
 *
 * Represents an aircraft being tracked via ADS-B data for OSINT purposes.
 *
 * @property int $id
 * @property string $uuid
 * @property string $icao_hex ICAO 24-bit aircraft address in hexadecimal
 * @property string|null $registration Aircraft tail number/registration
 * @property string|null $aircraft_type ICAO aircraft type designator (e.g., B738, A320)
 * @property string|null $aircraft_model Full aircraft model name
 * @property string|null $operator Aircraft operator/airline
 * @property string|null $owner Registered owner
 * @property int|null $country_id Country of registration
 * @property string|null $military_designation Military designation if applicable
 * @property bool $is_military Whether this is a military aircraft
 * @property bool $is_monitored Whether to actively track this aircraft
 * @property int $monitoring_priority Priority 1-5, where 1 is highest
 * @property string|null $notes
 * @property array|null $metadata Additional aircraft data
 * @property int|null $added_by User who added this aircraft
 * @property \Carbon\Carbon|null $first_seen_at First time we received data
 * @property \Carbon\Carbon|null $last_seen_at Most recent position data
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 *
 * @property-read Country|null $country
 * @property-read User|null $addedByUser
 * @property-read \Illuminate\Database\Eloquent\Collection<FlightPosition> $positions
 * @property-read \Illuminate\Database\Eloquent\Collection<FlightTrack> $tracks
 * @property-read FlightPosition|null $latestPosition
 */
class TrackedAircraft extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tracked_aircraft';

    /**
     * Priority levels
     */
    public const PRIORITY_CRITICAL = 1;
    public const PRIORITY_HIGH = 2;
    public const PRIORITY_MEDIUM = 3;
    public const PRIORITY_LOW = 4;
    public const PRIORITY_MINIMAL = 5;

    protected $fillable = [
        'uuid',
        'icao_hex',
        'registration',
        'aircraft_type',
        'aircraft_model',
        'operator',
        'owner',
        'country_id',
        'military_designation',
        'is_military',
        'is_monitored',
        'monitoring_priority',
        'notes',
        'metadata',
        'added_by',
        'first_seen_at',
        'last_seen_at',
    ];

    protected $casts = [
        'is_military' => 'boolean',
        'is_monitored' => 'boolean',
        'monitoring_priority' => 'integer',
        'metadata' => 'array',
        'first_seen_at' => 'datetime',
        'last_seen_at' => 'datetime',
    ];

    /**
     * Boot the model
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $aircraft) {
            if (empty($aircraft->uuid)) {
                $aircraft->uuid = (string) Str::uuid();
            }
            // Normalize ICAO hex to uppercase
            $aircraft->icao_hex = strtoupper($aircraft->icao_hex);
        });

        static::updating(function (self $aircraft) {
            if ($aircraft->isDirty('icao_hex')) {
                $aircraft->icao_hex = strtoupper($aircraft->icao_hex);
            }
        });
    }

    /**
     * Get the country of registration
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Get the user who added this aircraft
     */
    public function addedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    /**
     * Get all position reports for this aircraft
     */
    public function positions(): HasMany
    {
        return $this->hasMany(FlightPosition::class, 'aircraft_id')
            ->orderBy('recorded_at', 'desc');
    }

    /**
     * Get all flight tracks for this aircraft
     */
    public function tracks(): HasMany
    {
        return $this->hasMany(FlightTrack::class, 'aircraft_id')
            ->orderBy('departure_time', 'desc');
    }

    /**
     * Get the most recent position
     */
    public function latestPosition(): HasMany
    {
        return $this->hasMany(FlightPosition::class, 'aircraft_id')
            ->orderBy('recorded_at', 'desc')
            ->limit(1);
    }

    /**
     * Get positions within a time range
     */
    public function positionsInRange(\Carbon\Carbon $start, \Carbon\Carbon $end): HasMany
    {
        return $this->positions()
            ->where('recorded_at', '>=', $start)
            ->where('recorded_at', '<=', $end);
    }

    /**
     * Update last seen timestamp
     */
    public function updateLastSeen(?\Carbon\Carbon $timestamp = null): void
    {
        $timestamp = $timestamp ?? now();

        $this->update([
            'last_seen_at' => $timestamp,
            'first_seen_at' => $this->first_seen_at ?? $timestamp,
        ]);
    }

    /**
     * Get display name for the aircraft
     */
    public function getDisplayName(): string
    {
        if ($this->registration) {
            return $this->registration;
        }

        if ($this->military_designation) {
            return $this->military_designation;
        }

        return strtoupper($this->icao_hex);
    }

    /**
     * Get full aircraft description
     */
    public function getFullDescription(): string
    {
        $parts = [];

        if ($this->registration) {
            $parts[] = $this->registration;
        }

        if ($this->aircraft_model) {
            $parts[] = $this->aircraft_model;
        } elseif ($this->aircraft_type) {
            $parts[] = $this->aircraft_type;
        }

        if ($this->operator) {
            $parts[] = "({$this->operator})";
        }

        return implode(' ', $parts) ?: strtoupper($this->icao_hex);
    }

    /**
     * Check if aircraft is currently active (seen recently)
     */
    public function isActive(int $minutesThreshold = 15): bool
    {
        if (!$this->last_seen_at) {
            return false;
        }

        return $this->last_seen_at->isAfter(now()->subMinutes($minutesThreshold));
    }

    /**
     * Check if aircraft is airborne
     */
    public function isAirborne(): bool
    {
        $latestPosition = $this->latestPosition()->first();

        return $latestPosition && !$latestPosition->on_ground;
    }

    /**
     * Get current altitude (from latest position)
     */
    public function getCurrentAltitude(): ?int
    {
        $latestPosition = $this->latestPosition()->first();

        return $latestPosition?->altitude_feet;
    }

    /**
     * Get current speed (from latest position)
     */
    public function getCurrentSpeed(): ?int
    {
        $latestPosition = $this->latestPosition()->first();

        return $latestPosition?->ground_speed_knots;
    }

    /**
     * Get the ICAO country prefix from ICAO hex
     */
    public function getIcaoCountryPrefix(): string
    {
        // ICAO addresses are allocated in blocks by country
        // First 3 hex characters generally indicate the country
        return substr($this->icao_hex, 0, 3);
    }

    /**
     * Scope to filter monitored aircraft
     */
    public function scopeMonitored($query)
    {
        return $query->where('is_monitored', true);
    }

    /**
     * Scope to filter military aircraft
     */
    public function scopeMilitary($query)
    {
        return $query->where('is_military', true);
    }

    /**
     * Scope to filter civilian aircraft
     */
    public function scopeCivilian($query)
    {
        return $query->where('is_military', false);
    }

    /**
     * Scope to filter by priority
     */
    public function scopePriority($query, int $priority)
    {
        return $query->where('monitoring_priority', '<=', $priority);
    }

    /**
     * Scope to filter by priority range
     */
    public function scopePriorityRange($query, int $min, int $max)
    {
        return $query->whereBetween('monitoring_priority', [$min, $max]);
    }

    /**
     * Scope to filter active aircraft (seen recently)
     */
    public function scopeActive($query, int $minutesThreshold = 15)
    {
        return $query->where('last_seen_at', '>=', now()->subMinutes($minutesThreshold));
    }

    /**
     * Scope to filter by country
     */
    public function scopeCountry($query, int $countryId)
    {
        return $query->where('country_id', $countryId);
    }

    /**
     * Scope to filter by aircraft type
     */
    public function scopeType($query, string $type)
    {
        return $query->where('aircraft_type', $type);
    }

    /**
     * Scope to filter by operator
     */
    public function scopeOperator($query, string $operator)
    {
        return $query->where('operator', 'like', "%{$operator}%");
    }

    /**
     * Scope to search by multiple fields
     */
    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('icao_hex', 'like', "%{$term}%")
                ->orWhere('registration', 'like', "%{$term}%")
                ->orWhere('aircraft_type', 'like', "%{$term}%")
                ->orWhere('aircraft_model', 'like', "%{$term}%")
                ->orWhere('operator', 'like', "%{$term}%")
                ->orWhere('military_designation', 'like', "%{$term}%");
        });
    }

    /**
     * Scope to order by priority then last seen
     */
    public function scopeOrderByPriority($query)
    {
        return $query->orderBy('monitoring_priority', 'asc')
            ->orderBy('last_seen_at', 'desc');
    }

    /**
     * Get all available priority levels
     */
    public static function getPriorityLevels(): array
    {
        return [
            self::PRIORITY_CRITICAL => 'Critical',
            self::PRIORITY_HIGH => 'High',
            self::PRIORITY_MEDIUM => 'Medium',
            self::PRIORITY_LOW => 'Low',
            self::PRIORITY_MINIMAL => 'Minimal',
        ];
    }

    /**
     * Get priority label
     */
    public function getPriorityLabel(): string
    {
        return self::getPriorityLevels()[$this->monitoring_priority] ?? 'Unknown';
    }
}
