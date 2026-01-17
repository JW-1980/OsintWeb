<?php

declare(strict_types=1);

namespace App\Mail;

/**
 * Password reset email
 */
class PasswordResetMail extends TemplateMailable
{
    public function __construct(
        protected string $resetUrl,
        protected int $expiresInMinutes = 60
    ) {}

    protected function getTemplateSlug(): string
    {
        return 'password-reset';
    }

    protected function getEmailCategory(): string
    {
        return 'authentication';
    }

    protected function getCustomData(): array
    {
        return [
            'reset_url' => $this->resetUrl,
            'expires_in_minutes' => $this->expiresInMinutes,
        ];
    }

    protected function getDefaultSubject(): string
    {
        return 'Reset your password - ' . config('app.name');
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
        .button { display: inline-block; padding: 12px 24px; background: #dc2626; color: white; text-decoration: none; border-radius: 6px; }
        .footer { padding: 20px; text-align: center; font-size: 12px; color: #666; }
        .warning { background: #fef3c7; border: 1px solid #f59e0b; padding: 10px; border-radius: 4px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Password Reset Request</h1>
        </div>
        <div class="content">
            <p>Hello {$data['user_first_name']},</p>
            <p>We received a request to reset your password. Click the button below to create a new password:</p>
            <p style="text-align: center; margin: 30px 0;">
                <a href="{$data['reset_url']}" class="button">Reset Password</a>
            </p>
            <div class="warning">
                <strong>Security Notice:</strong> This link will expire in {$data['expires_in_minutes']} minutes.
            </div>
            <p>If you didn't request a password reset, please ignore this email or contact support if you have concerns about your account security.</p>
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
