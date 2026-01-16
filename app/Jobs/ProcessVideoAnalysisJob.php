<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\VideoAnalysis;
use App\Services\VideoAnalysisService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessVideoAnalysisJob implements ShouldQueue
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
     *
     * @var int
     */
    public int $timeout = 600; // 10 minutes

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var array
     */
    public array $backoff = [30, 60, 120];

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected VideoAnalysis $analysis,
        protected array $options = []
    ) {}

    /**
     * Execute the job.
     */
    public function handle(VideoAnalysisService $videoAnalysisService): void
    {
        $analysisId = $this->analysis->id;

        Log::info('Starting video analysis job', [
            'analysis_id' => $analysisId,
            'filename' => $this->analysis->original_filename,
            'options' => $this->options,
        ]);

        try {
            // Step 1: Extract metadata and analyze video
            $this->analysis = $videoAnalysisService->analyzeVideo($this->analysis);

            Log::info('Video metadata extracted', [
                'analysis_id' => $analysisId,
                'duration' => $this->analysis->duration_seconds,
                'resolution' => $this->analysis->getResolutionString(),
                'codec' => $this->analysis->codec,
            ]);

            // Step 2: Extract keyframes at intervals (if enabled)
            if ($this->options['extract_keyframes'] ?? true) {
                $interval = (float) ($this->options['keyframe_interval'] ?? 5.0);

                $keyframes = $videoAnalysisService->extractKeyframes(
                    $this->analysis,
                    $interval
                );

                Log::info('Keyframes extracted', [
                    'analysis_id' => $analysisId,
                    'count' => count($keyframes),
                    'interval' => $interval,
                ]);
            }

            // Step 3: Detect scene changes (if enabled)
            if ($this->options['detect_scene_changes'] ?? true) {
                $threshold = (float) ($this->options['scene_threshold'] ?? 0.4);

                $sceneChanges = $videoAnalysisService->detectSceneChanges(
                    $this->analysis,
                    $threshold
                );

                Log::info('Scene changes detected', [
                    'analysis_id' => $analysisId,
                    'count' => count($sceneChanges),
                    'threshold' => $threshold,
                ]);
            }

            // Step 4: Analyze individual frames (if enabled)
            if ($this->options['analyze_frames'] ?? false) {
                $this->analyzeKeyframes($videoAnalysisService);
            }

            Log::info('Video analysis job completed successfully', [
                'analysis_id' => $analysisId,
                'status' => $this->analysis->analysis_status,
            ]);

        } catch (\Exception $e) {
            Log::error('Video analysis job failed', [
                'analysis_id' => $analysisId,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            // Only throw if we have retries left
            if ($this->attempts() < $this->tries) {
                throw $e;
            }

            // Final failure - mark as failed
            $this->analysis->markAsFailed($e->getMessage());
        }
    }

    /**
     * Analyze individual keyframes.
     */
    protected function analyzeKeyframes(VideoAnalysisService $videoAnalysisService): void
    {
        // Limit to representative or scene change frames to avoid excessive processing
        $keyframes = $this->analysis->keyframes()
            ->where(function ($q) {
                $q->where('is_scene_change', true)
                    ->orWhere('is_representative', true);
            })
            ->limit(20)
            ->get();

        foreach ($keyframes as $keyframe) {
            try {
                $videoAnalysisService->analyzeFrame($keyframe);
            } catch (\Exception $e) {
                Log::warning('Keyframe analysis failed', [
                    'keyframe_id' => $keyframe->id,
                    'error' => $e->getMessage(),
                ]);
                // Continue with other frames
            }
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(?\Throwable $exception): void
    {
        Log::error('Video analysis job failed permanently', [
            'analysis_id' => $this->analysis->id,
            'filename' => $this->analysis->original_filename,
            'error' => $exception?->getMessage(),
        ]);

        $this->analysis->markAsFailed(
            $exception?->getMessage() ?? 'Unknown error occurred'
        );
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array<int, string>
     */
    public function tags(): array
    {
        return [
            'video_analysis',
            'analysis:' . $this->analysis->id,
            'user:' . $this->analysis->created_by,
        ];
    }

    /**
     * Calculate the number of seconds to wait before retrying the job.
     */
    public function retryAfter(): int
    {
        $attempt = $this->attempts();
        return $this->backoff[$attempt - 1] ?? 120;
    }

    /**
     * Determine the time at which the job should timeout.
     */
    public function retryUntil(): \DateTime
    {
        return now()->addHours(2);
    }

    /**
     * Get the middleware the job should pass through.
     *
     * @return array
     */
    public function middleware(): array
    {
        return [];
    }
}
