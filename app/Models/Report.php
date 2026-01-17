<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * Report Model
 *
 * Generated intelligence reports (SITREP, equipment losses, timelines, etc.).
 *
 * Database Schema:
 * @property int $id Primary key
 * @property string $uuid Unique identifier for external reference
 * @property int $user_id Foreign key to users table (creator)
 * @property string $title Report title
 * @property string|null $description Report description
 * @property string $type Report type (event_summary, equipment_losses, timeline, custom)
 * @property string $status Generation status (draft, generating, completed, failed)
 * @property array|null $configuration Report configuration options (JSON)
 * @property array|null $filters Data filtering criteria (JSON)
 * @property array|null $sections Report sections configuration (JSON)
 * @property string|null $output_format Output format (pdf, docx, html)
 * @property string|null $file_path Path to generated file
 * @property \Carbon\Carbon|null $generated_at When report was generated
 * @property bool $is_public Whether report is publicly accessible
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at Soft delete timestamp
 *
 * @property-read User $user
 */
class Report extends Model
{
    use SoftDeletes;

    public const TYPE_EVENT_SUMMARY = 'event_summary';
    public const TYPE_EQUIPMENT_LOSSES = 'equipment_losses';
    public const TYPE_TIMELINE = 'timeline';
    public const TYPE_CUSTOM = 'custom';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_GENERATING = 'generating';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'uuid',
        'user_id',
        'title',
        'description',
        'type',
        'status',
        'configuration',
        'filters',
        'sections',
        'output_format',
        'file_path',
        'generated_at',
        'is_public',
    ];

    protected $casts = [
        'configuration' => 'array',
        'filters' => 'array',
        'sections' => 'array',
        'generated_at' => 'datetime',
        'is_public' => 'boolean',
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

    public function markAsGenerating(): void
    {
        $this->update(['status' => self::STATUS_GENERATING]);
    }

    public function markAsCompleted(string $filePath): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'file_path' => $filePath,
            'generated_at' => now(),
        ]);
    }

    public function markAsFailed(): void
    {
        $this->update(['status' => self::STATUS_FAILED]);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public static function getTypes(): array
    {
        return [
            self::TYPE_EVENT_SUMMARY => 'Event Summary',
            self::TYPE_EQUIPMENT_LOSSES => 'Equipment Losses',
            self::TYPE_TIMELINE => 'Timeline',
            self::TYPE_CUSTOM => 'Custom',
        ];
    }
}
