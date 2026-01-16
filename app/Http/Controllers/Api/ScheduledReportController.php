<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ExecuteScheduledReportJob;
use App\Models\ScheduledReport;
use App\Models\ScheduledReportRun;
use App\Services\ScheduledReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Scheduled Report Controller
 *
 * Manages scheduled report configurations and execution.
 */
class ScheduledReportController extends Controller
{
    public function __construct(
        protected ScheduledReportService $scheduledReportService
    ) {}

    /**
     * List all scheduled reports for the authenticated user
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = ScheduledReport::query()
            ->where('created_by', $request->user()->id)
            ->with(['creator', 'template']);

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN));
        }

        // Filter by report type
        if ($request->has('report_type')) {
            $query->where('report_type', $request->report_type);
        }

        // Filter by schedule type
        if ($request->has('schedule_type')) {
            $query->where('schedule_type', $request->schedule_type);
        }

        // Search by name
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDir = $request->get('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        $reports = $query->paginate($this->perPage);

        return $this->paginated($reports);
    }

    /**
     * Create a new scheduled report
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'report_type' => ['required', 'string', 'in:sitrep,event_digest,equipment_update,territorial_summary,actor_activity,custom_query'],
            'template_id' => ['nullable', 'integer', 'exists:sitrep_templates,id'],
            'schedule_type' => ['required', 'string', 'in:daily,weekly,biweekly,monthly,quarterly'],
            'schedule_config' => ['nullable', 'array'],
            'schedule_config.day_of_week' => ['nullable', 'integer', 'between:0,6'],
            'schedule_config.day_of_month' => ['nullable', 'integer', 'between:1,31'],
            'schedule_config.time_utc' => ['nullable', 'date_format:H:i'],
            'timezone' => ['nullable', 'string', 'timezone'],
            'filters' => ['nullable', 'array'],
            'filters.event_types' => ['nullable', 'array'],
            'filters.regions' => ['nullable', 'array'],
            'filters.actors' => ['nullable', 'array'],
            'filters.equipment' => ['nullable', 'array'],
            'date_range_type' => ['required', 'string', 'in:last_24h,last_7d,last_30d,last_quarter,custom'],
            'custom_date_range' => ['nullable', 'array', 'required_if:date_range_type,custom'],
            'custom_date_range.start_date' => ['nullable', 'date'],
            'custom_date_range.end_date' => ['nullable', 'date', 'after:custom_date_range.start_date'],
            'recipients' => ['required', 'array', 'min:1'],
            'recipients.*' => ['email'],
            'delivery_method' => ['required', 'string', 'in:email,webhook,api_push,storage'],
            'webhook_url' => ['nullable', 'url', 'required_if:delivery_method,webhook'],
            'export_formats' => ['required', 'array', 'min:1'],
            'export_formats.*' => ['string', 'in:pdf,docx,html,json'],
            'include_attachments' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $report = $this->scheduledReportService->create($validated, $request->user());

        return $this->success($report->load(['creator', 'template']), 201);
    }

    /**
     * Display a specific scheduled report
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function show(string $uuid): JsonResponse
    {
        $report = ScheduledReport::where('uuid', $uuid)
            ->with(['creator', 'template', 'runs' => function ($q) {
                $q->orderByDesc('started_at')->limit(10);
            }])
            ->firstOrFail();

        $this->authorize('view', $report);

        return $this->success($report);
    }

    /**
     * Update a scheduled report
     *
     * @param Request $request
     * @param string $uuid
     * @return JsonResponse
     */
    public function update(Request $request, string $uuid): JsonResponse
    {
        $report = ScheduledReport::where('uuid', $uuid)->firstOrFail();

        $this->authorize('update', $report);

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'report_type' => ['sometimes', 'string', 'in:sitrep,event_digest,equipment_update,territorial_summary,actor_activity,custom_query'],
            'template_id' => ['nullable', 'integer', 'exists:sitrep_templates,id'],
            'schedule_type' => ['sometimes', 'string', 'in:daily,weekly,biweekly,monthly,quarterly'],
            'schedule_config' => ['nullable', 'array'],
            'timezone' => ['nullable', 'string', 'timezone'],
            'filters' => ['nullable', 'array'],
            'date_range_type' => ['sometimes', 'string', 'in:last_24h,last_7d,last_30d,last_quarter,custom'],
            'custom_date_range' => ['nullable', 'array'],
            'recipients' => ['sometimes', 'array', 'min:1'],
            'recipients.*' => ['email'],
            'delivery_method' => ['sometimes', 'string', 'in:email,webhook,api_push,storage'],
            'webhook_url' => ['nullable', 'url'],
            'export_formats' => ['sometimes', 'array', 'min:1'],
            'export_formats.*' => ['string', 'in:pdf,docx,html,json'],
            'include_attachments' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $report = $this->scheduledReportService->update($report, $validated);

        return $this->success($report->load(['creator', 'template']));
    }

    /**
     * Delete a scheduled report
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function destroy(string $uuid): JsonResponse
    {
        $report = ScheduledReport::where('uuid', $uuid)->firstOrFail();

        $this->authorize('delete', $report);

        $report->delete();

        return $this->success(['message' => 'Scheduled report deleted successfully']);
    }

    /**
     * List execution history for a scheduled report
     *
     * @param Request $request
     * @param string $uuid
     * @return JsonResponse
     */
    public function runs(Request $request, string $uuid): JsonResponse
    {
        $report = ScheduledReport::where('uuid', $uuid)->firstOrFail();

        $this->authorize('view', $report);

        $query = $report->runs()
            ->orderByDesc('started_at');

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->has('from')) {
            $query->where('started_at', '>=', $request->from);
        }
        if ($request->has('to')) {
            $query->where('started_at', '<=', $request->to);
        }

        $runs = $query->paginate($this->perPage);

        return $this->paginated($runs);
    }

    /**
     * Trigger immediate execution of a scheduled report
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function runNow(string $uuid): JsonResponse
    {
        $report = ScheduledReport::where('uuid', $uuid)->firstOrFail();

        $this->authorize('update', $report);

        // Dispatch the job for async execution
        ExecuteScheduledReportJob::dispatch($report);

        return $this->success([
            'message' => 'Report execution has been queued',
            'report_id' => $report->id,
            'report_uuid' => $report->uuid,
        ]);
    }

    /**
     * Pause a scheduled report
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function pause(string $uuid): JsonResponse
    {
        $report = ScheduledReport::where('uuid', $uuid)->firstOrFail();

        $this->authorize('update', $report);

        $report = $this->scheduledReportService->pauseReport($report->id);

        return $this->success([
            'message' => 'Scheduled report paused',
            'report' => $report,
        ]);
    }

    /**
     * Resume a scheduled report
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function resume(string $uuid): JsonResponse
    {
        $report = ScheduledReport::where('uuid', $uuid)->firstOrFail();

        $this->authorize('update', $report);

        $report = $this->scheduledReportService->resumeReport($report->id);

        return $this->success([
            'message' => 'Scheduled report resumed',
            'report' => $report,
        ]);
    }

    /**
     * Toggle active status of a scheduled report
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function toggle(string $uuid): JsonResponse
    {
        $report = ScheduledReport::where('uuid', $uuid)->firstOrFail();

        $this->authorize('update', $report);

        if ($report->is_active) {
            $report = $this->scheduledReportService->pauseReport($report->id);
            $message = 'Scheduled report paused';
        } else {
            $report = $this->scheduledReportService->resumeReport($report->id);
            $message = 'Scheduled report resumed';
        }

        return $this->success([
            'message' => $message,
            'is_active' => $report->is_active,
            'report' => $report,
        ]);
    }

    /**
     * Preview what would be generated for a report
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function preview(string $uuid): JsonResponse
    {
        $report = ScheduledReport::where('uuid', $uuid)->firstOrFail();

        $this->authorize('view', $report);

        $preview = $this->scheduledReportService->preview($report);

        return $this->success($preview);
    }

    /**
     * Get available schedule types
     *
     * @return JsonResponse
     */
    public function scheduleTypes(): JsonResponse
    {
        return $this->success([
            'schedule_types' => ScheduledReport::getScheduleTypes(),
            'day_of_week_options' => [
                0 => 'Sunday',
                1 => 'Monday',
                2 => 'Tuesday',
                3 => 'Wednesday',
                4 => 'Thursday',
                5 => 'Friday',
                6 => 'Saturday',
            ],
        ]);
    }

    /**
     * Get available report types
     *
     * @return JsonResponse
     */
    public function reportTypes(): JsonResponse
    {
        return $this->success([
            'report_types' => ScheduledReport::getReportTypes(),
            'date_range_types' => ScheduledReport::getDateRangeTypes(),
            'delivery_methods' => ScheduledReport::getDeliveryMethods(),
            'export_formats' => ScheduledReport::getExportFormats(),
        ]);
    }

    /**
     * Get overall scheduling statistics
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function stats(Request $request): JsonResponse
    {
        // Get user-specific stats
        $userId = $request->user()->id;

        $userStats = [
            'total_reports' => ScheduledReport::where('created_by', $userId)->count(),
            'active_reports' => ScheduledReport::where('created_by', $userId)->active()->count(),
            'total_runs' => ScheduledReportRun::whereHas('scheduledReport', function ($q) use ($userId) {
                $q->where('created_by', $userId);
            })->count(),
            'successful_runs' => ScheduledReportRun::whereHas('scheduledReport', function ($q) use ($userId) {
                $q->where('created_by', $userId);
            })->where('status', 'success')->count(),
            'failed_runs' => ScheduledReportRun::whereHas('scheduledReport', function ($q) use ($userId) {
                $q->where('created_by', $userId);
            })->where('status', 'failed')->count(),
        ];

        $userStats['success_rate'] = $userStats['total_runs'] > 0
            ? round(($userStats['successful_runs'] / $userStats['total_runs']) * 100, 2)
            : 100;

        // Get upcoming runs for user
        $userStats['upcoming_runs'] = ScheduledReport::where('created_by', $userId)
            ->active()
            ->whereNotNull('next_run_at')
            ->orderBy('next_run_at')
            ->limit(5)
            ->get(['id', 'uuid', 'name', 'next_run_at', 'report_type']);

        // Get recent runs
        $userStats['recent_runs'] = ScheduledReportRun::whereHas('scheduledReport', function ($q) use ($userId) {
            $q->where('created_by', $userId);
        })
            ->with('scheduledReport:id,uuid,name')
            ->orderByDesc('started_at')
            ->limit(10)
            ->get();

        return $this->success($userStats);
    }

    /**
     * Get a specific run details
     *
     * @param string $reportUuid
     * @param string $runUuid
     * @return JsonResponse
     */
    public function showRun(string $reportUuid, string $runUuid): JsonResponse
    {
        $report = ScheduledReport::where('uuid', $reportUuid)->firstOrFail();

        $this->authorize('view', $report);

        $run = ScheduledReportRun::where('uuid', $runUuid)
            ->where('scheduled_report_id', $report->id)
            ->with('scheduledReport')
            ->firstOrFail();

        return $this->success($run);
    }

    /**
     * Duplicate a scheduled report
     *
     * @param Request $request
     * @param string $uuid
     * @return JsonResponse
     */
    public function duplicate(Request $request, string $uuid): JsonResponse
    {
        $original = ScheduledReport::where('uuid', $uuid)->firstOrFail();

        $this->authorize('view', $original);

        $newName = $request->input('name', $original->name . ' (Copy)');

        $data = $original->toArray();
        unset($data['id'], $data['uuid'], $data['created_at'], $data['updated_at'], $data['deleted_at']);
        unset($data['last_run_at'], $data['next_run_at'], $data['last_run_status'], $data['last_error']);
        unset($data['run_count'], $data['success_count'], $data['failure_count']);

        $data['name'] = $newName;
        $data['is_active'] = false; // Start as inactive

        $newReport = $this->scheduledReportService->create($data, $request->user());

        return $this->success($newReport->load(['creator', 'template']), 201);
    }

    /**
     * Validate schedule configuration
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function validateSchedule(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'schedule_type' => ['required', 'string', 'in:daily,weekly,biweekly,monthly,quarterly'],
            'schedule_config' => ['nullable', 'array'],
            'timezone' => ['nullable', 'string', 'timezone'],
        ]);

        $nextRun = $this->scheduledReportService->calculateNextRun(
            $validated['schedule_type'],
            $validated['schedule_config'] ?? [],
            $validated['timezone'] ?? 'UTC'
        );

        return $this->success([
            'valid' => $nextRun !== null,
            'next_run_at' => $nextRun?->toIso8601String(),
            'next_run_formatted' => $nextRun?->format('l, F j, Y \a\t g:i A T'),
            'schedule_type' => $validated['schedule_type'],
            'timezone' => $validated['timezone'] ?? 'UTC',
        ]);
    }
}
