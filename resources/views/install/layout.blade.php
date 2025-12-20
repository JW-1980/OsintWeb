<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>OsintWeb Installation</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: #333;
        }

        .container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 800px;
            width: 100%;
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }

        .header p {
            opacity: 0.9;
            font-size: 14px;
        }

        .progress-bar {
            background: rgba(255, 255, 255, 0.2);
            height: 4px;
            margin-top: 20px;
            border-radius: 2px;
            overflow: hidden;
        }

        .progress-bar-fill {
            background: white;
            height: 100%;
            transition: width 0.3s ease;
        }

        .content {
            padding: 40px;
        }

        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
            padding: 0 20px;
        }

        .step {
            flex: 1;
            text-align: center;
            position: relative;
        }

        .step::before {
            content: '';
            position: absolute;
            top: 15px;
            left: 50%;
            right: -50%;
            height: 2px;
            background: #e0e0e0;
            z-index: -1;
        }

        .step:last-child::before {
            display: none;
        }

        .step-number {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #e0e0e0;
            color: #999;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 8px;
        }

        .step.active .step-number {
            background: #667eea;
            color: white;
        }

        .step.completed .step-number {
            background: #4caf50;
            color: white;
        }

        .step-label {
            font-size: 11px;
            color: #999;
        }

        .step.active .step-label {
            color: #667eea;
            font-weight: 600;
        }

        .form-group {
            margin-bottom: 24px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 14px;
            color: #555;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="number"],
        input[type="url"],
        select {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.2s;
        }

        input:focus,
        select:focus {
            outline: none;
            border-color: #667eea;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .checkbox-group input[type="checkbox"] {
            width: auto;
        }

        .btn {
            padding: 12px 24px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: #667eea;
            color: white;
        }

        .btn-primary:hover {
            background: #5568d3;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background: #f5f5f5;
            color: #333;
        }

        .btn-secondary:hover {
            background: #e0e0e0;
        }

        .btn-success {
            background: #4caf50;
            color: white;
        }

        .btn-success:hover {
            background: #45a049;
        }

        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .btn-group {
            display: flex;
            gap: 12px;
            justify-content: space-between;
            margin-top: 40px;
        }

        .alert {
            padding: 16px;
            border-radius: 6px;
            margin-bottom: 24px;
        }

        .alert-error {
            background: #ffebee;
            color: #c62828;
            border-left: 4px solid #c62828;
        }

        .alert-success {
            background: #e8f5e9;
            color: #2e7d32;
            border-left: 4px solid #2e7d32;
        }

        .alert-info {
            background: #e3f2fd;
            color: #1565c0;
            border-left: 4px solid #1565c0;
        }

        .requirement-check {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 16px;
            background: #f9f9f9;
            border-radius: 6px;
            margin-bottom: 8px;
        }

        .requirement-check.passed {
            background: #e8f5e9;
        }

        .requirement-check.failed {
            background: #ffebee;
        }

        .status-icon {
            font-size: 18px;
            font-weight: bold;
        }

        .status-icon.passed {
            color: #4caf50;
        }

        .status-icon.failed {
            color: #c62828;
        }

        .loading {
            display: none;
            text-align: center;
            padding: 20px;
        }

        .loading.active {
            display: block;
        }

        .spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #667eea;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 16px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .help-text {
            font-size: 12px;
            color: #777;
            margin-top: 4px;
        }

        .error-text {
            font-size: 12px;
            color: #c62828;
            margin-top: 4px;
        }

        .card {
            background: #f9f9f9;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 24px;
        }

        .card h3 {
            margin-bottom: 16px;
            color: #667eea;
            font-size: 16px;
        }

        .info-box {
            background: #fff8e1;
            border-left: 4px solid #ffa000;
            padding: 16px;
            border-radius: 4px;
            margin-bottom: 24px;
        }

        .info-box strong {
            display: block;
            margin-bottom: 8px;
            color: #f57c00;
        }

        .info-box ul {
            margin-left: 20px;
        }

        .info-box li {
            margin-bottom: 4px;
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>OsintWeb Installation</h1>
            <p>Military Conflict Tracking Platform - Version 1.0.0</p>
            @if(isset($progress))
            <div class="progress-bar">
                <div class="progress-bar-fill" style="width: {{ $progress }}%"></div>
            </div>
            @endif
        </div>

        <div class="content">
            @yield('content')
        </div>
    </div>

    <script>
        // CSRF token setup
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        // Helper function for AJAX requests
        async function sendRequest(url, data = {}, method = 'POST') {
            const options = {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
            };

            if (method !== 'GET') {
                options.body = JSON.stringify(data);
            }

            const response = await fetch(url, options);
            return await response.json();
        }
    </script>
    @stack('scripts')
</body>
</html>
