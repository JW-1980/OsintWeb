<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Event Equipment Model
 *
 * Links military equipment to events with additional context.
 *
 * @property int $id
 * @property string $uuid
 * @property int $event_id
 * @property int $equipment_id
 * @property int|null $operator_faction_id
 * @property int|null $quantity
 * @property string|null $status
 * @property string|null $condition
 * @property string|null $notes
 * @property bool $is_confirmed
 * @property array|null $metadata
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class EventEquipment extends Model
{
    use HasFactory;

    protected $table = 'event_equipment';

    protected $fillable = [
        'uuid',
        'event_id',
        'equipment_id',
        'operator_faction_id',
        'quantity',
        'status',
        'condition',
        'notes',
        'is_confirmed',
        'metadata',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'is_confirmed' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Get the event
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the equipment
     */
    public function equipment(): BelongsTo
    {
        return $this->belongsTo(MilitaryEquipment::class, 'equipment_id');
    }

    /**
     * Get the faction operating this equipment
     */
    public function operatorFaction(): BelongsTo
    {
        return $this->belongsTo(Faction::class, 'operator_faction_id');
    }

    /**
     * Scope to filter by event
     */
    public function scopeEvent($query, int $eventId)
    {
        return $query->where('event_id', $eventId);
    }

    /**
     * Scope to filter by equipment
     */
    public function scopeEquipment($query, int $equipmentId)
    {
        return $query->where('equipment_id', $equipmentId);
    }

    /**
     * Scope to filter by operator faction
     */
    public function scopeOperator($query, int $factionId)
    {
        return $query->where('operator_faction_id', $factionId);
    }

    /**
     * Scope to filter confirmed equipment
     */
    public function scopeConfirmed($query)
    {
        return $query->where('is_confirmed', true);
    }

    /**
     * Scope to filter by status
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }
}
