<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

/**
 * Social Media Post Model
 *
 * Represents a post from a monitored social media account.
 *
 * Database Schema (social_media_posts):
 * @property int $id Primary key
 * @property string $uuid Public-facing unique identifier
 * @property int $account_id Foreign key to social_media_accounts
 * @property string $platform_post_id Platform's internal post ID
 * @property string|null $post_url Full URL to the post
 * @property string $post_type Type of post (text, image, video, link, thread)
 * @property string|null $content_text Text content of the post
 * @property array|null $media_urls JSON array of media URLs in the post
 * @property array|null $preserved_media_paths JSON array of local paths to preserved media
 * @property array|null $hashtags JSON array of hashtags in the post
 * @property array|null $mentions JSON array of mentioned accounts
 * @property int $like_count Number of likes/favorites
 * @property int $share_count Number of shares/retweets
 * @property int $comment_count Number of comments/replies
 * @property int $view_count Number of views (if available)
 * @property \Carbon\Carbon|null $posted_at When the post was published
 * @property bool $is_reply Whether this is a reply to another post
 * @property int|null $reply_to_id ID of parent post if reply
 * @property bool $is_repost Whether this is a repost/retweet
 * @property int|null $original_post_id ID of original post if repost
 * @property mixed|null $geolocation Spatial point if location data available
 * @property string|null $language_detected Detected language code
 * @property float|null $sentiment_score Sentiment analysis score (-1 to 1)
 * @property bool $is_archived Whether post content is preserved locally
 * @property int|null $event_id Optional link to an OSINT event
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 *
 * Relationships:
 * @property-read SocialMediaAccount $account The account that made this post
 * @property-read SocialMediaPost|null $replyTo The post this is replying to
 * @property-read SocialMediaPost|null $originalPost The original post if this is a repost
 * @property-read \Illuminate\Database\Eloquent\Collection|SocialMediaPost[] $replies Replies to this post
 * @property-read \Illuminate\Database\Eloquent\Collection|SocialMediaPost[] $reposts Reposts of this post
 * @property-read Event|null $event The linked OSINT event
 */
class SocialMediaPost extends Model
{
    use HasFactory, SoftDeletes;

    // Post type constants
    public const TYPE_TEXT = 'text';
    public const TYPE_IMAGE = 'image';
    public const TYPE_VIDEO = 'video';
    public const TYPE_LINK = 'link';
    public const TYPE_THREAD = 'thread';

    // Sentiment thresholds
    public const SENTIMENT_POSITIVE_THRESHOLD = 0.3;
    public const SENTIMENT_NEGATIVE_THRESHOLD = -0.3;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'uuid',
        'account_id',
        'platform_post_id',
        'post_url',
        'post_type',
        'content_text',
        'media_urls',
        'preserved_media_paths',
        'hashtags',
        'mentions',
        'like_count',
        'share_count',
        'comment_count',
        'view_count',
        'posted_at',
        'is_reply',
        'reply_to_id',
        'is_repost',
        'original_post_id',
        'geolocation',
        'language_detected',
        'sentiment_score',
        'is_archived',
        'event_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'media_urls' => 'array',
        'preserved_media_paths' => 'array',
        'hashtags' => 'array',
        'mentions' => 'array',
        'like_count' => 'integer',
        'share_count' => 'integer',
        'comment_count' => 'integer',
        'view_count' => 'integer',
        'posted_at' => 'datetime',
        'is_reply' => 'boolean',
        'is_repost' => 'boolean',
        'sentiment_score' => 'decimal:3',
        'is_archived' => 'boolean',
    ];

    /**
     * Get all post types.
     *
     * @return array<string>
     */
    public static function getPostTypes(): array
    {
        return [
            self::TYPE_TEXT,
            self::TYPE_IMAGE,
            self::TYPE_VIDEO,
            self::TYPE_LINK,
            self::TYPE_THREAD,
        ];
    }

    /**
     * Get post type labels.
     *
     * @return array<string, string>
     */
    public static function getPostTypeLabels(): array
    {
        return [
            self::TYPE_TEXT => 'Text',
            self::TYPE_IMAGE => 'Image',
            self::TYPE_VIDEO => 'Video',
            self::TYPE_LINK => 'Link',
            self::TYPE_THREAD => 'Thread',
        ];
    }

    /**
     * Get the account that made this post.
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(SocialMediaAccount::class, 'account_id');
    }

    /**
     * Get the post this is replying to.
     */
    public function replyTo(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reply_to_id');
    }

    /**
     * Get the original post if this is a repost.
     */
    public function originalPost(): BelongsTo
    {
        return $this->belongsTo(self::class, 'original_post_id');
    }

    /**
     * Get replies to this post.
     */
    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'reply_to_id');
    }

    /**
     * Get reposts of this post.
     */
    public function reposts(): HasMany
    {
        return $this->hasMany(self::class, 'original_post_id');
    }

    /**
     * Get the linked OSINT event.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    /**
     * Get the platform from the parent account.
     *
     * @return string|null
     */
    public function getPlatformAttribute(): ?string
    {
        return $this->account?->platform;
    }

    /**
     * Get post type label.
     *
     * @return string
     */
    public function getPostTypeLabel(): string
    {
        return self::getPostTypeLabels()[$this->post_type] ?? ucfirst($this->post_type);
    }

    /**
     * Get sentiment label based on score.
     *
     * @return string
     */
    public function getSentimentLabel(): string
    {
        if ($this->sentiment_score === null) {
            return 'Unknown';
        }

        if ($this->sentiment_score >= self::SENTIMENT_POSITIVE_THRESHOLD) {
            return 'Positive';
        }

        if ($this->sentiment_score <= self::SENTIMENT_NEGATIVE_THRESHOLD) {
            return 'Negative';
        }

        return 'Neutral';
    }

    /**
     * Check if post has media.
     *
     * @return bool
     */
    public function hasMedia(): bool
    {
        return !empty($this->media_urls);
    }

    /**
     * Get media count.
     *
     * @return int
     */
    public function getMediaCount(): int
    {
        return count($this->media_urls ?? []);
    }

    /**
     * Check if post has geolocation.
     *
     * @return bool
     */
    public function hasGeolocation(): bool
    {
        return $this->geolocation !== null;
    }

    /**
     * Get coordinates from geolocation.
     *
     * @return array|null
     */
    public function getCoordinates(): ?array
    {
        if (!$this->geolocation) {
            return null;
        }

        // Parse POINT geometry
        $result = DB::selectOne(
            'SELECT ST_X(geolocation) as lng, ST_Y(geolocation) as lat FROM social_media_posts WHERE id = ?',
            [$this->id]
        );

        if ($result) {
            return [
                'lat' => (float) $result->lat,
                'lng' => (float) $result->lng,
            ];
        }

        return null;
    }

    /**
     * Set geolocation from coordinates.
     *
     * @param float $latitude
     * @param float $longitude
     */
    public function setGeolocation(float $latitude, float $longitude): void
    {
        $this->geolocation = DB::raw("ST_GeomFromText('POINT({$longitude} {$latitude})')");
        $this->save();
    }

    /**
     * Get engagement rate.
     *
     * @return float
     */
    public function getEngagementRate(): float
    {
        $totalEngagement = $this->like_count + $this->share_count + $this->comment_count;
        $followerCount = $this->account?->follower_count ?? 0;

        if ($followerCount === 0) {
            return 0.0;
        }

        return round(($totalEngagement / $followerCount) * 100, 2);
    }

    /**
     * Get total engagement.
     *
     * @return int
     */
    public function getTotalEngagement(): int
    {
        return $this->like_count + $this->share_count + $this->comment_count;
    }

    /**
     * Mark post as archived.
     *
     * @param array|null $mediaPaths
     */
    public function markAsArchived(?array $mediaPaths = null): void
    {
        $this->update([
            'is_archived' => true,
            'preserved_media_paths' => $mediaPaths ?? $this->preserved_media_paths,
        ]);
    }

    /**
     * Link post to an event.
     *
     * @param int $eventId
     */
    public function linkToEvent(int $eventId): void
    {
        $this->update(['event_id' => $eventId]);
    }

    /**
     * Unlink post from event.
     */
    public function unlinkFromEvent(): void
    {
        $this->update(['event_id' => null]);
    }

    /**
     * Update engagement metrics.
     *
     * @param array $metrics
     */
    public function updateMetrics(array $metrics): void
    {
        $this->update([
            'like_count' => $metrics['like_count'] ?? $this->like_count,
            'share_count' => $metrics['share_count'] ?? $this->share_count,
            'comment_count' => $metrics['comment_count'] ?? $this->comment_count,
            'view_count' => $metrics['view_count'] ?? $this->view_count,
        ]);
    }

    /**
     * Check if post contains keyword.
     *
     * @param string $keyword
     * @return bool
     */
    public function containsKeyword(string $keyword): bool
    {
        $keyword = strtolower($keyword);
        $content = strtolower($this->content_text ?? '');

        return str_contains($content, $keyword);
    }

    /**
     * Check if post contains any of the keywords.
     *
     * @param array $keywords
     * @return array Matched keywords
     */
    public function findMatchingKeywords(array $keywords): array
    {
        $matches = [];
        $content = strtolower($this->content_text ?? '');

        foreach ($keywords as $keyword) {
            if (str_contains($content, strtolower($keyword))) {
                $matches[] = $keyword;
            }
        }

        return $matches;
    }

    /**
     * Scope for posts from a specific account.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $accountId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFromAccount($query, int $accountId)
    {
        return $query->where('account_id', $accountId);
    }

    /**
     * Scope for posts of a specific type.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('post_type', $type);
    }

    /**
     * Scope for posts with media.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithMedia($query)
    {
        return $query->whereNotNull('media_urls')
            ->whereRaw("JSON_LENGTH(media_urls) > 0");
    }

    /**
     * Scope for archived posts.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeArchived($query)
    {
        return $query->where('is_archived', true);
    }

    /**
     * Scope for posts not yet archived.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNotArchived($query)
    {
        return $query->where('is_archived', false);
    }

    /**
     * Scope for posts linked to events.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeLinkedToEvent($query)
    {
        return $query->whereNotNull('event_id');
    }

    /**
     * Scope for posts with geolocation.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithGeolocation($query)
    {
        return $query->whereNotNull('geolocation');
    }

    /**
     * Scope for posts within date range.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Carbon\Carbon $from
     * @param \Carbon\Carbon $to
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDateRange($query, $from, $to)
    {
        return $query->whereBetween('posted_at', [$from, $to]);
    }

    /**
     * Scope for posts with positive sentiment.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePositiveSentiment($query)
    {
        return $query->where('sentiment_score', '>=', self::SENTIMENT_POSITIVE_THRESHOLD);
    }

    /**
     * Scope for posts with negative sentiment.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNegativeSentiment($query)
    {
        return $query->where('sentiment_score', '<=', self::SENTIMENT_NEGATIVE_THRESHOLD);
    }

    /**
     * Scope for searching post content.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $term
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, string $term)
    {
        return $query->where('content_text', 'like', "%{$term}%");
    }

    /**
     * Scope for full-text search (MySQL).
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $term
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFullTextSearch($query, string $term)
    {
        return $query->whereRaw(
            "MATCH(content_text) AGAINST(? IN BOOLEAN MODE)",
            [$term]
        );
    }

    /**
     * Scope for posts containing a hashtag.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $hashtag
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithHashtag($query, string $hashtag)
    {
        $hashtag = ltrim($hashtag, '#');
        return $query->whereRaw("JSON_CONTAINS(hashtags, ?)", [json_encode($hashtag)]);
    }

    /**
     * Scope for posts mentioning a user.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $mention
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithMention($query, string $mention)
    {
        $mention = ltrim($mention, '@');
        return $query->whereRaw("JSON_CONTAINS(mentions, ?)", [json_encode($mention)]);
    }

    /**
     * Scope for original posts (not replies or reposts).
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOriginal($query)
    {
        return $query->where('is_reply', false)
            ->where('is_repost', false);
    }

    /**
     * Scope for replies only.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRepliesOnly($query)
    {
        return $query->where('is_reply', true);
    }

    /**
     * Scope for reposts only.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRepostsOnly($query)
    {
        return $query->where('is_repost', true);
    }

    /**
     * Scope for posts in a specific language.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $languageCode
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeLanguage($query, string $languageCode)
    {
        return $query->where('language_detected', $languageCode);
    }

    /**
     * Scope to order by engagement.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $direction
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrderByEngagement($query, string $direction = 'desc')
    {
        return $query->orderByRaw(
            "(like_count + share_count + comment_count) {$direction}"
        );
    }
}
