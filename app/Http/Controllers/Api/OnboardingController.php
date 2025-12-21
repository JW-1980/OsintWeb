<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OnboardingProgress;
use App\Models\UserActivity;
use App\Services\AvatarService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OnboardingController extends Controller
{
    public function __construct(
        protected AvatarService $avatarService
    ) {}

    /**
     * Get onboarding status and progress.
     */
    public function status(Request $request): JsonResponse
    {
        $user = $request->user();

        // Initialize onboarding if not done
        if (OnboardingProgress::where('user_id', $user->id)->doesntExist()) {
            OnboardingProgress::initializeForUser($user);
            UserActivity::log($user, UserActivity::TYPE_ONBOARDING_START);
        }

        $steps = OnboardingProgress::where('user_id', $user->id)
            ->orderBy('order')
            ->get();

        $nextStep = OnboardingProgress::getNextStep($user);
        $completionPercentage = OnboardingProgress::getCompletionPercentage($user);
        $isComplete = $user->onboarding_completed_at !== null;

        return response()->json([
            'data' => [
                'is_complete' => $isComplete,
                'is_skipped' => $user->onboarding_skipped,
                'completion_percentage' => $completionPercentage,
                'current_step' => $user->onboarding_step,
                'next_step' => $nextStep?->step_key,
                'completed_at' => $user->onboarding_completed_at,
                'steps' => $steps->map(fn ($step) => [
                    'key' => $step->step_key,
                    'name' => $step->step_name,
                    'completed' => $step->completed,
                    'completed_at' => $step->completed_at,
                    'order' => $step->order,
                ]),
            ],
        ]);
    }

    /**
     * Get specific step data.
     */
    public function step(Request $request, string $stepKey): JsonResponse
    {
        $user = $request->user();

        $step = OnboardingProgress::where('user_id', $user->id)
            ->where('step_key', $stepKey)
            ->firstOrFail();

        $stepData = $this->getStepData($user, $stepKey);

        return response()->json([
            'data' => [
                'step' => [
                    'key' => $step->step_key,
                    'name' => $step->step_name,
                    'completed' => $step->completed,
                    'order' => $step->order,
                ],
                'content' => $stepData,
            ],
        ]);
    }

    /**
     * Complete an onboarding step.
     */
    public function completeStep(Request $request, string $stepKey): JsonResponse
    {
        $user = $request->user();

        $step = OnboardingProgress::where('user_id', $user->id)
            ->where('step_key', $stepKey)
            ->firstOrFail();

        if ($step->completed) {
            return response()->json([
                'message' => 'Step already completed',
                'data' => ['step' => $step->step_key],
            ]);
        }

        // Handle step-specific logic
        $stepData = $request->input('step_data', []);
        $this->processStepCompletion($user, $stepKey, $stepData, $request);

        $step->markCompleted($stepData);

        // Update user's current step
        $nextStep = OnboardingProgress::getNextStep($user);
        $user->onboarding_step = $nextStep ? $nextStep->order : $step->order + 1;
        $user->save();

        UserActivity::log(
            $user,
            UserActivity::TYPE_ONBOARDING_STEP,
            "Completed step: {$step->step_name}",
            ['step_key' => $stepKey],
            $request->ip(),
            $request->userAgent()
        );

        // Check if onboarding is complete
        if ($stepKey === OnboardingProgress::STEP_COMPLETE || OnboardingProgress::isComplete($user)) {
            $this->markOnboardingComplete($user, $request);
        }

        return response()->json([
            'message' => 'Step completed',
            'data' => [
                'step' => $step->step_key,
                'next_step' => $nextStep?->step_key,
                'completion_percentage' => OnboardingProgress::getCompletionPercentage($user),
                'is_complete' => $user->onboarding_completed_at !== null,
            ],
        ]);
    }

    /**
     * Skip onboarding.
     */
    public function skip(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->onboarding_completed_at) {
            return response()->json([
                'message' => 'Onboarding already completed',
            ]);
        }

        $user->onboarding_skipped = true;
        $user->onboarding_completed_at = now();
        $user->save();

        UserActivity::log(
            $user,
            UserActivity::TYPE_ONBOARDING_SKIP,
            null,
            null,
            $request->ip(),
            $request->userAgent()
        );

        return response()->json([
            'message' => 'Onboarding skipped',
        ]);
    }

    /**
     * Reset onboarding (for re-running).
     */
    public function reset(Request $request): JsonResponse
    {
        $user = $request->user();

        OnboardingProgress::where('user_id', $user->id)->delete();

        $user->onboarding_step = 0;
        $user->onboarding_completed_at = null;
        $user->onboarding_skipped = false;
        $user->onboarding_data = null;
        $user->save();

        OnboardingProgress::initializeForUser($user);

        return response()->json([
            'message' => 'Onboarding reset',
        ]);
    }

    /**
     * Get step-specific content/data.
     */
    protected function getStepData($user, string $stepKey): array
    {
        return match ($stepKey) {
            OnboardingProgress::STEP_WELCOME => [
                'title' => 'Welcome to OsintWeb',
                'description' => 'Your comprehensive OSINT military conflict tracking platform.',
                'features' => [
                    'Interactive conflict mapping with real-time updates',
                    'Military equipment database with detailed specifications',
                    'Community-driven event verification',
                    'Advanced search and filtering capabilities',
                ],
            ],
            OnboardingProgress::STEP_PROFILE => [
                'title' => 'Complete Your Profile',
                'description' => 'Tell us a bit about yourself.',
                'current' => [
                    'name' => $user->name,
                    'bio' => $user->bio,
                    'organization' => $user->organization,
                    'location' => $user->location,
                ],
                'fields' => ['name', 'bio', 'organization', 'location', 'website'],
            ],
            OnboardingProgress::STEP_AVATAR => [
                'title' => 'Set Your Avatar',
                'description' => 'Choose how you want to appear in the community.',
                'current' => [
                    'avatar_url' => $this->avatarService->getAvatarUrl($user),
                    'avatar_type' => $user->avatar_type,
                ],
                'options' => [
                    'generated' => 'Unique geometric pattern',
                    'initials' => 'Your initials',
                    'gravatar' => 'Gravatar service',
                    'uploaded' => 'Upload custom image',
                ],
            ],
            OnboardingProgress::STEP_PRIVACY => [
                'title' => 'Privacy Settings',
                'description' => 'Control how your data is used.',
                'current' => [
                    'marketing_consent' => $user->marketing_consent,
                    'analytics_consent' => $user->analytics_consent,
                ],
                'options' => [
                    'marketing_consent' => 'Receive updates about new features and content',
                    'analytics_consent' => 'Help improve the platform with usage analytics',
                ],
            ],
            OnboardingProgress::STEP_PREFERENCES => [
                'title' => 'Preferences',
                'description' => 'Customize your experience.',
                'current' => $user->preferences ?? [],
                'options' => [
                    'theme' => ['light', 'dark', 'system'],
                    'email_notifications' => $user->email_notifications_enabled,
                ],
            ],
            OnboardingProgress::STEP_EXPLORE_MAP => [
                'title' => 'Explore the Map',
                'description' => 'Learn how to navigate and interact with the conflict map.',
                'features' => [
                    'Pan and zoom to explore regions',
                    'Click events for details',
                    'Filter by date, type, or actor',
                    'Toggle layers for different views',
                ],
            ],
            OnboardingProgress::STEP_EXPLORE_EVENTS => [
                'title' => 'Browse Events',
                'description' => 'Discover how to find and analyze events.',
                'features' => [
                    'Search events by keyword or location',
                    'Filter by event type or verification status',
                    'View event timelines and sources',
                    'Contribute your own verified events',
                ],
            ],
            OnboardingProgress::STEP_EXPLORE_EQUIPMENT => [
                'title' => 'Military Equipment Database',
                'description' => 'Explore our comprehensive equipment catalog.',
                'features' => [
                    'Browse equipment by category',
                    'View detailed specifications',
                    'Compare different systems',
                    'Link equipment to events',
                ],
            ],
            OnboardingProgress::STEP_COMPLETE => [
                'title' => 'You\'re All Set!',
                'description' => 'You\'re ready to start using OsintWeb.',
                'next_steps' => [
                    'Explore the map to see current conflicts',
                    'Read articles from our community',
                    'Join discussions in the comments',
                    'Contribute verified events',
                ],
            ],
            default => [],
        };
    }

    /**
     * Process step-specific completion logic.
     */
    protected function processStepCompletion($user, string $stepKey, array $stepData, Request $request): void
    {
        match ($stepKey) {
            OnboardingProgress::STEP_PROFILE => $this->processProfileStep($user, $stepData),
            OnboardingProgress::STEP_AVATAR => $this->processAvatarStep($user, $stepData),
            OnboardingProgress::STEP_PRIVACY => $this->processPrivacyStep($user, $stepData, $request),
            OnboardingProgress::STEP_PREFERENCES => $this->processPreferencesStep($user, $stepData),
            default => null,
        };
    }

    protected function processProfileStep($user, array $data): void
    {
        $allowed = ['name', 'bio', 'organization', 'location', 'website'];
        $updates = array_intersect_key($data, array_flip($allowed));

        if (!empty($updates)) {
            $user->update($updates);
        }
    }

    protected function processAvatarStep($user, array $data): void
    {
        if (isset($data['avatar_type'])) {
            $this->avatarService->setAvatarType(
                $user,
                $data['avatar_type'],
                $data['avatar_color'] ?? null
            );
        }
    }

    protected function processPrivacyStep($user, array $data, Request $request): void
    {
        if (isset($data['marketing_consent'])) {
            $user->marketing_consent = (bool) $data['marketing_consent'];
            $user->marketing_consent_at = now();
        }

        if (isset($data['analytics_consent'])) {
            $user->analytics_consent = (bool) $data['analytics_consent'];
            $user->analytics_consent_at = now();
        }

        $user->consent_ip = $request->ip();
        $user->save();
    }

    protected function processPreferencesStep($user, array $data): void
    {
        if (isset($data['preferences'])) {
            $user->preferences = array_merge(
                $user->preferences ?? [],
                $data['preferences']
            );
        }

        if (isset($data['email_notifications'])) {
            $user->email_notifications_enabled = (bool) $data['email_notifications'];
        }

        $user->save();
    }

    /**
     * Mark onboarding as complete.
     */
    protected function markOnboardingComplete($user, Request $request): void
    {
        if ($user->onboarding_completed_at) {
            return;
        }

        $user->onboarding_completed_at = now();
        $user->save();

        UserActivity::log(
            $user,
            UserActivity::TYPE_ONBOARDING_COMPLETE,
            null,
            null,
            $request->ip(),
            $request->userAgent()
        );
    }
}
