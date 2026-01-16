<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * Disinformation Pattern Model
 *
 * Pattern library for detecting known disinformation tactics.
 * Contains rules, examples, and metadata for various manipulation techniques.
 *
 * Database Schema (disinformation_patterns):
 * @property int $id Primary key
 * @property string $name Pattern name
 * @property string $slug URL-friendly slug
 * @property string $description Detailed description of the pattern
 * @property string $pattern_type Type (linguistic, behavioral, temporal, network, media)
 * @property array $detection_rules JSON rules for pattern matching
 * @property array|null $keywords Keywords and phrases to detect
 * @property array|null $regex_patterns Regex patterns for matching
 * @property int $severity Severity scale 1-5
 * @property int $base_threat_weight Weight added to threat score when matched
 * @property array|null $examples Real-world examples
 * @property array|null $indicators Specific indicators to look for
 * @property array|null $counter_indicators Signs of false positive
 * @property array|null $related_pattern_ids IDs of related patterns
 * @property bool $is_active Whether pattern is active
 * @property int $match_count Number of times matched
 * @property \Carbon\Carbon|null $last_matched_at When last matched
 * @property int $version Pattern version
 * @property int|null $created_by User who created this
 * @property int|null $updated_by User who last updated this
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 *
 * Relationships:
 * @property-read \Illuminate\Database\Eloquent\Collection|DisinformationAnalysis[] $analyses Analyses that matched this pattern
 * @property-read User|null $createdByUser User who created this
 * @property-read User|null $updatedByUser User who last updated this
 */
class DisinformationPattern extends Model
{
    use HasFactory, SoftDeletes;

    // Pattern type constants
    public const TYPE_LINGUISTIC = 'linguistic';
    public const TYPE_BEHAVIORAL = 'behavioral';
    public const TYPE_TEMPORAL = 'temporal';
    public const TYPE_NETWORK = 'network';
    public const TYPE_MEDIA = 'media';

    // Severity constants
    public const SEVERITY_MINIMAL = 1;
    public const SEVERITY_LOW = 2;
    public const SEVERITY_MODERATE = 3;
    public const SEVERITY_HIGH = 4;
    public const SEVERITY_CRITICAL = 5;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'pattern_type',
        'detection_rules',
        'keywords',
        'regex_patterns',
        'severity',
        'base_threat_weight',
        'examples',
        'indicators',
        'counter_indicators',
        'related_pattern_ids',
        'is_active',
        'match_count',
        'last_matched_at',
        'version',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'detection_rules' => 'array',
        'keywords' => 'array',
        'regex_patterns' => 'array',
        'severity' => 'integer',
        'base_threat_weight' => 'integer',
        'examples' => 'array',
        'indicators' => 'array',
        'counter_indicators' => 'array',
        'related_pattern_ids' => 'array',
        'is_active' => 'boolean',
        'match_count' => 'integer',
        'last_matched_at' => 'datetime',
        'version' => 'integer',
    ];

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($pattern) {
            if (empty($pattern->slug)) {
                $pattern->slug = Str::slug($pattern->name);
            }
        });
    }

    /**
     * Get all pattern types.
     *
     * @return array<string>
     */
    public static function getPatternTypes(): array
    {
        return [
            self::TYPE_LINGUISTIC,
            self::TYPE_BEHAVIORAL,
            self::TYPE_TEMPORAL,
            self::TYPE_NETWORK,
            self::TYPE_MEDIA,
        ];
    }

    /**
     * Get pattern type labels.
     *
     * @return array<string, string>
     */
    public static function getPatternTypeLabels(): array
    {
        return [
            self::TYPE_LINGUISTIC => 'Linguistic Pattern',
            self::TYPE_BEHAVIORAL => 'Behavioral Pattern',
            self::TYPE_TEMPORAL => 'Temporal Pattern',
            self::TYPE_NETWORK => 'Network Pattern',
            self::TYPE_MEDIA => 'Media Manipulation',
        ];
    }

    /**
     * Get severity levels.
     *
     * @return array<int, string>
     */
    public static function getSeverityLevels(): array
    {
        return [
            self::SEVERITY_MINIMAL => 'Minimal',
            self::SEVERITY_LOW => 'Low',
            self::SEVERITY_MODERATE => 'Moderate',
            self::SEVERITY_HIGH => 'High',
            self::SEVERITY_CRITICAL => 'Critical',
        ];
    }

    /**
     * Get analyses that matched this pattern.
     */
    public function analyses(): BelongsToMany
    {
        return $this->belongsToMany(DisinformationAnalysis::class, 'analysis_pattern')
            ->withPivot(['match_details', 'match_confidence'])
            ->withTimestamps();
    }

    /**
     * Get the user who created this pattern.
     */
    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this pattern.
     */
    public function updatedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get related patterns.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRelatedPatterns()
    {
        if (empty($this->related_pattern_ids)) {
            return collect();
        }

        return self::whereIn('id', $this->related_pattern_ids)->get();
    }

    /**
     * Get pattern type label.
     *
     * @return string
     */
    public function getPatternTypeLabel(): string
    {
        return self::getPatternTypeLabels()[$this->pattern_type] ?? ucfirst($this->pattern_type);
    }

    /**
     * Get severity label.
     *
     * @return string
     */
    public function getSeverityLabel(): string
    {
        return self::getSeverityLevels()[$this->severity] ?? 'Unknown';
    }

    /**
     * Get severity color.
     *
     * @return string
     */
    public function getSeverityColorAttribute(): string
    {
        return match ($this->severity) {
            self::SEVERITY_MINIMAL => '#94a3b8',
            self::SEVERITY_LOW => '#22c55e',
            self::SEVERITY_MODERATE => '#f59e0b',
            self::SEVERITY_HIGH => '#ef4444',
            self::SEVERITY_CRITICAL => '#b91c1c',
            default => '#64748b',
        };
    }

    /**
     * Check if pattern matches content.
     *
     * @param string $content Content to check
     * @return array|null Match result or null
     */
    public function matchContent(string $content): ?array
    {
        $matches = [];
        $confidence = 0;

        // Check keywords
        if (!empty($this->keywords)) {
            $keywordMatches = $this->checkKeywords($content);
            if (!empty($keywordMatches)) {
                $matches['keywords'] = $keywordMatches;
                $confidence += min(30, count($keywordMatches) * 10);
            }
        }

        // Check regex patterns
        if (!empty($this->regex_patterns)) {
            $regexMatches = $this->checkRegexPatterns($content);
            if (!empty($regexMatches)) {
                $matches['regex'] = $regexMatches;
                $confidence += min(40, count($regexMatches) * 15);
            }
        }

        // Check custom detection rules
        if (!empty($this->detection_rules)) {
            $ruleMatches = $this->checkDetectionRules($content);
            if (!empty($ruleMatches)) {
                $matches['rules'] = $ruleMatches;
                $confidence += min(30, count($ruleMatches) * 10);
            }
        }

        if (empty($matches)) {
            return null;
        }

        return [
            'pattern_id' => $this->id,
            'pattern_name' => $this->name,
            'pattern_type' => $this->pattern_type,
            'matches' => $matches,
            'confidence' => min(100, $confidence),
            'threat_weight' => $this->base_threat_weight,
        ];
    }

    /**
     * Check keywords in content.
     *
     * @param string $content
     * @return array
     */
    protected function checkKeywords(string $content): array
    {
        $matches = [];
        $contentLower = strtolower($content);

        foreach ($this->keywords as $keyword) {
            if (str_contains($contentLower, strtolower($keyword))) {
                $matches[] = $keyword;
            }
        }

        return $matches;
    }

    /**
     * Check regex patterns in content.
     *
     * @param string $content
     * @return array
     */
    protected function checkRegexPatterns(string $content): array
    {
        $matches = [];

        foreach ($this->regex_patterns as $pattern) {
            if (preg_match($pattern, $content, $patternMatches)) {
                $matches[] = [
                    'pattern' => $pattern,
                    'matched' => $patternMatches[0] ?? null,
                ];
            }
        }

        return $matches;
    }

    /**
     * Check custom detection rules.
     *
     * @param string $content
     * @return array
     */
    protected function checkDetectionRules(string $content): array
    {
        $matches = [];

        foreach ($this->detection_rules as $rule) {
            $ruleType = $rule['type'] ?? 'unknown';

            switch ($ruleType) {
                case 'word_count_above':
                    $wordCount = str_word_count($content);
                    if ($wordCount > ($rule['threshold'] ?? 0)) {
                        $matches[] = ['rule' => $ruleType, 'value' => $wordCount];
                    }
                    break;

                case 'uppercase_ratio':
                    $uppercaseRatio = $this->calculateUppercaseRatio($content);
                    if ($uppercaseRatio > ($rule['threshold'] ?? 0)) {
                        $matches[] = ['rule' => $ruleType, 'value' => $uppercaseRatio];
                    }
                    break;

                case 'exclamation_count':
                    $exclamationCount = substr_count($content, '!');
                    if ($exclamationCount > ($rule['threshold'] ?? 0)) {
                        $matches[] = ['rule' => $ruleType, 'value' => $exclamationCount];
                    }
                    break;

                case 'link_count':
                    preg_match_all('/https?:\/\/[^\s]+/i', $content, $links);
                    $linkCount = count($links[0] ?? []);
                    if ($linkCount > ($rule['threshold'] ?? 0)) {
                        $matches[] = ['rule' => $ruleType, 'value' => $linkCount];
                    }
                    break;

                case 'hashtag_count':
                    preg_match_all('/#\w+/', $content, $hashtags);
                    $hashtagCount = count($hashtags[0] ?? []);
                    if ($hashtagCount > ($rule['threshold'] ?? 0)) {
                        $matches[] = ['rule' => $ruleType, 'value' => $hashtagCount];
                    }
                    break;
            }
        }

        return $matches;
    }

    /**
     * Calculate uppercase ratio in text.
     *
     * @param string $text
     * @return float
     */
    protected function calculateUppercaseRatio(string $text): float
    {
        $letters = preg_replace('/[^a-zA-Z]/', '', $text);
        if (empty($letters)) {
            return 0.0;
        }

        $uppercase = preg_replace('/[^A-Z]/', '', $letters);
        return round(strlen($uppercase) / strlen($letters), 2);
    }

    /**
     * Record a match.
     */
    public function recordMatch(): void
    {
        $this->increment('match_count');
        $this->update(['last_matched_at' => now()]);
    }

    /**
     * Increment version.
     *
     * @param int|null $userId
     */
    public function incrementVersion(?int $userId = null): void
    {
        $this->increment('version');
        if ($userId) {
            $this->update(['updated_by' => $userId]);
        }
    }

    /**
     * Scope for active patterns.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for specific pattern type.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('pattern_type', $type);
    }

    /**
     * Scope for patterns by severity.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $severity
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBySeverity($query, int $severity)
    {
        return $query->where('severity', $severity);
    }

    /**
     * Scope for high severity patterns.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeHighSeverity($query)
    {
        return $query->where('severity', '>=', self::SEVERITY_HIGH);
    }

    /**
     * Scope for recently matched patterns.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $days
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRecentlyMatched($query, int $days = 7)
    {
        return $query->where('last_matched_at', '>=', now()->subDays($days));
    }

    /**
     * Scope for most used patterns.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeMostUsed($query, int $limit = 10)
    {
        return $query->orderBy('match_count', 'desc')->limit($limit);
    }

    /**
     * Search patterns by name or description.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $term
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%");
        });
    }
}
