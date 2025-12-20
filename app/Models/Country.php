<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Country Model
 *
 * Represents a country or territory.
 *
 * @property int $id
 * @property string $uuid
 * @property string $name
 * @property string $iso_code_2
 * @property string|null $iso_code_3
 * @property string|null $flag_emoji
 * @property string|null $flag_url
 * @property string|null $capital
 * @property int|null $population
 * @property float|null $area_km2
 * @property string|null $region
 * @property string|null $subregion
 * @property array|null $calling_codes
 * @property array|null $timezones
 * @property array|null $currencies
 * @property array|null $languages
 * @property bool $is_active
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Country extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'name',
        'iso_code_2',
        'iso_code_3',
        'flag_emoji',
        'flag_url',
        'capital',
        'population',
        'area_km2',
        'region',
        'subregion',
        'calling_codes',
        'timezones',
        'currencies',
        'languages',
        'is_active',
    ];

    protected $casts = [
        'population' => 'integer',
        'area_km2' => 'decimal:2',
        'calling_codes' => 'array',
        'timezones' => 'array',
        'currencies' => 'array',
        'languages' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the factions associated with this country
     */
    public function factions(): HasMany
    {
        return $this->hasMany(Faction::class);
    }

    /**
     * Get the equipment inventory for this country
     */
    public function equipmentInventories(): HasMany
    {
        return $this->hasMany(CountryEquipment::class);
    }

    /**
     * Get the military equipment through the inventory relationship
     */
    public function equipment(): HasMany
    {
        return $this->hasMany(MilitaryEquipment::class);
    }

    /**
     * Scope to filter active countries
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by region
     */
    public function scopeRegion($query, string $region)
    {
        return $query->where('region', $region);
    }

    /**
     * Scope to filter by subregion
     */
    public function scopeSubregion($query, string $subregion)
    {
        return $query->where('subregion', $subregion);
    }

    /**
     * Scope to search by name or code
     */
    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('iso_code_2', 'like', "%{$term}%")
                ->orWhere('iso_code_3', 'like', "%{$term}%");
        });
    }
}
