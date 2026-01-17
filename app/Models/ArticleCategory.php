<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * ArticleCategory Model
 *
 * Categories for organizing articles and news content.
 *
 * Database Schema:
 * @property int $id Primary key
 * @property string $uuid Unique identifier for external reference
 * @property string $name Category display name
 * @property string $slug URL-friendly slug
 * @property string|null $description Category description
 * @property string|null $color Hex color code for UI display
 * @property string|null $icon Icon identifier (e.g., FontAwesome class)
 * @property int $sort_order Display order (lower = first)
 * @property bool $is_active Whether category is active
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|Article[] $articles
 * @property-read \Illuminate\Database\Eloquent\Collection|Article[] $publishedArticles
 */
class ArticleCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'name',
        'slug',
        'description',
        'color',
        'icon',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (ArticleCategory $category) {
            if (empty($category->uuid)) {
                $category->uuid = Str::uuid()->toString();
            }
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class, 'category_id');
    }

    public function publishedArticles(): HasMany
    {
        return $this->articles()->published();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
