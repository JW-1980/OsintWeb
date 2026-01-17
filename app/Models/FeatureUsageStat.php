<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * FeatureUsageStat Model
 *
 * Tracks aggregated usage of platform features and functionality.
 * Helps understand which features provide value to users.
 *
 * Database Schema:
 * - feature_name: Identifier for the feature (e.g., 'map_zoom', 'event_filter')
 * - feature_category: Category grouping (navigation, content, tools, export, social)
 * - feature_action: Type of interaction (click, view, submit, toggle)
 * - date: The date of the statistics
 * - hour: Hour of day (0-23)
 * - usage_count: Total uses in this bucket
 * - unique_users: Unique anonymous session hashes
 * - metadata: Additional context as JSON
 */
class FeatureUsageStat extends Model
{
    protected $table = 'feature_usage_stats';

    protected $fillable = [
        'feature_name',
        'feature_category',
        'feature_action',
        'date',
        'hour',
        'usage_count',
        'unique_users',
        'metadata',
    ];

    protected $casts = [
        'date' => 'date',
        'hour' => 'integer',
        'usage_count' => 'integer',
        'unique_users' => 'integer',
        'metadata' => 'array',
    ];

    // Feature categories
    public const CATEGORY_NAVIGATION = 'navigation';
    public const CATEGORY_CONTENT = 'content';
    public const CATEGORY_TOOLS = 'tools';
    public const CATEGORY_EXPORT = 'export';
    public const CATEGORY_SOCIAL = 'social';
    public const CATEGORY_MAP = 'map';
    public const CATEGORY_SEARCH = 'search';
    public const CATEGORY_FILTER = 'filter';

    // Common feature actions
    public const ACTION_CLICK = 'click';
    public const ACTION_VIEW = 'view';
    public const ACTION_SUBMIT = 'submit';
    public const ACTION_TOGGLE = 'toggle';
    public const ACTION_EXPAND = 'expand';
    public const ACTION_DOWNLOAD = 'download';
    public const ACTION_SHARE = 'share';

    /**
     * Track feature usage.
     */
    public static function track(
        string $featureName,
        string $category,
        string $action = self::ACTION_CLICK,
        ?array $metadata = null,
        bool $isUniqueUser = false
    ): void {
        $now = now();
        $date = $now->toDateString();
        $hour = $now->hour;

        $stat = self::firstOrCreate([
            'feature_name' => substr($featureName, 0, 100),
            'feature_action' => $action,
            'date' => $date,
            'hour' => $hour,
        ], [
            'feature_category' => $category,
            'usage_count' => 0,
            'unique_users' => 0,
            'metadata' => $metadata,
        ]);

        $stat->increment('usage_count');
        if ($isUniqueUser) {
            $stat->increment('unique_users');
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
     * Scope for filtering by category.
     */
    public function scopeCategory(Builder $query, string $category): Builder
    {
        return $query->where('feature_category', $category);
    }

    /**
     * Get top features by usage.
     */
    public static function getTopFeatures(string $from, string $to, int $limit = 20): \Illuminate\Support\Collection
    {
        return self::selectRaw('feature_name, feature_category, feature_action, SUM(usage_count) as total_usage, SUM(unique_users) as total_unique')
            ->dateRange($from, $to)
            ->groupBy('feature_name', 'feature_category', 'feature_action')
            ->orderByDesc('total_usage')
            ->limit($limit)
            ->get();
    }

    /**
     * Get usage by category.
     */
    public static function getUsageByCategory(string $from, string $to): \Illuminate\Support\Collection
    {
        return self::selectRaw('feature_category, SUM(usage_count) as total_usage, COUNT(DISTINCT feature_name) as feature_count')
            ->dateRange($from, $to)
            ->groupBy('feature_category')
            ->orderByDesc('total_usage')
            ->get();
    }

    /**
     * Get daily feature usage totals.
     */
    public static function getDailyTotals(string $from, string $to): \Illuminate\Support\Collection
    {
        return self::selectRaw('date, SUM(usage_count) as total_usage')
            ->dateRange($from, $to)
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    /**
     * Get feature trends (compare periods).
     */
    public static function getFeatureTrends(string $currentFrom, string $currentTo, string $previousFrom, string $previousTo): array
    {
        $current = self::selectRaw('feature_name, SUM(usage_count) as usage')
            ->dateRange($currentFrom, $currentTo)
            ->groupBy('feature_name')
            ->pluck('usage', 'feature_name');

        $previous = self::selectRaw('feature_name, SUM(usage_count) as usage')
            ->dateRange($previousFrom, $previousTo)
            ->groupBy('feature_name')
            ->pluck('usage', 'feature_name');

        $trends = [];
        foreach ($current as $feature => $currentUsage) {
            $previousUsage = $previous[$feature] ?? 0;
            $change = $previousUsage > 0 ? (($currentUsage - $previousUsage) / $previousUsage) * 100 : 100;
            $trends[$feature] = [
                'current' => $currentUsage,
                'previous' => $previousUsage,
                'change_percent' => round($change, 1),
            ];
        }

        return $trends;
    }

    /**
     * Get available categories.
     */
    public static function getCategories(): array
    {
        return [
            self::CATEGORY_NAVIGATION => 'Navigation',
            self::CATEGORY_CONTENT => 'Content',
            self::CATEGORY_TOOLS => 'Tools',
            self::CATEGORY_EXPORT => 'Export',
            self::CATEGORY_SOCIAL => 'Social',
            self::CATEGORY_MAP => 'Map',
            self::CATEGORY_SEARCH => 'Search',
            self::CATEGORY_FILTER => 'Filter',
        ];
    }
}
