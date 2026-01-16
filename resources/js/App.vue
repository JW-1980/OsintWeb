<script setup lang="ts">
import { onMounted } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { useThemeStore } from '@/stores/theme';
import AppHeader from '@/components/layout/AppHeader.vue';

const authStore = useAuthStore();
const themeStore = useThemeStore();

onMounted(async () => {
  // Initialize theme
  themeStore.initTheme();

  // Fetch user if token exists
  if (authStore.token) {
    try {
      await authStore.fetchUser();
    } catch (error) {
      console.error('Failed to fetch user:', error);
    }
  }
});
</script>

<template>
  <div id="app" class="min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors duration-200">
    <AppHeader v-if="authStore.isAuthenticated" />

    <main :class="{ 'pt-16': authStore.isAuthenticated }">
      <div :class="{ 'container mx-auto px-4 py-6': $route.meta.containerized !== false }">
        <router-view />
      </div>
    </main>
  </div>
</template>

<style>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

#app {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}

.container {
  max-width: 1440px;
}

a {
  text-decoration: none;
  color: inherit;
}

/* Dark mode transitions */
.dark {
  color-scheme: dark;
}
</style>
