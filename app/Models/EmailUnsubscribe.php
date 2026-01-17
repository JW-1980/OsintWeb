<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Email Unsubscribe Model
 *
 * Tracks email unsubscribe preferences for users.
 *
 * @property int $id
 * @property string $uuid
 * @property int|null $user_id
 * @property string $email
 * @property string $token
 * @property string $type
 * @property string|null $reason
 * @property string|null $unsubscribed_ip
 * @property \Carbon\Carbon $unsubscribed_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class EmailUnsubscribe extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::creating(function (EmailUnsubscribe $unsubscribe) {
            if (empty($unsubscribe->uuid)) {
                $unsubscribe->uuid = (string) Str::uuid();
            }
            if (empty($unsubscribe->token)) {
                $unsubscribe->token = Str::random(64);
            }
            if (empty($unsubscribe->unsubscribed_at)) {
                $unsubscribe->unsubscribed_at = now();
            }
        });
    }

    protected $fillable = [
        'uuid',
        'user_id',
        'email',
        'token',
        'type',
        'reason',
        'unsubscribed_ip',
        'unsubscribed_at',
    ];

    protected $casts = [
        'unsubscribed_at' => 'datetime',
    ];

    protected $hidden = [
        'token',
        'unsubscribed_ip',
    ];

    /**
     * Get the route key name for route model binding.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * Get the user who unsubscribed
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if email is unsubscribed from a specific type
     */
    public static function isUnsubscribed(string $email, string $type = 'all'): bool
    {
        return static::where('email', $email)
            ->where(function ($query) use ($type) {
                $query->where('type', 'all')
                    ->orWhere('type', $type);
            })
            ->exists();
    }

    /**
     * Get all unsubscribe preferences for an email
     */
    public static function getPreferences(string $email): array
    {
        return static::where('email', $email)
            ->pluck('type')
            ->toArray();
    }

    /**
     * Available unsubscribe types
     */
    public static function types(): array
    {
        return [
            'all' => 'All Emails',
            'marketing' => 'Marketing & Promotional',
            'notifications' => 'Notifications',
            'alerts' => 'Alerts',
            'digest' => 'Weekly Digest',
        ];
    }

    /**
     * Scope by email
     */
    public function scopeForEmail($query, string $email)
    {
        return $query->where('email', $email);
    }

    /**
     * Scope by type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
