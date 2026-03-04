<?php

declare(strict_types=1);

namespace App\Traits;

/**
 * GeoDistance Trait
 *
 * Provides methods for calculating geographic distances.
 */
trait GeoDistanceTrait
{
    /**
     * Haversine distance calculation.
     *
     * @param float $lat1
     * @param float $lng1
     * @param float $lat2
     * @param float $lng2
     * @param float $earthRadius Earth's radius in desired units (default: meters)
     * @return float Distance in the same units as the radius
     */
    public function haversineDistance(float $lat1, float $lng1, float $lat2, float $lng2, float $earthRadius = 6371000): float
    {
        $lat1Rad = deg2rad($lat1);
        $lat2Rad = deg2rad($lat2);
        $deltaLat = deg2rad($lat2 - $lat1);
        $deltaLng = deg2rad($lng2 - $lng1);

        $a = sin($deltaLat / 2) ** 2 +
            cos($lat1Rad) * cos($lat2Rad) *
            sin($deltaLng / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
