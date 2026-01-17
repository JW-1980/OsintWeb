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

    // Frontend page view activities
    public const TYPE_PAGE_VIEW = 'page_view';
    public const TYPE_MAP_VIEW = 'map_view';
    public const TYPE_MAP_INTERACTION = 'map_interaction';
    public const TYPE_SEARCH = 'search';
    public const TYPE_FILTER_APPLY = 'filter_apply';
    public const TYPE_EXPORT_REQUEST = 'export_request';
    public const TYPE_SHARE = 'share';

    // Content interaction activities
    public const TYPE_CONFLICT_VIEW = 'conflict_view';
    public const TYPE_ACTOR_VIEW = 'actor_view';
    public const TYPE_EQUIPMENT_VIEW = 'equipment_view';
    public const TYPE_ZONE_VIEW = 'zone_view';
    public const TYPE_SOURCE_VIEW = 'source_view';
    public const TYPE_TIMELINE_VIEW = 'timeline_view';
    public const TYPE_REPORT_VIEW = 'report_view';

    // User contribution activities
    public const TYPE_EVENT_CREATE = 'event_create';
    public const TYPE_EVENT_EDIT = 'event_edit';
    public const TYPE_EVENT_VERIFY = 'event_verify';
    public const TYPE_EVENT_DISPUTE = 'event_dispute';
    public const TYPE_SOURCE_CREATE = 'source_create';
    public const TYPE_SOURCE_VERIFY = 'source_verify';
    public const TYPE_EVIDENCE_SUBMIT = 'evidence_submit';
    public const TYPE_TIP_SUBMIT = 'tip_submit';

    // Social/community activities
    public const TYPE_FOLLOW_USER = 'follow_user';
    public const TYPE_UNFOLLOW_USER = 'unfollow_user';
    public const TYPE_LIKE = 'like';
    public const TYPE_UNLIKE = 'unlike';
    public const TYPE_REPORT_CONTENT = 'report_content';

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
            // Frontend page view activities
            self::TYPE_PAGE_VIEW => 'eye',
            self::TYPE_MAP_VIEW => 'map',
            self::TYPE_MAP_INTERACTION => 'cursor-arrow-rays',
            self::TYPE_SEARCH => 'magnifying-glass',
            self::TYPE_FILTER_APPLY => 'funnel',
            self::TYPE_EXPORT_REQUEST => 'arrow-down-tray',
            self::TYPE_SHARE => 'share',
            // Content interaction activities
            self::TYPE_CONFLICT_VIEW => 'fire',
            self::TYPE_ACTOR_VIEW => 'user-group',
            self::TYPE_EQUIPMENT_VIEW => 'wrench-screwdriver',
            self::TYPE_ZONE_VIEW => 'map-pin',
            self::TYPE_SOURCE_VIEW => 'link',
            self::TYPE_TIMELINE_VIEW => 'clock',
            self::TYPE_REPORT_VIEW => 'document-chart-bar',
            // User contribution activities
            self::TYPE_EVENT_CREATE => 'plus-circle',
            self::TYPE_EVENT_EDIT => 'pencil-square',
            self::TYPE_EVENT_VERIFY => 'check-badge',
            self::TYPE_EVENT_DISPUTE => 'exclamation-triangle',
            self::TYPE_SOURCE_CREATE => 'link',
            self::TYPE_SOURCE_VERIFY => 'check-badge',
            self::TYPE_EVIDENCE_SUBMIT => 'document-arrow-up',
            self::TYPE_TIP_SUBMIT => 'light-bulb',
            // Social/community activities
            self::TYPE_FOLLOW_USER => 'user-plus',
            self::TYPE_UNFOLLOW_USER => 'user-minus',
            self::TYPE_LIKE => 'heart',
            self::TYPE_UNLIKE => 'heart',
            self::TYPE_REPORT_CONTENT => 'flag',
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
            // Frontend page view activities
            self::TYPE_PAGE_VIEW => 'Visited a page',
            self::TYPE_MAP_VIEW => 'Viewed the map',
            self::TYPE_MAP_INTERACTION => 'Interacted with the map',
            self::TYPE_SEARCH => 'Performed a search',
            self::TYPE_FILTER_APPLY => 'Applied filters',
            self::TYPE_EXPORT_REQUEST => 'Requested export',
            self::TYPE_SHARE => 'Shared content',
            // Content interaction activities
            self::TYPE_CONFLICT_VIEW => 'Viewed a conflict',
            self::TYPE_ACTOR_VIEW => 'Viewed an actor profile',
            self::TYPE_EQUIPMENT_VIEW => 'Viewed equipment details',
            self::TYPE_ZONE_VIEW => 'Viewed a control zone',
            self::TYPE_SOURCE_VIEW => 'Viewed a source',
            self::TYPE_TIMELINE_VIEW => 'Viewed the timeline',
            self::TYPE_REPORT_VIEW => 'Viewed a report',
            // User contribution activities
            self::TYPE_EVENT_CREATE => 'Created an event',
            self::TYPE_EVENT_EDIT => 'Edited an event',
            self::TYPE_EVENT_VERIFY => 'Verified an event',
            self::TYPE_EVENT_DISPUTE => 'Disputed an event',
            self::TYPE_SOURCE_CREATE => 'Added a new source',
            self::TYPE_SOURCE_VERIFY => 'Verified a source',
            self::TYPE_EVIDENCE_SUBMIT => 'Submitted evidence',
            self::TYPE_TIP_SUBMIT => 'Submitted a tip',
            // Social/community activities
            self::TYPE_FOLLOW_USER => 'Started following a user',
            self::TYPE_UNFOLLOW_USER => 'Stopped following a user',
            self::TYPE_LIKE => 'Liked content',
            self::TYPE_UNLIKE => 'Removed like from content',
            self::TYPE_REPORT_CONTENT => 'Reported content',
            default => 'Performed an action',
        };
    }

    /**
     * Get activity types that should be shown in the public feed.
     */
    public static function getPublicActivityTypes(): array
    {
        return [
            self::TYPE_EVENT_CREATE,
            self::TYPE_EVENT_VERIFY,
            self::TYPE_SOURCE_CREATE,
            self::TYPE_SOURCE_VERIFY,
            self::TYPE_EVIDENCE_SUBMIT,
            self::TYPE_TIP_SUBMIT,
            self::TYPE_COMMENT_CREATE,
            self::TYPE_ARTICLE_BOOKMARK,
        ];
    }

    /**
     * Scope to filter for public activity feed (contributions only).
     */
    public function scopePublicFeed($query)
    {
        return $query->whereIn('activity_type', self::getPublicActivityTypes());
    }

    /**
     * Get recent site-wide activity for display on homepage.
     */
    public static function getRecentPublicActivity(int $limit = 20): \Illuminate\Database\Eloquent\Collection
    {
        return self::with('user:id,name,avatar_url')
            ->publicFeed()
            ->recent(7)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
