<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ReverseImageResult Model
 *
 * Represents a single result from a reverse image search engine.
 * Multiple results belong to a single ReverseImageSearch.
 *
 * Database Schema:
 * @property int $id Primary key
 * @property int $search_id Foreign key to reverse_image_searches
 * @property string $engine Search engine (google, bing, tineye, yandex, baidu)
 * @property string $result_url URL where image was found
 * @property string|null $result_title Title of the page/post
 * @property string|null $result_snippet Text snippet from the page
 * @property string|null $thumbnail_url URL to thumbnail preview
 * @property string $match_type Type of match (exact, similar, partial)
 * @property int|null $similarity_score Similarity percentage 0-100
 * @property \Carbon\Carbon|null $first_seen_date Earliest indexing date
 * @property string $source_domain Domain where image was found
 * @property string|null $page_title Full page title
 * @property bool $is_potentially_manipulated Flag for suspected manipulation
 * @property array|null $manipulation_indicators Detected manipulation flags
 * @property string|null $image_dimensions Dimensions e.g., "1920x1080"
 * @property array|null $raw_response Full engine response for debugging
 * @property \Carbon\Carbon $created_at Record creation timestamp
 * @property \Carbon\Carbon $updated_at Record update timestamp
 *
 * Relationships:
 * @property-read ReverseImageSearch $search Parent search record
 *
 * Indexes:
 * - search_id (foreign key)
 * - engine (filtering by source)
 * - source_domain (domain analysis)
 * - match_type (filtering exact matches)
 * - first_seen_date (chronological analysis)
 * - similarity_score (quality filtering)
 * - is_potentially_manipulated (manipulation detection)
 * - (search_id, engine) composite
 * - (search_id, match_type) composite
 * - (search_id, similarity_score) composite
 * - (search_id, engine, result_url) unique constraint
 */
class ReverseImageResult extends Model
{
    use HasFactory;

    /**
     * Search engine constants
     */
    public const ENGINE_GOOGLE = 'google';
    public const ENGINE_BING = 'bing';
    public const ENGINE_TINEYE = 'tineye';
    public const ENGINE_YANDEX = 'yandex';
    public const ENGINE_BAIDU = 'baidu';

    /**
     * Match type constants
     */
    public const MATCH_EXACT = 'exact';
    public const MATCH_SIMILAR = 'similar';
    public const MATCH_PARTIAL = 'partial';

    /**
     * Manipulation indicator constants
     */
    public const MANIPULATION_METADATA_STRIPPED = 'metadata_stripped';
    public const MANIPULATION_RESIZE_DETECTED = 'resize_detected';
    public const MANIPULATION_CROP_DETECTED = 'crop_detected';
    public const MANIPULATION_COMPRESSION_ARTIFACTS = 'compression_artifacts';
    public const MANIPULATION_COLOR_ADJUSTMENT = 'color_adjustment';
    public const MANIPULATION_FILTER_APPLIED = 'filter_applied';
    public const MANIPULATION_WATERMARK_REMOVED = 'watermark_removed';
    public const MANIPULATION_CLONE_DETECTED = 'clone_detected';
    public const MANIPULATION_SPLICE_DETECTED = 'splice_detected';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'search_id',
        'engine',
        'result_url',
        'result_title',
        'result_snippet',
        'thumbnail_url',
        'match_type',
        'similarity_score',
        'first_seen_date',
        'source_domain',
        'page_title',
        'is_potentially_manipulated',
        'manipulation_indicators',
        'image_dimensions',
        'raw_response',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'first_seen_date' => 'date',
        'is_potentially_manipulated' => 'boolean',
        'manipulation_indicators' => 'array',
        'raw_response' => 'array',
        'similarity_score' => 'integer',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'reverse_image_results';

    /**
     * Get the parent search record
     */
    public function search(): BelongsTo
    {
        return $this->belongsTo(ReverseImageSearch::class, 'search_id');
    }

    /**
     * Scope to filter by search ID
     */
    public function scopeForSearch($query, int $searchId)
    {
        return $query->where('search_id', $searchId);
    }

    /**
     * Scope to filter by engine
     */
    public function scopeEngine($query, string $engine)
    {
        return $query->where('engine', $engine);
    }

    /**
     * Scope to filter by match type
     */
    public function scopeMatchType($query, string $type)
    {
        return $query->where('match_type', $type);
    }

    /**
     * Scope to filter exact matches only
     */
    public function scopeExactMatches($query)
    {
        return $query->where('match_type', self::MATCH_EXACT);
    }

    /**
     * Scope to filter similar matches
     */
    public function scopeSimilarMatches($query)
    {
        return $query->where('match_type', self::MATCH_SIMILAR);
    }

    /**
     * Scope to filter by domain
     */
    public function scopeDomain($query, string $domain)
    {
        return $query->where('source_domain', $domain);
    }

    /**
     * Scope to filter potentially manipulated results
     */
    public function scopeManipulated($query)
    {
        return $query->where('is_potentially_manipulated', true);
    }

    /**
     * Scope to filter by minimum similarity score
     */
    public function scopeMinSimilarity($query, int $minScore)
    {
        return $query->where('similarity_score', '>=', $minScore);
    }

    /**
     * Scope to filter by date range
     */
    public function scopeDateRange($query, $from, $to)
    {
        return $query->whereBetween('first_seen_date', [$from, $to]);
    }

    /**
     * Scope to order by similarity score descending
     */
    public function scopeOrderBySimilarity($query)
    {
        return $query->orderByDesc('similarity_score');
    }

    /**
     * Scope to order by first seen date ascending (oldest first)
     */
    public function scopeOrderByOldest($query)
    {
        return $query->orderBy('first_seen_date', 'asc');
    }

    /**
     * Check if this is an exact match
     */
    public function isExactMatch(): bool
    {
        return $this->match_type === self::MATCH_EXACT;
    }

    /**
     * Check if this is a similar match
     */
    public function isSimilarMatch(): bool
    {
        return $this->match_type === self::MATCH_SIMILAR;
    }

    /**
     * Check if this is a partial match
     */
    public function isPartialMatch(): bool
    {
        return $this->match_type === self::MATCH_PARTIAL;
    }

    /**
     * Check if this result has high similarity
     */
    public function isHighSimilarity(int $threshold = 80): bool
    {
        return $this->similarity_score !== null && $this->similarity_score >= $threshold;
    }

    /**
     * Get the engine display name
     */
    public function getEngineDisplayName(): string
    {
        return ReverseImageSearch::getEngineDisplayNames()[$this->engine] ?? ucfirst($this->engine);
    }

    /**
     * Get the match type display name
     */
    public function getMatchTypeDisplayName(): string
    {
        return match ($this->match_type) {
            self::MATCH_EXACT => 'Exact Match',
            self::MATCH_SIMILAR => 'Similar',
            self::MATCH_PARTIAL => 'Partial Match',
            default => ucfirst($this->match_type),
        };
    }

    /**
     * Get manipulation indicator display names
     */
    public function getManipulationIndicatorNames(): array
    {
        $indicators = $this->manipulation_indicators ?? [];
        $names = [
            self::MANIPULATION_METADATA_STRIPPED => 'EXIF Metadata Stripped',
            self::MANIPULATION_RESIZE_DETECTED => 'Image Resized',
            self::MANIPULATION_CROP_DETECTED => 'Image Cropped',
            self::MANIPULATION_COMPRESSION_ARTIFACTS => 'Compression Artifacts',
            self::MANIPULATION_COLOR_ADJUSTMENT => 'Color Adjusted',
            self::MANIPULATION_FILTER_APPLIED => 'Filter Applied',
            self::MANIPULATION_WATERMARK_REMOVED => 'Watermark Removed',
            self::MANIPULATION_CLONE_DETECTED => 'Clone/Copy-Move Detected',
            self::MANIPULATION_SPLICE_DETECTED => 'Splice Detected',
        ];

        return array_map(
            fn($indicator) => $names[$indicator] ?? ucfirst(str_replace('_', ' ', $indicator)),
            $indicators
        );
    }

    /**
     * Parse image dimensions into width and height
     */
    public function getParsedDimensions(): ?array
    {
        if (!$this->image_dimensions) {
            return null;
        }

        $parts = explode('x', strtolower($this->image_dimensions));
        if (count($parts) !== 2) {
            return null;
        }

        return [
            'width' => (int) $parts[0],
            'height' => (int) $parts[1],
        ];
    }

    /**
     * Get all available engines
     */
    public static function getEngines(): array
    {
        return [
            self::ENGINE_GOOGLE,
            self::ENGINE_BING,
            self::ENGINE_TINEYE,
            self::ENGINE_YANDEX,
            self::ENGINE_BAIDU,
        ];
    }

    /**
     * Get all available match types
     */
    public static function getMatchTypes(): array
    {
        return [
            self::MATCH_EXACT,
            self::MATCH_SIMILAR,
            self::MATCH_PARTIAL,
        ];
    }

    /**
     * Get all manipulation indicators
     */
    public static function getManipulationIndicators(): array
    {
        return [
            self::MANIPULATION_METADATA_STRIPPED,
            self::MANIPULATION_RESIZE_DETECTED,
            self::MANIPULATION_CROP_DETECTED,
            self::MANIPULATION_COMPRESSION_ARTIFACTS,
            self::MANIPULATION_COLOR_ADJUSTMENT,
            self::MANIPULATION_FILTER_APPLIED,
            self::MANIPULATION_WATERMARK_REMOVED,
            self::MANIPULATION_CLONE_DETECTED,
            self::MANIPULATION_SPLICE_DETECTED,
        ];
    }

    /**
     * Extract domain from URL
     */
    public static function extractDomain(string $url): string
    {
        $parsed = parse_url($url);
        $host = $parsed['host'] ?? '';

        // Remove 'www.' prefix if present
        if (str_starts_with($host, 'www.')) {
            $host = substr($host, 4);
        }

        return strtolower($host);
    }

    /**
     * Create a result from Google response
     */
    public static function createFromGoogle(int $searchId, array $data): self
    {
        return self::create([
            'search_id' => $searchId,
            'engine' => self::ENGINE_GOOGLE,
            'result_url' => $data['link'] ?? $data['url'] ?? '',
            'result_title' => $data['title'] ?? null,
            'result_snippet' => $data['snippet'] ?? null,
            'thumbnail_url' => $data['image']['thumbnailLink'] ?? $data['thumbnail'] ?? null,
            'match_type' => self::MATCH_SIMILAR,
            'similarity_score' => null,
            'first_seen_date' => isset($data['date']) ? \Carbon\Carbon::parse($data['date']) : null,
            'source_domain' => self::extractDomain($data['link'] ?? $data['url'] ?? ''),
            'page_title' => $data['title'] ?? null,
            'image_dimensions' => isset($data['image']['width'], $data['image']['height'])
                ? "{$data['image']['width']}x{$data['image']['height']}"
                : null,
            'raw_response' => $data,
        ]);
    }

    /**
     * Create a result from TinEye response
     */
    public static function createFromTineye(int $searchId, array $data): self
    {
        $score = isset($data['score']) ? (int) round($data['score'] * 100) : null;

        return self::create([
            'search_id' => $searchId,
            'engine' => self::ENGINE_TINEYE,
            'result_url' => $data['backlinks'][0]['url'] ?? $data['url'] ?? '',
            'result_title' => $data['backlinks'][0]['crawl_date'] ?? null,
            'result_snippet' => null,
            'thumbnail_url' => $data['image_url'] ?? null,
            'match_type' => $score >= 90 ? self::MATCH_EXACT : self::MATCH_SIMILAR,
            'similarity_score' => $score,
            'first_seen_date' => isset($data['backlinks'][0]['crawl_date'])
                ? \Carbon\Carbon::parse($data['backlinks'][0]['crawl_date'])
                : null,
            'source_domain' => self::extractDomain($data['backlinks'][0]['url'] ?? $data['url'] ?? ''),
            'page_title' => $data['backlinks'][0]['url'] ?? null,
            'image_dimensions' => isset($data['width'], $data['height'])
                ? "{$data['width']}x{$data['height']}"
                : null,
            'raw_response' => $data,
        ]);
    }

    /**
     * Create a result from Yandex response
     */
    public static function createFromYandex(int $searchId, array $data): self
    {
        return self::create([
            'search_id' => $searchId,
            'engine' => self::ENGINE_YANDEX,
            'result_url' => $data['url'] ?? $data['page_url'] ?? '',
            'result_title' => $data['title'] ?? null,
            'result_snippet' => $data['description'] ?? null,
            'thumbnail_url' => $data['thumb']['url'] ?? $data['thumbnail'] ?? null,
            'match_type' => self::MATCH_SIMILAR,
            'similarity_score' => null,
            'first_seen_date' => null,
            'source_domain' => self::extractDomain($data['url'] ?? $data['page_url'] ?? ''),
            'page_title' => $data['title'] ?? null,
            'image_dimensions' => isset($data['original_image']['width'], $data['original_image']['height'])
                ? "{$data['original_image']['width']}x{$data['original_image']['height']}"
                : null,
            'raw_response' => $data,
        ]);
    }

    /**
     * Create a result from Bing response
     */
    public static function createFromBing(int $searchId, array $data): self
    {
        return self::create([
            'search_id' => $searchId,
            'engine' => self::ENGINE_BING,
            'result_url' => $data['hostPageUrl'] ?? $data['contentUrl'] ?? '',
            'result_title' => $data['name'] ?? null,
            'result_snippet' => null,
            'thumbnail_url' => $data['thumbnailUrl'] ?? null,
            'match_type' => self::MATCH_SIMILAR,
            'similarity_score' => null,
            'first_seen_date' => isset($data['datePublished'])
                ? \Carbon\Carbon::parse($data['datePublished'])
                : null,
            'source_domain' => self::extractDomain($data['hostPageUrl'] ?? $data['contentUrl'] ?? ''),
            'page_title' => $data['hostPageDisplayUrl'] ?? null,
            'image_dimensions' => isset($data['width'], $data['height'])
                ? "{$data['width']}x{$data['height']}"
                : null,
            'raw_response' => $data,
        ]);
    }

    /**
     * Create a result from Baidu response
     */
    public static function createFromBaidu(int $searchId, array $data): self
    {
        return self::create([
            'search_id' => $searchId,
            'engine' => self::ENGINE_BAIDU,
            'result_url' => $data['fromUrl'] ?? $data['url'] ?? '',
            'result_title' => $data['fromPageTitle'] ?? null,
            'result_snippet' => null,
            'thumbnail_url' => $data['thumbUrl'] ?? null,
            'match_type' => self::MATCH_SIMILAR,
            'similarity_score' => null,
            'first_seen_date' => null,
            'source_domain' => self::extractDomain($data['fromUrl'] ?? $data['url'] ?? ''),
            'page_title' => $data['fromPageTitle'] ?? null,
            'image_dimensions' => isset($data['width'], $data['height'])
                ? "{$data['width']}x{$data['height']}"
                : null,
            'raw_response' => $data,
        ]);
    }
}
