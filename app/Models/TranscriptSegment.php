<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Transcript Segment Model
 *
 * Represents a timed segment of a transcription.
 *
 * @property int $id
 * @property int $transcription_id
 * @property int $segment_index
 * @property float $start_time
 * @property float $end_time
 * @property string $text
 * @property string|null $speaker_id
 * @property string|null $speaker_name
 * @property float|null $confidence
 * @property array|null $words
 * @property array|null $metadata
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class TranscriptSegment extends Model
{
    protected $fillable = [
        'transcription_id',
        'segment_index',
        'start_time',
        'end_time',
        'text',
        'speaker_id',
        'speaker_name',
        'confidence',
        'words',
        'metadata',
    ];

    protected $casts = [
        'segment_index' => 'integer',
        'start_time' => 'float',
        'end_time' => 'float',
        'confidence' => 'float',
        'words' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Get the transcription this segment belongs to.
     */
    public function transcription(): BelongsTo
    {
        return $this->belongsTo(Transcription::class);
    }

    /**
     * Get duration in seconds.
     */
    public function getDuration(): float
    {
        return $this->end_time - $this->start_time;
    }

    /**
     * Get formatted start time.
     */
    public function getFormattedStartTime(): string
    {
        return $this->formatTime($this->start_time);
    }

    /**
     * Get formatted end time.
     */
    public function getFormattedEndTime(): string
    {
        return $this->formatTime($this->end_time);
    }

    /**
     * Format time for display.
     */
    protected function formatTime(float $seconds): string
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = floor($seconds % 60);
        $ms = round(($seconds - floor($seconds)) * 1000);

        if ($hours > 0) {
            return sprintf('%02d:%02d:%02d.%03d', $hours, $minutes, $secs, $ms);
        }

        return sprintf('%02d:%02d.%03d', $minutes, $secs, $ms);
    }

    /**
     * Get word count.
     */
    public function getWordCount(): int
    {
        return str_word_count($this->text);
    }

    /**
     * Check if segment contains a specific time.
     */
    public function containsTime(float $seconds): bool
    {
        return $this->start_time <= $seconds && $this->end_time >= $seconds;
    }

    /**
     * Get VTT formatted segment.
     */
    public function toVtt(): string
    {
        $start = $this->formatVttTime($this->start_time);
        $end = $this->formatVttTime($this->end_time);

        $text = $this->text;
        if ($this->speaker_name) {
            $text = "<v {$this->speaker_name}>{$text}";
        }

        return "{$start} --> {$end}\n{$text}";
    }

    /**
     * Get SRT formatted segment.
     */
    public function toSrt(int $index): string
    {
        $start = $this->formatSrtTime($this->start_time);
        $end = $this->formatSrtTime($this->end_time);

        return "{$index}\n{$start} --> {$end}\n{$this->text}";
    }

    /**
     * Format time for VTT.
     */
    protected function formatVttTime(float $seconds): string
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = floor($seconds % 60);
        $ms = round(($seconds - floor($seconds)) * 1000);

        return sprintf('%02d:%02d:%02d.%03d', $hours, $minutes, $secs, $ms);
    }

    /**
     * Format time for SRT.
     */
    protected function formatSrtTime(float $seconds): string
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = floor($seconds % 60);
        $ms = round(($seconds - floor($seconds)) * 1000);

        return sprintf('%02d:%02d:%02d,%03d', $hours, $minutes, $secs, $ms);
    }
}
