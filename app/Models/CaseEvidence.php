<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * CaseEvidence Model - Links evidence items to investigation cases
 *
 * Supports polymorphic relationships to various evidence types:
 * - Events (incidents, observations)
 * - Documents (uploaded files, OCR results)
 * - Preserved evidence (archived web content)
 * - Social media posts
 * - Audio/video analyses
 * - Any other evidential content
 *
 * @property int $id
 * @property int $case_id Foreign key to cases table
 * @property string $evidence_type Polymorphic type (e.g., App\Models\Event)
 * @property int $evidence_id Polymorphic ID
 * @property int $relevance_score Relevance rating 1-5 (5 = most relevant)
 * @property string|null $notes Analyst notes about this evidence
 * @property string $status Review workflow status
 * @property int|null $added_by User who linked this evidence
 * @property \Carbon\Carbon $added_at When evidence was linked
 * @property int|null $reviewed_by User who reviewed this evidence
 * @property \Carbon\Carbon|null $reviewed_at When evidence was reviewed
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property-read InvestigationCase $case
 * @property-read Model $evidence Polymorphic evidence item
 * @property-read User|null $addedBy
 * @property-read User|null $reviewedBy
 */
class CaseEvidence extends Model
{
    // Status constants
    public const STATUS_PENDING_REVIEW = 'pending_review';
    public const STATUS_VERIFIED = 'verified';
    public const STATUS_DISPUTED = 'disputed';
    public const STATUS_EXCLUDED = 'excluded';

    // Relevance score constants
    public const RELEVANCE_LOW = 1;
    public const RELEVANCE_MEDIUM_LOW = 2;
    public const RELEVANCE_MEDIUM = 3;
    public const RELEVANCE_MEDIUM_HIGH = 4;
    public const RELEVANCE_HIGH = 5;

    protected $table = 'case_evidence';

    protected $fillable = [
        'case_id',
        'evidence_type',
        'evidence_id',
        'relevance_score',
        'notes',
        'status',
        'added_by',
        'added_at',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'relevance_score' => 'integer',
        'added_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    protected $attributes = [
        'relevance_score' => self::RELEVANCE_MEDIUM,
        'status' => self::STATUS_PENDING_REVIEW,
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->added_by) && auth()->check()) {
                $model->added_by = auth()->id();
            }
            if (empty($model->added_at)) {
                $model->added_at = now();
            }
        });
    }

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    /**
     * Get the case this evidence belongs to
     */
    public function case(): BelongsTo
    {
        return $this->belongsTo(InvestigationCase::class, 'case_id');
    }

    /**
     * Get the polymorphic evidence item
     */
    public function evidence(): MorphTo
    {
        return $this->morphTo('evidence');
    }

    /**
     * Get the user who added this evidence
     */
    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    /**
     * Get the user who reviewed this evidence
     */
    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // =========================================================================
    // STATUS METHODS
    // =========================================================================

    /**
     * Mark evidence as verified
     */
    public function verify(?int $reviewerId = null): void
    {
        $this->update([
            'status' => self::STATUS_VERIFIED,
            'reviewed_by' => $reviewerId ?? auth()->id(),
            'reviewed_at' => now(),
        ]);
    }

    /**
     * Mark evidence as disputed
     */
    public function dispute(?int $reviewerId = null): void
    {
        $this->update([
            'status' => self::STATUS_DISPUTED,
            'reviewed_by' => $reviewerId ?? auth()->id(),
            'reviewed_at' => now(),
        ]);
    }

    /**
     * Exclude evidence from the case
     */
    public function exclude(?int $reviewerId = null): void
    {
        $this->update([
            'status' => self::STATUS_EXCLUDED,
            'reviewed_by' => $reviewerId ?? auth()->id(),
            'reviewed_at' => now(),
        ]);
    }

    /**
     * Reset evidence to pending review
     */
    public function resetReview(): void
    {
        $this->update([
            'status' => self::STATUS_PENDING_REVIEW,
            'reviewed_by' => null,
            'reviewed_at' => null,
        ]);
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    /**
     * Scope to filter by case
     */
    public function scopeForCase($query, int $caseId)
    {
        return $query->where('case_id', $caseId);
    }

    /**
     * Scope to filter by evidence type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('evidence_type', $type);
    }

    /**
     * Scope to filter by status
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter verified evidence
     */
    public function scopeVerified($query)
    {
        return $query->where('status', self::STATUS_VERIFIED);
    }

    /**
     * Scope to filter pending evidence
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING_REVIEW);
    }

    /**
     * Scope to filter by minimum relevance
     */
    public function scopeMinRelevance($query, int $minScore)
    {
        return $query->where('relevance_score', '>=', $minScore);
    }

    /**
     * Scope to order by relevance
     */
    public function scopeByRelevance($query, string $direction = 'desc')
    {
        return $query->orderBy('relevance_score', $direction);
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    /**
     * Check if evidence is pending review
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING_REVIEW;
    }

    /**
     * Check if evidence is verified
     */
    public function isVerified(): bool
    {
        return $this->status === self::STATUS_VERIFIED;
    }

    /**
     * Check if evidence is disputed
     */
    public function isDisputed(): bool
    {
        return $this->status === self::STATUS_DISPUTED;
    }

    /**
     * Check if evidence is excluded
     */
    public function isExcluded(): bool
    {
        return $this->status === self::STATUS_EXCLUDED;
    }

    /**
     * Check if evidence has been reviewed
     */
    public function hasBeenReviewed(): bool
    {
        return $this->reviewed_at !== null;
    }

    /**
     * Get human-readable relevance label
     */
    public function getRelevanceLabel(): string
    {
        return match ($this->relevance_score) {
            self::RELEVANCE_LOW => 'Low',
            self::RELEVANCE_MEDIUM_LOW => 'Medium-Low',
            self::RELEVANCE_MEDIUM => 'Medium',
            self::RELEVANCE_MEDIUM_HIGH => 'Medium-High',
            self::RELEVANCE_HIGH => 'High',
            default => 'Unknown',
        };
    }

    // =========================================================================
    // STATIC REFERENCE DATA
    // =========================================================================

    /**
     * Get available statuses
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING_REVIEW => 'Pending Review',
            self::STATUS_VERIFIED => 'Verified',
            self::STATUS_DISPUTED => 'Disputed',
            self::STATUS_EXCLUDED => 'Excluded',
        ];
    }

    /**
     * Get available relevance scores
     */
    public static function getRelevanceScores(): array
    {
        return [
            self::RELEVANCE_LOW => 'Low (1)',
            self::RELEVANCE_MEDIUM_LOW => 'Medium-Low (2)',
            self::RELEVANCE_MEDIUM => 'Medium (3)',
            self::RELEVANCE_MEDIUM_HIGH => 'Medium-High (4)',
            self::RELEVANCE_HIGH => 'High (5)',
        ];
    }

    /**
     * Get supported evidence types with their model classes
     */
    public static function getSupportedTypes(): array
    {
        return [
            'event' => Event::class,
            'document' => DocumentAnalysis::class,
            'preserved_evidence' => PreservedEvidence::class,
            'social_media_post' => SocialMediaPost::class,
            'audio_analysis' => AudioAnalysis::class,
            'video_analysis' => VideoAnalysis::class,
            'reverse_image_search' => ReverseImageSearch::class,
            'geolocation' => GeolocationVerification::class,
        ];
    }
}
