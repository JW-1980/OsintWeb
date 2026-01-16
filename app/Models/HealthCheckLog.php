<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Health Check Log Model
 *
 * Records system health check results for monitoring and alerting purposes.
 *
 * @property int $id
 * @property string $uuid
 * @property string $check_type
 * @property string $status
 * @property int $duration_ms
 * @property array $checks_performed
 * @property array|null $failed_checks
 * @property array|null $warnings
 * @property array|null $metadata
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property int|null $triggered_by_user_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property-read User|null $triggeredBy
 */
class HealthCheckLog extends Model
{
    // Check types
    public const TYPE_FULL = 'full';
    public const TYPE_QUICK = 'quick';
    public const TYPE_DATABASE = 'database';
    public const TYPE_CACHE = 'cache';
    public const TYPE_SEARCH = 'search';
    public const TYPE_QUEUE = 'queue';
    public const TYPE_STORAGE = 'storage';
    public const TYPE_EXTERNAL = 'external';
    public const TYPE_MIGRATIONS = 'migrations';
    public const TYPE_SCHEDULER = 'scheduler';

    // Status values
    public const STATUS_HEALTHY = 'healthy';
    public const STATUS_DEGRADED = 'degraded';
    public const STATUS_UNHEALTHY = 'unhealthy';
    public const STATUS_ERROR = 'error';

    protected $fillable = [
        'uuid',
        'check_type',
        'status',
        'duration_ms',
        'checks_performed',
        'failed_checks',
        'warnings',
        'metadata',
        'ip_address',
        'user_agent',
        'triggered_by_user_id',
    ];

    protected $casts = [
        'checks_performed' => 'array',
        'failed_checks' => 'array',
        'warnings' => 'array',
        'metadata' => 'array',
        'duration_ms' => 'integer',
    ];

    /**
     * Boot method to auto-generate UUID
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (HealthCheckLog $log) {
            if (empty($log->uuid)) {
                $log->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the user who triggered this health check
     */
    public function triggeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triggered_by_user_id');
    }

    /**
     * Scope to filter by check type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('check_type', $type);
    }

    /**
     * Scope to filter by status
     */
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for unhealthy checks
     */
    public function scopeUnhealthy($query)
    {
        return $query->whereIn('status', [self::STATUS_UNHEALTHY, self::STATUS_ERROR]);
    }

    /**
     * Scope for recent checks
     */
    public function scopeRecent($query, int $hours = 24)
    {
        return $query->where('created_at', '>=', now()->subHours($hours));
    }

    /**
     * Check if the health check passed
     */
    public function isHealthy(): bool
    {
        return $this->status === self::STATUS_HEALTHY;
    }

    /**
     * Check if the health check has warnings but is still operational
     */
    public function isDegraded(): bool
    {
        return $this->status === self::STATUS_DEGRADED;
    }

    /**
     * Check if the system is unhealthy
     */
    public function isUnhealthy(): bool
    {
        return in_array($this->status, [self::STATUS_UNHEALTHY, self::STATUS_ERROR]);
    }

    /**
     * Get human-readable duration
     */
    public function getFormattedDurationAttribute(): string
    {
        if ($this->duration_ms < 1000) {
            return "{$this->duration_ms}ms";
        }

        return round($this->duration_ms / 1000, 2) . 's';
    }

    /**
     * Get count of failed checks
     */
    public function getFailedCountAttribute(): int
    {
        return count($this->failed_checks ?? []);
    }

    /**
     * Get count of warnings
     */
    public function getWarningCountAttribute(): int
    {
        return count($this->warnings ?? []);
    }

    /**
     * Get all available check types
     */
    public static function getCheckTypes(): array
    {
        return [
            self::TYPE_FULL,
            self::TYPE_QUICK,
            self::TYPE_DATABASE,
            self::TYPE_CACHE,
            self::TYPE_SEARCH,
            self::TYPE_QUEUE,
            self::TYPE_STORAGE,
            self::TYPE_EXTERNAL,
            self::TYPE_MIGRATIONS,
            self::TYPE_SCHEDULER,
        ];
    }

    /**
     * Get all available statuses
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_HEALTHY,
            self::STATUS_DEGRADED,
            self::STATUS_UNHEALTHY,
            self::STATUS_ERROR,
        ];
    }

    /**
     * Get the latest check of a specific type
     */
    public static function latestOfType(string $type): ?self
    {
        return static::ofType($type)
            ->latest()
            ->first();
    }

    /**
     * Get average duration for a check type over a period
     */
    public static function averageDuration(string $type, int $hours = 24): ?float
    {
        return static::ofType($type)
            ->recent($hours)
            ->avg('duration_ms');
    }

    /**
     * Get health check statistics
     */
    public static function getStatistics(int $hours = 24): array
    {
        $recent = static::recent($hours);

        return [
            'total_checks' => $recent->count(),
            'healthy_count' => (clone $recent)->withStatus(self::STATUS_HEALTHY)->count(),
            'degraded_count' => (clone $recent)->withStatus(self::STATUS_DEGRADED)->count(),
            'unhealthy_count' => (clone $recent)->unhealthy()->count(),
            'average_duration_ms' => (clone $recent)->avg('duration_ms'),
            'by_type' => static::query()
                ->selectRaw('check_type, COUNT(*) as count, AVG(duration_ms) as avg_duration')
                ->recent($hours)
                ->groupBy('check_type')
                ->get()
                ->keyBy('check_type')
                ->toArray(),
        ];
    }
}
