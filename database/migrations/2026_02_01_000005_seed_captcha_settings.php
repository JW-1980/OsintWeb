<?php

declare(strict_types=1);

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Seeds default CAPTCHA settings into the settings table.
     */
    public function up(): void
    {
        $settings = [
            [
                'key' => 'captcha.enabled',
                'value' => '0',
                'type' => 'boolean',
                'group' => 'captcha',
                'description' => 'Enable CAPTCHA verification on authentication routes',
                'is_public' => true,
                'is_encrypted' => false,
            ],
            [
                'key' => 'captcha.provider',
                'value' => 'recaptcha',
                'type' => 'string',
                'group' => 'captcha',
                'description' => 'Active CAPTCHA provider (recaptcha or turnstile)',
                'is_public' => true,
                'is_encrypted' => false,
            ],
            [
                'key' => 'captcha.recaptcha_site_key',
                'value' => '',
                'type' => 'string',
                'group' => 'captcha',
                'description' => 'Google reCAPTCHA v3 site key',
                'is_public' => true,
                'is_encrypted' => false,
            ],
            [
                'key' => 'captcha.recaptcha_secret_key',
                'value' => '',
                'type' => 'string',
                'group' => 'captcha',
                'description' => 'Google reCAPTCHA v3 secret key',
                'is_public' => false,
                'is_encrypted' => true,
            ],
            [
                'key' => 'captcha.recaptcha_score_threshold',
                'value' => '0.5',
                'type' => 'string',
                'group' => 'captcha',
                'description' => 'Minimum reCAPTCHA score to pass (0.0-1.0)',
                'is_public' => false,
                'is_encrypted' => false,
            ],
            [
                'key' => 'captcha.turnstile_site_key',
                'value' => '',
                'type' => 'string',
                'group' => 'captcha',
                'description' => 'Cloudflare Turnstile site key',
                'is_public' => true,
                'is_encrypted' => false,
            ],
            [
                'key' => 'captcha.turnstile_secret_key',
                'value' => '',
                'type' => 'string',
                'group' => 'captcha',
                'description' => 'Cloudflare Turnstile secret key',
                'is_public' => false,
                'is_encrypted' => true,
            ],
        ];

        foreach ($settings as $setting) {
            Setting::firstOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Setting::where('group', 'captcha')->delete();
    }
};
