<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActorAlias extends Model
{
    use HasFactory;

    protected $fillable = [
        'actor_id',
        'alias',
        'alias_type',
        'language_code',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function actor(): BelongsTo
    {
        return $this->belongsTo(Actor::class);
    }
}
