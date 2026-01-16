<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Evidence Tag Model
 *
 * Represents a tag attached to preserved evidence.
 *
 * @property int $id
 * @property int $preserved_evidence_id
 * @property string $tag
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class EvidenceTag extends Model
{
    protected $table = 'evidence_tags';

    protected $fillable = [
        'preserved_evidence_id',
        'tag',
    ];

    /**
     * Get the parent evidence
     */
    public function evidence(): BelongsTo
    {
        return $this->belongsTo(PreservedEvidence::class, 'preserved_evidence_id');
    }
}
