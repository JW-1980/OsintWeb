<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AudioCollection;
use App\Models\AudioFile;
use App\Models\Transcription;
use App\Models\TranscriptionJob;
use App\Services\TranscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AudioController extends Controller
{
    public function __construct(
        protected TranscriptionService $transcriptionService
    ) {}

    // ===== AUDIO FILES =====

    /**
     * List audio files.
     */
    public function index(Request $request): JsonResponse
    {
        $query = AudioFile::with(['user:id,name', 'primaryTranscription:id,audio_file_id,status,type'])
            ->where(function ($q) use ($request) {
                // Show user's own files
                if ($request->user()) {
                    $q->where('user_id', $request->user()->id);
                }
                // Show public files
                $q->orWhere('visibility', AudioFile::VISIBILITY_PUBLIC);
            })
            ->where('status', AudioFile::STATUS_READY);

        // Filters
        if ($request->has('visibility')) {
            $query->where('visibility', $request->visibility);
        }

        if ($request->has('language')) {
            $query->where('language', $request->language);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $audio = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'data' => $audio->map(fn ($a) => $this->formatAudioFile($a)),
            'meta' => [
                'current_page' => $audio->currentPage(),
                'last_page' => $audio->lastPage(),
                'total' => $audio->total(),
            ],
        ]);
    }

    /**
     * Upload a new audio file.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'audio' => 'required|file|mimetypes:' . implode(',', AudioFile::getSupportedMimeTypes()) . '|max:102400', // 100MB
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'language' => 'nullable|string|max:10',
            'visibility' => 'nullable|in:public,private,unlisted',
            'allow_download' => 'nullable|boolean',
        ]);

        $file = $request->file('audio');
        $uuid = Str::uuid()->toString();

        // Store file
        $path = $file->store("audio/{$uuid}", 'public');

        // Get audio metadata (duration would require ffprobe/getID3 library)
        $audioFile = AudioFile::create([
            'uuid' => $uuid,
            'user_id' => $request->user()->id,
            'title' => $request->title,
            'description' => $request->description,
            'file_path' => $path,
            'file_name' => basename($path),
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'language' => $request->language ?? 'en',
            'status' => AudioFile::STATUS_READY,
            'visibility' => $request->visibility ?? AudioFile::VISIBILITY_PRIVATE,
            'allow_download' => $request->boolean('allow_download', false),
        ]);

        return response()->json([
            'message' => 'Audio uploaded successfully',
            'data' => $this->formatAudioFile($audioFile),
        ], 201);
    }

    /**
     * Get audio file details.
     */
    public function show(Request $request, string $uuid): JsonResponse
    {
        $audio = AudioFile::where('uuid', $uuid)
            ->with(['user:id,name,uuid', 'transcriptions' => function ($q) {
                $q->select('id', 'uuid', 'audio_file_id', 'type', 'status', 'is_primary', 'language', 'word_count', 'created_at');
            }])
            ->firstOrFail();

        if (!$audio->canAccess($request->user())) {
            return response()->json(['message' => 'Access denied'], 403);
        }

        return response()->json([
            'data' => $this->formatAudioFile($audio, true),
        ]);
    }

    /**
     * Update audio file.
     */
    public function update(Request $request, string $uuid): JsonResponse
    {
        $audio = AudioFile::where('uuid', $uuid)->firstOrFail();

        if ($audio->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:2000',
            'language' => 'nullable|string|max:10',
            'visibility' => 'nullable|in:public,private,unlisted',
            'allow_download' => 'nullable|boolean',
        ]);

        $audio->update($validated);

        return response()->json([
            'message' => 'Audio updated successfully',
            'data' => $this->formatAudioFile($audio->fresh()),
        ]);
    }

    /**
     * Delete audio file.
     */
    public function destroy(Request $request, string $uuid): JsonResponse
    {
        $audio = AudioFile::where('uuid', $uuid)->firstOrFail();

        if ($audio->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Access denied'], 403);
        }

        // Delete file from storage
        if (Storage::disk('public')->exists($audio->file_path)) {
            Storage::disk('public')->delete($audio->file_path);
        }

        $audio->delete();

        return response()->json([
            'message' => 'Audio deleted successfully',
        ]);
    }

    /**
     * Record audio play.
     */
    public function recordPlay(Request $request, string $uuid): JsonResponse
    {
        $audio = AudioFile::where('uuid', $uuid)->firstOrFail();

        if (!$audio->canAccess($request->user())) {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $audio->recordPlay();

        return response()->json([
            'message' => 'Play recorded',
            'play_count' => $audio->play_count,
        ]);
    }

    // ===== TRANSCRIPTIONS =====

    /**
     * Get transcriptions for an audio file.
     */
    public function transcriptions(Request $request, string $uuid): JsonResponse
    {
        $audio = AudioFile::where('uuid', $uuid)->firstOrFail();

        if (!$audio->canAccess($request->user())) {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $transcriptions = $audio->transcriptions()
            ->with('user:id,name')
            ->orderBy('is_primary', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => $transcriptions->map(fn ($t) => $this->formatTranscription($t)),
        ]);
    }

    /**
     * Create manual transcription.
     */
    public function createManualTranscription(Request $request, string $uuid): JsonResponse
    {
        $audio = AudioFile::where('uuid', $uuid)->firstOrFail();

        if ($audio->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'language' => 'nullable|string|max:10',
        ]);

        $transcription = $this->transcriptionService->createManualTranscription(
            $audio,
            $request->user(),
            $validated['title'] ?? null,
            $validated['language'] ?? 'en'
        );

        return response()->json([
            'message' => 'Transcription created',
            'data' => $this->formatTranscription($transcription),
        ], 201);
    }

    /**
     * Request AI transcription.
     */
    public function requestAiTranscription(Request $request, string $uuid): JsonResponse
    {
        $audio = AudioFile::where('uuid', $uuid)->firstOrFail();

        if ($audio->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $validated = $request->validate([
            'language' => 'nullable|string|max:10',
            'model' => 'nullable|string|max:100',
        ]);

        // Check for existing pending job
        $existingJob = $audio->transcriptionJobs()
            ->whereIn('status', [TranscriptionJob::STATUS_QUEUED, TranscriptionJob::STATUS_PROCESSING])
            ->first();

        if ($existingJob) {
            return response()->json([
                'message' => 'A transcription job is already in progress',
                'data' => [
                    'job_uuid' => $existingJob->uuid,
                    'status' => $existingJob->status,
                    'progress' => $existingJob->progress,
                ],
            ], 409);
        }

        $job = $this->transcriptionService->queueAiTranscription(
            $audio,
            $request->user(),
            $validated['language'] ?? 'en',
            $validated['model'] ?? null
        );

        return response()->json([
            'message' => 'Transcription job queued',
            'data' => [
                'job_uuid' => $job->uuid,
                'status' => $job->status,
            ],
        ], 202);
    }

    /**
     * Get transcription job status.
     */
    public function transcriptionJobStatus(string $uuid, string $jobUuid): JsonResponse
    {
        $job = TranscriptionJob::where('uuid', $jobUuid)
            ->whereHas('audioFile', fn ($q) => $q->where('uuid', $uuid))
            ->firstOrFail();

        return response()->json([
            'data' => [
                'uuid' => $job->uuid,
                'status' => $job->status,
                'progress' => $job->progress,
                'model' => $job->model,
                'error_message' => $job->error_message,
                'started_at' => $job->started_at,
                'completed_at' => $job->completed_at,
                'transcription_uuid' => $job->transcription?->uuid,
            ],
        ]);
    }

    /**
     * Get available AI models.
     */
    public function aiModels(): JsonResponse
    {
        return response()->json([
            'data' => $this->transcriptionService->getAvailableModels(),
        ]);
    }

    // ===== TRANSCRIPT CONTENT =====

    /**
     * Get transcription with segments.
     */
    public function getTranscription(Request $request, string $uuid, string $transcriptionUuid): JsonResponse
    {
        $audio = AudioFile::where('uuid', $uuid)->firstOrFail();

        if (!$audio->canAccess($request->user())) {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $transcription = Transcription::where('uuid', $transcriptionUuid)
            ->where('audio_file_id', $audio->id)
            ->with(['segments', 'user:id,name'])
            ->firstOrFail();

        return response()->json([
            'data' => $this->formatTranscription($transcription, true),
        ]);
    }

    /**
     * Update transcription full text.
     */
    public function updateTranscription(Request $request, string $uuid, string $transcriptionUuid): JsonResponse
    {
        $audio = AudioFile::where('uuid', $uuid)->firstOrFail();

        $transcription = Transcription::where('uuid', $transcriptionUuid)
            ->where('audio_file_id', $audio->id)
            ->firstOrFail();

        if ($transcription->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'full_text' => 'sometimes|string',
            'status' => 'sometimes|in:draft,in_progress,completed',
        ]);

        // Create revision before updating
        if (isset($validated['full_text']) && $transcription->full_text !== $validated['full_text']) {
            $transcription->createRevision($request->user(), 'Manual edit');
        }

        $transcription->update($validated);

        if (isset($validated['full_text'])) {
            $transcription->word_count = str_word_count($validated['full_text']);
            $transcription->save();
        }

        return response()->json([
            'message' => 'Transcription updated',
            'data' => $this->formatTranscription($transcription->fresh()),
        ]);
    }

    /**
     * Add segment to transcription.
     */
    public function addSegment(Request $request, string $uuid, string $transcriptionUuid): JsonResponse
    {
        $audio = AudioFile::where('uuid', $uuid)->firstOrFail();

        $transcription = Transcription::where('uuid', $transcriptionUuid)
            ->where('audio_file_id', $audio->id)
            ->firstOrFail();

        if ($transcription->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $validated = $request->validate([
            'start_time' => 'required|numeric|min:0',
            'end_time' => 'required|numeric|gt:start_time',
            'text' => 'required|string|max:5000',
            'speaker_id' => 'nullable|string|max:50',
            'speaker_name' => 'nullable|string|max:100',
        ]);

        $segment = $this->transcriptionService->addSegment(
            $transcription,
            $validated['start_time'],
            $validated['end_time'],
            $validated['text'],
            $validated['speaker_id'] ?? null,
            $validated['speaker_name'] ?? null
        );

        return response()->json([
            'message' => 'Segment added',
            'data' => $segment,
        ], 201);
    }

    /**
     * Update segment.
     */
    public function updateSegment(Request $request, string $uuid, string $transcriptionUuid, int $segmentId): JsonResponse
    {
        $audio = AudioFile::where('uuid', $uuid)->firstOrFail();

        $transcription = Transcription::where('uuid', $transcriptionUuid)
            ->where('audio_file_id', $audio->id)
            ->firstOrFail();

        if ($transcription->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $segment = $transcription->segments()->findOrFail($segmentId);

        $validated = $request->validate([
            'start_time' => 'sometimes|numeric|min:0',
            'end_time' => 'sometimes|numeric',
            'text' => 'sometimes|string|max:5000',
            'speaker_id' => 'nullable|string|max:50',
            'speaker_name' => 'nullable|string|max:100',
        ]);

        $segment = $this->transcriptionService->updateSegment($segment, $validated);

        return response()->json([
            'message' => 'Segment updated',
            'data' => $segment,
        ]);
    }

    /**
     * Delete segment.
     */
    public function deleteSegment(Request $request, string $uuid, string $transcriptionUuid, int $segmentId): JsonResponse
    {
        $audio = AudioFile::where('uuid', $uuid)->firstOrFail();

        $transcription = Transcription::where('uuid', $transcriptionUuid)
            ->where('audio_file_id', $audio->id)
            ->firstOrFail();

        if ($transcription->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $segment = $transcription->segments()->findOrFail($segmentId);
        $this->transcriptionService->deleteSegment($segment);

        return response()->json(['message' => 'Segment deleted']);
    }

    /**
     * Set transcription as primary.
     */
    public function setPrimary(Request $request, string $uuid, string $transcriptionUuid): JsonResponse
    {
        $audio = AudioFile::where('uuid', $uuid)->firstOrFail();

        if ($audio->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $transcription = Transcription::where('uuid', $transcriptionUuid)
            ->where('audio_file_id', $audio->id)
            ->firstOrFail();

        $transcription->setAsPrimary();

        return response()->json(['message' => 'Transcription set as primary']);
    }

    /**
     * Export transcription.
     */
    public function exportTranscription(Request $request, string $uuid, string $transcriptionUuid): JsonResponse
    {
        $audio = AudioFile::where('uuid', $uuid)->firstOrFail();

        if (!$audio->canAccess($request->user())) {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $transcription = Transcription::where('uuid', $transcriptionUuid)
            ->where('audio_file_id', $audio->id)
            ->with('segments')
            ->firstOrFail();

        $format = $request->get('format', 'txt');

        $content = match ($format) {
            'vtt' => $this->transcriptionService->exportToVtt($transcription),
            'srt' => $this->transcriptionService->exportToSrt($transcription),
            'txt' => $this->transcriptionService->exportToText($transcription, $request->boolean('timestamps')),
            default => $transcription->full_text,
        };

        $mimeType = match ($format) {
            'vtt' => 'text/vtt',
            'srt' => 'text/srt',
            default => 'text/plain',
        };

        return response()->json([
            'data' => [
                'content' => $content,
                'format' => $format,
                'mime_type' => $mimeType,
                'filename' => Str::slug($transcription->title ?? $audio->title) . ".{$format}",
            ],
        ]);
    }

    /**
     * Get transcript revisions.
     */
    public function revisions(string $uuid, string $transcriptionUuid): JsonResponse
    {
        $audio = AudioFile::where('uuid', $uuid)->firstOrFail();

        $transcription = Transcription::where('uuid', $transcriptionUuid)
            ->where('audio_file_id', $audio->id)
            ->firstOrFail();

        $revisions = $transcription->revisions()
            ->with('user:id,name')
            ->get();

        return response()->json([
            'data' => $revisions->map(fn ($r) => [
                'revision_number' => $r->revision_number,
                'user' => $r->user?->name,
                'word_count' => $r->getWordCount(),
                'comment' => $r->comment,
                'created_at' => $r->created_at,
            ]),
        ]);
    }

    // ===== HELPERS =====

    protected function formatAudioFile(AudioFile $audio, bool $detailed = false): array
    {
        $data = [
            'uuid' => $audio->uuid,
            'title' => $audio->title,
            'description' => $audio->description,
            'duration' => $audio->duration,
            'duration_formatted' => $audio->getFormattedDuration(),
            'file_size' => $audio->file_size,
            'file_size_formatted' => $audio->getFormattedFileSize(),
            'mime_type' => $audio->mime_type,
            'language' => $audio->language,
            'visibility' => $audio->visibility,
            'allow_download' => $audio->allow_download,
            'play_count' => $audio->play_count,
            'status' => $audio->status,
            'file_url' => $audio->getFileUrl(),
            'thumbnail_url' => $audio->getThumbnailUrl(),
            'has_transcription' => $audio->relationLoaded('primaryTranscription') && $audio->primaryTranscription !== null,
            'user' => $audio->relationLoaded('user') ? [
                'uuid' => $audio->user->uuid ?? null,
                'name' => $audio->user->name ?? null,
            ] : null,
            'created_at' => $audio->created_at,
        ];

        if ($detailed) {
            $data['transcriptions'] = $audio->transcriptions->map(fn ($t) => $this->formatTranscription($t));
            $data['metadata'] = $audio->metadata;
            $data['sample_rate'] = $audio->sample_rate;
            $data['channels'] = $audio->channels;
            $data['bitrate'] = $audio->bitrate;
            $data['codec'] = $audio->codec;
        }

        return $data;
    }

    protected function formatTranscription(Transcription $transcription, bool $withSegments = false): array
    {
        $data = [
            'uuid' => $transcription->uuid,
            'title' => $transcription->title,
            'type' => $transcription->type,
            'language' => $transcription->language,
            'status' => $transcription->status,
            'is_primary' => $transcription->is_primary,
            'word_count' => $transcription->word_count,
            'segment_count' => $transcription->segment_count,
            'confidence_score' => $transcription->confidence_score,
            'ai_model' => $transcription->ai_model,
            'user' => $transcription->relationLoaded('user') ? [
                'name' => $transcription->user->name,
            ] : null,
            'created_at' => $transcription->created_at,
            'completed_at' => $transcription->transcription_completed_at,
        ];

        if ($withSegments) {
            $data['full_text'] = $transcription->full_text;
            $data['segments'] = $transcription->segments->map(fn ($s) => [
                'id' => $s->id,
                'index' => $s->segment_index,
                'start_time' => $s->start_time,
                'end_time' => $s->end_time,
                'start_formatted' => $s->getFormattedStartTime(),
                'end_formatted' => $s->getFormattedEndTime(),
                'text' => $s->text,
                'speaker_id' => $s->speaker_id,
                'speaker_name' => $s->speaker_name,
                'confidence' => $s->confidence,
            ]);
            $data['speakers'] = $transcription->getSpeakers();
        }

        return $data;
    }
}
