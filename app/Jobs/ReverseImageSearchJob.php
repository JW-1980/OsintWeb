<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\ReverseImageSearch;
use App\Services\ReverseImageService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * ReverseImageSearchJob
 *
 * Background job for executing reverse image searches across multiple engines.
 * This job is dispatched when a user initiates a reverse image search and handles:
 * - Querying all configured search engines
 * - Storing results in the database
 * - Detecting potential image manipulation
 * - Updating search status and statistics
 */
class ReverseImageSearchJob implements ShouldQueue
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
     * @var array<int>
     */
    public array $backoff = [60, 120, 300];

    /**
     * Create a new job instance.
     *
     * @param ReverseImageSearch $search The search record to process
     * @param array|null $engines Specific engines to search (null = all enabled)
     */
    public function __construct(
        protected ReverseImageSearch $search,
        protected ?array $engines = null
    ) {}

    /**
     * Execute the job.
     *
     * @param ReverseImageService $imageService
     * @return void
     */
    public function handle(ReverseImageService $imageService): void
    {
        $searchId = $this->search->id;
        $searchUuid = $this->search->uuid;

        Log::info('Starting reverse image search job', [
            'search_id' => $searchId,
            'uuid' => $searchUuid,
            'engines' => $this->engines,
        ]);

        try {
            // Refresh the search to get latest state
            $this->search = $this->search->fresh();

            // Check if search was cancelled or already completed
            if ($this->search->isCompleted()) {
                Log::info('Search already completed, skipping', [
                    'search_id' => $searchId,
                ]);
                return;
            }

            // Check if image file still exists
            if (!$this->search->imageExists()) {
                $this->search->markAsFailed('Image file not found');
                Log::error('Image file not found for search', [
                    'search_id' => $searchId,
                    'image_path' => $this->search->image_path,
                ]);
                return;
            }

            // Execute the search across all engines
            $result = $imageService->searchAllEngines($this->search, $this->engines);

            Log::info('Reverse image search job completed', [
                'search_id' => $searchId,
                'uuid' => $searchUuid,
                'status' => $result->search_status,
                'total_results' => $result->total_results,
                'engines_completed' => $result->engines_completed,
                'duration_seconds' => $result->getSearchDuration(),
            ]);

        } catch (\Exception $e) {
            Log::error('Reverse image search job failed', [
                'search_id' => $searchId,
                'uuid' => $searchUuid,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            // Only throw if we have retries left
            if ($this->attempts() < $this->tries) {
                throw $e;
            }

            // Final failure - mark as failed
            $this->search->markAsFailed($e->getMessage());
        }
    }

    /**
     * Handle a job failure.
     *
     * @param \Throwable|null $exception
     * @return void
     */
    public function failed(?\Throwable $exception): void
    {
        Log::error('Reverse image search job failed permanently', [
            'search_id' => $this->search->id,
            'uuid' => $this->search->uuid,
            'error' => $exception?->getMessage(),
        ]);

        $this->search->markAsFailed(
            $exception?->getMessage() ?? 'Unknown error occurred during search'
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
            'reverse_image_search',
            'search:' . $this->search->id,
            'user:' . $this->search->created_by,
        ];
    }

    /**
     * Calculate the number of seconds to wait before retrying the job.
     *
     * @return int
     */
    public function retryAfter(): int
    {
        $attempt = $this->attempts();
        return $this->backoff[$attempt - 1] ?? 300;
    }

    /**
     * Determine the time at which the job should timeout.
     *
     * @return \DateTime
     */
    public function retryUntil(): \DateTime
    {
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
            // Add rate limiting middleware to prevent API abuse
            new \Illuminate\Queue\Middleware\RateLimited('reverse-image-search'),
        ];
    }

    /**
     * Determine the unique ID of the job.
     *
     * Prevents duplicate searches from being queued.
     *
     * @return string
     */
    public function uniqueId(): string
    {
        return 'reverse-image-search-' . $this->search->id;
    }

    /**
     * The number of seconds after which the job's unique lock will be released.
     *
     * @return int
     */
    public function uniqueFor(): int
    {
        return 3600; // 1 hour
    }
}
