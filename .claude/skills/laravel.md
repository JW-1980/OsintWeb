---
name: laravel-expert
description: Comprehensive Laravel expertise covering testing, packages, middleware, deployment, performance optimization, and Dutch bookkeeping application development
version: 2.0.1
tags: [laravel, php, backend, api, testing, middleware, packages, deployment, dutch-bookkeeping]
trigger_keywords: [sk-laravel-expert, laravel development, laravel expert, laravel application, laravel backend, laravel api, laravel best practices, laravel deployment]
consolidated_from: [laravel-ecosystem, laravel-middleware, laravel-test-suite]
---

# Laravel Expert Skill

**This is the consolidated Laravel skill combining:**
- `laravel-ecosystem` - Packages, tools, deployment
- `laravel-middleware` - Middleware patterns and security
- `laravel-test-suite` - Testing strategies and best practices

Complete expertise for Laravel application development, including testing strategies, ecosystem packages, middleware patterns, deployment, and specialized knowledge for Dutch bookkeeping/financial applications.

## When to Use This Skill

- Building or maintaining Laravel applications
- Implementing testing strategies (PHPUnit, Pest, Dusk)
- Working with Laravel packages and ecosystem tools
- Creating custom middleware for authentication, authorization, logging
- Deploying Laravel applications (Forge, Envoyer, Vapor)
- Optimizing Laravel performance
- Building multi-tenant applications
- Implementing Dutch financial/bookkeeping features (BTW, invoicing)

## Related Skills

For detailed coverage, also see:
- **laravel-ecosystem.md** - Deep dive into packages, Sanctum, Horizon, deployment
- **laravel-middleware.md** - Comprehensive middleware patterns, audit logging, security
- **laravel-test-suite.md** - Full testing guide with coverage strategies

---

# PART 1: TESTING

## Test Categories

### Quick Test Commands

```bash
# Run all tests
php artisan test

# Run specific test suites
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature

# Run with coverage (min 80%)
php artisan test --coverage --min=80

# Run specific test file/method
php artisan test tests/Feature/InvoiceTest.php
php artisan test --filter=test_user_can_create_invoice

# Parallel testing (faster)
php artisan test --parallel

# Stop on first failure
php artisan test --stop-on-failure
```

### Testing Best Practices (AAA Pattern)

```php
public function test_user_can_create_invoice_with_permission()
{
    // Arrange - Set up test data
    $user = User::factory()->create();
    $company = Company::factory()->create();
    $user->companies()->attach($company, ['role' => 'admin']);

    // Act - Perform the action
    $response = $this->actingAs($user)
        ->post('/api/invoices', [
            'client_id' => Client::factory()->create()->id,
            'amount' => 1000,
        ]);

    // Assert - Verify the result
    $response->assertStatus(201);
    $this->assertDatabaseHas('invoices', [
        'company_id' => $company->id,
    ]);
}
```

### Multi-Tenancy Testing

```php
public function test_user_can_only_see_own_company_invoices()
{
    $company1 = Company::factory()->create();
    $company2 = Company::factory()->create();

    $user = User::factory()->create();
    $user->companies()->attach($company1, ['role' => 'admin']);

    $invoice1 = Invoice::factory()->create(['company_id' => $company1->id]);
    $invoice2 = Invoice::factory()->create(['company_id' => $company2->id]);

    $this->actingAs($user)
        ->get('/api/invoices')
        ->assertJsonCount(1, 'data')
        ->assertJsonFragment(['id' => $invoice1->id])
        ->assertJsonMissing(['id' => $invoice2->id]);
}
```

### Dutch VAT Testing

```php
class VatCalculationTest extends TestCase
{
    /** @dataProvider dutchVatRatesProvider */
    public function test_vat_rates_for_different_product_types($productType, $expectedRate)
    {
        $product = Product::factory()->create(['type' => $productType]);
        $this->assertEquals($expectedRate, $product->getVatRate());
    }

    public function dutchVatRatesProvider(): array
    {
        return [
            'standard_goods' => ['standard', 0.21],
            'food' => ['food', 0.09],
            'books' => ['books', 0.09],
            'export' => ['export', 0.00],
        ];
    }

    public function test_reverse_charge_for_eu_b2b()
    {
        $invoice = Invoice::factory()->create([
            'client_country' => 'DE',
            'client_vat_number' => 'DE123456789',
            'is_business' => true,
        ]);

        $this->assertTrue($invoice->isReverseCharge());
        $this->assertEquals(0, $invoice->vat_amount);
    }
}
```

### Security Testing

```php
public function test_unauthenticated_users_cannot_access_api()
{
    $this->getJson('/api/invoices')->assertUnauthorized();
}

public function test_sql_injection_prevention()
{
    $maliciousInput = "'; DROP TABLE invoices; --";

    $this->actingAs(User::factory()->create())
        ->get('/api/invoices?search=' . urlencode($maliciousInput))
        ->assertOk();

    $this->assertTrue(Schema::hasTable('invoices'));
}
```

### Coverage Requirements

| Component | Minimum | Target |
|-----------|---------|--------|
| Models | 90% | 95% |
| Controllers | 85% | 90% |
| Services | 90% | 95% |
| Policies | 95% | 100% |
| Overall | 80% | 85% |

---

# PART 2: ECOSYSTEM & PACKAGES

## Official Laravel Packages

### Laravel Sanctum (API Authentication)

```bash
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

```php
// User model
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
}

// Issue token on login
Route::post('/login', function (Request $request) {
    if (Auth::attempt($request->only('email', 'password'))) {
        $token = Auth::user()->createToken('auth-token')->plainTextToken;
        return response()->json(['token' => $token, 'user' => Auth::user()]);
    }
    return response()->json(['message' => 'Invalid credentials'], 401);
});

// Protect routes
Route::middleware('auth:sanctum')->get('/user', fn(Request $request) => $request->user());
```

### Laravel Horizon (Queue Monitoring)

```bash
composer require laravel/horizon
php artisan horizon:install
php artisan migrate
```

```php
// config/horizon.php
'environments' => [
    'production' => [
        'supervisor-1' => [
            'connection' => 'redis',
            'queue' => ['default', 'invoices', 'reports'],
            'balance' => 'auto',
            'processes' => 10,
            'tries' => 3,
        ],
    ],
],
```

### Laravel Telescope (Debugging)

```bash
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```

## Essential Third-Party Packages

### Spatie Laravel Permission

```bash
composer require spatie/laravel-permission
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
```

```php
// Create roles and permissions
Permission::create(['name' => 'edit invoices']);
$role = Role::create(['name' => 'admin']);
$role->givePermissionTo('edit invoices');

// Assign to user
$user->assignRole('admin');

// Middleware
Route::middleware(['permission:edit invoices'])->group(fn() => ...);
```

### Spatie Laravel Backup

```bash
composer require spatie/laravel-backup
php artisan vendor:publish --provider="Spatie\Backup\BackupServiceProvider"
```

```php
// Dutch law: 7 years financial data retention
'cleanup' => [
    'defaultStrategy' => [
        'keepMonthlyBackupsForMonths' => 84, // 7 years
    ],
],
```

### Laravel Excel

```bash
composer require maatwebsite/excel
```

```php
class BtwDeclarationExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Invoice::whereBetween('invoice_date', [$this->start, $this->end])->get();
    }

    public function headings(): array
    {
        return ['Factuurnummer', 'Datum', 'Bedrag ex. BTW', 'BTW Tarief', 'BTW Bedrag', 'Totaal'];
    }
}

// Download
return Excel::download(new BtwDeclarationExport($start, $end), 'btw-aangifte.xlsx');
```

### Laravel Livewire

```bash
composer require livewire/livewire
```

```php
class Counter extends Component
{
    public $count = 0;

    public function increment() { $this->count++; }

    public function render() { return view('livewire.counter'); }
}
```

### Laravel Filament (Admin Panel)

```bash
composer require filament/filament
php artisan filament:install
php artisan make:filament-resource Invoice
```

## Development Tools

### Laravel Pint (Code Style)

```bash
./vendor/bin/pint           # Fix all files
./vendor/bin/pint --test    # Check without fixing
```

### Laravel Sail (Docker)

```bash
composer require laravel/sail --dev
php artisan sail:install
./vendor/bin/sail up
./vendor/bin/sail artisan migrate
```

## Deployment

### Laravel Forge
- One-click server provisioning
- Zero-downtime deployments
- Queue worker management

### Laravel Vapor (Serverless)

```bash
composer require laravel/vapor-cli --dev
php vendor/bin/vapor login
php vendor/bin/vapor init
```

## Performance Optimization

```bash
# Production caching
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Clear all caches
php artisan optimize:clear
```

```php
// Eager loading to prevent N+1
$users = User::with('posts', 'comments')->get();

// Chunk large datasets
User::chunk(200, fn($users) => /* process */);

// Lazy collections for memory efficiency
User::cursor()->each(fn($user) => /* process */);
```

---

# PART 3: MIDDLEWARE

## Creating Middleware

```bash
php artisan make:middleware CheckCompanyAccess
```

```php
class CheckCompanyAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        // Before request
        $response = $next($request);
        // After response
        return $response;
    }
}
```

## Registration (Laravel 11+)

```php
// bootstrap/app.php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'company' => \App\Http\Middleware\CheckCompanyAccess::class,
        'permission' => \App\Http\Middleware\CheckPermission::class,
    ]);
})
```

## Common Middleware Patterns

### Authentication

```php
class Authenticate
{
    public function handle(Request $request, Closure $next, string ...$guards)
    {
        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                return $next($request);
            }
        }
        return redirect()->route('login');
    }
}
```

### Permission Checking

```php
class CheckPermission
{
    public function handle(Request $request, Closure $next, string $category, string $action = 'view')
    {
        $user = $request->user();
        $company = $user->getCurrentCompany();

        if (!$this->permissionService->hasPermission($user, $company, $category, $action)) {
            abort(403, "U heeft geen toestemming");
        }

        return $next($request);
    }
}

// Usage
Route::get('/invoices', [InvoiceController::class, 'index'])
    ->middleware('permission:invoices,view');
```

### Company Scoping (Multi-Tenancy)

```php
class ScopeToCompany
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user || !$user->current_company_id) {
            return redirect()->route('company.select');
        }

        $request->merge(['company_id' => $user->current_company_id]);

        return $next($request);
    }
}
```

### Audit Logging

```php
class AuditLoggingMiddleware
{
    protected array $sensitiveFields = ['password', 'bsn', 'iban', 'api_key'];

    public function handle(Request $request, Closure $next)
    {
        $startTime = microtime(true);
        $response = $next($request);

        if ($this->shouldAudit($request)) {
            AuditLog::create([
                'user_id' => $request->user()?->id,
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'route_name' => $request->route()?->getName(),
                'request_data' => $this->sanitizeData($request->except($this->sensitiveFields)),
                'response_status' => $response->getStatusCode(),
                'duration_ms' => round((microtime(true) - $startTime) * 1000, 2),
            ]);
        }

        return $response;
    }
}
```

### Rate Limiting

```php
class ThrottleByCompany
{
    public function handle(Request $request, Closure $next, int $maxAttempts = 60)
    {
        $company = $request->user()?->getCurrentCompany();
        $key = 'company:' . $company?->id;

        if ($this->limiter->tooManyAttempts($key, $maxAttempts)) {
            $seconds = $this->limiter->availableIn($key);
            return response()->json([
                'message' => "Te veel verzoeken. Probeer over {$seconds} seconden.",
            ], 429);
        }

        $this->limiter->hit($key, 60);
        return $next($request);
    }
}
```

### Security Headers

```php
class AddSecurityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        return $response;
    }
}
```

### Request Tracking

```php
class RequestTracking
{
    public function handle(Request $request, Closure $next)
    {
        $requestId = $request->header('X-Request-ID') ?? Str::uuid()->toString();
        $request->merge(['request_id' => $requestId]);
        Log::shareContext(['request_id' => $requestId]);

        $response = $next($request);
        $response->headers->set('X-Request-ID', $requestId);

        return $response;
    }
}
```

### Feature Flags

```php
class CheckFeatureFlag
{
    public function handle(Request $request, Closure $next, string $feature)
    {
        if (!$this->features->isEnabled($feature, $request->user())) {
            return response()->json(['error' => 'Feature not available'], 403);
        }
        return $next($request);
    }
}

// Usage
Route::post('/invoices/ai-suggest', [InvoiceController::class, 'aiSuggest'])
    ->middleware('feature:ai_suggestions');
```

### Idempotency (Replay Protection)

```php
class PreventReplayAttack
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->isMethodSafe()) {
            return $next($request);
        }

        $idempotencyKey = $request->header('Idempotency-Key');
        if (!$idempotencyKey) {
            return $next($request);
        }

        $cacheKey = 'idempotency:' . $request->user()?->id . ':' . $idempotencyKey;

        if ($cached = Cache::get($cacheKey)) {
            return response()->json($cached['body'], $cached['status'])
                ->header('X-Idempotency-Replayed', 'true');
        }

        $response = $next($request);

        Cache::put($cacheKey, [
            'status' => $response->getStatusCode(),
            'body' => json_decode($response->getContent(), true),
        ], now()->addHours(24));

        return $response;
    }
}
```

## Terminable Middleware

```php
class TrackUsage
{
    public function handle(Request $request, Closure $next)
    {
        return $next($request);
    }

    // Runs AFTER response is sent to browser
    public function terminate(Request $request, $response): void
    {
        DB::table('analytics')->insert([
            'user_id' => $request->user()?->id,
            'url' => $request->fullUrl(),
            'created_at' => now(),
        ]);
    }
}
```

---

# PART 4: DUTCH BOOKKEEPING SPECIFICS

## Dutch VAT Calculation Service

```php
class DutchInvoiceService
{
    public function createInvoice(array $data)
    {
        $amount = $data['amount'];
        $vatRate = $data['vat_rate'] ?? 0.21; // 21% standard Dutch BTW

        return Invoice::create([
            'company_id' => $data['company_id'],
            'number' => $this->generateInvoiceNumber($data['company_id']),
            'amount_ex_vat' => $amount,
            'vat_rate' => $vatRate,
            'vat_amount' => round($amount * $vatRate, 2),
            'total_incl_vat' => round($amount * (1 + $vatRate), 2),
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
        ]);
    }

    private function generateInvoiceNumber($companyId): string
    {
        return DB::transaction(function() use ($companyId) {
            $lastNumber = Invoice::where('company_id', $companyId)
                ->lockForUpdate()
                ->max('sequence_number') ?? 0;
            return sprintf('INV-%04d-%05d', date('Y'), $lastNumber + 1);
        });
    }
}
```

## Horizon Queues for Bookkeeping

```php
// config/horizon.php
'environments' => [
    'production' => [
        'supervisor-invoices' => [
            'queue' => ['invoices', 'invoice-emails'],
            'processes' => 3,
        ],
        'supervisor-btw' => [
            'queue' => ['btw-calculations', 'digipoort'],
            'processes' => 2,
            'timeout' => 300,
        ],
    ],
],
```

## Currency & Date Formatting

```php
// Dutch format: € 1.234,56
number_format($amount, 2, ',', '.');

// Dutch date: 15-01-2025
$date->format('d-m-Y');

// IBAN validation
preg_match('/^NL\d{2}[A-Z]{4}\d{10}$/', $iban);
```

---

# PART 5: BEST PRACTICES

## DO's

- ✅ Use Redis for cache and sessions in production
- ✅ Cache routes, config, and views in production
- ✅ Use eager loading for relationships
- ✅ Queue heavy operations (emails, exports)
- ✅ Write feature tests for critical flows
- ✅ Keep controllers thin, use service classes
- ✅ Use form requests for validation
- ✅ Run `composer audit` for security vulnerabilities
- ✅ Use environment variables for secrets
- ✅ Set up automatic backups (7 years for Dutch law)

## DON'Ts

- ❌ Install packages without checking Laravel version compatibility
- ❌ Use abandoned packages (> 1 year no updates)
- ❌ Echo/print in middleware
- ❌ Ignore N+1 query problems
- ❌ Store secrets in code
- ❌ Run queue workers with `sync` driver in production
- ❌ Skip testing before deployment

## Security Checklist

- ✅ HTTPS everywhere
- ✅ CSRF protection enabled
- ✅ SQL injection prevention (use Eloquent)
- ✅ XSS protection (Blade escapes by default)
- ✅ Mass assignment protection ($fillable)
- ✅ Rate limiting on API routes
- ✅ Input validation
- ✅ Password hashing (Hash facade)
- ✅ Two-factor authentication

---

# PART 6: CI/CD & DEPLOYMENT

## GitHub Actions

```yaml
name: Laravel Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_DATABASE: testing
          MYSQL_ROOT_PASSWORD: password
        ports: ['3306:3306']

    steps:
      - uses: actions/checkout@v3
      - uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
          coverage: xdebug
      - run: composer install
      - run: php artisan test --coverage --min=80
```

## Pre-Commit Hook

```bash
#!/bin/bash
# .git/hooks/pre-commit

./vendor/bin/pint --test && \
./vendor/bin/phpstan analyse && \
php artisan test --stop-on-failure

if [ $? -ne 0 ]; then
    echo "❌ Tests failed. Commit aborted."
    exit 1
fi

echo "✅ All checks passed!"
```

---

# Resources

**Official Documentation:**
- [Laravel Docs](https://laravel.com/docs)
- [Laravel Testing](https://laravel.com/docs/testing)
- [Laravel Middleware](https://laravel.com/docs/middleware)

**Packages:**
- [Packagist](https://packagist.org)
- [Spatie Packages](https://spatie.be/open-source/packages)

**Community:**
- [Laracasts](https://laracasts.com)
- [Laravel News](https://laravel-news.com)
- [Laravel Daily](https://laraveldaily.com)

---

# PART 7: TROUBLESHOOTING

## Common Laravel Issues and Solutions

### Problem 1: "Class not found" after composer install

**Symptoms:**
- `Class 'App\Services\MyService' not found`
- Error after adding new files

**Cause:**
Composer autoloader cache not updated

**Solution:**
```bash
composer dump-autoload
# Or with optimization:
composer dump-autoload -o
```

**Prevention:**
- Use `composer require` instead of manually editing composer.json
- Run `composer dump-autoload` after creating new directories

### Problem 2: Database migration failures

**Symptoms:**
- `SQLSTATE[42S01]: Base table or view already exists`
- Migration stuck at specific point

**Cause:**
- Previous failed migration left tables in inconsistent state
- Migration order issues

**Solution:**
```bash
# Check current migration status
php artisan migrate:status

# Rollback and retry
php artisan migrate:rollback

# If really stuck (DEVELOPMENT ONLY):
php artisan migrate:fresh --seed

# For production - fix migration manually
php artisan migrate:reset
```

**Prevention:**
- Always test migrations on a copy of production data
- Use transactions in migrations
- Never modify already-run migrations in production

### Problem 3: N+1 Query Problems

**Symptoms:**
- Slow page loads
- Hundreds of queries for simple pages
- Memory issues

**Cause:**
Loading relationships inside loops

**Solution:**
```php
// ❌ BAD - N+1 queries
$invoices = Invoice::all();
foreach ($invoices as $invoice) {
    echo $invoice->client->name; // Query per invoice!
}

// ✅ GOOD - Eager loading
$invoices = Invoice::with('client')->get();
foreach ($invoices as $invoice) {
    echo $invoice->client->name; // No extra queries
}

// Enable N+1 detection in development
// AppServiceProvider::boot()
Model::preventLazyLoading(!app()->isProduction());
```

### Problem 4: Redis/Cache connection issues

**Symptoms:**
- `Connection refused` errors
- Session data lost
- Slow performance

**Cause:**
- Redis not running
- Wrong cache driver configured

**Solution:**
```bash
# Check Redis is running
redis-cli ping
# Should return: PONG

# Clear Laravel cache
php artisan cache:clear
php artisan config:clear

# Check .env configuration
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

### Problem 5: Permission denied on storage

**Symptoms:**
- `The stream or file could not be opened`
- File upload failures

**Cause:**
Storage directory permissions wrong

**Solution:**
```bash
# Set correct permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Or using Laravel
php artisan storage:link
```

### Problem 6: Queue jobs not processing

**Symptoms:**
- Emails not sending
- Jobs stuck in queue

**Cause:**
- Queue worker not running
- Wrong queue connection

**Solution:**
```bash
# Start queue worker
php artisan queue:work --daemon

# Check failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all

# With Supervisor (production)
[program:laravel-worker]
command=php /var/www/app/artisan queue:work --daemon --tries=3
autostart=true
autorestart=true
```

### Problem 7: Multi-tenancy data leakage

**Symptoms:**
- User sees other company's data
- company_id missing in queries

**Cause:**
Missing tenant scope

**Solution:**
```php
// Use global scopes on models
class Invoice extends Model
{
    protected static function booted()
    {
        static::addGlobalScope('company', function ($query) {
            if (auth()->check()) {
                $query->where('company_id', auth()->user()->active_company_id);
            }
        });
    }
}

// Or use middleware
class CompanyScope
{
    public function handle($request, $next)
    {
        if ($user = auth()->user()) {
            config(['app.current_company_id' => $user->active_company_id]);
        }
        return $next($request);
    }
}
```

---

# PART 8: CHECKLISTS

## Pre-Implementation Checklist

- [ ] Requirements clearly documented
- [ ] Database schema designed
- [ ] API endpoints defined
- [ ] Test cases identified
- [ ] Security considerations reviewed
- [ ] Performance implications assessed
- [ ] Multi-tenancy requirements verified
- [ ] Dutch compliance requirements checked (BTW, retention)

## Feature Implementation Checklist

- [ ] Database migrations created and tested
- [ ] Models with relationships defined
- [ ] Form requests for validation
- [ ] Service class for business logic
- [ ] Controller with thin methods
- [ ] API resources/transformers
- [ ] Policy for authorization
- [ ] Unit tests written
- [ ] Feature tests written
- [ ] company_id scoping applied

## Pre-Deployment Checklist

- [ ] All tests passing locally
- [ ] Code reviewed
- [ ] .env.example updated
- [ ] Migrations tested on staging
- [ ] Config cached: `php artisan config:cache`
- [ ] Routes cached: `php artisan route:cache`
- [ ] Views cached: `php artisan view:cache`
- [ ] Assets compiled: `npm run build`
- [ ] Queue workers configured
- [ ] Backups verified
- [ ] Monitoring configured

## Post-Deployment Checklist

- [ ] Application accessible
- [ ] Login working
- [ ] Key features tested
- [ ] Logs checked for errors
- [ ] Performance acceptable
- [ ] Queue jobs processing
- [ ] Scheduled tasks running
- [ ] Monitoring alerts working

## Security Audit Checklist

- [ ] HTTPS enforced
- [ ] CSRF protection enabled
- [ ] SQL injection prevented (parameterized queries)
- [ ] XSS prevented (Blade escaping)
- [ ] Mass assignment protected ($fillable)
- [ ] Rate limiting on auth endpoints
- [ ] Passwords properly hashed
- [ ] Sensitive data encrypted
- [ ] File uploads validated
- [ ] API authentication required
- [ ] CORS configured correctly
- [ ] Debug mode disabled in production

---

# PART 9: ANTI-PATTERNS

## Anti-Pattern 1: Fat Controllers

**The Problem:**
```php
// ❌ BAD - All logic in controller
class InvoiceController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([...]); // Validation

        $invoice = Invoice::create($validated); // Creation

        // Business logic
        $invoice->calculateTotal();
        $invoice->applyVat();

        // Notification
        Mail::to($invoice->client)->send(new InvoiceCreated($invoice));

        // Audit
        activity()->on($invoice)->log('created');

        return response()->json($invoice);
    }
}
```

**Why It's Wrong:**
- Hard to test
- Hard to reuse
- Hard to maintain

**The Fix:**
```php
// ✅ GOOD - Thin controller, service class
class InvoiceController extends Controller
{
    public function store(StoreInvoiceRequest $request, InvoiceService $service)
    {
        $invoice = $service->create($request->validated());
        return new InvoiceResource($invoice);
    }
}

class InvoiceService
{
    public function create(array $data): Invoice
    {
        return DB::transaction(function () use ($data) {
            $invoice = Invoice::create($data);
            $this->calculateTotals($invoice);
            $this->sendNotification($invoice);
            return $invoice;
        });
    }
}
```

## Anti-Pattern 2: Missing Tenant Scope

**The Problem:**
```php
// ❌ BAD - Missing company_id check
public function index()
{
    return Invoice::all(); // Returns ALL invoices!
}
```

**Why It's Wrong:**
- Data leakage between tenants
- GDPR violation
- Security breach

**The Fix:**
```php
// ✅ GOOD - Always scope by company
public function index()
{
    return Invoice::where('company_id', auth()->user()->active_company_id)->get();
}

// ✅ BETTER - Use global scope
class Invoice extends Model
{
    use BelongsToCompany; // Trait adds scope automatically
}
```

## Anti-Pattern 3: Hardcoded Values

**The Problem:**
```php
// ❌ BAD - Hardcoded values
$vat = $amount * 0.21;
$retention = 7; // years
```

**Why It's Wrong:**
- Values change (VAT rates do change!)
- Hard to maintain
- No audit trail

**The Fix:**
```php
// ✅ GOOD - Use config/constants
$vat = $amount * config('dutch.vat.standard');
$retention = config('dutch.retention_years');

// ✅ BETTER - Use database settings
$vat = $amount * Setting::get('vat_rate', 0.21);
```

## Anti-Pattern 4: Not Using Transactions

**The Problem:**
```php
// ❌ BAD - No transaction
$invoice = Invoice::create($data);
$lines = InvoiceLine::createMany($linesData);
$ledger->record($invoice); // If this fails, we have orphan data!
```

**The Fix:**
```php
// ✅ GOOD - Use transaction
DB::transaction(function () use ($data, $linesData) {
    $invoice = Invoice::create($data);
    $invoice->lines()->createMany($linesData);
    $this->ledger->record($invoice);
});
```

## Anti-Pattern 5: Exposing Sensitive Data in API

**The Problem:**
```php
// ❌ BAD - Returns everything
return response()->json(User::find($id));
// Exposes: password, remember_token, etc.
```

**The Fix:**
```php
// ✅ GOOD - Use API Resources
return new UserResource(User::find($id));

class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            // Only expose what's needed
        ];
    }
}
```

---

# PART 10: PERFORMANCE OPTIMIZATION

## Database Optimization

### Use Proper Indexes
```php
// Migration
$table->index('company_id');
$table->index(['company_id', 'created_at']);
$table->index(['status', 'due_date']); // For filtering
```

### Use Chunking for Large Datasets
```php
// ❌ BAD - Loads everything into memory
Invoice::all()->each(fn($i) => $i->process());

// ✅ GOOD - Process in chunks
Invoice::chunk(1000, function ($invoices) {
    foreach ($invoices as $invoice) {
        $invoice->process();
    }
});

// ✅ BETTER - Use cursor for read-only
Invoice::cursor()->each(fn($i) => $i->process());

// ✅ BEST - Use lazy collections
Invoice::lazy()->each(fn($i) => $i->process());
```

### Select Only Needed Columns
```php
// ❌ BAD - Selects everything
$invoices = Invoice::all();

// ✅ GOOD - Select only what you need
$invoices = Invoice::select(['id', 'number', 'total'])->get();
```

## Caching Strategies

### Query Caching
```php
// Cache expensive queries
$stats = Cache::remember('company.'.$companyId.'.stats', 3600, function () {
    return [
        'total_invoices' => Invoice::count(),
        'total_revenue' => Invoice::sum('total'),
        'pending_amount' => Invoice::where('status', 'pending')->sum('total'),
    ];
});
```

### Route Model Binding Cache
```php
// In RouteServiceProvider
Route::bind('invoice', function ($value) {
    return Cache::remember("invoice.{$value}", 60, function () use ($value) {
        return Invoice::findOrFail($value);
    });
});
```

## Queue Heavy Operations

```php
// ❌ BAD - Synchronous email
Mail::to($client)->send(new InvoiceCreated($invoice));

// ✅ GOOD - Queued email
Mail::to($client)->queue(new InvoiceCreated($invoice));

// ✅ GOOD - Dedicated job
dispatch(new SendInvoiceEmail($invoice));
```

## Response Compression

```php
// In config/app.php
'compress' => env('RESPONSE_COMPRESS', true),

// Middleware
class CompressResponse
{
    public function handle($request, $next)
    {
        $response = $next($request);

        if ($this->shouldCompress($request, $response)) {
            return $response->setContent(
                gzencode($response->getContent(), 9)
            )->header('Content-Encoding', 'gzip');
        }

        return $response;
    }
}
```

---

# PART 11: SECURITY DEEP DIVE

## Input Validation

```php
// Always validate
public function rules(): array
{
    return [
        'email' => ['required', 'email', 'max:255'],
        'amount' => ['required', 'numeric', 'min:0', 'max:999999.99'],
        'iban' => ['required', 'regex:/^NL\d{2}[A-Z]{4}\d{10}$/'],
        'kvk' => ['required', 'digits:8'],
        'btw' => ['required', 'regex:/^NL\d{9}B\d{2}$/'],
    ];
}
```

## SQL Injection Prevention

```php
// ❌ BAD - Direct query interpolation
$users = DB::select("SELECT * FROM users WHERE name = '$name'");

// ✅ GOOD - Parameterized queries
$users = DB::select("SELECT * FROM users WHERE name = ?", [$name]);

// ✅ BEST - Use Eloquent
$users = User::where('name', $name)->get();
```

## XSS Prevention

```php
// Blade auto-escapes
{{ $user->name }} // Safe

// Don't use {!! !!} unless absolutely necessary
// If needed, sanitize first:
{!! clean($content) !!} // Using purifier package
```

## CSRF Protection

```php
// All POST/PUT/DELETE forms need token
<form method="POST">
    @csrf
    ...
</form>

// API routes use Sanctum tokens instead
Route::middleware('auth:sanctum')->group(function () {
    // Protected API routes
});
```

## Encryption for Sensitive Data

```php
// Encrypt sensitive fields
class BankAccount extends Model
{
    protected $casts = [
        'account_number' => 'encrypted',
        'api_key' => 'encrypted',
    ];
}

// Manual encryption
$encrypted = Crypt::encryptString($apiKey);
$decrypted = Crypt::decryptString($encrypted);
```

## Rate Limiting

```php
// In RouteServiceProvider
RateLimiter::for('api', function (Request $request) {
    return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
});

RateLimiter::for('login', function (Request $request) {
    return Limit::perMinute(5)->by($request->ip());
});
```

---

# PART 12: INTEGRATION GUIDES

## Integration with Other Skills

### Using with Dutch Bookkeeping Expert
When implementing financial features, combine with the dutch-bookkeeping-expert skill for:
- Correct BTW calculations
- Proper invoice numbering
- 7-year retention requirements
- KvK/BTW number validation

### Using with Testing Expert
Reference the testing-expert skill for:
- Test data factories
- Mocking external services
- CI/CD pipeline setup
- Coverage requirements

### Using with Database MySQL Expert
Combine with database-mysql-expert for:
- Query optimization
- Index strategies
- Backup procedures
- Performance tuning

### Using with Security Expert
Reference security-expert for:
- Penetration testing
- Vulnerability scanning
- Security headers
- Compliance audits

---

# PART 13: QUICK REFERENCE

## Most Common Artisan Commands

```bash
# Development
php artisan serve                    # Start dev server
php artisan tinker                   # Interactive shell
php artisan make:model Invoice -mcrf # Model + migration + controller + form request + factory

# Database
php artisan migrate                  # Run migrations
php artisan migrate:fresh --seed     # Reset and seed
php artisan db:seed                  # Run seeders

# Cache
php artisan cache:clear              # Clear cache
php artisan config:cache             # Cache config
php artisan route:cache              # Cache routes
php artisan view:cache               # Cache views
php artisan optimize                 # Cache everything

# Testing
php artisan test                     # Run tests
php artisan test --parallel          # Parallel tests
php artisan test --coverage          # With coverage

# Queue
php artisan queue:work               # Process jobs
php artisan queue:failed             # Show failed jobs
php artisan queue:retry all          # Retry all failed

# Scheduling
php artisan schedule:run             # Run scheduler
php artisan schedule:list            # List tasks
```

## Common Eloquent Operations

```php
// Creating
$invoice = Invoice::create(['amount' => 1000]);

// Finding
$invoice = Invoice::find($id);
$invoice = Invoice::findOrFail($id);
$invoice = Invoice::firstOrCreate(['number' => $num], ['amount' => 0]);

// Updating
$invoice->update(['status' => 'paid']);
Invoice::where('status', 'draft')->update(['status' => 'pending']);

// Deleting
$invoice->delete();
Invoice::destroy([1, 2, 3]);

// Relationships
$invoice->client;                    // belongsTo
$invoice->lines;                     // hasMany
$invoice->payments()->attach($id);   // belongsToMany
$invoice->load('client', 'lines');   // Eager load after

// Scopes
Invoice::pending()->get();           // Custom scope
Invoice::with('client')->get();      // With relationship
Invoice::select('id', 'total')->get(); // Select columns
```

## Common Validation Rules

```php
'email' => 'required|email|unique:users',
'password' => 'required|min:8|confirmed',
'amount' => 'required|numeric|between:0,999999.99',
'date' => 'required|date|after:today',
'file' => 'required|file|mimes:pdf,jpg|max:10240',
'items' => 'required|array|min:1',
'items.*.quantity' => 'required|integer|min:1',
```

---

# PART 14: CONFIGURATION REFERENCE

## Essential .env Variables

```env
# Application
APP_NAME="Boekhouder"
APP_ENV=production
APP_KEY=base64:...
APP_DEBUG=false
APP_URL=https://app.example.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=boekhouder
DB_USERNAME=user
DB_PASSWORD=secret

# Cache & Sessions
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_ENCRYPTION=tls

# Storage
FILESYSTEM_DISK=s3
AWS_BUCKET=boekhouder-files

# Dutch Settings
VAT_STANDARD_RATE=0.21
VAT_REDUCED_RATE=0.09
RETENTION_YEARS=7
DEFAULT_PAYMENT_TERMS=30
```

## Performance Config

```php
// config/cache.php - Production settings
'default' => env('CACHE_DRIVER', 'redis'),
'stores' => [
    'redis' => [
        'driver' => 'redis',
        'connection' => 'cache',
        'lock_connection' => 'default',
    ],
],

// config/database.php - Connection pooling
'mysql' => [
    'driver' => 'mysql',
    'pool' => [
        'min_connections' => 1,
        'max_connections' => 10,
    ],
],
```

---

# PART 15: TRAITS FOR CODE REUSABILITY

## What Are Traits?

Traits are a mechanism for code reuse in PHP that allows you to define methods that can be shared across multiple classes without using inheritance. They solve the problem of single inheritance limitation in PHP.

## Creating Traits

Traits are typically placed in `app/Traits` directory:

```php
// app/Traits/HasUuid.php
namespace App\Traits;

use Illuminate\Support\Str;

trait HasUuid
{
    protected static function bootHasUuid(): void
    {
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = Str::uuid()->toString();
            }
        });
    }
}
```

## Common Trait Use Cases

### 1. Reusable Query Scopes

```php
// app/Traits/HasStatus.php
trait HasStatus
{
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}

// Usage in any model
class Invoice extends Model
{
    use HasStatus;
}

// Then call: Invoice::active()->get();
```

### 2. Audit/Activity Logging

```php
// app/Traits/Auditable.php
trait Auditable
{
    protected static function bootAuditable(): void
    {
        static::created(function ($model) {
            activity()->on($model)->log('created');
        });

        static::updated(function ($model) {
            activity()->on($model)->withProperties([
                'old' => $model->getOriginal(),
                'new' => $model->getAttributes(),
            ])->log('updated');
        });

        static::deleted(function ($model) {
            activity()->on($model)->log('deleted');
        });
    }
}
```

### 3. Relationships Trait (like Spatie Laravel-Permission)

```php
// app/Traits/HasRoles.php
trait HasRoles
{
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    public function hasRole(string $role): bool
    {
        return $this->roles->contains('name', $role);
    }

    public function assignRole(string $role): void
    {
        $roleModel = Role::findByName($role);
        $this->roles()->attach($roleModel);
    }
}
```

### 4. Company Scope for Multi-Tenancy

```php
// app/Traits/BelongsToCompany.php
trait BelongsToCompany
{
    protected static function bootBelongsToCompany(): void
    {
        static::creating(function ($model) {
            if (auth()->check() && empty($model->company_id)) {
                $model->company_id = auth()->user()->current_company_id;
            }
        });

        static::addGlobalScope('company', function ($query) {
            if (auth()->check()) {
                $query->where('company_id', auth()->user()->current_company_id);
            }
        });
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
```

### 5. Soft Delete Restoration

```php
// app/Traits/HasSoftDeleteRestore.php
trait HasSoftDeleteRestore
{
    public function scopeOnlyTrashed($query)
    {
        return $query->whereNotNull('deleted_at');
    }

    public function restoreWithRelations(): void
    {
        DB::transaction(function () {
            $this->restore();
            foreach ($this->getRelationMethods() as $relation) {
                $this->{$relation}()->onlyTrashed()->restore();
            }
        });
    }
}
```

## Conflict Resolution

When two traits have methods with the same name:

```php
class Invoice extends Model
{
    use TraitA, TraitB {
        TraitA::conflictMethod insteadof TraitB;
        TraitB::conflictMethod as traitBMethod;
    }
}
```

## Best Practices

1. **Single Responsibility**: Each trait should do ONE thing well
2. **Naming Convention**: Use descriptive names like `HasUuid`, `CanBeActivated`, `BelongsToCompany`
3. **Boot Methods**: Use `bootTraitName()` for model event hooks
4. **Initialize Methods**: Use `initializeTraitName()` for property initialization
5. **Documentation**: Document what the trait provides
6. **Testing**: Test traits independently with mock models

---

# PART 16: SOLID PRINCIPLES IN LARAVEL

## Overview

SOLID principles are fundamental to professional Laravel development. This section provides Laravel-specific guidance with 10+ tips per principle.

---

## 1. Single Responsibility Principle (SRP)

**Definition**: A class should have only one reason to change.

### Why It Matters in Laravel
- Controllers stay thin and testable
- Services become reusable across controllers
- Models focus on data relationships
- Easier to maintain and extend

### 12 Tips for SRP in Laravel

**Tip 1: Use Form Requests for Validation**
```php
// ❌ BAD: Controller does validation
class InvoiceController extends Controller {
    public function store(Request $request) {
        $request->validate(['client_id' => 'required']);
    }
}

// ✅ GOOD: Form Request handles validation
class StoreInvoiceRequest extends FormRequest {
    public function rules(): array {
        return ['client_id' => 'required|exists:clients,id'];
    }
}
```

**Tip 2: Extract Business Logic to Services**
```php
// ✅ GOOD: Service handles business logic
class InvoiceService {
    public function create(array $data): Invoice {
        return DB::transaction(function () use ($data) {
            $invoice = Invoice::create($data);
            $this->calculateVat($invoice);
            $this->generateNumber($invoice);
            return $invoice;
        });
    }
}
```

**Tip 3: Use Events for Side Effects**
```php
// ✅ GOOD: Events decouple side effects
class InvoiceService {
    public function create(array $data): Invoice {
        $invoice = Invoice::create($data);
        event(new InvoiceCreated($invoice)); // Listeners handle notifications
        return $invoice;
    }
}
```

**Tip 4: Dedicated PDF/Export Services**
```php
class InvoicePdfService {
    public function generate(Invoice $invoice): string {
        return PDF::loadView('invoices.pdf', compact('invoice'))->output();
    }
}
```

**Tip 5: Use Policies for Authorization**
```php
// ✅ GOOD: Policy handles authorization
class InvoicePolicy {
    public function update(User $user, Invoice $invoice): bool {
        return $user->company_id === $invoice->company_id;
    }
}
```

**Tip 6: Repository Pattern for Complex Queries**
```php
class InvoiceRepository {
    public function findOverdue(int $companyId): Collection {
        return Invoice::where('company_id', $companyId)
            ->where('due_date', '<', now())
            ->whereNull('paid_at')
            ->get();
    }
}
```

**Tip 7: Use Actions for Single Operations**
```php
class CreateInvoiceAction {
    public function execute(array $data): Invoice {
        return DB::transaction(fn() => Invoice::create($data));
    }
}
```

**Tip 8: Resource Classes for Transformation**
```php
class InvoiceResource extends JsonResource {
    public function toArray($request): array {
        return ['id' => $this->id, 'total' => $this->formatted_total];
    }
}
```

**Tip 9: Jobs for Background Processing**
```php
class SendInvoiceEmailJob implements ShouldQueue {
    public function handle(Mailer $mailer): void {
        $mailer->to($this->invoice->client)->send(new InvoiceMail($this->invoice));
    }
}
```

**Tip 10: Middleware for Request Processing**
```php
class EnsureCompanyAccess {
    public function handle($request, $next) {
        if ($request->route('invoice')->company_id !== auth()->user()->company_id) {
            abort(403);
        }
        return $next($request);
    }
}
```

**Tip 11: Traits for Reusable Model Behavior**
```php
trait BelongsToCompany {
    protected static function bootBelongsToCompany(): void {
        static::addGlobalScope('company', fn($q) => $q->where('company_id', auth()->user()?->company_id));
    }
}
```

**Tip 12: Keep Controllers Thin**
```php
// ✅ GOOD: Controller only orchestrates
class InvoiceController extends Controller {
    public function store(StoreInvoiceRequest $request, InvoiceService $service) {
        $invoice = $service->create($request->validated());
        return new InvoiceResource($invoice);
    }
}
```

### Common SRP Violations in Laravel
- Controllers with business logic (calculate, format, notify)
- Models with PDF generation, email sending
- Services doing validation AND business logic AND notifications
- Fat middleware doing multiple checks

---

## 2. Open/Closed Principle (OCP)

**Definition**: Software entities should be open for extension, closed for modification.

### Why It Matters in Laravel
- Add payment gateways without modifying core code
- Extend functionality via service providers
- Plugin-friendly architecture

### 11 Tips for OCP in Laravel

**Tip 1: Use Interfaces for Extensibility**
```php
interface PaymentGatewayInterface {
    public function charge(Payment $payment): PaymentResult;
    public function refund(Payment $payment): RefundResult;
}

class MollieGateway implements PaymentGatewayInterface { }
class StripeGateway implements PaymentGatewayInterface { }
// Add new gateways without modifying existing code
```

**Tip 2: Strategy Pattern via Service Container**
```php
// AppServiceProvider
$this->app->bind(PaymentGatewayInterface::class, function ($app) {
    return match(config('payment.default')) {
        'mollie' => new MollieGateway(),
        'stripe' => new StripeGateway(),
    };
});
```

**Tip 3: Factory Pattern for Multiple Implementations**
```php
class PaymentGatewayFactory {
    protected array $gateways = [];

    public function register(string $name, string $class): void {
        $this->gateways[$name] = $class;
    }

    public function make(string $name): PaymentGatewayInterface {
        return app($this->gateways[$name]);
    }
}
```

**Tip 4: Events for Extension Points**
```php
// Core dispatches event
event(new InvoiceCreated($invoice));

// Extensions listen without modifying core
class SendToAccountingSoftware {
    public function handle(InvoiceCreated $event): void { }
}
```

**Tip 5: Middleware Pipeline**
```php
// Open for extension via config
'middleware' => ['auth', 'company', 'audit']
// Add new middleware without modifying routes
```

**Tip 6: Template Method in Base Classes**
```php
abstract class DocumentExporter {
    public function export(Document $doc): string {
        $data = $this->prepareData($doc);
        return $this->format($data);
    }
    abstract protected function format(array $data): string;
}

class PdfExporter extends DocumentExporter { }
class XmlExporter extends DocumentExporter { }
```

**Tip 7: Decorator Pattern**
```php
class CachedInvoiceRepository implements InvoiceRepositoryInterface {
    public function __construct(private InvoiceRepositoryInterface $inner) {}

    public function find(int $id): ?Invoice {
        return Cache::remember("invoice.{$id}", 3600,
            fn() => $this->inner->find($id));
    }
}
```

**Tip 8: Config-Driven Behavior**
```php
// Extend via config, not code
$exporters = config('documents.exporters');
foreach ($exporters as $exporter) {
    app($exporter)->export($document);
}
```

**Tip 9: Macros for Framework Extension**
```php
Collection::macro('toEuros', fn() => $this->map(fn($v) => '€' . number_format($v, 2)));
```

**Tip 10: Use Laravel's Built-in Extension Points**
```php
// Model observers, Gate definitions, Blade directives
Gate::define('edit-invoice', fn($user, $invoice) => ...);
```

**Tip 11: Avoid Switch on Type**
```php
// ❌ BAD: Adding types requires modifying
switch ($type) { case 'pdf': ...; case 'csv': ...; }

// ✅ GOOD: Use polymorphism
$exporter = $this->exporterFactory->make($type);
$exporter->export($document);
```

### Common OCP Violations
- Switch/case on document types
- Editing core services for new features
- Hardcoded class instantiation

---

## 3. Liskov Substitution Principle (LSP)

**Definition**: Derived classes must be substitutable for their base classes.

### Why It Matters in Laravel
- Polymorphic relationships work correctly
- Mock objects behave like real ones in tests
- Interfaces remain reliable contracts

### 10 Tips for LSP in Laravel

**Tip 1: Honor Parent Contracts**
```php
abstract class Document {
    abstract public function getNumber(): string;
    abstract public function getTotal(): float;
}

class Invoice extends Document {
    public function getNumber(): string { return $this->invoice_number; }
    public function getTotal(): float { return $this->total_incl_vat; }
}

class CreditNote extends Document {
    public function getNumber(): string { return $this->credit_note_number; }
    public function getTotal(): float { return -abs($this->total); } // Negative is valid
}
```

**Tip 2: Don't Throw Unexpected Exceptions**
```php
// ❌ BAD: Subclass throws where parent doesn't
class ReadOnlyInvoice extends Invoice {
    public function save(): bool {
        throw new \Exception('Cannot save'); // Violates LSP!
    }
}
```

**Tip 3: Respect Return Types**
```php
interface Repository {
    public function find(int $id): ?Model;
}

// Both implementations honor nullable return
class InvoiceRepository implements Repository { }
class CachedInvoiceRepository implements Repository { }
```

**Tip 4: Don't Strengthen Preconditions**
```php
// ❌ BAD: Subclass requires more
class PaymentProcessor {
    public function process(float $amount): void { }
}

class StrictProcessor extends PaymentProcessor {
    public function process(float $amount): void {
        if ($amount < 10) throw new \Exception(); // Strengthens precondition!
    }
}
```

**Tip 5: Use Composition for Incompatible Behaviors**
```php
// Instead of awkward inheritance, compose
class DraftInvoice {
    private Invoice $invoice;
    // Wrap and limit behavior
}
```

**Tip 6: Test Substitutability**
```php
public function testAllDocumentsCanBeProcessed(): void {
    $documents = [new Invoice(), new CreditNote(), new Quote()];
    foreach ($documents as $doc) {
        $this->assertIsString($doc->getNumber());
        $this->assertIsFloat($doc->getTotal());
    }
}
```

**Tip 7: Proper Interface Segregation Prevents LSP Issues**
If a class can't implement all methods properly, the interface is too broad.

**Tip 8: Abstract Base Classes for Shared Behavior**
```php
abstract class FinancialDocument extends Model {
    abstract public function calculateTotal(): float;

    public function getFormattedTotal(): string {
        return number_format($this->calculateTotal(), 2);
    }
}
```

**Tip 9: Use Traits Carefully**
Traits that assume specific properties can break LSP when used incorrectly.

**Tip 10: Maintain Invariants**
```php
// All documents must have a company_id - enforced in base
abstract class Document extends Model {
    protected static function booted(): void {
        static::creating(fn($doc) => $doc->company_id ??= auth()->user()->company_id);
    }
}
```

### Common LSP Violations
- Throwing exceptions in overridden methods
- Empty method implementations
- Changing return type semantics

---

## 4. Interface Segregation Principle (ISP)

**Definition**: Clients should not depend on interfaces they don't use.

### Why It Matters in Laravel
- Focused interfaces are easier to implement
- Better for mocking in tests
- Cleaner dependency injection

### 10 Tips for ISP in Laravel

**Tip 1: Split Fat Interfaces**
```php
// ❌ BAD: Fat interface
interface DocumentInterface {
    public function save();
    public function delete();
    public function print();
    public function email();
    public function sign();
}

// ✅ GOOD: Segregated interfaces
interface Persistable { public function save(); public function delete(); }
interface Printable { public function toPdf(): string; }
interface Emailable { public function toMail(): Mailable; }
interface Signable { public function sign(string $certificate): void; }
```

**Tip 2: Role-Based Interfaces**
```php
interface Approvable {
    public function approve(User $approver): void;
    public function reject(User $approver, string $reason): void;
}

class Expense implements Persistable, Approvable { }
class TimeEntry implements Persistable { } // No approval needed
```

**Tip 3: Query vs Command Interfaces**
```php
interface InvoiceReader {
    public function find(int $id): ?Invoice;
    public function findByNumber(string $number): ?Invoice;
}

interface InvoiceWriter {
    public function save(Invoice $invoice): void;
    public function delete(Invoice $invoice): void;
}
```

**Tip 4: Small Interfaces for Testing**
```php
interface PaymentCharger {
    public function charge(float $amount): PaymentResult;
}

// Easy to mock single method
$mock = $this->createMock(PaymentCharger::class);
```

**Tip 5: Laravel Contracts as Examples**
Laravel's contracts are well-segregated: `Authenticatable`, `Authorizable`, `CanResetPassword`.

**Tip 6: Avoid "Kitchen Sink" Repositories**
```php
// ❌ BAD: Too many methods
interface InvoiceRepository {
    public function find($id);
    public function findAll();
    public function findByClient($clientId);
    public function findOverdue();
    public function findPaid();
    // ... 20 more methods
}

// ✅ GOOD: Specific query interfaces
interface OverdueInvoiceQuery {
    public function execute(int $companyId): Collection;
}
```

**Tip 7: Feature-Based Interfaces**
```php
interface HasLineItems {
    public function getLineItems(): Collection;
    public function addLineItem(LineItem $item): void;
}

class Invoice implements HasLineItems { }
class Quote implements HasLineItems { }
```

**Tip 8: Implement Only What's Needed**
```php
// Model implements only relevant interfaces
class Invoice extends Model implements Persistable, Printable, Emailable { }
class AuditLog extends Model implements Persistable { } // Read-only, no print/email
```

**Tip 9: Interface Composition**
```php
interface FullDocument extends Persistable, Printable, Emailable, Signable { }

class Invoice implements FullDocument { }
```

**Tip 10: Don't Force Empty Implementations**
```php
// ❌ BAD: Forced to implement unused method
class SimpleNote implements DocumentInterface {
    public function sign(string $cert): void {
        // Do nothing - violates ISP
    }
}
```

### Common ISP Violations
- "God" interfaces with 10+ methods
- Classes with empty method implementations
- Mocks that need many unused stubs

---

## 5. Dependency Inversion Principle (DIP)

**Definition**: Depend on abstractions, not concretions.

### Why It Matters in Laravel
- Testability through mock injection
- Swap implementations via service container
- Loose coupling between modules

### 12 Tips for DIP in Laravel

**Tip 1: Constructor Injection with Interfaces**
```php
class InvoiceService {
    public function __construct(
        private PaymentGatewayInterface $paymentGateway,
        private InvoiceRepositoryInterface $repository
    ) {}
}
```

**Tip 2: Bind Interfaces in Service Providers**
```php
class AppServiceProvider extends ServiceProvider {
    public function register(): void {
        $this->app->bind(PaymentGatewayInterface::class, MollieGateway::class);
        $this->app->bind(InvoiceRepositoryInterface::class, EloquentInvoiceRepository::class);
    }
}
```

**Tip 3: Use Laravel's Container**
```php
// Automatic resolution
$service = app(InvoiceService::class);
// Dependencies injected automatically
```

**Tip 4: Contextual Binding**
```php
$this->app->when(InvoiceService::class)
    ->needs(PaymentGatewayInterface::class)
    ->give(MollieGateway::class);

$this->app->when(SubscriptionService::class)
    ->needs(PaymentGatewayInterface::class)
    ->give(StripeGateway::class);
```

**Tip 5: Environment-Based Binding**
```php
$this->app->bind(EmailSenderInterface::class, function () {
    return app()->environment('testing')
        ? new FakeEmailSender()
        : new SmtpEmailSender();
});
```

**Tip 6: Avoid `new` in Business Logic**
```php
// ❌ BAD: Direct instantiation
class InvoiceService {
    public function process(): void {
        $gateway = new MollieGateway(); // Tight coupling
    }
}

// ✅ GOOD: Inject dependency
class InvoiceService {
    public function __construct(private PaymentGatewayInterface $gateway) {}
}
```

**Tip 7: Factory Methods for Complex Creation**
```php
interface InvoiceFactoryInterface {
    public function createFromQuote(Quote $quote): Invoice;
}
```

**Tip 8: High-Level Defines Interface, Low-Level Implements**
```php
// Domain layer defines interface
namespace App\Domain\Contracts;
interface InvoiceRepository { }

// Infrastructure implements it
namespace App\Infrastructure\Repositories;
class EloquentInvoiceRepository implements InvoiceRepository { }
```

**Tip 9: Test with Interface Mocks**
```php
public function testInvoiceCreation(): void {
    $mockGateway = $this->createMock(PaymentGatewayInterface::class);
    $mockGateway->method('charge')->willReturn(new PaymentResult(true));

    $service = new InvoiceService($mockGateway);
    // Test without real payment gateway
}
```

**Tip 10: Config-Driven Implementation Selection**
```php
$this->app->bind(PaymentGatewayInterface::class, function () {
    $driver = config('payment.driver');
    return match($driver) {
        'mollie' => app(MollieGateway::class),
        'stripe' => app(StripeGateway::class),
    };
});
```

**Tip 11: Avoid Service Locator Anti-Pattern**
```php
// ❌ BAD: Service locator
class InvoiceService {
    public function process(): void {
        $gateway = app(PaymentGatewayInterface::class); // Hidden dependency
    }
}

// ✅ GOOD: Explicit injection
class InvoiceService {
    public function __construct(private PaymentGatewayInterface $gateway) {}
}
```

**Tip 12: Use Interfaces for External Services**
```php
interface KvkClientInterface {
    public function getCompany(string $kvkNumber): CompanyData;
}

class KvkApiClient implements KvkClientInterface { }
class FakeKvkClient implements KvkClientInterface { } // For testing
```

### Common DIP Violations
- Direct `new` instantiation in services
- Static method calls to concrete classes
- Using `app()` inside methods (service locator)
- Depending on Eloquent models in service contracts

---

## SOLID Quick Reference

| Principle | One-Liner | Laravel Tool |
|-----------|-----------|--------------|
| SRP | One class, one job | Form Requests, Services, Events, Jobs |
| OCP | Extend, don't modify | Interfaces, Service Container, Events |
| LSP | Subtypes must be substitutable | Abstract classes, proper inheritance |
| ISP | Small, focused interfaces | Role-based interfaces, contracts |
| DIP | Depend on abstractions | Interfaces, Service Container binding |

## Common Anti-Patterns

| Anti-Pattern | Problem | Solution |
|--------------|---------|----------|
| Fat Controller | Multiple responsibilities | Services, Form Requests, Events |
| God Model | Model does everything | Services, Repositories, Events |
| Tight Coupling | Direct class dependencies | Interfaces + DI container |
| Leaky Abstraction | Implementation details exposed | Proper interface design |
| Feature Envy | Class uses another's data excessively | Move method to data owner |

## SOLID Checklist

Before committing code, verify:
- [ ] Controllers only orchestrate (no business logic)
- [ ] Business logic in dedicated services
- [ ] Validation in Form Requests
- [ ] Side effects via Events/Listeners
- [ ] New features don't modify existing classes
- [ ] Interfaces used for swappable components
- [ ] Subclasses honor parent contracts
- [ ] Interfaces are small and focused
- [ ] Dependencies injected via constructor
- [ ] No direct `new` for swappable dependencies

---

# PART 17: CUSTOM FORM REQUESTS FOR COMPLEX VALIDATION

## Basic Form Request

```php
php artisan make:request StoreInvoiceRequest
```

```php
class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Use Gates for authorization
        return $this->user()->can('create', Invoice::class);
    }

    public function rules(): array
    {
        return [
            'client_id' => ['required', 'exists:clients,id'],
            'invoice_date' => ['required', 'date', 'before_or_equal:today'],
            'due_date' => ['required', 'date', 'after:invoice_date'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.description' => ['required', 'string', 'max:255'],
            'lines.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'lines.*.unit_price' => ['required', 'numeric', 'min:0'],
            'lines.*.vat_rate' => ['required', 'in:0,0.09,0.21'],
        ];
    }
}
```

## Advanced Validation Techniques

### Using Rule Objects

```php
use Illuminate\Validation\Rule;

public function rules(): array
{
    return [
        'email' => [
            'required',
            'email',
            Rule::unique('users')->ignore($this->user->id),
        ],
        'status' => [
            'required',
            Rule::in(['draft', 'sent', 'paid']),
        ],
        'country' => [
            'required',
            Rule::exists('countries', 'code')->where('active', true),
        ],
    ];
}
```

### Conditional Validation with Rule::when

```php
public function rules(): array
{
    return [
        'is_company' => ['required', 'boolean'],
        'company_name' => [
            Rule::when($this->is_company, ['required', 'string', 'max:255']),
        ],
        'vat_number' => [
            Rule::when($this->is_company && $this->country === 'NL', [
                'required',
                'regex:/^NL\d{9}B\d{2}$/',
            ]),
        ],
    ];
}
```

### Custom Validation with withValidator

```php
public function withValidator(Validator $validator): void
{
    $validator->after(function (Validator $validator) {
        // Cross-field validation
        if ($this->total_debit !== $this->total_credit) {
            $validator->errors()->add('balance', 'Debit and credit must balance');
        }

        // Business rule validation
        if ($this->isOverCreditLimit()) {
            $validator->errors()->add('amount', 'Client has exceeded credit limit');
        }
    });
}

private function isOverCreditLimit(): bool
{
    $client = Client::find($this->client_id);
    return $client && ($client->outstanding_balance + $this->amount) > $client->credit_limit;
}
```

### Prepare Data Before Validation

```php
protected function prepareForValidation(): void
{
    $this->merge([
        'slug' => Str::slug($this->title),
        'phone' => preg_replace('/[^0-9+]/', '', $this->phone),
        'email' => strtolower(trim($this->email)),
        'amount' => str_replace(',', '.', $this->amount), // Dutch to standard decimal
    ]);
}
```

### Add Computed Values After Validation

```php
public function validated($key = null, $default = null)
{
    return array_merge(parent::validated(), [
        'user_id' => auth()->id(),
        'company_id' => auth()->user()->current_company_id,
        'created_by' => auth()->id(),
    ]);
}
```

### Custom Error Messages

```php
public function messages(): array
{
    return [
        'client_id.required' => 'Selecteer een klant',
        'client_id.exists' => 'Geselecteerde klant bestaat niet',
        'lines.required' => 'Voeg minimaal één factuurregel toe',
        'lines.*.quantity.min' => 'Aantal moet minimaal :min zijn (regel :position)',
        'due_date.after' => 'Vervaldatum moet na factuurdatum liggen',
    ];
}

public function attributes(): array
{
    return [
        'client_id' => 'klant',
        'invoice_date' => 'factuurdatum',
        'due_date' => 'vervaldatum',
        'lines.*.description' => 'omschrijving',
    ];
}
```

### Stop on First Failure

```php
protected $stopOnFirstFailure = true;
```

### Custom Redirect on Failure

```php
protected $redirect = '/dashboard';
// or
protected $redirectRoute = 'invoices.create';
```

---

# PART 18: POLICIES FOR AUTHORIZATION

## Creating Policies

```php
php artisan make:policy InvoicePolicy --model=Invoice
```

## Basic Policy Structure

```php
class InvoicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('invoices.view');
    }

    public function view(User $user, Invoice $invoice): bool
    {
        // Check company ownership (multi-tenancy)
        return $user->current_company_id === $invoice->company_id
            && $user->hasPermission('invoices.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('invoices.create');
    }

    public function update(User $user, Invoice $invoice): bool
    {
        // Can't update paid invoices
        if ($invoice->status === 'paid') {
            return false;
        }

        return $user->current_company_id === $invoice->company_id
            && $user->hasPermission('invoices.edit');
    }

    public function delete(User $user, Invoice $invoice): bool
    {
        // Can only delete draft invoices
        if ($invoice->status !== 'draft') {
            return false;
        }

        return $user->current_company_id === $invoice->company_id
            && $user->hasPermission('invoices.delete');
    }

    public function restore(User $user, Invoice $invoice): bool
    {
        return $user->hasPermission('invoices.restore');
    }

    public function forceDelete(User $user, Invoice $invoice): bool
    {
        return $user->hasRole('admin');
    }
}
```

## Using Policies in Controllers

```php
class InvoiceController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Invoice::class, 'invoice');
    }

    // Or manually:
    public function show(Invoice $invoice)
    {
        $this->authorize('view', $invoice);
        return new InvoiceResource($invoice);
    }

    public function update(UpdateInvoiceRequest $request, Invoice $invoice)
    {
        $this->authorize('update', $invoice);
        // ...
    }
}
```

## Policy Auto-Discovery

Laravel automatically discovers policies following naming conventions:
- `App\Models\Invoice` → `App\Policies\InvoicePolicy`
- `App\Models\User` → `App\Policies\UserPolicy`

## Authorization Responses with Messages

```php
use Illuminate\Auth\Access\Response;

public function update(User $user, Invoice $invoice): Response
{
    if ($invoice->status === 'paid') {
        return Response::deny('Betaalde facturen kunnen niet worden gewijzigd');
    }

    if ($user->current_company_id !== $invoice->company_id) {
        return Response::deny('U heeft geen toegang tot deze factuur');
    }

    return $user->hasPermission('invoices.edit')
        ? Response::allow()
        : Response::deny('U heeft geen toestemming om facturen te bewerken');
}
```

## Gates vs Policies

```php
// Gates - for actions NOT related to a model
Gate::define('access-admin-dashboard', function (User $user) {
    return $user->hasRole('admin');
});

// Policies - for actions related to a model
// Use policies for Invoice CRUD

// Combining both
public function viewAny(User $user): bool
{
    // Policy can use Gate
    return Gate::allows('access-admin-dashboard') || $user->hasPermission('invoices.view');
}
```

## Using Authorization in Form Requests

```php
class UpdateInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $invoice = $this->route('invoice');
        return $this->user()->can('update', $invoice);
    }
}
```

## Using Authorization in Routes

```php
Route::put('/invoices/{invoice}', [InvoiceController::class, 'update'])
    ->middleware('can:update,invoice');

// For creation (no model instance)
Route::post('/invoices', [InvoiceController::class, 'store'])
    ->middleware('can:create,App\Models\Invoice');
```

## Using Authorization in Blade

```blade
@can('update', $invoice)
    <button>Edit Invoice</button>
@endcan

@cannot('delete', $invoice)
    <span class="text-gray-400">Cannot delete</span>
@endcannot

@canany(['update', 'delete'], $invoice)
    <div class="actions">...</div>
@endcanany
```

---

# PART 19: EAGER LOADING - SOLVING N+1 PROBLEMS

## The N+1 Problem Explained

```php
// ❌ BAD - N+1 queries (1 + 100 = 101 queries for 100 invoices)
$invoices = Invoice::all();
foreach ($invoices as $invoice) {
    echo $invoice->client->name; // Each access triggers a query!
}
```

## Solution 1: with() - Eager Loading at Query Time

```php
// ✅ GOOD - Only 2 queries (1 for invoices, 1 for clients)
$invoices = Invoice::with('client')->get();

// Multiple relationships
$invoices = Invoice::with(['client', 'lines', 'payments'])->get();

// Nested relationships
$invoices = Invoice::with('client.country')->get();

// Select specific columns (IMPORTANT: always include foreign keys!)
$invoices = Invoice::with('client:id,name,email')->get();
```

## Solution 2: load() - Lazy Eager Loading

```php
// Load relationships AFTER initial query
$invoices = Invoice::all();

if ($needClientInfo) {
    $invoices->load('client');
}
```

## Solution 3: Default Eager Loading with $with

```php
class Invoice extends Model
{
    // These relationships are ALWAYS loaded
    protected $with = ['client', 'lines'];
}

// Now Invoice::all() automatically includes client and lines
// Disable when not needed:
Invoice::without('client')->get();
```

## Solution 4: Prevent Lazy Loading (Development)

```php
// In AppServiceProvider::boot()
use Illuminate\Database\Eloquent\Model;

public function boot(): void
{
    Model::preventLazyLoading(!app()->isProduction());
}

// Now lazy loading throws LazyLoadingViolationException in development
```

## Solution 5: Automatic Eager Loading (Laravel 12+)

```php
// In AppServiceProvider::boot()
Model::automaticallyEagerLoadRelationships();

// Laravel will automatically detect and eager load accessed relationships
// Note: Still in beta, use with caution
```

## Eager Loading with Constraints

```php
// Only load active clients
$invoices = Invoice::with(['client' => function ($query) {
    $query->where('active', true);
}])->get();

// Order related records
$users = User::with(['posts' => function ($query) {
    $query->orderBy('created_at', 'desc')->limit(5);
}])->get();
```

## Counting Relationships Efficiently

```php
// ❌ BAD
$users = User::all();
foreach ($users as $user) {
    echo $user->posts->count(); // N+1!
}

// ✅ GOOD - withCount
$users = User::withCount('posts')->get();
foreach ($users as $user) {
    echo $user->posts_count; // No extra queries
}

// Multiple counts
$users = User::withCount(['posts', 'comments', 'likes'])->get();

// Conditional counts
$users = User::withCount(['posts as published_posts_count' => function ($query) {
    $query->where('published', true);
}])->get();
```

## Aggregate Loading

```php
// Sum, Avg, Min, Max
$clients = Client::withSum('invoices', 'total')
    ->withAvg('invoices', 'total')
    ->get();

// Access: $client->invoices_sum_total, $client->invoices_avg_total
```

## Debugging N+1 Problems

```php
// 1. Use Laravel Debugbar
composer require barryvdh/laravel-debugbar --dev

// 2. Use Query Log
DB::enableQueryLog();
// ... run your code ...
dd(DB::getQueryLog());

// 3. Use Laravel Telescope
composer require laravel/telescope --dev

// 4. Check query count
$queryCount = DB::getQueryLog();
$this->assertLessThan(10, count($queryCount));
```

---

# PART 20: LARAVEL ENCRYPTION FEATURES

## Configuration

Encryption requires an APP_KEY in `.env`. Generate with:
```bash
php artisan key:generate
```

## Encrypting and Decrypting

```php
use Illuminate\Support\Facades\Crypt;

// Encrypt
$encrypted = Crypt::encryptString('secret-data');

// Decrypt
try {
    $decrypted = Crypt::decryptString($encrypted);
} catch (DecryptException $e) {
    // Handle invalid data
}

// For serializable data (arrays, objects)
$encrypted = Crypt::encrypt(['key' => 'value']);
$decrypted = Crypt::decrypt($encrypted);
```

## Model-Level Encryption with Casts

```php
class BankAccount extends Model
{
    protected $casts = [
        'account_number' => 'encrypted',
        'api_secret' => 'encrypted',
        'settings' => 'encrypted:array',
        'metadata' => 'encrypted:object',
    ];
}

// Usage - encryption/decryption is automatic
$account = new BankAccount();
$account->account_number = 'NL91ABNA0417164300'; // Encrypted on save
echo $account->account_number; // Decrypted on access
```

## Custom Encryption Cast

```php
class EncryptedIban implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes)
    {
        return $value ? Crypt::decryptString($value) : null;
    }

    public function set($model, string $key, $value, array $attributes)
    {
        return $value ? Crypt::encryptString($value) : null;
    }
}

// Usage in model
protected $casts = [
    'iban' => EncryptedIban::class,
];
```

## Encrypting Queue Payloads

```php
// In config/queue.php
'connections' => [
    'redis' => [
        'driver' => 'redis',
        'encrypt' => true, // Encrypt all queue payloads
    ],
],
```

## Graceful Key Rotation

Laravel supports decryption with previous keys:

```php
// In .env
APP_KEY=base64:newKey...
APP_PREVIOUS_KEYS=base64:oldKey1...,base64:oldKey2...
```

## Best Practices

1. **Never expose APP_KEY**: Keep it secret, never commit to git
2. **Rotate keys regularly**: Update APP_KEY periodically
3. **Encrypt selectively**: Only encrypt truly sensitive data (performance cost)
4. **Handle exceptions**: Always wrap decryption in try-catch
5. **Backup keys**: Store encryption keys securely for disaster recovery
6. **Test decryption**: Verify encrypted data can be decrypted after key changes

---

# PART 21: RESPONSE MACROS FOR CONSISTENT APIs

## What Are Response Macros?

Response macros allow you to define custom response methods that can be reused throughout your application, ensuring consistent API response formats.

## Creating Response Macros

```php
// In AppServiceProvider::boot()
use Illuminate\Support\Facades\Response;

public function boot(): void
{
    Response::macro('success', function ($data = null, string $message = 'Success', int $status = 200) {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    });

    Response::macro('error', function (string $message = 'Error', int $status = 400, $errors = null) {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $status);
    });

    Response::macro('created', function ($data = null, string $message = 'Resource created successfully') {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], 201);
    });

    Response::macro('noContent', function () {
        return response()->json(null, 204);
    });

    Response::macro('paginated', function ($paginator, string $message = 'Success') {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
            'links' => [
                'first' => $paginator->url(1),
                'last' => $paginator->url($paginator->lastPage()),
                'prev' => $paginator->previousPageUrl(),
                'next' => $paginator->nextPageUrl(),
            ],
        ]);
    });
}
```

## Using Response Macros in Controllers

```php
class InvoiceController extends Controller
{
    public function index()
    {
        $invoices = Invoice::paginate(20);
        return response()->paginated($invoices, 'Facturen opgehaald');
    }

    public function store(StoreInvoiceRequest $request)
    {
        $invoice = Invoice::create($request->validated());
        return response()->created(new InvoiceResource($invoice), 'Factuur aangemaakt');
    }

    public function show(Invoice $invoice)
    {
        return response()->success(new InvoiceResource($invoice));
    }

    public function update(UpdateInvoiceRequest $request, Invoice $invoice)
    {
        $invoice->update($request->validated());
        return response()->success(new InvoiceResource($invoice), 'Factuur bijgewerkt');
    }

    public function destroy(Invoice $invoice)
    {
        $invoice->delete();
        return response()->noContent();
    }
}
```

## Error Response Macros

```php
Response::macro('notFound', function (string $message = 'Resource not found') {
    return response()->error($message, 404);
});

Response::macro('unauthorized', function (string $message = 'Unauthorized') {
    return response()->error($message, 401);
});

Response::macro('forbidden', function (string $message = 'Forbidden') {
    return response()->error($message, 403);
});

Response::macro('validationError', function ($errors) {
    return response()->json([
        'success' => false,
        'message' => 'Validatie mislukt',
        'errors' => $errors,
    ], 422);
});
```

## Using with Exception Handler

```php
// In app/Exceptions/Handler.php
public function render($request, Throwable $e)
{
    if ($request->expectsJson()) {
        if ($e instanceof ModelNotFoundException) {
            return response()->notFound();
        }

        if ($e instanceof AuthorizationException) {
            return response()->forbidden($e->getMessage());
        }

        if ($e instanceof ValidationException) {
            return response()->validationError($e->errors());
        }
    }

    return parent::render($request, $e);
}
```

---

# PART 22: DATABASE-BACKED NOTIFICATIONS

## Creating a Database Notification

```php
php artisan make:notification InvoiceCreatedNotification
```

```php
class InvoiceCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Invoice $invoice
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail']; // Multiple channels
    }

    // Database notification data
    public function toDatabase(object $notifiable): array
    {
        return [
            'invoice_id' => $this->invoice->id,
            'invoice_number' => $this->invoice->number,
            'client_name' => $this->invoice->client->name,
            'amount' => $this->invoice->total,
            'message' => "Nieuwe factuur {$this->invoice->number} aangemaakt",
            'url' => route('invoices.show', $this->invoice),
        ];
    }

    // Email notification
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Factuur {$this->invoice->number}")
            ->line("Er is een nieuwe factuur aangemaakt.")
            ->action('Bekijk Factuur', route('invoices.show', $this->invoice));
    }
}
```

## Database Setup

```bash
php artisan notifications:table
php artisan migrate
```

## Sending Notifications

```php
// Single user
$user->notify(new InvoiceCreatedNotification($invoice));

// Multiple users
Notification::send($users, new InvoiceCreatedNotification($invoice));

// Queue the notification
$user->notify((new InvoiceCreatedNotification($invoice))->delay(now()->addMinutes(5)));
```

## Retrieving Notifications

```php
// In User model
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;
}

// Get all notifications
$notifications = $user->notifications;

// Get unread notifications
$unread = $user->unreadNotifications;

// Get read notifications
$read = $user->readNotifications;

// Access notification data
foreach ($user->unreadNotifications as $notification) {
    echo $notification->data['message'];
    echo $notification->created_at;
}
```

## Marking Notifications as Read

```php
// Mark single notification
$notification->markAsRead();

// Mark all as read
$user->unreadNotifications->markAsRead();

// Mark specific notification
$user->notifications()->where('id', $notificationId)->first()->markAsRead();
```

## API for Notifications

```php
class NotificationController extends Controller
{
    public function index(Request $request)
    {
        return response()->paginated(
            $request->user()->notifications()->paginate(20)
        );
    }

    public function unread(Request $request)
    {
        return response()->success([
            'count' => $request->user()->unreadNotifications()->count(),
            'notifications' => $request->user()->unreadNotifications()->take(5)->get(),
        ]);
    }

    public function markAsRead(Request $request, string $id)
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();
        return response()->success(null, 'Notificatie gemarkeerd als gelezen');
    }

    public function markAllAsRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();
        return response()->success(null, 'Alle notificaties gemarkeerd als gelezen');
    }
}
```

## Custom Notification Channels

```php
public function via(object $notifiable): array
{
    $channels = ['database'];

    if ($notifiable->email_notifications) {
        $channels[] = 'mail';
    }

    if ($notifiable->slack_webhook) {
        $channels[] = 'slack';
    }

    return $channels;
}
```

---

# PART 23: LARAVEL TIPS FROM THE COMMUNITY

## Eloquent & Database Tips

### 1. Clone Queries for Reuse
```php
$query = User::where('active', true);
$admins = $query->clone()->where('role', 'admin')->get();
$users = $query->clone()->where('role', 'user')->get();
```

### 2. Get Single Column Value Efficiently
```php
// Instead of: User::find(1)->name
$name = User::where('id', 1)->value('name');
```

### 3. Check If Model Was Recently Created
```php
$user = User::firstOrCreate(['email' => $email]);
if ($user->wasRecentlyCreated) {
    // Send welcome email
}
```

### 4. WhereColumn for Same-Table Comparisons
```php
// Find users updated after creation
Task::whereColumn('updated_at', '>', 'created_at')->get();
```

### 5. Order By Relationship Aggregate
```php
Book::withAvg('ratings as average_rating', 'rating')
    ->orderByDesc('average_rating')
    ->get();
```

### 6. Update Or Create With Different Values
```php
Flight::updateOrCreate(
    ['departure' => 'Oakland', 'destination' => 'San Diego'], // Find by
    ['price' => 99, 'discounted' => true] // Update or create with
);
```

### 7. Find Multiple Records
```php
$users = User::find([1, 2, 3], ['id', 'name']); // With specific columns
```

## Routing Tips

### 8. Wildcard Subdomains
```php
Route::domain('{account}.myapp.com')->group(function () {
    Route::get('/dashboard', function ($account) {
        return "Dashboard for $account";
    });
});
```

### 9. Rate Limiting by User Type
```php
Route::middleware('throttle:10|60,1')->group(function () {
    // Guests: 10 requests/minute
    // Authenticated: 60 requests/minute
});
```

### 10. Route Model Binding with Custom Key
```php
// In model
public function getRouteKeyName(): string
{
    return 'slug';
}

// Or inline
Route::get('/posts/{post:slug}', function (Post $post) { });
```

### 11. Signed URLs for Secure Links
```php
$url = URL::signedRoute('unsubscribe', ['user' => $user]);
$url = URL::temporarySignedRoute('download', now()->addHour(), ['file' => $file]);
```

### 12. Arrow Functions in Routes
```php
Route::get('/users', fn() => User::all());
```

### 13. Controller Groups
```php
Route::controller(InvoiceController::class)->group(function () {
    Route::get('/invoices', 'index');
    Route::post('/invoices', 'store');
    Route::get('/invoices/{invoice}', 'show');
});
```

## Validation Tips

### 14. Access Route-Bound Models in Form Request
```php
public function rules(): array
{
    return [
        'name' => ['required', Rule::unique('users')->ignore($this->user)],
    ];
}
```

### 15. Exclude Fields from Validated Output
```php
'terms_accepted' => 'required|accepted|exclude', // Won't be in validated()
```

### 16. Array Position in Error Messages
```php
'items.*.name.required' => 'Item #:position requires a name'
```

### 17. Password Strength Defaults
```php
// In AppServiceProvider
Password::defaults(function () {
    return Password::min(8)->mixedCase()->numbers()->symbols();
});
```

## Relationship Tips

### 18. Shorter WhereHas Syntax
```php
// Instead of:
User::whereHas('posts', fn($q) => $q->where('published', true))->get();

// Use (Laravel 8.57+):
User::whereRelation('posts', 'published', true)->get();
```

### 19. Latest/Oldest Of Relationship
```php
public function latestOrder(): HasOne
{
    return $this->hasOne(Order::class)->latestOfMany();
}

public function oldestOrder(): HasOne
{
    return $this->hasOne(Order::class)->oldestOfMany();
}
```

### 20. Touch Parent Timestamps
```php
class Comment extends Model
{
    protected $touches = ['post']; // Updates post's updated_at when comment changes
}
```

### 21. Default Model for Empty Relationships
```php
public function user(): BelongsTo
{
    return $this->belongsTo(User::class)->withDefault([
        'name' => 'Guest User',
    ]);
}
```

## Performance Tips

### 22. Use Cursor for Large Datasets
```php
foreach (User::cursor() as $user) {
    // Processes one at a time, low memory
}
```

### 23. Select Only Needed Columns
```php
$users = User::select('id', 'name', 'email')->get();
```

### 24. Skip Timestamps When Not Needed
```php
$model->withoutTimestamps(fn() => $model->increment('views'));
```

### 25. Cache Config in Production
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

## Security Tips

### 26. Prevent Mass Assignment
```php
// In AppServiceProvider
Model::preventSilentlyDiscardingAttributes(!app()->isProduction());
```

### 27. Validate File Uploads Strictly
```php
'document' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
'image' => ['required', 'image', 'dimensions:max_width=4096,max_height=4096'],
```

### 28. Use Rate Limiting on Sensitive Endpoints
```php
RateLimiter::for('login', fn(Request $request) =>
    Limit::perMinute(5)->by($request->ip())
);
```

## Debugging Tips

### 29. Dump and Die Anywhere
```php
$users = User::where('active', true)->dd(); // Shows SQL and dies
$users = User::where('active', true)->dump()->get(); // Dumps SQL, continues
```

### 30. See Model Changes
```php
$user->fill($data);
$changes = $user->getDirty(); // Changed but not saved
$original = $user->getOriginal(); // Original values
```

---

# Resources

**Official Documentation:**
- [Laravel Docs](https://laravel.com/docs)
- [Laravel Testing](https://laravel.com/docs/testing)
- [Laravel Middleware](https://laravel.com/docs/middleware)
- [Laravel Queues](https://laravel.com/docs/queues)
- [Laravel Deployment](https://laravel.com/docs/deployment)
- [Laravel Authorization](https://laravel.com/docs/authorization)
- [Laravel Encryption](https://laravel.com/docs/encryption)
- [Laravel Notifications](https://laravel.com/docs/notifications)

**Packages:**
- [Packagist](https://packagist.org)
- [Spatie Packages](https://spatie.be/open-source/packages)
- [Laravel Package Registry](https://nova.laravel.com/)

**Community:**
- [Laracasts](https://laracasts.com)
- [Laravel News](https://laravel-news.com)
- [Laravel Daily](https://laraveldaily.com)
- [Laravel.io Forum](https://laravel.io)

**Dutch Resources:**
- [Belastingdienst BTW](https://www.belastingdienst.nl/btw)
- [KvK API](https://developers.kvk.nl/)
- [iDEAL Documentation](https://www.ideal.nl/en/developers/)

**Related Skills:**
- `dutch-bookkeeping-expert` - Dutch financial compliance
- `testing-expert` - Comprehensive testing strategies
- `database-mysql-expert` - Database optimization
- `security-expert` - Security best practices
- `deployment-checklist` - Production deployment

---

## Version History

### Version 2.0.0 (2025-12-17)
- Added comprehensive troubleshooting section (7 common problems)
- Added implementation checklists (pre/post)
- Added anti-patterns section (5 patterns)
- Added performance optimization guide
- Added security deep dive
- Added integration guides
- Added quick reference section
- Added configuration reference
- Enhanced with Skill Improver framework

### Version 1.0.0 (2025-12-17)
- Initial release
- Combined from laravel-test-suite, laravel-ecosystem, and laravel-middleware skills
- Basic testing, packages, and middleware coverage

---

*Enhanced with Skill Improver Framework - achieving 85+ quality score*

### Version 3.0.0 (2025-12-18)
- Added PART 15: Traits for Code Reusability (5 common trait patterns)
- Added PART 16: SOLID Principles in Laravel (comprehensive examples)
- Added PART 17: Custom Form Requests for Complex Validation
- Added PART 18: Policies for Authorization (Gates vs Policies)
- Added PART 19: Eager Loading - Solving N+1 Problems (5 solutions)
- Added PART 20: Laravel Encryption Features
- Added PART 21: Response Macros for Consistent APIs
- Added PART 22: Database-Backed Notifications
- Added PART 23: Laravel Tips from the Community (30 tips)
