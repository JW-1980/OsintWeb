<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CaseEvidence;
use App\Models\CaseNote;
use App\Models\CaseTask;
use App\Models\CaseTimeline;
use App\Models\InvestigationCase;
use App\Services\CaseManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * CaseController - API endpoints for Case Management Workspaces (Digital War Rooms)
 *
 * Provides comprehensive API for managing OSINT investigation cases:
 * - Full CRUD operations for cases
 * - Team member management
 * - Evidence linking and review
 * - Notes and documentation
 * - Task management
 * - Activity timeline
 * - Reporting and export
 */
class CaseController extends Controller
{
    public function __construct(
        private readonly CaseManagementService $caseService
    ) {}

    // =========================================================================
    // CASE CRUD OPERATIONS
    // =========================================================================

    /**
     * List cases with filtering and pagination
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $filters = [
            'status' => $request->input('status'),
            'priority' => $request->input('priority'),
            'case_type' => $request->input('case_type'),
            'classification_level' => $request->input('classification_level'),
            'user_id' => $request->boolean('my_cases') ? $request->user()->id : null,
            'overdue' => $request->boolean('overdue'),
            'search' => $request->input('search'),
            'sort_by' => $request->input('sort_by', 'updated_at'),
            'sort_dir' => $request->input('sort_dir', 'desc'),
        ];

        $perPage = min($request->input('per_page', 25), 100);

        $cases = $this->caseService->getCases($filters, $perPage);

        return response()->json([
            'data' => $cases->items(),
            'meta' => [
                'current_page' => $cases->currentPage(),
                'last_page' => $cases->lastPage(),
                'per_page' => $cases->perPage(),
                'total' => $cases->total(),
            ],
        ]);
    }

    /**
     * Create a new investigation case
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'objective' => 'nullable|string|max:2000',
            'case_type' => ['nullable', Rule::in(array_keys(InvestigationCase::getCaseTypes()))],
            'priority' => ['nullable', Rule::in(array_keys(InvestigationCase::getPriorities()))],
            'classification_level' => ['nullable', Rule::in(array_keys(InvestigationCase::getClassificationLevels()))],
            'lead_analyst_id' => 'nullable|exists:users,id',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $case = $this->caseService->createCase(
            array_merge($validator->validated(), ['user_id' => $request->user()->id])
        );

        return response()->json([
            'message' => 'Case created successfully',
            'data' => $case->load(['user', 'leadAnalyst']),
        ], 201);
    }

    /**
     * Get a single case by UUID
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function show(string $uuid): JsonResponse
    {
        $case = InvestigationCase::where('uuid', $uuid)
            ->with([
                'user:id,name,email',
                'leadAnalyst:id,name,email',
                'creator:id,name,email',
            ])
            ->firstOrFail();

        return response()->json([
            'data' => array_merge($case->toArray(), [
                'stats' => $case->getStats(),
                'is_overdue' => $case->isOverdue(),
            ]),
        ]);
    }

    /**
     * Update a case
     *
     * @param Request $request
     * @param string $uuid
     * @return JsonResponse
     */
    public function update(Request $request, string $uuid): JsonResponse
    {
        $case = InvestigationCase::where('uuid', $uuid)->firstOrFail();

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:5000',
            'objective' => 'nullable|string|max:2000',
            'case_type' => ['sometimes', Rule::in(array_keys(InvestigationCase::getCaseTypes()))],
            'priority' => ['sometimes', Rule::in(array_keys(InvestigationCase::getPriorities()))],
            'status' => ['sometimes', Rule::in(array_keys(InvestigationCase::getStatuses()))],
            'classification_level' => ['sometimes', Rule::in(array_keys(InvestigationCase::getClassificationLevels()))],
            'lead_analyst_id' => 'nullable|exists:users,id',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $case = $this->caseService->updateCase($case, $validator->validated());

        return response()->json([
            'message' => 'Case updated successfully',
            'data' => $case->load(['user', 'leadAnalyst']),
        ]);
    }

    /**
     * Delete (soft delete) a case
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function destroy(string $uuid): JsonResponse
    {
        $case = InvestigationCase::where('uuid', $uuid)->firstOrFail();
        $case->delete();

        return response()->json([
            'message' => 'Case deleted successfully',
        ]);
    }

    // =========================================================================
    // CASE STATUS ACTIONS
    // =========================================================================

    /**
     * Start/activate a case
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function start(string $uuid): JsonResponse
    {
        $case = InvestigationCase::where('uuid', $uuid)->firstOrFail();
        $oldStatus = $case->status;
        $case->start();

        CaseTimeline::logStatusChange($case->id, $oldStatus, $case->status);

        return response()->json([
            'message' => 'Case started',
            'data' => $case->fresh(),
        ]);
    }

    /**
     * Close a case
     *
     * @param Request $request
     * @param string $uuid
     * @return JsonResponse
     */
    public function close(Request $request, string $uuid): JsonResponse
    {
        $case = InvestigationCase::where('uuid', $uuid)->firstOrFail();

        $case = $this->caseService->closeCase(
            $case->id,
            $request->input('resolution')
        );

        return response()->json([
            'message' => 'Case closed',
            'data' => $case,
        ]);
    }

    /**
     * Reopen a closed case
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function reopen(string $uuid): JsonResponse
    {
        $case = InvestigationCase::where('uuid', $uuid)->firstOrFail();

        $case = $this->caseService->reopenCase($case->id);

        return response()->json([
            'message' => 'Case reopened',
            'data' => $case,
        ]);
    }

    /**
     * Archive a case
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function archive(string $uuid): JsonResponse
    {
        $case = InvestigationCase::where('uuid', $uuid)->firstOrFail();

        $case = $this->caseService->archiveCase($case->id);

        return response()->json([
            'message' => 'Case archived',
            'data' => $case,
        ]);
    }

    // =========================================================================
    // TEAM MANAGEMENT
    // =========================================================================

    /**
     * Get team members for a case
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function team(string $uuid): JsonResponse
    {
        $case = InvestigationCase::where('uuid', $uuid)
            ->with(['user:id,name,email', 'leadAnalyst:id,name,email'])
            ->firstOrFail();

        $members = collect($case->team_members ?? [])->map(function ($member) {
            $user = \App\Models\User::find($member['user_id']);
            return [
                'user_id' => $member['user_id'],
                'name' => $user?->name,
                'email' => $user?->email,
                'role' => $member['role'],
                'role_label' => InvestigationCase::getTeamRoles()[$member['role']] ?? $member['role'],
                'joined_at' => $member['joined_at'] ?? null,
            ];
        });

        return response()->json([
            'data' => [
                'owner' => $case->user,
                'lead_analyst' => $case->leadAnalyst,
                'team_members' => $members,
            ],
        ]);
    }

    /**
     * Add a team member
     *
     * @param Request $request
     * @param string $uuid
     * @return JsonResponse
     */
    public function addTeamMember(Request $request, string $uuid): JsonResponse
    {
        $case = InvestigationCase::where('uuid', $uuid)->firstOrFail();

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'role' => ['required', Rule::in(array_keys(InvestigationCase::getTeamRoles()))],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $case = $this->caseService->addTeamMember(
            $case->id,
            $request->input('user_id'),
            $request->input('role')
        );

        return response()->json([
            'message' => 'Team member added',
            'data' => $case,
        ]);
    }

    /**
     * Remove a team member
     *
     * @param string $uuid
     * @param int $userId
     * @return JsonResponse
     */
    public function removeTeamMember(string $uuid, int $userId): JsonResponse
    {
        $case = InvestigationCase::where('uuid', $uuid)->firstOrFail();

        $case = $this->caseService->removeTeamMember($case->id, $userId);

        return response()->json([
            'message' => 'Team member removed',
            'data' => $case,
        ]);
    }

    /**
     * Update a team member's role
     *
     * @param Request $request
     * @param string $uuid
     * @param int $userId
     * @return JsonResponse
     */
    public function updateTeamMemberRole(Request $request, string $uuid, int $userId): JsonResponse
    {
        $case = InvestigationCase::where('uuid', $uuid)->firstOrFail();

        $validator = Validator::make($request->all(), [
            'role' => ['required', Rule::in(array_keys(InvestigationCase::getTeamRoles()))],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $case = $this->caseService->updateTeamMemberRole(
            $case->id,
            $userId,
            $request->input('role')
        );

        return response()->json([
            'message' => 'Team member role updated',
            'data' => $case,
        ]);
    }

    // =========================================================================
    // EVIDENCE MANAGEMENT
    // =========================================================================

    /**
     * Get evidence linked to a case
     *
     * @param Request $request
     * @param string $uuid
     * @return JsonResponse
     */
    public function evidence(Request $request, string $uuid): JsonResponse
    {
        $case = InvestigationCase::where('uuid', $uuid)->firstOrFail();

        $query = CaseEvidence::forCase($case->id)
            ->with(['addedBy:id,name', 'reviewedBy:id,name']);

        if ($request->has('status')) {
            $query->status($request->input('status'));
        }

        if ($request->has('evidence_type')) {
            $query->ofType($request->input('evidence_type'));
        }

        if ($request->has('min_relevance')) {
            $query->minRelevance((int) $request->input('min_relevance'));
        }

        $evidence = $query->byRelevance()->get();

        return response()->json([
            'data' => $evidence,
        ]);
    }

    /**
     * Link evidence to a case
     *
     * @param Request $request
     * @param string $uuid
     * @return JsonResponse
     */
    public function linkEvidence(Request $request, string $uuid): JsonResponse
    {
        $case = InvestigationCase::where('uuid', $uuid)->firstOrFail();

        $validator = Validator::make($request->all(), [
            'evidence_type' => 'required|string',
            'evidence_id' => 'required|integer',
            'notes' => 'nullable|string|max:2000',
            'relevance_score' => 'nullable|integer|min:1|max:5',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $evidence = $this->caseService->linkEvidence(
            $case->id,
            $request->input('evidence_type'),
            $request->input('evidence_id'),
            $request->input('notes'),
            $request->input('relevance_score', CaseEvidence::RELEVANCE_MEDIUM)
        );

        return response()->json([
            'message' => 'Evidence linked successfully',
            'data' => $evidence->load(['addedBy']),
        ], 201);
    }

    /**
     * Update evidence metadata
     *
     * @param Request $request
     * @param string $uuid
     * @param int $evidenceId
     * @return JsonResponse
     */
    public function updateEvidence(Request $request, string $uuid, int $evidenceId): JsonResponse
    {
        InvestigationCase::where('uuid', $uuid)->firstOrFail();

        $validator = Validator::make($request->all(), [
            'notes' => 'nullable|string|max:2000',
            'relevance_score' => 'nullable|integer|min:1|max:5',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $evidence = $this->caseService->updateEvidence($evidenceId, $validator->validated());

        return response()->json([
            'message' => 'Evidence updated',
            'data' => $evidence,
        ]);
    }

    /**
     * Review evidence (verify, dispute, exclude)
     *
     * @param Request $request
     * @param string $uuid
     * @param int $evidenceId
     * @return JsonResponse
     */
    public function reviewEvidence(Request $request, string $uuid, int $evidenceId): JsonResponse
    {
        InvestigationCase::where('uuid', $uuid)->firstOrFail();

        $validator = Validator::make($request->all(), [
            'status' => ['required', Rule::in(array_keys(CaseEvidence::getStatuses()))],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $evidence = $this->caseService->reviewEvidence(
            $evidenceId,
            $request->input('status'),
            $request->user()->id
        );

        return response()->json([
            'message' => 'Evidence reviewed',
            'data' => $evidence,
        ]);
    }

    /**
     * Unlink evidence from a case
     *
     * @param string $uuid
     * @param int $evidenceId
     * @return JsonResponse
     */
    public function unlinkEvidence(string $uuid, int $evidenceId): JsonResponse
    {
        InvestigationCase::where('uuid', $uuid)->firstOrFail();

        $this->caseService->unlinkEvidence($evidenceId);

        return response()->json([
            'message' => 'Evidence unlinked',
        ]);
    }

    // =========================================================================
    // NOTES MANAGEMENT
    // =========================================================================

    /**
     * Get notes for a case
     *
     * @param Request $request
     * @param string $uuid
     * @return JsonResponse
     */
    public function notes(Request $request, string $uuid): JsonResponse
    {
        $case = InvestigationCase::where('uuid', $uuid)->firstOrFail();

        $query = CaseNote::forCase($case->id)
            ->with(['user:id,name', 'replies' => fn ($q) => $q->with('user:id,name')]);

        if ($request->has('note_type')) {
            $query->ofType($request->input('note_type'));
        }

        if ($request->boolean('root_only', true)) {
            $query->root();
        }

        if ($request->boolean('pinned_only')) {
            $query->pinned();
        }

        $notes = $query->orderByPinnedAndDate()->get();

        return response()->json([
            'data' => $notes,
        ]);
    }

    /**
     * Add a note to a case
     *
     * @param Request $request
     * @param string $uuid
     * @return JsonResponse
     */
    public function addNote(Request $request, string $uuid): JsonResponse
    {
        $case = InvestigationCase::where('uuid', $uuid)->firstOrFail();

        $validator = Validator::make($request->all(), [
            'note_type' => ['required', Rule::in(array_keys(CaseNote::getNoteTypes()))],
            'content' => 'required|string|max:50000',
            'title' => 'nullable|string|max:255',
            'is_pinned' => 'nullable|boolean',
            'parent_id' => 'nullable|exists:case_notes,id',
            'attachments' => 'nullable|array',
            'mentioned_users' => 'nullable|array',
            'mentioned_users.*' => 'integer|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $note = $this->caseService->addNote(
            $case->id,
            $request->input('note_type'),
            $request->input('content'),
            $validator->validated()
        );

        return response()->json([
            'message' => 'Note added successfully',
            'data' => $note->load('user'),
        ], 201);
    }

    /**
     * Update a note
     *
     * @param Request $request
     * @param string $uuid
     * @param int $noteId
     * @return JsonResponse
     */
    public function updateNote(Request $request, string $uuid, int $noteId): JsonResponse
    {
        InvestigationCase::where('uuid', $uuid)->firstOrFail();

        $validator = Validator::make($request->all(), [
            'content' => 'sometimes|string|max:50000',
            'title' => 'nullable|string|max:255',
            'is_pinned' => 'nullable|boolean',
            'attachments' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $note = $this->caseService->updateNote($noteId, $validator->validated());

        return response()->json([
            'message' => 'Note updated',
            'data' => $note,
        ]);
    }

    /**
     * Delete a note
     *
     * @param string $uuid
     * @param int $noteId
     * @return JsonResponse
     */
    public function deleteNote(string $uuid, int $noteId): JsonResponse
    {
        InvestigationCase::where('uuid', $uuid)->firstOrFail();

        $this->caseService->deleteNote($noteId);

        return response()->json([
            'message' => 'Note deleted',
        ]);
    }

    /**
     * Toggle pin status of a note
     *
     * @param string $uuid
     * @param int $noteId
     * @return JsonResponse
     */
    public function toggleNotePin(string $uuid, int $noteId): JsonResponse
    {
        InvestigationCase::where('uuid', $uuid)->firstOrFail();

        $note = CaseNote::findOrFail($noteId);
        $note->togglePin();

        return response()->json([
            'message' => $note->is_pinned ? 'Note pinned' : 'Note unpinned',
            'data' => $note,
        ]);
    }

    // =========================================================================
    // TASK MANAGEMENT
    // =========================================================================

    /**
     * Get tasks for a case
     *
     * @param Request $request
     * @param string $uuid
     * @return JsonResponse
     */
    public function tasks(Request $request, string $uuid): JsonResponse
    {
        $case = InvestigationCase::where('uuid', $uuid)->firstOrFail();

        $query = CaseTask::forCase($case->id)
            ->with(['assignee:id,name', 'creator:id,name']);

        if ($request->has('status')) {
            $query->status($request->input('status'));
        }

        if ($request->has('task_type')) {
            $query->ofType($request->input('task_type'));
        }

        if ($request->has('assigned_to')) {
            $query->assignedTo((int) $request->input('assigned_to'));
        }

        if ($request->boolean('overdue')) {
            $query->overdue();
        }

        $tasks = $query->orderByPriority()->get();

        return response()->json([
            'data' => $tasks,
        ]);
    }

    /**
     * Create a task
     *
     * @param Request $request
     * @param string $uuid
     * @return JsonResponse
     */
    public function createTask(Request $request, string $uuid): JsonResponse
    {
        $case = InvestigationCase::where('uuid', $uuid)->firstOrFail();

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'task_type' => ['nullable', Rule::in(array_keys(CaseTask::getTaskTypes()))],
            'assigned_to' => 'nullable|exists:users,id',
            'priority' => ['nullable', Rule::in(array_keys(CaseTask::getPriorities()))],
            'due_date' => 'nullable|date',
            'checklist' => 'nullable|array',
            'checklist.*.text' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $task = $this->caseService->createTask($case->id, $validator->validated());

        return response()->json([
            'message' => 'Task created successfully',
            'data' => $task->load(['assignee', 'creator']),
        ], 201);
    }

    /**
     * Update a task
     *
     * @param Request $request
     * @param string $uuid
     * @param string $taskUuid
     * @return JsonResponse
     */
    public function updateTask(Request $request, string $uuid, string $taskUuid): JsonResponse
    {
        InvestigationCase::where('uuid', $uuid)->firstOrFail();
        $task = CaseTask::where('uuid', $taskUuid)->firstOrFail();

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:5000',
            'task_type' => ['sometimes', Rule::in(array_keys(CaseTask::getTaskTypes()))],
            'assigned_to' => 'nullable|exists:users,id',
            'priority' => ['sometimes', Rule::in(array_keys(CaseTask::getPriorities()))],
            'status' => ['sometimes', Rule::in(array_keys(CaseTask::getStatuses()))],
            'due_date' => 'nullable|date',
            'checklist' => 'nullable|array',
            'blockers' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $task = $this->caseService->updateTask($task->id, $validator->validated());

        return response()->json([
            'message' => 'Task updated',
            'data' => $task,
        ]);
    }

    /**
     * Complete a task
     *
     * @param Request $request
     * @param string $uuid
     * @param string $taskUuid
     * @return JsonResponse
     */
    public function completeTask(Request $request, string $uuid, string $taskUuid): JsonResponse
    {
        InvestigationCase::where('uuid', $uuid)->firstOrFail();
        $task = CaseTask::where('uuid', $taskUuid)->firstOrFail();

        $task = $this->caseService->completeTask($task->id, $request->user()->id);

        return response()->json([
            'message' => 'Task completed',
            'data' => $task,
        ]);
    }

    /**
     * Reopen a completed task
     *
     * @param string $uuid
     * @param string $taskUuid
     * @return JsonResponse
     */
    public function reopenTask(string $uuid, string $taskUuid): JsonResponse
    {
        InvestigationCase::where('uuid', $uuid)->firstOrFail();
        $task = CaseTask::where('uuid', $taskUuid)->firstOrFail();

        $task = $this->caseService->reopenTask($task->id);

        return response()->json([
            'message' => 'Task reopened',
            'data' => $task,
        ]);
    }

    /**
     * Delete a task
     *
     * @param string $uuid
     * @param string $taskUuid
     * @return JsonResponse
     */
    public function deleteTask(string $uuid, string $taskUuid): JsonResponse
    {
        InvestigationCase::where('uuid', $uuid)->firstOrFail();
        $task = CaseTask::where('uuid', $taskUuid)->firstOrFail();

        $this->caseService->deleteTask($task->id);

        return response()->json([
            'message' => 'Task deleted',
        ]);
    }

    // =========================================================================
    // TIMELINE
    // =========================================================================

    /**
     * Get activity timeline for a case
     *
     * @param Request $request
     * @param string $uuid
     * @return JsonResponse
     */
    public function timeline(Request $request, string $uuid): JsonResponse
    {
        $case = InvestigationCase::where('uuid', $uuid)->firstOrFail();

        $query = CaseTimeline::forCase($case->id)
            ->with('creator:id,name');

        if ($request->has('entry_type')) {
            $types = is_array($request->input('entry_type'))
                ? $request->input('entry_type')
                : [$request->input('entry_type')];
            $query->ofTypes($types);
        }

        if ($request->has('from')) {
            $query->where('occurred_at', '>=', $request->input('from'));
        }

        if ($request->has('to')) {
            $query->where('occurred_at', '<=', $request->input('to'));
        }

        $limit = min($request->input('limit', 50), 200);
        $entries = $query->chronological()->limit($limit)->get();

        // Add description for each entry
        $entries = $entries->map(function ($entry) {
            return array_merge($entry->toArray(), [
                'description' => $entry->getDescription(),
                'icon' => $entry->getIcon(),
            ]);
        });

        return response()->json([
            'data' => $entries,
        ]);
    }

    /**
     * Add a comment to the timeline
     *
     * @param Request $request
     * @param string $uuid
     * @return JsonResponse
     */
    public function addTimelineComment(Request $request, string $uuid): JsonResponse
    {
        $case = InvestigationCase::where('uuid', $uuid)->firstOrFail();

        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $entry = CaseTimeline::logComment($case->id, $request->input('message'));

        return response()->json([
            'message' => 'Comment added',
            'data' => array_merge($entry->load('creator')->toArray(), [
                'description' => $entry->getDescription(),
            ]),
        ], 201);
    }

    // =========================================================================
    // REPORTING & EXPORT
    // =========================================================================

    /**
     * Generate a case summary report
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function report(string $uuid): JsonResponse
    {
        $case = InvestigationCase::where('uuid', $uuid)->firstOrFail();

        $report = $this->caseService->generateCaseReport($case->id);

        return response()->json([
            'data' => $report,
        ]);
    }

    /**
     * Export case data
     *
     * @param Request $request
     * @param string $uuid
     * @return JsonResponse
     */
    public function export(Request $request, string $uuid): JsonResponse
    {
        $case = InvestigationCase::where('uuid', $uuid)->firstOrFail();

        $format = $request->input('format', 'json');

        $exportData = $this->caseService->exportCase($case->id, $format);

        return response()->json([
            'data' => $exportData,
        ]);
    }

    /**
     * Get dashboard statistics
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function stats(Request $request): JsonResponse
    {
        $userId = $request->boolean('my_stats') ? $request->user()->id : null;

        $stats = $this->caseService->getDashboardStats($userId);

        return response()->json([
            'data' => $stats,
        ]);
    }

    // =========================================================================
    // REFERENCE DATA
    // =========================================================================

    /**
     * Get available case types
     *
     * @return JsonResponse
     */
    public function caseTypes(): JsonResponse
    {
        return response()->json([
            'data' => collect(InvestigationCase::getCaseTypes())->map(fn ($label, $value) => [
                'value' => $value,
                'label' => $label,
            ])->values(),
        ]);
    }

    /**
     * Get available priorities
     *
     * @return JsonResponse
     */
    public function priorities(): JsonResponse
    {
        return response()->json([
            'data' => collect(InvestigationCase::getPriorities())->map(fn ($label, $value) => [
                'value' => $value,
                'label' => $label,
            ])->values(),
        ]);
    }

    /**
     * Get available statuses
     *
     * @return JsonResponse
     */
    public function statuses(): JsonResponse
    {
        return response()->json([
            'data' => collect(InvestigationCase::getStatuses())->map(fn ($label, $value) => [
                'value' => $value,
                'label' => $label,
            ])->values(),
        ]);
    }

    /**
     * Get available classification levels
     *
     * @return JsonResponse
     */
    public function classificationLevels(): JsonResponse
    {
        return response()->json([
            'data' => collect(InvestigationCase::getClassificationLevels())->map(fn ($label, $value) => [
                'value' => $value,
                'label' => $label,
            ])->values(),
        ]);
    }

    /**
     * Get available team roles
     *
     * @return JsonResponse
     */
    public function teamRoles(): JsonResponse
    {
        return response()->json([
            'data' => collect(InvestigationCase::getTeamRoles())->map(fn ($label, $value) => [
                'value' => $value,
                'label' => $label,
            ])->values(),
        ]);
    }

    /**
     * Get available note types
     *
     * @return JsonResponse
     */
    public function noteTypes(): JsonResponse
    {
        return response()->json([
            'data' => collect(CaseNote::getNoteTypes())->map(fn ($label, $value) => [
                'value' => $value,
                'label' => $label,
            ])->values(),
        ]);
    }

    /**
     * Get available task types
     *
     * @return JsonResponse
     */
    public function taskTypes(): JsonResponse
    {
        return response()->json([
            'data' => collect(CaseTask::getTaskTypes())->map(fn ($label, $value) => [
                'value' => $value,
                'label' => $label,
            ])->values(),
        ]);
    }

    /**
     * Get available task statuses
     *
     * @return JsonResponse
     */
    public function taskStatuses(): JsonResponse
    {
        return response()->json([
            'data' => collect(CaseTask::getStatuses())->map(fn ($label, $value) => [
                'value' => $value,
                'label' => $label,
            ])->values(),
        ]);
    }

    /**
     * Get available evidence statuses
     *
     * @return JsonResponse
     */
    public function evidenceStatuses(): JsonResponse
    {
        return response()->json([
            'data' => collect(CaseEvidence::getStatuses())->map(fn ($label, $value) => [
                'value' => $value,
                'label' => $label,
            ])->values(),
        ]);
    }

    /**
     * Get supported evidence types
     *
     * @return JsonResponse
     */
    public function evidenceTypes(): JsonResponse
    {
        return response()->json([
            'data' => collect(CaseEvidence::getSupportedTypes())->map(fn ($class, $type) => [
                'type' => $type,
                'model' => $class,
            ])->values(),
        ]);
    }

    /**
     * Get timeline entry types
     *
     * @return JsonResponse
     */
    public function timelineEntryTypes(): JsonResponse
    {
        return response()->json([
            'data' => collect(CaseTimeline::getEntryTypes())->map(fn ($label, $value) => [
                'value' => $value,
                'label' => $label,
            ])->values(),
        ]);
    }
}
