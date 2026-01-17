<?php

declare(strict_types=1);

namespace App\Mail;

/**
 * Email verification mail
 */
class EmailVerificationMail extends TemplateMailable
{
    public function __construct(
        protected string $verificationUrl
    ) {}

    protected function getTemplateSlug(): string
    {
        return 'email-verification';
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
        return 'Verify your email address - ' . config('app.name');
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
            <h1>Verify Your Email</h1>
        </div>
        <div class="content">
            <p>Hello {$data['user_first_name']},</p>
            <p>Please click the button below to verify your email address:</p>
            <p style="text-align: center; margin: 30px 0;">
                <a href="{$data['verification_url']}" class="button">Verify Email Address</a>
            </p>
            <p>This link will expire in 24 hours.</p>
            <p>If you didn't request this verification, you can safely ignore this email.</p>
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
