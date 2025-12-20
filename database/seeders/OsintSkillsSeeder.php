<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OsintSkillsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Creates comprehensive OSINT skills with 100+ data points gathered from
     * extensive research on intelligence tools, techniques, and best practices.
     */
    public function run(): void
    {
        $now = now();

        // Create skill categories
        $categories = [
            ['name' => 'Geolocation', 'slug' => 'geolocation', 'description' => 'Techniques for determining location from imagery and data', 'icon' => 'map-pin', 'sort_order' => 1],
            ['name' => 'Image Analysis', 'slug' => 'image-analysis', 'description' => 'Tools and techniques for analyzing photographs', 'icon' => 'image', 'sort_order' => 2],
            ['name' => 'Video Verification', 'slug' => 'video-verification', 'description' => 'Verifying authenticity and metadata of videos', 'icon' => 'video', 'sort_order' => 3],
            ['name' => 'Flight Tracking', 'slug' => 'flight-tracking', 'description' => 'ADS-B and aircraft monitoring techniques', 'icon' => 'plane', 'sort_order' => 4],
            ['name' => 'Maritime Intelligence', 'slug' => 'maritime-intelligence', 'description' => 'AIS ship tracking and naval analysis', 'icon' => 'ship', 'sort_order' => 5],
            ['name' => 'Satellite Imagery', 'slug' => 'satellite-imagery', 'description' => 'Satellite data sources and analysis', 'icon' => 'satellite', 'sort_order' => 6],
            ['name' => 'Social Media OSINT', 'slug' => 'social-media', 'description' => 'Intelligence from social platforms', 'icon' => 'share-2', 'sort_order' => 7],
            ['name' => 'Weapons Identification', 'slug' => 'weapons-identification', 'description' => 'Military equipment and ordnance recognition', 'icon' => 'crosshairs', 'sort_order' => 8],
            ['name' => 'Unit Identification', 'slug' => 'unit-identification', 'description' => 'Military insignia, patches, and symbols', 'icon' => 'shield', 'sort_order' => 9],
            ['name' => 'Verification Tools', 'slug' => 'verification-tools', 'description' => 'Fact-checking and verification resources', 'icon' => 'check-circle', 'sort_order' => 10],
        ];

        foreach ($categories as $category) {
            DB::table('osint_skill_categories')->insert([
                'uuid' => Str::uuid(),
                ...$category,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Get category IDs
        $categoryIds = DB::table('osint_skill_categories')->pluck('id', 'slug');

        // Create skills with comprehensive data points
        $skills = [
            // ===============================================
            // GEOLOCATION SKILLS (15+ data points)
            // ===============================================
            [
                'category_id' => $categoryIds['geolocation'],
                'name' => 'Shadow Analysis',
                'slug' => 'shadow-analysis',
                'description' => 'Using shadows to determine time and location from imagery',
                'difficulty' => 'intermediate',
                'data' => json_encode([
                    'overview' => 'Shadows in images provide clues about when and where an image was taken by analyzing sun position.',
                    'key_concepts' => [
                        'Subsolar point - the single point on Earth where the sun is directly overhead',
                        'Shadow length increases as distance from subsolar point increases',
                        'Shadow direction indicates cardinal orientation relative to sun',
                        'Vertical objects cast measurable shadows for analysis',
                    ],
                    'tools' => [
                        ['name' => 'SunCalc', 'url' => 'https://www.suncalc.org/', 'purpose' => 'Calculate sun position for any time and location'],
                        ['name' => 'Bellingcat Shadow Finder', 'url' => 'https://www.bellingcat.com/resources/2024/08/22/shadow-geolocate-geolocation-locate-image-tool-open-source-bellingcat-measure/', 'purpose' => 'Narrow down image location using shadow analysis'],
                        ['name' => 'Google Earth Pro', 'url' => 'https://www.google.com/earth/versions/', 'purpose' => 'Measure object heights and shadow lengths'],
                    ],
                    'methodology' => [
                        'Identify a vertical shadow-casting object (pole, lamppost, building edge)',
                        'Measure object height and shadow length ratio',
                        'Note shadow direction (bearing) relative to North',
                        'Input data into shadow calculator with date range',
                        'Cross-reference with other geolocation clues',
                    ],
                    'best_practices' => [
                        'Use multiple images taken at different times for confirmation',
                        'Account for terrain slope affecting shadow length',
                        'Consider daylight saving time differences',
                        'Combine with landmark analysis for verification',
                    ],
                    'sources' => [
                        'Bellingcat - Using the Sun and Shadows for Geolocation (2020)',
                        'GIJN - Using the Sun and Shadows for Geolocating Photos and Videos',
                    ],
                ]),
            ],
            [
                'category_id' => $categoryIds['geolocation'],
                'name' => 'Landmark Recognition',
                'slug' => 'landmark-recognition',
                'description' => 'Identifying locations through visible landmarks and infrastructure',
                'difficulty' => 'beginner',
                'data' => json_encode([
                    'overview' => 'Use visible landmarks, buildings, and infrastructure to pinpoint locations.',
                    'key_elements' => [
                        'Distinctive buildings and architecture',
                        'Street signs and road markings',
                        'Vegetation and landscape features',
                        'Infrastructure (power lines, utility poles)',
                        'Advertising and signage (language clues)',
                        'Vehicle license plates and road traffic patterns',
                    ],
                    'tools' => [
                        ['name' => 'Google Maps/Street View', 'purpose' => 'Cross-reference visible features'],
                        ['name' => 'Yandex Maps', 'purpose' => 'Better coverage in Russia/Eastern Europe'],
                        ['name' => 'Mapillary', 'url' => 'https://www.mapillary.com/', 'purpose' => 'Crowdsourced street-level imagery'],
                        ['name' => 'OpenStreetMap', 'url' => 'https://www.openstreetmap.org/', 'purpose' => 'Detailed mapping data'],
                    ],
                    'regional_indicators' => [
                        'Eastern Europe' => 'Cyrillic signage, Soviet-era architecture, specific road marking styles',
                        'Middle East' => 'Arabic script, distinctive mosque architecture, desert terrain',
                        'East Asia' => 'Chinese/Japanese/Korean characters, specific building styles',
                        'Western Europe' => 'Roman alphabet variations, EU road signs, architectural styles',
                    ],
                ]),
            ],

            // ===============================================
            // IMAGE ANALYSIS SKILLS (15+ data points)
            // ===============================================
            [
                'category_id' => $categoryIds['image-analysis'],
                'name' => 'Reverse Image Search',
                'slug' => 'reverse-image-search',
                'description' => 'Finding image origins and variations using search engines',
                'difficulty' => 'beginner',
                'data' => json_encode([
                    'overview' => 'Search the internet using an image to find its origins, variations, and usage.',
                    'tools' => [
                        ['name' => 'Google Lens', 'url' => 'https://lens.google/', 'strength' => 'General web-wide searches, object identification'],
                        ['name' => 'TinEye', 'url' => 'https://tineye.com/', 'strength' => 'Finding exact and modified versions, 72+ billion image database'],
                        ['name' => 'Yandex Images', 'url' => 'https://yandex.com/images/', 'strength' => 'Best for facial recognition and Eastern European content'],
                        ['name' => 'Bing Visual Search', 'url' => 'https://www.bing.com/visualsearch', 'strength' => 'Good alternative coverage'],
                        ['name' => 'Baidu Image Search', 'url' => 'https://image.baidu.com/', 'strength' => 'Best for Chinese content'],
                    ],
                    'browser_extensions' => [
                        ['name' => 'RevEye', 'purpose' => 'Multi-engine reverse image search (Google, Bing, Yandex, Baidu, TinEye)'],
                        ['name' => 'Search By Image', 'purpose' => 'Right-click search across multiple engines'],
                    ],
                    'best_practices' => [
                        'Use multiple search engines - no single engine covers entire web',
                        'Crop image to focus on key details before searching',
                        'Try different image sections separately',
                        'Check TinEye for exact matches and modifications',
                        'Use Yandex for facial recognition queries',
                    ],
                    'applications' => [
                        'Debunking fake news and viral misinformation',
                        'Investigating scams and fraud',
                        'Verifying source authenticity',
                        'Tracking image manipulation history',
                    ],
                ]),
            ],
            [
                'category_id' => $categoryIds['image-analysis'],
                'name' => 'EXIF Metadata Analysis',
                'slug' => 'exif-metadata',
                'description' => 'Extracting and analyzing image metadata',
                'difficulty' => 'beginner',
                'data' => json_encode([
                    'overview' => 'EXIF (Exchangeable Image File Format) data contains camera settings, timestamps, and potentially GPS coordinates.',
                    'common_metadata' => [
                        'Date and time taken',
                        'Camera make and model',
                        'GPS coordinates (if enabled)',
                        'Lens information',
                        'Aperture, shutter speed, ISO',
                        'Software used for editing',
                        'Thumbnail preview',
                    ],
                    'tools' => [
                        ['name' => 'ExifTool', 'url' => 'https://exiftool.org/', 'purpose' => 'Comprehensive metadata extraction'],
                        ['name' => 'Jeffrey Exif Viewer', 'url' => 'http://exif.regex.info/exif.cgi', 'purpose' => 'Online EXIF analysis'],
                        ['name' => 'InVID/WeVerify Plugin', 'purpose' => 'Integrated metadata extraction'],
                    ],
                    'caveats' => [
                        'Social media platforms often strip EXIF data',
                        'Metadata can be manipulated',
                        'Screenshots lose original metadata',
                        'Some cameras/phones have GPS disabled by default',
                    ],
                    'verification_steps' => [
                        'Check if metadata is present and consistent',
                        'Verify camera model matches image quality',
                        'Cross-reference GPS with visible landmarks',
                        'Check for editing software traces',
                    ],
                ]),
            ],

            // ===============================================
            // VIDEO VERIFICATION SKILLS (10+ data points)
            // ===============================================
            [
                'category_id' => $categoryIds['video-verification'],
                'name' => 'InVID/WeVerify Toolkit',
                'slug' => 'invid-weverify',
                'description' => 'Using the InVID/WeVerify verification plugin for video analysis',
                'difficulty' => 'intermediate',
                'data' => json_encode([
                    'overview' => 'The InVID-WeVerify plugin is a comprehensive verification "Swiss army knife" for journalists and fact-checkers.',
                    'user_base' => '57,000+ weekly active users (journalists, fact-checkers, OSINT researchers, law enforcement)',
                    'recognition' => 'Poynter Institute: "one of the most powerful tools for spotting misinformation online"',
                    'features' => [
                        'Video fragmentation into keyframes',
                        'Reverse image search on multiple engines',
                        'Metadata extraction from videos and images',
                        'Contextual information from YouTube/Facebook',
                        'Twitter search with time filters',
                        'Magnifying lens for keyframe analysis',
                        'Deepfake detection (experimental)',
                    ],
                    'supported_platforms' => ['YouTube', 'Facebook', 'Twitter/X', 'Instagram'],
                    'search_engines' => ['Google', 'Google Lens', 'Bing', 'TinEye', 'Yandex', 'Baidu', 'Karma Decay'],
                    'deepfake_detection' => [
                        'Built by ITI CERTH under EU vera.ai project',
                        'Breaks video into frames and detects faces',
                        'Uses ensemble of 5 CNN-based detectors',
                        'Outputs 0-1 probability score for AI manipulation',
                    ],
                    'consortium' => [
                        'AFP Medialab (lead development)',
                        'Deutsche Welle',
                        'CERTH (Centre for Research and Technology Hellas)',
                        'Funded by EU Horizon 2020',
                    ],
                    'installation' => 'Available as Chrome/Firefox browser extension',
                ]),
            ],
            [
                'category_id' => $categoryIds['video-verification'],
                'name' => 'Chronolocation',
                'slug' => 'chronolocation',
                'description' => 'Determining when a video or image was taken',
                'difficulty' => 'advanced',
                'data' => json_encode([
                    'overview' => 'Chronolocation determines the time a video/image was captured using environmental clues.',
                    'methods' => [
                        'Shadow analysis with SunCalc',
                        'Weather data correlation',
                        'Astronomical events (moon phase, star positions)',
                        'Seasonal vegetation changes',
                        'Event correlation (news, sports, protests)',
                        'Clock/watch faces visible in footage',
                        'TV/computer screens showing timestamped content',
                    ],
                    'tools' => [
                        ['name' => 'SunCalc', 'url' => 'https://www.suncalc.org/', 'purpose' => 'Sun position and shadow direction'],
                        ['name' => 'Wolfram Alpha', 'purpose' => 'Moon phases, astronomical calculations'],
                        ['name' => 'Weather Underground History', 'purpose' => 'Historical weather data'],
                        ['name' => 'Stellarium', 'url' => 'https://stellarium.org/', 'purpose' => 'Star position simulation'],
                    ],
                    'validation' => [
                        'Cross-reference multiple time indicators',
                        'Check for consistency with known events',
                        'Verify timezone and daylight saving time',
                        'Consider camera clock accuracy',
                    ],
                ]),
            ],

            // ===============================================
            // FLIGHT TRACKING SKILLS (15+ data points)
            // ===============================================
            [
                'category_id' => $categoryIds['flight-tracking'],
                'name' => 'ADS-B Flight Tracking',
                'slug' => 'adsb-tracking',
                'description' => 'Using ADS-B data for aircraft surveillance and analysis',
                'difficulty' => 'intermediate',
                'data' => json_encode([
                    'overview' => 'ADS-B (Automatic Dependent Surveillance-Broadcast) allows tracking of aircraft transmitting their position.',
                    'how_it_works' => 'Aircraft determine position via GPS and broadcast it periodically, enabling ground-based receivers to track flights.',
                    'platforms' => [
                        ['name' => 'ADS-B Exchange', 'url' => 'https://globe.adsbexchange.com/', 'type' => 'unfiltered', 'description' => 'World\'s largest unfiltered flight data source, best for military/government aircraft'],
                        ['name' => 'Flightradar24', 'url' => 'https://www.flightradar24.com/', 'type' => 'commercial', 'description' => 'Best for commercial flights, user-friendly interface'],
                        ['name' => 'OpenSky Network', 'url' => 'https://opensky-network.org/', 'type' => 'research', 'description' => 'Non-profit, open access for research purposes'],
                        ['name' => 'adsb.fi', 'url' => 'https://adsb.fi/', 'type' => 'unfiltered', 'description' => 'Community-driven, unfiltered data'],
                        ['name' => 'ADSB.lol', 'url' => 'https://www.adsb.lol/', 'type' => 'open-source', 'description' => 'Fully open-source with free API and historical data'],
                    ],
                    'military_intelligence' => [
                        'ADS-B Exchange captures military helicopters and aircraft that commercial services may filter',
                        'Combine with satellite imagery to identify aircraft types',
                        'Note: Russian military aircraft typically do not use ADS-B',
                        'Used for OSINT on military movements for 10+ years',
                    ],
                    'use_cases' => [
                        'Track surveillance and reconnaissance aircraft',
                        'Monitor military exercises',
                        'Identify refueling operations',
                        'Document airspace violations',
                        'Verify claimed flight paths',
                    ],
                    'diy_tracking' => [
                        'RTL-SDR USB dongle ($25-40)',
                        'Antenna (DIY or commercial)',
                        'Software: dump1090, readsb',
                        'Feed data to multiple services',
                    ],
                    'limitations' => [
                        'Not all aircraft transmit ADS-B',
                        'Military aircraft often disable transponders',
                        'Coverage depends on receiver network density',
                        'Some services filter sensitive flights',
                    ],
                ]),
            ],

            // ===============================================
            // MARITIME INTELLIGENCE SKILLS (15+ data points)
            // ===============================================
            [
                'category_id' => $categoryIds['maritime-intelligence'],
                'name' => 'AIS Ship Tracking',
                'slug' => 'ais-tracking',
                'description' => 'Using Automatic Identification System data for vessel monitoring',
                'difficulty' => 'intermediate',
                'data' => json_encode([
                    'overview' => 'AIS is an automatic tracking system using transponders on ships, originally designed for collision avoidance.',
                    'platforms' => [
                        ['name' => 'MarineTraffic', 'url' => 'https://www.marinetraffic.com/', 'database' => '550,000+ vessels', 'languages' => 34, 'features' => 'Real-time positions, routes, port arrivals'],
                        ['name' => 'VesselFinder', 'url' => 'https://www.vesselfinder.com/', 'features' => 'More up-to-date data without subscription'],
                        ['name' => 'MyShipTracking', 'url' => 'https://www.myshiptracking.com/', 'features' => 'Free real-time tracking'],
                        ['name' => 'AISHub', 'url' => 'https://www.aishub.net/', 'features' => 'Free data exchange, API access'],
                    ],
                    'data_available' => [
                        'Vessel position (latitude, longitude)',
                        'Speed and heading',
                        'Vessel type and dimensions',
                        'Flag state and IMO number',
                        'Destination and ETA',
                        'Navigation status',
                        'Historical track data',
                    ],
                    'osint_applications' => [
                        'Tracking sanctions violations',
                        'Monitoring naval activities',
                        'Investigating illegal fishing',
                        'Documenting blockade enforcement',
                        'Economic analysis (shipping volume)',
                        'Refugee/migration route monitoring',
                    ],
                    'technical_details' => [
                        'T-AIS range: ~74 km (line of sight)',
                        'S-AIS: Satellite-based reception for ocean coverage',
                        'Class A: Mandatory for ships >300 GT',
                        'Class B: Smaller vessels, less frequent transmission',
                    ],
                    'limitations' => [
                        'AIS can be turned off or spoofed',
                        'Coverage gaps in remote areas',
                        'Military vessels may not transmit',
                        'Small boats exempt from requirements',
                    ],
                    'alternative_tools' => [
                        'Shodan - Search for ship communication systems',
                        'Sentinel-1 SAR - Detect vessels without AIS',
                    ],
                ]),
            ],

            // ===============================================
            // SATELLITE IMAGERY SKILLS (15+ data points)
            // ===============================================
            [
                'category_id' => $categoryIds['satellite-imagery'],
                'name' => 'Satellite Data Sources',
                'slug' => 'satellite-sources',
                'description' => 'Understanding satellite imagery providers and their capabilities',
                'difficulty' => 'intermediate',
                'data' => json_encode([
                    'overview' => 'Satellite imagery provides crucial evidence for conflict monitoring, damage assessment, and change detection.',
                    'providers' => [
                        [
                            'name' => 'Sentinel-2',
                            'operator' => 'ESA (European Space Agency)',
                            'resolution' => '10m',
                            'revisit' => '5 days',
                            'cost' => 'Free',
                            'access' => 'Copernicus Open Access Hub',
                            'best_for' => 'Large-scale environmental changes',
                        ],
                        [
                            'name' => 'Sentinel-1',
                            'operator' => 'ESA',
                            'type' => 'SAR (Synthetic Aperture Radar)',
                            'resolution' => '5-20m',
                            'advantage' => 'Works through clouds and at night',
                            'cost' => 'Free',
                        ],
                        [
                            'name' => 'Planet Labs',
                            'resolution' => '3m',
                            'revisit' => 'Daily',
                            'ethos' => 'Image entire globe every day',
                            'cost' => 'Commercial (free for some uses)',
                        ],
                        [
                            'name' => 'Maxar',
                            'resolution' => '0.3-0.5m',
                            'description' => 'Extremely powerful but less frequent',
                            'cost' => 'Commercial',
                        ],
                    ],
                    'access_tools' => [
                        ['name' => 'Sentinel Hub', 'url' => 'https://www.sentinel-hub.com/', 'purpose' => 'Easy access to ESA data'],
                        ['name' => 'Google Earth Engine', 'purpose' => 'Cloud analysis platform'],
                        ['name' => 'EOS Data Analytics', 'url' => 'https://eos.com/', 'purpose' => 'Sentinel-2 visualization'],
                    ],
                    'damage_assessment' => [
                        'Combine Sentinel-1 (radar) + Sentinel-2 (optical)',
                        'SAR log ratio for building damage detection',
                        'Texture analysis for structural changes',
                        'Machine learning models for automated detection',
                        'Google Earth Engine for scalable processing',
                    ],
                    'challenges' => [
                        'Cloud cover limits optical imagery',
                        'High-resolution imagery is expensive',
                        '10m resolution insufficient for building details',
                        'Requires verification with higher-res sources',
                    ],
                    'research_applications' => [
                        'UN damage assessments (schools, hospitals)',
                        'Ukraine conflict building damage mapping',
                        'Environmental monitoring of conflict zones',
                        'Infrastructure destruction tracking',
                    ],
                ]),
            ],

            // ===============================================
            // SOCIAL MEDIA OSINT SKILLS (15+ data points)
            // ===============================================
            [
                'category_id' => $categoryIds['social-media'],
                'name' => 'Twitter/X Investigation',
                'slug' => 'twitter-x-osint',
                'description' => 'Intelligence gathering from Twitter/X platform',
                'difficulty' => 'intermediate',
                'data' => json_encode([
                    'overview' => 'X (Twitter) is a "digital public square" - transparent, reactive, and valuable for real-time OSINT.',
                    'unique_characteristics' => [
                        'Almost entirely public by design',
                        'Real-time event coverage',
                        'Users frequently share personal information',
                        'Trending hashtags reveal sentiment',
                    ],
                    'investigation_uses' => [
                        'Journalism - verifying sources, exposing bad actors',
                        'Law enforcement - tracking criminal activity, extremism',
                        'Corporate - brand monitoring, threat identification',
                        'OSINT - real-time conflict documentation',
                    ],
                    'tools' => [
                        ['name' => 'twscrape', 'purpose' => 'Modern scraping tool using authenticated sessions'],
                        ['name' => 'InVID Twitter Search', 'purpose' => 'Time-filtered Twitter queries'],
                        ['name' => 'TweetDeck', 'purpose' => 'Multi-column monitoring'],
                        ['name' => 'Wayback Machine', 'purpose' => 'Archived deleted tweets'],
                    ],
                    'search_operators' => [
                        'from:username - tweets from specific user',
                        'to:username - tweets to specific user',
                        'since:YYYY-MM-DD until:YYYY-MM-DD - date range',
                        'geocode:lat,long,radius - location filter',
                        'filter:images/videos - media only',
                        'lang:xx - language filter',
                    ],
                    'challenges' => [
                        'Increased misinformation since policy changes',
                        'Legacy verification checkmarks removed',
                        'API access restrictions',
                        'Impersonation issues',
                    ],
                    'best_practices' => [
                        'Cross-verify information from multiple sources',
                        'Check account creation date and history',
                        'Analyze follower/following patterns',
                        'Be skeptical of viral unverified content',
                    ],
                ]),
            ],
            [
                'category_id' => $categoryIds['social-media'],
                'name' => 'Telegram Investigation',
                'slug' => 'telegram-osint',
                'description' => 'Intelligence gathering from Telegram channels and groups',
                'difficulty' => 'advanced',
                'data' => json_encode([
                    'overview' => 'Telegram sits between social media and messaging apps, with transparent public channels and private communications.',
                    'osint_value' => [
                        'Public channels accessible without membership',
                        'Used by activists, threat actors, and journalists',
                        'Critical for extremism and threat intelligence',
                        'Illicit marketplace monitoring',
                    ],
                    'investigation_capabilities' => [
                        'Map channel ecosystems',
                        'Identify admins and key figures',
                        'Trace content propagation',
                        'Monitor messaging flows',
                        'Detect coordinated campaigns',
                    ],
                    'tools' => [
                        ['name' => 'Telegram Scraper (Telethon)', 'purpose' => 'Collect messages and media from public channels'],
                        ['name' => 'Gephi', 'purpose' => 'Network visualization and community detection'],
                        ['name' => 'Maltego', 'purpose' => 'Relationship mapping with Telegram integrations'],
                        ['name' => 'Osavul', 'url' => 'https://www.osavul.cloud/', 'purpose' => 'Bot detection algorithms'],
                    ],
                    'network_analysis' => [
                        'Centrality metrics to identify key figures',
                        'Community detection algorithms',
                        'Message flow tracing between channels',
                        'Influence campaign detection',
                    ],
                    'challenges' => [
                        'Semi-encrypted structure',
                        'Privacy-focused design',
                        'Requires Telegram API access',
                        'Private groups need invites',
                    ],
                ]),
            ],

            // ===============================================
            // WEAPONS IDENTIFICATION SKILLS (15+ data points)
            // ===============================================
            [
                'category_id' => $categoryIds['weapons-identification'],
                'name' => 'Explosive Ordnance Identification',
                'slug' => 'ordnance-identification',
                'description' => 'Identifying bombs, missiles, and munitions from imagery',
                'difficulty' => 'advanced',
                'data' => json_encode([
                    'overview' => 'Identifying explosive ordnance from conflict zone imagery is critical for documentation and accountability.',
                    'identification_methods' => [
                        [
                            'method' => 'Text and Markings',
                            'details' => [
                                'Factory labels and stamps indicate model type',
                                'Manufacture date and production lot',
                                'CAGE codes (Commercial and Government Entity) for NATO weapons',
                                'Cyrillic markings for Russian/Soviet ordnance',
                            ],
                        ],
                        [
                            'method' => 'Physical Features',
                            'details' => [
                                'Shape and dimensions',
                                'Fins, blades, or guidance systems',
                                'Color coding (varies by country)',
                                'Nose cone/fuse design',
                            ],
                        ],
                    ],
                    'resources' => [
                        ['name' => 'Open Source Munitions Portal', 'url' => 'https://osmp.ngo/', 'purpose' => 'Detailed munition markings database'],
                        ['name' => 'GIJN Ordnance Guide', 'url' => 'https://gijn.org/stories/guide-identifying-explosive-ordnance-social-media/', 'purpose' => 'Beginner identification guide'],
                        ['name' => 'CAR (Conflict Armament Research)', 'purpose' => 'Weapons tracing methodology'],
                    ],
                    'common_munitions' => [
                        'MK 80 series (US) - 500/1000/2000 lb bombs',
                        'FAB series (Russia) - general purpose bombs',
                        'JDAM - GPS-guided bombs',
                        'Cluster munitions and submunitions',
                        'GRAD rockets (122mm)',
                        'HIMARS rockets (227mm)',
                    ],
                    'cage_codes' => 'NATO CAGE codes can be searched in online database to trace weapon origin - used in Syria and Yemen investigations',
                    'global_impact' => [
                        '26,000 people killed/maimed annually by UXO',
                        '78 countries contaminated by landmines',
                        '41+ countries affected by cluster munitions',
                        'Laos: 288 million cluster munitions dropped, 30% UXO rate',
                    ],
                    'banned_weapons' => [
                        'Cluster munitions - banned by 100+ countries',
                        'Anti-personnel mines - Ottawa Treaty',
                        'Chemical weapons - CWC',
                    ],
                ]),
            ],
            [
                'category_id' => $categoryIds['weapons-identification'],
                'name' => 'Military Equipment Recognition',
                'slug' => 'equipment-recognition',
                'description' => 'Identifying tanks, vehicles, aircraft, and ships',
                'difficulty' => 'intermediate',
                'data' => json_encode([
                    'overview' => 'Visual recognition of military equipment is essential for conflict documentation.',
                    'resources' => [
                        ['name' => 'Army Recognition', 'url' => 'https://www.armyrecognition.com/', 'coverage' => 'Global database covering land, naval, aerospace'],
                        ['name' => 'Military.com Equipment Guide', 'url' => 'https://www.military.com/equipment', 'features' => 'Photos, specifications, recognition quizzes'],
                        ['name' => 'MEGA App', 'url' => 'https://militaryequipmentguide.com/', 'features' => 'AI-driven image recognition for land equipment'],
                        ['name' => 'Joint Equipment Characteristics Database', 'purpose' => 'Official US military specifications'],
                    ],
                    'official_manuals' => [
                        ['name' => 'FM 44-80', 'purpose' => 'Visual Aircraft Recognition (US Army)'],
                        ['name' => 'Navy Ship Recognition', 'purpose' => 'Silhouette guides for naval vessels'],
                    ],
                    'key_features' => [
                        'tanks' => ['Turret shape', 'Gun caliber', 'Track design', 'ERA placement', 'Smoke launchers'],
                        'aircraft' => ['Wing configuration', 'Engine placement', 'Tail design', 'Cockpit shape'],
                        'ships' => ['Hull design', 'Superstructure', 'Weapons systems', 'Radar arrays'],
                    ],
                    'nomenclature' => [
                        'AN designation system (JETDS) for US military electronics',
                        'NATO reporting names for Russian/Soviet equipment',
                        'Model designations vary by country of origin',
                    ],
                ]),
            ],

            // ===============================================
            // UNIT IDENTIFICATION SKILLS (10+ data points)
            // ===============================================
            [
                'category_id' => $categoryIds['unit-identification'],
                'name' => 'Ukrainian Military Insignia',
                'slug' => 'ukraine-insignia',
                'description' => 'Identifying Ukrainian military units from patches and emblems',
                'difficulty' => 'intermediate',
                'data' => json_encode([
                    'overview' => 'Ukrainian military insignia have evolved significantly since 2014, with modern designs reflecting tradition and identity.',
                    'patch_placement' => [
                        'Right sleeve: Branch/service type emblem',
                        'Left sleeve: Specific unit insignia',
                        'Morale patches: Unofficial, individual expression',
                    ],
                    'branch_symbols' => [
                        'Army' => 'Crossed swords, shields',
                        'Navy' => 'Anchors, maritime imagery',
                        'Air Force' => 'Wings, aircraft silhouettes',
                        'Special Operations' => 'Silver werewolf with golden eyes (according to Cossack legend)',
                    ],
                    'color_significance' => [
                        'Blue/Yellow bands' => 'National colors',
                        'Green' => 'Ground forces camouflage',
                        'Red/Black' => 'Special operations, artillery',
                    ],
                    'notable_examples' => [
                        '10th Mountain Assault Brigade' => 'Carpathian animal/bird, edelweiss flower, Hutsul battle axes, mountains',
                        'National Guard' => 'Distinctive shield with trident',
                        'Territorial Defense' => 'Regional symbols',
                    ],
                    'official_process' => [
                        '110 insignia reviewed January-June 2025',
                        'Ministry of Defence designs and approves',
                        'Blend of historical tradition and modern design',
                    ],
                    'nato_standardization' => 'Ukraine adopted NATO STANAG 2116 rank structure on October 17, 2019',
                    'resources' => [
                        ['name' => 'UNITED24 Media', 'purpose' => 'Official chevron symbolism'],
                        ['name' => 'Wikimedia Commons', 'purpose' => 'Visual database of insignia'],
                    ],
                ]),
            ],
            [
                'category_id' => $categoryIds['unit-identification'],
                'name' => 'Russian Military Markings',
                'slug' => 'russia-markings',
                'description' => 'Identifying Russian military vehicles and unit markings',
                'difficulty' => 'intermediate',
                'data' => json_encode([
                    'overview' => 'Russian military vehicles use tactical markings for identification and operational control.',
                    'invasion_symbols' => [
                        ['symbol' => 'Z', 'meaning' => 'Most common, transcended military use as pro-war symbol', 'origin' => 'Possibly for "Zapad" (West) or vehicle numbering'],
                        ['symbol' => 'V', 'meaning' => 'Often seen on vehicles from certain military districts'],
                        ['symbol' => 'O', 'meaning' => 'Circle marking for specific units'],
                        ['symbol' => 'A', 'meaning' => 'Used by certain mechanized units'],
                    ],
                    'purpose' => [
                        'Prevent friendly fire incidents',
                        'Identify different combat groups/axis of advance',
                        'Mark vehicles from different military districts',
                    ],
                    'unit_patches' => [
                        'Similar to Ukrainian system for identifying unit affiliation',
                        'Wagner Group used distinctive skull/crossbones imagery',
                        'VDV (Airborne) use distinctive blue berets and insignia',
                    ],
                    'vehicle_numbers' => [
                        'Tactical numbers painted on hulls/turrets',
                        'Unit code systems vary by formation',
                        'Bumper numbers indicate parent unit',
                    ],
                ]),
            ],

            // ===============================================
            // VERIFICATION TOOLS (10+ data points)
            // ===============================================
            [
                'category_id' => $categoryIds['verification-tools'],
                'name' => 'Fact-Checking Methodology',
                'slug' => 'fact-checking',
                'description' => 'Systematic approaches to verifying information',
                'difficulty' => 'beginner',
                'data' => json_encode([
                    'overview' => 'Verification is the cornerstone of quality OSINT - ensuring accuracy prevents flawed analysis.',
                    'core_principles' => [
                        'Cross-reference multiple independent sources',
                        'Verify the authenticity of original sources',
                        'Check for manipulation or editing',
                        'Consider context and timing',
                        'Document your verification process',
                    ],
                    'verification_checklist' => [
                        'Source' => 'Who created/shared this? Are they credible?',
                        'Date' => 'When was this created? Is timing consistent?',
                        'Location' => 'Where was this taken? Can landmarks verify?',
                        'Motivation' => 'Why was this shared? Potential bias?',
                        'Manipulation' => 'Has this been edited or taken out of context?',
                    ],
                    'tools_summary' => [
                        'Images' => 'Reverse image search, EXIF analysis, manipulation detection',
                        'Videos' => 'InVID/WeVerify, keyframe extraction, metadata check',
                        'Accounts' => 'Creation date, posting history, network analysis',
                        'Claims' => 'Cross-reference with trusted sources, fact-check databases',
                    ],
                    'fact_check_organizations' => [
                        ['name' => 'Bellingcat', 'url' => 'https://www.bellingcat.com/', 'focus' => 'Conflict, human rights'],
                        ['name' => 'AFP Fact Check', 'focus' => 'Global news verification'],
                        ['name' => 'Reuters Fact Check', 'focus' => 'News and social media claims'],
                        ['name' => 'Snopes', 'url' => 'https://www.snopes.com/', 'focus' => 'General fact-checking'],
                    ],
                    'red_flags' => [
                        'Emotional/sensational framing',
                        'Anonymous or unverified sources',
                        'Inconsistent details',
                        'Pressure to share immediately',
                        'Claims that can\'t be verified',
                    ],
                ]),
            ],
        ];

        foreach ($skills as $skill) {
            DB::table('osint_skills')->insert([
                'uuid' => Str::uuid(),
                'category_id' => $skill['category_id'],
                'name' => $skill['name'],
                'slug' => $skill['slug'],
                'description' => $skill['description'],
                'difficulty' => $skill['difficulty'],
                'data' => $skill['data'],
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
