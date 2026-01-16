<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    /**
     * List user's reports.
     */
    public function index(Request $request): JsonResponse
    {
        $query = DB::table('reports')
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc');

        if ($request->has('type')) {
            $query->where('report_type', $request->input('type'));
        }

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        $reports = $query->paginate($request->input('per_page', 25));

        return response()->json([
            'data' => $reports->items(),
            'meta' => [
                'current_page' => $reports->currentPage(),
                'total' => $reports->total(),
            ],
        ]);
    }

    /**
     * Get available report templates.
     */
    public function templates(): JsonResponse
    {
        $templates = [
            [
                'id' => 'sitrep',
                'name' => 'Situation Report (SITREP)',
                'description' => 'Standard military situation report format',
                'sections' => ['summary', 'enemy_situation', 'friendly_situation', 'equipment_status', 'assessment'],
            ],
            [
                'id' => 'daily_briefing',
                'name' => 'Daily Intelligence Briefing',
                'description' => 'Summary of events in the past 24 hours',
                'sections' => ['key_events', 'territorial_changes', 'equipment_losses', 'forecast'],
            ],
            [
                'id' => 'equipment_analysis',
                'name' => 'Equipment Loss Analysis',
                'description' => 'Detailed analysis of equipment losses',
                'sections' => ['summary', 'by_category', 'by_country', 'trends', 'assessment'],
            ],
            [
                'id' => 'conflict_overview',
                'name' => 'Conflict Overview',
                'description' => 'Comprehensive conflict status report',
                'sections' => ['background', 'current_status', 'actors', 'timeline', 'outlook'],
            ],
            [
                'id' => 'custom',
                'name' => 'Custom Report',
                'description' => 'Build your own report structure',
                'sections' => [],
            ],
        ];

        return response()->json(['data' => $templates]);
    }

    /**
     * Generate a new report.
     */
    public function generate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'template' => 'required|string',
            'conflict_id' => 'nullable|exists:conflicts,id',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
            'filters' => 'nullable|array',
            'filters.countries' => 'nullable|array',
            'filters.event_types' => 'nullable|array',
            'filters.equipment_categories' => 'nullable|array',
            'format' => 'required|in:pdf,html,docx',
            'include_maps' => 'boolean',
            'include_charts' => 'boolean',
            'branding' => 'nullable|array',
            'branding.logo' => 'nullable|string',
            'branding.organization' => 'nullable|string',
        ]);

        // Create report record
        $reportId = DB::table('reports')->insertGetId([
            'uuid' => Str::uuid(),
            'user_id' => Auth::id(),
            'title' => $validated['title'],
            'report_type' => $validated['template'],
            'conflict_id' => $validated['conflict_id'] ?? null,
            'date_from' => $validated['date_from'] ?? null,
            'date_to' => $validated['date_to'] ?? null,
            'filters' => json_encode($validated['filters'] ?? []),
            'format' => $validated['format'],
            'options' => json_encode([
                'include_maps' => $validated['include_maps'] ?? false,
                'include_charts' => $validated['include_charts'] ?? true,
                'branding' => $validated['branding'] ?? null,
            ]),
            'status' => 'generating',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Generate report content
        $content = $this->buildReportContent($validated);

        // Generate file based on format
        $filename = $this->generateReportFile($reportId, $content, $validated['format']);

        // Update report record
        DB::table('reports')->where('id', $reportId)->update([
            'content' => json_encode($content),
            'file_path' => $filename,
            'status' => 'completed',
            'generated_at' => now(),
            'updated_at' => now(),
        ]);

        $report = DB::table('reports')->where('id', $reportId)->first();

        return response()->json([
            'message' => 'Report generated successfully',
            'data' => $report,
        ], 201);
    }

    /**
     * Show a specific report.
     */
    public function show(string $uuid): JsonResponse
    {
        $report = DB::table('reports')
            ->where('uuid', $uuid)
            ->where('user_id', Auth::id())
            ->first();

        if (!$report) {
            return response()->json(['message' => 'Report not found'], 404);
        }

        return response()->json(['data' => $report]);
    }

    /**
     * Download a report.
     */
    public function download(string $uuid)
    {
        $report = DB::table('reports')
            ->where('uuid', $uuid)
            ->where('user_id', Auth::id())
            ->first();

        if (!$report) {
            return response()->json(['message' => 'Report not found'], 404);
        }

        if (!$report->file_path || !Storage::exists($report->file_path)) {
            return response()->json(['message' => 'Report file not available'], 404);
        }

        return Storage::download($report->file_path, $report->title . '.' . $report->format);
    }

    /**
     * Delete a report.
     */
    public function destroy(string $uuid): JsonResponse
    {
        $report = DB::table('reports')
            ->where('uuid', $uuid)
            ->where('user_id', Auth::id())
            ->first();

        if (!$report) {
            return response()->json(['message' => 'Report not found'], 404);
        }

        // Delete file if exists
        if ($report->file_path && Storage::exists($report->file_path)) {
            Storage::delete($report->file_path);
        }

        DB::table('reports')->where('uuid', $uuid)->delete();

        return response()->json(['message' => 'Report deleted successfully']);
    }

    /**
     * Schedule recurring report generation.
     */
    public function schedule(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'report_config' => 'required|array',
            'frequency' => 'required|in:daily,weekly,monthly',
            'delivery_method' => 'required|in:email,download',
            'email' => 'required_if:delivery_method,email|email',
            'is_active' => 'boolean',
        ]);

        $scheduleId = DB::table('report_schedules')->insertGetId([
            'uuid' => Str::uuid(),
            'user_id' => Auth::id(),
            'report_config' => json_encode($validated['report_config']),
            'frequency' => $validated['frequency'],
            'delivery_method' => $validated['delivery_method'],
            'email' => $validated['email'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
            'next_run_at' => $this->calculateNextRun($validated['frequency']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $schedule = DB::table('report_schedules')->where('id', $scheduleId)->first();

        return response()->json([
            'message' => 'Report schedule created',
            'data' => $schedule,
        ], 201);
    }

    /**
     * List scheduled reports.
     */
    public function schedules(): JsonResponse
    {
        $schedules = DB::table('report_schedules')
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['data' => $schedules]);
    }

    /**
     * Delete a schedule.
     */
    public function deleteSchedule(string $uuid): JsonResponse
    {
        DB::table('report_schedules')
            ->where('uuid', $uuid)
            ->where('user_id', Auth::id())
            ->delete();

        return response()->json(['message' => 'Schedule deleted']);
    }

    /**
     * Build report content based on template and filters.
     */
    private function buildReportContent(array $config): array
    {
        $content = [
            'title' => $config['title'],
            'generated_at' => now()->toIso8601String(),
            'template' => $config['template'],
            'sections' => [],
        ];

        // Get events for the period
        $eventsQuery = DB::table('events');

        if (isset($config['conflict_id'])) {
            $eventsQuery->where('conflict_id', $config['conflict_id']);
        }

        if (isset($config['date_from'])) {
            $eventsQuery->where('date', '>=', $config['date_from']);
        }

        if (isset($config['date_to'])) {
            $eventsQuery->where('date', '<=', $config['date_to']);
        }

        $events = $eventsQuery->get();

        // Build sections based on template
        switch ($config['template']) {
            case 'sitrep':
                $content['sections'] = $this->buildSitrepSections($events, $config);
                break;
            case 'daily_briefing':
                $content['sections'] = $this->buildDailyBriefingSections($events, $config);
                break;
            case 'equipment_analysis':
                $content['sections'] = $this->buildEquipmentAnalysisSections($events, $config);
                break;
            default:
                $content['sections'] = $this->buildCustomSections($events, $config);
        }

        return $content;
    }

    private function buildSitrepSections($events, array $config): array
    {
        return [
            'summary' => [
                'title' => 'Executive Summary',
                'content' => 'Report covering ' . $events->count() . ' events.',
                'event_count' => $events->count(),
            ],
            'enemy_situation' => [
                'title' => 'Enemy Situation',
                'content' => 'Analysis of adversary activities and movements.',
            ],
            'friendly_situation' => [
                'title' => 'Friendly Situation',
                'content' => 'Status of friendly forces and operations.',
            ],
            'equipment_status' => [
                'title' => 'Equipment Status',
                'losses' => $events->where('event_type', 'equipment_loss')->count(),
            ],
            'assessment' => [
                'title' => 'Assessment',
                'content' => 'Overall situation assessment and forecast.',
            ],
        ];
    }

    private function buildDailyBriefingSections($events, array $config): array
    {
        return [
            'key_events' => [
                'title' => 'Key Events',
                'events' => $events->take(10)->toArray(),
            ],
            'territorial_changes' => [
                'title' => 'Territorial Changes',
                'changes' => $events->where('event_type', 'territorial_change')->count(),
            ],
            'equipment_losses' => [
                'title' => 'Equipment Losses',
                'count' => $events->where('event_type', 'equipment_loss')->count(),
            ],
            'forecast' => [
                'title' => '24-Hour Forecast',
                'content' => 'Expected developments in the next 24 hours.',
            ],
        ];
    }

    private function buildEquipmentAnalysisSections($events, array $config): array
    {
        $equipmentLosses = $events->where('event_type', 'equipment_loss');

        return [
            'summary' => [
                'title' => 'Summary',
                'total_losses' => $equipmentLosses->count(),
            ],
            'by_category' => [
                'title' => 'Losses by Category',
                'data' => [],
            ],
            'by_country' => [
                'title' => 'Losses by Country',
                'data' => [],
            ],
            'trends' => [
                'title' => 'Loss Trends',
                'data' => [],
            ],
            'assessment' => [
                'title' => 'Assessment',
                'content' => 'Analysis of equipment loss patterns.',
            ],
        ];
    }

    private function buildCustomSections($events, array $config): array
    {
        return [
            'events' => [
                'title' => 'Events',
                'data' => $events->toArray(),
            ],
        ];
    }

    private function generateReportFile(int $reportId, array $content, string $format): string
    {
        $filename = "reports/{$reportId}_" . Str::slug($content['title']) . '.' . $format;

        switch ($format) {
            case 'pdf':
                $pdf = Pdf::loadView('reports.template', ['content' => $content]);
                Storage::put($filename, $pdf->output());
                break;
            case 'html':
                $html = view('reports.template', ['content' => $content])->render();
                Storage::put($filename, $html);
                break;
            case 'docx':
                // For DOCX, we'd use PhpWord in a real implementation
                Storage::put($filename, json_encode($content));
                break;
        }

        return $filename;
    }

    private function calculateNextRun(string $frequency): string
    {
        return match ($frequency) {
            'daily' => now()->addDay()->setTime(6, 0)->toDateTimeString(),
            'weekly' => now()->addWeek()->startOfWeek()->setTime(6, 0)->toDateTimeString(),
            'monthly' => now()->addMonth()->startOfMonth()->setTime(6, 0)->toDateTimeString(),
            default => now()->addDay()->toDateTimeString(),
        };
    }
}
