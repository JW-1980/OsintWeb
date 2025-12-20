<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Setting Model
 *
 * Key-value storage for application settings.
 *
 * @property int $id
 * @property string $key
 * @property mixed $value
 * @property string|null $type
 * @property string|null $group
 * @property string|null $description
 * @property bool $is_public
 * @property bool $is_encrypted
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'description',
        'is_public',
        'is_encrypted',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'is_encrypted' => 'boolean',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'is_encrypted',
    ];

    /**
     * Get a setting value by key
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember("setting.{$key}", 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();

            if ($setting === null) {
                return $default;
            }

            return static::castValue($setting->value, $setting->type);
        });
    }

    /**
     * Set a setting value
     */
    public static function set(string $key, mixed $value, ?string $type = null, ?string $group = null): void
    {
        $type = $type ?? static::inferType($value);

        static::updateOrCreate(
            ['key' => $key],
            [
                'value' => static::serializeValue($value, $type),
                'type' => $type,
                'group' => $group,
            ]
        );

        Cache::forget("setting.{$key}");
    }

    /**
     * Check if a setting exists
     */
    public static function has(string $key): bool
    {
        return static::where('key', $key)->exists();
    }

    /**
     * Delete a setting
     */
    public static function remove(string $key): bool
    {
        Cache::forget("setting.{$key}");
        return static::where('key', $key)->delete() > 0;
    }

    /**
     * Get all settings in a group
     */
    public static function getGroup(string $group): array
    {
        return static::where('group', $group)
            ->get()
            ->mapWithKeys(function ($setting) {
                return [
                    $setting->key => static::castValue($setting->value, $setting->type)
                ];
            })
            ->toArray();
    }

    /**
     * Get all public settings
     */
    public static function getPublic(): array
    {
        return static::where('is_public', true)
            ->get()
            ->mapWithKeys(function ($setting) {
                return [
                    $setting->key => static::castValue($setting->value, $setting->type)
                ];
            })
            ->toArray();
    }

    /**
     * Scope to filter by group
     */
    public function scopeGroup($query, string $group)
    {
        return $query->where('group', $group);
    }

    /**
     * Scope to filter public settings
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Cast value based on type
     */
    protected static function castValue(mixed $value, ?string $type): mixed
    {
        return match ($type) {
            'boolean', 'bool' => (bool) $value,
            'integer', 'int' => (int) $value,
            'float', 'double' => (float) $value,
            'array', 'json' => json_decode($value, true),
            'object' => json_decode($value),
            default => $value,
        };
    }

    /**
     * Serialize value for storage
     */
    protected static function serializeValue(mixed $value, string $type): string
    {
        return match ($type) {
            'array', 'json', 'object' => json_encode($value),
            'boolean', 'bool' => $value ? '1' : '0',
            default => (string) $value,
        };
    }

    /**
     * Infer type from value
     */
    protected static function inferType(mixed $value): string
    {
        return match (true) {
            is_bool($value) => 'boolean',
            is_int($value) => 'integer',
            is_float($value) => 'float',
            is_array($value) => 'array',
            is_object($value) => 'object',
            default => 'string',
        };
    }

    /**
     * Boot the model
     */
    protected static function boot(): void
    {
        parent::boot();

        static::saved(function ($setting) {
            Cache::forget("setting.{$setting->key}");
        });

        static::deleted(function ($setting) {
            Cache::forget("setting.{$setting->key}");
        });
    }
}
