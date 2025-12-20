<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Country Equipment Model
 *
 * Represents the inventory of military equipment for a specific country.
 *
 * @property int $id
 * @property string $uuid
 * @property int $country_id
 * @property int $equipment_id
 * @property int|null $quantity
 * @property string|null $status
 * @property string|null $source
 * @property string|null $notes
 * @property \Carbon\Carbon|null $verified_at
 * @property array|null $metadata
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class CountryEquipment extends Model
{
    use HasFactory;

    protected $table = 'country_equipment';

    protected $fillable = [
        'uuid',
        'country_id',
        'equipment_id',
        'quantity',
        'status',
        'source',
        'notes',
        'verified_at',
        'metadata',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'verified_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Get the country that owns this equipment
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Get the equipment details
     */
    public function equipment(): BelongsTo
    {
        return $this->belongsTo(MilitaryEquipment::class, 'equipment_id');
    }

    /**
     * Scope to filter by country
     */
    public function scopeCountry($query, int $countryId)
    {
        return $query->where('country_id', $countryId);
    }

    /**
     * Scope to filter by equipment
     */
    public function scopeEquipment($query, int $equipmentId)
    {
        return $query->where('equipment_id', $equipmentId);
    }

    /**
     * Scope to filter by status
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter verified records
     */
    public function scopeVerified($query)
    {
        return $query->whereNotNull('verified_at');
    }

    /**
     * Scope to filter active equipment
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['active', 'operational', 'deployed']);
    }
}
