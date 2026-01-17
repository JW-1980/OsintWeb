<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * UserFlowStat Model
 *
 * Tracks aggregated user navigation patterns between pages.
 * Helps understand how users move through the site.
 *
 * Database Schema:
 * - from_page: The source page path
 * - to_page: The destination page path
 * - date: The date of the statistics
 * - transition_count: Number of times this path was taken
 * - exit_count: Number of users who exited from from_page
 */
class UserFlowStat extends Model
{
    protected $table = 'user_flow_stats';

    protected $fillable = [
        'from_page',
        'to_page',
        'date',
        'transition_count',
        'exit_count',
    ];

    protected $casts = [
        'date' => 'date',
        'transition_count' => 'integer',
        'exit_count' => 'integer',
    ];

    /**
     * Track a page transition.
     */
    public static function trackTransition(string $fromPage, string $toPage): void
    {
        $stat = self::firstOrCreate([
            'from_page' => substr($fromPage, 0, 255),
            'to_page' => substr($toPage, 0, 255),
            'date' => now()->toDateString(),
        ], [
            'transition_count' => 0,
            'exit_count' => 0,
        ]);

        $stat->increment('transition_count');
    }

    /**
     * Track an exit from a page.
     */
    public static function trackExit(string $page): void
    {
        $stat = self::firstOrCreate([
            'from_page' => substr($page, 0, 255),
            'to_page' => '__EXIT__',
            'date' => now()->toDateString(),
        ], [
            'transition_count' => 0,
            'exit_count' => 0,
        ]);

        $stat->increment('exit_count');
    }

    /**
     * Scope for filtering by date range.
     */
    public function scopeDateRange(Builder $query, string $from, string $to): Builder
    {
        return $query->whereBetween('date', [$from, $to]);
    }

    /**
     * Get top navigation paths.
     */
    public static function getTopPaths(string $from, string $to, int $limit = 50): \Illuminate\Support\Collection
    {
        return self::selectRaw('from_page, to_page, SUM(transition_count) as total_transitions')
            ->dateRange($from, $to)
            ->where('to_page', '!=', '__EXIT__')
            ->groupBy('from_page', 'to_page')
            ->orderByDesc('total_transitions')
            ->limit($limit)
            ->get();
    }

    /**
     * Get top entry pages.
     */
    public static function getTopEntryPages(string $from, string $to, int $limit = 20): \Illuminate\Support\Collection
    {
        return self::selectRaw('to_page as page, SUM(transition_count) as entries')
            ->dateRange($from, $to)
            ->where('from_page', '__ENTRY__')
            ->groupBy('to_page')
            ->orderByDesc('entries')
            ->limit($limit)
            ->get();
    }

    /**
     * Get top exit pages.
     */
    public static function getTopExitPages(string $from, string $to, int $limit = 20): \Illuminate\Support\Collection
    {
        return self::selectRaw('from_page as page, SUM(exit_count) as exits')
            ->dateRange($from, $to)
            ->where('to_page', '__EXIT__')
            ->groupBy('from_page')
            ->orderByDesc('exits')
            ->limit($limit)
            ->get();
    }

    /**
     * Get flow from a specific page.
     */
    public static function getFlowFromPage(string $page, string $from, string $to): \Illuminate\Support\Collection
    {
        $normalized = substr($page, 0, 255);

        return self::selectRaw('to_page, SUM(transition_count) as transitions, SUM(exit_count) as exits')
            ->dateRange($from, $to)
            ->where('from_page', $normalized)
            ->groupBy('to_page')
            ->orderByDesc('transitions')
            ->get();
    }

    /**
     * Get flow to a specific page.
     */
    public static function getFlowToPage(string $page, string $from, string $to): \Illuminate\Support\Collection
    {
        $normalized = substr($page, 0, 255);

        return self::selectRaw('from_page, SUM(transition_count) as transitions')
            ->dateRange($from, $to)
            ->where('to_page', $normalized)
            ->groupBy('from_page')
            ->orderByDesc('transitions')
            ->get();
    }

    /**
     * Build a flow graph for visualization.
     */
    public static function buildFlowGraph(string $from, string $to, int $minTransitions = 5): array
    {
        $flows = self::selectRaw('from_page, to_page, SUM(transition_count) as weight')
            ->dateRange($from, $to)
            ->where('to_page', '!=', '__EXIT__')
            ->where('from_page', '!=', '__ENTRY__')
            ->groupBy('from_page', 'to_page')
            ->havingRaw('SUM(transition_count) >= ?', [$minTransitions])
            ->orderByDesc('weight')
            ->limit(100)
            ->get();

        $nodes = collect();
        $links = [];

        foreach ($flows as $flow) {
            $nodes->push($flow->from_page);
            $nodes->push($flow->to_page);
            $links[] = [
                'source' => $flow->from_page,
                'target' => $flow->to_page,
                'value' => $flow->weight,
            ];
        }

        return [
            'nodes' => $nodes->unique()->values()->map(fn ($page) => ['id' => $page])->toArray(),
            'links' => $links,
        ];
    }
}
