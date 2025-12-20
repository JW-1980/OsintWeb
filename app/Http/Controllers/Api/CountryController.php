<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Country;

/**
 * Controller for managing countries
 */
class CountryController extends Controller
{
    /**
     * Display a list of all countries
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = Country::query();

        // Filter by region
        if ($request->has('region')) {
            $query->where('region', $request->region);
        }

        // Search by name
        if ($request->has('search')) {
            $search = $request->search;
            $query->where('name', 'LIKE', "%{$search}%");
        }

        // Order by name
        $query->orderBy('name', 'asc');

        $countries = $query->get();

        return $this->success($countries);
    }

    /**
     * Display the specified country
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $country = Country::with(['equipment', 'factions'])->findOrFail($id);

        return $this->success($country);
    }

    /**
     * Get equipment inventory for a country
     *
     * @param int $id
     * @return JsonResponse
     */
    public function equipment(int $id): JsonResponse
    {
        $country = Country::findOrFail($id);

        $equipment = $country->equipment()
            ->with('category')
            ->orderBy('category')
            ->orderBy('name')
            ->paginate($this->perPage);

        return $this->paginated($equipment);
    }

    /**
     * Get factions associated with a country
     *
     * @param int $id
     * @return JsonResponse
     */
    public function factions(int $id): JsonResponse
    {
        $country = Country::findOrFail($id);

        $factions = $country->factions()
            ->with('controlZones')
            ->orderBy('name')
            ->get();

        return $this->success($factions);
    }
}
