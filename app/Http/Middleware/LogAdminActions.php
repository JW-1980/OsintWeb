<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to log all admin panel actions.
 *
 * Creates immutable audit trail entries for admin operations.
 */
class LogAdminActions
{
    /**
     * Actions that should be logged.
     */
    private array $loggedMethods = ['POST', 'PUT', 'PATCH', 'DELETE'];

    /**
     * Routes that should not be logged (to prevent recursive logging).
     */
    private array $excludedRoutes = [
        'api/admin/audit-logs',
        'api/admin/audit-logs/export',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only log mutating actions
        if (!in_array($request->method(), $this->loggedMethods)) {
            return $response;
        }

        // Skip excluded routes
        $path = $request->path();
        foreach ($this->excludedRoutes as $excluded) {
            if (str_starts_with($path, $excluded)) {
                return $response;
            }
        }

        // Only log successful operations
        $statusCode = $response->getStatusCode();
        if ($statusCode >= 400) {
            return $response;
        }

        $this->logAction($request, $response);

        return $response;
    }

    /**
     * Log the admin action.
     */
    private function logAction(Request $request, Response $response): void
    {
        $user = $request->user();

        if (!$user) {
            return;
        }

        $action = $this->determineAction($request);
        $entityInfo = $this->extractEntityInfo($request);

        // Get the previous hash for chain integrity
        $previousLog = AuditLog::orderBy('id', 'desc')->first();
        $previousHash = $previousLog?->hash;

        // Prepare audit data
        $auditData = [
            'uuid' => (string) Str::uuid(),
            'user_id' => $user->id,
            'user_email' => $user->email,
            'user_name' => $user->name,
            'action' => $action,
            'auditable_type' => $entityInfo['type'],
            'auditable_id' => $entityInfo['id'],
            'auditable_uuid' => $entityInfo['uuid'],
            'new_values' => $this->sanitizeRequestData($request->all()),
            'changed_fields' => array_keys($request->except(['_token', '_method'])),
            'ip_address' => $request->ip(),
            'user_agent' => substr($request->userAgent() ?? '', 0, 500),
            'url' => $request->fullUrl(),
            'http_method' => $request->method(),
            'api_endpoint' => $request->path(),
            'session_id' => session()->getId(),
            'request_id' => $request->header('X-Request-ID'),
            'occurred_at' => now(),
            'previous_hash' => $previousHash,
            'chain_verified' => true,
            'tags' => ['admin_panel', $action],
        ];

        // Calculate hash for chain integrity
        $hashData = [
            'user_id' => $auditData['user_id'],
            'action' => $auditData['action'],
            'auditable_type' => $auditData['auditable_type'],
            'auditable_id' => $auditData['auditable_id'],
            'new_values' => $auditData['new_values'],
            'occurred_at' => $auditData['occurred_at']->toIso8601String(),
            'previous_hash' => $previousHash,
        ];
        $auditData['hash'] = hash('sha256', json_encode($hashData, JSON_UNESCAPED_UNICODE));

        try {
            AuditLog::create($auditData);
        } catch (\Exception $e) {
            // Log to system log but don't fail the request
            \Illuminate\Support\Facades\Log::error('Failed to create audit log', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'action' => $action,
            ]);
        }
    }

    /**
     * Determine the action type from the request.
     */
    private function determineAction(Request $request): string
    {
        $method = $request->method();
        $path = $request->path();

        // Check for specific action patterns in the URL
        if (str_contains($path, '/restore')) {
            return 'restore';
        }
        if (str_contains($path, '/verify')) {
            return 'verify';
        }
        if (str_contains($path, '/approve')) {
            return 'approve';
        }
        if (str_contains($path, '/reject')) {
            return 'reject';
        }
        if (str_contains($path, '/publish')) {
            return 'publish';
        }
        if (str_contains($path, '/toggle')) {
            return 'toggle';
        }
        if (str_contains($path, '/assign')) {
            return 'assign';
        }
        if (str_contains($path, '/revoke')) {
            return 'revoke';
        }

        return match ($method) {
            'POST' => 'create',
            'PUT', 'PATCH' => 'update',
            'DELETE' => 'delete',
            default => strtolower($method),
        };
    }

    /**
     * Extract entity information from the request.
     */
    private function extractEntityInfo(Request $request): array
    {
        $path = $request->path();
        $segments = explode('/', $path);

        // Default values
        $type = 'App\Models\Unknown';
        $id = 0;
        $uuid = null;

        // Parse common patterns like: api/admin/{entity}/{id}
        // or: api/{entity}/{uuid}
        foreach ($segments as $index => $segment) {
            // Skip 'api' and 'admin'
            if (in_array($segment, ['api', 'admin'])) {
                continue;
            }

            // Check if this might be an entity name
            $possibleEntity = $this->singularize($segment);
            $modelClass = 'App\\Models\\' . Str::studly($possibleEntity);

            if (class_exists($modelClass)) {
                $type = $modelClass;

                // Check next segment for ID or UUID
                $nextSegment = $segments[$index + 1] ?? null;
                if ($nextSegment && !$this->isActionWord($nextSegment)) {
                    if (Str::isUuid($nextSegment)) {
                        $uuid = $nextSegment;
                        // Try to find the actual ID from the model
                        try {
                            $model = $modelClass::where('uuid', $nextSegment)->first();
                            $id = $model?->id ?? 0;
                        } catch (\Exception $e) {
                            // Model might not have uuid column
                        }
                    } elseif (is_numeric($nextSegment)) {
                        $id = (int) $nextSegment;
                    }
                }
                break;
            }
        }

        return [
            'type' => $type,
            'id' => $id,
            'uuid' => $uuid,
        ];
    }

    /**
     * Check if a word is an action word (not an ID).
     */
    private function isActionWord(string $word): bool
    {
        $actionWords = [
            'restore', 'verify', 'approve', 'reject', 'publish',
            'toggle', 'assign', 'revoke', 'export', 'import',
            'stats', 'statistics', 'filter-options', 'verify-chain',
        ];

        return in_array($word, $actionWords);
    }

    /**
     * Convert plural to singular.
     */
    private function singularize(string $word): string
    {
        $irregulars = [
            'achievements' => 'achievement',
            'users' => 'user',
            'events' => 'event',
            'actors' => 'actor',
            'conflicts' => 'conflict',
            'equipment' => 'military_equipment',
            'zones' => 'control_zone',
            'articles' => 'article',
            'comments' => 'comment',
            'sources' => 'source',
            'roles' => 'role',
            'permissions' => 'permission',
            'settings' => 'setting',
            'audit-logs' => 'audit_log',
        ];

        if (isset($irregulars[$word])) {
            return $irregulars[$word];
        }

        // Simple pluralization rules
        if (str_ends_with($word, 'ies')) {
            return substr($word, 0, -3) . 'y';
        }
        if (str_ends_with($word, 's') && !str_ends_with($word, 'ss')) {
            return substr($word, 0, -1);
        }

        return $word;
    }

    /**
     * Sanitize request data to remove sensitive information.
     */
    private function sanitizeRequestData(array $data): array
    {
        $sensitiveFields = [
            'password',
            'password_confirmation',
            'current_password',
            'new_password',
            'api_token',
            'secret',
            'token',
            '_token',
            'credit_card',
            'cvv',
        ];

        foreach ($sensitiveFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = '[REDACTED]';
            }
        }

        // Also handle nested arrays
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->sanitizeRequestData($value);
            }
        }

        return $data;
    }
}
