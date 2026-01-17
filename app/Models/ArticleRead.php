<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ArticleRead Model
 *
 * Tracks article reading progress and analytics.
 *
 * Database Schema:
 * @property int $id Primary key
 * @property int $article_id Foreign key to articles table
 * @property int|null $user_id Foreign key to users table (null for anonymous)
 * @property string|null $ip_address Reader's IP address
 * @property string|null $session_id Session identifier for anonymous tracking
 * @property int $read_percentage Reading progress percentage (0-100)
 * @property int $time_spent_seconds Total time spent reading in seconds
 * @property \Carbon\Carbon|null $started_at When reading started
 * @property \Carbon\Carbon|null $completed_at When article was fully read
 *
 * @property-read Article $article
 * @property-read User|null $user
 */
class ArticleRead extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'article_id',
        'user_id',
        'ip_address',
        'session_id',
        'read_percentage',
        'time_spent_seconds',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'read_percentage' => 'integer',
        'time_spent_seconds' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mark as completed.
     */
    public function markCompleted(): void
    {
        $this->update([
            'read_percentage' => 100,
            'completed_at' => now(),
        ]);
    }

    /**
     * Update reading progress.
     */
    public function updateProgress(int $percentage, int $timeSpent): void
    {
        $this->update([
            'read_percentage' => min(100, max($this->read_percentage, $percentage)),
            'time_spent_seconds' => $this->time_spent_seconds + $timeSpent,
        ]);

        if ($percentage >= 100 && !$this->completed_at) {
            $this->completed_at = now();
            $this->save();
        }
    }
}
