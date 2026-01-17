<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unsubscribe Error - {{ config('app.name') }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f3f4f6;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            max-width: 480px;
            width: 100%;
            overflow: hidden;
        }
        .header {
            background: #dc2626;
            color: white;
            padding: 24px;
            text-align: center;
        }
        .header h1 { font-size: 24px; font-weight: 600; }
        .header .icon { font-size: 48px; margin-bottom: 12px; }
        .content { padding: 32px; text-align: center; }
        .content p { color: #4b5563; line-height: 1.6; margin-bottom: 16px; }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            margin-top: 16px;
            background: #2563eb;
            color: white;
        }
        .btn:hover { background: #1d4ed8; }
        .footer {
            padding: 20px 32px;
            background: #f9fafb;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 13px;
            color: #6b7280;
        }
        .footer a { color: #2563eb; text-decoration: none; }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <div class="icon">&#10007;</div>
            <h1>Unsubscribe Error</h1>
        </div>
        <div class="content">
            <p>{{ $message }}</p>
            <p>If you're trying to unsubscribe from our emails, please use the link in the email you received, or manage your preferences in your account settings.</p>

            <a href="{{ config('app.url') }}" class="btn">Return to {{ config('app.name') }}</a>
        </div>
        <div class="footer">
            <p>Need help? <a href="mailto:{{ config('mail.from.address') }}">Contact Support</a></p>
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
