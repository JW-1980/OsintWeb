<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class EventTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test successful event creation with valid data.
     *
     * @return void
     */
    public function test_can_create_event_with_valid_data()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $data = [
            'title' => 'Test Event',
            'description' => 'This is a test event description.',
            'type' => 'conflict',
            'occurred_at' => now()->toIso8601String(),
            'latitude' => 45.0,
            'longitude' => -73.0,
            'location_name' => 'Test Location',
            'status' => 'draft',
            'confidence' => 'confirmed',
            'custom_fields' => ['key' => 'value'],
        ];

        $response = $this->postJson('/api/events', $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'uuid',
                    'title',
                    'description',
                    'latitude',
                    'longitude',
                    'status',
                    'created_by',
                ]
            ]);

        $this->assertDatabaseHas('events', [
            'title' => 'Test Event',
            'latitude' => 45.0,
            'longitude' => -73.0,
            'created_by' => $user->id,
        ]);
    }

    /**
     * Test event creation validation failures.
     *
     * @return void
     */
    public function test_event_creation_validation_failures()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        // Test missing required fields
        $response = $this->postJson('/api/events', []);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'description', 'type', 'occurred_at', 'latitude', 'longitude', 'status', 'confidence']);

        // Test invalid data types
        $response = $this->postJson('/api/events', [
            'title' => 123, // Should be string
            'latitude' => 'invalid', // Should be numeric
        ]);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'latitude']);

        // Test constraints (e.g., max length)
        $response = $this->postJson('/api/events', [
            'title' => str_repeat('a', 256), // Max 255
        ]);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);

        // Test invalid enum values
        $response = $this->postJson('/api/events', [
            'status' => 'invalid_status',
            'confidence' => 'invalid_confidence',
        ]);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status', 'confidence']);
    }

    /**
     * Test authentication requirement for event creation.
     *
     * @return void
     */
    public function test_unauthenticated_user_cannot_create_event()
    {
        $response = $this->postJson('/api/events', []);
        $response->assertStatus(401); // Unauthorized
    }
}
