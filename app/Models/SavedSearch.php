<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class SavedSearch extends Model
{
    protected $fillable = [
        'uuid',
        'user_id',
        'name',
        'description',
        'entity_type',
        'filters',
        'sort',
        'is_default',
        'notify_on_new',
        'result_count',
        'last_executed_at',
    ];

    protected $casts = [
        'filters' => 'array',
        'sort' => 'array',
        'is_default' => 'boolean',
        'notify_on_new' => 'boolean',
        'result_count' => 'integer',
        'last_executed_at' => 'datetime',
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

    public function execute(): void
    {
        $this->update(['last_executed_at' => now()]);
    }

    public function updateResultCount(int $count): void
    {
        $this->update(['result_count' => $count]);
    }

    public function scopeByEntityType($query, string $type)
    {
        return $query->where('entity_type', $type);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeWithNotifications($query)
    {
        return $query->where('notify_on_new', true);
    }
}
