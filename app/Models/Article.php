<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Article extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'author_id',
        'category_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'type',
        'is_premium',
        'is_featured',
        'is_pinned',
        'status',
        'published_at',
        'scheduled_at',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'view_count',
        'share_count',
        'allow_comments',
        'comments_locked',
        'comments_locked_at',
        'comments_lock_reason',
        'reading_time_minutes',
    ];

    protected $casts = [
        'is_premium' => 'boolean',
        'is_featured' => 'boolean',
        'is_pinned' => 'boolean',
        'allow_comments' => 'boolean',
        'comments_locked' => 'boolean',
        'meta_keywords' => 'array',
        'published_at' => 'datetime',
        'scheduled_at' => 'datetime',
        'comments_locked_at' => 'datetime',
        'view_count' => 'integer',
        'share_count' => 'integer',
        'reading_time_minutes' => 'integer',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Article $article) {
            if (empty($article->uuid)) {
                $article->uuid = Str::uuid()->toString();
            }
            if (empty($article->slug)) {
                $article->slug = Str::slug($article->title);
            }
            $article->reading_time_minutes = $article->calculateReadingTime();
        });

        static::updating(function (Article $article) {
            if ($article->isDirty('content')) {
                $article->reading_time_minutes = $article->calculateReadingTime();
            }
        });
    }

    /**
     * Calculate reading time based on content.
     */
    public function calculateReadingTime(): int
    {
        $wordCount = str_word_count(strip_tags($this->content ?? ''));
        $wordsPerMinute = 200;
        return max(1, (int) ceil($wordCount / $wordsPerMinute));
    }

    /**
     * Check if user can access this article.
     */
    public function canAccess(?User $user): bool
    {
        if (!$this->is_premium) {
            return true;
        }

        if (!$user) {
            return false;
        }

        // Check if user has premium subscription
        return $user->hasActiveSubscription(['premium', 'enterprise']);
    }

    /**
     * Check if comments are allowed.
     */
    public function canComment(): bool
    {
        return $this->allow_comments && !$this->comments_locked && $this->status === 'published';
    }

    /**
     * Scope for published articles.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->where('published_at', '<=', now());
    }

    /**
     * Scope for featured articles.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope for premium articles.
     */
    public function scopePremium($query)
    {
        return $query->where('is_premium', true);
    }

    /**
     * Scope for free articles.
     */
    public function scopeFree($query)
    {
        return $query->where('is_premium', false);
    }

    /**
     * Scope by type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ArticleCategory::class, 'category_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(ArticleTag::class, 'article_tag');
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function approvedComments(): MorphMany
    {
        return $this->comments()->where('status', 'approved');
    }

    public function topLevelComments(): MorphMany
    {
        return $this->approvedComments()->whereNull('parent_id');
    }

    public function reads(): HasMany
    {
        return $this->hasMany(ArticleRead::class);
    }

    public function bookmarks(): HasMany
    {
        return $this->hasMany(ArticleBookmark::class);
    }

    /**
     * Increment view count.
     */
    public function incrementViewCount(): void
    {
        $this->increment('view_count');
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
