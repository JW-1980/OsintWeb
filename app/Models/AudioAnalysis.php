<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

/**
 * Audio Analysis Model
 *
 * Represents a forensic analysis of an audio file including spectrogram generation,
 * edit detection, frequency analysis, and authenticity scoring.
 *
 * Database Schema:
 * @property int $id Primary key
 * @property string $uuid Unique public identifier
 * @property int $audio_file_id Foreign key to audio_files table
 * @property int|null $event_id Foreign key to events table (nullable)
 * @property int $created_by Foreign key to users table
 * @property string|null $spectrogram_path Path to generated spectrogram image
 * @property string|null $waveform_path Path to generated waveform image
 * @property float|null $duration_seconds Audio duration with millisecond precision
 * @property int|null $sample_rate Sample rate in Hz (e.g., 44100, 48000)
 * @property int|null $channels Number of audio channels (1=mono, 2=stereo)
 * @property int|null $bit_depth Audio bit depth (16, 24, 32)
 * @property string|null $codec Audio codec (mp3, aac, flac, etc.)
 * @property string|null $container_format Container format (mp4, ogg, wav, etc.)
 * @property float|null $average_amplitude Average amplitude (0.0-1.0)
 * @property float|null $peak_amplitude Peak amplitude (0.0-1.0)
 * @property float|null $noise_floor_db Noise floor in decibels
 * @property array|null $detected_edits JSON array of detected edit points
 * @property array|null $frequency_analysis JSON object with frequency spectrum data
 * @property array|null $voice_activity_regions JSON array of voice activity timestamps
 * @property array|null $silence_regions JSON array of silence timestamps
 * @property string $analysis_status Status: pending, processing, completed, failed
 * @property int|null $manipulation_score Authenticity score (0-100, higher = more likely edited)
 * @property string|null $analysis_notes Human-readable analysis summary
 * @property array|null $processing_metadata JSON object with processing details
 * @property string|null $error_message Error message if analysis failed
 * @property \Carbon\Carbon|null $started_at When analysis started
 * @property \Carbon\Carbon|null $completed_at When analysis completed
 * @property \Carbon\Carbon $created_at Record creation timestamp
 * @property \Carbon\Carbon $updated_at Record update timestamp
 * @property \Carbon\Carbon|null $deleted_at Soft delete timestamp
 *
 * Relationships:
 * @property-read AudioFile $audioFile The analyzed audio file
 * @property-read Event|null $event The associated event (if any)
 * @property-read User $creator The user who initiated the analysis
 */
class AudioAnalysis extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'uuid',
        'audio_file_id',
        'event_id',
        'created_by',
        'spectrogram_path',
        'waveform_path',
        'duration_seconds',
        'sample_rate',
        'channels',
        'bit_depth',
        'codec',
        'container_format',
        'average_amplitude',
        'peak_amplitude',
        'noise_floor_db',
        'detected_edits',
        'frequency_analysis',
        'voice_activity_regions',
        'silence_regions',
        'analysis_status',
        'manipulation_score',
        'analysis_notes',
        'processing_metadata',
        'error_message',
        'started_at',
        'completed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'duration_seconds' => 'float',
        'sample_rate' => 'integer',
        'channels' => 'integer',
        'bit_depth' => 'integer',
        'average_amplitude' => 'float',
        'peak_amplitude' => 'float',
        'noise_floor_db' => 'float',
        'detected_edits' => 'array',
        'frequency_analysis' => 'array',
        'voice_activity_regions' => 'array',
        'silence_regions' => 'array',
        'manipulation_score' => 'integer',
        'processing_metadata' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Analysis status constants.
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    /**
     * Manipulation risk level thresholds.
     */
    public const RISK_LOW_THRESHOLD = 30;
    public const RISK_MEDIUM_THRESHOLD = 60;
    public const RISK_HIGH_THRESHOLD = 80;

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    /**
     * Get the audio file that was analyzed.
     */
    public function audioFile(): BelongsTo
    {
        return $this->belongsTo(AudioFile::class);
    }

    /**
     * Get the associated event.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the user who created this analysis.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    /**
     * Scope to filter by analysis status.
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('analysis_status', $status);
    }

    /**
     * Scope to get pending analyses.
     */
    public function scopePending($query)
    {
        return $query->where('analysis_status', self::STATUS_PENDING);
    }

    /**
     * Scope to get completed analyses.
     */
    public function scopeCompleted($query)
    {
        return $query->where('analysis_status', self::STATUS_COMPLETED);
    }

    /**
     * Scope to get processing analyses.
     */
    public function scopeProcessing($query)
    {
        return $query->where('analysis_status', self::STATUS_PROCESSING);
    }

    /**
     * Scope to get failed analyses.
     */
    public function scopeFailed($query)
    {
        return $query->where('analysis_status', self::STATUS_FAILED);
    }

    /**
     * Scope to filter analyses with detected edits.
     */
    public function scopeWithEdits($query)
    {
        return $query->whereNotNull('detected_edits')
            ->whereRaw("JSON_LENGTH(detected_edits) > 0");
    }

    /**
     * Scope to filter by manipulation score range.
     */
    public function scopeManipulationScoreAbove($query, int $threshold)
    {
        return $query->where('manipulation_score', '>=', $threshold);
    }

    /**
     * Scope to filter by high manipulation risk.
     */
    public function scopeHighRisk($query)
    {
        return $query->where('manipulation_score', '>=', self::RISK_HIGH_THRESHOLD);
    }

    /**
     * Scope to filter by user.
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('created_by', $userId);
    }

    // =========================================================================
    // EDIT DETECTION METHODS
    // =========================================================================

    /**
     * Check if any edits were detected in the audio.
     */
    public function hasDetectedEdits(): bool
    {
        return !empty($this->detected_edits);
    }

    /**
     * Get the timestamps of all detected edits.
     *
     * @return array<array{timestamp: float, type: string, confidence: float}>
     */
    public function getEditTimestamps(): array
    {
        if (empty($this->detected_edits)) {
            return [];
        }

        return array_map(function ($edit) {
            return [
                'timestamp' => $edit['timestamp'] ?? 0,
                'type' => $edit['type'] ?? 'unknown',
                'confidence' => $edit['confidence'] ?? 0,
            ];
        }, $this->detected_edits);
    }

    /**
     * Get detected edits by type.
     *
     * @param string $type Edit type (cut, splice, overdub, noise_reduction, etc.)
     * @return array
     */
    public function getEditsByType(string $type): array
    {
        if (empty($this->detected_edits)) {
            return [];
        }

        return array_filter($this->detected_edits, function ($edit) use ($type) {
            return ($edit['type'] ?? '') === $type;
        });
    }

    /**
     * Get the number of detected edits.
     */
    public function getEditCount(): int
    {
        return count($this->detected_edits ?? []);
    }

    /**
     * Get high-confidence edits (confidence > 0.7).
     */
    public function getHighConfidenceEdits(): array
    {
        if (empty($this->detected_edits)) {
            return [];
        }

        return array_filter($this->detected_edits, function ($edit) {
            return ($edit['confidence'] ?? 0) >= 0.7;
        });
    }

    // =========================================================================
    // MANIPULATION RISK METHODS
    // =========================================================================

    /**
     * Get the manipulation risk level based on the score.
     *
     * @return string 'low', 'medium', 'high', or 'critical'
     */
    public function getManipulationRisk(): string
    {
        if ($this->manipulation_score === null) {
            return 'unknown';
        }

        if ($this->manipulation_score < self::RISK_LOW_THRESHOLD) {
            return 'low';
        }

        if ($this->manipulation_score < self::RISK_MEDIUM_THRESHOLD) {
            return 'medium';
        }

        if ($this->manipulation_score < self::RISK_HIGH_THRESHOLD) {
            return 'high';
        }

        return 'critical';
    }

    /**
     * Check if the audio is likely authentic (low manipulation score).
     */
    public function isLikelyAuthentic(): bool
    {
        return $this->manipulation_score !== null
            && $this->manipulation_score < self::RISK_LOW_THRESHOLD;
    }

    /**
     * Check if the audio shows signs of manipulation.
     */
    public function showsSignsOfManipulation(): bool
    {
        return $this->manipulation_score !== null
            && $this->manipulation_score >= self::RISK_MEDIUM_THRESHOLD;
    }

    // =========================================================================
    // VOICE ACTIVITY METHODS
    // =========================================================================

    /**
     * Get voice activity regions.
     *
     * @return array<array{start: float, end: float, duration: float}>
     */
    public function getVoiceActivityRegions(): array
    {
        if (empty($this->voice_activity_regions)) {
            return [];
        }

        return array_map(function ($region) {
            $start = $region['start'] ?? 0;
            $end = $region['end'] ?? 0;
            return [
                'start' => $start,
                'end' => $end,
                'duration' => $end - $start,
                'speaker_id' => $region['speaker_id'] ?? null,
                'confidence' => $region['confidence'] ?? null,
            ];
        }, $this->voice_activity_regions);
    }

    /**
     * Get total voice activity duration.
     */
    public function getTotalVoiceActivityDuration(): float
    {
        $regions = $this->getVoiceActivityRegions();
        return array_sum(array_column($regions, 'duration'));
    }

    /**
     * Get the percentage of audio with voice activity.
     */
    public function getVoiceActivityPercentage(): float
    {
        if (!$this->duration_seconds || $this->duration_seconds == 0) {
            return 0.0;
        }

        return ($this->getTotalVoiceActivityDuration() / $this->duration_seconds) * 100;
    }

    // =========================================================================
    // SILENCE DETECTION METHODS
    // =========================================================================

    /**
     * Get silence regions.
     *
     * @return array<array{start: float, end: float, duration: float}>
     */
    public function getSilenceRegions(): array
    {
        if (empty($this->silence_regions)) {
            return [];
        }

        return array_map(function ($region) {
            $start = $region['start'] ?? 0;
            $end = $region['end'] ?? 0;
            return [
                'start' => $start,
                'end' => $end,
                'duration' => $end - $start,
            ];
        }, $this->silence_regions);
    }

    /**
     * Get total silence duration.
     */
    public function getTotalSilenceDuration(): float
    {
        $regions = $this->getSilenceRegions();
        return array_sum(array_column($regions, 'duration'));
    }

    /**
     * Get the percentage of audio that is silence.
     */
    public function getSilencePercentage(): float
    {
        if (!$this->duration_seconds || $this->duration_seconds == 0) {
            return 0.0;
        }

        return ($this->getTotalSilenceDuration() / $this->duration_seconds) * 100;
    }

    /**
     * Check if there are suspicious silence gaps (unusually long or placed).
     */
    public function hasSuspiciousSilenceGaps(): bool
    {
        $regions = $this->getSilenceRegions();

        foreach ($regions as $region) {
            // Long silence in the middle of speech could indicate editing
            if ($region['duration'] > 2.0 && $region['start'] > 1.0) {
                return true;
            }
        }

        return false;
    }

    // =========================================================================
    // FREQUENCY ANALYSIS METHODS
    // =========================================================================

    /**
     * Get dominant frequencies from the analysis.
     *
     * @return array<float>
     */
    public function getDominantFrequencies(): array
    {
        return $this->frequency_analysis['dominant_frequencies'] ?? [];
    }

    /**
     * Get the frequency spectrum data.
     */
    public function getSpectrumData(): array
    {
        return $this->frequency_analysis['spectrum_data'] ?? [];
    }

    /**
     * Check if the audio has typical human voice frequencies (85Hz - 255Hz fundamental).
     */
    public function hasVoiceFrequencies(): bool
    {
        $dominant = $this->getDominantFrequencies();

        foreach ($dominant as $freq) {
            if ($freq >= 85 && $freq <= 300) {
                return true;
            }
        }

        return false;
    }

    // =========================================================================
    // STATUS MANAGEMENT METHODS
    // =========================================================================

    /**
     * Mark analysis as started.
     */
    public function markAsProcessing(): void
    {
        $this->update([
            'analysis_status' => self::STATUS_PROCESSING,
            'started_at' => now(),
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
            'completed_at' => now(),
        ]);
    }

    /**
     * Mark analysis as failed.
     */
    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'analysis_status' => self::STATUS_FAILED,
            'error_message' => $errorMessage,
            'completed_at' => now(),
        ]);
    }

    /**
     * Check if the analysis is complete.
     */
    public function isCompleted(): bool
    {
        return $this->analysis_status === self::STATUS_COMPLETED;
    }

    /**
     * Check if the analysis is pending.
     */
    public function isPending(): bool
    {
        return $this->analysis_status === self::STATUS_PENDING;
    }

    /**
     * Check if the analysis is processing.
     */
    public function isProcessing(): bool
    {
        return $this->analysis_status === self::STATUS_PROCESSING;
    }

    /**
     * Check if the analysis failed.
     */
    public function hasFailed(): bool
    {
        return $this->analysis_status === self::STATUS_FAILED;
    }

    // =========================================================================
    // URL GENERATION METHODS
    // =========================================================================

    /**
     * Get the URL for the spectrogram image.
     */
    public function getSpectrogramUrl(): ?string
    {
        if (!$this->spectrogram_path) {
            return null;
        }

        return Storage::disk('public')->url($this->spectrogram_path);
    }

    /**
     * Get the URL for the waveform image.
     */
    public function getWaveformUrl(): ?string
    {
        if (!$this->waveform_path) {
            return null;
        }

        return Storage::disk('public')->url($this->waveform_path);
    }

    /**
     * Check if visualizations are available.
     */
    public function hasVisualizations(): bool
    {
        return $this->spectrogram_path !== null || $this->waveform_path !== null;
    }

    // =========================================================================
    // HELPER METHODS
    // =========================================================================

    /**
     * Get the processing duration in seconds.
     */
    public function getProcessingDuration(): ?float
    {
        if (!$this->started_at || !$this->completed_at) {
            return null;
        }

        return $this->completed_at->diffInSeconds($this->started_at);
    }

    /**
     * Get a human-readable duration string.
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

        if ($hours > 0) {
            return sprintf('%d:%02d:%02d', $hours, $minutes, $seconds);
        }

        return sprintf('%d:%02d', $minutes, $seconds);
    }

    /**
     * Get a formatted channel string.
     */
    public function getFormattedChannels(): string
    {
        return match ($this->channels) {
            1 => 'Mono',
            2 => 'Stereo',
            6 => '5.1 Surround',
            8 => '7.1 Surround',
            default => $this->channels ? "{$this->channels} channels" : 'Unknown',
        };
    }

    /**
     * Get the analysis summary as a structured array.
     */
    public function getSummary(): array
    {
        return [
            'status' => $this->analysis_status,
            'manipulation_risk' => $this->getManipulationRisk(),
            'manipulation_score' => $this->manipulation_score,
            'edit_count' => $this->getEditCount(),
            'has_detected_edits' => $this->hasDetectedEdits(),
            'voice_activity_percentage' => round($this->getVoiceActivityPercentage(), 1),
            'silence_percentage' => round($this->getSilencePercentage(), 1),
            'duration_formatted' => $this->getFormattedDuration(),
            'channels' => $this->getFormattedChannels(),
            'sample_rate' => $this->sample_rate,
            'bit_depth' => $this->bit_depth,
        ];
    }
}
