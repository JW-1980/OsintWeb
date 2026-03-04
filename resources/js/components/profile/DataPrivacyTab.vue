<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useApi } from '@/composables/useApi';

const emit = defineEmits<{
  (e: 'message', message: string, isError?: boolean): void;
}>();

const router = useRouter();
const authStore = useAuthStore();
const api = useApi();

const saving = ref(false);
const showDeleteModal = ref(false);
const deletePassword = ref('');

const exportData = async (format: 'json' | 'csv') => {
  saving.value = true;
  try {
    await api.post('/account/data-export', { format });
    emit('message', `Your data export request has been submitted. You'll be notified when it's ready.`);
  } catch (error: any) {
    emit('message', error.message || 'Failed to request data export', true);
  } finally {
    saving.value = false;
  }
};

const deleteAccount = async () => {
  if (!deletePassword.value) {
    emit('message', 'Please enter your password to confirm deletion', true);
    return;
  }

  saving.value = true;
  try {
    await api.post('/account/deletion', {
      password: deletePassword.value,
      reason: 'user_requested',
    });
    showDeleteModal.value = false;
    emit('message', 'Account deletion request submitted. Check your email to confirm.');
    // Wait a bit then logout
    setTimeout(async () => {
      await authStore.logout();
      router.push('/');
    }, 3000);
  } catch (error: any) {
    emit('message', error.message || 'Failed to delete account', true);
  } finally {
    saving.value = false;
    deletePassword.value = '';
  }
};
</script>

<template>
  <div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
      <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Export Your Data</h2>
      <p class="text-gray-600 dark:text-gray-400 mb-4">
        Download a copy of all your data in compliance with GDPR regulations.
      </p>
      <div class="flex space-x-3">
        <button
          @click="exportData('json')"
          :disabled="saving"
          class="px-4 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 disabled:opacity-50"
        >
          Export as JSON
        </button>
        <button
          @click="exportData('csv')"
          :disabled="saving"
          class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg font-medium hover:bg-gray-300 dark:hover:bg-gray-600 disabled:opacity-50"
        >
          Export as CSV
        </button>
      </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-2 border-red-200 dark:border-red-900">
      <h2 class="text-lg font-semibold text-red-700 dark:text-red-400 mb-4">Danger Zone</h2>
      <div class="p-4 bg-red-50 dark:bg-red-900/20 rounded-lg">
        <div class="flex items-center justify-between">
          <div>
            <p class="font-medium text-red-700 dark:text-red-400">Delete Account</p>
            <p class="text-sm text-red-600 dark:text-red-500">Permanently delete your account and all associated data</p>
          </div>
          <button
            @click="showDeleteModal = true"
            class="px-4 py-2 bg-red-600 text-white rounded-lg font-medium hover:bg-red-700"
          >
            Delete Account
          </button>
        </div>
      </div>
    </div>

    <!-- Delete Account Modal -->
    <div
      v-if="showDeleteModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
    >
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Delete Account</h3>
        <p class="text-gray-600 dark:text-gray-400 mb-4">
          This action is irreversible. All your data, events, and contributions will be permanently deleted.
        </p>
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            Enter your password to confirm
          </label>
          <input
            v-model="deletePassword"
            type="password"
            class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-red-500 focus:border-transparent"
            placeholder="Your password"
          />
        </div>
        <div class="flex justify-end space-x-3">
          <button
            @click="showDeleteModal = false; deletePassword = ''"
            class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg font-medium"
          >
            Cancel
          </button>
          <button
            @click="deleteAccount"
            :disabled="saving || !deletePassword"
            class="px-4 py-2 bg-red-600 text-white rounded-lg font-medium hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ saving ? 'Deleting...' : 'Yes, Delete My Account' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
