<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class AgentDataPoint extends Model
{
    use SoftDeletes;

    public const STATUS_RAW = 'raw';
    public const STATUS_PROCESSED = 'processed';
    public const STATUS_VERIFIED = 'verified';
    public const STATUS_ARCHIVED = 'archived';

    public const SOURCE_TWITTER = 'twitter';
    public const SOURCE_TELEGRAM = 'telegram';
    public const SOURCE_ADSB = 'adsb';
    public const SOURCE_AIS = 'ais';
    public const SOURCE_NEWS = 'news';
    public const SOURCE_SATELLITE = 'satellite';
    public const SOURCE_YOUTUBE = 'youtube';
    public const SOURCE_TIKTOK = 'tiktok';
    public const SOURCE_VK = 'vk';
    public const SOURCE_FACEBOOK = 'facebook';
    public const SOURCE_INSTAGRAM = 'instagram';
    public const SOURCE_REDDIT = 'reddit';

    protected $fillable = [
        'uuid',
        'agent_id',
        'execution_id',
        'source_type',
        'source_id',
        'source_url',
        'title',
        'content',
        'metadata',
        'extracted_entities',
        'coordinates',
        'confidence_score',
        'status',
        'source_timestamp',
    ];

    protected $casts = [
        'metadata' => 'array',
        'extracted_entities' => 'array',
        'coordinates' => 'array',
        'confidence_score' => 'decimal:2',
        'source_timestamp' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(IntelligenceAgent::class, 'agent_id');
    }

    public function execution(): BelongsTo
    {
        return $this->belongsTo(AgentExecution::class, 'execution_id');
    }

    public function getLatitude(): ?float
    {
        return $this->coordinates['lat'] ?? null;
    }

    public function getLongitude(): ?float
    {
        return $this->coordinates['lng'] ?? null;
    }

    public function hasCoordinates(): bool
    {
        return !empty($this->coordinates['lat']) && !empty($this->coordinates['lng']);
    }

    public function markAsProcessed(): void
    {
        $this->update(['status' => self::STATUS_PROCESSED]);
    }

    public function markAsVerified(): void
    {
        $this->update(['status' => self::STATUS_VERIFIED]);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeBySource($query, string $source)
    {
        return $query->where('source_type', $source);
    }

    public function scopeHighConfidence($query, float $threshold = 0.7)
    {
        return $query->where('confidence_score', '>=', $threshold);
    }

    public function scopeWithCoordinates($query)
    {
        return $query->whereNotNull('coordinates')
            ->whereRaw("JSON_EXTRACT(coordinates, '$.lat') IS NOT NULL");
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_RAW => 'Raw',
            self::STATUS_PROCESSED => 'Processed',
            self::STATUS_VERIFIED => 'Verified',
            self::STATUS_ARCHIVED => 'Archived',
        ];
    }

    public static function getSourceTypes(): array
    {
        return [
            self::SOURCE_TWITTER => 'Twitter/X',
            self::SOURCE_TELEGRAM => 'Telegram',
            self::SOURCE_ADSB => 'ADS-B Exchange',
            self::SOURCE_AIS => 'AIS Maritime',
            self::SOURCE_NEWS => 'News',
            self::SOURCE_SATELLITE => 'Satellite',
            self::SOURCE_YOUTUBE => 'YouTube',
            self::SOURCE_TIKTOK => 'TikTok',
            self::SOURCE_VK => 'VKontakte',
            self::SOURCE_FACEBOOK => 'Facebook',
            self::SOURCE_INSTAGRAM => 'Instagram',
            self::SOURCE_REDDIT => 'Reddit',
        ];
    }
}
