<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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
     * Test successful alert deletion.
     */
    public function test_can_delete_alert(): void
    {
        $user = User::factory()->create();

        $uuid = Str::uuid()->toString();
        $alertId = DB::table('alerts')->insertGetId([
            'uuid' => $uuid,
            'user_id' => $user->id,
            'name' => 'Alert to Delete',
            'alert_type' => 'keyword',
            'conditions' => json_encode(['keywords' => ['delete']]),
            'notification_channels' => json_encode(['email']),
            'frequency' => 'instant',
            'is_active' => true,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($user)
            ->deleteJson("/api/alerts/{$uuid}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Alert deleted successfully']);

        $this->assertDatabaseMissing('alerts', ['id' => $alertId]);
    }

    /**
     * Test deletion of non-existent alert.
     */
    public function test_delete_alert_not_found(): void
    {
        $user = User::factory()->create();
        $uuid = Str::uuid()->toString();

        $response = $this->actingAs($user)
            ->deleteJson("/api/alerts/{$uuid}");

        $response->assertStatus(404)
            ->assertJson(['message' => 'Alert not found']);
    }

    /**
     * Test cannot delete another user's alert.
     */
    public function test_cannot_delete_other_users_alert(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $uuid = Str::uuid()->toString();
        $alertId = DB::table('alerts')->insertGetId([
            'uuid' => $uuid,
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

        $response = $this->actingAs($user2)
            ->deleteJson("/api/alerts/{$uuid}");

        $response->assertStatus(404)
            ->assertJson(['message' => 'Alert not found']);

        $this->assertDatabaseHas('alerts', ['id' => $alertId]);
    }
}
