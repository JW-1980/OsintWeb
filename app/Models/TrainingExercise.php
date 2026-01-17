<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * TrainingExercise Model
 *
 * Training exercises for OSINT skill development.
 *
 * Database Schema:
 * @property int $id Primary key
 * @property string $uuid Unique identifier for external reference
 * @property int|null $skill_id Foreign key to osint_skills table
 * @property string $title Exercise title
 * @property string|null $description Exercise description
 * @property string $difficulty Difficulty level (beginner, intermediate, advanced, expert)
 * @property string $type Exercise type (geolocation, verification, analysis, identification)
 * @property array|null $scenario Exercise scenario data including images, context (JSON)
 * @property array|null $correct_answer The correct answer(s) for validation (JSON)
 * @property array|null $hints Available hints for the exercise (JSON)
 * @property int|null $time_limit_minutes Time limit in minutes
 * @property int $points Points awarded for completion
 * @property bool $is_active Whether exercise is currently active
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at Soft delete timestamp
 *
 * @property-read OsintSkill|null $skill
 * @property-read \Illuminate\Database\Eloquent\Collection|ExerciseAttempt[] $attempts
 */
class TrainingExercise extends Model
{
    use SoftDeletes;

    public const TYPE_GEOLOCATION = 'geolocation';
    public const TYPE_VERIFICATION = 'verification';
    public const TYPE_ANALYSIS = 'analysis';
    public const TYPE_IDENTIFICATION = 'identification';

    protected $fillable = [
        'uuid',
        'skill_id',
        'title',
        'description',
        'difficulty',
        'type',
        'scenario',
        'correct_answer',
        'hints',
        'time_limit_minutes',
        'points',
        'is_active',
    ];

    protected $casts = [
        'scenario' => 'array',
        'correct_answer' => 'array',
        'hints' => 'array',
        'time_limit_minutes' => 'integer',
        'points' => 'integer',
        'is_active' => 'boolean',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function skill(): BelongsTo
    {
        return $this->belongsTo(OsintSkill::class, 'skill_id');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(ExerciseAttempt::class, 'exercise_id');
    }

    public function checkAnswer(array $userAnswer): bool
    {
        // Simple equality check - can be overridden for complex answers
        return json_encode($userAnswer) === json_encode($this->correct_answer);
    }

    public function calculateScore(array $userAnswer, int $hintsUsed = 0, ?int $timeTaken = null): float
    {
        $baseScore = $this->checkAnswer($userAnswer) ? $this->points : 0;

        // Reduce score for hints used
        $hintPenalty = $hintsUsed * ($this->points * 0.1);
        $baseScore = max(0, $baseScore - $hintPenalty);

        // Bonus for fast completion if time limit exists
        if ($timeTaken && $this->time_limit_minutes) {
            $timeLimitSeconds = $this->time_limit_minutes * 60;
            if ($timeTaken < $timeLimitSeconds * 0.5) {
                $baseScore *= 1.1; // 10% bonus for completing in less than half the time
            }
        }

        return round($baseScore, 2);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByDifficulty($query, string $difficulty)
    {
        return $query->where('difficulty', $difficulty);
    }

    public static function getTypes(): array
    {
        return [
            self::TYPE_GEOLOCATION => 'Geolocation',
            self::TYPE_VERIFICATION => 'Verification',
            self::TYPE_ANALYSIS => 'Analysis',
            self::TYPE_IDENTIFICATION => 'Identification',
        ];
    }
}
