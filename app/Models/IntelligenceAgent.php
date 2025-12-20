<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class IntelligenceAgent extends Model
{
    use SoftDeletes;

    public const TYPE_MONITOR = 'monitor';
    public const TYPE_SCRAPER = 'scraper';
    public const TYPE_ANALYZER = 'analyzer';
    public const TYPE_AGGREGATOR = 'aggregator';
    public const TYPE_ALERT = 'alert';

    public const CATEGORY_SOCIAL_MEDIA = 'social_media';
    public const CATEGORY_FLIGHT = 'flight';
    public const CATEGORY_MARITIME = 'maritime';
    public const CATEGORY_SATELLITE = 'satellite';
    public const CATEGORY_NEWS = 'news';
    public const CATEGORY_GEOLOCATION = 'geolocation';
    public const CATEGORY_WEAPONS = 'weapons';
    public const CATEGORY_MILITARY = 'military';
    public const CATEGORY_TRANSLATION = 'translation';
    public const CATEGORY_VERIFICATION = 'verification';

    protected $fillable = [
        'uuid',
        'name',
        'slug',
        'description',
        'type',
        'category',
        'configuration',
        'data_sources',
        'output_format',
        'filters',
        'triggers',
        'schedule',
        'is_active',
        'requires_api_key',
        'api_providers',
        'rate_limit_per_minute',
        'priority',
    ];

    protected $casts = [
        'configuration' => 'array',
        'data_sources' => 'array',
        'output_format' => 'array',
        'filters' => 'array',
        'triggers' => 'array',
        'api_providers' => 'array',
        'is_active' => 'boolean',
        'requires_api_key' => 'boolean',
        'rate_limit_per_minute' => 'integer',
        'priority' => 'integer',
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

    public function executions(): HasMany
    {
        return $this->hasMany(AgentExecution::class, 'agent_id');
    }

    public function dataPoints(): HasMany
    {
        return $this->hasMany(AgentDataPoint::class, 'agent_id');
    }

    public function latestExecution()
    {
        return $this->hasOne(AgentExecution::class, 'agent_id')->latest();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeOrdered($query)
    {
        return $query->orderByDesc('priority')->orderBy('name');
    }

    public static function getTypes(): array
    {
        return [
            self::TYPE_MONITOR => 'Monitor',
            self::TYPE_SCRAPER => 'Scraper',
            self::TYPE_ANALYZER => 'Analyzer',
            self::TYPE_AGGREGATOR => 'Aggregator',
            self::TYPE_ALERT => 'Alert',
        ];
    }

    public static function getCategories(): array
    {
        return [
            self::CATEGORY_SOCIAL_MEDIA => 'Social Media',
            self::CATEGORY_FLIGHT => 'Flight Tracking',
            self::CATEGORY_MARITIME => 'Maritime Intelligence',
            self::CATEGORY_SATELLITE => 'Satellite Imagery',
            self::CATEGORY_NEWS => 'News Monitoring',
            self::CATEGORY_GEOLOCATION => 'Geolocation',
            self::CATEGORY_WEAPONS => 'Weapons Identification',
            self::CATEGORY_MILITARY => 'Military Intelligence',
            self::CATEGORY_TRANSLATION => 'Translation',
            self::CATEGORY_VERIFICATION => 'Verification',
        ];
    }
}
