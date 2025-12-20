<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AuditLog;
use App\Models\EntityVersion;
use Carbon\Carbon;

/**
 * Point In Time Service
 *
 * Reconstructs entity state at any point in time.
 */
class PointInTimeService
{
    /**
     * Retrieve entity state at specific timestamp
     */
    public function getEntityAt(string $type, int $id, Carbon $timestamp): ?array
    {
        // Strategy 1: Find exact version snapshot
        $version = EntityVersion::where('versionable_type', $type)
            ->where('versionable_id', $id)
            ->where('created_at', '<=', $timestamp)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($version) {
            return $version->snapshot;
        }

        // Strategy 2: Replay audit logs from creation
        return $this->replayAuditLogs($type, $id, $timestamp);
    }

    /**
     * Replay audit logs to reconstruct state
     */
    private function replayAuditLogs(string $type, int $id, Carbon $timestamp): ?array
    {
        // Get creation event
        $creation = AuditLog::where('auditable_type', $type)
            ->where('auditable_id', $id)
            ->where('action', 'create')
            ->where('created_at', '<=', $timestamp)
            ->first();

        if (!$creation) {
            return null; // Entity didn't exist at this time
        }

        // Start with initial state
        $state = $creation->new_values;

        // Apply all subsequent changes up to timestamp
        $changes = AuditLog::where('auditable_type', $type)
            ->where('auditable_id', $id)
            ->where('action', 'update')
            ->where('created_at', '>', $creation->created_at)
            ->where('created_at', '<=', $timestamp)
            ->orderBy('created_at', 'asc')
            ->get();

        foreach ($changes as $change) {
            if ($change->new_values) {
                $state = array_merge($state, $change->new_values);
            }
        }

        return $state;
    }

    /**
     * Get entity state at multiple timestamps
     */
    public function getEntityTimeline(string $type, int $id, array $timestamps): array
    {
        $timeline = [];

        foreach ($timestamps as $timestamp) {
            $carbon = $timestamp instanceof Carbon ? $timestamp : carbon($timestamp);
            $timeline[$carbon->toIso8601String()] = $this->getEntityAt($type, $id, $carbon);
        }

        return $timeline;
    }

    /**
     * Check if entity existed at timestamp
     */
    public function entityExistedAt(string $type, int $id, Carbon $timestamp): bool
    {
        $created = AuditLog::where('auditable_type', $type)
            ->where('auditable_id', $id)
            ->where('action', 'create')
            ->first();

        if (!$created || $created->created_at > $timestamp) {
            return false;
        }

        // Check if it was deleted before timestamp
        $deleted = AuditLog::where('auditable_type', $type)
            ->where('auditable_id', $id)
            ->where('action', 'delete')
            ->where('created_at', '<=', $timestamp)
            ->exists();

        return !$deleted;
    }

    /**
     * Get all changes to entity within date range
     */
    public function getChangesInRange(string $type, int $id, Carbon $from, Carbon $to): array
    {
        return AuditLog::where('auditable_type', $type)
            ->where('auditable_id', $id)
            ->whereBetween('created_at', [$from, $to])
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($log) {
                return [
                    'timestamp' => $log->created_at,
                    'action' => $log->action,
                    'user' => $log->user_email,
                    'changes' => $log->changed_fields,
                    'old_values' => $log->old_values,
                    'new_values' => $log->new_values,
                ];
            })
            ->toArray();
    }
}
