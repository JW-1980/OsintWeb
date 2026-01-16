<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\HealthCheckLog;
use App\Services\HealthCheckService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Health Check Artisan Command
 *
 * Run health checks from the command line for monitoring and debugging.
 *
 * Usage:
 *   php artisan health:check                    - Run full health check
 *   php artisan health:check --quick            - Run quick check (essentials only)
 *   php artisan health:check --component=database  - Check specific component
 *   php artisan health:check --json             - Output as JSON
 *   php artisan health:check --prometheus       - Output as Prometheus metrics
 *   php artisan health:check --no-log           - Don't save to database
 */
class HealthCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'health:check
        {--quick : Run quick check (essentials only)}
        {--component= : Check a specific component (database, cache, queue, storage, search, migrations, scheduler, external)}
        {--json : Output results as JSON}
        {--prometheus : Output results as Prometheus metrics}
        {--no-log : Do not save results to database}
        {--verbose-errors : Show detailed error messages}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run system health checks';

    /**
     * Available components for checking
     */
    private const COMPONENTS = [
        'database',
        'cache',
        'queue',
        'storage',
        'search',
        'migrations',
        'scheduler',
        'external',
    ];

    /**
     * Execute the console command.
     *
     * @param HealthCheckService $healthService
     * @return int
     */
    public function handle(HealthCheckService $healthService): int
    {
        $startTime = microtime(true);

        // Prometheus output
        if ($this->option('prometheus')) {
            $this->line($healthService->generatePrometheusMetrics());
            return self::SUCCESS;
        }

        // Component-specific check
        if ($component = $this->option('component')) {
            return $this->checkComponent($healthService, $component);
        }

        // Quick or full check
        $log = !$this->option('no-log');

        if ($this->option('quick')) {
            $result = $healthService->runQuickCheck(useCache: false);
            $this->info('Running quick health check...');
        } else {
            $this->info('Running full health check...');
            $this->newLine();
            $result = $healthService->runFullCheck(log: $log);
        }

        // JSON output
        if ($this->option('json')) {
            $this->line(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            return $result['status'] === HealthCheckLog::STATUS_HEALTHY ? self::SUCCESS : self::FAILURE;
        }

        // Human-readable output
        $this->displayResults($result);

        // Log to application log
        if ($result['status'] !== HealthCheckLog::STATUS_HEALTHY) {
            Log::warning('Health check reported issues', [
                'status' => $result['status'],
                'failed_checks' => $result['failed_checks'] ?? [],
                'warnings' => $result['warnings'] ?? [],
            ]);
        }

        $duration = round((microtime(true) - $startTime) * 1000);
        $this->newLine();
        $this->line("Completed in {$duration}ms");

        return $result['status'] === HealthCheckLog::STATUS_HEALTHY ? self::SUCCESS : self::FAILURE;
    }

    /**
     * Check a specific component
     *
     * @param HealthCheckService $healthService
     * @param string $component
     * @return int
     */
    private function checkComponent(HealthCheckService $healthService, string $component): int
    {
        if (!in_array($component, self::COMPONENTS)) {
            $this->error("Invalid component: {$component}");
            $this->line('Available components: ' . implode(', ', self::COMPONENTS));
            return self::FAILURE;
        }

        $this->info("Checking component: {$component}");
        $this->newLine();

        $result = $healthService->checkComponent($component);

        if ($this->option('json')) {
            $this->line(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            return $result['healthy'] ? self::SUCCESS : self::FAILURE;
        }

        // Display component-specific results
        $statusIcon = $result['healthy'] ? '<fg=green>PASS</>' : '<fg=red>FAIL</>';
        $this->line("{$statusIcon} {$component}");

        if (!empty($result['response_time_ms'])) {
            $this->line("  Response time: {$result['response_time_ms']}ms");
        }

        if (!empty($result['error'])) {
            $this->error("  Error: {$result['error']}");
        }

        if (!empty($result['warnings'])) {
            foreach ($result['warnings'] as $warning) {
                $this->warn("  Warning: {$warning}");
            }
        }

        // Display additional details based on component
        $this->displayComponentDetails($component, $result);

        return $result['healthy'] ? self::SUCCESS : self::FAILURE;
    }

    /**
     * Display component-specific details
     *
     * @param string $component
     * @param array $result
     */
    private function displayComponentDetails(string $component, array $result): void
    {
        switch ($component) {
            case 'database':
                if (!empty($result['version'])) {
                    $this->line("  Version: {$result['version']}");
                }
                if (!empty($result['connection'])) {
                    $this->line("  Connection: {$result['connection']}");
                }
                break;

            case 'cache':
                if (!empty($result['driver'])) {
                    $this->line("  Driver: {$result['driver']}");
                }
                $rwTest = $result['read_write_test'] ? 'Pass' : 'Fail';
                $this->line("  Read/Write Test: {$rwTest}");
                break;

            case 'queue':
                if (!empty($result['driver'])) {
                    $this->line("  Driver: {$result['driver']}");
                }
                if (isset($result['stuck_jobs'])) {
                    $this->line("  Stuck Jobs: {$result['stuck_jobs']}");
                }
                if (isset($result['recent_failed_jobs'])) {
                    $this->line("  Failed Jobs (24h): {$result['recent_failed_jobs']}");
                }
                break;

            case 'storage':
                $writable = $result['writable'] ? 'Yes' : 'No';
                $this->line("  Writable: {$writable}");
                if (!empty($result['disks']['storage']['free_space_gb'])) {
                    $this->line("  Free Space: {$result['disks']['storage']['free_space_gb']} GB");
                    $this->line("  Used: {$result['disks']['storage']['used_percent']}%");
                }
                break;

            case 'search':
                if (!empty($result['engine'])) {
                    $this->line("  Engine: {$result['engine']}");
                }
                $configured = $result['configured'] ? 'Yes' : 'No';
                $this->line("  Configured: {$configured}");
                if (!empty($result['status'])) {
                    $this->line("  Status: {$result['status']}");
                }
                break;

            case 'migrations':
                if (!empty($result['pending'])) {
                    $this->line("  Pending Migrations:");
                    foreach ($result['pending'] as $migration) {
                        $this->line("    - {$migration}");
                    }
                } else {
                    $this->line("  All migrations have been run");
                }
                break;

            case 'scheduler':
                if (!empty($result['last_run'])) {
                    $this->line("  Last Run: {$result['last_run']}");
                    $this->line("  Minutes Ago: {$result['minutes_ago']}");
                } else {
                    $this->line("  No scheduler heartbeat recorded");
                }
                break;

            case 'external':
                if (!empty($result['services'])) {
                    $this->line("  External Services:");
                    foreach ($result['services'] as $name => $service) {
                        $icon = $service['healthy'] ? '<fg=green>OK</>' : '<fg=yellow>--</>';
                        $configured = $service['configured'] ? '' : ' (not configured)';
                        $time = isset($service['response_time_ms']) ? " ({$service['response_time_ms']}ms)" : '';
                        $this->line("    {$icon} {$name}{$configured}{$time}");
                    }
                }
                break;
        }
    }

    /**
     * Display full health check results
     *
     * @param array $result
     */
    private function displayResults(array $result): void
    {
        // Overall status
        $statusColor = match ($result['status']) {
            HealthCheckLog::STATUS_HEALTHY => 'green',
            HealthCheckLog::STATUS_DEGRADED => 'yellow',
            default => 'red',
        };

        $statusText = strtoupper($result['status']);
        $this->line("Overall Status: <fg={$statusColor};options=bold>{$statusText}</>");
        $this->line("Timestamp: {$result['timestamp']}");
        $this->line("Duration: {$result['duration_ms']}ms");
        $this->newLine();

        // Component results
        $this->info('Component Status:');
        $this->newLine();

        $tableData = [];

        foreach ($result['checks'] as $component => $check) {
            // Skip aggregate sections
            if (in_array($component, ['system', 'database_metrics', 'queue_metrics'])) {
                continue;
            }

            $healthy = $check['healthy'] ?? false;
            $status = $healthy ? '<fg=green>PASS</>' : '<fg=red>FAIL</>';

            $details = [];

            // Extract relevant details
            if (isset($check['response_time_ms'])) {
                $details[] = "{$check['response_time_ms']}ms";
            }
            if (isset($check['driver'])) {
                $details[] = $check['driver'];
            }
            if (isset($check['configured']) && !$check['configured']) {
                $details[] = 'not configured';
            }
            if (isset($check['error']) && $this->option('verbose-errors')) {
                $details[] = $check['error'];
            }

            $tableData[] = [
                $component,
                $status,
                implode(', ', $details),
            ];
        }

        $this->table(['Component', 'Status', 'Details'], $tableData);

        // Warnings
        if (!empty($result['warnings'])) {
            $this->newLine();
            $this->warn('Warnings:');
            foreach ($result['warnings'] as $warning) {
                $this->line("  - {$warning}");
            }
        }

        // Failed checks
        if (!empty($result['failed_checks'])) {
            $this->newLine();
            $this->error('Failed Checks:');
            foreach ($result['failed_checks'] as $failed) {
                $this->line("  - {$failed}");
            }
        }

        // System metrics (if full check)
        if (isset($result['checks']['system'])) {
            $this->newLine();
            $this->info('System Metrics:');

            $system = $result['checks']['system'];

            $metricsData = [
                ['PHP Version', $system['php_version'] ?? 'N/A'],
                ['Laravel Version', $system['laravel_version'] ?? 'N/A'],
                ['Environment', $system['environment'] ?? 'N/A'],
                ['Debug Mode', ($system['debug_mode'] ?? false) ? 'Enabled' : 'Disabled'],
            ];

            if (isset($system['memory'])) {
                $metricsData[] = ['Memory Usage', "{$system['memory']['current_mb']} MB (peak: {$system['memory']['peak_mb']} MB)"];
            }

            if (isset($system['disk'])) {
                $metricsData[] = ['Disk Usage', "{$system['disk']['used_percent']}% ({$system['disk']['free_gb']} GB free)"];
            }

            if (isset($system['cpu_load'])) {
                $metricsData[] = ['CPU Load (1/5/15m)', "{$system['cpu_load']['1min']} / {$system['cpu_load']['5min']} / {$system['cpu_load']['15min']}"];
            }

            $this->table(['Metric', 'Value'], $metricsData);
        }

        // Queue metrics (if full check)
        if (isset($result['checks']['queue_metrics']) && !empty($result['checks']['queue_metrics'])) {
            $queue = $result['checks']['queue_metrics'];

            if (isset($queue['pending_jobs']) || isset($queue['failed_jobs_total'])) {
                $this->newLine();
                $this->info('Queue Metrics:');

                $queueData = [];

                if (isset($queue['pending_jobs'])) {
                    $queueData[] = ['Pending Jobs', $queue['pending_jobs']];
                }
                if (isset($queue['failed_jobs_total'])) {
                    $queueData[] = ['Failed Jobs (Total)', $queue['failed_jobs_total']];
                }
                if (isset($queue['failed_jobs_24h'])) {
                    $queueData[] = ['Failed Jobs (24h)', $queue['failed_jobs_24h']];
                }

                $this->table(['Metric', 'Value'], $queueData);
            }
        }

        // Summary
        $this->newLine();
        $summary = $result['summary'] ?? [];
        $this->line(sprintf(
            'Summary: %d checks, %d failed, %d warnings',
            $summary['total_checks'] ?? count($result['checks']),
            $summary['failed_count'] ?? count($result['failed_checks'] ?? []),
            $summary['warning_count'] ?? count($result['warnings'] ?? [])
        ));
    }
}
