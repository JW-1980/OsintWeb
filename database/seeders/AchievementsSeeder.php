<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AchievementsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $achievements = [
            // Comment Achievements
            [
                'name' => 'First Voice',
                'slug' => 'first-comment',
                'description' => 'Posted your first comment.',
                'icon' => 'chat-bubble-left',
                'color' => '#CD7F32', // Bronze
                'points' => 10,
                'threshold' => 1,
                'type' => 'comment_create',
                'is_secret' => false,
            ],
            [
                'name' => 'Conversationalist',
                'slug' => '10-comments',
                'description' => 'Posted 10 comments.',
                'icon' => 'chat-bubble-left-ellipsis',
                'color' => '#C0C0C0', // Silver
                'points' => 50,
                'threshold' => 10,
                'type' => 'comment_create',
                'is_secret' => false,
            ],
            [
                'name' => 'Opinion Leader',
                'slug' => '50-comments',
                'description' => 'Posted 50 comments.',
                'icon' => 'chat-bubble-oval-left-ellipsis',
                'color' => '#FFD700', // Gold
                'points' => 200,
                'threshold' => 50,
                'type' => 'comment_create',
                'is_secret' => false,
            ],

            // Engagement Achievements
            [
                'name' => 'Avid Reader',
                'slug' => '10-articles',
                'description' => 'Viewed 10 different articles.',
                'icon' => 'book-open',
                'color' => '#CD7F32',
                'points' => 20,
                'threshold' => 10,
                'type' => 'article_view',
                'is_secret' => false,
            ],
            [
                'name' => 'Knowledge Seeker',
                'slug' => '50-articles',
                'description' => 'Viewed 50 different articles.',
                'icon' => 'library',
                'color' => '#C0C0C0',
                'points' => 100,
                'threshold' => 50,
                'type' => 'article_view',
                'is_secret' => false,
            ],

            // Onboarding
            [
                'name' => 'Ready for Duty',
                'slug' => 'onboarding-complete',
                'description' => 'Completed the onboarding process.',
                'icon' => 'academic-cap',
                'color' => '#00FF00',
                'points' => 50,
                'threshold' => 1,
                'type' => 'onboarding_complete',
                'is_secret' => false,
            ],

            // Login Streak (Conceptual - handled via logic)
            [
                'name' => 'Dedicated Analyst',
                'slug' => '5-logins',
                'description' => 'Logged in 5 times.',
                'icon' => 'clock',
                'color' => '#CD7F32',
                'points' => 25,
                'threshold' => 5,
                'type' => 'login',
                'is_secret' => false,
            ],
        ];

        foreach ($achievements as $achievement) {
            DB::table('achievements')->updateOrInsert(
                ['slug' => $achievement['slug']],
                array_merge($achievement, [
                    'created_at' => now(),
                    'updated_at' => now(),
                    'is_active' => true,
                ])
            );
        }
    }
}
