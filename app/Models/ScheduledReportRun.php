<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ScheduledReportRun Model
 *
 * Represents a single execution of a scheduled report.
 *
 * @property int $id
 * @property string $uuid
 * @property int $scheduled_report_id
 * @property \Carbon\Carbon $started_at
 * @property \Carbon\Carbon|null $completed_at
 * @property string $status
 * @property int|null $sitrep_id
 * @property array|null $generated_files
 * @property array|null $recipients_notified
 * @property array|null $delivery_status
 * @property string|null $error_message
 * @property int|null $execution_time_ms
 * @property array|null $parameters_snapshot
 * @property int $events_included
 * @property int $total_records
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property-read ScheduledReport $scheduledReport
 * @property-read Sitrep|null $sitrep
 */
class ScheduledReportRun extends Model
{
    use HasFactory;

    // Run Statuses
    public const STATUS_PENDING = 'pending';
    public const STATUS_RUNNING = 'running';
    public const STATUS_SUCCESS = 'success';
    public const STATUS_FAILED = 'failed';
    public const STATUS_PARTIAL = 'partial';

    protected $fillable = [
        'uuid',
        'scheduled_report_id',
        'started_at',
        'completed_at',
        'status',
        'sitrep_id',
        'generated_files',
        'recipients_notified',
        'delivery_status',
        'error_message',
        'execution_time_ms',
        'parameters_snapshot',
        'events_included',
        'total_records',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'generated_files' => 'array',
        'recipients_notified' => 'array',
        'delivery_status' => 'array',
        'parameters_snapshot' => 'array',
        'execution_time_ms' => 'integer',
        'events_included' => 'integer',
        'total_records' => 'integer',
    ];

    protected $attributes = [
        'status' => self::STATUS_PENDING,
        'events_included' => 0,
        'total_records' => 0,
    ];

    /**
     * Get the scheduled report
     */
    public function scheduledReport(): BelongsTo
    {
        return $this->belongsTo(ScheduledReport::class);
    }

    /**
     * Get the generated sitrep (if applicable)
     */
    public function sitrep(): BelongsTo
    {
        return $this->belongsTo(Sitrep::class);
    }

    /**
     * Mark the run as started
     */
    public function markAsRunning(): void
    {
        $this->update([
            'status' => self::STATUS_RUNNING,
            'started_at' => now(),
        ]);
    }

    /**
     * Mark the run as completed successfully
     */
    public function markAsSuccess(array $data = []): void
    {
        $completedAt = now();
        $executionTime = $this->started_at
            ? $completedAt->diffInMilliseconds($this->started_at)
            : null;

        $this->update(array_merge([
            'status' => self::STATUS_SUCCESS,
            'completed_at' => $completedAt,
            'execution_time_ms' => $executionTime,
        ], $data));
    }

    /**
     * Mark the run as failed
     */
    public function markAsFailed(string $error): void
    {
        $completedAt = now();
        $executionTime = $this->started_at
            ? $completedAt->diffInMilliseconds($this->started_at)
            : null;

        $this->update([
            'status' => self::STATUS_FAILED,
            'completed_at' => $completedAt,
            'execution_time_ms' => $executionTime,
            'error_message' => $error,
        ]);
    }

    /**
     * Mark the run as partial (some deliveries failed)
     */
    public function markAsPartial(string $error, array $data = []): void
    {
        $completedAt = now();
        $executionTime = $this->started_at
            ? $completedAt->diffInMilliseconds($this->started_at)
            : null;

        $this->update(array_merge([
            'status' => self::STATUS_PARTIAL,
            'completed_at' => $completedAt,
            'execution_time_ms' => $executionTime,
            'error_message' => $error,
        ], $data));
    }

    /**
     * Add a generated file path
     */
    public function addGeneratedFile(string $path, string $format): void
    {
        $files = $this->generated_files ?? [];
        $files[$format] = $path;
        $this->update(['generated_files' => $files]);
    }

    /**
     * Record delivery status for a recipient
     */
    public function recordDeliveryStatus(string $recipient, string $status, ?string $error = null): void
    {
        $deliveryStatus = $this->delivery_status ?? [];
        $deliveryStatus[$recipient] = [
            'status' => $status,
            'error' => $error,
            'delivered_at' => $status === 'success' ? now()->toIso8601String() : null,
        ];
        $this->update(['delivery_status' => $deliveryStatus]);

        // Update recipients notified list
        if ($status === 'success') {
            $notified = $this->recipients_notified ?? [];
            if (!in_array($recipient, $notified)) {
                $notified[] = $recipient;
                $this->update(['recipients_notified' => $notified]);
            }
        }
    }

    /**
     * Check if the run is still in progress
     */
    public function isInProgress(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_RUNNING]);
    }

    /**
     * Check if the run completed (successfully or not)
     */
    public function isCompleted(): bool
    {
        return in_array($this->status, [self::STATUS_SUCCESS, self::STATUS_FAILED, self::STATUS_PARTIAL]);
    }

    /**
     * Check if the run was successful
     */
    public function isSuccessful(): bool
    {
        return $this->status === self::STATUS_SUCCESS;
    }

    /**
     * Get the duration as a human-readable string
     */
    public function getDurationAttribute(): ?string
    {
        if (!$this->execution_time_ms) {
            return null;
        }

        if ($this->execution_time_ms < 1000) {
            return $this->execution_time_ms . 'ms';
        }

        $seconds = round($this->execution_time_ms / 1000, 2);
        if ($seconds < 60) {
            return $seconds . 's';
        }

        $minutes = floor($seconds / 60);
        $remainingSeconds = round($seconds % 60);

        return "{$minutes}m {$remainingSeconds}s";
    }

    /**
     * Get delivery success count
     */
    public function getDeliverySuccessCountAttribute(): int
    {
        if (!$this->delivery_status) {
            return 0;
        }

        return collect($this->delivery_status)
            ->where('status', 'success')
            ->count();
    }

    /**
     * Get delivery failure count
     */
    public function getDeliveryFailureCountAttribute(): int
    {
        if (!$this->delivery_status) {
            return 0;
        }

        return collect($this->delivery_status)
            ->where('status', 'failed')
            ->count();
    }

    /**
     * Scope to filter by status
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter successful runs
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', self::STATUS_SUCCESS);
    }

    /**
     * Scope to filter failed runs
     */
    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    /**
     * Scope to filter in-progress runs
     */
    public function scopeInProgress($query)
    {
        return $query->whereIn('status', [self::STATUS_PENDING, self::STATUS_RUNNING]);
    }

    /**
     * Scope to filter completed runs
     */
    public function scopeCompleted($query)
    {
        return $query->whereIn('status', [self::STATUS_SUCCESS, self::STATUS_FAILED, self::STATUS_PARTIAL]);
    }

    /**
     * Scope to filter by date range
     */
    public function scopeDateRange($query, $from, $to)
    {
        return $query->whereBetween('started_at', [$from, $to]);
    }

    /**
     * Scope to order by most recent
     */
    public function scopeRecent($query)
    {
        return $query->orderByDesc('started_at');
    }

    /**
     * Get all available statuses
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_RUNNING => 'Running',
            self::STATUS_SUCCESS => 'Success',
            self::STATUS_FAILED => 'Failed',
            self::STATUS_PARTIAL => 'Partial',
        ];
    }
}
