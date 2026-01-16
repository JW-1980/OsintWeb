<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessAudioAnalysisJob;
use App\Models\AudioAnalysis;
use App\Models\AudioFile;
use App\Services\AudioAnalysisService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * Controller for audio forensic analysis API endpoints.
 *
 * Provides endpoints for analyzing audio files, generating spectrograms
 * and waveforms, detecting edits, and comparing audio segments.
 */
class AudioAnalysisController extends Controller
{
    public function __construct(
        protected AudioAnalysisService $analysisService
    ) {}

    /**
     * List audio analyses for the authenticated user.
     *
     * GET /api/audio-analysis
     *
     * Query Parameters:
     * - audio_file_uuid: Filter by audio file
     * - status: Filter by analysis status
     * - min_manipulation_score: Filter by minimum manipulation score
     * - has_edits: Filter to show only analyses with detected edits
     * - per_page: Items per page (default: 15)
     */
    public function index(Request $request): JsonResponse
    {
        $query = AudioAnalysis::with(['audioFile:id,uuid,title', 'creator:id,name'])
            ->where('created_by', $request->user()->id);

        // Filter by audio file
        if ($request->has('audio_file_uuid')) {
            $audioFile = AudioFile::where('uuid', $request->audio_file_uuid)->first();
            if ($audioFile) {
                $query->where('audio_file_id', $audioFile->id);
            }
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('analysis_status', $request->status);
        }

        // Filter by minimum manipulation score
        if ($request->has('min_manipulation_score')) {
            $query->where('manipulation_score', '>=', (int) $request->min_manipulation_score);
        }

        // Filter to show only analyses with detected edits
        if ($request->boolean('has_edits')) {
            $query->withEdits();
        }

        $analyses = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'data' => $analyses->map(fn ($a) => $this->formatAnalysis($a)),
            'meta' => [
                'current_page' => $analyses->currentPage(),
                'last_page' => $analyses->lastPage(),
                'per_page' => $analyses->perPage(),
                'total' => $analyses->total(),
            ],
        ]);
    }

    /**
     * Trigger analysis on an audio file.
     *
     * POST /api/audio-analysis
     *
     * Request Body:
     * - audio_file_uuid: (required) UUID of the audio file to analyze
     * - event_id: (optional) ID of related event
     */
    public function analyze(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'audio_file_uuid' => 'required|string|exists:audio_files,uuid',
            'event_id' => 'nullable|integer|exists:events,id',
        ]);

        $audioFile = AudioFile::where('uuid', $validated['audio_file_uuid'])->firstOrFail();

        // Check if user has access to this audio file
        if (!$audioFile->canAccess($request->user())) {
            return response()->json([
                'message' => 'You do not have access to this audio file.',
            ], 403);
        }

        // Check for existing pending/processing analysis
        $existingAnalysis = AudioAnalysis::where('audio_file_id', $audioFile->id)
            ->whereIn('analysis_status', [AudioAnalysis::STATUS_PENDING, AudioAnalysis::STATUS_PROCESSING])
            ->first();

        if ($existingAnalysis) {
            return response()->json([
                'message' => 'An analysis is already in progress for this audio file.',
                'data' => $this->formatAnalysis($existingAnalysis),
            ], 409);
        }

        // Create new analysis
        $analysis = $this->analysisService->analyzeAudio(
            $audioFile->id,
            $request->user(),
            $validated['event_id'] ?? null
        );

        // Dispatch background job
        ProcessAudioAnalysisJob::dispatch($analysis);

        return response()->json([
            'message' => 'Audio analysis has been queued.',
            'data' => $this->formatAnalysis($analysis),
        ], 202);
    }

    /**
     * Get analysis results.
     *
     * GET /api/audio-analysis/{uuid}
     */
    public function show(Request $request, string $uuid): JsonResponse
    {
        $analysis = AudioAnalysis::where('uuid', $uuid)
            ->with(['audioFile:id,uuid,title,file_path', 'event:id,uuid,title', 'creator:id,name'])
            ->firstOrFail();

        // Check access
        if ($analysis->created_by !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Access denied.'], 403);
        }

        return response()->json([
            'data' => $this->formatAnalysis($analysis, true),
        ]);
    }

    /**
     * Get or generate spectrogram for an analysis.
     *
     * GET /api/audio-analysis/{uuid}/spectrogram
     *
     * Query Parameters:
     * - regenerate: Force regeneration of spectrogram
     * - width: Custom width (default: 1200)
     * - height: Custom height (default: 400)
     * - dynamic_range: Dynamic range in dB (default: 120)
     */
    public function spectrogram(Request $request, string $uuid): JsonResponse
    {
        $analysis = AudioAnalysis::where('uuid', $uuid)->firstOrFail();

        // Check access
        if ($analysis->created_by !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Access denied.'], 403);
        }

        if (!$analysis->isCompleted() && !$analysis->spectrogram_path) {
            return response()->json([
                'message' => 'Analysis not yet completed. Spectrogram will be available after analysis.',
                'status' => $analysis->analysis_status,
            ], 202);
        }

        // Regenerate if requested or if not available
        if ($request->boolean('regenerate') || !$analysis->spectrogram_path) {
            $request->validate([
                'width' => 'nullable|integer|min:400|max:4000',
                'height' => 'nullable|integer|min:200|max:2000',
                'dynamic_range' => 'nullable|integer|min:60|max:180',
            ]);

            $options = array_filter([
                'width' => $request->input('width'),
                'height' => $request->input('height'),
                'dynamic_range' => $request->input('dynamic_range'),
            ]);

            $audioPath = Storage::disk('public')->path($analysis->audioFile->file_path);
            $spectrogramPath = $this->analysisService->generateSpectrogram(
                $audioPath,
                $options,
                $analysis->uuid
            );

            $analysis->update(['spectrogram_path' => $spectrogramPath]);
        }

        return response()->json([
            'data' => [
                'uuid' => $analysis->uuid,
                'spectrogram_url' => $analysis->getSpectrogramUrl(),
                'generated_at' => $analysis->updated_at,
            ],
        ]);
    }

    /**
     * Get or generate waveform for an analysis.
     *
     * GET /api/audio-analysis/{uuid}/waveform
     *
     * Query Parameters:
     * - regenerate: Force regeneration of waveform
     * - width: Custom width (default: 1200)
     * - height: Custom height (default: 200)
     * - color: Waveform color (default: #3b82f6)
     */
    public function waveform(Request $request, string $uuid): JsonResponse
    {
        $analysis = AudioAnalysis::where('uuid', $uuid)->firstOrFail();

        // Check access
        if ($analysis->created_by !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Access denied.'], 403);
        }

        if (!$analysis->isCompleted() && !$analysis->waveform_path) {
            return response()->json([
                'message' => 'Analysis not yet completed. Waveform will be available after analysis.',
                'status' => $analysis->analysis_status,
            ], 202);
        }

        // Regenerate if requested or if not available
        if ($request->boolean('regenerate') || !$analysis->waveform_path) {
            $request->validate([
                'width' => 'nullable|integer|min:400|max:4000',
                'height' => 'nullable|integer|min:100|max:1000',
                'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            ]);

            $options = array_filter([
                'width' => $request->input('width'),
                'height' => $request->input('height'),
                'color' => $request->input('color'),
            ]);

            $audioPath = Storage::disk('public')->path($analysis->audioFile->file_path);
            $waveformPath = $this->analysisService->generateWaveform(
                $audioPath,
                $options,
                $analysis->uuid
            );

            $analysis->update(['waveform_path' => $waveformPath]);
        }

        return response()->json([
            'data' => [
                'uuid' => $analysis->uuid,
                'waveform_url' => $analysis->getWaveformUrl(),
                'generated_at' => $analysis->updated_at,
            ],
        ]);
    }

    /**
     * Get detected edit points from an analysis.
     *
     * GET /api/audio-analysis/{uuid}/edits
     */
    public function edits(Request $request, string $uuid): JsonResponse
    {
        $analysis = AudioAnalysis::where('uuid', $uuid)->firstOrFail();

        // Check access
        if ($analysis->created_by !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Access denied.'], 403);
        }

        if (!$analysis->isCompleted()) {
            return response()->json([
                'message' => 'Analysis not yet completed.',
                'status' => $analysis->analysis_status,
            ], 202);
        }

        $edits = $analysis->detected_edits ?? [];
        $highConfidenceEdits = $analysis->getHighConfidenceEdits();

        return response()->json([
            'data' => [
                'uuid' => $analysis->uuid,
                'total_edits' => count($edits),
                'high_confidence_count' => count($highConfidenceEdits),
                'manipulation_score' => $analysis->manipulation_score,
                'manipulation_risk' => $analysis->getManipulationRisk(),
                'edits' => array_map(fn ($edit) => [
                    'timestamp' => $edit['timestamp'] ?? 0,
                    'timestamp_formatted' => $this->formatTimestamp($edit['timestamp'] ?? 0),
                    'type' => $edit['type'] ?? 'unknown',
                    'confidence' => $edit['confidence'] ?? 0,
                    'confidence_level' => ($edit['confidence'] ?? 0) >= 0.7 ? 'high' : (($edit['confidence'] ?? 0) >= 0.4 ? 'medium' : 'low'),
                    'description' => $edit['description'] ?? null,
                ], $edits),
            ],
        ]);
    }

    /**
     * Compare two audio files.
     *
     * POST /api/audio-analysis/compare
     *
     * Request Body:
     * - audio_file_uuid_1: (required) First audio file UUID
     * - audio_file_uuid_2: (required) Second audio file UUID
     */
    public function compare(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'audio_file_uuid_1' => 'required|string|exists:audio_files,uuid',
            'audio_file_uuid_2' => 'required|string|exists:audio_files,uuid|different:audio_file_uuid_1',
        ]);

        $audioFile1 = AudioFile::where('uuid', $validated['audio_file_uuid_1'])->firstOrFail();
        $audioFile2 = AudioFile::where('uuid', $validated['audio_file_uuid_2'])->firstOrFail();

        // Check access to both files
        if (!$audioFile1->canAccess($request->user()) || !$audioFile2->canAccess($request->user())) {
            return response()->json([
                'message' => 'You do not have access to one or both audio files.',
            ], 403);
        }

        $path1 = Storage::disk('public')->path($audioFile1->file_path);
        $path2 = Storage::disk('public')->path($audioFile2->file_path);

        $comparison = $this->analysisService->compareAudioSegments($path1, $path2);

        return response()->json([
            'data' => [
                'audio_file_1' => [
                    'uuid' => $audioFile1->uuid,
                    'title' => $audioFile1->title,
                    'duration' => $audioFile1->duration,
                ],
                'audio_file_2' => [
                    'uuid' => $audioFile2->uuid,
                    'title' => $audioFile2->title,
                    'duration' => $audioFile2->duration,
                ],
                'comparison' => [
                    'similarity_score' => round($comparison['similarity_score'] * 100, 1),
                    'is_similar' => $comparison['is_similar'],
                    'is_likely_same_source' => $comparison['is_likely_same_source'],
                    'method' => $comparison['method'],
                    'interpretation' => $this->interpretSimilarity($comparison['similarity_score']),
                ],
            ],
        ]);
    }

    /**
     * Get voice activity regions from an analysis.
     *
     * GET /api/audio-analysis/{uuid}/voice-activity
     */
    public function voiceActivity(Request $request, string $uuid): JsonResponse
    {
        $analysis = AudioAnalysis::where('uuid', $uuid)->firstOrFail();

        // Check access
        if ($analysis->created_by !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Access denied.'], 403);
        }

        if (!$analysis->isCompleted()) {
            return response()->json([
                'message' => 'Analysis not yet completed.',
                'status' => $analysis->analysis_status,
            ], 202);
        }

        $regions = $analysis->getVoiceActivityRegions();

        return response()->json([
            'data' => [
                'uuid' => $analysis->uuid,
                'total_regions' => count($regions),
                'total_voice_duration' => round($analysis->getTotalVoiceActivityDuration(), 2),
                'voice_percentage' => round($analysis->getVoiceActivityPercentage(), 1),
                'audio_duration' => $analysis->duration_seconds,
                'regions' => array_map(fn ($region) => [
                    'start' => $region['start'],
                    'end' => $region['end'],
                    'start_formatted' => $this->formatTimestamp($region['start']),
                    'end_formatted' => $this->formatTimestamp($region['end']),
                    'duration' => round($region['duration'], 2),
                    'speaker_id' => $region['speaker_id'],
                    'confidence' => $region['confidence'],
                ], $regions),
            ],
        ]);
    }

    /**
     * Get silence regions from an analysis.
     *
     * GET /api/audio-analysis/{uuid}/silence
     */
    public function silence(Request $request, string $uuid): JsonResponse
    {
        $analysis = AudioAnalysis::where('uuid', $uuid)->firstOrFail();

        // Check access
        if ($analysis->created_by !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Access denied.'], 403);
        }

        if (!$analysis->isCompleted()) {
            return response()->json([
                'message' => 'Analysis not yet completed.',
                'status' => $analysis->analysis_status,
            ], 202);
        }

        $regions = $analysis->getSilenceRegions();

        return response()->json([
            'data' => [
                'uuid' => $analysis->uuid,
                'total_regions' => count($regions),
                'total_silence_duration' => round($analysis->getTotalSilenceDuration(), 2),
                'silence_percentage' => round($analysis->getSilencePercentage(), 1),
                'has_suspicious_gaps' => $analysis->hasSuspiciousSilenceGaps(),
                'audio_duration' => $analysis->duration_seconds,
                'regions' => array_map(fn ($region) => [
                    'start' => $region['start'],
                    'end' => $region['end'],
                    'start_formatted' => $this->formatTimestamp($region['start']),
                    'end_formatted' => $this->formatTimestamp($region['end']),
                    'duration' => round($region['duration'], 2),
                ], $regions),
            ],
        ]);
    }

    /**
     * Get frequency analysis from an analysis.
     *
     * GET /api/audio-analysis/{uuid}/frequencies
     */
    public function frequencies(Request $request, string $uuid): JsonResponse
    {
        $analysis = AudioAnalysis::where('uuid', $uuid)->firstOrFail();

        // Check access
        if ($analysis->created_by !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Access denied.'], 403);
        }

        if (!$analysis->isCompleted()) {
            return response()->json([
                'message' => 'Analysis not yet completed.',
                'status' => $analysis->analysis_status,
            ], 202);
        }

        $freqAnalysis = $analysis->frequency_analysis ?? [];

        return response()->json([
            'data' => [
                'uuid' => $analysis->uuid,
                'dominant_frequencies' => $analysis->getDominantFrequencies(),
                'has_voice_frequencies' => $analysis->hasVoiceFrequencies(),
                'spectrum_data' => $freqAnalysis['spectrum_data'] ?? [],
                'harmonics' => $freqAnalysis['harmonics'] ?? [],
                'bandwidth' => $freqAnalysis['bandwidth'] ?? null,
            ],
        ]);
    }

    /**
     * Delete an analysis.
     *
     * DELETE /api/audio-analysis/{uuid}
     */
    public function destroy(Request $request, string $uuid): JsonResponse
    {
        $analysis = AudioAnalysis::where('uuid', $uuid)->firstOrFail();

        // Check access
        if ($analysis->created_by !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Access denied.'], 403);
        }

        // Delete associated files
        if ($analysis->spectrogram_path) {
            Storage::disk('public')->delete($analysis->spectrogram_path);
        }
        if ($analysis->waveform_path) {
            Storage::disk('public')->delete($analysis->waveform_path);
        }

        $analysis->delete();

        return response()->json([
            'message' => 'Audio analysis deleted successfully.',
        ]);
    }

    /**
     * Retry a failed analysis.
     *
     * POST /api/audio-analysis/{uuid}/retry
     */
    public function retry(Request $request, string $uuid): JsonResponse
    {
        $analysis = AudioAnalysis::where('uuid', $uuid)->firstOrFail();

        // Check access
        if ($analysis->created_by !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Access denied.'], 403);
        }

        if (!$analysis->hasFailed()) {
            return response()->json([
                'message' => 'Only failed analyses can be retried.',
                'status' => $analysis->analysis_status,
            ], 400);
        }

        // Reset status and dispatch job
        $analysis->update([
            'analysis_status' => AudioAnalysis::STATUS_PENDING,
            'error_message' => null,
            'started_at' => null,
            'completed_at' => null,
        ]);

        ProcessAudioAnalysisJob::dispatch($analysis);

        return response()->json([
            'message' => 'Analysis has been requeued.',
            'data' => $this->formatAnalysis($analysis->fresh()),
        ], 202);
    }

    /**
     * Get analysis statistics for the user.
     *
     * GET /api/audio-analysis/stats
     */
    public function stats(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $stats = [
            'total_analyses' => AudioAnalysis::where('created_by', $userId)->count(),
            'completed' => AudioAnalysis::where('created_by', $userId)->completed()->count(),
            'pending' => AudioAnalysis::where('created_by', $userId)->pending()->count(),
            'processing' => AudioAnalysis::where('created_by', $userId)->processing()->count(),
            'failed' => AudioAnalysis::where('created_by', $userId)->failed()->count(),
            'with_edits' => AudioAnalysis::where('created_by', $userId)->withEdits()->count(),
            'high_risk' => AudioAnalysis::where('created_by', $userId)->highRisk()->count(),
            'average_manipulation_score' => round(
                AudioAnalysis::where('created_by', $userId)
                    ->whereNotNull('manipulation_score')
                    ->avg('manipulation_score') ?? 0,
                1
            ),
        ];

        return response()->json(['data' => $stats]);
    }

    // =========================================================================
    // HELPER METHODS
    // =========================================================================

    /**
     * Format an analysis for API response.
     */
    protected function formatAnalysis(AudioAnalysis $analysis, bool $detailed = false): array
    {
        $data = [
            'uuid' => $analysis->uuid,
            'audio_file' => $analysis->relationLoaded('audioFile') && $analysis->audioFile ? [
                'uuid' => $analysis->audioFile->uuid,
                'title' => $analysis->audioFile->title,
            ] : null,
            'event' => $analysis->relationLoaded('event') && $analysis->event ? [
                'uuid' => $analysis->event->uuid,
                'title' => $analysis->event->title,
            ] : null,
            'status' => $analysis->analysis_status,
            'manipulation_score' => $analysis->manipulation_score,
            'manipulation_risk' => $analysis->manipulation_score !== null ? $analysis->getManipulationRisk() : null,
            'has_detected_edits' => $analysis->hasDetectedEdits(),
            'edit_count' => $analysis->getEditCount(),
            'duration_formatted' => $analysis->getFormattedDuration(),
            'has_visualizations' => $analysis->hasVisualizations(),
            'created_at' => $analysis->created_at,
            'completed_at' => $analysis->completed_at,
        ];

        if ($detailed) {
            $data['technical_info'] = [
                'duration_seconds' => $analysis->duration_seconds,
                'sample_rate' => $analysis->sample_rate,
                'channels' => $analysis->channels,
                'channels_formatted' => $analysis->getFormattedChannels(),
                'bit_depth' => $analysis->bit_depth,
                'codec' => $analysis->codec,
                'container_format' => $analysis->container_format,
            ];

            $data['amplitude'] = [
                'average' => $analysis->average_amplitude,
                'peak' => $analysis->peak_amplitude,
                'noise_floor_db' => $analysis->noise_floor_db,
            ];

            $data['visualizations'] = [
                'spectrogram_url' => $analysis->getSpectrogramUrl(),
                'waveform_url' => $analysis->getWaveformUrl(),
            ];

            $data['voice_activity'] = [
                'total_duration' => round($analysis->getTotalVoiceActivityDuration(), 2),
                'percentage' => round($analysis->getVoiceActivityPercentage(), 1),
                'region_count' => count($analysis->voice_activity_regions ?? []),
            ];

            $data['silence'] = [
                'total_duration' => round($analysis->getTotalSilenceDuration(), 2),
                'percentage' => round($analysis->getSilencePercentage(), 1),
                'region_count' => count($analysis->silence_regions ?? []),
                'has_suspicious_gaps' => $analysis->hasSuspiciousSilenceGaps(),
            ];

            $data['analysis_notes'] = $analysis->analysis_notes;
            $data['error_message'] = $analysis->error_message;
            $data['processing_metadata'] = $analysis->processing_metadata;

            $data['creator'] = $analysis->relationLoaded('creator') && $analysis->creator ? [
                'name' => $analysis->creator->name,
            ] : null;
        }

        return $data;
    }

    /**
     * Format a timestamp in seconds to MM:SS or HH:MM:SS format.
     */
    protected function formatTimestamp(float $seconds): string
    {
        $totalSeconds = (int) $seconds;
        $hours = floor($totalSeconds / 3600);
        $minutes = floor(($totalSeconds % 3600) / 60);
        $secs = $totalSeconds % 60;
        $ms = round(($seconds - $totalSeconds) * 1000);

        if ($hours > 0) {
            return sprintf('%d:%02d:%02d.%03d', $hours, $minutes, $secs, $ms);
        }

        return sprintf('%d:%02d.%03d', $minutes, $secs, $ms);
    }

    /**
     * Interpret similarity score for user-friendly message.
     */
    protected function interpretSimilarity(float $score): string
    {
        if ($score >= 0.95) {
            return 'These audio files appear to be from the same source or are nearly identical.';
        }

        if ($score >= 0.8) {
            return 'These audio files are highly similar and may share the same source.';
        }

        if ($score >= 0.6) {
            return 'These audio files show moderate similarity. They may be related.';
        }

        if ($score >= 0.4) {
            return 'These audio files show low similarity. They are likely from different sources.';
        }

        return 'These audio files appear to be completely different recordings.';
    }
}
