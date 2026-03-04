<script setup lang="ts">
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useThemeStore } from '@/stores/theme';

const router = useRouter();
const authStore = useAuthStore();
const themeStore = useThemeStore();

const searchQuery = ref('');
const showUserMenu = ref(false);
const showMobileMenu = ref(false);

const userName = computed(() => authStore.user?.name || 'User');
const userEmail = computed(() => authStore.user?.email || '');
const userAvatar = computed(() => authStore.user?.avatar_url);
const userInitials = computed(() => {
  const name = userName.value;
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
});

const handleSearch = () => {
  if (searchQuery.value.trim()) {
    router.push({
      name: 'explore-events',
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
  showMobileMenu.value = false;
};

// Public navigation items (no login required)
const navItems = [
  { name: 'explore-map', label: 'Map', path: '/explore' },
  { name: 'explore-events', label: 'Events', path: '/explore/events' },
  { name: 'explore-equipment', label: 'Equipment', path: '/explore/equipment' },
  { name: 'explore-conflicts', label: 'Conflicts', path: '/explore/conflicts' },
  { name: 'explore-actors', label: 'Actors', path: '/explore/actors' },
];

// Authenticated-only navigation items
const authNavItems = computed(() => authStore.isAuthenticated ? [
  { name: 'dashboard', label: 'Dashboard', path: '/dashboard' },
  { name: 'analytics', label: 'Analytics', path: '/analytics' },
] : []);
</script>

<template>
  <header class="fixed top-0 left-0 right-0 z-50 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 shadow-sm">
    <div class="mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center h-16">
        <!-- Logo and Nav -->
        <div class="flex items-center">
          <router-link to="/" class="flex items-center space-x-2">
            <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
              </svg>
            </div>
            <span class="text-xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">OsintWeb</span>
          </router-link>

          <nav class="hidden lg:ml-10 lg:flex lg:space-x-1">
            <!-- Public nav items (no login required) -->
            <router-link
              v-for="item in navItems"
              :key="item.name"
              :to="item.path"
              class="px-3 py-2 rounded-lg text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
              active-class="bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400"
            >
              {{ item.label }}
            </router-link>
            <!-- Authenticated nav items -->
            <router-link
              v-for="item in authNavItems"
              :key="item.name"
              :to="item.path"
              class="px-3 py-2 rounded-lg text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
              active-class="bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400"
            >
              {{ item.label }}
            </router-link>
            <router-link
              v-if="authStore.isAdmin"
              to="/admin"
              class="px-3 py-2 rounded-lg text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
              active-class="bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400"
            >
              Admin
            </router-link>
          </nav>
        </div>

        <!-- Right side -->
        <div class="flex items-center space-x-4">
          <!-- Search -->
          <div class="hidden md:block relative">
            <input
              v-model="searchQuery"
              type="search"
              placeholder="Search events..."
              class="w-64 pl-10 pr-4 py-2 text-sm bg-gray-100 dark:bg-gray-700 border-0 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-white dark:placeholder-gray-400"
              @keyup.enter="handleSearch"
            />
            <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>

          <!-- Dark mode toggle -->
          <button
            @click="themeStore.toggleTheme()"
            class="p-2 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
            :title="themeStore.isDark ? 'Switch to light mode' : 'Switch to dark mode'"
          >
            <svg v-if="themeStore.isDark" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
            </svg>
          </button>

          <!-- Login/Register buttons for non-authenticated users -->
          <template v-if="!authStore.isAuthenticated">
            <router-link
              to="/login"
              class="hidden sm:inline-flex px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
            >
              Login
            </router-link>
            <router-link
              to="/register"
              class="hidden sm:inline-flex px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 rounded-lg transition-colors"
            >
              Register
            </router-link>
          </template>

          <!-- Authenticated user: Notifications and User menu -->
          <template v-else>
            <!-- Notifications -->
            <button class="p-2 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors relative">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
              </svg>
              <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
            </button>

            <!-- User menu -->
            <div class="relative">
              <button
                @click="showUserMenu = !showUserMenu"
                class="flex items-center space-x-3 p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
              >
                <div
                  v-if="userAvatar"
                  class="w-8 h-8 rounded-full bg-cover bg-center ring-2 ring-gray-200 dark:ring-gray-600"
                  :style="{ backgroundImage: `url(${userAvatar})` }"
                />
                <div
                  v-else
                  class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white text-sm font-semibold ring-2 ring-gray-200 dark:ring-gray-600"
                >
                  {{ userInitials }}
                </div>
                <div class="hidden md:block text-left">
                  <p class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ userName }}</p>
                  <p class="text-xs text-gray-500 dark:text-gray-400">{{ authStore.user?.role }}</p>
                </div>
                <svg class="hidden md:block w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </button>

              <!-- Dropdown -->
              <transition
                enter-active-class="transition ease-out duration-100"
                enter-from-class="transform opacity-0 scale-95"
                enter-to-class="transform opacity-100 scale-100"
                leave-active-class="transition ease-in duration-75"
                leave-from-class="transform opacity-100 scale-100"
                leave-to-class="transform opacity-0 scale-95"
              >
                <div
                  v-if="showUserMenu"
                  class="absolute right-0 mt-2 w-56 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 py-2 z-50"
                >
                  <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ userName }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ userEmail }}</p>
                  </div>
                  <div class="py-1">
                    <a
                      @click="navigateTo('settings')"
                      class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer"
                    >
                      <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      </svg>
                      Settings
                    </a>
                  </div>
                  <div class="border-t border-gray-200 dark:border-gray-700 py-1">
                    <a
                      @click="handleLogout"
                      class="flex items-center px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer"
                    >
                      <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                      </svg>
                      Logout
                    </a>
                  </div>
                </div>
              </transition>
            </div>
          </template>

          <!-- Mobile menu button -->
          <button
            @click="showMobileMenu = !showMobileMenu"
            class="lg:hidden p-2 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700"
          >
            <svg v-if="!showMobileMenu" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
            <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Mobile menu -->
    <transition
      enter-active-class="transition ease-out duration-200"
      enter-from-class="opacity-0 -translate-y-1"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition ease-in duration-150"
      leave-from-class="opacity-100 translate-y-0"
      leave-to-class="opacity-0 -translate-y-1"
    >
      <div v-if="showMobileMenu" class="lg:hidden border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
        <div class="px-4 py-3 space-y-1">
          <!-- Public nav items -->
          <router-link
            v-for="item in navItems"
            :key="item.name"
            :to="item.path"
            class="block px-3 py-2 rounded-lg text-base font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700"
            active-class="bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400"
            @click="showMobileMenu = false"
          >
            {{ item.label }}
          </router-link>
          <!-- Authenticated nav items -->
          <router-link
            v-for="item in authNavItems"
            :key="item.name"
            :to="item.path"
            class="block px-3 py-2 rounded-lg text-base font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700"
            active-class="bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400"
            @click="showMobileMenu = false"
          >
            {{ item.label }}
          </router-link>
          <router-link
            v-if="authStore.isAdmin"
            to="/admin"
            class="block px-3 py-2 rounded-lg text-base font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700"
            active-class="bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400"
            @click="showMobileMenu = false"
          >
            Admin
          </router-link>
          <!-- Mobile Login/Register for non-authenticated users -->
          <template v-if="!authStore.isAuthenticated">
            <div class="border-t border-gray-200 dark:border-gray-700 mt-2 pt-2">
              <router-link
                to="/login"
                class="block px-3 py-2 rounded-lg text-base font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700"
                @click="showMobileMenu = false"
              >
                Login
              </router-link>
              <router-link
                to="/register"
                class="block px-3 py-2 rounded-lg text-base font-medium text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 mt-1"
                @click="showMobileMenu = false"
              >
                Register
              </router-link>
            </div>
          </template>
        </div>
        <!-- Mobile search -->
        <div class="px-4 pb-3">
          <div class="relative">
            <input
              v-model="searchQuery"
              type="search"
              placeholder="Search events..."
              class="w-full pl-10 pr-4 py-2 text-sm bg-gray-100 dark:bg-gray-700 border-0 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-white dark:placeholder-gray-400"
              @keyup.enter="handleSearch"
            />
            <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>
        </div>
      </div>
    </transition>
  </header>
</template>

<style scoped>
.router-link-active {
  @apply bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400;
}
</style>
