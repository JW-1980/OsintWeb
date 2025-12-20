<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AuditLog;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Audit Chain Service
 *
 * Manages cryptographic chain verification for audit logs.
 */
class AuditChainService
{
    /**
     * Calculate hash for audit log entry
     */
    public function calculateHash(AuditLog $log): string
    {
        $data = [
            'id' => $log->id,
            'user_id' => $log->user_id,
            'action' => $log->action,
            'auditable_type' => $log->auditable_type,
            'auditable_id' => $log->auditable_id,
            'old_values' => $log->old_values,
            'new_values' => $log->new_values,
            'occurred_at' => $log->occurred_at->toIso8601String(),
            'previous_hash' => $log->previous_hash,
        ];

        return hash('sha256', json_encode($data, JSON_UNESCAPED_UNICODE));
    }

    /**
     * Verify chain integrity for a date range
     */
    public function verifyChain(Carbon $from, Carbon $to): array
    {
        $logs = AuditLog::whereBetween('created_at', [$from, $to])
            ->orderBy('id', 'asc')
            ->get();

        $errors = [];
        $previousHash = null;

        foreach ($logs as $log) {
            // Verify hash matches
            $expectedHash = $this->calculateHash($log);
            if ($log->hash !== $expectedHash) {
                $errors[] = [
                    'log_id' => $log->id,
                    'error' => 'Hash mismatch',
                    'expected' => $expectedHash,
                    'actual' => $log->hash,
                ];
            }

            // Verify chain link
            if ($previousHash !== null && $log->previous_hash !== $previousHash) {
                $errors[] = [
                    'log_id' => $log->id,
                    'error' => 'Chain break',
                    'expected_previous' => $previousHash,
                    'actual_previous' => $log->previous_hash,
                ];
            }

            $previousHash = $log->hash;
        }

        return [
            'verified' => empty($errors),
            'checked_count' => $logs->count(),
            'errors' => $errors,
            'date_range' => [
                'from' => $from->toIso8601String(),
                'to' => $to->toIso8601String(),
            ],
        ];
    }

    /**
     * Verify entire chain from beginning
     */
    public function verifyEntireChain(): array
    {
        $firstLog = AuditLog::orderBy('id', 'asc')->first();
        $lastLog = AuditLog::orderBy('id', 'desc')->first();

        if (!$firstLog || !$lastLog) {
            return [
                'verified' => true,
                'checked_count' => 0,
                'errors' => [],
            ];
        }

        return $this->verifyChain(
            $firstLog->created_at,
            $lastLog->created_at
        );
    }

    /**
     * Mark broken chains in database
     */
    public function markBrokenChains(Carbon $from, Carbon $to): int
    {
        $result = $this->verifyChain($from, $to);

        if ($result['verified']) {
            return 0;
        }

        $brokenLogIds = collect($result['errors'])->pluck('log_id')->unique();

        return DB::table('audit_logs')
            ->whereIn('id', $brokenLogIds)
            ->update(['chain_verified' => false]);
    }

    /**
     * Get chain statistics
     */
    public function getChainStatistics(): array
    {
        return [
            'total_logs' => AuditLog::count(),
            'verified_logs' => AuditLog::where('chain_verified', true)->count(),
            'broken_logs' => AuditLog::where('chain_verified', false)->count(),
            'oldest_log' => AuditLog::orderBy('id', 'asc')->first()?->created_at,
            'newest_log' => AuditLog::orderBy('id', 'desc')->first()?->created_at,
        ];
    }
}
