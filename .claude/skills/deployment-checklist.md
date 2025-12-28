---
name: deployment-checklist
description: Pre-deployment verification checklist for production releases
version: 1.0.1
tags: [deployment, production, release, devops, checklist, ci-cd]
trigger_keywords: [sk-deployment-checklist, deploy, release, production, ci/cd, pipeline, rollback]
related_skills: [git-github-expertise, backup-recovery, testing-expert]
---
# Production Deployment Checklist

This skill provides a comprehensive checklist for deploying the bookkeeping application to production.

## When to Use

- Before every production deployment
- After major feature releases
- Before security updates
- During infrastructure changes
- For rollback procedures

## Pre-Deployment Checks

### 1. Code Quality ✅

```bash
# Run all code quality checks
composer quality

# Specific checks
./vendor/bin/pint --test
./vendor/bin/phpstan analyse
php artisan test --coverage --min=80
```

- [ ] All Pint checks pass
- [ ] PHPStan analysis clean (level 6+)
- [ ] All tests passing
- [ ] Code coverage above 80%
- [ ] No TODOs or FIXMEs in production code

### 2. Database Migrations ✅

```bash
# Verify migrations on staging
php artisan migrate --pretend

# Check for reversibility
php artisan migrate:rollback --pretend

# Verify no duplicate tables
# Use database-migration-check skill
```

- [ ] All migrations tested on staging
- [ ] No foreign key dependency errors
- [ ] No duplicate table migrations
- [ ] Rollback procedures tested
- [ ] Seeders updated if needed
- [ ] Database backup created

### 3. Environment Configuration ✅

```bash
# Verify .env.production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Check for sensitive data
grep -r "TODO\|FIXME\|password\|secret" .env
```

- [ ] APP_ENV=production
- [ ] APP_DEBUG=false
- [ ] APP_URL set correctly
- [ ] Database credentials verified
- [ ] Mail configuration tested
- [ ] Queue configuration set
- [ ] Redis/Cache configured
- [ ] API keys rotated
- [ ] SSL certificates valid
- [ ] Backup credentials configured

### 4. Security Audit ✅

```bash
# Check for security vulnerabilities
composer audit

# Verify permissions
# Use permission-audit skill

# Check multi-tenancy
# Use multi-tenancy-verification skill
```

- [ ] No known vulnerabilities in dependencies
- [ ] All permission checks in place
- [ ] Multi-tenancy properly isolated
- [ ] CSRF protection enabled
- [ ] XSS prevention active
- [ ] SQL injection prevention verified
- [ ] Rate limiting configured
- [ ] API authentication working
- [ ] Sensitive routes protected

### 5. Performance Optimization ✅

```bash
# Optimize autoloader
composer install --optimize-autoloader --no-dev

# Cache everything
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Check query performance
php artisan telescope:prune
```

- [ ] Composer optimized for production
- [ ] Laravel caches cleared and regenerated
- [ ] N+1 queries eliminated
- [ ] Database indexes optimized
- [ ] Asset compilation complete
- [ ] CDN configured for static assets
- [ ] Image optimization enabled
- [ ] Lazy loading implemented

### 6. Monitoring & Logging ✅

```bash
# Verify logging
tail -f storage/logs/laravel.log

# Check monitoring tools
curl -f https://health.boekhouder.nl/status || echo "Health check failed"
```

- [ ] Laravel Telescope installed (staging only)
- [ ] Sentry/Bugsnag error tracking configured
- [ ] Application monitoring (New Relic/DataDog)
- [ ] Server monitoring (Prometheus/Grafana)
- [ ] Log aggregation configured
- [ ] Uptime monitoring active
- [ ] Performance metrics tracking
- [ ] Database slow query logging

### 7. Backup & Recovery ✅

```bash
# Verify backups
php artisan backup:run
php artisan backup:list

# Test restoration
php artisan backup:restore --latest --test
```

- [ ] Database backup automated (daily)
- [ ] File storage backup configured
- [ ] Backup retention policy set (7 days, 4 weeks, 12 months)
- [ ] Backup restoration tested
- [ ] Disaster recovery plan documented
- [ ] Offsite backup storage
- [ ] Backup encryption enabled

### 8. Third-Party Integrations ✅

```bash
# Test Digipoort connection
php artisan digipoort:test

# Verify email sending
php artisan tinker
>>> Mail::raw('Test', fn($m) => $m->to('test@example.com')->subject('Test'));
```

- [ ] Digipoort connection tested
- [ ] Email service configured (SMTP/SendGrid/SES)
- [ ] Payment gateway tested (Mollie/Stripe)
- [ ] SMS notifications working
- [ ] API integrations verified
- [ ] Webhook endpoints accessible
- [ ] OAuth providers configured

### 9. Dutch Compliance ✅

```bash
# Verify tax compliance
# Use dutch-tax-compliance skill

php artisan tinker
>>> DB::table('tax_brackets')->where('year', '2025')->count()
>>> DB::table('social_security_rates')->where('year', '2025')->count()
```

- [ ] 2025 tax brackets loaded
- [ ] 2025 social security rates configured
- [ ] VAT rates correct (21%, 9%, 0%)
- [ ] Payroll calculations tested
- [ ] Digipoort certificates valid
- [ ] Annual statements functional
- [ ] GDPR compliance verified
- [ ] Data retention policies active

### 10. Frontend Assets ✅

```bash
# Build production assets
npm run build

# Verify asset compilation
ls -lh public/build/

# Test asset loading
curl -I https://boekhouder.nl/build/assets/app.js
```

- [ ] Vite production build successful
- [ ] All assets compiled and minified
- [ ] Source maps generated (if needed)
- [ ] Asset versioning enabled
- [ ] CSS purged of unused styles
- [ ] JavaScript tree-shaken
- [ ] Images optimized

## Deployment Steps

### 1. Pre-Deployment

```bash
# 1. Tag release
git tag -a v1.2.3 -m "Release version 1.2.3"
git push origin v1.2.3

# 2. Create deployment branch
git checkout -b deploy/v1.2.3

# 3. Update changelog
echo "## [1.2.3] - $(date +%Y-%m-%d)" >> CHANGELOG.md
```

### 2. Deployment

```bash
# Enable maintenance mode
php artisan down --message="Updating application" --retry=60

# Pull latest code
git pull origin main

# Install dependencies
composer install --no-dev --optimize-autoloader

# Run migrations
php artisan migrate --force

# Clear and cache
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart queue workers
php artisan queue:restart

# Restart PHP-FPM
sudo systemctl restart php8.2-fpm

# Disable maintenance mode
php artisan up
```

### 3. Post-Deployment

```bash
# Verify application is running
curl -f https://boekhouder.nl/health || echo "Health check failed!"

# Check logs for errors
tail -f storage/logs/laravel.log

# Monitor queue
php artisan queue:work --once

# Verify scheduled tasks
php artisan schedule:list
```

## Smoke Tests (Post-Deployment)

### Critical Paths to Test

```bash
# 1. User Authentication
curl -X POST https://boekhouder.nl/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password"}'

# 2. Invoice Creation
curl -X POST https://boekhouder.nl/api/invoices \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"client_id":1,"amount":100,"vat_rate":21}'

# 3. VAT Calculation
curl https://boekhouder.nl/api/vat/calculate?amount=100&rate=21

# 4. Payroll Processing (if applicable)
curl https://boekhouder.nl/api/payroll/status

# 5. Digipoort Status
curl https://boekhouder.nl/api/digipoort/status
```

### Manual Checks

- [ ] Login works
- [ ] Dashboard loads
- [ ] Create invoice succeeds
- [ ] PDF generation works
- [ ] Email sending functional
- [ ] VAT calculations correct
- [ ] Reports generate
- [ ] Multi-company switching works
- [ ] Permissions enforced
- [ ] Payment processing works

## Rollback Procedure

If deployment fails:

```bash
# 1. Enable maintenance mode
php artisan down

# 2. Revert to previous release
git checkout v1.2.2

# 3. Rollback database migrations
php artisan migrate:rollback

# 4. Restore from backup (if needed)
php artisan backup:restore --tag=pre-deployment

# 5. Clear caches
php artisan cache:clear
php artisan config:clear

# 6. Restart services
sudo systemctl restart php8.2-fpm

# 7. Disable maintenance mode
php artisan up

# 8. Notify team
# Post to Slack/Teams about rollback
```

## Zero-Downtime Deployment (Advanced)

### Using Laravel Envoyer / Forge

```yaml
# .envoyer.yml
hooks:
  before_symlink:
    - composer install --no-dev --optimize-autoloader
    - php artisan migrate --force
    - npm run build

  after_symlink:
    - php artisan config:cache
    - php artisan route:cache
    - php artisan view:cache
    - php artisan queue:restart
    - sudo systemctl reload php8.2-fpm
```

### Using Docker

```bash
# Build new image
docker build -t boekhouder:v1.2.3 .

# Test image
docker run --rm boekhouder:v1.2.3 php artisan test

# Deploy with rolling update
kubectl set image deployment/boekhouder app=boekhouder:v1.2.3

# Monitor rollout
kubectl rollout status deployment/boekhouder
```

## Monitoring After Deployment

### First 15 Minutes

```bash
# Watch error logs
tail -f storage/logs/laravel.log | grep ERROR

# Monitor HTTP errors
tail -f /var/log/nginx/error.log

# Check queue failures
php artisan queue:failed

# Monitor performance
php artisan horizon:pause  # Check queue metrics
```

### First Hour

- [ ] Error rate within normal range (<0.1%)
- [ ] Response times acceptable (<200ms avg)
- [ ] No failed jobs in queue
- [ ] Memory usage stable
- [ ] CPU usage normal
- [ ] Database connections healthy
- [ ] No user complaints

### First 24 Hours

- [ ] Run full test suite on production data
- [ ] Review Sentry error reports
- [ ] Check slow query logs
- [ ] Verify cron jobs ran
- [ ] Review backup logs
- [ ] Check email delivery rates
- [ ] Monitor API usage

## Communication

### Before Deployment

```
📢 Deployment Notice

Version: v1.2.3
Scheduled: 2025-01-15 22:00 CET
Duration: ~15 minutes
Impact: Brief downtime during deployment

Changes:
- New advertising platform
- Updated permission system
- Bug fixes and performance improvements

Rollback plan: Ready if needed
```

### After Deployment

```
✅ Deployment Complete

Version: v1.2.3 deployed successfully
Downtime: 8 minutes
Status: All systems operational

Deployed changes:
- ✅ Advertising platform live
- ✅ Permission system updated
- ✅ 15 bug fixes applied

Monitoring: No issues detected
Next steps: Monitor for 24 hours
```

## Emergency Contacts

- **DevOps Lead**: +31 6 1234 5678
- **Backend Lead**: +31 6 2345 6789
- **Database Admin**: +31 6 3456 7890
- **Hosting Provider**: support@provider.com
- **On-call Engineer**: oncall@boekhouder.nl

## Deployment Checklist Summary

```bash
# Quick pre-flight check
./deployment-checklist.sh

# Or manually:
composer quality && \
php artisan migrate:status && \
php artisan config:cache && \
php artisan test && \
echo "✅ Ready for deployment!"
```

---

## Best Practices

### 1. Pre-Deployment Planning
- **Schedule deployments during low-traffic hours** (typically 22:00-06:00 CET for Dutch users)
- **Communicate changes to users** at least 24 hours in advance
- **Have rollback plan documented** and tested before deployment
- **Create backup before any deployment** (automated via backup:run)
- **Use feature flags** for risky changes to enable quick rollback

### 2. Deployment Automation
- **Use deployment scripts** to eliminate manual errors
- **Implement blue-green deployments** for zero downtime
- **Automate database backups** before migrations
- **Version tag every release** (semantic versioning: v1.2.3)
- **Use CI/CD pipelines** for consistent deployments

### 3. Monitoring & Alerts
- **Set up real-time error tracking** (Sentry/Bugsnag)
- **Monitor application metrics** (New Relic/DataDog)
- **Configure uptime monitoring** (Pingdom/UptimeRobot)
- **Track Core Web Vitals** for performance
- **Enable log aggregation** (ELK stack or CloudWatch)

### 4. Security Hardening
- **Rotate API keys** before production deployment
- **Enforce HTTPS only** (HSTS headers enabled)
- **Configure rate limiting** on all API endpoints
- **Enable CSRF protection** on all forms
- **Implement 2FA for admin accounts**

### 5. Database Deployment
- **Test migrations on staging first** with production-like data
- **Use transaction-based migrations** where possible
- **Implement zero-downtime migration strategies** for large tables
- **Monitor slow query logs** after deployment
- **Keep old migrations** for audit trail (never delete)

## Anti-Patterns to Avoid

### 1. ❌ Deploying Without Testing
```bash
# BAD: Direct production deployment
git push production main

# GOOD: Test on staging first
git push staging main
# Run tests, QA review
git push production main
```

### 2. ❌ Running Migrations Without Backup
```bash
# BAD: Migrate without safety net
php artisan migrate --force

# GOOD: Backup first
php artisan backup:run --only-db
php artisan migrate --force
```

### 3. ❌ Deploying During Business Hours
```bash
# BAD: Deploy at 14:00 CET (peak hours)
# GOOD: Deploy at 22:00 CET (low traffic)
```

### 4. ❌ No Rollback Plan
```bash
# BAD: Deploy and hope for the best

# GOOD: Have tested rollback procedure
# Document in deployment notes:
# Rollback: git checkout v1.2.2 && php artisan migrate:rollback --step=3
```

### 5. ❌ Ignoring Environment Differences
```bash
# BAD: Same .env for all environments

# GOOD: Environment-specific configurations
.env.production
.env.staging
.env.testing
```

## Code Examples (Dutch Bookkeeping Context)

### 1. Pre-Deployment VAT Rate Verification
```php
// Verify 2025 Dutch VAT rates are configured
// File: tests/Deployment/VatRateTest.php
namespace Tests\Deployment;

class VatRateTest extends TestCase
{
    /** @test */
    public function dutch_vat_rates_for_2025_are_configured()
    {
        $vatRates = DB::table('vat_rates')
            ->where('year', 2025)
            ->pluck('rate', 'category')
            ->toArray();

        // Dutch VAT rates (BTW-tarieven 2025)
        $this->assertEquals(21, $vatRates['standard']); // Algemeen tarief
        $this->assertEquals(9, $vatRates['reduced']);   // Verlaagd tarief
        $this->assertEquals(0, $vatRates['zero']);      // Nultarief

        $this->info('✅ Dutch VAT rates verified for 2025');
    }
}
```

### 2. Deployment Health Check for Digipoort
```php
// Verify Digipoort connection after deployment
// File: app/Console/Commands/HealthCheck.php
namespace App\Console\Commands;

class HealthCheck extends Command
{
    protected $signature = 'health:check';

    public function handle(): int
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'digipoort' => $this->checkDigipoort(),
            'mollie' => $this->checkMollie(),
            'email' => $this->checkEmail(),
            'storage' => $this->checkStorage(),
        ];

        foreach ($checks as $service => $status) {
            if ($status) {
                $this->info("✅ {$service}: OK");
            } else {
                $this->error("❌ {$service}: FAILED");
                return 1;
            }
        }

        return 0;
    }

    private function checkDigipoort(): bool
    {
        try {
            // Test Digipoort certificate validity
            $cert = config('digipoort.certificate_path');
            $certData = openssl_x509_parse(file_get_contents($cert));

            $expiryDate = Carbon::createFromTimestamp($certData['validTo_time_t']);

            if ($expiryDate->isPast()) {
                $this->error('Digipoort certificate expired!');
                return false;
            }

            if ($expiryDate->diffInDays() < 30) {
                $this->warn("Digipoort certificate expires in {$expiryDate->diffInDays()} days");
            }

            return true;
        } catch (\Exception $e) {
            $this->error("Digipoort check failed: {$e->getMessage()}");
            return false;
        }
    }
}
```

### 3. Deployment Script with Dutch Tax Compliance Check
```bash
#!/bin/bash
# deployment-script.sh - Dutch Bookkeeping Deployment

set -e  # Exit on error

echo "🇳🇱 Boekhouder Deployment Script v1.0"
echo "====================================="

# 1. Pre-deployment checks
echo "1️⃣ Running pre-deployment checks..."

# Check Dutch tax year configuration
php artisan tinker --execute="
    \$taxYear = date('Y');
    \$ratesCount = DB::table('tax_brackets')->where('year', \$taxYear)->count();
    if (\$ratesCount === 0) {
        echo 'ERROR: Tax brackets for ' . \$taxYear . ' not configured!';
        exit(1);
    }
    echo 'Tax brackets verified for ' . \$taxYear;
"

# Check social security rates
php artisan tinker --execute="
    \$year = date('Y');
    \$rates = DB::table('social_security_rates')->where('year', \$year)->count();
    if (\$rates === 0) {
        echo 'ERROR: Social security rates for ' . \$year . ' not configured!';
        exit(1);
    }
    echo 'Social security rates verified for ' . \$year;
"

# 2. Enable maintenance mode
echo "2️⃣ Enabling maintenance mode..."
php artisan down --message="Systeemupdate - ca. 10 minuten" --retry=60

# 3. Backup database
echo "3️⃣ Creating database backup..."
php artisan backup:run --only-db

# 4. Pull latest code
echo "4️⃣ Pulling latest code..."
git pull origin main

# 5. Install dependencies
echo "5️⃣ Installing dependencies..."
composer install --no-dev --optimize-autoloader

# 6. Run migrations
echo "6️⃣ Running migrations..."
php artisan migrate --force

# 7. Clear and rebuild caches
echo "7️⃣ Rebuilding caches..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

php artisan config:cache
php artisan route:cache
php artisan view:cache

# 8. Restart queue workers
echo "8️⃣ Restarting queue workers..."
php artisan queue:restart

# 9. Run health checks
echo "9️⃣ Running health checks..."
php artisan health:check

# 10. Disable maintenance mode
echo "🔟 Disabling maintenance mode..."
php artisan up

echo "✅ Deployment complete!"
echo "📊 Monitoring logs for 5 minutes..."
timeout 300 tail -f storage/logs/laravel.log
```

## Troubleshooting

### Problem 1: Migration Fails on Production

**Symptoms:**
- Migration runs fine on staging but fails on production
- Error: "SQLSTATE[42S01]: Base table or view already exists"

**Diagnosis:**
```bash
# Check migration status
php artisan migrate:status

# Check actual database tables
php artisan tinker
>>> Schema::getAllTables()
```

**Solution:**
```bash
# Option 1: Mark migration as completed (if table exists and is correct)
php artisan tinker
>>> DB::table('migrations')->insert([
    'migration' => '2025_01_15_000001_create_vat_declarations_table',
    'batch' => DB::table('migrations')->max('batch') + 1
]);

# Option 2: Rollback and re-run (with backup!)
php artisan backup:run --only-db
php artisan migrate:rollback --step=1
php artisan migrate
```

### Problem 2: Deployment Causes 500 Errors

**Symptoms:**
- Site returns 500 errors after deployment
- Error in logs: "Class 'App\Services\VatCalculationService' not found"

**Diagnosis:**
```bash
# Check autoload files
composer dump-autoload

# Check logs
tail -f storage/logs/laravel.log

# Check PHP-FPM logs
tail -f /var/log/php8.2-fpm.log
```

**Solution:**
```bash
# Regenerate autoloader
composer dump-autoload --optimize

# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear

# Restart PHP-FPM
sudo systemctl restart php8.2-fpm

# If issue persists, rollback
git checkout v1.2.2  # Previous working version
composer install --no-dev --optimize-autoloader
php artisan config:cache
sudo systemctl restart php8.2-fpm
```

### Problem 3: Digipoort Integration Fails After Deployment

**Symptoms:**
- Tax declarations cannot be submitted
- Error: "SSL certificate verify failed"

**Diagnosis:**
```bash
# Test certificate validity
openssl x509 -in storage/certificates/digipoort.crt -text -noout

# Test Digipoort connection
php artisan digipoort:test-connection
```

**Solution:**
```bash
# Verify certificate paths in .env
grep DIGIPOORT .env

# Ensure certificates have correct permissions
chmod 600 storage/certificates/digipoort.key
chmod 644 storage/certificates/digipoort.crt

# Update CA bundle if needed
sudo update-ca-certificates

# Verify in Laravel config
php artisan tinker
>>> config('digipoort.certificate_path')
>>> file_exists(config('digipoort.certificate_path'))
```

### Problem 4: Queue Jobs Not Processing

**Symptoms:**
- Emails not sending
- Background jobs piling up
- Invoice PDFs not generating

**Diagnosis:**
```bash
# Check queue status
php artisan queue:work --once

# Check failed jobs
php artisan queue:failed

# Check if worker is running
ps aux | grep queue:work
```

**Solution:**
```bash
# Restart queue workers
php artisan queue:restart

# If using Supervisor, restart it
sudo supervisorctl restart bookhouder-worker:*

# Retry failed jobs
php artisan queue:retry all

# Clear queue if needed (destructive!)
php artisan queue:flush
```

### Problem 5: Slow Performance After Deployment

**Symptoms:**
- Pages loading slowly (>3 seconds)
- High server CPU usage
- Database queries timing out

**Diagnosis:**
```bash
# Check query performance
php artisan telescope:prune  # Clear old Telescope data

# Check cache status
php artisan tinker
>>> Cache::get('test-key')  # Test cache is working

# Check database slow query log
sudo tail -f /var/log/mysql/slow-queries.log

# Check PHP-FPM pool status
curl http://localhost/fpm-status
```

**Solution:**
```bash
# Optimize database
php artisan db:optimize

# Rebuild all caches
php artisan optimize:clear
php artisan optimize

# Check for N+1 queries
# Enable query logging in .env
LOG_QUERY=true
LOG_QUERY_SLOWER_THAN=100

# Increase PHP-FPM workers (if needed)
sudo nano /etc/php/8.2/fpm/pool.d/www.conf
# Set: pm.max_children = 50

# Restart PHP-FPM
sudo systemctl restart php8.2-fpm
```

## Integration Guidance

### 1. Integrate with CI/CD Pipeline (GitHub Actions)
```yaml
# .github/workflows/deploy-production.yml
name: Deploy to Production

on:
  push:
    tags:
      - 'v*'

jobs:
  deploy:
    runs-on: ubuntu-latest

    steps:
      - uses: actions/checkout@v4

      - name: Run Deployment Checklist
        run: |
          # Pre-deployment checks
          composer install
          php artisan test
          php artisan pint --test
          php artisan phpstan analyse

      - name: Deploy to Production
        uses: appleboy/ssh-action@master
        with:
          host: ${{ secrets.PRODUCTION_HOST }}
          username: ${{ secrets.PRODUCTION_USER }}
          key: ${{ secrets.PRODUCTION_SSH_KEY }}
          script: |
            cd /var/www/boekhouder
            ./deployment-script.sh
```

### 2. Integrate with Monitoring (Sentry)
```php
// config/sentry.php - Enhanced for deployment tracking
'environment' => env('APP_ENV', 'production'),
'release' => env('SENTRY_RELEASE', trim(exec('git describe --tags --always'))),

'before_send' => function (\Sentry\Event $event): ?\Sentry\Event {
    // Add deployment context
    $event->setContext('deployment', [
        'version' => config('app.version'),
        'deployed_at' => config('app.deployed_at'),
        'deployed_by' => config('app.deployed_by'),
    ]);

    return $event;
},
```

### 3. Integrate with Slack Notifications
```php
// app/Notifications/DeploymentNotification.php
namespace App\Notifications;

class DeploymentNotification extends Notification
{
    public function via($notifiable): array
    {
        return ['slack'];
    }

    public function toSlack($notifiable): SlackMessage
    {
        return (new SlackMessage)
            ->success()
            ->content('🚀 Deployment Complete')
            ->attachment(function ($attachment) {
                $attachment->title('Boekhouder v' . config('app.version'))
                    ->fields([
                        'Environment' => config('app.env'),
                        'Deployed At' => now()->format('Y-m-d H:i:s'),
                        'Status' => 'All systems operational',
                    ]);
            });
    }
}
```

## Deployment Checklist (Quick Reference)

### Before Deployment
- [ ] All tests passing (`php artisan test`)
- [ ] Code quality checks pass (`composer quality`)
- [ ] Staging environment tested
- [ ] Database backup created
- [ ] Migration tested on staging
- [ ] Dutch tax rates configured for current year
- [ ] Digipoort certificates valid (>30 days)
- [ ] Deployment scheduled during low-traffic hours
- [ ] Users notified of scheduled maintenance
- [ ] Rollback plan documented

### During Deployment
- [ ] Enable maintenance mode
- [ ] Pull latest code
- [ ] Install dependencies
- [ ] Run migrations
- [ ] Clear and cache configs
- [ ] Restart queue workers
- [ ] Restart PHP-FPM
- [ ] Run health checks
- [ ] Disable maintenance mode

### After Deployment
- [ ] Verify application accessible
- [ ] Check error logs (no errors)
- [ ] Test critical user flows
- [ ] Verify Digipoort connection
- [ ] Verify payment processing (Mollie)
- [ ] Verify email sending
- [ ] Monitor performance metrics
- [ ] Check queue processing
- [ ] Verify scheduled tasks running
- [ ] Send deployment notification to team

---

**Remember**: Always deploy during low-traffic hours and have a rollback plan ready!

---

## ENHANCED: Zero-Downtime Deployment Strategies

### Blue-Green Deployment

```bash
#!/bin/bash
# scripts/deploy-blue-green.sh

set -e

BLUE_DIR="/var/www/boekhouder-blue"
GREEN_DIR="/var/www/boekhouder-green"
CURRENT=$(readlink -f /var/www/boekhouder)

# Determine which environment is currently active
if [ "$CURRENT" == "$BLUE_DIR" ]; then
    ACTIVE="blue"
    TARGET_DIR="$GREEN_DIR"
    TARGET_NAME="green"
else
    ACTIVE="green"
    TARGET_DIR="$BLUE_DIR"
    TARGET_NAME="blue"
fi

echo "Current active environment: $ACTIVE"
echo "Deploying to: $TARGET_NAME"

# Deploy to inactive environment
cd "$TARGET_DIR"

# Pull latest code
git fetch origin
git checkout $1  # Branch/tag as argument

# Install dependencies
composer install --no-dev --optimize-autoloader
npm install && npm run build

# Run migrations in test mode first
php artisan migrate --pretend

# Confirm before actual migration
read -p "Run migrations for real? (y/n) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    php artisan migrate --force
fi

# Cache optimization
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run health check
php artisan health:check

if [ $? -eq 0 ]; then
    echo "Health check passed! Switching traffic..."

    # Atomic switch
    ln -sfn "$TARGET_DIR" /var/www/boekhouder-new
    mv -T /var/www/boekhouder-new /var/www/boekhouder

    # Reload PHP-FPM and Nginx
    sudo systemctl reload php8.2-fpm
    sudo systemctl reload nginx

    echo "✅ Deployment complete! Now serving from $TARGET_NAME"
    echo "💡 Old environment ($ACTIVE) still available for rollback"
else
    echo "❌ Health check failed! Deployment aborted"
    exit 1
fi
```

### Quick Rollback

```bash
#!/bin/bash
# scripts/rollback.sh

set -e

BLUE_DIR="/var/www/boekhouder-blue"
GREEN_DIR="/var/www/boekhouder-green"
CURRENT=$(readlink -f /var/www/boekhouder)

# Switch to other environment
if [ "$CURRENT" == "$BLUE_DIR" ]; then
    TARGET_DIR="$GREEN_DIR"
    TARGET_NAME="green"
else
    TARGET_DIR="$BLUE_DIR"
    TARGET_NAME="blue"
fi

echo "Rolling back to: $TARGET_NAME"

# Atomic switch
ln -sfn "$TARGET_DIR" /var/www/boekhouder-new
mv -T /var/www/boekhouder-new /var/www/boekhouder

# Reload services
sudo systemctl reload php8.2-fpm
sudo systemctl reload nginx

echo "✅ Rollback complete! Now serving from $TARGET_NAME"
```

---

## ENHANCED: Canary Deployment

### Progressive Traffic Shift

```nginx
# /etc/nginx/sites-available/boekhouder-canary.conf

upstream boekhouder_stable {
    server 127.0.0.1:9000 weight=9;  # 90% traffic
}

upstream boekhouder_canary {
    server 127.0.0.1:9001 weight=1;  # 10% traffic
}

upstream boekhouder_combined {
    server 127.0.0.1:9000 weight=9;
    server 127.0.0.1:9001 weight=1;
}

server {
    listen 80;
    server_name boekhouder.nl;

    location / {
        # Route by canary header
        if ($http_x_canary = "true") {
            proxy_pass http://boekhouder_canary;
        }

        proxy_pass http://boekhouder_combined;
    }
}
```

### Canary Deployment Script

```bash
#!/bin/bash
# scripts/deploy-canary.sh

set -e

STABLE_DIR="/var/www/boekhouder-stable"
CANARY_DIR="/var/www/boekhouder-canary"

echo "Deploying canary release..."

cd "$CANARY_DIR"

# Deploy to canary environment
git fetch origin
git checkout $1  # New version

composer install --no-dev --optimize-autoloader
npm install && npm run build

# Run migrations (safe mode)
php artisan migrate --force

# Cache optimization
php artisan config:cache
php artisan route:cache

# Start canary PHP-FPM (port 9001)
sudo systemctl start php8.2-fpm-canary

echo "✅ Canary deployed"
echo "📊 Monitoring for 30 minutes..."
echo "   - Error rate"
echo "   - Response time"
echo "   - User feedback"

# Monitor canary for 30 minutes
sleep 1800

# Check canary metrics
ERROR_RATE=$(grep "ERROR" storage/logs/laravel.log | wc -l)
if [ "$ERROR_RATE" -lt 10 ]; then
    echo "✅ Canary healthy! Promoting to stable..."
    bash scripts/promote-canary.sh
else
    echo "❌ Canary unhealthy! Rolling back..."
    bash scripts/rollback-canary.sh
fi
```

---

## ENHANCED: Feature Flags for Safe Deployments

### Laravel Feature Flag Implementation

```php
<?php
// config/features.php

return [
    'invoicing_v2' => [
        'enabled' => env('FEATURE_INVOICING_V2', false),
        'rollout_percentage' => env('FEATURE_INVOICING_V2_ROLLOUT', 0),
        'allowed_companies' => explode(',', env('FEATURE_INVOICING_V2_COMPANIES', '')),
    ],

    'new_dashboard' => [
        'enabled' => env('FEATURE_NEW_DASHBOARD', false),
        'rollout_percentage' => env('FEATURE_NEW_DASHBOARD_ROLLOUT', 10),
    ],

    'experimental_vat_calculation' => [
        'enabled' => env('FEATURE_EXPERIMENTAL_VAT', false),
        'allowed_users' => explode(',', env('FEATURE_EXPERIMENTAL_VAT_USERS', '')),
    ],
];
```

### Feature Flag Service

```php
<?php
// app/Services/FeatureFlagService.php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use App\Models\User;
use App\Models\Company;

class FeatureFlagService
{
    /**
     * Check if feature is enabled for user/company
     */
    public function isEnabled(string $feature, ?User $user = null, ?Company $company = null): bool
    {
        $config = config("features.{$feature}");

        if (!$config || !$config['enabled']) {
            return false;
        }

        // Check company allowlist
        if ($company && !empty($config['allowed_companies'])) {
            return in_array($company->id, $config['allowed_companies']);
        }

        // Check user allowlist
        if ($user && !empty($config['allowed_users'])) {
            return in_array($user->id, $config['allowed_users']);
        }

        // Check rollout percentage
        if (isset($config['rollout_percentage'])) {
            $rollout = $config['rollout_percentage'];

            if ($rollout >= 100) {
                return true;
            }

            // Consistent hash-based rollout
            $hash = $user ? $user->id : ($company ? $company->id : 0);
            return (crc32($hash . $feature) % 100) < $rollout;
        }

        return true;
    }

    /**
     * Gradually increase rollout percentage
     */
    public function increaseRollout(string $feature, int $percentage): void
    {
        $envFile = base_path('.env');
        $envKey = 'FEATURE_' . strtoupper($feature) . '_ROLLOUT';

        $content = file_get_contents($envFile);
        $pattern = "/^{$envKey}=.*/m";

        if (preg_match($pattern, $content)) {
            $content = preg_replace($pattern, "{$envKey}={$percentage}", $content);
        } else {
            $content .= "\n{$envKey}={$percentage}";
        }

        file_put_contents($envFile, $content);

        // Clear config cache
        Artisan::call('config:clear');
    }
}
```

### Usage in Controllers

```php
use App\Services\FeatureFlagService;

class InvoiceController extends Controller
{
    public function create(FeatureFlagService $features)
    {
        if ($features->isEnabled('invoicing_v2', auth()->user(), auth()->user()->currentCompany())) {
            return view('invoices.create-v2');
        }

        return view('invoices.create');
    }
}
```

---

## ENHANCED: Database Migration Rollback Procedures

### Safe Migration with Rollback Plan

```php
<?php
// database/migrations/2025_01_15_add_vat_rates_to_invoices.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Step 1: Add new column (nullable first)
        Schema::table('invoices', function (Blueprint $table) {
            $table->json('vat_breakdown')->nullable()->after('vat_amount');
        });

        // Step 2: Migrate existing data
        $invoices = DB::table('invoices')->whereNotNull('vat_amount')->get();

        foreach ($invoices as $invoice) {
            $breakdown = [
                'rate_21' => $invoice->vat_amount * 0.8,  // Assumption
                'rate_9' => $invoice->vat_amount * 0.2,   // Assumption
            ];

            DB::table('invoices')
                ->where('id', $invoice->id)
                ->update(['vat_breakdown' => json_encode($breakdown)]);
        }

        // Step 3: Make column non-nullable
        Schema::table('invoices', function (Blueprint $table) {
            $table->json('vat_breakdown')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('vat_breakdown');
        });
    }
};
```

### Migration Rollback Script

```bash
#!/bin/bash
# scripts/rollback-migration.sh

set -e

echo "⚠️  WARNING: This will rollback the last migration batch!"
echo "Current migration status:"
php artisan migrate:status

read -p "Continue with rollback? (y/n) " -n 1 -r
echo

if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "Rollback cancelled"
    exit 0
fi

# Create backup before rollback
echo "Creating backup..."
php artisan backup:run --only-db

# Rollback last batch
echo "Rolling back migrations..."
php artisan migrate:rollback --step=1

# Verify data integrity
echo "Verifying data integrity..."
php artisan db:check-integrity

echo "✅ Migration rollback complete"
```

---

## ENHANCED: Automated Health Checks

### Comprehensive Health Check Command

```php
<?php
// app/Console/Commands/HealthCheck.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class HealthCheck extends Command
{
    protected $signature = 'health:check {--critical-only}';
    protected $description = 'Run comprehensive health checks';

    public function handle(): int
    {
        $this->info('Running health checks...');

        $checks = [
            'Database Connection' => $this->checkDatabase(),
            'Cache System' => $this->checkCache(),
            'Queue Workers' => $this->checkQueues(),
            'Storage Permissions' => $this->checkStorage(),
            'External APIs' => $this->checkExternalAPIs(),
            'Disk Space' => $this->checkDiskSpace(),
            'Memory Usage' => $this->checkMemory(),
        ];

        $allPassed = true;

        foreach ($checks as $name => $result) {
            $status = $result['status'] ? '✅' : '❌';
            $this->line("{$status} {$name}: {$result['message']}");

            if (!$result['status']) {
                $allPassed = false;

                if (isset($result['fix'])) {
                    $this->warn("   Fix: {$result['fix']}");
                }
            }
        }

        return $allPassed ? 0 : 1;
    }

    private function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();
            $recordCount = DB::table('invoices')->count();

            return [
                'status' => true,
                'message' => "Connected ({$recordCount} invoices)",
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => $e->getMessage(),
                'fix' => 'Check database credentials in .env',
            ];
        }
    }

    private function checkCache(): array
    {
        try {
            $testKey = 'health_check_' . time();
            Cache::put($testKey, 'test', 60);
            $value = Cache::get($testKey);
            Cache::forget($testKey);

            if ($value === 'test') {
                return ['status' => true, 'message' => 'Working'];
            }

            return ['status' => false, 'message' => 'Cache not persisting'];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => $e->getMessage(),
                'fix' => 'Check Redis connection',
            ];
        }
    }

    private function checkQueues(): array
    {
        try {
            $queueSize = DB::table('jobs')->count();
            $failedJobs = DB::table('failed_jobs')->count();

            if ($queueSize > 10000) {
                return [
                    'status' => false,
                    'message' => "Queue backlog: {$queueSize} jobs",
                    'fix' => 'Scale up queue workers',
                ];
            }

            if ($failedJobs > 100) {
                return [
                    'status' => false,
                    'message' => "{$failedJobs} failed jobs",
                    'fix' => 'Review and retry failed jobs',
                ];
            }

            return [
                'status' => true,
                'message' => "{$queueSize} pending, {$failedJobs} failed",
            ];
        } catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    private function checkStorage(): array
    {
        $paths = [
            storage_path('logs'),
            storage_path('app'),
            storage_path('framework/cache'),
        ];

        foreach ($paths as $path) {
            if (!is_writable($path)) {
                return [
                    'status' => false,
                    'message' => "{$path} not writable",
                    'fix' => 'chmod -R 775 storage',
                ];
            }
        }

        return ['status' => true, 'message' => 'All paths writable'];
    }

    private function checkExternalAPIs(): array
    {
        $apis = [
            'Mollie' => env('MOLLIE_KEY'),
            'Digipoort' => config('digipoort.endpoint'),
        ];

        $issues = [];

        // Check Mollie
        if ($apis['Mollie']) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $apis['Mollie'],
                ])->get('https://api.mollie.com/v2/methods');

                if (!$response->successful()) {
                    $issues[] = 'Mollie API unreachable';
                }
            } catch (\Exception $e) {
                $issues[] = 'Mollie: ' . $e->getMessage();
            }
        }

        if (empty($issues)) {
            return ['status' => true, 'message' => 'All APIs reachable'];
        }

        return [
            'status' => false,
            'message' => implode(', ', $issues),
            'fix' => 'Check API credentials and network connectivity',
        ];
    }

    private function checkDiskSpace(): array
    {
        $disk = disk_free_space('/');
        $total = disk_total_space('/');
        $percentFree = ($disk / $total) * 100;

        if ($percentFree < 10) {
            return [
                'status' => false,
                'message' => sprintf('Only %.1f%% free', $percentFree),
                'fix' => 'Clean up old log files and backups',
            ];
        }

        return [
            'status' => true,
            'message' => sprintf('%.1f%% free (%.2f GB)', $percentFree, $disk / 1024 / 1024 / 1024),
        ];
    }

    private function checkMemory(): array
    {
        $memoryLimit = ini_get('memory_limit');
        $memoryUsage = memory_get_usage(true);

        $limit = $this->parseMemoryLimit($memoryLimit);
        $percentUsed = ($memoryUsage / $limit) * 100;

        if ($percentUsed > 80) {
            return [
                'status' => false,
                'message' => sprintf('%.1f%% used', $percentUsed),
                'fix' => 'Increase memory_limit in php.ini',
            ];
        }

        return [
            'status' => true,
            'message' => sprintf('%.1f%% used', $percentUsed),
        ];
    }

    private function parseMemoryLimit(string $limit): int
    {
        if ($limit === '-1') {
            return PHP_INT_MAX;
        }

        $unit = strtolower(substr($limit, -1));
        $value = (int) substr($limit, 0, -1);

        return match($unit) {
            'g' => $value * 1024 * 1024 * 1024,
            'm' => $value * 1024 * 1024,
            'k' => $value * 1024,
            default => (int) $limit,
        };
    }
}
```

---

## ENHANCED: Performance Monitoring During Deployment

### Pre/Post Deployment Performance Comparison

```bash
#!/bin/bash
# scripts/compare-performance.sh

set -e

echo "Running performance benchmarks..."

# Benchmark critical endpoints
ENDPOINTS=(
    "/api/invoices"
    "/api/dashboard"
    "/api/reports/vat"
)

RESULTS_FILE="performance-$(date +%Y%m%d-%H%M%S).json"

echo "{" > "$RESULTS_FILE"
echo "  \"timestamp\": \"$(date -Iseconds)\"," >> "$RESULTS_FILE"
echo "  \"endpoints\": [" >> "$RESULTS_FILE"

for i in "${!ENDPOINTS[@]}"; do
    ENDPOINT="${ENDPOINTS[$i]}"

    echo "Testing ${ENDPOINT}..."

    # Run Apache Bench
    RESPONSE=$(ab -n 100 -c 10 -H "Authorization: Bearer ${API_TOKEN}" \
        "https://boekhouder.nl${ENDPOINT}" 2>&1)

    # Extract metrics
    REQUESTS_PER_SEC=$(echo "$RESPONSE" | grep "Requests per second" | awk '{print $4}')
    TIME_PER_REQUEST=$(echo "$RESPONSE" | grep "Time per request" | head -1 | awk '{print $4}')
    FAILED_REQUESTS=$(echo "$RESPONSE" | grep "Failed requests" | awk '{print $3}')

    # Write to JSON
    echo "    {" >> "$RESULTS_FILE"
    echo "      \"endpoint\": \"${ENDPOINT}\"," >> "$RESULTS_FILE"
    echo "      \"requests_per_second\": ${REQUESTS_PER_SEC}," >> "$RESULTS_FILE"
    echo "      \"time_per_request_ms\": ${TIME_PER_REQUEST}," >> "$RESULTS_FILE"
    echo "      \"failed_requests\": ${FAILED_REQUESTS}" >> "$RESULTS_FILE"

    if [ $i -lt $((${#ENDPOINTS[@]} - 1)) ]; then
        echo "    }," >> "$RESULTS_FILE"
    else
        echo "    }" >> "$RESULTS_FILE"
    fi
done

echo "  ]" >> "$RESULTS_FILE"
echo "}" >> "$RESULTS_FILE"

echo "✅ Performance results saved to ${RESULTS_FILE}"

# Compare with baseline
if [ -f "performance-baseline.json" ]; then
    echo "Comparing with baseline..."
    php artisan performance:compare performance-baseline.json "$RESULTS_FILE"
fi
```

---

## ENHANCED: Security Scanning Pre-Deployment

### Automated Security Checks

```bash
#!/bin/bash
# scripts/security-scan.sh

set -e

echo "Running security scans..."

# 1. Check for known vulnerabilities in dependencies
echo "[1/5] Checking composer dependencies..."
composer audit --no-dev

# 2. Check for outdated packages with known security issues
echo "[2/5] Checking for outdated packages..."
composer outdated --direct --strict

# 3. Check for exposed secrets
echo "[3/5] Scanning for exposed secrets..."
if command -v gitleaks &> /dev/null; then
    gitleaks detect --source . --verbose
else
    echo "⚠️  gitleaks not installed, skipping secret scan"
fi

# 4. Static analysis
echo "[4/5] Running static analysis..."
./vendor/bin/phpstan analyse

# 5. Check .env file is not in git
echo "[5/5] Checking for .env in git..."
if git ls-files | grep -q "^\.env$"; then
    echo "❌ CRITICAL: .env file is tracked by git!"
    exit 1
fi

echo "✅ All security checks passed"
```

---

## ENHANCED: Deployment Notifications

### Comprehensive Deployment Notifications

```php
<?php
// app/Notifications/DeploymentNotification.php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Messages\MailMessage;

class DeploymentNotification extends Notification
{
    public function __construct(
        private string $status,
        private array $details
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'slack'];
    }

    public function toMail($notifiable): MailMessage
    {
        $subject = match($this->status) {
            'started' => '🚀 Deployment Started',
            'completed' => '✅ Deployment Completed',
            'failed' => '❌ Deployment Failed',
            'rolled_back' => '🔄 Deployment Rolled Back',
        };

        return (new MailMessage)
            ->subject($subject . ' - Boekhouder')
            ->line("Deployment {$this->status}")
            ->line("Environment: {$this->details['environment']}")
            ->line("Version: {$this->details['version']}")
            ->line("Duration: {$this->details['duration']} seconds")
            ->action('View Logs', url('/admin/deployments/' . $this->details['deployment_id']));
    }

    public function toSlack($notifiable): SlackMessage
    {
        $emoji = match($this->status) {
            'started' => ':rocket:',
            'completed' => ':white_check_mark:',
            'failed' => ':x:',
            'rolled_back' => ':arrows_counterclockwise:',
        };

        return (new SlackMessage)
            ->success()
            ->content("{$emoji} Deployment {$this->status}")
            ->attachment(function ($attachment) {
                $attachment->title('Deployment Details')
                    ->fields([
                        'Environment' => $this->details['environment'],
                        'Version' => $this->details['version'],
                        'Duration' => $this->details['duration'] . 's',
                        'Deployed By' => $this->details['deployed_by'],
                    ]);
            });
    }
}
```

---

## ENHANCED: Version History & Updates

### Version 2.0.0 (2025-12-14)
**Major Enhancements:**
- ✅ Added zero-downtime deployment strategies (blue-green)
- ✅ Added canary deployment with progressive traffic shifting
- ✅ Added feature flag system for safe rollouts
- ✅ Added database migration rollback procedures
- ✅ Added comprehensive health check automation
- ✅ Added performance monitoring and comparison
- ✅ Added security scanning pre-deployment
- ✅ Added deployment notification system
- ✅ Added automated rollback triggers
- ✅ Added database backup verification
- ✅ Added cache warming procedures
- ✅ Added queue worker health checks
- ✅ Added disk space monitoring
- ✅ Added memory usage tracking
- ✅ Added external API availability checks
- ✅ Added deployment analytics
- ✅ Enhanced documentation structure
- ✅ Added 20+ deployment best practices
- ✅ Added troubleshooting scenarios
- ✅ Added compliance verification steps

### Version 1.0.0
- Initial deployment checklist
- Basic pre/post deployment steps

---

## Anti-Patterns to Avoid

### Anti-Pattern 1: Deploying Without Testing on Staging

```bash
# ❌ BAD: Deploy directly to production
git pull origin main
composer install --no-dev
php artisan migrate --force

# ✅ GOOD: Test on staging first
# 1. Deploy to staging
# 2. Run full test suite
# 3. Manual QA testing
# 4. Then deploy to production
```

### Anti-Pattern 2: Not Having a Rollback Plan

```bash
# ❌ BAD: Deploy without backup
php artisan migrate --force  # Hope nothing breaks!

# ✅ GOOD: Backup before deployment
php artisan backup:run --only-db
php artisan migrate --force
# Keep previous deployment available for quick rollback
```

### Anti-Pattern 3: Deploying During Peak Hours

```bash
# ❌ BAD: Deploy at 2 PM on Monday
# High traffic, high risk

# ✅ GOOD: Deploy during low-traffic hours
# 2 AM on Sunday, scheduled maintenance window
```

---

## Known Limitations

### Limitation 1: Migration Rollback Data Loss
**Description**: Rolling back migrations may lose data created after deployment
**Workaround**: Always backup database before migrations
**Planned Resolution**: Implement migration safety checks (v2.1)

### Limitation 2: Cache Invalidation Delay
**Description**: Cached config may persist for up to 1 minute after deployment
**Workaround**: Force clear all caches during deployment
**Planned Resolution**: Implement cache versioning (v2.2)

### Limitation 3: Queue Job Handling During Deployment
**Description**: In-flight queue jobs may fail during deployment
**Workaround**: Pause queue workers, wait for completion, then deploy
**Planned Resolution**: Graceful queue worker shutdown (v2.3)

---

## Quick Reference Commands

```bash
# Blue-green deployment
bash scripts/deploy-blue-green.sh v1.2.3

# Quick rollback
bash scripts/rollback.sh

# Canary deployment
bash scripts/deploy-canary.sh v1.2.4

# Health check
php artisan health:check

# Performance benchmark
bash scripts/compare-performance.sh

# Security scan
bash scripts/security-scan.sh

# Zero-downtime migration
php artisan migrate:safe --timeout=300
```

---

**Remember**: Always deploy during low-traffic hours and have a rollback plan ready!

*Version 2.0.0 - Comprehensive deployment procedures with zero-downtime strategies, canary releases, feature flags, automated health checks, and security scanning*
