<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * SourceCrossReference Model
 *
 * Represents a relationship between two sources for verification purposes.
 *
 * @property int $id
 * @property int $source_id
 * @property int $referenced_source_id
 * @property string $relationship_type
 * @property int $confidence_score
 * @property string|null $notes
 * @property int|null $created_by
 * @property int|null $verified_by
 * @property \Carbon\Carbon|null $verified_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class SourceCrossReference extends Model
{
    use HasFactory;

    // Relationship type constants
    public const TYPE_CORROBORATES = 'corroborates';
    public const TYPE_CONTRADICTS = 'contradicts';
    public const TYPE_CITES = 'cites';
    public const TYPE_REPUBLISHES = 'republishes';
    public const TYPE_RELATED = 'related';
    public const TYPE_PARENT_ORGANIZATION = 'parent_organization';
    public const TYPE_SUBSIDIARY = 'subsidiary';
    public const TYPE_PARTNER = 'partner';

    protected $fillable = [
        'source_id',
        'referenced_source_id',
        'relationship_type',
        'confidence_score',
        'notes',
        'created_by',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'confidence_score' => 'integer',
        'verified_at' => 'datetime',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /**
     * Get the primary source in this reference
     */
    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class, 'source_id');
    }

    /**
     * Get the referenced source
     */
    public function referencedSource(): BelongsTo
    {
        return $this->belongsTo(Source::class, 'referenced_source_id');
    }

    /**
     * Get the user who created this cross-reference
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who verified this cross-reference
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    /**
     * Scope to filter by relationship type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('relationship_type', $type);
    }

    /**
     * Scope to filter corroborating relationships
     */
    public function scopeCorroborating($query)
    {
        return $query->where('relationship_type', self::TYPE_CORROBORATES);
    }

    /**
     * Scope to filter contradicting relationships
     */
    public function scopeContradicting($query)
    {
        return $query->where('relationship_type', self::TYPE_CONTRADICTS);
    }

    /**
     * Scope to filter by minimum confidence score
     */
    public function scopeMinConfidence($query, int $minScore)
    {
        return $query->where('confidence_score', '>=', $minScore);
    }

    /**
     * Scope to filter high confidence relationships (70+)
     */
    public function scopeHighConfidence($query)
    {
        return $query->where('confidence_score', '>=', 70);
    }

    /**
     * Scope to filter verified relationships
     */
    public function scopeVerified($query)
    {
        return $query->whereNotNull('verified_at');
    }

    /**
     * Scope to filter unverified relationships
     */
    public function scopeUnverified($query)
    {
        return $query->whereNull('verified_at');
    }

    /**
     * Scope to get references involving a specific source
     */
    public function scopeInvolvingSource($query, int $sourceId)
    {
        return $query->where(function ($q) use ($sourceId) {
            $q->where('source_id', $sourceId)
                ->orWhere('referenced_source_id', $sourceId);
        });
    }

    // -------------------------------------------------------------------------
    // Methods
    // -------------------------------------------------------------------------

    /**
     * Mark this cross-reference as verified
     */
    public function markAsVerified(User $verifier): void
    {
        $this->update([
            'verified_by' => $verifier->id,
            'verified_at' => now(),
        ]);
    }

    /**
     * Check if this reference is verified
     */
    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }

    /**
     * Get confidence level as human-readable string
     */
    public function getConfidenceLevel(): string
    {
        return match (true) {
            $this->confidence_score >= 85 => 'very_high',
            $this->confidence_score >= 70 => 'high',
            $this->confidence_score >= 50 => 'medium',
            $this->confidence_score >= 30 => 'low',
            default => 'very_low',
        };
    }

    /**
     * Check if this is a supporting relationship (corroborates, cites, etc.)
     */
    public function isSupportingRelationship(): bool
    {
        return in_array($this->relationship_type, [
            self::TYPE_CORROBORATES,
            self::TYPE_CITES,
            self::TYPE_REPUBLISHES,
            self::TYPE_RELATED,
        ]);
    }

    /**
     * Check if this is a conflicting relationship
     */
    public function isConflictingRelationship(): bool
    {
        return $this->relationship_type === self::TYPE_CONTRADICTS;
    }

    /**
     * Check if this is an organizational relationship
     */
    public function isOrganizationalRelationship(): bool
    {
        return in_array($this->relationship_type, [
            self::TYPE_PARENT_ORGANIZATION,
            self::TYPE_SUBSIDIARY,
            self::TYPE_PARTNER,
        ]);
    }

    /**
     * Get all available relationship types
     */
    public static function getRelationshipTypes(): array
    {
        return [
            self::TYPE_CORROBORATES,
            self::TYPE_CONTRADICTS,
            self::TYPE_CITES,
            self::TYPE_REPUBLISHES,
            self::TYPE_RELATED,
            self::TYPE_PARENT_ORGANIZATION,
            self::TYPE_SUBSIDIARY,
            self::TYPE_PARTNER,
        ];
    }

    /**
     * Get relationship type labels for display
     */
    public static function getRelationshipTypeLabels(): array
    {
        return [
            self::TYPE_CORROBORATES => 'Corroborates',
            self::TYPE_CONTRADICTS => 'Contradicts',
            self::TYPE_CITES => 'Cites',
            self::TYPE_REPUBLISHES => 'Republishes',
            self::TYPE_RELATED => 'Related',
            self::TYPE_PARENT_ORGANIZATION => 'Parent Organization',
            self::TYPE_SUBSIDIARY => 'Subsidiary',
            self::TYPE_PARTNER => 'Partner',
        ];
    }
}
