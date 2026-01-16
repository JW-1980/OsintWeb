<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\AudioAnalysis;
use App\Services\AudioAnalysisService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Background job for processing audio forensic analysis.
 *
 * This job handles the potentially long-running audio analysis tasks including:
 * - Metadata extraction
 * - Spectrogram generation
 * - Waveform visualization
 * - Silence/voice activity detection
 * - Edit detection
 * - Manipulation scoring
 */
class ProcessAudioAnalysisJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public int $tries = 3;

    /**
     * The maximum number of seconds the job should run.
     * Audio analysis can take time, especially for long files.
     *
     * @var int
     */
    public int $timeout = 600; // 10 minutes

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var array<int>
     */
    public array $backoff = [60, 180, 300];

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected AudioAnalysis $analysis
    ) {}

    /**
     * Execute the job.
     */
    public function handle(AudioAnalysisService $analysisService): void
    {
        $analysisId = $this->analysis->id;
        $audioFileId = $this->analysis->audio_file_id;

        Log::info('Starting audio analysis job', [
            'analysis_id' => $analysisId,
            'audio_file_id' => $audioFileId,
            'attempt' => $this->attempts(),
        ]);

        try {
            // Perform the full analysis
            $result = $analysisService->performFullAnalysis($this->analysis);

            Log::info('Audio analysis job completed successfully', [
                'analysis_id' => $analysisId,
                'manipulation_score' => $result->manipulation_score,
                'edit_count' => $result->getEditCount(),
                'processing_time' => $result->getProcessingDuration(),
            ]);

        } catch (\Exception $e) {
            Log::error('Audio analysis job failed', [
                'analysis_id' => $analysisId,
                'audio_file_id' => $audioFileId,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
                'max_attempts' => $this->tries,
            ]);

            // Let the job system handle retries
            if ($this->attempts() < $this->tries) {
                throw $e;
            }

            // Final failure - already marked as failed in the service
        }
    }

    /**
     * Handle a job failure after all retries are exhausted.
     */
    public function failed(?\Throwable $exception): void
    {
        Log::error('Audio analysis job failed permanently', [
            'analysis_id' => $this->analysis->id,
            'audio_file_id' => $this->analysis->audio_file_id,
            'error' => $exception?->getMessage(),
        ]);

        // Ensure the analysis is marked as failed
        if ($this->analysis->analysis_status !== AudioAnalysis::STATUS_FAILED) {
            $this->analysis->markAsFailed(
                $exception?->getMessage() ?? 'Job failed after maximum retry attempts'
            );
        }
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array<int, string>
     */
    public function tags(): array
    {
        return [
            'audio_analysis',
            'analysis:' . $this->analysis->id,
            'audio_file:' . $this->analysis->audio_file_id,
            'user:' . $this->analysis->created_by,
        ];
    }

    /**
     * Determine the time at which the job should timeout.
     */
    public function retryUntil(): \DateTime
    {
        // Allow retries for up to 24 hours
        return now()->addHours(24);
    }

    /**
     * Get the middleware the job should pass through.
     *
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [
            // Optionally add rate limiting or other middleware
            // new RateLimited('audio-analysis'),
        ];
    }

    /**
     * Determine if the job should be marked as failed on timeout.
     */
    public function shouldMarkAsFailedOnTimeout(): bool
    {
        return true;
    }

    /**
     * Get the unique ID for the job.
     * Prevents duplicate jobs for the same analysis.
     */
    public function uniqueId(): string
    {
        return 'audio-analysis-' . $this->analysis->id;
    }

    /**
     * The number of seconds after which the job's unique lock will be released.
     */
    public function uniqueFor(): int
    {
        return $this->timeout + 60; // Lock slightly longer than timeout
    }
}
