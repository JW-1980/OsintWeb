<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Event Source Model
 *
 * Represents a source reference for an event.
 *
 * @property int $id
 * @property string $uuid
 * @property int $event_id
 * @property string $type
 * @property string $url
 * @property string|null $title
 * @property string|null $description
 * @property string|null $author
 * @property \Carbon\Carbon|null $published_at
 * @property string|null $archive_url
 * @property int $reliability_score
 * @property bool $is_verified
 * @property int $sort_order
 * @property array|null $metadata
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class EventSource extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'event_id',
        'type',
        'url',
        'title',
        'description',
        'author',
        'published_at',
        'archive_url',
        'reliability_score',
        'is_verified',
        'sort_order',
        'metadata',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'reliability_score' => 'integer',
        'is_verified' => 'boolean',
        'sort_order' => 'integer',
        'metadata' => 'array',
    ];

    /**
     * Get the event this source belongs to
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Scope to filter by type
     */
    public function scopeType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to filter verified sources
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope to filter by minimum reliability
     */
    public function scopeReliable($query, int $minScore = 7)
    {
        return $query->where('reliability_score', '>=', $minScore);
    }

    /**
     * Scope to order by sort order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Scope to order by reliability
     */
    public function scopeByReliability($query)
    {
        return $query->orderBy('reliability_score', 'desc');
    }
}
