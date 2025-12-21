<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * User Skill Preference Model
 *
 * Stores user preferences for specific skills.
 *
 * @property int $id
 * @property int $user_id
 * @property int $skill_id
 * @property bool $is_enabled
 * @property int|null $priority_override
 * @property array|null $custom_settings
 */
class UserSkillPreference extends Model
{
    protected $fillable = [
        'user_id',
        'skill_id',
        'is_enabled',
        'priority_override',
        'custom_settings',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'priority_override' => 'integer',
        'custom_settings' => 'array',
    ];

    /**
     * Get the user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the skill.
     */
    public function skill(): BelongsTo
    {
        return $this->belongsTo(Skill::class);
    }
}
