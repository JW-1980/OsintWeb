<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Actor Relationship Model
 *
 * Represents relationships between actors.
 *
 * @property int $id
 * @property string $uuid
 * @property int $actor_id
 * @property int $related_actor_id
 * @property string $relationship_type
 * @property string|null $description
 * @property \Carbon\Carbon|null $from_date
 * @property \Carbon\Carbon|null $to_date
 * @property bool $is_verified
 * @property array|null $metadata
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class ActorRelationship extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'actor_id',
        'related_actor_id',
        'relationship_type',
        'description',
        'from_date',
        'to_date',
        'is_verified',
        'metadata',
    ];

    protected $casts = [
        'from_date' => 'date',
        'to_date' => 'date',
        'is_verified' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Get the primary actor
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(Actor::class, 'actor_id');
    }

    /**
     * Get the related actor
     */
    public function relatedActor(): BelongsTo
    {
        return $this->belongsTo(Actor::class, 'related_actor_id');
    }

    /**
     * Scope to filter verified relationships
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope to filter by relationship type
     */
    public function scopeType($query, string $type)
    {
        return $query->where('relationship_type', $type);
    }

    /**
     * Scope to filter current relationships
     */
    public function scopeCurrent($query)
    {
        $now = now();
        return $query->where(function ($q) use ($now) {
            $q->where(function ($sq) use ($now) {
                $sq->whereNull('from_date')
                    ->orWhere('from_date', '<=', $now);
            })->where(function ($sq) use ($now) {
                $sq->whereNull('to_date')
                    ->orWhere('to_date', '>=', $now);
            });
        });
    }
}
