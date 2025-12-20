@extends('install.layout', ['progress' => 87.5])

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
    <div class="step active">
        <div class="step-number">7</div>
        <div class="step-label">Search</div>
    </div>
    <div class="step">
        <div class="step-number">8</div>
        <div class="step-label">Finish</div>
    </div>
</div>

<h2 style="margin-bottom: 24px;">Search Configuration (Optional)</h2>

<div class="info-box">
    <strong>Choose a search engine for your application:</strong>
    <ul>
        <li><strong>MySQL Full-Text:</strong> Built-in, no additional setup required, good for smaller datasets</li>
        <li><strong>Meilisearch:</strong> Fast and typo-tolerant, requires separate service</li>
        <li><strong>Algolia:</strong> Cloud-hosted, powerful features, requires account</li>
    </ul>
</div>

@if($errors->any())
<div class="alert alert-error">
    @foreach($errors->all() as $error)
    <div>{{ $error }}</div>
    @endforeach
</div>
@endif

<div id="testResult"></div>

<form method="POST" action="{{ route('install.search.save') }}" id="searchForm">
    @csrf

    <div class="card">
        <h3>Search Engine</h3>

        <div class="form-group">
            <label for="search_driver">Search Driver</label>
            <select id="search_driver" name="search_driver" required>
                <option value="database" {{ old('search_driver', $scout_driver) == 'database' ? 'selected' : '' }}>MySQL Full-Text (Default)</option>
                <option value="meilisearch" {{ old('search_driver', $scout_driver) == 'meilisearch' ? 'selected' : '' }}>Meilisearch</option>
                <option value="algolia" {{ old('search_driver', $scout_driver) == 'algolia' ? 'selected' : '' }}>Algolia</option>
            </select>
        </div>
    </div>

    <div class="card" id="meilisearchConfig" style="display: none;">
        <h3>Meilisearch Configuration</h3>

        <div class="form-group">
            <label for="meilisearch_host">Meilisearch Host URL</label>
            <input type="url" id="meilisearch_host" name="meilisearch_host" value="{{ old('meilisearch_host') }}">
            <div class="help-text">e.g., http://localhost:7700</div>
        </div>

        <div class="form-group">
            <label for="meilisearch_key">Master Key (Optional)</label>
            <input type="text" id="meilisearch_key" name="meilisearch_key" value="{{ old('meilisearch_key') }}">
            <div class="help-text">Leave empty if no authentication is required</div>
        </div>

        <button type="button" class="btn btn-secondary" onclick="testSearch('meilisearch')">Test Connection</button>
    </div>

    <div class="card" id="algoliaConfig" style="display: none;">
        <h3>Algolia Configuration</h3>

        <div class="form-group">
            <label for="algolia_app_id">Application ID</label>
            <input type="text" id="algolia_app_id" name="algolia_app_id" value="{{ old('algolia_app_id') }}">
        </div>

        <div class="form-group">
            <label for="algolia_secret">Admin API Key</label>
            <input type="password" id="algolia_secret" name="algolia_secret" autocomplete="off">
        </div>

        <button type="button" class="btn btn-secondary" onclick="testSearch('algolia')">Test Connection</button>
    </div>

    <div class="loading" id="loadingIndicator">
        <div class="spinner"></div>
        <p>Testing search connection...</p>
    </div>

    <div class="btn-group">
        <a href="{{ route('install.email') }}" class="btn btn-secondary">← Back</a>
        <button type="submit" class="btn btn-primary">Continue →</button>
    </div>
</form>
@endsection

@push('scripts')
<script>
const searchDriverSelect = document.getElementById('search_driver');
const meilisearchConfig = document.getElementById('meilisearchConfig');
const algoliaConfig = document.getElementById('algoliaConfig');

// Show/hide config based on search driver
searchDriverSelect.addEventListener('change', function() {
    const driver = this.value;

    meilisearchConfig.style.display = 'none';
    algoliaConfig.style.display = 'none';

    if (driver === 'meilisearch') {
        meilisearchConfig.style.display = 'block';
    } else if (driver === 'algolia') {
        algoliaConfig.style.display = 'block';
    }
});

// Trigger on page load
searchDriverSelect.dispatchEvent(new Event('change'));

async function testSearch(driver) {
    const loadingIndicator = document.getElementById('loadingIndicator');
    const testResult = document.getElementById('testResult');

    loadingIndicator.classList.add('active');
    testResult.innerHTML = '';

    let data = {
        search_driver: driver,
    };

    if (driver === 'meilisearch') {
        data.meilisearch_host = document.getElementById('meilisearch_host').value;
        data.meilisearch_key = document.getElementById('meilisearch_key').value;

        if (!data.meilisearch_host) {
            loadingIndicator.classList.remove('active');
            testResult.innerHTML = '<div class="alert alert-error">Please enter Meilisearch host URL</div>';
            return;
        }
    } else if (driver === 'algolia') {
        data.algolia_app_id = document.getElementById('algolia_app_id').value;
        data.algolia_secret = document.getElementById('algolia_secret').value;

        if (!data.algolia_app_id || !data.algolia_secret) {
            loadingIndicator.classList.remove('active');
            testResult.innerHTML = '<div class="alert alert-error">Please enter Algolia credentials</div>';
            return;
        }
    }

    try {
        const result = await sendRequest('{{ route('install.search.test') }}', data);

        loadingIndicator.classList.remove('active');

        if (result.success) {
            testResult.innerHTML = `<div class="alert alert-success">${result.message}</div>`;
        } else {
            testResult.innerHTML = `<div class="alert alert-error"><strong>Connection failed:</strong><br>${result.message}</div>`;
        }
    } catch (error) {
        loadingIndicator.classList.remove('active');
        testResult.innerHTML = `<div class="alert alert-error"><strong>Error:</strong> ${error.message}</div>`;
    }
}
</script>
@endpush
