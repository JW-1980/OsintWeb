<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\AuditChainService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuditLogController extends Controller
{
    public function __construct(
        private readonly AuditChainService $auditChainService
    ) {}

    /**
     * List audit logs with filtering and pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isAdmin() && !$user->hasPermission('audit.view')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $query = AuditLog::with(['user:id,name,email'])
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('user_name', 'like', "%{$search}%")
                    ->orWhere('user_email', 'like', "%{$search}%")
                    ->orWhere('reason', 'like', "%{$search}%")
                    ->orWhere('auditable_type', 'like', "%{$search}%");
            });
        }

        if ($request->filled('action')) {
            $query->where('action', $request->input('action'));
        }

        if ($request->filled('entity')) {
            $entityType = $request->input('entity');
            // Support both short names and full class names
            if (!str_contains($entityType, '\\')) {
                $entityType = "App\\Models\\{$entityType}";
            }
            $query->where('auditable_type', $entityType);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->input('date'));
        }

        if ($request->boolean('critical_only')) {
            $query->whereIn('action', ['delete', 'failed_login', 'password_change', 'role_change', 'permission_change']);
        }

        if ($request->boolean('chain_broken')) {
            $query->where('chain_verified', false);
        }

        $perPage = min($request->integer('per_page', 25), 100);
        $logs = $query->paginate($perPage);

        // Transform the data
        $logs->getCollection()->transform(function ($log) {
            return $this->transformLog($log);
        });

        return response()->json($logs);
    }

    /**
     * Get a single audit log entry.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        if (!$user->isAdmin() && !$user->hasPermission('audit.view')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $log = AuditLog::with(['user:id,name,email', 'impersonator:id,name,email'])
            ->findOrFail($id);

        return response()->json([
            'data' => $this->transformLog($log, true),
        ]);
    }

    /**
     * Get audit log statistics.
     */
    public function stats(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isAdmin() && !$user->hasPermission('audit.view')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $today = now()->startOfDay();
        $thisWeek = now()->startOfWeek();
        $thisMonth = now()->startOfMonth();

        $chainStats = $this->auditChainService->getChainStatistics();

        return response()->json([
            'data' => [
                'total_logs' => AuditLog::count(),
                'today_logs' => AuditLog::where('created_at', '>=', $today)->count(),
                'week_logs' => AuditLog::where('created_at', '>=', $thisWeek)->count(),
                'month_logs' => AuditLog::where('created_at', '>=', $thisMonth)->count(),
                'unique_users_today' => AuditLog::where('created_at', '>=', $today)
                    ->distinct('user_id')
                    ->count('user_id'),
                'critical_events' => AuditLog::whereIn('action', ['delete', 'failed_login', 'password_change'])
                    ->where('created_at', '>=', $thisWeek)
                    ->count(),
                'actions_breakdown' => AuditLog::selectRaw('action, COUNT(*) as count')
                    ->where('created_at', '>=', $thisMonth)
                    ->groupBy('action')
                    ->pluck('count', 'action'),
                'entities_breakdown' => AuditLog::selectRaw('auditable_type, COUNT(*) as count')
                    ->where('created_at', '>=', $thisMonth)
                    ->groupBy('auditable_type')
                    ->get()
                    ->mapWithKeys(fn ($item) => [
                        class_basename($item->auditable_type) => $item->count
                    ]),
                'chain_integrity' => [
                    'total' => $chainStats['total_logs'],
                    'verified' => $chainStats['verified_logs'],
                    'broken' => $chainStats['broken_logs'],
                ],
            ],
        ]);
    }

    /**
     * Verify audit chain integrity.
     */
    public function verifyChain(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isAdmin() && !$user->hasPermission('audit.view')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $from = $request->filled('from')
            ? \Carbon\Carbon::parse($request->input('from'))
            : now()->subDays(30);

        $to = $request->filled('to')
            ? \Carbon\Carbon::parse($request->input('to'))
            : now();

        $result = $this->auditChainService->verifyChain($from, $to);

        return response()->json([
            'data' => $result,
        ]);
    }

    /**
     * Export audit logs as CSV.
     */
    public function export(Request $request): StreamedResponse
    {
        $user = $request->user();

        if (!$user->isAdmin() && !$user->hasPermission('audit.export')) {
            abort(403, 'Unauthorized');
        }

        $query = AuditLog::with(['user:id,name,email'])
            ->orderBy('created_at', 'desc');

        // Apply same filters as index
        if ($request->filled('action')) {
            $query->where('action', $request->input('action'));
        }

        if ($request->filled('entity')) {
            $entityType = $request->input('entity');
            if (!str_contains($entityType, '\\')) {
                $entityType = "App\\Models\\{$entityType}";
            }
            $query->where('auditable_type', $entityType);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $logs = $query->limit(10000)->get();

        // Log the export action
        AuditLog::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'user_id' => $user->id,
            'user_email' => $user->email,
            'user_name' => $user->name,
            'action' => 'export',
            'auditable_type' => AuditLog::class,
            'auditable_id' => 0,
            'new_values' => ['count' => $logs->count(), 'filters' => $request->only(['action', 'entity', 'date_from', 'date_to'])],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'occurred_at' => now(),
            'hash' => hash('sha256', json_encode(['export', now()->toIso8601String()])),
        ]);

        $filename = 'audit-logs-' . now()->format('Y-m-d-His') . '.csv';

        return Response::streamDownload(function () use ($logs) {
            $handle = fopen('php://output', 'w');

            // Header row
            fputcsv($handle, [
                'ID',
                'Date',
                'User',
                'Email',
                'Action',
                'Entity Type',
                'Entity ID',
                'IP Address',
                'User Agent',
                'Changes',
                'Chain Verified',
            ]);

            foreach ($logs as $log) {
                fputcsv($handle, [
                    $log->id,
                    $log->created_at->toIso8601String(),
                    $log->user_name ?? 'System',
                    $log->user_email ?? '',
                    $log->action,
                    class_basename($log->auditable_type),
                    $log->auditable_id,
                    $log->ip_address,
                    substr($log->user_agent ?? '', 0, 100),
                    json_encode($log->changed_fields ?? []),
                    $log->chain_verified ? 'Yes' : 'No',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * Get available filter options.
     */
    public function filterOptions(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isAdmin() && !$user->hasPermission('audit.view')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'data' => [
                'actions' => [
                    'create' => 'Created',
                    'update' => 'Updated',
                    'delete' => 'Deleted',
                    'restore' => 'Restored',
                    'login' => 'Login',
                    'logout' => 'Logout',
                    'failed_login' => 'Failed Login',
                    'password_change' => 'Password Change',
                    'role_change' => 'Role Change',
                    'permission_change' => 'Permission Change',
                    'export' => 'Export',
                    'import' => 'Import',
                    'verify' => 'Verified',
                    'dispute' => 'Disputed',
                ],
                'entities' => [
                    'User' => 'Users',
                    'Event' => 'Events',
                    'Actor' => 'Actors',
                    'Conflict' => 'Conflicts',
                    'MilitaryEquipment' => 'Equipment',
                    'ControlZone' => 'Zones',
                    'Article' => 'Articles',
                    'Comment' => 'Comments',
                    'Source' => 'Sources',
                    'Role' => 'Roles',
                    'Permission' => 'Permissions',
                    'Setting' => 'Settings',
                ],
                'users' => User::select('id', 'name', 'email')
                    ->whereIn('id', AuditLog::distinct('user_id')->pluck('user_id'))
                    ->orderBy('name')
                    ->get(),
            ],
        ]);
    }

    /**
     * Clear old audit logs (with safeguards).
     */
    public function clearOld(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isAdmin()) {
            return response()->json(['message' => 'Only administrators can clear audit logs'], 403);
        }

        $request->validate([
            'days' => 'required|integer|min:90|max:365',
            'confirm' => 'required|boolean|accepted',
        ]);

        $days = $request->integer('days');
        $cutoffDate = now()->subDays($days);

        $count = AuditLog::where('created_at', '<', $cutoffDate)->count();

        // Log the clear action before deleting
        AuditLog::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'user_id' => $user->id,
            'user_email' => $user->email,
            'user_name' => $user->name,
            'action' => 'delete',
            'auditable_type' => AuditLog::class,
            'auditable_id' => 0,
            'new_values' => [
                'action' => 'bulk_clear',
                'days' => $days,
                'count' => $count,
                'cutoff_date' => $cutoffDate->toIso8601String(),
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'occurred_at' => now(),
            'hash' => hash('sha256', json_encode(['clear', $days, now()->toIso8601String()])),
        ]);

        AuditLog::where('created_at', '<', $cutoffDate)->delete();

        return response()->json([
            'message' => "Cleared {$count} audit logs older than {$days} days",
            'deleted_count' => $count,
        ]);
    }

    /**
     * Transform audit log for API response.
     */
    private function transformLog(AuditLog $log, bool $detailed = false): array
    {
        $data = [
            'id' => $log->id,
            'uuid' => $log->uuid,
            'user_id' => $log->user_id,
            'user_name' => $log->user_name ?? $log->user?->name ?? 'System',
            'user_email' => $log->user_email,
            'action' => $log->action,
            'auditable_type' => class_basename($log->auditable_type),
            'auditable_type_full' => $log->auditable_type,
            'auditable_id' => $log->auditable_id,
            'auditable_uuid' => $log->auditable_uuid,
            'description' => $log->description,
            'ip_address' => $log->ip_address,
            'ip_country' => $log->ip_country,
            'user_agent_short' => $this->shortenUserAgent($log->user_agent),
            'created_at' => $log->created_at->toIso8601String(),
            'chain_verified' => $log->chain_verified,
        ];

        // Include changes if present
        if ($log->old_values || $log->new_values) {
            $data['changes'] = $this->formatChanges($log->old_values, $log->new_values, $log->changed_fields);
        }

        if ($detailed) {
            $data['user_agent'] = $log->user_agent;
            $data['old_values'] = $log->old_values;
            $data['new_values'] = $log->new_values;
            $data['changed_fields'] = $log->changed_fields;
            $data['reason'] = $log->reason;
            $data['tags'] = $log->tags;
            $data['metadata'] = $log->metadata;
            $data['url'] = $log->url;
            $data['http_method'] = $log->http_method;
            $data['api_endpoint'] = $log->api_endpoint;
            $data['session_id'] = $log->session_id;
            $data['request_id'] = $log->request_id;
            $data['hash'] = $log->hash;
            $data['previous_hash'] = $log->previous_hash;
            $data['impersonator'] = $log->impersonator ? [
                'id' => $log->impersonator->id,
                'name' => $log->impersonator->name,
            ] : null;
        }

        return $data;
    }

    /**
     * Shorten user agent string for display.
     */
    private function shortenUserAgent(?string $userAgent): ?string
    {
        if (!$userAgent) {
            return null;
        }

        // Extract browser and OS
        $browser = 'Unknown';
        $os = 'Unknown';

        if (preg_match('/Chrome\/[\d.]+/', $userAgent)) {
            $browser = 'Chrome';
        } elseif (preg_match('/Firefox\/[\d.]+/', $userAgent)) {
            $browser = 'Firefox';
        } elseif (preg_match('/Safari\/[\d.]+/', $userAgent) && !preg_match('/Chrome/', $userAgent)) {
            $browser = 'Safari';
        } elseif (preg_match('/Edge\/[\d.]+/', $userAgent)) {
            $browser = 'Edge';
        }

        if (preg_match('/Windows/', $userAgent)) {
            $os = 'Win';
        } elseif (preg_match('/Macintosh/', $userAgent)) {
            $os = 'Mac';
        } elseif (preg_match('/Linux/', $userAgent)) {
            $os = 'Linux';
        } elseif (preg_match('/iPhone|iPad/', $userAgent)) {
            $os = 'iOS';
        } elseif (preg_match('/Android/', $userAgent)) {
            $os = 'Android';
        }

        return "{$browser}/{$os}";
    }

    /**
     * Format changes for display.
     */
    private function formatChanges(?array $oldValues, ?array $newValues, ?array $changedFields): array
    {
        $changes = [];

        $fields = $changedFields ?? array_unique(array_merge(
            array_keys($oldValues ?? []),
            array_keys($newValues ?? [])
        ));

        foreach ($fields as $field) {
            // Skip sensitive or system fields
            if (in_array($field, ['password', 'remember_token', 'api_token', 'updated_at'])) {
                continue;
            }

            $old = $oldValues[$field] ?? null;
            $new = $newValues[$field] ?? null;

            if ($old !== $new) {
                $changes[$field] = [
                    'old' => is_array($old) ? json_encode($old) : $old,
                    'new' => is_array($new) ? json_encode($new) : $new,
                ];
            }
        }

        return $changes;
    }
}
