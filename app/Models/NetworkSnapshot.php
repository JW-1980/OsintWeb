<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Network Snapshot Model
 *
 * Represents a point-in-time capture of a network graph's state.
 *
 * @property int $id
 * @property string $uuid
 * @property int $network_graph_id
 * @property string|null $name
 * @property array $nodes_data
 * @property array $edges_data
 * @property array|null $stats
 * @property \Carbon\Carbon $captured_at
 * @property string|null $notes
 * @property string|null $reason
 * @property int|null $created_by
 * @property int|null $compared_to_snapshot_id
 * @property array|null $diff_data
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property-read NetworkGraph $graph
 * @property-read User|null $creator
 * @property-read NetworkSnapshot|null $comparedToSnapshot
 */
class NetworkSnapshot extends Model
{
    protected $fillable = [
        'uuid',
        'network_graph_id',
        'name',
        'nodes_data',
        'edges_data',
        'stats',
        'captured_at',
        'notes',
        'reason',
        'created_by',
        'compared_to_snapshot_id',
        'diff_data',
    ];

    protected $casts = [
        'nodes_data' => 'array',
        'edges_data' => 'array',
        'stats' => 'array',
        'captured_at' => 'datetime',
        'diff_data' => 'array',
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
            if (empty($model->captured_at)) {
                $model->captured_at = now();
            }
        });
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /**
     * Get the parent network graph
     */
    public function graph(): BelongsTo
    {
        return $this->belongsTo(NetworkGraph::class, 'network_graph_id');
    }

    /**
     * Get the user who created this snapshot
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the snapshot this was compared to
     */
    public function comparedToSnapshot(): BelongsTo
    {
        return $this->belongsTo(NetworkSnapshot::class, 'compared_to_snapshot_id');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    /**
     * Filter by graph
     */
    public function scopeForGraph($query, int $graphId)
    {
        return $query->where('network_graph_id', $graphId);
    }

    /**
     * Filter by date range
     */
    public function scopeDateRange($query, $from, $to)
    {
        return $query->whereBetween('captured_at', [$from, $to]);
    }

    /**
     * Order by capture time
     */
    public function scopeChronological($query, string $direction = 'desc')
    {
        return $query->orderBy('captured_at', $direction);
    }

    /**
     * Filter snapshots with comparison data
     */
    public function scopeWithComparison($query)
    {
        return $query->whereNotNull('compared_to_snapshot_id');
    }

    // -------------------------------------------------------------------------
    // Methods
    // -------------------------------------------------------------------------

    /**
     * Get node count from snapshot data
     *
     * @return int
     */
    public function getNodeCount(): int
    {
        return count($this->nodes_data ?? []);
    }

    /**
     * Get edge count from snapshot data
     *
     * @return int
     */
    public function getEdgeCount(): int
    {
        return count($this->edges_data ?? []);
    }

    /**
     * Get a specific metric from stats
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getMetric(string $key, mixed $default = null): mixed
    {
        return $this->stats[$key] ?? $default;
    }

    /**
     * Compare this snapshot to another and return diff
     *
     * @param NetworkSnapshot $other
     * @return array
     */
    public function compareTo(NetworkSnapshot $other): array
    {
        $thisNodes = collect($this->nodes_data ?? []);
        $otherNodes = collect($other->nodes_data ?? []);
        $thisEdges = collect($this->edges_data ?? []);
        $otherEdges = collect($other->edges_data ?? []);

        // Extract node IDs
        $thisNodeIds = $thisNodes->pluck('id')->toArray();
        $otherNodeIds = $otherNodes->pluck('id')->toArray();

        // Extract edge signatures (source-target pairs)
        $getEdgeSignature = function ($edge) {
            return $edge['source_id'] . '-' . $edge['target_id'] . '-' . ($edge['type'] ?? 'default');
        };
        $thisEdgeSignatures = $thisEdges->map($getEdgeSignature)->toArray();
        $otherEdgeSignatures = $otherEdges->map($getEdgeSignature)->toArray();

        // Compute differences
        $addedNodes = array_diff($thisNodeIds, $otherNodeIds);
        $removedNodes = array_diff($otherNodeIds, $thisNodeIds);
        $addedEdges = array_diff($thisEdgeSignatures, $otherEdgeSignatures);
        $removedEdges = array_diff($otherEdgeSignatures, $thisEdgeSignatures);

        // Compute metric differences
        $thisStats = $this->stats ?? [];
        $otherStats = $other->stats ?? [];
        $metricDiff = [];

        $metricKeys = ['density', 'avg_degree', 'max_degree', 'connected_components'];
        foreach ($metricKeys as $key) {
            $thisVal = $thisStats[$key] ?? 0;
            $otherVal = $otherStats[$key] ?? 0;
            $metricDiff[$key] = [
                'from' => $otherVal,
                'to' => $thisVal,
                'change' => $thisVal - $otherVal,
            ];
        }

        return [
            'snapshot_from' => [
                'id' => $other->id,
                'uuid' => $other->uuid,
                'captured_at' => $other->captured_at->toIso8601String(),
            ],
            'snapshot_to' => [
                'id' => $this->id,
                'uuid' => $this->uuid,
                'captured_at' => $this->captured_at->toIso8601String(),
            ],
            'nodes' => [
                'added' => count($addedNodes),
                'removed' => count($removedNodes),
                'added_ids' => array_values($addedNodes),
                'removed_ids' => array_values($removedNodes),
            ],
            'edges' => [
                'added' => count($addedEdges),
                'removed' => count($removedEdges),
            ],
            'metrics' => $metricDiff,
            'total_node_count' => [
                'from' => $other->getNodeCount(),
                'to' => $this->getNodeCount(),
            ],
            'total_edge_count' => [
                'from' => $other->getEdgeCount(),
                'to' => $this->getEdgeCount(),
            ],
        ];
    }

    /**
     * Store comparison result for this snapshot
     *
     * @param NetworkSnapshot $other
     * @return self
     */
    public function storeComparison(NetworkSnapshot $other): self
    {
        $diff = $this->compareTo($other);

        $this->update([
            'compared_to_snapshot_id' => $other->id,
            'diff_data' => $diff,
        ]);

        return $this;
    }

    /**
     * Find a node by ID in this snapshot
     *
     * @param int|string $nodeId
     * @return array|null
     */
    public function findNode(int|string $nodeId): ?array
    {
        $nodes = $this->nodes_data ?? [];
        foreach ($nodes as $node) {
            if ($node['id'] === $nodeId) {
                return $node;
            }
        }
        return null;
    }

    /**
     * Get all edges connected to a specific node
     *
     * @param int|string $nodeId
     * @return array
     */
    public function getNodeEdges(int|string $nodeId): array
    {
        $edges = $this->edges_data ?? [];
        return array_values(array_filter($edges, function ($edge) use ($nodeId) {
            return $edge['source_id'] === $nodeId || $edge['target_id'] === $nodeId;
        }));
    }

    /**
     * Get nodes by type
     *
     * @param string $type
     * @return array
     */
    public function getNodesByType(string $type): array
    {
        $nodes = $this->nodes_data ?? [];
        return array_values(array_filter($nodes, fn($n) => ($n['type'] ?? null) === $type));
    }

    /**
     * Get edges by type
     *
     * @param string $type
     * @return array
     */
    public function getEdgesByType(string $type): array
    {
        $edges = $this->edges_data ?? [];
        return array_values(array_filter($edges, fn($e) => ($e['type'] ?? null) === $type));
    }

    /**
     * Export snapshot data to array format
     *
     * @return array
     */
    public function toExportArray(): array
    {
        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'captured_at' => $this->captured_at->toIso8601String(),
            'graph' => [
                'uuid' => $this->graph->uuid ?? null,
                'name' => $this->graph->name ?? null,
                'type' => $this->graph->graph_type ?? null,
            ],
            'nodes' => $this->nodes_data,
            'edges' => $this->edges_data,
            'stats' => $this->stats,
            'notes' => $this->notes,
        ];
    }
}
