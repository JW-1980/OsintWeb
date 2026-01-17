<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\EmailUnsubscribe;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class EmailUnsubscribeController extends Controller
{
    /**
     * Handle single-click unsubscribe from email link.
     *
     * This endpoint handles both GET and POST requests for unsubscribe.
     * GET shows the unsubscribe confirmation page.
     * POST processes the unsubscribe request.
     */
    public function unsubscribe(Request $request): mixed
    {
        // Verify the signed URL
        if (!$request->hasValidSignature()) {
            return response()->view('emails.unsubscribe-error', [
                'message' => 'This unsubscribe link has expired or is invalid.',
            ], 403);
        }

        $email = base64_decode($request->get('email', ''));
        $type = $request->get('type', 'all');

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->view('emails.unsubscribe-error', [
                'message' => 'Invalid email address.',
            ], 400);
        }

        // Check if already unsubscribed
        $existingUnsubscribe = EmailUnsubscribe::where('email', $email)
            ->where('type', $type)
            ->first();

        if ($existingUnsubscribe) {
            return response()->view('emails.unsubscribe-success', [
                'email' => $email,
                'type' => $type,
                'typeName' => EmailUnsubscribe::types()[$type] ?? $type,
                'alreadyUnsubscribed' => true,
            ]);
        }

        // For GET requests, show confirmation page
        if ($request->isMethod('get')) {
            return response()->view('emails.unsubscribe-confirm', [
                'email' => $email,
                'type' => $type,
                'typeName' => EmailUnsubscribe::types()[$type] ?? $type,
                'types' => EmailUnsubscribe::types(),
            ]);
        }

        // Process unsubscribe for POST requests
        return $this->processUnsubscribe($request, $email, $type);
    }

    /**
     * Process the unsubscribe request.
     */
    public function processUnsubscribe(Request $request, string $email, string $type): mixed
    {
        $reason = $request->get('reason');

        // Find the user if they exist
        $user = User::where('email', $email)->first();

        // Create unsubscribe record
        EmailUnsubscribe::create([
            'user_id' => $user?->id,
            'email' => $email,
            'type' => $type,
            'reason' => $reason,
            'unsubscribed_ip' => $request->ip(),
        ]);

        // Update user's email preferences if they exist
        if ($user) {
            if ($type === 'all') {
                $user->update([
                    'email_notifications_enabled' => false,
                    'email_unsubscribed_at' => now(),
                ]);
            } else {
                $preferences = $user->email_preferences ?? [];
                $preferences[$type] = false;
                $user->update(['email_preferences' => $preferences]);
            }
        }

        // Return appropriate response
        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Successfully unsubscribed.',
                'email' => $email,
                'type' => $type,
            ]);
        }

        return response()->view('emails.unsubscribe-success', [
            'email' => $email,
            'type' => $type,
            'typeName' => EmailUnsubscribe::types()[$type] ?? $type,
            'alreadyUnsubscribed' => false,
        ]);
    }

    /**
     * Handle one-click unsubscribe (RFC 8058 List-Unsubscribe-Post).
     *
     * This endpoint receives a POST with List-Unsubscribe=One-Click header
     * for instant unsubscribe without user interaction.
     */
    public function oneClickUnsubscribe(Request $request): JsonResponse
    {
        // Verify the signed URL
        if (!$request->hasValidSignature()) {
            return response()->json([
                'message' => 'Invalid or expired unsubscribe link.',
            ], 403);
        }

        $email = base64_decode($request->get('email', ''));
        $type = $request->get('type', 'all');

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json([
                'message' => 'Invalid email address.',
            ], 400);
        }

        // Check if already unsubscribed
        $exists = EmailUnsubscribe::where('email', $email)
            ->where('type', $type)
            ->exists();

        if (!$exists) {
            $user = User::where('email', $email)->first();

            EmailUnsubscribe::create([
                'user_id' => $user?->id,
                'email' => $email,
                'type' => $type,
                'reason' => 'One-click unsubscribe',
                'unsubscribed_ip' => $request->ip(),
            ]);

            if ($user) {
                if ($type === 'all') {
                    $user->update([
                        'email_notifications_enabled' => false,
                        'email_unsubscribed_at' => now(),
                    ]);
                } else {
                    $preferences = $user->email_preferences ?? [];
                    $preferences[$type] = false;
                    $user->update(['email_preferences' => $preferences]);
                }
            }
        }

        return response()->json([
            'message' => 'Successfully unsubscribed.',
        ]);
    }

    /**
     * Resubscribe a user.
     */
    public function resubscribe(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'type' => 'required|string|in:all,marketing,notifications,alerts,digest',
        ]);

        $deleted = EmailUnsubscribe::where('email', $validated['email'])
            ->where('type', $validated['type'])
            ->delete();

        // Update user preferences if they exist
        $user = User::where('email', $validated['email'])->first();
        if ($user) {
            if ($validated['type'] === 'all') {
                $user->update([
                    'email_notifications_enabled' => true,
                    'email_unsubscribed_at' => null,
                ]);
            } else {
                $preferences = $user->email_preferences ?? [];
                $preferences[$validated['type']] = true;
                $user->update(['email_preferences' => $preferences]);
            }
        }

        return response()->json([
            'message' => $deleted > 0 ? 'Successfully resubscribed.' : 'Email was not unsubscribed.',
        ]);
    }

    /**
     * Get unsubscribe preferences for authenticated user.
     */
    public function preferences(Request $request): JsonResponse
    {
        $user = $request->user();

        $unsubscribes = EmailUnsubscribe::where('email', $user->email)
            ->pluck('type')
            ->toArray();

        return response()->json([
            'email' => $user->email,
            'email_notifications_enabled' => $user->email_notifications_enabled,
            'unsubscribed_from' => $unsubscribes,
            'email_preferences' => $user->email_preferences ?? [],
            'types' => EmailUnsubscribe::types(),
        ]);
    }

    /**
     * Update email preferences for authenticated user.
     */
    public function updatePreferences(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email_notifications_enabled' => 'boolean',
            'preferences' => 'array',
            'preferences.*' => 'boolean',
        ]);

        $user = $request->user();

        if (isset($validated['email_notifications_enabled'])) {
            $user->email_notifications_enabled = $validated['email_notifications_enabled'];

            // Handle global unsubscribe
            if (!$validated['email_notifications_enabled']) {
                $user->email_unsubscribed_at = now();

                // Create unsubscribe record if not exists
                EmailUnsubscribe::firstOrCreate(
                    ['email' => $user->email, 'type' => 'all'],
                    [
                        'user_id' => $user->id,
                        'unsubscribed_ip' => $request->ip(),
                    ]
                );
            } else {
                $user->email_unsubscribed_at = null;
                EmailUnsubscribe::where('email', $user->email)->where('type', 'all')->delete();
            }
        }

        if (isset($validated['preferences'])) {
            $user->email_preferences = $validated['preferences'];

            // Update unsubscribe records
            foreach ($validated['preferences'] as $type => $enabled) {
                if (!$enabled) {
                    EmailUnsubscribe::firstOrCreate(
                        ['email' => $user->email, 'type' => $type],
                        [
                            'user_id' => $user->id,
                            'unsubscribed_ip' => $request->ip(),
                        ]
                    );
                } else {
                    EmailUnsubscribe::where('email', $user->email)->where('type', $type)->delete();
                }
            }
        }

        $user->save();

        return response()->json([
            'message' => 'Email preferences updated.',
            'email_notifications_enabled' => $user->email_notifications_enabled,
            'email_preferences' => $user->email_preferences,
        ]);
    }

    /**
     * Generate unsubscribe URL for a user/email.
     */
    public static function generateUnsubscribeUrl(string $email, string $type = 'all'): string
    {
        return URL::temporarySignedRoute(
            'email.unsubscribe',
            now()->addDays(30),
            [
                'email' => base64_encode($email),
                'type' => $type,
            ]
        );
    }
}
