<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

/**
 * VesselPosition Model
 *
 * Represents a single AIS position report for a tracked vessel.
 *
 * @property int $id
 * @property int $vessel_id
 * @property \Illuminate\Database\Query\Expression $position Geographic position (POINT)
 * @property float|null $speed_knots Speed over ground in knots
 * @property float|null $course Course over ground in degrees (0-360)
 * @property float|null $heading True heading in degrees (0-360)
 * @property string $nav_status Navigation status
 * @property string|null $destination Reported destination port
 * @property \Carbon\Carbon|null $eta Estimated time of arrival
 * @property float|null $draught_current Current draught in meters
 * @property \Carbon\Carbon $recorded_at Time when position was recorded
 * @property string $data_source Source of position data
 * @property array|null $raw_data Original API response
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property-read TrackedVessel $vessel
 */
class VesselPosition extends Model
{
    use HasFactory;

    /**
     * Navigation status constants (AIS values).
     */
    public const NAV_UNDERWAY_ENGINE = 'underway_using_engine';
    public const NAV_AT_ANCHOR = 'at_anchor';
    public const NAV_NOT_UNDER_COMMAND = 'not_under_command';
    public const NAV_RESTRICTED_MANOEUVRABILITY = 'restricted_manoeuvrability';
    public const NAV_CONSTRAINED_BY_DRAUGHT = 'constrained_by_draught';
    public const NAV_MOORED = 'moored';
    public const NAV_AGROUND = 'aground';
    public const NAV_ENGAGED_IN_FISHING = 'engaged_in_fishing';
    public const NAV_UNDERWAY_SAILING = 'underway_sailing';
    public const NAV_NOT_DEFINED = 'not_defined';

    /**
     * Data source constants.
     */
    public const SOURCE_MARINETRAFFIC = 'marinetraffic';
    public const SOURCE_VESSELFINDER = 'vesselfinder';
    public const SOURCE_AIS_RECEIVER = 'ais_receiver';
    public const SOURCE_MYSHIPTRACKING = 'myshiptracking';
    public const SOURCE_FLEETMON = 'fleetmon';
    public const SOURCE_MANUAL = 'manual';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'vessel_id',
        'position',
        'speed_knots',
        'course',
        'heading',
        'nav_status',
        'destination',
        'eta',
        'draught_current',
        'recorded_at',
        'data_source',
        'raw_data',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'speed_knots' => 'decimal:2',
        'course' => 'decimal:2',
        'heading' => 'decimal:2',
        'draught_current' => 'decimal:2',
        'eta' => 'datetime',
        'recorded_at' => 'datetime',
        'raw_data' => 'array',
    ];

    /**
     * Get the vessel this position belongs to.
     */
    public function vessel(): BelongsTo
    {
        return $this->belongsTo(TrackedVessel::class, 'vessel_id');
    }

    /**
     * Get position coordinates as array.
     *
     * @return array{lat: float, lng: float}|null
     */
    public function getCoordinatesAttribute(): ?array
    {
        $coordinates = DB::selectOne(
            "SELECT ST_X(position) as lng, ST_Y(position) as lat FROM vessel_positions WHERE id = ?",
            [$this->id]
        );

        if (!$coordinates) {
            return null;
        }

        return [
            'lat' => (float) $coordinates->lat,
            'lng' => (float) $coordinates->lng,
        ];
    }

    /**
     * Set position from latitude and longitude.
     *
     * @param float $latitude
     * @param float $longitude
     */
    public function setPositionFromCoordinates(float $latitude, float $longitude): void
    {
        $this->position = DB::raw(
            sprintf("ST_GeomFromText('POINT(%f %f)', 4326)", $longitude, $latitude)
        );
    }

    /**
     * Scope to filter by vessel.
     */
    public function scopeForVessel($query, int $vesselId)
    {
        return $query->where('vessel_id', $vesselId);
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeRecordedBetween($query, $start, $end)
    {
        return $query->whereBetween('recorded_at', [$start, $end]);
    }

    /**
     * Scope to filter by data source.
     */
    public function scopeFromSource($query, string $source)
    {
        return $query->where('data_source', $source);
    }

    /**
     * Scope to filter by navigation status.
     */
    public function scopeWithNavStatus($query, string $status)
    {
        return $query->where('nav_status', $status);
    }

    /**
     * Scope to filter vessels in motion.
     */
    public function scopeInMotion($query, float $minSpeed = 0.5)
    {
        return $query->where('speed_knots', '>=', $minSpeed);
    }

    /**
     * Scope to filter stationary vessels.
     */
    public function scopeStationary($query, float $maxSpeed = 0.5)
    {
        return $query->where('speed_knots', '<', $maxSpeed)
            ->orWhereNull('speed_knots');
    }

    /**
     * Scope to filter by geographic bounding box.
     */
    public function scopeWithinBounds($query, float $swLat, float $swLng, float $neLat, float $neLng)
    {
        $polygon = sprintf(
            "POLYGON((%f %f, %f %f, %f %f, %f %f, %f %f))",
            $swLng, $swLat,
            $neLng, $swLat,
            $neLng, $neLat,
            $swLng, $neLat,
            $swLng, $swLat
        );

        return $query->whereRaw(
            "ST_Contains(ST_GeomFromText(?, 4326), position)",
            [$polygon]
        );
    }

    /**
     * Scope to filter positions within radius of a point.
     */
    public function scopeWithinRadius($query, float $lat, float $lng, float $radiusKm)
    {
        $radiusMeters = $radiusKm * 1000;

        return $query->whereRaw(
            "ST_Distance_Sphere(position, ST_GeomFromText('POINT(? ?)', 4326)) <= ?",
            [$lng, $lat, $radiusMeters]
        );
    }

    /**
     * Get all navigation statuses.
     *
     * @return array<string, string>
     */
    public static function getNavStatuses(): array
    {
        return [
            self::NAV_UNDERWAY_ENGINE => 'Underway Using Engine',
            self::NAV_AT_ANCHOR => 'At Anchor',
            self::NAV_NOT_UNDER_COMMAND => 'Not Under Command',
            self::NAV_RESTRICTED_MANOEUVRABILITY => 'Restricted Manoeuvrability',
            self::NAV_CONSTRAINED_BY_DRAUGHT => 'Constrained by Draught',
            self::NAV_MOORED => 'Moored',
            self::NAV_AGROUND => 'Aground',
            self::NAV_ENGAGED_IN_FISHING => 'Engaged in Fishing',
            self::NAV_UNDERWAY_SAILING => 'Underway Sailing',
            self::NAV_NOT_DEFINED => 'Not Defined',
        ];
    }

    /**
     * Get all data sources.
     *
     * @return array<string, string>
     */
    public static function getDataSources(): array
    {
        return [
            self::SOURCE_MARINETRAFFIC => 'MarineTraffic',
            self::SOURCE_VESSELFINDER => 'VesselFinder',
            self::SOURCE_AIS_RECEIVER => 'AIS Receiver',
            self::SOURCE_MYSHIPTRACKING => 'MyShipTracking',
            self::SOURCE_FLEETMON => 'FleetMon',
            self::SOURCE_MANUAL => 'Manual Entry',
        ];
    }

    /**
     * Get display name for navigation status.
     */
    public function getNavStatusDisplayAttribute(): string
    {
        return self::getNavStatuses()[$this->nav_status] ?? 'Unknown';
    }

    /**
     * Get display name for data source.
     */
    public function getDataSourceDisplayAttribute(): string
    {
        return self::getDataSources()[$this->data_source] ?? 'Unknown';
    }

    /**
     * Calculate distance to another position in nautical miles.
     */
    public function distanceTo(VesselPosition $other): ?float
    {
        $result = DB::selectOne(
            "SELECT ST_Distance_Sphere(
                (SELECT position FROM vessel_positions WHERE id = ?),
                (SELECT position FROM vessel_positions WHERE id = ?)
            ) / 1852 as distance_nm",
            [$this->id, $other->id]
        );

        return $result ? (float) $result->distance_nm : null;
    }
}
