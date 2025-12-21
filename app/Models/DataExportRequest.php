<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Data Export Request Model
 *
 * Handles GDPR right to data portability requests.
 *
 * @property int $id
 * @property string $uuid
 * @property int $user_id
 * @property string $status
 * @property string $format
 * @property array|null $include_data
 * @property string|null $download_token
 * @property string|null $file_path
 * @property int|null $file_size
 * @property \Carbon\Carbon $requested_at
 * @property \Carbon\Carbon|null $processing_started_at
 * @property \Carbon\Carbon|null $completed_at
 * @property \Carbon\Carbon|null $expires_at
 * @property \Carbon\Carbon|null $downloaded_at
 * @property string|null $ip_address
 * @property string|null $error_message
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class DataExportRequest extends Model
{
    protected $fillable = [
        'uuid',
        'user_id',
        'status',
        'format',
        'include_data',
        'download_token',
        'file_path',
        'file_size',
        'requested_at',
        'processing_started_at',
        'completed_at',
        'expires_at',
        'downloaded_at',
        'ip_address',
        'error_message',
    ];

    protected $casts = [
        'include_data' => 'array',
        'file_size' => 'integer',
        'requested_at' => 'datetime',
        'processing_started_at' => 'datetime',
        'completed_at' => 'datetime',
        'expires_at' => 'datetime',
        'downloaded_at' => 'datetime',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_EXPIRED = 'expired';

    public const FORMAT_JSON = 'json';
    public const FORMAT_CSV = 'csv';
    public const FORMAT_XML = 'xml';

    // Data types that can be exported
    public const DATA_PROFILE = 'profile';
    public const DATA_COMMENTS = 'comments';
    public const DATA_ARTICLES = 'articles';
    public const DATA_EVENTS = 'events';
    public const DATA_BOOKMARKS = 'bookmarks';
    public const DATA_ACTIVITY = 'activity';
    public const DATA_CONSENT = 'consent';
    public const DATA_SESSIONS = 'sessions';

    /**
     * Get the user that made this request.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if request is downloadable.
     */
    public function isDownloadable(): bool
    {
        return $this->status === self::STATUS_COMPLETED
            && $this->expires_at > now()
            && !empty($this->download_token);
    }

    /**
     * Check if request has expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at < now();
    }

    /**
     * Mark as processing.
     */
    public function startProcessing(): void
    {
        $this->update([
            'status' => self::STATUS_PROCESSING,
            'processing_started_at' => now(),
        ]);
    }

    /**
     * Mark as completed.
     */
    public function markCompleted(string $filePath, int $fileSize): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'file_path' => $filePath,
            'file_size' => $fileSize,
            'download_token' => Str::random(64),
            'completed_at' => now(),
            'expires_at' => now()->addDays(7), // Download available for 7 days
        ]);
    }

    /**
     * Mark as failed.
     */
    public function markFailed(string $errorMessage): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'error_message' => $errorMessage,
        ]);
    }

    /**
     * Record download.
     */
    public function recordDownload(): void
    {
        $this->update(['downloaded_at' => now()]);
    }

    /**
     * Create a new export request.
     */
    public static function createRequest(
        User $user,
        string $format = self::FORMAT_JSON,
        ?array $includeData = null,
        ?string $ipAddress = null
    ): self {
        return static::create([
            'uuid' => Str::uuid(),
            'user_id' => $user->id,
            'status' => self::STATUS_PENDING,
            'format' => $format,
            'include_data' => $includeData ?? [
                self::DATA_PROFILE,
                self::DATA_COMMENTS,
                self::DATA_BOOKMARKS,
                self::DATA_ACTIVITY,
                self::DATA_CONSENT,
            ],
            'requested_at' => now(),
            'ip_address' => $ipAddress,
        ]);
    }

    /**
     * Scope to filter by status.
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter pending requests.
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope to filter expired requests.
     */
    public function scopeExpired($query)
    {
        return $query->where('status', self::STATUS_COMPLETED)
            ->where('expires_at', '<', now());
    }

    /**
     * Get all exportable data types.
     */
    public static function getDataTypes(): array
    {
        return [
            self::DATA_PROFILE => 'Profile Information',
            self::DATA_COMMENTS => 'Comments',
            self::DATA_ARTICLES => 'Articles',
            self::DATA_EVENTS => 'Events',
            self::DATA_BOOKMARKS => 'Bookmarks',
            self::DATA_ACTIVITY => 'Activity Log',
            self::DATA_CONSENT => 'Consent History',
            self::DATA_SESSIONS => 'Session History',
        ];
    }
}
