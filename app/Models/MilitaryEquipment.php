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
 * @property string $designation
 * @property string|null $nato_designation
 * @property string|null $common_name
 * @property int $country_id
 * @property int $category_id
 * @property array|null $specifications
 * @property int|null $introduced_year
 * @property int|null $estimated_produced
 * @property string|null $description
 * @property string|null $image_url
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
        'designation',
        'nato_designation',
        'common_name',
        'country_id',
        'category_id',
        'specifications',
        'introduced_year',
        'estimated_produced',
        'description',
        'image_url',
    ];

    protected $casts = [
        'specifications' => 'array',
        'introduced_year' => 'integer',
        'estimated_produced' => 'integer',
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
     * Scope to search by designation, common name, or NATO designation
     */
    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('designation', 'like', "%{$term}%")
                ->orWhere('common_name', 'like', "%{$term}%")
                ->orWhere('nato_designation', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%");
        });
    }

    /**
     * Scope to filter by year range
     */
    public function scopeYearRange($query, ?int $from = null, ?int $to = null)
    {
        if ($from !== null) {
            $query->where('introduced_year', '>=', $from);
        }

        if ($to !== null) {
            $query->where('introduced_year', '<=', $to);
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
