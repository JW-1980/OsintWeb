<?php

declare(strict_types=1);

namespace App\Http\Controllers\Install;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;

/**
 * Installation completion controller
 */
class FinishController extends Controller
{
    /**
     * Display finish page
     */
    public function index(): View
    {
        return view('install.finish');
    }

    /**
     * Complete installation
     */
    public function finish(): RedirectResponse
    {
        try {
            // Generate app key if needed
            if (!env('APP_KEY')) {
                Artisan::call('key:generate', ['--force' => true]);
            }

            // Clear and rebuild caches
            Artisan::call('config:clear');
            Artisan::call('cache:clear');
            Artisan::call('view:clear');
            Artisan::call('route:clear');

            // Optimize for production (skip in local environment)
            if (config('app.env') !== 'local') {
                Artisan::call('config:cache');
                Artisan::call('route:cache');
                Artisan::call('view:cache');
            }

            // Create lock file
            $lockData = [
                'installed_at' => now()->toIso8601String(),
                'version' => config('app.version', '1.0.0'),
                'installer_ip' => request()->ip(),
                'checksum' => hash('sha256', file_get_contents(base_path('.env')) ?: ''),
            ];

            file_put_contents(
                storage_path('installed'),
                json_encode($lockData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
            );

            // Store installation settings to database if settings table exists
            if (session()->has('install_settings')) {
                $settings = session('install_settings');

                // This assumes a Settings model or similar exists
                // If not, these will need to be saved after models are available
                foreach ($settings as $key => $value) {
                    // Setting::set($key, $value);
                    // For now, we'll store in config
                    config(["installer.settings.{$key}" => $value]);
                }

                session()->forget('install_settings');
            }

            return redirect('/')
                ->with('success', 'Installation completed successfully! Please log in with your admin credentials.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withErrors(['installation' => 'Failed to complete installation: ' . $e->getMessage()]);
        }
    }
}
