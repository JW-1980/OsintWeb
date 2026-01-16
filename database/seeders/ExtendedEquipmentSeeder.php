<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ExtendedEquipmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Adds 100+ additional equipment items including 50 missiles with detailed descriptions.
     */
    public function run(): void
    {
        $this->seedMissiles();
        $this->seedAdditionalEquipment();
        $this->command->info('Extended equipment seeded successfully.');
    }

    private function seedMissiles(): void
    {
        $categories = DB::table('equipment_categories')->pluck('id', 'slug');
        $countries = DB::table('countries')->pluck('id', 'iso_code');

        $missiles = [
            // =============================================
            // AIR-TO-AIR MISSILES
            // =============================================
            [
                'designation' => 'AIM-120D AMRAAM',
                'nato_designation' => null,
                'common_name' => 'AMRAAM',
                'country_code' => 'US',
                'category_slug' => 'missiles',
                'introduced_year' => 2008,
                'estimated_produced' => 15000,
                'description' => 'Advanced Medium-Range Air-to-Air Missile developed by Hughes Aircraft (now Raytheon) for the US Air Force and Navy. Primary beyond-visual-range missile of NATO air forces. Features active radar homing with inertial navigation mid-course guidance. Recognizable by cylindrical body with four tail fins and four smaller control fins. Combat proven in multiple conflicts. Range exceeds 160km for latest variants.',
                'specifications' => json_encode([
                    'manufacturer' => 'Raytheon',
                    'origin_country' => 'United States',
                    'range_km' => 160,
                    'speed_mach' => 4,
                    'length_m' => 3.66,
                    'diameter_mm' => 178,
                    'weight_kg' => 152,
                    'warhead_kg' => 18,
                    'guidance' => 'Active radar homing + INS',
                    'operators' => 37,
                    'unit_cost_usd' => 400000,
                ]),
            ],
            [
                'designation' => 'AIM-9X Sidewinder',
                'nato_designation' => null,
                'common_name' => 'Sidewinder',
                'country_code' => 'US',
                'category_slug' => 'missiles',
                'introduced_year' => 2003,
                'estimated_produced' => 50000,
                'description' => 'Short-range infrared-guided air-to-air missile, latest variant of the legendary Sidewinder family developed since 1956. Manufactured by Raytheon. Features high off-boresight capability with helmet-mounted cueing, thrust vectoring for extreme maneuverability. Distinctive slim profile with rollerons on tail fins. Used by 40+ nations.',
                'specifications' => json_encode([
                    'manufacturer' => 'Raytheon',
                    'origin_country' => 'United States',
                    'range_km' => 35,
                    'speed_mach' => 2.5,
                    'length_m' => 3,
                    'diameter_mm' => 127,
                    'weight_kg' => 85,
                    'warhead_kg' => 9.4,
                    'guidance' => 'Infrared imaging seeker',
                    'lock_on_after_launch' => true,
                ]),
            ],
            [
                'designation' => 'R-77 Vympel',
                'nato_designation' => 'AA-12 Adder',
                'common_name' => 'R-77',
                'country_code' => 'RU',
                'category_slug' => 'missiles',
                'introduced_year' => 1994,
                'estimated_produced' => 5000,
                'description' => 'Russian medium-range active radar homing air-to-air missile developed by Vympel NPO. Designed to counter AIM-120 AMRAAM. Distinctive lattice tail fins (grid fins) provide high maneuverability. Used on Su-27, Su-30, Su-35, MiG-29, MiG-35. Exported to India, China, Algeria.',
                'specifications' => json_encode([
                    'manufacturer' => 'Vympel NPO',
                    'origin_country' => 'Russia',
                    'range_km' => 110,
                    'speed_mach' => 4.5,
                    'length_m' => 3.6,
                    'diameter_mm' => 200,
                    'weight_kg' => 175,
                    'warhead_kg' => 22,
                    'guidance' => 'Active radar + INS + datalink',
                    'recognition' => 'Distinctive grid tail fins',
                ]),
            ],
            [
                'designation' => 'R-73 Archer',
                'nato_designation' => 'AA-11 Archer',
                'common_name' => 'R-73',
                'country_code' => 'RU',
                'category_slug' => 'missiles',
                'introduced_year' => 1984,
                'estimated_produced' => 20000,
                'description' => 'Russian short-range infrared-guided missile developed by Vympel. Revolutionary at introduction with thrust vectoring and high off-boresight capability. Recognizable by distinctive forward canards and pointed nose. Standard dogfight missile for Russian and export fighters.',
                'specifications' => json_encode([
                    'manufacturer' => 'Vympel NPO',
                    'origin_country' => 'Russia',
                    'range_km' => 30,
                    'speed_mach' => 2.5,
                    'length_m' => 2.9,
                    'diameter_mm' => 170,
                    'weight_kg' => 105,
                    'warhead_kg' => 7.4,
                    'guidance' => 'Infrared + thrust vectoring',
                    'off_boresight_deg' => 45,
                ]),
            ],
            [
                'designation' => 'MBDA Meteor',
                'nato_designation' => null,
                'common_name' => 'Meteor',
                'country_code' => 'GB',
                'category_slug' => 'missiles',
                'introduced_year' => 2016,
                'estimated_produced' => 1500,
                'description' => 'European beyond-visual-range air-to-air missile with ramjet propulsion, developed by MBDA for Eurofighter Typhoon, Gripen, and Rafale. Unique throttleable ducted rocket maintains energy throughout flight envelope. Considered most capable BVR missile currently in service. 100km+ no-escape zone.',
                'specifications' => json_encode([
                    'manufacturer' => 'MBDA',
                    'origin_country' => 'UK/France/Germany/Italy/Spain/Sweden',
                    'range_km' => 200,
                    'speed_mach' => 4,
                    'length_m' => 3.65,
                    'diameter_mm' => 178,
                    'weight_kg' => 185,
                    'warhead_kg' => 25,
                    'guidance' => 'Active radar + INS + datalink',
                    'propulsion' => 'Throttleable ducted rocket (ramjet)',
                ]),
            ],
            [
                'designation' => 'PL-15',
                'nato_designation' => null,
                'common_name' => 'PL-15',
                'country_code' => 'CN',
                'category_slug' => 'missiles',
                'introduced_year' => 2016,
                'estimated_produced' => 2000,
                'description' => 'Chinese long-range air-to-air missile developed by CATIC/Luoyang. Features dual-pulse rocket motor for extended range. Primary armament for J-20, J-16, J-10C. Believed to have range exceeding AIM-120D. Poses significant challenge to AWACS and tanker aircraft.',
                'specifications' => json_encode([
                    'manufacturer' => 'CATIC/Luoyang',
                    'origin_country' => 'China',
                    'range_km' => 200,
                    'speed_mach' => 4,
                    'length_m' => 4,
                    'diameter_mm' => 203,
                    'weight_kg' => 210,
                    'guidance' => 'Active radar + INS + datalink',
                    'dual_pulse_motor' => true,
                ]),
            ],
            [
                'designation' => 'IRIS-T',
                'nato_designation' => null,
                'common_name' => 'IRIS-T',
                'country_code' => 'DE',
                'category_slug' => 'missiles',
                'introduced_year' => 2005,
                'estimated_produced' => 4000,
                'description' => 'German short-range infrared-guided missile developed by Diehl Defence. Replacement for AIM-9 Sidewinder. Features imaging infrared seeker with exceptional IRCCM. Thrust vector control for high agility. Also adapted for surface-to-air role (IRIS-T SLM/SLS). Used by 13 nations.',
                'specifications' => json_encode([
                    'manufacturer' => 'Diehl Defence',
                    'origin_country' => 'Germany',
                    'range_km' => 25,
                    'speed_mach' => 3,
                    'length_m' => 2.94,
                    'diameter_mm' => 127,
                    'weight_kg' => 89,
                    'warhead_kg' => 11.4,
                    'guidance' => 'Imaging infrared',
                    'lock_on_after_launch' => true,
                ]),
            ],
            [
                'designation' => 'MICA IR/EM',
                'nato_designation' => null,
                'common_name' => 'MICA',
                'country_code' => 'FR',
                'category_slug' => 'missiles',
                'introduced_year' => 1996,
                'estimated_produced' => 6000,
                'description' => 'French multi-role air-to-air missile by MBDA in two versions: IR (infrared) and EM (electromagnetic/radar). Interchangeable seekers on same airframe. Rafale primary armament. Unique thrust-vectoring with four jetavators. Fire-and-forget with datalink update capability.',
                'specifications' => json_encode([
                    'manufacturer' => 'MBDA France',
                    'origin_country' => 'France',
                    'range_km' => 80,
                    'speed_mach' => 4,
                    'length_m' => 3.1,
                    'diameter_mm' => 160,
                    'weight_kg' => 112,
                    'warhead_kg' => 12,
                    'guidance' => 'Active radar (EM) or Infrared imaging (IR)',
                    'thrust_vectoring' => true,
                ]),
            ],

            // =============================================
            // ANTI-SHIP MISSILES
            // =============================================
            [
                'designation' => 'RGM-84 Harpoon',
                'nato_designation' => null,
                'common_name' => 'Harpoon',
                'country_code' => 'US',
                'category_slug' => 'cruise',
                'introduced_year' => 1977,
                'estimated_produced' => 7500,
                'description' => 'American all-weather, over-the-horizon anti-ship missile developed by McDonnell Douglas (now Boeing). Sea-skimming cruise profile. Ship, sub, and air-launched variants. Standard Western anti-ship missile for 40+ years. Distinctive cylindrical body with pop-out wings and ventral air intake.',
                'specifications' => json_encode([
                    'manufacturer' => 'Boeing',
                    'origin_country' => 'United States',
                    'range_km' => 240,
                    'speed' => 'Mach 0.85 (subsonic)',
                    'length_m' => 4.6,
                    'diameter_mm' => 343,
                    'weight_kg' => 691,
                    'warhead_kg' => 221,
                    'guidance' => 'Active radar + GPS/INS',
                    'flight_profile' => 'Sea-skimming',
                    'operators' => 30,
                ]),
            ],
            [
                'designation' => 'AGM-158C LRASM',
                'nato_designation' => null,
                'common_name' => 'LRASM',
                'country_code' => 'US',
                'category_slug' => 'cruise',
                'introduced_year' => 2018,
                'estimated_produced' => 500,
                'description' => 'Long Range Anti-Ship Missile developed by Lockheed Martin. Stealthy successor to Harpoon with autonomous targeting using onboard sensors and AI. Designed for contested A2/AD environments. Multi-mode seeker with passive RF, active radar, and imaging infrared.',
                'specifications' => json_encode([
                    'manufacturer' => 'Lockheed Martin',
                    'origin_country' => 'United States',
                    'range_km' => 900,
                    'speed' => 'High subsonic',
                    'length_m' => 4.27,
                    'weight_kg' => 1020,
                    'warhead_kg' => 454,
                    'guidance' => 'Autonomous multi-mode (passive RF, active radar, IIR)',
                    'stealth' => true,
                    'unit_cost_usd' => 3000000,
                ]),
            ],
            [
                'designation' => 'P-800 Oniks',
                'nato_designation' => 'SS-N-26 Strobile',
                'common_name' => 'Oniks/Yakhont',
                'country_code' => 'RU',
                'category_slug' => 'cruise',
                'introduced_year' => 2002,
                'estimated_produced' => 1500,
                'description' => 'Russian supersonic anti-ship cruise missile developed by NPO Mashinostroyeniya. Export version called Yakhont, basis for BrahMos. Ramjet propulsion enables sustained Mach 2.5+ flight. Ship, coastal, and submarine-launched. High kinetic energy makes interception difficult.',
                'specifications' => json_encode([
                    'manufacturer' => 'NPO Mashinostroyeniya',
                    'origin_country' => 'Russia',
                    'range_km' => 500,
                    'speed_mach' => 2.6,
                    'length_m' => 8.9,
                    'diameter_mm' => 670,
                    'weight_kg' => 3000,
                    'warhead_kg' => 250,
                    'guidance' => 'Active/passive radar + INS',
                    'propulsion' => 'Ramjet',
                ]),
            ],
            [
                'designation' => 'Kh-35U',
                'nato_designation' => 'AS-20 Kayak',
                'common_name' => 'Uran',
                'country_code' => 'RU',
                'category_slug' => 'cruise',
                'introduced_year' => 2003,
                'estimated_produced' => 2000,
                'description' => 'Russian subsonic sea-skimming anti-ship missile developed by Tactical Missiles Corporation. Often called "Harpoonski" for similar role to Harpoon. Upgraded Kh-35U features extended range and improved guidance. Ship, coastal, and air-launched variants.',
                'specifications' => json_encode([
                    'manufacturer' => 'Tactical Missiles Corporation',
                    'origin_country' => 'Russia',
                    'range_km' => 260,
                    'speed' => 'Mach 0.9',
                    'length_m' => 4.4,
                    'diameter_mm' => 420,
                    'weight_kg' => 670,
                    'warhead_kg' => 145,
                    'guidance' => 'Active radar + INS + GLONASS',
                    'flight_profile' => 'Sea-skimming 3-5m',
                ]),
            ],
            [
                'designation' => 'NSM Naval Strike Missile',
                'nato_designation' => null,
                'common_name' => 'NSM',
                'country_code' => 'NO',
                'category_slug' => 'cruise',
                'introduced_year' => 2012,
                'estimated_produced' => 800,
                'description' => 'Norwegian stealth anti-ship missile developed by Kongsberg. Fifth-generation missile with passive imaging infrared seeker for terminal guidance. Low radar cross-section composite construction. Selected by US Navy for littoral combat ships. Can strike both naval and land targets.',
                'specifications' => json_encode([
                    'manufacturer' => 'Kongsberg Defence',
                    'origin_country' => 'Norway',
                    'range_km' => 185,
                    'speed' => 'High subsonic',
                    'length_m' => 3.96,
                    'weight_kg' => 407,
                    'warhead_kg' => 125,
                    'guidance' => 'Imaging infrared + INS + GPS + terrain reference',
                    'stealth' => true,
                    'operators' => ['Norway', 'Poland', 'USA', 'Germany', 'Malaysia'],
                ]),
            ],
            [
                'designation' => 'Exocet MM40 Block 3',
                'nato_designation' => null,
                'common_name' => 'Exocet',
                'country_code' => 'FR',
                'category_slug' => 'cruise',
                'introduced_year' => 2008,
                'estimated_produced' => 3500,
                'description' => 'French sea-skimming anti-ship missile by MBDA, famous from Falklands War. Block 3 features GPS waypoint navigation, land attack capability, and increased range. Distinctive cruciform wings. One of most widely exported anti-ship missiles with 35+ operators.',
                'specifications' => json_encode([
                    'manufacturer' => 'MBDA',
                    'origin_country' => 'France',
                    'range_km' => 200,
                    'speed' => 'Mach 0.93',
                    'length_m' => 5.8,
                    'diameter_mm' => 350,
                    'weight_kg' => 870,
                    'warhead_kg' => 165,
                    'guidance' => 'INS + GPS + active radar',
                    'operators' => 35,
                ]),
            ],
            [
                'designation' => 'YJ-18',
                'nato_designation' => 'CH-SS-NX-13',
                'common_name' => 'Eagle Strike 18',
                'country_code' => 'CN',
                'category_slug' => 'cruise',
                'introduced_year' => 2015,
                'estimated_produced' => 1000,
                'description' => 'Chinese anti-ship cruise missile with unique two-stage profile. Cruises subsonically, then accelerates to Mach 3 for terminal attack. Derived from Russian 3M54 Kalibr. Primary anti-ship weapon of PLAN Type 052D destroyers and Type 055 cruisers.',
                'specifications' => json_encode([
                    'manufacturer' => 'China Aerospace Science and Industry Corporation',
                    'origin_country' => 'China',
                    'range_km' => 540,
                    'speed' => 'Mach 0.8 cruise / Mach 3 terminal',
                    'weight_kg' => 2000,
                    'warhead_kg' => 300,
                    'guidance' => 'Active radar + INS + satellite',
                    'two_stage' => true,
                ]),
            ],

            // =============================================
            // SURFACE-TO-AIR MISSILES
            // =============================================
            [
                'designation' => 'MIM-104 PAC-3 MSE',
                'nato_designation' => null,
                'common_name' => 'Patriot PAC-3 MSE',
                'country_code' => 'US',
                'category_slug' => 'sam',
                'introduced_year' => 2015,
                'estimated_produced' => 1500,
                'description' => 'Most advanced Patriot interceptor variant developed by Lockheed Martin. Missile Segment Enhancement provides 50% more range and altitude than PAC-3. Hit-to-kill technology for ballistic missile defense. Distinguished by smaller size allowing 16 per launcher vs 4 PAC-2.',
                'specifications' => json_encode([
                    'manufacturer' => 'Lockheed Martin',
                    'origin_country' => 'United States',
                    'range_km' => 35,
                    'altitude_km' => 35,
                    'speed_mach' => 5,
                    'length_m' => 5.2,
                    'weight_kg' => 312,
                    'guidance' => 'Active radar + hit-to-kill',
                    'targets' => 'Ballistic missiles, cruise missiles, aircraft',
                    'interceptors_per_launcher' => 16,
                ]),
            ],
            [
                'designation' => 'THAAD',
                'nato_designation' => null,
                'common_name' => 'THAAD',
                'country_code' => 'US',
                'category_slug' => 'sam',
                'introduced_year' => 2008,
                'estimated_produced' => 200,
                'description' => 'Terminal High Altitude Area Defense system by Lockheed Martin. Designed to intercept ballistic missiles in terminal phase both inside and outside atmosphere. Hit-to-kill interceptor with no explosive warhead. Controversial deployment in South Korea due to Chinese objections.',
                'specifications' => json_encode([
                    'manufacturer' => 'Lockheed Martin',
                    'origin_country' => 'United States',
                    'range_km' => 200,
                    'altitude_km' => 150,
                    'speed_mach' => 8,
                    'length_m' => 6.17,
                    'weight_kg' => 900,
                    'guidance' => 'Infrared seeker + hit-to-kill',
                    'targets' => 'Short and medium range ballistic missiles',
                    'interceptors_per_launcher' => 8,
                    'unit_cost_usd' => 800000000,
                ]),
            ],
            [
                'designation' => 'S-300PMU-2 Favorit',
                'nato_designation' => 'SA-20B Gargoyle',
                'common_name' => 'S-300PMU-2',
                'country_code' => 'RU',
                'category_slug' => 'sam',
                'introduced_year' => 1997,
                'estimated_produced' => 50,
                'description' => 'Advanced Russian long-range surface-to-air missile system by Almaz-Antey. Export version of S-300PM2. Can engage aircraft, cruise missiles, and ballistic missiles. Recognizable by cylindrical 48N6E2 missiles in quad canisters on 5P85 TEL. Operated by China, Greece, Iran.',
                'specifications' => json_encode([
                    'manufacturer' => 'Almaz-Antey',
                    'origin_country' => 'Russia',
                    'range_km' => 200,
                    'altitude_km' => 27,
                    'missiles_per_battery' => 48,
                    'engagement_capability' => '36 targets / 72 missiles simultaneously',
                    'missile_type' => '48N6E2',
                    'guidance' => 'Semi-active radar + TVM',
                ]),
            ],
            [
                'designation' => 'HQ-9B',
                'nato_designation' => 'CSA-9',
                'common_name' => 'Hong Qi 9B',
                'country_code' => 'CN',
                'category_slug' => 'sam',
                'introduced_year' => 2017,
                'estimated_produced' => 100,
                'description' => 'Chinese long-range SAM developed by CPMIEC, evolved from S-300 technology. Features improved anti-ballistic missile capability. Export version FD-2000 selected by Turkey in controversial deal. Naval variant HHQ-9 on Type 052C/D destroyers.',
                'specifications' => json_encode([
                    'manufacturer' => 'CPMIEC',
                    'origin_country' => 'China',
                    'range_km' => 250,
                    'altitude_km' => 30,
                    'speed_mach' => 4.2,
                    'weight_kg' => 1300,
                    'guidance' => 'Active radar homing',
                    'targets' => 'Aircraft, cruise missiles, tactical ballistic missiles',
                ]),
            ],
            [
                'designation' => '9K37M1 Buk-M1',
                'nato_designation' => 'SA-11 Gadfly',
                'common_name' => 'Buk-M1',
                'country_code' => 'RU',
                'category_slug' => 'sam',
                'introduced_year' => 1980,
                'estimated_produced' => 500,
                'description' => 'Soviet/Russian medium-range SAM by Almaz-Antey. Notorious for shooting down Malaysia Airlines MH17 over Ukraine in 2014. Self-propelled on tracked chassis. Recognizable 9M38 missiles with distinctive nose cone. Upgraded versions still in widespread use.',
                'specifications' => json_encode([
                    'manufacturer' => 'Almaz-Antey',
                    'origin_country' => 'Russia/USSR',
                    'range_km' => 35,
                    'altitude_km' => 22,
                    'speed_mach' => 3,
                    'length_m' => 5.5,
                    'weight_kg' => 690,
                    'warhead_kg' => 70,
                    'guidance' => 'Semi-active radar',
                    'missiles_per_telar' => 4,
                ]),
            ],
            [
                'designation' => 'Tor-M2',
                'nato_designation' => 'SA-15 Gauntlet',
                'common_name' => 'Tor-M2',
                'country_code' => 'RU',
                'category_slug' => 'sam',
                'introduced_year' => 2012,
                'estimated_produced' => 150,
                'description' => 'Russian short-range tactical SAM by IEMZ Kupol. All-in-one system with radar, command, and 16 missiles on single tracked vehicle. Designed to protect armored formations from precision-guided munitions, cruise missiles, and drones. Reaction time under 10 seconds.',
                'specifications' => json_encode([
                    'manufacturer' => 'IEMZ Kupol',
                    'origin_country' => 'Russia',
                    'range_km' => 16,
                    'altitude_km' => 12,
                    'missiles' => 16,
                    'simultaneous_targets' => 4,
                    'reaction_time_sec' => 8,
                    'guidance' => 'Radio command',
                    'mobility' => 'Tracked, all-terrain',
                ]),
            ],
            [
                'designation' => 'NASAMS 3',
                'nato_designation' => null,
                'common_name' => 'NASAMS',
                'country_code' => 'NO',
                'category_slug' => 'sam',
                'introduced_year' => 2019,
                'estimated_produced' => 200,
                'description' => 'Norwegian/American Advanced Surface-to-Air Missile System developed by Kongsberg and Raytheon. Uses AMRAAM air-to-air missiles in ground-launched role. Highly mobile, network-centric. Provided to Ukraine for air defense. Guards Washington DC airspace.',
                'specifications' => json_encode([
                    'manufacturer' => 'Kongsberg/Raytheon',
                    'origin_country' => 'Norway/USA',
                    'range_km' => 40,
                    'altitude_km' => 21,
                    'missiles_per_launcher' => 6,
                    'missile_types' => ['AMRAAM', 'AMRAAM-ER', 'AIM-9X'],
                    'guidance' => 'Active radar (AMRAAM)',
                    'operators' => ['Norway', 'USA', 'Finland', 'Spain', 'Ukraine', 'Lithuania'],
                ]),
            ],
        ];

        foreach ($missiles as $item) {
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

        $this->command->info('First batch of missiles seeded (22 items)');
    }

    private function seedAdditionalEquipment(): void
    {
        $categories = DB::table('equipment_categories')->pluck('id', 'slug');
        $countries = DB::table('countries')->pluck('id', 'iso_code');

        $moreMissiles = [
            // =============================================
            // ANTI-TANK GUIDED MISSILES
            // =============================================
            [
                'designation' => 'BGM-71 TOW 2B',
                'nato_designation' => null,
                'common_name' => 'TOW',
                'country_code' => 'US',
                'category_slug' => 'atgm',
                'introduced_year' => 1991,
                'estimated_produced' => 700000,
                'description' => 'Tube-launched Optically-tracked Wire-guided missile developed by Hughes (now Raytheon). Most widely used ATGM in Western arsenals. TOW 2B variant features tandem warhead for top-attack against ERA-protected tanks. Wire-guided requiring operator to track target throughout flight. Recognizable by large missile tube and tripod launcher.',
                'specifications' => json_encode([
                    'manufacturer' => 'Raytheon',
                    'origin_country' => 'United States',
                    'range_m' => 4200,
                    'speed_ms' => 320,
                    'length_m' => 1.17,
                    'diameter_mm' => 152,
                    'weight_kg' => 22.6,
                    'penetration_mm' => 900,
                    'guidance' => 'Wire-guided SACLOS',
                    'warhead' => 'Tandem shaped charge',
                    'operators' => 40,
                ]),
            ],
            [
                'designation' => 'FGM-172 SRAW',
                'nato_designation' => null,
                'common_name' => 'SRAW',
                'country_code' => 'US',
                'category_slug' => 'atgm',
                'introduced_year' => 2002,
                'estimated_produced' => 10000,
                'description' => 'Short-Range Assault Weapon developed by Lockheed Martin. Predator variant (SRAW-MPV) features top-attack profile. Fire-and-forget inertial guidance compensates for target movement. Designed for urban combat with confined space capability. Soft-launch allows firing from enclosures.',
                'specifications' => json_encode([
                    'manufacturer' => 'Lockheed Martin',
                    'origin_country' => 'United States',
                    'range_m' => 600,
                    'weight_kg' => 9.5,
                    'penetration_mm' => 450,
                    'guidance' => 'Inertial with magnetic target detection',
                    'flight_profile' => 'Top-attack',
                    'soft_launch' => true,
                ]),
            ],
            [
                'designation' => '9M133FM-3 Kornet-EM',
                'nato_designation' => 'AT-14 Spriggan',
                'common_name' => 'Kornet-EM',
                'country_code' => 'RU',
                'category_slug' => 'atgm',
                'introduced_year' => 2012,
                'estimated_produced' => 25000,
                'description' => 'Enhanced Russian ATGM by KBP Instrument Design Bureau. Extended range variant of Kornet with thermobaric warhead option. Laser beam-riding guidance immune to jamming. Can engage helicopters and low-flying aircraft. Proven effective against Abrams and Merkava tanks in various conflicts.',
                'specifications' => json_encode([
                    'manufacturer' => 'KBP Instrument Design Bureau',
                    'origin_country' => 'Russia',
                    'range_m' => 10000,
                    'speed_ms' => 320,
                    'length_m' => 1.21,
                    'diameter_mm' => 152,
                    'weight_kg' => 33,
                    'penetration_mm' => 1300,
                    'guidance' => 'Laser beam-riding SACLOS',
                    'warhead_options' => ['Tandem HEAT', 'Thermobaric'],
                ]),
            ],
            [
                'designation' => '9M123 Khrizantema',
                'nato_designation' => 'AT-15 Springer',
                'common_name' => 'Khrizantema',
                'country_code' => 'RU',
                'category_slug' => 'atgm',
                'introduced_year' => 2005,
                'estimated_produced' => 5000,
                'description' => 'Russian supersonic ATGM capable of engaging targets in day, night, and adverse weather. Features both radar and laser guidance options. Vehicle-mounted on BMP-3 chassis (9P157). Only ATGM with millimeter-wave radar for fire-and-forget in all conditions.',
                'specifications' => json_encode([
                    'manufacturer' => 'KBP Instrument Design Bureau',
                    'origin_country' => 'Russia',
                    'range_m' => 6000,
                    'speed_ms' => 450,
                    'penetration_mm' => 1200,
                    'guidance' => 'MMW radar or laser beam-riding',
                    'supersonic' => true,
                    'all_weather' => true,
                ]),
            ],
            [
                'designation' => 'MILAN ER',
                'nato_designation' => null,
                'common_name' => 'MILAN',
                'country_code' => 'FR',
                'category_slug' => 'atgm',
                'introduced_year' => 2000,
                'estimated_produced' => 350000,
                'description' => 'Franco-German anti-tank missile developed by Euromissile. MILAN ER (Extended Response) features tandem warhead and increased range. Wire-guided SACLOS system. One of most widely exported ATGMs in history with 40+ operators. Still in active service worldwide.',
                'specifications' => json_encode([
                    'manufacturer' => 'MBDA',
                    'origin_country' => 'France/Germany',
                    'range_m' => 3000,
                    'speed_ms' => 200,
                    'weight_kg' => 7.1,
                    'penetration_mm' => 1000,
                    'guidance' => 'Wire-guided SACLOS',
                    'operators' => 40,
                ]),
            ],
            [
                'designation' => 'NLAW',
                'nato_designation' => null,
                'common_name' => 'NLAW',
                'country_code' => 'GB',
                'category_slug' => 'atgm',
                'introduced_year' => 2009,
                'estimated_produced' => 50000,
                'description' => 'Next generation Light Anti-tank Weapon developed by Saab Bofors Dynamics and Thales Air Defence. Single-use, shoulder-fired with predicted line-of-sight guidance. Features top-attack mode against tanks and direct attack for structures. Extensively supplied to Ukraine with proven combat success against Russian armor.',
                'specifications' => json_encode([
                    'manufacturer' => 'Saab Bofors Dynamics / Thales',
                    'origin_country' => 'UK/Sweden',
                    'range_m' => 800,
                    'weight_kg' => 12.5,
                    'penetration_mm' => 500,
                    'guidance' => 'PLOS (Predicted Line Of Sight)',
                    'flight_profile' => 'Top-attack or direct',
                    'single_use' => true,
                    'soft_launch' => true,
                ]),
            ],
            [
                'designation' => 'HJ-12 Red Arrow',
                'nato_designation' => null,
                'common_name' => 'Red Arrow 12',
                'country_code' => 'CN',
                'category_slug' => 'atgm',
                'introduced_year' => 2014,
                'estimated_produced' => 10000,
                'description' => 'Chinese fire-and-forget ATGM with imaging infrared seeker, similar to Javelin. Man-portable with top-attack capability. Represents significant advancement in Chinese ATGM technology. Export designated as HJ-12E.',
                'specifications' => json_encode([
                    'manufacturer' => 'NORINCO',
                    'origin_country' => 'China',
                    'range_m' => 4000,
                    'weight_kg' => 22,
                    'penetration_mm' => 1100,
                    'guidance' => 'Imaging infrared fire-and-forget',
                    'flight_profile' => 'Top-attack',
                    'soft_launch' => true,
                ]),
            ],
            [
                'designation' => 'Brimstone 2',
                'nato_designation' => null,
                'common_name' => 'Brimstone',
                'country_code' => 'GB',
                'category_slug' => 'atgm',
                'introduced_year' => 2016,
                'estimated_produced' => 3000,
                'description' => 'British air-launched precision strike missile by MBDA. Dual-mode millimeter wave radar and semi-active laser seeker. Fire-and-forget with salvo capability for multiple simultaneous engagements. Extensively used in Syria/Iraq and supplied to Ukraine. Can be ground-launched.',
                'specifications' => json_encode([
                    'manufacturer' => 'MBDA',
                    'origin_country' => 'United Kingdom',
                    'range_km' => 60,
                    'speed' => 'Mach 1.3',
                    'length_m' => 1.8,
                    'weight_kg' => 50,
                    'warhead_kg' => 6.2,
                    'guidance' => 'MMW radar + semi-active laser',
                    'fire_and_forget' => true,
                ]),
            ],

            // =============================================
            // MANPADS (Man-Portable Air-Defense Systems)
            // =============================================
            [
                'designation' => '9K38 Igla',
                'nato_designation' => 'SA-18 Grouse',
                'common_name' => 'Igla',
                'country_code' => 'RU',
                'category_slug' => 'manpads',
                'introduced_year' => 1983,
                'estimated_produced' => 50000,
                'description' => 'Soviet/Russian man-portable SAM developed by KB Mashinostroyeniya. Improved successor to Strela series. Features dual-band infrared seeker resistant to flares. Distinctive green launch tube. Widely exported and produced under license. Used by irregular forces worldwide.',
                'specifications' => json_encode([
                    'manufacturer' => 'KB Mashinostroyeniya',
                    'origin_country' => 'Russia',
                    'range_km' => 5.2,
                    'altitude_m' => 3500,
                    'speed_mach' => 2,
                    'weight_kg' => 17.9,
                    'warhead_kg' => 1.17,
                    'guidance' => 'Dual-band IR seeker',
                    'operators' => 50,
                ]),
            ],
            [
                'designation' => '9K333 Verba',
                'nato_designation' => 'SA-25',
                'common_name' => 'Verba',
                'country_code' => 'RU',
                'category_slug' => 'manpads',
                'introduced_year' => 2015,
                'estimated_produced' => 5000,
                'description' => 'Most advanced Russian MANPADS by KBM Kolomna. Features three-band infrared seeker (UV, near-IR, mid-IR) for exceptional flare rejection. Friend-or-foe identification system. Improved range over Igla-S. Limited export due to proliferation concerns.',
                'specifications' => json_encode([
                    'manufacturer' => 'KBM Kolomna',
                    'origin_country' => 'Russia',
                    'range_km' => 6.4,
                    'altitude_m' => 4500,
                    'speed_mach' => 2.3,
                    'weight_kg' => 17.2,
                    'warhead_kg' => 2.5,
                    'guidance' => 'Three-band IR seeker',
                    'iff' => true,
                ]),
            ],
            [
                'designation' => 'Mistral 3',
                'nato_designation' => null,
                'common_name' => 'Mistral',
                'country_code' => 'FR',
                'category_slug' => 'manpads',
                'introduced_year' => 2018,
                'estimated_produced' => 15000,
                'description' => 'French MANPADS developed by MBDA. Mistral 3 features improved imaging infrared seeker, datalink, and proximity fuze. Can be man-portable, vehicle-mounted, or ship-mounted. High kill probability against modern aircraft and UAVs.',
                'specifications' => json_encode([
                    'manufacturer' => 'MBDA',
                    'origin_country' => 'France',
                    'range_km' => 7.5,
                    'altitude_m' => 5000,
                    'speed_mach' => 2.6,
                    'weight_kg' => 19,
                    'warhead_kg' => 2.95,
                    'guidance' => 'Imaging IR with proximity fuze',
                    'operators' => 30,
                ]),
            ],
            [
                'designation' => 'RBS 70 NG',
                'nato_designation' => null,
                'common_name' => 'RBS 70',
                'country_code' => 'SE',
                'category_slug' => 'manpads',
                'introduced_year' => 2011,
                'estimated_produced' => 20000,
                'description' => 'Swedish laser beam-riding SAM by Saab Bofors Dynamics. Unique guidance system immune to IR countermeasures since missile has no seeker. Tripod-mounted with optional thermal sight. Bolide missile variant for enhanced anti-helicopter capability.',
                'specifications' => json_encode([
                    'manufacturer' => 'Saab Bofors Dynamics',
                    'origin_country' => 'Sweden',
                    'range_km' => 8,
                    'altitude_m' => 5000,
                    'speed_mach' => 2.5,
                    'weight_kg' => 87,
                    'guidance' => 'Laser beam-riding',
                    'jam_resistant' => true,
                    'operators' => 19,
                ]),
            ],
            [
                'designation' => 'QW-2 Vanguard',
                'nato_designation' => null,
                'common_name' => 'QW-2',
                'country_code' => 'CN',
                'category_slug' => 'manpads',
                'introduced_year' => 2002,
                'estimated_produced' => 10000,
                'description' => 'Chinese MANPADS with cooled infrared seeker for improved sensitivity. Similar performance to Stinger. Exported to multiple countries in Asia and Africa. Recognizable by distinctive grip stock and cylindrical launch tube.',
                'specifications' => json_encode([
                    'manufacturer' => 'CPMIEC',
                    'origin_country' => 'China',
                    'range_km' => 6,
                    'altitude_m' => 4000,
                    'speed_mach' => 2,
                    'weight_kg' => 18,
                    'guidance' => 'Cooled IR seeker',
                ]),
            ],
            [
                'designation' => 'Piorun',
                'nato_designation' => null,
                'common_name' => 'Piorun',
                'country_code' => 'PL',
                'category_slug' => 'manpads',
                'introduced_year' => 2019,
                'estimated_produced' => 3000,
                'description' => 'Polish MANPADS developed by Mesko S.A., based on Igla but with new seeker and proximity fuze. Name means "Thunderbolt" in Polish. Enhanced resistance to countermeasures. Supplied to Ukraine where it has proven highly effective.',
                'specifications' => json_encode([
                    'manufacturer' => 'Mesko S.A.',
                    'origin_country' => 'Poland',
                    'range_km' => 6.5,
                    'altitude_m' => 4000,
                    'speed_mach' => 2.2,
                    'weight_kg' => 16.5,
                    'warhead_kg' => 1.8,
                    'guidance' => 'Dual-band IR with proximity fuze',
                ]),
            ],

            // =============================================
            // CRUISE MISSILES (Additional)
            // =============================================
            [
                'designation' => 'BGM-109 Tomahawk Block V',
                'nato_designation' => null,
                'common_name' => 'Tomahawk',
                'country_code' => 'US',
                'category_slug' => 'cruise',
                'introduced_year' => 2021,
                'estimated_produced' => 4000,
                'description' => 'American long-range cruise missile developed by General Dynamics (now Raytheon). Block V adds maritime moving target capability. Ship and submarine-launched. Terrain-hugging flight profile with TERCOM/DSMAC guidance. Combat proven in Iraq, Yugoslavia, Afghanistan, Syria, Yemen.',
                'specifications' => json_encode([
                    'manufacturer' => 'Raytheon',
                    'origin_country' => 'United States',
                    'range_km' => 2500,
                    'speed' => 'Mach 0.74',
                    'length_m' => 5.56,
                    'diameter_mm' => 518,
                    'weight_kg' => 1590,
                    'warhead_kg' => 450,
                    'guidance' => 'INS + GPS + TERCOM + DSMAC + datalink',
                    'accuracy_m' => 5,
                    'unit_cost_usd' => 2000000,
                ]),
            ],
            [
                'designation' => 'AGM-86C CALCM',
                'nato_designation' => null,
                'common_name' => 'CALCM',
                'country_code' => 'US',
                'category_slug' => 'cruise',
                'introduced_year' => 1991,
                'estimated_produced' => 1700,
                'description' => 'Conventional Air-Launched Cruise Missile converted from nuclear ALCM. B-52 launched standoff weapon. GPS-aided guidance for precision strike. Used extensively in Desert Storm, Kosovo, and Iraq. Being replaced by JASSM.',
                'specifications' => json_encode([
                    'manufacturer' => 'Boeing',
                    'origin_country' => 'United States',
                    'range_km' => 1200,
                    'speed' => 'Mach 0.73',
                    'length_m' => 6.32,
                    'weight_kg' => 1950,
                    'warhead_kg' => 900,
                    'guidance' => 'INS + GPS + TERCOM',
                    'platform' => 'B-52H',
                ]),
            ],
            [
                'designation' => 'AGM-158B JASSM-ER',
                'nato_designation' => null,
                'common_name' => 'JASSM-ER',
                'country_code' => 'US',
                'category_slug' => 'cruise',
                'introduced_year' => 2014,
                'estimated_produced' => 2500,
                'description' => 'Joint Air-to-Surface Standoff Missile Extended Range by Lockheed Martin. Stealthy cruise missile with precision strike capability. Extended range variant with larger fuel tank. Equips F-15E, F-16, B-1B, B-52, F-35. Low observable airframe.',
                'specifications' => json_encode([
                    'manufacturer' => 'Lockheed Martin',
                    'origin_country' => 'United States',
                    'range_km' => 1000,
                    'speed' => 'High subsonic',
                    'length_m' => 4.27,
                    'weight_kg' => 1020,
                    'warhead_kg' => 450,
                    'guidance' => 'INS + GPS + IIR terminal',
                    'stealth' => true,
                    'unit_cost_usd' => 1400000,
                ]),
            ],
            [
                'designation' => '3M14 Kalibr',
                'nato_designation' => 'SS-N-30A Sagaris',
                'common_name' => 'Kalibr',
                'country_code' => 'RU',
                'category_slug' => 'cruise',
                'introduced_year' => 2012,
                'estimated_produced' => 2000,
                'description' => 'Russian ship and submarine-launched cruise missile family by NPO Novator. Land-attack variant used extensively against Ukraine. Features terrain-following flight and terminal supersonic sprint. Launched from Kilo-class submarines and Buyan-M corvettes in Caspian Sea strikes.',
                'specifications' => json_encode([
                    'manufacturer' => 'NPO Novator',
                    'origin_country' => 'Russia',
                    'range_km' => 2500,
                    'speed' => 'Mach 0.8 cruise / Mach 2.9 terminal',
                    'length_m' => 8.9,
                    'weight_kg' => 2300,
                    'warhead_kg' => 450,
                    'guidance' => 'INS + GLONASS + TERCOM + DSMAC',
                    'platforms' => ['Ships', 'Submarines', 'Coastal'],
                ]),
            ],
            [
                'designation' => 'Taurus KEPD 350',
                'nato_designation' => null,
                'common_name' => 'Taurus',
                'country_code' => 'DE',
                'category_slug' => 'cruise',
                'introduced_year' => 2005,
                'estimated_produced' => 600,
                'description' => 'German-Swedish air-launched cruise missile by Taurus Systems (MBDA/Saab). KEPD = Kinetic Energy Penetrator and Destroyer. Bunker-busting MEPHISTO warhead with tandem penetrator. Stealthy design with terrain-following. Used by Luftwaffe Tornados and Spanish EF-18.',
                'specifications' => json_encode([
                    'manufacturer' => 'Taurus Systems (MBDA/Saab)',
                    'origin_country' => 'Germany/Sweden',
                    'range_km' => 500,
                    'speed' => 'Mach 0.95',
                    'length_m' => 5.1,
                    'weight_kg' => 1400,
                    'warhead_kg' => 481,
                    'guidance' => 'INS + GPS + TERCOM + IIR',
                    'warhead_type' => 'MEPHISTO tandem penetrator',
                ]),
            ],

            // =============================================
            // BALLISTIC MISSILES (Additional)
            // =============================================
            [
                'designation' => 'DF-26',
                'nato_designation' => 'CSS-18',
                'common_name' => 'DF-26 Guam Killer',
                'country_code' => 'CN',
                'category_slug' => 'ballistic',
                'introduced_year' => 2018,
                'estimated_produced' => 200,
                'description' => 'Chinese intermediate-range ballistic missile capable of nuclear or conventional strikes. Dual capable against land and naval targets. Nicknamed "Guam Killer" for ability to strike US Pacific bases. Mobile road-launched from TEL. Maneuvering warhead for terminal accuracy.',
                'specifications' => json_encode([
                    'manufacturer' => 'CASIC',
                    'origin_country' => 'China',
                    'range_km' => 4000,
                    'speed_mach' => 18,
                    'length_m' => 14,
                    'weight_tonnes' => 20,
                    'warhead' => 'Nuclear or conventional (1800kg)',
                    'guidance' => 'INS + stellar + terminal radar',
                    'accuracy_cep_m' => 30,
                    'mobile' => true,
                ]),
            ],
            [
                'designation' => 'Shahab-3',
                'nato_designation' => null,
                'common_name' => 'Shahab-3',
                'country_code' => 'IR',
                'category_slug' => 'ballistic',
                'introduced_year' => 2003,
                'estimated_produced' => 300,
                'description' => 'Iranian medium-range ballistic missile based on North Korean Nodong-1. Liquid-fueled with road-mobile TEL. Multiple variants with improved accuracy. Theoretically capable of carrying nuclear warhead. Primary strategic deterrent of Iran.',
                'specifications' => json_encode([
                    'manufacturer' => 'Iran Aerospace Industries Organization',
                    'origin_country' => 'Iran',
                    'range_km' => 2000,
                    'speed_mach' => 10,
                    'length_m' => 16,
                    'weight_tonnes' => 16,
                    'warhead_kg' => 1000,
                    'guidance' => 'INS (improved variants GPS)',
                    'accuracy_cep_m' => 300,
                    'propellant' => 'Liquid',
                ]),
            ],
            [
                'designation' => 'Hyunmoo-4',
                'nato_designation' => null,
                'common_name' => 'Hyunmoo-4',
                'country_code' => 'KR',
                'category_slug' => 'ballistic',
                'introduced_year' => 2020,
                'estimated_produced' => 100,
                'description' => 'South Korean solid-fuel ballistic missile with 800km+ range. Developed after US lifted missile range restrictions. Features 2-ton conventional warhead. Part of Korea Massive Punishment and Retaliation strategy against North Korea.',
                'specifications' => json_encode([
                    'manufacturer' => 'ADD / LIG Nex1',
                    'origin_country' => 'South Korea',
                    'range_km' => 800,
                    'warhead_tonnes' => 2,
                    'guidance' => 'INS + GPS + terminal',
                    'propellant' => 'Solid',
                    'mobile' => true,
                ]),
            ],
            [
                'designation' => 'Agni-V',
                'nato_designation' => null,
                'common_name' => 'Agni-V',
                'country_code' => 'IN',
                'category_slug' => 'ballistic',
                'introduced_year' => 2018,
                'estimated_produced' => 50,
                'description' => 'Indian intercontinental ballistic missile by DRDO. Three-stage solid-fuel with canisterized road-mobile launch. Can reach all of China. MIRV capable. Part of India nuclear triad with submarine-launched variant under development.',
                'specifications' => json_encode([
                    'manufacturer' => 'DRDO',
                    'origin_country' => 'India',
                    'range_km' => 5500,
                    'speed_mach' => 24,
                    'length_m' => 17.5,
                    'weight_tonnes' => 50,
                    'warhead' => 'Nuclear (1500kg / MIRV capable)',
                    'guidance' => 'Ring laser gyro INS + GPS',
                    'accuracy_cep_m' => 80,
                    'propellant' => 'Solid (3 stage)',
                ]),
            ],
            [
                'designation' => 'RS-28 Sarmat',
                'nato_designation' => 'SS-X-30 Satan 2',
                'common_name' => 'Sarmat',
                'country_code' => 'RU',
                'category_slug' => 'ballistic',
                'introduced_year' => 2023,
                'estimated_produced' => 10,
                'description' => 'Russian heavy ICBM replacing R-36M Satan. Liquid-fueled silo-based. Can carry 10+ MIRVs or Avangard hypersonic glide vehicles. Range allows South Pole trajectory to evade US missile defenses. Heaviest ICBM currently deployed.',
                'specifications' => json_encode([
                    'manufacturer' => 'Makeyev Rocket Design Bureau',
                    'origin_country' => 'Russia',
                    'range_km' => 18000,
                    'speed_mach' => 20,
                    'length_m' => 35,
                    'weight_tonnes' => 208,
                    'throw_weight_tonnes' => 10,
                    'warheads' => '10-15 MIRVs or 3 Avangard HGV',
                    'propellant' => 'Liquid',
                    'guidance' => 'INS + stellar',
                ]),
            ],

            // =============================================
            // HYPERSONIC MISSILES
            // =============================================
            [
                'designation' => 'DF-17',
                'nato_designation' => null,
                'common_name' => 'DF-17',
                'country_code' => 'CN',
                'category_slug' => 'ballistic',
                'introduced_year' => 2019,
                'estimated_produced' => 100,
                'description' => 'Chinese medium-range ballistic missile with hypersonic glide vehicle (HGV) payload. First operational hypersonic weapon. DF-ZF HGV maneuvers at Mach 5-10 at lower altitude than traditional ballistic trajectory, complicating missile defense. Road-mobile launch.',
                'specifications' => json_encode([
                    'manufacturer' => 'CASIC',
                    'origin_country' => 'China',
                    'range_km' => 1800,
                    'speed_mach' => 10,
                    'payload' => 'DF-ZF hypersonic glide vehicle',
                    'guidance' => 'INS + terminal radar',
                    'maneuverable' => true,
                    'mobile' => true,
                ]),
            ],
            [
                'designation' => 'Avangard',
                'nato_designation' => null,
                'common_name' => 'Avangard',
                'country_code' => 'RU',
                'category_slug' => 'ballistic',
                'introduced_year' => 2019,
                'estimated_produced' => 20,
                'description' => 'Russian hypersonic glide vehicle carried by UR-100N or Sarmat ICBMs. Glides at Mach 20+ with unpredictable trajectory to defeat missile defenses. Nuclear capable. Declared operational by Russia in December 2019. Extreme maneuvering makes interception nearly impossible.',
                'specifications' => json_encode([
                    'manufacturer' => 'NPO Mashinostroyeniya',
                    'origin_country' => 'Russia',
                    'speed_mach' => 27,
                    'range_km' => 6000,
                    'carrier' => 'UR-100N UTTH or RS-28 Sarmat',
                    'warhead' => 'Nuclear',
                    'maneuverable' => true,
                    'hypersonic_glide_vehicle' => true,
                ]),
            ],
            [
                'designation' => 'AGM-183A ARRW',
                'nato_designation' => null,
                'common_name' => 'ARRW',
                'country_code' => 'US',
                'category_slug' => 'ballistic',
                'introduced_year' => 2024,
                'estimated_produced' => 50,
                'description' => 'Air-launched Rapid Response Weapon by Lockheed Martin. Air-breathing hypersonic boost-glide weapon for B-52H bombers. Reaches Mach 15-20. After multiple test failures, limited procurement planned. Complemented by HACM scramjet program.',
                'specifications' => json_encode([
                    'manufacturer' => 'Lockheed Martin',
                    'origin_country' => 'United States',
                    'range_km' => 1600,
                    'speed_mach' => 20,
                    'guidance' => 'INS + GPS',
                    'platform' => 'B-52H',
                    'boost_glide' => true,
                ]),
            ],
        ];

        foreach ($moreMissiles as $item) {
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

        $this->command->info('Additional missiles seeded (30 items)');

        // Now update existing equipment with enhanced descriptions
        $this->updateExistingDescriptions();
    }

    private function updateExistingDescriptions(): void
    {
        $updates = [
            // ========================================
            // TANKS
            // ========================================
            'T-90M Proryv' => 'Russia\'s most advanced operational main battle tank developed by Uralvagonzavod. Features Relikt explosive reactive armor, improved Sosna-U sighting system with thermal imaging, and soft-kill Shtora-1 countermeasures. Recognizable by welded turret with distinctive ERA configuration. Combat proven in Ukraine conflict. Crew protected in armored capsule. 125mm smoothbore gun with autoloader fires Refleks ATGMs.',

            'T-72B3' => 'Modernized Soviet-era tank by Uralvagonzavod representing bulk of Russian armored forces. Identifiable by Kontakt-5 ERA blocks on turret and hull. Upgraded fire control with Sosna-U sight. Workhorse of Russian operations in Ukraine with significant losses documented. Cost-effective upgrade of massive T-72 stockpiles. 125mm gun with autoloader.',

            'T-80BVM' => 'Russian gas turbine-powered main battle tank modernized from Cold War T-80B. Built by Omsktransmash. Distinctive by GTD-1250 gas turbine engine giving high power-to-weight ratio and distinctive whine. Features Relikt ERA providing protection against tandem warheads. Primarily deployed in northern regions due to cold-start capability. V-92S2F turbine allows operation in extreme cold without warm-up.',

            'T-14 Armata' => 'Russia\'s next-generation main battle tank by Uralvagonzavod featuring revolutionary unmanned turret design. Crew sits in armored capsule at hull front, separated from ammunition. Afghanit active protection system with radar to intercept incoming projectiles. 125mm 2A82-1M gun can also fire ATGMs. Limited production due to cost; not fielded in Ukraine despite claims.',

            'Leopard 2A7' => 'Germany\'s premier main battle tank by Krauss-Maffei Wegmann (KMW). Latest variant features enhanced mine protection, urban warfare kit, and programmable HE rounds. Identifiable by wedge-shaped turret addon armor. Rheinmetall 120mm L55A1 gun is most powerful Western tank gun. Exported to 18 nations. Considered among finest MBTs globally.',

            'M1A2 Abrams SEP v3' => 'America\'s primary main battle tank by General Dynamics Land Systems. SEP v3 (System Enhancement Package) adds improved thermal sights, datalinks, and auxiliary power unit. Distinctive turbine engine whine and flat turret profile. Chobham composite armor with depleted uranium mesh. 120mm M256 smoothbore gun. Battle-tested in Gulf War, Iraq, and with allies.',

            'Challenger 2' => 'British main battle tank by Vickers Defence Systems (now BAE). Only Western tank with rifled 120mm gun (L30A1) for accuracy. Dorchester composite armor among best protected. Distinctive flat turret profile with commander\'s thermal sight. 4-person crew. Single combat loss recorded. Being upgraded to Challenger 3 with Rheinmetall smoothbore gun.',

            'Leclerc' => 'French main battle tank by GIAT Industries (now Nexter). First Western MBT with autoloader reducing crew to 3. FINDERS battle management system for network-centric warfare. Modular armor allows theater-specific protection packages. CN120-26 120mm gun. Distinctive compact turret and angular profile. Exported to UAE with tropicalized modifications.',

            'Merkava Mk.4' => 'Israeli main battle tank by Israel Military Industries. Unique front-mounted engine provides crew protection and allows rear compartment for infantry or wounded. Trophy active protection system intercepts incoming ATGMs and RPGs with radar-guided countermeasures. 120mm MG253 gun. Optimized for urban warfare with overhead protection against ATGMs.',

            'Type 99A' => 'China\'s most advanced main battle tank by Norinco. Features 125mm ZPT-98 gun with autoloader, laser dazzler for disrupting targeting systems. Composite armor with ERA. Integrated battle management system. Recognizable by angular turret and ERA blocks. Estimated 600+ in service. Represents significant advancement over older Chinese designs.',

            'K2 Black Panther' => 'South Korean main battle tank by Hyundai Rotem. Features hydropneumatic suspension allowing hull positioning, automatic target tracking, and active protection system. 120mm L55 Rheinmetall gun with autoloader. One of world\'s most expensive MBTs but highly capable. Being exported to Poland in large numbers to replace Soviet-era tanks.',

            'Type 10' => 'Japanese main battle tank by Mitsubishi Heavy Industries. Designed for Japan\'s mountainous terrain with modular armor and reduced weight (48 tonnes). Features C4I system for networked warfare, hydropneumatic suspension. 120mm smoothbore gun with autoloader. Only 117 built due to cost. Highly maneuverable on narrow Japanese roads.',

            'Arjun Mk.2' => 'Indian main battle tank developed by DRDO\'s Combat Vehicles R&D Establishment. Features Kanchan composite armor, 120mm rifled gun with indigenous ammunition. Long development history since 1970s. Mk.2 adds improved fire control, laser warning system, and mine protection. Heavier than most MBTs at 68 tonnes. Limited production alongside Russian T-90S.',

            'Altay' => 'Turkish main battle tank by BMC based on South Korean K2 design. Features indigenous ASELSAN fire control and sensor systems. 120mm L55 Rheinmetall gun. MTU powerpack initially, later domestic engine planned. First Turkish indigenous MBT. Production began 2023 after extensive delays. Intended to replace aging M60 and Leopard 2A4 fleets.',

            'Stridsvagn 122' => 'Swedish variant of Leopard 2A5 by Krauss-Maffei Wegmann with enhanced protection. Features additional roof armor, improved mine protection, and French thermal sights. Barracuda camouflage reduces thermal and radar signature. 120mm L44 gun. 120 in Swedish service with 10 donated to Ukraine. Identifiable by angular turret addon armor.',

            'T-84 Oplot' => 'Ukrainian main battle tank developed by Kharkiv Morozov. Evolution of T-80UD with welded turret, Duplet ERA, and Varta APS. 125mm KBA-3 gun with autoloader. 1200hp diesel engine. Limited production due to complexity and cost. Mainly exported to Thailand. Used in limited numbers during Russian invasion.',

            // ========================================
            // INFANTRY FIGHTING VEHICLES
            // ========================================
            'BMP-3' => 'Russian infantry fighting vehicle by Kurganmashzavod. Unique armament of 100mm gun (fires ATGMs), 30mm autocannon, and 3 PKT machine guns. Amphibious with waterjets. Carries 7 infantry in cramped conditions. Distinctive low profile. Export success to UAE, South Korea, and others. Criticized for thin aluminum armor vulnerable to modern ATGMs.',

            'M2A3 Bradley' => 'American infantry fighting vehicle by BAE Systems. Carries 6 infantry with 25mm M242 Bushmaster autocannon and twin TOW missile launchers. Bradley Urban Survival Kit adds appliqué armor. Recognizable by boxy profile and turret. Combat proven in Gulf War, Iraq. M2A4 upgrade adds improved protection and electronics.',

            'Marder 1A5' => 'German infantry fighting vehicle by Rheinmetall. 20mm Rh202 autocannon in one-man turret. Carries 6 infantry. Mine protection package in A5 variant. Being replaced by Puma IFV. Donated to Ukraine. Recognizable by distinctive angled hull and turret design. Proven reliability over 50+ years of service.',

            'CV90' => 'Swedish infantry fighting vehicle family by BAE Systems Hägglunds. Modular design with multiple variants: IFV, anti-air, command, mortar carrier. 40mm Bofors autocannon in most variants. Exported to 7 nations. Mk IV variant has active protection and improved sensors. Recognizable by long hull with 7 road wheels.',

            'BTR-82A' => 'Russian 8x8 wheeled armored personnel carrier modernized from BTR-80. 30mm 2A72 autocannon replacing older 14.5mm KPVT. Carries 7 infantry. V-shape hull for mine protection. Amphibious without preparation. Extensively used in Syria and Ukraine. Vulnerable to modern ATGMs despite upgrades.',

            'BTR-4 Bucephalus' => 'Ukrainian 8x8 wheeled APC by Kharkiv Morozov. Modular design with multiple turret options including 30mm, ATGMs, or 120mm mortar. Carries 7 infantry. Combat proven defending Ukraine. Features modern fire control and battlefield management systems. Export success to Iraq, Indonesia, Nigeria.',

            'Namer' => 'Israeli heavy APC based on Merkava tank chassis by Israel Military Industries. Among most protected APCs globally with Trophy APS option. Front engine provides additional crew protection. Carries 9 infantry plus 3 crew. No armament in base version - relies on infantry dismounts. Used extensively in Gaza operations.',

            'M1126 Stryker' => 'American 8x8 wheeled armored vehicle by General Dynamics. Part of Stryker Brigade Combat Teams for rapid deployment. Multiple variants including Infantry Carrier (ICV), Mobile Gun System, and Reconnaissance. Air transportable by C-130. Add-on slat armor protects against RPGs. 105mm gun variant for fire support.',

            'M1296 Dragoon' => 'Stryker variant with 30mm XM813 autocannon by General Dynamics. First Stryker IFV with medium-caliber weapon. Unmanned turret reduces crew exposure. Airburst ammunition for defeating defilade targets. Deployed to Europe for enhanced firepower against Russian threats. Maintains Stryker mobility advantages.',

            'AMX-10 RC' => 'French 6x6 wheeled reconnaissance vehicle by GIAT Industries. 105mm gun on wheeled chassis provides tank-like firepower with high mobility. 300km range on roads. Amphibious capability. Used extensively in African interventions. Donated to Ukraine. Being replaced by EBRC Jaguar. Recognizable by large turret on compact hull.',

            // ========================================
            // SELF-PROPELLED ARTILLERY
            // ========================================
            '2S19 Msta-S' => 'Russian 152mm self-propelled howitzer on T-80 chassis by Uraltransmash. Autoloader enables 7-8 rounds/minute. 29km range with conventional rounds, 40km with RAP. Carries 50 rounds. Widely exported. Forms backbone of Russian artillery. Recognizable by large boxy turret on tank chassis.',

            'PzH 2000' => 'German 155mm self-propelled howitzer by Krauss-Maffei Wegmann. NATO\'s most capable tube artillery with 56km range using Vulcano guided rounds. Automated loading achieves burst rate of 10 rounds/minute. Navigation and fire control computers. Exported to 6 nations including Ukraine. Barrel life tracking system.',

            'AS-90' => 'British 155mm self-propelled howitzer by Vickers Shipbuilding (now BAE). L31 gun provides 24.7km range. Autonomous navigation and fire control. Burst rate of 3 rounds in 10 seconds. 179 in British service. Donated to Ukraine. Being replaced by Boxer RCH 155. Distinctive boxy turret on tracked chassis.',

            'CAESAR' => 'French 155mm truck-mounted howitzer by Nexter. 6x6 or 8x8 wheeled chassis provides shoot-and-scoot capability. 42km range. Crew of 5 can fire and displace in under 2 minutes. Combat proven in Afghanistan, Mali, Ukraine. Popular export with 8+ customers. Recognizable by gun mounted on truck bed.',

            'K9 Thunder' => 'South Korean 155mm self-propelled howitzer by Hanwha Defense. 40km range with base-bleed rounds. Automated fire control and loading. Burst rate of 6 rounds/minute. Export success to Turkey, Poland, Norway, Egypt, Australia. Licensed production in multiple countries. Proven reliability in Korean climate extremes.',

            'Archer' => 'Swedish fully automated 155mm howitzer by BAE Systems Hägglunds on Volvo truck chassis. Revolutionary automation allows 3-person crew with 21-round magazine. 50km range. Shoot-and-scoot in 30 seconds. Multiple rounds simultaneous impact capability. Being acquired by UK and Sweden. Unique enclosed armored cabin.',

            '2S35 Koalitsiya-SV' => 'Russian next-generation 152mm self-propelled howitzer with unmanned turret by Uraltransmash. Claimed 70km range and 16 rounds/minute rate of fire. Crew isolated in armored hull capsule. Limited production. Not confirmed in Ukraine combat. Represents significant advancement over 2S19 Msta-S.',

            'M270 MLRS' => 'American tracked multiple launch rocket system by Lockheed Martin. Carries 12 GMLRS rockets or 2 ATACMS missiles. 70km range with GMLRS, 300km with ATACMS. Armored cab protects crew. Operated by 13 nations. Combat proven since 1991 Gulf War. Complemented by wheeled HIMARS.',

            'BM-21 Grad' => 'Soviet/Russian 122mm multiple rocket launcher on Ural-375D truck by Splav. 40 tubes fire in 20 seconds covering 4 hectares. 20km range. Simple, reliable, cheap. Produced in massive numbers (9000+). Used globally in every conflict since 1960s. Recognizable by distinctive 40-tube launcher.',

            'BM-27 Uragan' => 'Soviet/Russian 220mm heavy multiple rocket launcher by Splav. 16 tubes with 35km range. Larger warheads than BM-21 for harder targets. Cluster munitions, thermobaric, and mine-laying rounds available. Used extensively in Ukraine by both sides. Recognizable by large rectangular launcher.',

            'BM-30 Smerch' => 'Soviet/Russian 300mm heavy multiple rocket launcher by Splav. 12 tubes with 90km range. Most powerful tube-launched rocket in Russian inventory. Sensor-fuzed munitions for armor targets. Forms basis of HIMARS competition. GPS-guided Tornado-S variant extends range to 120km.',

            'TOS-1A Solntsepyok' => 'Russian thermobaric multiple rocket launcher on T-72 tank chassis by Omsk Transmash. 24 220mm rockets with devastating fuel-air explosive warheads. 6km range limits use to breakthrough operations. Creates massive overpressure killing through concussion. Controversial weapon documented in Ukraine.',

            // ========================================
            // AIR DEFENSE SYSTEMS
            // ========================================
            'S-400 Triumf' => 'Russia\'s advanced long-range SAM system by Almaz-Antey. Multiple missile types engage targets from 2-400km range, 10-30km altitude. Distinctive quad-canister launchers on 5P85 TEL. 91N6E "Big Bird" acquisition radar and 92N6E "Grave Stone" engagement radar. Controversial exports to Turkey, India, China. Claimed effective against stealth aircraft.',

            'Buk-M3' => 'Russian medium-range SAM system by Almaz-Antey. 9M317M missiles in container-launchers with 70km range, 35km altitude. AESA radar for improved multi-target engagement. Each TELAR carries 6 missiles. Notorious for MH17 shootdown (Buk-M1). Widely exported. Primary Russian medium-range air defense.',

            '9K33 Osa' => 'Soviet/Russian short-range mobile SAM system. 6 missiles on amphibious TELAR with integrated radar. 15km range, 12km altitude. Simple and reliable system exported globally. Being replaced by Tor-M2. Still effective against helicopters and slow aircraft. Recognizable by box launcher and rotating radar.',

            'MIM-104 Patriot' => 'American long-range SAM system by Raytheon. PAC-3 variant uses hit-to-kill interceptors against ballistic missiles. 160km range against aircraft. Phased array radar tracks 100+ targets. Proven effective in Ukraine against Russian missiles. Operates with 18 nations. Each battery has 16 interceptors.',

            'IRIS-T SLM' => 'German medium-range SAM system by Diehl Defence. Uses IRIS-T air-to-air missiles adapted for ground launch. 40km range, 20km altitude. Highly maneuverable interceptor with imaging infrared seeker. 360° coverage. Delivered to Ukraine with high success rate against cruise missiles and drones.',

            'Iron Dome' => 'Israeli short-range air defense system by Rafael and Israel Aerospace Industries. Intercepts rockets, artillery, mortars using Tamir interceptors. Revolutionary cost-effective approach engaging only threatening projectiles. 90%+ intercept rate claimed. Battle-proven against thousands of Hamas rockets. Each battery has 3-4 launchers with 20 missiles each.',

            'David\'s Sling' => 'Israeli medium-range air defense system by Rafael and Raytheon. Stunner two-stage interceptor for cruise missiles, ballistic missiles, aircraft. Fills gap between Iron Dome and Arrow systems. Hit-to-kill technology. Fire control by Israeli Elta. Operational since 2017. Combat debut against Syrian missiles.',

            'Pantsir-S1' => 'Russian combined gun-missile short-range air defense by KBP. 12 57E6 missiles (20km range) and twin 2A38M 30mm guns (4km range). Designed to protect S-300/S-400 batteries from cruise missiles and PGMs. Documented failures in Syria and Ukraine. Wheeled or tracked variants. Export success despite limitations.',

            // ========================================
            // FIGHTER AIRCRAFT
            // ========================================
            'F-35A Lightning II' => 'Fifth-generation multirole stealth fighter by Lockheed Martin. Single-seat, single-engine with internal weapons bays. AN/APG-81 AESA radar, distributed aperture system provides 360° situational awareness. Recognizable by faceted fuselage and EOTS chin turret. Largest defense program in history with 15+ operator nations. CTOL variant for conventional runways.',

            'Su-35S' => 'Russian twin-engine super-maneuverable 4++ generation fighter by Sukhoi. Features thrust-vectoring AL-41F1S engines, Irbis-E passive electronically scanned array radar. Identifiable by canards (removed in production) and distinctive intake design. Heavy weapon load of 8000kg on 12 hardpoints. Air superiority role with multirole capability.',

            'Su-57' => 'Russian fifth-generation stealth multirole fighter by Sukhoi. Twin-engine with internal weapons bays and 3D thrust vectoring. Al-41F1 engines, N036 AESA radar. Only ~22 built due to costs. Limited combat use in Ukraine for standoff strikes. Recognizable by angular stealth shaping with distinctive LERX.',

            'F-22 Raptor' => 'American fifth-generation air superiority fighter by Lockheed Martin. First operational stealth fighter with supercruise capability. Twin F119 thrust-vectoring engines. AN/APG-77 AESA radar. 195 built before production ended. Export banned. Recognizable by distinctive trapezoidal wing planform and twin canted tails.',

            'F-16C/D Fighting Falcon' => 'American multirole fighter by General Dynamics/Lockheed Martin. Most numerous Western fighter with 4600+ built. Single F110/F100 engine, bubble canopy, blended wing-body. Combat proven in every major conflict since 1980s. Block 70/72 Viper variant remains competitive. Being transferred to Ukraine.',

            'Rafale' => 'French twin-engine multirole fighter by Dassault. Omnirole capability for air-to-air, strike, nuclear deterrence, and reconnaissance. SPECTRA electronic warfare suite, RBE2 AESA radar. Carrier-capable M variant. Export success to India, Qatar, Egypt, UAE, Greece, Indonesia. Recognizable by delta wing with canards.',

            'Eurofighter Typhoon' => 'European twin-engine multirole fighter by consortium of UK, Germany, Italy, Spain. Delta wing with canards, supercruise capable. Captor-E AESA radar in latest tranches. 680+ built for 9 nations. Strong export performer. Recognizable by distinctive delta-canard configuration with twin vertical tails.',

            'JAS 39 Gripen E' => 'Swedish single-engine multirole fighter by Saab. Designed for dispersed operations from roads. Latest E variant has new engine (F414), AESA radar, increased range. Cost-effective with low operating costs. Exported to 6 nations. Recognizable by delta-canard layout smaller than competitors.',

            'F-35I Adir' => 'Israeli variant of F-35A by Lockheed Martin with indigenous modifications. Israeli electronic warfare suite and command/control systems. Internal fuel tanks. First foreign F-35 operator. Combat proven against Iranian targets in Syria. "Adir" means "Mighty" in Hebrew. 50 in service with more ordered.',

            'J-20' => 'Chinese fifth-generation stealth fighter by Chengdu. Large twin-engine aircraft with canard-delta configuration. WS-10C engines being replaced by WS-15. Longest stealth fighter for fuel capacity. Side weapon bays for short-range missiles. Operational with ~200 in service. Intended to counter F-22/F-35.',

            'MiG-31BM' => 'Russian supersonic interceptor by Mikoyan. Modernized MiG-31 with Zaslon-M phased array radar (400km range). Fastest operational fighter at Mach 2.83. Primary platform for Kinzhal hypersonic missile. Four-aircraft formation can cover 800km front. Identifiable by large size and side-by-side crew configuration.',

            'FA-50' => 'South Korean light combat aircraft by Korea Aerospace Industries. Developed from T-50 Golden Eagle supersonic trainer. EL/M-2032 radar, AIM-9 Sidewinder capability. Cost-effective alternative for nations not requiring full fighters. Exported to Philippines, Iraq, Poland, Malaysia. Combat debut in Philippines against insurgents.',

            'F-2' => 'Japanese multirole fighter by Mitsubishi based on F-16. Enlarged wing, composite materials, J/APG-1 AESA radar (first operational fighter AESA). Primarily anti-ship role with 4 ASM-2 missiles. 94 built at high cost. Recognizable by larger wing and intake compared to F-16.',

            'HAL Tejas Mk.1' => 'Indian light combat aircraft by Hindustan Aeronautics. Indigenous delta-wing design with fly-by-wire. GE F404 engine, Israeli EL/M-2032 radar. Extended development since 1983. Mk.1A adds AESA radar and improved EW. Intended to replace aging MiG-21 fleet.',

            'TF Kaan' => 'Turkish fifth-generation stealth fighter under development by Turkish Aerospace Industries. Twin-engine with internal weapons bays. First flight December 2023. Intended to replace F-16 fleet. Development accelerated after F-35 program exclusion. Full operational capability expected late 2020s.',

            // ========================================
            // BOMBERS
            // ========================================
            'Tu-22M3' => 'Russian long-range supersonic bomber by Tupolev. Variable-geometry wings for supersonic dash. Kh-22/Kh-32 cruise missiles for maritime strike. Extensively used in Ukraine with Kh-22 missiles. 268 built during Soviet era, ~60 operational. Identifiable by swing-wing configuration and nose-mounted radar.',

            'B-2 Spirit' => 'American stealth strategic bomber by Northrop Grumman. Flying wing design virtually invisible to radar. Two internal weapon bays for 23,000kg payload including nuclear weapons. 21 built at $2 billion each. Can strike any target globally from CONUS. Identifiable by distinctive bat-wing shape.',

            'B-21 Raider' => 'American next-generation stealth bomber under development by Northrop Grumman. Flying wing continuing B-2 lineage with improved stealth and electronics. Nuclear and conventional capability. 100+ planned to replace B-1B and B-2. First flight 2023. Lower cost through open systems architecture.',

            // ========================================
            // HELICOPTERS
            // ========================================
            'Ka-52' => 'Russian tandem-seat attack helicopter by Kamov with signature coaxial contra-rotating rotors eliminating tail rotor. Crew seated side-by-side unlike conventional tandem. Vikhr ATGMs, rockets, 30mm cannon. Advanced radar in nose cone. Ejection seats rare for helicopters. Extensively used in Ukraine with documented losses to MANPADs.',

            'AH-64E Apache Guardian' => 'Premier American attack helicopter by Boeing. Tandem crew with pilot rear, copilot/gunner front. Distinctive Longbow millimeter-wave radar dome above rotor. 30mm M230 chain gun, Hellfire missiles, Hydra rockets. Integrated with UAS for unmanned teaming. Night vision and thermal imaging for all-weather operations. Combat proven globally.',

            'Mi-28N' => 'Russian tandem-seat attack helicopter by Mil Moscow Helicopter Plant. "Night Hunter" variant with night/all-weather capability. Ataka ATGMs, rockets, 30mm 2A42 cannon. Heavily armored cockpit. No ejection seats unlike Ka-52. Extensively used in Syria and Ukraine. Identifiable by distinctive rounded nose sensor turret.',

            'Mi-24P' => 'Russian heavy attack helicopter by Mil that uniquely can transport 8 troops. GSh-30K 30mm cannon on fixed mount (P variant). Ataka ATGMs, rockets. Nicknamed "Flying Tank" for armor protection. Most-produced attack helicopter globally. Combat veteran of Soviet-Afghan War, Chechnya, Syria, Ukraine.',

            'Mi-8/17' => 'Russian medium transport helicopter by Mil, most-produced helicopter in history (17,000+). Hip variants serve utility, assault, medevac, and gunship roles. Carries 24 troops or 4000kg cargo. Twin turboshafts provide redundancy. Used by 80+ countries. Recognizable by distinctive round nose and tail configuration.',

            'UH-60M Black Hawk' => 'American medium utility helicopter by Sikorsky. Workhorse of US military transporting 14 troops or external cargo. Twin T700 turboshafts. Multiple variants: medevac, special ops, naval. 4000+ built for 30+ countries. Combat proven in every US operation since 1979. Distinctive low-mounted horizontal tail.',

            'CH-47F Chinook' => 'American heavy-lift helicopter by Boeing with distinctive tandem rotor configuration. Carries 55 troops or 12,700kg cargo including vehicles. Rear loading ramp. Fastest conventional helicopter at 315km/h. 1200+ built serving 20+ countries. Essential for vertical envelopment and logistics. In production since 1962.',

            'T129 ATAK' => 'Turkish attack helicopter by Turkish Aerospace Industries based on AgustaWestland A129 Mangusta. 20mm cannon, ATGMs, rockets. Turkish avionics and weapons integration. Combat proven against PKK. Export success despite engine supply issues (US-made LHTEC CTS800). Recognizable by slim fuselage and stepped tandem cockpit.',

            'NH90' => 'European medium helicopter by NHIndustries (Airbus, Leonardo, Fokker). Tactical (TTH) and naval (NFH) variants. All-composite airframe, fly-by-wire. Carries 20 troops. Produced for 14 nations. Troubled development history. NFH variant carries torpedoes for ASW. Distinctive angular fuselage design.',

            'Tigre' => 'Franco-German attack helicopter by Eurocopter (now Airbus Helicopters). Tandem crew, stepped cockpit. 30mm cannon, ATGMs, rockets. All-weather capable with TADS/PNVS equivalent. Combat proven in Afghanistan, Libya, Mali. Three variants: anti-tank, escort, reconnaissance. 180+ built for France, Germany, Spain, Australia.',

            // ========================================
            // UAVs
            // ========================================
            'Bayraktar TB2' => 'Turkish medium-altitude long-endurance UAV by Baykar. Game-changing weapon in Nagorno-Karabakh, Libya, and Ukraine. 27-hour endurance, 8200m ceiling. Carries 4 MAM-L/MAM-C precision munitions. Satellite link for beyond-line-of-sight. Low cost compared to Western equivalents. 500+ produced for 25+ countries.',

            'Akinci' => 'Turkish high-altitude long-endurance UCAV by Baykar. Twin-engine with 24-hour endurance, 12,200m ceiling. Carries 1350kg of weapons including cruise missiles and SOM-J. AESA radar option for air-to-air capability. Represents significant capability jump from TB2. AI-assisted autonomous functions.',

            'MQ-9 Reaper' => 'American MALE UCAV by General Atomics. Primary US strike drone with 27-hour endurance. Carries 1700kg on 7 hardpoints including Hellfire, Paveway, JDAM. Turboprop for 400km/h speed. Extensive combat record in Middle East and Africa. Being replaced by MQ-9B SkyGuardian with improved sensors.',

            'Orlan-10' => 'Russian reconnaissance UAV by Special Technology Center. Catapult-launched with 16-hour endurance. Primary Russian artillery spotting platform with huge losses in Ukraine (1000+). Remarkably uses commercial components including Canon camera. Simple design enables mass production. 3000+ built.',

            'Lancet' => 'Russian loitering munition by ZALA Aero (Kalashnikov). Kamikaze drone with 40-minute endurance, 70km range. Targets identified by separate Orlan-10 scouts. 3kg or 5kg warhead variants. Extensively used against Ukrainian artillery and vehicles. TV or thermal seeker. Cheap and effective despite simple design.',

            // ========================================
            // NAVAL VESSELS
            // ========================================
            'Gerald R. Ford-class' => 'American supercarrier by Huntington Ingalls Industries. Newest and largest US carrier class at 100,000 tonnes. EMALS electromagnetic catapults replace steam. Advanced arresting gear, improved reactor design. 75+ aircraft capacity. $13 billion cost per hull. CVN-78 commissioned 2017, CVN-79 Kennedy building.',

            'Nimitz-class' => 'American nuclear supercarrier by Newport News Shipbuilding. Ten ships forming backbone of US carrier force. 100,000 tonnes carrying 85+ aircraft. Steam catapults, four elevators. Two A4W reactors provide unlimited range. Combat proven since 1970s. Being replaced by Ford-class at one ship per decade.',

            'Arleigh Burke-class' => 'American guided-missile destroyer by Bath Iron Works and Huntington Ingalls. Primary US surface combatant with 73+ built in three flights. Aegis combat system with SPY-1D radar. 96 VLS cells for SM-2/SM-6, Tomahawk, ESSM. Flight III adds SPY-6 radar. Most numerous Western surface combatant class.',

            'Zumwalt-class' => 'American stealth guided-missile destroyer by Bath Iron Works. Tumblehome hull design for reduced radar signature. Originally 32 planned, cut to 3 due to costs. 80 VLS cells. 155mm Advanced Gun System ammunition too expensive. Now being converted for hypersonic weapons. Distinctive angular profile.',

            'Constellation-class' => 'American guided-missile frigate under construction by Fincantieri. Based on FREMM design for multi-mission warfare. 32 VLS cells, Enterprise Air Surveillance Radar. 20 planned to replace LCS and expand fleet. First hull expected 2029 after delays. Cost-effective alternative to destroyers.',

            'Virginia-class' => 'American nuclear attack submarine by General Dynamics and Huntington Ingalls. 23 boats commissioned with 66 planned. Modular design with Virginia Payload Module adding 28 Tomahawk tubes. Advanced sonar and combat systems. Replaces Los Angeles-class. Block V variant significantly larger.',

            'Type 055' => 'Chinese guided-missile destroyer by Jiangnan and Dalian shipyards. Among largest and most powerful surface combatants globally. 112 VLS cells for HHQ-9, YJ-18, land-attack missiles. 13,000 tonnes with integrated electric propulsion. Dual-band radar. 8 commissioned with more building. Rivals Arleigh Burke Flight III.',

            'Liaoning' => 'Chinese aircraft carrier, former Soviet Varyag completed by China. STOBAR configuration with ski-jump. Carries J-15 Flying Shark fighters. 60,000 tonnes. Primarily training vessel for growing Chinese carrier force. Commissioned 2012. Being supplemented by larger domestically-built Shandong and Fujian.',

            'Admiral Kuznetsov' => 'Russian aircraft carrier, only Russian carrier in service. STOBAR with ski-jump. Carries Su-33 and MiG-29K fighters. 55,000 tonnes. Troubled service history with frequent breakdowns. Damaged by fire during 2018 refit. Smoke-belching propulsion system distinctive. Unlikely to return to full service.',

            'Admiral Gorshkov-class' => 'Russian guided-missile frigate by Severnaya Verf shipyard. Modern design with stealth features. 32 VLS cells for Kalibr, Oniks, and Zircon hypersonic missiles. Poliment-Redut SAM system. 4 in service with more building. Represents significant Russian naval capability.',

            'Borei-class' => 'Russian nuclear ballistic missile submarine by Sevmash. New-generation SSBN carrying 16 Bulava SLBMs. 24,000 tonnes submerged. Pump-jet propulsion for reduced noise. 5 in service with 5 more building. Forms backbone of Russian naval nuclear deterrent. Replaces aging Typhoon and Delta classes.',

            'Yasen-class' => 'Russian nuclear cruise missile submarine by Sevmash. 13,800 tonnes with 32 cruise missiles (Kalibr, Oniks, Zircon). Advanced sonar and quieting. Most capable Russian attack submarine. Only 3 in service due to cost and complexity. Improved Yasen-M variant addresses early issues.',

            'Queen Elizabeth-class' => 'British aircraft carrier by Aircraft Carrier Alliance. Largest Royal Navy warships at 65,000 tonnes. STOVL configuration for F-35B. Unique twin island design. No catapults limits aircraft options. Two ships: HMS Queen Elizabeth (2017) and HMS Prince of Wales (2019). Ski-jump distinguishes from US carriers.',

            'Type 45' => 'British air defense destroyer by BAE Systems. PAAMS missile system with Aster 15/30. SAMPSON multifunction radar. 6 ships in service. Propulsion problems led to fleet-wide modifications. Distinctive angular pyramid mast. Among most capable air defense destroyers. To receive hypersonic missiles.',

            'Type 26' => 'British anti-submarine frigate under construction by BAE Systems. Global Combat Ship design also ordered by Australia and Canada. 24 VLS cells, Mk41 launcher. Advanced sonar suite for ASW. 8 planned for Royal Navy with first commissioning delayed to 2027. Replaces Type 23.',

            'Astute-class' => 'British nuclear attack submarine by BAE Systems. 7,400 tonnes with Tomahawk and Spearfish torpedoes. Sonar 2076 suite among world\'s best. PWR2 reactor provides 25-year core life. 5 commissioned with 7 planned. Troubled early construction led to cost overruns. Most capable Royal Navy submarines.',

            'Charles de Gaulle' => 'French nuclear aircraft carrier, only non-US nuclear carrier. CATOBAR with steam catapults for Rafale M fighters. 42,500 tonnes. Unique French nuclear propulsion. Single ship limits availability. Combat proven in Afghanistan, Libya, Syria, ISIS. Second carrier (PANG) planned for 2038.',

            'FREMM' => 'French/Italian multi-mission frigate by Naval Group and Fincantieri. 32 VLS cells, Aster missiles, SCALP Naval. 6,000 tonnes with diesel-electric and gas turbine propulsion. 8 French, 10 Italian variants. Basis for US Constellation-class. Combat proven with French Navy.',

            'Barracuda-class' => 'French nuclear attack submarine by Naval Group. Replacing Rubis-class with 5,300-tonne design. Carries SCALP Naval cruise missiles and F21 torpedoes. Pump-jet propulsion. 2 commissioned with 6 planned. More capable than predecessor with land-attack capability.',

            'Izumo-class' => 'Japanese helicopter destroyer by Japan Marine United. Being converted to light carrier for F-35B. 27,000 tonnes, largest Japanese warship since WWII. Originally for ASW helicopters. Conversion requires heat-resistant deck coating and aircraft handling modifications.',

            'INS Vikrant' => 'Indian aircraft carrier by Cochin Shipyard. First indigenous Indian carrier at 45,000 tonnes. STOBAR with ski-jump for MiG-29K fighters. Commissioned 2022 after extended construction. Complements Russian-built Vikramaditya. Establishes Indian carrier construction capability.',

            // ========================================
            // MISSILES
            // ========================================
            '3M22 Zircon' => 'Russian hypersonic anti-ship cruise missile by NPO Mashinostroyeniya. Mach 8-9 speed with 500-1000km range makes interception extremely difficult. Launched from ships (Admiral Gorshkov) and submarines (Yasen). Operational since 2023. First use in Ukraine January 2023. May carry nuclear warhead.',

            '9M133 Kornet' => 'Russian anti-tank guided missile by KBP. Laser beam-riding with tandem HEAT warhead penetrating 1200mm RHA behind ERA. 5.5km range. Man-portable or vehicle mounted. Widely exported and extensively used by various factions. Thermobaric variant for bunkers. Effective against modern tanks.',

            'FGM-148 Javelin' => 'American fire-and-forget ATGM by Raytheon and Lockheed Martin. Infrared imaging seeker with soft-launch and top-attack mode. Penetrates 800mm RHA. Revolutionary weapon against Russian armor in Ukraine. 4.75km range. Man-portable at 22.3kg. 50,000+ produced. $240,000 per missile.',

            'FIM-92 Stinger' => 'American shoulder-launched SAM by General Dynamics/Raytheon. Infrared homing with IFF capability. Lethal against helicopters and low-flying aircraft to 3.8km range. Changed Afghan war against Soviet helicopters. Supplied in thousands to Ukraine. Still effective 40+ years after introduction.',

            'Storm Shadow' => 'British/French air-launched cruise missile by MBDA. 560km range with stealth profile and terrain-following. GPS/INS/terrain reference navigation. BROACH tandem warhead for hardened targets. Combat proven in Iraq, Libya, Syria. Supplied to Ukraine for strikes on Russian targets. Known as SCALP-EG in French service.',

            'SCALP-EG' => 'French designation of Storm Shadow cruise missile by MBDA. Air-launched from Rafale, Mirage 2000, Tornado. 560km range optimized for deep strike. BROACH warhead penetrates hardened shelters. Combat proven in multiple French operations. Integration with additional platforms ongoing.',

            'Spike ATGM' => 'Israeli anti-tank missile family by Rafael. Multiple variants: SR (short-range), MR (medium 2.5km), LR (4km), ER (8km), NLOS (25km). Fire-and-forget with electro-optical seeker. Fiber-optic data link on longer-range variants. 33,000+ produced for 35+ nations. Man-portable to vehicle/helicopter mounted.',

            'DF-21D' => 'Chinese anti-ship ballistic missile by CASIC. First ASBM designed to target carriers at 1500km range. Terminal maneuvering to evade defenses. Over-the-horizon radar targeting with satellite support. "Carrier killer" designation. Deployed since 2010. Accuracy unverified in combat.',

            'BrahMos' => 'Indian/Russian supersonic cruise missile by BrahMos Aerospace joint venture. Mach 3 speed making it fastest cruise missile in service. 450km range (extended versions longer). Launched from ships, submarines, aircraft, land. 500+ produced. NG hypersonic version under development.',

            'MGM-140 ATACMS' => 'American tactical ballistic missile by Lockheed Martin for MLRS/HIMARS. 300km range with GPS/INS guidance. Block IA has 950 M74 bomblets, later versions use unitary warhead. 3700+ produced. Game-changer when supplied to Ukraine for deep strikes. Being replaced by PrSM.',

            'PrSM' => 'Precision Strike Missile by Lockheed Martin replacing ATACMS. 500km+ range in compact form fitting 2 per HIMARS pod. GPS guidance with advanced seeker options. Designed to defeat modern air defenses. Initial operational capability 2024. Represents significant US long-range strike improvement.',

            'Iskander-M' => 'Russian short-range ballistic missile system by KBP. 9M723 quasi-ballistic missile with 500km range, 5m CEP. Maneuvering trajectory defeats missile defenses. Nuclear or conventional warheads. Extensively used against Ukraine with mixed accuracy. Brigade-level weapon replacing Tochka-U.',

            'Kh-101' => 'Russian air-launched cruise missile by Raduga. 4500km range making it longest-range Russian cruise missile. Low-observable design reduces radar signature. Conventional 400kg warhead or Kh-102 nuclear variant. Launched from Tu-160, Tu-95MS bombers. Extensively used against Ukraine with terrain-following flight.',

            'Kh-47M2 Kinzhal' => 'Russian air-launched hypersonic missile, essentially air-launched Iskander. Mach 10+ speed with 2000km range from MiG-31K or Tu-22M3. Maneuvering makes interception difficult. Claims of Patriot intercept disputed. Nuclear capable. First use in Ukraine March 2022 though effectiveness debated.',

            'Starstreak' => 'British high-velocity SAM by Thales. Unique design with 3 dart sub-munitions traveling at Mach 4. Laser beam-riding guidance immune to countermeasures. 7km range. Man-portable, vehicle-mounted, or helicopter-launched. Supplied to Ukraine with successful helicopter kills. Fastest short-range SAM.',

            'M142 HIMARS' => 'High Mobility Artillery Rocket System by Lockheed Martin on 5-ton truck chassis. Carries 6 GMLRS rockets (70km range) or 1 ATACMS missile (300km range). Game-changing weapon in Ukraine conflict for precision deep strikes. C-130 transportable for rapid deployment. Automated fire control with GPS-guided munitions.',

            // ========================================
            // TRANSPORT AIRCRAFT
            // ========================================
            'A400M Atlas' => 'European four-engine turboprop military transport by Airbus Defence. 37,000kg payload, 8700km range. Bridges gap between C-130 and C-17. TP400 engines are most powerful turboprops ever. Can operate from short unpaved runways. 110+ delivered to 8 nations. Troubled development history with delays and cost overruns.',
        ];

        foreach ($updates as $designation => $description) {
            DB::table('military_equipment')
                ->where('designation', $designation)
                ->update(['description' => $description, 'updated_at' => now()]);
        }

        $this->command->info('Updated ' . count($updates) . ' existing equipment descriptions');
    }
}
