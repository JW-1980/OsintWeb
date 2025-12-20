<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Actor Model
 *
 * Represents an individual person or entity involved in events.
 *
 * @property int $id
 * @property string $uuid
 * @property string $name
 * @property string|null $full_name
 * @property string|null $role
 * @property string|null $rank
 * @property string|null $affiliation
 * @property string|null $nationality
 * @property string|null $photo_url
 * @property string|null $biography
 * @property \Carbon\Carbon|null $date_of_birth
 * @property \Carbon\Carbon|null $date_of_death
 * @property bool $is_active
 * @property bool $is_verified
 * @property array|null $aliases
 * @property array|null $metadata
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class Actor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'name',
        'full_name',
        'role',
        'rank',
        'affiliation',
        'nationality',
        'photo_url',
        'biography',
        'date_of_birth',
        'date_of_death',
        'is_active',
        'is_verified',
        'aliases',
        'metadata',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'date_of_death' => 'date',
        'is_active' => 'boolean',
        'is_verified' => 'boolean',
        'aliases' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Get the events this actor is involved in
     */
    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'actor_event')
            ->withPivot(['role', 'notes'])
            ->withTimestamps();
    }

    /**
     * Get relationships with other actors
     */
    public function relationships(): HasMany
    {
        return $this->hasMany(ActorRelationship::class, 'actor_id');
    }

    /**
     * Get inverse relationships with other actors
     */
    public function inverseRelationships(): HasMany
    {
        return $this->hasMany(ActorRelationship::class, 'related_actor_id');
    }

    /**
     * Scope to filter active actors
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter verified actors
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope to filter by affiliation
     */
    public function scopeAffiliation($query, string $affiliation)
    {
        return $query->where('affiliation', $affiliation);
    }

    /**
     * Scope to filter living actors
     */
    public function scopeLiving($query)
    {
        return $query->whereNull('date_of_death');
    }

    /**
     * Scope to search by name or aliases
     */
    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('full_name', 'like', "%{$term}%");
        });
    }

    /**
     * Get all names including aliases
     */
    public function getAllNamesAttribute(): array
    {
        $names = [$this->name];

        if ($this->full_name !== null && $this->full_name !== $this->name) {
            $names[] = $this->full_name;
        }

        if ($this->aliases !== null) {
            $names = array_merge($names, $this->aliases);
        }

        return array_unique($names);
    }
}
