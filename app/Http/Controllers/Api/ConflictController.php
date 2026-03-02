<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConflictController extends Controller
{
    /**
     * List all conflicts.
     */
    public function index(Request $request): JsonResponse
    {
        $query = DB::table('conflicts')
            ->orderBy('start_date', 'desc');

        if ($request->has('status')) {
            $status = $request->input('status');
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive' || $status === 'concluded') {
                $query->where('is_active', false);
            }
            // Ignore other statuses if column doesn't exist
        }

        if ($request->has('region')) {
            $query->where('region', $request->input('region'));
        }

        if ($request->has('intensity')) {
            $query->where('intensity', $request->input('intensity'));
        }

        if ($request->has('type')) {
            $query->where('type', $request->input('type'));
        }

        $conflicts = $query->paginate($request->input('per_page', 25));

        return response()->json([
            'data' => $conflicts->items(),
            'meta' => [
                'current_page' => $conflicts->currentPage(),
                'total' => $conflicts->total(),
                'active_count' => DB::table('conflicts')->where('is_active', true)->count(),
            ],
        ]);
    }

    /**
     * Get active conflicts only.
     */
    public function active(): JsonResponse
    {
        $conflicts = DB::table('conflicts')
            ->where('is_active', true)
            ->orderBy('intensity_level', 'desc')
            ->get();

        return response()->json([
            'data' => $conflicts,
            'count' => $conflicts->count(),
        ]);
    }

    /**
     * Show a specific conflict.
     */
    public function show(string $identifier): JsonResponse
    {
        $conflict = DB::table('conflicts')
            ->where('uuid', $identifier)
            ->when(is_numeric($identifier), fn ($q) => $q->orWhere('id', $identifier))
            ->first();

        if (!$conflict) {
            return response()->json(['message' => 'Conflict not found'], 404);
        }

        // Get related data
        $events = DB::table('events')
            ->where('conflict_id', $conflict->id)
            ->orderBy('date', 'desc')
            ->limit(20)
            ->get();

        $zones = DB::table('zones')
            ->where('conflict_id', $conflict->id)
            ->get();

        $eventStats = DB::table('events')
            ->select([
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(casualties_military) as military_casualties'),
                DB::raw('SUM(casualties_civilian) as civilian_casualties'),
            ])
            ->where('conflict_id', $conflict->id)
            ->first();

        return response()->json([
            'data' => $conflict,
            'recent_events' => $events,
            'zones' => $zones,
            'statistics' => [
                'total_events' => $eventStats->total ?? 0,
                'military_casualties' => $eventStats->military_casualties ?? 0,
                'civilian_casualties' => $eventStats->civilian_casualties ?? 0,
                'zones_count' => $zones->count(),
            ],
        ]);
    }

    /**
     * Get conflict events.
     */
    public function events(Request $request, string $identifier): JsonResponse
    {
        $conflict = DB::table('conflicts')
            ->where('uuid', $identifier)
            ->when(is_numeric($identifier), fn ($q) => $q->orWhere('id', $identifier))
            ->first();

        if (!$conflict) {
            return response()->json(['message' => 'Conflict not found'], 404);
        }

        $query = DB::table('events')
            ->where('conflict_id', $conflict->id)
            ->orderBy('date', 'desc');

        if ($request->has('event_type')) {
            $query->where('event_type', $request->input('event_type'));
        }

        if ($request->has('date_from')) {
            $query->where('date', '>=', $request->input('date_from'));
        }

        if ($request->has('date_to')) {
            $query->where('date', '<=', $request->input('date_to'));
        }

        $events = $query->paginate($request->input('per_page', 25));

        return response()->json([
            'data' => $events->items(),
            'meta' => [
                'current_page' => $events->currentPage(),
                'total' => $events->total(),
            ],
        ]);
    }

    /**
     * Get conflict zones.
     */
    public function zones(string $identifier): JsonResponse
    {
        $conflict = DB::table('conflicts')
            ->where('uuid', $identifier)
            ->when(is_numeric($identifier), fn ($q) => $q->orWhere('id', $identifier))
            ->first();

        if (!$conflict) {
            return response()->json(['message' => 'Conflict not found'], 404);
        }

        $zones = DB::table('zones')
            ->where('conflict_id', $conflict->id)
            ->get();

        return response()->json(['data' => $zones]);
    }

    /**
     * Get conflict statistics.
     */
    public function statistics(string $identifier): JsonResponse
    {
        $conflict = DB::table('conflicts')
            ->where('uuid', $identifier)
            ->when(is_numeric($identifier), fn ($q) => $q->orWhere('id', $identifier))
            ->first();

        if (!$conflict) {
            return response()->json(['message' => 'Conflict not found'], 404);
        }

        $eventsByType = DB::table('events')
            ->select('event_type', DB::raw('COUNT(*) as count'))
            ->where('conflict_id', $conflict->id)
            ->groupBy('event_type')
            ->get();

        $eventsByMonth = DB::table('events')
            ->select(
                DB::raw('DATE_FORMAT(date, "%Y-%m") as month'),
                DB::raw('COUNT(*) as count')
            )
            ->where('conflict_id', $conflict->id)
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();

        $casualties = DB::table('events')
            ->select([
                DB::raw('SUM(casualties_military) as military'),
                DB::raw('SUM(casualties_civilian) as civilian'),
            ])
            ->where('conflict_id', $conflict->id)
            ->first();

        $zonesByController = DB::table('zones')
            ->select('controller', DB::raw('COUNT(*) as count'), DB::raw('SUM(area_sq_km) as total_area'))
            ->where('conflict_id', $conflict->id)
            ->groupBy('controller')
            ->get();

        return response()->json([
            'conflict' => $conflict->name,
            'events_by_type' => $eventsByType,
            'events_by_month' => $eventsByMonth,
            'casualties' => [
                'military' => $casualties->military ?? 0,
                'civilian' => $casualties->civilian ?? 0,
            ],
            'territorial_control' => $zonesByController,
        ]);
    }

    /**
     * Get conflict actors.
     */
    public function actors(string $identifier): JsonResponse
    {
        $conflict = DB::table('conflicts')
            ->where('uuid', $identifier)
            ->when(is_numeric($identifier), fn ($q) => $q->orWhere('id', $identifier))
            ->first();

        if (!$conflict) {
            return response()->json(['message' => 'Conflict not found'], 404);
        }

        $primaryParties = json_decode($conflict->primary_parties ?? '[]', true);
        $secondaryParties = json_decode($conflict->secondary_parties ?? '[]', true);

        // Try to find actor details
        $actorDetails = [];
        $allParties = array_merge($primaryParties, $secondaryParties);

        $potentialActors = collect();
        $potentialCountries = collect();

        if (!empty($allParties)) {
            // Optimize: Fetch all potential actors in one query
            $potentialActors = DB::table('actors')
                ->where(function ($query) use ($allParties) {
                    foreach ($allParties as $party) {
                        $query->orWhere('name', 'like', '%' . $party . '%');
                    }
                })
                ->get();

            // Optimize: Fetch all potential countries in one query
            $potentialCountries = DB::table('countries')
                ->where(function ($query) use ($allParties) {
                    foreach ($allParties as $party) {
                        $query->orWhere('name', 'like', '%' . $party . '%');
                    }
                })
                ->get();
        }

        foreach ($allParties as $party) {
            // Find in actors collection (case-insensitive check)
            $actor = $potentialActors->first(function ($item) use ($party) {
                return stripos($item->name, $party) !== false;
            });

            if ($actor) {
                $actorDetails[] = $actor;
            } else {
                // Check if it's a country
                $country = $potentialCountries->first(function ($item) use ($party) {
                    return stripos($item->name, $party) !== false;
                });

                if ($country) {
                    $actorDetails[] = [
                        'name' => $country->name,
                        'type' => 'state',
                        'iso_code' => $country->iso_code,
                    ];
                }
            }
        }

        return response()->json([
            'primary_parties' => $primaryParties,
            'secondary_parties' => $secondaryParties,
            'actor_details' => $actorDetails,
        ]);
    }

    /**
     * Get regions.
     */
    public function regions(): JsonResponse
    {
        $regions = DB::table('conflicts')
            ->select('region', DB::raw('COUNT(*) as count'))
            ->whereNotNull('region')
            ->groupBy('region')
            ->orderBy('count', 'desc')
            ->get();

        return response()->json(['data' => $regions]);
    }

    /**
     * Get conflict types.
     */
    public function types(): JsonResponse
    {
        return response()->json([
            'types' => [
                'interstate' => 'Interstate War',
                'civil_war' => 'Civil War',
                'insurgency' => 'Insurgency',
                'territorial' => 'Territorial Dispute',
                'asymmetric' => 'Asymmetric Conflict',
                'proxy' => 'Proxy War',
            ],
            'intensities' => [
                'high' => 'High Intensity',
                'medium' => 'Medium Intensity',
                'low' => 'Low Intensity',
            ],
            'statuses' => [
                'active' => 'Active Combat',
                'tension' => 'Elevated Tensions',
                'frozen' => 'Frozen Conflict',
                'concluded' => 'Concluded',
            ],
        ]);
    }

    /**
     * Search conflicts.
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->input('q');

        if (!$query || strlen($query) < 2) {
            return response()->json(['data' => []]);
        }

        $conflicts = DB::table('conflicts')
            ->where('name', 'like', '%' . $query . '%')
            ->orWhere('description', 'like', '%' . $query . '%')
            ->orWhere('keywords', 'like', '%' . $query . '%')
            ->limit(10)
            ->get();

        return response()->json(['data' => $conflicts]);
    }
}
