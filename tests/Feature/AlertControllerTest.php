<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class AlertControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * Test creating a new alert with valid data.
     */
    public function test_can_create_alert_with_valid_data(): void
    {
        $user = User::factory()->create();

        $payload = [
            'name' => 'Test Alert',
            'description' => 'This is a test alert',
            'alert_type' => 'keyword',
            'conditions' => [
                'keywords' => ['test', 'alert'],
            ],
            'notification_channels' => ['email', 'in_app'],
            'frequency' => 'instant',
            'is_active' => true,
        ];

        $response = $this->actingAs($user)
            ->postJson('/api/alerts', $payload);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'uuid',
                    'name',
                    'alert_type',
                    'conditions',
                    'notification_channels',
                    'frequency',
                    'is_active',
                    'created_at',
                    'updated_at',
                ],
            ]);

        $this->assertDatabaseHas('alerts', [
            'name' => 'Test Alert',
            'user_id' => $user->id,
            'alert_type' => 'keyword',
            'frequency' => 'instant',
        ]);
    }

    /**
     * Test validation failure for missing required fields.
     */
    public function test_alert_creation_requires_name(): void
    {
        $user = User::factory()->create();

        $payload = [
            // 'name' is missing
            'alert_type' => 'keyword',
            'conditions' => ['keywords' => ['test']],
            'notification_channels' => ['email'],
            'frequency' => 'instant',
        ];

        $response = $this->actingAs($user)
            ->postJson('/api/alerts', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /**
     * Test validation failure for invalid enum values.
     */
    public function test_alert_creation_validates_enums(): void
    {
        $user = User::factory()->create();

        $payload = [
            'name' => 'Invalid Alert',
            'alert_type' => 'invalid_type', // Invalid
            'conditions' => [],
            'notification_channels' => ['invalid_channel'], // Invalid
            'frequency' => 'yearly', // Invalid
        ];

        $response = $this->actingAs($user)
            ->postJson('/api/alerts', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['alert_type', 'notification_channels.0', 'frequency']);
    }


    /**
     * Test showing a specific alert.
     */
    public function test_can_show_alert(): void
    {
        $user = User::factory()->create();

        $alertId = DB::table('alerts')->insertGetId([
            'uuid' => Str::uuid(),
            'user_id' => $user->id,
            'name' => 'Test Show Alert',
            'alert_type' => 'keyword',
            'conditions' => json_encode(['keywords' => ['show']]),
            'notification_channels' => json_encode(['email']),
            'frequency' => 'instant',
            'is_active' => true,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $alert = DB::table('alerts')->where('id', $alertId)->first();

        // Add a trigger
        DB::table('alert_triggers')->insert([
            'uuid' => Str::uuid(),
            'alert_id' => $alertId,
            'triggered_at' => now(),
            'trigger_data' => json_encode(['matched' => 'show']),
            'notification_sent' => true,
            'created_at' => now(),
        ]);

        $response = $this->actingAs($user)
            ->getJson('/api/alerts/' . $alert->uuid);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'uuid',
                    'name',
                    'alert_type',
                    'conditions',
                    'notification_channels',
                    'frequency',
                    'is_active',
                    'status',
                    'created_at',
                    'updated_at',
                ],
                'triggers' => [
                    '*' => [
                        'id',
                        'uuid',
                        'alert_id',
                        'triggered_at',
                        'trigger_data',
                        'notification_sent',
                        'created_at',
                    ]
                ]
            ]);
    }

    /**
     * Test showing an alert returns 404 for nonexistent or unauthorized.
     */
    public function test_show_alert_returns_404_for_unauthorized_or_nonexistent(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $alertId = DB::table('alerts')->insertGetId([
            'uuid' => Str::uuid(),
            'user_id' => $user1->id,
            'name' => 'User 1 Alert',
            'alert_type' => 'keyword',
            'conditions' => json_encode(['keywords' => ['test']]),
            'notification_channels' => json_encode(['email']),
            'frequency' => 'instant',
            'is_active' => true,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $alert = DB::table('alerts')->where('id', $alertId)->first();

        // User 2 trying to access User 1's alert
        $response = $this->actingAs($user2)
            ->getJson('/api/alerts/' . $alert->uuid);

        $response->assertStatus(404)
            ->assertJson(['message' => 'Alert not found']);

        // Nonexistent UUID
        $response = $this->actingAs($user1)
            ->getJson('/api/alerts/' . (string) Str::uuid());

        $response->assertStatus(404)
            ->assertJson(['message' => 'Alert not found']);
    }
}
