<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * Skill Agent Execution Model
 *
 * Represents an execution of a skill-based agent.
 *
 * @property int $id
 * @property string $uuid
 * @property int $agent_id
 * @property int|null $user_id
 * @property string $input_prompt
 * @property array|null $matched_skills
 * @property array|null $context
 * @property string|null $output
 * @property array|null $output_metadata
 * @property string $status
 * @property string|null $error_message
 * @property int|null $tokens_used
 * @property float|null $execution_time
 * @property float|null $cost
 * @property array|null $steps
 * @property float|null $confidence_score
 * @property \Carbon\Carbon|null $started_at
 * @property \Carbon\Carbon|null $completed_at
 */
class SkillAgentExecution extends Model
{
    protected $table = 'agent_executions';

    protected $fillable = [
        'uuid',
        'agent_id',
        'user_id',
        'input_prompt',
        'matched_skills',
        'context',
        'output',
        'output_metadata',
        'status',
        'error_message',
        'tokens_used',
        'execution_time',
        'cost',
        'steps',
        'confidence_score',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'matched_skills' => 'array',
        'context' => 'array',
        'output_metadata' => 'array',
        'steps' => 'array',
        'tokens_used' => 'integer',
        'execution_time' => 'float',
        'cost' => 'float',
        'confidence_score' => 'float',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_RUNNING = 'running';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_CANCELLED = 'cancelled';

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($execution) {
            if (empty($execution->uuid)) {
                $execution->uuid = Str::uuid();
            }
        });
    }

    /**
     * Get the agent for this execution.
     */
    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    /**
     * Get the user who initiated this execution.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get skill triggers for this execution.
     */
    public function skillTriggers(): HasMany
    {
        return $this->hasMany(SkillTrigger::class, 'agent_execution_id');
    }

    /**
     * Start the execution.
     */
    public function start(): void
    {
        $this->update([
            'status' => self::STATUS_RUNNING,
            'started_at' => now(),
        ]);
    }

    /**
     * Complete the execution.
     */
    public function complete(
        string $output,
        ?array $outputMetadata = null,
        ?int $tokensUsed = null,
        ?float $cost = null,
        ?float $confidenceScore = null
    ): void {
        $executionTime = $this->started_at
            ? now()->diffInMilliseconds($this->started_at) / 1000
            : null;

        $this->update([
            'status' => self::STATUS_COMPLETED,
            'output' => $output,
            'output_metadata' => $outputMetadata,
            'tokens_used' => $tokensUsed,
            'execution_time' => $executionTime,
            'cost' => $cost,
            'confidence_score' => $confidenceScore,
            'completed_at' => now(),
        ]);

        // Update agent stats
        if ($executionTime) {
            $this->agent->recordExecution($executionTime);
        }
    }

    /**
     * Fail the execution.
     */
    public function fail(string $errorMessage): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'error_message' => $errorMessage,
            'completed_at' => now(),
        ]);
    }

    /**
     * Cancel the execution.
     */
    public function cancel(): void
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
            'completed_at' => now(),
        ]);
    }

    /**
     * Add execution step.
     */
    public function addStep(string $step, ?array $data = null): void
    {
        $steps = $this->steps ?? [];
        $steps[] = [
            'step' => $step,
            'data' => $data,
            'timestamp' => now()->toIso8601String(),
        ];
        $this->update(['steps' => $steps]);
    }

    /**
     * Check if execution is in progress.
     */
    public function isInProgress(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_RUNNING], true);
    }

    /**
     * Scope to filter by status.
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter completed executions.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }
}
