<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // Base data
            CountriesSeeder::class,

            // Actors (countries, groups, organizations)
            ActorsSeeder::class,

            // Military equipment categories and items
            MilitaryEquipmentSeeder::class,
        ]);
    }
}
