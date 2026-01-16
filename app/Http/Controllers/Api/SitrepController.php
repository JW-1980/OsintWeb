<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sitrep;
use App\Models\SitrepTemplate;
use App\Services\SitrepService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * Controller for SITREP (Situation Report) management API endpoints.
 *
 * Provides endpoints for creating, managing, and exporting situation reports
 * from templates with AI-powered summary generation.
 */
class SitrepController extends Controller
{
    public function __construct(
        protected SitrepService $sitrepService
    ) {}

    // =========================================================================
    // TEMPLATE MANAGEMENT
    // =========================================================================

    /**
     * List all accessible templates.
     *
     * GET /api/sitreps/templates
     *
     * Query Parameters:
     * - type: Filter by template type
     * - search: Search by name or description
     * - per_page: Items per page (default: 15)
     */
    public function templates(Request $request): JsonResponse
    {
        $query = SitrepTemplate::with('creator:id,name')
            ->accessibleBy($request->user());

        if ($request->has('type')) {
            $query->type($request->type);
        }

        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('description', 'like', "%{$request->search}%");
            });
        }

        $templates = $query->orderBy('is_default', 'desc')
            ->orderBy('name')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'data' => $templates->map(fn ($t) => $this->formatTemplate($t)),
            'meta' => [
                'current_page' => $templates->currentPage(),
                'last_page' => $templates->lastPage(),
                'per_page' => $templates->perPage(),
                'total' => $templates->total(),
            ],
        ]);
    }

    /**
     * Create a new template.
     *
     * POST /api/sitreps/templates
     */
    public function storeTemplate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'template_type' => ['required', Rule::in(array_keys(SitrepTemplate::getTemplateTypes()))],
            'sections' => 'nullable|array',
            'sections.*.title' => 'required|string|max:255',
            'sections.*.type' => ['required', Rule::in(array_keys(SitrepTemplate::getSectionTypes()))],
            'sections.*.order' => 'integer|min:0',
            'sections.*.query_config' => 'nullable|array',
            'header_config' => 'nullable|array',
            'footer_config' => 'nullable|array',
            'styling' => 'nullable|array',
            'is_public' => 'boolean',
        ]);

        $template = SitrepTemplate::create([
            ...$validated,
            'header_config' => $validated['header_config'] ?? SitrepTemplate::getDefaultHeaderConfig(),
            'footer_config' => $validated['footer_config'] ?? SitrepTemplate::getDefaultFooterConfig(),
            'styling' => $validated['styling'] ?? SitrepTemplate::getDefaultStyling(),
            'created_by' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Template created successfully.',
            'data' => $this->formatTemplate($template->fresh()),
        ], 201);
    }

    /**
     * Get a specific template.
     *
     * GET /api/sitreps/templates/{uuid}
     */
    public function showTemplate(Request $request, string $uuid): JsonResponse
    {
        $template = SitrepTemplate::where('uuid', $uuid)
            ->accessibleBy($request->user())
            ->firstOrFail();

        return response()->json([
            'data' => $this->formatTemplate($template, true),
        ]);
    }

    /**
     * Update a template.
     *
     * PUT /api/sitreps/templates/{uuid}
     */
    public function updateTemplate(Request $request, string $uuid): JsonResponse
    {
        $template = SitrepTemplate::where('uuid', $uuid)->firstOrFail();

        // Check ownership
        if ($template->created_by !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Access denied.'], 403);
        }

        $validated = $request->validate([
            'name' => 'string|max:255',
            'description' => 'nullable|string',
            'template_type' => [Rule::in(array_keys(SitrepTemplate::getTemplateTypes()))],
            'sections' => 'nullable|array',
            'header_config' => 'nullable|array',
            'footer_config' => 'nullable|array',
            'styling' => 'nullable|array',
            'is_default' => 'boolean',
            'is_public' => 'boolean',
        ]);

        $template->update($validated);

        return response()->json([
            'message' => 'Template updated successfully.',
            'data' => $this->formatTemplate($template->fresh()),
        ]);
    }

    /**
     * Delete a template.
     *
     * DELETE /api/sitreps/templates/{uuid}
     */
    public function destroyTemplate(Request $request, string $uuid): JsonResponse
    {
        $template = SitrepTemplate::where('uuid', $uuid)->firstOrFail();

        // Check ownership
        if ($template->created_by !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Access denied.'], 403);
        }

        // Check if template is in use
        if ($template->sitreps()->exists()) {
            return response()->json([
                'message' => 'Cannot delete template that is in use by sitreps.',
            ], 409);
        }

        $template->delete();

        return response()->json([
            'message' => 'Template deleted successfully.',
        ]);
    }

    // =========================================================================
    // SITREP CRUD
    // =========================================================================

    /**
     * List sitreps.
     *
     * GET /api/sitreps
     *
     * Query Parameters:
     * - status: Filter by status (draft, review, approved, published, archived)
     * - type: Filter by report type
     * - classification: Filter by classification level
     * - search: Search by title or summary
     * - date_from: Filter by period start
     * - date_to: Filter by period end
     * - per_page: Items per page (default: 15)
     */
    public function index(Request $request): JsonResponse
    {
        $query = Sitrep::with(['template:id,uuid,name', 'creator:id,name'])
            ->accessibleBy($request->user());

        if ($request->has('status')) {
            $query->status($request->status);
        }

        if ($request->has('type')) {
            $query->type($request->type);
        }

        if ($request->has('classification')) {
            $query->classification($request->classification);
        }

        if ($request->has('search')) {
            $query->search($request->search);
        }

        if ($request->has('date_from') && $request->has('date_to')) {
            $query->dateRange($request->date_from, $request->date_to);
        }

        $sitreps = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'data' => $sitreps->map(fn ($s) => $this->formatSitrep($s)),
            'meta' => [
                'current_page' => $sitreps->currentPage(),
                'last_page' => $sitreps->lastPage(),
                'per_page' => $sitreps->perPage(),
                'total' => $sitreps->total(),
            ],
        ]);
    }

    /**
     * Create a new sitrep manually.
     *
     * POST /api/sitreps
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'template_id' => 'nullable|exists:sitrep_templates,id',
            'report_type' => ['required', Rule::in(array_keys(Sitrep::getReportTypes()))],
            'classification_level' => ['required', Rule::in(array_keys(Sitrep::getClassificationLevels()))],
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'summary' => 'nullable|string',
            'key_findings' => 'nullable|array',
            'recommendations' => 'nullable|array',
        ]);

        $sitrep = Sitrep::create([
            ...$validated,
            'status' => Sitrep::STATUS_DRAFT,
            'created_by' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'SITREP created successfully.',
            'data' => $this->formatSitrep($sitrep->fresh(['template', 'creator'])),
        ], 201);
    }

    /**
     * Get a specific sitrep.
     *
     * GET /api/sitreps/{uuid}
     */
    public function show(Request $request, string $uuid): JsonResponse
    {
        $sitrep = Sitrep::with(['template', 'creator:id,name', 'approver:id,name'])
            ->where('uuid', $uuid)
            ->accessibleBy($request->user())
            ->firstOrFail();

        return response()->json([
            'data' => $this->formatSitrep($sitrep, true),
        ]);
    }

    /**
     * Update a sitrep.
     *
     * PUT /api/sitreps/{uuid}
     */
    public function update(Request $request, string $uuid): JsonResponse
    {
        $sitrep = Sitrep::where('uuid', $uuid)->firstOrFail();

        // Check access
        if ($sitrep->created_by !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Access denied.'], 403);
        }

        // Cannot edit published sitreps
        if ($sitrep->isPublished()) {
            return response()->json([
                'message' => 'Cannot edit published sitreps.',
            ], 409);
        }

        $validated = $request->validate([
            'title' => 'string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'classification_level' => [Rule::in(array_keys(Sitrep::getClassificationLevels()))],
            'period_start' => 'date',
            'period_end' => 'date|after_or_equal:period_start',
            'summary' => 'nullable|string',
            'key_findings' => 'nullable|array',
            'recommendations' => 'nullable|array',
            'content_sections' => 'nullable|array',
        ]);

        $sitrep->update($validated);

        return response()->json([
            'message' => 'SITREP updated successfully.',
            'data' => $this->formatSitrep($sitrep->fresh(['template', 'creator'])),
        ]);
    }

    /**
     * Delete a sitrep.
     *
     * DELETE /api/sitreps/{uuid}
     */
    public function destroy(Request $request, string $uuid): JsonResponse
    {
        $sitrep = Sitrep::where('uuid', $uuid)->firstOrFail();

        // Check access
        if ($sitrep->created_by !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Access denied.'], 403);
        }

        // Cannot delete published sitreps (archive instead)
        if ($sitrep->isPublished()) {
            return response()->json([
                'message' => 'Cannot delete published sitreps. Archive them instead.',
            ], 409);
        }

        // Delete generated files
        $files = $sitrep->generated_files ?? [];
        foreach ($files as $format => $fileData) {
            if (isset($fileData['path'])) {
                Storage::disk('public')->delete($fileData['path']);
            }
        }

        $sitrep->delete();

        return response()->json([
            'message' => 'SITREP deleted successfully.',
        ]);
    }

    // =========================================================================
    // GENERATION AND PREVIEW
    // =========================================================================

    /**
     * Generate a sitrep from a template.
     *
     * POST /api/sitreps/generate
     */
    public function generate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'template_id' => 'required|exists:sitrep_templates,id',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'classification_level' => [Rule::in(array_keys(Sitrep::getClassificationLevels()))],
            'subtitle' => 'nullable|string|max:255',
            'filters' => 'nullable|array',
            'generate_summary' => 'boolean',
            'generate_findings' => 'boolean',
        ]);

        $dateRange = [
            'start' => $validated['period_start'],
            'end' => $validated['period_end'],
        ];

        $filters = $validated['filters'] ?? [];
        $filters['classification_level'] = $validated['classification_level'] ?? Sitrep::CLASSIFICATION_UNCLASSIFIED;
        if (isset($validated['subtitle'])) {
            $filters['subtitle'] = $validated['subtitle'];
        }

        $sitrep = $this->sitrepService->createFromTemplate(
            $validated['template_id'],
            $dateRange,
            $filters,
            $request->user()
        );

        // Optionally generate AI summary
        if ($request->boolean('generate_summary')) {
            $summary = $this->sitrepService->generateExecutiveSummary($sitrep);
            if ($summary) {
                $sitrep->update(['summary' => $summary]);
            }
        }

        // Optionally generate key findings
        if ($request->boolean('generate_findings')) {
            $findings = $this->sitrepService->generateKeyFindings($sitrep);
            if ($findings) {
                $sitrep->update(['key_findings' => $findings]);
            }
        }

        return response()->json([
            'message' => 'SITREP generated successfully.',
            'data' => $this->formatSitrep($sitrep->fresh(['template', 'creator']), true),
        ], 201);
    }

    /**
     * Preview a sitrep without saving.
     *
     * POST /api/sitreps/preview
     */
    public function preview(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'template_id' => 'required|exists:sitrep_templates,id',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'filters' => 'nullable|array',
        ]);

        $template = SitrepTemplate::findOrFail($validated['template_id']);

        $dateRange = [
            'start' => $validated['period_start'],
            'end' => $validated['period_end'],
        ];

        $filters = $validated['filters'] ?? [];

        // Generate preview content without creating a sitrep
        $previewSections = [];
        foreach ($template->getOrderedSections() as $sectionConfig) {
            $type = $sectionConfig['type'] ?? 'custom_text';

            $content = match ($type) {
                SitrepTemplate::SECTION_EVENTS_SUMMARY => $this->sitrepService->buildEventsSummary($dateRange, $filters),
                SitrepTemplate::SECTION_EQUIPMENT_LOSSES => $this->sitrepService->buildEquipmentLosses($dateRange, $filters),
                SitrepTemplate::SECTION_TERRITORIAL_CHANGES => $this->sitrepService->buildTerritorialChanges($dateRange, $filters),
                SitrepTemplate::SECTION_ACTOR_ACTIVITY => $this->sitrepService->buildActorActivity($dateRange, $filters),
                default => ['content' => ['text' => 'Preview not available for this section type']],
            };

            $previewSections[] = [
                'title' => $sectionConfig['title'] ?? ucfirst(str_replace('_', ' ', $type)),
                'type' => $type,
                'content' => $content['content'] ?? $content,
            ];
        }

        return response()->json([
            'data' => [
                'template' => $this->formatTemplate($template),
                'date_range' => $dateRange,
                'sections' => $previewSections,
            ],
        ]);
    }

    /**
     * Generate AI summary for an existing sitrep.
     *
     * POST /api/sitreps/{uuid}/generate-summary
     */
    public function generateSummary(Request $request, string $uuid): JsonResponse
    {
        $sitrep = Sitrep::where('uuid', $uuid)->firstOrFail();

        // Check access
        if ($sitrep->created_by !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Access denied.'], 403);
        }

        $summary = $this->sitrepService->generateExecutiveSummary($sitrep);

        if (!$summary) {
            return response()->json([
                'message' => 'Failed to generate summary. Please try again.',
            ], 500);
        }

        $sitrep->update(['summary' => $summary]);

        return response()->json([
            'message' => 'Summary generated successfully.',
            'data' => [
                'summary' => $summary,
            ],
        ]);
    }

    /**
     * Generate AI key findings for an existing sitrep.
     *
     * POST /api/sitreps/{uuid}/generate-findings
     */
    public function generateFindings(Request $request, string $uuid): JsonResponse
    {
        $sitrep = Sitrep::where('uuid', $uuid)->firstOrFail();

        // Check access
        if ($sitrep->created_by !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Access denied.'], 403);
        }

        $findings = $this->sitrepService->generateKeyFindings($sitrep);

        if (!$findings) {
            return response()->json([
                'message' => 'Failed to generate key findings. Please try again.',
            ], 500);
        }

        $sitrep->update(['key_findings' => $findings]);

        return response()->json([
            'message' => 'Key findings generated successfully.',
            'data' => [
                'key_findings' => $findings,
            ],
        ]);
    }

    // =========================================================================
    // WORKFLOW ACTIONS
    // =========================================================================

    /**
     * Submit sitrep for review.
     *
     * POST /api/sitreps/{uuid}/submit
     */
    public function submit(Request $request, string $uuid): JsonResponse
    {
        $sitrep = Sitrep::where('uuid', $uuid)->firstOrFail();

        // Check access
        if ($sitrep->created_by !== $request->user()->id) {
            return response()->json(['message' => 'Access denied.'], 403);
        }

        if (!$sitrep->isDraft()) {
            return response()->json([
                'message' => 'Only draft sitreps can be submitted for review.',
            ], 409);
        }

        $sitrep->submitForReview();

        return response()->json([
            'message' => 'SITREP submitted for review.',
            'data' => $this->formatSitrep($sitrep->fresh()),
        ]);
    }

    /**
     * Approve a sitrep.
     *
     * POST /api/sitreps/{uuid}/approve
     */
    public function approve(Request $request, string $uuid): JsonResponse
    {
        $sitrep = Sitrep::where('uuid', $uuid)->firstOrFail();

        // Check if user can approve (requires admin or approver role)
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Access denied.'], 403);
        }

        if (!$sitrep->isInReview()) {
            return response()->json([
                'message' => 'Only sitreps in review can be approved.',
            ], 409);
        }

        $sitrep = $this->sitrepService->approve($sitrep->id, $request->user()->id);

        return response()->json([
            'message' => 'SITREP approved successfully.',
            'data' => $this->formatSitrep($sitrep),
        ]);
    }

    /**
     * Reject a sitrep back to draft.
     *
     * POST /api/sitreps/{uuid}/reject
     */
    public function reject(Request $request, string $uuid): JsonResponse
    {
        $sitrep = Sitrep::where('uuid', $uuid)->firstOrFail();

        // Check if user can reject (requires admin or approver role)
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Access denied.'], 403);
        }

        if (!$sitrep->isInReview()) {
            return response()->json([
                'message' => 'Only sitreps in review can be rejected.',
            ], 409);
        }

        $sitrep->reject();

        return response()->json([
            'message' => 'SITREP rejected and returned to draft.',
            'data' => $this->formatSitrep($sitrep->fresh()),
        ]);
    }

    /**
     * Publish a sitrep.
     *
     * POST /api/sitreps/{uuid}/publish
     */
    public function publish(Request $request, string $uuid): JsonResponse
    {
        $sitrep = Sitrep::where('uuid', $uuid)->firstOrFail();

        // Check if user can publish (requires admin)
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Access denied.'], 403);
        }

        if (!$sitrep->isApproved()) {
            return response()->json([
                'message' => 'Only approved sitreps can be published.',
            ], 409);
        }

        $sitrep = $this->sitrepService->publish($sitrep->id);

        return response()->json([
            'message' => 'SITREP published successfully.',
            'data' => $this->formatSitrep($sitrep),
        ]);
    }

    /**
     * Archive a sitrep.
     *
     * POST /api/sitreps/{uuid}/archive
     */
    public function archive(Request $request, string $uuid): JsonResponse
    {
        $sitrep = Sitrep::where('uuid', $uuid)->firstOrFail();

        // Check access
        if ($sitrep->created_by !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Access denied.'], 403);
        }

        $sitrep->archive();

        return response()->json([
            'message' => 'SITREP archived successfully.',
            'data' => $this->formatSitrep($sitrep->fresh()),
        ]);
    }

    // =========================================================================
    // EXPORT
    // =========================================================================

    /**
     * Export a sitrep to a specific format.
     *
     * POST /api/sitreps/{uuid}/export
     */
    public function export(Request $request, string $uuid): JsonResponse
    {
        $validated = $request->validate([
            'format' => ['required', Rule::in(['pdf', 'docx', 'html'])],
        ]);

        $sitrep = Sitrep::where('uuid', $uuid)
            ->accessibleBy($request->user())
            ->firstOrFail();

        $path = match ($validated['format']) {
            'pdf' => $this->sitrepService->exportToPdf($sitrep->id),
            'docx' => $this->sitrepService->exportToDocx($sitrep->id),
            'html' => $this->sitrepService->exportToHtml($sitrep->id),
        };

        if (!$path) {
            return response()->json([
                'message' => 'Export failed. Please try again.',
            ], 500);
        }

        return response()->json([
            'message' => 'Export generated successfully.',
            'data' => [
                'format' => $validated['format'],
                'path' => $path,
                'url' => Storage::disk('public')->url($path),
            ],
        ]);
    }

    /**
     * Download an exported file.
     *
     * GET /api/sitreps/{uuid}/download/{format}
     */
    public function download(Request $request, string $uuid, string $format): mixed
    {
        if (!in_array($format, ['pdf', 'docx', 'html'])) {
            return response()->json(['message' => 'Invalid format.'], 400);
        }

        $sitrep = Sitrep::where('uuid', $uuid)
            ->accessibleBy($request->user())
            ->firstOrFail();

        $path = $sitrep->getGeneratedFile($format);

        if (!$path || !Storage::disk('public')->exists($path)) {
            return response()->json([
                'message' => 'Export file not found. Please generate it first.',
            ], 404);
        }

        $filename = 'sitrep-' . $sitrep->uuid . '.' . $format;

        return Storage::disk('public')->download($path, $filename);
    }

    // =========================================================================
    // REFERENCE DATA
    // =========================================================================

    /**
     * List available section types.
     *
     * GET /api/sitreps/sections
     */
    public function sections(): JsonResponse
    {
        return response()->json([
            'data' => SitrepTemplate::getSectionTypes(),
        ]);
    }

    /**
     * List available classification levels.
     *
     * GET /api/sitreps/classifications
     */
    public function classifications(): JsonResponse
    {
        return response()->json([
            'data' => Sitrep::getClassificationLevels(),
        ]);
    }

    /**
     * List available report types.
     *
     * GET /api/sitreps/report-types
     */
    public function reportTypes(): JsonResponse
    {
        return response()->json([
            'data' => Sitrep::getReportTypes(),
        ]);
    }

    /**
     * List available statuses.
     *
     * GET /api/sitreps/statuses
     */
    public function statuses(): JsonResponse
    {
        return response()->json([
            'data' => Sitrep::getStatuses(),
        ]);
    }

    /**
     * Get statistics for sitreps.
     *
     * GET /api/sitreps/stats
     */
    public function stats(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $isAdmin = $request->user()->isAdmin();

        $baseQuery = $isAdmin
            ? Sitrep::query()
            : Sitrep::where('created_by', $userId);

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'by_status' => [
                'draft' => (clone $baseQuery)->status(Sitrep::STATUS_DRAFT)->count(),
                'review' => (clone $baseQuery)->status(Sitrep::STATUS_REVIEW)->count(),
                'approved' => (clone $baseQuery)->status(Sitrep::STATUS_APPROVED)->count(),
                'published' => (clone $baseQuery)->status(Sitrep::STATUS_PUBLISHED)->count(),
                'archived' => (clone $baseQuery)->status(Sitrep::STATUS_ARCHIVED)->count(),
            ],
            'by_type' => (clone $baseQuery)->selectRaw('report_type, COUNT(*) as count')
                ->groupBy('report_type')
                ->pluck('count', 'report_type')
                ->toArray(),
            'recent_7_days' => (clone $baseQuery)
                ->where('created_at', '>=', now()->subDays(7))
                ->count(),
            'templates_count' => SitrepTemplate::accessibleBy($request->user())->count(),
        ];

        return response()->json(['data' => $stats]);
    }

    // =========================================================================
    // HELPER METHODS
    // =========================================================================

    /**
     * Format a template for API response.
     */
    protected function formatTemplate(SitrepTemplate $template, bool $detailed = false): array
    {
        $data = [
            'uuid' => $template->uuid,
            'name' => $template->name,
            'description' => $template->description,
            'template_type' => $template->template_type,
            'template_type_name' => $template->getTemplateTypeName(),
            'sections_count' => count($template->sections ?? []),
            'is_default' => $template->is_default,
            'is_public' => $template->is_public,
            'created_at' => $template->created_at,
            'updated_at' => $template->updated_at,
        ];

        if ($template->relationLoaded('creator') && $template->creator) {
            $data['creator'] = [
                'id' => $template->creator->id,
                'name' => $template->creator->name,
            ];
        }

        if ($detailed) {
            $data['sections'] = $template->getOrderedSections();
            $data['header_config'] = $template->header_config;
            $data['footer_config'] = $template->footer_config;
            $data['styling'] = $template->styling;
        }

        return $data;
    }

    /**
     * Format a sitrep for API response.
     */
    protected function formatSitrep(Sitrep $sitrep, bool $detailed = false): array
    {
        $data = [
            'uuid' => $sitrep->uuid,
            'title' => $sitrep->title,
            'subtitle' => $sitrep->subtitle,
            'report_type' => $sitrep->report_type,
            'report_type_name' => $sitrep->getReportTypeName(),
            'classification_level' => $sitrep->classification_level,
            'classification_name' => $sitrep->getClassificationName(),
            'period_start' => $sitrep->period_start?->format('Y-m-d'),
            'period_end' => $sitrep->period_end?->format('Y-m-d'),
            'reporting_period' => $sitrep->getReportingPeriod(),
            'status' => $sitrep->status,
            'status_name' => $sitrep->getStatusName(),
            'has_summary' => !empty($sitrep->summary),
            'has_key_findings' => !empty($sitrep->key_findings),
            'has_recommendations' => !empty($sitrep->recommendations),
            'sections_count' => count($sitrep->content_sections ?? []),
            'events_count' => count($sitrep->event_ids ?? []),
            'equipment_count' => count($sitrep->equipment_ids ?? []),
            'actors_count' => count($sitrep->actor_ids ?? []),
            'generated_files' => array_keys($sitrep->generated_files ?? []),
            'approved_at' => $sitrep->approved_at?->toIso8601String(),
            'published_at' => $sitrep->published_at?->toIso8601String(),
            'created_at' => $sitrep->created_at,
            'updated_at' => $sitrep->updated_at,
        ];

        if ($sitrep->relationLoaded('template') && $sitrep->template) {
            $data['template'] = [
                'uuid' => $sitrep->template->uuid,
                'name' => $sitrep->template->name,
            ];
        }

        if ($sitrep->relationLoaded('creator') && $sitrep->creator) {
            $data['creator'] = [
                'id' => $sitrep->creator->id,
                'name' => $sitrep->creator->name,
            ];
        }

        if ($sitrep->relationLoaded('approver') && $sitrep->approver) {
            $data['approver'] = [
                'id' => $sitrep->approver->id,
                'name' => $sitrep->approver->name,
            ];
        }

        if ($detailed) {
            $data['summary'] = $sitrep->summary;
            $data['key_findings'] = $sitrep->key_findings;
            $data['recommendations'] = $sitrep->recommendations;
            $data['content_sections'] = $sitrep->content_sections;
            $data['appendices'] = $sitrep->appendices;
            $data['event_ids'] = $sitrep->event_ids;
            $data['equipment_ids'] = $sitrep->equipment_ids;
            $data['actor_ids'] = $sitrep->actor_ids;
            $data['metadata'] = $sitrep->metadata;

            // Include file URLs
            $data['export_urls'] = [];
            foreach ($sitrep->generated_files ?? [] as $format => $fileData) {
                if (isset($fileData['path'])) {
                    $data['export_urls'][$format] = Storage::disk('public')->url($fileData['path']);
                }
            }
        }

        return $data;
    }
}
