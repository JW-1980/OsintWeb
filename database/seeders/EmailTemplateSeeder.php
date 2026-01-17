<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = $this->getDefaultTemplates();

        foreach ($templates as $template) {
            EmailTemplate::updateOrCreate(
                ['slug' => $template['slug']],
                $template
            );
        }
    }

    protected function getDefaultTemplates(): array
    {
        return [
            [
                'slug' => 'welcome',
                'name' => 'Welcome Email',
                'description' => 'Sent to new users upon registration',
                'subject' => 'Welcome to {{ app_name }}',
                'category' => 'authentication',
                'is_system' => true,
                'is_active' => true,
                'variables' => ['verification_url'],
                'html_content' => $this->getWelcomeHtml(),
                'text_content' => $this->getWelcomeText(),
            ],
            [
                'slug' => 'email-verification',
                'name' => 'Email Verification',
                'description' => 'Sent when user needs to verify their email',
                'subject' => 'Verify your email address - {{ app_name }}',
                'category' => 'authentication',
                'is_system' => true,
                'is_active' => true,
                'variables' => ['verification_url'],
                'html_content' => $this->getEmailVerificationHtml(),
                'text_content' => $this->getEmailVerificationText(),
            ],
            [
                'slug' => 'password-reset',
                'name' => 'Password Reset',
                'description' => 'Sent when user requests a password reset',
                'subject' => 'Reset your password - {{ app_name }}',
                'category' => 'authentication',
                'is_system' => true,
                'is_active' => true,
                'variables' => ['reset_url', 'expires_in_minutes'],
                'html_content' => $this->getPasswordResetHtml(),
                'text_content' => $this->getPasswordResetText(),
            ],
            [
                'slug' => 'account-locked',
                'name' => 'Account Locked',
                'description' => 'Sent when user account is locked for security',
                'subject' => 'Account Security Alert - {{ app_name }}',
                'category' => 'authentication',
                'is_system' => true,
                'is_active' => true,
                'variables' => ['lock_reason', 'unlock_url', 'support_email'],
                'html_content' => $this->getAccountLockedHtml(),
                'text_content' => $this->getAccountLockedText(),
            ],
            [
                'slug' => 'new-login',
                'name' => 'New Login Alert',
                'description' => 'Sent when user logs in from a new device or location',
                'subject' => 'New login to your account - {{ app_name }}',
                'category' => 'notification',
                'is_system' => true,
                'is_active' => true,
                'variables' => ['ip_address', 'user_agent', 'location', 'login_time', 'security_url'],
                'html_content' => $this->getNewLoginHtml(),
                'text_content' => $this->getNewLoginText(),
            ],
            [
                'slug' => 'alert-triggered',
                'name' => 'Alert Triggered',
                'description' => 'Sent when a user-configured alert is triggered',
                'subject' => 'Alert: {{ alert_name }} - {{ app_name }}',
                'category' => 'alert',
                'is_system' => true,
                'is_active' => true,
                'variables' => ['alert_name', 'alert_type', 'trigger_data', 'view_url', 'triggered_at'],
                'html_content' => $this->getAlertTriggeredHtml(),
                'text_content' => $this->getAlertTriggeredText(),
            ],
            [
                'slug' => 'event-verified',
                'name' => 'Event Verified',
                'description' => 'Sent when a user submitted event is verified',
                'subject' => 'Your event has been {{ status }} - {{ app_name }}',
                'category' => 'notification',
                'is_system' => true,
                'is_active' => true,
                'variables' => ['event_title', 'event_uuid', 'verified_by', 'status', 'view_url'],
                'html_content' => $this->getEventVerifiedHtml(),
                'text_content' => $this->getEventVerifiedText(),
            ],
            [
                'slug' => 'weekly-digest',
                'name' => 'Weekly Digest',
                'description' => 'Weekly summary of platform activity',
                'subject' => 'Your Weekly Digest - {{ app_name }}',
                'category' => 'digest',
                'is_system' => true,
                'is_active' => true,
                'variables' => ['stats', 'top_events', 'alerts', 'period_start', 'period_end'],
                'html_content' => $this->getWeeklyDigestHtml(),
                'text_content' => $this->getWeeklyDigestText(),
            ],
            [
                'slug' => 'data-export-ready',
                'name' => 'Data Export Ready',
                'description' => 'Sent when user data export is ready for download',
                'subject' => 'Your data export is ready - {{ app_name }}',
                'category' => 'transactional',
                'is_system' => true,
                'is_active' => true,
                'variables' => ['download_url', 'expires_at', 'format'],
                'html_content' => $this->getDataExportReadyHtml(),
                'text_content' => $this->getDataExportReadyText(),
            ],
            [
                'slug' => 'account-deletion',
                'name' => 'Account Deletion',
                'description' => 'Sent when account deletion is scheduled',
                'subject' => 'Account deletion scheduled - {{ app_name }}',
                'category' => 'transactional',
                'is_system' => true,
                'is_active' => true,
                'variables' => ['deletion_date', 'cancel_url', 'support_email'],
                'html_content' => $this->getAccountDeletionHtml(),
                'text_content' => $this->getAccountDeletionText(),
            ],
        ];
    }

    protected function getBaseHtmlTemplate(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #1f2937; margin: 0; padding: 0; background: #f3f4f6; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .card { background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .header { background: {{header_color}}; color: white; padding: 24px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; font-weight: 600; }
        .content { padding: 24px; }
        .button { display: inline-block; padding: 12px 28px; background: #2563eb; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: 500; margin: 16px 0; }
        .button:hover { background: #1d4ed8; }
        .footer { padding: 20px 24px; text-align: center; font-size: 13px; color: #6b7280; border-top: 1px solid #e5e7eb; }
        .footer a { color: #2563eb; text-decoration: none; }
        .info-box { background: #f9fafb; padding: 16px; border-radius: 6px; margin: 16px 0; border: 1px solid #e5e7eb; }
        .alert-box { background: #fef2f2; padding: 16px; border-radius: 6px; margin: 16px 0; border: 1px solid #fecaca; }
        .success-box { background: #ecfdf5; padding: 16px; border-radius: 6px; margin: 16px 0; border: 1px solid #a7f3d0; }
        .warning-box { background: #fffbeb; padding: 16px; border-radius: 6px; margin: 16px 0; border: 1px solid #fde68a; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            {{content}}
        </div>
    </div>
</body>
</html>
HTML;
    }

    protected function getWelcomeHtml(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #1f2937; margin: 0; padding: 0; background: #f3f4f6; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .card { background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .header { background: #2563eb; color: white; padding: 24px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; font-weight: 600; }
        .content { padding: 24px; }
        .button { display: inline-block; padding: 12px 28px; background: #2563eb; color: #ffffff !important; text-decoration: none; border-radius: 6px; font-weight: 500; }
        .footer { padding: 20px 24px; text-align: center; font-size: 13px; color: #6b7280; border-top: 1px solid #e5e7eb; }
        .footer a { color: #2563eb; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="header">
                <h1>Welcome to {{ app_name }}</h1>
            </div>
            <div class="content">
                <p>Hello {{ user_first_name }},</p>
                <p>Thank you for joining {{ app_name }}! We're excited to have you on board.</p>
                <p>To get started, please verify your email address by clicking the button below:</p>
                <p style="text-align: center; margin: 30px 0;">
                    <a href="{{ verification_url }}" class="button">Verify Email Address</a>
                </p>
                <p>If you didn't create an account, you can safely ignore this email.</p>
                <p>Welcome aboard!<br>The {{ app_name }} Team</p>
            </div>
            <div class="footer">
                <p>&copy; {{ year }} {{ app_name }}. All rights reserved.</p>
                <p><a href="{{ unsubscribe_url }}">Unsubscribe</a> | <a href="{{ preferences_url }}">Email Preferences</a></p>
            </div>
        </div>
    </div>
</body>
</html>
HTML;
    }

    protected function getWelcomeText(): string
    {
        return <<<'TEXT'
Welcome to {{ app_name }}!

Hello {{ user_first_name }},

Thank you for joining {{ app_name }}! We're excited to have you on board.

To get started, please verify your email address by visiting:
{{ verification_url }}

If you didn't create an account, you can safely ignore this email.

Welcome aboard!
The {{ app_name }} Team

---
© {{ year }} {{ app_name }}. All rights reserved.
To unsubscribe: {{ unsubscribe_url }}
TEXT;
    }

    protected function getEmailVerificationHtml(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #1f2937; margin: 0; padding: 0; background: #f3f4f6; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .card { background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .header { background: #2563eb; color: white; padding: 24px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; font-weight: 600; }
        .content { padding: 24px; }
        .button { display: inline-block; padding: 12px 28px; background: #2563eb; color: #ffffff !important; text-decoration: none; border-radius: 6px; font-weight: 500; }
        .footer { padding: 20px 24px; text-align: center; font-size: 13px; color: #6b7280; border-top: 1px solid #e5e7eb; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="header">
                <h1>Verify Your Email</h1>
            </div>
            <div class="content">
                <p>Hello {{ user_first_name }},</p>
                <p>Please click the button below to verify your email address:</p>
                <p style="text-align: center; margin: 30px 0;">
                    <a href="{{ verification_url }}" class="button">Verify Email Address</a>
                </p>
                <p>This link will expire in 24 hours.</p>
                <p>If you didn't request this verification, you can safely ignore this email.</p>
            </div>
            <div class="footer">
                <p>&copy; {{ year }} {{ app_name }}. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>
HTML;
    }

    protected function getEmailVerificationText(): string
    {
        return <<<'TEXT'
Verify Your Email

Hello {{ user_first_name }},

Please verify your email address by visiting:
{{ verification_url }}

This link will expire in 24 hours.

If you didn't request this verification, you can safely ignore this email.

---
© {{ year }} {{ app_name }}. All rights reserved.
TEXT;
    }

    protected function getPasswordResetHtml(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #1f2937; margin: 0; padding: 0; background: #f3f4f6; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .card { background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .header { background: #dc2626; color: white; padding: 24px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; font-weight: 600; }
        .content { padding: 24px; }
        .button { display: inline-block; padding: 12px 28px; background: #dc2626; color: #ffffff !important; text-decoration: none; border-radius: 6px; font-weight: 500; }
        .warning-box { background: #fffbeb; padding: 16px; border-radius: 6px; margin: 16px 0; border: 1px solid #fde68a; }
        .footer { padding: 20px 24px; text-align: center; font-size: 13px; color: #6b7280; border-top: 1px solid #e5e7eb; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="header">
                <h1>Password Reset Request</h1>
            </div>
            <div class="content">
                <p>Hello {{ user_first_name }},</p>
                <p>We received a request to reset your password. Click the button below to create a new password:</p>
                <p style="text-align: center; margin: 30px 0;">
                    <a href="{{ reset_url }}" class="button">Reset Password</a>
                </p>
                <div class="warning-box">
                    <strong>Security Notice:</strong> This link will expire in {{ expires_in_minutes }} minutes.
                </div>
                <p>If you didn't request a password reset, please ignore this email or contact support if you have concerns about your account security.</p>
            </div>
            <div class="footer">
                <p>&copy; {{ year }} {{ app_name }}. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>
HTML;
    }

    protected function getPasswordResetText(): string
    {
        return <<<'TEXT'
Password Reset Request

Hello {{ user_first_name }},

We received a request to reset your password. Visit the following link to create a new password:
{{ reset_url }}

SECURITY NOTICE: This link will expire in {{ expires_in_minutes }} minutes.

If you didn't request a password reset, please ignore this email or contact support if you have concerns about your account security.

---
© {{ year }} {{ app_name }}. All rights reserved.
TEXT;
    }

    protected function getAccountLockedHtml(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #1f2937; margin: 0; padding: 0; background: #f3f4f6; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .card { background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .header { background: #dc2626; color: white; padding: 24px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; font-weight: 600; }
        .content { padding: 24px; }
        .alert-box { background: #fef2f2; padding: 16px; border-radius: 6px; margin: 16px 0; border: 1px solid #fecaca; }
        .footer { padding: 20px 24px; text-align: center; font-size: 13px; color: #6b7280; border-top: 1px solid #e5e7eb; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="header">
                <h1>Account Locked</h1>
            </div>
            <div class="content">
                <p>Hello {{ user_first_name }},</p>
                <div class="alert-box">
                    <strong>Your account has been temporarily locked.</strong>
                    <p style="margin-bottom: 0;">Reason: {{ lock_reason }}</p>
                </div>
                <p>This security measure helps protect your account from unauthorized access.</p>
                <p>If you believe this was done in error or need assistance, please contact our support team at <a href="mailto:{{ support_email }}">{{ support_email }}</a>.</p>
            </div>
            <div class="footer">
                <p>&copy; {{ year }} {{ app_name }}. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>
HTML;
    }

    protected function getAccountLockedText(): string
    {
        return <<<'TEXT'
Account Locked

Hello {{ user_first_name }},

Your account has been temporarily locked.
Reason: {{ lock_reason }}

This security measure helps protect your account from unauthorized access.

If you believe this was done in error or need assistance, please contact our support team at {{ support_email }}.

---
© {{ year }} {{ app_name }}. All rights reserved.
TEXT;
    }

    protected function getNewLoginHtml(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #1f2937; margin: 0; padding: 0; background: #f3f4f6; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .card { background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .header { background: #2563eb; color: white; padding: 24px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; font-weight: 600; }
        .content { padding: 24px; }
        .info-box { background: #f9fafb; padding: 16px; border-radius: 6px; margin: 16px 0; border: 1px solid #e5e7eb; }
        .info-box dt { font-weight: 600; color: #374151; margin-top: 10px; }
        .info-box dt:first-child { margin-top: 0; }
        .info-box dd { margin: 4px 0 0 0; color: #6b7280; }
        .button { display: inline-block; padding: 12px 28px; background: #2563eb; color: #ffffff !important; text-decoration: none; border-radius: 6px; font-weight: 500; }
        .footer { padding: 20px 24px; text-align: center; font-size: 13px; color: #6b7280; border-top: 1px solid #e5e7eb; }
        .footer a { color: #2563eb; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="header">
                <h1>New Login Detected</h1>
            </div>
            <div class="content">
                <p>Hello {{ user_first_name }},</p>
                <p>We detected a new login to your account. Here are the details:</p>
                <div class="info-box">
                    <dl style="margin: 0;">
                        <dt>Time:</dt>
                        <dd>{{ login_time }}</dd>
                        <dt>Location:</dt>
                        <dd>{{ location }}</dd>
                        <dt>IP Address:</dt>
                        <dd>{{ ip_address }}</dd>
                        <dt>Device:</dt>
                        <dd>{{ user_agent }}</dd>
                    </dl>
                </div>
                <p>If this was you, you can ignore this email. If you don't recognize this activity, please secure your account immediately:</p>
                <p style="text-align: center; margin: 30px 0;">
                    <a href="{{ security_url }}" class="button">Review Security Settings</a>
                </p>
            </div>
            <div class="footer">
                <p>&copy; {{ year }} {{ app_name }}. All rights reserved.</p>
                <p><a href="{{ unsubscribe_url }}">Unsubscribe from login alerts</a></p>
            </div>
        </div>
    </div>
</body>
</html>
HTML;
    }

    protected function getNewLoginText(): string
    {
        return <<<'TEXT'
New Login Detected

Hello {{ user_first_name }},

We detected a new login to your account. Here are the details:

Time: {{ login_time }}
Location: {{ location }}
IP Address: {{ ip_address }}
Device: {{ user_agent }}

If this was you, you can ignore this email.
If you don't recognize this activity, please secure your account immediately: {{ security_url }}

---
© {{ year }} {{ app_name }}. All rights reserved.
To unsubscribe: {{ unsubscribe_url }}
TEXT;
    }

    protected function getAlertTriggeredHtml(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #1f2937; margin: 0; padding: 0; background: #f3f4f6; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .card { background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .header { background: #f59e0b; color: white; padding: 24px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; font-weight: 600; }
        .content { padding: 24px; }
        .warning-box { background: #fffbeb; padding: 16px; border-radius: 6px; margin: 16px 0; border: 1px solid #fde68a; }
        .button { display: inline-block; padding: 12px 28px; background: #2563eb; color: #ffffff !important; text-decoration: none; border-radius: 6px; font-weight: 500; }
        .footer { padding: 20px 24px; text-align: center; font-size: 13px; color: #6b7280; border-top: 1px solid #e5e7eb; }
        .footer a { color: #2563eb; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="header">
                <h1>Alert Triggered</h1>
            </div>
            <div class="content">
                <p>Hello {{ user_first_name }},</p>
                <div class="warning-box">
                    <strong>Alert: {{ alert_name }}</strong>
                    <p style="margin: 5px 0 0 0;">Type: {{ alert_type }}</p>
                    <p style="margin: 5px 0 0 0;">Triggered at: {{ triggered_at }}</p>
                </div>
                <p style="text-align: center; margin: 30px 0;">
                    <a href="{{ view_url }}" class="button">View Details</a>
                </p>
            </div>
            <div class="footer">
                <p>&copy; {{ year }} {{ app_name }}. All rights reserved.</p>
                <p><a href="{{ unsubscribe_url }}">Unsubscribe from alerts</a> | <a href="{{ preferences_url }}">Manage alert preferences</a></p>
            </div>
        </div>
    </div>
</body>
</html>
HTML;
    }

    protected function getAlertTriggeredText(): string
    {
        return <<<'TEXT'
Alert Triggered

Hello {{ user_first_name }},

Alert: {{ alert_name }}
Type: {{ alert_type }}
Triggered at: {{ triggered_at }}

View details: {{ view_url }}

---
© {{ year }} {{ app_name }}. All rights reserved.
To unsubscribe: {{ unsubscribe_url }}
Manage preferences: {{ preferences_url }}
TEXT;
    }

    protected function getEventVerifiedHtml(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #1f2937; margin: 0; padding: 0; background: #f3f4f6; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .card { background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .header { background: #10b981; color: white; padding: 24px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; font-weight: 600; }
        .content { padding: 24px; }
        .event-card { background: #f9fafb; padding: 20px; border-radius: 8px; margin: 20px 0; border: 1px solid #e5e7eb; }
        .event-card h2 { margin: 0 0 10px 0; font-size: 18px; }
        .status-badge { display: inline-block; padding: 4px 12px; background: #10b981; color: white; border-radius: 20px; font-size: 13px; font-weight: 500; }
        .button { display: inline-block; padding: 12px 28px; background: #2563eb; color: #ffffff !important; text-decoration: none; border-radius: 6px; font-weight: 500; }
        .footer { padding: 20px 24px; text-align: center; font-size: 13px; color: #6b7280; border-top: 1px solid #e5e7eb; }
        .footer a { color: #2563eb; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="header">
                <h1>Event {{ status }}</h1>
            </div>
            <div class="content">
                <p>Hello {{ user_first_name }},</p>
                <p>Your submitted event has been reviewed:</p>
                <div class="event-card">
                    <h2>{{ event_title }}</h2>
                    <p style="margin: 5px 0;">Status: <span class="status-badge">{{ status }}</span></p>
                    <p style="color: #6b7280; margin: 10px 0 0 0;">Reviewed by: {{ verified_by }}</p>
                </div>
                <p style="text-align: center; margin: 30px 0;">
                    <a href="{{ view_url }}" class="button">View Event</a>
                </p>
            </div>
            <div class="footer">
                <p>&copy; {{ year }} {{ app_name }}. All rights reserved.</p>
                <p><a href="{{ unsubscribe_url }}">Unsubscribe</a> | <a href="{{ preferences_url }}">Email Preferences</a></p>
            </div>
        </div>
    </div>
</body>
</html>
HTML;
    }

    protected function getEventVerifiedText(): string
    {
        return <<<'TEXT'
Event {{ status }}

Hello {{ user_first_name }},

Your submitted event has been reviewed:

Event: {{ event_title }}
Status: {{ status }}
Reviewed by: {{ verified_by }}

View event: {{ view_url }}

---
© {{ year }} {{ app_name }}. All rights reserved.
To unsubscribe: {{ unsubscribe_url }}
TEXT;
    }

    protected function getWeeklyDigestHtml(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #1f2937; margin: 0; padding: 0; background: #f3f4f6; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .card { background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .header { background: #2563eb; color: white; padding: 24px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; font-weight: 600; }
        .header p { margin: 8px 0 0 0; opacity: 0.9; }
        .content { padding: 24px; }
        .button { display: inline-block; padding: 12px 28px; background: #2563eb; color: #ffffff !important; text-decoration: none; border-radius: 6px; font-weight: 500; }
        .footer { padding: 20px 24px; text-align: center; font-size: 13px; color: #6b7280; border-top: 1px solid #e5e7eb; }
        .footer a { color: #2563eb; text-decoration: none; }
        .section { background: #f9fafb; padding: 20px; border-radius: 8px; margin: 20px 0; border: 1px solid #e5e7eb; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="header">
                <h1>Weekly Digest</h1>
                <p>{{ period_start }} - {{ period_end }}</p>
            </div>
            <div class="content">
                <p>Hello {{ user_first_name }},</p>
                <p>Here's your weekly summary of activity on {{ app_name }}.</p>
                <div class="section">
                    <h3 style="margin-top: 0;">This Week's Highlights</h3>
                    <p>Check your dashboard for the latest events, alerts, and updates.</p>
                </div>
                <p style="text-align: center; margin: 30px 0;">
                    <a href="{{ app_url }}/dashboard" class="button">View Dashboard</a>
                </p>
            </div>
            <div class="footer">
                <p>&copy; {{ year }} {{ app_name }}. All rights reserved.</p>
                <p><a href="{{ unsubscribe_url }}">Unsubscribe from digest</a> | <a href="{{ preferences_url }}">Email Preferences</a></p>
            </div>
        </div>
    </div>
</body>
</html>
HTML;
    }

    protected function getWeeklyDigestText(): string
    {
        return <<<'TEXT'
Weekly Digest
{{ period_start }} - {{ period_end }}

Hello {{ user_first_name }},

Here's your weekly summary of activity on {{ app_name }}.

Visit your dashboard for the latest events, alerts, and updates:
{{ app_url }}/dashboard

---
© {{ year }} {{ app_name }}. All rights reserved.
To unsubscribe: {{ unsubscribe_url }}
TEXT;
    }

    protected function getDataExportReadyHtml(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #1f2937; margin: 0; padding: 0; background: #f3f4f6; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .card { background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .header { background: #10b981; color: white; padding: 24px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; font-weight: 600; }
        .content { padding: 24px; }
        .success-box { background: #ecfdf5; padding: 16px; border-radius: 6px; margin: 16px 0; border: 1px solid #a7f3d0; }
        .button { display: inline-block; padding: 12px 28px; background: #10b981; color: #ffffff !important; text-decoration: none; border-radius: 6px; font-weight: 500; }
        .footer { padding: 20px 24px; text-align: center; font-size: 13px; color: #6b7280; border-top: 1px solid #e5e7eb; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="header">
                <h1>Your Data Export is Ready</h1>
            </div>
            <div class="content">
                <p>Hello {{ user_first_name }},</p>
                <p>Your requested data export has been prepared and is ready for download.</p>
                <div class="success-box">
                    <p style="margin: 0;"><strong>Format:</strong> {{ format }}</p>
                    <p style="margin: 5px 0 0 0;"><strong>Expires:</strong> {{ expires_at }}</p>
                </div>
                <p style="text-align: center; margin: 30px 0;">
                    <a href="{{ download_url }}" class="button">Download Your Data</a>
                </p>
                <p style="color: #6b7280; font-size: 14px;">
                    This download link will expire on {{ expires_at }}. Please download your data before then.
                </p>
            </div>
            <div class="footer">
                <p>&copy; {{ year }} {{ app_name }}. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>
HTML;
    }

    protected function getDataExportReadyText(): string
    {
        return <<<'TEXT'
Your Data Export is Ready

Hello {{ user_first_name }},

Your requested data export has been prepared and is ready for download.

Format: {{ format }}
Expires: {{ expires_at }}

Download your data: {{ download_url }}

This download link will expire on {{ expires_at }}. Please download your data before then.

---
© {{ year }} {{ app_name }}. All rights reserved.
TEXT;
    }

    protected function getAccountDeletionHtml(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #1f2937; margin: 0; padding: 0; background: #f3f4f6; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .card { background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .header { background: #dc2626; color: white; padding: 24px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; font-weight: 600; }
        .content { padding: 24px; }
        .alert-box { background: #fef2f2; padding: 16px; border-radius: 6px; margin: 16px 0; border: 1px solid #fecaca; text-align: center; }
        .alert-box p { margin: 0; font-size: 18px; font-weight: 600; }
        .button { display: inline-block; padding: 12px 28px; background: #2563eb; color: #ffffff !important; text-decoration: none; border-radius: 6px; font-weight: 500; }
        .footer { padding: 20px 24px; text-align: center; font-size: 13px; color: #6b7280; border-top: 1px solid #e5e7eb; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="header">
                <h1>Account Deletion Scheduled</h1>
            </div>
            <div class="content">
                <p>Hello {{ user_first_name }},</p>
                <p>We've received your request to delete your account. Your account and all associated data will be permanently deleted on:</p>
                <div class="alert-box">
                    <p>{{ deletion_date }}</p>
                </div>
                <p><strong>What happens next:</strong></p>
                <ul>
                    <li>All your personal data will be permanently removed</li>
                    <li>Your events, comments, and contributions will be anonymized or deleted</li>
                    <li>This action cannot be undone after the deletion date</li>
                </ul>
                <p>If you changed your mind and want to keep your account, you can cancel the deletion request before the scheduled date:</p>
                <p style="text-align: center; margin: 30px 0;">
                    <a href="{{ cancel_url }}" class="button">Cancel Deletion Request</a>
                </p>
                <p>If you have any questions, please contact us at <a href="mailto:{{ support_email }}">{{ support_email }}</a>.</p>
            </div>
            <div class="footer">
                <p>&copy; {{ year }} {{ app_name }}. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>
HTML;
    }

    protected function getAccountDeletionText(): string
    {
        return <<<'TEXT'
Account Deletion Scheduled

Hello {{ user_first_name }},

We've received your request to delete your account. Your account and all associated data will be permanently deleted on:

{{ deletion_date }}

What happens next:
- All your personal data will be permanently removed
- Your events, comments, and contributions will be anonymized or deleted
- This action cannot be undone after the deletion date

If you changed your mind, cancel the deletion request: {{ cancel_url }}

If you have any questions, please contact us at {{ support_email }}.

---
© {{ year }} {{ app_name }}. All rights reserved.
TEXT;
    }
}
