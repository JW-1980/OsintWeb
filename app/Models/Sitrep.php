<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * Sitrep Model
 *
 * Represents a Situation Report (SITREP) - a structured intelligence report
 * summarizing events, equipment losses, territorial changes, and actor activity
 * for a specified time period.
 *
 * @property int $id
 * @property string $uuid
 * @property string $title
 * @property string|null $subtitle
 * @property int|null $template_id Foreign key to sitrep_templates
 * @property string $report_type One of: daily_brief, weekly_summary, incident_report, equipment_loss, territorial_change, custom
 * @property string $classification_level One of: unclassified, confidential, secret, top_secret
 * @property \Carbon\Carbon|null $period_start Start of the reporting period
 * @property \Carbon\Carbon|null $period_end End of the reporting period
 * @property array|null $content_sections Rendered section content
 * @property string|null $summary Executive summary text
 * @property array|null $key_findings Array of key findings
 * @property array|null $recommendations Array of recommendations
 * @property array|null $appendices Appendix content
 * @property string $status One of: draft, review, approved, published, archived
 * @property int|null $approved_by User ID who approved the report
 * @property \Carbon\Carbon|null $approved_at Timestamp of approval
 * @property \Carbon\Carbon|null $published_at Timestamp of publication
 * @property array|null $generated_files Paths to exported files (PDF, DOCX, HTML)
 * @property array|null $event_ids Array of linked event IDs
 * @property array|null $equipment_ids Array of linked equipment IDs
 * @property array|null $actor_ids Array of linked actor IDs
 * @property array|null $metadata Additional metadata
 * @property int|null $created_by User ID of the creator
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 *
 * @property-read SitrepTemplate|null $template
 * @property-read User|null $creator
 * @property-read User|null $approver
 */
class Sitrep extends Model
{
    use HasFactory, SoftDeletes;

    // Report types (matches template types)
    public const TYPE_DAILY_BRIEF = 'daily_brief';
    public const TYPE_WEEKLY_SUMMARY = 'weekly_summary';
    public const TYPE_INCIDENT_REPORT = 'incident_report';
    public const TYPE_EQUIPMENT_LOSS = 'equipment_loss';
    public const TYPE_TERRITORIAL_CHANGE = 'territorial_change';
    public const TYPE_CUSTOM = 'custom';

    // Classification levels
    public const CLASSIFICATION_UNCLASSIFIED = 'unclassified';
    public const CLASSIFICATION_CONFIDENTIAL = 'confidential';
    public const CLASSIFICATION_SECRET = 'secret';
    public const CLASSIFICATION_TOP_SECRET = 'top_secret';

    // Status values
    public const STATUS_DRAFT = 'draft';
    public const STATUS_REVIEW = 'review';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_ARCHIVED = 'archived';

    protected $fillable = [
        'uuid',
        'title',
        'subtitle',
        'template_id',
        'report_type',
        'classification_level',
        'period_start',
        'period_end',
        'content_sections',
        'summary',
        'key_findings',
        'recommendations',
        'appendices',
        'status',
        'approved_by',
        'approved_at',
        'published_at',
        'generated_files',
        'event_ids',
        'equipment_ids',
        'actor_ids',
        'metadata',
        'created_by',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'content_sections' => 'array',
        'key_findings' => 'array',
        'recommendations' => 'array',
        'appendices' => 'array',
        'approved_at' => 'datetime',
        'published_at' => 'datetime',
        'generated_files' => 'array',
        'event_ids' => 'array',
        'equipment_ids' => 'array',
        'actor_ids' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $model): void {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    /**
     * Get the template used for this sitrep.
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(SitrepTemplate::class, 'template_id');
    }

    /**
     * Get the user who created this sitrep.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who approved this sitrep.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    /**
     * Scope to filter by status.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter draft sitreps.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    /**
     * Scope to filter published sitreps.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED)
            ->whereNotNull('published_at');
    }

    /**
     * Scope to filter by report type.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeType($query, string $type)
    {
        return $query->where('report_type', $type);
    }

    /**
     * Scope to filter by classification level.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $level
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeClassification($query, string $level)
    {
        return $query->where('classification_level', $level);
    }

    /**
     * Scope to filter by date range.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Carbon\Carbon|string $start
     * @param \Carbon\Carbon|string $end
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDateRange($query, $start, $end)
    {
        return $query->where(function ($q) use ($start, $end) {
            $q->whereBetween('period_start', [$start, $end])
                ->orWhereBetween('period_end', [$start, $end])
                ->orWhere(function ($q2) use ($start, $end) {
                    $q2->where('period_start', '<=', $start)
                        ->where('period_end', '>=', $end);
                });
        });
    }

    /**
     * Scope to filter sitreps accessible by a user.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param User $user
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAccessibleBy($query, User $user)
    {
        return $query->where(function ($q) use ($user) {
            // Creator always has access
            $q->where('created_by', $user->id);

            // Published reports are accessible
            $q->orWhere('status', self::STATUS_PUBLISHED);

            // Admins have full access
            if ($user->isAdmin()) {
                $q->orWhereRaw('1=1');
            }
        });
    }

    /**
     * Scope to search by title or summary.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $term
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('title', 'like', "%{$term}%")
                ->orWhere('subtitle', 'like', "%{$term}%")
                ->orWhere('summary', 'like', "%{$term}%");
        });
    }

    // =========================================================================
    // STATUS METHODS
    // =========================================================================

    /**
     * Check if the sitrep is a draft.
     *
     * @return bool
     */
    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    /**
     * Check if the sitrep is in review.
     *
     * @return bool
     */
    public function isInReview(): bool
    {
        return $this->status === self::STATUS_REVIEW;
    }

    /**
     * Check if the sitrep is approved.
     *
     * @return bool
     */
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Check if the sitrep is published.
     *
     * @return bool
     */
    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED && $this->published_at !== null;
    }

    /**
     * Check if the sitrep is archived.
     *
     * @return bool
     */
    public function isArchived(): bool
    {
        return $this->status === self::STATUS_ARCHIVED;
    }

    /**
     * Submit the sitrep for review.
     *
     * @return void
     */
    public function submitForReview(): void
    {
        $this->update(['status' => self::STATUS_REVIEW]);
    }

    /**
     * Approve the sitrep.
     *
     * @param User $approver
     * @return void
     */
    public function approve(User $approver): void
    {
        $this->update([
            'status' => self::STATUS_APPROVED,
            'approved_by' => $approver->id,
            'approved_at' => now(),
        ]);
    }

    /**
     * Reject the sitrep back to draft.
     *
     * @return void
     */
    public function reject(): void
    {
        $this->update([
            'status' => self::STATUS_DRAFT,
            'approved_by' => null,
            'approved_at' => null,
        ]);
    }

    /**
     * Publish the sitrep.
     *
     * @return void
     */
    public function publish(): void
    {
        $this->update([
            'status' => self::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);
    }

    /**
     * Archive the sitrep.
     *
     * @return void
     */
    public function archive(): void
    {
        $this->update(['status' => self::STATUS_ARCHIVED]);
    }

    // =========================================================================
    // HELPER METHODS
    // =========================================================================

    /**
     * Get all available report types.
     *
     * @return array<string, string>
     */
    public static function getReportTypes(): array
    {
        return [
            self::TYPE_DAILY_BRIEF => 'Daily Brief',
            self::TYPE_WEEKLY_SUMMARY => 'Weekly Summary',
            self::TYPE_INCIDENT_REPORT => 'Incident Report',
            self::TYPE_EQUIPMENT_LOSS => 'Equipment Loss Report',
            self::TYPE_TERRITORIAL_CHANGE => 'Territorial Change Report',
            self::TYPE_CUSTOM => 'Custom Report',
        ];
    }

    /**
     * Get all available classification levels.
     *
     * @return array<string, string>
     */
    public static function getClassificationLevels(): array
    {
        return [
            self::CLASSIFICATION_UNCLASSIFIED => 'Unclassified',
            self::CLASSIFICATION_CONFIDENTIAL => 'Confidential',
            self::CLASSIFICATION_SECRET => 'Secret',
            self::CLASSIFICATION_TOP_SECRET => 'Top Secret',
        ];
    }

    /**
     * Get all available statuses.
     *
     * @return array<string, string>
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_REVIEW => 'In Review',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_PUBLISHED => 'Published',
            self::STATUS_ARCHIVED => 'Archived',
        ];
    }

    /**
     * Get the human-readable report type name.
     *
     * @return string
     */
    public function getReportTypeName(): string
    {
        return self::getReportTypes()[$this->report_type] ?? 'Unknown';
    }

    /**
     * Get the human-readable classification level name.
     *
     * @return string
     */
    public function getClassificationName(): string
    {
        return self::getClassificationLevels()[$this->classification_level] ?? 'Unknown';
    }

    /**
     * Get the human-readable status name.
     *
     * @return string
     */
    public function getStatusName(): string
    {
        return self::getStatuses()[$this->status] ?? 'Unknown';
    }

    /**
     * Get the reporting period as a formatted string.
     *
     * @return string|null
     */
    public function getReportingPeriod(): ?string
    {
        if ($this->period_start === null || $this->period_end === null) {
            return null;
        }

        return $this->period_start->format('M j, Y') . ' - ' . $this->period_end->format('M j, Y');
    }

    /**
     * Get the number of days in the reporting period.
     *
     * @return int|null
     */
    public function getReportingPeriodDays(): ?int
    {
        if ($this->period_start === null || $this->period_end === null) {
            return null;
        }

        return $this->period_start->diffInDays($this->period_end) + 1;
    }

    /**
     * Get the linked events.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getLinkedEvents()
    {
        $ids = $this->event_ids ?? [];

        if (empty($ids)) {
            return collect();
        }

        return Event::whereIn('id', $ids)->get();
    }

    /**
     * Get the linked equipment.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getLinkedEquipment()
    {
        $ids = $this->equipment_ids ?? [];

        if (empty($ids)) {
            return collect();
        }

        return MilitaryEquipment::whereIn('id', $ids)->get();
    }

    /**
     * Get the linked actors.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getLinkedActors()
    {
        $ids = $this->actor_ids ?? [];

        if (empty($ids)) {
            return collect();
        }

        return Actor::whereIn('id', $ids)->get();
    }

    /**
     * Add a generated file path.
     *
     * @param string $format
     * @param string $path
     * @return void
     */
    public function addGeneratedFile(string $format, string $path): void
    {
        $files = $this->generated_files ?? [];
        $files[$format] = [
            'path' => $path,
            'generated_at' => now()->toIso8601String(),
        ];
        $this->generated_files = $files;
        $this->save();
    }

    /**
     * Get a generated file path by format.
     *
     * @param string $format
     * @return string|null
     */
    public function getGeneratedFile(string $format): ?string
    {
        return $this->generated_files[$format]['path'] ?? null;
    }

    /**
     * Check if a generated file exists for a format.
     *
     * @param string $format
     * @return bool
     */
    public function hasGeneratedFile(string $format): bool
    {
        return isset($this->generated_files[$format]['path']);
    }

    /**
     * Get a section by its key/type.
     *
     * @param string $sectionType
     * @return array|null
     */
    public function getSection(string $sectionType): ?array
    {
        $sections = $this->content_sections ?? [];

        foreach ($sections as $section) {
            if (($section['type'] ?? '') === $sectionType) {
                return $section;
            }
        }

        return null;
    }

    /**
     * Update or add a content section.
     *
     * @param string $sectionType
     * @param array $content
     * @return void
     */
    public function updateSection(string $sectionType, array $content): void
    {
        $sections = $this->content_sections ?? [];
        $found = false;

        foreach ($sections as &$section) {
            if (($section['type'] ?? '') === $sectionType) {
                $section['content'] = $content;
                $section['updated_at'] = now()->toIso8601String();
                $found = true;
                break;
            }
        }

        if (!$found) {
            $sections[] = [
                'type' => $sectionType,
                'content' => $content,
                'created_at' => now()->toIso8601String(),
            ];
        }

        $this->content_sections = $sections;
    }

    /**
     * Generate a report number.
     *
     * @return string
     */
    public function generateReportNumber(): string
    {
        $prefix = match ($this->report_type) {
            self::TYPE_DAILY_BRIEF => 'DB',
            self::TYPE_WEEKLY_SUMMARY => 'WS',
            self::TYPE_INCIDENT_REPORT => 'IR',
            self::TYPE_EQUIPMENT_LOSS => 'EL',
            self::TYPE_TERRITORIAL_CHANGE => 'TC',
            default => 'SR',
        };

        $date = $this->period_end ?? now();
        $sequence = self::whereDate('created_at', $date->format('Y-m-d'))
            ->where('report_type', $this->report_type)
            ->count();

        return sprintf('%s-%s-%03d', $prefix, $date->format('Ymd'), $sequence + 1);
    }
}
