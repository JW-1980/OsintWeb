<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\ControlZone;
use Illuminate\Support\Facades\DB;

/**
 * Controller for managing territorial control zones
 */
class ControlZoneController extends Controller
{
    /**
     * Display a paginated list of control zones
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = ControlZone::query();

        // Filter by date (show zones valid at specific date)
        if ($request->has('date')) {
            $date = $request->date;
            $query->where('valid_from', '<=', $date)
                ->where(function ($q) use ($date) {
                    $q->whereNull('valid_to')
                        ->orWhere('valid_to', '>=', $date);
                });
        } else {
            // Default: show only currently valid zones
            $query->whereNull('valid_to')
                ->orWhere('valid_to', '>=', now());
        }

        // Filter by controller (faction/country)
        if ($request->has('controller_id')) {
            $query->where('controller_id', $request->controller_id);
        }

        // Filter by control type
        if ($request->has('control_type')) {
            $query->where('control_type', $request->control_type);
        }

        // Filter by confidence level
        if ($request->has('confidence')) {
            $query->where('confidence', $request->confidence);
        }

        // Eager load relationships
        $query->with('controller');

        // Order by most recent
        $query->orderBy('valid_from', 'desc');

        $zones = $query->paginate($this->perPage);

        return $this->paginated($zones);
    }

    /**
     * Store a newly created control zone
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'geometry' => ['required', 'array'],
            'geometry.type' => ['required', 'in:Polygon,MultiPolygon'],
            'geometry.coordinates' => ['required', 'array'],
            'controller_id' => ['required', 'exists:actors,id'],
            'control_type' => ['required', 'in:full,contested,claimed'],
            'valid_from' => ['required', 'date'],
            'valid_to' => ['nullable', 'date', 'after:valid_from'],
            'source_url' => ['nullable', 'url', 'max:500'],
            'confidence' => ['required', 'in:confirmed,likely,unconfirmed'],
            'notes' => ['nullable', 'string'],
        ]);

        // Convert GeoJSON to MySQL geometry format using parameter binding
        $geometryJson = json_encode($validated['geometry']);
        $geometryValue = DB::selectOne('SELECT ST_GeomFromGeoJSON(?) as geom', [$geometryJson])->geom;

        $zone = ControlZone::create([
            'uuid' => \Illuminate\Support\Str::uuid(),
            'name' => $validated['name'],
            'geometry' => $geometryValue,
            'controller_id' => $validated['controller_id'],
            'control_type' => $validated['control_type'],
            'valid_from' => $validated['valid_from'],
            'valid_to' => $validated['valid_to'] ?? null,
            'source_url' => $validated['source_url'] ?? null,
            'confidence' => $validated['confidence'],
            'notes' => $validated['notes'] ?? null,
            'created_by' => $request->user()->id,
        ]);

        return $this->success($zone->load('controller'), 201);
    }

    /**
     * Display the specified control zone
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function show(string $uuid): JsonResponse
    {
        $zone = ControlZone::with(['controller', 'versions'])
            ->where('uuid', $uuid)
            ->firstOrFail();

        return $this->success($zone);
    }

    /**
     * Update the specified control zone
     *
     * @param Request $request
     * @param string $uuid
     * @return JsonResponse
     */
    public function update(Request $request, string $uuid): JsonResponse
    {
        $zone = ControlZone::where('uuid', $uuid)->firstOrFail();

        $this->authorize('update', $zone);

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'geometry' => ['sometimes', 'array'],
            'geometry.type' => ['required_with:geometry', 'in:Polygon,MultiPolygon'],
            'geometry.coordinates' => ['required_with:geometry', 'array'],
            'controller_id' => ['sometimes', 'exists:actors,id'],
            'control_type' => ['sometimes', 'in:full,contested,claimed'],
            'valid_from' => ['sometimes', 'date'],
            'valid_to' => ['nullable', 'date'],
            'source_url' => ['nullable', 'url', 'max:500'],
            'confidence' => ['sometimes', 'in:confirmed,likely,unconfirmed'],
            'notes' => ['nullable', 'string'],
        ]);

        // Convert geometry if provided using parameter binding
        if (isset($validated['geometry'])) {
            $geometryJson = json_encode($validated['geometry']);
            $validated['geometry'] = DB::selectOne('SELECT ST_GeomFromGeoJSON(?) as geom', [$geometryJson])->geom;
        }

        $zone->update($validated);

        return $this->success($zone->fresh('controller'));
    }

    /**
     * Remove the specified control zone
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function destroy(string $uuid): JsonResponse
    {
        $zone = ControlZone::where('uuid', $uuid)->firstOrFail();

        $this->authorize('delete', $zone);

        $zone->delete();

        return $this->success(['message' => 'Control zone deleted successfully']);
    }

    /**
     * Get control zones valid at a specific date
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function atDate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
        ]);

        $date = $validated['date'];

        $zones = ControlZone::where('valid_from', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('valid_to')
                    ->orWhere('valid_to', '>=', $date);
            })
            ->with('controller')
            ->get();

        return $this->success($zones);
    }

    /**
     * Get version history for a control zone
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function history(string $uuid): JsonResponse
    {
        $zone = ControlZone::where('uuid', $uuid)->firstOrFail();

        $versions = \App\Models\EntityVersion::where('entity_type', ControlZone::class)
            ->where('entity_id', $zone->id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->success($versions);
    }
}
