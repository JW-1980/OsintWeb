<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | OSINT Platform Settings
    |--------------------------------------------------------------------------
    |
    | Core configuration for the military conflict tracking and OSINT platform.
    | These settings control map behavior, event types, intelligence gathering,
    | and analysis features.
    |
    */

    'map' => [
        'default_center' => [
            'latitude' => env('MAP_DEFAULT_LAT', 50.4501),
            'longitude' => env('MAP_DEFAULT_LNG', 30.5234),
        ],
        'default_zoom' => (int) env('MAP_DEFAULT_ZOOM', 6),
        'min_zoom' => (int) env('MAP_MIN_ZOOM', 3),
        'max_zoom' => (int) env('MAP_MAX_ZOOM', 18),
        'tile_providers' => [
            'osm' => [
                'name' => 'OpenStreetMap',
                'url' => 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
                'attribution' => '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                'max_zoom' => 19,
            ],
            'osm_humanitarian' => [
                'name' => 'Humanitarian',
                'url' => 'https://{s}.tile.openstreetmap.fr/hot/{z}/{x}/{y}.png',
                'attribution' => '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors, Tiles style by <a href="https://www.hotosm.org/">Humanitarian OpenStreetMap Team</a>',
                'max_zoom' => 20,
            ],
            'satellite' => [
                'name' => 'Satellite',
                'url' => 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
                'attribution' => 'Tiles &copy; Esri',
                'max_zoom' => 18,
            ],
        ],
        'cluster_radius' => (int) env('MAP_CLUSTER_RADIUS', 80),
        'enable_clustering' => env('MAP_ENABLE_CLUSTERING', true),
        'auto_refresh_interval' => (int) env('MAP_AUTO_REFRESH_INTERVAL', 30000),
    ],

    'events' => [
        'types' => [
            'military_movement' => [
                'label' => 'Military Movement',
                'icon' => 'truck',
                'color' => '#3b82f6',
                'severity' => 'low',
            ],
            'equipment_deployment' => [
                'label' => 'Equipment Deployment',
                'icon' => 'shield',
                'color' => '#8b5cf6',
                'severity' => 'medium',
            ],
            'combat_engagement' => [
                'label' => 'Combat Engagement',
                'icon' => 'fire',
                'color' => '#ef4444',
                'severity' => 'high',
            ],
            'artillery_strike' => [
                'label' => 'Artillery Strike',
                'icon' => 'target',
                'color' => '#dc2626',
                'severity' => 'high',
            ],
            'aerial_activity' => [
                'label' => 'Aerial Activity',
                'icon' => 'plane',
                'color' => '#06b6d4',
                'severity' => 'medium',
            ],
            'naval_movement' => [
                'label' => 'Naval Movement',
                'icon' => 'anchor',
                'color' => '#0284c7',
                'severity' => 'medium',
            ],
            'infrastructure_damage' => [
                'label' => 'Infrastructure Damage',
                'icon' => 'building',
                'color' => '#f97316',
                'severity' => 'medium',
            ],
            'civilian_casualty' => [
                'label' => 'Civilian Casualty',
                'icon' => 'users',
                'color' => '#b91c1c',
                'severity' => 'critical',
            ],
            'fortification' => [
                'label' => 'Fortification',
                'icon' => 'castle',
                'color' => '#64748b',
                'severity' => 'low',
            ],
            'supply_convoy' => [
                'label' => 'Supply Convoy',
                'icon' => 'boxes',
                'color' => '#10b981',
                'severity' => 'low',
            ],
            'reconnaissance' => [
                'label' => 'Reconnaissance',
                'icon' => 'binoculars',
                'color' => '#6366f1',
                'severity' => 'low',
            ],
            'ceasefire_violation' => [
                'label' => 'Ceasefire Violation',
                'icon' => 'exclamation',
                'color' => '#ea580c',
                'severity' => 'high',
            ],
        ],
        'severities' => [
            'low' => ['label' => 'Low', 'color' => '#22c55e', 'priority' => 1],
            'medium' => ['label' => 'Medium', 'color' => '#f59e0b', 'priority' => 2],
            'high' => ['label' => 'High', 'color' => '#ef4444', 'priority' => 3],
            'critical' => ['label' => 'Critical', 'color' => '#b91c1c', 'priority' => 4],
        ],
        'verification_statuses' => [
            'unverified' => ['label' => 'Unverified', 'color' => '#94a3b8'],
            'pending' => ['label' => 'Pending Verification', 'color' => '#fbbf24'],
            'verified' => ['label' => 'Verified', 'color' => '#22c55e'],
            'disputed' => ['label' => 'Disputed', 'color' => '#f97316'],
            'debunked' => ['label' => 'Debunked', 'color' => '#ef4444'],
        ],
        'max_attachment_size' => (int) env('EVENT_MAX_ATTACHMENT_SIZE', 10485760),
        'allowed_attachment_types' => [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'video/mp4',
            'video/webm',
            'application/pdf',
        ],
    ],

    'equipment' => [
        'categories' => [
            'armored_vehicle' => 'Armored Vehicle',
            'tank' => 'Tank',
            'artillery' => 'Artillery',
            'air_defense' => 'Air Defense System',
            'aircraft' => 'Aircraft',
            'helicopter' => 'Helicopter',
            'naval_vessel' => 'Naval Vessel',
            'drone' => 'Drone/UAV',
            'missile_system' => 'Missile System',
            'electronic_warfare' => 'Electronic Warfare System',
            'engineering_vehicle' => 'Engineering Vehicle',
            'logistics_vehicle' => 'Logistics Vehicle',
        ],
        'statuses' => [
            'operational' => ['label' => 'Operational', 'color' => '#22c55e'],
            'damaged' => ['label' => 'Damaged', 'color' => '#f59e0b'],
            'destroyed' => ['label' => 'Destroyed', 'color' => '#ef4444'],
            'captured' => ['label' => 'Captured', 'color' => '#3b82f6'],
            'abandoned' => ['label' => 'Abandoned', 'color' => '#94a3b8'],
            'unknown' => ['label' => 'Unknown', 'color' => '#64748b'],
        ],
    ],

    'intelligence' => [
        'source_types' => [
            'osint' => 'Open Source Intelligence',
            'social_media' => 'Social Media',
            'satellite' => 'Satellite Imagery',
            'video' => 'Video Evidence',
            'photo' => 'Photo Evidence',
            'witness' => 'Witness Report',
            'government' => 'Government Report',
            'news_media' => 'News Media',
            'analysis' => 'Expert Analysis',
        ],
        'reliability_ratings' => [
            'A' => 'Completely reliable',
            'B' => 'Usually reliable',
            'C' => 'Fairly reliable',
            'D' => 'Not usually reliable',
            'E' => 'Unreliable',
            'F' => 'Reliability cannot be judged',
        ],
        'credibility_ratings' => [
            '1' => 'Confirmed by other sources',
            '2' => 'Probably true',
            '3' => 'Possibly true',
            '4' => 'Doubtful',
            '5' => 'Improbable',
            '6' => 'Truth cannot be judged',
        ],
    ],

    'analysis' => [
        'timeline_aggregation_intervals' => [
            'hourly' => 'Hourly',
            'daily' => 'Daily',
            'weekly' => 'Weekly',
            'monthly' => 'Monthly',
        ],
        'heatmap_radius' => (int) env('ANALYSIS_HEATMAP_RADIUS', 25),
        'heatmap_blur' => (int) env('ANALYSIS_HEATMAP_BLUR', 15),
        'heatmap_max_zoom' => (int) env('ANALYSIS_HEATMAP_MAX_ZOOM', 15),
    ],

    'notifications' => [
        'channels' => [
            'database' => true,
            'mail' => env('NOTIFICATIONS_MAIL_ENABLED', true),
            'broadcast' => env('NOTIFICATIONS_BROADCAST_ENABLED', false),
        ],
        'events' => [
            'new_high_severity_event' => true,
            'event_verification_complete' => true,
            'mentioned_in_comment' => true,
            'report_ready' => true,
            'data_export_ready' => true,
        ],
    ],

    'api' => [
        'rate_limits' => [
            'default' => (int) env('API_RATE_LIMIT_DEFAULT', 60),
            'authenticated' => (int) env('API_RATE_LIMIT_AUTHENTICATED', 120),
            'premium' => (int) env('API_RATE_LIMIT_PREMIUM', 300),
        ],
        'pagination' => [
            'default_per_page' => 25,
            'max_per_page' => 100,
        ],
        'enable_cors' => env('API_ENABLE_CORS', true),
        'allowed_origins' => env('API_ALLOWED_ORIGINS', '*'),
    ],

    'data_retention' => [
        'soft_delete_retention_days' => (int) env('DATA_RETENTION_SOFT_DELETE_DAYS', 30),
        'export_retention_days' => (int) env('DATA_RETENTION_EXPORT_DAYS', 7),
        'audit_log_retention_days' => (int) env('DATA_RETENTION_AUDIT_LOG_DAYS', 365),
        'analytics_retention_days' => (int) env('DATA_RETENTION_ANALYTICS_DAYS', 90),
    ],

    'security' => [
        'enable_audit_logging' => env('SECURITY_AUDIT_LOGGING', true),
        'enable_ip_tracking' => env('SECURITY_IP_TRACKING', true),
        'max_login_attempts' => (int) env('SECURITY_MAX_LOGIN_ATTEMPTS', 5),
        'lockout_duration_minutes' => (int) env('SECURITY_LOCKOUT_DURATION', 15),
        'require_email_verification' => env('SECURITY_REQUIRE_EMAIL_VERIFICATION', true),
        'password_min_length' => (int) env('SECURITY_PASSWORD_MIN_LENGTH', 12),
    ],

    'features' => [
        'enable_user_registration' => env('FEATURE_USER_REGISTRATION', true),
        'enable_social_sharing' => env('FEATURE_SOCIAL_SHARING', false),
        'enable_data_exports' => env('FEATURE_DATA_EXPORTS', true),
        'enable_api_access' => env('FEATURE_API_ACCESS', true),
        'enable_collaboration' => env('FEATURE_COLLABORATION', true),
        'enable_advanced_analytics' => env('FEATURE_ADVANCED_ANALYTICS', true),
        'enable_ai_analysis' => env('FEATURE_AI_ANALYSIS', false),
    ],

];
