<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessVideoAnalysisJob;
use App\Models\VideoAnalysis;
use App\Models\VideoKeyframe;
use App\Services\VideoAnalysisService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VideoAnalysisController extends Controller
{
    public function __construct(
        protected VideoAnalysisService $videoAnalysisService
    ) {}

    /**
     * List video analyses.
     */
    public function index(Request $request): JsonResponse
    {
        $query = VideoAnalysis::with('creator:id,name,uuid')
            ->where('created_by', $request->user()->id);

        // Filter by status
        if ($request->has('status')) {
            $query->where('analysis_status', $request->status);
        }

        // Filter by event
        if ($request->has('event_id')) {
            $query->where('event_id', $request->event_id);
        }

        // Filter by GPS availability
        if ($request->boolean('with_gps')) {
            $query->withGps();
        }

        // Search by filename
        if ($request->has('search')) {
            $query->search($request->search);
        }

        $analyses = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'data' => $analyses->map(fn($a) => $this->formatVideoAnalysis($a)),
            'meta' => [
                'current_page' => $analyses->currentPage(),
                'last_page' => $analyses->lastPage(),
                'per_page' => $analyses->perPage(),
                'total' => $analyses->total(),
            ],
        ]);
    }

    /**
     * Upload video for analysis.
     */
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'video' => [
                'required',
                'file',
                'mimetypes:' . implode(',', VideoAnalysis::getSupportedMimeTypes()),
                'max:512000', // 500MB
            ],
            'event_id' => 'nullable|exists:events,id',
            'auto_analyze' => 'nullable|boolean',
            'extract_keyframes' => 'nullable|boolean',
            'keyframe_interval' => 'nullable|numeric|min:0.5|max:60',
            'detect_scene_changes' => 'nullable|boolean',
        ]);

        $analysis = $this->videoAnalysisService->storeVideo(
            $request->file('video'),
            $request->user(),
            $request->event_id
        );

        // Dispatch analysis job if auto_analyze is true
        if ($request->boolean('auto_analyze', true)) {
            ProcessVideoAnalysisJob::dispatch($analysis, [
                'extract_keyframes' => $request->boolean('extract_keyframes', true),
                'keyframe_interval' => $request->get('keyframe_interval', 5.0),
                'detect_scene_changes' => $request->boolean('detect_scene_changes', true),
            ]);
        }

        return response()->json([
            'message' => 'Video uploaded successfully',
            'data' => $this->formatVideoAnalysis($analysis),
        ], 201);
    }

    /**
     * Trigger video analysis.
     */
    public function analyze(Request $request, string $uuid): JsonResponse
    {
        $analysis = $this->findAnalysisOrFail($uuid, $request->user()->id);

        if ($analysis->isProcessing()) {
            return response()->json([
                'message' => 'Analysis is already in progress',
                'data' => $this->formatVideoAnalysis($analysis),
            ], 409);
        }

        $request->validate([
            'extract_keyframes' => 'nullable|boolean',
            'keyframe_interval' => 'nullable|numeric|min:0.5|max:60',
            'detect_scene_changes' => 'nullable|boolean',
            'scene_threshold' => 'nullable|numeric|min:0.1|max:0.9',
        ]);

        // Reset if previously failed
        if ($analysis->hasFailed()) {
            $analysis->resetForRetry();
        }

        ProcessVideoAnalysisJob::dispatch($analysis, [
            'extract_keyframes' => $request->boolean('extract_keyframes', true),
            'keyframe_interval' => $request->get('keyframe_interval', 5.0),
            'detect_scene_changes' => $request->boolean('detect_scene_changes', true),
            'scene_threshold' => $request->get('scene_threshold', 0.4),
        ]);

        return response()->json([
            'message' => 'Analysis job queued',
            'data' => $this->formatVideoAnalysis($analysis->fresh()),
        ], 202);
    }

    /**
     * Get analysis details.
     */
    public function show(Request $request, string $uuid): JsonResponse
    {
        $analysis = $this->findAnalysisOrFail($uuid, $request->user()->id);
        $analysis->load(['creator:id,name,uuid', 'event:id,uuid,title']);

        return response()->json([
            'data' => $this->formatVideoAnalysis($analysis, true),
        ]);
    }

    /**
     * Get raw metadata.
     */
    public function metadata(Request $request, string $uuid): JsonResponse
    {
        $analysis = $this->findAnalysisOrFail($uuid, $request->user()->id);

        return response()->json([
            'data' => [
                'uuid' => $analysis->uuid,
                'metadata' => $analysis->metadata,
                'exif_data' => $analysis->exif_data,
                'device_info' => $analysis->device_info,
                'gps' => $analysis->getGpsLocation(),
                'recording_datetime' => $analysis->recording_datetime,
                'timezone' => $analysis->timezone,
            ],
        ]);
    }

    /**
     * List keyframes for a video.
     */
    public function keyframes(Request $request, string $uuid): JsonResponse
    {
        $analysis = $this->findAnalysisOrFail($uuid, $request->user()->id);

        $query = $analysis->keyframes();

        // Filter by scene changes only
        if ($request->boolean('scene_changes_only')) {
            $query->sceneChanges();
        }

        // Filter by representative frames
        if ($request->boolean('representative_only')) {
            $query->representative();
        }

        // Filter by time range
        if ($request->has('start_time') && $request->has('end_time')) {
            $query->inTimeRange(
                (float) $request->start_time,
                (float) $request->end_time
            );
        }

        // Filter by OCR text
        if ($request->has('with_ocr')) {
            $query->withOcr();
        }

        // Filter by detected objects
        if ($request->has('with_objects')) {
            $query->withObjects();
        }

        $keyframes = $query->orderBy('timestamp_seconds')
            ->paginate($request->get('per_page', 50));

        return response()->json([
            'data' => $keyframes->map(fn($k) => $this->formatKeyframe($k)),
            'meta' => [
                'current_page' => $keyframes->currentPage(),
                'last_page' => $keyframes->lastPage(),
                'per_page' => $keyframes->perPage(),
                'total' => $keyframes->total(),
            ],
        ]);
    }

    /**
     * Extract frame at specific timestamp.
     */
    public function extractFrame(Request $request, string $uuid): JsonResponse
    {
        $analysis = $this->findAnalysisOrFail($uuid, $request->user()->id);

        if (!$analysis->isCompleted()) {
            return response()->json([
                'message' => 'Video analysis must be completed before extracting frames',
            ], 400);
        }

        $request->validate([
            'timestamp' => 'required|numeric|min:0',
        ]);

        $timestamp = (float) $request->timestamp;

        // Validate timestamp is within video duration
        if ($analysis->duration_seconds && $timestamp > $analysis->duration_seconds) {
            return response()->json([
                'message' => 'Timestamp exceeds video duration',
                'max_timestamp' => $analysis->duration_seconds,
            ], 400);
        }

        $keyframe = $this->videoAnalysisService->getFrameAt($analysis, $timestamp);

        if (!$keyframe) {
            return response()->json([
                'message' => 'Failed to extract frame at specified timestamp',
            ], 500);
        }

        return response()->json([
            'message' => 'Frame extracted successfully',
            'data' => $this->formatKeyframe($keyframe),
        ]);
    }

    /**
     * Get specific keyframe details.
     */
    public function keyframe(Request $request, string $uuid, int $keyframeId): JsonResponse
    {
        $analysis = $this->findAnalysisOrFail($uuid, $request->user()->id);

        $keyframe = $analysis->keyframes()->findOrFail($keyframeId);

        return response()->json([
            'data' => $this->formatKeyframe($keyframe, true),
        ]);
    }

    /**
     * Mark keyframe as representative.
     */
    public function markRepresentative(Request $request, string $uuid, int $keyframeId): JsonResponse
    {
        $analysis = $this->findAnalysisOrFail($uuid, $request->user()->id);

        $keyframe = $analysis->keyframes()->findOrFail($keyframeId);
        $keyframe->markAsRepresentative();

        return response()->json([
            'message' => 'Keyframe marked as representative',
            'data' => $this->formatKeyframe($keyframe->fresh()),
        ]);
    }

    /**
     * Analyze keyframe for objects/faces/OCR.
     */
    public function analyzeKeyframe(Request $request, string $uuid, int $keyframeId): JsonResponse
    {
        $analysis = $this->findAnalysisOrFail($uuid, $request->user()->id);

        $keyframe = $analysis->keyframes()->findOrFail($keyframeId);

        $keyframe = $this->videoAnalysisService->analyzeFrame($keyframe);

        return response()->json([
            'message' => 'Keyframe analyzed',
            'data' => $this->formatKeyframe($keyframe, true),
        ]);
    }

    /**
     * Delete video analysis.
     */
    public function destroy(Request $request, string $uuid): JsonResponse
    {
        $analysis = $this->findAnalysisOrFail($uuid, $request->user()->id);

        if ($analysis->isProcessing()) {
            return response()->json([
                'message' => 'Cannot delete video while analysis is in progress',
            ], 409);
        }

        $deleted = $this->videoAnalysisService->deleteVideo($analysis);

        if (!$deleted) {
            return response()->json([
                'message' => 'Failed to delete video',
            ], 500);
        }

        return response()->json([
            'message' => 'Video deleted successfully',
        ]);
    }

    /**
     * Get video analysis statistics.
     */
    public function stats(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $stats = [
            'total' => VideoAnalysis::createdBy($userId)->count(),
            'pending' => VideoAnalysis::createdBy($userId)->pending()->count(),
            'processing' => VideoAnalysis::createdBy($userId)->processing()->count(),
            'completed' => VideoAnalysis::createdBy($userId)->completed()->count(),
            'failed' => VideoAnalysis::createdBy($userId)->failed()->count(),
            'with_gps' => VideoAnalysis::createdBy($userId)->withGps()->count(),
            'total_keyframes' => VideoKeyframe::whereHas('videoAnalysis', fn($q) => $q->where('created_by', $userId))->count(),
            'total_storage_bytes' => VideoAnalysis::createdBy($userId)->sum('file_size'),
        ];

        return response()->json([
            'data' => $stats,
        ]);
    }

    /**
     * Get supported video formats.
     */
    public function formats(): JsonResponse
    {
        return response()->json([
            'data' => [
                'mime_types' => VideoAnalysis::getSupportedMimeTypes(),
                'extensions' => VideoAnalysis::getSupportedExtensions(),
                'max_size_mb' => 500,
            ],
        ]);
    }

    /**
     * Link video to event.
     */
    public function linkToEvent(Request $request, string $uuid): JsonResponse
    {
        $analysis = $this->findAnalysisOrFail($uuid, $request->user()->id);

        $request->validate([
            'event_id' => 'required|exists:events,id',
        ]);

        $analysis->update(['event_id' => $request->event_id]);

        return response()->json([
            'message' => 'Video linked to event',
            'data' => $this->formatVideoAnalysis($analysis->fresh()),
        ]);
    }

    /**
     * Unlink video from event.
     */
    public function unlinkFromEvent(Request $request, string $uuid): JsonResponse
    {
        $analysis = $this->findAnalysisOrFail($uuid, $request->user()->id);

        $analysis->update(['event_id' => null]);

        return response()->json([
            'message' => 'Video unlinked from event',
            'data' => $this->formatVideoAnalysis($analysis->fresh()),
        ]);
    }

    /**
     * Find analysis by UUID or fail.
     */
    protected function findAnalysisOrFail(string $uuid, int $userId): VideoAnalysis
    {
        return VideoAnalysis::where('uuid', $uuid)
            ->where('created_by', $userId)
            ->firstOrFail();
    }

    /**
     * Format video analysis for response.
     */
    protected function formatVideoAnalysis(VideoAnalysis $analysis, bool $detailed = false): array
    {
        $data = [
            'uuid' => $analysis->uuid,
            'original_filename' => $analysis->original_filename,
            'file_size' => $analysis->file_size,
            'file_size_formatted' => $analysis->getFormattedFileSize(),
            'duration_seconds' => $analysis->duration_seconds,
            'duration_formatted' => $analysis->getFormattedDuration(),
            'resolution' => $analysis->getResolutionString(),
            'aspect_ratio' => $analysis->getAspectRatio(),
            'fps' => $analysis->fps,
            'codec' => $analysis->codec,
            'container_format' => $analysis->container_format,
            'has_gps' => $analysis->hasGpsCoordinates(),
            'gps' => $analysis->getGpsLocation(),
            'recording_datetime' => $analysis->recording_datetime,
            'device' => $analysis->getDeviceSummary(),
            'analysis_status' => $analysis->analysis_status,
            'error_message' => $analysis->error_message,
            'video_url' => $analysis->getVideoUrl(),
            'thumbnail_url' => $analysis->getThumbnailUrl(),
            'keyframe_count' => $analysis->keyframes()->count(),
            'scene_change_count' => $analysis->sceneChanges()->count(),
            'event_id' => $analysis->event_id,
            'created_at' => $analysis->created_at,
            'updated_at' => $analysis->updated_at,
        ];

        if ($analysis->relationLoaded('creator')) {
            $data['creator'] = [
                'uuid' => $analysis->creator->uuid ?? null,
                'name' => $analysis->creator->name ?? null,
            ];
        }

        if ($analysis->relationLoaded('event') && $analysis->event) {
            $data['event'] = [
                'uuid' => $analysis->event->uuid,
                'title' => $analysis->event->title,
            ];
        }

        if ($detailed) {
            $data['frame_count'] = $analysis->frame_count;
            $data['resolution_width'] = $analysis->resolution_width;
            $data['resolution_height'] = $analysis->resolution_height;
            $data['device_info'] = $analysis->device_info;
            $data['exif_data'] = $analysis->exif_data;
            $data['timezone'] = $analysis->timezone;
            $data['analysis_started_at'] = $analysis->analysis_started_at;
            $data['analysis_completed_at'] = $analysis->analysis_completed_at;
            $data['retry_count'] = $analysis->retry_count;

            // Include representative frames
            $data['representative_frames'] = $analysis->representativeFrames()
                ->get()
                ->map(fn($k) => $this->formatKeyframe($k));
        }

        return $data;
    }

    /**
     * Format keyframe for response.
     */
    protected function formatKeyframe(VideoKeyframe $keyframe, bool $detailed = false): array
    {
        $data = [
            'id' => $keyframe->id,
            'frame_number' => $keyframe->frame_number,
            'timestamp_seconds' => $keyframe->timestamp_seconds,
            'timestamp_formatted' => $keyframe->getFormattedTimestamp(),
            'image_url' => $keyframe->getImageUrl(),
            'thumbnail_url' => $keyframe->getThumbnailUrl(),
            'is_scene_change' => $keyframe->is_scene_change,
            'scene_change_score' => $keyframe->scene_change_score,
            'is_representative' => $keyframe->is_representative,
            'has_objects' => $keyframe->hasDetectedObjects(),
            'object_count' => $keyframe->getObjectCount(),
            'has_faces' => $keyframe->hasDetectedFaces(),
            'face_count' => $keyframe->getFaceCount(),
            'has_ocr' => $keyframe->hasOcrText(),
            'ocr_preview' => $keyframe->getOcrPreview(50),
        ];

        if ($detailed) {
            $data['objects_detected'] = $keyframe->objects_detected;
            $data['object_labels'] = $keyframe->getObjectLabels();
            $data['faces_detected'] = $keyframe->faces_detected;
            $data['ocr_text'] = $keyframe->ocr_text;
            $data['ocr_regions'] = $keyframe->ocr_regions;
            $data['dominant_colors'] = $keyframe->dominant_colors;
            $data['primary_color'] = $keyframe->getPrimaryColor();
            $data['brightness'] = $keyframe->brightness;
            $data['contrast'] = $keyframe->contrast;
            $data['blur_score'] = $keyframe->blur_score;
            $data['is_blurry'] = $keyframe->isBlurry();
            $data['is_dark'] = $keyframe->isDark();
            $data['frame_type'] = $keyframe->frame_type;
            $data['metadata'] = $keyframe->metadata;
            $data['created_at'] = $keyframe->created_at;
        }

        return $data;
    }
}
