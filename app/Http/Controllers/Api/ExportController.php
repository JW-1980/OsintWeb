<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Domains\Intelligence\Models\Event;
use App\Models\ControlZone;
use App\Models\MilitaryEquipment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Controller for exporting data in various formats
 */
class ExportController extends Controller
{
    /**
     * Export data as KML format
     *
     * @param Request $request
     * @return Response
     */
    public function kml(Request $request): Response
    {
        $validated = $request->validate([
            'type' => ['required', 'in:events,zones,all'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
        ]);

        $kml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $kml .= '<kml xmlns="http://www.opengis.net/kml/2.2">' . "\n";
        $kml .= '<Document>' . "\n";
        $kml .= '<name>OsintWeb Export</name>' . "\n";

        // Export events
        if (in_array($validated['type'], ['events', 'all'])) {
            $query = Event::query();

            if (isset($validated['start_date'])) {
                $query->where('occurred_at', '>=', $validated['start_date']);
            }

            if (isset($validated['end_date'])) {
                $query->where('occurred_at', '<=', $validated['end_date']);
            }

            $events = $query->get();

            foreach ($events as $event) {
                $kml .= '<Placemark>' . "\n";
                $kml .= '<name>' . htmlspecialchars($event->title) . '</name>' . "\n";
                $kml .= '<description>' . htmlspecialchars($event->description) . '</description>' . "\n";
                $kml .= '<TimeStamp><when>' . $event->occurred_at->toIso8601String() . '</when></TimeStamp>' . "\n";
                $kml .= '<Point>' . "\n";
                $kml .= '<coordinates>' . $event->longitude . ',' . $event->latitude . ',0</coordinates>' . "\n";
                $kml .= '</Point>' . "\n";
                $kml .= '</Placemark>' . "\n";
            }
        }

        // Export control zones
        if (in_array($validated['type'], ['zones', 'all'])) {
            $zones = ControlZone::whereNull('valid_to')
                ->orWhere('valid_to', '>=', now())
                ->get();

            foreach ($zones as $zone) {
                $geometry = json_decode($zone->geometry_geojson);

                if ($geometry && $geometry->type === 'Polygon') {
                    $kml .= '<Placemark>' . "\n";
                    $kml .= '<name>' . htmlspecialchars($zone->name) . '</name>' . "\n";
                    $kml .= '<Polygon>' . "\n";
                    $kml .= '<outerBoundaryIs><LinearRing><coordinates>' . "\n";

                    foreach ($geometry->coordinates[0] as $coord) {
                        $kml .= $coord[0] . ',' . $coord[1] . ',0 ';
                    }

                    $kml .= '</coordinates></LinearRing></outerBoundaryIs>' . "\n";
                    $kml .= '</Polygon>' . "\n";
                    $kml .= '</Placemark>' . "\n";
                }
            }
        }

        $kml .= '</Document>' . "\n";
        $kml .= '</kml>';

        return response($kml, 200)
            ->header('Content-Type', 'application/vnd.google-earth.kml+xml')
            ->header('Content-Disposition', 'attachment; filename="osintweb-export.kml"');
    }

    /**
     * Export data as GeoJSON format
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function geojson(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'in:events,zones,all'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
        ]);

        $features = [];

        // Export events
        if (in_array($validated['type'], ['events', 'all'])) {
            $query = Event::query();

            if (isset($validated['start_date'])) {
                $query->where('occurred_at', '>=', $validated['start_date']);
            }

            if (isset($validated['end_date'])) {
                $query->where('occurred_at', '<=', $validated['end_date']);
            }

            $events = $query->with('actor')->get();

            foreach ($events as $event) {
                $features[] = [
                    'type' => 'Feature',
                    'geometry' => [
                        'type' => 'Point',
                        'coordinates' => [$event->longitude, $event->latitude],
                    ],
                    'properties' => [
                        'id' => $event->uuid,
                        'title' => $event->title,
                        'description' => $event->description,
                        'type' => $event->type,
                        'occurred_at' => $event->occurred_at->toIso8601String(),
                        'status' => $event->status,
                        'confidence' => $event->confidence,
                        'actor' => $event->actor ? $event->actor->name : null,
                    ],
                ];
            }
        }

        // Export control zones
        if (in_array($validated['type'], ['zones', 'all'])) {
            $zones = ControlZone::with('controller')
                ->whereNull('valid_to')
                ->orWhere('valid_to', '>=', now())
                ->get();

            foreach ($zones as $zone) {
                $geometry = json_decode($zone->geometry_geojson);

                if ($geometry) {
                    $features[] = [
                        'type' => 'Feature',
                        'geometry' => $geometry,
                        'properties' => [
                            'id' => $zone->uuid,
                            'name' => $zone->name,
                            'controller' => $zone->controller ? $zone->controller->name : null,
                            'control_type' => $zone->control_type,
                            'confidence' => $zone->confidence,
                            'valid_from' => $zone->valid_from,
                            'valid_to' => $zone->valid_to,
                        ],
                    ];
                }
            }
        }

        $geojson = [
            'type' => 'FeatureCollection',
            'features' => $features,
        ];

        return response()->json($geojson)
            ->header('Content-Disposition', 'attachment; filename="osintweb-export.geojson"');
    }

    /**
     * Export events as CSV
     *
     * @param Request $request
     * @return Response
     */
    public function csv(Request $request): Response
    {
        $validated = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
        ]);

        $query = Event::query();

        if (isset($validated['start_date'])) {
            $query->where('occurred_at', '>=', $validated['start_date']);
        }

        if (isset($validated['end_date'])) {
            $query->where('occurred_at', '<=', $validated['end_date']);
        }

        $events = $query->with('actor')->get();

        $csv = "ID,Title,Description,Type,Occurred At,Latitude,Longitude,Location Name,Actor,Status,Confidence\n";

        foreach ($events as $event) {
            $csv .= sprintf(
                '"%s","%s","%s","%s","%s",%f,%f,"%s","%s","%s","%s"' . "\n",
                $event->uuid,
                str_replace('"', '""', $event->title),
                str_replace('"', '""', $event->description),
                $event->type,
                $event->occurred_at->toIso8601String(),
                $event->latitude,
                $event->longitude,
                str_replace('"', '""', $event->location_name ?? ''),
                str_replace('"', '""', $event->actor ? $event->actor->name : ''),
                $event->status,
                $event->confidence
            );
        }

        return response($csv, 200)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="osintweb-events.csv"');
    }

    /**
     * Export equipment inventory as CSV
     *
     * @return Response
     */
    public function equipmentCsv(): Response
    {
        $equipment = MilitaryEquipment::orderBy('category')->orderBy('name')->get();

        $csv = "ID,Name,Designation,Category,Sub-Category,Country of Origin,Year Introduced,Status\n";

        foreach ($equipment as $item) {
            $csv .= sprintf(
                '"%s","%s","%s","%s","%s","%s","%s","%s"' . "\n",
                $item->uuid,
                str_replace('"', '""', $item->name),
                str_replace('"', '""', $item->designation ?? ''),
                str_replace('"', '""', $item->category),
                str_replace('"', '""', $item->sub_category ?? ''),
                str_replace('"', '""', $item->country_of_origin ?? ''),
                $item->year_introduced ?? '',
                $item->is_active ? 'Active' : 'Retired'
            );
        }

        return response($csv, 200)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="osintweb-equipment.csv"');
    }

    /**
     * Get available map export options
     *
     * @return JsonResponse
     */
    public function mapExportOptions(): JsonResponse
    {
        return response()->json([
            'formats' => [
                [
                    'id' => 'png',
                    'name' => 'PNG Image',
                    'extension' => 'png',
                    'mime_type' => 'image/png',
                    'description' => 'High-quality raster image with transparency support',
                    'client_side' => true,
                ],
                [
                    'id' => 'jpg',
                    'name' => 'JPEG Image',
                    'extension' => 'jpg',
                    'mime_type' => 'image/jpeg',
                    'description' => 'Compressed image format, smaller file size',
                    'client_side' => true,
                    'quality_options' => [0.7, 0.8, 0.9, 1.0],
                ],
                [
                    'id' => 'pdf',
                    'name' => 'PDF Document',
                    'extension' => 'pdf',
                    'mime_type' => 'application/pdf',
                    'description' => 'Printable document format with embedded metadata',
                    'client_side' => true,
                    'page_sizes' => ['A4', 'A3', 'Letter', 'Legal'],
                    'orientations' => ['portrait', 'landscape'],
                ],
                [
                    'id' => 'svg',
                    'name' => 'SVG Vector',
                    'extension' => 'svg',
                    'mime_type' => 'image/svg+xml',
                    'description' => 'Scalable vector format for high-quality printing',
                    'client_side' => true,
                ],
            ],
            'resolutions' => [
                ['id' => 'screen', 'name' => 'Screen (72 DPI)', 'dpi' => 72],
                ['id' => 'print', 'name' => 'Print (150 DPI)', 'dpi' => 150],
                ['id' => 'high', 'name' => 'High Quality (300 DPI)', 'dpi' => 300],
            ],
            'include_options' => [
                'legend' => true,
                'scale_bar' => true,
                'north_arrow' => true,
                'title' => true,
                'timestamp' => true,
                'attribution' => true,
                'events' => true,
                'control_zones' => true,
            ],
        ]);
    }

    /**
     * Store a map image upload for PDF generation
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function storeMapImage(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'image' => ['required', 'string'], // Base64 encoded image
            'format' => ['required', 'in:png,jpg,pdf'],
            'filename' => ['nullable', 'string', 'max:255'],
            'metadata' => ['nullable', 'array'],
            'metadata.title' => ['nullable', 'string'],
            'metadata.description' => ['nullable', 'string'],
            'metadata.center' => ['nullable', 'array'],
            'metadata.zoom' => ['nullable', 'integer'],
            'metadata.bounds' => ['nullable', 'array'],
        ]);

        // Decode base64 image
        $imageData = $validated['image'];
        if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $matches)) {
            $imageData = substr($imageData, strpos($imageData, ',') + 1);
        }
        $imageData = base64_decode($imageData);

        if ($imageData === false) {
            return response()->json(['error' => 'Invalid image data'], 400);
        }

        // Generate unique filename
        $uuid = Str::uuid();
        $extension = $validated['format'] === 'jpg' ? 'jpg' : 'png';
        $filename = $validated['filename'] ?? "map-export-{$uuid}";
        $filename = preg_replace('/[^a-zA-Z0-9_-]/', '', $filename);
        $storagePath = "exports/{$filename}.{$extension}";

        // Store the image
        Storage::disk('local')->put($storagePath, $imageData);

        // Store metadata
        $metadata = array_merge($validated['metadata'] ?? [], [
            'exported_at' => now()->toIso8601String(),
            'format' => $validated['format'],
            'original_filename' => $validated['filename'] ?? null,
        ]);
        Storage::disk('local')->put("exports/{$filename}.json", json_encode($metadata, JSON_PRETTY_PRINT));

        return response()->json([
            'success' => true,
            'uuid' => $uuid,
            'filename' => "{$filename}.{$extension}",
            'download_url' => route('api.export.download', ['filename' => "{$filename}.{$extension}"]),
            'expires_at' => now()->addHours(24)->toIso8601String(),
        ]);
    }

    /**
     * Download a previously exported map
     *
     * @param string $filename
     * @return Response
     */
    public function downloadMapExport(string $filename): Response
    {
        $safeName = preg_replace('/[^a-zA-Z0-9_.-]/', '', $filename);
        $path = "exports/{$safeName}";

        if (!Storage::disk('local')->exists($path)) {
            abort(404, 'Export file not found');
        }

        $extension = pathinfo($safeName, PATHINFO_EXTENSION);
        $mimeTypes = [
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'pdf' => 'application/pdf',
            'svg' => 'image/svg+xml',
        ];

        $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';

        return response(Storage::disk('local')->get($path))
            ->header('Content-Type', $mimeType)
            ->header('Content-Disposition', "attachment; filename=\"{$safeName}\"");
    }

    /**
     * Generate PDF from map data
     *
     * @param Request $request
     * @return Response|JsonResponse
     */
    public function generateMapPdf(Request $request): Response|JsonResponse
    {
        $validated = $request->validate([
            'image' => ['required', 'string'], // Base64 encoded image
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'page_size' => ['nullable', 'in:A4,A3,Letter,Legal'],
            'orientation' => ['nullable', 'in:portrait,landscape'],
            'include_legend' => ['nullable', 'boolean'],
            'include_timestamp' => ['nullable', 'boolean'],
            'include_scale' => ['nullable', 'boolean'],
            'metadata' => ['nullable', 'array'],
        ]);

        // Decode base64 image
        $imageData = $validated['image'];
        if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $matches)) {
            $imageData = substr($imageData, strpos($imageData, ',') + 1);
        }
        $imageData = base64_decode($imageData);

        if ($imageData === false) {
            return response()->json(['error' => 'Invalid image data'], 400);
        }

        // Page dimensions in points (72 points = 1 inch)
        $pageSizes = [
            'A4' => ['width' => 595, 'height' => 842],
            'A3' => ['width' => 842, 'height' => 1191],
            'Letter' => ['width' => 612, 'height' => 792],
            'Legal' => ['width' => 612, 'height' => 1008],
        ];

        $pageSize = $validated['page_size'] ?? 'A4';
        $orientation = $validated['orientation'] ?? 'landscape';
        $dimensions = $pageSizes[$pageSize];

        if ($orientation === 'landscape') {
            [$dimensions['width'], $dimensions['height']] = [$dimensions['height'], $dimensions['width']];
        }

        // Create a simple PDF structure
        // In production, you would use a library like TCPDF, mPDF, or DomPDF
        // For now, we'll store the image and return metadata for client-side PDF generation
        $uuid = Str::uuid();
        $filename = "map-export-{$uuid}";

        // Store the image for potential server-side processing
        Storage::disk('local')->put("exports/{$filename}.png", $imageData);

        // Store PDF generation parameters
        $pdfParams = [
            'image_file' => "{$filename}.png",
            'title' => $validated['title'] ?? 'Map Export',
            'description' => $validated['description'] ?? '',
            'page_size' => $pageSize,
            'orientation' => $orientation,
            'dimensions' => $dimensions,
            'include_legend' => $validated['include_legend'] ?? true,
            'include_timestamp' => $validated['include_timestamp'] ?? true,
            'include_scale' => $validated['include_scale'] ?? true,
            'generated_at' => now()->toIso8601String(),
            'metadata' => $validated['metadata'] ?? [],
        ];

        Storage::disk('local')->put("exports/{$filename}-pdf-params.json", json_encode($pdfParams, JSON_PRETTY_PRINT));

        // Return parameters for client-side PDF generation
        // The frontend will use jsPDF with the image and these parameters
        return response()->json([
            'success' => true,
            'uuid' => $uuid,
            'pdf_params' => $pdfParams,
            'message' => 'PDF generation parameters prepared. Use client-side jsPDF for optimal results.',
            'image_url' => route('api.export.download', ['filename' => "{$filename}.png"]),
        ]);
    }

    /**
     * Get map export statistics
     *
     * @return JsonResponse
     */
    public function mapExportStats(): JsonResponse
    {
        // Count events and zones for export preview
        $eventCount = Event::count();
        $zoneCount = ControlZone::whereNull('valid_to')
            ->orWhere('valid_to', '>=', now())
            ->count();

        // Get date range of events
        $earliestEvent = Event::orderBy('occurred_at')->first();
        $latestEvent = Event::orderBy('occurred_at', 'desc')->first();

        return response()->json([
            'events' => [
                'count' => $eventCount,
                'earliest' => $earliestEvent?->occurred_at?->toIso8601String(),
                'latest' => $latestEvent?->occurred_at?->toIso8601String(),
            ],
            'zones' => [
                'count' => $zoneCount,
            ],
            'export_formats' => ['png', 'jpg', 'pdf', 'svg', 'kml', 'geojson', 'csv'],
        ]);
    }
}
