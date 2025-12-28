---
name: laravel-ecosystem
description: Expert guidance on Laravel packages, tools, integrations, deployment, and modern development workflows
version: 1.0.2
tags: [laravel, php, backend, api, packages, deployment]
trigger_keywords: [sk-laravel-ecosystem, "laravel package", "eloquent model", "api endpoint", "laravel controller", "service class", "laravel deployment", "composer require", "artisan command", "laravel migration"]
---
# Laravel Ecosystem Expert

Use this skill when working with Laravel packages, tools, integrations, deployment, and the broader Laravel ecosystem including popular packages, best practices, and modern development workflows.

## When to Use This Skill

- Implementing new Laravel features
- Integrating third-party packages
- Setting up API authentication (Sanctum)
- Configuring queues and background jobs
- Deploying Laravel applications
- Optimizing Laravel performance
- Working with Inertia.js and Vue.js integration

## Official Laravel Packages

### 1. Laravel Sanctum (API Authentication)

**Purpose:** Simple token-based authentication for SPAs and mobile apps

**Setup:**
```bash
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

**Configuration:**
```php
// config/sanctum.php
'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
    '%s%s',
    'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1',
    env('APP_URL') ? ','.parse_url(env('APP_URL'), PHP_URL_HOST) : ''
))),

// User model
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
}
```

**Usage:**
```php
// Issue token on login
Route::post('/login', function (Request $request) {
    $credentials = $request->only('email', 'password');

    if (Auth::attempt($credentials)) {
        $user = Auth::user();
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user,
        ]);
    }

    return response()->json(['message' => 'Invalid credentials'], 401);
});

// Protect routes
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Revoke tokens
$request->user()->currentAccessToken()->delete(); // Current token
$request->user()->tokens()->delete(); // All tokens
```

### 2. Laravel Horizon (Queue Monitoring)

**Purpose:** Beautiful dashboard and code-driven configuration for Redis queues

**Setup:**
```bash
composer require laravel/horizon
php artisan horizon:install
php artisan migrate
```

**Configuration:**
```php
// config/horizon.php
'environments' => [
    'production' => [
        'supervisor-1' => [
            'connection' => 'redis',
            'queue' => ['default', 'emails', 'reports'],
            'balance' => 'auto',
            'processes' => 10,
            'tries' => 3,
            'timeout' => 60,
        ],
    ],
],
```

**Running:**
```bash
php artisan horizon          # Start Horizon
php artisan horizon:pause    # Pause workers
php artisan horizon:continue # Resume workers
php artisan horizon:terminate # Graceful shutdown
```

**Dashboard:** Access at `/horizon`

### 3. Laravel Telescope (Debugging Assistant)

**Purpose:** Elegant debug assistant providing insight into requests, exceptions, database queries, queued jobs, and more

**Setup:**
```bash
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```

**Configuration:**
```php
// config/telescope.php
'enabled' => env('TELESCOPE_ENABLED', true),

'watchers' => [
    Watchers\QueryWatcher::class => [
        'enabled' => env('TELESCOPE_QUERY_WATCHER', true),
        'slow' => 100, // Log queries slower than 100ms
    ],
    Watchers\RequestWatcher::class => env('TELESCOPE_REQUEST_WATCHER', true),
    Watchers\ExceptionWatcher::class => env('TELESCOPE_EXCEPTION_WATCHER', true),
],
```

**Dashboard:** Access at `/telescope`

### 4. Laravel Octane (High Performance)

**Purpose:** Supercharge application performance using Swoole or RoadRunner

**Setup:**
```bash
composer require laravel/octane
php artisan octane:install # Choose Swoole or RoadRunner
```

**Running:**
```bash
php artisan octane:start --workers=4 --task-workers=6 --watch
```

**Considerations:**
- Application state persists between requests
- Clear singleton instances appropriately
- Use Octane-aware code patterns

```php
// Octane-aware singleton clearing
Octane::tick('1m', function () {
    Cache::flush('temp');
});

// Per-request cleanup
Octane::table('users', [
    'name' => 'string:1000',
    'votes' => 'int',
]);
```

### 5. Laravel Socialite (OAuth Authentication)

**Purpose:** OAuth authentication with Facebook, Twitter, Google, GitHub, GitLab, and Bitbucket

**Setup:**
```bash
composer require laravel/socialite
```

**Configuration:**
```php
// config/services.php
'github' => [
    'client_id' => env('GITHUB_CLIENT_ID'),
    'client_secret' => env('GITHUB_CLIENT_SECRET'),
    'redirect' => env('GITHUB_REDIRECT_URL'),
],
```

**Usage:**
```php
use Laravel\Socialite\Facades\Socialite;

// Redirect to provider
Route::get('/auth/github', function () {
    return Socialite::driver('github')->redirect();
});

// Handle callback
Route::get('/auth/github/callback', function () {
    $githubUser = Socialite::driver('github')->user();

    $user = User::updateOrCreate([
        'github_id' => $githubUser->id,
    ], [
        'name' => $githubUser->name,
        'email' => $githubUser->email,
        'github_token' => $githubUser->token,
    ]);

    Auth::login($user);

    return redirect('/dashboard');
});
```

## Essential Third-Party Packages

### 1. Spatie Permission (Role & Permission Management)

**Purpose:** Associate users with roles and permissions

```bash
composer require spatie/laravel-permission
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan migrate
```

**Usage:**
```php
// Create roles and permissions
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

Permission::create(['name' => 'edit articles']);
Permission::create(['name' => 'delete articles']);

$role = Role::create(['name' => 'admin']);
$role->givePermissionTo('edit articles', 'delete articles');

// Assign to user
$user->assignRole('admin');
$user->givePermissionTo('edit articles');

// Check permissions
if ($user->can('edit articles')) {
    // User has permission
}

// Middleware
Route::group(['middleware' => ['role:admin']], function () {
    // Admin-only routes
});

Route::group(['middleware' => ['permission:edit articles']], function () {
    // Permission-specific routes
});
```

### 2. Laravel Debugbar (Development Tool)

**Purpose:** Displays debugging information for current request

```bash
composer require barryvdh/laravel-debugbar --dev
```

**Features:**
- Query count and execution time
- View rendering time
- Route information
- Session data
- Memory usage

### 3. Laravel IDE Helper (Code Completion)

**Purpose:** Generate helper files for IDE autocompletion

```bash
composer require --dev barryvdh/laravel-ide-helper
```

**Generate Helpers:**
```bash
php artisan ide-helper:generate     # Generate PHPDoc for Facades
php artisan ide-helper:models       # Generate PHPDoc for models
php artisan ide-helper:meta         # Generate PhpStorm meta file
```

### 4. Spatie Laravel Backup

**Purpose:** Backup database and files to various destinations

```bash
composer require spatie/laravel-backup
php artisan vendor:publish --provider="Spatie\Backup\BackupServiceProvider"
```

**Configuration:**
```php
// config/backup.php
'backup' => [
    'name' => env('APP_NAME', 'laravel-backup'),
    'source' => [
        'files' => [
            'include' => [
                base_path(),
            ],
            'exclude' => [
                base_path('vendor'),
                base_path('node_modules'),
            ],
        ],
        'databases' => ['mysql'],
    ],
    'destination' => [
        'disks' => ['s3', 'local'],
    ],
],
```

**Usage:**
```bash
php artisan backup:run          # Create backup
php artisan backup:clean        # Remove old backups
php artisan backup:list         # List all backups
```

### 5. Spatie Laravel Media Library

**Purpose:** Associate files with Eloquent models

```bash
composer require spatie/laravel-medialibrary
php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider"
php artisan migrate
```

**Usage:**
```php
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Product extends Model implements HasMedia
{
    use InteractsWithMedia;

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->singleFile(); // Only one file per collection

        $this->addMediaCollection('documents')
            ->acceptsMimeTypes(['application/pdf']);
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(368)
            ->height(232)
            ->sharpen(10);
    }
}

// Add media
$product->addMedia($request->file('image'))->toMediaCollection('images');

// Retrieve media
$mediaItems = $product->getMedia('images');
$url = $product->getFirstMediaUrl('images');
$thumbUrl = $product->getFirstMediaUrl('images', 'thumb');
```

### 6. Laravel Excel (Import/Export)

**Purpose:** Export and import Excel and CSV files

```bash
composer require maatwebsite/excel
```

**Export:**
```php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class UsersExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return User::all();
    }

    public function headings(): array
    {
        return ['ID', 'Name', 'Email', 'Created At'];
    }
}

// Download
return Excel::download(new UsersExport, 'users.xlsx');

// Store
Excel::store(new UsersExport, 'users.xlsx', 's3');
```

**Import:**
```php
namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToModel;

class UsersImport implements ToModel
{
    public function model(array $row)
    {
        return new User([
            'name' => $row[0],
            'email' => $row[1],
        ]);
    }
}

// Import
Excel::import(new UsersImport, 'users.xlsx');
```

### 7. Laravel Livewire (Full-Stack Framework)

**Purpose:** Build dynamic interfaces without leaving PHP

```bash
composer require livewire/livewire
```

**Component:**
```php
namespace App\Http\Livewire;

use Livewire\Component;

class Counter extends Component
{
    public $count = 0;

    public function increment()
    {
        $this->count++;
    }

    public function render()
    {
        return view('livewire.counter');
    }
}
```

**Blade View:**
```blade
<div>
    <h1>{{ $count }}</h1>
    <button wire:click="increment">+</button>
</div>
```

**Usage in Blade:**
```blade
@livewire('counter')
<!-- Or -->
<livewire:counter />
```

### 8. Laravel Filament (Admin Panel)

**Purpose:** Beautiful admin panel built on Livewire

```bash
composer require filament/filament
php artisan filament:install
```

**Create Resource:**
```bash
php artisan make:filament-resource User
```

**Resource Example:**
```php
namespace App\Filament\Resources;

use Filament\Resources\Resource;
use Filament\Resources\Form;
use Filament\Resources\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')->required(),
                TextInput::make('email')->email()->required(),
                DatePicker::make('created_at')->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->sortable()->searchable(),
                TextColumn::make('email')->sortable()->searchable(),
                TextColumn::make('created_at')->dateTime(),
            ])
            ->filters([
                Filter::make('verified')->query(fn ($query) => $query->whereNotNull('email_verified_at')),
            ]);
    }
}
```

## Development Tools

### 1. Laravel Vite (Asset Bundling)

**Default in Laravel 9.19+**

```bash
npm install
npm run dev   # Development with hot reload
npm run build # Production build
```

**Configuration:**
```js
// vite.config.js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
});
```

**Blade Usage:**
```blade
@vite(['resources/css/app.css', 'resources/js/app.js'])
```

### 2. Laravel Pint (Code Style)

**Built-in opinionated PHP code style fixer**

```bash
./vendor/bin/pint           # Fix all files
./vendor/bin/pint --test    # Check without fixing
./vendor/bin/pint app/Models # Fix specific directory
```

**Configuration:**
```json
// pint.json
{
    "preset": "laravel",
    "rules": {
        "simplified_null_return": true,
        "braces": false,
        "new_with_braces": true
    }
}
```

### 3. Laravel Sail (Docker Development)

**Official Docker development environment**

```bash
composer require laravel/sail --dev
php artisan sail:install
```

**Usage:**
```bash
./vendor/bin/sail up        # Start containers
./vendor/bin/sail artisan migrate
./vendor/bin/sail composer require package
./vendor/bin/sail npm install
./vendor/bin/sail test

# Create alias for convenience
alias sail='./vendor/bin/sail'
```

**Services Available:**
- MySQL, PostgreSQL, MariaDB, Redis
- Mailhog, MinIO, Selenium
- MeiliSearch, Soketi

### 4. Laravel Tinker (REPL)

**Interactive shell for Laravel**

```bash
php artisan tinker
```

**Usage:**
```php
// Execute queries
User::count()
User::where('email', 'LIKE', '%@gmail.com')->get()

// Test relationships
$user = User::first()
$user->posts

// Test services
$result = app(PaymentService::class)->charge($user, 100)

// Clear cache
Cache::flush()
```

## Testing Tools

### 1. Laravel Dusk (Browser Testing)

```bash
composer require --dev laravel/dusk
php artisan dusk:install
```

**Test Example:**
```php
namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class LoginTest extends DuskTestCase
{
    public function test_user_can_login()
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->visit('/login')
                    ->type('email', $user->email)
                    ->type('password', 'password')
                    ->press('Login')
                    ->assertPathIs('/dashboard')
                    ->assertSee($user->name);
        });
    }
}
```

### 2. Pest (Testing Framework)

**Modern testing framework with elegant syntax**

```bash
composer require pestphp/pest --dev --with-all-dependencies
php artisan pest:install
```

**Test Example:**
```php
use function Pest\Laravel\{get, post, actingAs};

it('displays the homepage', function () {
    get('/')->assertStatus(200);
});

it('requires authentication', function () {
    get('/dashboard')->assertRedirect('/login');
});

it('allows authenticated users', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->get('/dashboard')
        ->assertStatus(200);
});

test('user can create post', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->post('/posts', ['title' => 'Test Post', 'body' => 'Content'])
        ->assertRedirect('/posts')
        ->assertDatabaseHas('posts', ['title' => 'Test Post']);
});
```

## Deployment & DevOps

### 1. Laravel Forge (Server Management)

**Official server management and deployment platform**

- One-click server provisioning on AWS, DigitalOcean, Linode, etc.
- Automatic SSL certificates via Let's Encrypt
- Queue worker management
- Scheduled job management
- Database backups
- Zero-downtime deployments

### 2. Laravel Envoyer (Zero-Downtime Deployment)

**Deployment automation with zero downtime**

- Deploy to multiple servers
- Health checks before switching
- Rollback with one click
- Deployment hooks
- Slack/email notifications

### 3. Laravel Vapor (Serverless Deployment)

**Auto-scaling serverless deployment on AWS Lambda**

```bash
composer require laravel/vapor-cli --dev
php vendor/bin/vapor login
php vendor/bin/vapor init
```

**Configuration:**
```yaml
# vapor.yml
id: 12345
name: my-app
environments:
    production:
        memory: 1024
        cli-memory: 512
        runtime: 'php-8.2'
        build:
            - 'composer install --no-dev'
            - 'php artisan event:cache'
        deploy:
            - 'php artisan migrate --force'
```

## Package Development

### Creating a Laravel Package

**Structure:**
```
my-package/
├── src/
│   ├── MyPackageServiceProvider.php
│   ├── Facades/
│   └── ...
├── config/
│   └── my-package.php
├── routes/
│   └── web.php
├── resources/
│   ├── views/
│   └── lang/
├── tests/
└── composer.json
```

**Service Provider:**
```php
namespace Vendor\MyPackage;

use Illuminate\Support\ServiceProvider;

class MyPackageServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/my-package.php', 'my-package');
    }

    public function boot()
    {
        // Publish config
        $this->publishes([
            __DIR__.'/../config/my-package.php' => config_path('my-package.php'),
        ], 'config');

        // Load routes
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        // Load views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'my-package');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Publish views
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/my-package'),
        ], 'views');
    }
}
```

## Performance Optimization

### Caching Strategies

```bash
# Config caching (production)
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Clear caches
php artisan optimize:clear

# OPcache optimization
php artisan optimize
```

### Database Optimization

```php
// Eager loading to prevent N+1
$users = User::with('posts', 'comments')->get();

// Chunk large datasets
User::chunk(200, function ($users) {
    foreach ($users as $user) {
        // Process user
    }
});

// Lazy collections for memory efficiency
User::cursor()->each(function ($user) {
    // Process user with minimal memory
});

// Query optimization
DB::enableQueryLog();
// ... your queries
dd(DB::getQueryLog());
```

## Security Best Practices

### Essential Security Packages

**1. Laravel Sanctum** - API authentication
**2. Laravel Security Headers**
```bash
composer require bepsvpt/secure-headers
php artisan vendor:publish --tag=secure-headers
```

**3. Laravel Security Checker**
```bash
composer require enlightn/security-checker --dev
php artisan security:check
```

### Security Checklist

- ✅ Use HTTPS everywhere (SSL certificate)
- ✅ CSRF protection enabled (default in Laravel)
- ✅ SQL injection prevention (use query builder/Eloquent)
- ✅ XSS protection (Blade escapes by default)
- ✅ Mass assignment protection (use `$fillable` or `$guarded`)
- ✅ Rate limiting on API routes
- ✅ Input validation on all user input
- ✅ Password hashing (use `Hash` facade)
- ✅ Environment variables for secrets (.env)
- ✅ Regular dependency updates
- ✅ Two-factor authentication (Laravel Fortify)
- ✅ Security headers configured

## Dutch Bookkeeping Code Examples

### 1. Invoice with Dutch VAT Calculation

```php
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Role;

// Dutch invoice creation with BTW calculation
class DutchInvoiceService
{
    public function createInvoice(array $data)
    {
        $amount = $data['amount'];
        $vatRate = $data['vat_rate'] ?? 0.21; // 21% standard Dutch BTW

        $invoice = Invoice::create([
            'company_id' => $data['company_id'],
            'client_id' => $data['client_id'],
            'number' => $this->generateInvoiceNumber($data['company_id']),
            'amount_ex_vat' => $amount,
            'vat_rate' => $vatRate,
            'vat_amount' => round($amount * $vatRate, 2),
            'total_incl_vat' => round($amount * (1 + $vatRate), 2),
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
        ]);

        // Queue PDF generation
        GenerateInvoicePdf::dispatch($invoice);

        return $invoice;
    }

    private function generateInvoiceNumber($companyId): string
    {
        // Thread-safe invoice numbering
        return DB::transaction(function() use ($companyId) {
            $lastNumber = Invoice::where('company_id', $companyId)
                ->lockForUpdate()
                ->max('sequence_number') ?? 0;

            $newNumber = $lastNumber + 1;
            return sprintf('INV-%04d-%05d', date('Y'), $newNumber);
        });
    }
}
```

### 2. BTW Declaration Export with Laravel Excel

```php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class BtwDeclarationExport implements FromCollection, WithHeadings, WithMapping
{
    protected $period;

    public function __construct($startDate, $endDate)
    {
        $this->period = compact('startDate', 'endDate');
    }

    public function collection()
    {
        return Invoice::whereBetween('invoice_date', [
            $this->period['startDate'],
            $this->period['endDate']
        ])->get();
    }

    public function headings(): array
    {
        return [
            'Factuurnummer',
            'Datum',
            'Bedrag ex. BTW',
            'BTW Tarief',
            'BTW Bedrag',
            'Totaal incl. BTW',
        ];
    }

    public function map($invoice): array
    {
        return [
            $invoice->number,
            $invoice->invoice_date->format('d-m-Y'),
            '€ ' . number_format($invoice->amount_ex_vat, 2, ',', '.'),
            ($invoice->vat_rate * 100) . '%',
            '€ ' . number_format($invoice->vat_amount, 2, ',', '.'),
            '€ ' . number_format($invoice->total_incl_vat, 2, ',', '.'),
        ];
    }
}

// Usage in controller
public function exportBtwDeclaration(Request $request)
{
    return Excel::download(
        new BtwDeclarationExport($request->start_date, $request->end_date),
        'btw-aangifte-' . now()->format('Y-m-d') . '.xlsx'
    );
}
```

### 3. Multi-Tenant Backup with Spatie Backup

```php
// config/backup.php - Dutch bookkeeping specific
return [
    'backup' => [
        'name' => env('APP_NAME', 'boekhouder'),
        'source' => [
            'files' => [
                'include' => [
                    storage_path('app/invoices'),
                    storage_path('app/receipts'),
                    storage_path('app/contracts'),
                ],
                'exclude' => [
                    storage_path('app/cache'),
                    storage_path('app/temp'),
                ],
            ],
            'databases' => ['mysql'],
        ],
        'destination' => [
            'disks' => ['s3', 'local'],
        ],
        'notifications' => [
            'mail' => [
                'to' => env('BACKUP_NOTIFICATION_EMAIL'),
            ],
            'slack' => [
                'webhook_url' => env('BACKUP_SLACK_WEBHOOK'),
            ],
        ],
    ],

    // Dutch law requires 7 years of financial data retention
    'cleanup' => [
        'strategy' => \Spatie\Backup\Tasks\Cleanup\Strategies\DefaultStrategy::class,
        'defaultStrategy' => [
            'keepAllBackupsForDays' => 7,
            'keepDailyBackupsForDays' => 30,
            'keepWeeklyBackupsForWeeks' => 52,
            'keepMonthlyBackupsForMonths' => 84, // 7 years
            'keepYearlyBackupsForYears' => 7,
            'deleteOldestBackupsWhenUsingMoreMegabytesThan' => 50000,
        ],
    ],
];

// Custom command for per-company backup
class BackupCompanyData extends Command
{
    protected $signature = 'backup:company {company_id}';

    public function handle()
    {
        $company = Company::findOrFail($this->argument('company_id'));

        // Export all company data
        $data = [
            'company' => $company,
            'invoices' => $company->invoices,
            'expenses' => $company->expenses,
            'clients' => $company->clients,
            'vat_declarations' => $company->vatDeclarations,
        ];

        Storage::disk('s3')->put(
            "company-backups/{$company->id}/" . now()->format('Y-m-d') . '.json',
            json_encode($data, JSON_PRETTY_PRINT)
        );

        $this->info("Backup created for {$company->name}");
    }
}
```

### 4. Laravel Horizon for BTW Processing Queue

```php
// config/horizon.php - Dutch bookkeeping queues
'environments' => [
    'production' => [
        'supervisor-invoices' => [
            'connection' => 'redis',
            'queue' => ['invoices', 'invoice-emails'],
            'balance' => 'auto',
            'processes' => 3,
            'tries' => 3,
        ],
        'supervisor-btw' => [
            'connection' => 'redis',
            'queue' => ['btw-calculations', 'digipoort'],
            'balance' => 'simple',
            'processes' => 2,
            'tries' => 5,
            'timeout' => 300,
        ],
        'supervisor-reports' => [
            'connection' => 'redis',
            'queue' => ['reports', 'exports'],
            'balance' => 'auto',
            'processes' => 2,
            'tries' => 2,
        ],
    ],
],
```

## Troubleshooting Guide

### Problem 1: Package Conflict Between Spatie Packages

**Symptoms:**
```bash
Problem 1
  - spatie/laravel-permission[5.0] requires illuminate/support ^9.0
  - spatie/laravel-backup[8.0] requires illuminate/support ^10.0
```

**Solution:**
```bash
# Check all Spatie package versions for compatibility
composer show spatie/*

# Update to compatible versions
composer require spatie/laravel-permission:^5.11
composer require spatie/laravel-backup:^8.3
composer require spatie/laravel-medialibrary:^10.0

# If still conflicts, use version constraints
composer require "spatie/laravel-permission:^5.0" --with-all-dependencies
```

### Problem 2: Sanctum Token Not Working After Deployment

**Symptoms:**
```
401 Unauthenticated on API calls
```

**Solution:**
```php
// 1. Check .env configuration
SANCTUM_STATEFUL_DOMAINS=localhost,localhost:3000,yourdomain.com,www.yourdomain.com
SESSION_DRIVER=database  // Not 'cookie' for API
SESSION_DOMAIN=.yourdomain.com  // Note the leading dot

// 2. Ensure CORS is configured
// config/cors.php
'paths' => ['api/*', 'sanctum/csrf-cookie'],
'supports_credentials' => true,

// 3. Clear config cache after changes
php artisan config:clear
php artisan cache:clear

// 4. Verify middleware in Kernel.php
protected $middlewareGroups = [
    'api' => [
        \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        'throttle:api',
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
    ],
];
```

### Problem 3: Telescope Filling Database

**Symptoms:**
```
Database size growing rapidly
telescope_entries table > 10GB
```

**Solution:**
```bash
# Prune old entries regularly
php artisan telescope:prune --hours=48

# Add to app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    $schedule->command('telescope:prune --hours=48')->daily();
}

# Exclude common requests from being recorded
// config/telescope.php
'ignore_paths' => [
    'nova-api*',
    'pulse*',
    'livewire*',
],

'ignore_commands' => [
    'schedule:run',
    'queue:work',
],

// Disable in production if not needed
// .env
TELESCOPE_ENABLED=false
```

### Problem 4: Laravel Excel Memory Exhaustion

**Symptoms:**
```
Fatal error: Allowed memory size of 134217728 bytes exhausted
```

**Solution:**
```php
// Use chunking for large exports
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class LargeInvoiceExport implements FromQuery, WithChunkReading
{
    public function query()
    {
        return Invoice::query();
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}

// Or use queue for large exports
Excel::queue(new LargeInvoiceExport, 'invoices.xlsx')->chain([
    new NotifyUserOfCompletedExport(request()->user()),
]);

// Increase memory limit for specific job
ini_set('memory_limit', '512M');
```

### Problem 5: Livewire State Not Persisting

**Symptoms:**
```
Component resets after validation error
Form data disappears
```

**Solution:**
```php
// Use wire:model.defer for better performance
<input type="text" wire:model.defer="amount">

// Add public properties with initial values
class InvoiceForm extends Component
{
    public $amount = '';
    public $vatRate = 21;

    protected function rules()
    {
        return [
            'amount' => 'required|numeric|min:0',
            'vatRate' => 'required|in:0,9,21',
        ];
    }

    // Use $this->validateOnly() for real-time validation
    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }
}

// For complex forms, use wire:key
<div wire:key="invoice-{{ $invoice->id }}">
```

## Best Practices

### 1. Package Selection

**DO:**
- ✅ Choose Spatie packages for Dutch-specific needs (they're Dutch!)
- ✅ Verify package is actively maintained (check last commit date)
- ✅ Check Laravel version compatibility
- ✅ Review package stars and downloads
- ✅ Read documentation before installing
- ✅ Use semantic versioning constraints (`^` or `~`)
- ✅ Test packages in development before production

**DON'T:**
- ❌ Install packages without checking compatibility
- ❌ Use abandoned packages (> 1 year no updates)
- ❌ Install packages without reading reviews
- ❌ Lock to exact versions (`1.2.3`) unless necessary

### 2. Performance Optimization

**DO:**
- ✅ Use Redis for cache and sessions in production
- ✅ Enable OPcache in production
- ✅ Cache routes, config, and views
- ✅ Use eager loading for relationships
- ✅ Queue heavy operations (emails, exports)
- ✅ Optimize database queries with indexes
- ✅ Use Laravel Octane for high-traffic apps

### 3. Security

**DO:**
- ✅ Keep all packages updated regularly
- ✅ Run `composer audit` to check vulnerabilities
- ✅ Use environment variables for secrets
- ✅ Enable HTTPS everywhere
- ✅ Configure CORS properly
- ✅ Use rate limiting on API routes
- ✅ Validate all user input

### 4. Code Organization

**DO:**
- ✅ Follow Laravel folder structure
- ✅ Use service classes for business logic
- ✅ Keep controllers thin
- ✅ Use form requests for validation
- ✅ Create custom Blade components
- ✅ Use repositories for complex queries

### 5. Testing

**DO:**
- ✅ Write feature tests for critical flows
- ✅ Test API endpoints thoroughly
- ✅ Mock external services
- ✅ Use factories for test data
- ✅ Run tests before deploying
- ✅ Achieve >80% code coverage

### 6. Deployment

**DO:**
- ✅ Use Laravel Forge or Envoyer for zero-downtime
- ✅ Run migrations in maintenance mode
- ✅ Clear all caches after deployment
- ✅ Use queue workers with supervisord
- ✅ Monitor application with Telescope/Horizon
- ✅ Set up automatic backups

### 7. Monitoring

**DO:**
- ✅ Use Laravel Horizon for queue monitoring
- ✅ Set up error tracking (Sentry, Flare)
- ✅ Monitor performance (New Relic, Scout APM)
- ✅ Track user activity with analytics
- ✅ Set up uptime monitoring
- ✅ Review logs regularly

## Anti-Patterns to Avoid

### 1. Package Overload

**❌ BAD:**
```php
// Installing 50+ packages for simple features
composer require package1 package2 package3 ... package50
```

**✅ GOOD:**
```php
// Use built-in Laravel features when possible
// Only install packages that provide significant value
// Review dependencies before adding new packages
```

### 2. Not Using Package Service Providers

**❌ BAD:**
```php
// Manually including package classes everywhere
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserController
{
    public function assignRole()
    {
        $role = Role::create(['name' => 'admin']);
        // ... everywhere in code
    }
}
```

**✅ GOOD:**
```php
// Let service provider auto-discover
// Use facades when available
// Configure once in config file
```

### 3. Ignoring Package Documentation

**❌ BAD:**
```php
// Trying to use package without reading docs
// Copy-pasting from Stack Overflow
// Not understanding what package does
```

**✅ GOOD:**
```php
// Read official documentation first
// Understand package architecture
// Follow recommended usage patterns
// Check changelog for updates
```

### 4. Mixing Package Versions

**❌ BAD:**
```json
{
    "require": {
        "spatie/laravel-permission": "^5.0",  // Laravel 9
        "spatie/laravel-backup": "^8.0",      // Laravel 10
        "laravel/framework": "^9.0"           // Conflict!
    }
}
```

**✅ GOOD:**
```json
{
    "require": {
        "laravel/framework": "^10.0",
        "spatie/laravel-permission": "^5.11",
        "spatie/laravel-backup": "^8.3"
    }
}
```

### 5. Not Configuring Packages

**❌ BAD:**
```php
// Using default configuration in production
// Not publishing config files
// Hardcoding package settings in code
```

**✅ GOOD:**
```bash
# Publish and configure properly
php artisan vendor:publish --provider="Spatie\Backup\BackupServiceProvider"

# Customize for your needs
# Use environment variables
# Test configuration changes
```

### 6. Ignoring Queue Configuration

**❌ BAD:**
```php
// Running heavy tasks synchronously
Mail::to($user)->send(new InvoiceMail($invoice));

// Default queue driver in production
QUEUE_CONNECTION=sync  // Bad for production!
```

**✅ GOOD:**
```php
// Queue everything that can wait
Mail::to($user)->queue(new InvoiceMail($invoice));

// Use proper queue driver
QUEUE_CONNECTION=redis  // Good for production
```

### 7. Not Monitoring Package Performance

**❌ BAD:**
```php
// Installing packages without monitoring impact
// Not checking memory usage
// Ignoring slow queries from packages
```

**✅ GOOD:**
```php
// Use Telescope to monitor package performance
// Profile slow operations
// Optimize package usage
// Consider lighter alternatives if needed
```

## Integration Checklist

### Before Installing a Package

- [ ] Check Laravel version compatibility
- [ ] Review package documentation
- [ ] Check last update date (< 6 months is ideal)
- [ ] Review open issues on GitHub
- [ ] Check download statistics
- [ ] Verify license compatibility
- [ ] Test in development environment first

### After Installing a Package

- [ ] Publish configuration files
- [ ] Review and customize config
- [ ] Add to `.gitignore` if needed
- [ ] Document package usage in README
- [ ] Test package functionality
- [ ] Write tests for package integration
- [ ] Update deployment scripts if needed

### Package Maintenance

- [ ] Run `composer outdated` monthly
- [ ] Review package changelogs before updating
- [ ] Test updates in staging first
- [ ] Monitor for security vulnerabilities
- [ ] Remove unused packages
- [ ] Keep documentation updated
- [ ] Review package performance regularly

### Dutch Bookkeeping Specific

- [ ] Verify Dutch VAT calculation support
- [ ] Check Digipoort compatibility (if applicable)
- [ ] Ensure IBAN validation works
- [ ] Verify Dutch date formats (dd-mm-yyyy)
- [ ] Check Euro currency formatting (€ 1.234,56)
- [ ] Test with Dutch language locale
- [ ] Verify GDPR compliance features

## When to Use This Skill

- Selecting appropriate Laravel packages for features
- Setting up Laravel development environment
- Implementing authentication and authorization
- Configuring deployment pipelines
- Optimizing Laravel application performance
- Building admin panels and dashboards
- Setting up testing frameworks
- Implementing file uploads and media management
- Creating and publishing Laravel packages
- Troubleshooting package conflicts or issues

## Key Ecosystem Resources

**Official Resources:**
- Laravel News (blog and newsletter)
- Laracasts (video tutorials)
- Laravel Bootcamp (official tutorial)
- Laravel Daily (tips and tutorials)

**Package Discovery:**
- Packagist (packagist.org)
- Laravel Packages (laravelpackages.com)
- Spatie (spatie.be/open-source/packages)

**Community:**
- Laravel Forums
- Laracasts Forum
- Laravel Discord
- Reddit r/laravel

## Related Skills

- **backend-api**: Building APIs with Laravel
- **testing-expert**: Testing package integrations
- **security-expert**: Securing third-party packages
- **database-expert**: Database packages and migrations

---

## Package Selection Checklist

### Pre-Installation Assessment
- [ ] Package actively maintained (updated within 6 months)
- [ ] Compatible with current Laravel version
- [ ] Sufficient GitHub stars/downloads (>1000 for critical packages)
- [ ] Good documentation available
- [ ] Active community support
- [ ] Security audit passed (composer audit)
- [ ] License compatible with project
- [ ] Performance benchmarks acceptable
- [ ] Tests included and passing
- [ ] No known security vulnerabilities

### Package Evaluation Criteria

```php
/**
 * Package Evaluation Matrix for Bookkeeping App
 */
class PackageEvaluator
{
    public function evaluate(string $packageName): array
    {
        return [
            'maintenance' => $this->checkMaintenance($packageName),
            'security' => $this->checkSecurity($packageName),
            'performance' => $this->checkPerformance($packageName),
            'compatibility' => $this->checkCompatibility($packageName),
            'community' => $this->checkCommunity($packageName),
        ];
    }

    private function checkMaintenance(string $package): array
    {
        // Check last commit, open issues, response time
        return [
            'last_commit' => '< 6 months',
            'open_issues' => '< 50',
            'maintainer_responsive' => true,
        ];
    }

    private function checkSecurity(string $package): array
    {
        // Run: composer audit
        // Check: https://security.laravel.com/
        return [
            'vulnerabilities' => 0,
            'security_policy' => true,
            'signed_commits' => true,
        ];
    }
}
```

---

## Advanced Package Integration Patterns

### 1. Invoice PDF Generation with DomPDF

**Complete Integration**:
```php
<?php

namespace App\Services;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class InvoicePdfService
{
    /**
     * Generate PDF from invoice
     */
    public function generate(Invoice $invoice): string
    {
        $pdf = Pdf::loadView('pdfs.invoice', [
            'invoice' => $invoice,
            'company' => $invoice->company,
            'lines' => $invoice->lines,
        ]);

        // Configure for Dutch invoices
        $pdf->setPaper('a4', 'portrait');
        $pdf->setOption('defaultFont', 'DejaVu Sans');
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', true);

        // Generate filename
        $filename = sprintf(
            'invoices/%s/%s.pdf',
            $invoice->company_id,
            $invoice->invoice_number
        );

        // Store PDF
        Storage::put($filename, $pdf->output());

        return $filename;
    }
}
```

---

## Package Security Best Practices

### 1. Automated Security Auditing

**Composer Audit Integration**:
```bash
# Add to CI/CD pipeline
composer audit

# Configure in composer.json
{
    "scripts": {
        "security-check": [
            "@php artisan security:check",
            "composer audit"
        ],
        "pre-commit": [
            "@security-check",
            "@test"
        ]
    }
}
```

### 2. Dependency Scanning with Snyk

**GitHub Action for Snyk**:
```yaml
name: Snyk Security Scan

on: [push, pull_request]

jobs:
  security:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3

      - name: Run Snyk to check for vulnerabilities
        uses: snyk/actions/php@master
        env:
          SNYK_TOKEN: ${{ secrets.SNYK_TOKEN }}
        with:
          args: --severity-threshold=high
```

---

## Performance Optimization for Packages

### 1. Package Auto-Discovery Optimization

**Optimize composer.json**:
```json
{
    "extra": {
        "laravel": {
            "dont-discover": [
                "laravel/telescope",
                "barryvdh/laravel-debugbar"
            ]
        }
    },
    "config": {
        "optimize-autoloader": true,
        "preferred-install": "dist",
        "sort-packages": true,
        "cache-files-ttl": 15552000
    }
}
```

### 2. Lazy Loading Service Providers

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class LazyPackageServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Only load heavy packages when needed
        $this->app->singleton('pdf-generator', function ($app) {
            return new \Barryvdh\DomPDF\PDF();
        });
    }

    public function boot(): void
    {
        // Only in development
        if ($this->app->environment('local')) {
            $this->app->register(\Barryvdh\Debugbar\ServiceProvider::class);
        }
    }
}
```

---

## Troubleshooting Package Issues

### Problem 1: Package Conflicts After Update

**Symptoms**: Composer update fails with dependency conflicts

**Solution**:
```bash
# 1. Check what's blocking the update
composer why-not laravel/framework 10.0

# 2. Update dependencies with constraints
composer update --with-all-dependencies

# 3. Review and fix conflicts manually
composer show -t | grep conflicting-package
```

### Problem 2: Service Provider Not Loading

**Symptoms**: Package features not available after installation

**Solution**:
```bash
# 1. Clear caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear

# 2. Rebuild discovery cache
composer dump-autoload
php artisan package:discover

# 3. Publish configuration
php artisan vendor:publish --provider="Vendor\\Package\\ServiceProvider"
```

### Problem 3: Memory Exhaustion with Large Exports

**Symptoms**: Excel exports fail with out of memory errors

**Solution**:
```php
<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class LargeInvoiceExport implements FromQuery, WithChunkReading
{
    public function query()
    {
        return Invoice::query()
            ->select(['id', 'invoice_number', 'total', 'status']); // Only needed columns
    }

    public function chunkSize(): int
    {
        return 1000; // Process in chunks
    }
}
```

---

## Package Maintenance Workflow

### Monthly Package Audit

```bash
#!/bin/bash
# scripts/audit-packages.sh

echo "=== Package Security Audit ==="
composer audit

echo "\n=== Outdated Packages ==="
composer outdated --direct

echo "\n=== License Compatibility ==="
composer licenses
```

### Pre-Update Checklist
- [ ] Review changelog for breaking changes
- [ ] Check Laravel compatibility matrix
- [ ] Backup database
- [ ] Run full test suite
- [ ] Update on staging first
- [ ] Monitor error logs post-update

### Post-Update Checklist
- [ ] Update lock file committed to git
- [ ] Publish new config files if needed
- [ ] Clear all caches
- [ ] Rebuild assets
- [ ] Update documentation

---

## Dutch Bookkeeping-Specific Package Integrations

### 1. iDEAL Payment Integration with Mollie

```php
<?php

namespace App\Services;

use App\Models\Invoice;
use Mollie\Laravel\Facades\Mollie;

class MolliePaymentService
{
    public function createPayment(Invoice $invoice): string
    {
        $payment = Mollie::api()->payments->create([
            'amount' => [
                'currency' => 'EUR',
                'value' => number_format($invoice->total, 2, '.', ''),
            ],
            'description' => "Factuur {$invoice->invoice_number}",
            'redirectUrl' => route('invoices.payment.return', $invoice),
            'webhookUrl' => route('webhooks.mollie'),
            'metadata' => [
                'invoice_id' => $invoice->id,
            ],
            'method' => 'ideal',
        ]);

        return $payment->getCheckoutUrl();
    }
}
```

### 2. KVK Number Validation

```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class KvkValidationService
{
    protected $apiKey;
    protected $baseUrl = 'https://api.kvk.nl/api/v1';

    public function validateKvkNumber(string $kvkNumber): ?array
    {
        $response = Http::withHeaders([
            'apikey' => $this->apiKey,
        ])->get("{$this->baseUrl}/zoeken", [
            'kvkNummer' => $kvkNumber,
        ]);

        if ($response->successful() && !empty($response->json('resultaten'))) {
            $company = $response->json('resultaten')[0];

            return [
                'naam' => $company['naam'] ?? null,
                'kvk_nummer' => $company['kvkNummer'] ?? null,
                'actief' => $company['actief'] ?? false,
            ];
        }

        return null;
    }
}
```

---

**Version 2.0.0** - Enhanced with package selection checklist, advanced integrations, security best practices, performance optimization, troubleshooting guides, and Dutch-specific integrations
