<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ArticleBookmark Model
 *
 * Stores user bookmarks for articles.
 *
 * Database Schema:
 * @property int $id Primary key
 * @property int $article_id Foreign key to articles table
 * @property int $user_id Foreign key to users table
 * @property string|null $folder Optional folder name for organization
 * @property string|null $notes User notes about the bookmark
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property-read Article $article
 * @property-read User $user
 */
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
