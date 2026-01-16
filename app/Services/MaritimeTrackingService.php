<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Event;
use App\Models\PortCall;
use App\Models\TrackedVessel;
use App\Models\User;
use App\Models\VesselAlert;
use App\Models\VesselPosition;
use App\Models\VesselTrack;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Maritime Tracking Service
 *
 * Provides methods for tracking vessels via AIS data, analyzing
 * maritime activity patterns, and detecting anomalies.
 */
class MaritimeTrackingService
{
    /**
     * Nautical miles to meters conversion factor.
     */
    private const NM_TO_METERS = 1852;

    /**
     * Default dark activity threshold in hours.
     */
    private const DEFAULT_DARK_THRESHOLD_HOURS = 4;

    /**
     * Default STS detection distance in nautical miles.
     */
    private const DEFAULT_STS_DISTANCE_NM = 0.5;

    /**
     * Add a new vessel to tracking.
     *
     * @param string $mmsi Maritime Mobile Service Identity
     * @param array $data Vessel data
     * @param User|null $user User adding the vessel
     * @return TrackedVessel
     *
     * @throws \InvalidArgumentException
     */
    public function addVessel(string $mmsi, array $data, ?User $user = null): TrackedVessel
    {
        // Validate MMSI format (9 digits)
        if (!preg_match('/^\d{9}$/', $mmsi)) {
            throw new \InvalidArgumentException('MMSI must be exactly 9 digits');
        }

        // Check for duplicate
        $existing = TrackedVessel::where('mmsi', $mmsi)->first();
        if ($existing) {
            throw new \InvalidArgumentException("Vessel with MMSI {$mmsi} already exists");
        }

        return DB::transaction(function () use ($mmsi, $data, $user) {
            $vessel = new TrackedVessel();
            $vessel->uuid = Str::uuid()->toString();
            $vessel->mmsi = $mmsi;
            $vessel->name = $data['name'];
            $vessel->imo_number = $data['imo_number'] ?? null;
            $vessel->callsign = $data['callsign'] ?? null;
            $vessel->vessel_type = $data['vessel_type'] ?? TrackedVessel::TYPE_OTHER;
            $vessel->vessel_subtype = $data['vessel_subtype'] ?? null;
            $vessel->flag_country_id = $data['flag_country_id'] ?? null;
            $vessel->length_meters = $data['length_meters'] ?? null;
            $vessel->width_meters = $data['width_meters'] ?? null;
            $vessel->draft_meters = $data['draft_meters'] ?? null;
            $vessel->gross_tonnage = $data['gross_tonnage'] ?? null;
            $vessel->deadweight_tonnage = $data['deadweight_tonnage'] ?? null;
            $vessel->build_year = $data['build_year'] ?? null;
            $vessel->owner = $data['owner'] ?? null;
            $vessel->operator = $data['operator'] ?? null;
            $vessel->is_military = $data['is_military'] ?? false;
            $vessel->is_monitored = $data['is_monitored'] ?? true;
            $vessel->monitoring_priority = $data['monitoring_priority'] ?? TrackedVessel::PRIORITY_MEDIUM;
            $vessel->notes = $data['notes'] ?? null;
            $vessel->metadata = $data['metadata'] ?? null;
            $vessel->added_by = $user?->id;
            $vessel->save();

            // Log the addition
            $this->logActivity('vessel.added', $vessel, $user, [
                'mmsi' => $mmsi,
                'name' => $vessel->name,
            ]);

            return $vessel;
        });
    }

    /**
     * Fetch latest positions from an external AIS data source.
     *
     * @param string $source Data source identifier
     * @param array $options Fetch options
     * @return Collection<int, array> Collection of position data
     *
     * @throws \RuntimeException
     */
    public function fetchPositions(string $source, array $options = []): Collection
    {
        $config = config("maritime.providers.{$source}");

        if (!$config) {
            throw new \RuntimeException("Unknown AIS data source: {$source}");
        }

        if (empty($config['api_key'])) {
            throw new \RuntimeException("API key not configured for {$source}");
        }

        Log::info('Fetching vessel positions from source', [
            'source' => $source,
            'options' => $options,
        ]);

        try {
            switch ($source) {
                case 'marinetraffic':
                    return $this->fetchFromMarineTraffic($config, $options);

                case 'vesselfinder':
                    return $this->fetchFromVesselFinder($config, $options);

                default:
                    throw new \RuntimeException("Fetcher not implemented for: {$source}");
            }
        } catch (\Exception $e) {
            Log::error('Failed to fetch vessel positions', [
                'source' => $source,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Record a position update for a vessel.
     *
     * @param int $vesselId
     * @param array $data Position data
     * @return VesselPosition
     *
     * @throws \InvalidArgumentException
     */
    public function recordPosition(int $vesselId, array $data): VesselPosition
    {
        $vessel = TrackedVessel::findOrFail($vesselId);

        if (!isset($data['latitude']) || !isset($data['longitude'])) {
            throw new \InvalidArgumentException('Latitude and longitude are required');
        }

        $recordedAt = isset($data['recorded_at'])
            ? Carbon::parse($data['recorded_at'])
            : now();

        // Check for duplicate position (same vessel, same timestamp)
        $existing = VesselPosition::where('vessel_id', $vesselId)
            ->where('recorded_at', $recordedAt)
            ->first();

        if ($existing) {
            return $existing;
        }

        $position = new VesselPosition();
        $position->vessel_id = $vesselId;
        $position->setPositionFromCoordinates(
            (float) $data['latitude'],
            (float) $data['longitude']
        );
        $position->speed_knots = $data['speed_knots'] ?? null;
        $position->course = $data['course'] ?? null;
        $position->heading = $data['heading'] ?? null;
        $position->nav_status = $data['nav_status'] ?? VesselPosition::NAV_NOT_DEFINED;
        $position->destination = $data['destination'] ?? null;
        $position->eta = isset($data['eta']) ? Carbon::parse($data['eta']) : null;
        $position->draught_current = $data['draught_current'] ?? null;
        $position->recorded_at = $recordedAt;
        $position->data_source = $data['data_source'] ?? VesselPosition::SOURCE_MANUAL;
        $position->raw_data = $data['raw_data'] ?? null;
        $position->save();

        // Check alerts for this new position
        $this->checkAlertsForPosition($position);

        return $position;
    }

    /**
     * Build a track from vessel positions over a time range.
     *
     * @param int $vesselId
     * @param Carbon $startTime
     * @param Carbon $endTime
     * @return VesselTrack|null
     */
    public function buildVesselTrack(int $vesselId, Carbon $startTime, Carbon $endTime): ?VesselTrack
    {
        $vessel = TrackedVessel::findOrFail($vesselId);

        $positions = VesselPosition::where('vessel_id', $vesselId)
            ->whereBetween('recorded_at', [$startTime, $endTime])
            ->orderBy('recorded_at', 'asc')
            ->get();

        if ($positions->count() < 2) {
            Log::info('Not enough positions to build track', [
                'vessel_id' => $vesselId,
                'position_count' => $positions->count(),
            ]);
            return null;
        }

        // Extract coordinates for track
        $coordinates = [];
        $totalDistance = 0;

        foreach ($positions as $index => $pos) {
            $coords = $pos->coordinates;
            if ($coords) {
                $coordinates[] = $coords;

                // Calculate distance from previous point
                if ($index > 0) {
                    $totalDistance += $pos->distanceTo($positions[$index - 1]) ?? 0;
                }
            }
        }

        if (count($coordinates) < 2) {
            return null;
        }

        // Calculate average speed
        $duration = $startTime->diffInHours($endTime);
        $avgSpeed = $duration > 0 ? $totalDistance / $duration : 0;

        $track = new VesselTrack();
        $track->uuid = Str::uuid()->toString();
        $track->vessel_id = $vesselId;
        $track->departure_time = $startTime;
        $track->arrival_time = $endTime;
        $track->total_distance_nm = $totalDistance;
        $track->avg_speed = $avgSpeed;
        $track->setTrackFromPositions($coordinates);
        $track->voyage_type = VesselTrack::VOYAGE_UNKNOWN;
        $track->save();

        Log::info('Built vessel track', [
            'vessel_id' => $vesselId,
            'track_id' => $track->id,
            'position_count' => $track->position_count,
            'distance_nm' => $track->total_distance_nm,
        ]);

        return $track;
    }

    /**
     * Get vessels currently within a geographic zone.
     *
     * @param string $polygonWkt WKT polygon string
     * @param int $hoursBack Only consider positions from last N hours
     * @return Collection<int, TrackedVessel>
     */
    public function getVesselsInZone(string $polygonWkt, int $hoursBack = 1): Collection
    {
        $cutoff = now()->subHours($hoursBack);

        // Get latest position for each vessel
        $vesselIds = DB::table('vessel_positions')
            ->select('vessel_id')
            ->whereRaw(
                "ST_Contains(ST_GeomFromText(?, 4326), position)",
                [$polygonWkt]
            )
            ->where('recorded_at', '>=', $cutoff)
            ->groupBy('vessel_id')
            ->pluck('vessel_id');

        return TrackedVessel::whereIn('id', $vesselIds)
            ->with(['flagCountry', 'latestPosition'])
            ->get();
    }

    /**
     * Detect dark activity (AIS gaps) for a vessel.
     *
     * @param int $vesselId
     * @param int|null $thresholdHours Hours of gap to consider "dark"
     * @param Carbon|null $since Only check positions since this time
     * @return array Array of detected dark periods
     */
    public function detectDarkActivity(
        int $vesselId,
        ?int $thresholdHours = null,
        ?Carbon $since = null
    ): array {
        $thresholdHours = $thresholdHours ?? self::DEFAULT_DARK_THRESHOLD_HOURS;
        $since = $since ?? now()->subDays(30);

        $positions = VesselPosition::where('vessel_id', $vesselId)
            ->where('recorded_at', '>=', $since)
            ->orderBy('recorded_at', 'asc')
            ->get();

        if ($positions->count() < 2) {
            return [];
        }

        $darkPeriods = [];
        $thresholdMinutes = $thresholdHours * 60;

        for ($i = 1; $i < $positions->count(); $i++) {
            $gap = $positions[$i - 1]->recorded_at->diffInMinutes($positions[$i]->recorded_at);

            if ($gap >= $thresholdMinutes) {
                $beforeCoords = $positions[$i - 1]->coordinates;
                $afterCoords = $positions[$i]->coordinates;

                $darkPeriods[] = [
                    'start_time' => $positions[$i - 1]->recorded_at->toIso8601String(),
                    'end_time' => $positions[$i]->recorded_at->toIso8601String(),
                    'duration_hours' => round($gap / 60, 2),
                    'last_known_position' => $beforeCoords,
                    'resume_position' => $afterCoords,
                    'distance_nm' => $positions[$i - 1]->distanceTo($positions[$i]),
                    'position_before_id' => $positions[$i - 1]->id,
                    'position_after_id' => $positions[$i]->id,
                ];
            }
        }

        Log::info('Dark activity detection completed', [
            'vessel_id' => $vesselId,
            'periods_found' => count($darkPeriods),
            'threshold_hours' => $thresholdHours,
        ]);

        return $darkPeriods;
    }

    /**
     * Detect potential ship-to-ship (STS) transfers.
     *
     * @param int $vesselId
     * @param float|null $distanceNm Maximum distance to consider STS
     * @param Carbon|null $since Only check positions since this time
     * @return array Array of potential STS events
     */
    public function detectSTSTransfer(
        int $vesselId,
        ?float $distanceNm = null,
        ?Carbon $since = null
    ): array {
        $distanceNm = $distanceNm ?? self::DEFAULT_STS_DISTANCE_NM;
        $distanceMeters = $distanceNm * self::NM_TO_METERS;
        $since = $since ?? now()->subDays(7);

        // Get positions where vessel was slow or stationary
        $suspectPositions = VesselPosition::where('vessel_id', $vesselId)
            ->where('recorded_at', '>=', $since)
            ->where(function ($q) {
                $q->where('speed_knots', '<', 3)
                    ->orWhereNull('speed_knots');
            })
            ->orderBy('recorded_at', 'asc')
            ->get();

        $stsEvents = [];

        foreach ($suspectPositions as $pos) {
            // Find other vessels nearby at similar time
            $nearbyVessels = DB::table('vessel_positions')
                ->join('tracked_vessels', 'vessel_positions.vessel_id', '=', 'tracked_vessels.id')
                ->select([
                    'tracked_vessels.id as vessel_id',
                    'tracked_vessels.name',
                    'tracked_vessels.mmsi',
                    'tracked_vessels.vessel_type',
                    'vessel_positions.id as position_id',
                    'vessel_positions.recorded_at',
                    'vessel_positions.speed_knots',
                ])
                ->where('vessel_positions.vessel_id', '!=', $vesselId)
                ->whereBetween('vessel_positions.recorded_at', [
                    $pos->recorded_at->copy()->subMinutes(30),
                    $pos->recorded_at->copy()->addMinutes(30),
                ])
                ->whereRaw(
                    "ST_Distance_Sphere(vessel_positions.position, (SELECT position FROM vessel_positions WHERE id = ?)) < ?",
                    [$pos->id, $distanceMeters]
                )
                ->get();

            if ($nearbyVessels->isNotEmpty()) {
                $stsEvents[] = [
                    'detected_at' => $pos->recorded_at->toIso8601String(),
                    'position' => $pos->coordinates,
                    'speed_knots' => $pos->speed_knots,
                    'nearby_vessels' => $nearbyVessels->map(function ($v) {
                        return [
                            'vessel_id' => $v->vessel_id,
                            'name' => $v->name,
                            'mmsi' => $v->mmsi,
                            'vessel_type' => $v->vessel_type,
                            'position_id' => $v->position_id,
                            'recorded_at' => $v->recorded_at,
                            'speed_knots' => $v->speed_knots,
                        ];
                    })->toArray(),
                ];
            }
        }

        Log::info('STS detection completed', [
            'vessel_id' => $vesselId,
            'events_found' => count($stsEvents),
            'threshold_nm' => $distanceNm,
        ]);

        return $stsEvents;
    }

    /**
     * Correlate a vessel track with an OSINT event.
     *
     * @param int $trackId
     * @param int $eventId
     * @param User|null $user
     * @return VesselTrack
     */
    public function correlateWithEvent(int $trackId, int $eventId, ?User $user = null): VesselTrack
    {
        $track = VesselTrack::findOrFail($trackId);
        $event = Event::findOrFail($eventId);

        $track->linkToEvent($event);

        $this->logActivity('track.correlated', $track, $user, [
            'event_id' => $eventId,
            'event_title' => $event->title,
        ]);

        return $track->fresh();
    }

    /**
     * Get port calls for a vessel.
     *
     * @param int $vesselId
     * @param Carbon|null $since
     * @param int $limit
     * @return Collection<int, PortCall>
     */
    public function getPortCalls(int $vesselId, ?Carbon $since = null, int $limit = 50): Collection
    {
        $query = PortCall::where('vessel_id', $vesselId)
            ->recentFirst()
            ->with('portCountry');

        if ($since) {
            $query->where('arrival_time', '>=', $since);
        }

        return $query->limit($limit)->get();
    }

    /**
     * Record a port call for a vessel.
     *
     * @param int $vesselId
     * @param array $data
     * @return PortCall
     */
    public function recordPortCall(int $vesselId, array $data): PortCall
    {
        $portCall = new PortCall();
        $portCall->uuid = Str::uuid()->toString();
        $portCall->vessel_id = $vesselId;
        $portCall->port_name = $data['port_name'];
        $portCall->port_code = $data['port_code'] ?? null;
        $portCall->port_country_id = $data['port_country_id'] ?? null;
        $portCall->arrival_time = isset($data['arrival_time'])
            ? Carbon::parse($data['arrival_time'])
            : null;
        $portCall->departure_time = isset($data['departure_time'])
            ? Carbon::parse($data['departure_time'])
            : null;
        $portCall->previous_port = $data['previous_port'] ?? null;
        $portCall->next_port = $data['next_port'] ?? null;
        $portCall->call_type = $data['call_type'] ?? PortCall::CALL_UNKNOWN;
        $portCall->data_source = $data['data_source'] ?? PortCall::SOURCE_MANUAL;
        $portCall->metadata = $data['metadata'] ?? null;

        if (isset($data['latitude']) && isset($data['longitude'])) {
            $portCall->setPositionFromCoordinates(
                (float) $data['latitude'],
                (float) $data['longitude']
            );
        }

        $portCall->save();
        $portCall->calculateDuration();

        return $portCall;
    }

    /**
     * Create or update an alert for vessel monitoring.
     *
     * @param User $user
     * @param array $data
     * @return VesselAlert
     */
    public function createAlert(User $user, array $data): VesselAlert
    {
        $alert = new VesselAlert();
        $alert->uuid = Str::uuid()->toString();
        $alert->user_id = $user->id;
        $alert->alert_type = $data['alert_type'];
        $alert->name = $data['name'];
        $alert->description = $data['description'] ?? null;
        $alert->vessel_id = $data['vessel_id'] ?? null;
        $alert->conditions = $data['conditions'] ?? null;
        $alert->notification_channels = $data['notification_channels'] ?? ['database'];
        $alert->is_active = $data['is_active'] ?? true;
        $alert->cooldown_minutes = $data['cooldown_minutes'] ?? 60;

        if (isset($data['zone_geojson'])) {
            $alert->setZoneFromGeoJson($data['zone_geojson']);
        } elseif (isset($data['zone_wkt'])) {
            $alert->setZoneFromWkt($data['zone_wkt']);
        }

        $alert->save();

        return $alert;
    }

    /**
     * Get statistics for tracked vessels.
     *
     * @return array
     */
    public function getStatistics(): array
    {
        return [
            'total_vessels' => TrackedVessel::count(),
            'monitored_vessels' => TrackedVessel::monitored()->count(),
            'military_vessels' => TrackedVessel::military()->count(),
            'by_type' => TrackedVessel::select('vessel_type', DB::raw('count(*) as count'))
                ->groupBy('vessel_type')
                ->pluck('count', 'vessel_type')
                ->toArray(),
            'by_priority' => TrackedVessel::select('monitoring_priority', DB::raw('count(*) as count'))
                ->groupBy('monitoring_priority')
                ->pluck('count', 'monitoring_priority')
                ->toArray(),
            'total_positions' => VesselPosition::count(),
            'positions_today' => VesselPosition::where('recorded_at', '>=', now()->startOfDay())->count(),
            'total_tracks' => VesselTrack::count(),
            'total_port_calls' => PortCall::count(),
            'active_alerts' => VesselAlert::active()->count(),
        ];
    }

    /**
     * Check all active alerts for a new position.
     *
     * @param VesselPosition $position
     */
    protected function checkAlertsForPosition(VesselPosition $position): void
    {
        // Zone-based alerts
        $zoneAlerts = VesselAlert::active()
            ->canTrigger()
            ->zoneBased()
            ->where(function ($q) use ($position) {
                // Alert applies to all vessels or this specific vessel
                $q->whereNull('vessel_id')
                    ->orWhere('vessel_id', $position->vessel_id);
            })
            ->get();

        foreach ($zoneAlerts as $alert) {
            $this->checkZoneAlert($alert, $position);
        }

        // Vessel-specific alerts
        $vesselAlerts = VesselAlert::active()
            ->canTrigger()
            ->where('vessel_id', $position->vessel_id)
            ->whereNull('zone_polygon')
            ->get();

        foreach ($vesselAlerts as $alert) {
            $this->checkVesselAlert($alert, $position);
        }
    }

    /**
     * Check if a position triggers a zone-based alert.
     *
     * @param VesselAlert $alert
     * @param VesselPosition $position
     */
    protected function checkZoneAlert(VesselAlert $alert, VesselPosition $position): void
    {
        $inZone = DB::selectOne(
            "SELECT ST_Contains(
                (SELECT zone_polygon FROM vessel_alerts WHERE id = ?),
                (SELECT position FROM vessel_positions WHERE id = ?)
            ) as in_zone",
            [$alert->id, $position->id]
        );

        if ($inZone && $inZone->in_zone) {
            if ($alert->alert_type === VesselAlert::TYPE_ENTERED_ZONE) {
                $coords = $position->coordinates;
                $alert->recordTrigger([
                    'vessel_id' => $position->vessel_id,
                    'position_id' => $position->id,
                    'latitude' => $coords['lat'] ?? null,
                    'longitude' => $coords['lng'] ?? null,
                    'trigger_data' => [
                        'alert_type' => 'entered_zone',
                        'recorded_at' => $position->recorded_at->toIso8601String(),
                    ],
                    'message' => "Vessel entered monitored zone at {$position->recorded_at}",
                ]);

                Log::info('Zone alert triggered', [
                    'alert_id' => $alert->id,
                    'vessel_id' => $position->vessel_id,
                    'position_id' => $position->id,
                ]);
            }
        }
    }

    /**
     * Check if a position triggers a vessel-specific alert.
     *
     * @param VesselAlert $alert
     * @param VesselPosition $position
     */
    protected function checkVesselAlert(VesselAlert $alert, VesselPosition $position): void
    {
        $triggered = false;
        $message = '';

        switch ($alert->alert_type) {
            case VesselAlert::TYPE_VESSEL_SPOTTED:
                $triggered = true;
                $message = "Vessel spotted at new position";
                break;

            case VesselAlert::TYPE_SPEED_ANOMALY:
                $conditions = $alert->conditions ?? [];
                $minSpeed = $conditions['min_speed'] ?? 0;
                $maxSpeed = $conditions['max_speed'] ?? 50;

                if (
                    $position->speed_knots !== null &&
                    ($position->speed_knots < $minSpeed || $position->speed_knots > $maxSpeed)
                ) {
                    $triggered = true;
                    $message = "Speed anomaly detected: {$position->speed_knots} knots";
                }
                break;
        }

        if ($triggered) {
            $coords = $position->coordinates;
            $alert->recordTrigger([
                'vessel_id' => $position->vessel_id,
                'position_id' => $position->id,
                'latitude' => $coords['lat'] ?? null,
                'longitude' => $coords['lng'] ?? null,
                'trigger_data' => [
                    'alert_type' => $alert->alert_type,
                    'speed_knots' => $position->speed_knots,
                    'recorded_at' => $position->recorded_at->toIso8601String(),
                ],
                'message' => $message,
            ]);
        }
    }

    /**
     * Fetch positions from MarineTraffic API.
     *
     * @param array $config
     * @param array $options
     * @return Collection
     */
    protected function fetchFromMarineTraffic(array $config, array $options): Collection
    {
        $apiKey = $config['api_key'];
        $baseUrl = $config['base_url'] ?? 'https://services.marinetraffic.com/api/exportvessels';

        $params = [
            'v' => '8',
            'msgtype' => 'extended',
            'protocol' => 'json',
        ];

        // Add MMSI filter if specified
        if (isset($options['mmsi'])) {
            $params['mmsi'] = is_array($options['mmsi'])
                ? implode(',', $options['mmsi'])
                : $options['mmsi'];
        }

        // Add bounding box if specified
        if (isset($options['bounds'])) {
            $params['MINLAT'] = $options['bounds']['sw_lat'];
            $params['MAXLAT'] = $options['bounds']['ne_lat'];
            $params['MINLON'] = $options['bounds']['sw_lng'];
            $params['MAXLON'] = $options['bounds']['ne_lng'];
        }

        $url = "{$baseUrl}/{$apiKey}/?" . http_build_query($params);

        $response = Http::timeout(30)->get($url);

        if (!$response->successful()) {
            throw new \RuntimeException(
                "MarineTraffic API error: " . $response->status()
            );
        }

        $data = $response->json();

        return collect($data)->map(function ($item) {
            return [
                'mmsi' => $item['MMSI'] ?? null,
                'latitude' => $item['LAT'] ?? null,
                'longitude' => $item['LON'] ?? null,
                'speed_knots' => $item['SPEED'] ?? null,
                'course' => $item['COURSE'] ?? null,
                'heading' => $item['HEADING'] ?? null,
                'nav_status' => $this->mapNavStatus($item['STATUS'] ?? null),
                'destination' => $item['DESTINATION'] ?? null,
                'eta' => $item['ETA'] ?? null,
                'recorded_at' => $item['TIMESTAMP'] ?? now()->toIso8601String(),
                'raw_data' => $item,
            ];
        });
    }

    /**
     * Fetch positions from VesselFinder API.
     *
     * @param array $config
     * @param array $options
     * @return Collection
     */
    protected function fetchFromVesselFinder(array $config, array $options): Collection
    {
        $apiKey = $config['api_key'];
        $baseUrl = $config['base_url'] ?? 'https://api.vesselfinder.com/vessels';

        $headers = [
            'Authorization' => "Bearer {$apiKey}",
        ];

        $params = [];

        if (isset($options['mmsi'])) {
            $params['mmsi'] = is_array($options['mmsi'])
                ? implode(',', $options['mmsi'])
                : $options['mmsi'];
        }

        $response = Http::timeout(30)
            ->withHeaders($headers)
            ->get($baseUrl, $params);

        if (!$response->successful()) {
            throw new \RuntimeException(
                "VesselFinder API error: " . $response->status()
            );
        }

        $data = $response->json()['vessels'] ?? [];

        return collect($data)->map(function ($item) {
            return [
                'mmsi' => $item['mmsi'] ?? null,
                'latitude' => $item['lat'] ?? null,
                'longitude' => $item['lon'] ?? null,
                'speed_knots' => $item['speed'] ?? null,
                'course' => $item['course'] ?? null,
                'heading' => $item['heading'] ?? null,
                'nav_status' => $this->mapNavStatus($item['navstat'] ?? null),
                'destination' => $item['destination'] ?? null,
                'eta' => $item['eta'] ?? null,
                'recorded_at' => $item['timestamp'] ?? now()->toIso8601String(),
                'raw_data' => $item,
            ];
        });
    }

    /**
     * Map AIS navigation status code to enum value.
     *
     * @param mixed $status
     * @return string
     */
    protected function mapNavStatus($status): string
    {
        $map = [
            0 => VesselPosition::NAV_UNDERWAY_ENGINE,
            1 => VesselPosition::NAV_AT_ANCHOR,
            2 => VesselPosition::NAV_NOT_UNDER_COMMAND,
            3 => VesselPosition::NAV_RESTRICTED_MANOEUVRABILITY,
            4 => VesselPosition::NAV_CONSTRAINED_BY_DRAUGHT,
            5 => VesselPosition::NAV_MOORED,
            6 => VesselPosition::NAV_AGROUND,
            7 => VesselPosition::NAV_ENGAGED_IN_FISHING,
            8 => VesselPosition::NAV_UNDERWAY_SAILING,
        ];

        return $map[$status] ?? VesselPosition::NAV_NOT_DEFINED;
    }

    /**
     * Log activity to audit log.
     *
     * @param string $action
     * @param Model $model
     * @param User|null $user
     * @param array $properties
     */
    protected function logActivity(string $action, $model, ?User $user, array $properties = []): void
    {
        AuditLog::create([
            'user_id' => $user?->id,
            'action' => $action,
            'auditable_type' => get_class($model),
            'auditable_id' => $model->id,
            'properties' => $properties,
        ]);
    }
}
