<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\ScheduledReport;
use App\Services\ScheduledReportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Job to execute a scheduled report
 *
 * This job handles the asynchronous execution of scheduled reports,
 * including report generation, export to requested formats, and delivery
 * via configured methods (email, webhook, etc.).
 */
class ExecuteScheduledReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public int $tries = 3;

    /**
     * The maximum number of seconds the job should run.
     *
     * @var int
     */
    public int $timeout = 600; // 10 minutes

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var array
     */
    public array $backoff = [60, 180, 300];

    /**
     * Create a new job instance.
     *
     * @param ScheduledReport $scheduledReport
     * @param bool $isManualTrigger Whether this was manually triggered
     */
    public function __construct(
        protected ScheduledReport $scheduledReport,
        protected bool $isManualTrigger = false
    ) {}

    /**
     * Execute the job.
     *
     * @param ScheduledReportService $reportService
     * @return void
     */
    public function handle(ScheduledReportService $reportService): void
    {
        $reportId = $this->scheduledReport->id;
        $reportName = $this->scheduledReport->name;

        Log::info('Starting scheduled report execution job', [
            'report_id' => $reportId,
            'report_name' => $reportName,
            'report_type' => $this->scheduledReport->report_type,
            'is_manual_trigger' => $this->isManualTrigger,
            'attempt' => $this->attempts(),
        ]);

        try {
            // Check if report is still active (unless manually triggered)
            if (!$this->isManualTrigger && !$this->scheduledReport->is_active) {
                Log::info('Skipping execution - report is inactive', [
                    'report_id' => $reportId,
                ]);
                return;
            }

            // Execute the report
            $run = $reportService->executeReport($reportId);

            Log::info('Scheduled report execution completed', [
                'report_id' => $reportId,
                'run_id' => $run->id,
                'status' => $run->status,
                'execution_time_ms' => $run->execution_time_ms,
                'events_included' => $run->events_included,
                'total_records' => $run->total_records,
            ]);

        } catch (\Exception $e) {
            Log::error('Scheduled report execution failed', [
                'report_id' => $reportId,
                'report_name' => $reportName,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'attempt' => $this->attempts(),
            ]);

            // Only throw to trigger retry if we have attempts remaining
            if ($this->attempts() < $this->tries) {
                throw $e;
            }

            // Final failure - error has already been recorded by the service
        }
    }

    /**
     * Handle a job failure.
     *
     * @param \Throwable|null $exception
     * @return void
     */
    public function failed(?\Throwable $exception): void
    {
        Log::error('Scheduled report job failed permanently', [
            'report_id' => $this->scheduledReport->id,
            'report_name' => $this->scheduledReport->name,
            'error' => $exception?->getMessage(),
        ]);

        // The service already records failure, but we can add notification here
        // For example, notify admins of repeated failures
        $this->scheduledReport->refresh();

        if ($this->scheduledReport->failure_count >= 3) {
            Log::warning('Scheduled report has failed 3+ times', [
                'report_id' => $this->scheduledReport->id,
                'failure_count' => $this->scheduledReport->failure_count,
            ]);

            // Could trigger admin notification here
        }
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array<int, string>
     */
    public function tags(): array
    {
        return [
            'scheduled_report',
            'report:' . $this->scheduledReport->id,
            'type:' . $this->scheduledReport->report_type,
            'user:' . $this->scheduledReport->created_by,
        ];
    }

    /**
     * Get the unique ID for the job.
     *
     * @return string
     */
    public function uniqueId(): string
    {
        return 'scheduled_report_' . $this->scheduledReport->id;
    }

    /**
     * Determine the time at which the job should timeout.
     *
     * @return \DateTime
     */
    public function retryUntil(): \DateTime
    {
        return now()->addHours(2);
    }

    /**
     * Calculate the number of seconds to wait before retrying the job.
     *
     * @return int
     */
    public function retryAfter(): int
    {
        $attempt = $this->attempts();
        return $this->backoff[$attempt - 1] ?? 300;
    }

    /**
     * Determine if the job should be dispatched after all database transactions have committed.
     *
     * @var bool
     */
    public bool $afterCommit = true;

    /**
     * Get the middleware the job should pass through.
     *
     * @return array
     */
    public function middleware(): array
    {
        // Prevent concurrent execution of the same report
        return [
            new \Illuminate\Queue\Middleware\WithoutOverlapping($this->uniqueId()),
        ];
    }
}
