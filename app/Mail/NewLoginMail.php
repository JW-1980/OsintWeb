<?php

declare(strict_types=1);

namespace App\Mail;

/**
 * New login notification email
 */
class NewLoginMail extends TemplateMailable
{
    public function __construct(
        protected string $ipAddress,
        protected string $userAgent,
        protected string $location = 'Unknown'
    ) {}

    protected function getTemplateSlug(): string
    {
        return 'new-login';
    }

    protected function getEmailCategory(): string
    {
        return 'notifications';
    }

    protected function getCustomData(): array
    {
        return [
            'ip_address' => $this->ipAddress,
            'user_agent' => $this->userAgent,
            'location' => $this->location,
            'login_time' => now()->format('F j, Y \a\t g:i A T'),
            'security_url' => config('app.url') . '/settings/security',
        ];
    }

    protected function getDefaultSubject(): string
    {
        return 'New login to your account - ' . config('app.name');
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
        .info-box { background: #f3f4f6; padding: 15px; border-radius: 4px; margin: 20px 0; }
        .info-box dt { font-weight: bold; color: #374151; }
        .info-box dd { margin: 5px 0 15px 0; color: #6b7280; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>New Login Detected</h1>
        </div>
        <div class="content">
            <p>Hello {$data['user_first_name']},</p>
            <p>We detected a new login to your account. Here are the details:</p>
            <div class="info-box">
                <dl>
                    <dt>Time:</dt>
                    <dd>{$data['login_time']}</dd>
                    <dt>Location:</dt>
                    <dd>{$data['location']}</dd>
                    <dt>IP Address:</dt>
                    <dd>{$data['ip_address']}</dd>
                    <dt>Device:</dt>
                    <dd>{$data['user_agent']}</dd>
                </dl>
            </div>
            <p>If this was you, you can ignore this email. If you don't recognize this activity, please secure your account immediately:</p>
            <p style="text-align: center; margin: 30px 0;">
                <a href="{$data['security_url']}" class="button">Review Security Settings</a>
            </p>
        </div>
        <div class="footer">
            <p>&copy; {$data['year']} {$data['app_name']}. All rights reserved.</p>
            <p><a href="{$data['unsubscribe_url']}">Unsubscribe from login alerts</a></p>
        </div>
    </div>
</body>
</html>
HTML;
    }
}
