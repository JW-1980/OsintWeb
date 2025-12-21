<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * Agent Model
 *
 * Represents an AI agent that can execute skills.
 *
 * @property int $id
 * @property string $uuid
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string $type
 * @property string|null $system_prompt
 * @property array|null $personality
 * @property array|null $capabilities
 * @property array|null $limitations
 * @property string|null $model
 * @property float $temperature
 * @property int|null $max_tokens
 * @property array|null $tools
 * @property array|null $memory_config
 * @property bool $is_active
 * @property bool $is_public
 * @property int|null $created_by
 * @property int $usage_count
 * @property float|null $avg_response_time
 */
class Agent extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'name',
        'slug',
        'description',
        'type',
        'system_prompt',
        'personality',
        'capabilities',
        'limitations',
        'model',
        'temperature',
        'max_tokens',
        'tools',
        'memory_config',
        'is_active',
        'is_public',
        'created_by',
        'usage_count',
        'avg_response_time',
    ];

    protected $casts = [
        'personality' => 'array',
        'capabilities' => 'array',
        'limitations' => 'array',
        'tools' => 'array',
        'memory_config' => 'array',
        'temperature' => 'float',
        'max_tokens' => 'integer',
        'is_active' => 'boolean',
        'is_public' => 'boolean',
        'usage_count' => 'integer',
        'avg_response_time' => 'float',
    ];

    public const TYPE_SKILL_BASED = 'skill-based';
    public const TYPE_WORKFLOW = 'workflow';
    public const TYPE_COMPOSITE = 'composite';
    public const TYPE_SPECIALIST = 'specialist';

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($agent) {
            if (empty($agent->uuid)) {
                $agent->uuid = Str::uuid();
            }
            if (empty($agent->slug)) {
                $agent->slug = Str::slug($agent->name);
            }
        });
    }

    /**
     * Get the user who created this agent.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get skills this agent has.
     */
    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class)
            ->withPivot(['proficiency', 'is_primary'])
            ->withTimestamps();
    }

    /**
     * Get primary skills.
     */
    public function primarySkills(): BelongsToMany
    {
        return $this->skills()->wherePivot('is_primary', true);
    }

    /**
     * Get executions of this agent.
     */
    public function executions(): HasMany
    {
        return $this->hasMany(AgentExecution::class);
    }

    /**
     * Attach a skill to this agent.
     */
    public function addSkill(Skill $skill, int $proficiency = 100, bool $isPrimary = false): void
    {
        $this->skills()->syncWithoutDetaching([
            $skill->id => [
                'proficiency' => $proficiency,
                'is_primary' => $isPrimary,
            ],
        ]);
    }

    /**
     * Remove a skill from this agent.
     */
    public function removeSkill(Skill $skill): void
    {
        $this->skills()->detach($skill->id);
    }

    /**
     * Build system prompt including skill instructions.
     */
    public function buildSystemPrompt(): string
    {
        $prompt = $this->system_prompt ?? '';

        // Add personality traits
        if (!empty($this->personality)) {
            $traits = implode(', ', $this->personality);
            $prompt .= "\n\nPersonality: {$traits}";
        }

        // Add capabilities
        if (!empty($this->capabilities)) {
            $caps = implode("\n- ", $this->capabilities);
            $prompt .= "\n\nCapabilities:\n- {$caps}";
        }

        // Add limitations
        if (!empty($this->limitations)) {
            $limits = implode("\n- ", $this->limitations);
            $prompt .= "\n\nLimitations:\n- {$limits}";
        }

        // Add skill instructions
        $skills = $this->skills()->active()->get();
        if ($skills->isNotEmpty()) {
            $prompt .= "\n\n## Available Skills\n";
            foreach ($skills as $skill) {
                $prompt .= "\n### {$skill->name}\n";
                if ($skill->instructions) {
                    $prompt .= "{$skill->instructions}\n";
                }
                if ($skill->system_prompt) {
                    $prompt .= "{$skill->system_prompt}\n";
                }
            }
        }

        return trim($prompt);
    }

    /**
     * Find matching skills for given prompt.
     */
    public function findMatchingSkills(string $prompt): array
    {
        $skills = $this->skills()->active()->byPriority()->get();
        $matches = [];

        foreach ($skills as $skill) {
            $score = $skill->calculateMatchScore($prompt);
            if ($score >= $skill->match_threshold) {
                $matches[] = [
                    'skill' => $skill,
                    'score' => $score,
                    'matched_keywords' => $skill->getMatchedKeywords($prompt),
                ];
            }
        }

        // Sort by score descending
        usort($matches, fn ($a, $b) => $b['score'] <=> $a['score']);

        return $matches;
    }

    /**
     * Record usage and update avg response time.
     */
    public function recordExecution(float $responseTime): void
    {
        $currentAvg = $this->avg_response_time ?? 0;
        $count = $this->usage_count;

        // Calculate new average
        $newAvg = (($currentAvg * $count) + $responseTime) / ($count + 1);

        $this->update([
            'usage_count' => $count + 1,
            'avg_response_time' => round($newAvg, 2),
        ]);
    }

    /**
     * Create agent from skill.
     */
    public static function createFromSkill(Skill $skill, ?User $creator = null): self
    {
        $agent = self::create([
            'name' => "{$skill->name} Agent",
            'slug' => "{$skill->slug}-agent",
            'description' => "Specialized agent for {$skill->name}",
            'type' => self::TYPE_SPECIALIST,
            'system_prompt' => $skill->system_prompt,
            'capabilities' => $skill->capabilities,
            'tools' => $skill->tools,
            'is_active' => true,
            'is_public' => false,
            'created_by' => $creator?->id,
        ]);

        $agent->addSkill($skill, 100, true);

        return $agent;
    }

    /**
     * Create agent from multiple skills.
     */
    public static function createFromSkills(
        array $skills,
        string $name,
        ?string $description = null,
        ?User $creator = null
    ): self {
        $agent = self::create([
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => $description,
            'type' => self::TYPE_COMPOSITE,
            'is_active' => true,
            'is_public' => false,
            'created_by' => $creator?->id,
        ]);

        foreach ($skills as $index => $skill) {
            $agent->addSkill($skill, 100, $index === 0);
        }

        return $agent;
    }

    /**
     * Scope to filter active agents.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter public agents.
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope to filter by type.
     */
    public function scopeType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Get agent types.
     */
    public static function getTypes(): array
    {
        return [
            self::TYPE_SKILL_BASED => 'Skill-Based',
            self::TYPE_WORKFLOW => 'Workflow',
            self::TYPE_COMPOSITE => 'Composite',
            self::TYPE_SPECIALIST => 'Specialist',
        ];
    }
}
