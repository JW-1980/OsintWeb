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
 * Represents a state or non-state actor (military forces, insurgent groups, militias, etc.)
 * involved in conflict events. Supports autocomplete with priority scoring.
 *
 * Database Table: actors
 *
 * Database Schema:
 * - id: bigint unsigned, auto-increment, primary key
 * - uuid: char(36), unique
 * - name: varchar(500), indexed
 * - short_name: varchar(100), nullable, indexed
 * - alias_names: json, nullable
 * - actor_type: enum('STATE','SEPARATIST','INSURGENT','TERRORIST','MILITIA','PMC','CARTEL','REBEL','ETHNIC_MILITIA','GOVERNMENT_FORCES','COALITION','PROXY'), indexed
 * - country_id: bigint unsigned, nullable, foreign key to countries
 * - primary_region: varchar(100), nullable, indexed
 * - operational_areas: json, nullable
 * - is_state_actor: boolean, default false, indexed
 * - is_designated_terrorist: boolean, default false, indexed
 * - designations: json, nullable (us, eu, un designation flags)
 * - is_active_in_conflict: boolean, default false, indexed
 * - activity_level: enum('high','medium','low','inactive'), default 'inactive', indexed
 * - last_activity_date: date, nullable, indexed
 * - autocomplete_priority: int, default 0, indexed
 * - priority_score: decimal(5,2), default 0.0, indexed
 * - flag_emoji: varchar(10), nullable
 * - logo_url: varchar(500), nullable
 * - flag_url: varchar(500), nullable
 * - color_hex: varchar(7), nullable
 * - icon: varchar(100), nullable
 * - description: text, nullable
 * - founded_date: date, nullable
 * - dissolved_date: date, nullable
 * - successor_id: bigint unsigned, nullable
 * - parent_organization_id: bigint unsigned, nullable
 * - wikipedia_url: varchar(500), nullable
 * - official_website: varchar(500), nullable
 * - deleted_at: timestamp, nullable
 * - created_at: timestamp, nullable
 * - updated_at: timestamp, nullable
 *
 * @property int $id
 * @property string $uuid
 * @property string $name
 * @property string|null $short_name
 * @property array|null $alias_names
 * @property string $actor_type
 * @property int|null $country_id
 * @property string|null $primary_region
 * @property array|null $operational_areas
 * @property bool $is_state_actor
 * @property bool $is_designated_terrorist
 * @property array|null $designations
 * @property bool $is_active_in_conflict
 * @property string $activity_level
 * @property \Carbon\Carbon|null $last_activity_date
 * @property int $autocomplete_priority
 * @property float $priority_score
 * @property string|null $flag_emoji
 * @property string|null $logo_url
 * @property string|null $flag_url
 * @property string|null $color_hex
 * @property string|null $icon
 * @property string|null $description
 * @property \Carbon\Carbon|null $founded_date
 * @property \Carbon\Carbon|null $dissolved_date
 * @property int|null $successor_id
 * @property int|null $parent_organization_id
 * @property string|null $wikipedia_url
 * @property string|null $official_website
 * @property \Carbon\Carbon|null $deleted_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 *
 * @property-read array $all_names
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<\App\Models\Event> $events
 * @property-read \Illuminate\Database\Eloquent\Collection<\App\Models\ActorRelationship> $relationships
 * @property-read \Illuminate\Database\Eloquent\Collection<\App\Models\ActorRelationship> $inverseRelationships
 *
 * @method static \Illuminate\Database\Eloquent\Builder active()
 * @method static \Illuminate\Database\Eloquent\Builder verified()
 * @method static \Illuminate\Database\Eloquent\Builder affiliation(string $affiliation)
 * @method static \Illuminate\Database\Eloquent\Builder living()
 * @method static \Illuminate\Database\Eloquent\Builder search(string $term)
 */
class Actor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'name',
        'short_name',
        'alias_names',
        'actor_type',
        'country_id',
        'primary_region',
        'operational_areas',
        'is_state_actor',
        'is_designated_terrorist',
        'designations',
        'is_active_in_conflict',
        'activity_level',
        'last_activity_date',
        'autocomplete_priority',
        'priority_score',
        'flag_emoji',
        'logo_url',
        'flag_url',
        'color_hex',
        'icon',
        'description',
        'founded_date',
        'dissolved_date',
        'successor_id',
        'parent_organization_id',
        'wikipedia_url',
        'official_website',
    ];

    protected $casts = [
        'alias_names' => 'array',
        'operational_areas' => 'array',
        'designations' => 'array',
        'is_state_actor' => 'boolean',
        'is_designated_terrorist' => 'boolean',
        'is_active_in_conflict' => 'boolean',
        'last_activity_date' => 'date',
        'priority_score' => 'decimal:2',
        'founded_date' => 'date',
        'dissolved_date' => 'date',
    ];

    /**
     * Get the actor's aliases
     */
    public function actorAliases(): HasMany
    {
        return $this->hasMany(ActorAlias::class);
    }

    /**
     * Alias for actorAliases for convenience
     */
    public function aliases(): HasMany
    {
        return $this->actorAliases();
    }

    /**
     * Get the conflicts this actor is involved in
     */
    public function conflicts(): BelongsToMany
    {
        return $this->belongsToMany(Conflict::class, 'conflict_parties')
            ->withPivot(['side', 'role', 'joined_date', 'left_date', 'is_currently_active', 'notes'])
            ->withTimestamps();
    }

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

        if ($this->short_name) {
            $names[] = $this->short_name;
        }

        // Legacy full_name support (if property exists)
        if (isset($this->full_name) && $this->full_name !== $this->name) {
            $names[] = $this->full_name;
        }

        // Get aliases from JSON column (deprecated)
        if (!empty($this->alias_names)) {
            $names = array_merge($names, $this->alias_names);
        }

        // Legacy aliases property support
        if (isset($this->aliases) && is_array($this->aliases)) {
            $names = array_merge($names, $this->aliases);
        }

        // Get aliases from relationship (new source of truth)
        $relatedAliases = $this->actorAliases->pluck('alias')->toArray();
        if (!empty($relatedAliases)) {
            $names = array_merge($names, $relatedAliases);
        }

        return array_unique($names);
    }
}
