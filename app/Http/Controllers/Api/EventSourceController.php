<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\EventSource;

/**
 * Controller for managing event sources and references
 */
class EventSourceController extends Controller
{
    /**
     * Add a source to an event
     *
     * @param Request $request
     * @param string $uuid
     * @return JsonResponse
     */
    public function store(Request $request, string $uuid): JsonResponse
    {
        $event = Event::where('uuid', $uuid)->firstOrFail();

        $this->authorize('update', $event);

        $validated = $request->validate([
            'url' => ['required', 'url', 'max:500'],
            'source_type' => ['required', 'in:social_media,news,official,satellite,osint,other'],
            'source_name' => ['nullable', 'string', 'max:255'],
            'archive_url' => ['nullable', 'url', 'max:500'],
            'reliability' => ['required', 'in:high,medium,low,unknown'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $source = EventSource::create([
            'event_id' => $event->id,
            'url' => $validated['url'],
            'source_type' => $validated['source_type'],
            'source_name' => $validated['source_name'] ?? null,
            'accessed_at' => now(),
            'archive_url' => $validated['archive_url'] ?? null,
            'reliability' => $validated['reliability'],
            'notes' => $validated['notes'] ?? null,
        ]);

        return $this->success($source, 201);
    }

    /**
     * Remove a source from an event
     *
     * @param string $uuid
     * @param int $sourceId
     * @return JsonResponse
     */
    public function destroy(string $uuid, int $sourceId): JsonResponse
    {
        $event = Event::where('uuid', $uuid)->firstOrFail();

        $this->authorize('update', $event);

        $source = EventSource::where('event_id', $event->id)
            ->where('id', $sourceId)
            ->firstOrFail();

        $source->delete();

        return $this->success(['message' => 'Source removed successfully']);
    }
}
