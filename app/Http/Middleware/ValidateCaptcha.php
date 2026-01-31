<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Validates CAPTCHA tokens for incoming requests.
 *
 * Supports dual providers:
 * - Google reCAPTCHA v3 (score-based: 0.0 bot to 1.0 human)
 * - Cloudflare Turnstile (pass/fail challenge)
 *
 * Settings are loaded from SystemSettings with a 5-minute cache,
 * falling back to config/captcha.php defaults.
 */
class ValidateCaptcha
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $settings = $this->getCaptchaSettings();

        if (! $settings['enabled']) {
            return $next($request);
        }

        $token = $request->input('captcha_token') ?? $request->header('X-Captcha-Token');

        if (empty($token)) {
            return response()->json([
                'message' => 'CAPTCHA verification required.',
                'errors' => [
                    'captcha_token' => ['A CAPTCHA token is required.'],
                ],
            ], 422);
        }

        $provider = $settings['provider'];

        $result = match ($provider) {
            'recaptcha' => $this->verifyRecaptcha($token, $request->ip(), $settings),
            'turnstile' => $this->verifyTurnstile($token, $request->ip(), $settings),
            default => ['success' => false, 'error' => "Unsupported CAPTCHA provider: {$provider}"],
        };

        if (! $result['success']) {
            Log::warning('CAPTCHA verification failed', [
                'provider' => $provider,
                'ip' => $request->ip(),
                'error' => $result['error'] ?? 'Unknown error',
                'score' => $result['score'] ?? null,
            ]);

            return response()->json([
                'message' => 'CAPTCHA verification failed. Please try again.',
                'errors' => [
                    'captcha_token' => [$result['error'] ?? 'Verification failed.'],
                ],
            ], 422);
        }

        return $next($request);
    }

    /**
     * Verify a Google reCAPTCHA v3 token.
     *
     * @return array{success: bool, score?: float, error?: string}
     */
    protected function verifyRecaptcha(string $token, ?string $ip, array $settings): array
    {
        $secretKey = $settings['recaptcha_secret_key'] ?: config('captcha.recaptcha.secret_key');

        if (empty($secretKey)) {
            return ['success' => false, 'error' => 'reCAPTCHA secret key not configured.'];
        }

        try {
            $response = Http::asForm()
                ->timeout(10)
                ->post(config('captcha.recaptcha.verify_url'), [
                    'secret' => $secretKey,
                    'response' => $token,
                    'remoteip' => $ip,
                ]);

            if (! $response->successful()) {
                return ['success' => false, 'error' => 'Failed to contact reCAPTCHA service.'];
            }

            $body = $response->json();

            if (! ($body['success'] ?? false)) {
                $codes = implode(', ', $body['error-codes'] ?? ['unknown']);
                return ['success' => false, 'error' => "reCAPTCHA error: {$codes}"];
            }

            $score = (float) ($body['score'] ?? 0.0);
            $threshold = $settings['recaptcha_score_threshold']
                ?? config('captcha.recaptcha.score_threshold', 0.5);

            if ($score < (float) $threshold) {
                return [
                    'success' => false,
                    'score' => $score,
                    'error' => 'reCAPTCHA score too low.',
                ];
            }

            return ['success' => true, 'score' => $score];
        } catch (\Throwable $e) {
            Log::error('reCAPTCHA verification exception', ['exception' => $e->getMessage()]);
            return ['success' => false, 'error' => 'reCAPTCHA service unavailable.'];
        }
    }

    /**
     * Verify a Cloudflare Turnstile token.
     *
     * @return array{success: bool, error?: string}
     */
    protected function verifyTurnstile(string $token, ?string $ip, array $settings): array
    {
        $secretKey = $settings['turnstile_secret_key'] ?: config('captcha.turnstile.secret_key');

        if (empty($secretKey)) {
            return ['success' => false, 'error' => 'Turnstile secret key not configured.'];
        }

        try {
            $response = Http::asForm()
                ->timeout(10)
                ->post(config('captcha.turnstile.verify_url'), [
                    'secret' => $secretKey,
                    'response' => $token,
                    'remoteip' => $ip,
                ]);

            if (! $response->successful()) {
                return ['success' => false, 'error' => 'Failed to contact Turnstile service.'];
            }

            $body = $response->json();

            if (! ($body['success'] ?? false)) {
                $codes = implode(', ', $body['error-codes'] ?? ['unknown']);
                return ['success' => false, 'error' => "Turnstile error: {$codes}"];
            }

            return ['success' => true];
        } catch (\Throwable $e) {
            Log::error('Turnstile verification exception', ['exception' => $e->getMessage()]);
            return ['success' => false, 'error' => 'Turnstile service unavailable.'];
        }
    }

    /**
     * Load CAPTCHA settings from database cache, falling back to config.
     *
     * @return array<string, mixed>
     */
    protected function getCaptchaSettings(): array
    {
        return Cache::remember('captcha.settings', config('captcha.cache_ttl', 300), function () {
            return [
                'enabled' => (bool) Setting::get('captcha.enabled', config('captcha.enabled', false)),
                'provider' => (string) Setting::get('captcha.provider', config('captcha.provider', 'recaptcha')),
                'recaptcha_secret_key' => (string) Setting::get('captcha.recaptcha_secret_key', ''),
                'recaptcha_score_threshold' => (float) Setting::get('captcha.recaptcha_score_threshold', config('captcha.recaptcha.score_threshold', 0.5)),
                'turnstile_secret_key' => (string) Setting::get('captcha.turnstile_secret_key', ''),
            ];
        });
    }
}
