<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

/**
 * Experiment Model
 *
 * Defines an A/B test experiment with multiple variants.
 * Supports multivariate testing of text, buttons, layouts, and features.
 *
 * Database Schema:
 * - uuid: Public identifier
 * - name: Human-readable name
 * - slug: URL-friendly identifier
 * - description: What is being tested and why
 * - type: text, button, layout, feature
 * - location: Where in UI (e.g., 'homepage.hero', 'event.cta')
 * - status: draft, running, paused, completed
 * - traffic_percentage: % of traffic to include in test
 * - started_at/ended_at: Test duration
 * - min_sample_size: Minimum conversions before significance
 * - min_confidence: Required statistical confidence
 * - primary_goal: Main conversion metric
 * - secondary_goals: Additional metrics to track
 */
class Experiment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'name',
        'slug',
        'description',
        'type',
        'location',
        'status',
        'traffic_percentage',
        'started_at',
        'ended_at',
        'min_sample_size',
        'min_confidence',
        'primary_goal',
        'secondary_goals',
        'created_by',
    ];

    protected $casts = [
        'traffic_percentage' => 'integer',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'min_sample_size' => 'integer',
        'min_confidence' => 'float',
        'secondary_goals' => 'array',
    ];

    // Experiment types
    public const TYPE_TEXT = 'text';
    public const TYPE_BUTTON = 'button';
    public const TYPE_LAYOUT = 'layout';
    public const TYPE_FEATURE = 'feature';
    public const TYPE_IMAGE = 'image';
    public const TYPE_COLOR = 'color';

    // Experiment statuses
    public const STATUS_DRAFT = 'draft';
    public const STATUS_RUNNING = 'running';
    public const STATUS_PAUSED = 'paused';
    public const STATUS_COMPLETED = 'completed';

    // Common goals
    public const GOAL_CLICK = 'click';
    public const GOAL_SIGNUP = 'signup';
    public const GOAL_EVENT_CREATE = 'event_create';
    public const GOAL_EVENT_VIEW = 'event_view';
    public const GOAL_SEARCH = 'search';
    public const GOAL_EXPORT = 'export';
    public const GOAL_SHARE = 'share';
    public const GOAL_SUBSCRIBE = 'subscribe';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($experiment) {
            if (empty($experiment->uuid)) {
                $experiment->uuid = (string) Str::uuid();
            }
            if (empty($experiment->slug)) {
                $experiment->slug = Str::slug($experiment->name);
            }
        });
    }

    /**
     * Variants for this experiment.
     */
    public function variants(): HasMany
    {
        return $this->hasMany(ExperimentVariant::class);
    }

    /**
     * Assignments for this experiment.
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(ExperimentAssignment::class);
    }

    /**
     * Conversions for this experiment.
     */
    public function conversions(): HasMany
    {
        return $this->hasMany(ExperimentConversion::class);
    }

    /**
     * Results for this experiment.
     */
    public function results(): HasMany
    {
        return $this->hasMany(ExperimentResult::class);
    }

    /**
     * User who created this experiment.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the control variant.
     */
    public function getControlVariant(): ?ExperimentVariant
    {
        return $this->variants()->where('is_control', true)->first();
    }

    /**
     * Scope for running experiments.
     */
    public function scopeRunning(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_RUNNING);
    }

    /**
     * Scope for experiments at a location.
     */
    public function scopeAtLocation(Builder $query, string $location): Builder
    {
        return $query->where('location', $location);
    }

    /**
     * Check if experiment is active.
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_RUNNING;
    }

    /**
     * Start the experiment.
     */
    public function start(): bool
    {
        if ($this->variants()->count() < 2) {
            return false;
        }

        if (!$this->variants()->where('is_control', true)->exists()) {
            return false;
        }

        $this->status = self::STATUS_RUNNING;
        $this->started_at = now();
        return $this->save();
    }

    /**
     * Pause the experiment.
     */
    public function pause(): bool
    {
        $this->status = self::STATUS_PAUSED;
        return $this->save();
    }

    /**
     * Complete the experiment.
     */
    public function complete(): bool
    {
        $this->status = self::STATUS_COMPLETED;
        $this->ended_at = now();
        return $this->save();
    }

    /**
     * Get experiment types.
     */
    public static function getTypes(): array
    {
        return [
            self::TYPE_TEXT => 'Text Content',
            self::TYPE_BUTTON => 'Button/CTA',
            self::TYPE_LAYOUT => 'Layout',
            self::TYPE_FEATURE => 'Feature Toggle',
            self::TYPE_IMAGE => 'Image/Media',
            self::TYPE_COLOR => 'Color/Style',
        ];
    }

    /**
     * Get common goals.
     */
    public static function getGoals(): array
    {
        return [
            self::GOAL_CLICK => 'Click',
            self::GOAL_SIGNUP => 'Sign Up',
            self::GOAL_EVENT_CREATE => 'Create Event',
            self::GOAL_EVENT_VIEW => 'View Event',
            self::GOAL_SEARCH => 'Perform Search',
            self::GOAL_EXPORT => 'Export Data',
            self::GOAL_SHARE => 'Share Content',
            self::GOAL_SUBSCRIBE => 'Subscribe',
        ];
    }

    /**
     * Get summary statistics.
     */
    public function getSummary(): array
    {
        $results = $this->results()->where('goal', $this->primary_goal)->get();

        $controlResult = $results->firstWhere('variant_id', $this->getControlVariant()?->id);

        return [
            'total_impressions' => $results->sum('total_impressions'),
            'total_conversions' => $results->sum('total_conversions'),
            'variants' => $results->map(fn ($r) => [
                'variant_id' => $r->variant_id,
                'impressions' => $r->total_impressions,
                'conversions' => $r->total_conversions,
                'conversion_rate' => $r->conversion_rate,
                'lift' => $r->lift_vs_control,
                'confidence' => $r->confidence,
                'is_significant' => $r->is_significant,
                'is_winner' => $r->is_winner,
            ]),
            'has_winner' => $results->where('is_winner', true)->isNotEmpty(),
            'control_rate' => $controlResult?->conversion_rate ?? 0,
        ];
    }
}
