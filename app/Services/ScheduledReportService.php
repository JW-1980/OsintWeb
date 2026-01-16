<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Event;
use App\Models\ScheduledReport;
use App\Models\ScheduledReportRun;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ScheduledReportService
{
    /**
     * Create a new scheduled report
     *
     * @param array $data Report configuration data
     * @param User $user User creating the report
     * @return ScheduledReport
     */
    public function create(array $data, User $user): ScheduledReport
    {
        $data['uuid'] = Str::uuid()->toString();
        $data['created_by'] = $user->id;

        // Calculate the next run time
        $nextRun = $this->calculateNextRun(
            $data['schedule_type'],
            $data['schedule_config'] ?? [],
            $data['timezone'] ?? 'UTC'
        );
        $data['next_run_at'] = $nextRun;

        $report = ScheduledReport::create($data);

        Log::info('Scheduled report created', [
            'report_id' => $report->id,
            'report_type' => $report->report_type,
            'schedule_type' => $report->schedule_type,
            'next_run_at' => $nextRun?->toIso8601String(),
            'created_by' => $user->id,
        ]);

        return $report;
    }

    /**
     * Update an existing scheduled report
     *
     * @param ScheduledReport $report
     * @param array $data
     * @return ScheduledReport
     */
    public function update(ScheduledReport $report, array $data): ScheduledReport
    {
        // Recalculate next run if schedule changed
        if (
            isset($data['schedule_type']) ||
            isset($data['schedule_config']) ||
            isset($data['timezone'])
        ) {
            $scheduleType = $data['schedule_type'] ?? $report->schedule_type;
            $scheduleConfig = $data['schedule_config'] ?? $report->schedule_config ?? [];
            $timezone = $data['timezone'] ?? $report->timezone;

            $data['next_run_at'] = $this->calculateNextRun($scheduleType, $scheduleConfig, $timezone);
        }

        $report->update($data);

        Log::info('Scheduled report updated', [
            'report_id' => $report->id,
            'changes' => array_keys($data),
        ]);

        return $report->fresh();
    }

    /**
     * Calculate the next run time based on schedule configuration
     *
     * @param string $scheduleType
     * @param array $config
     * @param string $timezone
     * @return Carbon|null
     */
    public function calculateNextRun(string $scheduleType, array $config, string $timezone): ?Carbon
    {
        $now = Carbon::now($timezone);
        $timeUtc = $config['time_utc'] ?? '08:00';
        [$hour, $minute] = array_map('intval', explode(':', $timeUtc));

        $baseTime = $now->copy()->setTime($hour, $minute, 0);

        // If base time is in the past for today, start from tomorrow
        if ($baseTime->isPast()) {
            $baseTime->addDay();
        }

        return match ($scheduleType) {
            ScheduledReport::SCHEDULE_DAILY => $this->calculateDailyNextRun($baseTime),
            ScheduledReport::SCHEDULE_WEEKLY => $this->calculateWeeklyNextRun($baseTime, $config),
            ScheduledReport::SCHEDULE_BIWEEKLY => $this->calculateBiweeklyNextRun($baseTime, $config),
            ScheduledReport::SCHEDULE_MONTHLY => $this->calculateMonthlyNextRun($baseTime, $config),
            ScheduledReport::SCHEDULE_QUARTERLY => $this->calculateQuarterlyNextRun($baseTime, $config),
            default => null,
        };
    }

    /**
     * Calculate next daily run
     */
    protected function calculateDailyNextRun(Carbon $baseTime): Carbon
    {
        return $baseTime;
    }

    /**
     * Calculate next weekly run
     */
    protected function calculateWeeklyNextRun(Carbon $baseTime, array $config): Carbon
    {
        $dayOfWeek = $config['day_of_week'] ?? 1; // Default to Monday

        $nextRun = $baseTime->copy();

        // Move to the next occurrence of the specified day
        while ($nextRun->dayOfWeek !== $dayOfWeek) {
            $nextRun->addDay();
        }

        return $nextRun;
    }

    /**
     * Calculate next biweekly run
     */
    protected function calculateBiweeklyNextRun(Carbon $baseTime, array $config): Carbon
    {
        $dayOfWeek = $config['day_of_week'] ?? 1;

        $nextRun = $this->calculateWeeklyNextRun($baseTime, $config);

        // Check if we need to skip a week (biweekly logic)
        // We use week number to determine odd/even weeks
        $referenceWeek = $config['reference_week'] ?? 1;
        $currentWeek = $nextRun->weekOfYear;

        if (($currentWeek - $referenceWeek) % 2 !== 0) {
            $nextRun->addWeek();
        }

        return $nextRun;
    }

    /**
     * Calculate next monthly run
     */
    protected function calculateMonthlyNextRun(Carbon $baseTime, array $config): Carbon
    {
        $dayOfMonth = $config['day_of_month'] ?? 1;
        $nextRun = $baseTime->copy();

        // If we're past the target day this month, move to next month
        if ($nextRun->day > $dayOfMonth) {
            $nextRun->addMonth();
        }

        // Set the day, handling months with fewer days
        $maxDay = $nextRun->daysInMonth;
        $targetDay = min($dayOfMonth, $maxDay);
        $nextRun->setDay($targetDay);

        return $nextRun;
    }

    /**
     * Calculate next quarterly run
     */
    protected function calculateQuarterlyNextRun(Carbon $baseTime, array $config): Carbon
    {
        $dayOfMonth = $config['day_of_month'] ?? 1;
        $nextRun = $baseTime->copy();

        // Get the first month of the next quarter
        $currentQuarter = $nextRun->quarter;
        $quarterStartMonth = (($currentQuarter - 1) * 3) + 1;

        // If we're in the first month of quarter and before the day, use this quarter
        if ($nextRun->month === $quarterStartMonth && $nextRun->day <= $dayOfMonth) {
            $nextRun->setDay(min($dayOfMonth, $nextRun->daysInMonth));
        } else {
            // Move to next quarter
            $nextRun->addQuarterNoOverflow();
            $nextRun->firstOfQuarter();
            $nextRun->setDay(min($dayOfMonth, $nextRun->daysInMonth));
        }

        return $nextRun;
    }

    /**
     * Execute a scheduled report
     *
     * @param int $scheduledReportId
     * @return ScheduledReportRun
     */
    public function executeReport(int $scheduledReportId): ScheduledReportRun
    {
        $report = ScheduledReport::findOrFail($scheduledReportId);

        // Create run record
        $run = ScheduledReportRun::create([
            'uuid' => Str::uuid()->toString(),
            'scheduled_report_id' => $report->id,
            'started_at' => now(),
            'status' => ScheduledReportRun::STATUS_RUNNING,
            'parameters_snapshot' => [
                'filters' => $report->filters,
                'date_range_type' => $report->date_range_type,
                'custom_date_range' => $report->custom_date_range,
                'export_formats' => $report->export_formats,
                'recipients' => $report->recipients,
            ],
        ]);

        Log::info('Executing scheduled report', [
            'report_id' => $report->id,
            'run_id' => $run->id,
            'report_type' => $report->report_type,
        ]);

        try {
            // Generate the report content
            $result = $this->generateReport($report, $run);

            // Deliver the report
            $deliveryResult = $this->deliverReport($run, $result['files'], $report);

            // Update run statistics
            $run->markAsSuccess([
                'generated_files' => $result['files'],
                'events_included' => $result['events_count'] ?? 0,
                'total_records' => $result['total_records'] ?? 0,
                'sitrep_id' => $result['sitrep_id'] ?? null,
            ]);

            // Update report statistics
            $report->recordSuccess();

            // Calculate next run
            $nextRun = $this->calculateNextRun(
                $report->schedule_type,
                $report->schedule_config ?? [],
                $report->timezone
            );
            $report->update(['next_run_at' => $nextRun]);

            Log::info('Scheduled report executed successfully', [
                'report_id' => $report->id,
                'run_id' => $run->id,
                'execution_time_ms' => $run->execution_time_ms,
                'next_run_at' => $nextRun?->toIso8601String(),
            ]);

            return $run->fresh();

        } catch (\Exception $e) {
            $run->markAsFailed($e->getMessage());
            $report->recordFailure($e->getMessage());

            Log::error('Scheduled report execution failed', [
                'report_id' => $report->id,
                'run_id' => $run->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Generate the report content
     *
     * @param ScheduledReport $report
     * @param ScheduledReportRun $run
     * @return array
     */
    public function generateReport(ScheduledReport $report, ScheduledReportRun $run): array
    {
        $dateRange = $report->getDateRange();
        $filters = $report->filters ?? [];

        // Build query based on report type
        $data = match ($report->report_type) {
            ScheduledReport::TYPE_EVENT_DIGEST => $this->generateEventDigest($dateRange, $filters),
            ScheduledReport::TYPE_EQUIPMENT_UPDATE => $this->generateEquipmentUpdate($dateRange, $filters),
            ScheduledReport::TYPE_TERRITORIAL_SUMMARY => $this->generateTerritorialSummary($dateRange, $filters),
            ScheduledReport::TYPE_ACTOR_ACTIVITY => $this->generateActorActivity($dateRange, $filters),
            ScheduledReport::TYPE_SITREP => $this->generateSitrep($report, $dateRange, $filters),
            ScheduledReport::TYPE_CUSTOM_QUERY => $this->generateCustomQuery($dateRange, $filters),
            default => ['data' => [], 'events_count' => 0, 'total_records' => 0],
        };

        // Generate files in requested formats
        $files = [];
        foreach ($report->export_formats as $format) {
            $filePath = $this->exportToFormat($report, $data, $format);
            if ($filePath) {
                $files[$format] = $filePath;
            }
        }

        return [
            'files' => $files,
            'events_count' => $data['events_count'] ?? 0,
            'total_records' => $data['total_records'] ?? 0,
            'sitrep_id' => $data['sitrep_id'] ?? null,
        ];
    }

    /**
     * Generate event digest report
     */
    protected function generateEventDigest(array $dateRange, array $filters): array
    {
        $query = Event::query()
            ->whereBetween('occurred_at', [$dateRange['start'], $dateRange['end']])
            ->with(['eventType', 'user', 'actors']);

        // Apply filters
        if (!empty($filters['event_types'])) {
            $query->whereIn('event_type_id', $filters['event_types']);
        }

        if (!empty($filters['regions'])) {
            // Filter by region/location
            $query->where(function ($q) use ($filters) {
                foreach ($filters['regions'] as $region) {
                    $q->orWhere('location_name', 'like', "%{$region}%");
                }
            });
        }

        if (!empty($filters['statuses'])) {
            $query->whereIn('status', $filters['statuses']);
        }

        $events = $query->orderBy('occurred_at', 'desc')->get();

        return [
            'data' => [
                'title' => 'Event Digest',
                'period' => [
                    'start' => $dateRange['start']->toIso8601String(),
                    'end' => $dateRange['end']->toIso8601String(),
                ],
                'summary' => [
                    'total_events' => $events->count(),
                    'by_type' => $events->groupBy('eventType.name')->map->count(),
                    'by_status' => $events->groupBy('status')->map->count(),
                ],
                'events' => $events->toArray(),
            ],
            'events_count' => $events->count(),
            'total_records' => $events->count(),
        ];
    }

    /**
     * Generate equipment update report
     */
    protected function generateEquipmentUpdate(array $dateRange, array $filters): array
    {
        $query = \App\Models\MilitaryEquipment::query()
            ->whereBetween('updated_at', [$dateRange['start'], $dateRange['end']])
            ->with(['category', 'countries']);

        if (!empty($filters['equipment_types'])) {
            $query->whereIn('category_id', $filters['equipment_types']);
        }

        $equipment = $query->orderBy('updated_at', 'desc')->get();

        return [
            'data' => [
                'title' => 'Equipment Update',
                'period' => [
                    'start' => $dateRange['start']->toIso8601String(),
                    'end' => $dateRange['end']->toIso8601String(),
                ],
                'summary' => [
                    'total_updates' => $equipment->count(),
                    'by_category' => $equipment->groupBy('category.name')->map->count(),
                ],
                'equipment' => $equipment->toArray(),
            ],
            'events_count' => 0,
            'total_records' => $equipment->count(),
        ];
    }

    /**
     * Generate territorial summary report
     */
    protected function generateTerritorialSummary(array $dateRange, array $filters): array
    {
        $query = \App\Models\ControlZone::query()
            ->whereBetween('updated_at', [$dateRange['start'], $dateRange['end']])
            ->with(['faction']);

        $zones = $query->orderBy('updated_at', 'desc')->get();

        return [
            'data' => [
                'title' => 'Territorial Summary',
                'period' => [
                    'start' => $dateRange['start']->toIso8601String(),
                    'end' => $dateRange['end']->toIso8601String(),
                ],
                'summary' => [
                    'total_changes' => $zones->count(),
                    'by_faction' => $zones->groupBy('faction.name')->map->count(),
                ],
                'zones' => $zones->toArray(),
            ],
            'events_count' => 0,
            'total_records' => $zones->count(),
        ];
    }

    /**
     * Generate actor activity report
     */
    protected function generateActorActivity(array $dateRange, array $filters): array
    {
        $actorQuery = \App\Models\Actor::query()
            ->with(['events' => function ($q) use ($dateRange) {
                $q->whereBetween('occurred_at', [$dateRange['start'], $dateRange['end']]);
            }]);

        if (!empty($filters['actors'])) {
            $actorQuery->whereIn('id', $filters['actors']);
        }

        $actors = $actorQuery->get();

        $actorData = $actors->map(function ($actor) {
            return [
                'id' => $actor->id,
                'name' => $actor->name,
                'type' => $actor->type,
                'event_count' => $actor->events->count(),
                'events' => $actor->events->toArray(),
            ];
        })->filter(fn($a) => $a['event_count'] > 0)->values();

        return [
            'data' => [
                'title' => 'Actor Activity',
                'period' => [
                    'start' => $dateRange['start']->toIso8601String(),
                    'end' => $dateRange['end']->toIso8601String(),
                ],
                'summary' => [
                    'active_actors' => $actorData->count(),
                    'total_events' => $actorData->sum('event_count'),
                ],
                'actors' => $actorData->toArray(),
            ],
            'events_count' => $actorData->sum('event_count'),
            'total_records' => $actorData->count(),
        ];
    }

    /**
     * Generate sitrep report
     */
    protected function generateSitrep(ScheduledReport $report, array $dateRange, array $filters): array
    {
        // This would integrate with a SitrepService if available
        $eventData = $this->generateEventDigest($dateRange, $filters);

        return [
            'data' => [
                'title' => 'Situation Report - ' . now()->format('Y-m-d'),
                'period' => $eventData['data']['period'],
                'executive_summary' => $this->generateExecutiveSummary($eventData['data']),
                'details' => $eventData['data'],
            ],
            'events_count' => $eventData['events_count'],
            'total_records' => $eventData['total_records'],
            'sitrep_id' => null, // Would be set if integrated with Sitrep model
        ];
    }

    /**
     * Generate custom query report
     */
    protected function generateCustomQuery(array $dateRange, array $filters): array
    {
        // Custom query would be defined in filters['query']
        $query = $filters['query'] ?? null;

        if (!$query) {
            return [
                'data' => ['title' => 'Custom Query', 'results' => []],
                'events_count' => 0,
                'total_records' => 0,
            ];
        }

        // For security, only support predefined query types
        $results = match ($query['type'] ?? '') {
            'events' => $this->generateEventDigest($dateRange, $filters),
            'equipment' => $this->generateEquipmentUpdate($dateRange, $filters),
            'actors' => $this->generateActorActivity($dateRange, $filters),
            default => ['data' => [], 'events_count' => 0, 'total_records' => 0],
        };

        return $results;
    }

    /**
     * Generate executive summary from report data
     */
    protected function generateExecutiveSummary(array $data): string
    {
        $totalEvents = $data['summary']['total_events'] ?? 0;
        $period = $data['period'] ?? [];

        $start = isset($period['start']) ? Carbon::parse($period['start'])->format('M j, Y') : 'N/A';
        $end = isset($period['end']) ? Carbon::parse($period['end'])->format('M j, Y') : 'N/A';

        return "This report covers the period from {$start} to {$end}. "
            . "A total of {$totalEvents} events were recorded during this period.";
    }

    /**
     * Export report data to specified format
     */
    protected function exportToFormat(ScheduledReport $report, array $data, string $format): ?string
    {
        $filename = sprintf(
            'reports/%s/%s_%s.%s',
            now()->format('Y/m/d'),
            Str::slug($report->name),
            now()->format('YmdHis'),
            $format
        );

        try {
            $content = match ($format) {
                ScheduledReport::FORMAT_JSON => json_encode($data['data'], JSON_PRETTY_PRINT),
                ScheduledReport::FORMAT_HTML => $this->renderHtml($report, $data['data']),
                ScheduledReport::FORMAT_PDF => $this->renderPdf($report, $data['data']),
                ScheduledReport::FORMAT_DOCX => $this->renderDocx($report, $data['data']),
                default => null,
            };

            if ($content) {
                Storage::disk('local')->put($filename, $content);
                return $filename;
            }

            return null;

        } catch (\Exception $e) {
            Log::error('Failed to export report to format', [
                'report_id' => $report->id,
                'format' => $format,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Render report as HTML
     */
    protected function renderHtml(ScheduledReport $report, array $data): string
    {
        // Basic HTML rendering - in production, use Blade templates
        $title = $data['title'] ?? $report->name;
        $json = json_encode($data, JSON_PRETTY_PRINT);
        $generatedAt = $data['period']['end'] ?? now()->toIso8601String();

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>{$title}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        h1 { color: #333; }
        pre { background: #f5f5f5; padding: 20px; overflow-x: auto; }
        .meta { color: #666; margin-bottom: 20px; }
    </style>
</head>
<body>
    <h1>{$title}</h1>
    <div class="meta">Generated: {$generatedAt}</div>
    <pre>{$json}</pre>
</body>
</html>
HTML;
    }

    /**
     * Render report as PDF
     */
    protected function renderPdf(ScheduledReport $report, array $data): string
    {
        // In production, use a PDF library like DomPDF or Snappy
        // For now, return HTML content that could be converted
        return $this->renderHtml($report, $data);
    }

    /**
     * Render report as DOCX
     */
    protected function renderDocx(ScheduledReport $report, array $data): string
    {
        // In production, use PhpWord library
        // For now, return JSON
        return json_encode($data, JSON_PRETTY_PRINT);
    }

    /**
     * Deliver the report via configured method
     *
     * @param ScheduledReportRun $run
     * @param array $files
     * @param ScheduledReport $report
     * @return array
     */
    public function deliverReport(ScheduledReportRun $run, array $files, ScheduledReport $report): array
    {
        $results = [];

        switch ($report->delivery_method) {
            case ScheduledReport::DELIVERY_EMAIL:
                $results = $this->sendEmail($report->recipients, $files, $report, $run);
                break;

            case ScheduledReport::DELIVERY_WEBHOOK:
                if ($report->webhook_url) {
                    $results = $this->sendWebhook($report->webhook_url, [
                        'report_id' => $report->id,
                        'run_id' => $run->id,
                        'files' => $files,
                        'generated_at' => now()->toIso8601String(),
                    ], $run);
                }
                break;

            case ScheduledReport::DELIVERY_API_PUSH:
                // API push implementation would go here
                Log::info('API push delivery not implemented', ['report_id' => $report->id]);
                break;

            case ScheduledReport::DELIVERY_STORAGE:
                // Files are already stored, just log
                Log::info('Report stored', [
                    'report_id' => $report->id,
                    'files' => $files,
                ]);
                $results['storage'] = ['status' => 'success'];
                break;
        }

        return $results;
    }

    /**
     * Send report via email
     *
     * @param array $recipients
     * @param array $files
     * @param ScheduledReport $report
     * @param ScheduledReportRun $run
     * @return array
     */
    public function sendEmail(array $recipients, array $files, ScheduledReport $report, ScheduledReportRun $run): array
    {
        $results = [];

        foreach ($recipients as $recipient) {
            try {
                // Build attachments
                $attachments = [];
                if ($report->include_attachments) {
                    foreach ($files as $format => $path) {
                        if (Storage::disk('local')->exists($path)) {
                            $attachments[] = [
                                'path' => Storage::disk('local')->path($path),
                                'name' => basename($path),
                            ];
                        }
                    }
                }

                // Send email (using Laravel's Mail facade)
                Mail::send([], [], function ($message) use ($recipient, $report, $attachments, $files) {
                    $message->to($recipient)
                        ->subject("Scheduled Report: {$report->name}")
                        ->html($this->buildEmailBody($report, $files));

                    foreach ($attachments as $attachment) {
                        $message->attach($attachment['path'], ['as' => $attachment['name']]);
                    }
                });

                $run->recordDeliveryStatus($recipient, 'success');
                $results[$recipient] = ['status' => 'success'];

                Log::info('Report email sent', [
                    'report_id' => $report->id,
                    'recipient' => $recipient,
                ]);

            } catch (\Exception $e) {
                $run->recordDeliveryStatus($recipient, 'failed', $e->getMessage());
                $results[$recipient] = ['status' => 'failed', 'error' => $e->getMessage()];

                Log::error('Failed to send report email', [
                    'report_id' => $report->id,
                    'recipient' => $recipient,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $results;
    }

    /**
     * Build email body HTML
     */
    protected function buildEmailBody(ScheduledReport $report, array $files): string
    {
        $fileList = collect($files)->map(fn($path, $format) => strtoupper($format))->implode(', ');

        return <<<HTML
<html>
<body style="font-family: Arial, sans-serif;">
    <h2>Scheduled Report: {$report->name}</h2>
    <p>Your scheduled report has been generated.</p>
    <p><strong>Report Type:</strong> {$report->report_type}</p>
    <p><strong>Generated:</strong> {$report->last_run_at?->format('Y-m-d H:i:s T')}</p>
    <p><strong>Formats:</strong> {$fileList}</p>
    <hr>
    <p style="color: #666; font-size: 12px;">
        This is an automated report from OsintWeb.
        To manage your scheduled reports, please visit your dashboard.
    </p>
</body>
</html>
HTML;
    }

    /**
     * Send report via webhook
     *
     * @param string $url
     * @param array $data
     * @param ScheduledReportRun $run
     * @return array
     */
    public function sendWebhook(string $url, array $data, ScheduledReportRun $run): array
    {
        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'X-OsintWeb-Event' => 'scheduled_report.completed',
                    'X-OsintWeb-Signature' => $this->generateWebhookSignature($data),
                ])
                ->post($url, $data);

            if ($response->successful()) {
                $run->recordDeliveryStatus($url, 'success');

                Log::info('Webhook delivered successfully', [
                    'url' => $url,
                    'status' => $response->status(),
                ]);

                return ['status' => 'success', 'http_status' => $response->status()];
            }

            $error = "HTTP {$response->status()}: {$response->body()}";
            $run->recordDeliveryStatus($url, 'failed', $error);

            Log::warning('Webhook delivery failed', [
                'url' => $url,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return ['status' => 'failed', 'error' => $error];

        } catch (\Exception $e) {
            $run->recordDeliveryStatus($url, 'failed', $e->getMessage());

            Log::error('Webhook delivery exception', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);

            return ['status' => 'failed', 'error' => $e->getMessage()];
        }
    }

    /**
     * Generate webhook signature for verification
     */
    protected function generateWebhookSignature(array $data): string
    {
        $secret = config('services.webhook_secret', config('app.key'));
        return hash_hmac('sha256', json_encode($data), $secret);
    }

    /**
     * Get reports that are due for execution
     *
     * @return Collection<ScheduledReport>
     */
    public function getDueReports(): Collection
    {
        return ScheduledReport::due()->get();
    }

    /**
     * Update run statistics for a report
     *
     * @param int $scheduledReportId
     * @param bool $success
     * @return void
     */
    public function updateRunStats(int $scheduledReportId, bool $success): void
    {
        $report = ScheduledReport::findOrFail($scheduledReportId);

        if ($success) {
            $report->recordSuccess();
        } else {
            $report->recordFailure('Unknown error');
        }
    }

    /**
     * Pause a scheduled report
     *
     * @param int $id
     * @return ScheduledReport
     */
    public function pauseReport(int $id): ScheduledReport
    {
        $report = ScheduledReport::findOrFail($id);
        $report->pause();

        Log::info('Scheduled report paused', ['report_id' => $id]);

        return $report->fresh();
    }

    /**
     * Resume a scheduled report
     *
     * @param int $id
     * @return ScheduledReport
     */
    public function resumeReport(int $id): ScheduledReport
    {
        $report = ScheduledReport::findOrFail($id);
        $report->resume();

        // Recalculate next run time
        $nextRun = $this->calculateNextRun(
            $report->schedule_type,
            $report->schedule_config ?? [],
            $report->timezone
        );
        $report->update(['next_run_at' => $nextRun]);

        Log::info('Scheduled report resumed', [
            'report_id' => $id,
            'next_run_at' => $nextRun?->toIso8601String(),
        ]);

        return $report->fresh();
    }

    /**
     * Run a report immediately (manual trigger)
     *
     * @param int $id
     * @return ScheduledReportRun
     */
    public function runNow(int $id): ScheduledReportRun
    {
        Log::info('Manual report execution triggered', ['report_id' => $id]);

        return $this->executeReport($id);
    }

    /**
     * Preview what would be generated for a report
     *
     * @param ScheduledReport $report
     * @return array
     */
    public function preview(ScheduledReport $report): array
    {
        $dateRange = $report->getDateRange();
        $filters = $report->filters ?? [];

        // Generate preview data without creating files
        $data = match ($report->report_type) {
            ScheduledReport::TYPE_EVENT_DIGEST => $this->generateEventDigest($dateRange, $filters),
            ScheduledReport::TYPE_EQUIPMENT_UPDATE => $this->generateEquipmentUpdate($dateRange, $filters),
            ScheduledReport::TYPE_TERRITORIAL_SUMMARY => $this->generateTerritorialSummary($dateRange, $filters),
            ScheduledReport::TYPE_ACTOR_ACTIVITY => $this->generateActorActivity($dateRange, $filters),
            ScheduledReport::TYPE_SITREP => $this->generateSitrep($report, $dateRange, $filters),
            ScheduledReport::TYPE_CUSTOM_QUERY => $this->generateCustomQuery($dateRange, $filters),
            default => ['data' => [], 'events_count' => 0, 'total_records' => 0],
        };

        return [
            'report_id' => $report->id,
            'report_name' => $report->name,
            'report_type' => $report->report_type,
            'date_range' => [
                'start' => $dateRange['start']->toIso8601String(),
                'end' => $dateRange['end']->toIso8601String(),
            ],
            'preview_data' => $data['data'],
            'events_count' => $data['events_count'],
            'total_records' => $data['total_records'],
            'export_formats' => $report->export_formats,
            'recipients' => $report->recipients,
        ];
    }

    /**
     * Get scheduling statistics
     *
     * @return array
     */
    public function getStats(): array
    {
        $reports = ScheduledReport::query();

        return [
            'total_reports' => $reports->count(),
            'active_reports' => ScheduledReport::active()->count(),
            'inactive_reports' => ScheduledReport::inactive()->count(),
            'by_type' => ScheduledReport::selectRaw('report_type, COUNT(*) as count')
                ->groupBy('report_type')
                ->pluck('count', 'report_type'),
            'by_schedule' => ScheduledReport::selectRaw('schedule_type, COUNT(*) as count')
                ->groupBy('schedule_type')
                ->pluck('count', 'schedule_type'),
            'runs_last_24h' => ScheduledReportRun::where('started_at', '>=', now()->subHours(24))->count(),
            'success_rate_last_24h' => $this->calculateSuccessRate(24),
            'upcoming_runs' => ScheduledReport::active()
                ->whereNotNull('next_run_at')
                ->orderBy('next_run_at')
                ->limit(5)
                ->get(['id', 'uuid', 'name', 'next_run_at']),
        ];
    }

    /**
     * Calculate success rate for recent runs
     */
    protected function calculateSuccessRate(int $hours): float
    {
        $since = now()->subHours($hours);
        $total = ScheduledReportRun::where('started_at', '>=', $since)->count();

        if ($total === 0) {
            return 100.0;
        }

        $successful = ScheduledReportRun::where('started_at', '>=', $since)
            ->where('status', ScheduledReportRun::STATUS_SUCCESS)
            ->count();

        return round(($successful / $total) * 100, 2);
    }
}
