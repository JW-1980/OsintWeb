<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\VideoAnalysis;
use App\Models\VideoKeyframe;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VideoAnalysisService
{
    protected string $ffprobePath;
    protected string $ffmpegPath;

    public function __construct()
    {
        $this->ffprobePath = config('services.ffmpeg.ffprobe_path', '/usr/bin/ffprobe');
        $this->ffmpegPath = config('services.ffmpeg.ffmpeg_path', '/usr/bin/ffmpeg');
    }

    /**
     * Store uploaded video and create analysis record.
     */
    public function storeVideo(UploadedFile $file, User $user, ?int $eventId = null): VideoAnalysis
    {
        $uuid = Str::uuid()->toString();
        $extension = $file->getClientOriginalExtension();
        $filename = "{$uuid}.{$extension}";

        // Store the video file
        $path = $file->storeAs("videos/{$uuid}", $filename, 'public');

        // Create the analysis record
        return VideoAnalysis::create([
            'uuid' => $uuid,
            'original_filename' => $file->getClientOriginalName(),
            'stored_path' => $path,
            'file_size' => $file->getSize(),
            'analysis_status' => VideoAnalysis::STATUS_PENDING,
            'event_id' => $eventId,
            'created_by' => $user->id,
        ]);
    }

    /**
     * Analyze video and extract all metadata using ffprobe.
     */
    public function analyzeVideo(VideoAnalysis $analysis): VideoAnalysis
    {
        $analysis->markAsProcessing();

        try {
            $videoPath = Storage::disk('public')->path($analysis->stored_path);

            if (!file_exists($videoPath)) {
                throw new \Exception("Video file not found: {$videoPath}");
            }

            // Extract metadata using ffprobe
            $metadata = $this->extractMetadataWithFfprobe($videoPath);

            // Parse video stream information
            $videoStream = $this->findVideoStream($metadata);
            $audioStream = $this->findAudioStream($metadata);

            // Extract GPS coordinates from metadata
            $gpsData = $this->extractGpsFromMetadata($metadata);

            // Extract device information
            $deviceInfo = $this->extractDeviceInfo($metadata);

            // Extract recording datetime
            $recordingDatetime = $this->extractRecordingDatetime($metadata);

            // Extract EXIF data if available
            $exifData = $this->extractExifData($metadata);

            // Generate thumbnail
            $thumbnailPath = $this->generateThumbnail($analysis, $videoPath);

            // Update analysis with extracted data
            $analysis->update([
                'duration_seconds' => isset($metadata['format']['duration'])
                    ? (float) $metadata['format']['duration']
                    : null,
                'frame_count' => $videoStream['nb_frames'] ?? null,
                'resolution_width' => $videoStream['width'] ?? null,
                'resolution_height' => $videoStream['height'] ?? null,
                'fps' => $this->parseFps($videoStream['r_frame_rate'] ?? null),
                'codec' => $videoStream['codec_name'] ?? null,
                'container_format' => $metadata['format']['format_name'] ?? null,
                'metadata' => $metadata,
                'exif_data' => $exifData,
                'gps_latitude' => $gpsData['latitude'] ?? null,
                'gps_longitude' => $gpsData['longitude'] ?? null,
                'gps_accuracy_meters' => $gpsData['accuracy'] ?? null,
                'recording_datetime' => $recordingDatetime,
                'timezone' => $this->extractTimezone($metadata),
                'device_info' => $deviceInfo,
                'thumbnail_path' => $thumbnailPath,
            ]);

            $analysis->markAsCompleted();

            Log::info('Video analysis completed', [
                'video_id' => $analysis->id,
                'duration' => $analysis->duration_seconds,
                'resolution' => $analysis->getResolutionString(),
            ]);

            return $analysis->fresh();

        } catch (\Exception $e) {
            Log::error('Video analysis failed', [
                'video_id' => $analysis->id,
                'error' => $e->getMessage(),
            ]);

            $analysis->markAsFailed($e->getMessage());
            throw $e;
        }
    }

    /**
     * Extract metadata using ffprobe.
     */
    protected function extractMetadataWithFfprobe(string $videoPath): array
    {
        $command = [
            $this->ffprobePath,
            '-v', 'quiet',
            '-print_format', 'json',
            '-show_format',
            '-show_streams',
            '-show_chapters',
            $videoPath,
        ];

        $result = Process::run($command);

        if (!$result->successful()) {
            throw new \Exception("ffprobe failed: " . $result->errorOutput());
        }

        $json = json_decode($result->output(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Failed to parse ffprobe output: " . json_last_error_msg());
        }

        return $json;
    }

    /**
     * Find video stream from metadata.
     */
    protected function findVideoStream(array $metadata): ?array
    {
        foreach ($metadata['streams'] ?? [] as $stream) {
            if ($stream['codec_type'] === 'video') {
                return $stream;
            }
        }
        return null;
    }

    /**
     * Find audio stream from metadata.
     */
    protected function findAudioStream(array $metadata): ?array
    {
        foreach ($metadata['streams'] ?? [] as $stream) {
            if ($stream['codec_type'] === 'audio') {
                return $stream;
            }
        }
        return null;
    }

    /**
     * Parse FPS from frame rate string (e.g., "30000/1001" or "30/1").
     */
    protected function parseFps(?string $frameRate): ?float
    {
        if (!$frameRate) {
            return null;
        }

        $parts = explode('/', $frameRate);
        if (count($parts) === 2 && $parts[1] > 0) {
            return round((float) $parts[0] / (float) $parts[1], 4);
        }

        return is_numeric($frameRate) ? (float) $frameRate : null;
    }

    /**
     * Extract GPS coordinates from metadata.
     */
    public function extractGpsFromMetadata(array $metadata): array
    {
        $tags = $metadata['format']['tags'] ?? [];

        // Check common GPS tag locations
        $locationTag = $tags['location'] ?? $tags['com.apple.quicktime.location.ISO6709'] ?? null;

        if ($locationTag) {
            return $this->parseIso6709Location($locationTag);
        }

        // Check for separate latitude/longitude tags
        $lat = $tags['location-lat'] ?? $tags['latitude'] ?? null;
        $lng = $tags['location-lng'] ?? $tags['longitude'] ?? null;

        if ($lat !== null && $lng !== null) {
            return [
                'latitude' => (float) $lat,
                'longitude' => (float) $lng,
                'accuracy' => null,
            ];
        }

        // Check stream metadata for GPS
        foreach ($metadata['streams'] ?? [] as $stream) {
            $streamTags = $stream['tags'] ?? [];
            if (isset($streamTags['location'])) {
                return $this->parseIso6709Location($streamTags['location']);
            }
        }

        return [];
    }

    /**
     * Parse ISO 6709 location string (e.g., "+40.7128-074.0060/").
     */
    protected function parseIso6709Location(string $location): array
    {
        // Match patterns like +40.7128-074.0060+10.000/ or +40.7128-074.0060/
        $pattern = '/([+-]?\d+\.?\d*)([+-]\d+\.?\d*)([+-]?\d+\.?\d*)?/';

        if (preg_match($pattern, $location, $matches)) {
            return [
                'latitude' => (float) $matches[1],
                'longitude' => (float) $matches[2],
                'altitude' => isset($matches[3]) ? (float) $matches[3] : null,
                'accuracy' => null,
            ];
        }

        return [];
    }

    /**
     * Extract device/camera information from metadata.
     */
    public function extractDeviceInfo(array $metadata): array
    {
        $tags = $metadata['format']['tags'] ?? [];

        $deviceInfo = [
            'make' => $tags['com.apple.quicktime.make'] ?? $tags['make'] ?? null,
            'model' => $tags['com.apple.quicktime.model'] ?? $tags['model'] ?? null,
            'software' => $tags['com.apple.quicktime.software'] ?? $tags['encoder'] ?? $tags['software'] ?? null,
            'creation_time' => $tags['creation_time'] ?? null,
        ];

        // Clean up null values
        return array_filter($deviceInfo, fn($v) => $v !== null);
    }

    /**
     * Extract recording datetime from metadata.
     */
    protected function extractRecordingDatetime(array $metadata): ?\Carbon\Carbon
    {
        $tags = $metadata['format']['tags'] ?? [];

        $dateString = $tags['creation_time']
            ?? $tags['com.apple.quicktime.creationdate']
            ?? $tags['date']
            ?? null;

        if (!$dateString) {
            return null;
        }

        try {
            return \Carbon\Carbon::parse($dateString);
        } catch (\Exception) {
            return null;
        }
    }

    /**
     * Extract timezone from metadata.
     */
    protected function extractTimezone(array $metadata): ?string
    {
        $tags = $metadata['format']['tags'] ?? [];

        // Check for timezone in creation date
        $dateString = $tags['creation_time'] ?? $tags['com.apple.quicktime.creationdate'] ?? null;

        if ($dateString && preg_match('/([+-]\d{2}:?\d{2}|Z)$/', $dateString, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Extract EXIF-like data from metadata.
     */
    protected function extractExifData(array $metadata): array
    {
        $tags = $metadata['format']['tags'] ?? [];

        $exifData = [];

        // Map common metadata to EXIF-like structure
        $mappings = [
            'Make' => ['com.apple.quicktime.make', 'make'],
            'Model' => ['com.apple.quicktime.model', 'model'],
            'Software' => ['com.apple.quicktime.software', 'encoder', 'software'],
            'DateTimeOriginal' => ['creation_time', 'com.apple.quicktime.creationdate'],
            'GPSLatitude' => ['location-lat', 'latitude'],
            'GPSLongitude' => ['location-lng', 'longitude'],
            'Copyright' => ['copyright'],
            'Artist' => ['artist', 'author'],
            'Title' => ['title'],
            'Description' => ['description', 'comment'],
        ];

        foreach ($mappings as $exifKey => $tagKeys) {
            foreach ($tagKeys as $tagKey) {
                if (isset($tags[$tagKey])) {
                    $exifData[$exifKey] = $tags[$tagKey];
                    break;
                }
            }
        }

        return $exifData;
    }

    /**
     * Generate thumbnail for video.
     */
    protected function generateThumbnail(VideoAnalysis $analysis, string $videoPath): ?string
    {
        try {
            $thumbnailDir = dirname($analysis->stored_path);
            $thumbnailFilename = pathinfo($analysis->stored_path, PATHINFO_FILENAME) . '_thumb.jpg';
            $thumbnailPath = "{$thumbnailDir}/{$thumbnailFilename}";
            $fullThumbnailPath = Storage::disk('public')->path($thumbnailPath);

            // Ensure directory exists
            $dir = dirname($fullThumbnailPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            // Extract thumbnail at 1 second or at 10% of duration
            $seekTime = '00:00:01';

            $command = [
                $this->ffmpegPath,
                '-ss', $seekTime,
                '-i', $videoPath,
                '-vframes', '1',
                '-vf', 'scale=320:-1',
                '-q:v', '3',
                '-y',
                $fullThumbnailPath,
            ];

            $result = Process::timeout(30)->run($command);

            if ($result->successful() && file_exists($fullThumbnailPath)) {
                return $thumbnailPath;
            }

            Log::warning('Thumbnail generation failed', [
                'video_id' => $analysis->id,
                'error' => $result->errorOutput(),
            ]);

            return null;

        } catch (\Exception $e) {
            Log::error('Thumbnail generation error', [
                'video_id' => $analysis->id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Extract keyframes at specified intervals.
     */
    public function extractKeyframes(
        VideoAnalysis $analysis,
        float $intervalSeconds = 5.0,
        bool $includeSceneChanges = true
    ): array {
        $videoPath = Storage::disk('public')->path($analysis->stored_path);

        if (!file_exists($videoPath)) {
            throw new \Exception("Video file not found: {$videoPath}");
        }

        $duration = (float) ($analysis->duration_seconds ?? 0);
        $fps = (float) ($analysis->fps ?? 30);

        $keyframes = [];
        $timestamp = 0.0;

        // Extract frames at intervals
        while ($timestamp < $duration) {
            $frameNumber = (int) ($timestamp * $fps);
            $keyframe = $this->extractFrameAtTimestamp($analysis, $timestamp, $frameNumber);

            if ($keyframe) {
                $keyframes[] = $keyframe;
            }

            $timestamp += $intervalSeconds;
        }

        Log::info('Keyframes extracted', [
            'video_id' => $analysis->id,
            'count' => count($keyframes),
            'interval' => $intervalSeconds,
        ]);

        return $keyframes;
    }

    /**
     * Detect scene changes in video.
     */
    public function detectSceneChanges(VideoAnalysis $analysis, float $threshold = 0.4): array
    {
        $videoPath = Storage::disk('public')->path($analysis->stored_path);

        if (!file_exists($videoPath)) {
            throw new \Exception("Video file not found: {$videoPath}");
        }

        // Use ffmpeg scene detection filter
        $command = [
            $this->ffmpegPath,
            '-i', $videoPath,
            '-vf', "select='gt(scene,{$threshold})',showinfo",
            '-f', 'null',
            '-',
        ];

        $result = Process::timeout(300)->run($command);

        $sceneChanges = [];
        $output = $result->errorOutput(); // ffmpeg outputs to stderr

        // Parse showinfo output for timestamps
        $pattern = '/pts_time:(\d+\.?\d*)/';
        preg_match_all($pattern, $output, $matches);

        $fps = (float) ($analysis->fps ?? 30);

        foreach ($matches[1] ?? [] as $timestamp) {
            $timestamp = (float) $timestamp;
            $frameNumber = (int) ($timestamp * $fps);

            $keyframe = $this->extractFrameAtTimestamp($analysis, $timestamp, $frameNumber, true);

            if ($keyframe) {
                $keyframe->update([
                    'is_scene_change' => true,
                    'scene_change_score' => $threshold,
                ]);
                $sceneChanges[] = $keyframe;
            }
        }

        Log::info('Scene changes detected', [
            'video_id' => $analysis->id,
            'count' => count($sceneChanges),
            'threshold' => $threshold,
        ]);

        return $sceneChanges;
    }

    /**
     * Extract frame at specific timestamp.
     */
    public function extractFrameAtTimestamp(
        VideoAnalysis $analysis,
        float $timestamp,
        ?int $frameNumber = null,
        bool $isSceneChange = false
    ): ?VideoKeyframe {
        try {
            $videoPath = Storage::disk('public')->path($analysis->stored_path);

            if (!file_exists($videoPath)) {
                throw new \Exception("Video file not found: {$videoPath}");
            }

            $fps = (float) ($analysis->fps ?? 30);
            $frameNumber = $frameNumber ?? (int) ($timestamp * $fps);

            // Create output paths
            $frameDir = dirname($analysis->stored_path) . '/frames';
            $frameName = sprintf('frame_%06d.jpg', $frameNumber);
            $thumbName = sprintf('thumb_%06d.jpg', $frameNumber);

            $framePath = "{$frameDir}/{$frameName}";
            $thumbPath = "{$frameDir}/{$thumbName}";

            $fullFramePath = Storage::disk('public')->path($framePath);
            $fullThumbPath = Storage::disk('public')->path($thumbPath);

            // Ensure directory exists
            $dir = dirname($fullFramePath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            // Format timestamp for ffmpeg
            $timeString = gmdate('H:i:s', (int) $timestamp) . sprintf('.%03d', ($timestamp - floor($timestamp)) * 1000);

            // Extract full resolution frame
            $frameCommand = [
                $this->ffmpegPath,
                '-ss', $timeString,
                '-i', $videoPath,
                '-vframes', '1',
                '-q:v', '2',
                '-y',
                $fullFramePath,
            ];

            $result = Process::timeout(30)->run($frameCommand);

            if (!$result->successful() || !file_exists($fullFramePath)) {
                Log::warning('Frame extraction failed', [
                    'video_id' => $analysis->id,
                    'timestamp' => $timestamp,
                    'error' => $result->errorOutput(),
                ]);
                return null;
            }

            // Generate thumbnail
            $thumbCommand = [
                $this->ffmpegPath,
                '-i', $fullFramePath,
                '-vf', 'scale=160:-1',
                '-q:v', '4',
                '-y',
                $fullThumbPath,
            ];

            Process::timeout(10)->run($thumbCommand);
            $thumbnailPath = file_exists($fullThumbPath) ? $thumbPath : null;

            // Create keyframe record
            $keyframe = VideoKeyframe::create([
                'video_analysis_id' => $analysis->id,
                'frame_number' => $frameNumber,
                'timestamp_seconds' => $timestamp,
                'stored_path' => $framePath,
                'thumbnail_path' => $thumbnailPath,
                'is_scene_change' => $isSceneChange,
            ]);

            return $keyframe;

        } catch (\Exception $e) {
            Log::error('Frame extraction error', [
                'video_id' => $analysis->id,
                'timestamp' => $timestamp,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Get frame at specific timestamp (returns existing or extracts new).
     */
    public function getFrameAt(VideoAnalysis $analysis, float $timestamp): ?VideoKeyframe
    {
        $fps = (float) ($analysis->fps ?? 30);
        $frameNumber = (int) ($timestamp * $fps);

        // Check if frame already exists
        $existing = $analysis->keyframes()
            ->where('frame_number', $frameNumber)
            ->first();

        if ($existing) {
            return $existing;
        }

        // Extract the frame
        return $this->extractFrameAtTimestamp($analysis, $timestamp, $frameNumber);
    }

    /**
     * Calculate scene change score between two frames.
     */
    public function calculateSceneChangeScore(string $frame1Path, string $frame2Path): float
    {
        // This is a simplified implementation
        // In production, you would use image comparison libraries or ML models
        // For now, return a placeholder value

        if (!file_exists($frame1Path) || !file_exists($frame2Path)) {
            return 0.0;
        }

        // Could use ImageMagick, GD, or ML-based comparison here
        // For now, returning random-ish value based on file difference
        $size1 = filesize($frame1Path);
        $size2 = filesize($frame2Path);

        $diff = abs($size1 - $size2);
        $avg = ($size1 + $size2) / 2;

        return min(1.0, $diff / max($avg, 1));
    }

    /**
     * Analyze frame for objects, faces, and OCR.
     * This is a placeholder for AI integration.
     */
    public function analyzeFrame(VideoKeyframe $keyframe): VideoKeyframe
    {
        // This would integrate with AI services like:
        // - OpenCV for object detection
        // - Face detection APIs
        // - OCR services like Tesseract or cloud APIs

        // For now, just calculate basic visual features
        $framePath = Storage::disk('public')->path($keyframe->stored_path);

        if (file_exists($framePath) && function_exists('imagecreatefromjpeg')) {
            $image = @imagecreatefromjpeg($framePath);

            if ($image) {
                // Calculate average brightness
                $brightness = $this->calculateImageBrightness($image);

                $keyframe->update([
                    'brightness' => $brightness,
                ]);

                imagedestroy($image);
            }
        }

        return $keyframe->fresh();
    }

    /**
     * Calculate average brightness of an image.
     */
    protected function calculateImageBrightness($image): float
    {
        $width = imagesx($image);
        $height = imagesy($image);

        $totalBrightness = 0;
        $sampleSize = 0;

        // Sample every 10th pixel for performance
        for ($x = 0; $x < $width; $x += 10) {
            for ($y = 0; $y < $height; $y += 10) {
                $rgb = imagecolorat($image, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;

                // Calculate perceived brightness
                $brightness = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;
                $totalBrightness += $brightness;
                $sampleSize++;
            }
        }

        return $sampleSize > 0 ? round($totalBrightness / $sampleSize, 4) : 0;
    }

    /**
     * Delete video and all associated files.
     */
    public function deleteVideo(VideoAnalysis $analysis): bool
    {
        try {
            // Delete keyframe files
            foreach ($analysis->keyframes as $keyframe) {
                if ($keyframe->stored_path) {
                    Storage::disk('public')->delete($keyframe->stored_path);
                }
                if ($keyframe->thumbnail_path) {
                    Storage::disk('public')->delete($keyframe->thumbnail_path);
                }
            }

            // Delete thumbnail
            if ($analysis->thumbnail_path) {
                Storage::disk('public')->delete($analysis->thumbnail_path);
            }

            // Delete video file
            if ($analysis->stored_path) {
                Storage::disk('public')->delete($analysis->stored_path);
            }

            // Delete frames directory
            $framesDir = dirname($analysis->stored_path) . '/frames';
            Storage::disk('public')->deleteDirectory($framesDir);

            // Delete video directory
            Storage::disk('public')->deleteDirectory(dirname($analysis->stored_path));

            // Delete database records
            $analysis->keyframes()->delete();
            $analysis->forceDelete();

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to delete video', [
                'video_id' => $analysis->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Get analysis statistics.
     */
    public function getStatistics(): array
    {
        return [
            'total' => VideoAnalysis::count(),
            'pending' => VideoAnalysis::pending()->count(),
            'processing' => VideoAnalysis::processing()->count(),
            'completed' => VideoAnalysis::completed()->count(),
            'failed' => VideoAnalysis::failed()->count(),
            'with_gps' => VideoAnalysis::withGps()->count(),
            'total_keyframes' => VideoKeyframe::count(),
            'total_scene_changes' => VideoKeyframe::sceneChanges()->count(),
            'total_storage_bytes' => VideoAnalysis::sum('file_size'),
        ];
    }
}
