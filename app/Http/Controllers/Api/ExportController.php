<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Event;
use App\Models\ControlZone;
use App\Models\MilitaryEquipment;
use Illuminate\Support\Facades\DB;

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
}
