<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\PreservedEvidence;
use App\Services\EvidencePreservationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PreserveEvidenceJob implements ShouldQueue
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
    public int $timeout = 300;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var array
     */
    public array $backoff = [60, 120, 300];

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected PreservedEvidence $evidence,
        protected array $options = []
    ) {}

    /**
     * Execute the job.
     */
    public function handle(EvidencePreservationService $preservationService): void
    {
        $evidenceId = $this->evidence->id;
        $originalUrl = $this->evidence->original_url;

        Log::info('Starting evidence preservation job', [
            'evidence_id' => $evidenceId,
            'url' => $originalUrl,
            'options' => $this->options,
        ]);

        try {
            // Step 1: Preserve to local storage
            $this->evidence = $preservationService->preserveToLocal($this->evidence);

            Log::info('Evidence preserved locally', [
                'evidence_id' => $evidenceId,
                'local_path' => $this->evidence->local_path,
            ]);

            // Step 2: Submit to Wayback Machine (if enabled)
            if ($this->options['submit_to_wayback'] ?? true) {
                $archivedUrl = $preservationService->submitToWaybackMachine($this->evidence);

                if ($archivedUrl) {
                    Log::info('Evidence submitted to Wayback Machine', [
                        'evidence_id' => $evidenceId,
                        'archived_url' => $archivedUrl,
                    ]);
                }
            }

            // Step 3: Preserve to IPFS (if enabled)
            if ($this->options['preserve_to_ipfs'] ?? false) {
                $ipfsHash = $preservationService->preserveToIpfs($this->evidence);

                if ($ipfsHash) {
                    Log::info('Evidence uploaded to IPFS', [
                        'evidence_id' => $evidenceId,
                        'ipfs_hash' => $ipfsHash,
                    ]);
                }
            }

            // Step 4: Capture screenshot (if enabled and applicable)
            if ($this->options['capture_screenshot'] ?? false) {
                $screenshotPath = $preservationService->captureScreenshot($this->evidence);

                if ($screenshotPath) {
                    Log::info('Screenshot captured', [
                        'evidence_id' => $evidenceId,
                        'screenshot_path' => $screenshotPath,
                    ]);
                }
            }

            Log::info('Evidence preservation job completed successfully', [
                'evidence_id' => $evidenceId,
                'status' => $this->evidence->capture_status,
            ]);

        } catch (\Exception $e) {
            Log::error('Evidence preservation job failed', [
                'evidence_id' => $evidenceId,
                'url' => $originalUrl,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            // Only throw if we have retries left
            if ($this->attempts() < $this->tries) {
                throw $e;
            }

            // Final failure - mark as failed
            $this->evidence->markAsFailed($e->getMessage());
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(?\Throwable $exception): void
    {
        Log::error('Evidence preservation job failed permanently', [
            'evidence_id' => $this->evidence->id,
            'url' => $this->evidence->original_url,
            'error' => $exception?->getMessage(),
        ]);

        $this->evidence->markAsFailed(
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
            'evidence_preservation',
            'evidence:' . $this->evidence->id,
            'user:' . $this->evidence->created_by,
        ];
    }

    /**
     * Calculate the number of seconds to wait before retrying the job.
     */
    public function retryAfter(): int
    {
        $attempt = $this->attempts();
        return $this->backoff[$attempt - 1] ?? 300;
    }

    /**
     * Determine the time at which the job should timeout.
     */
    public function retryUntil(): \DateTime
    {
        return now()->addHours(24);
    }
}
