<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * CommentRateLimit Model
 *
 * Tracks comment rate limiting per user/IP to prevent spam.
 *
 * Database Schema:
 * @property int $id Primary key
 * @property int|null $user_id Foreign key to users table
 * @property string|null $ip_address IP address for anonymous users
 * @property string|null $fingerprint Browser fingerprint for tracking
 * @property \Carbon\Carbon|null $last_comment_at Timestamp of last comment
 * @property int $comment_count_hour Number of comments in current hour
 * @property int $comment_count_day Number of comments today
 * @property bool $is_blocked Whether user/IP is blocked
 * @property \Carbon\Carbon|null $blocked_until Block expiration time
 * @property string|null $block_reason Reason for blocking
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property-read User|null $user
 */
class CommentRateLimit extends Model
{
    protected $fillable = [
        'user_id',
        'ip_address',
        'fingerprint',
        'last_comment_at',
        'comment_count_hour',
        'comment_count_day',
        'is_blocked',
        'blocked_until',
        'block_reason',
    ];

    protected $casts = [
        'last_comment_at' => 'datetime',
        'blocked_until' => 'datetime',
        'is_blocked' => 'boolean',
        'comment_count_hour' => 'integer',
        'comment_count_day' => 'integer',
    ];

    /**
     * Check if rate limited.
     */
    public function isRateLimited(): bool
    {
        if ($this->is_blocked && $this->blocked_until && $this->blocked_until->isFuture()) {
            return true;
        }

        $cooldownSeconds = (int) Setting::get('comment_cooldown_seconds', 30);
        $maxPerHour = (int) Setting::get('comment_max_per_hour', 20);
        $maxPerDay = (int) Setting::get('comment_max_per_day', 100);

        // Check cooldown
        if ($this->last_comment_at && $this->last_comment_at->addSeconds($cooldownSeconds)->isFuture()) {
            return true;
        }

        // Check hourly limit
        if ($this->comment_count_hour >= $maxPerHour) {
            return true;
        }

        // Check daily limit
        if ($this->comment_count_day >= $maxPerDay) {
            return true;
        }

        return false;
    }

    /**
     * Get seconds until can comment again.
     */
    public function getSecondsUntilAllowed(): int
    {
        if ($this->is_blocked && $this->blocked_until) {
            return max(0, now()->diffInSeconds($this->blocked_until, false));
        }

        $cooldownSeconds = (int) Setting::get('comment_cooldown_seconds', 30);

        if ($this->last_comment_at) {
            $allowedAt = $this->last_comment_at->addSeconds($cooldownSeconds);
            return max(0, now()->diffInSeconds($allowedAt, false));
        }

        return 0;
    }

    /**
     * Record a comment.
     */
    public function recordComment(): void
    {
        $this->update([
            'last_comment_at' => now(),
            'comment_count_hour' => $this->shouldResetHourly() ? 1 : $this->comment_count_hour + 1,
            'comment_count_day' => $this->shouldResetDaily() ? 1 : $this->comment_count_day + 1,
        ]);
    }

    /**
     * Check if hourly count should reset.
     */
    protected function shouldResetHourly(): bool
    {
        return !$this->last_comment_at || $this->last_comment_at->diffInHours(now()) >= 1;
    }

    /**
     * Check if daily count should reset.
     */
    protected function shouldResetDaily(): bool
    {
        return !$this->last_comment_at || !$this->last_comment_at->isToday();
    }

    /**
     * Block the user/IP.
     */
    public function block(string $reason, int $hours = 24): void
    {
        $this->update([
            'is_blocked' => true,
            'blocked_until' => now()->addHours($hours),
            'block_reason' => $reason,
        ]);
    }

    /**
     * Unblock.
     */
    public function unblock(): void
    {
        $this->update([
            'is_blocked' => false,
            'blocked_until' => null,
            'block_reason' => null,
        ]);
    }

    /**
     * Find or create rate limit record.
     */
    public static function findOrCreateForRequest(?int $userId, ?string $ipAddress, ?string $fingerprint = null): self
    {
        $query = self::query();

        if ($userId) {
            $query->where('user_id', $userId);
        } elseif ($ipAddress) {
            $query->where('ip_address', $ipAddress);
        } else {
            // Create a temporary record
            return new self([
                'last_comment_at' => null,
                'comment_count_hour' => 0,
                'comment_count_day' => 0,
            ]);
        }

        return $query->firstOrCreate(
            $userId ? ['user_id' => $userId] : ['ip_address' => $ipAddress],
            [
                'user_id' => $userId,
                'ip_address' => $ipAddress,
                'fingerprint' => $fingerprint,
                'last_comment_at' => null,
                'comment_count_hour' => 0,
                'comment_count_day' => 0,
            ]
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
