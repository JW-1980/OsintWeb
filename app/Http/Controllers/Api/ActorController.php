<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Actor;
use Illuminate\Support\Facades\DB;

/**
 * Controller for managing actors (conflict parties, organizations, entities)
 */
class ActorController extends Controller
{
    /**
     * Display a paginated list of actors
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = Actor::query();

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Filter by active conflicts
        if ($request->has('active')) {
            $active = filter_var($request->active, FILTER_VALIDATE_BOOLEAN);
            if ($active) {
                $query->whereHas('conflicts', function ($q) {
                    $q->where('is_active', true);
                });
            }
        }

        // Search by name
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('short_name', 'LIKE', "%{$search}%")
                    ->orWhereHas('aliases', function ($aq) use ($search) {
                        $aq->where('alias', 'LIKE', "%{$search}%");
                    });
            });
        }

        // Order by name
        $query->orderBy('name', 'asc');

        // Eager load relationships
        $query->with(['conflicts', 'aliases']);

        $actors = $query->paginate($this->perPage);

        return $this->paginated($actors);
    }

    /**
     * Display the specified actor
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $actor = Actor::with([
            'conflicts',
            'aliases',
            'relationships.relatedActor',
            'events' => function ($q) {
                $q->orderBy('occurred_at', 'desc')->limit(10);
            }
        ])->findOrFail($id);

        return $this->success($actor);
    }

    /**
     * Autocomplete search with priority sorting for active conflicts
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function autocomplete(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'query' => ['required', 'string', 'min:1'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $query = $validated['query'];
        $limit = $validated['limit'] ?? 10;

        // Use the MySQL function for priority sorting (see migration 2024_12_19_000006)
        $actors = DB::select(
            'SELECT * FROM actors
             WHERE name LIKE ? OR short_name LIKE ?
             ORDER BY calculate_actor_priority(id) DESC, name ASC
             LIMIT ?',
            ["%{$query}%", "%{$query}%", $limit]
        );

        // Also search aliases
        $aliasResults = DB::select(
            'SELECT a.*, aa.alias
             FROM actors a
             INNER JOIN actor_aliases aa ON a.id = aa.actor_id
             WHERE aa.alias LIKE ?
             ORDER BY calculate_actor_priority(a.id) DESC
             LIMIT ?',
            ["%{$query}%", $limit]
        );

        // Merge and deduplicate results
        $results = collect($actors)->concat($aliasResults)
            ->unique('id')
            ->take($limit)
            ->values();

        return $this->success($results);
    }

    /**
     * Full-text search across actors and aliases
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'query' => ['required', 'string', 'min:2'],
            'type' => ['nullable', 'string'],
        ]);

        $searchTerm = $validated['query'];

        $query = Actor::query();

        // Search across name, short_name, and aliases
        $query->where(function ($q) use ($searchTerm) {
            $q->where('name', 'LIKE', "%{$searchTerm}%")
                ->orWhere('short_name', 'LIKE', "%{$searchTerm}%")
                ->orWhere('description', 'LIKE', "%{$searchTerm}%")
                ->orWhereHas('aliases', function ($aq) use ($searchTerm) {
                    $aq->where('alias', 'LIKE', "%{$searchTerm}%");
                });
        });

        // Filter by type if provided
        if (isset($validated['type'])) {
            $query->where('type', $validated['type']);
        }

        // Order by relevance (priority sorting)
        $query->orderByRaw('calculate_actor_priority(id) DESC')
            ->orderBy('name', 'asc');

        $results = $query->with(['conflicts', 'aliases'])
            ->limit(50)
            ->get();

        return $this->success($results);
    }
}
