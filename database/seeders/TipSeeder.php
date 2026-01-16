<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TipSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Seeds sample anonymous tips for demonstration.
     */
    public function run(): void
    {
        $tips = [
            [
                'title' => 'Possible military convoy movement',
                'description' => 'Observed large military convoy (approximately 20-30 vehicles) moving north on M04 highway near Kupyansk around 0600 local time. Mix of trucks and what appeared to be armored vehicles. Uploaded dashcam footage.',
                'location_name' => 'M04 Highway, Kharkiv Oblast',
                'latitude' => 49.7000,
                'longitude' => 37.6000,
                'tip_type' => 'military_movement',
                'priority' => 'high',
                'status' => 'pending_review',
                'source_reliability' => 'unknown',
                'attachments' => json_encode(['dashcam_video_001.mp4']),
                'contact_info' => null,
                'ip_hash' => hash('sha256', '192.168.1.1' . config('app.key')),
                'submitted_at' => now()->subDays(2),
            ],
            [
                'title' => 'Damaged bridge observation',
                'description' => 'Bridge over small river appears to have been recently destroyed or damaged. Visible debris in water. Location is approximately 5km south of reported position. No military activity visible at time of observation.',
                'location_name' => 'Donetsk Oblast',
                'latitude' => 48.2500,
                'longitude' => 37.9000,
                'tip_type' => 'infrastructure_damage',
                'priority' => 'medium',
                'status' => 'verified',
                'source_reliability' => 'credible',
                'attachments' => json_encode(['bridge_damage_01.jpg', 'bridge_damage_02.jpg']),
                'contact_info' => null,
                'ip_hash' => hash('sha256', '10.0.0.1' . config('app.key')),
                'submitted_at' => now()->subDays(5),
                'verified_at' => now()->subDays(4),
                'verified_by' => 1,
            ],
            [
                'title' => 'Unusual aircraft activity',
                'description' => 'Multiple low-flying aircraft observed over rural area. Not typical flight patterns for the region. Could not identify aircraft type due to altitude and lighting conditions. Activity lasted approximately 15 minutes between 2200-2215.',
                'location_name' => 'Southern Zaporizhzhia Oblast',
                'latitude' => 47.1000,
                'longitude' => 35.3000,
                'tip_type' => 'air_activity',
                'priority' => 'medium',
                'status' => 'under_investigation',
                'source_reliability' => 'unknown',
                'attachments' => null,
                'contact_info' => 'secure_contact_7392@proton.me',
                'ip_hash' => hash('sha256', '172.16.0.1' . config('app.key')),
                'submitted_at' => now()->subDays(1),
            ],
            [
                'title' => 'Possible equipment storage site',
                'description' => 'Noticed new tarps/camouflage over previously empty field near industrial area. Increased vehicle traffic to location over past week. No markings visible. Coordinates are approximate.',
                'location_name' => 'Near Melitopol',
                'latitude' => 46.8500,
                'longitude' => 35.3700,
                'tip_type' => 'military_installation',
                'priority' => 'high',
                'status' => 'pending_review',
                'source_reliability' => 'unknown',
                'attachments' => json_encode(['sat_comparison.png']),
                'contact_info' => null,
                'ip_hash' => hash('sha256', '8.8.8.8' . config('app.key')),
                'submitted_at' => now()->subHours(12),
            ],
            [
                'title' => 'Humanitarian situation report',
                'description' => 'Village has been without power for 5 days. Water supply intermittent. Approximately 200 residents remaining, mostly elderly. No military presence observed. Road to regional center passable but damaged.',
                'location_name' => 'Small village, Kherson Oblast',
                'latitude' => 46.7500,
                'longitude' => 33.1000,
                'tip_type' => 'humanitarian',
                'priority' => 'high',
                'status' => 'verified',
                'source_reliability' => 'credible',
                'attachments' => null,
                'contact_info' => null,
                'ip_hash' => hash('sha256', '203.0.113.1' . config('app.key')),
                'submitted_at' => now()->subDays(3),
                'verified_at' => now()->subDays(2),
                'verified_by' => 1,
            ],
            [
                'title' => 'Social media account spreading disinformation',
                'description' => 'Telegram channel "xxx_news_official" posting manipulated images claiming to show Ukrainian losses. Same images traced to 2014 and other conflicts. Channel has 50k subscribers and posts 10-15 times daily.',
                'location_name' => null,
                'latitude' => null,
                'longitude' => null,
                'tip_type' => 'disinformation',
                'priority' => 'low',
                'status' => 'archived',
                'source_reliability' => 'credible',
                'attachments' => json_encode(['screenshot_01.png', 'reverse_image_results.pdf']),
                'contact_info' => null,
                'ip_hash' => hash('sha256', '198.51.100.1' . config('app.key')),
                'submitted_at' => now()->subWeeks(2),
                'verified_at' => now()->subDays(10),
                'verified_by' => 2,
            ],
            [
                'title' => 'Weapons system identification request',
                'description' => 'Captured image from video showing unidentified rocket launcher system. Appears similar to BM-21 but different configuration. Requesting expert identification. Video source is Telegram channel monitoring Russian forces.',
                'location_name' => 'Unknown, possibly Luhansk Oblast',
                'latitude' => null,
                'longitude' => null,
                'tip_type' => 'equipment_identification',
                'priority' => 'medium',
                'status' => 'under_investigation',
                'source_reliability' => 'unknown',
                'attachments' => json_encode(['rocket_launcher_frame.jpg']),
                'contact_info' => null,
                'ip_hash' => hash('sha256', '192.0.2.1' . config('app.key')),
                'submitted_at' => now()->subDays(4),
            ],
            [
                'title' => 'Geolocated strike aftermath',
                'description' => 'Successfully geolocated strike footage posted on social media. Coordinates match shopping center in Kharkiv. Strike appears to have occurred approximately 1400 local time based on shadow analysis. Multiple civilian casualties reported in original post.',
                'location_name' => 'Kharkiv, Ukraine',
                'latitude' => 49.9935,
                'longitude' => 36.2304,
                'tip_type' => 'strike_verification',
                'priority' => 'high',
                'status' => 'verified',
                'source_reliability' => 'high',
                'attachments' => json_encode(['geolocation_analysis.pdf', 'shadow_analysis.png']),
                'contact_info' => 'osint_analyst@signal.org',
                'ip_hash' => hash('sha256', '100.0.0.1' . config('app.key')),
                'submitted_at' => now()->subDays(7),
                'verified_at' => now()->subDays(6),
                'verified_by' => 1,
            ],
            [
                'title' => 'Maritime activity observation',
                'description' => 'Observed unusual naval activity near reported location. Multiple small vessels and what appeared to be a larger ship approximately 15km offshore. Activity continued for several hours. No AIS signals detected for vessels.',
                'location_name' => 'Black Sea coast near Odesa',
                'latitude' => 46.4000,
                'longitude' => 30.7000,
                'tip_type' => 'naval_activity',
                'priority' => 'medium',
                'status' => 'pending_review',
                'source_reliability' => 'unknown',
                'attachments' => null,
                'contact_info' => null,
                'ip_hash' => hash('sha256', '93.184.216.34' . config('app.key')),
                'submitted_at' => now()->subHours(36),
            ],
            [
                'title' => 'Possible war crime evidence',
                'description' => 'Local resident shared images of what appears to be execution site in recently liberated village. Multiple bodies visible. Images too graphic to upload directly. Can provide via secure channel if needed for documentation.',
                'location_name' => 'Liberated village, Kharkiv Oblast',
                'latitude' => 49.5000,
                'longitude' => 37.2000,
                'tip_type' => 'war_crime',
                'priority' => 'critical',
                'status' => 'escalated',
                'source_reliability' => 'unknown',
                'attachments' => null,
                'contact_info' => 'secure_contact_available',
                'ip_hash' => hash('sha256', '185.199.108.1' . config('app.key')),
                'submitted_at' => now()->subDays(1),
            ],
        ];

        foreach ($tips as $tip) {
            DB::table('tips')->updateOrInsert(
                ['title' => $tip['title'], 'submitted_at' => $tip['submitted_at']],
                array_merge($tip, [
                    'uuid' => Str::uuid(),
                    'created_at' => $tip['submitted_at'],
                    'updated_at' => now(),
                ])
            );
        }

        $this->command->info('Tips seeded: ' . count($tips) . ' anonymous tips');
    }
}
