<?php

declare(strict_types=1);

namespace App\Mail;

/**
 * Welcome email sent when a new user registers
 */
class WelcomeMail extends TemplateMailable
{
    public function __construct(
        protected string $verificationUrl = ''
    ) {}

    protected function getTemplateSlug(): string
    {
        return 'welcome';
    }

    protected function getEmailCategory(): string
    {
        return 'authentication';
    }

    protected function getCustomData(): array
    {
        return [
            'verification_url' => $this->verificationUrl,
        ];
    }

    protected function getDefaultSubject(): string
    {
        return 'Welcome to ' . config('app.name');
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
        .header { background: #2563eb; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f9fafb; }
        .button { display: inline-block; padding: 12px 24px; background: #2563eb; color: white; text-decoration: none; border-radius: 6px; }
        .footer { padding: 20px; text-align: center; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Welcome to {$data['app_name']}</h1>
        </div>
        <div class="content">
            <p>Hello {$data['user_first_name']},</p>
            <p>Thank you for joining {$data['app_name']}! We're excited to have you on board.</p>
            <p>To get started, please verify your email address by clicking the button below:</p>
            <p style="text-align: center; margin: 30px 0;">
                <a href="{$data['verification_url']}" class="button">Verify Email Address</a>
            </p>
            <p>If you didn't create an account, you can safely ignore this email.</p>
        </div>
        <div class="footer">
            <p>&copy; {$data['year']} {$data['app_name']}. All rights reserved.</p>
            <p><a href="{$data['unsubscribe_url']}">Unsubscribe</a> | <a href="{$data['preferences_url']}">Email Preferences</a></p>
        </div>
    </div>
</body>
</html>
HTML;
    }
}
