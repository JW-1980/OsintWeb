<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * WeatherData Model
 *
 * Stores weather and environmental data for OSINT verification purposes.
 *
 * @property int $id
 * @property string $uuid
 * @property mixed $location
 * @property float $latitude
 * @property float $longitude
 * @property string|null $location_name
 * @property \Carbon\Carbon $recorded_at
 * @property float|null $temperature
 * @property float|null $feels_like
 * @property float|null $temperature_min
 * @property float|null $temperature_max
 * @property int|null $humidity
 * @property float|null $wind_speed
 * @property float|null $wind_direction
 * @property float|null $wind_gust
 * @property int|null $visibility
 * @property int|null $cloud_cover
 * @property string $precipitation_type
 * @property float|null $precipitation_amount
 * @property float|null $snow_depth
 * @property float|null $pressure
 * @property float|null $pressure_sea_level
 * @property float|null $pressure_ground_level
 * @property float|null $uv_index
 * @property string|null $sunrise
 * @property string|null $sunset
 * @property float|null $sun_azimuth
 * @property float|null $sun_elevation
 * @property float|null $solar_noon
 * @property string $weather_condition
 * @property string|null $weather_description
 * @property string|null $weather_icon
 * @property string $data_source
 * @property string|null $data_source_id
 * @property string $data_type
 * @property array|null $raw_data
 * @property int|null $fetched_by_user_id
 * @property \Carbon\Carbon|null $fetched_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class WeatherData extends Model
{
    use HasFactory;

    protected $table = 'weather_data';

    // Weather conditions
    public const CONDITION_CLEAR = 'clear';
    public const CONDITION_PARTLY_CLOUDY = 'partly_cloudy';
    public const CONDITION_CLOUDY = 'cloudy';
    public const CONDITION_OVERCAST = 'overcast';
    public const CONDITION_FOG = 'fog';
    public const CONDITION_MIST = 'mist';
    public const CONDITION_HAZE = 'haze';
    public const CONDITION_RAIN = 'rain';
    public const CONDITION_DRIZZLE = 'drizzle';
    public const CONDITION_SNOW = 'snow';
    public const CONDITION_THUNDERSTORM = 'thunderstorm';
    public const CONDITION_SLEET = 'sleet';
    public const CONDITION_DUST = 'dust';
    public const CONDITION_SAND = 'sand';
    public const CONDITION_SMOKE = 'smoke';
    public const CONDITION_UNKNOWN = 'unknown';

    // Precipitation types
    public const PRECIPITATION_NONE = 'none';
    public const PRECIPITATION_RAIN = 'rain';
    public const PRECIPITATION_DRIZZLE = 'drizzle';
    public const PRECIPITATION_SNOW = 'snow';
    public const PRECIPITATION_SLEET = 'sleet';
    public const PRECIPITATION_HAIL = 'hail';
    public const PRECIPITATION_FREEZING_RAIN = 'freezing_rain';

    // Data types
    public const DATA_TYPE_CURRENT = 'current';
    public const DATA_TYPE_HISTORICAL = 'historical';
    public const DATA_TYPE_FORECAST = 'forecast';

    // Data sources
    public const SOURCE_OPENWEATHERMAP = 'openweathermap';
    public const SOURCE_VISUAL_CROSSING = 'visualcrossing';
    public const SOURCE_MANUAL = 'manual';

    protected $fillable = [
        'uuid',
        'location',
        'latitude',
        'longitude',
        'location_name',
        'recorded_at',
        'temperature',
        'feels_like',
        'temperature_min',
        'temperature_max',
        'humidity',
        'wind_speed',
        'wind_direction',
        'wind_gust',
        'visibility',
        'cloud_cover',
        'precipitation_type',
        'precipitation_amount',
        'snow_depth',
        'pressure',
        'pressure_sea_level',
        'pressure_ground_level',
        'uv_index',
        'sunrise',
        'sunset',
        'sun_azimuth',
        'sun_elevation',
        'solar_noon',
        'weather_condition',
        'weather_description',
        'weather_icon',
        'data_source',
        'data_source_id',
        'data_type',
        'raw_data',
        'fetched_by_user_id',
        'fetched_at',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'recorded_at' => 'datetime',
        'temperature' => 'decimal:2',
        'feels_like' => 'decimal:2',
        'temperature_min' => 'decimal:2',
        'temperature_max' => 'decimal:2',
        'humidity' => 'integer',
        'wind_speed' => 'decimal:2',
        'wind_direction' => 'decimal:2',
        'wind_gust' => 'decimal:2',
        'visibility' => 'integer',
        'cloud_cover' => 'integer',
        'precipitation_amount' => 'decimal:2',
        'snow_depth' => 'decimal:2',
        'pressure' => 'decimal:1',
        'pressure_sea_level' => 'decimal:1',
        'pressure_ground_level' => 'decimal:1',
        'uv_index' => 'decimal:2',
        'sun_azimuth' => 'decimal:2',
        'sun_elevation' => 'decimal:2',
        'solar_noon' => 'decimal:4',
        'raw_data' => 'array',
        'fetched_at' => 'datetime',
    ];

    /**
     * Get the user who fetched this weather data
     */
    public function fetchedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'fetched_by_user_id');
    }

    /**
     * Get the events linked to this weather data
     */
    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'event_weather')
            ->withPivot([
                'link_type',
                'distance_km',
                'time_diff_minutes',
                'shadow_verified',
                'claimed_shadow_angle',
                'calculated_shadow_angle',
                'shadow_angle_deviation',
                'shadow_matches',
                'verification_notes',
                'linked_by_user_id',
                'linked_at',
            ])
            ->withTimestamps();
    }

    /**
     * Calculate sun position for shadow verification
     *
     * Uses astronomical calculations to determine the sun's position
     * at the recorded time and location.
     *
     * @return array{azimuth: float, elevation: float, is_daylight: bool}
     */
    public function getSunPosition(): array
    {
        // If we already have calculated values, return them
        if ($this->sun_azimuth !== null && $this->sun_elevation !== null) {
            return [
                'azimuth' => (float) $this->sun_azimuth,
                'elevation' => (float) $this->sun_elevation,
                'is_daylight' => $this->sun_elevation > 0,
            ];
        }

        // Calculate sun position
        return $this->calculateSunPositionForDateTime(
            (float) $this->latitude,
            (float) $this->longitude,
            $this->recorded_at
        );
    }

    /**
     * Calculate sun position for a given datetime and location
     *
     * @param float $latitude
     * @param float $longitude
     * @param Carbon $datetime
     * @return array{azimuth: float, elevation: float, is_daylight: bool}
     */
    public function calculateSunPositionForDateTime(float $latitude, float $longitude, Carbon $datetime): array
    {
        // Convert to Julian date
        $jd = $this->dateTimeToJulianDate($datetime);

        // Calculate Julian century
        $T = ($jd - 2451545.0) / 36525.0;

        // Geometric mean longitude of the Sun (degrees)
        $L0 = fmod(280.46646 + $T * (36000.76983 + 0.0003032 * $T), 360);

        // Mean anomaly of the Sun (degrees)
        $M = fmod(357.52911 + $T * (35999.05029 - 0.0001537 * $T), 360);

        // Eccentricity of Earth's orbit
        $e = 0.016708634 - $T * (0.000042037 + 0.0000001267 * $T);

        // Sun's equation of center
        $C = sin(deg2rad($M)) * (1.914602 - $T * (0.004817 + 0.000014 * $T))
            + sin(deg2rad(2 * $M)) * (0.019993 - 0.000101 * $T)
            + sin(deg2rad(3 * $M)) * 0.000289;

        // Sun's true longitude
        $sunLong = $L0 + $C;

        // Sun's apparent longitude
        $omega = 125.04 - 1934.136 * $T;
        $lambda = $sunLong - 0.00569 - 0.00478 * sin(deg2rad($omega));

        // Mean obliquity of the ecliptic
        $obliq = 23.439291 - $T * (0.013004167 + $T * (0.0000001639 - $T * 0.0000005036));

        // Corrected obliquity
        $obliqCorr = $obliq + 0.00256 * cos(deg2rad($omega));

        // Sun's declination
        $declination = rad2deg(asin(sin(deg2rad($obliqCorr)) * sin(deg2rad($lambda))));

        // Equation of time (minutes)
        $y = tan(deg2rad($obliqCorr / 2)) ** 2;
        $eqTime = 4 * rad2deg(
            $y * sin(2 * deg2rad($L0))
            - 2 * $e * sin(deg2rad($M))
            + 4 * $e * $y * sin(deg2rad($M)) * cos(2 * deg2rad($L0))
            - 0.5 * $y * $y * sin(4 * deg2rad($L0))
            - 1.25 * $e * $e * sin(2 * deg2rad($M))
        );

        // Time offset (minutes) from UTC
        $timeOffset = $eqTime + 4 * $longitude;

        // True solar time
        $trueSolarTime = fmod(
            $datetime->hour * 60 + $datetime->minute + $datetime->second / 60 + $timeOffset,
            1440
        );

        // Hour angle
        $hourAngle = ($trueSolarTime / 4) - 180;
        if ($hourAngle < -180) {
            $hourAngle += 360;
        }

        // Solar zenith angle
        $zenith = rad2deg(acos(
            sin(deg2rad($latitude)) * sin(deg2rad($declination))
            + cos(deg2rad($latitude)) * cos(deg2rad($declination)) * cos(deg2rad($hourAngle))
        ));

        // Solar elevation angle
        $elevation = 90 - $zenith;

        // Solar azimuth angle
        if ($hourAngle > 0) {
            $azimuth = fmod(
                rad2deg(acos(
                    ((sin(deg2rad($latitude)) * cos(deg2rad($zenith))) - sin(deg2rad($declination)))
                    / (cos(deg2rad($latitude)) * sin(deg2rad($zenith)))
                )) + 180,
                360
            );
        } else {
            $azimuth = fmod(
                540 - rad2deg(acos(
                    ((sin(deg2rad($latitude)) * cos(deg2rad($zenith))) - sin(deg2rad($declination)))
                    / (cos(deg2rad($latitude)) * sin(deg2rad($zenith)))
                )),
                360
            );
        }

        return [
            'azimuth' => round($azimuth, 2),
            'elevation' => round($elevation, 2),
            'is_daylight' => $elevation > 0,
        ];
    }

    /**
     * Convert datetime to Julian date
     */
    private function dateTimeToJulianDate(Carbon $datetime): float
    {
        $year = $datetime->year;
        $month = $datetime->month;
        $day = $datetime->day + ($datetime->hour + ($datetime->minute + $datetime->second / 60) / 60) / 24;

        if ($month <= 2) {
            $year -= 1;
            $month += 12;
        }

        $A = floor($year / 100);
        $B = 2 - $A + floor($A / 4);

        return floor(365.25 * ($year + 4716)) + floor(30.6001 * ($month + 1)) + $day + $B - 1524.5;
    }

    /**
     * Calculate expected shadow angle based on sun position
     *
     * @return float|null Shadow angle in degrees (from north), null if sun is below horizon
     */
    public function getExpectedShadowAngle(): ?float
    {
        $sunPosition = $this->getSunPosition();

        // No shadow if sun is below horizon
        if ($sunPosition['elevation'] <= 0) {
            return null;
        }

        // Shadow angle is opposite to sun azimuth (180 degrees offset)
        $shadowAngle = fmod($sunPosition['azimuth'] + 180, 360);

        return round($shadowAngle, 2);
    }

    /**
     * Calculate shadow length ratio (for vertical objects)
     *
     * Returns the ratio of shadow length to object height.
     *
     * @return float|null Shadow length ratio, null if sun is below horizon
     */
    public function getShadowLengthRatio(): ?float
    {
        $sunPosition = $this->getSunPosition();

        // No shadow if sun is at or below horizon
        if ($sunPosition['elevation'] <= 0) {
            return null;
        }

        // Shadow length ratio = 1 / tan(elevation)
        // This gives the ratio of shadow length to object height
        return round(1 / tan(deg2rad($sunPosition['elevation'])), 4);
    }

    /**
     * Verify if a claimed shadow angle matches the expected angle
     *
     * @param float $claimedAngle The shadow angle claimed in the image/video
     * @param float $tolerance Acceptable deviation in degrees (default 15)
     * @return array{matches: bool, deviation: float, expected: float|null, claimed: float}
     */
    public function verifyShadowAngle(float $claimedAngle, float $tolerance = 15.0): array
    {
        $expectedAngle = $this->getExpectedShadowAngle();

        if ($expectedAngle === null) {
            return [
                'matches' => false,
                'deviation' => null,
                'expected' => null,
                'claimed' => $claimedAngle,
                'reason' => 'Sun below horizon - no shadow expected',
            ];
        }

        // Calculate angular deviation (accounting for circular nature of angles)
        $deviation = abs($claimedAngle - $expectedAngle);
        if ($deviation > 180) {
            $deviation = 360 - $deviation;
        }

        return [
            'matches' => $deviation <= $tolerance,
            'deviation' => round($deviation, 2),
            'expected' => $expectedAngle,
            'claimed' => $claimedAngle,
            'tolerance' => $tolerance,
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

    /**
     * Get weather summary
     */
    public function getSummary(): array
    {
        return [
            'condition' => $this->weather_condition,
            'description' => $this->weather_description,
            'temperature' => $this->temperature,
            'feels_like' => $this->feels_like,
            'humidity' => $this->humidity,
            'wind_speed' => $this->wind_speed,
            'visibility' => $this->visibility,
            'cloud_cover' => $this->cloud_cover,
        ];
    }

    /**
     * Check if weather is suitable for shadow analysis
     */
    public function isSuitableForShadowAnalysis(): bool
    {
        $sunPosition = $this->getSunPosition();

        // Need daylight
        if (!$sunPosition['is_daylight']) {
            return false;
        }

        // Need clear or partly cloudy conditions
        $clearConditions = [
            self::CONDITION_CLEAR,
            self::CONDITION_PARTLY_CLOUDY,
        ];

        if (!in_array($this->weather_condition, $clearConditions)) {
            return false;
        }

        // Cloud cover should be low enough
        if ($this->cloud_cover !== null && $this->cloud_cover > 50) {
            return false;
        }

        return true;
    }

    /**
     * Scope to find weather data near a location
     */
    public function scopeNearLocation(Builder $query, float $latitude, float $longitude, float $radiusKm = 50): Builder
    {
        // Using Haversine formula for distance calculation
        return $query->whereRaw(
            '(6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) <= ?',
            [$latitude, $longitude, $latitude, $radiusKm]
        );
    }

    /**
     * Scope to find weather data using spatial ST_Distance_Sphere
     */
    public function scopeWithinRadius(Builder $query, float $latitude, float $longitude, float $radiusMeters): Builder
    {
        return $query->whereRaw(
            'ST_Distance_Sphere(location, ST_GeomFromText(?, 4326)) <= ?',
            ["POINT({$longitude} {$latitude})", $radiusMeters]
        );
    }

    /**
     * Scope to filter by date range
     */
    public function scopeDateRange(Builder $query, Carbon|string $from, Carbon|string $to): Builder
    {
        return $query->whereBetween('recorded_at', [$from, $to]);
    }

    /**
     * Scope to filter by specific date
     */
    public function scopeOnDate(Builder $query, Carbon|string $date): Builder
    {
        $date = $date instanceof Carbon ? $date : Carbon::parse($date);

        return $query->whereDate('recorded_at', $date->toDateString());
    }

    /**
     * Scope to get the closest weather data to a specific datetime
     */
    public function scopeClosestTo(Builder $query, Carbon $datetime): Builder
    {
        return $query->orderByRaw('ABS(TIMESTAMPDIFF(SECOND, recorded_at, ?))', [$datetime]);
    }

    /**
     * Scope to filter by data source
     */
    public function scopeFromSource(Builder $query, string $source): Builder
    {
        return $query->where('data_source', $source);
    }

    /**
     * Scope to filter by weather condition
     */
    public function scopeWithCondition(Builder $query, string $condition): Builder
    {
        return $query->where('weather_condition', $condition);
    }

    /**
     * Scope to filter by data type
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('data_type', $type);
    }

    /**
     * Scope to filter historical data only
     */
    public function scopeHistorical(Builder $query): Builder
    {
        return $query->where('data_type', self::DATA_TYPE_HISTORICAL);
    }

    /**
     * Scope to find weather suitable for shadow analysis
     */
    public function scopeSuitableForShadowAnalysis(Builder $query): Builder
    {
        return $query->whereIn('weather_condition', [
            self::CONDITION_CLEAR,
            self::CONDITION_PARTLY_CLOUDY,
        ])->where(function ($q) {
            $q->whereNull('cloud_cover')
                ->orWhere('cloud_cover', '<=', 50);
        });
    }

    /**
     * Get all available weather conditions
     */
    public static function getConditions(): array
    {
        return [
            self::CONDITION_CLEAR,
            self::CONDITION_PARTLY_CLOUDY,
            self::CONDITION_CLOUDY,
            self::CONDITION_OVERCAST,
            self::CONDITION_FOG,
            self::CONDITION_MIST,
            self::CONDITION_HAZE,
            self::CONDITION_RAIN,
            self::CONDITION_DRIZZLE,
            self::CONDITION_SNOW,
            self::CONDITION_THUNDERSTORM,
            self::CONDITION_SLEET,
            self::CONDITION_DUST,
            self::CONDITION_SAND,
            self::CONDITION_SMOKE,
            self::CONDITION_UNKNOWN,
        ];
    }

    /**
     * Get all available data sources
     */
    public static function getDataSources(): array
    {
        return [
            self::SOURCE_OPENWEATHERMAP,
            self::SOURCE_VISUAL_CROSSING,
            self::SOURCE_MANUAL,
        ];
    }

    /**
     * Convert wind direction degrees to cardinal direction
     */
    public function getWindDirectionCardinal(): ?string
    {
        if ($this->wind_direction === null) {
            return null;
        }

        $directions = ['N', 'NNE', 'NE', 'ENE', 'E', 'ESE', 'SE', 'SSE', 'S', 'SSW', 'SW', 'WSW', 'W', 'WNW', 'NW', 'NNW'];
        $index = (int) round($this->wind_direction / 22.5) % 16;

        return $directions[$index];
    }

    /**
     * Get UV index category
     */
    public function getUvCategory(): ?string
    {
        if ($this->uv_index === null) {
            return null;
        }

        return match (true) {
            $this->uv_index < 3 => 'low',
            $this->uv_index < 6 => 'moderate',
            $this->uv_index < 8 => 'high',
            $this->uv_index < 11 => 'very_high',
            default => 'extreme',
        };
    }

    /**
     * Get visibility category
     */
    public function getVisibilityCategory(): ?string
    {
        if ($this->visibility === null) {
            return null;
        }

        return match (true) {
            $this->visibility < 1000 => 'very_poor',
            $this->visibility < 4000 => 'poor',
            $this->visibility < 10000 => 'moderate',
            $this->visibility < 20000 => 'good',
            default => 'excellent',
        };
    }

    /**
     * Create a new weather data entry from location point
     */
    public static function createWithLocation(float $latitude, float $longitude, array $attributes = []): self
    {
        $point = DB::raw(sprintf("ST_GeomFromText('POINT(%f %f)', 4326)", $longitude, $latitude));

        return static::create(array_merge([
            'uuid' => \Illuminate\Support\Str::uuid()->toString(),
            'location' => $point,
            'latitude' => $latitude,
            'longitude' => $longitude,
        ], $attributes));
    }
}
