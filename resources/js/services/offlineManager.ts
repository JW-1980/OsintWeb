/**
 * OfflineManager
 *
 * Provides comprehensive offline capability management including:
 * - IndexedDB wrapper for local storage
 * - Sync queue management
 * - Conflict detection and resolution
 * - Network status detection
 * - Cache management
 * - Service worker communication
 */

import { useApi } from '@/composables/useApi';

// IndexedDB Configuration
const DB_NAME = 'osint-offline-db';
const DB_VERSION = 2;

// Store names
const STORES = {
  ENTITIES: 'entities',
  SYNC_QUEUE: 'sync-queue',
  CACHE_META: 'cache-meta',
  PENDING_REQUESTS: 'pending-requests',
} as const;

// Entity types
export type EntityType =
  | 'events'
  | 'equipment'
  | 'countries'
  | 'factions'
  | 'actors'
  | 'articles'
  | 'sources';

// Sync operation types
export type SyncOperation = 'create' | 'update' | 'delete';

// Sync item status
export type SyncStatus = 'pending' | 'processing' | 'synced' | 'failed' | 'conflict';

// Priority levels
export type Priority = 1 | 2 | 3 | 4 | 5;

// Interfaces
export interface SyncQueueItem {
  id: string;
  clientId: string;
  operation: SyncOperation;
  entityType: EntityType;
  entityId?: number;
  payload: Record<string, unknown>;
  priority: Priority;
  status: SyncStatus;
  attempts: number;
  createdAt: number;
  lastAttemptAt?: number;
  errorMessage?: string;
  conflictData?: ConflictData;
}

export interface ConflictData {
  serverVersion: Record<string, unknown>;
  clientVersion: Record<string, unknown>;
  serverUpdatedAt: string;
  clientUpdatedAt: string;
}

export interface CachedEntity {
  id: number | string;
  type: EntityType;
  data: Record<string, unknown>;
  cachedAt: number;
  updatedAt?: string;
}

export interface CacheMeta {
  type: EntityType;
  lastSync: number;
  itemCount: number;
  version: string;
}

export interface OfflineStatus {
  isOnline: boolean;
  pendingCount: number;
  conflictCount: number;
  lastSyncAt?: number;
  storageUsed: number;
}

export interface SyncResult {
  synced: number;
  failed: number;
  conflicts: number;
  items: Array<{
    clientId: string;
    status: SyncStatus;
    entityId?: number;
    message?: string;
  }>;
}

/**
 * OfflineManager class
 */
class OfflineManager {
  private db: IDBDatabase | null = null;
  private isOnline: boolean = navigator.onLine;
  private statusListeners: Set<(status: OfflineStatus) => void> = new Set();
  private syncInProgress: boolean = false;
  private serviceWorkerReady: boolean = false;

  constructor() {
    this.setupNetworkListeners();
    this.setupServiceWorkerListeners();
  }

  /**
   * Initialize the offline manager
   */
  async init(): Promise<void> {
    await this.openDatabase();
    await this.registerServiceWorker();
  }

  /**
   * Open IndexedDB database
   */
  private openDatabase(): Promise<IDBDatabase> {
    return new Promise((resolve, reject) => {
      if (this.db) {
        resolve(this.db);
        return;
      }

      const request = indexedDB.open(DB_NAME, DB_VERSION);

      request.onerror = () => {
        console.error('Failed to open IndexedDB:', request.error);
        reject(request.error);
      };

      request.onsuccess = () => {
        this.db = request.result;
        resolve(this.db);
      };

      request.onupgradeneeded = (event) => {
        const db = (event.target as IDBOpenDBRequest).result;

        // Entities store
        if (!db.objectStoreNames.contains(STORES.ENTITIES)) {
          const entityStore = db.createObjectStore(STORES.ENTITIES, {
            keyPath: ['type', 'id'],
          });
          entityStore.createIndex('type', 'type', { unique: false });
          entityStore.createIndex('cachedAt', 'cachedAt', { unique: false });
        }

        // Sync queue store
        if (!db.objectStoreNames.contains(STORES.SYNC_QUEUE)) {
          const syncStore = db.createObjectStore(STORES.SYNC_QUEUE, {
            keyPath: 'id',
          });
          syncStore.createIndex('status', 'status', { unique: false });
          syncStore.createIndex('priority', 'priority', { unique: false });
          syncStore.createIndex('clientId', 'clientId', { unique: true });
        }

        // Cache metadata store
        if (!db.objectStoreNames.contains(STORES.CACHE_META)) {
          db.createObjectStore(STORES.CACHE_META, { keyPath: 'type' });
        }

        // Pending requests store
        if (!db.objectStoreNames.contains(STORES.PENDING_REQUESTS)) {
          const pendingStore = db.createObjectStore(STORES.PENDING_REQUESTS, {
            keyPath: 'id',
          });
          pendingStore.createIndex('timestamp', 'timestamp', { unique: false });
        }
      };
    });
  }

  /**
   * Setup network status listeners
   */
  private setupNetworkListeners(): void {
    window.addEventListener('online', () => {
      this.isOnline = true;
      this.notifyStatusChange();
      this.triggerSync();
    });

    window.addEventListener('offline', () => {
      this.isOnline = false;
      this.notifyStatusChange();
    });
  }

  /**
   * Setup service worker message listeners
   */
  private setupServiceWorkerListeners(): void {
    if ('serviceWorker' in navigator) {
      navigator.serviceWorker.addEventListener('message', (event) => {
        this.handleServiceWorkerMessage(event.data);
      });
    }
  }

  /**
   * Handle service worker messages
   */
  private handleServiceWorkerMessage(message: { type: string; data?: unknown }): void {
    switch (message.type) {
      case 'OFFLINE_REQUEST_QUEUED':
        this.notifyStatusChange();
        break;

      case 'SYNC_COMPLETED':
        this.notifyStatusChange();
        break;

      case 'CACHE_CLEARED':
        this.notifyStatusChange();
        break;
    }
  }

  /**
   * Register service worker
   */
  async registerServiceWorker(): Promise<void> {
    if (!('serviceWorker' in navigator)) {
      console.warn('Service Worker not supported');
      return;
    }

    try {
      const registration = await navigator.serviceWorker.register('/service-worker.js', {
        scope: '/',
      });

      this.serviceWorkerReady = true;
      console.log('Service Worker registered:', registration.scope);

      // Handle updates
      registration.addEventListener('updatefound', () => {
        const newWorker = registration.installing;
        if (newWorker) {
          newWorker.addEventListener('statechange', () => {
            if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
              // New version available
              this.notifyNewVersionAvailable();
            }
          });
        }
      });
    } catch (error) {
      console.error('Service Worker registration failed:', error);
    }
  }

  /**
   * Notify about new version available
   */
  private notifyNewVersionAvailable(): void {
    // Dispatch custom event for UI to handle
    window.dispatchEvent(
      new CustomEvent('sw-update-available', {
        detail: { skipWaiting: () => this.skipWaiting() },
      })
    );
  }

  /**
   * Skip waiting and activate new service worker
   */
  async skipWaiting(): Promise<void> {
    const registration = await navigator.serviceWorker.ready;
    registration.waiting?.postMessage({ type: 'SKIP_WAITING' });
    window.location.reload();
  }

  // ============================================
  // Entity Storage Methods
  // ============================================

  /**
   * Store an entity locally
   */
  async storeEntity(type: EntityType, data: Record<string, unknown>): Promise<void> {
    const db = await this.openDatabase();
    const tx = db.transaction(STORES.ENTITIES, 'readwrite');
    const store = tx.objectStore(STORES.ENTITIES);

    const entity: CachedEntity = {
      id: data.id as number | string,
      type,
      data,
      cachedAt: Date.now(),
      updatedAt: data.updated_at as string | undefined,
    };

    await this.promisifyRequest(store.put(entity));
  }

  /**
   * Store multiple entities
   */
  async storeEntities(type: EntityType, items: Record<string, unknown>[]): Promise<void> {
    const db = await this.openDatabase();
    const tx = db.transaction(STORES.ENTITIES, 'readwrite');
    const store = tx.objectStore(STORES.ENTITIES);

    for (const item of items) {
      const entity: CachedEntity = {
        id: item.id as number | string,
        type,
        data: item,
        cachedAt: Date.now(),
        updatedAt: item.updated_at as string | undefined,
      };
      store.put(entity);
    }

    await this.promisifyTransaction(tx);

    // Update cache metadata
    await this.updateCacheMeta(type, items.length);
  }

  /**
   * Get an entity from local storage
   */
  async getEntity(type: EntityType, id: number | string): Promise<CachedEntity | null> {
    const db = await this.openDatabase();
    const tx = db.transaction(STORES.ENTITIES, 'readonly');
    const store = tx.objectStore(STORES.ENTITIES);

    const result = await this.promisifyRequest<CachedEntity>(store.get([type, id]));
    return result || null;
  }

  /**
   * Get all entities of a type
   */
  async getEntities(type: EntityType): Promise<CachedEntity[]> {
    const db = await this.openDatabase();
    const tx = db.transaction(STORES.ENTITIES, 'readonly');
    const store = tx.objectStore(STORES.ENTITIES);
    const index = store.index('type');

    return this.promisifyRequest<CachedEntity[]>(index.getAll(type));
  }

  /**
   * Delete an entity
   */
  async deleteEntity(type: EntityType, id: number | string): Promise<void> {
    const db = await this.openDatabase();
    const tx = db.transaction(STORES.ENTITIES, 'readwrite');
    const store = tx.objectStore(STORES.ENTITIES);

    await this.promisifyRequest(store.delete([type, id]));
  }

  /**
   * Clear all entities of a type
   */
  async clearEntities(type: EntityType): Promise<void> {
    const db = await this.openDatabase();
    const tx = db.transaction(STORES.ENTITIES, 'readwrite');
    const store = tx.objectStore(STORES.ENTITIES);
    const index = store.index('type');

    const keys = await this.promisifyRequest<IDBValidKey[]>(index.getAllKeys(type));

    for (const key of keys) {
      store.delete(key);
    }

    await this.promisifyTransaction(tx);
  }

  /**
   * Update cache metadata
   */
  private async updateCacheMeta(type: EntityType, itemCount: number): Promise<void> {
    const db = await this.openDatabase();
    const tx = db.transaction(STORES.CACHE_META, 'readwrite');
    const store = tx.objectStore(STORES.CACHE_META);

    const meta: CacheMeta = {
      type,
      lastSync: Date.now(),
      itemCount,
      version: '1.0.0',
    };

    await this.promisifyRequest(store.put(meta));
  }

  // ============================================
  // Sync Queue Methods
  // ============================================

  /**
   * Generate unique client ID
   */
  private generateClientId(): string {
    return `${Date.now()}-${Math.random().toString(36).substr(2, 9)}`;
  }

  /**
   * Queue a change for sync
   */
  async queueChange(
    operation: SyncOperation,
    entityType: EntityType,
    entityId: number | undefined,
    payload: Record<string, unknown>,
    priority: Priority = 3
  ): Promise<SyncQueueItem> {
    const db = await this.openDatabase();
    const tx = db.transaction(STORES.SYNC_QUEUE, 'readwrite');
    const store = tx.objectStore(STORES.SYNC_QUEUE);

    const clientId = this.generateClientId();

    const item: SyncQueueItem = {
      id: crypto.randomUUID(),
      clientId,
      operation,
      entityType,
      entityId,
      payload: {
        ...payload,
        _client_id: clientId,
        _client_updated_at: new Date().toISOString(),
        _offline: true,
      },
      priority,
      status: 'pending',
      attempts: 0,
      createdAt: Date.now(),
    };

    await this.promisifyRequest(store.add(item));
    this.notifyStatusChange();

    // Try to sync immediately if online
    if (this.isOnline) {
      this.triggerSync();
    }

    return item;
  }

  /**
   * Get all pending sync items
   */
  async getPendingItems(): Promise<SyncQueueItem[]> {
    const db = await this.openDatabase();
    const tx = db.transaction(STORES.SYNC_QUEUE, 'readonly');
    const store = tx.objectStore(STORES.SYNC_QUEUE);
    const index = store.index('status');

    const pending = await this.promisifyRequest<SyncQueueItem[]>(index.getAll('pending'));
    const processing = await this.promisifyRequest<SyncQueueItem[]>(index.getAll('processing'));

    return [...pending, ...processing].sort((a, b) => {
      if (a.priority !== b.priority) return a.priority - b.priority;
      return a.createdAt - b.createdAt;
    });
  }

  /**
   * Get all conflict items
   */
  async getConflicts(): Promise<SyncQueueItem[]> {
    const db = await this.openDatabase();
    const tx = db.transaction(STORES.SYNC_QUEUE, 'readonly');
    const store = tx.objectStore(STORES.SYNC_QUEUE);
    const index = store.index('status');

    return this.promisifyRequest<SyncQueueItem[]>(index.getAll('conflict'));
  }

  /**
   * Update sync item status
   */
  private async updateSyncItemStatus(
    id: string,
    status: SyncStatus,
    extra?: Partial<SyncQueueItem>
  ): Promise<void> {
    const db = await this.openDatabase();
    const tx = db.transaction(STORES.SYNC_QUEUE, 'readwrite');
    const store = tx.objectStore(STORES.SYNC_QUEUE);

    const item = await this.promisifyRequest<SyncQueueItem>(store.get(id));

    if (item) {
      const updated: SyncQueueItem = {
        ...item,
        ...extra,
        status,
        lastAttemptAt: Date.now(),
      };
      await this.promisifyRequest(store.put(updated));
    }
  }

  /**
   * Remove synced items
   */
  async removeSyncedItems(): Promise<void> {
    const db = await this.openDatabase();
    const tx = db.transaction(STORES.SYNC_QUEUE, 'readwrite');
    const store = tx.objectStore(STORES.SYNC_QUEUE);
    const index = store.index('status');

    const keys = await this.promisifyRequest<IDBValidKey[]>(index.getAllKeys('synced'));

    for (const key of keys) {
      store.delete(key);
    }

    await this.promisifyTransaction(tx);
  }

  /**
   * Resolve a conflict
   */
  async resolveConflict(
    id: string,
    resolution: 'server' | 'client' | 'merge',
    mergedData?: Record<string, unknown>
  ): Promise<void> {
    const api = useApi();

    const db = await this.openDatabase();
    const tx = db.transaction(STORES.SYNC_QUEUE, 'readwrite');
    const store = tx.objectStore(STORES.SYNC_QUEUE);

    const item = await this.promisifyRequest<SyncQueueItem>(store.get(id));

    if (!item || item.status !== 'conflict') {
      throw new Error('Item not found or not in conflict state');
    }

    try {
      // Send resolution to server
      await api.post('/offline/conflicts/' + id + '/resolve', {
        resolution,
        merged_data: mergedData,
      });

      if (resolution === 'server') {
        // Discard local changes
        await this.promisifyRequest(store.delete(id));
      } else {
        // Reset for retry with local or merged data
        const updated: SyncQueueItem = {
          ...item,
          status: 'pending',
          attempts: 0,
          conflictData: undefined,
          payload: mergedData || item.payload,
        };
        await this.promisifyRequest(store.put(updated));
      }

      this.notifyStatusChange();
    } catch (error) {
      console.error('Failed to resolve conflict:', error);
      throw error;
    }
  }

  // ============================================
  // Sync Methods
  // ============================================

  /**
   * Trigger sync process
   */
  async triggerSync(): Promise<SyncResult | null> {
    if (!this.isOnline || this.syncInProgress) {
      return null;
    }

    this.syncInProgress = true;
    const api = useApi();

    try {
      const pendingItems = await this.getPendingItems();

      if (pendingItems.length === 0) {
        return { synced: 0, failed: 0, conflicts: 0, items: [] };
      }

      // Prepare batch for server
      const changes = pendingItems.slice(0, 10).map((item) => ({
        operation: item.operation,
        entity_type: item.entityType,
        entity_id: item.entityId,
        payload: item.payload,
        priority: item.priority,
        client_id: item.clientId,
      }));

      // Mark as processing
      for (const item of pendingItems.slice(0, 10)) {
        await this.updateSyncItemStatus(item.id, 'processing');
      }

      // Send to server
      const response = await api.post<{ data: { results: Array<{
        index: number;
        success: boolean;
        uuid?: string;
        client_id?: string;
        error?: string;
      }> } }>('/offline/queue/batch', { changes });

      const results = response.data.results;
      const syncResult: SyncResult = {
        synced: 0,
        failed: 0,
        conflicts: 0,
        items: [],
      };

      // Process results
      for (const result of results) {
        const item = pendingItems[result.index];
        if (!item) continue;

        if (result.success) {
          await this.updateSyncItemStatus(item.id, 'synced');
          syncResult.synced++;
        } else {
          await this.updateSyncItemStatus(item.id, 'failed', {
            errorMessage: result.error,
            attempts: item.attempts + 1,
          });
          syncResult.failed++;
        }

        syncResult.items.push({
          clientId: item.clientId,
          status: result.success ? 'synced' : 'failed',
          message: result.error,
        });
      }

      // Clean up synced items
      await this.removeSyncedItems();

      this.notifyStatusChange();

      return syncResult;
    } catch (error) {
      console.error('Sync failed:', error);

      // Mark items as pending again
      const pendingItems = await this.getPendingItems();
      for (const item of pendingItems.filter((i) => i.status === 'processing')) {
        await this.updateSyncItemStatus(item.id, 'pending');
      }

      return null;
    } finally {
      this.syncInProgress = false;
    }
  }

  // ============================================
  // Cache Management
  // ============================================

  /**
   * Request cache for specific URLs via service worker
   */
  async cacheUrls(urls: string[]): Promise<void> {
    const registration = await navigator.serviceWorker.ready;

    return new Promise((resolve) => {
      const messageChannel = new MessageChannel();
      messageChannel.port1.onmessage = () => resolve();

      registration.active?.postMessage(
        { type: 'CACHE_URLS', data: { urls } },
        [messageChannel.port2]
      );
    });
  }

  /**
   * Clear all caches
   */
  async clearAllCaches(): Promise<void> {
    // Clear IndexedDB
    const db = await this.openDatabase();

    const stores = [STORES.ENTITIES, STORES.CACHE_META];
    for (const storeName of stores) {
      const tx = db.transaction(storeName, 'readwrite');
      tx.objectStore(storeName).clear();
      await this.promisifyTransaction(tx);
    }

    // Clear service worker caches
    const registration = await navigator.serviceWorker.ready;
    registration.active?.postMessage({ type: 'CLEAR_CACHE' });

    this.notifyStatusChange();
  }

  /**
   * Get cache status from service worker
   */
  async getCacheStatus(): Promise<unknown> {
    const registration = await navigator.serviceWorker.ready;

    return new Promise((resolve) => {
      const messageChannel = new MessageChannel();
      messageChannel.port1.onmessage = (event) => resolve(event.data);

      registration.active?.postMessage(
        { type: 'GET_CACHE_STATUS' },
        [messageChannel.port2]
      );
    });
  }

  // ============================================
  // Status Methods
  // ============================================

  /**
   * Get current offline status
   */
  async getStatus(): Promise<OfflineStatus> {
    const pendingItems = await this.getPendingItems();
    const conflicts = await this.getConflicts();
    const storageEstimate = await navigator.storage?.estimate();

    return {
      isOnline: this.isOnline,
      pendingCount: pendingItems.length,
      conflictCount: conflicts.length,
      storageUsed: storageEstimate?.usage || 0,
    };
  }

  /**
   * Subscribe to status changes
   */
  onStatusChange(callback: (status: OfflineStatus) => void): () => void {
    this.statusListeners.add(callback);
    return () => this.statusListeners.delete(callback);
  }

  /**
   * Notify all status listeners
   */
  private async notifyStatusChange(): Promise<void> {
    const status = await this.getStatus();
    this.statusListeners.forEach((callback) => callback(status));
  }

  // ============================================
  // Utility Methods
  // ============================================

  /**
   * Promisify IDBRequest
   */
  private promisifyRequest<T>(request: IDBRequest<T>): Promise<T> {
    return new Promise((resolve, reject) => {
      request.onerror = () => reject(request.error);
      request.onsuccess = () => resolve(request.result);
    });
  }

  /**
   * Promisify IDBTransaction
   */
  private promisifyTransaction(tx: IDBTransaction): Promise<void> {
    return new Promise((resolve, reject) => {
      tx.onerror = () => reject(tx.error);
      tx.oncomplete = () => resolve();
    });
  }

  /**
   * Check if browser supports offline features
   */
  static isSupported(): boolean {
    return (
      'indexedDB' in window &&
      'serviceWorker' in navigator &&
      'caches' in window
    );
  }
}

// Singleton instance
let offlineManagerInstance: OfflineManager | null = null;

/**
 * Get or create the offline manager instance
 */
export function getOfflineManager(): OfflineManager {
  if (!offlineManagerInstance) {
    offlineManagerInstance = new OfflineManager();
  }
  return offlineManagerInstance;
}

/**
 * Initialize offline capability
 */
export async function initOffline(): Promise<OfflineManager> {
  const manager = getOfflineManager();
  await manager.init();
  return manager;
}

export default OfflineManager;
