<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Consent Log Model
 *
 * Tracks all consent changes for GDPR/privacy compliance.
 *
 * @property int $id
 * @property string $uuid
 * @property int $user_id
 * @property string $consent_type
 * @property bool $consented
 * @property string|null $version
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property array|null $metadata
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class ConsentLog extends Model
{
    protected $fillable = [
        'uuid',
        'user_id',
        'consent_type',
        'consented',
        'version',
        'ip_address',
        'user_agent',
        'metadata',
    ];

    protected $casts = [
        'consented' => 'boolean',
        'metadata' => 'array',
    ];

    public const TYPE_TERMS_OF_SERVICE = 'terms_of_service';
    public const TYPE_PRIVACY_POLICY = 'privacy_policy';
    public const TYPE_MARKETING = 'marketing';
    public const TYPE_ANALYTICS = 'analytics';
    public const TYPE_DATA_PROCESSING = 'data_processing';
    public const TYPE_COOKIES = 'cookies';
    public const TYPE_THIRD_PARTY_SHARING = 'third_party_sharing';

    /**
     * Get the user that this consent belongs to.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to filter by consent type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('consent_type', $type);
    }

    /**
     * Scope to filter consented records.
     */
    public function scopeConsented($query)
    {
        return $query->where('consented', true);
    }

    /**
     * Scope to filter revoked consent.
     */
    public function scopeRevoked($query)
    {
        return $query->where('consented', false);
    }

    /**
     * Get the latest consent for a user and type.
     */
    public static function getLatest(int $userId, string $type): ?self
    {
        return static::where('user_id', $userId)
            ->where('consent_type', $type)
            ->latest()
            ->first();
    }

    /**
     * Check if user has active consent for a type.
     */
    public static function hasActiveConsent(int $userId, string $type): bool
    {
        $latest = static::getLatest($userId, $type);
        return $latest?->consented ?? false;
    }

    /**
     * Record a consent action.
     */
    public static function record(
        User $user,
        string $type,
        bool $consented,
        ?string $version = null,
        ?string $ipAddress = null,
        ?string $userAgent = null,
        ?array $metadata = null
    ): self {
        return static::create([
            'uuid' => \Illuminate\Support\Str::uuid(),
            'user_id' => $user->id,
            'consent_type' => $type,
            'consented' => $consented,
            'version' => $version,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'metadata' => $metadata,
        ]);
    }
}
