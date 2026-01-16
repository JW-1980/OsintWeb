<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\HealthCheckLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Health Check Service
 *
 * Provides comprehensive system health monitoring capabilities including
 * database, cache, queue, storage, and external service checks.
 *
 * Usage:
 * - runFullCheck(): Perform all health checks
 * - runQuickCheck(): Perform essential checks only
 * - checkDatabase(): Test database connectivity and performance
 * - checkCache(): Test cache read/write operations
 * - checkQueue(): Test queue connectivity and job processing
 * - checkStorage(): Test disk space and write permissions
 * - checkExternalServices(): Test third-party API connectivity
 */
class HealthCheckService
{
    private const CACHE_KEY_SCHEDULER = 'health:scheduler:last_run';
    private const CACHE_KEY_QUICK_CHECK = 'health:quick:result';
    private const QUICK_CHECK_CACHE_TTL = 30; // seconds

    /**
     * Run a full system health check
     *
     * @param bool $log Whether to save the result to the database
     * @return array
     */
    public function runFullCheck(bool $log = true): array
    {
        $startTime = microtime(true);
        $checks = [];
        $failedChecks = [];
        $warnings = [];

        // Database
        $checks['database'] = $this->checkDatabase();
        if (!$checks['database']['healthy']) {
            $failedChecks[] = 'database';
        }
        if (!empty($checks['database']['warnings'])) {
            $warnings = array_merge($warnings, $checks['database']['warnings']);
        }

        // Cache
        $checks['cache'] = $this->checkCache();
        if (!$checks['cache']['healthy']) {
            $failedChecks[] = 'cache';
        }

        // Queue
        $checks['queue'] = $this->checkQueue();
        if (!$checks['queue']['healthy']) {
            $failedChecks[] = 'queue';
        }
        if (!empty($checks['queue']['warnings'])) {
            $warnings = array_merge($warnings, $checks['queue']['warnings']);
        }

        // Storage
        $checks['storage'] = $this->checkStorage();
        if (!$checks['storage']['healthy']) {
            $failedChecks[] = 'storage';
        }
        if (!empty($checks['storage']['warnings'])) {
            $warnings = array_merge($warnings, $checks['storage']['warnings']);
        }

        // Search (Meilisearch)
        $checks['search'] = $this->checkSearch();
        if (!$checks['search']['healthy'] && $checks['search']['configured']) {
            $failedChecks[] = 'search';
        }

        // Migrations
        $checks['migrations'] = $this->checkMigrations();
        if (!$checks['migrations']['healthy']) {
            $warnings[] = 'Pending migrations detected';
        }

        // Scheduler
        $checks['scheduler'] = $this->checkScheduler();
        if (!$checks['scheduler']['healthy']) {
            $warnings[] = 'Scheduler may not be running';
        }

        // External services
        $checks['external'] = $this->checkExternalServices();
        foreach ($checks['external']['services'] as $serviceName => $serviceCheck) {
            if (!$serviceCheck['healthy'] && $serviceCheck['configured']) {
                $warnings[] = "External service '{$serviceName}' is unavailable";
            }
        }

        // System metrics
        $checks['system'] = $this->getSystemMetrics();

        // Database metrics
        $checks['database_metrics'] = $this->getDatabaseMetrics();

        // Queue metrics
        $checks['queue_metrics'] = $this->getQueueMetrics();

        $durationMs = (int) ((microtime(true) - $startTime) * 1000);

        // Determine overall status
        $status = $this->determineOverallStatus($failedChecks, $warnings);

        $result = [
            'status' => $status,
            'timestamp' => now()->toIso8601String(),
            'duration_ms' => $durationMs,
            'checks' => $checks,
            'failed_checks' => $failedChecks,
            'warnings' => $warnings,
            'summary' => [
                'total_checks' => count($checks),
                'failed_count' => count($failedChecks),
                'warning_count' => count($warnings),
            ],
        ];

        if ($log) {
            $this->logCheck(HealthCheckLog::TYPE_FULL, $result);
        }

        return $result;
    }

    /**
     * Run a quick health check (essential checks only)
     *
     * @param bool $useCache Whether to use cached result if available
     * @return array
     */
    public function runQuickCheck(bool $useCache = true): array
    {
        // Return cached result if recent
        if ($useCache) {
            $cached = Cache::get(self::CACHE_KEY_QUICK_CHECK);
            if ($cached) {
                $cached['from_cache'] = true;
                return $cached;
            }
        }

        $startTime = microtime(true);
        $checks = [];
        $failedChecks = [];

        // Database (essential)
        $dbCheck = $this->checkDatabase();
        $checks['database'] = [
            'healthy' => $dbCheck['healthy'],
            'response_time_ms' => $dbCheck['response_time_ms'] ?? null,
        ];
        if (!$dbCheck['healthy']) {
            $failedChecks[] = 'database';
        }

        // Cache (essential)
        $cacheCheck = $this->checkCache();
        $checks['cache'] = [
            'healthy' => $cacheCheck['healthy'],
            'driver' => $cacheCheck['driver'] ?? null,
        ];
        if (!$cacheCheck['healthy']) {
            $failedChecks[] = 'cache';
        }

        // Storage (essential)
        $storageCheck = $this->checkStorage();
        $checks['storage'] = [
            'healthy' => $storageCheck['healthy'],
            'writable' => $storageCheck['writable'] ?? false,
        ];
        if (!$storageCheck['healthy']) {
            $failedChecks[] = 'storage';
        }

        $durationMs = (int) ((microtime(true) - $startTime) * 1000);
        $status = empty($failedChecks) ? HealthCheckLog::STATUS_HEALTHY : HealthCheckLog::STATUS_UNHEALTHY;

        $result = [
            'status' => $status,
            'timestamp' => now()->toIso8601String(),
            'duration_ms' => $durationMs,
            'checks' => $checks,
            'failed_checks' => $failedChecks,
            'from_cache' => false,
        ];

        // Cache the result
        Cache::put(self::CACHE_KEY_QUICK_CHECK, $result, self::QUICK_CHECK_CACHE_TTL);

        return $result;
    }

    /**
     * Check database connectivity and performance
     *
     * @return array
     */
    public function checkDatabase(): array
    {
        $result = [
            'healthy' => false,
            'connection' => config('database.default'),
            'response_time_ms' => null,
            'version' => null,
            'warnings' => [],
        ];

        try {
            $startTime = microtime(true);

            // Test basic connectivity
            DB::connection()->getPdo();

            // Run a simple query to measure response time
            DB::select('SELECT 1');

            $result['response_time_ms'] = (int) ((microtime(true) - $startTime) * 1000);

            // Get database version
            try {
                $version = DB::select('SELECT VERSION() as version')[0]->version ?? null;
                $result['version'] = $version;
            } catch (\Exception $e) {
                // Ignore version check failure
            }

            // Check for slow response
            $threshold = config('health.thresholds.database_response_ms', 100);
            if ($result['response_time_ms'] > $threshold) {
                $result['warnings'][] = "Database response time ({$result['response_time_ms']}ms) exceeds threshold ({$threshold}ms)";
            }

            $result['healthy'] = true;

        } catch (\Exception $e) {
            $result['error'] = $e->getMessage();

            Log::error('Health check: Database connection failed', [
                'error' => $e->getMessage(),
            ]);
        }

        return $result;
    }

    /**
     * Check cache read/write operations
     *
     * @return array
     */
    public function checkCache(): array
    {
        $result = [
            'healthy' => false,
            'driver' => config('cache.default'),
            'read_write_test' => false,
        ];

        try {
            $testKey = 'health_check_' . Str::random(8);
            $testValue = 'test_' . time();

            // Write test
            Cache::put($testKey, $testValue, 60);

            // Read test
            $retrieved = Cache::get($testKey);

            // Cleanup
            Cache::forget($testKey);

            if ($retrieved === $testValue) {
                $result['read_write_test'] = true;
                $result['healthy'] = true;
            } else {
                $result['error'] = 'Cache read/write mismatch';
            }

        } catch (\Exception $e) {
            $result['error'] = $e->getMessage();

            Log::error('Health check: Cache test failed', [
                'error' => $e->getMessage(),
            ]);
        }

        return $result;
    }

    /**
     * Check Meilisearch if configured
     *
     * @return array
     */
    public function checkSearch(): array
    {
        $result = [
            'healthy' => true,
            'configured' => false,
            'engine' => null,
        ];

        $meilisearchHost = config('scout.meilisearch.host');

        if (empty($meilisearchHost)) {
            $result['engine'] = 'none';
            return $result;
        }

        $result['configured'] = true;
        $result['engine'] = 'meilisearch';
        $result['host'] = $meilisearchHost;

        try {
            $startTime = microtime(true);

            $response = Http::timeout(5)->get($meilisearchHost . '/health');

            $result['response_time_ms'] = (int) ((microtime(true) - $startTime) * 1000);

            if ($response->successful()) {
                $health = $response->json();
                $result['status'] = $health['status'] ?? 'unknown';
                $result['healthy'] = ($health['status'] ?? '') === 'available';
            } else {
                $result['healthy'] = false;
                $result['error'] = 'Meilisearch returned status ' . $response->status();
            }

        } catch (\Exception $e) {
            $result['healthy'] = false;
            $result['error'] = $e->getMessage();
        }

        return $result;
    }

    /**
     * Check queue connection and job status
     *
     * @return array
     */
    public function checkQueue(): array
    {
        $result = [
            'healthy' => false,
            'driver' => config('queue.default'),
            'warnings' => [],
        ];

        try {
            $driver = config('queue.default');

            if ($driver === 'sync') {
                $result['healthy'] = true;
                $result['note'] = 'Sync driver - jobs run immediately';
                return $result;
            }

            if ($driver === 'database') {
                // Check jobs table exists
                if (!Schema::hasTable('jobs')) {
                    $result['error'] = 'Jobs table does not exist';
                    return $result;
                }

                // Check for stuck jobs (jobs older than 1 hour that haven't been reserved)
                $stuckJobsThreshold = config('health.thresholds.stuck_jobs_minutes', 60);
                $stuckJobs = DB::table('jobs')
                    ->where('created_at', '<', now()->subMinutes($stuckJobsThreshold))
                    ->whereNull('reserved_at')
                    ->count();

                if ($stuckJobs > 0) {
                    $result['warnings'][] = "{$stuckJobs} job(s) have been waiting for over {$stuckJobsThreshold} minutes";
                    $result['stuck_jobs'] = $stuckJobs;
                }

                // Check failed jobs
                if (Schema::hasTable('failed_jobs')) {
                    $failedJobs = DB::table('failed_jobs')
                        ->where('failed_at', '>=', now()->subHours(24))
                        ->count();

                    if ($failedJobs > 0) {
                        $result['warnings'][] = "{$failedJobs} job(s) failed in the last 24 hours";
                        $result['recent_failed_jobs'] = $failedJobs;
                    }
                }

                $result['healthy'] = true;
            } elseif ($driver === 'redis') {
                // Test Redis connection
                try {
                    Queue::connection('redis')->size('default');
                    $result['healthy'] = true;
                } catch (\Exception $e) {
                    $result['error'] = 'Redis queue connection failed: ' . $e->getMessage();
                }
            } else {
                $result['healthy'] = true;
                $result['note'] = "Queue driver: {$driver}";
            }

        } catch (\Exception $e) {
            $result['error'] = $e->getMessage();

            Log::error('Health check: Queue check failed', [
                'error' => $e->getMessage(),
            ]);
        }

        return $result;
    }

    /**
     * Check storage disk space and write permissions
     *
     * @return array
     */
    public function checkStorage(): array
    {
        $result = [
            'healthy' => false,
            'writable' => false,
            'disks' => [],
            'warnings' => [],
        ];

        // Check storage disk
        $storagePath = storage_path();
        $diskFreeSpace = @disk_free_space($storagePath);
        $diskTotalSpace = @disk_total_space($storagePath);

        if ($diskFreeSpace !== false && $diskTotalSpace !== false) {
            $usedPercent = (($diskTotalSpace - $diskFreeSpace) / $diskTotalSpace) * 100;
            $freeGB = round($diskFreeSpace / 1024 / 1024 / 1024, 2);

            $result['disks']['storage'] = [
                'path' => $storagePath,
                'free_space_gb' => $freeGB,
                'used_percent' => round($usedPercent, 2),
            ];

            // Check disk space thresholds
            $warningThreshold = config('health.thresholds.disk_warning_percent', 80);
            $criticalThreshold = config('health.thresholds.disk_critical_percent', 95);

            if ($usedPercent >= $criticalThreshold) {
                $result['warnings'][] = "Critical: Disk usage at {$usedPercent}%";
            } elseif ($usedPercent >= $warningThreshold) {
                $result['warnings'][] = "Warning: Disk usage at {$usedPercent}%";
            }
        }

        // Test write permissions
        try {
            $testFile = storage_path('health_check_test_' . Str::random(8) . '.tmp');
            file_put_contents($testFile, 'test');
            $content = file_get_contents($testFile);
            unlink($testFile);

            $result['writable'] = ($content === 'test');
        } catch (\Exception $e) {
            $result['writable'] = false;
            $result['warnings'][] = 'Storage directory is not writable: ' . $e->getMessage();
        }

        // Check Laravel Storage disks
        foreach (['local', 'public'] as $disk) {
            try {
                $diskInstance = Storage::disk($disk);
                $testPath = 'health_check_' . Str::random(8) . '.tmp';

                $diskInstance->put($testPath, 'test');
                $diskInstance->delete($testPath);

                $result['disks'][$disk] = [
                    'writable' => true,
                ];
            } catch (\Exception $e) {
                $result['disks'][$disk] = [
                    'writable' => false,
                    'error' => $e->getMessage(),
                ];
            }
        }

        $result['healthy'] = $result['writable'];

        return $result;
    }

    /**
     * Check external service connectivity
     *
     * @return array
     */
    public function checkExternalServices(): array
    {
        $result = [
            'services' => [],
        ];

        $services = config('health.external_services', []);

        foreach ($services as $name => $config) {
            $serviceResult = [
                'healthy' => false,
                'configured' => !empty($config['url']),
                'response_time_ms' => null,
            ];

            if (empty($config['url'])) {
                $serviceResult['note'] = 'Not configured';
                $result['services'][$name] = $serviceResult;
                continue;
            }

            try {
                $startTime = microtime(true);
                $timeout = $config['timeout'] ?? 5;

                $response = Http::timeout($timeout)->get($config['url']);

                $serviceResult['response_time_ms'] = (int) ((microtime(true) - $startTime) * 1000);
                $serviceResult['status_code'] = $response->status();
                $serviceResult['healthy'] = $response->successful();

            } catch (\Exception $e) {
                $serviceResult['error'] = $e->getMessage();
            }

            $result['services'][$name] = $serviceResult;
        }

        return $result;
    }

    /**
     * Check if all migrations have been run
     *
     * @return array
     */
    public function checkMigrations(): array
    {
        $result = [
            'healthy' => true,
            'pending' => [],
        ];

        try {
            // Get ran migrations
            $ran = DB::table('migrations')->pluck('migration')->toArray();

            // Get all migration files
            $files = glob(database_path('migrations/*.php'));
            $all = array_map(function ($file) {
                return pathinfo($file, PATHINFO_FILENAME);
            }, $files);

            // Find pending migrations
            $pending = array_diff($all, $ran);

            if (!empty($pending)) {
                $result['healthy'] = false;
                $result['pending'] = array_values($pending);
                $result['pending_count'] = count($pending);
            }

        } catch (\Exception $e) {
            $result['healthy'] = false;
            $result['error'] = $e->getMessage();
        }

        return $result;
    }

    /**
     * Check if the scheduler is running
     *
     * @return array
     */
    public function checkScheduler(): array
    {
        $result = [
            'healthy' => false,
            'last_run' => null,
        ];

        $lastRun = Cache::get(self::CACHE_KEY_SCHEDULER);

        if ($lastRun) {
            $lastRunTime = Carbon::parse($lastRun);
            $result['last_run'] = $lastRunTime->toIso8601String();
            $result['minutes_ago'] = $lastRunTime->diffInMinutes(now());

            // Scheduler should run every minute, so 5 minutes is concerning
            $threshold = config('health.thresholds.scheduler_warning_minutes', 5);
            $result['healthy'] = $result['minutes_ago'] <= $threshold;
        } else {
            $result['note'] = 'No scheduler heartbeat recorded';
        }

        return $result;
    }

    /**
     * Record scheduler heartbeat
     */
    public function recordSchedulerHeartbeat(): void
    {
        Cache::put(self::CACHE_KEY_SCHEDULER, now()->toIso8601String(), 60 * 10);
    }

    /**
     * Get system metrics (CPU, memory, disk)
     *
     * @return array
     */
    public function getSystemMetrics(): array
    {
        $metrics = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'server_time' => now()->toIso8601String(),
            'timezone' => config('app.timezone'),
            'environment' => config('app.env'),
            'debug_mode' => config('app.debug'),
        ];

        // Memory usage
        $metrics['memory'] = [
            'current_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
            'peak_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
            'limit' => ini_get('memory_limit'),
        ];

        // Disk usage
        $storagePath = storage_path();
        $diskFree = @disk_free_space($storagePath);
        $diskTotal = @disk_total_space($storagePath);

        if ($diskFree !== false && $diskTotal !== false) {
            $metrics['disk'] = [
                'free_gb' => round($diskFree / 1024 / 1024 / 1024, 2),
                'total_gb' => round($diskTotal / 1024 / 1024 / 1024, 2),
                'used_percent' => round((($diskTotal - $diskFree) / $diskTotal) * 100, 2),
            ];
        }

        // CPU load (Unix only)
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            $metrics['cpu_load'] = [
                '1min' => $load[0],
                '5min' => $load[1],
                '15min' => $load[2],
            ];
        }

        // Uptime
        if (defined('LARAVEL_START')) {
            $metrics['request_duration_ms'] = round((microtime(true) - LARAVEL_START) * 1000, 2);
        }

        return $metrics;
    }

    /**
     * Get database-specific metrics
     *
     * @return array
     */
    public function getDatabaseMetrics(): array
    {
        $metrics = [];

        try {
            $driver = config('database.default');

            if ($driver === 'mysql') {
                // Connection count
                $connections = DB::select("SHOW STATUS WHERE Variable_name = 'Threads_connected'");
                $metrics['active_connections'] = (int) ($connections[0]->Value ?? 0);

                // Max connections
                $maxConnections = DB::select("SHOW VARIABLES WHERE Variable_name = 'max_connections'");
                $metrics['max_connections'] = (int) ($maxConnections[0]->Value ?? 0);

                // Connection usage percentage
                if ($metrics['max_connections'] > 0) {
                    $metrics['connection_usage_percent'] = round(
                        ($metrics['active_connections'] / $metrics['max_connections']) * 100,
                        2
                    );
                }

                // Uptime
                $uptime = DB::select("SHOW STATUS WHERE Variable_name = 'Uptime'");
                $metrics['uptime_seconds'] = (int) ($uptime[0]->Value ?? 0);
                $metrics['uptime_days'] = round($metrics['uptime_seconds'] / 86400, 2);

                // Slow queries
                $slowQueries = DB::select("SHOW STATUS WHERE Variable_name = 'Slow_queries'");
                $metrics['slow_queries_total'] = (int) ($slowQueries[0]->Value ?? 0);
            }

            // Table sizes (for any database)
            $tables = DB::select('SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = ?', [
                config('database.connections.' . $driver . '.database'),
            ]);
            $metrics['table_count'] = $tables[0]->count ?? 0;

        } catch (\Exception $e) {
            $metrics['error'] = $e->getMessage();
        }

        return $metrics;
    }

    /**
     * Get queue-specific metrics
     *
     * @return array
     */
    public function getQueueMetrics(): array
    {
        $metrics = [
            'driver' => config('queue.default'),
        ];

        try {
            if (config('queue.default') === 'database' && Schema::hasTable('jobs')) {
                $metrics['pending_jobs'] = DB::table('jobs')->count();
                $metrics['reserved_jobs'] = DB::table('jobs')
                    ->whereNotNull('reserved_at')
                    ->count();

                // Jobs by queue
                $metrics['by_queue'] = DB::table('jobs')
                    ->selectRaw('queue, COUNT(*) as count')
                    ->groupBy('queue')
                    ->pluck('count', 'queue')
                    ->toArray();

                // Oldest pending job age
                $oldest = DB::table('jobs')
                    ->whereNull('reserved_at')
                    ->orderBy('created_at')
                    ->first();

                if ($oldest) {
                    $metrics['oldest_job_age_minutes'] = now()->diffInMinutes(
                        Carbon::createFromTimestamp($oldest->created_at)
                    );
                }
            }

            if (Schema::hasTable('failed_jobs')) {
                $metrics['failed_jobs_total'] = DB::table('failed_jobs')->count();
                $metrics['failed_jobs_24h'] = DB::table('failed_jobs')
                    ->where('failed_at', '>=', now()->subHours(24))
                    ->count();
            }

        } catch (\Exception $e) {
            $metrics['error'] = $e->getMessage();
        }

        return $metrics;
    }

    /**
     * Check a specific component
     *
     * @param string $component
     * @return array
     */
    public function checkComponent(string $component): array
    {
        $startTime = microtime(true);

        $result = match ($component) {
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'search' => $this->checkSearch(),
            'queue' => $this->checkQueue(),
            'storage' => $this->checkStorage(),
            'external' => $this->checkExternalServices(),
            'migrations' => $this->checkMigrations(),
            'scheduler' => $this->checkScheduler(),
            default => ['error' => 'Unknown component: ' . $component, 'healthy' => false],
        };

        $result['component'] = $component;
        $result['duration_ms'] = (int) ((microtime(true) - $startTime) * 1000);
        $result['timestamp'] = now()->toIso8601String();

        // Log this check
        $this->logCheck($component, [
            'status' => $result['healthy'] ? HealthCheckLog::STATUS_HEALTHY : HealthCheckLog::STATUS_UNHEALTHY,
            'duration_ms' => $result['duration_ms'],
            'checks' => [$component => $result],
            'failed_checks' => $result['healthy'] ? [] : [$component],
            'warnings' => $result['warnings'] ?? [],
        ]);

        return $result;
    }

    /**
     * Log a health check result
     *
     * @param string $type
     * @param array $result
     * @return HealthCheckLog
     */
    public function logCheck(string $type, array $result): HealthCheckLog
    {
        return HealthCheckLog::create([
            'check_type' => $type,
            'status' => $result['status'] ?? $this->determineOverallStatus(
                $result['failed_checks'] ?? [],
                $result['warnings'] ?? []
            ),
            'duration_ms' => $result['duration_ms'] ?? 0,
            'checks_performed' => $result['checks'] ?? [],
            'failed_checks' => $result['failed_checks'] ?? [],
            'warnings' => $result['warnings'] ?? [],
            'metadata' => [
                'summary' => $result['summary'] ?? null,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'triggered_by_user_id' => auth()->id(),
        ]);
    }

    /**
     * Generate Prometheus-style metrics
     *
     * @return string
     */
    public function generatePrometheusMetrics(): string
    {
        $result = $this->runQuickCheck(useCache: true);
        $system = $this->getSystemMetrics();
        $queue = $this->getQueueMetrics();

        $lines = [];

        // Help and type declarations
        $lines[] = '# HELP osint_health_status Overall system health status (1=healthy, 0=unhealthy)';
        $lines[] = '# TYPE osint_health_status gauge';
        $lines[] = 'osint_health_status ' . ($result['status'] === HealthCheckLog::STATUS_HEALTHY ? '1' : '0');

        $lines[] = '';
        $lines[] = '# HELP osint_health_check_duration_ms Duration of health check in milliseconds';
        $lines[] = '# TYPE osint_health_check_duration_ms gauge';
        $lines[] = 'osint_health_check_duration_ms ' . $result['duration_ms'];

        // Component statuses
        $lines[] = '';
        $lines[] = '# HELP osint_component_health Health status per component (1=healthy, 0=unhealthy)';
        $lines[] = '# TYPE osint_component_health gauge';

        foreach ($result['checks'] as $component => $check) {
            $healthy = ($check['healthy'] ?? false) ? '1' : '0';
            $lines[] = "osint_component_health{component=\"{$component}\"} {$healthy}";
        }

        // Memory metrics
        if (isset($system['memory'])) {
            $lines[] = '';
            $lines[] = '# HELP osint_memory_usage_mb Current memory usage in megabytes';
            $lines[] = '# TYPE osint_memory_usage_mb gauge';
            $lines[] = 'osint_memory_usage_mb ' . $system['memory']['current_mb'];

            $lines[] = '';
            $lines[] = '# HELP osint_memory_peak_mb Peak memory usage in megabytes';
            $lines[] = '# TYPE osint_memory_peak_mb gauge';
            $lines[] = 'osint_memory_peak_mb ' . $system['memory']['peak_mb'];
        }

        // Disk metrics
        if (isset($system['disk'])) {
            $lines[] = '';
            $lines[] = '# HELP osint_disk_used_percent Disk usage percentage';
            $lines[] = '# TYPE osint_disk_used_percent gauge';
            $lines[] = 'osint_disk_used_percent ' . $system['disk']['used_percent'];

            $lines[] = '';
            $lines[] = '# HELP osint_disk_free_gb Free disk space in gigabytes';
            $lines[] = '# TYPE osint_disk_free_gb gauge';
            $lines[] = 'osint_disk_free_gb ' . $system['disk']['free_gb'];
        }

        // CPU load
        if (isset($system['cpu_load'])) {
            $lines[] = '';
            $lines[] = '# HELP osint_cpu_load_1m CPU load average over 1 minute';
            $lines[] = '# TYPE osint_cpu_load_1m gauge';
            $lines[] = 'osint_cpu_load_1m ' . $system['cpu_load']['1min'];
        }

        // Queue metrics
        if (isset($queue['pending_jobs'])) {
            $lines[] = '';
            $lines[] = '# HELP osint_queue_pending_jobs Number of pending jobs';
            $lines[] = '# TYPE osint_queue_pending_jobs gauge';
            $lines[] = 'osint_queue_pending_jobs ' . $queue['pending_jobs'];
        }

        if (isset($queue['failed_jobs_total'])) {
            $lines[] = '';
            $lines[] = '# HELP osint_queue_failed_jobs_total Total number of failed jobs';
            $lines[] = '# TYPE osint_queue_failed_jobs_total counter';
            $lines[] = 'osint_queue_failed_jobs_total ' . $queue['failed_jobs_total'];
        }

        return implode("\n", $lines) . "\n";
    }

    /**
     * Determine overall status based on failed checks and warnings
     *
     * @param array $failedChecks
     * @param array $warnings
     * @return string
     */
    private function determineOverallStatus(array $failedChecks, array $warnings): string
    {
        // Critical components
        $criticalComponents = ['database', 'storage'];

        foreach ($failedChecks as $failed) {
            if (in_array($failed, $criticalComponents)) {
                return HealthCheckLog::STATUS_UNHEALTHY;
            }
        }

        if (!empty($failedChecks)) {
            return HealthCheckLog::STATUS_DEGRADED;
        }

        if (!empty($warnings)) {
            return HealthCheckLog::STATUS_DEGRADED;
        }

        return HealthCheckLog::STATUS_HEALTHY;
    }

    /**
     * Get check history
     *
     * @param string|null $type
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection<int, HealthCheckLog>
     */
    public function getHistory(?string $type = null, int $limit = 100): \Illuminate\Database\Eloquent\Collection
    {
        $query = HealthCheckLog::query()
            ->orderBy('created_at', 'desc')
            ->limit($limit);

        if ($type) {
            $query->ofType($type);
        }

        return $query->get();
    }
}
