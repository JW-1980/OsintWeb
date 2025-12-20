@extends('install.layout', ['progress' => 100])

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
    <div class="step completed">
        <div class="step-number">✓</div>
        <div class="step-label">Email</div>
    </div>
    <div class="step completed">
        <div class="step-number">✓</div>
        <div class="step-label">Search</div>
    </div>
    <div class="step active">
        <div class="step-number">8</div>
        <div class="step-label">Finish</div>
    </div>
</div>

<h2 style="margin-bottom: 24px; text-align: center;">Ready to Complete Installation</h2>

<div class="alert alert-info">
    <strong>Final Steps:</strong><br>
    Click the button below to finalize the installation. This will:
    <ul style="margin-top: 8px; margin-left: 20px;">
        <li>Generate application encryption key</li>
        <li>Clear and rebuild caches</li>
        <li>Optimize the application for performance</li>
        <li>Create installation lock file</li>
    </ul>
</div>

<div id="completionResult"></div>

<form method="POST" action="{{ route('install.finish.complete') }}" id="finishForm">
    @csrf

    <div class="card" style="text-align: center; padding: 40px;">
        <div style="font-size: 64px; margin-bottom: 20px;">🎉</div>
        <h3 style="margin-bottom: 16px;">Almost Done!</h3>
        <p style="color: #777; margin-bottom: 32px;">
            You've successfully configured OsintWeb.<br>
            Click the button below to complete the installation.
        </p>
        <button type="button" class="btn btn-success" onclick="completeInstallation()" style="font-size: 16px; padding: 16px 32px;">
            Complete Installation
        </button>
    </div>
</form>

<div class="loading" id="loadingIndicator">
    <div class="spinner"></div>
    <p>Finalizing installation...</p>
</div>

<div class="btn-group" style="display: none;" id="finalButtons">
    <div></div>
    <div style="display: flex; gap: 12px;">
        <a href="/" class="btn btn-primary">Go to Homepage →</a>
    </div>
</div>
@endsection

@push('scripts')
<script>
async function completeInstallation() {
    const loadingIndicator = document.getElementById('loadingIndicator');
    const completionResult = document.getElementById('completionResult');
    const finishForm = document.getElementById('finishForm');
    const finalButtons = document.getElementById('finalButtons');

    loadingIndicator.classList.add('active');
    finishForm.style.display = 'none';
    completionResult.innerHTML = '';

    try {
        const formData = new FormData(finishForm);
        const response = await fetch('{{ route('install.finish.complete') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: formData,
        });

        loadingIndicator.classList.remove('active');

        if (response.redirected) {
            // Successful completion with redirect
            completionResult.innerHTML = `
                <div class="alert alert-success" style="text-align: center;">
                    <div style="font-size: 48px; margin-bottom: 16px;">✓</div>
                    <h3 style="margin-bottom: 16px; color: #2e7d32;">Installation Complete!</h3>
                    <p style="margin-bottom: 24px;">OsintWeb has been successfully installed and configured.</p>
                </div>

                <div class="card">
                    <h3>What's Next?</h3>
                    <ul style="margin-left: 20px; line-height: 1.8;">
                        <li>Log in with your admin account</li>
                        <li>Configure additional settings in the Admin Panel</li>
                        <li>Import equipment database (optional)</li>
                        <li>Create your first event or control zone</li>
                        <li>Set up automated backups</li>
                        <li>Configure queue workers for background jobs</li>
                    </ul>
                </div>

                <div class="info-box">
                    <strong>Important:</strong>
                    <ul style="margin-top: 8px; margin-left: 20px;">
                        <li>The installation wizard is now locked and cannot be accessed again</li>
                        <li>To reinstall, you must delete the file: <code>storage/installed</code></li>
                        <li>Admin panel URL: <strong>/admin</strong></li>
                    </ul>
                </div>
            `;

            finalButtons.style.display = 'flex';

            // Redirect after a delay
            setTimeout(() => {
                window.location.href = '/';
            }, 3000);
        } else {
            const result = await response.json();

            if (result.errors) {
                let errorMessages = '';
                Object.values(result.errors).forEach(errors => {
                    errors.forEach(error => {
                        errorMessages += `<li>${error}</li>`;
                    });
                });

                completionResult.innerHTML = `
                    <div class="alert alert-error">
                        <strong>Installation failed:</strong>
                        <ul style="margin-top: 8px; margin-left: 20px;">
                            ${errorMessages}
                        </ul>
                    </div>
                `;
            } else {
                completionResult.innerHTML = `
                    <div class="alert alert-error">
                        <strong>Installation failed:</strong><br>
                        ${result.message || 'An unknown error occurred'}
                    </div>
                `;
            }

            finishForm.style.display = 'block';
        }
    } catch (error) {
        loadingIndicator.classList.remove('active');

        completionResult.innerHTML = `
            <div class="alert alert-error">
                <strong>Error:</strong> ${error.message}
            </div>
        `;

        finishForm.style.display = 'block';
    }
}
</script>
@endpush
