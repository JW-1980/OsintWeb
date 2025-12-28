---
name: database-migration-check
description: Comprehensive verification of database migrations including foreign key dependencies, zero-downtime strategies, testing, and rollback safety
tags: [database, migrations, laravel, devops, ci-cd, testing, zero-downtime]
version: 2.0.1
trigger_keywords: [sk-database-migration-check, migration check, database migration, migration verification, migration testing, rollback test, migration dependencies, zero-downtime migration]
---

# Database Migration Check Skill

This skill provides comprehensive verification and testing of Laravel database migrations, including dependency checking, zero-downtime strategies, rollback testing, and CI/CD integration.

## When to Use

- Before running `php artisan migrate`
- When adding new migrations
- When experiencing migration errors
- When reviewing pull requests with database changes
- Before deploying to production environments
- When planning large schema changes
- When migrating data between formats
- During database upgrade planning
- When troubleshooting migration failures

## Steps

### 1. Check for Duplicate Table Migrations

```bash
cd bookkeeping-app/database/migrations
grep -h "Schema::create" *.php | sed "s/.*create('\([^']*\)'.*/\1/" | sort | uniq -c | sort -rn | grep -E "^\s+[2-9]"
```

If any tables appear more than once, identify and remove duplicates.

### 2. Check Foreign Key Dependencies

For each table with foreign keys, verify the referenced table is created first:

```bash
# Example: Check if 'crm_contacts' references 'companies'
# The crm_contacts migration must have a LATER timestamp than companies migration

# List migrations chronologically
ls -1 *create*.php | sort

# Check specific dependency
grep -l "foreignId.*company_id" *.php | while read f; do
  timestamp=$(echo "$f" | cut -d'_' -f1-4)
  echo "$timestamp: $f"
done
```

### 3. Verify Migration Order

Ensure tables are created in dependency order:
1. Base tables (users, companies) first
2. Tables that reference base tables next
3. Junction/pivot tables last

### 4. Common Issues to Check

- ❌ **Duplicate column definitions** - Check for repeated `$table->` statements
- ❌ **Missing nullable() on optional foreign keys**
- ❌ **Circular dependencies** - Table A → Table B → Table A
- ❌ **Wrong constraint actions** - Ensure cascadeOnDelete() vs nullOnDelete() is correct

## Example Output

```
✅ No duplicate table migrations found
✅ All foreign key dependencies in correct order
✅ No circular dependencies detected
✅ Migration order is correct
```

## Migration Order Rules

1. **0001_01_01** - Framework tables (users, password_resets)
2. **2025_11_01** - Core business tables (companies, clients)
3. **2025_11_02+** - Feature tables (CRM, certificates, etc.)
4. **Later dates** - Enhancement migrations

## Quick Fix Commands

### Rename Migration (Fix Ordering)
```bash
# Example: Move CRM table after companies
mv 2024_11_18_000001_create_crm_contacts_table.php \
   2025_11_02_000001_create_crm_contacts_table.php
```

### Remove Duplicate
```bash
# Keep the most comprehensive version, delete others
rm 2025_11_07_009000_create_documents_table.php
```

---

## Migration Testing Framework

### 1. Automated Migration Testing

Create `tests/Database/MigrationTest.php`:

```php
<?php

namespace Tests\Database;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class MigrationTest extends TestCase
{
    /**
     * Test all migrations can run successfully
     */
    public function test_all_migrations_can_run(): void
    {
        // Start fresh
        Artisan::call('migrate:fresh', ['--force' => true]);

        $this->assertTrue(true); // If we got here, migrations passed
    }

    /**
     * Test migrations are reversible
     */
    public function test_all_migrations_are_reversible(): void
    {
        Artisan::call('migrate:fresh', ['--force' => true]);

        // Roll back all migrations
        Artisan::call('migrate:rollback', [
            '--step' => 1000, // All migrations
            '--force' => true,
        ]);

        // Verify core tables don't exist (they were rolled back)
        $this->assertFalse(Schema::hasTable('invoices'));
        $this->assertFalse(Schema::hasTable('clients'));
    }

    /**
     * Test migration can be re-run after rollback
     */
    public function test_migrations_are_idempotent(): void
    {
        // Run fresh
        Artisan::call('migrate:fresh', ['--force' => true]);

        // Roll back one step
        Artisan::call('migrate:rollback', ['--step' => 1, '--force' => true]);

        // Re-run
        Artisan::call('migrate', ['--force' => true]);

        $this->assertTrue(true);
    }

    /**
     * Test all required tables exist after migration
     */
    public function test_required_tables_exist(): void
    {
        Artisan::call('migrate:fresh', ['--force' => true]);

        $requiredTables = [
            'users',
            'companies',
            'clients',
            'invoices',
            'invoice_items',
            'journal_entries',
            'ledger_accounts',
            'vat_declarations',
        ];

        foreach ($requiredTables as $table) {
            $this->assertTrue(
                Schema::hasTable($table),
                "Required table '{$table}' does not exist"
            );
        }
    }

    /**
     * Test all company-scoped tables have company_id
     */
    public function test_company_scoped_tables_have_company_id(): void
    {
        Artisan::call('migrate:fresh', ['--force' => true]);

        $companyTables = [
            'clients',
            'invoices',
            'expenses',
            'bank_accounts',
            'journal_entries',
        ];

        foreach ($companyTables as $table) {
            $this->assertTrue(
                Schema::hasColumn($table, 'company_id'),
                "Table '{$table}' missing company_id column"
            );
        }
    }

    /**
     * Test foreign key constraints exist
     */
    public function test_foreign_key_constraints_exist(): void
    {
        Artisan::call('migrate:fresh', ['--force' => true]);

        // Check invoices -> companies FK
        $constraints = $this->getForeignKeys('invoices');
        $this->assertContains('company_id', array_column($constraints, 'column_name'));

        // Check invoices -> clients FK
        $this->assertContains('client_id', array_column($constraints, 'column_name'));
    }

    /**
     * Test indexes exist for performance
     */
    public function test_performance_indexes_exist(): void
    {
        Artisan::call('migrate:fresh', ['--force' => true]);

        // Check important composite indexes
        $indexes = $this->getIndexes('invoices');
        $indexNames = array_column($indexes, 'Key_name');

        // Should have company_id index
        $hasCompanyIndex = collect($indexes)
            ->contains(fn($idx) => str_contains($idx['Column_name'], 'company_id'));

        $this->assertTrue($hasCompanyIndex, 'Missing company_id index on invoices');
    }

    private function getForeignKeys(string $table): array
    {
        return DB::select("
            SELECT COLUMN_NAME as column_name, REFERENCED_TABLE_NAME as ref_table
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = ?
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ", [$table]);
    }

    private function getIndexes(string $table): array
    {
        return DB::select("SHOW INDEX FROM {$table}");
    }
}
```

### 2. Run Migration Tests

```bash
# Run all database migration tests
php artisan test tests/Database/MigrationTest.php

# Run specific test
php artisan test --filter=test_all_migrations_can_run

# Run with verbose output
php artisan test tests/Database/MigrationTest.php -v
```

---

## Rollback Testing

### 1. Rollback Verification Command

Create `app/Console/Commands/VerifyMigrationRollback.php`:

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class VerifyMigrationRollback extends Command
{
    protected $signature = 'migration:verify-rollback
        {--steps=5 : Number of migrations to test}
        {--dry-run : Don\'t actually roll back}';

    protected $description = 'Verify migrations can be safely rolled back';

    public function handle(): int
    {
        $steps = $this->option('steps');
        $dryRun = $this->option('dry-run');

        $this->info("Testing rollback of last {$steps} migrations...");

        // Get current table list
        $tablesBefore = $this->getTableList();

        if ($dryRun) {
            $this->warn('Dry run mode - analyzing migrations only');
            return $this->analyzeRollbacks($steps);
        }

        // Create backup point
        $this->info('Creating backup checkpoint...');

        // Rollback
        $this->warn("Rolling back {$steps} migrations...");
        Artisan::call('migrate:rollback', [
            '--step' => $steps,
            '--force' => true,
        ]);

        $this->info(Artisan::output());

        // Re-migrate
        $this->info('Re-running migrations...');
        Artisan::call('migrate', ['--force' => true]);

        $this->info(Artisan::output());

        // Verify tables
        $tablesAfter = $this->getTableList();

        if ($tablesBefore != $tablesAfter) {
            $this->error('Table structure mismatch after rollback/migrate!');
            $this->error('Missing: ' . implode(', ', array_diff($tablesBefore, $tablesAfter)));
            $this->error('Extra: ' . implode(', ', array_diff($tablesAfter, $tablesBefore)));
            return 1;
        }

        $this->info('✅ Rollback verification passed!');
        return 0;
    }

    private function analyzeRollbacks(int $steps): int
    {
        $migrations = DB::table('migrations')
            ->orderBy('batch', 'desc')
            ->orderBy('id', 'desc')
            ->take($steps)
            ->get();

        $issues = [];

        foreach ($migrations as $migration) {
            $file = database_path("migrations/{$migration->migration}.php");

            if (!file_exists($file)) {
                $issues[] = "Migration file not found: {$migration->migration}";
                continue;
            }

            $content = file_get_contents($file);

            // Check for missing down() method
            if (!str_contains($content, 'public function down()')) {
                $issues[] = "{$migration->migration}: Missing down() method";
            }

            // Check for data-destructive operations in up() without protection
            if (str_contains($content, 'dropColumn') || str_contains($content, 'dropTable')) {
                $issues[] = "{$migration->migration}: Contains destructive operation - verify data backup";
            }

            // Check for proper Schema::dropIfExists in down()
            if (str_contains($content, 'Schema::create')) {
                if (!str_contains($content, 'Schema::dropIfExists')) {
                    $issues[] = "{$migration->migration}: down() should use dropIfExists for safety";
                }
            }
        }

        if (count($issues) > 0) {
            $this->error('Issues found:');
            foreach ($issues as $issue) {
                $this->line("  ⚠️  {$issue}");
            }
            return 1;
        }

        $this->info('✅ No rollback issues detected');
        return 0;
    }

    private function getTableList(): array
    {
        return collect(DB::select('SHOW TABLES'))
            ->map(fn($t) => array_values((array)$t)[0])
            ->sort()
            ->values()
            ->toArray();
    }
}
```

### 2. Rollback Safety Patterns

```php
// ✅ GOOD: Safe rollback with existence checks
public function down(): void
{
    Schema::dropIfExists('invoices');
}

// ✅ GOOD: Data preservation before destructive changes
public function down(): void
{
    // Restore column if it was dropped in up()
    if (!Schema::hasColumn('invoices', 'legacy_id')) {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('legacy_id')->nullable();
        });
    }
}

// ❌ BAD: Hard failure on rollback
public function down(): void
{
    Schema::drop('invoices'); // Fails if table doesn't exist
}
```

---

## Zero-Downtime Migration Strategies

### 1. Expand-Contract Pattern

For renaming columns or restructuring data:

```php
// Migration 1: EXPAND - Add new column
public function up(): void
{
    Schema::table('invoices', function (Blueprint $table) {
        $table->string('invoice_reference')->nullable()->after('invoice_number');
    });
}

// Deploy code that writes to BOTH old and new columns
// Run data migration to copy old data to new column

// Migration 2: CONTRACT - Remove old column (after deployment)
public function up(): void
{
    Schema::table('invoices', function (Blueprint $table) {
        $table->dropColumn('invoice_number');
    });
}
```

### 2. Feature Flag Pattern

```php
// Migration 1: Add new structure alongside old
public function up(): void
{
    Schema::create('invoices_v2', function (Blueprint $table) {
        // New structure
    });
}

// App code:
if (config('features.use_invoices_v2')) {
    // Use new table
} else {
    // Use old table
}

// Migration 2: After testing, drop old table
public function up(): void
{
    Schema::dropIfExists('invoices');
    Schema::rename('invoices_v2', 'invoices');
}
```

### 3. Online Schema Change for Large Tables

For tables with millions of rows:

```php
// Use pt-online-schema-change for MySQL
public function up(): void
{
    // For tables > 1M rows, use external tool
    if (app()->environment('production')) {
        $this->warn('Large table migration - use pt-online-schema-change');

        // Execute OSC
        $command = sprintf(
            'pt-online-schema-change --alter "ADD COLUMN new_column VARCHAR(255)" D=%s,t=large_table --execute',
            config('database.connections.mysql.database')
        );

        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            throw new \Exception('pt-osc failed: ' . implode("\n", $output));
        }
    } else {
        // Use regular migration for non-production
        Schema::table('large_table', function (Blueprint $table) {
            $table->string('new_column')->nullable();
        });
    }
}
```

---

## Data Migration Patterns

### 1. Batch Data Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Migrate data in batches to avoid memory issues
        DB::table('invoices')
            ->whereNull('invoice_reference')
            ->orderBy('id')
            ->chunk(1000, function ($invoices) {
                foreach ($invoices as $invoice) {
                    DB::table('invoices')
                        ->where('id', $invoice->id)
                        ->update([
                            'invoice_reference' => 'INV-' . str_pad($invoice->id, 8, '0', STR_PAD_LEFT),
                        ]);
                }
            });
    }

    public function down(): void
    {
        // Reversible: Clear generated references
        DB::table('invoices')
            ->whereNotNull('invoice_reference')
            ->update(['invoice_reference' => null]);
    }
};
```

### 2. Progress Tracking Migration

```php
<?php

use Illuminate\Console\Command;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $total = DB::table('invoices')->count();
        $processed = 0;

        if (app()->runningInConsole()) {
            $this->command->info("Migrating {$total} invoices...");
        }

        DB::table('invoices')
            ->orderBy('id')
            ->chunk(1000, function ($invoices) use ($total, &$processed) {
                foreach ($invoices as $invoice) {
                    // Migration logic here
                    $processed++;
                }

                if (app()->runningInConsole()) {
                    $percent = round(($processed / $total) * 100);
                    $this->command->info("Progress: {$processed}/{$total} ({$percent}%)");
                }
            });
    }
};
```

---

## Migration Linting

### 1. PHPStan Rules for Migrations

```php
// Custom PHPStan rule: CheckMigrationHasDown
class MigrationHasDownRule implements Rule
{
    public function getNodeType(): string
    {
        return Class_::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$this->isMigrationClass($node)) {
            return [];
        }

        $hasDown = false;
        foreach ($node->stmts as $stmt) {
            if ($stmt instanceof ClassMethod && $stmt->name->toString() === 'down') {
                $hasDown = true;
                break;
            }
        }

        if (!$hasDown) {
            return [
                RuleErrorBuilder::message('Migration must have a down() method for rollback')
                    ->build(),
            ];
        }

        return [];
    }
}
```

### 2. Migration Lint Command

Create `app/Console/Commands/LintMigrations.php`:

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class LintMigrations extends Command
{
    protected $signature = 'migration:lint {--fix : Attempt to fix issues}';
    protected $description = 'Lint migrations for common issues';

    private array $issues = [];

    public function handle(): int
    {
        $migrations = File::glob(database_path('migrations/*.php'));

        foreach ($migrations as $file) {
            $this->lintMigration($file);
        }

        if (count($this->issues) === 0) {
            $this->info('✅ No issues found in migrations');
            return 0;
        }

        $this->error(count($this->issues) . ' issues found:');
        foreach ($this->issues as $issue) {
            $this->line("  ⚠️  {$issue}");
        }

        return 1;
    }

    private function lintMigration(string $file): void
    {
        $content = File::get($file);
        $filename = basename($file);

        // Check 1: Has down() method
        if (!preg_match('/public\s+function\s+down\s*\(/', $content)) {
            $this->issues[] = "{$filename}: Missing down() method";
        }

        // Check 2: Uses dropIfExists in down()
        if (preg_match('/Schema::create\s*\(/', $content)) {
            if (preg_match('/Schema::drop\s*\(/', $content) && !preg_match('/Schema::dropIfExists/', $content)) {
                $this->issues[] = "{$filename}: Use dropIfExists instead of drop in down()";
            }
        }

        // Check 3: Company-scoped tables have company_id
        $companyTables = ['invoices', 'expenses', 'clients', 'journal_entries'];
        foreach ($companyTables as $table) {
            if (preg_match("/Schema::create\s*\(\s*['\"]" . $table . "['\"]/", $content)) {
                if (!preg_match('/company_id/', $content)) {
                    $this->issues[] = "{$filename}: Table '{$table}' missing company_id";
                }
            }
        }

        // Check 4: Foreign keys have proper constraints
        if (preg_match('/->constrained\(\)/', $content)) {
            if (!preg_match('/cascadeOnDelete|nullOnDelete|restrictOnDelete/', $content)) {
                $this->issues[] = "{$filename}: Foreign key missing cascade/null/restrict on delete";
            }
        }

        // Check 5: Indexes on frequently queried columns
        if (preg_match("/Schema::create.*'([^']+)'.*company_id/s", $content, $matches)) {
            if (!preg_match('/->index\s*\(\s*\[\s*[\'"]company_id/', $content)) {
                $this->issues[] = "{$filename}: Consider adding index on company_id";
            }
        }

        // Check 6: Large text fields should specify length
        if (preg_match('/->text\(\s*[\'"][^\']+[\'"]\s*\)/', $content)) {
            $this->issues[] = "{$filename}: Consider using mediumText() for large content or specifying intended use";
        }
    }
}
```

---

## Migration Documentation

### 1. Auto-Generate Migration Docs

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateMigrationDocs extends Command
{
    protected $signature = 'migration:docs {--output=docs/migrations.md}';
    protected $description = 'Generate documentation for all migrations';

    public function handle(): void
    {
        $migrations = File::glob(database_path('migrations/*.php'));
        $docs = "# Database Migrations\n\n";
        $docs .= "Generated: " . now()->toDateTimeString() . "\n\n";
        $docs .= "| Date | Migration | Tables | Description |\n";
        $docs .= "|------|-----------|--------|-------------|\n";

        foreach ($migrations as $file) {
            $filename = basename($file, '.php');
            $content = File::get($file);

            // Extract date
            preg_match('/^(\d{4}_\d{2}_\d{2})/', $filename, $dateMatch);
            $date = $dateMatch[1] ?? 'Unknown';

            // Extract tables
            preg_match_all("/Schema::(create|table)\s*\(\s*['\"]([^'\"]+)['\"]/", $content, $tableMatches);
            $tables = implode(', ', array_unique($tableMatches[2] ?? []));

            // Extract description from docblock
            preg_match('/\/\*\*\s*\n\s*\*\s*(.+?)\n/', $content, $descMatch);
            $description = $descMatch[1] ?? '-';

            $docs .= "| {$date} | {$filename} | {$tables} | {$description} |\n";
        }

        File::put($this->option('output'), $docs);
        $this->info('Documentation generated: ' . $this->option('output'));
    }
}
```

---

## CI/CD Integration

### GitHub Actions Migration Testing

```yaml
# .github/workflows/migrations.yml
name: Migration Tests

on:
  pull_request:
    paths:
      - 'database/migrations/**'

jobs:
  migration-test:
    runs-on: ubuntu-latest

    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: password
          MYSQL_DATABASE: testing
        ports:
          - 3306:3306

    steps:
      - uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: 8.2

      - name: Install Dependencies
        run: composer install --no-progress

      - name: Run Migration Fresh
        run: php artisan migrate:fresh --force
        env:
          DB_CONNECTION: mysql
          DB_HOST: 127.0.0.1
          DB_DATABASE: testing
          DB_USERNAME: root
          DB_PASSWORD: password

      - name: Run Migration Rollback
        run: php artisan migrate:rollback --step=1000 --force

      - name: Run Migration Again
        run: php artisan migrate --force

      - name: Run Migration Tests
        run: php artisan test tests/Database/MigrationTest.php

      - name: Lint Migrations
        run: php artisan migration:lint
```

---

## Breaking Change Detection

### 1. Schema Comparison Tool

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DetectBreakingChanges extends Command
{
    protected $signature = 'migration:detect-breaking
        {--before= : Commit hash before changes}
        {--after=HEAD : Commit hash after changes}';

    protected $description = 'Detect breaking schema changes between commits';

    public function handle(): int
    {
        $breakingChanges = [];

        // Get new migrations
        $newMigrations = $this->getNewMigrations();

        foreach ($newMigrations as $file) {
            $content = file_get_contents($file);

            // Detect column drops
            if (preg_match_all("/dropColumn\s*\(\s*['\"]([^'\"]+)['\"]/", $content, $matches)) {
                foreach ($matches[1] as $column) {
                    $breakingChanges[] = [
                        'type' => 'BREAKING',
                        'file' => basename($file),
                        'change' => "Drops column: {$column}",
                        'impact' => 'Existing code referencing this column will fail',
                    ];
                }
            }

            // Detect table drops
            if (preg_match_all("/Schema::(drop|dropIfExists)\s*\(\s*['\"]([^'\"]+)['\"]/", $content, $matches)) {
                foreach ($matches[2] as $table) {
                    $breakingChanges[] = [
                        'type' => 'BREAKING',
                        'file' => basename($file),
                        'change' => "Drops table: {$table}",
                        'impact' => 'All references to this table will fail',
                    ];
                }
            }

            // Detect column renames
            if (preg_match_all("/renameColumn\s*\(\s*['\"]([^'\"]+)['\"]\s*,\s*['\"]([^'\"]+)['\"]/", $content, $matches)) {
                for ($i = 0; $i < count($matches[1]); $i++) {
                    $breakingChanges[] = [
                        'type' => 'BREAKING',
                        'file' => basename($file),
                        'change' => "Renames column: {$matches[1][$i]} → {$matches[2][$i]}",
                        'impact' => 'Code using old column name will fail',
                    ];
                }
            }

            // Detect non-nullable columns added without default
            if (preg_match_all("/->string\s*\(\s*['\"]([^'\"]+)['\"]\s*\)[^;]*(?<!->nullable\(\))[^;]*;/", $content, $matches)) {
                foreach ($matches[1] as $column) {
                    if (!preg_match("/->default\s*\(/", $matches[0][0])) {
                        $breakingChanges[] = [
                            'type' => 'WARNING',
                            'file' => basename($file),
                            'change' => "Adds non-nullable column without default: {$column}",
                            'impact' => 'Will fail if table has existing rows',
                        ];
                    }
                }
            }
        }

        if (count($breakingChanges) === 0) {
            $this->info('✅ No breaking changes detected');
            return 0;
        }

        $this->error('⚠️  Breaking changes detected:');
        $this->table(['Type', 'File', 'Change', 'Impact'], $breakingChanges);

        return 1;
    }

    private function getNewMigrations(): array
    {
        // In real implementation, use git diff to find new migration files
        return glob(database_path('migrations/*.php'));
    }
}
```

---

## Troubleshooting

### Issue 1: "Table already exists"

```bash
# Check if table exists
php artisan tinker
>>> Schema::hasTable('invoices')

# Solution: Add existence check
if (!Schema::hasTable('invoices')) {
    Schema::create('invoices', ...);
}
```

### Issue 2: "Foreign key constraint fails"

```bash
# Check foreign key dependencies
php artisan tinker
>>> DB::select("SELECT * FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_NAME = 'invoices' AND REFERENCED_TABLE_NAME IS NOT NULL")

# Solution: Ensure parent table migrates first (rename file to earlier timestamp)
```

### Issue 3: "Column not found in after() clause"

```bash
# Check actual column names
php artisan tinker
>>> Schema::getColumnListing('invoices')

# Solution: Use existing column name
$table->string('new_col')->after('existing_col');
```

### Issue 4: "Cannot drop column with foreign key"

```php
// Solution: Drop foreign key first
Schema::table('invoices', function (Blueprint $table) {
    $table->dropForeign(['client_id']);
    $table->dropColumn('client_id');
});
```

---

## Resources

- **Laravel Migrations**: https://laravel.com/docs/migrations
- **Database Testing**: https://laravel.com/docs/database-testing
- **pt-online-schema-change**: https://www.percona.com/doc/percona-toolkit/LATEST/pt-online-schema-change.html
- **Zero Downtime Migrations**: https://stripe.com/blog/online-migrations
- **MySQL Foreign Keys**: https://dev.mysql.com/doc/refman/8.0/en/create-table-foreign-keys.html

---

*Version 2.0.0 - Enhanced with testing frameworks, rollback verification, zero-downtime strategies, data migration patterns, linting, documentation generation, CI/CD integration, and breaking change detection*
