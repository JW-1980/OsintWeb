<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Check if the application is installed and redirect to installer if needed
 */
class CheckInstallation
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip if already installed
        if (file_exists(storage_path('installed'))) {
            return $next($request);
        }

        // Skip if already on install routes
        if ($request->is('install') || $request->is('install/*')) {
            return $next($request);
        }

        // Redirect to installation wizard
        return redirect()->route('install.welcome');
    }
}
