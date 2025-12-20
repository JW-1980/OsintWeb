<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Event Model
 *
 * Represents an OSINT event on the map.
 *
 * @property int $id
 * @property string $uuid
 * @property int $event_type_id
 * @property int $user_id
 * @property string $title
 * @property string|null $description
 * @property \Illuminate\Database\Query\Expression|null $location
 * @property float|null $latitude
 * @property float|null $longitude
 * @property string|null $location_name
 * @property \Carbon\Carbon $occurred_at
 * @property int|null $confidence_score
 * @property string $status
 * @property bool $is_verified
 * @property \Carbon\Carbon|null $verified_at
 * @property int|null $verified_by
 * @property array|null $custom_fields
 * @property int $views_count
 * @property int $upvotes_count
 * @property int $downvotes_count
 * @property array|null $tags
 * @property array|null $metadata
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class Event extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'event_type_id',
        'user_id',
        'title',
        'description',
        'location',
        'latitude',
        'longitude',
        'location_name',
        'occurred_at',
        'confidence_score',
        'status',
        'is_verified',
        'verified_at',
        'verified_by',
        'custom_fields',
        'views_count',
        'upvotes_count',
        'downvotes_count',
        'tags',
        'metadata',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'confidence_score' => 'integer',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
        'custom_fields' => 'array',
        'views_count' => 'integer',
        'upvotes_count' => 'integer',
        'downvotes_count' => 'integer',
        'tags' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Get the event type
     */
    public function eventType(): BelongsTo
    {
        return $this->belongsTo(EventType::class);
    }

    /**
     * Get the user who created this event
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who verified this event
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Get the media associated with this event
     */
    public function media(): HasMany
    {
        return $this->hasMany(EventMedia::class);
    }

    /**
     * Get the sources for this event
     */
    public function sources(): HasMany
    {
        return $this->hasMany(EventSource::class);
    }

    /**
     * Get the equipment involved in this event
     */
    public function equipment(): HasMany
    {
        return $this->hasMany(EventEquipment::class);
    }

    /**
     * Get the actors involved in this event
     */
    public function actors(): BelongsToMany
    {
        return $this->belongsToMany(Actor::class, 'actor_event')
            ->withPivot(['role', 'notes'])
            ->withTimestamps();
    }

    /**
     * Scope to filter verified events
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope to filter by status
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter published events
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope to filter by event type
     */
    public function scopeEventType($query, int $eventTypeId)
    {
        return $query->where('event_type_id', $eventTypeId);
    }

    /**
     * Scope to filter by date range
     */
    public function scopeDateRange($query, $from, $to)
    {
        return $query->whereBetween('occurred_at', [$from, $to]);
    }

    /**
     * Scope to filter by bounding box
     */
    public function scopeWithinBounds($query, float $swLat, float $swLng, float $neLat, float $neLng)
    {
        return $query->whereBetween('latitude', [$swLat, $neLat])
            ->whereBetween('longitude', [$swLng, $neLng]);
    }

    /**
     * Scope to search by title, description, or location
     */
    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('title', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%")
                ->orWhere('location_name', 'like', "%{$term}%");
        });
    }

    /**
     * Scope to order by popularity
     */
    public function scopePopular($query)
    {
        return $query->orderByRaw('(upvotes_count - downvotes_count) DESC');
    }

    /**
     * Get coordinates as array
     */
    public function getCoordinates(): ?array
    {
        if ($this->latitude === null || $this->longitude === null) {
            return null;
        }

        return [
            'lat' => (float) $this->latitude,
            'lng' => (float) $this->longitude,
        ];
    }

    /**
     * Set location from coordinates
     */
    public function setLocationFromCoordinates(float $latitude, float $longitude): void
    {
        $this->latitude = $latitude;
        $this->longitude = $longitude;

        // Set PostGIS POINT if using spatial columns
        // $this->location = DB::raw("ST_GeomFromText('POINT({$longitude} {$latitude})', 4326)");
    }
}
