<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * Source Model
 *
 * Represents a verified news source or information outlet for OSINT verification.
 *
 * @property int $id
 * @property string $uuid
 * @property string $name
 * @property string|null $url
 * @property string|null $description
 * @property int $reliability_score
 * @property string $verification_status
 * @property string $source_type
 * @property int|null $country_id
 * @property \Carbon\Carbon|null $last_verified_at
 * @property int|null $verified_by
 * @property int $total_reports
 * @property int $accurate_reports
 * @property int $disputed_reports
 * @property array|null $metadata
 * @property array|null $contact_info
 * @property array|null $known_biases
 * @property string|null $language
 * @property string|null $logo_url
 * @property bool $is_active
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class Source extends Model
{
    use HasFactory, SoftDeletes;

    // Verification status constants
    public const STATUS_UNVERIFIED = 'unverified';
    public const STATUS_VERIFIED = 'verified';
    public const STATUS_TRUSTED = 'trusted';
    public const STATUS_UNRELIABLE = 'unreliable';

    // Source type constants
    public const TYPE_NEWS_OUTLET = 'news_outlet';
    public const TYPE_SOCIAL_MEDIA = 'social_media';
    public const TYPE_OFFICIAL = 'official';
    public const TYPE_EYEWITNESS = 'eyewitness';
    public const TYPE_SATELLITE = 'satellite';
    public const TYPE_DOCUMENT = 'document';

    // Reliability score thresholds
    public const RELIABILITY_LOW = 30;
    public const RELIABILITY_MEDIUM = 50;
    public const RELIABILITY_HIGH = 70;
    public const RELIABILITY_EXCELLENT = 85;

    protected $fillable = [
        'uuid',
        'name',
        'url',
        'description',
        'reliability_score',
        'verification_status',
        'source_type',
        'country_id',
        'last_verified_at',
        'verified_by',
        'total_reports',
        'accurate_reports',
        'disputed_reports',
        'metadata',
        'contact_info',
        'known_biases',
        'language',
        'logo_url',
        'is_active',
    ];

    protected $casts = [
        'reliability_score' => 'integer',
        'total_reports' => 'integer',
        'accurate_reports' => 'integer',
        'disputed_reports' => 'integer',
        'metadata' => 'array',
        'contact_info' => 'array',
        'known_biases' => 'array',
        'is_active' => 'boolean',
        'last_verified_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Source $source) {
            if (empty($source->uuid)) {
                $source->uuid = (string) Str::uuid();
            }
        });
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /**
     * Get the country this source is based in
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Get the user who verified this source
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Get the events linked to this source
     */
    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'event_source_links')
            ->withPivot([
                'source_url',
                'archive_url',
                'accessed_at',
                'reliability',
                'excerpt',
                'notes',
            ])
            ->withTimestamps();
    }

    /**
     * Get cross-references where this source is the primary
     */
    public function crossReferences(): HasMany
    {
        return $this->hasMany(SourceCrossReference::class, 'source_id');
    }

    /**
     * Get cross-references where this source is the referenced one
     */
    public function referencedBy(): HasMany
    {
        return $this->hasMany(SourceCrossReference::class, 'referenced_source_id');
    }

    /**
     * Get sources that corroborate this source
     */
    public function corroboratingSources(): BelongsToMany
    {
        return $this->belongsToMany(
            self::class,
            'source_cross_references',
            'source_id',
            'referenced_source_id'
        )->wherePivot('relationship_type', 'corroborates')
            ->withPivot(['confidence_score', 'notes', 'verified_at'])
            ->withTimestamps();
    }

    /**
     * Get sources that contradict this source
     */
    public function contradictingSources(): BelongsToMany
    {
        return $this->belongsToMany(
            self::class,
            'source_cross_references',
            'source_id',
            'referenced_source_id'
        )->wherePivot('relationship_type', 'contradicts')
            ->withPivot(['confidence_score', 'notes', 'verified_at'])
            ->withTimestamps();
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    /**
     * Scope to filter verified sources
     */
    public function scopeVerified($query)
    {
        return $query->where('verification_status', self::STATUS_VERIFIED);
    }

    /**
     * Scope to filter trusted sources
     */
    public function scopeTrusted($query)
    {
        return $query->where('verification_status', self::STATUS_TRUSTED);
    }

    /**
     * Scope to filter unreliable sources
     */
    public function scopeUnreliable($query)
    {
        return $query->where('verification_status', self::STATUS_UNRELIABLE);
    }

    /**
     * Scope to filter by minimum reliability score
     */
    public function scopeByReliability($query, int $minScore)
    {
        return $query->where('reliability_score', '>=', $minScore);
    }

    /**
     * Scope to filter highly reliable sources (70+)
     */
    public function scopeHighlyReliable($query)
    {
        return $query->where('reliability_score', '>=', self::RELIABILITY_HIGH);
    }

    /**
     * Scope to filter by source type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('source_type', $type);
    }

    /**
     * Scope to filter by country
     */
    public function scopeFromCountry($query, int $countryId)
    {
        return $query->where('country_id', $countryId);
    }

    /**
     * Scope to filter active sources
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to search by name or description
     */
    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%")
                ->orWhere('url', 'like', "%{$term}%");
        });
    }

    /**
     * Scope to order by reliability
     */
    public function scopeOrderByReliability($query, string $direction = 'desc')
    {
        return $query->orderBy('reliability_score', $direction);
    }

    /**
     * Scope to filter recently verified sources
     */
    public function scopeRecentlyVerified($query, int $days = 30)
    {
        return $query->where('last_verified_at', '>=', now()->subDays($days));
    }

    /**
     * Scope to filter sources needing re-verification
     */
    public function scopeNeedsVerification($query, int $days = 90)
    {
        return $query->where(function ($q) use ($days) {
            $q->whereNull('last_verified_at')
                ->orWhere('last_verified_at', '<', now()->subDays($days));
        });
    }

    // -------------------------------------------------------------------------
    // Methods
    // -------------------------------------------------------------------------

    /**
     * Calculate and update the reliability score based on report accuracy
     */
    public function calculateReliabilityScore(): int
    {
        // Base score calculation
        if ($this->total_reports === 0) {
            // No reports yet, use default or manual score
            return $this->reliability_score;
        }

        // Calculate accuracy percentage
        $accuracyRate = ($this->accurate_reports / $this->total_reports) * 100;

        // Calculate dispute penalty (each disputed report reduces score)
        $disputePenalty = ($this->disputed_reports / max(1, $this->total_reports)) * 20;

        // Calculate base score from accuracy
        $baseScore = $accuracyRate;

        // Apply dispute penalty
        $adjustedScore = $baseScore - $disputePenalty;

        // Apply verification status bonuses/penalties
        $statusModifier = match ($this->verification_status) {
            self::STATUS_TRUSTED => 10,
            self::STATUS_VERIFIED => 5,
            self::STATUS_UNVERIFIED => 0,
            self::STATUS_UNRELIABLE => -20,
            default => 0,
        };

        $finalScore = $adjustedScore + $statusModifier;

        // Clamp score between 0 and 100
        $this->reliability_score = max(0, min(100, (int) round($finalScore)));
        $this->save();

        return $this->reliability_score;
    }

    /**
     * Create a cross-reference with another source
     */
    public function crossReferenceWith(
        Source $otherSource,
        string $relationshipType,
        int $confidenceScore = 50,
        ?string $notes = null,
        ?User $createdBy = null
    ): SourceCrossReference {
        return SourceCrossReference::create([
            'source_id' => $this->id,
            'referenced_source_id' => $otherSource->id,
            'relationship_type' => $relationshipType,
            'confidence_score' => $confidenceScore,
            'notes' => $notes,
            'created_by' => $createdBy?->id,
        ]);
    }

    /**
     * Mark this source as verified by a user
     */
    public function markAsVerified(User $verifier, string $status = self::STATUS_VERIFIED): void
    {
        $this->update([
            'verification_status' => $status,
            'verified_by' => $verifier->id,
            'last_verified_at' => now(),
        ]);

        // Log to audit
        AuditLog::create([
            'user_id' => $verifier->id,
            'action' => 'source.verified',
            'auditable_type' => self::class,
            'auditable_id' => $this->id,
            'properties' => [
                'status' => $status,
                'previous_status' => $this->getOriginal('verification_status'),
            ],
        ]);
    }

    /**
     * Mark this source as unreliable
     */
    public function markAsUnreliable(User $verifier, ?string $reason = null): void
    {
        $this->update([
            'verification_status' => self::STATUS_UNRELIABLE,
            'verified_by' => $verifier->id,
            'last_verified_at' => now(),
        ]);

        // Add reason to metadata if provided
        if ($reason) {
            $metadata = $this->metadata ?? [];
            $metadata['unreliable_reason'] = $reason;
            $metadata['marked_unreliable_at'] = now()->toIso8601String();
            $this->update(['metadata' => $metadata]);
        }

        AuditLog::create([
            'user_id' => $verifier->id,
            'action' => 'source.marked_unreliable',
            'auditable_type' => self::class,
            'auditable_id' => $this->id,
            'properties' => ['reason' => $reason],
        ]);
    }

    /**
     * Increment report statistics
     */
    public function recordReport(bool $accurate = true, bool $disputed = false): void
    {
        $this->increment('total_reports');

        if ($accurate) {
            $this->increment('accurate_reports');
        }

        if ($disputed) {
            $this->increment('disputed_reports');
        }

        // Recalculate reliability after recording
        $this->calculateReliabilityScore();
    }

    /**
     * Get reliability level as a human-readable string
     */
    public function getReliabilityLevel(): string
    {
        return match (true) {
            $this->reliability_score >= self::RELIABILITY_EXCELLENT => 'excellent',
            $this->reliability_score >= self::RELIABILITY_HIGH => 'high',
            $this->reliability_score >= self::RELIABILITY_MEDIUM => 'medium',
            $this->reliability_score >= self::RELIABILITY_LOW => 'low',
            default => 'very_low',
        };
    }

    /**
     * Check if source is considered reliable
     */
    public function isReliable(): bool
    {
        return $this->reliability_score >= self::RELIABILITY_MEDIUM
            && $this->verification_status !== self::STATUS_UNRELIABLE;
    }

    /**
     * Get all related sources through cross-references
     */
    public function getRelatedSources(): \Illuminate\Database\Eloquent\Collection
    {
        $referenced = $this->crossReferences()
            ->with('referencedSource')
            ->get()
            ->pluck('referencedSource');

        $referencedBy = $this->referencedBy()
            ->with('source')
            ->get()
            ->pluck('source');

        return $referenced->merge($referencedBy)->unique('id');
    }

    /**
     * Get the accuracy rate as a percentage
     */
    public function getAccuracyRate(): float
    {
        if ($this->total_reports === 0) {
            return 0.0;
        }

        return round(($this->accurate_reports / $this->total_reports) * 100, 2);
    }

    /**
     * Get dispute rate as a percentage
     */
    public function getDisputeRate(): float
    {
        if ($this->total_reports === 0) {
            return 0.0;
        }

        return round(($this->disputed_reports / $this->total_reports) * 100, 2);
    }

    /**
     * Get all available verification statuses
     */
    public static function getVerificationStatuses(): array
    {
        return [
            self::STATUS_UNVERIFIED,
            self::STATUS_VERIFIED,
            self::STATUS_TRUSTED,
            self::STATUS_UNRELIABLE,
        ];
    }

    /**
     * Get all available source types
     */
    public static function getSourceTypes(): array
    {
        return [
            self::TYPE_NEWS_OUTLET,
            self::TYPE_SOCIAL_MEDIA,
            self::TYPE_OFFICIAL,
            self::TYPE_EYEWITNESS,
            self::TYPE_SATELLITE,
            self::TYPE_DOCUMENT,
        ];
    }
}
