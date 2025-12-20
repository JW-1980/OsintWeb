<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * EquipmentProperty Model
 *
 * Represents an extensible property for military equipment.
 *
 * @property int $id
 * @property string $uuid
 * @property int $equipment_id
 * @property int|null $category_id
 * @property string $name
 * @property string $slug
 * @property string $type
 * @property string|null $value
 * @property array|null $metadata
 * @property int $sort_order
 * @property bool $is_public
 * @property bool $is_verified
 * @property int|null $verified_by
 * @property \Carbon\Carbon|null $verified_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int $version
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class EquipmentProperty extends Model
{
    use HasFactory, SoftDeletes;

    public const TYPE_TEXT = 'text';
    public const TYPE_RICHTEXT = 'richtext';
    public const TYPE_NUMBER = 'number';
    public const TYPE_BOOLEAN = 'boolean';
    public const TYPE_LINK = 'link';
    public const TYPE_IMAGE = 'image';
    public const TYPE_VIDEO = 'video';
    public const TYPE_FILE = 'file';
    public const TYPE_JSON = 'json';
    public const TYPE_DATE = 'date';
    public const TYPE_LOCATION = 'location';

    protected $fillable = [
        'uuid',
        'equipment_id',
        'category_id',
        'name',
        'slug',
        'type',
        'value',
        'metadata',
        'sort_order',
        'is_public',
        'is_verified',
        'verified_by',
        'verified_at',
        'created_by',
        'updated_by',
        'version',
    ];

    protected $casts = [
        'metadata' => 'array',
        'sort_order' => 'integer',
        'is_public' => 'boolean',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
        'version' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (EquipmentProperty $property) {
            if (empty($property->uuid)) {
                $property->uuid = (string) Str::uuid();
            }
            if (empty($property->slug)) {
                $property->slug = Str::slug($property->name);
            }
        });

        static::updating(function (EquipmentProperty $property) {
            // Create version before updating
            $property->createVersion();
            $property->version = $property->version + 1;
        });
    }

    /**
     * Get the equipment this property belongs to
     */
    public function equipment(): BelongsTo
    {
        return $this->belongsTo(MilitaryEquipment::class, 'equipment_id');
    }

    /**
     * Get the category of this property
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(EquipmentPropertyCategory::class, 'category_id');
    }

    /**
     * Get the user who created this property
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this property
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who verified this property
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Get the version history
     */
    public function versions(): HasMany
    {
        return $this->hasMany(EquipmentPropertyVersion::class, 'property_id')
            ->orderByDesc('version_number');
    }

    /**
     * Create a version snapshot
     */
    public function createVersion(?string $reason = null, ?User $changedBy = null): void
    {
        $this->versions()->create([
            'version_number' => $this->version,
            'name' => $this->getOriginal('name') ?? $this->name,
            'type' => $this->getOriginal('type') ?? $this->type,
            'value' => $this->getOriginal('value') ?? $this->value,
            'metadata' => $this->getOriginal('metadata') ?? $this->metadata,
            'changed_by' => $changedBy?->id ?? $this->updated_by,
            'change_reason' => $reason,
            'diff' => $this->getDiff(),
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ]);
    }

    /**
     * Get the diff between original and current values
     */
    protected function getDiff(): array
    {
        $diff = [];
        $original = $this->getOriginal();

        foreach (['name', 'value', 'type', 'metadata'] as $field) {
            if ($this->$field !== ($original[$field] ?? null)) {
                $diff[$field] = [
                    'old' => $original[$field] ?? null,
                    'new' => $this->$field,
                ];
            }
        }

        return $diff;
    }

    /**
     * Restore to a specific version
     */
    public function restoreToVersion(int $versionNumber): bool
    {
        $version = $this->versions()->where('version_number', $versionNumber)->first();

        if (!$version) {
            return false;
        }

        $this->update([
            'name' => $version->name,
            'type' => $version->type,
            'value' => $version->value,
            'metadata' => $version->metadata,
        ]);

        return true;
    }

    /**
     * Scope to filter by equipment
     */
    public function scopeForEquipment($query, int $equipmentId)
    {
        return $query->where('equipment_id', $equipmentId);
    }

    /**
     * Scope to filter by type
     */
    public function scopeType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to filter public properties
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope to filter verified properties
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope to order by sort order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Get all available property types
     */
    public static function getTypes(): array
    {
        return [
            self::TYPE_TEXT,
            self::TYPE_RICHTEXT,
            self::TYPE_NUMBER,
            self::TYPE_BOOLEAN,
            self::TYPE_LINK,
            self::TYPE_IMAGE,
            self::TYPE_VIDEO,
            self::TYPE_FILE,
            self::TYPE_JSON,
            self::TYPE_DATE,
            self::TYPE_LOCATION,
        ];
    }
}
