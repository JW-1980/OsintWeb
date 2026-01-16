<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TimelineController extends Controller
{
    /**
     * Get timeline of events.
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'conflict_id' => 'nullable|exists:conflicts,id',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
            'event_types' => 'nullable|array',
            'countries' => 'nullable|array',
            'actors' => 'nullable|array',
            'granularity' => 'nullable|in:hour,day,week,month',
        ]);

        $query = DB::table('events')
            ->select([
                'events.uuid',
                'events.title',
                'events.date',
                'events.time',
                'events.event_type',
                'events.latitude',
                'events.longitude',
                'events.location_name',
                'events.casualties_military',
                'events.casualties_civilian',
                'conflicts.name as conflict_name',
            ])
            ->leftJoin('conflicts', 'events.conflict_id', '=', 'conflicts.id')
            ->orderBy('events.date', 'asc')
            ->orderBy('events.time', 'asc');

        if (isset($validated['conflict_id'])) {
            $query->where('events.conflict_id', $validated['conflict_id']);
        }

        if (isset($validated['date_from'])) {
            $query->where('events.date', '>=', $validated['date_from']);
        }

        if (isset($validated['date_to'])) {
            $query->where('events.date', '<=', $validated['date_to']);
        }

        if (isset($validated['event_types'])) {
            $query->whereIn('events.event_type', $validated['event_types']);
        }

        $events = $query->get();

        // Group by granularity
        $granularity = $validated['granularity'] ?? 'day';
        $grouped = $this->groupByGranularity($events, $granularity);

        return response()->json([
            'data' => $events,
            'grouped' => $grouped,
            'meta' => [
                'total_events' => $events->count(),
                'date_range' => [
                    'from' => $events->min('date'),
                    'to' => $events->max('date'),
                ],
                'granularity' => $granularity,
            ],
        ]);
    }

    /**
     * Compare two time periods.
     */
    public function compare(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'period1_from' => 'required|date',
            'period1_to' => 'required|date',
            'period2_from' => 'required|date',
            'period2_to' => 'required|date',
            'conflict_id' => 'nullable|exists:conflicts,id',
        ]);

        $query1 = DB::table('events')
            ->whereBetween('date', [$validated['period1_from'], $validated['period1_to']]);

        $query2 = DB::table('events')
            ->whereBetween('date', [$validated['period2_from'], $validated['period2_to']]);

        if (isset($validated['conflict_id'])) {
            $query1->where('conflict_id', $validated['conflict_id']);
            $query2->where('conflict_id', $validated['conflict_id']);
        }

        $period1 = [
            'events_count' => $query1->count(),
            'casualties_military' => $query1->sum('casualties_military'),
            'casualties_civilian' => $query1->sum('casualties_civilian'),
            'by_type' => DB::table('events')
                ->select('event_type', DB::raw('count(*) as count'))
                ->whereBetween('date', [$validated['period1_from'], $validated['period1_to']])
                ->groupBy('event_type')
                ->get(),
        ];

        $period2 = [
            'events_count' => $query2->count(),
            'casualties_military' => $query2->sum('casualties_military'),
            'casualties_civilian' => $query2->sum('casualties_civilian'),
            'by_type' => DB::table('events')
                ->select('event_type', DB::raw('count(*) as count'))
                ->whereBetween('date', [$validated['period2_from'], $validated['period2_to']])
                ->groupBy('event_type')
                ->get(),
        ];

        return response()->json([
            'period1' => $period1,
            'period2' => $period2,
            'comparison' => [
                'events_change' => $period2['events_count'] - $period1['events_count'],
                'events_change_percent' => $period1['events_count'] > 0
                    ? round(($period2['events_count'] - $period1['events_count']) / $period1['events_count'] * 100, 2)
                    : 0,
            ],
        ]);
    }

    /**
     * Get territorial changes over time.
     */
    public function territorial(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'conflict_id' => 'nullable|exists:conflicts,id',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
        ]);

        $query = DB::table('zones')
            ->select([
                'zones.*',
                'conflicts.name as conflict_name',
            ])
            ->leftJoin('conflicts', 'zones.conflict_id', '=', 'conflicts.id')
            ->orderBy('zones.date_established', 'asc');

        if (isset($validated['conflict_id'])) {
            $query->where('zones.conflict_id', $validated['conflict_id']);
        }

        if (isset($validated['date_from'])) {
            $query->where('zones.date_established', '>=', $validated['date_from']);
        }

        if (isset($validated['date_to'])) {
            $query->where('zones.date_established', '<=', $validated['date_to']);
        }

        $zones = $query->get();

        // Group by date for animation
        $zonesByDate = $zones->groupBy('date_established');

        return response()->json([
            'data' => $zones,
            'by_date' => $zonesByDate,
            'meta' => [
                'total_zones' => $zones->count(),
            ],
        ]);
    }

    /**
     * Get playback data for map animation.
     */
    public function playback(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'conflict_id' => 'required|exists:conflicts,id',
            'date_from' => 'required|date',
            'date_to' => 'required|date',
            'interval' => 'nullable|in:day,week,month',
        ]);

        $interval = $validated['interval'] ?? 'day';

        // Get all events in the period
        $events = DB::table('events')
            ->where('conflict_id', $validated['conflict_id'])
            ->whereBetween('date', [$validated['date_from'], $validated['date_to']])
            ->orderBy('date', 'asc')
            ->get();

        // Get all zone changes
        $zones = DB::table('zones')
            ->where('conflict_id', $validated['conflict_id'])
            ->whereBetween('date_established', [$validated['date_from'], $validated['date_to']])
            ->orderBy('date_established', 'asc')
            ->get();

        // Build frames for animation
        $frames = $this->buildPlaybackFrames(
            $validated['date_from'],
            $validated['date_to'],
            $interval,
            $events,
            $zones
        );

        return response()->json([
            'frames' => $frames,
            'meta' => [
                'total_frames' => count($frames),
                'interval' => $interval,
                'events_count' => $events->count(),
                'zones_count' => $zones->count(),
            ],
        ]);
    }

    /**
     * Build chronological investigation timeline.
     */
    public function investigation(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'events' => 'required|array',
            'events.*' => 'uuid|exists:events,uuid',
            'annotations' => 'nullable|array',
        ]);

        // Get the specified events in order
        $events = DB::table('events')
            ->whereIn('uuid', $validated['events'])
            ->orderBy('date', 'asc')
            ->orderBy('time', 'asc')
            ->get();

        // Reorder based on input order if needed
        $orderedEvents = collect($validated['events'])->map(function ($uuid) use ($events) {
            return $events->firstWhere('uuid', $uuid);
        })->filter();

        return response()->json([
            'title' => $validated['title'],
            'events' => $orderedEvents,
            'annotations' => $validated['annotations'] ?? [],
            'meta' => [
                'event_count' => $orderedEvents->count(),
                'date_range' => [
                    'from' => $orderedEvents->min('date'),
                    'to' => $orderedEvents->max('date'),
                ],
            ],
        ]);
    }

    /**
     * Get conflict phases and milestones.
     */
    public function milestones(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'conflict_id' => 'required|exists:conflicts,id',
        ]);

        // Get significant events (high casualties, territorial changes, etc.)
        $milestones = DB::table('events')
            ->where('conflict_id', $validated['conflict_id'])
            ->where(function ($query) {
                $query->where('event_type', 'territorial_change')
                    ->orWhere('casualties_military', '>', 100)
                    ->orWhere('casualties_civilian', '>', 50)
                    ->orWhere('verification_level', 'confirmed');
            })
            ->orderBy('date', 'asc')
            ->get();

        return response()->json([
            'data' => $milestones,
            'meta' => [
                'total_milestones' => $milestones->count(),
            ],
        ]);
    }

    /**
     * Group events by time granularity.
     */
    private function groupByGranularity($events, string $granularity): array
    {
        $grouped = [];

        foreach ($events as $event) {
            $key = match ($granularity) {
                'hour' => substr($event->date, 0, 10) . ' ' . substr($event->time ?? '00:00', 0, 2) . ':00',
                'day' => $event->date,
                'week' => date('Y-W', strtotime($event->date)),
                'month' => substr($event->date, 0, 7),
                default => $event->date,
            };

            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'period' => $key,
                    'events' => [],
                    'count' => 0,
                    'casualties_military' => 0,
                    'casualties_civilian' => 0,
                ];
            }

            $grouped[$key]['events'][] = $event;
            $grouped[$key]['count']++;
            $grouped[$key]['casualties_military'] += $event->casualties_military ?? 0;
            $grouped[$key]['casualties_civilian'] += $event->casualties_civilian ?? 0;
        }

        return array_values($grouped);
    }

    /**
     * Build animation frames for playback.
     */
    private function buildPlaybackFrames(
        string $dateFrom,
        string $dateTo,
        string $interval,
        $events,
        $zones
    ): array {
        $frames = [];
        $current = strtotime($dateFrom);
        $end = strtotime($dateTo);

        while ($current <= $end) {
            $currentDate = date('Y-m-d', $current);

            $frameEvents = $events->filter(function ($event) use ($currentDate) {
                return $event->date <= $currentDate;
            });

            $frameZones = $zones->filter(function ($zone) use ($currentDate) {
                return $zone->date_established <= $currentDate;
            });

            $frames[] = [
                'date' => $currentDate,
                'events' => $frameEvents->values(),
                'zones' => $frameZones->values(),
                'cumulative_events' => $frameEvents->count(),
            ];

            $current = match ($interval) {
                'day' => strtotime('+1 day', $current),
                'week' => strtotime('+1 week', $current),
                'month' => strtotime('+1 month', $current),
                default => strtotime('+1 day', $current),
            };
        }

        return $frames;
    }
}
