---
name: testing
description: Testing strategies, unit tests, integration tests, TDD, code coverage
version: 1.0.3
tags: [testing, unit-test, integration, tdd, quality, coverage]
trigger_keywords: [sk-testing, "write tests", "test coverage", "unit test", "integration test", "test strategy", "tdd approach", "phpunit test", "pest test", "mock dependency", "feature test", "test suite", "testing framework", "test automation"]
related_skills: [laravel-test-suite, code-quality-standards, laravel-ecosystem]
---
# Software Testing Expert

You are a senior QA engineer and testing expert who has mastered all testing methodologies for the Boekhouder application. You create comprehensive test suites, identify testing gaps, and ensure code quality through rigorous automated testing.

## Your Testing Expertise

### Testing Frameworks

#### PHP/Laravel Testing
- **PHPUnit 11.x**: Unit tests, assertions, data providers, mocking
- **Pest 3.x**: Modern expressive syntax, higher-order tests, architectural tests
- **Laravel Testing**: Feature tests, HTTP tests, database testing
- **Laravel Dusk**: Browser automation, JavaScript testing
- **Laravel Sanctum**: API authentication testing

#### JavaScript/Vue Testing
- **Vitest/Jest**: Unit testing for Vue components
- **Vue Test Utils**: Component mounting, DOM interaction
- **Cypress/Playwright**: E2E browser testing

#### Flutter/Dart Testing
- **flutter_test**: Widget tests, unit tests
- **integration_test**: Full app integration testing
- **mockito**: Mocking dependencies
- **bloc_test**: Testing BLoC patterns

### Database Testing
- **SQLite in-memory**: Fast test isolation
- **MySQL/MariaDB**: Production-like testing
- **Redis**: Cache and queue testing
- **Database transactions**: Test isolation with rollback

## PHPUnit Testing

### Configuration
```xml
<!-- phpunit.xml -->
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="./vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         colors="true">
    <testsuites>
        <testsuite name="Unit">
            <directory suffix="Test.php">./tests/Unit</directory>
        </testsuite>
        <testsuite name="Feature">
            <directory suffix="Test.php">./tests/Feature</directory>
        </testsuite>
    </testsuites>
    <coverage>
        <include>
            <directory suffix=".php">./app</directory>
        </include>
    </coverage>
    <php>
        <env name="APP_ENV" value="testing"/>
        <env name="DB_CONNECTION" value="sqlite"/>
        <env name="DB_DATABASE" value=":memory:"/>
        <env name="CACHE_DRIVER" value="array"/>
        <env name="SESSION_DRIVER" value="array"/>
        <env name="QUEUE_CONNECTION" value="sync"/>
    </php>
</phpunit>
```

### Unit Test Patterns
```php
<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\InvoiceCalculator;
use App\Models\Invoice;
use App\Models\InvoiceLine;

class InvoiceCalculatorTest extends TestCase
{
    private InvoiceCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new InvoiceCalculator();
    }

    /** @test */
    public function it_calculates_subtotal_correctly(): void
    {
        $lines = [
            new InvoiceLine(['quantity' => 2, 'unit_price' => 100.00]),
            new InvoiceLine(['quantity' => 3, 'unit_price' => 50.00]),
        ];

        $subtotal = $this->calculator->calculateSubtotal($lines);

        $this->assertEquals(350.00, $subtotal);
    }

    /** @test */
    public function it_calculates_vat_at_21_percent(): void
    {
        $subtotal = 100.00;
        $vatRate = 21;

        $vat = $this->calculator->calculateVat($subtotal, $vatRate);

        $this->assertEquals(21.00, $vat);
    }

    /**
     * @test
     * @dataProvider vatRateProvider
     */
    public function it_handles_different_vat_rates(float $subtotal, int $rate, float $expected): void
    {
        $vat = $this->calculator->calculateVat($subtotal, $rate);
        $this->assertEquals($expected, $vat);
    }

    public static function vatRateProvider(): array
    {
        return [
            'standard rate' => [100.00, 21, 21.00],
            'reduced rate' => [100.00, 9, 9.00],
            'zero rate' => [100.00, 0, 0.00],
            'with decimals' => [99.99, 21, 20.9979],
        ];
    }

    /** @test */
    public function it_throws_exception_for_negative_amounts(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Amount cannot be negative');

        $this->calculator->calculateVat(-100.00, 21);
    }
}
```

### Mocking Dependencies
```php
<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\PaymentProcessor;
use App\Contracts\PaymentGatewayInterface;
use App\Models\Payment;
use Mockery;

class PaymentProcessorTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_processes_payment_through_gateway(): void
    {
        // Arrange
        $gateway = Mockery::mock(PaymentGatewayInterface::class);
        $gateway->shouldReceive('charge')
            ->once()
            ->with(100.00, 'EUR', Mockery::type('string'))
            ->andReturn(['transaction_id' => 'txn_123', 'status' => 'success']);

        $processor = new PaymentProcessor($gateway);
        $payment = new Payment(['amount' => 100.00, 'currency' => 'EUR']);

        // Act
        $result = $processor->process($payment);

        // Assert
        $this->assertTrue($result->isSuccessful());
        $this->assertEquals('txn_123', $result->getTransactionId());
    }

    /** @test */
    public function it_handles_gateway_failure(): void
    {
        $gateway = Mockery::mock(PaymentGatewayInterface::class);
        $gateway->shouldReceive('charge')
            ->once()
            ->andThrow(new \App\Exceptions\PaymentFailedException('Insufficient funds'));

        $processor = new PaymentProcessor($gateway);
        $payment = new Payment(['amount' => 100.00, 'currency' => 'EUR']);

        $result = $processor->process($payment);

        $this->assertFalse($result->isSuccessful());
        $this->assertEquals('Insufficient funds', $result->getErrorMessage());
    }
}
```

## Pest Testing

### Modern Pest Syntax
```php
<?php

// tests/Feature/InvoiceTest.php

use App\Models\Invoice;
use App\Models\User;
use App\Models\Company;
use function Pest\Laravel\{actingAs, get, post, put, delete, assertDatabaseHas};

beforeEach(function () {
    $this->company = Company::factory()->create();
    $this->user = User::factory()->for($this->company)->create();
    $this->invoice = Invoice::factory()->for($this->company)->create();
});

describe('Invoice CRUD', function () {
    it('lists invoices for authenticated user', function () {
        actingAs($this->user)
            ->get('/api/invoices')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $this->invoice->id);
    });

    it('creates an invoice with valid data', function () {
        $invoiceData = [
            'customer_name' => 'Test Customer',
            'invoice_number' => 'INV-2025-001',
            'amount' => 1500.00,
            'vat_rate' => 21,
            'due_date' => now()->addDays(30)->toDateString(),
        ];

        actingAs($this->user)
            ->post('/api/invoices', $invoiceData)
            ->assertCreated()
            ->assertJsonPath('data.customer_name', 'Test Customer');

        assertDatabaseHas('invoices', [
            'company_id' => $this->company->id,
            'customer_name' => 'Test Customer',
        ]);
    });

    it('prevents creating invoice without authentication', function () {
        post('/api/invoices', ['customer_name' => 'Test'])
            ->assertUnauthorized();
    });

    it('validates required fields', function () {
        actingAs($this->user)
            ->post('/api/invoices', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['customer_name', 'invoice_number', 'amount']);
    });
});

describe('Multi-tenant isolation', function () {
    it('prevents access to another company invoices', function () {
        $otherCompany = Company::factory()->create();
        $otherInvoice = Invoice::factory()->for($otherCompany)->create();

        actingAs($this->user)
            ->get("/api/invoices/{$otherInvoice->id}")
            ->assertNotFound();
    });

    it('scopes invoice listing to user company', function () {
        $otherCompany = Company::factory()->create();
        Invoice::factory()->for($otherCompany)->count(5)->create();

        actingAs($this->user)
            ->get('/api/invoices')
            ->assertOk()
            ->assertJsonCount(1, 'data'); // Only own company invoice
    });
});
```

### Higher-Order Tests
```php
<?php

// Concise testing with higher-order syntax
it('has a valid invoice number format')
    ->expect(fn () => Invoice::factory()->create()->invoice_number)
    ->toMatch('/^INV-\d{4}-\d{3,}$/');

it('calculates total with VAT correctly')
    ->expect(fn () => (new Invoice(['amount' => 100, 'vat_rate' => 21]))->total)
    ->toBe(121.00);

it('cannot have negative amount')
    ->expect(fn () => Invoice::factory()->create(['amount' => -100]))
    ->toThrow(\Illuminate\Database\QueryException::class);
```

### Architectural Tests
```php
<?php

// tests/Architecture/ArchitectureTest.php

arch('controllers should not have public properties')
    ->expect('App\Http\Controllers')
    ->not->toHavePublicProperties();

arch('models should extend base model')
    ->expect('App\Models')
    ->toExtend('Illuminate\Database\Eloquent\Model');

arch('services should be final')
    ->expect('App\Services')
    ->toBeFinal();

arch('no debugging statements in production code')
    ->expect(['dd', 'dump', 'var_dump', 'print_r'])
    ->not->toBeUsed();

arch('controllers use form requests for validation')
    ->expect('App\Http\Controllers')
    ->toUseNothing()
    ->ignoring('App\Http\Requests');

arch('models should use soft deletes trait')
    ->expect('App\Models')
    ->toUseTrait('Illuminate\Database\Eloquent\SoftDeletes')
    ->ignoring(['App\Models\AuditLog', 'App\Models\Session']);
```

## Feature Testing (Laravel)

### HTTP Tests
```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Company;
use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class InvoiceApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;
    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::factory()->create();
        $this->user = User::factory()->for($this->company)->create();
    }

    /** @test */
    public function authenticated_user_can_list_invoices(): void
    {
        Invoice::factory()->for($this->company)->count(3)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/invoices');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'invoice_number', 'customer_name', 'amount', 'status']
                ],
                'meta' => ['current_page', 'total', 'per_page']
            ])
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function it_filters_invoices_by_status(): void
    {
        Invoice::factory()->for($this->company)->create(['status' => 'paid']);
        Invoice::factory()->for($this->company)->count(2)->create(['status' => 'pending']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/invoices?status=paid');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.status', 'paid');
    }

    /** @test */
    public function it_searches_invoices_by_customer_name(): void
    {
        Invoice::factory()->for($this->company)->create(['customer_name' => 'Acme Corp']);
        Invoice::factory()->for($this->company)->create(['customer_name' => 'Beta Inc']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/invoices?search=acme');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.customer_name', 'Acme Corp');
    }

    /** @test */
    public function it_paginates_results(): void
    {
        Invoice::factory()->for($this->company)->count(25)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/invoices?per_page=10');

        $response->assertOk()
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('meta.total', 25)
            ->assertJsonPath('meta.per_page', 10);
    }

    /** @test */
    public function it_creates_invoice_with_lines(): void
    {
        $data = [
            'customer_name' => 'Test Customer',
            'customer_email' => 'test@example.com',
            'invoice_number' => 'INV-2025-001',
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'lines' => [
                ['description' => 'Consulting', 'quantity' => 10, 'unit_price' => 150.00, 'vat_rate' => 21],
                ['description' => 'Development', 'quantity' => 20, 'unit_price' => 125.00, 'vat_rate' => 21],
            ],
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/invoices', $data);

        $response->assertCreated()
            ->assertJsonPath('data.customer_name', 'Test Customer')
            ->assertJsonCount(2, 'data.lines');

        $this->assertDatabaseHas('invoices', [
            'company_id' => $this->company->id,
            'customer_name' => 'Test Customer',
        ]);

        $this->assertDatabaseCount('invoice_lines', 2);
    }

    /** @test */
    public function it_validates_invoice_data(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/invoices', [
                'customer_name' => '', // Required
                'invoice_number' => 'INVALID', // Wrong format
                'due_date' => 'not-a-date',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'customer_name',
                'invoice_number',
                'due_date',
            ]);
    }

    /** @test */
    public function it_marks_invoice_as_paid(): void
    {
        $invoice = Invoice::factory()
            ->for($this->company)
            ->create(['status' => 'pending']);

        $response = $this->actingAs($this->user)
            ->postJson("/api/invoices/{$invoice->id}/mark-paid", [
                'payment_date' => now()->toDateString(),
                'payment_method' => 'bank_transfer',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.status', 'paid');

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'status' => 'paid',
        ]);
    }

    /** @test */
    public function it_sends_invoice_email(): void
    {
        Mail::fake();

        $invoice = Invoice::factory()->for($this->company)->create([
            'customer_email' => 'customer@example.com',
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/invoices/{$invoice->id}/send");

        $response->assertOk();

        Mail::assertSent(\App\Mail\InvoiceMail::class, function ($mail) {
            return $mail->hasTo('customer@example.com');
        });
    }

    /** @test */
    public function it_generates_pdf(): void
    {
        $invoice = Invoice::factory()->for($this->company)->create();

        $response = $this->actingAs($this->user)
            ->get("/api/invoices/{$invoice->id}/pdf");

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }
}
```

### Database Testing
```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Company;
use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InvoiceModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_generates_sequential_invoice_numbers(): void
    {
        $company = Company::factory()->create();

        $invoice1 = Invoice::factory()->for($company)->create();
        $invoice2 = Invoice::factory()->for($company)->create();
        $invoice3 = Invoice::factory()->for($company)->create();

        $this->assertMatchesRegularExpression('/^INV-\d{4}-001$/', $invoice1->invoice_number);
        $this->assertMatchesRegularExpression('/^INV-\d{4}-002$/', $invoice2->invoice_number);
        $this->assertMatchesRegularExpression('/^INV-\d{4}-003$/', $invoice3->invoice_number);
    }

    /** @test */
    public function it_soft_deletes_invoices(): void
    {
        $invoice = Invoice::factory()->create();

        $invoice->delete();

        $this->assertSoftDeleted('invoices', ['id' => $invoice->id]);
        $this->assertDatabaseHas('invoices', ['id' => $invoice->id]);
    }

    /** @test */
    public function it_cascades_delete_to_lines(): void
    {
        $invoice = Invoice::factory()
            ->has(\App\Models\InvoiceLine::factory()->count(3), 'lines')
            ->create();

        $invoice->forceDelete();

        $this->assertDatabaseMissing('invoices', ['id' => $invoice->id]);
        $this->assertDatabaseCount('invoice_lines', 0);
    }

    /** @test */
    public function it_calculates_totals_automatically(): void
    {
        $invoice = Invoice::factory()->create();
        $invoice->lines()->createMany([
            ['description' => 'Item 1', 'quantity' => 2, 'unit_price' => 100, 'vat_rate' => 21],
            ['description' => 'Item 2', 'quantity' => 1, 'unit_price' => 50, 'vat_rate' => 21],
        ]);

        $invoice->refresh();

        $this->assertEquals(250.00, $invoice->subtotal);
        $this->assertEquals(52.50, $invoice->vat_amount);
        $this->assertEquals(302.50, $invoice->total);
    }
}
```

### Testing Events and Listeners
```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Invoice;
use App\Events\InvoiceCreated;
use App\Events\InvoicePaid;
use App\Listeners\SendInvoiceNotification;
use App\Listeners\UpdateAccountingRecords;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InvoiceEventsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_dispatches_invoice_created_event(): void
    {
        Event::fake([InvoiceCreated::class]);

        $invoice = Invoice::factory()->create();

        Event::assertDispatched(InvoiceCreated::class, function ($event) use ($invoice) {
            return $event->invoice->id === $invoice->id;
        });
    }

    /** @test */
    public function it_dispatches_invoice_paid_event_when_marked_paid(): void
    {
        Event::fake([InvoicePaid::class]);

        $invoice = Invoice::factory()->create(['status' => 'pending']);
        $invoice->markAsPaid();

        Event::assertDispatched(InvoicePaid::class);
    }

    /** @test */
    public function invoice_created_triggers_notification_listener(): void
    {
        Event::fake();

        $invoice = Invoice::factory()->create();

        Event::assertListening(
            InvoiceCreated::class,
            SendInvoiceNotification::class
        );
    }
}
```

### Testing Jobs and Queues
```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Jobs\ProcessInvoicePayment;
use App\Jobs\GenerateMonthlyReport;
use App\Models\Invoice;
use App\Models\Company;
use Illuminate\Support\Facades\Queue;
use Illuminate\Foundation\Testing\RefreshDatabase;

class JobsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_queues_payment_processing_job(): void
    {
        Queue::fake();

        $invoice = Invoice::factory()->create();

        ProcessInvoicePayment::dispatch($invoice);

        Queue::assertPushed(ProcessInvoicePayment::class, function ($job) use ($invoice) {
            return $job->invoice->id === $invoice->id;
        });
    }

    /** @test */
    public function payment_job_updates_invoice_status(): void
    {
        $invoice = Invoice::factory()->create(['status' => 'pending']);

        $job = new ProcessInvoicePayment($invoice);
        $job->handle();

        $this->assertEquals('paid', $invoice->fresh()->status);
    }

    /** @test */
    public function monthly_report_job_generates_pdf(): void
    {
        Storage::fake('reports');

        $company = Company::factory()->create();
        Invoice::factory()->for($company)->count(10)->create();

        $job = new GenerateMonthlyReport($company, now());
        $job->handle();

        Storage::disk('reports')->assertExists(
            "monthly/{$company->id}/" . now()->format('Y-m') . '.pdf'
        );
    }
}
```

## Flutter Testing

### Unit Tests
```dart
// test/unit/invoice_calculator_test.dart
import 'package:flutter_test/flutter_test.dart';
import 'package:boekhouder/services/invoice_calculator.dart';
import 'package:boekhouder/models/invoice_line.dart';

void main() {
  late InvoiceCalculator calculator;

  setUp(() {
    calculator = InvoiceCalculator();
  });

  group('InvoiceCalculator', () {
    test('calculates subtotal correctly', () {
      final lines = [
        InvoiceLine(quantity: 2, unitPrice: 100.0),
        InvoiceLine(quantity: 3, unitPrice: 50.0),
      ];

      expect(calculator.calculateSubtotal(lines), equals(350.0));
    });

    test('calculates VAT at 21%', () {
      expect(calculator.calculateVat(100.0, 21), equals(21.0));
    });

    test('calculates total with VAT', () {
      expect(calculator.calculateTotal(100.0, 21.0), equals(121.0));
    });

    test('handles zero amount', () {
      expect(calculator.calculateVat(0.0, 21), equals(0.0));
    });

    test('throws for negative amount', () {
      expect(
        () => calculator.calculateVat(-100.0, 21),
        throwsArgumentError,
      );
    });
  });

  group('VAT rate validation', () {
    test('accepts standard Dutch VAT rates', () {
      expect(calculator.isValidVatRate(21), isTrue); // Standard
      expect(calculator.isValidVatRate(9), isTrue);  // Reduced
      expect(calculator.isValidVatRate(0), isTrue);  // Zero
    });

    test('rejects invalid VAT rates', () {
      expect(calculator.isValidVatRate(-1), isFalse);
      expect(calculator.isValidVatRate(100), isFalse);
    });
  });
}
```

### Widget Tests
```dart
// test/widget/invoice_card_test.dart
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:boekhouder/widgets/invoice_card.dart';
import 'package:boekhouder/models/invoice.dart';

void main() {
  group('InvoiceCard', () {
    late Invoice testInvoice;

    setUp(() {
      testInvoice = Invoice(
        id: '1',
        invoiceNumber: 'INV-2025-001',
        customerName: 'Test Customer',
        amount: 1500.00,
        status: InvoiceStatus.pending,
        dueDate: DateTime.now().add(const Duration(days: 30)),
      );
    });

    testWidgets('displays invoice information', (tester) async {
      await tester.pumpWidget(
        MaterialApp(
          home: Scaffold(
            body: InvoiceCard(invoice: testInvoice),
          ),
        ),
      );

      expect(find.text('INV-2025-001'), findsOneWidget);
      expect(find.text('Test Customer'), findsOneWidget);
      expect(find.text('€1,500.00'), findsOneWidget);
    });

    testWidgets('shows pending status chip', (tester) async {
      await tester.pumpWidget(
        MaterialApp(
          home: Scaffold(
            body: InvoiceCard(invoice: testInvoice),
          ),
        ),
      );

      expect(find.text('Pending'), findsOneWidget);

      final chip = tester.widget<Chip>(find.byType(Chip));
      expect(chip.backgroundColor, equals(Colors.orange));
    });

    testWidgets('shows paid status with green color', (tester) async {
      testInvoice = testInvoice.copyWith(status: InvoiceStatus.paid);

      await tester.pumpWidget(
        MaterialApp(
          home: Scaffold(
            body: InvoiceCard(invoice: testInvoice),
          ),
        ),
      );

      expect(find.text('Paid'), findsOneWidget);

      final chip = tester.widget<Chip>(find.byType(Chip));
      expect(chip.backgroundColor, equals(Colors.green));
    });

    testWidgets('triggers onTap callback', (tester) async {
      bool tapped = false;

      await tester.pumpWidget(
        MaterialApp(
          home: Scaffold(
            body: InvoiceCard(
              invoice: testInvoice,
              onTap: () => tapped = true,
            ),
          ),
        ),
      );

      await tester.tap(find.byType(InvoiceCard));
      expect(tapped, isTrue);
    });

    testWidgets('shows overdue warning for past due invoices', (tester) async {
      testInvoice = testInvoice.copyWith(
        dueDate: DateTime.now().subtract(const Duration(days: 1)),
      );

      await tester.pumpWidget(
        MaterialApp(
          home: Scaffold(
            body: InvoiceCard(invoice: testInvoice),
          ),
        ),
      );

      expect(find.byIcon(Icons.warning), findsOneWidget);
      expect(find.text('Overdue'), findsOneWidget);
    });
  });
}
```

### Screen/Integration Widget Tests
```dart
// test/widget/invoice_list_screen_test.dart
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:mockito/mockito.dart';
import 'package:mockito/annotations.dart';
import 'package:boekhouder/screens/invoice_list_screen.dart';
import 'package:boekhouder/blocs/invoice/invoice_bloc.dart';
import 'package:boekhouder/models/invoice.dart';

@GenerateMocks([InvoiceBloc])
import 'invoice_list_screen_test.mocks.dart';

void main() {
  late MockInvoiceBloc mockBloc;

  setUp(() {
    mockBloc = MockInvoiceBloc();
  });

  Widget createTestWidget() {
    return MaterialApp(
      home: BlocProvider<InvoiceBloc>.value(
        value: mockBloc,
        child: const InvoiceListScreen(),
      ),
    );
  }

  group('InvoiceListScreen', () {
    testWidgets('shows loading indicator when loading', (tester) async {
      when(mockBloc.state).thenReturn(InvoiceLoading());
      whenListen(mockBloc, Stream.value(InvoiceLoading()));

      await tester.pumpWidget(createTestWidget());

      expect(find.byType(CircularProgressIndicator), findsOneWidget);
    });

    testWidgets('shows invoice list when loaded', (tester) async {
      final invoices = [
        Invoice(id: '1', invoiceNumber: 'INV-001', customerName: 'Customer 1', amount: 100),
        Invoice(id: '2', invoiceNumber: 'INV-002', customerName: 'Customer 2', amount: 200),
      ];

      when(mockBloc.state).thenReturn(InvoiceLoaded(invoices: invoices));

      await tester.pumpWidget(createTestWidget());
      await tester.pump();

      expect(find.byType(InvoiceCard), findsNWidgets(2));
      expect(find.text('INV-001'), findsOneWidget);
      expect(find.text('INV-002'), findsOneWidget);
    });

    testWidgets('shows empty state when no invoices', (tester) async {
      when(mockBloc.state).thenReturn(InvoiceLoaded(invoices: []));

      await tester.pumpWidget(createTestWidget());

      expect(find.text('No invoices yet'), findsOneWidget);
      expect(find.text('Create your first invoice'), findsOneWidget);
    });

    testWidgets('shows error message on failure', (tester) async {
      when(mockBloc.state).thenReturn(InvoiceError(message: 'Failed to load'));

      await tester.pumpWidget(createTestWidget());

      expect(find.text('Failed to load'), findsOneWidget);
      expect(find.text('Retry'), findsOneWidget);
    });

    testWidgets('refresh triggers reload', (tester) async {
      when(mockBloc.state).thenReturn(InvoiceLoaded(invoices: []));

      await tester.pumpWidget(createTestWidget());

      await tester.fling(find.byType(RefreshIndicator), const Offset(0, 300), 1000);
      await tester.pumpAndSettle();

      verify(mockBloc.add(LoadInvoices())).called(1);
    });

    testWidgets('FAB navigates to create screen', (tester) async {
      when(mockBloc.state).thenReturn(InvoiceLoaded(invoices: []));

      await tester.pumpWidget(createTestWidget());

      await tester.tap(find.byType(FloatingActionButton));
      await tester.pumpAndSettle();

      // Verify navigation occurred
      expect(find.byType(CreateInvoiceScreen), findsOneWidget);
    });
  });
}
```

### BLoC Tests
```dart
// test/bloc/invoice_bloc_test.dart
import 'package:flutter_test/flutter_test.dart';
import 'package:bloc_test/bloc_test.dart';
import 'package:mockito/mockito.dart';
import 'package:mockito/annotations.dart';
import 'package:boekhouder/blocs/invoice/invoice_bloc.dart';
import 'package:boekhouder/repositories/invoice_repository.dart';
import 'package:boekhouder/models/invoice.dart';

@GenerateMocks([InvoiceRepository])
import 'invoice_bloc_test.mocks.dart';

void main() {
  late MockInvoiceRepository mockRepository;
  late InvoiceBloc bloc;

  setUp(() {
    mockRepository = MockInvoiceRepository();
    bloc = InvoiceBloc(repository: mockRepository);
  });

  tearDown(() {
    bloc.close();
  });

  group('InvoiceBloc', () {
    final testInvoices = [
      Invoice(id: '1', invoiceNumber: 'INV-001', customerName: 'Test', amount: 100),
    ];

    blocTest<InvoiceBloc, InvoiceState>(
      'emits [Loading, Loaded] when LoadInvoices succeeds',
      build: () {
        when(mockRepository.getInvoices()).thenAnswer((_) async => testInvoices);
        return bloc;
      },
      act: (bloc) => bloc.add(LoadInvoices()),
      expect: () => [
        InvoiceLoading(),
        InvoiceLoaded(invoices: testInvoices),
      ],
    );

    blocTest<InvoiceBloc, InvoiceState>(
      'emits [Loading, Error] when LoadInvoices fails',
      build: () {
        when(mockRepository.getInvoices()).thenThrow(Exception('Network error'));
        return bloc;
      },
      act: (bloc) => bloc.add(LoadInvoices()),
      expect: () => [
        InvoiceLoading(),
        isA<InvoiceError>(),
      ],
    );

    blocTest<InvoiceBloc, InvoiceState>(
      'adds new invoice to existing list',
      build: () {
        when(mockRepository.createInvoice(any)).thenAnswer((_) async =>
          Invoice(id: '2', invoiceNumber: 'INV-002', customerName: 'New', amount: 200)
        );
        return bloc;
      },
      seed: () => InvoiceLoaded(invoices: testInvoices),
      act: (bloc) => bloc.add(CreateInvoice(customerName: 'New', amount: 200)),
      expect: () => [
        isA<InvoiceLoaded>().having(
          (s) => s.invoices.length,
          'invoice count',
          2,
        ),
      ],
    );

    blocTest<InvoiceBloc, InvoiceState>(
      'filters invoices by status',
      build: () => bloc,
      seed: () => InvoiceLoaded(invoices: [
        Invoice(id: '1', status: InvoiceStatus.paid, customerName: 'A', amount: 100),
        Invoice(id: '2', status: InvoiceStatus.pending, customerName: 'B', amount: 200),
        Invoice(id: '3', status: InvoiceStatus.paid, customerName: 'C', amount: 300),
      ]),
      act: (bloc) => bloc.add(FilterByStatus(InvoiceStatus.paid)),
      expect: () => [
        isA<InvoiceLoaded>().having(
          (s) => s.filteredInvoices.length,
          'filtered count',
          2,
        ),
      ],
    );
  });
}
```

### Integration Tests
```dart
// integration_test/invoice_flow_test.dart
import 'package:flutter_test/flutter_test.dart';
import 'package:integration_test/integration_test.dart';
import 'package:boekhouder/main.dart' as app;

void main() {
  IntegrationTestWidgetsFlutterBinding.ensureInitialized();

  group('Invoice Flow', () {
    testWidgets('complete invoice creation flow', (tester) async {
      app.main();
      await tester.pumpAndSettle();

      // Login first
      await tester.enterText(find.byKey(Key('email_field')), 'test@example.com');
      await tester.enterText(find.byKey(Key('password_field')), 'password123');
      await tester.tap(find.byKey(Key('login_button')));
      await tester.pumpAndSettle();

      // Navigate to invoices
      await tester.tap(find.text('Invoices'));
      await tester.pumpAndSettle();

      // Tap FAB to create new invoice
      await tester.tap(find.byType(FloatingActionButton));
      await tester.pumpAndSettle();

      // Fill invoice form
      await tester.enterText(find.byKey(Key('customer_name')), 'Test Customer');
      await tester.enterText(find.byKey(Key('customer_email')), 'customer@test.com');

      // Add invoice line
      await tester.tap(find.text('Add Line'));
      await tester.pumpAndSettle();

      await tester.enterText(find.byKey(Key('line_description_0')), 'Consulting');
      await tester.enterText(find.byKey(Key('line_quantity_0')), '10');
      await tester.enterText(find.byKey(Key('line_price_0')), '150');

      // Save invoice
      await tester.tap(find.text('Save Invoice'));
      await tester.pumpAndSettle();

      // Verify invoice appears in list
      expect(find.text('Test Customer'), findsOneWidget);
      expect(find.text('€1,815.00'), findsOneWidget); // 1500 + 21% VAT
    });

    testWidgets('mark invoice as paid', (tester) async {
      app.main();
      await tester.pumpAndSettle();

      // Assuming already logged in, navigate to invoice
      await tester.tap(find.text('Invoices'));
      await tester.pumpAndSettle();

      // Tap on first invoice
      await tester.tap(find.byType(InvoiceCard).first);
      await tester.pumpAndSettle();

      // Tap mark as paid
      await tester.tap(find.text('Mark as Paid'));
      await tester.pumpAndSettle();

      // Confirm
      await tester.tap(find.text('Confirm'));
      await tester.pumpAndSettle();

      // Verify status changed
      expect(find.text('Paid'), findsOneWidget);
    });
  });
}
```

## Security/Penetration Testing

### OWASP Top 10 Testing

#### A01: Broken Access Control
```bash
# Test IDOR (Insecure Direct Object Reference)
curl -X GET "https://app.com/api/invoices/123" -H "Authorization: Bearer $TOKEN"
curl -X GET "https://app.com/api/invoices/124" -H "Authorization: Bearer $TOKEN"

# Test multi-tenant bypass
curl -X GET "https://app.com/api/companies/2/invoices" -H "Authorization: Bearer $COMPANY1_TOKEN"

# Test privilege escalation
curl -X PUT "https://app.com/api/users/me" -d '{"role": "admin"}'
```

#### Laravel Code Audit Points
```php
// ❌ VULNERABLE: No authorization check
public function show($id) {
    return Invoice::find($id);
}

// ✅ SAFE: With policy
public function show($id) {
    $invoice = Invoice::findOrFail($id);
    $this->authorize('view', $invoice);
    return $invoice;
}
```

#### A03: Injection Testing
```bash
# SQL Injection
curl "https://app.com/api/invoices?search=test' OR '1'='1"
curl "https://app.com/api/invoices?sort=name; DROP TABLE invoices;--"

# XSS Testing
curl -X POST "https://app.com/api/invoices" \
  -d '{"customer_name": "<script>alert(1)</script>"}'
```

#### Authentication Testing
```bash
# Brute force (should be rate limited)
for i in {1..100}; do
  curl -X POST "https://app.com/api/login" -d '{"email": "admin@test.com", "password": "attempt'$i'"}'
done

# Password policy
curl -X POST "https://app.com/api/register" -d '{"password": "123"}'
```

### Security Test Automation
```php
<?php

namespace Tests\Security;

use Tests\TestCase;
use App\Models\User;
use App\Models\Company;
use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthorizationSecurityTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_cannot_access_other_company_invoices(): void
    {
        $company1 = Company::factory()->create();
        $company2 = Company::factory()->create();

        $user1 = User::factory()->for($company1)->create();
        $invoice2 = Invoice::factory()->for($company2)->create();

        $response = $this->actingAs($user1)
            ->getJson("/api/invoices/{$invoice2->id}");

        $response->assertNotFound();
    }

    /** @test */
    public function user_cannot_modify_other_company_data(): void
    {
        $company1 = Company::factory()->create();
        $company2 = Company::factory()->create();

        $user1 = User::factory()->for($company1)->create();
        $invoice2 = Invoice::factory()->for($company2)->create();

        $response = $this->actingAs($user1)
            ->putJson("/api/invoices/{$invoice2->id}", [
                'customer_name' => 'Hacked',
            ]);

        $response->assertNotFound();
        $this->assertDatabaseMissing('invoices', [
            'id' => $invoice2->id,
            'customer_name' => 'Hacked',
        ]);
    }

    /** @test */
    public function mass_assignment_is_protected(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $response = $this->actingAs($user)
            ->putJson("/api/users/{$user->id}", [
                'name' => 'Updated Name',
                'is_admin' => true, // Should be ignored
            ]);

        $user->refresh();
        $this->assertFalse($user->is_admin);
    }

    /** @test */
    public function sql_injection_is_prevented(): void
    {
        $user = User::factory()->create();
        Invoice::factory()->for($user->company)->count(3)->create();

        $response = $this->actingAs($user)
            ->getJson("/api/invoices?search=' OR '1'='1");

        // Should not return all invoices, just those matching search
        $response->assertOk();
        $this->assertLessThanOrEqual(3, count($response->json('data')));
    }

    /** @test */
    public function xss_is_prevented(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/invoices', [
                'customer_name' => '<script>alert("xss")</script>',
                'invoice_number' => 'INV-001',
                'amount' => 100,
            ]);

        // Output should be escaped or sanitized
        $invoice = $response->json('data');
        $this->assertStringNotContainsString('<script>', $invoice['customer_name']);
    }

    /** @test */
    public function rate_limiting_works(): void
    {
        $responses = collect();

        for ($i = 0; $i < 70; $i++) {
            $responses->push(
                $this->postJson('/api/login', [
                    'email' => 'test@test.com',
                    'password' => 'wrong',
                ])
            );
        }

        // Should have at least one 429 response
        $this->assertTrue(
            $responses->contains(fn($r) => $r->status() === 429)
        );
    }
}
```

## Test Commands Reference

### Running Tests
```bash
# PHPUnit
php artisan test                          # Run all tests
php artisan test --filter=InvoiceTest     # Run specific test
php artisan test --testsuite=Unit         # Run unit tests only
php artisan test --testsuite=Feature      # Run feature tests only
php artisan test --parallel               # Run in parallel
php artisan test --coverage               # With coverage

# Pest
./vendor/bin/pest                         # Run all tests
./vendor/bin/pest --filter="invoice"      # Filter tests
./vendor/bin/pest --parallel              # Parallel execution
./vendor/bin/pest --coverage              # With coverage
./vendor/bin/pest --type-coverage         # Type coverage

# Flutter
flutter test                              # Run all tests
flutter test test/unit/                   # Run unit tests
flutter test test/widget/                 # Run widget tests
flutter test --coverage                   # With coverage
flutter test integration_test/            # Integration tests

# Combined test script
composer test                             # Run PHP tests
npm run test                              # Run JS tests
flutter test                              # Run Dart tests
```

### Coverage Thresholds
```xml
<!-- phpunit.xml -->
<coverage>
    <report>
        <clover outputFile="coverage/clover.xml"/>
        <html outputDirectory="coverage/html"/>
    </report>
</coverage>
```

```bash
# Check coverage thresholds
php artisan test --coverage --min=80
./vendor/bin/pest --coverage --min=80
```

## Test Report Format

```markdown
# Test Execution Report

**Date:** [Date]
**Tester:** Software Testing Expert
**Scope:** [Components tested]

## Summary

| Category | Total | Passed | Failed | Skipped |
|----------|-------|--------|--------|---------|
| Unit Tests | X | X | X | X |
| Feature Tests | X | X | X | X |
| Widget Tests | X | X | X | X |
| Integration | X | X | X | X |
| Security | X | X | X | X |
| **Total** | **X** | **X** | **X** | **X** |

## Coverage

| Package/Module | Line % | Branch % | Function % |
|----------------|--------|----------|------------|
| app/Models | X% | X% | X% |
| app/Services | X% | X% | X% |
| app/Http | X% | X% | X% |

## Failed Tests

### TEST-001: [Test Name]
- **File:** tests/Feature/InvoiceTest.php:45
- **Error:** [Error message]
- **Expected:** [Expected result]
- **Actual:** [Actual result]

## Recommendations

1. [Missing test coverage areas]
2. [Flaky tests to fix]
3. [Test improvements needed]
```

---

## CI/CD Integration for Testing

### GitHub Actions Configuration

**Complete CI/CD Pipeline for Bookkeeping App**:
```yaml
# .github/workflows/tests.yml
name: Tests

on:
  push:
    branches: [ main, develop ]
  pull_request:
    branches: [ main, develop ]

jobs:
  phpunit:
    runs-on: ubuntu-latest

    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: password
          MYSQL_DATABASE: testing
        ports:
          - 3306:3306
        options: --health-cmd="mysqladmin ping" --health-interval=10s --health-timeout=5s --health-retries=3

      redis:
        image: redis:alpine
        ports:
          - 6379:6379
        options: --health-cmd="redis-cli ping" --health-interval=10s --health-timeout=5s --health-retries=3

    steps:
      - uses: actions/checkout@v3

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
          extensions: mbstring, xml, ctype, json, bcmath, pdo_mysql, redis
          coverage: xdebug

      - name: Copy .env
        run: php -r "file_exists('.env') || copy('.env.testing', '.env');"

      - name: Install Dependencies
        run: composer install --prefer-dist --no-interaction --no-progress

      - name: Generate key
        run: php artisan key:generate

      - name: Run Migrations
        run: php artisan migrate --force
        env:
          DB_CONNECTION: mysql
          DB_HOST: 127.0.0.1
          DB_PORT: 3306
          DB_DATABASE: testing
          DB_USERNAME: root
          DB_PASSWORD: password

      - name: Run PHPUnit Tests
        run: vendor/bin/phpunit --coverage-clover=coverage.xml
        env:
          DB_CONNECTION: mysql
          DB_HOST: 127.0.0.1
          DB_PORT: 3306
          DB_DATABASE: testing
          DB_USERNAME: root
          DB_PASSWORD: password
          REDIS_HOST: 127.0.0.1
          REDIS_PORT: 6379

      - name: Run Pest Tests
        run: vendor/bin/pest --coverage --min=80

      - name: Upload Coverage to Codecov
        uses: codecov/codecov-action@v3
        with:
          files: ./coverage.xml
          flags: backend
          fail_ci_if_error: true

  flutter-tests:
    runs-on: ubuntu-latest

    steps:
      - uses: actions/checkout@v3

      - name: Setup Flutter
        uses: subosito/flutter-action@v2
        with:
          flutter-version: '3.16.0'
          channel: 'stable'

      - name: Install Dependencies
        run: flutter pub get
        working-directory: ./mobile

      - name: Run Analyzer
        run: flutter analyze
        working-directory: ./mobile

      - name: Run Tests
        run: flutter test --coverage
        working-directory: ./mobile

      - name: Upload Coverage
        uses: codecov/codecov-action@v3
        with:
          files: ./mobile/coverage/lcov.info
          flags: frontend
          fail_ci_if_error: true

  integration-tests:
    runs-on: ubuntu-latest
    needs: [phpunit, flutter-tests]

    steps:
      - uses: actions/checkout@v3

      - name: Setup PHP and Services
        # ... setup steps

      - name: Setup Flutter
        # ... setup steps

      - name: Run E2E Tests
        run: flutter drive --driver=test_driver/integration_test.dart --target=integration_test/app_test.dart
        working-directory: ./mobile

  security-scan:
    runs-on: ubuntu-latest

    steps:
      - uses: actions/checkout@v3

      - name: Run Security Checker
        run: |
          composer require --dev enlightn/security-checker
          vendor/bin/security-checker security:check

      - name: Run PHPStan
        run: |
          composer require --dev phpstan/phpstan
          vendor/bin/phpstan analyse app --level=8

      - name: Snyk Security Scan
        uses: snyk/actions/php@master
        env:
          SNYK_TOKEN: ${{ secrets.SNYK_TOKEN }}
```

### GitLab CI Configuration

```yaml
# .gitlab-ci.yml
stages:
  - test
  - security
  - deploy

variables:
  MYSQL_ROOT_PASSWORD: password
  MYSQL_DATABASE: testing

.php-template: &php-template
  image: php:8.2
  services:
    - mysql:8.0
    - redis:alpine
  before_script:
    - apt-get update && apt-get install -y git unzip
    - docker-php-ext-install pdo pdo_mysql bcmath
    - curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
    - composer install --prefer-dist --no-interaction --no-progress
    - cp .env.testing .env
    - php artisan key:generate
    - php artisan migrate --force

phpunit:
  <<: *php-template
  stage: test
  script:
    - vendor/bin/phpunit --coverage-text --colors=never
  coverage: '/^\s*Lines:\s*\d+.\d+\%/'
  artifacts:
    reports:
      coverage_report:
        coverage_format: cobertura
        path: coverage.xml

pest:
  <<: *php-template
  stage: test
  script:
    - vendor/bin/pest --coverage --min=80

flutter:
  image: ghcr.io/cirruslabs/flutter:stable
  stage: test
  script:
    - cd mobile
    - flutter pub get
    - flutter test --coverage
    - flutter analyze
  coverage: '/lines\.*: \d+\.\d+/'
  artifacts:
    paths:
      - mobile/coverage/

security:
  <<: *php-template
  stage: security
  script:
    - composer audit
    - vendor/bin/security-checker security:check
  allow_failure: false
```

---

## Test Data Factories & Seeders

### Advanced Factory Patterns

**Bookkeeping-Specific Factories**:
```php
<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        $subtotal = $this->faker->randomFloat(2, 100, 10000);
        $vatRate = $this->faker->randomElement([0, 0.09, 0.21]);
        $vatAmount = round($subtotal * $vatRate, 2);

        return [
            'company_id' => Company::factory(),
            'invoice_number' => $this->generateInvoiceNumber(),
            'customer_name' => $this->faker->company,
            'customer_email' => $this->faker->companyEmail,
            'invoice_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'due_date' => $this->faker->dateTimeBetween('now', '+60 days'),
            'subtotal' => $subtotal,
            'vat_rate' => $vatRate,
            'vat_amount' => $vatAmount,
            'total' => $subtotal + $vatAmount,
            'status' => $this->faker->randomElement(['draft', 'sent', 'paid', 'overdue']),
            'notes' => $this->faker->optional()->sentence,
        ];
    }

    /**
     * State: Paid invoice
     */
    public function paid(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paid',
            'paid_at' => $this->faker->dateTimeBetween($attributes['invoice_date'], 'now'),
            'payment_method' => $this->faker->randomElement(['bank_transfer', 'ideal', 'cash']),
        ]);
    }

    /**
     * State: Overdue invoice
     */
    public function overdue(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'overdue',
            'due_date' => $this->faker->dateTimeBetween('-60 days', '-1 day'),
            'invoice_date' => $this->faker->dateTimeBetween('-90 days', '-61 days'),
        ]);
    }

    /**
     * State: With invoice lines
     */
    public function withLines(int $count = 3): self
    {
        return $this->has(
            \App\Models\InvoiceLine::factory()->count($count),
            'lines'
        );
    }

    /**
     * State: Dutch company customer
     */
    public function dutchCustomer(): self
    {
        return $this->state(fn (array $attributes) => [
            'customer_name' => $this->faker->company . ' B.V.',
            'customer_kvk' => $this->faker->numerify('########'),
            'customer_btw' => $this->generateDutchVAT(),
            'customer_address' => $this->faker->streetAddress,
            'customer_city' => $this->faker->city,
            'customer_postcode' => $this->faker->postcode,
            'customer_country' => 'NL',
        ]);
    }

    /**
     * Generate realistic Dutch invoice number
     */
    private function generateInvoiceNumber(): string
    {
        $year = $this->faker->year('-1 year');
        $sequence = $this->faker->numberBetween(1, 999);
        return sprintf('INV-%d-%03d', $year, $sequence);
    }

    /**
     * Generate valid Dutch VAT number
     */
    private function generateDutchVAT(): string
    {
        return 'NL' . $this->faker->numerify('#########') . 'B' . $this->faker->numerify('##');
    }
}
```

### Seeder for Realistic Test Data

```php
<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use App\Models\Invoice;
use App\Models\Expense;
use Illuminate\Database\Seeder;

class TestDataSeeder extends Seeder
{
    /**
     * Seed realistic bookkeeping data for testing
     */
    public function run(): void
    {
        // Create a test company
        $company = Company::factory()->create([
            'name' => 'Test Boekhouding B.V.',
            'kvk_number' => '12345678',
            'btw_number' => 'NL123456789B01',
        ]);

        // Create users with different roles
        $admin = User::factory()->for($company)->create([
            'name' => 'Admin User',
            'email' => 'admin@test.nl',
            'role' => 'admin',
        ]);

        $accountant = User::factory()->for($company)->create([
            'name' => 'Accountant User',
            'email' => 'accountant@test.nl',
            'role' => 'accountant',
        ]);

        // Create realistic invoices
        Invoice::factory()
            ->for($company)
            ->count(20)
            ->paid()
            ->withLines(3)
            ->dutchCustomer()
            ->create();

        Invoice::factory()
            ->for($company)
            ->count(10)
            ->state(['status' => 'sent'])
            ->withLines(2)
            ->dutchCustomer()
            ->create();

        Invoice::factory()
            ->for($company)
            ->count(5)
            ->overdue()
            ->withLines(4)
            ->dutchCustomer()
            ->create();

        // Create expenses
        Expense::factory()
            ->for($company)
            ->count(30)
            ->create();

        $this->command->info('Test data seeded successfully!');
    }
}
```

---

## Performance Testing

### Load Testing with Laravel

**API Endpoint Load Test**:
```php
<?php

namespace Tests\Performance;

use Tests\TestCase;
use App\Models\User;
use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class InvoiceApiPerformanceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_handles_100_concurrent_invoice_requests(): void
    {
        $user = User::factory()->create();
        Invoice::factory()->count(1000)->for($user->company)->create();

        $startTime = microtime(true);
        $requests = 0;

        // Simulate 100 concurrent requests
        for ($i = 0; $i < 100; $i++) {
            $response = $this->actingAs($user)->getJson('/api/invoices');
            $response->assertOk();
            $requests++;
        }

        $duration = microtime(true) - $startTime;
        $rps = $requests / $duration;

        // Assert performance threshold
        $this->assertGreaterThan(50, $rps, "API should handle at least 50 req/s, got {$rps}");
        $this->assertLessThan(2, $duration / 100, "Average response time should be under 2s");
    }

    /** @test */
    public function it_performs_bulk_invoice_creation_efficiently(): void
    {
        $user = User::factory()->create();

        $startTime = microtime(true);

        // Create 100 invoices
        Invoice::factory()->count(100)->for($user->company)->create();

        $duration = microtime(true) - $startTime;

        // Should complete in under 5 seconds
        $this->assertLessThan(5, $duration, "Bulk creation took {$duration}s, should be under 5s");
    }

    /** @test */
    public function it_handles_complex_queries_efficiently(): void
    {
        $user = User::factory()->create();
        Invoice::factory()->count(5000)->for($user->company)->create();

        $startTime = microtime(true);

        // Complex aggregation query
        $stats = Invoice::where('company_id', $user->company_id)
            ->selectRaw('
                COUNT(*) as total_invoices,
                SUM(total) as total_revenue,
                AVG(total) as average_invoice,
                COUNT(CASE WHEN status = "paid" THEN 1 END) as paid_count,
                COUNT(CASE WHEN status = "overdue" THEN 1 END) as overdue_count
            ')
            ->first();

        $duration = microtime(true) - $startTime;

        $this->assertLessThan(0.5, $duration, "Complex query took {$duration}s, should be under 0.5s");
        $this->assertNotNull($stats);
    }

    /** @test */
    public function database_queries_are_optimized(): void
    {
        $user = User::factory()->create();
        Invoice::factory()->count(100)->for($user->company)->create();

        // Enable query logging
        DB::enableQueryLog();

        $this->actingAs($user)->getJson('/api/invoices');

        $queries = DB::getQueryLog();
        $queryCount = count($queries);

        // Should not have N+1 query problem
        $this->assertLessThan(10, $queryCount, "Too many queries: {$queryCount}, check for N+1 problems");
    }
}
```

### Memory Profiling

```php
<?php

namespace Tests\Performance;

use Tests\TestCase;
use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MemoryProfileTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_does_not_leak_memory_during_bulk_operations(): void
    {
        $startMemory = memory_get_usage();

        // Process 1000 invoices
        Invoice::factory()->count(1000)->create();

        // Force garbage collection
        gc_collect_cycles();

        $endMemory = memory_get_usage();
        $memoryIncrease = ($endMemory - $startMemory) / 1024 / 1024; // MB

        // Memory increase should be reasonable (< 50MB)
        $this->assertLessThan(50, $memoryIncrease,
            "Memory increased by {$memoryIncrease}MB, check for memory leaks");
    }

    /** @test */
    public function chunked_processing_uses_less_memory(): void
    {
        Invoice::factory()->count(10000)->create();

        $memoryBefore = memory_get_usage();

        // Process in chunks
        Invoice::chunk(100, function ($invoices) {
            foreach ($invoices as $invoice) {
                // Process invoice
            }
        });

        $memoryAfter = memory_get_usage();
        $memoryUsed = ($memoryAfter - $memoryBefore) / 1024 / 1024;

        // Should use less than 20MB
        $this->assertLessThan(20, $memoryUsed,
            "Chunked processing used {$memoryUsed}MB, should be under 20MB");
    }
}
```

---

## Mutation Testing

### Infection PHP for Mutation Testing

**Configuration**:
```json
{
    "$schema": "vendor/infection/infection/resources/schema.json",
    "source": {
        "directories": [
            "app"
        ],
        "excludes": [
            "Console/Kernel.php",
            "Exceptions/Handler.php",
            "Providers"
        ]
    },
    "logs": {
        "text": "infection.log",
        "html": "infection.html",
        "badge": {
            "branch": "main"
        }
    },
    "mutators": {
        "@default": true,
        "global-ignoreSourceCodeByRegex": [
            ".*test.*"
        ]
    },
    "phpUnit": {
        "configDir": ".",
        "customPath": "vendor/bin/phpunit"
    },
    "minMsi": 80,
    "minCoveredMsi": 85
}
```

**Running Mutation Tests**:
```bash
# Install Infection
composer require --dev infection/infection

# Run mutation testing
vendor/bin/infection --threads=4 --min-msi=80

# Run on specific files
vendor/bin/infection --filter=app/Services/InvoiceService.php

# Generate HTML report
vendor/bin/infection --logger-html=infection-report.html
```

**Example: Testing Invoice Calculator Mutations**:
```php
<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\InvoiceCalculator;

class InvoiceCalculatorMutationTest extends TestCase
{
    /**
     * This test ensures mutations like:
     * - Changing + to -
     * - Changing * to /
     * - Changing comparison operators
     * - Removing return statements
     * will be caught
     */
    public function test_vat_calculation_catches_all_mutations(): void
    {
        $calculator = new InvoiceCalculator();

        // Test boundary conditions
        $this->assertEquals(0, $calculator->calculateVat(0, 21));
        $this->assertEquals(21, $calculator->calculateVat(100, 21));
        $this->assertEquals(9, $calculator->calculateVat(100, 9));

        // Test floating point precision
        $this->assertEquals(20.9979, $calculator->calculateVat(99.99, 21));

        // Test edge cases
        $this->assertEquals(0.21, $calculator->calculateVat(1, 21));
        $this->assertEquals(2100, $calculator->calculateVat(10000, 21));

        // Test that mutations changing operators are caught
        $result = $calculator->calculateVat(100, 21);
        $this->assertGreaterThan(20, $result);
        $this->assertLessThan(22, $result);
        $this->assertEquals(21, $result);
    }
}
```

---

## Database Testing Strategies

### Transaction Testing

**Ensuring Database Integrity**:
```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class DatabaseTransactionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_rolls_back_on_invoice_line_failure(): void
    {
        $initialCount = Invoice::count();

        try {
            DB::transaction(function () {
                $invoice = Invoice::factory()->create();

                // Simulate failure when creating lines
                throw new \Exception('Line creation failed');
            });
        } catch (\Exception $e) {
            // Expected to fail
        }

        // Invoice should be rolled back
        $this->assertEquals($initialCount, Invoice::count());
    }

    /** @test */
    public function it_maintains_referential_integrity(): void
    {
        $invoice = Invoice::factory()->create();
        InvoiceLine::factory()->count(3)->for($invoice)->create();

        // Attempt to delete invoice (should be prevented by foreign key)
        $this->expectException(\Exception::class);

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        $invoice->forceDelete();
    }

    /** @test */
    public function concurrent_updates_handle_correctly(): void
    {
        $invoice = Invoice::factory()->create(['status' => 'draft']);

        // Simulate two concurrent update attempts
        $invoice1 = Invoice::find($invoice->id);
        $invoice2 = Invoice::find($invoice->id);

        $invoice1->status = 'sent';
        $invoice1->save();

        $invoice2->status = 'paid';
        $invoice2->save();

        // Last write wins
        $this->assertEquals('paid', $invoice->fresh()->status);
    }
}
```

### Database Seeding for Tests

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;

class SeededDatabaseTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed database before each test
        Artisan::call('db:seed', ['--class' => 'TestDataSeeder']);
    }

    /** @test */
    public function seeded_data_provides_realistic_test_environment(): void
    {
        // Should have test company with users and invoices
        $this->assertDatabaseCount('companies', 1);
        $this->assertDatabaseCount('users', 2);
        $this->assertDatabaseCount('invoices', 35); // 20 paid + 10 sent + 5 overdue
    }
}
```

---

## Troubleshooting Advanced Test Issues

### Problem 7: Flaky Tests Due to Time Dependencies

**Symptoms**: Tests pass/fail inconsistently based on time of day

**Solution**:
```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Date;
use Carbon\Carbon;

class TimeBasedTest extends TestCase
{
    /** @test */
    public function invoice_overdue_calculation_is_consistent(): void
    {
        // ✅ GOOD: Mock current time
        Date::setTestNow(Carbon::parse('2025-01-15 12:00:00'));

        $invoice = Invoice::factory()->create([
            'due_date' => Carbon::parse('2025-01-10'),
        ]);

        $this->assertTrue($invoice->isOverdue());

        // Cleanup
        Date::setTestNow();
    }

    /** @test */
    public function vat_quarter_calculation_handles_edge_cases(): void
    {
        // Test quarter boundaries
        $dates = [
            '2025-03-31' => 'Q1',
            '2025-04-01' => 'Q2',
            '2025-06-30' => 'Q2',
            '2025-07-01' => 'Q3',
        ];

        foreach ($dates as $date => $expectedQuarter) {
            Date::setTestNow(Carbon::parse($date));
            $quarter = app(VatService::class)->getCurrentQuarter();
            $this->assertEquals($expectedQuarter, $quarter, "Failed for date: {$date}");
        }

        Date::setTestNow();
    }
}
```

### Problem 8: Tests Failing in CI But Passing Locally

**Symptoms**: Different behavior between local and CI environment

**Solution**:
```php
<?php

// Create environment-specific test configuration

// phpunit.ci.xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="./vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         colors="true">
    <php>
        <env name="APP_ENV" value="testing"/>
        <env name="DB_CONNECTION" value="mysql"/>
        <env name="DB_HOST" value="127.0.0.1"/>
        <env name="DB_PORT" value="3306"/>
        <env name="DB_DATABASE" value="testing"/>
        <env name="DB_USERNAME" value="root"/>
        <env name="DB_PASSWORD" value="password"/>
        <env name="CACHE_DRIVER" value="redis"/>
        <env name="REDIS_HOST" value="127.0.0.1"/>
        <env name="REDIS_PORT" value="6379"/>
        <env name="QUEUE_CONNECTION" value="sync"/>
    </php>
</phpunit>

// Run in CI:
// php artisan test --configuration=phpunit.ci.xml
```

### Problem 9: Database Test Pollution

**Symptoms**: Tests affect each other, inconsistent results

**Solution**:
```php
<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure clean state
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        $tables = ['invoices', 'invoice_lines', 'expenses', 'payments'];
        foreach ($tables as $table) {
            DB::table($table)->truncate();
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // Reset Redis
        \Illuminate\Support\Facades\Redis::flushdb();

        // Clear cache
        \Illuminate\Support\Facades\Cache::flush();
    }

    protected function tearDown(): void
    {
        // Additional cleanup if needed
        parent::tearDown();
    }
}
```

---

## Testing Best Practices Checklist

### Pre-Implementation Checklist
- [ ] Test requirements clearly defined
- [ ] Test data factories created
- [ ] Database seeders prepared
- [ ] Mock services identified
- [ ] Testing environment configured
- [ ] CI/CD pipeline set up

### Implementation Checklist
- [ ] Unit tests cover all business logic
- [ ] Feature tests cover all API endpoints
- [ ] Integration tests cover critical flows
- [ ] Security tests verify authorization
- [ ] Performance tests establish baselines
- [ ] Tests follow AAA pattern (Arrange, Act, Assert)
- [ ] Test names are descriptive
- [ ] Edge cases are tested
- [ ] Error conditions are tested
- [ ] Mocks are used appropriately

### Post-Implementation Checklist
- [ ] All tests passing
- [ ] Code coverage > 80%
- [ ] Mutation score > 80%
- [ ] No flaky tests
- [ ] Performance benchmarks met
- [ ] Security scans passed
- [ ] CI/CD pipeline green
- [ ] Test documentation updated

---

## Integration with Project Workflow

### When to Use This Skill

- Before writing any production code (TDD)
- When adding new features
- When refactoring existing code
- Before deploying to production
- When investigating bugs
- During code reviews
- For performance optimization
- When ensuring security compliance

### Related Skills
- **backend-api**: Test API endpoints, authentication, authorization
- **laravel-ecosystem**: Test package integrations, middleware
- **flutter-dart-expert**: Test mobile app logic, state management
- **security-expert**: Test for vulnerabilities, authorization bypasses
- **database-expert**: Test queries, transactions, data integrity

---

**Version 2.0.0** - Enhanced with CI/CD integration, advanced factories, performance testing, mutation testing, database strategies, and comprehensive troubleshooting
