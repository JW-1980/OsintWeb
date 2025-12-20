@extends('install.layout', ['progress' => 75])

@section('content')
<div class="step-indicator">
    <div class="step completed">
        <div class="step-number">✓</div>
        <div class="step-label">Welcome</div>
    </div>
    <div class="step completed">
        <div class="step-number">✓</div>
        <div class="step-label">Database</div>
    </div>
    <div class="step completed">
        <div class="step-number">✓</div>
        <div class="step-label">Migrations</div>
    </div>
    <div class="step completed">
        <div class="step-number">✓</div>
        <div class="step-label">Settings</div>
    </div>
    <div class="step completed">
        <div class="step-number">✓</div>
        <div class="step-label">Admin</div>
    </div>
    <div class="step active">
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

<h2 style="margin-bottom: 24px;">Email Configuration (Optional)</h2>

<div class="info-box">
    <strong>Email is optional but recommended for:</strong>
    <ul>
        <li>Password reset functionality</li>
        <li>User registration confirmation</li>
        <li>System notifications</li>
    </ul>
    <div style="margin-top: 8px;">You can skip this step and configure email later from the admin panel.</div>
</div>

@if($errors->any())
<div class="alert alert-error">
    @foreach($errors->all() as $error)
    <div>{{ $error }}</div>
    @endforeach
</div>
@endif

<div id="testResult"></div>

<form method="POST" action="{{ route('install.email.save') }}" id="emailForm">
    @csrf

    <div class="card">
        <h3>Email Provider</h3>

        <div class="form-group">
            <label for="mail_mailer">Mail Driver</label>
            <select id="mail_mailer" name="mail_mailer" required>
                <option value="smtp" {{ old('mail_mailer', $mail_driver) == 'smtp' ? 'selected' : '' }}>SMTP</option>
                <option value="mailgun" {{ old('mail_mailer', $mail_driver) == 'mailgun' ? 'selected' : '' }}>Mailgun</option>
                <option value="postmark" {{ old('mail_mailer', $mail_driver) == 'postmark' ? 'selected' : '' }}>Postmark</option>
                <option value="ses" {{ old('mail_mailer', $mail_driver) == 'ses' ? 'selected' : '' }}>Amazon SES</option>
                <option value="log" {{ old('mail_mailer', $mail_driver) == 'log' ? 'selected' : '' }}>Log (Testing)</option>
            </select>
        </div>
    </div>

    <div class="card" id="smtpConfig">
        <h3>SMTP Configuration</h3>

        <div class="form-group">
            <label for="mail_host">SMTP Host</label>
            <input type="text" id="mail_host" name="mail_host" value="{{ old('mail_host', $mail_host) }}">
            <div class="help-text">e.g., smtp.gmail.com, smtp.mailgun.org</div>
        </div>

        <div class="form-group">
            <label for="mail_port">SMTP Port</label>
            <input type="number" id="mail_port" name="mail_port" value="{{ old('mail_port', $mail_port) }}">
            <div class="help-text">Usually 587 for TLS or 465 for SSL</div>
        </div>

        <div class="form-group">
            <label for="mail_username">SMTP Username</label>
            <input type="text" id="mail_username" name="mail_username" value="{{ old('mail_username', $mail_username) }}">
        </div>

        <div class="form-group">
            <label for="mail_password">SMTP Password</label>
            <input type="password" id="mail_password" name="mail_password" autocomplete="off">
        </div>

        <div class="form-group">
            <label for="mail_encryption">Encryption</label>
            <select id="mail_encryption" name="mail_encryption">
                <option value="tls" {{ old('mail_encryption', $mail_encryption) == 'tls' ? 'selected' : '' }}>TLS</option>
                <option value="ssl" {{ old('mail_encryption', $mail_encryption) == 'ssl' ? 'selected' : '' }}>SSL</option>
            </select>
        </div>
    </div>

    <div class="card">
        <h3>Sender Information</h3>

        <div class="form-group">
            <label for="mail_from_address">From Email Address</label>
            <input type="email" id="mail_from_address" name="mail_from_address" value="{{ old('mail_from_address', $mail_from_address) }}" required>
        </div>

        <div class="form-group">
            <label for="mail_from_name">From Name</label>
            <input type="text" id="mail_from_name" name="mail_from_name" value="{{ old('mail_from_name', $mail_from_name) }}" required>
        </div>
    </div>

    <div class="card" id="testEmailSection">
        <h3>Test Email</h3>
        <div class="form-group">
            <label for="test_email">Send test email to:</label>
            <div style="display: flex; gap: 12px;">
                <input type="email" id="test_email" style="flex: 1;">
                <button type="button" class="btn btn-secondary" onclick="testEmail()">Send Test</button>
            </div>
        </div>
    </div>

    <div class="loading" id="loadingIndicator">
        <div class="spinner"></div>
        <p>Sending test email...</p>
    </div>

    <div class="btn-group">
        <a href="{{ route('install.admin') }}" class="btn btn-secondary">← Back</a>
        <div style="display: flex; gap: 12px;">
            <form method="POST" action="{{ route('install.email.skip') }}" style="margin: 0;">
                @csrf
                <button type="submit" class="btn btn-secondary">Skip Email Setup</button>
            </form>
            <button type="submit" class="btn btn-primary">Continue →</button>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
const mailMailerSelect = document.getElementById('mail_mailer');
const smtpConfig = document.getElementById('smtpConfig');
const testEmailSection = document.getElementById('testEmailSection');

// Show/hide SMTP config based on mail driver
mailMailerSelect.addEventListener('change', function() {
    const driver = this.value;
    if (driver === 'smtp') {
        smtpConfig.style.display = 'block';
        testEmailSection.style.display = 'block';
    } else if (driver === 'log') {
        smtpConfig.style.display = 'none';
        testEmailSection.style.display = 'none';
    } else {
        smtpConfig.style.display = 'none';
        testEmailSection.style.display = 'block';
    }
});

// Trigger on page load
mailMailerSelect.dispatchEvent(new Event('change'));

async function testEmail() {
    const testEmailInput = document.getElementById('test_email');
    const loadingIndicator = document.getElementById('loadingIndicator');
    const testResult = document.getElementById('testResult');

    if (!testEmailInput.value) {
        alert('Please enter an email address to test');
        return;
    }

    loadingIndicator.classList.add('active');
    testResult.innerHTML = '';

    const data = {
        test_email: testEmailInput.value,
        mail_mailer: document.getElementById('mail_mailer').value,
        mail_host: document.getElementById('mail_host').value,
        mail_port: parseInt(document.getElementById('mail_port').value) || 587,
        mail_username: document.getElementById('mail_username').value,
        mail_password: document.getElementById('mail_password').value,
        mail_encryption: document.getElementById('mail_encryption').value,
        mail_from_address: document.getElementById('mail_from_address').value,
        mail_from_name: document.getElementById('mail_from_name').value,
    };

    try {
        const result = await sendRequest('{{ route('install.email.test') }}', data);

        loadingIndicator.classList.remove('active');

        if (result.success) {
            testResult.innerHTML = `<div class="alert alert-success">${result.message}</div>`;
        } else {
            testResult.innerHTML = `<div class="alert alert-error"><strong>Test failed:</strong><br>${result.message}</div>`;
        }
    } catch (error) {
        loadingIndicator.classList.remove('active');
        testResult.innerHTML = `<div class="alert alert-error"><strong>Error:</strong> ${error.message}</div>`;
    }
}
</script>
@endpush
