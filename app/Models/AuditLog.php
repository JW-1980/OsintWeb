<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Audit Log Model
 *
 * Tracks all changes to auditable entities in the system.
 *
 * @property int $id
 * @property string $uuid
 * @property int|null $user_id
 * @property string $user_email
 * @property string|null $user_name
 * @property int|null $impersonator_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $occurred_at
 * @property string $ip_address
 * @property string|null $ip_country
 * @property string|null $user_agent
 * @property string|null $session_id
 * @property string|null $request_id
 * @property string $action
 * @property string $auditable_type
 * @property int $auditable_id
 * @property string|null $auditable_uuid
 * @property array|null $old_values
 * @property array|null $new_values
 * @property array|null $changed_fields
 * @property string|null $reason
 * @property array|null $tags
 * @property array|null $metadata
 * @property string|null $url
 * @property string|null $http_method
 * @property string|null $api_endpoint
 * @property string $hash
 * @property string|null $previous_hash
 * @property bool $chain_verified
 */
class AuditLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'uuid',
        'user_id',
        'user_email',
        'user_name',
        'impersonator_id',
        'occurred_at',
        'ip_address',
        'ip_country',
        'user_agent',
        'session_id',
        'request_id',
        'action',
        'auditable_type',
        'auditable_id',
        'auditable_uuid',
        'old_values',
        'new_values',
        'changed_fields',
        'reason',
        'tags',
        'metadata',
        'url',
        'http_method',
        'api_endpoint',
        'hash',
        'previous_hash',
        'chain_verified',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
        'old_values' => 'array',
        'new_values' => 'array',
        'changed_fields' => 'array',
        'tags' => 'array',
        'metadata' => 'array',
        'chain_verified' => 'boolean',
    ];

    /**
     * Get the user who performed the action
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the impersonator (if action was performed while impersonating)
     */
    public function impersonator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'impersonator_id');
    }

    /**
     * Get the auditable entity
     */
    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope to filter by action type
     */
    public function scopeAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope to filter by entity type
     */
    public function scopeForEntity($query, string $type, ?int $id = null)
    {
        $query->where('auditable_type', $type);

        if ($id !== null) {
            $query->where('auditable_id', $id);
        }

        return $query;
    }

    /**
     * Scope to filter by user
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to filter by date range
     */
    public function scopeDateRange($query, $from, $to)
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }

    /**
     * Scope for full-text search
     */
    public function scopeSearch($query, string $term)
    {
        return $query->whereRaw(
            "to_tsvector('english', coalesce(reason, '') || ' ' || coalesce(old_values::text, '') || ' ' || coalesce(new_values::text, '')) @@ plainto_tsquery('english', ?)",
            [$term]
        );
    }

    /**
     * Get human-readable description of the action
     */
    public function getDescriptionAttribute(): string
    {
        $entityName = class_basename($this->auditable_type);
        $entityId = $this->auditable_uuid ?? "#{$this->auditable_id}";

        return match($this->action) {
            'create' => "Created {$entityName} {$entityId}",
            'update' => "Updated {$entityName} {$entityId}",
            'delete' => "Deleted {$entityName} {$entityId}",
            'restore' => "Restored {$entityName} {$entityId}",
            'verify' => "Verified {$entityName} {$entityId}",
            'dispute' => "Disputed {$entityName} {$entityId}",
            'export' => "Exported {$entityName} data",
            'login' => "Logged in",
            'logout' => "Logged out",
            'failed_login' => "Failed login attempt",
            'password_change' => "Changed password",
            default => ucfirst($this->action) . " {$entityName} {$entityId}",
        };
    }

    /**
     * Get the count of changes made
     */
    public function getChangesCountAttribute(): int
    {
        return count($this->changed_fields ?? []);
    }
}
