<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Transcription Model
 *
 * Represents a text transcription of an audio file.
 *
 * @property int $id
 * @property string $uuid
 * @property int $audio_file_id
 * @property int $user_id
 * @property string $type
 * @property string $language
 * @property string|null $title
 * @property string|null $full_text
 * @property string $status
 * @property bool $is_primary
 * @property float|null $confidence_score
 * @property string|null $ai_model
 * @property string|null $ai_provider
 * @property int $word_count
 * @property int $segment_count
 * @property array|null $speaker_labels
 * @property array|null $metadata
 * @property \Carbon\Carbon|null $transcription_started_at
 * @property \Carbon\Carbon|null $transcription_completed_at
 * @property int|null $verified_by
 * @property \Carbon\Carbon|null $verified_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class Transcription extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'audio_file_id',
        'user_id',
        'type',
        'language',
        'title',
        'full_text',
        'status',
        'is_primary',
        'confidence_score',
        'ai_model',
        'ai_provider',
        'word_count',
        'segment_count',
        'speaker_labels',
        'metadata',
        'transcription_started_at',
        'transcription_completed_at',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'confidence_score' => 'float',
        'word_count' => 'integer',
        'segment_count' => 'integer',
        'speaker_labels' => 'array',
        'metadata' => 'array',
        'transcription_started_at' => 'datetime',
        'transcription_completed_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public const TYPE_MANUAL = 'manual';
    public const TYPE_AI = 'ai';
    public const TYPE_HYBRID = 'hybrid';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_VERIFIED = 'verified';
    public const STATUS_PUBLISHED = 'published';

    /**
     * Get the audio file this transcription belongs to.
     */
    public function audioFile(): BelongsTo
    {
        return $this->belongsTo(AudioFile::class);
    }

    /**
     * Get the user who created this transcription.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who verified this transcription.
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Get the segments of this transcription.
     */
    public function segments(): HasMany
    {
        return $this->hasMany(TranscriptSegment::class)->orderBy('segment_index');
    }

    /**
     * Get the revisions of this transcription.
     */
    public function revisions(): HasMany
    {
        return $this->hasMany(TranscriptRevision::class)->orderBy('revision_number', 'desc');
    }

    /**
     * Get the latest revision.
     */
    public function latestRevision()
    {
        return $this->hasOne(TranscriptRevision::class)->latest('revision_number');
    }

    /**
     * Create a new revision.
     */
    public function createRevision(User $user, ?string $comment = null): TranscriptRevision
    {
        $lastRevision = $this->revisions()->max('revision_number') ?? 0;

        return $this->revisions()->create([
            'user_id' => $user->id,
            'revision_number' => $lastRevision + 1,
            'full_text' => $this->full_text,
            'comment' => $comment,
            'created_at' => now(),
        ]);
    }

    /**
     * Update full text from segments.
     */
    public function syncFullTextFromSegments(): void
    {
        $segments = $this->segments()->orderBy('segment_index')->get();

        $this->full_text = $segments->pluck('text')->implode("\n\n");
        $this->word_count = str_word_count($this->full_text);
        $this->segment_count = $segments->count();
        $this->save();
    }

    /**
     * Set as primary transcription.
     */
    public function setAsPrimary(): void
    {
        // Remove primary from other transcriptions
        self::where('audio_file_id', $this->audio_file_id)
            ->where('id', '!=', $this->id)
            ->update(['is_primary' => false]);

        $this->update(['is_primary' => true]);
    }

    /**
     * Mark as verified.
     */
    public function verify(User $verifier): void
    {
        $this->update([
            'status' => self::STATUS_VERIFIED,
            'verified_by' => $verifier->id,
            'verified_at' => now(),
        ]);
    }

    /**
     * Publish the transcription.
     */
    public function publish(): void
    {
        $this->update(['status' => self::STATUS_PUBLISHED]);
    }

    /**
     * Get transcript text with timestamps.
     */
    public function getFormattedTranscript(): string
    {
        $segments = $this->segments()->orderBy('segment_index')->get();
        $output = [];

        foreach ($segments as $segment) {
            $timestamp = $this->formatTimestamp($segment->start_time);
            $speaker = $segment->speaker_name ?? $segment->speaker_id;

            if ($speaker) {
                $output[] = "[{$timestamp}] {$speaker}: {$segment->text}";
            } else {
                $output[] = "[{$timestamp}] {$segment->text}";
            }
        }

        return implode("\n", $output);
    }

    /**
     * Format timestamp for display.
     */
    protected function formatTimestamp(float $seconds): string
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = floor($seconds % 60);

        if ($hours > 0) {
            return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
        }

        return sprintf('%02d:%02d', $minutes, $secs);
    }

    /**
     * Get segment at specific time.
     */
    public function getSegmentAtTime(float $seconds): ?TranscriptSegment
    {
        return $this->segments()
            ->where('start_time', '<=', $seconds)
            ->where('end_time', '>=', $seconds)
            ->first();
    }

    /**
     * Search segments by text.
     */
    public function searchSegments(string $query): \Illuminate\Database\Eloquent\Collection
    {
        return $this->segments()
            ->where('text', 'like', "%{$query}%")
            ->orderBy('segment_index')
            ->get();
    }

    /**
     * Get unique speakers.
     */
    public function getSpeakers(): array
    {
        return $this->segments()
            ->whereNotNull('speaker_id')
            ->distinct()
            ->pluck('speaker_name', 'speaker_id')
            ->toArray();
    }

    /**
     * Scope to filter by type.
     */
    public function scopeType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to filter primary transcriptions.
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    /**
     * Scope to filter published transcriptions.
     */
    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    /**
     * Scope to filter verified transcriptions.
     */
    public function scopeVerified($query)
    {
        return $query->whereIn('status', [self::STATUS_VERIFIED, self::STATUS_PUBLISHED]);
    }
}
