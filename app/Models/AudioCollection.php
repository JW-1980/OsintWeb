<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Audio Collection Model
 *
 * Represents a playlist or folder of audio files.
 *
 * @property int $id
 * @property string $uuid
 * @property int $user_id
 * @property string $name
 * @property string|null $description
 * @property string $visibility
 * @property string|null $cover_image_path
 * @property int $audio_count
 * @property int $total_duration
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class AudioCollection extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'user_id',
        'name',
        'description',
        'visibility',
        'cover_image_path',
        'audio_count',
        'total_duration',
    ];

    protected $casts = [
        'audio_count' => 'integer',
        'total_duration' => 'integer',
    ];

    /**
     * Get the user who owns this collection.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the audio files in this collection.
     */
    public function audioFiles(): BelongsToMany
    {
        return $this->belongsToMany(AudioFile::class, 'audio_collection_items')
            ->withPivot('position')
            ->orderByPivot('position')
            ->withTimestamps();
    }

    /**
     * Add audio file to collection.
     */
    public function addAudio(AudioFile $audio, ?int $position = null): void
    {
        $position = $position ?? ($this->audioFiles()->count() + 1);

        $this->audioFiles()->attach($audio->id, ['position' => $position]);
        $this->updateStats();
    }

    /**
     * Remove audio file from collection.
     */
    public function removeAudio(AudioFile $audio): void
    {
        $this->audioFiles()->detach($audio->id);
        $this->updateStats();
    }

    /**
     * Reorder audio files in collection.
     */
    public function reorder(array $audioIds): void
    {
        foreach ($audioIds as $position => $audioId) {
            $this->audioFiles()->updateExistingPivot($audioId, ['position' => $position + 1]);
        }
    }

    /**
     * Update collection statistics.
     */
    public function updateStats(): void
    {
        $stats = $this->audioFiles()
            ->selectRaw('COUNT(*) as count, SUM(duration) as duration')
            ->first();

        $this->update([
            'audio_count' => $stats->count ?? 0,
            'total_duration' => $stats->duration ?? 0,
        ]);
    }

    /**
     * Get formatted total duration.
     */
    public function getFormattedDuration(): string
    {
        $hours = floor($this->total_duration / 3600);
        $minutes = floor(($this->total_duration % 3600) / 60);

        if ($hours > 0) {
            return "{$hours}h {$minutes}m";
        }

        return "{$minutes} min";
    }

    /**
     * Scope to filter public collections.
     */
    public function scopePublic($query)
    {
        return $query->where('visibility', 'public');
    }
}
