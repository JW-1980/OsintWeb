<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Content Security Policy (CSP) headers implementation.
        // It strictly limits the domains from which various resources can be loaded, preventing XSS and other attacks.
        $csp = "default-src 'self'; " .
               "script-src 'self' 'unsafe-inline' 'unsafe-eval'; " .
               "style-src 'self' 'unsafe-inline'; " .
               "img-src 'self' data: https: blob:; " .
               "font-src 'self'; " .
               "connect-src 'self' https: wss:; " .
               "media-src 'self'; " .
               "object-src 'none'; " .
               "frame-src 'self'; " .
               "base-uri 'self'; " .
               "form-action 'self';";

        // Applying various security headers
        $response->headers->set('Content-Security-Policy', $csp);
        $response->headers->set('X-Content-Type-Options', 'nosniff'); // Prevents MIME-sniffing
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN'); // Prevents clickjacking
        $response->headers->set('X-XSS-Protection', '1; mode=block'); // Enables cross-site scripting filter
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin'); // Strict referer-policy
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload'); // Enforces HTTPS

        return $response;
    }
}
