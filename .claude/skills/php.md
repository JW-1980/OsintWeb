---
name: php
description: Deep PHP expertise covering language internals, OOP patterns, performance optimization, debugging, security, and modern PHP development practices
version: 1.0.2
tags: [php, backend, debugging, performance, security, oop, patterns]
trigger_keywords: [sk-php, "php code", "php debugging", "php performance", "oop pattern", "php 8 feature", "php error", "design pattern", "php optimization", "composer package", "php internals", namespace, "dependency injection"]
---
# PHP Expert Skill

Deep PHP expertise for the Boekhouder application, covering language internals, object-oriented programming, performance optimization, debugging techniques, security best practices, and modern PHP 8.x development.

## When to Use This Skill

- Writing or reviewing PHP code
- Debugging PHP errors and exceptions
- Optimizing PHP performance
- Implementing design patterns
- Securing PHP applications
- Understanding PHP internals
- Working with Composer and packages
- Migrating to newer PHP versions

## Quick Reference

### PHP 8.x Feature Summary

| Version | Key Features |
|---------|--------------|
| 8.0 | Named arguments, attributes, union types, match expression, nullsafe operator, constructor promotion |
| 8.1 | Enums, fibers, readonly properties, intersection types, never type, final class constants |
| 8.2 | Readonly classes, disjunctive normal form types, null/false standalone types, constants in traits |
| 8.3 | Typed class constants, json_validate(), #[Override] attribute, readonly amendments |

### Essential Commands

```bash
# PHP version and configuration
php -v                        # Version
php -m                        # Loaded modules
php -i                        # Full phpinfo
php --ini                     # Configuration file locations
php -r "var_dump(ini_get('memory_limit'));"

# Composer
composer install              # Install dependencies
composer update               # Update dependencies
composer require package      # Add package
composer dump-autoload -o     # Optimize autoloader

# Debugging
php -l file.php               # Syntax check
php -d display_errors=1 file.php
```

## 25 Essential PHP Tips

### 1. Use Strict Types

```php
<?php

declare(strict_types=1);

function calculateTotal(float $price, int $quantity): float
{
    return $price * $quantity;
}

// Without strict_types: calculateTotal("10.5", "3") would work
// With strict_types: TypeError is thrown
```

### 2. Constructor Property Promotion

```php
// PHP 8.0+: Concise class definitions
class Invoice
{
    public function __construct(
        private readonly int $id,
        private readonly string $number,
        private float $amount,
        private ?Client $client = null,
    ) {}
}

// Equivalent to defining properties and assigning in constructor
```

### 3. Named Arguments

```php
// Clear, self-documenting function calls
$invoice = new Invoice(
    id: 1,
    number: 'INV-2024-001',
    amount: 1000.00,
    client: $client,
);

// Skip optional parameters
sendEmail(
    to: 'user@example.com',
    subject: 'Invoice',
    // template parameter has default, can skip
    priority: 'high',
);
```

### 4. Match Expression

```php
// PHP 8.0+: Better than switch
$vatRate = match($country) {
    'NL' => 0.21,
    'DE' => 0.19,
    'BE' => 0.21,
    'LU' => 0.17,
    default => throw new InvalidCountryException($country),
};

// Match is an expression, returns value
// Strict comparison (===)
// No break needed
// Must be exhaustive
```

### 5. Nullsafe Operator

```php
// PHP 8.0+: Chain without null checks
$city = $invoice->client?->address?->city;

// Equivalent to:
$city = null;
if ($invoice->client !== null) {
    if ($invoice->client->address !== null) {
        $city = $invoice->client->address->city;
    }
}
```

### 6. Enums for Type Safety

```php
// PHP 8.1+: Proper enumerations
enum InvoiceStatus: string
{
    case Draft = 'draft';
    case Sent = 'sent';
    case Paid = 'paid';
    case Overdue = 'overdue';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::Draft => 'Concept',
            self::Sent => 'Verzonden',
            self::Paid => 'Betaald',
            self::Overdue => 'Achterstallig',
            self::Cancelled => 'Geannuleerd',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Draft => 'gray',
            self::Sent => 'blue',
            self::Paid => 'green',
            self::Overdue => 'red',
            self::Cancelled => 'gray',
        };
    }
}

// Usage
$invoice->status = InvoiceStatus::Draft;
echo $invoice->status->label(); // "Concept"
```

### 7. Readonly Properties and Classes

```php
// PHP 8.1+: Immutable properties
class Money
{
    public function __construct(
        public readonly int $amount,
        public readonly string $currency,
    ) {}
}

// PHP 8.2+: Readonly class
readonly class ValueObject
{
    public function __construct(
        public string $value,
    ) {}
}
```

### 8. Union and Intersection Types

```php
// Union types (PHP 8.0+)
function process(int|float|string $value): int|float
{
    return is_string($value) ? strlen($value) : $value * 2;
}

// Intersection types (PHP 8.1+)
function process(Iterator&Countable $collection): int
{
    return count($collection);
}

// DNF types (PHP 8.2+)
function handle((Renderable&Stringable)|null $value): string
{
    return $value?->render() ?? '';
}
```

### 9. Attributes for Metadata

```php
// PHP 8.0+: Native annotations
#[Route('/api/invoices', methods: ['GET'])]
#[Middleware('auth')]
class InvoiceController
{
    #[Get('/api/invoices/{id}')]
    public function show(int $id): JsonResponse
    {
        // ...
    }
}

// Custom attribute
#[Attribute(Attribute::TARGET_PROPERTY)]
class Encrypted
{
    public function __construct(
        public string $algorithm = 'aes-256-cbc',
    ) {}
}

class User
{
    #[Encrypted]
    private string $bsn;
}
```

### 10. First-Class Callable Syntax

```php
// PHP 8.1+: Create closures from callables
$invoices = collect($data)->map($this->createInvoice(...));

// Equivalent to:
$invoices = collect($data)->map(fn($item) => $this->createInvoice($item));

// Works with static methods too
$results = array_map(Invoice::fromArray(...), $arrays);
```

### 11. Fiber for Async

```php
// PHP 8.1+: Cooperative multitasking
$fiber = new Fiber(function(): void {
    $value = Fiber::suspend('suspended');
    echo "Resumed with: $value\n";
});

$value = $fiber->start();
echo "Fiber yielded: $value\n";
$fiber->resume('hello');
```

### 12. Arrow Functions

```php
// Concise closures for simple operations
$totals = array_map(fn($invoice) => $invoice->total, $invoices);

$filtered = array_filter(
    $invoices,
    fn($invoice) => $invoice->amount > 1000 && $invoice->status === InvoiceStatus::Paid
);

// Arrow functions capture by value automatically
$multiplier = 1.21;
$withVat = array_map(fn($price) => $price * $multiplier, $prices);
```

### 13. Spread Operator

```php
// Array spreading
$defaults = ['status' => 'draft', 'currency' => 'EUR'];
$invoice = [...$defaults, ...$clientDefaults, 'amount' => 1000];

// Function argument spreading
function sum(int ...$numbers): int
{
    return array_sum($numbers);
}

$numbers = [1, 2, 3, 4, 5];
$total = sum(...$numbers);
```

### 14. Null Coalescing Assignment

```php
// Initialize if not set
$cache['key'] ??= expensiveComputation();

// Equivalent to:
if (!isset($cache['key'])) {
    $cache['key'] = expensiveComputation();
}
```

### 15. Array Destructuring

```php
// Named keys
['first' => $first, 'last' => $last] = $user;

// List style
[$a, $b, $c] = [1, 2, 3];

// Skip elements
[, , $third] = getValues();

// Nested destructuring
['user' => ['name' => $name]] = $response;
```

### 16. Generators for Memory Efficiency

```php
// Memory-efficient iteration
function readLargeFile(string $path): Generator
{
    $handle = fopen($path, 'r');
    while (($line = fgets($handle)) !== false) {
        yield trim($line);
    }
    fclose($handle);
}

// Yielding keys
function getInvoices(): Generator
{
    foreach ($this->query() as $row) {
        yield $row['id'] => Invoice::fromArray($row);
    }
}

// Processing millions of rows with constant memory
foreach (readLargeFile('huge.csv') as $line) {
    process($line);
}
```

### 17. Exception Handling Best Practices

```php
// Create specific exceptions
class InvoiceNotFoundException extends RuntimeException
{
    public static function forId(int $id): self
    {
        return new self("Invoice with ID {$id} not found");
    }
}

// Catch multiple types
try {
    $invoice = $this->findOrFail($id);
} catch (InvoiceNotFoundException|ClientNotFoundException $e) {
    return response()->json(['error' => $e->getMessage()], 404);
} catch (Throwable $e) {
    report($e);
    return response()->json(['error' => 'Server error'], 500);
}

// Non-capturing catches (PHP 8.0+)
try {
    $value = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
} catch (JsonException) {
    return [];
}
```

### 18. Late Static Binding

```php
abstract class Model
{
    public static function find(int $id): static
    {
        return static::query()->find($id);
    }

    public static function create(array $data): static
    {
        $instance = new static();
        $instance->fill($data);
        $instance->save();
        return $instance;
    }
}

class Invoice extends Model
{
    // find() and create() return Invoice, not Model
}
```

### 19. Traits Effectively

```php
trait HasTimestamps
{
    public ?Carbon $created_at = null;
    public ?Carbon $updated_at = null;

    public function touch(): void
    {
        $this->updated_at = Carbon::now();
    }

    protected function initializeHasTimestamps(): void
    {
        $this->created_at = Carbon::now();
        $this->updated_at = Carbon::now();
    }
}

trait BelongsToCompany
{
    public function scopeForCompany($query, int $companyId): void
    {
        $query->where('company_id', $companyId);
    }

    protected static function bootBelongsToCompany(): void
    {
        static::addGlobalScope(new CompanyScope());
    }
}

class Invoice extends Model
{
    use HasTimestamps, BelongsToCompany;
}
```

### 20. Interfaces for Contracts

```php
interface InvoiceRepositoryInterface
{
    public function find(int $id): ?Invoice;
    public function findOrFail(int $id): Invoice;
    public function save(Invoice $invoice): void;
    public function delete(Invoice $invoice): void;
    public function findByNumber(string $number): ?Invoice;
    public function getUnpaidForClient(Client $client): Collection;
}

// Implementation can be swapped (DB, API, in-memory for testing)
class EloquentInvoiceRepository implements InvoiceRepositoryInterface
{
    // ...
}

class ApiInvoiceRepository implements InvoiceRepositoryInterface
{
    // ...
}
```

### 21. Value Objects

```php
readonly class Money
{
    public function __construct(
        public int $cents,
        public string $currency = 'EUR',
    ) {
        if ($this->cents < 0) {
            throw new InvalidArgumentException('Amount cannot be negative');
        }
    }

    public static function fromFloat(float $amount, string $currency = 'EUR'): self
    {
        return new self((int) round($amount * 100), $currency);
    }

    public function add(Money $other): self
    {
        $this->ensureSameCurrency($other);
        return new self($this->cents + $other->cents, $this->currency);
    }

    public function toFloat(): float
    {
        return $this->cents / 100;
    }

    public function format(): string
    {
        return number_format($this->toFloat(), 2, ',', '.') . ' ' . $this->currency;
    }
}
```

### 22. Dependency Injection

```php
class InvoiceService
{
    public function __construct(
        private readonly InvoiceRepositoryInterface $invoices,
        private readonly ClientRepositoryInterface $clients,
        private readonly VatCalculator $vatCalculator,
        private readonly InvoiceNumberGenerator $numberGenerator,
        private readonly EventDispatcher $events,
    ) {}

    public function create(CreateInvoiceDTO $dto): Invoice
    {
        $client = $this->clients->findOrFail($dto->clientId);

        $invoice = new Invoice(
            number: $this->numberGenerator->next(),
            client: $client,
            lines: $dto->lines,
            vatAmount: $this->vatCalculator->calculate($dto->lines),
        );

        $this->invoices->save($invoice);
        $this->events->dispatch(new InvoiceCreated($invoice));

        return $invoice;
    }
}
```

### 23. Collections and Iterators

```php
class InvoiceCollection implements IteratorAggregate, Countable
{
    public function __construct(
        private array $invoices = [],
    ) {}

    public function add(Invoice $invoice): void
    {
        $this->invoices[] = $invoice;
    }

    public function filter(callable $callback): self
    {
        return new self(array_filter($this->invoices, $callback));
    }

    public function map(callable $callback): array
    {
        return array_map($callback, $this->invoices);
    }

    public function totalAmount(): Money
    {
        return array_reduce(
            $this->invoices,
            fn(Money $total, Invoice $inv) => $total->add($inv->amount),
            Money::fromFloat(0),
        );
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->invoices);
    }

    public function count(): int
    {
        return count($this->invoices);
    }
}
```

### 24. Immutable Data Transfer Objects

```php
readonly class CreateInvoiceDTO
{
    public function __construct(
        public int $clientId,
        public array $lines,
        public ?string $reference = null,
        public ?Carbon $dueDate = null,
        public string $currency = 'EUR',
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            clientId: $request->validated('client_id'),
            lines: $request->validated('lines'),
            reference: $request->validated('reference'),
            dueDate: $request->validated('due_date')
                ? Carbon::parse($request->validated('due_date'))
                : null,
            currency: $request->validated('currency', 'EUR'),
        );
    }

    public function with(array $overrides): self
    {
        return new self(
            clientId: $overrides['clientId'] ?? $this->clientId,
            lines: $overrides['lines'] ?? $this->lines,
            reference: $overrides['reference'] ?? $this->reference,
            dueDate: $overrides['dueDate'] ?? $this->dueDate,
            currency: $overrides['currency'] ?? $this->currency,
        );
    }
}
```

### 25. Error Handling with Result Types

```php
readonly class Result
{
    private function __construct(
        public bool $success,
        public mixed $value = null,
        public ?string $error = null,
    ) {}

    public static function success(mixed $value): self
    {
        return new self(true, $value);
    }

    public static function failure(string $error): self
    {
        return new self(false, null, $error);
    }

    public function map(callable $fn): self
    {
        if (!$this->success) {
            return $this;
        }
        return self::success($fn($this->value));
    }

    public function getOrElse(mixed $default): mixed
    {
        return $this->success ? $this->value : $default;
    }

    public function getOrThrow(): mixed
    {
        if (!$this->success) {
            throw new RuntimeException($this->error);
        }
        return $this->value;
    }
}

// Usage
function parseInvoice(string $json): Result
{
    try {
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        $invoice = Invoice::fromArray($data);
        return Result::success($invoice);
    } catch (JsonException $e) {
        return Result::failure("Invalid JSON: {$e->getMessage()}");
    } catch (ValidationException $e) {
        return Result::failure("Invalid data: {$e->getMessage()}");
    }
}
```

## Debugging Techniques

### Using Xdebug

```php
// php.ini configuration
xdebug.mode = debug,develop
xdebug.start_with_request = yes
xdebug.client_host = host.docker.internal
xdebug.client_port = 9003
xdebug.idekey = PHPSTORM

// Conditional breakpoints in code
if ($invoice->amount > 10000) {
    xdebug_break();
}
```

### Debug Functions

```php
// Pretty print with types
function dd(...$vars): never
{
    foreach ($vars as $var) {
        var_dump($var);
    }
    die(1);
}

// Dump and continue
function dump(...$vars): void
{
    foreach ($vars as $var) {
        var_dump($var);
    }
}

// Backtrace
debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

// Memory usage
echo memory_get_usage(true) / 1024 / 1024 . ' MB';
echo memory_get_peak_usage(true) / 1024 / 1024 . ' MB peak';
```

### Profiling

```php
// Simple profiling
$start = microtime(true);
// ... code to profile ...
$elapsed = microtime(true) - $start;
echo "Elapsed: {$elapsed}s\n";

// Using Blackfire or Tideways
// Install extension and annotate code
```

## Performance Optimization

### OPcache Configuration

```ini
; php.ini for production
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0  ; Disable in production
opcache.jit=1255               ; PHP 8.0+ JIT
opcache.jit_buffer_size=256M
```

### Code Optimization Tips

```php
// Use isset() instead of array_key_exists() when possible
isset($array[$key]) // faster
array_key_exists($key, $array) // handles null values

// Pre-calculate loop bounds
$count = count($items);
for ($i = 0; $i < $count; $i++) { ... }

// Use string functions over regex when possible
strpos($haystack, $needle) !== false // faster
preg_match('/needle/', $haystack) // slower

// Avoid unnecessary object creation in loops
$formatter = new NumberFormatter('nl_NL', NumberFormatter::CURRENCY);
foreach ($invoices as $invoice) {
    $formatted = $formatter->formatCurrency($invoice->amount, 'EUR');
}

// Use references for large arrays
foreach ($largeArray as &$item) {
    $item = transform($item);
}
unset($item); // Important: unset reference after loop
```

## Security Best Practices

### Input Validation

```php
// Always validate and sanitize input
$email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
$amount = filter_var($_POST['amount'], FILTER_VALIDATE_FLOAT);

// Use prepared statements
$stmt = $pdo->prepare('SELECT * FROM invoices WHERE id = :id AND company_id = :company');
$stmt->execute(['id' => $id, 'company' => $companyId]);

// Parameterized queries with Eloquent
Invoice::where('id', $id)->where('company_id', $companyId)->first();
```

### Output Encoding

```php
// HTML encoding
htmlspecialchars($userInput, ENT_QUOTES | ENT_HTML5, 'UTF-8');

// JSON encoding
echo json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);

// Blade auto-escapes by default
{{ $userInput }}  // Safe
{!! $userInput !!}  // Raw - avoid with user input
```

### Password Handling

```php
// Always use password_hash
$hash = password_hash($password, PASSWORD_ARGON2ID);

// Verify passwords
if (password_verify($attemptedPassword, $storedHash)) {
    // Correct password
}

// Check if rehash needed
if (password_needs_rehash($hash, PASSWORD_ARGON2ID)) {
    $newHash = password_hash($password, PASSWORD_ARGON2ID);
    // Update stored hash
}
```

## Troubleshooting

### Common Errors

| Error | Cause | Solution |
|-------|-------|----------|
| Maximum execution time | Long-running script | Increase max_execution_time or optimize |
| Allowed memory size exhausted | Memory leak/large data | Increase memory_limit, use generators |
| Class not found | Autoload issue | Run composer dump-autoload |
| Undefined property | Typo or uninitialized | Use typed properties, initialize |
| Cannot use return value | Immediate method call on construct | Wrap in parentheses |

### Memory Issues

```php
// Identify memory hogs
gc_collect_cycles();
echo memory_get_usage(true) / 1024 / 1024 . ' MB';

// Process large datasets in chunks
Invoice::chunk(1000, function ($invoices) {
    foreach ($invoices as $invoice) {
        process($invoice);
    }
});

// Unset large variables when done
$largeData = fetchLargeDataset();
processData($largeData);
unset($largeData);
gc_collect_cycles();
```

---

# SOLID PRINCIPLES IN PHP

## Overview

SOLID is a set of five design principles that help create maintainable, scalable, and testable code. These principles are fundamental to professional PHP development.

---

## 1. Single Responsibility Principle (SRP)

**Definition**: A class should have only one reason to change.

### Why It Matters
- Easier to understand and maintain
- Reduces side effects from changes
- Improves testability
- Enables code reuse

### 12 Tips for SRP

**Tip 1: Extract Validation Logic**
```php
// ❌ BAD: Controller does validation
class InvoiceController {
    public function store(Request $request) {
        if (empty($request->client_id)) {
            throw new ValidationException('Client required');
        }
        // ... more validation
    }
}

// ✅ GOOD: Dedicated Form Request
class StoreInvoiceRequest extends FormRequest {
    public function rules(): array {
        return ['client_id' => 'required|exists:clients,id'];
    }
}
```

**Tip 2: Extract Business Logic to Services**
```php
// ❌ BAD: Controller has business logic
class InvoiceController {
    public function store(Request $request) {
        $invoice = Invoice::create($request->all());
        $invoice->calculateVat();
        $invoice->generateNumber();
        Mail::send(new InvoiceCreated($invoice));
        return $invoice;
    }
}

// ✅ GOOD: Service handles business logic
class InvoiceService {
    public function create(array $data): Invoice {
        return DB::transaction(function () use ($data) {
            $invoice = Invoice::create($data);
            $this->calculateVat($invoice);
            $this->generateNumber($invoice);
            event(new InvoiceCreated($invoice));
            return $invoice;
        });
    }
}
```

**Tip 3: One Class = One Actor**
Ask: "Who would request changes to this class?" If multiple stakeholders, split.

**Tip 4: Keep Methods Focused**
Each method should do one thing. If you need "and" to describe it, split it.

**Tip 5: Separate Data from Behavior**
Use DTOs for data transfer, Services for behavior.

**Tip 6: Extract Formatting Logic**
```php
// ❌ BAD: Model has formatting
class Invoice extends Model {
    public function getFormattedTotal(): string {
        return '€ ' . number_format($this->total, 2, ',', '.');
    }
}

// ✅ GOOD: Dedicated presenter/formatter
class InvoicePresenter {
    public function formatTotal(Invoice $invoice): string {
        return $this->currencyFormatter->format($invoice->total);
    }
}
```

**Tip 7: Separate Query Logic**
Use Repository pattern or query classes for complex queries.

**Tip 8: Extract Notification Logic**
Use Events and Listeners instead of direct notifications in services.

**Tip 9: One Reason Test**
If changing a class for multiple reasons, it violates SRP.

**Tip 10: Avoid God Classes**
Classes with 500+ lines usually violate SRP. Split them.

**Tip 11: Use Traits Sparingly**
Traits should add one capability, not dump multiple responsibilities.

**Tip 12: Separate Configuration**
Extract hardcoded values to config files or constants.

### Common SRP Violations
- Controllers with business logic
- Models with formatting, notification, or orchestration
- Services doing validation, logging, and business logic
- Classes named "Manager", "Handler", "Processor" (too vague)

---

## 2. Open/Closed Principle (OCP)

**Definition**: Software entities should be open for extension, closed for modification.

### Why It Matters
- Reduces risk of breaking existing code
- Enables adding features without touching stable code
- Promotes use of abstractions
- Supports plugin architectures

### 11 Tips for OCP

**Tip 1: Use Interfaces for Extensibility**
```php
// ✅ Open for extension: Add new gateways without modifying
interface PaymentGatewayInterface {
    public function process(Payment $payment): PaymentResult;
}

class StripeGateway implements PaymentGatewayInterface { }
class MollieGateway implements PaymentGatewayInterface { }
class IdealGateway implements PaymentGatewayInterface { } // New - no changes to existing
```

**Tip 2: Use Strategy Pattern**
```php
interface TaxCalculator {
    public function calculate(float $amount): float;
}

class DutchVatCalculator implements TaxCalculator {
    public function calculate(float $amount): float {
        return $amount * 0.21;
    }
}

class GermanVatCalculator implements TaxCalculator {
    public function calculate(float $amount): float {
        return $amount * 0.19;
    }
}
```

**Tip 3: Prefer Composition Over Inheritance**
```php
class InvoiceProcessor {
    public function __construct(
        private TaxCalculator $taxCalculator,
        private NumberGenerator $numberGenerator
    ) {}
}
```

**Tip 4: Use Factory Pattern for Object Creation**
```php
class PaymentGatewayFactory {
    public function make(string $provider): PaymentGatewayInterface {
        return match($provider) {
            'stripe' => new StripeGateway(),
            'mollie' => new MollieGateway(),
            default => throw new InvalidArgumentException(),
        };
    }

    // Open for extension
    public function register(string $name, string $class): void {
        $this->gateways[$name] = $class;
    }
}
```

**Tip 5: Use Template Method Pattern**
```php
abstract class DocumentExporter {
    public function export(Document $doc): string {
        $data = $this->prepareData($doc);
        return $this->formatOutput($data); // Subclasses override
    }

    abstract protected function formatOutput(array $data): string;
}

class PdfExporter extends DocumentExporter {
    protected function formatOutput(array $data): string { /* PDF logic */ }
}

class CsvExporter extends DocumentExporter {
    protected function formatOutput(array $data): string { /* CSV logic */ }
}
```

**Tip 6: Use Decorator Pattern**
```php
interface Logger {
    public function log(string $message): void;
}

class FileLogger implements Logger { /* ... */ }

class TimestampedLogger implements Logger {
    public function __construct(private Logger $logger) {}

    public function log(string $message): void {
        $this->logger->log('[' . date('c') . '] ' . $message);
    }
}
```

**Tip 7: Avoid Switch/Case on Types**
Switch statements often indicate missing abstraction.

**Tip 8: Use Events for Extension Points**
```php
// Core code dispatches event
event(new InvoiceCreated($invoice));

// Extensions listen without modifying core
class SendInvoiceEmail {
    public function handle(InvoiceCreated $event): void { /* ... */ }
}
```

**Tip 9: Configuration Over Code Changes**
```php
// Extensible via config, not code changes
$processors = config('invoices.processors');
foreach ($processors as $processor) {
    app($processor)->process($invoice);
}
```

**Tip 10: Use Middleware Pattern**
```php
class Pipeline {
    public function through(array $middleware): self { /* ... */ }
    public function then(Closure $destination): mixed { /* ... */ }
}
```

**Tip 11: Abstract Stable Parts, Vary Unstable Parts**
Identify what changes frequently and abstract it.

### Common OCP Violations
- Adding `if` statements for new types
- Modifying switch cases for new options
- Editing core classes for new features
- Hardcoded dependencies

---

## 3. Liskov Substitution Principle (LSP)

**Definition**: Objects of a superclass should be replaceable with objects of its subclasses without breaking the application.

### Why It Matters
- Ensures polymorphism works correctly
- Prevents unexpected behavior
- Enables reliable abstraction
- Supports testing with mocks

### 10 Tips for LSP

**Tip 1: Maintain Method Signatures**
```php
// ❌ BAD: Subclass changes return type
class Bird {
    public function fly(): void { /* ... */ }
}

class Penguin extends Bird {
    public function fly(): void {
        throw new CannotFlyException(); // Violates LSP!
    }
}

// ✅ GOOD: Use proper abstraction
interface Bird { public function move(): void; }
interface FlyingBird extends Bird { public function fly(): void; }

class Penguin implements Bird {
    public function move(): void { $this->swim(); }
}
```

**Tip 2: Don't Strengthen Preconditions**
Subclasses shouldn't require more than parent.
```php
// ❌ BAD: Subclass requires more
class Parent {
    public function process(int $amount): void { /* works with any int */ }
}

class Child extends Parent {
    public function process(int $amount): void {
        if ($amount < 0) throw new Exception(); // Strengthens precondition!
    }
}
```

**Tip 3: Don't Weaken Postconditions**
Subclasses should deliver at least what parent promises.

**Tip 4: Preserve Invariants**
Subclasses must maintain class invariants (constraints that must always be true).

**Tip 5: History Constraint**
Subclasses shouldn't modify inherited properties in ways parent wouldn't allow.

**Tip 6: Use Abstract Classes for Shared Behavior**
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
    public function getTotal(): float { return -abs($this->total); }
}
```

**Tip 7: Favor Composition for Incompatible Behaviors**
If a subclass can't honor parent's contract, don't extend—compose instead.

**Tip 8: Use Interface Segregation**
If not all methods apply, the interface is too broad.

**Tip 9: Test Substitutability**
```php
function processDocument(Document $doc) {
    echo $doc->getNumber() . ': ' . $doc->getTotal();
}

// Both should work identically
processDocument(new Invoice());
processDocument(new CreditNote());
```

**Tip 10: Don't Throw New Exceptions**
Subclass methods shouldn't throw exceptions parent doesn't throw.

### Common LSP Violations
- Throwing exceptions in overridden methods
- Ignoring parent method's contract
- Returning different types (before PHP 7.4 covariance)
- Empty method implementations
- Square/Rectangle problem (mutable inheritance issues)

---

## 4. Interface Segregation Principle (ISP)

**Definition**: Clients should not be forced to depend on interfaces they do not use.

### Why It Matters
- Reduces coupling
- Prevents implementing unused methods
- Makes code more focused
- Improves maintainability

### 10 Tips for ISP

**Tip 1: Split Fat Interfaces**
```php
// ❌ BAD: Fat interface
interface DocumentInterface {
    public function save();
    public function delete();
    public function print();
    public function email();
    public function archive();
    public function sign();
}

// ✅ GOOD: Segregated interfaces
interface Persistable { public function save(); public function delete(); }
interface Printable { public function print(); }
interface Emailable { public function email(); }
interface Archivable { public function archive(); }
interface Signable { public function sign(); }

class Invoice implements Persistable, Printable, Emailable, Signable { }
class InternalNote implements Persistable { } // Only what it needs
```

**Tip 2: One Interface Per Capability**
Each interface should represent one capability or role.

**Tip 3: Role-Based Interfaces**
```php
interface Auditable {
    public function getAuditData(): array;
}

interface Lockable {
    public function lock(): void;
    public function isLocked(): bool;
}

interface Versionable {
    public function getVersion(): int;
    public function getPreviousVersions(): array;
}
```

**Tip 4: Prefer Multiple Small Interfaces**
A class implementing 5 small interfaces is better than 1 large interface.

**Tip 5: Client-Focused Interfaces**
Design interfaces from the client's perspective, not the implementer's.

**Tip 6: Avoid Interface Pollution**
Don't add methods "just in case" they might be needed.

**Tip 7: Use Interface Composition**
```php
interface ReadableRepository {
    public function find(int $id): ?Model;
    public function all(): Collection;
}

interface WritableRepository {
    public function save(Model $model): void;
    public function delete(Model $model): void;
}

interface Repository extends ReadableRepository, WritableRepository { }
```

**Tip 8: Command Query Separation**
```php
interface Query { public function execute(): mixed; }  // Returns data
interface Command { public function execute(): void; } // Changes state
```

**Tip 9: Don't Force Empty Implementations**
If you need empty methods, the interface is too broad.

**Tip 10: Review Interface Usage**
If implementations only use some methods, split the interface.

### Common ISP Violations
- Interfaces with 10+ methods
- Classes with empty method implementations
- "God interfaces" trying to cover everything
- Interfaces named after implementations

---

## 5. Dependency Inversion Principle (DIP)

**Definition**: High-level modules should not depend on low-level modules. Both should depend on abstractions.

### Why It Matters
- Reduces coupling between components
- Enables easier testing with mocks
- Supports flexible architecture
- Allows swapping implementations

### 12 Tips for DIP

**Tip 1: Depend on Interfaces, Not Classes**
```php
// ❌ BAD: Depends on concrete class
class InvoiceService {
    private StripePayment $payment;

    public function __construct() {
        $this->payment = new StripePayment();
    }
}

// ✅ GOOD: Depends on abstraction
class InvoiceService {
    public function __construct(
        private PaymentGatewayInterface $payment
    ) {}
}
```

**Tip 2: Use Constructor Injection**
```php
class ReportGenerator {
    public function __construct(
        private readonly ReportRepositoryInterface $repository,
        private readonly FormatterInterface $formatter,
        private readonly CacheInterface $cache
    ) {}
}
```

**Tip 3: Configure Bindings in Service Provider**
```php
// AppServiceProvider
public function register(): void
{
    $this->app->bind(
        PaymentGatewayInterface::class,
        StripePayment::class
    );
}
```

**Tip 4: Use Interface for External Services**
```php
interface NotificationService {
    public function send(Notification $notification): void;
}

class EmailNotificationService implements NotificationService { }
class SmsNotificationService implements NotificationService { }
class SlackNotificationService implements NotificationService { }
```

**Tip 5: Avoid `new` in Business Logic**
Only use `new` for value objects, DTOs, and in factories.

**Tip 6: Use Factory for Complex Creation**
```php
interface InvoiceFactoryInterface {
    public function createFromOrder(Order $order): Invoice;
}
```

**Tip 7: Abstract Third-Party Dependencies**
```php
// Wrap third-party library
interface PdfGeneratorInterface {
    public function generate(string $html): string;
}

class DompdfGenerator implements PdfGeneratorInterface {
    public function generate(string $html): string {
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        return $dompdf->output();
    }
}
```

**Tip 8: Use Method Injection for Occasional Dependencies**
```php
public function processWithAudit(
    Invoice $invoice,
    AuditLoggerInterface $logger
): void {
    $logger->log('Processing invoice ' . $invoice->id);
    $this->process($invoice);
}
```

**Tip 9: Avoid Service Locator Pattern**
```php
// ❌ BAD: Service locator
class InvoiceService {
    public function process() {
        $logger = app()->make(Logger::class); // Hidden dependency
    }
}

// ✅ GOOD: Explicit injection
class InvoiceService {
    public function __construct(private Logger $logger) {}
}
```

**Tip 10: Create Abstractions at Boundaries**
Database, filesystem, HTTP, email, etc. should have abstractions.

**Tip 11: Test with Mock Implementations**
```php
class MockPaymentGateway implements PaymentGatewayInterface {
    public function process(Payment $payment): PaymentResult {
        return new PaymentResult(success: true);
    }
}
```

**Tip 12: Inversion of Control Container**
Laravel's service container handles DIP automatically when properly configured.

### Common DIP Violations
- Using `new` for services in business logic
- Static method calls to concrete classes
- Hardcoded class names in business logic
- Service locator usage
- Tight coupling to framework specifics

---

## SOLID Checklist

Before completing a class:

- [ ] **SRP**: Does it have only one reason to change?
- [ ] **OCP**: Can it be extended without modification?
- [ ] **LSP**: Can subclasses substitute the parent?
- [ ] **ISP**: Are interfaces focused and minimal?
- [ ] **DIP**: Does it depend on abstractions?

---

## Common Problems & Solutions

| Problem | Principle Violated | Solution |
|---------|-------------------|----------|
| Fat controller | SRP | Extract to services |
| Switch on type | OCP | Use polymorphism |
| Empty method impl | ISP | Split interface |
| `new` in service | DIP | Constructor injection |
| Subclass throws | LSP | Redesign hierarchy |
| God class | SRP | Split by responsibility |
| Hardcoded deps | DIP | Interface abstraction |

## Related Skills

- **laravel-expert** - Laravel-specific PHP patterns
- **security-expert** - Application security
- **testing-expert** - PHP testing strategies
- **database-mysql-expert** - Database optimization

---

## Version History

### Version 1.1.0 (2025-12-21)
- Added comprehensive SOLID principles section with 55+ tips
- Added common problems and solutions table
- Added SOLID checklist
