<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * TrainingEnvironment Model
 *
 * Represents a training/simulation environment for OSINT skill development.
 * Environments can be sandboxes, tutorials, exercises, or certifications.
 *
 * @property int $id
 * @property string $uuid
 * @property string $name
 * @property string|null $description
 * @property string $environment_type (sandbox|tutorial|exercise|certification)
 * @property array|null $base_scenario Initial data state for the scenario
 * @property array|null $objectives Array of objectives to complete
 * @property array|null $success_criteria Criteria for successful completion
 * @property int|null $time_limit_minutes Time limit in minutes (null = unlimited)
 * @property string $difficulty (beginner|intermediate|advanced|expert)
 * @property array|null $skills_covered Array of skill IDs or slugs covered
 * @property bool $is_active Whether environment is active
 * @property bool $is_public Whether environment is publicly available
 * @property int|null $created_by User ID who created the environment
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 *
 * @property-read User|null $creator
 * @property-read \Illuminate\Database\Eloquent\Collection|TrainingSession[] $sessions
 * @property-read int $sessions_count
 * @property-read int $active_sessions_count
 * @property-read int $completed_sessions_count
 * @property-read float $average_score
 * @property-read float $completion_rate
 */
class TrainingEnvironment extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Environment types
     */
    public const TYPE_SANDBOX = 'sandbox';
    public const TYPE_TUTORIAL = 'tutorial';
    public const TYPE_EXERCISE = 'exercise';
    public const TYPE_CERTIFICATION = 'certification';

    /**
     * Difficulty levels
     */
    public const DIFFICULTY_BEGINNER = 'beginner';
    public const DIFFICULTY_INTERMEDIATE = 'intermediate';
    public const DIFFICULTY_ADVANCED = 'advanced';
    public const DIFFICULTY_EXPERT = 'expert';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'uuid',
        'name',
        'description',
        'environment_type',
        'base_scenario',
        'objectives',
        'success_criteria',
        'time_limit_minutes',
        'difficulty',
        'skills_covered',
        'is_active',
        'is_public',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'base_scenario' => 'array',
        'objectives' => 'array',
        'success_criteria' => 'array',
        'skills_covered' => 'array',
        'time_limit_minutes' => 'integer',
        'is_active' => 'boolean',
        'is_public' => 'boolean',
    ];

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $model): void {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the user who created this environment.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all training sessions for this environment.
     */
    public function sessions(): HasMany
    {
        return $this->hasMany(TrainingSession::class, 'environment_id');
    }

    /**
     * Get active training sessions.
     */
    public function activeSessions(): HasMany
    {
        return $this->sessions()->where('status', TrainingSession::STATUS_ACTIVE);
    }

    /**
     * Get completed training sessions.
     */
    public function completedSessions(): HasMany
    {
        return $this->sessions()->where('status', TrainingSession::STATUS_COMPLETED);
    }

    /**
     * Scope to filter active environments.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter public environments.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope to filter by environment type.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('environment_type', $type);
    }

    /**
     * Scope to filter by difficulty.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $difficulty
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfDifficulty($query, string $difficulty)
    {
        return $query->where('difficulty', $difficulty);
    }

    /**
     * Scope to filter sandboxes.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSandboxes($query)
    {
        return $query->where('environment_type', self::TYPE_SANDBOX);
    }

    /**
     * Scope to filter certifications.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCertifications($query)
    {
        return $query->where('environment_type', self::TYPE_CERTIFICATION);
    }

    /**
     * Get the count of objectives.
     */
    public function getObjectivesCountAttribute(): int
    {
        return count($this->objectives ?? []);
    }

    /**
     * Get the average score across all completed sessions.
     */
    public function getAverageScoreAttribute(): float
    {
        return (float) $this->completedSessions()->avg('score') ?? 0.0;
    }

    /**
     * Get the completion rate (completed sessions / total sessions).
     */
    public function getCompletionRateAttribute(): float
    {
        $total = $this->sessions()->count();
        if ($total === 0) {
            return 0.0;
        }

        $completed = $this->completedSessions()->count();
        return round(($completed / $total) * 100, 2);
    }

    /**
     * Check if environment has time limit.
     */
    public function hasTimeLimit(): bool
    {
        return $this->time_limit_minutes !== null && $this->time_limit_minutes > 0;
    }

    /**
     * Check if this is a certification environment.
     */
    public function isCertification(): bool
    {
        return $this->environment_type === self::TYPE_CERTIFICATION;
    }

    /**
     * Check if this is a sandbox environment.
     */
    public function isSandbox(): bool
    {
        return $this->environment_type === self::TYPE_SANDBOX;
    }

    /**
     * Get all available environment types.
     *
     * @return array<string, string>
     */
    public static function getEnvironmentTypes(): array
    {
        return [
            self::TYPE_SANDBOX => 'Sandbox',
            self::TYPE_TUTORIAL => 'Tutorial',
            self::TYPE_EXERCISE => 'Exercise',
            self::TYPE_CERTIFICATION => 'Certification',
        ];
    }

    /**
     * Get all available difficulty levels.
     *
     * @return array<string, string>
     */
    public static function getDifficultyLevels(): array
    {
        return [
            self::DIFFICULTY_BEGINNER => 'Beginner',
            self::DIFFICULTY_INTERMEDIATE => 'Intermediate',
            self::DIFFICULTY_ADVANCED => 'Advanced',
            self::DIFFICULTY_EXPERT => 'Expert',
        ];
    }
}
