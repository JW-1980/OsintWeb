<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use App\Models\PageViewStat;
use App\Models\FeatureUsageStat;
use App\Models\SessionAnalytics;
use App\Models\SearchAnalytics;
use App\Models\UserFlowStat;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SiteAnalyticsController extends Controller
{
    public function __construct(
        private readonly AnalyticsService $analyticsService
    ) {}

    /**
     * Get analytics dashboard summary.
     */
    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user->isAdmin() && !$user->hasPermission('analytics.view')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $from = $request->input('from', now()->subDays(30)->toDateString());
        $to = $request->input('to', now()->toDateString());

        $summary = $this->analyticsService->getDashboardSummary($from, $to);

        return response()->json([
            'data' => $summary,
            'period' => ['from' => $from, 'to' => $to],
        ]);
    }

    /**
     * Get page view statistics.
     */
    public function pageViews(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user->isAdmin() && !$user->hasPermission('analytics.view')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $from = $request->input('from', now()->subDays(30)->toDateString());
        $to = $request->input('to', now()->toDateString());

        return response()->json([
            'data' => [
                'daily' => PageViewStat::getDailyTotals($from, $to),
                'top_pages' => PageViewStat::getTopPages($from, $to, 30),
                'hourly' => PageViewStat::getHourlyDistribution($from, $to),
                'devices' => PageViewStat::getDeviceBreakdown($from, $to),
                'countries' => PageViewStat::getCountryBreakdown($from, $to, 20),
            ],
            'period' => ['from' => $from, 'to' => $to],
        ]);
    }

    /**
     * Get feature usage statistics.
     */
    public function featureUsage(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user->isAdmin() && !$user->hasPermission('analytics.view')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $from = $request->input('from', now()->subDays(30)->toDateString());
        $to = $request->input('to', now()->toDateString());

        // Calculate trend comparison
        $periodDays = now()->parse($from)->diffInDays(now()->parse($to));
        $previousFrom = now()->parse($from)->subDays($periodDays)->toDateString();
        $previousTo = now()->parse($from)->subDay()->toDateString();

        return response()->json([
            'data' => [
                'top_features' => FeatureUsageStat::getTopFeatures($from, $to, 30),
                'by_category' => FeatureUsageStat::getUsageByCategory($from, $to),
                'daily' => FeatureUsageStat::getDailyTotals($from, $to),
                'trends' => FeatureUsageStat::getFeatureTrends($from, $to, $previousFrom, $previousTo),
                'categories' => FeatureUsageStat::getCategories(),
            ],
            'period' => ['from' => $from, 'to' => $to],
        ]);
    }

    /**
     * Get session analytics.
     */
    public function sessions(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user->isAdmin() && !$user->hasPermission('analytics.view')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $from = $request->input('from', now()->subDays(30)->toDateString());
        $to = $request->input('to', now()->toDateString());

        return response()->json([
            'data' => [
                'summary' => SessionAnalytics::getSummary($from, $to),
                'daily' => SessionAnalytics::getDailyTrend($from, $to),
            ],
            'period' => ['from' => $from, 'to' => $to],
        ]);
    }

    /**
     * Get search analytics.
     */
    public function searchAnalytics(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user->isAdmin() && !$user->hasPermission('analytics.view')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $from = $request->input('from', now()->subDays(30)->toDateString());
        $to = $request->input('to', now()->toDateString());

        return response()->json([
            'data' => [
                'top_searches' => SearchAnalytics::getTopSearches($from, $to, 50),
                'zero_results' => SearchAnalytics::getZeroResultSearches($from, $to, 20),
                'by_type' => SearchAnalytics::getVolumeByType($from, $to),
                'daily' => SearchAnalytics::getDailyVolume($from, $to),
                'trending' => SearchAnalytics::getTrendingSearches(7, 15),
            ],
            'period' => ['from' => $from, 'to' => $to],
        ]);
    }

    /**
     * Get user flow analytics.
     */
    public function userFlows(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user->isAdmin() && !$user->hasPermission('analytics.view')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $from = $request->input('from', now()->subDays(30)->toDateString());
        $to = $request->input('to', now()->toDateString());

        return response()->json([
            'data' => $this->analyticsService->getUserFlow($from, $to),
            'period' => ['from' => $from, 'to' => $to],
        ]);
    }

    /**
     * Get flow for a specific page.
     */
    public function pageFlow(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user->isAdmin() && !$user->hasPermission('analytics.view')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'page' => 'required|string|max:255',
        ]);

        $from = $request->input('from', now()->subDays(30)->toDateString());
        $to = $request->input('to', now()->toDateString());
        $page = $request->input('page');

        return response()->json([
            'data' => [
                'from_page' => UserFlowStat::getFlowFromPage($page, $from, $to),
                'to_page' => UserFlowStat::getFlowToPage($page, $from, $to),
            ],
            'page' => $page,
            'period' => ['from' => $from, 'to' => $to],
        ]);
    }

    /**
     * Get real-time stats (last 24 hours with hourly breakdown).
     */
    public function realtime(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user->isAdmin() && !$user->hasPermission('analytics.view')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $now = now();
        $from = $now->copy()->subHours(24)->toDateString();
        $to = $now->toDateString();

        // Get hourly data for the last 24 hours
        $pageViews = PageViewStat::selectRaw('hour, SUM(view_count) as views')
            ->where('date', '>=', $from)
            ->groupBy('hour')
            ->orderBy('hour')
            ->pluck('views', 'hour');

        $sessions = SessionAnalytics::where('date', '>=', $from)->get();

        return response()->json([
            'data' => [
                'page_views_today' => PageViewStat::where('date', $to)->sum('view_count'),
                'sessions_today' => $sessions->sum('total_sessions'),
                'active_now' => $this->estimateActiveUsers(),
                'hourly_views' => $pageViews,
                'top_pages_now' => PageViewStat::getTopPages($to, $to, 10),
            ],
            'timestamp' => $now->toIso8601String(),
        ]);
    }

    /**
     * Compare two periods.
     */
    public function compare(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user->isAdmin() && !$user->hasPermission('analytics.view')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'period1_from' => 'required|date',
            'period1_to' => 'required|date',
            'period2_from' => 'required|date',
            'period2_to' => 'required|date',
        ]);

        $period1 = $this->analyticsService->getDashboardSummary(
            $request->input('period1_from'),
            $request->input('period1_to')
        );

        $period2 = $this->analyticsService->getDashboardSummary(
            $request->input('period2_from'),
            $request->input('period2_to')
        );

        return response()->json([
            'data' => [
                'period1' => $period1,
                'period2' => $period2,
                'comparison' => $this->calculateComparison($period1, $period2),
            ],
        ]);
    }

    /**
     * Export analytics data.
     */
    public function export(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user->isAdmin() && !$user->hasPermission('analytics.export')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $from = $request->input('from', now()->subDays(30)->toDateString());
        $to = $request->input('to', now()->toDateString());
        $type = $request->input('type', 'summary');

        $data = match ($type) {
            'page_views' => PageViewStat::dateRange($from, $to)->get(),
            'features' => FeatureUsageStat::dateRange($from, $to)->get(),
            'sessions' => SessionAnalytics::whereBetween('date', [$from, $to])->get(),
            'searches' => SearchAnalytics::dateRange($from, $to)->get(),
            default => $this->analyticsService->getDashboardSummary($from, $to),
        };

        return response()->json([
            'data' => $data,
            'type' => $type,
            'period' => ['from' => $from, 'to' => $to],
            'exported_at' => now()->toIso8601String(),
        ]);
    }

    /**
     * Get top pages.
     */
    public function topPages(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user->isAdmin() && !$user->hasPermission('analytics.view')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $from = $request->input('from', now()->subDays(30)->toDateString());
        $to = $request->input('to', now()->toDateString());
        $limit = $request->integer('limit', 20);

        $topPages = PageViewStat::getTopPages($from, $to, $limit);

        return response()->json([
            'data' => $topPages->map(fn ($page) => [
                'page_path' => $page->page_path,
                'page_group' => $page->page_group,
                'total_views' => $page->total_views,
                'unique_views' => $page->total_unique,
            ]),
            'period' => ['from' => $from, 'to' => $to],
        ]);
    }

    /**
     * Get hourly distribution.
     */
    public function hourlyDistribution(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user->isAdmin() && !$user->hasPermission('analytics.view')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $from = $request->input('from', now()->subDays(30)->toDateString());
        $to = $request->input('to', now()->toDateString());

        $hourly = PageViewStat::getHourlyDistribution($from, $to);

        return response()->json([
            'data' => $hourly->map(fn ($h) => [
                'hour' => $h->hour,
                'total_views' => $h->total_views,
            ]),
            'period' => ['from' => $from, 'to' => $to],
        ]);
    }

    /**
     * Get daily trends.
     */
    public function dailyTrends(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user->isAdmin() && !$user->hasPermission('analytics.view')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $from = $request->input('from', now()->subDays(30)->toDateString());
        $to = $request->input('to', now()->toDateString());

        $daily = PageViewStat::getDailyTotals($from, $to);

        return response()->json([
            'data' => $daily->map(fn ($d) => [
                'date' => $d->date->format('Y-m-d'),
                'total_views' => $d->total_views,
                'unique_views' => $d->total_unique,
            ]),
            'period' => ['from' => $from, 'to' => $to],
        ]);
    }

    /**
     * Get device statistics.
     */
    public function deviceStats(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user->isAdmin() && !$user->hasPermission('analytics.view')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $from = $request->input('from', now()->subDays(30)->toDateString());
        $to = $request->input('to', now()->toDateString());

        $devices = PageViewStat::getDeviceBreakdown($from, $to);
        $total = $devices->sum('total_views');

        return response()->json([
            'data' => $devices->map(fn ($d) => [
                'device_type' => $d->device_type ?? 'unknown',
                'total_views' => $d->total_views,
                'percentage' => $total > 0 ? round(($d->total_views / $total) * 100, 1) : 0,
            ]),
            'period' => ['from' => $from, 'to' => $to],
        ]);
    }

    /**
     * Estimate currently active users.
     */
    private function estimateActiveUsers(): int
    {
        // Count page views in the last 5 minutes as a proxy for active users
        $fiveMinutesAgo = now()->subMinutes(5);
        $currentHour = now()->hour;
        $today = now()->toDateString();

        $recentViews = PageViewStat::where('date', $today)
            ->where('hour', $currentHour)
            ->sum('unique_visitors');

        // Rough estimate: divide hourly unique visitors by 12 (5-minute windows)
        return (int) ceil($recentViews / 12);
    }

    /**
     * Calculate comparison metrics between two periods.
     */
    private function calculateComparison(array $period1, array $period2): array
    {
        $calcChange = function ($current, $previous) {
            if ($previous == 0) {
                return $current > 0 ? 100 : 0;
            }
            return round((($current - $previous) / $previous) * 100, 1);
        };

        return [
            'page_views_change' => $calcChange(
                $period1['page_views']['total_views'] ?? 0,
                $period2['page_views']['total_views'] ?? 0
            ),
            'sessions_change' => $calcChange(
                $period1['sessions']['total_sessions'] ?? 0,
                $period2['sessions']['total_sessions'] ?? 0
            ),
            'bounce_rate_change' => $calcChange(
                $period1['sessions']['bounce_rate'] ?? 0,
                $period2['sessions']['bounce_rate'] ?? 0
            ),
            'avg_duration_change' => $calcChange(
                $period1['sessions']['avg_duration'] ?? 0,
                $period2['sessions']['avg_duration'] ?? 0
            ),
        ];
    }
}
