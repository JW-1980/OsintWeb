<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * SearchAnalytics Model
 *
 * Tracks search behavior to understand what users are looking for.
 * Helps identify content gaps and popular topics.
 *
 * Database Schema:
 * - search_term: The search query (normalized)
 * - search_type: Category of search (events, equipment, actors, global)
 * - date: The date of the statistics
 * - search_count: Number of times this term was searched
 * - result_clicks: Number of result clicks from this search
 * - zero_results: Searches that returned no results
 * - avg_results_count: Average number of results shown
 */
class SearchAnalytics extends Model
{
    protected $table = 'search_analytics';

    protected $fillable = [
        'search_term',
        'search_type',
        'date',
        'search_count',
        'result_clicks',
        'zero_results',
        'avg_results_count',
    ];

    protected $casts = [
        'date' => 'date',
        'search_count' => 'integer',
        'result_clicks' => 'integer',
        'zero_results' => 'integer',
        'avg_results_count' => 'float',
    ];

    // Search types
    public const TYPE_GLOBAL = 'global';
    public const TYPE_EVENTS = 'events';
    public const TYPE_EQUIPMENT = 'equipment';
    public const TYPE_ACTORS = 'actors';
    public const TYPE_CONFLICTS = 'conflicts';
    public const TYPE_ZONES = 'zones';
    public const TYPE_ARTICLES = 'articles';

    /**
     * Track a search.
     */
    public static function track(
        string $searchTerm,
        string $searchType = self::TYPE_GLOBAL,
        int $resultCount = 0,
        bool $clicked = false
    ): void {
        $normalizedTerm = self::normalizeTerm($searchTerm);
        if (strlen($normalizedTerm) < 2) {
            return;
        }

        $stat = self::firstOrCreate([
            'search_term' => $normalizedTerm,
            'search_type' => $searchType,
            'date' => now()->toDateString(),
        ], [
            'search_count' => 0,
            'result_clicks' => 0,
            'zero_results' => 0,
            'avg_results_count' => 0,
        ]);

        $stat->increment('search_count');

        if ($clicked) {
            $stat->increment('result_clicks');
        }

        if ($resultCount === 0) {
            $stat->increment('zero_results');
        }

        // Update average result count
        $totalSearches = $stat->search_count;
        $stat->avg_results_count = (($stat->avg_results_count * ($totalSearches - 1)) + $resultCount) / $totalSearches;
        $stat->save();
    }

    /**
     * Normalize search term for aggregation.
     */
    public static function normalizeTerm(string $term): string
    {
        $term = mb_strtolower(trim($term));
        $term = preg_replace('/\s+/', ' ', $term);
        return substr($term, 0, 255);
    }

    /**
     * Scope for filtering by date range.
     */
    public function scopeDateRange(Builder $query, string $from, string $to): Builder
    {
        return $query->whereBetween('date', [$from, $to]);
    }

    /**
     * Get top search terms.
     */
    public static function getTopSearches(string $from, string $to, int $limit = 50): \Illuminate\Support\Collection
    {
        return self::selectRaw('search_term, search_type, SUM(search_count) as total_searches, SUM(result_clicks) as total_clicks, SUM(zero_results) as total_zero')
            ->dateRange($from, $to)
            ->groupBy('search_term', 'search_type')
            ->orderByDesc('total_searches')
            ->limit($limit)
            ->get()
            ->map(fn ($item) => [
                'term' => $item->search_term,
                'type' => $item->search_type,
                'searches' => $item->total_searches,
                'clicks' => $item->total_clicks,
                'ctr' => $item->total_searches > 0
                    ? round(($item->total_clicks / $item->total_searches) * 100, 1)
                    : 0,
                'zero_rate' => $item->total_searches > 0
                    ? round(($item->total_zero / $item->total_searches) * 100, 1)
                    : 0,
            ]);
    }

    /**
     * Get searches with zero results (content gaps).
     */
    public static function getZeroResultSearches(string $from, string $to, int $limit = 30): \Illuminate\Support\Collection
    {
        return self::selectRaw('search_term, search_type, SUM(zero_results) as total_zero, SUM(search_count) as total_searches')
            ->dateRange($from, $to)
            ->havingRaw('SUM(zero_results) > 0')
            ->groupBy('search_term', 'search_type')
            ->orderByDesc('total_zero')
            ->limit($limit)
            ->get();
    }

    /**
     * Get search volume by type.
     */
    public static function getVolumeByType(string $from, string $to): \Illuminate\Support\Collection
    {
        return self::selectRaw('search_type, SUM(search_count) as total_searches, COUNT(DISTINCT search_term) as unique_terms')
            ->dateRange($from, $to)
            ->groupBy('search_type')
            ->orderByDesc('total_searches')
            ->get();
    }

    /**
     * Get daily search volume.
     */
    public static function getDailyVolume(string $from, string $to): \Illuminate\Support\Collection
    {
        return self::selectRaw('date, SUM(search_count) as total_searches, SUM(result_clicks) as total_clicks')
            ->dateRange($from, $to)
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    /**
     * Get trending searches (comparing to previous period).
     */
    public static function getTrendingSearches(int $days = 7, int $limit = 20): \Illuminate\Support\Collection
    {
        $currentFrom = now()->subDays($days)->toDateString();
        $currentTo = now()->toDateString();
        $previousFrom = now()->subDays($days * 2)->toDateString();
        $previousTo = now()->subDays($days + 1)->toDateString();

        $current = self::selectRaw('search_term, SUM(search_count) as searches')
            ->dateRange($currentFrom, $currentTo)
            ->groupBy('search_term')
            ->pluck('searches', 'search_term');

        $previous = self::selectRaw('search_term, SUM(search_count) as searches')
            ->dateRange($previousFrom, $previousTo)
            ->groupBy('search_term')
            ->pluck('searches', 'search_term');

        $trends = collect();
        foreach ($current as $term => $currentCount) {
            $previousCount = $previous[$term] ?? 0;
            if ($previousCount > 0) {
                $growth = (($currentCount - $previousCount) / $previousCount) * 100;
            } else {
                $growth = $currentCount > 5 ? 100 : 0; // New term with significant volume
            }

            if ($growth > 20) { // At least 20% growth
                $trends->push([
                    'term' => $term,
                    'current' => $currentCount,
                    'previous' => $previousCount,
                    'growth' => round($growth, 1),
                ]);
            }
        }

        return $trends->sortByDesc('growth')->take($limit)->values();
    }
}
