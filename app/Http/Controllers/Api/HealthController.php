<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HealthCheckLog;
use App\Services\HealthCheckService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

/**
 * Health Controller
 *
 * Provides health check endpoints for monitoring system status,
 * Kubernetes probes, and Prometheus metrics export.
 *
 * Public endpoints:
 * - GET /health - Quick health check
 * - GET /health/live - Kubernetes liveness probe
 * - GET /health/ready - Kubernetes readiness probe
 *
 * Authenticated endpoints:
 * - GET /api/health/full - Detailed health check
 * - GET /api/health/{component} - Component-specific check
 * - GET /api/health/metrics - Prometheus metrics
 * - GET /api/health/history - Check history
 */
class HealthController extends Controller
{
    public function __construct(
        private readonly HealthCheckService $healthService
    ) {
    }

    /**
     * Quick health check (public)
     *
     * Returns a brief health status suitable for load balancer checks.
     *
     * @return JsonResponse
     */
    public function health(): JsonResponse
    {
        $result = $this->healthService->runQuickCheck(useCache: true);

        $statusCode = match ($result['status']) {
            HealthCheckLog::STATUS_HEALTHY => 200,
            HealthCheckLog::STATUS_DEGRADED => 200,
            HealthCheckLog::STATUS_UNHEALTHY, HealthCheckLog::STATUS_ERROR => 503,
            default => 500,
        };

        return response()->json([
            'status' => $result['status'],
            'timestamp' => $result['timestamp'],
            'duration_ms' => $result['duration_ms'],
            'checks' => $result['checks'],
            'service' => 'OsintWeb API',
            'version' => config('app.version', '1.0.0'),
        ], $statusCode);
    }

    /**
     * Full health check (authenticated)
     *
     * Returns comprehensive health information including all components,
     * system metrics, and detailed diagnostics.
     *
     * @return JsonResponse
     */
    public function healthFull(): JsonResponse
    {
        $result = $this->healthService->runFullCheck(log: true);

        $statusCode = match ($result['status']) {
            HealthCheckLog::STATUS_HEALTHY => 200,
            HealthCheckLog::STATUS_DEGRADED => 200,
            HealthCheckLog::STATUS_UNHEALTHY, HealthCheckLog::STATUS_ERROR => 503,
            default => 500,
        };

        return response()->json([
            'data' => $result,
        ], $statusCode);
    }

    /**
     * Check a specific component
     *
     * @param Request $request
     * @param string $component
     * @return JsonResponse
     */
    public function healthComponent(Request $request, string $component): JsonResponse
    {
        $validComponents = [
            'database',
            'cache',
            'search',
            'queue',
            'storage',
            'external',
            'migrations',
            'scheduler',
        ];

        if (!in_array($component, $validComponents)) {
            return $this->error(
                "Invalid component: {$component}. Valid components: " . implode(', ', $validComponents),
                400
            );
        }

        $result = $this->healthService->checkComponent($component);

        $statusCode = $result['healthy'] ? 200 : 503;

        return response()->json([
            'data' => $result,
        ], $statusCode);
    }

    /**
     * Get Prometheus-style metrics
     *
     * Returns metrics in Prometheus text format for scraping.
     *
     * @return Response
     */
    public function metrics(): Response
    {
        $metrics = $this->healthService->generatePrometheusMetrics();

        return response($metrics, 200, [
            'Content-Type' => 'text/plain; version=0.0.4; charset=utf-8',
        ]);
    }

    /**
     * Get health check history
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function history(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['nullable', 'string', Rule::in(HealthCheckLog::getCheckTypes())],
            'status' => ['nullable', 'string', Rule::in(HealthCheckLog::getStatuses())],
            'hours' => ['nullable', 'integer', 'min:1', 'max:168'], // Max 1 week
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = HealthCheckLog::query()
            ->orderBy('created_at', 'desc');

        if (isset($validated['type'])) {
            $query->ofType($validated['type']);
        }

        if (isset($validated['status'])) {
            $query->withStatus($validated['status']);
        }

        $hours = $validated['hours'] ?? 24;
        $query->recent($hours);

        $perPage = $validated['per_page'] ?? $this->perPage;

        return $this->paginated($query->paginate($perPage));
    }

    /**
     * Get health statistics
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function statistics(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'hours' => ['nullable', 'integer', 'min:1', 'max:168'],
        ]);

        $hours = $validated['hours'] ?? 24;

        $statistics = HealthCheckLog::getStatistics($hours);

        return $this->success([
            'period_hours' => $hours,
            'statistics' => $statistics,
        ]);
    }

    /**
     * Kubernetes liveness probe
     *
     * Returns 200 if the application is alive and able to serve requests.
     * This probe should return quickly and only check critical components.
     *
     * @return Response
     */
    public function liveness(): Response
    {
        $format = config('health.kubernetes.response_format', 'simple');

        // Basic check: can we execute PHP?
        // Optionally check database connection
        $checks = config('health.kubernetes.liveness_checks', ['database']);

        $healthy = true;
        $errors = [];

        foreach ($checks as $component) {
            $result = $this->healthService->checkComponent($component);
            if (!$result['healthy']) {
                $healthy = false;
                $errors[] = $component;
            }
        }

        if ($healthy) {
            if ($format === 'json') {
                return response()->json(['status' => 'OK'], 200);
            }
            return response('OK', 200, ['Content-Type' => 'text/plain']);
        }

        if ($format === 'json') {
            return response()->json([
                'status' => 'FAIL',
                'failed_components' => $errors,
            ], 503);
        }

        return response('FAIL: ' . implode(', ', $errors), 503, ['Content-Type' => 'text/plain']);
    }

    /**
     * Kubernetes readiness probe
     *
     * Returns 200 if the application is ready to receive traffic.
     * Checks more components than the liveness probe.
     *
     * @return Response
     */
    public function readiness(): Response
    {
        $format = config('health.kubernetes.response_format', 'simple');
        $checks = config('health.kubernetes.readiness_checks', ['database', 'cache', 'storage']);

        $healthy = true;
        $errors = [];

        foreach ($checks as $component) {
            $result = $this->healthService->checkComponent($component);
            if (!$result['healthy']) {
                $healthy = false;
                $errors[] = $component;
            }
        }

        if ($healthy) {
            if ($format === 'json') {
                return response()->json(['status' => 'OK'], 200);
            }
            return response('OK', 200, ['Content-Type' => 'text/plain']);
        }

        if ($format === 'json') {
            return response()->json([
                'status' => 'FAIL',
                'failed_components' => $errors,
            ], 503);
        }

        return response('FAIL: ' . implode(', ', $errors), 503, ['Content-Type' => 'text/plain']);
    }

    /**
     * Get system metrics
     *
     * @return JsonResponse
     */
    public function systemMetrics(): JsonResponse
    {
        $system = $this->healthService->getSystemMetrics();
        $database = $this->healthService->getDatabaseMetrics();
        $queue = $this->healthService->getQueueMetrics();

        return $this->success([
            'system' => $system,
            'database' => $database,
            'queue' => $queue,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Get available check types
     *
     * @return JsonResponse
     */
    public function types(): JsonResponse
    {
        return $this->success([
            'check_types' => HealthCheckLog::getCheckTypes(),
            'statuses' => HealthCheckLog::getStatuses(),
            'components' => [
                'database' => 'Database connectivity and performance',
                'cache' => 'Cache read/write operations',
                'queue' => 'Queue connection and job status',
                'storage' => 'Disk space and write permissions',
                'search' => 'Meilisearch connectivity',
                'migrations' => 'Pending database migrations',
                'scheduler' => 'Task scheduler status',
                'external' => 'External service availability',
            ],
        ]);
    }

    /**
     * Clean up old health check logs
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function cleanup(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'days' => ['nullable', 'integer', 'min:1', 'max:365'],
        ]);

        $days = $validated['days'] ?? config('health.logging.retention_days', 30);

        if ($days === 0) {
            return $this->error('Log cleanup is disabled (retention_days = 0)', 400);
        }

        $cutoff = now()->subDays($days);
        $deleted = HealthCheckLog::where('created_at', '<', $cutoff)->delete();

        return $this->success([
            'message' => "Deleted {$deleted} health check logs older than {$days} days",
            'deleted_count' => $deleted,
            'cutoff_date' => $cutoff->toIso8601String(),
        ]);
    }
}
