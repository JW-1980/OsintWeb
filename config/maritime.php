<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Default AIS Data Provider
    |--------------------------------------------------------------------------
    |
    | This option controls the default AIS data provider that will be used
    | when fetching vessel position data. You may set this to any of the
    | providers defined in the "providers" array below.
    |
    | Supported: "marinetraffic", "vesselfinder", "myshiptracking", "ais_receiver"
    |
    */

    'default_provider' => env('MARITIME_DEFAULT_PROVIDER', 'marinetraffic'),

    /*
    |--------------------------------------------------------------------------
    | AIS Data Providers
    |--------------------------------------------------------------------------
    |
    | Here you may configure all of the AIS data providers used by your
    | application. Each provider has different capabilities, coverage,
    | and API limits.
    |
    | MarineTraffic: Most comprehensive, premium API required
    | VesselFinder: Good coverage, competitive pricing
    | MyShipTracking: Alternative with good free tier
    | AIS Receiver: For direct AIS antenna feed
    |
    */

    'providers' => [

        'marinetraffic' => [
            'name' => 'MarineTraffic',
            'api_key' => env('MARINETRAFFIC_API_KEY'),
            'base_url' => 'https://services.marinetraffic.com/api',
            'rate_limit' => 1000,  // requests per day
            'supports_historical' => true,
            'supports_port_calls' => true,
            'supports_vessel_photos' => true,

            /*
            | API endpoints available:
            | - exportvessels: Current vessel positions
            | - exportvesseltrack: Historical track
            | - portcalls: Port call history
            | - expectedarrivals: ETA at ports
            */
            'endpoints' => [
                'positions' => '/exportvessels',
                'track' => '/exportvesseltrack',
                'port_calls' => '/portcalls',
                'arrivals' => '/expectedarrivals',
            ],
        ],

        'vesselfinder' => [
            'name' => 'VesselFinder',
            'api_key' => env('VESSELFINDER_API_KEY'),
            'base_url' => 'https://api.vesselfinder.com',
            'rate_limit' => 500,  // requests per day
            'supports_historical' => true,
            'supports_port_calls' => true,
            'supports_vessel_photos' => false,
        ],

        'myshiptracking' => [
            'name' => 'MyShipTracking',
            'api_key' => env('MYSHIPTRACKING_API_KEY'),
            'base_url' => 'https://api.myshiptracking.com',
            'rate_limit' => 100,  // requests per day (free tier)
            'supports_historical' => false,
            'supports_port_calls' => false,
            'supports_vessel_photos' => false,
        ],

        'ais_receiver' => [
            'name' => 'Local AIS Receiver',
            'enabled' => env('AIS_RECEIVER_ENABLED', false),
            'host' => env('AIS_RECEIVER_HOST', '127.0.0.1'),
            'port' => env('AIS_RECEIVER_PORT', 10110),
            'protocol' => 'nmea',  // nmea or json
        ],

        'fleetmon' => [
            'name' => 'FleetMon',
            'api_key' => env('FLEETMON_API_KEY'),
            'base_url' => 'https://apiv2.fleetmon.com',
            'rate_limit' => 1000,
            'supports_historical' => true,
            'supports_port_calls' => true,
            'supports_vessel_photos' => true,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Position Fetching Configuration
    |--------------------------------------------------------------------------
    |
    | Configure how often to fetch position updates and retention settings.
    |
    */

    'fetching' => [
        // How often to fetch positions for high-priority vessels (minutes)
        'high_priority_interval' => env('MARITIME_HIGH_PRIORITY_INTERVAL', 5),

        // How often to fetch positions for medium-priority vessels (minutes)
        'medium_priority_interval' => env('MARITIME_MEDIUM_PRIORITY_INTERVAL', 15),

        // How often to fetch positions for low-priority vessels (minutes)
        'low_priority_interval' => env('MARITIME_LOW_PRIORITY_INTERVAL', 60),

        // Maximum vessels to fetch per API call
        'batch_size' => 50,

        // Retry failed fetches after this many minutes
        'retry_after' => 5,
    ],

    /*
    |--------------------------------------------------------------------------
    | Dark Activity Detection
    |--------------------------------------------------------------------------
    |
    | Settings for detecting AIS gaps (dark activity) which may indicate
    | intentional concealment or equipment issues.
    |
    */

    'dark_activity' => [
        // Minimum gap in hours to consider "dark"
        'threshold_hours' => env('MARITIME_DARK_THRESHOLD', 4),

        // Special thresholds by vessel type (hours)
        'thresholds_by_type' => [
            'tanker' => 2,      // More sensitive for tankers
            'cargo' => 3,
            'military' => 1,    // Very sensitive for military
            'fishing' => 6,     // Less sensitive for fishing
            'yacht' => 12,      // Least sensitive for yachts
        ],

        // Minimum distance (nm) traveled during gap to flag as suspicious
        'min_suspicious_distance' => 10,
    ],

    /*
    |--------------------------------------------------------------------------
    | Ship-to-Ship (STS) Transfer Detection
    |--------------------------------------------------------------------------
    |
    | Settings for detecting potential ship-to-ship transfers based on
    | proximity and speed patterns.
    |
    */

    'sts_detection' => [
        // Maximum distance (nautical miles) to consider STS
        'max_distance_nm' => env('MARITIME_STS_DISTANCE', 0.5),

        // Maximum speed (knots) for both vessels during STS
        'max_speed_knots' => 3,

        // Minimum duration (minutes) of proximity to consider STS
        'min_duration_minutes' => 30,

        // Vessel types most likely for STS operations
        'suspicious_types' => ['tanker', 'cargo'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Geofencing Configuration
    |--------------------------------------------------------------------------
    |
    | Predefined zones for alert monitoring.
    |
    */

    'zones' => [
        // Example strategic chokepoints
        'strait_of_hormuz' => [
            'name' => 'Strait of Hormuz',
            'polygon' => 'POLYGON((56.0 25.0, 56.5 25.0, 56.5 26.5, 56.0 26.5, 56.0 25.0))',
        ],
        'suez_canal' => [
            'name' => 'Suez Canal',
            'polygon' => 'POLYGON((32.2 29.8, 32.6 29.8, 32.6 31.3, 32.2 31.3, 32.2 29.8))',
        ],
        'panama_canal' => [
            'name' => 'Panama Canal',
            'polygon' => 'POLYGON((-79.9 8.8, -79.4 8.8, -79.4 9.4, -79.9 9.4, -79.9 8.8))',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Data Retention
    |--------------------------------------------------------------------------
    |
    | Configure how long to keep maritime data in the database.
    |
    */

    'retention' => [
        // Keep position data for this many days
        'positions_days' => env('MARITIME_POSITIONS_RETENTION', 90),

        // Keep track data for this many days
        'tracks_days' => env('MARITIME_TRACKS_RETENTION', 365),

        // Keep port call data for this many days
        'port_calls_days' => env('MARITIME_PORT_CALLS_RETENTION', 730),

        // Keep alert trigger history for this many days
        'alert_triggers_days' => env('MARITIME_ALERT_TRIGGERS_RETENTION', 90),

        // Archived/correlated data is kept indefinitely
        'keep_correlated_indefinitely' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Rate limiting settings for maritime API endpoints.
    |
    */

    'rate_limits' => [
        // Maximum API requests per user per minute
        'per_minute' => 30,

        // Maximum API requests per user per hour
        'per_hour' => 300,

        // Maximum vessels a user can track
        'max_tracked_vessels' => env('MARITIME_MAX_TRACKED', 100),

        // Maximum alerts per user
        'max_alerts' => env('MARITIME_MAX_ALERTS', 50),
    ],

    /*
    |--------------------------------------------------------------------------
    | Caching Configuration
    |--------------------------------------------------------------------------
    |
    | Cache settings for frequently accessed maritime data.
    |
    */

    'cache' => [
        'enabled' => env('MARITIME_CACHE_ENABLED', true),
        'prefix' => 'maritime:',

        // Cache durations in seconds
        'ttl' => [
            'vessel_list' => 300,       // 5 minutes
            'vessel_details' => 60,     // 1 minute
            'positions' => 30,          // 30 seconds
            'statistics' => 600,        // 10 minutes
            'port_calls' => 3600,       // 1 hour
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    |
    | Configure notification channels for maritime alerts.
    |
    */

    'notifications' => [
        // Available channels
        'channels' => ['database', 'mail', 'slack'],

        // Default channels for new alerts
        'default_channels' => ['database'],

        // Slack webhook for maritime alerts
        'slack_webhook' => env('MARITIME_SLACK_WEBHOOK'),

        // Email address for critical alerts
        'critical_email' => env('MARITIME_CRITICAL_EMAIL'),
    ],

];
