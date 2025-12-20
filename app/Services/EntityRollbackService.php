<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AuditLog;
use App\Models\EntityVersion;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Entity Rollback Service
 *
 * Handles rolling back entities to previous versions.
 */
class EntityRollbackService
{
    /**
     * Rollback entity to previous version
     */
    public function rollback(
        string $type,
        int $id,
        int $targetVersionNumber,
        User $user,
        string $reason
    ): bool {
        DB::beginTransaction();

        try {
            // Get target version
            $version = EntityVersion::where('versionable_type', $type)
                ->where('versionable_id', $id)
                ->where('version_number', $targetVersionNumber)
                ->firstOrFail();

            // Get current state
            $modelClass = $type;
            $model = $modelClass::findOrFail($id);
            $currentState = $model->toArray();

            // Check if user has permission to rollback
            if (!$this->canRollback($user, $model)) {
                throw new InvalidArgumentException('User does not have permission to rollback this entity');
            }

            // Restore from snapshot
            $model->fill($version->snapshot);
            $model->save();

            // Create audit log
            AuditLog::create([
                'user_id' => $user->id,
                'user_email' => $user->email,
                'user_name' => $user->name,
                'action' => 'rollback',
                'auditable_type' => $type,
                'auditable_id' => $id,
                'auditable_uuid' => $model->uuid ?? null,
                'old_values' => $currentState,
                'new_values' => $version->snapshot,
                'reason' => $reason,
                'metadata' => [
                    'rollback_to_version' => $targetVersionNumber,
                    'rollback_from_version' => $model->current_version ?? null,
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'session_id' => session()->getId(),
                'occurred_at' => now(),
            ]);

            // Create new version snapshot
            $currentVersion = EntityVersion::where('versionable_type', $type)
                ->where('versionable_id', $id)
                ->max('version_number') ?? 0;

            EntityVersion::create([
                'versionable_type' => $type,
                'versionable_id' => $id,
                'versionable_uuid' => $model->uuid ?? null,
                'version_number' => $currentVersion + 1,
                'version_hash' => hash('sha256', json_encode($version->snapshot)),
                'snapshot' => $version->snapshot,
                'created_by' => $user->id,
                'change_summary' => "Rolled back to version $targetVersionNumber",
                'change_type' => 'rollback',
                'parent_version_id' => $version->id,
            ]);

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Rollback to specific timestamp
     */
    public function rollbackToTimestamp(
        string $type,
        int $id,
        string $timestamp,
        User $user,
        string $reason
    ): bool {
        $pointInTime = app(PointInTimeService::class);
        $state = $pointInTime->getEntityAt($type, $id, carbon($timestamp));

        if (!$state) {
            throw new InvalidArgumentException('Entity did not exist at specified timestamp');
        }

        DB::beginTransaction();

        try {
            $modelClass = $type;
            $model = $modelClass::findOrFail($id);
            $currentState = $model->toArray();

            // Check permission
            if (!$this->canRollback($user, $model)) {
                throw new InvalidArgumentException('User does not have permission to rollback this entity');
            }

            // Restore state
            $model->fill($state);
            $model->save();

            // Create audit log
            AuditLog::create([
                'user_id' => $user->id,
                'user_email' => $user->email,
                'user_name' => $user->name,
                'action' => 'rollback',
                'auditable_type' => $type,
                'auditable_id' => $id,
                'old_values' => $currentState,
                'new_values' => $state,
                'reason' => $reason,
                'metadata' => [
                    'rollback_to_timestamp' => $timestamp,
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'occurred_at' => now(),
            ]);

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Check if user can rollback entity
     */
    private function canRollback(User $user, $model): bool
    {
        // Admins can rollback anything
        if ($user->hasRole('admin')) {
            return true;
        }

        // Editors can rollback
        if ($user->hasRole('editor')) {
            return true;
        }

        // Users can rollback their own content
        if (isset($model->user_id) && $model->user_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Get rollback preview (what will change)
     */
    public function getRollbackPreview(string $type, int $id, int $targetVersionNumber): array
    {
        $version = EntityVersion::where('versionable_type', $type)
            ->where('versionable_id', $id)
            ->where('version_number', $targetVersionNumber)
            ->firstOrFail();

        $modelClass = $type;
        $model = $modelClass::findOrFail($id);
        $currentState = $model->toArray();

        $diffService = app(VersionDiffService::class);

        return [
            'current_state' => $currentState,
            'target_state' => $version->snapshot,
            'changes' => $diffService->computeDiff($currentState, $version->snapshot),
            'summary' => $diffService->generateSummary($currentState, $version->snapshot),
            'target_version' => [
                'number' => $version->version_number,
                'created_at' => $version->created_at,
                'created_by' => $version->creator?->only(['id', 'name', 'email']),
            ],
        ];
    }
}
