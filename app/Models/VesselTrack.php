<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

/**
 * VesselTrack Model
 *
 * Represents a complete voyage track for a vessel between ports.
 *
 * @property int $id
 * @property string $uuid
 * @property int $vessel_id
 * @property string|null $departure_port Port of departure
 * @property string|null $arrival_port Port of arrival
 * @property \Carbon\Carbon|null $departure_time Departure timestamp
 * @property \Carbon\Carbon|null $arrival_time Arrival timestamp
 * @property \Illuminate\Database\Query\Expression|null $track_path Complete track as LineString
 * @property float|null $total_distance_nm Total distance in nautical miles
 * @property float|null $avg_speed Average speed in knots
 * @property int $position_count Number of position points
 * @property string $voyage_type Whether vessel is loaded or empty
 * @property int|null $event_id Correlated OSINT event
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property-read TrackedVessel $vessel
 * @property-read Event|null $event
 */
class VesselTrack extends Model
{
    use HasFactory;

    /**
     * Voyage type constants.
     */
    public const VOYAGE_LADEN = 'laden';
    public const VOYAGE_BALLAST = 'ballast';
    public const VOYAGE_UNKNOWN = 'unknown';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'uuid',
        'vessel_id',
        'departure_port',
        'arrival_port',
        'departure_time',
        'arrival_time',
        'track_path',
        'total_distance_nm',
        'avg_speed',
        'position_count',
        'voyage_type',
        'event_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'departure_time' => 'datetime',
        'arrival_time' => 'datetime',
        'total_distance_nm' => 'decimal:2',
        'avg_speed' => 'decimal:2',
        'position_count' => 'integer',
    ];

    /**
     * Get the vessel this track belongs to.
     */
    public function vessel(): BelongsTo
    {
        return $this->belongsTo(TrackedVessel::class, 'vessel_id');
    }

    /**
     * Get the correlated event.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get track path as array of coordinates.
     *
     * @return array<int, array{lat: float, lng: float}>|null
     */
    public function getTrackCoordinatesAttribute(): ?array
    {
        if (!$this->track_path) {
            return null;
        }

        $result = DB::selectOne(
            "SELECT ST_AsText(track_path) as wkt FROM vessel_tracks WHERE id = ?",
            [$this->id]
        );

        if (!$result || !$result->wkt) {
            return null;
        }

        // Parse LINESTRING WKT
        preg_match('/LINESTRING\((.*)\)/', $result->wkt, $matches);

        if (empty($matches[1])) {
            return null;
        }

        $coordinates = [];
        $points = explode(',', $matches[1]);

        foreach ($points as $point) {
            $parts = explode(' ', trim($point));
            if (count($parts) >= 2) {
                $coordinates[] = [
                    'lng' => (float) $parts[0],
                    'lat' => (float) $parts[1],
                ];
            }
        }

        return $coordinates;
    }

    /**
     * Build track path from positions.
     *
     * @param array<int, array{lat: float, lng: float}> $positions
     */
    public function setTrackFromPositions(array $positions): void
    {
        if (count($positions) < 2) {
            return;
        }

        $points = array_map(function ($pos) {
            return sprintf('%f %f', $pos['lng'], $pos['lat']);
        }, $positions);

        $linestring = sprintf("LINESTRING(%s)", implode(', ', $points));

        $this->track_path = DB::raw(
            sprintf("ST_GeomFromText('%s', 4326)", $linestring)
        );
        $this->position_count = count($positions);
    }

    /**
     * Calculate voyage duration in hours.
     */
    public function getDurationHoursAttribute(): ?float
    {
        if (!$this->departure_time || !$this->arrival_time) {
            return null;
        }

        return $this->departure_time->diffInMinutes($this->arrival_time) / 60;
    }

    /**
     * Scope to filter by vessel.
     */
    public function scopeForVessel($query, int $vesselId)
    {
        return $query->where('vessel_id', $vesselId);
    }

    /**
     * Scope to filter by voyage type.
     */
    public function scopeOfVoyageType($query, string $type)
    {
        return $query->where('voyage_type', $type);
    }

    /**
     * Scope to filter tracks that have an associated event.
     */
    public function scopeWithEvent($query)
    {
        return $query->whereNotNull('event_id');
    }

    /**
     * Scope to filter tracks without an associated event.
     */
    public function scopeWithoutEvent($query)
    {
        return $query->whereNull('event_id');
    }

    /**
     * Scope to filter tracks within a date range.
     */
    public function scopeDepartedBetween($query, $start, $end)
    {
        return $query->whereBetween('departure_time', [$start, $end]);
    }

    /**
     * Scope to filter tracks by departure port.
     */
    public function scopeFromPort($query, string $port)
    {
        return $query->where('departure_port', 'like', "%{$port}%");
    }

    /**
     * Scope to filter tracks by arrival port.
     */
    public function scopeToPort($query, string $port)
    {
        return $query->where('arrival_port', 'like', "%{$port}%");
    }

    /**
     * Scope to filter tracks that pass through a geographic zone.
     */
    public function scopePassesThrough($query, string $polygonWkt)
    {
        return $query->whereRaw(
            "ST_Intersects(track_path, ST_GeomFromText(?, 4326))",
            [$polygonWkt]
        );
    }

    /**
     * Get all voyage types.
     *
     * @return array<string, string>
     */
    public static function getVoyageTypes(): array
    {
        return [
            self::VOYAGE_LADEN => 'Laden (Loaded)',
            self::VOYAGE_BALLAST => 'Ballast (Empty)',
            self::VOYAGE_UNKNOWN => 'Unknown',
        ];
    }

    /**
     * Get display name for voyage type.
     */
    public function getVoyageTypeDisplayAttribute(): string
    {
        return self::getVoyageTypes()[$this->voyage_type] ?? 'Unknown';
    }

    /**
     * Link this track to an event.
     */
    public function linkToEvent(Event $event): void
    {
        $this->update(['event_id' => $event->id]);
    }

    /**
     * Unlink this track from its event.
     */
    public function unlinkFromEvent(): void
    {
        $this->update(['event_id' => null]);
    }
}
