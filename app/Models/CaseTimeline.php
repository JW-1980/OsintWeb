<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * CaseTimeline Model - Activity log for investigation cases
 *
 * Records all significant events within a case for:
 * - Audit trail and compliance
 * - Activity feed for team awareness
 * - Progress tracking
 * - Historical reference
 *
 * Entry types include:
 * - Case lifecycle: status changes, priority updates
 * - Evidence: additions and removals
 * - Notes: new notes and updates
 * - Tasks: creation and completion
 * - Team: member joins and departures
 *
 * @property int $id
 * @property int $case_id Foreign key to cases table
 * @property string $entry_type Type of timeline entry
 * @property array|null $entry_data JSON data specific to entry type
 * @property \Carbon\Carbon $occurred_at When this event occurred
 * @property int|null $created_by User who triggered this entry
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property-read InvestigationCase $case
 * @property-read User|null $creator
 */
class CaseTimeline extends Model
{
    // Entry type constants
    public const TYPE_EVENT = 'event';
    public const TYPE_NOTE = 'note';
    public const TYPE_EVIDENCE_ADDED = 'evidence_added';
    public const TYPE_EVIDENCE_REMOVED = 'evidence_removed';
    public const TYPE_STATUS_CHANGE = 'status_change';
    public const TYPE_TASK_CREATED = 'task_created';
    public const TYPE_TASK_COMPLETED = 'task_completed';
    public const TYPE_USER_JOINED = 'user_joined';
    public const TYPE_USER_LEFT = 'user_left';
    public const TYPE_PRIORITY_CHANGE = 'priority_change';
    public const TYPE_COMMENT = 'comment';

    protected $table = 'case_timeline';

    protected $fillable = [
        'case_id',
        'entry_type',
        'entry_data',
        'occurred_at',
        'created_by',
    ];

    protected $casts = [
        'entry_data' => 'array',
        'occurred_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->occurred_at)) {
                $model->occurred_at = now();
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
     * Get the case this entry belongs to
     */
    public function case(): BelongsTo
    {
        return $this->belongsTo(InvestigationCase::class, 'case_id');
    }

    /**
     * Get the user who created this entry
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // =========================================================================
    // FACTORY METHODS
    // =========================================================================

    /**
     * Log a status change
     */
    public static function logStatusChange(
        int $caseId,
        string $oldStatus,
        string $newStatus,
        ?int $userId = null
    ): self {
        return static::create([
            'case_id' => $caseId,
            'entry_type' => self::TYPE_STATUS_CHANGE,
            'entry_data' => [
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
            ],
            'created_by' => $userId ?? auth()->id(),
        ]);
    }

    /**
     * Log a priority change
     */
    public static function logPriorityChange(
        int $caseId,
        string $oldPriority,
        string $newPriority,
        ?int $userId = null
    ): self {
        return static::create([
            'case_id' => $caseId,
            'entry_type' => self::TYPE_PRIORITY_CHANGE,
            'entry_data' => [
                'old_priority' => $oldPriority,
                'new_priority' => $newPriority,
            ],
            'created_by' => $userId ?? auth()->id(),
        ]);
    }

    /**
     * Log evidence addition
     */
    public static function logEvidenceAdded(
        int $caseId,
        string $evidenceType,
        int $evidenceId,
        ?string $title = null,
        ?int $userId = null
    ): self {
        return static::create([
            'case_id' => $caseId,
            'entry_type' => self::TYPE_EVIDENCE_ADDED,
            'entry_data' => [
                'evidence_type' => $evidenceType,
                'evidence_id' => $evidenceId,
                'title' => $title,
            ],
            'created_by' => $userId ?? auth()->id(),
        ]);
    }

    /**
     * Log evidence removal
     */
    public static function logEvidenceRemoved(
        int $caseId,
        string $evidenceType,
        int $evidenceId,
        ?string $title = null,
        ?int $userId = null
    ): self {
        return static::create([
            'case_id' => $caseId,
            'entry_type' => self::TYPE_EVIDENCE_REMOVED,
            'entry_data' => [
                'evidence_type' => $evidenceType,
                'evidence_id' => $evidenceId,
                'title' => $title,
            ],
            'created_by' => $userId ?? auth()->id(),
        ]);
    }

    /**
     * Log note creation
     */
    public static function logNoteAdded(
        int $caseId,
        int $noteId,
        string $noteType,
        ?string $preview = null,
        ?int $userId = null
    ): self {
        return static::create([
            'case_id' => $caseId,
            'entry_type' => self::TYPE_NOTE,
            'entry_data' => [
                'note_id' => $noteId,
                'note_type' => $noteType,
                'preview' => $preview,
            ],
            'created_by' => $userId ?? auth()->id(),
        ]);
    }

    /**
     * Log task creation
     */
    public static function logTaskCreated(
        int $caseId,
        int $taskId,
        string $title,
        ?int $assignedTo = null,
        ?int $userId = null
    ): self {
        return static::create([
            'case_id' => $caseId,
            'entry_type' => self::TYPE_TASK_CREATED,
            'entry_data' => [
                'task_id' => $taskId,
                'title' => $title,
                'assigned_to' => $assignedTo,
            ],
            'created_by' => $userId ?? auth()->id(),
        ]);
    }

    /**
     * Log task completion
     */
    public static function logTaskCompleted(
        int $caseId,
        int $taskId,
        string $title,
        ?int $userId = null
    ): self {
        return static::create([
            'case_id' => $caseId,
            'entry_type' => self::TYPE_TASK_COMPLETED,
            'entry_data' => [
                'task_id' => $taskId,
                'title' => $title,
            ],
            'created_by' => $userId ?? auth()->id(),
        ]);
    }

    /**
     * Log user joining the case
     */
    public static function logUserJoined(
        int $caseId,
        int $joinedUserId,
        string $role,
        ?int $addedBy = null
    ): self {
        return static::create([
            'case_id' => $caseId,
            'entry_type' => self::TYPE_USER_JOINED,
            'entry_data' => [
                'user_id' => $joinedUserId,
                'role' => $role,
            ],
            'created_by' => $addedBy ?? auth()->id(),
        ]);
    }

    /**
     * Log user leaving the case
     */
    public static function logUserLeft(
        int $caseId,
        int $leftUserId,
        ?int $removedBy = null
    ): self {
        return static::create([
            'case_id' => $caseId,
            'entry_type' => self::TYPE_USER_LEFT,
            'entry_data' => [
                'user_id' => $leftUserId,
            ],
            'created_by' => $removedBy ?? auth()->id(),
        ]);
    }

    /**
     * Log a general comment
     */
    public static function logComment(
        int $caseId,
        string $message,
        ?int $userId = null
    ): self {
        return static::create([
            'case_id' => $caseId,
            'entry_type' => self::TYPE_COMMENT,
            'entry_data' => [
                'message' => $message,
            ],
            'created_by' => $userId ?? auth()->id(),
        ]);
    }

    /**
     * Log a custom event
     */
    public static function logEvent(
        int $caseId,
        string $description,
        array $data = [],
        ?int $userId = null
    ): self {
        return static::create([
            'case_id' => $caseId,
            'entry_type' => self::TYPE_EVENT,
            'entry_data' => array_merge(['description' => $description], $data),
            'created_by' => $userId ?? auth()->id(),
        ]);
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
     * Scope to filter by entry type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('entry_type', $type);
    }

    /**
     * Scope to filter by multiple types
     */
    public function scopeOfTypes($query, array $types)
    {
        return $query->whereIn('entry_type', $types);
    }

    /**
     * Scope to filter by creator
     */
    public function scopeByCreator($query, int $userId)
    {
        return $query->where('created_by', $userId);
    }

    /**
     * Scope to filter by date range
     */
    public function scopeBetween($query, $start, $end)
    {
        return $query->whereBetween('occurred_at', [$start, $end]);
    }

    /**
     * Scope to order chronologically
     */
    public function scopeChronological($query, string $direction = 'desc')
    {
        return $query->orderBy('occurred_at', $direction);
    }

    /**
     * Scope to get recent entries
     */
    public function scopeRecent($query, int $limit = 50)
    {
        return $query->orderBy('occurred_at', 'desc')->limit($limit);
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    /**
     * Get a human-readable description of this entry
     */
    public function getDescription(): string
    {
        $data = $this->entry_data ?? [];

        return match ($this->entry_type) {
            self::TYPE_STATUS_CHANGE => sprintf(
                'Status changed from %s to %s',
                $data['old_status'] ?? 'unknown',
                $data['new_status'] ?? 'unknown'
            ),
            self::TYPE_PRIORITY_CHANGE => sprintf(
                'Priority changed from %s to %s',
                $data['old_priority'] ?? 'unknown',
                $data['new_priority'] ?? 'unknown'
            ),
            self::TYPE_EVIDENCE_ADDED => sprintf(
                'Evidence added: %s',
                $data['title'] ?? $data['evidence_type'] ?? 'Unknown'
            ),
            self::TYPE_EVIDENCE_REMOVED => sprintf(
                'Evidence removed: %s',
                $data['title'] ?? $data['evidence_type'] ?? 'Unknown'
            ),
            self::TYPE_NOTE => sprintf(
                'Note added: %s',
                $data['preview'] ?? 'New ' . ($data['note_type'] ?? 'note')
            ),
            self::TYPE_TASK_CREATED => sprintf(
                'Task created: %s',
                $data['title'] ?? 'Unnamed task'
            ),
            self::TYPE_TASK_COMPLETED => sprintf(
                'Task completed: %s',
                $data['title'] ?? 'Unnamed task'
            ),
            self::TYPE_USER_JOINED => sprintf(
                'User joined as %s',
                $data['role'] ?? 'team member'
            ),
            self::TYPE_USER_LEFT => 'User left the case',
            self::TYPE_COMMENT => $data['message'] ?? 'Comment added',
            self::TYPE_EVENT => $data['description'] ?? 'Event logged',
            default => 'Unknown activity',
        };
    }

    /**
     * Get icon for entry type (for UI)
     */
    public function getIcon(): string
    {
        return match ($this->entry_type) {
            self::TYPE_STATUS_CHANGE => 'refresh',
            self::TYPE_PRIORITY_CHANGE => 'flag',
            self::TYPE_EVIDENCE_ADDED => 'plus-circle',
            self::TYPE_EVIDENCE_REMOVED => 'minus-circle',
            self::TYPE_NOTE => 'file-text',
            self::TYPE_TASK_CREATED => 'check-square',
            self::TYPE_TASK_COMPLETED => 'check-circle',
            self::TYPE_USER_JOINED => 'user-plus',
            self::TYPE_USER_LEFT => 'user-minus',
            self::TYPE_COMMENT => 'message-circle',
            self::TYPE_EVENT => 'activity',
            default => 'circle',
        };
    }

    // =========================================================================
    // STATIC REFERENCE DATA
    // =========================================================================

    /**
     * Get available entry types
     */
    public static function getEntryTypes(): array
    {
        return [
            self::TYPE_EVENT => 'Event',
            self::TYPE_NOTE => 'Note',
            self::TYPE_EVIDENCE_ADDED => 'Evidence Added',
            self::TYPE_EVIDENCE_REMOVED => 'Evidence Removed',
            self::TYPE_STATUS_CHANGE => 'Status Change',
            self::TYPE_TASK_CREATED => 'Task Created',
            self::TYPE_TASK_COMPLETED => 'Task Completed',
            self::TYPE_USER_JOINED => 'User Joined',
            self::TYPE_USER_LEFT => 'User Left',
            self::TYPE_PRIORITY_CHANGE => 'Priority Change',
            self::TYPE_COMMENT => 'Comment',
        ];
    }
}
