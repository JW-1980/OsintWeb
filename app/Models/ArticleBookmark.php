<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArticleBookmark extends Model
{
    protected $fillable = [
        'article_id',
        'user_id',
        'folder',
        'notes',
    ];

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope by folder.
     */
    public function scopeInFolder($query, ?string $folder)
    {
        if ($folder === null) {
            return $query->whereNull('folder');
        }
        return $query->where('folder', $folder);
    }
}
