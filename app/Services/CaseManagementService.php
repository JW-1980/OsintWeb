<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CaseEvidence;
use App\Models\CaseNote;
use App\Models\CaseTask;
use App\Models\CaseTimeline;
use App\Models\InvestigationCase;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * CaseManagementService - Business logic for investigation case management
 *
 * Provides comprehensive case management functionality:
 * - Case lifecycle management (create, update, close, archive)
 * - Team member management with roles
 * - Evidence linking and review workflow
 * - Notes and documentation
 * - Task management with progress tracking
 * - Activity timeline logging
 * - Report generation
 * - Data export
 */
class CaseManagementService
{
    /**
     * Create a new investigation case
     *
     * @param array $data Case data including title, description, etc.
     * @return InvestigationCase
     */
    public function createCase(array $data): InvestigationCase
    {
        return DB::transaction(function () use ($data) {
            $case = InvestigationCase::create([
                'user_id' => $data['user_id'] ?? auth()->id(),
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'objective' => $data['objective'] ?? null,
                'case_type' => $data['case_type'] ?? InvestigationCase::TYPE_GENERAL,
                'priority' => $data['priority'] ?? InvestigationCase::PRIORITY_MEDIUM,
                'classification_level' => $data['classification_level'] ?? InvestigationCase::CLASSIFICATION_UNCLASSIFIED,
                'lead_analyst_id' => $data['lead_analyst_id'] ?? null,
                'tags' => $data['tags'] ?? [],
                'start_date' => $data['start_date'] ?? null,
                'due_date' => $data['due_date'] ?? null,
            ]);

            // Log case creation
            CaseTimeline::logEvent(
                $case->id,
                'Case created',
                ['title' => $case->title]
            );

            return $case;
        });
    }

    /**
     * Update an existing case
     *
     * @param InvestigationCase $case
     * @param array $data
     * @return InvestigationCase
     */
    public function updateCase(InvestigationCase $case, array $data): InvestigationCase
    {
        return DB::transaction(function () use ($case, $data) {
            $oldStatus = $case->status;
            $oldPriority = $case->priority;

            $case->update($data);

            // Log status change if changed
            if (isset($data['status']) && $data['status'] !== $oldStatus) {
                CaseTimeline::logStatusChange($case->id, $oldStatus, $data['status']);
            }

            // Log priority change if changed
            if (isset($data['priority']) && $data['priority'] !== $oldPriority) {
                CaseTimeline::logPriorityChange($case->id, $oldPriority, $data['priority']);
            }

            return $case->fresh();
        });
    }

    /**
     * Add a team member to a case
     *
     * @param int $caseId
     * @param int $userId
     * @param string $role
     * @return InvestigationCase
     */
    public function addTeamMember(int $caseId, int $userId, string $role = InvestigationCase::ROLE_ANALYST): InvestigationCase
    {
        $case = InvestigationCase::findOrFail($caseId);

        // Verify user exists
        User::findOrFail($userId);

        $case->addTeamMember($userId, $role);

        CaseTimeline::logUserJoined($caseId, $userId, $role);

        return $case->fresh();
    }

    /**
     * Remove a team member from a case
     *
     * @param int $caseId
     * @param int $userId
     * @return InvestigationCase
     */
    public function removeTeamMember(int $caseId, int $userId): InvestigationCase
    {
        $case = InvestigationCase::findOrFail($caseId);

        $case->removeTeamMember($userId);

        CaseTimeline::logUserLeft($caseId, $userId);

        return $case->fresh();
    }

    /**
     * Update a team member's role
     *
     * @param int $caseId
     * @param int $userId
     * @param string $role
     * @return InvestigationCase
     */
    public function updateTeamMemberRole(int $caseId, int $userId, string $role): InvestigationCase
    {
        $case = InvestigationCase::findOrFail($caseId);

        $case->updateTeamMemberRole($userId, $role);

        CaseTimeline::logComment(
            $caseId,
            "Team member role updated to {$role}"
        );

        return $case->fresh();
    }

    /**
     * Link evidence to a case
     *
     * @param int $caseId
     * @param string $evidenceType Model class or type identifier
     * @param int $evidenceId
     * @param string|null $notes
     * @param int $relevanceScore
     * @return CaseEvidence
     */
    public function linkEvidence(
        int $caseId,
        string $evidenceType,
        int $evidenceId,
        ?string $notes = null,
        int $relevanceScore = CaseEvidence::RELEVANCE_MEDIUM
    ): CaseEvidence {
        return DB::transaction(function () use ($caseId, $evidenceType, $evidenceId, $notes, $relevanceScore) {
            $evidence = CaseEvidence::create([
                'case_id' => $caseId,
                'evidence_type' => $evidenceType,
                'evidence_id' => $evidenceId,
                'notes' => $notes,
                'relevance_score' => $relevanceScore,
            ]);

            // Try to get a title for the timeline entry
            $title = null;
            try {
                $model = $evidenceType::find($evidenceId);
                $title = $model->title ?? $model->name ?? null;
            } catch (\Exception $e) {
                // Ignore if we can't get title
            }

            CaseTimeline::logEvidenceAdded(
                $caseId,
                class_basename($evidenceType),
                $evidenceId,
                $title
            );

            return $evidence;
        });
    }

    /**
     * Remove evidence from a case
     *
     * @param int $evidenceId CaseEvidence ID
     * @return bool
     */
    public function unlinkEvidence(int $evidenceId): bool
    {
        $evidence = CaseEvidence::findOrFail($evidenceId);

        CaseTimeline::logEvidenceRemoved(
            $evidence->case_id,
            class_basename($evidence->evidence_type),
            $evidence->evidence_id
        );

        return $evidence->delete();
    }

    /**
     * Update evidence metadata
     *
     * @param int $evidenceId
     * @param array $data
     * @return CaseEvidence
     */
    public function updateEvidence(int $evidenceId, array $data): CaseEvidence
    {
        $evidence = CaseEvidence::findOrFail($evidenceId);
        $evidence->update($data);
        return $evidence->fresh();
    }

    /**
     * Review evidence (verify, dispute, exclude)
     *
     * @param int $evidenceId
     * @param string $status
     * @param int|null $reviewerId
     * @return CaseEvidence
     */
    public function reviewEvidence(int $evidenceId, string $status, ?int $reviewerId = null): CaseEvidence
    {
        $evidence = CaseEvidence::findOrFail($evidenceId);

        match ($status) {
            CaseEvidence::STATUS_VERIFIED => $evidence->verify($reviewerId),
            CaseEvidence::STATUS_DISPUTED => $evidence->dispute($reviewerId),
            CaseEvidence::STATUS_EXCLUDED => $evidence->exclude($reviewerId),
            default => $evidence->resetReview(),
        };

        return $evidence->fresh();
    }

    /**
     * Add a note to a case
     *
     * @param int $caseId
     * @param string $noteType
     * @param string $content
     * @param array $options Additional options (title, is_pinned, parent_id, attachments)
     * @return CaseNote
     */
    public function addNote(
        int $caseId,
        string $noteType,
        string $content,
        array $options = []
    ): CaseNote {
        return DB::transaction(function () use ($caseId, $noteType, $content, $options) {
            $note = CaseNote::create([
                'case_id' => $caseId,
                'note_type' => $noteType,
                'content' => $content,
                'title' => $options['title'] ?? null,
                'is_pinned' => $options['is_pinned'] ?? false,
                'parent_id' => $options['parent_id'] ?? null,
                'attachments' => $options['attachments'] ?? null,
                'mentioned_users' => $options['mentioned_users'] ?? null,
            ]);

            CaseTimeline::logNoteAdded(
                $caseId,
                $note->id,
                $noteType,
                $note->getPreview(100)
            );

            return $note;
        });
    }

    /**
     * Update a note
     *
     * @param int $noteId
     * @param array $data
     * @return CaseNote
     */
    public function updateNote(int $noteId, array $data): CaseNote
    {
        $note = CaseNote::findOrFail($noteId);
        $note->update($data);
        return $note->fresh();
    }

    /**
     * Delete a note
     *
     * @param int $noteId
     * @return bool
     */
    public function deleteNote(int $noteId): bool
    {
        $note = CaseNote::findOrFail($noteId);
        return $note->delete();
    }

    /**
     * Create a task for a case
     *
     * @param int $caseId
     * @param array $taskData
     * @return CaseTask
     */
    public function createTask(int $caseId, array $taskData): CaseTask
    {
        return DB::transaction(function () use ($caseId, $taskData) {
            $task = CaseTask::create([
                'case_id' => $caseId,
                'title' => $taskData['title'],
                'description' => $taskData['description'] ?? null,
                'task_type' => $taskData['task_type'] ?? CaseTask::TYPE_RESEARCH,
                'assigned_to' => $taskData['assigned_to'] ?? null,
                'priority' => $taskData['priority'] ?? CaseTask::PRIORITY_MEDIUM,
                'due_date' => $taskData['due_date'] ?? null,
                'checklist' => $taskData['checklist'] ?? null,
            ]);

            CaseTimeline::logTaskCreated(
                $caseId,
                $task->id,
                $task->title,
                $task->assigned_to
            );

            // Update case progress
            $case = InvestigationCase::find($caseId);
            $case?->updateProgress();

            return $task;
        });
    }

    /**
     * Update a task
     *
     * @param int $taskId
     * @param array $data
     * @return CaseTask
     */
    public function updateTask(int $taskId, array $data): CaseTask
    {
        $task = CaseTask::findOrFail($taskId);
        $task->update($data);
        return $task->fresh();
    }

    /**
     * Complete a task
     *
     * @param int $taskId
     * @param int|null $completedBy
     * @return CaseTask
     */
    public function completeTask(int $taskId, ?int $completedBy = null): CaseTask
    {
        $task = CaseTask::findOrFail($taskId);
        $task->complete($completedBy);

        CaseTimeline::logTaskCompleted(
            $task->case_id,
            $task->id,
            $task->title
        );

        return $task->fresh();
    }

    /**
     * Reopen a completed task
     *
     * @param int $taskId
     * @return CaseTask
     */
    public function reopenTask(int $taskId): CaseTask
    {
        $task = CaseTask::findOrFail($taskId);
        $task->reopen();
        return $task->fresh();
    }

    /**
     * Delete a task
     *
     * @param int $taskId
     * @return bool
     */
    public function deleteTask(int $taskId): bool
    {
        $task = CaseTask::findOrFail($taskId);
        $caseId = $task->case_id;

        $result = $task->delete();

        // Update case progress
        $case = InvestigationCase::find($caseId);
        $case?->updateProgress();

        return $result;
    }

    /**
     * Update case progress based on task completion
     *
     * @param int $caseId
     * @return int Progress percentage
     */
    public function updateProgress(int $caseId): int
    {
        $case = InvestigationCase::findOrFail($caseId);
        return $case->updateProgress();
    }

    /**
     * Add a timeline entry
     *
     * @param int $caseId
     * @param string $entryType
     * @param array $data
     * @return CaseTimeline
     */
    public function addTimelineEntry(int $caseId, string $entryType, array $data = []): CaseTimeline
    {
        return CaseTimeline::create([
            'case_id' => $caseId,
            'entry_type' => $entryType,
            'entry_data' => $data,
        ]);
    }

    /**
     * Generate a case summary report
     *
     * @param int $caseId
     * @return array
     */
    public function generateCaseReport(int $caseId): array
    {
        $case = InvestigationCase::with([
            'user',
            'leadAnalyst',
            'evidence' => fn ($q) => $q->with('evidence')->verified(),
            'notes' => fn ($q) => $q->root()->orderByPinnedAndDate(),
            'tasks',
            'timeline' => fn ($q) => $q->chronological()->limit(50),
            'networkEntities',
        ])->findOrFail($caseId);

        return [
            'case' => [
                'case_number' => $case->case_number,
                'title' => $case->title,
                'description' => $case->description,
                'objective' => $case->objective,
                'case_type' => InvestigationCase::getCaseTypes()[$case->case_type] ?? $case->case_type,
                'status' => InvestigationCase::getStatuses()[$case->status] ?? $case->status,
                'priority' => InvestigationCase::getPriorities()[$case->priority] ?? $case->priority,
                'classification_level' => InvestigationCase::getClassificationLevels()[$case->classification_level] ?? $case->classification_level,
                'progress' => $case->progress_percentage,
                'owner' => $case->user?->name,
                'lead_analyst' => $case->leadAnalyst?->name,
                'start_date' => $case->start_date?->format('Y-m-d'),
                'due_date' => $case->due_date?->format('Y-m-d'),
                'created_at' => $case->created_at->format('Y-m-d H:i:s'),
                'closed_at' => $case->closed_at?->format('Y-m-d H:i:s'),
            ],
            'statistics' => $case->getStats(),
            'team' => array_map(function ($member) {
                $user = User::find($member['user_id']);
                return [
                    'name' => $user?->name ?? 'Unknown',
                    'role' => InvestigationCase::getTeamRoles()[$member['role']] ?? $member['role'],
                    'joined_at' => $member['joined_at'] ?? null,
                ];
            }, $case->team_members ?? []),
            'evidence_summary' => [
                'total' => $case->evidence->count(),
                'by_type' => $case->evidence->groupBy('evidence_type')
                    ->map(fn ($items) => $items->count()),
                'by_status' => $case->evidence->groupBy('status')
                    ->map(fn ($items) => $items->count()),
            ],
            'tasks_summary' => [
                'total' => $case->tasks->count(),
                'completed' => $case->tasks->where('status', CaseTask::STATUS_COMPLETED)->count(),
                'in_progress' => $case->tasks->where('status', CaseTask::STATUS_IN_PROGRESS)->count(),
                'blocked' => $case->tasks->where('status', CaseTask::STATUS_BLOCKED)->count(),
                'todo' => $case->tasks->where('status', CaseTask::STATUS_TODO)->count(),
                'overdue' => $case->tasks->filter(fn ($t) => $t->isOverdue())->count(),
            ],
            'key_findings' => $case->notes
                ->where('note_type', CaseNote::TYPE_FINDING)
                ->map(fn ($note) => [
                    'title' => $note->title,
                    'content' => $note->getPreview(500),
                    'created_at' => $note->created_at->format('Y-m-d H:i:s'),
                ]),
            'recent_activity' => $case->timeline->map(fn ($entry) => [
                'type' => $entry->entry_type,
                'description' => $entry->getDescription(),
                'occurred_at' => $entry->occurred_at->format('Y-m-d H:i:s'),
            ]),
            'generated_at' => now()->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Close a case with resolution summary
     *
     * @param int $caseId
     * @param string|null $resolution Resolution summary/notes
     * @return InvestigationCase
     */
    public function closeCase(int $caseId, ?string $resolution = null): InvestigationCase
    {
        return DB::transaction(function () use ($caseId, $resolution) {
            $case = InvestigationCase::findOrFail($caseId);
            $oldStatus = $case->status;

            $case->close();

            CaseTimeline::logStatusChange($caseId, $oldStatus, InvestigationCase::STATUS_CLOSED);

            if ($resolution) {
                $this->addNote(
                    $caseId,
                    CaseNote::TYPE_FINDING,
                    $resolution,
                    ['title' => 'Case Resolution Summary', 'is_pinned' => true]
                );
            }

            return $case->fresh();
        });
    }

    /**
     * Archive a case
     *
     * @param int $caseId
     * @return InvestigationCase
     */
    public function archiveCase(int $caseId): InvestigationCase
    {
        $case = InvestigationCase::findOrFail($caseId);
        $oldStatus = $case->status;

        $case->archive();

        CaseTimeline::logStatusChange($caseId, $oldStatus, InvestigationCase::STATUS_ARCHIVED);

        return $case->fresh();
    }

    /**
     * Reopen a closed case
     *
     * @param int $caseId
     * @return InvestigationCase
     */
    public function reopenCase(int $caseId): InvestigationCase
    {
        $case = InvestigationCase::findOrFail($caseId);
        $oldStatus = $case->status;

        $case->reopen();

        CaseTimeline::logStatusChange($caseId, $oldStatus, InvestigationCase::STATUS_ACTIVE);

        return $case->fresh();
    }

    /**
     * Export all case data for archival or GDPR compliance
     *
     * @param int $caseId
     * @param string $format 'json' or 'csv'
     * @return array
     */
    public function exportCase(int $caseId, string $format = 'json'): array
    {
        $case = InvestigationCase::with([
            'user:id,name,email',
            'leadAnalyst:id,name,email',
            'creator:id,name,email',
            'evidence.evidence',
            'evidence.addedBy:id,name',
            'evidence.reviewedBy:id,name',
            'notes.user:id,name',
            'notes.replies',
            'tasks.assignee:id,name',
            'tasks.completer:id,name',
            'tasks.creator:id,name',
            'timeline.creator:id,name',
            'timelineEntries',
            'networkEntities',
        ])->findOrFail($caseId);

        $exportData = [
            'export_metadata' => [
                'exported_at' => now()->toIso8601String(),
                'format' => $format,
                'version' => '1.0',
            ],
            'case' => [
                'uuid' => $case->uuid,
                'case_number' => $case->case_number,
                'title' => $case->title,
                'description' => $case->description,
                'objective' => $case->objective,
                'case_type' => $case->case_type,
                'status' => $case->status,
                'priority' => $case->priority,
                'classification_level' => $case->classification_level,
                'tags' => $case->tags,
                'progress_percentage' => $case->progress_percentage,
                'owner' => [
                    'id' => $case->user?->id,
                    'name' => $case->user?->name,
                    'email' => $case->user?->email,
                ],
                'lead_analyst' => $case->leadAnalyst ? [
                    'id' => $case->leadAnalyst->id,
                    'name' => $case->leadAnalyst->name,
                    'email' => $case->leadAnalyst->email,
                ] : null,
                'team_members' => array_map(function ($member) {
                    $user = User::find($member['user_id']);
                    return [
                        'user_id' => $member['user_id'],
                        'name' => $user?->name,
                        'role' => $member['role'],
                        'joined_at' => $member['joined_at'] ?? null,
                    ];
                }, $case->team_members ?? []),
                'start_date' => $case->start_date?->toDateString(),
                'due_date' => $case->due_date?->toDateString(),
                'started_at' => $case->started_at?->toIso8601String(),
                'closed_at' => $case->closed_at?->toIso8601String(),
                'created_at' => $case->created_at->toIso8601String(),
                'updated_at' => $case->updated_at->toIso8601String(),
            ],
            'evidence' => $case->evidence->map(function ($evidence) {
                return [
                    'id' => $evidence->id,
                    'evidence_type' => $evidence->evidence_type,
                    'evidence_id' => $evidence->evidence_id,
                    'relevance_score' => $evidence->relevance_score,
                    'notes' => $evidence->notes,
                    'status' => $evidence->status,
                    'added_by' => $evidence->addedBy?->name,
                    'added_at' => $evidence->added_at->toIso8601String(),
                    'reviewed_by' => $evidence->reviewedBy?->name,
                    'reviewed_at' => $evidence->reviewed_at?->toIso8601String(),
                ];
            }),
            'notes' => $case->notes->map(function ($note) {
                return [
                    'id' => $note->id,
                    'note_type' => $note->note_type,
                    'title' => $note->title,
                    'content' => $note->content,
                    'is_pinned' => $note->is_pinned,
                    'parent_id' => $note->parent_id,
                    'attachments' => $note->attachments,
                    'mentioned_users' => $note->mentioned_users,
                    'author' => $note->user?->name,
                    'created_at' => $note->created_at->toIso8601String(),
                ];
            }),
            'tasks' => $case->tasks->map(function ($task) {
                return [
                    'uuid' => $task->uuid,
                    'title' => $task->title,
                    'description' => $task->description,
                    'task_type' => $task->task_type,
                    'priority' => $task->priority,
                    'status' => $task->status,
                    'assigned_to' => $task->assignee?->name,
                    'due_date' => $task->due_date?->toDateString(),
                    'checklist' => $task->checklist,
                    'blockers' => $task->blockers,
                    'completed_at' => $task->completed_at?->toIso8601String(),
                    'completed_by' => $task->completer?->name,
                    'created_by' => $task->creator?->name,
                    'created_at' => $task->created_at->toIso8601String(),
                ];
            }),
            'timeline' => $case->timeline->map(function ($entry) {
                return [
                    'entry_type' => $entry->entry_type,
                    'entry_data' => $entry->entry_data,
                    'description' => $entry->getDescription(),
                    'occurred_at' => $entry->occurred_at->toIso8601String(),
                    'created_by' => $entry->creator?->name,
                ];
            }),
            'investigation_timeline' => $case->timelineEntries->map(function ($entry) {
                return [
                    'uuid' => $entry->uuid,
                    'title' => $entry->title,
                    'description' => $entry->description,
                    'type' => $entry->type,
                    'category' => $entry->category,
                    'occurred_at' => $entry->occurred_at->toIso8601String(),
                    'location' => [
                        'latitude' => $entry->latitude,
                        'longitude' => $entry->longitude,
                        'name' => $entry->location_name,
                    ],
                    'confidence' => $entry->confidence,
                    'is_verified' => $entry->is_verified,
                    'sources' => $entry->sources,
                ];
            }),
            'network_entities' => $case->networkEntities->map(function ($entity) {
                return [
                    'uuid' => $entity->uuid,
                    'name' => $entity->name,
                    'type' => $entity->type,
                    'attributes' => $entity->attributes,
                    'identifiers' => $entity->identifiers,
                    'notes' => $entity->notes,
                ];
            }),
        ];

        return $exportData;
    }

    /**
     * Get cases with pagination and filters
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getCases(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $query = InvestigationCase::with(['user:id,name', 'leadAnalyst:id,name']);

        // Apply filters
        if (!empty($filters['status'])) {
            $query->status($filters['status']);
        }

        if (!empty($filters['priority'])) {
            $query->byPriority($filters['priority']);
        }

        if (!empty($filters['case_type'])) {
            $query->byType($filters['case_type']);
        }

        if (!empty($filters['classification_level'])) {
            $query->byClassification($filters['classification_level']);
        }

        if (!empty($filters['user_id'])) {
            $query->forUser($filters['user_id']);
        }

        if (!empty($filters['overdue'])) {
            $query->overdue();
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('case_number', 'like', "%{$search}%");
            });
        }

        // Default ordering
        $sortBy = $filters['sort_by'] ?? 'updated_at';
        $sortDir = $filters['sort_dir'] ?? 'desc';
        $query->orderBy($sortBy, $sortDir);

        return $query->paginate($perPage);
    }

    /**
     * Get case statistics for dashboard
     *
     * @param int|null $userId Filter by user
     * @return array
     */
    public function getDashboardStats(?int $userId = null): array
    {
        $query = InvestigationCase::query();

        if ($userId) {
            $query->forUser($userId);
        }

        $cases = $query->get();

        return [
            'total_cases' => $cases->count(),
            'open_cases' => $cases->whereIn('status', [
                InvestigationCase::STATUS_OPEN,
                InvestigationCase::STATUS_ACTIVE,
                InvestigationCase::STATUS_IN_PROGRESS,
            ])->count(),
            'on_hold_cases' => $cases->where('status', InvestigationCase::STATUS_ON_HOLD)->count(),
            'closed_cases' => $cases->where('status', InvestigationCase::STATUS_CLOSED)->count(),
            'archived_cases' => $cases->where('status', InvestigationCase::STATUS_ARCHIVED)->count(),
            'critical_priority' => $cases->where('priority', InvestigationCase::PRIORITY_CRITICAL)->count(),
            'high_priority' => $cases->where('priority', InvestigationCase::PRIORITY_HIGH)->count(),
            'overdue_cases' => $cases->filter(fn ($c) => $c->isOverdue())->count(),
            'by_type' => $cases->groupBy('case_type')
                ->map(fn ($items) => $items->count()),
            'by_status' => $cases->groupBy('status')
                ->map(fn ($items) => $items->count()),
            'average_progress' => round($cases->whereIn('status', [
                InvestigationCase::STATUS_ACTIVE,
                InvestigationCase::STATUS_IN_PROGRESS,
            ])->avg('progress_percentage') ?? 0),
        ];
    }
}
