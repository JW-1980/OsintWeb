---
name: backup-recovery
description: Backup verification and disaster recovery procedures
version: 1.0.1
tags: [backup, disaster-recovery, data-integrity, restoration, data-protection]
trigger_keywords: [sk-backup-recovery, "backup strategy", "disaster recovery", "restore database", "data corruption", "backup verification", "recovery plan", "data loss prevention", "backup restore", "database backup", "file backup"]
related_skills: [document-keeping-expert, deployment-checklist, database-mysql-expert]
---
# Backup & Disaster Recovery

This skill ensures proper backup procedures and provides recovery strategies for the bookkeeping application.

## When to Use

- Setting up new environments
- Before major deployments
- After data corruption
- During disaster recovery
- For compliance audits
- Testing restoration procedures

## Backup Strategy

### Required Backups

1. **Database** - All financial data (CRITICAL)
2. **File Storage** - Invoices, receipts, documents
3. **Application Code** - Git repository
4. **Configuration** - Environment files, secrets
5. **Logs** - Audit trails (7 years for Dutch compliance)

### Retention Policy

```
Daily backups:    7 days
Weekly backups:   4 weeks
Monthly backups:  12 months
Annual backups:   7 years (legal requirement)
```

## Database Backup

### 1. Laravel Backup Package

```bash
# Install
composer require spatie/laravel-backup

# Publish config
php artisan vendor:publish --provider="Spatie\Backup\BackupServiceProvider"
```

### Configuration

Edit `config/backup.php`:

```php
return [
    'backup' => [
        'name' => env('APP_NAME', 'boekhouder'),

        'source' => [
            'files' => [
                'include' => [
                    base_path(),
                ],
                'exclude' => [
                    base_path('vendor'),
                    base_path('node_modules'),
                    base_path('storage/app/backups'),
                ],
            ],

            'databases' => [
                'mysql',
            ],
        ],

        'destination' => [
            'disks' => [
                's3',  // AWS S3
                'backup-disk',  // Local/NAS
            ],
        ],
    ],

    'notifications' => [
        'mail' => [
            'to' => env('BACKUP_NOTIFICATION_EMAIL', 'admin@boekhouder.nl'),
        ],

        'slack' => [
            'webhook_url' => env('BACKUP_SLACK_WEBHOOK'),
        ],
    ],

    'monitor_backups' => [
        [
            'name' => env('APP_NAME', 'boekhouder'),
            'disks' => ['s3', 'backup-disk'],
            'newestBackupsShouldNotBeOlderThanDays' => 1,
            'storageUsedMayNotBeHigherThanMegabytes' => 10000,
        ],
    ],

    'cleanup' => [
        'strategy' => \Spatie\Backup\Tasks\Cleanup\Strategies\DefaultStrategy::class,
        'defaultStrategy' => [
            'keepAllBackupsForDays' => 7,
            'keepDailyBackupsForDays' => 16,
            'keepWeeklyBackupsForWeeks' => 8,
            'keepMonthlyBackupsForMonths' => 12,
            'keepYearlyBackupsForYears' => 7,
            'deleteOldestBackupsWhenUsingMoreMegabytesThan' => 10000,
        ],
    ],
];
```

### Backup Commands

```bash
# Create backup
php artisan backup:run

# Only database
php artisan backup:run --only-db

# Only files
php artisan backup:run --only-files

# List backups
php artisan backup:list

# Check backup health
php artisan backup:monitor

# Clean old backups
php artisan backup:clean
```

### Automated Backups

Add to `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    // Daily database backup at 2 AM
    $schedule->command('backup:run --only-db')
        ->dailyAt('02:00')
        ->emailOutputOnFailure('admin@boekhouder.nl');

    // Weekly full backup on Sunday at 3 AM
    $schedule->command('backup:run')
        ->weeklyOn(0, '03:00');

    // Daily cleanup
    $schedule->command('backup:clean')
        ->dailyAt('04:00');

    // Monitor backups
    $schedule->command('backup:monitor')
        ->dailyAt('05:00');
}
```

## File Storage Backup

### S3 Configuration

```bash
# .env
AWS_ACCESS_KEY_ID=your-key
AWS_SECRET_ACCESS_KEY=your-secret
AWS_DEFAULT_REGION=eu-west-1
AWS_BUCKET=boekhouder-backups
AWS_USE_PATH_STYLE_ENDPOINT=false

BACKUP_DISK=s3
```

### Configure Filesystem

Edit `config/filesystems.php`:

```php
'disks' => [
    's3-backup' => [
        'driver' => 's3',
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION'),
        'bucket' => env('AWS_BACKUP_BUCKET', 'boekhouder-backups'),
        'url' => env('AWS_URL'),
        'endpoint' => env('AWS_ENDPOINT'),
    ],

    'backup' => [
        'driver' => 'local',
        'root' => storage_path('app/backups'),
    ],
],
```

### Backup Documents & Invoices

```bash
# Sync to S3
aws s3 sync storage/app/documents s3://boekhouder-documents/documents/ \
    --storage-class STANDARD_IA \
    --delete

# Backup invoices (PDFs)
aws s3 sync storage/app/invoices s3://boekhouder-documents/invoices/ \
    --storage-class STANDARD_IA

# Backup receipts
aws s3 sync storage/app/receipts s3://boekhouder-documents/receipts/ \
    --storage-class STANDARD_IA
```

## Manual Database Backup

### MySQL Dump

```bash
# Full backup
mysqldump -u username -p \
    --databases boekhouder \
    --single-transaction \
    --routines \
    --triggers \
    --events \
    --result-file=backup-$(date +%Y%m%d-%H%M%S).sql

# Compressed backup
mysqldump -u username -p \
    --databases boekhouder \
    --single-transaction | \
    gzip > backup-$(date +%Y%m%d-%H%M%S).sql.gz

# Backup specific tables
mysqldump -u username -p boekhouder \
    invoices clients payments \
    > critical-tables-$(date +%Y%m%d).sql
```

### Encrypted Backup

```bash
# Encrypt with GPG
mysqldump -u username -p boekhouder | \
    gzip | \
    gpg --encrypt --recipient admin@boekhouder.nl \
    > backup-$(date +%Y%m%d).sql.gz.gpg

# Decrypt and restore
gpg --decrypt backup-20250115.sql.gz.gpg | \
    gunzip | \
    mysql -u username -p boekhouder
```

## Restoration Procedures

### 1. Database Restoration

```bash
# Restore latest backup
php artisan backup:restore --latest

# Restore specific backup
php artisan backup:restore backup-20250115-020000.zip

# Manual MySQL restoration
mysql -u username -p boekhouder < backup-20250115.sql

# Restore from compressed backup
gunzip < backup-20250115.sql.gz | mysql -u username -p boekhouder
```

### 2. File Restoration

```bash
# Restore from S3
aws s3 sync s3://boekhouder-backups/latest/ storage/app/ --delete

# Restore specific directory
aws s3 sync s3://boekhouder-documents/invoices/ storage/app/invoices/

# Restore specific file
aws s3 cp s3://boekhouder-documents/invoices/INV-2025-001.pdf \
    storage/app/invoices/
```

### 3. Application Restoration

```bash
# 1. Clone repository
git clone https://github.com/your-org/boekhouder.git
cd boekhouder

# 2. Checkout specific version
git checkout v1.2.3

# 3. Install dependencies
composer install --no-dev --optimize-autoloader
npm install && npm run build

# 4. Configure environment
cp .env.example .env
php artisan key:generate

# 5. Restore database
mysql -u username -p boekhouder < backup-latest.sql

# 6. Restore files
aws s3 sync s3://boekhouder-backups/storage/ storage/app/

# 7. Set permissions
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# 8. Run migrations (if needed)
php artisan migrate --force

# 9. Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 10. Restart services
sudo systemctl restart php8.2-fpm nginx
```

## Disaster Recovery Scenarios

### Scenario 1: Database Corruption

```bash
# 1. Enable maintenance mode
php artisan down

# 2. Backup current (corrupted) database
mysqldump -u username -p boekhouder > corrupted-$(date +%Y%m%d-%H%M%S).sql

# 3. Restore from last good backup
mysql -u username -p boekhouder < backup-latest-good.sql

# 4. Verify data integrity
php artisan tinker
>>> Invoice::count()
>>> Client::count()
>>> User::count()

# 5. Test critical functions
php artisan test --filter=CriticalPathsTest

# 6. Disable maintenance mode
php artisan up

# 7. Notify users
# Send email about recovery
```

### Scenario 2: Complete Server Failure

```bash
# 1. Provision new server
# - Install PHP 8.2, MySQL 8.0, Nginx
# - Configure firewalls, SSH access

# 2. Clone application
git clone https://github.com/your-org/boekhouder.git
cd boekhouder

# 3. Install dependencies
composer install --no-dev --optimize-autoloader

# 4. Restore database from S3
aws s3 cp s3://boekhouder-backups/latest-db.sql.gz .
gunzip latest-db.sql.gz
mysql -u username -p boekhouder < latest-db.sql

# 5. Restore files from S3
aws s3 sync s3://boekhouder-backups/storage/ storage/app/

# 6. Configure environment
# Copy secrets from secure vault
# Update .env with new server details

# 7. Update DNS
# Point domain to new server

# 8. Test and verify
php artisan test
curl -f https://boekhouder.nl/health

# 9. Monitor
tail -f storage/logs/laravel.log
```

### Scenario 3: Accidental Data Deletion

```bash
# 1. Identify what was deleted
mysql -u username -p boekhouder
> SELECT * FROM invoices WHERE deleted_at IS NOT NULL ORDER BY deleted_at DESC LIMIT 10;

# 2. Restore specific table from backup
# Extract just that table
mysql -u username -p boekhouder_temp < backup-latest.sql

# 3. Copy back deleted records
INSERT INTO boekhouder.invoices
SELECT * FROM boekhouder_temp.invoices
WHERE id IN (123, 456, 789);

# 4. Verify restoration
SELECT * FROM invoices WHERE id IN (123, 456, 789);

# 5. Drop temp database
DROP DATABASE boekhouder_temp;
```

## Data Integrity Verification

### 1. Database Integrity Checks

```bash
# MySQL table check
php artisan tinker
>>> DB::statement('CHECK TABLE invoices');
>>> DB::statement('CHECK TABLE clients');

# Check for orphaned records
>>> Invoice::whereDoesntHave('company')->count()  // Should be 0
>>> InvoiceItem::whereDoesntHave('invoice')->count()  // Should be 0
```

### 2. File Integrity Verification

```bash
# Check for missing invoice PDFs
php artisan tinker
>>> $invoices = Invoice::where('pdf_path', '!=', null)->get();
>>> $missing = $invoices->filter(fn($inv) => !Storage::exists($inv->pdf_path));
>>> echo "Missing PDFs: " . $missing->count();

# Verify checksums
find storage/app/invoices -type f -exec sha256sum {} \; > checksums.txt
sha256sum -c checksums.txt
```

### 3. Backup Testing

```bash
# Test restoration monthly
php artisan backup:restore --latest --test

# Restore to staging environment
# 1. Create staging database
CREATE DATABASE boekhouder_staging;

# 2. Restore backup
mysql -u username -p boekhouder_staging < backup-latest.sql

# 3. Run tests on staging
php artisan test --env=staging

# 4. Verify data
php artisan tinker --env=staging
>>> Company::count()
>>> Invoice::count()
```

## Monitoring & Alerts

### Backup Monitoring

```php
// In app/Console/Commands/CheckBackups.php
public function handle()
{
    $lastBackup = Backup::latest()->first();

    if (!$lastBackup || $lastBackup->created_at->diffInHours() > 25) {
        // Alert: Backup is too old
        Notification::route('mail', 'admin@boekhouder.nl')
            ->notify(new BackupFailedNotification());

        // Alert: Slack/Teams
        Http::post(env('SLACK_WEBHOOK'), [
            'text' => '🚨 Backup is more than 24 hours old!',
        ]);

        $this->error('Backup is too old!');
        return 1;
    }

    $this->info('Backup is healthy');
    return 0;
}
```

### Scheduled Checks

```php
// In app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    // Check backup health every 6 hours
    $schedule->command('backup:check')
        ->everySixHours()
        ->emailOutputOnFailure('admin@boekhouder.nl');

    // Test restoration weekly
    $schedule->command('backup:test-restore')
        ->weekly()
        ->sundays()
        ->at('06:00');
}
```

## Compliance Requirements (Dutch Law)

### Data Retention

```php
// 7-year retention for financial records
// Implement in model or observer

class InvoiceObserver
{
    public function deleting(Invoice $invoice)
    {
        if ($invoice->invoice_date > now()->subYears(7)) {
            throw new Exception('Cannot delete invoice less than 7 years old (Dutch law)');
        }
    }
}
```

### Audit Trail

```bash
# Ensure audit logs are backed up
php artisan backup:run --only-db --tables=audit_logs,activity_log
```

## Backup Checklist

### Daily
- [ ] Database backup completed
- [ ] Backup uploaded to S3
- [ ] Backup notification received
- [ ] Backup size within expected range

### Weekly
- [ ] Full backup (database + files) completed
- [ ] Old backups cleaned up
- [ ] Storage usage monitored

### Monthly
- [ ] Test restoration procedure
- [ ] Verify backup integrity
- [ ] Check file checksums
- [ ] Review retention policy
- [ ] Update documentation

### Quarterly
- [ ] Full disaster recovery test
- [ ] Review and update recovery procedures
- [ ] Verify off-site backups
- [ ] Test alternative restoration methods

## Quick Commands

```bash
# Backup now
php artisan backup:run

# List all backups
php artisan backup:list

# Restore latest
php artisan backup:restore --latest

# Check backup health
php artisan backup:monitor

# Test restoration (dry run)
php artisan backup:test-restore
```

---

**Remember**: Test your backups regularly. A backup that can't be restored is useless!
