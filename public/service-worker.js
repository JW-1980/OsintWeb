/**
 * OsintWeb Service Worker
 *
 * Provides offline capability through:
 * - Static asset caching (JS, CSS, images)
 * - API response caching for specified routes
 * - Offline request handling with cached data
 * - Background sync for offline changes
 * - Push notifications for sync status
 */

const CACHE_VERSION = 'v1.0.0';
const STATIC_CACHE_NAME = `osint-static-${CACHE_VERSION}`;
const API_CACHE_NAME = `osint-api-${CACHE_VERSION}`;
const IMAGE_CACHE_NAME = `osint-images-${CACHE_VERSION}`;

// Static assets to cache on install
const STATIC_ASSETS = [
  '/',
  '/offline',
  '/manifest.json',
];

// API routes that can be cached
const CACHEABLE_API_ROUTES = [
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
  '/api/offline/manifest',
  '/api/offline/priority-entities',
];

// API routes that should not be cached
const NO_CACHE_API_ROUTES = [
  '/api/auth',
  '/api/offline/sync',
  '/api/offline/queue',
];

// Maximum age for cached API responses (in seconds)
const API_CACHE_MAX_AGE = 3600; // 1 hour

// IndexedDB configuration
const DB_NAME = 'osint-offline-db';
const DB_VERSION = 1;
const SYNC_QUEUE_STORE = 'sync-queue';
const PENDING_REQUESTS_STORE = 'pending-requests';

/**
 * Install event - cache static assets
 */
self.addEventListener('install', (event) => {
  console.log('[ServiceWorker] Install');

  event.waitUntil(
    caches.open(STATIC_CACHE_NAME)
      .then((cache) => {
        console.log('[ServiceWorker] Caching static assets');
        return cache.addAll(STATIC_ASSETS);
      })
      .then(() => {
        console.log('[ServiceWorker] Skip waiting');
        return self.skipWaiting();
      })
      .catch((error) => {
        console.error('[ServiceWorker] Install failed:', error);
      })
  );
});

/**
 * Activate event - clean up old caches
 */
self.addEventListener('activate', (event) => {
  console.log('[ServiceWorker] Activate');

  event.waitUntil(
    caches.keys()
      .then((cacheNames) => {
        return Promise.all(
          cacheNames
            .filter((cacheName) => {
              return cacheName.startsWith('osint-') &&
                cacheName !== STATIC_CACHE_NAME &&
                cacheName !== API_CACHE_NAME &&
                cacheName !== IMAGE_CACHE_NAME;
            })
            .map((cacheName) => {
              console.log('[ServiceWorker] Deleting old cache:', cacheName);
              return caches.delete(cacheName);
            })
        );
      })
      .then(() => {
        console.log('[ServiceWorker] Claiming clients');
        return self.clients.claim();
      })
  );
});

/**
 * Fetch event - handle requests
 */
self.addEventListener('fetch', (event) => {
  const { request } = event;
  const url = new URL(request.url);

  // Skip non-GET requests for caching (but handle for offline queue)
  if (request.method !== 'GET') {
    event.respondWith(handleMutatingRequest(request));
    return;
  }

  // Handle API requests
  if (url.pathname.startsWith('/api/')) {
    event.respondWith(handleApiRequest(request));
    return;
  }

  // Handle image requests
  if (isImageRequest(request)) {
    event.respondWith(handleImageRequest(request));
    return;
  }

  // Handle static assets and navigation
  event.respondWith(handleStaticRequest(request));
});

/**
 * Handle mutating (POST, PUT, DELETE) requests
 */
async function handleMutatingRequest(request) {
  const url = new URL(request.url);

  // Don't queue auth requests
  if (url.pathname.startsWith('/api/auth')) {
    return fetch(request);
  }

  try {
    // Try to make the request
    const response = await fetch(request.clone());
    return response;
  } catch (error) {
    // If offline, queue the request for later sync
    if (!navigator.onLine && url.pathname.startsWith('/api/')) {
      await queueOfflineRequest(request);

      // Return a synthetic response indicating queued
      return new Response(
        JSON.stringify({
          data: {
            message: 'Request queued for offline sync',
            offline: true,
          }
        }),
        {
          status: 202,
          headers: { 'Content-Type': 'application/json' },
        }
      );
    }

    throw error;
  }
}

/**
 * Handle API requests with caching strategy
 */
async function handleApiRequest(request) {
  const url = new URL(request.url);

  // Check if route should be cached
  const shouldCache = CACHEABLE_API_ROUTES.some(route =>
    url.pathname.startsWith(route)
  );

  const shouldNotCache = NO_CACHE_API_ROUTES.some(route =>
    url.pathname.startsWith(route)
  );

  if (shouldNotCache) {
    return fetch(request);
  }

  // Network-first strategy for cacheable API routes
  if (shouldCache) {
    try {
      const networkResponse = await fetch(request.clone());

      if (networkResponse.ok) {
        // Cache the response
        const cache = await caches.open(API_CACHE_NAME);
        const responseToCache = networkResponse.clone();

        // Add timestamp header for cache invalidation
        const headers = new Headers(responseToCache.headers);
        headers.set('sw-cached-at', Date.now().toString());

        const cachedResponse = new Response(await responseToCache.blob(), {
          status: responseToCache.status,
          statusText: responseToCache.statusText,
          headers: headers,
        });

        await cache.put(request, cachedResponse);
      }

      return networkResponse;
    } catch (error) {
      // Network failed, try cache
      const cachedResponse = await caches.match(request);

      if (cachedResponse) {
        // Check if cache is still valid
        const cachedAt = cachedResponse.headers.get('sw-cached-at');
        if (cachedAt) {
          const age = (Date.now() - parseInt(cachedAt)) / 1000;
          if (age <= API_CACHE_MAX_AGE) {
            console.log('[ServiceWorker] Serving from API cache:', url.pathname);
            return cachedResponse;
          }
        }
        // Return stale cache if available
        return cachedResponse;
      }

      // Return offline response
      return new Response(
        JSON.stringify({
          message: 'Offline - No cached data available',
          offline: true,
        }),
        {
          status: 503,
          headers: { 'Content-Type': 'application/json' },
        }
      );
    }
  }

  // Default: try network, fall back to cache
  try {
    return await fetch(request);
  } catch (error) {
    const cachedResponse = await caches.match(request);
    if (cachedResponse) {
      return cachedResponse;
    }
    throw error;
  }
}

/**
 * Handle image requests with caching
 */
async function handleImageRequest(request) {
  const cache = await caches.open(IMAGE_CACHE_NAME);
  const cachedResponse = await cache.match(request);

  if (cachedResponse) {
    // Refresh cache in background
    fetch(request).then((response) => {
      if (response.ok) {
        cache.put(request, response.clone());
      }
    }).catch(() => {});

    return cachedResponse;
  }

  try {
    const networkResponse = await fetch(request);

    if (networkResponse.ok) {
      cache.put(request, networkResponse.clone());
    }

    return networkResponse;
  } catch (error) {
    // Return placeholder image
    return new Response(
      `<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg">
        <rect width="100" height="100" fill="#e0e0e0"/>
        <text x="50" y="50" font-size="12" text-anchor="middle" fill="#666">Offline</text>
      </svg>`,
      {
        headers: { 'Content-Type': 'image/svg+xml' },
      }
    );
  }
}

/**
 * Handle static requests
 */
async function handleStaticRequest(request) {
  // Cache-first for static assets
  const cachedResponse = await caches.match(request);

  if (cachedResponse) {
    return cachedResponse;
  }

  try {
    const networkResponse = await fetch(request);

    // Cache static assets
    if (networkResponse.ok && shouldCacheStaticAsset(request)) {
      const cache = await caches.open(STATIC_CACHE_NAME);
      cache.put(request, networkResponse.clone());
    }

    return networkResponse;
  } catch (error) {
    // Return offline page for navigation requests
    if (request.mode === 'navigate') {
      const offlinePage = await caches.match('/offline');
      if (offlinePage) {
        return offlinePage;
      }
    }

    throw error;
  }
}

/**
 * Check if request is for an image
 */
function isImageRequest(request) {
  const url = new URL(request.url);
  const imageExtensions = ['.jpg', '.jpeg', '.png', '.gif', '.webp', '.svg', '.ico'];
  return imageExtensions.some(ext => url.pathname.toLowerCase().endsWith(ext));
}

/**
 * Check if static asset should be cached
 */
function shouldCacheStaticAsset(request) {
  const url = new URL(request.url);
  const cacheableExtensions = ['.js', '.css', '.woff', '.woff2', '.ttf'];
  return cacheableExtensions.some(ext => url.pathname.endsWith(ext));
}

/**
 * Queue offline request for later sync
 */
async function queueOfflineRequest(request) {
  const db = await openDatabase();
  const tx = db.transaction(PENDING_REQUESTS_STORE, 'readwrite');
  const store = tx.objectStore(PENDING_REQUESTS_STORE);

  const requestData = {
    id: crypto.randomUUID(),
    url: request.url,
    method: request.method,
    headers: Object.fromEntries(request.headers.entries()),
    body: await request.clone().text(),
    timestamp: Date.now(),
  };

  await store.add(requestData);
  console.log('[ServiceWorker] Request queued for offline sync:', request.url);

  // Register for background sync if supported
  if ('sync' in self.registration) {
    try {
      await self.registration.sync.register('offline-sync');
    } catch (error) {
      console.error('[ServiceWorker] Background sync registration failed:', error);
    }
  }

  // Notify clients
  notifyClients({
    type: 'OFFLINE_REQUEST_QUEUED',
    data: { url: request.url, method: request.method },
  });
}

/**
 * Open IndexedDB database
 */
function openDatabase() {
  return new Promise((resolve, reject) => {
    const request = indexedDB.open(DB_NAME, DB_VERSION);

    request.onerror = () => reject(request.error);
    request.onsuccess = () => resolve(request.result);

    request.onupgradeneeded = (event) => {
      const db = event.target.result;

      if (!db.objectStoreNames.contains(SYNC_QUEUE_STORE)) {
        db.createObjectStore(SYNC_QUEUE_STORE, { keyPath: 'id' });
      }

      if (!db.objectStoreNames.contains(PENDING_REQUESTS_STORE)) {
        const store = db.createObjectStore(PENDING_REQUESTS_STORE, { keyPath: 'id' });
        store.createIndex('timestamp', 'timestamp', { unique: false });
      }
    };
  });
}

/**
 * Background sync event handler
 */
self.addEventListener('sync', (event) => {
  console.log('[ServiceWorker] Background sync:', event.tag);

  if (event.tag === 'offline-sync') {
    event.waitUntil(processPendingRequests());
  }
});

/**
 * Process pending offline requests
 */
async function processPendingRequests() {
  const db = await openDatabase();
  const tx = db.transaction(PENDING_REQUESTS_STORE, 'readonly');
  const store = tx.objectStore(PENDING_REQUESTS_STORE);
  const requests = await getAllFromStore(store);

  console.log('[ServiceWorker] Processing', requests.length, 'pending requests');

  let synced = 0;
  let failed = 0;

  for (const requestData of requests) {
    try {
      const response = await fetch(requestData.url, {
        method: requestData.method,
        headers: requestData.headers,
        body: requestData.method !== 'GET' ? requestData.body : undefined,
      });

      if (response.ok) {
        // Remove from queue
        const deleteTx = db.transaction(PENDING_REQUESTS_STORE, 'readwrite');
        await deleteTx.objectStore(PENDING_REQUESTS_STORE).delete(requestData.id);
        synced++;
      } else {
        failed++;
      }
    } catch (error) {
      console.error('[ServiceWorker] Failed to sync request:', error);
      failed++;
    }
  }

  // Notify clients of sync completion
  notifyClients({
    type: 'SYNC_COMPLETED',
    data: { synced, failed, total: requests.length },
  });

  return { synced, failed };
}

/**
 * Get all items from IndexedDB store
 */
function getAllFromStore(store) {
  return new Promise((resolve, reject) => {
    const request = store.getAll();
    request.onerror = () => reject(request.error);
    request.onsuccess = () => resolve(request.result);
  });
}

/**
 * Notify all clients
 */
async function notifyClients(message) {
  const clients = await self.clients.matchAll({ includeUncontrolled: true });
  clients.forEach((client) => {
    client.postMessage(message);
  });
}

/**
 * Message event handler - receive commands from clients
 */
self.addEventListener('message', (event) => {
  const { type, data } = event.data || {};

  switch (type) {
    case 'SKIP_WAITING':
      self.skipWaiting();
      break;

    case 'CACHE_URLS':
      event.waitUntil(cacheUrls(data.urls, data.cacheName || API_CACHE_NAME));
      break;

    case 'CLEAR_CACHE':
      event.waitUntil(clearCache(data?.cacheName));
      break;

    case 'GET_CACHE_STATUS':
      event.waitUntil(getCacheStatus().then((status) => {
        event.ports[0].postMessage(status);
      }));
      break;

    case 'FORCE_SYNC':
      event.waitUntil(processPendingRequests().then((result) => {
        event.ports[0].postMessage(result);
      }));
      break;

    default:
      console.log('[ServiceWorker] Unknown message type:', type);
  }
});

/**
 * Cache multiple URLs
 */
async function cacheUrls(urls, cacheName) {
  const cache = await caches.open(cacheName);

  for (const url of urls) {
    try {
      const response = await fetch(url);
      if (response.ok) {
        const headers = new Headers(response.headers);
        headers.set('sw-cached-at', Date.now().toString());

        const cachedResponse = new Response(await response.blob(), {
          status: response.status,
          statusText: response.statusText,
          headers: headers,
        });

        await cache.put(url, cachedResponse);
      }
    } catch (error) {
      console.error('[ServiceWorker] Failed to cache URL:', url, error);
    }
  }
}

/**
 * Clear cache(s)
 */
async function clearCache(cacheName) {
  if (cacheName) {
    await caches.delete(cacheName);
  } else {
    const cacheNames = await caches.keys();
    await Promise.all(
      cacheNames
        .filter((name) => name.startsWith('osint-'))
        .map((name) => caches.delete(name))
    );
  }

  notifyClients({ type: 'CACHE_CLEARED', data: { cacheName } });
}

/**
 * Get cache status
 */
async function getCacheStatus() {
  const cacheNames = await caches.keys();
  const status = {
    caches: {},
    totalSize: 0,
  };

  for (const name of cacheNames) {
    if (name.startsWith('osint-')) {
      const cache = await caches.open(name);
      const keys = await cache.keys();
      status.caches[name] = {
        count: keys.length,
        urls: keys.map((req) => req.url),
      };
    }
  }

  return status;
}

/**
 * Push notification event handler
 */
self.addEventListener('push', (event) => {
  if (!event.data) return;

  const data = event.data.json();

  const options = {
    body: data.body || 'Sync update available',
    icon: '/images/icon-192.png',
    badge: '/images/badge.png',
    tag: data.tag || 'osint-notification',
    data: data.data || {},
    actions: data.actions || [],
  };

  event.waitUntil(
    self.registration.showNotification(data.title || 'OsintWeb', options)
  );
});

/**
 * Notification click event handler
 */
self.addEventListener('notificationclick', (event) => {
  event.notification.close();

  const urlToOpen = event.notification.data?.url || '/';

  event.waitUntil(
    self.clients.matchAll({ type: 'window' }).then((clientList) => {
      // Focus existing window if available
      for (const client of clientList) {
        if (client.url === urlToOpen && 'focus' in client) {
          return client.focus();
        }
      }
      // Open new window
      return self.clients.openWindow(urlToOpen);
    })
  );
});

console.log('[ServiceWorker] Loaded', CACHE_VERSION);
