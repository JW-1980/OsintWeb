<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * EquipmentLink Model
 *
 * Represents an external link/reference for military equipment.
 *
 * @property int $id
 * @property string $uuid
 * @property int $equipment_id
 * @property string $url
 * @property string $title
 * @property string|null $description
 * @property string $type
 * @property string $language
 * @property bool $is_verified
 * @property bool $is_active
 * @property \Carbon\Carbon|null $last_checked_at
 * @property int|null $added_by
 * @property int $sort_order
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class EquipmentLink extends Model
{
    use HasFactory, SoftDeletes;

    public const TYPE_WIKIPEDIA = 'wikipedia';
    public const TYPE_OFFICIAL = 'official';
    public const TYPE_MANUFACTURER = 'manufacturer';
    public const TYPE_DOCUMENTATION = 'documentation';
    public const TYPE_VIDEO = 'video';
    public const TYPE_NEWS = 'news';
    public const TYPE_ANALYSIS = 'analysis';
    public const TYPE_FORUM = 'forum';
    public const TYPE_OTHER = 'other';

    protected $fillable = [
        'uuid',
        'equipment_id',
        'url',
        'title',
        'description',
        'type',
        'language',
        'is_verified',
        'is_active',
        'last_checked_at',
        'added_by',
        'sort_order',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'is_active' => 'boolean',
        'last_checked_at' => 'datetime',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (EquipmentLink $link) {
            if (empty($link->uuid)) {
                $link->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the equipment this link belongs to
     */
    public function equipment(): BelongsTo
    {
        return $this->belongsTo(MilitaryEquipment::class, 'equipment_id');
    }

    /**
     * Get the user who added this link
     */
    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    /**
     * Scope to filter by type
     */
    public function scopeType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to filter active links
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter verified links
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
     * Get all available link types
     */
    public static function getTypes(): array
    {
        return [
            self::TYPE_WIKIPEDIA,
            self::TYPE_OFFICIAL,
            self::TYPE_MANUFACTURER,
            self::TYPE_DOCUMENTATION,
            self::TYPE_VIDEO,
            self::TYPE_NEWS,
            self::TYPE_ANALYSIS,
            self::TYPE_FORUM,
            self::TYPE_OTHER,
        ];
    }
}
