<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Reverse Image Search Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the Reverse Image Search Aggregator feature.
    | This feature searches multiple image search engines to find where
    | an image appears online, detect manipulation, and find the earliest
    | appearance date.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | General Settings
    |--------------------------------------------------------------------------
    */

    // Default timeout for API requests (seconds)
    'timeout' => env('REVERSE_IMAGE_TIMEOUT', 30),

    // Maximum image file size (bytes) - default 10MB
    'max_file_size' => env('REVERSE_IMAGE_MAX_FILE_SIZE', 10485760),

    // Storage disk for uploaded images
    'storage_disk' => env('REVERSE_IMAGE_STORAGE_DISK', 'reverse_images'),

    // Retention period for search results (days, null = forever)
    'retention_days' => env('REVERSE_IMAGE_RETENTION_DAYS', 90),

    // Enable automatic cleanup of expired searches
    'cleanup_enabled' => env('REVERSE_IMAGE_CLEANUP_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Search Engine Configuration
    |--------------------------------------------------------------------------
    |
    | Configure each search engine with their API credentials and settings.
    | Set 'enabled' to true and provide API keys to activate each engine.
    |
    */

    'engines' => [

        /*
        |----------------------------------------------------------------------
        | Google Custom Search API
        |----------------------------------------------------------------------
        |
        | Requires a Custom Search Engine ID and API Key.
        | Create at: https://console.developers.google.com/
        | Custom Search: https://programmablesearchengine.google.com/
        |
        | Pricing: 100 queries/day free, then $5 per 1000 queries
        |
        */
        'google' => [
            'enabled' => env('REVERSE_IMAGE_GOOGLE_ENABLED', false),
            'api_key' => env('GOOGLE_CUSTOM_SEARCH_API_KEY'),
            'search_engine_id' => env('GOOGLE_CUSTOM_SEARCH_ENGINE_ID'),
            'max_results' => env('REVERSE_IMAGE_GOOGLE_MAX_RESULTS', 20),
        ],

        /*
        |----------------------------------------------------------------------
        | Bing Visual Search API
        |----------------------------------------------------------------------
        |
        | Requires an Azure Cognitive Services subscription.
        | Create at: https://azure.microsoft.com/services/cognitive-services/
        |
        | Pricing: 1000 transactions/month free (S1 tier)
        |
        */
        'bing' => [
            'enabled' => env('REVERSE_IMAGE_BING_ENABLED', false),
            'api_key' => env('BING_VISUAL_SEARCH_API_KEY'),
            'endpoint' => env('BING_VISUAL_SEARCH_ENDPOINT', 'https://api.bing.microsoft.com/v7.0/images/visualsearch'),
            'max_results' => env('REVERSE_IMAGE_BING_MAX_RESULTS', 50),
        ],

        /*
        |----------------------------------------------------------------------
        | TinEye API
        |----------------------------------------------------------------------
        |
        | Professional reverse image search service.
        | Sign up at: https://tineye.com/
        |
        | Pricing: Pay-per-search model, starting at $200 for 5000 searches
        | Best for: Finding exact matches and crawl dates
        |
        */
        'tineye' => [
            'enabled' => env('REVERSE_IMAGE_TINEYE_ENABLED', false),
            'api_key' => env('TINEYE_API_KEY'),
            'api_secret' => env('TINEYE_API_SECRET'),
            'max_results' => env('REVERSE_IMAGE_TINEYE_MAX_RESULTS', 50),
            // Sort options: 'score', 'size', 'crawl_date'
            'sort' => env('REVERSE_IMAGE_TINEYE_SORT', 'crawl_date'),
            'order' => env('REVERSE_IMAGE_TINEYE_ORDER', 'asc'),
        ],

        /*
        |----------------------------------------------------------------------
        | Yandex Images API
        |----------------------------------------------------------------------
        |
        | Can use direct Yandex API or SerpApi as a proxy.
        | SerpApi recommended for reliability: https://serpapi.com/
        |
        | Pricing (SerpApi): 100 searches/month free, then from $50/month
        |
        */
        'yandex' => [
            'enabled' => env('REVERSE_IMAGE_YANDEX_ENABLED', false),
            'api_key' => env('YANDEX_API_KEY'),
            'folder_id' => env('YANDEX_FOLDER_ID'),
            // Use SerpApi for more reliable access
            'serpapi_key' => env('SERPAPI_API_KEY'),
            'max_results' => env('REVERSE_IMAGE_YANDEX_MAX_RESULTS', 30),
        ],

        /*
        |----------------------------------------------------------------------
        | Baidu Images API
        |----------------------------------------------------------------------
        |
        | Requires SerpApi for reliable access outside China.
        | Sign up at: https://serpapi.com/
        |
        | Best for: Finding Chinese sources and reposts
        |
        */
        'baidu' => [
            'enabled' => env('REVERSE_IMAGE_BAIDU_ENABLED', false),
            // Baidu direct API is complex; SerpApi recommended
            'serpapi_key' => env('SERPAPI_API_KEY'),
            'max_results' => env('REVERSE_IMAGE_BAIDU_MAX_RESULTS', 30),
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Image Processing
    |--------------------------------------------------------------------------
    */

    'processing' => [
        // Allowed MIME types for uploaded images
        'allowed_mime_types' => [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'image/bmp',
        ],

        // Maximum image dimensions (pixels) - larger images will be resized
        'max_dimension' => env('REVERSE_IMAGE_MAX_DIMENSION', 4096),

        // JPEG quality for processed images (1-100)
        'jpeg_quality' => env('REVERSE_IMAGE_JPEG_QUALITY', 85),

        // Enable EXIF data extraction
        'extract_exif' => env('REVERSE_IMAGE_EXTRACT_EXIF', true),

        // Strip EXIF data before uploading to engines (privacy)
        'strip_exif_on_upload' => env('REVERSE_IMAGE_STRIP_EXIF', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Perceptual Hashing
    |--------------------------------------------------------------------------
    */

    'hashing' => [
        // Hash algorithm for perceptual hashing
        // Options: 'average', 'difference', 'perceptual' (DCT-based)
        'algorithm' => env('REVERSE_IMAGE_HASH_ALGORITHM', 'perceptual'),

        // Hash size in bits (8 = 64 bit hash, 16 = 256 bit hash)
        'hash_size' => env('REVERSE_IMAGE_HASH_SIZE', 8),

        // Hamming distance threshold for considering images similar
        // Lower = stricter matching
        'similarity_threshold' => env('REVERSE_IMAGE_SIMILARITY_THRESHOLD', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | Manipulation Detection
    |--------------------------------------------------------------------------
    */

    'manipulation_indicators' => [
        // Domains known for image manipulation/editing tools
        'suspicious_domains' => [
            'photoshop.com',
            'canva.com',
            'pixlr.com',
            'fotor.com',
            'befunky.com',
            'picmonkey.com',
            'fakeimagedetector.com',
        ],

        // Social media domains (may indicate repost/share)
        'social_media_domains' => [
            'twitter.com',
            'x.com',
            'facebook.com',
            'instagram.com',
            'tiktok.com',
            'reddit.com',
            'tumblr.com',
            'pinterest.com',
            'vk.com',
            't.me',
            'telegram.org',
        ],

        // Minimum similarity score to flag potential manipulation (0-100)
        'manipulation_threshold' => env('REVERSE_IMAGE_MANIPULATION_THRESHOLD', 70),
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    */

    'rate_limits' => [
        // Maximum searches per user per hour
        'per_hour' => env('REVERSE_IMAGE_RATE_LIMIT_HOUR', 20),

        // Maximum searches per user per day
        'per_day' => env('REVERSE_IMAGE_RATE_LIMIT_DAY', 100),

        // Cooldown between searches (seconds)
        'cooldown' => env('REVERSE_IMAGE_COOLDOWN', 5),
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue Configuration
    |--------------------------------------------------------------------------
    */

    'queue' => [
        // Queue name for search jobs
        'name' => env('REVERSE_IMAGE_QUEUE', 'default'),

        // Maximum attempts for failed jobs
        'max_attempts' => env('REVERSE_IMAGE_MAX_ATTEMPTS', 3),

        // Job timeout in seconds
        'timeout' => env('REVERSE_IMAGE_JOB_TIMEOUT', 600),

        // Retry delays in seconds
        'retry_delays' => [60, 120, 300],
    ],

    /*
    |--------------------------------------------------------------------------
    | Caching
    |--------------------------------------------------------------------------
    */

    'cache' => [
        // Enable result caching
        'enabled' => env('REVERSE_IMAGE_CACHE_ENABLED', true),

        // Cache TTL in minutes
        'ttl' => env('REVERSE_IMAGE_CACHE_TTL', 1440), // 24 hours

        // Cache driver
        'driver' => env('REVERSE_IMAGE_CACHE_DRIVER', 'database'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    */

    'logging' => [
        // Log level for reverse image operations
        'level' => env('REVERSE_IMAGE_LOG_LEVEL', 'info'),

        // Log API responses (useful for debugging)
        'log_responses' => env('REVERSE_IMAGE_LOG_RESPONSES', false),

        // Mask API keys in logs
        'mask_api_keys' => env('REVERSE_IMAGE_MASK_KEYS', true),
    ],

];
