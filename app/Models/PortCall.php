<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

/**
 * PortCall Model
 *
 * Represents a vessel's visit to a port.
 *
 * @property int $id
 * @property string $uuid
 * @property int $vessel_id
 * @property string $port_name Name of the port
 * @property string|null $port_code UN/LOCODE or port code
 * @property int|null $port_country_id Country where port is located
 * @property \Illuminate\Database\Query\Expression|null $position Geographic position
 * @property \Carbon\Carbon|null $arrival_time Actual arrival time
 * @property \Carbon\Carbon|null $departure_time Actual departure time
 * @property int|null $duration_hours Duration of port call
 * @property string|null $previous_port Previous port of call
 * @property string|null $next_port Next port of call
 * @property string $call_type Purpose of port call
 * @property string $data_source Source of port call data
 * @property array|null $metadata Additional metadata
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property-read TrackedVessel $vessel
 * @property-read Country|null $portCountry
 */
class PortCall extends Model
{
    use HasFactory;

    /**
     * Call type constants.
     */
    public const CALL_LOADING = 'loading';
    public const CALL_UNLOADING = 'unloading';
    public const CALL_BUNKERING = 'bunkering';
    public const CALL_REPAIR = 'repair';
    public const CALL_CREW_CHANGE = 'crew_change';
    public const CALL_TRANSIT = 'transit';
    public const CALL_UNKNOWN = 'unknown';

    /**
     * Data source constants.
     */
    public const SOURCE_MARINETRAFFIC = 'marinetraffic';
    public const SOURCE_VESSELFINDER = 'vesselfinder';
    public const SOURCE_AIS_RECEIVER = 'ais_receiver';
    public const SOURCE_MYSHIPTRACKING = 'myshiptracking';
    public const SOURCE_PORT_AUTHORITY = 'port_authority';
    public const SOURCE_MANUAL = 'manual';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'uuid',
        'vessel_id',
        'port_name',
        'port_code',
        'port_country_id',
        'position',
        'arrival_time',
        'departure_time',
        'duration_hours',
        'previous_port',
        'next_port',
        'call_type',
        'data_source',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'arrival_time' => 'datetime',
        'departure_time' => 'datetime',
        'duration_hours' => 'integer',
        'metadata' => 'array',
    ];

    /**
     * Get the vessel that made this port call.
     */
    public function vessel(): BelongsTo
    {
        return $this->belongsTo(TrackedVessel::class, 'vessel_id');
    }

    /**
     * Get the country where the port is located.
     */
    public function portCountry(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'port_country_id');
    }

    /**
     * Get position coordinates.
     *
     * @return array{lat: float, lng: float}|null
     */
    public function getCoordinatesAttribute(): ?array
    {
        $result = DB::selectOne(
            "SELECT ST_X(position) as lng, ST_Y(position) as lat FROM port_calls WHERE id = ?",
            [$this->id]
        );

        if (!$result || ($result->lat === null && $result->lng === null)) {
            return null;
        }

        return [
            'lat' => (float) $result->lat,
            'lng' => (float) $result->lng,
        ];
    }

    /**
     * Set position from coordinates.
     *
     * @param float $latitude
     * @param float $longitude
     */
    public function setPositionFromCoordinates(float $latitude, float $longitude): void
    {
        $this->position = DB::raw(
            sprintf("ST_GeomFromText('POINT(%f %f)', 4326)", $longitude, $latitude)
        );
    }

    /**
     * Calculate duration if not set.
     */
    public function calculateDuration(): void
    {
        if ($this->arrival_time && $this->departure_time) {
            $this->duration_hours = (int) $this->arrival_time->diffInHours($this->departure_time);
            $this->save();
        }
    }

    /**
     * Check if vessel is currently at this port.
     */
    public function isCurrentlyAtPort(): bool
    {
        return $this->arrival_time !== null
            && $this->arrival_time->isPast()
            && ($this->departure_time === null || $this->departure_time->isFuture());
    }

    /**
     * Scope to filter by vessel.
     */
    public function scopeForVessel($query, int $vesselId)
    {
        return $query->where('vessel_id', $vesselId);
    }

    /**
     * Scope to filter by port name.
     */
    public function scopeAtPort($query, string $portName)
    {
        return $query->where('port_name', 'like', "%{$portName}%");
    }

    /**
     * Scope to filter by port code.
     */
    public function scopeWithCode($query, string $code)
    {
        return $query->where('port_code', $code);
    }

    /**
     * Scope to filter by port country.
     */
    public function scopeInCountry($query, int $countryId)
    {
        return $query->where('port_country_id', $countryId);
    }

    /**
     * Scope to filter by call type.
     */
    public function scopeOfCallType($query, string $type)
    {
        return $query->where('call_type', $type);
    }

    /**
     * Scope to filter port calls within date range.
     */
    public function scopeArrivedBetween($query, $start, $end)
    {
        return $query->whereBetween('arrival_time', [$start, $end]);
    }

    /**
     * Scope to filter current port calls (vessel still at port).
     */
    public function scopeCurrentCalls($query)
    {
        return $query->whereNotNull('arrival_time')
            ->where('arrival_time', '<=', now())
            ->where(function ($q) {
                $q->whereNull('departure_time')
                    ->orWhere('departure_time', '>', now());
            });
    }

    /**
     * Scope to filter completed port calls.
     */
    public function scopeCompleted($query)
    {
        return $query->whereNotNull('arrival_time')
            ->whereNotNull('departure_time')
            ->where('departure_time', '<=', now());
    }

    /**
     * Scope to order by arrival time (most recent first).
     */
    public function scopeRecentFirst($query)
    {
        return $query->orderBy('arrival_time', 'desc');
    }

    /**
     * Get all call types.
     *
     * @return array<string, string>
     */
    public static function getCallTypes(): array
    {
        return [
            self::CALL_LOADING => 'Loading',
            self::CALL_UNLOADING => 'Unloading',
            self::CALL_BUNKERING => 'Bunkering',
            self::CALL_REPAIR => 'Repair',
            self::CALL_CREW_CHANGE => 'Crew Change',
            self::CALL_TRANSIT => 'Transit',
            self::CALL_UNKNOWN => 'Unknown',
        ];
    }

    /**
     * Get all data sources.
     *
     * @return array<string, string>
     */
    public static function getDataSources(): array
    {
        return [
            self::SOURCE_MARINETRAFFIC => 'MarineTraffic',
            self::SOURCE_VESSELFINDER => 'VesselFinder',
            self::SOURCE_AIS_RECEIVER => 'AIS Receiver',
            self::SOURCE_MYSHIPTRACKING => 'MyShipTracking',
            self::SOURCE_PORT_AUTHORITY => 'Port Authority',
            self::SOURCE_MANUAL => 'Manual Entry',
        ];
    }

    /**
     * Get display name for call type.
     */
    public function getCallTypeDisplayAttribute(): string
    {
        return self::getCallTypes()[$this->call_type] ?? 'Unknown';
    }

    /**
     * Get display name for data source.
     */
    public function getDataSourceDisplayAttribute(): string
    {
        return self::getDataSources()[$this->data_source] ?? 'Unknown';
    }
}
