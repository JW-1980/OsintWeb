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

/**
 * Article Model
 *
 * Represents news articles, analysis pieces, reports, and premium content.
 * Supports publishing workflow, SEO metadata, and comment management.
 *
 * Database Table: articles
 *
 * Database Schema:
 * - id: bigint unsigned, auto-increment, primary key
 * - uuid: char(36), unique
 * - author_id: bigint unsigned, foreign key to users
 * - category_id: bigint unsigned, nullable, foreign key to article_categories
 * - title: varchar(255)
 * - slug: varchar(255), unique
 * - excerpt: text, nullable
 * - content: longtext
 * - featured_image: varchar(255), nullable
 * - type: enum('news', 'article', 'analysis', 'report', 'tutorial'), default 'article'
 * - is_premium: boolean, default false
 * - is_featured: boolean, default false
 * - is_pinned: boolean, default false
 * - status: enum('draft', 'pending_review', 'published', 'archived'), default 'draft'
 * - published_at: timestamp, nullable
 * - scheduled_at: timestamp, nullable
 * - meta_title: varchar(255), nullable
 * - meta_description: text, nullable
 * - meta_keywords: json, nullable
 * - view_count: bigint unsigned, default 0
 * - share_count: bigint unsigned, default 0
 * - allow_comments: boolean, default true
 * - comments_locked: boolean, default false
 * - comments_locked_at: timestamp, nullable
 * - comments_lock_reason: varchar(255), nullable
 * - reading_time_minutes: smallint unsigned, default 1
 * - deleted_at: timestamp, nullable
 * - created_at: timestamp, nullable
 * - updated_at: timestamp, nullable
 *
 * @property int $id
 * @property string $uuid
 * @property int $author_id
 * @property int|null $category_id
 * @property string $title
 * @property string $slug
 * @property string|null $excerpt
 * @property string $content
 * @property string|null $featured_image
 * @property string $type
 * @property bool $is_premium
 * @property bool $is_featured
 * @property bool $is_pinned
 * @property string $status
 * @property \Carbon\Carbon|null $published_at
 * @property \Carbon\Carbon|null $scheduled_at
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property array|null $meta_keywords
 * @property int $view_count
 * @property int $share_count
 * @property bool $allow_comments
 * @property bool $comments_locked
 * @property \Carbon\Carbon|null $comments_locked_at
 * @property string|null $comments_lock_reason
 * @property int $reading_time_minutes
 * @property \Carbon\Carbon|null $deleted_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 *
 * @property-read \App\Models\User $author
 * @property-read \App\Models\ArticleCategory|null $category
 * @property-read \Illuminate\Database\Eloquent\Collection<\App\Models\ArticleTag> $tags
 * @property-read \Illuminate\Database\Eloquent\Collection<\App\Models\Comment> $comments
 * @property-read \Illuminate\Database\Eloquent\Collection<\App\Models\Comment> $approvedComments
 * @property-read \Illuminate\Database\Eloquent\Collection<\App\Models\ArticleRead> $reads
 * @property-read \Illuminate\Database\Eloquent\Collection<\App\Models\ArticleBookmark> $bookmarks
 *
 * @method static \Illuminate\Database\Eloquent\Builder published()
 * @method static \Illuminate\Database\Eloquent\Builder featured()
 * @method static \Illuminate\Database\Eloquent\Builder premium()
 * @method static \Illuminate\Database\Eloquent\Builder free()
 * @method static \Illuminate\Database\Eloquent\Builder ofType(string $type)
 */
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
