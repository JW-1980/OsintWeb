<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <!-- Public Navigation -->
    <nav class="fixed top-0 left-0 right-0 z-50 bg-white/80 dark:bg-gray-900/80 backdrop-blur-md border-b border-gray-200 dark:border-gray-800">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
          <div class="flex items-center space-x-2">
            <router-link to="/" class="flex items-center space-x-2">
              <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                </svg>
              </div>
              <span class="text-xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">OsintWeb</span>
            </router-link>
          </div>
          <div class="hidden md:flex items-center space-x-6">
            <router-link to="/explore" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors">Map</router-link>
            <router-link to="/explore/events" class="text-blue-600 dark:text-blue-400 font-medium">Events</router-link>
            <router-link to="/explore/equipment" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors">Equipment</router-link>
            <router-link to="/explore/actors" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors">Actors</router-link>
            <router-link to="/explore/conflicts" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors">Conflicts</router-link>
          </div>
          <div class="flex items-center space-x-4">
            <button @click="toggleTheme" class="p-2 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
              <svg v-if="isDark" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
              </svg>
              <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
              </svg>
            </button>
            <router-link to="/login" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors">Login</router-link>
            <router-link to="/register" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-medium rounded-lg transition-all">
              Sign Up
            </router-link>
          </div>
        </div>
      </div>
    </nav>

    <!-- Main Content -->
    <div class="pt-24 pb-12 px-4 sm:px-6 lg:px-8">
      <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
          <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Verified Events</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">Browse all verified OSINT events from our database</p>
          </div>
          <div class="mt-4 md:mt-0 flex items-center space-x-3">
            <button
              @click="viewMode = 'grid'"
              :class="['p-2 rounded-lg transition-colors', viewMode === 'grid' ? 'bg-blue-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 border border-gray-300 dark:border-gray-600']"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
              </svg>
            </button>
            <button
              @click="viewMode = 'list'"
              :class="['p-2 rounded-lg transition-colors', viewMode === 'list' ? 'bg-blue-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 border border-gray-300 dark:border-gray-600']"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
              </svg>
            </button>
          </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border border-gray-200 dark:border-gray-700">
            <p class="text-sm text-gray-500 dark:text-gray-400">Total Events</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.total.toLocaleString() }}</p>
          </div>
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border border-gray-200 dark:border-gray-700">
            <p class="text-sm text-gray-500 dark:text-gray-400">This Week</p>
            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ stats.thisWeek }}</p>
          </div>
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border border-gray-200 dark:border-gray-700">
            <p class="text-sm text-gray-500 dark:text-gray-400">Verified Rate</p>
            <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ stats.verifiedRate }}%</p>
          </div>
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border border-gray-200 dark:border-gray-700">
            <p class="text-sm text-gray-500 dark:text-gray-400">Countries</p>
            <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ stats.countries }}</p>
          </div>
        </div>

        <!-- Filters -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 mb-8 border border-gray-200 dark:border-gray-700">
          <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div class="md:col-span-2">
              <div class="relative">
                <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input
                  v-model="filters.search"
                  type="text"
                  placeholder="Search events..."
                  class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
              </div>
            </div>
            <select
              v-model="filters.type"
              class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="">All Types</option>
              <option value="combat_engagement">Combat Engagement</option>
              <option value="airstrike">Airstrike</option>
              <option value="artillery_strike">Artillery Strike</option>
              <option value="missile_strike">Missile Strike</option>
              <option value="equipment_destroyed">Equipment Destroyed</option>
              <option value="troop_movement">Troop Movement</option>
            </select>
            <input
              v-model="filters.dateFrom"
              type="date"
              class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
            <input
              v-model="filters.dateTo"
              type="date"
              class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>
        </div>

        <!-- Grid View -->
        <div v-if="viewMode === 'grid'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <router-link
            v-for="event in paginatedEvents"
            :key="event.id"
            :to="{ name: 'explore-event-detail', params: { id: event.id } }"
            class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden hover:shadow-lg transition-all border border-gray-200 dark:border-gray-700 group"
          >
            <div class="p-6">
              <div class="flex items-center justify-between mb-3">
                <span :class="getEventBadgeClass(event.event_type)" class="px-3 py-1 text-xs font-medium rounded-full">
                  {{ formatEventType(event.event_type) }}
                </span>
                <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400">
                  Verified
                </span>
              </div>
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                {{ event.title }}
              </h3>
              <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                {{ event.location_name }}
              </p>
              <p v-if="event.description" class="text-sm text-gray-600 dark:text-gray-300 line-clamp-2 mb-4">
                {{ event.description }}
              </p>
              <div class="flex items-center justify-between text-sm">
                <span class="text-gray-500 dark:text-gray-400">
                  {{ formatDate(event.occurred_at) }}
                </span>
                <span v-if="event.source_count" class="text-gray-500 dark:text-gray-400">
                  {{ event.source_count }} sources
                </span>
              </div>
            </div>
          </router-link>
        </div>

        <!-- List View -->
        <div v-else class="space-y-4">
          <router-link
            v-for="event in paginatedEvents"
            :key="event.id"
            :to="{ name: 'explore-event-detail', params: { id: event.id } }"
            class="block bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden hover:shadow-lg transition-all border border-gray-200 dark:border-gray-700"
          >
            <div class="p-6">
              <div class="flex flex-col md:flex-row md:items-start md:justify-between">
                <div class="flex-1">
                  <div class="flex items-center space-x-2 mb-2">
                    <span :class="getEventBadgeClass(event.event_type)" class="px-3 py-1 text-xs font-medium rounded-full">
                      {{ formatEventType(event.event_type) }}
                    </span>
                    <span :class="getConfidenceBadgeClass(event.confidence_level)" class="px-3 py-1 text-xs font-medium rounded-full">
                      {{ event.confidence_level }}
                    </span>
                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400">
                      Verified
                    </span>
                  </div>
                  <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">{{ event.title }}</h3>
                  <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">{{ event.location_name }}</p>
                  <p v-if="event.description" class="text-sm text-gray-600 dark:text-gray-300 line-clamp-2">
                    {{ event.description }}
                  </p>
                </div>
                <div class="mt-4 md:mt-0 md:ml-6 md:text-right flex-shrink-0">
                  <p class="text-sm text-gray-500 dark:text-gray-400">{{ formatDate(event.occurred_at) }}</p>
                  <p v-if="event.source_count" class="text-sm text-blue-600 dark:text-blue-400 mt-1">{{ event.source_count }} sources</p>
                </div>
              </div>
            </div>
          </router-link>
        </div>

        <!-- Pagination -->
        <div class="mt-8 flex flex-col md:flex-row items-center justify-between">
          <p class="text-sm text-gray-500 dark:text-gray-400 mb-4 md:mb-0">
            Showing {{ (currentPage - 1) * perPage + 1 }} to {{ Math.min(currentPage * perPage, filteredEvents.length) }} of {{ filteredEvents.length }} events
          </p>
          <div class="flex items-center space-x-2">
            <button
              @click="currentPage--"
              :disabled="currentPage === 1"
              class="px-4 py-2 rounded-lg bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
              Previous
            </button>
            <div class="flex items-center space-x-1">
              <button
                v-for="page in visiblePages"
                :key="page"
                @click="currentPage = page"
                :class="[
                  'w-10 h-10 rounded-lg font-medium transition-colors',
                  currentPage === page
                    ? 'bg-blue-600 text-white'
                    : 'bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700'
                ]"
              >
                {{ page }}
              </button>
            </div>
            <button
              @click="currentPage++"
              :disabled="currentPage === totalPages"
              class="px-4 py-2 rounded-lg bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
              Next
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-8 px-4 sm:px-6 lg:px-8">
      <div class="max-w-7xl mx-auto text-center">
        <p class="text-gray-400">Browse verified OSINT events. <router-link to="/register" class="text-blue-400 hover:underline">Create an account</router-link> to access advanced features.</p>
      </div>
    </footer>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed } from 'vue';
import { useThemeStore } from '@/stores/theme';

const themeStore = useThemeStore();
const isDark = computed(() => themeStore.isDark);

const toggleTheme = () => {
  themeStore.toggleTheme();
};

const viewMode = ref<'grid' | 'list'>('grid');
const currentPage = ref(1);
const perPage = ref(12);

const stats = ref({
  total: 15847,
  thisWeek: 342,
  verifiedRate: 78,
  countries: 24
});

const filters = reactive({
  search: '',
  type: '',
  dateFrom: '',
  dateTo: ''
});

interface PublicEvent {
  id: number;
  title: string;
  event_type: string;
  description: string;
  location_name: string;
  occurred_at: string;
  confidence_level: 'confirmed' | 'likely';
  verified: boolean;
  source_count: number;
}

const events = ref<PublicEvent[]>([
  { id: 1, title: 'Artillery strike on military positions', event_type: 'artillery_strike', description: 'Multiple MLRS strikes reported targeting defensive positions near the front line.', location_name: 'Zaporizhzhia Oblast', occurred_at: '2024-01-16T14:30:00Z', confidence_level: 'confirmed', verified: true, source_count: 5 },
  { id: 2, title: 'Air defense engagement over Kyiv', event_type: 'airstrike', description: 'Air defense systems engaged incoming cruise missiles overnight.', location_name: 'Kyiv, Ukraine', occurred_at: '2024-01-16T06:15:00Z', confidence_level: 'confirmed', verified: true, source_count: 8 },
  { id: 3, title: 'Tank destroyed by FPV drone', event_type: 'equipment_destroyed', description: 'T-72B3 main battle tank destroyed by first-person view drone strike.', location_name: 'Donetsk Oblast', occurred_at: '2024-01-15T18:45:00Z', confidence_level: 'confirmed', verified: true, source_count: 3 },
  { id: 4, title: 'Troop movement detected via satellite', event_type: 'troop_movement', description: 'Satellite imagery shows significant troop concentration near border area.', location_name: 'Luhansk Oblast', occurred_at: '2024-01-15T12:00:00Z', confidence_level: 'likely', verified: true, source_count: 2 },
  { id: 5, title: 'Missile strike on infrastructure', event_type: 'missile_strike', description: 'Cruise missile struck energy infrastructure, causing power outages.', location_name: 'Kharkiv, Ukraine', occurred_at: '2024-01-14T22:30:00Z', confidence_level: 'confirmed', verified: true, source_count: 7 },
  { id: 6, title: 'Combat engagement near Bakhmut', event_type: 'combat_engagement', description: 'Infantry clashes reported in the suburbs with both sides claiming territorial gains.', location_name: 'Bakhmut, Donetsk Oblast', occurred_at: '2024-01-14T16:20:00Z', confidence_level: 'confirmed', verified: true, source_count: 4 },
  { id: 7, title: 'Artillery barrage on supply routes', event_type: 'artillery_strike', description: 'Heavy artillery fire targeted logistics convoy along main supply route.', location_name: 'Kherson Oblast', occurred_at: '2024-01-13T09:45:00Z', confidence_level: 'confirmed', verified: true, source_count: 3 },
  { id: 8, title: 'Drone swarm attack reported', event_type: 'airstrike', description: 'Multiple drones engaged over military installation.', location_name: 'Crimea', occurred_at: '2024-01-13T03:20:00Z', confidence_level: 'confirmed', verified: true, source_count: 6 },
  { id: 9, title: 'IFV column destroyed', event_type: 'equipment_destroyed', description: 'Multiple infantry fighting vehicles destroyed in ambush operation.', location_name: 'Zaporizhzhia Oblast', occurred_at: '2024-01-12T14:15:00Z', confidence_level: 'confirmed', verified: true, source_count: 4 }
]);

const filteredEvents = computed(() => {
  return events.value.filter(event => {
    if (!event.verified) return false;
    const matchesSearch = !filters.search ||
      event.title.toLowerCase().includes(filters.search.toLowerCase()) ||
      event.location_name.toLowerCase().includes(filters.search.toLowerCase());
    const matchesType = !filters.type || event.event_type === filters.type;
    const matchesDateFrom = !filters.dateFrom || new Date(event.occurred_at) >= new Date(filters.dateFrom);
    const matchesDateTo = !filters.dateTo || new Date(event.occurred_at) <= new Date(filters.dateTo);
    return matchesSearch && matchesType && matchesDateFrom && matchesDateTo;
  });
});

const totalPages = computed(() => Math.ceil(filteredEvents.value.length / perPage.value) || 1);

const paginatedEvents = computed(() => {
  const start = (currentPage.value - 1) * perPage.value;
  return filteredEvents.value.slice(start, start + perPage.value);
});

const visiblePages = computed(() => {
  const pages: number[] = [];
  const start = Math.max(1, currentPage.value - 2);
  const end = Math.min(totalPages.value, start + 4);
  for (let i = start; i <= end; i++) {
    pages.push(i);
  }
  return pages;
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

const getConfidenceBadgeClass = (confidence: string) => {
  const classes: Record<string, string> = {
    'confirmed': 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400',
    'likely': 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400'
  };
  return classes[confidence] || 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-400';
};

const formatEventType = (type: string) => {
  return type.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
};

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
