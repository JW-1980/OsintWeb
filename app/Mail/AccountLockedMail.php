<?php

declare(strict_types=1);

namespace App\Mail;

/**
 * Account locked notification email
 */
class AccountLockedMail extends TemplateMailable
{
    public function __construct(
        protected string $reason,
        protected string $unlockUrl = ''
    ) {}

    protected function getTemplateSlug(): string
    {
        return 'account-locked';
    }

    protected function getEmailCategory(): string
    {
        return 'authentication';
    }

    protected function getCustomData(): array
    {
        return [
            'lock_reason' => $this->reason,
            'unlock_url' => $this->unlockUrl,
            'support_email' => config('mail.from.address'),
        ];
    }

    protected function getDefaultSubject(): string
    {
        return 'Account Security Alert - ' . config('app.name');
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
        .alert { background: #fef2f2; border: 1px solid #dc2626; padding: 15px; border-radius: 4px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Account Locked</h1>
        </div>
        <div class="content">
            <p>Hello {$data['user_first_name']},</p>
            <div class="alert">
                <strong>Your account has been temporarily locked.</strong>
                <p>Reason: {$data['lock_reason']}</p>
            </div>
            <p>This security measure helps protect your account from unauthorized access.</p>
            <p>If you believe this was done in error or need assistance, please contact our support team at <a href="mailto:{$data['support_email']}">{$data['support_email']}</a>.</p>
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
