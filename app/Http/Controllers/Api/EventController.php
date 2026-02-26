<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Domains\Intelligence\Models\Event;
use App\Domains\Intelligence\DataTransfer\EventData;
use App\Domains\Intelligence\Actions\CreateEventAction;
use Illuminate\Support\Facades\DB;

/**
 * Event controller for managing conflict events
 */
class EventController extends Controller
{
    /**
     * Display a paginated list of events with filters
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = Event::query();

        // Filter by date range
        if ($request->has('start_date')) {
            $query->where('occurred_at', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->where('occurred_at', '<=', $request->end_date);
        }

        // Filter by event type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->withStatus($request->status);
        }

        // Filter by location radius (requires lat, lng, radius in km)
        if ($request->has(['lat', 'lng', 'radius'])) {
            $query->nearLocation(
                (float) $request->lat,
                (float) $request->lng,
                (float) $request->radius
            );
        }

        // Filter by actor
        if ($request->has('actor_id')) {
            $query->where('actor_id', $request->actor_id);
        }

        // Order by most recent
        $query->orderBy('occurred_at', 'desc');

        // Eager load relationships
        $query->with(['actor', 'media', 'sources', 'equipment']);

        $events = $query->paginate($this->perPage);

        return $this->paginated($events);
    }

    /**
     * Store a newly created event
     *
     * @param Request $request
     * @param CreateEventAction $createEventAction
     * @return JsonResponse
     */
    public function store(Request $request, CreateEventAction $createEventAction): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'type' => ['required', 'string', 'max:50'],
            'occurred_at' => ['required', 'date'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'location_name' => ['nullable', 'string', 'max:255'],
            'actor_id' => ['nullable', 'exists:actors,id'],
            'status' => ['required', 'in:draft,pending,verified,disputed'],
            'confidence' => ['required', 'in:confirmed,likely,unconfirmed'],
            'custom_fields' => ['nullable', 'array'],
        ]);

        $data = new EventData(
            title: $validated['title'],
            description: $validated['description'],
            type: $validated['type'],
            occurredAt: \Carbon\Carbon::parse($validated['occurred_at']),
            latitude: (float) $validated['latitude'],
            longitude: (float) $validated['longitude'],
            status: $validated['status'],
            confidence: $validated['confidence'],
            locationName: $validated['location_name'] ?? null,
            actorId: isset($validated['actor_id']) ? (int) $validated['actor_id'] : null,
            customFields: $validated['custom_fields'] ?? null,
        );

        $event = $createEventAction->execute($data, $request->user());

        return $this->success($event->load(['actor', 'media', 'sources']), 201);
    }

    /**
     * Display the specified event
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function show(string $uuid): JsonResponse
    {
        $event = Event::with(['actor', 'media', 'sources', 'equipment', 'versions'])
            ->where('uuid', $uuid)
            ->firstOrFail();

        return $this->success($event);
    }

    /**
     * Update the specified event
     *
     * @param Request $request
     * @param string $uuid
     * @return JsonResponse
     */
    public function update(Request $request, string $uuid): JsonResponse
    {
        $event = Event::where('uuid', $uuid)->firstOrFail();

        $this->authorize('update', $event);

        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string'],
            'type' => ['sometimes', 'string', 'max:50'],
            'occurred_at' => ['sometimes', 'date'],
            'latitude' => ['sometimes', 'numeric', 'between:-90,90'],
            'longitude' => ['sometimes', 'numeric', 'between:-180,180'],
            'location_name' => ['nullable', 'string', 'max:255'],
            'actor_id' => ['nullable', 'exists:actors,id'],
            'status' => ['sometimes', 'in:draft,pending,verified,disputed'],
            'confidence' => ['sometimes', 'in:confirmed,likely,unconfirmed'],
            'custom_fields' => ['nullable', 'array'],
        ]);

        // Update location if coordinates changed
        if (isset($validated['latitude']) || isset($validated['longitude'])) {
            $lat = $validated['latitude'] ?? $event->latitude;
            $lng = $validated['longitude'] ?? $event->longitude;

            $validated['location'] = DB::raw(sprintf('POINT(%f, %f)', $lng, $lat));
        }

        $event->update($validated);

        return $this->success($event->fresh(['actor', 'media', 'sources']));
    }

    /**
     * Remove the specified event
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function destroy(string $uuid): JsonResponse
    {
        $event = Event::where('uuid', $uuid)->firstOrFail();

        $this->authorize('delete', $event);

        $event->delete();

        return $this->success(['message' => 'Event deleted successfully']);
    }

    /**
     * Verify an event
     *
     * @param Request $request
     * @param string $uuid
     * @return JsonResponse
     */
    public function verify(Request $request, string $uuid): JsonResponse
    {
        $event = Event::where('uuid', $uuid)->firstOrFail();

        $this->authorize('verify', $event);

        $validated = $request->validate([
            'notes' => ['nullable', 'string'],
        ]);

        $event->update([
            'status' => 'verified',
            'verified_by' => $request->user()->id,
            'verified_at' => now(),
            'verification_score' => $event->verification_score + 1,
        ]);

        // Log verification in audit trail
        \App\Models\AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'event.verified',
            'auditable_type' => Event::class,
            'auditable_id' => $event->id,
            'properties' => $validated,
        ]);

        return $this->success($event->fresh());
    }

    /**
     * Dispute an event
     *
     * @param Request $request
     * @param string $uuid
     * @return JsonResponse
     */
    public function dispute(Request $request, string $uuid): JsonResponse
    {
        $event = Event::where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'reason' => ['required', 'string'],
            'evidence' => ['nullable', 'array'],
        ]);

        $event->update([
            'status' => 'disputed',
        ]);

        // Log dispute in audit trail
        \App\Models\AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'event.disputed',
            'auditable_type' => Event::class,
            'auditable_id' => $event->id,
            'properties' => $validated,
        ]);

        return $this->success($event->fresh());
    }

    /**
     * Get version history for an event
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function history(string $uuid): JsonResponse
    {
        $event = Event::where('uuid', $uuid)->firstOrFail();

        $versions = \App\Models\EntityVersion::where('entity_type', Event::class)
            ->where('entity_id', $event->id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->success($versions);
    }
}
