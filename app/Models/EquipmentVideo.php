<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * EquipmentVideo Model
 *
 * Represents an embedded video for military equipment.
 *
 * @property int $id
 * @property string $uuid
 * @property int $equipment_id
 * @property string $url
 * @property string|null $embed_url
 * @property string $platform
 * @property string|null $video_id
 * @property string $title
 * @property string|null $description
 * @property string|null $thumbnail_url
 * @property int|null $duration
 * @property string|null $channel_name
 * @property string|null $channel_url
 * @property \Carbon\Carbon|null $published_at
 * @property bool $is_public
 * @property bool $is_verified
 * @property int|null $added_by
 * @property int $sort_order
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class EquipmentVideo extends Model
{
    use HasFactory, SoftDeletes;

    public const PLATFORM_YOUTUBE = 'youtube';
    public const PLATFORM_VIMEO = 'vimeo';
    public const PLATFORM_DAILYMOTION = 'dailymotion';
    public const PLATFORM_TWITTER = 'twitter';
    public const PLATFORM_OTHER = 'other';

    protected $fillable = [
        'uuid',
        'equipment_id',
        'url',
        'embed_url',
        'platform',
        'video_id',
        'title',
        'description',
        'thumbnail_url',
        'duration',
        'channel_name',
        'channel_url',
        'published_at',
        'is_public',
        'is_verified',
        'added_by',
        'sort_order',
    ];

    protected $casts = [
        'duration' => 'integer',
        'published_at' => 'date',
        'is_public' => 'boolean',
        'is_verified' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (EquipmentVideo $video) {
            if (empty($video->uuid)) {
                $video->uuid = (string) Str::uuid();
            }
            // Auto-detect platform and video ID
            $video->detectPlatform();
        });
    }

    /**
     * Detect the platform and extract video ID from URL
     */
    public function detectPlatform(): void
    {
        $url = $this->url;

        // YouTube
        if (preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $url, $matches)) {
            $this->platform = self::PLATFORM_YOUTUBE;
            $this->video_id = $matches[1];
            $this->embed_url = "https://www.youtube.com/embed/{$matches[1]}";
            $this->thumbnail_url = $this->thumbnail_url ?? "https://img.youtube.com/vi/{$matches[1]}/maxresdefault.jpg";
        }
        // Vimeo
        elseif (preg_match('/vimeo\.com\/(\d+)/', $url, $matches)) {
            $this->platform = self::PLATFORM_VIMEO;
            $this->video_id = $matches[1];
            $this->embed_url = "https://player.vimeo.com/video/{$matches[1]}";
        }
        // Dailymotion
        elseif (preg_match('/dailymotion\.com\/video\/([a-zA-Z0-9]+)/', $url, $matches)) {
            $this->platform = self::PLATFORM_DAILYMOTION;
            $this->video_id = $matches[1];
            $this->embed_url = "https://www.dailymotion.com/embed/video/{$matches[1]}";
        }
        // Twitter/X
        elseif (preg_match('/(?:twitter\.com|x\.com)\/\w+\/status\/(\d+)/', $url, $matches)) {
            $this->platform = self::PLATFORM_TWITTER;
            $this->video_id = $matches[1];
        }
    }

    /**
     * Get the equipment this video belongs to
     */
    public function equipment(): BelongsTo
    {
        return $this->belongsTo(MilitaryEquipment::class, 'equipment_id');
    }

    /**
     * Get the user who added this video
     */
    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    /**
     * Get formatted duration (MM:SS or HH:MM:SS)
     */
    public function getFormattedDurationAttribute(): ?string
    {
        if ($this->duration === null) {
            return null;
        }

        $hours = floor($this->duration / 3600);
        $minutes = floor(($this->duration % 3600) / 60);
        $seconds = $this->duration % 60;

        if ($hours > 0) {
            return sprintf('%d:%02d:%02d', $hours, $minutes, $seconds);
        }

        return sprintf('%d:%02d', $minutes, $seconds);
    }

    /**
     * Scope to filter by platform
     */
    public function scopePlatform($query, string $platform)
    {
        return $query->where('platform', $platform);
    }

    /**
     * Scope to filter public videos
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope to order by sort order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Get all available platforms
     */
    public static function getPlatforms(): array
    {
        return [
            self::PLATFORM_YOUTUBE,
            self::PLATFORM_VIMEO,
            self::PLATFORM_DAILYMOTION,
            self::PLATFORM_TWITTER,
            self::PLATFORM_OTHER,
        ];
    }
}
