<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ZoneSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Seeds control zones for demonstration purposes.
     */
    public function run(): void
    {
        $conflicts = DB::table('conflicts')->pluck('id', 'slug');

        $zones = [
            // Ukraine War Zones
            [
                'name' => 'Russian-Occupied Crimea',
                'slug' => 'crimea-occupied',
                'description' => 'Crimean Peninsula annexed by Russia in 2014. Contains major Russian military installations including Sevastopol naval base.',
                'conflict_slug' => 'russia-ukraine-war',
                'controller' => 'Russia',
                'controller_type' => 'state',
                'status' => 'occupied',
                'color' => '#FF4444',
                'coordinates' => json_encode([
                    [45.0, 33.5], [45.0, 36.5], [46.2, 36.5], [46.2, 33.5], [45.0, 33.5]
                ]),
                'area_sq_km' => 27000,
                'population_estimate' => 2400000,
                'date_established' => '2014-03-18',
            ],
            [
                'name' => 'Donetsk Oblast Frontline',
                'slug' => 'donetsk-frontline',
                'description' => 'Active combat zone in Donetsk Oblast. Includes cities like Bakhmut, Avdiivka, and areas around Donetsk city.',
                'conflict_slug' => 'russia-ukraine-war',
                'controller' => 'Contested',
                'controller_type' => 'contested',
                'status' => 'active_combat',
                'color' => '#FF8800',
                'coordinates' => json_encode([
                    [48.0, 37.5], [48.0, 38.5], [48.5, 38.5], [48.5, 37.5], [48.0, 37.5]
                ]),
                'area_sq_km' => 5000,
                'population_estimate' => 500000,
                'date_established' => '2022-02-24',
            ],
            [
                'name' => 'Zaporizhzhia Nuclear Plant Zone',
                'slug' => 'znpp-zone',
                'description' => 'Area around Zaporizhzhia Nuclear Power Plant, Europe\'s largest nuclear facility. Russian-occupied since March 2022.',
                'conflict_slug' => 'russia-ukraine-war',
                'controller' => 'Russia',
                'controller_type' => 'state',
                'status' => 'occupied',
                'color' => '#FFFF00',
                'coordinates' => json_encode([
                    [47.5, 34.5], [47.5, 35.0], [47.6, 35.0], [47.6, 34.5], [47.5, 34.5]
                ]),
                'area_sq_km' => 500,
                'population_estimate' => 50000,
                'date_established' => '2022-03-04',
            ],
            [
                'name' => 'Kherson Oblast (Ukrainian Controlled)',
                'slug' => 'kherson-ukrainian',
                'description' => 'Western Kherson Oblast under Ukrainian control after liberation in November 2022. Active shelling across Dnipro River.',
                'conflict_slug' => 'russia-ukraine-war',
                'controller' => 'Ukraine',
                'controller_type' => 'state',
                'status' => 'liberated',
                'color' => '#0057B7',
                'coordinates' => json_encode([
                    [46.5, 32.0], [46.5, 33.5], [47.0, 33.5], [47.0, 32.0], [46.5, 32.0]
                ]),
                'area_sq_km' => 8000,
                'population_estimate' => 300000,
                'date_established' => '2022-11-11',
            ],

            // Gaza Zones
            [
                'name' => 'Northern Gaza',
                'slug' => 'northern-gaza',
                'description' => 'Northern part of Gaza Strip including Gaza City. Heavy urban combat and destruction since October 2023.',
                'conflict_slug' => 'israel-hamas-war-2023',
                'controller' => 'Contested',
                'controller_type' => 'contested',
                'status' => 'active_combat',
                'color' => '#FF4444',
                'coordinates' => json_encode([
                    [31.45, 34.35], [31.45, 34.55], [31.60, 34.55], [31.60, 34.35], [31.45, 34.35]
                ]),
                'area_sq_km' => 150,
                'population_estimate' => 400000,
                'date_established' => '2023-10-27',
            ],
            [
                'name' => 'Rafah Border Zone',
                'slug' => 'rafah-zone',
                'description' => 'Southern Gaza Strip along Egyptian border. Site of humanitarian crossing and displaced population concentration.',
                'conflict_slug' => 'israel-hamas-war-2023',
                'controller' => 'Contested',
                'controller_type' => 'contested',
                'status' => 'active_combat',
                'color' => '#FF8800',
                'coordinates' => json_encode([
                    [31.20, 34.20], [31.20, 34.35], [31.30, 34.35], [31.30, 34.20], [31.20, 34.20]
                ]),
                'area_sq_km' => 60,
                'population_estimate' => 1500000,
                'date_established' => '2024-01-01',
            ],

            // Syria Zones
            [
                'name' => 'Idlib De-escalation Zone',
                'slug' => 'idlib-zone',
                'description' => 'Last major opposition-held area in Syria. Controlled by HTS (Hayat Tahrir al-Sham). Turkish observation posts along perimeter.',
                'conflict_slug' => 'syrian-civil-war',
                'controller' => 'HTS',
                'controller_type' => 'non_state',
                'status' => 'opposition_held',
                'color' => '#228B22',
                'coordinates' => json_encode([
                    [35.5, 36.0], [35.5, 37.0], [36.5, 37.0], [36.5, 36.0], [35.5, 36.0]
                ]),
                'area_sq_km' => 6000,
                'population_estimate' => 4000000,
                'date_established' => '2018-09-17',
            ],
            [
                'name' => 'SDF-Controlled Northeast Syria',
                'slug' => 'rojava-sdf',
                'description' => 'Autonomous Administration of North and East Syria (Rojava). Controlled by Syrian Democratic Forces with US military presence.',
                'conflict_slug' => 'syrian-civil-war',
                'controller' => 'SDF',
                'controller_type' => 'non_state',
                'status' => 'autonomous',
                'color' => '#FFD700',
                'coordinates' => json_encode([
                    [35.0, 38.0], [35.0, 42.0], [37.5, 42.0], [37.5, 38.0], [35.0, 38.0]
                ]),
                'area_sq_km' => 50000,
                'population_estimate' => 5000000,
                'date_established' => '2015-10-10',
            ],
            [
                'name' => 'Turkish-Controlled Northern Syria',
                'slug' => 'tfsa-zone',
                'description' => 'Area controlled by Turkish-backed Syrian National Army following Operations Euphrates Shield and Olive Branch.',
                'conflict_slug' => 'syrian-civil-war',
                'controller' => 'Turkey/SNA',
                'controller_type' => 'state',
                'status' => 'occupied',
                'color' => '#FF6347',
                'coordinates' => json_encode([
                    [36.0, 36.5], [36.0, 38.5], [37.0, 38.5], [37.0, 36.5], [36.0, 36.5]
                ]),
                'area_sq_km' => 8000,
                'population_estimate' => 1500000,
                'date_established' => '2016-08-24',
            ],

            // Yemen Zones
            [
                'name' => 'Houthi-Controlled Yemen',
                'slug' => 'houthi-territory',
                'description' => 'Northern Yemen including capital Sanaa under Houthi (Ansar Allah) control. Contains majority of Yemen\'s population.',
                'conflict_slug' => 'yemeni-civil-war',
                'controller' => 'Houthis',
                'controller_type' => 'non_state',
                'status' => 'rebel_held',
                'color' => '#006400',
                'coordinates' => json_encode([
                    [14.0, 43.0], [14.0, 46.0], [17.0, 46.0], [17.0, 43.0], [14.0, 43.0]
                ]),
                'area_sq_km' => 150000,
                'population_estimate' => 20000000,
                'date_established' => '2014-09-21',
            ],

            // Sudan Zones
            [
                'name' => 'Khartoum Combat Zone',
                'slug' => 'khartoum-combat',
                'description' => 'Greater Khartoum area with ongoing combat between SAF and RSF. Includes Khartoum, Omdurman, and Bahri.',
                'conflict_slug' => 'sudan-civil-war-2023',
                'controller' => 'Contested',
                'controller_type' => 'contested',
                'status' => 'active_combat',
                'color' => '#FF4444',
                'coordinates' => json_encode([
                    [15.3, 32.3], [15.3, 32.8], [15.8, 32.8], [15.8, 32.3], [15.3, 32.3]
                ]),
                'area_sq_km' => 2000,
                'population_estimate' => 6000000,
                'date_established' => '2023-04-15',
            ],
            [
                'name' => 'Darfur Conflict Zone',
                'slug' => 'darfur-zone',
                'description' => 'Western Sudan region with RSF control and ethnic violence. Ongoing humanitarian crisis.',
                'conflict_slug' => 'sudan-civil-war-2023',
                'controller' => 'RSF',
                'controller_type' => 'non_state',
                'status' => 'rebel_held',
                'color' => '#800000',
                'coordinates' => json_encode([
                    [10.0, 22.0], [10.0, 27.0], [16.0, 27.0], [16.0, 22.0], [10.0, 22.0]
                ]),
                'area_sq_km' => 500000,
                'population_estimate' => 9000000,
                'date_established' => '2023-04-15',
            ],

            // Myanmar Zones
            [
                'name' => 'Rakhine State (AA Control)',
                'slug' => 'rakhine-aa',
                'description' => 'Areas of Rakhine State under Arakan Army control following Operation 1027 gains.',
                'conflict_slug' => 'myanmar-civil-war',
                'controller' => 'Arakan Army',
                'controller_type' => 'non_state',
                'status' => 'rebel_held',
                'color' => '#4169E1',
                'coordinates' => json_encode([
                    [19.0, 93.0], [19.0, 94.5], [21.5, 94.5], [21.5, 93.0], [19.0, 93.0]
                ]),
                'area_sq_km' => 36000,
                'population_estimate' => 3000000,
                'date_established' => '2023-11-01',
            ],
        ];

        foreach ($zones as $zone) {
            $conflictId = isset($zone['conflict_slug']) ? ($conflicts[$zone['conflict_slug']] ?? null) : null;

            $zoneData = [
                'uuid' => Str::uuid(),
                'name' => $zone['name'],
                'slug' => $zone['slug'],
                'description' => $zone['description'],
                'conflict_id' => $conflictId,
                'controller' => $zone['controller'],
                'controller_type' => $zone['controller_type'],
                'status' => $zone['status'],
                'color' => $zone['color'],
                'coordinates' => $zone['coordinates'],
                'area_sq_km' => $zone['area_sq_km'] ?? null,
                'population_estimate' => $zone['population_estimate'] ?? null,
                'date_established' => $zone['date_established'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            DB::table('zones')->updateOrInsert(
                ['slug' => $zone['slug']],
                $zoneData
            );
        }

        $this->command->info('Control zones seeded: ' . count($zones) . ' zones');
    }
}
