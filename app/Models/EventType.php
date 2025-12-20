<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Event Type Model
 *
 * Defines types of events with custom fields and validation schemas.
 *
 * @property int $id
 * @property string $uuid
 * @property string $name
 * @property string|null $slug
 * @property string|null $description
 * @property string|null $icon
 * @property string|null $color
 * @property array|null $schema
 * @property bool $is_active
 * @property int $sort_order
 * @property array|null $metadata
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class EventType extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'name',
        'slug',
        'description',
        'icon',
        'color',
        'schema',
        'is_active',
        'sort_order',
        'metadata',
    ];

    protected $casts = [
        'schema' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'metadata' => 'array',
    ];

    /**
     * Get the events of this type
     */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class, 'event_type_id');
    }

    /**
     * Scope to filter active event types
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order by sort order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
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
     * Validate custom fields against schema
     */
    public function validateCustomFields(array $fields): bool
    {
        if ($this->schema === null) {
            return true;
        }

        // Basic validation - in production, use JSON Schema validator
        foreach ($this->schema as $fieldName => $fieldSchema) {
            if (($fieldSchema['required'] ?? false) && !isset($fields[$fieldName])) {
                return false;
            }
        }

        return true;
    }
}
