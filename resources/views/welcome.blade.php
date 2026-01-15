<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'OsintWeb') }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
        }
        .container {
            text-align: center;
            padding: 40px;
            max-width: 600px;
        }
        .logo {
            font-size: 48px;
            font-weight: 700;
            margin-bottom: 16px;
            background: linear-gradient(90deg, #e94560, #ff6b6b);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .tagline {
            font-size: 18px;
            color: #a0a0a0;
            margin-bottom: 40px;
        }
        .card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            padding: 32px;
            backdrop-filter: blur(10px);
        }
        .card h2 {
            font-size: 24px;
            margin-bottom: 16px;
        }
        .card p {
            color: #a0a0a0;
            margin-bottom: 24px;
            line-height: 1.6;
        }
        .btn {
            display: inline-block;
            padding: 14px 32px;
            background: linear-gradient(90deg, #e94560, #ff6b6b);
            color: #fff;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(233, 69, 96, 0.3);
        }
        .features {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-top: 40px;
        }
        .feature {
            padding: 16px;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 8px;
        }
        .feature-icon {
            font-size: 24px;
            margin-bottom: 8px;
        }
        .feature-label {
            font-size: 12px;
            color: #a0a0a0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">OsintWeb</div>
        <p class="tagline">Open Source Intelligence Platform</p>

        <div class="card">
            <h2>Welcome</h2>
            <p>
                Your intelligence platform is ready to be configured.
                Click below to start the installation wizard.
            </p>
            <a href="{{ route('install.welcome') }}" class="btn">
                Start Installation
            </a>
        </div>

        <div class="features">
            <div class="feature">
                <div class="feature-icon">&#127758;</div>
                <div class="feature-label">Interactive Maps</div>
            </div>
            <div class="feature">
                <div class="feature-icon">&#128736;</div>
                <div class="feature-label">Equipment DB</div>
            </div>
            <div class="feature">
                <div class="feature-icon">&#128202;</div>
                <div class="feature-label">Event Tracking</div>
            </div>
        </div>
    </div>
</body>
</html>
