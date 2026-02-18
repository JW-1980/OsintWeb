<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <!-- Navigation -->
    <nav class="fixed top-0 left-0 right-0 z-50 bg-white/80 dark:bg-gray-900/80 backdrop-blur-md border-b border-gray-200 dark:border-gray-800">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
          <div class="flex items-center space-x-2">
            <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
              </svg>
            </div>
            <span class="text-xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">OsintWeb</span>
          </div>
          <div class="hidden md:flex items-center space-x-8">
            <router-link to="/explore" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors">Map</router-link>
            <router-link to="/explore/events" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors">Events</router-link>
            <router-link to="/explore/equipment" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors">Equipment</router-link>
            <router-link to="/explore/actors" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors">Actors</router-link>
            <router-link to="/explore/conflicts" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors">Conflicts</router-link>
            <router-link to="/about" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors">About</router-link>
          </div>
          <div class="flex items-center space-x-4">
            <button @click="toggleTheme" class="p-2 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
              <svg v-if="themeStore.isDark" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
              </svg>
              <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
              </svg>
            </button>
            <router-link to="/login" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors">Login</router-link>
            <router-link to="/register" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-medium rounded-lg transition-all">
              Register
            </router-link>
          </div>
        </div>
      </div>
    </nav>

    <!-- Hero Section -->
    <section class="pt-28 pb-12 px-4 sm:px-6 lg:px-8">
      <div class="max-w-7xl mx-auto text-center">
        <h1 class="text-4xl sm:text-5xl font-bold text-gray-900 dark:text-white mb-4">
          Open Source Intelligence
          <span class="bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">Platform</span>
        </h1>
        <p class="max-w-2xl mx-auto text-lg text-gray-600 dark:text-gray-400 mb-8">
          Track, analyze, and visualize global events with real-time mapping and comprehensive data management.
        </p>
        <router-link to="/explore" class="inline-flex items-center px-8 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all">
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
          </svg>
          Explore Map
        </router-link>
      </div>
    </section>

    <!-- Stats Section -->
    <section class="py-8 px-4 sm:px-6 lg:px-8">
      <div class="max-w-7xl mx-auto">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <router-link to="/explore/events" class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow border border-gray-200 dark:border-gray-700 hover:shadow-lg transition-shadow text-center group">
            <p class="text-3xl font-bold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">{{ stats.events }}</p>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Events</p>
          </router-link>
          <router-link to="/explore/equipment" class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow border border-gray-200 dark:border-gray-700 hover:shadow-lg transition-shadow text-center group">
            <p class="text-3xl font-bold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">{{ stats.equipment }}</p>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Equipment</p>
          </router-link>
          <router-link to="/explore/actors" class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow border border-gray-200 dark:border-gray-700 hover:shadow-lg transition-shadow text-center group">
            <p class="text-3xl font-bold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">{{ stats.actors }}</p>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Actors</p>
          </router-link>
          <router-link to="/explore/conflicts" class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow border border-gray-200 dark:border-gray-700 hover:shadow-lg transition-shadow text-center group">
            <p class="text-3xl font-bold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">{{ stats.conflicts }}</p>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Conflicts</p>
          </router-link>
        </div>
      </div>
    </section>

    <!-- Recent Activity Section -->
    <section class="py-8 px-4 sm:px-6 lg:px-8">
      <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Recent Activity</h2>
          <router-link to="/explore/events" class="text-blue-600 dark:text-blue-400 hover:underline text-sm font-medium">
            View all events
          </router-link>
        </div>

        <!-- Empty State -->
        <div v-if="recentEvents.length === 0" class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 p-12 text-center">
          <svg class="w-16 h-16 mx-auto text-gray-400 dark:text-gray-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
          </svg>
          <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">No events yet</h3>
          <p class="text-gray-600 dark:text-gray-400 mb-6 max-w-md mx-auto">
            This platform is just getting started. Events, equipment sightings, and actor information will appear here as they are added.
          </p>
          <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
            <router-link to="/explore" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
              Explore the Map
            </router-link>
            <router-link to="/submit-tip" class="px-6 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-900 dark:text-white font-medium rounded-lg transition-colors">
              Submit a Tip
            </router-link>
          </div>
        </div>

        <!-- Events List -->
        <div v-else class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
          <router-link
            v-for="event in recentEvents"
            :key="event.id"
            :to="{ name: 'explore-event-detail', params: { id: event.id } }"
            class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 p-5 hover:shadow-lg transition-all group"
          >
            <div class="flex items-center justify-between mb-2">
              <span :class="getEventBadgeClass(event.event_type)" class="px-2 py-1 text-xs font-medium rounded-full">
                {{ formatEventType(event.event_type) }}
              </span>
              <span class="text-xs text-gray-500 dark:text-gray-400">{{ formatDate(event.occurred_at) }}</span>
            </div>
            <h3 class="font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors mb-1">
              {{ event.title }}
            </h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ event.location_name }}</p>
          </router-link>
        </div>
      </div>
    </section>

    <!-- Browse Features Section -->
    <section class="py-12 px-4 sm:px-6 lg:px-8 bg-white dark:bg-gray-800 border-y border-gray-200 dark:border-gray-700">
      <div class="max-w-7xl mx-auto">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 text-center">Browse by Category</h2>
        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
          <!-- Map -->
          <router-link to="/explore" class="bg-gray-50 dark:bg-gray-700 rounded-xl p-6 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors group border border-gray-200 dark:border-gray-600">
            <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center mb-4 group-hover:bg-blue-200 dark:group-hover:bg-blue-800/40 transition-colors">
              <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
              </svg>
            </div>
            <h3 class="font-semibold text-gray-900 dark:text-white mb-1">Interactive Map</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">Visualize events and zones on a global map</p>
          </router-link>

          <!-- Events -->
          <router-link to="/explore/events" class="bg-gray-50 dark:bg-gray-700 rounded-xl p-6 hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors group border border-gray-200 dark:border-gray-600">
            <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center mb-4 group-hover:bg-green-200 dark:group-hover:bg-green-800/40 transition-colors">
              <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
              </svg>
            </div>
            <h3 class="font-semibold text-gray-900 dark:text-white mb-1">Events</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">Browse verified incidents and reports</p>
          </router-link>

          <!-- Equipment -->
          <router-link to="/explore/equipment" class="bg-gray-50 dark:bg-gray-700 rounded-xl p-6 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-colors group border border-gray-200 dark:border-gray-600">
            <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg flex items-center justify-center mb-4 group-hover:bg-indigo-200 dark:group-hover:bg-indigo-800/40 transition-colors">
              <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
              </svg>
            </div>
            <h3 class="font-semibold text-gray-900 dark:text-white mb-1">Equipment</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">Military equipment database and sightings</p>
          </router-link>

          <!-- Actors -->
          <router-link to="/explore/actors" class="bg-gray-50 dark:bg-gray-700 rounded-xl p-6 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors group border border-gray-200 dark:border-gray-600">
            <div class="w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-lg flex items-center justify-center mb-4 group-hover:bg-red-200 dark:group-hover:bg-red-800/40 transition-colors">
              <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
            </div>
            <h3 class="font-semibold text-gray-900 dark:text-white mb-1">Actors</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">State and non-state actor profiles</p>
          </router-link>

          <!-- Conflicts -->
          <router-link to="/explore/conflicts" class="bg-gray-50 dark:bg-gray-700 rounded-xl p-6 hover:bg-yellow-50 dark:hover:bg-yellow-900/20 transition-colors group border border-gray-200 dark:border-gray-600">
            <div class="w-12 h-12 bg-yellow-100 dark:bg-yellow-900/30 rounded-lg flex items-center justify-center mb-4 group-hover:bg-yellow-200 dark:group-hover:bg-yellow-800/40 transition-colors">
              <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
            </div>
            <h3 class="font-semibold text-gray-900 dark:text-white mb-1">Conflicts</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">Active and historical conflict zones</p>
          </router-link>

          <!-- Submit Tip -->
          <router-link to="/submit-tip" class="bg-gray-50 dark:bg-gray-700 rounded-xl p-6 hover:bg-purple-50 dark:hover:bg-purple-900/20 transition-colors group border border-gray-200 dark:border-gray-600">
            <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center mb-4 group-hover:bg-purple-200 dark:group-hover:bg-purple-800/40 transition-colors">
              <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
              </svg>
            </div>
            <h3 class="font-semibold text-gray-900 dark:text-white mb-1">Submit a Tip</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">Report an event or sighting</p>
          </router-link>

          <!-- About -->
          <router-link to="/about" class="bg-gray-50 dark:bg-gray-700 rounded-xl p-6 hover:bg-teal-50 dark:hover:bg-teal-900/20 transition-colors group border border-gray-200 dark:border-gray-600">
            <div class="w-12 h-12 bg-teal-100 dark:bg-teal-900/30 rounded-lg flex items-center justify-center mb-4 group-hover:bg-teal-200 dark:group-hover:bg-teal-800/40 transition-colors">
              <svg class="w-6 h-6 text-teal-600 dark:text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <h3 class="font-semibold text-gray-900 dark:text-white mb-1">About</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">Learn about this platform</p>
          </router-link>

          <!-- Contact -->
          <router-link to="/contact" class="bg-gray-50 dark:bg-gray-700 rounded-xl p-6 hover:bg-pink-50 dark:hover:bg-pink-900/20 transition-colors group border border-gray-200 dark:border-gray-600">
            <div class="w-12 h-12 bg-pink-100 dark:bg-pink-900/30 rounded-lg flex items-center justify-center mb-4 group-hover:bg-pink-200 dark:group-hover:bg-pink-800/40 transition-colors">
              <svg class="w-6 h-6 text-pink-600 dark:text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
              </svg>
            </div>
            <h3 class="font-semibold text-gray-900 dark:text-white mb-1">Contact</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">Get in touch with us</p>
          </router-link>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-8 px-4 sm:px-6 lg:px-8">
      <div class="max-w-7xl mx-auto">
        <div class="flex flex-col md:flex-row items-center justify-between">
          <div class="flex items-center space-x-2 mb-4 md:mb-0">
            <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
              </svg>
            </div>
            <span class="text-xl font-bold">OsintWeb</span>
          </div>
          <div class="flex items-center space-x-6 text-gray-400 text-sm">
            <router-link to="/about" class="hover:text-white transition-colors">About</router-link>
            <router-link to="/contact" class="hover:text-white transition-colors">Contact</router-link>
            <a href="#" class="hover:text-white transition-colors">Privacy</a>
            <a href="#" class="hover:text-white transition-colors">Terms</a>
          </div>
        </div>
        <div class="mt-6 pt-6 border-t border-gray-800 text-center text-gray-400 text-sm">
          <p>&copy; {{ new Date().getFullYear() }} OsintWeb. Open Source Intelligence Platform.</p>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useThemeStore } from '@/stores/theme';

const themeStore = useThemeStore();

const toggleTheme = () => {
  themeStore.toggleTheme();
};

// Stats - will show 0 when no data
const stats = ref({
  events: 0,
  equipment: 0,
  actors: 0,
  conflicts: 0
});

// Recent events - empty by default
interface RecentEvent {
  id: number;
  title: string;
  event_type: string;
  location_name: string;
  occurred_at: string;
}

const recentEvents = ref<RecentEvent[]>([]);

// Load stats and recent events from API
onMounted(async () => {
  try {
    const [statsResponse, eventsResponse] = await Promise.allSettled([
      api.get('/stats/overview'),
      api.get('/events', { params: { per_page: 5, sort: '-occurred_at' } }),
    ]);

    if (statsResponse.status === 'fulfilled' && statsResponse.value?.data?.data) {
      const data = statsResponse.value.data.data;
      stats.value.totalEvents = data.total_events ?? stats.value.totalEvents;
      stats.value.totalEquipment = data.total_equipment ?? stats.value.totalEquipment;
      stats.value.activeConflicts = data.active_conflicts ?? stats.value.activeConflicts;
      stats.value.countriesTracked = data.countries_tracked ?? stats.value.countriesTracked;
    }

    if (eventsResponse.status === 'fulfilled' && eventsResponse.value?.data?.data) {
      recentEvents.value = eventsResponse.value.data.data;
    }
  } catch (error) {
    console.error('Failed to load home page data:', error);
  }
});

const getEventBadgeClass = (type: string) => {
  const classes: Record<string, string> = {
    'combat_engagement': 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400',
    'airstrike': 'bg-pink-100 dark:bg-pink-900/30 text-pink-700 dark:text-pink-400',
    'artillery_strike': 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400',
    'missile_strike': 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400',
    'equipment_destroyed': 'bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400',
    'troop_movement': 'bg-teal-100 dark:bg-teal-900/30 text-teal-700 dark:text-teal-400'
  };
  return classes[type] || 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-400';
};

const formatEventType = (type: string) => {
  return type.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
};

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric'
  });
};
</script>
