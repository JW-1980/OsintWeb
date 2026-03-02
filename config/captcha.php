<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | CAPTCHA Enabled
    |--------------------------------------------------------------------------
    |
    | Master toggle for CAPTCHA verification. When disabled, all routes
    | with the captcha middleware will skip verification.
    |
    */

    'enabled' => env('CAPTCHA_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | CAPTCHA Provider
    |--------------------------------------------------------------------------
    |
    | The CAPTCHA provider to use for verification. Supported providers:
    | "recaptcha" (Google reCAPTCHA v3) or "turnstile" (Cloudflare Turnstile).
    |
    */

    'provider' => env('CAPTCHA_PROVIDER', 'recaptcha'),

    /*
    |--------------------------------------------------------------------------
    | Google reCAPTCHA v3 Configuration
    |--------------------------------------------------------------------------
    |
    | Score-based bot detection. Scores range from 0.0 (likely bot) to
    | 1.0 (likely human). The threshold defines the minimum acceptable score.
    |
    | Sign up: https://www.google.com/recaptcha/admin
    |
    */

    'recaptcha' => [
        'site_key' => env('RECAPTCHA_SITE_KEY', ''),
        'secret_key' => env('RECAPTCHA_SECRET_KEY', ''),
        'verify_url' => 'https://www.google.com/recaptcha/api/siteverify',
        'score_threshold' => (float) env('RECAPTCHA_SCORE_THRESHOLD', 0.5),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cloudflare Turnstile Configuration
    |--------------------------------------------------------------------------
    |
    | Privacy-focused CAPTCHA alternative by Cloudflare. Uses a pass/fail
    | model without scoring. Supports managed, non-interactive, and
    | invisible challenge modes.
    |
    | Sign up: https://dash.cloudflare.com/turnstile
    |
    */

    'turnstile' => [
        'site_key' => env('TURNSTILE_SITE_KEY', ''),
        'secret_key' => env('TURNSTILE_SECRET_KEY', ''),
        'verify_url' => 'https://challenges.cloudflare.com/turnstile/v0/siteverify',
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache TTL (seconds)
    |--------------------------------------------------------------------------
    |
    | How long CAPTCHA settings loaded from the database are cached.
    |
    */

    'cache_ttl' => 300,

];
