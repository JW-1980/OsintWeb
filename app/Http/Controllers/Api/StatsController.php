<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\ControlZone;
use App\Models\MilitaryEquipment;
use App\Models\Actor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

/**
 * Controller for analytics and statistics
 */
class StatsController extends Controller
{
    /**
     * Get overview statistics
     *
     * @return JsonResponse
     */
    public function overview(): JsonResponse
    {
        $stats = Cache::remember('stats.overview', 3600, function () {
            return [
                'total_events' => Event::count(),
                'verified_events' => Event::where('status', 'verified')->count(),
                'total_actors' => Actor::count(),
                'active_conflicts' => DB::table('conflicts')->where('is_active', true)->count(),
                'control_zones' => ControlZone::whereNull('valid_to')->count(),
                'equipment_types' => MilitaryEquipment::count(),
                'recent_events' => Event::where('occurred_at', '>=', now()->subDays(7))->count(),
                'events_this_month' => Event::whereYear('occurred_at', now()->year)
                    ->whereMonth('occurred_at', now()->month)
                    ->count(),
            ];
        });

        return $this->success($stats);
    }

    /**
     * Get equipment loss statistics
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function losses(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'actor_id' => ['nullable', 'exists:actors,id'],
        ]);

        $query = DB::table('event_equipment')
            ->join('events', 'event_equipment.event_id', '=', 'events.id')
            ->join('military_equipment', 'event_equipment.equipment_id', '=', 'military_equipment.id')
            ->select(
                'military_equipment.category',
                'event_equipment.status',
                DB::raw('SUM(event_equipment.quantity) as total')
            );

        if (isset($validated['start_date'])) {
            $query->where('events.occurred_at', '>=', $validated['start_date']);
        }

        if (isset($validated['end_date'])) {
            $query->where('events.occurred_at', '<=', $validated['end_date']);
        }

        if (isset($validated['actor_id'])) {
            $query->where('event_equipment.operator_faction_id', $validated['actor_id']);
        }

        $losses = $query->groupBy('military_equipment.category', 'event_equipment.status')
            ->get()
            ->groupBy('category')
            ->map(function ($items) {
                return $items->groupBy('status')->map(function ($statusItems) {
                    return $statusItems->sum('total');
                });
            });

        return $this->success($losses);
    }

    /**
     * Get event statistics
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function events(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'group_by' => ['nullable', 'in:day,week,month,type,status'],
        ]);

        $query = Event::query();

        if (isset($validated['start_date'])) {
            $query->where('occurred_at', '>=', $validated['start_date']);
        }

        if (isset($validated['end_date'])) {
            $query->where('occurred_at', '<=', $validated['end_date']);
        }

        $groupBy = $validated['group_by'] ?? 'type';

        $stats = match ($groupBy) {
            'day' => $query->selectRaw('DATE(occurred_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->orderBy('date')
                ->get(),

            'week' => $query->selectRaw('YEARWEEK(occurred_at) as week, COUNT(*) as count')
                ->groupBy('week')
                ->orderBy('week')
                ->get(),

            'month' => $query->selectRaw('DATE_FORMAT(occurred_at, "%Y-%m") as month, COUNT(*) as count')
                ->groupBy('month')
                ->orderBy('month')
                ->get(),

            'type' => $query->select('event_type_id', DB::raw('COUNT(*) as count'))
                ->groupBy('event_type_id')
                ->orderBy('count', 'desc')
                ->get(),

            'status' => $query->select('status', DB::raw('COUNT(*) as count'))
                ->groupBy('status')
                ->get(),

            default => $query->select('event_type_id', DB::raw('COUNT(*) as count'))
                ->groupBy('event_type_id')
                ->orderBy('count', 'desc')
                ->get(),
        };

        return $this->success($stats);
    }

    /**
     * Get timeline data for visualization
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function timeline(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date'],
            'interval' => ['nullable', 'in:day,week,month'],
            'event_type' => ['nullable', 'string'],
        ]);

        $interval = $validated['interval'] ?? 'day';

        $query = Event::query()
            ->where('occurred_at', '>=', $validated['start_date'])
            ->where('occurred_at', '<=', $validated['end_date']);

        if (isset($validated['event_type'])) {
            $query->where('event_type_id', $validated['event_type']);
        }

        $dateFormat = match ($interval) {
            'day' => '%Y-%m-%d',
            'week' => '%Y-%u',
            'month' => '%Y-%m',
            default => '%Y-%m-%d',
        };

        $timeline = $query->selectRaw("DATE_FORMAT(occurred_at, '{$dateFormat}') as period, event_type_id as type, COUNT(*) as count")
            ->groupBy('period', 'event_type_id')
            ->orderBy('period')
            ->get()
            ->groupBy('period')
            ->map(function ($items) {
                return [
                    'total' => $items->sum('count'),
                    'by_type' => $items->pluck('count', 'type'),
                ];
            });

        return $this->success($timeline);
    }

    /**
     * Get geographic distribution heatmap data
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function heatmap(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'event_type' => ['nullable', 'string'],
        ]);

        $query = Event::select('latitude', 'longitude', DB::raw('COUNT(*) as weight'));

        if (isset($validated['start_date'])) {
            $query->where('occurred_at', '>=', $validated['start_date']);
        }

        if (isset($validated['end_date'])) {
            $query->where('occurred_at', '<=', $validated['end_date']);
        }

        if (isset($validated['event_type'])) {
            $query->where('event_type_id', $validated['event_type']);
        }

        $heatmapData = $query->groupBy('latitude', 'longitude')
            ->orderBy('weight', 'desc')
            ->limit(1000)
            ->get()
            ->map(function ($item) {
                return [
                    'lat' => $item->latitude,
                    'lng' => $item->longitude,
                    'weight' => $item->weight,
                ];
            });

        return $this->success($heatmapData);
    }
}
