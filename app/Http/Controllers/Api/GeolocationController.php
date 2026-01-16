<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GeolocationController extends Controller
{
    /**
     * Create a new geolocation project.
     */
    public function createProject(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'media_url' => 'nullable|url',
            'media_type' => 'nullable|in:image,video',
        ]);

        $projectId = DB::table('geolocation_projects')->insertGetId([
            'uuid' => Str::uuid(),
            'user_id' => auth()->id(),
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'media_url' => $validated['media_url'] ?? null,
            'media_type' => $validated['media_type'] ?? null,
            'status' => 'in_progress',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $project = DB::table('geolocation_projects')->where('id', $projectId)->first();

        return response()->json([
            'message' => 'Geolocation project created',
            'data' => $project,
        ], 201);
    }

    /**
     * List user's geolocation projects.
     */
    public function projects(Request $request): JsonResponse
    {
        $projects = DB::table('geolocation_projects')
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 25));

        return response()->json([
            'data' => $projects->items(),
            'meta' => [
                'current_page' => $projects->currentPage(),
                'total' => $projects->total(),
            ],
        ]);
    }

    /**
     * Get a specific project.
     */
    public function showProject(string $uuid): JsonResponse
    {
        $project = DB::table('geolocation_projects')
            ->where('uuid', $uuid)
            ->where('user_id', auth()->id())
            ->first();

        if (!$project) {
            return response()->json(['message' => 'Project not found'], 404);
        }

        $markers = DB::table('geolocation_markers')
            ->where('project_id', $project->id)
            ->get();

        return response()->json([
            'data' => $project,
            'markers' => $markers,
        ]);
    }

    /**
     * Add a reference marker to a project.
     */
    public function addMarker(Request $request, string $uuid): JsonResponse
    {
        $project = DB::table('geolocation_projects')
            ->where('uuid', $uuid)
            ->where('user_id', auth()->id())
            ->first();

        if (!$project) {
            return response()->json(['message' => 'Project not found'], 404);
        }

        $validated = $request->validate([
            'type' => 'required|in:landmark,building,road,vegetation,shadow,other',
            'description' => 'required|string',
            'image_x' => 'nullable|numeric',
            'image_y' => 'nullable|numeric',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'confidence' => 'nullable|in:high,medium,low',
        ]);

        $markerId = DB::table('geolocation_markers')->insertGetId([
            'uuid' => Str::uuid(),
            'project_id' => $project->id,
            'type' => $validated['type'],
            'description' => $validated['description'],
            'image_x' => $validated['image_x'] ?? null,
            'image_y' => $validated['image_y'] ?? null,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'confidence' => $validated['confidence'] ?? 'medium',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $marker = DB::table('geolocation_markers')->where('id', $markerId)->first();

        return response()->json([
            'message' => 'Marker added',
            'data' => $marker,
        ], 201);
    }

    /**
     * Calculate sun position for shadow analysis.
     */
    public function sunPosition(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'datetime' => 'required|date',
        ]);

        $timestamp = strtotime($validated['datetime']);
        $latitude = $validated['latitude'];
        $longitude = $validated['longitude'];

        // Calculate sun position
        $dayOfYear = date('z', $timestamp) + 1;
        $hour = (float) date('G', $timestamp) + (float) date('i', $timestamp) / 60;

        // Declination angle
        $declination = 23.45 * sin(deg2rad(360 / 365 * ($dayOfYear - 81)));

        // Hour angle
        $solarNoon = 12 - ($longitude / 15);
        $hourAngle = 15 * ($hour - $solarNoon);

        // Solar elevation angle
        $elevation = rad2deg(asin(
            sin(deg2rad($latitude)) * sin(deg2rad($declination)) +
            cos(deg2rad($latitude)) * cos(deg2rad($declination)) * cos(deg2rad($hourAngle))
        ));

        // Solar azimuth angle
        $azimuth = rad2deg(atan2(
            sin(deg2rad($hourAngle)),
            cos(deg2rad($hourAngle)) * sin(deg2rad($latitude)) - tan(deg2rad($declination)) * cos(deg2rad($latitude))
        ));

        // Normalize azimuth to 0-360
        if ($azimuth < 0) {
            $azimuth += 360;
        }

        return response()->json([
            'datetime' => $validated['datetime'],
            'location' => [
                'latitude' => $latitude,
                'longitude' => $longitude,
            ],
            'sun_position' => [
                'elevation' => round($elevation, 2),
                'azimuth' => round($azimuth, 2),
                'is_daytime' => $elevation > 0,
            ],
            'shadow_direction' => round(($azimuth + 180) % 360, 2),
            'shadow_length_ratio' => $elevation > 0 ? round(1 / tan(deg2rad($elevation)), 2) : null,
        ]);
    }

    /**
     * Reverse geocode coordinates.
     */
    public function reverseGeocode(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        // Use OpenStreetMap Nominatim for reverse geocoding
        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'User-Agent' => 'OsintWeb/1.0',
            ])->get('https://nominatim.openstreetmap.org/reverse', [
                'lat' => $validated['latitude'],
                'lon' => $validated['longitude'],
                'format' => 'json',
                'addressdetails' => 1,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return response()->json([
                    'coordinates' => [
                        'latitude' => $validated['latitude'],
                        'longitude' => $validated['longitude'],
                    ],
                    'address' => $data['address'] ?? null,
                    'display_name' => $data['display_name'] ?? null,
                    'osm_type' => $data['osm_type'] ?? null,
                    'osm_id' => $data['osm_id'] ?? null,
                ]);
            }
        } catch (\Exception $e) {
            // Fallback to basic response
        }

        return response()->json([
            'coordinates' => [
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
            ],
            'message' => 'Geocoding service unavailable',
        ]);
    }

    /**
     * Get weather data for chronolocation.
     */
    public function weatherData(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'date' => 'required|date',
        ]);

        // In a real implementation, this would call a weather API
        // For now, return a structured response
        return response()->json([
            'location' => [
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
            ],
            'date' => $validated['date'],
            'weather' => [
                'note' => 'Weather data requires API integration (e.g., OpenWeatherMap, Visual Crossing)',
                'data_available' => false,
            ],
            'useful_for' => [
                'Shadow analysis verification',
                'Cloud cover assessment',
                'Precipitation evidence',
                'Visibility estimation',
            ],
        ]);
    }

    /**
     * Submit geolocation result.
     */
    public function submitResult(Request $request, string $uuid): JsonResponse
    {
        $project = DB::table('geolocation_projects')
            ->where('uuid', $uuid)
            ->where('user_id', auth()->id())
            ->first();

        if (!$project) {
            return response()->json(['message' => 'Project not found'], 404);
        }

        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'confidence' => 'required|in:high,medium,low',
            'methodology' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        DB::table('geolocation_projects')
            ->where('uuid', $uuid)
            ->update([
                'result_latitude' => $validated['latitude'],
                'result_longitude' => $validated['longitude'],
                'result_confidence' => $validated['confidence'],
                'methodology' => $validated['methodology'],
                'notes' => $validated['notes'] ?? null,
                'status' => 'completed',
                'completed_at' => now(),
                'updated_at' => now(),
            ]);

        return response()->json([
            'message' => 'Geolocation result saved',
            'data' => DB::table('geolocation_projects')->where('uuid', $uuid)->first(),
        ]);
    }

    /**
     * Get satellite imagery layers.
     */
    public function satelliteLayers(): JsonResponse
    {
        return response()->json([
            'layers' => [
                [
                    'id' => 'osm',
                    'name' => 'OpenStreetMap',
                    'url' => 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
                    'type' => 'base',
                    'attribution' => '© OpenStreetMap contributors',
                ],
                [
                    'id' => 'satellite',
                    'name' => 'Satellite (ESRI)',
                    'url' => 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
                    'type' => 'satellite',
                    'attribution' => '© Esri',
                ],
                [
                    'id' => 'topo',
                    'name' => 'Topographic',
                    'url' => 'https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png',
                    'type' => 'topo',
                    'attribution' => '© OpenTopoMap',
                ],
            ],
            'note' => 'High-resolution commercial imagery requires separate API integration (Maxar, Planet, etc.)',
        ]);
    }
}
