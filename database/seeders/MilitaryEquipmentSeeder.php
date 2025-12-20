<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MilitaryEquipmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Populates equipment categories and military equipment with real-world data
     * from active conflict zones (Russia-Ukraine, Israel-Gaza, etc.)
     */
    public function run(): void
    {
        // Seed equipment categories first
        $this->seedCategories();

        // Seed military equipment
        $this->seedEquipment();

        $this->command->info('Military Equipment seeded successfully.');
    }

    private function seedCategories(): void
    {
        $categories = [
            // Main categories
            ['name' => 'Naval Vessels', 'slug' => 'naval', 'icon' => 'ship', 'sort_order' => 1],
            ['name' => 'Land Vehicles', 'slug' => 'land', 'icon' => 'tank', 'sort_order' => 2],
            ['name' => 'Aircraft', 'slug' => 'aircraft', 'icon' => 'plane', 'sort_order' => 3],
            ['name' => 'Helicopters', 'slug' => 'helicopters', 'icon' => 'helicopter', 'sort_order' => 4],
            ['name' => 'Missiles & Rockets', 'slug' => 'missiles', 'icon' => 'rocket', 'sort_order' => 5],
        ];

        foreach ($categories as $category) {
            DB::table('equipment_categories')->updateOrInsert(
                ['slug' => $category['slug']],
                array_merge($category, ['created_at' => now(), 'updated_at' => now()])
            );
        }

        // Subcategories
        $subcategories = [
            // Naval subcategories
            ['name' => 'Aircraft Carriers', 'slug' => 'carriers', 'parent_slug' => 'naval', 'icon' => 'ship', 'sort_order' => 1],
            ['name' => 'Destroyers', 'slug' => 'destroyers', 'parent_slug' => 'naval', 'icon' => 'ship', 'sort_order' => 2],
            ['name' => 'Frigates', 'slug' => 'frigates', 'parent_slug' => 'naval', 'icon' => 'ship', 'sort_order' => 3],
            ['name' => 'Submarines', 'slug' => 'submarines', 'parent_slug' => 'naval', 'icon' => 'submarine', 'sort_order' => 4],
            ['name' => 'Patrol Boats', 'slug' => 'patrol-boats', 'parent_slug' => 'naval', 'icon' => 'ship', 'sort_order' => 5],

            // Land subcategories
            ['name' => 'Main Battle Tanks', 'slug' => 'mbt', 'parent_slug' => 'land', 'icon' => 'tank', 'sort_order' => 1],
            ['name' => 'Infantry Fighting Vehicles', 'slug' => 'ifv', 'parent_slug' => 'land', 'icon' => 'truck-military', 'sort_order' => 2],
            ['name' => 'Armored Personnel Carriers', 'slug' => 'apc', 'parent_slug' => 'land', 'icon' => 'truck', 'sort_order' => 3],
            ['name' => 'Self-Propelled Artillery', 'slug' => 'spa', 'parent_slug' => 'land', 'icon' => 'crosshairs', 'sort_order' => 4],
            ['name' => 'Multiple Launch Rocket Systems', 'slug' => 'mlrs', 'parent_slug' => 'land', 'icon' => 'rocket', 'sort_order' => 5],
            ['name' => 'Air Defense Systems', 'slug' => 'sam', 'parent_slug' => 'land', 'icon' => 'shield-alt', 'sort_order' => 6],

            // Aircraft subcategories
            ['name' => 'Fighter Jets', 'slug' => 'fighters', 'parent_slug' => 'aircraft', 'icon' => 'fighter-jet', 'sort_order' => 1],
            ['name' => 'Bombers', 'slug' => 'bombers', 'parent_slug' => 'aircraft', 'icon' => 'plane', 'sort_order' => 2],
            ['name' => 'Transport Aircraft', 'slug' => 'transport-aircraft', 'parent_slug' => 'aircraft', 'icon' => 'plane', 'sort_order' => 3],
            ['name' => 'AWACS/AEW', 'slug' => 'awacs', 'parent_slug' => 'aircraft', 'icon' => 'satellite-dish', 'sort_order' => 4],
            ['name' => 'Unmanned Aerial Vehicles', 'slug' => 'uav', 'parent_slug' => 'aircraft', 'icon' => 'drone', 'sort_order' => 5],

            // Helicopter subcategories
            ['name' => 'Attack Helicopters', 'slug' => 'attack-heli', 'parent_slug' => 'helicopters', 'icon' => 'helicopter', 'sort_order' => 1],
            ['name' => 'Transport Helicopters', 'slug' => 'transport-heli', 'parent_slug' => 'helicopters', 'icon' => 'helicopter', 'sort_order' => 2],
            ['name' => 'Naval Helicopters', 'slug' => 'naval-heli', 'parent_slug' => 'helicopters', 'icon' => 'helicopter', 'sort_order' => 3],

            // Missile subcategories
            ['name' => 'MANPADS', 'slug' => 'manpads', 'parent_slug' => 'missiles', 'icon' => 'rocket', 'sort_order' => 1],
            ['name' => 'Anti-Tank Guided Missiles', 'slug' => 'atgm', 'parent_slug' => 'missiles', 'icon' => 'bullseye', 'sort_order' => 2],
            ['name' => 'Cruise Missiles', 'slug' => 'cruise', 'parent_slug' => 'missiles', 'icon' => 'rocket', 'sort_order' => 3],
            ['name' => 'Ballistic Missiles', 'slug' => 'ballistic', 'parent_slug' => 'missiles', 'icon' => 'rocket', 'sort_order' => 4],
        ];

        foreach ($subcategories as $sub) {
            $parent = DB::table('equipment_categories')->where('slug', $sub['parent_slug'])->first();
            if ($parent) {
                DB::table('equipment_categories')->updateOrInsert(
                    ['slug' => $sub['slug']],
                    [
                        'name' => $sub['name'],
                        'slug' => $sub['slug'],
                        'parent_id' => $parent->id,
                        'icon' => $sub['icon'],
                        'sort_order' => $sub['sort_order'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }

    private function seedEquipment(): void
    {
        // Get category IDs
        $categories = DB::table('equipment_categories')->pluck('id', 'slug');

        // Get country IDs (assuming countries are seeded)
        $countries = DB::table('countries')->pluck('id', 'iso_code');

        $equipment = [
            // ========================================
            // RUSSIAN EQUIPMENT
            // ========================================
            // Tanks
            [
                'designation' => 'T-90M Proryv',
                'nato_designation' => null,
                'common_name' => 'T-90M',
                'country_code' => 'RU',
                'category_slug' => 'mbt',
                'introduced_year' => 2020,
                'estimated_produced' => 500,
                'description' => 'Modernized main battle tank with improved armor, fire control, and active protection system.',
                'specifications' => json_encode([
                    'crew' => 3,
                    'weight_tonnes' => 48,
                    'main_gun' => '125mm 2A46M-5 smoothbore',
                    'engine' => 'V-92S2F 1130hp diesel',
                    'max_speed_kmh' => 60,
                    'range_km' => 550,
                    'armor' => 'Relikt ERA + composite',
                ]),
            ],
            [
                'designation' => 'T-72B3',
                'nato_designation' => null,
                'common_name' => 'T-72B3',
                'country_code' => 'RU',
                'category_slug' => 'mbt',
                'introduced_year' => 2012,
                'estimated_produced' => 1400,
                'description' => 'Upgraded T-72B with modern fire control and ERA.',
                'specifications' => json_encode([
                    'crew' => 3,
                    'weight_tonnes' => 46,
                    'main_gun' => '125mm 2A46M-5',
                    'engine' => 'V-92S2 1000hp',
                    'max_speed_kmh' => 65,
                ]),
            ],
            [
                'designation' => 'T-80BVM',
                'nato_designation' => null,
                'common_name' => 'T-80BVM',
                'country_code' => 'RU',
                'category_slug' => 'mbt',
                'introduced_year' => 2017,
                'estimated_produced' => 200,
                'description' => 'Modernized T-80B with gas turbine engine and Relikt ERA.',
                'specifications' => json_encode([
                    'crew' => 3,
                    'weight_tonnes' => 46,
                    'main_gun' => '125mm 2A46M-4',
                    'engine' => 'GTD-1250 gas turbine 1250hp',
                    'max_speed_kmh' => 70,
                ]),
            ],

            // Russian IFVs
            [
                'designation' => 'BMP-3',
                'nato_designation' => null,
                'common_name' => 'BMP-3',
                'country_code' => 'RU',
                'category_slug' => 'ifv',
                'introduced_year' => 1987,
                'estimated_produced' => 2000,
                'description' => 'Infantry fighting vehicle with 100mm gun and 30mm autocannon.',
                'specifications' => json_encode([
                    'crew' => 3,
                    'passengers' => 7,
                    'weight_tonnes' => 19,
                    'main_armament' => '100mm 2A70 rifled gun + 30mm 2A72 autocannon',
                    'engine' => 'UTD-29 500hp',
                ]),
            ],
            [
                'designation' => 'BTR-82A',
                'nato_designation' => null,
                'common_name' => 'BTR-82A',
                'country_code' => 'RU',
                'category_slug' => 'apc',
                'introduced_year' => 2013,
                'estimated_produced' => 1500,
                'description' => 'Wheeled armored personnel carrier with 30mm autocannon.',
                'specifications' => json_encode([
                    'crew' => 3,
                    'passengers' => 7,
                    'wheels' => '8x8',
                    'weight_tonnes' => 16,
                    'main_armament' => '30mm 2A72 autocannon',
                ]),
            ],

            // Russian Artillery
            [
                'designation' => '2S19 Msta-S',
                'nato_designation' => null,
                'common_name' => 'Msta-S',
                'country_code' => 'RU',
                'category_slug' => 'spa',
                'introduced_year' => 1989,
                'estimated_produced' => 800,
                'description' => 'Self-propelled 152mm howitzer.',
                'specifications' => json_encode([
                    'crew' => 5,
                    'main_gun' => '152mm 2A64',
                    'range_km' => 29,
                    'rate_of_fire' => '7-8 rounds/min',
                ]),
            ],
            [
                'designation' => 'BM-21 Grad',
                'nato_designation' => null,
                'common_name' => 'Grad',
                'country_code' => 'RU',
                'category_slug' => 'mlrs',
                'introduced_year' => 1963,
                'estimated_produced' => 9000,
                'description' => '122mm multiple rocket launcher system.',
                'specifications' => json_encode([
                    'tubes' => 40,
                    'caliber' => '122mm',
                    'range_km' => 20,
                    'reload_time_min' => 10,
                ]),
            ],
            [
                'designation' => 'BM-27 Uragan',
                'nato_designation' => null,
                'common_name' => 'Uragan',
                'country_code' => 'RU',
                'category_slug' => 'mlrs',
                'introduced_year' => 1975,
                'estimated_produced' => 800,
                'description' => '220mm heavy multiple rocket launcher.',
                'specifications' => json_encode([
                    'tubes' => 16,
                    'caliber' => '220mm',
                    'range_km' => 35,
                ]),
            ],

            // Russian Air Defense
            [
                'designation' => 'S-400 Triumf',
                'nato_designation' => 'SA-21 Growler',
                'common_name' => 'S-400',
                'country_code' => 'RU',
                'category_slug' => 'sam',
                'introduced_year' => 2007,
                'estimated_produced' => 100,
                'description' => 'Long-range surface-to-air missile system.',
                'specifications' => json_encode([
                    'missiles_per_battery' => 32,
                    'range_km' => 400,
                    'altitude_km' => 30,
                    'target_speed_ms' => 4800,
                ]),
            ],
            [
                'designation' => 'Buk-M3',
                'nato_designation' => 'SA-17 Grizzly',
                'common_name' => 'Buk-M3',
                'country_code' => 'RU',
                'category_slug' => 'sam',
                'introduced_year' => 2016,
                'estimated_produced' => 100,
                'description' => 'Medium-range surface-to-air missile system.',
                'specifications' => json_encode([
                    'missiles_per_vehicle' => 6,
                    'range_km' => 70,
                    'altitude_km' => 35,
                ]),
            ],
            [
                'designation' => '9K33 Osa',
                'nato_designation' => 'SA-8 Gecko',
                'common_name' => 'Osa',
                'country_code' => 'RU',
                'category_slug' => 'sam',
                'introduced_year' => 1971,
                'estimated_produced' => 1200,
                'description' => 'Short-range tactical surface-to-air missile system.',
                'specifications' => json_encode([
                    'missiles' => 6,
                    'range_km' => 15,
                    'altitude_km' => 12,
                ]),
            ],

            // Russian Aircraft
            [
                'designation' => 'Su-35S',
                'nato_designation' => 'Flanker-E',
                'common_name' => 'Su-35S',
                'country_code' => 'RU',
                'category_slug' => 'fighters',
                'introduced_year' => 2014,
                'estimated_produced' => 140,
                'description' => 'Super-maneuverable 4++ generation multirole fighter.',
                'specifications' => json_encode([
                    'crew' => 1,
                    'max_speed_mach' => 2.25,
                    'range_km' => 3600,
                    'combat_radius_km' => 1600,
                    'hardpoints' => 12,
                ]),
            ],
            [
                'designation' => 'Su-57',
                'nato_designation' => 'Felon',
                'common_name' => 'Su-57',
                'country_code' => 'RU',
                'category_slug' => 'fighters',
                'introduced_year' => 2020,
                'estimated_produced' => 22,
                'description' => 'Fifth-generation stealth multirole fighter.',
                'specifications' => json_encode([
                    'crew' => 1,
                    'max_speed_mach' => 2,
                    'range_km' => 3500,
                    'stealth' => true,
                    'supercruise' => true,
                ]),
            ],
            [
                'designation' => 'Tu-22M3',
                'nato_designation' => 'Backfire',
                'common_name' => 'Tu-22M3',
                'country_code' => 'RU',
                'category_slug' => 'bombers',
                'introduced_year' => 1983,
                'estimated_produced' => 268,
                'description' => 'Long-range supersonic maritime strike bomber.',
                'specifications' => json_encode([
                    'crew' => 4,
                    'max_speed_mach' => 1.88,
                    'range_km' => 6800,
                    'payload_kg' => 24000,
                ]),
            ],

            // Russian Helicopters
            [
                'designation' => 'Ka-52',
                'nato_designation' => 'Hokum-B',
                'common_name' => 'Alligator',
                'country_code' => 'RU',
                'category_slug' => 'attack-heli',
                'introduced_year' => 2011,
                'estimated_produced' => 160,
                'description' => 'All-weather attack helicopter with coaxial rotor.',
                'specifications' => json_encode([
                    'crew' => 2,
                    'max_speed_kmh' => 300,
                    'range_km' => 460,
                    'main_armament' => '30mm 2A42 cannon',
                    'missiles' => 12,
                ]),
            ],
            [
                'designation' => 'Mi-28N',
                'nato_designation' => 'Havoc',
                'common_name' => 'Night Hunter',
                'country_code' => 'RU',
                'category_slug' => 'attack-heli',
                'introduced_year' => 2009,
                'estimated_produced' => 130,
                'description' => 'All-weather day-night attack helicopter.',
                'specifications' => json_encode([
                    'crew' => 2,
                    'max_speed_kmh' => 305,
                    'range_km' => 450,
                    'main_armament' => '30mm 2A42 cannon',
                ]),
            ],

            // Russian Missiles
            [
                'designation' => '3M22 Zircon',
                'nato_designation' => 'SS-N-33',
                'common_name' => 'Zircon',
                'country_code' => 'RU',
                'category_slug' => 'cruise',
                'introduced_year' => 2023,
                'estimated_produced' => null,
                'description' => 'Hypersonic anti-ship cruise missile.',
                'specifications' => json_encode([
                    'speed_mach' => 9,
                    'range_km' => 1000,
                    'warhead_kg' => 300,
                ]),
            ],
            [
                'designation' => '9M133 Kornet',
                'nato_designation' => 'AT-14 Spriggan',
                'common_name' => 'Kornet',
                'country_code' => 'RU',
                'category_slug' => 'atgm',
                'introduced_year' => 1998,
                'estimated_produced' => 35000,
                'description' => 'Laser-guided anti-tank guided missile.',
                'specifications' => json_encode([
                    'guidance' => 'SACLOS laser beam-riding',
                    'range_m' => 5500,
                    'penetration_mm' => 1200,
                    'warhead' => 'tandem HEAT',
                ]),
            ],

            // ========================================
            // UKRAINIAN EQUIPMENT
            // ========================================
            [
                'designation' => 'T-84 Oplot',
                'nato_designation' => null,
                'common_name' => 'Oplot',
                'country_code' => 'UA',
                'category_slug' => 'mbt',
                'introduced_year' => 1999,
                'estimated_produced' => 100,
                'description' => 'Ukrainian main battle tank based on T-80.',
                'specifications' => json_encode([
                    'crew' => 3,
                    'weight_tonnes' => 51,
                    'main_gun' => '125mm KBA3',
                    'engine' => '6TD-2 1200hp',
                ]),
            ],
            [
                'designation' => 'BTR-4 Bucephalus',
                'nato_designation' => null,
                'common_name' => 'BTR-4',
                'country_code' => 'UA',
                'category_slug' => 'apc',
                'introduced_year' => 2008,
                'estimated_produced' => 200,
                'description' => 'Modern 8x8 wheeled APC with modular turret.',
                'specifications' => json_encode([
                    'crew' => 3,
                    'passengers' => 7,
                    'weight_tonnes' => 17,
                    'main_armament' => '30mm ZTM-1',
                ]),
            ],
            [
                'designation' => 'Bayraktar TB2',
                'nato_designation' => null,
                'common_name' => 'Bayraktar TB2',
                'country_code' => 'TR',
                'category_slug' => 'uav',
                'introduced_year' => 2014,
                'estimated_produced' => 500,
                'description' => 'Turkish medium-altitude long-endurance UAV.',
                'specifications' => json_encode([
                    'wingspan_m' => 12,
                    'endurance_hours' => 27,
                    'ceiling_m' => 8200,
                    'payload_kg' => 150,
                ]),
            ],

            // ========================================
            // US EQUIPMENT (Supplied to Ukraine)
            // ========================================
            [
                'designation' => 'M1A2 Abrams SEP v3',
                'nato_designation' => null,
                'common_name' => 'Abrams',
                'country_code' => 'US',
                'category_slug' => 'mbt',
                'introduced_year' => 2020,
                'estimated_produced' => 1500,
                'description' => 'Main battle tank with advanced composite armor.',
                'specifications' => json_encode([
                    'crew' => 4,
                    'weight_tonnes' => 67,
                    'main_gun' => '120mm M256 smoothbore',
                    'engine' => 'AGT1500 gas turbine 1500hp',
                    'max_speed_kmh' => 68,
                ]),
            ],
            [
                'designation' => 'M2A3 Bradley',
                'nato_designation' => null,
                'common_name' => 'Bradley',
                'country_code' => 'US',
                'category_slug' => 'ifv',
                'introduced_year' => 2000,
                'estimated_produced' => 6000,
                'description' => 'Infantry fighting vehicle with TOW missiles.',
                'specifications' => json_encode([
                    'crew' => 3,
                    'passengers' => 6,
                    'weight_tonnes' => 30,
                    'main_armament' => '25mm M242 Bushmaster',
                    'missiles' => 'TOW-2',
                ]),
            ],
            [
                'designation' => 'M142 HIMARS',
                'nato_designation' => null,
                'common_name' => 'HIMARS',
                'country_code' => 'US',
                'category_slug' => 'mlrs',
                'introduced_year' => 2005,
                'estimated_produced' => 550,
                'description' => 'High Mobility Artillery Rocket System.',
                'specifications' => json_encode([
                    'rockets' => 6,
                    'caliber' => '227mm',
                    'range_km' => 300,
                    'gmlrs_range_km' => 80,
                ]),
            ],
            [
                'designation' => 'FGM-148 Javelin',
                'nato_designation' => null,
                'common_name' => 'Javelin',
                'country_code' => 'US',
                'category_slug' => 'atgm',
                'introduced_year' => 1996,
                'estimated_produced' => 50000,
                'description' => 'Fire-and-forget anti-tank missile with top-attack mode.',
                'specifications' => json_encode([
                    'guidance' => 'Infrared imaging',
                    'range_m' => 4750,
                    'penetration_mm' => 800,
                    'weight_kg' => 22.3,
                ]),
            ],
            [
                'designation' => 'FIM-92 Stinger',
                'nato_designation' => null,
                'common_name' => 'Stinger',
                'country_code' => 'US',
                'category_slug' => 'manpads',
                'introduced_year' => 1981,
                'estimated_produced' => 70000,
                'description' => 'Infrared homing surface-to-air missile.',
                'specifications' => json_encode([
                    'guidance' => 'IR homing',
                    'range_km' => 8,
                    'altitude_m' => 3800,
                    'weight_kg' => 15.2,
                ]),
            ],
            [
                'designation' => 'MIM-104 Patriot',
                'nato_designation' => null,
                'common_name' => 'Patriot',
                'country_code' => 'US',
                'category_slug' => 'sam',
                'introduced_year' => 1984,
                'estimated_produced' => 1100,
                'description' => 'Long-range surface-to-air missile system.',
                'specifications' => json_encode([
                    'range_km' => 160,
                    'altitude_km' => 24,
                    'interceptors_per_battery' => 16,
                ]),
            ],
            [
                'designation' => 'F-16C/D Fighting Falcon',
                'nato_designation' => null,
                'common_name' => 'F-16',
                'country_code' => 'US',
                'category_slug' => 'fighters',
                'introduced_year' => 1978,
                'estimated_produced' => 4600,
                'description' => 'Multirole fighter aircraft.',
                'specifications' => json_encode([
                    'crew' => 1,
                    'max_speed_mach' => 2,
                    'range_km' => 4220,
                    'hardpoints' => 9,
                ]),
            ],
            [
                'designation' => 'F-35A Lightning II',
                'nato_designation' => null,
                'common_name' => 'F-35A',
                'country_code' => 'US',
                'category_slug' => 'fighters',
                'introduced_year' => 2015,
                'estimated_produced' => 1000,
                'description' => 'Fifth-generation stealth multirole fighter.',
                'specifications' => json_encode([
                    'crew' => 1,
                    'max_speed_mach' => 1.6,
                    'range_km' => 2200,
                    'stealth' => true,
                    'vtol' => false,
                ]),
            ],
            [
                'designation' => 'AH-64E Apache Guardian',
                'nato_designation' => null,
                'common_name' => 'Apache',
                'country_code' => 'US',
                'category_slug' => 'attack-heli',
                'introduced_year' => 2011,
                'estimated_produced' => 700,
                'description' => 'Advanced attack helicopter with Longbow radar.',
                'specifications' => json_encode([
                    'crew' => 2,
                    'max_speed_kmh' => 293,
                    'range_km' => 476,
                    'main_armament' => '30mm M230 chain gun',
                    'missiles' => 16,
                ]),
            ],

            // ========================================
            // GERMAN EQUIPMENT
            // ========================================
            [
                'designation' => 'Leopard 2A7',
                'nato_designation' => null,
                'common_name' => 'Leopard 2',
                'country_code' => 'DE',
                'category_slug' => 'mbt',
                'introduced_year' => 2014,
                'estimated_produced' => 200,
                'description' => 'German main battle tank, latest variant.',
                'specifications' => json_encode([
                    'crew' => 4,
                    'weight_tonnes' => 67,
                    'main_gun' => '120mm L55A1',
                    'engine' => 'MTU MB 873 1500hp',
                ]),
            ],
            [
                'designation' => 'Marder 1A5',
                'nato_designation' => null,
                'common_name' => 'Marder',
                'country_code' => 'DE',
                'category_slug' => 'ifv',
                'introduced_year' => 2002,
                'estimated_produced' => 350,
                'description' => 'German infantry fighting vehicle.',
                'specifications' => json_encode([
                    'crew' => 3,
                    'passengers' => 6,
                    'weight_tonnes' => 35,
                    'main_armament' => '20mm Rh202',
                ]),
            ],
            [
                'designation' => 'PzH 2000',
                'nato_designation' => null,
                'common_name' => 'Panzerhaubitze 2000',
                'country_code' => 'DE',
                'category_slug' => 'spa',
                'introduced_year' => 1998,
                'estimated_produced' => 300,
                'description' => 'Self-propelled 155mm howitzer.',
                'specifications' => json_encode([
                    'crew' => 5,
                    'main_gun' => '155mm L52',
                    'range_km' => 56,
                    'rate_of_fire' => '10 rounds/min',
                ]),
            ],
            [
                'designation' => 'IRIS-T SLM',
                'nato_designation' => null,
                'common_name' => 'IRIS-T',
                'country_code' => 'DE',
                'category_slug' => 'sam',
                'introduced_year' => 2022,
                'estimated_produced' => 40,
                'description' => 'Medium-range surface-to-air missile system.',
                'specifications' => json_encode([
                    'range_km' => 40,
                    'altitude_km' => 20,
                    'missiles' => 8,
                ]),
            ],

            // ========================================
            // UK EQUIPMENT
            // ========================================
            [
                'designation' => 'Challenger 2',
                'nato_designation' => null,
                'common_name' => 'Challenger 2',
                'country_code' => 'GB',
                'category_slug' => 'mbt',
                'introduced_year' => 1998,
                'estimated_produced' => 386,
                'description' => 'British main battle tank with rifled gun.',
                'specifications' => json_encode([
                    'crew' => 4,
                    'weight_tonnes' => 75,
                    'main_gun' => '120mm L30A1 rifled',
                    'engine' => 'Perkins CV12 1200hp',
                ]),
            ],
            [
                'designation' => 'AS-90',
                'nato_designation' => null,
                'common_name' => 'AS-90',
                'country_code' => 'GB',
                'category_slug' => 'spa',
                'introduced_year' => 1993,
                'estimated_produced' => 179,
                'description' => 'Self-propelled 155mm howitzer.',
                'specifications' => json_encode([
                    'crew' => 5,
                    'main_gun' => '155mm L31',
                    'range_km' => 24.7,
                ]),
            ],
            [
                'designation' => 'Starstreak',
                'nato_designation' => null,
                'common_name' => 'Starstreak',
                'country_code' => 'GB',
                'category_slug' => 'manpads',
                'introduced_year' => 1997,
                'estimated_produced' => 5000,
                'description' => 'High-velocity surface-to-air missile.',
                'specifications' => json_encode([
                    'guidance' => 'Laser beam-riding',
                    'speed_mach' => 4,
                    'range_km' => 7,
                    'darts' => 3,
                ]),
            ],
            [
                'designation' => 'Storm Shadow',
                'nato_designation' => null,
                'common_name' => 'Storm Shadow',
                'country_code' => 'GB',
                'category_slug' => 'cruise',
                'introduced_year' => 2003,
                'estimated_produced' => 900,
                'description' => 'Long-range air-launched cruise missile.',
                'specifications' => json_encode([
                    'range_km' => 560,
                    'speed' => 'subsonic',
                    'guidance' => 'INS + terrain reference + GPS',
                    'warhead_kg' => 450,
                ]),
            ],

            // ========================================
            // FRENCH EQUIPMENT
            // ========================================
            [
                'designation' => 'Leclerc',
                'nato_designation' => null,
                'common_name' => 'Leclerc',
                'country_code' => 'FR',
                'category_slug' => 'mbt',
                'introduced_year' => 1992,
                'estimated_produced' => 862,
                'description' => 'French main battle tank with autoloader.',
                'specifications' => json_encode([
                    'crew' => 3,
                    'weight_tonnes' => 57,
                    'main_gun' => '120mm CN120-26/52',
                    'engine' => 'SACM V8X 1500hp',
                ]),
            ],
            [
                'designation' => 'AMX-10 RC',
                'nato_designation' => null,
                'common_name' => 'AMX-10 RC',
                'country_code' => 'FR',
                'category_slug' => 'ifv',
                'introduced_year' => 1981,
                'estimated_produced' => 337,
                'description' => 'Wheeled armored reconnaissance vehicle.',
                'specifications' => json_encode([
                    'crew' => 4,
                    'wheels' => '6x6',
                    'weight_tonnes' => 17,
                    'main_gun' => '105mm CN105 F1',
                ]),
            ],
            [
                'designation' => 'CAESAR',
                'nato_designation' => null,
                'common_name' => 'CAESAR',
                'country_code' => 'FR',
                'category_slug' => 'spa',
                'introduced_year' => 2008,
                'estimated_produced' => 500,
                'description' => 'Truck-mounted 155mm howitzer.',
                'specifications' => json_encode([
                    'crew' => 5,
                    'main_gun' => '155mm L52',
                    'range_km' => 42,
                    'mobility' => 'wheeled',
                ]),
            ],
            [
                'designation' => 'SCALP-EG',
                'nato_designation' => null,
                'common_name' => 'SCALP',
                'country_code' => 'FR',
                'category_slug' => 'cruise',
                'introduced_year' => 2003,
                'estimated_produced' => 600,
                'description' => 'Air-launched cruise missile (French Storm Shadow).',
                'specifications' => json_encode([
                    'range_km' => 560,
                    'speed' => 'subsonic',
                    'warhead_kg' => 450,
                ]),
            ],
            [
                'designation' => 'Rafale',
                'nato_designation' => null,
                'common_name' => 'Rafale',
                'country_code' => 'FR',
                'category_slug' => 'fighters',
                'introduced_year' => 2001,
                'estimated_produced' => 230,
                'description' => 'Twin-engine multirole fighter.',
                'specifications' => json_encode([
                    'crew' => 1,
                    'max_speed_mach' => 1.8,
                    'range_km' => 3700,
                    'hardpoints' => 14,
                ]),
            ],

            // ========================================
            // ISRAELI EQUIPMENT
            // ========================================
            [
                'designation' => 'Merkava Mk.4',
                'nato_designation' => null,
                'common_name' => 'Merkava Mk.4',
                'country_code' => 'IL',
                'category_slug' => 'mbt',
                'introduced_year' => 2004,
                'estimated_produced' => 360,
                'description' => 'Israeli main battle tank with Trophy APS.',
                'specifications' => json_encode([
                    'crew' => 4,
                    'weight_tonnes' => 65,
                    'main_gun' => '120mm MG253 smoothbore',
                    'engine' => 'MTU MT 883 1500hp',
                    'aps' => 'Trophy',
                ]),
            ],
            [
                'designation' => 'Namer',
                'nato_designation' => null,
                'common_name' => 'Namer',
                'country_code' => 'IL',
                'category_slug' => 'apc',
                'introduced_year' => 2008,
                'estimated_produced' => 120,
                'description' => 'Heavy APC based on Merkava chassis.',
                'specifications' => json_encode([
                    'crew' => 3,
                    'passengers' => 9,
                    'weight_tonnes' => 60,
                    'aps' => 'Trophy',
                ]),
            ],
            [
                'designation' => 'Iron Dome',
                'nato_designation' => null,
                'common_name' => 'Iron Dome',
                'country_code' => 'IL',
                'category_slug' => 'sam',
                'introduced_year' => 2011,
                'estimated_produced' => 15,
                'description' => 'Mobile air defense system for short-range rockets.',
                'specifications' => json_encode([
                    'interceptors_per_battery' => 60,
                    'range_km' => 70,
                    'intercept_rate' => '90%',
                ]),
            ],
            [
                'designation' => 'David\'s Sling',
                'nato_designation' => null,
                'common_name' => 'David\'s Sling',
                'country_code' => 'IL',
                'category_slug' => 'sam',
                'introduced_year' => 2017,
                'estimated_produced' => 6,
                'description' => 'Medium-range air and missile defense system.',
                'specifications' => json_encode([
                    'range_km' => 300,
                    'targets' => 'cruise missiles, ballistic missiles, aircraft',
                ]),
            ],
            [
                'designation' => 'Spike ATGM',
                'nato_designation' => null,
                'common_name' => 'Spike',
                'country_code' => 'IL',
                'category_slug' => 'atgm',
                'introduced_year' => 1997,
                'estimated_produced' => 33000,
                'description' => 'Family of fire-and-forget anti-tank missiles.',
                'specifications' => json_encode([
                    'variants' => ['MR', 'LR', 'ER', 'NLOS'],
                    'range_km' => '2.5-25',
                    'guidance' => 'electro-optical',
                ]),
            ],
            [
                'designation' => 'F-35I Adir',
                'nato_designation' => null,
                'common_name' => 'F-35I Adir',
                'country_code' => 'IL',
                'category_slug' => 'fighters',
                'introduced_year' => 2017,
                'estimated_produced' => 50,
                'description' => 'Israeli variant of F-35A with unique avionics.',
                'specifications' => json_encode([
                    'crew' => 1,
                    'max_speed_mach' => 1.6,
                    'stealth' => true,
                    'israeli_systems' => true,
                ]),
            ],

            // ========================================
            // CHINESE EQUIPMENT
            // ========================================
            [
                'designation' => 'Type 99A',
                'nato_designation' => null,
                'common_name' => 'Type 99A',
                'country_code' => 'CN',
                'category_slug' => 'mbt',
                'introduced_year' => 2011,
                'estimated_produced' => 600,
                'description' => 'Chinese third-generation main battle tank.',
                'specifications' => json_encode([
                    'crew' => 3,
                    'weight_tonnes' => 58,
                    'main_gun' => '125mm ZPT-98',
                    'engine' => '1500hp diesel',
                ]),
            ],
            [
                'designation' => 'J-20',
                'nato_designation' => 'Black Eagle',
                'common_name' => 'J-20',
                'country_code' => 'CN',
                'category_slug' => 'fighters',
                'introduced_year' => 2017,
                'estimated_produced' => 200,
                'description' => 'Fifth-generation stealth fighter.',
                'specifications' => json_encode([
                    'crew' => 1,
                    'max_speed_mach' => 2,
                    'stealth' => true,
                ]),
            ],
            [
                'designation' => 'DF-21D',
                'nato_designation' => 'CSS-5 Mod 4',
                'common_name' => 'Carrier Killer',
                'country_code' => 'CN',
                'category_slug' => 'ballistic',
                'introduced_year' => 2010,
                'estimated_produced' => 100,
                'description' => 'Anti-ship ballistic missile.',
                'specifications' => json_encode([
                    'range_km' => 1500,
                    'warhead_kg' => 600,
                    'guidance' => 'terminal maneuvering',
                ]),
            ],
        ];

        foreach ($equipment as $item) {
            $countryId = $countries[$item['country_code']] ?? null;
            $categoryId = $categories[$item['category_slug']] ?? null;

            if (!$countryId || !$categoryId) {
                $this->command->warn("Skipping {$item['designation']}: Missing country or category");
                continue;
            }

            DB::table('military_equipment')->updateOrInsert(
                ['designation' => $item['designation']],
                [
                    'uuid' => Str::uuid(),
                    'designation' => $item['designation'],
                    'nato_designation' => $item['nato_designation'],
                    'common_name' => $item['common_name'],
                    'country_id' => $countryId,
                    'category_id' => $categoryId,
                    'introduced_year' => $item['introduced_year'],
                    'estimated_produced' => $item['estimated_produced'],
                    'description' => $item['description'],
                    'specifications' => $item['specifications'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
