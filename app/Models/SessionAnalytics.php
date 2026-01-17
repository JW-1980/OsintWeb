<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * SessionAnalytics Model
 *
 * Stores daily aggregated session metrics.
 * No individual session tracking - only aggregate statistics.
 *
 * Database Schema:
 * - date: The date of the statistics
 * - total_sessions: Total anonymous sessions
 * - new_sessions: First-time visitors
 * - returning_sessions: Returning visitors
 * - bounced_sessions: Single-page visits
 * - avg_duration_seconds: Average session length
 * - avg_pages_per_session: Average pages viewed
 * - Device/browser/country breakdowns stored as JSON
 */
class SessionAnalytics extends Model
{
    protected $table = 'session_analytics';

    protected $fillable = [
        'date',
        'total_sessions',
        'new_sessions',
        'returning_sessions',
        'bounced_sessions',
        'avg_duration_seconds',
        'avg_pages_per_session',
        'desktop_sessions',
        'mobile_sessions',
        'tablet_sessions',
        'countries',
        'referrers',
        'browsers',
    ];

    protected $casts = [
        'date' => 'date',
        'total_sessions' => 'integer',
        'new_sessions' => 'integer',
        'returning_sessions' => 'integer',
        'bounced_sessions' => 'integer',
        'avg_duration_seconds' => 'integer',
        'avg_pages_per_session' => 'integer',
        'desktop_sessions' => 'integer',
        'mobile_sessions' => 'integer',
        'tablet_sessions' => 'integer',
        'countries' => 'array',
        'referrers' => 'array',
        'browsers' => 'array',
    ];

    /**
     * Get or create today's analytics record.
     */
    public static function getOrCreateToday(): self
    {
        return self::firstOrCreate(
            ['date' => now()->toDateString()],
            [
                'total_sessions' => 0,
                'new_sessions' => 0,
                'returning_sessions' => 0,
                'bounced_sessions' => 0,
                'avg_duration_seconds' => 0,
                'avg_pages_per_session' => 0,
                'desktop_sessions' => 0,
                'mobile_sessions' => 0,
                'tablet_sessions' => 0,
                'countries' => [],
                'referrers' => [],
                'browsers' => [],
            ]
        );
    }

    /**
     * Record a new session.
     */
    public static function recordSession(
        bool $isNew,
        string $deviceType,
        ?string $countryCode = null,
        ?string $referrer = null,
        ?string $browser = null
    ): void {
        $analytics = self::getOrCreateToday();

        $analytics->increment('total_sessions');
        $analytics->increment($isNew ? 'new_sessions' : 'returning_sessions');

        // Device type
        $deviceColumn = match ($deviceType) {
            'mobile' => 'mobile_sessions',
            'tablet' => 'tablet_sessions',
            default => 'desktop_sessions',
        };
        $analytics->increment($deviceColumn);

        // Update country breakdown
        if ($countryCode) {
            $countries = $analytics->countries ?? [];
            $countries[$countryCode] = ($countries[$countryCode] ?? 0) + 1;
            arsort($countries);
            $analytics->countries = array_slice($countries, 0, 20, true);
        }

        // Update referrer breakdown
        if ($referrer) {
            $referrers = $analytics->referrers ?? [];
            $referrers[$referrer] = ($referrers[$referrer] ?? 0) + 1;
            arsort($referrers);
            $analytics->referrers = array_slice($referrers, 0, 20, true);
        }

        // Update browser breakdown
        if ($browser) {
            $browsers = $analytics->browsers ?? [];
            $browsers[$browser] = ($browsers[$browser] ?? 0) + 1;
            arsort($browsers);
            $analytics->browsers = array_slice($browsers, 0, 10, true);
        }

        $analytics->save();
    }

    /**
     * Record a bounced session.
     */
    public static function recordBounce(): void
    {
        self::getOrCreateToday()->increment('bounced_sessions');
    }

    /**
     * Update session duration averages.
     */
    public static function updateDurationStats(int $durationSeconds, int $pagesViewed): void
    {
        $analytics = self::getOrCreateToday();

        // Simple moving average approximation
        $totalSessions = max($analytics->total_sessions, 1);
        $analytics->avg_duration_seconds = (int) round(
            (($analytics->avg_duration_seconds * ($totalSessions - 1)) + $durationSeconds) / $totalSessions
        );
        $analytics->avg_pages_per_session = (int) round(
            (($analytics->avg_pages_per_session * ($totalSessions - 1)) + $pagesViewed) / $totalSessions
        );
        $analytics->save();
    }

    /**
     * Get summary for date range.
     */
    public static function getSummary(string $from, string $to): array
    {
        $stats = self::whereBetween('date', [$from, $to])->get();

        if ($stats->isEmpty()) {
            return [
                'total_sessions' => 0,
                'new_sessions' => 0,
                'returning_sessions' => 0,
                'bounced_sessions' => 0,
                'bounce_rate' => 0,
                'avg_duration' => 0,
                'avg_pages' => 0,
                'devices' => ['desktop' => 0, 'mobile' => 0, 'tablet' => 0],
            ];
        }

        $totalSessions = $stats->sum('total_sessions');

        return [
            'total_sessions' => $totalSessions,
            'new_sessions' => $stats->sum('new_sessions'),
            'returning_sessions' => $stats->sum('returning_sessions'),
            'bounced_sessions' => $stats->sum('bounced_sessions'),
            'bounce_rate' => $totalSessions > 0
                ? round(($stats->sum('bounced_sessions') / $totalSessions) * 100, 1)
                : 0,
            'avg_duration' => (int) round($stats->avg('avg_duration_seconds')),
            'avg_pages' => round($stats->avg('avg_pages_per_session'), 1),
            'devices' => [
                'desktop' => $stats->sum('desktop_sessions'),
                'mobile' => $stats->sum('mobile_sessions'),
                'tablet' => $stats->sum('tablet_sessions'),
            ],
        ];
    }

    /**
     * Get daily trend data.
     */
    public static function getDailyTrend(string $from, string $to): \Illuminate\Database\Eloquent\Collection
    {
        return self::whereBetween('date', [$from, $to])
            ->orderBy('date')
            ->get(['date', 'total_sessions', 'new_sessions', 'bounced_sessions', 'avg_duration_seconds']);
    }
}
