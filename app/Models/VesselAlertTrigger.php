<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

/**
 * VesselAlertTrigger Model
 *
 * Represents a single trigger event for a vessel alert.
 *
 * @property int $id
 * @property string $uuid
 * @property int $alert_id
 * @property int|null $vessel_id
 * @property int|null $position_id
 * @property \Illuminate\Database\Query\Expression|null $trigger_position Position where alert was triggered
 * @property array|null $trigger_data Data that triggered the alert
 * @property string|null $message Alert message sent
 * @property bool $was_notified Whether notification was sent
 * @property \Carbon\Carbon $triggered_at
 * @property \Carbon\Carbon|null $acknowledged_at
 * @property int|null $acknowledged_by
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property-read VesselAlert $alert
 * @property-read TrackedVessel|null $vessel
 * @property-read VesselPosition|null $position
 * @property-read User|null $acknowledgedByUser
 */
class VesselAlertTrigger extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'uuid',
        'alert_id',
        'vessel_id',
        'position_id',
        'trigger_position',
        'trigger_data',
        'message',
        'was_notified',
        'triggered_at',
        'acknowledged_at',
        'acknowledged_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'trigger_data' => 'array',
        'was_notified' => 'boolean',
        'triggered_at' => 'datetime',
        'acknowledged_at' => 'datetime',
    ];

    /**
     * Get the alert this trigger belongs to.
     */
    public function alert(): BelongsTo
    {
        return $this->belongsTo(VesselAlert::class, 'alert_id');
    }

    /**
     * Get the vessel that triggered this alert.
     */
    public function vessel(): BelongsTo
    {
        return $this->belongsTo(TrackedVessel::class, 'vessel_id');
    }

    /**
     * Get the position that triggered this alert.
     */
    public function position(): BelongsTo
    {
        return $this->belongsTo(VesselPosition::class, 'position_id');
    }

    /**
     * Get the user who acknowledged this trigger.
     */
    public function acknowledgedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acknowledged_by');
    }

    /**
     * Get trigger position coordinates.
     *
     * @return array{lat: float, lng: float}|null
     */
    public function getTriggerCoordinatesAttribute(): ?array
    {
        if (!$this->trigger_position) {
            return null;
        }

        $result = DB::selectOne(
            "SELECT ST_X(trigger_position) as lng, ST_Y(trigger_position) as lat FROM vessel_alert_triggers WHERE id = ?",
            [$this->id]
        );

        if (!$result) {
            return null;
        }

        return [
            'lat' => (float) $result->lat,
            'lng' => (float) $result->lng,
        ];
    }

    /**
     * Mark as notified.
     */
    public function markNotified(): void
    {
        $this->update(['was_notified' => true]);
    }

    /**
     * Acknowledge the trigger.
     *
     * @param User $user
     */
    public function acknowledge(User $user): void
    {
        $this->update([
            'acknowledged_at' => now(),
            'acknowledged_by' => $user->id,
        ]);
    }

    /**
     * Scope to filter unacknowledged triggers.
     */
    public function scopeUnacknowledged($query)
    {
        return $query->whereNull('acknowledged_at');
    }

    /**
     * Scope to filter acknowledged triggers.
     */
    public function scopeAcknowledged($query)
    {
        return $query->whereNotNull('acknowledged_at');
    }

    /**
     * Scope to filter triggers within date range.
     */
    public function scopeTriggeredBetween($query, $start, $end)
    {
        return $query->whereBetween('triggered_at', [$start, $end]);
    }

    /**
     * Scope to filter by alert.
     */
    public function scopeForAlert($query, int $alertId)
    {
        return $query->where('alert_id', $alertId);
    }

    /**
     * Check if trigger is acknowledged.
     */
    public function isAcknowledged(): bool
    {
        return $this->acknowledged_at !== null;
    }
}
