<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use App\Services\EnvironmentWriter;
use App\Services\RequirementsChecker;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use PDO;
use PDOException;

/**
 * CLI installer for OsintWeb
 */
class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'osint:install
        {--db-host=127.0.0.1 : Database host}
        {--db-port=3306 : Database port}
        {--db-name=osintweb : Database name}
        {--db-user= : Database username}
        {--db-pass= : Database password}
        {--admin-name= : Admin user name}
        {--admin-email= : Admin email address}
        {--admin-pass= : Admin password}
        {--app-url= : Application URL}
        {--skip-email : Skip email configuration}
        {--seed : Seed initial data}
        {--force : Force reinstallation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install OsintWeb application';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // Check if already installed
        if (file_exists(storage_path('installed')) && !$this->option('force')) {
            $this->error('OsintWeb is already installed. Use --force to reinstall.');
            return self::FAILURE;
        }

        $this->info('Starting OsintWeb installation...');
        $this->newLine();

        // Check requirements
        if (!$this->checkRequirements()) {
            return self::FAILURE;
        }

        // Interactive mode if no options provided
        if (!$this->option('db-user')) {
            return $this->interactive();
        }

        // Non-interactive installation
        return $this->automated();
    }

    /**
     * Check system requirements
     */
    private function checkRequirements(): bool
    {
        $this->info('Checking system requirements...');

        $checker = new RequirementsChecker();
        $requirements = $checker->check();

        // Check PHP version
        if (!$requirements['php_version']['passed']) {
            $this->error(sprintf(
                'PHP %s or higher is required. Current: %s',
                $requirements['php_version']['required'],
                $requirements['php_version']['current']
            ));
            return false;
        }

        $this->info('✓ PHP version: ' . $requirements['php_version']['current']);

        // Check extensions
        $failed = [];
        foreach ($requirements['extensions'] as $extension => $loaded) {
            if (!$loaded) {
                $failed[] = $extension;
            }
        }

        if (!empty($failed)) {
            $this->error('Missing required PHP extensions: ' . implode(', ', $failed));
            return false;
        }

        $this->info('✓ All required PHP extensions are loaded');

        // Check permissions
        $permissionIssues = [];
        foreach ($requirements['permissions'] as $path => $writable) {
            if (!$writable) {
                $permissionIssues[] = $path;
            }
        }

        if (!empty($permissionIssues)) {
            $this->error('The following paths are not writable: ' . implode(', ', $permissionIssues));
            return false;
        }

        $this->info('✓ All required directories are writable');
        $this->newLine();

        return true;
    }

    /**
     * Interactive installation mode
     */
    private function interactive(): int
    {
        $this->info('Interactive Installation Mode');
        $this->newLine();

        // Database configuration
        $this->info('Database Configuration');
        $dbHost = $this->ask('Database host', '127.0.0.1');
        $dbPort = $this->ask('Database port', '3306');
        $dbName = $this->ask('Database name', 'osintweb');
        $dbUser = $this->ask('Database username');
        $dbPass = $this->secret('Database password');

        if (!$this->testDatabase($dbHost, (int) $dbPort, $dbName, $dbUser, $dbPass)) {
            return self::FAILURE;
        }

        // Create database if needed
        if ($this->confirm('Create database if it doesn\'t exist?', true)) {
            $this->createDatabase($dbHost, (int) $dbPort, $dbName, $dbUser, $dbPass);
        }

        $this->updateDatabase($dbHost, (int) $dbPort, $dbName, $dbUser, $dbPass);
        $this->newLine();

        // Run migrations
        $this->info('Running database migrations...');
        Artisan::call('migrate', ['--force' => true]);
        $this->info('✓ Migrations completed');

        if ($this->confirm('Seed initial data?', true)) {
            Artisan::call('db:seed', ['--class' => 'InitialDataSeeder', '--force' => true]);
            $this->info('✓ Initial data seeded');
        }

        $this->newLine();

        // Admin account
        $this->info('Admin Account');
        $adminName = $this->ask('Admin name');
        $adminEmail = $this->ask('Admin email');
        $adminPass = $this->secret('Admin password (min 12 characters)');
        $adminPassConfirm = $this->secret('Confirm admin password');

        if ($adminPass !== $adminPassConfirm) {
            $this->error('Passwords do not match');
            return self::FAILURE;
        }

        $this->createAdmin($adminName, $adminEmail, $adminPass);
        $this->newLine();

        // Application settings
        $this->info('Application Settings');
        $appUrl = $this->ask('Application URL', 'http://localhost');

        $writer = new EnvironmentWriter();
        $writer->update([
            'APP_URL' => $appUrl,
        ]);

        $this->newLine();

        // Email configuration
        if (!$this->confirm('Skip email configuration?', false)) {
            $this->configureEmail();
        }

        $this->newLine();

        // Finalize
        return $this->finalize();
    }

    /**
     * Automated installation mode
     */
    private function automated(): int
    {
        // Database
        $dbHost = $this->option('db-host');
        $dbPort = (int) $this->option('db-port');
        $dbName = $this->option('db-name');
        $dbUser = $this->option('db-user');
        $dbPass = $this->option('db-pass');

        if (!$this->testDatabase($dbHost, $dbPort, $dbName, $dbUser, $dbPass)) {
            return self::FAILURE;
        }

        $this->createDatabase($dbHost, $dbPort, $dbName, $dbUser, $dbPass);
        $this->updateDatabase($dbHost, $dbPort, $dbName, $dbUser, $dbPass);

        // Migrations
        $this->info('Running migrations...');
        Artisan::call('migrate', ['--force' => true]);

        if ($this->option('seed')) {
            Artisan::call('db:seed', ['--force' => true]);
        }

        // Admin
        $this->createAdmin(
            $this->option('admin-name'),
            $this->option('admin-email'),
            $this->option('admin-pass')
        );

        // Settings
        $writer = new EnvironmentWriter();
        if ($this->option('app-url')) {
            $writer->update(['APP_URL' => $this->option('app-url')]);
        }

        return $this->finalize();
    }

    /**
     * Test database connection
     */
    private function testDatabase(string $host, int $port, string $database, string $username, ?string $password): bool
    {
        $this->info('Testing database connection...');

        try {
            $pdo = new PDO(
                "mysql:host={$host};port={$port}",
                $username,
                $password ?? ''
            );

            $version = $pdo->query('SELECT VERSION()')->fetchColumn();

            if (!is_string($version)) {
                $this->error('Unable to determine MySQL version');
                return false;
            }

            if (version_compare($version, '8.0', '<')) {
                $this->error("MySQL 8.0+ required. Found: {$version}");
                return false;
            }

            $this->info("✓ Connected to MySQL {$version}");
            return true;
        } catch (PDOException $e) {
            $this->error('Database connection failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Create database
     */
    private function createDatabase(string $host, int $port, string $database, string $username, ?string $password): void
    {
        try {
            $pdo = new PDO(
                "mysql:host={$host};port={$port}",
                $username,
                $password ?? ''
            );

            // Final security check before raw SQL execution
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $database)) {
                $this->error('Invalid database name. Only alphanumeric characters and underscores are allowed.');
                return;
            }

            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $this->info("✓ Database '{$database}' ready");
        } catch (PDOException $e) {
            $this->warn('Could not create database: ' . $e->getMessage());
        }
    }

    /**
     * Update database configuration in .env
     */
    private function updateDatabase(string $host, int $port, string $database, string $username, ?string $password): void
    {
        $writer = new EnvironmentWriter();
        $writer->update([
            'DB_CONNECTION' => 'mysql',
            'DB_HOST' => $host,
            'DB_PORT' => (string) $port,
            'DB_DATABASE' => $database,
            'DB_USERNAME' => $username,
            'DB_PASSWORD' => $password ?? '',
        ]);

        $this->info('✓ Database configuration saved');
    }

    /**
     * Create admin user
     */
    private function createAdmin(string $name, string $email, string $password): void
    {
        $this->info('Creating admin user...');

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'email_verified_at' => now(),
        ]);
        $user->forceFill(['role' => 'admin', 'is_active' => true, 'is_verified' => true])->save();

        $this->info('✓ Admin user created');
    }

    /**
     * Configure email
     */
    private function configureEmail(): void
    {
        $mailer = $this->choice('Email driver', ['smtp', 'mailgun', 'postmark', 'ses', 'log'], 'smtp');

        $writer = new EnvironmentWriter();
        $envData = ['MAIL_MAILER' => $mailer];

        if ($mailer === 'smtp') {
            $envData['MAIL_HOST'] = $this->ask('SMTP host', 'smtp.mailgun.org');
            $envData['MAIL_PORT'] = $this->ask('SMTP port', '587');
            $envData['MAIL_USERNAME'] = $this->ask('SMTP username');
            $envData['MAIL_PASSWORD'] = $this->secret('SMTP password');
            $envData['MAIL_ENCRYPTION'] = $this->choice('Encryption', ['tls', 'ssl'], 'tls');
        }

        $envData['MAIL_FROM_ADDRESS'] = $this->ask('From address', 'noreply@example.com');
        $envData['MAIL_FROM_NAME'] = $this->ask('From name', 'OsintWeb');

        $writer->update($envData);
        $this->info('✓ Email configuration saved');
    }

    /**
     * Finalize installation
     */
    private function finalize(): int
    {
        $this->info('Finalizing installation...');

        // Generate app key if needed
        if (!env('APP_KEY')) {
            Artisan::call('key:generate', ['--force' => true]);
        }

        // Clear caches
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        Artisan::call('view:clear');

        // Create lock file
        $lockData = [
            'installed_at' => now()->toIso8601String(),
            'version' => config('app.version', '1.0.0'),
            'installer' => 'cli',
        ];

        file_put_contents(
            storage_path('installed'),
            json_encode($lockData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );

        $this->newLine();
        $this->info('✓ Installation completed successfully!');
        $this->newLine();
        $this->info('Next steps:');
        $this->info('  1. Configure your web server to point to the public directory');
        $this->info('  2. Set up queue workers for background jobs');
        $this->info('  3. Configure automated backups');
        $this->info('  4. Log in with your admin credentials');
        $this->newLine();

        return self::SUCCESS;
    }
}
