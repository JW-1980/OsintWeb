<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserActivity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserActivityController extends Controller
{
    /**
     * Get the public activity feed for the homepage.
     *
     * Shows recent user contributions (events created, sources verified, etc.)
     */
    public function publicFeed(Request $request): JsonResponse
    {
        $limit = min($request->integer('limit', 20), 50);
        $days = min($request->integer('days', 7), 30);

        $activities = UserActivity::with('user:id,name,avatar_url')
            ->publicFeed()
            ->recent($days)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(fn ($activity) => $this->transformActivity($activity));

        return response()->json([
            'data' => $activities,
            'meta' => [
                'limit' => $limit,
                'days' => $days,
            ],
        ]);
    }

    /**
     * Get activity statistics for the homepage.
     */
    public function stats(Request $request): JsonResponse
    {
        $today = now()->startOfDay();
        $thisWeek = now()->startOfWeek();
        $thisMonth = now()->startOfMonth();

        return response()->json([
            'data' => [
                'contributions_today' => UserActivity::publicFeed()->where('created_at', '>=', $today)->count(),
                'contributions_week' => UserActivity::publicFeed()->where('created_at', '>=', $thisWeek)->count(),
                'active_contributors_today' => UserActivity::publicFeed()
                    ->where('created_at', '>=', $today)
                    ->distinct('user_id')
                    ->count('user_id'),
                'events_created_week' => UserActivity::where('activity_type', UserActivity::TYPE_EVENT_CREATE)
                    ->where('created_at', '>=', $thisWeek)
                    ->count(),
                'events_verified_week' => UserActivity::where('activity_type', UserActivity::TYPE_EVENT_VERIFY)
                    ->where('created_at', '>=', $thisWeek)
                    ->count(),
                'sources_verified_week' => UserActivity::where('activity_type', UserActivity::TYPE_SOURCE_VERIFY)
                    ->where('created_at', '>=', $thisWeek)
                    ->count(),
            ],
        ]);
    }

    /**
     * Log a user activity from the frontend.
     */
    public function log(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'activity_type' => 'required|string|max:50',
            'description' => 'nullable|string|max:500',
            'properties' => 'nullable|array',
        ]);

        // Validate that the activity type is allowed
        $allowedTypes = [
            UserActivity::TYPE_PAGE_VIEW,
            UserActivity::TYPE_MAP_VIEW,
            UserActivity::TYPE_MAP_INTERACTION,
            UserActivity::TYPE_SEARCH,
            UserActivity::TYPE_FILTER_APPLY,
            UserActivity::TYPE_SHARE,
            UserActivity::TYPE_CONFLICT_VIEW,
            UserActivity::TYPE_ACTOR_VIEW,
            UserActivity::TYPE_EQUIPMENT_VIEW,
            UserActivity::TYPE_ZONE_VIEW,
            UserActivity::TYPE_SOURCE_VIEW,
            UserActivity::TYPE_TIMELINE_VIEW,
            UserActivity::TYPE_REPORT_VIEW,
        ];

        if (!in_array($validated['activity_type'], $allowedTypes)) {
            return response()->json([
                'message' => 'Invalid activity type',
            ], 422);
        }

        $activity = UserActivity::log(
            user: $user,
            type: $validated['activity_type'],
            description: $validated['description'] ?? null,
            properties: $validated['properties'] ?? null,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return response()->json([
            'data' => $this->transformActivity($activity),
        ], 201);
    }

    /**
     * Batch log multiple activities (for efficiency).
     */
    public function logBatch(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'activities' => 'required|array|max:20',
            'activities.*.activity_type' => 'required|string|max:50',
            'activities.*.description' => 'nullable|string|max:500',
            'activities.*.properties' => 'nullable|array',
            'activities.*.timestamp' => 'nullable|date',
        ]);

        $logged = [];
        $ipAddress = $request->ip();
        $userAgent = $request->userAgent();

        foreach ($validated['activities'] as $activityData) {
            $activity = UserActivity::create([
                'user_id' => $user->id,
                'activity_type' => $activityData['activity_type'],
                'description' => $activityData['description'] ?? null,
                'properties' => $activityData['properties'] ?? null,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent ? substr($userAgent, 0, 500) : null,
                'created_at' => $activityData['timestamp'] ?? now(),
            ]);
            $logged[] = $this->transformActivity($activity);
        }

        return response()->json([
            'data' => $logged,
            'logged_count' => count($logged),
        ], 201);
    }

    /**
     * Get current user's activity history.
     */
    public function myActivity(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $perPage = min($request->integer('per_page', 25), 100);

        $activities = UserActivity::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        $activities->getCollection()->transform(fn ($activity) => $this->transformActivity($activity));

        return response()->json($activities);
    }

    /**
     * Get available activity types.
     */
    public function types(): JsonResponse
    {
        $types = [
            'page_views' => [
                UserActivity::TYPE_PAGE_VIEW => 'Page View',
                UserActivity::TYPE_MAP_VIEW => 'Map View',
                UserActivity::TYPE_MAP_INTERACTION => 'Map Interaction',
            ],
            'searches' => [
                UserActivity::TYPE_SEARCH => 'Search',
                UserActivity::TYPE_FILTER_APPLY => 'Filter Apply',
            ],
            'content' => [
                UserActivity::TYPE_EVENT_VIEW => 'Event View',
                UserActivity::TYPE_CONFLICT_VIEW => 'Conflict View',
                UserActivity::TYPE_ACTOR_VIEW => 'Actor View',
                UserActivity::TYPE_EQUIPMENT_VIEW => 'Equipment View',
                UserActivity::TYPE_ZONE_VIEW => 'Zone View',
                UserActivity::TYPE_SOURCE_VIEW => 'Source View',
                UserActivity::TYPE_ARTICLE_VIEW => 'Article View',
                UserActivity::TYPE_TIMELINE_VIEW => 'Timeline View',
                UserActivity::TYPE_REPORT_VIEW => 'Report View',
            ],
            'contributions' => [
                UserActivity::TYPE_EVENT_CREATE => 'Event Created',
                UserActivity::TYPE_EVENT_EDIT => 'Event Edited',
                UserActivity::TYPE_EVENT_VERIFY => 'Event Verified',
                UserActivity::TYPE_EVENT_DISPUTE => 'Event Disputed',
                UserActivity::TYPE_SOURCE_CREATE => 'Source Created',
                UserActivity::TYPE_SOURCE_VERIFY => 'Source Verified',
                UserActivity::TYPE_EVIDENCE_SUBMIT => 'Evidence Submitted',
                UserActivity::TYPE_TIP_SUBMIT => 'Tip Submitted',
                UserActivity::TYPE_COMMENT_CREATE => 'Comment Created',
            ],
            'social' => [
                UserActivity::TYPE_SHARE => 'Share',
                UserActivity::TYPE_FOLLOW_USER => 'Follow User',
                UserActivity::TYPE_LIKE => 'Like',
            ],
        ];

        return response()->json(['data' => $types]);
    }

    /**
     * Transform activity for API response.
     */
    private function transformActivity(UserActivity $activity): array
    {
        return [
            'id' => $activity->id,
            'user' => $activity->user ? [
                'id' => $activity->user->id,
                'name' => $activity->user->name,
                'avatar_url' => $activity->user->avatar_url,
            ] : null,
            'activity_type' => $activity->activity_type,
            'description' => $activity->getReadableDescription(),
            'icon' => $activity->getIcon(),
            'properties' => $activity->properties,
            'created_at' => $activity->created_at->toIso8601String(),
            'time_ago' => $activity->created_at->diffForHumans(),
        ];
    }
}
