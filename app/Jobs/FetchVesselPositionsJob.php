<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\TrackedVessel;
use App\Services\MaritimeTrackingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Job for fetching latest positions for tracked vessels from AIS providers.
 *
 * This job can be scheduled to run periodically to update vessel positions.
 * It respects monitoring priority and fetches positions more frequently
 * for high-priority vessels.
 */
class FetchVesselPositionsJob implements ShouldQueue
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
    public int $timeout = 300;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var array
     */
    public array $backoff = [30, 60, 120];

    /**
     * Create a new job instance.
     *
     * @param string|null $source Specific data source to fetch from
     * @param int|null $priority Only fetch vessels with this priority or higher (1=highest)
     * @param TrackedVessel|null $vessel Specific vessel to fetch
     * @param array $options Additional options
     */
    public function __construct(
        protected ?string $source = null,
        protected ?int $priority = null,
        protected ?TrackedVessel $vessel = null,
        protected array $options = []
    ) {}

    /**
     * Execute the job.
     *
     * @param MaritimeTrackingService $maritimeService
     */
    public function handle(MaritimeTrackingService $maritimeService): void
    {
        $source = $this->source ?? config('maritime.default_provider');

        Log::info('Starting vessel position fetch job', [
            'source' => $source,
            'priority' => $this->priority,
            'vessel_id' => $this->vessel?->id,
        ]);

        if ($this->vessel) {
            $this->fetchForVessel($maritimeService, $source);
        } else {
            $this->fetchForPriority($maritimeService, $source);
        }
    }

    /**
     * Fetch positions for a specific vessel.
     *
     * @param MaritimeTrackingService $maritimeService
     * @param string $source
     */
    protected function fetchForVessel(
        MaritimeTrackingService $maritimeService,
        string $source
    ): void {
        if (!$this->vessel->is_monitored) {
            Log::info('Vessel monitoring disabled, skipping', [
                'vessel_id' => $this->vessel->id,
            ]);
            return;
        }

        try {
            $options = array_merge($this->options, [
                'mmsi' => $this->vessel->mmsi,
            ]);

            $positions = $maritimeService->fetchPositions($source, $options);

            $recorded = 0;
            foreach ($positions as $posData) {
                if ($posData['mmsi'] === $this->vessel->mmsi) {
                    $maritimeService->recordPosition($this->vessel->id, array_merge(
                        $posData,
                        ['data_source' => $source]
                    ));
                    $recorded++;
                }
            }

            Log::info('Vessel position fetch completed', [
                'vessel_id' => $this->vessel->id,
                'mmsi' => $this->vessel->mmsi,
                'positions_recorded' => $recorded,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch vessel position', [
                'vessel_id' => $this->vessel->id,
                'mmsi' => $this->vessel->mmsi,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            if ($this->attempts() < $this->tries) {
                throw $e;
            }
        }
    }

    /**
     * Fetch positions for all vessels at a priority level.
     *
     * @param MaritimeTrackingService $maritimeService
     * @param string $source
     */
    protected function fetchForPriority(
        MaritimeTrackingService $maritimeService,
        string $source
    ): void {
        $query = TrackedVessel::monitored();

        if ($this->priority !== null) {
            $query->where('monitoring_priority', '<=', $this->priority);
        }

        $batchSize = $this->options['batch_size'] ?? config('maritime.fetching.batch_size', 50);
        $vessels = $query->pluck('mmsi', 'id');

        if ($vessels->isEmpty()) {
            Log::info('No monitored vessels to fetch', [
                'priority' => $this->priority,
            ]);
            return;
        }

        Log::info('Fetching positions for vessels', [
            'vessel_count' => $vessels->count(),
            'priority' => $this->priority,
        ]);

        // Fetch in batches
        $batches = $vessels->chunk($batchSize);
        $totalRecorded = 0;

        foreach ($batches as $batch) {
            try {
                $mmsiList = $batch->values()->toArray();
                $idByMmsi = $batch->flip()->toArray();

                $options = array_merge($this->options, [
                    'mmsi' => $mmsiList,
                ]);

                $positions = $maritimeService->fetchPositions($source, $options);

                foreach ($positions as $posData) {
                    $mmsi = $posData['mmsi'] ?? null;
                    if ($mmsi && isset($idByMmsi[$mmsi])) {
                        try {
                            $maritimeService->recordPosition(
                                $idByMmsi[$mmsi],
                                array_merge($posData, ['data_source' => $source])
                            );
                            $totalRecorded++;
                        } catch (\Exception $e) {
                            Log::warning('Failed to record position', [
                                'mmsi' => $mmsi,
                                'error' => $e->getMessage(),
                            ]);
                        }
                    }
                }

                // Small delay between batches to respect rate limits
                if ($batches->count() > 1) {
                    usleep(500000); // 0.5 second
                }

            } catch (\Exception $e) {
                Log::error('Failed to fetch batch', [
                    'batch_count' => $batch->count(),
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('Batch vessel position fetch completed', [
            'vessels_total' => $vessels->count(),
            'positions_recorded' => $totalRecorded,
            'priority' => $this->priority,
        ]);
    }

    /**
     * Handle a job failure.
     *
     * @param \Throwable|null $exception
     */
    public function failed(?\Throwable $exception): void
    {
        $context = [
            'error' => $exception?->getMessage(),
            'source' => $this->source ?? config('maritime.default_provider'),
            'priority' => $this->priority,
        ];

        if ($this->vessel) {
            $context['vessel_id'] = $this->vessel->id;
            $context['mmsi'] = $this->vessel->mmsi;
        }

        Log::error('Vessel position fetch job failed permanently', $context);
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array<int, string>
     */
    public function tags(): array
    {
        $tags = ['maritime', 'position_fetch'];

        $source = $this->source ?? config('maritime.default_provider');
        $tags[] = 'source:' . $source;

        if ($this->vessel) {
            $tags[] = 'vessel:' . $this->vessel->id;
            $tags[] = 'mmsi:' . $this->vessel->mmsi;
        }

        if ($this->priority !== null) {
            $tags[] = 'priority:' . $this->priority;
        }

        return $tags;
    }

    /**
     * Get the middleware the job should pass through.
     *
     * @return array
     */
    public function middleware(): array
    {
        $key = 'vessel_fetch';

        if ($this->vessel) {
            $key .= ':' . $this->vessel->id;
        } elseif ($this->priority !== null) {
            $key .= ':priority:' . $this->priority;
        }

        return [
            new \Illuminate\Queue\Middleware\WithoutOverlapping($key),
            new \Illuminate\Queue\Middleware\RateLimited('maritime_api'),
        ];
    }

    /**
     * Determine the time at which the job should timeout.
     *
     * @return \DateTime
     */
    public function retryUntil(): \DateTime
    {
        return now()->addHours(1);
    }

    /**
     * Dispatch jobs for all priority levels based on configuration.
     *
     * This method can be called from a scheduler to dispatch appropriate
     * jobs based on the configured fetch intervals.
     */
    public static function dispatchByPriority(): void
    {
        $now = now();
        $config = config('maritime.fetching');

        // High priority (1-2): fetch frequently
        if ($now->minute % $config['high_priority_interval'] === 0) {
            self::dispatch(null, TrackedVessel::PRIORITY_HIGH);
        }

        // Medium priority (3): fetch less frequently
        if ($now->minute % $config['medium_priority_interval'] === 0) {
            self::dispatch(null, TrackedVessel::PRIORITY_MEDIUM);
        }

        // Low priority (4-5): fetch infrequently
        if ($now->minute % $config['low_priority_interval'] === 0) {
            self::dispatch(null, TrackedVessel::PRIORITY_MINIMAL);
        }
    }
}
