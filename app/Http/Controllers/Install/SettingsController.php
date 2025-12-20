<?php

declare(strict_types=1);

namespace App\Http\Controllers\Install;

use App\Http\Controllers\Controller;
use App\Services\EnvironmentWriter;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Application settings controller
 */
class SettingsController extends Controller
{
    public function __construct(
        private readonly EnvironmentWriter $environmentWriter
    ) {
    }

    /**
     * Display settings configuration page
     */
    public function index(): View
    {
        return view('install.settings', [
            'app_name' => $this->environmentWriter->get('APP_NAME') ?? 'OsintWeb',
            'app_url' => $this->environmentWriter->get('APP_URL') ?? request()->getSchemeAndHttpHost(),
            'timezone' => $this->environmentWriter->get('APP_TIMEZONE') ?? 'UTC',
            'locale' => $this->environmentWriter->get('APP_LOCALE') ?? 'en',
        ]);
    }

    /**
     * Save application settings
     */
    public function save(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'app_name' => 'required|string|max:255',
            'app_url' => 'required|url',
            'timezone' => 'required|timezone',
            'locale' => 'required|string|size:2',
            'map_center_lat' => 'required|numeric|between:-90,90',
            'map_center_lng' => 'required|numeric|between:-180,180',
            'map_zoom' => 'required|integer|between:1,18',
            'map_base_layer' => 'required|string|in:openstreetmap,satellite,terrain',
            'allow_registration' => 'boolean',
            'require_verification' => 'boolean',
            'default_user_role' => 'required|string|in:viewer,contributor,analyst',
            'session_lifetime' => 'required|integer|min:5',
            'api_rate_limit' => 'required|integer|min:10',
            'enable_2fa' => 'boolean',
        ]);

        // Update .env file with core settings
        $this->environmentWriter->update([
            'APP_NAME' => $validated['app_name'],
            'APP_URL' => $validated['app_url'],
            'APP_TIMEZONE' => $validated['timezone'],
            'APP_LOCALE' => $validated['locale'],
            'SESSION_LIFETIME' => (string) $validated['session_lifetime'],
        ]);

        // Store settings in session for later use (will be saved to database after migrations)
        session([
            'install_settings' => [
                'map_center_lat' => $validated['map_center_lat'],
                'map_center_lng' => $validated['map_center_lng'],
                'map_default_zoom' => $validated['map_zoom'],
                'map_base_layer' => $validated['map_base_layer'],
                'allow_registration' => $validated['allow_registration'] ?? false,
                'require_email_verification' => $validated['require_verification'] ?? true,
                'default_user_role' => $validated['default_user_role'],
                'api_rate_limit' => $validated['api_rate_limit'],
                'enable_2fa' => $validated['enable_2fa'] ?? true,
            ],
        ]);

        return redirect()->route('install.admin');
    }
}
