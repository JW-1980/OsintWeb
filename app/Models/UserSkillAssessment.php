<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSkillAssessment extends Model
{
    public const PROFICIENCY_NONE = 'none';
    public const PROFICIENCY_BEGINNER = 'beginner';
    public const PROFICIENCY_INTERMEDIATE = 'intermediate';
    public const PROFICIENCY_ADVANCED = 'advanced';
    public const PROFICIENCY_EXPERT = 'expert';

    protected $fillable = [
        'user_id',
        'skill_id',
        'proficiency_level',
        'practice_count',
        'last_practiced_at',
        'notes',
        'is_certified',
        'certified_at',
    ];

    protected $casts = [
        'notes' => 'array',
        'practice_count' => 'integer',
        'is_certified' => 'boolean',
        'last_practiced_at' => 'datetime',
        'certified_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function skill(): BelongsTo
    {
        return $this->belongsTo(OsintSkill::class, 'skill_id');
    }

    public function incrementPractice(): void
    {
        $this->update([
            'practice_count' => $this->practice_count + 1,
            'last_practiced_at' => now(),
        ]);
    }

    public function certify(): void
    {
        $this->update([
            'is_certified' => true,
            'certified_at' => now(),
        ]);
    }

    public function upgradeProficiency(): void
    {
        $levels = array_keys(self::getProficiencyLevels());
        $currentIndex = array_search($this->proficiency_level, $levels);

        if ($currentIndex !== false && $currentIndex < count($levels) - 1) {
            $this->update([
                'proficiency_level' => $levels[$currentIndex + 1],
            ]);
        }
    }

    public function scopeByProficiency($query, string $level)
    {
        return $query->where('proficiency_level', $level);
    }

    public function scopeCertified($query)
    {
        return $query->where('is_certified', true);
    }

    public static function getProficiencyLevels(): array
    {
        return [
            self::PROFICIENCY_NONE => 'None',
            self::PROFICIENCY_BEGINNER => 'Beginner',
            self::PROFICIENCY_INTERMEDIATE => 'Intermediate',
            self::PROFICIENCY_ADVANCED => 'Advanced',
            self::PROFICIENCY_EXPERT => 'Expert',
        ];
    }
}
