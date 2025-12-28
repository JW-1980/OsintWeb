---
name: laravel-middleware
description: Laravel middleware for authentication, authorization, request/response handling, and custom logic
tags: [laravel, middleware, authentication, authorization, http, security, routing]
trigger_keywords: [sk-laravel-middleware, "laravel middleware", "request middleware", "response filter", "middleware stack", "http middleware", "route middleware", "middleware group", "before middleware", "after middleware"]
---
# Laravel Middleware

This skill covers Laravel middleware - from basic concepts to advanced patterns for authentication, authorization, request/response manipulation, and custom application logic.

## When to Use

- Creating custom middleware for cross-cutting concerns
- Implementing authentication and authorization
- Validating or transforming HTTP requests
- Adding headers to HTTP responses
- Logging requests and responses
- Rate limiting and throttling
- Multi-tenancy enforcement
- API versioning
- CORS handling

## Middleware Basics

### 1. What is Middleware?

Middleware acts as a bridge between a request and a response. It provides a convenient mechanism for filtering HTTP requests entering your application.

**Request Flow**:
```
Browser → Middleware 1 → Middleware 2 → Controller → Middleware 2 → Middleware 1 → Browser
         (before)      (before)                    (after)       (after)
```

### 2. Creating Middleware

```bash
php artisan make:middleware CheckCompanyAccess
```

**Generated file** (`app/Http/Middleware/CheckCompanyAccess.php`):
```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckCompanyAccess
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Before request reaches controller

        $response = $next($request);

        // After controller has generated response

        return $response;
    }
}
```

### 3. Registering Middleware

**Global Middleware** (`bootstrap/app.php` in Laravel 11+):
```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->append(CheckCompanyAccess::class);
})
```

**Or in `app/Http/Kernel.php` (Laravel 10)**:
```php
protected $middleware = [
    \App\Http\Middleware\CheckCompanyAccess::class,
];
```

**Route Middleware** (`bootstrap/app.php`):
```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'company' => \App\Http\Middleware\CheckCompanyAccess::class,
        'permission' => \App\Http\Middleware\CheckPermission::class,
    ]);
})
```

**Middleware Groups**:
```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->group('web', [
        \App\Http\Middleware\EncryptCookies::class,
        \App\Http\Middleware\VerifyCsrfToken::class,
    ]);

    $middleware->group('api', [
        'throttle:api',
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
    ]);
})
```

## Audit Logging Middleware for Bookkeeping Applications

### Complete Audit Logging Solution

```php
<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AuditLoggingMiddleware
{
    /**
     * Routes that require detailed audit logging
     */
    protected array $auditedRoutes = [
        'invoices.*',
        'tax.*',
        'payroll.*',
        'customers.*',
        'products.*',
        'bank.*',
        'companies.*',
    ];

    /**
     * Fields to redact from logs
     */
    protected array $sensitiveFields = [
        'password',
        'password_confirmation',
        'bsn',
        'bank_account',
        'iban',
        'api_key',
        'secret',
        'token',
    ];

    public function handle(Request $request, Closure $next)
    {
        $startTime = microtime(true);

        // Execute request
        $response = $next($request);

        // Log if this route should be audited
        if ($this->shouldAudit($request)) {
            $this->logRequest($request, $response, $startTime);
        }

        return $response;
    }

    protected function shouldAudit(Request $request): bool
    {
        foreach ($this->auditedRoutes as $pattern) {
            if ($request->routeIs($pattern)) {
                return true;
            }
        }

        return false;
    }

    protected function logRequest(Request $request, $response, float $startTime): void
    {
        $user = $request->user();
        $duration = round((microtime(true) - $startTime) * 1000, 2);

        try {
            AuditLog::create([
                'user_id' => $user?->id,
                'company_id' => $user?->current_company_id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'route_name' => $request->route()?->getName(),
                'route_params' => $this->sanitizeData($request->route()?->parameters() ?? []),
                'request_data' => $this->sanitizeData($request->except($this->sensitiveFields)),
                'response_status' => $response->getStatusCode(),
                'response_message' => $this->getResponseMessage($response),
                'duration_ms' => $duration,
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            // Don't let audit logging break the application
            Log::error('Audit logging failed', [
                'error' => $e->getMessage(),
                'route' => $request->route()?->getName(),
            ]);
        }
    }

    protected function sanitizeData(array $data): array
    {
        foreach ($this->sensitiveFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = '[REDACTED]';
            }
        }

        return $data;
    }

    protected function getResponseMessage($response): ?string
    {
        if ($response->getStatusCode() >= 400) {
            $content = $response->getContent();

            // Try to extract error message from JSON response
            if ($this->isJson($content)) {
                $decoded = json_decode($content, true);
                return $decoded['message'] ?? $decoded['error'] ?? null;
            }

            return substr($content, 0, 200);
        }

        return null;
    }

    protected function isJson($string): bool
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
}
```

### Audit Log Migration

```php
Schema::create('audit_logs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
    $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
    $table->ipAddress('ip_address');
    $table->text('user_agent')->nullable();
    $table->string('method', 10);
    $table->text('url');
    $table->string('route_name')->nullable()->index();
    $table->json('route_params')->nullable();
    $table->json('request_data')->nullable();
    $table->unsignedSmallInteger('response_status');
    $table->text('response_message')->nullable();
    $table->decimal('duration_ms', 10, 2);
    $table->timestamp('created_at');

    // Indexes for common queries
    $table->index(['company_id', 'created_at']);
    $table->index(['user_id', 'created_at']);
    $table->index(['route_name', 'created_at']);
    $table->index(['response_status', 'created_at']);
});
```

### Financial Transaction Audit Middleware

```php
<?php

namespace App\Http\Middleware;

use App\Models\FinancialAuditLog;
use Closure;
use Illuminate\Http\Request;

class FinancialTransactionAudit
{
    /**
     * Log all financial transactions with extra detail
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Only log successful financial mutations
        if ($response->isSuccessful() && $this->isFinancialMutation($request)) {
            $this->logFinancialTransaction($request, $response);
        }

        return $response;
    }

    protected function isFinancialMutation(Request $request): bool
    {
        return in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])
            && $request->routeIs([
                'invoices.store',
                'invoices.update',
                'invoices.destroy',
                'payments.store',
                'tax.submit',
                'payroll.process',
            ]);
    }

    protected function logFinancialTransaction(Request $request, $response): void
    {
        $user = $request->user();

        // Get the affected resource
        $resource = $this->extractResource($request, $response);

        FinancialAuditLog::create([
            'user_id' => $user->id,
            'company_id' => $user->current_company_id,
            'action' => $request->route()->getName(),
            'resource_type' => $resource['type'] ?? null,
            'resource_id' => $resource['id'] ?? null,
            'before_state' => $resource['before'] ?? null,
            'after_state' => $resource['after'] ?? null,
            'amount' => $resource['amount'] ?? null,
            'description' => $this->generateDescription($request),
            'ip_address' => $request->ip(),
            'created_at' => now(),
        ]);
    }

    protected function extractResource(Request $request, $response): array
    {
        // Extract from route model binding
        $invoice = $request->route('invoice');
        $payment = $request->route('payment');

        if ($invoice) {
            return [
                'type' => 'invoice',
                'id' => $invoice->id,
                'amount' => $invoice->total,
                'before' => $invoice->getOriginal(),
                'after' => $invoice->getAttributes(),
            ];
        }

        if ($payment) {
            return [
                'type' => 'payment',
                'id' => $payment->id,
                'amount' => $payment->amount,
            ];
        }

        return [];
    }

    protected function generateDescription(Request $request): string
    {
        $action = $request->route()->getName();

        return match ($action) {
            'invoices.store' => 'Created new invoice',
            'invoices.update' => 'Updated invoice',
            'invoices.destroy' => 'Deleted invoice',
            'payments.store' => 'Recorded payment',
            'tax.submit' => 'Submitted tax declaration',
            'payroll.process' => 'Processed payroll',
            default => 'Financial transaction',
        };
    }
}
```

### Real-Time Anomaly Detection Middleware

```php
<?php

namespace App\Http\Middleware;

use App\Services\SecurityAnomalyService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\SecurityAlertMail;

class AnomalyDetectionMiddleware
{
    public function __construct(
        protected SecurityAnomalyService $anomalyService
    ) {}

    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user) {
            $this->detectAnomalies($user, $request);
        }

        return $next($request);
    }

    protected function detectAnomalies($user, Request $request): void
    {
        $anomalies = [];

        // Check 1: Unusual time of access
        $hour = now()->hour;
        if ($hour < 6 || $hour > 22) {
            $anomalies[] = [
                'type' => 'unusual_time',
                'message' => "Access at unusual hour: {$hour}:00",
                'severity' => 'medium',
            ];
        }

        // Check 2: Multiple failed attempts
        $failureKey = "failed_attempts:{$user->id}";
        $failures = Cache::get($failureKey, 0);

        if ($failures > 3) {
            $anomalies[] = [
                'type' => 'multiple_failures',
                'message' => "Multiple failed attempts: {$failures} in last 10 minutes",
                'severity' => 'high',
            ];
        }

        // Check 3: Rapid company switching
        $switchKey = "company_switches:{$user->id}";
        $switches = Cache::get($switchKey, 0);

        if ($switches > 5) {
            $anomalies[] = [
                'type' => 'rapid_company_switching',
                'message' => "Rapid company switching: {$switches} times in 5 minutes",
                'severity' => 'high',
            ];
        }

        // Check 4: Access from new location
        $recentIps = Cache::remember("recent_ips:{$user->id}", 3600, function () use ($user) {
            return AuditLog::where('user_id', $user->id)
                ->where('created_at', '>', now()->subDays(7))
                ->distinct('ip_address')
                ->pluck('ip_address')
                ->toArray();
        });

        $currentIp = $request->ip();
        if (!in_array($currentIp, $recentIps)) {
            $anomalies[] = [
                'type' => 'new_location',
                'message' => "Access from new IP: {$currentIp}",
                'severity' => 'medium',
            ];
        }

        // Log and alert on anomalies
        if (!empty($anomalies)) {
            $this->handleAnomalies($user, $request, $anomalies);
        }
    }

    protected function handleAnomalies($user, Request $request, array $anomalies): void
    {
        // Log to database
        foreach ($anomalies as $anomaly) {
            Log::warning('Security anomaly detected', [
                'user_id' => $user->id,
                'company_id' => $user->current_company_id,
                'type' => $anomaly['type'],
                'message' => $anomaly['message'],
                'severity' => $anomaly['severity'],
                'ip' => $request->ip(),
                'route' => $request->route()?->getName(),
            ]);
        }

        // Send email alert for high severity
        $highSeverity = collect($anomalies)->where('severity', 'high');

        if ($highSeverity->isNotEmpty()) {
            Mail::to(config('app.security_email'))
                ->send(new SecurityAlertMail($user, $anomalies));
        }

        // Track for rate limiting
        $anomalyKey = "anomalies:{$user->id}";
        $count = Cache::increment($anomalyKey);

        if (!Cache::has($anomalyKey . ':expiry')) {
            Cache::put($anomalyKey, $count, now()->addHour());
        }

        // Temporarily lock account after too many anomalies
        if ($count > 10) {
            $user->update(['locked_until' => now()->addHours(1)]);

            Log::critical('User account temporarily locked due to anomalies', [
                'user_id' => $user->id,
                'anomaly_count' => $count,
            ]);
        }
    }
}
```

### Registration and Usage

```php
// bootstrap/app.php (Laravel 11+)
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'audit' => \App\Http\Middleware\AuditLoggingMiddleware::class,
        'financial.audit' => \App\Http\Middleware\FinancialTransactionAudit::class,
        'anomaly.detect' => \App\Http\Middleware\AnomalyDetectionMiddleware::class,
    ]);

    // Apply to web group
    $middleware->group('web', [
        // ... other middleware
        \App\Http\Middleware\AuditLoggingMiddleware::class,
        \App\Http\Middleware\AnomalyDetectionMiddleware::class,
    ]);
})

// Or apply to specific routes
Route::middleware(['auth', 'financial.audit'])->group(function () {
    Route::resource('invoices', InvoiceController::class);
    Route::resource('payments', PaymentController::class);
    Route::post('/tax/submit', [TaxController::class, 'submit']);
});
```

---

## Common Middleware Patterns

### 1. Authentication Middleware

**Check if user is authenticated**:
```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Authenticate
{
    public function handle(Request $request, Closure $next, string ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                return $next($request);
            }
        }

        return redirect()->route('login');
    }
}
```

**Usage**:
```php
Route::get('/dashboard', function () {
    // Only authenticated users
})->middleware('auth');

// Or in controller
public function __construct()
{
    $this->middleware('auth');
}
```

### 2. Authorization Middleware

**Check user permissions**:
```php
<?php

namespace App\Http\Middleware;

use App\Services\CompanyPermissionService;
use Closure;
use Illuminate\Http\Request;

class CheckPermission
{
    public function __construct(
        private CompanyPermissionService $permissionService
    ) {}

    public function handle(Request $request, Closure $next, string $category, string $action = 'view')
    {
        $user = $request->user();
        $company = $user->getCurrentCompany();

        if (!$company) {
            abort(403, 'Geen bedrijf geselecteerd');
        }

        if (!$this->permissionService->hasPermission($user, $company, $category, $action)) {
            abort(403, "U heeft geen toestemming om {$category} te {$action}");
        }

        return $next($request);
    }
}
```

**Usage**:
```php
Route::get('/invoices', [InvoiceController::class, 'index'])
    ->middleware('permission:invoices,view');

Route::post('/invoices', [InvoiceController::class, 'store'])
    ->middleware('permission:invoices,create');
```

### 3. Company Scoping Middleware

**Ensure requests are scoped to user's current company**:
```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

class ScopeToCompany
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user || !$user->current_company_id) {
            return redirect()->route('company.select');
        }

        // Add global scope to all queries
        Builder::macro('forCurrentCompany', function () use ($user) {
            return $this->where('company_id', $user->current_company_id);
        });

        // Store in request for easy access
        $request->merge(['company_id' => $user->current_company_id]);

        return $next($request);
    }
}
```

### 4. Request Validation Middleware

**Validate required headers/parameters**:
```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ValidateApiVersion
{
    public function handle(Request $request, Closure $next)
    {
        $version = $request->header('Accept-Version');

        if (!$version) {
            return response()->json([
                'error' => 'API version header required'
            ], 400);
        }

        if (!in_array($version, ['v1', 'v2'])) {
            return response()->json([
                'error' => 'Invalid API version'
            ], 400);
        }

        // Store for use in controllers
        $request->merge(['api_version' => $version]);

        return $next($request);
    }
}
```

### 5. Response Modification Middleware

**Add headers to all responses**:
```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AddSecurityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        return $response;
    }
}
```

### 6. Logging Middleware

**Log all API requests**:
```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogApiRequests
{
    public function handle(Request $request, Closure $next)
    {
        $startTime = microtime(true);

        $response = $next($request);

        $duration = round((microtime(true) - $startTime) * 1000, 2);

        Log::info('API Request', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_id' => $request->user()?->id,
            'status' => $response->getStatusCode(),
            'duration_ms' => $duration,
        ]);

        return $response;
    }
}
```

### 7. Rate Limiting Middleware

**Custom rate limiting**:
```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;

class ThrottleByCompany
{
    public function __construct(
        private RateLimiter $limiter
    ) {}

    public function handle(Request $request, Closure $next, int $maxAttempts = 60)
    {
        $user = $request->user();
        $company = $user?->getCurrentCompany();

        if (!$company) {
            return $next($request);
        }

        $key = 'company:' . $company->id;

        if ($this->limiter->tooManyAttempts($key, $maxAttempts)) {
            $seconds = $this->limiter->availableIn($key);

            return response()->json([
                'message' => "Te veel verzoeken. Probeer het over {$seconds} seconden opnieuw.",
            ], 429);
        }

        $this->limiter->hit($key, 60); // 60 seconds window

        return $next($request);
    }
}
```

**Usage**:
```php
Route::middleware(['throttle.company:100'])->group(function () {
    // Max 100 requests per minute per company
});
```

## Advanced Patterns

### 1. Terminable Middleware

**Execute code after response is sent to browser**:
```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TrackUsage
{
    public function handle(Request $request, Closure $next)
    {
        return $next($request);
    }

    public function terminate(Request $request, $response): void
    {
        // This runs after response is sent
        DB::table('analytics')->insert([
            'user_id' => $request->user()?->id,
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'created_at' => now(),
        ]);
    }
}
```

### 2. Conditional Middleware

**Apply middleware based on conditions**:
```php
class SomeController extends Controller
{
    public function __construct()
    {
        // Apply to all methods
        $this->middleware('auth');

        // Apply to specific methods
        $this->middleware('permission:invoices,create')->only(['create', 'store']);

        // Apply except specific methods
        $this->middleware('throttle:60,1')->except('index');

        // Conditional application
        $this->middleware(function ($request, $next) {
            if ($request->user()->isAdmin()) {
                return $next($request);
            }

            abort(403);
        })->only('destroy');
    }
}
```

### 3. Middleware Parameters

**Pass parameters to middleware**:
```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        $user = $request->user();

        if (!$user || !$user->hasAnyRole($roles)) {
            abort(403, 'U heeft niet de juiste rol voor deze actie');
        }

        return $next($request);
    }
}
```

**Usage**:
```php
Route::get('/admin', function () {
    // Only admins
})->middleware('role:admin');

Route::get('/management', function () {
    // Admins or managers
})->middleware('role:admin,manager');
```

### 4. Middleware Priority

**Control execution order** (`bootstrap/app.php`):
```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->priority([
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \App\Http\Middleware\Authenticate::class,
        \App\Http\Middleware\ScopeToCompany::class,
        \App\Http\Middleware\CheckPermission::class,
    ]);
})
```

## CORS Middleware

**Handle Cross-Origin Resource Sharing**:
```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Cors
{
    public function handle(Request $request, Closure $next)
    {
        $allowedOrigins = config('cors.allowed_origins', ['*']);
        $origin = $request->header('Origin');

        $response = $next($request);

        if (in_array('*', $allowedOrigins) || in_array($origin, $allowedOrigins)) {
            $response->headers->set('Access-Control-Allow-Origin', $origin ?? '*');
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
            $response->headers->set('Access-Control-Max-Age', '86400');
        }

        return $response;
    }
}
```

**config/cors.php**:
```php
return [
    'allowed_origins' => explode(',', env('CORS_ALLOWED_ORIGINS', 'http://localhost:3000')),
];
```

## Testing Middleware

### 1. Unit Test

```php
<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\CheckPermission;
use App\Models\User;
use Illuminate\Http\Request;
use Tests\TestCase;

class CheckPermissionTest extends TestCase
{
    public function test_allows_user_with_permission()
    {
        $user = User::factory()->create();
        $company = $user->companies()->create(['name' => 'Test Co']);
        $user->switchCompany($company);

        // Grant permission
        $company->permissions()->update([
            'invoices_permissions' => ['view', 'create'],
        ]);

        $request = Request::create('/invoices', 'GET');
        $request->setUserResolver(fn() => $user);

        $middleware = new CheckPermission(app(CompanyPermissionService::class));

        $response = $middleware->handle($request, fn() => response('OK'), 'invoices', 'view');

        $this->assertEquals('OK', $response->getContent());
    }

    public function test_denies_user_without_permission()
    {
        $user = User::factory()->create();
        $company = $user->companies()->create(['name' => 'Test Co']);
        $user->switchCompany($company);

        $request = Request::create('/invoices', 'GET');
        $request->setUserResolver(fn() => $user);

        $middleware = new CheckPermission(app(CompanyPermissionService::class));

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        $middleware->handle($request, fn() => response('OK'), 'invoices', 'create');
    }
}
```

### 2. Feature Test

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class InvoiceAccessTest extends TestCase
{
    public function test_unauthenticated_user_redirected()
    {
        $response = $this->get('/invoices');

        $response->assertRedirect('/login');
    }

    public function test_user_without_permission_denied()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/invoices');

        $response->assertStatus(403);
    }

    public function test_user_with_permission_allowed()
    {
        $user = User::factory()->create();
        $company = $user->companies()->create(['name' => 'Test Co']);
        $user->switchCompany($company);

        $company->permissions()->update([
            'invoices_permissions' => ['view'],
        ]);

        $response = $this->actingAs($user)->get('/invoices');

        $response->assertStatus(200);
    }
}
```

## Best Practices

### 1. Single Responsibility

✅ **Each middleware should do ONE thing**:
```php
// ✅ GOOD: Focused middleware
class CheckAuthentication { }
class CheckCompanyAccess { }
class CheckPermission { }

// ❌ BAD: Too many responsibilities
class CheckEverything {
    // Checks auth, company, permissions, rate limits, etc.
}
```

### 2. Fail Fast

```php
public function handle(Request $request, Closure $next)
{
    // ✅ GOOD: Check and return early
    if (!$request->user()) {
        return redirect('/login');
    }

    if (!$request->user()->getCurrentCompany()) {
        return redirect('/company/select');
    }

    return $next($request);
}
```

### 3. Use Dependency Injection

```php
// ✅ GOOD: Inject dependencies
class CheckPermission
{
    public function __construct(
        private CompanyPermissionService $permissionService
    ) {}
}

// ❌ BAD: Use facades or manual instantiation
class CheckPermission
{
    public function handle(Request $request, Closure $next)
    {
        $service = new CompanyPermissionService();
    }
}
```

### 4. Descriptive Naming

```php
// ✅ GOOD: Clear names
class EnsureUserHasCompanyAccess { }
class ValidateApiToken { }
class LogApiRequest { }

// ❌ BAD: Vague names
class Check { }
class Validator { }
class Logger { }
```

### 5. Handle Exceptions

```php
public function handle(Request $request, Closure $next)
{
    try {
        $company = $this->getCompanyFromRequest($request);

        if (!$this->userHasAccess($request->user(), $company)) {
            abort(403);
        }

        return $next($request);
    } catch (\Exception $e) {
        Log::error('Company access check failed', [
            'user_id' => $request->user()?->id,
            'error' => $e->getMessage(),
        ]);

        return redirect()->route('dashboard')
            ->with('error', 'Er is een fout opgetreden bij het controleren van toegang.');
    }
}
```

## Common Middleware Use Cases

### 1. Multi-Tenancy

```php
class SetDatabaseConnection
{
    public function handle(Request $request, Closure $next)
    {
        $company = $request->user()->getCurrentCompany();

        config(['database.connections.tenant' => [
            'driver' => 'mysql',
            'database' => 'company_' . $company->id,
            // ...
        ]]);

        DB::purge('tenant');
        DB::reconnect('tenant');

        return $next($request);
    }
}
```

### 2. API Versioning

```php
class ApiVersion
{
    public function handle(Request $request, Closure $next, string $version)
    {
        $request->merge(['api_version' => $version]);

        return $next($request);
    }
}

// routes/api.php
Route::prefix('v1')->middleware('api.version:v1')->group(function () {
    // v1 routes
});

Route::prefix('v2')->middleware('api.version:v2')->group(function () {
    // v2 routes
});
```

### 3. Maintenance Mode with Whitelist

```php
class CheckMaintenanceMode
{
    public function handle(Request $request, Closure $next)
    {
        if (app()->isDownForMaintenance()) {
            $allowedIps = config('app.maintenance_whitelist', []);

            if (!in_array($request->ip(), $allowedIps)) {
                abort(503);
            }
        }

        return $next($request);
    }
}
```

---

## Request Signature Validation Middleware

### 1. Webhook Signature Verification

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifyWebhookSignature
{
    public function handle(Request $request, Closure $next, string $provider)
    {
        $signature = $request->header('X-Webhook-Signature');
        $secret = config("services.{$provider}.webhook_secret");

        if (!$signature || !$secret) {
            abort(401, 'Missing signature or secret');
        }

        $expectedSignature = hash_hmac('sha256', $request->getContent(), $secret);

        if (!hash_equals($expectedSignature, $signature)) {
            abort(403, 'Invalid webhook signature');
        }

        return $next($request);
    }
}

// Usage
Route::post('/webhooks/mollie', [WebhookController::class, 'mollie'])
    ->middleware('webhook.signature:mollie');
```

### 2. API Request Signing

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifyApiSignature
{
    /**
     * Verify HMAC signature for API requests
     * Client should sign: timestamp + method + path + body
     */
    public function handle(Request $request, Closure $next)
    {
        $signature = $request->header('X-Signature');
        $timestamp = $request->header('X-Timestamp');
        $apiKey = $request->header('X-API-Key');

        if (!$signature || !$timestamp || !$apiKey) {
            return response()->json(['error' => 'Missing authentication headers'], 401);
        }

        // Prevent replay attacks - timestamp must be within 5 minutes
        if (abs(time() - intval($timestamp)) > 300) {
            return response()->json(['error' => 'Request expired'], 401);
        }

        // Look up API secret
        $apiSecret = DB::table('api_keys')
            ->where('key', $apiKey)
            ->where('active', true)
            ->value('secret');

        if (!$apiSecret) {
            return response()->json(['error' => 'Invalid API key'], 401);
        }

        // Compute expected signature
        $payload = $timestamp . $request->method() . $request->path() . $request->getContent();
        $expectedSignature = hash_hmac('sha256', $payload, $apiSecret);

        if (!hash_equals($expectedSignature, $signature)) {
            return response()->json(['error' => 'Invalid signature'], 403);
        }

        return $next($request);
    }
}
```

---

## IP Whitelisting/Blacklisting Middleware

### 1. IP Whitelist

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class IpWhitelist
{
    public function handle(Request $request, Closure $next, string $listName = 'default')
    {
        $allowedIps = config("security.ip_whitelist.{$listName}", []);
        $clientIp = $request->ip();

        // Support CIDR notation
        foreach ($allowedIps as $allowedIp) {
            if ($this->ipInRange($clientIp, $allowedIp)) {
                return $next($request);
            }
        }

        abort(403, 'IP address not allowed');
    }

    private function ipInRange(string $ip, string $range): bool
    {
        if (str_contains($range, '/')) {
            [$subnet, $mask] = explode('/', $range);
            $ipLong = ip2long($ip);
            $subnetLong = ip2long($subnet);
            $maskLong = ~((1 << (32 - $mask)) - 1);

            return ($ipLong & $maskLong) === ($subnetLong & $maskLong);
        }

        return $ip === $range;
    }
}

// config/security.php
return [
    'ip_whitelist' => [
        'admin' => ['192.168.1.0/24', '10.0.0.1'],
        'api' => ['*'],  // Allow all
    ],
];

// Usage
Route::prefix('admin')->middleware('ip.whitelist:admin')->group(fn() => ...);
```

### 2. IP Blacklist with Rate Limiting

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class IpBlacklist
{
    public function handle(Request $request, Closure $next)
    {
        $ip = $request->ip();

        // Check static blacklist
        $blacklist = config('security.ip_blacklist', []);
        if (in_array($ip, $blacklist)) {
            abort(403);
        }

        // Check dynamic blacklist (temporary bans)
        if (Cache::has("ip_banned:{$ip}")) {
            abort(403, 'IP temporarily banned due to suspicious activity');
        }

        // Track failed attempts
        $key = "ip_failed_attempts:{$ip}";
        $attempts = Cache::get($key, 0);

        $response = $next($request);

        // Ban IP after 10 failed attempts in 5 minutes
        if ($response->getStatusCode() === 401 || $response->getStatusCode() === 403) {
            Cache::put($key, $attempts + 1, now()->addMinutes(5));

            if ($attempts + 1 >= 10) {
                Cache::put("ip_banned:{$ip}", true, now()->addHours(1));
                Log::warning("IP banned for suspicious activity: {$ip}");
            }
        }

        return $response;
    }
}
```

---

## Request ID Tracking Middleware

### Trace Requests Across Services

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class RequestTracking
{
    public function handle(Request $request, Closure $next)
    {
        // Get or generate request ID
        $requestId = $request->header('X-Request-ID') ?? Str::uuid()->toString();

        // Store in request for later use
        $request->merge(['request_id' => $requestId]);

        // Add to log context
        Log::shareContext(['request_id' => $requestId]);

        // Process request
        $response = $next($request);

        // Add to response headers
        $response->headers->set('X-Request-ID', $requestId);

        // Log request completion
        Log::info('Request completed', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'status' => $response->getStatusCode(),
            'duration_ms' => round((microtime(true) - LARAVEL_START) * 1000, 2),
        ]);

        return $response;
    }
}

// Usage in logs
Log::info('Processing invoice', ['invoice_id' => $id]);
// Output: [2025-01-15 14:30:00] INFO: Processing invoice {"invoice_id":123,"request_id":"abc-123-def"}
```

---

## Response Caching Middleware

### Cache API Responses

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CacheResponse
{
    public function handle(Request $request, Closure $next, int $minutes = 5)
    {
        // Only cache GET requests
        if ($request->method() !== 'GET') {
            return $next($request);
        }

        // Skip caching for authenticated users with personalized content
        if ($request->user() && !$request->routeIs('api.public.*')) {
            return $next($request);
        }

        // Generate cache key
        $key = 'response_cache:' . md5($request->fullUrl());

        // Return cached response if exists
        if ($cached = Cache::get($key)) {
            return response($cached['content'], $cached['status'])
                ->withHeaders($cached['headers'])
                ->header('X-Cache', 'HIT');
        }

        // Get fresh response
        $response = $next($request);

        // Cache successful responses
        if ($response->isSuccessful()) {
            Cache::put($key, [
                'content' => $response->getContent(),
                'status' => $response->getStatusCode(),
                'headers' => $response->headers->all(),
            ], now()->addMinutes($minutes));

            $response->header('X-Cache', 'MISS');
        }

        return $response;
    }
}

// Usage
Route::get('/api/public/rates', [RatesController::class, 'index'])
    ->middleware('cache.response:60'); // Cache for 60 minutes
```

---

## Database Transaction Middleware

### Wrap Requests in Transactions

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DatabaseTransaction
{
    public function handle(Request $request, Closure $next)
    {
        // Only wrap write operations
        if (!in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            return $next($request);
        }

        return DB::transaction(function () use ($request, $next) {
            return $next($request);
        });
    }
}

// Rollback on any exception - data consistency guaranteed
```

---

## Feature Flag Middleware

### Toggle Features Per Request

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\FeatureFlagService;

class CheckFeatureFlag
{
    public function __construct(
        private FeatureFlagService $features
    ) {}

    public function handle(Request $request, Closure $next, string $feature)
    {
        $user = $request->user();
        $company = $user?->getCurrentCompany();

        if (!$this->features->isEnabled($feature, $user, $company)) {
            return response()->json([
                'error' => 'Feature not available',
                'feature' => $feature,
            ], 403);
        }

        return $next($request);
    }
}

// Feature flag service
class FeatureFlagService
{
    public function isEnabled(string $feature, ?User $user, ?Company $company): bool
    {
        $flag = FeatureFlag::where('name', $feature)->first();

        if (!$flag) {
            return false;
        }

        // Check global toggle
        if (!$flag->enabled) {
            return false;
        }

        // Check user whitelist
        if ($user && in_array($user->id, $flag->user_whitelist ?? [])) {
            return true;
        }

        // Check company whitelist
        if ($company && in_array($company->id, $flag->company_whitelist ?? [])) {
            return true;
        }

        // Check percentage rollout
        if ($flag->rollout_percentage > 0) {
            $hash = crc32($feature . ($user?->id ?? 'guest'));
            return ($hash % 100) < $flag->rollout_percentage;
        }

        return $flag->default_enabled;
    }
}

// Usage
Route::post('/invoices/ai-suggest', [InvoiceController::class, 'aiSuggest'])
    ->middleware('feature:ai_suggestions');
```

---

## A/B Testing Middleware

### Assign Users to Test Groups

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class AbTestMiddleware
{
    public function handle(Request $request, Closure $next, string $experiment)
    {
        $user = $request->user();
        $userId = $user?->id ?? Cookie::get('ab_user_id');

        // Generate stable user ID for guests
        if (!$userId) {
            $userId = 'guest_' . bin2hex(random_bytes(8));
            Cookie::queue('ab_user_id', $userId, 60 * 24 * 365); // 1 year
        }

        // Determine variant (A or B)
        $variant = $this->getVariant($experiment, $userId);

        // Store in request
        $request->merge([
            'ab_experiment' => $experiment,
            'ab_variant' => $variant,
        ]);

        // Log for analytics
        if ($user) {
            AbTestParticipation::updateOrCreate(
                ['user_id' => $user->id, 'experiment' => $experiment],
                ['variant' => $variant]
            );
        }

        return $next($request);
    }

    private function getVariant(string $experiment, string $userId): string
    {
        // Stable hash-based assignment
        $hash = crc32($experiment . $userId);
        return ($hash % 100) < 50 ? 'A' : 'B';
    }
}

// Usage in controller
public function checkout(Request $request)
{
    $variant = $request->input('ab_variant');

    if ($variant === 'B') {
        return view('checkout.new-design');
    }

    return view('checkout.classic');
}
```

---

## Request Sanitization Middleware

### Clean Input Data

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SanitizeInput
{
    /**
     * Fields to exclude from sanitization
     */
    protected array $except = [
        'password',
        'password_confirmation',
        'html_content', // For WYSIWYG editors
    ];

    public function handle(Request $request, Closure $next)
    {
        $input = $request->all();

        array_walk_recursive($input, function (&$value, $key) {
            if (is_string($value) && !in_array($key, $this->except)) {
                // Trim whitespace
                $value = trim($value);

                // Remove null bytes
                $value = str_replace("\0", '', $value);

                // Normalize line endings
                $value = str_replace(["\r\n", "\r"], "\n", $value);

                // Remove invisible characters
                $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value);
            }
        });

        $request->merge($input);

        return $next($request);
    }
}
```

---

## Response Compression Middleware

### Gzip Compress Responses

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CompressResponse
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Check if client accepts gzip
        $acceptEncoding = $request->header('Accept-Encoding', '');

        if (!str_contains($acceptEncoding, 'gzip')) {
            return $response;
        }

        // Don't compress if already compressed
        if ($response->headers->has('Content-Encoding')) {
            return $response;
        }

        // Don't compress small responses
        $content = $response->getContent();
        if (strlen($content) < 1024) {
            return $response;
        }

        // Compress
        $compressed = gzencode($content, 9);

        $response->setContent($compressed);
        $response->headers->set('Content-Encoding', 'gzip');
        $response->headers->set('Content-Length', strlen($compressed));

        return $response;
    }
}
```

---

## Request Replay Protection

### Prevent Duplicate Requests

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PreventReplayAttack
{
    public function handle(Request $request, Closure $next)
    {
        // Only check POST/PUT/PATCH/DELETE requests
        if ($request->isMethodSafe()) {
            return $next($request);
        }

        // Get idempotency key from header
        $idempotencyKey = $request->header('Idempotency-Key');

        if (!$idempotencyKey) {
            return $next($request);
        }

        $cacheKey = 'idempotency:' . $request->user()?->id . ':' . $idempotencyKey;

        // Check if we've seen this request before
        if ($cached = Cache::get($cacheKey)) {
            return response()
                ->json($cached['body'], $cached['status'])
                ->withHeaders($cached['headers'])
                ->header('X-Idempotency-Replayed', 'true');
        }

        // Process request
        $response = $next($request);

        // Cache response for 24 hours
        Cache::put($cacheKey, [
            'status' => $response->getStatusCode(),
            'body' => json_decode($response->getContent(), true),
            'headers' => $response->headers->all(),
        ], now()->addHours(24));

        return $response;
    }
}

// Client usage:
// POST /api/payments
// Headers:
//   Idempotency-Key: unique-request-id-123
```

---

## Troubleshooting

### Issue 1: Middleware Not Executing

**Symptoms**: Middleware code never runs

**Causes & Solutions**:
```php
// 1. Not registered - check bootstrap/app.php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias(['custom' => CustomMiddleware::class]);
})

// 2. Wrong middleware order
// Solution: Define priority
$middleware->priority([...]);

// 3. Exception before middleware
// Check global error handlers
```

### Issue 2: Response Already Sent

**Symptoms**: Headers can't be modified

**Solution**:
```php
// Don't echo/print in middleware
public function handle(Request $request, Closure $next)
{
    // ❌ BAD
    echo "Debug";

    // ✅ GOOD
    Log::debug("Debug");

    return $next($request);
}
```

### Issue 3: Session Not Available

**Symptoms**: Session data null in API routes

**Solution**:
```php
// API routes don't have session by default
// Add session middleware if needed
Route::middleware(['api', 'web'])->group(fn() => ...);

// Or use stateless authentication
Route::middleware(['auth:sanctum'])->group(fn() => ...);
```

---

## Resources

- **Laravel Middleware Docs**: https://laravel.com/docs/middleware
- **HTTP Middleware**: https://laravel.com/docs/middleware#introduction
- **Route Middleware**: https://laravel.com/docs/routing#route-middleware
- **Middleware Groups**: https://laravel.com/docs/middleware#middleware-groups
- **CORS**: https://laravel.com/docs/routing#cors
- **Rate Limiting**: https://laravel.com/docs/rate-limiting
- **OWASP Security**: https://cheatsheetseries.owasp.org/

---

*Version 2.0.0 - Enhanced with request signing, IP filtering, request tracking, caching, transactions, feature flags, A/B testing, sanitization, compression, replay protection, and troubleshooting*

---

## Advanced Middleware Patterns

### 1. Multi-Tenancy Middleware

**Complete Tenant Isolation**:
```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use App\Models\Company;

class TenantMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Extract tenant from subdomain or header
        $tenantIdentifier = $request->header('X-Tenant-ID') 
            ?? $this->getTenantFromSubdomain($request);

        if (!$tenantIdentifier) {
            return response()->json(['error' => 'Tenant not specified'], 400);
        }

        // Load tenant
        $tenant = Company::where('slug', $tenantIdentifier)->firstOrFail();

        // Set tenant context
        app()->instance('tenant', $tenant);
        Config::set('app.tenant_id', $tenant->id);

        // Switch database if multi-database setup
        if ($tenant->database_name) {
            Config::set('database.connections.tenant', [
                'driver' => 'mysql',
                'host' => env('DB_HOST'),
                'database' => $tenant->database_name,
                'username' => env('DB_USERNAME'),
                'password' => env('DB_PASSWORD'),
            ]);
            DB::purge('tenant');
            DB::setDefaultConnection('tenant');
        }

        // Apply global scope for single-database multi-tenancy
        else {
            \App\Models\Invoice::addGlobalScope('tenant', function ($query) use ($tenant) {
                $query->where('company_id', $tenant->id);
            });
            \App\Models\Expense::addGlobalScope('tenant', function ($query) use ($tenant) {
                $query->where('company_id', $tenant->id);
            });
        }

        return $next($request);
    }

    private function getTenantFromSubdomain(Request $request): ?string
    {
        $host = $request->getHost();
        $parts = explode('.', $host);
        
        // Extract subdomain (e.g., acme.boekhouder.nl -> acme)
        if (count($parts) >= 3) {
            return $parts[0];
        }

        return null;
    }
}
```

### 2. API Versioning Middleware

**Flexible API Version Handling**:
```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ApiVersionMiddleware
{
    private const SUPPORTED_VERSIONS = ['v1', 'v2', 'v3'];
    private const DEFAULT_VERSION = 'v1';

    public function handle(Request $request, Closure $next)
    {
        // Get version from header, query param, or URL
        $version = $this->extractVersion($request);

        // Validate version
        if (!in_array($version, self::SUPPORTED_VERSIONS)) {
            return response()->json([
                'error' => 'Unsupported API version',
                'supported_versions' => self::SUPPORTED_VERSIONS,
            ], 400);
        }

        // Set version in request for controllers to use
        $request->attributes->set('api_version', $version);

        // Add version to response headers
        $response = $next($request);
        $response->headers->set('X-API-Version', $version);

        return $response;
    }

    private function extractVersion(Request $request): string
    {
        // 1. Check Accept header (preferred)
        $accept = $request->header('Accept');
        if (preg_match('/application\/vnd\.boekhouder\.(v\d+)\+json/', $accept, $matches)) {
            return $matches[1];
        }

        // 2. Check custom header
        if ($request->hasHeader('API-Version')) {
            return $request->header('API-Version');
        }

        // 3. Check query parameter
        if ($request->query('version')) {
            return $request->query('version');
        }

        // 4. Extract from URL path (/api/v2/invoices)
        if (preg_match('/\/(v\d+)\//', $request->path(), $matches)) {
            return $matches[1];
        }

        return self::DEFAULT_VERSION;
    }
}
```

### 3. Request/Response Transformation Middleware

**Automatic Data Transformation**:
```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TransformRequestMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Transform incoming data (snake_case to camelCase)
        if ($request->isJson()) {
            $transformed = $this->transformKeys($request->json()->all(), 'camel');
            $request->merge($transformed);
        }

        $response = $next($request);

        // Transform outgoing data (camelCase to snake_case)
        if ($response instanceof \Illuminate\Http\JsonResponse) {
            $data = $response->getData(true);
            $transformed = $this->transformKeys($data, 'snake');
            $response->setData($transformed);
        }

        return $response;
    }

    private function transformKeys(array $data, string $case): array
    {
        $result = [];

        foreach ($data as $key => $value) {
            $newKey = $case === 'camel' ? Str::camel($key) : Str::snake($key);

            $result[$newKey] = is_array($value) 
                ? $this->transformKeys($value, $case)
                : $value;
        }

        return $result;
    }
}
```

---

## Testing Middleware

### Unit Testing Middleware

```php
<?php

namespace Tests\Unit\Middleware;

use Tests\TestCase;
use App\Http\Middleware\TenantMiddleware;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TenantMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_loads_tenant_from_header(): void
    {
        $company = Company::factory()->create(['slug' => 'acme']);

        $request = Request::create('/api/invoices', 'GET');
        $request->headers->set('X-Tenant-ID', 'acme');

        $middleware = new TenantMiddleware();
        $middleware->handle($request, function ($req) use ($company) {
            $this->assertEquals($company->id, app('tenant')->id);
            return response()->json(['success' => true]);
        });
    }

    public function test_it_returns_400_when_tenant_missing(): void
    {
        $request = Request::create('/api/invoices', 'GET');

        $middleware = new TenantMiddleware();
        $response = $middleware->handle($request, function ($req) {
            return response()->json(['success' => true]);
        });

        $this->assertEquals(400, $response->status());
    }
}
```

### Feature Testing with Middleware

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InvoiceApiWithMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_middleware_isolates_data(): void
    {
        $company1 = Company::factory()->create(['slug' => 'acme']);
        $company2 = Company::factory()->create(['slug' => 'other']);

        Invoice::factory()->for($company1)->count(5)->create();
        Invoice::factory()->for($company2)->count(3)->create();

        // Request as company1
        $response = $this->withHeader('X-Tenant-ID', 'acme')
            ->actingAs(User::factory()->for($company1)->create())
            ->getJson('/api/invoices');

        $response->assertOk();
        $this->assertCount(5, $response->json('data'));

        // Request as company2
        $response = $this->withHeader('X-Tenant-ID', 'other')
            ->actingAs(User::factory()->for($company2)->create())
            ->getJson('/api/invoices');

        $response->assertOk();
        $this->assertCount(3, $response->json('data'));
    }
}
```

---

## Performance Optimization for Middleware

### 1. Middleware Caching

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CachedPermissionsMiddleware
{
    public function handle(Request $request, Closure $next, string $permission)
    {
        $user = $request->user();

        // Cache permissions for 1 hour
        $hasPermission = Cache::remember(
            "user.{$user->id}.permissions.{$permission}",
            3600,
            fn() => $user->hasPermission($permission)
        );

        if (!$hasPermission) {
            abort(403, 'Unauthorized action');
        }

        return $next($request);
    }
}
```

### 2. Early Termination

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EarlyApiValidation
{
    public function handle(Request $request, Closure $next)
    {
        // Validate critical parameters before hitting controller
        if ($request->route('invoice_id')) {
            if (!is_numeric($request->route('invoice_id'))) {
                return response()->json(['error' => 'Invalid invoice ID'], 400);
            }

            // Check if invoice exists (cached check)
            if (!Cache::has("invoice.{$request->route('invoice_id')}.exists")) {
                $exists = Invoice::where('id', $request->route('invoice_id'))->exists();
                Cache::put("invoice.{$request->route('invoice_id')}.exists", $exists, 300);

                if (!$exists) {
                    return response()->json(['error' => 'Invoice not found'], 404);
                }
            }
        }

        return $next($request);
    }
}
```

---

## Debugging Middleware

### Middleware Logger

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RequestResponseLogger
{
    public function handle(Request $request, Closure $next)
    {
        $startTime = microtime(true);

        // Log request
        Log::channel('middleware')->info('Incoming Request', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'user_id' => $request->user()?->id,
            'headers' => $request->headers->all(),
            'payload' => $request->except(['password', 'password_confirmation']),
        ]);

        $response = $next($request);

        $duration = (microtime(true) - $startTime) * 1000;

        // Log response
        Log::channel('middleware')->info('Outgoing Response', [
            'status' => $response->status(),
            'duration_ms' => round($duration, 2),
            'memory_mb' => round(memory_get_peak_usage() / 1024 / 1024, 2),
        ]);

        return $response;
    }
}
```

---

## Troubleshooting Middleware Issues

### Problem 1: Middleware Order Causing Issues

**Symptoms**: Authentication fails, data not available

**Solution**:
```php
// Kernel.php - Order matters!
protected $middlewareGroups = [
    'api' => [
        \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        'throttle:api',
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
        
        // Authentication BEFORE tenant
        \App\Http\Middleware\Authenticate::class,
        
        // Tenant isolation AFTER auth
        \App\Http\Middleware\TenantMiddleware::class,
        
        // Permissions AFTER tenant
        \App\Http\Middleware\CheckPermissions::class,
    ],
];
```

### Problem 2: Middleware Not Applied

**Symptoms**: Routes bypass middleware

**Solution**:
```php
// 1. Check route definition
Route::middleware(['auth:sanctum', 'tenant'])->group(function () {
    Route::apiResource('invoices', InvoiceController::class);
});

// 2. Verify middleware alias in Kernel.php
protected $middlewareAliases = [
    'tenant' => \App\Http\Middleware\TenantMiddleware::class,
];

// 3. Clear route cache
php artisan route:clear
php artisan route:cache
```

### Problem 3: Global Middleware Performance Impact

**Symptoms**: All requests slow, even simple ones

**Solution**:
```php
// DON'T apply heavy middleware globally
protected $middleware = [
    // ❌ BAD - runs on every request
    // \App\Http\Middleware\ExpensiveOperation::class,
];

// DO apply selectively
protected $middlewareGroups = [
    'api' => [
        // ✓ GOOD - only on API routes
        'throttle:api',
        \App\Http\Middleware\SelectiveMiddleware::class,
    ],
];

// Or use route-specific middleware
Route::middleware('expensive')->group(function () {
    // Only these routes get the middleware
});
```

---

## Middleware Checklist

### Development Checklist
- [ ] Middleware has single responsibility
- [ ] Order in kernel is correct
- [ ] Performance impact is minimal
- [ ] Errors are handled gracefully
- [ ] Logging is implemented
- [ ] Tests are written
- [ ] Documentation is updated

### Security Checklist
- [ ] Input validation implemented
- [ ] Authorization checks in place
- [ ] Rate limiting configured
- [ ] CORS properly set
- [ ] XSS protection enabled
- [ ] SQL injection prevention
- [ ] No sensitive data in logs

### Performance Checklist
- [ ] Caching utilized where appropriate
- [ ] Database queries optimized
- [ ] Early termination for invalid requests
- [ ] Lazy loading implemented
- [ ] Memory usage monitored
- [ ] Execution time tracked

---

## Integration with Project Architecture

### Middleware for Bookkeeping App

**Complete Middleware Stack**:
```php
// app/Http/Kernel.php

protected $middlewareGroups = [
    'api' => [
        // 1. Request preparation
        \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        'throttle:api',
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
        
        // 2. CORS
        \App\Http\Middleware\CorsMiddleware::class,
        
        // 3. Authentication
        \App\Http\Middleware\Authenticate::class,
        
        // 4. Tenant isolation
        \App\Http\Middleware\TenantMiddleware::class,
        
        // 5. Authorization
        \App\Http\Middleware\CheckCompanyAccess::class,
        
        // 6. API versioning
        \App\Http\Middleware\ApiVersionMiddleware::class,
        
        // 7. Request logging
        \App\Http\Middleware\RequestLogger::class,
    ],
];

protected $middlewareAliases = [
    'invoice.owner' => \App\Http\Middleware\EnsureInvoiceOwnership::class,
    'expense.owner' => \App\Http\Middleware\EnsureExpenseOwnership::class,
    'admin' => \App\Http\Middleware\RequireAdminRole::class,
    'accountant' => \App\Http\Middleware\RequireAccountantRole::class,
];
```

---

## Related Skills

- **security-expert**: Implement security middleware
- **backend-api**: Build API with middleware protection
- **testing-expert**: Test middleware behavior
- **performance-optimization**: Optimize middleware performance

---

**Version 3.0.0** - Enhanced with multi-tenancy, API versioning, request transformation, comprehensive testing, performance optimization, debugging tools, troubleshooting guides, and complete checklists
