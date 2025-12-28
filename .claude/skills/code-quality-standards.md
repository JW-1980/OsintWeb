---
name: code-quality-standards
description: Enforce Laravel code quality standards with Pint, PHPStan, and best practices
version: 1.0.0
tags: [code-quality, standards, pint, phpstan, static-analysis, lint]
trigger_keywords: [quality, standards, lint, pint, phpstan, review, clean code]
related_skills: [testing-expert, laravel-ecosystem]
---

# Code Quality Standards

This skill enforces code quality standards for the Laravel bookkeeping application.

## When to Use

- Before committing code
- During code review
- Before creating pull requests
- When refactoring
- Setting up CI/CD pipelines

## Tools Configuration

### 1. Laravel Pint (Code Style)

Laravel Pint enforces PSR-12 coding style.

#### Install
```bash
cd bookkeeping-app
composer require laravel/pint --dev
```

#### Configuration

Create `pint.json`:
```json
{
    "preset": "laravel",
    "rules": {
        "array_syntax": {
            "syntax": "short"
        },
        "blank_line_before_statement": true,
        "no_unused_imports": true,
        "ordered_imports": {
            "sort_algorithm": "alpha"
        },
        "not_operator_with_successor_space": true
    }
}
```

#### Run Pint
```bash
# Check without fixing
./vendor/bin/pint --test

# Fix all issues
./vendor/bin/pint

# Fix specific directory
./vendor/bin/pint app/Http/Controllers
```

### 2. PHPStan (Static Analysis)

PHPStan finds bugs without running code.

#### Install
```bash
composer require --dev phpstan/phpstan
composer require --dev larastan/larastan
```

#### Configuration

Create `phpstan.neon`:
```neon
includes:
    - ./vendor/larastan/larastan/extension.neon

parameters:
    paths:
        - app
        - database
        - routes
        - config

    level: 6

    ignoreErrors:
        - '#Unsafe usage of new static#'

    excludePaths:
        - app/Exceptions/Handler.php
        - database/migrations/*

    checkMissingIterableValueType: false
```

#### Run PHPStan
```bash
./vendor/bin/phpstan analyse

# With memory limit
./vendor/bin/phpstan analyse --memory-limit=2G

# Specific path
./vendor/bin/phpstan analyse app/Services
```

### 3. PHP Code Sniffer (Additional Checks)

```bash
composer require --dev squizlabs/php_codesniffer

# Check
./vendor/bin/phpcs --standard=PSR12 app/

# Fix
./vendor/bin/phpcbf --standard=PSR12 app/
```

## Laravel Best Practices

### 1. Controller Structure

```php
// ✅ GOOD - Thin controller with clear responsibility
class InvoiceController extends Controller
{
    public function __construct(
        private InvoiceService $invoiceService
    ) {
        $this->middleware('auth');
        $this->authorizeResource(Invoice::class);
    }

    public function store(StoreInvoiceRequest $request): RedirectResponse
    {
        $invoice = $this->invoiceService->create(
            auth()->user(),
            auth()->user()->getCurrentCompany(),
            $request->validated()
        );

        return redirect()
            ->route('invoices.show', $invoice)
            ->with('success', 'Invoice created successfully');
    }
}

// ❌ BAD - Fat controller with business logic
class InvoiceController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->all();
        $invoice = new Invoice();
        $invoice->number = $this->generateNumber();
        $invoice->company_id = auth()->user()->company_id;
        // ... lots of business logic ...
        $invoice->save();
        return back();
    }
}
```

### 2. Service Layer Pattern

```php
// ✅ GOOD - Service handles business logic
namespace App\Services;

class InvoiceService
{
    public function create(User $user, Company $company, array $data): Invoice
    {
        return DB::transaction(function () use ($user, $company, $data) {
            $invoice = Invoice::create([
                'company_id' => $company->id,
                'number' => $this->generateInvoiceNumber($company),
                'client_id' => $data['client_id'],
                'amount' => $data['amount'],
                'vat_rate' => $data['vat_rate'],
                'vat_amount' => $this->calculateVat($data['amount'], $data['vat_rate']),
            ]);

            $this->createInvoiceItems($invoice, $data['items']);
            $this->notifyClient($invoice);

            return $invoice;
        });
    }
}
```

### 3. Request Validation

```php
// ✅ GOOD - Form Request class
namespace App\Http\Requests;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Invoice::class);
    }

    public function rules(): array
    {
        return [
            'client_id' => 'required|exists:clients,id',
            'amount' => 'required|numeric|min:0',
            'vat_rate' => 'required|in:0,9,21',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:500',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'client_id.required' => 'Selecteer een klant',
            'amount.min' => 'Bedrag moet minimaal €0 zijn',
        ];
    }
}
```

### 4. Model Structure

```php
// ✅ GOOD - Well-structured model
namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasCompanyScope, SoftDeletes;

    protected $fillable = [
        'company_id',
        'client_id',
        'number',
        'amount',
        'vat_amount',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'invoice_date' => 'date',
        'due_date' => 'date',
        'paid_at' => 'datetime',
    ];

    protected $appends = [
        'total_amount',
        'is_overdue',
    ];

    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    // Accessors
    public function getTotalAmountAttribute(): float
    {
        return $this->amount + $this->vat_amount;
    }

    public function getIsOverdueAttribute(): bool
    {
        return !$this->paid_at && $this->due_date < now();
    }

    // Scopes
    public function scopeUnpaid($query)
    {
        return $query->whereNull('paid_at');
    }

    public function scopeOverdue($query)
    {
        return $query->whereNull('paid_at')
            ->where('due_date', '<', now());
    }
}
```

## Code Quality Checks

### 1. Type Hints

```php
// ✅ GOOD - Proper type hints
public function calculateTotal(int $amount, float $vatRate): float
{
    return $amount * (1 + $vatRate);
}

// ❌ BAD - No type hints
public function calculateTotal($amount, $vatRate)
{
    return $amount * (1 + $vatRate);
}
```

### 2. Return Types

```php
// ✅ GOOD - Explicit return type
public function getInvoices(Company $company): Collection
{
    return Invoice::where('company_id', $company->id)->get();
}

// ❌ BAD - No return type
public function getInvoices($company)
{
    return Invoice::where('company_id', $company->id)->get();
}
```

### 3. Nullable Types

```php
// ✅ GOOD - Nullable indicated
public function findInvoice(int $id): ?Invoice
{
    return Invoice::find($id);
}

// ❌ BAD - Doesn't indicate null return
public function findInvoice(int $id): Invoice
{
    return Invoice::find($id);  // Could return null!
}
```

### 4. Collections vs Arrays

```php
// ✅ GOOD - Use Laravel collections
public function getActiveInvoices(): Collection
{
    return Invoice::where('status', 'active')->get();
}

// ❌ BAD - Using arrays unnecessarily
public function getActiveInvoices(): array
{
    return Invoice::where('status', 'active')->get()->toArray();
}
```

## Naming Conventions

### Controllers
```php
// ✅ Singular, suffix "Controller"
InvoiceController
ClientController
PaymentController
```

### Models
```php
// ✅ Singular
Invoice
Client
Payment
```

### Database Tables
```php
// ✅ Plural, snake_case
invoices
clients
payment_transactions
```

### Migrations
```php
// ✅ Descriptive action
2025_11_01_create_invoices_table.php
2025_11_15_add_status_to_payments_table.php
```

### Methods
```php
// ✅ camelCase, descriptive
public function calculateVatAmount()
public function sendInvoiceEmail()
public function markAsPaid()
```

### Variables
```php
// ✅ camelCase, descriptive
$invoiceAmount
$vatRate
$clientEmail
```

## Performance Best Practices

### 1. N+1 Query Prevention

```php
// ✅ GOOD - Eager loading
$invoices = Invoice::with(['client', 'items'])->get();

// ❌ BAD - N+1 queries
$invoices = Invoice::all();
foreach ($invoices as $invoice) {
    echo $invoice->client->name;  // Triggers query for each invoice
}
```

### 2. Chunking Large Datasets

```php
// ✅ GOOD - Process in chunks
Invoice::where('status', 'sent')
    ->chunk(100, function ($invoices) {
        foreach ($invoices as $invoice) {
            $this->processInvoice($invoice);
        }
    });

// ❌ BAD - Load all at once
$invoices = Invoice::where('status', 'sent')->get();  // Could be 10,000 rows!
```

### 3. Select Only Needed Columns

```php
// ✅ GOOD - Select specific columns
$invoices = Invoice::select(['id', 'number', 'amount'])->get();

// ❌ BAD - Select all columns
$invoices = Invoice::all();  // Loads all columns
```

## Pre-Commit Hook

Create `.git/hooks/pre-commit`:

```bash
#!/bin/bash

echo "🔍 Running code quality checks..."

# Run Pint
echo "Running Laravel Pint..."
./vendor/bin/pint --test
if [ $? -ne 0 ]; then
    echo "❌ Laravel Pint found issues. Run: ./vendor/bin/pint"
    exit 1
fi

# Run PHPStan
echo "Running PHPStan..."
./vendor/bin/phpstan analyse --error-format=table --no-progress
if [ $? -ne 0 ]; then
    echo "❌ PHPStan found issues"
    exit 1
fi

# Run tests
echo "Running tests..."
php artisan test --stop-on-failure
if [ $? -ne 0 ]; then
    echo "❌ Tests failed"
    exit 1
fi

echo "✅ All checks passed!"
exit 0
```

Make executable:
```bash
chmod +x .git/hooks/pre-commit
```

## CI/CD Integration

### GitHub Actions

```yaml
name: Code Quality

on: [push, pull_request]

jobs:
  code-quality:
    runs-on: ubuntu-latest

    steps:
      - uses: actions/checkout@v3

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'

      - name: Install dependencies
        run: composer install --prefer-dist --no-progress

      - name: Run Pint
        run: ./vendor/bin/pint --test

      - name: Run PHPStan
        run: ./vendor/bin/phpstan analyse

      - name: Run tests
        run: php artisan test --coverage --min=80
```

## Quick Commands

```bash
# Run all quality checks
composer quality

# Run Pint
composer pint

# Run PHPStan
composer phpstan

# Run tests
composer test

# Full suite
composer ci
```

Add to `composer.json`:

```json
{
    "scripts": {
        "pint": "./vendor/bin/pint",
        "pint:test": "./vendor/bin/pint --test",
        "phpstan": "./vendor/bin/phpstan analyse",
        "test": "php artisan test",
        "quality": [
            "@pint:test",
            "@phpstan",
            "@test"
        ],
        "ci": [
            "@quality"
        ]
    }
}
```

---

## PSR Standards Compliance

### PSR-12: Extended Coding Style Guide

**Key Requirements:**
```php
<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Client;
use Illuminate\Support\Collection;

/**
 * Service for managing invoice operations
 */
class InvoiceService
{
    // Properties (visibility required)
    private const MAX_ITEMS_PER_INVOICE = 50;

    private InvoiceCalculator $calculator;

    /**
     * Constructor with dependency injection
     */
    public function __construct(InvoiceCalculator $calculator)
    {
        $this->calculator = $calculator;
    }

    /**
     * Create a new invoice with VAT calculation
     *
     * @param Client $client
     * @param array<int, array{description: string, quantity: int, price: float}> $items
     * @return Invoice
     */
    public function createInvoice(Client $client, array $items): Invoice
    {
        // Opening brace on new line for methods
        if (count($items) > self::MAX_ITEMS_PER_INVOICE) {
            throw new \InvalidArgumentException(
                'Invoice cannot have more than ' . self::MAX_ITEMS_PER_INVOICE . ' items'
            );
        }

        $subtotal = $this->calculator->calculateSubtotal($items);
        $vatAmount = $this->calculator->calculateVat($subtotal, $client->vat_rate);

        return Invoice::create([
            'client_id' => $client->id,
            'subtotal' => $subtotal,
            'vat_amount' => $vatAmount,
            'total' => $subtotal + $vatAmount,
        ]);
    }
}
```

### PSR-4: Autoloading Standard

**Directory Structure:**
```
app/
├── Http/
│   ├── Controllers/      # App\Http\Controllers
│   ├── Requests/         # App\Http\Requests
│   └── Middleware/       # App\Http\Middleware
├── Models/               # App\Models
├── Services/             # App\Services
├── Repositories/         # App\Repositories
└── Traits/               # App\Traits
```

**Namespace Rules:**
```php
// ✅ GOOD: Matches directory structure
namespace App\Services\Accounting;  // app/Services/Accounting/
class JournalEntryService { }

// ❌ BAD: Doesn't match directory
namespace App\Services;  // But file is in app/Services/Accounting/
class JournalEntryService { }
```

### PSR-3: Logger Interface

```php
// ✅ GOOD: Use PSR-3 compliant logging
use Psr\Log\LoggerInterface;

class VatDeclarationService
{
    public function __construct(
        private LoggerInterface $logger
    ) {}

    public function submitToDigipoort(VatDeclaration $declaration): void
    {
        $this->logger->info('Submitting VAT declaration to Digipoort', [
            'declaration_id' => $declaration->id,
            'company_id' => $declaration->company_id,
            'period' => $declaration->period,
        ]);

        try {
            $response = $this->digipoortClient->submit($declaration);

            $this->logger->notice('VAT declaration submitted successfully', [
                'transaction_id' => $response->transactionId,
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to submit VAT declaration', [
                'error' => $e->getMessage(),
                'declaration_id' => $declaration->id,
            ]);

            throw $e;
        }
    }
}
```

## Best Practices

### 1. Single Responsibility Principle
```php
// ✅ GOOD: Each class has one responsibility
class InvoiceNumberGenerator
{
    public function generate(Company $company): string
    {
        $year = now()->year;
        $sequence = Invoice::where('company_id', $company->id)
            ->whereYear('created_at', $year)
            ->count() + 1;

        return sprintf('INV-%04d-%04d', $year, $sequence);
    }
}

class InvoicePdfGenerator
{
    public function generate(Invoice $invoice): string
    {
        return PDF::loadView('invoices.pdf', ['invoice' => $invoice])
            ->save(storage_path("invoices/{$invoice->number}.pdf"));
    }
}

// ❌ BAD: God class doing everything
class InvoiceManager
{
    public function createInvoice() { }
    public function generatePdf() { }
    public function sendEmail() { }
    public function calculateVat() { }
    public function generateNumber() { }
}
```

### 2. Dependency Injection
```php
// ✅ GOOD: Constructor injection
class InvoiceController extends Controller
{
    public function __construct(
        private InvoiceService $invoiceService,
        private VatCalculator $vatCalculator
    ) {}

    public function store(StoreInvoiceRequest $request)
    {
        $invoice = $this->invoiceService->create($request->validated());
        return redirect()->route('invoices.show', $invoice);
    }
}

// ❌ BAD: Direct instantiation
class InvoiceController extends Controller
{
    public function store(Request $request)
    {
        $service = new InvoiceService();  // Hard to test
        $invoice = $service->create($request->all());
        return redirect()->route('invoices.show', $invoice);
    }
}
```

### 3. Explicit Return Types
```php
// ✅ GOOD: All return types declared
public function calculateVat(float $amount, int $rate): float
{
    return $amount * ($rate / 100);
}

public function findInvoice(int $id): ?Invoice
{
    return Invoice::find($id);
}

public function getInvoices(Company $company): Collection
{
    return Invoice::where('company_id', $company->id)->get();
}

// ❌ BAD: No return types
public function calculateVat($amount, $rate)
{
    return $amount * ($rate / 100);
}
```

### 4. Early Returns
```php
// ✅ GOOD: Guard clauses with early returns
public function processPayment(Invoice $invoice, float $amount): PaymentResult
{
    if ($invoice->isPaid()) {
        return PaymentResult::alreadyPaid();
    }

    if ($amount <= 0) {
        return PaymentResult::invalidAmount();
    }

    if ($amount > $invoice->total) {
        return PaymentResult::overpayment();
    }

    // Happy path at the lowest indentation
    $payment = $this->createPayment($invoice, $amount);
    $invoice->markAsPaid();

    return PaymentResult::success($payment);
}

// ❌ BAD: Nested conditionals
public function processPayment(Invoice $invoice, float $amount)
{
    if (!$invoice->isPaid()) {
        if ($amount > 0) {
            if ($amount <= $invoice->total) {
                $payment = $this->createPayment($invoice, $amount);
                $invoice->markAsPaid();
                return PaymentResult::success($payment);
            }
        }
    }
}
```

### 5. Immutable Data Transfer Objects
```php
// ✅ GOOD: Read-only DTO
readonly class InvoiceData
{
    public function __construct(
        public string $clientName,
        public string $clientEmail,
        public array $items,
        public int $vatRate,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            clientName: $data['client_name'],
            clientEmail: $data['client_email'],
            items: $data['items'],
            vatRate: $data['vat_rate'],
        );
    }
}

// Use in controller
public function store(StoreInvoiceRequest $request)
{
    $data = InvoiceData::fromRequest($request->validated());
    $invoice = $this->invoiceService->create($data);

    return redirect()->route('invoices.show', $invoice);
}
```

## Anti-Patterns to Avoid

### 1. ❌ Using Mass Assignment Without Protection
```php
// BAD: Accepting all request data
public function update(Request $request, Invoice $invoice)
{
    $invoice->update($request->all());  // Security risk!
}

// GOOD: Using Form Request validation
public function update(UpdateInvoiceRequest $request, Invoice $invoice)
{
    $invoice->update($request->validated());
}

// GOOD: Explicit field assignment
public function update(Request $request, Invoice $invoice)
{
    $invoice->update([
        'client_name' => $request->input('client_name'),
        'amount' => $request->input('amount'),
        'vat_rate' => $request->input('vat_rate'),
    ]);
}
```

### 2. ❌ Using Eloquent in Views
```php
// BAD: Database queries in Blade templates
<!-- resources/views/dashboard.blade.php -->
@foreach(App\Models\Invoice::where('status', 'unpaid')->get() as $invoice)
    <li>{{ $invoice->number }}</li>
@endforeach

// GOOD: Pass data from controller
// Controller
public function index()
{
    return view('dashboard', [
        'unpaidInvoices' => Invoice::unpaid()->get(),
    ]);
}

<!-- View -->
@foreach($unpaidInvoices as $invoice)
    <li>{{ $invoice->number }}</li>
@endforeach
```

### 3. ❌ Not Using Database Transactions
```php
// BAD: No transaction for multi-step operations
public function createInvoice(array $data)
{
    $invoice = Invoice::create($data);

    foreach ($data['items'] as $item) {
        InvoiceItem::create([...]);  // If this fails, invoice is orphaned
    }

    return $invoice;
}

// GOOD: Use transactions
public function createInvoice(array $data): Invoice
{
    return DB::transaction(function () use ($data) {
        $invoice = Invoice::create($data);

        foreach ($data['items'] as $item) {
            $invoice->items()->create($item);
        }

        return $invoice;
    });
}
```

### 4. ❌ Using Static Facades in Services
```php
// BAD: Hard to test
class InvoiceService
{
    public function sendInvoice(Invoice $invoice)
    {
        Mail::to($invoice->client->email)->send(new InvoiceMail($invoice));
    }
}

// GOOD: Dependency injection
class InvoiceService
{
    public function __construct(
        private MailerInterface $mailer
    ) {}

    public function sendInvoice(Invoice $invoice): void
    {
        $this->mailer->send(
            new InvoiceMail($invoice),
            $invoice->client->email
        );
    }
}
```

### 5. ❌ N+1 Query Problems
```php
// BAD: N+1 queries (1 + N queries for N invoices)
$invoices = Invoice::all();
foreach ($invoices as $invoice) {
    echo $invoice->client->name;  // Query for each invoice!
}

// GOOD: Eager loading (2 queries total)
$invoices = Invoice::with('client')->get();
foreach ($invoices as $invoice) {
    echo $invoice->client->name;  // No additional query
}
```

## Code Examples (Dutch Bookkeeping)

### 1. VAT Calculation Service with Dutch Rates
```php
<?php

namespace App\Services;

use App\Models\Invoice;

/**
 * Dutch VAT (BTW) calculation service
 * Implements Dutch tax law requirements
 */
final class VatCalculationService
{
    // Dutch VAT rates for 2025
    private const STANDARD_RATE = 21;  // Algemeen tarief
    private const REDUCED_RATE = 9;    // Verlaagd tarief
    private const ZERO_RATE = 0;       // Nultarief

    /**
     * Calculate VAT amount based on Dutch tax rules
     *
     * @param float $amount Net amount (excl. VAT)
     * @param int $rate VAT rate percentage
     * @return float VAT amount
     * @throws \InvalidArgumentException
     */
    public function calculateVatAmount(float $amount, int $rate): float
    {
        $this->validateVatRate($rate);

        if ($amount < 0) {
            throw new \InvalidArgumentException('Amount cannot be negative');
        }

        // Dutch VAT calculation: amount * (rate / 100)
        $vatAmount = round($amount * ($rate / 100), 2);

        \Log::debug('VAT calculated', [
            'net_amount' => $amount,
            'vat_rate' => $rate,
            'vat_amount' => $vatAmount,
        ]);

        return $vatAmount;
    }

    /**
     * Calculate gross amount (incl. VAT)
     */
    public function calculateGrossAmount(float $netAmount, int $rate): float
    {
        $vatAmount = $this->calculateVatAmount($netAmount, $rate);
        return round($netAmount + $vatAmount, 2);
    }

    /**
     * Calculate net amount from gross (reverse calculation)
     */
    public function calculateNetFromGross(float $grossAmount, int $rate): float
    {
        $this->validateVatRate($rate);

        // Formula: net = gross / (1 + rate/100)
        $netAmount = round($grossAmount / (1 + $rate / 100), 2);

        return $netAmount;
    }

    /**
     * Validate VAT rate is a valid Dutch rate
     */
    private function validateVatRate(int $rate): void
    {
        $validRates = [self::ZERO_RATE, self::REDUCED_RATE, self::STANDARD_RATE];

        if (!in_array($rate, $validRates, true)) {
            throw new \InvalidArgumentException(
                "Invalid VAT rate: {$rate}. Valid Dutch rates: " . implode(', ', $validRates)
            );
        }
    }

    /**
     * Get applicable VAT rate for product category
     */
    public function getRateForCategory(string $category): int
    {
        return match ($category) {
            'food', 'books', 'medicine' => self::REDUCED_RATE,
            'export', 'intra_eu' => self::ZERO_RATE,
            default => self::STANDARD_RATE,
        };
    }
}
```

### 2. Dutch IBAN Validator
```php
<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

/**
 * Validate Dutch IBAN (International Bank Account Number)
 */
class DutchIban implements Rule
{
    /**
     * Determine if the validation rule passes
     */
    public function passes($attribute, $value): bool
    {
        // Remove spaces and convert to uppercase
        $iban = strtoupper(str_replace(' ', '', $value));

        // Dutch IBAN starts with NL and is 18 characters
        if (!preg_match('/^NL\d{2}[A-Z]{4}\d{10}$/', $iban)) {
            return false;
        }

        // Validate checksum (mod 97 algorithm)
        $rearranged = substr($iban, 4) . substr($iban, 0, 4);
        $numeric = '';

        for ($i = 0; $i < strlen($rearranged); $i++) {
            $char = $rearranged[$i];
            $numeric .= ctype_digit($char) ? $char : (ord($char) - 55);
        }

        // IBAN is valid if mod 97 equals 1
        return bcmod($numeric, '97') === '1';
    }

    /**
     * Get the validation error message
     */
    public function message(): string
    {
        return 'Het :attribute moet een geldig Nederlands IBAN zijn.';
    }
}

// Usage in Form Request
class UpdateBankAccountRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'iban' => ['required', new DutchIban],
            'account_holder' => 'required|string|max:255',
        ];
    }
}
```

### 3. KVK Number Validator (Dutch Chamber of Commerce)
```php
<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

/**
 * Validate Dutch KVK (Kamer van Koophandel) number
 * Format: 8 digits
 */
class KvkNumber implements Rule
{
    public function passes($attribute, $value): bool
    {
        // KVK number must be exactly 8 digits
        if (!preg_match('/^\d{8}$/', $value)) {
            return false;
        }

        // Validate using "11-proof" algorithm (modulo 11)
        $sum = 0;
        $weights = [9, 8, 7, 6, 5, 4, 3, 2];

        for ($i = 0; $i < 8; $i++) {
            $sum += (int)$value[$i] * $weights[$i];
        }

        return $sum % 11 === 0;
    }

    public function message(): string
    {
        return 'Het :attribute moet een geldig KVK-nummer zijn (8 cijfers).';
    }
}

// Usage in Company Registration
class RegisterCompanyRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'kvk_number' => ['required', new KvkNumber, 'unique:companies,kvk_number'],
            'vat_number' => ['required', 'regex:/^NL\d{9}B\d{2}$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'vat_number.regex' => 'Het BTW-nummer moet het formaat NL123456789B01 hebben.',
        ];
    }
}
```

## Troubleshooting

### Problem 1: PHPStan False Positives with Laravel
**Symptom:** PHPStan reports errors on valid Laravel code

```bash
# Error: "Property App\Models\Invoice::$client is never read, only written"
# This is because PHPStan doesn't understand Eloquent relationships
```

**Solution:**
```bash
# Install Laravel-specific PHPStan extensions
composer require --dev larastan/larastan

# Update phpstan.neon
includes:
    - ./vendor/larastan/larastan/extension.neon

parameters:
    level: 6
    checkModelProperties: false  # Disable if too many false positives
```

### Problem 2: Pint Formatting Conflicts with Team Preferences
**Symptom:** Pint changes formatting that team doesn't like

**Solution:**
```json
// Customize pint.json
{
    "preset": "laravel",
    "rules": {
        "array_syntax": {"syntax": "short"},
        "blank_line_before_statement": {
            "statements": ["return", "throw", "try"]
        },
        "method_chaining_indentation": true,
        "not_operator_with_successor_space": false,  // Team prefers !$var not ! $var
        "single_line_comment_spacing": true
    },
    "exclude": [
        "storage",
        "vendor",
        "node_modules",
        "bootstrap/cache"
    ]
}
```

### Problem 3: Slow PHPStan Analysis
**Symptom:** PHPStan takes >5 minutes to run

**Solution:**
```neon
# phpstan.neon - Optimize for speed
parameters:
    paths:
        - app
        # Don't analyze everything

    excludePaths:
        - app/Exceptions/Handler.php
        - database/*
        - storage/*

    tmpDir: storage/phpstan  # Use faster storage

    parallel:
        maximumNumberOfProcesses: 4  # Use CPU cores
        processTimeout: 300.0
```

### Problem 4: Pre-commit Hook Too Slow
**Symptom:** Git commits take 30+ seconds due to checks

**Solution:**
```bash
# Only check staged files, not entire codebase
# .git/hooks/pre-commit

#!/bin/bash

# Get list of staged PHP files
STAGED_FILES=$(git diff --cached --name-only --diff-filter=ACM | grep "\.php$")

if [ -z "$STAGED_FILES" ]; then
    exit 0
fi

# Run Pint only on staged files
./vendor/bin/pint $STAGED_FILES --test

if [ $? -ne 0 ]; then
    echo "❌ Pint found issues. Run: ./vendor/bin/pint"
    exit 1
fi

# Run PHPStan only on changed files
echo "$STAGED_FILES" | xargs ./vendor/bin/phpstan analyse --no-progress

if [ $? -ne 0 ]; then
    echo "❌ PHPStan found issues"
    exit 1
fi

echo "✅ Code quality checks passed"
exit 0
```

### Problem 5: Type Errors with Mixed Arrays
**Symptom:** PHPStan complains about array types

```php
// Error: "Cannot call method getName() on mixed"
foreach ($invoices as $invoice) {
    echo $invoice->getName();  // PHPStan doesn't know $invoice type
}
```

**Solution:**
```php
// Add PHPDoc type hints
/** @var Collection<int, Invoice> $invoices */
$invoices = Invoice::all();

foreach ($invoices as $invoice) {
    echo $invoice->getName();  // PHPStan now knows it's Invoice
}

// Or use assertions
foreach ($invoices as $invoice) {
    assert($invoice instanceof Invoice);
    echo $invoice->getName();
}
```

## Integration Guidance

### 1. Integrate with Git Hooks (Husky Alternative)
```bash
# Install package
composer require --dev brainmaestro/composer-git-hooks

# Configure in composer.json
{
    "extra": {
        "hooks": {
            "pre-commit": [
                "echo 'Running code quality checks...'",
                "./vendor/bin/pint --test",
                "./vendor/bin/phpstan analyse --error-format=table --no-progress"
            ],
            "pre-push": [
                "php artisan test"
            ],
            "post-merge": [
                "composer install"
            ]
        }
    }
}

# Install hooks
composer cghooks add --ignore-lock
```

### 2. Integrate with IDE (PHPStorm)
```xml
<!-- .idea/inspectionProfiles/Project_Default.xml -->
<component name="InspectionProjectProfileManager">
  <profile version="1.0">
    <option name="myName" value="Project Default" />
    <inspection_tool class="PhpStanGlobal" enabled="true" level="WARNING" enabled_by_default="true" />
    <inspection_tool class="PhpCSValidation" enabled="true" level="WARNING" enabled_by_default="true">
      <option name="CODING_STANDARD" value="PSR12" />
      <option name="SHOW_SNIFF_NAMES" value="true" />
    </inspection_tool>
  </profile>
</component>
```

### 3. Integrate with Continuous Integration
```yaml
# .github/workflows/code-quality.yml
name: Code Quality

on: [push, pull_request]

jobs:
  quality:
    runs-on: ubuntu-latest

    steps:
      - uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: 8.2
          tools: composer:v2
          coverage: none

      - name: Install dependencies
        run: composer install --no-progress --prefer-dist

      - name: Run Pint
        run: ./vendor/bin/pint --test

      - name: Run PHPStan
        run: ./vendor/bin/phpstan analyse --error-format=github

      - name: Run Tests
        run: php artisan test --parallel
```

## Code Quality Checklist

### Before Committing
- [ ] Code follows PSR-12 standard (`./vendor/bin/pint --test`)
- [ ] No PHPStan errors (`./vendor/bin/phpstan analyse`)
- [ ] All tests passing (`php artisan test`)
- [ ] No debug statements (`dd`, `dump`, `var_dump`)
- [ ] Type hints on all methods
- [ ] DocBlocks on complex methods
- [ ] No commented-out code

### Before Pull Request
- [ ] Code reviewed by at least one other developer
- [ ] No N+1 query issues
- [ ] Database queries optimized
- [ ] Eager loading implemented where needed
- [ ] Error handling implemented
- [ ] Logging added for critical operations
- [ ] Tests cover new functionality
- [ ] Documentation updated

### Production Readiness
- [ ] No TODOs or FIXMEs in code
- [ ] All environment variables documented in `.env.example`
- [ ] Security vulnerabilities checked (`composer audit`)
- [ ] Performance tested under load
- [ ] Error tracking configured (Sentry)
- [ ] Monitoring and alerts set up
- [ ] Rollback plan documented

---

**Remember**: Code quality is not just about following rules—it's about writing maintainable, testable, and understandable code!
