<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Conflict Model
 *
 * Represents an armed conflict or dispute tracked by the system.
 *
 * @property int $id
 * @property string $uuid
 * @property string $name
 * @property string|null $short_name
 * @property string|null $alias_names
 * @property string|null $conflict_type
 * @property string $intensity_level
 * @property int|null $primary_country_id
 * @property string|null $affected_countries
 * @property string|null $region
 * @property \Carbon\Carbon $start_date
 * @property \Carbon\Carbon|null $end_date
 * @property bool $is_active
 * @property array|null $estimated_casualties
 * @property string|null $description
 * @property string|null $background
 * @property string|null $wikipedia_url
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 *
 * @property-read \App\Models\Country|null $primaryCountry
 * @property-read \Illuminate\Database\Eloquent\Collection<\App\Models\Actor> $actors
 * @property-read \Illuminate\Database\Eloquent\Collection<\App\Models\Country> $countries
 */
class Conflict extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'name',
        'short_name',
        'alias_names',
        'conflict_type',
        'intensity_level',
        'primary_country_id',
        'affected_countries',
        'region',
        'start_date',
        'end_date',
        'is_active',
        'estimated_casualties',
        'description',
        'background',
        'wikipedia_url',
    ];

    protected $casts = [
        'alias_names' => 'array',
        'affected_countries' => 'array',
        'estimated_casualties' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Get the primary country for this conflict
     */
    public function primaryCountry(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'primary_country_id');
    }

    /**
     * Get the actors involved in this conflict
     */
    public function actors(): BelongsToMany
    {
        return $this->belongsToMany(Actor::class, 'conflict_parties')
            ->withPivot(['side', 'role', 'joined_date', 'left_date', 'is_currently_active', 'notes'])
            ->withTimestamps();
    }

    /**
     * Get the affected countries for this conflict
     */
    public function countries(): BelongsToMany
    {
        return $this->belongsToMany(Country::class, 'conflict_countries')
            ->withTimestamps();
    }

    /**
     * Scope to filter active conflicts
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by intensity level
     */
    public function scopeIntensity($query, string $level)
    {
        return $query->where('intensity_level', $level);
    }

    /**
     * Scope to filter by region
     */
    public function scopeRegion($query, string $region)
    {
        return $query->where('region', $region);
    }
}
