<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * NetworkRelationship Model
 *
 * Relationships/edges between network entities in analysis graphs.
 *
 * Database Schema:
 * @property int $id Primary key
 * @property int $source_id Foreign key to network_entities table (source node)
 * @property int $target_id Foreign key to network_entities table (target node)
 * @property string $relationship_type Type of relationship (communicates_with, member_of, etc.)
 * @property string $direction Relationship direction (bidirectional, directed)
 * @property float|null $strength Relationship strength/weight 0.00-1.00
 * @property array|null $metadata Additional relationship metadata (JSON)
 * @property string|null $notes Notes about the relationship
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property-read NetworkEntity $source
 * @property-read NetworkEntity $target
 */
class NetworkRelationship extends Model
{
    public const TYPE_COMMUNICATES_WITH = 'communicates_with';
    public const TYPE_MEMBER_OF = 'member_of';
    public const TYPE_LOCATED_AT = 'located_at';
    public const TYPE_OWNS = 'owns';
    public const TYPE_REPORTS_TO = 'reports_to';
    public const TYPE_ASSOCIATED_WITH = 'associated_with';

    public const DIRECTION_BIDIRECTIONAL = 'bidirectional';
    public const DIRECTION_DIRECTED = 'directed';

    protected $fillable = [
        'source_id',
        'target_id',
        'relationship_type',
        'direction',
        'strength',
        'metadata',
        'notes',
    ];

    protected $casts = [
        'strength' => 'decimal:2',
        'metadata' => 'array',
    ];

    public function source(): BelongsTo
    {
        return $this->belongsTo(NetworkEntity::class, 'source_id');
    }

    public function target(): BelongsTo
    {
        return $this->belongsTo(NetworkEntity::class, 'target_id');
    }

    public function isBidirectional(): bool
    {
        return $this->direction === self::DIRECTION_BIDIRECTIONAL;
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('relationship_type', $type);
    }

    public function scopeStrong($query, float $threshold = 0.7)
    {
        return $query->where('strength', '>=', $threshold);
    }

    public static function getTypes(): array
    {
        return [
            self::TYPE_COMMUNICATES_WITH => 'Communicates With',
            self::TYPE_MEMBER_OF => 'Member Of',
            self::TYPE_LOCATED_AT => 'Located At',
            self::TYPE_OWNS => 'Owns',
            self::TYPE_REPORTS_TO => 'Reports To',
            self::TYPE_ASSOCIATED_WITH => 'Associated With',
        ];
    }
}
