<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * OfflineCacheManifest Model
 *
 * Tracks offline cache state for users including cached routes,
 * entities, and sync status.
 *
 * @property int $id
 * @property int $user_id
 * @property string $cache_version
 * @property array|null $cached_routes
 * @property array|null $cached_entities
 * @property \Carbon\Carbon|null $last_sync_at
 * @property string $sync_status
 * @property array|null $pending_changes
 * @property int $storage_used_bytes
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class OfflineCacheManifest extends Model
{
    use HasFactory;

    public const STATUS_SYNCED = 'synced';
    public const STATUS_PENDING = 'pending';
    public const STATUS_ERROR = 'error';

    protected $fillable = [
        'user_id',
        'cache_version',
        'cached_routes',
        'cached_entities',
        'last_sync_at',
        'sync_status',
        'pending_changes',
        'storage_used_bytes',
    ];

    protected $casts = [
        'cached_routes' => 'array',
        'cached_entities' => 'array',
        'pending_changes' => 'array',
        'last_sync_at' => 'datetime',
        'storage_used_bytes' => 'integer',
    ];

    /**
     * Get the user that owns this cache manifest
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all available sync statuses
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_SYNCED,
            self::STATUS_PENDING,
            self::STATUS_ERROR,
        ];
    }

    /**
     * Mark as synced
     */
    public function markAsSynced(): void
    {
        $this->update([
            'sync_status' => self::STATUS_SYNCED,
            'last_sync_at' => now(),
            'pending_changes' => null,
        ]);
    }

    /**
     * Mark as pending
     */
    public function markAsPending(array $changes = []): void
    {
        $existingChanges = $this->pending_changes ?? [];
        $mergedChanges = array_merge($existingChanges, $changes);

        $this->update([
            'sync_status' => self::STATUS_PENDING,
            'pending_changes' => $mergedChanges,
        ]);
    }

    /**
     * Mark as error
     */
    public function markAsError(): void
    {
        $this->update([
            'sync_status' => self::STATUS_ERROR,
        ]);
    }

    /**
     * Add a cached route
     */
    public function addCachedRoute(string $route): void
    {
        $routes = $this->cached_routes ?? [];
        if (!in_array($route, $routes)) {
            $routes[] = $route;
            $this->update(['cached_routes' => $routes]);
        }
    }

    /**
     * Remove a cached route
     */
    public function removeCachedRoute(string $route): void
    {
        $routes = $this->cached_routes ?? [];
        $routes = array_values(array_diff($routes, [$route]));
        $this->update(['cached_routes' => $routes]);
    }

    /**
     * Add a cached entity
     */
    public function addCachedEntity(string $type, int|string $id): void
    {
        $entities = $this->cached_entities ?? [];

        if (!isset($entities[$type])) {
            $entities[$type] = [];
        }

        if (!in_array($id, $entities[$type])) {
            $entities[$type][] = $id;
            $this->update(['cached_entities' => $entities]);
        }
    }

    /**
     * Remove a cached entity
     */
    public function removeCachedEntity(string $type, int|string $id): void
    {
        $entities = $this->cached_entities ?? [];

        if (isset($entities[$type])) {
            $entities[$type] = array_values(array_diff($entities[$type], [$id]));

            if (empty($entities[$type])) {
                unset($entities[$type]);
            }

            $this->update(['cached_entities' => $entities]);
        }
    }

    /**
     * Check if entity is cached
     */
    public function hasEntity(string $type, int|string $id): bool
    {
        $entities = $this->cached_entities ?? [];
        return isset($entities[$type]) && in_array($id, $entities[$type]);
    }

    /**
     * Update storage usage
     */
    public function updateStorageUsage(int $bytes): void
    {
        $this->update(['storage_used_bytes' => $bytes]);
    }

    /**
     * Get human-readable storage size
     */
    public function getHumanStorageSizeAttribute(): string
    {
        $bytes = $this->storage_used_bytes;

        if ($bytes < 1024) {
            return $bytes . ' B';
        } elseif ($bytes < 1048576) {
            return round($bytes / 1024, 2) . ' KB';
        } elseif ($bytes < 1073741824) {
            return round($bytes / 1048576, 2) . ' MB';
        }

        return round($bytes / 1073741824, 2) . ' GB';
    }

    /**
     * Clear all cached data references
     */
    public function clearCache(): void
    {
        $this->update([
            'cached_routes' => null,
            'cached_entities' => null,
            'pending_changes' => null,
            'storage_used_bytes' => 0,
            'sync_status' => self::STATUS_SYNCED,
        ]);
    }

    /**
     * Increment cache version
     */
    public function incrementVersion(): void
    {
        $parts = explode('.', $this->cache_version);
        $parts[2] = isset($parts[2]) ? (int) $parts[2] + 1 : 1;
        $this->update(['cache_version' => implode('.', $parts)]);
    }

    /**
     * Get pending changes count
     */
    public function getPendingChangesCountAttribute(): int
    {
        return count($this->pending_changes ?? []);
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
        return $query->where('sync_status', $status);
    }

    /**
     * Scope to filter pending syncs
     */
    public function scopePending($query)
    {
        return $query->where('sync_status', self::STATUS_PENDING);
    }

    /**
     * Scope to filter synced
     */
    public function scopeSynced($query)
    {
        return $query->where('sync_status', self::STATUS_SYNCED);
    }

    /**
     * Scope to filter errors
     */
    public function scopeWithErrors($query)
    {
        return $query->where('sync_status', self::STATUS_ERROR);
    }
}
