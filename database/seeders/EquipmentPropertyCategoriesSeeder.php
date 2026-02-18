<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EquipmentPropertyCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Performance',
                'slug' => 'performance',
                'description' => 'Speed, range, and performance characteristics',
                'sort_order' => 1,
                'icon' => 'gauge',
                'is_active' => true,
            ],
            [
                'name' => 'Dimensions',
                'slug' => 'dimensions',
                'description' => 'Physical dimensions and measurements',
                'sort_order' => 2,
                'icon' => 'ruler',
                'is_active' => true,
            ],
            [
                'name' => 'Armament',
                'slug' => 'armament',
                'description' => 'Weapons and munitions capabilities',
                'sort_order' => 3,
                'icon' => 'crosshairs',
                'is_active' => true,
            ],
            [
                'name' => 'Protection',
                'slug' => 'protection',
                'description' => 'Armor, defense systems, and countermeasures',
                'sort_order' => 4,
                'icon' => 'shield',
                'is_active' => true,
            ],
            [
                'name' => 'Electronics',
                'slug' => 'electronics',
                'description' => 'Sensors, radar, communications, and electronics',
                'sort_order' => 5,
                'icon' => 'satellite-dish',
                'is_active' => true,
            ],
            [
                'name' => 'Propulsion',
                'slug' => 'propulsion',
                'description' => 'Engine, power plant, and propulsion details',
                'sort_order' => 6,
                'icon' => 'cog',
                'is_active' => true,
            ],
            [
                'name' => 'Crew',
                'slug' => 'crew',
                'description' => 'Crew requirements and capacity',
                'sort_order' => 7,
                'icon' => 'users',
                'is_active' => true,
            ],
            [
                'name' => 'History',
                'slug' => 'history',
                'description' => 'Development and operational history',
                'sort_order' => 8,
                'icon' => 'history',
                'is_active' => true,
            ],
            [
                'name' => 'Variants',
                'slug' => 'variants',
                'description' => 'Equipment variants and modifications',
                'sort_order' => 9,
                'icon' => 'code-branch',
                'is_active' => true,
            ],
            [
                'name' => 'Combat Record',
                'slug' => 'combat-record',
                'description' => 'Combat deployments and operational use',
                'sort_order' => 10,
                'icon' => 'medal',
                'is_active' => true,
            ],
            [
                'name' => 'External Resources',
                'slug' => 'external-resources',
                'description' => 'Links to external documentation and resources',
                'sort_order' => 11,
                'icon' => 'link',
                'is_active' => true,
            ],
            [
                'name' => 'Media',
                'slug' => 'media',
                'description' => 'Images, videos, and other media',
                'sort_order' => 12,
                'icon' => 'image',
                'is_active' => true,
            ],
        ];

        $now = now();

        foreach ($categories as $category) {
            DB::table('equipment_property_categories')->insertOrIgnore([
                ...$category,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
