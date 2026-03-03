<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Domains\Intelligence\Queries\EventQueryBuilder;
use App\Models\WeatherData;

/**
 * Event Model
 *
 * Represents an OSINT event on the map.
 *
 * @property int $id
 * @property string $uuid
 * @property int $event_type_id
 * @property int $user_id
 * @property string $title
 * @property string|null $description
 * @property \Illuminate\Database\Query\Expression|null $location
 * @property float|null $latitude
 * @property float|null $longitude
 * @property string|null $location_name
 * @property \Carbon\Carbon $occurred_at
 * @property int|null $confidence_score
 * @property string $status
 * @property \Carbon\Carbon|null $scheduled_publish_at
 * @property int|null $approved_by
 * @property \Carbon\Carbon|null $approved_at
 * @property string|null $approval_notes
 * @property bool $requires_approval
 * @property \Carbon\Carbon|null $published_at
 * @property string $visibility
 * @property int $version
 * @property int|null $parent_version_id
 * @property bool $is_verified
 * @property \Carbon\Carbon|null $verified_at
 * @property int|null $verified_by
 * @property array|null $custom_fields
 * @property int $views_count
 * @property int $upvotes_count
 * @property int $downvotes_count
 * @property array|null $tags
 * @property array|null $metadata
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class Event extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PENDING = 'pending';
    public const STATUS_VERIFIED = 'verified';
    public const STATUS_DISPUTED = 'disputed';
    public const STATUS_ARCHIVED = 'archived';

    public const VISIBILITY_PUBLIC = 'public';
    public const VISIBILITY_PRIVATE = 'private';
    public const VISIBILITY_RESTRICTED = 'restricted';
    public const VISIBILITY_INTERNAL = 'internal';

    protected $fillable = [
        'uuid',
        'event_type_id',
        'user_id',
        'title',
        'description',
        'location',
        'latitude',
        'longitude',
        'location_name',
        'occurred_at',
        'confidence_score',
        'status',
        'scheduled_publish_at',
        'approved_by',
        'approved_at',
        'approval_notes',
        'requires_approval',
        'published_at',
        'visibility',
        'version',
        'parent_version_id',
        'is_verified',
        'verified_at',
        'verified_by',
        'custom_fields',
        'views_count',
        'upvotes_count',
        'downvotes_count',
        'tags',
        'metadata',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'confidence_score' => 'integer',
        'scheduled_publish_at' => 'datetime',
        'approved_at' => 'datetime',
        'requires_approval' => 'boolean',
        'published_at' => 'datetime',
        'version' => 'integer',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
        'custom_fields' => 'array',
        'views_count' => 'integer',
        'upvotes_count' => 'integer',
        'downvotes_count' => 'integer',
        'tags' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Create a new Eloquent query builder for the model.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @return \App\Domains\Intelligence\Queries\EventQueryBuilder
     */
    public function newEloquentBuilder($query)
    {
        return new EventQueryBuilder($query);
    }

    /**
     * Get the event type
     */
    public function eventType(): BelongsTo
    {
        return $this->belongsTo(EventType::class);
    }

    /**
     * Get the user who created this event
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who verified this event
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Get the user who approved this event
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the parent version of this event
     */
    public function parentVersion(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_version_id');
    }

    /**
     * Get child versions of this event
     */
    public function childVersions(): HasMany
    {
        return $this->hasMany(self::class, 'parent_version_id');
    }

    /**
     * Get the media associated with this event
     */
    public function media(): HasMany
    {
        return $this->hasMany(EventMedia::class);
    }

    /**
     * Get the sources for this event
     */
    public function sources(): HasMany
    {
        return $this->hasMany(EventSource::class);
    }

    /**
     * Get the equipment involved in this event
     */
    public function equipment(): HasMany
    {
        return $this->hasMany(EventEquipment::class);
    }

    /**
     * Get the geolocation verifications for this event
     */
    public function geolocationVerifications(): HasMany
    {
        return $this->hasMany(GeolocationVerification::class);
    }

    /**
     * Get the actors involved in this event
     */
    public function actors(): BelongsToMany
    {
        return $this->belongsToMany(Actor::class, 'actor_event')
            ->withPivot(['role', 'notes'])
            ->withTimestamps();
    }

    /**
     * Get users with direct access to this event
     */
    public function userAccess(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'event_user_access')
            ->withPivot(['access_level', 'granted_by', 'expires_at'])
            ->withTimestamps();
    }

    /**
     * Get groups with access to this event
     */
    public function groupAccess(): BelongsToMany
    {
        return $this->belongsToMany(UserGroup::class, 'event_group_access')
            ->withPivot(['access_level', 'granted_by', 'expires_at'])
            ->withTimestamps();
    }

    /**
     * Get roles with access to this event
     */
    public function roleAccess(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'event_role_access')
            ->withPivot(['access_level', 'granted_by', 'expires_at'])
            ->withTimestamps();
    }

    /**
     * Get weather data linked to this event
     */
    public function weatherData(): BelongsToMany
    {
        return $this->belongsToMany(WeatherData::class, 'event_weather')
            ->withPivot([
                'link_type',
                'distance_km',
                'time_diff_minutes',
                'shadow_verified',
                'claimed_shadow_angle',
                'calculated_shadow_angle',
                'shadow_angle_deviation',
                'shadow_matches',
                'verification_notes',
                'linked_by_user_id',
                'linked_at',
            ])
            ->withTimestamps();
    }

    /**
     * Get the primary weather data for this event
     */
    public function primaryWeather(): ?WeatherData
    {
        return $this->weatherData()
            ->orderByPivot('created_at', 'asc')
            ->first();
    }

    /**
     * Check if event is a draft
     */
    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    /**
     * Check if event is pending approval
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if event is published/verified
     */
    public function isPublished(): bool
    {
        return $this->status === self::STATUS_VERIFIED && $this->published_at !== null;
    }

    /**
     * Check if event is scheduled for publishing
     */
    public function isScheduled(): bool
    {
        return $this->scheduled_publish_at !== null && $this->scheduled_publish_at->isFuture();
    }

    /**
     * Submit event for approval
     */
    public function submitForApproval(): void
    {
        $this->update([
            'status' => self::STATUS_PENDING,
        ]);
    }

    /**
     * Approve the event
     */
    public function approve(User $approver, ?string $notes = null): void
    {
        $this->update([
            'status' => self::STATUS_VERIFIED,
            'approved_by' => $approver->id,
            'approved_at' => now(),
            'approval_notes' => $notes,
            'published_at' => $this->scheduled_publish_at ?? now(),
        ]);
    }

    /**
     * Reject the event back to draft
     */
    public function reject(User $approver, ?string $notes = null): void
    {
        $this->update([
            'status' => self::STATUS_DRAFT,
            'approved_by' => $approver->id,
            'approved_at' => now(),
            'approval_notes' => $notes,
        ]);
    }

    /**
     * Schedule the event for publishing
     */
    public function schedulePublish(\Carbon\Carbon $publishAt): void
    {
        $this->update([
            'scheduled_publish_at' => $publishAt,
        ]);
    }

    /**
     * Publish the event immediately
     */
    public function publish(): void
    {
        $this->update([
            'status' => self::STATUS_VERIFIED,
            'published_at' => now(),
            'scheduled_publish_at' => null,
        ]);
    }

    /**
     * Unpublish the event (back to draft)
     */
    public function unpublish(): void
    {
        $this->update([
            'status' => self::STATUS_DRAFT,
            'published_at' => null,
        ]);
    }

    /**
     * Grant access to a user
     */
    public function grantUserAccess(User $user, string $accessLevel = 'view', ?User $grantedBy = null): void
    {
        $this->userAccess()->syncWithoutDetaching([
            $user->id => [
                'access_level' => $accessLevel,
                'granted_by' => $grantedBy?->id,
            ],
        ]);
    }

    /**
     * Grant access to a group
     */
    public function grantGroupAccess(UserGroup $group, string $accessLevel = 'view', ?User $grantedBy = null): void
    {
        $this->groupAccess()->syncWithoutDetaching([
            $group->id => [
                'access_level' => $accessLevel,
                'granted_by' => $grantedBy?->id,
            ],
        ]);
    }

    /**
     * Grant access to a role
     */
    public function grantRoleAccess(Role $role, string $accessLevel = 'view', ?User $grantedBy = null): void
    {
        $this->roleAccess()->syncWithoutDetaching([
            $role->id => [
                'access_level' => $accessLevel,
                'granted_by' => $grantedBy?->id,
            ],
        ]);
    }

    /**
     * Revoke user access
     */
    public function revokeUserAccess(User $user): void
    {
        $this->userAccess()->detach($user->id);
    }

    /**
     * Revoke group access
     */
    public function revokeGroupAccess(UserGroup $group): void
    {
        $this->groupAccess()->detach($group->id);
    }

    /**
     * Revoke role access
     */
    public function revokeRoleAccess(Role $role): void
    {
        $this->roleAccess()->detach($role->id);
    }

    /**
     * Scope to filter verified events
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope to filter by status
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter published events
     */
    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_VERIFIED)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /**
     * Scope to filter draft events
     */
    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    /**
     * Scope to filter pending events
     */
    public function scopePendingApproval($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope to filter scheduled events
     */
    public function scopeScheduled($query)
    {
        return $query->whereNotNull('scheduled_publish_at')
            ->where('scheduled_publish_at', '>', now());
    }

    /**
     * Scope to filter events due for publishing
     */
    public function scopeDueForPublishing($query)
    {
        return $query->where('status', self::STATUS_PENDING)
            ->whereNotNull('scheduled_publish_at')
            ->where('scheduled_publish_at', '<=', now())
            ->where('requires_approval', false);
    }

    /**
     * Scope to filter by visibility
     */
    public function scopeVisibility($query, string $visibility)
    {
        return $query->where('visibility', $visibility);
    }

    /**
     * Scope to filter public events
     */
    public function scopePubliclyVisible($query)
    {
        return $query->where('visibility', self::VISIBILITY_PUBLIC)
            ->published();
    }

    /**
     * Scope to filter events accessible by a user
     */
    public function scopeAccessibleBy($query, User $user)
    {
        return $query->where(function ($q) use ($user) {
            // Author always has access
            $q->where('user_id', $user->id);

            // Public and published
            $q->orWhere(function ($q2) {
                $q2->where('visibility', self::VISIBILITY_PUBLIC)
                    ->where('status', self::STATUS_VERIFIED);
            });

            // Internal events for authenticated users
            $q->orWhere('visibility', self::VISIBILITY_INTERNAL);

            // Direct user access
            $q->orWhereHas('userAccess', function ($q2) use ($user) {
                $q2->where('user_id', $user->id)
                    ->where(function ($q3) {
                        $q3->whereNull('expires_at')
                            ->orWhere('expires_at', '>', now());
                    });
            });

            // Group access
            $userGroupIds = $user->groups()->pluck('user_groups.id');
            if ($userGroupIds->isNotEmpty()) {
                $q->orWhereHas('groupAccess', function ($q2) use ($userGroupIds) {
                    $q2->whereIn('user_group_id', $userGroupIds)
                        ->where(function ($q3) {
                            $q3->whereNull('expires_at')
                                ->orWhere('expires_at', '>', now());
                        });
                });
            }

            // Role access
            $userRoleIds = $user->roles()->pluck('roles.id');
            if ($userRoleIds->isNotEmpty()) {
                $q->orWhereHas('roleAccess', function ($q2) use ($userRoleIds) {
                    $q2->whereIn('role_id', $userRoleIds)
                        ->where(function ($q3) {
                            $q3->whereNull('expires_at')
                                ->orWhere('expires_at', '>', now());
                        });
                });
            }
        });
    }

    /**
     * Scope to filter by event type
     */
    public function scopeEventType($query, int $eventTypeId)
    {
        return $query->where('event_type_id', $eventTypeId);
    }

    /**
     * Scope to filter by date range
     */
    public function scopeDateRange($query, $from, $to)
    {
        return $query->whereBetween('occurred_at', [$from, $to]);
    }

    /**
     * Scope to filter by bounding box
     */
    public function scopeWithinBounds($query, float $swLat, float $swLng, float $neLat, float $neLng)
    {
        return $query->whereBetween('latitude', [$swLat, $neLat])
            ->whereBetween('longitude', [$swLng, $neLng]);
    }

    /**
     * Scope to search by title, description, or location
     */
    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('title', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%")
                ->orWhere('location_name', 'like', "%{$term}%");
        });
    }

    /**
     * Scope to order by popularity
     */
    public function scopePopular($query)
    {
        return $query->orderByRaw('(upvotes_count - downvotes_count) DESC');
    }

    /**
     * Get coordinates as array
     */
    public function getCoordinates(): ?array
    {
        if ($this->latitude === null || $this->longitude === null) {
            return null;
        }

        return [
            'lat' => (float) $this->latitude,
            'lng' => (float) $this->longitude,
        ];
    }

    /**
     * Set location from coordinates
     */
    public function setLocationFromCoordinates(float $latitude, float $longitude): void
    {
        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }

    /**
     * Get all available statuses
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_DRAFT,
            self::STATUS_PENDING,
            self::STATUS_VERIFIED,
            self::STATUS_DISPUTED,
            self::STATUS_ARCHIVED,
        ];
    }

    /**
     * Get all available visibility options
     */
    public static function getVisibilityOptions(): array
    {
        return [
            self::VISIBILITY_PUBLIC,
            self::VISIBILITY_PRIVATE,
            self::VISIBILITY_RESTRICTED,
            self::VISIBILITY_INTERNAL,
        ];
    }
}
