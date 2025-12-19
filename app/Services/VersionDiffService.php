<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\EntityVersion;
use InvalidArgumentException;

/**
 * Version Diff Service
 *
 * Compares different versions of entities and generates diffs.
 */
class VersionDiffService
{
    /**
     * Generate diff between two versions
     */
    public function diff(int $versionAId, int $versionBId): array
    {
        $versionA = EntityVersion::findOrFail($versionAId);
        $versionB = EntityVersion::findOrFail($versionBId);

        if ($versionA->versionable_type !== $versionB->versionable_type ||
            $versionA->versionable_id !== $versionB->versionable_id) {
            throw new InvalidArgumentException('Versions must be of same entity');
        }

        return [
            'entity_type' => $versionA->versionable_type,
            'entity_id' => $versionA->versionable_id,
            'version_a' => [
                'id' => $versionA->id,
                'number' => $versionA->version_number,
                'created_at' => $versionA->created_at,
                'created_by' => $versionA->created_by,
                'creator' => $versionA->creator?->only(['id', 'name', 'email']),
            ],
            'version_b' => [
                'id' => $versionB->id,
                'number' => $versionB->version_number,
                'created_at' => $versionB->created_at,
                'created_by' => $versionB->created_by,
                'creator' => $versionB->creator?->only(['id', 'name', 'email']),
            ],
            'changes' => $this->computeDiff($versionA->snapshot, $versionB->snapshot),
            'summary' => $this->generateSummary($versionA->snapshot, $versionB->snapshot),
        ];
    }

    /**
     * Compare entity state at two different timestamps
     */
    public function diffAtTimestamps(string $type, int $id, string $timestampA, string $timestampB): array
    {
        $pointInTime = app(PointInTimeService::class);

        $stateA = $pointInTime->getEntityAt($type, $id, carbon($timestampA));
        $stateB = $pointInTime->getEntityAt($type, $id, carbon($timestampB));

        if (!$stateA || !$stateB) {
            throw new InvalidArgumentException('Entity did not exist at one or both timestamps');
        }

        return [
            'entity_type' => $type,
            'entity_id' => $id,
            'timestamp_a' => $timestampA,
            'timestamp_b' => $timestampB,
            'changes' => $this->computeDiff($stateA, $stateB),
            'summary' => $this->generateSummary($stateA, $stateB),
        ];
    }

    /**
     * Compute differences between two states
     */
    private function computeDiff(array $old, array $new): array
    {
        $changes = [];

        // Find all keys
        $allKeys = array_unique(array_merge(array_keys($old), array_keys($new)));

        foreach ($allKeys as $key) {
            $oldVal = $old[$key] ?? null;
            $newVal = $new[$key] ?? null;

            if ($oldVal !== $newVal) {
                $changes[$key] = [
                    'old' => $oldVal,
                    'new' => $newVal,
                    'change_type' => $this->determineChangeType($oldVal, $newVal),
                ];
            }
        }

        return $changes;
    }

    /**
     * Determine the type of change
     */
    private function determineChangeType($old, $new): string
    {
        if ($old === null) {
            return 'added';
        }
        if ($new === null) {
            return 'removed';
        }
        return 'modified';
    }

    /**
     * Generate human-readable summary
     */
    private function generateSummary(array $old, array $new): string
    {
        $changes = $this->computeDiff($old, $new);
        $count = count($changes);

        if ($count === 0) {
            return 'No changes';
        }

        $modified = count(array_filter($changes, fn($c) => $c['change_type'] === 'modified'));
        $added = count(array_filter($changes, fn($c) => $c['change_type'] === 'added'));
        $removed = count(array_filter($changes, fn($c) => $c['change_type'] === 'removed'));

        $parts = [];
        if ($modified > 0) {
            $parts[] = "$modified modified";
        }
        if ($added > 0) {
            $parts[] = "$added added";
        }
        if ($removed > 0) {
            $parts[] = "$removed removed";
        }

        return implode(', ', $parts);
    }

    /**
     * Get field-by-field comparison
     */
    public function getFieldComparison(int $versionAId, int $versionBId): array
    {
        $diff = $this->diff($versionAId, $versionBId);

        $fieldComparisons = [];

        foreach ($diff['changes'] as $field => $change) {
            $fieldComparisons[] = [
                'field' => $field,
                'old_value' => $change['old'],
                'new_value' => $change['new'],
                'change_type' => $change['change_type'],
                'old_display' => $this->formatValue($change['old']),
                'new_display' => $this->formatValue($change['new']),
            ];
        }

        return $fieldComparisons;
    }

    /**
     * Format value for display
     */
    private function formatValue($value): string
    {
        if ($value === null) {
            return '(null)';
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_array($value)) {
            return json_encode($value);
        }

        return (string) $value;
    }
}
