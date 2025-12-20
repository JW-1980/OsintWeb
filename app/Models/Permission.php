<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Permission Model
 *
 * Represents a permission that can be assigned to roles or users.
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $group
 * @property string|null $description
 * @property bool $is_system
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Permission extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'group',
        'description',
        'is_system',
    ];

    protected $casts = [
        'is_system' => 'boolean',
    ];

    /**
     * Get the roles that have this permission
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permission')
            ->withTimestamps();
    }

    /**
     * Get the users that have this permission directly
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_permission')
            ->withPivot(['type', 'granted_by', 'expires_at'])
            ->withTimestamps();
    }

    /**
     * Scope to filter by group
     */
    public function scopeGroup($query, string $group)
    {
        return $query->where('group', $group);
    }

    /**
     * Scope to filter system permissions
     */
    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }

    /**
     * Get all available permission groups
     */
    public static function getGroups(): array
    {
        return static::distinct('group')->pluck('group')->toArray();
    }
}
