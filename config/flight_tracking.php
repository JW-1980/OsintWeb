<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Default ADS-B Data Provider
    |--------------------------------------------------------------------------
    |
    | This option controls the default ADS-B data provider that will be used
    | when fetching flight position data. You may set this to any of the
    | providers defined in the "providers" array below.
    |
    | Supported: "opensky", "adsb_exchange", "flightradar24", "local_receiver"
    |
    */

    'default_provider' => env('FLIGHT_DEFAULT_PROVIDER', 'opensky'),

    /*
    |--------------------------------------------------------------------------
    | ADS-B Data Providers
    |--------------------------------------------------------------------------
    |
    | Configure all ADS-B data providers used by the application. Each provider
    | has different capabilities, coverage, and API limits.
    |
    | OpenSky Network: Free, global coverage, 10-second update interval
    | ADS-B Exchange: Paid, comprehensive data, near real-time
    | FlightRadar24: Paid, excellent coverage, real-time
    |
    */

    'providers' => [

        'opensky' => [
            'name' => 'OpenSky Network',
            'enabled' => env('OPENSKY_ENABLED', true),
            'api_url' => 'https://opensky-network.org/api',
            'username' => env('OPENSKY_USERNAME'),
            'password' => env('OPENSKY_PASSWORD'),
            'rate_limit' => [
                'anonymous' => 100, // requests per day
                'authenticated' => 4000, // requests per day
            ],
            'update_interval' => 10, // seconds
            'timeout' => 30,
            'supports_historical' => true,
            'historical_days_limit' => 30,
        ],

        'adsb_exchange' => [
            'name' => 'ADS-B Exchange',
            'enabled' => env('ADSBX_ENABLED', false),
            'api_url' => 'https://adsbexchange.com/api/aircraft/json',
            'api_key' => env('ADSBX_API_KEY'),
            'rapidapi_key' => env('ADSBX_RAPIDAPI_KEY'),
            'rate_limit' => 500, // requests per day (depends on plan)
            'update_interval' => 1, // seconds (real-time)
            'timeout' => 30,
            'supports_historical' => false,
        ],

        'flightradar24' => [
            'name' => 'FlightRadar24',
            'enabled' => env('FR24_ENABLED', false),
            'api_url' => 'https://data-live.flightradar24.com',
            'api_key' => env('FR24_API_KEY'),
            'rate_limit' => 1000, // requests per day (depends on plan)
            'update_interval' => 2, // seconds
            'timeout' => 30,
            'supports_historical' => true,
            'historical_days_limit' => 365,
        ],

        'flightaware' => [
            'name' => 'FlightAware',
            'enabled' => env('FLIGHTAWARE_ENABLED', false),
            'api_url' => 'https://aeroapi.flightaware.com/aeroapi',
            'api_key' => env('FLIGHTAWARE_API_KEY'),
            'rate_limit' => 150, // requests per minute (depends on plan)
            'update_interval' => 5, // seconds
            'timeout' => 30,
            'supports_historical' => true,
        ],

        'local_receiver' => [
            'name' => 'Local ADS-B Receiver',
            'enabled' => env('LOCAL_ADSB_ENABLED', false),
            'host' => env('LOCAL_ADSB_HOST', '127.0.0.1'),
            'port' => env('LOCAL_ADSB_PORT', 30003),
            'protocol' => env('LOCAL_ADSB_PROTOCOL', 'beast'), // beast, sbs1, json
            'timeout' => 10,
            'supports_historical' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Position Fetching Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for periodic position fetching job.
    |
    */

    'fetch' => [
        // How often to fetch positions (in seconds)
        'interval' => env('FLIGHT_FETCH_INTERVAL', 60),

        // Maximum number of aircraft to fetch per batch
        'batch_size' => env('FLIGHT_FETCH_BATCH', 100),

        // Only fetch positions for monitored aircraft
        'monitored_only' => true,

        // Fetch by priority (1 = most frequent, 5 = least frequent)
        'priority_intervals' => [
            1 => 15,  // Critical: every 15 seconds
            2 => 30,  // High: every 30 seconds
            3 => 60,  // Medium: every minute
            4 => 120, // Low: every 2 minutes
            5 => 300, // Minimal: every 5 minutes
        ],

        // Geographic bounds for area-based fetching (optional)
        'default_bounds' => [
            'enabled' => false,
            'min_lat' => env('FLIGHT_BOUNDS_MIN_LAT'),
            'max_lat' => env('FLIGHT_BOUNDS_MAX_LAT'),
            'min_lon' => env('FLIGHT_BOUNDS_MIN_LON'),
            'max_lon' => env('FLIGHT_BOUNDS_MAX_LON'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Track Building Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for building flight tracks from position data.
    |
    */

    'tracks' => [
        // Minimum positions to form a track
        'min_positions' => 5,

        // Maximum gap between positions before starting new track (minutes)
        'max_gap_minutes' => 30,

        // Consider flight complete if on ground for this long (minutes)
        'ground_completion_minutes' => 5,

        // Auto-detect airports within this distance (nautical miles)
        'airport_detection_radius_nm' => 5,

        // Simplify track paths for storage (Douglas-Peucker tolerance)
        'simplify_tolerance' => 0.0001, // degrees (~10m)
    ],

    /*
    |--------------------------------------------------------------------------
    | Alert Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for flight alerts.
    |
    */

    'alerts' => [
        // Default cooldown between repeated alerts (minutes)
        'default_cooldown_minutes' => 15,

        // Maximum alerts per user
        'max_alerts_per_user' => 50,

        // Maximum polygon points for zone alerts
        'max_zone_points' => 100,

        // Emergency squawk codes to always alert on
        'emergency_squawks' => ['7500', '7600', '7700'],

        // Process alerts in background job
        'process_async' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Caching Configuration
    |--------------------------------------------------------------------------
    |
    | Flight position data caching settings.
    |
    */

    'cache' => [
        'enabled' => env('FLIGHT_CACHE_ENABLED', true),
        'prefix' => 'flight:',

        // Cache durations in seconds
        'ttl' => [
            'position' => 60,        // 1 minute for current positions
            'aircraft' => 3600,      // 1 hour for aircraft data
            'track' => 300,          // 5 minutes for active tracks
            'stats' => 900,          // 15 minutes for statistics
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Data Retention
    |--------------------------------------------------------------------------
    |
    | Configure how long to keep flight tracking data.
    |
    */

    'retention' => [
        // Keep individual positions for this many days
        'positions_days' => env('FLIGHT_RETENTION_POSITIONS', 30),

        // Keep flight tracks for this many days
        'tracks_days' => env('FLIGHT_RETENTION_TRACKS', 365),

        // Keep tracks linked to events indefinitely
        'keep_event_tracks' => true,

        // Compress old position data instead of deleting
        'compress_after_days' => 7,
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Rate limits for flight tracking API endpoints.
    |
    */

    'rate_limits' => [
        // Maximum requests per user per minute
        'per_minute' => 60,

        // Maximum live position requests per minute
        'live_per_minute' => 30,

        // Maximum historical data requests per hour
        'historical_per_hour' => 100,
    ],

    /*
    |--------------------------------------------------------------------------
    | Unusual Activity Detection
    |--------------------------------------------------------------------------
    |
    | Settings for detecting unusual flight patterns.
    |
    */

    'unusual_detection' => [
        'enabled' => true,

        // Circling detection (degrees heading change per minute)
        'circling_threshold' => 180,

        // Loitering detection (time in same area)
        'loitering_minutes' => 30,
        'loitering_radius_nm' => 10,

        // Altitude anomaly (feet below normal for area)
        'low_altitude_threshold' => 1000,

        // Speed anomaly (knots below normal)
        'slow_speed_threshold' => 100,

        // Squawk changes to flag
        'squawk_changes_enabled' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Military Aircraft Detection
    |--------------------------------------------------------------------------
    |
    | Settings for identifying military aircraft.
    |
    */

    'military' => [
        // ICAO hex ranges for military registrations (by country)
        'icao_ranges' => [
            'US' => [
                ['ADF7C7', 'AE0000'], // US Military
            ],
            'UK' => [
                ['43C000', '43DFFF'], // UK Military
            ],
            'RU' => [
                ['155000', '155FFF'], // Russian Military
            ],
            // Add more as needed
        ],

        // Known military aircraft types
        'aircraft_types' => [
            'F16', 'F15', 'F18', 'F22', 'F35',
            'C130', 'C17', 'C5',
            'KC135', 'KC10', 'KC46',
            'E3', 'E8', 'RC135',
            'B1', 'B2', 'B52',
            'MQ9', 'RQ4',
            // Add more
        ],

        // Military operator keywords
        'operator_keywords' => [
            'air force', 'navy', 'army', 'marine', 'military',
            'luftwaffe', 'royal air force', 'raf',
        ],
    ],

];
