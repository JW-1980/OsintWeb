<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
