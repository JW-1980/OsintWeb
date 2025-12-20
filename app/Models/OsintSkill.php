<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class OsintSkill extends Model
{
    use SoftDeletes;

    public const DIFFICULTY_BEGINNER = 'beginner';
    public const DIFFICULTY_INTERMEDIATE = 'intermediate';
    public const DIFFICULTY_ADVANCED = 'advanced';
    public const DIFFICULTY_EXPERT = 'expert';

    protected $fillable = [
        'uuid',
        'category_id',
        'name',
        'slug',
        'description',
        'difficulty',
        'data',
        'icon',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'data' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->name);
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(OsintSkillCategory::class, 'category_id');
    }

    public function userAssessments(): HasMany
    {
        return $this->hasMany(UserSkillAssessment::class, 'skill_id');
    }

    public function getTools(): array
    {
        return $this->data['tools'] ?? [];
    }

    public function getResources(): array
    {
        return $this->data['resources'] ?? $this->data['sources'] ?? [];
    }

    public function getBestPractices(): array
    {
        return $this->data['best_practices'] ?? [];
    }

    public function getMethodology(): array
    {
        return $this->data['methodology'] ?? [];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByDifficulty($query, string $level)
    {
        return $query->where('difficulty', $level);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

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
