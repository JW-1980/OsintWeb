<template>
  <div class="p-6 bg-gray-50 dark:bg-gray-900 min-h-screen">
    <div class="max-w-4xl mx-auto">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">CAPTCHA Settings</h1>
        <p class="text-gray-500 dark:text-gray-400 mt-1">Configure bot protection for authentication and public forms</p>
      </div>

      <!-- Status Banner -->
      <div v-if="saveMessage" :class="[
        'mb-6 p-4 rounded-lg text-sm font-medium',
        saveError
          ? 'bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-800'
          : 'bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-300 border border-green-200 dark:border-green-800'
      ]">
        {{ saveMessage }}
      </div>

      <!-- Master Toggle -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
        <div class="flex items-center justify-between">
          <div>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">CAPTCHA Protection</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
              Enable bot protection on login, registration, and public forms
            </p>
          </div>
          <label class="relative inline-flex items-center cursor-pointer">
            <input type="checkbox" v-model="settings.enabled" class="sr-only peer" />
            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
          </label>
        </div>
      </div>

      <!-- Provider Selection -->
      <div v-if="settings.enabled" class="space-y-6">
        <div>
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Select Provider</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- reCAPTCHA Card -->
            <button
              @click="settings.provider = 'recaptcha'"
              :class="[
                'text-left p-5 rounded-lg border-2 transition-all',
                settings.provider === 'recaptcha'
                  ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20'
                  : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:border-gray-300 dark:hover:border-gray-600'
              ]"
            >
              <div class="flex items-center gap-3 mb-2">
                <div :class="[
                  'w-10 h-10 rounded-lg flex items-center justify-center text-lg font-bold',
                  settings.provider === 'recaptcha'
                    ? 'bg-blue-100 dark:bg-blue-800 text-blue-600 dark:text-blue-300'
                    : 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400'
                ]">
                  G
                </div>
                <div>
                  <h3 class="font-semibold text-gray-900 dark:text-white">Google reCAPTCHA v3</h3>
                  <p class="text-xs text-gray-500 dark:text-gray-400">Score-based detection</p>
                </div>
              </div>
              <p class="text-sm text-gray-600 dark:text-gray-300">
                Invisible bot detection using behavioral analysis. Returns a score (0.0 to 1.0) without
                user interaction. Best for frictionless UX.
              </p>
              <div class="mt-3 flex flex-wrap gap-2">
                <span class="px-2 py-0.5 text-xs rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">Invisible</span>
                <span class="px-2 py-0.5 text-xs rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">Score-based</span>
                <span class="px-2 py-0.5 text-xs rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">Google</span>
              </div>
            </button>

            <!-- Turnstile Card -->
            <button
              @click="settings.provider = 'turnstile'"
              :class="[
                'text-left p-5 rounded-lg border-2 transition-all',
                settings.provider === 'turnstile'
                  ? 'border-orange-500 bg-orange-50 dark:bg-orange-900/20'
                  : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:border-gray-300 dark:hover:border-gray-600'
              ]"
            >
              <div class="flex items-center gap-3 mb-2">
                <div :class="[
                  'w-10 h-10 rounded-lg flex items-center justify-center text-lg font-bold',
                  settings.provider === 'turnstile'
                    ? 'bg-orange-100 dark:bg-orange-800 text-orange-600 dark:text-orange-300'
                    : 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400'
                ]">
                  CF
                </div>
                <div>
                  <h3 class="font-semibold text-gray-900 dark:text-white">Cloudflare Turnstile</h3>
                  <p class="text-xs text-gray-500 dark:text-gray-400">Privacy-focused challenge</p>
                </div>
              </div>
              <p class="text-sm text-gray-600 dark:text-gray-300">
                Privacy-preserving alternative with managed challenges. Pass/fail model without
                tracking. GDPR-friendly with no cookie requirements.
              </p>
              <div class="mt-3 flex flex-wrap gap-2">
                <span class="px-2 py-0.5 text-xs rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">Privacy-first</span>
                <span class="px-2 py-0.5 text-xs rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">Pass/Fail</span>
                <span class="px-2 py-0.5 text-xs rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">Cloudflare</span>
              </div>
            </button>
          </div>
        </div>

        <!-- reCAPTCHA Configuration -->
        <div v-if="settings.provider === 'recaptcha'" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">reCAPTCHA v3 Configuration</h2>
          <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
            Get your keys from the
            <a href="https://www.google.com/recaptcha/admin" target="_blank" rel="noopener" class="text-blue-600 hover:underline">
              Google reCAPTCHA Admin Console
            </a>.
            Select "reCAPTCHA v3" when creating a new site.
          </p>
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Site Key</label>
              <input
                v-model="settings.recaptcha.site_key"
                type="text"
                placeholder="6Lc..."
                class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white font-mono text-sm"
              />
              <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Used in the frontend widget. Safe to expose publicly.</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Secret Key</label>
              <input
                v-model="settings.recaptcha.secret_key"
                type="password"
                placeholder="Enter new secret key..."
                class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white font-mono text-sm"
              />
              <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Used server-side for verification. Keep confidential.</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Score Threshold: {{ settings.recaptcha.score_threshold }}
              </label>
              <input
                v-model.number="settings.recaptcha.score_threshold"
                type="range"
                min="0"
                max="1"
                step="0.1"
                class="w-full"
              />
              <div class="flex justify-between text-xs text-gray-400 dark:text-gray-500 mt-1">
                <span>0.0 (permissive)</span>
                <span>0.5 (balanced)</span>
                <span>1.0 (strict)</span>
              </div>
              <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                Requests scoring below this threshold are rejected. Default 0.5 is recommended for most sites.
              </p>
            </div>
          </div>
        </div>

        <!-- Turnstile Configuration -->
        <div v-if="settings.provider === 'turnstile'" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Cloudflare Turnstile Configuration</h2>
          <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
            Get your keys from the
            <a href="https://dash.cloudflare.com/turnstile" target="_blank" rel="noopener" class="text-blue-600 hover:underline">
              Cloudflare Turnstile Dashboard
            </a>.
            Choose Managed, Non-interactive, or Invisible widget type.
          </p>
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Site Key</label>
              <input
                v-model="settings.turnstile.site_key"
                type="text"
                placeholder="0x4AAA..."
                class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white font-mono text-sm"
              />
              <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Used in the frontend widget. Safe to expose publicly.</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Secret Key</label>
              <input
                v-model="settings.turnstile.secret_key"
                type="password"
                placeholder="Enter new secret key..."
                class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white font-mono text-sm"
              />
              <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Used server-side for verification. Keep confidential.</p>
            </div>
          </div>
        </div>

        <!-- Integration Info -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Protected Routes</h2>
          <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">
            When enabled, CAPTCHA verification is applied to the following routes:
          </p>
          <div class="space-y-2">
            <div class="flex items-center gap-2 text-sm">
              <span class="px-2 py-0.5 rounded bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 font-mono text-xs">POST</span>
              <span class="text-gray-700 dark:text-gray-300">/api/auth/register</span>
              <span class="text-gray-400 dark:text-gray-500">- User registration</span>
            </div>
            <div class="flex items-center gap-2 text-sm">
              <span class="px-2 py-0.5 rounded bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 font-mono text-xs">POST</span>
              <span class="text-gray-700 dark:text-gray-300">/api/auth/login</span>
              <span class="text-gray-400 dark:text-gray-500">- User login</span>
            </div>
            <div class="flex items-center gap-2 text-sm">
              <span class="px-2 py-0.5 rounded bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 font-mono text-xs">POST</span>
              <span class="text-gray-700 dark:text-gray-300">/api/auth/forgot-password</span>
              <span class="text-gray-400 dark:text-gray-500">- Password reset request</span>
            </div>
            <div class="flex items-center gap-2 text-sm">
              <span class="px-2 py-0.5 rounded bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 font-mono text-xs">POST</span>
              <span class="text-gray-700 dark:text-gray-300">/api/tips/submit</span>
              <span class="text-gray-400 dark:text-gray-500">- Public tip submission</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Save Button -->
      <div class="mt-8 flex justify-end gap-3">
        <button
          @click="testProvider"
          :disabled="saving || !settings.enabled"
          class="px-5 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 font-medium disabled:opacity-50 disabled:cursor-not-allowed"
        >
          Test Configuration
        </button>
        <button
          @click="saveSettings"
          :disabled="saving"
          class="px-6 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium disabled:opacity-50 disabled:cursor-not-allowed"
        >
          {{ saving ? 'Saving...' : 'Save Settings' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue';

interface CaptchaSettings {
  enabled: boolean;
  provider: 'recaptcha' | 'turnstile';
  recaptcha: {
    site_key: string;
    secret_key: string;
    score_threshold: number;
  };
  turnstile: {
    site_key: string;
    secret_key: string;
  };
}

const settings = reactive<CaptchaSettings>({
  enabled: false,
  provider: 'recaptcha',
  recaptcha: {
    site_key: '',
    secret_key: '',
    score_threshold: 0.5,
  },
  turnstile: {
    site_key: '',
    secret_key: '',
  },
});

const saving = ref(false);
const saveMessage = ref('');
const saveError = ref(false);

const loadSettings = async () => {
  try {
    const response = await fetch('/api/admin/captcha', {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      credentials: 'include',
    });

    if (response.ok) {
      const json = await response.json();
      const data = json.data;

      settings.enabled = data.enabled;
      settings.provider = data.provider;
      settings.recaptcha.site_key = data.recaptcha.site_key;
      settings.recaptcha.secret_key = '';
      settings.recaptcha.score_threshold = data.recaptcha.score_threshold;
      settings.turnstile.site_key = data.turnstile.site_key;
      settings.turnstile.secret_key = '';
    }
  } catch (error) {
    console.error('Failed to load CAPTCHA settings:', error);
  }
};

const saveSettings = async () => {
  saving.value = true;
  saveMessage.value = '';
  saveError.value = false;

  try {
    const payload: Record<string, unknown> = {
      enabled: settings.enabled,
      provider: settings.provider,
      recaptcha_site_key: settings.recaptcha.site_key,
      recaptcha_score_threshold: settings.recaptcha.score_threshold,
      turnstile_site_key: settings.turnstile.site_key,
    };

    // Only send secret keys if the user entered a new value
    if (settings.recaptcha.secret_key) {
      payload.recaptcha_secret_key = settings.recaptcha.secret_key;
    }
    if (settings.turnstile.secret_key) {
      payload.turnstile_secret_key = settings.turnstile.secret_key;
    }

    const response = await fetch('/api/admin/captcha', {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      credentials: 'include',
      body: JSON.stringify(payload),
    });

    const json = await response.json();

    if (response.ok) {
      saveMessage.value = json.message || 'Settings saved successfully.';
      saveError.value = false;
      // Clear secret key fields after successful save
      settings.recaptcha.secret_key = '';
      settings.turnstile.secret_key = '';
    } else {
      const errors = json.errors ? Object.values(json.errors).flat().join(' ') : '';
      saveMessage.value = json.message || 'Failed to save settings.';
      if (errors) {
        saveMessage.value += ' ' + errors;
      }
      saveError.value = true;
    }
  } catch (error) {
    saveMessage.value = 'Network error. Please try again.';
    saveError.value = true;
  } finally {
    saving.value = false;
    setTimeout(() => { saveMessage.value = ''; }, 5000);
  }
};

const testProvider = async () => {
  try {
    const response = await fetch(`/api/admin/captcha/test?provider=${settings.provider}`, {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      credentials: 'include',
    });

    const json = await response.json();
    saveMessage.value = json.message;
    saveError.value = !response.ok;
    setTimeout(() => { saveMessage.value = ''; }, 5000);
  } catch (error) {
    saveMessage.value = 'Failed to test provider connectivity.';
    saveError.value = true;
    setTimeout(() => { saveMessage.value = ''; }, 5000);
  }
};

onMounted(() => {
  loadSettings();
});
</script>
