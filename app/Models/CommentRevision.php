<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * CommentRevision Model
 *
 * Stores edit history for comments.
 *
 * Database Schema:
 * @property int $id Primary key
 * @property int $comment_id Foreign key to comments table
 * @property string $content Original markdown content of this revision
 * @property string|null $content_html Rendered HTML content
 * @property int $revision_number Sequential revision number
 * @property string|null $ip_address IP address of editor
 * @property string|null $edit_reason Reason for the edit
 * @property \Carbon\Carbon $created_at When revision was created
 *
 * @property-read Comment $comment
 */
class CommentRevision extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'comment_id',
        'content',
        'content_html',
        'revision_number',
        'ip_address',
        'edit_reason',
        'created_at',
    ];

    protected $casts = [
        'revision_number' => 'integer',
        'created_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (CommentRevision $revision) {
            $revision->created_at = now();
        });
    }

    public function comment(): BelongsTo
    {
        return $this->belongsTo(Comment::class);
    }
}
