<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SocialMediaAccount;
use App\Models\SocialMediaAlert;
use App\Models\SocialMediaPost;
use App\Services\SocialMediaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Social Media Controller
 *
 * Handles API endpoints for social media monitoring including:
 * - Account management (CRUD)
 * - Post listing and search
 * - Post archival
 * - Alert management
 * - Statistics
 */
class SocialMediaController extends Controller
{
    public function __construct(
        protected SocialMediaService $socialMediaService
    ) {}

    // =========================================================================
    // ACCOUNTS
    // =========================================================================

    /**
     * List all monitored social media accounts.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = SocialMediaAccount::query()
            ->with(['addedByUser:id,name']);

        // Filter by platform
        if ($request->has('platform')) {
            $query->platform($request->platform);
        }

        // Filter by monitoring status
        if ($request->has('is_monitored')) {
            $request->boolean('is_monitored')
                ? $query->monitored()
                : $query->where('is_monitored', false);
        }

        // Filter by priority
        if ($request->has('priority')) {
            $query->priority((int) $request->priority);
        }

        // Filter by verified status
        if ($request->has('is_verified')) {
            $request->boolean('is_verified')
                ? $query->verified()
                : $query->where('is_verified', false);
        }

        // Search
        if ($request->has('search')) {
            $query->search($request->search);
        }

        // Sort
        $sortField = $request->input('sort_by', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $accounts = $query->paginate($this->perPage);

        return $this->paginated($accounts);
    }

    /**
     * Add a new social media account to monitor.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'platform' => ['required', Rule::in(SocialMediaAccount::getPlatforms())],
            'username' => ['required', 'string', 'max:255'],
            'display_name' => ['nullable', 'string', 'max:255'],
            'profile_url' => ['nullable', 'url', 'max:2048'],
            'platform_user_id' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:5000'],
            'location' => ['nullable', 'string', 'max:255'],
            'is_verified' => ['nullable', 'boolean'],
            'is_monitored' => ['nullable', 'boolean'],
            'monitoring_priority' => ['nullable', 'integer', 'min:1', 'max:5'],
            'metadata' => ['nullable', 'array'],
        ]);

        try {
            $account = $this->socialMediaService->addAccount(
                $validated['platform'],
                $validated['username'],
                $request->user(),
                $validated
            );

            return $this->success($account->load('addedByUser:id,name'), 201);

        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    /**
     * Get a specific social media account.
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function show(string $uuid): JsonResponse
    {
        $account = SocialMediaAccount::where('uuid', $uuid)
            ->with(['addedByUser:id,name'])
            ->firstOrFail();

        return $this->success($account);
    }

    /**
     * Update a social media account.
     *
     * @param Request $request
     * @param string $uuid
     * @return JsonResponse
     */
    public function update(Request $request, string $uuid): JsonResponse
    {
        $account = SocialMediaAccount::where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'display_name' => ['nullable', 'string', 'max:255'],
            'profile_url' => ['nullable', 'url', 'max:2048'],
            'profile_image_url' => ['nullable', 'url', 'max:2048'],
            'bio' => ['nullable', 'string', 'max:5000'],
            'location' => ['nullable', 'string', 'max:255'],
            'is_verified' => ['nullable', 'boolean'],
            'is_monitored' => ['nullable', 'boolean'],
            'monitoring_priority' => ['nullable', 'integer', 'min:1', 'max:5'],
            'follower_count' => ['nullable', 'integer', 'min:0'],
            'following_count' => ['nullable', 'integer', 'min:0'],
            'post_count' => ['nullable', 'integer', 'min:0'],
            'metadata' => ['nullable', 'array'],
        ]);

        $account->update($validated);

        return $this->success($account->fresh('addedByUser:id,name'));
    }

    /**
     * Delete (soft) a social media account.
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function destroy(string $uuid): JsonResponse
    {
        $account = SocialMediaAccount::where('uuid', $uuid)->firstOrFail();

        $account->delete();

        return $this->success(['message' => 'Account removed from monitoring']);
    }

    /**
     * Toggle monitoring status for an account.
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function toggleMonitoring(string $uuid): JsonResponse
    {
        $account = SocialMediaAccount::where('uuid', $uuid)->firstOrFail();

        $newStatus = $account->toggleMonitoring();

        return $this->success([
            'is_monitored' => $newStatus,
            'message' => $newStatus ? 'Monitoring enabled' : 'Monitoring disabled',
        ]);
    }

    /**
     * Get statistics for a specific account.
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function accountStats(string $uuid): JsonResponse
    {
        $account = SocialMediaAccount::where('uuid', $uuid)->firstOrFail();

        $stats = $this->socialMediaService->getAccountStats($account->id);

        return $this->success($stats);
    }

    // =========================================================================
    // POSTS
    // =========================================================================

    /**
     * List posts for a specific account.
     *
     * @param Request $request
     * @param string $uuid Account UUID
     * @return JsonResponse
     */
    public function posts(Request $request, string $uuid): JsonResponse
    {
        $account = SocialMediaAccount::where('uuid', $uuid)->firstOrFail();

        $query = SocialMediaPost::where('account_id', $account->id);

        // Filter by post type
        if ($request->has('post_type')) {
            $query->ofType($request->post_type);
        }

        // Filter by archived status
        if ($request->has('is_archived')) {
            $request->boolean('is_archived')
                ? $query->archived()
                : $query->notArchived();
        }

        // Filter by date range
        if ($request->has('from_date')) {
            $query->where('posted_at', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->where('posted_at', '<=', $request->to_date);
        }

        // Search content
        if ($request->has('search')) {
            $query->search($request->search);
        }

        // Sort
        $sortField = $request->input('sort_by', 'posted_at');
        $sortDirection = $request->input('sort_direction', 'desc');

        if ($sortField === 'engagement') {
            $query->orderByEngagement($sortDirection);
        } else {
            $query->orderBy($sortField, $sortDirection);
        }

        $posts = $query->paginate($this->perPage);

        return $this->paginated($posts);
    }

    /**
     * Get a specific post.
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function showPost(string $uuid): JsonResponse
    {
        $post = SocialMediaPost::where('uuid', $uuid)
            ->with(['account:id,uuid,platform,username,display_name', 'event:id,uuid,title'])
            ->firstOrFail();

        return $this->success($post);
    }

    /**
     * Archive a post (preserve locally).
     *
     * @param Request $request
     * @param string $uuid
     * @return JsonResponse
     */
    public function archive(Request $request, string $uuid): JsonResponse
    {
        $post = SocialMediaPost::where('uuid', $uuid)->firstOrFail();

        $options = [
            'archive_media' => $request->boolean('archive_media', true),
            'force' => $request->boolean('force', false),
        ];

        try {
            $post = $this->socialMediaService->archivePost($post->id, $options);

            return $this->success([
                'post' => $post,
                'message' => 'Post archived successfully',
            ]);

        } catch (\Exception $e) {
            return $this->error('Failed to archive post: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Search posts across all accounts.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:500'],
            'platform' => ['nullable', Rule::in(SocialMediaAccount::getPlatforms())],
            'account_id' => ['nullable', 'exists:social_media_accounts,id'],
            'post_type' => ['nullable', Rule::in(SocialMediaPost::getPostTypes())],
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
            'language' => ['nullable', 'string', 'max:10'],
            'sentiment' => ['nullable', Rule::in(['positive', 'negative', 'neutral'])],
            'has_media' => ['nullable', 'boolean'],
            'has_geolocation' => ['nullable', 'boolean'],
            'is_archived' => ['nullable', 'boolean'],
            'has_event' => ['nullable', 'boolean'],
            'hashtag' => ['nullable', 'string', 'max:100'],
            'sort_by' => ['nullable', Rule::in(['posted_at', 'engagement', 'created_at'])],
            'sort_direction' => ['nullable', Rule::in(['asc', 'desc'])],
        ]);

        $posts = $this->socialMediaService->searchPosts(
            $validated['q'] ?? '',
            $validated,
            $this->perPage
        );

        return $this->paginated($posts);
    }

    /**
     * Link a post to an event.
     *
     * @param Request $request
     * @param string $uuid Post UUID
     * @return JsonResponse
     */
    public function linkToEvent(Request $request, string $uuid): JsonResponse
    {
        $post = SocialMediaPost::where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'event_id' => ['required', 'exists:events,id'],
        ]);

        $post = $this->socialMediaService->linkToEvent($post->id, $validated['event_id']);

        return $this->success([
            'post' => $post->load('event:id,uuid,title'),
            'message' => 'Post linked to event',
        ]);
    }

    /**
     * Unlink a post from an event.
     *
     * @param string $uuid Post UUID
     * @return JsonResponse
     */
    public function unlinkFromEvent(string $uuid): JsonResponse
    {
        $post = SocialMediaPost::where('uuid', $uuid)->firstOrFail();

        $post = $this->socialMediaService->unlinkFromEvent($post->id);

        return $this->success([
            'post' => $post,
            'message' => 'Post unlinked from event',
        ]);
    }

    // =========================================================================
    // ALERTS
    // =========================================================================

    /**
     * List alerts for the current user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function alerts(Request $request): JsonResponse
    {
        $query = SocialMediaAlert::forUser($request->user()->id)
            ->with(['account:id,uuid,platform,username']);

        // Filter by type
        if ($request->has('alert_type')) {
            $query->ofType($request->alert_type);
        }

        // Filter by active status
        if ($request->has('is_active')) {
            $request->boolean('is_active')
                ? $query->active()
                : $query->where('is_active', false);
        }

        // Filter by account
        if ($request->has('account_id')) {
            $query->forAccount($request->account_id);
        }

        $alerts = $query->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        return $this->paginated($alerts);
    }

    /**
     * Create a new alert.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createAlert(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'alert_type' => ['required', Rule::in(SocialMediaAlert::getAlertTypes())],
            'account_id' => ['nullable', 'exists:social_media_accounts,id'],
            'keyword' => ['nullable', 'required_if:alert_type,keyword_match', 'string', 'max:500'],
            'notification_channels' => ['nullable', 'array'],
            'notification_channels.*' => [Rule::in(SocialMediaAlert::getNotificationChannels())],
            'filters' => ['nullable', 'array'],
            'filters.platforms' => ['nullable', 'array'],
            'filters.platforms.*' => [Rule::in(SocialMediaAccount::getPlatforms())],
            'filters.languages' => ['nullable', 'array'],
            'filters.post_types' => ['nullable', 'array'],
            'filters.post_types.*' => [Rule::in(SocialMediaPost::getPostTypes())],
            'is_active' => ['nullable', 'boolean'],
            'cooldown_minutes' => ['nullable', 'integer', 'min:1', 'max:1440'],
        ]);

        $alert = $this->socialMediaService->createAlert($request->user(), $validated);

        return $this->success($alert->load('account:id,uuid,platform,username'), 201);
    }

    /**
     * Get a specific alert.
     *
     * @param Request $request
     * @param string $uuid
     * @return JsonResponse
     */
    public function showAlert(Request $request, string $uuid): JsonResponse
    {
        $alert = SocialMediaAlert::where('uuid', $uuid)
            ->forUser($request->user()->id)
            ->with(['account:id,uuid,platform,username'])
            ->firstOrFail();

        return $this->success($alert);
    }

    /**
     * Update an alert.
     *
     * @param Request $request
     * @param string $uuid
     * @return JsonResponse
     */
    public function updateAlert(Request $request, string $uuid): JsonResponse
    {
        $alert = SocialMediaAlert::where('uuid', $uuid)
            ->forUser($request->user()->id)
            ->firstOrFail();

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'keyword' => ['nullable', 'string', 'max:500'],
            'notification_channels' => ['nullable', 'array'],
            'notification_channels.*' => [Rule::in(SocialMediaAlert::getNotificationChannels())],
            'filters' => ['nullable', 'array'],
            'is_active' => ['nullable', 'boolean'],
            'cooldown_minutes' => ['nullable', 'integer', 'min:1', 'max:1440'],
        ]);

        $alert->update($validated);

        return $this->success($alert->fresh('account:id,uuid,platform,username'));
    }

    /**
     * Delete an alert.
     *
     * @param Request $request
     * @param string $uuid
     * @return JsonResponse
     */
    public function deleteAlert(Request $request, string $uuid): JsonResponse
    {
        $alert = SocialMediaAlert::where('uuid', $uuid)
            ->forUser($request->user()->id)
            ->firstOrFail();

        $alert->delete();

        return $this->success(['message' => 'Alert deleted']);
    }

    /**
     * Toggle alert active status.
     *
     * @param Request $request
     * @param string $uuid
     * @return JsonResponse
     */
    public function toggleAlert(Request $request, string $uuid): JsonResponse
    {
        $alert = SocialMediaAlert::where('uuid', $uuid)
            ->forUser($request->user()->id)
            ->firstOrFail();

        $newStatus = $alert->toggle();

        return $this->success([
            'is_active' => $newStatus,
            'message' => $newStatus ? 'Alert activated' : 'Alert deactivated',
        ]);
    }

    /**
     * Get trigger history for an alert.
     *
     * @param Request $request
     * @param string $uuid
     * @return JsonResponse
     */
    public function alertTriggers(Request $request, string $uuid): JsonResponse
    {
        $alert = SocialMediaAlert::where('uuid', $uuid)
            ->forUser($request->user()->id)
            ->firstOrFail();

        $triggers = $alert->triggeredPosts()
            ->with(['account:id,uuid,platform,username'])
            ->paginate($this->perPage);

        return $this->paginated($triggers);
    }

    // =========================================================================
    // UTILITY ENDPOINTS
    // =========================================================================

    /**
     * Get list of supported platforms.
     *
     * @return JsonResponse
     */
    public function platforms(): JsonResponse
    {
        $platforms = [];

        foreach (SocialMediaAccount::getPlatforms() as $platform) {
            $platforms[] = [
                'id' => $platform,
                'name' => SocialMediaAccount::getPlatformLabels()[$platform] ?? ucfirst($platform),
            ];
        }

        return $this->success($platforms);
    }

    /**
     * Get list of post types.
     *
     * @return JsonResponse
     */
    public function postTypes(): JsonResponse
    {
        $types = [];

        foreach (SocialMediaPost::getPostTypes() as $type) {
            $types[] = [
                'id' => $type,
                'name' => SocialMediaPost::getPostTypeLabels()[$type] ?? ucfirst($type),
            ];
        }

        return $this->success($types);
    }

    /**
     * Get list of alert types.
     *
     * @return JsonResponse
     */
    public function alertTypes(): JsonResponse
    {
        $types = [];

        foreach (SocialMediaAlert::getAlertTypes() as $type) {
            $types[] = [
                'id' => $type,
                'name' => SocialMediaAlert::getAlertTypeLabels()[$type] ?? ucfirst($type),
            ];
        }

        return $this->success($types);
    }

    /**
     * Get overall statistics.
     *
     * @return JsonResponse
     */
    public function stats(): JsonResponse
    {
        $stats = $this->socialMediaService->getOverallStats();

        return $this->success($stats);
    }

    /**
     * Get priority levels.
     *
     * @return JsonResponse
     */
    public function priorities(): JsonResponse
    {
        $priorities = [];

        foreach (SocialMediaAccount::getPriorityLevels() as $level => $name) {
            $priorities[] = [
                'level' => $level,
                'name' => $name,
            ];
        }

        return $this->success($priorities);
    }

    /**
     * Fetch latest posts for an account (manual trigger).
     *
     * @param string $uuid Account UUID
     * @return JsonResponse
     */
    public function fetchPosts(string $uuid): JsonResponse
    {
        $account = SocialMediaAccount::where('uuid', $uuid)->firstOrFail();

        if (!$account->is_monitored) {
            return $this->error('Account is not being monitored', 400);
        }

        $posts = $this->socialMediaService->fetchLatestPosts($account->id);

        return $this->success([
            'account' => $account->only(['uuid', 'platform', 'username', 'last_checked_at']),
            'posts_count' => $posts->count(),
            'message' => 'Fetch completed',
        ]);
    }

    /**
     * Detect keywords in a post.
     *
     * @param Request $request
     * @param string $uuid Post UUID
     * @return JsonResponse
     */
    public function detectKeywords(Request $request, string $uuid): JsonResponse
    {
        $post = SocialMediaPost::where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'keywords' => ['required', 'array', 'min:1'],
            'keywords.*' => ['string', 'max:100'],
        ]);

        $matches = $this->socialMediaService->detectKeywords(
            $post->id,
            $validated['keywords']
        );

        return $this->success([
            'post_uuid' => $post->uuid,
            'keywords_checked' => $validated['keywords'],
            'matches' => $matches,
            'has_matches' => !empty($matches),
        ]);
    }
}
