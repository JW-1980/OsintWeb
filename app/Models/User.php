<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
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
     * Check if user has a specific permission
     */
    public function hasPermission(string $permission): bool
    {
        if ($this->role === 'admin') {
            return true;
        }

        return in_array($permission, $this->permissions ?? [], true);
    }

    /**
     * Check if user has a specific role
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Check if user is an admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is a moderator or admin
     */
    public function isModerator(): bool
    {
        return in_array($this->role, ['admin', 'moderator'], true);
    }
}
