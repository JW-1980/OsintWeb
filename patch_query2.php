<?php
$file = 'app/Domains/Intelligence/Queries/EventQueryBuilder.php';
$content = file_get_contents($file);

$content = str_replace(
    'public function dateRange(string , string ): self',
    'public function dateRange(string $startDate, string $endDate): self',
    $content
);

$content = str_replace(
    'return ->whereBetween(\'occurred_at\', [, ]);',
    'return $this->whereBetween(\'occurred_at\', [$startDate, $endDate]);',
    $content
);

$content = str_replace(
    'public function withStatus(string ): self',
    'public function withStatus(string $status): self',
    $content
);

$content = str_replace(
    'return ->where(\'status\', );',
    'return $this->where(\'status\', $status);',
    $content
);

$content = str_replace(
    'public function involvingActor(int ): self',
    'public function involvingActor(int $actorId): self',
    $content
);

$content = str_replace(
    'return ->whereHas(\'actors\', function (Builder ) use () {',
    'return $this->whereHas(\'actors\', function (Builder $query) use ($actorId) {',
    $content
);

$content = str_replace(
    '->where(\'actors.id\', );',
    '$query->where(\'actors.id\', $actorId);',
    $content
);

$content = str_replace(
    'public function relatedToConflict(int ): self',
    'public function relatedToConflict(int $conflictId): self',
    $content
);

$content = str_replace(
    'return ->where(\'conflict_id\', );',
    'return $this->where(\'conflict_id\', $conflictId);',
    $content
);

$content = str_replace(
    'public function withMinimumConfidence(int ): self',
    'public function withMinimumConfidence(int $confidenceScore): self',
    $content
);

$content = str_replace(
    'return ->where(\'confidence_score\', \'>=\', );',
    'return $this->where(\'confidence_score\', \'>=\', $confidenceScore);',
    $content
);

file_put_contents($file, $content);
echo "Patched EventQueryBuilder.php\n";
