<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Entity Version Model
 *
 * Stores complete snapshots of entities at each version.
 *
 * @property int $id
 * @property string $uuid
 * @property string $versionable_type
 * @property int $versionable_id
 * @property string|null $versionable_uuid
 * @property int $version_number
 * @property string $version_hash
 * @property array $snapshot
 * @property int|null $created_by
 * @property \Carbon\Carbon $created_at
 * @property string|null $change_summary
 * @property string|null $change_type
 * @property int|null $parent_version_id
 * @property int|null $audit_log_id
 */
class EntityVersion extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'uuid',
        'versionable_type',
        'versionable_id',
        'versionable_uuid',
        'version_number',
        'version_hash',
        'snapshot',
        'created_by',
        'change_summary',
        'change_type',
        'parent_version_id',
        'audit_log_id',
    ];

    protected $casts = [
        'version_number' => 'integer',
        'snapshot' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Get the versionable entity
     */
    public function versionable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user who created this version
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the parent version
     */
    public function parentVersion(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_version_id');
    }

    /**
     * Get the audit log entry
     */
    public function auditLog(): BelongsTo
    {
        return $this->belongsTo(AuditLog::class);
    }

    /**
     * Scope to get versions for a specific entity
     */
    public function scopeForEntity($query, string $type, int $id)
    {
        return $query->where('versionable_type', $type)
            ->where('versionable_id', $id);
    }

    /**
     * Scope to get latest version
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('version_number', 'desc');
    }

    /**
     * Get specific field value from snapshot
     */
    public function getSnapshotValue(string $field)
    {
        return $this->snapshot[$field] ?? null;
    }
}
