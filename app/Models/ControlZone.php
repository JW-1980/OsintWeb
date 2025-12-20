<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Control Zone Model
 *
 * Represents a geographic area controlled by a faction.
 *
 * @property int $id
 * @property string $uuid
 * @property string $name
 * @property int $controller_id
 * @property \Illuminate\Database\Query\Expression|null $geometry
 * @property string|null $description
 * @property \Carbon\Carbon|null $controlled_from
 * @property \Carbon\Carbon|null $controlled_until
 * @property bool $is_active
 * @property bool $is_verified
 * @property string|null $color
 * @property int|null $confidence_score
 * @property string|null $source
 * @property array|null $metadata
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class ControlZone extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'name',
        'controller_id',
        'geometry',
        'description',
        'controlled_from',
        'controlled_until',
        'is_active',
        'is_verified',
        'color',
        'confidence_score',
        'source',
        'metadata',
    ];

    protected $casts = [
        'controlled_from' => 'datetime',
        'controlled_until' => 'datetime',
        'is_active' => 'boolean',
        'is_verified' => 'boolean',
        'confidence_score' => 'integer',
        'metadata' => 'array',
    ];

    /**
     * Get the faction that controls this zone
     */
    public function controller(): BelongsTo
    {
        return $this->belongsTo(Faction::class, 'controller_id');
    }

    /**
     * Scope to filter active zones
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter verified zones
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope to filter by controller faction
     */
    public function scopeController($query, int $factionId)
    {
        return $query->where('controller_id', $factionId);
    }

    /**
     * Scope to filter currently controlled zones
     */
    public function scopeCurrent($query)
    {
        $now = now();
        return $query->where(function ($q) use ($now) {
            $q->where('controlled_from', '<=', $now)
                ->where(function ($sq) use ($now) {
                    $sq->whereNull('controlled_until')
                        ->orWhere('controlled_until', '>=', $now);
                });
        });
    }

    /**
     * Scope to search by name
     */
    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%");
        });
    }

    /**
     * Get geometry as GeoJSON
     */
    public function getGeometryAsGeoJson(): ?array
    {
        if ($this->geometry === null) {
            return null;
        }

        // If using PostGIS, convert from WKT/WKB to GeoJSON
        // This is a placeholder - actual implementation depends on DB
        return json_decode($this->geometry, true);
    }

    /**
     * Set geometry from GeoJSON
     */
    public function setGeometryFromGeoJson(array $geojson): void
    {
        // Convert GeoJSON to PostGIS geometry
        // This is a placeholder - actual implementation depends on DB
        $this->geometry = json_encode($geojson);
    }

    /**
     * Get the area in square kilometers
     */
    public function getAreaKm2(): ?float
    {
        if ($this->geometry === null) {
            return null;
        }

        // Calculate area using PostGIS ST_Area
        // This is a placeholder - actual implementation depends on DB
        return null;
    }
}
