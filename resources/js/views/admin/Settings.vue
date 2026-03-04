<template>
  <div class="p-6 bg-gray-50 dark:bg-gray-900 min-h-screen">
    <div class="max-w-4xl mx-auto">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">System Settings</h1>
        <p class="text-gray-500 dark:text-gray-400 mt-1">Configure platform-wide settings and preferences</p>
      </div>

      <!-- Settings Navigation -->
      <div class="flex flex-wrap gap-2 mb-6">
        <button
          v-for="tab in tabs"
          :key="tab.id"
          @click="activeTab = tab.id"
          :class="[
            'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
            activeTab === tab.id
              ? 'bg-blue-600 text-white'
              : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
          ]"
        >
          {{ tab.label }}
        </button>
      </div>

      <!-- General Settings -->
      <div v-if="activeTab === 'general'" class="space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Site Information</h2>
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Site Name</label>
              <input v-model="settings.siteName" type="text" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Site Description</label>
              <textarea v-model="settings.siteDescription" rows="3" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"></textarea>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Contact Email</label>
              <input v-model="settings.contactEmail" type="email" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Display Settings</h2>
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Default Theme</label>
              <select v-model="settings.defaultTheme" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                <option value="light">Light</option>
                <option value="dark">Dark</option>
                <option value="system">System Default</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Default Language</label>
              <select v-model="settings.defaultLanguage" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                <option value="en">English</option>
                <option value="uk">Ukrainian</option>
                <option value="ru">Russian</option>
                <option value="de">German</option>
                <option value="fr">French</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Timezone</label>
              <select v-model="settings.timezone" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                <option value="UTC">UTC</option>
                <option value="Europe/Kiev">Europe/Kyiv</option>
                <option value="Europe/Moscow">Europe/Moscow</option>
                <option value="Europe/London">Europe/London</option>
                <option value="America/New_York">America/New York</option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <!-- Registration Settings -->
      <div v-if="activeTab === 'registration'" class="space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Registration Settings</h2>
          <div class="space-y-4">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-900 dark:text-white">Allow Public Registration</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Allow new users to register accounts</p>
              </div>
              <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" v-model="settings.allowRegistration" class="sr-only peer" />
                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
              </label>
            </div>
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-900 dark:text-white">Require Email Verification</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Users must verify their email before accessing</p>
              </div>
              <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" v-model="settings.requireEmailVerification" class="sr-only peer" />
                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
              </label>
            </div>
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-900 dark:text-white">Require Admin Approval</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">New accounts require admin approval</p>
              </div>
              <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" v-model="settings.requireAdminApproval" class="sr-only peer" />
                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
              </label>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Default User Role</label>
              <select v-model="settings.defaultUserRole" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                <option value="viewer">Viewer</option>
                <option value="contributor">Contributor</option>
                <option value="analyst">Analyst</option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <!-- Map Settings -->
      <div v-if="activeTab === 'map'" class="space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Map Configuration</h2>
          <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Default Latitude</label>
                <input v-model.number="settings.defaultMapCenter.lat" type="number" step="0.0001" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Default Longitude</label>
                <input v-model.number="settings.defaultMapCenter.lng" type="number" step="0.0001" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Default Zoom Level</label>
              <input v-model.number="settings.defaultMapZoom" type="range" min="1" max="18" class="w-full" />
              <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Current: {{ settings.defaultMapZoom }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Default Base Map</label>
              <select v-model="settings.defaultBasemap" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                <option value="osm">OpenStreetMap</option>
                <option value="satellite">Satellite</option>
                <option value="terrain">Terrain</option>
              </select>
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Default Map Layers</h2>
          <div class="space-y-4">
            <div class="flex items-center justify-between">
              <p class="text-sm font-medium text-gray-900 dark:text-white">Show Events Layer</p>
              <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" v-model="settings.defaultLayers.events" class="sr-only peer" />
                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
              </label>
            </div>
            <div class="flex items-center justify-between">
              <p class="text-sm font-medium text-gray-900 dark:text-white">Show Control Zones Layer</p>
              <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" v-model="settings.defaultLayers.zones" class="sr-only peer" />
                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
              </label>
            </div>
            <div class="flex items-center justify-between">
              <p class="text-sm font-medium text-gray-900 dark:text-white">Show Equipment Layer</p>
              <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" v-model="settings.defaultLayers.equipment" class="sr-only peer" />
                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
              </label>
            </div>
            <div class="flex items-center justify-between">
              <p class="text-sm font-medium text-gray-900 dark:text-white">Show Heatmap Layer</p>
              <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" v-model="settings.defaultLayers.heatmap" class="sr-only peer" />
                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
              </label>
            </div>
          </div>
        </div>
      </div>

      <!-- Security Settings -->
      <div v-if="activeTab === 'security'" class="space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Security Settings</h2>
          <div class="space-y-4">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-900 dark:text-white">Two-Factor Authentication</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Require 2FA for all admin accounts</p>
              </div>
              <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" v-model="settings.require2FA" class="sr-only peer" />
                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
              </label>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Session Timeout (minutes)</label>
              <input v-model.number="settings.sessionTimeout" type="number" min="5" max="1440" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Max Login Attempts</label>
              <input v-model.number="settings.maxLoginAttempts" type="number" min="3" max="10" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Password Minimum Length</label>
              <input v-model.number="settings.passwordMinLength" type="number" min="8" max="32" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">API Settings</h2>
          <div class="space-y-4">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-900 dark:text-white">Enable Public API</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Allow public access to API endpoints</p>
              </div>
              <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" v-model="settings.enablePublicAPI" class="sr-only peer" />
                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
              </label>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">API Rate Limit (requests/minute)</label>
              <input v-model.number="settings.apiRateLimit" type="number" min="10" max="1000" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
            </div>
          </div>
        </div>
      </div>

      <!-- Email Settings -->
      <div v-if="activeTab === 'email'" class="space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Email Configuration</h2>
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">SMTP Host</label>
              <input v-model="settings.smtpHost" type="text" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">SMTP Port</label>
                <input v-model.number="settings.smtpPort" type="number" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Encryption</label>
                <select v-model="settings.smtpEncryption" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                  <option value="tls">TLS</option>
                  <option value="ssl">SSL</option>
                  <option value="none">None</option>
                </select>
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">SMTP Username</label>
              <input v-model="settings.smtpUsername" type="text" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">SMTP Password</label>
              <input v-model="settings.smtpPassword" type="password" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">From Email Address</label>
              <input v-model="settings.fromEmail" type="email" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">From Name</label>
              <input v-model="settings.fromName" type="text" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
            </div>
            <button type="button" class="px-4 py-2 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg">
              Send Test Email
            </button>
          </div>
        </div>
      </div>

      <!-- AI Provider Settings -->
      <div v-if="activeTab === 'ai'" class="space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">AI Provider Configuration</h2>
            <span
              :class="[
                'px-3 py-1 text-xs font-medium rounded-full',
                aiSettings.configured
                  ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400'
                  : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400'
              ]"
            >
              {{ aiSettings.configured ? 'Configured' : 'Not Configured' }}
            </span>
          </div>
          <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
            The active AI provider is set via the <code class="bg-gray-100 dark:bg-gray-700 px-1.5 py-0.5 rounded text-xs">AI_PROVIDER</code> environment variable.
            All providers use OpenAI-compatible APIs for OCR, translation, entity extraction, and document analysis.
          </p>

          <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-4">
            <div class="flex items-start gap-3">
              <svg class="w-5 h-5 text-blue-500 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
              </svg>
              <div>
                <p class="text-sm font-medium text-blue-800 dark:text-blue-300">Active Provider: {{ aiSettings.provider_name }}</p>
                <p class="text-xs text-blue-600 dark:text-blue-400 mt-1">Provider key: <code>{{ aiSettings.active_provider }}</code></p>
              </div>
            </div>
          </div>

          <!-- Test Connection -->
          <button
            @click="testAIConnection"
            :disabled="aiTestLoading || !aiSettings.configured"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium text-sm disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ aiTestLoading ? 'Testing...' : 'Test Connection' }}
          </button>

          <div v-if="aiTestResult" class="mt-3 p-3 rounded-lg text-sm" :class="aiTestResult.success ? 'bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400' : 'bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400'">
            <p class="font-medium">{{ aiTestResult.success ? 'Connection successful' : 'Connection failed' }}</p>
            <p class="text-xs mt-1">{{ aiTestResult.success ? `Model: ${aiTestResult.model}` : aiTestResult.error }}</p>
          </div>
        </div>

        <!-- Available Providers -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Available Providers</h2>
          <div class="space-y-4">
            <div
              v-for="(provider, key) in aiSettings.providers"
              :key="key"
              :class="[
                'border rounded-lg p-4',
                provider.is_active
                  ? 'border-blue-300 dark:border-blue-700 bg-blue-50/50 dark:bg-blue-900/10'
                  : 'border-gray-200 dark:border-gray-700'
              ]"
            >
              <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-2">
                  <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ provider.name }}</h3>
                  <span v-if="provider.is_active" class="px-2 py-0.5 text-xs font-medium bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 rounded-full">Active</span>
                </div>
                <span
                  :class="[
                    'px-2 py-0.5 text-xs font-medium rounded-full',
                    provider.configured
                      ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
                      : 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400'
                  ]"
                >
                  {{ provider.configured ? 'API Key Set' : 'Not Configured' }}
                </span>
              </div>
              <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">{{ provider.description }}</p>
              <div class="flex items-center gap-4 text-xs text-gray-400 dark:text-gray-500">
                <span>{{ provider.free_models?.length || 0 }} free models</span>
                <a v-if="provider.docs_url" :href="provider.docs_url" target="_blank" class="text-blue-500 hover:text-blue-600">Documentation</a>
              </div>
            </div>
          </div>
        </div>

        <!-- Active Models -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Active Models</h2>
          <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Models currently assigned to each AI task from the active provider.</p>
          <div class="space-y-3">
            <div v-for="(model, task) in aiSettings.models" :key="task" class="flex items-center justify-between py-2 border-b border-gray-100 dark:border-gray-700 last:border-0">
              <span class="text-sm font-medium text-gray-700 dark:text-gray-300 capitalize">{{ String(task).replace('_', ' ') }}</span>
              <code class="text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 px-2 py-1 rounded">{{ model }}</code>
            </div>
          </div>
        </div>

        <!-- Environment Setup Guide -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Setup Guide</h2>
          <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
            To switch providers, update your <code class="bg-gray-100 dark:bg-gray-700 px-1.5 py-0.5 rounded text-xs">.env</code> file:
          </p>
          <div class="bg-gray-900 rounded-lg p-4 text-sm font-mono text-gray-300 overflow-x-auto">
            <p class="text-green-400"># Choose your AI provider</p>
            <p>AI_PROVIDER=openrouter <span class="text-gray-500"># or: huggingface, aimlapi</span></p>
            <p class="mt-2 text-green-400"># Set the API key for your chosen provider</p>
            <p>OPENROUTER_API_KEY=your-key-here</p>
            <p>HUGGINGFACE_API_KEY=your-key-here</p>
            <p>AIMLAPI_API_KEY=your-key-here</p>
          </div>
        </div>
      </div>

      <!-- Save Button -->
      <div class="mt-8 flex justify-end">
        <button @click="saveSettings" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
          Save Settings
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue';
import axios from 'axios';

const activeTab = ref('general');

const tabs = [
  { id: 'general', label: 'General' },
  { id: 'registration', label: 'Registration' },
  { id: 'map', label: 'Map' },
  { id: 'security', label: 'Security' },
  { id: 'email', label: 'Email' },
  { id: 'ai', label: 'AI Provider' }
];

const settings = reactive({
  // General
  siteName: 'OsintWeb',
  siteDescription: 'Open Source Intelligence Platform for tracking and analyzing global events',
  contactEmail: 'admin@osintweb.com',
  defaultTheme: 'system',
  defaultLanguage: 'en',
  timezone: 'UTC',

  // Registration
  allowRegistration: true,
  requireEmailVerification: true,
  requireAdminApproval: false,
  defaultUserRole: 'viewer',

  // Map
  defaultMapCenter: { lat: 48.3794, lng: 31.1656 },
  defaultMapZoom: 6,
  defaultBasemap: 'osm',
  defaultLayers: {
    events: true,
    zones: true,
    equipment: false,
    heatmap: false
  },

  // Security
  require2FA: false,
  sessionTimeout: 60,
  maxLoginAttempts: 5,
  passwordMinLength: 8,
  enablePublicAPI: false,
  apiRateLimit: 60,

  // Email
  smtpHost: '',
  smtpPort: 587,
  smtpEncryption: 'tls',
  smtpUsername: '',
  smtpPassword: '',
  fromEmail: '',
  fromName: 'OsintWeb'
});

// AI Provider state
const aiSettings = reactive({
  active_provider: '',
  provider_name: '',
  configured: false,
  providers: {} as Record<string, any>,
  models: {} as Record<string, string>,
  free_models: [] as string[],
});
const aiTestLoading = ref(false);
const aiTestResult = ref<{ success: boolean; model?: string; error?: string } | null>(null);

const loadAISettings = async () => {
  try {
    const response = await axios.get('/api/admin/ai-settings');
    Object.assign(aiSettings, response.data.data);
  } catch (error) {
    console.error('Failed to load AI settings:', error);
  }
};

const testAIConnection = async () => {
  aiTestLoading.value = true;
  aiTestResult.value = null;
  try {
    const response = await axios.post('/api/admin/ai-settings/test-connection');
    aiTestResult.value = response.data.data;
  } catch (error: any) {
    aiTestResult.value = {
      success: false,
      error: error.response?.data?.message || 'Connection test failed',
    };
  } finally {
    aiTestLoading.value = false;
  }
};

onMounted(() => {
  loadAISettings();
});

const saveSettings = () => {
  console.log('Saving settings:', settings);
  alert('Settings saved successfully!');
};
</script>
