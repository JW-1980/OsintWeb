<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Disinformation Analysis Model
 *
 * Stores comprehensive analysis results for detecting disinformation campaigns,
 * coordinated behavior, propaganda, and manipulation across various content types.
 *
 * Database Schema (disinformation_analyses):
 * @property int $id Primary key
 * @property string $uuid Public-facing unique identifier
 * @property string $analyzable_type Polymorphic type (Event, Article, SocialMediaPost)
 * @property int $analyzable_id Polymorphic ID
 * @property string $analysis_type Type of analysis (coordinated_behavior, propaganda, fake_account, manipulated_media, false_narrative)
 * @property int $threat_score Threat level score (0-100)
 * @property int $confidence_score Analysis confidence score (0-100)
 * @property array|null $indicators JSON array of detected pattern indicators
 * @property array|null $network_analysis Related accounts/sources if coordinated behavior
 * @property array|null $temporal_patterns Posting time patterns and anomalies
 * @property array|null $content_patterns Repeated phrases, hashtags, narratives
 * @property array|null $source_credibility Source reliability and track record data
 * @property array|null $ai_analysis AI model output and reasoning
 * @property string $manual_review_status Review status (pending, confirmed, rejected, needs_review)
 * @property int|null $reviewed_by User ID of reviewer
 * @property \Carbon\Carbon|null $reviewed_at When review was completed
 * @property string|null $review_notes Notes from manual review
 * @property string $analysis_status Processing status (pending, processing, completed, failed)
 * @property string|null $error_message Error message if analysis failed
 * @property int $created_by User ID of analyst who created this
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 *
 * Relationships:
 * @property-read Model $analyzable The polymorphic analyzed content
 * @property-read User $creator The user who created this analysis
 * @property-read User|null $reviewer The user who reviewed this analysis
 * @property-read \Illuminate\Database\Eloquent\Collection|DisinformationPattern[] $patterns Matched patterns
 * @property-read \Illuminate\Database\Eloquent\Collection|FlaggedContent[] $flaggedContent Content flagged from this analysis
 */
class DisinformationAnalysis extends Model
{
    use HasFactory, SoftDeletes;

    // Analysis type constants
    public const TYPE_COORDINATED_BEHAVIOR = 'coordinated_behavior';
    public const TYPE_PROPAGANDA = 'propaganda';
    public const TYPE_FAKE_ACCOUNT = 'fake_account';
    public const TYPE_MANIPULATED_MEDIA = 'manipulated_media';
    public const TYPE_FALSE_NARRATIVE = 'false_narrative';

    // Manual review status constants
    public const REVIEW_PENDING = 'pending';
    public const REVIEW_CONFIRMED = 'confirmed';
    public const REVIEW_REJECTED = 'rejected';
    public const REVIEW_NEEDS_REVIEW = 'needs_review';

    // Analysis status constants
    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    // Threat level thresholds
    public const THREAT_LOW_MAX = 25;
    public const THREAT_MEDIUM_MAX = 50;
    public const THREAT_HIGH_MAX = 75;
    public const THREAT_CRITICAL_MIN = 76;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'uuid',
        'analyzable_type',
        'analyzable_id',
        'analysis_type',
        'threat_score',
        'confidence_score',
        'indicators',
        'network_analysis',
        'temporal_patterns',
        'content_patterns',
        'source_credibility',
        'ai_analysis',
        'manual_review_status',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
        'analysis_status',
        'error_message',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'threat_score' => 'integer',
        'confidence_score' => 'integer',
        'indicators' => 'array',
        'network_analysis' => 'array',
        'temporal_patterns' => 'array',
        'content_patterns' => 'array',
        'source_credibility' => 'array',
        'ai_analysis' => 'array',
        'reviewed_at' => 'datetime',
    ];

    /**
     * Get all analysis types.
     *
     * @return array<string>
     */
    public static function getAnalysisTypes(): array
    {
        return [
            self::TYPE_COORDINATED_BEHAVIOR,
            self::TYPE_PROPAGANDA,
            self::TYPE_FAKE_ACCOUNT,
            self::TYPE_MANIPULATED_MEDIA,
            self::TYPE_FALSE_NARRATIVE,
        ];
    }

    /**
     * Get analysis type labels.
     *
     * @return array<string, string>
     */
    public static function getAnalysisTypeLabels(): array
    {
        return [
            self::TYPE_COORDINATED_BEHAVIOR => 'Coordinated Inauthentic Behavior',
            self::TYPE_PROPAGANDA => 'Propaganda',
            self::TYPE_FAKE_ACCOUNT => 'Fake Account',
            self::TYPE_MANIPULATED_MEDIA => 'Manipulated Media',
            self::TYPE_FALSE_NARRATIVE => 'False Narrative',
        ];
    }

    /**
     * Get all review statuses.
     *
     * @return array<string>
     */
    public static function getReviewStatuses(): array
    {
        return [
            self::REVIEW_PENDING,
            self::REVIEW_CONFIRMED,
            self::REVIEW_REJECTED,
            self::REVIEW_NEEDS_REVIEW,
        ];
    }

    /**
     * Get all analysis statuses.
     *
     * @return array<string>
     */
    public static function getAnalysisStatuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_PROCESSING,
            self::STATUS_COMPLETED,
            self::STATUS_FAILED,
        ];
    }

    /**
     * Get the analyzed content (polymorphic).
     */
    public function analyzable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user who created this analysis.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who reviewed this analysis.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Get the matched patterns.
     */
    public function patterns(): BelongsToMany
    {
        return $this->belongsToMany(DisinformationPattern::class, 'analysis_pattern')
            ->withPivot(['match_details', 'match_confidence'])
            ->withTimestamps();
    }

    /**
     * Get flagged content from this analysis.
     */
    public function flaggedContent(): HasMany
    {
        return $this->hasMany(FlaggedContent::class);
    }

    /**
     * Get threat level label.
     *
     * @return string
     */
    public function getThreatLevelAttribute(): string
    {
        if ($this->threat_score <= self::THREAT_LOW_MAX) {
            return 'low';
        }
        if ($this->threat_score <= self::THREAT_MEDIUM_MAX) {
            return 'medium';
        }
        if ($this->threat_score <= self::THREAT_HIGH_MAX) {
            return 'high';
        }
        return 'critical';
    }

    /**
     * Get threat level color.
     *
     * @return string
     */
    public function getThreatColorAttribute(): string
    {
        return match ($this->threat_level) {
            'low' => '#22c55e',
            'medium' => '#f59e0b',
            'high' => '#ef4444',
            'critical' => '#b91c1c',
            default => '#94a3b8',
        };
    }

    /**
     * Get analysis type label.
     *
     * @return string
     */
    public function getAnalysisTypeLabel(): string
    {
        return self::getAnalysisTypeLabels()[$this->analysis_type] ?? ucfirst($this->analysis_type);
    }

    /**
     * Check if analysis is completed.
     *
     * @return bool
     */
    public function isCompleted(): bool
    {
        return $this->analysis_status === self::STATUS_COMPLETED;
    }

    /**
     * Check if analysis failed.
     *
     * @return bool
     */
    public function isFailed(): bool
    {
        return $this->analysis_status === self::STATUS_FAILED;
    }

    /**
     * Check if analysis is pending review.
     *
     * @return bool
     */
    public function isPendingReview(): bool
    {
        return $this->manual_review_status === self::REVIEW_PENDING;
    }

    /**
     * Check if confirmed as disinformation.
     *
     * @return bool
     */
    public function isConfirmedDisinformation(): bool
    {
        return $this->manual_review_status === self::REVIEW_CONFIRMED;
    }

    /**
     * Check if high threat.
     *
     * @return bool
     */
    public function isHighThreat(): bool
    {
        return $this->threat_score >= self::THREAT_HIGH_MAX;
    }

    /**
     * Mark analysis as processing.
     */
    public function markAsProcessing(): void
    {
        $this->update([
            'analysis_status' => self::STATUS_PROCESSING,
        ]);
    }

    /**
     * Mark analysis as completed.
     *
     * @param array $results Analysis results
     */
    public function markAsCompleted(array $results): void
    {
        $this->update(array_merge($results, [
            'analysis_status' => self::STATUS_COMPLETED,
            'error_message' => null,
        ]));
    }

    /**
     * Mark analysis as failed.
     *
     * @param string $errorMessage Error message
     */
    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'analysis_status' => self::STATUS_FAILED,
            'error_message' => $errorMessage,
        ]);
    }

    /**
     * Submit for review.
     *
     * @param int $userId Reviewer user ID
     * @param string $status Review status
     * @param string|null $notes Review notes
     */
    public function submitReview(int $userId, string $status, ?string $notes = null): void
    {
        $this->update([
            'reviewed_by' => $userId,
            'reviewed_at' => now(),
            'manual_review_status' => $status,
            'review_notes' => $notes,
        ]);
    }

    /**
     * Add a matched pattern.
     *
     * @param int $patternId Pattern ID
     * @param array $matchDetails Match details
     * @param int $confidence Confidence score
     */
    public function addPattern(int $patternId, array $matchDetails = [], int $confidence = 0): void
    {
        $this->patterns()->attach($patternId, [
            'match_details' => json_encode($matchDetails),
            'match_confidence' => $confidence,
        ]);
    }

    /**
     * Get total number of indicators.
     *
     * @return int
     */
    public function getIndicatorCount(): int
    {
        return count($this->indicators ?? []);
    }

    /**
     * Scope for pending analyses.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query)
    {
        return $query->where('analysis_status', self::STATUS_PENDING);
    }

    /**
     * Scope for completed analyses.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCompleted($query)
    {
        return $query->where('analysis_status', self::STATUS_COMPLETED);
    }

    /**
     * Scope for high threat analyses.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeHighThreat($query)
    {
        return $query->where('threat_score', '>=', self::THREAT_HIGH_MAX);
    }

    /**
     * Scope for analyses pending review.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePendingReview($query)
    {
        return $query->where('manual_review_status', self::REVIEW_PENDING)
            ->where('analysis_status', self::STATUS_COMPLETED);
    }

    /**
     * Scope for confirmed disinformation.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeConfirmed($query)
    {
        return $query->where('manual_review_status', self::REVIEW_CONFIRMED);
    }

    /**
     * Scope for specific analysis type.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('analysis_type', $type);
    }

    /**
     * Scope for analyses by user.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('created_by', $userId);
    }

    /**
     * Scope for threat level range.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $min
     * @param int $max
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeThreatRange($query, int $min, int $max)
    {
        return $query->whereBetween('threat_score', [$min, $max]);
    }

    /**
     * Scope for recent analyses.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $days
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope for analyses of specific content type.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForContentType($query, string $type)
    {
        return $query->where('analyzable_type', $type);
    }
}
