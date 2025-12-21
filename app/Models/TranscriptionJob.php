<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Transcription Job Model
 *
 * Represents an async AI transcription job.
 *
 * @property int $id
 * @property string $uuid
 * @property int $audio_file_id
 * @property int $user_id
 * @property int|null $transcription_id
 * @property string $provider
 * @property string|null $model
 * @property string $status
 * @property string|null $language
 * @property array|null $options
 * @property string|null $error_message
 * @property string|null $external_job_id
 * @property int $progress
 * @property float|null $cost
 * @property \Carbon\Carbon|null $started_at
 * @property \Carbon\Carbon|null $completed_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class TranscriptionJob extends Model
{
    protected $fillable = [
        'uuid',
        'audio_file_id',
        'user_id',
        'transcription_id',
        'provider',
        'model',
        'status',
        'language',
        'options',
        'error_message',
        'external_job_id',
        'progress',
        'cost',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'options' => 'array',
        'progress' => 'integer',
        'cost' => 'float',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public const STATUS_QUEUED = 'queued';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_CANCELLED = 'cancelled';

    public const PROVIDER_OPENROUTER = 'openrouter';
    public const PROVIDER_WHISPER = 'whisper';

    /**
     * Get the audio file for this job.
     */
    public function audioFile(): BelongsTo
    {
        return $this->belongsTo(AudioFile::class);
    }

    /**
     * Get the user who created this job.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the resulting transcription.
     */
    public function transcription(): BelongsTo
    {
        return $this->belongsTo(Transcription::class);
    }

    /**
     * Mark job as processing.
     */
    public function start(): void
    {
        $this->update([
            'status' => self::STATUS_PROCESSING,
            'started_at' => now(),
        ]);
    }

    /**
     * Update progress.
     */
    public function setProgress(int $progress): void
    {
        $this->update(['progress' => min(100, max(0, $progress))]);
    }

    /**
     * Mark job as completed.
     */
    public function complete(Transcription $transcription, ?float $cost = null): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'transcription_id' => $transcription->id,
            'progress' => 100,
            'cost' => $cost,
            'completed_at' => now(),
        ]);
    }

    /**
     * Mark job as failed.
     */
    public function fail(string $errorMessage): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'error_message' => $errorMessage,
            'completed_at' => now(),
        ]);
    }

    /**
     * Cancel the job.
     */
    public function cancel(): void
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
            'completed_at' => now(),
        ]);
    }

    /**
     * Check if job is in progress.
     */
    public function isInProgress(): bool
    {
        return in_array($this->status, [self::STATUS_QUEUED, self::STATUS_PROCESSING], true);
    }

    /**
     * Get estimated time remaining based on progress.
     */
    public function getEstimatedTimeRemaining(): ?int
    {
        if ($this->progress === 0 || !$this->started_at) {
            return null;
        }

        $elapsed = now()->diffInSeconds($this->started_at);
        $rate = $elapsed / $this->progress;
        $remaining = (100 - $this->progress) * $rate;

        return (int) round($remaining);
    }

    /**
     * Scope to filter by status.
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter queued jobs.
     */
    public function scopeQueued($query)
    {
        return $query->where('status', self::STATUS_QUEUED);
    }

    /**
     * Scope to filter processing jobs.
     */
    public function scopeProcessing($query)
    {
        return $query->where('status', self::STATUS_PROCESSING);
    }
}
