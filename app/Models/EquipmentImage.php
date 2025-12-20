<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * EquipmentImage Model
 *
 * Represents an image attached to military equipment.
 *
 * @property int $id
 * @property string $uuid
 * @property int $equipment_id
 * @property string $url
 * @property string|null $thumbnail_url
 * @property string|null $title
 * @property string|null $description
 * @property string|null $alt_text
 * @property string|null $source_url
 * @property string|null $source_name
 * @property string|null $license
 * @property int|null $width
 * @property int|null $height
 * @property int|null $file_size
 * @property string|null $mime_type
 * @property bool $is_primary
 * @property bool $is_public
 * @property bool $is_verified
 * @property int|null $uploaded_by
 * @property int $sort_order
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class EquipmentImage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'equipment_id',
        'url',
        'thumbnail_url',
        'title',
        'description',
        'alt_text',
        'source_url',
        'source_name',
        'license',
        'width',
        'height',
        'file_size',
        'mime_type',
        'is_primary',
        'is_public',
        'is_verified',
        'uploaded_by',
        'sort_order',
    ];

    protected $casts = [
        'width' => 'integer',
        'height' => 'integer',
        'file_size' => 'integer',
        'is_primary' => 'boolean',
        'is_public' => 'boolean',
        'is_verified' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (EquipmentImage $image) {
            if (empty($image->uuid)) {
                $image->uuid = (string) Str::uuid();
            }
        });

        // Ensure only one primary image per equipment
        static::saved(function (EquipmentImage $image) {
            if ($image->is_primary) {
                static::where('equipment_id', $image->equipment_id)
                    ->where('id', '!=', $image->id)
                    ->update(['is_primary' => false]);
            }
        });
    }

    /**
     * Get the equipment this image belongs to
     */
    public function equipment(): BelongsTo
    {
        return $this->belongsTo(MilitaryEquipment::class, 'equipment_id');
    }

    /**
     * Get the user who uploaded this image
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Scope to filter public images
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope to filter primary images
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    /**
     * Scope to order by sort order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}
