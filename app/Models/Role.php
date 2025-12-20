<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Role Model
 *
 * Represents a role that can be assigned to users.
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string|null $color
 * @property int $priority
 * @property bool $is_system
 * @property bool $is_default
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'color',
        'priority',
        'is_system',
        'is_default',
    ];

    protected $casts = [
        'priority' => 'integer',
        'is_system' => 'boolean',
        'is_default' => 'boolean',
    ];

    /**
     * Get the permissions for this role
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permission')
            ->withTimestamps();
    }

    /**
     * Get the users with this role
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_role')
            ->withPivot(['granted_by', 'expires_at'])
            ->withTimestamps();
    }

    /**
     * Get the events accessible by this role
     */
    public function accessibleEvents(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'event_role_access')
            ->withPivot(['access_level', 'granted_by', 'expires_at'])
            ->withTimestamps();
    }

    /**
     * Check if this role has a specific permission
     */
    public function hasPermission(string $permissionSlug): bool
    {
        return $this->permissions()->where('slug', $permissionSlug)->exists();
    }

    /**
     * Grant a permission to this role
     */
    public function grantPermission(Permission $permission): void
    {
        if (!$this->hasPermission($permission->slug)) {
            $this->permissions()->attach($permission->id);
        }
    }

    /**
     * Revoke a permission from this role
     */
    public function revokePermission(Permission $permission): void
    {
        $this->permissions()->detach($permission->id);
    }

    /**
     * Scope to filter system roles
     */
    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }

    /**
     * Scope to filter default roles
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope to order by priority
     */
    public function scopeOrdered($query)
    {
        return $query->orderByDesc('priority');
    }

    /**
     * Get the default role for new users
     */
    public static function getDefault(): ?self
    {
        return static::where('is_default', true)->first();
    }
}
