<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\OfflineCacheManifest;
use App\Models\SyncQueue;
use App\Models\User;
use App\Domains\Intelligence\Models\Event;
use App\Models\MilitaryEquipment;
use App\Models\Country;
use App\Models\Faction;
use App\Models\Actor;
use App\Models\Article;
use App\Models\Source;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * OfflineService
 *
 * Manages offline capability including cache manifest generation,
 * data caching, sync queue processing, and conflict resolution.
 */
class OfflineService
{
    /**
     * Entity types that can be cached offline
     */
    public const CACHEABLE_ENTITIES = [
        'events' => Event::class,
        'equipment' => MilitaryEquipment::class,
        'countries' => Country::class,
        'factions' => Faction::class,
        'actors' => Actor::class,
        'articles' => Article::class,
        'sources' => Source::class,
    ];

    /**
     * API routes that can be cached
     */
    public const CACHEABLE_ROUTES = [
        '/api/events',
        '/api/equipment',
        '/api/equipment/categories',
        '/api/countries',
        '/api/factions',
        '/api/actors',
        '/api/zones',
        '/api/stats/overview',
        '/api/articles',
        '/api/sources',
    ];

    /**
     * High-priority entities to cache first
     */
    public const PRIORITY_ENTITIES = [
        'countries',
        'factions',
        'equipment',
    ];

    /**
     * Generate cache manifest for a user
     *
     * @param int $userId
     * @return array
     */
    public function generateCacheManifest(int $userId): array
    {
        $manifest = $this->getOrCreateManifest($userId);

        $cacheableRoutes = $this->getCacheableRoutes();
        $priorityEntities = $this->getPriorityEntities();

        return [
            'version' => $manifest->cache_version,
            'generated_at' => now()->toIso8601String(),
            'routes' => $cacheableRoutes,
            'priority_entities' => $priorityEntities,
            'current_cache' => [
                'routes' => $manifest->cached_routes ?? [],
                'entities' => $manifest->cached_entities ?? [],
            ],
            'sync_status' => $manifest->sync_status,
            'pending_changes_count' => $manifest->pending_changes_count,
            'storage_used' => $manifest->storage_used_bytes,
            'storage_used_human' => $manifest->human_storage_size,
            'last_sync_at' => $manifest->last_sync_at?->toIso8601String(),
        ];
    }

    /**
     * Get data for caching
     *
     * @param int $userId
     * @param array $entities Types and optional IDs to cache
     * @return array
     */
    public function getCacheableData(int $userId, array $entities): array
    {
        $data = [];
        $totalSize = 0;

        foreach ($entities as $entityType => $options) {
            if (!isset(self::CACHEABLE_ENTITIES[$entityType])) {
                continue;
            }

            $modelClass = self::CACHEABLE_ENTITIES[$entityType];
            $query = $modelClass::query();

            // Apply user-specific filters where applicable
            if (method_exists($modelClass, 'scopeAccessibleBy')) {
                $query->accessibleBy($userId);
            }

            // Limit results if specified
            $limit = $options['limit'] ?? 100;
            if ($limit > 0) {
                $query->limit($limit);
            }

            // Filter by specific IDs if provided
            if (!empty($options['ids'])) {
                $query->whereIn('id', $options['ids']);
            }

            // Filter by updated_since for incremental sync
            if (!empty($options['updated_since'])) {
                $query->where('updated_at', '>', $options['updated_since']);
            }

            // Order by recency
            $query->orderBy('updated_at', 'desc');

            $items = $query->get();

            $entityData = $items->map(function ($item) use ($entityType) {
                return $this->serializeForCache($item, $entityType);
            })->toArray();

            $data[$entityType] = [
                'items' => $entityData,
                'count' => count($entityData),
                'cached_at' => now()->toIso8601String(),
            ];

            $totalSize += strlen(json_encode($entityData));
        }

        // Update manifest with cached entities
        $manifest = $this->getOrCreateManifest($userId);
        foreach ($entities as $entityType => $options) {
            if (isset($data[$entityType])) {
                foreach ($data[$entityType]['items'] as $item) {
                    $manifest->addCachedEntity($entityType, $item['id']);
                }
            }
        }
        $manifest->updateStorageUsage($totalSize);

        return [
            'data' => $data,
            'total_items' => array_sum(array_column($data, 'count')),
            'total_size_bytes' => $totalSize,
            'cached_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Queue an offline change for sync
     *
     * @param int $userId
     * @param string $operation
     * @param string $entityType
     * @param int|null $entityId
     * @param array $payload
     * @param int $priority
     * @param string|null $clientId
     * @return SyncQueue
     */
    public function queueOfflineChange(
        int $userId,
        string $operation,
        string $entityType,
        ?int $entityId,
        array $payload,
        int $priority = SyncQueue::PRIORITY_NORMAL,
        ?string $clientId = null
    ): SyncQueue {
        // Check for duplicate based on client_id
        if ($clientId) {
            $existing = SyncQueue::forUser($userId)
                ->byClientId($clientId)
                ->first();

            if ($existing) {
                // Update existing instead of creating duplicate
                $existing->update([
                    'payload' => $payload,
                    'status' => SyncQueue::STATUS_PENDING,
                    'error_message' => null,
                ]);
                return $existing;
            }
        }

        $syncItem = SyncQueue::create([
            'user_id' => $userId,
            'operation' => $operation,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'payload' => $payload,
            'priority' => $priority,
            'status' => SyncQueue::STATUS_PENDING,
            'client_id' => $clientId,
        ]);

        // Update manifest
        $manifest = $this->getOrCreateManifest($userId);
        $manifest->markAsPending([
            'sync_item_id' => $syncItem->id,
            'operation' => $operation,
            'entity_type' => $entityType,
        ]);

        Log::info('Offline change queued', [
            'user_id' => $userId,
            'sync_id' => $syncItem->id,
            'operation' => $operation,
            'entity_type' => $entityType,
        ]);

        return $syncItem;
    }

    /**
     * Process sync queue for a user
     *
     * @param int $userId
     * @param int $batchSize
     * @return array
     */
    public function processSync(int $userId, int $batchSize = 10): array
    {
        $results = [
            'processed' => 0,
            'synced' => 0,
            'failed' => 0,
            'conflicts' => 0,
            'items' => [],
        ];

        $syncItems = SyncQueue::forUser($userId)
            ->retryable()
            ->ordered()
            ->limit($batchSize)
            ->get();

        foreach ($syncItems as $syncItem) {
            try {
                $syncItem->markAsProcessing();

                $result = $this->processSyncItem($syncItem);

                $results['processed']++;
                $results['items'][] = [
                    'uuid' => $syncItem->uuid,
                    'status' => $result['status'],
                    'entity_id' => $result['entity_id'] ?? null,
                    'message' => $result['message'] ?? null,
                ];

                if ($result['status'] === 'synced') {
                    $results['synced']++;
                } elseif ($result['status'] === 'conflict') {
                    $results['conflicts']++;
                } else {
                    $results['failed']++;
                }
            } catch (\Exception $e) {
                $syncItem->markAsFailed($e->getMessage());
                $results['failed']++;
                $results['items'][] = [
                    'uuid' => $syncItem->uuid,
                    'status' => 'failed',
                    'message' => $e->getMessage(),
                ];

                Log::error('Sync item processing failed', [
                    'sync_id' => $syncItem->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Update manifest if all items synced
        if ($results['processed'] > 0 && $results['failed'] === 0 && $results['conflicts'] === 0) {
            $pendingCount = SyncQueue::forUser($userId)->pending()->count();
            if ($pendingCount === 0) {
                $manifest = $this->getOrCreateManifest($userId);
                $manifest->markAsSynced();
            }
        }

        return $results;
    }

    /**
     * Process a single sync item
     *
     * @param SyncQueue $syncItem
     * @return array
     */
    protected function processSyncItem(SyncQueue $syncItem): array
    {
        $entityClass = self::CACHEABLE_ENTITIES[$syncItem->entity_type] ?? null;

        if (!$entityClass) {
            $syncItem->markAsFailed("Unknown entity type: {$syncItem->entity_type}");
            return ['status' => 'failed', 'message' => 'Unknown entity type'];
        }

        // Check for conflicts on update/delete
        if ($syncItem->isUpdate() || $syncItem->isDelete()) {
            $serverEntity = $entityClass::find($syncItem->entity_id);

            if (!$serverEntity) {
                if ($syncItem->isDelete()) {
                    // Already deleted, mark as synced
                    $syncItem->markAsSynced();
                    return ['status' => 'synced', 'message' => 'Already deleted'];
                }

                $syncItem->markAsFailed('Entity not found on server');
                return ['status' => 'failed', 'message' => 'Entity not found'];
            }

            // Check for conflict based on updated_at
            $clientUpdatedAt = $syncItem->payload['_client_updated_at'] ?? null;
            if ($clientUpdatedAt && $serverEntity->updated_at->gt($clientUpdatedAt)) {
                $syncItem->markAsConflict([
                    'server_version' => $this->serializeForCache($serverEntity, $syncItem->entity_type),
                    'client_version' => $syncItem->payload,
                    'server_updated_at' => $serverEntity->updated_at->toIso8601String(),
                    'client_updated_at' => $clientUpdatedAt,
                ]);
                return ['status' => 'conflict', 'message' => 'Server has newer version'];
            }
        }

        return DB::transaction(function () use ($syncItem, $entityClass) {
            switch ($syncItem->operation) {
                case SyncQueue::OPERATION_CREATE:
                    return $this->processCreate($syncItem, $entityClass);

                case SyncQueue::OPERATION_UPDATE:
                    return $this->processUpdate($syncItem, $entityClass);

                case SyncQueue::OPERATION_DELETE:
                    return $this->processDelete($syncItem, $entityClass);

                default:
                    $syncItem->markAsFailed("Unknown operation: {$syncItem->operation}");
                    return ['status' => 'failed', 'message' => 'Unknown operation'];
            }
        });
    }

    /**
     * Process create operation
     */
    protected function processCreate(SyncQueue $syncItem, string $entityClass): array
    {
        $payload = $syncItem->payload;

        // Remove internal fields
        unset($payload['_client_id'], $payload['_client_updated_at'], $payload['_offline']);

        // Add user_id if applicable
        if (in_array($syncItem->entity_type, ['events', 'articles', 'sources'])) {
            $payload['user_id'] = $syncItem->user_id;
        }

        $entity = $entityClass::create($payload);

        $syncItem->markAsSynced($entity->id);

        return [
            'status' => 'synced',
            'entity_id' => $entity->id,
            'message' => 'Created successfully',
        ];
    }

    /**
     * Process update operation
     */
    protected function processUpdate(SyncQueue $syncItem, string $entityClass): array
    {
        $entity = $entityClass::find($syncItem->entity_id);

        if (!$entity) {
            $syncItem->markAsFailed('Entity not found');
            return ['status' => 'failed', 'message' => 'Entity not found'];
        }

        $payload = $syncItem->payload;

        // Remove internal fields
        unset($payload['_client_id'], $payload['_client_updated_at'], $payload['_offline']);

        $entity->update($payload);

        $syncItem->markAsSynced($entity->id);

        return [
            'status' => 'synced',
            'entity_id' => $entity->id,
            'message' => 'Updated successfully',
        ];
    }

    /**
     * Process delete operation
     */
    protected function processDelete(SyncQueue $syncItem, string $entityClass): array
    {
        $entity = $entityClass::find($syncItem->entity_id);

        if ($entity) {
            $entity->delete();
        }

        $syncItem->markAsSynced();

        return [
            'status' => 'synced',
            'message' => 'Deleted successfully',
        ];
    }

    /**
     * Resolve a conflict
     *
     * @param int $syncId
     * @param string $resolution 'server' | 'client' | 'merge'
     * @param array|null $mergedData For 'merge' resolution
     * @return array
     */
    public function resolveConflict(int $syncId, string $resolution, ?array $mergedData = null): array
    {
        $syncItem = SyncQueue::findOrFail($syncId);

        if (!$syncItem->isInConflict()) {
            return [
                'success' => false,
                'message' => 'Item is not in conflict state',
            ];
        }

        switch ($resolution) {
            case 'server':
                $syncItem->resolveWithServerVersion();
                return [
                    'success' => true,
                    'message' => 'Resolved with server version (local changes discarded)',
                ];

            case 'client':
                $syncItem->resolveWithLocalVersion();
                return [
                    'success' => true,
                    'message' => 'Resolved with client version (will overwrite server)',
                ];

            case 'merge':
                if (empty($mergedData)) {
                    return [
                        'success' => false,
                        'message' => 'Merged data is required for merge resolution',
                    ];
                }
                $syncItem->resolveWithMergedData($mergedData);
                return [
                    'success' => true,
                    'message' => 'Resolved with merged data',
                ];

            default:
                return [
                    'success' => false,
                    'message' => 'Invalid resolution type',
                ];
        }
    }

    /**
     * Get sync status for a user
     *
     * @param int $userId
     * @return array
     */
    public function getSyncStatus(int $userId): array
    {
        $manifest = $this->getOrCreateManifest($userId);

        $counts = [
            'pending' => SyncQueue::forUser($userId)->pending()->count(),
            'processing' => SyncQueue::forUser($userId)->processing()->count(),
            'synced' => SyncQueue::forUser($userId)->synced()->count(),
            'failed' => SyncQueue::forUser($userId)->failed()->count(),
            'conflicts' => SyncQueue::forUser($userId)->conflicts()->count(),
        ];

        return [
            'manifest' => [
                'version' => $manifest->cache_version,
                'sync_status' => $manifest->sync_status,
                'last_sync_at' => $manifest->last_sync_at?->toIso8601String(),
                'storage_used_bytes' => $manifest->storage_used_bytes,
                'storage_used_human' => $manifest->human_storage_size,
            ],
            'queue' => $counts,
            'total_pending' => $counts['pending'] + $counts['processing'] + $counts['conflicts'],
            'has_conflicts' => $counts['conflicts'] > 0,
            'is_synced' => $counts['pending'] === 0 && $counts['processing'] === 0 && $counts['conflicts'] === 0,
        ];
    }

    /**
     * Get storage usage for a user
     *
     * @param int $userId
     * @return array
     */
    public function getStorageUsage(int $userId): array
    {
        $manifest = $this->getOrCreateManifest($userId);
        $cachedEntities = $manifest->cached_entities ?? [];

        $breakdown = [];
        foreach ($cachedEntities as $type => $ids) {
            $breakdown[$type] = count($ids);
        }

        return [
            'total_bytes' => $manifest->storage_used_bytes,
            'total_human' => $manifest->human_storage_size,
            'cached_routes_count' => count($manifest->cached_routes ?? []),
            'cached_entities_breakdown' => $breakdown,
            'total_entities' => array_sum($breakdown),
        ];
    }

    /**
     * Clear offline cache for a user
     *
     * @param int $userId
     * @return array
     */
    public function clearOfflineCache(int $userId): array
    {
        $manifest = $this->getOrCreateManifest($userId);

        // Clear manifest
        $manifest->clearCache();
        $manifest->incrementVersion();

        // Clear pending sync items (except conflicts which need resolution)
        $deletedCount = SyncQueue::forUser($userId)
            ->whereIn('status', [SyncQueue::STATUS_PENDING, SyncQueue::STATUS_PROCESSING, SyncQueue::STATUS_SYNCED])
            ->delete();

        Log::info('Offline cache cleared', [
            'user_id' => $userId,
            'deleted_sync_items' => $deletedCount,
        ]);

        return [
            'success' => true,
            'message' => 'Offline cache cleared',
            'new_version' => $manifest->cache_version,
            'deleted_sync_items' => $deletedCount,
        ];
    }

    /**
     * Get high-priority entities for caching
     *
     * @return array
     */
    public function getPriorityEntities(): array
    {
        $entities = [];

        // Countries - cache all (limited set)
        $countries = Country::select('id', 'name', 'code', 'updated_at')
            ->orderBy('name')
            ->get();
        $entities['countries'] = [
            'items' => $countries->toArray(),
            'total' => $countries->count(),
        ];

        // Factions - cache all
        $factions = Faction::select('id', 'name', 'code', 'country_id', 'updated_at')
            ->orderBy('name')
            ->get();
        $entities['factions'] = [
            'items' => $factions->toArray(),
            'total' => $factions->count(),
        ];

        // Equipment categories - limited set
        $equipment = MilitaryEquipment::select('id', 'name', 'category', 'updated_at')
            ->orderBy('name')
            ->limit(500)
            ->get();
        $entities['equipment'] = [
            'items' => $equipment->toArray(),
            'total' => $equipment->count(),
        ];

        return $entities;
    }

    /**
     * Get cacheable routes
     *
     * @return array
     */
    protected function getCacheableRoutes(): array
    {
        return array_map(function ($route) {
            return [
                'path' => $route,
                'method' => 'GET',
                'cacheable' => true,
            ];
        }, self::CACHEABLE_ROUTES);
    }

    /**
     * Get or create manifest for user
     *
     * @param int $userId
     * @return OfflineCacheManifest
     */
    protected function getOrCreateManifest(int $userId): OfflineCacheManifest
    {
        return OfflineCacheManifest::firstOrCreate(
            ['user_id' => $userId],
            [
                'cache_version' => '1.0.0',
                'sync_status' => OfflineCacheManifest::STATUS_SYNCED,
            ]
        );
    }

    /**
     * Serialize entity for cache
     *
     * @param mixed $entity
     * @param string $type
     * @return array
     */
    protected function serializeForCache($entity, string $type): array
    {
        $data = $entity->toArray();

        // Add metadata for sync
        $data['_cached_at'] = now()->toIso8601String();
        $data['_entity_type'] = $type;

        return $data;
    }

    /**
     * Get conflicts for a user
     *
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Collection<int, SyncQueue>
     */
    public function getConflicts(int $userId): \Illuminate\Database\Eloquent\Collection
    {
        return SyncQueue::forUser($userId)
            ->conflicts()
            ->ordered()
            ->get();
    }
}
