<?php

namespace Tests\Feature\Services;

use App\Models\EntityVersion;
use App\Models\User;
use App\Services\EntityRollbackService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
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
}
