<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

/**
 * ExperimentConversion Model
 *
 * Stores aggregated daily conversion data per variant.
 * No individual user tracking - only aggregate counts.
 *
 * Database Schema:
 * - experiment_id: The experiment
 * - variant_id: The variant
 * - date: Date of the aggregation
 * - goal: Which conversion goal
 * - impressions: Times variant was shown
 * - conversions: Times goal was achieved
 * - unique_impressions: Unique visitors who saw variant
 * - unique_conversions: Unique visitors who converted
 */
class ExperimentConversion extends Model
{
    protected $fillable = [
        'experiment_id',
        'variant_id',
        'date',
        'goal',
        'impressions',
        'conversions',
        'unique_impressions',
        'unique_conversions',
    ];

    protected $casts = [
        'date' => 'date',
        'impressions' => 'integer',
        'conversions' => 'integer',
        'unique_impressions' => 'integer',
        'unique_conversions' => 'integer',
    ];

    /**
     * The experiment this conversion belongs to.
     */
    public function experiment(): BelongsTo
    {
        return $this->belongsTo(Experiment::class);
    }

    /**
     * The variant this conversion belongs to.
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ExperimentVariant::class, 'variant_id');
    }

    /**
     * Record an impression.
     */
    public static function recordImpression(
        int $experimentId,
        int $variantId,
        string $goal,
        bool $isUnique = false
    ): void {
        $conversion = self::firstOrCreate([
            'experiment_id' => $experimentId,
            'variant_id' => $variantId,
            'date' => now()->toDateString(),
            'goal' => $goal,
        ], [
            'impressions' => 0,
            'conversions' => 0,
            'unique_impressions' => 0,
            'unique_conversions' => 0,
        ]);

        $conversion->increment('impressions');
        if ($isUnique) {
            $conversion->increment('unique_impressions');
        }
    }

    /**
     * Record a conversion.
     */
    public static function recordConversion(
        int $experimentId,
        int $variantId,
        string $goal,
        bool $isUnique = false
    ): void {
        $conversion = self::firstOrCreate([
            'experiment_id' => $experimentId,
            'variant_id' => $variantId,
            'date' => now()->toDateString(),
            'goal' => $goal,
        ], [
            'impressions' => 0,
            'conversions' => 0,
            'unique_impressions' => 0,
            'unique_conversions' => 0,
        ]);

        $conversion->increment('conversions');
        if ($isUnique) {
            $conversion->increment('unique_conversions');
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
     * Get totals for an experiment.
     */
    public static function getTotalsForExperiment(int $experimentId, string $goal): \Illuminate\Support\Collection
    {
        return self::selectRaw('variant_id, SUM(impressions) as total_impressions, SUM(conversions) as total_conversions, SUM(unique_impressions) as total_unique_impressions, SUM(unique_conversions) as total_unique_conversions')
            ->where('experiment_id', $experimentId)
            ->where('goal', $goal)
            ->groupBy('variant_id')
            ->get();
    }

    /**
     * Get daily data for a variant.
     */
    public static function getDailyDataForVariant(int $variantId, string $goal): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('variant_id', $variantId)
            ->where('goal', $goal)
            ->orderBy('date')
            ->get();
    }
}
