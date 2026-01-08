<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Achievement;
use App\Models\Setting;
use App\Models\User;
use App\Models\UserActivity;
use Illuminate\Support\Facades\Log;

class AchievementService
{
    /**
     * Check and award achievements for a user based on activity type.
     */
    public function checkAchievements(User $user, string $activityType): void
    {
        // Check if achievement system is enabled
        $enabled = Setting::where('key', 'achievements_enabled')->value('value');
        if ($enabled === 'false' || $enabled === '0') {
            return;
        }

        // Get all active achievements of this type that the user hasn't earned yet
        $existingAchievementIds = $user->achievements()->pluck('achievements.id');

        $potentialAchievements = Achievement::active()
            ->ofType($activityType)
            ->whereNotIn('id', $existingAchievementIds)
            ->get();

        if ($potentialAchievements->isEmpty()) {
            return;
        }

        // Calculate metric based on type
        $count = $this->calculateMetric($user, $activityType);

        foreach ($potentialAchievements as $achievement) {
            if ($count >= $achievement->threshold) {
                $this->awardAchievement($user, $achievement);
            }
        }
    }

    /**
     * Calculate the current metric for the user and activity type.
     */
    protected function calculateMetric(User $user, string $type): int
    {
        return match ($type) {
            UserActivity::TYPE_COMMENT_CREATE => $user->comments()->count(),
            UserActivity::TYPE_ARTICLE_VIEW => $user->activities()->where('activity_type', UserActivity::TYPE_ARTICLE_VIEW)->distinct('description')->count('description'), // Assumes description holds article ID or title, approximate
            UserActivity::TYPE_ONBOARDING_COMPLETE => $user->hasCompletedOnboarding() ? 1 : 0,
            UserActivity::TYPE_LOGIN => $user->activities()->where('activity_type', UserActivity::TYPE_LOGIN)->count(),
            default => 0,
        };
    }

    /**
     * Award an achievement to a user.
     */
    public function awardAchievement(User $user, Achievement $achievement): void
    {
        $user->achievements()->attach($achievement->id, ['awarded_at' => now()]);

        // Log the award (optional, could trigger notification)
        Log::info("User {$user->id} earned achievement: {$achievement->name}");
    }
}
