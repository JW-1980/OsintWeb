<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * User Session Model
 *
 * Tracks active user sessions for security and device management.
 *
 * @property int $id
 * @property string $uuid
 * @property int $user_id
 * @property string $session_id
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property string|null $device_type
 * @property string|null $browser
 * @property string|null $platform
 * @property string|null $location
 * @property bool $is_current
 * @property \Carbon\Carbon $last_activity_at
 * @property \Carbon\Carbon $expires_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class UserSession extends Model
{
    protected $fillable = [
        'uuid',
        'user_id',
        'session_id',
        'ip_address',
        'user_agent',
        'device_type',
        'browser',
        'platform',
        'location',
        'is_current',
        'last_activity_at',
        'expires_at',
    ];

    protected $casts = [
        'is_current' => 'boolean',
        'last_activity_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Get the user that owns this session.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if session is active.
     */
    public function isActive(): bool
    {
        return $this->expires_at > now();
    }

    /**
     * Check if session is stale (no activity for a while).
     */
    public function isStale(int $minutes = 30): bool
    {
        return $this->last_activity_at < now()->subMinutes($minutes);
    }

    /**
     * Update last activity timestamp.
     */
    public function touch(): bool
    {
        $this->last_activity_at = now();
        return $this->save();
    }

    /**
     * Parse user agent string.
     */
    public static function parseUserAgent(string $userAgent): array
    {
        $deviceType = 'desktop';
        $browser = 'Unknown';
        $platform = 'Unknown';

        // Detect device type
        if (preg_match('/mobile|android|iphone|ipad|ipod/i', $userAgent)) {
            $deviceType = preg_match('/tablet|ipad/i', $userAgent) ? 'tablet' : 'mobile';
        }

        // Detect browser
        if (preg_match('/Firefox\/([\d.]+)/i', $userAgent, $m)) {
            $browser = 'Firefox ' . $m[1];
        } elseif (preg_match('/Chrome\/([\d.]+)/i', $userAgent, $m)) {
            if (preg_match('/Edg\/([\d.]+)/i', $userAgent, $e)) {
                $browser = 'Edge ' . $e[1];
            } else {
                $browser = 'Chrome ' . $m[1];
            }
        } elseif (preg_match('/Safari\/([\d.]+)/i', $userAgent, $m)) {
            if (preg_match('/Version\/([\d.]+)/i', $userAgent, $v)) {
                $browser = 'Safari ' . $v[1];
            }
        } elseif (preg_match('/MSIE ([\d.]+)/i', $userAgent, $m)) {
            $browser = 'IE ' . $m[1];
        }

        // Detect platform
        if (preg_match('/Windows NT ([\d.]+)/i', $userAgent, $m)) {
            $platform = 'Windows';
        } elseif (preg_match('/Mac OS X ([\d._]+)/i', $userAgent, $m)) {
            $platform = 'macOS';
        } elseif (preg_match('/Linux/i', $userAgent)) {
            $platform = 'Linux';
        } elseif (preg_match('/Android ([\d.]+)/i', $userAgent, $m)) {
            $platform = 'Android ' . $m[1];
        } elseif (preg_match('/iPhone OS ([\d_]+)/i', $userAgent, $m)) {
            $platform = 'iOS ' . str_replace('_', '.', $m[1]);
        }

        return [
            'device_type' => $deviceType,
            'browser' => $browser,
            'platform' => $platform,
        ];
    }

    /**
     * Create a new session record.
     */
    public static function createSession(
        User $user,
        string $sessionId,
        ?string $ipAddress = null,
        ?string $userAgent = null,
        int $lifetimeMinutes = 120
    ): self {
        $parsed = $userAgent ? self::parseUserAgent($userAgent) : [];

        // Mark all other sessions as not current
        self::where('user_id', $user->id)
            ->where('is_current', true)
            ->update(['is_current' => false]);

        return self::create([
            'uuid' => Str::uuid(),
            'user_id' => $user->id,
            'session_id' => $sessionId,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent ? substr($userAgent, 0, 500) : null,
            'device_type' => $parsed['device_type'] ?? null,
            'browser' => $parsed['browser'] ?? null,
            'platform' => $parsed['platform'] ?? null,
            'is_current' => true,
            'last_activity_at' => now(),
            'expires_at' => now()->addMinutes($lifetimeMinutes),
        ]);
    }

    /**
     * Scope to filter active sessions.
     */
    public function scopeActive($query)
    {
        return $query->where('expires_at', '>', now());
    }

    /**
     * Scope to filter expired sessions.
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }

    /**
     * Terminate this session.
     */
    public function terminate(): void
    {
        $this->update([
            'expires_at' => now(),
            'is_current' => false,
        ]);
    }

    /**
     * Terminate all other sessions for user.
     */
    public static function terminateOtherSessions(User $user, string $exceptSessionId): int
    {
        return self::where('user_id', $user->id)
            ->where('session_id', '!=', $exceptSessionId)
            ->active()
            ->update([
                'expires_at' => now(),
                'is_current' => false,
            ]);
    }

    /**
     * Get session icon based on device type.
     */
    public function getDeviceIcon(): string
    {
        return match ($this->device_type) {
            'mobile' => 'device-mobile',
            'tablet' => 'device-tablet',
            default => 'computer-desktop',
        };
    }
}
