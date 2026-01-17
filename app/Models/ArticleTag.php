<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

/**
 * ArticleTag Model
 *
 * Tags for categorizing and organizing articles.
 *
 * Database Schema:
 * @property int $id Primary key
 * @property string $name Tag display name
 * @property string $slug URL-friendly slug (unique)
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|Article[] $articles
 * @property-read \Illuminate\Database\Eloquent\Collection|Article[] $publishedArticles
 */
class ArticleTag extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (ArticleTag $tag) {
            if (empty($tag->slug)) {
                $tag->slug = Str::slug($tag->name);
            }
        });
    }

    public function articles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class, 'article_tag');
    }

    public function publishedArticles(): BelongsToMany
    {
        return $this->articles()->published();
    }

    /**
     * Find or create a tag by name.
     */
    public static function findOrCreateByName(string $name): self
    {
        $slug = Str::slug($name);
        return self::firstOrCreate(
            ['slug' => $slug],
            ['name' => $name]
        );
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
