<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Experiment;
use App\Models\ExperimentVariant;
use App\Models\ExperimentAssignment;
use App\Models\ExperimentConversion;
use App\Models\ExperimentResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;

/**
 * ABTestingService
 *
 * Manages A/B test variant assignment and conversion tracking.
 * All tracking is anonymous using cookie-based visitor identification.
 */
class ABTestingService
{
    private const VISITOR_COOKIE = 'ab_visitor';
    private const VISITOR_TTL = 365 * 24 * 60; // 1 year in minutes

    /**
     * Get variant for a visitor at a specific location.
     */
    public function getVariant(Request $request, string $location): ?array
    {
        $experiment = $this->getActiveExperiment($location);
        if (!$experiment) {
            return null;
        }

        $visitorHash = $this->getVisitorHash($request);
        $assignment = ExperimentAssignment::findOrAssign($experiment, $visitorHash);

        if (!$assignment) {
            return null;
        }

        $variant = $assignment->variant;

        // Record impression
        $isUnique = $this->isFirstImpression($visitorHash, $experiment->id);
        ExperimentConversion::recordImpression(
            $experiment->id,
            $variant->id,
            $experiment->primary_goal,
            $isUnique
        );

        if ($isUnique) {
            $this->markImpression($visitorHash, $experiment->id);
        }

        return [
            'experiment_id' => $experiment->id,
            'experiment_slug' => $experiment->slug,
            'variant_id' => $variant->id,
            'variant_slug' => $variant->slug,
            'is_control' => $variant->is_control,
            'content' => $variant->content,
        ];
    }

    /**
     * Get multiple variants for a visitor.
     */
    public function getVariants(Request $request, array $locations): array
    {
        $results = [];

        foreach ($locations as $location) {
            $variant = $this->getVariant($request, $location);
            if ($variant) {
                $results[$location] = $variant;
            }
        }

        return $results;
    }

    /**
     * Record a conversion.
     */
    public function trackConversion(Request $request, string $experimentSlug, string $goal): bool
    {
        $experiment = Experiment::where('slug', $experimentSlug)
            ->running()
            ->first();

        if (!$experiment) {
            return false;
        }

        $visitorHash = $this->getVisitorHash($request);
        $assignment = ExperimentAssignment::where('experiment_id', $experiment->id)
            ->where('visitor_hash', $visitorHash)
            ->first();

        if (!$assignment) {
            return false;
        }

        $isUnique = $this->isFirstConversion($visitorHash, $experiment->id, $goal);

        ExperimentConversion::recordConversion(
            $experiment->id,
            $assignment->variant_id,
            $goal,
            $isUnique
        );

        if ($isUnique) {
            $this->markConversion($visitorHash, $experiment->id, $goal);
        }

        return true;
    }

    /**
     * Track conversion by location.
     */
    public function trackConversionByLocation(Request $request, string $location, ?string $goal = null): bool
    {
        $experiment = $this->getActiveExperiment($location);
        if (!$experiment) {
            return false;
        }

        return $this->trackConversion($request, $experiment->slug, $goal ?? $experiment->primary_goal);
    }

    /**
     * Get active experiment at a location.
     */
    public function getActiveExperiment(string $location): ?Experiment
    {
        return Cache::remember("ab_experiment_{$location}", 60, function () use ($location) {
            return Experiment::with('variants')
                ->running()
                ->atLocation($location)
                ->first();
        });
    }

    /**
     * Get all active experiments.
     */
    public function getActiveExperiments(): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember('ab_experiments_active', 60, function () {
            return Experiment::with('variants')
                ->running()
                ->get();
        });
    }

    /**
     * Create a new experiment.
     */
    public function createExperiment(array $data): Experiment
    {
        $experiment = Experiment::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'type' => $data['type'] ?? Experiment::TYPE_TEXT,
            'location' => $data['location'],
            'status' => Experiment::STATUS_DRAFT,
            'traffic_percentage' => $data['traffic_percentage'] ?? 100,
            'min_sample_size' => $data['min_sample_size'] ?? 100,
            'min_confidence' => $data['min_confidence'] ?? 0.95,
            'primary_goal' => $data['primary_goal'] ?? Experiment::GOAL_CLICK,
            'secondary_goals' => $data['secondary_goals'] ?? [],
            'created_by' => $data['created_by'] ?? null,
        ]);

        // Create variants
        foreach ($data['variants'] as $index => $variantData) {
            ExperimentVariant::create([
                'experiment_id' => $experiment->id,
                'name' => $variantData['name'],
                'slug' => $variantData['slug'] ?? "variant-{$index}",
                'is_control' => $variantData['is_control'] ?? ($index === 0),
                'weight' => $variantData['weight'] ?? 50,
                'content' => $variantData['content'],
                'description' => $variantData['description'] ?? null,
            ]);
        }

        $this->clearCache();

        return $experiment->load('variants');
    }

    /**
     * Update an experiment.
     */
    public function updateExperiment(Experiment $experiment, array $data): Experiment
    {
        $experiment->update($data);
        $this->clearCache();
        return $experiment;
    }

    /**
     * Start an experiment.
     */
    public function startExperiment(Experiment $experiment): bool
    {
        $result = $experiment->start();
        $this->clearCache();
        return $result;
    }

    /**
     * Pause an experiment.
     */
    public function pauseExperiment(Experiment $experiment): bool
    {
        $result = $experiment->pause();
        $this->clearCache();
        return $result;
    }

    /**
     * Complete an experiment.
     */
    public function completeExperiment(Experiment $experiment): bool
    {
        $result = $experiment->complete();
        $this->clearCache();
        return $result;
    }

    /**
     * Calculate results for an experiment.
     */
    public function calculateResults(Experiment $experiment): void
    {
        ExperimentResult::calculateForExperiment($experiment);
    }

    /**
     * Calculate results for all running experiments.
     */
    public function calculateAllResults(): void
    {
        $experiments = Experiment::running()->get();

        foreach ($experiments as $experiment) {
            $this->calculateResults($experiment);
        }
    }

    /**
     * Get experiment with full statistics.
     */
    public function getExperimentWithStats(Experiment $experiment): array
    {
        $experiment->load(['variants', 'results', 'creator']);

        $summary = $experiment->getSummary();
        $dailyData = [];

        foreach ($experiment->variants as $variant) {
            $dailyData[$variant->id] = ExperimentConversion::getDailyDataForVariant(
                $variant->id,
                $experiment->primary_goal
            );
        }

        return [
            'experiment' => $experiment,
            'summary' => $summary,
            'daily_data' => $dailyData,
            'duration_days' => $experiment->started_at
                ? $experiment->started_at->diffInDays($experiment->ended_at ?? now())
                : 0,
        ];
    }

    /**
     * Get anonymous visitor hash.
     */
    private function getVisitorHash(Request $request): string
    {
        $visitorId = $request->cookie(self::VISITOR_COOKIE);

        if (!$visitorId) {
            $visitorId = bin2hex(random_bytes(16));
            Cookie::queue(self::VISITOR_COOKIE, $visitorId, self::VISITOR_TTL);
        }

        return ExperimentAssignment::createVisitorHash($visitorId);
    }

    /**
     * Check if this is first impression for visitor.
     */
    private function isFirstImpression(string $visitorHash, int $experimentId): bool
    {
        $key = "ab_impression_{$visitorHash}_{$experimentId}";
        return !Cache::has($key);
    }

    /**
     * Mark impression for visitor.
     */
    private function markImpression(string $visitorHash, int $experimentId): void
    {
        $key = "ab_impression_{$visitorHash}_{$experimentId}";
        Cache::put($key, true, now()->addDays(30));
    }

    /**
     * Check if this is first conversion for visitor.
     */
    private function isFirstConversion(string $visitorHash, int $experimentId, string $goal): bool
    {
        $key = "ab_conversion_{$visitorHash}_{$experimentId}_{$goal}";
        return !Cache::has($key);
    }

    /**
     * Mark conversion for visitor.
     */
    private function markConversion(string $visitorHash, int $experimentId, string $goal): void
    {
        $key = "ab_conversion_{$visitorHash}_{$experimentId}_{$goal}";
        Cache::put($key, true, now()->addDays(30));
    }

    /**
     * Clear experiment cache.
     */
    private function clearCache(): void
    {
        Cache::forget('ab_experiments_active');

        // Clear location-specific caches
        $experiments = Experiment::all();
        foreach ($experiments as $experiment) {
            Cache::forget("ab_experiment_{$experiment->location}");
        }
    }
}
