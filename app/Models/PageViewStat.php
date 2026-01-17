<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * PageViewStat Model
 *
 * Stores aggregated anonymous page view statistics in hourly buckets.
 * No individual user tracking - fully privacy-compliant.
 *
 * Database Schema:
 * - page_path: The URL path being tracked
 * - page_group: Category grouping (map, events, equipment, etc.)
 * - date: The date of the statistics
 * - hour: Hour of day (0-23)
 * - view_count: Total page views in this bucket
 * - unique_visitors: Unique anonymous session hashes
 * - referrer_domain: Source domain (if external)
 * - device_type: desktop, mobile, tablet
 * - country_code: ISO 2-letter country code
 */
class PageViewStat extends Model
{
    protected $table = 'page_view_stats';

    protected $fillable = [
        'page_path',
        'page_group',
        'date',
        'hour',
        'view_count',
        'unique_visitors',
        'referrer_domain',
        'device_type',
        'country_code',
    ];

    protected $casts = [
        'date' => 'date',
        'hour' => 'integer',
        'view_count' => 'integer',
        'unique_visitors' => 'integer',
    ];

    /**
     * Increment page view count for a specific bucket.
     */
    public static function increment(
        string $pagePath,
        ?string $pageGroup = null,
        ?string $deviceType = null,
        ?string $countryCode = null,
        ?string $referrerDomain = null,
        bool $isUniqueVisitor = false
    ): void {
        $now = now();
        $date = $now->toDateString();
        $hour = $now->hour;

        $stat = self::firstOrCreate([
            'page_path' => substr($pagePath, 0, 500),
            'date' => $date,
            'hour' => $hour,
            'device_type' => $deviceType,
            'country_code' => $countryCode,
        ], [
            'page_group' => $pageGroup,
            'referrer_domain' => $referrerDomain ? substr($referrerDomain, 0, 255) : null,
            'view_count' => 0,
            'unique_visitors' => 0,
        ]);

        $stat->increment('view_count');
        if ($isUniqueVisitor) {
            $stat->increment('unique_visitors');
        }
    }

    /**
     * Scope for filtering by date range.
     */
    public function scopeDateRange(Builder $query, string $from, string $to): Builder
    {
        return $query->whereBetween('date', [$from, $to]);
    }

    /**
     * Scope for filtering by page group.
     */
    public function scopePageGroup(Builder $query, string $group): Builder
    {
        return $query->where('page_group', $group);
    }

    /**
     * Get top pages by view count.
     */
    public static function getTopPages(string $from, string $to, int $limit = 20): \Illuminate\Support\Collection
    {
        return self::selectRaw('page_path, page_group, SUM(view_count) as total_views, SUM(unique_visitors) as total_unique')
            ->dateRange($from, $to)
            ->groupBy('page_path', 'page_group')
            ->orderByDesc('total_views')
            ->limit($limit)
            ->get();
    }

    /**
     * Get daily view totals.
     */
    public static function getDailyTotals(string $from, string $to): \Illuminate\Support\Collection
    {
        return self::selectRaw('date, SUM(view_count) as total_views, SUM(unique_visitors) as total_unique')
            ->dateRange($from, $to)
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    /**
     * Get hourly distribution.
     */
    public static function getHourlyDistribution(string $from, string $to): \Illuminate\Support\Collection
    {
        return self::selectRaw('hour, SUM(view_count) as total_views')
            ->dateRange($from, $to)
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();
    }

    /**
     * Get device breakdown.
     */
    public static function getDeviceBreakdown(string $from, string $to): \Illuminate\Support\Collection
    {
        return self::selectRaw('device_type, SUM(view_count) as total_views')
            ->dateRange($from, $to)
            ->whereNotNull('device_type')
            ->groupBy('device_type')
            ->get();
    }

    /**
     * Get country breakdown.
     */
    public static function getCountryBreakdown(string $from, string $to, int $limit = 10): \Illuminate\Support\Collection
    {
        return self::selectRaw('country_code, SUM(view_count) as total_views')
            ->dateRange($from, $to)
            ->whereNotNull('country_code')
            ->groupBy('country_code')
            ->orderByDesc('total_views')
            ->limit($limit)
            ->get();
    }
}
