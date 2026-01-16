<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * SyncQueue Model
 *
 * Manages offline changes that need to be synchronized with the server.
 *
 * @property int $id
 * @property string $uuid
 * @property int $user_id
 * @property string $operation
 * @property string $entity_type
 * @property int|null $entity_id
 * @property array $payload
 * @property int $priority
 * @property string $status
 * @property array|null $conflict_data
 * @property int $attempts
 * @property \Carbon\Carbon|null $last_attempt_at
 * @property \Carbon\Carbon|null $synced_at
 * @property string|null $error_message
 * @property string|null $client_id
 * @property array|null $metadata
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class SyncQueue extends Model
{
    use HasFactory;

    protected $table = 'sync_queue';

    public const OPERATION_CREATE = 'create';
    public const OPERATION_UPDATE = 'update';
    public const OPERATION_DELETE = 'delete';

    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_SYNCED = 'synced';
    public const STATUS_FAILED = 'failed';
    public const STATUS_CONFLICT = 'conflict';

    public const PRIORITY_CRITICAL = 1;
    public const PRIORITY_HIGH = 2;
    public const PRIORITY_NORMAL = 3;
    public const PRIORITY_LOW = 4;
    public const PRIORITY_BACKGROUND = 5;

    public const MAX_ATTEMPTS = 5;

    protected $fillable = [
        'uuid',
        'user_id',
        'operation',
        'entity_type',
        'entity_id',
        'payload',
        'priority',
        'status',
        'conflict_data',
        'attempts',
        'last_attempt_at',
        'synced_at',
        'error_message',
        'client_id',
        'metadata',
    ];

    protected $casts = [
        'payload' => 'array',
        'conflict_data' => 'array',
        'metadata' => 'array',
        'priority' => 'integer',
        'attempts' => 'integer',
        'entity_id' => 'integer',
        'last_attempt_at' => 'datetime',
        'synced_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (SyncQueue $sync) {
            if (empty($sync->uuid)) {
                $sync->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the user that owns this sync item
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all available operations
     */
    public static function getOperations(): array
    {
        return [
            self::OPERATION_CREATE,
            self::OPERATION_UPDATE,
            self::OPERATION_DELETE,
        ];
    }

    /**
     * Get all available statuses
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_PROCESSING,
            self::STATUS_SYNCED,
            self::STATUS_FAILED,
            self::STATUS_CONFLICT,
        ];
    }

    /**
     * Get all priority levels
     */
    public static function getPriorities(): array
    {
        return [
            self::PRIORITY_CRITICAL => 'Critical',
            self::PRIORITY_HIGH => 'High',
            self::PRIORITY_NORMAL => 'Normal',
            self::PRIORITY_LOW => 'Low',
            self::PRIORITY_BACKGROUND => 'Background',
        ];
    }

    /**
     * Mark as processing
     */
    public function markAsProcessing(): void
    {
        $this->update([
            'status' => self::STATUS_PROCESSING,
            'attempts' => $this->attempts + 1,
            'last_attempt_at' => now(),
        ]);
    }

    /**
     * Mark as synced
     */
    public function markAsSynced(?int $entityId = null): void
    {
        $updateData = [
            'status' => self::STATUS_SYNCED,
            'synced_at' => now(),
            'error_message' => null,
        ];

        if ($entityId !== null) {
            $updateData['entity_id'] = $entityId;
        }

        $this->update($updateData);
    }

    /**
     * Mark as failed
     */
    public function markAsFailed(string $errorMessage): void
    {
        $status = $this->attempts >= self::MAX_ATTEMPTS
            ? self::STATUS_FAILED
            : self::STATUS_PENDING;

        $this->update([
            'status' => $status,
            'error_message' => $errorMessage,
        ]);
    }

    /**
     * Mark as conflict
     */
    public function markAsConflict(array $conflictData): void
    {
        $this->update([
            'status' => self::STATUS_CONFLICT,
            'conflict_data' => $conflictData,
        ]);
    }

    /**
     * Reset for retry
     */
    public function resetForRetry(): void
    {
        $this->update([
            'status' => self::STATUS_PENDING,
            'error_message' => null,
            'attempts' => 0,
        ]);
    }

    /**
     * Check if can retry
     */
    public function canRetry(): bool
    {
        return $this->attempts < self::MAX_ATTEMPTS
            && !in_array($this->status, [self::STATUS_SYNCED, self::STATUS_CONFLICT]);
    }

    /**
     * Check if is in conflict
     */
    public function isInConflict(): bool
    {
        return $this->status === self::STATUS_CONFLICT;
    }

    /**
     * Resolve conflict with server version (discard local changes)
     */
    public function resolveWithServerVersion(): void
    {
        $this->update([
            'status' => self::STATUS_SYNCED,
            'conflict_data' => null,
            'synced_at' => now(),
        ]);
    }

    /**
     * Resolve conflict with local version (overwrite server)
     */
    public function resolveWithLocalVersion(): void
    {
        $this->update([
            'status' => self::STATUS_PENDING,
            'conflict_data' => null,
            'attempts' => 0,
        ]);
    }

    /**
     * Resolve conflict with merged data
     */
    public function resolveWithMergedData(array $mergedPayload): void
    {
        $this->update([
            'status' => self::STATUS_PENDING,
            'payload' => $mergedPayload,
            'conflict_data' => null,
            'attempts' => 0,
        ]);
    }

    /**
     * Get the priority label
     */
    public function getPriorityLabelAttribute(): string
    {
        $priorities = self::getPriorities();
        return $priorities[$this->priority] ?? 'Unknown';
    }

    /**
     * Check if operation is create
     */
    public function isCreate(): bool
    {
        return $this->operation === self::OPERATION_CREATE;
    }

    /**
     * Check if operation is update
     */
    public function isUpdate(): bool
    {
        return $this->operation === self::OPERATION_UPDATE;
    }

    /**
     * Check if operation is delete
     */
    public function isDelete(): bool
    {
        return $this->operation === self::OPERATION_DELETE;
    }

    /**
     * Scope to filter by user
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to filter by status
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter pending items
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope to filter processing items
     */
    public function scopeProcessing($query)
    {
        return $query->where('status', self::STATUS_PROCESSING);
    }

    /**
     * Scope to filter synced items
     */
    public function scopeSynced($query)
    {
        return $query->where('status', self::STATUS_SYNCED);
    }

    /**
     * Scope to filter failed items
     */
    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    /**
     * Scope to filter conflict items
     */
    public function scopeConflicts($query)
    {
        return $query->where('status', self::STATUS_CONFLICT);
    }

    /**
     * Scope to filter by entity type
     */
    public function scopeEntityType($query, string $type)
    {
        return $query->where('entity_type', $type);
    }

    /**
     * Scope to filter retryable items
     */
    public function scopeRetryable($query)
    {
        return $query->where('status', self::STATUS_PENDING)
            ->where('attempts', '<', self::MAX_ATTEMPTS);
    }

    /**
     * Scope to order by priority and created date
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('priority', 'asc')
            ->orderBy('created_at', 'asc');
    }

    /**
     * Scope to filter by client ID
     */
    public function scopeByClientId($query, string $clientId)
    {
        return $query->where('client_id', $clientId);
    }
}
