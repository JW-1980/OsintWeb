<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * UserGroup Model
 *
 * Represents a group of users for organizing access and permissions.
 *
 * @property int $id
 * @property string $uuid
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string|null $color
 * @property int|null $created_by
 * @property bool $is_active
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class UserGroup extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'name',
        'slug',
        'description',
        'color',
        'created_by',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the user who created this group
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the members of this group
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_group_members')
            ->withPivot(['role', 'added_by'])
            ->withTimestamps();
    }

    /**
     * Get the events accessible by this group
     */
    public function accessibleEvents(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'event_group_access')
            ->withPivot(['access_level', 'granted_by', 'expires_at'])
            ->withTimestamps();
    }

    /**
     * Check if a user is a member of this group
     */
    public function hasMember(User $user): bool
    {
        return $this->members()->where('user_id', $user->id)->exists();
    }

    /**
     * Add a user to this group
     */
    public function addMember(User $user, string $role = 'member', ?User $addedBy = null): void
    {
        if (!$this->hasMember($user)) {
            $this->members()->attach($user->id, [
                'role' => $role,
                'added_by' => $addedBy?->id,
            ]);
        }
    }

    /**
     * Remove a user from this group
     */
    public function removeMember(User $user): void
    {
        $this->members()->detach($user->id);
    }

    /**
     * Scope to filter active groups
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to search by name
     */
    public function scopeSearch($query, string $term)
    {
        return $query->where('name', 'like', "%{$term}%");
    }
}
