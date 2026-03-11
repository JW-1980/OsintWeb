<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Queries;

use Illuminate\Database\Eloquent\Builder;

class EventQueryBuilder extends Builder
{
    /**
     * Scope to filter events within a radius of a location.
     *
     * @param float
     * @param float
     * @param float
     * @return self
     */
    public function nearLocation(float $latitude, float $longitude, float $radiusKm): self
    {
        return $this->whereRaw(
            'ST_Distance_Sphere(location, POINT(?, ?)) <= ?',
            [$longitude, $latitude, $radiusKm * 1000]
        );
    }

    /**
     * Scope to filter by date range.
     *
     * @param string
     * @param string
     * @return self
     */
    public function dateRange(string $startDate, string $endDate): self
    {
        return $this->whereBetween('occurred_at', [$startDate, $endDate]);
    }

    /**
     * Scope to filter by status.
     *
     * @param string
     * @return self
     */
    public function withStatus(string $status): self
    {
        return $this->where('status', $status);
    }
}
