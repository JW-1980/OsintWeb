<?php

namespace Tests\Unit\Services;

use App\Services\GeolocationService;
use Carbon\Carbon;
use Tests\TestCase;

class GeolocationServiceTest extends TestCase
{
    /**
     * @var GeolocationService
     */
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new GeolocationService();
    }

    /**
     * Test calculateSunPosition with known data points.
     *
     * @dataProvider sunPositionProvider
     */
    public function test_calculate_sun_position($latitude, $longitude, $dateTime, $expectedAltitude, $expectedAzimuth, $tolerance)
    {
        $result = $this->service->calculateSunPosition($latitude, $longitude, $dateTime);

        $this->assertEqualsWithDelta($expectedAltitude, $result['altitude'], $tolerance, "Altitude mismatch");
        $this->assertEqualsWithDelta($expectedAzimuth, $result['azimuth'], $tolerance, "Azimuth mismatch");
    }

    public static function sunPositionProvider()
    {
        return [
            'London Summer Solstice Noon' => [
                51.5074,
                -0.1278,
                Carbon::create(2023, 6, 21, 12, 0, 0, 'UTC'),
                61.93, // Expected Altitude
                178.88, // Expected Azimuth
                1.0 // Tolerance
            ],
            'Equator Equinox Noon' => [
                0.0,
                0.0,
                Carbon::create(2023, 3, 21, 12, 0, 0, 'UTC'),
                88.17, // Expected Altitude
                82.3, // Expected Azimuth
                5.0 // Higher tolerance
            ],
            'Sydney Winter Solstice Noon' => [
                -33.8688,
                151.2093,
                Carbon::create(2023, 6, 21, 2, 0, 0, 'UTC'), // ~Noon Sydney time
                32.5, // Winter sun is lower
                355.0, // North facing
                2.0
            ]
        ];
    }
}
