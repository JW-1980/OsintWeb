@extends('install.layout', ['progress' => 12.5])

@section('content')
<div class="step-indicator">
    <div class="step active">
        <div class="step-number">1</div>
        <div class="step-label">Welcome</div>
    </div>
    <div class="step">
        <div class="step-number">2</div>
        <div class="step-label">Database</div>
    </div>
    <div class="step">
        <div class="step-number">3</div>
        <div class="step-label">Migrations</div>
    </div>
    <div class="step">
        <div class="step-number">4</div>
        <div class="step-label">Settings</div>
    </div>
    <div class="step">
        <div class="step-number">5</div>
        <div class="step-label">Admin</div>
    </div>
    <div class="step">
        <div class="step-number">6</div>
        <div class="step-label">Email</div>
    </div>
    <div class="step">
        <div class="step-number">7</div>
        <div class="step-label">Search</div>
    </div>
    <div class="step">
        <div class="step-number">8</div>
        <div class="step-label">Finish</div>
    </div>
</div>

<h2 style="margin-bottom: 24px;">Welcome to OsintWeb</h2>

<div class="info-box">
    <strong>Before you begin, please ensure you have:</strong>
    <ul>
        <li>MySQL 8.0+ database credentials</li>
        <li>SMTP server details (optional, for email notifications)</li>
        <li>Admin account information ready</li>
    </ul>
</div>

<div class="card">
    <h3>System Requirements Check</h3>

    <div style="margin-top: 16px;">
        <h4 style="font-size: 14px; margin-bottom: 12px; color: #555;">PHP Version</h4>
        <div class="requirement-check {{ $requirements['php_version']['passed'] ? 'passed' : 'failed' }}">
            <div>
                <strong>PHP {{ $requirements['php_version']['required'] }}+</strong>
                <span style="color: #777; font-size: 12px; margin-left: 8px;">
                    (Current: {{ $requirements['php_version']['current'] }})
                </span>
            </div>
            <div class="status-icon {{ $requirements['php_version']['passed'] ? 'passed' : 'failed' }}">
                {{ $requirements['php_version']['passed'] ? '✓' : '✗' }}
            </div>
        </div>
    </div>

    <div style="margin-top: 24px;">
        <h4 style="font-size: 14px; margin-bottom: 12px; color: #555;">PHP Extensions</h4>
        @foreach($requirements['extensions'] as $extension => $loaded)
        <div class="requirement-check {{ $loaded ? 'passed' : 'failed' }}">
            <div>
                <strong>{{ $extension }}</strong>
            </div>
            <div class="status-icon {{ $loaded ? 'passed' : 'failed' }}">
                {{ $loaded ? '✓' : '✗' }}
            </div>
        </div>
        @endforeach
    </div>

    <div style="margin-top: 24px;">
        <h4 style="font-size: 14px; margin-bottom: 12px; color: #555;">Permissions</h4>
        @foreach($requirements['permissions'] as $path => $writable)
        <div class="requirement-check {{ $writable ? 'passed' : 'failed' }}">
            <div>
                <strong>{{ ucfirst($path) }}</strong>
                <span style="color: #777; font-size: 12px; margin-left: 8px;">writable</span>
            </div>
            <div class="status-icon {{ $writable ? 'passed' : 'failed' }}">
                {{ $writable ? '✓' : '✗' }}
            </div>
        </div>
        @endforeach
    </div>
</div>

@if(!empty($messages))
<div class="alert alert-error">
    <strong>Requirements Not Met:</strong>
    <ul style="margin-top: 8px; margin-left: 20px;">
        @foreach($messages as $message)
        <li>{{ $message }}</li>
        @endforeach
    </ul>
</div>
@endif

@if($allPassed)
<div class="alert alert-success">
    All system requirements are met! You can proceed with the installation.
</div>
@endif

<div class="btn-group">
    <div></div>
    <a href="{{ route('install.database') }}" class="btn btn-primary" {{ !$allPassed ? 'style=pointer-events:none;opacity:0.5;' : '' }}>
        Start Installation →
    </a>
</div>
@endsection
