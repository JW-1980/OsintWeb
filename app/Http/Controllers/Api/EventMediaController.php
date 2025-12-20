<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\EventMedia;
use Illuminate\Support\Facades\Storage;

/**
 * Controller for managing event media (images, videos, documents)
 */
class EventMediaController extends Controller
{
    /**
     * Upload media file for an event
     *
     * @param Request $request
     * @param string $uuid
     * @return JsonResponse
     */
    public function store(Request $request, string $uuid): JsonResponse
    {
        $event = Event::where('uuid', $uuid)->firstOrFail();

        $this->authorize('update', $event);

        $validated = $request->validate([
            'file' => ['required', 'file', 'max:102400'], // 100MB max
            'type' => ['required', 'in:image,video,document'],
            'caption' => ['nullable', 'string', 'max:1000'],
            'source_url' => ['nullable', 'url', 'max:500'],
        ]);

        // Validate file types based on media type
        $mimeTypes = [
            'image' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
            'video' => ['video/mp4', 'video/webm', 'video/quicktime'],
            'document' => ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
        ];

        $file = $request->file('file');
        $mimeType = $file->getMimeType();

        if (!in_array($mimeType, $mimeTypes[$validated['type']])) {
            return $this->error('Invalid file type for selected media type', 422);
        }

        // Store file
        $path = $file->store('event-media/' . $event->id, 'public');

        // Extract metadata
        $metadata = [
            'size' => $file->getSize(),
            'mime_type' => $mimeType,
        ];

        // For images, extract EXIF data
        if ($validated['type'] === 'image') {
            try {
                $exif = @exif_read_data($file->getRealPath());
                if ($exif) {
                    $metadata['exif'] = [
                        'width' => $exif['COMPUTED']['Width'] ?? null,
                        'height' => $exif['COMPUTED']['Height'] ?? null,
                        'camera' => $exif['Model'] ?? null,
                        'date_taken' => $exif['DateTimeOriginal'] ?? null,
                        'gps' => isset($exif['GPSLatitude']) && isset($exif['GPSLongitude']) ? [
                            'lat' => $this->getGps($exif['GPSLatitude'], $exif['GPSLatitudeRef']),
                            'lng' => $this->getGps($exif['GPSLongitude'], $exif['GPSLongitudeRef']),
                        ] : null,
                    ];
                }
            } catch (\Exception $e) {
                // EXIF extraction failed, continue without it
            }
        }

        $media = EventMedia::create([
            'event_id' => $event->id,
            'type' => $validated['type'],
            'file_path' => $path,
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => $mimeType,
            'file_size' => $file->getSize(),
            'metadata' => $metadata,
            'caption' => $validated['caption'] ?? null,
            'source_url' => $validated['source_url'] ?? null,
        ]);

        return $this->success($media, 201);
    }

    /**
     * Delete media from an event
     *
     * @param string $uuid
     * @param int $mediaId
     * @return JsonResponse
     */
    public function destroy(string $uuid, int $mediaId): JsonResponse
    {
        $event = Event::where('uuid', $uuid)->firstOrFail();

        $this->authorize('update', $event);

        $media = EventMedia::where('event_id', $event->id)
            ->where('id', $mediaId)
            ->firstOrFail();

        // Delete file from storage
        if (Storage::disk('public')->exists($media->file_path)) {
            Storage::disk('public')->delete($media->file_path);
        }

        $media->delete();

        return $this->success(['message' => 'Media deleted successfully']);
    }

    /**
     * Convert GPS coordinates from EXIF format to decimal
     *
     * @param array $coordinate
     * @param string $hemisphere
     * @return float
     */
    private function getGps(array $coordinate, string $hemisphere): float
    {
        $degrees = count($coordinate) > 0 ? $this->gps2Num($coordinate[0]) : 0;
        $minutes = count($coordinate) > 1 ? $this->gps2Num($coordinate[1]) : 0;
        $seconds = count($coordinate) > 2 ? $this->gps2Num($coordinate[2]) : 0;

        $flip = ($hemisphere === 'W' || $hemisphere === 'S') ? -1 : 1;

        return $flip * ($degrees + $minutes / 60 + $seconds / 3600);
    }

    /**
     * Convert GPS rational number to float
     *
     * @param string $coordPart
     * @return float
     */
    private function gps2Num(string $coordPart): float
    {
        $parts = explode('/', $coordPart);

        if (count($parts) <= 0) {
            return 0;
        }

        if (count($parts) === 1) {
            return (float) $parts[0];
        }

        return (float) $parts[0] / (float) $parts[1];
    }
}
