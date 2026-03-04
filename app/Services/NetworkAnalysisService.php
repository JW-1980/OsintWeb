<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Actor;
use App\Models\ActorRelationship;
use App\Models\Country;
use App\Domains\Intelligence\Models\Event;
use App\Models\Faction;
use App\Models\NetworkEntity;
use App\Models\NetworkGraph;
use App\Models\NetworkRelationship;
use App\Models\NetworkSnapshot;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Network Analysis Service
 *
 * Provides graph building, analysis algorithms, and export functionality
 * for network visualization and analysis.
 */
class NetworkAnalysisService
{
    /**
     * Build a network graph from a root entity
     *
     * @param string $rootType Fully qualified class name or short type
     * @param int $rootId Root entity ID
     * @param int $depth Maximum traversal depth
     * @param array $filters Optional filters for nodes/edges
     * @return array{nodes: array, edges: array}
     */
    public function buildGraph(
        string $rootType,
        int $rootId,
        int $depth = 3,
        array $filters = []
    ): array {
        // Normalize root type
        $entityClass = $this->resolveEntityClass($rootType);
        if (!$entityClass) {
            throw new \InvalidArgumentException("Unsupported entity type: {$rootType}");
        }

        // Find root entity
        $rootEntity = $entityClass::find($rootId);
        if (!$rootEntity) {
            throw new \InvalidArgumentException("Entity not found: {$rootType}#{$rootId}");
        }

        $nodes = [];
        $edges = [];
        $visited = [];

        // Start BFS traversal
        $queue = [
            ['entity' => $rootEntity, 'type' => $entityClass, 'depth' => 0],
        ];

        while (!empty($queue)) {
            $current = array_shift($queue);
            $entity = $current['entity'];
            $type = $current['type'];
            $currentDepth = $current['depth'];

            $entityKey = $type . ':' . $entity->id;

            if (isset($visited[$entityKey])) {
                continue;
            }
            $visited[$entityKey] = true;

            // Add node
            $nodeData = $this->entityToNode($entity, $type);
            if ($this->passesFilters($nodeData, $filters, 'node')) {
                $nodes[] = $nodeData;
            }

            // Stop if depth limit reached
            if ($currentDepth >= $depth) {
                continue;
            }

            // Get related entities and edges
            $relations = $this->getRelatedEntities($entity, $type, $filters);

            foreach ($relations as $relation) {
                // Add edge
                $edgeData = [
                    'id' => count($edges) + 1,
                    'source_id' => $entityKey,
                    'target_id' => $relation['target_type'] . ':' . $relation['target_id'],
                    'type' => $relation['relationship_type'],
                    'strength' => $relation['strength'] ?? 1.0,
                    'direction' => $relation['direction'] ?? 'directed',
                    'metadata' => $relation['metadata'] ?? [],
                ];

                if ($this->passesFilters($edgeData, $filters, 'edge')) {
                    $edges[] = $edgeData;
                }

                // Queue related entity if not visited
                $relatedKey = $relation['target_type'] . ':' . $relation['target_id'];
                if (!isset($visited[$relatedKey])) {
                    $relatedClass = $relation['target_type'];
                    $relatedEntity = $relatedClass::find($relation['target_id']);
                    if ($relatedEntity) {
                        $queue[] = [
                            'entity' => $relatedEntity,
                            'type' => $relatedClass,
                            'depth' => $currentDepth + 1,
                        ];
                    }
                }
            }
        }

        return [
            'nodes' => $nodes,
            'edges' => $edges,
        ];
    }

    /**
     * Find shortest path between two entities
     *
     * @param int $graphId Network graph ID
     * @param string $fromId Source node ID
     * @param string $toId Target node ID
     * @return array|null Path array or null if no path exists
     */
    public function findShortestPath(int $graphId, string $fromId, string $toId): ?array
    {
        $graph = NetworkGraph::findOrFail($graphId);
        $nodes = $graph->cached_nodes ?? [];
        $edges = $graph->cached_edges ?? [];

        if (empty($nodes) || empty($edges)) {
            return null;
        }

        // Build adjacency list
        $adjacency = [];
        foreach ($nodes as $node) {
            $adjacency[$node['id']] = [];
        }

        foreach ($edges as $edge) {
            $source = $edge['source_id'];
            $target = $edge['target_id'];
            $adjacency[$source][] = ['node' => $target, 'edge' => $edge];
            // If bidirectional, add reverse edge
            if (($edge['direction'] ?? 'directed') === 'bidirectional') {
                $adjacency[$target][] = ['node' => $source, 'edge' => $edge];
            }
        }

        // BFS to find shortest path
        $visited = [];
        $parent = [];
        $parentEdge = [];
        $queue = [$fromId];
        $visited[$fromId] = true;

        while (!empty($queue)) {
            $current = array_shift($queue);

            if ($current === $toId) {
                // Reconstruct path
                $path = [];
                $pathEdges = [];
                $node = $toId;

                while ($node !== $fromId) {
                    $path[] = $node;
                    if (isset($parentEdge[$node])) {
                        $pathEdges[] = $parentEdge[$node];
                    }
                    $node = $parent[$node];
                }
                $path[] = $fromId;

                return [
                    'found' => true,
                    'path' => array_reverse($path),
                    'edges' => array_reverse($pathEdges),
                    'length' => count($path) - 1,
                ];
            }

            foreach ($adjacency[$current] ?? [] as $neighbor) {
                $neighborId = $neighbor['node'];
                if (!isset($visited[$neighborId])) {
                    $visited[$neighborId] = true;
                    $parent[$neighborId] = $current;
                    $parentEdge[$neighborId] = $neighbor['edge'];
                    $queue[] = $neighborId;
                }
            }
        }

        return [
            'found' => false,
            'path' => [],
            'edges' => [],
            'length' => -1,
        ];
    }

    /**
     * Detect clusters in the graph using connected components
     *
     * @param int $graphId
     * @return array
     */
    public function detectClusters(int $graphId): array
    {
        $graph = NetworkGraph::findOrFail($graphId);
        $nodes = $graph->cached_nodes ?? [];
        $edges = $graph->cached_edges ?? [];

        if (empty($nodes)) {
            return ['clusters' => [], 'count' => 0];
        }

        // Build adjacency list (bidirectional)
        $adjacency = [];
        foreach ($nodes as $node) {
            $adjacency[$node['id']] = [];
        }

        foreach ($edges as $edge) {
            $source = $edge['source_id'];
            $target = $edge['target_id'];
            $adjacency[$source][] = $target;
            $adjacency[$target][] = $source;
        }

        // Find connected components
        $visited = [];
        $clusters = [];

        foreach ($nodes as $node) {
            $nodeId = $node['id'];
            if (!isset($visited[$nodeId])) {
                $cluster = [];
                $queue = [$nodeId];
                $visited[$nodeId] = true;

                while (!empty($queue)) {
                    $current = array_shift($queue);
                    $cluster[] = $current;

                    foreach ($adjacency[$current] ?? [] as $neighbor) {
                        if (!isset($visited[$neighbor])) {
                            $visited[$neighbor] = true;
                            $queue[] = $neighbor;
                        }
                    }
                }

                $clusters[] = [
                    'id' => count($clusters) + 1,
                    'nodes' => $cluster,
                    'size' => count($cluster),
                ];
            }
        }

        // Sort by size descending
        usort($clusters, fn($a, $b) => $b['size'] <=> $a['size']);

        return [
            'clusters' => $clusters,
            'count' => count($clusters),
            'largest_cluster_size' => $clusters[0]['size'] ?? 0,
            'isolated_nodes' => count(array_filter($clusters, fn($c) => $c['size'] === 1)),
        ];
    }

    /**
     * Calculate centrality scores for nodes
     *
     * @param int $graphId
     * @return array
     */
    public function calculateCentrality(int $graphId): array
    {
        $graph = NetworkGraph::findOrFail($graphId);
        $nodes = $graph->cached_nodes ?? [];
        $edges = $graph->cached_edges ?? [];

        if (empty($nodes)) {
            return [
                'degree' => [],
                'betweenness' => [],
                'closeness' => [],
            ];
        }

        // Build adjacency list
        $adjacency = [];
        $nodeIds = [];
        foreach ($nodes as $node) {
            $nodeId = $node['id'];
            $nodeIds[] = $nodeId;
            $adjacency[$nodeId] = [];
        }

        foreach ($edges as $edge) {
            $source = $edge['source_id'];
            $target = $edge['target_id'];
            $adjacency[$source][] = $target;
            $adjacency[$target][] = $source;
        }

        // Degree centrality (normalized)
        $degreeCentrality = [];
        $maxPossibleDegree = count($nodes) - 1;
        foreach ($nodes as $node) {
            $nodeId = $node['id'];
            $degree = count($adjacency[$nodeId] ?? []);
            $degreeCentrality[$nodeId] = $maxPossibleDegree > 0
                ? round($degree / $maxPossibleDegree, 4)
                : 0;
        }

        // Betweenness centrality (simplified approximation using BFS)
        $betweenness = array_fill_keys($nodeIds, 0);
        foreach ($nodeIds as $source) {
            foreach ($nodeIds as $target) {
                if ($source >= $target) {
                    continue;
                }

                // Find all nodes on shortest paths from source to target
                $pathNodes = $this->findNodesOnShortestPath($adjacency, $source, $target);
                foreach ($pathNodes as $node) {
                    if ($node !== $source && $node !== $target) {
                        $betweenness[$node]++;
                    }
                }
            }
        }

        // Normalize betweenness
        $normFactor = (count($nodes) - 1) * (count($nodes) - 2) / 2;
        if ($normFactor > 0) {
            foreach ($betweenness as $nodeId => $value) {
                $betweenness[$nodeId] = round($value / $normFactor, 4);
            }
        }

        // Closeness centrality
        $closenessCentrality = [];
        foreach ($nodeIds as $nodeId) {
            $distances = $this->bfsDistances($adjacency, $nodeId);
            $totalDistance = array_sum($distances);
            $reachable = count(array_filter($distances, fn($d) => $d < PHP_INT_MAX));

            $closenessCentrality[$nodeId] = $totalDistance > 0 && $reachable > 1
                ? round(($reachable - 1) / $totalDistance, 4)
                : 0;
        }

        // Sort by centrality value descending
        arsort($degreeCentrality);
        arsort($betweenness);
        arsort($closenessCentrality);

        return [
            'degree' => $degreeCentrality,
            'betweenness' => $betweenness,
            'closeness' => $closenessCentrality,
        ];
    }

    /**
     * Find bridge edges (edges whose removal disconnects the graph)
     *
     * @param int $graphId
     * @return array
     */
    public function findBridges(int $graphId): array
    {
        $graph = NetworkGraph::findOrFail($graphId);
        $nodes = $graph->cached_nodes ?? [];
        $edges = $graph->cached_edges ?? [];

        if (empty($nodes) || empty($edges)) {
            return ['bridges' => [], 'count' => 0];
        }

        // Build adjacency list with edge references
        $adjacency = [];
        foreach ($nodes as $node) {
            $adjacency[$node['id']] = [];
        }

        foreach ($edges as $index => $edge) {
            $source = $edge['source_id'];
            $target = $edge['target_id'];
            $adjacency[$source][] = ['node' => $target, 'edge_index' => $index];
            $adjacency[$target][] = ['node' => $source, 'edge_index' => $index];
        }

        // Use Tarjan's algorithm to find bridges
        $visited = [];
        $disc = [];
        $low = [];
        $parent = [];
        $bridges = [];
        $time = 0;

        $nodeIds = array_column($nodes, 'id');

        $dfs = function ($nodeId) use (
            &$dfs, &$visited, &$disc, &$low, &$parent, &$bridges, &$time, $adjacency, $edges
        ) {
            $visited[$nodeId] = true;
            $disc[$nodeId] = $low[$nodeId] = ++$time;

            foreach ($adjacency[$nodeId] ?? [] as $neighbor) {
                $neighborId = $neighbor['node'];

                if (!isset($visited[$neighborId])) {
                    $parent[$neighborId] = $nodeId;
                    $dfs($neighborId);

                    $low[$nodeId] = min($low[$nodeId], $low[$neighborId]);

                    // If low[neighbor] > disc[node], then edge is a bridge
                    if ($low[$neighborId] > $disc[$nodeId]) {
                        $bridges[] = [
                            'edge' => $edges[$neighbor['edge_index']],
                            'criticality' => 'high',
                        ];
                    }
                } elseif (($parent[$nodeId] ?? null) !== $neighborId) {
                    $low[$nodeId] = min($low[$nodeId], $disc[$neighborId]);
                }
            }
        };

        foreach ($nodeIds as $nodeId) {
            if (!isset($visited[$nodeId])) {
                $dfs($nodeId);
            }
        }

        return [
            'bridges' => $bridges,
            'count' => count($bridges),
        ];
    }

    /**
     * Detect communities using label propagation algorithm
     *
     * @param int $graphId
     * @param int $maxIterations
     * @return array
     */
    public function detectCommunities(int $graphId, int $maxIterations = 100): array
    {
        $graph = NetworkGraph::findOrFail($graphId);
        $nodes = $graph->cached_nodes ?? [];
        $edges = $graph->cached_edges ?? [];

        if (empty($nodes)) {
            return ['communities' => [], 'count' => 0, 'modularity' => 0];
        }

        // Build adjacency list with weights
        $adjacency = [];
        $nodeIds = [];
        foreach ($nodes as $node) {
            $nodeId = $node['id'];
            $nodeIds[] = $nodeId;
            $adjacency[$nodeId] = [];
        }

        foreach ($edges as $edge) {
            $source = $edge['source_id'];
            $target = $edge['target_id'];
            $weight = $edge['strength'] ?? 1.0;
            $adjacency[$source][$target] = $weight;
            $adjacency[$target][$source] = $weight;
        }

        // Initialize: each node in its own community
        $labels = [];
        foreach ($nodeIds as $index => $nodeId) {
            $labels[$nodeId] = $index;
        }

        // Label propagation
        for ($iteration = 0; $iteration < $maxIterations; $iteration++) {
            $changed = false;
            shuffle($nodeIds); // Random order

            foreach ($nodeIds as $nodeId) {
                $neighborLabels = [];

                foreach ($adjacency[$nodeId] as $neighbor => $weight) {
                    $label = $labels[$neighbor];
                    $neighborLabels[$label] = ($neighborLabels[$label] ?? 0) + $weight;
                }

                if (!empty($neighborLabels)) {
                    // Find most common label among neighbors
                    arsort($neighborLabels);
                    $newLabel = array_key_first($neighborLabels);

                    if ($labels[$nodeId] !== $newLabel) {
                        $labels[$nodeId] = $newLabel;
                        $changed = true;
                    }
                }
            }

            if (!$changed) {
                break;
            }
        }

        // Group nodes by label
        $communities = [];
        foreach ($labels as $nodeId => $label) {
            if (!isset($communities[$label])) {
                $communities[$label] = [];
            }
            $communities[$label][] = $nodeId;
        }

        // Format output
        $result = [];
        $communityId = 1;
        foreach ($communities as $nodes) {
            $result[] = [
                'id' => $communityId++,
                'nodes' => $nodes,
                'size' => count($nodes),
            ];
        }

        // Sort by size descending
        usort($result, fn($a, $b) => $b['size'] <=> $a['size']);

        // Calculate modularity
        $modularity = $this->calculateModularity($adjacency, $labels, count($edges));

        return [
            'communities' => $result,
            'count' => count($result),
            'modularity' => round($modularity, 4),
        ];
    }

    /**
     * Export graph to GraphML format
     *
     * @param int $graphId
     * @return string XML string
     */
    public function exportToGraphML(int $graphId): string
    {
        $graph = NetworkGraph::findOrFail($graphId);
        $nodes = $graph->cached_nodes ?? [];
        $edges = $graph->cached_edges ?? [];

        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?>
            <graphml xmlns="http://graphml.graphdrawing.org/xmlns"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xsi:schemaLocation="http://graphml.graphdrawing.org/xmlns
                http://graphml.graphdrawing.org/xmlns/1.0/graphml.xsd">
            </graphml>');

        // Define attribute keys
        $keyNode = $xml->addChild('key');
        $keyNode->addAttribute('id', 'label');
        $keyNode->addAttribute('for', 'node');
        $keyNode->addAttribute('attr.name', 'label');
        $keyNode->addAttribute('attr.type', 'string');

        $keyNodeType = $xml->addChild('key');
        $keyNodeType->addAttribute('id', 'type');
        $keyNodeType->addAttribute('for', 'node');
        $keyNodeType->addAttribute('attr.name', 'type');
        $keyNodeType->addAttribute('attr.type', 'string');

        $keyEdgeType = $xml->addChild('key');
        $keyEdgeType->addAttribute('id', 'relationship');
        $keyEdgeType->addAttribute('for', 'edge');
        $keyEdgeType->addAttribute('attr.name', 'relationship');
        $keyEdgeType->addAttribute('attr.type', 'string');

        $keyEdgeWeight = $xml->addChild('key');
        $keyEdgeWeight->addAttribute('id', 'weight');
        $keyEdgeWeight->addAttribute('for', 'edge');
        $keyEdgeWeight->addAttribute('attr.name', 'weight');
        $keyEdgeWeight->addAttribute('attr.type', 'double');

        // Create graph element
        $graphElement = $xml->addChild('graph');
        $graphElement->addAttribute('id', $graph->uuid);
        $graphElement->addAttribute('edgedefault', 'directed');

        // Add nodes
        foreach ($nodes as $node) {
            $nodeElement = $graphElement->addChild('node');
            $nodeElement->addAttribute('id', (string) $node['id']);

            $dataLabel = $nodeElement->addChild('data', htmlspecialchars($node['name'] ?? ''));
            $dataLabel->addAttribute('key', 'label');

            $dataType = $nodeElement->addChild('data', $node['type'] ?? 'unknown');
            $dataType->addAttribute('key', 'type');
        }

        // Add edges
        foreach ($edges as $index => $edge) {
            $edgeElement = $graphElement->addChild('edge');
            $edgeElement->addAttribute('id', 'e' . $index);
            $edgeElement->addAttribute('source', (string) $edge['source_id']);
            $edgeElement->addAttribute('target', (string) $edge['target_id']);

            $dataRel = $edgeElement->addChild('data', $edge['type'] ?? 'related');
            $dataRel->addAttribute('key', 'relationship');

            $dataWeight = $edgeElement->addChild('data', (string) ($edge['strength'] ?? 1.0));
            $dataWeight->addAttribute('key', 'weight');
        }

        return $xml->asXML();
    }

    /**
     * Export graph to Cytoscape JSON format
     *
     * @param int $graphId
     * @return array
     */
    public function exportToCytoscape(int $graphId): array
    {
        $graph = NetworkGraph::findOrFail($graphId);
        $nodes = $graph->cached_nodes ?? [];
        $edges = $graph->cached_edges ?? [];
        $layout = $graph->layout_config ?? [];

        $elements = [];

        // Add nodes
        foreach ($nodes as $node) {
            $position = $layout['positions'][$node['id']] ?? null;

            $nodeData = [
                'group' => 'nodes',
                'data' => [
                    'id' => (string) $node['id'],
                    'label' => $node['name'] ?? '',
                    'type' => $node['type'] ?? 'unknown',
                ],
            ];

            if (isset($node['attributes'])) {
                $nodeData['data'] = array_merge($nodeData['data'], $node['attributes']);
            }

            if ($position) {
                $nodeData['position'] = [
                    'x' => $position['x'] ?? 0,
                    'y' => $position['y'] ?? 0,
                ];
            }

            $elements[] = $nodeData;
        }

        // Add edges
        foreach ($edges as $edge) {
            $elements[] = [
                'group' => 'edges',
                'data' => [
                    'id' => 'e' . ($edge['id'] ?? count($elements)),
                    'source' => (string) $edge['source_id'],
                    'target' => (string) $edge['target_id'],
                    'type' => $edge['type'] ?? 'related',
                    'weight' => $edge['strength'] ?? 1.0,
                ],
            ];
        }

        return [
            'format_version' => '1.0',
            'generated_by' => 'OsintWeb Network Analysis',
            'target_cytoscapejs_version' => '~3.19.0',
            'data' => [
                'name' => $graph->name,
                'description' => $graph->description,
                'graph_type' => $graph->graph_type,
            ],
            'elements' => $elements,
        ];
    }

    /**
     * Calculate influence score for an entity
     *
     * @param int $graphId
     * @param string $entityId Node ID in the graph
     * @return array
     */
    public function getInfluenceScore(int $graphId, string $entityId): array
    {
        $graph = NetworkGraph::findOrFail($graphId);
        $nodes = $graph->cached_nodes ?? [];
        $edges = $graph->cached_edges ?? [];

        // Find the node
        $nodeData = collect($nodes)->firstWhere('id', $entityId);
        if (!$nodeData) {
            return [
                'entity_id' => $entityId,
                'found' => false,
                'score' => 0,
            ];
        }

        // Calculate centrality for this specific node
        $centrality = $this->calculateCentrality($graphId);

        $degreeScore = $centrality['degree'][$entityId] ?? 0;
        $betweennessScore = $centrality['betweenness'][$entityId] ?? 0;
        $closenessScore = $centrality['closeness'][$entityId] ?? 0;

        // Weighted influence score
        $influenceScore = ($degreeScore * 0.4) + ($betweennessScore * 0.35) + ($closenessScore * 0.25);

        // Get direct connections
        $directConnections = collect($edges)->filter(function ($edge) use ($entityId) {
            return $edge['source_id'] === $entityId || $edge['target_id'] === $entityId;
        });

        // Calculate reach (nodes within 2 hops)
        $reachableNodes = $this->getNodesWithinDistance($graph, $entityId, 2);

        return [
            'entity_id' => $entityId,
            'found' => true,
            'score' => round($influenceScore, 4),
            'normalized_score' => round($influenceScore * 100, 2),
            'components' => [
                'degree_centrality' => round($degreeScore, 4),
                'betweenness_centrality' => round($betweennessScore, 4),
                'closeness_centrality' => round($closenessScore, 4),
            ],
            'direct_connections' => $directConnections->count(),
            'reach_within_2_hops' => count($reachableNodes),
            'rank_in_graph' => $this->getRankInGraph($centrality['degree'], $entityId),
        ];
    }

    /**
     * Compare two graph snapshots
     *
     * @param int $graphId1 First snapshot ID
     * @param int $graphId2 Second snapshot ID
     * @return array
     */
    public function compareGraphs(int $graphId1, int $graphId2): array
    {
        $snapshot1 = NetworkSnapshot::findOrFail($graphId1);
        $snapshot2 = NetworkSnapshot::findOrFail($graphId2);

        return $snapshot2->compareTo($snapshot1);
    }

    // -------------------------------------------------------------------------
    // Helper Methods
    // -------------------------------------------------------------------------

    /**
     * Resolve entity class from type string
     */
    protected function resolveEntityClass(string $type): ?string
    {
        $mapping = [
            'actor' => Actor::class,
            'Actor' => Actor::class,
            'event' => Event::class,
            'Event' => Event::class,
            'country' => Country::class,
            'Country' => Country::class,
            'faction' => Faction::class,
            'Faction' => Faction::class,
            'network_entity' => NetworkEntity::class,
            'NetworkEntity' => NetworkEntity::class,
        ];

        // Check if already a class name
        if (class_exists($type)) {
            return $type;
        }

        return $mapping[$type] ?? null;
    }

    /**
     * Convert entity to node data
     */
    protected function entityToNode(mixed $entity, string $type): array
    {
        $shortType = class_basename($type);

        return [
            'id' => $type . ':' . $entity->id,
            'entity_id' => $entity->id,
            'entity_type' => $type,
            'type' => strtolower($shortType),
            'name' => $entity->name ?? $entity->title ?? "Entity #{$entity->id}",
            'uuid' => $entity->uuid ?? null,
            'attributes' => $this->extractEntityAttributes($entity, $type),
        ];
    }

    /**
     * Extract relevant attributes from entity
     */
    protected function extractEntityAttributes(mixed $entity, string $type): array
    {
        $attrs = [];

        if ($entity instanceof Actor) {
            $attrs['actor_type'] = $entity->actor_type ?? null;
            $attrs['country_id'] = $entity->country_id ?? null;
            $attrs['is_state_actor'] = $entity->is_state_actor ?? false;
        } elseif ($entity instanceof Event) {
            $attrs['event_type_id'] = $entity->event_type_id ?? null;
            $attrs['occurred_at'] = $entity->occurred_at?->toIso8601String();
            $attrs['latitude'] = $entity->latitude;
            $attrs['longitude'] = $entity->longitude;
        } elseif ($entity instanceof Country) {
            $attrs['iso_code'] = $entity->iso_code ?? null;
        } elseif ($entity instanceof NetworkEntity) {
            $attrs['case_id'] = $entity->case_id ?? null;
            $attrs['identifiers'] = $entity->identifiers ?? [];
        }

        return $attrs;
    }

    /**
     * Get related entities for graph traversal
     */
    protected function getRelatedEntities(mixed $entity, string $type, array $filters): array
    {
        $relations = [];

        if ($entity instanceof Actor) {
            // Get actor relationships
            $actorRelations = ActorRelationship::where('actor_id', $entity->id)
                ->orWhere('related_actor_id', $entity->id)
                ->get();

            foreach ($actorRelations as $rel) {
                $targetId = $rel->actor_id === $entity->id
                    ? $rel->related_actor_id
                    : $rel->actor_id;

                $relations[] = [
                    'target_type' => Actor::class,
                    'target_id' => $targetId,
                    'relationship_type' => $rel->relationship_type ?? 'related',
                    'strength' => $rel->is_verified ? 1.0 : 0.5,
                    'direction' => 'bidirectional',
                    'metadata' => $rel->metadata ?? [],
                ];
            }

            // Get events involving this actor
            if ($entity->events) {
                foreach ($entity->events as $event) {
                    $relations[] = [
                        'target_type' => Event::class,
                        'target_id' => $event->id,
                        'relationship_type' => 'participated_in',
                        'strength' => 0.8,
                        'direction' => 'directed',
                        'metadata' => ['role' => $event->pivot->role ?? null],
                    ];
                }
            }
        } elseif ($entity instanceof Event) {
            // Get actors involved
            if ($entity->actors) {
                foreach ($entity->actors as $actor) {
                    $relations[] = [
                        'target_type' => Actor::class,
                        'target_id' => $actor->id,
                        'relationship_type' => 'involved_actor',
                        'strength' => 0.8,
                        'direction' => 'directed',
                        'metadata' => ['role' => $actor->pivot->role ?? null],
                    ];
                }
            }
        } elseif ($entity instanceof NetworkEntity) {
            // Get network relationships
            $outgoing = NetworkRelationship::where('source_id', $entity->id)->get();
            $incoming = NetworkRelationship::where('target_id', $entity->id)->get();

            foreach ($outgoing as $rel) {
                $relations[] = [
                    'target_type' => NetworkEntity::class,
                    'target_id' => $rel->target_id,
                    'relationship_type' => $rel->relationship_type ?? 'related',
                    'strength' => (float) ($rel->strength ?? 1.0),
                    'direction' => $rel->direction ?? 'directed',
                    'metadata' => $rel->metadata ?? [],
                ];
            }

            foreach ($incoming as $rel) {
                $relations[] = [
                    'target_type' => NetworkEntity::class,
                    'target_id' => $rel->source_id,
                    'relationship_type' => $rel->relationship_type ?? 'related',
                    'strength' => (float) ($rel->strength ?? 1.0),
                    'direction' => $rel->isBidirectional() ? 'bidirectional' : 'directed',
                    'metadata' => $rel->metadata ?? [],
                ];
            }
        }

        return $relations;
    }

    /**
     * Check if node/edge passes filters
     */
    protected function passesFilters(array $data, array $filters, string $elementType): bool
    {
        if (empty($filters)) {
            return true;
        }

        if ($elementType === 'node') {
            // Filter by entity types
            if (!empty($filters['entity_types'])) {
                $nodeType = $data['type'] ?? '';
                if (!in_array($nodeType, $filters['entity_types'])) {
                    return false;
                }
            }
        }

        if ($elementType === 'edge') {
            // Filter by relationship types
            if (!empty($filters['relationship_types'])) {
                $edgeType = $data['type'] ?? '';
                if (!in_array($edgeType, $filters['relationship_types'])) {
                    return false;
                }
            }

            // Filter by minimum strength
            if (isset($filters['min_strength'])) {
                $strength = $data['strength'] ?? 1.0;
                if ($strength < $filters['min_strength']) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Calculate modularity of a partition
     */
    protected function calculateModularity(array $adjacency, array $labels, int $edgeCount): float
    {
        if ($edgeCount === 0) {
            return 0;
        }

        $m = $edgeCount;
        $modularity = 0;

        foreach ($adjacency as $i => $neighbors) {
            $ki = count($neighbors);
            foreach ($neighbors as $j => $weight) {
                if ($labels[$i] === $labels[$j]) {
                    $kj = count($adjacency[$j] ?? []);
                    $modularity += ($weight - ($ki * $kj) / (2 * $m));
                }
            }
        }

        return $modularity / (2 * $m);
    }

    /**
     * BFS to get all distances from a source node
     */
    protected function bfsDistances(array $adjacency, string $source): array
    {
        $distances = [];
        foreach (array_keys($adjacency) as $nodeId) {
            $distances[$nodeId] = PHP_INT_MAX;
        }
        $distances[$source] = 0;

        $queue = [$source];
        while (!empty($queue)) {
            $current = array_shift($queue);
            foreach ($adjacency[$current] ?? [] as $neighbor) {
                if ($distances[$neighbor] === PHP_INT_MAX) {
                    $distances[$neighbor] = $distances[$current] + 1;
                    $queue[] = $neighbor;
                }
            }
        }

        return $distances;
    }

    /**
     * Find nodes on shortest path between two nodes
     */
    protected function findNodesOnShortestPath(array $adjacency, string $source, string $target): array
    {
        $distances = $this->bfsDistances($adjacency, $source);
        if ($distances[$target] === PHP_INT_MAX) {
            return [];
        }

        $nodesOnPath = [];
        $targetDist = $distances[$target];

        foreach ($distances as $nodeId => $dist) {
            if ($dist > 0 && $dist < $targetDist) {
                // Check if this node could be on a shortest path
                $distFromTarget = $this->bfsDistances($adjacency, $target);
                if ($dist + ($distFromTarget[$nodeId] ?? PHP_INT_MAX) === $targetDist) {
                    $nodesOnPath[] = $nodeId;
                }
            }
        }

        return $nodesOnPath;
    }

    /**
     * Get nodes within a certain distance from source
     */
    protected function getNodesWithinDistance(NetworkGraph $graph, string $source, int $maxDistance): array
    {
        $nodes = $graph->cached_nodes ?? [];
        $edges = $graph->cached_edges ?? [];

        $adjacency = [];
        foreach ($nodes as $node) {
            $adjacency[$node['id']] = [];
        }

        foreach ($edges as $edge) {
            $adjacency[$edge['source_id']][] = $edge['target_id'];
            $adjacency[$edge['target_id']][] = $edge['source_id'];
        }

        $distances = $this->bfsDistances($adjacency, $source);

        return array_keys(array_filter($distances, fn($d) => $d <= $maxDistance && $d > 0));
    }

    /**
     * Get rank of a node in a sorted centrality map
     */
    protected function getRankInGraph(array $sortedCentrality, string $nodeId): int
    {
        $rank = 1;
        foreach ($sortedCentrality as $id => $value) {
            if ($id === $nodeId) {
                return $rank;
            }
            $rank++;
        }
        return $rank;
    }
}
