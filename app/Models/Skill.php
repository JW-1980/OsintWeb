<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * Skill Model
 *
 * Represents a skill that can be triggered by keywords in prompts.
 *
 * @property int $id
 * @property string $uuid
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property array $keywords
 * @property array|null $aliases
 * @property string|null $category
 * @property string|null $icon
 * @property array|null $capabilities
 * @property array|null $required_inputs
 * @property array|null $output_format
 * @property string|null $system_prompt
 * @property string|null $instructions
 * @property array|null $example_prompts
 * @property array|null $tools
 * @property int $priority
 * @property float $match_threshold
 * @property bool $is_active
 * @property bool $requires_auth
 * @property string|null $permission_required
 * @property int $usage_count
 */
class Skill extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'name',
        'slug',
        'description',
        'keywords',
        'aliases',
        'category',
        'icon',
        'capabilities',
        'required_inputs',
        'output_format',
        'system_prompt',
        'instructions',
        'example_prompts',
        'tools',
        'priority',
        'match_threshold',
        'is_active',
        'requires_auth',
        'permission_required',
        'usage_count',
    ];

    protected $casts = [
        'keywords' => 'array',
        'aliases' => 'array',
        'capabilities' => 'array',
        'required_inputs' => 'array',
        'output_format' => 'array',
        'example_prompts' => 'array',
        'tools' => 'array',
        'priority' => 'integer',
        'match_threshold' => 'float',
        'is_active' => 'boolean',
        'requires_auth' => 'boolean',
        'usage_count' => 'integer',
    ];

    // Skill categories
    public const CATEGORY_OSINT = 'osint';
    public const CATEGORY_ANALYSIS = 'analysis';
    public const CATEGORY_VERIFICATION = 'verification';
    public const CATEGORY_GEOLOCATION = 'geolocation';
    public const CATEGORY_MEDIA = 'media';
    public const CATEGORY_RESEARCH = 'research';
    public const CATEGORY_TRANSLATION = 'translation';
    public const CATEGORY_MILITARY = 'military';

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($skill) {
            if (empty($skill->uuid)) {
                $skill->uuid = Str::uuid();
            }
            if (empty($skill->slug)) {
                $skill->slug = Str::slug($skill->name);
            }
        });
    }

    /**
     * Get agents that have this skill.
     */
    public function agents(): BelongsToMany
    {
        return $this->belongsToMany(Agent::class)
            ->withPivot(['proficiency', 'is_primary'])
            ->withTimestamps();
    }

    /**
     * Get skills this skill depends on.
     */
    public function dependencies(): BelongsToMany
    {
        return $this->belongsToMany(
            self::class,
            'skill_dependencies',
            'skill_id',
            'depends_on_skill_id'
        )->withPivot('dependency_type')
            ->withTimestamps();
    }

    /**
     * Get skills that depend on this skill.
     */
    public function dependents(): BelongsToMany
    {
        return $this->belongsToMany(
            self::class,
            'skill_dependencies',
            'depends_on_skill_id',
            'skill_id'
        )->withPivot('dependency_type')
            ->withTimestamps();
    }

    /**
     * Get trigger logs for this skill.
     */
    public function triggers(): HasMany
    {
        return $this->hasMany(SkillTrigger::class);
    }

    /**
     * Get user preferences for this skill.
     */
    public function userPreferences(): HasMany
    {
        return $this->hasMany(UserSkillPreference::class);
    }

    /**
     * Get all trigger keywords including aliases.
     */
    public function getAllKeywords(): array
    {
        $keywords = $this->keywords ?? [];
        $aliases = $this->aliases ?? [];

        // Add skill name and slug as implicit keywords
        $keywords[] = strtolower($this->name);
        $keywords[] = str_replace('-', ' ', $this->slug);

        // Add individual words from name
        $nameWords = explode(' ', strtolower($this->name));
        $keywords = array_merge($keywords, $nameWords);

        // Add aliases
        foreach ($aliases as $alias) {
            $keywords[] = strtolower($alias);
            $aliasWords = explode(' ', strtolower($alias));
            $keywords = array_merge($keywords, $aliasWords);
        }

        return array_unique(array_filter(array_map('trim', $keywords)));
    }

    /**
     * Calculate match score for given text.
     */
    public function calculateMatchScore(string $text): float
    {
        $text = strtolower($text);
        $keywords = $this->getAllKeywords();
        $matchedKeywords = [];
        $totalScore = 0;

        foreach ($keywords as $keyword) {
            $keyword = strtolower(trim($keyword));
            if (empty($keyword)) {
                continue;
            }

            // Exact word match
            if (preg_match('/\b' . preg_quote($keyword, '/') . '\b/i', $text)) {
                $matchedKeywords[] = $keyword;
                // Longer keywords get higher scores
                $score = min(1.0, strlen($keyword) / 10);
                $totalScore += $score;
            }
        }

        if (empty($matchedKeywords)) {
            return 0.0;
        }

        // Normalize score based on number of keywords matched
        $normalizedScore = min(1.0, $totalScore / max(1, count($keywords) * 0.3));

        // Boost score if multiple keywords matched
        if (count($matchedKeywords) > 1) {
            $normalizedScore = min(1.0, $normalizedScore * (1 + count($matchedKeywords) * 0.1));
        }

        return round($normalizedScore, 3);
    }

    /**
     * Check if this skill matches the given text.
     */
    public function matches(string $text): bool
    {
        return $this->calculateMatchScore($text) >= $this->match_threshold;
    }

    /**
     * Get matched keywords from text.
     */
    public function getMatchedKeywords(string $text): array
    {
        $text = strtolower($text);
        $keywords = $this->getAllKeywords();
        $matched = [];

        foreach ($keywords as $keyword) {
            $keyword = strtolower(trim($keyword));
            if (!empty($keyword) && preg_match('/\b' . preg_quote($keyword, '/') . '\b/i', $text)) {
                $matched[] = $keyword;
            }
        }

        return array_unique($matched);
    }

    /**
     * Increment usage count.
     */
    public function recordUsage(): void
    {
        $this->increment('usage_count');
    }

    /**
     * Check if user can use this skill.
     */
    public function canBeUsedBy(?User $user): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if (!$this->requires_auth) {
            return true;
        }

        if (!$user) {
            return false;
        }

        if (!$this->permission_required) {
            return true;
        }

        return $user->hasPermission($this->permission_required);
    }

    /**
     * Scope to filter active skills.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by category.
     */
    public function scopeCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope to order by priority.
     */
    public function scopeByPriority($query)
    {
        return $query->orderBy('priority', 'desc');
    }

    /**
     * Get available categories.
     */
    public static function getCategories(): array
    {
        return [
            self::CATEGORY_OSINT => 'OSINT',
            self::CATEGORY_ANALYSIS => 'Analysis',
            self::CATEGORY_VERIFICATION => 'Verification',
            self::CATEGORY_GEOLOCATION => 'Geolocation',
            self::CATEGORY_MEDIA => 'Media',
            self::CATEGORY_RESEARCH => 'Research',
            self::CATEGORY_TRANSLATION => 'Translation',
            self::CATEGORY_MILITARY => 'Military',
        ];
    }
}
