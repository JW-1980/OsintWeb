<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Actor;
use App\Models\ControlZone;
use App\Domains\Intelligence\Models\Event;
use App\Models\EventEquipment;
use App\Models\MilitaryEquipment;
use App\Models\Sitrep;
use App\Models\SitrepTemplate;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Html;

/**
 * Service for generating and managing Situation Reports (SITREPs).
 *
 * Handles creation of reports from templates, section generation,
 * AI-powered summaries, and export to various formats.
 */
class SitrepService
{
    protected OpenRouterService $openRouter;

    public function __construct(OpenRouterService $openRouter)
    {
        $this->openRouter = $openRouter;
    }

    // =========================================================================
    // SITREP CREATION
    // =========================================================================

    /**
     * Create a new SITREP from a template.
     *
     * @param int $templateId Template ID to use
     * @param array $dateRange ['start' => Carbon, 'end' => Carbon]
     * @param array $filters Optional filters for data selection
     * @param User $user The user creating the sitrep
     * @return Sitrep
     */
    public function createFromTemplate(
        int $templateId,
        array $dateRange,
        array $filters,
        User $user
    ): Sitrep {
        $template = SitrepTemplate::findOrFail($templateId);

        $sitrep = Sitrep::create([
            'title' => $this->generateTitle($template, $dateRange),
            'subtitle' => $filters['subtitle'] ?? null,
            'template_id' => $template->id,
            'report_type' => $template->template_type,
            'classification_level' => $filters['classification_level'] ?? Sitrep::CLASSIFICATION_UNCLASSIFIED,
            'period_start' => Carbon::parse($dateRange['start']),
            'period_end' => Carbon::parse($dateRange['end']),
            'status' => Sitrep::STATUS_DRAFT,
            'created_by' => $user->id,
            'metadata' => [
                'filters' => $filters,
                'generated_at' => now()->toIso8601String(),
            ],
        ]);

        // Generate content for each section
        $contentSections = [];
        $eventIds = [];
        $equipmentIds = [];
        $actorIds = [];

        foreach ($template->getOrderedSections() as $sectionConfig) {
            $section = $this->generateSection($sitrep, $sectionConfig, $dateRange, $filters);
            $contentSections[] = $section;

            // Collect linked entity IDs
            $eventIds = array_merge($eventIds, $section['event_ids'] ?? []);
            $equipmentIds = array_merge($equipmentIds, $section['equipment_ids'] ?? []);
            $actorIds = array_merge($actorIds, $section['actor_ids'] ?? []);
        }

        $sitrep->update([
            'content_sections' => $contentSections,
            'event_ids' => array_unique($eventIds),
            'equipment_ids' => array_unique($equipmentIds),
            'actor_ids' => array_unique($actorIds),
        ]);

        return $sitrep->fresh();
    }

    /**
     * Generate a single section for a SITREP.
     *
     * @param Sitrep $sitrep The sitrep being generated
     * @param array $sectionConfig Section configuration from template
     * @param array $dateRange Date range for the report
     * @param array $filters Optional filters
     * @return array Generated section content
     */
    public function generateSection(
        Sitrep $sitrep,
        array $sectionConfig,
        array $dateRange,
        array $filters = []
    ): array {
        $type = $sectionConfig['type'] ?? 'custom_text';
        $queryConfig = $sectionConfig['query_config'] ?? [];

        $content = match ($type) {
            SitrepTemplate::SECTION_EVENTS_SUMMARY => $this->buildEventsSummary($dateRange, array_merge($filters, $queryConfig)),
            SitrepTemplate::SECTION_EQUIPMENT_LOSSES => $this->buildEquipmentLosses($dateRange, array_merge($filters, $queryConfig)),
            SitrepTemplate::SECTION_TERRITORIAL_CHANGES => $this->buildTerritorialChanges($dateRange, $queryConfig),
            SitrepTemplate::SECTION_ACTOR_ACTIVITY => $this->buildActorActivity($dateRange, array_merge($filters, $queryConfig)),
            SitrepTemplate::SECTION_TIMELINE => $this->buildTimeline($dateRange, $filters),
            SitrepTemplate::SECTION_STATISTICS => $this->buildStatistics($dateRange, $filters),
            SitrepTemplate::SECTION_MAP => $this->buildMapSection($dateRange, $filters),
            SitrepTemplate::SECTION_SOURCES => $this->buildSourcesSection($sitrep),
            default => ['text' => $sectionConfig['default_text'] ?? ''],
        };

        return [
            'title' => $sectionConfig['title'] ?? ucfirst(str_replace('_', ' ', $type)),
            'type' => $type,
            'order' => $sectionConfig['order'] ?? 999,
            'content' => $content['content'] ?? $content,
            'event_ids' => $content['event_ids'] ?? [],
            'equipment_ids' => $content['equipment_ids'] ?? [],
            'actor_ids' => $content['actor_ids'] ?? [],
            'generated_at' => now()->toIso8601String(),
        ];
    }

    // =========================================================================
    // SECTION BUILDERS
    // =========================================================================

    /**
     * Build the events summary section.
     *
     * @param array $dateRange Date range for events
     * @param array $filters Optional filters
     * @return array
     */
    public function buildEventsSummary(array $dateRange, array $filters = []): array
    {
        $query = Event::with(['eventType', 'user'])
            ->whereBetween('occurred_at', [
                Carbon::parse($dateRange['start'])->startOfDay(),
                Carbon::parse($dateRange['end'])->endOfDay(),
            ])
            ->where('status', Event::STATUS_VERIFIED);

        // Apply filters
        if (!empty($filters['event_type_id'])) {
            $query->where('event_type_id', $filters['event_type_id']);
        }

        if (!empty($filters['min_confidence'])) {
            $query->where('confidence_score', '>=', $filters['min_confidence']);
        }

        // Apply sorting
        $sort = $filters['sort'] ?? 'occurred_at';
        if ($sort === 'importance') {
            $query->orderByRaw('(upvotes_count - downvotes_count) DESC');
        } else {
            $query->orderBy($sort, 'desc');
        }

        // Apply limit
        $limit = $filters['limit'] ?? 20;
        $events = $query->limit($limit)->get();

        $eventIds = $events->pluck('id')->toArray();

        // Group events by type
        $byType = $events->groupBy(fn ($e) => $e->eventType?->name ?? 'Other');

        $summary = [
            'total_events' => $events->count(),
            'by_type' => $byType->map(fn ($group) => [
                'count' => $group->count(),
                'events' => $group->map(fn ($e) => [
                    'id' => $e->id,
                    'uuid' => $e->uuid,
                    'title' => $e->title,
                    'description' => Str::limit($e->description, 200),
                    'occurred_at' => $e->occurred_at->format('Y-m-d H:i'),
                    'location_name' => $e->location_name,
                    'confidence_score' => $e->confidence_score,
                ])->toArray(),
            ])->toArray(),
            'date_range' => [
                'start' => $dateRange['start'],
                'end' => $dateRange['end'],
            ],
        ];

        return [
            'content' => $summary,
            'event_ids' => $eventIds,
        ];
    }

    /**
     * Build the equipment losses section.
     *
     * @param array $dateRange Date range for losses
     * @param array $filters Optional filters
     * @return array
     */
    public function buildEquipmentLosses(array $dateRange, array $filters = []): array
    {
        $query = EventEquipment::with(['equipment.category', 'equipment.country', 'event'])
            ->whereHas('event', function ($q) use ($dateRange) {
                $q->whereBetween('occurred_at', [
                    Carbon::parse($dateRange['start'])->startOfDay(),
                    Carbon::parse($dateRange['end'])->endOfDay(),
                ])->where('status', Event::STATUS_VERIFIED);
            });

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $losses = $query->get();

        $equipmentIds = $losses->pluck('equipment_id')->unique()->toArray();
        $eventIds = $losses->pluck('event_id')->unique()->toArray();

        // Group by equipment category
        $byCategory = $losses->groupBy(fn ($l) => $l->equipment?->category?->name ?? 'Unknown');

        // Group by country (faction)
        $byCountry = $losses->groupBy(fn ($l) => $l->equipment?->country?->name ?? 'Unknown');

        $summary = [
            'total_losses' => $losses->count(),
            'total_equipment_types' => count($equipmentIds),
            'by_category' => $byCategory->map(fn ($group) => [
                'count' => $group->count(),
                'items' => $group->map(fn ($l) => [
                    'equipment_id' => $l->equipment_id,
                    'equipment_name' => $l->equipment?->name,
                    'quantity' => $l->quantity ?? 1,
                    'status' => $l->status,
                    'event_title' => $l->event?->title,
                    'event_date' => $l->event?->occurred_at?->format('Y-m-d'),
                ])->toArray(),
            ])->toArray(),
            'by_country' => $byCountry->map(fn ($group) => [
                'count' => $group->count(),
                'quantity' => $group->sum('quantity'),
            ])->toArray(),
            'date_range' => [
                'start' => $dateRange['start'],
                'end' => $dateRange['end'],
            ],
        ];

        return [
            'content' => $summary,
            'event_ids' => $eventIds,
            'equipment_ids' => $equipmentIds,
        ];
    }

    /**
     * Build the territorial changes section.
     *
     * @param array $dateRange Date range
     * @param array $filters Optional filters
     * @return array
     */
    public function buildTerritorialChanges(array $dateRange, array $filters = []): array
    {
        $startDate = Carbon::parse($dateRange['start']);
        $endDate = Carbon::parse($dateRange['end']);

        // Get zones that changed during the period
        $changedZones = ControlZone::with('controller')
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('controlled_from', [$startDate, $endDate])
                    ->orWhereBetween('controlled_until', [$startDate, $endDate]);
            })
            ->get();

        // Get new zones created during the period
        $newZones = ControlZone::with('controller')
            ->whereBetween('controlled_from', [$startDate, $endDate])
            ->get();

        // Get zones that ended during the period
        $endedZones = ControlZone::with('controller')
            ->whereBetween('controlled_until', [$startDate, $endDate])
            ->get();

        // Group by controller/faction
        $byFaction = $changedZones->groupBy(fn ($z) => $z->controller?->name ?? 'Unknown');

        $summary = [
            'total_changes' => $changedZones->count(),
            'new_zones' => $newZones->count(),
            'ended_zones' => $endedZones->count(),
            'by_faction' => $byFaction->map(fn ($group) => [
                'count' => $group->count(),
                'zones' => $group->map(fn ($z) => [
                    'uuid' => $z->uuid,
                    'name' => $z->name,
                    'controlled_from' => $z->controlled_from?->format('Y-m-d'),
                    'controlled_until' => $z->controlled_until?->format('Y-m-d'),
                    'is_active' => $z->is_active,
                ])->toArray(),
            ])->toArray(),
            'date_range' => [
                'start' => $dateRange['start'],
                'end' => $dateRange['end'],
            ],
        ];

        return [
            'content' => $summary,
        ];
    }

    /**
     * Build the actor activity section.
     *
     * @param array $dateRange Date range
     * @param array $filters Optional filters
     * @return array
     */
    public function buildActorActivity(array $dateRange, array $filters = []): array
    {
        $startDate = Carbon::parse($dateRange['start']);
        $endDate = Carbon::parse($dateRange['end']);

        // Get actors with recent activity
        $query = Actor::withCount(['events' => function ($q) use ($startDate, $endDate) {
            $q->whereBetween('occurred_at', [$startDate, $endDate])
                ->where('status', Event::STATUS_VERIFIED);
        }])
            ->having('events_count', '>', 0)
            ->orderBy('events_count', 'desc');

        $limit = $filters['limit'] ?? 10;
        $actors = $query->limit($limit)->get();

        $actorIds = $actors->pluck('id')->toArray();

        // Get event IDs for these actors
        $eventIds = [];
        foreach ($actors as $actor) {
            $actorEventIds = $actor->events()
                ->whereBetween('occurred_at', [$startDate, $endDate])
                ->pluck('events.id')
                ->toArray();
            $eventIds = array_merge($eventIds, $actorEventIds);
        }

        $summary = [
            'total_active_actors' => $actors->count(),
            'actors' => $actors->map(fn ($a) => [
                'id' => $a->id,
                'uuid' => $a->uuid,
                'name' => $a->name,
                'actor_type' => $a->actor_type,
                'is_state_actor' => $a->is_state_actor,
                'event_count' => $a->events_count,
                'activity_level' => $a->activity_level,
            ])->toArray(),
            'date_range' => [
                'start' => $dateRange['start'],
                'end' => $dateRange['end'],
            ],
        ];

        return [
            'content' => $summary,
            'actor_ids' => $actorIds,
            'event_ids' => array_unique($eventIds),
        ];
    }

    /**
     * Build a timeline section.
     *
     * @param array $dateRange Date range
     * @param array $filters Optional filters
     * @return array
     */
    public function buildTimeline(array $dateRange, array $filters = []): array
    {
        $events = Event::with(['eventType'])
            ->whereBetween('occurred_at', [
                Carbon::parse($dateRange['start'])->startOfDay(),
                Carbon::parse($dateRange['end'])->endOfDay(),
            ])
            ->where('status', Event::STATUS_VERIFIED)
            ->orderBy('occurred_at', 'asc')
            ->limit($filters['limit'] ?? 50)
            ->get();

        $timeline = $events->map(fn ($e) => [
            'uuid' => $e->uuid,
            'title' => $e->title,
            'type' => $e->eventType?->name,
            'occurred_at' => $e->occurred_at->toIso8601String(),
            'location_name' => $e->location_name,
        ])->toArray();

        return [
            'content' => [
                'events' => $timeline,
                'total' => count($timeline),
            ],
            'event_ids' => $events->pluck('id')->toArray(),
        ];
    }

    /**
     * Build a statistics section.
     *
     * @param array $dateRange Date range
     * @param array $filters Optional filters
     * @return array
     */
    public function buildStatistics(array $dateRange, array $filters = []): array
    {
        $startDate = Carbon::parse($dateRange['start']);
        $endDate = Carbon::parse($dateRange['end']);

        $stats = [
            'events' => [
                'total' => Event::whereBetween('occurred_at', [$startDate, $endDate])
                    ->where('status', Event::STATUS_VERIFIED)
                    ->count(),
                'by_day' => Event::selectRaw('DATE(occurred_at) as date, COUNT(*) as count')
                    ->whereBetween('occurred_at', [$startDate, $endDate])
                    ->where('status', Event::STATUS_VERIFIED)
                    ->groupBy('date')
                    ->orderBy('date')
                    ->pluck('count', 'date')
                    ->toArray(),
            ],
            'equipment_losses' => [
                'total' => EventEquipment::whereHas('event', function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('occurred_at', [$startDate, $endDate])
                        ->where('status', Event::STATUS_VERIFIED);
                })->count(),
            ],
            'active_actors' => Actor::whereHas('events', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('occurred_at', [$startDate, $endDate]);
            })->count(),
            'date_range' => [
                'start' => $dateRange['start'],
                'end' => $dateRange['end'],
                'days' => $startDate->diffInDays($endDate) + 1,
            ],
        ];

        return [
            'content' => $stats,
        ];
    }

    /**
     * Build a map section placeholder.
     *
     * @param array $dateRange Date range
     * @param array $filters Optional filters
     * @return array
     */
    public function buildMapSection(array $dateRange, array $filters = []): array
    {
        // Get events with coordinates
        $events = Event::whereBetween('occurred_at', [
            Carbon::parse($dateRange['start'])->startOfDay(),
            Carbon::parse($dateRange['end'])->endOfDay(),
        ])
            ->where('status', Event::STATUS_VERIFIED)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->select(['id', 'uuid', 'title', 'latitude', 'longitude', 'occurred_at'])
            ->get();

        return [
            'content' => [
                'markers' => $events->map(fn ($e) => [
                    'uuid' => $e->uuid,
                    'title' => $e->title,
                    'lat' => (float) $e->latitude,
                    'lng' => (float) $e->longitude,
                ])->toArray(),
                'total_markers' => $events->count(),
                'note' => 'Map visualization to be rendered by frontend',
            ],
            'event_ids' => $events->pluck('id')->toArray(),
        ];
    }

    /**
     * Build the sources section.
     *
     * @param Sitrep $sitrep The sitrep
     * @return array
     */
    public function buildSourcesSection(Sitrep $sitrep): array
    {
        $eventIds = $sitrep->event_ids ?? [];

        if (empty($eventIds)) {
            return ['content' => ['sources' => [], 'total' => 0]];
        }

        // Get unique sources from linked events
        $sources = \App\Models\EventSource::with('source')
            ->whereIn('event_id', $eventIds)
            ->get()
            ->unique('source_id');

        return [
            'content' => [
                'sources' => $sources->map(fn ($es) => [
                    'url' => $es->url,
                    'title' => $es->title ?? $es->source?->name,
                    'type' => $es->source?->type,
                    'reliability' => $es->source?->reliability_score,
                ])->toArray(),
                'total' => $sources->count(),
            ],
        ];
    }

    // =========================================================================
    // AI-POWERED SUMMARY
    // =========================================================================

    /**
     * Generate an executive summary using AI.
     *
     * @param Sitrep $sitrep The sitrep to summarize
     * @return string|null Generated summary or null on failure
     */
    public function generateExecutiveSummary(Sitrep $sitrep): ?string
    {
        if (!$this->openRouter->isConfigured()) {
            Log::warning('OpenRouter not configured, cannot generate AI summary');
            return null;
        }

        // Build context from content sections
        $contextParts = [];
        $contextParts[] = "Report Type: {$sitrep->getReportTypeName()}";
        $contextParts[] = "Reporting Period: {$sitrep->getReportingPeriod()}";

        $sections = $sitrep->content_sections ?? [];
        foreach ($sections as $section) {
            $contextParts[] = "\n## {$section['title']}";
            $content = $section['content'] ?? [];

            if (isset($content['total_events'])) {
                $contextParts[] = "Total events: {$content['total_events']}";
            }
            if (isset($content['total_losses'])) {
                $contextParts[] = "Equipment losses: {$content['total_losses']}";
            }
            if (isset($content['total_changes'])) {
                $contextParts[] = "Territorial changes: {$content['total_changes']}";
            }
            if (isset($content['total_active_actors'])) {
                $contextParts[] = "Active actors: {$content['total_active_actors']}";
            }
        }

        $context = implode("\n", $contextParts);

        $prompt = <<<PROMPT
You are an intelligence analyst writing an executive summary for a situation report (SITREP).

Based on the following report data, write a concise executive summary (2-4 paragraphs) that:
1. Highlights the most significant developments
2. Identifies key trends or patterns
3. Notes any notable changes from typical activity levels
4. Maintains a professional, objective tone

Report Data:
{$context}

Write the executive summary:
PROMPT;

        $result = $this->openRouter->chat([
            ['role' => 'user', 'content' => $prompt],
        ], null, [
            'temperature' => 0.7,
            'max_tokens' => 1000,
        ]);

        if ($result && $result['success']) {
            return $result['content'];
        }

        Log::error('Failed to generate AI summary', [
            'sitrep_id' => $sitrep->id,
            'error' => $this->openRouter->getLastError(),
        ]);

        return null;
    }

    /**
     * Generate key findings using AI.
     *
     * @param Sitrep $sitrep The sitrep to analyze
     * @return array|null Array of key findings or null on failure
     */
    public function generateKeyFindings(Sitrep $sitrep): ?array
    {
        if (!$this->openRouter->isConfigured()) {
            return null;
        }

        // Build context similar to summary
        $context = json_encode($sitrep->content_sections, JSON_PRETTY_PRINT);

        $prompt = <<<PROMPT
Analyze the following SITREP data and extract 3-5 key findings.
Each finding should be a concise statement (1-2 sentences) highlighting important intelligence.

Report Data:
{$context}

Return the findings as a JSON array of strings. Example:
["Finding 1", "Finding 2", "Finding 3"]

Key findings:
PROMPT;

        $result = $this->openRouter->chat([
            ['role' => 'user', 'content' => $prompt],
        ], null, [
            'temperature' => 0.5,
            'max_tokens' => 500,
        ]);

        if ($result && $result['success']) {
            $content = $result['content'];

            // Try to parse JSON
            if (preg_match('/\[[\s\S]*\]/', $content, $matches)) {
                $parsed = json_decode($matches[0], true);
                if (is_array($parsed)) {
                    return $parsed;
                }
            }
        }

        return null;
    }

    // =========================================================================
    // EXPORT METHODS
    // =========================================================================

    /**
     * Export sitrep to PDF.
     *
     * @param int $sitrepId Sitrep ID
     * @return string|null File path or null on failure
     */
    public function exportToPdf(int $sitrepId): ?string
    {
        $sitrep = Sitrep::with(['template', 'creator', 'approver'])->findOrFail($sitrepId);

        try {
            $html = $this->renderToHtml($sitrep);

            $pdf = Pdf::loadHTML($html);
            $pdf->setPaper('A4', 'portrait');

            $filename = $this->generateExportFilename($sitrep, 'pdf');
            $path = 'sitreps/exports/' . $filename;

            Storage::disk('public')->put($path, $pdf->output());

            $sitrep->addGeneratedFile('pdf', $path);

            return $path;
        } catch (\Exception $e) {
            Log::error('Failed to export SITREP to PDF', [
                'sitrep_id' => $sitrepId,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Export sitrep to DOCX.
     *
     * @param int $sitrepId Sitrep ID
     * @return string|null File path or null on failure
     */
    public function exportToDocx(int $sitrepId): ?string
    {
        $sitrep = Sitrep::with(['template', 'creator', 'approver'])->findOrFail($sitrepId);

        try {
            $phpWord = new PhpWord();
            $section = $phpWord->addSection();

            // Add title
            $section->addText(
                $sitrep->title,
                ['bold' => true, 'size' => 18],
                ['alignment' => 'center']
            );

            if ($sitrep->subtitle) {
                $section->addText(
                    $sitrep->subtitle,
                    ['italic' => true, 'size' => 14],
                    ['alignment' => 'center']
                );
            }

            // Add classification banner
            $section->addText(
                strtoupper($sitrep->getClassificationName()),
                ['bold' => true, 'color' => 'FF0000'],
                ['alignment' => 'center', 'spaceBefore' => 200, 'spaceAfter' => 200]
            );

            // Add metadata
            $section->addText('Report Period: ' . $sitrep->getReportingPeriod(), ['size' => 10]);
            $section->addText('Generated: ' . now()->format('F j, Y H:i'), ['size' => 10]);
            $section->addTextBreak(2);

            // Add executive summary
            if ($sitrep->summary) {
                $section->addText('Executive Summary', ['bold' => true, 'size' => 14]);
                $section->addText($sitrep->summary);
                $section->addTextBreak();
            }

            // Add key findings
            if (!empty($sitrep->key_findings)) {
                $section->addText('Key Findings', ['bold' => true, 'size' => 14]);
                foreach ($sitrep->key_findings as $finding) {
                    $section->addListItem($finding, 0);
                }
                $section->addTextBreak();
            }

            // Add content sections
            foreach ($sitrep->content_sections ?? [] as $contentSection) {
                $section->addText(
                    $contentSection['title'] ?? 'Section',
                    ['bold' => true, 'size' => 14]
                );

                // Add section content as formatted text
                $this->addSectionContentToWord($section, $contentSection['content'] ?? []);
                $section->addTextBreak();
            }

            // Add recommendations
            if (!empty($sitrep->recommendations)) {
                $section->addText('Recommendations', ['bold' => true, 'size' => 14]);
                foreach ($sitrep->recommendations as $rec) {
                    $section->addListItem($rec, 0);
                }
            }

            $filename = $this->generateExportFilename($sitrep, 'docx');
            $path = 'sitreps/exports/' . $filename;
            $fullPath = Storage::disk('public')->path($path);

            // Ensure directory exists
            $dir = dirname($fullPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            $writer = IOFactory::createWriter($phpWord, 'Word2007');
            $writer->save($fullPath);

            $sitrep->addGeneratedFile('docx', $path);

            return $path;
        } catch (\Exception $e) {
            Log::error('Failed to export SITREP to DOCX', [
                'sitrep_id' => $sitrepId,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Export sitrep to HTML.
     *
     * @param int $sitrepId Sitrep ID
     * @return string|null File path or null on failure
     */
    public function exportToHtml(int $sitrepId): ?string
    {
        $sitrep = Sitrep::with(['template', 'creator', 'approver'])->findOrFail($sitrepId);

        try {
            $html = $this->renderToHtml($sitrep);

            $filename = $this->generateExportFilename($sitrep, 'html');
            $path = 'sitreps/exports/' . $filename;

            Storage::disk('public')->put($path, $html);

            $sitrep->addGeneratedFile('html', $path);

            return $path;
        } catch (\Exception $e) {
            Log::error('Failed to export SITREP to HTML', [
                'sitrep_id' => $sitrepId,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    // =========================================================================
    // APPROVAL AND PUBLISHING
    // =========================================================================

    /**
     * Approve a sitrep.
     *
     * @param int $sitrepId Sitrep ID
     * @param int $userId Approving user ID
     * @return Sitrep
     */
    public function approve(int $sitrepId, int $userId): Sitrep
    {
        $sitrep = Sitrep::findOrFail($sitrepId);
        $user = User::findOrFail($userId);

        $sitrep->approve($user);

        Log::info('SITREP approved', [
            'sitrep_id' => $sitrepId,
            'approved_by' => $userId,
        ]);

        return $sitrep->fresh();
    }

    /**
     * Publish a sitrep.
     *
     * @param int $sitrepId Sitrep ID
     * @return Sitrep
     */
    public function publish(int $sitrepId): Sitrep
    {
        $sitrep = Sitrep::findOrFail($sitrepId);

        if (!$sitrep->isApproved()) {
            throw new \InvalidArgumentException('SITREP must be approved before publishing');
        }

        $sitrep->publish();

        Log::info('SITREP published', [
            'sitrep_id' => $sitrepId,
        ]);

        return $sitrep->fresh();
    }

    // =========================================================================
    // HELPER METHODS
    // =========================================================================

    /**
     * Generate a title for the sitrep based on template and date range.
     *
     * @param SitrepTemplate $template
     * @param array $dateRange
     * @return string
     */
    protected function generateTitle(SitrepTemplate $template, array $dateRange): string
    {
        $endDate = Carbon::parse($dateRange['end']);

        return match ($template->template_type) {
            SitrepTemplate::TYPE_DAILY_BRIEF => 'Daily Brief - ' . $endDate->format('F j, Y'),
            SitrepTemplate::TYPE_WEEKLY_SUMMARY => 'Weekly Summary - Week ' . $endDate->weekOfYear . ', ' . $endDate->year,
            SitrepTemplate::TYPE_INCIDENT_REPORT => 'Incident Report - ' . $endDate->format('F j, Y'),
            SitrepTemplate::TYPE_EQUIPMENT_LOSS => 'Equipment Loss Report - ' . $endDate->format('F j, Y'),
            SitrepTemplate::TYPE_TERRITORIAL_CHANGE => 'Territorial Change Report - ' . $endDate->format('F j, Y'),
            default => 'Situation Report - ' . $endDate->format('F j, Y'),
        };
    }

    /**
     * Generate a filename for export.
     *
     * @param Sitrep $sitrep
     * @param string $extension
     * @return string
     */
    protected function generateExportFilename(Sitrep $sitrep, string $extension): string
    {
        $datePart = $sitrep->period_end?->format('Ymd') ?? now()->format('Ymd');
        $typePart = Str::slug($sitrep->report_type);

        return "sitrep-{$typePart}-{$datePart}-{$sitrep->uuid}.{$extension}";
    }

    /**
     * Render sitrep to HTML.
     *
     * @param Sitrep $sitrep
     * @return string
     */
    protected function renderToHtml(Sitrep $sitrep): string
    {
        $styling = $sitrep->template?->styling ?? SitrepTemplate::getDefaultStyling();

        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8">';
        $html .= '<style>';
        $html .= "body { font-family: {$styling['font_family']}; font-size: {$styling['font_size']}; line-height: {$styling['line_height']}; margin: 40px; }";
        $html .= "h1 { color: {$styling['primary_color']}; font-size: {$styling['heading_font_size']}; text-align: center; }";
        $html .= "h2 { color: {$styling['secondary_color']}; border-bottom: 1px solid {$styling['accent_color']}; padding-bottom: 5px; }";
        $html .= '.classification { text-align: center; color: red; font-weight: bold; margin: 20px 0; }';
        $html .= '.meta { text-align: center; color: #666; margin-bottom: 30px; }';
        $html .= '.summary { background: #f5f5f5; padding: 15px; margin: 20px 0; border-left: 4px solid ' . $styling['accent_color'] . '; }';
        $html .= '.finding { margin-left: 20px; }';
        $html .= 'table { width: 100%; border-collapse: collapse; margin: 10px 0; }';
        $html .= 'th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }';
        $html .= 'th { background: ' . $styling['primary_color'] . '; color: white; }';
        $html .= '</style></head><body>';

        // Classification banner
        $html .= '<div class="classification">' . strtoupper($sitrep->getClassificationName()) . '</div>';

        // Title
        $html .= '<h1>' . htmlspecialchars($sitrep->title) . '</h1>';
        if ($sitrep->subtitle) {
            $html .= '<p style="text-align: center; font-style: italic;">' . htmlspecialchars($sitrep->subtitle) . '</p>';
        }

        // Metadata
        $html .= '<div class="meta">';
        $html .= 'Report Period: ' . $sitrep->getReportingPeriod() . '<br>';
        $html .= 'Generated: ' . now()->format('F j, Y H:i');
        if ($sitrep->creator) {
            $html .= '<br>Author: ' . htmlspecialchars($sitrep->creator->name);
        }
        $html .= '</div>';

        // Executive Summary
        if ($sitrep->summary) {
            $html .= '<div class="summary"><h2>Executive Summary</h2>';
            $html .= '<p>' . nl2br(htmlspecialchars($sitrep->summary)) . '</p></div>';
        }

        // Key Findings
        if (!empty($sitrep->key_findings)) {
            $html .= '<h2>Key Findings</h2><ul>';
            foreach ($sitrep->key_findings as $finding) {
                $html .= '<li class="finding">' . htmlspecialchars($finding) . '</li>';
            }
            $html .= '</ul>';
        }

        // Content Sections
        foreach ($sitrep->content_sections ?? [] as $section) {
            $html .= '<h2>' . htmlspecialchars($section['title'] ?? 'Section') . '</h2>';
            $html .= $this->renderSectionContentToHtml($section['content'] ?? []);
        }

        // Recommendations
        if (!empty($sitrep->recommendations)) {
            $html .= '<h2>Recommendations</h2><ol>';
            foreach ($sitrep->recommendations as $rec) {
                $html .= '<li>' . htmlspecialchars($rec) . '</li>';
            }
            $html .= '</ol>';
        }

        // Footer classification
        $html .= '<div class="classification">' . strtoupper($sitrep->getClassificationName()) . '</div>';

        $html .= '</body></html>';

        return $html;
    }

    /**
     * Render section content to HTML.
     *
     * @param array $content
     * @return string
     */
    protected function renderSectionContentToHtml(array $content): string
    {
        $html = '';

        if (isset($content['total_events'])) {
            $html .= '<p><strong>Total Events:</strong> ' . $content['total_events'] . '</p>';

            if (!empty($content['by_type'])) {
                foreach ($content['by_type'] as $type => $data) {
                    $html .= '<h3>' . htmlspecialchars($type) . ' (' . $data['count'] . ')</h3>';
                    if (!empty($data['events'])) {
                        $html .= '<table><tr><th>Title</th><th>Date</th><th>Location</th></tr>';
                        foreach (array_slice($data['events'], 0, 10) as $event) {
                            $html .= '<tr>';
                            $html .= '<td>' . htmlspecialchars($event['title']) . '</td>';
                            $html .= '<td>' . $event['occurred_at'] . '</td>';
                            $html .= '<td>' . htmlspecialchars($event['location_name'] ?? 'N/A') . '</td>';
                            $html .= '</tr>';
                        }
                        $html .= '</table>';
                    }
                }
            }
        } elseif (isset($content['total_losses'])) {
            $html .= '<p><strong>Total Equipment Losses:</strong> ' . $content['total_losses'] . '</p>';

            if (!empty($content['by_category'])) {
                foreach ($content['by_category'] as $category => $data) {
                    $html .= '<h3>' . htmlspecialchars($category) . ' (' . $data['count'] . ')</h3>';
                }
            }
        } elseif (isset($content['actors'])) {
            $html .= '<p><strong>Active Actors:</strong> ' . $content['total_active_actors'] . '</p>';
            $html .= '<table><tr><th>Actor</th><th>Type</th><th>Events</th></tr>';
            foreach ($content['actors'] as $actor) {
                $html .= '<tr>';
                $html .= '<td>' . htmlspecialchars($actor['name']) . '</td>';
                $html .= '<td>' . htmlspecialchars($actor['actor_type'] ?? 'N/A') . '</td>';
                $html .= '<td>' . $actor['event_count'] . '</td>';
                $html .= '</tr>';
            }
            $html .= '</table>';
        } elseif (isset($content['events'])) {
            $html .= '<p><strong>Total Timeline Events:</strong> ' . $content['total'] . '</p>';
        } elseif (isset($content['text'])) {
            $html .= '<p>' . nl2br(htmlspecialchars($content['text'])) . '</p>';
        } else {
            $html .= '<pre>' . htmlspecialchars(json_encode($content, JSON_PRETTY_PRINT)) . '</pre>';
        }

        return $html;
    }

    /**
     * Add section content to Word document.
     *
     * @param \PhpOffice\PhpWord\Element\Section $section
     * @param array $content
     * @return void
     */
    protected function addSectionContentToWord($section, array $content): void
    {
        if (isset($content['total_events'])) {
            $section->addText("Total Events: {$content['total_events']}");
        } elseif (isset($content['total_losses'])) {
            $section->addText("Total Equipment Losses: {$content['total_losses']}");
        } elseif (isset($content['total_active_actors'])) {
            $section->addText("Active Actors: {$content['total_active_actors']}");
        } elseif (isset($content['text'])) {
            $section->addText($content['text']);
        } else {
            $section->addText(json_encode($content, JSON_PRETTY_PRINT), ['size' => 8]);
        }
    }
}
