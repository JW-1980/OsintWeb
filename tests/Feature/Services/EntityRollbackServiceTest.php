<?php

namespace Tests\Feature\Services;

use App\Models\EntityVersion;
use App\Models\User;
use App\Services\EntityRollbackService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use InvalidArgumentException;
use RuntimeException;
use Tests\TestCase;

class EntityRollbackServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test rollback throws InvalidArgumentException when user lacks permission
     *
     * @return void
     */
    public function test_rollback_throws_exception_when_user_lacks_permission(): void
    {
        // Create an unauthorized user (not an admin or editor)
        $unauthorizedUser = User::factory()->create([
            'role' => 'user' // Assume 'user' is the default and lacks rollback permissions for other users' models
        ]);

        // Create a model that the unauthorized user does not own
        $targetModel = User::factory()->create();

        // Create an EntityVersion for the target model
        $version = EntityVersion::create([
            'versionable_type' => User::class,
            'versionable_id' => $targetModel->id,
            'version_number' => 1,
            'version_hash' => hash('sha256', json_encode($targetModel->toArray())),
            'snapshot' => $targetModel->toArray(),
            'created_by' => $targetModel->id,
            'change_type' => 'create'
        ]);

        $service = new EntityRollbackService();

        // We expect the specific exception
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('User does not have permission to rollback this entity');

        // Attempt rollback using correct parameter order and names based on EntityRollbackService.php
        $service->rollback(
            User::class,            // type
            $targetModel->id,       // id
            1,                      // targetVersionNumber
            $unauthorizedUser,      // user
            'Test rollback'         // reason
        );
    }

    /**
     * Test rollback triggers DB rollback on exception during restore
     *
     * @return void
     */
    public function test_rollback_db_rollback_on_exception_during_restoration(): void
    {
        $adminUser = User::factory()->create([
            'role' => 'admin'
        ]);

        $targetModel = User::factory()->create();

        $version = EntityVersion::create([
            'versionable_type' => User::class,
            'versionable_id' => $targetModel->id,
            'version_number' => 1,
            'version_hash' => hash('sha256', json_encode($targetModel->toArray())),
            'snapshot' => $targetModel->toArray(),
            'created_by' => $targetModel->id,
            'change_type' => 'create'
        ]);

        $service = new EntityRollbackService();

        $initialTransactionLevel = DB::transactionLevel();

        // Throw an exception when trying to save the restored model
        Event::listen('eloquent.saving: ' . User::class, function () {
            throw new RuntimeException('Simulated failure during save');
        });

        try {
            $service->rollback(User::class, $targetModel->id, 1, $adminUser, 'Test rollback');
            $this->fail('Expected RuntimeException was not thrown');
        } catch (RuntimeException $e) {
            $this->assertEquals('Simulated failure during save', $e->getMessage());

            // Assert that the transaction level has returned to its initial state
            $this->assertEquals($initialTransactionLevel, DB::transactionLevel());
        }
    }

    /**
     * Test rollbackToTimestamp triggers DB rollback on exception during restore
     *
     * @return void
     */
    public function test_rollback_to_timestamp_db_rollback_on_exception(): void
    {
        $adminUser = User::factory()->create([
            'role' => 'admin'
        ]);

        $targetModel = User::factory()->create();
        $targetModelId = $targetModel->id;

        // Mock the PointInTimeService to return a valid state
        $pointInTimeServiceMock = \Mockery::mock(\App\Services\PointInTimeService::class);
        $pointInTimeServiceMock->shouldReceive('getEntityAt')
            ->once()
            ->andReturn($targetModel->toArray());

        // Bind the mock to the container
        $this->app->instance(\App\Services\PointInTimeService::class, $pointInTimeServiceMock);

        $service = new EntityRollbackService();

        $initialTransactionLevel = DB::transactionLevel();

        // Throw an exception when trying to save the restored model
        Event::listen('eloquent.saving: ' . User::class, function () {
            throw new RuntimeException('Simulated failure during save for timestamp rollback');
        });

        try {
            $service->rollbackToTimestamp(User::class, $targetModelId, now()->subDay()->toDateTimeString(), $adminUser, 'Test rollback to timestamp');
            $this->fail('Expected RuntimeException was not thrown');
        } catch (RuntimeException $e) {
            $this->assertEquals('Simulated failure during save for timestamp rollback', $e->getMessage());

            // Assert that the transaction level has returned to its initial state
            $this->assertEquals($initialTransactionLevel, DB::transactionLevel());
        }
    }
}
