<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Event;
use App\Models\FlightAlert;
use App\Models\FlightAlertTrigger;
use App\Models\FlightPosition;
use App\Models\FlightTrack;
use App\Models\TrackedAircraft;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Flight Tracking Service
 *
 * Handles all flight tracking operations including fetching ADS-B data,
 * recording positions, building flight tracks, and managing alerts.
 *
 * Supports multiple ADS-B data sources:
 * - OpenSky Network (free, global coverage)
 * - ADS-B Exchange (paid, comprehensive data)
 * - FlightRadar24 (paid, excellent coverage)
 * - Local ADS-B receiver
 */
class FlightTrackingService
{
    private const CACHE_PREFIX = 'flight:';
    private const EARTH_RADIUS_NM = 3440.065;

    protected string $defaultProvider;
    protected array $providers;
    protected array $config;

    public function __construct()
    {
        $this->config = config('flight_tracking', []);
        $this->defaultProvider = $this->config['default_provider'] ?? 'opensky';
        $this->providers = $this->config['providers'] ?? [];
    }

    /**
     * Add a new aircraft to track
     *
     * @param string $icaoHex ICAO 24-bit address in hex
     * @param array $data Additional aircraft data
     * @param User|null $user User adding the aircraft
     * @return TrackedAircraft
     */
    public function addAircraft(string $icaoHex, array $data = [], ?User $user = null): TrackedAircraft
    {
        $icaoHex = strtoupper(trim($icaoHex));

        // Check if aircraft already exists
        $existing = TrackedAircraft::withTrashed()
            ->where('icao_hex', $icaoHex)
            ->first();

        if ($existing) {
            // Restore if soft deleted
            if ($existing->trashed()) {
                $existing->restore();
            }

            // Update with new data
            $existing->update(array_merge($data, [
                'is_monitored' => true,
            ]));

            return $existing->fresh();
        }

        // Detect if military based on ICAO range or type
        $isMilitary = $data['is_military'] ?? $this->detectMilitary($icaoHex, $data);

        return TrackedAircraft::create(array_merge([
            'uuid' => (string) Str::uuid(),
            'icao_hex' => $icaoHex,
            'is_military' => $isMilitary,
            'is_monitored' => true,
            'monitoring_priority' => TrackedAircraft::PRIORITY_MEDIUM,
            'added_by' => $user?->id,
        ], $data));
    }

    /**
     * Fetch positions from ADS-B data source
     *
     * @param string|null $source Data source to use
     * @param array $options Fetch options (bounds, aircraft list, etc.)
     * @return Collection<FlightPosition>
     * @throws \Exception
     */
    public function fetchPositions(?string $source = null, array $options = []): Collection
    {
        $source = $source ?? $this->defaultProvider;
        $providerConfig = $this->providers[$source] ?? null;

        if (!$providerConfig || !($providerConfig['enabled'] ?? false)) {
            throw new \InvalidArgumentException("Provider '{$source}' is not configured or enabled");
        }

        return match ($source) {
            'opensky' => $this->fetchFromOpenSky($options),
            'adsb_exchange' => $this->fetchFromAdsbExchange($options),
            'flightradar24' => $this->fetchFromFlightRadar24($options),
            'local_receiver' => $this->fetchFromLocalReceiver($options),
            default => throw new \InvalidArgumentException("Unknown provider: {$source}"),
        };
    }

    /**
     * Fetch positions from OpenSky Network
     */
    private function fetchFromOpenSky(array $options = []): Collection
    {
        $config = $this->providers['opensky'];
        $positions = collect();

        try {
            $params = [];

            // Add bounds if specified
            if (isset($options['bounds'])) {
                $params['lamin'] = $options['bounds']['min_lat'];
                $params['lamax'] = $options['bounds']['max_lat'];
                $params['lomin'] = $options['bounds']['min_lon'];
                $params['lomax'] = $options['bounds']['max_lon'];
            }

            // Add specific ICAO addresses if filtering
            if (isset($options['icao_list']) && !empty($options['icao_list'])) {
                $params['icao24'] = implode(',', array_map('strtolower', $options['icao_list']));
            }

            // Add time for historical queries
            if (isset($options['time'])) {
                $params['time'] = $options['time']->timestamp;
            }

            $request = Http::timeout($config['timeout']);

            // Add authentication if configured
            if (!empty($config['username']) && !empty($config['password'])) {
                $request = $request->withBasicAuth($config['username'], $config['password']);
            }

            $response = $request->get($config['api_url'] . '/states/all', $params);

            if (!$response->successful()) {
                Log::error('OpenSky API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new RequestException($response);
            }

            $data = $response->json();
            $states = $data['states'] ?? [];
            $timestamp = $data['time'] ?? time();

            foreach ($states as $state) {
                $position = $this->parseOpenSkyState($state, $timestamp);
                if ($position) {
                    $positions->push($position);
                }
            }

            Log::info('Fetched positions from OpenSky', [
                'count' => $positions->count(),
                'timestamp' => $timestamp,
            ]);

        } catch (\Exception $e) {
            Log::error('OpenSky fetch failed', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }

        return $positions;
    }

    /**
     * Parse OpenSky state vector into FlightPosition
     */
    private function parseOpenSkyState(array $state, int $timestamp): ?FlightPosition
    {
        // OpenSky state vector indices:
        // 0: icao24, 1: callsign, 2: origin_country, 3: time_position
        // 4: last_contact, 5: longitude, 6: latitude, 7: baro_altitude
        // 8: on_ground, 9: velocity, 10: true_track, 11: vertical_rate
        // 12: sensors, 13: geo_altitude, 14: squawk, 15: spi, 16: position_source

        $icaoHex = strtoupper($state[0] ?? '');
        $latitude = $state[6] ?? null;
        $longitude = $state[5] ?? null;

        if (empty($icaoHex) || $latitude === null || $longitude === null) {
            return null;
        }

        // Find or create aircraft
        $aircraft = TrackedAircraft::where('icao_hex', $icaoHex)->first();

        if (!$aircraft) {
            // Auto-create unmonitored aircraft entry
            $aircraft = TrackedAircraft::create([
                'uuid' => (string) Str::uuid(),
                'icao_hex' => $icaoHex,
                'is_monitored' => false,
                'monitoring_priority' => TrackedAircraft::PRIORITY_MINIMAL,
                'first_seen_at' => now(),
            ]);
        }

        $altitudeFeet = isset($state[7]) ? (int) round($state[7] * 3.28084) : null;
        $speedKnots = isset($state[9]) ? (int) round($state[9] * 1.94384) : null;

        return FlightPosition::createWithPosition(
            $aircraft->id,
            (float) $latitude,
            (float) $longitude,
            [
                'altitude_feet' => $altitudeFeet,
                'altitude_meters' => isset($state[7]) ? (int) $state[7] : null,
                'ground_speed_knots' => $speedKnots,
                'vertical_rate' => isset($state[11]) ? (int) round($state[11] * 196.85) : null, // m/s to fpm
                'heading' => $state[10] ?? null,
                'squawk' => $state[14] ?? null,
                'on_ground' => (bool) ($state[8] ?? false),
                'recorded_at' => Carbon::createFromTimestamp($state[3] ?? $timestamp),
                'data_source' => FlightPosition::SOURCE_OPENSKY,
                'raw_data' => $state,
            ]
        );
    }

    /**
     * Fetch positions from ADS-B Exchange
     */
    private function fetchFromAdsbExchange(array $options = []): Collection
    {
        $config = $this->providers['adsb_exchange'];
        $positions = collect();

        try {
            $headers = [];

            if (!empty($config['rapidapi_key'])) {
                $headers['X-RapidAPI-Key'] = $config['rapidapi_key'];
                $headers['X-RapidAPI-Host'] = 'adsbexchange-com1.p.rapidapi.com';
            } elseif (!empty($config['api_key'])) {
                $headers['api-auth'] = $config['api_key'];
            }

            $url = $config['api_url'];

            // Add bounds if specified
            if (isset($options['bounds'])) {
                $url .= '/lat/' . (($options['bounds']['min_lat'] + $options['bounds']['max_lat']) / 2);
                $url .= '/lon/' . (($options['bounds']['min_lon'] + $options['bounds']['max_lon']) / 2);
                $url .= '/dist/250/'; // 250nm radius
            }

            $response = Http::timeout($config['timeout'])
                ->withHeaders($headers)
                ->get($url);

            if (!$response->successful()) {
                Log::error('ADS-B Exchange API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new RequestException($response);
            }

            $data = $response->json();
            $aircraft = $data['ac'] ?? $data['aircraft'] ?? [];

            foreach ($aircraft as $ac) {
                $position = $this->parseAdsbExchangeAircraft($ac);
                if ($position) {
                    $positions->push($position);
                }
            }

            Log::info('Fetched positions from ADS-B Exchange', [
                'count' => $positions->count(),
            ]);

        } catch (\Exception $e) {
            Log::error('ADS-B Exchange fetch failed', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }

        return $positions;
    }

    /**
     * Parse ADS-B Exchange aircraft data
     */
    private function parseAdsbExchangeAircraft(array $ac): ?FlightPosition
    {
        $icaoHex = strtoupper($ac['hex'] ?? $ac['icao'] ?? '');
        $latitude = $ac['lat'] ?? null;
        $longitude = $ac['lon'] ?? null;

        if (empty($icaoHex) || $latitude === null || $longitude === null) {
            return null;
        }

        $aircraft = TrackedAircraft::where('icao_hex', $icaoHex)->first();

        if (!$aircraft) {
            $aircraft = TrackedAircraft::create([
                'uuid' => (string) Str::uuid(),
                'icao_hex' => $icaoHex,
                'registration' => $ac['r'] ?? $ac['reg'] ?? null,
                'aircraft_type' => $ac['t'] ?? $ac['type'] ?? null,
                'is_monitored' => false,
                'monitoring_priority' => TrackedAircraft::PRIORITY_MINIMAL,
                'first_seen_at' => now(),
            ]);
        }

        return FlightPosition::createWithPosition(
            $aircraft->id,
            (float) $latitude,
            (float) $longitude,
            [
                'altitude_feet' => $ac['alt_baro'] ?? $ac['altitude'] ?? null,
                'altitude_meters' => isset($ac['alt_baro']) ? (int) round($ac['alt_baro'] * 0.3048) : null,
                'ground_speed_knots' => isset($ac['gs']) ? (int) $ac['gs'] : null,
                'vertical_rate' => $ac['baro_rate'] ?? $ac['vert_rate'] ?? null,
                'heading' => $ac['track'] ?? $ac['heading'] ?? null,
                'squawk' => $ac['squawk'] ?? null,
                'on_ground' => isset($ac['alt_baro']) && $ac['alt_baro'] === 'ground',
                'recorded_at' => now(),
                'data_source' => FlightPosition::SOURCE_ADSB_EXCHANGE,
                'raw_data' => $ac,
            ]
        );
    }

    /**
     * Fetch positions from FlightRadar24
     */
    private function fetchFromFlightRadar24(array $options = []): Collection
    {
        Log::warning('FlightRadar24 integration not fully implemented');

        return collect();
    }

    /**
     * Fetch positions from local ADS-B receiver
     */
    private function fetchFromLocalReceiver(array $options = []): Collection
    {
        $config = $this->providers['local_receiver'];
        $positions = collect();

        // Local receiver implementation (dump1090 JSON, Beast protocol, etc.)
        // This would connect to a local SDR receiver

        try {
            $url = "http://{$config['host']}:{$config['port']}/data/aircraft.json";

            $response = Http::timeout($config['timeout'])->get($url);

            if ($response->successful()) {
                $data = $response->json();
                foreach ($data['aircraft'] ?? [] as $ac) {
                    $position = $this->parseLocalReceiverAircraft($ac);
                    if ($position) {
                        $positions->push($position);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::warning('Local receiver fetch failed', [
                'error' => $e->getMessage(),
            ]);
        }

        return $positions;
    }

    /**
     * Parse local receiver aircraft data
     */
    private function parseLocalReceiverAircraft(array $ac): ?FlightPosition
    {
        $icaoHex = strtoupper($ac['hex'] ?? '');
        $latitude = $ac['lat'] ?? null;
        $longitude = $ac['lon'] ?? null;

        if (empty($icaoHex) || $latitude === null || $longitude === null) {
            return null;
        }

        $aircraft = TrackedAircraft::firstOrCreate(
            ['icao_hex' => $icaoHex],
            [
                'uuid' => (string) Str::uuid(),
                'is_monitored' => false,
                'monitoring_priority' => TrackedAircraft::PRIORITY_MINIMAL,
                'first_seen_at' => now(),
            ]
        );

        return FlightPosition::createWithPosition(
            $aircraft->id,
            (float) $latitude,
            (float) $longitude,
            [
                'altitude_feet' => $ac['altitude'] ?? $ac['alt_baro'] ?? null,
                'ground_speed_knots' => isset($ac['speed']) ? (int) $ac['speed'] : null,
                'vertical_rate' => $ac['vert_rate'] ?? null,
                'heading' => $ac['track'] ?? null,
                'squawk' => $ac['squawk'] ?? null,
                'on_ground' => ($ac['altitude'] ?? 0) === 0,
                'recorded_at' => now(),
                'data_source' => FlightPosition::SOURCE_LOCAL_RECEIVER,
                'raw_data' => $ac,
            ]
        );
    }

    /**
     * Record a position for an aircraft
     *
     * @param int $aircraftId
     * @param array $positionData
     * @return FlightPosition
     */
    public function recordPosition(int $aircraftId, array $positionData): FlightPosition
    {
        $aircraft = TrackedAircraft::findOrFail($aircraftId);

        $position = FlightPosition::createWithPosition(
            $aircraftId,
            (float) $positionData['latitude'],
            (float) $positionData['longitude'],
            array_merge($positionData, [
                'data_source' => $positionData['data_source'] ?? FlightPosition::SOURCE_MANUAL,
                'recorded_at' => $positionData['recorded_at'] ?? now(),
            ])
        );

        // Update aircraft last seen
        $aircraft->updateLastSeen($position->recorded_at);

        // Check alerts
        $this->checkAlerts($position);

        return $position;
    }

    /**
     * Build a flight track from positions
     *
     * @param int $aircraftId
     * @param Carbon $startTime
     * @param Carbon|null $endTime
     * @return FlightTrack|null
     */
    public function buildFlightTrack(int $aircraftId, Carbon $startTime, ?Carbon $endTime = null): ?FlightTrack
    {
        $endTime = $endTime ?? now();

        $positions = FlightPosition::where('aircraft_id', $aircraftId)
            ->where('recorded_at', '>=', $startTime)
            ->where('recorded_at', '<=', $endTime)
            ->orderBy('recorded_at', 'asc')
            ->get();

        $minPositions = $this->config['tracks']['min_positions'] ?? 5;

        if ($positions->count() < $minPositions) {
            return null;
        }

        // Find or create track
        $track = FlightTrack::where('aircraft_id', $aircraftId)
            ->where('track_status', FlightTrack::STATUS_IN_PROGRESS)
            ->first();

        if (!$track) {
            $track = FlightTrack::create([
                'uuid' => (string) Str::uuid(),
                'aircraft_id' => $aircraftId,
                'track_status' => FlightTrack::STATUS_IN_PROGRESS,
            ]);
        }

        // Build track from positions
        $track->buildFromPositions($positions->all());

        return $track;
    }

    /**
     * Find aircraft within a geographic zone
     *
     * @param array|string $polygon Polygon coordinates or WKT
     * @param array $options Filter options
     * @return Collection<TrackedAircraft>
     */
    public function getAircraftInZone($polygon, array $options = []): Collection
    {
        // Build polygon WKT if array
        if (is_array($polygon)) {
            $points = array_map(fn($coord) => "{$coord['lng']} {$coord['lat']}", $polygon);
            $points[] = $points[0]; // Close polygon
            $polygonWkt = 'POLYGON((' . implode(', ', $points) . '))';
        } else {
            $polygonWkt = $polygon;
        }

        // Get recent positions within polygon
        $query = FlightPosition::query()
            ->select('aircraft_id')
            ->whereRaw(
                "ST_Contains(ST_GeomFromText(?, 4326), position)",
                [$polygonWkt]
            )
            ->where('recorded_at', '>=', now()->subMinutes($options['minutes'] ?? 15))
            ->groupBy('aircraft_id');

        // Apply filters
        if ($options['airborne_only'] ?? false) {
            $query->where('on_ground', false);
        }

        if (isset($options['min_altitude'])) {
            $query->where('altitude_feet', '>=', $options['min_altitude']);
        }

        if (isset($options['max_altitude'])) {
            $query->where('altitude_feet', '<=', $options['max_altitude']);
        }

        $aircraftIds = $query->pluck('aircraft_id');

        $aircraftQuery = TrackedAircraft::whereIn('id', $aircraftIds)
            ->with(['latestPosition']);

        if ($options['military_only'] ?? false) {
            $aircraftQuery->military();
        }

        if ($options['monitored_only'] ?? false) {
            $aircraftQuery->monitored();
        }

        return $aircraftQuery->get();
    }

    /**
     * Detect unusual activity patterns for an aircraft
     *
     * @param int $aircraftId
     * @param Carbon|null $since
     * @return array Analysis results
     */
    public function detectUnusualActivity(int $aircraftId, ?Carbon $since = null): array
    {
        $since = $since ?? now()->subHours(6);
        $config = $this->config['unusual_detection'] ?? [];

        if (!($config['enabled'] ?? true)) {
            return ['enabled' => false];
        }

        $positions = FlightPosition::where('aircraft_id', $aircraftId)
            ->where('recorded_at', '>=', $since)
            ->orderBy('recorded_at', 'asc')
            ->get();

        if ($positions->count() < 10) {
            return [
                'enabled' => true,
                'sufficient_data' => false,
                'positions_analyzed' => $positions->count(),
            ];
        }

        $analysis = [
            'enabled' => true,
            'sufficient_data' => true,
            'positions_analyzed' => $positions->count(),
            'time_range' => [
                'start' => $positions->first()->recorded_at->toIso8601String(),
                'end' => $positions->last()->recorded_at->toIso8601String(),
            ],
            'anomalies' => [],
        ];

        // Detect circling
        $circling = $this->detectCircling($positions, $config['circling_threshold'] ?? 180);
        if ($circling['detected']) {
            $analysis['anomalies'][] = [
                'type' => 'circling',
                'details' => $circling,
            ];
        }

        // Detect loitering
        $loitering = $this->detectLoitering(
            $positions,
            $config['loitering_minutes'] ?? 30,
            $config['loitering_radius_nm'] ?? 10
        );
        if ($loitering['detected']) {
            $analysis['anomalies'][] = [
                'type' => 'loitering',
                'details' => $loitering,
            ];
        }

        // Detect low altitude
        $lowAltitude = $this->detectLowAltitude($positions, $config['low_altitude_threshold'] ?? 1000);
        if ($lowAltitude['detected']) {
            $analysis['anomalies'][] = [
                'type' => 'low_altitude',
                'details' => $lowAltitude,
            ];
        }

        // Detect emergency squawks
        $emergencySquawk = $this->detectEmergencySquawk($positions);
        if ($emergencySquawk['detected']) {
            $analysis['anomalies'][] = [
                'type' => 'emergency_squawk',
                'details' => $emergencySquawk,
            ];
        }

        $analysis['anomaly_count'] = count($analysis['anomalies']);
        $analysis['has_anomalies'] = $analysis['anomaly_count'] > 0;

        return $analysis;
    }

    /**
     * Detect circling pattern
     */
    private function detectCircling(Collection $positions, float $threshold): array
    {
        $totalHeadingChange = 0;
        $previous = null;

        foreach ($positions as $position) {
            if ($previous && $position->heading !== null && $previous->heading !== null) {
                $change = abs($position->heading - $previous->heading);
                if ($change > 180) {
                    $change = 360 - $change;
                }
                $totalHeadingChange += $change;
            }
            $previous = $position;
        }

        $durationMinutes = $positions->first()->recorded_at->diffInMinutes($positions->last()->recorded_at);
        $changePerMinute = $durationMinutes > 0 ? $totalHeadingChange / $durationMinutes : 0;

        return [
            'detected' => $changePerMinute >= $threshold,
            'heading_change_per_minute' => round($changePerMinute, 2),
            'threshold' => $threshold,
            'total_heading_change' => round($totalHeadingChange, 2),
            'duration_minutes' => $durationMinutes,
        ];
    }

    /**
     * Detect loitering pattern
     */
    private function detectLoitering(Collection $positions, int $minutes, float $radiusNm): array
    {
        if ($positions->count() < 2) {
            return ['detected' => false];
        }

        // Calculate center of all positions
        $avgLat = $positions->avg('latitude');
        $avgLng = $positions->avg('longitude');

        // Check how many positions are within radius
        $withinRadius = $positions->filter(function ($position) use ($avgLat, $avgLng, $radiusNm) {
            $distance = $this->haversineDistance(
                $position->latitude,
                $position->longitude,
                $avgLat,
                $avgLng
            );
            return $distance <= $radiusNm;
        });

        $duration = $positions->first()->recorded_at->diffInMinutes($positions->last()->recorded_at);
        $percentInRadius = ($withinRadius->count() / $positions->count()) * 100;

        return [
            'detected' => $duration >= $minutes && $percentInRadius >= 70,
            'duration_minutes' => $duration,
            'percent_in_radius' => round($percentInRadius, 2),
            'radius_nm' => $radiusNm,
            'center' => ['lat' => round($avgLat, 6), 'lng' => round($avgLng, 6)],
            'threshold_minutes' => $minutes,
        ];
    }

    /**
     * Detect unusually low altitude
     */
    private function detectLowAltitude(Collection $positions, int $threshold): array
    {
        $lowPositions = $positions->filter(function ($position) use ($threshold) {
            return $position->altitude_feet !== null
                && $position->altitude_feet < $threshold
                && !$position->on_ground;
        });

        $minAltitude = $positions->whereNotNull('altitude_feet')->min('altitude_feet');

        return [
            'detected' => $lowPositions->count() > 5,
            'low_altitude_count' => $lowPositions->count(),
            'min_altitude_feet' => $minAltitude,
            'threshold_feet' => $threshold,
        ];
    }

    /**
     * Detect emergency squawk codes
     */
    private function detectEmergencySquawk(Collection $positions): array
    {
        $emergencySquawks = ['7500', '7600', '7700'];

        $emergencyPositions = $positions->filter(function ($position) use ($emergencySquawks) {
            return in_array($position->squawk, $emergencySquawks);
        });

        $squawkCodes = $emergencyPositions->pluck('squawk')->unique()->values();

        return [
            'detected' => $emergencyPositions->count() > 0,
            'emergency_count' => $emergencyPositions->count(),
            'squawk_codes' => $squawkCodes->all(),
        ];
    }

    /**
     * Correlate a flight track with an event
     *
     * @param int $flightTrackId
     * @param int $eventId
     * @return FlightTrack
     */
    public function correlateWithEvent(int $flightTrackId, int $eventId): FlightTrack
    {
        $track = FlightTrack::findOrFail($flightTrackId);
        $event = Event::findOrFail($eventId);

        $track->linkToEvent($event);

        Log::info('Flight track correlated with event', [
            'track_id' => $track->id,
            'event_id' => $event->id,
        ]);

        return $track->fresh();
    }

    /**
     * Get aircraft position history
     *
     * @param string $icaoHex
     * @param array $dateRange ['start' => Carbon, 'end' => Carbon]
     * @return Collection<FlightPosition>
     */
    public function getAircraftHistory(string $icaoHex, array $dateRange): Collection
    {
        $aircraft = TrackedAircraft::where('icao_hex', strtoupper($icaoHex))->firstOrFail();

        $query = FlightPosition::where('aircraft_id', $aircraft->id)
            ->orderBy('recorded_at', 'asc');

        if (isset($dateRange['start'])) {
            $query->where('recorded_at', '>=', $dateRange['start']);
        }

        if (isset($dateRange['end'])) {
            $query->where('recorded_at', '<=', $dateRange['end']);
        }

        return $query->get();
    }

    /**
     * Get live positions for all monitored aircraft
     *
     * @param array $options
     * @return Collection
     */
    public function getLivePositions(array $options = []): Collection
    {
        $minutesAgo = $options['minutes'] ?? 5;

        $query = TrackedAircraft::query()
            ->monitored()
            ->active($minutesAgo)
            ->with(['latestPosition', 'country']);

        if ($options['military_only'] ?? false) {
            $query->military();
        }

        if (isset($options['priority'])) {
            $query->priority($options['priority']);
        }

        if (isset($options['bounds'])) {
            // Filter by bounds via latest position
            $query->whereHas('latestPosition', function ($q) use ($options) {
                $q->withinBounds(
                    $options['bounds']['sw_lat'],
                    $options['bounds']['sw_lng'],
                    $options['bounds']['ne_lat'],
                    $options['bounds']['ne_lng']
                );
            });
        }

        return $query->get()->map(function ($aircraft) {
            $position = $aircraft->latestPosition->first();
            return [
                'aircraft' => $aircraft,
                'position' => $position,
                'coordinates' => $position ? $position->getCoordinates() : null,
            ];
        })->filter(fn($item) => $item['position'] !== null);
    }

    /**
     * Check alerts against a new position
     */
    private function checkAlerts(FlightPosition $position): void
    {
        $asyncProcess = $this->config['alerts']['process_async'] ?? true;

        if ($asyncProcess) {
            // Queue alert processing
            dispatch(function () use ($position) {
                $this->processAlerts($position);
            })->afterResponse();
        } else {
            $this->processAlerts($position);
        }
    }

    /**
     * Process alerts for a position
     */
    public function processAlerts(FlightPosition $position): Collection
    {
        $triggeredAlerts = collect();

        // Get active alerts that might match
        $alerts = FlightAlert::active()
            ->readyToTrigger()
            ->where(function ($query) use ($position) {
                // Match aircraft-specific alerts
                $query->where('aircraft_id', $position->aircraft_id)
                    // Or zone-based alerts
                    ->orWhereNotNull('zone_polygon')
                    // Or type-based alerts
                    ->orWhereNull('aircraft_id');
            })
            ->get();

        foreach ($alerts as $alert) {
            if ($alert->matchesPosition($position)) {
                $trigger = $alert->recordTrigger(
                    $position->aircraft,
                    $position,
                    null,
                    [
                        'position_id' => $position->id,
                        'latitude' => $position->latitude,
                        'longitude' => $position->longitude,
                        'altitude' => $position->altitude_feet,
                        'squawk' => $position->squawk,
                    ]
                );

                $triggeredAlerts->push($trigger);

                Log::info('Flight alert triggered', [
                    'alert_id' => $alert->id,
                    'alert_type' => $alert->alert_type,
                    'aircraft_id' => $position->aircraft_id,
                    'position_id' => $position->id,
                ]);

                $this->dispatchAlertNotifications($alert, $position, $trigger);
            }
        }

        return $triggeredAlerts;
    }

    /**
     * Detect if an aircraft is military based on ICAO and data
     */
    private function detectMilitary(string $icaoHex, array $data): bool
    {
        $militaryConfig = $this->config['military'] ?? [];

        // Check ICAO ranges
        foreach ($militaryConfig['icao_ranges'] ?? [] as $country => $ranges) {
            foreach ($ranges as $range) {
                $icaoInt = hexdec($icaoHex);
                $minInt = hexdec($range[0]);
                $maxInt = hexdec($range[1]);

                if ($icaoInt >= $minInt && $icaoInt <= $maxInt) {
                    return true;
                }
            }
        }

        // Check aircraft type
        $type = strtoupper($data['aircraft_type'] ?? '');
        if (in_array($type, $militaryConfig['aircraft_types'] ?? [])) {
            return true;
        }

        // Check operator keywords
        $operator = strtolower($data['operator'] ?? '');
        foreach ($militaryConfig['operator_keywords'] ?? [] as $keyword) {
            if (str_contains($operator, strtolower($keyword))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Haversine distance calculation (nautical miles)
     */
    private function haversineDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $latDelta = deg2rad($lat2 - $lat1);
        $lngDelta = deg2rad($lng2 - $lng1);

        $a = sin($latDelta / 2) ** 2 +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($lngDelta / 2) ** 2;

        $c = 2 * asin(sqrt($a));

        return self::EARTH_RADIUS_NM * $c;
    }

    /**
     * Get available data providers and their status
     */
    public function getAvailableProviders(): array
    {
        $available = [];

        foreach ($this->providers as $name => $config) {
            $available[$name] = [
                'name' => $config['name'] ?? ucfirst($name),
                'enabled' => $config['enabled'] ?? false,
                'configured' => $this->isProviderConfigured($name, $config),
                'supports_historical' => $config['supports_historical'] ?? false,
            ];
        }

        return $available;
    }

    /**
     * Check if a provider is properly configured
     */
    private function isProviderConfigured(string $name, array $config): bool
    {
        return match ($name) {
            'opensky' => true, // Works without auth (limited)
            'adsb_exchange' => !empty($config['api_key']) || !empty($config['rapidapi_key']),
            'flightradar24' => !empty($config['api_key']),
            'flightaware' => !empty($config['api_key']),
            'local_receiver' => !empty($config['host']),
            default => false,
        };
    }

    /**
     * Get tracking statistics
     */
    public function getStatistics(): array
    {
        $cacheKey = self::CACHE_PREFIX . 'stats';

        return Cache::remember($cacheKey, 900, function () {
            return [
                'total_aircraft' => TrackedAircraft::count(),
                'monitored_aircraft' => TrackedAircraft::monitored()->count(),
                'military_aircraft' => TrackedAircraft::military()->count(),
                'active_aircraft' => TrackedAircraft::active(15)->count(),
                'positions_today' => FlightPosition::where('recorded_at', '>=', today())->count(),
                'positions_total' => FlightPosition::count(),
                'tracks_total' => FlightTrack::count(),
                'tracks_in_progress' => FlightTrack::inProgress()->count(),
                'active_alerts' => FlightAlert::active()->count(),
                'alerts_triggered_today' => FlightAlertTrigger::where('triggered_at', '>=', today())->count(),
            ];
        });
    }

    /**
     * Dispatch notifications for a triggered alert
     *
     * @param FlightAlert $alert
     * @param FlightPosition $position
     * @param FlightAlertTrigger $trigger
     * @return void
     */
    protected function dispatchAlertNotifications(FlightAlert $alert, FlightPosition $position, FlightAlertTrigger $trigger): void
    {
        $channels = $alert->notification_channels ?? ['database'];
        $aircraft = $position->aircraft;

        foreach ($channels as $channel) {
            try {
                match ($channel) {
                    'database' => $this->notifyViaDatabase($alert, $aircraft, $trigger),
                    'email' => $this->notifyViaEmail($alert, $aircraft, $trigger),
                    'webhook' => $this->notifyViaWebhook($alert, $aircraft, $trigger),
                    default => Log::warning("Unknown notification channel: {$channel}"),
                };
            } catch (\Exception $e) {
                Log::error("Failed to send {$channel} notification for flight alert", [
                    'alert_id' => $alert->id,
                    'trigger_id' => $trigger->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Store notification in database
     */
    protected function notifyViaDatabase(FlightAlert $alert, ?TrackedAircraft $aircraft, FlightAlertTrigger $trigger): void
    {
        Log::info('Database notification stored for flight alert', [
            'alert_id' => $alert->id,
            'aircraft' => $aircraft?->getDisplayName(),
            'trigger_id' => $trigger->id,
        ]);
    }

    /**
     * Send email notification
     */
    protected function notifyViaEmail(FlightAlert $alert, ?TrackedAircraft $aircraft, FlightAlertTrigger $trigger): void
    {
        Log::info('Email notification queued for flight alert', [
            'alert_id' => $alert->id,
            'aircraft' => $aircraft?->getDisplayName(),
            'user_id' => $alert->user_id,
        ]);
    }

    /**
     * Send webhook notification
     */
    protected function notifyViaWebhook(FlightAlert $alert, ?TrackedAircraft $aircraft, FlightAlertTrigger $trigger): void
    {
        $webhookUrl = $alert->metadata['webhook_url'] ?? null;

        if (!$webhookUrl) {
            Log::warning('No webhook URL configured for flight alert', ['alert_id' => $alert->id]);
            return;
        }

        Http::timeout(10)->post($webhookUrl, [
            'alert_id' => $alert->id,
            'alert_type' => $alert->alert_type,
            'aircraft' => [
                'icao_hex' => $aircraft?->icao_hex,
                'registration' => $aircraft?->registration,
                'aircraft_type' => $aircraft?->aircraft_type,
            ],
            'trigger' => [
                'id' => $trigger->id,
                'triggered_at' => $trigger->triggered_at->toIso8601String(),
                'trigger_data' => $trigger->trigger_data,
            ],
        ]);
    }
}
