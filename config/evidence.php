<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Evidence Preservation Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for the Evidence Preservation System, including storage,
    | IPFS, Wayback Machine, and screenshot capture settings.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Default User Agent
    |--------------------------------------------------------------------------
    |
    | The User-Agent string to use when fetching URLs for preservation.
    | Some websites may block certain user agents.
    |
    */

    'default_user_agent' => env(
        'EVIDENCE_USER_AGENT',
        'Mozilla/5.0 (compatible; OsintWeb Evidence Archiver/1.0; +https://osint.web)'
    ),

    /*
    |--------------------------------------------------------------------------
    | Max Retries
    |--------------------------------------------------------------------------
    |
    | Maximum number of retry attempts for failed evidence preservation.
    |
    */

    'max_retries' => env('EVIDENCE_MAX_RETRIES', 3),

    /*
    |--------------------------------------------------------------------------
    | Storage Settings
    |--------------------------------------------------------------------------
    |
    | Configure how and where evidence files are stored locally.
    |
    */

    'storage' => [
        // Disk name defined in config/filesystems.php
        'disk' => env('EVIDENCE_STORAGE_DISK', 'evidence'),

        // Maximum file size in bytes (default: 100MB)
        'max_file_size' => env('EVIDENCE_MAX_FILE_SIZE', 104857600),

        // Retention period in days (null = forever)
        'retention_days' => env('EVIDENCE_RETENTION_DAYS', null),

        // Enable automatic cleanup of expired evidence
        'cleanup_enabled' => env('EVIDENCE_CLEANUP_ENABLED', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Fetch Settings
    |--------------------------------------------------------------------------
    |
    | Configure URL fetching behavior.
    |
    */

    'fetch' => [
        // Request timeout in seconds
        'timeout' => env('EVIDENCE_FETCH_TIMEOUT', 30),

        // Maximum number of redirects to follow
        'max_redirects' => env('EVIDENCE_MAX_REDIRECTS', 5),

        // Verify SSL certificates
        'verify_ssl' => env('EVIDENCE_VERIFY_SSL', true),

        // Maximum retry attempts for fetching
        'max_retries' => env('EVIDENCE_FETCH_MAX_RETRIES', 3),
    ],

    /*
    |--------------------------------------------------------------------------
    | IPFS Settings
    |--------------------------------------------------------------------------
    |
    | Configure IPFS storage for decentralized evidence preservation.
    | Supported providers: 'pinata', 'infura'
    |
    */

    'ipfs' => [
        // Enable IPFS preservation
        'enabled' => env('EVIDENCE_IPFS_ENABLED', false),

        // IPFS provider (pinata, infura)
        'provider' => env('EVIDENCE_IPFS_PROVIDER', 'pinata'),

        // IPFS gateway URL for accessing content
        'gateway' => env('EVIDENCE_IPFS_GATEWAY', 'https://ipfs.io/ipfs/'),

        // Pinata API credentials
        'pinata_api_key' => env('PINATA_API_KEY'),
        'pinata_api_secret' => env('PINATA_API_SECRET'),

        // Infura IPFS credentials
        'infura_project_id' => env('INFURA_IPFS_PROJECT_ID'),
        'infura_project_secret' => env('INFURA_IPFS_PROJECT_SECRET'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Wayback Machine Settings
    |--------------------------------------------------------------------------
    |
    | Configure integration with the Internet Archive's Wayback Machine.
    |
    */

    'wayback' => [
        // Enable automatic submission to Wayback Machine
        'enabled' => env('EVIDENCE_WAYBACK_ENABLED', true),

        // Request timeout in seconds
        'timeout' => env('EVIDENCE_WAYBACK_TIMEOUT', 60),

        // Wayback Machine API URL
        'api_url' => env(
            'EVIDENCE_WAYBACK_API_URL',
            'https://web.archive.org/save/'
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | Screenshot Settings
    |--------------------------------------------------------------------------
    |
    | Configure screenshot capture for webpage evidence.
    | Supported services: 'none', 'screenshotone', 'browserless'
    |
    */

    'screenshot' => [
        // Screenshot service to use (none, screenshotone, browserless)
        'service' => env('EVIDENCE_SCREENSHOT_SERVICE', 'none'),

        // ScreenshotOne API key
        'screenshotone_api_key' => env('SCREENSHOTONE_API_KEY'),

        // Browserless settings
        'browserless_url' => env('BROWSERLESS_URL'),
        'browserless_token' => env('BROWSERLESS_TOKEN'),

        // Default viewport settings
        'viewport_width' => env('EVIDENCE_SCREENSHOT_WIDTH', 1920),
        'viewport_height' => env('EVIDENCE_SCREENSHOT_HEIGHT', 1080),

        // Capture full page
        'full_page' => env('EVIDENCE_SCREENSHOT_FULL_PAGE', true),

        // Output format (png, jpg, webp)
        'format' => env('EVIDENCE_SCREENSHOT_FORMAT', 'png'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Integrity Verification
    |--------------------------------------------------------------------------
    |
    | Configure automatic integrity verification settings.
    |
    */

    'verification' => [
        // Automatically verify evidence periodically
        'auto_verify' => env('EVIDENCE_AUTO_VERIFY', false),

        // Verification interval in hours
        'verify_interval_hours' => env('EVIDENCE_VERIFY_INTERVAL', 24),

        // Hash algorithm (sha256 recommended)
        'hash_algorithm' => 'sha256',
    ],

    /*
    |--------------------------------------------------------------------------
    | Content Type Detection
    |--------------------------------------------------------------------------
    |
    | File extensions mapped to content types for automatic detection.
    |
    */

    'content_types' => [
        'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg', 'ico'],
        'video' => ['mp4', 'webm', 'avi', 'mov', 'mkv', 'flv', 'm4v'],
        'audio' => ['mp3', 'wav', 'ogg', 'flac', 'm4a', 'aac'],
        'document' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'rtf'],
        'archive' => ['zip', 'rar', '7z', 'tar', 'gz', 'bz2'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Social Media Platforms
    |--------------------------------------------------------------------------
    |
    | Domains that should be classified as social media posts.
    |
    */

    'social_platforms' => [
        'twitter.com',
        'x.com',
        'facebook.com',
        'instagram.com',
        'tiktok.com',
        'youtube.com',
        'youtu.be',
        'telegram.org',
        't.me',
        'vk.com',
        'reddit.com',
        'linkedin.com',
        'threads.net',
        'mastodon.social',
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Configure rate limits for evidence preservation to prevent abuse.
    |
    */

    'rate_limits' => [
        // Maximum preservations per user per hour
        'per_hour' => env('EVIDENCE_RATE_LIMIT_HOUR', 100),

        // Maximum preservations per user per day
        'per_day' => env('EVIDENCE_RATE_LIMIT_DAY', 500),

        // Maximum file size per preservation (bytes)
        'max_size' => env('EVIDENCE_RATE_LIMIT_SIZE', 104857600), // 100MB
    ],

];
