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

            // Base data - Countries (required by Actors and MilitaryEquipment)
            CountriesSeeder::class,

            // Actors (countries, groups, organizations)
            ActorsSeeder::class,

            // Conflicts (requires Actors)
            ConflictsSeeder::class,

            // Equipment property categories
            EquipmentPropertyCategoriesSeeder::class,

            // Military equipment categories and items
            MilitaryEquipmentSeeder::class,
            ExtendedEquipmentSeeder::class,

            // OSINT Skills and Intelligence Agents
            OsintSkillsSeeder::class,
            IntelligenceAgentsSeeder::class,
            SkillSeeder::class,

            // Articles & Comments
            ArticleCategoriesSeeder::class,
            SpamPatternsSeeder::class,

            // Achievements
            AchievementsSeeder::class,
        ]);
    }
}
