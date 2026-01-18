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

            // Users (Admins, Analysts, etc.)
            UserSeeder::class,

            // Base data - Countries (required by Actors and MilitaryEquipment)
            CountriesSeeder::class,

            // Verified Information Sources (requires Countries)
            SourcesSeeder::class,

            // Actors (countries, groups, organizations)
            ActorsSeeder::class,

            // Conflicts (requires Actors)
            ConflictsSeeder::class,

            // Event Types (required by Events)
            EventTypesSeeder::class,

            // Equipment property categories
            EquipmentPropertyCategoriesSeeder::class,

            // Military equipment categories and items
            MilitaryEquipmentSeeder::class,
            ExtendedEquipmentSeeder::class,
            AdditionalEquipmentSeeder::class,
            ExtensiveEquipmentSeeder::class,
            FinalEquipmentSeeder::class,

            // Extended Conflicts and Non-State Actors
            ConflictSeeder::class,
            ActorSeeder::class,

            // Events and Control Zones
            EventSeeder::class,
            ZoneSeeder::class,

            // Crowdsourced Intelligence (sample tips)
            TipSeeder::class,

            // OSINT Skills and Intelligence Agents
            OsintSkillsSeeder::class,
            IntelligenceAgentsSeeder::class,
            SkillSeeder::class,

            // Articles & Comments
            ArticleCategoriesSeeder::class,
            SpamPatternsSeeder::class,

            // Achievements
            AchievementsSeeder::class,

            // Disinformation Detection Patterns
            DisinformationPatternsSeeder::class,

            // SITREP Report Templates
            SitrepTemplatesSeeder::class,

            // Training Environments and Scenarios
            TrainingEnvironmentsSeeder::class,
        ]);
    }
}
