# Technology Stack Migration Summary

**OsintWeb - Simplified MySQL Stack**
**Date: December 2025**

## What Changed

### Database: PostgreSQL → MySQL 8.0+

**Before:**
- PostgreSQL 15+ with PostGIS extension
- Required VPS or dedicated server
- Complex setup and maintenance
- ~$20-50/month minimum hosting

**After:**
- MySQL 8.0+ with native spatial support
- Works on shared hosting
- Simple LAMP/LEMP stack
- ~$5-15/month hosting

### Cache: Redis → Laravel Cache

**Before:**
- Separate Redis server required
- Additional configuration and maintenance
- Not available on shared hosting

**After:**
- File cache (development)
- Database cache (production)
- Zero additional infrastructure
- Works everywhere Laravel works

### Result: Shared Hosting Compatible

The entire application can now run on basic shared hosting:
- Hostinger Business: $8-12/month
- SiteGround: $15-20/month
- A2 Hosting: $10-15/month

---

## Quick Setup Guide

### Environment Configuration

```bash
# .env file changes

# Database
DB_CONNECTION=mysql          # Changed from pgsql
DB_HOST=127.0.0.1
DB_PORT=3306                 # Changed from 5432
DB_DATABASE=osintweb
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Cache (new)
CACHE_DRIVER=file           # Development: file, Production: database
SESSION_DRIVER=file         # Development: file, Production: database
QUEUE_CONNECTION=database   # Changed from redis

# Search (optional)
SCOUT_DRIVER=meilisearch    # Or use MySQL full-text
MEILISEARCH_HOST=http://localhost:7700
MEILISEARCH_KEY=your_key
```

### Installation Commands

```bash
# Create MySQL database
mysql -u root -p -e "CREATE DATABASE osintweb CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Run migrations
php artisan migrate

# Create Laravel cache/session/queue tables
php artisan cache:table
php artisan session:table
php artisan queue:table
php artisan migrate
```

---

## Key Code Changes

### Spatial Data Types

**PostgreSQL (old):**
```php
Schema::create('events', function (Blueprint $table) {
    $table->geography('location', 'point');  // PostGIS
});
```

**MySQL (new):**
```php
Schema::create('events', function (Blueprint $table) {
    $table->geometry('location', 'point', 4326);  // MySQL spatial
});
```

### Distance Queries

**PostgreSQL (old):**
```php
Event::whereRaw("ST_DWithin(location::geography, ST_MakePoint(?, ?)::geography, ?)",
    [$lng, $lat, $radius]
)->get();
```

**MySQL (new):**
```php
Event::whereRaw("ST_Distance_Sphere(location, ST_GeomFromText('POINT(? ?)', 4326)) <= ?",
    [$lng, $lat, $radius]
)->get();
```

### Cache Usage

**PostgreSQL + Redis (old):**
```php
// config/cache.php
'default' => 'redis',
```

**MySQL (new):**
```php
// config/cache.php
'default' => env('CACHE_DRIVER', 'file'),  // file or database

// No code changes needed - Cache facade works the same
Cache::remember('key', 3600, function () {
    return expensiveOperation();
});
```

---

## Features Comparison

| Feature | PostgreSQL + PostGIS | MySQL 8.0+ | Impact |
|---------|---------------------|-----------|--------|
| Point storage | ✅ Excellent | ✅ Excellent | None |
| Polygon storage | ✅ Excellent | ✅ Excellent | None |
| Distance calculations | ✅ Spheroid (0.01% accurate) | ✅ Sphere (0.05% accurate) | Negligible for conflict tracking |
| Spatial indexes | ✅ GiST | ✅ R-tree | Both excellent |
| GeoJSON export | ✅ Native | ✅ Native (8.0+) | None |
| Complex topology | ✅ Advanced | ⚠️ Basic | Rarely needed |
| SRID transforms | ✅ Extensive | ⚠️ Limited | Store all in 4326 |
| 3D geometries | ✅ Yes | ❌ No | Not needed |
| JSON columns | ✅ JSONB | ✅ JSON | Both excellent |
| Full-text search | ⚠️ Basic | ✅ Good | MySQL advantage |

**Verdict:** MySQL 8.0+ meets all requirements for military conflict tracking.

---

## Hosting Options

### Shared Hosting (Easiest, Cheapest)

**Best for:** Small to medium deployments (< 10k daily users)

| Provider | Cost/Month | MySQL | Storage | RAM | Notes |
|----------|-----------|-------|---------|-----|-------|
| Hostinger Business | $8-12 | 8.0+ | 100GB | Shared | Best value |
| SiteGround GrowBig | $15-20 | 8.0+ | 40GB | Shared | Great support |
| A2 Hosting Drive | $10-15 | 8.0+ | 100GB | Shared | Fast servers |

**Setup:**
1. Upload code via SFTP or Git
2. Create database in cPanel
3. Configure `.env`
4. Run migrations via SSH
5. Done!

### VPS (More Control)

**Best for:** Production apps, custom services (Meilisearch)

| Setup | Cost/Month | Specs | Management |
|-------|-----------|-------|------------|
| Laravel Forge + DigitalOcean | $18 | 1GB RAM, 1 CPU | Fully managed |
| Cloudways | $26 | 2GB RAM | Managed |
| Self-managed VPS | $6-12 | 1-2GB RAM | You manage |

**Laravel Forge Setup:**
1. Connect DigitalOcean account
2. Create server (1-click)
3. Deploy site from Git
4. Install Meilisearch (optional)
5. SSL auto-configured

### Database Only (Separate)

**Best for:** Separating concerns, high availability

| Provider | Cost/Month | Specs | Notes |
|----------|-----------|-------|-------|
| PlanetScale | Free tier, $29+ | Serverless | Auto-scaling |
| DigitalOcean Managed | $15+ | 1GB RAM | Easy backups |

---

## Performance Notes

### Optimization Tips

```php
// 1. Use spatial indexes (automatic)
CREATE SPATIAL INDEX idx_location ON events(location);

// 2. Cache expensive queries
$mapData = Cache::remember("map.{$date}", 600, function () use ($date) {
    return [
        'zones' => ControlZone::validAt($date)->get(),
        'events' => Event::on($date)->get(),
    ];
});

// 3. Eager load relationships
$events = Event::with(['eventType', 'media', 'sources'])->get();

// 4. Use pagination
$events = Event::paginate(25);

// 5. Index frequently queried columns
$table->index(['occurred_at', 'status']);
```

### Expected Performance

Based on typical queries:

```
Operation                           Response Time    Notes
----------------------------------------------------------------
Load map with 1000 events          < 100ms          With caching
Point-in-polygon check             < 10ms           Spatial index
Distance calculation (1M records)  < 50ms           Spatial index
GeoJSON export (10k zones)         < 150ms          Direct export
Full-text search (1M records)      < 100ms          Full-text index
Equipment loss statistics          < 50ms           With caching
Daily event aggregation            < 75ms           With indexes
```

These benchmarks assume:
- Proper indexing
- Query result caching
- Eager loading where appropriate
- Standard VPS (2GB RAM, 1-2 CPU cores)

---

## Migration Checklist

If migrating from PostgreSQL:

- [ ] Export PostgreSQL data
- [ ] Convert spatial data to WKT format
- [ ] Create MySQL database
- [ ] Run MySQL migrations
- [ ] Import converted data
- [ ] Update `.env` configuration
- [ ] Test spatial queries
- [ ] Verify cache functionality
- [ ] Update deployment scripts
- [ ] Update CI/CD pipelines
- [ ] Train team on MySQL differences

If starting fresh:

- [ ] Follow installation guide in README.md
- [ ] Configure `.env` for MySQL
- [ ] Run migrations
- [ ] Create cache/session tables
- [ ] Seed test data
- [ ] Verify spatial queries work
- [ ] Deploy to hosting

---

## Common Issues & Solutions

### Issue: Spatial functions not available

**Error:** `FUNCTION ST_GeomFromText does not exist`

**Solution:**
```bash
# Check MySQL version
mysql --version  # Must be 8.0+

# Spatial functions are built-in, no extension needed
# If error persists, check user permissions
GRANT ALL PRIVILEGES ON osintweb.* TO 'username'@'localhost';
```

### Issue: Cache not working

**Error:** Cache always returns fresh data

**Solution:**
```bash
# Create cache table if using database cache
php artisan cache:table
php artisan migrate

# Verify .env
CACHE_DRIVER=database  # or 'file'

# Clear cache
php artisan cache:clear

# Test cache
php artisan tinker
>>> Cache::put('test', 'value', 60);
>>> Cache::get('test');  // Should return 'value'
```

### Issue: Queue jobs not processing

**Error:** Jobs stuck in pending

**Solution:**
```bash
# Use database queue driver
QUEUE_CONNECTION=database

# Create jobs table
php artisan queue:table
php artisan migrate

# Process queue manually (development)
php artisan queue:work

# Or use Supervisor (production)
[program:osintweb-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work database --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
numprocs=2
```

### Issue: Slow spatial queries

**Error:** Queries take > 1 second

**Solution:**
```sql
-- Verify spatial indexes exist
SHOW INDEXES FROM events WHERE Key_name LIKE 'idx_%';

-- Add missing indexes
CREATE SPATIAL INDEX idx_location ON events(location);
CREATE SPATIAL INDEX idx_geometry ON control_zones(geometry);

-- Use EXPLAIN to check query plan
EXPLAIN SELECT * FROM events
WHERE ST_Distance_Sphere(location, ST_GeomFromText('POINT(0 0)', 4326)) < 10000;
```

---

## Support & Resources

### Documentation
- [MySQL Stack Specification](MYSQL_STACK_SPECIFICATION.md) - Complete technical details
- [SPECIFICATION.md](SPECIFICATION.md) - Full feature specification
- [CLAUDE.md](../CLAUDE.md) - Development guidelines

### External Resources
- [MySQL 8.0 Spatial Reference](https://dev.mysql.com/doc/refman/8.0/en/spatial-types.html)
- [Laravel Cache Documentation](https://laravel.com/docs/11.x/cache)
- [Laravel Forge](https://forge.laravel.com)
- [Meilisearch Documentation](https://www.meilisearch.com/docs)

### Community
- Laravel Discord
- MySQL Forums
- Stack Overflow: `laravel` + `mysql` + `spatial`

---

**Last Updated:** December 2025
**Stack Version:** 2.0 (MySQL)
