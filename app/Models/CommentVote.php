<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * CommentVote Model
 *
 * Stores user votes on comments (upvote/downvote).
 *
 * Database Schema:
 * @property int $id Primary key
 * @property int $comment_id Foreign key to comments table
 * @property int $user_id Foreign key to users table
 * @property int $vote Vote value (1 for upvote, -1 for downvote)
 * @property \Carbon\Carbon $created_at When vote was cast
 *
 * @property-read Comment $comment
 * @property-read User $user
 */
class CommentVote extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'comment_id',
        'user_id',
        'vote',
        'created_at',
    ];

    protected $casts = [
        'vote' => 'integer',
        'created_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (CommentVote $vote) {
            $vote->created_at = now();
        });

        static::created(function (CommentVote $vote) {
            $vote->updateCommentVoteCounts();
        });

        static::updated(function (CommentVote $vote) {
            $vote->updateCommentVoteCounts();
        });

        static::deleted(function (CommentVote $vote) {
            $vote->updateCommentVoteCounts();
        });
    }

    /**
     * Update the comment's vote counts.
     */
    public function updateCommentVoteCounts(): void
    {
        $comment = $this->comment;
        if ($comment) {
            $comment->update([
                'upvotes' => $comment->votes()->where('vote', 1)->count(),
                'downvotes' => $comment->votes()->where('vote', -1)->count(),
            ]);
        }
    }

    public function comment(): BelongsTo
    {
        return $this->belongsTo(Comment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if this is an upvote.
     */
    public function isUpvote(): bool
    {
        return $this->vote === 1;
    }

    /**
     * Check if this is a downvote.
     */
    public function isDownvote(): bool
    {
        return $this->vote === -1;
    }
}
