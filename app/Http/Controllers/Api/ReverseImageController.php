<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ReverseImageSearchJob;
use App\Models\ReverseImageResult;
use App\Models\ReverseImageSearch;
use App\Services\ReverseImageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * Reverse Image Search Controller
 *
 * API controller for managing reverse image searches across multiple engines.
 * Provides endpoints for:
 * - Initiating new searches (upload or URL)
 * - Checking search status
 * - Retrieving aggregated results
 * - Managing search history
 * - Listing available engines
 */
class ReverseImageController extends Controller
{
    public function __construct(
        protected ReverseImageService $imageService
    ) {}

    /**
     * List user's search history
     *
     * GET /api/reverse-image
     *
     * Query Parameters:
     * - status: Filter by search status (pending, searching, completed, failed)
     * - event_id: Filter by associated event
     * - from: Filter by date range start
     * - to: Filter by date range end
     * - per_page: Results per page (default: 25)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = ReverseImageSearch::query()
            ->with(['creator', 'event'])
            ->withCount('results')
            ->byCreator($request->user()->id);

        // Filter by status
        if ($request->has('status')) {
            $query->status($request->status);
        }

        // Filter by event
        if ($request->has('event_id')) {
            $query->forEvent((int) $request->event_id);
        }

        // Filter by date range
        if ($request->has('from')) {
            $query->where('created_at', '>=', $request->from);
        }
        if ($request->has('to')) {
            $query->where('created_at', '<=', $request->to);
        }

        $query->orderBy('created_at', 'desc');

        $searches = $query->paginate($request->input('per_page', $this->perPage));

        return $this->paginated($searches);
    }

    /**
     * Initiate a new reverse image search
     *
     * POST /api/reverse-image/search
     *
     * Body Parameters:
     * - image: Uploaded image file (required if no image_url)
     * - image_url: URL of image to search (required if no image)
     * - event_id: Associated event ID (optional)
     * - engines: Array of engines to search (optional, defaults to all enabled)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'image' => ['required_without:image_url', 'file', 'image', 'max:10240'],
            'image_url' => ['required_without:image', 'url', 'max:2048'],
            'event_id' => ['nullable', 'exists:events,id'],
            'engines' => ['nullable', 'array'],
            'engines.*' => [Rule::in(ReverseImageSearch::getEngines())],
        ]);

        try {
            // Create search record from upload or URL
            if ($request->hasFile('image')) {
                $search = $this->imageService->createSearchFromUpload(
                    $request->file('image'),
                    $request->user(),
                    ['event_id' => $validated['event_id'] ?? null]
                );
            } else {
                $search = $this->imageService->createSearchFromUrl(
                    $validated['image_url'],
                    $request->user(),
                    ['event_id' => $validated['event_id'] ?? null]
                );
            }

            // Check for duplicate searches with same image hash
            $duplicates = $this->imageService->findDuplicateSearches($search->image_hash, $search->id);
            $hasDuplicates = $duplicates->isNotEmpty();

            // Dispatch background job
            ReverseImageSearchJob::dispatch(
                $search,
                $validated['engines'] ?? null
            );

            return $this->success([
                'message' => 'Reverse image search initiated',
                'search' => $search->fresh(['creator', 'event']),
                'has_previous_searches' => $hasDuplicates,
                'previous_search_count' => $duplicates->count(),
            ], 202);

        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * Get search status
     *
     * GET /api/reverse-image/{uuid}/status
     *
     * @param string $uuid Search UUID
     * @return JsonResponse
     */
    public function status(string $uuid): JsonResponse
    {
        $search = ReverseImageSearch::with(['creator', 'event'])
            ->withCount('results')
            ->where('uuid', $uuid)
            ->firstOrFail();

        // Check access
        if ($search->created_by !== $this->user()->id) {
            return $this->forbidden('You do not have access to this search');
        }

        return $this->success([
            'search' => $search,
            'status' => $search->search_status,
            'is_complete' => $search->isCompleted(),
            'is_failed' => $search->isFailed(),
            'can_retry' => $search->canRetry(),
            'progress' => [
                'percentage' => $search->getProgressPercentage(),
                'engines_searched' => $search->engines_searched,
                'engines_completed' => $search->engines_completed,
            ],
            'duration_seconds' => $search->getSearchDuration(),
        ]);
    }

    /**
     * Get aggregated search results
     *
     * GET /api/reverse-image/{uuid}/results
     *
     * Query Parameters:
     * - engine: Filter by specific engine
     * - match_type: Filter by match type (exact, similar, partial)
     * - min_similarity: Minimum similarity score (0-100)
     * - manipulated_only: Show only potentially manipulated results
     * - domain: Filter by source domain
     * - per_page: Results per page
     *
     * @param string $uuid Search UUID
     * @param Request $request
     * @return JsonResponse
     */
    public function results(string $uuid, Request $request): JsonResponse
    {
        $search = ReverseImageSearch::where('uuid', $uuid)->firstOrFail();

        // Check access
        if ($search->created_by !== $this->user()->id) {
            return $this->forbidden('You do not have access to this search');
        }

        if (!$search->isCompleted() && !$search->isFailed()) {
            return $this->error('Search is still in progress', 400);
        }

        // Build results query
        $query = ReverseImageResult::forSearch($search->id);

        // Filter by engine
        if ($request->has('engine')) {
            $query->engine($request->engine);
        }

        // Filter by match type
        if ($request->has('match_type')) {
            $query->matchType($request->match_type);
        }

        // Filter by minimum similarity
        if ($request->has('min_similarity')) {
            $query->minSimilarity((int) $request->min_similarity);
        }

        // Filter manipulated only
        if ($request->boolean('manipulated_only')) {
            $query->manipulated();
        }

        // Filter by domain
        if ($request->has('domain')) {
            $query->domain($request->domain);
        }

        // Order by similarity score by default
        $query->orderBySimilarity();

        $results = $query->paginate($request->input('per_page', $this->perPage));

        // Get aggregated stats
        $aggregated = $this->imageService->aggregateResults($search->id);

        return response()->json([
            'data' => $results->items(),
            'aggregated' => $aggregated,
            'meta' => [
                'pagination' => [
                    'total' => $results->total(),
                    'count' => $results->count(),
                    'per_page' => $results->perPage(),
                    'current_page' => $results->currentPage(),
                    'total_pages' => $results->lastPage(),
                ],
            ],
            'links' => [
                'first' => $results->url(1),
                'last' => $results->url($results->lastPage()),
                'prev' => $results->previousPageUrl(),
                'next' => $results->nextPageUrl(),
            ],
        ]);
    }

    /**
     * Get a single search with all details
     *
     * GET /api/reverse-image/{uuid}
     *
     * @param string $uuid Search UUID
     * @return JsonResponse
     */
    public function show(string $uuid): JsonResponse
    {
        $search = ReverseImageSearch::with(['creator', 'event', 'results'])
            ->where('uuid', $uuid)
            ->firstOrFail();

        // Check access
        if ($search->created_by !== $this->user()->id) {
            return $this->forbidden('You do not have access to this search');
        }

        return $this->success([
            'search' => $search,
            'stats' => $search->getStatsSummary(),
            'image_url' => $search->getImageUrl(),
            'image_exists' => $search->imageExists(),
        ]);
    }

    /**
     * Retry a failed search
     *
     * POST /api/reverse-image/{uuid}/retry
     *
     * Body Parameters:
     * - engines: Array of engines to retry (optional, defaults to all enabled)
     *
     * @param string $uuid Search UUID
     * @param Request $request
     * @return JsonResponse
     */
    public function retry(string $uuid, Request $request): JsonResponse
    {
        $search = ReverseImageSearch::where('uuid', $uuid)->firstOrFail();

        // Check access
        if ($search->created_by !== $this->user()->id) {
            return $this->forbidden('You do not have access to this search');
        }

        if (!$search->canRetry()) {
            return $this->error('This search cannot be retried', 400);
        }

        $validated = $request->validate([
            'engines' => ['nullable', 'array'],
            'engines.*' => [Rule::in(ReverseImageSearch::getEngines())],
        ]);

        // Reset search status
        $search->update([
            'search_status' => ReverseImageSearch::STATUS_PENDING,
            'error_message' => null,
            'total_results' => 0,
            'engines_searched' => null,
            'engines_completed' => null,
            'search_started_at' => null,
            'search_completed_at' => null,
        ]);

        // Delete old results
        $search->results()->delete();

        // Dispatch new job
        ReverseImageSearchJob::dispatch(
            $search,
            $validated['engines'] ?? null
        );

        return $this->success([
            'message' => 'Search retry initiated',
            'search' => $search->fresh(),
        ], 202);
    }

    /**
     * Delete a search and its results
     *
     * DELETE /api/reverse-image/{uuid}
     *
     * @param string $uuid Search UUID
     * @return JsonResponse
     */
    public function destroy(string $uuid): JsonResponse
    {
        $search = ReverseImageSearch::where('uuid', $uuid)->firstOrFail();

        // Check access
        if ($search->created_by !== $this->user()->id) {
            return $this->forbidden('You do not have access to this search');
        }

        // Delete local image file
        if ($search->image_path && Storage::disk('reverse_images')->exists($search->image_path)) {
            Storage::disk('reverse_images')->delete($search->image_path);
        }

        // Delete search (cascade deletes results)
        $search->delete();

        return $this->success(['message' => 'Search deleted successfully']);
    }

    /**
     * List available search engines with their configuration status
     *
     * GET /api/reverse-image/engines
     *
     * @return JsonResponse
     */
    public function engines(): JsonResponse
    {
        $engines = $this->imageService->getEnginesConfig();
        $enabledEngines = $this->imageService->getEnabledEngines();

        return $this->success([
            'engines' => $engines,
            'enabled_engines' => $enabledEngines,
            'total_configured' => count($enabledEngines),
        ]);
    }

    /**
     * Get user's search history with statistics
     *
     * GET /api/reverse-image/history
     *
     * Query Parameters:
     * - limit: Number of recent searches (default: 10)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function history(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $limit = min((int) $request->input('limit', 10), 100);

        // Recent searches
        $recentSearches = ReverseImageSearch::byCreator($userId)
            ->with(['event'])
            ->withCount('results')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        // Statistics
        $stats = [
            'total_searches' => ReverseImageSearch::byCreator($userId)->count(),
            'completed_searches' => ReverseImageSearch::byCreator($userId)->completed()->count(),
            'failed_searches' => ReverseImageSearch::byCreator($userId)->failed()->count(),
            'pending_searches' => ReverseImageSearch::byCreator($userId)
                ->whereIn('search_status', [
                    ReverseImageSearch::STATUS_PENDING,
                    ReverseImageSearch::STATUS_SEARCHING,
                ])->count(),
            'total_results_found' => ReverseImageSearch::byCreator($userId)->sum('total_results'),
            'searches_with_exact_matches' => ReverseImageSearch::byCreator($userId)
                ->whereHas('results', fn($q) => $q->where('match_type', ReverseImageResult::MATCH_EXACT))
                ->count(),
            'searches_with_manipulation' => ReverseImageSearch::byCreator($userId)
                ->whereHas('results', fn($q) => $q->where('is_potentially_manipulated', true))
                ->count(),
        ];

        // Engine usage statistics
        $engineStats = [];
        foreach (ReverseImageSearch::getEngines() as $engine) {
            $engineStats[$engine] = ReverseImageResult::whereHas('search', fn($q) =>
                $q->where('created_by', $userId)
            )->where('engine', $engine)->count();
        }

        return $this->success([
            'recent_searches' => $recentSearches,
            'statistics' => $stats,
            'engine_usage' => $engineStats,
        ]);
    }

    /**
     * Get search statistics across all user's searches
     *
     * GET /api/reverse-image/stats
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function stats(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        // Overall statistics
        $stats = [
            'searches' => [
                'total' => ReverseImageSearch::byCreator($userId)->count(),
                'completed' => ReverseImageSearch::byCreator($userId)->completed()->count(),
                'failed' => ReverseImageSearch::byCreator($userId)->failed()->count(),
                'pending' => ReverseImageSearch::byCreator($userId)->pending()->count(),
                'in_progress' => ReverseImageSearch::byCreator($userId)
                    ->where('search_status', ReverseImageSearch::STATUS_SEARCHING)->count(),
            ],
            'results' => [
                'total' => ReverseImageResult::whereHas('search', fn($q) =>
                    $q->where('created_by', $userId)
                )->count(),
                'exact_matches' => ReverseImageResult::whereHas('search', fn($q) =>
                    $q->where('created_by', $userId)
                )->where('match_type', ReverseImageResult::MATCH_EXACT)->count(),
                'similar_matches' => ReverseImageResult::whereHas('search', fn($q) =>
                    $q->where('created_by', $userId)
                )->where('match_type', ReverseImageResult::MATCH_SIMILAR)->count(),
                'potentially_manipulated' => ReverseImageResult::whereHas('search', fn($q) =>
                    $q->where('created_by', $userId)
                )->where('is_potentially_manipulated', true)->count(),
            ],
            'by_engine' => [],
            'top_domains' => [],
        ];

        // Results by engine
        foreach (ReverseImageSearch::getEngines() as $engine) {
            $stats['by_engine'][$engine] = ReverseImageResult::whereHas('search', fn($q) =>
                $q->where('created_by', $userId)
            )->where('engine', $engine)->count();
        }

        // Top source domains
        $topDomains = ReverseImageResult::whereHas('search', fn($q) =>
            $q->where('created_by', $userId)
        )
            ->selectRaw('source_domain, COUNT(*) as count')
            ->groupBy('source_domain')
            ->orderByDesc('count')
            ->limit(10)
            ->pluck('count', 'source_domain');

        $stats['top_domains'] = $topDomains->toArray();

        return $this->success($stats);
    }

    /**
     * Get match types and their descriptions
     *
     * GET /api/reverse-image/match-types
     *
     * @return JsonResponse
     */
    public function matchTypes(): JsonResponse
    {
        return $this->success([
            'match_types' => [
                ReverseImageResult::MATCH_EXACT => [
                    'value' => ReverseImageResult::MATCH_EXACT,
                    'name' => 'Exact Match',
                    'description' => 'The exact same image was found with no modifications',
                ],
                ReverseImageResult::MATCH_SIMILAR => [
                    'value' => ReverseImageResult::MATCH_SIMILAR,
                    'name' => 'Similar',
                    'description' => 'A visually similar image was found, possibly cropped or resized',
                ],
                ReverseImageResult::MATCH_PARTIAL => [
                    'value' => ReverseImageResult::MATCH_PARTIAL,
                    'name' => 'Partial Match',
                    'description' => 'Part of the image was found within another image',
                ],
            ],
            'manipulation_indicators' => ReverseImageResult::getManipulationIndicators(),
        ]);
    }

    /**
     * Compare two images for similarity
     *
     * POST /api/reverse-image/compare
     *
     * Body Parameters:
     * - image1: First image file
     * - image2: Second image file
     * - threshold: Similarity threshold (default: 10)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function compare(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'image1' => ['required', 'file', 'image', 'max:10240'],
            'image2' => ['required', 'file', 'image', 'max:10240'],
            'threshold' => ['nullable', 'integer', 'min:0', 'max:64'],
        ]);

        $threshold = $validated['threshold'] ?? 10;

        try {
            // Calculate hashes for both images
            $tempPath1 = $request->file('image1')->getPathname();
            $tempPath2 = $request->file('image2')->getPathname();

            $hash1 = $this->imageService->calculatePerceptualHash($tempPath1);
            $hash2 = $this->imageService->calculatePerceptualHash($tempPath2);

            // Calculate Hamming distance
            $distance = $this->imageService->calculateHashDistance($hash1, $hash2);
            $areSimilar = $this->imageService->areImagesSimilar($hash1, $hash2, $threshold);

            // Calculate similarity percentage (inverse of distance)
            $maxDistance = strlen($hash1) * 4; // 4 bits per hex char
            $similarityPercent = round((1 - ($distance / $maxDistance)) * 100, 2);

            return $this->success([
                'are_similar' => $areSimilar,
                'hamming_distance' => $distance,
                'similarity_percentage' => $similarityPercent,
                'threshold_used' => $threshold,
                'hashes' => [
                    'image1' => $hash1,
                    'image2' => $hash2,
                ],
            ]);

        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * Find existing searches with similar images
     *
     * POST /api/reverse-image/find-similar
     *
     * Body Parameters:
     * - image: Image file to find similar searches for
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function findSimilar(Request $request): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'file', 'image', 'max:10240'],
        ]);

        try {
            $tempPath = $request->file('image')->getPathname();
            $hash = $this->imageService->calculatePerceptualHash($tempPath);

            // Find searches with the same hash
            $exactMatches = ReverseImageSearch::byHash($hash)
                ->byCreator($request->user()->id)
                ->with(['event'])
                ->withCount('results')
                ->get();

            return $this->success([
                'image_hash' => $hash,
                'exact_matches' => $exactMatches,
                'match_count' => $exactMatches->count(),
            ]);

        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 400);
        }
    }
}
