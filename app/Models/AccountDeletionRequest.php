<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Account Deletion Request Model
 *
 * Handles GDPR right to be forgotten requests.
 *
 * @property int $id
 * @property string $uuid
 * @property int $user_id
 * @property string $status
 * @property string|null $confirmation_token
 * @property \Carbon\Carbon|null $confirmation_sent_at
 * @property \Carbon\Carbon|null $confirmed_at
 * @property \Carbon\Carbon|null $scheduled_for
 * @property \Carbon\Carbon|null $completed_at
 * @property \Carbon\Carbon|null $cancelled_at
 * @property string|null $cancellation_reason
 * @property string|null $reason
 * @property string|null $feedback
 * @property string|null $ip_address
 * @property array|null $deleted_data_summary
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class AccountDeletionRequest extends Model
{
    protected $fillable = [
        'uuid',
        'user_id',
        'status',
        'confirmation_token',
        'confirmation_sent_at',
        'confirmed_at',
        'scheduled_for',
        'completed_at',
        'cancelled_at',
        'cancellation_reason',
        'reason',
        'feedback',
        'ip_address',
        'deleted_data_summary',
    ];

    protected $casts = [
        'confirmation_sent_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'scheduled_for' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'deleted_data_summary' => 'array',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    // Grace period in days before deletion
    public const GRACE_PERIOD_DAYS = 14;

    // Deletion reasons
    public const REASON_PRIVACY = 'privacy_concerns';
    public const REASON_NOT_USEFUL = 'not_useful';
    public const REASON_TOO_COMPLEX = 'too_complex';
    public const REASON_ALTERNATIVE = 'found_alternative';
    public const REASON_TEMPORARY = 'temporary_use';
    public const REASON_OTHER = 'other';

    /**
     * Get the user that made this request.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if request can be cancelled.
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_CONFIRMED], true);
    }

    /**
     * Check if request is within grace period.
     */
    public function isWithinGracePeriod(): bool
    {
        if (!$this->scheduled_for) {
            return true;
        }
        return $this->scheduled_for > now();
    }

    /**
     * Send confirmation email.
     */
    public function sendConfirmation(): void
    {
        $this->update([
            'confirmation_token' => Str::random(64),
            'confirmation_sent_at' => now(),
        ]);

        // TODO: Send actual email notification
    }

    /**
     * Confirm the deletion request.
     */
    public function confirm(): void
    {
        $this->update([
            'status' => self::STATUS_CONFIRMED,
            'confirmed_at' => now(),
            'scheduled_for' => now()->addDays(self::GRACE_PERIOD_DAYS),
        ]);
    }

    /**
     * Cancel the deletion request.
     */
    public function cancel(?string $reason = null): void
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
        ]);
    }

    /**
     * Start processing deletion.
     */
    public function startProcessing(): void
    {
        $this->update(['status' => self::STATUS_PROCESSING]);
    }

    /**
     * Complete the deletion.
     */
    public function complete(array $deletedDataSummary): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => now(),
            'deleted_data_summary' => $deletedDataSummary,
        ]);
    }

    /**
     * Create a new deletion request.
     */
    public static function createRequest(
        User $user,
        ?string $reason = null,
        ?string $feedback = null,
        ?string $ipAddress = null
    ): self {
        // Cancel any existing pending requests
        static::where('user_id', $user->id)
            ->whereIn('status', [self::STATUS_PENDING, self::STATUS_CONFIRMED])
            ->update([
                'status' => self::STATUS_CANCELLED,
                'cancelled_at' => now(),
                'cancellation_reason' => 'New request created',
            ]);

        return static::create([
            'uuid' => Str::uuid(),
            'user_id' => $user->id,
            'status' => self::STATUS_PENDING,
            'reason' => $reason,
            'feedback' => $feedback,
            'ip_address' => $ipAddress,
        ]);
    }

    /**
     * Scope to filter by status.
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get requests ready for deletion.
     */
    public function scopeReadyForDeletion($query)
    {
        return $query->where('status', self::STATUS_CONFIRMED)
            ->where('scheduled_for', '<=', now());
    }

    /**
     * Get deletion reason options.
     */
    public static function getReasonOptions(): array
    {
        return [
            self::REASON_PRIVACY => 'Privacy concerns',
            self::REASON_NOT_USEFUL => 'Not finding the platform useful',
            self::REASON_TOO_COMPLEX => 'Too complicated to use',
            self::REASON_ALTERNATIVE => 'Found a better alternative',
            self::REASON_TEMPORARY => 'Only needed temporarily',
            self::REASON_OTHER => 'Other reason',
        ];
    }

    /**
     * Get days remaining until deletion.
     */
    public function getDaysRemaining(): ?int
    {
        if (!$this->scheduled_for) {
            return null;
        }

        return max(0, now()->diffInDays($this->scheduled_for, false));
    }
}
