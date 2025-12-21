<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Speaker Model
 *
 * Represents an identified speaker for transcriptions.
 *
 * @property int $id
 * @property string $uuid
 * @property int $user_id
 * @property string $name
 * @property string $identifier
 * @property string|null $description
 * @property string|null $organization
 * @property string|null $role
 * @property string|null $avatar_url
 * @property array|null $voice_samples
 * @property array|null $metadata
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class Speaker extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'user_id',
        'name',
        'identifier',
        'description',
        'organization',
        'role',
        'avatar_url',
        'voice_samples',
        'metadata',
    ];

    protected $casts = [
        'voice_samples' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Get the user who created this speaker profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get segments with this speaker.
     */
    public function getSegmentCount(): int
    {
        return TranscriptSegment::where('speaker_id', $this->identifier)->count();
    }

    /**
     * Search speakers by name or identifier.
     */
    public static function search(string $query)
    {
        return static::where('name', 'like', "%{$query}%")
            ->orWhere('identifier', 'like', "%{$query}%")
            ->orWhere('organization', 'like', "%{$query}%")
            ->limit(10)
            ->get();
    }
}
