<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Faction Model
 *
 * Represents a military or political faction (government, rebel group, etc.).
 *
 * @property int $id
 * @property string $uuid
 * @property string $name
 * @property string|null $short_name
 * @property int|null $country_id
 * @property int|null $parent_id
 * @property string $type
 * @property string|null $ideology
 * @property string|null $flag_url
 * @property string|null $logo_url
 * @property string|null $description
 * @property \Carbon\Carbon|null $founded_date
 * @property \Carbon\Carbon|null $dissolved_date
 * @property int|null $estimated_strength
 * @property bool $is_active
 * @property bool $is_verified
 * @property array|null $aliases
 * @property array|null $metadata
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class Faction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'name',
        'short_name',
        'country_id',
        'parent_id',
        'type',
        'ideology',
        'flag_url',
        'logo_url',
        'description',
        'founded_date',
        'dissolved_date',
        'estimated_strength',
        'is_active',
        'is_verified',
        'aliases',
        'metadata',
    ];

    protected $casts = [
        'founded_date' => 'date',
        'dissolved_date' => 'date',
        'estimated_strength' => 'integer',
        'is_active' => 'boolean',
        'is_verified' => 'boolean',
        'aliases' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Get the country this faction belongs to
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Get the parent faction
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Get the child factions
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * Get the control zones controlled by this faction
     */
    public function controlZones(): HasMany
    {
        return $this->hasMany(ControlZone::class, 'controller_id');
    }

    /**
     * Get the events involving this faction
     */
    public function events(): HasMany
    {
        return $this->hasMany(EventEquipment::class, 'operator_faction_id');
    }

    /**
     * Scope to filter active factions
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter verified factions
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope to filter by type
     */
    public function scopeType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to filter government factions
     */
    public function scopeGovernment($query)
    {
        return $query->where('type', 'government');
    }

    /**
     * Scope to filter by country
     */
    public function scopeCountry($query, int $countryId)
    {
        return $query->where('country_id', $countryId);
    }

    /**
     * Scope to search by name or aliases
     */
    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('short_name', 'like', "%{$term}%");
        });
    }
}
