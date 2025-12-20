<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Event Media Model
 *
 * Represents media (images, videos, documents) associated with an event.
 *
 * @property int $id
 * @property string $uuid
 * @property int $event_id
 * @property string $type
 * @property string $url
 * @property string|null $thumbnail_url
 * @property string|null $original_filename
 * @property string|null $mime_type
 * @property int|null $file_size
 * @property int|null $width
 * @property int|null $height
 * @property int|null $duration
 * @property string|null $caption
 * @property string|null $credit
 * @property int $sort_order
 * @property bool $is_verified
 * @property array|null $metadata
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class EventMedia extends Model
{
    use HasFactory;

    protected $table = 'event_media';

    protected $fillable = [
        'uuid',
        'event_id',
        'type',
        'url',
        'thumbnail_url',
        'original_filename',
        'mime_type',
        'file_size',
        'width',
        'height',
        'duration',
        'caption',
        'credit',
        'sort_order',
        'is_verified',
        'metadata',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'duration' => 'integer',
        'sort_order' => 'integer',
        'is_verified' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Get the event this media belongs to
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
     * Scope to filter images
     */
    public function scopeImages($query)
    {
        return $query->where('type', 'image');
    }

    /**
     * Scope to filter videos
     */
    public function scopeVideos($query)
    {
        return $query->where('type', 'video');
    }

    /**
     * Scope to filter verified media
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope to order by sort order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Get human-readable file size
     */
    public function getFormattedFileSizeAttribute(): ?string
    {
        if ($this->file_size === null) {
            return null;
        }

        $size = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($size >= 1024 && $i < count($units) - 1) {
            $size /= 1024;
            $i++;
        }

        return round($size, 2) . ' ' . $units[$i];
    }
}
