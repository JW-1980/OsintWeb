<?php

declare(strict_types=1);

namespace App\Mail;

/**
 * Weekly digest email
 */
class WeeklyDigestMail extends TemplateMailable
{
    public function __construct(
        protected array $stats = [],
        protected array $topEvents = [],
        protected array $alerts = []
    ) {}

    protected function getTemplateSlug(): string
    {
        return 'weekly-digest';
    }

    protected function getEmailCategory(): string
    {
        return 'digest';
    }

    protected function getCustomData(): array
    {
        return [
            'stats' => $this->stats,
            'top_events' => $this->topEvents,
            'alerts' => $this->alerts,
            'period_start' => now()->subWeek()->format('F j, Y'),
            'period_end' => now()->format('F j, Y'),
        ];
    }

    protected function getDefaultSubject(): string
    {
        return 'Your Weekly Digest - ' . config('app.name');
    }

    protected function getDefaultHtmlContent(): string
    {
        $data = $this->templateData;

        $eventsHtml = '';
        foreach ($data['top_events'] as $event) {
            $title = $event['title'] ?? 'Untitled Event';
            $date = $event['date'] ?? '';
            $url = $event['url'] ?? '#';
            $eventsHtml .= "<li style='margin: 10px 0;'><a href='{$url}'>{$title}</a> - {$date}</li>";
        }
        if (empty($eventsHtml)) {
            $eventsHtml = '<li>No major events this week</li>';
        }

        $newEvents = $data['stats']['new_events'] ?? 0;
        $verifiedEvents = $data['stats']['verified_events'] ?? 0;
        $activeAlerts = $data['stats']['active_alerts'] ?? 0;

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
        .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin: 20px 0; }
        .stat-box { background: white; padding: 15px; border-radius: 8px; text-align: center; border: 1px solid #e5e7eb; }
        .stat-number { font-size: 24px; font-weight: bold; color: #2563eb; }
        .stat-label { font-size: 12px; color: #6b7280; }
        .section { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border: 1px solid #e5e7eb; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Weekly Digest</h1>
            <p style="margin: 0; opacity: 0.9;">{$data['period_start']} - {$data['period_end']}</p>
        </div>
        <div class="content">
            <p>Hello {$data['user_first_name']},</p>
            <p>Here's your weekly summary of activity on {$data['app_name']}:</p>

            <div class="stats-grid">
                <div class="stat-box">
                    <div class="stat-number">{$newEvents}</div>
                    <div class="stat-label">New Events</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number">{$verifiedEvents}</div>
                    <div class="stat-label">Verified</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number">{$activeAlerts}</div>
                    <div class="stat-label">Alerts</div>
                </div>
            </div>

            <div class="section">
                <h3 style="margin-top: 0;">Notable Events This Week</h3>
                <ul>
                    {$eventsHtml}
                </ul>
            </div>

            <p style="text-align: center; margin: 30px 0;">
                <a href="{$data['app_url']}/dashboard" class="button">View Dashboard</a>
            </p>
        </div>
        <div class="footer">
            <p>&copy; {$data['year']} {$data['app_name']}. All rights reserved.</p>
            <p><a href="{$data['unsubscribe_url']}">Unsubscribe from digest</a> | <a href="{$data['preferences_url']}">Email Preferences</a></p>
        </div>
    </div>
</body>
</html>
HTML;
    }
}
