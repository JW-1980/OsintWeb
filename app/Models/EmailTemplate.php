<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Email Template Model
 *
 * Represents customizable email templates for the platform.
 *
 * @property int $id
 * @property string $uuid
 * @property string $slug
 * @property string $name
 * @property string|null $description
 * @property string $subject
 * @property string $html_content
 * @property string $text_content
 * @property string $category
 * @property array|null $variables
 * @property bool $is_active
 * @property bool $is_system
 * @property int|null $updated_by
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class EmailTemplate extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::creating(function (EmailTemplate $template) {
            if (empty($template->uuid)) {
                $template->uuid = (string) Str::uuid();
            }
        });
    }

    protected $fillable = [
        'uuid',
        'slug',
        'name',
        'description',
        'subject',
        'html_content',
        'text_content',
        'category',
        'variables',
        'is_active',
        'is_system',
        'updated_by',
    ];

    protected $casts = [
        'variables' => 'array',
        'is_active' => 'boolean',
        'is_system' => 'boolean',
    ];

    /**
     * Get the route key name for route model binding.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * Get the user who last updated this template
     */
    public function updatedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scope to filter active templates
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by category
     */
    public function scopeCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope to filter system templates
     */
    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }

    /**
     * Scope to filter custom templates
     */
    public function scopeCustom($query)
    {
        return $query->where('is_system', false);
    }

    /**
     * Render the HTML content with variables
     */
    public function renderHtml(array $data = []): string
    {
        return $this->replaceVariables($this->html_content, $data);
    }

    /**
     * Render the text content with variables
     */
    public function renderText(array $data = []): string
    {
        return $this->replaceVariables($this->text_content, $data);
    }

    /**
     * Render the subject with variables
     */
    public function renderSubject(array $data = []): string
    {
        return $this->replaceVariables($this->subject, $data);
    }

    /**
     * Replace template variables with actual values
     */
    protected function replaceVariables(string $content, array $data): string
    {
        foreach ($data as $key => $value) {
            if (is_string($value) || is_numeric($value)) {
                $content = str_replace('{{ ' . $key . ' }}', (string) $value, $content);
                $content = str_replace('{{' . $key . '}}', (string) $value, $content);
            }
        }

        return $content;
    }

    /**
     * Get available variable names for this template
     */
    public function getAvailableVariables(): array
    {
        return $this->variables ?? [];
    }

    /**
     * Available categories
     */
    public static function categories(): array
    {
        return [
            'authentication' => 'Authentication & Security',
            'notification' => 'Notifications',
            'marketing' => 'Marketing & Promotional',
            'transactional' => 'Transactional',
            'alert' => 'Alerts',
            'digest' => 'Digest & Summaries',
        ];
    }
}
