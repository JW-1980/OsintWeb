<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'OsintWeb') }} - Offline</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #e0e0e0;
        }

        .container {
            text-align: center;
            padding: 2rem;
            max-width: 500px;
        }

        .icon {
            width: 120px;
            height: 120px;
            margin: 0 auto 2rem;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .icon svg {
            width: 60px;
            height: 60px;
            stroke: #e94560;
        }

        h1 {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: #fff;
        }

        p {
            font-size: 1.1rem;
            line-height: 1.6;
            margin-bottom: 2rem;
            color: #b0b0b0;
        }

        .status {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .status-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .status-item:last-child {
            border-bottom: none;
        }

        .status-label {
            color: #888;
        }

        .status-value {
            font-weight: 600;
            color: #e94560;
        }

        .status-value.synced {
            color: #4ade80;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.875rem 1.5rem;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
        }

        .btn-primary {
            background: #e94560;
            color: #fff;
        }

        .btn-primary:hover {
            background: #d63752;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: transparent;
            border: 2px solid rgba(255, 255, 255, 0.2);
            color: #fff;
            margin-left: 1rem;
        }

        .btn-secondary:hover {
            border-color: rgba(255, 255, 255, 0.4);
        }

        .actions {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .hint {
            margin-top: 2rem;
            font-size: 0.875rem;
            color: #666;
        }

        @media (max-width: 480px) {
            .actions {
                flex-direction: column;
            }

            .btn-secondary {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
            </svg>
        </div>

        <h1>You're Offline</h1>
        <p>It looks like you've lost your internet connection. Some features may be unavailable until you're back online.</p>

        <div class="status">
            <div class="status-item">
                <span class="status-label">Connection Status</span>
                <span class="status-value" id="connection-status">Offline</span>
            </div>
            <div class="status-item">
                <span class="status-label">Pending Changes</span>
                <span class="status-value" id="pending-count">0</span>
            </div>
            <div class="status-item">
                <span class="status-label">Last Sync</span>
                <span class="status-value synced" id="last-sync">Never</span>
            </div>
        </div>

        <div class="actions">
            <button class="btn btn-primary" onclick="tryReconnect()">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                </svg>
                Try Again
            </button>
            <button class="btn btn-secondary" onclick="goToCachedPage()">
                View Cached Data
            </button>
        </div>

        <p class="hint">Your changes will be synced automatically when you're back online.</p>
    </div>

    <script>
        // Check online status periodically
        function checkOnlineStatus() {
            if (navigator.onLine) {
                document.getElementById('connection-status').textContent = 'Online';
                document.getElementById('connection-status').classList.add('synced');
                // Redirect to home after a short delay
                setTimeout(() => {
                    window.location.href = '/';
                }, 1000);
            }
        }

        // Listen for online/offline events
        window.addEventListener('online', checkOnlineStatus);
        window.addEventListener('offline', () => {
            document.getElementById('connection-status').textContent = 'Offline';
            document.getElementById('connection-status').classList.remove('synced');
        });

        // Get pending changes count from IndexedDB
        async function updatePendingCount() {
            try {
                const request = indexedDB.open('osint-offline-db', 2);
                request.onsuccess = () => {
                    const db = request.result;
                    if (db.objectStoreNames.contains('sync-queue')) {
                        const tx = db.transaction('sync-queue', 'readonly');
                        const store = tx.objectStore('sync-queue');
                        const countRequest = store.count();
                        countRequest.onsuccess = () => {
                            document.getElementById('pending-count').textContent = countRequest.result;
                        };
                    }
                };
            } catch (e) {
                console.error('Failed to get pending count:', e);
            }
        }

        // Update last sync time
        function updateLastSync() {
            const lastSync = localStorage.getItem('lastSyncAt');
            if (lastSync) {
                const date = new Date(lastSync);
                document.getElementById('last-sync').textContent = date.toLocaleString();
            }
        }

        function tryReconnect() {
            checkOnlineStatus();
            if (!navigator.onLine) {
                alert('Still offline. Please check your internet connection.');
            }
        }

        function goToCachedPage() {
            // Navigate to the main app - service worker will serve cached content
            window.location.href = '/';
        }

        // Initial checks
        checkOnlineStatus();
        updatePendingCount();
        updateLastSync();

        // Periodic check
        setInterval(checkOnlineStatus, 5000);
    </script>
</body>
</html>
