<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unsubscribe - {{ config('app.name') }}</title>
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
            background: #2563eb;
            color: white;
            padding: 24px;
            text-align: center;
        }
        .header h1 { font-size: 24px; font-weight: 600; }
        .content { padding: 32px; }
        .content p { color: #4b5563; line-height: 1.6; margin-bottom: 16px; }
        .email { font-weight: 600; color: #1f2937; }
        .type-badge {
            display: inline-block;
            padding: 4px 12px;
            background: #dbeafe;
            color: #1e40af;
            border-radius: 20px;
            font-size: 14px;
            margin: 8px 0;
        }
        .form-group { margin: 24px 0; }
        .form-group label { display: block; font-weight: 500; color: #374151; margin-bottom: 8px; }
        .form-group select, .form-group textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
        }
        .buttons { display: flex; gap: 12px; margin-top: 24px; }
        .btn {
            flex: 1;
            padding: 12px 20px;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            border: none;
            font-size: 14px;
        }
        .btn-primary { background: #dc2626; color: white; }
        .btn-primary:hover { background: #b91c1c; }
        .btn-secondary { background: #f3f4f6; color: #374151; }
        .btn-secondary:hover { background: #e5e7eb; }
        .footer {
            padding: 20px 32px;
            background: #f9fafb;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 13px;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <h1>Unsubscribe</h1>
        </div>
        <div class="content">
            <p>You are about to unsubscribe <span class="email">{{ $email }}</span> from:</p>
            <div class="type-badge">{{ $typeName }}</div>

            <form method="POST" action="{{ request()->fullUrl() }}">
                @csrf
                <div class="form-group">
                    <label for="type">Unsubscribe from:</label>
                    <select name="type" id="type">
                        @foreach($types as $key => $label)
                            <option value="{{ $key }}" {{ $type === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="reason">Reason (optional):</label>
                    <textarea name="reason" id="reason" rows="3" placeholder="Help us improve by sharing why you're unsubscribing..."></textarea>
                </div>

                <div class="buttons">
                    <a href="{{ config('app.url') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Unsubscribe</button>
                </div>
            </form>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
