<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\PreserveEvidenceJob;
use App\Models\EvidenceSnapshot;
use App\Models\PreservedEvidence;
use App\Services\EvidencePreservationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * Evidence Controller for managing preserved web evidence
 */
class EvidenceController extends Controller
{
    public function __construct(
        protected EvidencePreservationService $preservationService
    ) {}

    /**
     * List all preserved evidence for the authenticated user
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = PreservedEvidence::query()
            ->with(['creator', 'event', 'snapshots'])
            ->byCreator($request->user()->id);

        // Filter by status
        if ($request->has('status')) {
            $query->status($request->status);
        }

        // Filter by content type
        if ($request->has('type')) {
            $query->type($request->type);
        }

        // Filter by event
        if ($request->has('event_id')) {
            $query->forEvent($request->event_id);
        }

        // Search by URL
        if ($request->has('search')) {
            $query->searchUrl($request->search);
        }

        // Filter by verification status
        if ($request->has('verified')) {
            $query->where('is_verified', $request->boolean('verified'));
        }

        // Filter by date range
        if ($request->has('from')) {
            $query->where('created_at', '>=', $request->from);
        }
        if ($request->has('to')) {
            $query->where('created_at', '<=', $request->to);
        }

        $query->orderBy('created_at', 'desc');

        $evidence = $query->paginate($this->perPage);

        return $this->paginated($evidence);
    }

    /**
     * Submit a URL for preservation
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function preserve(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'url' => ['required', 'url', 'max:2048'],
            'content_type' => [
                'nullable',
                Rule::in(PreservedEvidence::getContentTypes()),
            ],
            'event_id' => ['nullable', 'exists:events,id'],
            'expires_at' => ['nullable', 'date', 'after:now'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:100'],
            'preserve_to_ipfs' => ['boolean'],
            'submit_to_wayback' => ['boolean'],
            'capture_screenshot' => ['boolean'],
        ]);

        // Create the evidence record
        $evidence = $this->preservationService->preserveUrl(
            $validated['url'],
            $request->user(),
            [
                'content_type' => $validated['content_type'] ?? null,
                'event_id' => $validated['event_id'] ?? null,
                'expires_at' => $validated['expires_at'] ?? null,
            ]
        );

        // Store tags if provided
        if (!empty($validated['tags'])) {
            foreach ($validated['tags'] as $tag) {
                $evidence->tags()->create(['tag' => $tag]);
            }
        }

        // Dispatch background job for actual preservation
        PreserveEvidenceJob::dispatch($evidence, [
            'preserve_to_ipfs' => $validated['preserve_to_ipfs'] ?? false,
            'submit_to_wayback' => $validated['submit_to_wayback'] ?? true,
            'capture_screenshot' => $validated['capture_screenshot'] ?? false,
        ]);

        return $this->success([
            'message' => 'Evidence preservation queued',
            'evidence' => $evidence->fresh(['creator', 'event']),
        ], 202);
    }

    /**
     * Get preservation status for specific evidence
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function status(string $uuid): JsonResponse
    {
        $evidence = PreservedEvidence::with(['creator', 'event', 'snapshots'])
            ->where('uuid', $uuid)
            ->firstOrFail();

        // Check if user has access
        if ($evidence->created_by !== $this->user()->id) {
            return $this->forbidden('You do not have access to this evidence');
        }

        return $this->success([
            'evidence' => $evidence,
            'status' => $evidence->capture_status,
            'is_complete' => $evidence->capture_status === PreservedEvidence::STATUS_CAPTURED,
            'can_retry' => $evidence->canRetry(),
            'access_url' => $evidence->getAccessUrl(),
            'verification' => [
                'is_verified' => $evidence->is_verified,
                'last_verified_at' => $evidence->last_verified_at,
            ],
        ]);
    }

    /**
     * Show specific evidence details
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function show(string $uuid): JsonResponse
    {
        $evidence = PreservedEvidence::with(['creator', 'event', 'snapshots'])
            ->where('uuid', $uuid)
            ->firstOrFail();

        // Check if user has access
        if ($evidence->created_by !== $this->user()->id) {
            return $this->forbidden('You do not have access to this evidence');
        }

        return $this->success($evidence);
    }

    /**
     * Verify the integrity of preserved evidence
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function verify(string $uuid): JsonResponse
    {
        $evidence = PreservedEvidence::where('uuid', $uuid)->firstOrFail();

        // Check if user has access
        if ($evidence->created_by !== $this->user()->id) {
            return $this->forbidden('You do not have access to this evidence');
        }

        if ($evidence->capture_status !== PreservedEvidence::STATUS_CAPTURED) {
            return $this->error('Evidence has not been captured yet', 400);
        }

        $result = $this->preservationService->verifyHash($evidence->id);

        return $this->success($result);
    }

    /**
     * Download preserved content
     *
     * @param string $uuid
     * @return \Symfony\Component\HttpFoundation\StreamedResponse|JsonResponse
     */
    public function download(string $uuid)
    {
        $evidence = PreservedEvidence::where('uuid', $uuid)->firstOrFail();

        // Check if user has access
        if ($evidence->created_by !== $this->user()->id) {
            return $this->forbidden('You do not have access to this evidence');
        }

        if ($evidence->capture_status !== PreservedEvidence::STATUS_CAPTURED) {
            return $this->error('Evidence has not been captured yet', 400);
        }

        if (!$evidence->local_path || !Storage::disk('evidence')->exists($evidence->local_path)) {
            return $this->error('Local file not found', 404);
        }

        $fileName = $evidence->uuid . '.' . pathinfo($evidence->local_path, PATHINFO_EXTENSION);

        return Storage::disk('evidence')->download($evidence->local_path, $fileName, [
            'Content-Type' => $evidence->metadata['response_headers']['Content-Type'] ?? 'application/octet-stream',
        ]);
    }

    /**
     * List version snapshots for evidence
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function snapshots(string $uuid): JsonResponse
    {
        $evidence = PreservedEvidence::where('uuid', $uuid)->firstOrFail();

        // Check if user has access
        if ($evidence->created_by !== $this->user()->id) {
            return $this->forbidden('You do not have access to this evidence');
        }

        $snapshots = EvidenceSnapshot::forEvidence($evidence->id)
            ->latest()
            ->get()
            ->map(function ($snapshot) {
                return [
                    'id' => $snapshot->id,
                    'snapshot_hash' => $snapshot->snapshot_hash,
                    'file_size' => $snapshot->file_size,
                    'human_file_size' => $snapshot->human_file_size,
                    'is_primary' => $snapshot->is_primary,
                    'captured_at' => $snapshot->captured_at,
                    'has_changed' => $snapshot->hasChanged(),
                    'metadata' => $snapshot->metadata,
                ];
            });

        return $this->success([
            'evidence_id' => $evidence->id,
            'evidence_uuid' => $evidence->uuid,
            'total_snapshots' => $snapshots->count(),
            'snapshots' => $snapshots,
        ]);
    }

    /**
     * Create a new snapshot of existing evidence
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function createSnapshot(string $uuid): JsonResponse
    {
        $evidence = PreservedEvidence::where('uuid', $uuid)->firstOrFail();

        // Check if user has access
        if ($evidence->created_by !== $this->user()->id) {
            return $this->forbidden('You do not have access to this evidence');
        }

        if ($evidence->capture_status !== PreservedEvidence::STATUS_CAPTURED) {
            return $this->error('Evidence has not been captured yet', 400);
        }

        try {
            $snapshot = $this->preservationService->createSnapshot($evidence);

            return $this->success([
                'message' => 'Snapshot created successfully',
                'snapshot' => [
                    'id' => $snapshot->id,
                    'snapshot_hash' => $snapshot->snapshot_hash,
                    'file_size' => $snapshot->file_size,
                    'captured_at' => $snapshot->captured_at,
                ],
            ], 201);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    /**
     * Retry failed preservation
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function retry(string $uuid): JsonResponse
    {
        $evidence = PreservedEvidence::where('uuid', $uuid)->firstOrFail();

        // Check if user has access
        if ($evidence->created_by !== $this->user()->id) {
            return $this->forbidden('You do not have access to this evidence');
        }

        if (!$evidence->canRetry()) {
            return $this->error('This evidence cannot be retried (max retries exceeded or not in failed state)', 400);
        }

        // Reset status and dispatch job
        $evidence->update([
            'capture_status' => PreservedEvidence::STATUS_PENDING,
            'error_message' => null,
        ]);

        PreserveEvidenceJob::dispatch($evidence, [
            'preserve_to_ipfs' => (bool) $evidence->ipfs_hash,
            'submit_to_wayback' => (bool) $evidence->archived_url,
        ]);

        return $this->success([
            'message' => 'Evidence preservation retry queued',
            'evidence' => $evidence->fresh(),
        ], 202);
    }

    /**
     * Delete preserved evidence
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function destroy(string $uuid): JsonResponse
    {
        $evidence = PreservedEvidence::where('uuid', $uuid)->firstOrFail();

        // Check if user has access
        if ($evidence->created_by !== $this->user()->id) {
            return $this->forbidden('You do not have access to this evidence');
        }

        // Delete local files
        if ($evidence->local_path && Storage::disk('evidence')->exists($evidence->local_path)) {
            Storage::disk('evidence')->delete($evidence->local_path);
        }

        // Delete snapshots
        foreach ($evidence->snapshots as $snapshot) {
            if (Storage::disk('evidence')->exists($snapshot->snapshot_path)) {
                Storage::disk('evidence')->delete($snapshot->snapshot_path);
            }
        }

        // Delete screenshot if exists
        if (!empty($evidence->metadata['screenshot_path'])) {
            $screenshotPath = $evidence->metadata['screenshot_path'];
            if (Storage::disk('evidence')->exists($screenshotPath)) {
                Storage::disk('evidence')->delete($screenshotPath);
            }
        }

        $evidence->delete();

        return $this->success(['message' => 'Evidence deleted successfully']);
    }

    /**
     * Get evidence statistics for the user
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function stats(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $stats = [
            'total' => PreservedEvidence::byCreator($userId)->count(),
            'by_status' => [
                'pending' => PreservedEvidence::byCreator($userId)->pending()->count(),
                'captured' => PreservedEvidence::byCreator($userId)->captured()->count(),
                'failed' => PreservedEvidence::byCreator($userId)->failed()->count(),
            ],
            'by_type' => [],
            'total_size' => PreservedEvidence::byCreator($userId)->sum('file_size'),
            'verified_count' => PreservedEvidence::byCreator($userId)->where('is_verified', true)->count(),
            'ipfs_count' => PreservedEvidence::byCreator($userId)->whereNotNull('ipfs_hash')->count(),
            'wayback_count' => PreservedEvidence::byCreator($userId)->whereNotNull('archived_url')->count(),
        ];

        foreach (PreservedEvidence::getContentTypes() as $type) {
            $stats['by_type'][$type] = PreservedEvidence::byCreator($userId)->type($type)->count();
        }

        return $this->success($stats);
    }

    /**
     * Get available content types
     *
     * @return JsonResponse
     */
    public function types(): JsonResponse
    {
        return $this->success([
            'content_types' => PreservedEvidence::getContentTypes(),
            'statuses' => PreservedEvidence::getStatuses(),
        ]);
    }
}
