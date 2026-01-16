<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AudioAnalysis;
use App\Models\AudioFile;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Service for audio forensic analysis including spectrogram generation,
 * edit detection, frequency analysis, and authenticity scoring.
 *
 * This service uses external tools (sox, ffmpeg, ffprobe) for audio processing.
 */
class AudioAnalysisService
{
    /**
     * Default spectrogram generation options.
     */
    protected array $defaultSpectrogramOptions = [
        'width' => 1200,
        'height' => 400,
        'color_scheme' => 'default', // default, grayscale, heat
        'frequency_range' => [0, 8000], // Hz
        'dynamic_range' => 120, // dB
    ];

    /**
     * Default waveform generation options.
     */
    protected array $defaultWaveformOptions = [
        'width' => 1200,
        'height' => 200,
        'color' => '#3b82f6', // Tailwind blue-500
        'background' => '#1e293b', // Tailwind slate-800
    ];

    /**
     * Silence detection thresholds.
     */
    protected float $silenceThresholdDb = -40.0;
    protected float $minSilenceDuration = 0.5; // seconds

    /**
     * Create a new audio analysis for an audio file.
     */
    public function analyzeAudio(int $audioFileId, User $user, ?int $eventId = null): AudioAnalysis
    {
        $audioFile = AudioFile::findOrFail($audioFileId);

        // Create the analysis record
        $analysis = AudioAnalysis::create([
            'uuid' => Str::uuid()->toString(),
            'audio_file_id' => $audioFile->id,
            'event_id' => $eventId,
            'created_by' => $user->id,
            'analysis_status' => AudioAnalysis::STATUS_PENDING,
        ]);

        return $analysis;
    }

    /**
     * Perform full analysis on an audio file.
     * This is typically called from a queued job.
     */
    public function performFullAnalysis(AudioAnalysis $analysis): AudioAnalysis
    {
        $analysis->markAsProcessing();
        $startTime = microtime(true);

        try {
            $audioFile = $analysis->audioFile;
            $audioPath = Storage::disk('public')->path($audioFile->file_path);

            if (!file_exists($audioPath)) {
                throw new \Exception("Audio file not found: {$audioPath}");
            }

            // Step 1: Extract technical metadata
            $metadata = $this->extractMetadata($audioPath);
            $analysis->fill($metadata);
            $analysis->save();

            Log::info('Audio metadata extracted', [
                'analysis_id' => $analysis->id,
                'duration' => $metadata['duration_seconds'] ?? null,
            ]);

            // Step 2: Generate spectrogram
            $spectrogramPath = $this->generateSpectrogram(
                $audioPath,
                $this->defaultSpectrogramOptions,
                $analysis->uuid
            );
            $analysis->spectrogram_path = $spectrogramPath;
            $analysis->save();

            Log::info('Spectrogram generated', [
                'analysis_id' => $analysis->id,
                'path' => $spectrogramPath,
            ]);

            // Step 3: Generate waveform
            $waveformPath = $this->generateWaveform(
                $audioPath,
                $this->defaultWaveformOptions,
                $analysis->uuid
            );
            $analysis->waveform_path = $waveformPath;
            $analysis->save();

            Log::info('Waveform generated', [
                'analysis_id' => $analysis->id,
                'path' => $waveformPath,
            ]);

            // Step 4: Detect silence regions
            $silenceRegions = $this->detectSilence($audioPath);
            $analysis->silence_regions = $silenceRegions;
            $analysis->save();

            // Step 5: Detect voice activity regions
            $voiceRegions = $this->detectVoiceActivity($audioPath, $silenceRegions);
            $analysis->voice_activity_regions = $voiceRegions;
            $analysis->save();

            // Step 6: Analyze frequencies
            $frequencyAnalysis = $this->analyzeFrequencies($audioPath);
            $analysis->frequency_analysis = $frequencyAnalysis;
            $analysis->save();

            // Step 7: Detect edits/splices
            $detectedEdits = $this->detectEdits($audioPath, $silenceRegions);
            $analysis->detected_edits = $detectedEdits;
            $analysis->save();

            // Step 8: Calculate manipulation score
            $manipulationScore = $this->calculateManipulationScore($analysis);
            $analysis->manipulation_score = $manipulationScore;

            // Step 9: Generate analysis notes
            $analysis->analysis_notes = $this->generateAnalysisNotes($analysis);

            // Record processing metadata
            $processingTime = microtime(true) - $startTime;
            $analysis->processing_metadata = [
                'tools_used' => $this->getToolsUsed(),
                'processing_time_seconds' => round($processingTime, 2),
                'analyzed_at' => now()->toIso8601String(),
            ];

            $analysis->markAsCompleted();

            Log::info('Audio analysis completed', [
                'analysis_id' => $analysis->id,
                'manipulation_score' => $manipulationScore,
                'processing_time' => round($processingTime, 2),
            ]);

            return $analysis->fresh();

        } catch (\Exception $e) {
            Log::error('Audio analysis failed', [
                'analysis_id' => $analysis->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $analysis->markAsFailed($e->getMessage());
            throw $e;
        }
    }

    /**
     * Extract technical metadata from audio file using ffprobe.
     */
    public function extractMetadata(string $audioPath): array
    {
        $result = Process::run([
            'ffprobe',
            '-v', 'quiet',
            '-print_format', 'json',
            '-show_format',
            '-show_streams',
            $audioPath,
        ]);

        if (!$result->successful()) {
            throw new \Exception('Failed to extract audio metadata: ' . $result->errorOutput());
        }

        $data = json_decode($result->output(), true);
        $format = $data['format'] ?? [];
        $audioStream = collect($data['streams'] ?? [])->firstWhere('codec_type', 'audio') ?? [];

        // Calculate amplitude statistics using sox
        $amplitudeStats = $this->getAmplitudeStats($audioPath);

        return [
            'duration_seconds' => isset($format['duration']) ? (float) $format['duration'] : null,
            'sample_rate' => isset($audioStream['sample_rate']) ? (int) $audioStream['sample_rate'] : null,
            'channels' => isset($audioStream['channels']) ? (int) $audioStream['channels'] : null,
            'bit_depth' => $this->extractBitDepth($audioStream),
            'codec' => $audioStream['codec_name'] ?? null,
            'container_format' => $format['format_name'] ?? null,
            'average_amplitude' => $amplitudeStats['average'] ?? null,
            'peak_amplitude' => $amplitudeStats['peak'] ?? null,
            'noise_floor_db' => $amplitudeStats['noise_floor'] ?? null,
        ];
    }

    /**
     * Extract bit depth from audio stream info.
     */
    protected function extractBitDepth(array $audioStream): ?int
    {
        // Try bits_per_raw_sample first
        if (isset($audioStream['bits_per_raw_sample'])) {
            return (int) $audioStream['bits_per_raw_sample'];
        }

        // Try to infer from sample_fmt
        $sampleFmt = $audioStream['sample_fmt'] ?? '';
        return match ($sampleFmt) {
            's16', 's16p' => 16,
            's24', 's24p' => 24,
            's32', 's32p', 'flt', 'fltp' => 32,
            'dbl', 'dblp' => 64,
            default => null,
        };
    }

    /**
     * Get amplitude statistics using sox.
     */
    protected function getAmplitudeStats(string $audioPath): array
    {
        $result = Process::run([
            'sox',
            $audioPath,
            '-n',
            'stat',
        ]);

        // Sox outputs stats to stderr
        $output = $result->errorOutput();

        $stats = [];

        // Parse RMS amplitude (average)
        if (preg_match('/RMS\s+amplitude:\s+([\d.]+)/i', $output, $matches)) {
            $stats['average'] = (float) $matches[1];
        }

        // Parse peak amplitude
        if (preg_match('/Maximum\s+amplitude:\s+([\d.]+)/i', $output, $matches)) {
            $stats['peak'] = (float) $matches[1];
        }

        // Parse noise floor (minimum amplitude > 0)
        if (preg_match('/Minimum\s+amplitude:\s+([\d.-]+)/i', $output, $matches)) {
            $minAmp = abs((float) $matches[1]);
            // Convert to dB (reference 1.0)
            $stats['noise_floor'] = $minAmp > 0 ? 20 * log10($minAmp) : -120.0;
        }

        return $stats;
    }

    /**
     * Generate a spectrogram image for the audio file.
     */
    public function generateSpectrogram(
        string $audioPath,
        array $options = [],
        ?string $identifier = null
    ): string {
        $options = array_merge($this->defaultSpectrogramOptions, $options);
        $identifier = $identifier ?? Str::uuid()->toString();

        // Create output directory
        $outputDir = 'audio-analysis/' . substr($identifier, 0, 2);
        Storage::disk('public')->makeDirectory($outputDir);

        $outputPath = "{$outputDir}/{$identifier}_spectrogram.png";
        $fullOutputPath = Storage::disk('public')->path($outputPath);

        // Use sox to generate spectrogram
        $result = Process::run([
            'sox',
            $audioPath,
            '-n',
            'spectrogram',
            '-x', (string) $options['width'],
            '-y', (string) $options['height'],
            '-z', (string) $options['dynamic_range'],
            '-o', $fullOutputPath,
        ]);

        if (!$result->successful()) {
            // Fallback to ffmpeg if sox fails
            return $this->generateSpectrogramWithFfmpeg($audioPath, $options, $outputPath);
        }

        return $outputPath;
    }

    /**
     * Generate spectrogram using ffmpeg as fallback.
     */
    protected function generateSpectrogramWithFfmpeg(
        string $audioPath,
        array $options,
        string $outputPath
    ): string {
        $fullOutputPath = Storage::disk('public')->path($outputPath);

        $result = Process::run([
            'ffmpeg',
            '-y',
            '-i', $audioPath,
            '-lavfi', sprintf(
                'showspectrumpic=s=%dx%d:color=channel:scale=log',
                $options['width'],
                $options['height']
            ),
            $fullOutputPath,
        ]);

        if (!$result->successful()) {
            throw new \Exception('Failed to generate spectrogram: ' . $result->errorOutput());
        }

        return $outputPath;
    }

    /**
     * Generate a waveform visualization image.
     */
    public function generateWaveform(
        string $audioPath,
        array $options = [],
        ?string $identifier = null
    ): string {
        $options = array_merge($this->defaultWaveformOptions, $options);
        $identifier = $identifier ?? Str::uuid()->toString();

        // Create output directory
        $outputDir = 'audio-analysis/' . substr($identifier, 0, 2);
        Storage::disk('public')->makeDirectory($outputDir);

        $outputPath = "{$outputDir}/{$identifier}_waveform.png";
        $fullOutputPath = Storage::disk('public')->path($outputPath);

        // Use ffmpeg to generate waveform
        $result = Process::run([
            'ffmpeg',
            '-y',
            '-i', $audioPath,
            '-filter_complex', sprintf(
                'showwavespic=s=%dx%d:colors=%s:split_channels=0',
                $options['width'],
                $options['height'],
                $options['color']
            ),
            '-frames:v', '1',
            $fullOutputPath,
        ]);

        if (!$result->successful()) {
            throw new \Exception('Failed to generate waveform: ' . $result->errorOutput());
        }

        return $outputPath;
    }

    /**
     * Detect silence regions in the audio.
     */
    public function detectSilence(string $audioPath): array
    {
        $result = Process::run([
            'ffmpeg',
            '-i', $audioPath,
            '-af', sprintf(
                'silencedetect=noise=%ddB:d=%s',
                (int) $this->silenceThresholdDb,
                $this->minSilenceDuration
            ),
            '-f', 'null',
            '-',
        ]);

        $output = $result->errorOutput();
        $silenceRegions = [];

        // Parse silence_start and silence_end from ffmpeg output
        preg_match_all('/silence_start:\s*([\d.]+)/', $output, $starts);
        preg_match_all('/silence_end:\s*([\d.]+)\s*\|\s*silence_duration:\s*([\d.]+)/', $output, $ends);

        $startTimes = $starts[1] ?? [];
        $endTimes = $ends[1] ?? [];
        $durations = $ends[2] ?? [];

        for ($i = 0; $i < count($startTimes); $i++) {
            $silenceRegions[] = [
                'start' => (float) $startTimes[$i],
                'end' => (float) ($endTimes[$i] ?? $startTimes[$i] + 1),
                'duration' => (float) ($durations[$i] ?? 1),
            ];
        }

        return $silenceRegions;
    }

    /**
     * Detect voice activity regions (inverse of silence).
     */
    public function detectVoiceActivity(string $audioPath, array $silenceRegions = []): array
    {
        if (empty($silenceRegions)) {
            $silenceRegions = $this->detectSilence($audioPath);
        }

        // Get audio duration
        $metadata = $this->extractMetadata($audioPath);
        $duration = $metadata['duration_seconds'] ?? 0;

        if ($duration <= 0) {
            return [];
        }

        $voiceRegions = [];
        $currentPos = 0.0;

        foreach ($silenceRegions as $silence) {
            if ($silence['start'] > $currentPos) {
                $voiceRegions[] = [
                    'start' => $currentPos,
                    'end' => $silence['start'],
                    'speaker_id' => null, // Could be enhanced with speaker diarization
                    'confidence' => 0.8, // Base confidence
                ];
            }
            $currentPos = $silence['end'];
        }

        // Add final region if there's voice activity at the end
        if ($currentPos < $duration) {
            $voiceRegions[] = [
                'start' => $currentPos,
                'end' => $duration,
                'speaker_id' => null,
                'confidence' => 0.8,
            ];
        }

        return $voiceRegions;
    }

    /**
     * Analyze frequency distribution of the audio.
     */
    public function analyzeFrequencies(string $audioPath): array
    {
        // Use ffmpeg to extract frequency data
        $tempFile = sys_get_temp_dir() . '/' . Str::uuid() . '_freq.txt';

        $result = Process::run([
            'ffmpeg',
            '-i', $audioPath,
            '-af', 'aspectralstats=win_size=4096:measure=mean',
            '-f', 'null',
            '-',
        ]);

        $output = $result->errorOutput();

        // Parse dominant frequencies
        $dominantFrequencies = [];

        // Look for frequency peaks in the output
        if (preg_match_all('/mean:\s*([\d.]+)/', $output, $matches)) {
            $dominantFrequencies = array_map('floatval', array_slice($matches[1], 0, 10));
        }

        // Simple frequency band analysis using sox
        $bandAnalysis = $this->analyzeBands($audioPath);

        return [
            'dominant_frequencies' => $dominantFrequencies,
            'spectrum_data' => $bandAnalysis,
            'harmonics' => $this->detectHarmonics($dominantFrequencies),
            'bandwidth' => $this->estimateBandwidth($bandAnalysis),
        ];
    }

    /**
     * Analyze frequency bands.
     */
    protected function analyzeBands(string $audioPath): array
    {
        $bands = [
            'sub_bass' => [20, 60],
            'bass' => [60, 250],
            'low_mid' => [250, 500],
            'mid' => [500, 2000],
            'upper_mid' => [2000, 4000],
            'presence' => [4000, 6000],
            'brilliance' => [6000, 20000],
        ];

        $bandData = [];

        foreach ($bands as $name => $range) {
            // Get average level for each band using sox
            $result = Process::run([
                'sox',
                $audioPath,
                '-n',
                'sinc', "{$range[0]}-{$range[1]}",
                'stat',
            ]);

            $output = $result->errorOutput();
            $level = 0.0;

            if (preg_match('/RMS\s+amplitude:\s+([\d.]+)/i', $output, $matches)) {
                $level = (float) $matches[1];
            }

            $bandData[$name] = [
                'range' => $range,
                'level' => $level,
                'db' => $level > 0 ? 20 * log10($level) : -120,
            ];
        }

        return $bandData;
    }

    /**
     * Detect harmonic relationships in frequencies.
     */
    protected function detectHarmonics(array $frequencies): array
    {
        if (count($frequencies) < 2) {
            return [];
        }

        $harmonics = [];
        $fundamental = $frequencies[0] ?? 0;

        if ($fundamental <= 0) {
            return [];
        }

        foreach ($frequencies as $freq) {
            $ratio = $freq / $fundamental;
            $nearestHarmonic = round($ratio);

            if ($nearestHarmonic > 1 && abs($ratio - $nearestHarmonic) < 0.1) {
                $harmonics[] = [
                    'frequency' => $freq,
                    'harmonic_number' => (int) $nearestHarmonic,
                    'deviation' => abs($ratio - $nearestHarmonic),
                ];
            }
        }

        return $harmonics;
    }

    /**
     * Estimate audio bandwidth.
     */
    protected function estimateBandwidth(array $bandAnalysis): ?array
    {
        if (empty($bandAnalysis)) {
            return null;
        }

        $threshold = -60; // dB threshold for "active" frequency content
        $activeBands = [];

        foreach ($bandAnalysis as $name => $data) {
            if ($data['db'] > $threshold) {
                $activeBands[] = $data['range'];
            }
        }

        if (empty($activeBands)) {
            return null;
        }

        return [
            'low' => min(array_column($activeBands, 0)),
            'high' => max(array_column($activeBands, 1)),
        ];
    }

    /**
     * Detect potential edits, cuts, and splices in the audio.
     */
    public function detectEdits(string $audioPath, array $silenceRegions = []): array
    {
        $edits = [];

        // Detection method 1: Analyze silence gaps for suspicious patterns
        foreach ($silenceRegions as $silence) {
            // Very short silences in unexpected places can indicate cuts
            if ($silence['duration'] < 0.1 && $silence['start'] > 1.0) {
                $edits[] = [
                    'timestamp' => $silence['start'],
                    'type' => 'potential_cut',
                    'confidence' => 0.5,
                    'description' => 'Very short silence detected, possible cut point',
                ];
            }

            // Unusually long silences in the middle might indicate removed content
            if ($silence['duration'] > 3.0 && $silence['start'] > 2.0) {
                $edits[] = [
                    'timestamp' => $silence['start'],
                    'type' => 'potential_removal',
                    'confidence' => 0.4,
                    'description' => 'Extended silence detected, possible content removal',
                ];
            }
        }

        // Detection method 2: Analyze for sudden amplitude changes (clicks/pops)
        $clickDetection = $this->detectClicks($audioPath);
        foreach ($clickDetection as $click) {
            $edits[] = [
                'timestamp' => $click['timestamp'],
                'type' => 'click_artifact',
                'confidence' => $click['confidence'],
                'description' => 'Click or pop artifact detected, possible edit point',
            ];
        }

        // Detection method 3: Analyze phase discontinuities
        $phaseDiscontinuities = $this->detectPhaseDiscontinuities($audioPath);
        foreach ($phaseDiscontinuities as $disc) {
            $edits[] = [
                'timestamp' => $disc['timestamp'],
                'type' => 'phase_discontinuity',
                'confidence' => $disc['confidence'],
                'description' => 'Phase discontinuity detected, possible splice point',
            ];
        }

        // Sort by timestamp
        usort($edits, fn($a, $b) => $a['timestamp'] <=> $b['timestamp']);

        // Merge nearby detections
        return $this->mergeNearbyEdits($edits);
    }

    /**
     * Detect clicks and pops using high-pass filtering.
     */
    protected function detectClicks(string $audioPath): array
    {
        // Use ffmpeg to detect high-amplitude transients
        $result = Process::run([
            'ffmpeg',
            '-i', $audioPath,
            '-af', 'highpass=f=8000,astats=metadata=1:reset=1',
            '-f', 'null',
            '-',
        ]);

        $output = $result->errorOutput();
        $clicks = [];

        // Parse peak levels that exceed normal thresholds
        if (preg_match_all('/Peak level dB:\s*([-\d.]+).*?pts_time:([\d.]+)/s', $output, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $peakDb = (float) $match[1];
                $timestamp = (float) $match[2];

                // High-frequency peaks suggest clicks
                if ($peakDb > -10) {
                    $clicks[] = [
                        'timestamp' => $timestamp,
                        'confidence' => min(1.0, ($peakDb + 20) / 20),
                    ];
                }
            }
        }

        return $clicks;
    }

    /**
     * Detect phase discontinuities.
     */
    protected function detectPhaseDiscontinuities(string $audioPath): array
    {
        // This is a simplified detection - full implementation would use
        // cross-correlation analysis across short windows
        $discontinuities = [];

        $result = Process::run([
            'ffmpeg',
            '-i', $audioPath,
            '-af', 'aphaseshift',
            '-f', 'null',
            '-',
        ]);

        // Parse phase shift warnings
        $output = $result->errorOutput();

        if (preg_match_all('/phase.*?(\d+\.\d+)/i', $output, $matches)) {
            foreach ($matches[1] as $timestamp) {
                $discontinuities[] = [
                    'timestamp' => (float) $timestamp,
                    'confidence' => 0.5,
                ];
            }
        }

        return $discontinuities;
    }

    /**
     * Merge nearby edit detections into single entries.
     */
    protected function mergeNearbyEdits(array $edits, float $threshold = 0.1): array
    {
        if (count($edits) < 2) {
            return $edits;
        }

        $merged = [];
        $current = $edits[0];

        for ($i = 1; $i < count($edits); $i++) {
            if ($edits[$i]['timestamp'] - $current['timestamp'] < $threshold) {
                // Merge: keep highest confidence and combine descriptions
                if ($edits[$i]['confidence'] > $current['confidence']) {
                    $current['confidence'] = $edits[$i]['confidence'];
                    $current['type'] = $edits[$i]['type'];
                }
                $current['description'] = 'Multiple edit indicators detected at this timestamp';
            } else {
                $merged[] = $current;
                $current = $edits[$i];
            }
        }

        $merged[] = $current;
        return $merged;
    }

    /**
     * Calculate an overall manipulation score (0-100).
     */
    public function calculateManipulationScore(AudioAnalysis $analysis): int
    {
        $score = 0;
        $factors = [];

        // Factor 1: Number of detected edits (0-30 points)
        $editCount = count($analysis->detected_edits ?? []);
        $editScore = min(30, $editCount * 10);
        $score += $editScore;
        $factors['edits'] = $editScore;

        // Factor 2: High-confidence edits (0-25 points)
        $highConfidenceEdits = array_filter(
            $analysis->detected_edits ?? [],
            fn($e) => ($e['confidence'] ?? 0) >= 0.7
        );
        $highConfScore = min(25, count($highConfidenceEdits) * 12);
        $score += $highConfScore;
        $factors['high_confidence_edits'] = $highConfScore;

        // Factor 3: Suspicious silence patterns (0-20 points)
        $silenceRegions = $analysis->silence_regions ?? [];
        $suspiciousSilence = array_filter($silenceRegions, function ($s) {
            // Short gaps or very regular gaps are suspicious
            return ($s['duration'] ?? 0) < 0.15 || ($s['duration'] ?? 0) > 5.0;
        });
        $silenceScore = min(20, count($suspiciousSilence) * 5);
        $score += $silenceScore;
        $factors['suspicious_silence'] = $silenceScore;

        // Factor 4: Frequency anomalies (0-15 points)
        $freqAnalysis = $analysis->frequency_analysis ?? [];
        $bandwidth = $freqAnalysis['bandwidth'] ?? null;
        if ($bandwidth && ($bandwidth['high'] ?? 0) < 4000) {
            // Unusually narrow bandwidth might indicate processing
            $score += 10;
            $factors['narrow_bandwidth'] = 10;
        }

        // Factor 5: Amplitude anomalies (0-10 points)
        if ($analysis->peak_amplitude !== null && $analysis->average_amplitude !== null) {
            $dynamicRange = $analysis->peak_amplitude / max(0.0001, $analysis->average_amplitude);
            if ($dynamicRange < 2.0 || $dynamicRange > 20.0) {
                // Unusual dynamic range
                $score += 10;
                $factors['dynamic_range'] = 10;
            }
        }

        // Cap at 100
        $finalScore = min(100, $score);

        Log::debug('Manipulation score calculated', [
            'analysis_id' => $analysis->id,
            'score' => $finalScore,
            'factors' => $factors,
        ]);

        return $finalScore;
    }

    /**
     * Generate human-readable analysis notes.
     */
    public function generateAnalysisNotes(AudioAnalysis $analysis): string
    {
        $notes = [];

        // Technical summary
        $notes[] = sprintf(
            "Audio: %s, %s, %d Hz, %s",
            $analysis->getFormattedDuration(),
            $analysis->getFormattedChannels(),
            $analysis->sample_rate ?? 0,
            $analysis->codec ?? 'unknown codec'
        );

        // Edit detection summary
        $editCount = count($analysis->detected_edits ?? []);
        if ($editCount > 0) {
            $highConfCount = count(array_filter(
                $analysis->detected_edits ?? [],
                fn($e) => ($e['confidence'] ?? 0) >= 0.7
            ));
            $notes[] = sprintf(
                "Edit Detection: %d potential edit point(s) found (%d with high confidence).",
                $editCount,
                $highConfCount
            );
        } else {
            $notes[] = "Edit Detection: No obvious edit points detected.";
        }

        // Voice activity summary
        $voicePercent = $analysis->getVoiceActivityPercentage();
        $notes[] = sprintf(
            "Voice Activity: %.1f%% of audio contains voice/speech.",
            $voicePercent
        );

        // Manipulation risk assessment
        $risk = $analysis->getManipulationRisk();
        $riskDescriptions = [
            'low' => 'The audio appears authentic with no significant signs of manipulation.',
            'medium' => 'Some indicators of potential editing detected. Manual review recommended.',
            'high' => 'Multiple indicators of editing detected. Careful verification advised.',
            'critical' => 'Strong evidence of manipulation. This audio should be treated with caution.',
            'unknown' => 'Analysis incomplete. Unable to assess manipulation risk.',
        ];
        $notes[] = sprintf(
            "Manipulation Risk: %s - %s",
            ucfirst($risk),
            $riskDescriptions[$risk] ?? $riskDescriptions['unknown']
        );

        return implode("\n\n", $notes);
    }

    /**
     * Compare two audio segments for similarity.
     */
    public function compareAudioSegments(string $path1, string $path2): array
    {
        // Extract fingerprints using chromaprint (if available) or fall back to simple comparison
        $fingerprint1 = $this->getAudioFingerprint($path1);
        $fingerprint2 = $this->getAudioFingerprint($path2);

        if ($fingerprint1 && $fingerprint2) {
            $similarity = $this->calculateFingerprintSimilarity($fingerprint1, $fingerprint2);
        } else {
            // Fallback: compare basic statistics
            $stats1 = $this->getAmplitudeStats($path1);
            $stats2 = $this->getAmplitudeStats($path2);
            $similarity = $this->calculateStatsSimilarity($stats1, $stats2);
        }

        return [
            'similarity_score' => $similarity,
            'is_similar' => $similarity > 0.8,
            'is_likely_same_source' => $similarity > 0.95,
            'method' => $fingerprint1 && $fingerprint2 ? 'fingerprint' : 'statistical',
        ];
    }

    /**
     * Get audio fingerprint using fpcalc (chromaprint).
     */
    protected function getAudioFingerprint(string $audioPath): ?string
    {
        $result = Process::run([
            'fpcalc',
            '-raw',
            $audioPath,
        ]);

        if (!$result->successful()) {
            return null;
        }

        $output = $result->output();
        if (preg_match('/FINGERPRINT=(.+)/', $output, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Calculate similarity between two audio fingerprints.
     */
    protected function calculateFingerprintSimilarity(string $fp1, string $fp2): float
    {
        $arr1 = explode(',', $fp1);
        $arr2 = explode(',', $fp2);

        $minLen = min(count($arr1), count($arr2));
        if ($minLen === 0) {
            return 0.0;
        }

        $matches = 0;
        for ($i = 0; $i < $minLen; $i++) {
            if ($arr1[$i] === $arr2[$i]) {
                $matches++;
            }
        }

        return $matches / $minLen;
    }

    /**
     * Calculate similarity between amplitude statistics.
     */
    protected function calculateStatsSimilarity(array $stats1, array $stats2): float
    {
        $factors = [];

        if (isset($stats1['average'], $stats2['average'])) {
            $avgDiff = abs($stats1['average'] - $stats2['average']);
            $factors[] = max(0, 1 - $avgDiff * 10);
        }

        if (isset($stats1['peak'], $stats2['peak'])) {
            $peakDiff = abs($stats1['peak'] - $stats2['peak']);
            $factors[] = max(0, 1 - $peakDiff * 5);
        }

        if (empty($factors)) {
            return 0.0;
        }

        return array_sum($factors) / count($factors);
    }

    /**
     * Get list of tools used for analysis.
     */
    protected function getToolsUsed(): array
    {
        $tools = [];

        // Check for ffmpeg
        $result = Process::run(['ffmpeg', '-version']);
        if ($result->successful() && preg_match('/ffmpeg version (\S+)/', $result->output(), $m)) {
            $tools['ffmpeg'] = $m[1];
        }

        // Check for sox
        $result = Process::run(['sox', '--version']);
        if ($result->successful() && preg_match('/SoX v?([\d.]+)/', $result->output(), $m)) {
            $tools['sox'] = $m[1];
        }

        // Check for fpcalc (chromaprint)
        $result = Process::run(['fpcalc', '-version']);
        if ($result->successful() && preg_match('/fpcalc version (\S+)/', $result->output(), $m)) {
            $tools['fpcalc'] = $m[1];
        }

        return $tools;
    }

    /**
     * Regenerate visualizations for an existing analysis.
     */
    public function regenerateVisualizations(
        AudioAnalysis $analysis,
        array $spectrogramOptions = [],
        array $waveformOptions = []
    ): AudioAnalysis {
        $audioFile = $analysis->audioFile;
        $audioPath = Storage::disk('public')->path($audioFile->file_path);

        // Delete old visualizations
        if ($analysis->spectrogram_path) {
            Storage::disk('public')->delete($analysis->spectrogram_path);
        }
        if ($analysis->waveform_path) {
            Storage::disk('public')->delete($analysis->waveform_path);
        }

        // Generate new visualizations
        $analysis->spectrogram_path = $this->generateSpectrogram(
            $audioPath,
            $spectrogramOptions,
            $analysis->uuid
        );

        $analysis->waveform_path = $this->generateWaveform(
            $audioPath,
            $waveformOptions,
            $analysis->uuid
        );

        $analysis->save();

        return $analysis;
    }
}
