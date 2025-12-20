@extends('install.layout', ['progress' => 25])

@section('content')
<div class="step-indicator">
    <div class="step completed">
        <div class="step-number">✓</div>
        <div class="step-label">Welcome</div>
    </div>
    <div class="step active">
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

<h2 style="margin-bottom: 24px;">Database Configuration</h2>

@if($errors->any())
<div class="alert alert-error">
    @foreach($errors->all() as $error)
    <div>{{ $error }}</div>
    @endforeach
</div>
@endif

<div id="testResult"></div>

<form method="POST" action="{{ route('install.database.save') }}" id="databaseForm">
    @csrf

    <div class="card">
        <h3>MySQL Database Settings</h3>

        <div class="form-group">
            <label for="host">Database Host</label>
            <input type="text" id="host" name="host" value="{{ old('host', $host) }}" required>
            <div class="help-text">Usually 127.0.0.1 or localhost</div>
        </div>

        <div class="form-group">
            <label for="port">Database Port</label>
            <input type="number" id="port" name="port" value="{{ old('port', $port) }}" required>
            <div class="help-text">Default MySQL port is 3306</div>
        </div>

        <div class="form-group">
            <label for="database">Database Name</label>
            <input type="text" id="database" name="database" value="{{ old('database', $database) }}" required>
        </div>

        <div class="form-group">
            <label for="username">Database Username</label>
            <input type="text" id="username" name="username" value="{{ old('username', $username) }}" required>
        </div>

        <div class="form-group">
            <label for="password">Database Password</label>
            <input type="password" id="password" name="password" autocomplete="off">
        </div>

        <div class="checkbox-group" style="margin-top: 20px;">
            <input type="checkbox" id="create_database" name="create_database" value="1">
            <label for="create_database" style="margin: 0; font-weight: normal;">
                Create database if it doesn't exist
            </label>
        </div>
    </div>

    <div class="btn-group">
        <a href="{{ route('install.welcome') }}" class="btn btn-secondary">← Back</a>
        <div style="display: flex; gap: 12px;">
            <button type="button" class="btn btn-secondary" onclick="testConnection()">Test Connection</button>
            <button type="submit" class="btn btn-primary" id="submitBtn" disabled>Continue →</button>
        </div>
    </div>
</form>

<div class="loading" id="loadingIndicator">
    <div class="spinner"></div>
    <p>Testing database connection...</p>
</div>
@endsection

@push('scripts')
<script>
let connectionTested = false;

async function testConnection() {
    const loadingIndicator = document.getElementById('loadingIndicator');
    const testResult = document.getElementById('testResult');
    const submitBtn = document.getElementById('submitBtn');

    loadingIndicator.classList.add('active');
    testResult.innerHTML = '';

    const data = {
        host: document.getElementById('host').value,
        port: parseInt(document.getElementById('port').value),
        database: document.getElementById('database').value,
        username: document.getElementById('username').value,
        password: document.getElementById('password').value,
    };

    try {
        const result = await sendRequest('{{ route('install.database.test') }}', data);

        loadingIndicator.classList.remove('active');

        if (result.success) {
            connectionTested = true;
            submitBtn.disabled = false;

            let message = `Successfully connected to MySQL ${result.mysql_version}`;
            if (result.database_exists) {
                message += `. Database "${data.database}" exists.`;
            } else {
                message += `. Database "${data.database}" does not exist - it will be created.`;
                document.getElementById('create_database').checked = true;
            }

            testResult.innerHTML = `<div class="alert alert-success">${message}</div>`;
        } else {
            connectionTested = false;
            submitBtn.disabled = true;
            testResult.innerHTML = `<div class="alert alert-error"><strong>Connection failed:</strong><br>${result.message}</div>`;
        }
    } catch (error) {
        loadingIndicator.classList.remove('active');
        connectionTested = false;
        submitBtn.disabled = true;
        testResult.innerHTML = `<div class="alert alert-error"><strong>Error:</strong> ${error.message}</div>`;
    }
}

// Enable submit only after successful test
document.getElementById('databaseForm').addEventListener('submit', function(e) {
    if (!connectionTested) {
        e.preventDefault();
        alert('Please test the database connection first.');
    }
});

// Reset connection test when values change
document.querySelectorAll('#host, #port, #database, #username, #password').forEach(input => {
    input.addEventListener('input', function() {
        connectionTested = false;
        document.getElementById('submitBtn').disabled = true;
        document.getElementById('testResult').innerHTML = '';
    });
});
</script>
@endpush
