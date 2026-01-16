<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Flight Track Model
 *
 * Represents an aggregated flight path for a tracked aircraft.
 *
 * @property int $id
 * @property string $uuid
 * @property int $aircraft_id
 * @property string|null $callsign Flight callsign
 * @property string|null $flight_number Commercial flight number
 * @property string|null $departure_airport ICAO departure airport code
 * @property string|null $arrival_airport ICAO arrival airport code
 * @property \Carbon\Carbon|null $departure_time Actual departure time
 * @property \Carbon\Carbon|null $arrival_time Actual arrival time
 * @property \Illuminate\Database\Query\Expression|null $track_path Flight path as LineString
 * @property int|null $max_altitude_feet Maximum altitude reached
 * @property float|null $avg_speed_knots Average ground speed
 * @property float|null $max_speed_knots Maximum ground speed
 * @property float|null $total_distance_nm Total distance in nautical miles
 * @property int $position_count Number of position reports
 * @property int|null $duration_minutes Flight duration in minutes
 * @property int|null $event_id Linked OSINT event
 * @property string $track_status Track status
 * @property string|null $notes
 * @property array|null $metadata Additional flight data
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property-read TrackedAircraft $aircraft
 * @property-read Event|null $event
 */
class FlightTrack extends Model
{
    use HasFactory;

    protected $table = 'flight_tracks';

    /**
     * Track status constants
     */
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_INCOMPLETE = 'incomplete';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'uuid',
        'aircraft_id',
        'callsign',
        'flight_number',
        'departure_airport',
        'arrival_airport',
        'departure_time',
        'arrival_time',
        'track_path',
        'max_altitude_feet',
        'avg_speed_knots',
        'max_speed_knots',
        'total_distance_nm',
        'position_count',
        'duration_minutes',
        'event_id',
        'track_status',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'departure_time' => 'datetime',
        'arrival_time' => 'datetime',
        'max_altitude_feet' => 'integer',
        'avg_speed_knots' => 'decimal:2',
        'max_speed_knots' => 'decimal:2',
        'total_distance_nm' => 'decimal:2',
        'position_count' => 'integer',
        'duration_minutes' => 'integer',
        'metadata' => 'array',
    ];

    /**
     * Boot the model
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $track) {
            if (empty($track->uuid)) {
                $track->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the aircraft this track belongs to
     */
    public function aircraft(): BelongsTo
    {
        return $this->belongsTo(TrackedAircraft::class, 'aircraft_id');
    }

    /**
     * Get the linked event
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Build track path from positions
     */
    public function buildFromPositions(array $positions): void
    {
        if (count($positions) < 2) {
            return;
        }

        // Build LineString from positions
        $points = array_map(function ($position) {
            return "{$position->longitude} {$position->latitude}";
        }, $positions);

        $lineString = 'LINESTRING(' . implode(', ', $points) . ')';

        // Calculate statistics
        $firstPosition = $positions[0];
        $lastPosition = end($positions);

        $altitudes = array_filter(array_map(fn($p) => $p->altitude_feet, $positions));
        $speeds = array_filter(array_map(fn($p) => $p->ground_speed_knots, $positions));

        // Calculate total distance
        $totalDistance = 0;
        for ($i = 1; $i < count($positions); $i++) {
            $totalDistance += $positions[$i - 1]->distanceTo($positions[$i]);
        }

        $this->update([
            'track_path' => DB::raw("ST_GeomFromText('{$lineString}', 4326)"),
            'departure_time' => $this->departure_time ?? $firstPosition->recorded_at,
            'arrival_time' => $lastPosition->on_ground ? $lastPosition->recorded_at : null,
            'max_altitude_feet' => !empty($altitudes) ? max($altitudes) : null,
            'avg_speed_knots' => !empty($speeds) ? round(array_sum($speeds) / count($speeds), 2) : null,
            'max_speed_knots' => !empty($speeds) ? max($speeds) : null,
            'total_distance_nm' => round($totalDistance, 2),
            'position_count' => count($positions),
            'duration_minutes' => $firstPosition->recorded_at->diffInMinutes($lastPosition->recorded_at),
            'track_status' => $lastPosition->on_ground ? self::STATUS_COMPLETED : self::STATUS_IN_PROGRESS,
        ]);
    }

    /**
     * Get track path as array of coordinates
     */
    public function getPathCoordinates(): array
    {
        if (!$this->track_path) {
            return [];
        }

        $result = DB::selectOne(
            "SELECT ST_AsText(track_path) as path FROM flight_tracks WHERE id = ?",
            [$this->id]
        );

        if (!$result || !$result->path) {
            return [];
        }

        // Parse LINESTRING
        preg_match('/LINESTRING\((.+)\)/', $result->path, $matches);
        if (!isset($matches[1])) {
            return [];
        }

        $points = explode(', ', $matches[1]);
        $coordinates = [];

        foreach ($points as $point) {
            [$lng, $lat] = explode(' ', trim($point));
            $coordinates[] = [
                'lat' => (float) $lat,
                'lng' => (float) $lng,
            ];
        }

        return $coordinates;
    }

    /**
     * Get flight duration as formatted string
     */
    public function getFormattedDuration(): ?string
    {
        if ($this->duration_minutes === null) {
            return null;
        }

        $hours = floor($this->duration_minutes / 60);
        $minutes = $this->duration_minutes % 60;

        if ($hours > 0) {
            return "{$hours}h {$minutes}m";
        }

        return "{$minutes}m";
    }

    /**
     * Get route string (departure -> arrival)
     */
    public function getRouteString(): string
    {
        $departure = $this->departure_airport ?? '???';
        $arrival = $this->arrival_airport ?? '???';

        return "{$departure} -> {$arrival}";
    }

    /**
     * Check if flight is complete
     */
    public function isComplete(): bool
    {
        return $this->track_status === self::STATUS_COMPLETED;
    }

    /**
     * Check if flight is in progress
     */
    public function isInProgress(): bool
    {
        return $this->track_status === self::STATUS_IN_PROGRESS;
    }

    /**
     * Link to an event
     */
    public function linkToEvent(Event $event): void
    {
        $this->update(['event_id' => $event->id]);
    }

    /**
     * Unlink from event
     */
    public function unlinkFromEvent(): void
    {
        $this->update(['event_id' => null]);
    }

    /**
     * Get altitude in flight levels
     */
    public function getMaxFlightLevel(): ?string
    {
        if ($this->max_altitude_feet === null) {
            return null;
        }

        $fl = (int) round($this->max_altitude_feet / 100);

        return "FL{$fl}";
    }

    /**
     * Scope to filter completed flights
     */
    public function scopeCompleted($query)
    {
        return $query->where('track_status', self::STATUS_COMPLETED);
    }

    /**
     * Scope to filter in-progress flights
     */
    public function scopeInProgress($query)
    {
        return $query->where('track_status', self::STATUS_IN_PROGRESS);
    }

    /**
     * Scope to filter by departure airport
     */
    public function scopeDepartureAirport($query, string $icao)
    {
        return $query->where('departure_airport', strtoupper($icao));
    }

    /**
     * Scope to filter by arrival airport
     */
    public function scopeArrivalAirport($query, string $icao)
    {
        return $query->where('arrival_airport', strtoupper($icao));
    }

    /**
     * Scope to filter by route (either airport)
     */
    public function scopeRoute($query, string $icao)
    {
        $icao = strtoupper($icao);
        return $query->where(function ($q) use ($icao) {
            $q->where('departure_airport', $icao)
                ->orWhere('arrival_airport', $icao);
        });
    }

    /**
     * Scope to filter by date range
     */
    public function scopeDateRange($query, \Carbon\Carbon $start, ?\Carbon\Carbon $end = null)
    {
        $query->where('departure_time', '>=', $start);

        if ($end) {
            $query->where('departure_time', '<=', $end);
        }

        return $query;
    }

    /**
     * Scope to filter flights with linked events
     */
    public function scopeWithEvent($query)
    {
        return $query->whereNotNull('event_id');
    }

    /**
     * Scope to filter flights without linked events
     */
    public function scopeWithoutEvent($query)
    {
        return $query->whereNull('event_id');
    }

    /**
     * Scope to filter by minimum distance
     */
    public function scopeMinDistance($query, float $distanceNm)
    {
        return $query->where('total_distance_nm', '>=', $distanceNm);
    }

    /**
     * Scope to filter by callsign
     */
    public function scopeCallsign($query, string $callsign)
    {
        return $query->where('callsign', 'like', "%{$callsign}%");
    }

    /**
     * Scope to search by multiple fields
     */
    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('callsign', 'like', "%{$term}%")
                ->orWhere('flight_number', 'like', "%{$term}%")
                ->orWhere('departure_airport', 'like', "%{$term}%")
                ->orWhere('arrival_airport', 'like', "%{$term}%");
        });
    }

    /**
     * Scope that crosses a geographic area (polygon)
     */
    public function scopeCrossesArea($query, string $polygonWkt)
    {
        return $query->whereRaw(
            "ST_Intersects(track_path, ST_GeomFromText(?, 4326))",
            [$polygonWkt]
        );
    }

    /**
     * Get all available statuses
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_IN_PROGRESS => 'In Progress',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_INCOMPLETE => 'Incomplete',
            self::STATUS_CANCELLED => 'Cancelled',
        ];
    }

    /**
     * Get status label
     */
    public function getStatusLabel(): string
    {
        return self::getStatuses()[$this->track_status] ?? 'Unknown';
    }
}
