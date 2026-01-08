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
            // Application settings
            SettingsSeeder::class,

            // RBAC - Permissions and Roles
            PermissionsSeeder::class,

            // Base data
            // CountriesSeeder::class, // Removed as file is missing

            // Actors (countries, groups, organizations)
            ActorsSeeder::class,

            // Equipment property categories
            EquipmentPropertyCategoriesSeeder::class,

            // Military equipment categories and items
            MilitaryEquipmentSeeder::class,

            // OSINT Skills and Intelligence Agents
            OsintSkillsSeeder::class,
            IntelligenceAgentsSeeder::class,

            // Articles & Comments
            ArticleCategoriesSeeder::class,
            SpamPatternsSeeder::class,
        ]);
    }
}
