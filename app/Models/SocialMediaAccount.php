<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Social Media Account Model
 *
 * Represents a social media account being monitored for OSINT purposes.
 *
 * Database Schema (social_media_accounts):
 * @property int $id Primary key
 * @property string $uuid Public-facing unique identifier
 * @property string $platform Social media platform (twitter, telegram, facebook, instagram, youtube, tiktok, vk, reddit)
 * @property string $username The account's username/handle
 * @property string|null $display_name The account's display name
 * @property string|null $profile_url Full URL to the profile
 * @property string|null $platform_user_id Platform's internal user ID
 * @property string|null $profile_image_url URL to profile image
 * @property int $follower_count Number of followers
 * @property int $following_count Number of accounts being followed
 * @property int $post_count Total number of posts
 * @property \Carbon\Carbon|null $account_created_at When the account was created on the platform
 * @property string|null $bio Account biography/description
 * @property string|null $location Self-reported location
 * @property bool $is_verified Whether the account is verified on the platform
 * @property bool $is_monitored Whether we are actively monitoring this account
 * @property int $monitoring_priority Priority level 1-5 (1=highest)
 * @property \Carbon\Carbon|null $last_checked_at When we last checked for new posts
 * @property \Carbon\Carbon|null $last_post_at When the account last posted
 * @property array|null $metadata Additional platform-specific data (JSON)
 * @property int|null $added_by User ID who added this account
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 *
 * Relationships:
 * @property-read User|null $addedByUser The user who added this account
 * @property-read \Illuminate\Database\Eloquent\Collection|SocialMediaPost[] $posts Posts from this account
 * @property-read \Illuminate\Database\Eloquent\Collection|SocialMediaAlert[] $alerts Alerts for this account
 */
class SocialMediaAccount extends Model
{
    use HasFactory, SoftDeletes;

    // Platform constants
    public const PLATFORM_TWITTER = 'twitter';
    public const PLATFORM_TELEGRAM = 'telegram';
    public const PLATFORM_FACEBOOK = 'facebook';
    public const PLATFORM_INSTAGRAM = 'instagram';
    public const PLATFORM_YOUTUBE = 'youtube';
    public const PLATFORM_TIKTOK = 'tiktok';
    public const PLATFORM_VK = 'vk';
    public const PLATFORM_REDDIT = 'reddit';

    // Priority constants
    public const PRIORITY_CRITICAL = 1;
    public const PRIORITY_HIGH = 2;
    public const PRIORITY_MEDIUM = 3;
    public const PRIORITY_LOW = 4;
    public const PRIORITY_MINIMAL = 5;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'uuid',
        'platform',
        'username',
        'display_name',
        'profile_url',
        'platform_user_id',
        'profile_image_url',
        'follower_count',
        'following_count',
        'post_count',
        'account_created_at',
        'bio',
        'location',
        'is_verified',
        'is_monitored',
        'monitoring_priority',
        'last_checked_at',
        'last_post_at',
        'metadata',
        'added_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'follower_count' => 'integer',
        'following_count' => 'integer',
        'post_count' => 'integer',
        'account_created_at' => 'datetime',
        'is_verified' => 'boolean',
        'is_monitored' => 'boolean',
        'monitoring_priority' => 'integer',
        'last_checked_at' => 'datetime',
        'last_post_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Get all supported platforms.
     *
     * @return array<string>
     */
    public static function getPlatforms(): array
    {
        return [
            self::PLATFORM_TWITTER,
            self::PLATFORM_TELEGRAM,
            self::PLATFORM_FACEBOOK,
            self::PLATFORM_INSTAGRAM,
            self::PLATFORM_YOUTUBE,
            self::PLATFORM_TIKTOK,
            self::PLATFORM_VK,
            self::PLATFORM_REDDIT,
        ];
    }

    /**
     * Get platform display names.
     *
     * @return array<string, string>
     */
    public static function getPlatformLabels(): array
    {
        return [
            self::PLATFORM_TWITTER => 'Twitter/X',
            self::PLATFORM_TELEGRAM => 'Telegram',
            self::PLATFORM_FACEBOOK => 'Facebook',
            self::PLATFORM_INSTAGRAM => 'Instagram',
            self::PLATFORM_YOUTUBE => 'YouTube',
            self::PLATFORM_TIKTOK => 'TikTok',
            self::PLATFORM_VK => 'VKontakte',
            self::PLATFORM_REDDIT => 'Reddit',
        ];
    }

    /**
     * Get all priority levels.
     *
     * @return array<int, string>
     */
    public static function getPriorityLevels(): array
    {
        return [
            self::PRIORITY_CRITICAL => 'Critical',
            self::PRIORITY_HIGH => 'High',
            self::PRIORITY_MEDIUM => 'Medium',
            self::PRIORITY_LOW => 'Low',
            self::PRIORITY_MINIMAL => 'Minimal',
        ];
    }

    /**
     * Get the user who added this account.
     */
    public function addedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    /**
     * Get posts from this account.
     */
    public function posts(): HasMany
    {
        return $this->hasMany(SocialMediaPost::class, 'account_id');
    }

    /**
     * Get alerts configured for this account.
     */
    public function alerts(): HasMany
    {
        return $this->hasMany(SocialMediaAlert::class, 'account_id');
    }

    /**
     * Generate profile URL based on platform and username.
     *
     * @return string|null
     */
    public function generateProfileUrl(): ?string
    {
        return match ($this->platform) {
            self::PLATFORM_TWITTER => "https://twitter.com/{$this->username}",
            self::PLATFORM_TELEGRAM => "https://t.me/{$this->username}",
            self::PLATFORM_FACEBOOK => "https://facebook.com/{$this->username}",
            self::PLATFORM_INSTAGRAM => "https://instagram.com/{$this->username}",
            self::PLATFORM_YOUTUBE => "https://youtube.com/@{$this->username}",
            self::PLATFORM_TIKTOK => "https://tiktok.com/@{$this->username}",
            self::PLATFORM_VK => "https://vk.com/{$this->username}",
            self::PLATFORM_REDDIT => "https://reddit.com/user/{$this->username}",
            default => null,
        };
    }

    /**
     * Get the platform display name.
     *
     * @return string
     */
    public function getPlatformLabel(): string
    {
        return self::getPlatformLabels()[$this->platform] ?? ucfirst($this->platform);
    }

    /**
     * Get the priority display name.
     *
     * @return string
     */
    public function getPriorityLabel(): string
    {
        return self::getPriorityLevels()[$this->monitoring_priority] ?? 'Unknown';
    }

    /**
     * Mark the account as checked.
     */
    public function markAsChecked(): void
    {
        $this->update(['last_checked_at' => now()]);
    }

    /**
     * Update account stats.
     *
     * @param array $stats
     */
    public function updateStats(array $stats): void
    {
        $this->update([
            'follower_count' => $stats['follower_count'] ?? $this->follower_count,
            'following_count' => $stats['following_count'] ?? $this->following_count,
            'post_count' => $stats['post_count'] ?? $this->post_count,
        ]);
    }

    /**
     * Toggle monitoring status.
     *
     * @return bool New monitoring status
     */
    public function toggleMonitoring(): bool
    {
        $this->is_monitored = !$this->is_monitored;
        $this->save();

        return $this->is_monitored;
    }

    /**
     * Set monitoring priority.
     *
     * @param int $priority
     */
    public function setPriority(int $priority): void
    {
        $priority = max(1, min(5, $priority)); // Clamp to 1-5
        $this->update(['monitoring_priority' => $priority]);
    }

    /**
     * Get recent posts.
     *
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRecentPosts(int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return $this->posts()
            ->orderBy('posted_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Scope for monitored accounts.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeMonitored($query)
    {
        return $query->where('is_monitored', true);
    }

    /**
     * Scope for accounts needing check (ordered by priority).
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $olderThanMinutes
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNeedingCheck($query, int $olderThanMinutes = 60)
    {
        return $query->where('is_monitored', true)
            ->where(function ($q) use ($olderThanMinutes) {
                $q->whereNull('last_checked_at')
                    ->orWhere('last_checked_at', '<', now()->subMinutes($olderThanMinutes));
            })
            ->orderBy('monitoring_priority')
            ->orderBy('last_checked_at');
    }

    /**
     * Scope for filtering by platform.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $platform
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePlatform($query, string $platform)
    {
        return $query->where('platform', $platform);
    }

    /**
     * Scope for filtering by priority.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $priority
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePriority($query, int $priority)
    {
        return $query->where('monitoring_priority', $priority);
    }

    /**
     * Scope for verified accounts.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope for searching by username or display name.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $term
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('username', 'like', "%{$term}%")
                ->orWhere('display_name', 'like', "%{$term}%")
                ->orWhere('bio', 'like', "%{$term}%");
        });
    }

    /**
     * Scope for accounts added by specific user.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAddedBy($query, int $userId)
    {
        return $query->where('added_by', $userId);
    }
}
