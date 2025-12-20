<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MilitaryEquipment;
use App\Models\EquipmentCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MilitaryEquipmentController extends Controller
{
    /**
     * Display a listing of military equipment.
     */
    public function index(Request $request): JsonResponse
    {
        $query = MilitaryEquipment::with(['country', 'category']);

        // Filter by category
        if ($request->has('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        // Filter by category slug
        if ($request->has('category')) {
            $category = EquipmentCategory::where('slug', $request->input('category'))->first();
            if ($category) {
                $categoryIds = [$category->id];
                // Include subcategories
                $subcategories = EquipmentCategory::where('parent_id', $category->id)->pluck('id');
                $categoryIds = array_merge($categoryIds, $subcategories->toArray());
                $query->whereIn('category_id', $categoryIds);
            }
        }

        // Filter by country
        if ($request->has('country_id')) {
            $query->where('country_id', $request->input('country_id'));
        }

        // Filter by country code
        if ($request->has('country')) {
            $query->whereHas('country', function ($q) use ($request) {
                $q->where('iso_code', strtoupper($request->input('country')));
            });
        }

        // Search
        if ($request->has('search')) {
            $term = $request->input('search');
            $query->where(function ($q) use ($term) {
                $q->where('designation', 'like', "%{$term}%")
                    ->orWhere('nato_designation', 'like', "%{$term}%")
                    ->orWhere('common_name', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%");
            });
        }

        // Filter by year range
        if ($request->has('year_from')) {
            $query->where('introduced_year', '>=', $request->input('year_from'));
        }
        if ($request->has('year_to')) {
            $query->where('introduced_year', '<=', $request->input('year_to'));
        }

        // Sorting
        $sortField = $request->input('sort', 'designation');
        $sortOrder = $request->input('order', 'asc');
        $allowedSorts = ['designation', 'common_name', 'introduced_year', 'estimated_produced', 'created_at'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortOrder === 'desc' ? 'desc' : 'asc');
        }

        // Pagination
        $perPage = min((int) $request->input('per_page', 25), 100);
        $equipment = $query->paginate($perPage);

        return response()->json([
            'data' => $equipment->items(),
            'meta' => [
                'current_page' => $equipment->currentPage(),
                'last_page' => $equipment->lastPage(),
                'per_page' => $equipment->perPage(),
                'total' => $equipment->total(),
            ],
            'links' => [
                'first' => $equipment->url(1),
                'last' => $equipment->url($equipment->lastPage()),
                'prev' => $equipment->previousPageUrl(),
                'next' => $equipment->nextPageUrl(),
            ],
        ]);
    }

    /**
     * Store a newly created equipment in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'designation' => 'required|string|max:255|unique:military_equipment,designation',
            'nato_designation' => 'nullable|string|max:255',
            'common_name' => 'nullable|string|max:255',
            'country_id' => 'required|exists:countries,id',
            'category_id' => 'required|exists:equipment_categories,id',
            'introduced_year' => 'nullable|integer|min:1900|max:' . (date('Y') + 5),
            'estimated_produced' => 'nullable|integer|min:0',
            'description' => 'nullable|string|max:5000',
            'specifications' => 'nullable|array',
            'image_url' => 'nullable|url|max:500',
        ]);

        $equipment = MilitaryEquipment::create([
            'uuid' => Str::uuid(),
            ...$validated,
            'specifications' => isset($validated['specifications']) ? json_encode($validated['specifications']) : null,
        ]);

        return response()->json([
            'message' => 'Equipment created successfully',
            'data' => $equipment->load(['country', 'category']),
        ], 201);
    }

    /**
     * Display the specified equipment.
     */
    public function show(string $id): JsonResponse
    {
        $equipment = MilitaryEquipment::with(['country', 'category', 'countryInventories.country'])
            ->where('id', $id)
            ->orWhere('uuid', $id)
            ->firstOrFail();

        return response()->json([
            'data' => $equipment,
        ]);
    }

    /**
     * Update the specified equipment in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $equipment = MilitaryEquipment::where('id', $id)
            ->orWhere('uuid', $id)
            ->firstOrFail();

        $validated = $request->validate([
            'designation' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('military_equipment', 'designation')->ignore($equipment->id),
            ],
            'nato_designation' => 'nullable|string|max:255',
            'common_name' => 'nullable|string|max:255',
            'country_id' => 'sometimes|exists:countries,id',
            'category_id' => 'sometimes|exists:equipment_categories,id',
            'introduced_year' => 'nullable|integer|min:1900|max:' . (date('Y') + 5),
            'estimated_produced' => 'nullable|integer|min:0',
            'description' => 'nullable|string|max:5000',
            'specifications' => 'nullable|array',
            'image_url' => 'nullable|url|max:500',
        ]);

        if (isset($validated['specifications'])) {
            $validated['specifications'] = json_encode($validated['specifications']);
        }

        $equipment->update($validated);

        return response()->json([
            'message' => 'Equipment updated successfully',
            'data' => $equipment->fresh(['country', 'category']),
        ]);
    }

    /**
     * Remove the specified equipment from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $equipment = MilitaryEquipment::where('id', $id)
            ->orWhere('uuid', $id)
            ->firstOrFail();

        $equipment->delete();

        return response()->json([
            'message' => 'Equipment deleted successfully',
        ]);
    }

    /**
     * Restore a soft-deleted equipment.
     */
    public function restore(string $id): JsonResponse
    {
        $equipment = MilitaryEquipment::withTrashed()
            ->where('id', $id)
            ->orWhere('uuid', $id)
            ->firstOrFail();

        $equipment->restore();

        return response()->json([
            'message' => 'Equipment restored successfully',
            'data' => $equipment->load(['country', 'category']),
        ]);
    }

    /**
     * Get equipment categories.
     */
    public function categories(): JsonResponse
    {
        $categories = EquipmentCategory::with('children')
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'data' => $categories,
        ]);
    }

    /**
     * Get equipment by country with inventory.
     */
    public function byCountry(int $countryId): JsonResponse
    {
        $equipment = MilitaryEquipment::with(['category'])
            ->whereHas('countryInventories', function ($q) use ($countryId) {
                $q->where('country_id', $countryId);
            })
            ->get()
            ->map(function ($item) use ($countryId) {
                $inventory = $item->countryInventories->where('country_id', $countryId)->first();
                return [
                    'equipment' => $item,
                    'quantity' => $inventory?->quantity ?? 0,
                    'status' => $inventory?->status ?? 'unknown',
                ];
            });

        return response()->json([
            'data' => $equipment,
        ]);
    }

    /**
     * Search equipment for autocomplete.
     */
    public function autocomplete(Request $request): JsonResponse
    {
        $term = $request->input('q', '');

        if (strlen($term) < 2) {
            return response()->json(['data' => []]);
        }

        $equipment = MilitaryEquipment::with(['country:id,name,iso_code', 'category:id,name,slug'])
            ->where('designation', 'like', "%{$term}%")
            ->orWhere('nato_designation', 'like', "%{$term}%")
            ->orWhere('common_name', 'like', "%{$term}%")
            ->limit(15)
            ->get(['id', 'uuid', 'designation', 'nato_designation', 'common_name', 'country_id', 'category_id']);

        return response()->json([
            'data' => $equipment,
        ]);
    }

    /**
     * Get statistics about equipment.
     */
    public function stats(): JsonResponse
    {
        $stats = [
            'total_equipment' => MilitaryEquipment::count(),
            'by_category' => EquipmentCategory::withCount('equipment')
                ->whereNull('parent_id')
                ->get()
                ->map(fn($c) => ['name' => $c->name, 'slug' => $c->slug, 'count' => $c->equipment_count]),
            'by_country' => MilitaryEquipment::selectRaw('country_id, COUNT(*) as count')
                ->with('country:id,name,iso_code')
                ->groupBy('country_id')
                ->orderByDesc('count')
                ->limit(10)
                ->get(),
            'by_decade' => MilitaryEquipment::selectRaw('FLOOR(introduced_year / 10) * 10 as decade, COUNT(*) as count')
                ->whereNotNull('introduced_year')
                ->groupBy('decade')
                ->orderBy('decade')
                ->get(),
        ];

        return response()->json([
            'data' => $stats,
        ]);
    }
}
