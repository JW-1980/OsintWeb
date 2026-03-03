<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domains\Intelligence\Models\Event;
use App\Models\FlightAlert;
use App\Models\FlightPosition;
use App\Models\FlightTrack;
use App\Models\TrackedAircraft;
use App\Services\FlightTrackingService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

/**
 * Flight Tracking Controller
 *
 * Provides API endpoints for ADS-B flight tracking, aircraft management,
 * position history, flight tracks, and alert management.
 */
class FlightTrackingController extends Controller
{
    public function __construct(
        private readonly FlightTrackingService $flightTrackingService
    ) {
    }

    // ==================== AIRCRAFT ENDPOINTS ====================

    /**
     * List tracked aircraft with filtering
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'monitored' => ['nullable', 'boolean'],
            'military' => ['nullable', 'boolean'],
            'active' => ['nullable', 'boolean'],
            'priority' => ['nullable', 'integer', 'min:1', 'max:5'],
            'country_id' => ['nullable', 'integer', 'exists:countries,id'],
            'type' => ['nullable', 'string', 'max:50'],
            'search' => ['nullable', 'string', 'max:100'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = TrackedAircraft::query()
            ->with(['country', 'latestPosition']);

        if (isset($validated['monitored'])) {
            $validated['monitored'] ? $query->monitored() : $query->where('is_monitored', false);
        }

        if (isset($validated['military'])) {
            $validated['military'] ? $query->military() : $query->civilian();
        }

        if ($validated['active'] ?? false) {
            $query->active(15);
        }

        if (isset($validated['priority'])) {
            $query->priority($validated['priority']);
        }

        if (isset($validated['country_id'])) {
            $query->country($validated['country_id']);
        }

        if (isset($validated['type'])) {
            $query->type($validated['type']);
        }

        if (isset($validated['search'])) {
            $query->search($validated['search']);
        }

        $query->orderByPriority();

        $perPage = $validated['per_page'] ?? $this->perPage;
        $aircraft = $query->paginate($perPage);

        return $this->paginated($aircraft);
    }

    /**
     * Add a new aircraft to track
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'icao_hex' => ['required', 'string', 'size:6', 'regex:/^[0-9A-Fa-f]{6}$/'],
            'registration' => ['nullable', 'string', 'max:20'],
            'aircraft_type' => ['nullable', 'string', 'max:50'],
            'aircraft_model' => ['nullable', 'string', 'max:100'],
            'operator' => ['nullable', 'string', 'max:100'],
            'owner' => ['nullable', 'string', 'max:100'],
            'country_id' => ['nullable', 'integer', 'exists:countries,id'],
            'military_designation' => ['nullable', 'string', 'max:50'],
            'is_military' => ['nullable', 'boolean'],
            'monitoring_priority' => ['nullable', 'integer', 'min:1', 'max:5'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'metadata' => ['nullable', 'array'],
        ]);

        try {
            $aircraft = $this->flightTrackingService->addAircraft(
                $validated['icao_hex'],
                $validated,
                $request->user()
            );

            return $this->success([
                'aircraft' => $this->formatAircraftResponse($aircraft),
                'message' => 'Aircraft added to tracking successfully',
            ], 201);

        } catch (\Exception $e) {
            Log::error('Failed to add aircraft', [
                'icao_hex' => $validated['icao_hex'],
                'error' => $e->getMessage(),
            ]);

            return $this->error('Failed to add aircraft: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get aircraft details
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function show(string $uuid): JsonResponse
    {
        $aircraft = TrackedAircraft::where('uuid', $uuid)
            ->with(['country', 'latestPosition', 'addedByUser'])
            ->firstOrFail();

        return $this->success([
            'aircraft' => $this->formatAircraftResponse($aircraft),
            'latest_position' => $aircraft->latestPosition->first()
                ? $this->formatPositionResponse($aircraft->latestPosition->first())
                : null,
            'statistics' => [
                'total_positions' => $aircraft->positions()->count(),
                'total_tracks' => $aircraft->tracks()->count(),
                'first_seen' => $aircraft->first_seen_at?->toIso8601String(),
                'last_seen' => $aircraft->last_seen_at?->toIso8601String(),
                'is_active' => $aircraft->isActive(),
                'is_airborne' => $aircraft->isAirborne(),
            ],
        ]);
    }

    /**
     * Update aircraft details
     *
     * @param Request $request
     * @param string $uuid
     * @return JsonResponse
     */
    public function update(Request $request, string $uuid): JsonResponse
    {
        $aircraft = TrackedAircraft::where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'registration' => ['nullable', 'string', 'max:20'],
            'aircraft_type' => ['nullable', 'string', 'max:50'],
            'aircraft_model' => ['nullable', 'string', 'max:100'],
            'operator' => ['nullable', 'string', 'max:100'],
            'owner' => ['nullable', 'string', 'max:100'],
            'country_id' => ['nullable', 'integer', 'exists:countries,id'],
            'military_designation' => ['nullable', 'string', 'max:50'],
            'is_military' => ['nullable', 'boolean'],
            'is_monitored' => ['nullable', 'boolean'],
            'monitoring_priority' => ['nullable', 'integer', 'min:1', 'max:5'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'metadata' => ['nullable', 'array'],
        ]);

        $aircraft->update($validated);

        return $this->success([
            'aircraft' => $this->formatAircraftResponse($aircraft->fresh()),
            'message' => 'Aircraft updated successfully',
        ]);
    }

    /**
     * Delete aircraft (soft delete)
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function destroy(string $uuid): JsonResponse
    {
        $aircraft = TrackedAircraft::where('uuid', $uuid)->firstOrFail();

        $aircraft->delete();

        return $this->success([
            'message' => 'Aircraft removed from tracking',
        ]);
    }

    // ==================== POSITION ENDPOINTS ====================

    /**
     * Get position history for an aircraft
     *
     * @param Request $request
     * @param string $uuid Aircraft UUID
     * @return JsonResponse
     */
    public function positions(Request $request, string $uuid): JsonResponse
    {
        $aircraft = TrackedAircraft::where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'source' => ['nullable', 'string'],
            'airborne_only' => ['nullable', 'boolean'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:1000'],
        ]);

        $query = FlightPosition::where('aircraft_id', $aircraft->id)
            ->orderBy('recorded_at', 'desc');

        if (isset($validated['from'])) {
            $query->where('recorded_at', '>=', Carbon::parse($validated['from']));
        }

        if (isset($validated['to'])) {
            $query->where('recorded_at', '<=', Carbon::parse($validated['to']));
        }

        if (isset($validated['source'])) {
            $query->fromSource($validated['source']);
        }

        if ($validated['airborne_only'] ?? false) {
            $query->airborne();
        }

        $perPage = $validated['per_page'] ?? 100;
        $positions = $query->paginate($perPage);

        return $this->paginated($positions);
    }

    /**
     * Record a new position manually
     *
     * @param Request $request
     * @param string $uuid Aircraft UUID
     * @return JsonResponse
     */
    public function recordPosition(Request $request, string $uuid): JsonResponse
    {
        $aircraft = TrackedAircraft::where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'altitude_feet' => ['nullable', 'integer', 'min:0', 'max:60000'],
            'ground_speed_knots' => ['nullable', 'integer', 'min:0', 'max:2000'],
            'vertical_rate' => ['nullable', 'integer'],
            'heading' => ['nullable', 'numeric', 'between:0,360'],
            'squawk' => ['nullable', 'string', 'size:4', 'regex:/^[0-7]{4}$/'],
            'on_ground' => ['nullable', 'boolean'],
            'recorded_at' => ['nullable', 'date'],
        ]);

        try {
            $position = $this->flightTrackingService->recordPosition($aircraft->id, $validated);

            return $this->success([
                'position' => $this->formatPositionResponse($position),
                'message' => 'Position recorded successfully',
            ], 201);

        } catch (\Exception $e) {
            Log::error('Failed to record position', [
                'aircraft_uuid' => $uuid,
                'error' => $e->getMessage(),
            ]);

            return $this->error('Failed to record position: ' . $e->getMessage(), 500);
        }
    }

    // ==================== TRACK ENDPOINTS ====================

    /**
     * Get flight tracks for an aircraft
     *
     * @param Request $request
     * @param string $uuid Aircraft UUID
     * @return JsonResponse
     */
    public function tracks(Request $request, string $uuid): JsonResponse
    {
        $aircraft = TrackedAircraft::where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'status' => ['nullable', 'string', Rule::in(array_keys(FlightTrack::getStatuses()))],
            'with_event' => ['nullable', 'boolean'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = FlightTrack::where('aircraft_id', $aircraft->id)
            ->with(['event'])
            ->orderBy('departure_time', 'desc');

        if (isset($validated['from'])) {
            $query->where('departure_time', '>=', Carbon::parse($validated['from']));
        }

        if (isset($validated['to'])) {
            $query->where('departure_time', '<=', Carbon::parse($validated['to']));
        }

        if (isset($validated['status'])) {
            $query->where('track_status', $validated['status']);
        }

        if (isset($validated['with_event'])) {
            $validated['with_event'] ? $query->withEvent() : $query->withoutEvent();
        }

        $perPage = $validated['per_page'] ?? $this->perPage;
        $tracks = $query->paginate($perPage);

        return $this->paginated($tracks);
    }

    /**
     * Get a specific flight track
     *
     * @param string $uuid Track UUID
     * @return JsonResponse
     */
    public function showTrack(string $uuid): JsonResponse
    {
        $track = FlightTrack::where('uuid', $uuid)
            ->with(['aircraft', 'event'])
            ->firstOrFail();

        return $this->success([
            'track' => $this->formatTrackResponse($track),
            'path' => $track->getPathCoordinates(),
        ]);
    }

    /**
     * Build a flight track from positions
     *
     * @param Request $request
     * @param string $uuid Aircraft UUID
     * @return JsonResponse
     */
    public function buildTrack(Request $request, string $uuid): JsonResponse
    {
        $aircraft = TrackedAircraft::where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'start_time' => ['required', 'date'],
            'end_time' => ['nullable', 'date', 'after:start_time'],
        ]);

        try {
            $track = $this->flightTrackingService->buildFlightTrack(
                $aircraft->id,
                Carbon::parse($validated['start_time']),
                isset($validated['end_time']) ? Carbon::parse($validated['end_time']) : null
            );

            if (!$track) {
                return $this->error('Insufficient position data to build track', 422);
            }

            return $this->success([
                'track' => $this->formatTrackResponse($track),
                'message' => 'Flight track built successfully',
            ], 201);

        } catch (\Exception $e) {
            Log::error('Failed to build track', [
                'aircraft_uuid' => $uuid,
                'error' => $e->getMessage(),
            ]);

            return $this->error('Failed to build track: ' . $e->getMessage(), 500);
        }
    }

    // ==================== LIVE DATA ENDPOINTS ====================

    /**
     * Get live positions for monitored aircraft
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function live(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'military_only' => ['nullable', 'boolean'],
            'priority' => ['nullable', 'integer', 'min:1', 'max:5'],
            'bounds' => ['nullable', 'array'],
            'bounds.sw_lat' => ['required_with:bounds', 'numeric', 'between:-90,90'],
            'bounds.sw_lng' => ['required_with:bounds', 'numeric', 'between:-180,180'],
            'bounds.ne_lat' => ['required_with:bounds', 'numeric', 'between:-90,90'],
            'bounds.ne_lng' => ['required_with:bounds', 'numeric', 'between:-180,180'],
        ]);

        try {
            $liveData = $this->flightTrackingService->getLivePositions($validated);

            return $this->success([
                'aircraft' => $liveData->map(fn($item) => [
                    'aircraft' => $this->formatAircraftResponse($item['aircraft']),
                    'position' => $this->formatPositionResponse($item['position']),
                ]),
                'count' => $liveData->count(),
                'timestamp' => now()->toIso8601String(),
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get live positions', [
                'error' => $e->getMessage(),
            ]);

            return $this->error('Failed to get live positions: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Find aircraft in a geographic zone
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function inZone(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'polygon' => ['required', 'array', 'min:3'],
            'polygon.*.lat' => ['required', 'numeric', 'between:-90,90'],
            'polygon.*.lng' => ['required', 'numeric', 'between:-180,180'],
            'minutes' => ['nullable', 'integer', 'min:1', 'max:60'],
            'military_only' => ['nullable', 'boolean'],
            'airborne_only' => ['nullable', 'boolean'],
            'min_altitude' => ['nullable', 'integer', 'min:0'],
            'max_altitude' => ['nullable', 'integer', 'max:60000'],
        ]);

        try {
            $aircraft = $this->flightTrackingService->getAircraftInZone(
                $validated['polygon'],
                $validated
            );

            return $this->success([
                'aircraft' => $aircraft->map(fn($ac) => [
                    'aircraft' => $this->formatAircraftResponse($ac),
                    'latest_position' => $ac->latestPosition->first()
                        ? $this->formatPositionResponse($ac->latestPosition->first())
                        : null,
                ]),
                'count' => $aircraft->count(),
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to find aircraft in zone', [
                'error' => $e->getMessage(),
            ]);

            return $this->error('Failed to find aircraft in zone: ' . $e->getMessage(), 500);
        }
    }

    // ==================== ALERT ENDPOINTS ====================

    /**
     * List user's flight alerts
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function alerts(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'active' => ['nullable', 'boolean'],
            'type' => ['nullable', 'string', Rule::in(array_keys(FlightAlert::getAlertTypes()))],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = FlightAlert::forUser($request->user()->id)
            ->with(['aircraft'])
            ->orderBy('created_at', 'desc');

        if (isset($validated['active'])) {
            $validated['active'] ? $query->active() : $query->where('is_active', false);
        }

        if (isset($validated['type'])) {
            $query->ofType($validated['type']);
        }

        $perPage = $validated['per_page'] ?? $this->perPage;
        $alerts = $query->paginate($perPage);

        return $this->paginated($alerts);
    }

    /**
     * Create a new flight alert
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createAlert(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
            'alert_type' => ['required', 'string', Rule::in(array_keys(FlightAlert::getAlertTypes()))],
            'aircraft_uuid' => ['nullable', 'string', 'exists:tracked_aircraft,uuid'],
            'zone_polygon' => ['nullable', 'array', 'min:3'],
            'zone_polygon.*.lat' => ['required_with:zone_polygon', 'numeric', 'between:-90,90'],
            'zone_polygon.*.lng' => ['required_with:zone_polygon', 'numeric', 'between:-180,180'],
            'altitude_min' => ['nullable', 'integer', 'min:0', 'max:60000'],
            'altitude_max' => ['nullable', 'integer', 'max:60000', 'gte:altitude_min'],
            'squawk_codes' => ['nullable', 'array'],
            'squawk_codes.*' => ['string', 'size:4', 'regex:/^[0-7]{4}$/'],
            'aircraft_types' => ['nullable', 'array'],
            'aircraft_types.*' => ['string', 'max:50'],
            'military_only' => ['nullable', 'boolean'],
            'notification_channels' => ['nullable', 'string', Rule::in(array_keys(FlightAlert::getNotificationChannels()))],
            'cooldown_minutes' => ['nullable', 'integer', 'min:1', 'max:1440'],
        ]);

        // Check max alerts limit
        $maxAlerts = config('flight_tracking.alerts.max_alerts_per_user', 50);
        $currentCount = FlightAlert::forUser($request->user()->id)->count();

        if ($currentCount >= $maxAlerts) {
            return $this->error("Maximum alert limit ({$maxAlerts}) reached", 422);
        }

        try {
            $data = array_filter([
                'description' => $validated['description'] ?? null,
                'altitude_min' => $validated['altitude_min'] ?? null,
                'altitude_max' => $validated['altitude_max'] ?? null,
                'squawk_codes' => $validated['squawk_codes'] ?? null,
                'aircraft_types' => $validated['aircraft_types'] ?? null,
                'military_only' => $validated['military_only'] ?? false,
                'notification_channels' => $validated['notification_channels'] ?? FlightAlert::CHANNEL_DATABASE,
                'cooldown_minutes' => $validated['cooldown_minutes'] ?? config('flight_tracking.alerts.default_cooldown_minutes', 15),
            ], fn($value) => $value !== null);

            // Add aircraft reference if provided
            if (isset($validated['aircraft_uuid'])) {
                $aircraft = TrackedAircraft::where('uuid', $validated['aircraft_uuid'])->first();
                $data['aircraft_id'] = $aircraft?->id;
            }

            // Create alert with or without zone
            if (isset($validated['zone_polygon'])) {
                $alert = FlightAlert::createWithZone(
                    $request->user()->id,
                    $validated['name'],
                    $validated['alert_type'],
                    $validated['zone_polygon'],
                    $data
                );
            } else {
                $alert = FlightAlert::create(array_merge($data, [
                    'user_id' => $request->user()->id,
                    'name' => $validated['name'],
                    'alert_type' => $validated['alert_type'],
                ]));
            }

            return $this->success([
                'alert' => $this->formatAlertResponse($alert),
                'message' => 'Flight alert created successfully',
            ], 201);

        } catch (\Exception $e) {
            Log::error('Failed to create alert', [
                'error' => $e->getMessage(),
            ]);

            return $this->error('Failed to create alert: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get alert details
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function showAlert(string $uuid): JsonResponse
    {
        $alert = FlightAlert::where('uuid', $uuid)
            ->forUser(auth()->id())
            ->with(['aircraft', 'triggers' => fn($q) => $q->limit(20)])
            ->firstOrFail();

        return $this->success([
            'alert' => $this->formatAlertResponse($alert),
            'zone_coordinates' => $alert->getZoneCoordinates(),
            'recent_triggers' => $alert->triggers->map(fn($t) => [
                'id' => $t->id,
                'aircraft_id' => $t->aircraft_id,
                'triggered_at' => $t->triggered_at->toIso8601String(),
                'is_read' => $t->is_read,
                'trigger_data' => $t->trigger_data,
            ]),
            'statistics' => [
                'trigger_count' => $alert->trigger_count,
                'unread_count' => $alert->getUnreadTriggerCount(),
                'last_triggered' => $alert->last_triggered_at?->toIso8601String(),
            ],
        ]);
    }

    /**
     * Update an alert
     *
     * @param Request $request
     * @param string $uuid
     * @return JsonResponse
     */
    public function updateAlert(Request $request, string $uuid): JsonResponse
    {
        $alert = FlightAlert::where('uuid', $uuid)
            ->forUser(auth()->id())
            ->firstOrFail();

        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
            'altitude_min' => ['nullable', 'integer', 'min:0', 'max:60000'],
            'altitude_max' => ['nullable', 'integer', 'max:60000'],
            'squawk_codes' => ['nullable', 'array'],
            'aircraft_types' => ['nullable', 'array'],
            'military_only' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'notification_channels' => ['nullable', 'string', Rule::in(array_keys(FlightAlert::getNotificationChannels()))],
            'cooldown_minutes' => ['nullable', 'integer', 'min:1', 'max:1440'],
        ]);

        $alert->update($validated);

        return $this->success([
            'alert' => $this->formatAlertResponse($alert->fresh()),
            'message' => 'Alert updated successfully',
        ]);
    }

    /**
     * Toggle alert active status
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function toggleAlert(string $uuid): JsonResponse
    {
        $alert = FlightAlert::where('uuid', $uuid)
            ->forUser(auth()->id())
            ->firstOrFail();

        $alert->toggle();

        return $this->success([
            'is_active' => $alert->is_active,
            'message' => $alert->is_active ? 'Alert activated' : 'Alert deactivated',
        ]);
    }

    /**
     * Delete an alert
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function deleteAlert(string $uuid): JsonResponse
    {
        $alert = FlightAlert::where('uuid', $uuid)
            ->forUser(auth()->id())
            ->firstOrFail();

        $alert->delete();

        return $this->success([
            'message' => 'Alert deleted successfully',
        ]);
    }

    /**
     * Get alert triggers (notifications)
     *
     * @param Request $request
     * @param string $uuid
     * @return JsonResponse
     */
    public function alertTriggers(Request $request, string $uuid): JsonResponse
    {
        $alert = FlightAlert::where('uuid', $uuid)
            ->forUser(auth()->id())
            ->firstOrFail();

        $validated = $request->validate([
            'unread_only' => ['nullable', 'boolean'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = $alert->triggers()
            ->with(['aircraft', 'position'])
            ->orderBy('triggered_at', 'desc');

        if ($validated['unread_only'] ?? false) {
            $query->unread();
        }

        $perPage = $validated['per_page'] ?? $this->perPage;
        $triggers = $query->paginate($perPage);

        return $this->paginated($triggers);
    }

    // ==================== EVENT LINKING ====================

    /**
     * Link a flight track to an event
     *
     * @param Request $request
     * @param string $trackUuid
     * @return JsonResponse
     */
    public function linkToEvent(Request $request, string $trackUuid): JsonResponse
    {
        $track = FlightTrack::where('uuid', $trackUuid)->firstOrFail();

        $validated = $request->validate([
            'event_uuid' => ['required', 'string', 'exists:events,uuid'],
        ]);

        $event = Event::where('uuid', $validated['event_uuid'])->firstOrFail();

        $this->authorize('update', $event);

        try {
            $this->flightTrackingService->correlateWithEvent($track->id, $event->id);

            return $this->success([
                'message' => 'Flight track linked to event successfully',
                'track_uuid' => $track->uuid,
                'event_uuid' => $event->uuid,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to link track to event', [
                'track_uuid' => $trackUuid,
                'error' => $e->getMessage(),
            ]);

            return $this->error('Failed to link track to event: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Unlink a flight track from an event
     *
     * @param string $trackUuid
     * @return JsonResponse
     */
    public function unlinkFromEvent(string $trackUuid): JsonResponse
    {
        $track = FlightTrack::where('uuid', $trackUuid)->firstOrFail();

        if ($track->event_id) {
            $this->authorize('update', $track->event);
        }

        $track->unlinkFromEvent();

        return $this->success([
            'message' => 'Flight track unlinked from event',
        ]);
    }

    // ==================== ANALYSIS ====================

    /**
     * Detect unusual activity for an aircraft
     *
     * @param Request $request
     * @param string $uuid Aircraft UUID
     * @return JsonResponse
     */
    public function detectUnusual(Request $request, string $uuid): JsonResponse
    {
        $aircraft = TrackedAircraft::where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'hours' => ['nullable', 'integer', 'min:1', 'max:72'],
        ]);

        $since = now()->subHours($validated['hours'] ?? 6);

        try {
            $analysis = $this->flightTrackingService->detectUnusualActivity(
                $aircraft->id,
                $since
            );

            return $this->success([
                'aircraft' => $this->formatAircraftResponse($aircraft),
                'analysis' => $analysis,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to detect unusual activity', [
                'aircraft_uuid' => $uuid,
                'error' => $e->getMessage(),
            ]);

            return $this->error('Failed to analyze activity: ' . $e->getMessage(), 500);
        }
    }

    // ==================== UTILITY ENDPOINTS ====================

    /**
     * Get tracking statistics
     *
     * @return JsonResponse
     */
    public function stats(): JsonResponse
    {
        return $this->success([
            'statistics' => $this->flightTrackingService->getStatistics(),
        ]);
    }

    /**
     * Get available data providers
     *
     * @return JsonResponse
     */
    public function providers(): JsonResponse
    {
        return $this->success([
            'providers' => $this->flightTrackingService->getAvailableProviders(),
            'default' => config('flight_tracking.default_provider'),
        ]);
    }

    /**
     * Get available data sources
     *
     * @return JsonResponse
     */
    public function dataSources(): JsonResponse
    {
        return $this->success([
            'sources' => FlightPosition::getDataSources(),
        ]);
    }

    /**
     * Get alert types
     *
     * @return JsonResponse
     */
    public function alertTypes(): JsonResponse
    {
        return $this->success([
            'types' => FlightAlert::getAlertTypes(),
            'notification_channels' => FlightAlert::getNotificationChannels(),
        ]);
    }

    /**
     * Get priority levels
     *
     * @return JsonResponse
     */
    public function priorities(): JsonResponse
    {
        return $this->success([
            'priorities' => TrackedAircraft::getPriorityLevels(),
        ]);
    }

    /**
     * Get track statuses
     *
     * @return JsonResponse
     */
    public function trackStatuses(): JsonResponse
    {
        return $this->success([
            'statuses' => FlightTrack::getStatuses(),
        ]);
    }

    // ==================== RESPONSE FORMATTERS ====================

    /**
     * Format aircraft for API response
     */
    private function formatAircraftResponse(TrackedAircraft $aircraft): array
    {
        return [
            'uuid' => $aircraft->uuid,
            'icao_hex' => $aircraft->icao_hex,
            'registration' => $aircraft->registration,
            'aircraft_type' => $aircraft->aircraft_type,
            'aircraft_model' => $aircraft->aircraft_model,
            'operator' => $aircraft->operator,
            'owner' => $aircraft->owner,
            'country' => $aircraft->country ? [
                'id' => $aircraft->country->id,
                'name' => $aircraft->country->name,
                'code' => $aircraft->country->code,
            ] : null,
            'military_designation' => $aircraft->military_designation,
            'is_military' => $aircraft->is_military,
            'is_monitored' => $aircraft->is_monitored,
            'monitoring_priority' => $aircraft->monitoring_priority,
            'priority_label' => $aircraft->getPriorityLabel(),
            'display_name' => $aircraft->getDisplayName(),
            'notes' => $aircraft->notes,
            'metadata' => $aircraft->metadata,
            'first_seen_at' => $aircraft->first_seen_at?->toIso8601String(),
            'last_seen_at' => $aircraft->last_seen_at?->toIso8601String(),
            'is_active' => $aircraft->isActive(),
        ];
    }

    /**
     * Format position for API response
     */
    private function formatPositionResponse(FlightPosition $position): array
    {
        return [
            'id' => $position->id,
            'coordinates' => $position->getCoordinates(),
            'altitude' => [
                'feet' => $position->altitude_feet,
                'meters' => $position->altitude_meters,
                'km' => $position->getAltitudeKm(),
            ],
            'speed' => [
                'knots' => $position->ground_speed_knots,
                'kmh' => $position->getSpeedKmh(),
            ],
            'heading' => [
                'degrees' => $position->heading,
                'cardinal' => $position->getHeadingCardinal(),
            ],
            'vertical_rate' => [
                'fpm' => $position->vertical_rate,
                'description' => $position->getVerticalRateDescription(),
            ],
            'squawk' => [
                'code' => $position->squawk,
                'description' => $position->getSquawkDescription(),
                'is_emergency' => $position->isEmergencySquawk(),
            ],
            'on_ground' => $position->on_ground,
            'recorded_at' => $position->recorded_at->toIso8601String(),
            'data_source' => $position->data_source,
        ];
    }

    /**
     * Format track for API response
     */
    private function formatTrackResponse(FlightTrack $track): array
    {
        return [
            'uuid' => $track->uuid,
            'aircraft_id' => $track->aircraft_id,
            'callsign' => $track->callsign,
            'flight_number' => $track->flight_number,
            'route' => $track->getRouteString(),
            'departure_airport' => $track->departure_airport,
            'arrival_airport' => $track->arrival_airport,
            'departure_time' => $track->departure_time?->toIso8601String(),
            'arrival_time' => $track->arrival_time?->toIso8601String(),
            'duration' => [
                'minutes' => $track->duration_minutes,
                'formatted' => $track->getFormattedDuration(),
            ],
            'altitude' => [
                'max_feet' => $track->max_altitude_feet,
                'flight_level' => $track->getMaxFlightLevel(),
            ],
            'speed' => [
                'avg_knots' => $track->avg_speed_knots,
                'max_knots' => $track->max_speed_knots,
            ],
            'distance_nm' => $track->total_distance_nm,
            'position_count' => $track->position_count,
            'track_status' => $track->track_status,
            'status_label' => $track->getStatusLabel(),
            'event_id' => $track->event_id,
            'has_event' => $track->event_id !== null,
            'notes' => $track->notes,
            'metadata' => $track->metadata,
            'created_at' => $track->created_at->toIso8601String(),
        ];
    }

    /**
     * Format alert for API response
     */
    private function formatAlertResponse(FlightAlert $alert): array
    {
        return [
            'uuid' => $alert->uuid,
            'name' => $alert->name,
            'description' => $alert->description,
            'alert_type' => $alert->alert_type,
            'alert_type_label' => $alert->getAlertTypeLabel(),
            'aircraft_id' => $alert->aircraft_id,
            'has_zone' => $alert->zone_polygon !== null,
            'altitude_min' => $alert->altitude_min,
            'altitude_max' => $alert->altitude_max,
            'squawk_codes' => $alert->squawk_codes,
            'aircraft_types' => $alert->aircraft_types,
            'military_only' => $alert->military_only,
            'is_active' => $alert->is_active,
            'notification_channels' => $alert->notification_channels,
            'notification_channel_label' => $alert->getNotificationChannelLabel(),
            'cooldown_minutes' => $alert->cooldown_minutes,
            'trigger_count' => $alert->trigger_count,
            'last_triggered_at' => $alert->last_triggered_at?->toIso8601String(),
            'created_at' => $alert->created_at->toIso8601String(),
        ];
    }
}
