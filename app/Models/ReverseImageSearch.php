<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

/**
 * ReverseImageSearch Model
 *
 * Represents a reverse image search request that aggregates results from multiple
 * search engines (Google, Bing, TinEye, Yandex, Baidu) to find where an image
 * appears online and detect potential manipulation.
 *
 * Database Schema:
 * @property int $id Primary key
 * @property string $uuid Public unique identifier
 * @property string $image_path Local storage path for uploaded/cached image
 * @property string $image_hash Perceptual hash (pHash) for deduplication
 * @property string|null $image_url Original URL if image was fetched from web
 * @property string $search_status Status: pending, searching, completed, failed
 * @property int $created_by Foreign key to users table
 * @property int|null $event_id Foreign key to events table (nullable)
 * @property int $total_results Count of results found across all engines
 * @property array|null $engines_searched List of engines queried
 * @property array|null $engines_completed List of engines that completed
 * @property \Carbon\Carbon|null $earliest_found_date Earliest date image appeared online
 * @property string|null $error_message Error message if search failed
 * @property \Carbon\Carbon|null $search_started_at When search began
 * @property \Carbon\Carbon|null $search_completed_at When search finished
 * @property array|null $metadata Image dimensions, format, file size, etc.
 * @property \Carbon\Carbon $created_at Record creation timestamp
 * @property \Carbon\Carbon $updated_at Record update timestamp
 *
 * Relationships:
 * @property-read User $creator The user who created this search
 * @property-read Event|null $event Associated OSINT event (optional)
 * @property-read \Illuminate\Database\Eloquent\Collection<ReverseImageResult> $results Search results
 *
 * Indexes:
 * - uuid (unique)
 * - image_hash (for deduplication)
 * - created_by (user lookup)
 * - event_id (event association)
 * - search_status (queue processing)
 * - created_at (chronological ordering)
 * - (created_by, created_at) composite
 */
class ReverseImageSearch extends Model
{
    use HasFactory;

    /**
     * Search status constants
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_SEARCHING = 'searching';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    /**
     * Available search engines
     */
    public const ENGINE_GOOGLE = 'google';
    public const ENGINE_BING = 'bing';
    public const ENGINE_TINEYE = 'tineye';
    public const ENGINE_YANDEX = 'yandex';
    public const ENGINE_BAIDU = 'baidu';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'uuid',
        'image_path',
        'image_hash',
        'image_url',
        'search_status',
        'created_by',
        'event_id',
        'total_results',
        'engines_searched',
        'engines_completed',
        'earliest_found_date',
        'error_message',
        'search_started_at',
        'search_completed_at',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'engines_searched' => 'array',
        'engines_completed' => 'array',
        'earliest_found_date' => 'date',
        'search_started_at' => 'datetime',
        'search_completed_at' => 'datetime',
        'metadata' => 'array',
        'total_results' => 'integer',
    ];

    /**
     * Get the user who created this search
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the associated event (if any)
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get all search results
     */
    public function results(): HasMany
    {
        return $this->hasMany(ReverseImageResult::class, 'search_id');
    }

    /**
     * Scope to filter by creator
     */
    public function scopeByCreator($query, int $userId)
    {
        return $query->where('created_by', $userId);
    }

    /**
     * Scope to filter by status
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('search_status', $status);
    }

    /**
     * Scope to filter pending searches
     */
    public function scopePending($query)
    {
        return $query->where('search_status', self::STATUS_PENDING);
    }

    /**
     * Scope to filter completed searches
     */
    public function scopeCompleted($query)
    {
        return $query->where('search_status', self::STATUS_COMPLETED);
    }

    /**
     * Scope to filter failed searches
     */
    public function scopeFailed($query)
    {
        return $query->where('search_status', self::STATUS_FAILED);
    }

    /**
     * Scope to filter by image hash (for finding duplicates)
     */
    public function scopeByHash($query, string $hash)
    {
        return $query->where('image_hash', $hash);
    }

    /**
     * Scope to filter by event
     */
    public function scopeForEvent($query, int $eventId)
    {
        return $query->where('event_id', $eventId);
    }

    /**
     * Scope to search by URL
     */
    public function scopeSearchUrl($query, string $term)
    {
        return $query->where('image_url', 'like', "%{$term}%");
    }

    /**
     * Mark search as started
     */
    public function markAsSearching(array $engines): void
    {
        $this->update([
            'search_status' => self::STATUS_SEARCHING,
            'engines_searched' => $engines,
            'engines_completed' => [],
            'search_started_at' => now(),
            'error_message' => null,
        ]);
    }

    /**
     * Mark an engine as completed
     */
    public function markEngineCompleted(string $engine, int $resultsCount = 0): void
    {
        $completed = $this->engines_completed ?? [];
        if (!in_array($engine, $completed)) {
            $completed[] = $engine;
        }

        $this->update([
            'engines_completed' => $completed,
            'total_results' => $this->total_results + $resultsCount,
        ]);
    }

    /**
     * Mark search as completed
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'search_status' => self::STATUS_COMPLETED,
            'search_completed_at' => now(),
        ]);
    }

    /**
     * Mark search as failed
     */
    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'search_status' => self::STATUS_FAILED,
            'error_message' => $errorMessage,
            'search_completed_at' => now(),
        ]);
    }

    /**
     * Check if search is pending
     */
    public function isPending(): bool
    {
        return $this->search_status === self::STATUS_PENDING;
    }

    /**
     * Check if search is in progress
     */
    public function isSearching(): bool
    {
        return $this->search_status === self::STATUS_SEARCHING;
    }

    /**
     * Check if search is completed
     */
    public function isCompleted(): bool
    {
        return $this->search_status === self::STATUS_COMPLETED;
    }

    /**
     * Check if search failed
     */
    public function isFailed(): bool
    {
        return $this->search_status === self::STATUS_FAILED;
    }

    /**
     * Check if search can be retried
     */
    public function canRetry(): bool
    {
        return $this->isFailed() || $this->isPending();
    }

    /**
     * Get the search progress percentage
     */
    public function getProgressPercentage(): int
    {
        if ($this->isCompleted()) {
            return 100;
        }

        if ($this->isPending()) {
            return 0;
        }

        $searched = count($this->engines_searched ?? []);
        $completed = count($this->engines_completed ?? []);

        if ($searched === 0) {
            return 0;
        }

        return (int) round(($completed / $searched) * 100);
    }

    /**
     * Get search duration in seconds
     */
    public function getSearchDuration(): ?int
    {
        if (!$this->search_started_at) {
            return null;
        }

        $endTime = $this->search_completed_at ?? now();
        return $this->search_started_at->diffInSeconds($endTime);
    }

    /**
     * Get results grouped by engine
     */
    public function getResultsByEngine(): array
    {
        return $this->results()
            ->get()
            ->groupBy('engine')
            ->map(fn($results) => $results->values())
            ->toArray();
    }

    /**
     * Get results by match type
     */
    public function getResultsByMatchType(): array
    {
        return $this->results()
            ->get()
            ->groupBy('match_type')
            ->map(fn($results) => $results->values())
            ->toArray();
    }

    /**
     * Get exact match results only
     */
    public function getExactMatches()
    {
        return $this->results()
            ->where('match_type', ReverseImageResult::MATCH_EXACT)
            ->orderByDesc('similarity_score')
            ->get();
    }

    /**
     * Get potentially manipulated results
     */
    public function getManipulatedResults()
    {
        return $this->results()
            ->where('is_potentially_manipulated', true)
            ->get();
    }

    /**
     * Get unique domains where image was found
     */
    public function getUniqueDomains(): array
    {
        return $this->results()
            ->distinct()
            ->pluck('source_domain')
            ->toArray();
    }

    /**
     * Get the earliest found date across all results
     */
    public function findEarliestDate(): ?\Carbon\Carbon
    {
        $earliest = $this->results()
            ->whereNotNull('first_seen_date')
            ->min('first_seen_date');

        return $earliest ? \Carbon\Carbon::parse($earliest) : null;
    }

    /**
     * Update the earliest found date based on results
     */
    public function updateEarliestFoundDate(): void
    {
        $earliest = $this->findEarliestDate();
        if ($earliest) {
            $this->update(['earliest_found_date' => $earliest]);
        }
    }

    /**
     * Get the image URL for display
     */
    public function getImageUrl(): ?string
    {
        if ($this->image_url) {
            return $this->image_url;
        }

        if ($this->image_path && Storage::disk('reverse_images')->exists($this->image_path)) {
            return Storage::disk('reverse_images')->url($this->image_path);
        }

        return null;
    }

    /**
     * Check if the local image file exists
     */
    public function imageExists(): bool
    {
        return $this->image_path && Storage::disk('reverse_images')->exists($this->image_path);
    }

    /**
     * Get all available statuses
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_SEARCHING,
            self::STATUS_COMPLETED,
            self::STATUS_FAILED,
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
     * Get engine display names
     */
    public static function getEngineDisplayNames(): array
    {
        return [
            self::ENGINE_GOOGLE => 'Google Images',
            self::ENGINE_BING => 'Bing Visual Search',
            self::ENGINE_TINEYE => 'TinEye',
            self::ENGINE_YANDEX => 'Yandex Images',
            self::ENGINE_BAIDU => 'Baidu Images',
        ];
    }

    /**
     * Get statistics summary
     */
    public function getStatsSummary(): array
    {
        $results = $this->results;

        return [
            'total_results' => $results->count(),
            'exact_matches' => $results->where('match_type', ReverseImageResult::MATCH_EXACT)->count(),
            'similar_matches' => $results->where('match_type', ReverseImageResult::MATCH_SIMILAR)->count(),
            'partial_matches' => $results->where('match_type', ReverseImageResult::MATCH_PARTIAL)->count(),
            'potentially_manipulated' => $results->where('is_potentially_manipulated', true)->count(),
            'unique_domains' => $results->unique('source_domain')->count(),
            'engines_completed' => count($this->engines_completed ?? []),
            'engines_searched' => count($this->engines_searched ?? []),
            'earliest_found_date' => $this->earliest_found_date?->toDateString(),
            'search_duration_seconds' => $this->getSearchDuration(),
        ];
    }
}
