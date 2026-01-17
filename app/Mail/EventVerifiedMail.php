<?php

declare(strict_types=1);

namespace App\Mail;

/**
 * Event verified notification email
 */
class EventVerifiedMail extends TemplateMailable
{
    public function __construct(
        protected string $eventTitle,
        protected string $eventUuid,
        protected string $verifiedBy,
        protected string $status = 'verified'
    ) {}

    protected function getTemplateSlug(): string
    {
        return 'event-verified';
    }

    protected function getEmailCategory(): string
    {
        return 'notifications';
    }

    protected function getCustomData(): array
    {
        return [
            'event_title' => $this->eventTitle,
            'event_uuid' => $this->eventUuid,
            'verified_by' => $this->verifiedBy,
            'status' => $this->status,
            'view_url' => config('app.url') . '/events/' . $this->eventUuid,
        ];
    }

    protected function getDefaultSubject(): string
    {
        return 'Your event has been ' . $this->status . ' - ' . config('app.name');
    }

    protected function getDefaultHtmlContent(): string
    {
        $data = $this->templateData;
        $statusColor = $data['status'] === 'verified' ? '#10b981' : '#f59e0b';
        $statusIcon = $data['status'] === 'verified' ? '&#10003;' : '&#9888;';

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: {$statusColor}; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f9fafb; }
        .button { display: inline-block; padding: 12px 24px; background: #2563eb; color: white; text-decoration: none; border-radius: 6px; }
        .footer { padding: 20px; text-align: center; font-size: 12px; color: #666; }
        .status-badge { display: inline-block; padding: 5px 15px; background: {$statusColor}; color: white; border-radius: 20px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{$statusIcon} Event {$data['status']}</h1>
        </div>
        <div class="content">
            <p>Hello {$data['user_first_name']},</p>
            <p>Your submitted event has been reviewed:</p>
            <div style="background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border: 1px solid #e5e7eb;">
                <h2 style="margin: 0 0 10px 0;">{$data['event_title']}</h2>
                <p style="margin: 0;">
                    Status: <span class="status-badge">{$data['status']}</span>
                </p>
                <p style="color: #6b7280; margin: 10px 0 0 0;">Reviewed by: {$data['verified_by']}</p>
            </div>
            <p style="text-align: center; margin: 30px 0;">
                <a href="{$data['view_url']}" class="button">View Event</a>
            </p>
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
