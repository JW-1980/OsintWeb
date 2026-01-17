<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\PageViewStat;
use App\Models\FeatureUsageStat;
use App\Models\SessionAnalytics;
use App\Models\SearchAnalytics;
use App\Models\UserFlowStat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;

/**
 * AnalyticsService
 *
 * Collects and aggregates anonymous site usage data.
 * All data is privacy-compliant with no personal information stored.
 */
class AnalyticsService
{
    private const SESSION_COOKIE = 'analytics_session';
    private const VISITOR_COOKIE = 'analytics_visitor';
    private const SESSION_TTL = 30; // minutes
    private const VISITOR_TTL = 365 * 24 * 60; // 1 year in minutes

    /**
     * Track a page view.
     */
    public function trackPageView(Request $request, string $pagePath, ?string $pageGroup = null): void
    {
        $sessionId = $this->getOrCreateSession($request);
        $visitorId = $this->getOrCreateVisitor($request);
        $isNewSession = $this->isNewSession($sessionId);
        $isNewVisitor = $this->isNewVisitor($visitorId);

        // Track page view
        PageViewStat::increment(
            pagePath: $pagePath,
            pageGroup: $pageGroup ?? $this->inferPageGroup($pagePath),
            deviceType: $this->detectDeviceType($request),
            countryCode: $this->detectCountry($request),
            referrerDomain: $this->extractReferrerDomain($request),
            isUniqueVisitor: $isNewSession
        );

        // Track user flow
        $previousPage = $this->getPreviousPage($sessionId);
        if ($previousPage && $previousPage !== $pagePath) {
            UserFlowStat::trackTransition($previousPage, $pagePath);
        } elseif (!$previousPage) {
            UserFlowStat::trackTransition('__ENTRY__', $pagePath);
        }
        $this->setPreviousPage($sessionId, $pagePath);

        // Update session analytics
        if ($isNewSession) {
            SessionAnalytics::recordSession(
                isNew: $isNewVisitor,
                deviceType: $this->detectDeviceType($request),
                countryCode: $this->detectCountry($request),
                referrer: $this->extractReferrerDomain($request),
                browser: $this->detectBrowser($request)
            );
        }
    }

    /**
     * Track feature usage.
     */
    public function trackFeature(
        Request $request,
        string $featureName,
        string $category,
        string $action = FeatureUsageStat::ACTION_CLICK,
        ?array $metadata = null
    ): void {
        $sessionId = $this->getOrCreateSession($request);
        $isUnique = $this->isFirstFeatureUse($sessionId, $featureName);

        FeatureUsageStat::track(
            featureName: $featureName,
            category: $category,
            action: $action,
            metadata: $metadata,
            isUniqueUser: $isUnique
        );

        $this->markFeatureUsed($sessionId, $featureName);
    }

    /**
     * Track a search.
     */
    public function trackSearch(
        Request $request,
        string $searchTerm,
        string $searchType = SearchAnalytics::TYPE_GLOBAL,
        int $resultCount = 0
    ): void {
        SearchAnalytics::track(
            searchTerm: $searchTerm,
            searchType: $searchType,
            resultCount: $resultCount
        );
    }

    /**
     * Track search result click.
     */
    public function trackSearchClick(string $searchTerm, string $searchType = SearchAnalytics::TYPE_GLOBAL): void
    {
        SearchAnalytics::track(
            searchTerm: $searchTerm,
            searchType: $searchType,
            resultCount: 1,
            clicked: true
        );
    }

    /**
     * Track session end (bounce detection).
     */
    public function trackSessionEnd(Request $request, int $pagesViewed, int $durationSeconds): void
    {
        if ($pagesViewed === 1) {
            SessionAnalytics::recordBounce();
        }

        if ($durationSeconds > 0 && $pagesViewed > 0) {
            SessionAnalytics::updateDurationStats($durationSeconds, $pagesViewed);
        }

        // Track exit
        $sessionId = $request->cookie(self::SESSION_COOKIE);
        if ($sessionId) {
            $lastPage = $this->getPreviousPage($sessionId);
            if ($lastPage) {
                UserFlowStat::trackExit($lastPage);
            }
        }
    }

    /**
     * Get analytics dashboard summary.
     */
    public function getDashboardSummary(string $from, string $to): array
    {
        $cacheKey = "analytics_summary_{$from}_{$to}";

        return Cache::remember($cacheKey, 300, function () use ($from, $to) {
            return [
                'page_views' => $this->getPageViewSummary($from, $to),
                'sessions' => SessionAnalytics::getSummary($from, $to),
                'features' => $this->getFeatureSummary($from, $to),
                'searches' => $this->getSearchSummary($from, $to),
            ];
        });
    }

    /**
     * Get page view summary.
     */
    public function getPageViewSummary(string $from, string $to): array
    {
        $dailyTotals = PageViewStat::getDailyTotals($from, $to);

        return [
            'total_views' => $dailyTotals->sum('total_views'),
            'total_unique' => $dailyTotals->sum('total_unique'),
            'daily' => $dailyTotals,
            'top_pages' => PageViewStat::getTopPages($from, $to, 10),
            'devices' => PageViewStat::getDeviceBreakdown($from, $to),
            'countries' => PageViewStat::getCountryBreakdown($from, $to, 10),
            'hourly' => PageViewStat::getHourlyDistribution($from, $to),
        ];
    }

    /**
     * Get feature usage summary.
     */
    public function getFeatureSummary(string $from, string $to): array
    {
        return [
            'top_features' => FeatureUsageStat::getTopFeatures($from, $to, 15),
            'by_category' => FeatureUsageStat::getUsageByCategory($from, $to),
            'daily' => FeatureUsageStat::getDailyTotals($from, $to),
        ];
    }

    /**
     * Get search summary.
     */
    public function getSearchSummary(string $from, string $to): array
    {
        return [
            'top_searches' => SearchAnalytics::getTopSearches($from, $to, 20),
            'zero_results' => SearchAnalytics::getZeroResultSearches($from, $to, 10),
            'by_type' => SearchAnalytics::getVolumeByType($from, $to),
            'daily' => SearchAnalytics::getDailyVolume($from, $to),
            'trending' => SearchAnalytics::getTrendingSearches(7, 10),
        ];
    }

    /**
     * Get user flow data.
     */
    public function getUserFlow(string $from, string $to): array
    {
        return [
            'top_paths' => UserFlowStat::getTopPaths($from, $to, 30),
            'entry_pages' => UserFlowStat::getTopEntryPages($from, $to, 10),
            'exit_pages' => UserFlowStat::getTopExitPages($from, $to, 10),
            'flow_graph' => UserFlowStat::buildFlowGraph($from, $to),
        ];
    }

    /**
     * Get or create anonymous session ID.
     */
    private function getOrCreateSession(Request $request): string
    {
        $sessionId = $request->cookie(self::SESSION_COOKIE);

        if (!$sessionId) {
            $sessionId = bin2hex(random_bytes(16));
            Cookie::queue(self::SESSION_COOKIE, $sessionId, self::SESSION_TTL);
        }

        return $sessionId;
    }

    /**
     * Get or create anonymous visitor ID.
     */
    private function getOrCreateVisitor(Request $request): string
    {
        $visitorId = $request->cookie(self::VISITOR_COOKIE);

        if (!$visitorId) {
            $visitorId = bin2hex(random_bytes(16));
            Cookie::queue(self::VISITOR_COOKIE, $visitorId, self::VISITOR_TTL);
        }

        return $visitorId;
    }

    /**
     * Check if this is a new session.
     */
    private function isNewSession(string $sessionId): bool
    {
        $key = "analytics_session_{$sessionId}";
        if (Cache::has($key)) {
            return false;
        }

        Cache::put($key, true, now()->addMinutes(self::SESSION_TTL));
        return true;
    }

    /**
     * Check if this is a new visitor.
     */
    private function isNewVisitor(string $visitorId): bool
    {
        $key = "analytics_visitor_{$visitorId}";
        if (Cache::has($key)) {
            return false;
        }

        Cache::put($key, true, now()->addDays(30));
        return true;
    }

    /**
     * Get previous page in session.
     */
    private function getPreviousPage(string $sessionId): ?string
    {
        return Cache::get("analytics_prev_page_{$sessionId}");
    }

    /**
     * Set previous page in session.
     */
    private function setPreviousPage(string $sessionId, string $page): void
    {
        Cache::put("analytics_prev_page_{$sessionId}", $page, now()->addMinutes(self::SESSION_TTL));
    }

    /**
     * Check if feature was used in this session.
     */
    private function isFirstFeatureUse(string $sessionId, string $feature): bool
    {
        $key = "analytics_feature_{$sessionId}_{$feature}";
        if (Cache::has($key)) {
            return false;
        }
        return true;
    }

    /**
     * Mark feature as used in session.
     */
    private function markFeatureUsed(string $sessionId, string $feature): void
    {
        $key = "analytics_feature_{$sessionId}_{$feature}";
        Cache::put($key, true, now()->addMinutes(self::SESSION_TTL));
    }

    /**
     * Detect device type from user agent.
     */
    private function detectDeviceType(Request $request): string
    {
        $userAgent = $request->userAgent() ?? '';

        if (preg_match('/Mobile|Android|iPhone|iPad|iPod/i', $userAgent)) {
            if (preg_match('/iPad|Tablet/i', $userAgent)) {
                return 'tablet';
            }
            return 'mobile';
        }

        return 'desktop';
    }

    /**
     * Detect browser from user agent.
     */
    private function detectBrowser(Request $request): string
    {
        $userAgent = $request->userAgent() ?? '';

        if (preg_match('/Chrome\/[\d.]+/', $userAgent) && !preg_match('/Edg/', $userAgent)) {
            return 'Chrome';
        }
        if (preg_match('/Firefox\/[\d.]+/', $userAgent)) {
            return 'Firefox';
        }
        if (preg_match('/Safari\/[\d.]+/', $userAgent) && !preg_match('/Chrome/', $userAgent)) {
            return 'Safari';
        }
        if (preg_match('/Edg\/[\d.]+/', $userAgent)) {
            return 'Edge';
        }

        return 'Other';
    }

    /**
     * Detect country from request (simplified).
     */
    private function detectCountry(Request $request): ?string
    {
        // Check for CloudFlare header
        $country = $request->header('CF-IPCountry');
        if ($country && $country !== 'XX') {
            return strtoupper($country);
        }

        // Check for other common headers
        $country = $request->header('X-Country-Code');
        if ($country) {
            return strtoupper(substr($country, 0, 2));
        }

        return null;
    }

    /**
     * Extract referrer domain.
     */
    private function extractReferrerDomain(Request $request): ?string
    {
        $referrer = $request->header('Referer');
        if (!$referrer) {
            return null;
        }

        $parsed = parse_url($referrer);
        $host = $parsed['host'] ?? null;

        if (!$host) {
            return null;
        }

        // Skip internal referrers
        $appHost = parse_url(config('app.url'), PHP_URL_HOST);
        if ($host === $appHost) {
            return null;
        }

        return strtolower($host);
    }

    /**
     * Infer page group from path.
     */
    private function inferPageGroup(string $path): string
    {
        $path = trim($path, '/');
        $segments = explode('/', $path);
        $first = $segments[0] ?? '';

        return match ($first) {
            '', 'home' => 'home',
            'map' => 'map',
            'events' => 'events',
            'equipment' => 'equipment',
            'actors' => 'actors',
            'conflicts' => 'conflicts',
            'zones' => 'zones',
            'articles', 'news' => 'articles',
            'admin' => 'admin',
            'auth', 'login', 'register' => 'auth',
            'profile', 'settings', 'account' => 'account',
            default => 'other',
        };
    }
}
