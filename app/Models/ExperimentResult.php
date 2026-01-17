<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ExperimentResult Model
 *
 * Caches calculated experiment results for quick dashboard display.
 * Updated periodically by the results calculation job.
 *
 * Database Schema:
 * - experiment_id: The experiment
 * - variant_id: The variant
 * - goal: Which conversion goal
 * - total_impressions: Cumulative impressions
 * - total_conversions: Cumulative conversions
 * - conversion_rate: conversions / impressions
 * - lift_vs_control: % improvement over control
 * - confidence: Statistical confidence level
 * - z_score: Z-score for significance testing
 * - is_significant: Whether result is statistically significant
 * - is_winner: Whether this variant is the winner
 * - calculated_at: When results were last calculated
 */
class ExperimentResult extends Model
{
    protected $fillable = [
        'experiment_id',
        'variant_id',
        'goal',
        'total_impressions',
        'total_conversions',
        'conversion_rate',
        'lift_vs_control',
        'confidence',
        'z_score',
        'is_significant',
        'is_winner',
        'calculated_at',
    ];

    protected $casts = [
        'total_impressions' => 'integer',
        'total_conversions' => 'integer',
        'conversion_rate' => 'float',
        'lift_vs_control' => 'float',
        'confidence' => 'float',
        'z_score' => 'float',
        'is_significant' => 'boolean',
        'is_winner' => 'boolean',
        'calculated_at' => 'datetime',
    ];

    /**
     * The experiment this result belongs to.
     */
    public function experiment(): BelongsTo
    {
        return $this->belongsTo(Experiment::class);
    }

    /**
     * The variant this result belongs to.
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ExperimentVariant::class, 'variant_id');
    }

    /**
     * Calculate and update results for an experiment.
     */
    public static function calculateForExperiment(Experiment $experiment): void
    {
        $goals = array_merge([$experiment->primary_goal], $experiment->secondary_goals ?? []);

        foreach ($goals as $goal) {
            self::calculateForGoal($experiment, $goal);
        }
    }

    /**
     * Calculate results for a specific goal.
     */
    private static function calculateForGoal(Experiment $experiment, string $goal): void
    {
        $totals = ExperimentConversion::getTotalsForExperiment($experiment->id, $goal);

        if ($totals->isEmpty()) {
            return;
        }

        $controlVariant = $experiment->getControlVariant();
        $controlData = $totals->firstWhere('variant_id', $controlVariant?->id);
        $controlRate = $controlData && $controlData->total_impressions > 0
            ? $controlData->total_conversions / $controlData->total_impressions
            : 0;

        $results = [];
        $highestLift = -PHP_FLOAT_MAX;
        $winnerId = null;

        foreach ($totals as $data) {
            $impressions = $data->total_impressions;
            $conversions = $data->total_conversions;

            if ($impressions === 0) {
                continue;
            }

            $rate = $conversions / $impressions;
            $lift = $controlRate > 0 ? (($rate - $controlRate) / $controlRate) * 100 : 0;

            // Calculate Z-score for significance testing
            $zScore = self::calculateZScore(
                $controlData?->total_impressions ?? 0,
                $controlData?->total_conversions ?? 0,
                $impressions,
                $conversions
            );

            // Convert Z-score to confidence level
            $confidence = self::zScoreToConfidence($zScore);

            $isSignificant = $confidence >= $experiment->min_confidence
                && $conversions >= $experiment->min_sample_size;

            // Track potential winner
            if ($data->variant_id !== $controlVariant?->id && $isSignificant && $lift > $highestLift) {
                $highestLift = $lift;
                $winnerId = $data->variant_id;
            }

            $results[$data->variant_id] = [
                'total_impressions' => $impressions,
                'total_conversions' => $conversions,
                'conversion_rate' => round($rate * 100, 4),
                'lift_vs_control' => $data->variant_id === $controlVariant?->id ? null : round($lift, 2),
                'confidence' => round($confidence, 4),
                'z_score' => round($zScore, 4),
                'is_significant' => $isSignificant,
            ];
        }

        // Save results
        foreach ($results as $variantId => $data) {
            self::updateOrCreate(
                [
                    'experiment_id' => $experiment->id,
                    'variant_id' => $variantId,
                    'goal' => $goal,
                ],
                array_merge($data, [
                    'is_winner' => $variantId === $winnerId,
                    'calculated_at' => now(),
                ])
            );
        }
    }

    /**
     * Calculate Z-score for two-proportion z-test.
     */
    private static function calculateZScore(
        int $controlImpressions,
        int $controlConversions,
        int $variantImpressions,
        int $variantConversions
    ): float {
        if ($controlImpressions === 0 || $variantImpressions === 0) {
            return 0;
        }

        $p1 = $controlConversions / $controlImpressions;
        $p2 = $variantConversions / $variantImpressions;

        $pooled = ($controlConversions + $variantConversions) / ($controlImpressions + $variantImpressions);

        if ($pooled === 0 || $pooled === 1) {
            return 0;
        }

        $se = sqrt($pooled * (1 - $pooled) * ((1 / $controlImpressions) + (1 / $variantImpressions)));

        if ($se === 0) {
            return 0;
        }

        return ($p2 - $p1) / $se;
    }

    /**
     * Convert Z-score to confidence level using normal distribution.
     */
    private static function zScoreToConfidence(float $zScore): float
    {
        // Standard normal CDF approximation
        $absZ = abs($zScore);

        // Approximation of the cumulative distribution function
        $t = 1 / (1 + 0.2316419 * $absZ);
        $d = 0.3989423 * exp(-$absZ * $absZ / 2);
        $probability = $d * $t * (0.3193815 + $t * (-0.3565638 + $t * (1.781478 + $t * (-1.821256 + $t * 1.330274))));

        if ($zScore > 0) {
            $probability = 1 - $probability;
        }

        // Two-tailed test
        $pValue = 2 * min($probability, 1 - $probability);

        return 1 - $pValue;
    }

    /**
     * Format confidence as percentage string.
     */
    public function getConfidencePercentage(): string
    {
        return round($this->confidence * 100, 1) . '%';
    }

    /**
     * Get human-readable significance status.
     */
    public function getSignificanceStatus(): string
    {
        if (!$this->is_significant) {
            return 'Not significant';
        }

        if ($this->confidence >= 0.99) {
            return 'Highly significant (99%+)';
        }

        if ($this->confidence >= 0.95) {
            return 'Significant (95%+)';
        }

        return 'Marginally significant (90%+)';
    }
}
