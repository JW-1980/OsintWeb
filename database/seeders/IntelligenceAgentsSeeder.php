<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class IntelligenceAgentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Creates comprehensive intelligence agents with 100+ configuration data points
     * for automated OSINT data collection and analysis.
     */
    public function run(): void
    {
        $now = now();

        $agents = [
            // ===============================================
            // FLIGHT TRACKING AGENTS (15+ data points)
            // ===============================================
            [
                'name' => 'ADS-B Military Aircraft Monitor',
                'slug' => 'adsb-military-monitor',
                'description' => 'Monitors military and government aircraft via ADS-B Exchange and OpenSky Network',
                'type' => 'monitor',
                'category' => 'flight',
                'configuration' => [
                    'update_interval_seconds' => 30,
                    'alert_on_new_aircraft' => true,
                    'track_history_hours' => 72,
                    'min_altitude_feet' => 0,
                    'max_altitude_feet' => 60000,
                ],
                'data_sources' => [
                    ['name' => 'ADS-B Exchange', 'url' => 'https://globe.adsbexchange.com/', 'type' => 'api', 'unfiltered' => true],
                    ['name' => 'OpenSky Network', 'url' => 'https://opensky-network.org/', 'type' => 'api', 'research_access' => true],
                    ['name' => 'adsb.fi', 'url' => 'https://adsb.fi/', 'type' => 'api', 'community' => true],
                    ['name' => 'ADSB.lol', 'url' => 'https://www.adsb.lol/', 'type' => 'api', 'open_source' => true],
                ],
                'filters' => [
                    'aircraft_types' => ['military', 'government', 'surveillance', 'tanker', 'awacs', 'reconnaissance'],
                    'mil_hex_codes' => true,
                    'interesting_callsigns' => ['RCH', 'REACH', 'FORTE', 'DUKE', 'JAKE', 'HOMER', 'LAGR', 'NATO'],
                    'registration_prefixes' => ['AF', 'N1', 'N2', 'N3', 'N4', 'N5'],
                ],
                'triggers' => [
                    ['type' => 'geofence_entry', 'regions' => ['conflict_zones', 'border_areas']],
                    ['type' => 'unusual_pattern', 'patterns' => ['loiter', 'race_track', 'spiral']],
                    ['type' => 'squawk_codes', 'codes' => ['7500', '7600', '7700', '0000']],
                ],
                'output_format' => [
                    'fields' => ['icao24', 'callsign', 'registration', 'aircraft_type', 'latitude', 'longitude', 'altitude', 'speed', 'heading', 'squawk'],
                    'formats' => ['json', 'geojson', 'csv'],
                ],
                'requires_api_key' => false,
                'rate_limit_per_minute' => 60,
                'priority' => 90,
            ],
            [
                'name' => 'Reconnaissance Aircraft Tracker',
                'slug' => 'recon-aircraft-tracker',
                'description' => 'Specialized tracker for ISR (Intelligence, Surveillance, Reconnaissance) aircraft',
                'type' => 'monitor',
                'category' => 'flight',
                'configuration' => [
                    'track_types' => ['RC-135', 'E-3', 'E-8', 'P-8', 'P-3', 'EP-3', 'RQ-4', 'MQ-9', 'U-2', 'E-2', 'Sentinel', 'Rivet Joint'],
                    'nato_awacs' => ['LX-N90448', 'LX-N90449', 'LX-N90450', 'LX-N90451', 'LX-N90452', 'LX-N90453', 'LX-N90454', 'LX-N90455', 'LX-N90456', 'LX-N90457'],
                    'us_global_hawk_prefix' => 'FORTE',
                    'alert_regions' => ['Ukraine', 'Black Sea', 'Baltic Sea', 'Taiwan Strait', 'South China Sea'],
                ],
                'data_sources' => [
                    ['name' => 'ADS-B Exchange', 'priority' => 1],
                    ['name' => 'Flightradar24', 'priority' => 2, 'note' => 'May filter some military'],
                ],
                'filters' => [
                    'callsign_patterns' => ['^FORTE', '^JAKE', '^HOMER', '^LAGR', '^RRR', '^NATO'],
                    'aircraft_categories' => ['A7', 'B2', 'B4'],
                ],
                'triggers' => [
                    ['type' => 'new_mission', 'description' => 'Alert when ISR aircraft begins new mission profile'],
                    ['type' => 'proximity_alert', 'distance_nm' => 50, 'to' => 'conflict_zones'],
                ],
                'output_format' => [
                    'include_track_history' => true,
                    'include_estimated_mission_type' => true,
                ],
                'requires_api_key' => false,
                'priority' => 95,
            ],

            // ===============================================
            // MARITIME INTELLIGENCE AGENTS (15+ data points)
            // ===============================================
            [
                'name' => 'AIS Vessel Monitor',
                'slug' => 'ais-vessel-monitor',
                'description' => 'Monitors vessel movements via AIS (Automatic Identification System)',
                'type' => 'monitor',
                'category' => 'maritime',
                'configuration' => [
                    'update_interval_seconds' => 60,
                    'track_history_days' => 30,
                    'include_satellite_ais' => true,
                ],
                'data_sources' => [
                    ['name' => 'MarineTraffic', 'url' => 'https://www.marinetraffic.com/', 'vessels' => '550000+', 'languages' => 34],
                    ['name' => 'VesselFinder', 'url' => 'https://www.vesselfinder.com/', 'note' => 'More current without subscription'],
                    ['name' => 'MyShipTracking', 'url' => 'https://www.myshiptracking.com/', 'free' => true],
                    ['name' => 'AISHub', 'url' => 'https://www.aishub.net/', 'api' => true],
                    ['name' => 'Global Fishing Watch', 'url' => 'https://globalfishingwatch.org/', 'focus' => 'fishing_vessels'],
                ],
                'filters' => [
                    'vessel_types' => ['military', 'cargo', 'tanker', 'fishing', 'research'],
                    'flag_states' => [], // Empty = all
                    'min_length_meters' => 0,
                    'max_length_meters' => null,
                ],
                'triggers' => [
                    ['type' => 'dark_vessel', 'description' => 'AIS switched off unexpectedly', 'gap_hours' => 6],
                    ['type' => 'sanctions_flag', 'countries' => ['Russia', 'Iran', 'North Korea', 'Syria']],
                    ['type' => 'geofence', 'regions' => ['conflict_zones', 'exclusive_economic_zones']],
                    ['type' => 'sts_transfer', 'description' => 'Ship-to-ship transfer detected'],
                ],
                'output_format' => [
                    'fields' => ['mmsi', 'imo', 'vessel_name', 'flag', 'vessel_type', 'latitude', 'longitude', 'speed', 'heading', 'destination', 'eta'],
                ],
                'requires_api_key' => true,
                'api_providers' => ['MarineTraffic', 'VesselFinder'],
                'priority' => 85,
            ],
            [
                'name' => 'Sanctions Evasion Detector',
                'slug' => 'sanctions-evasion-detector',
                'description' => 'Detects potential sanctions evasion patterns in maritime shipping',
                'type' => 'analyzer',
                'category' => 'maritime',
                'configuration' => [
                    'analysis_window_days' => 90,
                    'ais_gap_threshold_hours' => 12,
                    'port_dwell_threshold_hours' => 48,
                ],
                'data_sources' => [
                    ['name' => 'AIS Data', 'type' => 'primary'],
                    ['name' => 'OFAC SDN List', 'url' => 'https://sanctionssearch.ofac.treas.gov/', 'type' => 'reference'],
                    ['name' => 'EU Sanctions List', 'type' => 'reference'],
                    ['name' => 'UN Sanctions', 'type' => 'reference'],
                ],
                'filters' => [
                    'patterns' => [
                        'ais_manipulation' => 'Frequent AIS gaps or position jumps',
                        'flag_hopping' => 'Multiple flag state changes',
                        'name_changes' => 'Frequent vessel name or MMSI changes',
                        'dark_port_calls' => 'Port visits during AIS blackout',
                        'sts_transfers' => 'Ship-to-ship transfers at sea',
                    ],
                ],
                'triggers' => [
                    ['type' => 'high_risk_score', 'threshold' => 0.7],
                    ['type' => 'sanctioned_entity_match'],
                ],
                'requires_api_key' => true,
                'priority' => 80,
            ],

            // ===============================================
            // SATELLITE IMAGERY AGENTS (15+ data points)
            // ===============================================
            [
                'name' => 'Sentinel-2 Change Detector',
                'slug' => 'sentinel2-change-detector',
                'description' => 'Monitors areas of interest using Sentinel-2 optical imagery for changes',
                'type' => 'analyzer',
                'category' => 'satellite',
                'configuration' => [
                    'revisit_days' => 5,
                    'cloud_cover_max_percent' => 30,
                    'resolution_meters' => 10,
                    'bands' => ['B02', 'B03', 'B04', 'B08', 'B11', 'B12'],
                ],
                'data_sources' => [
                    ['name' => 'Copernicus Open Access Hub', 'url' => 'https://scihub.copernicus.eu/', 'cost' => 'free'],
                    ['name' => 'Sentinel Hub', 'url' => 'https://www.sentinel-hub.com/', 'api' => true],
                    ['name' => 'Google Earth Engine', 'url' => 'https://earthengine.google.com/', 'processing' => 'cloud'],
                    ['name' => 'EOS Data Analytics', 'url' => 'https://eos.com/', 'visualization' => true],
                ],
                'filters' => [
                    'change_types' => ['building_damage', 'infrastructure', 'vegetation', 'water_bodies', 'burn_scars'],
                    'min_change_percent' => 10,
                    'analysis_methods' => ['ndvi_difference', 'nir_false_color', 'spectral_unmixing'],
                ],
                'triggers' => [
                    ['type' => 'significant_change', 'threshold' => 0.3],
                    ['type' => 'new_imagery_available'],
                ],
                'output_format' => [
                    'formats' => ['geotiff', 'png', 'geojson'],
                    'include_before_after' => true,
                    'include_change_map' => true,
                ],
                'requires_api_key' => false,
                'priority' => 75,
            ],
            [
                'name' => 'SAR Damage Assessment',
                'slug' => 'sar-damage-assessment',
                'description' => 'Uses Sentinel-1 SAR imagery for building damage detection (works through clouds)',
                'type' => 'analyzer',
                'category' => 'satellite',
                'configuration' => [
                    'sensor' => 'Sentinel-1',
                    'mode' => 'IW (Interferometric Wide Swath)',
                    'resolution_meters' => 20,
                    'polarization' => ['VV', 'VH'],
                    'works_through_clouds' => true,
                    'works_at_night' => true,
                ],
                'data_sources' => [
                    ['name' => 'Copernicus Sentinel-1', 'type' => 'SAR', 'cost' => 'free'],
                    ['name' => 'ASF DAAC', 'url' => 'https://search.asf.alaska.edu/', 'archive' => true],
                ],
                'filters' => [
                    'analysis_methods' => [
                        'coherence_change' => 'Detect structural changes via interferometric coherence',
                        'log_ratio' => 'Amplitude change detection',
                        'texture_analysis' => 'Building footprint texture changes',
                    ],
                    'reference_period_days' => 12,
                ],
                'triggers' => [
                    ['type' => 'coherence_drop', 'threshold' => 0.5],
                    ['type' => 'amplitude_change', 'db_threshold' => 3],
                ],
                'output_format' => [
                    'damage_classification' => ['no_damage', 'minor', 'moderate', 'severe', 'destroyed'],
                    'formats' => ['geotiff', 'vector_polygons'],
                ],
                'requires_api_key' => false,
                'priority' => 80,
            ],

            // ===============================================
            // SOCIAL MEDIA AGENTS (20+ data points)
            // ===============================================
            [
                'name' => 'Twitter/X Conflict Monitor',
                'slug' => 'twitter-conflict-monitor',
                'description' => 'Monitors Twitter/X for conflict-related content with geolocation',
                'type' => 'monitor',
                'category' => 'social_media',
                'configuration' => [
                    'languages' => ['en', 'ru', 'uk', 'ar', 'fa', 'zh'],
                    'include_retweets' => false,
                    'include_media' => true,
                    'geocode_enabled' => true,
                ],
                'data_sources' => [
                    ['name' => 'Twitter API v2', 'type' => 'official', 'requires_elevated' => true],
                    ['name' => 'twscrape', 'url' => 'https://github.com/vladkens/twscrape', 'type' => 'scraper'],
                    ['name' => 'Wayback Machine', 'url' => 'https://web.archive.org/', 'purpose' => 'deleted_content'],
                ],
                'filters' => [
                    'keywords' => ['conflict', 'airstrike', 'explosion', 'casualties', 'military', 'attack', 'shelling'],
                    'hashtags' => [],
                    'exclude_keywords' => ['game', 'movie', 'trailer', 'RT'],
                    'verified_only' => false,
                    'min_followers' => 0,
                    'min_engagement' => 0,
                ],
                'triggers' => [
                    ['type' => 'breaking_news', 'velocity_threshold' => 100],
                    ['type' => 'verified_source', 'account_types' => ['journalist', 'official', 'osint']],
                    ['type' => 'media_with_geotag'],
                ],
                'output_format' => [
                    'fields' => ['tweet_id', 'author', 'text', 'media_urls', 'geolocation', 'timestamp', 'engagement'],
                    'archive_media' => true,
                ],
                'requires_api_key' => true,
                'api_providers' => ['Twitter'],
                'rate_limit_per_minute' => 100,
                'priority' => 90,
            ],
            [
                'name' => 'Telegram Channel Monitor',
                'slug' => 'telegram-channel-monitor',
                'description' => 'Monitors public Telegram channels for OSINT-relevant content',
                'type' => 'scraper',
                'category' => 'social_media',
                'configuration' => [
                    'channel_types' => ['military', 'news', 'osint', 'official'],
                    'media_download' => true,
                    'message_history_limit' => 1000,
                    'update_interval_seconds' => 300,
                ],
                'data_sources' => [
                    ['name' => 'Telethon', 'url' => 'https://github.com/LonamiWebs/Telethon', 'type' => 'library'],
                    ['name' => 'Telegram API', 'type' => 'official'],
                ],
                'filters' => [
                    'keywords' => ['бойові', 'удар', 'вибух', 'ракета', 'БПЛА', 'атака'],
                    'media_types' => ['photo', 'video', 'document'],
                    'forward_analysis' => true,
                ],
                'triggers' => [
                    ['type' => 'breaking_content', 'engagement_velocity' => 50],
                    ['type' => 'admin_post', 'priority' => 'high'],
                    ['type' => 'geolocation_mention'],
                ],
                'output_format' => [
                    'fields' => ['channel', 'message_id', 'text', 'media', 'forwards', 'views', 'timestamp'],
                    'extract_coordinates' => true,
                ],
                'requires_api_key' => true,
                'api_providers' => ['Telegram'],
                'priority' => 85,
            ],
            [
                'name' => 'VKontakte Monitor',
                'slug' => 'vk-monitor',
                'description' => 'Monitors Russian VKontakte social network for military and conflict content',
                'type' => 'scraper',
                'category' => 'social_media',
                'configuration' => [
                    'content_types' => ['posts', 'photos', 'videos', 'comments'],
                    'profile_analysis' => true,
                    'group_monitoring' => true,
                ],
                'data_sources' => [
                    ['name' => 'VK API', 'type' => 'official'],
                    ['name' => 'Archive.org', 'purpose' => 'deleted_content'],
                ],
                'filters' => [
                    'keywords_ru' => ['СВО', 'мобилизация', 'военные', 'потери', 'ЧВК'],
                    'group_types' => ['military_units', 'memorial', 'families'],
                    'geolocation' => true,
                ],
                'triggers' => [
                    ['type' => 'memorial_post', 'keywords' => ['память', 'погиб', 'герой']],
                    ['type' => 'unit_identification'],
                    ['type' => 'equipment_photo'],
                ],
                'requires_api_key' => true,
                'api_providers' => ['VK'],
                'priority' => 75,
            ],

            // ===============================================
            // GEOLOCATION AGENTS (10+ data points)
            // ===============================================
            [
                'name' => 'Image Geolocation Assistant',
                'slug' => 'image-geolocation',
                'description' => 'Assists with geolocating images using multiple data sources',
                'type' => 'analyzer',
                'category' => 'geolocation',
                'configuration' => [
                    'reverse_image_search' => true,
                    'exif_extraction' => true,
                    'shadow_analysis' => true,
                    'landmark_detection' => true,
                ],
                'data_sources' => [
                    ['name' => 'Google Lens', 'url' => 'https://lens.google/', 'purpose' => 'object_recognition'],
                    ['name' => 'TinEye', 'url' => 'https://tineye.com/', 'database' => '72_billion_images'],
                    ['name' => 'Yandex Images', 'url' => 'https://yandex.com/images/', 'strength' => 'faces_eastern_europe'],
                    ['name' => 'Bing Visual Search', 'url' => 'https://www.bing.com/visualsearch'],
                    ['name' => 'Baidu Images', 'url' => 'https://image.baidu.com/', 'strength' => 'chinese_content'],
                    ['name' => 'SunCalc', 'url' => 'https://www.suncalc.org/', 'purpose' => 'shadow_analysis'],
                    ['name' => 'Google Earth Pro', 'purpose' => 'verification'],
                    ['name' => 'Mapillary', 'url' => 'https://www.mapillary.com/', 'purpose' => 'street_view'],
                ],
                'filters' => [
                    'analysis_steps' => [
                        'extract_exif_metadata',
                        'reverse_image_search_all_engines',
                        'identify_text_and_signage',
                        'analyze_shadows_if_visible',
                        'detect_landmarks_and_infrastructure',
                        'cross_reference_street_view',
                    ],
                ],
                'output_format' => [
                    'confidence_score' => true,
                    'candidate_locations' => true,
                    'supporting_evidence' => true,
                ],
                'requires_api_key' => false,
                'priority' => 85,
            ],
            [
                'name' => 'Chronolocation Analyzer',
                'slug' => 'chronolocation-analyzer',
                'description' => 'Determines when an image or video was taken using environmental clues',
                'type' => 'analyzer',
                'category' => 'geolocation',
                'configuration' => [
                    'shadow_analysis' => true,
                    'weather_correlation' => true,
                    'astronomical_analysis' => true,
                    'vegetation_analysis' => true,
                ],
                'data_sources' => [
                    ['name' => 'SunCalc', 'url' => 'https://www.suncalc.org/', 'purpose' => 'sun_position'],
                    ['name' => 'Weather Underground History', 'purpose' => 'historical_weather'],
                    ['name' => 'Wolfram Alpha', 'purpose' => 'astronomical_calculations'],
                    ['name' => 'Stellarium', 'url' => 'https://stellarium.org/', 'purpose' => 'star_positions'],
                    ['name' => 'timeanddate.com', 'purpose' => 'sunrise_sunset_moonphase'],
                ],
                'filters' => [
                    'indicators' => ['shadow_length', 'shadow_direction', 'sun_position', 'moon_phase', 'weather_conditions', 'vegetation_state', 'clock_faces', 'screen_content'],
                ],
                'output_format' => [
                    'estimated_datetime_range' => true,
                    'confidence_level' => true,
                    'methodology_used' => true,
                ],
                'requires_api_key' => false,
                'priority' => 70,
            ],

            // ===============================================
            // WEAPONS IDENTIFICATION AGENTS (10+ data points)
            // ===============================================
            [
                'name' => 'Ordnance Identifier',
                'slug' => 'ordnance-identifier',
                'description' => 'Identifies explosive ordnance and munitions from imagery',
                'type' => 'analyzer',
                'category' => 'weapons',
                'configuration' => [
                    'munition_types' => ['bombs', 'missiles', 'rockets', 'artillery_shells', 'cluster_munitions', 'landmines'],
                    'marking_analysis' => true,
                    'dimension_estimation' => true,
                ],
                'data_sources' => [
                    ['name' => 'Open Source Munitions Portal', 'url' => 'https://osmp.ngo/', 'type' => 'database'],
                    ['name' => 'CAR (Conflict Armament Research)', 'url' => 'https://www.conflictarm.com/', 'type' => 'research'],
                    ['name' => 'GIJN Ordnance Guide', 'url' => 'https://gijn.org/', 'type' => 'guide'],
                    ['name' => 'NATO CAGE Code Database', 'purpose' => 'manufacturer_identification'],
                    ['name' => 'Janes', 'url' => 'https://www.janes.com/', 'type' => 'commercial_database'],
                ],
                'filters' => [
                    'identification_features' => [
                        'physical_dimensions',
                        'color_codes',
                        'markings_and_stamps',
                        'fuse_types',
                        'fin_configurations',
                        'guidance_systems',
                    ],
                    'origin_countries' => ['US', 'Russia', 'China', 'Iran', 'North Korea', 'Israel'],
                ],
                'output_format' => [
                    'munition_type' => true,
                    'manufacturer' => true,
                    'origin_country' => true,
                    'specifications' => true,
                    'legal_status' => true,
                ],
                'requires_api_key' => false,
                'priority' => 80,
            ],
            [
                'name' => 'Military Equipment Recognizer',
                'slug' => 'equipment-recognizer',
                'description' => 'Identifies military vehicles, aircraft, and naval vessels from imagery',
                'type' => 'analyzer',
                'category' => 'weapons',
                'configuration' => [
                    'equipment_categories' => ['tanks', 'ifvs', 'apcs', 'artillery', 'aircraft', 'helicopters', 'ships', 'air_defense'],
                    'variant_detection' => true,
                    'marking_analysis' => true,
                ],
                'data_sources' => [
                    ['name' => 'Army Recognition', 'url' => 'https://www.armyrecognition.com/', 'coverage' => 'global'],
                    ['name' => 'Military.com Equipment Guide', 'url' => 'https://www.military.com/equipment'],
                    ['name' => 'MEGA App', 'url' => 'https://militaryequipmentguide.com/', 'ai_recognition' => true],
                    ['name' => 'Oryx', 'url' => 'https://www.oryxspioenkop.com/', 'focus' => 'losses_documentation'],
                    ['name' => 'Wikipedia Military Equipment', 'type' => 'reference'],
                ],
                'filters' => [
                    'key_identification_features' => [
                        'turret_shape',
                        'gun_caliber',
                        'track_design',
                        'era_placement',
                        'wing_configuration',
                        'engine_placement',
                        'hull_design',
                    ],
                ],
                'output_format' => [
                    'equipment_type' => true,
                    'variant' => true,
                    'operator_country' => true,
                    'specifications' => true,
                    'known_variants' => true,
                ],
                'requires_api_key' => false,
                'priority' => 85,
            ],

            // ===============================================
            // VERIFICATION AGENTS (10+ data points)
            // ===============================================
            [
                'name' => 'Video Verification Suite',
                'slug' => 'video-verification',
                'description' => 'Comprehensive video verification using InVID/WeVerify methodology',
                'type' => 'analyzer',
                'category' => 'verification',
                'configuration' => [
                    'keyframe_extraction' => true,
                    'metadata_analysis' => true,
                    'reverse_search' => true,
                    'deepfake_detection' => true,
                ],
                'data_sources' => [
                    ['name' => 'InVID/WeVerify Plugin', 'url' => 'https://www.invid-project.eu/', 'users' => '57000+'],
                    ['name' => 'YouTube DataViewer', 'url' => 'https://citizenevidence.amnestyusa.org/'],
                    ['name' => 'TinEye', 'purpose' => 'keyframe_search'],
                    ['name' => 'Google Lens', 'purpose' => 'keyframe_search'],
                    ['name' => 'vera.ai Deepfake Detector', 'purpose' => 'manipulation_detection'],
                ],
                'filters' => [
                    'verification_steps' => [
                        'extract_keyframes',
                        'analyze_metadata',
                        'reverse_search_keyframes',
                        'check_upload_history',
                        'verify_context',
                        'detect_manipulation',
                    ],
                    'platforms' => ['YouTube', 'Facebook', 'Twitter', 'TikTok', 'Telegram'],
                ],
                'triggers' => [
                    ['type' => 'manipulation_detected', 'confidence' => 0.7],
                    ['type' => 'older_version_found'],
                    ['type' => 'metadata_mismatch'],
                ],
                'output_format' => [
                    'verification_status' => true,
                    'original_source' => true,
                    'manipulation_analysis' => true,
                    'confidence_score' => true,
                ],
                'requires_api_key' => false,
                'priority' => 90,
            ],
            [
                'name' => 'Disinformation Pattern Detector',
                'slug' => 'disinfo-detector',
                'description' => 'Detects coordinated disinformation campaigns and manipulation patterns',
                'type' => 'analyzer',
                'category' => 'verification',
                'configuration' => [
                    'network_analysis' => true,
                    'temporal_analysis' => true,
                    'content_similarity' => true,
                    'bot_detection' => true,
                ],
                'data_sources' => [
                    ['name' => 'Social Media APIs', 'type' => 'primary'],
                    ['name' => 'Botometer', 'url' => 'https://botometer.osome.iu.edu/', 'purpose' => 'bot_detection'],
                    ['name' => 'Hoaxy', 'url' => 'https://hoaxy.osome.iu.edu/', 'purpose' => 'spread_tracking'],
                    ['name' => 'EUvsDisinfo', 'url' => 'https://euvsdisinfo.eu/', 'type' => 'database'],
                ],
                'filters' => [
                    'indicators' => [
                        'coordinated_posting',
                        'artificial_amplification',
                        'suspicious_account_patterns',
                        'content_farming',
                        'hashtag_hijacking',
                        'narrative_seeding',
                    ],
                ],
                'triggers' => [
                    ['type' => 'coordination_detected', 'accounts_threshold' => 10],
                    ['type' => 'bot_network_identified'],
                    ['type' => 'known_disinfo_narrative'],
                ],
                'requires_api_key' => true,
                'priority' => 75,
            ],

            // ===============================================
            // NEWS MONITORING AGENTS (10+ data points)
            // ===============================================
            [
                'name' => 'Breaking News Aggregator',
                'slug' => 'breaking-news',
                'description' => 'Aggregates breaking news from multiple sources with conflict focus',
                'type' => 'aggregator',
                'category' => 'news',
                'configuration' => [
                    'update_interval_seconds' => 60,
                    'languages' => ['en', 'ru', 'uk', 'ar', 'zh', 'fr', 'de'],
                    'deduplication' => true,
                    'sentiment_analysis' => true,
                ],
                'data_sources' => [
                    ['name' => 'Reuters', 'url' => 'https://www.reuters.com/', 'type' => 'wire_service'],
                    ['name' => 'AFP', 'url' => 'https://www.afp.com/', 'type' => 'wire_service'],
                    ['name' => 'AP News', 'url' => 'https://apnews.com/', 'type' => 'wire_service'],
                    ['name' => 'BBC', 'url' => 'https://www.bbc.com/', 'type' => 'broadcaster'],
                    ['name' => 'Al Jazeera', 'url' => 'https://www.aljazeera.com/', 'type' => 'broadcaster'],
                    ['name' => 'TASS', 'url' => 'https://tass.com/', 'type' => 'state_media', 'country' => 'Russia'],
                    ['name' => 'Ukrinform', 'url' => 'https://www.ukrinform.net/', 'type' => 'state_media', 'country' => 'Ukraine'],
                    ['name' => 'Interfax', 'url' => 'https://interfax.com/', 'type' => 'wire_service', 'country' => 'Russia'],
                ],
                'filters' => [
                    'topics' => ['military', 'conflict', 'diplomacy', 'sanctions', 'casualties', 'humanitarian'],
                    'regions' => ['Ukraine', 'Russia', 'Middle East', 'Asia Pacific'],
                    'exclude_topics' => ['entertainment', 'sports', 'lifestyle'],
                ],
                'triggers' => [
                    ['type' => 'breaking_tag'],
                    ['type' => 'multiple_sources', 'threshold' => 3],
                    ['type' => 'high_impact_keywords'],
                ],
                'output_format' => [
                    'deduplicated' => true,
                    'source_comparison' => true,
                    'sentiment_score' => true,
                ],
                'requires_api_key' => false,
                'priority' => 85,
            ],

            // ===============================================
            // TRANSLATION AGENTS (5+ data points)
            // ===============================================
            [
                'name' => 'Multi-Language Translator',
                'slug' => 'multi-translator',
                'description' => 'Translates OSINT content from multiple languages with context preservation',
                'type' => 'analyzer',
                'category' => 'translation',
                'configuration' => [
                    'source_languages' => ['ru', 'uk', 'ar', 'fa', 'zh', 'ko', 'he', 'tr'],
                    'target_language' => 'en',
                    'preserve_context' => true,
                    'military_terminology' => true,
                ],
                'data_sources' => [
                    ['name' => 'DeepL', 'url' => 'https://www.deepl.com/', 'quality' => 'high', 'languages' => 29],
                    ['name' => 'Google Translate', 'url' => 'https://translate.google.com/', 'languages' => 130],
                    ['name' => 'Yandex Translate', 'url' => 'https://translate.yandex.com/', 'strength' => 'russian'],
                    ['name' => 'Microsoft Translator', 'languages' => 100],
                ],
                'filters' => [
                    'special_handling' => [
                        'military_acronyms',
                        'unit_designations',
                        'place_names',
                        'slang_and_jargon',
                    ],
                ],
                'output_format' => [
                    'translation' => true,
                    'original_text' => true,
                    'detected_language' => true,
                    'confidence' => true,
                    'alternative_translations' => true,
                ],
                'requires_api_key' => true,
                'api_providers' => ['DeepL', 'Google'],
                'priority' => 70,
            ],

            // ===============================================
            // ALERT AGENTS (5+ data points)
            // ===============================================
            [
                'name' => 'Real-Time Conflict Alert',
                'slug' => 'conflict-alert',
                'description' => 'Generates real-time alerts for significant conflict events',
                'type' => 'alert',
                'category' => 'military',
                'configuration' => [
                    'alert_channels' => ['email', 'webhook', 'push', 'telegram'],
                    'severity_levels' => ['critical', 'high', 'medium', 'low'],
                    'consolidation_minutes' => 5,
                ],
                'data_sources' => [
                    ['name' => 'All Active Monitors', 'type' => 'internal'],
                    ['name' => 'ACLED', 'url' => 'https://acleddata.com/', 'type' => 'conflict_data'],
                    ['name' => 'GDELT', 'url' => 'https://www.gdeltproject.org/', 'type' => 'event_data'],
                ],
                'filters' => [
                    'event_types' => [
                        'major_attack',
                        'mass_casualties',
                        'territory_change',
                        'high_value_target',
                        'strategic_infrastructure',
                        'nuclear_facility',
                        'chemical_biological',
                    ],
                ],
                'triggers' => [
                    ['type' => 'casualty_threshold', 'count' => 10],
                    ['type' => 'strategic_target_hit'],
                    ['type' => 'wmd_indicator'],
                    ['type' => 'major_advance'],
                ],
                'output_format' => [
                    'alert_summary' => true,
                    'sources' => true,
                    'confidence' => true,
                    'map_link' => true,
                ],
                'schedule' => '* * * * *',
                'requires_api_key' => false,
                'priority' => 100,
            ],
        ];

        foreach ($agents as $agent) {
            DB::table('intelligence_agents')->insertOrIgnore([
                'uuid' => Str::uuid(),
                'name' => $agent['name'],
                'slug' => $agent['slug'],
                'description' => $agent['description'],
                'type' => $agent['type'],
                'category' => $agent['category'],
                'configuration' => json_encode($agent['configuration'] ?? []),
                'data_sources' => json_encode($agent['data_sources'] ?? []),
                'output_format' => json_encode($agent['output_format'] ?? []),
                'filters' => json_encode($agent['filters'] ?? []),
                'triggers' => json_encode($agent['triggers'] ?? []),
                'schedule' => $agent['schedule'] ?? null,
                'is_active' => true,
                'requires_api_key' => $agent['requires_api_key'] ?? false,
                'api_providers' => json_encode($agent['api_providers'] ?? []),
                'rate_limit_per_minute' => $agent['rate_limit_per_minute'] ?? null,
                'priority' => $agent['priority'] ?? 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
