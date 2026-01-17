<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * TimelineEntry Model
 *
 * Entries in investigation timelines for case management.
 *
 * Database Schema:
 * @property int $id Primary key
 * @property string $uuid Unique identifier for external reference
 * @property int|null $case_id Foreign key to investigation_cases table
 * @property int|null $user_id Foreign key to users table (creator)
 * @property string $title Entry title
 * @property string|null $description Detailed description
 * @property \Carbon\Carbon $occurred_at When the event occurred
 * @property \Carbon\Carbon|null $end_at End time for duration events
 * @property string $type Entry type (event, observation, analysis, milestone)
 * @property string|null $category Entry category
 * @property array|null $sources Source references (JSON)
 * @property array|null $metadata Additional metadata (JSON)
 * @property float|null $latitude Geographic latitude
 * @property float|null $longitude Geographic longitude
 * @property string|null $location_name Human-readable location name
 * @property string|null $linked_entity_type Polymorphic type for linked entity
 * @property int|null $linked_entity_id Polymorphic ID for linked entity
 * @property string|null $confidence Confidence level
 * @property bool $is_verified Whether entry has been verified
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at Soft delete timestamp
 *
 * @property-read InvestigationCase|null $case
 * @property-read User|null $user
 * @property-read Model|null $linkedEntity
 */
class TimelineEntry extends Model
{
    use SoftDeletes;

    public const TYPE_EVENT = 'event';
    public const TYPE_OBSERVATION = 'observation';
    public const TYPE_ANALYSIS = 'analysis';
    public const TYPE_MILESTONE = 'milestone';

    protected $fillable = [
        'uuid',
        'case_id',
        'user_id',
        'title',
        'description',
        'occurred_at',
        'end_at',
        'type',
        'category',
        'sources',
        'metadata',
        'latitude',
        'longitude',
        'location_name',
        'linked_entity_type',
        'linked_entity_id',
        'confidence',
        'is_verified',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
        'end_at' => 'datetime',
        'sources' => 'array',
        'metadata' => 'array',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'is_verified' => 'boolean',
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

    public function linkedEntity(): MorphTo
    {
        return $this->morphTo('linked_entity');
    }

    public function verify(): void
    {
        $this->update(['is_verified' => true]);
    }

    public function hasLocation(): bool
    {
        return !is_null($this->latitude) && !is_null($this->longitude);
    }

    public function isDuration(): bool
    {
        return !is_null($this->end_at);
    }

    public function scopeByCase($query, int $caseId)
    {
        return $query->where('case_id', $caseId);
    }

    public function scopeChronological($query)
    {
        return $query->orderBy('occurred_at');
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public static function getTypes(): array
    {
        return [
            self::TYPE_EVENT => 'Event',
            self::TYPE_OBSERVATION => 'Observation',
            self::TYPE_ANALYSIS => 'Analysis',
            self::TYPE_MILESTONE => 'Milestone',
        ];
    }
}
