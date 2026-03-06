<?php

namespace Tests\Feature\Services;

use App\Models\SocialMediaAccount;
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

    /**
     * Test getAccountsNeedingCheck ordering for same monitoring_priority.
     */
    public function test_get_accounts_needing_check_ordering_same_priority(): void
    {
        // Older checked time
        SocialMediaAccount::create([
            'uuid' => 'acc-1',
            'platform' => 'twitter',
            'username' => 'user1',
            'is_monitored' => true,
            'monitoring_priority' => 1,
            'last_checked_at' => Carbon::now()->subMinutes(120),
        ]);

        // Never checked (null last_checked_at)
        SocialMediaAccount::create([
            'uuid' => 'acc-2',
            'platform' => 'twitter',
            'username' => 'user2',
            'is_monitored' => true,
            'monitoring_priority' => 1,
            'last_checked_at' => null,
        ]);

        // Newer checked time but still needing check
        SocialMediaAccount::create([
            'uuid' => 'acc-3',
            'platform' => 'twitter',
            'username' => 'user3',
            'is_monitored' => true,
            'monitoring_priority' => 1,
            'last_checked_at' => Carbon::now()->subMinutes(90),
        ]);

        $results = $this->service->getAccountsNeedingCheck(10, 60);

        $this->assertCount(3, $results);

        // Ordering should be: null first, then oldest date
        $this->assertEquals('user2', $results[0]->username); // null
        $this->assertEquals('user1', $results[1]->username); // 120 mins ago
        $this->assertEquals('user3', $results[2]->username); // 90 mins ago
    }

    /**
     * Test getAccountsNeedingCheck returns empty when no matching accounts exist.
     */
    public function test_get_accounts_needing_check_empty(): void
    {
        $this->assertCount(0, $this->service->getAccountsNeedingCheck(10, 60));
    }

    /**
     * Test getAccountsNeedingCheck ignores exactly matching threshold.
     */
    public function test_get_accounts_needing_check_exact_threshold(): void
    {
        SocialMediaAccount::create([
            'uuid' => 'acc-1',
            'platform' => 'twitter',
            'username' => 'user1',
            'is_monitored' => true,
            'monitoring_priority' => 1,
            'last_checked_at' => Carbon::now()->subMinutes(59),
        ]);

        $this->assertCount(0, $this->service->getAccountsNeedingCheck(10, 60));
    }
}