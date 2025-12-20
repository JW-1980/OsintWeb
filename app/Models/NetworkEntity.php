<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class NetworkEntity extends Model
{
    use SoftDeletes;

    public const TYPE_PERSON = 'person';
    public const TYPE_ORGANIZATION = 'organization';
    public const TYPE_LOCATION = 'location';
    public const TYPE_EQUIPMENT = 'equipment';
    public const TYPE_COMMUNICATION = 'communication';

    protected $fillable = [
        'uuid',
        'case_id',
        'user_id',
        'name',
        'type',
        'attributes',
        'identifiers',
        'notes',
    ];

    protected $casts = [
        'attributes' => 'array',
        'identifiers' => 'array',
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

    public function case(): BelongsTo
    {
        return $this->belongsTo(InvestigationCase::class, 'case_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function outgoingRelationships(): HasMany
    {
        return $this->hasMany(NetworkRelationship::class, 'source_id');
    }

    public function incomingRelationships(): HasMany
    {
        return $this->hasMany(NetworkRelationship::class, 'target_id');
    }

    public function getAllRelationships()
    {
        return $this->outgoingRelationships->merge($this->incomingRelationships);
    }

    public function connectTo(NetworkEntity $target, string $type, array $metadata = []): NetworkRelationship
    {
        return NetworkRelationship::create([
            'source_id' => $this->id,
            'target_id' => $target->id,
            'relationship_type' => $type,
            'metadata' => $metadata,
        ]);
    }

    public function scopeByCase($query, int $caseId)
    {
        return $query->where('case_id', $caseId);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public static function getTypes(): array
    {
        return [
            self::TYPE_PERSON => 'Person',
            self::TYPE_ORGANIZATION => 'Organization',
            self::TYPE_LOCATION => 'Location',
            self::TYPE_EQUIPMENT => 'Equipment',
            self::TYPE_COMMUNICATION => 'Communication',
        ];
    }
}
