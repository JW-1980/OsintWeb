<?php

declare(strict_types=1);

namespace App\Mail;

/**
 * Data export ready notification email
 */
class DataExportReadyMail extends TemplateMailable
{
    public function __construct(
        protected string $downloadUrl,
        protected string $expiresAt,
        protected string $format = 'JSON'
    ) {}

    protected function getTemplateSlug(): string
    {
        return 'data-export-ready';
    }

    protected function getEmailCategory(): string
    {
        return 'transactional';
    }

    protected function getCustomData(): array
    {
        return [
            'download_url' => $this->downloadUrl,
            'expires_at' => $this->expiresAt,
            'format' => $this->format,
        ];
    }

    protected function getDefaultSubject(): string
    {
        return 'Your data export is ready - ' . config('app.name');
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
        .header { background: #10b981; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f9fafb; }
        .button { display: inline-block; padding: 12px 24px; background: #10b981; color: white; text-decoration: none; border-radius: 6px; }
        .footer { padding: 20px; text-align: center; font-size: 12px; color: #666; }
        .info-box { background: #ecfdf5; border: 1px solid #10b981; padding: 15px; border-radius: 4px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Your Data Export is Ready</h1>
        </div>
        <div class="content">
            <p>Hello {$data['user_first_name']},</p>
            <p>Your requested data export has been prepared and is ready for download.</p>
            <div class="info-box">
                <p><strong>Format:</strong> {$data['format']}</p>
                <p><strong>Expires:</strong> {$data['expires_at']}</p>
            </div>
            <p style="text-align: center; margin: 30px 0;">
                <a href="{$data['download_url']}" class="button">Download Your Data</a>
            </p>
            <p style="color: #6b7280; font-size: 14px;">
                This download link will expire on {$data['expires_at']}. Please download your data before then.
            </p>
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
