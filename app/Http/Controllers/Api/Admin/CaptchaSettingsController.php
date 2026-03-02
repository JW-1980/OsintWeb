<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

/**
 * Manages CAPTCHA provider settings from the admin panel.
 *
 * Supports Google reCAPTCHA v3 and Cloudflare Turnstile.
 * Settings are persisted to SystemSettings and cached for 5 minutes.
 */
class CaptchaSettingsController extends Controller
{
    /**
     * Get current CAPTCHA settings.
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => [
                'enabled' => (bool) Setting::get('captcha.enabled', config('captcha.enabled', false)),
                'provider' => (string) Setting::get('captcha.provider', config('captcha.provider', 'recaptcha')),
                'recaptcha' => [
                    'site_key' => (string) Setting::get('captcha.recaptcha_site_key', config('captcha.recaptcha.site_key', '')),
                    'secret_key' => $this->maskSecret(
                        (string) Setting::get('captcha.recaptcha_secret_key', config('captcha.recaptcha.secret_key', ''))
                    ),
                    'score_threshold' => (float) Setting::get('captcha.recaptcha_score_threshold', config('captcha.recaptcha.score_threshold', 0.5)),
                ],
                'turnstile' => [
                    'site_key' => (string) Setting::get('captcha.turnstile_site_key', config('captcha.turnstile.site_key', '')),
                    'secret_key' => $this->maskSecret(
                        (string) Setting::get('captcha.turnstile_secret_key', config('captcha.turnstile.secret_key', ''))
                    ),
                ],
            ],
        ]);
    }

    /**
     * Update CAPTCHA settings.
     */
    public function update(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'enabled' => 'required|boolean',
            'provider' => 'required|in:recaptcha,turnstile',
            'recaptcha_site_key' => 'nullable|string|max:255',
            'recaptcha_secret_key' => 'nullable|string|max:255',
            'recaptcha_score_threshold' => 'nullable|numeric|min:0|max:1',
            'turnstile_site_key' => 'nullable|string|max:255',
            'turnstile_secret_key' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        // Validate that the selected provider has keys configured
        if ($validated['enabled']) {
            $provider = $validated['provider'];

            if ($provider === 'recaptcha') {
                $siteKey = $validated['recaptcha_site_key']
                    ?? Setting::get('captcha.recaptcha_site_key', '');
                $secretKey = $validated['recaptcha_secret_key']
                    ?? Setting::get('captcha.recaptcha_secret_key', '');

                if (empty($siteKey) || empty($secretKey)) {
                    return response()->json([
                        'message' => 'reCAPTCHA site key and secret key are required when enabling reCAPTCHA.',
                        'errors' => [
                            'recaptcha_site_key' => empty($siteKey) ? ['Site key is required.'] : [],
                            'recaptcha_secret_key' => empty($secretKey) ? ['Secret key is required.'] : [],
                        ],
                    ], 422);
                }
            }

            if ($provider === 'turnstile') {
                $siteKey = $validated['turnstile_site_key']
                    ?? Setting::get('captcha.turnstile_site_key', '');
                $secretKey = $validated['turnstile_secret_key']
                    ?? Setting::get('captcha.turnstile_secret_key', '');

                if (empty($siteKey) || empty($secretKey)) {
                    return response()->json([
                        'message' => 'Turnstile site key and secret key are required when enabling Turnstile.',
                        'errors' => [
                            'turnstile_site_key' => empty($siteKey) ? ['Site key is required.'] : [],
                            'turnstile_secret_key' => empty($secretKey) ? ['Secret key is required.'] : [],
                        ],
                    ], 422);
                }
            }
        }

        // Save settings
        Setting::set('captcha.enabled', $validated['enabled'], 'boolean', 'captcha');
        Setting::set('captcha.provider', $validated['provider'], 'string', 'captcha');

        if (isset($validated['recaptcha_site_key'])) {
            Setting::set('captcha.recaptcha_site_key', $validated['recaptcha_site_key'], 'string', 'captcha');
        }

        if (isset($validated['recaptcha_secret_key']) && $validated['recaptcha_secret_key'] !== '') {
            Setting::set('captcha.recaptcha_secret_key', $validated['recaptcha_secret_key'], 'string', 'captcha');
        }

        if (isset($validated['recaptcha_score_threshold'])) {
            Setting::set('captcha.recaptcha_score_threshold', (string) $validated['recaptcha_score_threshold'], 'string', 'captcha');
        }

        if (isset($validated['turnstile_site_key'])) {
            Setting::set('captcha.turnstile_site_key', $validated['turnstile_site_key'], 'string', 'captcha');
        }

        if (isset($validated['turnstile_secret_key']) && $validated['turnstile_secret_key'] !== '') {
            Setting::set('captcha.turnstile_secret_key', $validated['turnstile_secret_key'], 'string', 'captcha');
        }

        // Clear cached settings so new values take effect immediately
        Cache::forget('captcha.settings');

        return response()->json([
            'message' => 'CAPTCHA settings updated successfully.',
            'data' => [
                'enabled' => $validated['enabled'],
                'provider' => $validated['provider'],
            ],
        ]);
    }

    /**
     * Test CAPTCHA provider connectivity.
     */
    public function test(Request $request): JsonResponse
    {
        $provider = $request->input('provider', Setting::get('captcha.provider', 'recaptcha'));

        if ($provider === 'recaptcha') {
            $secretKey = Setting::get('captcha.recaptcha_secret_key', config('captcha.recaptcha.secret_key', ''));

            if (empty($secretKey)) {
                return response()->json([
                    'message' => 'reCAPTCHA secret key is not configured.',
                    'status' => 'error',
                ], 422);
            }

            return response()->json([
                'message' => 'reCAPTCHA is configured. Verification will be tested on the next form submission.',
                'status' => 'ok',
                'provider' => 'recaptcha',
            ]);
        }

        if ($provider === 'turnstile') {
            $secretKey = Setting::get('captcha.turnstile_secret_key', config('captcha.turnstile.secret_key', ''));

            if (empty($secretKey)) {
                return response()->json([
                    'message' => 'Turnstile secret key is not configured.',
                    'status' => 'error',
                ], 422);
            }

            return response()->json([
                'message' => 'Turnstile is configured. Verification will be tested on the next form submission.',
                'status' => 'ok',
                'provider' => 'turnstile',
            ]);
        }

        return response()->json([
            'message' => "Unknown provider: {$provider}",
            'status' => 'error',
        ], 422);
    }

    /**
     * Mask a secret key for display, showing only the last 4 characters.
     */
    protected function maskSecret(string $key): string
    {
        if (strlen($key) <= 4) {
            return $key ? str_repeat('*', strlen($key)) : '';
        }

        return str_repeat('*', strlen($key) - 4) . substr($key, -4);
    }
}
