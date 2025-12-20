# Audit Trail System - Implementation Guide

## Quick Start

### 1. Run Migrations

```bash
php artisan migrate
```

This will create all audit trail tables:
- `audit_logs` - Main audit log
- `entity_versions` - Version snapshots
- `session_logs` - User session tracking
- `export_logs` - Data export tracking
- `sensitive_access_logs` - Sensitive data access
- `verification_logs` - Event verification tracking
- `audit_retention_policies` - Retention policy configuration

### 2. Make Models Auditable

Add the `Auditable` trait to any model you want to track:

```php
<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use Auditable;

    // Optional: Specify fields to exclude from auditing
    protected $auditExclude = [
        'password',
        'remember_token',
        'temporary_data',
    ];

    // Your model code...
}
```

### 3. Access Audit Data

#### Get Audit Logs for an Entity

```php
$event = Event::find(1);

// Get all audit logs
$logs = $event->auditLogs;

// Get latest 10 changes
$recent = $event->auditLogs()->limit(10)->get();

// Filter by action
$creates = $event->auditLogs()->action('create')->get();
$updates = $event->auditLogs()->action('update')->get();
```

#### Get Version History

```php
$event = Event::find(1);

// Get all versions
$versions = $event->versions;

// Get current version number
$currentVersion = $event->current_version;

// Get latest version snapshot
$latestVersion = $event->latest_version;

// Get specific version
$version = $event->versions()->where('version_number', 5)->first();
$snapshot = $version->snapshot; // Complete state at version 5
```

#### View Entity at Point in Time

```php
use App\Services\PointInTimeService;

$service = app(PointInTimeService::class);

// Get event as it was on January 1, 2025
$state = $service->getEntityAt(
    Event::class,
    $eventId,
    carbon('2025-01-01 12:00:00')
);

// Check if entity existed at timestamp
$existed = $service->entityExistedAt(
    Event::class,
    $eventId,
    carbon('2025-01-01')
);

// Get all changes in date range
$changes = $service->getChangesInRange(
    Event::class,
    $eventId,
    carbon('2025-01-01'),
    carbon('2025-01-31')
);
```

#### Compare Versions

```php
use App\Services\VersionDiffService;

$service = app(VersionDiffService::class);

// Compare two versions
$diff = $service->diff($versionId1, $versionId2);

// Result structure:
[
    'entity_type' => 'App\\Models\\Event',
    'entity_id' => 123,
    'version_a' => [...],
    'version_b' => [...],
    'changes' => [
        'status' => [
            'old' => 'pending',
            'new' => 'verified',
            'change_type' => 'modified'
        ],
        'title' => [
            'old' => 'Old Title',
            'new' => 'New Title',
            'change_type' => 'modified'
        ]
    ],
    'summary' => '2 modified'
]

// Compare at two timestamps
$diff = $service->diffAtTimestamps(
    Event::class,
    $eventId,
    '2025-01-01 12:00:00',
    '2025-01-15 12:00:00'
);
```

#### Rollback to Previous Version

```php
use App\Services\EntityRollbackService;

$service = app(EntityRollbackService::class);

// Rollback to version number
$service->rollback(
    Event::class,
    $eventId,
    $targetVersionNumber = 5,
    $user = auth()->user(),
    $reason = 'Reverting incorrect verification'
);

// Rollback to timestamp
$service->rollbackToTimestamp(
    Event::class,
    $eventId,
    $timestamp = '2025-01-01 12:00:00',
    $user = auth()->user(),
    $reason = 'Restoring to known good state'
);

// Preview rollback changes before executing
$preview = $service->getRollbackPreview(
    Event::class,
    $eventId,
    $targetVersionNumber = 5
);
```

#### Verify Audit Chain Integrity

```php
use App\Services\AuditChainService;

$service = app(AuditChainService::class);

// Verify chain for date range
$result = $service->verifyChain(
    carbon('2025-01-01'),
    carbon('2025-01-31')
);

// Result:
[
    'verified' => true,  // or false if errors found
    'checked_count' => 1500,
    'errors' => [],  // Array of any errors found
    'date_range' => [...]
]

// Verify entire chain
$result = $service->verifyEntireChain();

// Get chain statistics
$stats = $service->getChainStatistics();
```

## Advanced Usage

### Manual Audit Logging

For actions that don't involve model changes:

```php
use App\Models\AuditLog;
use Illuminate\Support\Str;

AuditLog::create([
    'uuid' => (string) Str::uuid(),
    'user_id' => auth()->id(),
    'user_email' => auth()->user()->email,
    'user_name' => auth()->user()->name,
    'action' => 'export',
    'auditable_type' => 'Event',
    'auditable_id' => 0,  // 0 for bulk operations
    'metadata' => [
        'export_format' => 'csv',
        'records_count' => 1500,
    ],
    'ip_address' => request()->ip(),
    'user_agent' => request()->userAgent(),
    'session_id' => session()->getId(),
    'occurred_at' => now(),
]);
```

### Custom Version Snapshots

Create snapshots at important milestones:

```php
use App\Models\EntityVersion;
use Illuminate\Support\Str;

$event = Event::find(1);

EntityVersion::create([
    'uuid' => (string) Str::uuid(),
    'versionable_type' => Event::class,
    'versionable_id' => $event->id,
    'version_number' => $event->current_version + 1,
    'version_hash' => hash('sha256', json_encode($event->toArray())),
    'snapshot' => $event->toArray(),
    'created_by' => auth()->id(),
    'change_summary' => 'Milestone: Event verified by expert',
    'change_type' => 'milestone',
]);
```

### Session Tracking

Track user sessions with detailed metrics:

```php
use App\Models\SessionLog;

// Session is automatically created on login
// Update session metrics
SessionLog::where('session_id', session()->getId())
    ->increment('events_created');

// Flag suspicious activity
SessionLog::where('session_id', session()->getId())
    ->update([
        'suspicious_activity' => true,
        'suspicious_reasons' => ['rapid_api_calls', 'unusual_ip'],
    ]);
```

### Export Tracking

Log all data exports:

```php
use App\Models\ExportLog;

ExportLog::create([
    'user_id' => auth()->id(),
    'export_type' => 'csv',
    'entity_type' => 'Event',
    'filter_criteria' => [
        'date_from' => '2025-01-01',
        'date_to' => '2025-01-31',
        'status' => 'verified',
    ],
    'records_exported' => 150,
    'file_size_bytes' => 52428800,  // 50MB
    'file_hash' => hash_file('sha256', $filePath),
    'file_path' => $filePath,
    'ip_address' => request()->ip(),
    'contains_pii' => true,
    'retention_category' => 'research',
]);
```

### Sensitive Data Access Logging

Track access to sensitive data:

```php
use App\Models\SensitiveAccessLog;

SensitiveAccessLog::create([
    'user_id' => auth()->id(),
    'user_role' => auth()->user()->role,
    'resource_type' => 'Event',
    'resource_id' => $event->id,
    'access_type' => 'view',
    'sensitivity_level' => 'confidential',
    'data_category' => 'source_identity',
    'purpose' => 'Verification of source credibility',
    'ip_address' => request()->ip(),
]);
```

## Artisan Commands

### Verify Audit Chain

```bash
# Verify last 7 days
php artisan audit:verify-chain

# Verify specific date range
php artisan audit:verify-chain --from=2025-01-01 --to=2025-01-31
```

### Archive Old Logs

```bash
# Archive logs according to retention policies
php artisan audit:archive

# Schedule in app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    $schedule->command('audit:archive')
        ->daily()
        ->at('02:00');
}
```

### View Audit Statistics

```bash
# Statistics for last 7 days
php artisan audit:stats

# Custom period
php artisan audit:stats --days=30
```

## API Endpoints

### Get Audit Logs

```http
GET /api/audit-logs?action=update&entity_type=Event&date_from=2025-01-01

Response:
{
  "data": [
    {
      "id": 1,
      "user_email": "analyst@example.com",
      "action": "update",
      "auditable_type": "Event",
      "auditable_id": 123,
      "changed_fields": ["status", "verification_score"],
      "created_at": "2025-01-15T14:30:00Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "total": 150
  }
}
```

### Get Entity History

```http
GET /api/events/123/history

Response:
{
  "entity_type": "Event",
  "entity_id": 123,
  "total_changes": 45,
  "created_at": "2025-01-01T10:00:00Z",
  "created_by": 15,
  "last_modified_at": "2025-01-15T14:30:00Z",
  "changelog": [...],
  "contributors": [...]
}
```

### Get Version History

```http
GET /api/events/123/versions

Response:
{
  "data": [
    {
      "id": 1,
      "version_number": 5,
      "created_at": "2025-01-15T14:30:00Z",
      "created_by": 15,
      "change_summary": "Updated status to verified",
      "change_type": "update"
    }
  ]
}
```

### Compare Versions

```http
GET /api/versions/1/diff/2

Response:
{
  "version_a": {...},
  "version_b": {...},
  "changes": {
    "status": {
      "old": "pending",
      "new": "verified",
      "change_type": "modified"
    }
  },
  "summary": "1 modified"
}
```

### Rollback Entity

```http
POST /api/events/123/rollback
Content-Type: application/json

{
  "target_version": 5,
  "reason": "Reverting incorrect verification"
}

Response:
{
  "success": true,
  "message": "Entity rolled back to version 5",
  "new_version": 8
}
```

## UI Integration

### Vue.js Components

Use the provided Vue components:

```vue
<template>
  <div>
    <!-- Audit log viewer -->
    <AuditLogViewer
      :filters="{ entity_type: 'Event', action: 'update' }"
    />

    <!-- Version history -->
    <VersionHistory
      entity-type="Event"
      :entity-id="123"
      :can-rollback="user.role === 'admin'"
    />

    <!-- Activity feed -->
    <ActivityFeed
      :user-id="user.id"
    />
  </div>
</template>

<script setup>
import AuditLogViewer from '@/components/AuditLog/AuditLogViewer.vue'
import VersionHistory from '@/components/History/VersionHistory.vue'
import ActivityFeed from '@/components/Activity/ActivityFeed.vue'
</script>
```

## Security Best Practices

### 1. Restrict Audit Log Access

```php
// app/Policies/AuditLogPolicy.php
public function viewAny(User $user): bool
{
    return $user->hasAnyRole(['admin', 'auditor']);
}

public function view(User $user, AuditLog $log): bool
{
    // Admins can see everything
    if ($user->hasRole('admin')) {
        return true;
    }

    // Users can see their own actions
    return $log->user_id === $user->id;
}
```

### 2. GDPR Compliance

```php
use App\Services\GDPRErasureService;

$service = app(GDPRErasureService::class);

// Anonymize user data
$service->anonymizeUser($user, 'User requested data deletion');

// Export all user data
$data = $service->exportUserData($user);
return response()->json($data);
```

### 3. Retention Policies

Configure in `audit_retention_policies` table:

```sql
UPDATE audit_retention_policies
SET hot_storage_days = 90,
    warm_storage_days = 730,
    cold_storage_years = 7
WHERE data_category = 'audit_logs';
```

### 4. Tamper Detection

Regularly verify chain integrity:

```php
// In scheduled job
use App\Services\AuditChainService;

$service = app(AuditChainService::class);
$result = $service->verifyChain(now()->subDay(), now());

if (!$result['verified']) {
    // Alert administrators
    \Log::critical('Audit chain verification failed', $result['errors']);
    // Send notification to security team
}
```

## Performance Optimization

### 1. Index Usage

All critical queries use indexes:
- `(auditable_type, auditable_id)` for entity lookups
- `(user_id, created_at)` for user activity
- `created_at` for time-based queries
- GIN indexes for JSONB columns

### 2. Partitioning

Partition audit logs by month for better performance:

```sql
-- Create partition for each month
CREATE TABLE audit_logs_2025_01 PARTITION OF audit_logs
    FOR VALUES FROM ('2025-01-01') TO ('2025-02-01');

CREATE TABLE audit_logs_2025_02 PARTITION OF audit_logs
    FOR VALUES FROM ('2025-02-01') TO ('2025-03-01');
```

### 3. Archival

Old logs are automatically archived to S3:

```bash
php artisan audit:archive
```

### 4. Query Optimization

Use scopes for efficient queries:

```php
// Good - uses indexes
AuditLog::byUser(123)
    ->action('update')
    ->dateRange('2025-01-01', '2025-01-31')
    ->get();

// Avoid - full table scan
AuditLog::whereRaw("metadata->>'custom_field' = 'value'")->get();
```

## Monitoring & Alerts

### Dashboard Metrics

Track these key metrics:

```php
// Audit log statistics
$stats = [
    'total_logs' => AuditLog::count(),
    'logs_today' => AuditLog::whereDate('created_at', today())->count(),
    'unique_users_today' => AuditLog::whereDate('created_at', today())
        ->distinct('user_id')
        ->count(),
    'failed_logins_today' => AuditLog::action('failed_login')
        ->whereDate('created_at', today())
        ->count(),
    'exports_today' => ExportLog::whereDate('created_at', today())->count(),
];
```

### Alert on Suspicious Activity

```php
// Detect rapid changes by same user
$rapidChanges = AuditLog::where('user_id', $userId)
    ->where('created_at', '>', now()->subMinutes(5))
    ->count();

if ($rapidChanges > 50) {
    // Alert security team
}

// Detect unusual access patterns
$sensitiveAccess = SensitiveAccessLog::where('user_id', $userId)
    ->where('created_at', '>', now()->subHour())
    ->count();

if ($sensitiveAccess > 100) {
    // Flag for review
    SessionLog::where('user_id', $userId)
        ->update([
            'suspicious_activity' => true,
            'suspicious_reasons' => ['excessive_sensitive_access'],
        ]);
}
```

## Troubleshooting

### Chain Verification Failures

If chain verification fails:

1. Check for database corruption
2. Verify no direct database modifications
3. Check for application errors during log creation
4. Review `chain_verified` column for false values

```sql
-- Find broken chain links
SELECT * FROM audit_logs WHERE chain_verified = false;
```

### Performance Issues

If queries are slow:

1. Check partition strategy
2. Verify indexes are being used (`EXPLAIN ANALYZE`)
3. Archive old logs
4. Consider read replicas for reporting

### Missing Audit Logs

If logs are missing:

1. Verify trait is added to model
2. Check if fields are in `auditExclude` array
3. Verify database permissions
4. Check application logs for errors

## Support

For issues or questions:
- Check `/docs/AUDIT_TRAIL_SPECIFICATION.md` for detailed architecture
- Review database migrations in `/database/migrations/`
- Examine model implementations in `/app/Models/`
- See service classes in `/app/Services/`
