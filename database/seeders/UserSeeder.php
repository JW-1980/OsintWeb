<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Admin User
        // Note: Password hashing is handled automatically by the User model's 'hashed' cast.
        // We do not need to use Hash::make() here.
        $admin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'id' => 1,
                'name' => 'John Admin',
                'password' => 'KCmGnvvA8H0Eq4SaQ&TkaW!Y',
                'role' => 'admin',
                'is_active' => true,
                'is_verified' => true,
                'email_verified_at' => now(),
            ]
        );

        $adminRole = Role::where('slug', 'admin')->first();
        if ($adminRole) {
             $admin->assignRole($adminRole);
        }

        // 2. Analyst User
        $analyst = User::updateOrCreate(
            ['email' => 'analyst@example.com'],
            [
                'id' => 2,
                'name' => 'Jane Analyst',
                'password' => 'password',
                'role' => 'analyst',
                'is_active' => true,
                'is_verified' => true,
                'email_verified_at' => now(),
            ]
        );

        $analystRole = Role::where('slug', 'analyst')->first();
        if ($analystRole) {
            $analyst->assignRole($analystRole);
        }
    }
}
