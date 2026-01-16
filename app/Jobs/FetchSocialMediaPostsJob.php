<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\SocialMediaAccount;
use App\Services\SocialMediaService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Job for fetching latest posts from monitored social media accounts.
 *
 * This job can be scheduled to run periodically to check for new posts
 * from all monitored accounts or dispatched for a specific account.
 */
class FetchSocialMediaPostsJob implements ShouldQueue
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
     *
     * @param SocialMediaAccount|null $account Specific account to fetch, or null for batch processing
     * @param array $options Job options
     */
    public function __construct(
        protected ?SocialMediaAccount $account = null,
        protected array $options = []
    ) {}

    /**
     * Execute the job.
     *
     * @param SocialMediaService $socialMediaService
     */
    public function handle(SocialMediaService $socialMediaService): void
    {
        if ($this->account) {
            // Fetch posts for a specific account
            $this->fetchForAccount($socialMediaService, $this->account);
        } else {
            // Batch fetch for accounts needing check
            $this->batchFetch($socialMediaService);
        }
    }

    /**
     * Fetch posts for a specific account.
     *
     * @param SocialMediaService $socialMediaService
     * @param SocialMediaAccount $account
     */
    protected function fetchForAccount(
        SocialMediaService $socialMediaService,
        SocialMediaAccount $account
    ): void {
        $accountId = $account->id;
        $platform = $account->platform;
        $username = $account->username;

        Log::info('Starting social media post fetch for account', [
            'account_id' => $accountId,
            'platform' => $platform,
            'username' => $username,
        ]);

        try {
            // Check if account is still monitored
            $freshAccount = SocialMediaAccount::find($accountId);

            if (!$freshAccount || !$freshAccount->is_monitored) {
                Log::info('Account no longer monitored, skipping', [
                    'account_id' => $accountId,
                ]);
                return;
            }

            // Fetch posts
            $limit = $this->options['limit'] ?? 50;
            $posts = $socialMediaService->fetchLatestPosts($accountId, $limit);

            // Optionally archive new posts
            if ($this->options['auto_archive'] ?? false) {
                foreach ($posts as $post) {
                    if (!$post->is_archived) {
                        try {
                            $socialMediaService->archivePost($post->id, [
                                'archive_media' => $this->options['archive_media'] ?? true,
                            ]);
                        } catch (\Exception $e) {
                            Log::warning('Failed to auto-archive post', [
                                'post_id' => $post->id,
                                'error' => $e->getMessage(),
                            ]);
                        }
                    }
                }
            }

            Log::info('Social media post fetch completed for account', [
                'account_id' => $accountId,
                'platform' => $platform,
                'username' => $username,
                'posts_fetched' => $posts->count(),
            ]);

        } catch (\Exception $e) {
            Log::error('Social media post fetch failed for account', [
                'account_id' => $accountId,
                'platform' => $platform,
                'username' => $username,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            // Only throw if we have retries left
            if ($this->attempts() < $this->tries) {
                throw $e;
            }
        }
    }

    /**
     * Batch fetch for multiple accounts that need checking.
     *
     * @param SocialMediaService $socialMediaService
     */
    protected function batchFetch(SocialMediaService $socialMediaService): void
    {
        $batchSize = $this->options['batch_size'] ?? 10;
        $olderThanMinutes = $this->options['older_than_minutes'] ?? 60;

        Log::info('Starting batch social media post fetch', [
            'batch_size' => $batchSize,
            'older_than_minutes' => $olderThanMinutes,
        ]);

        $accounts = $socialMediaService->getAccountsNeedingCheck($batchSize, $olderThanMinutes);

        $processed = 0;
        $failed = 0;

        foreach ($accounts as $account) {
            try {
                $this->fetchForAccount($socialMediaService, $account);
                $processed++;
            } catch (\Exception $e) {
                $failed++;
                Log::error('Failed to fetch posts for account in batch', [
                    'account_id' => $account->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('Batch social media post fetch completed', [
            'total_accounts' => $accounts->count(),
            'processed' => $processed,
            'failed' => $failed,
        ]);
    }

    /**
     * Handle a job failure.
     *
     * @param \Throwable|null $exception
     */
    public function failed(?\Throwable $exception): void
    {
        $context = [
            'error' => $exception?->getMessage(),
        ];

        if ($this->account) {
            $context['account_id'] = $this->account->id;
            $context['platform'] = $this->account->platform;
            $context['username'] = $this->account->username;
        }

        Log::error('Social media post fetch job failed permanently', $context);
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array<int, string>
     */
    public function tags(): array
    {
        $tags = ['social_media', 'post_fetch'];

        if ($this->account) {
            $tags[] = 'account:' . $this->account->id;
            $tags[] = 'platform:' . $this->account->platform;
        } else {
            $tags[] = 'batch';
        }

        return $tags;
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
        return now()->addHours(6);
    }

    /**
     * Get the middleware the job should pass through.
     *
     * @return array
     */
    public function middleware(): array
    {
        // Prevent overlapping jobs for the same account
        if ($this->account) {
            return [
                new \Illuminate\Queue\Middleware\WithoutOverlapping(
                    'social_media_fetch:' . $this->account->id
                ),
            ];
        }

        return [
            new \Illuminate\Queue\Middleware\WithoutOverlapping('social_media_batch_fetch'),
        ];
    }

    /**
     * Determine the number of seconds before the overlapping lock should be released.
     *
     * @return int
     */
    public function releaseAfter(): int
    {
        return $this->timeout + 60;
    }
}
