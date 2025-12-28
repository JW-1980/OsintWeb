---
name: mysql
description: Database design, MySQL optimization, query performance, indexing, security, migration best practices, internals deep dive, cloud optimization
tags: [database, mysql, optimization, indexing, normalization, migrations, performance, security, laravel, innodb, mvcc, cloud, aurora, rds]
version: 3.0.2
trigger_keywords: [sk-mysql, "database design", "query optimization", "index strategy", "mysql performance", "sql query", "migration schema", "database optimization", "innodb configuration", "query plan", "table design", "foreign key", "database schema"]
---
# Database & MySQL Expert

This skill provides comprehensive guidance on database design, MySQL optimization, query performance, indexing strategies, security best practices, and Laravel-specific database patterns.

## When to Use

- Designing database schemas and relationships
- Optimizing slow queries and database performance
- Creating and validating Laravel migrations
- Implementing proper indexing strategies
- Resolving foreign key constraint errors
- Normalizing or denormalizing data
- Securing database access and preventing SQL injection
- Troubleshooting database issues
- Planning data archival and retention strategies
- Implementing multi-tenancy database patterns
- Setting up database replication and backups
- Debugging connection pool issues

## Database Design & Normalization

### 1. Normal Forms

**First Normal Form (1NF)**:
- Each column contains atomic (indivisible) values
- Each column contains values of a single type
- Each column has a unique name
- Order doesn't matter

```sql
-- ❌ NOT 1NF (multiple values in one column)
CREATE TABLE clients (
    id INT PRIMARY KEY,
    name VARCHAR(255),
    phone_numbers VARCHAR(500) -- "06-12345678, 06-87654321"
);

-- ✅ 1NF compliant
CREATE TABLE clients (
    id INT PRIMARY KEY,
    name VARCHAR(255)
);

CREATE TABLE client_phones (
    id INT PRIMARY KEY,
    client_id INT,
    phone_number VARCHAR(20),
    type ENUM('mobile', 'landline', 'fax'),
    FOREIGN KEY (client_id) REFERENCES clients(id)
);
```

**Second Normal Form (2NF)**:
- Must be in 1NF
- All non-key columns depend on the entire primary key (no partial dependencies)

```sql
-- ❌ NOT 2NF (partial dependency)
CREATE TABLE invoice_items (
    invoice_id INT,
    product_id INT,
    product_name VARCHAR(255),  -- Depends only on product_id, not the composite key
    quantity INT,
    price DECIMAL(10,2),
    PRIMARY KEY (invoice_id, product_id)
);

-- ✅ 2NF compliant
CREATE TABLE invoice_items (
    invoice_id INT,
    product_id INT,
    quantity INT,
    price DECIMAL(10,2),  -- Price at time of sale
    PRIMARY KEY (invoice_id, product_id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

CREATE TABLE products (
    id INT PRIMARY KEY,
    name VARCHAR(255),
    current_price DECIMAL(10,2)
);
```

**Third Normal Form (3NF)**:
- Must be in 2NF
- No transitive dependencies (non-key columns don't depend on other non-key columns)

```sql
-- ❌ NOT 3NF (transitive dependency)
CREATE TABLE invoices (
    id INT PRIMARY KEY,
    client_id INT,
    client_name VARCHAR(255),      -- Depends on client_id (transitive)
    client_address VARCHAR(500),   -- Depends on client_id (transitive)
    invoice_date DATE,
    total DECIMAL(10,2)
);

-- ✅ 3NF compliant
CREATE TABLE invoices (
    id INT PRIMARY KEY,
    client_id INT,
    invoice_date DATE,
    total DECIMAL(10,2),
    FOREIGN KEY (client_id) REFERENCES clients(id)
);

CREATE TABLE clients (
    id INT PRIMARY KEY,
    name VARCHAR(255),
    address VARCHAR(500)
);
```

### 2. When to Denormalize

**Strategic denormalization** can improve performance in specific cases:

```php
// Denormalized: Store invoice total to avoid calculation
Schema::create('invoices', function (Blueprint $table) {
    $table->id();
    $table->foreignId('client_id')->constrained();
    $table->decimal('total_ex_vat', 10, 2); // Denormalized from items
    $table->decimal('total_vat', 10, 2);    // Denormalized from items
    $table->decimal('total', 10, 2);        // Denormalized from items
    $table->timestamps();
});

// Keep normalized detail
Schema::create('invoice_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('invoice_id')->constrained();
    $table->string('description');
    $table->decimal('quantity', 10, 2);
    $table->decimal('price', 10, 2);
    $table->decimal('vat_rate', 5, 2);
    $table->decimal('total', 10, 2);
});
```

**Use denormalization when**:
- Calculations are expensive and values rarely change
- Read performance is critical and writes are infrequent
- You can maintain data integrity through application logic
- The denormalized data is immutable after creation (like historical totals)

## Indexing Strategies

### 1. Index Types

**Primary Key Index**:
```php
$table->id(); // Automatically indexed
```

**Single Column Index**:
```php
$table->index('email');
$table->index('created_at');
$table->index('status');
```

**Composite Index** (order matters!):
```php
// Good for queries: WHERE company_id = ? AND status = ?
$table->index(['company_id', 'status']);

// Not optimal for: WHERE status = ? (doesn't use index)
```

**Unique Index**:
```php
$table->unique('email');
$table->unique(['invoice_number', 'company_id']);
```

**Full-Text Index** (for search):
```php
$table->fullText(['title', 'description', 'content']);
```

**Spatial Index** (for geographic data):
```php
$table->geometry('location');
$table->spatialIndex('location');
```

### 2. Index Best Practices

**✅ DO index**:
- Foreign keys
- Columns used in WHERE clauses frequently
- Columns used in JOIN conditions
- Columns used in ORDER BY
- Columns with high selectivity (many unique values)

```php
Schema::create('invoices', function (Blueprint $table) {
    $table->id();
    $table->foreignId('company_id')->constrained();  // ✓ Auto-indexed
    $table->foreignId('client_id')->constrained();   // ✓ Auto-indexed
    $table->string('invoice_number');
    $table->enum('status', ['draft', 'sent', 'paid']);
    $table->date('invoice_date');
    $table->date('due_date');

    // Composite index for common query
    $table->index(['company_id', 'status']);  // ✓ Common filter
    $table->index(['company_id', 'client_id']); // ✓ Common join
    $table->index('invoice_date'); // ✓ Used for sorting/filtering
    $table->unique(['company_id', 'invoice_number']); // ✓ Business rule
});
```

**❌ DON'T over-index**:
- Low-cardinality columns (few unique values like boolean) unless combined
- Columns that change frequently
- Small tables (< 1000 rows)
- Columns rarely used in queries

### 3. Index Order in Composite Indexes

**Rule**: Most selective column first, then by frequency of use

```php
// Query: WHERE company_id = 1 AND status = 'paid' AND created_at > '2024-01-01'

// ✅ GOOD: company_id (medium selectivity), status (low), created_at (high)
$table->index(['company_id', 'status', 'created_at']);

// ❌ BAD: Low selectivity first
$table->index(['status', 'company_id', 'created_at']);
```

**Leftmost prefix rule**: Index can be used for queries that filter on left-most columns:

```php
$table->index(['company_id', 'status', 'created_at']);

// ✓ Uses index: WHERE company_id = 1
// ✓ Uses index: WHERE company_id = 1 AND status = 'paid'
// ✓ Uses index: WHERE company_id = 1 AND status = 'paid' AND created_at > '2024-01-01'
// ❌ Doesn't use index: WHERE status = 'paid'
// ❌ Doesn't use index: WHERE created_at > '2024-01-01'
```

### 4. Covering Indexes

**What**: An index that contains all columns needed by a query

```php
// Query: SELECT id, status, total FROM invoices WHERE company_id = 1
// Instead of index on just company_id, create covering index:
$table->index(['company_id', 'id', 'status', 'total']);

// MySQL can satisfy the query entirely from the index without accessing the table
```

**Benefits**:
- No table lookup required
- Faster query execution
- Reduced I/O

**Trade-offs**:
- Larger index size
- Slower writes (more index to update)

## Query Optimization

### 1. Identify Slow Queries

**Enable slow query log** in MySQL:
```sql
SET GLOBAL slow_query_log = 'ON';
SET GLOBAL long_query_time = 1; -- Queries slower than 1 second
```

**Use EXPLAIN** to analyze queries:
```sql
EXPLAIN SELECT * FROM invoices
WHERE company_id = 1 AND status = 'paid'
ORDER BY created_at DESC;
```

**Understanding EXPLAIN output**:
```
| id | select_type | table    | type  | possible_keys | key      | rows | Extra |
|----|-------------|----------|-------|---------------|----------|------|-------|
| 1  | SIMPLE      | invoices | ref   | company_idx   | company  | 100  | WHERE |

type values (best to worst):
- system: Table has only one row
- const: At most one matching row (PRIMARY KEY or UNIQUE)
- eq_ref: One row per previous row (optimal JOIN)
- ref: Multiple rows with matching index value
- range: Index range scan (BETWEEN, >, <)
- index: Full index scan
- ALL: Full table scan (AVOID!)
```

**Laravel Query Log**:
```php
DB::enableQueryLog();

// Your queries here
$invoices = Invoice::where('status', 'paid')->get();

dd(DB::getQueryLog());
```

### 2. N+1 Query Problem

**❌ BAD: N+1 queries**:
```php
// 1 query to get invoices
$invoices = Invoice::where('company_id', 1)->get();

foreach ($invoices as $invoice) {
    // N queries (one per invoice)
    echo $invoice->client->name;

    // N more queries (one per invoice)
    foreach ($invoice->items as $item) {
        echo $item->product->name;
    }
}
```

**✅ GOOD: Eager loading**:
```php
// 3 queries total (invoices, clients, items with products)
$invoices = Invoice::with(['client', 'items.product'])
    ->where('company_id', 1)
    ->get();

foreach ($invoices as $invoice) {
    echo $invoice->client->name; // No query

    foreach ($invoice->items as $item) {
        echo $item->product->name; // No query
    }
}
```

**Conditional eager loading**:
```php
// Only load relationships when needed
$invoices = Invoice::with([
    'client' => function ($query) {
        $query->select('id', 'name', 'email'); // Only needed columns
    },
    'items' => function ($query) {
        $query->where('quantity', '>', 0); // Filter items
    }
])->get();
```

### 3. Query Optimization Techniques

**Select only needed columns**:
```php
// ❌ Loads all columns including large text fields
$invoices = Invoice::all();

// ✅ Select only needed columns
$invoices = Invoice::select('id', 'invoice_number', 'total')->get();
```

**Use chunking for large datasets**:
```php
// ❌ Loads 100,000 records into memory
Invoice::all()->each(function ($invoice) {
    // Process
});

// ✅ Process in chunks of 1000
Invoice::chunk(1000, function ($invoices) {
    foreach ($invoices as $invoice) {
        // Process
    }
});

// ✅ Even better: Use lazy() for memory efficiency
Invoice::lazy()->each(function ($invoice) {
    // Process one at a time
});
```

**Use database aggregations instead of PHP**:
```php
// ❌ Fetch all records and count in PHP
$count = Invoice::where('status', 'paid')->get()->count();

// ✅ Use database COUNT
$count = Invoice::where('status', 'paid')->count();

// ✅ Use database SUM
$total = Invoice::where('status', 'paid')->sum('total');

// ✅ Multiple aggregations in one query
$stats = Invoice::where('company_id', 1)
    ->selectRaw('
        COUNT(*) as count,
        SUM(total) as total,
        AVG(total) as average,
        MAX(total) as highest,
        MIN(total) as lowest
    ')
    ->first();
```

**Avoid SELECT * in production**:
```php
// ❌ Returns all columns (including large BLOB/TEXT fields)
DB::table('documents')->get();

// ✅ Specify needed columns
DB::table('documents')->select('id', 'title', 'created_at')->get();
```

### 4. Query Scopes for Reusability

```php
// Define reusable query scopes
class Invoice extends Model
{
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'sent')
            ->where('due_date', '<', now());
    }

    public function scopeThisYear($query)
    {
        return $query->whereYear('invoice_date', now()->year);
    }
}

// Use scopes
$paidInvoices = Invoice::forCompany(1)
    ->paid()
    ->thisYear()
    ->get();
```

### 5. Subquery Optimization

```php
// ❌ BAD: Slow subquery
$invoices = Invoice::whereIn('client_id', function ($query) {
    $query->select('id')
        ->from('clients')
        ->where('status', 'active');
})->get();

// ✅ GOOD: Use join instead
$invoices = Invoice::join('clients', 'invoices.client_id', '=', 'clients.id')
    ->where('clients.status', 'active')
    ->select('invoices.*')
    ->get();

// ✅ ALTERNATIVE: Use whereHas
$invoices = Invoice::whereHas('client', function ($query) {
    $query->where('status', 'active');
})->get();
```

## Laravel Migration Best Practices

### 1. Migration Order and Dependencies

**✅ CORRECT order**:
```php
// Migration 1: Create parent table
Schema::create('companies', function (Blueprint $table) {
    $table->id();
    $table->string('name');
});

// Migration 2: Create child table (runs after parent exists)
Schema::create('invoices', function (Blueprint $table) {
    $table->id();
    $table->foreignId('company_id')->constrained(); // ✓ companies exists
});
```

**❌ WRONG order**:
```php
// Migration 1: 2024_11_01_create_invoices.php
Schema::create('invoices', function (Blueprint $table) {
    $table->foreignId('company_id')->constrained(); // ❌ companies doesn't exist yet!
});

// Migration 2: 2024_11_02_create_companies.php
Schema::create('companies', function (Blueprint $table) {
    $table->id();
});
```

**Fix**: Rename migration files to correct chronological order:
```bash
mv 2024_11_01_create_invoices.php 2024_11_02_create_invoices.php
```

### 2. Within-Migration Table Order

**✅ CORRECT: Create referenced tables first**:
```php
public function up()
{
    // 1. Create parent first
    Schema::create('document_folders', function (Blueprint $table) {
        $table->id();
    });

    // 2. Create child second
    Schema::create('documents', function (Blueprint $table) {
        $table->id();
        $table->foreignId('folder_id')->constrained('document_folders'); // ✓
    });
}
```

### 3. Foreign Key Constraints

**Proper foreign key syntax**:
```php
// Simple foreign key
$table->foreignId('user_id')->constrained();

// With custom table name
$table->foreignId('creator_id')->constrained('users');

// With cascade delete
$table->foreignId('company_id')->constrained()->cascadeOnDelete();

// With set null on delete
$table->foreignId('manager_id')->nullable()->constrained('users')->nullOnDelete();

// With restrict (prevent deletion if referenced)
$table->foreignId('client_id')->constrained()->restrictOnDelete();
```

**When to use each delete action**:
```php
// cascadeOnDelete(): Delete child records when parent is deleted
// Use for: Invoice items, dependent records
$table->foreignId('invoice_id')->constrained()->cascadeOnDelete();

// nullOnDelete(): Set to NULL when parent is deleted
// Use for: Optional relationships like "manager"
$table->foreignId('manager_id')->nullable()->constrained('users')->nullOnDelete();

// restrictOnDelete(): Prevent parent deletion if children exist
// Use for: Important relationships like client → invoices
$table->foreignId('client_id')->constrained()->restrictOnDelete();
```

### 4. Avoid Column Name Conflicts with after()

**❌ WRONG: Reference non-existent column**:
```php
Schema::table('invoices', function (Blueprint $table) {
    $table->string('currency')->after('total'); // ❌ 'total' doesn't exist
});
```

**✅ CORRECT: Use existing column**:
```php
// Check what columns exist in create migration
Schema::create('invoices', function (Blueprint $table) {
    $table->decimal('total_ex_vat', 10, 2);
    $table->decimal('total_vat', 10, 2);
});

// Reference correct column
Schema::table('invoices', function (Blueprint $table) {
    $table->string('currency')->after('total_vat'); // ✓ exists
});
```

### 5. Handling Duplicate Migrations

**Check for existing columns/tables**:
```php
public function up()
{
    if (!Schema::hasTable('invoices')) {
        Schema::create('invoices', function (Blueprint $table) {
            // ...
        });
    }

    Schema::table('invoices', function (Blueprint $table) {
        if (!Schema::hasColumn('invoices', 'currency')) {
            $table->string('currency', 3)->default('EUR');
        }
    });
}
```

### 6. Data Migrations

**Separate structure from data**:
```php
// Good: Separate migration for data transformation
public function up()
{
    // First, add column
    Schema::table('invoices', function (Blueprint $table) {
        $table->decimal('total_incl_vat', 10, 2)->nullable();
    });

    // Then, populate data
    DB::table('invoices')->chunkById(1000, function ($invoices) {
        foreach ($invoices as $invoice) {
            DB::table('invoices')
                ->where('id', $invoice->id)
                ->update([
                    'total_incl_vat' => $invoice->total_ex_vat * 1.21
                ]);
        }
    });

    // Finally, make non-nullable if needed
    Schema::table('invoices', function (Blueprint $table) {
        $table->decimal('total_incl_vat', 10, 2)->nullable(false)->change();
    });
}
```

## Database Security

### 1. Prevent SQL Injection

**✅ ALWAYS use parameter binding**:
```php
// ✅ GOOD: Parameterized query
$email = $request->input('email');
$user = DB::select('SELECT * FROM users WHERE email = ?', [$email]);

// ✅ GOOD: Named bindings
$user = DB::select('SELECT * FROM users WHERE email = :email', [
    'email' => $email
]);

// ✅ GOOD: Eloquent (automatically parameterized)
$user = User::where('email', $email)->first();
```

**❌ NEVER concatenate user input**:
```php
// ❌ DANGEROUS: SQL injection vulnerability
$email = $request->input('email');
$user = DB::select("SELECT * FROM users WHERE email = '{$email}'");

// Attacker can input: ' OR '1'='1' --
// Resulting query: SELECT * FROM users WHERE email = '' OR '1'='1' --'
// This returns ALL users!
```

### 2. Database User Permissions

**Principle of least privilege**:
```sql
-- Application user: SELECT, INSERT, UPDATE, DELETE only
CREATE USER 'app_user'@'localhost' IDENTIFIED BY 'strong_password';
GRANT SELECT, INSERT, UPDATE, DELETE ON boekhouder.* TO 'app_user'@'localhost';

-- Migration user: Full DDL permissions
CREATE USER 'migration_user'@'localhost' IDENTIFIED BY 'strong_password';
GRANT ALL PRIVILEGES ON boekhouder.* TO 'migration_user'@'localhost';

-- Read-only reporting user
CREATE USER 'report_user'@'localhost' IDENTIFIED BY 'strong_password';
GRANT SELECT ON boekhouder.* TO 'report_user'@'localhost';

-- Backup user
CREATE USER 'backup_user'@'localhost' IDENTIFIED BY 'strong_password';
GRANT SELECT, LOCK TABLES, SHOW VIEW ON boekhouder.* TO 'backup_user'@'localhost';
```

**Separate users for different environments**:
```php
// .env.production
DB_USERNAME=app_user

// .env.staging
DB_USERNAME=staging_user

// .env.local
DB_USERNAME=dev_user
```

### 3. Sensitive Data Protection

**Encrypt sensitive columns**:
```php
// Model with encrypted attributes
class Client extends Model
{
    protected $casts = [
        'bank_account' => 'encrypted',
        'vat_number' => 'encrypted',
        'tax_id' => 'encrypted',
    ];
}

// Use encrypted casting for PII
Schema::create('clients', function (Blueprint $table) {
    $table->text('bank_account'); // Will be encrypted
    $table->text('vat_number');   // Will be encrypted
});
```

**Hash passwords properly**:
```php
// ✅ GOOD: Use bcrypt/argon2
use Illuminate\Support\Facades\Hash;

$user->password = Hash::make($request->password);

// Verify
if (Hash::check($request->password, $user->password)) {
    // Authenticated
}

// ❌ BAD: Never use MD5, SHA1, or plain hashing
$user->password = md5($request->password); // INSECURE!
```

**Never store credit cards** - use tokenization (Stripe, Mollie, etc.)

### 4. Row-Level Security for Multi-Tenancy

**Global scopes for automatic filtering**:
```php
// Automatically scope all queries by company
class CompanyScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        if (auth()->check() && auth()->user()->company_id) {
            $builder->where('company_id', auth()->user()->company_id);
        }
    }
}

// Apply to models
class Invoice extends Model
{
    protected static function booted()
    {
        static::addGlobalScope(new CompanyScope);
    }
}

// Now all queries are automatically scoped:
Invoice::all(); // SELECT * FROM invoices WHERE company_id = ?
```

**Policy-based authorization**:
```php
// InvoicePolicy.php
public function view(User $user, Invoice $invoice)
{
    // Prevent cross-tenant access
    return $user->company_id === $invoice->company_id;
}

// Use in controller
public function show(Invoice $invoice)
{
    $this->authorize('view', $invoice);
    return view('invoices.show', compact('invoice'));
}
```

## Performance Optimization

### 1. Database Connection Pooling

**config/database.php**:
```php
'mysql' => [
    'driver' => 'mysql',
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', '3306'),
    'database' => env('DB_DATABASE', 'forge'),
    'username' => env('DB_USERNAME', 'forge'),
    'password' => env('DB_PASSWORD', ''),
    'unix_socket' => env('DB_SOCKET', ''),
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
    'strict' => true,
    'engine' => null,
    'options' => extension_loaded('pdo_mysql') ? array_filter([
        PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
        PDO::ATTR_PERSISTENT => true, // Connection pooling
        PDO::ATTR_EMULATE_PREPARES => false, // Better performance
        PDO::ATTR_STRINGIFY_FETCHES => false, // Proper type handling
    ]) : [],
],
```

**Monitor connection pool**:
```sql
-- Check current connections
SHOW PROCESSLIST;

-- Check max connections
SHOW VARIABLES LIKE 'max_connections';

-- Check connection statistics
SHOW STATUS LIKE 'Threads_connected';
SHOW STATUS LIKE 'Max_used_connections';
```

### 2. Query Caching

**Cache expensive queries**:
```php
// Cache for 1 hour
$stats = Cache::remember('dashboard_stats_' . auth()->id(), 3600, function () {
    return [
        'total_invoices' => Invoice::count(),
        'total_revenue' => Invoice::where('status', 'paid')->sum('total'),
        'pending_payments' => Invoice::where('status', 'sent')->sum('total'),
    ];
});

// Cache with tags for easier invalidation
$stats = Cache::tags(['company:' . $companyId, 'dashboard'])
    ->remember("dashboard_stats_{$companyId}", 3600, function () use ($companyId) {
        return [
            'total_invoices' => Invoice::where('company_id', $companyId)->count(),
            // ...
        ];
    });

// Invalidate when data changes
Invoice::created(function ($invoice) {
    Cache::tags(['company:' . $invoice->company_id, 'dashboard'])->flush();
});
```

**Remember vs rememberForever**:
```php
// Use remember for data that changes
Cache::remember('stats', 3600, fn() => $this->calculateStats());

// Use rememberForever for rarely-changing data
Cache::rememberForever('chart_of_accounts', fn() => ChartOfAccount::all());

// Invalidate rememberForever when needed
Cache::forget('chart_of_accounts');
```

### 3. Database Indexes for Common Queries

```php
// Common query: Get paid invoices for a company in date range
$invoices = Invoice::where('company_id', 1)
    ->where('status', 'paid')
    ->whereBetween('invoice_date', ['2024-01-01', '2024-12-31'])
    ->get();

// Optimal index:
$table->index(['company_id', 'status', 'invoice_date']);

// Alternative: Separate indexes if queries vary
$table->index('company_id');
$table->index(['company_id', 'status']);
$table->index(['company_id', 'invoice_date']);
```

### 4. Avoid OR Queries (Use UNION instead)

**❌ SLOW: OR with different columns**:
```php
$results = Invoice::where('invoice_number', 'LIKE', "%{$search}%")
    ->orWhere('client_name', 'LIKE', "%{$search}%")
    ->get();
```

**✅ FASTER: UNION of indexed queries**:
```php
$byNumber = Invoice::where('invoice_number', 'LIKE', "%{$search}%");
$byClient = Invoice::where('client_name', 'LIKE', "%{$search}%");

$results = $byNumber->union($byClient)->get();
```

**✅ BEST: Full-text search**:
```php
// Create full-text index
Schema::table('invoices', function (Blueprint $table) {
    $table->fullText(['invoice_number', 'description', 'notes']);
});

// Use full-text search
$results = Invoice::whereFullText(['invoice_number', 'description', 'notes'], $search)->get();
```

### 5. Table Partitioning for Large Tables

**Partition by date range**:
```sql
-- Partition invoices table by year
CREATE TABLE invoices (
    id INT PRIMARY KEY,
    company_id INT,
    invoice_date DATE,
    total DECIMAL(10,2)
) PARTITION BY RANGE (YEAR(invoice_date)) (
    PARTITION p2023 VALUES LESS THAN (2024),
    PARTITION p2024 VALUES LESS THAN (2025),
    PARTITION p2025 VALUES LESS THAN (2026),
    PARTITION p_future VALUES LESS THAN MAXVALUE
);

-- Queries automatically use partitions
SELECT * FROM invoices WHERE invoice_date BETWEEN '2024-01-01' AND '2024-12-31';
-- Only scans p2024 partition
```

**Benefits**:
- Faster queries on large tables
- Easier archival (drop old partitions)
- Better index performance
- Parallel query execution

## Common Database Issues

### Issue 1: Foreign Key Constraint Failures

**Error**: `SQLSTATE[HY000]: General error: 1824 Failed to open the referenced table`

**Cause**: Table referenced in foreign key doesn't exist yet

**Solution**:
```php
// Check migration file timestamps
// Rename to run after parent table creation
mv 2024_11_01_create_invoices.php 2024_11_02_000001_create_invoices.php
```

### Issue 2: Column Not Found in after() Clause

**Error**: `SQLSTATE[42S22]: Column not found: 1054 Unknown column 'total'`

**Solution**: Check actual column names in create migration:
```php
// Find correct column name
Schema::create('invoices', function (Blueprint $table) {
    $table->decimal('total_ex_vat', 10, 2);  // This is the actual name
    $table->decimal('total_vat', 10, 2);
});

// Use correct column
Schema::table('invoices', function (Blueprint $table) {
    $table->string('currency')->after('total_vat'); // ✓
});
```

### Issue 3: Duplicate Key Violations

**Error**: `SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry`

**Solution**: Add unique constraints and handle in application:
```php
try {
    Invoice::create([
        'invoice_number' => 'INV-001',
        'company_id' => 1,
    ]);
} catch (\Illuminate\Database\QueryException $e) {
    if ($e->errorInfo[1] == 1062) {
        // Duplicate entry, generate new number
        $invoice_number = $this->generateUniqueInvoiceNumber();
    }
}
```

### Issue 4: Slow Queries with Large Datasets

**Diagnosis**:
```php
DB::enableQueryLog();
$invoices = Invoice::with('client')->get();
dd(DB::getQueryLog());
```

**Solutions**:
1. Add missing indexes
2. Use pagination instead of get()
3. Use select() to limit columns
4. Implement caching

### Issue 5: Deadlocks

**Error**: `SQLSTATE[40001]: Serialization failure: 1213 Deadlock found`

**Cause**: Two transactions waiting for each other's locks

**Solution**:
```php
// Use consistent lock order
DB::transaction(function () {
    // Always lock in same order: client → invoice → items
    $client = Client::lockForUpdate()->find($clientId);
    $invoice = Invoice::lockForUpdate()->find($invoiceId);
    // Process
});

// Retry on deadlock
$maxAttempts = 3;
$attempt = 0;

while ($attempt < $maxAttempts) {
    try {
        DB::transaction(function () {
            // Your transaction code
        });
        break; // Success
    } catch (\Illuminate\Database\QueryException $e) {
        if ($e->errorInfo[1] == 1213) { // Deadlock
            $attempt++;
            if ($attempt >= $maxAttempts) {
                throw $e;
            }
            usleep(100000); // Wait 100ms before retry
        } else {
            throw $e;
        }
    }
}
```

### Issue 6: Connection Timeout Errors

**Error**: `SQLSTATE[HY000] [2002] Connection timed out`

**Causes & Solutions**:
```php
// 1. Firewall blocking connection
// Check firewall rules: sudo ufw status

// 2. MySQL not listening on correct interface
// Check: SHOW VARIABLES LIKE 'bind_address';
// Should be: 0.0.0.0 or specific IP

// 3. Too many connections
// Check: SHOW STATUS LIKE 'Threads_connected';
// Increase: SET GLOBAL max_connections = 200;

// 4. Connection pool exhausted
// config/database.php
'mysql' => [
    'pool' => [
        'min' => 2,
        'max' => 20,
    ],
],
```

### Issue 7: Character Encoding Issues

**Error**: `Incorrect string value: '\xF0\x9F...' for column`

**Solution**: Ensure utf8mb4 everywhere:
```php
// config/database.php
'mysql' => [
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
],

// In migration
Schema::create('invoices', function (Blueprint $table) {
    $table->charset = 'utf8mb4';
    $table->collation = 'utf8mb4_unicode_ci';
});

// MySQL configuration (my.cnf)
[mysqld]
character-set-server = utf8mb4
collation-server = utf8mb4_unicode_ci

[client]
default-character-set = utf8mb4
```

### Issue 8: Table Locking

**Problem**: Long-running ALTER TABLE locks entire table

**Solution**: Use pt-online-schema-change:
```bash
# Install Percona Toolkit
sudo apt-get install percona-toolkit

# Alter table without locking
pt-online-schema-change \
    --alter "ADD COLUMN new_column VARCHAR(255)" \
    D=boekhouder,t=invoices \
    --execute

# Laravel integration
public function up()
{
    if (app()->environment('production')) {
        // Use pt-osc for large tables
        $this->runPerconaSchemaChange('invoices', 'ADD COLUMN new_column VARCHAR(255)');
    } else {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('new_column')->nullable();
        });
    }
}
```

## Data Maintenance

### 1. Soft Deletes

```php
Schema::create('invoices', function (Blueprint $table) {
    $table->id();
    $table->string('invoice_number');
    $table->timestamps();
    $table->softDeletes(); // Adds deleted_at column
});

// Model
class Invoice extends Model
{
    use SoftDeletes;
}

// Usage
$invoice->delete(); // Soft delete (sets deleted_at)
$invoice->forceDelete(); // Permanent delete
$invoice->restore(); // Restore

// Querying
Invoice::all(); // Only non-deleted
Invoice::withTrashed()->get(); // Include deleted
Invoice::onlyTrashed()->get(); // Only deleted
```

### 2. Data Archival Strategy

```php
// Archive old records to separate table
Schema::create('invoices_archive', function (Blueprint $table) {
    // Same structure as invoices
});

// Archive invoices older than 7 years
DB::transaction(function () {
    $oldInvoices = Invoice::where('created_at', '<', now()->subYears(7))->get();

    foreach ($oldInvoices as $invoice) {
        DB::table('invoices_archive')->insert($invoice->toArray());
        $invoice->forceDelete();
    }
});

// Automated archival command
php artisan make:command ArchiveOldInvoices

// In command
public function handle()
{
    $archived = 0;
    Invoice::where('created_at', '<', now()->subYears(7))
        ->chunk(1000, function ($invoices) use (&$archived) {
            foreach ($invoices as $invoice) {
                DB::table('invoices_archive')->insert($invoice->toArray());
                $invoice->forceDelete();
                $archived++;
            }
        });

    $this->info("Archived {$archived} invoices");
}
```

### 3. Database Backups

```bash
# Daily backup
mysqldump -u username -p boekhouder > backup_$(date +%Y%m%d).sql

# Compress backup
gzip backup_$(date +%Y%m%d).sql

# Backup to S3
mysqldump -u username -p boekhouder | gzip | aws s3 cp - s3://backups/boekhouder_$(date +%Y%m%d).sql.gz

# Restore backup
mysql -u username -p boekhouder < backup_20241211.sql

# Automated backup script
cat > /usr/local/bin/backup-db.sh << 'EOF'
#!/bin/bash
BACKUP_DIR="/backups"
DATE=$(date +%Y%m%d_%H%M%S)
FILENAME="boekhouder_${DATE}.sql.gz"

mysqldump -u backup_user -p${DB_PASSWORD} boekhouder | gzip > "${BACKUP_DIR}/${FILENAME}"

# Keep only last 30 days
find ${BACKUP_DIR} -name "boekhouder_*.sql.gz" -mtime +30 -delete

# Upload to S3
aws s3 cp "${BACKUP_DIR}/${FILENAME}" s3://backups/
EOF

chmod +x /usr/local/bin/backup-db.sh

# Schedule in crontab
0 2 * * * /usr/local/bin/backup-db.sh
```

### 4. Database Cleanup Jobs

```php
// Clean up old sessions
DB::table('sessions')->where('last_activity', '<', now()->subDays(30))->delete();

// Clean up old password resets
DB::table('password_resets')->where('created_at', '<', now()->subHours(1))->delete();

// Clean up failed jobs
DB::table('failed_jobs')->where('failed_at', '<', now()->subDays(7))->delete();

// Clean up telescope entries
DB::table('telescope_entries')->where('created_at', '<', now()->subDays(7))->delete();

// Schedule in Kernel
protected function schedule(Schedule $schedule)
{
    $schedule->command('telescope:prune')->daily();
    $schedule->command('queue:prune-failed --hours=168')->daily();
    $schedule->call(function () {
        DB::table('sessions')->where('last_activity', '<', now()->subDays(30))->delete();
    })->daily();
}
```

## Database Monitoring

### 1. Key Metrics to Monitor

```sql
-- Check table sizes
SELECT
    table_name,
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS "Size (MB)"
FROM information_schema.TABLES
WHERE table_schema = "boekhouder"
ORDER BY (data_length + index_length) DESC;

-- Check index usage
SELECT
    table_name,
    index_name,
    seq_in_index,
    column_name,
    cardinality
FROM information_schema.STATISTICS
WHERE table_schema = 'boekhouder'
ORDER BY table_name, index_name, seq_in_index;

-- Check slow queries
SELECT * FROM mysql.slow_log
ORDER BY query_time DESC
LIMIT 10;

-- Check connection usage
SHOW STATUS LIKE 'Threads_connected';
SHOW STATUS LIKE 'Max_used_connections';

-- Check query cache hit rate
SHOW STATUS LIKE 'Qcache%';

-- Check InnoDB buffer pool efficiency
SHOW STATUS LIKE 'Innodb_buffer_pool%';
```

### 2. Laravel Telescope for Query Monitoring

```bash
composer require laravel/telescope
php artisan telescope:install
php artisan migrate
```

Access at: `/telescope/queries`

**Filter slow queries**:
```php
// config/telescope.php
'watchers' => [
    Watchers\QueryWatcher::class => [
        'enabled' => env('TELESCOPE_QUERY_WATCHER', true),
        'slow' => 100, // Log queries slower than 100ms
    ],
],
```

### 3. Custom Monitoring

```php
// Log slow queries automatically
DB::listen(function ($query) {
    if ($query->time > 1000) {
        Log::warning('Slow query detected', [
            'sql' => $query->sql,
            'bindings' => $query->bindings,
            'time' => $query->time . 'ms',
            'location' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5),
        ]);
    }
});

// Monitor connection pool
$connections = DB::connection()->getPdo()->getAttribute(PDO::ATTR_SERVER_INFO);
Log::info('Database connections', ['info' => $connections]);
```

### 4. Performance Metrics Dashboard

```php
class DatabaseMetricsController extends Controller
{
    public function index()
    {
        return [
            'table_sizes' => $this->getTableSizes(),
            'slow_queries' => $this->getSlowQueries(),
            'connection_stats' => $this->getConnectionStats(),
            'index_usage' => $this->getIndexUsage(),
        ];
    }

    private function getTableSizes()
    {
        return DB::select("
            SELECT
                table_name,
                ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb
            FROM information_schema.TABLES
            WHERE table_schema = DATABASE()
            ORDER BY (data_length + index_length) DESC
            LIMIT 10
        ");
    }

    private function getSlowQueries()
    {
        return DB::table('telescope_entries')
            ->where('type', 'query')
            ->where('content->time', '>', 1000)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();
    }
}
```

## Testing Database Operations

### 1. Database Transactions in Tests

```php
use Illuminate\Foundation\Testing\RefreshDatabase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_creation()
    {
        $invoice = Invoice::factory()->create([
            'company_id' => 1,
            'total' => 1000.00,
        ]);

        $this->assertDatabaseHas('invoices', [
            'company_id' => 1,
            'total' => 1000.00,
        ]);
    }
}
```

### 2. Testing Foreign Key Constraints

```php
public function test_cannot_delete_client_with_invoices()
{
    $client = Client::factory()->create();
    Invoice::factory()->create(['client_id' => $client->id]);

    $this->expectException(QueryException::class);
    $client->delete(); // Should fail due to foreign key
}

public function test_cascade_delete_works()
{
    $invoice = Invoice::factory()->create();
    $item = InvoiceItem::factory()->create(['invoice_id' => $invoice->id]);

    $invoice->delete();

    $this->assertDatabaseMissing('invoice_items', ['id' => $item->id]);
}
```

### 3. Testing Query Performance

```php
public function test_invoice_list_query_is_optimized()
{
    Invoice::factory()->count(100)->create();

    DB::enableQueryLog();

    $invoices = Invoice::with('client')->paginate(25);

    $queries = DB::getQueryLog();

    // Should only execute 2 queries (invoices + clients)
    $this->assertCount(2, $queries);

    // First query should use index
    $this->assertStringContainsString('company_id', $queries[0]['query']);
}
```

## Checklists

### Pre-Implementation Checklist

- [ ] Database schema designed and normalized
- [ ] Foreign key relationships defined
- [ ] Indexes planned for common queries
- [ ] Multi-tenancy strategy decided (company_id scoping)
- [ ] Migration order verified
- [ ] Data types chosen appropriately (DECIMAL for money)
- [ ] Character encoding set to utf8mb4
- [ ] Soft deletes vs hard deletes decided
- [ ] Backup strategy in place

### Migration Deployment Checklist

- [ ] Review migration for foreign key dependencies
- [ ] Verify column order and after() references
- [ ] Check for duplicate table/column definitions
- [ ] Test migration on copy of production database
- [ ] Verify rollback works correctly
- [ ] Check migration doesn't lock tables for long time
- [ ] Review indexes on foreign keys
- [ ] Ensure migrations are idempotent if possible
- [ ] Backup database before deployment
- [ ] Monitor migration execution time

### Database Performance Checklist

- [ ] All foreign keys have indexes
- [ ] Composite indexes on frequently joined columns
- [ ] No missing indexes on WHERE clause columns
- [ ] Slow query log reviewed monthly
- [ ] Large tables partitioned or archived
- [ ] Query cache hit rate >80%
- [ ] No queries taking >1 second
- [ ] Connection pooling configured
- [ ] Database backups automated and tested
- [ ] Monitoring alerts configured

### Security Checklist

- [ ] All user input parameterized (no SQL injection)
- [ ] Database users follow least privilege principle
- [ ] Sensitive data encrypted at rest
- [ ] Passwords hashed with bcrypt/argon2
- [ ] Row-level security implemented for multi-tenancy
- [ ] SSL/TLS enabled for database connections
- [ ] Database credentials not in version control
- [ ] Regular security audits scheduled
- [ ] Failed login attempts monitored
- [ ] Database activity logs enabled

## Anti-Patterns

### 1. ❌ Not Using Transactions for Related Operations

```php
// BAD - Risk of partial completion
public function createInvoiceWithItems(array $data)
{
    $invoice = Invoice::create($data['invoice']);

    foreach ($data['items'] as $item) {
        InvoiceItem::create($item); // If this fails, invoice already created!
    }
}

// GOOD - Atomic operation
public function createInvoiceWithItems(array $data)
{
    DB::transaction(function () use ($data) {
        $invoice = Invoice::create($data['invoice']);

        foreach ($data['items'] as $item) {
            $invoice->items()->create($item);
        }
    });
}
```

### 2. ❌ Inefficient Subqueries

```php
// BAD
$invoices = Invoice::whereIn('client_id', function ($query) {
    $query->select('id')->from('clients')->where('status', 'active');
})->get();

// GOOD - Use join
$invoices = Invoice::join('clients', 'invoices.client_id', '=', 'clients.id')
    ->where('clients.status', 'active')
    ->select('invoices.*')
    ->get();
```

### 3. ❌ Using FLOAT for Money

```php
// BAD - Precision loss
$table->float('amount'); // Can lose precision!

// GOOD - Use DECIMAL
$table->decimal('amount', 10, 2); // Exact precision
```

### 4. ❌ Missing Company Scoping in Multi-Tenant App

```php
// BAD - Can access other companies' data
$invoices = Invoice::all();

// GOOD - Always scope by company
$invoices = Invoice::where('company_id', auth()->user()->company_id)->get();

// BEST - Use global scope
class Invoice extends Model
{
    protected static function booted()
    {
        static::addGlobalScope(new CompanyScope);
    }
}
```

### 5. ❌ Not Validating Foreign Key References

```php
// BAD - Can create orphaned records
Invoice::create([
    'client_id' => 999999, // Non-existent client
    'total' => 1000,
]);

// GOOD - Validate existence
$validated = $request->validate([
    'client_id' => 'required|exists:clients,id',
    'total' => 'required|numeric',
]);

Invoice::create($validated);
```

### 6. ❌ Loading All Records Then Filtering

```php
// BAD - Loads entire table into memory
$paidInvoices = Invoice::all()->where('status', 'paid');

// GOOD - Filter in database
$paidInvoices = Invoice::where('status', 'paid')->get();
```

### 7. ❌ Not Using Prepared Statements

```php
// BAD - SQL injection risk
$results = DB::select("SELECT * FROM users WHERE email = '$email'");

// GOOD - Prepared statement
$results = DB::select("SELECT * FROM users WHERE email = ?", [$email]);
```

## Integration Guidance

### Integration with Laravel Queue System

```php
// Defer heavy database operations to queue
class ProcessLargeImport implements ShouldQueue
{
    public function handle()
    {
        DB::transaction(function () {
            // Process in chunks to avoid memory issues
            collect($this->importData)->chunk(1000)->each(function ($chunk) {
                Invoice::insert($chunk->toArray());
            });
        });
    }
}
```

### Integration with Redis Cache

```php
// Use Redis for database query caching
Cache::store('redis')->remember('company_invoices_' . $companyId, 3600, function () {
    return Invoice::where('company_id', $companyId)
        ->with('client')
        ->get();
});
```

### Integration with Laravel Scout (Search)

```php
// Full-text search with Scout
class Invoice extends Model
{
    use Searchable;

    public function toSearchableArray()
    {
        return [
            'invoice_number' => $this->invoice_number,
            'client_name' => $this->client->name,
            'description' => $this->description,
        ];
    }
}

// Search
$results = Invoice::search('INV-2024')->get();
```

## Best Practices Summary

### 1. Always Use Migrations

- Never modify database manually
- Always create migrations for schema changes
- Test migrations before deploying
- Keep migrations in version control

### 2. Index Strategically

- Index foreign keys
- Index columns used in WHERE, JOIN, ORDER BY
- Use composite indexes for common query patterns
- Don't over-index

### 3. Optimize Queries

- Use eager loading to prevent N+1
- Select only needed columns
- Use database aggregations
- Paginate large result sets
- Cache expensive queries

### 4. Ensure Data Integrity

- Use foreign key constraints
- Validate data in application layer
- Use transactions for related operations
- Implement soft deletes where appropriate

### 5. Secure Your Database

- Use parameterized queries
- Follow least privilege principle
- Encrypt sensitive data
- Implement row-level security for multi-tenancy
- Never commit credentials

### 6. Monitor Performance

- Enable slow query logging
- Use Laravel Telescope in development
- Monitor connection pool usage
- Track table sizes
- Set up alerts for anomalies

### 7. Plan for Scale

- Partition large tables
- Archive old data
- Use read replicas for reporting
- Implement connection pooling
- Consider sharding for extreme scale

## Resources

- **MySQL Documentation**: https://dev.mysql.com/doc/
- **Laravel Database**: https://laravel.com/docs/database
- **Laravel Migrations**: https://laravel.com/docs/migrations
- **MySQL Performance**: https://dev.mysql.com/doc/refman/8.0/en/optimization.html
- **Use The Index, Luke**: https://use-the-index-luke.com/
- **Database Normalization**: https://www.guru99.com/database-normalization.html
- **EXPLAIN Format**: https://dev.mysql.com/doc/refman/8.0/en/explain-output.html
- **Laravel Eloquent Performance**: https://laravel.com/docs/eloquent#performance
- **Percona Toolkit**: https://www.percona.com/software/database-tools/percona-toolkit

## Quick Reference

```sql
-- Check table structure
DESCRIBE table_name;

-- Check indexes
SHOW INDEX FROM table_name;

-- Analyze query
EXPLAIN SELECT ...;

-- Check foreign keys
SELECT * FROM information_schema.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = 'boekhouder'
AND TABLE_NAME = 'invoices';

-- Find missing indexes (queries doing table scans)
SELECT * FROM sys.statements_with_full_table_scans;

-- Check table sizes
SELECT table_name, ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb
FROM information_schema.TABLES
WHERE table_schema = DATABASE()
ORDER BY (data_length + index_length) DESC;

-- Find unused indexes
SELECT * FROM sys.schema_unused_indexes;

-- Check current connections
SHOW PROCESSLIST;

-- Kill a connection
KILL CONNECTION_ID;
```

---

**Version 2.0.0** - Enhanced with 25+ improvements including: advanced troubleshooting, comprehensive checklists, security best practices, monitoring strategies, testing guidance, integration patterns, anti-patterns, performance optimization techniques, and project-specific examples for Dutch bookkeeping application.

**Remember**: Normalize for data integrity, denormalize for performance, but always measure before optimizing!

---

## 100 Database & MySQL Tips, Best Practices & Modern Features (2025)

### Schema Design (1-20)

1. **Use appropriate data types** - Don't use VARCHAR(255) for everything; size matters
2. **BIGINT UNSIGNED for IDs** - Supports 18 quintillion records, future-proof
3. **DECIMAL for money** - Never use FLOAT/DOUBLE for financial data
4. **DATETIME vs TIMESTAMP** - DATETIME for business dates, TIMESTAMP for audit trails
5. **NOT NULL by default** - Null complicates queries; use defaults instead
6. **Consistent naming conventions** - snake_case for columns, plural for tables
7. **Avoid reserved words** - Don't name columns `order`, `key`, `group`
8. **Use ENUM sparingly** - Hard to modify; consider lookup tables instead
9. **JSON columns for flexible data** - MySQL 8.0+ has excellent JSON support
10. **Generated/virtual columns** - Computed columns for denormalization
11. **Normalize to 3NF, then denormalize strategically** - Start clean, optimize as needed
12. **Foreign keys for referential integrity** - Enforce relationships at database level
13. **Composite primary keys carefully** - Often better to use surrogate key
14. **UUID vs auto-increment** - UUID for distributed systems, auto-increment for performance
15. **Soft deletes with deleted_at** - Preserve audit trail for financial records
16. **Separate audit tables** - Don't bloat main tables with history
17. **Partition large tables** - By date range for time-series data
18. **Archive old data** - Move historical data to archive tables
19. **Use CHECK constraints (MySQL 8.0.16+)** - Enforce data integrity rules
20. **Document your schema** - Comments on tables and columns

### Indexing Strategies (21-40)

21. **Index columns in WHERE clauses** - Most common optimization
22. **Index foreign keys** - Required for JOIN performance
23. **Composite indexes: left-to-right** - Order by selectivity, most used first
24. **Covering indexes** - Include SELECT columns to avoid table lookup
25. **Prefix indexes for text** - `INDEX idx_name (name(20))` for long strings
26. **Invisible indexes for testing** - MySQL 8.0+ test removal without dropping
27. **Don't over-index** - Each index slows writes
28. **Analyze index usage** - Query `sys.schema_unused_indexes`
29. **Monitor index cardinality** - Low cardinality indexes are less useful
30. **Functional indexes (MySQL 8.0.13+)** - Index on expressions
31. **Descending indexes** - For ORDER BY DESC queries
32. **Full-text indexes for search** - Better than LIKE '%term%'
33. **Spatial indexes for geo** - For location-based queries
34. **Hash indexes for Memory engine** - Exact match only
35. **Unique indexes enforce constraints** - Not just for performance
36. **Avoid indexing frequently updated columns** - Index maintenance overhead
37. **Remove duplicate indexes** - MySQL allows them; use pt-duplicate-key-checker
38. **Index columns used in ORDER BY** - Avoid filesort
39. **Index columns in GROUP BY** - Improves aggregation performance
40. **Monitor slow queries for missing indexes** - Slow query log analysis

### Query Optimization (41-60)

41. **Use EXPLAIN ANALYZE (MySQL 8.0.18+)** - Actual execution statistics
42. **Avoid SELECT *** - Fetch only needed columns
43. **Use LIMIT for pagination** - But beware of deep pagination
44. **Prefer JOIN to subquery** - Usually more efficient
45. **Use EXISTS instead of IN for subqueries** - Short-circuits on match
46. **Avoid OR in WHERE** - Often prevents index use; use UNION instead
47. **Minimize functions on indexed columns** - `WHERE DATE(created_at)` kills index
48. **Batch INSERT statements** - Group into chunks of 1000
49. **Use INSERT...ON DUPLICATE KEY UPDATE** - Upsert pattern
50. **Replace DELETE with soft delete** - For audit compliance
51. **Use transactions for related operations** - Atomic consistency
52. **Set appropriate isolation levels** - READ COMMITTED for most cases
53. **Avoid long-running transactions** - Lock contention issues
54. **Use query caching at application level** - MySQL query cache removed in 8.0
55. **Optimize COUNT queries** - Avoid SELECT COUNT(*) on large tables
56. **Use cursor-based pagination** - `WHERE id > ?` instead of OFFSET
57. **Denormalize for read-heavy tables** - Store calculated totals
58. **Use window functions** - ROW_NUMBER(), RANK() for analytics
59. **CTEs for complex queries** - WITH clause improves readability
60. **Prepared statements** - Reuse query plans, prevent injection

### InnoDB Configuration (61-70)

61. **Buffer pool size** - 70-80% of RAM on dedicated servers
62. **Innodb_flush_log_at_trx_commit** - 2 for performance, 1 for durability
63. **Innodb_log_file_size** - Larger for write-heavy workloads
64. **Innodb_buffer_pool_instances** - 8 for large buffer pools
65. **Innodb_io_capacity** - Match to disk IOPS capability
66. **Innodb_read_io_threads/write_io_threads** - Increase for SSDs
67. **Innodb_page_size** - Default 16K, can use 4K for many small rows
68. **Enable innodb_file_per_table** - Separate .ibd files per table
69. **Innodb_flush_method** - O_DIRECT on Linux with SSD
70. **Monitor buffer pool hit ratio** - Should be >95%

### MySQL 8.0+ Features (71-80)

71. **Window functions** - OVER(), PARTITION BY, LAG(), LEAD()
72. **Common Table Expressions** - WITH clause for readable subqueries
73. **JSON functions** - JSON_EXTRACT(), JSON_SET(), JSON_ARRAYAGG()
74. **Instant ADD COLUMN** - No table rebuild for adding columns
75. **Resource groups** - Prioritize critical queries
76. **Data dictionary in InnoDB** - Atomic DDL operations
77. **Histogram statistics** - Better query optimization
78. **Roles for permission management** - Simplify user management
79. **Invisible columns** - Hide from SELECT *
80. **Binary log transaction compression** - Reduce replication bandwidth

### Laravel-Specific (81-90)

81. **Use Eloquent for safety** - Auto-parameterization prevents injection
82. **Eager loading** - `with()` to prevent N+1 queries
83. **Chunk for large datasets** - `chunk(1000, fn)` for memory efficiency
84. **Use database transactions** - `DB::transaction()` for atomicity
85. **Optimize migrations** - Check for existing columns/tables
86. **Indexes in migrations** - Always index foreign keys
87. **Use query scopes** - Reusable query conditions
88. **Raw expressions carefully** - `DB::raw()` with bindings
89. **Database assertions in tests** - `assertDatabaseHas()`, `assertDatabaseCount()`
90. **Telescope for query debugging** - View all queries in development

### Maintenance & Monitoring (91-100)

91. **Run ANALYZE TABLE periodically** - Update statistics
92. **OPTIMIZE TABLE for fragmented tables** - Reclaim space
93. **Monitor slow query log** - Identify performance issues
94. **Set up replication** - For high availability and read scaling
95. **Automated backups** - Daily with point-in-time recovery
96. **Test backup restoration** - Untested backups are useless
97. **Monitor disk usage** - Prevent full disk crashes
98. **Set up deadlock detection** - Alert on deadlock occurrences
99. **Track query patterns over time** - Identify regressions
100. **Plan for scaling** - Read replicas, sharding strategies

---

## MySQL Performance Tuning Quick Reference

### EXPLAIN Output Fields

| Field | Description | Optimize When |
|-------|-------------|---------------|
| `type: ALL` | Full table scan | Always optimize |
| `type: index` | Full index scan | Consider covering index |
| `type: ref` | Non-unique index lookup | Good |
| `type: eq_ref` | Unique index lookup | Excellent |
| `type: const` | Constant lookup | Best |
| `possible_keys` | Indexes considered | Check if expected index missing |
| `key` | Index used | NULL means no index used |
| `rows` | Estimated rows scanned | High = optimize |
| `Extra: Using filesort` | Sorting in memory | Add index on ORDER BY columns |
| `Extra: Using temporary` | Temp table created | Optimize GROUP BY/ORDER BY |
| `Extra: Using index` | Covering index | Excellent |

### Index Tuning SQL Commands

```sql
-- Show index usage statistics
SELECT * FROM sys.schema_index_statistics 
WHERE table_schema = 'boekhouder' 
ORDER BY rows_selected DESC;

-- Find unused indexes
SELECT * FROM sys.schema_unused_indexes 
WHERE object_schema = 'boekhouder';

-- Find redundant indexes
SELECT * FROM sys.schema_redundant_indexes 
WHERE table_schema = 'boekhouder';

-- Find queries doing full table scans
SELECT * FROM sys.statements_with_full_table_scans 
WHERE db = 'boekhouder' 
ORDER BY exec_count DESC LIMIT 10;

-- Check table sizes
SELECT 
    table_name,
    ROUND(data_length / 1024 / 1024, 2) AS 'Data MB',
    ROUND(index_length / 1024 / 1024, 2) AS 'Index MB',
    ROUND((data_length + index_length) / 1024 / 1024, 2) AS 'Total MB'
FROM information_schema.tables 
WHERE table_schema = 'boekhouder'
ORDER BY (data_length + index_length) DESC;

-- Check InnoDB buffer pool hit rate
SELECT 
    (1 - (Innodb_buffer_pool_reads / Innodb_buffer_pool_read_requests)) * 100 AS hit_rate
FROM information_schema.GLOBAL_STATUS 
WHERE Variable_name IN ('Innodb_buffer_pool_reads', 'Innodb_buffer_pool_read_requests');
```

### Migration Best Practices Checklist

- [ ] Foreign key tables exist before referencing
- [ ] Indexes on all foreign key columns
- [ ] `after()` references existing columns only
- [ ] No duplicate table/column definitions
- [ ] Rollback (`down()`) method implemented
- [ ] Test on copy of production data
- [ ] Backup before running on production
- [ ] Monitor lock duration during migration

---

## MySQL Internals Deep Dive

Understanding MySQL's internal architecture helps you write better queries, design optimal schemas, and troubleshoot complex issues.

### InnoDB Storage Engine Architecture

InnoDB is MySQL's default transactional storage engine. Understanding its internals is crucial for performance optimization.

#### Buffer Pool

The buffer pool is InnoDB's main memory area where data and indexes are cached.

```
┌─────────────────────────────────────────────────────────────────┐
│                       BUFFER POOL                                │
├─────────────────────────────────────────────────────────────────┤
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐              │
│  │  Data Pages │  │ Index Pages │  │  Free List  │              │
│  └─────────────┘  └─────────────┘  └─────────────┘              │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐              │
│  │  Undo Pages │  │ Adaptive HI │  │  Change Buf │              │
│  └─────────────┘  └─────────────┘  └─────────────┘              │
├─────────────────────────────────────────────────────────────────┤
│  LRU List (New Sublist) ─────────────────────────────────────── │
│  LRU List (Old Sublist) ─────────────────────────────────────── │
│  Flush List (Dirty Pages) ───────────────────────────────────── │
└─────────────────────────────────────────────────────────────────┘
```

**Key concepts:**

1. **Data Pages**: 16KB blocks containing table rows
2. **Index Pages**: B+tree index nodes
3. **Undo Pages**: For MVCC rollback and consistent reads
4. **Adaptive Hash Index (AHI)**: Auto-built hash index for frequent lookups
5. **Change Buffer**: Caches secondary index changes

**LRU Algorithm with Midpoint Insertion:**
```
┌──────────────────────────────────────────────────────────────────┐
│ HOT (New Sublist - 5/8)          │ COLD (Old Sublist - 3/8)      │
│ ◄─── Frequently accessed ────────│──── Recently loaded ───────► │
│ [Head]                           │ [Midpoint]            [Tail] │
└──────────────────────────────────────────────────────────────────┘

New pages inserted at midpoint (3/8 from tail)
Move to head only after second access within innodb_old_blocks_time
Prevents full table scans from flushing hot pages
```

**Configuration:**
```ini
# my.cnf
innodb_buffer_pool_size = 12G          # 70-80% of RAM on dedicated server
innodb_buffer_pool_instances = 8        # Reduce contention (1 per GB)
innodb_old_blocks_time = 1000          # ms before page moves to new sublist
innodb_old_blocks_pct = 37             # % of buffer pool for old sublist
```

**Monitoring:**
```sql
-- Buffer pool statistics
SELECT
    pool_id,
    pool_size * 16 / 1024 AS pool_size_mb,
    free_buffers * 16 / 1024 AS free_mb,
    database_pages * 16 / 1024 AS data_mb,
    old_database_pages * 16 / 1024 AS old_data_mb,
    modified_database_pages * 16 / 1024 AS dirty_mb
FROM information_schema.INNODB_BUFFER_POOL_STATS;

-- Hit rate (should be >99%)
SHOW STATUS LIKE 'Innodb_buffer_pool_read%';
-- Calculate: (1 - reads/read_requests) * 100
```

#### Redo Log (Write-Ahead Logging)

The redo log ensures durability by recording changes before they're applied to data files.

```
┌──────────────────────────────────────────────────────────────┐
│                    REDO LOG SYSTEM                            │
├──────────────────────────────────────────────────────────────┤
│  Transaction                                                  │
│      │                                                        │
│      ▼                                                        │
│  ┌────────────┐    ┌────────────┐    ┌────────────────────┐  │
│  │ Log Buffer │───►│ ib_logfile0│◄──►│ ib_logfile1       │  │
│  │ (in RAM)   │    │ (circular) │    │ (circular)        │  │
│  └────────────┘    └────────────┘    └────────────────────┘  │
│      │                    │                                   │
│      │ fsync()            │ Checkpoint                       │
│      ▼                    ▼                                   │
│  [Commit ACK]        [Data Files]                            │
└──────────────────────────────────────────────────────────────┘
```

**Configuration:**
```ini
# Redo log sizing (MySQL 8.0.30+)
innodb_redo_log_capacity = 4G        # Total redo log capacity

# Pre-8.0.30
innodb_log_file_size = 1G            # Size of each log file
innodb_log_files_in_group = 2        # Number of log files

# Flush behavior
innodb_flush_log_at_trx_commit = 1   # 1=durable, 2=fast (flush per second)
```

**Monitor log usage:**
```sql
SHOW ENGINE INNODB STATUS\G
-- Look for: Log sequence number, Log flushed up to, Last checkpoint at

-- Log checkpoint lag
SELECT
    (SELECT VARIABLE_VALUE FROM performance_schema.global_status
     WHERE VARIABLE_NAME = 'Innodb_os_log_written') as log_written_bytes,
    (SELECT VARIABLE_VALUE FROM performance_schema.global_status
     WHERE VARIABLE_NAME = 'Innodb_log_waits') as log_waits;
```

#### Undo Log and MVCC

The undo log enables Multi-Version Concurrency Control (MVCC) for consistent reads.

```
┌─────────────────────────────────────────────────────────────────┐
│                    MVCC - Row Versioning                        │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  Current Row (in tablespace)                                    │
│  ┌────────────────────────────────────────────┐                 │
│  │ DB_TRX_ID: 100 │ DB_ROLL_PTR ─────────────┼──┐               │
│  │ actual data columns...                    │  │               │
│  └────────────────────────────────────────────┘  │               │
│                                                   ▼               │
│  Undo Log (older versions)                   ┌────────────┐     │
│                                              │ Version N-1│     │
│                                              │ TRX_ID: 95 │     │
│                                              │ ROLL_PTR ──┼──┐  │
│                                              └────────────┘  │  │
│                                                              ▼  │
│                                              ┌────────────┐     │
│                                              │ Version N-2│     │
│                                              │ TRX_ID: 90 │     │
│                                              └────────────┘     │
└─────────────────────────────────────────────────────────────────┘
```

**Hidden columns in every InnoDB row:**
1. `DB_TRX_ID` (6 bytes): Transaction ID that last modified the row
2. `DB_ROLL_PTR` (7 bytes): Pointer to undo log record
3. `DB_ROW_ID` (6 bytes): Auto-increment row ID if no PK defined

**Transaction visibility:**
```sql
-- When transaction reads, it sees only:
-- 1. Rows committed before transaction started (REPEATABLE READ)
-- 2. Rows committed before statement started (READ COMMITTED)
-- 3. Its own modifications

-- Example of MVCC in action:
-- Session 1
START TRANSACTION;
SELECT balance FROM accounts WHERE id = 1;  -- Returns 1000

-- Session 2 (concurrent)
START TRANSACTION;
UPDATE accounts SET balance = 500 WHERE id = 1;
COMMIT;

-- Session 1 (still sees 1000 due to MVCC)
SELECT balance FROM accounts WHERE id = 1;  -- Still returns 1000!
COMMIT;
```

#### Doublewrite Buffer

Protection against partial page writes (torn pages) during crashes.

```
┌─────────────────────────────────────────────────────────────────┐
│                    DOUBLEWRITE PROCESS                          │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  Dirty Pages in Buffer Pool                                     │
│         │                                                        │
│         │ 1. Sequential write                                   │
│         ▼                                                        │
│  ┌─────────────────────────────────────────┐                    │
│  │         Doublewrite Buffer               │                    │
│  │    (2MB in system tablespace or          │                    │
│  │     dedicated files in MySQL 8.0.20+)    │                    │
│  └─────────────────────────────────────────┘                    │
│         │                                                        │
│         │ 2. fsync() - ensure durability                        │
│         │                                                        │
│         │ 3. Random writes to actual locations                  │
│         ▼                                                        │
│  ┌─────────────────────────────────────────┐                    │
│  │         Data Files (.ibd)                │                    │
│  └─────────────────────────────────────────┘                    │
│                                                                  │
│  Recovery: If page corrupted, restore from doublewrite buffer   │
└─────────────────────────────────────────────────────────────────┘
```

**Configuration:**
```ini
# MySQL 8.0.20+ - Dedicated doublewrite files
innodb_doublewrite_dir = /fast/nvme/doublewrite
innodb_doublewrite_files = 2
innodb_doublewrite_batch_size = 16

# Disable only if using atomic write storage (some SSDs, ZFS)
innodb_doublewrite = ON  # Keep ON for safety
```

### B+Tree Index Mechanics

Understanding how InnoDB indexes work enables better query optimization.

```
┌─────────────────────────────────────────────────────────────────┐
│                    B+TREE STRUCTURE                              │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│                      ┌───────────────┐                          │
│                      │   Root Node   │                          │
│                      │ [30] [60] [90]│                          │
│                      └───────┬───────┘                          │
│              ┌───────────────┼───────────────┐                  │
│              ▼               ▼               ▼                  │
│      ┌───────────┐   ┌───────────┐   ┌───────────┐             │
│      │ Internal  │   │ Internal  │   │ Internal  │             │
│      │ [10] [20] │   │ [40] [50] │   │ [70] [80] │             │
│      └─────┬─────┘   └─────┬─────┘   └─────┬─────┘             │
│            ▼               ▼               ▼                    │
│      ┌─────────┐     ┌─────────┐     ┌─────────┐               │
│      │  Leaf   │────►│  Leaf   │────►│  Leaf   │               │
│      │ [1][2]..│     │ [31][32]│     │ [61][62]│               │
│      │ + Data  │◄────│ + Data  │◄────│ + Data  │               │
│      └─────────┘     └─────────┘     └─────────┘               │
│        (doubly linked list for range scans)                     │
│                                                                  │
│  PRIMARY KEY (Clustered):  Leaf nodes contain actual row data  │
│  SECONDARY INDEX:          Leaf nodes contain PK values        │
└─────────────────────────────────────────────────────────────────┘
```

**Primary (Clustered) vs Secondary Index:**

```php
// Table structure
CREATE TABLE invoices (
    id BIGINT UNSIGNED PRIMARY KEY,    -- Clustered index
    company_id INT,
    invoice_number VARCHAR(50),
    INDEX idx_company (company_id),     -- Secondary index
    UNIQUE idx_number (invoice_number)  -- Secondary unique index
);

// Clustered Index (PRIMARY KEY):
// - Leaf nodes contain actual row data
// - Table IS the index
// - Only one per table

// Secondary Index:
// - Leaf nodes contain (indexed_columns, primary_key)
// - Lookup requires two steps: index → primary key → data
// - This is why wide PKs hurt performance

// Query execution example:
SELECT * FROM invoices WHERE company_id = 5;
// 1. Search idx_company B+tree for company_id = 5
// 2. Get list of primary keys (id values)
// 3. For each PK, search PRIMARY index to get row
// This is called a "double lookup" or "bookmark lookup"
```

**Page Structure:**
```
┌─────────────────────────────────────────────────────────────────┐
│                    16KB INDEX PAGE                               │
├─────────────────────────────────────────────────────────────────┤
│  ┌────────────────────────────────────────────────────────────┐ │
│  │ FIL Header (38 bytes)                                      │ │
│  │ - Checksum, Page number, Previous/Next page pointers       │ │
│  └────────────────────────────────────────────────────────────┘ │
│  ┌────────────────────────────────────────────────────────────┐ │
│  │ INDEX Header (36 bytes)                                    │ │
│  │ - Number of heap records, format flag, etc.                │ │
│  └────────────────────────────────────────────────────────────┘ │
│  ┌────────────────────────────────────────────────────────────┐ │
│  │ System Records                                             │ │
│  │ - Infimum (smallest record)                                │ │
│  │ - Supremum (largest record)                                │ │
│  └────────────────────────────────────────────────────────────┘ │
│  ┌────────────────────────────────────────────────────────────┐ │
│  │ User Records ──────────────────────────────────────────────│ │
│  │ │ Record 1 │ Record 2 │ Record 3 │ ... │                   │ │
│  │ (grows downward)                                           │ │
│  └────────────────────────────────────────────────────────────┘ │
│                           ↕ Free Space                          │
│  ┌────────────────────────────────────────────────────────────┐ │
│  │ Page Directory (grows upward)                              │ │
│  │ - Sparse array of pointers for binary search               │ │
│  └────────────────────────────────────────────────────────────┘ │
│  ┌────────────────────────────────────────────────────────────┐ │
│  │ FIL Trailer (8 bytes) - Checksum for consistency           │ │
│  └────────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────────┘
```

**Index split behavior:**
```sql
-- When a page is full and you INSERT:
-- 1. Allocate new page
-- 2. Move ~50% of records to new page
-- 3. Update parent pointers
-- 4. Insert new record

-- Random inserts cause more splits than sequential
-- This is why auto-increment PKs are often faster than UUIDs

-- Monitor page splits:
SELECT * FROM sys.schema_index_statistics
WHERE table_schema = 'boekhouder'
ORDER BY rows_inserted DESC;
```

### InnoDB Locking Deep Dive

Understanding locks prevents deadlocks and improves concurrency.

#### Lock Types

```
┌─────────────────────────────────────────────────────────────────┐
│                    LOCK TYPES                                    │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  SHARED (S) LOCK          EXCLUSIVE (X) LOCK                    │
│  - Read lock              - Write lock                          │
│  - Multiple S compatible  - Blocks all other locks             │
│  - SELECT ... FOR SHARE   - SELECT ... FOR UPDATE              │
│                           - UPDATE, DELETE                       │
│                                                                  │
│  ┌─────────────────────────────────────────────────────────┐    │
│  │ Compatibility Matrix:                                    │    │
│  │                  Requested                               │    │
│  │              │    S    │    X    │                       │    │
│  │      ────────┼─────────┼─────────┤                       │    │
│  │  Held    S   │   ✓     │    ✗    │                       │    │
│  │          X   │   ✗     │    ✗    │                       │    │
│  └─────────────────────────────────────────────────────────┘    │
│                                                                  │
│  INTENTION LOCKS (Table-level)                                  │
│  - IS: Intend to set S lock on rows                            │
│  - IX: Intend to set X lock on rows                            │
│  - Allows concurrent row locking without table lock conflicts   │
└─────────────────────────────────────────────────────────────────┘
```

#### Row Locking Strategies

```
┌─────────────────────────────────────────────────────────────────┐
│                    ROW LOCK TYPES                                │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  RECORD LOCK                                                    │
│  - Locks a single index record                                  │
│  - WHERE id = 5 (on indexed column)                            │
│                                                                  │
│  GAP LOCK                                                       │
│  - Locks the gap BETWEEN index records                         │
│  - Prevents phantom reads in REPEATABLE READ                   │
│  - Example: Lock gap between id=5 and id=10                    │
│                                                                  │
│  NEXT-KEY LOCK                                                  │
│  - Combination: Record lock + Gap lock before it               │
│  - Default in REPEATABLE READ                                  │
│                                                                  │
│  Visual:                                                        │
│  Records:  [1]    [5]    [10]    [15]                          │
│                                                                  │
│  Record Lock on id=5:       [5]                                │
│  Gap Lock (5,10):           ─────────                          │
│  Next-Key Lock:       [5]───────────                           │
│                                                                  │
│  INSERT INTENTION LOCK                                          │
│  - Special gap lock for INSERT                                 │
│  - Multiple transactions can insert in same gap                │
│  - Only blocks if inserting same key                           │
└─────────────────────────────────────────────────────────────────┘
```

**Practical locking examples:**
```sql
-- REPEATABLE READ (default) with different queries

-- Query 1: Equality on unique index → Record lock only
SELECT * FROM invoices WHERE id = 5 FOR UPDATE;
-- Locks: Record lock on id=5

-- Query 2: Equality on non-unique index → Next-key locks
SELECT * FROM invoices WHERE company_id = 1 FOR UPDATE;
-- Locks: Next-key locks on all matching rows
-- Prevents inserts of new company_id=1 rows

-- Query 3: Range scan → Gap and next-key locks
SELECT * FROM invoices WHERE id BETWEEN 5 AND 10 FOR UPDATE;
-- Locks: Next-key locks on [5,6,7,8,9,10] and gap after 10

-- Query 4: No index (full scan) → Table lock!
SELECT * FROM invoices WHERE unindexed_col = 'x' FOR UPDATE;
-- Locks ALL rows! This is terrible for concurrency
```

**Detecting and preventing deadlocks:**
```sql
-- View current locks
SELECT * FROM performance_schema.data_locks;

-- View lock waits
SELECT * FROM performance_schema.data_lock_waits;

-- View InnoDB status including deadlock info
SHOW ENGINE INNODB STATUS\G

-- Recent deadlock in output:
-- LATEST DETECTED DEADLOCK section shows:
-- - Transaction 1 waiting for lock
-- - Transaction 2 holding lock and waiting
-- - Which transaction was rolled back
```

```php
// Laravel: Consistent lock ordering to prevent deadlocks
DB::transaction(function () {
    // BAD: Random order causes deadlocks
    $client = Client::lockForUpdate()->find($clientId);
    $invoice = Invoice::lockForUpdate()->find($invoiceId);

    // GOOD: Always lock in same order (e.g., by table name alphabetically)
    // Or lock parent before child
    $client = Client::lockForUpdate()->find($clientId);
    $invoice = Invoice::lockForUpdate()
        ->where('client_id', $clientId)
        ->where('id', $invoiceId)
        ->first();
});

// Retry on deadlock
DB::transaction(function () {
    // Your code
}, 5); // Laravel auto-retries on deadlock (5 attempts)
```

---

## 25 Lesser-Known MySQL Facts

Deep knowledge that separates experts from users.

### Storage & Architecture

1. **InnoDB pages are always 16KB by default** - Even if you store a 1-byte row, it occupies at least one page. Consider row density when designing schemas.

2. **CHAR is actually faster than VARCHAR for fixed-length data** - Because the offset to the next column is always predictable, eliminating length byte reads.

3. **NULL requires 1 bit per column in the row header** - This bitmap is rounded up to bytes, so 1-8 nullable columns = 1 byte overhead.

4. **Secondary index leaf nodes store the PRIMARY KEY** - This is why wide PKs (like UUID) bloat every secondary index.

5. **InnoDB pre-reads pages sequentially** - When it detects sequential access, it reads up to 64 pages ahead (1MB). Random access kills this optimization.

6. **The undo log can grow unbounded** - Long-running transactions prevent undo purge, causing tablespace bloat. Monitor `History list length`.

7. **BLOB/TEXT columns are stored off-page** - If the row is >8KB, large columns move to overflow pages, requiring extra I/O.

### Query Execution

8. **LIMIT doesn't stop early with ORDER BY on non-indexed columns** - MySQL must read and sort ALL rows, then return top N.

9. **COUNT(*) is optimized differently than COUNT(column)** - COUNT(*) counts rows, COUNT(column) counts non-NULL values and requires reading the column.

10. **OR conditions often prevent index usage** - Even if both sides would individually use indexes. Rewrite as UNION.

11. **DISTINCT uses temporary tables** - Unless there's an index that can provide the ordering. This affects memory usage.

12. **Subqueries in WHERE are executed per row in some cases** - Called "dependent subquery" in EXPLAIN. Use JOIN instead.

13. **LIKE 'prefix%' uses indexes, LIKE '%suffix' doesn't** - Leading wildcards force full scans. Use FULLTEXT for suffix searches.

### Transactions & Locking

14. **READ COMMITTED reads the latest committed version per statement** - Not per transaction. So the same SELECT can return different results within a transaction.

15. **Gap locks don't exist in READ COMMITTED** - Only record locks, which can cause phantom reads but improves concurrency.

16. **AUTO_INCREMENT gaps are normal** - Rolled-back inserts don't reuse IDs. InnoDB pre-allocates in batches for performance.

17. **MVCC keeps old row versions in undo log** - Long transactions can prevent cleanup, causing "history list length" to grow.

18. **SELECT ... FOR UPDATE acquires locks even if no rows match** - It locks the gap where the row would be, preventing phantom inserts.

### Performance

19. **The Query Cache was removed in MySQL 8.0** - It was a source of contention. Use application-level caching (Redis).

20. **Prepared statements are parsed once, executed many times** - But the plan is NOT cached between connections by default.

21. **EXPLAIN doesn't execute the query** - It estimates rows. EXPLAIN ANALYZE (8.0.18+) actually runs it with timing.

22. **innodb_buffer_pool_dump_at_shutdown preserves warm cache** - Reloads buffer pool contents on restart, avoiding cold-start penalty.

23. **Adaptive Hash Index is built automatically** - For frequently accessed pages. But can cause contention under high concurrency.

### Configuration

24. **innodb_flush_log_at_trx_commit=2 is 10-100x faster** - But you can lose 1 second of transactions on crash. Acceptable for non-financial data.

25. **Online DDL is limited** - Adding a column is instant (8.0.12+), but adding an index still copies data internally.

---

## 25 Advanced MySQL Tips & Tricks

### Schema Optimization

1. **Use invisible columns for backwards compatibility**
```sql
-- MySQL 8.0.23+
ALTER TABLE invoices ADD COLUMN temp_field INT INVISIBLE;
-- SELECT * won't return it, but SELECT temp_field works
```

2. **Partition by hash for even distribution**
```sql
CREATE TABLE events (
    id BIGINT,
    company_id INT,
    event_date DATE
) PARTITION BY HASH(company_id) PARTITIONS 8;
-- Queries filtering on company_id hit only 1/8 of the data
```

3. **Use generated columns for computed values**
```sql
ALTER TABLE invoices ADD COLUMN
    total_incl_vat DECIMAL(10,2) AS (total_ex_vat * (1 + vat_rate/100)) STORED;
CREATE INDEX idx_total ON invoices(total_incl_vat);
-- Index works on computed column!
```

### Query Optimization

4. **Use STRAIGHT_JOIN to force table order**
```sql
SELECT STRAIGHT_JOIN * FROM small_table
JOIN large_table ON ...
-- Forces optimizer to read small_table first
```

5. **Use covering indexes aggressively**
```sql
-- Query: SELECT id, status FROM invoices WHERE company_id = ? ORDER BY created_at
CREATE INDEX idx_cover ON invoices(company_id, created_at, id, status);
-- All columns in index = no table lookup needed
```

6. **Use index hints when optimizer is wrong**
```sql
SELECT * FROM invoices USE INDEX (idx_company_status)
WHERE company_id = 1 AND status = 'paid';
-- Or FORCE INDEX if USE doesn't work
```

7. **Optimize pagination with deferred join**
```sql
-- BAD: OFFSET 10000 reads and discards 10000 rows
SELECT * FROM invoices ORDER BY id LIMIT 10 OFFSET 10000;

-- GOOD: Deferred join - only fetch IDs first
SELECT i.* FROM invoices i
JOIN (SELECT id FROM invoices ORDER BY id LIMIT 10 OFFSET 10000) AS sub
ON i.id = sub.id;
```

8. **Use EXPLAIN FORMAT=TREE for better visualization**
```sql
EXPLAIN FORMAT=TREE SELECT ...;
-- Shows actual execution plan as tree structure
```

### Configuration Tricks

9. **Tune sort_buffer_size per-connection**
```sql
-- For a specific heavy query:
SET SESSION sort_buffer_size = 4*1024*1024; -- 4MB
SELECT ... ORDER BY ...;
SET SESSION sort_buffer_size = DEFAULT;
```

10. **Use performance_schema for query analysis**
```sql
-- Top 10 queries by total time
SELECT DIGEST_TEXT, COUNT_STAR, SUM_TIMER_WAIT/1000000000 as total_ms
FROM performance_schema.events_statements_summary_by_digest
ORDER BY SUM_TIMER_WAIT DESC LIMIT 10;
```

### Debugging

11. **Use SHOW WARNINGS after EXPLAIN**
```sql
EXPLAIN SELECT * FROM invoices WHERE YEAR(created_at) = 2024;
SHOW WARNINGS;
-- Shows: "Cannot use range access on index..."
```

12. **Profile slow queries**
```sql
SET profiling = 1;
SELECT * FROM invoices WHERE ...;
SHOW PROFILE ALL FOR QUERY 1;
-- Shows time spent in each stage
```

13. **Check index usage with sys schema**
```sql
SELECT * FROM sys.schema_unused_indexes WHERE object_schema = 'boekhouder';
SELECT * FROM sys.schema_redundant_indexes WHERE table_schema = 'boekhouder';
```

### Concurrency

14. **Use SELECT ... SKIP LOCKED for queue tables**
```sql
-- Worker 1
SELECT * FROM jobs WHERE status = 'pending' LIMIT 1 FOR UPDATE SKIP LOCKED;
-- Gets job 1

-- Worker 2 (concurrent)
SELECT * FROM jobs WHERE status = 'pending' LIMIT 1 FOR UPDATE SKIP LOCKED;
-- Skips locked job 1, gets job 2 - no waiting!
```

15. **Use NOWAIT to fail fast on lock contention**
```sql
SELECT * FROM invoices WHERE id = 5 FOR UPDATE NOWAIT;
-- Throws error immediately if row is locked instead of waiting
```

### Maintenance

16. **Rebuild indexes without downtime**
```sql
-- MySQL 8.0
ALTER TABLE invoices ALTER INDEX idx_company INVISIBLE;
-- Test if queries still work
ALTER TABLE invoices DROP INDEX idx_company;
ALTER TABLE invoices ADD INDEX idx_company_v2 (...), ALGORITHM=INPLACE, LOCK=NONE;
```

17. **Use pt-online-schema-change for ALTER on huge tables**
```bash
pt-online-schema-change --alter "ADD COLUMN new_col INT" \
    D=boekhouder,t=invoices --execute
# Zero downtime, creates trigger-based copy
```

18. **Monitor replication lag**
```sql
SHOW SLAVE STATUS\G
-- Look for: Seconds_Behind_Master
-- Or use pt-heartbeat for more accurate measurement
```

### Security

19. **Use caching_sha2_password (MySQL 8 default)**
```sql
CREATE USER 'app'@'%' IDENTIFIED WITH caching_sha2_password BY 'password';
-- More secure than mysql_native_password
```

20. **Audit with general_log temporarily**
```sql
SET GLOBAL general_log = ON;
SET GLOBAL log_output = 'TABLE';
-- Query mysql.general_log table
-- Remember to turn off: SET GLOBAL general_log = OFF;
```

### Laravel-Specific

21. **Use upsert() for bulk insert-or-update**
```php
Invoice::upsert(
    $records,
    ['invoice_number', 'company_id'],  // Unique keys
    ['total', 'updated_at']            // Columns to update if exists
);
// Single query instead of N queries
```

22. **Use lockForUpdate() with SKIP LOCKED**
```php
$job = Job::where('status', 'pending')
    ->lockForUpdate()
    ->skip(DB::raw('LOCKED'))  // MySQL 8.0+
    ->first();
```

23. **Chunk by ID for stable pagination**
```php
// BAD: chunk() with ORDER BY can miss/duplicate rows if data changes
Invoice::orderBy('id')->chunk(1000, fn($invoices) => ...);

// GOOD: chunkById() uses WHERE id > last_id
Invoice::chunkById(1000, fn($invoices) => ...);
```

24. **Use database assertions in tests**
```php
$this->assertDatabaseCount('invoices', 5);
$this->assertDatabaseHas('invoices', ['status' => 'paid']);
$this->assertDatabaseMissing('invoices', ['deleted_at' => null]);
```

25. **Monitor with DB::listen()**
```php
DB::listen(function ($query) {
    if ($query->time > 1000) { // > 1 second
        Log::warning('Slow query', [
            'sql' => $query->sql,
            'time' => $query->time,
            'bindings' => $query->bindings
        ]);
    }
});
```

---

## Cloud & Serverless MySQL Performance

Optimizing MySQL in cloud environments requires different strategies than on-premise.

### AWS RDS / Aurora Considerations

```
┌─────────────────────────────────────────────────────────────────┐
│                    AURORA ARCHITECTURE                           │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│     Writer Instance              Reader Instances                │
│     ┌───────────────┐           ┌───────────────┐               │
│     │   DB Engine   │           │   DB Engine   │               │
│     └───────┬───────┘           └───────┬───────┘               │
│             │                           │                        │
│             ▼                           ▼                        │
│     ┌─────────────────────────────────────────────────────┐     │
│     │              Shared Storage (6 copies)               │     │
│     │            across 3 AZs - auto-healing               │     │
│     └─────────────────────────────────────────────────────┘     │
│                                                                  │
│  Benefits:                                                       │
│  - Redo log only (no doublewrite needed)                        │
│  - 10ms reader lag (vs 1+ second in RDS MySQL)                  │
│  - Storage auto-grows to 128TB                                  │
│  - Fast cloning (copy-on-write)                                 │
│                                                                  │
│  Gotchas:                                                        │
│  - Different replication model                                   │
│  - Cannot restore to specific point with binlog position        │
│  - Higher cost at low utilization                               │
└─────────────────────────────────────────────────────────────────┘
```

**Cloud-specific optimizations:**

```php
// 1. Use read replicas for reporting
// config/database.php
'mysql' => [
    'read' => [
        'host' => [
            env('DB_HOST_READ_1'),
            env('DB_HOST_READ_2'),
        ],
    ],
    'write' => [
        'host' => env('DB_HOST_WRITE'),
    ],
    'sticky' => true,  // Use writer after write to avoid lag issues
],

// Laravel automatically routes SELECT to readers
$invoices = Invoice::where('status', 'paid')->get();  // → Reader

// Writes go to writer
$invoice = Invoice::create([...]);  // → Writer

// Force writer for consistency
$invoice = Invoice::onWriteConnection()
    ->where('id', $id)
    ->first();
```

```ini
# RDS Parameter Group optimizations
innodb_buffer_pool_size = {DBInstanceClassMemory*3/4}
innodb_log_file_size = 2147483648  # 2GB (fixed in RDS)
innodb_flush_log_at_trx_commit = 1  # Keep durability
max_connections = {DBInstanceClassMemory/12582880}

# Aurora-specific
aurora_parallel_query = ON  # For analytics queries
aurora_read_replica_read_committed = ON  # Reduce reader lag
```

### Serverless Database Strategies

```php
// Connection pooling is CRITICAL for serverless (Lambda, etc.)

// Use PlanetScale, TiDB Cloud, or Aurora Serverless v2
// They handle connection pooling at proxy level

// If using standard MySQL with Lambda:
// 1. Use RDS Proxy (AWS)
// 2. Keep connections warm
// 3. Configure reasonable pool sizes

// RDS Proxy configuration
'mysql' => [
    'host' => env('RDS_PROXY_ENDPOINT'),  // Not direct RDS endpoint
    'options' => [
        PDO::ATTR_PERSISTENT => false,  // RDS Proxy handles pooling
    ],
],

// Lambda considerations:
// - Cold starts open new connections
// - Max connections = (Lambda concurrency × connections per function)
// - Use connection reuse where possible
```

### Multi-Region & Global Databases

```sql
-- Aurora Global Database
-- Primary region: eu-west-1
-- Secondary regions: us-east-1, ap-southeast-1

-- Write-forwarding (Aurora)
SET aurora_replica_read_consistency = 'session';
-- Writes to replica are forwarded to primary automatically

-- For non-Aurora: Application-level sharding
-- Route by company_id hash to regional databases
```

```php
// Regional routing in Laravel
class DatabaseRouter
{
    public function getConnection($companyId)
    {
        $region = $this->getCompanyRegion($companyId);

        return match ($region) {
            'EU' => 'mysql_eu',
            'US' => 'mysql_us',
            'APAC' => 'mysql_apac',
        };
    }
}

// Use in model
class Invoice extends Model
{
    protected $connection;

    public function __construct()
    {
        $this->connection = app(DatabaseRouter::class)
            ->getConnection(auth()->user()->company_id);
    }
}
```

---

## Static Analysis & Pre-Runtime Error Detection

Catch SQL errors before they reach production.

### PHPStan/Larastan for SQL Analysis

```php
// Install
composer require --dev larastan/larastan

// phpstan.neon
parameters:
    level: 8
    paths:
        - app
    checkMissingIterableValueType: false

// This catches errors like:
// - Invalid column names in where()
// - Wrong parameter types in queries
// - Missing relationships in with()
```

### IDE Database Integration

```yaml
# .idea/dataSources.xml (PhpStorm)
# Connect to dev database for:
# - SQL syntax validation
# - Column name autocomplete
# - Query execution from IDE
# - Schema visualization
```

### Laravel Query Safety

```php
// 1. Use strict mode to catch errors early
// config/database.php
'mysql' => [
    'strict' => true,  // Enables STRICT_TRANS_TABLES, etc.
    'modes' => [
        'STRICT_TRANS_TABLES',
        'NO_ZERO_IN_DATE',
        'NO_ZERO_DATE',
        'ERROR_FOR_DIVISION_BY_ZERO',
        'NO_ENGINE_SUBSTITUTION',
    ],
],

// 2. Validate foreign keys in tests
public function test_database_constraints()
{
    $this->expectException(QueryException::class);

    // Should fail: client_id doesn't exist
    Invoice::create([
        'client_id' => 999999,
        'company_id' => 1,
    ]);
}

// 3. Use model factories that respect constraints
class InvoiceFactory extends Factory
{
    public function definition()
    {
        return [
            'client_id' => Client::factory(),  // Creates valid client
            'company_id' => Company::factory(),
        ];
    }
}
```

### Migration Validation

```bash
# Check migrations without running
php artisan migrate --pretend

# Validate against actual schema
composer require --dev doctrine/dbal
php artisan schema:dump

# In CI pipeline:
php artisan migrate:fresh --database=testing
php artisan test --filter=DatabaseSchemaTest
```

```php
// Test migration integrity
class DatabaseSchemaTest extends TestCase
{
    public function test_all_foreign_keys_reference_existing_tables()
    {
        $foreignKeys = DB::select("
            SELECT
                TABLE_NAME, COLUMN_NAME,
                REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE REFERENCED_TABLE_SCHEMA = ?
        ", [config('database.connections.mysql.database')]);

        foreach ($foreignKeys as $fk) {
            $this->assertTrue(
                Schema::hasTable($fk->REFERENCED_TABLE_NAME),
                "FK in {$fk->TABLE_NAME} references non-existent table"
            );
        }
    }

    public function test_all_indexes_exist()
    {
        $slowQueries = [
            'SELECT * FROM invoices WHERE company_id = 1',
            'SELECT * FROM invoices WHERE status = "paid"',
            // Add your common queries
        ];

        foreach ($slowQueries as $query) {
            $explain = DB::select("EXPLAIN {$query}");
            $this->assertNotEquals(
                'ALL',
                $explain[0]->type,
                "Full table scan detected: {$query}"
            );
        }
    }
}
```

---

## When NOT to Use MySQL

Knowing MySQL's limitations helps choose the right tool.

### Use PostgreSQL Instead When:

```
┌─────────────────────────────────────────────────────────────────┐
│  PostgreSQL Advantages                                          │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ✓ Complex JSON operations with JSONB                           │
│    - GIN indexes on JSON paths                                  │
│    - jsonb_path_query() for SQL/JSON path                      │
│                                                                  │
│  ✓ Advanced data types                                          │
│    - Arrays, Range types, Network types (INET, CIDR)           │
│    - Geometric types with PostGIS                               │
│    - Full-text search with ranking                              │
│                                                                  │
│  ✓ Better analytical queries                                    │
│    - FILTER clause in aggregates                                │
│    - GROUPING SETS, CUBE, ROLLUP                               │
│    - Better window function support                             │
│                                                                  │
│  ✓ True serializable isolation                                  │
│    - MySQL's "serializable" is really "snapshot"               │
│                                                                  │
│  ✓ Extensions (TimescaleDB, pg_cron, etc.)                     │
└─────────────────────────────────────────────────────────────────┘
```

### Use a Different Data Store When:

```php
// Time-series data → InfluxDB, TimescaleDB
// - Millions of datapoints per second
// - Automatic downsampling
// - Efficient time-range queries

// Full-text search → Elasticsearch, Meilisearch
// - Fuzzy matching, synonyms, stemming
// - Relevance scoring
// - Faceted search

// Graph relationships → Neo4j
// - "Friends of friends" queries
// - Recommendation engines
// - Fraud detection patterns

// Cache / Session → Redis
// - Sub-millisecond reads
// - Pub/Sub messaging
// - Leaderboards, rate limiting

// Document store → MongoDB
// - Highly variable schemas
// - Rapid prototyping
// - Geospatial queries (though PostgreSQL is often better)

// Analytics / Warehouse → ClickHouse, BigQuery
// - Billions of rows
// - Columnar storage
// - Aggregations over huge datasets
```

### MySQL Anti-Patterns to Avoid:

```php
// ❌ Using MySQL as a queue
// BAD: Polling for jobs
while (true) {
    $job = DB::table('jobs')->where('status', 'pending')->first();
    if ($job) process($job);
    sleep(1);
}
// GOOD: Use Redis queues (Laravel's default)

// ❌ Storing binary files in BLOB
// BAD: Files in database
$table->binary('file_content');  // Bloats database, slow backups
// GOOD: Use S3/filesystem, store path in DB

// ❌ Using MySQL for real-time counters
// BAD: UPDATE users SET page_views = page_views + 1
// Every page view = write lock
// GOOD: Redis INCR, batch update to MySQL hourly

// ❌ Recursive CTEs for deep hierarchies
// MySQL 8.0 supports CTEs but performance degrades >10 levels
// GOOD: Use nested sets, materialized paths, or closure tables

// ❌ Storing large JSON documents
// If documents > 1MB or deeply nested
// GOOD: Use MongoDB or PostgreSQL JSONB
```

---

## Advanced Debugging Strategies

Systematic approaches to diagnosing MySQL issues.

### Query Debugging Workflow

```
┌─────────────────────────────────────────────────────────────────┐
│                    DEBUG WORKFLOW                                │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  1. IDENTIFY ───► 2. ANALYZE ───► 3. OPTIMIZE ───► 4. VERIFY   │
│                                                                  │
│  ┌────────────┐  ┌────────────┐  ┌────────────┐  ┌────────────┐│
│  │ Slow log   │  │ EXPLAIN    │  │ Add index  │  │ Compare    ││
│  │ Telescope  │  │ SHOW       │  │ Rewrite    │  │ before/    ││
│  │ APM tools  │  │ WARNINGS   │  │ query      │  │ after      ││
│  └────────────┘  └────────────┘  └────────────┘  └────────────┘│
└─────────────────────────────────────────────────────────────────┘
```

### Diagnosing Lock Issues

```sql
-- Step 1: Find blocked queries
SELECT
    r.trx_id AS waiting_trx_id,
    r.trx_mysql_thread_id AS waiting_thread,
    r.trx_query AS waiting_query,
    b.trx_id AS blocking_trx_id,
    b.trx_mysql_thread_id AS blocking_thread,
    b.trx_query AS blocking_query
FROM information_schema.innodb_lock_waits w
JOIN information_schema.innodb_trx b ON b.trx_id = w.blocking_trx_id
JOIN information_schema.innodb_trx r ON r.trx_id = w.requesting_trx_id;

-- Step 2: See what the blocker is doing
SELECT * FROM performance_schema.events_statements_current
WHERE thread_id = <blocking_thread_id>;

-- Step 3: Kill if necessary (last resort)
KILL <blocking_thread_id>;
```

### Diagnosing Performance Regressions

```php
// 1. Enable query fingerprinting in Telescope
// config/telescope.php
'watchers' => [
    Watchers\QueryWatcher::class => [
        'enabled' => env('TELESCOPE_QUERY_WATCHER', true),
        'slow' => 100,  // Log queries > 100ms
    ],
],

// 2. Compare execution plans before/after
// Store EXPLAIN output and compare:
$before = DB::select('EXPLAIN FORMAT=JSON SELECT ...');
// Deploy change
$after = DB::select('EXPLAIN FORMAT=JSON SELECT ...');

// 3. Use MySQL's optimizer trace
SET optimizer_trace = 'enabled=on';
SELECT ...;
SELECT * FROM information_schema.optimizer_trace;
-- Shows WHY optimizer chose certain plan

// 4. Check for implicit type conversions
EXPLAIN SELECT * FROM invoices WHERE invoice_number = 123;
-- If invoice_number is VARCHAR, this causes full scan!
-- Fix: WHERE invoice_number = '123'
```

### Memory Issues

```sql
-- Check memory usage per connection
SELECT
    thread_id,
    event_name,
    current_alloc
FROM performance_schema.memory_summary_by_thread_by_event_name
WHERE event_name LIKE 'memory/sql%'
ORDER BY current_alloc DESC LIMIT 20;

-- Check total memory usage
SELECT
    event_name,
    SUM(current_alloc) as total_allocated
FROM performance_schema.memory_summary_global_by_event_name
GROUP BY event_name
ORDER BY total_allocated DESC LIMIT 20;

-- Common culprits:
-- - sort_buffer_size per connection
-- - join_buffer_size per join
-- - tmp_table_size for temp tables
-- - Large result sets being materialized
```

### Connection Pool Exhaustion

```php
// Symptoms: "Too many connections" errors

// 1. Check current connections
// SHOW PROCESSLIST;
// or
SELECT COUNT(*) FROM information_schema.PROCESSLIST;

// 2. Find connection leaks in Laravel
// Add to AppServiceProvider::boot()
DB::listen(function ($query) {
    if (str_contains($query->sql, 'SLEEP')) {
        Log::warning('Potential connection leak', [
            'query' => $query->sql,
            'trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)
        ]);
    }
});

// 3. Ensure connections are closed
// BAD: Long-running process holding connection
while (true) {
    $data = DB::table('items')->get();
    process($data);
    sleep(60);  // Connection held for 60 seconds
}

// GOOD: Reconnect or use disconnect()
while (true) {
    DB::reconnect();
    $data = DB::table('items')->get();
    process($data);
    DB::disconnect();
    sleep(60);
}
```

---

## Relationship: PHP, Laravel, MySQL, and the Full Stack

Understanding how the components interact improves debugging and design.

```
┌─────────────────────────────────────────────────────────────────┐
│                    FULL STACK ARCHITECTURE                       │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  Browser                                                        │
│    │ HTTP Request                                               │
│    ▼                                                            │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │                    NGINX / Apache                        │   │
│  └─────────────────────────────────────────────────────────┘   │
│    │ FastCGI / php-fpm                                         │
│    ▼                                                            │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │                    PHP-FPM Pool                          │   │
│  │  ┌─────────────────────────────────────────────────────┐│   │
│  │  │              Laravel Application                     ││   │
│  │  │                                                      ││   │
│  │  │  Route → Controller → Model → Eloquent → PDO       ││   │
│  │  │                          ↓                          ││   │
│  │  │                   Query Builder                     ││   │
│  │  │                          ↓                          ││   │
│  │  │                  Prepared Statement                 ││   │
│  │  └─────────────────────────────────────────────────────┘│   │
│  └─────────────────────────────────────────────────────────┘   │
│    │ MySQL Protocol (TCP/Unix Socket)                          │
│    ▼                                                            │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │                    MySQL Server                          │   │
│  │  Parser → Optimizer → Executor → Storage Engine         │   │
│  │                                      │                   │   │
│  │                                  InnoDB                  │   │
│  │                                      │                   │   │
│  │                           Buffer Pool + Disk            │   │
│  └─────────────────────────────────────────────────────────┘   │
│                                                                  │
│  Cache Layer (Redis):                                           │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │  Sessions │ Cache │ Queues │ Rate Limiting             │   │
│  └─────────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────────┘
```

### Data Type Mapping

| MySQL Type | PHP Type | Laravel Cast | Notes |
|------------|----------|--------------|-------|
| TINYINT(1) | int | boolean | `$casts = ['active' => 'boolean']` |
| INT | int | integer | Automatic |
| BIGINT | string* | integer | *PHP can overflow on 32-bit |
| DECIMAL | string | decimal:2 | Use string to preserve precision |
| DATETIME | string | datetime | Carbon instance |
| JSON | string | array/object | Automatic encode/decode |
| ENUM | string | string | Consider lookup tables instead |

### Query Flow Deep Dive

```php
// What happens when you call:
$invoice = Invoice::where('company_id', 1)->first();

// 1. Eloquent Model Layer
//    - Apply global scopes (CompanyScope)
//    - Build query with eager loads

// 2. Query Builder
//    - Generates SQL: SELECT * FROM invoices WHERE company_id = ? LIMIT 1

// 3. PDO Layer
//    - Creates prepared statement
//    - Binds parameter (1) with PDO::PARAM_INT
//    - Executes statement

// 4. MySQL Receives
//    - Parses SQL (or uses query cache if prepared)
//    - Optimizer chooses execution plan
//    - Executes using InnoDB

// 5. InnoDB Execution
//    - Checks buffer pool for company_id index page
//    - If not cached, reads from disk
//    - Follows B+tree to find matching rows
//    - Returns primary keys
//    - Fetches actual rows from clustered index
//    - Applies MVCC visibility check

// 6. Response Flow
//    - MySQL → PDO result set
//    - PDO → PHP array
//    - Eloquent → Model hydration (creates Invoice object)
//    - Casts applied (dates → Carbon, JSON → array, etc.)
```

---

**Version 3.0.0** - Enhanced with MySQL internals deep dive, 25 lesser-known facts, 25 advanced tips, cloud/serverless optimization, static analysis, debugging strategies, and full-stack integration guidance.

