<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Email Log Model
 *
 * Tracks sent emails for analytics and debugging.
 *
 * @property int $id
 * @property string $uuid
 * @property int|null $user_id
 * @property string $email
 * @property string $template_slug
 * @property string $subject
 * @property string $status
 * @property array|null $variables
 * @property string|null $error_message
 * @property \Carbon\Carbon|null $sent_at
 * @property \Carbon\Carbon|null $opened_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class EmailLog extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::creating(function (EmailLog $log) {
            if (empty($log->uuid)) {
                $log->uuid = (string) Str::uuid();
            }
        });
    }

    protected $fillable = [
        'uuid',
        'user_id',
        'email',
        'template_slug',
        'subject',
        'status',
        'variables',
        'error_message',
        'sent_at',
        'opened_at',
    ];

    protected $casts = [
        'variables' => 'array',
        'sent_at' => 'datetime',
        'opened_at' => 'datetime',
    ];

    /**
     * Get the route key name for route model binding.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * Get the user who received this email
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the template used for this email
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class, 'template_slug', 'slug');
    }

    /**
     * Mark email as sent
     */
    public function markAsSent(): void
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    /**
     * Mark email as failed
     */
    public function markAsFailed(string $error): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $error,
        ]);
    }

    /**
     * Mark email as opened
     */
    public function markAsOpened(): void
    {
        if ($this->opened_at === null) {
            $this->update([
                'status' => 'opened',
                'opened_at' => now(),
            ]);
        }
    }

    /**
     * Available statuses
     */
    public static function statuses(): array
    {
        return [
            'pending' => 'Pending',
            'sent' => 'Sent',
            'delivered' => 'Delivered',
            'opened' => 'Opened',
            'clicked' => 'Clicked',
            'bounced' => 'Bounced',
            'failed' => 'Failed',
        ];
    }

    /**
     * Scope by status
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope failed emails
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope sent emails
     */
    public function scopeSent($query)
    {
        return $query->whereIn('status', ['sent', 'delivered', 'opened', 'clicked']);
    }
}
