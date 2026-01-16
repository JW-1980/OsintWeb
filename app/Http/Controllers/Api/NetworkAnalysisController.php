<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\NetworkGraph;
use App\Models\NetworkSnapshot;
use App\Services\NetworkAnalysisService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Network Analysis Controller
 *
 * Provides endpoints for network graph visualization and analysis,
 * including graph building, pathfinding, clustering, and exports.
 */
class NetworkAnalysisController extends Controller
{
    public function __construct(
        protected NetworkAnalysisService $networkService
    ) {}

    // -------------------------------------------------------------------------
    // Graph CRUD Operations
    // -------------------------------------------------------------------------

    /**
     * List network graphs
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = NetworkGraph::query()->with(['creator:id,name,email']);

        // Filter by graph type
        if ($request->has('type')) {
            $query->ofType($request->type);
        }

        // Filter by creator
        if ($request->has('created_by')) {
            $query->createdBy((int) $request->created_by);
        }

        // Filter only my graphs
        if ($request->boolean('mine', false)) {
            $query->createdBy($request->user()->id);
        }

        // Filter public graphs
        if ($request->boolean('public_only', false)) {
            $query->public();
        }

        // Filter templates only
        if ($request->boolean('templates_only', false)) {
            $query->templates();
        }

        // Search by name/description
        if ($request->has('search')) {
            $query->search($request->search);
        }

        // Filter by root entity type
        if ($request->has('entity_type')) {
            $query->forEntityType($request->entity_type);
        }

        // Sorting
        $sortField = $request->input('sort', 'created_at');
        $sortDirection = $request->input('direction', 'desc');
        $allowedSorts = ['name', 'created_at', 'last_generated_at', 'node_count', 'edge_count'];

        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection);
        }

        $graphs = $query->paginate($this->perPage);

        return $this->paginated($graphs);
    }

    /**
     * Store a new network graph
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'graph_type' => ['required', Rule::in(array_keys(NetworkGraph::getGraphTypes()))],
            'root_entity_type' => ['nullable', 'string', 'max:100'],
            'root_entity_id' => ['nullable', 'integer'],
            'depth_limit' => ['nullable', 'integer', 'min:1', 'max:10'],
            'filters' => ['nullable', 'array'],
            'layout_config' => ['nullable', 'array'],
            'is_public' => ['nullable', 'boolean'],
            'is_template' => ['nullable', 'boolean'],
        ]);

        $graph = NetworkGraph::create([
            'uuid' => (string) Str::uuid(),
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'graph_type' => $validated['graph_type'],
            'root_entity_type' => $validated['root_entity_type'] ?? null,
            'root_entity_id' => $validated['root_entity_id'] ?? null,
            'depth_limit' => $validated['depth_limit'] ?? 3,
            'filters' => $validated['filters'] ?? null,
            'layout_config' => $validated['layout_config'] ?? null,
            'is_public' => $validated['is_public'] ?? false,
            'is_template' => $validated['is_template'] ?? false,
            'created_by' => $request->user()->id,
        ]);

        // Log creation
        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'network_graph.created',
            'auditable_type' => NetworkGraph::class,
            'auditable_id' => $graph->id,
            'properties' => ['name' => $graph->name, 'type' => $graph->graph_type],
        ]);

        return $this->success($graph->load('creator:id,name,email'), 201);
    }

    /**
     * Display a specific network graph
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function show(string $uuid): JsonResponse
    {
        $graph = NetworkGraph::with(['creator:id,name,email', 'snapshots' => function ($q) {
            $q->orderBy('captured_at', 'desc')->limit(5);
        }])->where('uuid', $uuid)->firstOrFail();

        // Add computed properties
        $graph->has_valid_cache = $graph->hasCacheValid();

        return $this->success($graph);
    }

    /**
     * Update a network graph
     *
     * @param Request $request
     * @param string $uuid
     * @return JsonResponse
     */
    public function update(Request $request, string $uuid): JsonResponse
    {
        $graph = NetworkGraph::where('uuid', $uuid)->firstOrFail();

        // Check ownership
        if ($graph->created_by !== $request->user()->id) {
            return $this->forbidden('You can only update your own graphs');
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'graph_type' => ['sometimes', Rule::in(array_keys(NetworkGraph::getGraphTypes()))],
            'root_entity_type' => ['nullable', 'string', 'max:100'],
            'root_entity_id' => ['nullable', 'integer'],
            'depth_limit' => ['nullable', 'integer', 'min:1', 'max:10'],
            'filters' => ['nullable', 'array'],
            'layout_config' => ['nullable', 'array'],
            'is_public' => ['nullable', 'boolean'],
            'is_template' => ['nullable', 'boolean'],
        ]);

        $graph->update($validated);

        // Log update
        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'network_graph.updated',
            'auditable_type' => NetworkGraph::class,
            'auditable_id' => $graph->id,
            'properties' => ['changes' => $graph->getChanges()],
        ]);

        return $this->success($graph->fresh(['creator:id,name,email']));
    }

    /**
     * Delete a network graph
     *
     * @param Request $request
     * @param string $uuid
     * @return JsonResponse
     */
    public function destroy(Request $request, string $uuid): JsonResponse
    {
        $graph = NetworkGraph::where('uuid', $uuid)->firstOrFail();

        // Check ownership
        if ($graph->created_by !== $request->user()->id) {
            return $this->forbidden('You can only delete your own graphs');
        }

        // Log deletion
        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'network_graph.deleted',
            'auditable_type' => NetworkGraph::class,
            'auditable_id' => $graph->id,
            'properties' => ['name' => $graph->name],
        ]);

        $graph->delete();

        return $this->success(['message' => 'Network graph deleted successfully']);
    }

    // -------------------------------------------------------------------------
    // Graph Building & Data
    // -------------------------------------------------------------------------

    /**
     * Build/generate graph data from root entity
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function build(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'graph_id' => ['nullable', 'exists:network_graphs,id'],
            'root_entity_type' => ['required_without:graph_id', 'string', 'max:100'],
            'root_entity_id' => ['required_without:graph_id', 'integer'],
            'depth' => ['nullable', 'integer', 'min:1', 'max:10'],
            'filters' => ['nullable', 'array'],
            'filters.entity_types' => ['nullable', 'array'],
            'filters.relationship_types' => ['nullable', 'array'],
            'filters.min_strength' => ['nullable', 'numeric', 'min:0', 'max:1'],
            'cache' => ['nullable', 'boolean'],
        ]);

        try {
            // If graph_id provided, use its configuration
            if (!empty($validated['graph_id'])) {
                $graph = NetworkGraph::findOrFail($validated['graph_id']);

                // Check if cached data is still valid
                if ($graph->hasCacheValid() && !$request->boolean('force_refresh', false)) {
                    return $this->success([
                        'nodes' => $graph->cached_nodes,
                        'edges' => $graph->cached_edges,
                        'metrics' => $graph->cached_metrics,
                        'from_cache' => true,
                        'cache_expires_at' => $graph->cache_expires_at?->toIso8601String(),
                    ]);
                }

                $rootType = $graph->root_entity_type;
                $rootId = $graph->root_entity_id;
                $depth = $validated['depth'] ?? $graph->depth_limit;
                $filters = $validated['filters'] ?? $graph->filters ?? [];
            } else {
                $graph = null;
                $rootType = $validated['root_entity_type'];
                $rootId = $validated['root_entity_id'];
                $depth = $validated['depth'] ?? 3;
                $filters = $validated['filters'] ?? [];
            }

            // Build the graph
            $result = $this->networkService->buildGraph($rootType, $rootId, $depth, $filters);

            // Cache if requested and graph exists
            if ($graph && $request->boolean('cache', true)) {
                $graph->updateCache($result['nodes'], $result['edges'], 60);
                $graph->calculateMetrics();
            }

            return $this->success([
                'nodes' => $result['nodes'],
                'edges' => $result['edges'],
                'node_count' => count($result['nodes']),
                'edge_count' => count($result['edges']),
                'from_cache' => false,
            ]);

        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return $this->error('Failed to build graph: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get nodes from a graph
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function nodes(string $uuid): JsonResponse
    {
        $graph = NetworkGraph::where('uuid', $uuid)->firstOrFail();

        return $this->success([
            'nodes' => $graph->cached_nodes ?? [],
            'count' => $graph->node_count,
        ]);
    }

    /**
     * Get edges from a graph
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function edges(string $uuid): JsonResponse
    {
        $graph = NetworkGraph::where('uuid', $uuid)->firstOrFail();

        return $this->success([
            'edges' => $graph->cached_edges ?? [],
            'count' => $graph->edge_count,
        ]);
    }

    /**
     * Add a node to a graph
     *
     * @param Request $request
     * @param string $uuid
     * @return JsonResponse
     */
    public function addNode(Request $request, string $uuid): JsonResponse
    {
        $graph = NetworkGraph::where('uuid', $uuid)->firstOrFail();

        if ($graph->created_by !== $request->user()->id) {
            return $this->forbidden('You can only modify your own graphs');
        }

        $validated = $request->validate([
            'id' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:100'],
            'attributes' => ['nullable', 'array'],
        ]);

        $graph->addNode($validated);

        return $this->success([
            'message' => 'Node added successfully',
            'node_count' => $graph->fresh()->node_count,
        ]);
    }

    /**
     * Add an edge to a graph
     *
     * @param Request $request
     * @param string $uuid
     * @return JsonResponse
     */
    public function addEdge(Request $request, string $uuid): JsonResponse
    {
        $graph = NetworkGraph::where('uuid', $uuid)->firstOrFail();

        if ($graph->created_by !== $request->user()->id) {
            return $this->forbidden('You can only modify your own graphs');
        }

        $validated = $request->validate([
            'source_id' => ['required', 'string', 'max:255'],
            'target_id' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:100'],
            'strength' => ['nullable', 'numeric', 'min:0', 'max:1'],
            'direction' => ['nullable', Rule::in(['directed', 'bidirectional'])],
            'metadata' => ['nullable', 'array'],
        ]);

        $graph->addEdge($validated);

        return $this->success([
            'message' => 'Edge added successfully',
            'edge_count' => $graph->fresh()->edge_count,
        ]);
    }

    /**
     * Update layout configuration
     *
     * @param Request $request
     * @param string $uuid
     * @return JsonResponse
     */
    public function updateLayout(Request $request, string $uuid): JsonResponse
    {
        $graph = NetworkGraph::where('uuid', $uuid)->firstOrFail();

        if ($graph->created_by !== $request->user()->id) {
            return $this->forbidden('You can only modify your own graphs');
        }

        $validated = $request->validate([
            'positions' => ['required', 'array'],
            'zoom' => ['nullable', 'numeric', 'min:0.1', 'max:10'],
            'center' => ['nullable', 'array'],
            'center.x' => ['nullable', 'numeric'],
            'center.y' => ['nullable', 'numeric'],
            'selected_nodes' => ['nullable', 'array'],
        ]);

        $options = array_filter([
            'zoom' => $validated['zoom'] ?? null,
            'center' => $validated['center'] ?? null,
            'selected_nodes' => $validated['selected_nodes'] ?? null,
        ]);

        $graph->updateLayout($validated['positions'], $options ?: null);

        return $this->success(['message' => 'Layout updated successfully']);
    }

    // -------------------------------------------------------------------------
    // Analysis Operations
    // -------------------------------------------------------------------------

    /**
     * Find shortest path between two nodes
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function shortestPath(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'graph_id' => ['required', 'exists:network_graphs,id'],
            'from_id' => ['required', 'string', 'max:255'],
            'to_id' => ['required', 'string', 'max:255'],
        ]);

        $result = $this->networkService->findShortestPath(
            (int) $validated['graph_id'],
            $validated['from_id'],
            $validated['to_id']
        );

        return $this->success($result);
    }

    /**
     * Detect clusters in a graph
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function clusters(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'graph_id' => ['required', 'exists:network_graphs,id'],
        ]);

        $result = $this->networkService->detectClusters((int) $validated['graph_id']);

        return $this->success($result);
    }

    /**
     * Calculate centrality scores for all nodes
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function centrality(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'graph_id' => ['required', 'exists:network_graphs,id'],
        ]);

        $result = $this->networkService->calculateCentrality((int) $validated['graph_id']);

        return $this->success($result);
    }

    /**
     * Find bridge edges (critical connections)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bridges(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'graph_id' => ['required', 'exists:network_graphs,id'],
        ]);

        $result = $this->networkService->findBridges((int) $validated['graph_id']);

        return $this->success($result);
    }

    /**
     * Detect communities using label propagation
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function communities(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'graph_id' => ['required', 'exists:network_graphs,id'],
            'max_iterations' => ['nullable', 'integer', 'min:10', 'max:500'],
        ]);

        $result = $this->networkService->detectCommunities(
            (int) $validated['graph_id'],
            $validated['max_iterations'] ?? 100
        );

        return $this->success($result);
    }

    /**
     * Calculate metrics for a graph
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function metrics(string $uuid): JsonResponse
    {
        $graph = NetworkGraph::where('uuid', $uuid)->firstOrFail();

        $metrics = $graph->calculateMetrics();

        return $this->success($metrics);
    }

    /**
     * Get influence score for a specific entity
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function influenceScore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'graph_id' => ['required', 'exists:network_graphs,id'],
            'entity_id' => ['required', 'string', 'max:255'],
        ]);

        $result = $this->networkService->getInfluenceScore(
            (int) $validated['graph_id'],
            $validated['entity_id']
        );

        return $this->success($result);
    }

    // -------------------------------------------------------------------------
    // Snapshots
    // -------------------------------------------------------------------------

    /**
     * List snapshots for a graph
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function snapshots(string $uuid): JsonResponse
    {
        $graph = NetworkGraph::where('uuid', $uuid)->firstOrFail();

        $snapshots = $graph->snapshots()
            ->with('creator:id,name,email')
            ->orderBy('captured_at', 'desc')
            ->paginate($this->perPage);

        return $this->paginated($snapshots);
    }

    /**
     * Create a snapshot of current graph state
     *
     * @param Request $request
     * @param string $uuid
     * @return JsonResponse
     */
    public function createSnapshot(Request $request, string $uuid): JsonResponse
    {
        $graph = NetworkGraph::where('uuid', $uuid)->firstOrFail();

        if ($graph->created_by !== $request->user()->id) {
            return $this->forbidden('You can only create snapshots for your own graphs');
        }

        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        // Make sure graph has data
        if (empty($graph->cached_nodes)) {
            return $this->error('Graph has no cached data. Build the graph first.', 400);
        }

        $snapshot = $graph->createSnapshot($request->user(), $validated['notes'] ?? null);

        if (!empty($validated['name'])) {
            $snapshot->update(['name' => $validated['name']]);
        }
        if (!empty($validated['reason'])) {
            $snapshot->update(['reason' => $validated['reason']]);
        }

        return $this->success($snapshot->load('creator:id,name,email'), 201);
    }

    /**
     * Get a specific snapshot
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function showSnapshot(string $uuid): JsonResponse
    {
        $snapshot = NetworkSnapshot::with(['graph:id,uuid,name,graph_type', 'creator:id,name,email'])
            ->where('uuid', $uuid)
            ->firstOrFail();

        return $this->success($snapshot);
    }

    /**
     * Compare two snapshots
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function compareSnapshots(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'snapshot_id_1' => ['required', 'exists:network_snapshots,id'],
            'snapshot_id_2' => ['required', 'exists:network_snapshots,id'],
        ]);

        $result = $this->networkService->compareGraphs(
            (int) $validated['snapshot_id_1'],
            (int) $validated['snapshot_id_2']
        );

        return $this->success($result);
    }

    // -------------------------------------------------------------------------
    // Export
    // -------------------------------------------------------------------------

    /**
     * Export graph in various formats
     *
     * @param Request $request
     * @param string $uuid
     * @return JsonResponse|\Illuminate\Http\Response
     */
    public function export(Request $request, string $uuid)
    {
        $graph = NetworkGraph::where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'format' => ['required', Rule::in(['graphml', 'cytoscape', 'json'])],
        ]);

        $format = $validated['format'];

        switch ($format) {
            case 'graphml':
                $xml = $this->networkService->exportToGraphML($graph->id);
                return response($xml, 200, [
                    'Content-Type' => 'application/xml',
                    'Content-Disposition' => "attachment; filename=\"{$graph->uuid}.graphml\"",
                ]);

            case 'cytoscape':
                $data = $this->networkService->exportToCytoscape($graph->id);
                return response()->json($data, 200, [
                    'Content-Disposition' => "attachment; filename=\"{$graph->uuid}.cyjs\"",
                ]);

            case 'json':
            default:
                return $this->success([
                    'graph' => [
                        'uuid' => $graph->uuid,
                        'name' => $graph->name,
                        'type' => $graph->graph_type,
                    ],
                    'nodes' => $graph->cached_nodes ?? [],
                    'edges' => $graph->cached_edges ?? [],
                    'metrics' => $graph->cached_metrics ?? [],
                    'layout' => $graph->layout_config ?? [],
                    'exported_at' => now()->toIso8601String(),
                ]);
        }
    }

    // -------------------------------------------------------------------------
    // Reference Data
    // -------------------------------------------------------------------------

    /**
     * Get available graph types
     *
     * @return JsonResponse
     */
    public function graphTypes(): JsonResponse
    {
        $types = [];
        foreach (NetworkGraph::getGraphTypes() as $value => $label) {
            $types[] = [
                'value' => $value,
                'label' => $label,
            ];
        }

        return $this->success($types);
    }

    /**
     * Get supported entity types for graph roots
     *
     * @return JsonResponse
     */
    public function entityTypes(): JsonResponse
    {
        $types = [];
        foreach (NetworkGraph::getSupportedEntityTypes() as $class => $label) {
            $types[] = [
                'value' => $class,
                'label' => $label,
                'short_name' => class_basename($class),
            ];
        }

        return $this->success($types);
    }

    /**
     * Get network analysis statistics
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function statistics(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        // User's graphs
        $userGraphs = NetworkGraph::where('created_by', $userId)->count();
        $userSnapshots = NetworkSnapshot::whereHas('graph', function ($q) use ($userId) {
            $q->where('created_by', $userId);
        })->count();

        // Overall stats
        $totalPublicGraphs = NetworkGraph::public()->count();
        $totalTemplates = NetworkGraph::templates()->count();

        // By graph type
        $byType = NetworkGraph::select('graph_type', DB::raw('count(*) as count'))
            ->where('created_by', $userId)
            ->groupBy('graph_type')
            ->pluck('count', 'graph_type');

        // Recent graphs
        $recentGraphs = NetworkGraph::where('created_by', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get(['uuid', 'name', 'graph_type', 'node_count', 'edge_count', 'created_at']);

        return $this->success([
            'user_graphs' => $userGraphs,
            'user_snapshots' => $userSnapshots,
            'public_graphs' => $totalPublicGraphs,
            'templates' => $totalTemplates,
            'by_type' => $byType,
            'recent_graphs' => $recentGraphs,
        ]);
    }
}
