# Installation Wizard Specification

A one-time setup wizard that guides users through the initial configuration of OsintWeb.

## Overview

The installation wizard runs automatically when the application is first accessed and the system detects it hasn't been configured yet. It provides a step-by-step interface to configure all essential settings.

---

## 1. Wizard Detection

### How It Works

```php
// Middleware: CheckInstallation.php
class CheckInstallation
{
    public function handle(Request $request, Closure $next)
    {
        // Skip if already installed
        if (file_exists(storage_path('installed'))) {
            return $next($request);
        }

        // Skip if already on install routes
        if ($request->is('install/*')) {
            return $next($request);
        }

        // Redirect to installation wizard
        return redirect()->route('install.welcome');
    }
}
```

### Installation Lock File

After successful installation, create a lock file:
```
storage/installed
```

Contents:
```json
{
    "installed_at": "2025-01-15T10:30:00Z",
    "version": "1.0.0",
    "installer_ip": "192.168.1.100",
    "checksum": "sha256_of_config"
}
```

---

## 2. Wizard Steps

### Step 1: Welcome & Requirements Check

**URL:** `/install/welcome`

**Display:**
```
┌─────────────────────────────────────────────────────────────┐
│                    Welcome to OsintWeb                       │
│                                                             │
│  Military Conflict Tracking Platform                        │
│  Version 1.0.0                                              │
│                                                             │
│  This wizard will guide you through the installation        │
│  process. Please ensure you have:                           │
│                                                             │
│  ✓ MySQL 8.0+ database credentials                         │
│  ✓ SMTP server details (optional)                          │
│  ✓ Admin account information                               │
│                                                             │
│  [Start Installation →]                                     │
└─────────────────────────────────────────────────────────────┘
```

**Requirements Check:**

| Requirement | Minimum | Check |
|-------------|---------|-------|
| PHP Version | 8.2+ | `phpversion()` |
| MySQL Extension | Enabled | `extension_loaded('pdo_mysql')` |
| mbstring | Enabled | `extension_loaded('mbstring')` |
| xml | Enabled | `extension_loaded('xml')` |
| curl | Enabled | `extension_loaded('curl')` |
| zip | Enabled | `extension_loaded('zip')` |
| bcmath | Enabled | `extension_loaded('bcmath')` |
| gd/imagick | Enabled | `extension_loaded('gd')` |
| storage/ writable | Yes | `is_writable(storage_path())` |
| .env writable | Yes | `is_writable(base_path('.env'))` |

**Implementation:**

```php
// app/Services/RequirementsChecker.php
class RequirementsChecker
{
    public function check(): array
    {
        return [
            'php_version' => [
                'required' => '8.2.0',
                'current' => phpversion(),
                'passed' => version_compare(phpversion(), '8.2.0', '>='),
            ],
            'extensions' => [
                'pdo_mysql' => extension_loaded('pdo_mysql'),
                'mbstring' => extension_loaded('mbstring'),
                'xml' => extension_loaded('xml'),
                'curl' => extension_loaded('curl'),
                'zip' => extension_loaded('zip'),
                'bcmath' => extension_loaded('bcmath'),
                'gd' => extension_loaded('gd') || extension_loaded('imagick'),
                'json' => extension_loaded('json'),
                'tokenizer' => extension_loaded('tokenizer'),
            ],
            'permissions' => [
                'storage' => is_writable(storage_path()),
                'cache' => is_writable(storage_path('framework/cache')),
                'logs' => is_writable(storage_path('logs')),
                'env' => is_writable(base_path('.env')),
            ],
        ];
    }

    public function allPassed(): bool
    {
        $checks = $this->check();

        if (!$checks['php_version']['passed']) return false;
        if (in_array(false, $checks['extensions'])) return false;
        if (in_array(false, $checks['permissions'])) return false;

        return true;
    }
}
```

---

### Step 2: Database Configuration

**URL:** `/install/database`

**Form Fields:**

| Field | Type | Default | Required |
|-------|------|---------|----------|
| Database Host | text | `127.0.0.1` | Yes |
| Database Port | number | `3306` | Yes |
| Database Name | text | `osintweb` | Yes |
| Database Username | text | - | Yes |
| Database Password | password | - | Yes |
| Table Prefix | text | `osint_` | No |

**Validation:**
- Test connection before proceeding
- Check if database exists (offer to create if not)
- Check MySQL version (8.0+ required)
- Verify spatial extension support

**Implementation:**

```php
// app/Http/Controllers/Install/DatabaseController.php
public function test(Request $request)
{
    $validated = $request->validate([
        'host' => 'required|string',
        'port' => 'required|integer',
        'database' => 'required|string',
        'username' => 'required|string',
        'password' => 'nullable|string',
    ]);

    try {
        $pdo = new PDO(
            "mysql:host={$validated['host']};port={$validated['port']}",
            $validated['username'],
            $validated['password']
        );

        // Check MySQL version
        $version = $pdo->query('SELECT VERSION()')->fetchColumn();
        if (version_compare($version, '8.0', '<')) {
            return response()->json([
                'success' => false,
                'message' => "MySQL 8.0+ required. Found: {$version}",
            ]);
        }

        // Check if database exists
        $databases = $pdo->query('SHOW DATABASES')->fetchAll(PDO::FETCH_COLUMN);
        $dbExists = in_array($validated['database'], $databases);

        return response()->json([
            'success' => true,
            'database_exists' => $dbExists,
            'mysql_version' => $version,
        ]);

    } catch (PDOException $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
        ]);
    }
}

public function save(Request $request)
{
    // Update .env file
    $this->updateEnv([
        'DB_CONNECTION' => 'mysql',
        'DB_HOST' => $request->host,
        'DB_PORT' => $request->port,
        'DB_DATABASE' => $request->database,
        'DB_USERNAME' => $request->username,
        'DB_PASSWORD' => $request->password,
    ]);

    // Create database if requested
    if ($request->create_database) {
        $this->createDatabase($request->database);
    }

    return redirect()->route('install.migrations');
}
```

---

### Step 3: Run Migrations

**URL:** `/install/migrations`

**Process:**
1. Show list of migrations to run
2. Run migrations with progress indicator
3. Show success/error status for each
4. Option to seed initial data

**Display:**
```
┌─────────────────────────────────────────────────────────────┐
│                   Database Setup                            │
│                                                             │
│  Running migrations...                                      │
│                                                             │
│  ✓ create_users_table ........................... done      │
│  ✓ create_countries_table ....................... done      │
│  ✓ create_actors_table .......................... done      │
│  ✓ create_events_table .......................... done      │
│  ○ create_control_zones_table ................... running   │
│  · create_audit_logs_table ...................... pending   │
│                                                             │
│  Progress: ████████████░░░░░░░░ 60%                         │
│                                                             │
│  ☐ Seed initial data (countries, equipment categories)     │
│  ☐ Load sample conflict data (Russia-Ukraine, etc.)        │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

**Implementation:**

```php
// Run migrations via Artisan
public function runMigrations(Request $request)
{
    $output = new BufferedOutput();

    try {
        Artisan::call('migrate', [
            '--force' => true,
        ], $output);

        // Optionally seed data
        if ($request->seed_data) {
            Artisan::call('db:seed', [
                '--class' => 'InitialDataSeeder',
                '--force' => true,
            ], $output);
        }

        if ($request->sample_conflicts) {
            Artisan::call('db:seed', [
                '--class' => 'ConflictsSeeder',
                '--force' => true,
            ], $output);
        }

        return response()->json([
            'success' => true,
            'output' => $output->fetch(),
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
            'output' => $output->fetch(),
        ]);
    }
}
```

---

### Step 4: Application Settings

**URL:** `/install/settings`

**Form Fields:**

| Section | Field | Type | Default |
|---------|-------|------|---------|
| **General** | Application Name | text | `OsintWeb` |
| | Application URL | url | Auto-detected |
| | Timezone | select | `UTC` |
| | Default Language | select | `en` |
| **Map** | Default Center Lat | number | `48.8566` |
| | Default Center Lng | number | `2.3522` |
| | Default Zoom | number | `4` |
| | Default Base Layer | select | `OpenStreetMap` |
| **Registration** | Allow Public Registration | boolean | `false` |
| | Require Email Verification | boolean | `true` |
| | Default User Role | select | `viewer` |
| **Security** | Session Lifetime (minutes) | number | `120` |
| | API Rate Limit (per minute) | number | `60` |
| | Enable 2FA | boolean | `true` |

**Implementation:**

```php
// app/Http/Controllers/Install/SettingsController.php
public function save(Request $request)
{
    $validated = $request->validate([
        'app_name' => 'required|string|max:255',
        'app_url' => 'required|url',
        'timezone' => 'required|timezone',
        'locale' => 'required|string|size:2',
        'map_center_lat' => 'required|numeric|between:-90,90',
        'map_center_lng' => 'required|numeric|between:-180,180',
        'map_zoom' => 'required|integer|between:1,18',
        'allow_registration' => 'boolean',
        'require_verification' => 'boolean',
        'session_lifetime' => 'required|integer|min:5',
        'api_rate_limit' => 'required|integer|min:10',
    ]);

    // Update .env
    $this->updateEnv([
        'APP_NAME' => $validated['app_name'],
        'APP_URL' => $validated['app_url'],
        'APP_TIMEZONE' => $validated['timezone'],
        'APP_LOCALE' => $validated['locale'],
        'SESSION_LIFETIME' => $validated['session_lifetime'],
    ]);

    // Save to settings table
    foreach ($validated as $key => $value) {
        Setting::set($key, $value);
    }

    return redirect()->route('install.admin');
}
```

---

### Step 5: Admin Account

**URL:** `/install/admin`

**Form Fields:**

| Field | Type | Validation |
|-------|------|------------|
| Name | text | Required, max 255 |
| Email | email | Required, unique |
| Password | password | Required, min 12, confirmed |
| Password Confirmation | password | Required |

**Security Requirements:**
- Minimum 12 characters
- At least 1 uppercase letter
- At least 1 lowercase letter
- At least 1 number
- At least 1 special character

**Implementation:**

```php
// app/Http/Controllers/Install/AdminController.php
public function create(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => [
            'required',
            'confirmed',
            Password::min(12)
                ->mixedCase()
                ->numbers()
                ->symbols(),
        ],
    ]);

    $user = User::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'password' => Hash::make($validated['password']),
        'role' => 'admin',
        'email_verified_at' => now(),
    ]);

    return redirect()->route('install.email');
}
```

---

### Step 6: Email Configuration (Optional)

**URL:** `/install/email`

**Form Fields:**

| Field | Type | Default |
|-------|------|---------|
| Mail Driver | select | `smtp` |
| SMTP Host | text | `smtp.mailgun.org` |
| SMTP Port | number | `587` |
| SMTP Username | text | - |
| SMTP Password | password | - |
| Encryption | select | `tls` |
| From Address | email | - |
| From Name | text | `${APP_NAME}` |

**Options:**
- SMTP
- Mailgun
- Postmark
- SES
- Log (for testing)

**Test Email:**
- Send test email to admin address
- Verify delivery before proceeding

**Implementation:**

```php
public function testEmail(Request $request)
{
    try {
        Mail::raw('Test email from OsintWeb installation wizard.', function ($message) use ($request) {
            $message->to($request->test_email)
                    ->subject('OsintWeb - Installation Test Email');
        });

        return response()->json([
            'success' => true,
            'message' => 'Test email sent successfully!',
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
        ]);
    }
}
```

---

### Step 7: Search Configuration (Optional)

**URL:** `/install/search`

**Options:**

1. **MySQL Full-Text (Default)**
   - No additional configuration needed
   - Works on all MySQL 8.0+ installations
   - Good for smaller datasets (<1M records)

2. **Meilisearch**
   - Host URL
   - API Key
   - Connection test

3. **Algolia**
   - Application ID
   - Admin API Key
   - Search-only API Key

**Implementation:**

```php
public function save(Request $request)
{
    $driver = $request->search_driver;

    $this->updateEnv([
        'SCOUT_DRIVER' => $driver,
    ]);

    if ($driver === 'meilisearch') {
        $this->updateEnv([
            'MEILISEARCH_HOST' => $request->meilisearch_host,
            'MEILISEARCH_KEY' => $request->meilisearch_key,
        ]);
    } elseif ($driver === 'algolia') {
        $this->updateEnv([
            'ALGOLIA_APP_ID' => $request->algolia_app_id,
            'ALGOLIA_SECRET' => $request->algolia_secret,
        ]);
    }

    return redirect()->route('install.finish');
}
```

---

### Step 8: Installation Complete

**URL:** `/install/finish`

**Actions:**
1. Generate application key (if not set)
2. Clear all caches
3. Create installation lock file
4. Optimize application
5. Show success message with next steps

**Display:**
```
┌─────────────────────────────────────────────────────────────┐
│                 Installation Complete! ✓                    │
│                                                             │
│  OsintWeb has been successfully installed.                  │
│                                                             │
│  What's Next:                                               │
│                                                             │
│  1. Log in with your admin account                         │
│  2. Configure additional settings in Admin Panel           │
│  3. Import equipment database (optional)                   │
│  4. Create your first event or control zone                │
│                                                             │
│  Important:                                                 │
│  • Bookmark the admin URL: /admin                          │
│  • Set up automated backups                                │
│  • Configure queue workers for background jobs             │
│                                                             │
│  [Go to Login →]           [Open Admin Panel →]            │
└─────────────────────────────────────────────────────────────┘
```

**Implementation:**

```php
// app/Http/Controllers/Install/FinishController.php
public function finish()
{
    // Generate app key if needed
    if (!env('APP_KEY')) {
        Artisan::call('key:generate', ['--force' => true]);
    }

    // Clear and rebuild caches
    Artisan::call('config:clear');
    Artisan::call('cache:clear');
    Artisan::call('view:clear');
    Artisan::call('route:clear');

    // Optimize for production
    Artisan::call('config:cache');
    Artisan::call('route:cache');
    Artisan::call('view:cache');

    // Create lock file
    $lockData = [
        'installed_at' => now()->toISOString(),
        'version' => config('app.version', '1.0.0'),
        'installer_ip' => request()->ip(),
        'checksum' => hash('sha256', file_get_contents(base_path('.env'))),
    ];

    file_put_contents(
        storage_path('installed'),
        json_encode($lockData, JSON_PRETTY_PRINT)
    );

    return view('install.finish');
}
```

---

## 3. Security Considerations

### Installation Lock

Once installed, the wizard is permanently disabled:
- Lock file in `storage/installed`
- Middleware blocks access to `/install/*`
- Cannot be re-run without server access

### Re-running Installation

If re-installation is needed:

```bash
# Delete lock file (requires server access)
rm storage/installed

# Optionally reset database
php artisan migrate:fresh
```

### Installer Routes Protection

```php
// routes/web.php
Route::middleware(['web', 'install.check'])->prefix('install')->group(function () {
    Route::get('/', [WelcomeController::class, 'index'])->name('install.welcome');
    Route::get('/database', [DatabaseController::class, 'index'])->name('install.database');
    // ... other routes
});

// Middleware prevents access after installation
Route::middleware(['web', 'installed.only'])->group(function () {
    // Normal application routes
});
```

---

## 4. Database Schema

### Settings Table

```php
Schema::create('settings', function (Blueprint $table) {
    $table->id();
    $table->string('key')->unique();
    $table->text('value')->nullable();
    $table->string('type')->default('string'); // string, boolean, integer, json
    $table->string('group')->default('general');
    $table->text('description')->nullable();
    $table->boolean('is_public')->default(false);
    $table->timestamps();
});
```

### Default Settings

```php
// database/seeders/SettingsSeeder.php
$settings = [
    // General
    ['key' => 'app_name', 'value' => 'OsintWeb', 'group' => 'general'],
    ['key' => 'app_description', 'value' => 'Military Conflict Tracking Platform', 'group' => 'general'],
    ['key' => 'timezone', 'value' => 'UTC', 'group' => 'general'],
    ['key' => 'date_format', 'value' => 'Y-m-d', 'group' => 'general'],
    ['key' => 'time_format', 'value' => 'H:i:s', 'group' => 'general'],

    // Map
    ['key' => 'map_center_lat', 'value' => '48.8566', 'group' => 'map', 'type' => 'float'],
    ['key' => 'map_center_lng', 'value' => '2.3522', 'group' => 'map', 'type' => 'float'],
    ['key' => 'map_default_zoom', 'value' => '4', 'group' => 'map', 'type' => 'integer'],
    ['key' => 'map_base_layer', 'value' => 'openstreetmap', 'group' => 'map'],

    // Registration
    ['key' => 'allow_registration', 'value' => 'false', 'group' => 'registration', 'type' => 'boolean'],
    ['key' => 'require_email_verification', 'value' => 'true', 'group' => 'registration', 'type' => 'boolean'],
    ['key' => 'default_user_role', 'value' => 'viewer', 'group' => 'registration'],

    // Security
    ['key' => 'session_lifetime', 'value' => '120', 'group' => 'security', 'type' => 'integer'],
    ['key' => 'api_rate_limit', 'value' => '60', 'group' => 'security', 'type' => 'integer'],
    ['key' => 'enable_2fa', 'value' => 'true', 'group' => 'security', 'type' => 'boolean'],

    // Features
    ['key' => 'enable_public_api', 'value' => 'false', 'group' => 'features', 'type' => 'boolean'],
    ['key' => 'enable_exports', 'value' => 'true', 'group' => 'features', 'type' => 'boolean'],
    ['key' => 'max_upload_size_mb', 'value' => '10', 'group' => 'features', 'type' => 'integer'],
];
```

---

## 5. Frontend Components

### Vue Components

```
resources/js/install/
├── App.vue                 # Main installation app
├── components/
│   ├── ProgressBar.vue     # Step progress indicator
│   ├── RequirementCheck.vue # Individual requirement row
│   ├── DatabaseForm.vue    # Database configuration form
│   ├── SettingsForm.vue    # Application settings form
│   ├── AdminForm.vue       # Admin account form
│   ├── EmailForm.vue       # Email configuration form
│   └── SearchForm.vue      # Search configuration form
└── views/
    ├── Welcome.vue         # Step 1
    ├── Database.vue        # Step 2
    ├── Migrations.vue      # Step 3
    ├── Settings.vue        # Step 4
    ├── Admin.vue           # Step 5
    ├── Email.vue           # Step 6
    ├── Search.vue          # Step 7
    └── Finish.vue          # Step 8
```

### Progress Bar Component

```vue
<template>
  <div class="install-progress">
    <div
      v-for="(step, index) in steps"
      :key="step.id"
      :class="['step', {
        'completed': index < currentStep,
        'current': index === currentStep,
        'pending': index > currentStep
      }]"
    >
      <div class="step-indicator">
        <span v-if="index < currentStep">✓</span>
        <span v-else>{{ index + 1 }}</span>
      </div>
      <div class="step-label">{{ step.label }}</div>
    </div>
  </div>
</template>

<script setup>
const steps = [
  { id: 'welcome', label: 'Welcome' },
  { id: 'database', label: 'Database' },
  { id: 'migrations', label: 'Migrations' },
  { id: 'settings', label: 'Settings' },
  { id: 'admin', label: 'Admin' },
  { id: 'email', label: 'Email' },
  { id: 'search', label: 'Search' },
  { id: 'finish', label: 'Finish' },
];

defineProps<{
  currentStep: number;
}>();
</script>
```

---

## 6. Routes

```php
// routes/install.php
use App\Http\Controllers\Install\*;

Route::prefix('install')->name('install.')->group(function () {
    Route::get('/', [WelcomeController::class, 'index'])->name('welcome');

    Route::get('/database', [DatabaseController::class, 'index'])->name('database');
    Route::post('/database/test', [DatabaseController::class, 'test'])->name('database.test');
    Route::post('/database', [DatabaseController::class, 'save'])->name('database.save');

    Route::get('/migrations', [MigrationController::class, 'index'])->name('migrations');
    Route::post('/migrations/run', [MigrationController::class, 'run'])->name('migrations.run');

    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::post('/settings', [SettingsController::class, 'save'])->name('settings.save');

    Route::get('/admin', [AdminController::class, 'index'])->name('admin');
    Route::post('/admin', [AdminController::class, 'create'])->name('admin.create');

    Route::get('/email', [EmailController::class, 'index'])->name('email');
    Route::post('/email/test', [EmailController::class, 'test'])->name('email.test');
    Route::post('/email', [EmailController::class, 'save'])->name('email.save');
    Route::post('/email/skip', [EmailController::class, 'skip'])->name('email.skip');

    Route::get('/search', [SearchController::class, 'index'])->name('search');
    Route::post('/search/test', [SearchController::class, 'test'])->name('search.test');
    Route::post('/search', [SearchController::class, 'save'])->name('search.save');

    Route::get('/finish', [FinishController::class, 'index'])->name('finish');
    Route::post('/finish', [FinishController::class, 'finish'])->name('finish.complete');
});
```

---

## 7. CLI Alternative

For advanced users or automated deployments:

```bash
# Interactive CLI installer
php artisan osint:install

# Non-interactive with all parameters
php artisan osint:install \
    --db-host=localhost \
    --db-port=3306 \
    --db-name=osintweb \
    --db-user=root \
    --db-pass=secret \
    --admin-name="Admin User" \
    --admin-email=admin@example.com \
    --admin-pass=SecurePassword123! \
    --app-url=https://osint.example.com \
    --skip-email \
    --seed
```

**Implementation:**

```php
// app/Console/Commands/InstallCommand.php
class InstallCommand extends Command
{
    protected $signature = 'osint:install
        {--db-host=127.0.0.1}
        {--db-port=3306}
        {--db-name=osintweb}
        {--db-user=}
        {--db-pass=}
        {--admin-name=}
        {--admin-email=}
        {--admin-pass=}
        {--app-url=}
        {--skip-email}
        {--seed}
        {--force}';

    protected $description = 'Install OsintWeb application';

    public function handle()
    {
        // Check if already installed
        if (file_exists(storage_path('installed')) && !$this->option('force')) {
            $this->error('OsintWeb is already installed. Use --force to reinstall.');
            return 1;
        }

        $this->info('Starting OsintWeb installation...');

        // Interactive mode if no options provided
        if (!$this->option('db-user')) {
            return $this->interactive();
        }

        // Non-interactive installation
        return $this->automated();
    }
}
```

---

## 8. File Structure

```
app/
├── Console/Commands/
│   └── InstallCommand.php
├── Http/
│   ├── Controllers/Install/
│   │   ├── WelcomeController.php
│   │   ├── DatabaseController.php
│   │   ├── MigrationController.php
│   │   ├── SettingsController.php
│   │   ├── AdminController.php
│   │   ├── EmailController.php
│   │   ├── SearchController.php
│   │   └── FinishController.php
│   └── Middleware/
│       ├── CheckInstallation.php
│       └── RedirectIfInstalled.php
├── Services/
│   ├── RequirementsChecker.php
│   ├── EnvironmentWriter.php
│   └── InstallerService.php
resources/
├── js/install/
│   └── [Vue components]
└── views/install/
    └── [Blade templates]
routes/
└── install.php
```

---

*Document Version: 1.0*
*Last Updated: December 2024*
