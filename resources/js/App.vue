<script setup lang="ts">
import { onMounted } from 'vue';
import { useAuthStore } from '@/stores/auth';
import AppHeader from '@/components/layout/AppHeader.vue';

const authStore = useAuthStore();

onMounted(async () => {
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
  <div id="app" class="min-h-screen bg-gray-50">
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
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}

.container {
  max-width: 1280px;
}

a {
  text-decoration: none;
  color: inherit;
}
</style>
