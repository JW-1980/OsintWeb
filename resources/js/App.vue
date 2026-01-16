<script setup lang="ts">
import { onMounted, computed } from 'vue';
import { useRoute } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useThemeStore } from '@/stores/theme';
import AppHeader from '@/components/layout/AppHeader.vue';

const route = useRoute();
const authStore = useAuthStore();
const themeStore = useThemeStore();

const isPublicPage = computed(() => {
  return route.meta?.isPublic === true || route.meta?.requiresGuest === true;
});

const showHeader = computed(() => {
  return authStore.isAuthenticated && !isPublicPage.value;
});

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
    <AppHeader v-if="showHeader" />

    <main :class="{ 'pt-16': showHeader }">
      <div v-if="!isPublicPage" :class="{ 'container mx-auto px-4 py-6': $route.meta.containerized !== false }">
        <router-view />
      </div>
      <router-view v-else />
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
