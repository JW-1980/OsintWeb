<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

/**
 * Preserved Evidence Model
 *
 * Represents preserved web content for OSINT evidence.
 *
 * @property int $id
 * @property string $uuid
 * @property string $original_url
 * @property string|null $archived_url
 * @property string|null $ipfs_hash
 * @property string|null $local_path
 * @property string $content_hash
 * @property string $content_type
 * @property int $file_size
 * @property array|null $metadata
 * @property string $capture_status
 * @property string|null $error_message
 * @property int $retry_count
 * @property \Carbon\Carbon|null $captured_at
 * @property \Carbon\Carbon|null $expires_at
 * @property \Carbon\Carbon|null $last_verified_at
 * @property bool $is_verified
 * @property int|null $event_id
 * @property int $created_by
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class PreservedEvidence extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'preserved_evidence';

    // Content types
    public const TYPE_WEBPAGE = 'webpage';
    public const TYPE_IMAGE = 'image';
    public const TYPE_VIDEO = 'video';
    public const TYPE_DOCUMENT = 'document';
    public const TYPE_SOCIAL_POST = 'social_post';
    public const TYPE_AUDIO = 'audio';
    public const TYPE_ARCHIVE = 'archive';

    // Capture statuses
    public const STATUS_PENDING = 'pending';
    public const STATUS_CAPTURING = 'capturing';
    public const STATUS_CAPTURED = 'captured';
    public const STATUS_FAILED = 'failed';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_DELETED = 'deleted';

    protected $fillable = [
        'uuid',
        'original_url',
        'archived_url',
        'ipfs_hash',
        'local_path',
        'content_hash',
        'content_type',
        'file_size',
        'metadata',
        'capture_status',
        'error_message',
        'retry_count',
        'captured_at',
        'expires_at',
        'last_verified_at',
        'is_verified',
        'event_id',
        'created_by',
    ];

    protected $casts = [
        'metadata' => 'array',
        'file_size' => 'integer',
        'retry_count' => 'integer',
        'captured_at' => 'datetime',
        'expires_at' => 'datetime',
        'last_verified_at' => 'datetime',
        'is_verified' => 'boolean',
    ];

    /**
     * Get the user who created this evidence
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the associated event
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the tags for this evidence
     */
    public function tags(): HasMany
    {
        return $this->hasMany(EvidenceTag::class, 'preserved_evidence_id');
    }

    /**
     * Get all snapshots for this evidence
     */
    public function snapshots(): HasMany
    {
        return $this->hasMany(EvidenceSnapshot::class, 'preserved_evidence_id');
    }

    /**
     * Get the primary/latest snapshot
     */
    public function primarySnapshot()
    {
        return $this->hasOne(EvidenceSnapshot::class, 'preserved_evidence_id')
            ->where('is_primary', true);
    }

    /**
     * Verify the integrity of the stored content
     *
     * @return bool True if content matches the stored hash
     */
    public function verifyIntegrity(): bool
    {
        if (!$this->local_path || !Storage::disk('evidence')->exists($this->local_path)) {
            return false;
        }

        $content = Storage::disk('evidence')->get($this->local_path);
        $currentHash = hash('sha256', $content);

        $isValid = hash_equals($this->content_hash, $currentHash);

        $this->update([
            'is_verified' => $isValid,
            'last_verified_at' => now(),
        ]);

        return $isValid;
    }

    /**
     * Get the best available access URL for this evidence
     *
     * Priority: Local -> IPFS -> Wayback Machine -> Original URL
     *
     * @return string|null
     */
    public function getAccessUrl(): ?string
    {
        // Try local storage first
        if ($this->local_path && Storage::disk('evidence')->exists($this->local_path)) {
            return Storage::disk('evidence')->url($this->local_path);
        }

        // Try IPFS gateway
        if ($this->ipfs_hash) {
            $gateway = config('evidence.ipfs.gateway', 'https://ipfs.io/ipfs/');
            return rtrim($gateway, '/') . '/' . $this->ipfs_hash;
        }

        // Try archived URL (Wayback Machine)
        if ($this->archived_url) {
            return $this->archived_url;
        }

        // Fall back to original URL
        return $this->original_url;
    }

    /**
     * Get the local file path if available
     *
     * @return string|null
     */
    public function getLocalFilePath(): ?string
    {
        if (!$this->local_path) {
            return null;
        }

        return Storage::disk('evidence')->path($this->local_path);
    }

    /**
     * Check if the evidence has expired
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    /**
     * Check if evidence can be retried
     *
     * @return bool
     */
    public function canRetry(): bool
    {
        $maxRetries = config('evidence.max_retries', 3);
        return $this->capture_status === self::STATUS_FAILED
            && $this->retry_count < $maxRetries;
    }

    /**
     * Mark as capturing
     */
    public function markAsCapturing(): void
    {
        $this->update([
            'capture_status' => self::STATUS_CAPTURING,
        ]);
    }

    /**
     * Mark as captured successfully
     *
     * @param string $contentHash
     * @param int $fileSize
     * @param string|null $localPath
     */
    public function markAsCaptured(string $contentHash, int $fileSize, ?string $localPath = null): void
    {
        $this->update([
            'capture_status' => self::STATUS_CAPTURED,
            'content_hash' => $contentHash,
            'file_size' => $fileSize,
            'local_path' => $localPath ?? $this->local_path,
            'captured_at' => now(),
            'error_message' => null,
        ]);
    }

    /**
     * Mark as failed
     *
     * @param string $errorMessage
     */
    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'capture_status' => self::STATUS_FAILED,
            'error_message' => $errorMessage,
            'retry_count' => $this->retry_count + 1,
        ]);
    }

    /**
     * Get human-readable file size
     *
     * @return string
     */
    public function getHumanFileSizeAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes >= 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get the title from metadata
     *
     * @return string|null
     */
    public function getTitleAttribute(): ?string
    {
        return $this->metadata['title'] ?? null;
    }

    /**
     * Get the description from metadata
     *
     * @return string|null
     */
    public function getDescriptionAttribute(): ?string
    {
        return $this->metadata['description'] ?? null;
    }

    /**
     * Scope to filter by status
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('capture_status', $status);
    }

    /**
     * Scope to filter pending evidence
     */
    public function scopePending($query)
    {
        return $query->where('capture_status', self::STATUS_PENDING);
    }

    /**
     * Scope to filter captured evidence
     */
    public function scopeCaptured($query)
    {
        return $query->where('capture_status', self::STATUS_CAPTURED);
    }

    /**
     * Scope to filter failed evidence
     */
    public function scopeFailed($query)
    {
        return $query->where('capture_status', self::STATUS_FAILED);
    }

    /**
     * Scope to filter retriable evidence
     */
    public function scopeRetriable($query)
    {
        $maxRetries = config('evidence.max_retries', 3);
        return $query->where('capture_status', self::STATUS_FAILED)
            ->where('retry_count', '<', $maxRetries);
    }

    /**
     * Scope to filter by content type
     */
    public function scopeType($query, string $type)
    {
        return $query->where('content_type', $type);
    }

    /**
     * Scope to filter by creator
     */
    public function scopeByCreator($query, int $userId)
    {
        return $query->where('created_by', $userId);
    }

    /**
     * Scope to filter by event
     */
    public function scopeForEvent($query, int $eventId)
    {
        return $query->where('event_id', $eventId);
    }

    /**
     * Scope to filter expired evidence
     */
    public function scopeExpired($query)
    {
        return $query->whereNotNull('expires_at')
            ->where('expires_at', '<', now());
    }

    /**
     * Scope to filter non-expired evidence
     */
    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
                ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Scope to filter unverified evidence
     */
    public function scopeUnverified($query)
    {
        return $query->where('is_verified', false)
            ->where('capture_status', self::STATUS_CAPTURED);
    }

    /**
     * Scope to search by URL
     */
    public function scopeSearchUrl($query, string $term)
    {
        return $query->where('original_url', 'like', "%{$term}%");
    }

    /**
     * Get all available content types
     *
     * @return array
     */
    public static function getContentTypes(): array
    {
        return [
            self::TYPE_WEBPAGE,
            self::TYPE_IMAGE,
            self::TYPE_VIDEO,
            self::TYPE_DOCUMENT,
            self::TYPE_SOCIAL_POST,
            self::TYPE_AUDIO,
            self::TYPE_ARCHIVE,
        ];
    }

    /**
     * Get all available statuses
     *
     * @return array
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_CAPTURING,
            self::STATUS_CAPTURED,
            self::STATUS_FAILED,
            self::STATUS_EXPIRED,
            self::STATUS_DELETED,
        ];
    }
}
