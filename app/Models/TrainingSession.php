<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * TrainingSession Model
 *
 * Represents a user's training session within a training environment.
 * Tracks progress, scores, actions, and achievements during training.
 *
 * @property int $id
 * @property string $uuid
 * @property int $environment_id
 * @property int $user_id
 * @property string $status (active|paused|completed|abandoned|expired)
 * @property \Carbon\Carbon|null $started_at
 * @property \Carbon\Carbon|null $paused_at
 * @property \Carbon\Carbon|null $completed_at
 * @property int $time_spent_seconds Total time spent in session
 * @property float $score Score achieved in the session
 * @property float $max_score Maximum possible score
 * @property array|null $objectives_completed Which objectives have been completed
 * @property array|null $actions_log Log of user actions during the session
 * @property array|null $feedback Post-session feedback and results
 * @property string $sandbox_data_prefix Prefix for sandbox data isolation
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property-read TrainingEnvironment $environment
 * @property-read User $user
 * @property-read \Illuminate\Database\Eloquent\Collection|TrainingAchievement[] $achievements
 * @property-read int $achievements_count
 * @property-read float $score_percentage
 * @property-read int $objectives_completed_count
 * @property-read string $formatted_time_spent
 * @property-read bool $is_active
 * @property-read bool $is_expired
 */
class TrainingSession extends Model
{
    use HasFactory;

    /**
     * Session statuses
     */
    public const STATUS_ACTIVE = 'active';
    public const STATUS_PAUSED = 'paused';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_ABANDONED = 'abandoned';
    public const STATUS_EXPIRED = 'expired';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'uuid',
        'environment_id',
        'user_id',
        'status',
        'started_at',
        'paused_at',
        'completed_at',
        'time_spent_seconds',
        'score',
        'max_score',
        'objectives_completed',
        'actions_log',
        'feedback',
        'sandbox_data_prefix',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'started_at' => 'datetime',
        'paused_at' => 'datetime',
        'completed_at' => 'datetime',
        'time_spent_seconds' => 'integer',
        'score' => 'decimal:2',
        'max_score' => 'decimal:2',
        'objectives_completed' => 'array',
        'actions_log' => 'array',
        'feedback' => 'array',
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

            if (empty($model->sandbox_data_prefix)) {
                $model->sandbox_data_prefix = 'sandbox_' . Str::random(16);
            }

            if (empty($model->started_at)) {
                $model->started_at = now();
            }
        });
    }

    /**
     * Get the training environment.
     */
    public function environment(): BelongsTo
    {
        return $this->belongsTo(TrainingEnvironment::class, 'environment_id');
    }

    /**
     * Get the user participating in this session.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get achievements earned in this session.
     */
    public function achievements(): HasMany
    {
        return $this->hasMany(TrainingAchievement::class);
    }

    /**
     * Scope to filter active sessions.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope to filter paused sessions.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePaused($query)
    {
        return $query->where('status', self::STATUS_PAUSED);
    }

    /**
     * Scope to filter completed sessions.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope to filter sessions by user.
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
     * Scope to filter sessions by environment.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $environmentId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForEnvironment($query, int $environmentId)
    {
        return $query->where('environment_id', $environmentId);
    }

    /**
     * Scope to filter in-progress sessions (active or paused).
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInProgress($query)
    {
        return $query->whereIn('status', [self::STATUS_ACTIVE, self::STATUS_PAUSED]);
    }

    /**
     * Get the score as a percentage.
     */
    public function getScorePercentageAttribute(): float
    {
        if ($this->max_score <= 0) {
            return 0.0;
        }

        return round(($this->score / $this->max_score) * 100, 2);
    }

    /**
     * Get the count of completed objectives.
     */
    public function getObjectivesCompletedCountAttribute(): int
    {
        if (!$this->objectives_completed) {
            return 0;
        }

        return count(array_filter($this->objectives_completed, fn($obj) => $obj['completed'] ?? false));
    }

    /**
     * Get the formatted time spent (HH:MM:SS).
     */
    public function getFormattedTimeSpentAttribute(): string
    {
        $hours = floor($this->time_spent_seconds / 3600);
        $minutes = floor(($this->time_spent_seconds % 3600) / 60);
        $seconds = $this->time_spent_seconds % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }

    /**
     * Check if session is active.
     */
    public function getIsActiveAttribute(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check if session has expired based on time limit.
     */
    public function getIsExpiredAttribute(): bool
    {
        if ($this->status === self::STATUS_EXPIRED) {
            return true;
        }

        if (!$this->environment || !$this->environment->hasTimeLimit()) {
            return false;
        }

        $timeLimitSeconds = $this->environment->time_limit_minutes * 60;
        return $this->time_spent_seconds >= $timeLimitSeconds;
    }

    /**
     * Pause the session.
     */
    public function pause(): self
    {
        if ($this->status !== self::STATUS_ACTIVE) {
            return $this;
        }

        $this->updateTimeSpent();
        $this->status = self::STATUS_PAUSED;
        $this->paused_at = now();
        $this->save();

        return $this;
    }

    /**
     * Resume the session.
     */
    public function resume(): self
    {
        if ($this->status !== self::STATUS_PAUSED) {
            return $this;
        }

        $this->status = self::STATUS_ACTIVE;
        $this->started_at = now();
        $this->paused_at = null;
        $this->save();

        return $this;
    }

    /**
     * Complete the session.
     */
    public function complete(): self
    {
        if (!in_array($this->status, [self::STATUS_ACTIVE, self::STATUS_PAUSED])) {
            return $this;
        }

        $this->updateTimeSpent();
        $this->status = self::STATUS_COMPLETED;
        $this->completed_at = now();
        $this->save();

        return $this;
    }

    /**
     * Abandon the session.
     */
    public function abandon(): self
    {
        if (!in_array($this->status, [self::STATUS_ACTIVE, self::STATUS_PAUSED])) {
            return $this;
        }

        $this->updateTimeSpent();
        $this->status = self::STATUS_ABANDONED;
        $this->save();

        return $this;
    }

    /**
     * Mark the session as expired.
     */
    public function markExpired(): self
    {
        if (!in_array($this->status, [self::STATUS_ACTIVE, self::STATUS_PAUSED])) {
            return $this;
        }

        $this->updateTimeSpent();
        $this->status = self::STATUS_EXPIRED;
        $this->save();

        return $this;
    }

    /**
     * Update the time spent in the session.
     */
    public function updateTimeSpent(): self
    {
        if ($this->status === self::STATUS_ACTIVE && $this->started_at) {
            $additionalSeconds = (int) now()->diffInSeconds($this->started_at);
            $this->time_spent_seconds += $additionalSeconds;
        }

        return $this;
    }

    /**
     * Log an action in the session.
     *
     * @param string $action
     * @param array $data
     */
    public function logAction(string $action, array $data = []): self
    {
        $log = $this->actions_log ?? [];
        $log[] = [
            'action' => $action,
            'data' => $data,
            'timestamp' => now()->toIso8601String(),
            'time_elapsed_seconds' => $this->time_spent_seconds + (int) now()->diffInSeconds($this->started_at ?? now()),
        ];

        $this->actions_log = $log;
        $this->save();

        return $this;
    }

    /**
     * Mark an objective as completed.
     *
     * @param string $objectiveId
     * @param array $data
     */
    public function completeObjective(string $objectiveId, array $data = []): self
    {
        $objectives = $this->objectives_completed ?? [];
        $objectives[$objectiveId] = [
            'completed' => true,
            'completed_at' => now()->toIso8601String(),
            'data' => $data,
        ];

        $this->objectives_completed = $objectives;
        $this->save();

        return $this;
    }

    /**
     * Check if an objective is completed.
     *
     * @param string $objectiveId
     */
    public function isObjectiveCompleted(string $objectiveId): bool
    {
        $objectives = $this->objectives_completed ?? [];
        return isset($objectives[$objectiveId]) && ($objectives[$objectiveId]['completed'] ?? false);
    }

    /**
     * Update the session score.
     *
     * @param float $score
     */
    public function updateScore(float $score): self
    {
        $this->score = $score;
        $this->save();

        return $this;
    }

    /**
     * Add points to the session score.
     *
     * @param float $points
     */
    public function addPoints(float $points): self
    {
        $this->score = min($this->score + $points, $this->max_score);
        $this->save();

        return $this;
    }

    /**
     * Get remaining time in seconds (if time limit exists).
     */
    public function getRemainingTimeSeconds(): ?int
    {
        if (!$this->environment || !$this->environment->hasTimeLimit()) {
            return null;
        }

        $timeLimitSeconds = $this->environment->time_limit_minutes * 60;
        $currentTimeSpent = $this->time_spent_seconds;

        if ($this->status === self::STATUS_ACTIVE && $this->started_at) {
            $currentTimeSpent += (int) now()->diffInSeconds($this->started_at);
        }

        return max(0, $timeLimitSeconds - $currentTimeSpent);
    }

    /**
     * Get all available session statuses.
     *
     * @return array<string, string>
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_PAUSED => 'Paused',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_ABANDONED => 'Abandoned',
            self::STATUS_EXPIRED => 'Expired',
        ];
    }
}
