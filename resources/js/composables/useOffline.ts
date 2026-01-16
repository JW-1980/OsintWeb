/**
 * useOffline Composable
 *
 * Vue composable for offline capability providing:
 * - Reactive offline/online status
 * - Pending changes tracking
 * - Sync status and triggers
 * - Cache management
 * - Conflict handling
 */

import { ref, computed, onMounted, onUnmounted, watch, Ref, ComputedRef } from 'vue';
import {
  getOfflineManager,
  initOffline,
  OfflineStatus,
  SyncQueueItem,
  SyncResult,
  EntityType,
  SyncOperation,
  Priority,
  ConflictData,
} from '@/services/offlineManager';
import { useApi } from '@/composables/useApi';

export interface UseOfflineReturn {
  // Reactive state
  isOffline: Ref<boolean>;
  isOnline: ComputedRef<boolean>;
  pendingChanges: Ref<number>;
  conflictCount: Ref<number>;
  syncStatus: Ref<SyncStatusType>;
  lastSyncAt: Ref<Date | null>;
  storageUsed: Ref<number>;
  storageUsedFormatted: ComputedRef<string>;

  // Methods
  triggerSync: () => Promise<SyncResult | null>;
  clearCache: () => Promise<void>;
  queueChange: (
    operation: SyncOperation,
    entityType: EntityType,
    entityId: number | undefined,
    payload: Record<string, unknown>,
    priority?: Priority
  ) => Promise<SyncQueueItem>;
  getConflicts: () => Promise<SyncQueueItem[]>;
  resolveConflict: (
    id: string,
    resolution: 'server' | 'client' | 'merge',
    mergedData?: Record<string, unknown>
  ) => Promise<void>;
  cacheEntities: (type: EntityType, items: Record<string, unknown>[]) => Promise<void>;
  getCachedEntity: (type: EntityType, id: number | string) => Promise<unknown | null>;
  getCachedEntities: (type: EntityType) => Promise<unknown[]>;

  // Status
  isInitialized: Ref<boolean>;
  isSupported: ComputedRef<boolean>;
  isSyncing: Ref<boolean>;
}

export type SyncStatusType = 'idle' | 'syncing' | 'synced' | 'error' | 'offline' | 'conflict';

// Singleton state (shared across all composable instances)
const isOffline = ref(!navigator.onLine);
const pendingChanges = ref(0);
const conflictCount = ref(0);
const syncStatus = ref<SyncStatusType>('idle');
const lastSyncAt = ref<Date | null>(null);
const storageUsed = ref(0);
const isInitialized = ref(false);
const isSyncing = ref(false);

let statusUnsubscribe: (() => void) | null = null;
let updateAvailableHandler: ((event: Event) => void) | null = null;

/**
 * useOffline composable
 */
export function useOffline(): UseOfflineReturn {
  const api = useApi();

  // Computed properties
  const isOnline = computed(() => !isOffline.value);

  const isSupported = computed(() => {
    return (
      'indexedDB' in window &&
      'serviceWorker' in navigator &&
      'caches' in window
    );
  });

  const storageUsedFormatted = computed(() => {
    const bytes = storageUsed.value;
    if (bytes < 1024) return `${bytes} B`;
    if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`;
    if (bytes < 1024 * 1024 * 1024) return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
    return `${(bytes / (1024 * 1024 * 1024)).toFixed(1)} GB`;
  });

  /**
   * Initialize offline capability
   */
  const initialize = async (): Promise<void> => {
    if (isInitialized.value || !isSupported.value) return;

    try {
      const manager = await initOffline();

      // Subscribe to status changes
      statusUnsubscribe = manager.onStatusChange((status: OfflineStatus) => {
        isOffline.value = !status.isOnline;
        pendingChanges.value = status.pendingCount;
        conflictCount.value = status.conflictCount;
        storageUsed.value = status.storageUsed;

        // Update sync status
        if (!status.isOnline) {
          syncStatus.value = 'offline';
        } else if (status.conflictCount > 0) {
          syncStatus.value = 'conflict';
        } else if (status.pendingCount > 0) {
          syncStatus.value = 'idle';
        } else {
          syncStatus.value = 'synced';
        }
      });

      // Listen for service worker updates
      updateAvailableHandler = (event: Event) => {
        const customEvent = event as CustomEvent;
        console.log('New version available');
        // You can show a notification to the user here
        if (customEvent.detail?.skipWaiting) {
          // Optionally auto-update
          // customEvent.detail.skipWaiting();
        }
      };
      window.addEventListener('sw-update-available', updateAvailableHandler);

      // Initial status fetch
      const status = await manager.getStatus();
      isOffline.value = !status.isOnline;
      pendingChanges.value = status.pendingCount;
      conflictCount.value = status.conflictCount;
      storageUsed.value = status.storageUsed;

      isInitialized.value = true;
      console.log('Offline capability initialized');
    } catch (error) {
      console.error('Failed to initialize offline capability:', error);
    }
  };

  /**
   * Trigger sync process
   */
  const triggerSync = async (): Promise<SyncResult | null> => {
    if (!isInitialized.value || isOffline.value || isSyncing.value) {
      return null;
    }

    isSyncing.value = true;
    syncStatus.value = 'syncing';

    try {
      const manager = getOfflineManager();
      const result = await manager.triggerSync();

      if (result) {
        lastSyncAt.value = new Date();
        syncStatus.value = result.conflicts > 0 ? 'conflict' : 'synced';
      } else {
        syncStatus.value = 'error';
      }

      return result;
    } catch (error) {
      console.error('Sync failed:', error);
      syncStatus.value = 'error';
      return null;
    } finally {
      isSyncing.value = false;
    }
  };

  /**
   * Clear all caches
   */
  const clearCache = async (): Promise<void> => {
    if (!isInitialized.value) return;

    try {
      const manager = getOfflineManager();
      await manager.clearAllCaches();

      // Also clear server-side cache info
      await api.post('/offline/clear');

      pendingChanges.value = 0;
      conflictCount.value = 0;
      storageUsed.value = 0;
      syncStatus.value = 'idle';
    } catch (error) {
      console.error('Failed to clear cache:', error);
      throw error;
    }
  };

  /**
   * Queue a change for offline sync
   */
  const queueChange = async (
    operation: SyncOperation,
    entityType: EntityType,
    entityId: number | undefined,
    payload: Record<string, unknown>,
    priority: Priority = 3
  ): Promise<SyncQueueItem> => {
    if (!isInitialized.value) {
      throw new Error('Offline capability not initialized');
    }

    const manager = getOfflineManager();
    return manager.queueChange(operation, entityType, entityId, payload, priority);
  };

  /**
   * Get all conflicts
   */
  const getConflicts = async (): Promise<SyncQueueItem[]> => {
    if (!isInitialized.value) return [];

    const manager = getOfflineManager();
    return manager.getConflicts();
  };

  /**
   * Resolve a conflict
   */
  const resolveConflict = async (
    id: string,
    resolution: 'server' | 'client' | 'merge',
    mergedData?: Record<string, unknown>
  ): Promise<void> => {
    if (!isInitialized.value) {
      throw new Error('Offline capability not initialized');
    }

    const manager = getOfflineManager();
    await manager.resolveConflict(id, resolution, mergedData);
  };

  /**
   * Cache entities locally
   */
  const cacheEntities = async (
    type: EntityType,
    items: Record<string, unknown>[]
  ): Promise<void> => {
    if (!isInitialized.value) return;

    const manager = getOfflineManager();
    await manager.storeEntities(type, items);
  };

  /**
   * Get a cached entity
   */
  const getCachedEntity = async (
    type: EntityType,
    id: number | string
  ): Promise<unknown | null> => {
    if (!isInitialized.value) return null;

    const manager = getOfflineManager();
    const cached = await manager.getEntity(type, id);
    return cached?.data || null;
  };

  /**
   * Get all cached entities of a type
   */
  const getCachedEntities = async (type: EntityType): Promise<unknown[]> => {
    if (!isInitialized.value) return [];

    const manager = getOfflineManager();
    const cached = await manager.getEntities(type);
    return cached.map((item) => item.data);
  };

  // Lifecycle hooks
  onMounted(() => {
    initialize();
  });

  onUnmounted(() => {
    // Note: We don't unsubscribe here because the state is shared
    // The subscription will remain active for other components
  });

  // Watch for online/offline changes
  watch(isOffline, (offline) => {
    if (!offline && pendingChanges.value > 0) {
      // Auto-sync when coming back online
      triggerSync();
    }
  });

  return {
    // Reactive state
    isOffline,
    isOnline,
    pendingChanges,
    conflictCount,
    syncStatus,
    lastSyncAt,
    storageUsed,
    storageUsedFormatted,

    // Methods
    triggerSync,
    clearCache,
    queueChange,
    getConflicts,
    resolveConflict,
    cacheEntities,
    getCachedEntity,
    getCachedEntities,

    // Status
    isInitialized,
    isSupported,
    isSyncing,
  };
}

/**
 * Helper to wrap API calls with offline support
 */
export function useOfflineApi() {
  const { isOffline, queueChange, getCachedEntity, getCachedEntities, cacheEntities } =
    useOffline();
  const api = useApi();

  /**
   * Fetch with offline fallback
   */
  const fetchWithOfflineFallback = async <T>(
    entityType: EntityType,
    url: string,
    options?: { cache?: boolean }
  ): Promise<T> => {
    const shouldCache = options?.cache !== false;

    try {
      const response = await api.get<{ data: T }>(url);
      const data = response.data;

      // Cache the response if it's an array of items
      if (shouldCache && Array.isArray(data)) {
        await cacheEntities(entityType, data as Record<string, unknown>[]);
      }

      return data;
    } catch (error) {
      // If offline, try to get from cache
      if (isOffline.value) {
        const cached = await getCachedEntities(entityType);
        if (cached.length > 0) {
          return cached as unknown as T;
        }
      }
      throw error;
    }
  };

  /**
   * Create with offline queue
   */
  const createWithOfflineQueue = async <T>(
    entityType: EntityType,
    url: string,
    data: Record<string, unknown>,
    priority?: Priority
  ): Promise<T | SyncQueueItem> => {
    if (isOffline.value) {
      // Queue for later sync
      return queueChange('create', entityType, undefined, data, priority);
    }

    // Online: make the request
    const response = await api.post<{ data: T }>(url, data);
    return response.data;
  };

  /**
   * Update with offline queue
   */
  const updateWithOfflineQueue = async <T>(
    entityType: EntityType,
    url: string,
    id: number,
    data: Record<string, unknown>,
    priority?: Priority
  ): Promise<T | SyncQueueItem> => {
    if (isOffline.value) {
      // Queue for later sync
      return queueChange('update', entityType, id, data, priority);
    }

    // Online: make the request
    const response = await api.put<{ data: T }>(url, data);
    return response.data;
  };

  /**
   * Delete with offline queue
   */
  const deleteWithOfflineQueue = async (
    entityType: EntityType,
    url: string,
    id: number,
    priority?: Priority
  ): Promise<void | SyncQueueItem> => {
    if (isOffline.value) {
      // Queue for later sync
      return queueChange('delete', entityType, id, {}, priority);
    }

    // Online: make the request
    await api.delete(url);
  };

  return {
    fetchWithOfflineFallback,
    createWithOfflineQueue,
    updateWithOfflineQueue,
    deleteWithOfflineQueue,
  };
}

export default useOffline;
