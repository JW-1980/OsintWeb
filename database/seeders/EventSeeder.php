<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Seeds sample events for demonstration purposes.
     */
    public function run(): void
    {
        // Get reference data
        $countries = DB::table('countries')->pluck('id', 'iso_code');

        // Map event_type values from seed data to event_types table slugs
        $eventTypeSlugMap = [
            'military_operation' => 'ground-combat',
            'naval_engagement'   => 'naval-engagement',
            'territorial_change' => 'territorial-advance',
            'sabotage'           => 'special-operations',
            'terrorist_attack'   => 'ground-combat',
            'airstrike'          => 'airstrike',
            'special_operation'  => 'special-operations',
            'equipment_loss'     => 'equipment-destroyed',
        ];

        // Look up event_type_id values from the event_types table
        $eventTypes = DB::table('event_types')->pluck('id', 'slug');
        $fallbackEventTypeId = $eventTypes->first();

        $events = [
            // Ukraine War Events
            [
                'title' => 'Russian forces cross Ukrainian border',
                'description' => 'Russian military forces crossed the Ukrainian border from multiple directions including Belarus, Russia, and Crimea. Cruise missiles and airstrikes targeted military installations across Ukraine. Full-scale invasion begins.',
                'event_type' => 'military_operation',
                'date' => '2022-02-24',
                'time' => '05:00:00',
                'latitude' => 50.4501,
                'longitude' => 30.5234,
                'location_name' => 'Kyiv, Ukraine',
                'perpetrator_country' => 'RU',
                'victim_country' => 'UA',
                'verified' => true,
                'verification_level' => 'confirmed',
                'casualties_military' => null,
                'casualties_civilian' => null,
                'sources' => json_encode(['Reuters', 'AP News', 'BBC', 'Ukrainian Government']),
            ],
            [
                'title' => 'Sinking of cruiser Moskva',
                'description' => 'Russian Black Sea Fleet flagship Moskva sunk after being hit by two Ukrainian Neptune anti-ship missiles. Russia claimed ammunition explosion. Largest warship lost in combat since WWII.',
                'event_type' => 'naval_engagement',
                'date' => '2022-04-14',
                'time' => '20:00:00',
                'latitude' => 45.3500,
                'longitude' => 32.0000,
                'location_name' => 'Black Sea, near Snake Island',
                'perpetrator_country' => 'UA',
                'victim_country' => 'RU',
                'verified' => true,
                'verification_level' => 'confirmed',
                'casualties_military' => 40,
                'casualties_civilian' => 0,
                'sources' => json_encode(['Pentagon', 'Ukrainian Navy', 'Satellite imagery']),
                'equipment_destroyed' => json_encode(['Slava-class cruiser Moskva']),
            ],
            [
                'title' => 'Kherson Liberation',
                'description' => 'Ukrainian forces entered Kherson city after Russian withdrawal from west bank of Dnipro River. Only regional capital captured and subsequently liberated during the invasion.',
                'event_type' => 'territorial_change',
                'date' => '2022-11-11',
                'time' => '10:00:00',
                'latitude' => 46.6354,
                'longitude' => 32.6169,
                'location_name' => 'Kherson, Ukraine',
                'perpetrator_country' => 'UA',
                'victim_country' => null,
                'verified' => true,
                'verification_level' => 'confirmed',
                'casualties_military' => null,
                'casualties_civilian' => null,
                'sources' => json_encode(['Ukrainian Government', 'BBC', 'Social media geolocated']),
            ],
            [
                'title' => 'Kerch Bridge Explosion',
                'description' => 'Explosion damaged the Kerch Strait Bridge connecting Russia to Crimea. Road section collapsed, railway section damaged. Ukraine did not officially claim responsibility.',
                'event_type' => 'sabotage',
                'date' => '2022-10-08',
                'time' => '06:07:00',
                'latitude' => 45.3167,
                'longitude' => 36.5167,
                'location_name' => 'Kerch Strait, Crimea',
                'perpetrator_country' => 'UA',
                'victim_country' => 'RU',
                'verified' => true,
                'verification_level' => 'confirmed',
                'casualties_military' => 0,
                'casualties_civilian' => 3,
                'sources' => json_encode(['Russian media', 'Satellite imagery', 'Social media']),
            ],
            [
                'title' => 'Wagner Mutiny',
                'description' => 'Wagner Group forces under Prigozhin seized Rostov-on-Don military headquarters and marched toward Moscow before standing down. Exposed tensions between PMC and Russian military.',
                'event_type' => 'military_operation',
                'date' => '2023-06-24',
                'time' => '02:00:00',
                'latitude' => 47.2357,
                'longitude' => 39.7015,
                'location_name' => 'Rostov-on-Don, Russia',
                'perpetrator_country' => 'RU',
                'victim_country' => 'RU',
                'verified' => true,
                'verification_level' => 'confirmed',
                'casualties_military' => 15,
                'casualties_civilian' => 0,
                'sources' => json_encode(['Social media', 'Russian media', 'Western intelligence']),
            ],
            [
                'title' => 'Avdiivka Falls to Russian Forces',
                'description' => 'Ukrainian forces withdrew from Avdiivka after months of intense fighting. Russian forces suffered heavy casualties in repeated assaults. First major territorial gain for Russia since Bakhmut.',
                'event_type' => 'territorial_change',
                'date' => '2024-02-17',
                'time' => '12:00:00',
                'latitude' => 48.1375,
                'longitude' => 37.7478,
                'location_name' => 'Avdiivka, Donetsk Oblast',
                'perpetrator_country' => 'RU',
                'victim_country' => 'UA',
                'verified' => true,
                'verification_level' => 'confirmed',
                'casualties_military' => 5000,
                'casualties_civilian' => null,
                'sources' => json_encode(['Ukrainian General Staff', 'ISW', 'Geolocated footage']),
            ],

            // Israel-Hamas War Events
            [
                'title' => 'Hamas October 7 Attack',
                'description' => 'Hamas launched surprise multi-pronged attack on Israel from Gaza. Militants breached border fence, attacked communities and music festival. 1,200 killed, 250+ hostages taken.',
                'event_type' => 'terrorist_attack',
                'date' => '2023-10-07',
                'time' => '06:30:00',
                'latitude' => 31.3547,
                'longitude' => 34.3088,
                'location_name' => 'Southern Israel',
                'perpetrator_country' => null,
                'victim_country' => 'IL',
                'verified' => true,
                'verification_level' => 'confirmed',
                'casualties_military' => 300,
                'casualties_civilian' => 900,
                'sources' => json_encode(['Israeli Government', 'IDF', 'ZAKA', 'International media']),
                'perpetrator_actor' => 'hamas',
            ],
            [
                'title' => 'IDF Ground Invasion of Gaza',
                'description' => 'Israeli Defense Forces launched ground operation into northern Gaza Strip following weeks of airstrikes. Urban combat in Gaza City and surrounding areas.',
                'event_type' => 'military_operation',
                'date' => '2023-10-27',
                'time' => '22:00:00',
                'latitude' => 31.5000,
                'longitude' => 34.4667,
                'location_name' => 'Gaza Strip',
                'perpetrator_country' => 'IL',
                'victim_country' => null,
                'verified' => true,
                'verification_level' => 'confirmed',
                'casualties_military' => null,
                'casualties_civilian' => null,
                'sources' => json_encode(['IDF', 'International media']),
            ],
            [
                'title' => 'Al-Ahli Hospital Explosion',
                'description' => 'Explosion at Al-Ahli Arab Hospital in Gaza City. Hamas claimed Israeli airstrike, Israel attributed to failed PIJ rocket. Multiple independent analyses support rocket malfunction.',
                'event_type' => 'airstrike',
                'date' => '2023-10-17',
                'time' => '18:59:00',
                'latitude' => 31.5200,
                'longitude' => 34.4400,
                'location_name' => 'Gaza City, Gaza Strip',
                'perpetrator_country' => null,
                'victim_country' => null,
                'verified' => true,
                'verification_level' => 'disputed',
                'casualties_military' => 0,
                'casualties_civilian' => 100,
                'sources' => json_encode(['Gaza Health Ministry', 'IDF', 'Independent analyses']),
            ],

            // Yemen Events
            [
                'title' => 'Houthi Red Sea Attacks Begin',
                'description' => 'Houthi forces began targeting commercial shipping in Red Sea in support of Gaza. Multiple ships attacked with drones and missiles. Major disruption to global shipping.',
                'event_type' => 'naval_engagement',
                'date' => '2023-11-19',
                'time' => '14:00:00',
                'latitude' => 14.7000,
                'longitude' => 42.0000,
                'location_name' => 'Red Sea, Bab el-Mandeb',
                'perpetrator_country' => null,
                'victim_country' => null,
                'verified' => true,
                'verification_level' => 'confirmed',
                'casualties_military' => 0,
                'casualties_civilian' => 0,
                'sources' => json_encode(['UKMTO', 'Shipping companies', 'Houthi statements']),
                'perpetrator_actor' => 'houthis',
            ],
            [
                'title' => 'US-UK Strike Houthi Targets in Yemen',
                'description' => 'United States and United Kingdom conducted airstrikes against Houthi military targets in Yemen in response to Red Sea shipping attacks. First direct Western military action in Yemen civil war.',
                'event_type' => 'airstrike',
                'date' => '2024-01-12',
                'time' => '02:00:00',
                'latitude' => 15.3694,
                'longitude' => 44.1910,
                'location_name' => 'Sanaa, Yemen',
                'perpetrator_country' => 'US',
                'victim_country' => null,
                'verified' => true,
                'verification_level' => 'confirmed',
                'casualties_military' => 5,
                'casualties_civilian' => 0,
                'sources' => json_encode(['Pentagon', 'UK MoD', 'Houthi media']),
            ],

            // Syrian Events
            [
                'title' => 'Turkish Operation Claw-Sword',
                'description' => 'Turkish airstrikes and artillery bombardment against SDF/PKK targets in northern Syria and Iraq following Istanbul bombing.',
                'event_type' => 'airstrike',
                'date' => '2022-11-20',
                'time' => '01:00:00',
                'latitude' => 36.5000,
                'longitude' => 40.0000,
                'location_name' => 'Northern Syria',
                'perpetrator_country' => 'TR',
                'victim_country' => null,
                'verified' => true,
                'verification_level' => 'confirmed',
                'casualties_military' => 89,
                'casualties_civilian' => 12,
                'sources' => json_encode(['Turkish MoD', 'SOHR', 'SDF statements']),
            ],
            [
                'title' => 'US Airstrike Kills ISIS Leader',
                'description' => 'US special operations raid killed ISIS leader Abu Ibrahim al-Hashimi al-Qurayshi in Idlib, Syria. Al-Qurayshi detonated suicide vest during raid.',
                'event_type' => 'special_operation',
                'date' => '2022-02-03',
                'time' => '01:00:00',
                'latitude' => 35.9333,
                'longitude' => 36.6333,
                'location_name' => 'Atmeh, Idlib Province, Syria',
                'perpetrator_country' => 'US',
                'victim_country' => null,
                'verified' => true,
                'verification_level' => 'confirmed',
                'casualties_military' => 1,
                'casualties_civilian' => 6,
                'sources' => json_encode(['White House', 'Pentagon', 'SOHR']),
            ],

            // Myanmar Events
            [
                'title' => 'Operation 1027 Begins',
                'description' => 'Three Brotherhood Alliance launched coordinated offensive in northern Shan State. Major territorial gains against Myanmar military. Turning point in Myanmar civil war.',
                'event_type' => 'military_operation',
                'date' => '2023-10-27',
                'time' => '04:00:00',
                'latitude' => 23.5000,
                'longitude' => 98.0000,
                'location_name' => 'Northern Shan State, Myanmar',
                'perpetrator_country' => null,
                'victim_country' => 'MM',
                'verified' => true,
                'verification_level' => 'confirmed',
                'casualties_military' => 500,
                'casualties_civilian' => null,
                'sources' => json_encode(['Myanmar military', 'Resistance media', 'ISP Myanmar']),
                'perpetrator_actor' => 'arakan-army',
            ],

            // Sudan Events
            [
                'title' => 'Fighting Erupts in Khartoum',
                'description' => 'Armed conflict began between Sudanese Armed Forces and Rapid Support Forces in Khartoum. Heavy fighting in the capital with artillery and airstrikes.',
                'event_type' => 'military_operation',
                'date' => '2023-04-15',
                'time' => '09:00:00',
                'latitude' => 15.5007,
                'longitude' => 32.5599,
                'location_name' => 'Khartoum, Sudan',
                'perpetrator_country' => 'SD',
                'victim_country' => 'SD',
                'verified' => true,
                'verification_level' => 'confirmed',
                'casualties_military' => 100,
                'casualties_civilian' => 50,
                'sources' => json_encode(['Reuters', 'Al Jazeera', 'Local reports']),
            ],

            // Historical events for reference
            [
                'title' => 'Fall of Kabul to Taliban',
                'description' => 'Taliban forces entered Kabul as Afghan government collapsed. President Ghani fled country. End of 20-year US-led intervention in Afghanistan.',
                'event_type' => 'territorial_change',
                'date' => '2021-08-15',
                'time' => '12:00:00',
                'latitude' => 34.5553,
                'longitude' => 69.2075,
                'location_name' => 'Kabul, Afghanistan',
                'perpetrator_country' => null,
                'victim_country' => 'AF',
                'verified' => true,
                'verification_level' => 'confirmed',
                'casualties_military' => null,
                'casualties_civilian' => null,
                'sources' => json_encode(['International media', 'US Government']),
                'perpetrator_actor' => 'taliban',
            ],
            [
                'title' => 'Kabul Airport Bombing',
                'description' => 'ISIS-K suicide bombing at Abbey Gate, Hamid Karzai International Airport during US evacuation. Killed 13 US service members and 170 Afghan civilians.',
                'event_type' => 'terrorist_attack',
                'date' => '2021-08-26',
                'time' => '17:50:00',
                'latitude' => 34.5658,
                'longitude' => 69.2122,
                'location_name' => 'Kabul Airport, Afghanistan',
                'perpetrator_country' => null,
                'victim_country' => 'AF',
                'verified' => true,
                'verification_level' => 'confirmed',
                'casualties_military' => 13,
                'casualties_civilian' => 170,
                'sources' => json_encode(['Pentagon', 'International media']),
                'perpetrator_actor' => 'isis-k',
            ],

            // Equipment loss events
            [
                'title' => 'T-90M Destroyed by FPV Drone',
                'description' => 'Russian T-90M Proryv main battle tank destroyed by Ukrainian FPV kamikaze drone in Donetsk Oblast. Demonstrates vulnerability of modern armor to cheap drones.',
                'event_type' => 'equipment_loss',
                'date' => '2024-01-15',
                'time' => '14:30:00',
                'latitude' => 48.1000,
                'longitude' => 37.8000,
                'location_name' => 'Donetsk Oblast, Ukraine',
                'perpetrator_country' => 'UA',
                'victim_country' => 'RU',
                'verified' => true,
                'verification_level' => 'confirmed',
                'casualties_military' => 3,
                'casualties_civilian' => 0,
                'sources' => json_encode(['Geolocated video', 'Ukrainian military']),
                'equipment_destroyed' => json_encode(['T-90M Proryv']),
            ],
            [
                'title' => 'Leopard 2A6 Lost to Mine',
                'description' => 'German-supplied Leopard 2A6 tank destroyed by anti-tank mine during Ukrainian counteroffensive. First confirmed Leopard 2A6 loss in Ukraine.',
                'event_type' => 'equipment_loss',
                'date' => '2023-06-08',
                'time' => '10:00:00',
                'latitude' => 47.5000,
                'longitude' => 35.5000,
                'location_name' => 'Zaporizhzhia Oblast, Ukraine',
                'perpetrator_country' => 'RU',
                'victim_country' => 'UA',
                'verified' => true,
                'verification_level' => 'confirmed',
                'casualties_military' => 0,
                'casualties_civilian' => 0,
                'sources' => json_encode(['Russian military bloggers', 'Satellite imagery']),
                'equipment_destroyed' => json_encode(['Leopard 2A6']),
            ],
        ];

        foreach ($events as $event) {
            // Resolve event_type_id from event_types table via slug mapping
            $mappedSlug = $eventTypeSlugMap[$event['event_type']] ?? null;
            $eventTypeId = $mappedSlug ? ($eventTypes[$mappedSlug] ?? $fallbackEventTypeId) : $fallbackEventTypeId;

            // Combine date + time into occurred_at datetime
            $occurredAt = $event['date'] . ' ' . ($event['time'] ?? '00:00:00');

            // Determine status from verified flag and verification_level
            $status = 'pending';
            if ($event['verified'] && $event['verification_level'] === 'confirmed') {
                $status = 'verified';
            } elseif ($event['verification_level'] === 'disputed') {
                $status = 'disputed';
            }

            // Map verification_level to a numeric score (0.00 - 1.00)
            $verificationScore = match ($event['verification_level']) {
                'confirmed' => 1.00,
                'disputed'  => 0.50,
                default     => 0.00,
            };

            // Build custom_fields with extra data that has no dedicated column
            $customFields = [];
            $perpetratorCountry = $event['perpetrator_country'] ?? null;
            $victimCountry = $event['victim_country'] ?? null;

            if ($perpetratorCountry !== null) {
                $customFields['perpetrator_country'] = $perpetratorCountry;
            }
            if ($victimCountry !== null) {
                $customFields['victim_country'] = $victimCountry;
            }
            if (isset($event['perpetrator_actor'])) {
                $customFields['perpetrator_actor'] = $event['perpetrator_actor'];
            }
            if (($event['casualties_military'] ?? null) !== null) {
                $customFields['casualties_military'] = $event['casualties_military'];
            }
            if (($event['casualties_civilian'] ?? null) !== null) {
                $customFields['casualties_civilian'] = $event['casualties_civilian'];
            }
            if (isset($event['sources'])) {
                $customFields['sources'] = json_decode($event['sources'], true);
            }
            if (isset($event['equipment_destroyed'])) {
                $customFields['equipment_destroyed'] = json_decode($event['equipment_destroyed'], true);
            }
            if (isset($event['latitude']) && isset($event['longitude'])) {
                $customFields['latitude'] = $event['latitude'];
                $customFields['longitude'] = $event['longitude'];
            }

            $eventData = [
                'uuid' => Str::uuid(),
                'event_type_id' => $eventTypeId,
                'user_id' => 1,
                'title' => $event['title'],
                'description' => $event['description'],
                'location_name' => $event['location_name'],
                'location_accuracy' => 'approximate',
                'occurred_at' => $occurredAt,
                'date_accuracy' => 'exact',
                'status' => $status,
                'verification_score' => $verificationScore,
                'visibility' => 'public',
                'custom_fields' => !empty($customFields) ? json_encode($customFields) : null,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            DB::table('events')->updateOrInsert(
                ['title' => $event['title']],
                $eventData
            );
        }

        $this->command->info('Events seeded: ' . count($events) . ' events');
    }
}
