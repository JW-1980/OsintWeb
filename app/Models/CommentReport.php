<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * CommentReport Model
 *
 * User reports for inappropriate or rule-violating comments.
 *
 * Database Schema:
 * @property int $id Primary key
 * @property string $uuid Unique identifier for external reference
 * @property int $comment_id Foreign key to comments table
 * @property int|null $reporter_id Foreign key to users table (reporter)
 * @property string|null $reporter_ip IP address if anonymous reporter
 * @property string $reason Report reason code (spam, harassment, hate_speech, etc.)
 * @property string|null $details Additional details from reporter
 * @property string $status Review status (pending, reviewed, actioned, dismissed)
 * @property int|null $reviewed_by Foreign key to users table (reviewer)
 * @property \Carbon\Carbon|null $reviewed_at When report was reviewed
 * @property string|null $review_notes Reviewer's notes
 * @property string|null $action_taken Action taken on the comment
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property-read Comment $comment
 * @property-read User|null $reporter
 * @property-read User|null $reviewer
 * @property-read string $reason_label Human-readable reason
 */
class CommentReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'comment_id',
        'reporter_id',
        'reporter_ip',
        'reason',
        'details',
        'status',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
        'action_taken',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public const REASONS = [
        'spam' => 'Spam or advertising',
        'harassment' => 'Harassment or bullying',
        'hate_speech' => 'Hate speech or discrimination',
        'misinformation' => 'Misinformation or false claims',
        'off_topic' => 'Off-topic or irrelevant',
        'inappropriate' => 'Inappropriate content',
        'copyright' => 'Copyright violation',
        'other' => 'Other',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (CommentReport $report) {
            if (empty($report->uuid)) {
                $report->uuid = Str::uuid()->toString();
            }
        });
    }

    /**
     * Mark as reviewed.
     */
    public function review(User $reviewer, string $status, ?string $notes = null, ?string $action = null): void
    {
        $this->update([
            'status' => $status,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'review_notes' => $notes,
            'action_taken' => $action,
        ]);
    }

    /**
     * Dismiss the report.
     */
    public function dismiss(User $reviewer, ?string $notes = null): void
    {
        $this->review($reviewer, 'dismissed', $notes);
    }

    /**
     * Take action on the report.
     */
    public function takeAction(User $reviewer, string $action, ?string $notes = null): void
    {
        $this->review($reviewer, 'actioned', $notes, $action);
    }

    /**
     * Scope for pending reports.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for reviewed reports.
     */
    public function scopeReviewed($query)
    {
        return $query->whereIn('status', ['reviewed', 'actioned', 'dismissed']);
    }

    public function comment(): BelongsTo
    {
        return $this->belongsTo(Comment::class);
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Get human-readable reason.
     */
    public function getReasonLabelAttribute(): string
    {
        return self::REASONS[$this->reason] ?? $this->reason;
    }
}
