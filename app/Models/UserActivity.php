<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * User Activity Model
 *
 * Tracks user activities for onboarding progress and engagement analytics.
 *
 * @property int $id
 * @property int $user_id
 * @property string $activity_type
 * @property string|null $description
 * @property array|null $properties
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property \Carbon\Carbon $created_at
 */
class UserActivity extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'activity_type',
        'description',
        'properties',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'properties' => 'array',
        'created_at' => 'datetime',
    ];

    // Activity types
    public const TYPE_LOGIN = 'login';
    public const TYPE_LOGOUT = 'logout';
    public const TYPE_REGISTER = 'register';
    public const TYPE_EMAIL_VERIFY = 'email_verify';
    public const TYPE_PASSWORD_CHANGE = 'password_change';
    public const TYPE_PASSWORD_RESET = 'password_reset';
    public const TYPE_PROFILE_UPDATE = 'profile_update';
    public const TYPE_AVATAR_CHANGE = 'avatar_change';
    public const TYPE_SETTINGS_CHANGE = 'settings_change';
    public const TYPE_COMMENT_CREATE = 'comment_create';
    public const TYPE_COMMENT_EDIT = 'comment_edit';
    public const TYPE_ARTICLE_VIEW = 'article_view';
    public const TYPE_ARTICLE_BOOKMARK = 'article_bookmark';
    public const TYPE_EVENT_VIEW = 'event_view';
    public const TYPE_ONBOARDING_START = 'onboarding_start';
    public const TYPE_ONBOARDING_STEP = 'onboarding_step';
    public const TYPE_ONBOARDING_COMPLETE = 'onboarding_complete';
    public const TYPE_ONBOARDING_SKIP = 'onboarding_skip';
    public const TYPE_CONSENT_UPDATE = 'consent_update';
    public const TYPE_DATA_EXPORT = 'data_export';
    public const TYPE_ACCOUNT_DELETE_REQUEST = 'account_delete_request';
    public const TYPE_TWO_FACTOR_ENABLE = '2fa_enable';
    public const TYPE_TWO_FACTOR_DISABLE = '2fa_disable';
    public const TYPE_SESSION_TERMINATE = 'session_terminate';

    /**
     * Get the user that performed this activity.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Log an activity.
     */
    public static function log(
        User $user,
        string $type,
        ?string $description = null,
        ?array $properties = null,
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): self {
        $activity = self::create([
            'user_id' => $user->id,
            'activity_type' => $type,
            'description' => $description,
            'properties' => $properties,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent ? substr($userAgent, 0, 500) : null,
            'created_at' => now(),
        ]);

        // Trigger achievement check
        try {
            /** @var \App\Services\AchievementService $service */
            $service = app(\App\Services\AchievementService::class);
            $service->checkAchievements($user, $type);
        } catch (\Exception $e) {
            // Silently fail to not disrupt the activity log
            \Illuminate\Support\Facades\Log::error('Achievement check failed: ' . $e->getMessage());
        }

        return $activity;
    }

    /**
     * Scope to filter by activity type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('activity_type', $type);
    }

    /**
     * Scope to filter activities within a date range.
     */
    public function scopeBetween($query, $start, $end)
    {
        return $query->whereBetween('created_at', [$start, $end]);
    }

    /**
     * Scope to filter recent activities.
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Get activity icon based on type.
     */
    public function getIcon(): string
    {
        return match ($this->activity_type) {
            self::TYPE_LOGIN => 'arrow-right-on-rectangle',
            self::TYPE_LOGOUT => 'arrow-left-on-rectangle',
            self::TYPE_REGISTER => 'user-plus',
            self::TYPE_EMAIL_VERIFY => 'envelope-check',
            self::TYPE_PASSWORD_CHANGE, self::TYPE_PASSWORD_RESET => 'key',
            self::TYPE_PROFILE_UPDATE => 'user',
            self::TYPE_AVATAR_CHANGE => 'photo',
            self::TYPE_SETTINGS_CHANGE => 'cog',
            self::TYPE_COMMENT_CREATE, self::TYPE_COMMENT_EDIT => 'chat-bubble-left',
            self::TYPE_ARTICLE_VIEW => 'document-text',
            self::TYPE_ARTICLE_BOOKMARK => 'bookmark',
            self::TYPE_EVENT_VIEW => 'map-pin',
            self::TYPE_ONBOARDING_START, self::TYPE_ONBOARDING_STEP, self::TYPE_ONBOARDING_COMPLETE => 'academic-cap',
            self::TYPE_CONSENT_UPDATE => 'shield-check',
            self::TYPE_DATA_EXPORT => 'arrow-down-tray',
            self::TYPE_ACCOUNT_DELETE_REQUEST => 'trash',
            self::TYPE_TWO_FACTOR_ENABLE, self::TYPE_TWO_FACTOR_DISABLE => 'device-phone-mobile',
            self::TYPE_SESSION_TERMINATE => 'x-circle',
            default => 'information-circle',
        };
    }

    /**
     * Get human-readable activity description.
     */
    public function getReadableDescription(): string
    {
        return $this->description ?? match ($this->activity_type) {
            self::TYPE_LOGIN => 'Logged in',
            self::TYPE_LOGOUT => 'Logged out',
            self::TYPE_REGISTER => 'Created account',
            self::TYPE_EMAIL_VERIFY => 'Verified email address',
            self::TYPE_PASSWORD_CHANGE => 'Changed password',
            self::TYPE_PASSWORD_RESET => 'Reset password',
            self::TYPE_PROFILE_UPDATE => 'Updated profile',
            self::TYPE_AVATAR_CHANGE => 'Changed avatar',
            self::TYPE_SETTINGS_CHANGE => 'Updated settings',
            self::TYPE_COMMENT_CREATE => 'Posted a comment',
            self::TYPE_COMMENT_EDIT => 'Edited a comment',
            self::TYPE_ARTICLE_VIEW => 'Viewed an article',
            self::TYPE_ARTICLE_BOOKMARK => 'Bookmarked an article',
            self::TYPE_EVENT_VIEW => 'Viewed an event',
            self::TYPE_ONBOARDING_START => 'Started onboarding',
            self::TYPE_ONBOARDING_STEP => 'Completed onboarding step',
            self::TYPE_ONBOARDING_COMPLETE => 'Completed onboarding',
            self::TYPE_ONBOARDING_SKIP => 'Skipped onboarding',
            self::TYPE_CONSENT_UPDATE => 'Updated consent preferences',
            self::TYPE_DATA_EXPORT => 'Requested data export',
            self::TYPE_ACCOUNT_DELETE_REQUEST => 'Requested account deletion',
            self::TYPE_TWO_FACTOR_ENABLE => 'Enabled two-factor authentication',
            self::TYPE_TWO_FACTOR_DISABLE => 'Disabled two-factor authentication',
            self::TYPE_SESSION_TERMINATE => 'Terminated a session',
            default => 'Performed an action',
        };
    }
}
