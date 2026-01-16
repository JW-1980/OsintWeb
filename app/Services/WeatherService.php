<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Event;
use App\Models\User;
use App\Models\WeatherData;
use Carbon\Carbon;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Weather Service for fetching and analyzing weather data
 *
 * Supports multiple weather API providers and provides
 * chronolocation/shadow analysis capabilities for OSINT.
 */
class WeatherService
{
    private const CACHE_PREFIX = 'weather:';
    private const CACHE_TTL_CURRENT = 1800; // 30 minutes for current weather
    private const CACHE_TTL_HISTORICAL = 86400 * 30; // 30 days for historical data

    // Provider API endpoints
    private const OPENWEATHERMAP_BASE_URL = 'https://api.openweathermap.org/data/3.0';
    private const VISUAL_CROSSING_BASE_URL = 'https://weather.visualcrossing.com/VisualCrossingWebServices/rest/services/timeline';

    protected string $defaultProvider;
    protected array $providers;

    public function __construct()
    {
        $this->defaultProvider = config('weather.default_provider', 'openweathermap');
        $this->providers = config('weather.providers', []);
    }

    /**
     * Fetch historical weather for a location and datetime
     *
     * @param float $latitude
     * @param float $longitude
     * @param Carbon $datetime
     * @param string|null $provider
     * @param User|null $user
     * @return WeatherData
     * @throws \Exception
     */
    public function fetchHistoricalWeather(
        float $latitude,
        float $longitude,
        Carbon $datetime,
        ?string $provider = null,
        ?User $user = null
    ): WeatherData {
        $provider = $provider ?? $this->defaultProvider;

        // Check cache first
        $cacheKey = $this->getCacheKey('historical', $latitude, $longitude, $datetime->timestamp);
        $cached = Cache::get($cacheKey);

        if ($cached instanceof WeatherData) {
            return $cached;
        }

        // Check if we already have this data in the database
        $existing = WeatherData::nearLocation($latitude, $longitude, 5) // Within 5km
            ->where('recorded_at', '>=', $datetime->copy()->subHours(1))
            ->where('recorded_at', '<=', $datetime->copy()->addHours(1))
            ->historical()
            ->first();

        if ($existing) {
            Cache::put($cacheKey, $existing, self::CACHE_TTL_HISTORICAL);
            return $existing;
        }

        // Fetch from API
        $weatherData = match ($provider) {
            'openweathermap' => $this->fetchFromOpenWeatherMap($latitude, $longitude, $datetime, true),
            'visualcrossing' => $this->fetchFromVisualCrossing($latitude, $longitude, $datetime),
            default => throw new \InvalidArgumentException("Unknown weather provider: {$provider}"),
        };

        // Calculate sun position
        $sunPosition = $this->calculateSunPosition($latitude, $longitude, $datetime);

        // Save to database
        $record = WeatherData::createWithLocation($latitude, $longitude, array_merge(
            $weatherData,
            [
                'recorded_at' => $datetime,
                'data_type' => WeatherData::DATA_TYPE_HISTORICAL,
                'data_source' => $provider,
                'sun_azimuth' => $sunPosition['azimuth'],
                'sun_elevation' => $sunPosition['elevation'],
                'fetched_by_user_id' => $user?->id,
                'fetched_at' => now(),
            ]
        ));

        Cache::put($cacheKey, $record, self::CACHE_TTL_HISTORICAL);

        return $record;
    }

    /**
     * Fetch current weather for a location
     *
     * @param float $latitude
     * @param float $longitude
     * @param string|null $provider
     * @param User|null $user
     * @return WeatherData
     */
    public function fetchCurrentWeather(
        float $latitude,
        float $longitude,
        ?string $provider = null,
        ?User $user = null
    ): WeatherData {
        $provider = $provider ?? $this->defaultProvider;
        $now = now();

        // Check cache
        $cacheKey = $this->getCacheKey('current', $latitude, $longitude);
        $cached = Cache::get($cacheKey);

        if ($cached instanceof WeatherData) {
            return $cached;
        }

        // Fetch from API
        $weatherData = match ($provider) {
            'openweathermap' => $this->fetchFromOpenWeatherMap($latitude, $longitude, $now, false),
            'visualcrossing' => $this->fetchFromVisualCrossing($latitude, $longitude, $now),
            default => throw new \InvalidArgumentException("Unknown weather provider: {$provider}"),
        };

        // Calculate sun position
        $sunPosition = $this->calculateSunPosition($latitude, $longitude, $now);

        // Save to database
        $record = WeatherData::createWithLocation($latitude, $longitude, array_merge(
            $weatherData,
            [
                'recorded_at' => $now,
                'data_type' => WeatherData::DATA_TYPE_CURRENT,
                'data_source' => $provider,
                'sun_azimuth' => $sunPosition['azimuth'],
                'sun_elevation' => $sunPosition['elevation'],
                'fetched_by_user_id' => $user?->id,
                'fetched_at' => now(),
            ]
        ));

        Cache::put($cacheKey, $record, self::CACHE_TTL_CURRENT);

        return $record;
    }

    /**
     * Fetch weather from OpenWeatherMap API
     */
    private function fetchFromOpenWeatherMap(
        float $latitude,
        float $longitude,
        Carbon $datetime,
        bool $historical
    ): array {
        $apiKey = $this->providers['openweathermap']['api_key'] ?? '';

        if (empty($apiKey)) {
            throw new \Exception('OpenWeatherMap API key not configured');
        }

        try {
            if ($historical) {
                // Use Time Machine API for historical data
                $response = Http::timeout(30)->get(self::OPENWEATHERMAP_BASE_URL . '/timemachine', [
                    'lat' => $latitude,
                    'lon' => $longitude,
                    'dt' => $datetime->timestamp,
                    'appid' => $apiKey,
                    'units' => 'metric',
                ]);
            } else {
                // Use One Call API for current data
                $response = Http::timeout(30)->get(self::OPENWEATHERMAP_BASE_URL . '/onecall', [
                    'lat' => $latitude,
                    'lon' => $longitude,
                    'appid' => $apiKey,
                    'units' => 'metric',
                    'exclude' => 'minutely,hourly,daily,alerts',
                ]);
            }

            if (!$response->successful()) {
                Log::error('OpenWeatherMap API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new RequestException($response);
            }

            $data = $response->json();

            return $this->parseOpenWeatherMapResponse($data, $historical);
        } catch (RequestException $e) {
            Log::error('OpenWeatherMap API request failed', [
                'error' => $e->getMessage(),
                'lat' => $latitude,
                'lng' => $longitude,
            ]);
            throw $e;
        }
    }

    /**
     * Parse OpenWeatherMap API response
     */
    private function parseOpenWeatherMapResponse(array $data, bool $historical): array
    {
        $current = $historical ? ($data['data'][0] ?? $data) : ($data['current'] ?? $data);
        $weather = $current['weather'][0] ?? [];

        return [
            'temperature' => $current['temp'] ?? null,
            'feels_like' => $current['feels_like'] ?? null,
            'humidity' => $current['humidity'] ?? null,
            'pressure' => $current['pressure'] ?? null,
            'wind_speed' => $current['wind_speed'] ?? null,
            'wind_direction' => $current['wind_deg'] ?? null,
            'wind_gust' => $current['wind_gust'] ?? null,
            'visibility' => $current['visibility'] ?? null,
            'cloud_cover' => $current['clouds'] ?? null,
            'uv_index' => $current['uvi'] ?? null,
            'sunrise' => isset($current['sunrise']) ? Carbon::createFromTimestamp($current['sunrise'])->format('H:i:s') : null,
            'sunset' => isset($current['sunset']) ? Carbon::createFromTimestamp($current['sunset'])->format('H:i:s') : null,
            'weather_condition' => $this->mapOpenWeatherCondition($weather['id'] ?? 0),
            'weather_description' => $weather['description'] ?? null,
            'weather_icon' => $weather['icon'] ?? null,
            'precipitation_type' => $this->mapPrecipitationType($weather['id'] ?? 0),
            'precipitation_amount' => $current['rain']['1h'] ?? $current['snow']['1h'] ?? null,
            'snow_depth' => $current['snow']['1h'] ?? null,
            'data_source_id' => (string) ($weather['id'] ?? ''),
            'raw_data' => $data,
        ];
    }

    /**
     * Fetch weather from Visual Crossing API
     */
    private function fetchFromVisualCrossing(
        float $latitude,
        float $longitude,
        Carbon $datetime
    ): array {
        $apiKey = $this->providers['visualcrossing']['api_key'] ?? '';

        if (empty($apiKey)) {
            throw new \Exception('Visual Crossing API key not configured');
        }

        try {
            $dateString = $datetime->format('Y-m-d');
            $url = sprintf(
                '%s/%f,%f/%s',
                self::VISUAL_CROSSING_BASE_URL,
                $latitude,
                $longitude,
                $dateString
            );

            $response = Http::timeout(30)->get($url, [
                'key' => $apiKey,
                'unitGroup' => 'metric',
                'include' => 'hours,current',
                'contentType' => 'json',
            ]);

            if (!$response->successful()) {
                Log::error('Visual Crossing API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new RequestException($response);
            }

            $data = $response->json();

            return $this->parseVisualCrossingResponse($data, $datetime);
        } catch (RequestException $e) {
            Log::error('Visual Crossing API request failed', [
                'error' => $e->getMessage(),
                'lat' => $latitude,
                'lng' => $longitude,
            ]);
            throw $e;
        }
    }

    /**
     * Parse Visual Crossing API response
     */
    private function parseVisualCrossingResponse(array $data, Carbon $datetime): array
    {
        // Find the closest hour
        $targetHour = $datetime->format('H:i:s');
        $days = $data['days'] ?? [];
        $dayData = $days[0] ?? [];
        $hours = $dayData['hours'] ?? [];

        $current = null;
        $minDiff = PHP_INT_MAX;

        foreach ($hours as $hourData) {
            $hourTime = Carbon::parse($hourData['datetime']);
            $diff = abs($datetime->diffInMinutes($hourTime));
            if ($diff < $minDiff) {
                $minDiff = $diff;
                $current = $hourData;
            }
        }

        $current = $current ?? $dayData;

        return [
            'temperature' => $current['temp'] ?? null,
            'feels_like' => $current['feelslike'] ?? null,
            'humidity' => isset($current['humidity']) ? (int) $current['humidity'] : null,
            'pressure' => $current['pressure'] ?? null,
            'wind_speed' => isset($current['windspeed']) ? $current['windspeed'] / 3.6 : null, // Convert km/h to m/s
            'wind_direction' => $current['winddir'] ?? null,
            'wind_gust' => isset($current['windgust']) ? $current['windgust'] / 3.6 : null,
            'visibility' => isset($current['visibility']) ? (int) ($current['visibility'] * 1000) : null, // Convert km to m
            'cloud_cover' => isset($current['cloudcover']) ? (int) $current['cloudcover'] : null,
            'uv_index' => $current['uvindex'] ?? null,
            'sunrise' => $dayData['sunrise'] ?? null,
            'sunset' => $dayData['sunset'] ?? null,
            'weather_condition' => $this->mapVisualCrossingCondition($current['conditions'] ?? ''),
            'weather_description' => $current['conditions'] ?? null,
            'weather_icon' => $current['icon'] ?? null,
            'precipitation_type' => $this->mapVisualCrossingPrecipitation($current['preciptype'] ?? []),
            'precipitation_amount' => $current['precip'] ?? null,
            'snow_depth' => $current['snowdepth'] ?? null,
            'data_source_id' => null,
            'raw_data' => $data,
        ];
    }

    /**
     * Map OpenWeatherMap condition code to our enum
     */
    private function mapOpenWeatherCondition(int $code): string
    {
        return match (true) {
            $code >= 200 && $code < 300 => WeatherData::CONDITION_THUNDERSTORM,
            $code >= 300 && $code < 400 => WeatherData::CONDITION_DRIZZLE,
            $code >= 500 && $code < 600 => WeatherData::CONDITION_RAIN,
            $code >= 600 && $code < 700 => WeatherData::CONDITION_SNOW,
            $code === 701 => WeatherData::CONDITION_MIST,
            $code === 711 => WeatherData::CONDITION_SMOKE,
            $code === 721 => WeatherData::CONDITION_HAZE,
            $code === 731, $code === 761 => WeatherData::CONDITION_DUST,
            $code === 741 => WeatherData::CONDITION_FOG,
            $code === 751 => WeatherData::CONDITION_SAND,
            $code === 800 => WeatherData::CONDITION_CLEAR,
            $code === 801, $code === 802 => WeatherData::CONDITION_PARTLY_CLOUDY,
            $code === 803 => WeatherData::CONDITION_CLOUDY,
            $code === 804 => WeatherData::CONDITION_OVERCAST,
            default => WeatherData::CONDITION_UNKNOWN,
        };
    }

    /**
     * Map OpenWeatherMap code to precipitation type
     */
    private function mapPrecipitationType(int $code): string
    {
        return match (true) {
            $code >= 300 && $code < 400 => WeatherData::PRECIPITATION_DRIZZLE,
            $code >= 500 && $code < 600 => WeatherData::PRECIPITATION_RAIN,
            $code >= 600 && $code < 700 => WeatherData::PRECIPITATION_SNOW,
            $code === 611, $code === 612, $code === 613 => WeatherData::PRECIPITATION_SLEET,
            $code === 511 => WeatherData::PRECIPITATION_FREEZING_RAIN,
            default => WeatherData::PRECIPITATION_NONE,
        };
    }

    /**
     * Map Visual Crossing condition string to our enum
     */
    private function mapVisualCrossingCondition(string $condition): string
    {
        $condition = strtolower($condition);

        return match (true) {
            str_contains($condition, 'thunder') => WeatherData::CONDITION_THUNDERSTORM,
            str_contains($condition, 'drizzle') => WeatherData::CONDITION_DRIZZLE,
            str_contains($condition, 'rain') || str_contains($condition, 'shower') => WeatherData::CONDITION_RAIN,
            str_contains($condition, 'snow') || str_contains($condition, 'blizzard') => WeatherData::CONDITION_SNOW,
            str_contains($condition, 'sleet') || str_contains($condition, 'ice') => WeatherData::CONDITION_SLEET,
            str_contains($condition, 'fog') => WeatherData::CONDITION_FOG,
            str_contains($condition, 'mist') => WeatherData::CONDITION_MIST,
            str_contains($condition, 'haze') => WeatherData::CONDITION_HAZE,
            str_contains($condition, 'dust') => WeatherData::CONDITION_DUST,
            str_contains($condition, 'sand') => WeatherData::CONDITION_SAND,
            str_contains($condition, 'smoke') => WeatherData::CONDITION_SMOKE,
            str_contains($condition, 'overcast') => WeatherData::CONDITION_OVERCAST,
            str_contains($condition, 'cloud') => WeatherData::CONDITION_CLOUDY,
            str_contains($condition, 'partly') => WeatherData::CONDITION_PARTLY_CLOUDY,
            str_contains($condition, 'clear') || str_contains($condition, 'sunny') => WeatherData::CONDITION_CLEAR,
            default => WeatherData::CONDITION_UNKNOWN,
        };
    }

    /**
     * Map Visual Crossing precipitation type
     */
    private function mapVisualCrossingPrecipitation(?array $precipTypes): string
    {
        if (empty($precipTypes)) {
            return WeatherData::PRECIPITATION_NONE;
        }

        $type = strtolower($precipTypes[0] ?? '');

        return match (true) {
            str_contains($type, 'rain') => WeatherData::PRECIPITATION_RAIN,
            str_contains($type, 'snow') => WeatherData::PRECIPITATION_SNOW,
            str_contains($type, 'sleet') => WeatherData::PRECIPITATION_SLEET,
            str_contains($type, 'hail') => WeatherData::PRECIPITATION_HAIL,
            str_contains($type, 'freezing') => WeatherData::PRECIPITATION_FREEZING_RAIN,
            default => WeatherData::PRECIPITATION_NONE,
        };
    }

    /**
     * Calculate sun position for a given location and time
     *
     * Uses astronomical calculations for accurate sun position.
     *
     * @param float $latitude
     * @param float $longitude
     * @param Carbon $datetime
     * @return array{azimuth: float, elevation: float, is_daylight: bool, sunrise: string|null, sunset: string|null, solar_noon: float|null}
     */
    public function calculateSunPosition(float $latitude, float $longitude, Carbon $datetime): array
    {
        // Create a temporary WeatherData instance to use its calculation method
        $tempWeather = new WeatherData();
        $position = $tempWeather->calculateSunPositionForDateTime($latitude, $longitude, $datetime);

        // Calculate sunrise/sunset times
        $sunTimes = $this->calculateSunriseSunset($latitude, $longitude, $datetime);

        return array_merge($position, [
            'sunrise' => $sunTimes['sunrise'],
            'sunset' => $sunTimes['sunset'],
            'solar_noon' => $sunTimes['solar_noon'],
        ]);
    }

    /**
     * Calculate sunrise and sunset times
     */
    private function calculateSunriseSunset(float $latitude, float $longitude, Carbon $datetime): array
    {
        // Use PHP's built-in functions for sunrise/sunset
        $timestamp = $datetime->timestamp;

        $sunrise = date_sunrise($timestamp, SUNFUNCS_RET_STRING, $latitude, $longitude, 90.833, $datetime->offsetHours);
        $sunset = date_sunset($timestamp, SUNFUNCS_RET_STRING, $latitude, $longitude, 90.833, $datetime->offsetHours);

        // Calculate solar noon
        $sunriseTimestamp = strtotime($datetime->format('Y-m-d') . ' ' . $sunrise);
        $sunsetTimestamp = strtotime($datetime->format('Y-m-d') . ' ' . $sunset);
        $solarNoonTimestamp = ($sunriseTimestamp + $sunsetTimestamp) / 2;
        $solarNoonHours = (float) date('G', (int) $solarNoonTimestamp) + (float) date('i', (int) $solarNoonTimestamp) / 60;

        return [
            'sunrise' => $sunrise,
            'sunset' => $sunset,
            'solar_noon' => round($solarNoonHours, 4),
        ];
    }

    /**
     * Verify if a shadow angle matches the expected angle for a given time/location
     *
     * @param float $claimedShadowAngle The shadow angle claimed in the image/video
     * @param float $latitude
     * @param float $longitude
     * @param Carbon $datetime
     * @param float $tolerance Acceptable deviation in degrees (default 15)
     * @return array{matches: bool, deviation: float|null, expected: float|null, claimed: float, sun_position: array, analysis: string}
     */
    public function verifyShadowAngle(
        float $claimedShadowAngle,
        float $latitude,
        float $longitude,
        Carbon $datetime,
        float $tolerance = 15.0
    ): array {
        $sunPosition = $this->calculateSunPosition($latitude, $longitude, $datetime);

        // No shadow if sun is below horizon
        if ($sunPosition['elevation'] <= 0) {
            return [
                'matches' => false,
                'deviation' => null,
                'expected' => null,
                'claimed' => $claimedShadowAngle,
                'sun_position' => $sunPosition,
                'analysis' => 'Sun is below the horizon at this time - no shadow would be cast',
                'confidence' => 'low',
            ];
        }

        // Calculate expected shadow angle (opposite to sun azimuth)
        $expectedShadowAngle = fmod($sunPosition['azimuth'] + 180, 360);

        // Calculate angular deviation (accounting for circular nature)
        $deviation = abs($claimedShadowAngle - $expectedShadowAngle);
        if ($deviation > 180) {
            $deviation = 360 - $deviation;
        }

        $matches = $deviation <= $tolerance;

        // Generate analysis text
        $analysis = $this->generateShadowAnalysis($matches, $deviation, $tolerance, $sunPosition);

        // Calculate confidence based on sun elevation
        $confidence = $this->calculateShadowConfidence($sunPosition['elevation'], $deviation);

        return [
            'matches' => $matches,
            'deviation' => round($deviation, 2),
            'expected' => round($expectedShadowAngle, 2),
            'claimed' => $claimedShadowAngle,
            'tolerance' => $tolerance,
            'sun_position' => $sunPosition,
            'shadow_length_ratio' => $sunPosition['elevation'] > 0 ? round(1 / tan(deg2rad($sunPosition['elevation'])), 4) : null,
            'analysis' => $analysis,
            'confidence' => $confidence,
        ];
    }

    /**
     * Generate human-readable shadow analysis
     */
    private function generateShadowAnalysis(bool $matches, float $deviation, float $tolerance, array $sunPosition): string
    {
        if ($matches) {
            if ($deviation < 5) {
                return 'Shadow angle closely matches expected position - strong support for claimed time/location';
            } elseif ($deviation < 10) {
                return 'Shadow angle is consistent with expected position - supports claimed time/location';
            } else {
                return 'Shadow angle is within acceptable tolerance - moderate support for claimed time/location';
            }
        } else {
            if ($deviation > 90) {
                return sprintf(
                    'Shadow angle deviates by %.1f degrees - significantly inconsistent with claimed time. ' .
                    'This suggests the actual time may differ by several hours.',
                    $deviation
                );
            } elseif ($deviation > 45) {
                return sprintf(
                    'Shadow angle deviates by %.1f degrees - inconsistent with claimed time. ' .
                    'The actual time may differ by 1-3 hours.',
                    $deviation
                );
            } else {
                return sprintf(
                    'Shadow angle deviates by %.1f degrees (tolerance: %.1f) - minor inconsistency. ' .
                    'This could indicate the time is off by up to an hour, or measurement error.',
                    $deviation,
                    $tolerance
                );
            }
        }
    }

    /**
     * Calculate confidence level for shadow analysis
     */
    private function calculateShadowConfidence(float $elevation, float $deviation): string
    {
        // Higher sun elevation = shorter shadows = less reliable measurement
        // Lower deviation = more confidence

        if ($elevation < 10 || $elevation > 80) {
            return 'low'; // Very long or very short shadows
        }

        if ($deviation <= 5) {
            return 'high';
        } elseif ($deviation <= 15) {
            return 'medium';
        } else {
            return 'low';
        }
    }

    /**
     * Get weather data for an event
     *
     * Fetches or creates weather data linked to the event's time and location.
     *
     * @param int|Event $event
     * @param bool $forceRefresh
     * @param User|null $user
     * @return WeatherData|null
     */
    public function getWeatherForEvent(int|Event $event, bool $forceRefresh = false, ?User $user = null): ?WeatherData
    {
        if (is_int($event)) {
            $event = Event::findOrFail($event);
        }

        // Check if event has coordinates and datetime
        if ($event->latitude === null || $event->longitude === null || $event->occurred_at === null) {
            return null;
        }

        // Check for existing linked weather data
        if (!$forceRefresh) {
            $existingWeather = $event->weatherData()->first();
            if ($existingWeather) {
                return $existingWeather;
            }
        }

        // Fetch historical weather
        try {
            $weatherData = $this->fetchHistoricalWeather(
                (float) $event->latitude,
                (float) $event->longitude,
                $event->occurred_at,
                null,
                $user
            );

            // Calculate distance and time difference
            $distance = $this->calculateDistance(
                (float) $event->latitude,
                (float) $event->longitude,
                (float) $weatherData->latitude,
                (float) $weatherData->longitude
            );

            $timeDiff = abs($event->occurred_at->diffInMinutes($weatherData->recorded_at));

            // Link weather to event
            $event->weatherData()->syncWithoutDetaching([
                $weatherData->id => [
                    'link_type' => 'auto',
                    'distance_km' => $distance,
                    'time_diff_minutes' => $timeDiff,
                    'linked_by_user_id' => $user?->id,
                    'linked_at' => now(),
                ],
            ]);

            return $weatherData;
        } catch (\Exception $e) {
            Log::error('Failed to fetch weather for event', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Link weather data to an event with shadow verification
     *
     * @param Event $event
     * @param WeatherData $weatherData
     * @param float|null $claimedShadowAngle
     * @param User|null $user
     * @param string|null $notes
     * @return array
     */
    public function linkWeatherToEvent(
        Event $event,
        WeatherData $weatherData,
        ?float $claimedShadowAngle = null,
        ?User $user = null,
        ?string $notes = null
    ): array {
        $pivotData = [
            'link_type' => 'manual',
            'distance_km' => $this->calculateDistance(
                (float) $event->latitude,
                (float) $event->longitude,
                (float) $weatherData->latitude,
                (float) $weatherData->longitude
            ),
            'time_diff_minutes' => abs($event->occurred_at->diffInMinutes($weatherData->recorded_at)),
            'linked_by_user_id' => $user?->id,
            'linked_at' => now(),
            'verification_notes' => $notes,
        ];

        // Perform shadow verification if angle provided
        if ($claimedShadowAngle !== null) {
            $verification = $weatherData->verifyShadowAngle($claimedShadowAngle);

            $pivotData['shadow_verified'] = true;
            $pivotData['claimed_shadow_angle'] = $claimedShadowAngle;
            $pivotData['calculated_shadow_angle'] = $verification['expected'];
            $pivotData['shadow_angle_deviation'] = $verification['deviation'];
            $pivotData['shadow_matches'] = $verification['matches'];
        }

        $event->weatherData()->syncWithoutDetaching([
            $weatherData->id => $pivotData,
        ]);

        return $pivotData;
    }

    /**
     * Find possible times when shadow angle matches
     *
     * Useful for chronolocation - finding when a photo was taken based on shadows.
     *
     * @param float $shadowAngle
     * @param float $latitude
     * @param float $longitude
     * @param Carbon $date
     * @param float $tolerance
     * @return array
     */
    public function findMatchingTimes(
        float $shadowAngle,
        float $latitude,
        float $longitude,
        Carbon $date,
        float $tolerance = 15.0
    ): array {
        $matches = [];
        $sunTimes = $this->calculateSunriseSunset($latitude, $longitude, $date);

        // Check every 15 minutes during daylight hours
        $sunrise = Carbon::parse($date->format('Y-m-d') . ' ' . $sunTimes['sunrise']);
        $sunset = Carbon::parse($date->format('Y-m-d') . ' ' . $sunTimes['sunset']);

        $current = $sunrise->copy();
        while ($current <= $sunset) {
            $verification = $this->verifyShadowAngle($shadowAngle, $latitude, $longitude, $current, $tolerance);

            if ($verification['matches']) {
                $matches[] = [
                    'time' => $current->format('H:i'),
                    'datetime' => $current->toIso8601String(),
                    'deviation' => $verification['deviation'],
                    'sun_azimuth' => $verification['sun_position']['azimuth'],
                    'sun_elevation' => $verification['sun_position']['elevation'],
                    'confidence' => $verification['confidence'],
                ];
            }

            $current->addMinutes(15);
        }

        // Sort by smallest deviation
        usort($matches, fn($a, $b) => $a['deviation'] <=> $b['deviation']);

        return [
            'date' => $date->toDateString(),
            'location' => [
                'latitude' => $latitude,
                'longitude' => $longitude,
            ],
            'shadow_angle' => $shadowAngle,
            'tolerance' => $tolerance,
            'sunrise' => $sunTimes['sunrise'],
            'sunset' => $sunTimes['sunset'],
            'matches' => $matches,
            'match_count' => count($matches),
        ];
    }

    /**
     * Calculate Haversine distance between two points
     */
    private function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371; // km

        $latDelta = deg2rad($lat2 - $lat1);
        $lngDelta = deg2rad($lng2 - $lng1);

        $a = sin($latDelta / 2) ** 2 +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($lngDelta / 2) ** 2;

        $c = 2 * asin(sqrt($a));

        return round($earthRadius * $c, 3);
    }

    /**
     * Generate cache key
     */
    private function getCacheKey(string $type, float $latitude, float $longitude, ?int $timestamp = null): string
    {
        $roundedLat = round($latitude, 2);
        $roundedLng = round($longitude, 2);

        $key = self::CACHE_PREFIX . "{$type}:{$roundedLat}:{$roundedLng}";

        if ($timestamp !== null) {
            // Round to nearest hour for historical data
            $roundedTimestamp = floor($timestamp / 3600) * 3600;
            $key .= ":{$roundedTimestamp}";
        }

        return $key;
    }

    /**
     * Get available weather providers
     */
    public function getAvailableProviders(): array
    {
        $available = [];

        foreach ($this->providers as $name => $config) {
            $available[$name] = [
                'name' => $config['name'] ?? ucfirst($name),
                'supports_historical' => $config['supports_historical'] ?? false,
                'configured' => !empty($config['api_key']),
            ];
        }

        return $available;
    }

    /**
     * Clear cached weather data for a location
     */
    public function clearCache(float $latitude, float $longitude): void
    {
        $roundedLat = round($latitude, 2);
        $roundedLng = round($longitude, 2);

        Cache::forget(self::CACHE_PREFIX . "current:{$roundedLat}:{$roundedLng}");
    }
}
