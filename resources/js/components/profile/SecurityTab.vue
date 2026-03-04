<script setup lang="ts">
import { ref, reactive, computed } from 'vue';
import { useApi } from '@/composables/useApi';

interface Session {
  uuid: string;
  device_type: string;
  browser: string;
  platform: string;
  ip_address: string;
  location: string | null;
  is_current: boolean;
  last_activity_at: string;
  created_at: string;
}

defineProps<{
  sessions: Session[];
}>();

const emit = defineEmits<{
  (e: 'message', message: string, isError?: boolean): void;
  (e: 'refreshSessions'): void;
}>();

const api = useApi();
const saving = ref(false);
const showTwoFactorModal = ref(false);

const passwordForm = reactive({
  current: '',
  new: '',
  confirm: ''
});

const twoFactor = reactive({
  enabled: false,
  qrCode: '',
  verificationCode: ''
});

const isPasswordValid = computed(() => {
  return passwordForm.current &&
    passwordForm.new &&
    passwordForm.new.length >= 8 &&
    passwordForm.new === passwordForm.confirm;
});

const changePassword = async () => {
  if (!isPasswordValid.value) return;

  saving.value = true;
  try {
    await api.put('/account/password', {
      current_password: passwordForm.current,
      password: passwordForm.new,
      password_confirmation: passwordForm.confirm,
    });
    passwordForm.current = '';
    passwordForm.new = '';
    passwordForm.confirm = '';
    emit('message', 'Password changed successfully!');
  } catch (error: any) {
    emit('message', error.message || 'Failed to change password', true);
  } finally {
    saving.value = false;
  }
};

const enableTwoFactor = async () => {
  try {
    const response = await api.post<{ data: { qr_code: string } }>('/account/2fa/enable');
    twoFactor.qrCode = response.data.qr_code;
    showTwoFactorModal.value = true;
  } catch (error: any) {
    emit('message', error.message || 'Failed to enable 2FA', true);
  }
};

const verifyTwoFactor = async () => {
  if (!twoFactor.verificationCode) return;

  saving.value = true;
  try {
    await api.post('/account/2fa/verify', {
      code: twoFactor.verificationCode,
    });
    twoFactor.enabled = true;
    showTwoFactorModal.value = false;
    twoFactor.verificationCode = '';
    emit('message', 'Two-factor authentication enabled!');
  } catch (error: any) {
    emit('message', error.message || 'Failed to verify 2FA code', true);
  } finally {
    saving.value = false;
  }
};

const disableTwoFactor = async () => {
  if (confirm('Are you sure you want to disable two-factor authentication?')) {
    try {
      await api.post('/account/2fa/disable');
      twoFactor.enabled = false;
      emit('message', 'Two-factor authentication disabled');
    } catch (error: any) {
      emit('message', error.message || 'Failed to disable 2FA', true);
    }
  }
};

const revokeSession = async (sessionUuid: string) => {
  try {
    await api.delete(`/account/sessions/${sessionUuid}`);
    emit('refreshSessions');
    emit('message', 'Session revoked');
  } catch (error: any) {
    emit('message', error.message || 'Failed to revoke session', true);
  }
};

const revokeAllSessions = async () => {
  if (confirm('Are you sure you want to revoke all other sessions?')) {
    try {
      await api.delete('/account/sessions');
      emit('refreshSessions');
      emit('message', 'All other sessions revoked');
    } catch (error: any) {
      emit('message', error.message || 'Failed to revoke sessions', true);
    }
  }
};
</script>

<template>
  <div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
      <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Change Password</h2>
      <div class="space-y-4 max-w-md">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Current Password</label>
          <input
            v-model="passwordForm.current"
            type="password"
            class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">New Password</label>
          <input
            v-model="passwordForm.new"
            type="password"
            class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          />
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Minimum 8 characters</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Confirm New Password</label>
          <input
            v-model="passwordForm.confirm"
            type="password"
            class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          />
        </div>
        <button
          @click="changePassword"
          :disabled="!isPasswordValid || saving"
          class="px-4 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          {{ saving ? 'Updating...' : 'Update Password' }}
        </button>
      </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
      <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Two-Factor Authentication</h2>
      <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
        <div class="flex items-center space-x-4">
          <div
            :class="[
              'w-12 h-12 rounded-full flex items-center justify-center',
              twoFactor.enabled ? 'bg-green-100 dark:bg-green-900/30' : 'bg-gray-100 dark:bg-gray-700'
            ]"
          >
            <svg
              class="w-6 h-6"
              :class="twoFactor.enabled ? 'text-green-600 dark:text-green-400' : 'text-gray-500'"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
          </div>
          <div>
            <p class="font-medium text-gray-900 dark:text-white">
              {{ twoFactor.enabled ? 'Enabled' : 'Not Enabled' }}
            </p>
            <p class="text-sm text-gray-500 dark:text-gray-400">Add an extra layer of security</p>
          </div>
        </div>
        <button
          @click="twoFactor.enabled ? disableTwoFactor() : enableTwoFactor()"
          :class="[
            'px-4 py-2 rounded-lg font-medium',
            twoFactor.enabled
              ? 'bg-red-100 text-red-700 hover:bg-red-200 dark:bg-red-900/30 dark:text-red-400'
              : 'bg-blue-600 text-white hover:bg-blue-700'
          ]"
        >
          {{ twoFactor.enabled ? 'Disable' : 'Enable' }}
        </button>
      </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
      <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Active Sessions</h2>
      <div v-if="sessions.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
        <p>No active sessions</p>
      </div>
      <div v-else class="space-y-3">
        <div
          v-for="session in sessions"
          :key="session.uuid"
          class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg"
        >
          <div class="flex items-center space-x-3">
            <svg class="w-8 h-8 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
            <div>
              <p class="font-medium text-gray-900 dark:text-white">{{ session.browser }} on {{ session.platform }}</p>
              <p class="text-sm text-gray-500 dark:text-gray-400">
                {{ session.location || session.ip_address }} -
                {{ session.is_current ? 'Now' : new Date(session.last_activity_at).toLocaleString() }}
              </p>
            </div>
          </div>
          <div class="flex items-center space-x-2">
            <span
              v-if="session.is_current"
              class="px-2 py-1 text-xs font-medium bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 rounded"
            >
              Current
            </span>
            <button
              v-else
              @click="revokeSession(session.uuid)"
              class="text-red-600 dark:text-red-400 hover:text-red-700 text-sm"
            >
              Revoke
            </button>
          </div>
        </div>
      </div>
      <button
        @click="revokeAllSessions"
        class="mt-4 text-red-600 dark:text-red-400 hover:text-red-700 text-sm font-medium"
      >
        Revoke All Other Sessions
      </button>
    </div>

    <!-- Two-Factor Setup Modal -->
    <div
      v-if="showTwoFactorModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
    >
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Set Up Two-Factor Authentication</h3>
        <div class="text-center mb-4">
          <p class="text-gray-600 dark:text-gray-400 mb-4">
            Scan this QR code with your authenticator app
          </p>
          <div class="inline-block p-4 bg-white rounded-lg">
            <div class="w-40 h-40 bg-gray-200 rounded flex items-center justify-center">
              <span class="text-gray-500 text-sm">QR Code</span>
            </div>
          </div>
        </div>
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Verification Code</label>
          <input
            v-model="twoFactor.verificationCode"
            type="text"
            maxlength="6"
            class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-center text-2xl tracking-widest"
            placeholder="000000"
          />
        </div>
        <div class="flex justify-end space-x-3">
          <button
            @click="showTwoFactorModal = false"
            class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg font-medium"
          >
            Cancel
          </button>
          <button
            @click="verifyTwoFactor"
            :disabled="!twoFactor.verificationCode || saving"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 disabled:opacity-50"
          >
            {{ saving ? 'Verifying...' : 'Verify & Enable' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
