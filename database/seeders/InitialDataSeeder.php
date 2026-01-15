<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class InitialDataSeeder extends Seeder
{
    /**
     * Seed the application's database with initial data.
     *
     * This seeder is called by the installation wizard and install command.
     * It delegates to DatabaseSeeder for the actual seeding.
     */
    public function run(): void
    {
        $this->call(DatabaseSeeder::class);
    }
}
