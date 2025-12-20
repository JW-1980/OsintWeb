<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\AuditLog;
use App\Models\EntityVersion;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Auditable Trait
 *
 * Automatically tracks changes to models that use this trait.
 */
trait Auditable
{
    /**
     * Boot the auditable trait for a model.
     */
    protected static function bootAuditable(): void
    {
        static::created(function ($model) {
            static::auditAction($model, 'create', null, $model->getAuditableAttributes());
            static::createVersionSnapshot($model, 'create');
        });

        static::updated(function ($model) {
            $old = $model->getOriginal();
            $new = $model->getAttributes();

            $changes = static::getChangedAuditableAttributes($old, $new);

            if (!empty($changes['old']) || !empty($changes['new'])) {
                static::auditAction(
                    $model,
                    'update',
                    $changes['old'],
                    $changes['new']
                );
                static::createVersionSnapshot($model, 'update');
            }
        });

        static::deleted(function ($model) {
            static::auditAction($model, 'delete', $model->getAuditableAttributes(), null);
            static::createVersionSnapshot($model, 'delete');
        });

        if (method_exists(static::class, 'restored')) {
            static::restored(function ($model) {
                static::auditAction($model, 'restore', null, $model->getAuditableAttributes());
                static::createVersionSnapshot($model, 'restore');
            });
        }
    }

    /**
     * Create an audit log entry
     */
    protected static function auditAction($model, string $action, ?array $oldValues, ?array $newValues): void
    {
        $user = Auth::user();
        $request = request();

        // Get previous hash for chain
        $previousLog = AuditLog::orderBy('id', 'desc')->first();

        $changedFields = $oldValues && $newValues
            ? array_keys(array_diff_assoc($newValues, $oldValues))
            : null;

        $data = [
            'uuid' => (string) Str::uuid(),
            'user_id' => $user?->id,
            'user_email' => $user?->email ?? 'system@osint.local',
            'user_name' => $user?->name,
            'action' => $action,
            'auditable_type' => get_class($model),
            'auditable_id' => $model->id,
            'auditable_uuid' => $model->uuid ?? null,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'changed_fields' => $changedFields,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'session_id' => session()->getId(),
            'request_id' => $request->headers->get('X-Request-ID') ?? (string) Str::uuid(),
            'url' => $request->fullUrl(),
            'http_method' => $request->method(),
            'previous_hash' => $previousLog?->hash,
            'metadata' => static::getAuditMetadata($model, $action),
            'occurred_at' => now(),
        ];

        // Create audit log
        $auditLog = AuditLog::create($data);

        // Calculate and update hash
        $hash = static::calculateAuditHash($auditLog);
        DB::table('audit_logs')
            ->where('id', $auditLog->id)
            ->update(['hash' => $hash]);
    }

    /**
     * Create a version snapshot
     */
    protected static function createVersionSnapshot($model, string $changeType): void
    {
        // Get current version number
        $currentVersion = EntityVersion::where('versionable_type', get_class($model))
            ->where('versionable_id', $model->id)
            ->max('version_number') ?? 0;

        $snapshot = $model->toArray();

        EntityVersion::create([
            'uuid' => (string) Str::uuid(),
            'versionable_type' => get_class($model),
            'versionable_id' => $model->id,
            'versionable_uuid' => $model->uuid ?? null,
            'version_number' => $currentVersion + 1,
            'version_hash' => hash('sha256', json_encode($snapshot)),
            'snapshot' => $snapshot,
            'created_by' => Auth::id(),
            'change_type' => $changeType,
            'change_summary' => static::generateChangeSummary($model, $changeType),
        ]);
    }

    /**
     * Get attributes that should be audited
     */
    protected function getAuditableAttributes(): array
    {
        $attributes = $this->getAttributes();

        // Exclude non-auditable fields
        $excluded = $this->auditExclude ?? ['password', 'remember_token'];

        foreach ($excluded as $field) {
            unset($attributes[$field]);
        }

        return $attributes;
    }

    /**
     * Get changed auditable attributes
     */
    protected static function getChangedAuditableAttributes(array $old, array $new): array
    {
        $excluded = (new static)->auditExclude ?? ['password', 'remember_token', 'updated_at'];

        $oldFiltered = array_diff_key($old, array_flip($excluded));
        $newFiltered = array_diff_key($new, array_flip($excluded));

        $changed = array_diff_assoc($newFiltered, $oldFiltered);

        if (empty($changed)) {
            return ['old' => [], 'new' => []];
        }

        return [
            'old' => array_intersect_key($oldFiltered, $changed),
            'new' => $changed,
        ];
    }

    /**
     * Calculate SHA-256 hash for audit log entry
     */
    protected static function calculateAuditHash(AuditLog $log): string
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
     * Get audit metadata
     */
    protected static function getAuditMetadata($model, string $action): array
    {
        return [
            'model_class' => get_class($model),
            'action' => $action,
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * Generate change summary
     */
    protected static function generateChangeSummary($model, string $changeType): string
    {
        $className = class_basename($model);

        return match($changeType) {
            'create' => "Created {$className}",
            'update' => "Updated {$className}",
            'delete' => "Deleted {$className}",
            'restore' => "Restored {$className}",
            default => "{$changeType} {$className}",
        };
    }

    /**
     * Get audit logs for this model
     */
    public function auditLogs()
    {
        return $this->morphMany(AuditLog::class, 'auditable')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Get version history for this model
     */
    public function versions()
    {
        return $this->morphMany(EntityVersion::class, 'versionable')
            ->orderBy('version_number', 'desc');
    }

    /**
     * Get current version number
     */
    public function getCurrentVersionAttribute(): int
    {
        return $this->versions()->max('version_number') ?? 0;
    }

    /**
     * Get latest version
     */
    public function getLatestVersionAttribute(): ?EntityVersion
    {
        return $this->versions()->first();
    }
}
