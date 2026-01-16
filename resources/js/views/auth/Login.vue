<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8">
      <h1 class="text-2xl font-bold text-center text-gray-900 mb-6">Login to OsintWeb</h1>

      <!-- Error message -->
      <div v-if="error" class="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded">
        {{ error }}
      </div>

      <form @submit.prevent="handleLogin" class="space-y-4">
        <div>
          <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
          <input
            id="email"
            v-model="form.email"
            type="email"
            required
            :disabled="loading"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-osint-primary-500 focus:ring-osint-primary-500"
          />
        </div>
        <div>
          <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
          <input
            id="password"
            v-model="form.password"
            type="password"
            required
            :disabled="loading"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-osint-primary-500 focus:ring-osint-primary-500"
          />
        </div>
        <button
          type="submit"
          :disabled="loading"
          class="w-full py-2 px-4 bg-osint-primary-600 text-white rounded-md hover:bg-osint-primary-700 focus:outline-none focus:ring-2 focus:ring-osint-primary-500 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          {{ loading ? 'Logging in...' : 'Login' }}
        </button>
      </form>
      <p class="mt-4 text-center text-sm text-gray-600">
        Don't have an account?
        <router-link to="/register" class="text-osint-primary-600 hover:text-osint-primary-500">Register</router-link>
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { reactive, ref, nextTick } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();

const form = reactive({
  email: '',
  password: ''
});

const loading = ref(false);
const error = ref('');

const handleLogin = async () => {
  error.value = '';
  loading.value = true;

  try {
    await authStore.login(form.email, form.password);

    // Wait for state to update
    await nextTick();

    // Redirect to intended destination or dashboard
    const redirect = route.query.redirect as string;
    await router.replace(redirect || '/');
  } catch (err: any) {
    console.error('Login failed:', err);
    error.value = err?.message || 'Login failed. Please check your credentials.';
    loading.value = false;
  }
};
</script>
