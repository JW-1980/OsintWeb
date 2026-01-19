<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Password Reset Token Model
 *
 * Manages password reset tokens with secure generation and validation.
 */
class PasswordResetToken extends Model
{
    protected $table = 'password_reset_tokens';

    protected $primaryKey = 'email';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'email',
        'token',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Token expiration time in minutes.
     */
    public const EXPIRATION_MINUTES = 60;

    /**
     * Create a new password reset token for the given email.
     */
    public static function createToken(string $email): string
    {
        $plainToken = Str::random(64);

        static::query()->updateOrInsert(
            ['email' => $email],
            [
                'token' => Hash::make($plainToken),
                'created_at' => now(),
            ]
        );

        return $plainToken;
    }

    /**
     * Verify a token for the given email.
     */
    public static function verifyToken(string $email, string $token): bool
    {
        $record = static::where('email', $email)->first();

        if (!$record) {
            return false;
        }

        // Check expiration
        if ($record->isExpired()) {
            $record->delete();
            return false;
        }

        // Verify token hash
        return Hash::check($token, $record->token);
    }

    /**
     * Delete the token for the given email.
     */
    public static function deleteToken(string $email): void
    {
        static::where('email', $email)->delete();
    }

    /**
     * Check if the token is expired.
     */
    public function isExpired(): bool
    {
        if (!$this->created_at) {
            return true;
        }

        return Carbon::parse($this->created_at)
            ->addMinutes(self::EXPIRATION_MINUTES)
            ->isPast();
    }

    /**
     * Get the remaining time before expiration in minutes.
     */
    public function remainingMinutes(): int
    {
        if (!$this->created_at || $this->isExpired()) {
            return 0;
        }

        return (int) now()->diffInMinutes(
            Carbon::parse($this->created_at)->addMinutes(self::EXPIRATION_MINUTES),
            false
        );
    }

    /**
     * Clean up expired tokens.
     */
    public static function cleanupExpired(): int
    {
        return static::where('created_at', '<', now()->subMinutes(self::EXPIRATION_MINUTES))
            ->delete();
    }
}
