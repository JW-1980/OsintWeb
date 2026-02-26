<?php

namespace Tests\Unit\Services;

use App\Services\GeolocationService;
use Tests\TestCase;

class GeolocationServiceTest extends TestCase
{
    /**
     * Test matching satellite imagery with valid inputs.
     */
    public function test_match_satellite_imagery_returns_correct_structure_for_valid_input(): void
    {
        $service = new GeolocationService();
        $groundImagePath = 'path/to/image.jpg';
        // Region: [sw_lat, sw_lng, ne_lat, ne_lng]
        $region = [10.0, 20.0, 11.0, 21.0];
        $options = ['provider' => 'sentinel-2'];

        $result = $service->matchSatelliteImagery($groundImagePath, $region, $options);

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
        $service = new GeolocationService();
        $groundImagePath = 'path/to/image.jpg';
        $region = [10.0, 20.0, 11.0]; // Missing one coordinate

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Region must contain [sw_lat, sw_lng, ne_lat, ne_lng]');

        $service->matchSatelliteImagery($groundImagePath, $region);
    }

    /**
     * Test matching satellite imagery calculates area.
     */
    public function test_match_satellite_imagery_calculates_area(): void
    {
        $service = new GeolocationService();
        $groundImagePath = 'path/to/image.jpg';
        $region = [0.0, 0.0, 1.0, 1.0]; // 1 degree square at equator

        $result = $service->matchSatelliteImagery($groundImagePath, $region);

        $this->assertArrayHasKey('search_area_km2', $result);
        $this->assertIsFloat($result['search_area_km2']);
        $this->assertGreaterThan(0, $result['search_area_km2']);

        // 1 degree at equator is approx 111km. 111 * 111 = ~12321 km2
        $this->assertGreaterThan(12000, $result['search_area_km2']);
        $this->assertLessThan(13000, $result['search_area_km2']);
    }
}
