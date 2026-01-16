<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Social Media Alert Model
 *
 * Represents an alert configuration for monitoring social media activity.
 *
 * Database Schema (social_media_alerts):
 * @property int $id Primary key
 * @property string $uuid Public-facing unique identifier
 * @property int|null $account_id Optional foreign key to monitor specific account
 * @property string|null $keyword Optional keyword to watch for
 * @property string $alert_type Type of alert (new_post, keyword_match, account_activity)
 * @property int $user_id User who owns this alert
 * @property string $name Human-readable alert name
 * @property string|null $description Optional description of alert purpose
 * @property array|null $notification_channels Array of notification methods
 * @property array|null $filters Additional filtering criteria
 * @property bool $is_active Whether alert is currently active
 * @property int $cooldown_minutes Minimum minutes between triggers
 * @property \Carbon\Carbon|null $last_triggered_at When alert was last triggered
 * @property int $trigger_count Total times alert has been triggered
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * Relationships:
 * @property-read SocialMediaAccount|null $account The monitored account (if account-specific)
 * @property-read User $user The user who owns this alert
 * @property-read \Illuminate\Database\Eloquent\Collection|SocialMediaPost[] $triggeredPosts Posts that triggered this alert
 */
class SocialMediaAlert extends Model
{
    use HasFactory;

    // Alert type constants
    public const TYPE_NEW_POST = 'new_post';
    public const TYPE_KEYWORD_MATCH = 'keyword_match';
    public const TYPE_ACCOUNT_ACTIVITY = 'account_activity';

    // Notification channel constants
    public const CHANNEL_EMAIL = 'email';
    public const CHANNEL_DATABASE = 'database';
    public const CHANNEL_SLACK = 'slack';
    public const CHANNEL_WEBHOOK = 'webhook';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'uuid',
        'account_id',
        'keyword',
        'alert_type',
        'user_id',
        'name',
        'description',
        'notification_channels',
        'filters',
        'is_active',
        'cooldown_minutes',
        'last_triggered_at',
        'trigger_count',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'notification_channels' => 'array',
        'filters' => 'array',
        'is_active' => 'boolean',
        'cooldown_minutes' => 'integer',
        'last_triggered_at' => 'datetime',
        'trigger_count' => 'integer',
    ];

    /**
     * Get all alert types.
     *
     * @return array<string>
     */
    public static function getAlertTypes(): array
    {
        return [
            self::TYPE_NEW_POST,
            self::TYPE_KEYWORD_MATCH,
            self::TYPE_ACCOUNT_ACTIVITY,
        ];
    }

    /**
     * Get alert type labels.
     *
     * @return array<string, string>
     */
    public static function getAlertTypeLabels(): array
    {
        return [
            self::TYPE_NEW_POST => 'New Post',
            self::TYPE_KEYWORD_MATCH => 'Keyword Match',
            self::TYPE_ACCOUNT_ACTIVITY => 'Account Activity',
        ];
    }

    /**
     * Get all notification channels.
     *
     * @return array<string>
     */
    public static function getNotificationChannels(): array
    {
        return [
            self::CHANNEL_EMAIL,
            self::CHANNEL_DATABASE,
            self::CHANNEL_SLACK,
            self::CHANNEL_WEBHOOK,
        ];
    }

    /**
     * Get the monitored account (if account-specific).
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(SocialMediaAccount::class, 'account_id');
    }

    /**
     * Get the user who owns this alert.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get posts that triggered this alert.
     */
    public function triggeredPosts(): BelongsToMany
    {
        return $this->belongsToMany(
            SocialMediaPost::class,
            'social_media_alert_triggers',
            'alert_id',
            'post_id'
        )
            ->withPivot(['match_reason', 'match_details', 'triggered_at'])
            ->orderByPivot('triggered_at', 'desc');
    }

    /**
     * Get alert type label.
     *
     * @return string
     */
    public function getAlertTypeLabel(): string
    {
        return self::getAlertTypeLabels()[$this->alert_type] ?? ucfirst($this->alert_type);
    }

    /**
     * Check if alert is account-specific.
     *
     * @return bool
     */
    public function isAccountSpecific(): bool
    {
        return $this->account_id !== null;
    }

    /**
     * Check if alert is keyword-based.
     *
     * @return bool
     */
    public function isKeywordBased(): bool
    {
        return $this->keyword !== null && $this->alert_type === self::TYPE_KEYWORD_MATCH;
    }

    /**
     * Check if alert is on cooldown.
     *
     * @return bool
     */
    public function isOnCooldown(): bool
    {
        if ($this->last_triggered_at === null) {
            return false;
        }

        return $this->last_triggered_at->addMinutes($this->cooldown_minutes)->isFuture();
    }

    /**
     * Get minutes until cooldown ends.
     *
     * @return int
     */
    public function getCooldownRemainingMinutes(): int
    {
        if (!$this->isOnCooldown()) {
            return 0;
        }

        return (int) now()->diffInMinutes(
            $this->last_triggered_at->addMinutes($this->cooldown_minutes)
        );
    }

    /**
     * Check if alert can be triggered.
     *
     * @return bool
     */
    public function canTrigger(): bool
    {
        return $this->is_active && !$this->isOnCooldown();
    }

    /**
     * Record a trigger event.
     *
     * @param SocialMediaPost $post
     * @param string|null $matchReason
     * @param array|null $matchDetails
     */
    public function recordTrigger(
        SocialMediaPost $post,
        ?string $matchReason = null,
        ?array $matchDetails = null
    ): void {
        $this->triggeredPosts()->attach($post->id, [
            'match_reason' => $matchReason,
            'match_details' => $matchDetails ? json_encode($matchDetails) : null,
            'triggered_at' => now(),
        ]);

        $this->update([
            'last_triggered_at' => now(),
            'trigger_count' => $this->trigger_count + 1,
        ]);
    }

    /**
     * Toggle alert active status.
     *
     * @return bool New status
     */
    public function toggle(): bool
    {
        $this->is_active = !$this->is_active;
        $this->save();

        return $this->is_active;
    }

    /**
     * Activate the alert.
     */
    public function activate(): void
    {
        $this->update(['is_active' => true]);
    }

    /**
     * Deactivate the alert.
     */
    public function deactivate(): void
    {
        $this->update(['is_active' => false]);
    }

    /**
     * Check if a post matches this alert.
     *
     * @param SocialMediaPost $post
     * @return array|null Match result with reason, or null if no match
     */
    public function matchesPost(SocialMediaPost $post): ?array
    {
        // Check account filter
        if ($this->account_id !== null && $post->account_id !== $this->account_id) {
            return null;
        }

        // Apply additional filters if present
        if ($this->filters) {
            // Platform filter
            if (isset($this->filters['platforms'])) {
                $platform = $post->account?->platform;
                if (!in_array($platform, $this->filters['platforms'])) {
                    return null;
                }
            }

            // Language filter
            if (isset($this->filters['languages']) && $post->language_detected) {
                if (!in_array($post->language_detected, $this->filters['languages'])) {
                    return null;
                }
            }

            // Post type filter
            if (isset($this->filters['post_types'])) {
                if (!in_array($post->post_type, $this->filters['post_types'])) {
                    return null;
                }
            }
        }

        // Check by alert type
        return match ($this->alert_type) {
            self::TYPE_NEW_POST => [
                'reason' => 'New post from monitored account',
                'details' => ['account_id' => $post->account_id],
            ],
            self::TYPE_KEYWORD_MATCH => $this->matchKeyword($post),
            self::TYPE_ACCOUNT_ACTIVITY => [
                'reason' => 'Account activity detected',
                'details' => ['activity_type' => $post->is_reply ? 'reply' : ($post->is_repost ? 'repost' : 'post')],
            ],
            default => null,
        };
    }

    /**
     * Check if post matches keyword.
     *
     * @param SocialMediaPost $post
     * @return array|null
     */
    protected function matchKeyword(SocialMediaPost $post): ?array
    {
        if (!$this->keyword) {
            return null;
        }

        $content = strtolower($post->content_text ?? '');
        $keyword = strtolower($this->keyword);

        // Support multiple keywords separated by comma
        $keywords = array_map('trim', explode(',', $keyword));
        $matched = [];

        foreach ($keywords as $kw) {
            if (str_contains($content, $kw)) {
                $matched[] = $kw;
            }
        }

        if (empty($matched)) {
            return null;
        }

        return [
            'reason' => 'Keyword match found',
            'details' => [
                'keywords' => $matched,
                'content_preview' => substr($post->content_text ?? '', 0, 200),
            ],
        ];
    }

    /**
     * Get notification channel instances.
     *
     * @return array
     */
    public function getNotificationChannelInstances(): array
    {
        $channels = $this->notification_channels ?? [self::CHANNEL_DATABASE];

        return array_map(function ($channel) {
            return match ($channel) {
                self::CHANNEL_EMAIL => 'mail',
                self::CHANNEL_DATABASE => 'database',
                self::CHANNEL_SLACK => 'slack',
                self::CHANNEL_WEBHOOK => 'webhook',
                default => 'database',
            };
        }, $channels);
    }

    /**
     * Scope for active alerts.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for alerts of a specific type.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('alert_type', $type);
    }

    /**
     * Scope for alerts owned by a user.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for alerts for a specific account.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $accountId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForAccount($query, int $accountId)
    {
        return $query->where('account_id', $accountId);
    }

    /**
     * Scope for alerts with a specific keyword.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $keyword
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithKeyword($query, string $keyword)
    {
        return $query->where('keyword', 'like', "%{$keyword}%");
    }

    /**
     * Scope for alerts not on cooldown.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNotOnCooldown($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('last_triggered_at')
                ->orWhereRaw(
                    "DATE_ADD(last_triggered_at, INTERVAL cooldown_minutes MINUTE) <= NOW()"
                );
        });
    }

    /**
     * Scope for triggerable alerts (active and not on cooldown).
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeTriggerable($query)
    {
        return $query->active()->notOnCooldown();
    }
}
