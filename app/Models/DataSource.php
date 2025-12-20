<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class DataSource extends Model
{
    use SoftDeletes;

    public const TYPE_ADSB = 'adsb';
    public const TYPE_AIS = 'ais';
    public const TYPE_SATELLITE = 'satellite';
    public const TYPE_SOCIAL = 'social';
    public const TYPE_NEWS = 'news';
    public const TYPE_API = 'api';

    protected $fillable = [
        'uuid',
        'name',
        'slug',
        'type',
        'description',
        'provider',
        'base_url',
        'configuration',
        'credentials',
        'is_active',
        'requires_subscription',
        'rate_limits',
        'last_synced_at',
    ];

    protected $casts = [
        'configuration' => 'array',
        'credentials' => 'encrypted:array',
        'rate_limits' => 'array',
        'is_active' => 'boolean',
        'requires_subscription' => 'boolean',
        'last_synced_at' => 'datetime',
    ];

    protected $hidden = [
        'credentials',
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

    public function markAsSynced(): void
    {
        $this->update(['last_synced_at' => now()]);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeFree($query)
    {
        return $query->where('requires_subscription', false);
    }

    public static function getTypes(): array
    {
        return [
            self::TYPE_ADSB => 'ADS-B Flight Tracking',
            self::TYPE_AIS => 'AIS Maritime Tracking',
            self::TYPE_SATELLITE => 'Satellite Imagery',
            self::TYPE_SOCIAL => 'Social Media',
            self::TYPE_NEWS => 'News Monitoring',
            self::TYPE_API => 'External API',
        ];
    }
}
