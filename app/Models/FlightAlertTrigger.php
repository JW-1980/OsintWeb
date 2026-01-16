<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Flight Alert Trigger Model
 *
 * Represents a triggered instance of a flight alert.
 *
 * @property int $id
 * @property int $alert_id
 * @property int|null $aircraft_id
 * @property int|null $position_id
 * @property int|null $track_id
 * @property array|null $trigger_data Data that triggered the alert
 * @property bool $is_read
 * @property \Carbon\Carbon $triggered_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property-read FlightAlert $alert
 * @property-read TrackedAircraft|null $aircraft
 * @property-read FlightPosition|null $position
 * @property-read FlightTrack|null $track
 */
class FlightAlertTrigger extends Model
{
    use HasFactory;

    protected $table = 'flight_alert_triggers';

    protected $fillable = [
        'alert_id',
        'aircraft_id',
        'position_id',
        'track_id',
        'trigger_data',
        'is_read',
        'triggered_at',
    ];

    protected $casts = [
        'trigger_data' => 'array',
        'is_read' => 'boolean',
        'triggered_at' => 'datetime',
    ];

    /**
     * Get the alert this trigger belongs to
     */
    public function alert(): BelongsTo
    {
        return $this->belongsTo(FlightAlert::class, 'alert_id');
    }

    /**
     * Get the aircraft that triggered the alert
     */
    public function aircraft(): BelongsTo
    {
        return $this->belongsTo(TrackedAircraft::class, 'aircraft_id');
    }

    /**
     * Get the position that triggered the alert
     */
    public function position(): BelongsTo
    {
        return $this->belongsTo(FlightPosition::class, 'position_id');
    }

    /**
     * Get the track associated with the trigger
     */
    public function track(): BelongsTo
    {
        return $this->belongsTo(FlightTrack::class, 'track_id');
    }

    /**
     * Mark as read
     */
    public function markAsRead(): void
    {
        $this->update(['is_read' => true]);
    }

    /**
     * Mark as unread
     */
    public function markAsUnread(): void
    {
        $this->update(['is_read' => false]);
    }

    /**
     * Scope to filter unread triggers
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope to filter by time range
     */
    public function scopeInTimeRange($query, \Carbon\Carbon $start, \Carbon\Carbon $end)
    {
        return $query->where('triggered_at', '>=', $start)
            ->where('triggered_at', '<=', $end);
    }

    /**
     * Scope to filter by user (via alert)
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->whereHas('alert', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        });
    }
}
