<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SyncQueue;
use App\Services\OfflineService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * OfflineController
 *
 * Handles offline capability API endpoints including cache manifest,
 * data caching, sync queue management, and conflict resolution.
 */
class OfflineController extends Controller
{
    public function __construct(
        protected OfflineService $offlineService
    ) {}

    /**
     * Get cache manifest for the authenticated user
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function manifest(Request $request): JsonResponse
    {
        $manifest = $this->offlineService->generateCacheManifest(
            $request->user()->id
        );

        return $this->success($manifest);
    }

    /**
     * Get data to cache for offline use
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function cacheData(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'entities' => ['required', 'array'],
            'entities.*.type' => ['required', 'string', Rule::in(array_keys(OfflineService::CACHEABLE_ENTITIES))],
            'entities.*.limit' => ['nullable', 'integer', 'min:1', 'max:500'],
            'entities.*.ids' => ['nullable', 'array'],
            'entities.*.ids.*' => ['integer'],
            'entities.*.updated_since' => ['nullable', 'date'],
        ]);

        // Transform to expected format
        $entities = [];
        foreach ($validated['entities'] as $entityConfig) {
            $type = $entityConfig['type'];
            $entities[$type] = [
                'limit' => $entityConfig['limit'] ?? 100,
                'ids' => $entityConfig['ids'] ?? [],
                'updated_since' => $entityConfig['updated_since'] ?? null,
            ];
        }

        $data = $this->offlineService->getCacheableData(
            $request->user()->id,
            $entities
        );

        return $this->success($data);
    }

    /**
     * Queue an offline change for sync
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function queueChange(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'operation' => ['required', Rule::in(SyncQueue::getOperations())],
            'entity_type' => ['required', 'string', Rule::in(array_keys(OfflineService::CACHEABLE_ENTITIES))],
            'entity_id' => ['nullable', 'integer'],
            'payload' => ['required', 'array'],
            'priority' => ['nullable', 'integer', 'min:1', 'max:5'],
            'client_id' => ['nullable', 'string', 'max:100'],
        ]);

        // Validate entity_id is provided for update/delete
        if (in_array($validated['operation'], ['update', 'delete']) && empty($validated['entity_id'])) {
            return $this->error('entity_id is required for update and delete operations', 422);
        }

        $syncItem = $this->offlineService->queueOfflineChange(
            $request->user()->id,
            $validated['operation'],
            $validated['entity_type'],
            $validated['entity_id'] ?? null,
            $validated['payload'],
            $validated['priority'] ?? SyncQueue::PRIORITY_NORMAL,
            $validated['client_id'] ?? null
        );

        return $this->success([
            'message' => 'Change queued for sync',
            'sync_item' => [
                'uuid' => $syncItem->uuid,
                'operation' => $syncItem->operation,
                'entity_type' => $syncItem->entity_type,
                'entity_id' => $syncItem->entity_id,
                'status' => $syncItem->status,
                'priority' => $syncItem->priority,
                'created_at' => $syncItem->created_at->toIso8601String(),
            ],
        ], 202);
    }

    /**
     * Queue multiple offline changes at once
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function queueBatch(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'changes' => ['required', 'array', 'min:1', 'max:50'],
            'changes.*.operation' => ['required', Rule::in(SyncQueue::getOperations())],
            'changes.*.entity_type' => ['required', 'string', Rule::in(array_keys(OfflineService::CACHEABLE_ENTITIES))],
            'changes.*.entity_id' => ['nullable', 'integer'],
            'changes.*.payload' => ['required', 'array'],
            'changes.*.priority' => ['nullable', 'integer', 'min:1', 'max:5'],
            'changes.*.client_id' => ['nullable', 'string', 'max:100'],
        ]);

        $results = [];
        $queued = 0;
        $failed = 0;

        foreach ($validated['changes'] as $index => $change) {
            try {
                // Validate entity_id for update/delete
                if (in_array($change['operation'], ['update', 'delete']) && empty($change['entity_id'])) {
                    $results[] = [
                        'index' => $index,
                        'success' => false,
                        'error' => 'entity_id is required for update and delete operations',
                    ];
                    $failed++;
                    continue;
                }

                $syncItem = $this->offlineService->queueOfflineChange(
                    $request->user()->id,
                    $change['operation'],
                    $change['entity_type'],
                    $change['entity_id'] ?? null,
                    $change['payload'],
                    $change['priority'] ?? SyncQueue::PRIORITY_NORMAL,
                    $change['client_id'] ?? null
                );

                $results[] = [
                    'index' => $index,
                    'success' => true,
                    'uuid' => $syncItem->uuid,
                    'client_id' => $change['client_id'] ?? null,
                ];
                $queued++;
            } catch (\Exception $e) {
                $results[] = [
                    'index' => $index,
                    'success' => false,
                    'error' => $e->getMessage(),
                ];
                $failed++;
            }
        }

        return $this->success([
            'message' => "Batch processed: {$queued} queued, {$failed} failed",
            'queued' => $queued,
            'failed' => $failed,
            'results' => $results,
        ], $failed > 0 ? 207 : 202);
    }

    /**
     * Process sync queue
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sync(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'batch_size' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $results = $this->offlineService->processSync(
            $request->user()->id,
            $validated['batch_size'] ?? 10
        );

        return $this->success([
            'message' => "Sync processed: {$results['synced']} synced, {$results['failed']} failed, {$results['conflicts']} conflicts",
            'results' => $results,
        ]);
    }

    /**
     * Get sync status
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function status(Request $request): JsonResponse
    {
        $status = $this->offlineService->getSyncStatus($request->user()->id);

        return $this->success($status);
    }

    /**
     * Get pending sync items
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function pending(Request $request): JsonResponse
    {
        $query = SyncQueue::forUser($request->user()->id)
            ->whereIn('status', [SyncQueue::STATUS_PENDING, SyncQueue::STATUS_PROCESSING])
            ->ordered();

        $items = $query->paginate($this->perPage);

        return $this->paginated($items);
    }

    /**
     * List conflicts
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function conflicts(Request $request): JsonResponse
    {
        $conflicts = $this->offlineService->getConflicts($request->user()->id);

        return $this->success([
            'conflicts' => $conflicts->map(function ($item) {
                return [
                    'uuid' => $item->uuid,
                    'operation' => $item->operation,
                    'entity_type' => $item->entity_type,
                    'entity_id' => $item->entity_id,
                    'conflict_data' => $item->conflict_data,
                    'created_at' => $item->created_at->toIso8601String(),
                    'last_attempt_at' => $item->last_attempt_at?->toIso8601String(),
                ];
            }),
            'count' => $conflicts->count(),
        ]);
    }

    /**
     * Get a specific conflict
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function showConflict(string $uuid): JsonResponse
    {
        $conflict = SyncQueue::forUser($this->user()->id)
            ->where('uuid', $uuid)
            ->conflicts()
            ->firstOrFail();

        return $this->success([
            'uuid' => $conflict->uuid,
            'operation' => $conflict->operation,
            'entity_type' => $conflict->entity_type,
            'entity_id' => $conflict->entity_id,
            'payload' => $conflict->payload,
            'conflict_data' => $conflict->conflict_data,
            'attempts' => $conflict->attempts,
            'created_at' => $conflict->created_at->toIso8601String(),
            'last_attempt_at' => $conflict->last_attempt_at?->toIso8601String(),
        ]);
    }

    /**
     * Resolve a conflict
     *
     * @param Request $request
     * @param string $uuid
     * @return JsonResponse
     */
    public function resolveConflict(Request $request, string $uuid): JsonResponse
    {
        $validated = $request->validate([
            'resolution' => ['required', Rule::in(['server', 'client', 'merge'])],
            'merged_data' => ['required_if:resolution,merge', 'array'],
        ]);

        $conflict = SyncQueue::forUser($request->user()->id)
            ->where('uuid', $uuid)
            ->conflicts()
            ->firstOrFail();

        $result = $this->offlineService->resolveConflict(
            $conflict->id,
            $validated['resolution'],
            $validated['merged_data'] ?? null
        );

        if (!$result['success']) {
            return $this->error($result['message'], 400);
        }

        return $this->success([
            'message' => $result['message'],
            'uuid' => $uuid,
            'resolution' => $validated['resolution'],
        ]);
    }

    /**
     * Get storage usage
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function storage(Request $request): JsonResponse
    {
        $usage = $this->offlineService->getStorageUsage($request->user()->id);

        return $this->success($usage);
    }

    /**
     * Clear offline cache
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function clear(Request $request): JsonResponse
    {
        $result = $this->offlineService->clearOfflineCache($request->user()->id);

        return $this->success($result);
    }

    /**
     * Get priority entities for initial cache
     *
     * @return JsonResponse
     */
    public function priorityEntities(): JsonResponse
    {
        $entities = $this->offlineService->getPriorityEntities();

        return $this->success([
            'priority_entities' => $entities,
            'generated_at' => now()->toIso8601String(),
        ]);
    }

    /**
     * Get available entity types
     *
     * @return JsonResponse
     */
    public function entityTypes(): JsonResponse
    {
        return $this->success([
            'entity_types' => array_keys(OfflineService::CACHEABLE_ENTITIES),
        ]);
    }

    /**
     * Get sync queue operations
     *
     * @return JsonResponse
     */
    public function operations(): JsonResponse
    {
        return $this->success([
            'operations' => SyncQueue::getOperations(),
            'statuses' => SyncQueue::getStatuses(),
            'priorities' => SyncQueue::getPriorities(),
        ]);
    }

    /**
     * Retry a failed sync item
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function retry(string $uuid): JsonResponse
    {
        $syncItem = SyncQueue::forUser($this->user()->id)
            ->where('uuid', $uuid)
            ->firstOrFail();

        if (!$syncItem->canRetry()) {
            return $this->error('This item cannot be retried', 400);
        }

        $syncItem->resetForRetry();

        return $this->success([
            'message' => 'Item queued for retry',
            'uuid' => $syncItem->uuid,
            'status' => $syncItem->status,
        ]);
    }

    /**
     * Cancel a pending sync item
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function cancel(string $uuid): JsonResponse
    {
        $syncItem = SyncQueue::forUser($this->user()->id)
            ->where('uuid', $uuid)
            ->whereIn('status', [SyncQueue::STATUS_PENDING, SyncQueue::STATUS_FAILED, SyncQueue::STATUS_CONFLICT])
            ->firstOrFail();

        $syncItem->delete();

        return $this->success([
            'message' => 'Sync item cancelled',
            'uuid' => $uuid,
        ]);
    }

    /**
     * Get sync history
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function history(Request $request): JsonResponse
    {
        $query = SyncQueue::forUser($request->user()->id)
            ->synced()
            ->orderBy('synced_at', 'desc');

        if ($request->has('entity_type')) {
            $query->entityType($request->entity_type);
        }

        if ($request->has('from')) {
            $query->where('synced_at', '>=', $request->from);
        }

        if ($request->has('to')) {
            $query->where('synced_at', '<=', $request->to);
        }

        $items = $query->paginate($this->perPage);

        return $this->paginated($items);
    }
}
