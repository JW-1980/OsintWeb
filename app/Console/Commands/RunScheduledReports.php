<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\ExecuteScheduledReportJob;
use App\Models\ScheduledReport;
use App\Services\ScheduledReportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Artisan command to check and run due scheduled reports
 *
 * This command should be run via the Laravel scheduler every minute
 * to check for reports that are due for execution.
 *
 * Usage:
 *   php artisan reports:run           - Check and queue due reports
 *   php artisan reports:run --sync    - Run reports synchronously (for testing)
 *   php artisan reports:run --dry-run - Show what would run without executing
 */
class RunScheduledReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:run
        {--sync : Run reports synchronously instead of queuing}
        {--dry-run : Show what would run without executing}
        {--report= : Run a specific report by ID or UUID}
        {--force : Force run even if report is inactive}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and execute scheduled reports that are due';

    /**
     * Execute the console command.
     *
     * @param ScheduledReportService $reportService
     * @return int
     */
    public function handle(ScheduledReportService $reportService): int
    {
        $this->info('Checking for scheduled reports due for execution...');
        $this->newLine();

        // Handle specific report execution
        if ($reportId = $this->option('report')) {
            return $this->runSpecificReport($reportId, $reportService);
        }

        // Get due reports
        $dueReports = $reportService->getDueReports();

        if ($dueReports->isEmpty()) {
            $this->info('No reports are due for execution.');
            return self::SUCCESS;
        }

        $this->info("Found {$dueReports->count()} report(s) due for execution.");
        $this->newLine();

        // Display report list
        $this->table(
            ['ID', 'Name', 'Type', 'Schedule', 'Next Run', 'Last Status'],
            $dueReports->map(fn($r) => [
                $r->id,
                $r->name,
                $r->report_type,
                $r->schedule_type,
                $r->next_run_at?->format('Y-m-d H:i:s'),
                $r->last_run_status ?? 'Never run',
            ])
        );

        // Dry run mode
        if ($this->option('dry-run')) {
            $this->newLine();
            $this->warn('Dry run mode - no reports were executed.');
            return self::SUCCESS;
        }

        $this->newLine();
        $this->info('Executing reports...');
        $this->newLine();

        $successCount = 0;
        $failureCount = 0;

        foreach ($dueReports as $report) {
            $this->line("Processing: {$report->name} (ID: {$report->id})");

            try {
                if ($this->option('sync')) {
                    // Synchronous execution (for testing/debugging)
                    $run = $reportService->executeReport($report->id);
                    $this->info("  - Completed: {$run->status} ({$run->execution_time_ms}ms)");
                } else {
                    // Queue for async execution
                    ExecuteScheduledReportJob::dispatch($report);
                    $this->info("  - Queued for execution");
                }

                $successCount++;

            } catch (\Exception $e) {
                $this->error("  - Failed: {$e->getMessage()}");

                Log::error('Failed to process scheduled report', [
                    'report_id' => $report->id,
                    'error' => $e->getMessage(),
                ]);

                $failureCount++;
            }
        }

        $this->newLine();
        $this->info("Processing complete: {$successCount} succeeded, {$failureCount} failed");

        // Log summary
        Log::info('Scheduled reports processing complete', [
            'total_due' => $dueReports->count(),
            'success' => $successCount,
            'failure' => $failureCount,
            'mode' => $this->option('sync') ? 'sync' : 'async',
        ]);

        return $failureCount > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * Run a specific report by ID or UUID
     *
     * @param string $reportId
     * @param ScheduledReportService $reportService
     * @return int
     */
    protected function runSpecificReport(string $reportId, ScheduledReportService $reportService): int
    {
        // Find report by ID or UUID
        $report = is_numeric($reportId)
            ? ScheduledReport::find($reportId)
            : ScheduledReport::where('uuid', $reportId)->first();

        if (!$report) {
            $this->error("Report not found: {$reportId}");
            return self::FAILURE;
        }

        $this->info("Running specific report: {$report->name} (ID: {$report->id})");
        $this->newLine();

        // Check if active
        if (!$report->is_active && !$this->option('force')) {
            $this->warn('Report is inactive. Use --force to run anyway.');
            return self::FAILURE;
        }

        // Dry run mode
        if ($this->option('dry-run')) {
            $this->table(
                ['Property', 'Value'],
                [
                    ['ID', $report->id],
                    ['Name', $report->name],
                    ['Type', $report->report_type],
                    ['Schedule', $report->schedule_type],
                    ['Active', $report->is_active ? 'Yes' : 'No'],
                    ['Recipients', implode(', ', $report->recipients ?? [])],
                    ['Formats', implode(', ', $report->export_formats ?? [])],
                    ['Last Run', $report->last_run_at?->format('Y-m-d H:i:s') ?? 'Never'],
                ]
            );
            $this->newLine();
            $this->warn('Dry run mode - report was not executed.');
            return self::SUCCESS;
        }

        try {
            if ($this->option('sync')) {
                $this->info('Executing synchronously...');
                $run = $reportService->runNow($report->id);
                $this->newLine();
                $this->info("Execution complete!");
                $this->table(
                    ['Metric', 'Value'],
                    [
                        ['Status', $run->status],
                        ['Duration', $run->duration ?? 'N/A'],
                        ['Events Included', $run->events_included],
                        ['Total Records', $run->total_records],
                        ['Files Generated', count($run->generated_files ?? [])],
                        ['Recipients Notified', count($run->recipients_notified ?? [])],
                    ]
                );
            } else {
                ExecuteScheduledReportJob::dispatch($report, isManualTrigger: true);
                $this->info('Report queued for execution.');
            }

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error("Execution failed: {$e->getMessage()}");

            Log::error('Manual report execution failed', [
                'report_id' => $report->id,
                'error' => $e->getMessage(),
            ]);

            return self::FAILURE;
        }
    }
}
