<?php

declare(strict_types=1);

namespace App\Mail;

/**
 * Account deletion confirmation email
 */
class AccountDeletionMail extends TemplateMailable
{
    public function __construct(
        protected string $deletionDate,
        protected string $cancelUrl = ''
    ) {}

    protected function getTemplateSlug(): string
    {
        return 'account-deletion';
    }

    protected function getEmailCategory(): string
    {
        return 'transactional';
    }

    protected function getCustomData(): array
    {
        return [
            'deletion_date' => $this->deletionDate,
            'cancel_url' => $this->cancelUrl,
            'support_email' => config('mail.from.address'),
        ];
    }

    protected function getDefaultSubject(): string
    {
        return 'Account deletion scheduled - ' . config('app.name');
    }

    protected function getDefaultHtmlContent(): string
    {
        $data = $this->templateData;
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #dc2626; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f9fafb; }
        .button { display: inline-block; padding: 12px 24px; background: #2563eb; color: white; text-decoration: none; border-radius: 6px; }
        .footer { padding: 20px; text-align: center; font-size: 12px; color: #666; }
        .warning { background: #fef2f2; border: 1px solid #dc2626; padding: 15px; border-radius: 4px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Account Deletion Scheduled</h1>
        </div>
        <div class="content">
            <p>Hello {$data['user_first_name']},</p>
            <p>We've received your request to delete your account. Your account and all associated data will be permanently deleted on:</p>
            <div class="warning">
                <p style="text-align: center; font-size: 18px; font-weight: bold; margin: 0;">
                    {$data['deletion_date']}
                </p>
            </div>
            <p><strong>What happens next:</strong></p>
            <ul>
                <li>All your personal data will be permanently removed</li>
                <li>Your events, comments, and contributions will be anonymized or deleted</li>
                <li>This action cannot be undone after the deletion date</li>
            </ul>
            <p>If you changed your mind and want to keep your account, you can cancel the deletion request before the scheduled date:</p>
            <p style="text-align: center; margin: 30px 0;">
                <a href="{$data['cancel_url']}" class="button">Cancel Deletion Request</a>
            </p>
            <p>If you have any questions, please contact us at <a href="mailto:{$data['support_email']}">{$data['support_email']}</a>.</p>
        </div>
        <div class="footer">
            <p>&copy; {$data['year']} {$data['app_name']}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
}
