<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * User Model
 *
 * Represents a user in the OSINT platform.
 *
 * @property int $id
 * @property string $uuid
 * @property string $name
 * @property string $email
 * @property \Carbon\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property string $role
 * @property bool $is_active
 * @property bool $is_verified
 * @property string|null $avatar_url
 * @property string|null $bio
 * @property string|null $organization
 * @property string|null $location
 * @property string|null $website
 * @property array|null $preferences
 * @property array|null $permissions
 * @property \Carbon\Carbon|null $last_login_at
 * @property string|null $last_login_ip
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'uuid',
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'is_verified',
        'avatar_url',
        'bio',
        'organization',
        'location',
        'website',
        'preferences',
        'permissions',
        'last_login_at',
        'last_login_ip',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'is_verified' => 'boolean',
        'preferences' => 'array',
        'permissions' => 'array',
        'last_login_at' => 'datetime',
    ];

    /**
     * Get the roles assigned to this user
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_role')
            ->withPivot(['granted_by', 'expires_at'])
            ->withTimestamps();
    }

    /**
     * Get direct permissions assigned to this user
     */
    public function directPermissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'user_permission')
            ->withPivot(['type', 'granted_by', 'expires_at'])
            ->withTimestamps();
    }

    /**
     * Get the user groups this user belongs to
     */
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(UserGroup::class, 'user_group_members')
            ->withPivot(['role', 'added_by'])
            ->withTimestamps();
    }

    /**
     * Get events this user has direct access to
     */
    public function accessibleEvents(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'event_user_access')
            ->withPivot(['access_level', 'granted_by', 'expires_at'])
            ->withTimestamps();
    }

    /**
     * Get the events created by this user
     */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    /**
     * Get the audit logs for this user
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    /**
     * Get the entity versions created by this user
     */
    public function entityVersions(): HasMany
    {
        return $this->hasMany(EntityVersion::class, 'created_by');
    }

    /**
     * Get all auditable events for this user
     */
    public function auditableEvents(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'auditable');
    }

    /**
     * Scope to filter active users
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter verified users
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope to filter by role
     */
    public function scopeRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Scope to filter admins
     */
    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin');
    }

    /**
     * Scope to filter moderators
     */
    public function scopeModerators($query)
    {
        return $query->whereIn('role', ['admin', 'moderator']);
    }

    /**
     * Check if user has a specific permission (RBAC)
     */
    public function hasPermission(string $permissionSlug): bool
    {
        // Admin bypass
        if ($this->role === 'admin' || $this->hasRole('admin')) {
            return true;
        }

        // Check for direct deny permission
        $directDeny = $this->directPermissions()
            ->where('slug', $permissionSlug)
            ->wherePivot('type', 'deny')
            ->where(function ($q) {
                $q->whereNull('user_permission.expires_at')
                    ->orWhere('user_permission.expires_at', '>', now());
            })
            ->exists();

        if ($directDeny) {
            return false;
        }

        // Check for direct grant permission
        $directGrant = $this->directPermissions()
            ->where('slug', $permissionSlug)
            ->wherePivot('type', 'grant')
            ->where(function ($q) {
                $q->whereNull('user_permission.expires_at')
                    ->orWhere('user_permission.expires_at', '>', now());
            })
            ->exists();

        if ($directGrant) {
            return true;
        }

        // Check through roles
        $activeRoles = $this->roles()
            ->where(function ($q) {
                $q->whereNull('user_role.expires_at')
                    ->orWhere('user_role.expires_at', '>', now());
            })
            ->get();

        foreach ($activeRoles as $role) {
            if ($role->hasPermission($permissionSlug)) {
                return true;
            }
        }

        // Fallback to legacy permissions array
        return in_array($permissionSlug, $this->permissions ?? [], true);
    }

    /**
     * Check if user has a specific role (RBAC)
     */
    public function hasRole(string $roleSlug): bool
    {
        // Check legacy role column
        if ($this->role === $roleSlug) {
            return true;
        }

        // Check RBAC roles
        return $this->roles()
            ->where('slug', $roleSlug)
            ->where(function ($q) {
                $q->whereNull('user_role.expires_at')
                    ->orWhere('user_role.expires_at', '>', now());
            })
            ->exists();
    }

    /**
     * Check if user has any of the given roles
     */
    public function hasAnyRole(array $roleSlugs): bool
    {
        foreach ($roleSlugs as $roleSlug) {
            if ($this->hasRole($roleSlug)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if user is an admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin' || $this->hasRole('admin');
    }

    /**
     * Check if user is a moderator or admin
     */
    public function isModerator(): bool
    {
        return in_array($this->role, ['admin', 'moderator'], true)
            || $this->hasAnyRole(['admin', 'moderator']);
    }

    /**
     * Get all permissions this user has (including through roles)
     */
    public function getAllPermissions(): array
    {
        $permissions = [];

        // Get permissions from roles
        $roles = $this->roles()
            ->where(function ($q) {
                $q->whereNull('user_role.expires_at')
                    ->orWhere('user_role.expires_at', '>', now());
            })
            ->with('permissions')
            ->get();

        foreach ($roles as $role) {
            foreach ($role->permissions as $permission) {
                $permissions[$permission->slug] = true;
            }
        }

        // Apply direct permissions
        $directPermissions = $this->directPermissions()
            ->where(function ($q) {
                $q->whereNull('user_permission.expires_at')
                    ->orWhere('user_permission.expires_at', '>', now());
            })
            ->get();

        foreach ($directPermissions as $permission) {
            if ($permission->pivot->type === 'grant') {
                $permissions[$permission->slug] = true;
            } else {
                unset($permissions[$permission->slug]);
            }
        }

        return array_keys($permissions);
    }

    /**
     * Assign a role to this user
     */
    public function assignRole(Role $role, ?User $grantedBy = null, ?\Carbon\Carbon $expiresAt = null): void
    {
        if (!$this->roles()->where('roles.id', $role->id)->exists()) {
            $this->roles()->attach($role->id, [
                'granted_by' => $grantedBy?->id,
                'expires_at' => $expiresAt,
            ]);
        }
    }

    /**
     * Remove a role from this user
     */
    public function removeRole(Role $role): void
    {
        $this->roles()->detach($role->id);
    }

    /**
     * Check if user can access a specific event
     */
    public function canAccessEvent(Event $event): bool
    {
        // Author always has access
        if ($event->user_id === $this->id) {
            return true;
        }

        // Admins always have access
        if ($this->isAdmin()) {
            return true;
        }

        // Public events are visible to all when published
        if ($event->visibility === 'public' && $event->status === 'verified') {
            return true;
        }

        // Internal events visible to authenticated users
        if ($event->visibility === 'internal') {
            return true;
        }

        // Private events only visible to author (handled above)
        if ($event->visibility === 'private') {
            return false;
        }

        // Restricted events - check direct access
        $hasDirectAccess = $this->accessibleEvents()
            ->where('events.id', $event->id)
            ->where(function ($q) {
                $q->whereNull('event_user_access.expires_at')
                    ->orWhere('event_user_access.expires_at', '>', now());
            })
            ->exists();

        if ($hasDirectAccess) {
            return true;
        }

        // Check group access
        $groupIds = $this->groups()->pluck('user_groups.id');
        if ($groupIds->isNotEmpty()) {
            $hasGroupAccess = $event->groupAccess()
                ->whereIn('user_group_id', $groupIds)
                ->where(function ($q) {
                    $q->whereNull('event_group_access.expires_at')
                        ->orWhere('event_group_access.expires_at', '>', now());
                })
                ->exists();

            if ($hasGroupAccess) {
                return true;
            }
        }

        // Check role access
        $roleIds = $this->roles()->pluck('roles.id');
        if ($roleIds->isNotEmpty()) {
            $hasRoleAccess = $event->roleAccess()
                ->whereIn('role_id', $roleIds)
                ->where(function ($q) {
                    $q->whereNull('event_role_access.expires_at')
                        ->orWhere('event_role_access.expires_at', '>', now());
                })
                ->exists();

            if ($hasRoleAccess) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user belongs to a specific group
     */
    public function inGroup(UserGroup $group): bool
    {
        return $this->groups()->where('user_groups.id', $group->id)->exists();
    }
}
