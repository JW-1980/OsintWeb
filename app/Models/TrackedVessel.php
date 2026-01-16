<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * TrackedVessel Model
 *
 * Represents a vessel tracked via AIS (Automatic Identification System).
 *
 * @property int $id
 * @property string $uuid
 * @property string $mmsi Maritime Mobile Service Identity (9 digits)
 * @property string|null $imo_number IMO ship identification number
 * @property string $name Vessel name
 * @property string|null $callsign Radio callsign
 * @property string $vessel_type Primary vessel type (cargo, tanker, passenger, etc.)
 * @property string|null $vessel_subtype Detailed vessel subtype
 * @property int|null $flag_country_id Flag state country ID
 * @property float|null $length_meters Length overall in meters
 * @property float|null $width_meters Beam/width in meters
 * @property float|null $draft_meters Maximum draft in meters
 * @property int|null $gross_tonnage Gross tonnage (GT)
 * @property int|null $deadweight_tonnage Deadweight tonnage (DWT)
 * @property int|null $build_year Year of construction
 * @property string|null $owner Registered owner name
 * @property string|null $operator Commercial operator name
 * @property bool $is_military Military or government vessel
 * @property bool $is_monitored Active monitoring status
 * @property int $monitoring_priority Priority 1-5 (1=highest)
 * @property string|null $notes Analyst notes
 * @property array|null $metadata Additional metadata
 * @property int|null $added_by User who added this vessel
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 *
 * @property-read Country|null $flagCountry
 * @property-read User|null $addedByUser
 * @property-read \Illuminate\Database\Eloquent\Collection|VesselPosition[] $positions
 * @property-read \Illuminate\Database\Eloquent\Collection|VesselTrack[] $tracks
 * @property-read \Illuminate\Database\Eloquent\Collection|PortCall[] $portCalls
 * @property-read \Illuminate\Database\Eloquent\Collection|VesselAlert[] $alerts
 * @property-read VesselPosition|null $latestPosition
 */
class TrackedVessel extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Vessel type constants.
     */
    public const TYPE_CARGO = 'cargo';
    public const TYPE_TANKER = 'tanker';
    public const TYPE_PASSENGER = 'passenger';
    public const TYPE_FISHING = 'fishing';
    public const TYPE_MILITARY = 'military';
    public const TYPE_TUG = 'tug';
    public const TYPE_YACHT = 'yacht';
    public const TYPE_OTHER = 'other';

    /**
     * Monitoring priority constants.
     */
    public const PRIORITY_CRITICAL = 1;
    public const PRIORITY_HIGH = 2;
    public const PRIORITY_MEDIUM = 3;
    public const PRIORITY_LOW = 4;
    public const PRIORITY_MINIMAL = 5;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'uuid',
        'mmsi',
        'imo_number',
        'name',
        'callsign',
        'vessel_type',
        'vessel_subtype',
        'flag_country_id',
        'length_meters',
        'width_meters',
        'draft_meters',
        'gross_tonnage',
        'deadweight_tonnage',
        'build_year',
        'owner',
        'operator',
        'is_military',
        'is_monitored',
        'monitoring_priority',
        'notes',
        'metadata',
        'added_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'length_meters' => 'decimal:2',
        'width_meters' => 'decimal:2',
        'draft_meters' => 'decimal:2',
        'gross_tonnage' => 'integer',
        'deadweight_tonnage' => 'integer',
        'build_year' => 'integer',
        'is_military' => 'boolean',
        'is_monitored' => 'boolean',
        'monitoring_priority' => 'integer',
        'metadata' => 'array',
    ];

    /**
     * Get the flag country.
     */
    public function flagCountry(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'flag_country_id');
    }

    /**
     * Get the user who added this vessel.
     */
    public function addedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    /**
     * Get all positions for this vessel.
     */
    public function positions(): HasMany
    {
        return $this->hasMany(VesselPosition::class, 'vessel_id');
    }

    /**
     * Get all tracks for this vessel.
     */
    public function tracks(): HasMany
    {
        return $this->hasMany(VesselTrack::class, 'vessel_id');
    }

    /**
     * Get all port calls for this vessel.
     */
    public function portCalls(): HasMany
    {
        return $this->hasMany(PortCall::class, 'vessel_id');
    }

    /**
     * Get all alerts specifically for this vessel.
     */
    public function alerts(): HasMany
    {
        return $this->hasMany(VesselAlert::class, 'vessel_id');
    }

    /**
     * Get the latest position for this vessel.
     */
    public function latestPosition(): BelongsTo
    {
        return $this->belongsTo(VesselPosition::class, 'id', 'vessel_id')
            ->latestOfMany('recorded_at');
    }

    /**
     * Get positions within a date range.
     *
     * @param \Carbon\Carbon $start
     * @param \Carbon\Carbon $end
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPositionsInRange(\Carbon\Carbon $start, \Carbon\Carbon $end)
    {
        return $this->positions()
            ->whereBetween('recorded_at', [$start, $end])
            ->orderBy('recorded_at', 'asc')
            ->get();
    }

    /**
     * Scope to filter monitored vessels.
     */
    public function scopeMonitored($query)
    {
        return $query->where('is_monitored', true);
    }

    /**
     * Scope to filter military vessels.
     */
    public function scopeMilitary($query)
    {
        return $query->where('is_military', true);
    }

    /**
     * Scope to filter by vessel type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('vessel_type', $type);
    }

    /**
     * Scope to filter by flag country.
     */
    public function scopeWithFlag($query, int $countryId)
    {
        return $query->where('flag_country_id', $countryId);
    }

    /**
     * Scope to filter by monitoring priority.
     */
    public function scopePriority($query, int $priority)
    {
        return $query->where('monitoring_priority', '<=', $priority);
    }

    /**
     * Scope to search by name, MMSI, IMO, or callsign.
     */
    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('mmsi', 'like', "%{$term}%")
                ->orWhere('imo_number', 'like', "%{$term}%")
                ->orWhere('callsign', 'like', "%{$term}%");
        });
    }

    /**
     * Scope to order by priority (critical first).
     */
    public function scopeOrderByPriority($query)
    {
        return $query->orderBy('monitoring_priority', 'asc');
    }

    /**
     * Get all vessel types.
     *
     * @return array<string, string>
     */
    public static function getVesselTypes(): array
    {
        return [
            self::TYPE_CARGO => 'Cargo',
            self::TYPE_TANKER => 'Tanker',
            self::TYPE_PASSENGER => 'Passenger',
            self::TYPE_FISHING => 'Fishing',
            self::TYPE_MILITARY => 'Military',
            self::TYPE_TUG => 'Tug',
            self::TYPE_YACHT => 'Yacht',
            self::TYPE_OTHER => 'Other',
        ];
    }

    /**
     * Get all priority levels.
     *
     * @return array<int, string>
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
     * Get display name for vessel type.
     */
    public function getVesselTypeDisplayAttribute(): string
    {
        return self::getVesselTypes()[$this->vessel_type] ?? 'Unknown';
    }

    /**
     * Get display name for priority.
     */
    public function getPriorityDisplayAttribute(): string
    {
        return self::getPriorityLevels()[$this->monitoring_priority] ?? 'Unknown';
    }

    /**
     * Check if vessel has recent position data (within hours).
     */
    public function hasRecentPosition(int $hours = 24): bool
    {
        return $this->positions()
            ->where('recorded_at', '>=', now()->subHours($hours))
            ->exists();
    }

    /**
     * Calculate hours since last position update.
     */
    public function hoursSinceLastPosition(): ?float
    {
        $lastPosition = $this->positions()
            ->orderBy('recorded_at', 'desc')
            ->first();

        if (!$lastPosition) {
            return null;
        }

        return now()->diffInMinutes($lastPosition->recorded_at) / 60;
    }
}
