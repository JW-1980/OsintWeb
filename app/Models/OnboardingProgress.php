<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Onboarding Progress Model
 *
 * Tracks individual onboarding step completion for users.
 *
 * @property int $id
 * @property int $user_id
 * @property string $step_key
 * @property string $step_name
 * @property bool $completed
 * @property \Carbon\Carbon|null $completed_at
 * @property array|null $step_data
 * @property int $order
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class OnboardingProgress extends Model
{
    protected $table = 'onboarding_progress';

    protected $fillable = [
        'user_id',
        'step_key',
        'step_name',
        'completed',
        'completed_at',
        'step_data',
        'order',
    ];

    protected $casts = [
        'completed' => 'boolean',
        'completed_at' => 'datetime',
        'step_data' => 'array',
        'order' => 'integer',
    ];

    // Onboarding step keys
    public const STEP_WELCOME = 'welcome';
    public const STEP_PROFILE = 'profile';
    public const STEP_AVATAR = 'avatar';
    public const STEP_PRIVACY = 'privacy';
    public const STEP_PREFERENCES = 'preferences';
    public const STEP_EXPLORE_MAP = 'explore_map';
    public const STEP_EXPLORE_EVENTS = 'explore_events';
    public const STEP_EXPLORE_EQUIPMENT = 'explore_equipment';
    public const STEP_FIRST_COMMENT = 'first_comment';
    public const STEP_COMPLETE = 'complete';

    /**
     * Get the user that owns this progress.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mark step as completed.
     */
    public function markCompleted(?array $stepData = null): void
    {
        $this->update([
            'completed' => true,
            'completed_at' => now(),
            'step_data' => $stepData ?? $this->step_data,
        ]);
    }

    /**
     * Get default onboarding steps.
     */
    public static function getDefaultSteps(): array
    {
        return [
            [
                'step_key' => self::STEP_WELCOME,
                'step_name' => 'Welcome',
                'order' => 1,
            ],
            [
                'step_key' => self::STEP_PROFILE,
                'step_name' => 'Complete Profile',
                'order' => 2,
            ],
            [
                'step_key' => self::STEP_AVATAR,
                'step_name' => 'Set Avatar',
                'order' => 3,
            ],
            [
                'step_key' => self::STEP_PRIVACY,
                'step_name' => 'Privacy Settings',
                'order' => 4,
            ],
            [
                'step_key' => self::STEP_PREFERENCES,
                'step_name' => 'Preferences',
                'order' => 5,
            ],
            [
                'step_key' => self::STEP_EXPLORE_MAP,
                'step_name' => 'Explore Map',
                'order' => 6,
            ],
            [
                'step_key' => self::STEP_EXPLORE_EVENTS,
                'step_name' => 'Browse Events',
                'order' => 7,
            ],
            [
                'step_key' => self::STEP_EXPLORE_EQUIPMENT,
                'step_name' => 'Explore Equipment',
                'order' => 8,
            ],
            [
                'step_key' => self::STEP_COMPLETE,
                'step_name' => 'Complete',
                'order' => 9,
            ],
        ];
    }

    /**
     * Initialize onboarding steps for a user.
     */
    public static function initializeForUser(User $user): void
    {
        foreach (self::getDefaultSteps() as $step) {
            self::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'step_key' => $step['step_key'],
                ],
                [
                    'step_name' => $step['step_name'],
                    'completed' => false,
                    'order' => $step['order'],
                ]
            );
        }
    }

    /**
     * Get next uncompleted step for user.
     */
    public static function getNextStep(User $user): ?self
    {
        return self::where('user_id', $user->id)
            ->where('completed', false)
            ->orderBy('order')
            ->first();
    }

    /**
     * Get completion percentage for user.
     */
    public static function getCompletionPercentage(User $user): int
    {
        $total = self::where('user_id', $user->id)->count();
        if ($total === 0) {
            return 0;
        }

        $completed = self::where('user_id', $user->id)
            ->where('completed', true)
            ->count();

        return (int) round(($completed / $total) * 100);
    }

    /**
     * Check if all steps are completed.
     */
    public static function isComplete(User $user): bool
    {
        return self::where('user_id', $user->id)
            ->where('completed', false)
            ->doesntExist();
    }

    /**
     * Scope to filter completed steps.
     */
    public function scopeCompleted($query)
    {
        return $query->where('completed', true);
    }

    /**
     * Scope to filter pending steps.
     */
    public function scopePending($query)
    {
        return $query->where('completed', false);
    }
}
