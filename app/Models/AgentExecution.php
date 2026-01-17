<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * AgentExecution Model
 *
 * Tracks individual execution runs of intelligence agents.
 *
 * Database Schema:
 * @property int $id Primary key
 * @property int $agent_id Foreign key to intelligence_agents table
 * @property int|null $triggered_by Foreign key to users table (who initiated)
 * @property string $status Execution status (pending, running, completed, failed)
 * @property \Carbon\Carbon|null $started_at When execution started
 * @property \Carbon\Carbon|null $completed_at When execution completed
 * @property int $items_processed Number of items processed during execution
 * @property int $items_matched Number of items that matched criteria
 * @property array|null $results_summary Summary of execution results (JSON)
 * @property string|null $error_message Error message if execution failed
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property-read IntelligenceAgent $agent
 * @property-read User|null $triggeredBy
 * @property-read \Illuminate\Database\Eloquent\Collection|AgentDataPoint[] $dataPoints
 */
class AgentExecution extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_RUNNING = 'running';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'agent_id',
        'triggered_by',
        'status',
        'started_at',
        'completed_at',
        'items_processed',
        'items_matched',
        'results_summary',
        'error_message',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'items_processed' => 'integer',
        'items_matched' => 'integer',
        'results_summary' => 'array',
    ];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(IntelligenceAgent::class, 'agent_id');
    }

    public function triggeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triggered_by');
    }

    public function dataPoints(): HasMany
    {
        return $this->hasMany(AgentDataPoint::class, 'execution_id');
    }

    public function markAsRunning(): void
    {
        $this->update([
            'status' => self::STATUS_RUNNING,
            'started_at' => now(),
        ]);
    }

    public function markAsCompleted(array $summary = []): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => now(),
            'results_summary' => $summary,
        ]);
    }

    public function markAsFailed(string $error): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'completed_at' => now(),
            'error_message' => $error,
        ]);
    }

    public function getDurationInSeconds(): ?int
    {
        if (!$this->started_at || !$this->completed_at) {
            return null;
        }

        return $this->completed_at->diffInSeconds($this->started_at);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_RUNNING => 'Running',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_FAILED => 'Failed',
        ];
    }
}
