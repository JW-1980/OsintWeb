@extends('install.layout', ['progress' => 37.5])

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
    <div class="step active">
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

<h2 style="margin-bottom: 24px;">Database Setup</h2>

<div class="info-box">
    <strong>This step will:</strong>
    <ul>
        <li>Create all necessary database tables</li>
        <li>Set up indexes and relationships</li>
        <li>Optionally seed initial data (countries, equipment categories)</li>
    </ul>
</div>

<div id="migrationResult"></div>

<div class="card" id="optionsCard">
    <h3>Options</h3>

    <div class="checkbox-group" style="margin-bottom: 16px;">
        <input type="checkbox" id="seed_data" name="seed_data" value="1" checked>
        <label for="seed_data" style="margin: 0; font-weight: normal;">
            Seed initial data (countries, equipment categories, etc.)
        </label>
    </div>

    <div class="checkbox-group">
        <input type="checkbox" id="sample_conflicts" name="sample_conflicts" value="1">
        <label for="sample_conflicts" style="margin: 0; font-weight: normal;">
            Load sample conflict data (for testing and demonstration)
        </label>
    </div>
</div>

<div class="loading" id="loadingIndicator">
    <div class="spinner"></div>
    <p id="loadingText">Running migrations...</p>
</div>

<div class="btn-group" id="btnGroup">
    <a href="{{ route('install.database') }}" class="btn btn-secondary">← Back</a>
    <button type="button" class="btn btn-primary" onclick="runMigrations()">Run Migrations →</button>
</div>
@endsection

@push('scripts')
<script>
let migrationsCompleted = false;

async function runMigrations() {
    const loadingIndicator = document.getElementById('loadingIndicator');
    const migrationResult = document.getElementById('migrationResult');
    const btnGroup = document.getElementById('btnGroup');
    const optionsCard = document.getElementById('optionsCard');

    loadingIndicator.classList.add('active');
    migrationResult.innerHTML = '';
    optionsCard.style.display = 'none';
    btnGroup.style.display = 'none';

    const data = {
        seed_data: document.getElementById('seed_data').checked,
        sample_conflicts: document.getElementById('sample_conflicts').checked,
    };

    try {
        const result = await sendRequest('{{ route('install.migrations.run') }}', data);

        loadingIndicator.classList.remove('active');

        if (result.success) {
            migrationsCompleted = true;

            let output = result.output || result.migration_output || 'Migrations completed successfully';
            output = output.replace(/\n/g, '<br>');

            migrationResult.innerHTML = `
                <div class="alert alert-success">
                    <strong>Success!</strong><br>
                    Database tables created successfully.
                </div>
                <div class="card">
                    <h3>Migration Output</h3>
                    <div style="font-family: monospace; font-size: 12px; background: #f5f5f5; padding: 12px; border-radius: 4px; max-height: 300px; overflow-y: auto;">
                        ${output}
                    </div>
                </div>
            `;

            // Show continue button
            btnGroup.innerHTML = `
                <div></div>
                <a href="{{ route('install.settings') }}" class="btn btn-primary">Continue →</a>
            `;
            btnGroup.style.display = 'flex';
        } else {
            let output = result.output || result.message || 'Migration failed';
            output = output.replace(/\n/g, '<br>');

            migrationResult.innerHTML = `
                <div class="alert alert-error">
                    <strong>Migration Failed:</strong><br>
                    ${result.message || 'An error occurred'}
                </div>
                <div class="card">
                    <h3>Error Details</h3>
                    <div style="font-family: monospace; font-size: 12px; background: #fff5f5; padding: 12px; border-radius: 4px; max-height: 300px; overflow-y: auto;">
                        ${output}
                    </div>
                </div>
            `;

            // Show retry button
            btnGroup.innerHTML = `
                <a href="{{ route('install.database') }}" class="btn btn-secondary">← Back</a>
                <button type="button" class="btn btn-primary" onclick="runMigrations()">Retry →</button>
            `;
            btnGroup.style.display = 'flex';
            optionsCard.style.display = 'block';
        }
    } catch (error) {
        loadingIndicator.classList.remove('active');

        migrationResult.innerHTML = `
            <div class="alert alert-error">
                <strong>Error:</strong><br>
                ${error.message}
            </div>
        `;

        btnGroup.innerHTML = `
            <a href="{{ route('install.database') }}" class="btn btn-secondary">← Back</a>
            <button type="button" class="btn btn-primary" onclick="runMigrations()">Retry →</button>
        `;
        btnGroup.style.display = 'flex';
        optionsCard.style.display = 'block';
    }
}
</script>
@endpush
