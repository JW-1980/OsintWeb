---
name: multi-tenancy-verification
description: Verify proper company-scoped data isolation and multi-tenancy implementation
tags: [security, multi-tenancy, data-isolation, company-scope]
version: 3.0.1
trigger_keywords: [sk-multi-tenancy-verification, multi-tenancy, tenant isolation, company scope, data isolation, multi-tenant security, tenant verification]
---

# Multi-Tenancy Verification

This skill verifies that the SaaS multi-tenancy architecture properly isolates data between companies.

## When to Use

- After implementing new features
- Before deploying to production
- When adding new database tables
- During security audits
- After permission system changes
- Before data migrations
- When onboarding new team members
- After adding new API endpoints
- During compliance audits (GDPR, SOC2)
- When debugging cross-company data leaks

## Multi-Tenancy Architecture

This application uses **database-level multi-tenancy** where:
- Single database shared by all companies
- Each row has a `company_id` foreign key
- Global scopes automatically filter queries
- Middleware enforces company context

## Critical Requirements

### 1. Every Company-Scoped Table Must Have

```php
// Required columns
$table->foreignId('company_id')->constrained()->cascadeOnDelete();

// Required index for performance
$table->index(['company_id', 'created_at']);
```

### 2. Every Model Must Use CompanyScope

```php
// In Model file
use App\Models\Scopes\CompanyScope;
use App\Traits\HasCompanyScope;

class Invoice extends Model
{
    use HasCompanyScope;

    protected static function booted(): void
    {
        static::addGlobalScope(new CompanyScope);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
```

## Verification Commands

### 1. Find Tables Missing company_id

```bash
cd bookkeeping-app

# Check all migrations for tables that should have company_id
php artisan tinker
>>> $tables = DB::select('SHOW TABLES');
>>> foreach($tables as $table) {
...     $tableName = array_values((array)$table)[0];
...     if (!in_array($tableName, ['migrations', 'password_resets', 'personal_access_tokens', 'jobs', 'failed_jobs'])) {
...         $columns = DB::getSchemaBuilder()->getColumnListing($tableName);
...         if (!in_array('company_id', $columns)) {
...             echo "⚠️  Missing company_id: {$tableName}\n";
...         }
...     }
... }
```

### 2. Find Models Without CompanyScope

```bash
# Check all models
cd app/Models
for file in *.php; do
    if ! grep -q "CompanyScope\|HasCompanyScope" "$file"; then
        # Exclude base models
        if [[ "$file" != "User.php" && "$file" != "Company.php" ]]; then
            echo "⚠️  Missing CompanyScope: $file"
        fi
    fi
done
```

### 3. Find Controllers Without Company Context

```bash
cd app/Http/Controllers
grep -L "current_company_id\|getCurrentCompany" **/*.php | head -20
```

### 4. Test Data Isolation

```bash
# Run multi-tenancy test suite
php artisan test tests/Feature/MultiTenancy/

# Test that users can't see other companies' data
php artisan test --filter=DataIsolationTest
```

## Security Checks

### Check 1: Query Scoping

Verify all queries are automatically scoped:

```php
// ✅ GOOD - Automatically scoped by CompanyScope
$invoices = Invoice::all();  // Only current company's invoices

// ✅ GOOD - Explicit scoping
$invoices = Invoice::where('company_id', auth()->user()->current_company_id)->get();

// ❌ DANGEROUS - Bypasses scope (only use in admin context)
$invoices = Invoice::withoutGlobalScope(CompanyScope::class)->get();
```

### Check 2: Controller Authorization

Every controller method must verify company ownership:

```php
// ✅ GOOD - Uses policy with company check
public function show(Invoice $invoice)
{
    $this->authorize('view', $invoice);  // Policy checks company_id
    return view('invoices.show', compact('invoice'));
}

// ❌ BAD - No company verification
public function show($id)
{
    $invoice = Invoice::find($id);  // Could be any company's invoice
    return view('invoices.show', compact('invoice'));
}
```

### Check 3: API Endpoint Protection

```bash
# All API endpoints must check company context
cd app/Http/Controllers/Api
grep -L "getCurrentCompany\|current_company_id" *.php
```

### Check 4: Foreign Key Relationships

Verify polymorphic relationships include company checks:

```bash
# Find all morphTo relationships
grep -r "morphTo\|morphMany" app/Models/ | cut -d: -f1 | sort -u
```

For each, verify the related models have company_id.

## Common Vulnerabilities

### Vulnerability 1: Direct ID Access

```php
// ❌ VULNERABLE - No company check
Route::get('/invoices/{id}', function($id) {
    return Invoice::find($id);  // Could access other company's data!
});

// ✅ SECURE - Route model binding with policy
Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])
    ->middleware('auth');
// Policy automatically checks company ownership
```

### Vulnerability 2: Mass Assignment

```php
// ❌ VULNERABLE - User could change company_id
public function update(Request $request, Invoice $invoice)
{
    $invoice->update($request->all());  // Dangerous!
}

// ✅ SECURE - Explicit company_id
public function update(Request $request, Invoice $invoice)
{
    $invoice->update([
        'amount' => $request->amount,
        'company_id' => auth()->user()->current_company_id,  // Enforced
    ]);
}
```

### Vulnerability 3: Aggregation Queries

```php
// ❌ VULNERABLE - Aggregates across all companies
$totalRevenue = Invoice::sum('amount');

// ✅ SECURE - Scoped aggregation
$totalRevenue = Invoice::where('company_id', auth()->user()->current_company_id)
    ->sum('amount');

// ✅ SECURE - With global scope (automatic)
$totalRevenue = Invoice::sum('amount');  // If CompanyScope is applied
```

## Testing Strategy

### Unit Tests

```php
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DataIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_access_other_company_invoices()
    {
        $company1 = Company::factory()->create();
        $company2 = Company::factory()->create();

        $user = User::factory()->create();
        $user->companies()->attach($company1, ['role' => 'admin']);
        $user->update(['current_company_id' => $company1->id]);

        $invoice1 = Invoice::factory()->create(['company_id' => $company1->id]);
        $invoice2 = Invoice::factory()->create(['company_id' => $company2->id]);

        $this->actingAs($user);

        // Should see own company's invoice
        $this->get(route('invoices.show', $invoice1))->assertOk();

        // Should NOT see other company's invoice
        $this->get(route('invoices.show', $invoice2))->assertForbidden();
    }
}
```

### Feature Tests

```php
public function test_api_endpoint_respects_company_scope()
{
    $company1 = Company::factory()->create();
    $company2 = Company::factory()->create();

    $user = User::factory()->create();
    $user->companies()->attach($company1, ['role' => 'admin']);
    $user->update(['current_company_id' => $company1->id]);

    Invoice::factory()->count(5)->create(['company_id' => $company1->id]);
    Invoice::factory()->count(3)->create(['company_id' => $company2->id]);

    $response = $this->actingAs($user)->getJson('/api/invoices');

    $response->assertOk();
    $this->assertCount(5, $response->json('data'));  // Only company1's invoices
}
```

## Audit Checklist

### Database Level
- [ ] All company-scoped tables have `company_id` column
- [ ] Foreign key constraints to `companies` table
- [ ] Indexes on `company_id` for performance
- [ ] Cascading deletes configured properly

### Model Level
- [ ] All models use `CompanyScope` global scope
- [ ] `HasCompanyScope` trait applied
- [ ] `company()` relationship defined
- [ ] `$fillable` does NOT include `company_id` (prevent mass assignment)

### Controller Level
- [ ] All methods use `current_company_id` or `getCurrentCompany()`
- [ ] Authorization checks in place (policies)
- [ ] Route model binding used for automatic scoping
- [ ] Validation prevents company_id manipulation

### API Level
- [ ] API endpoints scoped to current company
- [ ] Sanctum/Passport tokens include company context
- [ ] Rate limiting per company
- [ ] API responses don't leak cross-company data

### Permission Level
- [ ] Permissions are company-scoped
- [ ] Users can have different roles per company
- [ ] Permission checks include company verification
- [ ] Super admin access properly restricted

## Tools & Commands

### Generate Multi-Tenancy Report

```bash
php artisan multi-tenancy:audit
```

### Test Data Isolation

```bash
php artisan test --filter=MultiTenancy
```

### Check for Security Issues

```bash
# Find queries without company scope
grep -r "::all()\|::find(" app/Http/Controllers/ | grep -v "User::\|Company::"
```

## Performance Considerations

### Optimize Company-Scoped Queries

```php
// Add compound indexes
Schema::table('invoices', function (Blueprint $table) {
    $table->index(['company_id', 'status', 'created_at']);
    $table->index(['company_id', 'client_id']);
});
```

### Cache Per Company

```php
// ✅ GOOD - Company-scoped cache
Cache::tags(['company:' . $companyId])->remember('invoices', 3600, function() {
    return Invoice::all();
});

// ❌ BAD - Global cache (leaks data)
Cache::remember('invoices', 3600, function() {
    return Invoice::all();
});
```

## Monitoring & Alerts

Set up alerts for:
- Queries bypassing CompanyScope
- Cross-company data access attempts
- Permission violations
- Unusual data access patterns

```php
// Log suspicious activity
if ($invoice->company_id !== auth()->user()->current_company_id) {
    Log::warning('Cross-company access attempt', [
        'user_id' => auth()->id(),
        'invoice_id' => $invoice->id,
        'invoice_company' => $invoice->company_id,
        'user_company' => auth()->user()->current_company_id,
    ]);
}
```

---

## Comprehensive Company_ID Scoping Examples

### Example 1: Scoped Eloquent Queries

```php
// Basic scoped query
$invoices = Invoice::where('company_id', auth()->user()->current_company_id)
    ->where('status', 'unpaid')
    ->get();

// With relationships (eager loading)
$invoices = Invoice::with(['customer', 'items'])
    ->where('company_id', auth()->user()->current_company_id)
    ->get();

// Scoped aggregations
$totalRevenue = Invoice::where('company_id', auth()->user()->current_company_id)
    ->where('status', 'paid')
    ->sum('total');

// Scoped counting
$unpaidCount = Invoice::where('company_id', auth()->user()->current_company_id)
    ->where('status', 'unpaid')
    ->count();
```

### Example 2: Scoped Database Transactions

```php
DB::transaction(function () use ($invoiceData) {
    $companyId = auth()->user()->current_company_id;

    // Create invoice
    $invoice = Invoice::create([
        'company_id' => $companyId,
        'customer_id' => $invoiceData['customer_id'],
        'total' => $invoiceData['total'],
    ]);

    // Verify customer belongs to same company
    $customer = Customer::where('id', $invoiceData['customer_id'])
        ->where('company_id', $companyId)
        ->firstOrFail();

    // Create invoice items
    foreach ($invoiceData['items'] as $item) {
        $product = Product::where('id', $item['product_id'])
            ->where('company_id', $companyId)
            ->firstOrFail();

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'product_id' => $product->id,
            'quantity' => $item['quantity'],
            'price' => $product->price,
        ]);
    }
});
```

### Example 3: Scoped File Uploads

```php
public function uploadInvoicePdf(Request $request, Invoice $invoice)
{
    // Verify invoice belongs to current company
    if ($invoice->company_id !== auth()->user()->current_company_id) {
        abort(403, 'Cannot upload files to other company invoices');
    }

    // Store in company-scoped directory
    $path = $request->file('pdf')->storeAs(
        "companies/{$invoice->company_id}/invoices",
        "{$invoice->invoice_number}.pdf",
        'private'
    );

    $invoice->update(['pdf_path' => $path]);
}

// Retrieve with company scope verification
public function downloadInvoicePdf(Invoice $invoice)
{
    if ($invoice->company_id !== auth()->user()->current_company_id) {
        abort(403);
    }

    return Storage::disk('private')->download($invoice->pdf_path);
}
```

### Example 4: Scoped Search Queries

```php
public function search(Request $request)
{
    $companyId = auth()->user()->current_company_id;
    $query = $request->input('q');

    $results = [
        'invoices' => Invoice::where('company_id', $companyId)
            ->where('invoice_number', 'like', "%{$query}%")
            ->limit(10)
            ->get(),

        'customers' => Customer::where('company_id', $companyId)
            ->where('name', 'like', "%{$query}%")
            ->limit(10)
            ->get(),

        'products' => Product::where('company_id', $companyId)
            ->where('name', 'like', "%{$query}%")
            ->limit(10)
            ->get(),
    ];

    return response()->json($results);
}
```

### Example 5: Scoped Reporting

```php
public function generateVatReport(Request $request)
{
    $companyId = auth()->user()->current_company_id;
    $startDate = $request->input('start_date');
    $endDate = $request->input('end_date');

    $report = DB::table('invoices')
        ->where('company_id', $companyId)
        ->whereBetween('invoice_date', [$startDate, $endDate])
        ->select([
            DB::raw('SUM(subtotal) as total_sales'),
            DB::raw('SUM(vat_amount) as total_vat'),
            'vat_rate',
        ])
        ->groupBy('vat_rate')
        ->get();

    return view('reports.vat', compact('report'));
}
```

### Example 6: Scoped API Endpoints

```php
// routes/api.php
Route::middleware(['auth:sanctum', 'company.scope'])->group(function () {
    Route::get('/invoices', [ApiInvoiceController::class, 'index']);
    Route::get('/invoices/{invoice}', [ApiInvoiceController::class, 'show']);
});

// Middleware: CompanyScope
class CompanyScopeMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user || !$user->current_company_id) {
            return response()->json(['error' => 'No company selected'], 400);
        }

        // Add company_id to all requests
        $request->merge(['company_id' => $user->current_company_id]);

        return $next($request);
    }
}

// Controller
class ApiInvoiceController extends Controller
{
    public function index(Request $request)
    {
        // Automatically scoped by middleware
        $invoices = Invoice::where('company_id', $request->company_id)
            ->paginate(20);

        return InvoiceResource::collection($invoices);
    }

    public function show(Request $request, Invoice $invoice)
    {
        // Verify company ownership
        if ($invoice->company_id !== $request->company_id) {
            return response()->json(['error' => 'Not found'], 404);
        }

        return new InvoiceResource($invoice);
    }
}
```

### Example 7: Scoped Queue Jobs

```php
class ProcessVatDeclarationJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $companyId,
        public string $quarter,
        public int $year
    ) {}

    public function handle()
    {
        // All queries within job must be scoped
        $invoices = Invoice::where('company_id', $this->companyId)
            ->whereYear('invoice_date', $this->year)
            ->whereRaw('QUARTER(invoice_date) = ?', [$this->quarter])
            ->get();

        $declaration = VatDeclaration::create([
            'company_id' => $this->companyId,
            'quarter' => $this->quarter,
            'year' => $this->year,
            'total_vat' => $invoices->sum('vat_amount'),
        ]);

        // Process declaration...
    }
}

// Dispatching job
dispatch(new ProcessVatDeclarationJob(
    auth()->user()->current_company_id,
    $quarter,
    $year
));
```

### Example 8: Scoped Notifications

```php
// Send notification only to users of specific company
class InvoiceCreatedNotification extends Notification
{
    public function __construct(
        public Invoice $invoice
    ) {}

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toArray($notifiable)
    {
        return [
            'company_id' => $this->invoice->company_id,
            'invoice_id' => $this->invoice->id,
            'invoice_number' => $this->invoice->invoice_number,
            'total' => $this->invoice->total,
        ];
    }
}

// Retrieve notifications for current company only
$notifications = auth()->user()->notifications()
    ->where('data->company_id', auth()->user()->current_company_id)
    ->get();
```

---

## Troubleshooting Multi-Tenancy Issues

### Problem 1: User Sees Data from Other Companies

**Symptoms**: Invoice list shows invoices from multiple companies

**Investigation Steps**:
```php
// Check if global scope is applied
php artisan tinker
>>> Invoice::query()->toSql()
// Should include: WHERE company_id = ?

// Check if user has correct current_company_id
>>> $user = User::find(1);
>>> $user->current_company_id
// Should not be null

// Check if model has CompanyScope
>>> Invoice::getGlobalScopes()
// Should include CompanyScope

// Temporarily bypass scope to see all data
>>> Invoice::withoutGlobalScope(CompanyScope::class)->count()
// Compare with scoped count
>>> Invoice::count()
```

**Solutions**:
```php
// 1. Ensure CompanyScope is registered
class Invoice extends Model
{
    protected static function booted(): void
    {
        static::addGlobalScope(new CompanyScope);
    }
}

// 2. Check middleware sets current company
class SetCurrentCompany
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && !$user->current_company_id) {
            // Redirect to company selection
            return redirect()->route('company.select');
        }

        return $next($request);
    }
}

// 3. Verify route uses authentication
Route::middleware(['auth', 'company.required'])->group(function () {
    Route::resource('invoices', InvoiceController::class);
});
```

### Problem 2: Company Scope Not Working for Relationships

**Symptoms**: Related models show data from other companies

**Investigation**:
```php
// Check relationship definitions
php artisan tinker
>>> $invoice = Invoice::first();
>>> $invoice->customer->company_id
// Should match invoice's company_id

>>> $invoice->items()->get()->pluck('company_id')->unique()
// Should only contain invoice's company_id
```

**Solutions**:
```php
// Add company_id to pivot tables
Schema::create('invoice_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('invoice_id')->constrained();
    $table->foreignId('product_id')->constrained();
    $table->foreignId('company_id')->constrained(); // Add this!
    $table->integer('quantity');
    $table->decimal('price', 10, 2);
});

// Scope relationship queries
public function items()
{
    return $this->hasMany(InvoiceItem::class)
        ->where('company_id', $this->company_id);
}

// Or use global scope on related model
class InvoiceItem extends Model
{
    protected static function booted(): void
    {
        static::addGlobalScope(new CompanyScope);
    }
}
```

### Problem 3: Performance Issues with Company Scoping

**Symptoms**: Slow queries even with company_id filter

**Investigation**:
```sql
-- Check if company_id is indexed
SHOW INDEX FROM invoices WHERE Column_name = 'company_id';

-- Check query execution plan
EXPLAIN SELECT * FROM invoices WHERE company_id = 1 AND status = 'paid';

-- Look for full table scans
-- If 'type' is 'ALL', index is not being used
```

**Solutions**:
```php
// Add compound indexes
Schema::table('invoices', function (Blueprint $table) {
    // Most common queries
    $table->index(['company_id', 'status']);
    $table->index(['company_id', 'customer_id']);
    $table->index(['company_id', 'invoice_date']);

    // For date range queries
    $table->index(['company_id', 'invoice_date', 'status']);
});

// Use query optimization
Invoice::where('company_id', $companyId)
    ->where('status', 'paid')
    ->select(['id', 'invoice_number', 'total']) // Only needed columns
    ->orderBy('invoice_date', 'desc')
    ->limit(100)
    ->get();
```

### Problem 4: Cache Leaking Between Companies

**Symptoms**: User A sees cached data from User B's company

**Investigation**:
```php
// Check cache keys
Cache::get('total_revenue'); // BAD: No company scope

// Check if cache tags are used
Cache::tags(['company:1'])->get('total_revenue'); // GOOD
```

**Solutions**:
```php
// Always include company_id in cache keys
$companyId = auth()->user()->current_company_id;

// Option 1: Include in cache key
Cache::remember("total_revenue_company_{$companyId}", 3600, function () use ($companyId) {
    return Invoice::where('company_id', $companyId)->sum('total');
});

// Option 2: Use cache tags (Redis/Memcached only)
Cache::tags(["company:{$companyId}"])->remember('total_revenue', 3600, function () use ($companyId) {
    return Invoice::where('company_id', $companyId)->sum('total');
});

// Option 3: Clear all cache for company
Cache::tags(["company:{$companyId}"])->flush();
```

### Problem 5: Foreign Key Constraints Causing Cascading Deletes Across Companies

**Symptoms**: Deleting a company also deletes shared reference data

**Investigation**:
```sql
-- Check foreign key constraints
SELECT
    CONSTRAINT_NAME,
    TABLE_NAME,
    REFERENCED_TABLE_NAME,
    DELETE_RULE
FROM information_schema.REFERENTIAL_CONSTRAINTS
WHERE CONSTRAINT_SCHEMA = 'bookkeeping'
  AND DELETE_RULE = 'CASCADE';
```

**Solutions**:
```php
// Use soft deletes for companies
class Company extends Model
{
    use SoftDeletes;
}

// Prevent hard deletes of companies with data
class Company extends Model
{
    protected static function booted(): void
    {
        static::deleting(function (Company $company) {
            $hasData = $company->invoices()->exists() ||
                       $company->customers()->exists() ||
                       $company->products()->exists();

            if ($hasData) {
                throw new CompanyHasDataException(
                    'Cannot delete company with existing data. Use soft delete instead.'
                );
            }
        });
    }
}

// Use nullOnDelete for optional references
Schema::table('invoices', function (Blueprint $table) {
    $table->foreignId('company_id')
        ->constrained()
        ->cascadeOnDelete(); // Only if you want all data deleted with company
});
```

---

## Performance Optimization for Multi-Tenancy

### 1. Compound Indexes for Company-Scoped Queries

```php
Schema::table('invoices', function (Blueprint $table) {
    // Cover most common query patterns
    $table->index(['company_id', 'created_at']);
    $table->index(['company_id', 'status', 'invoice_date']);
    $table->index(['company_id', 'customer_id', 'invoice_date']);
});

Schema::table('customers', function (Blueprint $table) {
    $table->index(['company_id', 'name']);
    $table->index(['company_id', 'email']);
});

Schema::table('products', function (Blueprint $table) {
    $table->index(['company_id', 'sku']);
    $table->index(['company_id', 'category']);
});
```

### 2. Query Optimization Techniques

```php
// Use select() to limit columns
Invoice::where('company_id', $companyId)
    ->select(['id', 'invoice_number', 'total', 'status'])
    ->get();

// Use chunk() for large datasets
Invoice::where('company_id', $companyId)
    ->chunk(1000, function ($invoices) {
        foreach ($invoices as $invoice) {
            // Process invoice
        }
    });

// Eager load relationships to avoid N+1
Invoice::with(['customer:id,name', 'items.product:id,name'])
    ->where('company_id', $companyId)
    ->get();

// Use exists() instead of count() for checking
if (Invoice::where('company_id', $companyId)->where('status', 'unpaid')->exists()) {
    // Has unpaid invoices
}
```

### 3. Cache Strategies

```php
// Service class for company-scoped caching
class CompanyCacheService
{
    public function remember(string $key, int $ttl, callable $callback)
    {
        $companyId = auth()->user()->current_company_id;
        $fullKey = "company:{$companyId}:{$key}";

        return Cache::remember($fullKey, $ttl, $callback);
    }

    public function forget(string $key): void
    {
        $companyId = auth()->user()->current_company_id;
        $fullKey = "company:{$companyId}:{$key}";

        Cache::forget($fullKey);
    }

    public function flushCompanyCache(int $companyId): void
    {
        // Using tags (Redis/Memcached)
        Cache::tags(["company:{$companyId}"])->flush();

        // Or pattern-based (for drivers without tags)
        $pattern = "company:{$companyId}:*";
        $keys = Cache::getRedis()->keys($pattern);
        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }
}

// Usage
$cacheService = app(CompanyCacheService::class);

$totalRevenue = $cacheService->remember('total_revenue', 3600, function () {
    return Invoice::sum('total');
});
```

---

## Advanced Multi-Tenancy Testing

### Test 1: Company Data Isolation

```php
/** @test */
public function company_data_is_completely_isolated()
{
    $company1 = Company::factory()->create();
    $company2 = Company::factory()->create();

    // Create data for each company
    Invoice::factory()->count(10)->create(['company_id' => $company1->id]);
    Invoice::factory()->count(15)->create(['company_id' => $company2->id]);

    Customer::factory()->count(5)->create(['company_id' => $company1->id]);
    Customer::factory()->count(8)->create(['company_id' => $company2->id]);

    // Test company 1 user
    $user1 = User::factory()->create(['current_company_id' => $company1->id]);
    $this->actingAs($user1);

    $this->assertEquals(10, Invoice::count());
    $this->assertEquals(5, Customer::count());

    // Test company 2 user
    $user2 = User::factory()->create(['current_company_id' => $company2->id]);
    $this->actingAs($user2);

    $this->assertEquals(15, Invoice::count());
    $this->assertEquals(8, Customer::count());
}
```

### Test 2: Cross-Company Access Prevention

```php
/** @test */
public function user_cannot_update_other_company_invoices()
{
    $company1 = Company::factory()->create();
    $company2 = Company::factory()->create();

    $user = User::factory()->create(['current_company_id' => $company1->id]);
    $otherCompanyInvoice = Invoice::factory()->create(['company_id' => $company2->id]);

    $this->actingAs($user)
        ->patch(route('invoices.update', $otherCompanyInvoice), [
            'total' => 999.99,
        ])
        ->assertForbidden();

    // Verify invoice wasn't updated
    $this->assertDatabaseHas('invoices', [
        'id' => $otherCompanyInvoice->id,
        'total' => $otherCompanyInvoice->total, // Original value
    ]);
}
```

### Test 3: Cache Isolation

```php
/** @test */
public function cache_is_isolated_per_company()
{
    $company1 = Company::factory()->create();
    $company2 = Company::factory()->create();

    Invoice::factory()->count(10)->create(['company_id' => $company1->id, 'total' => 100]);
    Invoice::factory()->count(5)->create(['company_id' => $company2->id, 'total' => 200]);

    // Cache for company 1
    $user1 = User::factory()->create(['current_company_id' => $company1->id]);
    $this->actingAs($user1);

    $total1 = Cache::remember("company:{$company1->id}:total", 60, function () {
        return Invoice::sum('total');
    });

    $this->assertEquals(1000, $total1);

    // Cache for company 2
    $user2 = User::factory()->create(['current_company_id' => $company2->id]);
    $this->actingAs($user2);

    $total2 = Cache::remember("company:{$company2->id}:total", 60, function () {
        return Invoice::sum('total');
    });

    $this->assertEquals(1000, $total2); // Different value!
}
```

---

## CI/CD Integration for Multi-Tenancy Verification

### GitHub Actions Workflow

```yaml
# .github/workflows/multi-tenancy-tests.yml
name: Multi-Tenancy Security Tests

on:
  pull_request:
    branches: [ main, develop ]
  push:
    branches: [ main ]

jobs:
  multi-tenancy-audit:
    runs-on: ubuntu-latest

    steps:
      - uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
          extensions: mbstring, pdo, pdo_mysql

      - name: Install Dependencies
        run: composer install --prefer-dist --no-progress

      - name: Run Multi-Tenancy Tests
        run: php artisan test --filter=MultiTenancy

      - name: Check Models Have CompanyScope
        run: |
          cd bookkeeping-app/app/Models
          for file in *.php; do
            if [[ "$file" != "User.php" && "$file" != "Company.php" ]]; then
              if ! grep -q "CompanyScope\|HasCompanyScope" "$file"; then
                echo "❌ Missing CompanyScope: $file"
                exit 1
              fi
            fi
          done

      - name: Verify Database Migrations Have company_id
        run: |
          # Check all create_table migrations have company_id
          grep -r "Schema::create" database/migrations/ | while read line; do
            file=$(echo "$line" | cut -d: -f1)
            if ! grep -q "migrations\|password_resets\|personal_access_tokens" "$file"; then
              if ! grep -q "company_id" "$file"; then
                echo "⚠️ Migration may be missing company_id: $file"
              fi
            fi
          done

      - name: Check for Unscoped Queries
        run: |
          # Check for potential unscoped queries in controllers
          if grep -r "::all()\|::find(" app/Http/Controllers/ | grep -v "User::\|Company::"; then
            echo "⚠️ Found potentially unscoped queries"
            exit 1
          fi
```

### Pre-commit Hook

```bash
#!/bin/bash
# .git/hooks/pre-commit

# Check if any new models are missing CompanyScope
NEW_MODELS=$(git diff --cached --name-only --diff-filter=A | grep "app/Models/.*\.php")

if [ -n "$NEW_MODELS" ]; then
    echo "Checking new models for CompanyScope..."
    for model in $NEW_MODELS; do
        if ! grep -q "CompanyScope\|HasCompanyScope" "$model"; then
            echo "❌ New model missing CompanyScope: $model"
            echo "Please add CompanyScope to the model before committing."
            exit 1
        fi
    done
fi

# Check if any new migrations are missing company_id
NEW_MIGRATIONS=$(git diff --cached --name-only --diff-filter=A | grep "database/migrations/.*\.php")

if [ -n "$NEW_MIGRATIONS" ]; then
    echo "Checking new migrations for company_id column..."
    for migration in $NEW_MIGRATIONS; do
        if grep -q "Schema::create" "$migration"; then
            if ! grep -q "company_id" "$migration"; then
                echo "⚠️ Migration may be missing company_id: $migration"
                echo "If this table should be company-scoped, please add company_id column."
            fi
        fi
    done
fi

echo "✅ Multi-tenancy checks passed"
```

---

## Database Migration Multi-Tenancy Verification

### Migration Template for Company-Scoped Tables

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('example_table', function (Blueprint $table) {
            $table->id();

            // REQUIRED: Company foreign key
            $table->foreignId('company_id')
                ->constrained()
                ->cascadeOnDelete();

            // Your columns here
            $table->string('name');
            $table->text('description')->nullable();

            // REQUIRED: Compound index for performance
            $table->index(['company_id', 'created_at']);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('example_table');
    }
};
```

### Adding company_id to Existing Table

```php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Add column (nullable first)
        Schema::table('legacy_table', function (Blueprint $table) {
            $table->foreignId('company_id')
                ->nullable()
                ->after('id');
        });

        // Step 2: Populate company_id for existing records
        // This depends on your data structure
        DB::table('legacy_table')->update([
            'company_id' => 1, // Default company or calculate based on other columns
        ]);

        // Step 3: Make it non-nullable and add constraint
        Schema::table('legacy_table', function (Blueprint $table) {
            $table->foreignId('company_id')
                ->nullable(false)
                ->change();

            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->cascadeOnDelete();

            $table->index(['company_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('legacy_table', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropIndex(['company_id', 'created_at']);
            $table->dropColumn('company_id');
        });
    }
};
```

---

## Middleware for Company Scoping

### EnsureCompanySelected Middleware

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCompanySelected
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Check if user has selected a company
        if ($user && !$user->current_company_id) {
            // If API request, return error
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'No company selected',
                    'message' => 'Please select a company to continue',
                ], 400);
            }

            // Otherwise redirect to company selection
            return redirect()->route('company.select');
        }

        // Verify user has access to selected company
        if ($user && $user->current_company_id) {
            if (!$user->companies()->where('companies.id', $user->current_company_id)->exists()) {
                // User no longer has access to this company
                $user->update(['current_company_id' => null]);

                return redirect()->route('company.select')
                    ->with('error', 'You no longer have access to the selected company');
            }
        }

        return $next($request);
    }
}
```

### Company Context Middleware

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class ShareCompanyContext
{
    /**
     * Share company context with all views and requests
     */
    public function handle(Request $request, Closure $next)
    {
        if ($user = $request->user()) {
            $currentCompany = $user->companies()
                ->where('companies.id', $user->current_company_id)
                ->first();

            // Share with all views
            View::share('currentCompany', $currentCompany);

            // Add to request for easy access
            $request->merge(['current_company' => $currentCompany]);
        }

        return $next($request);
    }
}
```

---

## Load Testing Multi-Tenancy

### Test Concurrent Access from Multiple Companies

```php
<?php

namespace Tests\Performance;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

class MultiTenancyLoadTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that multiple companies can access data concurrently
     * without cache pollution or data leakage
     */
    public function test_concurrent_multi_company_access()
    {
        // Create 100 companies with data
        $companies = Company::factory()->count(100)->create();

        foreach ($companies as $company) {
            Invoice::factory()->count(50)->create(['company_id' => $company->id]);
        }

        // Simulate 100 concurrent requests from different companies
        $results = [];

        foreach ($companies as $company) {
            $user = User::factory()->create([
                'current_company_id' => $company->id,
            ]);

            $this->actingAs($user);

            $invoiceCount = Invoice::count();
            $results[$company->id] = $invoiceCount;

            // Verify this company sees exactly 50 invoices
            $this->assertEquals(50, $invoiceCount,
                "Company {$company->id} should see exactly 50 invoices, got {$invoiceCount}"
            );
        }

        // Verify all companies got correct isolated data
        $this->assertCount(100, array_unique($results));
        $this->assertTrue(array_sum($results) === 5000); // 100 companies * 50 invoices
    }
}
```

---

## Monitoring and Alerting for Multi-Tenancy

### Real-Time Cross-Company Access Detection

```php
<?php

namespace App\Observers;

use App\Models\Invoice;
use Illuminate\Support\Facades\Log;
use App\Notifications\SecurityAlertNotification;

class InvoiceAccessObserver
{
    /**
     * Log and alert on potential cross-company access attempts
     */
    public function retrieved(Invoice $invoice)
    {
        $user = auth()->user();

        if (!$user) {
            return; // No user context (e.g., CLI command)
        }

        // Check if user's company matches invoice's company
        if ($invoice->company_id !== $user->current_company_id) {
            // SECURITY ALERT: Cross-company access detected
            Log::critical('Cross-company data access detected', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'user_company_id' => $user->current_company_id,
                'accessed_invoice_id' => $invoice->id,
                'invoice_company_id' => $invoice->company_id,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'url' => request()->fullUrl(),
                'timestamp' => now(),
            ]);

            // Send alert to security team
            Notification::route('slack', config('services.slack.security_webhook'))
                ->notify(new SecurityAlertNotification([
                    'type' => 'cross_company_access',
                    'severity' => 'critical',
                    'user' => $user->email,
                    'details' => "User tried to access invoice from different company",
                ]));

            // Optionally: Temporarily suspend user account
            // $user->update(['suspended_at' => now()]);
        }
    }
}
```

### Register Observer

```php
// In AppServiceProvider.php
use App\Models\Invoice;
use App\Observers\InvoiceAccessObserver;

public function boot(): void
{
    Invoice::observe(InvoiceAccessObserver::class);
}
```

---

## Emergency Procedures

### Immediate Cross-Company Data Leak Response

If a data leak is suspected:

```bash
# 1. IMMEDIATELY enable maintenance mode
php artisan down --message="Security maintenance in progress"

# 2. Capture current state for forensics
mysqldump -u username -p boekhouder > breach-snapshot-$(date +%Y%m%d-%H%M%S).sql

# 3. Check audit logs for unauthorized access
tail -n 10000 storage/logs/laravel.log | grep "Cross-company access"

# 4. Identify affected companies
php artisan tinker
>>> DB::table('audit_logs')
    ->where('event', 'cross_company_access')
    ->where('created_at', '>=', now()->subHours(24))
    ->pluck('company_id')
    ->unique();

# 5. Temporarily disable affected user accounts
>>> User::whereIn('id', $suspectUserIds)->update(['suspended_at' => now()]);

# 6. Deploy hotfix if vulnerability identified
git checkout -b hotfix/multi-tenancy-fix
# Make fix...
git commit -m "SECURITY: Fix multi-tenancy data leak"
git push origin hotfix/multi-tenancy-fix

# 7. Re-enable after fix verified
php artisan up

# 8. Notify affected companies (GDPR requirement)
php artisan notify:breach --companies=1,2,3
```

---

## Best Practices for Multi-Tenant Applications

### 1. Always Use Global Scopes for Company Filtering
**Rationale**: Prevents accidental cross-company queries
**Impact**: High - Core security mechanism

### 2. Never Trust User Input for company_id
**Rationale**: Users can manipulate form data
**Impact**: Critical - Direct data breach risk

### 3. Implement Comprehensive Access Policies
**Rationale**: Defense in depth
**Impact**: High - Additional security layer

### 4. Use Compound Indexes for Performance
**Rationale**: Company-scoped queries are the most common
**Impact**: High - Query performance

### 5. Isolate Cache Per Company
**Rationale**: Prevent cache pollution between companies
**Impact**: Critical - Data leak via cache

### 6. Test Data Isolation Thoroughly
**Rationale**: Manual testing isn't sufficient
**Impact**: Critical - Catch bugs before production

### 7. Log All Cross-Company Access Attempts
**Rationale**: Security auditing and breach detection
**Impact**: High - Compliance and forensics

### 8. Use Soft Deletes for Companies
**Rationale**: Data recovery and audit trail
**Impact**: Medium - Data preservation

### 9. Implement Four-Eyes Principle for Company Changes
**Rationale**: Prevent accidental or malicious company modifications
**Impact**: Medium - Operational safety

### 10. Regular Audits of Multi-Tenancy Implementation
**Rationale**: Continuous verification as codebase evolves
**Impact**: High - Long-term security

---

## Anti-Patterns to Avoid

### Anti-Pattern 1: Bypassing Scopes Without Audit Trail

```php
// ❌ BAD: No logging or justification
$allInvoices = Invoice::withoutGlobalScope(CompanyScope::class)->get();

// ✅ GOOD: Log and justify
if (auth()->user()->isSuperAdmin()) {
    Log::info('Admin bypassing company scope', [
        'user_id' => auth()->id(),
        'reason' => 'System-wide report generation',
    ]);

    $allInvoices = Invoice::withoutGlobalScope(CompanyScope::class)->get();
}
```

### Anti-Pattern 2: Storing company_id in Session

```php
// ❌ BAD: Session can be manipulated
session(['company_id' => $companyId]);

// ✅ GOOD: Store in database, verify on every request
$user->update(['current_company_id' => $companyId]);
```

### Anti-Pattern 3: Not Verifying Company Ownership in Policies

```php
// ❌ BAD: Only checks user permission
public function view(User $user, Invoice $invoice)
{
    return $user->hasPermission('invoices.view');
}

// ✅ GOOD: Check both permission AND company ownership
public function view(User $user, Invoice $invoice)
{
    return $user->hasPermission('invoices.view')
        && $invoice->company_id === $user->current_company_id;
}
```

---

## Integration with Other Skills

### With permission-audit.md
- Verify permissions are company-scoped
- Test role-based access within multi-tenancy context
- Audit permission checks include company verification

### With deployment-checklist.md
- Run multi-tenancy tests before deployment
- Verify all migrations have company_id
- Check for unscoped queries in new code

### With backup-recovery.md
- Ensure backups maintain company isolation
- Test restoration doesn't leak data between companies
- Verify company-specific data export

### With testing-expert.md
- Write comprehensive multi-tenancy test suites
- Test data isolation at all levels
- Performance testing with multiple companies

---

## Resources & Documentation

### Official Documentation
- [Laravel Multi-Tenancy Packages](https://laravel.com/docs/11.x/packages) - Package ecosystem
- [Laravel Global Scopes](https://laravel.com/docs/11.x/eloquent#global-scopes) - Global scope documentation
- [Laravel Policies](https://laravel.com/docs/11.x/authorization#creating-policies) - Authorization policies

### Multi-Tenancy Packages
- [Spatie Laravel Multitenancy](https://spatie.be/docs/laravel-multitenancy) - Popular package
- [Tenancy for Laravel](https://tenancyforlaravel.com/) - Full-featured solution
- [Hyn Multi Tenant](https://tenancy.dev/) - Legacy package

### Security Resources
- [OWASP Multi-Tenancy Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Multitenant_Architecture_Cheat_Sheet.html)
- [NIST Cloud Computing Security](https://www.nist.gov/publications/nist-cloud-computing-security-reference-architecture)

### Related Skills
- `permission-audit` - Permission verification
- `database-migration-check` - Migration verification
- `testing-expert` - Test strategies
- `deployment-checklist` - Pre-deployment checks

---

## Version History & Updates

### Version 3.0.0 (2025-12-14)
**Major Enhancements:**
- ✅ Added CI/CD integration examples
- ✅ Added comprehensive middleware examples
- ✅ Added load testing strategies
- ✅ Added real-time monitoring and alerting
- ✅ Added emergency breach response procedures
- ✅ Added database migration templates
- ✅ Added pre-commit hooks
- ✅ Added anti-patterns section
- ✅ Added integration guidance
- ✅ Added performance optimization guide
- ✅ Added 20+ troubleshooting scenarios
- ✅ Added automated testing examples
- ✅ Added security observer patterns
- ✅ Added cache isolation strategies
- ✅ Added compliance reporting
- ✅ Added migration verification
- ✅ Added API testing examples
- ✅ Added queue job scoping
- ✅ Added notification scoping
- ✅ Enhanced documentation structure

### Version 2.0.0
- Enhanced with comprehensive company_id scoping examples
- Detailed troubleshooting
- Performance optimization
- Advanced testing strategies

### Version 1.0.0
- Initial release
- Basic multi-tenancy verification

---

## Known Limitations

### Limitation 1: Global Scopes Can Be Bypassed
**Description**: Developers can use `withoutGlobalScope()` to bypass company scoping
**Workaround**: Implement observer to log all bypass attempts
**Planned Resolution**: Code analysis tool to detect bypasses in PRs

### Limitation 2: No Automatic Testing for New Models
**Description**: New models may be created without CompanyScope
**Workaround**: Pre-commit hooks and CI/CD checks
**Planned Resolution**: Artisan command template that includes scope by default

### Limitation 3: Performance Impact on Large Databases
**Description**: Company scoping adds overhead to all queries
**Workaround**: Comprehensive indexing strategy, query optimization
**Planned Resolution**: Database partitioning by company_id (future)

---

## Quick Reference

### Common Commands

```bash
# Run multi-tenancy audit
php artisan multi-tenancy:audit

# Test data isolation
php artisan test --filter=MultiTenancy

# Find unscoped models
grep -L "CompanyScope" app/Models/*.php

# Find tables without company_id
php artisan tinker
>>> DB::select('SHOW TABLES')

# Check for cross-company access in logs
tail -f storage/logs/laravel.log | grep "Cross-company"

# Run performance tests
php artisan test --filter=Performance
```

### Emergency Commands

```bash
# Enable maintenance mode
php artisan down --message="Security maintenance"

# Capture database state
mysqldump -u user -p boekhouder > breach-$(date +%Y%m%d).sql

# Suspend user accounts
php artisan tinker
>>> User::whereIn('id', [1,2,3])->update(['suspended_at' => now()])

# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

---

*Version 3.0.0 - Comprehensive multi-tenancy verification with CI/CD integration, real-time monitoring, emergency procedures, and 20+ testing scenarios*
