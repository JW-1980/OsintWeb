<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * Video Keyframe Model
 *
 * Represents an extracted frame from a video analysis.
 *
 * Database Schema (video_keyframes table):
 * ----------------------------------------
 * @property int $id - Primary key
 * @property int $video_analysis_id - Foreign key to video_analyses
 * @property int $frame_number - Frame number in the video
 * @property float $timestamp_seconds - Timestamp with millisecond precision
 * @property string $stored_path - Path to full resolution extracted frame
 * @property string|null $thumbnail_path - Path to thumbnail version
 * @property float|null $scene_change_score - 0-1 score of difference from previous frame
 * @property bool $is_scene_change - Flagged as significant scene change
 * @property array|null $objects_detected - Detected objects [{label, confidence, bbox}]
 * @property array|null $faces_detected - Detected faces [{bbox, confidence, landmarks}]
 * @property string|null $ocr_text - Extracted text from frame
 * @property array|null $ocr_regions - OCR regions [{text, bbox, confidence}]
 * @property array|null $dominant_colors - Dominant colors [{color, percentage}]
 * @property float|null $brightness - Average brightness 0-1
 * @property float|null $contrast - Contrast ratio
 * @property float|null $blur_score - Blur detection score
 * @property string|null $frame_type - I-frame, P-frame, B-frame
 * @property bool $is_representative - Marked as good representative frame
 * @property array|null $metadata - Additional metadata
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * Relationships:
 * @property-read VideoAnalysis $videoAnalysis
 */
class VideoKeyframe extends Model
{
    use HasFactory;

    protected $fillable = [
        'video_analysis_id',
        'frame_number',
        'timestamp_seconds',
        'stored_path',
        'thumbnail_path',
        'scene_change_score',
        'is_scene_change',
        'objects_detected',
        'faces_detected',
        'ocr_text',
        'ocr_regions',
        'dominant_colors',
        'brightness',
        'contrast',
        'blur_score',
        'frame_type',
        'is_representative',
        'metadata',
    ];

    protected $casts = [
        'frame_number' => 'integer',
        'timestamp_seconds' => 'decimal:3',
        'scene_change_score' => 'decimal:4',
        'is_scene_change' => 'boolean',
        'objects_detected' => 'array',
        'faces_detected' => 'array',
        'ocr_regions' => 'array',
        'dominant_colors' => 'array',
        'brightness' => 'decimal:4',
        'contrast' => 'decimal:4',
        'blur_score' => 'decimal:4',
        'is_representative' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Get the video analysis this keyframe belongs to.
     */
    public function videoAnalysis(): BelongsTo
    {
        return $this->belongsTo(VideoAnalysis::class);
    }

    /**
     * Get the frame image URL.
     */
    public function getImageUrl(): string
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
     * Get formatted timestamp.
     */
    public function getFormattedTimestamp(): string
    {
        $totalSeconds = (int) $this->timestamp_seconds;
        $hours = floor($totalSeconds / 3600);
        $minutes = floor(($totalSeconds % 3600) / 60);
        $seconds = $totalSeconds % 60;
        $milliseconds = (int) (($this->timestamp_seconds - $totalSeconds) * 1000);

        if ($hours > 0) {
            return sprintf('%d:%02d:%02d.%03d', $hours, $minutes, $seconds, $milliseconds);
        }

        return sprintf('%d:%02d.%03d', $minutes, $seconds, $milliseconds);
    }

    /**
     * Get formatted timestamp short (without milliseconds).
     */
    public function getFormattedTimestampShort(): string
    {
        $totalSeconds = (int) $this->timestamp_seconds;
        $hours = floor($totalSeconds / 3600);
        $minutes = floor(($totalSeconds % 3600) / 60);
        $seconds = $totalSeconds % 60;

        if ($hours > 0) {
            return sprintf('%d:%02d:%02d', $hours, $minutes, $seconds);
        }

        return sprintf('%d:%02d', $minutes, $seconds);
    }

    /**
     * Check if this frame has detected objects.
     */
    public function hasDetectedObjects(): bool
    {
        return !empty($this->objects_detected);
    }

    /**
     * Get count of detected objects.
     */
    public function getObjectCount(): int
    {
        return is_array($this->objects_detected) ? count($this->objects_detected) : 0;
    }

    /**
     * Get unique object labels.
     */
    public function getObjectLabels(): array
    {
        if (!is_array($this->objects_detected)) {
            return [];
        }

        return array_unique(array_column($this->objects_detected, 'label'));
    }

    /**
     * Check if this frame has detected faces.
     */
    public function hasDetectedFaces(): bool
    {
        return !empty($this->faces_detected);
    }

    /**
     * Get count of detected faces.
     */
    public function getFaceCount(): int
    {
        return is_array($this->faces_detected) ? count($this->faces_detected) : 0;
    }

    /**
     * Check if this frame has OCR text.
     */
    public function hasOcrText(): bool
    {
        return !empty($this->ocr_text);
    }

    /**
     * Get OCR text preview (truncated).
     */
    public function getOcrPreview(int $maxLength = 100): ?string
    {
        if (!$this->ocr_text) {
            return null;
        }

        if (strlen($this->ocr_text) <= $maxLength) {
            return $this->ocr_text;
        }

        return substr($this->ocr_text, 0, $maxLength) . '...';
    }

    /**
     * Get primary dominant color.
     */
    public function getPrimaryColor(): ?string
    {
        if (empty($this->dominant_colors)) {
            return null;
        }

        return $this->dominant_colors[0]['color'] ?? null;
    }

    /**
     * Check if frame is blurry (based on blur_score threshold).
     */
    public function isBlurry(float $threshold = 0.3): bool
    {
        return $this->blur_score !== null && $this->blur_score < $threshold;
    }

    /**
     * Check if frame is dark (based on brightness threshold).
     */
    public function isDark(float $threshold = 0.2): bool
    {
        return $this->brightness !== null && $this->brightness < $threshold;
    }

    /**
     * Check if frame is bright (based on brightness threshold).
     */
    public function isBright(float $threshold = 0.8): bool
    {
        return $this->brightness !== null && $this->brightness > $threshold;
    }

    /**
     * Mark as representative frame.
     */
    public function markAsRepresentative(): void
    {
        $this->update(['is_representative' => true]);
    }

    /**
     * Unmark as representative frame.
     */
    public function unmarkAsRepresentative(): void
    {
        $this->update(['is_representative' => false]);
    }

    /**
     * Scope for scene change frames.
     */
    public function scopeSceneChanges($query)
    {
        return $query->where('is_scene_change', true);
    }

    /**
     * Scope for representative frames.
     */
    public function scopeRepresentative($query)
    {
        return $query->where('is_representative', true);
    }

    /**
     * Scope for frames with detected objects.
     */
    public function scopeWithObjects($query)
    {
        return $query->whereNotNull('objects_detected')
            ->whereRaw('JSON_LENGTH(objects_detected) > 0');
    }

    /**
     * Scope for frames with detected faces.
     */
    public function scopeWithFaces($query)
    {
        return $query->whereNotNull('faces_detected')
            ->whereRaw('JSON_LENGTH(faces_detected) > 0');
    }

    /**
     * Scope for frames with OCR text.
     */
    public function scopeWithOcr($query)
    {
        return $query->whereNotNull('ocr_text')
            ->where('ocr_text', '!=', '');
    }

    /**
     * Scope to filter by time range.
     */
    public function scopeInTimeRange($query, float $start, float $end)
    {
        return $query->whereBetween('timestamp_seconds', [$start, $end]);
    }

    /**
     * Scope to filter by scene change score threshold.
     */
    public function scopeHighSceneChange($query, float $threshold = 0.5)
    {
        return $query->where('scene_change_score', '>=', $threshold);
    }

    /**
     * Scope to filter by frame type.
     */
    public function scopeFrameType($query, string $type)
    {
        return $query->where('frame_type', $type);
    }

    /**
     * Scope for I-frames (keyframes).
     */
    public function scopeIFrames($query)
    {
        return $query->where('frame_type', 'I');
    }

    /**
     * Scope to search OCR text.
     */
    public function scopeSearchOcr($query, string $term)
    {
        return $query->where('ocr_text', 'like', "%{$term}%");
    }

    /**
     * Scope to find frames with specific object label.
     */
    public function scopeWithObjectLabel($query, string $label)
    {
        return $query->whereRaw(
            'JSON_CONTAINS(objects_detected, ?)',
            [json_encode(['label' => $label])]
        );
    }
}
