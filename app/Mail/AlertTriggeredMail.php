<?php

declare(strict_types=1);

namespace App\Mail;

/**
 * Alert triggered notification email
 */
class AlertTriggeredMail extends TemplateMailable
{
    public function __construct(
        protected string $alertName,
        protected string $alertType,
        protected array $triggerData = [],
        protected string $viewUrl = ''
    ) {}

    protected function getTemplateSlug(): string
    {
        return 'alert-triggered';
    }

    protected function getEmailCategory(): string
    {
        return 'alerts';
    }

    protected function getCustomData(): array
    {
        return [
            'alert_name' => $this->alertName,
            'alert_type' => $this->alertType,
            'trigger_data' => $this->triggerData,
            'view_url' => $this->viewUrl,
            'triggered_at' => now()->format('F j, Y \a\t g:i A T'),
        ];
    }

    protected function getDefaultSubject(): string
    {
        return 'Alert: ' . $this->alertName . ' - ' . config('app.name');
    }

    protected function getDefaultHtmlContent(): string
    {
        $data = $this->templateData;
        $triggerDetails = '';
        foreach ($data['trigger_data'] as $key => $value) {
            $label = ucwords(str_replace('_', ' ', $key));
            $triggerDetails .= "<dt>{$label}:</dt><dd>{$value}</dd>";
        }

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #f59e0b; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f9fafb; }
        .button { display: inline-block; padding: 12px 24px; background: #2563eb; color: white; text-decoration: none; border-radius: 6px; }
        .footer { padding: 20px; text-align: center; font-size: 12px; color: #666; }
        .alert-box { background: #fef3c7; border: 1px solid #f59e0b; padding: 15px; border-radius: 4px; margin: 20px 0; }
        .info-box { background: #f3f4f6; padding: 15px; border-radius: 4px; margin: 20px 0; }
        .info-box dt { font-weight: bold; color: #374151; }
        .info-box dd { margin: 5px 0 15px 0; color: #6b7280; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Alert Triggered</h1>
        </div>
        <div class="content">
            <p>Hello {$data['user_first_name']},</p>
            <div class="alert-box">
                <strong>Alert: {$data['alert_name']}</strong>
                <p>Type: {$data['alert_type']}</p>
                <p>Triggered at: {$data['triggered_at']}</p>
            </div>
            <div class="info-box">
                <dl>
                    {$triggerDetails}
                </dl>
            </div>
            <p style="text-align: center; margin: 30px 0;">
                <a href="{$data['view_url']}" class="button">View Details</a>
            </p>
        </div>
        <div class="footer">
            <p>&copy; {$data['year']} {$data['app_name']}. All rights reserved.</p>
            <p><a href="{$data['unsubscribe_url']}">Unsubscribe from alerts</a> | <a href="{$data['preferences_url']}">Manage alert preferences</a></p>
        </div>
    </div>
</body>
</html>
HTML;
    }
}
