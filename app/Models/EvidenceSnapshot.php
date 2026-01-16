<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * Evidence Snapshot Model
 *
 * Represents a version/snapshot of preserved evidence.
 *
 * @property int $id
 * @property int $preserved_evidence_id
 * @property string $snapshot_hash
 * @property string $snapshot_path
 * @property int $file_size
 * @property array|null $metadata
 * @property bool $is_primary
 * @property \Carbon\Carbon $captured_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class EvidenceSnapshot extends Model
{
    use HasFactory;

    protected $table = 'evidence_snapshots';

    protected $fillable = [
        'preserved_evidence_id',
        'snapshot_hash',
        'snapshot_path',
        'file_size',
        'metadata',
        'is_primary',
        'captured_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'file_size' => 'integer',
        'is_primary' => 'boolean',
        'captured_at' => 'datetime',
    ];

    /**
     * Get the parent preserved evidence
     */
    public function evidence(): BelongsTo
    {
        return $this->belongsTo(PreservedEvidence::class, 'preserved_evidence_id');
    }

    /**
     * Verify the integrity of this snapshot
     *
     * @return bool True if content matches the stored hash
     */
    public function verifyIntegrity(): bool
    {
        if (!Storage::disk('evidence')->exists($this->snapshot_path)) {
            return false;
        }

        $content = Storage::disk('evidence')->get($this->snapshot_path);
        $currentHash = hash('sha256', $content);

        return hash_equals($this->snapshot_hash, $currentHash);
    }

    /**
     * Get the local file path
     *
     * @return string|null
     */
    public function getLocalFilePath(): ?string
    {
        return Storage::disk('evidence')->path($this->snapshot_path);
    }

    /**
     * Get the download URL
     *
     * @return string|null
     */
    public function getDownloadUrl(): ?string
    {
        if (Storage::disk('evidence')->exists($this->snapshot_path)) {
            return Storage::disk('evidence')->url($this->snapshot_path);
        }

        return null;
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
     * Check if content has changed compared to parent evidence
     *
     * @return bool
     */
    public function hasChanged(): bool
    {
        return $this->snapshot_hash !== $this->evidence->content_hash;
    }

    /**
     * Mark this snapshot as primary
     */
    public function markAsPrimary(): void
    {
        // Remove primary from other snapshots of the same evidence
        self::where('preserved_evidence_id', $this->preserved_evidence_id)
            ->where('id', '!=', $this->id)
            ->update(['is_primary' => false]);

        $this->update(['is_primary' => true]);
    }

    /**
     * Scope to filter primary snapshots
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    /**
     * Scope to filter by evidence
     */
    public function scopeForEvidence($query, int $evidenceId)
    {
        return $query->where('preserved_evidence_id', $evidenceId);
    }

    /**
     * Scope to order by most recent
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('captured_at', 'desc');
    }
}
