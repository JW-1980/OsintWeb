<?php
$file = '/app/app/Domains/Intelligence/Queries/EventQueryBuilder.php';
$content = file_get_contents($file);

$content = str_replace(
    'public function nearLocation(float , float , float ): self',
    'public function nearLocation(float $latitude, float $longitude, float $radiusKm): self',
    $content
);

$content = str_replace(
    'return ->whereRaw(',
    'return $this->whereRaw(',
    $content
);

$content = str_replace(
    '[, ,  * 1000]',
    '[$longitude, $latitude, $radiusKm * 1000]',
    $content
);

$content = str_replace(
    'public function withinDateRange(string , string ): self',
    'public function withinDateRange(string $startDate, string $endDate): self',
    $content
);

$content = str_replace(
    'return ->whereBetween(\'event_date\', [, ]);',
    'return $this->whereBetween(\'event_date\', [$startDate, $endDate]);',
    $content
);

$content = str_replace(
    'public function byType(string ): self',
    'public function byType(string $type): self',
    $content
);

$content = str_replace(
    'return ->where(\'event_type_id\', );',
    'return $this->where(\'event_type_id\', $type);',
    $content
);

file_put_contents($file, $content);
echo "Patched EventQueryBuilder.php\n";
