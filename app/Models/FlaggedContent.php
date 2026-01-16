<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Flagged Content Model
 *
 * Tracks content that has been flagged for potential disinformation
 * with detailed audit trail and resolution workflow.
 *
 * Database Schema (flagged_content):
 * @property int $id Primary key
 * @property string $uuid Public-facing unique identifier
 * @property int $disinformation_analysis_id Link to parent analysis
 * @property string $content_type Polymorphic type
 * @property int $content_id Polymorphic ID
 * @property string $flag_reason Reason for flagging
 * @property string|null $flag_notes Additional notes about the flag
 * @property array|null $flag_metadata Additional context about the flag
 * @property int $flagged_by User ID who flagged this
 * @property \Carbon\Carbon $flagged_at When content was flagged
 * @property string $resolution_status Current resolution status
 * @property int|null $resolved_by User ID who resolved this
 * @property \Carbon\Carbon|null $resolved_at When resolved
 * @property string|null $resolution_notes Notes from resolution
 * @property array|null $actions_taken List of moderation actions
 * @property bool $has_appeal Whether content has been appealed
 * @property string|null $appeal_reason Reason for appeal
 * @property \Carbon\Carbon|null $appealed_at When appealed
 * @property int|null $appeal_reviewed_by User who reviewed appeal
 * @property string $priority Flag priority level
 * @property bool $content_hidden Whether content is hidden
 * @property bool $content_removed Whether content is removed
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 *
 * Relationships:
 * @property-read DisinformationAnalysis $analysis Parent analysis
 * @property-read Model $content Polymorphic flagged content
 * @property-read User $flagger User who flagged this
 * @property-read User|null $resolver User who resolved this
 * @property-read User|null $appealReviewer User who reviewed the appeal
 */
class FlaggedContent extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'flagged_content';

    // Flag reason constants
    public const REASON_AUTOMATED_DETECTION = 'automated_detection';
    public const REASON_USER_REPORT = 'user_report';
    public const REASON_MODERATOR_REVIEW = 'moderator_review';
    public const REASON_PATTERN_MATCH = 'pattern_match';
    public const REASON_AI_DETECTION = 'ai_detection';
    public const REASON_NETWORK_ANALYSIS = 'network_analysis';

    // Resolution status constants
    public const STATUS_PENDING = 'pending';
    public const STATUS_UNDER_REVIEW = 'under_review';
    public const STATUS_CONFIRMED_DISINFORMATION = 'confirmed_disinformation';
    public const STATUS_FALSE_POSITIVE = 'false_positive';
    public const STATUS_INCONCLUSIVE = 'inconclusive';
    public const STATUS_APPEALED = 'appealed';
    public const STATUS_RESOLVED = 'resolved';

    // Priority constants
    public const PRIORITY_LOW = 'low';
    public const PRIORITY_MEDIUM = 'medium';
    public const PRIORITY_HIGH = 'high';
    public const PRIORITY_CRITICAL = 'critical';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'uuid',
        'disinformation_analysis_id',
        'content_type',
        'content_id',
        'flag_reason',
        'flag_notes',
        'flag_metadata',
        'flagged_by',
        'flagged_at',
        'resolution_status',
        'resolved_by',
        'resolved_at',
        'resolution_notes',
        'actions_taken',
        'has_appeal',
        'appeal_reason',
        'appealed_at',
        'appeal_reviewed_by',
        'priority',
        'content_hidden',
        'content_removed',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'flag_metadata' => 'array',
        'flagged_at' => 'datetime',
        'resolved_at' => 'datetime',
        'actions_taken' => 'array',
        'has_appeal' => 'boolean',
        'appealed_at' => 'datetime',
        'content_hidden' => 'boolean',
        'content_removed' => 'boolean',
    ];

    /**
     * Get all flag reasons.
     *
     * @return array<string>
     */
    public static function getFlagReasons(): array
    {
        return [
            self::REASON_AUTOMATED_DETECTION,
            self::REASON_USER_REPORT,
            self::REASON_MODERATOR_REVIEW,
            self::REASON_PATTERN_MATCH,
            self::REASON_AI_DETECTION,
            self::REASON_NETWORK_ANALYSIS,
        ];
    }

    /**
     * Get flag reason labels.
     *
     * @return array<string, string>
     */
    public static function getFlagReasonLabels(): array
    {
        return [
            self::REASON_AUTOMATED_DETECTION => 'Automated Detection',
            self::REASON_USER_REPORT => 'User Report',
            self::REASON_MODERATOR_REVIEW => 'Moderator Review',
            self::REASON_PATTERN_MATCH => 'Pattern Match',
            self::REASON_AI_DETECTION => 'AI Detection',
            self::REASON_NETWORK_ANALYSIS => 'Network Analysis',
        ];
    }

    /**
     * Get all resolution statuses.
     *
     * @return array<string>
     */
    public static function getResolutionStatuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_UNDER_REVIEW,
            self::STATUS_CONFIRMED_DISINFORMATION,
            self::STATUS_FALSE_POSITIVE,
            self::STATUS_INCONCLUSIVE,
            self::STATUS_APPEALED,
            self::STATUS_RESOLVED,
        ];
    }

    /**
     * Get resolution status labels.
     *
     * @return array<string, string>
     */
    public static function getResolutionStatusLabels(): array
    {
        return [
            self::STATUS_PENDING => 'Pending Review',
            self::STATUS_UNDER_REVIEW => 'Under Review',
            self::STATUS_CONFIRMED_DISINFORMATION => 'Confirmed Disinformation',
            self::STATUS_FALSE_POSITIVE => 'False Positive',
            self::STATUS_INCONCLUSIVE => 'Inconclusive',
            self::STATUS_APPEALED => 'Appealed',
            self::STATUS_RESOLVED => 'Resolved',
        ];
    }

    /**
     * Get all priorities.
     *
     * @return array<string>
     */
    public static function getPriorities(): array
    {
        return [
            self::PRIORITY_LOW,
            self::PRIORITY_MEDIUM,
            self::PRIORITY_HIGH,
            self::PRIORITY_CRITICAL,
        ];
    }

    /**
     * Get priority labels.
     *
     * @return array<string, string>
     */
    public static function getPriorityLabels(): array
    {
        return [
            self::PRIORITY_LOW => 'Low',
            self::PRIORITY_MEDIUM => 'Medium',
            self::PRIORITY_HIGH => 'High',
            self::PRIORITY_CRITICAL => 'Critical',
        ];
    }

    /**
     * Get the parent analysis.
     */
    public function analysis(): BelongsTo
    {
        return $this->belongsTo(DisinformationAnalysis::class, 'disinformation_analysis_id');
    }

    /**
     * Get the flagged content (polymorphic).
     */
    public function content(): MorphTo
    {
        return $this->morphTo('content', 'content_type', 'content_id');
    }

    /**
     * Get the user who flagged this content.
     */
    public function flagger(): BelongsTo
    {
        return $this->belongsTo(User::class, 'flagged_by');
    }

    /**
     * Get the user who resolved this flag.
     */
    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    /**
     * Get the user who reviewed the appeal.
     */
    public function appealReviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'appeal_reviewed_by');
    }

    /**
     * Get flag reason label.
     *
     * @return string
     */
    public function getFlagReasonLabel(): string
    {
        return self::getFlagReasonLabels()[$this->flag_reason] ?? ucfirst($this->flag_reason);
    }

    /**
     * Get resolution status label.
     *
     * @return string
     */
    public function getResolutionStatusLabel(): string
    {
        return self::getResolutionStatusLabels()[$this->resolution_status] ?? ucfirst($this->resolution_status);
    }

    /**
     * Get priority label.
     *
     * @return string
     */
    public function getPriorityLabel(): string
    {
        return self::getPriorityLabels()[$this->priority] ?? ucfirst($this->priority);
    }

    /**
     * Get priority color.
     *
     * @return string
     */
    public function getPriorityColorAttribute(): string
    {
        return match ($this->priority) {
            self::PRIORITY_LOW => '#22c55e',
            self::PRIORITY_MEDIUM => '#f59e0b',
            self::PRIORITY_HIGH => '#ef4444',
            self::PRIORITY_CRITICAL => '#b91c1c',
            default => '#94a3b8',
        };
    }

    /**
     * Get status color.
     *
     * @return string
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->resolution_status) {
            self::STATUS_PENDING => '#94a3b8',
            self::STATUS_UNDER_REVIEW => '#3b82f6',
            self::STATUS_CONFIRMED_DISINFORMATION => '#ef4444',
            self::STATUS_FALSE_POSITIVE => '#22c55e',
            self::STATUS_INCONCLUSIVE => '#f59e0b',
            self::STATUS_APPEALED => '#8b5cf6',
            self::STATUS_RESOLVED => '#10b981',
            default => '#64748b',
        };
    }

    /**
     * Check if pending resolution.
     *
     * @return bool
     */
    public function isPending(): bool
    {
        return $this->resolution_status === self::STATUS_PENDING;
    }

    /**
     * Check if under review.
     *
     * @return bool
     */
    public function isUnderReview(): bool
    {
        return $this->resolution_status === self::STATUS_UNDER_REVIEW;
    }

    /**
     * Check if resolved.
     *
     * @return bool
     */
    public function isResolved(): bool
    {
        return in_array($this->resolution_status, [
            self::STATUS_CONFIRMED_DISINFORMATION,
            self::STATUS_FALSE_POSITIVE,
            self::STATUS_RESOLVED,
        ]);
    }

    /**
     * Check if confirmed as disinformation.
     *
     * @return bool
     */
    public function isConfirmedDisinformation(): bool
    {
        return $this->resolution_status === self::STATUS_CONFIRMED_DISINFORMATION;
    }

    /**
     * Check if false positive.
     *
     * @return bool
     */
    public function isFalsePositive(): bool
    {
        return $this->resolution_status === self::STATUS_FALSE_POSITIVE;
    }

    /**
     * Start review process.
     *
     * @param int $reviewerId Reviewer user ID
     */
    public function startReview(int $reviewerId): void
    {
        $this->update([
            'resolution_status' => self::STATUS_UNDER_REVIEW,
            'resolved_by' => $reviewerId,
        ]);
    }

    /**
     * Resolve as confirmed disinformation.
     *
     * @param int $resolverId Resolver user ID
     * @param string|null $notes Resolution notes
     * @param array|null $actions Actions taken
     */
    public function confirmDisinformation(int $resolverId, ?string $notes = null, ?array $actions = null): void
    {
        $this->update([
            'resolution_status' => self::STATUS_CONFIRMED_DISINFORMATION,
            'resolved_by' => $resolverId,
            'resolved_at' => now(),
            'resolution_notes' => $notes,
            'actions_taken' => $actions,
        ]);
    }

    /**
     * Resolve as false positive.
     *
     * @param int $resolverId Resolver user ID
     * @param string|null $notes Resolution notes
     */
    public function markFalsePositive(int $resolverId, ?string $notes = null): void
    {
        $this->update([
            'resolution_status' => self::STATUS_FALSE_POSITIVE,
            'resolved_by' => $resolverId,
            'resolved_at' => now(),
            'resolution_notes' => $notes,
        ]);
    }

    /**
     * Mark as inconclusive.
     *
     * @param int $resolverId Resolver user ID
     * @param string|null $notes Resolution notes
     */
    public function markInconclusive(int $resolverId, ?string $notes = null): void
    {
        $this->update([
            'resolution_status' => self::STATUS_INCONCLUSIVE,
            'resolved_by' => $resolverId,
            'resolved_at' => now(),
            'resolution_notes' => $notes,
        ]);
    }

    /**
     * Submit appeal.
     *
     * @param string $reason Appeal reason
     */
    public function submitAppeal(string $reason): void
    {
        $this->update([
            'has_appeal' => true,
            'appeal_reason' => $reason,
            'appealed_at' => now(),
            'resolution_status' => self::STATUS_APPEALED,
        ]);
    }

    /**
     * Add moderation action.
     *
     * @param string $action Action taken
     * @param array $details Action details
     */
    public function addAction(string $action, array $details = []): void
    {
        $actions = $this->actions_taken ?? [];
        $actions[] = [
            'action' => $action,
            'details' => $details,
            'timestamp' => now()->toIso8601String(),
        ];
        $this->update(['actions_taken' => $actions]);
    }

    /**
     * Hide content.
     */
    public function hideContent(): void
    {
        $this->update(['content_hidden' => true]);
        $this->addAction('content_hidden', []);
    }

    /**
     * Remove content.
     */
    public function removeContent(): void
    {
        $this->update(['content_removed' => true]);
        $this->addAction('content_removed', []);
    }

    /**
     * Restore content visibility.
     */
    public function restoreContent(): void
    {
        $this->update([
            'content_hidden' => false,
            'content_removed' => false,
        ]);
        $this->addAction('content_restored', []);
    }

    /**
     * Scope for pending flags.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query)
    {
        return $query->where('resolution_status', self::STATUS_PENDING);
    }

    /**
     * Scope for under review flags.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUnderReview($query)
    {
        return $query->where('resolution_status', self::STATUS_UNDER_REVIEW);
    }

    /**
     * Scope for resolved flags.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeResolved($query)
    {
        return $query->whereIn('resolution_status', [
            self::STATUS_CONFIRMED_DISINFORMATION,
            self::STATUS_FALSE_POSITIVE,
            self::STATUS_RESOLVED,
        ]);
    }

    /**
     * Scope for confirmed disinformation.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeConfirmed($query)
    {
        return $query->where('resolution_status', self::STATUS_CONFIRMED_DISINFORMATION);
    }

    /**
     * Scope for appealed flags.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAppealed($query)
    {
        return $query->where('has_appeal', true);
    }

    /**
     * Scope for high priority flags.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeHighPriority($query)
    {
        return $query->whereIn('priority', [self::PRIORITY_HIGH, self::PRIORITY_CRITICAL]);
    }

    /**
     * Scope for specific priority.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $priority
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope for specific flag reason.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $reason
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByReason($query, string $reason)
    {
        return $query->where('flag_reason', $reason);
    }

    /**
     * Scope for flags by user.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('flagged_by', $userId);
    }

    /**
     * Scope for recent flags.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $days
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('flagged_at', '>=', now()->subDays($days));
    }

    /**
     * Scope for content that needs action.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNeedsAction($query)
    {
        return $query->whereIn('resolution_status', [
            self::STATUS_PENDING,
            self::STATUS_APPEALED,
        ])->orderByRaw("
            CASE priority
                WHEN 'critical' THEN 1
                WHEN 'high' THEN 2
                WHEN 'medium' THEN 3
                WHEN 'low' THEN 4
            END
        ");
    }
}
