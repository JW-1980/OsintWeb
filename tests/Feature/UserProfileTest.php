<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class UserProfileTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_user_can_update_their_profile()
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum');

        $response = $this->putJson('/api/account/profile', [
            'username' => 'newusername',
            'country' => 'Canada',
            'spoken_languages' => ['English', 'French'],
            'date_of_birth' => '1990-01-01',
            'gender' => 'male',
            'interests' => ['coding', 'hiking'],
            'skills' => ['PHP', 'Vue.js'],
            'education' => 'University of Toronto',
            'work' => 'Software Engineer',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'username' => 'newusername',
            'country' => 'Canada',
        ]);
    }
}
