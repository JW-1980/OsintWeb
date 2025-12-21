<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Skill;
use App\Models\SkillTrigger;
use App\Models\User;
use App\Models\UserSkillPreference;
use Illuminate\Support\Collection;

class SkillMatcherService
{
    /**
     * Find all skills that match the given prompt.
     */
    public function findMatchingSkills(string $prompt, ?User $user = null): Collection
    {
        // Get all active skills
        $skills = Skill::active()->byPriority()->get();

        // Apply user preferences if available
        if ($user) {
            $skills = $this->applyUserPreferences($skills, $user);
        }

        $matches = collect();

        foreach ($skills as $skill) {
            // Check if user can use this skill
            if (!$skill->canBeUsedBy($user)) {
                continue;
            }

            $score = $skill->calculateMatchScore($prompt);

            if ($score >= $skill->match_threshold) {
                $matches->push([
                    'skill' => $skill,
                    'score' => $score,
                    'matched_keywords' => $skill->getMatchedKeywords($prompt),
                    'priority' => $this->getUserPriority($skill, $user),
                ]);
            }
        }

        // Sort by score (descending), then by priority (descending)
        return $matches->sortByDesc(function ($match) {
            return $match['score'] * 1000 + $match['priority'];
        })->values();
    }

    /**
     * Find the best matching skill for a prompt.
     */
    public function findBestMatch(string $prompt, ?User $user = null): ?array
    {
        $matches = $this->findMatchingSkills($prompt, $user);

        return $matches->first();
    }

    /**
     * Find skills by category that match the prompt.
     */
    public function findMatchingSkillsByCategory(
        string $prompt,
        string $category,
        ?User $user = null
    ): Collection {
        $skills = Skill::active()
            ->category($category)
            ->byPriority()
            ->get();

        if ($user) {
            $skills = $this->applyUserPreferences($skills, $user);
        }

        $matches = collect();

        foreach ($skills as $skill) {
            if (!$skill->canBeUsedBy($user)) {
                continue;
            }

            $score = $skill->calculateMatchScore($prompt);

            if ($score >= $skill->match_threshold) {
                $matches->push([
                    'skill' => $skill,
                    'score' => $score,
                    'matched_keywords' => $skill->getMatchedKeywords($prompt),
                ]);
            }
        }

        return $matches->sortByDesc('score')->values();
    }

    /**
     * Check if a specific skill matches the prompt.
     */
    public function skillMatches(Skill $skill, string $prompt): bool
    {
        return $skill->matches($prompt);
    }

    /**
     * Get match details for a specific skill.
     */
    public function getMatchDetails(Skill $skill, string $prompt): array
    {
        return [
            'matches' => $skill->matches($prompt),
            'score' => $skill->calculateMatchScore($prompt),
            'threshold' => $skill->match_threshold,
            'matched_keywords' => $skill->getMatchedKeywords($prompt),
            'all_keywords' => $skill->getAllKeywords(),
        ];
    }

    /**
     * Log skill triggers and return triggered skills.
     */
    public function triggerMatchingSkills(
        string $prompt,
        ?User $user = null,
        ?string $source = null,
        bool $logTriggers = true
    ): Collection {
        $matches = $this->findMatchingSkills($prompt, $user);

        if ($logTriggers) {
            foreach ($matches as $match) {
                SkillTrigger::log(
                    $match['skill'],
                    $prompt,
                    $match['matched_keywords'],
                    $match['score'],
                    null,
                    $user,
                    true,
                    $source
                );

                // Increment skill usage
                $match['skill']->recordUsage();
            }
        }

        return $matches;
    }

    /**
     * Apply user preferences to skill collection.
     */
    protected function applyUserPreferences(Collection $skills, User $user): Collection
    {
        $preferences = UserSkillPreference::where('user_id', $user->id)
            ->get()
            ->keyBy('skill_id');

        return $skills->filter(function ($skill) use ($preferences) {
            $pref = $preferences->get($skill->id);

            // If user has explicitly disabled this skill, filter it out
            if ($pref && !$pref->is_enabled) {
                return false;
            }

            return true;
        });
    }

    /**
     * Get user-adjusted priority for a skill.
     */
    protected function getUserPriority(Skill $skill, ?User $user): int
    {
        if (!$user) {
            return $skill->priority;
        }

        $pref = UserSkillPreference::where('user_id', $user->id)
            ->where('skill_id', $skill->id)
            ->first();

        if ($pref && $pref->priority_override !== null) {
            return $pref->priority_override;
        }

        return $skill->priority;
    }

    /**
     * Suggest skills based on partial input.
     */
    public function suggestSkills(string $partialInput, ?User $user = null, int $limit = 5): Collection
    {
        $skills = Skill::active()->byPriority()->get();

        if ($user) {
            $skills = $this->applyUserPreferences($skills, $user);
        }

        $suggestions = collect();
        $partialLower = strtolower($partialInput);

        foreach ($skills as $skill) {
            if (!$skill->canBeUsedBy($user)) {
                continue;
            }

            $keywords = $skill->getAllKeywords();
            $matchedKeywords = [];

            foreach ($keywords as $keyword) {
                if (str_starts_with(strtolower($keyword), $partialLower)) {
                    $matchedKeywords[] = $keyword;
                }
            }

            if (!empty($matchedKeywords)) {
                $suggestions->push([
                    'skill' => $skill,
                    'matched_keywords' => $matchedKeywords,
                    'priority' => $skill->priority,
                ]);
            }
        }

        return $suggestions
            ->sortByDesc('priority')
            ->take($limit)
            ->values();
    }

    /**
     * Analyze prompt to identify potential skill categories.
     */
    public function analyzePromptCategories(string $prompt): array
    {
        $categoryKeywords = [
            Skill::CATEGORY_OSINT => ['osint', 'intelligence', 'investigate', 'research', 'find'],
            Skill::CATEGORY_ANALYSIS => ['analyze', 'analysis', 'examine', 'study', 'evaluate'],
            Skill::CATEGORY_VERIFICATION => ['verify', 'verification', 'confirm', 'validate', 'check'],
            Skill::CATEGORY_GEOLOCATION => ['location', 'geolocate', 'where', 'place', 'coordinates', 'map'],
            Skill::CATEGORY_MEDIA => ['image', 'video', 'photo', 'media', 'picture', 'audio'],
            Skill::CATEGORY_RESEARCH => ['search', 'find', 'lookup', 'discover', 'research'],
            Skill::CATEGORY_TRANSLATION => ['translate', 'translation', 'language', 'interpret'],
            Skill::CATEGORY_MILITARY => ['military', 'weapon', 'tank', 'aircraft', 'equipment', 'army'],
        ];

        $promptLower = strtolower($prompt);
        $matchedCategories = [];

        foreach ($categoryKeywords as $category => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($promptLower, $keyword)) {
                    if (!isset($matchedCategories[$category])) {
                        $matchedCategories[$category] = [
                            'category' => $category,
                            'matched_keywords' => [],
                        ];
                    }
                    $matchedCategories[$category]['matched_keywords'][] = $keyword;
                }
            }
        }

        return array_values($matchedCategories);
    }

    /**
     * Get statistics about skill matching.
     */
    public function getMatchingStats(): array
    {
        return [
            'total_skills' => Skill::count(),
            'active_skills' => Skill::active()->count(),
            'total_triggers' => SkillTrigger::count(),
            'triggers_today' => SkillTrigger::where('created_at', '>=', now()->startOfDay())->count(),
            'most_triggered_skills' => Skill::orderBy('usage_count', 'desc')
                ->take(5)
                ->get(['id', 'name', 'usage_count']),
            'avg_match_score' => SkillTrigger::avg('match_score'),
        ];
    }
}
