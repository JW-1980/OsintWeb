<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\AnalyticsService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to track page views and basic analytics.
 * Only tracks web requests, not API calls.
 */
class TrackAnalytics
{
    public function __construct(
        private readonly AnalyticsService $analyticsService
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only track successful page loads
        if ($response->isSuccessful() && $this->shouldTrack($request)) {
            $this->trackPageView($request);
        }

        return $response;
    }

    /**
     * Determine if this request should be tracked.
     */
    private function shouldTrack(Request $request): bool
    {
        // Skip API requests
        if ($request->is('api/*')) {
            return false;
        }

        // Skip assets
        $path = $request->path();
        if (preg_match('/\.(js|css|png|jpg|jpeg|gif|svg|ico|woff|woff2|ttf|eot)$/i', $path)) {
            return false;
        }

        // Skip certain paths
        $skipPaths = [
            'health',
            'up',
            '_debugbar/*',
            'telescope/*',
            'horizon/*',
        ];

        foreach ($skipPaths as $skipPath) {
            if ($request->is($skipPath)) {
                return false;
            }
        }

        // Only track GET requests
        if (!$request->isMethod('GET')) {
            return false;
        }

        return true;
    }

    /**
     * Track the page view.
     */
    private function trackPageView(Request $request): void
    {
        try {
            $path = '/' . ltrim($request->path(), '/');

            $this->analyticsService->trackPageView(
                request: $request,
                pagePath: $path
            );
        } catch (\Exception $e) {
            // Log but don't fail the request
            \Illuminate\Support\Facades\Log::warning('Failed to track page view', [
                'error' => $e->getMessage(),
                'path' => $request->path(),
            ]);
        }
    }
}
