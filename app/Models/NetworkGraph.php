<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * Network Graph Model
 *
 * Represents a saved network graph configuration for visualization and analysis.
 *
 * @property int $id
 * @property string $uuid
 * @property string $name
 * @property string|null $description
 * @property string $graph_type
 * @property string|null $root_entity_type
 * @property int|null $root_entity_id
 * @property int $depth_limit
 * @property array|null $filters
 * @property array|null $layout_config
 * @property bool $is_public
 * @property bool $is_template
 * @property int $created_by
 * @property array|null $cached_nodes
 * @property array|null $cached_edges
 * @property array|null $cached_metrics
 * @property \Carbon\Carbon|null $cache_expires_at
 * @property int $node_count
 * @property int $edge_count
 * @property \Carbon\Carbon|null $last_generated_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 *
 * @property-read User $creator
 * @property-read Model|null $rootEntity
 * @property-read \Illuminate\Database\Eloquent\Collection|NetworkSnapshot[] $snapshots
 */
class NetworkGraph extends Model
{
    use SoftDeletes;

    // Graph types
    public const TYPE_ACTOR_NETWORK = 'actor_network';
    public const TYPE_COMMUNICATION = 'communication';
    public const TYPE_SUPPLY_CHAIN = 'supply_chain';
    public const TYPE_COMMAND_STRUCTURE = 'command_structure';
    public const TYPE_FINANCIAL = 'financial';
    public const TYPE_INFLUENCE = 'influence';

    protected $fillable = [
        'uuid',
        'name',
        'description',
        'graph_type',
        'root_entity_type',
        'root_entity_id',
        'depth_limit',
        'filters',
        'layout_config',
        'is_public',
        'is_template',
        'created_by',
        'cached_nodes',
        'cached_edges',
        'cached_metrics',
        'cache_expires_at',
        'node_count',
        'edge_count',
        'last_generated_at',
    ];

    protected $casts = [
        'depth_limit' => 'integer',
        'filters' => 'array',
        'layout_config' => 'array',
        'is_public' => 'boolean',
        'is_template' => 'boolean',
        'cached_nodes' => 'array',
        'cached_edges' => 'array',
        'cached_metrics' => 'array',
        'cache_expires_at' => 'datetime',
        'node_count' => 'integer',
        'edge_count' => 'integer',
        'last_generated_at' => 'datetime',
    ];

    /**
     * Boot the model
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /**
     * Get the user who created this graph
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the root entity (polymorphic)
     */
    public function rootEntity(): MorphTo
    {
        return $this->morphTo('root_entity', 'root_entity_type', 'root_entity_id');
    }

    /**
     * Get all snapshots for this graph
     */
    public function snapshots(): HasMany
    {
        return $this->hasMany(NetworkSnapshot::class, 'network_graph_id');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    /**
     * Filter by graph type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('graph_type', $type);
    }

    /**
     * Filter public graphs only
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Filter template graphs only
     */
    public function scopeTemplates($query)
    {
        return $query->where('is_template', true);
    }

    /**
     * Filter by creator
     */
    public function scopeCreatedBy($query, int $userId)
    {
        return $query->where('created_by', $userId);
    }

    /**
     * Filter graphs with valid cache
     */
    public function scopeWithValidCache($query)
    {
        return $query->where('cache_expires_at', '>', now());
    }

    /**
     * Search by name or description
     */
    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('description', 'like', "%{$term}%");
        });
    }

    /**
     * Filter by root entity type
     */
    public function scopeForEntityType($query, string $entityType)
    {
        return $query->where('root_entity_type', $entityType);
    }

    // -------------------------------------------------------------------------
    // Graph Building Methods
    // -------------------------------------------------------------------------

    /**
     * Generate the graph data from root entity
     *
     * @return array{nodes: array, edges: array, metrics: array}
     */
    public function generateGraph(): array
    {
        // This is a placeholder - actual implementation in NetworkAnalysisService
        return [
            'nodes' => $this->cached_nodes ?? [],
            'edges' => $this->cached_edges ?? [],
            'metrics' => $this->cached_metrics ?? [],
        ];
    }

    /**
     * Add a node to the graph
     *
     * @param array $nodeData Node data including id, type, name, attributes
     * @return self
     */
    public function addNode(array $nodeData): self
    {
        $nodes = $this->cached_nodes ?? [];

        // Check if node already exists
        $existingIndex = array_search($nodeData['id'], array_column($nodes, 'id'));
        if ($existingIndex !== false) {
            $nodes[$existingIndex] = array_merge($nodes[$existingIndex], $nodeData);
        } else {
            $nodes[] = $nodeData;
        }

        $this->update([
            'cached_nodes' => $nodes,
            'node_count' => count($nodes),
        ]);

        return $this;
    }

    /**
     * Add an edge to the graph
     *
     * @param array $edgeData Edge data including source_id, target_id, type, strength
     * @return self
     */
    public function addEdge(array $edgeData): self
    {
        $edges = $this->cached_edges ?? [];

        // Check if edge already exists (same source and target)
        $exists = collect($edges)->first(function ($edge) use ($edgeData) {
            return $edge['source_id'] === $edgeData['source_id']
                && $edge['target_id'] === $edgeData['target_id']
                && ($edge['type'] ?? null) === ($edgeData['type'] ?? null);
        });

        if (!$exists) {
            $edges[] = array_merge([
                'id' => count($edges) + 1,
            ], $edgeData);

            $this->update([
                'cached_edges' => $edges,
                'edge_count' => count($edges),
            ]);
        }

        return $this;
    }

    /**
     * Remove a node from the graph
     *
     * @param int|string $nodeId
     * @return self
     */
    public function removeNode(int|string $nodeId): self
    {
        $nodes = $this->cached_nodes ?? [];
        $edges = $this->cached_edges ?? [];

        // Remove the node
        $nodes = array_values(array_filter($nodes, fn($n) => $n['id'] !== $nodeId));

        // Remove edges connected to this node
        $edges = array_values(array_filter($edges, function ($e) use ($nodeId) {
            return $e['source_id'] !== $nodeId && $e['target_id'] !== $nodeId;
        }));

        $this->update([
            'cached_nodes' => $nodes,
            'cached_edges' => $edges,
            'node_count' => count($nodes),
            'edge_count' => count($edges),
        ]);

        return $this;
    }

    /**
     * Remove an edge from the graph
     *
     * @param int $edgeId
     * @return self
     */
    public function removeEdge(int $edgeId): self
    {
        $edges = $this->cached_edges ?? [];
        $edges = array_values(array_filter($edges, fn($e) => ($e['id'] ?? null) !== $edgeId));

        $this->update([
            'cached_edges' => $edges,
            'edge_count' => count($edges),
        ]);

        return $this;
    }

    /**
     * Calculate basic graph metrics
     *
     * @return array
     */
    public function calculateMetrics(): array
    {
        $nodes = $this->cached_nodes ?? [];
        $edges = $this->cached_edges ?? [];

        $nodeCount = count($nodes);
        $edgeCount = count($edges);

        if ($nodeCount === 0) {
            return [
                'node_count' => 0,
                'edge_count' => 0,
                'density' => 0,
                'avg_degree' => 0,
                'max_degree' => 0,
                'connected_components' => 0,
            ];
        }

        // Calculate degree for each node
        $degrees = [];
        foreach ($nodes as $node) {
            $degrees[$node['id']] = 0;
        }

        foreach ($edges as $edge) {
            $sourceId = $edge['source_id'];
            $targetId = $edge['target_id'];

            if (isset($degrees[$sourceId])) {
                $degrees[$sourceId]++;
            }
            if (isset($degrees[$targetId])) {
                $degrees[$targetId]++;
            }
        }

        $avgDegree = count($degrees) > 0 ? array_sum($degrees) / count($degrees) : 0;
        $maxDegree = count($degrees) > 0 ? max($degrees) : 0;

        // Graph density: 2 * E / (V * (V - 1))
        $maxPossibleEdges = $nodeCount * ($nodeCount - 1) / 2;
        $density = $maxPossibleEdges > 0 ? $edgeCount / $maxPossibleEdges : 0;

        // Find connected components using BFS
        $connectedComponents = $this->findConnectedComponents($nodes, $edges);

        $metrics = [
            'node_count' => $nodeCount,
            'edge_count' => $edgeCount,
            'density' => round($density, 4),
            'avg_degree' => round($avgDegree, 2),
            'max_degree' => $maxDegree,
            'connected_components' => $connectedComponents,
            'degree_distribution' => $degrees,
        ];

        $this->update([
            'cached_metrics' => $metrics,
            'node_count' => $nodeCount,
            'edge_count' => $edgeCount,
        ]);

        return $metrics;
    }

    /**
     * Find connected components in the graph
     *
     * @param array $nodes
     * @param array $edges
     * @return int Number of connected components
     */
    protected function findConnectedComponents(array $nodes, array $edges): int
    {
        if (empty($nodes)) {
            return 0;
        }

        // Build adjacency list
        $adjacency = [];
        foreach ($nodes as $node) {
            $adjacency[$node['id']] = [];
        }

        foreach ($edges as $edge) {
            $source = $edge['source_id'];
            $target = $edge['target_id'];

            if (isset($adjacency[$source])) {
                $adjacency[$source][] = $target;
            }
            if (isset($adjacency[$target])) {
                $adjacency[$target][] = $source;
            }
        }

        // BFS to find components
        $visited = [];
        $components = 0;

        foreach ($nodes as $node) {
            $nodeId = $node['id'];
            if (!isset($visited[$nodeId])) {
                $components++;
                $queue = [$nodeId];
                $visited[$nodeId] = true;

                while (!empty($queue)) {
                    $current = array_shift($queue);
                    foreach ($adjacency[$current] ?? [] as $neighbor) {
                        if (!isset($visited[$neighbor])) {
                            $visited[$neighbor] = true;
                            $queue[] = $neighbor;
                        }
                    }
                }
            }
        }

        return $components;
    }

    /**
     * Update the layout configuration
     *
     * @param array $positions Node positions { nodeId: { x, y } }
     * @param array|null $options Additional layout options (zoom, center, etc.)
     * @return self
     */
    public function updateLayout(array $positions, ?array $options = null): self
    {
        $layoutConfig = $this->layout_config ?? [];
        $layoutConfig['positions'] = $positions;

        if ($options !== null) {
            $layoutConfig = array_merge($layoutConfig, $options);
        }

        $this->update(['layout_config' => $layoutConfig]);

        return $this;
    }

    /**
     * Clear the cached graph data
     *
     * @return self
     */
    public function clearCache(): self
    {
        $this->update([
            'cached_nodes' => null,
            'cached_edges' => null,
            'cached_metrics' => null,
            'cache_expires_at' => null,
            'node_count' => 0,
            'edge_count' => 0,
        ]);

        return $this;
    }

    /**
     * Update cache with new data
     *
     * @param array $nodes
     * @param array $edges
     * @param int $ttlMinutes Cache TTL in minutes
     * @return self
     */
    public function updateCache(array $nodes, array $edges, int $ttlMinutes = 60): self
    {
        $this->update([
            'cached_nodes' => $nodes,
            'cached_edges' => $edges,
            'node_count' => count($nodes),
            'edge_count' => count($edges),
            'cache_expires_at' => now()->addMinutes($ttlMinutes),
            'last_generated_at' => now(),
        ]);

        return $this;
    }

    /**
     * Check if cache is valid
     *
     * @return bool
     */
    public function hasCacheValid(): bool
    {
        return $this->cache_expires_at !== null
            && $this->cache_expires_at->isFuture()
            && $this->cached_nodes !== null;
    }

    /**
     * Create a snapshot of the current graph state
     *
     * @param User|null $user
     * @param string|null $notes
     * @return NetworkSnapshot
     */
    public function createSnapshot(?User $user = null, ?string $notes = null): NetworkSnapshot
    {
        return NetworkSnapshot::create([
            'uuid' => (string) Str::uuid(),
            'network_graph_id' => $this->id,
            'nodes_data' => $this->cached_nodes ?? [],
            'edges_data' => $this->cached_edges ?? [],
            'stats' => $this->cached_metrics ?? [],
            'captured_at' => now(),
            'notes' => $notes,
            'created_by' => $user?->id,
        ]);
    }

    // -------------------------------------------------------------------------
    // Static Methods
    // -------------------------------------------------------------------------

    /**
     * Get available graph types with labels
     *
     * @return array
     */
    public static function getGraphTypes(): array
    {
        return [
            self::TYPE_ACTOR_NETWORK => 'Actor Network',
            self::TYPE_COMMUNICATION => 'Communication Network',
            self::TYPE_SUPPLY_CHAIN => 'Supply Chain',
            self::TYPE_COMMAND_STRUCTURE => 'Command Structure',
            self::TYPE_FINANCIAL => 'Financial Network',
            self::TYPE_INFLUENCE => 'Influence Network',
        ];
    }

    /**
     * Get supported root entity types
     *
     * @return array
     */
    public static function getSupportedEntityTypes(): array
    {
        return [
            Actor::class => 'Actor',
            Event::class => 'Event',
            Country::class => 'Country',
            Faction::class => 'Faction',
            NetworkEntity::class => 'Network Entity',
        ];
    }
}
