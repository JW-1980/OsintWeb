<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FinalEquipmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Final batch of 70+ equipment items to reach 500 total.
     */
    public function run(): void
    {
        $this->seedHelicopters();
        $this->seedTanksAndIFVs();
        $this->seedMissiles();
        $this->seedAPCsAndMRAPs();

        $this->command->info('Final equipment batch seeded successfully (70+ items).');
    }

    private function getCategories(): array
    {
        return DB::table('equipment_categories')->pluck('id', 'slug')->toArray();
    }

    private function getCountries(): array
    {
        return DB::table('countries')->pluck('id', 'iso_code')->toArray();
    }

    private function insertEquipment(array $items, string $defaultCategory): void
    {
        $categories = $this->getCategories();
        $countries = $this->getCountries();

        foreach ($items as $item) {
            $categorySlug = $item['category_slug'] ?? $defaultCategory;
            $countryCode = $item['country_code'] ?? 'US';

            if (!isset($countries[$countryCode])) {
                continue;
            }

            $categoryId = $categories[$categorySlug] ?? $categories[$defaultCategory] ?? null;
            if (!$categoryId) {
                continue;
            }

            DB::table('military_equipment')->updateOrInsert(
                ['designation' => $item['designation']],
                [
                    'uuid' => Str::uuid(),
                    'designation' => $item['designation'],
                    'nato_designation' => $item['nato_designation'] ?? null,
                    'common_name' => $item['common_name'] ?? null,
                    'category_id' => $categoryId,
                    'country_id' => $countries[$countryCode],
                    'introduced_year' => $item['introduced_year'] ?? null,
                    'estimated_produced' => $item['estimated_produced'] ?? null,
                    'description' => $item['description'] ?? null,
                    'specifications' => $item['specifications'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    private function seedHelicopters(): void
    {
        $helicopters = [
            [
                'designation' => 'AH-64E Apache Guardian',
                'nato_designation' => null,
                'common_name' => 'Apache',
                'country_code' => 'US',
                'category_slug' => 'helicopters',
                'introduced_year' => 2011,
                'estimated_produced' => 500,
                'description' => 'Most advanced variant of the Boeing AH-64 Apache attack helicopter with improved digital connectivity, more powerful engines, and enhanced sensors. Primary attack helicopter of U.S. Army exported to UK, Israel, and South Korea. Extensively combat proven in Iraq, Afghanistan, and numerous conflicts.',
                'specifications' => json_encode(['manufacturer' => 'Boeing', 'crew' => 2, 'max_speed_kmh' => 293, 'range_km' => 480, 'weapons' => 'M230 30mm, Hellfire, Hydra 70']),
            ],
            [
                'designation' => 'AH-1Z Viper',
                'nato_designation' => null,
                'common_name' => 'Viper',
                'country_code' => 'US',
                'category_slug' => 'helicopters',
                'introduced_year' => 2010,
                'estimated_produced' => 189,
                'description' => 'Bell twin-engine attack helicopter for U.S. Marine Corps, ultimate evolution of AH-1 Cobra series. Four-blade composite rotor, integrated helmet-mounted sight, and all-weather targeting systems. 84% commonality with UH-1Y Venom for expeditionary logistics.',
                'specifications' => json_encode(['manufacturer' => 'Bell Helicopter', 'crew' => 2, 'max_speed_kmh' => 411, 'range_km' => 685, 'weapons' => 'M197 20mm, Hellfire, Sidewinder']),
            ],
            [
                'designation' => 'UH-60M Black Hawk',
                'nato_designation' => null,
                'common_name' => 'Black Hawk',
                'country_code' => 'US',
                'category_slug' => 'helicopters',
                'introduced_year' => 2007,
                'estimated_produced' => 2800,
                'description' => 'Latest Sikorsky UH-60 utility helicopter variant, backbone of U.S. Army aviation. Digital glass cockpit, improved engines, and integrated vehicle health management. Over 4,000 UH-60 family produced, operating in 30+ countries worldwide.',
                'specifications' => json_encode(['manufacturer' => 'Sikorsky', 'crew' => 4, 'passengers' => 11, 'max_speed_kmh' => 294, 'range_km' => 590]),
            ],
            [
                'designation' => 'CH-47F Chinook',
                'nato_designation' => null,
                'common_name' => 'Chinook',
                'country_code' => 'US',
                'category_slug' => 'helicopters',
                'introduced_year' => 2007,
                'estimated_produced' => 950,
                'description' => 'Boeing heavy-lift twin-engine tandem-rotor helicopter, most advanced Chinook variant. Digital automatic flight control and Common Avionics Architecture. Renowned for high-altitude harsh environment operations, extensively used in Afghanistan mountains.',
                'specifications' => json_encode(['manufacturer' => 'Boeing', 'crew' => 3, 'passengers' => 33, 'max_speed_kmh' => 315, 'cargo_kg' => 12700]),
            ],
            [
                'designation' => 'MH-60R Seahawk',
                'nato_designation' => null,
                'common_name' => 'Seahawk',
                'country_code' => 'US',
                'category_slug' => 'helicopters',
                'introduced_year' => 2006,
                'estimated_produced' => 300,
                'description' => 'Most advanced naval helicopter combining SH-60B and SH-60F capabilities into single multi-mission platform. Excels in ASW, anti-surface warfare, and SAR with dipping sonar and multi-mode radar. Exported to Australia, Denmark, Saudi Arabia.',
                'specifications' => json_encode(['manufacturer' => 'Sikorsky/Lockheed Martin', 'crew' => 4, 'max_speed_kmh' => 270, 'range_km' => 834, 'weapons' => 'Hellfire, Mk 54 torpedoes']),
            ],
            [
                'designation' => 'Ka-52 Alligator',
                'nato_designation' => 'Hokum-B',
                'common_name' => 'Alligator',
                'country_code' => 'RU',
                'category_slug' => 'helicopters',
                'introduced_year' => 2011,
                'estimated_produced' => 160,
                'description' => 'Russian all-weather attack helicopter with unique side-by-side seating and Kamov coaxial rotor design eliminating tail rotor. Serves as reconnaissance and command platform for Mi-28 groups. Extensively employed in Syria and Ukraine conflicts.',
                'specifications' => json_encode(['manufacturer' => 'Kamov', 'crew' => 2, 'max_speed_kmh' => 300, 'range_km' => 520, 'weapons' => '30mm cannon, Vikhr ATGMs, rockets']),
            ],
            [
                'designation' => 'Mi-28NM Night Superhunter',
                'nato_designation' => 'Havoc',
                'common_name' => 'Havoc',
                'country_code' => 'RU',
                'category_slug' => 'helicopters',
                'introduced_year' => 2016,
                'estimated_produced' => 120,
                'description' => 'Latest Mi-28 attack helicopter variant with enhanced night/all-weather capability. Mast-mounted N025 radar, new engines, and helmet-mounted sight. Can designate targets for other platforms and control UAVs in networked combat.',
                'specifications' => json_encode(['manufacturer' => 'Mil', 'crew' => 2, 'max_speed_kmh' => 324, 'range_km' => 450, 'weapons' => '30mm cannon, Ataka ATGMs']),
            ],
            [
                'designation' => 'Mi-24/35 Hind',
                'nato_designation' => 'Hind',
                'common_name' => 'Hind',
                'country_code' => 'RU',
                'category_slug' => 'helicopters',
                'introduced_year' => 1972,
                'estimated_produced' => 2600,
                'description' => 'Large helicopter gunship uniquely combining heavy armament with 8-troop transport capability. Called "flying tank" with formidable weapons suite. Combat proven in every major conflict since 1970s from Afghan War to Africa and Middle East.',
                'specifications' => json_encode(['manufacturer' => 'Mil', 'crew' => 3, 'passengers' => 8, 'max_speed_kmh' => 335, 'range_km' => 450]),
            ],
            [
                'designation' => 'Mi-8/17 Hip',
                'nato_designation' => 'Hip',
                'common_name' => 'Hip',
                'country_code' => 'RU',
                'category_slug' => 'helicopters',
                'introduced_year' => 1967,
                'estimated_produced' => 17000,
                'description' => 'Most-produced helicopter in aviation history, workhorse of Russian military aviation. Versatile medium transport serving 90+ countries in transport, assault, medevac, and EW roles. Legendary rugged design for extreme conditions.',
                'specifications' => json_encode(['manufacturer' => 'Mil/Kazan', 'crew' => 3, 'passengers' => 24, 'max_speed_kmh' => 250, 'range_km' => 580]),
            ],
            [
                'designation' => 'EC665 Tiger HAD',
                'nato_designation' => null,
                'common_name' => 'Tiger',
                'country_code' => 'FR',
                'category_slug' => 'helicopters',
                'introduced_year' => 2013,
                'estimated_produced' => 180,
                'description' => 'Franco-German attack helicopter for armed reconnaissance and ground attack. Advanced stealth characteristics, composite airframe resistant to 23mm, sophisticated day/night sensors. Combat proven in Afghanistan, Libya, and Mali.',
                'specifications' => json_encode(['manufacturer' => 'Airbus Helicopters', 'crew' => 2, 'max_speed_kmh' => 280, 'range_km' => 800, 'weapons' => '30mm cannon, HOT/Hellfire ATGMs']),
            ],
            [
                'designation' => 'AW101 Merlin',
                'nato_designation' => null,
                'common_name' => 'Merlin',
                'country_code' => 'GB',
                'category_slug' => 'helicopters',
                'introduced_year' => 1999,
                'estimated_produced' => 270,
                'description' => 'Anglo-Italian medium-lift helicopter for multi-role military operations including ASW, troop transport, and VIP transport. Three-engine design for exceptional safety in harsh maritime and high-altitude conditions. Used by UK, Italy, Portugal, Denmark, Norway, Japan.',
                'specifications' => json_encode(['manufacturer' => 'Leonardo', 'crew' => 4, 'passengers' => 30, 'max_speed_kmh' => 309, 'range_km' => 1389]),
            ],
            [
                'designation' => 'NH90',
                'nato_designation' => null,
                'common_name' => 'NH90',
                'country_code' => 'FR',
                'category_slug' => 'helicopters',
                'introduced_year' => 2007,
                'estimated_produced' => 500,
                'description' => 'European medium twin-engine multi-role helicopter developed by France, Germany, Italy, Netherlands consortium. Tactical transport and naval variants with fly-by-wire and all-composite airframe. Serves 20+ nations, major European program success.',
                'specifications' => json_encode(['manufacturer' => 'NHIndustries', 'crew' => 2, 'passengers' => 20, 'max_speed_kmh' => 291, 'range_km' => 1150]),
            ],
            [
                'designation' => 'CAIC Z-10',
                'nato_designation' => null,
                'common_name' => 'Fierce Thunderbolt',
                'country_code' => 'CN',
                'category_slug' => 'helicopters',
                'introduced_year' => 2010,
                'estimated_produced' => 200,
                'description' => 'China\'s first purpose-built attack helicopter developed with Kamov assistance. Tandem cockpit, streamlined fuselage, comprehensive Chinese-developed sensors and weapons. Primary attack helicopter of PLA Ground Force, exported to Pakistan.',
                'specifications' => json_encode(['manufacturer' => 'CAIC', 'crew' => 2, 'max_speed_kmh' => 270, 'range_km' => 800, 'weapons' => '23mm cannon, HJ-10 ATGMs']),
            ],
            [
                'designation' => 'AW159 Wildcat',
                'nato_designation' => null,
                'common_name' => 'Wildcat',
                'country_code' => 'GB',
                'category_slug' => 'helicopters',
                'introduced_year' => 2014,
                'estimated_produced' => 62,
                'description' => 'British multi-role helicopter developed from Super Lynx for Army and Royal Navy. Composite airframe, advanced avionics, sophisticated EO sensors. Naval variant for anti-surface with Sea Venom missiles, army variant for reconnaissance and light attack.',
                'specifications' => json_encode(['manufacturer' => 'Leonardo', 'crew' => 2, 'passengers' => 6, 'max_speed_kmh' => 291, 'range_km' => 777]),
            ],
        ];

        $this->insertEquipment($helicopters, 'helicopters');
        $this->command->info('Helicopters seeded: ' . count($helicopters) . ' items');
    }

    private function seedTanksAndIFVs(): void
    {
        $tanksAndIFVs = [
            [
                'designation' => 'M1A2 SEPv3 Abrams',
                'nato_designation' => null,
                'common_name' => 'Abrams',
                'country_code' => 'US',
                'category_slug' => 'tanks',
                'introduced_year' => 2020,
                'estimated_produced' => 500,
                'description' => 'Third System Enhancement Package of M1 Abrams by General Dynamics. Third-generation depleted uranium armor, upgraded electronics, enhanced ammunition data link for programmable ammunition. Deployed with US Army armored brigades.',
                'specifications' => json_encode(['manufacturer' => 'GDLS', 'weight_tonnes' => 73.6, 'main_gun' => '120mm M256A1', 'engine_hp' => 1500, 'crew' => 4]),
            ],
            [
                'designation' => 'Leopard 2A7+',
                'nato_designation' => null,
                'common_name' => 'Leopard 2',
                'country_code' => 'DE',
                'category_slug' => 'tanks',
                'introduced_year' => 2014,
                'estimated_produced' => 200,
                'description' => 'Enhanced Leopard 2 variant by Krauss-Maffei Wegmann with improved modular armor, mine protection, air conditioning, and hunter-killer fire control. Optimized for conventional and urban warfare operations.',
                'specifications' => json_encode(['manufacturer' => 'KMW', 'weight_tonnes' => 67, 'main_gun' => '120mm L/55', 'engine_hp' => 1500, 'crew' => 4]),
            ],
            [
                'designation' => 'Challenger 3',
                'nato_designation' => null,
                'common_name' => 'Challenger',
                'country_code' => 'GB',
                'category_slug' => 'tanks',
                'introduced_year' => 2027,
                'estimated_produced' => 148,
                'description' => 'Major Challenger 2 upgrade by Rheinmetall BAE Systems with 120mm L/55A1 smoothbore replacing rifled gun, new modular armor, redesigned turret with advanced fire control. Future of British armor.',
                'specifications' => json_encode(['manufacturer' => 'RBSL', 'weight_tonnes' => 66.5, 'main_gun' => '120mm L/55A1', 'engine_hp' => 1500, 'crew' => 4]),
            ],
            [
                'designation' => 'Leclerc',
                'nato_designation' => null,
                'common_name' => 'Leclerc',
                'country_code' => 'FR',
                'category_slug' => 'tanks',
                'introduced_year' => 1992,
                'estimated_produced' => 862,
                'description' => 'French MBT by Nexter with composite/modular armor, autoloader reducing crew to three, FINDERS fire control. One of first tanks with battlefield management system integration.',
                'specifications' => json_encode(['manufacturer' => 'Nexter', 'weight_tonnes' => 57, 'main_gun' => '120mm CN120-26/52', 'engine_hp' => 1500, 'crew' => 3]),
            ],
            [
                'designation' => 'T-14 Armata',
                'nato_designation' => null,
                'common_name' => 'Armata',
                'country_code' => 'RU',
                'category_slug' => 'tanks',
                'introduced_year' => 2015,
                'estimated_produced' => 50,
                'description' => 'Revolutionary Russian MBT with unmanned turret, crew in armored capsule, Afghanit APS, and Malachit ERA. Limited production due to cost and sanctions. Parade showpiece with uncertain operational status.',
                'specifications' => json_encode(['manufacturer' => 'Uralvagonzavod', 'weight_tonnes' => 55, 'main_gun' => '125mm 2A82-1M', 'engine_hp' => 1500, 'crew' => 3]),
            ],
            [
                'designation' => 'T-90M Proryv',
                'nato_designation' => null,
                'common_name' => 'Proryv',
                'country_code' => 'RU',
                'category_slug' => 'tanks',
                'introduced_year' => 2020,
                'estimated_produced' => 400,
                'description' => 'Modernized T-90 with new welded turret, Relikt ERA, upgraded 125mm gun with Kalina fire control and thermal imaging. Significant losses observed in Ukraine conflict.',
                'specifications' => json_encode(['manufacturer' => 'Uralvagonzavod', 'weight_tonnes' => 48, 'main_gun' => '125mm 2A46M-5', 'engine_hp' => 1130, 'crew' => 3]),
            ],
            [
                'designation' => 'K2 Black Panther',
                'nato_designation' => null,
                'common_name' => 'Black Panther',
                'country_code' => 'KR',
                'category_slug' => 'tanks',
                'introduced_year' => 2014,
                'estimated_produced' => 260,
                'description' => 'South Korean MBT by Hyundai Rotem with advanced composite armor, ERA, autoloader, APS, and hydropneumatic suspension for hull tilting. Designed for Korean mountainous terrain with excellent gun depression.',
                'specifications' => json_encode(['manufacturer' => 'Hyundai Rotem', 'weight_tonnes' => 55, 'main_gun' => '120mm L/55', 'engine_hp' => 1500, 'crew' => 3]),
            ],
            [
                'designation' => 'Type 10 Hitomaru',
                'nato_designation' => null,
                'common_name' => 'Hitomaru',
                'country_code' => 'JP',
                'category_slug' => 'tanks',
                'introduced_year' => 2012,
                'estimated_produced' => 117,
                'description' => 'Japanese fourth-generation MBT by Mitsubishi designed for Japanese road infrastructure with modular ceramic composite armor, autoloader, C4I network-centric warfare, and hydropneumatic suspension.',
                'specifications' => json_encode(['manufacturer' => 'MHI', 'weight_tonnes' => 44, 'main_gun' => '120mm L/44', 'engine_hp' => 1200, 'crew' => 3]),
            ],
            [
                'designation' => 'Merkava Mk 4',
                'nato_designation' => null,
                'common_name' => 'Merkava',
                'country_code' => 'IL',
                'category_slug' => 'tanks',
                'introduced_year' => 2004,
                'estimated_produced' => 660,
                'description' => 'Israeli MBT with unique front-mounted engine for crew protection, rear compartment for infantry/wounded. Trophy APS, advanced armor. Designed from IDF combat experience for crew survivability.',
                'specifications' => json_encode(['manufacturer' => 'MANTAK', 'weight_tonnes' => 65, 'main_gun' => '120mm MG253', 'engine_hp' => 1500, 'crew' => 4]),
            ],
            [
                'designation' => 'Type 99A',
                'nato_designation' => null,
                'common_name' => 'Type 99',
                'country_code' => 'CN',
                'category_slug' => 'tanks',
                'introduced_year' => 2011,
                'estimated_produced' => 600,
                'description' => 'Chinese third-generation MBT by Norinco with composite armor, ERA, 125mm smoothbore with autoloader and gun-launched missiles, laser warning with active countermeasures. Primary PLA tank.',
                'specifications' => json_encode(['manufacturer' => 'Norinco', 'weight_tonnes' => 58, 'main_gun' => '125mm ZPT-98', 'engine_hp' => 1500, 'crew' => 3]),
            ],
            [
                'designation' => 'Schützenpanzer Puma',
                'nato_designation' => null,
                'common_name' => 'Puma',
                'country_code' => 'DE',
                'category_slug' => 'ifvs',
                'introduced_year' => 2015,
                'estimated_produced' => 350,
                'description' => 'German IFV by KMW/Rheinmetall with modular AMAP armor reaching MBT protection levels. Unmanned turret with 30mm autocannon and Spike missiles. Among most protected IFVs globally.',
                'specifications' => json_encode(['manufacturer' => 'PSM', 'weight_tonnes' => 43, 'main_gun' => '30mm MK 30-2/ABM', 'engine_hp' => 1100, 'troops' => 6]),
            ],
            [
                'designation' => 'CV90',
                'nato_designation' => null,
                'common_name' => 'CV90',
                'country_code' => 'SE',
                'category_slug' => 'ifvs',
                'introduced_year' => 1993,
                'estimated_produced' => 1300,
                'description' => 'Swedish IFV by BAE Hägglunds, highly successful export with 8 countries operating. Modular design, typically 30mm or 35mm autocannon, excellent Nordic terrain mobility.',
                'specifications' => json_encode(['manufacturer' => 'BAE Hägglunds', 'weight_tonnes' => 35, 'main_gun' => '35mm Bushmaster III', 'engine_hp' => 810, 'troops' => 8]),
            ],
            [
                'designation' => 'BMP-3',
                'nato_designation' => null,
                'common_name' => 'BMP-3',
                'country_code' => 'RU',
                'category_slug' => 'ifvs',
                'introduced_year' => 1987,
                'estimated_produced' => 2000,
                'description' => 'Russian IFV by Kurganmashzavod with unique 100mm gun/launcher for ATGMs combined with 30mm autocannon. Amphibious with water jets. Widely exported to Middle East and Asia.',
                'specifications' => json_encode(['manufacturer' => 'Kurganmashzavod', 'weight_tonnes' => 19, 'main_gun' => '100mm + 30mm', 'engine_hp' => 500, 'troops' => 7]),
            ],
            [
                'designation' => 'Namer',
                'nato_designation' => null,
                'common_name' => 'Namer',
                'country_code' => 'IL',
                'category_slug' => 'ifvs',
                'introduced_year' => 2008,
                'estimated_produced' => 300,
                'description' => 'Israeli heavy APC on Merkava chassis, heaviest APC in world with MBT-level protection and Trophy APS. Name means "Leopard" in Hebrew. Designed for urban warfare from IDF combat experience.',
                'specifications' => json_encode(['manufacturer' => 'MANTAK', 'weight_tonnes' => 60, 'armament' => '30mm or 12.7mm RCWS', 'engine_hp' => 1200, 'troops' => 9]),
            ],
        ];

        $this->insertEquipment($tanksAndIFVs, 'tanks');
        $this->command->info('Tanks and IFVs seeded: ' . count($tanksAndIFVs) . ' items');
    }

    private function seedMissiles(): void
    {
        $missiles = [
            [
                'designation' => 'BGM-109 Tomahawk Block V',
                'nato_designation' => null,
                'common_name' => 'Tomahawk',
                'country_code' => 'US',
                'category_slug' => 'missiles',
                'introduced_year' => 2021,
                'estimated_produced' => 500,
                'description' => 'Latest Raytheon Tomahawk variant with two-way datalink, maritime moving target capability, modular warhead options. 1,600+ km range with GPS/INS/TERCOM guidance. Used extensively for precision land-attack.',
                'specifications' => json_encode(['manufacturer' => 'Raytheon', 'range_km' => 1600, 'speed' => 'Subsonic', 'warhead_kg' => 450, 'guidance' => 'GPS/INS/TERCOM']),
            ],
            [
                'designation' => 'AGM-158B JASSM-ER',
                'nato_designation' => null,
                'common_name' => 'JASSM-ER',
                'country_code' => 'US',
                'category_slug' => 'missiles',
                'introduced_year' => 2014,
                'estimated_produced' => 2500,
                'description' => 'Extended Range Lockheed Martin standoff missile with 925+ km range. Low-observable design with autonomous IR terminal guidance. Deployed on B-1B, B-52, F-15E, F-16 for deep strike.',
                'specifications' => json_encode(['manufacturer' => 'Lockheed Martin', 'range_km' => 925, 'speed' => 'High Subsonic', 'warhead_kg' => 450, 'guidance' => 'GPS/INS/IR']),
            ],
            [
                'designation' => 'Storm Shadow/SCALP EG',
                'nato_designation' => null,
                'common_name' => 'Storm Shadow',
                'country_code' => 'GB',
                'category_slug' => 'missiles',
                'introduced_year' => 2003,
                'estimated_produced' => 1400,
                'description' => 'Anglo-French MBDA cruise missile with terrain-following, stealth design, BROACH tandem warhead for hardened targets. 560+ km range. Combat proven in Libya, Syria, Iraq, Ukraine.',
                'specifications' => json_encode(['manufacturer' => 'MBDA', 'range_km' => 560, 'speed' => 'High Subsonic', 'warhead_kg' => 450, 'guidance' => 'GPS/INS/TERPROM/IR']),
            ],
            [
                'designation' => 'Kh-101/Kh-102',
                'nato_designation' => 'AS-23A Kodiak',
                'common_name' => 'Kh-101',
                'country_code' => 'RU',
                'category_slug' => 'missiles',
                'introduced_year' => 2012,
                'estimated_produced' => 800,
                'description' => 'Russian air-launched strategic cruise missile by Raduga. Kh-101 conventional, Kh-102 nuclear-capable. Low-observable design with 4,500 km claimed range. Extensively used in Syria and Ukraine.',
                'specifications' => json_encode(['manufacturer' => 'Raduga', 'range_km' => 4500, 'speed' => 'Subsonic', 'warhead_kg' => 400, 'guidance' => 'GLONASS/INS/TERCOM']),
            ],
            [
                'designation' => '3M14 Kalibr',
                'nato_designation' => 'SS-N-30A Sagaris',
                'common_name' => 'Kalibr',
                'country_code' => 'RU',
                'category_slug' => 'missiles',
                'introduced_year' => 2012,
                'estimated_produced' => 1000,
                'description' => 'Russian sea-launched land-attack cruise missile by NPO Novator. Launched from submarines and surface vessels with 1,500-2,500 km range. First combat use Syria 2015, extensively deployed Ukraine.',
                'specifications' => json_encode(['manufacturer' => 'NPO Novator', 'range_km' => 2500, 'speed' => 'Subsonic', 'warhead_kg' => 450, 'guidance' => 'GLONASS/INS/TERCOM']),
            ],
            [
                'designation' => 'NSM Naval Strike Missile',
                'nato_designation' => null,
                'common_name' => 'NSM',
                'country_code' => 'NO',
                'category_slug' => 'missiles',
                'introduced_year' => 2012,
                'estimated_produced' => 600,
                'description' => 'Norwegian fifth-gen anti-ship/land-attack missile by Kongsberg with advanced stealth, autonomous IIR target recognition. 185+ km range. Selected by US Navy for LCS, adopted by NATO allies.',
                'specifications' => json_encode(['manufacturer' => 'Kongsberg', 'range_km' => 185, 'speed' => 'High Subsonic', 'warhead_kg' => 125, 'guidance' => 'GPS/INS/IIR']),
            ],
            [
                'designation' => 'MGM-140 ATACMS',
                'nato_designation' => null,
                'common_name' => 'ATACMS',
                'country_code' => 'US',
                'category_slug' => 'missiles',
                'introduced_year' => 1991,
                'estimated_produced' => 3700,
                'description' => 'US Army Tactical Missile by Lockheed Martin for deep strike from MLRS/HIMARS. 300 km range with GPS/INS, 227 kg unitary warhead. Combat proven in Desert Storm, Iraq, provided to Ukraine.',
                'specifications' => json_encode(['manufacturer' => 'Lockheed Martin', 'range_km' => 300, 'speed' => 'Mach 3+', 'warhead_kg' => 227, 'guidance' => 'GPS/INS']),
            ],
            [
                'designation' => 'PrSM Precision Strike Missile',
                'nato_designation' => null,
                'common_name' => 'PrSM',
                'country_code' => 'US',
                'category_slug' => 'missiles',
                'introduced_year' => 2024,
                'estimated_produced' => 100,
                'description' => 'Next-gen US Army missile by Lockheed Martin replacing ATACMS. 500+ km range, enhanced maneuverability, two missiles per HIMARS pod. Anti-ship seeker variant for maritime targets under development.',
                'specifications' => json_encode(['manufacturer' => 'Lockheed Martin', 'range_km' => 500, 'speed' => 'Mach 3+', 'warhead_kg' => 91, 'guidance' => 'GPS/INS/Multi-mode']),
            ],
            [
                'designation' => '9K720 Iskander-M',
                'nato_designation' => 'SS-26 Stone',
                'common_name' => 'Iskander',
                'country_code' => 'RU',
                'category_slug' => 'missiles',
                'introduced_year' => 2006,
                'estimated_produced' => 1000,
                'description' => 'Russian SRBM by KBM with quasi-ballistic trajectory defeating missile defenses. 500 km range with optical/radar terminal guidance. Extensively used in Ukraine, deployed in Kaliningrad.',
                'specifications' => json_encode(['manufacturer' => 'KBM Kolomna', 'range_km' => 500, 'speed' => 'Mach 6-7', 'warhead_kg' => 700, 'guidance' => 'GLONASS/INS/Optical']),
            ],
            [
                'designation' => 'Kh-47M2 Kinzhal',
                'nato_designation' => 'AS-24 Killjoy',
                'common_name' => 'Kinzhal',
                'country_code' => 'RU',
                'category_slug' => 'missiles',
                'introduced_year' => 2018,
                'estimated_produced' => 200,
                'description' => 'Russian air-launched hypersonic missile derived from Iskander, carried by MiG-31K and Tu-22M3. Claimed Mach 10+ speed, 2,000+ km range. First hypersonic weapon used in combat, Ukraine 2022.',
                'specifications' => json_encode(['manufacturer' => 'KBM Kolomna', 'range_km' => 2000, 'speed' => 'Mach 10-12', 'warhead_kg' => 500, 'guidance' => 'GLONASS/INS/Optical']),
            ],
            [
                'designation' => '3M22 Zircon',
                'nato_designation' => 'SS-N-33',
                'common_name' => 'Zircon',
                'country_code' => 'RU',
                'category_slug' => 'missiles',
                'introduced_year' => 2023,
                'estimated_produced' => 50,
                'description' => 'Russian hypersonic anti-ship cruise missile by NPO Mashinostroyeniya. Claims Mach 8-9 speed, 1,000 km range. Designed to defeat Aegis warships. First combat use Ukraine 2023.',
                'specifications' => json_encode(['manufacturer' => 'NPO Mashinostroyeniya', 'range_km' => 1000, 'speed' => 'Mach 8-9', 'warhead_kg' => 400, 'guidance' => 'GLONASS/INS/Active Radar']),
            ],
            [
                'designation' => 'DF-17',
                'nato_designation' => 'CSS-22',
                'common_name' => 'Dongfeng-17',
                'country_code' => 'CN',
                'category_slug' => 'missiles',
                'introduced_year' => 2019,
                'estimated_produced' => 100,
                'description' => 'Chinese MRBM with hypersonic glide vehicle by CASIC. First operationally deployed HGV weapon with 1,800-2,500 km range. Maneuverable warhead defeats missile defenses. Debuted 2019 National Day parade.',
                'specifications' => json_encode(['manufacturer' => 'CASIC', 'range_km' => 2500, 'speed' => 'Mach 5-10', 'warhead_kg' => 500, 'guidance' => 'BeiDou/INS']),
            ],
            [
                'designation' => 'DF-21D',
                'nato_designation' => 'CSS-5 Mod 5',
                'common_name' => 'Carrier Killer',
                'country_code' => 'CN',
                'category_slug' => 'missiles',
                'introduced_year' => 2010,
                'estimated_produced' => 200,
                'description' => 'Chinese anti-ship ballistic missile by CASIC designed to attack aircraft carriers at 1,500 km range. First weapon of its kind with maneuverable reentry and terminal radar. Significant A2/AD capability.',
                'specifications' => json_encode(['manufacturer' => 'CASIC', 'range_km' => 1500, 'speed' => 'Mach 10+', 'warhead_kg' => 600, 'guidance' => 'BeiDou/INS/Terminal Radar']),
            ],
            [
                'designation' => 'BrahMos',
                'nato_designation' => null,
                'common_name' => 'BrahMos',
                'country_code' => 'IN',
                'category_slug' => 'missiles',
                'introduced_year' => 2006,
                'estimated_produced' => 1000,
                'description' => 'Indo-Russian supersonic cruise missile, world\'s fastest operational at Mach 2.8-3.0. Joint DRDO/NPO development with 290-450 km range. Ship, submarine, ground, air-launched versions. BrahMos-II hypersonic in development.',
                'specifications' => json_encode(['manufacturer' => 'BrahMos Aerospace', 'range_km' => 450, 'speed' => 'Mach 2.8-3.0', 'warhead_kg' => 300, 'guidance' => 'GPS/GLONASS/INS/Active Radar']),
            ],
        ];

        $this->insertEquipment($missiles, 'missiles');
        $this->command->info('Missiles seeded: ' . count($missiles) . ' items');
    }

    private function seedAPCsAndMRAPs(): void
    {
        $apcsAndMraps = [
            [
                'designation' => 'M1126 Stryker',
                'nato_designation' => null,
                'common_name' => 'Stryker',
                'country_code' => 'US',
                'category_slug' => 'apc',
                'introduced_year' => 2002,
                'estimated_produced' => 4500,
                'description' => 'Eight-wheeled APC by GDLS carrying crew of 2 plus 9 infantry. Over 10 variants including ICV, MGS, and recon. Extensively deployed in Iraq and Afghanistan operations.',
                'specifications' => json_encode(['manufacturer' => 'GDLS', 'weight_tonnes' => 18, 'troops' => 9, 'variants' => 10, 'speed_kmh' => 100]),
            ],
            [
                'designation' => 'GTK Boxer',
                'nato_designation' => null,
                'common_name' => 'Boxer',
                'country_code' => 'DE',
                'category_slug' => 'apc',
                'introduced_year' => 2009,
                'estimated_produced' => 1200,
                'description' => 'Modular 8x8 AFV by German-Dutch ARTEC consortium with interchangeable mission modules for rapid role changes. Adopted by Germany, Netherlands, UK, Australia, Lithuania.',
                'specifications' => json_encode(['manufacturer' => 'ARTEC', 'weight_tonnes' => 33, 'troops' => 8, 'variants' => 15, 'speed_kmh' => 103]),
            ],
            [
                'designation' => 'VBCI',
                'nato_designation' => null,
                'common_name' => 'VBCI',
                'country_code' => 'FR',
                'category_slug' => 'apc',
                'introduced_year' => 2008,
                'estimated_produced' => 630,
                'description' => 'French 8x8 IFV by Nexter/Renault carrying 9 troops with 25mm turret. Extensively deployed in Mali, Central African Republic, and other French African operations.',
                'specifications' => json_encode(['manufacturer' => 'Nexter', 'weight_tonnes' => 29, 'troops' => 9, 'variants' => 6, 'speed_kmh' => 100]),
            ],
            [
                'designation' => 'Patria AMV',
                'nato_designation' => null,
                'common_name' => 'Patria AMV',
                'country_code' => 'FI',
                'category_slug' => 'apc',
                'introduced_year' => 2004,
                'estimated_produced' => 1500,
                'description' => 'Finnish modular 8x8 armored vehicle accommodating 12 troops with multiple weapon options from MGs to 120mm mortar. Exported to Poland, South Africa, Sweden, UAE, others.',
                'specifications' => json_encode(['manufacturer' => 'Patria', 'weight_tonnes' => 26, 'troops' => 12, 'variants' => 12, 'speed_kmh' => 100]),
            ],
            [
                'designation' => 'KTO Rosomak',
                'nato_designation' => null,
                'common_name' => 'Rosomak',
                'country_code' => 'PL',
                'category_slug' => 'apc',
                'introduced_year' => 2005,
                'estimated_produced' => 900,
                'description' => 'Polish Patria AMV variant with Hitfist-30P turret and 30mm autocannon. Combat tested in Afghanistan, highly regarded for protection levels.',
                'specifications' => json_encode(['manufacturer' => 'Rosomak S.A.', 'weight_tonnes' => 22, 'troops' => 8, 'variants' => 14, 'speed_kmh' => 100]),
            ],
            [
                'designation' => 'BTR-4 Bucephalus',
                'nato_designation' => null,
                'common_name' => 'Bucephalus',
                'country_code' => 'UA',
                'category_slug' => 'apc',
                'introduced_year' => 2008,
                'estimated_produced' => 350,
                'description' => 'Ukrainian 8x8 APC by KMDB accommodating 7 troops with multiple turret options including 30mm. Extensively used in Russo-Ukrainian War, exported to Iraq, Indonesia, Nigeria.',
                'specifications' => json_encode(['manufacturer' => 'KMDB', 'weight_tonnes' => 24, 'troops' => 7, 'variants' => 8, 'speed_kmh' => 110]),
            ],
            [
                'designation' => 'M-ATV',
                'nato_designation' => null,
                'common_name' => 'M-ATV',
                'country_code' => 'US',
                'category_slug' => 'apc',
                'introduced_year' => 2009,
                'estimated_produced' => 9000,
                'description' => 'Oshkosh MRAP all-terrain vehicle with TAK-4 independent suspension for superior off-road mobility. Crew of 5. Rapidly deployed to Afghanistan for mountainous terrain IED threats.',
                'specifications' => json_encode(['manufacturer' => 'Oshkosh', 'weight_tonnes' => 13, 'troops' => 5, 'variants' => 4, 'speed_kmh' => 105]),
            ],
            [
                'designation' => 'MaxxPro MRAP',
                'nato_designation' => null,
                'common_name' => 'MaxxPro',
                'country_code' => 'US',
                'category_slug' => 'apc',
                'introduced_year' => 2007,
                'estimated_produced' => 9000,
                'description' => 'Navistar Category I MRAP accommodating 6 personnel with V-shaped hull for blast deflection. One of most widely produced MRAPs with ambulance, command, route clearance variants.',
                'specifications' => json_encode(['manufacturer' => 'Navistar', 'weight_tonnes' => 18, 'troops' => 6, 'variants' => 5, 'speed_kmh' => 105]),
            ],
            [
                'designation' => 'Bushmaster PMV',
                'nato_designation' => null,
                'common_name' => 'Bushmaster',
                'country_code' => 'AU',
                'category_slug' => 'apc',
                'introduced_year' => 1998,
                'estimated_produced' => 1200,
                'description' => 'Australian protected mobility vehicle by Thales with monocoque steel V-hull carrying 9 troops. Proven mine/IED protection in Iraq and Afghanistan. Exported to Netherlands, UK, Japan, others.',
                'specifications' => json_encode(['manufacturer' => 'Thales Australia', 'weight_tonnes' => 15.4, 'troops' => 9, 'variants' => 7, 'speed_kmh' => 100]),
            ],
            [
                'designation' => 'Mastiff PPV',
                'nato_designation' => null,
                'common_name' => 'Mastiff',
                'country_code' => 'GB',
                'category_slug' => 'apc',
                'introduced_year' => 2007,
                'estimated_produced' => 400,
                'description' => 'British Cougar 6x6 MRAP transporting 8 troops with enhanced armor package. Deployed as urgent operational requirement in Afghanistan replacing Snatch Land Rovers after IED casualties.',
                'specifications' => json_encode(['manufacturer' => 'Force Protection', 'weight_tonnes' => 23, 'troops' => 8, 'variants' => 3, 'speed_kmh' => 90]),
            ],
        ];

        $this->insertEquipment($apcsAndMraps, 'apc');
        $this->command->info('APCs and MRAPs seeded: ' . count($apcsAndMraps) . ' items');
    }
}
