<?php

namespace Tests\Unit\Services;

use App\Models\Event;
use App\Services\GeolocationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GeolocationServiceTest extends TestCase
{
    use RefreshDatabase;

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

    /**
     * Test matching satellite imagery with valid inputs.
     */
    public function test_match_satellite_imagery_returns_correct_structure_for_valid_input(): void
    {
        $groundImagePath = 'path/to/image.jpg';
        // Region: [sw_lat, sw_lng, ne_lat, ne_lng]
        $region = [10.0, 20.0, 11.0, 21.0];
        $options = ['provider' => 'sentinel-2'];

        $result = $this->service->matchSatelliteImagery($groundImagePath, $region, $options);

        $this->assertIsArray($result);
        $this->assertEquals('satellite_match', $result['analysis_type']);
        $this->assertEquals($groundImagePath, $result['ground_image']);

        // Check center calculation
        // Center lat: (10 + 11) / 2 = 10.5
        // Center lng: (20 + 21) / 2 = 20.5
        $this->assertEquals(10.5, $result['search_region']['center']['lat']);
        $this->assertEquals(20.5, $result['search_region']['center']['lng']);

        // Check options pass-through
        $this->assertEquals($options, $result['options']);

        // Check status
        $this->assertEquals('ready_for_processing', $result['status']);
    }

    /**
     * Test matching satellite imagery throws exception for invalid region.
     */
    public function test_match_satellite_imagery_throws_exception_for_invalid_region_count(): void
    {
        $groundImagePath = 'path/to/image.jpg';
        $region = [10.0, 20.0, 11.0]; // Missing one coordinate

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Region must contain [sw_lat, sw_lng, ne_lat, ne_lng]');

        $this->service->matchSatelliteImagery($groundImagePath, $region);
    }

    /**
     * Test matching satellite imagery calculates area.
     */
    public function test_match_satellite_imagery_calculates_area(): void
    {
        $groundImagePath = 'path/to/image.jpg';
        $region = [0.0, 0.0, 1.0, 1.0]; // 1 degree square at equator

        $result = $this->service->matchSatelliteImagery($groundImagePath, $region);

        $this->assertArrayHasKey('search_area_km2', $result);
        $this->assertIsFloat($result['search_area_km2']);
        $this->assertGreaterThan(0, $result['search_area_km2']);

        // 1 degree at equator is approx 111km. 111 * 111 = ~12321 km2
        $this->assertGreaterThan(12000, $result['search_area_km2']);
        $this->assertLessThan(13000, $result['search_area_km2']);
    }

    /** @test */
    public function verify_coordinates_throws_exception_for_invalid_method()
    {
        // Create an event so findOrFail succeeds
        $event = Event::factory()->create([
            'latitude' => 10.0,
            'longitude' => 20.0,
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid verification method: invalid_method');

        $this->service->verifyCoordinates(
            $event->id,
            'invalid_method',
            ['verified_latitude' => 10.001, 'verified_longitude' => 20.001]
        );
    }
}
