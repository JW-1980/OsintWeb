<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Conflict Model
 *
 * Represents an armed conflict or military engagement.
 */
class Conflict extends Model
{
    use SoftDeletes;

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
        'is_active' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Get the primary country for this conflict.
     */
    public function primaryCountry(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'primary_country_id');
    }

    /**
     * Get the actors (parties) involved in this conflict.
     */
    public function actors(): BelongsToMany
    {
        return $this->belongsToMany(Actor::class, 'conflict_parties')
            ->withPivot(['side', 'role', 'joined_date', 'left_date', 'is_currently_active'])
            ->withTimestamps();
    }

    /**
     * Scope to filter active conflicts.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
