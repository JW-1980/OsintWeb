<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

/**
 * Video Analysis Model
 *
 * Represents a video file with extracted metadata and frame analysis.
 *
 * Database Schema (video_analyses table):
 * ----------------------------------------
 * @property int $id - Primary key
 * @property string $uuid - Unique identifier for public APIs
 * @property string $original_filename - Original uploaded filename
 * @property string $stored_path - Path to stored video file
 * @property int $file_size - File size in bytes
 * @property float|null $duration_seconds - Video duration with millisecond precision
 * @property int|null $frame_count - Total number of frames
 * @property int|null $resolution_width - Video width in pixels
 * @property int|null $resolution_height - Video height in pixels
 * @property float|null $fps - Frames per second
 * @property string|null $codec - Video codec (e.g., h264, h265, vp9)
 * @property string|null $container_format - Container format (e.g., mp4, mkv, webm)
 * @property array|null $metadata - Full ffprobe output as JSON
 * @property array|null $exif_data - Extracted EXIF/XMP metadata
 * @property mixed|null $gps_coordinates - Spatial POINT for GPS location
 * @property float|null $gps_latitude - GPS latitude for non-spatial queries
 * @property float|null $gps_longitude - GPS longitude for non-spatial queries
 * @property int|null $gps_accuracy_meters - GPS accuracy in meters
 * @property \Carbon\Carbon|null $recording_datetime - When video was recorded (from metadata)
 * @property string|null $timezone - Recording timezone
 * @property array|null $device_info - Camera/device information
 * @property string $analysis_status - Status: pending, processing, completed, failed
 * @property string|null $error_message - Error message if analysis failed
 * @property int $retry_count - Number of analysis retry attempts
 * @property \Carbon\Carbon|null $analysis_started_at - When analysis started
 * @property \Carbon\Carbon|null $analysis_completed_at - When analysis completed
 * @property string|null $thumbnail_path - Path to video thumbnail
 * @property int|null $event_id - Optional linked event
 * @property int $created_by - User who uploaded the video
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 *
 * Relationships:
 * @property-read User $creator
 * @property-read Event|null $event
 * @property-read \Illuminate\Database\Eloquent\Collection|VideoKeyframe[] $keyframes
 */
class VideoAnalysis extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    protected $table = 'video_analyses';

    protected $fillable = [
        'uuid',
        'original_filename',
        'stored_path',
        'file_size',
        'duration_seconds',
        'frame_count',
        'resolution_width',
        'resolution_height',
        'fps',
        'codec',
        'container_format',
        'metadata',
        'exif_data',
        'gps_coordinates',
        'gps_latitude',
        'gps_longitude',
        'gps_accuracy_meters',
        'recording_datetime',
        'timezone',
        'device_info',
        'analysis_status',
        'error_message',
        'retry_count',
        'analysis_started_at',
        'analysis_completed_at',
        'thumbnail_path',
        'event_id',
        'created_by',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'duration_seconds' => 'decimal:3',
        'frame_count' => 'integer',
        'resolution_width' => 'integer',
        'resolution_height' => 'integer',
        'fps' => 'decimal:4',
        'metadata' => 'array',
        'exif_data' => 'array',
        'gps_latitude' => 'decimal:8',
        'gps_longitude' => 'decimal:8',
        'gps_accuracy_meters' => 'integer',
        'recording_datetime' => 'datetime',
        'device_info' => 'array',
        'retry_count' => 'integer',
        'analysis_started_at' => 'datetime',
        'analysis_completed_at' => 'datetime',
    ];

    /**
     * Get the user who created this video analysis.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the associated event.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the keyframes extracted from this video.
     */
    public function keyframes(): HasMany
    {
        return $this->hasMany(VideoKeyframe::class);
    }

    /**
     * Get keyframes marked as scene changes.
     */
    public function sceneChanges(): HasMany
    {
        return $this->hasMany(VideoKeyframe::class)
            ->where('is_scene_change', true)
            ->orderBy('timestamp_seconds');
    }

    /**
     * Get representative keyframes.
     */
    public function representativeFrames(): HasMany
    {
        return $this->hasMany(VideoKeyframe::class)
            ->where('is_representative', true)
            ->orderBy('timestamp_seconds');
    }

    /**
     * Get the video file URL.
     */
    public function getVideoUrl(): string
    {
        return Storage::disk('public')->url($this->stored_path);
    }

    /**
     * Get the thumbnail URL.
     */
    public function getThumbnailUrl(): ?string
    {
        if (!$this->thumbnail_path) {
            return null;
        }
        return Storage::disk('public')->url($this->thumbnail_path);
    }

    /**
     * Get formatted file size.
     */
    public function getFormattedFileSize(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $index = 0;

        while ($bytes >= 1024 && $index < count($units) - 1) {
            $bytes /= 1024;
            $index++;
        }

        return round($bytes, 2) . ' ' . $units[$index];
    }

    /**
     * Get formatted duration.
     */
    public function getFormattedDuration(): string
    {
        if (!$this->duration_seconds) {
            return '0:00';
        }

        $totalSeconds = (int) $this->duration_seconds;
        $hours = floor($totalSeconds / 3600);
        $minutes = floor(($totalSeconds % 3600) / 60);
        $seconds = $totalSeconds % 60;
        $milliseconds = (int) (($this->duration_seconds - $totalSeconds) * 1000);

        if ($hours > 0) {
            return sprintf('%d:%02d:%02d.%03d', $hours, $minutes, $seconds, $milliseconds);
        }

        return sprintf('%d:%02d.%03d', $minutes, $seconds, $milliseconds);
    }

    /**
     * Get resolution string.
     */
    public function getResolutionString(): ?string
    {
        if (!$this->resolution_width || !$this->resolution_height) {
            return null;
        }

        return "{$this->resolution_width}x{$this->resolution_height}";
    }

    /**
     * Get aspect ratio.
     */
    public function getAspectRatio(): ?string
    {
        if (!$this->resolution_width || !$this->resolution_height) {
            return null;
        }

        $gcd = $this->gcd($this->resolution_width, $this->resolution_height);
        $width = $this->resolution_width / $gcd;
        $height = $this->resolution_height / $gcd;

        return "{$width}:{$height}";
    }

    /**
     * Calculate greatest common divisor.
     */
    private function gcd(int $a, int $b): int
    {
        return $b === 0 ? $a : $this->gcd($b, $a % $b);
    }

    /**
     * Get GPS location as array.
     */
    public function getGpsLocation(): ?array
    {
        if ($this->gps_latitude === null || $this->gps_longitude === null) {
            return null;
        }

        return [
            'latitude' => (float) $this->gps_latitude,
            'longitude' => (float) $this->gps_longitude,
            'accuracy_meters' => $this->gps_accuracy_meters,
        ];
    }

    /**
     * Check if GPS coordinates are available.
     */
    public function hasGpsCoordinates(): bool
    {
        return $this->gps_latitude !== null && $this->gps_longitude !== null;
    }

    /**
     * Get device information summary.
     */
    public function getDeviceSummary(): ?string
    {
        if (!$this->device_info) {
            return null;
        }

        $parts = [];
        if (!empty($this->device_info['make'])) {
            $parts[] = $this->device_info['make'];
        }
        if (!empty($this->device_info['model'])) {
            $parts[] = $this->device_info['model'];
        }

        return $parts ? implode(' ', $parts) : null;
    }

    /**
     * Get all keyframes as collection.
     */
    public function getKeyframes(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->keyframes()->orderBy('timestamp_seconds')->get();
    }

    /**
     * Get keyframe at specific timestamp.
     */
    public function getKeyframeAtTimestamp(float $timestamp): ?VideoKeyframe
    {
        return $this->keyframes()
            ->where('timestamp_seconds', '<=', $timestamp)
            ->orderBy('timestamp_seconds', 'desc')
            ->first();
    }

    /**
     * Get keyframes in time range.
     */
    public function getKeyframesInRange(float $start, float $end): \Illuminate\Database\Eloquent\Collection
    {
        return $this->keyframes()
            ->whereBetween('timestamp_seconds', [$start, $end])
            ->orderBy('timestamp_seconds')
            ->get();
    }

    /**
     * Check if analysis is pending.
     */
    public function isPending(): bool
    {
        return $this->analysis_status === self::STATUS_PENDING;
    }

    /**
     * Check if analysis is in progress.
     */
    public function isProcessing(): bool
    {
        return $this->analysis_status === self::STATUS_PROCESSING;
    }

    /**
     * Check if analysis is completed.
     */
    public function isCompleted(): bool
    {
        return $this->analysis_status === self::STATUS_COMPLETED;
    }

    /**
     * Check if analysis has failed.
     */
    public function hasFailed(): bool
    {
        return $this->analysis_status === self::STATUS_FAILED;
    }

    /**
     * Mark analysis as started.
     */
    public function markAsProcessing(): void
    {
        $this->update([
            'analysis_status' => self::STATUS_PROCESSING,
            'analysis_started_at' => now(),
            'error_message' => null,
        ]);
    }

    /**
     * Mark analysis as completed.
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'analysis_status' => self::STATUS_COMPLETED,
            'analysis_completed_at' => now(),
        ]);
    }

    /**
     * Mark analysis as failed.
     */
    public function markAsFailed(string $message): void
    {
        $this->update([
            'analysis_status' => self::STATUS_FAILED,
            'error_message' => $message,
            'retry_count' => $this->retry_count + 1,
        ]);
    }

    /**
     * Reset for retry.
     */
    public function resetForRetry(): void
    {
        $this->update([
            'analysis_status' => self::STATUS_PENDING,
            'error_message' => null,
            'analysis_started_at' => null,
            'analysis_completed_at' => null,
        ]);
    }

    /**
     * Scope to filter by analysis status.
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('analysis_status', $status);
    }

    /**
     * Scope for pending analyses.
     */
    public function scopePending($query)
    {
        return $query->where('analysis_status', self::STATUS_PENDING);
    }

    /**
     * Scope for processing analyses.
     */
    public function scopeProcessing($query)
    {
        return $query->where('analysis_status', self::STATUS_PROCESSING);
    }

    /**
     * Scope for completed analyses.
     */
    public function scopeCompleted($query)
    {
        return $query->where('analysis_status', self::STATUS_COMPLETED);
    }

    /**
     * Scope for failed analyses.
     */
    public function scopeFailed($query)
    {
        return $query->where('analysis_status', self::STATUS_FAILED);
    }

    /**
     * Scope for videos with GPS data.
     */
    public function scopeWithGps($query)
    {
        return $query->whereNotNull('gps_latitude')
            ->whereNotNull('gps_longitude');
    }

    /**
     * Scope to filter by user.
     */
    public function scopeCreatedBy($query, int $userId)
    {
        return $query->where('created_by', $userId);
    }

    /**
     * Scope to filter by event.
     */
    public function scopeForEvent($query, int $eventId)
    {
        return $query->where('event_id', $eventId);
    }

    /**
     * Scope to filter by codec.
     */
    public function scopeCodec($query, string $codec)
    {
        return $query->where('codec', $codec);
    }

    /**
     * Scope to search by filename.
     */
    public function scopeSearch($query, string $term)
    {
        return $query->where('original_filename', 'like', "%{$term}%");
    }

    /**
     * Scope for videos within bounding box.
     */
    public function scopeWithinBounds($query, float $swLat, float $swLng, float $neLat, float $neLng)
    {
        return $query->whereBetween('gps_latitude', [$swLat, $neLat])
            ->whereBetween('gps_longitude', [$swLng, $neLng]);
    }

    /**
     * Get all available statuses.
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_PROCESSING,
            self::STATUS_COMPLETED,
            self::STATUS_FAILED,
        ];
    }

    /**
     * Get supported video mime types.
     */
    public static function getSupportedMimeTypes(): array
    {
        return [
            'video/mp4',
            'video/mpeg',
            'video/quicktime',
            'video/x-msvideo',
            'video/x-ms-wmv',
            'video/webm',
            'video/x-matroska',
            'video/3gpp',
            'video/3gpp2',
            'video/ogg',
            'video/x-flv',
        ];
    }

    /**
     * Get supported file extensions.
     */
    public static function getSupportedExtensions(): array
    {
        return ['mp4', 'mpeg', 'mpg', 'mov', 'avi', 'wmv', 'webm', 'mkv', '3gp', '3g2', 'ogv', 'flv'];
    }
}
