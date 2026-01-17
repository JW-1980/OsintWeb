<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * ExperimentVariant Model
 *
 * Represents a single variant (version) in an A/B test.
 * Each experiment has at least 2 variants: control + treatment(s).
 *
 * Database Schema:
 * - experiment_id: Parent experiment
 * - name: Human-readable name (e.g., 'Control', 'Variant A')
 * - slug: URL-friendly identifier
 * - is_control: Whether this is the control variant
 * - weight: Traffic allocation weight (should sum to 100)
 * - content: JSON containing the variant's content/config
 * - description: What makes this variant different
 */
class ExperimentVariant extends Model
{
    protected $fillable = [
        'experiment_id',
        'name',
        'slug',
        'is_control',
        'weight',
        'content',
        'description',
    ];

    protected $casts = [
        'is_control' => 'boolean',
        'weight' => 'integer',
        'content' => 'array',
    ];

    /**
     * The experiment this variant belongs to.
     */
    public function experiment(): BelongsTo
    {
        return $this->belongsTo(Experiment::class);
    }

    /**
     * Assignments to this variant.
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(ExperimentAssignment::class, 'variant_id');
    }

    /**
     * Conversions for this variant.
     */
    public function conversions(): HasMany
    {
        return $this->hasMany(ExperimentConversion::class, 'variant_id');
    }

    /**
     * Results for this variant.
     */
    public function results(): HasMany
    {
        return $this->hasMany(ExperimentResult::class, 'variant_id');
    }

    /**
     * Get the content value for a specific key.
     */
    public function getContentValue(string $key, mixed $default = null): mixed
    {
        return data_get($this->content, $key, $default);
    }

    /**
     * Get text content (for text experiments).
     */
    public function getText(): ?string
    {
        return $this->getContentValue('text');
    }

    /**
     * Get button text (for button experiments).
     */
    public function getButtonText(): ?string
    {
        return $this->getContentValue('button_text');
    }

    /**
     * Get button color (for button experiments).
     */
    public function getButtonColor(): ?string
    {
        return $this->getContentValue('button_color');
    }

    /**
     * Get feature enabled state (for feature experiments).
     */
    public function isFeatureEnabled(): bool
    {
        return (bool) $this->getContentValue('enabled', false);
    }

    /**
     * Get image URL (for image experiments).
     */
    public function getImageUrl(): ?string
    {
        return $this->getContentValue('image_url');
    }

    /**
     * Get total impressions.
     */
    public function getTotalImpressions(): int
    {
        return $this->conversions()->sum('impressions');
    }

    /**
     * Get total conversions for a goal.
     */
    public function getTotalConversions(string $goal): int
    {
        return $this->conversions()->where('goal', $goal)->sum('conversions');
    }

    /**
     * Get conversion rate for a goal.
     */
    public function getConversionRate(string $goal): float
    {
        $impressions = $this->getTotalImpressions();
        if ($impressions === 0) {
            return 0;
        }

        return ($this->getTotalConversions($goal) / $impressions) * 100;
    }
}
