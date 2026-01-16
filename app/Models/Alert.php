<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * Alert Model
 *
 * Represents user-configured alerts for real-time notifications about events,
 * zone changes, equipment sightings, threshold triggers, and custom conditions.
 *
 * Database Table: alerts
 *
 * Database Schema:
 * - id: bigint unsigned, auto-increment, primary key
 * - uuid: char(36), unique
 * - user_id: bigint unsigned, nullable, foreign key to users
 * - type: varchar(255) (event, zone, equipment, custom, threshold)
 * - name: varchar(255)
 * - description: text, nullable
 * - conditions: json (alert trigger conditions)
 * - filters: json, nullable (geographic, temporal, entity filters)
 * - notification_channels: json, nullable (email, webhook, push)
 * - is_active: boolean, default true
 * - frequency: varchar(255), default 'immediate' (immediate, daily, weekly)
 * - last_triggered_at: timestamp, nullable
 * - trigger_count: int, default 0
 * - deleted_at: timestamp, nullable
 * - created_at: timestamp, nullable
 * - updated_at: timestamp, nullable
 *
 * @property int $id
 * @property string $uuid
 * @property int|null $user_id
 * @property string $type
 * @property string $name
 * @property string|null $description
 * @property array $conditions
 * @property array|null $filters
 * @property array|null $notification_channels
 * @property bool $is_active
 * @property string $frequency
 * @property \Carbon\Carbon|null $last_triggered_at
 * @property int $trigger_count
 * @property \Carbon\Carbon|null $deleted_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 *
 * @property-read \App\Models\User|null $user
 * @property-read \Illuminate\Database\Eloquent\Collection<\App\Models\AlertLog> $logs
 *
 * @method static \Illuminate\Database\Eloquent\Builder active()
 * @method static \Illuminate\Database\Eloquent\Builder byType(string $type)
 */
class Alert extends Model
{
    use SoftDeletes;

    public const TYPE_EVENT = 'event';
    public const TYPE_ZONE = 'zone';
    public const TYPE_EQUIPMENT = 'equipment';
    public const TYPE_CUSTOM = 'custom';
    public const TYPE_THRESHOLD = 'threshold';

    public const FREQUENCY_IMMEDIATE = 'immediate';
    public const FREQUENCY_DAILY = 'daily';
    public const FREQUENCY_WEEKLY = 'weekly';

    protected $fillable = [
        'uuid',
        'user_id',
        'type',
        'name',
        'description',
        'conditions',
        'filters',
        'notification_channels',
        'is_active',
        'frequency',
        'last_triggered_at',
        'trigger_count',
    ];

    protected $casts = [
        'conditions' => 'array',
        'filters' => 'array',
        'notification_channels' => 'array',
        'is_active' => 'boolean',
        'last_triggered_at' => 'datetime',
        'trigger_count' => 'integer',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(AlertLog::class);
    }

    public function trigger(array $data): void
    {
        $this->logs()->create([
            'trigger_type' => $data['type'] ?? 'manual',
            'trigger_data' => $data['data'] ?? null,
            'matched_entities' => $data['entities'] ?? null,
        ]);

        $this->update([
            'last_triggered_at' => now(),
            'trigger_count' => $this->trigger_count + 1,
        ]);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public static function getTypes(): array
    {
        return [
            self::TYPE_EVENT => 'Event',
            self::TYPE_ZONE => 'Control Zone',
            self::TYPE_EQUIPMENT => 'Equipment',
            self::TYPE_CUSTOM => 'Custom',
            self::TYPE_THRESHOLD => 'Threshold',
        ];
    }
}
