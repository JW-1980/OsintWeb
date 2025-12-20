<script setup lang="ts">
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const router = useRouter();
const authStore = useAuthStore();

const searchQuery = ref('');
const showUserMenu = ref(false);

const userName = computed(() => authStore.user?.name || 'User');
const userAvatar = computed(() => authStore.user?.avatar_url);

const handleSearch = () => {
  if (searchQuery.value.trim()) {
    router.push({
      name: 'events',
      query: { search: searchQuery.value }
    });
  }
};

const handleLogout = async () => {
  await authStore.logout();
  router.push({ name: 'login' });
};

const navigateTo = (routeName: string) => {
  router.push({ name: routeName });
  showUserMenu.value = false;
};
</script>

<template>
  <header class="bg-white shadow-md border-b border-gray-200">
    <div class="mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center h-16">
        <div class="flex items-center">
          <router-link to="/" class="flex items-center">
            <span class="text-2xl font-bold text-blue-600">OsintWeb</span>
          </router-link>

          <nav class="hidden md:ml-10 md:flex md:space-x-8">
            <router-link
              to="/"
              class="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-900 hover:text-blue-600"
              active-class="border-b-2 border-blue-600"
            >
              Dashboard
            </router-link>
            <router-link
              to="/map"
              class="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-500 hover:text-blue-600"
              active-class="border-b-2 border-blue-600 text-gray-900"
            >
              Map
            </router-link>
            <router-link
              to="/events"
              class="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-500 hover:text-blue-600"
              active-class="border-b-2 border-blue-600 text-gray-900"
            >
              Events
            </router-link>
            <router-link
              to="/equipment"
              class="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-500 hover:text-blue-600"
              active-class="border-b-2 border-blue-600 text-gray-900"
            >
              Equipment
            </router-link>
            <router-link
              to="/analytics"
              class="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-500 hover:text-blue-600"
              active-class="border-b-2 border-blue-600 text-gray-900"
            >
              Analytics
            </router-link>
            <router-link
              v-if="authStore.isAdmin"
              to="/admin"
              class="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-500 hover:text-blue-600"
              active-class="border-b-2 border-blue-600 text-gray-900"
            >
              Admin
            </router-link>
          </nav>
        </div>

        <div class="flex items-center space-x-4">
          <div class="relative">
            <input
              v-model="searchQuery"
              type="search"
              placeholder="Search events..."
              class="w-64 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              @keyup.enter="handleSearch"
            />
            <button
              @click="handleSearch"
              class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </button>
          </div>

          <div class="relative">
            <button
              @click="showUserMenu = !showUserMenu"
              class="flex items-center space-x-2 focus:outline-none"
            >
              <div
                v-if="userAvatar"
                class="w-8 h-8 rounded-full bg-gray-300"
                :style="{ backgroundImage: `url(${userAvatar})`, backgroundSize: 'cover' }"
              />
              <div
                v-else
                class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center text-white font-semibold"
              >
                {{ userName.charAt(0).toUpperCase() }}
              </div>
              <span class="hidden md:block text-sm font-medium text-gray-700">{{ userName }}</span>
              <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </button>

            <div
              v-if="showUserMenu"
              class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50"
            >
              <a
                @click="navigateTo('settings')"
                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 cursor-pointer"
              >
                Settings
              </a>
              <a
                @click="handleLogout"
                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 cursor-pointer"
              >
                Logout
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </header>
</template>

<style scoped>
.router-link-active {
  border-bottom-width: 2px;
  border-bottom-color: #2563eb;
  color: #111827;
}
</style>
