<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailLog;
use App\Models\EmailTemplate;
use App\Models\EmailUnsubscribe;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class EmailTemplateController extends Controller
{
    /**
     * List all email templates.
     */
    public function index(Request $request): JsonResponse
    {
        $query = EmailTemplate::query();

        // Filter by category
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        // Filter by status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Search by name or slug
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $templates = $query->orderBy('category')
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => $templates,
            'meta' => [
                'categories' => EmailTemplate::categories(),
            ],
        ]);
    }

    /**
     * Store a new email template.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:email_templates|regex:/^[a-z0-9-]+$/',
            'description' => 'nullable|string|max:500',
            'subject' => 'required|string|max:255',
            'html_content' => 'required|string',
            'text_content' => 'required|string',
            'category' => ['required', Rule::in(array_keys(EmailTemplate::categories()))],
            'variables' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $validated['updated_by'] = $request->user()->id;

        $template = EmailTemplate::create($validated);

        return response()->json($template, 201);
    }

    /**
     * Show a specific email template.
     */
    public function show(EmailTemplate $template): JsonResponse
    {
        $template->load('updatedByUser');

        return response()->json([
            'data' => $template,
            'meta' => [
                'available_variables' => $this->getAvailableVariables($template->slug),
            ],
        ]);
    }

    /**
     * Update an email template.
     */
    public function update(Request $request, EmailTemplate $template): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'slug' => [
                'sometimes',
                'string',
                'max:255',
                'regex:/^[a-z0-9-]+$/',
                Rule::unique('email_templates')->ignore($template->id),
            ],
            'description' => 'nullable|string|max:500',
            'subject' => 'sometimes|string|max:255',
            'html_content' => 'sometimes|string',
            'text_content' => 'sometimes|string',
            'category' => ['sometimes', Rule::in(array_keys(EmailTemplate::categories()))],
            'variables' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $validated['updated_by'] = $request->user()->id;

        $template->update($validated);

        return response()->json($template);
    }

    /**
     * Delete an email template.
     */
    public function destroy(EmailTemplate $template): JsonResponse
    {
        // Prevent deletion of system templates
        if ($template->is_system) {
            return response()->json([
                'message' => 'System templates cannot be deleted.',
            ], 403);
        }

        $template->delete();

        return response()->json(null, 204);
    }

    /**
     * Preview an email template with sample data.
     */
    public function preview(Request $request, EmailTemplate $template): JsonResponse
    {
        $sampleData = $request->get('data', []);

        // Add default sample data
        $defaultData = [
            'app_name' => config('app.name'),
            'app_url' => config('app.url'),
            'year' => date('Y'),
            'user_name' => 'John Doe',
            'user_email' => 'john@example.com',
            'user_first_name' => 'John',
            'unsubscribe_url' => '#',
            'preferences_url' => '#',
        ];

        $mergedData = array_merge($defaultData, $sampleData);

        return response()->json([
            'subject' => $template->renderSubject($mergedData),
            'html' => $template->renderHtml($mergedData),
            'text' => $template->renderText($mergedData),
        ]);
    }

    /**
     * Send a test email.
     */
    public function sendTest(Request $request, EmailTemplate $template): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'data' => 'nullable|array',
        ]);

        $testData = array_merge([
            'app_name' => config('app.name'),
            'app_url' => config('app.url'),
            'year' => date('Y'),
            'user_name' => 'Test User',
            'user_email' => $validated['email'],
            'user_first_name' => 'Test',
            'unsubscribe_url' => '#',
            'preferences_url' => '#',
        ], $validated['data'] ?? []);

        try {
            \Illuminate\Support\Facades\Mail::send([], [], function ($message) use ($template, $testData, $validated) {
                $message->to($validated['email'])
                    ->subject('[TEST] ' . $template->renderSubject($testData))
                    ->html($template->renderHtml($testData));
            });

            return response()->json([
                'message' => 'Test email sent successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to send test email.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Duplicate an email template.
     */
    public function duplicate(EmailTemplate $template): JsonResponse
    {
        $newTemplate = $template->replicate();
        $newTemplate->uuid = (string) Str::uuid();
        $newTemplate->slug = $template->slug . '-copy-' . Str::random(4);
        $newTemplate->name = $template->name . ' (Copy)';
        $newTemplate->is_system = false;
        $newTemplate->save();

        return response()->json($newTemplate, 201);
    }

    /**
     * Reset a template to its default content.
     */
    public function reset(EmailTemplate $template): JsonResponse
    {
        $defaults = $this->getDefaultTemplateContent($template->slug);

        if (empty($defaults)) {
            return response()->json([
                'message' => 'No default content available for this template.',
            ], 404);
        }

        $template->update([
            'subject' => $defaults['subject'],
            'html_content' => $defaults['html_content'],
            'text_content' => $defaults['text_content'],
        ]);

        return response()->json($template);
    }

    /**
     * Get email template categories.
     */
    public function categories(): JsonResponse
    {
        return response()->json(EmailTemplate::categories());
    }

    /**
     * Get email statistics.
     */
    public function stats(): JsonResponse
    {
        $stats = [
            'total_templates' => EmailTemplate::count(),
            'active_templates' => EmailTemplate::where('is_active', true)->count(),
            'emails_sent_today' => EmailLog::whereDate('created_at', today())->count(),
            'emails_sent_week' => EmailLog::where('created_at', '>=', now()->subWeek())->count(),
            'emails_sent_month' => EmailLog::where('created_at', '>=', now()->subMonth())->count(),
            'failed_emails' => EmailLog::where('status', 'failed')->where('created_at', '>=', now()->subWeek())->count(),
            'unsubscribes' => EmailUnsubscribe::where('created_at', '>=', now()->subMonth())->count(),
            'by_template' => EmailLog::selectRaw('template_slug, COUNT(*) as count')
                ->where('created_at', '>=', now()->subMonth())
                ->groupBy('template_slug')
                ->orderByDesc('count')
                ->limit(10)
                ->get(),
            'by_status' => EmailLog::selectRaw('status, COUNT(*) as count')
                ->where('created_at', '>=', now()->subMonth())
                ->groupBy('status')
                ->get(),
        ];

        return response()->json($stats);
    }

    /**
     * Get email logs.
     */
    public function logs(Request $request): JsonResponse
    {
        $query = EmailLog::query()->with('user');

        if ($request->has('template_slug')) {
            $query->where('template_slug', $request->template_slug);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('email')) {
            $query->where('email', 'like', '%' . $request->email . '%');
        }

        $logs = $query->orderByDesc('created_at')
            ->paginate($request->get('per_page', 25));

        return response()->json($logs);
    }

    /**
     * Get unsubscribe list.
     */
    public function unsubscribes(Request $request): JsonResponse
    {
        $query = EmailUnsubscribe::query()->with('user');

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('email')) {
            $query->where('email', 'like', '%' . $request->email . '%');
        }

        $unsubscribes = $query->orderByDesc('unsubscribed_at')
            ->paginate($request->get('per_page', 25));

        return response()->json([
            'data' => $unsubscribes,
            'meta' => [
                'types' => EmailUnsubscribe::types(),
            ],
        ]);
    }

    /**
     * Get available variables for a template.
     */
    protected function getAvailableVariables(string $slug): array
    {
        $commonVariables = [
            'app_name' => 'Application name',
            'app_url' => 'Application URL',
            'year' => 'Current year',
            'user_name' => 'User full name',
            'user_email' => 'User email address',
            'user_first_name' => 'User first name',
            'unsubscribe_url' => 'Unsubscribe link',
            'preferences_url' => 'Email preferences link',
        ];

        $templateSpecificVariables = [
            'welcome' => ['verification_url' => 'Email verification link'],
            'email-verification' => ['verification_url' => 'Email verification link'],
            'password-reset' => ['reset_url' => 'Password reset link', 'expires_in_minutes' => 'Link expiration time'],
            'account-locked' => ['lock_reason' => 'Reason for lock', 'unlock_url' => 'Unlock link', 'support_email' => 'Support email'],
            'new-login' => ['ip_address' => 'Login IP', 'user_agent' => 'Browser/device', 'location' => 'Login location', 'login_time' => 'Login time', 'security_url' => 'Security settings URL'],
            'alert-triggered' => ['alert_name' => 'Alert name', 'alert_type' => 'Alert type', 'trigger_data' => 'Trigger details', 'view_url' => 'View link', 'triggered_at' => 'Trigger time'],
            'event-verified' => ['event_title' => 'Event title', 'event_uuid' => 'Event UUID', 'verified_by' => 'Verifier name', 'status' => 'Verification status', 'view_url' => 'Event link'],
            'weekly-digest' => ['stats' => 'Statistics object', 'top_events' => 'Top events list', 'alerts' => 'Alerts list', 'period_start' => 'Period start date', 'period_end' => 'Period end date'],
            'data-export-ready' => ['download_url' => 'Download link', 'expires_at' => 'Expiration date', 'format' => 'Export format'],
            'account-deletion' => ['deletion_date' => 'Scheduled deletion date', 'cancel_url' => 'Cancel deletion link', 'support_email' => 'Support email'],
        ];

        return array_merge($commonVariables, $templateSpecificVariables[$slug] ?? []);
    }

    /**
     * Get default template content for reset.
     */
    protected function getDefaultTemplateContent(string $slug): array
    {
        // This would typically load from a config file or seeder
        // For now, return empty to indicate no default available
        return [];
    }
}
