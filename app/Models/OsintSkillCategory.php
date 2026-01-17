<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * OsintSkillCategory Model
 *
 * Categories for organizing OSINT skills.
 *
 * Database Schema:
 * @property int $id Primary key
 * @property string $uuid Unique identifier for external reference
 * @property string $name Category name
 * @property string $slug URL-friendly slug (unique)
 * @property string|null $description Category description
 * @property string|null $icon Icon identifier for UI display
 * @property string|null $color Hex color code for UI display
 * @property int $sort_order Display order (lower = first)
 * @property bool $is_active Whether category is currently active
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|OsintSkill[] $skills
 * @property-read \Illuminate\Database\Eloquent\Collection|OsintSkill[] $activeSkills
 */
class OsintSkillCategory extends Model
{
    protected $fillable = [
        'uuid',
        'name',
        'slug',
        'description',
        'icon',
        'color',
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

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->name);
            }
        });
    }

    public function skills(): HasMany
    {
        return $this->hasMany(OsintSkill::class, 'category_id');
    }

    public function activeSkills(): HasMany
    {
        return $this->skills()->where('is_active', true)->orderBy('sort_order');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
