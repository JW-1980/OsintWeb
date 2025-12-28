---
name: laravel-test-suite
description: Run comprehensive Laravel test suite with PHPUnit and code coverage
version: 2.0.1
tags: [testing, laravel, phpunit, quality-assurance, pest, tdd, coverage]
trigger_keywords: [sk-laravel-test-suite, laravel testing, phpunit tests, pest tests, test suite, test coverage, laravel tdd, feature tests]
---

# Laravel Test Suite Skill

This skill runs comprehensive tests for the Laravel bookkeeping application with PHPUnit/Pest, providing complete testing strategies for multi-tenant applications.

## When to Use

- Before committing code
- After implementing new features
- Before creating pull requests
- During CI/CD pipeline
- When debugging failing tests
- After refactoring existing code
- When implementing new API endpoints
- Before deploying to staging/production
- During security audits
- When onboarding new developers

## Test Categories

### 1. Unit Tests
Test individual classes and methods in isolation.

```bash
cd bookkeeping-app
php artisan test --testsuite=Unit
```

### 2. Feature Tests
Test HTTP endpoints, controllers, and integration points.

```bash
php artisan test --testsuite=Feature
```

### 3. Specific Test Files

```bash
# Test permissions
php artisan test tests/Feature/PermissionTest.php

# Test advertising features
php artisan test tests/Feature/AdvertisingTest.php

# Test migrations
php artisan test tests/Feature/MigrationTest.php
```

### 4. With Code Coverage

```bash
php artisan test --coverage --min=80
```

## Quick Test Commands

### Run All Tests
```bash
php artisan test
```

### Run Specific Test Method
```bash
php artisan test --filter=test_user_can_create_advertisement
```

### Run Tests for Specific Feature
```bash
# Permission tests
php artisan test tests/Feature/Permissions/

# API tests
php artisan test tests/Feature/Api/
```

### Parallel Testing (Faster)
```bash
php artisan test --parallel
```

## Testing Best Practices

### 1. Test Structure (AAA Pattern)
```php
public function test_user_can_create_advertisement_with_permission()
{
    // Arrange - Set up test data
    $user = User::factory()->create();
    $company = Company::factory()->create();
    $user->companies()->attach($company, ['role' => 'admin']);

    // Act - Perform the action
    $response = $this->actingAs($user)
        ->post('/api/advertisements', [
            'title' => 'Test Ad',
            'budget' => 1000,
        ]);

    // Assert - Verify the result
    $response->assertStatus(201);
    $this->assertDatabaseHas('advertisements', [
        'title' => 'Test Ad',
        'company_id' => $company->id,
    ]);
}
```

### 2. Permission Testing
```php
public function test_user_without_permission_cannot_create_advertisement()
{
    $user = User::factory()->create();
    $company = Company::factory()->create();
    // Grant viewer role (no create permission)
    $user->companies()->attach($company, ['role' => 'viewer']);

    $response = $this->actingAs($user)
        ->post('/api/advertisements', ['title' => 'Test Ad']);

    $response->assertForbidden();
}
```

### 3. Database Testing
```php
use Illuminate\Foundation\Testing\RefreshDatabase;

class MigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_migrations_run_without_errors()
    {
        $this->artisan('migrate:fresh')->assertSuccessful();
    }

    public function test_companies_table_exists()
    {
        $this->assertTrue(Schema::hasTable('companies'));
        $this->assertTrue(Schema::hasColumn('companies', 'name'));
    }
}
```

## Common Test Scenarios

### Authorization Tests
```bash
# Test all permission-protected endpoints
php artisan test tests/Feature/Authorization/
```

### API Tests
```bash
# Test RESTful API endpoints
php artisan test tests/Feature/Api/
```

### Integration Tests
```bash
# Test service interactions
php artisan test tests/Integration/
```

## Test Output Interpretation

```
PASS  Tests\Feature\AdvertisingTest
✓ user can view advertising dashboard
✓ user can create advertisement with permission
✓ user cannot create advertisement without permission
✓ advertisement budget is validated

Tests:  4 passed
Time:   0.34s
```

## Coverage Requirements

Minimum coverage targets:
- **Models**: 90%
- **Controllers**: 85%
- **Services**: 90%
- **Policies**: 95%
- **Overall**: 80%

## Debugging Failed Tests

### View Detailed Output
```bash
php artisan test --verbose
```

### Stop on First Failure
```bash
php artisan test --stop-on-failure
```

### Run Only Failed Tests
```bash
php artisan test --failed
```

## CI/CD Integration

```yaml
# .github/workflows/tests.yml
- name: Run Tests
  run: |
    cp .env.testing .env
    php artisan key:generate
    php artisan migrate:fresh
    php artisan test --coverage --min=80
```

## Multi-Tenancy Testing Patterns

### Testing with Company Scoping

```php
use Illuminate\Foundation\Testing\RefreshDatabase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_only_see_own_company_invoices()
    {
        // Arrange: Create two companies
        $company1 = Company::factory()->create();
        $company2 = Company::factory()->create();

        $user = User::factory()->create();
        $user->companies()->attach($company1, ['role' => 'admin']);

        // Create invoices for both companies
        $invoice1 = Invoice::factory()->create(['company_id' => $company1->id]);
        $invoice2 = Invoice::factory()->create(['company_id' => $company2->id]);

        // Act & Assert
        $this->actingAs($user)
            ->get('/api/invoices')
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['id' => $invoice1->id])
            ->assertJsonMissing(['id' => $invoice2->id]);
    }

    public function test_global_scope_prevents_cross_company_access()
    {
        $company1 = Company::factory()->create();
        $company2 = Company::factory()->create();

        $invoice = Invoice::factory()->create(['company_id' => $company2->id]);

        // Set current company context
        app()->singleton('current_company', fn() => $company1);

        // Should not find invoice from other company
        $this->assertNull(Invoice::find($invoice->id));
    }
}
```

### Testing Permission-Based Access

```php
public function test_user_without_invoice_create_permission_cannot_create()
{
    $user = User::factory()->create();
    $company = Company::factory()->create();

    // Attach with viewer role (no create permission)
    $user->companies()->attach($company, [
        'role' => 'viewer',
        'permissions' => [
            'invoice_permissions' => ['view' => true, 'create' => false]
        ]
    ]);

    $this->actingAs($user)
        ->post('/api/invoices', [
            'client_id' => 1,
            'amount' => 1000
        ])
        ->assertForbidden();
}

public function test_external_accountant_has_read_only_access()
{
    $user = User::factory()->create();
    $company = Company::factory()->create();
    $user->companies()->attach($company, ['role' => 'external_accountant']);

    $invoice = Invoice::factory()->create(['company_id' => $company->id]);

    // Can view
    $this->actingAs($user)
        ->get("/api/invoices/{$invoice->id}")
        ->assertOk();

    // Cannot delete
    $this->actingAs($user)
        ->delete("/api/invoices/{$invoice->id}")
        ->assertForbidden();
}
```

## Advanced Testing Techniques

### Testing Dutch VAT Calculations

```php
class VatCalculationTest extends TestCase
{
    public function test_vat_calculation_with_standard_rate()
    {
        $amount = 1000.00;
        $vatRate = 0.21; // 21% Dutch standard rate

        $calculator = new VatCalculator();
        $result = $calculator->calculate($amount, $vatRate);

        $this->assertEquals(1000.00, $result['amount_ex_vat']);
        $this->assertEquals(210.00, $result['vat_amount']);
        $this->assertEquals(1210.00, $result['total_incl_vat']);
    }

    public function test_vat_calculation_with_reduced_rate()
    {
        $amount = 100.00;
        $vatRate = 0.09; // 9% Dutch reduced rate

        $calculator = new VatCalculator();
        $result = $calculator->calculate($amount, $vatRate);

        $this->assertEquals(9.00, $result['vat_amount']);
        $this->assertEquals(109.00, $result['total_incl_vat']);
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
        $this->assertStringContainsString('Reverse charge', $invoice->vat_note);
    }

    /** @dataProvider dutchVatRatesProvider */
    public function test_vat_rates_for_different_product_types($productType, $expectedRate)
    {
        $product = Product::factory()->create(['type' => $productType]);
        $this->assertEquals($expectedRate, $product->getVatRate());
    }

    public function dutchVatRatesProvider()
    {
        return [
            'standard_goods' => ['standard', 0.21],
            'food' => ['food', 0.09],
            'books' => ['books', 0.09],
            'export' => ['export', 0.00],
        ];
    }
}
```

### Testing Sequential Invoice Numbering

```php
public function test_invoice_numbers_are_sequential_without_gaps()
{
    $company = Company::factory()->create();
    $user = User::factory()->create();
    $user->companies()->attach($company, ['role' => 'admin']);

    // Create 10 invoices concurrently
    $invoiceNumbers = collect();

    for ($i = 0; $i < 10; $i++) {
        $this->actingAs($user)
            ->post('/api/invoices', [
                'client_id' => Client::factory()->create(['company_id' => $company->id])->id,
                'amount' => 100 * ($i + 1),
            ])
            ->assertCreated();

        $invoice = Invoice::latest()->first();
        $invoiceNumbers->push($invoice->sequence_number);
    }

    // Verify no gaps
    $this->assertEquals(
        range(1, 10),
        $invoiceNumbers->sort()->values()->toArray()
    );
}

public function test_invoice_numbering_uses_database_lock()
{
    // This test verifies race condition protection
    $company = Company::factory()->create();

    // Simulate concurrent requests
    $promises = [];
    for ($i = 0; $i < 5; $i++) {
        $promises[] = function() use ($company) {
            return InvoiceService::createInvoice($company, [/* data */]);
        };
    }

    // All should complete successfully with unique numbers
    $results = Promise::all($promises)->wait();
    $numbers = collect($results)->pluck('sequence_number');

    $this->assertCount(5, $numbers->unique());
}
```

### Testing Email Notifications

```php
use Illuminate\Support\Facades\Mail;
use App\Mail\InvoiceSent;

public function test_invoice_sent_email_is_queued()
{
    Mail::fake();

    $invoice = Invoice::factory()->create();

    $this->post("/api/invoices/{$invoice->id}/send");

    Mail::assertQueued(InvoiceSent::class, function ($mail) use ($invoice) {
        return $mail->invoice->id === $invoice->id;
    });
}

public function test_invoice_email_contains_correct_information()
{
    $invoice = Invoice::factory()->create([
        'number' => 'INV-2025-001',
        'total' => 1210.00
    ]);

    $mailable = new InvoiceSent($invoice);
    $mailable->assertSeeInHtml('INV-2025-001');
    $mailable->assertSeeInHtml('€ 1.210,00');
    $mailable->assertHasAttachment('invoice.pdf');
}
```

### Testing Background Jobs

```php
use Illuminate\Support\Facades\Queue;
use App\Jobs\ProcessVatDeclaration;

public function test_vat_declaration_job_is_dispatched()
{
    Queue::fake();

    $company = Company::factory()->create();

    $this->post('/api/vat-declarations', [
        'company_id' => $company->id,
        'period_start' => '2025-01-01',
        'period_end' => '2025-03-31',
    ]);

    Queue::assertPushed(ProcessVatDeclaration::class);
}

public function test_vat_declaration_job_processes_correctly()
{
    $company = Company::factory()->create();

    // Create test invoices
    Invoice::factory()->count(10)->create([
        'company_id' => $company->id,
        'vat_amount' => 210.00,
    ]);

    $job = new ProcessVatDeclaration($company, '2025-01-01', '2025-03-31');
    $job->handle();

    $declaration = VatDeclaration::where('company_id', $company->id)->first();
    $this->assertEquals(2100.00, $declaration->total_vat_owed);
}
```

## Security Testing

### Testing Authentication

```php
public function test_unauthenticated_users_cannot_access_api()
{
    $this->getJson('/api/invoices')
        ->assertUnauthorized();
}

public function test_sanctum_token_authentication_works()
{
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/invoices')
        ->assertOk();
}

public function test_expired_tokens_are_rejected()
{
    $user = User::factory()->create();
    $token = $user->createToken('test-token', ['*'], now()->subDay());

    $this->withHeader('Authorization', "Bearer {$token->plainTextToken}")
        ->getJson('/api/invoices')
        ->assertUnauthorized();
}
```

### Testing SQL Injection Prevention

```php
public function test_search_parameter_is_sql_injection_safe()
{
    $user = User::factory()->create();
    $company = Company::factory()->create();
    $user->companies()->attach($company);

    // Attempt SQL injection
    $maliciousInput = "'; DROP TABLE invoices; --";

    $this->actingAs($user)
        ->get('/api/invoices?search=' . urlencode($maliciousInput))
        ->assertOk();

    // Verify table still exists
    $this->assertTrue(Schema::hasTable('invoices'));
}
```

### Testing CSRF Protection

```php
public function test_csrf_protection_on_forms()
{
    $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

    $response = $this->post('/invoices', [/* data */]);
    $response->assertStatus(419); // CSRF token mismatch
}
```

### Testing XSS Prevention

```php
public function test_html_in_input_is_escaped()
{
    $user = User::factory()->create();
    $company = Company::factory()->create();
    $user->companies()->attach($company, ['role' => 'admin']);

    $xssAttempt = '<script>alert("XSS")</script>';

    $this->actingAs($user)
        ->post('/api/clients', [
            'name' => $xssAttempt,
            'company_id' => $company->id,
        ])
        ->assertCreated();

    $client = Client::latest()->first();

    // HTML should be escaped
    $this->assertStringContainsString('&lt;script&gt;', $client->name);
    $this->assertStringNotContainsString('<script>', $client->name);
}
```

## Performance Testing

### Testing Query Performance

```php
use Illuminate\Support\Facades\DB;

public function test_invoice_list_uses_eager_loading()
{
    $company = Company::factory()->create();
    Invoice::factory()->count(20)->create(['company_id' => $company->id]);

    DB::enableQueryLog();

    $this->actingAs(User::factory()->create())
        ->get('/api/invoices');

    $queries = DB::getQueryLog();

    // Should be 2-3 queries max (invoices, clients, items)
    $this->assertLessThan(5, count($queries));
}

public function test_large_dataset_pagination_performance()
{
    $company = Company::factory()->create();
    Invoice::factory()->count(1000)->create(['company_id' => $company->id]);

    $startTime = microtime(true);

    $this->get('/api/invoices?page=1&per_page=15');

    $executionTime = microtime(true) - $startTime;

    // Should complete in less than 1 second
    $this->assertLessThan(1.0, $executionTime);
}

public function test_index_usage_for_common_queries()
{
    $query = Invoice::where('company_id', 1)
        ->where('status', 'paid')
        ->toSql();

    $explain = DB::select("EXPLAIN {$query}");

    // Verify index is used, not full table scan
    $this->assertStringNotContainsString('ALL', $explain[0]->type);
}
```

## Troubleshooting Guide

### Problem 1: Tests Fail Due to Missing Database

**Symptoms:**
```
SQLSTATE[HY000] [1049] Unknown database 'testing'
```

**Solution:**
```bash
# Create test database
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS testing;"

# Or use SQLite for testing
# .env.testing
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
```

### Problem 2: Foreign Key Constraint Failures

**Symptoms:**
```
SQLSTATE[23000]: Integrity constraint violation: 1452 Cannot add or update a child row
```

**Solution:**
```php
// Ensure factories create related models
Invoice::factory()->create([
    'company_id' => Company::factory(),  // ✓ Creates company first
    'client_id' => Client::factory(),    // ✓ Creates client first
]);

// Or disable foreign key checks in test setup
protected function setUp(): void
{
    parent::setUp();
    DB::statement('SET FOREIGN_KEY_CHECKS=0');
}
```

### Problem 3: Flaky Tests (Sometimes Pass, Sometimes Fail)

**Causes:**
- Race conditions
- Date/time dependencies
- Random data generation
- External API calls

**Solutions:**
```php
// Use Carbon::setTestNow() for time-dependent tests
public function test_invoice_is_overdue()
{
    Carbon::setTestNow('2025-03-01');

    $invoice = Invoice::factory()->create([
        'due_date' => '2025-02-28',
    ]);

    $this->assertTrue($invoice->isOverdue());

    Carbon::setTestNow(); // Reset
}

// Mock external services
public function test_digipoort_submission()
{
    Http::fake([
        'digipoort.nl/*' => Http::response(['status' => 'success'], 200)
    ]);

    $result = DigipoortClient::submit($data);

    $this->assertTrue($result->success);
}
```

### Problem 4: Memory Exhaustion

**Symptoms:**
```
Fatal error: Allowed memory size exhausted
```

**Solution:**
```php
// Use chunk() instead of all()
public function test_process_large_dataset()
{
    Invoice::factory()->count(10000)->create();

    Invoice::chunk(100, function ($invoices) {
        foreach ($invoices as $invoice) {
            $this->assertNotNull($invoice->id);
        }
    });
}

// Or increase memory limit in phpunit.xml
<php>
    <ini name="memory_limit" value="512M"/>
</php>
```

### Problem 5: Slow Test Suite

**Solutions:**
```bash
# Run tests in parallel (Laravel 9+)
php artisan test --parallel

# Run specific test suite
php artisan test --testsuite=Unit  # Fast unit tests only

# Use SQLite in-memory for faster tests
# .env.testing
DB_CONNECTION=sqlite
DB_DATABASE=:memory:

# Optimize autoloader
composer dump-autoload --optimize
```

## Integration with Other Skills

### With `code-quality-standards`
```bash
# Run tests and quality checks together
./vendor/bin/pint --test && \
./vendor/bin/phpstan analyse && \
php artisan test --coverage --min=80
```

### With `database-migration-check`
```bash
# Verify migrations before running tests
php artisan migrate:status
php artisan test tests/Feature/MigrationTest.php
```

### With `permission-audit`
```bash
# Run permission tests specifically
php artisan test tests/Feature/Permissions/
```

## Test Coverage Best Practices

### Measuring Coverage

```bash
# Generate HTML coverage report
php artisan test --coverage-html coverage-report

# Set minimum coverage requirement
php artisan test --coverage --min=80

# Check specific paths
php artisan test --coverage --path=app/Services
```

### Coverage Targets

| Component | Minimum Coverage | Target Coverage |
|-----------|------------------|-----------------|
| Models | 90% | 95% |
| Controllers | 85% | 90% |
| Services | 90% | 95% |
| Policies | 95% | 100% |
| Helpers | 85% | 90% |
| Overall | 80% | 85% |

### What NOT to Test

- Framework code (Laravel internals)
- Third-party packages
- Simple getters/setters
- Database migrations (test via feature tests)
- Configuration files

## Pre-Commit Hook

Create `.git/hooks/pre-commit`:
```bash
#!/bin/bash
echo "🧪 Running tests..."

# Run fast unit tests first
php artisan test --testsuite=Unit --stop-on-failure

if [ $? -ne 0 ]; then
    echo "❌ Unit tests failed. Commit aborted."
    exit 1
fi

# Run feature tests
php artisan test --testsuite=Feature --stop-on-failure

if [ $? -ne 0 ]; then
    echo "❌ Feature tests failed. Commit aborted."
    exit 1
fi

# Check code coverage
php artisan test --coverage --min=80 > /dev/null 2>&1

if [ $? -ne 0 ]; then
    echo "⚠️  Warning: Code coverage below 80%"
    read -p "Continue anyway? (y/n) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        exit 1
    fi
fi

echo "✅ All tests passed!"
exit 0
```

Make executable:
```bash
chmod +x .git/hooks/pre-commit
```

## Test Data Management

### Using Factories Effectively

```php
// Define flexible factory states
class InvoiceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'client_id' => Client::factory(),
            'number' => $this->faker->unique()->numerify('INV-####'),
            'amount_ex_vat' => $this->faker->randomFloat(2, 100, 10000),
            'vat_rate' => 0.21,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => ['status' => 'draft']);
    }

    public function sent(): static
    {
        return $this->state(fn () => ['status' => 'sent', 'sent_at' => now()]);
    }

    public function paid(): static
    {
        return $this->state(fn () => ['status' => 'paid', 'paid_at' => now()]);
    }

    public function overdue(): static
    {
        return $this->state(fn () => [
            'status' => 'sent',
            'due_date' => now()->subDays(30),
        ]);
    }
}

// Usage in tests
$draftInvoice = Invoice::factory()->draft()->create();
$overdueInvoice = Invoice::factory()->overdue()->create();
```

### Seeders for Test Data

```php
// TestDataSeeder.php
class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::factory()->create(['name' => 'Test Company']);

        User::factory()
            ->count(5)
            ->create()
            ->each(fn($user) => $user->companies()->attach($company, [
                'role' => 'admin'
            ]));

        Client::factory()->count(20)->create(['company_id' => $company->id]);
        Invoice::factory()->count(50)->create(['company_id' => $company->id]);
    }
}

// Run in tests
protected function setUp(): void
{
    parent::setUp();
    $this->seed(TestDataSeeder::class);
}
```

## Continuous Integration Examples

### GitHub Actions

```yaml
name: Laravel Tests

on:
  push:
    branches: [ main, develop ]
  pull_request:
    branches: [ main, develop ]

jobs:
  test:
    runs-on: ubuntu-latest

    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: password
          MYSQL_DATABASE: testing
        ports:
          - 3306:3306
        options: >-
          --health-cmd="mysqladmin ping"
          --health-interval=10s
          --health-timeout=5s
          --health-retries=3

    steps:
      - uses: actions/checkout@v3

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
          extensions: mbstring, pdo, pdo_mysql, bcmath
          coverage: xdebug

      - name: Copy .env
        run: php -r "file_exists('.env') || copy('.env.testing', '.env');"

      - name: Install Dependencies
        run: composer install -q --no-ansi --no-interaction --no-scripts --no-progress --prefer-dist

      - name: Generate key
        run: php artisan key:generate

      - name: Directory Permissions
        run: chmod -R 777 storage bootstrap/cache

      - name: Run Migrations
        env:
          DB_CONNECTION: mysql
          DB_HOST: 127.0.0.1
          DB_PORT: 3306
          DB_DATABASE: testing
          DB_USERNAME: root
          DB_PASSWORD: password
        run: php artisan migrate --force

      - name: Execute tests (Unit and Feature tests) with coverage
        env:
          DB_CONNECTION: mysql
          DB_HOST: 127.0.0.1
          DB_PORT: 3306
          DB_DATABASE: testing
          DB_USERNAME: root
          DB_PASSWORD: password
        run: php artisan test --coverage --min=80

      - name: Upload coverage to Codecov
        uses: codecov/codecov-action@v3
        with:
          files: ./coverage.xml
```

### GitLab CI/CD

```yaml
image: php:8.2-fpm

stages:
  - test
  - deploy

variables:
  MYSQL_ROOT_PASSWORD: root
  MYSQL_DATABASE: testing
  MYSQL_USER: user
  MYSQL_PASSWORD: password

test:
  stage: test
  services:
    - mysql:8.0
  before_script:
    - apt-get update -yqq
    - apt-get install -yqq git libzip-dev libpng-dev
    - docker-php-ext-install pdo_mysql zip gd
    - curl -sS https://getcomposer.org/installer | php
    - php composer.phar install
    - cp .env.testing .env
    - php artisan key:generate
  script:
    - php artisan migrate --force
    - php artisan test --coverage --min=80
  coverage: '/^\s*Lines:\s*\d+.\d+\%/'
```

## Advanced Testing Patterns

### Testing API Rate Limiting

```php
public function test_api_rate_limiting()
{
    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    // Make 61 requests (limit is 60/minute)
    for ($i = 0; $i < 61; $i++) {
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/invoices');

        if ($i < 60) {
            $response->assertOk();
        } else {
            $response->assertStatus(429); // Too Many Requests
        }
    }
}
```

### Testing File Uploads

```php
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

public function test_invoice_pdf_upload()
{
    Storage::fake('invoices');

    $file = UploadedFile::fake()->create('invoice.pdf', 1000, 'application/pdf');

    $response = $this->actingAs(User::factory()->create())
        ->post('/api/invoices/upload', [
            'file' => $file,
        ]);

    $response->assertCreated();

    // Assert file was stored
    Storage::disk('invoices')->assertExists($file->hashName());
}

public function test_only_pdf_files_allowed()
{
    $file = UploadedFile::fake()->create('invoice.txt', 100, 'text/plain');

    $response = $this->actingAs(User::factory()->create())
        ->post('/api/invoices/upload', [
            'file' => $file,
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['file']);
}
```

### Testing Event Listeners

```php
use Illuminate\Support\Facades\Event;
use App\Events\InvoicePaid;
use App\Listeners\SendPaymentConfirmation;

public function test_payment_confirmation_sent_when_invoice_paid()
{
    Event::fake();

    $invoice = Invoice::factory()->create(['status' => 'sent']);

    $invoice->markAsPaid();

    Event::assertDispatched(InvoicePaid::class, function ($event) use ($invoice) {
        return $event->invoice->id === $invoice->id;
    });

    Event::assertListening(
        InvoicePaid::class,
        SendPaymentConfirmation::class
    );
}
```

## Test Organization Best Practices

### Directory Structure

```
tests/
├── Feature/
│   ├── Api/
│   │   ├── InvoiceApiTest.php
│   │   ├── ClientApiTest.php
│   │   └── ExpenseApiTest.php
│   ├── Permissions/
│   │   ├── InvoicePermissionsTest.php
│   │   └── PayrollPermissionsTest.php
│   ├── Compliance/
│   │   ├── VatDeclarationTest.php
│   │   └── DigipoortSubmissionTest.php
│   └── Integration/
│       ├── PaymentGatewayTest.php
│       └── EmailDeliveryTest.php
├── Unit/
│   ├── Models/
│   │   ├── InvoiceTest.php
│   │   └── ClientTest.php
│   ├── Services/
│   │   ├── VatCalculatorTest.php
│   │   └── InvoiceNumberGeneratorTest.php
│   └── Helpers/
│       └── CurrencyFormatterTest.php
└── TestCase.php
```

### Naming Conventions

```php
// ✅ GOOD: Descriptive test names
public function test_user_can_create_invoice_with_valid_data()
public function test_invoice_number_generation_prevents_duplicates()
public function test_vat_calculation_handles_rounding_correctly()

// ❌ BAD: Vague test names
public function testInvoice()
public function testCreate()
public function test1()
```

### Using Test Traits

```php
trait CreatesCompanyWithUser
{
    protected function createCompanyWithUser(string $role = 'admin'): array
    {
        $company = Company::factory()->create();
        $user = User::factory()->create();
        $user->companies()->attach($company, ['role' => $role]);

        return compact('company', 'user');
    }
}

// Use in tests
class InvoiceTest extends TestCase
{
    use CreatesCompanyWithUser;

    public function test_invoice_creation()
    {
        ['company' => $company, 'user' => $user] = $this->createCompanyWithUser();

        $this->actingAs($user)
            ->post('/api/invoices', [/* data */])
            ->assertCreated();
    }
}
```

## Pest PHP Alternative

### Converting PHPUnit to Pest

```php
// PHPUnit style
class InvoiceTest extends TestCase
{
    public function test_user_can_view_invoices()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/api/invoices')
            ->assertOk();
    }
}

// Pest style
it('allows users to view invoices', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/api/invoices')
        ->assertOk();
});

// With beforeEach
beforeEach(function () {
    $this->user = User::factory()->create();
});

it('allows users to view invoices')->actingAs($this->user)->get('/api/invoices')->assertOk();

// With datasets
it('calculates VAT correctly', function ($amount, $rate, $expected) {
    expect(calculateVat($amount, $rate))->toBe($expected);
})->with([
    [100, 0.21, 21],
    [100, 0.09, 9],
    [100, 0.00, 0],
]);
```

## Resources & Documentation

### Official Documentation
- [Laravel Testing](https://laravel.com/docs/testing) - Official Laravel testing guide
- [PHPUnit Documentation](https://phpunit.de/documentation.html) - PHPUnit manual
- [Pest PHP](https://pestphp.com/) - Modern PHP testing framework
- [Laravel Dusk](https://laravel.com/docs/dusk) - Browser testing

### Testing Tools
- [Laravel Debugbar](https://github.com/barryvdh/laravel-debugbar) - Debug toolbar
- [Laravel Telescope](https://laravel.com/docs/telescope) - Debugging assistant
- [Mockery](https://github.com/mockery/mockery) - Mocking framework
- [Faker](https://fakerphp.github.io/) - Fake data generator

### Best Practices
- [Test-Driven Development](https://martinfowler.com/bliki/TestDrivenDevelopment.html) - TDD principles
- [FIRST Principles](https://github.com/ghsukumar/SFDC_Best_Practices/wiki/F.I.R.S.T-Principles-of-Unit-Testing) - Fast, Independent, Repeatable, Self-validating, Timely
- [AAA Pattern](https://automationpanda.com/2020/07/07/arrange-act-assert-a-pattern-for-writing-good-tests/) - Arrange, Act, Assert

### Community Resources
- [Laravel News](https://laravel-news.com/) - Latest Laravel updates
- [Laracasts](https://laracasts.com/) - Video tutorials
- [Laravel Daily](https://laraveldaily.com/) - Daily tips and tricks

## Quick Reference

### Most Common Test Commands

```bash
# Run all tests
php artisan test

# Run with coverage
php artisan test --coverage

# Run specific file
php artisan test tests/Feature/InvoiceTest.php

# Run specific test method
php artisan test --filter=test_user_can_create_invoice

# Stop on first failure
php artisan test --stop-on-failure

# Parallel execution
php artisan test --parallel

# With memory limit
php artisan test --memory-limit=512M
```

### Common Assertions

```php
// HTTP Assertions
$response->assertOk();                    // 200
$response->assertCreated();               // 201
$response->assertNoContent();             // 204
$response->assertNotFound();              // 404
$response->assertForbidden();             // 403
$response->assertUnauthorized();          // 401

// JSON Assertions
$response->assertJson(['key' => 'value']);
$response->assertJsonStructure(['data' => ['id', 'name']]);
$response->assertJsonCount(10, 'data');
$response->assertJsonFragment(['name' => 'John']);
$response->assertJsonMissing(['deleted' => true]);

// Database Assertions
$this->assertDatabaseHas('invoices', ['id' => 1]);
$this->assertDatabaseMissing('invoices', ['deleted_at' => null]);
$this->assertDatabaseCount('invoices', 10);

// General Assertions
$this->assertTrue($condition);
$this->assertFalse($condition);
$this->assertEquals($expected, $actual);
$this->assertNotEquals($expected, $actual);
$this->assertNull($value);
$this->assertNotNull($value);
$this->assertEmpty($array);
$this->assertCount(5, $array);
```

## Final Checklist

### Before Committing
- [ ] All tests pass locally
- [ ] Code coverage meets minimum threshold (80%)
- [ ] No skipped or incomplete tests without justification
- [ ] Test names are descriptive
- [ ] Database migrations tested
- [ ] API endpoints tested
- [ ] Permission checks tested
- [ ] Edge cases covered

### Before Deploying
- [ ] All tests pass in CI/CD pipeline
- [ ] Feature tests cover main user flows
- [ ] Security tests pass
- [ ] Performance tests pass
- [ ] Email/notification tests pass
- [ ] Background job tests pass
- [ ] Multi-tenancy isolation verified

### Code Review Checklist
- [ ] Tests are readable and maintainable
- [ ] Tests follow AAA pattern
- [ ] Factories used instead of manual object creation
- [ ] No hardcoded test data
- [ ] Database is properly reset between tests
- [ ] External services are mocked
- [ ] Tests are independent (can run in any order)

---

**Version:** 2.0.0
**Last Updated:** December 2025
**Maintainer:** Development Team
