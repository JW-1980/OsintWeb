<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * EquipmentPropertyCategory Model
 *
 * Represents a category for organizing equipment properties.
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property int $sort_order
 * @property string|null $icon
 * @property bool $is_active
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class EquipmentPropertyCategory extends Model
{
    use HasFactory;

    protected $table = 'equipment_property_categories';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'sort_order',
        'icon',
        'is_active',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Get the properties in this category
     */
    public function properties(): HasMany
    {
        return $this->hasMany(EquipmentProperty::class, 'category_id');
    }

    /**
     * Scope to filter active categories
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
}
