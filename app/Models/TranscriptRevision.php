<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Transcript Revision Model
 *
 * Represents a revision/version of a transcription for edit history.
 *
 * @property int $id
 * @property int $transcription_id
 * @property int $user_id
 * @property int $revision_number
 * @property string $full_text
 * @property array|null $changes
 * @property string|null $comment
 * @property \Carbon\Carbon $created_at
 */
class TranscriptRevision extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'transcription_id',
        'user_id',
        'revision_number',
        'full_text',
        'changes',
        'comment',
        'created_at',
    ];

    protected $casts = [
        'revision_number' => 'integer',
        'changes' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Get the transcription this revision belongs to.
     */
    public function transcription(): BelongsTo
    {
        return $this->belongsTo(Transcription::class);
    }

    /**
     * Get the user who created this revision.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get word count of this revision.
     */
    public function getWordCount(): int
    {
        return str_word_count($this->full_text);
    }

    /**
     * Compare with another revision and return diff.
     */
    public function diffWith(self $other): array
    {
        $thisLines = explode("\n", $this->full_text);
        $otherLines = explode("\n", $other->full_text);

        $diff = [];
        $maxLines = max(count($thisLines), count($otherLines));

        for ($i = 0; $i < $maxLines; $i++) {
            $thisLine = $thisLines[$i] ?? null;
            $otherLine = $otherLines[$i] ?? null;

            if ($thisLine !== $otherLine) {
                $diff[] = [
                    'line' => $i + 1,
                    'old' => $otherLine,
                    'new' => $thisLine,
                ];
            }
        }

        return $diff;
    }
}
