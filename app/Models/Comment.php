<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Comment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'commentable_type',
        'commentable_id',
        'user_id',
        'guest_name',
        'guest_email',
        'parent_id',
        'depth',
        'path',
        'content',
        'content_html',
        'is_edited',
        'last_edited_at',
        'edit_count',
        'status',
        'moderated_by',
        'moderated_at',
        'moderation_reason',
        'ip_address',
        'user_agent',
        'spam_score',
        'spam_flags',
        'upvotes',
        'downvotes',
        'reply_count',
        'is_pinned',
        'is_author_reply',
        'is_highlighted',
    ];

    protected $casts = [
        'is_edited' => 'boolean',
        'is_pinned' => 'boolean',
        'is_author_reply' => 'boolean',
        'is_highlighted' => 'boolean',
        'last_edited_at' => 'datetime',
        'moderated_at' => 'datetime',
        'spam_flags' => 'array',
        'spam_score' => 'decimal:2',
        'depth' => 'integer',
        'edit_count' => 'integer',
        'upvotes' => 'integer',
        'downvotes' => 'integer',
        'reply_count' => 'integer',
    ];

    protected $appends = ['author_name', 'can_edit', 'can_delete', 'vote_score'];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Comment $comment) {
            if (empty($comment->uuid)) {
                $comment->uuid = Str::uuid()->toString();
            }

            // Set depth and path based on parent
            if ($comment->parent_id) {
                $parent = Comment::find($comment->parent_id);
                if ($parent) {
                    $comment->depth = $parent->depth + 1;
                    $comment->path = $parent->path ? $parent->path . '/' . $parent->id : (string) $parent->id;
                }
            } else {
                $comment->depth = 0;
                $comment->path = null;
            }

            // Parse content to HTML
            $comment->content_html = $comment->parseContent($comment->content);
        });

        static::created(function (Comment $comment) {
            // Update parent reply count
            if ($comment->parent_id) {
                Comment::where('id', $comment->parent_id)->increment('reply_count');
            }
        });

        static::deleted(function (Comment $comment) {
            // Update parent reply count
            if ($comment->parent_id) {
                Comment::where('id', $comment->parent_id)->decrement('reply_count');
            }
        });
    }

    /**
     * Parse markdown content to HTML (basic implementation).
     */
    public function parseContent(string $content): string
    {
        // Basic sanitization and markdown parsing
        $html = htmlspecialchars($content, ENT_QUOTES, 'UTF-8');

        // Convert line breaks
        $html = nl2br($html);

        // Basic markdown: **bold**, *italic*, `code`
        $html = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $html);
        $html = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $html);
        $html = preg_replace('/`(.+?)`/', '<code>$1</code>', $html);

        // Convert URLs to links
        $html = preg_replace(
            '/(https?:\/\/[^\s<]+)/',
            '<a href="$1" target="_blank" rel="noopener noreferrer">$1</a>',
            $html
        );

        return $html;
    }

    /**
     * Check if comment can be edited (within edit window).
     */
    public function canEdit(?User $user = null): bool
    {
        if (!$user) {
            return false;
        }

        // Admin/moderator can always edit
        if ($user->hasPermission('comments.edit')) {
            return true;
        }

        // Must be the author
        if ($this->user_id !== $user->id) {
            return false;
        }

        // Check edit window
        $editWindowMinutes = (int) Setting::get('comment_edit_window_minutes', 15);
        $editDeadline = $this->created_at->addMinutes($editWindowMinutes);

        return now()->isBefore($editDeadline);
    }

    /**
     * Check if comment can be deleted.
     */
    public function canDelete(?User $user = null): bool
    {
        if (!$user) {
            return false;
        }

        // Admin/moderator can always delete
        if ($user->hasPermission('comments.delete')) {
            return true;
        }

        // Author can delete their own comments
        return $this->user_id === $user->id;
    }

    /**
     * Get minutes remaining for edit.
     */
    public function getEditTimeRemaining(): int
    {
        $editWindowMinutes = (int) Setting::get('comment_edit_window_minutes', 15);
        $editDeadline = $this->created_at->addMinutes($editWindowMinutes);

        if (now()->isAfter($editDeadline)) {
            return 0;
        }

        return (int) now()->diffInMinutes($editDeadline);
    }

    /**
     * Edit the comment and create a revision.
     */
    public function editContent(string $newContent, ?string $reason = null, ?string $ipAddress = null): void
    {
        // Create revision of current content
        CommentRevision::create([
            'comment_id' => $this->id,
            'content' => $this->content,
            'content_html' => $this->content_html,
            'revision_number' => $this->edit_count + 1,
            'ip_address' => $ipAddress,
            'edit_reason' => $reason,
        ]);

        // Update comment
        $this->update([
            'content' => $newContent,
            'content_html' => $this->parseContent($newContent),
            'is_edited' => true,
            'last_edited_at' => now(),
            'edit_count' => $this->edit_count + 1,
        ]);
    }

    /**
     * Calculate spam score based on content and patterns.
     */
    public function calculateSpamScore(): float
    {
        $score = 0.0;
        $flags = [];
        $content = strtolower($this->content);

        // Check against spam patterns
        $patterns = SpamPattern::where('is_active', true)->get();

        foreach ($patterns as $pattern) {
            $matched = false;

            switch ($pattern->type) {
                case 'keyword':
                    if (str_contains($content, strtolower($pattern->pattern))) {
                        $matched = true;
                    }
                    break;

                case 'regex':
                    if (preg_match($pattern->pattern, $content)) {
                        $matched = true;
                    }
                    break;

                case 'domain':
                    if (str_contains($content, strtolower($pattern->pattern))) {
                        $matched = true;
                    }
                    break;
            }

            if ($matched) {
                $score += $pattern->weight;
                $flags[] = [
                    'pattern_id' => $pattern->id,
                    'type' => $pattern->type,
                    'pattern' => $pattern->pattern,
                    'weight' => $pattern->weight,
                ];
                $pattern->increment('hit_count');
            }
        }

        // Additional heuristics
        // All caps
        if (strlen($this->content) > 20 && strtoupper($this->content) === $this->content) {
            $score += 0.5;
            $flags[] = ['type' => 'all_caps', 'weight' => 0.5];
        }

        // Too many links
        $linkCount = preg_match_all('/https?:\/\//', $content);
        if ($linkCount > 3) {
            $score += 0.3 * $linkCount;
            $flags[] = ['type' => 'excessive_links', 'count' => $linkCount, 'weight' => 0.3 * $linkCount];
        }

        // Very short comment
        if (strlen(trim($this->content)) < 10) {
            $score += 0.2;
            $flags[] = ['type' => 'too_short', 'weight' => 0.2];
        }

        $this->spam_score = min($score, 10.0);
        $this->spam_flags = $flags;

        return $this->spam_score;
    }

    /**
     * Approve comment.
     */
    public function approve(User $moderator, ?string $reason = null): void
    {
        $this->update([
            'status' => 'approved',
            'moderated_by' => $moderator->id,
            'moderated_at' => now(),
            'moderation_reason' => $reason,
        ]);
    }

    /**
     * Reject comment.
     */
    public function reject(User $moderator, ?string $reason = null): void
    {
        $this->update([
            'status' => 'rejected',
            'moderated_by' => $moderator->id,
            'moderated_at' => now(),
            'moderation_reason' => $reason,
        ]);
    }

    /**
     * Mark as spam.
     */
    public function markAsSpam(User $moderator, ?string $reason = null): void
    {
        $this->update([
            'status' => 'spam',
            'moderated_by' => $moderator->id,
            'moderated_at' => now(),
            'moderation_reason' => $reason,
        ]);
    }

    /**
     * Scope for approved comments.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope for pending moderation.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for top-level comments.
     */
    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope for threaded retrieval (ordered by path).
     */
    public function scopeThreaded($query)
    {
        return $query->orderByRaw('COALESCE(path, id) ASC, created_at ASC');
    }

    // Relationships

    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    public function approvedReplies(): HasMany
    {
        return $this->replies()->approved()->orderBy('created_at');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(CommentRevision::class)->orderBy('revision_number', 'desc');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(CommentVote::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(CommentReport::class);
    }

    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }

    // Accessors

    public function getAuthorNameAttribute(): string
    {
        if ($this->user) {
            return $this->user->name;
        }
        return $this->guest_name ?? 'Anonymous';
    }

    public function getCanEditAttribute(): bool
    {
        return $this->canEdit(auth()->user());
    }

    public function getCanDeleteAttribute(): bool
    {
        return $this->canDelete(auth()->user());
    }

    public function getVoteScoreAttribute(): int
    {
        return $this->upvotes - $this->downvotes;
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
