<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * TrainingAchievement Model
 *
 * Represents an achievement earned during a training session.
 * These are session-specific achievements distinct from user-level achievements.
 *
 * @property int $id
 * @property int $training_session_id
 * @property string $achievement_type Achievement type (completed_exercise, passed_certification, etc.)
 * @property array|null $details Additional details about the achievement
 * @property \Carbon\Carbon $awarded_at When the achievement was awarded
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property-read TrainingSession $session
 * @property-read User $user (via session)
 * @property-read TrainingEnvironment $environment (via session)
 * @property-read string $display_name Human-readable achievement name
 * @property-read string $icon Achievement icon
 */
class TrainingAchievement extends Model
{
    use HasFactory;

    /**
     * Achievement types
     */
    public const TYPE_COMPLETED_EXERCISE = 'completed_exercise';
    public const TYPE_PASSED_CERTIFICATION = 'passed_certification';
    public const TYPE_HIGH_SCORE = 'high_score';
    public const TYPE_PERFECT_SCORE = 'perfect_score';
    public const TYPE_FAST_COMPLETION = 'fast_completion';
    public const TYPE_FIRST_ATTEMPT = 'first_attempt';
    public const TYPE_STREAK = 'streak';
    public const TYPE_SKILL_MASTERY = 'skill_mastery';
    public const TYPE_ALL_OBJECTIVES = 'all_objectives';
    public const TYPE_NO_HINTS_USED = 'no_hints_used';
    public const TYPE_EXPERT_LEVEL = 'expert_level';
    public const TYPE_COLLABORATION = 'collaboration';
    public const TYPE_CUSTOM = 'custom';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'training_session_id',
        'achievement_type',
        'details',
        'awarded_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'details' => 'array',
        'awarded_at' => 'datetime',
    ];

    /**
     * Get the training session this achievement belongs to.
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(TrainingSession::class, 'training_session_id');
    }

    /**
     * Scope to filter by achievement type.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('achievement_type', $type);
    }

    /**
     * Scope to filter achievements awarded after a certain date.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Carbon\Carbon $date
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAwardedAfter($query, $date)
    {
        return $query->where('awarded_at', '>=', $date);
    }

    /**
     * Scope to filter achievements for a specific user (via session).
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->whereHas('session', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        });
    }

    /**
     * Get human-readable display name for the achievement.
     */
    public function getDisplayNameAttribute(): string
    {
        return self::getAchievementTypes()[$this->achievement_type] ?? 'Unknown Achievement';
    }

    /**
     * Get the icon for this achievement type.
     */
    public function getIconAttribute(): string
    {
        return self::getAchievementIcons()[$this->achievement_type] ?? 'trophy';
    }

    /**
     * Get the color for this achievement type.
     */
    public function getColorAttribute(): string
    {
        return self::getAchievementColors()[$this->achievement_type] ?? '#FFD700';
    }

    /**
     * Get all available achievement types with display names.
     *
     * @return array<string, string>
     */
    public static function getAchievementTypes(): array
    {
        return [
            self::TYPE_COMPLETED_EXERCISE => 'Exercise Completed',
            self::TYPE_PASSED_CERTIFICATION => 'Certification Passed',
            self::TYPE_HIGH_SCORE => 'High Score',
            self::TYPE_PERFECT_SCORE => 'Perfect Score',
            self::TYPE_FAST_COMPLETION => 'Speed Demon',
            self::TYPE_FIRST_ATTEMPT => 'First Try Success',
            self::TYPE_STREAK => 'On a Streak',
            self::TYPE_SKILL_MASTERY => 'Skill Mastery',
            self::TYPE_ALL_OBJECTIVES => 'All Objectives Complete',
            self::TYPE_NO_HINTS_USED => 'No Hints Needed',
            self::TYPE_EXPERT_LEVEL => 'Expert Level',
            self::TYPE_COLLABORATION => 'Team Player',
            self::TYPE_CUSTOM => 'Special Achievement',
        ];
    }

    /**
     * Get icons for each achievement type.
     *
     * @return array<string, string>
     */
    public static function getAchievementIcons(): array
    {
        return [
            self::TYPE_COMPLETED_EXERCISE => 'check-circle',
            self::TYPE_PASSED_CERTIFICATION => 'award',
            self::TYPE_HIGH_SCORE => 'star',
            self::TYPE_PERFECT_SCORE => 'crown',
            self::TYPE_FAST_COMPLETION => 'zap',
            self::TYPE_FIRST_ATTEMPT => 'target',
            self::TYPE_STREAK => 'flame',
            self::TYPE_SKILL_MASTERY => 'graduation-cap',
            self::TYPE_ALL_OBJECTIVES => 'list-checks',
            self::TYPE_NO_HINTS_USED => 'brain',
            self::TYPE_EXPERT_LEVEL => 'medal',
            self::TYPE_COLLABORATION => 'users',
            self::TYPE_CUSTOM => 'trophy',
        ];
    }

    /**
     * Get colors for each achievement type.
     *
     * @return array<string, string>
     */
    public static function getAchievementColors(): array
    {
        return [
            self::TYPE_COMPLETED_EXERCISE => '#4CAF50',
            self::TYPE_PASSED_CERTIFICATION => '#9C27B0',
            self::TYPE_HIGH_SCORE => '#FFC107',
            self::TYPE_PERFECT_SCORE => '#FFD700',
            self::TYPE_FAST_COMPLETION => '#FF5722',
            self::TYPE_FIRST_ATTEMPT => '#2196F3',
            self::TYPE_STREAK => '#FF9800',
            self::TYPE_SKILL_MASTERY => '#673AB7',
            self::TYPE_ALL_OBJECTIVES => '#00BCD4',
            self::TYPE_NO_HINTS_USED => '#E91E63',
            self::TYPE_EXPERT_LEVEL => '#FFD700',
            self::TYPE_COLLABORATION => '#3F51B5',
            self::TYPE_CUSTOM => '#607D8B',
        ];
    }

    /**
     * Create an achievement for a session.
     *
     * @param TrainingSession $session
     * @param string $type
     * @param array $details
     */
    public static function award(TrainingSession $session, string $type, array $details = []): self
    {
        return static::create([
            'training_session_id' => $session->id,
            'achievement_type' => $type,
            'details' => $details,
            'awarded_at' => now(),
        ]);
    }
}
