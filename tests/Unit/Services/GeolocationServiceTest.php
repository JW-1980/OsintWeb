<?php

namespace Tests\Unit\Services;

use App\Models\Event;
use App\Services\GeolocationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GeolocationServiceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function verify_coordinates_throws_exception_for_invalid_method()
    {
        // Create an event so findOrFail succeeds
        $event = Event::factory()->create([
            'latitude' => 10.0,
            'longitude' => 20.0,
        ]);

        $service = new GeolocationService();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid verification method: invalid_method');

        $service->verifyCoordinates(
            $event->id,
            'invalid_method',
            ['verified_latitude' => 10.001, 'verified_longitude' => 20.001]
        );
    }
}
