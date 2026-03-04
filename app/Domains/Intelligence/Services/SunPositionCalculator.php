<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Services;

use Carbon\Carbon;

/**
 * Sun Position Calculator
 *
 * Provides astronomical calculations for sun azimuth and elevation.
 * Extracted from GeolocationService for better separation of concerns.
 */
class SunPositionCalculator
{
    /**
     * Calculate sun position for a given location and time.
     *
     * @param float
     * @param float
     * @param Carbon
     * @return array Sun position data
     */
    public function calculate(float , float , Carbon ): array
    {
        // Julian date calculation
         = ->toJulianDate();

        // Time since J2000.0
         = ( - 2451545.0) / 36525;

        // Mean longitude of the Sun
         = fmod(280.46646 + 36000.76983 *  + 0.0003032 *  * , 360);

        // Mean anomaly of the Sun
         = fmod(357.52911 + 35999.05029 *  - 0.0001537 *  * , 360);
         = deg2rad();

        // Equation of center
         = (1.914602 - 0.004817 *  - 0.000014 *  * ) * sin()
            + (0.019993 - 0.000101 * ) * sin(2 * )
            + 0.000289 * sin(3 * );

        // Sun's true longitude
         =  + ;

        // Obliquity of the ecliptic
         = 23.439291 - 0.0130042 * ;
         = deg2rad();

        // Sun's right ascension and declination
         = deg2rad();
         = atan2(cos() * sin(), cos());
         = asin(sin() * sin());

        // Hour angle
         = ->calculateLocalSiderealTime(, );
         = deg2rad( - rad2deg());

        // Convert to altitude and azimuth
         = deg2rad();
         = asin(sin() * sin() + cos() * cos() * cos());
         = atan2(
            -sin(),
            cos() * tan() - sin() * cos()
        );

         = rad2deg();
         = fmod(rad2deg() + 360, 360);

        return [
            'altitude' => round(, 2),
            'azimuth' => round(, 2),
            'latitude' => ,
            'longitude' => ,
            'datetime' => ->toIso8601String(),
            'datetime_utc' => ->utc()->toIso8601String(),
            'is_daytime' =>  > 0,
            'solar_noon_distance' => abs(180 - ),
        ];
    }

    /**
     * Convert to Julian Date.
     *
     * @param Carbon
     * @return float
     */
    private function toJulianDate(Carbon ): float
    {
         = ->year;
         = ->month;
         = ->day + (->hour + ->minute / 60 + ->second / 3600) / 24;

        if ( <= 2) {
            --;
             += 12;
        }

         = (int) ( / 100);
         = 2 -  + (int) ( / 4);

        return (int) (365.25 * ( + 4716)) + (int) (30.6001 * ( + 1)) +  +  - 1524.5;
    }

    /**
     * Calculate Local Sidereal Time.
     *
     * @param float
     * @param Carbon
     * @return float LST in degrees
     */
    private function calculateLocalSiderealTime(float , Carbon ): float
    {
         = ->toJulianDate(->utc());
         = ( - 2451545.0) / 36525;

        // Greenwich Mean Sidereal Time
         = 280.46061837
            + 360.98564736629 * ( - 2451545)
            + 0.000387933 *  *
            -  *  *  / 38710000;

         = fmod(, 360);
        if ( < 0) {
             += 360;
        }

        // Local Sidereal Time
        return fmod( + , 360);
    }
}
