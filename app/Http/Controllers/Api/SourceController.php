<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Source;
use App\Models\SourceCrossReference;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Source controller for managing verified information sources
 */
class SourceController extends Controller
{
    /**
     * Display a paginated list of sources with filters
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = Source::query()->with(['country', 'verifier']);

        // Filter by verification status
        if ($request->has('status')) {
            $query->where('verification_status', $request->status);
        }

        // Filter by source type
        if ($request->has('type')) {
            $query->where('source_type', $request->type);
        }

        // Filter by country
        if ($request->has('country_id')) {
            $query->where('country_id', $request->country_id);
        }

        // Filter by minimum reliability score
        if ($request->has('min_reliability')) {
            $query->where('reliability_score', '>=', (int) $request->min_reliability);
        }

        // Filter active only
        if ($request->boolean('active_only', true)) {
            $query->active();
        }

        // Filter trusted sources only
        if ($request->boolean('trusted_only', false)) {
            $query->trusted();
        }

        // Search by name, description, or URL
        if ($request->has('search')) {
            $query->search($request->search);
        }

        // Filter by language
        if ($request->has('language')) {
            $query->where('language', $request->language);
        }

        // Filter sources needing verification
        if ($request->boolean('needs_verification', false)) {
            $query->needsVerification($request->integer('verification_days', 90));
        }

        // Sorting
        $sortField = $request->input('sort', 'reliability_score');
        $sortDirection = $request->input('direction', 'desc');
        $allowedSorts = ['name', 'reliability_score', 'total_reports', 'created_at', 'last_verified_at'];

        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection);
        }

        $sources = $query->paginate($this->perPage);

        return $this->paginated($sources);
    }

    /**
     * Store a newly created source
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        // Creating verified sources requires moderator or admin role
        if (!$request->user()->isModerator()) {
            return response()->json(['message' => 'Insufficient permissions to create sources.'], 403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:sources,name'],
            'url' => ['nullable', 'url', 'max:500'],
            'description' => ['nullable', 'string', 'max:2000'],
            'source_type' => ['required', Rule::in(Source::getSourceTypes())],
            'country_id' => ['nullable', 'exists:countries,id'],
            'reliability_score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'verification_status' => ['nullable', Rule::in(Source::getVerificationStatuses())],
            'language' => ['nullable', 'string', 'max:10'],
            'logo_url' => ['nullable', 'url', 'max:500'],
            'metadata' => ['nullable', 'array'],
            'contact_info' => ['nullable', 'array'],
            'known_biases' => ['nullable', 'array'],
        ]);

        $source = Source::create([
            'uuid' => (string) Str::uuid(),
            'name' => $validated['name'],
            'url' => $validated['url'] ?? null,
            'description' => $validated['description'] ?? null,
            'source_type' => $validated['source_type'],
            'country_id' => $validated['country_id'] ?? null,
            'reliability_score' => $validated['reliability_score'] ?? 50,
            'verification_status' => $validated['verification_status'] ?? Source::STATUS_UNVERIFIED,
            'language' => $validated['language'] ?? null,
            'logo_url' => $validated['logo_url'] ?? null,
            'metadata' => $validated['metadata'] ?? null,
            'contact_info' => $validated['contact_info'] ?? null,
            'known_biases' => $validated['known_biases'] ?? null,
            'is_active' => true,
        ]);

        // Log creation
        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'source.created',
            'auditable_type' => Source::class,
            'auditable_id' => $source->id,
            'properties' => ['name' => $source->name],
        ]);

        return $this->success($source->load(['country']), 201);
    }

    /**
     * Display the specified source
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function show(string $uuid): JsonResponse
    {
        $source = Source::with([
            'country',
            'verifier',
            'crossReferences.referencedSource',
            'referencedBy.source',
        ])->where('uuid', $uuid)->firstOrFail();

        // Add computed attributes
        $source->accuracy_rate = $source->getAccuracyRate();
        $source->dispute_rate = $source->getDisputeRate();
        $source->reliability_level = $source->getReliabilityLevel();

        return $this->success($source);
    }

    /**
     * Update the specified source
     *
     * @param Request $request
     * @param string $uuid
     * @return JsonResponse
     */
    public function update(Request $request, string $uuid): JsonResponse
    {
        $source = Source::where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255', Rule::unique('sources', 'name')->ignore($source->id)],
            'url' => ['nullable', 'url', 'max:500'],
            'description' => ['nullable', 'string', 'max:2000'],
            'source_type' => ['sometimes', Rule::in(Source::getSourceTypes())],
            'country_id' => ['nullable', 'exists:countries,id'],
            'reliability_score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'language' => ['nullable', 'string', 'max:10'],
            'logo_url' => ['nullable', 'url', 'max:500'],
            'metadata' => ['nullable', 'array'],
            'contact_info' => ['nullable', 'array'],
            'known_biases' => ['nullable', 'array'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $source->update($validated);

        // Log update
        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'source.updated',
            'auditable_type' => Source::class,
            'auditable_id' => $source->id,
            'properties' => ['changes' => $source->getChanges()],
        ]);

        return $this->success($source->fresh(['country', 'verifier']));
    }

    /**
     * Remove the specified source (soft delete)
     *
     * @param Request $request
     * @param string $uuid
     * @return JsonResponse
     */
    public function destroy(Request $request, string $uuid): JsonResponse
    {
        $source = Source::where('uuid', $uuid)->firstOrFail();

        // Log deletion
        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'source.deleted',
            'auditable_type' => Source::class,
            'auditable_id' => $source->id,
            'properties' => ['name' => $source->name],
        ]);

        $source->delete();

        return $this->success(['message' => 'Source deleted successfully']);
    }

    /**
     * Verify a source (admin/moderator action)
     *
     * @param Request $request
     * @param string $uuid
     * @return JsonResponse
     */
    public function verify(Request $request, string $uuid): JsonResponse
    {
        // Source verification requires moderator or admin role
        if (!$request->user()->isModerator()) {
            return response()->json(['message' => 'Insufficient permissions to verify sources.'], 403);
        }

        $source = Source::where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'status' => ['required', Rule::in([
                Source::STATUS_VERIFIED,
                Source::STATUS_TRUSTED,
                Source::STATUS_UNRELIABLE,
            ])],
            'notes' => ['nullable', 'string', 'max:1000'],
            'reliability_adjustment' => ['nullable', 'integer', 'min:-50', 'max:50'],
        ]);

        // Apply reliability adjustment if provided
        if (isset($validated['reliability_adjustment'])) {
            $newScore = max(0, min(100, $source->reliability_score + $validated['reliability_adjustment']));
            $source->reliability_score = $newScore;
        }

        // Mark as verified with the specified status
        $source->markAsVerified($request->user(), $validated['status']);

        // Add notes to metadata if provided
        if (!empty($validated['notes'])) {
            $metadata = $source->metadata ?? [];
            $metadata['verification_notes'] = $validated['notes'];
            $metadata['verified_at'] = now()->toIso8601String();
            $source->update(['metadata' => $metadata]);
        }

        return $this->success([
            'message' => 'Source verified successfully',
            'source' => $source->fresh(['country', 'verifier']),
        ]);
    }

    /**
     * Create a cross-reference between two sources
     *
     * @param Request $request
     * @param string $uuid
     * @return JsonResponse
     */
    public function crossReference(Request $request, string $uuid): JsonResponse
    {
        $source = Source::where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'referenced_source_uuid' => ['required', 'exists:sources,uuid'],
            'relationship_type' => ['required', Rule::in(SourceCrossReference::getRelationshipTypes())],
            'confidence_score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $referencedSource = Source::where('uuid', $validated['referenced_source_uuid'])->firstOrFail();

        // Check for existing relationship
        $existingRef = SourceCrossReference::where('source_id', $source->id)
            ->where('referenced_source_id', $referencedSource->id)
            ->where('relationship_type', $validated['relationship_type'])
            ->first();

        if ($existingRef) {
            return $this->error('This cross-reference already exists', 409);
        }

        // Prevent self-reference
        if ($source->id === $referencedSource->id) {
            return $this->error('A source cannot reference itself', 400);
        }

        $crossReference = $source->crossReferenceWith(
            $referencedSource,
            $validated['relationship_type'],
            $validated['confidence_score'] ?? 50,
            $validated['notes'] ?? null,
            $request->user()
        );

        // Log cross-reference creation
        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'source.cross_referenced',
            'auditable_type' => Source::class,
            'auditable_id' => $source->id,
            'properties' => [
                'referenced_source_id' => $referencedSource->id,
                'relationship_type' => $validated['relationship_type'],
            ],
        ]);

        return $this->success([
            'message' => 'Cross-reference created successfully',
            'cross_reference' => $crossReference->load(['source', 'referencedSource']),
        ], 201);
    }

    /**
     * Get cross-references for a source
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function getCrossReferences(string $uuid): JsonResponse
    {
        $source = Source::where('uuid', $uuid)->firstOrFail();

        $outgoing = $source->crossReferences()
            ->with(['referencedSource', 'creator', 'verifier'])
            ->get();

        $incoming = $source->referencedBy()
            ->with(['source', 'creator', 'verifier'])
            ->get();

        return $this->success([
            'outgoing' => $outgoing,
            'incoming' => $incoming,
            'total' => $outgoing->count() + $incoming->count(),
        ]);
    }

    /**
     * Autocomplete sources for search/selection
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function autocomplete(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['required', 'string', 'min:2', 'max:100'],
            'type' => ['nullable', Rule::in(Source::getSourceTypes())],
            'trusted_only' => ['nullable', 'boolean'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $query = Source::query()
            ->select(['id', 'uuid', 'name', 'url', 'source_type', 'reliability_score', 'verification_status', 'logo_url'])
            ->active()
            ->search($validated['q']);

        if (!empty($validated['type'])) {
            $query->ofType($validated['type']);
        }

        if ($request->boolean('trusted_only', false)) {
            $query->where(function ($q) {
                $q->where('verification_status', Source::STATUS_TRUSTED)
                    ->orWhere('verification_status', Source::STATUS_VERIFIED);
            });
        }

        $query->orderByReliability();

        $limit = $validated['limit'] ?? 10;
        $sources = $query->limit($limit)->get();

        return $this->success($sources);
    }

    /**
     * Get source statistics
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function statistics(Request $request): JsonResponse
    {
        // Overall counts
        $totalSources = Source::count();
        $activeSources = Source::active()->count();

        // By verification status
        $byStatus = Source::select('verification_status', DB::raw('count(*) as count'))
            ->groupBy('verification_status')
            ->pluck('count', 'verification_status');

        // By source type
        $byType = Source::select('source_type', DB::raw('count(*) as count'))
            ->groupBy('source_type')
            ->pluck('count', 'source_type');

        // Reliability distribution
        $reliabilityDistribution = [
            'excellent' => Source::where('reliability_score', '>=', 85)->count(),
            'high' => Source::whereBetween('reliability_score', [70, 84])->count(),
            'medium' => Source::whereBetween('reliability_score', [50, 69])->count(),
            'low' => Source::whereBetween('reliability_score', [30, 49])->count(),
            'very_low' => Source::where('reliability_score', '<', 30)->count(),
        ];

        // Average reliability
        $averageReliability = round(Source::active()->avg('reliability_score') ?? 0, 2);

        // Top 10 most reliable sources
        $topSources = Source::active()
            ->highlyReliable()
            ->orderByReliability()
            ->limit(10)
            ->get(['uuid', 'name', 'reliability_score', 'verification_status', 'source_type']);

        // Sources needing verification (not verified in 90 days)
        $needsVerification = Source::active()
            ->needsVerification(90)
            ->count();

        // Cross-reference statistics
        $totalCrossReferences = SourceCrossReference::count();
        $verifiedCrossReferences = SourceCrossReference::verified()->count();

        // By country (top 10)
        $byCountry = Source::select('country_id', DB::raw('count(*) as count'))
            ->whereNotNull('country_id')
            ->groupBy('country_id')
            ->orderByDesc('count')
            ->limit(10)
            ->with('country:id,name,iso_code')
            ->get()
            ->map(function ($item) {
                return [
                    'country' => $item->country?->name ?? 'Unknown',
                    'iso_code' => $item->country?->iso_code ?? '',
                    'count' => $item->count,
                ];
            });

        // Recent activity
        $recentlyAdded = Source::whereDate('created_at', '>=', now()->subDays(30))->count();
        $recentlyVerified = Source::whereDate('last_verified_at', '>=', now()->subDays(30))->count();

        return $this->success([
            'total_sources' => $totalSources,
            'active_sources' => $activeSources,
            'by_status' => $byStatus,
            'by_type' => $byType,
            'reliability_distribution' => $reliabilityDistribution,
            'average_reliability' => $averageReliability,
            'top_sources' => $topSources,
            'needs_verification' => $needsVerification,
            'cross_references' => [
                'total' => $totalCrossReferences,
                'verified' => $verifiedCrossReferences,
            ],
            'by_country' => $byCountry,
            'recent_activity' => [
                'added_last_30_days' => $recentlyAdded,
                'verified_last_30_days' => $recentlyVerified,
            ],
        ]);
    }

    /**
     * Get source types with labels
     *
     * @return JsonResponse
     */
    public function types(): JsonResponse
    {
        $types = [
            ['value' => Source::TYPE_NEWS_OUTLET, 'label' => 'News Outlet'],
            ['value' => Source::TYPE_SOCIAL_MEDIA, 'label' => 'Social Media'],
            ['value' => Source::TYPE_OFFICIAL, 'label' => 'Official Source'],
            ['value' => Source::TYPE_EYEWITNESS, 'label' => 'Eyewitness'],
            ['value' => Source::TYPE_SATELLITE, 'label' => 'Satellite Imagery'],
            ['value' => Source::TYPE_DOCUMENT, 'label' => 'Document/Report'],
        ];

        return $this->success($types);
    }

    /**
     * Get verification status options
     *
     * @return JsonResponse
     */
    public function statuses(): JsonResponse
    {
        $statuses = [
            ['value' => Source::STATUS_UNVERIFIED, 'label' => 'Unverified', 'color' => 'gray'],
            ['value' => Source::STATUS_VERIFIED, 'label' => 'Verified', 'color' => 'blue'],
            ['value' => Source::STATUS_TRUSTED, 'label' => 'Trusted', 'color' => 'green'],
            ['value' => Source::STATUS_UNRELIABLE, 'label' => 'Unreliable', 'color' => 'red'],
        ];

        return $this->success($statuses);
    }

    /**
     * Recalculate reliability scores for all sources
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function recalculateScores(Request $request): JsonResponse
    {
        $updated = 0;

        Source::active()->chunk(100, function ($sources) use (&$updated) {
            foreach ($sources as $source) {
                $oldScore = $source->reliability_score;
                $newScore = $source->calculateReliabilityScore();

                if ($oldScore !== $newScore) {
                    $updated++;
                }
            }
        });

        // Log the batch recalculation
        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'source.batch_recalculate',
            'auditable_type' => Source::class,
            'auditable_id' => 0,
            'properties' => ['updated_count' => $updated],
        ]);

        return $this->success([
            'message' => 'Reliability scores recalculated',
            'updated_count' => $updated,
        ]);
    }

    /**
     * Get events linked to a source
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function events(string $uuid): JsonResponse
    {
        $source = Source::where('uuid', $uuid)->firstOrFail();

        $events = $source->events()
            ->with(['eventType'])
            ->orderBy('occurred_at', 'desc')
            ->paginate($this->perPage);

        return $this->paginated($events);
    }

    /**
     * Record a report from this source
     *
     * @param Request $request
     * @param string $uuid
     * @return JsonResponse
     */
    public function recordReport(Request $request, string $uuid): JsonResponse
    {
        $source = Source::where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'accurate' => ['required', 'boolean'],
            'disputed' => ['nullable', 'boolean'],
        ]);

        $source->recordReport(
            $validated['accurate'],
            $validated['disputed'] ?? false
        );

        return $this->success([
            'message' => 'Report recorded',
            'source' => $source->fresh(),
        ]);
    }
}
