<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Achievement Model
 *
 * Represents an achievement that can be earned by users.
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string $icon
 * @property string $color
 * @property int $points
 * @property int $threshold
 * @property string $type
 * @property bool $is_secret
 * @property bool $is_active
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Achievement extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'color',
        'points',
        'threshold',
        'type',
        'is_secret',
        'is_active',
    ];

    protected $casts = [
        'is_secret' => 'boolean',
        'is_active' => 'boolean',
        'points' => 'integer',
        'threshold' => 'integer',
    ];

    /**
     * Users who have earned this achievement.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_achievements')
            ->withPivot('awarded_at')
            ->withTimestamps();
    }

    /**
     * Scope to filter active achievements.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
