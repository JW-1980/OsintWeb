<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\TrainingSession;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * TrainingSandbox Middleware
 *
 * Detects if the user is in an active training session and applies
 * sandbox data isolation to prevent training data from affecting production.
 *
 * This middleware:
 * - Checks if user has an active training session
 * - Adds sandbox prefix to request context
 * - Marks requests as "sandbox mode" for downstream handlers
 * - Prevents certain operations that shouldn't be allowed in sandbox mode
 */
class TrainingSandbox
{
    /**
     * Routes that are allowed in sandbox mode.
     *
     * @var array<string>
     */
    private const ALLOWED_SANDBOX_ROUTES = [
        'api/training/*',
        'api/events/*',
        'api/equipment/*',
        'api/sources/*',
        'api/geolocation/*',
        'api/evidence/*',
        'api/audio-analysis/*',
        'api/reverse-image/*',
        'api/video-analysis/*',
        'api/social-media/*',
        'api/documents/*',
        'api/disinformation/*',
        'api/flight-tracking/*',
        'api/maritime/*',
        'api/network-analysis/*',
    ];

    /**
     * Routes that are blocked in sandbox mode (sensitive operations).
     *
     * @var array<string>
     */
    private const BLOCKED_SANDBOX_ROUTES = [
        'api/auth/password',
        'api/account/deletion',
        'api/admin/*',
        'api/export/*',
    ];

    /**
     * HTTP methods that create/modify data and need sandbox isolation.
     *
     * @var array<string>
     */
    private const DATA_MODIFYING_METHODS = ['POST', 'PUT', 'PATCH', 'DELETE'];

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure(Request): Response $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Skip if not authenticated
        if (!$user) {
            return $next($request);
        }

        // Check for active training session
        $activeSession = $this->getActiveTrainingSession($user->id);

        if (!$activeSession) {
            // No active training session, proceed normally
            $request->attributes->set('sandbox_mode', false);
            $request->attributes->set('sandbox_prefix', null);
            return $next($request);
        }

        // User is in sandbox mode
        $sandboxPrefix = $activeSession->sandbox_data_prefix;

        // Check if route is blocked in sandbox mode
        if ($this->isRouteBlocked($request)) {
            return $this->sandboxBlockedResponse($request);
        }

        // Set sandbox context on request
        $request->attributes->set('sandbox_mode', true);
        $request->attributes->set('sandbox_prefix', $sandboxPrefix);
        $request->attributes->set('training_session_id', $activeSession->id);
        $request->attributes->set('training_session_uuid', $activeSession->uuid);
        $request->attributes->set('training_environment_id', $activeSession->environment_id);

        // Add sandbox header to response
        $response = $next($request);

        if ($response instanceof Response) {
            $response->headers->set('X-Sandbox-Mode', 'true');
            $response->headers->set('X-Sandbox-Session', $activeSession->uuid);
        }

        // Log data modifications in sandbox mode
        if ($this->isDataModifyingRequest($request)) {
            $this->logSandboxActivity($request, $activeSession);
        }

        return $response;
    }

    /**
     * Get the user's active training session.
     *
     * @param int $userId
     * @return TrainingSession|null
     */
    private function getActiveTrainingSession(int $userId): ?TrainingSession
    {
        return TrainingSession::where('user_id', $userId)
            ->where('status', TrainingSession::STATUS_ACTIVE)
            ->with('environment')
            ->first();
    }

    /**
     * Check if the current route is blocked in sandbox mode.
     *
     * @param Request $request
     * @return bool
     */
    private function isRouteBlocked(Request $request): bool
    {
        $path = $request->path();

        foreach (self::BLOCKED_SANDBOX_ROUTES as $pattern) {
            if ($this->matchPath($path, $pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the current route is allowed in sandbox mode.
     *
     * @param Request $request
     * @return bool
     */
    private function isRouteAllowed(Request $request): bool
    {
        $path = $request->path();

        foreach (self::ALLOWED_SANDBOX_ROUTES as $pattern) {
            if ($this->matchPath($path, $pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Match a path against a pattern (supports * wildcard).
     *
     * @param string $path
     * @param string $pattern
     * @return bool
     */
    private function matchPath(string $path, string $pattern): bool
    {
        $pattern = preg_quote($pattern, '/');
        $pattern = str_replace('\*', '.*', $pattern);
        return (bool) preg_match("/^{$pattern}$/", $path);
    }

    /**
     * Check if this is a data-modifying request.
     *
     * @param Request $request
     * @return bool
     */
    private function isDataModifyingRequest(Request $request): bool
    {
        return in_array($request->method(), self::DATA_MODIFYING_METHODS, true);
    }

    /**
     * Return a blocked response for sandbox mode.
     *
     * @param Request $request
     * @return Response
     */
    private function sandboxBlockedResponse(Request $request): Response
    {
        return response()->json([
            'message' => 'This action is not available in training/sandbox mode.',
            'error' => 'sandbox_blocked',
            'details' => 'Please exit training mode to perform this action.',
        ], 403);
    }

    /**
     * Log sandbox activity for audit purposes.
     *
     * @param Request $request
     * @param TrainingSession $session
     * @return void
     */
    private function logSandboxActivity(Request $request, TrainingSession $session): void
    {
        Log::channel('training')->info('Sandbox data modification', [
            'session_uuid' => $session->uuid,
            'user_id' => $request->user()->id,
            'method' => $request->method(),
            'path' => $request->path(),
            'sandbox_prefix' => $session->sandbox_data_prefix,
            'timestamp' => now()->toIso8601String(),
        ]);

        // Also log in the session's action log
        try {
            $session->logAction('api_request', [
                'method' => $request->method(),
                'path' => $request->path(),
                'route_name' => $request->route()?->getName(),
            ]);
        } catch (\Exception $e) {
            // Silent fail - don't break the request for logging failures
        }
    }

    /**
     * Get the sandbox prefix from the current request.
     *
     * Static helper method for use in other parts of the application.
     *
     * @param Request|null $request
     * @return string|null
     */
    public static function getSandboxPrefix(?Request $request = null): ?string
    {
        $request = $request ?? request();
        return $request->attributes->get('sandbox_prefix');
    }

    /**
     * Check if the current request is in sandbox mode.
     *
     * Static helper method for use in other parts of the application.
     *
     * @param Request|null $request
     * @return bool
     */
    public static function isSandboxMode(?Request $request = null): bool
    {
        $request = $request ?? request();
        return (bool) $request->attributes->get('sandbox_mode', false);
    }

    /**
     * Get the training session ID from the current request.
     *
     * Static helper method for use in other parts of the application.
     *
     * @param Request|null $request
     * @return int|null
     */
    public static function getTrainingSessionId(?Request $request = null): ?int
    {
        $request = $request ?? request();
        return $request->attributes->get('training_session_id');
    }

    /**
     * Apply sandbox prefix to a query builder for data isolation.
     *
     * This method can be used in model scopes or service methods
     * to filter data by sandbox prefix when in training mode.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $prefixColumn Column that stores the sandbox prefix
     * @param Request|null $request
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function applySandboxScope($query, string $prefixColumn = 'sandbox_prefix', ?Request $request = null)
    {
        $prefix = self::getSandboxPrefix($request);

        if ($prefix !== null) {
            return $query->where($prefixColumn, $prefix);
        }

        // In non-sandbox mode, exclude sandbox data
        return $query->whereNull($prefixColumn)->orWhere($prefixColumn, '');
    }
}
