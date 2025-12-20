<?php

declare(strict_types=1);

namespace App\Http\Controllers\Install;

use App\Http\Controllers\Controller;
use App\Services\EnvironmentWriter;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

/**
 * Email configuration controller
 */
class EmailController extends Controller
{
    public function __construct(
        private readonly EnvironmentWriter $environmentWriter
    ) {
    }

    /**
     * Display email configuration page
     */
    public function index(): View
    {
        return view('install.email', [
            'mail_driver' => $this->environmentWriter->get('MAIL_MAILER') ?? 'smtp',
            'mail_host' => $this->environmentWriter->get('MAIL_HOST') ?? 'smtp.mailgun.org',
            'mail_port' => $this->environmentWriter->get('MAIL_PORT') ?? '587',
            'mail_username' => $this->environmentWriter->get('MAIL_USERNAME') ?? '',
            'mail_encryption' => $this->environmentWriter->get('MAIL_ENCRYPTION') ?? 'tls',
            'mail_from_address' => $this->environmentWriter->get('MAIL_FROM_ADDRESS') ?? '',
            'mail_from_name' => $this->environmentWriter->get('MAIL_FROM_NAME') ?? config('app.name', 'OsintWeb'),
        ]);
    }

    /**
     * Test email configuration
     */
    public function test(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'test_email' => 'required|email',
            'mail_mailer' => 'required|string',
            'mail_host' => 'required_if:mail_mailer,smtp|string',
            'mail_port' => 'required_if:mail_mailer,smtp|integer',
            'mail_username' => 'nullable|string',
            'mail_password' => 'nullable|string',
            'mail_encryption' => 'required_if:mail_mailer,smtp|string|in:tls,ssl',
            'mail_from_address' => 'required|email',
            'mail_from_name' => 'required|string',
        ]);

        try {
            // Temporarily update mail config for testing
            config([
                'mail.default' => $validated['mail_mailer'],
                'mail.mailers.smtp.host' => $validated['mail_host'] ?? null,
                'mail.mailers.smtp.port' => $validated['mail_port'] ?? null,
                'mail.mailers.smtp.username' => $validated['mail_username'] ?? null,
                'mail.mailers.smtp.password' => $validated['mail_password'] ?? null,
                'mail.mailers.smtp.encryption' => $validated['mail_encryption'] ?? null,
                'mail.from.address' => $validated['mail_from_address'],
                'mail.from.name' => $validated['mail_from_name'],
            ]);

            Mail::raw('This is a test email from the OsintWeb installation wizard. If you received this, your email configuration is working correctly!', function ($message) use ($validated) {
                $message->to($validated['test_email'])
                    ->subject('OsintWeb - Installation Test Email');
            });

            return response()->json([
                'success' => true,
                'message' => 'Test email sent successfully! Please check your inbox.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send test email: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Save email configuration
     */
    public function save(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'mail_mailer' => 'required|string|in:smtp,mailgun,postmark,ses,log',
            'mail_host' => 'required_if:mail_mailer,smtp|nullable|string',
            'mail_port' => 'required_if:mail_mailer,smtp|nullable|integer',
            'mail_username' => 'nullable|string',
            'mail_password' => 'nullable|string',
            'mail_encryption' => 'required_if:mail_mailer,smtp|nullable|string|in:tls,ssl',
            'mail_from_address' => 'required|email',
            'mail_from_name' => 'required|string',
        ]);

        // Update .env file
        $envData = [
            'MAIL_MAILER' => $validated['mail_mailer'],
            'MAIL_FROM_ADDRESS' => $validated['mail_from_address'],
            'MAIL_FROM_NAME' => $validated['mail_from_name'],
        ];

        if ($validated['mail_mailer'] === 'smtp') {
            $envData['MAIL_HOST'] = $validated['mail_host'] ?? '';
            $envData['MAIL_PORT'] = (string) ($validated['mail_port'] ?? '587');
            $envData['MAIL_USERNAME'] = $validated['mail_username'] ?? '';
            $envData['MAIL_PASSWORD'] = $validated['mail_password'] ?? '';
            $envData['MAIL_ENCRYPTION'] = $validated['mail_encryption'] ?? 'tls';
        }

        $this->environmentWriter->update($envData);

        return redirect()->route('install.search');
    }

    /**
     * Skip email configuration
     */
    public function skip(): RedirectResponse
    {
        // Set to log driver for development
        $this->environmentWriter->update([
            'MAIL_MAILER' => 'log',
            'MAIL_FROM_ADDRESS' => 'noreply@example.com',
            'MAIL_FROM_NAME' => config('app.name', 'OsintWeb'),
        ]);

        return redirect()->route('install.search');
    }
}
