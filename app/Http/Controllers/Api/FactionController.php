<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Faction;

/**
 * Controller for managing factions and armed groups
 */
class FactionController extends Controller
{
    /**
     * Display a list of factions
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = Faction::query();

        // Filter by conflict
        if ($request->has('conflict_id')) {
            $query->where('conflict_id', $request->conflict_id);
        }

        // Filter by country
        if ($request->has('country_id')) {
            $query->where('country_id', $request->country_id);
        }

        // Filter by active status
        if ($request->has('active')) {
            $query->where('is_active', filter_var($request->active, FILTER_VALIDATE_BOOLEAN));
        }

        // Search by name
        if ($request->has('search')) {
            $search = $request->search;
            $query->where('name', 'LIKE', "%{$search}%");
        }

        // Order by name
        $query->orderBy('name', 'asc');

        $factions = $query->with('country')->get();

        return $this->success($factions);
    }

    /**
     * Display the specified faction
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $faction = Faction::with(['country', 'controlZones', 'events'])
            ->findOrFail($id);

        return $this->success($faction);
    }

    /**
     * Get control zones for a faction
     *
     * @param int $id
     * @return JsonResponse
     */
    public function zones(int $id): JsonResponse
    {
        $faction = Faction::findOrFail($id);

        // Get currently valid zones
        $zones = $faction->controlZones()
            ->where(function ($q) {
                $q->whereNull('valid_to')
                    ->orWhere('valid_to', '>=', now());
            })
            ->orderBy('valid_from', 'desc')
            ->get();

        return $this->success($zones);
    }
}
