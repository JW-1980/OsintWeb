<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

/**
 * Audio File Model
 *
 * Represents an uploaded audio file with metadata.
 *
 * @property int $id
 * @property string $uuid
 * @property int $user_id
 * @property string $title
 * @property string|null $description
 * @property string $file_path
 * @property string $file_name
 * @property string $original_name
 * @property string $mime_type
 * @property int $file_size
 * @property int|null $duration
 * @property int|null $sample_rate
 * @property int|null $channels
 * @property string|null $bitrate
 * @property string|null $codec
 * @property string|null $language
 * @property string $status
 * @property string $visibility
 * @property bool $allow_download
 * @property int $play_count
 * @property array|null $metadata
 * @property array|null $waveform_data
 * @property string|null $thumbnail_path
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class AudioFile extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'user_id',
        'title',
        'description',
        'file_path',
        'file_name',
        'original_name',
        'mime_type',
        'file_size',
        'duration',
        'sample_rate',
        'channels',
        'bitrate',
        'codec',
        'language',
        'status',
        'visibility',
        'allow_download',
        'play_count',
        'metadata',
        'waveform_data',
        'thumbnail_path',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'duration' => 'integer',
        'sample_rate' => 'integer',
        'channels' => 'integer',
        'allow_download' => 'boolean',
        'play_count' => 'integer',
        'metadata' => 'array',
        'waveform_data' => 'array',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_READY = 'ready';
    public const STATUS_ERROR = 'error';

    public const VISIBILITY_PUBLIC = 'public';
    public const VISIBILITY_PRIVATE = 'private';
    public const VISIBILITY_UNLISTED = 'unlisted';

    /**
     * Get the user who uploaded this audio.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all transcriptions for this audio.
     */
    public function transcriptions(): HasMany
    {
        return $this->hasMany(Transcription::class);
    }

    /**
     * Get the primary transcription.
     */
    public function primaryTranscription(): HasOne
    {
        return $this->hasOne(Transcription::class)->where('is_primary', true);
    }

    /**
     * Get transcription jobs for this audio.
     */
    public function transcriptionJobs(): HasMany
    {
        return $this->hasMany(TranscriptionJob::class);
    }

    /**
     * Get events linked to this audio.
     */
    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'audio_event')
            ->withPivot(['relationship_type', 'notes'])
            ->withTimestamps();
    }

    /**
     * Get collections containing this audio.
     */
    public function collections(): BelongsToMany
    {
        return $this->belongsToMany(AudioCollection::class, 'audio_collection_items')
            ->withPivot('position')
            ->withTimestamps();
    }

    /**
     * Get forensic analyses for this audio.
     */
    public function analyses(): HasMany
    {
        return $this->hasMany(AudioAnalysis::class);
    }

    /**
     * Get the latest completed analysis.
     */
    public function latestAnalysis(): HasOne
    {
        return $this->hasOne(AudioAnalysis::class)
            ->where('analysis_status', 'completed')
            ->latest();
    }

    /**
     * Get the file URL.
     */
    public function getFileUrl(): string
    {
        return Storage::disk('public')->url($this->file_path);
    }

    /**
     * Get thumbnail URL.
     */
    public function getThumbnailUrl(): ?string
    {
        if (!$this->thumbnail_path) {
            return null;
        }
        return Storage::disk('public')->url($this->thumbnail_path);
    }

    /**
     * Get formatted duration.
     */
    public function getFormattedDuration(): string
    {
        if (!$this->duration) {
            return '0:00';
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
     * Get formatted file size.
     */
    public function getFormattedFileSize(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $index = 0;

        while ($bytes >= 1024 && $index < count($units) - 1) {
            $bytes /= 1024;
            $index++;
        }

        return round($bytes, 2) . ' ' . $units[$index];
    }

    /**
     * Increment play count.
     */
    public function recordPlay(): void
    {
        $this->increment('play_count');
    }

    /**
     * Check if audio is publicly accessible.
     */
    public function isPublic(): bool
    {
        return $this->visibility === self::VISIBILITY_PUBLIC && $this->status === self::STATUS_READY;
    }

    /**
     * Check if user can access this audio.
     */
    public function canAccess(?User $user): bool
    {
        if ($this->visibility === self::VISIBILITY_PUBLIC) {
            return true;
        }

        if (!$user) {
            return false;
        }

        if ($this->user_id === $user->id) {
            return true;
        }

        if ($user->isAdmin() || $user->isModerator()) {
            return true;
        }

        return $this->visibility === self::VISIBILITY_UNLISTED;
    }

    /**
     * Scope to filter public audio.
     */
    public function scopePublic($query)
    {
        return $query->where('visibility', self::VISIBILITY_PUBLIC)
            ->where('status', self::STATUS_READY);
    }

    /**
     * Scope to filter ready audio.
     */
    public function scopeReady($query)
    {
        return $query->where('status', self::STATUS_READY);
    }

    /**
     * Scope to filter by status.
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Get supported audio mime types.
     */
    public static function getSupportedMimeTypes(): array
    {
        return [
            'audio/mpeg',      // MP3
            'audio/mp3',       // MP3 alternative
            'audio/wav',       // WAV
            'audio/x-wav',     // WAV alternative
            'audio/ogg',       // OGG
            'audio/flac',      // FLAC
            'audio/x-flac',    // FLAC alternative
            'audio/aac',       // AAC
            'audio/mp4',       // M4A
            'audio/x-m4a',     // M4A alternative
            'audio/webm',      // WebM
        ];
    }

    /**
     * Get supported file extensions.
     */
    public static function getSupportedExtensions(): array
    {
        return ['mp3', 'wav', 'ogg', 'flac', 'aac', 'm4a', 'webm'];
    }
}
