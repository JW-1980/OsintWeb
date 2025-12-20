<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Military Equipment Model
 *
 * Represents a type of military equipment (vehicle, weapon, aircraft, etc.).
 *
 * @property int $id
 * @property string $uuid
 * @property string $name
 * @property string|null $model_number
 * @property string|null $nato_designation
 * @property int|null $category_id
 * @property int|null $country_id
 * @property string|null $manufacturer
 * @property string|null $description
 * @property array|null $specifications
 * @property array|null $images
 * @property int|null $year_introduced
 * @property int|null $year_retired
 * @property bool $is_active
 * @property bool $is_verified
 * @property array|null $metadata
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class MilitaryEquipment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'military_equipment';

    protected $fillable = [
        'uuid',
        'name',
        'model_number',
        'nato_designation',
        'category_id',
        'country_id',
        'manufacturer',
        'description',
        'specifications',
        'images',
        'year_introduced',
        'year_retired',
        'is_active',
        'is_verified',
        'metadata',
    ];

    protected $casts = [
        'specifications' => 'array',
        'images' => 'array',
        'year_introduced' => 'integer',
        'year_retired' => 'integer',
        'is_active' => 'boolean',
        'is_verified' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Get the country of origin
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Get the category this equipment belongs to
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(EquipmentCategory::class);
    }

    /**
     * Get the country inventories for this equipment
     */
    public function countryInventories(): HasMany
    {
        return $this->hasMany(CountryEquipment::class, 'equipment_id');
    }

    /**
     * Get the events involving this equipment
     */
    public function events(): HasMany
    {
        return $this->hasMany(EventEquipment::class, 'equipment_id');
    }

    /**
     * Get countries that have this equipment through the inventory
     */
    public function countries(): BelongsToMany
    {
        return $this->belongsToMany(Country::class, 'country_equipment')
            ->withPivot(['quantity', 'status', 'source', 'verified_at'])
            ->withTimestamps();
    }

    /**
     * Get the extensible properties for this equipment
     */
    public function properties(): HasMany
    {
        return $this->hasMany(EquipmentProperty::class, 'equipment_id')
            ->orderBy('sort_order');
    }

    /**
     * Get properties by category
     */
    public function propertiesByCategory(?int $categoryId = null): HasMany
    {
        $query = $this->properties();

        if ($categoryId !== null) {
            $query->where('category_id', $categoryId);
        }

        return $query;
    }

    /**
     * Get properties by type
     */
    public function propertiesByType(string $type): HasMany
    {
        return $this->properties()->where('type', $type);
    }

    /**
     * Get the images attached to this equipment
     */
    public function equipmentImages(): HasMany
    {
        return $this->hasMany(EquipmentImage::class, 'equipment_id')
            ->orderBy('sort_order');
    }

    /**
     * Get the primary image
     */
    public function primaryImage(): HasMany
    {
        return $this->hasMany(EquipmentImage::class, 'equipment_id')
            ->where('is_primary', true)
            ->limit(1);
    }

    /**
     * Get the links attached to this equipment
     */
    public function links(): HasMany
    {
        return $this->hasMany(EquipmentLink::class, 'equipment_id')
            ->orderBy('sort_order');
    }

    /**
     * Get the videos attached to this equipment
     */
    public function videos(): HasMany
    {
        return $this->hasMany(EquipmentVideo::class, 'equipment_id')
            ->orderBy('sort_order');
    }

    /**
     * Add a property to this equipment
     */
    public function addProperty(array $data): EquipmentProperty
    {
        return $this->properties()->create($data);
    }

    /**
     * Add an image to this equipment
     */
    public function addImage(array $data): EquipmentImage
    {
        return $this->equipmentImages()->create($data);
    }

    /**
     * Add a link to this equipment
     */
    public function addLink(array $data): EquipmentLink
    {
        return $this->links()->create($data);
    }

    /**
     * Add a video to this equipment
     */
    public function addVideo(array $data): EquipmentVideo
    {
        return $this->videos()->create($data);
    }

    /**
     * Scope to filter active equipment
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter verified equipment
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope to filter by category
     */
    public function scopeCategory($query, int $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope to filter by country of origin
     */
    public function scopeOriginCountry($query, int $countryId)
    {
        return $query->where('country_id', $countryId);
    }

    /**
     * Scope to search by name, model, or NATO designation
     */
    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('model_number', 'like', "%{$term}%")
                ->orWhere('nato_designation', 'like', "%{$term}%")
                ->orWhere('manufacturer', 'like', "%{$term}%");
        });
    }

    /**
     * Scope to filter by year range
     */
    public function scopeYearRange($query, ?int $from = null, ?int $to = null)
    {
        if ($from !== null) {
            $query->where('year_introduced', '>=', $from);
        }

        if ($to !== null) {
            $query->where(function ($q) use ($to) {
                $q->where('year_retired', '<=', $to)
                    ->orWhereNull('year_retired');
            });
        }

        return $query;
    }

    /**
     * Scope to include all related media
     */
    public function scopeWithAllMedia($query)
    {
        return $query->with(['equipmentImages', 'links', 'videos', 'properties']);
    }
}
