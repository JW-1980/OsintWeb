<?php

declare(strict_types=1);

namespace App\Services;

use App\Domains\Intelligence\Models\Event;
use App\Models\SocialMediaAccount;
use App\Models\SocialMediaAlert;
use App\Models\SocialMediaPost;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Social Media Service
 *
 * Handles all business logic for social media monitoring including:
 * - Account management and monitoring
 * - Post fetching and storage
 * - Content archival and preservation
 * - Keyword detection and alerting
 * - Event linking and evidence management
 */
class SocialMediaService
{
    /**
     * Add a new social media account to monitor.
     *
     * @param string $platform Platform identifier (twitter, telegram, etc.)
     * @param string $username Account username
     * @param User|null $addedBy User adding the account
     * @param array $options Additional options
     * @return SocialMediaAccount
     * @throws \InvalidArgumentException
     */
    public function addAccount(
        string $platform,
        string $username,
        ?User $addedBy = null,
        array $options = []
    ): SocialMediaAccount {
        // Validate platform
        if (!in_array($platform, SocialMediaAccount::getPlatforms())) {
            throw new \InvalidArgumentException("Invalid platform: {$platform}");
        }

        // Clean username (remove @ prefix if present)
        $username = ltrim($username, '@');

        // Check if account already exists
        $existing = SocialMediaAccount::withTrashed()
            ->where('platform', $platform)
            ->where('username', $username)
            ->first();

        if ($existing) {
            if ($existing->trashed()) {
                $existing->restore();
                $existing->update(['is_monitored' => true]);
                return $existing->fresh();
            }

            return $existing;
        }

        // Create account
        $account = SocialMediaAccount::create([
            'uuid' => Str::uuid()->toString(),
            'platform' => $platform,
            'username' => $username,
            'display_name' => $options['display_name'] ?? null,
            'profile_url' => $options['profile_url'] ?? null,
            'platform_user_id' => $options['platform_user_id'] ?? null,
            'profile_image_url' => $options['profile_image_url'] ?? null,
            'follower_count' => $options['follower_count'] ?? 0,
            'following_count' => $options['following_count'] ?? 0,
            'post_count' => $options['post_count'] ?? 0,
            'account_created_at' => $options['account_created_at'] ?? null,
            'bio' => $options['bio'] ?? null,
            'location' => $options['location'] ?? null,
            'is_verified' => $options['is_verified'] ?? false,
            'is_monitored' => $options['is_monitored'] ?? true,
            'monitoring_priority' => $options['monitoring_priority'] ?? SocialMediaAccount::PRIORITY_MEDIUM,
            'metadata' => $options['metadata'] ?? null,
            'added_by' => $addedBy?->id,
        ]);

        // Generate profile URL if not provided
        if (!$account->profile_url) {
            $account->update(['profile_url' => $account->generateProfileUrl()]);
        }

        Log::info('Social media account added for monitoring', [
            'account_id' => $account->id,
            'platform' => $platform,
            'username' => $username,
            'added_by' => $addedBy?->id,
        ]);

        return $account;
    }

    /**
     * Fetch latest posts for an account.
     *
     * This is a placeholder implementation. In production, you would integrate
     * with platform-specific APIs or scraping services.
     *
     * @param int $accountId Account ID
     * @param int $limit Maximum posts to fetch
     * @return Collection Fetched posts
     */
    public function fetchLatestPosts(int $accountId, int $limit = 50): Collection
    {
        $account = SocialMediaAccount::findOrFail($accountId);

        Log::info('Fetching latest posts for account', [
            'account_id' => $accountId,
            'platform' => $account->platform,
            'username' => $account->username,
        ]);

        // Mark account as checked
        $account->markAsChecked();

        // In a real implementation, you would:
        // 1. Call the appropriate platform API
        // 2. Parse the response
        // 3. Store new posts
        // 4. Update account stats

        // Return recent posts from database as placeholder
        return $account->posts()
            ->orderBy('posted_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Store a post from external data.
     *
     * @param int $accountId Account ID
     * @param array $postData Post data from API or scraper
     * @return SocialMediaPost
     */
    public function storePost(int $accountId, array $postData): SocialMediaPost
    {
        $account = SocialMediaAccount::findOrFail($accountId);

        // Check if post already exists
        $existing = SocialMediaPost::where('account_id', $accountId)
            ->where('platform_post_id', $postData['platform_post_id'])
            ->first();

        if ($existing) {
            // Update engagement metrics
            $existing->updateMetrics([
                'like_count' => $postData['like_count'] ?? $existing->like_count,
                'share_count' => $postData['share_count'] ?? $existing->share_count,
                'comment_count' => $postData['comment_count'] ?? $existing->comment_count,
                'view_count' => $postData['view_count'] ?? $existing->view_count,
            ]);

            return $existing->fresh();
        }

        // Create new post
        $post = SocialMediaPost::create([
            'uuid' => Str::uuid()->toString(),
            'account_id' => $accountId,
            'platform_post_id' => $postData['platform_post_id'],
            'post_url' => $postData['post_url'] ?? null,
            'post_type' => $postData['post_type'] ?? SocialMediaPost::TYPE_TEXT,
            'content_text' => $postData['content_text'] ?? null,
            'media_urls' => $postData['media_urls'] ?? null,
            'hashtags' => $this->extractHashtags($postData['content_text'] ?? ''),
            'mentions' => $this->extractMentions($postData['content_text'] ?? ''),
            'like_count' => $postData['like_count'] ?? 0,
            'share_count' => $postData['share_count'] ?? 0,
            'comment_count' => $postData['comment_count'] ?? 0,
            'view_count' => $postData['view_count'] ?? 0,
            'posted_at' => $postData['posted_at'] ?? now(),
            'is_reply' => $postData['is_reply'] ?? false,
            'reply_to_id' => $postData['reply_to_id'] ?? null,
            'is_repost' => $postData['is_repost'] ?? false,
            'original_post_id' => $postData['original_post_id'] ?? null,
            'language_detected' => $postData['language_detected'] ?? null,
            'sentiment_score' => $postData['sentiment_score'] ?? null,
        ]);

        // Update account's last post timestamp
        if ($post->posted_at && (!$account->last_post_at || $post->posted_at->isAfter($account->last_post_at))) {
            $account->update(['last_post_at' => $post->posted_at]);
        }

        // Process alerts for this new post
        $this->processAlertsForPost($post);

        Log::info('Social media post stored', [
            'post_id' => $post->id,
            'account_id' => $accountId,
            'platform_post_id' => $postData['platform_post_id'],
        ]);

        return $post;
    }

    /**
     * Archive a post by preserving its content locally.
     *
     * @param int $postId Post ID
     * @param array $options Archive options
     * @return SocialMediaPost
     * @throws \Exception
     */
    public function archivePost(int $postId, array $options = []): SocialMediaPost
    {
        $post = SocialMediaPost::findOrFail($postId);

        if ($post->is_archived && !($options['force'] ?? false)) {
            Log::info('Post already archived', ['post_id' => $postId]);
            return $post;
        }

        $preservedPaths = [];

        try {
            // Archive text content
            $textPath = $this->archiveTextContent($post);
            if ($textPath) {
                $preservedPaths['text'] = $textPath;
            }

            // Archive media files
            if ($options['archive_media'] ?? true) {
                $mediaPaths = $this->archiveMediaContent($post);
                if (!empty($mediaPaths)) {
                    $preservedPaths['media'] = $mediaPaths;
                }
            }

            // Archive metadata
            $metadataPath = $this->archiveMetadata($post);
            if ($metadataPath) {
                $preservedPaths['metadata'] = $metadataPath;
            }

            $post->markAsArchived($preservedPaths);

            Log::info('Post archived successfully', [
                'post_id' => $postId,
                'paths' => $preservedPaths,
            ]);

            return $post->fresh();

        } catch (\Exception $e) {
            Log::error('Failed to archive post', [
                'post_id' => $postId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Search posts across all accounts.
     *
     * @param string $query Search query
     * @param array $filters Additional filters
     * @param int $perPage Results per page
     * @return LengthAwarePaginator
     */
    public function searchPosts(
        string $query,
        array $filters = [],
        int $perPage = 25
    ): LengthAwarePaginator {
        $builder = SocialMediaPost::query()
            ->with(['account']);

        // Full-text search on content
        if (!empty($query)) {
            // Try full-text search first, fall back to LIKE
            try {
                $builder->fullTextSearch($query);
            } catch (\Exception $e) {
                $builder->search($query);
            }
        }

        // Platform filter
        if (!empty($filters['platform'])) {
            $builder->whereHas('account', function ($q) use ($filters) {
                $q->where('platform', $filters['platform']);
            });
        }

        // Account filter
        if (!empty($filters['account_id'])) {
            $builder->where('account_id', $filters['account_id']);
        }

        // Post type filter
        if (!empty($filters['post_type'])) {
            $builder->where('post_type', $filters['post_type']);
        }

        // Date range filter
        if (!empty($filters['from_date'])) {
            $builder->where('posted_at', '>=', $filters['from_date']);
        }
        if (!empty($filters['to_date'])) {
            $builder->where('posted_at', '<=', $filters['to_date']);
        }

        // Language filter
        if (!empty($filters['language'])) {
            $builder->where('language_detected', $filters['language']);
        }

        // Sentiment filter
        if (isset($filters['sentiment'])) {
            match ($filters['sentiment']) {
                'positive' => $builder->positiveSentiment(),
                'negative' => $builder->negativeSentiment(),
                'neutral' => $builder->whereBetween('sentiment_score', [
                    SocialMediaPost::SENTIMENT_NEGATIVE_THRESHOLD,
                    SocialMediaPost::SENTIMENT_POSITIVE_THRESHOLD,
                ]),
                default => null,
            };
        }

        // Has media filter
        if (!empty($filters['has_media'])) {
            $builder->withMedia();
        }

        // Has geolocation filter
        if (!empty($filters['has_geolocation'])) {
            $builder->withGeolocation();
        }

        // Archived filter
        if (isset($filters['is_archived'])) {
            $filters['is_archived'] ? $builder->archived() : $builder->notArchived();
        }

        // Linked to event filter
        if (!empty($filters['has_event'])) {
            $builder->linkedToEvent();
        }

        // Hashtag filter
        if (!empty($filters['hashtag'])) {
            $builder->withHashtag($filters['hashtag']);
        }

        // Sort options
        $sortField = $filters['sort_by'] ?? 'posted_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';

        if ($sortField === 'engagement') {
            $builder->orderByEngagement($sortDirection);
        } else {
            $builder->orderBy($sortField, $sortDirection);
        }

        return $builder->paginate($perPage);
    }

    /**
     * Detect keywords in a post.
     *
     * @param int $postId Post ID
     * @param array $keywords Keywords to detect
     * @return array Matched keywords
     */
    public function detectKeywords(int $postId, array $keywords): array
    {
        $post = SocialMediaPost::findOrFail($postId);

        return $post->findMatchingKeywords($keywords);
    }

    /**
     * Link a post to an event as evidence.
     *
     * @param int $postId Post ID
     * @param int $eventId Event ID
     * @return SocialMediaPost
     */
    public function linkToEvent(int $postId, int $eventId): SocialMediaPost
    {
        $post = SocialMediaPost::findOrFail($postId);
        $event = Event::findOrFail($eventId);

        $post->linkToEvent($eventId);

        Log::info('Post linked to event', [
            'post_id' => $postId,
            'event_id' => $eventId,
        ]);

        return $post->fresh();
    }

    /**
     * Unlink a post from an event.
     *
     * @param int $postId Post ID
     * @return SocialMediaPost
     */
    public function unlinkFromEvent(int $postId): SocialMediaPost
    {
        $post = SocialMediaPost::findOrFail($postId);

        $oldEventId = $post->event_id;
        $post->unlinkFromEvent();

        Log::info('Post unlinked from event', [
            'post_id' => $postId,
            'old_event_id' => $oldEventId,
        ]);

        return $post->fresh();
    }

    /**
     * Get statistics for an account.
     *
     * @param int $accountId Account ID
     * @return array
     */
    public function getAccountStats(int $accountId): array
    {
        $account = SocialMediaAccount::findOrFail($accountId);

        $postsQuery = SocialMediaPost::where('account_id', $accountId);

        $totalPosts = $postsQuery->count();
        $archivedPosts = (clone $postsQuery)->archived()->count();
        $linkedPosts = (clone $postsQuery)->linkedToEvent()->count();
        $postsWithMedia = (clone $postsQuery)->withMedia()->count();

        // Engagement stats
        $engagementStats = DB::table('social_media_posts')
            ->where('account_id', $accountId)
            ->whereNull('deleted_at')
            ->selectRaw('
                SUM(like_count) as total_likes,
                SUM(share_count) as total_shares,
                SUM(comment_count) as total_comments,
                SUM(view_count) as total_views,
                AVG(like_count) as avg_likes,
                AVG(share_count) as avg_shares,
                AVG(comment_count) as avg_comments,
                AVG(sentiment_score) as avg_sentiment
            ')
            ->first();

        // Post type distribution
        $postTypeDistribution = DB::table('social_media_posts')
            ->where('account_id', $accountId)
            ->whereNull('deleted_at')
            ->selectRaw('post_type, COUNT(*) as count')
            ->groupBy('post_type')
            ->pluck('count', 'post_type')
            ->toArray();

        // Posts over time (last 30 days)
        $postsOverTime = DB::table('social_media_posts')
            ->where('account_id', $accountId)
            ->whereNull('deleted_at')
            ->where('posted_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(posted_at) as date, COUNT(*) as count')
            ->groupBy(DB::raw('DATE(posted_at)'))
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date')
            ->toArray();

        // Top hashtags
        $topHashtags = $this->getTopHashtags($accountId, 10);

        return [
            'account' => [
                'id' => $account->id,
                'uuid' => $account->uuid,
                'platform' => $account->platform,
                'username' => $account->username,
                'follower_count' => $account->follower_count,
                'following_count' => $account->following_count,
                'is_verified' => $account->is_verified,
                'is_monitored' => $account->is_monitored,
                'last_checked_at' => $account->last_checked_at?->toIso8601String(),
                'last_post_at' => $account->last_post_at?->toIso8601String(),
            ],
            'posts' => [
                'total' => $totalPosts,
                'archived' => $archivedPosts,
                'linked_to_events' => $linkedPosts,
                'with_media' => $postsWithMedia,
            ],
            'engagement' => [
                'total_likes' => (int) ($engagementStats->total_likes ?? 0),
                'total_shares' => (int) ($engagementStats->total_shares ?? 0),
                'total_comments' => (int) ($engagementStats->total_comments ?? 0),
                'total_views' => (int) ($engagementStats->total_views ?? 0),
                'avg_likes' => round((float) ($engagementStats->avg_likes ?? 0), 1),
                'avg_shares' => round((float) ($engagementStats->avg_shares ?? 0), 1),
                'avg_comments' => round((float) ($engagementStats->avg_comments ?? 0), 1),
                'avg_sentiment' => round((float) ($engagementStats->avg_sentiment ?? 0), 3),
            ],
            'post_types' => $postTypeDistribution,
            'posts_over_time' => $postsOverTime,
            'top_hashtags' => $topHashtags,
        ];
    }

    /**
     * Get overall social media monitoring statistics.
     *
     * @return array
     */
    public function getOverallStats(): array
    {
        $accountStats = DB::table('social_media_accounts')
            ->whereNull('deleted_at')
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN is_monitored = 1 THEN 1 ELSE 0 END) as monitored,
                SUM(CASE WHEN is_verified = 1 THEN 1 ELSE 0 END) as verified
            ')
            ->first();

        $platformDistribution = DB::table('social_media_accounts')
            ->whereNull('deleted_at')
            ->selectRaw('platform, COUNT(*) as count')
            ->groupBy('platform')
            ->pluck('count', 'platform')
            ->toArray();

        $postStats = DB::table('social_media_posts')
            ->whereNull('deleted_at')
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN is_archived = 1 THEN 1 ELSE 0 END) as archived,
                SUM(CASE WHEN event_id IS NOT NULL THEN 1 ELSE 0 END) as linked_to_events
            ')
            ->first();

        $alertStats = DB::table('social_media_alerts')
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active,
                SUM(trigger_count) as total_triggers
            ')
            ->first();

        // Recent activity (last 24 hours)
        $recentPosts = SocialMediaPost::where('created_at', '>=', now()->subDay())
            ->count();

        return [
            'accounts' => [
                'total' => (int) ($accountStats->total ?? 0),
                'monitored' => (int) ($accountStats->monitored ?? 0),
                'verified' => (int) ($accountStats->verified ?? 0),
                'by_platform' => $platformDistribution,
            ],
            'posts' => [
                'total' => (int) ($postStats->total ?? 0),
                'archived' => (int) ($postStats->archived ?? 0),
                'linked_to_events' => (int) ($postStats->linked_to_events ?? 0),
                'last_24h' => $recentPosts,
            ],
            'alerts' => [
                'total' => (int) ($alertStats->total ?? 0),
                'active' => (int) ($alertStats->active ?? 0),
                'total_triggers' => (int) ($alertStats->total_triggers ?? 0),
            ],
        ];
    }

    /**
     * Create an alert for social media monitoring.
     *
     * @param User $user Alert owner
     * @param array $data Alert configuration
     * @return SocialMediaAlert
     */
    public function createAlert(User $user, array $data): SocialMediaAlert
    {
        $alert = SocialMediaAlert::create([
            'uuid' => Str::uuid()->toString(),
            'account_id' => $data['account_id'] ?? null,
            'keyword' => $data['keyword'] ?? null,
            'alert_type' => $data['alert_type'],
            'user_id' => $user->id,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'notification_channels' => $data['notification_channels'] ?? [SocialMediaAlert::CHANNEL_DATABASE],
            'filters' => $data['filters'] ?? null,
            'is_active' => $data['is_active'] ?? true,
            'cooldown_minutes' => $data['cooldown_minutes'] ?? 5,
        ]);

        Log::info('Social media alert created', [
            'alert_id' => $alert->id,
            'user_id' => $user->id,
            'alert_type' => $alert->alert_type,
        ]);

        return $alert;
    }

    /**
     * Process alerts for a new post.
     *
     * @param SocialMediaPost $post
     * @return array Triggered alerts
     */
    public function processAlertsForPost(SocialMediaPost $post): array
    {
        $triggeredAlerts = [];

        // Get all triggerable alerts
        $alerts = SocialMediaAlert::triggerable()
            ->where(function ($query) use ($post) {
                // Global alerts (no specific account)
                $query->whereNull('account_id')
                    // Or alerts for this specific account
                    ->orWhere('account_id', $post->account_id);
            })
            ->get();

        foreach ($alerts as $alert) {
            $match = $alert->matchesPost($post);

            if ($match) {
                $alert->recordTrigger($post, $match['reason'], $match['details']);
                $triggeredAlerts[] = [
                    'alert_id' => $alert->id,
                    'alert_name' => $alert->name,
                    'reason' => $match['reason'],
                    'details' => $match['details'],
                ];

                // Here you would dispatch notifications
                // Notification::send($alert->user, new SocialMediaAlertNotification($alert, $post, $match));

                Log::info('Social media alert triggered', [
                    'alert_id' => $alert->id,
                    'post_id' => $post->id,
                    'reason' => $match['reason'],
                ]);
            }
        }

        return $triggeredAlerts;
    }

    /**
     * Get accounts needing to be checked.
     *
     * @param int $limit Maximum accounts to return
     * @param int $olderThanMinutes Minimum minutes since last check
     * @return Collection
     */
    public function getAccountsNeedingCheck(int $limit = 10, int $olderThanMinutes = 60): Collection
    {
        return SocialMediaAccount::needingCheck($olderThanMinutes)
            ->limit($limit)
            ->get();
    }

    /**
     * Extract hashtags from text content.
     *
     * @param string $text
     * @return array
     */
    protected function extractHashtags(string $text): array
    {
        preg_match_all('/#(\w+)/u', $text, $matches);
        return array_unique($matches[1] ?? []);
    }

    /**
     * Extract mentions from text content.
     *
     * @param string $text
     * @return array
     */
    protected function extractMentions(string $text): array
    {
        preg_match_all('/@(\w+)/u', $text, $matches);
        return array_unique($matches[1] ?? []);
    }

    /**
     * Archive text content to storage.
     *
     * @param SocialMediaPost $post
     * @return string|null Storage path
     */
    protected function archiveTextContent(SocialMediaPost $post): ?string
    {
        if (empty($post->content_text)) {
            return null;
        }

        $content = json_encode([
            'post_id' => $post->id,
            'uuid' => $post->uuid,
            'platform' => $post->account?->platform,
            'username' => $post->account?->username,
            'content_text' => $post->content_text,
            'posted_at' => $post->posted_at?->toIso8601String(),
            'archived_at' => now()->toIso8601String(),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $path = "social_media/posts/{$post->uuid}/content.json";
        Storage::disk('local')->put($path, $content);

        return $path;
    }

    /**
     * Archive media content to storage.
     *
     * @param SocialMediaPost $post
     * @return array Preserved media paths
     */
    protected function archiveMediaContent(SocialMediaPost $post): array
    {
        $mediaUrls = $post->media_urls ?? [];
        $preservedPaths = [];

        foreach ($mediaUrls as $index => $url) {
            try {
                $response = Http::timeout(30)->get($url);

                if ($response->successful()) {
                    $extension = $this->getExtensionFromUrl($url) ?? 'bin';
                    $path = "social_media/posts/{$post->uuid}/media_{$index}.{$extension}";

                    Storage::disk('local')->put($path, $response->body());
                    $preservedPaths[] = $path;
                }
            } catch (\Exception $e) {
                Log::warning('Failed to archive media', [
                    'post_id' => $post->id,
                    'url' => $url,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $preservedPaths;
    }

    /**
     * Archive post metadata.
     *
     * @param SocialMediaPost $post
     * @return string|null
     */
    protected function archiveMetadata(SocialMediaPost $post): ?string
    {
        $metadata = [
            'post_id' => $post->id,
            'uuid' => $post->uuid,
            'account' => [
                'id' => $post->account?->id,
                'platform' => $post->account?->platform,
                'username' => $post->account?->username,
                'display_name' => $post->account?->display_name,
            ],
            'platform_post_id' => $post->platform_post_id,
            'post_url' => $post->post_url,
            'post_type' => $post->post_type,
            'posted_at' => $post->posted_at?->toIso8601String(),
            'engagement' => [
                'like_count' => $post->like_count,
                'share_count' => $post->share_count,
                'comment_count' => $post->comment_count,
                'view_count' => $post->view_count,
            ],
            'hashtags' => $post->hashtags,
            'mentions' => $post->mentions,
            'language_detected' => $post->language_detected,
            'sentiment_score' => $post->sentiment_score,
            'archived_at' => now()->toIso8601String(),
        ];

        $path = "social_media/posts/{$post->uuid}/metadata.json";
        Storage::disk('local')->put($path, json_encode($metadata, JSON_PRETTY_PRINT));

        return $path;
    }

    /**
     * Get file extension from URL.
     *
     * @param string $url
     * @return string|null
     */
    protected function getExtensionFromUrl(string $url): ?string
    {
        $path = parse_url($url, PHP_URL_PATH);
        if (!$path) {
            return null;
        }

        $extension = pathinfo($path, PATHINFO_EXTENSION);
        return $extension ?: null;
    }

    /**
     * Get top hashtags for an account.
     *
     * @param int $accountId
     * @param int $limit
     * @return array
     */
    protected function getTopHashtags(int $accountId, int $limit = 10): array
    {
        $posts = SocialMediaPost::where('account_id', $accountId)
            ->whereNotNull('hashtags')
            ->get(['hashtags']);

        $hashtagCounts = [];

        foreach ($posts as $post) {
            foreach ($post->hashtags ?? [] as $hashtag) {
                $hashtag = strtolower($hashtag);
                $hashtagCounts[$hashtag] = ($hashtagCounts[$hashtag] ?? 0) + 1;
            }
        }

        arsort($hashtagCounts);

        return array_slice($hashtagCounts, 0, $limit, true);
    }
}
