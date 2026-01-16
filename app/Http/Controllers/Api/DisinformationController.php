<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DisinformationAnalysis;
use App\Models\DisinformationPattern;
use App\Models\FlaggedContent;
use App\Models\SocialMediaPost;
use App\Services\DisinformationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Disinformation Detection Controller
 *
 * Handles API endpoints for disinformation detection and analysis including:
 * - Content analysis for propaganda, coordinated behavior, fake accounts
 * - Pattern management for detecting known disinformation tactics
 * - Flagged content management and review workflow
 * - Statistics and trending analysis
 */
class DisinformationController extends Controller
{
    public function __construct(
        protected DisinformationService $disinformationService
    ) {}

    // =========================================================================
    // ANALYSIS ENDPOINTS
    // =========================================================================

    /**
     * Trigger analysis on content.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function analyze(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'content_type' => ['required', Rule::in(['event', 'post', 'article'])],
            'content_id' => ['required', 'integer'],
            'analysis_type' => ['nullable', Rule::in(DisinformationAnalysis::getAnalysisTypes())],
        ]);

        try {
            $analysis = $this->disinformationService->analyzeContent(
                $validated['content_type'],
                $validated['content_id'],
                $request->user(),
                $validated['analysis_type'] ?? null
            );

            return $this->success([
                'analysis' => $analysis,
                'message' => 'Analysis initiated successfully',
            ], 201);

        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 422);
        } catch (\Exception $e) {
            return $this->error('Analysis failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * List all analyses with filtering.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = DisinformationAnalysis::query()
            ->with(['creator:id,name', 'reviewer:id,name', 'patterns:id,name,pattern_type']);

        // Filter by analysis type
        if ($request->has('analysis_type')) {
            $query->ofType($request->analysis_type);
        }

        // Filter by status
        if ($request->has('analysis_status')) {
            $query->where('analysis_status', $request->analysis_status);
        }

        // Filter by review status
        if ($request->has('review_status')) {
            $query->where('manual_review_status', $request->review_status);
        }

        // Filter by threat level
        if ($request->has('min_threat')) {
            $query->where('threat_score', '>=', (int) $request->min_threat);
        }
        if ($request->has('max_threat')) {
            $query->where('threat_score', '<=', (int) $request->max_threat);
        }

        // Filter by content type
        if ($request->has('content_type')) {
            $typeMap = [
                'event' => 'App\\Models\\Event',
                'post' => 'App\\Models\\SocialMediaPost',
                'article' => 'App\\Models\\Article',
            ];
            if (isset($typeMap[$request->content_type])) {
                $query->forContentType($typeMap[$request->content_type]);
            }
        }

        // Filter by date range
        if ($request->has('from_date')) {
            $query->where('created_at', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->where('created_at', '<=', $request->to_date);
        }

        // Filter pending review
        if ($request->boolean('pending_review')) {
            $query->pendingReview();
        }

        // Filter high threat
        if ($request->boolean('high_threat')) {
            $query->highThreat();
        }

        // Sort
        $sortField = $request->input('sort_by', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $analyses = $query->paginate($this->perPage);

        return $this->paginated($analyses);
    }

    /**
     * Get analysis results.
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function show(string $uuid): JsonResponse
    {
        $analysis = DisinformationAnalysis::where('uuid', $uuid)
            ->with([
                'creator:id,name',
                'reviewer:id,name',
                'patterns',
                'flaggedContent',
                'analyzable',
            ])
            ->firstOrFail();

        return $this->success($analysis);
    }

    /**
     * Analyze coordinated behavior across multiple posts.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function analyzeCoordinated(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'post_ids' => ['required', 'array', 'min:2'],
            'post_ids.*' => ['integer', 'exists:social_media_posts,id'],
        ]);

        $posts = SocialMediaPost::whereIn('id', $validated['post_ids'])->get();

        $result = $this->disinformationService->detectCoordinatedBehavior($posts);

        return $this->success([
            'coordinated_behavior' => $result,
        ]);
    }

    // =========================================================================
    // FLAG ENDPOINTS
    // =========================================================================

    /**
     * Flag content for review.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function flag(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'analysis_id' => ['required', 'exists:disinformation_analyses,id'],
            'reason' => ['required', Rule::in(FlaggedContent::getFlagReasons())],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            $flagged = $this->disinformationService->flagContent(
                $validated['analysis_id'],
                $validated['reason'],
                $request->user()->id,
                $validated['notes'] ?? null
            );

            return $this->success([
                'flagged_content' => $flagged->load(['analysis', 'flagger:id,name']),
                'message' => 'Content flagged successfully',
            ], 201);

        } catch (\Exception $e) {
            return $this->error('Failed to flag content: ' . $e->getMessage(), 500);
        }
    }

    /**
     * List flagged content.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function flaggedContent(Request $request): JsonResponse
    {
        $query = FlaggedContent::query()
            ->with([
                'analysis:id,uuid,analysis_type,threat_score,confidence_score',
                'flagger:id,name',
                'resolver:id,name',
            ]);

        // Filter by resolution status
        if ($request->has('resolution_status')) {
            $query->where('resolution_status', $request->resolution_status);
        }

        // Filter by priority
        if ($request->has('priority')) {
            $query->byPriority($request->priority);
        }

        // Filter by reason
        if ($request->has('flag_reason')) {
            $query->byReason($request->flag_reason);
        }

        // Filter pending only
        if ($request->boolean('pending_only')) {
            $query->pending();
        }

        // Filter needs action
        if ($request->boolean('needs_action')) {
            $query->needsAction();
        }

        // Filter high priority
        if ($request->boolean('high_priority')) {
            $query->highPriority();
        }

        // Filter by date
        if ($request->has('from_date')) {
            $query->where('flagged_at', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->where('flagged_at', '<=', $request->to_date);
        }

        // Sort
        $sortField = $request->input('sort_by', 'flagged_at');
        $sortDirection = $request->input('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $flagged = $query->paginate($this->perPage);

        return $this->paginated($flagged);
    }

    /**
     * Get specific flagged content.
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function showFlagged(string $uuid): JsonResponse
    {
        $flagged = FlaggedContent::where('uuid', $uuid)
            ->with([
                'analysis.patterns',
                'flagger:id,name',
                'resolver:id,name',
                'appealReviewer:id,name',
                'content',
            ])
            ->firstOrFail();

        return $this->success($flagged);
    }

    // =========================================================================
    // REVIEW ENDPOINTS (Admin)
    // =========================================================================

    /**
     * Manual review of analysis.
     *
     * @param Request $request
     * @param string $uuid
     * @return JsonResponse
     */
    public function review(Request $request, string $uuid): JsonResponse
    {
        $analysis = DisinformationAnalysis::where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'status' => ['required', Rule::in(DisinformationAnalysis::getReviewStatuses())],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $analysis->submitReview(
            $request->user()->id,
            $validated['status'],
            $validated['notes'] ?? null
        );

        return $this->success([
            'analysis' => $analysis->fresh(['reviewer:id,name']),
            'message' => 'Review submitted successfully',
        ]);
    }

    /**
     * Resolve flagged content.
     *
     * @param Request $request
     * @param string $uuid
     * @return JsonResponse
     */
    public function resolveFlagged(Request $request, string $uuid): JsonResponse
    {
        $flagged = FlaggedContent::where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'resolution' => ['required', Rule::in([
                'confirmed_disinformation',
                'false_positive',
                'inconclusive',
            ])],
            'notes' => ['nullable', 'string', 'max:2000'],
            'actions' => ['nullable', 'array'],
            'hide_content' => ['nullable', 'boolean'],
            'remove_content' => ['nullable', 'boolean'],
        ]);

        $userId = $request->user()->id;

        match ($validated['resolution']) {
            'confirmed_disinformation' => $flagged->confirmDisinformation(
                $userId,
                $validated['notes'] ?? null,
                $validated['actions'] ?? null
            ),
            'false_positive' => $flagged->markFalsePositive($userId, $validated['notes'] ?? null),
            'inconclusive' => $flagged->markInconclusive($userId, $validated['notes'] ?? null),
        };

        // Apply visibility actions
        if ($validated['hide_content'] ?? false) {
            $flagged->hideContent();
        }
        if ($validated['remove_content'] ?? false) {
            $flagged->removeContent();
        }

        return $this->success([
            'flagged_content' => $flagged->fresh(['resolver:id,name']),
            'message' => 'Resolution recorded successfully',
        ]);
    }

    /**
     * Handle appeal on flagged content.
     *
     * @param Request $request
     * @param string $uuid
     * @return JsonResponse
     */
    public function handleAppeal(Request $request, string $uuid): JsonResponse
    {
        $flagged = FlaggedContent::where('uuid', $uuid)->firstOrFail();

        if (!$flagged->has_appeal) {
            return $this->error('No appeal exists for this content', 400);
        }

        $validated = $request->validate([
            'decision' => ['required', Rule::in(['uphold', 'overturn'])],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $flagged->update([
            'appeal_reviewed_by' => $request->user()->id,
        ]);

        if ($validated['decision'] === 'overturn') {
            $flagged->markFalsePositive($request->user()->id, $validated['notes'] ?? 'Appeal upheld');
            $flagged->restoreContent();
        } else {
            $flagged->update([
                'resolution_status' => FlaggedContent::STATUS_RESOLVED,
                'resolution_notes' => $validated['notes'] ?? 'Appeal denied',
            ]);
        }

        return $this->success([
            'flagged_content' => $flagged->fresh(),
            'message' => 'Appeal decision recorded',
        ]);
    }

    // =========================================================================
    // PATTERN ENDPOINTS (Admin)
    // =========================================================================

    /**
     * List all patterns.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function patterns(Request $request): JsonResponse
    {
        $query = DisinformationPattern::query();

        // Filter by type
        if ($request->has('pattern_type')) {
            $query->ofType($request->pattern_type);
        }

        // Filter by severity
        if ($request->has('severity')) {
            $query->bySeverity((int) $request->severity);
        }

        // Filter active only
        if ($request->boolean('active_only', true)) {
            $query->active();
        }

        // Search
        if ($request->has('search')) {
            $query->search($request->search);
        }

        // Sort
        $sortField = $request->input('sort_by', 'name');
        $sortDirection = $request->input('sort_direction', 'asc');
        $query->orderBy($sortField, $sortDirection);

        $patterns = $query->paginate($this->perPage);

        return $this->paginated($patterns);
    }

    /**
     * Get specific pattern.
     *
     * @param string $slug
     * @return JsonResponse
     */
    public function showPattern(string $slug): JsonResponse
    {
        $pattern = DisinformationPattern::where('slug', $slug)
            ->with(['createdByUser:id,name', 'updatedByUser:id,name'])
            ->firstOrFail();

        return $this->success($pattern);
    }

    /**
     * Create new pattern.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function storePattern(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'pattern_type' => ['required', Rule::in(DisinformationPattern::getPatternTypes())],
            'detection_rules' => ['required', 'array'],
            'keywords' => ['nullable', 'array'],
            'regex_patterns' => ['nullable', 'array'],
            'severity' => ['required', 'integer', 'min:1', 'max:5'],
            'base_threat_weight' => ['nullable', 'integer', 'min:0', 'max:100'],
            'examples' => ['nullable', 'array'],
            'indicators' => ['nullable', 'array'],
            'counter_indicators' => ['nullable', 'array'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $pattern = DisinformationPattern::create(array_merge($validated, [
            'slug' => Str::slug($validated['name']),
            'created_by' => $request->user()->id,
        ]));

        return $this->success($pattern, 201);
    }

    /**
     * Update pattern.
     *
     * @param Request $request
     * @param string $slug
     * @return JsonResponse
     */
    public function updatePattern(Request $request, string $slug): JsonResponse
    {
        $pattern = DisinformationPattern::where('slug', $slug)->firstOrFail();

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string', 'max:5000'],
            'pattern_type' => ['sometimes', Rule::in(DisinformationPattern::getPatternTypes())],
            'detection_rules' => ['sometimes', 'array'],
            'keywords' => ['nullable', 'array'],
            'regex_patterns' => ['nullable', 'array'],
            'severity' => ['sometimes', 'integer', 'min:1', 'max:5'],
            'base_threat_weight' => ['nullable', 'integer', 'min:0', 'max:100'],
            'examples' => ['nullable', 'array'],
            'indicators' => ['nullable', 'array'],
            'counter_indicators' => ['nullable', 'array'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        // Update slug if name changed
        if (isset($validated['name']) && $validated['name'] !== $pattern->name) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $pattern->update($validated);
        $pattern->incrementVersion($request->user()->id);

        return $this->success($pattern->fresh());
    }

    /**
     * Delete pattern (soft delete).
     *
     * @param string $slug
     * @return JsonResponse
     */
    public function destroyPattern(string $slug): JsonResponse
    {
        $pattern = DisinformationPattern::where('slug', $slug)->firstOrFail();

        $pattern->delete();

        return $this->success(['message' => 'Pattern deleted']);
    }

    /**
     * Toggle pattern active status.
     *
     * @param string $slug
     * @return JsonResponse
     */
    public function togglePattern(string $slug): JsonResponse
    {
        $pattern = DisinformationPattern::where('slug', $slug)->firstOrFail();

        $pattern->update(['is_active' => !$pattern->is_active]);

        return $this->success([
            'is_active' => $pattern->is_active,
            'message' => $pattern->is_active ? 'Pattern activated' : 'Pattern deactivated',
        ]);
    }

    // =========================================================================
    // STATISTICS ENDPOINTS
    // =========================================================================

    /**
     * Get disinformation statistics.
     *
     * @return JsonResponse
     */
    public function statistics(): JsonResponse
    {
        $stats = $this->disinformationService->getStatistics();

        return $this->success($stats);
    }

    /**
     * Get trending disinformation patterns.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function trends(Request $request): JsonResponse
    {
        $days = (int) $request->input('days', 7);
        $days = max(1, min(90, $days)); // Limit to 1-90 days

        $trends = $this->disinformationService->getTrends($days);

        return $this->success($trends);
    }

    // =========================================================================
    // REFERENCE DATA ENDPOINTS
    // =========================================================================

    /**
     * Get analysis types.
     *
     * @return JsonResponse
     */
    public function analysisTypes(): JsonResponse
    {
        $types = [];

        foreach (DisinformationAnalysis::getAnalysisTypes() as $type) {
            $types[] = [
                'id' => $type,
                'name' => DisinformationAnalysis::getAnalysisTypeLabels()[$type] ?? ucfirst($type),
            ];
        }

        return $this->success($types);
    }

    /**
     * Get pattern types.
     *
     * @return JsonResponse
     */
    public function patternTypes(): JsonResponse
    {
        $types = [];

        foreach (DisinformationPattern::getPatternTypes() as $type) {
            $types[] = [
                'id' => $type,
                'name' => DisinformationPattern::getPatternTypeLabels()[$type] ?? ucfirst($type),
            ];
        }

        return $this->success($types);
    }

    /**
     * Get flag reasons.
     *
     * @return JsonResponse
     */
    public function flagReasons(): JsonResponse
    {
        $reasons = [];

        foreach (FlaggedContent::getFlagReasons() as $reason) {
            $reasons[] = [
                'id' => $reason,
                'name' => FlaggedContent::getFlagReasonLabels()[$reason] ?? ucfirst($reason),
            ];
        }

        return $this->success($reasons);
    }

    /**
     * Get resolution statuses.
     *
     * @return JsonResponse
     */
    public function resolutionStatuses(): JsonResponse
    {
        $statuses = [];

        foreach (FlaggedContent::getResolutionStatuses() as $status) {
            $statuses[] = [
                'id' => $status,
                'name' => FlaggedContent::getResolutionStatusLabels()[$status] ?? ucfirst($status),
            ];
        }

        return $this->success($statuses);
    }

    /**
     * Get severity levels.
     *
     * @return JsonResponse
     */
    public function severityLevels(): JsonResponse
    {
        $levels = [];

        foreach (DisinformationPattern::getSeverityLevels() as $level => $name) {
            $levels[] = [
                'level' => $level,
                'name' => $name,
            ];
        }

        return $this->success($levels);
    }

    /**
     * Get priority levels.
     *
     * @return JsonResponse
     */
    public function priorityLevels(): JsonResponse
    {
        $priorities = [];

        foreach (FlaggedContent::getPriorities() as $priority) {
            $priorities[] = [
                'id' => $priority,
                'name' => FlaggedContent::getPriorityLabels()[$priority] ?? ucfirst($priority),
            ];
        }

        return $this->success($priorities);
    }
}
