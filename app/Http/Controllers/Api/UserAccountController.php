<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AccountDeletionRequest;
use App\Models\ConsentLog;
use App\Models\DataExportRequest;
use App\Models\OnboardingProgress;
use App\Models\Setting;
use App\Models\UserActivity;
use App\Models\UserSession;
use App\Services\AvatarService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserAccountController extends Controller
{
    public function __construct(
        protected AvatarService $avatarService
    ) {}

    // ===== PROFILE MANAGEMENT =====

    /**
     * Get current user's profile.
     */
    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load('roles:id,name,slug');

        return response()->json([
            'data' => [
                'uuid' => $user->uuid,
                'name' => $user->name,
                'email' => $user->email,
                'avatar_url' => $this->avatarService->getAvatarUrl($user),
                'avatar_type' => $user->avatar_type,
                'bio' => $user->bio,
                'organization' => $user->organization,
                'location' => $user->location,
                'website' => $user->website,
                'timezone' => $user->timezone ?? 'UTC',
                'locale' => $user->locale ?? 'en',
                'role' => $user->role,
                'roles' => $user->roles->pluck('slug'),
                'is_verified' => $user->is_verified,
                'email_verified_at' => $user->email_verified_at,
                'created_at' => $user->created_at,
                'last_login_at' => $user->last_login_at,
            ],
            'meta' => [
                'onboarding_completed' => $user->onboarding_completed_at !== null,
                'onboarding_step' => $user->onboarding_step,
                'permissions' => $user->getAllPermissions(),
            ],
        ]);
    }

    /**
     * Update user profile.
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'sometimes|string|min:2|max:100',
            'bio' => 'nullable|string|max:500',
            'organization' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:100',
            'website' => 'nullable|url|max:255',
            'timezone' => 'nullable|string|timezone',
            'locale' => 'nullable|string|max:10',
        ]);

        $user->update($validated);

        UserActivity::log(
            $user,
            UserActivity::TYPE_PROFILE_UPDATE,
            'Updated profile information',
            ['fields' => array_keys($validated)],
            $request->ip(),
            $request->userAgent()
        );

        return response()->json([
            'message' => 'Profile updated successfully',
            'data' => $user->only([
                'name', 'bio', 'organization', 'location',
                'website', 'timezone', 'locale',
            ]),
        ]);
    }

    /**
     * Change password.
     */
    public function changePassword(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'current_password' => 'required|string',
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ]);

        if (!Hash::check($validated['current_password'], $user->password)) {
            return response()->json([
                'message' => 'Current password is incorrect',
                'errors' => ['current_password' => ['The provided password is incorrect.']],
            ], 422);
        }

        $user->update([
            'password' => Hash::make($validated['password']),
            'password_changed_at' => now(),
        ]);

        UserActivity::log(
            $user,
            UserActivity::TYPE_PASSWORD_CHANGE,
            null,
            null,
            $request->ip(),
            $request->userAgent()
        );

        return response()->json([
            'message' => 'Password changed successfully',
        ]);
    }

    // ===== AVATAR MANAGEMENT =====

    /**
     * Get avatar options.
     */
    public function avatarOptions(): JsonResponse
    {
        return response()->json([
            'data' => [
                'types' => [
                    AvatarService::TYPE_GENERATED => 'Geometric Pattern',
                    AvatarService::TYPE_INITIALS => 'Initials',
                    AvatarService::TYPE_GRAVATAR => 'Gravatar',
                    AvatarService::TYPE_UPLOADED => 'Upload Custom',
                ],
                'palettes' => array_keys($this->avatarService->getColorPalettes()),
            ],
        ]);
    }

    /**
     * Update avatar settings.
     */
    public function updateAvatar(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'type' => 'required|in:generated,initials,gravatar,uploaded',
            'color' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        $this->avatarService->setAvatarType(
            $user,
            $validated['type'],
            $validated['color'] ?? null
        );

        UserActivity::log(
            $user,
            UserActivity::TYPE_AVATAR_CHANGE,
            "Changed avatar to {$validated['type']}",
            $validated,
            $request->ip(),
            $request->userAgent()
        );

        return response()->json([
            'message' => 'Avatar updated successfully',
            'data' => [
                'avatar_url' => $this->avatarService->getAvatarUrl($user),
                'avatar_type' => $user->avatar_type,
            ],
        ]);
    }

    /**
     * Regenerate avatar with new random seed.
     */
    public function regenerateAvatar(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'color' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        $this->avatarService->regenerateAvatar($user, $validated['color'] ?? null);

        return response()->json([
            'message' => 'Avatar regenerated',
            'data' => [
                'avatar_url' => $this->avatarService->getAvatarUrl($user),
            ],
        ]);
    }

    /**
     * Upload custom avatar.
     */
    public function uploadAvatar(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,gif,webp|max:2048',
        ]);

        $url = $this->avatarService->uploadAvatar($user, $request->file('avatar'));

        UserActivity::log(
            $user,
            UserActivity::TYPE_AVATAR_CHANGE,
            'Uploaded custom avatar',
            null,
            $request->ip(),
            $request->userAgent()
        );

        return response()->json([
            'message' => 'Avatar uploaded successfully',
            'data' => [
                'avatar_url' => $url,
            ],
        ]);
    }

    /**
     * Delete custom avatar.
     */
    public function deleteAvatar(Request $request): JsonResponse
    {
        $user = $request->user();

        $this->avatarService->deleteAvatar($user);

        return response()->json([
            'message' => 'Avatar deleted, reverted to generated',
            'data' => [
                'avatar_url' => $this->avatarService->getAvatarUrl($user),
                'avatar_type' => $user->avatar_type,
            ],
        ]);
    }

    // ===== PREFERENCES & SETTINGS =====

    /**
     * Get user preferences.
     */
    public function preferences(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'data' => [
                'preferences' => $user->preferences ?? [],
                'email_preferences' => $user->email_preferences ?? [],
                'email_notifications_enabled' => $user->email_notifications_enabled,
            ],
        ]);
    }

    /**
     * Update user preferences.
     */
    public function updatePreferences(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'preferences' => 'nullable|array',
            'preferences.theme' => 'nullable|in:light,dark,system',
            'preferences.map_style' => 'nullable|string|max:50',
            'preferences.default_zoom' => 'nullable|integer|min:1|max:18',
            'preferences.show_clusters' => 'nullable|boolean',
            'preferences.notifications_enabled' => 'nullable|boolean',
            'email_preferences' => 'nullable|array',
            'email_notifications_enabled' => 'nullable|boolean',
        ]);

        if (isset($validated['preferences'])) {
            $user->preferences = array_merge(
                $user->preferences ?? [],
                $validated['preferences']
            );
        }

        if (isset($validated['email_preferences'])) {
            $user->email_preferences = $validated['email_preferences'];
        }

        if (isset($validated['email_notifications_enabled'])) {
            $user->email_notifications_enabled = $validated['email_notifications_enabled'];
        }

        $user->save();

        UserActivity::log(
            $user,
            UserActivity::TYPE_SETTINGS_CHANGE,
            'Updated preferences',
            null,
            $request->ip(),
            $request->userAgent()
        );

        return response()->json([
            'message' => 'Preferences updated successfully',
            'data' => [
                'preferences' => $user->preferences,
                'email_preferences' => $user->email_preferences,
            ],
        ]);
    }

    // ===== PRIVACY & CONSENT =====

    /**
     * Get privacy settings and consent status.
     */
    public function privacySettings(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'data' => [
                'consents' => [
                    'terms_of_service' => [
                        'accepted' => $user->terms_accepted_at !== null,
                        'accepted_at' => $user->terms_accepted_at,
                        'version' => $user->terms_version,
                    ],
                    'privacy_policy' => [
                        'accepted' => $user->privacy_policy_accepted_at !== null,
                        'accepted_at' => $user->privacy_policy_accepted_at,
                        'version' => $user->privacy_policy_version,
                    ],
                    'marketing' => [
                        'consented' => $user->marketing_consent,
                        'consented_at' => $user->marketing_consent_at,
                    ],
                    'analytics' => [
                        'consented' => $user->analytics_consent,
                        'consented_at' => $user->analytics_consent_at,
                    ],
                    'data_processing' => [
                        'consented' => $user->data_processing_consent,
                        'consented_at' => $user->data_processing_consent_at,
                    ],
                ],
                'data_export' => [
                    'available_types' => DataExportRequest::getDataTypes(),
                    'pending_request' => DataExportRequest::where('user_id', $user->id)
                        ->pending()
                        ->exists(),
                ],
                'deletion' => [
                    'pending_request' => AccountDeletionRequest::where('user_id', $user->id)
                        ->whereIn('status', ['pending', 'confirmed'])
                        ->exists(),
                ],
            ],
        ]);
    }

    /**
     * Update consent preferences.
     */
    public function updateConsent(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'marketing_consent' => 'sometimes|boolean',
            'analytics_consent' => 'sometimes|boolean',
            'data_processing_consent' => 'sometimes|boolean',
        ]);

        $updates = [];

        foreach ($validated as $key => $value) {
            $field = $key;
            $atField = str_replace('_consent', '_consent_at', $key);

            $user->$field = $value;
            $user->$atField = now();
            $updates[$field] = $value;

            // Log consent change
            ConsentLog::record(
                $user,
                str_replace('_consent', '', $key),
                $value,
                null,
                $request->ip(),
                $request->userAgent()
            );
        }

        $user->consent_ip = $request->ip();
        $user->save();

        UserActivity::log(
            $user,
            UserActivity::TYPE_CONSENT_UPDATE,
            'Updated consent preferences',
            $updates,
            $request->ip(),
            $request->userAgent()
        );

        return response()->json([
            'message' => 'Consent preferences updated',
            'data' => $updates,
        ]);
    }

    /**
     * Get consent history.
     */
    public function consentHistory(Request $request): JsonResponse
    {
        $user = $request->user();

        $history = ConsentLog::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return response()->json([
            'data' => $history,
        ]);
    }

    // ===== DATA EXPORT (GDPR) =====

    /**
     * Request data export.
     */
    public function requestDataExport(Request $request): JsonResponse
    {
        $user = $request->user();

        // Check for existing pending request
        if (DataExportRequest::where('user_id', $user->id)->pending()->exists()) {
            return response()->json([
                'message' => 'You already have a pending data export request',
            ], 422);
        }

        $validated = $request->validate([
            'format' => 'nullable|in:json,csv,xml',
            'include_data' => 'nullable|array',
            'include_data.*' => 'string|in:profile,comments,articles,events,bookmarks,activity,consent,sessions',
        ]);

        $exportRequest = DataExportRequest::createRequest(
            $user,
            $validated['format'] ?? 'json',
            $validated['include_data'] ?? null,
            $request->ip()
        );

        UserActivity::log(
            $user,
            UserActivity::TYPE_DATA_EXPORT,
            'Requested data export',
            ['format' => $exportRequest->format],
            $request->ip(),
            $request->userAgent()
        );

        return response()->json([
            'message' => 'Data export request submitted. You will be notified when ready.',
            'data' => [
                'uuid' => $exportRequest->uuid,
                'status' => $exportRequest->status,
                'format' => $exportRequest->format,
            ],
        ], 201);
    }

    /**
     * Get data export requests.
     */
    public function dataExportRequests(Request $request): JsonResponse
    {
        $user = $request->user();

        $requests = DataExportRequest::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get(['uuid', 'status', 'format', 'file_size', 'requested_at', 'completed_at', 'expires_at']);

        return response()->json([
            'data' => $requests,
        ]);
    }

    // ===== ACCOUNT DELETION (GDPR) =====

    /**
     * Request account deletion.
     */
    public function requestAccountDeletion(Request $request): JsonResponse
    {
        $user = $request->user();

        // Check for existing request
        $existing = AccountDeletionRequest::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'You already have a pending deletion request',
                'data' => [
                    'uuid' => $existing->uuid,
                    'status' => $existing->status,
                    'scheduled_for' => $existing->scheduled_for,
                    'days_remaining' => $existing->getDaysRemaining(),
                ],
            ], 422);
        }

        $validated = $request->validate([
            'reason' => 'nullable|string|in:' . implode(',', array_keys(AccountDeletionRequest::getReasonOptions())),
            'feedback' => 'nullable|string|max:1000',
            'password' => 'required|string',
        ]);

        // Verify password
        if (!Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'Password is incorrect',
                'errors' => ['password' => ['The provided password is incorrect.']],
            ], 422);
        }

        $deletionRequest = AccountDeletionRequest::createRequest(
            $user,
            $validated['reason'] ?? null,
            $validated['feedback'] ?? null,
            $request->ip()
        );

        // Send confirmation email
        $deletionRequest->sendConfirmation();

        UserActivity::log(
            $user,
            UserActivity::TYPE_ACCOUNT_DELETE_REQUEST,
            'Requested account deletion',
            ['reason' => $validated['reason'] ?? 'not_specified'],
            $request->ip(),
            $request->userAgent()
        );

        return response()->json([
            'message' => 'Account deletion request submitted. Please check your email to confirm.',
            'data' => [
                'uuid' => $deletionRequest->uuid,
                'status' => $deletionRequest->status,
            ],
        ], 201);
    }

    /**
     * Cancel account deletion request.
     */
    public function cancelAccountDeletion(Request $request): JsonResponse
    {
        $user = $request->user();

        $deletionRequest = AccountDeletionRequest::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->first();

        if (!$deletionRequest) {
            return response()->json([
                'message' => 'No pending deletion request found',
            ], 404);
        }

        if (!$deletionRequest->canBeCancelled()) {
            return response()->json([
                'message' => 'This deletion request cannot be cancelled',
            ], 422);
        }

        $deletionRequest->cancel('User cancelled request');

        return response()->json([
            'message' => 'Account deletion request cancelled',
        ]);
    }

    // ===== SESSION MANAGEMENT =====

    /**
     * Get active sessions.
     */
    public function sessions(Request $request): JsonResponse
    {
        $user = $request->user();

        $sessions = UserSession::where('user_id', $user->id)
            ->active()
            ->orderBy('last_activity_at', 'desc')
            ->get();

        return response()->json([
            'data' => $sessions->map(fn ($session) => [
                'uuid' => $session->uuid,
                'device_type' => $session->device_type,
                'browser' => $session->browser,
                'platform' => $session->platform,
                'ip_address' => $session->ip_address,
                'location' => $session->location,
                'is_current' => $session->is_current,
                'last_activity_at' => $session->last_activity_at,
                'created_at' => $session->created_at,
            ]),
        ]);
    }

    /**
     * Terminate a specific session.
     */
    public function terminateSession(Request $request, string $uuid): JsonResponse
    {
        $user = $request->user();

        $session = UserSession::where('uuid', $uuid)
            ->where('user_id', $user->id)
            ->firstOrFail();

        if ($session->is_current) {
            return response()->json([
                'message' => 'Cannot terminate current session',
            ], 422);
        }

        $session->terminate();

        UserActivity::log(
            $user,
            UserActivity::TYPE_SESSION_TERMINATE,
            'Terminated session',
            ['session_uuid' => $uuid],
            $request->ip(),
            $request->userAgent()
        );

        return response()->json([
            'message' => 'Session terminated',
        ]);
    }

    /**
     * Terminate all other sessions.
     */
    public function terminateOtherSessions(Request $request): JsonResponse
    {
        $user = $request->user();
        $currentSessionId = session()->getId();

        $count = UserSession::terminateOtherSessions($user, $currentSessionId);

        UserActivity::log(
            $user,
            UserActivity::TYPE_SESSION_TERMINATE,
            "Terminated {$count} other sessions",
            ['count' => $count],
            $request->ip(),
            $request->userAgent()
        );

        return response()->json([
            'message' => "{$count} sessions terminated",
            'data' => ['terminated_count' => $count],
        ]);
    }

    // ===== ACTIVITY LOG =====

    /**
     * Get user activity log.
     */
    public function activityLog(Request $request): JsonResponse
    {
        $user = $request->user();

        $activities = UserActivity::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(25);

        return response()->json([
            'data' => $activities->map(fn ($activity) => [
                'type' => $activity->activity_type,
                'description' => $activity->getReadableDescription(),
                'icon' => $activity->getIcon(),
                'ip_address' => $activity->ip_address,
                'created_at' => $activity->created_at,
            ]),
            'meta' => [
                'current_page' => $activities->currentPage(),
                'last_page' => $activities->lastPage(),
                'total' => $activities->total(),
            ],
        ]);
    }
}
