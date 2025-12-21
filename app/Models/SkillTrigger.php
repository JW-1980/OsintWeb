<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Skill Trigger Model
 *
 * Logs when a skill is triggered by keyword matching.
 *
 * @property int $id
 * @property int $skill_id
 * @property int|null $agent_execution_id
 * @property int|null $user_id
 * @property string $trigger_text
 * @property array $matched_keywords
 * @property float $match_score
 * @property bool $was_executed
 * @property string|null $trigger_source
 * @property \Carbon\Carbon $created_at
 */
class SkillTrigger extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'skill_id',
        'agent_execution_id',
        'user_id',
        'trigger_text',
        'matched_keywords',
        'match_score',
        'was_executed',
        'trigger_source',
        'created_at',
    ];

    protected $casts = [
        'matched_keywords' => 'array',
        'match_score' => 'float',
        'was_executed' => 'boolean',
        'created_at' => 'datetime',
    ];

    /**
     * Get the skill that was triggered.
     */
    public function skill(): BelongsTo
    {
        return $this->belongsTo(Skill::class);
    }

    /**
     * Get the agent execution that triggered this.
     */
    public function execution(): BelongsTo
    {
        return $this->belongsTo(AgentExecution::class, 'agent_execution_id');
    }

    /**
     * Get the user who triggered this.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Log a skill trigger.
     */
    public static function log(
        Skill $skill,
        string $triggerText,
        array $matchedKeywords,
        float $matchScore,
        ?AgentExecution $execution = null,
        ?User $user = null,
        bool $wasExecuted = true,
        ?string $source = null
    ): self {
        return self::create([
            'skill_id' => $skill->id,
            'agent_execution_id' => $execution?->id,
            'user_id' => $user?->id,
            'trigger_text' => substr($triggerText, 0, 1000),
            'matched_keywords' => $matchedKeywords,
            'match_score' => $matchScore,
            'was_executed' => $wasExecuted,
            'trigger_source' => $source,
            'created_at' => now(),
        ]);
    }
}
