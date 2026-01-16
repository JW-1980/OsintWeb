<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\WeatherData;
use App\Services\WeatherService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

/**
 * Weather Controller for weather and environmental data API
 *
 * Provides endpoints for fetching weather data, sun position calculations,
 * and shadow verification for OSINT chronolocation purposes.
 */
class WeatherController extends Controller
{
    public function __construct(
        private readonly WeatherService $weatherService
    ) {
    }

    /**
     * Get weather data for a specific location and time
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getForLocation(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'datetime' => ['nullable', 'date'],
            'provider' => ['nullable', 'string', Rule::in(['openweathermap', 'visualcrossing'])],
        ]);

        try {
            $datetime = isset($validated['datetime'])
                ? Carbon::parse($validated['datetime'])
                : now();

            $isHistorical = $datetime->isPast() && $datetime->diffInHours(now()) > 1;

            if ($isHistorical) {
                $weatherData = $this->weatherService->fetchHistoricalWeather(
                    (float) $validated['latitude'],
                    (float) $validated['longitude'],
                    $datetime,
                    $validated['provider'] ?? null,
                    $request->user()
                );
            } else {
                $weatherData = $this->weatherService->fetchCurrentWeather(
                    (float) $validated['latitude'],
                    (float) $validated['longitude'],
                    $validated['provider'] ?? null,
                    $request->user()
                );
            }

            return $this->success([
                'weather' => $this->formatWeatherResponse($weatherData),
                'sun_position' => $weatherData->getSunPosition(),
                'shadow_analysis' => [
                    'suitable' => $weatherData->isSuitableForShadowAnalysis(),
                    'expected_shadow_angle' => $weatherData->getExpectedShadowAngle(),
                    'shadow_length_ratio' => $weatherData->getShadowLengthRatio(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Weather fetch failed', [
                'error' => $e->getMessage(),
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
            ]);

            return $this->error(
                'Failed to fetch weather data: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Get weather data for an event
     *
     * @param Request $request
     * @param string $eventUuid
     * @return JsonResponse
     */
    public function getForEvent(Request $request, string $eventUuid): JsonResponse
    {
        $event = Event::where('uuid', $eventUuid)->firstOrFail();

        $this->authorize('view', $event);

        if ($event->latitude === null || $event->longitude === null || $event->occurred_at === null) {
            return $this->error(
                'Event does not have complete location or time information',
                422,
                [
                    'latitude' => $event->latitude === null ? ['Latitude is required'] : [],
                    'longitude' => $event->longitude === null ? ['Longitude is required'] : [],
                    'occurred_at' => $event->occurred_at === null ? ['Event datetime is required'] : [],
                ]
            );
        }

        $forceRefresh = $request->boolean('refresh', false);

        try {
            $weatherData = $this->weatherService->getWeatherForEvent(
                $event,
                $forceRefresh,
                $request->user()
            );

            if (!$weatherData) {
                return $this->error('Could not retrieve weather data for this event', 404);
            }

            // Get pivot data if it exists
            $pivotData = $event->weatherData()
                ->where('weather_data.id', $weatherData->id)
                ->first()
                ?->pivot;

            return $this->success([
                'event' => [
                    'uuid' => $event->uuid,
                    'title' => $event->title,
                    'occurred_at' => $event->occurred_at->toIso8601String(),
                    'location' => [
                        'latitude' => $event->latitude,
                        'longitude' => $event->longitude,
                        'name' => $event->location_name,
                    ],
                ],
                'weather' => $this->formatWeatherResponse($weatherData),
                'sun_position' => $weatherData->getSunPosition(),
                'link_info' => $pivotData ? [
                    'link_type' => $pivotData->link_type,
                    'distance_km' => $pivotData->distance_km,
                    'time_diff_minutes' => $pivotData->time_diff_minutes,
                    'linked_at' => $pivotData->linked_at,
                ] : null,
                'shadow_analysis' => [
                    'suitable' => $weatherData->isSuitableForShadowAnalysis(),
                    'expected_shadow_angle' => $weatherData->getExpectedShadowAngle(),
                    'shadow_length_ratio' => $weatherData->getShadowLengthRatio(),
                    'verified' => $pivotData?->shadow_verified ?? false,
                    'verification_result' => $pivotData?->shadow_verified ? [
                        'matches' => $pivotData->shadow_matches,
                        'claimed_angle' => $pivotData->claimed_shadow_angle,
                        'calculated_angle' => $pivotData->calculated_shadow_angle,
                        'deviation' => $pivotData->shadow_angle_deviation,
                        'notes' => $pivotData->verification_notes,
                    ] : null,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Weather fetch for event failed', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);

            return $this->error(
                'Failed to fetch weather data for event: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Verify shadow angle against expected sun position
     *
     * Used for chronolocation verification.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function verifyShadow(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'datetime' => ['required', 'date'],
            'shadow_angle' => ['required', 'numeric', 'between:0,360'],
            'tolerance' => ['nullable', 'numeric', 'between:1,45'],
            'event_uuid' => ['nullable', 'string', 'exists:events,uuid'],
        ]);

        try {
            $datetime = Carbon::parse($validated['datetime']);
            $tolerance = $validated['tolerance'] ?? 15.0;

            $verification = $this->weatherService->verifyShadowAngle(
                (float) $validated['shadow_angle'],
                (float) $validated['latitude'],
                (float) $validated['longitude'],
                $datetime,
                $tolerance
            );

            $response = [
                'verification' => $verification,
                'input' => [
                    'latitude' => $validated['latitude'],
                    'longitude' => $validated['longitude'],
                    'datetime' => $datetime->toIso8601String(),
                    'shadow_angle' => $validated['shadow_angle'],
                    'tolerance' => $tolerance,
                ],
            ];

            // If event UUID provided, also link the verification
            if (isset($validated['event_uuid'])) {
                $event = Event::where('uuid', $validated['event_uuid'])->first();

                if ($event && $request->user()) {
                    // Fetch or get weather data
                    $weatherData = $this->weatherService->fetchHistoricalWeather(
                        (float) $validated['latitude'],
                        (float) $validated['longitude'],
                        $datetime,
                        null,
                        $request->user()
                    );

                    // Link with verification
                    $this->weatherService->linkWeatherToEvent(
                        $event,
                        $weatherData,
                        (float) $validated['shadow_angle'],
                        $request->user(),
                        $verification['analysis']
                    );

                    $response['event_linked'] = true;
                    $response['event_uuid'] = $event->uuid;
                }
            }

            return $this->success($response);
        } catch (\Exception $e) {
            Log::error('Shadow verification failed', [
                'error' => $e->getMessage(),
            ]);

            return $this->error(
                'Shadow verification failed: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Get sun position for chronolocation
     *
     * Returns sun position (azimuth, elevation) for a given location and time.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getSunPosition(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'datetime' => ['nullable', 'date'],
        ]);

        $datetime = isset($validated['datetime'])
            ? Carbon::parse($validated['datetime'])
            : now();

        $sunPosition = $this->weatherService->calculateSunPosition(
            (float) $validated['latitude'],
            (float) $validated['longitude'],
            $datetime
        );

        $expectedShadowAngle = null;
        $shadowLengthRatio = null;

        if ($sunPosition['elevation'] > 0) {
            $expectedShadowAngle = fmod($sunPosition['azimuth'] + 180, 360);
            $shadowLengthRatio = 1 / tan(deg2rad($sunPosition['elevation']));
        }

        return $this->success([
            'sun_position' => [
                'azimuth' => round($sunPosition['azimuth'], 2),
                'elevation' => round($sunPosition['elevation'], 2),
                'is_daylight' => $sunPosition['is_daylight'],
            ],
            'sun_times' => [
                'sunrise' => $sunPosition['sunrise'],
                'sunset' => $sunPosition['sunset'],
                'solar_noon' => $sunPosition['solar_noon'],
            ],
            'shadow' => [
                'expected_angle' => $expectedShadowAngle !== null ? round($expectedShadowAngle, 2) : null,
                'length_ratio' => $shadowLengthRatio !== null ? round($shadowLengthRatio, 4) : null,
                'direction' => $expectedShadowAngle !== null ? $this->getCardinalDirection($expectedShadowAngle) : null,
            ],
            'input' => [
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'datetime' => $datetime->toIso8601String(),
            ],
        ]);
    }

    /**
     * Find times when shadow matches a given angle
     *
     * Useful for determining when a photo was taken based on shadow analysis.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function findMatchingTimes(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'shadow_angle' => ['required', 'numeric', 'between:0,360'],
            'date' => ['required', 'date'],
            'tolerance' => ['nullable', 'numeric', 'between:1,45'],
        ]);

        $date = Carbon::parse($validated['date']);
        $tolerance = $validated['tolerance'] ?? 15.0;

        $results = $this->weatherService->findMatchingTimes(
            (float) $validated['shadow_angle'],
            (float) $validated['latitude'],
            (float) $validated['longitude'],
            $date,
            $tolerance
        );

        return $this->success($results);
    }

    /**
     * List weather data records with filtering
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'radius_km' => ['nullable', 'numeric', 'min:1', 'max:500'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'condition' => ['nullable', 'string'],
            'source' => ['nullable', 'string'],
            'type' => ['nullable', 'string', Rule::in(['current', 'historical', 'forecast'])],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = WeatherData::query();

        // Filter by location radius
        if (isset($validated['latitude'], $validated['longitude'])) {
            $radiusKm = $validated['radius_km'] ?? 50;
            $query->nearLocation(
                (float) $validated['latitude'],
                (float) $validated['longitude'],
                $radiusKm
            );
        }

        // Filter by date range
        if (isset($validated['from'])) {
            $query->where('recorded_at', '>=', Carbon::parse($validated['from']));
        }
        if (isset($validated['to'])) {
            $query->where('recorded_at', '<=', Carbon::parse($validated['to']));
        }

        // Filter by condition
        if (isset($validated['condition'])) {
            $query->withCondition($validated['condition']);
        }

        // Filter by source
        if (isset($validated['source'])) {
            $query->fromSource($validated['source']);
        }

        // Filter by type
        if (isset($validated['type'])) {
            $query->ofType($validated['type']);
        }

        // Order by most recent
        $query->orderBy('recorded_at', 'desc');

        $perPage = $validated['per_page'] ?? $this->perPage;
        $weatherData = $query->paginate($perPage);

        return $this->paginated($weatherData);
    }

    /**
     * Get a specific weather data record
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function show(string $uuid): JsonResponse
    {
        $weatherData = WeatherData::where('uuid', $uuid)->firstOrFail();

        return $this->success([
            'weather' => $this->formatWeatherResponse($weatherData),
            'sun_position' => $weatherData->getSunPosition(),
            'shadow_analysis' => [
                'suitable' => $weatherData->isSuitableForShadowAnalysis(),
                'expected_shadow_angle' => $weatherData->getExpectedShadowAngle(),
                'shadow_length_ratio' => $weatherData->getShadowLengthRatio(),
            ],
            'linked_events' => $weatherData->events->map(fn($event) => [
                'uuid' => $event->uuid,
                'title' => $event->title,
                'occurred_at' => $event->occurred_at->toIso8601String(),
                'link_type' => $event->pivot->link_type,
            ]),
        ]);
    }

    /**
     * Get available weather providers and their status
     *
     * @return JsonResponse
     */
    public function providers(): JsonResponse
    {
        return $this->success([
            'providers' => $this->weatherService->getAvailableProviders(),
            'default' => config('weather.default_provider'),
        ]);
    }

    /**
     * Get weather conditions enum values
     *
     * @return JsonResponse
     */
    public function conditions(): JsonResponse
    {
        return $this->success([
            'conditions' => WeatherData::getConditions(),
            'precipitation_types' => [
                WeatherData::PRECIPITATION_NONE,
                WeatherData::PRECIPITATION_RAIN,
                WeatherData::PRECIPITATION_DRIZZLE,
                WeatherData::PRECIPITATION_SNOW,
                WeatherData::PRECIPITATION_SLEET,
                WeatherData::PRECIPITATION_HAIL,
                WeatherData::PRECIPITATION_FREEZING_RAIN,
            ],
        ]);
    }

    /**
     * Link weather data to an event manually
     *
     * @param Request $request
     * @param string $eventUuid
     * @return JsonResponse
     */
    public function linkToEvent(Request $request, string $eventUuid): JsonResponse
    {
        $event = Event::where('uuid', $eventUuid)->firstOrFail();

        $this->authorize('update', $event);

        $validated = $request->validate([
            'weather_uuid' => ['required', 'exists:weather_data,uuid'],
            'shadow_angle' => ['nullable', 'numeric', 'between:0,360'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $weatherData = WeatherData::where('uuid', $validated['weather_uuid'])->firstOrFail();

        $linkData = $this->weatherService->linkWeatherToEvent(
            $event,
            $weatherData,
            $validated['shadow_angle'] ?? null,
            $request->user(),
            $validated['notes'] ?? null
        );

        return $this->success([
            'message' => 'Weather data linked to event successfully',
            'link_info' => $linkData,
            'shadow_verification' => isset($validated['shadow_angle']) ? [
                'verified' => true,
                'matches' => $linkData['shadow_matches'],
                'deviation' => $linkData['shadow_angle_deviation'],
            ] : null,
        ]);
    }

    /**
     * Unlink weather data from an event
     *
     * @param Request $request
     * @param string $eventUuid
     * @param string $weatherUuid
     * @return JsonResponse
     */
    public function unlinkFromEvent(Request $request, string $eventUuid, string $weatherUuid): JsonResponse
    {
        $event = Event::where('uuid', $eventUuid)->firstOrFail();

        $this->authorize('update', $event);

        $weatherData = WeatherData::where('uuid', $weatherUuid)->firstOrFail();

        $event->weatherData()->detach($weatherData->id);

        return $this->success([
            'message' => 'Weather data unlinked from event successfully',
        ]);
    }

    /**
     * Format weather data for API response
     */
    private function formatWeatherResponse(WeatherData $weatherData): array
    {
        return [
            'uuid' => $weatherData->uuid,
            'location' => [
                'latitude' => $weatherData->latitude,
                'longitude' => $weatherData->longitude,
                'name' => $weatherData->location_name,
            ],
            'recorded_at' => $weatherData->recorded_at->toIso8601String(),
            'temperature' => [
                'current' => $weatherData->temperature,
                'feels_like' => $weatherData->feels_like,
                'min' => $weatherData->temperature_min,
                'max' => $weatherData->temperature_max,
                'unit' => 'celsius',
            ],
            'humidity' => $weatherData->humidity,
            'wind' => [
                'speed' => $weatherData->wind_speed,
                'direction' => $weatherData->wind_direction,
                'direction_cardinal' => $weatherData->getWindDirectionCardinal(),
                'gust' => $weatherData->wind_gust,
                'unit' => 'm/s',
            ],
            'visibility' => [
                'value' => $weatherData->visibility,
                'category' => $weatherData->getVisibilityCategory(),
                'unit' => 'meters',
            ],
            'clouds' => [
                'cover' => $weatherData->cloud_cover,
                'unit' => 'percent',
            ],
            'precipitation' => [
                'type' => $weatherData->precipitation_type,
                'amount' => $weatherData->precipitation_amount,
                'snow_depth' => $weatherData->snow_depth,
                'unit' => 'mm',
            ],
            'pressure' => [
                'value' => $weatherData->pressure,
                'sea_level' => $weatherData->pressure_sea_level,
                'ground_level' => $weatherData->pressure_ground_level,
                'unit' => 'hPa',
            ],
            'uv' => [
                'index' => $weatherData->uv_index,
                'category' => $weatherData->getUvCategory(),
            ],
            'sun' => [
                'sunrise' => $weatherData->sunrise,
                'sunset' => $weatherData->sunset,
                'azimuth' => $weatherData->sun_azimuth,
                'elevation' => $weatherData->sun_elevation,
            ],
            'condition' => [
                'type' => $weatherData->weather_condition,
                'description' => $weatherData->weather_description,
                'icon' => $weatherData->weather_icon,
            ],
            'data_source' => $weatherData->data_source,
            'data_type' => $weatherData->data_type,
            'fetched_at' => $weatherData->fetched_at?->toIso8601String(),
        ];
    }

    /**
     * Get cardinal direction from angle
     */
    private function getCardinalDirection(float $angle): string
    {
        $directions = ['N', 'NNE', 'NE', 'ENE', 'E', 'ESE', 'SE', 'SSE', 'S', 'SSW', 'SW', 'WSW', 'W', 'WNW', 'NW', 'NNW'];
        $index = (int) round($angle / 22.5) % 16;

        return $directions[$index];
    }
}
