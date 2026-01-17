<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ExperimentAssignment Model
 *
 * Maps anonymous visitors to their assigned variant.
 * Ensures consistent experience across sessions.
 * No personal data stored - uses anonymous hash only.
 *
 * Database Schema:
 * - experiment_id: The experiment
 * - variant_id: The assigned variant
 * - visitor_hash: SHA-256 hash of anonymous identifier
 * - assigned_at: When assignment was made
 * - last_seen_at: Last time visitor saw this variant
 */
class ExperimentAssignment extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'experiment_id',
        'variant_id',
        'visitor_hash',
        'assigned_at',
        'last_seen_at',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'last_seen_at' => 'datetime',
    ];

    /**
     * The experiment this assignment belongs to.
     */
    public function experiment(): BelongsTo
    {
        return $this->belongsTo(Experiment::class);
    }

    /**
     * The assigned variant.
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ExperimentVariant::class, 'variant_id');
    }

    /**
     * Create an anonymous visitor hash.
     */
    public static function createVisitorHash(string $identifier): string
    {
        return hash('sha256', $identifier);
    }

    /**
     * Find or create assignment for a visitor.
     */
    public static function findOrAssign(Experiment $experiment, string $visitorHash): ?self
    {
        // Check for existing assignment
        $assignment = self::where('experiment_id', $experiment->id)
            ->where('visitor_hash', $visitorHash)
            ->first();

        if ($assignment) {
            $assignment->last_seen_at = now();
            $assignment->save();
            return $assignment;
        }

        // Check if visitor should be included in experiment
        if (!self::shouldIncludeVisitor($experiment, $visitorHash)) {
            return null;
        }

        // Assign to a variant based on weights
        $variant = self::selectVariant($experiment, $visitorHash);
        if (!$variant) {
            return null;
        }

        return self::create([
            'experiment_id' => $experiment->id,
            'variant_id' => $variant->id,
            'visitor_hash' => $visitorHash,
            'assigned_at' => now(),
            'last_seen_at' => now(),
        ]);
    }

    /**
     * Check if visitor should be included based on traffic percentage.
     */
    private static function shouldIncludeVisitor(Experiment $experiment, string $visitorHash): bool
    {
        if ($experiment->traffic_percentage >= 100) {
            return true;
        }

        // Use hash to deterministically include/exclude
        $hashInt = hexdec(substr($visitorHash, 0, 8));
        $bucket = $hashInt % 100;

        return $bucket < $experiment->traffic_percentage;
    }

    /**
     * Select a variant based on weights.
     */
    private static function selectVariant(Experiment $experiment, string $visitorHash): ?ExperimentVariant
    {
        $variants = $experiment->variants()->orderBy('id')->get();
        if ($variants->isEmpty()) {
            return null;
        }

        $totalWeight = $variants->sum('weight');
        if ($totalWeight === 0) {
            return $variants->first();
        }

        // Use hash to deterministically select variant
        $hashInt = hexdec(substr($visitorHash, 8, 8));
        $bucket = $hashInt % $totalWeight;

        $cumulative = 0;
        foreach ($variants as $variant) {
            $cumulative += $variant->weight;
            if ($bucket < $cumulative) {
                return $variant;
            }
        }

        return $variants->last();
    }
}
