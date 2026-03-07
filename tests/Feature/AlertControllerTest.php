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

    /**
     * Test marking all unread notifications as read.
     */
    public function test_mark_all_read_updates_notifications(): void
    {
        $user = User::factory()->create();

        // Create unread notifications for the user
        DB::table('notifications')->insert([
            [
                'id' => Str::uuid(),
                'type' => 'App\Notifications\TestNotification',
                'notifiable_type' => 'App\Models\User',
                'notifiable_id' => $user->id,
                'data' => json_encode(['message' => 'Test 1']),
                'read_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'type' => 'App\Notifications\TestNotification',
                'notifiable_type' => 'App\Models\User',
                'notifiable_id' => $user->id,
                'data' => json_encode(['message' => 'Test 2']),
                'read_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->assertEquals(2, DB::table('notifications')->where('notifiable_id', $user->id)->whereNull('read_at')->count());

        $response = $this->actingAs($user)->postJson('/api/notifications/read-all');

        $response->assertStatus(200)
            ->assertJson(['message' => 'All notifications marked as read']);

        $this->assertEquals(0, DB::table('notifications')->where('notifiable_id', $user->id)->whereNull('read_at')->count());
    }

    /**
     * Test marking all read only affects the current user.
     */
    public function test_mark_all_read_only_affects_current_user(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Create unread notifications for both users
        DB::table('notifications')->insert([
            [
                'id' => Str::uuid(),
                'type' => 'App\Notifications\TestNotification',
                'notifiable_type' => 'App\Models\User',
                'notifiable_id' => $user1->id,
                'data' => json_encode(['message' => 'User 1 Notification']),
                'read_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'type' => 'App\Notifications\TestNotification',
                'notifiable_type' => 'App\Models\User',
                'notifiable_id' => $user2->id,
                'data' => json_encode(['message' => 'User 2 Notification']),
                'read_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Authenticate as user 1 and mark all read
        $this->actingAs($user1)->postJson('/api/notifications/read-all');

        // Verify user 1's notification is read
        $this->assertEquals(0, DB::table('notifications')->where('notifiable_id', $user1->id)->whereNull('read_at')->count());

        // Verify user 2's notification is STILL unread
        $this->assertEquals(1, DB::table('notifications')->where('notifiable_id', $user2->id)->whereNull('read_at')->count());
    }
}
