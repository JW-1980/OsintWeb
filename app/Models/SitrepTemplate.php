<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * SitrepTemplate Model
 *
 * Represents a template for generating Situation Reports (SITREPs).
 * Templates define the structure, sections, and styling for reports.
 *
 * @property int $id
 * @property string $uuid
 * @property string $name
 * @property string|null $description
 * @property string $template_type One of: daily_brief, weekly_summary, incident_report, equipment_loss, territorial_change, custom
 * @property array|null $sections Array of section configs with title, type, query_config, order
 * @property array|null $header_config Logo, classification, distribution configuration
 * @property array|null $footer_config Footer configuration
 * @property array|null $styling Fonts, colors, margins configuration
 * @property bool $is_default Whether this is the default template for its type
 * @property bool $is_public Whether this template is publicly available
 * @property int|null $created_by User ID of the creator
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 *
 * @property-read User|null $creator
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Sitrep> $sitreps
 */
class SitrepTemplate extends Model
{
    use HasFactory, SoftDeletes;

    // Template types
    public const TYPE_DAILY_BRIEF = 'daily_brief';
    public const TYPE_WEEKLY_SUMMARY = 'weekly_summary';
    public const TYPE_INCIDENT_REPORT = 'incident_report';
    public const TYPE_EQUIPMENT_LOSS = 'equipment_loss';
    public const TYPE_TERRITORIAL_CHANGE = 'territorial_change';
    public const TYPE_CUSTOM = 'custom';

    // Section types
    public const SECTION_EVENTS_SUMMARY = 'events_summary';
    public const SECTION_EQUIPMENT_LOSSES = 'equipment_losses';
    public const SECTION_TERRITORIAL_CHANGES = 'territorial_changes';
    public const SECTION_ACTOR_ACTIVITY = 'actor_activity';
    public const SECTION_TIMELINE = 'timeline';
    public const SECTION_STATISTICS = 'statistics';
    public const SECTION_MAP = 'map';
    public const SECTION_CUSTOM_TEXT = 'custom_text';
    public const SECTION_SOURCES = 'sources';

    protected $fillable = [
        'uuid',
        'name',
        'description',
        'template_type',
        'sections',
        'header_config',
        'footer_config',
        'styling',
        'is_default',
        'is_public',
        'created_by',
    ];

    protected $casts = [
        'sections' => 'array',
        'header_config' => 'array',
        'footer_config' => 'array',
        'styling' => 'array',
        'is_default' => 'boolean',
        'is_public' => 'boolean',
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

        // Ensure only one default template per type
        static::saving(function (self $model): void {
            if ($model->is_default) {
                self::where('template_type', $model->template_type)
                    ->where('id', '!=', $model->id ?? 0)
                    ->update(['is_default' => false]);
            }
        });
    }

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    /**
     * Get the user who created this template.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the sitreps using this template.
     */
    public function sitreps(): HasMany
    {
        return $this->hasMany(Sitrep::class, 'template_id');
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    /**
     * Scope to filter by template type.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeType($query, string $type)
    {
        return $query->where('template_type', $type);
    }

    /**
     * Scope to filter public templates.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope to filter default templates.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope to filter templates accessible by a user.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param User $user
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAccessibleBy($query, User $user)
    {
        return $query->where(function ($q) use ($user) {
            $q->where('is_public', true)
                ->orWhere('created_by', $user->id);
        });
    }

    // =========================================================================
    // HELPER METHODS
    // =========================================================================

    /**
     * Get all available template types.
     *
     * @return array<string, string>
     */
    public static function getTemplateTypes(): array
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
     * Get all available section types.
     *
     * @return array<string, string>
     */
    public static function getSectionTypes(): array
    {
        return [
            self::SECTION_EVENTS_SUMMARY => 'Events Summary',
            self::SECTION_EQUIPMENT_LOSSES => 'Equipment Losses',
            self::SECTION_TERRITORIAL_CHANGES => 'Territorial Changes',
            self::SECTION_ACTOR_ACTIVITY => 'Actor Activity',
            self::SECTION_TIMELINE => 'Timeline',
            self::SECTION_STATISTICS => 'Statistics',
            self::SECTION_MAP => 'Map',
            self::SECTION_CUSTOM_TEXT => 'Custom Text',
            self::SECTION_SOURCES => 'Sources',
        ];
    }

    /**
     * Get the human-readable template type name.
     *
     * @return string
     */
    public function getTemplateTypeName(): string
    {
        return self::getTemplateTypes()[$this->template_type] ?? 'Unknown';
    }

    /**
     * Get the ordered sections configuration.
     *
     * @return array
     */
    public function getOrderedSections(): array
    {
        $sections = $this->sections ?? [];

        usort($sections, fn ($a, $b) => ($a['order'] ?? 999) <=> ($b['order'] ?? 999));

        return $sections;
    }

    /**
     * Add a section to the template.
     *
     * @param array $section
     * @return void
     */
    public function addSection(array $section): void
    {
        $sections = $this->sections ?? [];
        $section['order'] = $section['order'] ?? count($sections);
        $sections[] = $section;
        $this->sections = $sections;
    }

    /**
     * Get the default header configuration.
     *
     * @return array
     */
    public static function getDefaultHeaderConfig(): array
    {
        return [
            'show_logo' => true,
            'logo_position' => 'left',
            'show_classification' => true,
            'show_distribution' => true,
            'show_date' => true,
            'show_report_number' => true,
        ];
    }

    /**
     * Get the default footer configuration.
     *
     * @return array
     */
    public static function getDefaultFooterConfig(): array
    {
        return [
            'show_page_numbers' => true,
            'show_classification' => true,
            'show_generated_date' => true,
            'custom_text' => null,
        ];
    }

    /**
     * Get the default styling configuration.
     *
     * @return array
     */
    public static function getDefaultStyling(): array
    {
        return [
            'font_family' => 'Arial, sans-serif',
            'font_size' => '12pt',
            'heading_font_size' => '16pt',
            'primary_color' => '#1a365d',
            'secondary_color' => '#2d3748',
            'accent_color' => '#3182ce',
            'margin_top' => '1in',
            'margin_bottom' => '1in',
            'margin_left' => '1in',
            'margin_right' => '1in',
            'line_height' => '1.5',
        ];
    }

    /**
     * Get the default template for a specific type.
     *
     * @param string $type
     * @return self|null
     */
    public static function getDefaultForType(string $type): ?self
    {
        return self::where('template_type', $type)
            ->where('is_default', true)
            ->first();
    }

    /**
     * Create a default daily brief template.
     *
     * @param User $user
     * @return self
     */
    public static function createDefaultDailyBrief(User $user): self
    {
        return self::create([
            'name' => 'Default Daily Brief',
            'description' => 'Standard daily situation report template',
            'template_type' => self::TYPE_DAILY_BRIEF,
            'sections' => [
                ['title' => 'Executive Summary', 'type' => self::SECTION_CUSTOM_TEXT, 'order' => 1, 'query_config' => []],
                ['title' => 'Key Events', 'type' => self::SECTION_EVENTS_SUMMARY, 'order' => 2, 'query_config' => ['limit' => 10, 'sort' => 'importance']],
                ['title' => 'Equipment Losses', 'type' => self::SECTION_EQUIPMENT_LOSSES, 'order' => 3, 'query_config' => []],
                ['title' => 'Territorial Changes', 'type' => self::SECTION_TERRITORIAL_CHANGES, 'order' => 4, 'query_config' => []],
                ['title' => 'Actor Activity', 'type' => self::SECTION_ACTOR_ACTIVITY, 'order' => 5, 'query_config' => ['limit' => 5]],
                ['title' => 'Sources', 'type' => self::SECTION_SOURCES, 'order' => 6, 'query_config' => []],
            ],
            'header_config' => self::getDefaultHeaderConfig(),
            'footer_config' => self::getDefaultFooterConfig(),
            'styling' => self::getDefaultStyling(),
            'is_default' => true,
            'is_public' => true,
            'created_by' => $user->id,
        ]);
    }
}
