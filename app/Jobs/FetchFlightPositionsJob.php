<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\TrackedAircraft;
use App\Services\FlightTrackingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Job for fetching ADS-B flight positions from configured data sources.
 *
 * This job can be scheduled to run periodically to update positions
 * for all monitored aircraft or dispatched with specific options.
 */
class FetchFlightPositionsJob implements ShouldQueue
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
    public int $timeout = 120;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var array
     */
    public array $backoff = [30, 60, 120];

    /**
     * Create a new job instance.
     *
     * @param string|null $source Specific data source to fetch from (null = default)
     * @param array $options Fetch options (bounds, priority, etc.)
     */
    public function __construct(
        protected ?string $source = null,
        protected array $options = []
    ) {}

    /**
     * Execute the job.
     *
     * @param FlightTrackingService $flightTrackingService
     */
    public function handle(FlightTrackingService $flightTrackingService): void
    {
        $config = config('flight_tracking', []);
        $source = $this->source ?? $config['default_provider'] ?? 'opensky';

        Log::info('Starting flight position fetch', [
            'source' => $source,
            'options' => $this->options,
        ]);

        try {
            // Determine what aircraft to fetch
            $fetchOptions = $this->buildFetchOptions($config);

            // Fetch positions from the data source
            $positions = $flightTrackingService->fetchPositions($source, $fetchOptions);

            // Filter to only monitored aircraft if configured
            if ($config['fetch']['monitored_only'] ?? true) {
                $monitoredIcaos = TrackedAircraft::monitored()
                    ->pluck('icao_hex')
                    ->map(fn($hex) => strtoupper($hex))
                    ->toArray();

                $positions = $positions->filter(function ($position) use ($monitoredIcaos) {
                    $aircraft = $position->aircraft;
                    return in_array(strtoupper($aircraft->icao_hex), $monitoredIcaos);
                });
            }

            // Update last seen timestamps
            $aircraftIds = $positions->pluck('aircraft_id')->unique();
            TrackedAircraft::whereIn('id', $aircraftIds)
                ->update(['last_seen_at' => now()]);

            // Process alerts for new positions
            foreach ($positions as $position) {
                try {
                    $flightTrackingService->processAlerts($position);
                } catch (\Exception $e) {
                    Log::warning('Alert processing failed for position', [
                        'position_id' => $position->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            Log::info('Flight position fetch completed', [
                'source' => $source,
                'positions_fetched' => $positions->count(),
                'aircraft_updated' => $aircraftIds->count(),
            ]);

        } catch (\Exception $e) {
            Log::error('Flight position fetch failed', [
                'source' => $source,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            // Only throw if we have retries left
            if ($this->attempts() < $this->tries) {
                throw $e;
            }
        }
    }

    /**
     * Build fetch options from config and job options
     */
    private function buildFetchOptions(array $config): array
    {
        $fetchOptions = $this->options;

        // Add default bounds if configured
        $defaultBounds = $config['fetch']['default_bounds'] ?? [];
        if (($defaultBounds['enabled'] ?? false) && !isset($fetchOptions['bounds'])) {
            $fetchOptions['bounds'] = [
                'min_lat' => $defaultBounds['min_lat'],
                'max_lat' => $defaultBounds['max_lat'],
                'min_lon' => $defaultBounds['min_lon'],
                'max_lon' => $defaultBounds['max_lon'],
            ];
        }

        // Add ICAO list for monitored aircraft if not doing area fetch
        if (!isset($fetchOptions['bounds']) && ($config['fetch']['monitored_only'] ?? true)) {
            $priority = $this->options['priority'] ?? null;

            $query = TrackedAircraft::monitored();

            if ($priority !== null) {
                $query->priority($priority);
            }

            $fetchOptions['icao_list'] = $query
                ->limit($config['fetch']['batch_size'] ?? 100)
                ->pluck('icao_hex')
                ->toArray();
        }

        return $fetchOptions;
    }

    /**
     * Handle a job failure.
     *
     * @param \Throwable|null $exception
     */
    public function failed(?\Throwable $exception): void
    {
        Log::error('Flight position fetch job failed permanently', [
            'source' => $this->source,
            'options' => $this->options,
            'error' => $exception?->getMessage(),
        ]);
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array<int, string>
     */
    public function tags(): array
    {
        $tags = ['flight_tracking', 'position_fetch'];

        if ($this->source) {
            $tags[] = 'source:' . $this->source;
        }

        if (isset($this->options['priority'])) {
            $tags[] = 'priority:' . $this->options['priority'];
        }

        return $tags;
    }

    /**
     * Calculate the number of seconds to wait before retrying the job.
     *
     * @return int
     */
    public function retryAfter(): int
    {
        $attempt = $this->attempts();
        return $this->backoff[$attempt - 1] ?? 120;
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
     * Get the middleware the job should pass through.
     *
     * @return array
     */
    public function middleware(): array
    {
        $lockKey = 'flight_fetch';

        if ($this->source) {
            $lockKey .= ':' . $this->source;
        }

        if (isset($this->options['priority'])) {
            $lockKey .= ':priority:' . $this->options['priority'];
        }

        return [
            new \Illuminate\Queue\Middleware\WithoutOverlapping($lockKey),
        ];
    }

    /**
     * Determine the number of seconds before the overlapping lock should be released.
     *
     * @return int
     */
    public function releaseAfter(): int
    {
        return $this->timeout + 30;
    }

    /**
     * Create a scheduled job for periodic fetching
     *
     * @return static
     */
    public static function scheduled(): static
    {
        return new static(null, ['scheduled' => true]);
    }

    /**
     * Create a job for fetching a specific priority level
     *
     * @param int $priority
     * @return static
     */
    public static function forPriority(int $priority): static
    {
        return new static(null, ['priority' => $priority]);
    }

    /**
     * Create a job for fetching from a specific source
     *
     * @param string $source
     * @return static
     */
    public static function fromSource(string $source): static
    {
        return new static($source);
    }

    /**
     * Create a job for fetching within bounds
     *
     * @param array $bounds
     * @return static
     */
    public static function withinBounds(array $bounds): static
    {
        return new static(null, ['bounds' => $bounds]);
    }
}
