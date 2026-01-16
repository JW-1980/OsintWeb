<script setup lang="ts">
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();

const form = reactive({
  email: ''
});

const loading = ref(false);
const error = ref('');
const success = ref(false);

const handleSubmit = async () => {
  if (!form.email) {
    error.value = 'Please enter your email address';
    return;
  }

  error.value = '';
  loading.value = true;

  try {
    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 1500));

    // In a real app, this would call the password reset API
    // await authApi.forgotPassword(form.email);

    success.value = true;
  } catch (err: any) {
    console.error('Password reset request failed:', err);
    error.value = err?.message || 'Failed to send reset email. Please try again.';
  } finally {
    loading.value = false;
  }
};

const goToLogin = () => {
  router.push('/login');
};
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-900 dark:to-gray-800 px-4">
    <div class="max-w-md w-full bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8">
      <!-- Logo -->
      <div class="flex justify-center mb-6">
        <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center shadow-lg">
          <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
          </svg>
        </div>
      </div>

      <!-- Success State -->
      <div v-if="success" class="text-center">
        <div class="w-16 h-16 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
          <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
        </div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Check Your Email</h1>
        <p class="text-gray-600 dark:text-gray-400 mb-6">
          We've sent a password reset link to <span class="font-medium text-gray-900 dark:text-white">{{ form.email }}</span>.
          Please check your inbox and follow the instructions.
        </p>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
          Didn't receive the email? Check your spam folder or try again.
        </p>
        <div class="space-y-3">
          <button
            @click="success = false"
            class="w-full py-3 px-4 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
          >
            Try Another Email
          </button>
          <button
            @click="goToLogin"
            class="w-full py-3 px-4 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-medium rounded-lg transition-all duration-200"
          >
            Return to Login
          </button>
        </div>
      </div>

      <!-- Form State -->
      <div v-else>
        <h1 class="text-2xl font-bold text-center text-gray-900 dark:text-white mb-2">Forgot Password?</h1>
        <p class="text-center text-gray-500 dark:text-gray-400 mb-6">
          Enter your email address and we'll send you a link to reset your password.
        </p>

        <!-- Error message -->
        <div v-if="error" class="mb-4 p-3 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 rounded-lg text-sm">
          <div class="flex items-center">
            <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
            </svg>
            {{ error }}
          </div>
        </div>

        <form @submit.prevent="handleSubmit" class="space-y-5">
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email Address</label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              required
              :disabled="loading"
              placeholder="you@example.com"
              class="mt-1 block w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            />
          </div>

          <button
            type="submit"
            :disabled="loading"
            class="w-full py-3 px-4 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-medium rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 flex items-center justify-center"
          >
            <svg v-if="loading" class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ loading ? 'Sending...' : 'Send Reset Link' }}
          </button>
        </form>

        <div class="mt-6 text-center">
          <router-link
            to="/login"
            class="inline-flex items-center text-sm text-blue-600 dark:text-blue-400 hover:text-blue-500 font-medium"
          >
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back to Login
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>
