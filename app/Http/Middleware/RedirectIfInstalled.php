<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Block access to installation routes if already installed
 */
class RedirectIfInstalled
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Block access if already installed
        if (file_exists(storage_path('installed'))) {
            abort(403, 'Application is already installed.');
        }

        return $next($request);
    }
}
