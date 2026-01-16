<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Default Weather Provider
    |--------------------------------------------------------------------------
    |
    | This option controls the default weather data provider that will be used
    | when fetching weather data. You may set this to any of the providers
    | defined in the "providers" array below.
    |
    | Supported: "openweathermap", "visualcrossing"
    |
    */

    'default_provider' => env('WEATHER_DEFAULT_PROVIDER', 'openweathermap'),

    /*
    |--------------------------------------------------------------------------
    | Weather Data Providers
    |--------------------------------------------------------------------------
    |
    | Here you may configure all of the weather data providers used by your
    | application. Each provider has different capabilities and API limits.
    |
    | OpenWeatherMap: Good for current weather, historical data requires paid plan
    | Visual Crossing: Excellent historical data, good free tier
    |
    */

    'providers' => [

        'openweathermap' => [
            'name' => 'OpenWeatherMap',
            'api_key' => env('OPENWEATHERMAP_API_KEY'),
            'base_url' => 'https://api.openweathermap.org/data/3.0',
            'supports_historical' => true,
            'supports_forecast' => true,
            'rate_limit' => 60, // requests per minute (free tier)
            'historical_days_limit' => 5, // Free tier: 5 days back, paid: unlimited

            /*
            | API tiers determine available features:
            | - Free: Current weather, 5-day forecast
            | - One Call 3.0: Includes historical data (past 5 days free, paid for more)
            | - History API: Full historical data (paid only)
            */
            'tier' => env('OPENWEATHERMAP_TIER', 'free'),
        ],

        'visualcrossing' => [
            'name' => 'Visual Crossing',
            'api_key' => env('VISUALCROSSING_API_KEY'),
            'base_url' => 'https://weather.visualcrossing.com/VisualCrossingWebServices/rest/services/timeline',
            'supports_historical' => true,
            'supports_forecast' => true,
            'rate_limit' => 1000, // requests per day (free tier)
            'historical_years_limit' => 50, // Historical data back to 1970s

            /*
            | Visual Crossing has excellent historical weather data
            | Free tier: 1000 records/day, up to 15 days history
            | Paid: Unlimited history, higher limits
            */
            'tier' => env('VISUALCROSSING_TIER', 'free'),
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Caching Configuration
    |--------------------------------------------------------------------------
    |
    | Weather data is cached to reduce API calls and improve performance.
    | Different cache durations are used for different types of data.
    |
    */

    'cache' => [
        'enabled' => env('WEATHER_CACHE_ENABLED', true),
        'prefix' => 'weather:',

        // Cache durations in seconds
        'ttl' => [
            'current' => 1800,        // 30 minutes for current weather
            'forecast' => 3600,       // 1 hour for forecasts
            'historical' => 2592000,  // 30 days for historical data (won't change)
        ],

        // Cache by location precision (decimal places)
        // Higher = more precise but more API calls
        // 2 decimals = ~1.1km precision
        'location_precision' => 2,
    ],

    /*
    |--------------------------------------------------------------------------
    | Shadow Verification Settings
    |--------------------------------------------------------------------------
    |
    | Settings for shadow-based chronolocation verification.
    |
    */

    'shadow_verification' => [
        // Default tolerance in degrees for shadow angle matching
        'default_tolerance' => 15.0,

        // Minimum sun elevation for reliable shadow analysis
        'min_sun_elevation' => 10.0,

        // Maximum sun elevation (shadows too short for reliable analysis)
        'max_sun_elevation' => 80.0,

        // Maximum cloud cover percentage for shadow analysis
        'max_cloud_cover' => 50,

        // Time step in minutes for finding matching times
        'search_time_step' => 15,
    ],

    /*
    |--------------------------------------------------------------------------
    | Data Retention
    |--------------------------------------------------------------------------
    |
    | Configure how long to keep weather data in the database.
    |
    */

    'retention' => [
        // Keep current weather data for this many days
        'current_days' => 30,

        // Keep historical weather data indefinitely (for OSINT purposes)
        'historical_days' => null, // null = forever

        // Keep forecast data for this many days after forecast date
        'forecast_days' => 7,
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Rate limiting settings for weather API endpoints.
    |
    */

    'rate_limits' => [
        // Maximum requests per user per minute
        'per_minute' => 30,

        // Maximum requests per user per hour
        'per_hour' => 300,

        // Maximum historical data fetches per day per user
        'historical_per_day' => 100,
    ],

    /*
    |--------------------------------------------------------------------------
    | Units Configuration
    |--------------------------------------------------------------------------
    |
    | Default units for weather data. Data is stored in metric but can
    | be converted for display.
    |
    */

    'units' => [
        'temperature' => 'celsius', // celsius, fahrenheit
        'wind_speed' => 'm/s',      // m/s, km/h, mph, knots
        'pressure' => 'hPa',        // hPa, mbar, mmHg, inHg
        'visibility' => 'meters',   // meters, kilometers, miles
        'precipitation' => 'mm',    // mm, inches
    ],

];
