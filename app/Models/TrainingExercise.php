<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

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
