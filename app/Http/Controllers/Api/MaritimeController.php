<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PortCall;
use App\Models\TrackedVessel;
use App\Models\VesselAlert;
use App\Models\VesselPosition;
use App\Models\VesselTrack;
use App\Services\MaritimeTrackingService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Maritime Controller
 *
 * API endpoints for Maritime Vessel Tracking (AIS) functionality.
 */
class MaritimeController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected MaritimeTrackingService $maritimeService
    ) {}

    /**
     * List all tracked vessels with filters.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = TrackedVessel::query();

        // Filter by monitoring status
        if ($request->has('monitored')) {
            $query->where('is_monitored', $request->boolean('monitored'));
        }

        // Filter by military
        if ($request->has('military')) {
            $query->where('is_military', $request->boolean('military'));
        }

        // Filter by vessel type
        if ($request->filled('type')) {
            $query->where('vessel_type', $request->type);
        }

        // Filter by flag country
        if ($request->filled('country_id')) {
            $query->where('flag_country_id', $request->country_id);
        }

        // Filter by priority
        if ($request->filled('priority')) {
            $query->where('monitoring_priority', '<=', (int) $request->priority);
        }

        // Search by name, MMSI, IMO, or callsign
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Ordering
        $allowedSorts = [
            'id',
            'monitoring_priority',
            'mmsi',
            'imo_number',
            'name',
            'callsign',
            'vessel_type',
            'build_year',
            'created_at',
            'updated_at'
        ];

        $sortBy = $request->get('sort', 'monitoring_priority');
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'monitoring_priority';
        }

        $sortDir = $request->get('direction', 'asc');
        $sortDir = strtolower($sortDir) === 'desc' ? 'desc' : 'asc';

        $query->orderBy($sortBy, $sortDir);

        // Eager load relationships
        $query->with(['flagCountry', 'addedByUser']);

        $vessels = $query->paginate($this->perPage);

        return $this->paginated($vessels);
    }

    /**
     * Store a new tracked vessel.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'mmsi' => ['required', 'string', 'regex:/^\d{9}$/', 'unique:tracked_vessels,mmsi'],
            'name' => ['required', 'string', 'max:255'],
            'imo_number' => ['nullable', 'string', 'max:10'],
            'callsign' => ['nullable', 'string', 'max:10'],
            'vessel_type' => ['required', Rule::in(array_keys(TrackedVessel::getVesselTypes()))],
            'vessel_subtype' => ['nullable', 'string', 'max:100'],
            'flag_country_id' => ['nullable', 'exists:countries,id'],
            'length_meters' => ['nullable', 'numeric', 'min:0', 'max:500'],
            'width_meters' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'draft_meters' => ['nullable', 'numeric', 'min:0', 'max:50'],
            'gross_tonnage' => ['nullable', 'integer', 'min:0'],
            'deadweight_tonnage' => ['nullable', 'integer', 'min:0'],
            'build_year' => ['nullable', 'integer', 'min:1800', 'max:' . (date('Y') + 5)],
            'owner' => ['nullable', 'string', 'max:255'],
            'operator' => ['nullable', 'string', 'max:255'],
            'is_military' => ['boolean'],
            'is_monitored' => ['boolean'],
            'monitoring_priority' => ['integer', 'min:1', 'max:5'],
            'notes' => ['nullable', 'string'],
            'metadata' => ['nullable', 'array'],
        ]);

        try {
            $vessel = $this->maritimeService->addVessel(
                $validated['mmsi'],
                $validated,
                $request->user()
            );

            return $this->success($vessel->load('flagCountry'), 201);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    /**
     * Display a specific vessel.
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function show(string $uuid): JsonResponse
    {
        $vessel = TrackedVessel::where('uuid', $uuid)
            ->with(['flagCountry', 'addedByUser'])
            ->firstOrFail();

        // Add latest position
        $latestPosition = $vessel->positions()
            ->orderBy('recorded_at', 'desc')
            ->first();

        $response = $vessel->toArray();
        $response['latest_position'] = $latestPosition?->toArray();
        $response['latest_position_coordinates'] = $latestPosition?->coordinates;
        $response['hours_since_update'] = $vessel->hoursSinceLastPosition();

        return $this->success($response);
    }

    /**
     * Update a tracked vessel.
     *
     * @param Request $request
     * @param string $uuid
     * @return JsonResponse
     */
    public function update(Request $request, string $uuid): JsonResponse
    {
        $vessel = TrackedVessel::where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'imo_number' => ['nullable', 'string', 'max:10'],
            'callsign' => ['nullable', 'string', 'max:10'],
            'vessel_type' => ['sometimes', Rule::in(array_keys(TrackedVessel::getVesselTypes()))],
            'vessel_subtype' => ['nullable', 'string', 'max:100'],
            'flag_country_id' => ['nullable', 'exists:countries,id'],
            'length_meters' => ['nullable', 'numeric', 'min:0', 'max:500'],
            'width_meters' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'draft_meters' => ['nullable', 'numeric', 'min:0', 'max:50'],
            'gross_tonnage' => ['nullable', 'integer', 'min:0'],
            'deadweight_tonnage' => ['nullable', 'integer', 'min:0'],
            'build_year' => ['nullable', 'integer', 'min:1800', 'max:' . (date('Y') + 5)],
            'owner' => ['nullable', 'string', 'max:255'],
            'operator' => ['nullable', 'string', 'max:255'],
            'is_military' => ['boolean'],
            'is_monitored' => ['boolean'],
            'monitoring_priority' => ['integer', 'min:1', 'max:5'],
            'notes' => ['nullable', 'string'],
            'metadata' => ['nullable', 'array'],
        ]);

        $vessel->update($validated);

        return $this->success($vessel->fresh(['flagCountry']));
    }

    /**
     * Delete a tracked vessel.
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function destroy(string $uuid): JsonResponse
    {
        $vessel = TrackedVessel::where('uuid', $uuid)->firstOrFail();
        $vessel->delete();

        return $this->success(['message' => 'Vessel removed from tracking']);
    }

    /**
     * Get positions for a vessel.
     *
     * @param Request $request
     * @param string $uuid
     * @return JsonResponse
     */
    public function positions(Request $request, string $uuid): JsonResponse
    {
        $vessel = TrackedVessel::where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'start' => ['nullable', 'date'],
            'end' => ['nullable', 'date'],
            'source' => ['nullable', Rule::in(array_keys(VesselPosition::getDataSources()))],
            'limit' => ['nullable', 'integer', 'min:1', 'max:1000'],
        ]);

        $query = $vessel->positions()
            ->orderBy('recorded_at', 'desc');

        if (isset($validated['start'])) {
            $query->where('recorded_at', '>=', Carbon::parse($validated['start']));
        }

        if (isset($validated['end'])) {
            $query->where('recorded_at', '<=', Carbon::parse($validated['end']));
        }

        if (isset($validated['source'])) {
            $query->where('data_source', $validated['source']);
        }

        $limit = $validated['limit'] ?? 100;
        $positions = $query->limit($limit)->get();

        // Add coordinates to each position
        $positions = $positions->map(function ($pos) {
            $data = $pos->toArray();
            $data['coordinates'] = $pos->coordinates;
            return $data;
        });

        return $this->success([
            'vessel_id' => $vessel->id,
            'vessel_name' => $vessel->name,
            'count' => $positions->count(),
            'positions' => $positions,
        ]);
    }

    /**
     * Record a new position for a vessel.
     *
     * @param Request $request
     * @param string $uuid
     * @return JsonResponse
     */
    public function recordPosition(Request $request, string $uuid): JsonResponse
    {
        $vessel = TrackedVessel::where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'speed_knots' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'course' => ['nullable', 'numeric', 'min:0', 'max:360'],
            'heading' => ['nullable', 'numeric', 'min:0', 'max:360'],
            'nav_status' => ['nullable', Rule::in(array_keys(VesselPosition::getNavStatuses()))],
            'destination' => ['nullable', 'string', 'max:255'],
            'eta' => ['nullable', 'date'],
            'draught_current' => ['nullable', 'numeric', 'min:0', 'max:50'],
            'recorded_at' => ['nullable', 'date'],
            'data_source' => ['nullable', Rule::in(array_keys(VesselPosition::getDataSources()))],
        ]);

        $position = $this->maritimeService->recordPosition($vessel->id, $validated);

        $response = $position->toArray();
        $response['coordinates'] = $position->coordinates;

        return $this->success($response, 201);
    }

    /**
     * Get tracks for a vessel.
     *
     * @param Request $request
     * @param string $uuid
     * @return JsonResponse
     */
    public function tracks(Request $request, string $uuid): JsonResponse
    {
        $vessel = TrackedVessel::where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'start' => ['nullable', 'date'],
            'end' => ['nullable', 'date'],
            'voyage_type' => ['nullable', Rule::in(array_keys(VesselTrack::getVoyageTypes()))],
        ]);

        $query = $vessel->tracks()
            ->with('event')
            ->orderBy('departure_time', 'desc');

        if (isset($validated['start'])) {
            $query->where('departure_time', '>=', Carbon::parse($validated['start']));
        }

        if (isset($validated['end'])) {
            $query->where('departure_time', '<=', Carbon::parse($validated['end']));
        }

        if (isset($validated['voyage_type'])) {
            $query->where('voyage_type', $validated['voyage_type']);
        }

        $tracks = $query->paginate($this->perPage);

        return $this->paginated($tracks);
    }

    /**
     * Build a track from positions.
     *
     * @param Request $request
     * @param string $uuid
     * @return JsonResponse
     */
    public function buildTrack(Request $request, string $uuid): JsonResponse
    {
        $vessel = TrackedVessel::where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'start' => ['required', 'date'],
            'end' => ['required', 'date', 'after:start'],
        ]);

        $track = $this->maritimeService->buildVesselTrack(
            $vessel->id,
            Carbon::parse($validated['start']),
            Carbon::parse($validated['end'])
        );

        if (!$track) {
            return $this->error('Not enough positions to build track', 422);
        }

        $response = $track->toArray();
        $response['track_coordinates'] = $track->track_coordinates;

        return $this->success($response, 201);
    }

    /**
     * Get live vessel data (latest positions for all monitored vessels).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function live(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'sw_lat' => ['nullable', 'numeric', 'between:-90,90'],
            'sw_lng' => ['nullable', 'numeric', 'between:-180,180'],
            'ne_lat' => ['nullable', 'numeric', 'between:-90,90'],
            'ne_lng' => ['nullable', 'numeric', 'between:-180,180'],
            'hours' => ['nullable', 'integer', 'min:1', 'max:72'],
            'types' => ['nullable', 'array'],
            'types.*' => [Rule::in(array_keys(TrackedVessel::getVesselTypes()))],
        ]);

        $hoursBack = $validated['hours'] ?? 24;
        $cutoff = now()->subHours($hoursBack);

        // Get latest position for each vessel
        $subQuery = VesselPosition::select('vessel_id', DB::raw('MAX(id) as max_id'))
            ->where('recorded_at', '>=', $cutoff)
            ->groupBy('vessel_id');

        $query = VesselPosition::joinSub($subQuery, 'latest', function ($join) {
            $join->on('vessel_positions.id', '=', 'latest.max_id');
        })->with(['vessel', 'vessel.flagCountry']);

        // Apply bounding box filter
        if (
            isset($validated['sw_lat']) &&
            isset($validated['sw_lng']) &&
            isset($validated['ne_lat']) &&
            isset($validated['ne_lng'])
        ) {
            $query->withinBounds(
                $validated['sw_lat'],
                $validated['sw_lng'],
                $validated['ne_lat'],
                $validated['ne_lng']
            );
        }

        $positions = $query->get();

        // Filter by vessel type if specified
        if (isset($validated['types'])) {
            $positions = $positions->filter(function ($pos) use ($validated) {
                return in_array($pos->vessel->vessel_type, $validated['types']);
            });
        }

        // Format response
        $liveData = $positions->map(function ($pos) {
            return [
                'vessel' => [
                    'id' => $pos->vessel->id,
                    'uuid' => $pos->vessel->uuid,
                    'mmsi' => $pos->vessel->mmsi,
                    'name' => $pos->vessel->name,
                    'type' => $pos->vessel->vessel_type,
                    'flag' => $pos->vessel->flagCountry?->iso_code_2,
                    'flag_emoji' => $pos->vessel->flagCountry?->flag_emoji,
                    'is_military' => $pos->vessel->is_military,
                ],
                'position' => $pos->coordinates,
                'speed_knots' => $pos->speed_knots,
                'course' => $pos->course,
                'heading' => $pos->heading,
                'nav_status' => $pos->nav_status,
                'destination' => $pos->destination,
                'recorded_at' => $pos->recorded_at->toIso8601String(),
            ];
        })->values();

        return $this->success([
            'count' => $liveData->count(),
            'as_of' => now()->toIso8601String(),
            'vessels' => $liveData,
        ]);
    }

    /**
     * Get vessels within a geographic zone.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function inZone(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'zone' => ['required', 'string'],  // WKT polygon
            'hours' => ['nullable', 'integer', 'min:1', 'max:24'],
        ]);

        $hoursBack = $validated['hours'] ?? 1;

        $vessels = $this->maritimeService->getVesselsInZone(
            $validated['zone'],
            $hoursBack
        );

        return $this->success([
            'count' => $vessels->count(),
            'vessels' => $vessels,
        ]);
    }

    /**
     * Get alerts for the current user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function alerts(Request $request): JsonResponse
    {
        $query = VesselAlert::where('user_id', $request->user()->id)
            ->with(['vessel'])
            ->orderBy('created_at', 'desc');

        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        if ($request->filled('type')) {
            $query->where('alert_type', $request->type);
        }

        $alerts = $query->paginate($this->perPage);

        return $this->paginated($alerts);
    }

    /**
     * Create a new alert.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createAlert(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'alert_type' => ['required', Rule::in(array_keys(VesselAlert::getAlertTypes()))],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'vessel_id' => ['nullable', 'exists:tracked_vessels,id'],
            'zone_geojson' => ['nullable', 'array'],
            'zone_wkt' => ['nullable', 'string'],
            'conditions' => ['nullable', 'array'],
            'notification_channels' => ['nullable', 'array'],
            'is_active' => ['boolean'],
            'cooldown_minutes' => ['integer', 'min:1', 'max:1440'],
        ]);

        $alert = $this->maritimeService->createAlert($request->user(), $validated);

        return $this->success($alert, 201);
    }

    /**
     * Update an alert.
     *
     * @param Request $request
     * @param string $uuid
     * @return JsonResponse
     */
    public function updateAlert(Request $request, string $uuid): JsonResponse
    {
        $alert = VesselAlert::where('uuid', $uuid)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'vessel_id' => ['nullable', 'exists:tracked_vessels,id'],
            'conditions' => ['nullable', 'array'],
            'notification_channels' => ['nullable', 'array'],
            'is_active' => ['boolean'],
            'cooldown_minutes' => ['integer', 'min:1', 'max:1440'],
        ]);

        $alert->update($validated);

        return $this->success($alert->fresh());
    }

    /**
     * Delete an alert.
     *
     * @param Request $request
     * @param string $uuid
     * @return JsonResponse
     */
    public function deleteAlert(Request $request, string $uuid): JsonResponse
    {
        $alert = VesselAlert::where('uuid', $uuid)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $alert->delete();

        return $this->success(['message' => 'Alert deleted']);
    }

    /**
     * Toggle alert active state.
     *
     * @param Request $request
     * @param string $uuid
     * @return JsonResponse
     */
    public function toggleAlert(Request $request, string $uuid): JsonResponse
    {
        $alert = VesselAlert::where('uuid', $uuid)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $alert->toggle();

        return $this->success($alert->fresh());
    }

    /**
     * Get port calls for a vessel.
     *
     * @param Request $request
     * @param string $uuid
     * @return JsonResponse
     */
    public function portCalls(Request $request, string $uuid): JsonResponse
    {
        $vessel = TrackedVessel::where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'since' => ['nullable', 'date'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $since = isset($validated['since'])
            ? Carbon::parse($validated['since'])
            : null;

        $portCalls = $this->maritimeService->getPortCalls(
            $vessel->id,
            $since,
            $validated['limit'] ?? 50
        );

        $portCalls = $portCalls->map(function ($call) {
            $data = $call->toArray();
            $data['coordinates'] = $call->coordinates;
            return $data;
        });

        return $this->success([
            'vessel_id' => $vessel->id,
            'vessel_name' => $vessel->name,
            'count' => $portCalls->count(),
            'port_calls' => $portCalls,
        ]);
    }

    /**
     * Record a port call.
     *
     * @param Request $request
     * @param string $uuid
     * @return JsonResponse
     */
    public function recordPortCall(Request $request, string $uuid): JsonResponse
    {
        $vessel = TrackedVessel::where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'port_name' => ['required', 'string', 'max:255'],
            'port_code' => ['nullable', 'string', 'max:10'],
            'port_country_id' => ['nullable', 'exists:countries,id'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'arrival_time' => ['nullable', 'date'],
            'departure_time' => ['nullable', 'date'],
            'previous_port' => ['nullable', 'string', 'max:255'],
            'next_port' => ['nullable', 'string', 'max:255'],
            'call_type' => ['nullable', Rule::in(array_keys(PortCall::getCallTypes()))],
            'data_source' => ['nullable', Rule::in(array_keys(PortCall::getDataSources()))],
            'metadata' => ['nullable', 'array'],
        ]);

        $portCall = $this->maritimeService->recordPortCall($vessel->id, $validated);

        $response = $portCall->toArray();
        $response['coordinates'] = $portCall->coordinates;

        return $this->success($response, 201);
    }

    /**
     * Link a track to an event.
     *
     * @param Request $request
     * @param string $trackUuid
     * @return JsonResponse
     */
    public function linkToEvent(Request $request, string $trackUuid): JsonResponse
    {
        $track = VesselTrack::where('uuid', $trackUuid)->firstOrFail();

        $validated = $request->validate([
            'event_id' => ['required', 'exists:events,id'],
        ]);

        $track = $this->maritimeService->correlateWithEvent(
            $track->id,
            $validated['event_id'],
            $request->user()
        );

        return $this->success($track->load('event'));
    }

    /**
     * Detect dark activity (AIS gaps) for a vessel.
     *
     * @param Request $request
     * @param string $uuid
     * @return JsonResponse
     */
    public function detectDarkActivity(Request $request, string $uuid): JsonResponse
    {
        $vessel = TrackedVessel::where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'threshold_hours' => ['nullable', 'integer', 'min:1', 'max:48'],
            'since' => ['nullable', 'date'],
        ]);

        $since = isset($validated['since'])
            ? Carbon::parse($validated['since'])
            : null;

        $darkPeriods = $this->maritimeService->detectDarkActivity(
            $vessel->id,
            $validated['threshold_hours'] ?? null,
            $since
        );

        return $this->success([
            'vessel_id' => $vessel->id,
            'vessel_name' => $vessel->name,
            'threshold_hours' => $validated['threshold_hours'] ?? config('maritime.dark_activity.threshold_hours'),
            'periods_found' => count($darkPeriods),
            'dark_periods' => $darkPeriods,
        ]);
    }

    /**
     * Detect potential STS (ship-to-ship) transfers.
     *
     * @param Request $request
     * @param string $uuid
     * @return JsonResponse
     */
    public function detectSTSTransfer(Request $request, string $uuid): JsonResponse
    {
        $vessel = TrackedVessel::where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'distance_nm' => ['nullable', 'numeric', 'min:0.1', 'max:5'],
            'since' => ['nullable', 'date'],
        ]);

        $since = isset($validated['since'])
            ? Carbon::parse($validated['since'])
            : null;

        $stsEvents = $this->maritimeService->detectSTSTransfer(
            $vessel->id,
            $validated['distance_nm'] ?? null,
            $since
        );

        return $this->success([
            'vessel_id' => $vessel->id,
            'vessel_name' => $vessel->name,
            'threshold_nm' => $validated['distance_nm'] ?? config('maritime.sts_detection.max_distance_nm'),
            'events_found' => count($stsEvents),
            'sts_events' => $stsEvents,
        ]);
    }

    /**
     * Get vessel types reference data.
     *
     * @return JsonResponse
     */
    public function vesselTypes(): JsonResponse
    {
        return $this->success([
            'vessel_types' => TrackedVessel::getVesselTypes(),
            'priority_levels' => TrackedVessel::getPriorityLevels(),
            'nav_statuses' => VesselPosition::getNavStatuses(),
            'data_sources' => VesselPosition::getDataSources(),
            'voyage_types' => VesselTrack::getVoyageTypes(),
            'alert_types' => VesselAlert::getAlertTypes(),
            'call_types' => PortCall::getCallTypes(),
        ]);
    }

    /**
     * Get maritime tracking statistics.
     *
     * @return JsonResponse
     */
    public function statistics(): JsonResponse
    {
        $stats = $this->maritimeService->getStatistics();

        return $this->success($stats);
    }
}
