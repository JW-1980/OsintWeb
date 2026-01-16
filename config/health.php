<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Health Check Thresholds
    |--------------------------------------------------------------------------
    |
    | These thresholds define when warnings or critical alerts should be
    | triggered during health checks.
    |
    */

    'thresholds' => [
        // Database response time in milliseconds before warning
        'database_response_ms' => env('HEALTH_DB_RESPONSE_THRESHOLD', 100),

        // Disk usage percentage thresholds
        'disk_warning_percent' => env('HEALTH_DISK_WARNING_PERCENT', 80),
        'disk_critical_percent' => env('HEALTH_DISK_CRITICAL_PERCENT', 95),

        // Queue job thresholds
        'stuck_jobs_minutes' => env('HEALTH_STUCK_JOBS_MINUTES', 60),
        'failed_jobs_warning_count' => env('HEALTH_FAILED_JOBS_WARNING', 10),

        // Scheduler heartbeat threshold
        'scheduler_warning_minutes' => env('HEALTH_SCHEDULER_WARNING_MINUTES', 5),

        // Memory usage thresholds (percentage of limit)
        'memory_warning_percent' => env('HEALTH_MEMORY_WARNING_PERCENT', 80),
        'memory_critical_percent' => env('HEALTH_MEMORY_CRITICAL_PERCENT', 95),

        // External service timeout in seconds
        'external_service_timeout' => env('HEALTH_EXTERNAL_TIMEOUT', 5),
    ],

    /*
    |--------------------------------------------------------------------------
    | Quick Check Components
    |--------------------------------------------------------------------------
    |
    | Components to check during a quick health check. These are the essential
    | services that must be running for the application to function.
    |
    */

    'quick_check_components' => [
        'database',
        'cache',
        'storage',
    ],

    /*
    |--------------------------------------------------------------------------
    | Full Check Components
    |--------------------------------------------------------------------------
    |
    | Components to check during a full health check. This includes all
    | services and integrations.
    |
    */

    'full_check_components' => [
        'database',
        'cache',
        'queue',
        'storage',
        'search',
        'migrations',
        'scheduler',
        'external',
    ],

    /*
    |--------------------------------------------------------------------------
    | External Services
    |--------------------------------------------------------------------------
    |
    | External service endpoints to check during health checks. Each service
    | should have a URL and optional timeout and expected status.
    |
    */

    'external_services' => [
        'openrouter' => [
            'name' => 'OpenRouter AI',
            'url' => env('OPENROUTER_HEALTH_URL', ''),
            'timeout' => 5,
            'required' => false,
        ],
        'openweathermap' => [
            'name' => 'OpenWeatherMap',
            'url' => env('OPENWEATHERMAP_API_KEY')
                ? 'https://api.openweathermap.org/data/2.5/weather?q=London&appid=' . env('OPENWEATHERMAP_API_KEY')
                : '',
            'timeout' => 5,
            'required' => false,
        ],
        'visualcrossing' => [
            'name' => 'Visual Crossing Weather',
            'url' => '', // Health check URL not publicly available
            'timeout' => 5,
            'required' => false,
        ],
        'meilisearch' => [
            'name' => 'Meilisearch',
            'url' => env('MEILISEARCH_HOST') ? env('MEILISEARCH_HOST') . '/health' : '',
            'timeout' => 3,
            'required' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Critical Components
    |--------------------------------------------------------------------------
    |
    | Components that are critical for system operation. If any of these fail,
    | the overall status will be marked as "unhealthy".
    |
    */

    'critical_components' => [
        'database',
        'storage',
    ],

    /*
    |--------------------------------------------------------------------------
    | Caching Configuration
    |--------------------------------------------------------------------------
    |
    | Cache settings for health check results.
    |
    */

    'cache' => [
        // Cache duration for quick checks (seconds)
        'quick_check_ttl' => env('HEALTH_QUICK_CACHE_TTL', 30),

        // Cache prefix
        'prefix' => 'health:',
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for health check result logging.
    |
    */

    'logging' => [
        // Whether to log all health check results
        'enabled' => env('HEALTH_LOGGING_ENABLED', true),

        // Only log failed checks
        'failures_only' => env('HEALTH_LOG_FAILURES_ONLY', false),

        // Log retention in days (0 = forever)
        'retention_days' => env('HEALTH_LOG_RETENTION_DAYS', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Rate limiting for health check endpoints.
    |
    */

    'rate_limits' => [
        // Public endpoints (per minute)
        'public' => env('HEALTH_RATE_LIMIT_PUBLIC', 60),

        // Authenticated endpoints (per minute)
        'authenticated' => env('HEALTH_RATE_LIMIT_AUTHENTICATED', 120),

        // Metrics endpoint (per minute)
        'metrics' => env('HEALTH_RATE_LIMIT_METRICS', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Prometheus Metrics Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for Prometheus-format metrics export.
    |
    */

    'prometheus' => [
        // Metric prefix
        'prefix' => 'osint_',

        // Include system metrics (memory, disk, CPU)
        'include_system_metrics' => true,

        // Include queue metrics
        'include_queue_metrics' => true,

        // Include database metrics
        'include_database_metrics' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Alerting Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for health check alerts. Configure channels to receive
    | notifications when health checks fail.
    |
    */

    'alerting' => [
        // Enable alerting
        'enabled' => env('HEALTH_ALERTING_ENABLED', false),

        // Alert channels (log, mail, slack)
        'channels' => explode(',', env('HEALTH_ALERT_CHANNELS', 'log')),

        // Minimum severity to trigger alerts (degraded, unhealthy, error)
        'min_severity' => env('HEALTH_ALERT_MIN_SEVERITY', 'unhealthy'),

        // Cooldown period between alerts (minutes)
        'cooldown_minutes' => env('HEALTH_ALERT_COOLDOWN', 15),

        // Slack webhook URL (if using Slack channel)
        'slack_webhook' => env('HEALTH_SLACK_WEBHOOK'),

        // Alert email recipients
        'email_recipients' => array_filter(explode(',', env('HEALTH_ALERT_EMAILS', ''))),
    ],

    /*
    |--------------------------------------------------------------------------
    | Kubernetes Probe Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for Kubernetes liveness and readiness probes.
    |
    */

    'kubernetes' => [
        // Components to check for liveness probe
        'liveness_checks' => [
            'database',
        ],

        // Components to check for readiness probe
        'readiness_checks' => [
            'database',
            'cache',
            'storage',
        ],

        // Response format (simple text or JSON)
        'response_format' => 'simple', // 'simple' or 'json'
    ],

];
