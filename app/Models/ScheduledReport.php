<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * ScheduledReport Model
 *
 * Represents a scheduled report configuration for automated report generation.
 *
 * @property int $id
 * @property string $uuid
 * @property string $name
 * @property string|null $description
 * @property string $report_type
 * @property int|null $template_id
 * @property string $schedule_type
 * @property array|null $schedule_config
 * @property string $timezone
 * @property array|null $filters
 * @property string $date_range_type
 * @property array|null $custom_date_range
 * @property array $recipients
 * @property string $delivery_method
 * @property string|null $webhook_url
 * @property array $export_formats
 * @property bool $include_attachments
 * @property bool $is_active
 * @property \Carbon\Carbon|null $last_run_at
 * @property \Carbon\Carbon|null $next_run_at
 * @property string|null $last_run_status
 * @property string|null $last_error
 * @property int $run_count
 * @property int $success_count
 * @property int $failure_count
 * @property int $created_by
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 *
 * @property-read User $creator
 * @property-read SitrepTemplate|null $template
 * @property-read \Illuminate\Database\Eloquent\Collection<ScheduledReportRun> $runs
 * @property-read ScheduledReportRun|null $lastRun
 */
class ScheduledReport extends Model
{
    use HasFactory, SoftDeletes;

    // Report Types
    public const TYPE_SITREP = 'sitrep';
    public const TYPE_EVENT_DIGEST = 'event_digest';
    public const TYPE_EQUIPMENT_UPDATE = 'equipment_update';
    public const TYPE_TERRITORIAL_SUMMARY = 'territorial_summary';
    public const TYPE_ACTOR_ACTIVITY = 'actor_activity';
    public const TYPE_CUSTOM_QUERY = 'custom_query';

    // Schedule Types
    public const SCHEDULE_DAILY = 'daily';
    public const SCHEDULE_WEEKLY = 'weekly';
    public const SCHEDULE_BIWEEKLY = 'biweekly';
    public const SCHEDULE_MONTHLY = 'monthly';
    public const SCHEDULE_QUARTERLY = 'quarterly';

    // Date Range Types
    public const DATE_RANGE_LAST_24H = 'last_24h';
    public const DATE_RANGE_LAST_7D = 'last_7d';
    public const DATE_RANGE_LAST_30D = 'last_30d';
    public const DATE_RANGE_LAST_QUARTER = 'last_quarter';
    public const DATE_RANGE_CUSTOM = 'custom';

    // Delivery Methods
    public const DELIVERY_EMAIL = 'email';
    public const DELIVERY_WEBHOOK = 'webhook';
    public const DELIVERY_API_PUSH = 'api_push';
    public const DELIVERY_STORAGE = 'storage';

    // Run Statuses
    public const STATUS_SUCCESS = 'success';
    public const STATUS_FAILED = 'failed';
    public const STATUS_PARTIAL = 'partial';

    // Export Formats
    public const FORMAT_PDF = 'pdf';
    public const FORMAT_DOCX = 'docx';
    public const FORMAT_HTML = 'html';
    public const FORMAT_JSON = 'json';

    protected $fillable = [
        'uuid',
        'name',
        'description',
        'report_type',
        'template_id',
        'schedule_type',
        'schedule_config',
        'timezone',
        'filters',
        'date_range_type',
        'custom_date_range',
        'recipients',
        'delivery_method',
        'webhook_url',
        'export_formats',
        'include_attachments',
        'is_active',
        'last_run_at',
        'next_run_at',
        'last_run_status',
        'last_error',
        'run_count',
        'success_count',
        'failure_count',
        'created_by',
    ];

    protected $casts = [
        'schedule_config' => 'array',
        'filters' => 'array',
        'custom_date_range' => 'array',
        'recipients' => 'array',
        'export_formats' => 'array',
        'include_attachments' => 'boolean',
        'is_active' => 'boolean',
        'last_run_at' => 'datetime',
        'next_run_at' => 'datetime',
        'run_count' => 'integer',
        'success_count' => 'integer',
        'failure_count' => 'integer',
    ];

    protected $attributes = [
        'is_active' => true,
        'run_count' => 0,
        'success_count' => 0,
        'failure_count' => 0,
        'delivery_method' => self::DELIVERY_EMAIL,
        'date_range_type' => self::DATE_RANGE_LAST_7D,
        'timezone' => 'UTC',
    ];

    /**
     * Get the user who created this scheduled report
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the template for this scheduled report
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(SitrepTemplate::class, 'template_id');
    }

    /**
     * Get all runs for this scheduled report
     */
    public function runs(): HasMany
    {
        return $this->hasMany(ScheduledReportRun::class);
    }

    /**
     * Get the most recent run
     */
    public function lastRun(): BelongsTo
    {
        return $this->belongsTo(ScheduledReportRun::class, 'id', 'scheduled_report_id')
            ->ofMany('started_at', 'max');
    }

    /**
     * Get recent runs
     */
    public function recentRuns(int $limit = 10): HasMany
    {
        return $this->runs()
            ->orderByDesc('started_at')
            ->limit($limit);
    }

    /**
     * Check if the report is due for execution
     */
    public function isDue(): bool
    {
        if (!$this->is_active || !$this->next_run_at) {
            return false;
        }

        return $this->next_run_at->isPast();
    }

    /**
     * Pause the scheduled report
     */
    public function pause(): void
    {
        $this->update(['is_active' => false]);
    }

    /**
     * Resume the scheduled report
     */
    public function resume(): void
    {
        $this->update(['is_active' => true]);
    }

    /**
     * Record a successful run
     */
    public function recordSuccess(): void
    {
        $this->increment('run_count');
        $this->increment('success_count');
        $this->update([
            'last_run_at' => now(),
            'last_run_status' => self::STATUS_SUCCESS,
            'last_error' => null,
        ]);
    }

    /**
     * Record a failed run
     */
    public function recordFailure(string $error): void
    {
        $this->increment('run_count');
        $this->increment('failure_count');
        $this->update([
            'last_run_at' => now(),
            'last_run_status' => self::STATUS_FAILED,
            'last_error' => $error,
        ]);
    }

    /**
     * Record a partial run
     */
    public function recordPartial(string $error): void
    {
        $this->increment('run_count');
        $this->update([
            'last_run_at' => now(),
            'last_run_status' => self::STATUS_PARTIAL,
            'last_error' => $error,
        ]);
    }

    /**
     * Get the success rate as a percentage
     */
    public function getSuccessRateAttribute(): float
    {
        if ($this->run_count === 0) {
            return 0.0;
        }

        return round(($this->success_count / $this->run_count) * 100, 2);
    }

    /**
     * Get the date range for the next run
     */
    public function getDateRange(): array
    {
        $now = now()->timezone($this->timezone);

        return match ($this->date_range_type) {
            self::DATE_RANGE_LAST_24H => [
                'start' => $now->copy()->subHours(24),
                'end' => $now,
            ],
            self::DATE_RANGE_LAST_7D => [
                'start' => $now->copy()->subDays(7),
                'end' => $now,
            ],
            self::DATE_RANGE_LAST_30D => [
                'start' => $now->copy()->subDays(30),
                'end' => $now,
            ],
            self::DATE_RANGE_LAST_QUARTER => [
                'start' => $now->copy()->subMonths(3),
                'end' => $now,
            ],
            self::DATE_RANGE_CUSTOM => [
                'start' => isset($this->custom_date_range['start_date'])
                    ? \Carbon\Carbon::parse($this->custom_date_range['start_date'], $this->timezone)
                    : $now->copy()->subDays(7),
                'end' => isset($this->custom_date_range['end_date'])
                    ? \Carbon\Carbon::parse($this->custom_date_range['end_date'], $this->timezone)
                    : $now,
            ],
            default => [
                'start' => $now->copy()->subDays(7),
                'end' => $now,
            ],
        };
    }

    /**
     * Scope to filter active reports
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter inactive reports
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Scope to get reports due for execution
     */
    public function scopeDue($query)
    {
        return $query->active()
            ->whereNotNull('next_run_at')
            ->where('next_run_at', '<=', now());
    }

    /**
     * Scope to filter by report type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('report_type', $type);
    }

    /**
     * Scope to filter by schedule type
     */
    public function scopeScheduleType($query, string $scheduleType)
    {
        return $query->where('schedule_type', $scheduleType);
    }

    /**
     * Scope to filter by creator
     */
    public function scopeCreatedBy($query, int $userId)
    {
        return $query->where('created_by', $userId);
    }

    /**
     * Get all available report types
     */
    public static function getReportTypes(): array
    {
        return [
            self::TYPE_SITREP => 'Situation Report',
            self::TYPE_EVENT_DIGEST => 'Event Digest',
            self::TYPE_EQUIPMENT_UPDATE => 'Equipment Update',
            self::TYPE_TERRITORIAL_SUMMARY => 'Territorial Summary',
            self::TYPE_ACTOR_ACTIVITY => 'Actor Activity',
            self::TYPE_CUSTOM_QUERY => 'Custom Query',
        ];
    }

    /**
     * Get all available schedule types
     */
    public static function getScheduleTypes(): array
    {
        return [
            self::SCHEDULE_DAILY => 'Daily',
            self::SCHEDULE_WEEKLY => 'Weekly',
            self::SCHEDULE_BIWEEKLY => 'Biweekly',
            self::SCHEDULE_MONTHLY => 'Monthly',
            self::SCHEDULE_QUARTERLY => 'Quarterly',
        ];
    }

    /**
     * Get all available date range types
     */
    public static function getDateRangeTypes(): array
    {
        return [
            self::DATE_RANGE_LAST_24H => 'Last 24 Hours',
            self::DATE_RANGE_LAST_7D => 'Last 7 Days',
            self::DATE_RANGE_LAST_30D => 'Last 30 Days',
            self::DATE_RANGE_LAST_QUARTER => 'Last Quarter',
            self::DATE_RANGE_CUSTOM => 'Custom Range',
        ];
    }

    /**
     * Get all available delivery methods
     */
    public static function getDeliveryMethods(): array
    {
        return [
            self::DELIVERY_EMAIL => 'Email',
            self::DELIVERY_WEBHOOK => 'Webhook',
            self::DELIVERY_API_PUSH => 'API Push',
            self::DELIVERY_STORAGE => 'Storage Only',
        ];
    }

    /**
     * Get all available export formats
     */
    public static function getExportFormats(): array
    {
        return [
            self::FORMAT_PDF => 'PDF',
            self::FORMAT_DOCX => 'Word Document',
            self::FORMAT_HTML => 'HTML',
            self::FORMAT_JSON => 'JSON',
        ];
    }
}
