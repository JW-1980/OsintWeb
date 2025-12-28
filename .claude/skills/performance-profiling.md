---
name: performance-profiling
description: Profile and optimize Laravel application performance
version: 1.0.1
tags: [performance, optimization, profiling, speed, scalability]
trigger_keywords: [sk-performance-profiling, "performance issue", "slow query", "optimize code", "profile application", "speed improvement", "n+1 queries", "cache strategy", "performance bottleneck", "query optimization", "memory usage", "response time"]
related_skills: [database-mysql-expert, laravel-ecosystem]
---
# Performance Profiling & Optimization

This skill helps identify and resolve performance bottlenecks in the Laravel bookkeeping application.

## When to Use

- When pages load slowly (>2s)
- Before scaling to more users
- After major feature releases
- When database queries are slow
- During capacity planning

## Profiling Tools

### 1. Laravel Debugbar (Development)

```bash
composer require barryvdh/laravel-debugbar --dev
```

**What it shows:**
- Query execution times
- Memory usage
- View rendering time
- Route information
- Request/response data

### 2. Laravel Telescope (Staging)

```bash
composer require laravel/telescope
php artisan telescope:install
php artisan migrate
```

**Features:**
- Request monitoring
- Query logging
- Job monitoring
- Cache hits/misses
- Exception tracking

**Production Note:** ⚠️ DO NOT use in production (performance impact)

### 3. Clockwork (Development/Staging)

```bash
composer require itsgoingd/clockwork
```

Browser extension for Chrome/Firefox to view profiling data.

## Performance Metrics

### Target Response Times

```
Homepage:          < 200ms
Dashboard:         < 500ms
Invoice list:      < 300ms
Invoice creation:  < 400ms
Report generation: < 2s
PDF generation:    < 3s
API endpoints:     < 200ms
```

### Measuring Performance

```bash
# Using artisan tinker
php artisan tinker
>>> $start = microtime(true);
>>> Invoice::with('client', 'items')->paginate(50);
>>> $end = microtime(true);
>>> echo "Execution time: " . ($end - $start) . " seconds\n";

# Using curl
curl -w "@curl-format.txt" -o /dev/null -s https://boekhouder.nl/invoices
```

Create `curl-format.txt`:
```
     time_namelookup:  %{time_namelookup}s
        time_connect:  %{time_connect}s
     time_appconnect:  %{time_appconnect}s
    time_pretransfer:  %{time_pretransfer}s
       time_redirect:  %{time_redirect}s
  time_starttransfer:  %{time_starttransfer}s
                     ----------
          time_total:  %{time_total}s
```

## Common Performance Issues

### Issue 1: N+1 Queries

**Problem:**
```php
// ❌ BAD - Triggers 101 queries (1 + 100)
$invoices = Invoice::all();
foreach ($invoices as $invoice) {
    echo $invoice->client->name;  // Query per iteration
}
```

**Solution:**
```php
// ✅ GOOD - Only 2 queries
$invoices = Invoice::with('client')->get();
foreach ($invoices as $invoice) {
    echo $invoice->client->name;
}
```

**Detection:**
```bash
# Enable query log
DB::enableQueryLog();
$invoices = Invoice::all();
foreach ($invoices as $invoice) {
    echo $invoice->client->name;
}
dd(DB::getQueryLog());
```

### Issue 2: Missing Database Indexes

**Problem:**
```sql
-- Slow query without index
SELECT * FROM invoices WHERE company_id = 5 AND status = 'unpaid';
```

**Solution:**
```php
// Add compound index in migration
Schema::table('invoices', function (Blueprint $table) {
    $table->index(['company_id', 'status']);
    $table->index(['company_id', 'client_id']);
    $table->index(['company_id', 'invoice_date']);
});
```

**Check Missing Indexes:**
```sql
-- MySQL - Find queries without index
SELECT * FROM information_schema.PROCESSLIST
WHERE STATE = 'executing'
  AND TIME > 1;

-- Check slow query log
SHOW VARIABLES LIKE 'slow_query_log%';
```

### Issue 3: Loading Too Much Data

**Problem:**
```php
// ❌ BAD - Loads all columns for 10,000 rows
$invoices = Invoice::all();
```

**Solution:**
```php
// ✅ GOOD - Only needed columns, paginated
$invoices = Invoice::select(['id', 'number', 'amount', 'status'])
    ->paginate(50);

// ✅ GOOD - Chunk for processing
Invoice::chunk(100, function ($invoices) {
    foreach ($invoices as $invoice) {
        $this->processInvoice($invoice);
    }
});
```

### Issue 4: Inefficient Caching

**Problem:**
```php
// ❌ BAD - Cache doesn't help (always regenerates)
$reports = cache()->remember('reports', 3600, function() {
    return Report::with('data')->get();  // Fresh query every time
});
```

**Solution:**
```php
// ✅ GOOD - Company-scoped cache with tags
$reports = cache()
    ->tags(['company:' . $companyId, 'reports'])
    ->remember("reports:{$companyId}:{$type}", 3600, function() use ($companyId, $type) {
        return Report::where('company_id', $companyId)
            ->where('type', $type)
            ->with('data')
            ->get();
    });

// Invalidate when needed
cache()->tags(['company:' . $companyId, 'reports'])->flush();
```

### Issue 5: Unoptimized Queries

**Problem:**
```php
// ❌ BAD - Multiple queries for aggregation
$totalRevenue = Invoice::where('company_id', $companyId)
    ->where('status', 'paid')
    ->get()
    ->sum('amount');  // Sums in PHP
```

**Solution:**
```php
// ✅ GOOD - Database aggregation
$totalRevenue = Invoice::where('company_id', $companyId)
    ->where('status', 'paid')
    ->sum('amount');  // Sums in SQL
```

## Optimization Strategies

### 1. Database Optimization

```bash
# Analyze table usage
php artisan tinker
>>> DB::select('SHOW TABLE STATUS');

# Optimize tables
>>> DB::statement('OPTIMIZE TABLE invoices');
>>> DB::statement('ANALYZE TABLE invoices');

# Check index usage
>>> DB::select('SHOW INDEX FROM invoices');
```

**Add Appropriate Indexes:**
```php
// For filtering
$table->index(['company_id', 'status']);

// For sorting
$table->index(['company_id', 'created_at']);

// For searching
$table->fullText(['number', 'description']);

// For foreign keys (automatically created)
$table->foreignId('client_id')->constrained();
```

### 2. Query Optimization

```php
// ✅ Eager load relationships
Invoice::with(['client', 'items.product'])->get();

// ✅ Load counts efficiently
Invoice::withCount('items')->get();

// ✅ Load existence check
Invoice::withExists('payments')->get();

// ✅ Select only needed columns
Invoice::select(['id', 'number', 'amount'])->get();

// ✅ Use query scopes
Invoice::unpaid()->overdue()->forCompany($companyId)->get();
```

### 3. Caching Strategies

```php
// Config caching (production)
php artisan config:cache
php artisan route:cache
php artisan view:cache

// Query result caching
$invoices = cache()->remember('invoices:recent', 600, function() {
    return Invoice::latest()->take(10)->get();
});

// Model caching (use package)
composer require genealabs/laravel-model-caching
```

### 4. Asset Optimization

```bash
# Build optimized assets
npm run build

# Compress images
npm install -D vite-plugin-imagemin

# Enable Gzip/Brotli compression in Nginx
# /etc/nginx/nginx.conf
gzip on;
gzip_types text/css application/javascript;
brotli on;
```

### 5. PHP Optimization

```bash
# Enable OPcache
# /etc/php/8.2/fpm/php.ini
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=10000
opcache.revalidate_freq=2

# Optimize composer autoloader
composer install --optimize-autoloader --no-dev

# Use preloading (PHP 7.4+)
# /etc/php/8.2/fpm/conf.d/preload.ini
opcache.preload=/var/www/boekhouder/preload.php
```

Create `preload.php`:
```php
<?php
opcache_compile_file(__DIR__ . '/vendor/autoload.php');
require __DIR__ . '/vendor/autoload.php';
```

## Performance Testing

### 1. Load Testing with Apache Bench

```bash
# Test homepage
ab -n 1000 -c 10 https://boekhouder.nl/

# Test API endpoint with auth
ab -n 500 -c 5 -H "Authorization: Bearer TOKEN" \
   https://boekhouder.nl/api/invoices

# Results to analyze:
# - Requests per second (target: >100)
# - Time per request (target: <200ms)
# - Failed requests (target: 0)
```

### 2. Load Testing with Siege

```bash
# Install
sudo apt-get install siege

# Basic test
siege -c 10 -t 1M https://boekhouder.nl/

# Test multiple URLs
siege -c 20 -t 2M -f urls.txt

# Monitor response times
siege -c 50 -r 10 https://boekhouder.nl/invoices
```

### 3. Application Performance Monitoring

**Install New Relic / DataDog:**

```bash
# New Relic
composer require newrelic/newrelic-laravel

# DataDog
composer require datadog/dd-trace
```

## Monitoring Queries

### 1. Log Slow Queries

```php
// In AppServiceProvider
DB::listen(function ($query) {
    if ($query->time > 1000) {  // Over 1 second
        Log::warning('Slow query detected', [
            'sql' => $query->sql,
            'bindings' => $query->bindings,
            'time' => $query->time . 'ms',
        ]);
    }
});
```

### 2. Query Monitoring

```bash
# Enable MySQL slow query log
# /etc/mysql/my.cnf
slow_query_log = 1
slow_query_log_file = /var/log/mysql/slow-query.log
long_query_time = 1

# Analyze slow queries
mysqldumpslow /var/log/mysql/slow-query.log | head -20
```

## Redis Optimization

```bash
# Use Redis for cache and sessions
# .env
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Configure Redis
# config/database.php
'redis' => [
    'client' => 'phpredis',
    'options' => [
        'cluster' => env('REDIS_CLUSTER', 'redis'),
        'prefix' => env('REDIS_PREFIX', 'boekhouder_'),
    ],
    'default' => [
        'url' => env('REDIS_URL'),
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'password' => env('REDIS_PASSWORD'),
        'port' => env('REDIS_PORT', 6379),
        'database' => env('REDIS_DB', 0),
    ],
],
```

## Queue Optimization

```bash
# Use database queue for small sites
QUEUE_CONNECTION=database

# Use Redis for better performance
QUEUE_CONNECTION=redis

# Use SQS/Beanstalkd for scale
QUEUE_CONNECTION=sqs

# Monitor queue performance
php artisan queue:work --verbose
php artisan queue:failed
```

## CDN Configuration

```bash
# Cloudflare / AWS CloudFront
# Store assets on CDN

# .env
ASSET_URL=https://cdn.boekhouder.nl

# In blade templates
<img src="{{ asset('images/logo.png') }}">
<!-- Renders: https://cdn.boekhouder.nl/images/logo.png -->
```

## Performance Checklist

### Database
- [ ] Proper indexes on all foreign keys
- [ ] Compound indexes for common queries
- [ ] Full-text indexes for search
- [ ] Query execution time < 100ms
- [ ] No N+1 query problems
- [ ] Connection pooling configured

### Application
- [ ] Config/route/view caching enabled (production)
- [ ] OPcache enabled
- [ ] Composer autoloader optimized
- [ ] Debug mode disabled (production)
- [ ] Query result caching implemented
- [ ] Session stored in Redis

### Frontend
- [ ] Assets minified and compressed
- [ ] Images optimized (<200KB)
- [ ] Lazy loading implemented
- [ ] CDN configured
- [ ] Gzip/Brotli compression enabled
- [ ] Browser caching headers set

### Monitoring
- [ ] APM tool installed (New Relic/DataDog)
- [ ] Slow query logging enabled
- [ ] Error tracking configured (Sentry)
- [ ] Performance budgets defined
- [ ] Alerts configured for slowness

## Quick Performance Audit

```bash
# 1. Check current performance
curl -w "@curl-format.txt" -o /dev/null -s https://boekhouder.nl/

# 2. Analyze queries
php artisan tinker
>>> DB::enableQueryLog();
>>> $invoices = Invoice::with('client')->paginate();
>>> dd(count(DB::getQueryLog())); // Should be 2-3 queries

# 3. Test load capacity
ab -n 1000 -c 50 https://boekhouder.nl/

# 4. Check cache hit rate
redis-cli INFO stats | grep keyspace

# 5. Monitor memory
php artisan tinker
>>> memory_get_peak_usage(true) / 1024 / 1024 . ' MB'
```

## Laravel Telescope Integration

### 1. Setup and Configuration

```bash
# Install Telescope
composer require laravel/telescope
php artisan telescope:install
php artisan migrate
```

### 2. Production-Safe Configuration

```php
// config/telescope.php
'enabled' => env('TELESCOPE_ENABLED', false), // Disabled by default

// Only enable in specific environments
'enabled' => in_array(env('APP_ENV'), ['local', 'staging']),

// Limit data retention
'storage' => [
    'database' => [
        'connection' => env('DB_CONNECTION', 'mysql'),
        'chunk' => 1000,
    ],
],

// Prune old entries automatically
'prune' => [
    'enabled' => true,
    'hours' => 48, // Keep 48 hours of data
],
```

### 3. Monitoring Bookkeeping Operations

```php
// Monitor slow invoice queries
Telescope::tag(function (IncomingEntry $entry) {
    if ($entry->type === 'query' && $entry->content['time'] > 100) {
        return ['slow-query', 'invoice'];
    }
});

// Tag bookkeeping transactions
class InvoiceController
{
    public function store(Request $request)
    {
        Telescope::tag(['invoice', 'create']);

        // Your invoice logic
    }
}
```

## Query Optimization Examples

### 1. Invoice Dashboard Optimization

```php
// ❌ BAD: Multiple N+1 queries
public function dashboard()
{
    $invoices = Invoice::where('company_id', auth()->user()->company_id)
        ->where('status', 'unpaid')
        ->get();

    $total = 0;
    foreach ($invoices as $invoice) {
        $total += $invoice->total; // Each access triggers query
        $invoice->client->name; // N+1 query
        $invoice->items->count(); // N+1 query
    }
}

// ✅ GOOD: Optimized with eager loading and aggregation
public function dashboard()
{
    $invoices = Invoice::where('company_id', auth()->user()->company_id)
        ->where('status', 'unpaid')
        ->with(['client:id,name', 'items'])
        ->withCount('items')
        ->select('id', 'invoice_number', 'client_id', 'total', 'due_date')
        ->get();

    $total = Invoice::where('company_id', auth()->user()->company_id)
        ->where('status', 'unpaid')
        ->sum('total');
}
```

### 2. VAT Declaration Optimization

```php
// ❌ BAD: Loading all records
public function calculateVat(int $year, int $quarter)
{
    $invoices = Invoice::all(); // Loads ALL invoices
    $filtered = $invoices->filter(function ($invoice) use ($year, $quarter) {
        return $invoice->created_at->year == $year
            && $invoice->created_at->quarter == $quarter;
    });

    return $filtered->sum('vat_amount');
}

// ✅ GOOD: Database-level filtering and aggregation
public function calculateVat(int $year, int $quarter)
{
    $startDate = Carbon::createFromDate($year, ($quarter - 1) * 3 + 1, 1)->startOfMonth();
    $endDate = $startDate->copy()->addMonths(3)->endOfMonth();

    return Invoice::whereBetween('invoice_date', [$startDate, $endDate])
        ->where('company_id', auth()->user()->company_id)
        ->sum('vat_amount');
}
```

### 3. Financial Reports Optimization

```php
// ❌ BAD: Multiple separate queries
public function financialSummary()
{
    $revenue = Invoice::where('status', 'paid')->sum('total');
    $expenses = Expense::sum('amount');
    $outstanding = Invoice::where('status', 'unpaid')->sum('total');

    // 3 separate queries
}

// ✅ GOOD: Single query with subqueries
public function financialSummary()
{
    return DB::table('companies')
        ->where('id', auth()->user()->company_id)
        ->select([
            DB::raw('(SELECT SUM(total) FROM invoices WHERE status = "paid" AND company_id = companies.id) as revenue'),
            DB::raw('(SELECT SUM(amount) FROM expenses WHERE company_id = companies.id) as expenses'),
            DB::raw('(SELECT SUM(total) FROM invoices WHERE status = "unpaid" AND company_id = companies.id) as outstanding'),
        ])
        ->first();
}
```

## Caching Strategies for Bookkeeping

### 1. Dashboard Metrics Caching

```php
class DashboardService
{
    public function getMetrics(Company $company): array
    {
        return cache()->remember(
            "dashboard:metrics:{$company->id}",
            now()->addMinutes(5),
            function () use ($company) {
                return [
                    'total_revenue' => $this->calculateRevenue($company),
                    'outstanding_invoices' => $this->calculateOutstanding($company),
                    'recent_expenses' => $this->getRecentExpenses($company),
                ];
            }
        );
    }

    // Invalidate cache when data changes
    public function invoicePaid(Invoice $invoice): void
    {
        cache()->forget("dashboard:metrics:{$invoice->company_id}");
    }
}
```

### 2. Report Caching with Tags

```php
// Generate and cache report
$report = cache()
    ->tags(['company:' . $companyId, 'reports', 'vat'])
    ->remember("vat-report:{$companyId}:{$quarter}", 3600, function () {
        return $this->generateVatReport($companyId, $quarter);
    });

// Invalidate when VAT data changes
public function invoiceCreated(Invoice $invoice)
{
    cache()->tags(['company:' . $invoice->company_id, 'vat'])->flush();
}
```

### 3. Chart of Accounts Caching

```php
// Cache chart of accounts (rarely changes)
public function getChartOfAccounts(Company $company): Collection
{
    return cache()->rememberForever(
        "coa:{$company->id}",
        fn() => ChartOfAccount::where('company_id', $company->id)
            ->orderBy('account_code')
            ->get()
    );
}

// Clear cache when accounts are modified
public function accountUpdated(ChartOfAccount $account): void
{
    cache()->forget("coa:{$account->company_id}");
}
```

## Best Practices

### 1. Database Optimization
- **Always add indexes** on foreign keys and frequently queried columns
- **Use composite indexes** for multi-column WHERE clauses
- **Avoid SELECT *** - specify needed columns
- **Use database aggregations** instead of PHP calculations
- **Implement query result caching** for expensive calculations
- **Paginate large datasets** - never load all records at once

### 2. Laravel-Specific Optimizations
- **Use eager loading** to prevent N+1 queries
- **Cache configuration** in production (config:cache, route:cache, view:cache)
- **Enable OPcache** for PHP opcode caching
- **Use queue workers** for long-running tasks
- **Implement Redis** for cache and sessions
- **Monitor with Telescope** in staging, APM in production

### 3. Frontend Optimization
- **Lazy load components** with Vue/React lazy loading
- **Code splitting** to reduce initial bundle size
- **Image optimization** - compress and use appropriate formats
- **Implement CDN** for static assets
- **Enable Gzip/Brotli** compression
- **Use browser caching** headers

### 4. Query Performance
- **Profile with Telescope** to identify slow queries
- **Use EXPLAIN** to understand query execution
- **Add missing indexes** identified by slow query log
- **Denormalize judiciously** for read-heavy scenarios
- **Partition large tables** (invoices by year)
- **Archive old data** to keep tables lean

### 5. Monitoring and Alerts
- **Set up APM** (New Relic, DataDog) for production
- **Enable slow query logging** (>1s queries)
- **Monitor memory usage** and prevent leaks
- **Track response times** and set alerts
- **Monitor queue lengths** and processing times
- **Set up error tracking** (Sentry, Bugsnag)

## Anti-Patterns

### 1. ❌ Loading Entire Dataset
```php
// BAD
$allInvoices = Invoice::all();
$filtered = $allInvoices->where('status', 'paid');

// GOOD
$filtered = Invoice::where('status', 'paid')->get();
```

### 2. ❌ N+1 Queries in Loops
```php
// BAD
foreach ($invoices as $invoice) {
    echo $invoice->client->name; // Query per iteration
}

// GOOD
$invoices = Invoice::with('client')->get();
foreach ($invoices as $invoice) {
    echo $invoice->client->name; // No extra queries
}
```

### 3. ❌ No Query Caching
```php
// BAD
public function stats() {
    return Invoice::count(); // Queries every time
}

// GOOD
public function stats() {
    return cache()->remember('invoice-count', 600, fn() => Invoice::count());
}
```

### 4. ❌ Not Using Indexes
```php
// BAD - No index on commonly queried columns
Schema::create('invoices', function (Blueprint $table) {
    $table->foreignId('company_id'); // No index!
});

// GOOD
Schema::create('invoices', function (Blueprint $table) {
    $table->foreignId('company_id')->constrained()->index();
});
```

## Troubleshooting

### Problem 1: Page Loading Slowly

**Diagnosis**:
```php
// Enable Telescope query logging
// Check /telescope/queries for slow queries

// Or use Query Log
DB::enableQueryLog();
// ... your code ...
dd(DB::getQueryLog());
```

**Solutions**:
- Add eager loading for relationships
- Add missing database indexes
- Cache expensive calculations
- Paginate results

### Problem 2: Memory Limit Exceeded

**Diagnosis**:
```php
// Check memory usage
$memoryBefore = memory_get_usage();
// ... your code ...
$memoryAfter = memory_get_usage();
echo "Memory used: " . ($memoryAfter - $memoryBefore) / 1024 / 1024 . " MB";
```

**Solutions**:
- Use chunking for large datasets
- Unset large variables when done
- Increase PHP memory_limit (temporary fix)
- Fix memory leaks in code

### Problem 3: Queue Workers Backing Up

**Diagnosis**:
```bash
# Check queue length
php artisan queue:monitor redis:default --max=100

# Check failed jobs
php artisan queue:failed
```

**Solutions**:
- Increase number of queue workers
- Optimize job processing code
- Add job timeout and retry logic
- Implement job batching

## Performance Checklists

### Pre-Deployment Checklist
- [ ] All routes cached (`route:cache`)
- [ ] Config cached (`config:cache`)
- [ ] Views compiled (`view:cache`)
- [ ] OPcache enabled in PHP
- [ ] Debug mode disabled (`APP_DEBUG=false`)
- [ ] Composer autoloader optimized (`--optimize-autoloader`)
- [ ] No Telescope in production
- [ ] CDN configured for assets
- [ ] Database indexes verified
- [ ] Query profiling reviewed

### Monthly Performance Audit
- [ ] Review Telescope/APM for slow queries (>1s)
- [ ] Check database size and archive old data
- [ ] Review and optimize largest tables
- [ ] Check Redis memory usage
- [ ] Review queue metrics
- [ ] Load test critical endpoints
- [ ] Review error logs
- [ ] Update dependencies
- [ ] Check disk space on servers
- [ ] Review caching hit rates

---

**Target**: 95% of requests under 200ms, 99% under 500ms
