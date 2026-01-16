<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * InvestigationCase Model - Digital War Room for OSINT Investigations
 *
 * Represents a collaborative investigation workspace where analysts can:
 * - Track and link evidence from multiple sources
 * - Manage team members with defined roles
 * - Create and assign tasks
 * - Document findings with threaded notes
 * - Build investigation timelines
 * - Generate case reports
 *
 * @property int $id
 * @property string $uuid
 * @property string|null $case_number Auto-generated unique identifier (e.g., CASE-2026-001234)
 * @property int $user_id Owner/creator of the case
 * @property int|null $lead_analyst_id Primary responsible analyst
 * @property string $title Case title/name
 * @property string|null $description Detailed description
 * @property string|null $objective Investigation goal/mission statement
 * @property string $case_type Type of investigation
 * @property string $status Current workflow status
 * @property string $priority Priority level
 * @property string $classification_level Security classification
 * @property array|null $tags Categorization tags
 * @property array|null $collaborators Legacy collaborator IDs (deprecated, use team_members)
 * @property array|null $team_members Team members with roles [{user_id, role, joined_at}]
 * @property \Carbon\Carbon|null $started_at When investigation actively began
 * @property \Carbon\Carbon|null $closed_at When case was closed
 * @property \Carbon\Carbon|null $start_date Planned start date
 * @property \Carbon\Carbon|null $due_date Target completion date
 * @property int $progress_percentage Calculated progress (0-100)
 * @property int|null $created_by Original creator user ID
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 *
 * @property-read User $user
 * @property-read User|null $leadAnalyst
 * @property-read User|null $creator
 * @property-read \Illuminate\Database\Eloquent\Collection|CaseNote[] $notes
 * @property-read \Illuminate\Database\Eloquent\Collection|CaseEvidence[] $evidence
 * @property-read \Illuminate\Database\Eloquent\Collection|CaseTask[] $tasks
 * @property-read \Illuminate\Database\Eloquent\Collection|CaseTimeline[] $timeline
 * @property-read \Illuminate\Database\Eloquent\Collection|TimelineEntry[] $timelineEntries
 * @property-read \Illuminate\Database\Eloquent\Collection|NetworkEntity[] $networkEntities
 */
class InvestigationCase extends Model
{
    use SoftDeletes;

    protected $table = 'cases';

    // Status constants
    public const STATUS_OPEN = 'open';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_ON_HOLD = 'on_hold';
    public const STATUS_CLOSED = 'closed';
    public const STATUS_ARCHIVED = 'archived';

    // Priority constants
    public const PRIORITY_LOW = 'low';
    public const PRIORITY_MEDIUM = 'medium';
    public const PRIORITY_HIGH = 'high';
    public const PRIORITY_CRITICAL = 'critical';

    // Case type constants
    public const TYPE_INCIDENT = 'incident';
    public const TYPE_ACTOR_PROFILE = 'actor_profile';
    public const TYPE_EQUIPMENT_TRACKING = 'equipment_tracking';
    public const TYPE_TERRITORIAL = 'territorial';
    public const TYPE_NETWORK = 'network';
    public const TYPE_GENERAL = 'general';

    // Classification level constants
    public const CLASSIFICATION_UNCLASSIFIED = 'unclassified';
    public const CLASSIFICATION_CONFIDENTIAL = 'confidential';
    public const CLASSIFICATION_SECRET = 'secret';

    // Team member role constants
    public const ROLE_LEAD = 'lead';
    public const ROLE_ANALYST = 'analyst';
    public const ROLE_REVIEWER = 'reviewer';
    public const ROLE_OBSERVER = 'observer';

    protected $fillable = [
        'uuid',
        'case_number',
        'user_id',
        'lead_analyst_id',
        'title',
        'description',
        'objective',
        'case_type',
        'status',
        'priority',
        'classification_level',
        'tags',
        'collaborators',
        'team_members',
        'started_at',
        'closed_at',
        'start_date',
        'due_date',
        'progress_percentage',
        'created_by',
    ];

    protected $casts = [
        'tags' => 'array',
        'collaborators' => 'array',
        'team_members' => 'array',
        'started_at' => 'datetime',
        'closed_at' => 'datetime',
        'start_date' => 'date',
        'due_date' => 'date',
        'progress_percentage' => 'integer',
    ];

    protected $attributes = [
        'status' => self::STATUS_OPEN,
        'priority' => self::PRIORITY_MEDIUM,
        'case_type' => self::TYPE_GENERAL,
        'classification_level' => self::CLASSIFICATION_UNCLASSIFIED,
        'progress_percentage' => 0,
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
            if (empty($model->case_number)) {
                $model->case_number = static::generateCaseNumber();
            }
            if (empty($model->created_by) && auth()->check()) {
                $model->created_by = auth()->id();
            }
        });
    }

    /**
     * Generate a unique case number in format CASE-YYYY-XXXXXX
     */
    public static function generateCaseNumber(): string
    {
        $year = now()->year;
        $prefix = "CASE-{$year}-";

        $lastCase = static::withTrashed()
            ->where('case_number', 'like', "{$prefix}%")
            ->orderBy('case_number', 'desc')
            ->first();

        if ($lastCase) {
            $lastNumber = (int) substr($lastCase->case_number, strlen($prefix));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad((string) $newNumber, 6, '0', STR_PAD_LEFT);
    }

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    /**
     * Get the owner of this case
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the lead analyst assigned to this case
     */
    public function leadAnalyst(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lead_analyst_id');
    }

    /**
     * Get the original creator of this case
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all notes for this case
     */
    public function notes(): HasMany
    {
        return $this->hasMany(CaseNote::class, 'case_id');
    }

    /**
     * Get all evidence linked to this case
     */
    public function evidence(): HasMany
    {
        return $this->hasMany(CaseEvidence::class, 'case_id');
    }

    /**
     * Get all tasks for this case
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(CaseTask::class, 'case_id');
    }

    /**
     * Get the activity timeline for this case
     */
    public function timeline(): HasMany
    {
        return $this->hasMany(CaseTimeline::class, 'case_id');
    }

    /**
     * Get timeline entries (investigation timeline, not activity log)
     */
    public function timelineEntries(): HasMany
    {
        return $this->hasMany(TimelineEntry::class, 'case_id');
    }

    /**
     * Get network entities for this case
     */
    public function networkEntities(): HasMany
    {
        return $this->hasMany(NetworkEntity::class, 'case_id');
    }

    // =========================================================================
    // TEAM MANAGEMENT
    // =========================================================================

    /**
     * Add a team member to this case
     */
    public function addTeamMember(int $userId, string $role = self::ROLE_ANALYST): void
    {
        $members = $this->team_members ?? [];

        // Check if already a member
        foreach ($members as $member) {
            if ($member['user_id'] === $userId) {
                return;
            }
        }

        $members[] = [
            'user_id' => $userId,
            'role' => $role,
            'joined_at' => now()->toIso8601String(),
        ];

        $this->update(['team_members' => $members]);
    }

    /**
     * Remove a team member from this case
     */
    public function removeTeamMember(int $userId): void
    {
        $members = $this->team_members ?? [];
        $members = array_filter($members, fn ($m) => $m['user_id'] !== $userId);
        $this->update(['team_members' => array_values($members)]);
    }

    /**
     * Update a team member's role
     */
    public function updateTeamMemberRole(int $userId, string $role): void
    {
        $members = $this->team_members ?? [];

        foreach ($members as &$member) {
            if ($member['user_id'] === $userId) {
                $member['role'] = $role;
                break;
            }
        }

        $this->update(['team_members' => $members]);
    }

    /**
     * Check if a user is a team member
     */
    public function isTeamMember(int $userId): bool
    {
        if ($this->user_id === $userId || $this->lead_analyst_id === $userId) {
            return true;
        }

        $members = $this->team_members ?? [];
        foreach ($members as $member) {
            if ($member['user_id'] === $userId) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get a team member's role
     */
    public function getTeamMemberRole(int $userId): ?string
    {
        if ($this->lead_analyst_id === $userId) {
            return self::ROLE_LEAD;
        }

        if ($this->user_id === $userId) {
            return self::ROLE_LEAD;
        }

        $members = $this->team_members ?? [];
        foreach ($members as $member) {
            if ($member['user_id'] === $userId) {
                return $member['role'];
            }
        }

        return null;
    }

    /**
     * Get all team member user IDs
     */
    public function getAllTeamMemberIds(): array
    {
        $ids = [$this->user_id];

        if ($this->lead_analyst_id) {
            $ids[] = $this->lead_analyst_id;
        }

        $members = $this->team_members ?? [];
        foreach ($members as $member) {
            $ids[] = $member['user_id'];
        }

        return array_unique($ids);
    }

    // =========================================================================
    // STATUS MANAGEMENT
    // =========================================================================

    /**
     * Start the investigation
     */
    public function start(): void
    {
        $this->update([
            'status' => self::STATUS_ACTIVE,
            'started_at' => now(),
        ]);
    }

    /**
     * Put the case on hold
     */
    public function hold(): void
    {
        $this->update(['status' => self::STATUS_ON_HOLD]);
    }

    /**
     * Resume an on-hold case
     */
    public function resume(): void
    {
        $this->update(['status' => self::STATUS_ACTIVE]);
    }

    /**
     * Close the case
     */
    public function close(): void
    {
        $this->update([
            'status' => self::STATUS_CLOSED,
            'closed_at' => now(),
            'progress_percentage' => 100,
        ]);
    }

    /**
     * Archive the case
     */
    public function archive(): void
    {
        $this->update(['status' => self::STATUS_ARCHIVED]);
    }

    /**
     * Reopen a closed case
     */
    public function reopen(): void
    {
        $this->update([
            'status' => self::STATUS_ACTIVE,
            'closed_at' => null,
        ]);
    }

    // =========================================================================
    // PROGRESS CALCULATION
    // =========================================================================

    /**
     * Calculate and update progress percentage based on tasks
     */
    public function updateProgress(): int
    {
        $totalTasks = $this->tasks()->count();

        if ($totalTasks === 0) {
            $this->update(['progress_percentage' => 0]);
            return 0;
        }

        $completedTasks = $this->tasks()
            ->where('status', CaseTask::STATUS_COMPLETED)
            ->count();

        $progress = (int) round(($completedTasks / $totalTasks) * 100);

        $this->update(['progress_percentage' => $progress]);

        return $progress;
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    /**
     * Scope to filter open/active cases
     */
    public function scopeOpen($query)
    {
        return $query->whereIn('status', [
            self::STATUS_OPEN,
            self::STATUS_ACTIVE,
            self::STATUS_IN_PROGRESS,
        ]);
    }

    /**
     * Scope to filter by status
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by priority
     */
    public function scopeByPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope to filter by case type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('case_type', $type);
    }

    /**
     * Scope to filter by classification level
     */
    public function scopeByClassification($query, string $level)
    {
        return $query->where('classification_level', $level);
    }

    /**
     * Scope to filter overdue cases
     */
    public function scopeOverdue($query)
    {
        return $query->whereNotNull('due_date')
            ->where('due_date', '<', now())
            ->whereNotIn('status', [self::STATUS_CLOSED, self::STATUS_ARCHIVED]);
    }

    /**
     * Scope to filter cases where user is a team member
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('user_id', $userId)
                ->orWhere('lead_analyst_id', $userId)
                ->orWhereJsonContains('team_members', ['user_id' => $userId]);
        });
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    /**
     * Check if case is overdue
     */
    public function isOverdue(): bool
    {
        if (!$this->due_date) {
            return false;
        }

        return $this->due_date->isPast() &&
            !in_array($this->status, [self::STATUS_CLOSED, self::STATUS_ARCHIVED]);
    }

    /**
     * Check if case is active
     */
    public function isActive(): bool
    {
        return in_array($this->status, [
            self::STATUS_OPEN,
            self::STATUS_ACTIVE,
            self::STATUS_IN_PROGRESS,
        ]);
    }

    /**
     * Check if case is closed
     */
    public function isClosed(): bool
    {
        return in_array($this->status, [
            self::STATUS_CLOSED,
            self::STATUS_ARCHIVED,
        ]);
    }

    /**
     * Get summary statistics for the case
     */
    public function getStats(): array
    {
        return [
            'evidence_count' => $this->evidence()->count(),
            'notes_count' => $this->notes()->count(),
            'tasks_total' => $this->tasks()->count(),
            'tasks_completed' => $this->tasks()->where('status', CaseTask::STATUS_COMPLETED)->count(),
            'tasks_pending' => $this->tasks()->where('status', CaseTask::STATUS_TODO)->count(),
            'tasks_blocked' => $this->tasks()->where('status', CaseTask::STATUS_BLOCKED)->count(),
            'team_members_count' => count($this->getAllTeamMemberIds()),
            'timeline_entries' => $this->timeline()->count(),
            'network_entities' => $this->networkEntities()->count(),
        ];
    }

    // =========================================================================
    // STATIC REFERENCE DATA
    // =========================================================================

    /**
     * Get available statuses
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_OPEN => 'Open',
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_IN_PROGRESS => 'In Progress',
            self::STATUS_ON_HOLD => 'On Hold',
            self::STATUS_CLOSED => 'Closed',
            self::STATUS_ARCHIVED => 'Archived',
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
     * Get available case types
     */
    public static function getCaseTypes(): array
    {
        return [
            self::TYPE_INCIDENT => 'Incident Investigation',
            self::TYPE_ACTOR_PROFILE => 'Actor Profile',
            self::TYPE_EQUIPMENT_TRACKING => 'Equipment Tracking',
            self::TYPE_TERRITORIAL => 'Territorial Analysis',
            self::TYPE_NETWORK => 'Network Analysis',
            self::TYPE_GENERAL => 'General Investigation',
        ];
    }

    /**
     * Get available classification levels
     */
    public static function getClassificationLevels(): array
    {
        return [
            self::CLASSIFICATION_UNCLASSIFIED => 'Unclassified',
            self::CLASSIFICATION_CONFIDENTIAL => 'Confidential',
            self::CLASSIFICATION_SECRET => 'Secret',
        ];
    }

    /**
     * Get available team member roles
     */
    public static function getTeamRoles(): array
    {
        return [
            self::ROLE_LEAD => 'Lead Analyst',
            self::ROLE_ANALYST => 'Analyst',
            self::ROLE_REVIEWER => 'Reviewer',
            self::ROLE_OBSERVER => 'Observer',
        ];
    }
}
