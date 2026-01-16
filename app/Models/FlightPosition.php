<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

/**
 * Flight Position Model
 *
 * Represents a single position report for a tracked aircraft from ADS-B data.
 *
 * @property int $id
 * @property int $aircraft_id
 * @property \Illuminate\Database\Query\Expression $position Geographic position (POINT)
 * @property float $latitude Position latitude
 * @property float $longitude Position longitude
 * @property int|null $altitude_feet Barometric altitude in feet
 * @property int|null $altitude_meters Altitude in meters
 * @property int|null $ground_speed_knots Ground speed in knots
 * @property int|null $vertical_rate Vertical rate in feet per minute
 * @property float|null $heading Aircraft heading in degrees (0-360)
 * @property string|null $squawk Transponder squawk code
 * @property bool $on_ground Whether aircraft is on ground
 * @property \Carbon\Carbon $recorded_at Timestamp from ADS-B data
 * @property string $data_source Source of the position data
 * @property array|null $raw_data Original API response data
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property-read TrackedAircraft $aircraft
 */
class FlightPosition extends Model
{
    use HasFactory;

    protected $table = 'flight_positions';

    /**
     * Data source constants
     */
    public const SOURCE_ADSB_EXCHANGE = 'adsb_exchange';
    public const SOURCE_OPENSKY = 'opensky';
    public const SOURCE_FLIGHTRADAR24 = 'flightradar24';
    public const SOURCE_FLIGHTAWARE = 'flightaware';
    public const SOURCE_LOCAL_RECEIVER = 'local_receiver';
    public const SOURCE_MANUAL = 'manual';

    /**
     * Emergency squawk codes
     */
    public const SQUAWK_HIJACK = '7500';
    public const SQUAWK_RADIO_FAILURE = '7600';
    public const SQUAWK_EMERGENCY = '7700';
    public const SQUAWK_VFR = '1200';

    protected $fillable = [
        'aircraft_id',
        'position',
        'latitude',
        'longitude',
        'altitude_feet',
        'altitude_meters',
        'ground_speed_knots',
        'vertical_rate',
        'heading',
        'squawk',
        'on_ground',
        'recorded_at',
        'data_source',
        'raw_data',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'altitude_feet' => 'integer',
        'altitude_meters' => 'integer',
        'ground_speed_knots' => 'integer',
        'vertical_rate' => 'integer',
        'heading' => 'decimal:2',
        'on_ground' => 'boolean',
        'recorded_at' => 'datetime',
        'raw_data' => 'array',
    ];

    /**
     * Get the aircraft this position belongs to
     */
    public function aircraft(): BelongsTo
    {
        return $this->belongsTo(TrackedAircraft::class, 'aircraft_id');
    }

    /**
     * Create position with spatial data
     */
    public static function createWithPosition(
        int $aircraftId,
        float $latitude,
        float $longitude,
        array $data = []
    ): self {
        $point = DB::raw("ST_GeomFromText('POINT({$longitude} {$latitude})', 4326)");

        return self::create(array_merge($data, [
            'aircraft_id' => $aircraftId,
            'position' => $point,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'altitude_meters' => isset($data['altitude_feet'])
                ? self::feetToMeters($data['altitude_feet'])
                : null,
        ]));
    }

    /**
     * Convert feet to meters
     */
    public static function feetToMeters(int $feet): int
    {
        return (int) round($feet * 0.3048);
    }

    /**
     * Convert meters to feet
     */
    public static function metersToFeet(int $meters): int
    {
        return (int) round($meters / 0.3048);
    }

    /**
     * Convert knots to km/h
     */
    public static function knotsToKmh(int $knots): float
    {
        return round($knots * 1.852, 2);
    }

    /**
     * Convert knots to mph
     */
    public static function knotsToMph(int $knots): float
    {
        return round($knots * 1.15078, 2);
    }

    /**
     * Get altitude in kilometers
     */
    public function getAltitudeKm(): ?float
    {
        if ($this->altitude_meters === null) {
            return null;
        }

        return round($this->altitude_meters / 1000, 2);
    }

    /**
     * Get ground speed in km/h
     */
    public function getSpeedKmh(): ?float
    {
        if ($this->ground_speed_knots === null) {
            return null;
        }

        return self::knotsToKmh($this->ground_speed_knots);
    }

    /**
     * Get speed in mph
     */
    public function getSpeedMph(): ?float
    {
        if ($this->ground_speed_knots === null) {
            return null;
        }

        return self::knotsToMph($this->ground_speed_knots);
    }

    /**
     * Get cardinal direction from heading
     */
    public function getHeadingCardinal(): ?string
    {
        if ($this->heading === null) {
            return null;
        }

        $directions = ['N', 'NNE', 'NE', 'ENE', 'E', 'ESE', 'SE', 'SSE', 'S', 'SSW', 'SW', 'WSW', 'W', 'WNW', 'NW', 'NNW'];
        $index = (int) round($this->heading / 22.5) % 16;

        return $directions[$index];
    }

    /**
     * Get vertical rate description
     */
    public function getVerticalRateDescription(): string
    {
        if ($this->vertical_rate === null || $this->vertical_rate === 0) {
            return 'Level';
        }

        if ($this->vertical_rate > 0) {
            return "Climbing at {$this->vertical_rate} fpm";
        }

        return "Descending at " . abs($this->vertical_rate) . " fpm";
    }

    /**
     * Check if this is an emergency squawk
     */
    public function isEmergencySquawk(): bool
    {
        return in_array($this->squawk, [
            self::SQUAWK_HIJACK,
            self::SQUAWK_RADIO_FAILURE,
            self::SQUAWK_EMERGENCY,
        ]);
    }

    /**
     * Get squawk description
     */
    public function getSquawkDescription(): ?string
    {
        return match ($this->squawk) {
            self::SQUAWK_HIJACK => 'Hijack/Unlawful Interference',
            self::SQUAWK_RADIO_FAILURE => 'Radio Failure',
            self::SQUAWK_EMERGENCY => 'General Emergency',
            self::SQUAWK_VFR => 'VFR Flight',
            default => null,
        };
    }

    /**
     * Calculate distance to another position in nautical miles
     */
    public function distanceTo(self $other): float
    {
        return $this->haversineDistance(
            $this->latitude,
            $this->longitude,
            $other->latitude,
            $other->longitude
        );
    }

    /**
     * Calculate distance to coordinates in nautical miles
     */
    public function distanceToCoordinates(float $latitude, float $longitude): float
    {
        return $this->haversineDistance(
            $this->latitude,
            $this->longitude,
            $latitude,
            $longitude
        );
    }

    /**
     * Haversine distance calculation (returns nautical miles)
     */
    private function haversineDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadiusNm = 3440.065; // Earth radius in nautical miles

        $latDelta = deg2rad($lat2 - $lat1);
        $lngDelta = deg2rad($lng2 - $lng1);

        $a = sin($latDelta / 2) ** 2 +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($lngDelta / 2) ** 2;

        $c = 2 * asin(sqrt($a));

        return round($earthRadiusNm * $c, 2);
    }

    /**
     * Scope to filter by time range
     */
    public function scopeInTimeRange($query, \Carbon\Carbon $start, \Carbon\Carbon $end)
    {
        return $query->where('recorded_at', '>=', $start)
            ->where('recorded_at', '<=', $end);
    }

    /**
     * Scope to filter by data source
     */
    public function scopeFromSource($query, string $source)
    {
        return $query->where('data_source', $source);
    }

    /**
     * Scope to filter airborne positions
     */
    public function scopeAirborne($query)
    {
        return $query->where('on_ground', false);
    }

    /**
     * Scope to filter ground positions
     */
    public function scopeOnGround($query)
    {
        return $query->where('on_ground', true);
    }

    /**
     * Scope to filter by altitude range
     */
    public function scopeAltitudeRange($query, int $minFeet, ?int $maxFeet = null)
    {
        $query->where('altitude_feet', '>=', $minFeet);

        if ($maxFeet !== null) {
            $query->where('altitude_feet', '<=', $maxFeet);
        }

        return $query;
    }

    /**
     * Scope to filter by bounding box
     */
    public function scopeWithinBounds($query, float $swLat, float $swLng, float $neLat, float $neLng)
    {
        return $query->whereBetween('latitude', [$swLat, $neLat])
            ->whereBetween('longitude', [$swLng, $neLng]);
    }

    /**
     * Scope to filter within distance of a point (in nautical miles)
     */
    public function scopeNearLocation($query, float $latitude, float $longitude, float $radiusNm)
    {
        // Approximate degree distance for efficiency (then filter with Haversine)
        $latDelta = $radiusNm / 60; // 1 degree latitude = ~60 nm
        $lngDelta = $radiusNm / (60 * cos(deg2rad($latitude)));

        return $query->whereBetween('latitude', [$latitude - $latDelta, $latitude + $latDelta])
            ->whereBetween('longitude', [$longitude - $lngDelta, $longitude + $lngDelta])
            ->whereRaw(
                "ST_Distance_Sphere(position, ST_GeomFromText('POINT(? ?)', 4326)) <= ?",
                [$longitude, $latitude, $radiusNm * 1852] // Convert nm to meters
            );
    }

    /**
     * Scope to filter by emergency squawk
     */
    public function scopeEmergency($query)
    {
        return $query->whereIn('squawk', [
            self::SQUAWK_HIJACK,
            self::SQUAWK_RADIO_FAILURE,
            self::SQUAWK_EMERGENCY,
        ]);
    }

    /**
     * Scope to filter by specific squawk code
     */
    public function scopeSquawk($query, string $code)
    {
        return $query->where('squawk', $code);
    }

    /**
     * Get all available data sources
     */
    public static function getDataSources(): array
    {
        return [
            self::SOURCE_ADSB_EXCHANGE => 'ADS-B Exchange',
            self::SOURCE_OPENSKY => 'OpenSky Network',
            self::SOURCE_FLIGHTRADAR24 => 'FlightRadar24',
            self::SOURCE_FLIGHTAWARE => 'FlightAware',
            self::SOURCE_LOCAL_RECEIVER => 'Local Receiver',
            self::SOURCE_MANUAL => 'Manual Entry',
        ];
    }

    /**
     * Get coordinates as array
     */
    public function getCoordinates(): array
    {
        return [
            'lat' => (float) $this->latitude,
            'lng' => (float) $this->longitude,
        ];
    }
}
