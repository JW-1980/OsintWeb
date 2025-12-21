<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
