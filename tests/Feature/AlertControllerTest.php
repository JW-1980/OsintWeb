<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
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
     * Test listing user's notifications.
     */
    public function test_can_list_notifications(): void
    {
        $user = User::factory()->create();

        // Create some notifications
        \Illuminate\Support\Facades\DB::table('notifications')->insert([
            [
                'id' => \Illuminate\Support\Str::uuid()->toString(),
                'type' => 'App\Notifications\TestNotification',
                'notifiable_type' => 'App\Models\User',
                'notifiable_id' => $user->id,
                'data' => json_encode(['message' => 'Notification 1']),
                'read_at' => null,
                'created_at' => now()->subMinutes(10),
                'updated_at' => now()->subMinutes(10),
            ],
            [
                'id' => \Illuminate\Support\Str::uuid()->toString(),
                'type' => 'App\Notifications\TestNotification',
                'notifiable_type' => 'App\Models\User',
                'notifiable_id' => $user->id,
                'data' => json_encode(['message' => 'Notification 2']),
                'read_at' => now(),
                'created_at' => now()->subMinutes(5),
                'updated_at' => now()->subMinutes(5),
            ]
        ]);

        $response = $this->actingAs($user)
            ->getJson('/api/notifications');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'type',
                        'notifiable_type',
                        'notifiable_id',
                        'data',
                        'read_at',
                        'created_at',
                        'updated_at',
                    ]
                ],
                'meta' => [
                    'current_page',
                    'total',
                    'unread_count',
                ]
            ]);

        $this->assertCount(2, $response->json('data'));
        $this->assertEquals(1, $response->json('meta.unread_count'));
    }

    /**
     * Test filtering unread notifications.
     */
    public function test_can_filter_unread_notifications(): void
    {
        $user = User::factory()->create();

        // Create some notifications
        \Illuminate\Support\Facades\DB::table('notifications')->insert([
            [
                'id' => \Illuminate\Support\Str::uuid()->toString(),
                'type' => 'App\Notifications\TestNotification',
                'notifiable_type' => 'App\Models\User',
                'notifiable_id' => $user->id,
                'data' => json_encode(['message' => 'Unread Notification']),
                'read_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => \Illuminate\Support\Str::uuid()->toString(),
                'type' => 'App\Notifications\TestNotification',
                'notifiable_type' => 'App\Models\User',
                'notifiable_id' => $user->id,
                'data' => json_encode(['message' => 'Read Notification']),
                'read_at' => now(),
                'created_at' => now()->subMinutes(5),
                'updated_at' => now()->subMinutes(5),
            ]
        ]);

        $response = $this->actingAs($user)
            ->getJson('/api/notifications?unread_only=1');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertNull($response->json('data.0.read_at'));
    }
}