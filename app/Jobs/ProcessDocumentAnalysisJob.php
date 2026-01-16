<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\DocumentAnalysis;
use App\Services\OcrService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Background job for processing document OCR and analysis.
 *
 * This job handles the potentially long-running document analysis tasks including:
 * - Text extraction (OCR) using Tesseract and/or AI
 * - Language detection
 * - Named entity recognition
 * - Document type classification
 * - Table extraction
 */
class ProcessDocumentAnalysisJob implements ShouldQueue
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
     * Document analysis can take time, especially for large PDFs.
     *
     * @var int
     */
    public int $timeout = 900; // 15 minutes

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
        protected DocumentAnalysis $analysis
    ) {}

    /**
     * Execute the job.
     */
    public function handle(OcrService $ocrService): void
    {
        $analysisId = $this->analysis->id;
        $filename = $this->analysis->original_filename;

        Log::info('Starting document analysis job', [
            'analysis_id' => $analysisId,
            'filename' => $filename,
            'file_type' => $this->analysis->file_type,
            'attempt' => $this->attempts(),
        ]);

        try {
            // Perform the full analysis
            $result = $ocrService->analyzeDocument($this->analysis);

            Log::info('Document analysis job completed successfully', [
                'analysis_id' => $analysisId,
                'word_count' => $result->getWordCount(),
                'confidence' => $result->text_confidence,
                'language' => $result->language_detected,
                'document_type' => $result->document_type_detected,
                'entity_count' => $result->getEntityCount(),
                'processing_time_ms' => $result->processing_time_ms,
            ]);

        } catch (\Exception $e) {
            Log::error('Document analysis job failed', [
                'analysis_id' => $analysisId,
                'filename' => $filename,
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
        Log::error('Document analysis job failed permanently', [
            'analysis_id' => $this->analysis->id,
            'filename' => $this->analysis->original_filename,
            'error' => $exception?->getMessage(),
        ]);

        // Ensure the analysis is marked as failed
        if ($this->analysis->analysis_status !== DocumentAnalysis::STATUS_FAILED) {
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
            'document_analysis',
            'analysis:' . $this->analysis->id,
            'file_type:' . $this->analysis->file_type,
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
            // new RateLimited('document-analysis'),
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
        return 'document-analysis-' . $this->analysis->id;
    }

    /**
     * The number of seconds after which the job's unique lock will be released.
     */
    public function uniqueFor(): int
    {
        return $this->timeout + 60; // Lock slightly longer than timeout
    }
}
