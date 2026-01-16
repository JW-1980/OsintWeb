<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TrainingAchievement;
use App\Models\TrainingEnvironment;
use App\Models\TrainingSession;
use App\Services\TrainingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * TrainingController
 *
 * API controller for the Training & Simulation Mode (Sandbox).
 * Handles training environments, sessions, achievements, and certificates.
 */
class TrainingController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private readonly TrainingService $trainingService
    ) {
    }

    // =========================================================================
    // Reference Data Endpoints
    // =========================================================================

    /**
     * Get available environment types.
     *
     * @return JsonResponse
     */
    public function environmentTypes(): JsonResponse
    {
        return response()->json([
            'data' => TrainingEnvironment::getEnvironmentTypes(),
        ]);
    }

    /**
     * Get available difficulty levels.
     *
     * @return JsonResponse
     */
    public function difficultyLevels(): JsonResponse
    {
        return response()->json([
            'data' => TrainingEnvironment::getDifficultyLevels(),
        ]);
    }

    /**
     * Get available session statuses.
     *
     * @return JsonResponse
     */
    public function sessionStatuses(): JsonResponse
    {
        return response()->json([
            'data' => TrainingSession::getStatuses(),
        ]);
    }

    /**
     * Get available achievement types.
     *
     * @return JsonResponse
     */
    public function achievementTypes(): JsonResponse
    {
        return response()->json([
            'data' => TrainingAchievement::getAchievementTypes(),
            'icons' => TrainingAchievement::getAchievementIcons(),
            'colors' => TrainingAchievement::getAchievementColors(),
        ]);
    }

    /**
     * Get training statistics for current user.
     *
     * @return JsonResponse
     */
    public function stats(): JsonResponse
    {
        $stats = $this->trainingService->getUserStats(Auth::id());

        return response()->json([
            'data' => $stats,
        ]);
    }

    // =========================================================================
    // Environment CRUD Endpoints
    // =========================================================================

    /**
     * List training environments.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function environments(Request $request): JsonResponse
    {
        $query = TrainingEnvironment::query()
            ->with('creator:id,uuid,name')
            ->withCount(['sessions', 'completedSessions']);

        // Filters
        if ($request->has('type')) {
            $query->ofType($request->input('type'));
        }

        if ($request->has('difficulty')) {
            $query->ofDifficulty($request->input('difficulty'));
        }

        if ($request->boolean('active_only', true)) {
            $query->active();
        }

        if ($request->boolean('public_only', false)) {
            $query->public();
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortField = $request->input('sort', 'created_at');
        $sortDir = $request->input('direction', 'desc');
        $query->orderBy($sortField, $sortDir);

        // Paginate
        $perPage = min($request->integer('per_page', 25), 100);
        $environments = $query->paginate($perPage);

        return response()->json([
            'data' => $environments->items(),
            'meta' => [
                'current_page' => $environments->currentPage(),
                'last_page' => $environments->lastPage(),
                'per_page' => $environments->perPage(),
                'total' => $environments->total(),
            ],
        ]);
    }

    /**
     * Get a single training environment.
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function showEnvironment(string $uuid): JsonResponse
    {
        $environment = TrainingEnvironment::where('uuid', $uuid)
            ->with('creator:id,uuid,name')
            ->withCount(['sessions', 'completedSessions'])
            ->firstOrFail();

        // Get user's session history for this environment
        $userSessions = TrainingSession::where('environment_id', $environment->id)
            ->where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return response()->json([
            'data' => array_merge($environment->toArray(), [
                'average_score' => $environment->average_score,
                'completion_rate' => $environment->completion_rate,
                'user_sessions' => $userSessions,
            ]),
        ]);
    }

    /**
     * Create a new training environment.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createEnvironment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'environment_type' => ['required', Rule::in(array_keys(TrainingEnvironment::getEnvironmentTypes()))],
            'base_scenario' => 'nullable|array',
            'objectives' => 'nullable|array',
            'objectives.*.id' => 'sometimes|string|max:50',
            'objectives.*.title' => 'required_with:objectives|string|max:255',
            'objectives.*.description' => 'nullable|string|max:1000',
            'objectives.*.points' => 'required_with:objectives|integer|min:0|max:1000',
            'success_criteria' => 'nullable|array',
            'success_criteria.max_score' => 'nullable|numeric|min:1',
            'success_criteria.passing_score' => 'nullable|numeric|min:0',
            'time_limit_minutes' => 'nullable|integer|min:1|max:480',
            'difficulty' => ['required', Rule::in(array_keys(TrainingEnvironment::getDifficultyLevels()))],
            'skills_covered' => 'nullable|array',
            'is_active' => 'boolean',
            'is_public' => 'boolean',
        ]);

        $validated['created_by'] = Auth::id();

        $environment = $this->trainingService->createEnvironment($validated);

        return response()->json([
            'message' => 'Training environment created successfully.',
            'data' => $environment->load('creator:id,uuid,name'),
        ], 201);
    }

    /**
     * Update a training environment.
     *
     * @param Request $request
     * @param string $uuid
     * @return JsonResponse
     */
    public function updateEnvironment(Request $request, string $uuid): JsonResponse
    {
        $environment = TrainingEnvironment::where('uuid', $uuid)->firstOrFail();

        // Check authorization
        if ($environment->created_by !== Auth::id() && !Auth::user()->isAdmin()) {
            return response()->json([
                'message' => 'You are not authorized to update this environment.',
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:5000',
            'environment_type' => ['sometimes', Rule::in(array_keys(TrainingEnvironment::getEnvironmentTypes()))],
            'base_scenario' => 'nullable|array',
            'objectives' => 'nullable|array',
            'success_criteria' => 'nullable|array',
            'time_limit_minutes' => 'nullable|integer|min:1|max:480',
            'difficulty' => ['sometimes', Rule::in(array_keys(TrainingEnvironment::getDifficultyLevels()))],
            'skills_covered' => 'nullable|array',
            'is_active' => 'boolean',
            'is_public' => 'boolean',
        ]);

        $environment = $this->trainingService->updateEnvironment($environment, $validated);

        return response()->json([
            'message' => 'Training environment updated successfully.',
            'data' => $environment->load('creator:id,uuid,name'),
        ]);
    }

    /**
     * Delete a training environment.
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function deleteEnvironment(string $uuid): JsonResponse
    {
        $environment = TrainingEnvironment::where('uuid', $uuid)->firstOrFail();

        // Check authorization
        if ($environment->created_by !== Auth::id() && !Auth::user()->isAdmin()) {
            return response()->json([
                'message' => 'You are not authorized to delete this environment.',
            ], 403);
        }

        // Soft delete
        $environment->delete();

        return response()->json([
            'message' => 'Training environment deleted successfully.',
        ]);
    }

    // =========================================================================
    // Session Endpoints
    // =========================================================================

    /**
     * List user's training sessions.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sessions(Request $request): JsonResponse
    {
        $query = TrainingSession::with(['environment:id,uuid,name,environment_type,difficulty'])
            ->where('user_id', Auth::id());

        // Filters
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->has('environment_uuid')) {
            $environment = TrainingEnvironment::where('uuid', $request->input('environment_uuid'))->first();
            if ($environment) {
                $query->where('environment_id', $environment->id);
            }
        }

        // Sort
        $query->orderByDesc('created_at');

        // Paginate
        $perPage = min($request->integer('per_page', 25), 100);
        $sessions = $query->paginate($perPage);

        return response()->json([
            'data' => $sessions->items(),
            'meta' => [
                'current_page' => $sessions->currentPage(),
                'last_page' => $sessions->lastPage(),
                'per_page' => $sessions->perPage(),
                'total' => $sessions->total(),
            ],
        ]);
    }

    /**
     * Start a new training session.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function start(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'environment_uuid' => 'required|string|exists:training_environments,uuid',
        ]);

        $environment = TrainingEnvironment::where('uuid', $validated['environment_uuid'])->firstOrFail();

        try {
            $session = $this->trainingService->startSession($environment->id, Auth::id());

            return response()->json([
                'message' => 'Training session started successfully.',
                'data' => $session->load('environment:id,uuid,name,environment_type,difficulty,objectives,time_limit_minutes'),
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get current session status.
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function status(string $uuid): JsonResponse
    {
        $session = TrainingSession::where('uuid', $uuid)
            ->where('user_id', Auth::id())
            ->with(['environment', 'achievements'])
            ->firstOrFail();

        // Check for expiration
        $this->trainingService->checkAndExpireSession($session->id);
        $session->refresh();

        $objectivesEval = $this->trainingService->evaluateObjectives($session->id);

        return response()->json([
            'data' => array_merge($session->toArray(), [
                'score_percentage' => $session->score_percentage,
                'formatted_time_spent' => $session->formatted_time_spent,
                'remaining_time_seconds' => $session->getRemainingTimeSeconds(),
                'objectives_evaluation' => $objectivesEval,
                'is_expired' => $session->is_expired,
            ]),
        ]);
    }

    /**
     * Pause a training session.
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function pause(string $uuid): JsonResponse
    {
        $session = TrainingSession::where('uuid', $uuid)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        try {
            $session = $this->trainingService->pauseSession($session->id);

            return response()->json([
                'message' => 'Training session paused.',
                'data' => $session,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Resume a paused training session.
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function resume(string $uuid): JsonResponse
    {
        $session = TrainingSession::where('uuid', $uuid)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        try {
            $session = $this->trainingService->resumeSession($session->id);

            return response()->json([
                'message' => 'Training session resumed.',
                'data' => $session->load('environment'),
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Complete a training session.
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function complete(string $uuid): JsonResponse
    {
        $session = TrainingSession::where('uuid', $uuid)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        try {
            $session = $this->trainingService->completeSession($session->id);

            return response()->json([
                'message' => 'Training session completed.',
                'data' => $session->load(['environment', 'achievements']),
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Abandon a training session.
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function abandon(string $uuid): JsonResponse
    {
        $session = TrainingSession::where('uuid', $uuid)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        try {
            $session = $this->trainingService->abandonSession($session->id);

            return response()->json([
                'message' => 'Training session abandoned.',
                'data' => $session,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Reset a session (start fresh in same environment).
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function reset(string $uuid): JsonResponse
    {
        $session = TrainingSession::where('uuid', $uuid)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        try {
            $newSession = $this->trainingService->resetSession($session->id);

            return response()->json([
                'message' => 'Training session reset. New session started.',
                'data' => $newSession->load('environment'),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Log an action in the current session.
     *
     * @param Request $request
     * @param string $uuid
     * @return JsonResponse
     */
    public function logAction(Request $request, string $uuid): JsonResponse
    {
        $session = TrainingSession::where('uuid', $uuid)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $validated = $request->validate([
            'action' => 'required|string|max:100',
            'data' => 'nullable|array',
        ]);

        try {
            $this->trainingService->logAction(
                $session->id,
                $validated['action'],
                $validated['data'] ?? []
            );

            return response()->json([
                'message' => 'Action logged.',
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Complete an objective in the current session.
     *
     * @param Request $request
     * @param string $uuid
     * @return JsonResponse
     */
    public function completeObjective(Request $request, string $uuid): JsonResponse
    {
        $session = TrainingSession::where('uuid', $uuid)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $validated = $request->validate([
            'objective_id' => 'required|string|max:50',
            'data' => 'nullable|array',
        ]);

        if ($session->status !== TrainingSession::STATUS_ACTIVE) {
            return response()->json([
                'message' => 'Objectives can only be completed in active sessions.',
            ], 400);
        }

        $session->completeObjective($validated['objective_id'], $validated['data'] ?? []);
        $session->logAction('objective_completed', [
            'objective_id' => $validated['objective_id'],
        ]);

        // Recalculate score
        $this->trainingService->calculateScore($session->id);
        $session->refresh();

        return response()->json([
            'message' => 'Objective completed.',
            'data' => [
                'score' => $session->score,
                'objectives_completed' => $session->objectives_completed,
            ],
        ]);
    }

    // =========================================================================
    // Achievement Endpoints
    // =========================================================================

    /**
     * Get user's training achievements.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function achievements(Request $request): JsonResponse
    {
        $query = TrainingAchievement::with(['session.environment:id,uuid,name'])
            ->forUser(Auth::id());

        // Filter by type
        if ($request->has('type')) {
            $query->ofType($request->input('type'));
        }

        // Sort
        $query->orderByDesc('awarded_at');

        // Paginate
        $perPage = min($request->integer('per_page', 25), 100);
        $achievements = $query->paginate($perPage);

        return response()->json([
            'data' => $achievements->items(),
            'meta' => [
                'current_page' => $achievements->currentPage(),
                'last_page' => $achievements->lastPage(),
                'per_page' => $achievements->perPage(),
                'total' => $achievements->total(),
            ],
        ]);
    }

    // =========================================================================
    // Leaderboard Endpoint
    // =========================================================================

    /**
     * Get leaderboard for an environment.
     *
     * @param Request $request
     * @param string $uuid
     * @return JsonResponse
     */
    public function leaderboard(Request $request, string $uuid): JsonResponse
    {
        $environment = TrainingEnvironment::where('uuid', $uuid)->firstOrFail();

        $limit = min($request->integer('limit', 10), 100);
        $leaderboard = $this->trainingService->getLeaderboard($environment->id, $limit);

        // Get current user's rank
        $userBestSession = TrainingSession::where('environment_id', $environment->id)
            ->where('user_id', Auth::id())
            ->where('status', TrainingSession::STATUS_COMPLETED)
            ->orderByDesc('score')
            ->first();

        $userRank = null;
        if ($userBestSession) {
            $userRank = TrainingSession::where('environment_id', $environment->id)
                ->where('status', TrainingSession::STATUS_COMPLETED)
                ->where(function ($q) use ($userBestSession) {
                    $q->where('score', '>', $userBestSession->score)
                        ->orWhere(function ($q2) use ($userBestSession) {
                            $q2->where('score', $userBestSession->score)
                                ->where('time_spent_seconds', '<', $userBestSession->time_spent_seconds);
                        });
                })
                ->count() + 1;
        }

        return response()->json([
            'data' => [
                'environment' => [
                    'uuid' => $environment->uuid,
                    'name' => $environment->name,
                ],
                'leaderboard' => $leaderboard,
                'user_rank' => $userRank,
                'user_best_score' => $userBestSession?->score,
            ],
        ]);
    }

    // =========================================================================
    // Certificate Endpoints
    // =========================================================================

    /**
     * Generate or get certificate for a certification session.
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function certificate(string $uuid): JsonResponse
    {
        $session = TrainingSession::where('uuid', $uuid)
            ->where('user_id', Auth::id())
            ->with('environment')
            ->firstOrFail();

        // Check if certificate already exists
        if (isset($session->feedback['certificate'])) {
            return response()->json([
                'data' => $session->feedback['certificate'],
            ]);
        }

        // Try to generate certificate
        $certificate = $this->trainingService->generateCertificate($session->id);

        if (!$certificate) {
            return response()->json([
                'message' => 'Certificate not available. Session must be a completed certification with a passing score.',
            ], 400);
        }

        return response()->json([
            'message' => 'Certificate generated successfully.',
            'data' => $certificate,
        ]);
    }

    /**
     * Verify a certificate by ID.
     *
     * @param string $certificateId
     * @return JsonResponse
     */
    public function verifyCertificate(string $certificateId): JsonResponse
    {
        // Search for the certificate in session feedback
        $session = TrainingSession::with(['environment', 'user:id,uuid,name'])
            ->whereRaw("JSON_EXTRACT(feedback, '$.certificate.certificate_id') = ?", [$certificateId])
            ->first();

        if (!$session || !isset($session->feedback['certificate'])) {
            return response()->json([
                'message' => 'Certificate not found.',
                'valid' => false,
            ], 404);
        }

        $certificate = $session->feedback['certificate'];

        // Check if still valid
        $validUntil = \Carbon\Carbon::parse($certificate['valid_until']);
        $isValid = $validUntil->isFuture();

        return response()->json([
            'valid' => $isValid,
            'data' => [
                'certificate_id' => $certificate['certificate_id'],
                'certification_name' => $certificate['certification_name'],
                'recipient_name' => $certificate['recipient_name'],
                'score_percentage' => $certificate['score_percentage'],
                'issued_at' => $certificate['issued_at'],
                'valid_until' => $certificate['valid_until'],
                'difficulty' => $certificate['difficulty'],
                'expired' => !$isValid,
            ],
        ]);
    }

    // =========================================================================
    // Active Session Check Endpoint
    // =========================================================================

    /**
     * Get user's current active training session (if any).
     *
     * @return JsonResponse
     */
    public function activeSession(): JsonResponse
    {
        $session = TrainingSession::with('environment')
            ->where('user_id', Auth::id())
            ->where('status', TrainingSession::STATUS_ACTIVE)
            ->first();

        if (!$session) {
            return response()->json([
                'data' => null,
                'in_training_mode' => false,
            ]);
        }

        // Check for expiration
        $this->trainingService->checkAndExpireSession($session->id);
        $session->refresh();

        return response()->json([
            'data' => $session->status === TrainingSession::STATUS_ACTIVE ? $session : null,
            'in_training_mode' => $session->status === TrainingSession::STATUS_ACTIVE,
            'remaining_time_seconds' => $session->getRemainingTimeSeconds(),
        ]);
    }
}
