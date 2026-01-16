<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * CaseTask Model - Task management for investigation cases
 *
 * Supports various task types for organizing investigation work:
 * - Research: Information gathering and data collection
 * - Verification: Fact-checking and validation
 * - Analysis: Data analysis and interpretation
 * - Documentation: Report writing and documentation
 * - Review: Quality assurance and peer review
 *
 * Features:
 * - Assignment to team members
 * - Priority levels matching case priorities
 * - Status workflow with blocked state support
 * - Due date tracking
 * - Checklist support for sub-tasks
 * - Completion audit trail
 *
 * @property int $id
 * @property string $uuid Unique identifier for API access
 * @property int $case_id Foreign key to cases table
 * @property string $title Task title
 * @property string|null $description Detailed description
 * @property string $task_type Type classification
 * @property int|null $assigned_to User assigned to this task
 * @property string $priority Priority level
 * @property string $status Workflow status
 * @property \Carbon\Carbon|null $due_date Target completion date
 * @property \Carbon\Carbon|null $completed_at When task was completed
 * @property int|null $completed_by User who completed the task
 * @property array|null $checklist Sub-tasks as JSON array
 * @property string|null $blockers Reason for blocked status
 * @property int|null $created_by User who created the task
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property-read InvestigationCase $case
 * @property-read User|null $assignee
 * @property-read User|null $completer
 * @property-read User|null $creator
 */
class CaseTask extends Model
{
    // Task type constants
    public const TYPE_RESEARCH = 'research';
    public const TYPE_VERIFICATION = 'verification';
    public const TYPE_ANALYSIS = 'analysis';
    public const TYPE_DOCUMENTATION = 'documentation';
    public const TYPE_REVIEW = 'review';

    // Priority constants (same as case)
    public const PRIORITY_LOW = 'low';
    public const PRIORITY_MEDIUM = 'medium';
    public const PRIORITY_HIGH = 'high';
    public const PRIORITY_CRITICAL = 'critical';

    // Status constants
    public const STATUS_TODO = 'todo';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_BLOCKED = 'blocked';
    public const STATUS_COMPLETED = 'completed';

    protected $table = 'case_tasks';

    protected $fillable = [
        'uuid',
        'case_id',
        'title',
        'description',
        'task_type',
        'assigned_to',
        'priority',
        'status',
        'due_date',
        'completed_at',
        'completed_by',
        'checklist',
        'blockers',
        'created_by',
    ];

    protected $casts = [
        'due_date' => 'date',
        'completed_at' => 'datetime',
        'checklist' => 'array',
    ];

    protected $attributes = [
        'task_type' => self::TYPE_RESEARCH,
        'priority' => self::PRIORITY_MEDIUM,
        'status' => self::STATUS_TODO,
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
            if (empty($model->created_by) && auth()->check()) {
                $model->created_by = auth()->id();
            }
        });
    }

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    /**
     * Get the case this task belongs to
     */
    public function case(): BelongsTo
    {
        return $this->belongsTo(InvestigationCase::class, 'case_id');
    }

    /**
     * Get the user assigned to this task
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the user who completed this task
     */
    public function completer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    /**
     * Get the user who created this task
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // =========================================================================
    // STATUS METHODS
    // =========================================================================

    /**
     * Start working on this task
     */
    public function start(): void
    {
        $this->update(['status' => self::STATUS_IN_PROGRESS]);
    }

    /**
     * Mark task as blocked
     */
    public function block(string $reason): void
    {
        $this->update([
            'status' => self::STATUS_BLOCKED,
            'blockers' => $reason,
        ]);
    }

    /**
     * Unblock the task and resume
     */
    public function unblock(): void
    {
        $this->update([
            'status' => self::STATUS_IN_PROGRESS,
            'blockers' => null,
        ]);
    }

    /**
     * Complete the task
     */
    public function complete(?int $completedBy = null): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => now(),
            'completed_by' => $completedBy ?? auth()->id(),
        ]);

        // Update case progress
        $this->case->updateProgress();
    }

    /**
     * Reopen a completed task
     */
    public function reopen(): void
    {
        $this->update([
            'status' => self::STATUS_TODO,
            'completed_at' => null,
            'completed_by' => null,
        ]);

        // Update case progress
        $this->case->updateProgress();
    }

    /**
     * Assign task to a user
     */
    public function assignTo(int $userId): void
    {
        $this->update(['assigned_to' => $userId]);
    }

    /**
     * Unassign task
     */
    public function unassign(): void
    {
        $this->update(['assigned_to' => null]);
    }

    // =========================================================================
    // CHECKLIST METHODS
    // =========================================================================

    /**
     * Add a checklist item
     */
    public function addChecklistItem(string $text): void
    {
        $checklist = $this->checklist ?? [];
        $checklist[] = [
            'text' => $text,
            'completed' => false,
            'added_at' => now()->toIso8601String(),
        ];
        $this->update(['checklist' => $checklist]);
    }

    /**
     * Toggle a checklist item
     */
    public function toggleChecklistItem(int $index): void
    {
        $checklist = $this->checklist ?? [];
        if (isset($checklist[$index])) {
            $checklist[$index]['completed'] = !$checklist[$index]['completed'];
            $checklist[$index]['toggled_at'] = now()->toIso8601String();
            $this->update(['checklist' => $checklist]);
        }
    }

    /**
     * Remove a checklist item
     */
    public function removeChecklistItem(int $index): void
    {
        $checklist = $this->checklist ?? [];
        unset($checklist[$index]);
        $this->update(['checklist' => array_values($checklist)]);
    }

    /**
     * Get checklist progress
     */
    public function getChecklistProgress(): array
    {
        $checklist = $this->checklist ?? [];
        $total = count($checklist);
        $completed = count(array_filter($checklist, fn ($item) => $item['completed'] ?? false));

        return [
            'total' => $total,
            'completed' => $completed,
            'percentage' => $total > 0 ? round(($completed / $total) * 100) : 0,
        ];
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    /**
     * Scope to filter by case
     */
    public function scopeForCase($query, int $caseId)
    {
        return $query->where('case_id', $caseId);
    }

    /**
     * Scope to filter by task type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('task_type', $type);
    }

    /**
     * Scope to filter by status
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter incomplete tasks
     */
    public function scopeIncomplete($query)
    {
        return $query->whereIn('status', [
            self::STATUS_TODO,
            self::STATUS_IN_PROGRESS,
            self::STATUS_BLOCKED,
        ]);
    }

    /**
     * Scope to filter completed tasks
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope to filter assigned to a user
     */
    public function scopeAssignedTo($query, int $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    /**
     * Scope to filter unassigned tasks
     */
    public function scopeUnassigned($query)
    {
        return $query->whereNull('assigned_to');
    }

    /**
     * Scope to filter by priority
     */
    public function scopeByPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope to filter overdue tasks
     */
    public function scopeOverdue($query)
    {
        return $query->whereNotNull('due_date')
            ->where('due_date', '<', now())
            ->where('status', '!=', self::STATUS_COMPLETED);
    }

    /**
     * Scope to filter tasks due soon (within days)
     */
    public function scopeDueSoon($query, int $days = 7)
    {
        return $query->whereNotNull('due_date')
            ->whereBetween('due_date', [now(), now()->addDays($days)])
            ->where('status', '!=', self::STATUS_COMPLETED);
    }

    /**
     * Scope to order by priority (critical first)
     */
    public function scopeOrderByPriority($query)
    {
        return $query->orderByRaw("FIELD(priority, 'critical', 'high', 'medium', 'low')");
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    /**
     * Check if task is overdue
     */
    public function isOverdue(): bool
    {
        if (!$this->due_date) {
            return false;
        }
        return $this->due_date->isPast() && $this->status !== self::STATUS_COMPLETED;
    }

    /**
     * Check if task is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if task is blocked
     */
    public function isBlocked(): bool
    {
        return $this->status === self::STATUS_BLOCKED;
    }

    /**
     * Check if task is assigned
     */
    public function isAssigned(): bool
    {
        return $this->assigned_to !== null;
    }

    /**
     * Check if task has a checklist
     */
    public function hasChecklist(): bool
    {
        return !empty($this->checklist);
    }

    /**
     * Get days until due (negative if overdue)
     */
    public function getDaysUntilDue(): ?int
    {
        if (!$this->due_date) {
            return null;
        }
        return (int) now()->diffInDays($this->due_date, false);
    }

    // =========================================================================
    // STATIC REFERENCE DATA
    // =========================================================================

    /**
     * Get available task types
     */
    public static function getTaskTypes(): array
    {
        return [
            self::TYPE_RESEARCH => 'Research',
            self::TYPE_VERIFICATION => 'Verification',
            self::TYPE_ANALYSIS => 'Analysis',
            self::TYPE_DOCUMENTATION => 'Documentation',
            self::TYPE_REVIEW => 'Review',
        ];
    }

    /**
     * Get available priorities
     */
    public static function getPriorities(): array
    {
        return [
            self::PRIORITY_LOW => 'Low',
            self::PRIORITY_MEDIUM => 'Medium',
            self::PRIORITY_HIGH => 'High',
            self::PRIORITY_CRITICAL => 'Critical',
        ];
    }

    /**
     * Get available statuses
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_TODO => 'To Do',
            self::STATUS_IN_PROGRESS => 'In Progress',
            self::STATUS_BLOCKED => 'Blocked',
            self::STATUS_COMPLETED => 'Completed',
        ];
    }
}
