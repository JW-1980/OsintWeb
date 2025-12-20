@extends('install.layout', ['progress' => 62.5])

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
    <div class="step active">
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

<h2 style="margin-bottom: 24px;">Create Admin Account</h2>

<div class="info-box">
    <strong>Password Requirements:</strong>
    <ul>
        <li>Minimum 12 characters</li>
        <li>At least one uppercase letter</li>
        <li>At least one lowercase letter</li>
        <li>At least one number</li>
        <li>At least one special character</li>
    </ul>
</div>

@if($errors->any())
<div class="alert alert-error">
    @foreach($errors->all() as $error)
    <div>{{ $error }}</div>
    @endforeach
</div>
@endif

<form method="POST" action="{{ route('install.admin.create') }}" id="adminForm">
    @csrf

    <div class="card">
        <h3>Administrator Details</h3>

        <div class="form-group">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required>
        </div>

        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required>
            <div class="help-text">This will be your login username</div>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
            <div id="passwordStrength" style="margin-top: 8px;"></div>
        </div>

        <div class="form-group">
            <label for="password_confirmation">Confirm Password</label>
            <input type="password" id="password_confirmation" name="password_confirmation" required>
            <div id="passwordMatch" style="margin-top: 8px;"></div>
        </div>
    </div>

    <div class="btn-group">
        <a href="{{ route('install.settings') }}" class="btn btn-secondary">← Back</a>
        <button type="submit" class="btn btn-primary">Create Admin Account →</button>
    </div>
</form>
@endsection

@push('scripts')
<script>
// Password strength checker
const passwordInput = document.getElementById('password');
const passwordConfirmation = document.getElementById('password_confirmation');
const strengthDiv = document.getElementById('passwordStrength');
const matchDiv = document.getElementById('passwordMatch');

passwordInput.addEventListener('input', function() {
    const password = this.value;
    const requirements = {
        length: password.length >= 12,
        uppercase: /[A-Z]/.test(password),
        lowercase: /[a-z]/.test(password),
        number: /[0-9]/.test(password),
        special: /[^A-Za-z0-9]/.test(password),
    };

    const passed = Object.values(requirements).filter(Boolean).length;
    const total = Object.keys(requirements).length;

    let strength = '';
    let color = '';

    if (passed === total) {
        strength = 'Strong';
        color = '#4caf50';
    } else if (passed >= 3) {
        strength = 'Medium';
        color = '#ff9800';
    } else {
        strength = 'Weak';
        color = '#f44336';
    }

    strengthDiv.innerHTML = `
        <div style="font-size: 12px;">
            <span style="color: ${color}; font-weight: 600;">Password Strength: ${strength}</span>
            <div style="margin-top: 4px; color: #777;">
                ${requirements.length ? '✓' : '✗'} 12+ characters
                ${requirements.uppercase ? '✓' : '✗'} Uppercase
                ${requirements.lowercase ? '✓' : '✗'} Lowercase
                ${requirements.number ? '✓' : '✗'} Number
                ${requirements.special ? '✓' : '✗'} Special character
            </div>
        </div>
    `;

    checkPasswordMatch();
});

passwordConfirmation.addEventListener('input', checkPasswordMatch);

function checkPasswordMatch() {
    const password = passwordInput.value;
    const confirmation = passwordConfirmation.value;

    if (confirmation.length === 0) {
        matchDiv.innerHTML = '';
        return;
    }

    if (password === confirmation) {
        matchDiv.innerHTML = '<div class="help-text" style="color: #4caf50;">✓ Passwords match</div>';
    } else {
        matchDiv.innerHTML = '<div class="error-text">✗ Passwords do not match</div>';
    }
}

// Form validation
document.getElementById('adminForm').addEventListener('submit', function(e) {
    const password = passwordInput.value;
    const confirmation = passwordConfirmation.value;

    if (password !== confirmation) {
        e.preventDefault();
        alert('Passwords do not match');
        return;
    }

    const requirements = {
        length: password.length >= 12,
        uppercase: /[A-Z]/.test(password),
        lowercase: /[a-z]/.test(password),
        number: /[0-9]/.test(password),
        special: /[^A-Za-z0-9]/.test(password),
    };

    if (!Object.values(requirements).every(Boolean)) {
        e.preventDefault();
        alert('Password does not meet all requirements');
    }
});
</script>
@endpush
