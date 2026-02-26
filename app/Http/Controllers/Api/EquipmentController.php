<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\MilitaryEquipment;

/**
 * Controller for managing military equipment database
 */
class EquipmentController extends Controller
{
    /**
     * Display a paginated list of equipment with filters
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = MilitaryEquipment::query();

        // Filter by category
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        // Filter by sub-category
        if ($request->has('sub_category')) {
            $query->where('sub_category', $request->sub_category);
        }

        // Filter by country of origin
        if ($request->has('country')) {
            $query->where('country_of_origin', $request->country);
        }

        // Filter by active status
        if ($request->has('active')) {
            $query->where('is_active', filter_var($request->active, FILTER_VALIDATE_BOOLEAN));
        }

        // Order by name
        $query->orderBy('name', 'asc');

        $equipment = $query->paginate($this->perPage);

        return $this->paginated($equipment);
    }

    /**
     * Display the specified equipment
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function show(string $uuid): JsonResponse
    {
        $equipment = MilitaryEquipment::where('uuid', $uuid)->firstOrFail();

        return $this->success($equipment);
    }

    /**
     * Get all equipment categories
     *
     * @return JsonResponse
     */
    public function categories(): JsonResponse
    {
        $categories = MilitaryEquipment::select('category', 'sub_category')
            ->distinct()
            ->orderBy('category')
            ->orderBy('sub_category')
            ->get()
            ->groupBy('category')
            ->map(function ($items) {
                return $items->pluck('sub_category')->filter()->unique()->values();
            });

        return $this->success($categories);
    }

    /**
     * Search equipment by name or designation
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'query' => ['required', 'string', 'min:2'],
            'category' => ['nullable', 'string'],
        ]);

        $query = MilitaryEquipment::query();

        // Full-text search on name, designation, and aliases
        $searchTerm = $validated['query'];

        $query->where(function ($q) use ($searchTerm) {
            $q->where('name', 'LIKE', "%{$searchTerm}%")
                ->orWhere('designation', 'LIKE', "%{$searchTerm}%")
                ->orWhereRaw('JSON_SEARCH(aliases, "one", ?) IS NOT NULL', ["%{$searchTerm}%"]);
        });

        // Filter by category if provided
        if (isset($validated['category'])) {
            $query->where('category', $validated['category']);
        }

        $results = $query->limit(50)->get();

        return $this->success($results);
    }

    /**
     * Get events related to specific equipment
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function events(string $uuid): JsonResponse
    {
        $equipment = MilitaryEquipment::where('uuid', $uuid)->firstOrFail();

        $events = $equipment->events()
            ->with(['event', 'operatorFaction'])
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        return $this->paginated($events);
    }
}
