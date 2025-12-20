<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * EquipmentPropertyVersion Model
 *
 * Represents a version snapshot of an equipment property for audit trail.
 *
 * @property int $id
 * @property int $property_id
 * @property int $version_number
 * @property string $name
 * @property string $type
 * @property string|null $value
 * @property array|null $metadata
 * @property int|null $changed_by
 * @property string|null $change_reason
 * @property array|null $diff
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property \Carbon\Carbon $created_at
 */
class EquipmentPropertyVersion extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'property_id',
        'version_number',
        'name',
        'type',
        'value',
        'metadata',
        'changed_by',
        'change_reason',
        'diff',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'version_number' => 'integer',
        'metadata' => 'array',
        'diff' => 'array',
        'created_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (EquipmentPropertyVersion $version) {
            $version->created_at = now();
        });
    }

    /**
     * Get the property this version belongs to
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(EquipmentProperty::class, 'property_id');
    }

    /**
     * Get the user who made this change
     */
    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
