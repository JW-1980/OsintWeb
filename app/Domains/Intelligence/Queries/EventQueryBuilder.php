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
    public function nearLocation(float , float , float ): self
    {
        return ->whereRaw(
            'ST_Distance_Sphere(location, POINT(?, ?)) <= ?',
            [, ,  * 1000]
        );
    }

    /**
     * Scope to filter by date range.
     *
     * @param string
     * @param string
     * @return self
     */
    public function dateRange(string , string ): self
    {
        return ->whereBetween('occurred_at', [, ]);
    }

    /**
     * Scope to filter by status.
     *
     * @param string
     * @return self
     */
    public function withStatus(string ): self
    {
        return ->where('status', );
    }
}
