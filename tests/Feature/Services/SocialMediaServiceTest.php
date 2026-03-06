<?php

namespace Tests\Feature\Services;

use App\Models\SocialMediaAccount;
use App\Models\SocialMediaPost;
use App\Models\SocialMediaAlert;
use App\Models\User;
use App\Services\SocialMediaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class SocialMediaServiceTest extends TestCase
{
    use RefreshDatabase;

    private SocialMediaService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SocialMediaService();
    }

    /**
     * Test getOverallStats returns correct empty structure when no data exists.
     */
    public function test_get_overall_stats_empty(): void
    {
        $stats = $this->service->getOverallStats();

        $this->assertEquals([
            'accounts' => [
                'total' => 0,
                'monitored' => 0,
                'verified' => 0,
                'by_platform' => [],
            ],
            'posts' => [
                'total' => 0,
                'archived' => 0,
                'linked_to_events' => 0,
                'last_24h' => 0,
            ],
            'alerts' => [
                'total' => 0,
                'active' => 0,
                'total_triggers' => 0,
            ],
        ], $stats);
    }

    /**
     * Test getOverallStats returns correctly aggregated statistics.
     */
    public function test_get_overall_stats_populated(): void
    {
        $user = User::factory()->create();

        // Setup Accounts
        SocialMediaAccount::create([
            'uuid' => 'acc-1', 'platform' => 'twitter', 'username' => 'user1',
            'is_monitored' => true, 'is_verified' => true
        ]);
        SocialMediaAccount::create([
            'uuid' => 'acc-2', 'platform' => 'telegram', 'username' => 'user2',
            'is_monitored' => false, 'is_verified' => false
        ]);
        SocialMediaAccount::create([
            'uuid' => 'acc-3', 'platform' => 'twitter', 'username' => 'user3',
            'is_monitored' => true, 'is_verified' => false
        ]);
        // Soft deleted account
        $deletedAccount = SocialMediaAccount::create([
            'uuid' => 'acc-4', 'platform' => 'facebook', 'username' => 'user4',
            'is_monitored' => true, 'is_verified' => true
        ]);
        $deletedAccount->delete();

        // Setup Posts
        SocialMediaPost::create([
            'uuid' => 'post-1', 'account_id' => 1, 'platform_post_id' => 'p1',
            'is_archived' => true, 'event_id' => 1, 'created_at' => now()
        ]);
        SocialMediaPost::create([
            'uuid' => 'post-2', 'account_id' => 2, 'platform_post_id' => 'p2',
            'is_archived' => false, 'event_id' => null, 'created_at' => now()->subHours(12)
        ]);
        SocialMediaPost::create([
            'uuid' => 'post-3', 'account_id' => 1, 'platform_post_id' => 'p3',
            'is_archived' => true, 'event_id' => null, 'created_at' => now()->subDays(2)
        ]);
        // Soft deleted post
        $deletedPost = SocialMediaPost::create([
            'uuid' => 'post-4', 'account_id' => 1, 'platform_post_id' => 'p4',
            'is_archived' => true, 'event_id' => 2, 'created_at' => now()
        ]);
        $deletedPost->delete();

        // Setup Alerts
        SocialMediaAlert::create([
            'uuid' => 'alert-1', 'name' => 'Alert 1', 'alert_type' => 'keyword',
            'is_active' => true, 'trigger_count' => 5, 'user_id' => $user->id
        ]);
        SocialMediaAlert::create([
            'uuid' => 'alert-2', 'name' => 'Alert 2', 'alert_type' => 'account',
            'is_active' => false, 'trigger_count' => 2, 'user_id' => $user->id
        ]);

        $stats = $this->service->getOverallStats();

        $this->assertEquals([
            'accounts' => [
                'total' => 3, // 4 created, 1 deleted
                'monitored' => 2,
                'verified' => 1,
                'by_platform' => [
                    'twitter' => 2,
                    'telegram' => 1,
                ],
            ],
            'posts' => [
                'total' => 3, // 4 created, 1 deleted
                'archived' => 2,
                'linked_to_events' => 1,
                'last_24h' => 2, // post-1, post-2
            ],
            'alerts' => [
                'total' => 2,
                'active' => 1,
                'total_triggers' => 7, // 5 + 2
            ],
        ], $stats);
    }

    /**
     * Test getAccountsNeedingCheck retrieves accounts correctly.
     */
    public function test_get_accounts_needing_check(): void
    {
        // 1. Account that has never been checked (should be included)
        SocialMediaAccount::create([
            'uuid' => 'acc-1',
            'platform' => 'twitter',
            'username' => 'user1',
            'is_monitored' => true,
            'monitoring_priority' => 3,
            'last_checked_at' => null,
        ]);

        // 2. Account checked long ago (should be included)
        SocialMediaAccount::create([
            'uuid' => 'acc-2',
            'platform' => 'twitter',
            'username' => 'user2',
            'is_monitored' => true,
            'monitoring_priority' => 2,
            'last_checked_at' => Carbon::now()->subMinutes(120),
        ]);

        // 3. Account checked recently (should be excluded)
        SocialMediaAccount::create([
            'uuid' => 'acc-3',
            'platform' => 'twitter',
            'username' => 'user3',
            'is_monitored' => true,
            'monitoring_priority' => 1,
            'last_checked_at' => Carbon::now()->subMinutes(30),
        ]);

        // 4. Account not monitored (should be excluded)
        SocialMediaAccount::create([
            'uuid' => 'acc-4',
            'platform' => 'twitter',
            'username' => 'user4',
            'is_monitored' => false,
            'monitoring_priority' => 1,
            'last_checked_at' => null,
        ]);

        // Call the service method (older than 60 minutes)
        $results = $this->service->getAccountsNeedingCheck(10, 60);

        // Assertions
        $this->assertCount(2, $results);

        // Check ordering: priority (2 then 3), then last_checked_at
        $this->assertEquals('user2', $results[0]->username); // Priority 2
        $this->assertEquals('user1', $results[1]->username); // Priority 3
    }

    /**
     * Test getAccountsNeedingCheck respects the limit parameter.
     */
    public function test_get_accounts_needing_check_respects_limit(): void
    {
        // Create 5 accounts needing check
        for ($i = 1; $i <= 5; $i++) {
            SocialMediaAccount::create([
                'uuid' => "acc-$i",
                'platform' => 'twitter',
                'username' => "user$i",
                'is_monitored' => true,
                'monitoring_priority' => 3,
                'last_checked_at' => null,
            ]);
        }

        $results = $this->service->getAccountsNeedingCheck(3, 60);

        $this->assertCount(3, $results);
    }

    /**
     * Test getAccountsNeedingCheck respects custom olderThanMinutes.
     */
    public function test_get_accounts_needing_check_respects_custom_threshold(): void
    {
        // Checked 45 minutes ago
        SocialMediaAccount::create([
            'uuid' => 'acc-1',
            'platform' => 'twitter',
            'username' => 'user1',
            'is_monitored' => true,
            'monitoring_priority' => 3,
            'last_checked_at' => Carbon::now()->subMinutes(45),
        ]);

        // With default 60 mins, should return 0
        $this->assertCount(0, $this->service->getAccountsNeedingCheck(10, 60));

        // With 30 mins, should return 1
        $this->assertCount(1, $this->service->getAccountsNeedingCheck(10, 30));
    }
}
