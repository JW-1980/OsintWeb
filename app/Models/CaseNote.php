<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * CaseNote Model - Notes and documentation for investigation cases
 *
 * Supports various note types for organizing investigation findings:
 * - Observations: Raw observations and data points
 * - Hypotheses: Working theories and assumptions
 * - Findings: Verified conclusions and discoveries
 * - Action Items: Tasks and follow-up items
 * - Timeline Entries: Chronological event notes
 *
 * Features:
 * - Threaded replies (parent_id for nested discussions)
 * - Pinning important notes
 * - User mentions for collaboration
 * - File attachments
 *
 * @property int $id
 * @property int $case_id Foreign key to cases table
 * @property int|null $user_id Author of the note
 * @property string $note_type Type classification
 * @property string|null $title Optional note title
 * @property string $content Note content (supports markdown)
 * @property array|null $attachments File attachments as JSON
 * @property bool $is_pinned Whether note is pinned to top
 * @property int|null $parent_id Parent note for threading
 * @property array|null $mentioned_users User IDs mentioned in note
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 *
 * @property-read InvestigationCase $case
 * @property-read User|null $user
 * @property-read CaseNote|null $parent
 * @property-read \Illuminate\Database\Eloquent\Collection|CaseNote[] $replies
 */
class CaseNote extends Model
{
    use SoftDeletes;

    // Note type constants
    public const TYPE_OBSERVATION = 'observation';
    public const TYPE_HYPOTHESIS = 'hypothesis';
    public const TYPE_FINDING = 'finding';
    public const TYPE_ACTION_ITEM = 'action_item';
    public const TYPE_TIMELINE_ENTRY = 'timeline_entry';

    protected $table = 'case_notes';

    protected $fillable = [
        'case_id',
        'user_id',
        'note_type',
        'title',
        'content',
        'attachments',
        'is_pinned',
        'parent_id',
        'mentioned_users',
    ];

    protected $casts = [
        'attachments' => 'array',
        'is_pinned' => 'boolean',
        'mentioned_users' => 'array',
    ];

    protected $attributes = [
        'note_type' => self::TYPE_OBSERVATION,
        'is_pinned' => false,
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->user_id) && auth()->check()) {
                $model->user_id = auth()->id();
            }
        });
    }

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    /**
     * Get the case this note belongs to
     */
    public function case(): BelongsTo
    {
        return $this->belongsTo(InvestigationCase::class, 'case_id');
    }

    /**
     * Get the author of this note
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the parent note (if this is a reply)
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(CaseNote::class, 'parent_id');
    }

    /**
     * Get replies to this note
     */
    public function replies(): HasMany
    {
        return $this->hasMany(CaseNote::class, 'parent_id');
    }

    // =========================================================================
    // METHODS
    // =========================================================================

    /**
     * Pin this note
     */
    public function pin(): void
    {
        $this->update(['is_pinned' => true]);
    }

    /**
     * Unpin this note
     */
    public function unpin(): void
    {
        $this->update(['is_pinned' => false]);
    }

    /**
     * Toggle pin status
     */
    public function togglePin(): void
    {
        $this->update(['is_pinned' => !$this->is_pinned]);
    }

    /**
     * Add an attachment to this note
     */
    public function addAttachment(array $attachment): void
    {
        $attachments = $this->attachments ?? [];
        $attachments[] = $attachment;
        $this->update(['attachments' => $attachments]);
    }

    /**
     * Remove an attachment from this note
     */
    public function removeAttachment(int $index): void
    {
        $attachments = $this->attachments ?? [];
        unset($attachments[$index]);
        $this->update(['attachments' => array_values($attachments)]);
    }

    /**
     * Mention a user in this note
     */
    public function mentionUser(int $userId): void
    {
        $mentioned = $this->mentioned_users ?? [];
        if (!in_array($userId, $mentioned, true)) {
            $mentioned[] = $userId;
            $this->update(['mentioned_users' => $mentioned]);
        }
    }

    /**
     * Create a reply to this note
     */
    public function reply(string $content, ?string $noteType = null): CaseNote
    {
        return static::create([
            'case_id' => $this->case_id,
            'parent_id' => $this->id,
            'content' => $content,
            'note_type' => $noteType ?? self::TYPE_OBSERVATION,
        ]);
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    /**
     * Scope to filter by case
     */
    public function scopeForCase($query, int $caseId)
    {
        return $query->where('case_id', $caseId);
    }

    /**
     * Scope to filter by note type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('note_type', $type);
    }

    /**
     * Scope to filter pinned notes
     */
    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }

    /**
     * Scope to filter root notes (not replies)
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope to filter replies only
     */
    public function scopeReplies($query)
    {
        return $query->whereNotNull('parent_id');
    }

    /**
     * Scope to filter notes by author
     */
    public function scopeByAuthor($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to filter notes mentioning a user
     */
    public function scopeMentioning($query, int $userId)
    {
        return $query->whereJsonContains('mentioned_users', $userId);
    }

    /**
     * Scope to order by most recent
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Scope to order pinned first, then by date
     */
    public function scopeOrderByPinnedAndDate($query)
    {
        return $query->orderBy('is_pinned', 'desc')
            ->orderBy('created_at', 'desc');
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    /**
     * Check if this note is a reply
     */
    public function isReply(): bool
    {
        return $this->parent_id !== null;
    }

    /**
     * Check if this note has replies
     */
    public function hasReplies(): bool
    {
        return $this->replies()->exists();
    }

    /**
     * Get the reply count
     */
    public function getReplyCount(): int
    {
        return $this->replies()->count();
    }

    /**
     * Check if this note has attachments
     */
    public function hasAttachments(): bool
    {
        return !empty($this->attachments);
    }

    /**
     * Get attachment count
     */
    public function getAttachmentCount(): int
    {
        return count($this->attachments ?? []);
    }

    /**
     * Check if a user is mentioned in this note
     */
    public function mentionsUser(int $userId): bool
    {
        return in_array($userId, $this->mentioned_users ?? [], true);
    }

    /**
     * Extract content preview (first 200 chars)
     */
    public function getPreview(int $length = 200): string
    {
        $content = strip_tags($this->content);
        if (strlen($content) <= $length) {
            return $content;
        }
        return substr($content, 0, $length) . '...';
    }

    // =========================================================================
    // STATIC REFERENCE DATA
    // =========================================================================

    /**
     * Get available note types
     */
    public static function getNoteTypes(): array
    {
        return [
            self::TYPE_OBSERVATION => 'Observation',
            self::TYPE_HYPOTHESIS => 'Hypothesis',
            self::TYPE_FINDING => 'Finding',
            self::TYPE_ACTION_ITEM => 'Action Item',
            self::TYPE_TIMELINE_ENTRY => 'Timeline Entry',
        ];
    }
}
