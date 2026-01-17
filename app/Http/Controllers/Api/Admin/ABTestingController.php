<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\ABTestingService;
use App\Models\Experiment;
use App\Models\ExperimentVariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ABTestingController extends Controller
{
    public function __construct(
        private readonly ABTestingService $abTestingService
    ) {}

    /**
     * List all experiments.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user->isAdmin() && !$user->hasPermission('experiments.view')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $query = Experiment::with(['variants', 'creator:id,name'])
            ->withCount('assignments');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $experiments = $query->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 20));

        return response()->json($experiments);
    }

    /**
     * Get a single experiment with full statistics.
     */
    public function show(Request $request, Experiment $experiment): JsonResponse
    {
        $user = $request->user();
        if (!$user->isAdmin() && !$user->hasPermission('experiments.view')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $data = $this->abTestingService->getExperimentWithStats($experiment);

        return response()->json(['data' => $data]);
    }

    /**
     * Create a new experiment.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user->isAdmin() && !$user->hasPermission('experiments.manage')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'type' => ['required', Rule::in(array_keys(Experiment::getTypes()))],
            'location' => 'required|string|max:255',
            'traffic_percentage' => 'integer|min:1|max:100',
            'min_sample_size' => 'integer|min:10|max:10000',
            'min_confidence' => 'numeric|min:0.80|max:0.99',
            'primary_goal' => ['required', Rule::in(array_keys(Experiment::getGoals()))],
            'secondary_goals' => 'nullable|array',
            'secondary_goals.*' => [Rule::in(array_keys(Experiment::getGoals()))],
            'variants' => 'required|array|min:2|max:5',
            'variants.*.name' => 'required|string|max:100',
            'variants.*.slug' => 'nullable|string|max:100',
            'variants.*.is_control' => 'boolean',
            'variants.*.weight' => 'integer|min:1|max:100',
            'variants.*.content' => 'required|array',
            'variants.*.description' => 'nullable|string|max:500',
        ]);

        // Ensure exactly one control variant
        $controlCount = collect($validated['variants'])->where('is_control', true)->count();
        if ($controlCount !== 1) {
            return response()->json([
                'message' => 'Exactly one variant must be marked as control',
            ], 422);
        }

        // Ensure weights sum to 100
        $totalWeight = collect($validated['variants'])->sum('weight');
        if ($totalWeight !== 100) {
            return response()->json([
                'message' => 'Variant weights must sum to 100',
            ], 422);
        }

        $validated['created_by'] = $user->id;

        $experiment = $this->abTestingService->createExperiment($validated);

        return response()->json([
            'data' => $experiment,
            'message' => 'Experiment created successfully',
        ], 201);
    }

    /**
     * Update an experiment.
     */
    public function update(Request $request, Experiment $experiment): JsonResponse
    {
        $user = $request->user();
        if (!$user->isAdmin() && !$user->hasPermission('experiments.manage')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Cannot update running experiments (pause first)
        if ($experiment->status === Experiment::STATUS_RUNNING) {
            return response()->json([
                'message' => 'Cannot update a running experiment. Pause it first.',
            ], 422);
        }

        $validated = $request->validate([
            'name' => 'string|max:255',
            'description' => 'nullable|string|max:2000',
            'traffic_percentage' => 'integer|min:1|max:100',
            'min_sample_size' => 'integer|min:10|max:10000',
            'min_confidence' => 'numeric|min:0.80|max:0.99',
            'primary_goal' => [Rule::in(array_keys(Experiment::getGoals()))],
            'secondary_goals' => 'nullable|array',
        ]);

        $experiment = $this->abTestingService->updateExperiment($experiment, $validated);

        return response()->json([
            'data' => $experiment,
            'message' => 'Experiment updated successfully',
        ]);
    }

    /**
     * Start an experiment.
     */
    public function start(Request $request, Experiment $experiment): JsonResponse
    {
        $user = $request->user();
        if (!$user->isAdmin() && !$user->hasPermission('experiments.manage')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($experiment->status !== Experiment::STATUS_DRAFT && $experiment->status !== Experiment::STATUS_PAUSED) {
            return response()->json([
                'message' => 'Only draft or paused experiments can be started',
            ], 422);
        }

        // Check for conflicting experiments at same location
        $conflict = Experiment::running()
            ->atLocation($experiment->location)
            ->where('id', '!=', $experiment->id)
            ->exists();

        if ($conflict) {
            return response()->json([
                'message' => 'Another experiment is already running at this location',
            ], 422);
        }

        if (!$this->abTestingService->startExperiment($experiment)) {
            return response()->json([
                'message' => 'Failed to start experiment. Ensure it has at least 2 variants with one control.',
            ], 422);
        }

        return response()->json([
            'data' => $experiment->fresh(),
            'message' => 'Experiment started successfully',
        ]);
    }

    /**
     * Pause an experiment.
     */
    public function pause(Request $request, Experiment $experiment): JsonResponse
    {
        $user = $request->user();
        if (!$user->isAdmin() && !$user->hasPermission('experiments.manage')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($experiment->status !== Experiment::STATUS_RUNNING) {
            return response()->json([
                'message' => 'Only running experiments can be paused',
            ], 422);
        }

        $this->abTestingService->pauseExperiment($experiment);

        return response()->json([
            'data' => $experiment->fresh(),
            'message' => 'Experiment paused successfully',
        ]);
    }

    /**
     * Complete an experiment.
     */
    public function complete(Request $request, Experiment $experiment): JsonResponse
    {
        $user = $request->user();
        if (!$user->isAdmin() && !$user->hasPermission('experiments.manage')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $this->abTestingService->completeExperiment($experiment);

        // Calculate final results
        $this->abTestingService->calculateResults($experiment);

        return response()->json([
            'data' => $this->abTestingService->getExperimentWithStats($experiment),
            'message' => 'Experiment completed successfully',
        ]);
    }

    /**
     * Delete an experiment.
     */
    public function destroy(Request $request, Experiment $experiment): JsonResponse
    {
        $user = $request->user();
        if (!$user->isAdmin() && !$user->hasPermission('experiments.manage')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($experiment->status === Experiment::STATUS_RUNNING) {
            return response()->json([
                'message' => 'Cannot delete a running experiment. Complete or pause it first.',
            ], 422);
        }

        $experiment->delete();

        return response()->json([
            'message' => 'Experiment deleted successfully',
        ]);
    }

    /**
     * Add a variant to an experiment.
     */
    public function addVariant(Request $request, Experiment $experiment): JsonResponse
    {
        $user = $request->user();
        if (!$user->isAdmin() && !$user->hasPermission('experiments.manage')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($experiment->status === Experiment::STATUS_RUNNING) {
            return response()->json([
                'message' => 'Cannot add variants to a running experiment',
            ], 422);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'nullable|string|max:100',
            'is_control' => 'boolean',
            'weight' => 'required|integer|min:1|max:100',
            'content' => 'required|array',
            'description' => 'nullable|string|max:500',
        ]);

        // If setting as control, remove control from other variants
        if ($validated['is_control'] ?? false) {
            $experiment->variants()->update(['is_control' => false]);
        }

        $variant = ExperimentVariant::create([
            'experiment_id' => $experiment->id,
            ...$validated,
        ]);

        return response()->json([
            'data' => $variant,
            'message' => 'Variant added successfully',
        ], 201);
    }

    /**
     * Update a variant.
     */
    public function updateVariant(Request $request, Experiment $experiment, ExperimentVariant $variant): JsonResponse
    {
        $user = $request->user();
        if (!$user->isAdmin() && !$user->hasPermission('experiments.manage')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($variant->experiment_id !== $experiment->id) {
            return response()->json(['message' => 'Variant does not belong to this experiment'], 404);
        }

        if ($experiment->status === Experiment::STATUS_RUNNING) {
            return response()->json([
                'message' => 'Cannot update variants of a running experiment',
            ], 422);
        }

        $validated = $request->validate([
            'name' => 'string|max:100',
            'weight' => 'integer|min:1|max:100',
            'content' => 'array',
            'description' => 'nullable|string|max:500',
        ]);

        $variant->update($validated);

        return response()->json([
            'data' => $variant,
            'message' => 'Variant updated successfully',
        ]);
    }

    /**
     * Delete a variant.
     */
    public function deleteVariant(Request $request, Experiment $experiment, ExperimentVariant $variant): JsonResponse
    {
        $user = $request->user();
        if (!$user->isAdmin() && !$user->hasPermission('experiments.manage')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($variant->experiment_id !== $experiment->id) {
            return response()->json(['message' => 'Variant does not belong to this experiment'], 404);
        }

        if ($experiment->status === Experiment::STATUS_RUNNING) {
            return response()->json([
                'message' => 'Cannot delete variants from a running experiment',
            ], 422);
        }

        if ($experiment->variants()->count() <= 2) {
            return response()->json([
                'message' => 'Experiment must have at least 2 variants',
            ], 422);
        }

        if ($variant->is_control) {
            return response()->json([
                'message' => 'Cannot delete the control variant',
            ], 422);
        }

        $variant->delete();

        return response()->json([
            'message' => 'Variant deleted successfully',
        ]);
    }

    /**
     * Recalculate results for an experiment.
     */
    public function recalculate(Request $request, Experiment $experiment): JsonResponse
    {
        $user = $request->user();
        if (!$user->isAdmin() && !$user->hasPermission('experiments.manage')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $this->abTestingService->calculateResults($experiment);

        return response()->json([
            'data' => $this->abTestingService->getExperimentWithStats($experiment),
            'message' => 'Results recalculated successfully',
        ]);
    }

    /**
     * Get experiment types and goals.
     */
    public function options(Request $request): JsonResponse
    {
        return response()->json([
            'data' => [
                'types' => Experiment::getTypes(),
                'goals' => Experiment::getGoals(),
                'statuses' => [
                    Experiment::STATUS_DRAFT => 'Draft',
                    Experiment::STATUS_RUNNING => 'Running',
                    Experiment::STATUS_PAUSED => 'Paused',
                    Experiment::STATUS_COMPLETED => 'Completed',
                ],
            ],
        ]);
    }

    /**
     * Get summary stats for all experiments.
     */
    public function summary(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user->isAdmin() && !$user->hasPermission('experiments.view')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'data' => [
                'total' => Experiment::count(),
                'running' => Experiment::running()->count(),
                'draft' => Experiment::where('status', Experiment::STATUS_DRAFT)->count(),
                'completed' => Experiment::where('status', Experiment::STATUS_COMPLETED)->count(),
                'with_winners' => Experiment::whereHas('results', fn ($q) => $q->where('is_winner', true))->count(),
            ],
        ]);
    }
}
