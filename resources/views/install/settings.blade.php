@extends('install.layout', ['progress' => 50])

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
    <div class="step active">
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

<h2 style="margin-bottom: 24px;">Application Settings</h2>

@if($errors->any())
<div class="alert alert-error">
    @foreach($errors->all() as $error)
    <div>{{ $error }}</div>
    @endforeach
</div>
@endif

<form method="POST" action="{{ route('install.settings.save') }}">
    @csrf

    <div class="card">
        <h3>General Settings</h3>

        <div class="form-group">
            <label for="app_name">Application Name</label>
            <input type="text" id="app_name" name="app_name" value="{{ old('app_name', $app_name) }}" required>
        </div>

        <div class="form-group">
            <label for="app_url">Application URL</label>
            <input type="url" id="app_url" name="app_url" value="{{ old('app_url', $app_url) }}" required>
            <div class="help-text">The full URL where this application will be accessible</div>
        </div>

        <div class="form-group">
            <label for="timezone">Timezone</label>
            <select id="timezone" name="timezone" required>
                <option value="UTC" {{ old('timezone', $timezone) == 'UTC' ? 'selected' : '' }}>UTC</option>
                <option value="America/New_York" {{ old('timezone', $timezone) == 'America/New_York' ? 'selected' : '' }}>America/New York (EST)</option>
                <option value="America/Los_Angeles" {{ old('timezone', $timezone) == 'America/Los_Angeles' ? 'selected' : '' }}>America/Los Angeles (PST)</option>
                <option value="Europe/London" {{ old('timezone', $timezone) == 'Europe/London' ? 'selected' : '' }}>Europe/London (GMT)</option>
                <option value="Europe/Paris" {{ old('timezone', $timezone) == 'Europe/Paris' ? 'selected' : '' }}>Europe/Paris (CET)</option>
                <option value="Asia/Tokyo" {{ old('timezone', $timezone) == 'Asia/Tokyo' ? 'selected' : '' }}>Asia/Tokyo (JST)</option>
                <option value="Asia/Dubai" {{ old('timezone', $timezone) == 'Asia/Dubai' ? 'selected' : '' }}>Asia/Dubai (GST)</option>
            </select>
        </div>

        <div class="form-group">
            <label for="locale">Default Language</label>
            <select id="locale" name="locale" required>
                <option value="en" {{ old('locale', $locale) == 'en' ? 'selected' : '' }}>English</option>
                <option value="fr" {{ old('locale', $locale) == 'fr' ? 'selected' : '' }}>French</option>
                <option value="es" {{ old('locale', $locale) == 'es' ? 'selected' : '' }}>Spanish</option>
                <option value="de" {{ old('locale', $locale) == 'de' ? 'selected' : '' }}>German</option>
                <option value="ar" {{ old('locale', $locale) == 'ar' ? 'selected' : '' }}>Arabic</option>
            </select>
        </div>
    </div>

    <div class="card">
        <h3>Map Settings</h3>

        <div class="form-group">
            <label for="map_center_lat">Default Map Center - Latitude</label>
            <input type="number" step="0.000001" id="map_center_lat" name="map_center_lat" value="{{ old('map_center_lat', '48.8566') }}" min="-90" max="90" required>
        </div>

        <div class="form-group">
            <label for="map_center_lng">Default Map Center - Longitude</label>
            <input type="number" step="0.000001" id="map_center_lng" name="map_center_lng" value="{{ old('map_center_lng', '2.3522') }}" min="-180" max="180" required>
        </div>

        <div class="form-group">
            <label for="map_zoom">Default Zoom Level</label>
            <input type="number" id="map_zoom" name="map_zoom" value="{{ old('map_zoom', '4') }}" min="1" max="18" required>
            <div class="help-text">1 = world view, 18 = street level</div>
        </div>

        <div class="form-group">
            <label for="map_base_layer">Default Base Layer</label>
            <select id="map_base_layer" name="map_base_layer" required>
                <option value="openstreetmap" {{ old('map_base_layer') == 'openstreetmap' ? 'selected' : '' }}>OpenStreetMap</option>
                <option value="satellite" {{ old('map_base_layer') == 'satellite' ? 'selected' : '' }}>Satellite</option>
                <option value="terrain" {{ old('map_base_layer') == 'terrain' ? 'selected' : '' }}>Terrain</option>
            </select>
        </div>
    </div>

    <div class="card">
        <h3>User Registration</h3>

        <div class="checkbox-group" style="margin-bottom: 16px;">
            <input type="checkbox" id="allow_registration" name="allow_registration" value="1" {{ old('allow_registration') ? 'checked' : '' }}>
            <label for="allow_registration" style="margin: 0; font-weight: normal;">
                Allow public registration
            </label>
        </div>

        <div class="checkbox-group" style="margin-bottom: 16px;">
            <input type="checkbox" id="require_verification" name="require_verification" value="1" checked {{ old('require_verification', '1') ? 'checked' : '' }}>
            <label for="require_verification" style="margin: 0; font-weight: normal;">
                Require email verification for new users
            </label>
        </div>

        <div class="form-group">
            <label for="default_user_role">Default User Role</label>
            <select id="default_user_role" name="default_user_role" required>
                <option value="viewer" {{ old('default_user_role', 'viewer') == 'viewer' ? 'selected' : '' }}>Viewer (read-only)</option>
                <option value="contributor" {{ old('default_user_role') == 'contributor' ? 'selected' : '' }}>Contributor (can add data)</option>
                <option value="analyst" {{ old('default_user_role') == 'analyst' ? 'selected' : '' }}>Analyst (full access)</option>
            </select>
        </div>
    </div>

    <div class="card">
        <h3>Security Settings</h3>

        <div class="form-group">
            <label for="session_lifetime">Session Lifetime (minutes)</label>
            <input type="number" id="session_lifetime" name="session_lifetime" value="{{ old('session_lifetime', '120') }}" min="5" required>
            <div class="help-text">How long users stay logged in when inactive</div>
        </div>

        <div class="form-group">
            <label for="api_rate_limit">API Rate Limit (requests per minute)</label>
            <input type="number" id="api_rate_limit" name="api_rate_limit" value="{{ old('api_rate_limit', '60') }}" min="10" required>
        </div>

        <div class="checkbox-group">
            <input type="checkbox" id="enable_2fa" name="enable_2fa" value="1" checked {{ old('enable_2fa', '1') ? 'checked' : '' }}>
            <label for="enable_2fa" style="margin: 0; font-weight: normal;">
                Enable two-factor authentication
            </label>
        </div>
    </div>

    <div class="btn-group">
        <a href="{{ route('install.migrations') }}" class="btn btn-secondary">← Back</a>
        <button type="submit" class="btn btn-primary">Continue →</button>
    </div>
</form>
@endsection
