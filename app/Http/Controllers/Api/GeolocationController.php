<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AnalyzeGeolocationRequest;
use App\Http\Requests\DisputeGeolocationRequest;
use App\Http\Requests\StoreGeolocationVerificationRequest;
use App\Http\Requests\UpdateGeolocationVerificationRequest;
use App\Http\Requests\VerifyGeolocationRequest;
use App\Models\AuditLog;
use App\Models\GeolocationVerification;
use App\Services\GeolocationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Geolocation Controller
 *
 * Handles API endpoints for geolocation verification tools including
 * satellite imagery matching, shadow analysis, and coordinate verification.
 */
class GeolocationController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private readonly GeolocationService $geolocationService
    ) {
    }

    /**
     * List all geolocation verifications with filters.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = GeolocationVerification::query()
            ->with(['event', 'verifier']);

        // Filter by event
        if ($request->has('event_id')) {
            $query->forEvent((int) $request->event_id);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->status($request->status);
        }

        // Filter by method
        if ($request->has('method')) {
            $query->method($request->method);
        }

        // Filter by minimum confidence
        if ($request->has('min_confidence')) {
            $query->minConfidence((int) $request->min_confidence);
        }

        // Filter by verifier
        if ($request->has('verified_by')) {
            $query->verifiedBy((int) $request->verified_by);
        }

        // Filter by date range
        if ($request->has('from_date')) {
            $query->where('created_at', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->where('created_at', '<=', $request->to_date);
        }

        // Filter by bounding box
        if ($request->has(['sw_lat', 'sw_lng', 'ne_lat', 'ne_lng'])) {
            $query->withinBounds(
                (float) $request->sw_lat,
                (float) $request->sw_lng,
                (float) $request->ne_lat,
                (float) $request->ne_lng
            );
        }

        // Filter by radius from point
        if ($request->has(['lat', 'lng', 'radius'])) {
            $query->withinRadius(
                (float) $request->lat,
                (float) $request->lng,
                (float) $request->radius
            );
        }

        // Sorting
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $allowedSorts = ['created_at', 'confidence_score', 'verified_at', 'distance_from_original'];

        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection === 'asc' ? 'asc' : 'desc');
        }

        $verifications = $query->paginate($request->input('per_page', 25));

        // Transform coordinates for response
        $verifications->getCollection()->transform(function ($verification) {
            return $this->transformVerification($verification);
        });

        return response()->json([
            'data' => $verifications->items(),
            'meta' => [
                'current_page' => $verifications->currentPage(),
                'last_page' => $verifications->lastPage(),
                'per_page' => $verifications->perPage(),
                'total' => $verifications->total(),
            ],
        ]);
    }

    /**
     * Store a new geolocation verification.
     *
     * @param StoreGeolocationVerificationRequest $request
     * @return JsonResponse
     */
    public function store(StoreGeolocationVerificationRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $verification = $this->geolocationService->verifyCoordinates(
            $validated['event_id'],
            $validated['verification_method'],
            $validated,
            $request->user()
        );

        return response()->json([
            'data' => $this->transformVerification($verification->load(['event', 'verifier'])),
        ], 201);
    }

    /**
     * Display a specific geolocation verification.
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function show(string $uuid): JsonResponse
    {
        $verification = GeolocationVerification::with(['event', 'verifier'])
            ->where('uuid', $uuid)
            ->firstOrFail();

        return response()->json([
            'data' => $this->transformVerification($verification),
        ]);
    }

    /**
     * Update a geolocation verification.
     *
     * @param UpdateGeolocationVerificationRequest $request
     * @param string $uuid
     * @return JsonResponse
     */
    public function update(UpdateGeolocationVerificationRequest $request, string $uuid): JsonResponse
    {
        $verification = GeolocationVerification::where('uuid', $uuid)->firstOrFail();

        // Only allow updates if still pending
        if (!$verification->isPending()) {
            return response()->json(['message' => 'Cannot update a verification that is not pending'], 422);
        }

        $validated = $request->validated();

        DB::transaction(function () use ($verification, $validated) {
            // Update verified coordinates if provided
            if (isset($validated['verified_latitude']) && isset($validated['verified_longitude'])) {
                $verification->verified_coordinates = DB::raw(
                    sprintf(
                        "ST_GeomFromText('POINT(%f %f)', 4326)",
                        (float) $validated['verified_longitude'],
                        (float) $validated['verified_latitude']
                    )
                );
                unset($validated['verified_latitude'], $validated['verified_longitude']);
            }

            $verification->fill($validated);
            $verification->save();

            // Recalculate distance and confidence
            if ($verification->verified_coordinates) {
                $verification->calculateDistanceFromOriginal();
            }
            $verification->calculateConfidence();
            $verification->save();
        });

        return response()->json([
            'data' => $this->transformVerification($verification->fresh(['event', 'verifier'])),
        ]);
    }

    /**
     * Mark a verification as verified.
     *
     * @param VerifyGeolocationRequest $request
     * @param string $uuid
     * @return JsonResponse
     */
    public function verify(VerifyGeolocationRequest $request, string $uuid): JsonResponse
    {
        $verification = GeolocationVerification::where('uuid', $uuid)->firstOrFail();

        if ($verification->isVerified()) {
            return response()->json(['message' => 'This verification is already verified'], 422);
        }

        if ($verification->isRejected()) {
            return response()->json(['message' => 'Cannot verify a rejected verification'], 422);
        }

        $validated = $request->validated();
        $user = $request->user();

        DB::transaction(function () use ($verification, $validated, $user) {
            // Update verified coordinates if provided
            if (isset($validated['verified_latitude']) && isset($validated['verified_longitude'])) {
                $verification->setVerifiedCoordinates(
                    (float) $validated['verified_latitude'],
                    (float) $validated['verified_longitude']
                );
                $verification->save();
                $verification->calculateDistanceFromOriginal();
            }

            // Override confidence if provided
            if (isset($validated['confidence_score'])) {
                $verification->confidence_score = $validated['confidence_score'];
            }

            $verification->markAsVerified($user, $validated['notes'] ?? null);

            // Log the verification
            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'geolocation.verification.verified',
                'auditable_type' => GeolocationVerification::class,
                'auditable_id' => $verification->id,
                'properties' => [
                    'event_id' => $verification->event_id,
                    'confidence_score' => $verification->confidence_score,
                    'method' => $verification->verification_method,
                ],
            ]);
        });

        return response()->json([
            'message' => 'Verification marked as verified',
            'data' => $this->transformVerification($verification->fresh(['event', 'verifier'])),
        ]);
    }

    /**
     * Dispute a verification.
     *
     * @param DisputeGeolocationRequest $request
     * @param string $uuid
     * @return JsonResponse
     */
    public function dispute(DisputeGeolocationRequest $request, string $uuid): JsonResponse
    {
        $verification = GeolocationVerification::where('uuid', $uuid)->firstOrFail();

        if ($verification->isDisputed()) {
            return response()->json(['message' => 'This verification is already disputed'], 422);
        }

        if ($verification->isRejected()) {
            return response()->json(['message' => 'Cannot dispute a rejected verification'], 422);
        }

        $validated = $request->validated();
        $user = $request->user();

        DB::transaction(function () use ($verification, $validated, $user) {
            // Build dispute notes
            $notes = "DISPUTED: {$validated['reason']}";

            if (!empty($validated['evidence'])) {
                $notes .= "\n\nEvidence provided:";
                foreach ($validated['evidence'] as $evidence) {
                    $notes .= "\n- [{$evidence['type']}]: {$evidence['value']}";
                }
            }

            if (isset($validated['alternative_latitude']) && isset($validated['alternative_longitude'])) {
                $notes .= sprintf(
                    "\n\nAlternative coordinates suggested: %f, %f",
                    $validated['alternative_latitude'],
                    $validated['alternative_longitude']
                );
            }

            $verification->markAsDisputed($notes);

            // Log the dispute
            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'geolocation.verification.disputed',
                'auditable_type' => GeolocationVerification::class,
                'auditable_id' => $verification->id,
                'properties' => [
                    'event_id' => $verification->event_id,
                    'reason' => $validated['reason'],
                    'has_evidence' => !empty($validated['evidence']),
                    'has_alternative_coords' => isset($validated['alternative_latitude']),
                ],
            ]);
        });

        return response()->json([
            'message' => 'Verification disputed successfully',
            'data' => $this->transformVerification($verification->fresh(['event', 'verifier'])),
        ]);
    }

    /**
     * Reject a verification.
     *
     * @param Request $request
     * @param string $uuid
     * @return JsonResponse
     */
    public function reject(Request $request, string $uuid): JsonResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:5000'],
        ]);

        $verification = GeolocationVerification::where('uuid', $uuid)->firstOrFail();

        if ($verification->isRejected()) {
            return response()->json(['message' => 'This verification is already rejected'], 422);
        }

        $user = $request->user();

        DB::transaction(function () use ($verification, $validated, $user) {
            $verification->markAsRejected($user, "REJECTED: {$validated['reason']}");

            // Log the rejection
            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'geolocation.verification.rejected',
                'auditable_type' => GeolocationVerification::class,
                'auditable_id' => $verification->id,
                'properties' => [
                    'event_id' => $verification->event_id,
                    'reason' => $validated['reason'],
                ],
            ]);
        });

        return response()->json([
            'message' => 'Verification rejected',
            'data' => $this->transformVerification($verification->fresh(['event', 'verifier'])),
        ]);
    }

    /**
     * Run analysis on an image.
     *
     * @param AnalyzeGeolocationRequest $request
     * @return JsonResponse
     */
    public function analyze(AnalyzeGeolocationRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $analysisType = $validated['analysis_type'];

        // Get image path
        $imagePath = $request->hasFile('image')
            ? $request->file('image')->getPathname()
            : $validated['image_url'];

        $result = match ($analysisType) {
            'satellite_match' => $this->geolocationService->matchSatelliteImagery(
                $imagePath,
                [
                    $validated['region']['sw_lat'],
                    $validated['region']['sw_lng'],
                    $validated['region']['ne_lat'],
                    $validated['region']['ne_lng'],
                ],
                $validated['options'] ?? []
            ),
            'shadow_analysis' => $this->analyzeShadowsWithSunPosition($validated, $imagePath),
            'metadata_extraction' => $this->geolocationService->extractImageMetadata($imagePath),
            default => throw new \InvalidArgumentException("Unknown analysis type: {$analysisType}"),
        };

        // Log analysis
        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'geolocation.analysis.performed',
            'auditable_type' => 'image_analysis',
            'auditable_id' => 0,
            'properties' => [
                'analysis_type' => $analysisType,
                'has_file_upload' => $request->hasFile('image'),
            ],
        ]);

        return response()->json(['data' => $result]);
    }

    /**
     * Get a verification report.
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function report(string $uuid): JsonResponse
    {
        $verification = GeolocationVerification::with(['event', 'verifier'])
            ->where('uuid', $uuid)
            ->firstOrFail();

        $report = $this->geolocationService->generateVerificationReport($verification);

        return response()->json(['data' => $report]);
    }

    /**
     * Get verification statistics for an event.
     *
     * @param int $eventId
     * @return JsonResponse
     */
    public function eventStats(int $eventId): JsonResponse
    {
        $stats = $this->geolocationService->getEventVerificationStats($eventId);

        return response()->json(['data' => $stats]);
    }

    /**
     * Calculate sun position for a given location and time.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sunPosition(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'datetime' => ['required', 'date'],
        ]);

        $dateTime = Carbon::parse($validated['datetime']);

        $sunPosition = $this->geolocationService->calculateSunPosition(
            (float) $validated['latitude'],
            (float) $validated['longitude'],
            $dateTime
        );

        return response()->json(['data' => $sunPosition]);
    }

    /**
     * Get available verification methods.
     *
     * @return JsonResponse
     */
    public function methods(): JsonResponse
    {
        $methods = [
            [
                'key' => GeolocationVerification::METHOD_SATELLITE_MATCH,
                'name' => 'Satellite Imagery Match',
                'description' => 'Compare ground images with satellite/aerial imagery to verify location',
                'confidence_base' => 40,
            ],
            [
                'key' => GeolocationVerification::METHOD_LANDMARK_MATCH,
                'name' => 'Landmark Matching',
                'description' => 'Identify and match visible landmarks to verify location',
                'confidence_base' => 35,
            ],
            [
                'key' => GeolocationVerification::METHOD_SHADOW_ANALYSIS,
                'name' => 'Shadow Analysis',
                'description' => 'Analyze shadows to estimate sun position and verify time/location',
                'confidence_base' => 30,
            ],
            [
                'key' => GeolocationVerification::METHOD_METADATA_EXTRACTION,
                'name' => 'Image Metadata (EXIF)',
                'description' => 'Extract GPS and timestamp data from image metadata',
                'confidence_base' => 45,
            ],
            [
                'key' => GeolocationVerification::METHOD_CROWD_VERIFIED,
                'name' => 'Crowd Verification',
                'description' => 'Location verified by multiple independent sources',
                'confidence_base' => 25,
            ],
        ];

        return response()->json(['data' => $methods]);
    }

    /**
     * Delete a verification.
     *
     * @param Request $request
     * @param string $uuid
     * @return JsonResponse
     */
    public function destroy(Request $request, string $uuid): JsonResponse
    {
        $verification = GeolocationVerification::where('uuid', $uuid)->firstOrFail();

        // Only allow deletion of pending verifications
        if (!$verification->isPending()) {
            return response()->json(['message' => 'Only pending verifications can be deleted'], 422);
        }

        $user = $request->user();

        // Log deletion
        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'geolocation.verification.deleted',
            'auditable_type' => GeolocationVerification::class,
            'auditable_id' => $verification->id,
            'properties' => [
                'event_id' => $verification->event_id,
                'method' => $verification->verification_method,
            ],
        ]);

        $verification->delete();

        return response()->json(['message' => 'Verification deleted successfully']);
    }

    // ========================================================================
    // Project-based Geolocation Methods (Simpler Workflow)
    // ========================================================================

    /**
     * Create a new geolocation project.
     */
    public function createProject(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'media_url' => 'nullable|url',
            'media_type' => 'nullable|in:image,video',
        ]);

        $projectId = DB::table('geolocation_projects')->insertGetId([
            'uuid' => Str::uuid(),
            'user_id' => auth()->id(),
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'media_url' => $validated['media_url'] ?? null,
            'media_type' => $validated['media_type'] ?? null,
            'status' => 'in_progress',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $project = DB::table('geolocation_projects')->where('id', $projectId)->first();

        return response()->json([
            'message' => 'Geolocation project created',
            'data' => $project,
        ], 201);
    }

    /**
     * List user's geolocation projects.
     */
    public function projects(Request $request): JsonResponse
    {
        $projects = DB::table('geolocation_projects')
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 25));

        return response()->json([
            'data' => $projects->items(),
            'meta' => [
                'current_page' => $projects->currentPage(),
                'total' => $projects->total(),
            ],
        ]);
    }

    /**
     * Get a specific project.
     */
    public function showProject(string $uuid): JsonResponse
    {
        $project = DB::table('geolocation_projects')
            ->where('uuid', $uuid)
            ->where('user_id', auth()->id())
            ->first();

        if (!$project) {
            return response()->json(['message' => 'Project not found'], 404);
        }

        $markers = DB::table('geolocation_markers')
            ->where('project_id', $project->id)
            ->get();

        return response()->json([
            'data' => $project,
            'markers' => $markers,
        ]);
    }

    /**
     * Add a reference marker to a project.
     */
    public function addMarker(Request $request, string $uuid): JsonResponse
    {
        $project = DB::table('geolocation_projects')
            ->where('uuid', $uuid)
            ->where('user_id', auth()->id())
            ->first();

        if (!$project) {
            return response()->json(['message' => 'Project not found'], 404);
        }

        $validated = $request->validate([
            'type' => 'required|in:landmark,building,road,vegetation,shadow,other',
            'description' => 'required|string',
            'image_x' => 'nullable|numeric',
            'image_y' => 'nullable|numeric',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'confidence' => 'nullable|in:high,medium,low',
        ]);

        $markerId = DB::table('geolocation_markers')->insertGetId([
            'uuid' => Str::uuid(),
            'project_id' => $project->id,
            'type' => $validated['type'],
            'description' => $validated['description'],
            'image_x' => $validated['image_x'] ?? null,
            'image_y' => $validated['image_y'] ?? null,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'confidence' => $validated['confidence'] ?? 'medium',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $marker = DB::table('geolocation_markers')->where('id', $markerId)->first();

        return response()->json([
            'message' => 'Marker added',
            'data' => $marker,
        ], 201);
    }

    /**
     * Reverse geocode coordinates.
     */
    public function reverseGeocode(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        // Use OpenStreetMap Nominatim for reverse geocoding
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'OsintWeb/1.0',
            ])->get(config('services.nominatim.url'), [
                'lat' => $validated['latitude'],
                'lon' => $validated['longitude'],
                'format' => 'json',
                'addressdetails' => 1,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return response()->json([
                    'coordinates' => [
                        'latitude' => $validated['latitude'],
                        'longitude' => $validated['longitude'],
                    ],
                    'address' => $data['address'] ?? null,
                    'display_name' => $data['display_name'] ?? null,
                    'osm_type' => $data['osm_type'] ?? null,
                    'osm_id' => $data['osm_id'] ?? null,
                ]);
            }
        } catch (\Exception $e) {
            // Fallback to basic response
        }

        return response()->json([
            'coordinates' => [
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
            ],
            'message' => 'Geocoding service unavailable',
        ]);
    }

    /**
     * Get weather data for chronolocation.
     */
    public function weatherData(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'date' => 'required|date',
        ]);

        // Delegate to weather service if available
        return response()->json([
            'location' => [
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
            ],
            'date' => $validated['date'],
            'note' => 'Use /api/weather/location endpoint for full weather data',
        ]);
    }

    /**
     * Submit geolocation result for a project.
     */
    public function submitResult(Request $request, string $uuid): JsonResponse
    {
        $project = DB::table('geolocation_projects')
            ->where('uuid', $uuid)
            ->where('user_id', auth()->id())
            ->first();

        if (!$project) {
            return response()->json(['message' => 'Project not found'], 404);
        }

        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'confidence' => 'required|in:high,medium,low',
            'methodology' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        DB::table('geolocation_projects')
            ->where('uuid', $uuid)
            ->update([
                'result_latitude' => $validated['latitude'],
                'result_longitude' => $validated['longitude'],
                'result_confidence' => $validated['confidence'],
                'methodology' => $validated['methodology'],
                'notes' => $validated['notes'] ?? null,
                'status' => 'completed',
                'completed_at' => now(),
                'updated_at' => now(),
            ]);

        return response()->json([
            'message' => 'Geolocation result saved',
            'data' => DB::table('geolocation_projects')->where('uuid', $uuid)->first(),
        ]);
    }

    /**
     * Get satellite imagery layers.
     */
    public function satelliteLayers(): JsonResponse
    {
        return response()->json([
            'layers' => [
                [
                    'id' => 'osm',
                    'name' => 'OpenStreetMap',
                    'url' => 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
                    'type' => 'base',
                    'attribution' => '© OpenStreetMap contributors',
                ],
                [
                    'id' => 'satellite',
                    'name' => 'Satellite (ESRI)',
                    'url' => 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
                    'type' => 'satellite',
                    'attribution' => '© Esri',
                ],
                [
                    'id' => 'topo',
                    'name' => 'Topographic',
                    'url' => 'https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png',
                    'type' => 'topo',
                    'attribution' => '© OpenTopoMap',
                ],
            ],
            'note' => 'High-resolution commercial imagery requires separate API integration (Maxar, Planet, etc.)',
        ]);
    }

    // ========================================================================
    // Private Helper Methods
    // ========================================================================

    /**
     * Transform a verification for API response.
     *
     * @param GeolocationVerification $verification
     * @return array
     */
    private function transformVerification(GeolocationVerification $verification): array
    {
        return [
            'uuid' => $verification->uuid,
            'event_id' => $verification->event_id,
            'event' => $verification->relationLoaded('event') && $verification->event ? [
                'uuid' => $verification->event->uuid,
                'title' => $verification->event->title,
            ] : null,
            'original_coordinates' => $verification->getOriginalCoordinatesArray(),
            'verified_coordinates' => $verification->getVerifiedCoordinatesArray(),
            'verification_method' => $verification->verification_method,
            'method_display' => $verification->getMethodDisplayName(),
            'confidence_score' => $verification->confidence_score,
            'confidence_level' => $verification->getConfidenceLevel(),
            'satellite_image_url' => $verification->satellite_image_url,
            'ground_image_url' => $verification->ground_image_url,
            'landmark_matches' => $verification->landmark_matches,
            'shadow_analysis_data' => $verification->shadow_analysis_data,
            'metadata' => $verification->metadata,
            'verification_notes' => $verification->verification_notes,
            'verified_by' => $verification->verified_by,
            'verifier' => $verification->relationLoaded('verifier') && $verification->verifier ? [
                'id' => $verification->verifier->id,
                'name' => $verification->verifier->name,
            ] : null,
            'verified_at' => $verification->verified_at?->toIso8601String(),
            'status' => $verification->status,
            'distance_from_original' => $verification->distance_from_original,
            'created_at' => $verification->created_at->toIso8601String(),
            'updated_at' => $verification->updated_at->toIso8601String(),
        ];
    }

    /**
     * Run shadow analysis with sun position calculation.
     *
     * @param array $validated
     * @param string $imagePath
     * @return array
     */
    private function analyzeShadowsWithSunPosition(array $validated, string $imagePath): array
    {
        $result = $this->geolocationService->analyzeShadows(
            $imagePath,
            $validated['date'] ?? null,
            $validated['options'] ?? []
        );

        // If approximate coordinates provided, calculate sun position
        if (isset($validated['approximate_latitude']) && isset($validated['approximate_longitude'])) {
            $dateTime = isset($validated['date'])
                ? Carbon::parse($validated['date'])->midDay()
                : now();

            $result['sun_position_at_location'] = $this->geolocationService->calculateSunPosition(
                (float) $validated['approximate_latitude'],
                (float) $validated['approximate_longitude'],
                $dateTime
            );
        }

        return $result;
    }
}
