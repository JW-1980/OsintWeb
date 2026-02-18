<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ArticleCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Breaking News',
                'slug' => 'breaking-news',
                'description' => 'Latest breaking news and urgent updates',
                'color' => '#EF4444',
                'icon' => 'bell-alert',
                'sort_order' => 1,
            ],
            [
                'name' => 'Conflict Analysis',
                'slug' => 'conflict-analysis',
                'description' => 'In-depth analysis of ongoing conflicts',
                'color' => '#F97316',
                'icon' => 'chart-bar',
                'sort_order' => 2,
            ],
            [
                'name' => 'Equipment & Technology',
                'slug' => 'equipment-technology',
                'description' => 'Military equipment, weapons systems, and technology',
                'color' => '#3B82F6',
                'icon' => 'cog',
                'sort_order' => 3,
            ],
            [
                'name' => 'OSINT Techniques',
                'slug' => 'osint-techniques',
                'description' => 'Open source intelligence methods and tutorials',
                'color' => '#8B5CF6',
                'icon' => 'academic-cap',
                'sort_order' => 4,
            ],
            [
                'name' => 'Geopolitics',
                'slug' => 'geopolitics',
                'description' => 'Geopolitical developments and international relations',
                'color' => '#10B981',
                'icon' => 'globe-alt',
                'sort_order' => 5,
            ],
            [
                'name' => 'Investigative Reports',
                'slug' => 'investigative-reports',
                'description' => 'Deep-dive investigative journalism and reports',
                'color' => '#6366F1',
                'icon' => 'document-magnifying-glass',
                'sort_order' => 6,
            ],
            [
                'name' => 'Humanitarian',
                'slug' => 'humanitarian',
                'description' => 'Humanitarian situations, refugees, and civilian impact',
                'color' => '#EC4899',
                'icon' => 'heart',
                'sort_order' => 7,
            ],
            [
                'name' => 'Cyber & Information Warfare',
                'slug' => 'cyber-information-warfare',
                'description' => 'Cyber attacks, disinformation, and information operations',
                'color' => '#14B8A6',
                'icon' => 'shield-exclamation',
                'sort_order' => 8,
            ],
            [
                'name' => 'Opinion & Commentary',
                'slug' => 'opinion-commentary',
                'description' => 'Expert opinions and editorial commentary',
                'color' => '#F59E0B',
                'icon' => 'chat-bubble-left-right',
                'sort_order' => 9,
            ],
            [
                'name' => 'Regional Focus',
                'slug' => 'regional-focus',
                'description' => 'Region-specific news and analysis',
                'color' => '#84CC16',
                'icon' => 'map',
                'sort_order' => 10,
            ],
        ];

        $now = now();

        foreach ($categories as $category) {
            DB::table('article_categories')->insertOrIgnore([
                'uuid' => Str::uuid(),
                ...$category,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
