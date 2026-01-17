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
            <router-link to="/explore/events" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors">Events</router-link>
            <router-link to="/explore/equipment" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors">Equipment</router-link>
            <router-link to="/explore/actors" class="text-blue-600 dark:text-blue-400 font-medium">Actors</router-link>
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
        <div class="mb-8">
          <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Actor Database</h1>
          <p class="mt-2 text-gray-600 dark:text-gray-400">State and non-state actors tracked in our OSINT database</p>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border border-gray-200 dark:border-gray-700">
            <p class="text-sm text-gray-500 dark:text-gray-400">Total Actors</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.total }}</p>
          </div>
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border border-gray-200 dark:border-gray-700">
            <p class="text-sm text-gray-500 dark:text-gray-400">State Actors</p>
            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ stats.state }}</p>
          </div>
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border border-gray-200 dark:border-gray-700">
            <p class="text-sm text-gray-500 dark:text-gray-400">Non-State Actors</p>
            <p class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ stats.nonState }}</p>
          </div>
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border border-gray-200 dark:border-gray-700">
            <p class="text-sm text-gray-500 dark:text-gray-400">Active in Conflicts</p>
            <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ stats.active }}</p>
          </div>
        </div>

        <!-- Filters -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 mb-8 border border-gray-200 dark:border-gray-700">
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="md:col-span-2">
              <div class="relative">
                <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input
                  v-model="filters.search"
                  type="text"
                  placeholder="Search actors..."
                  class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
              </div>
            </div>
            <select
              v-model="filters.type"
              class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="">All Types</option>
              <option value="STATE">State Actor</option>
              <option value="SEPARATIST">Separatist</option>
              <option value="INSURGENT">Insurgent</option>
              <option value="TERRORIST">Terrorist</option>
              <option value="MILITIA">Militia</option>
              <option value="PMC">PMC</option>
              <option value="REBEL">Rebel</option>
            </select>
            <select
              v-model="filters.activity"
              class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="">All Activity Levels</option>
              <option value="high">High Activity</option>
              <option value="medium">Medium Activity</option>
              <option value="low">Low Activity</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="flex items-center justify-center py-16">
          <div class="flex flex-col items-center space-y-4">
            <svg class="animate-spin h-10 w-10 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <p class="text-gray-600 dark:text-gray-400">Loading actors...</p>
          </div>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="text-center py-16">
          <svg class="w-24 h-24 mx-auto text-red-400 dark:text-red-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
          <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Unable to Load Actors</h3>
          <p class="text-gray-600 dark:text-gray-400 mb-6">{{ error }}</p>
          <div class="flex items-center justify-center space-x-4">
            <button @click="fetchActors" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
              Try Again
            </button>
            <router-link to="/register" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-lg hover:from-blue-700 hover:to-indigo-700 transition-colors">
              Sign Up for Access
            </router-link>
          </div>
        </div>

        <!-- Actors Grid -->
        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <router-link
            v-for="actor in filteredActors"
            :key="actor.id"
            :to="{ name: 'explore-actor-detail', params: { id: actor.id } }"
            class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden border border-gray-200 dark:border-gray-700 hover:shadow-lg transition-all group block"
          >
            <div class="p-6">
              <div class="flex items-start justify-between mb-4">
                <div class="flex items-center space-x-3">
                  <!-- Actor Icon/Flag -->
                  <div
                    :class="getActorIconBg(actor.actor_type)"
                    class="w-12 h-12 rounded-lg flex items-center justify-center flex-shrink-0"
                  >
                    <span v-if="actor.flag_emoji" class="text-2xl">{{ actor.flag_emoji }}</span>
                    <svg v-else class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9" />
                    </svg>
                  </div>
                  <div>
                    <h3 class="font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">{{ actor.name }}</h3>
                    <p v-if="actor.short_name" class="text-sm text-gray-500 dark:text-gray-400">{{ actor.short_name }}</p>
                  </div>
                </div>
                <!-- Arrow indicator -->
                <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-500 transition-colors flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
              </div>

              <!-- Actor Type & Activity -->
              <div class="flex flex-wrap items-center gap-2 mb-4">
                <span :class="getTypeBadgeClass(actor.actor_type)" class="px-2 py-1 text-xs font-medium rounded-full">
                  {{ formatActorType(actor.actor_type) }}
                </span>
                <span :class="getActivityBadgeClass(actor.activity_level)" class="px-2 py-1 text-xs font-medium rounded-full">
                  {{ actor.activity_level }} activity
                </span>
                <span v-if="actor.is_designated_terrorist" class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400">
                  Designated Terrorist
                </span>
              </div>

              <!-- Description -->
              <p v-if="actor.description" class="text-sm text-gray-600 dark:text-gray-300 line-clamp-3 mb-4">
                {{ actor.description }}
              </p>

              <!-- Stats -->
              <div class="flex items-center justify-between text-sm">
                <div class="flex items-center space-x-4 text-gray-500 dark:text-gray-400">
                  <span v-if="actor.country" class="flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ actor.country }}
                  </span>
                  <span v-if="actor.events_count" class="flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    {{ actor.events_count }} events
                  </span>
                </div>
              </div>
            </div>
          </router-link>
        </div>

        <!-- Empty State -->
        <div v-if="!loading && !error && filteredActors.length === 0" class="text-center py-16">
          <svg class="w-24 h-24 mx-auto text-gray-400 dark:text-gray-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
          </svg>
          <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">No Actors Found</h3>
          <p class="text-gray-600 dark:text-gray-400">Try adjusting your filters or search terms.</p>
        </div>

        <!-- Pagination -->
        <div v-if="!loading && !error && filteredActors.length > 0" class="mt-8 flex flex-col md:flex-row items-center justify-between">
          <p class="text-sm text-gray-500 dark:text-gray-400 mb-4 md:mb-0">
            Showing {{ (currentPage - 1) * perPage + 1 }} to {{ Math.min(currentPage * perPage, totalItems || filteredActors.length) }} of {{ totalItems || filteredActors.length }} actors
          </p>
          <div class="flex items-center space-x-2">
            <button
              @click="currentPage--"
              :disabled="currentPage === 1"
              class="px-4 py-2 rounded-lg bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
              Previous
            </button>
            <span class="px-4 py-2 text-gray-700 dark:text-gray-300">Page {{ currentPage }} of {{ totalPages }}</span>
            <button
              @click="currentPage++"
              :disabled="currentPage >= totalPages"
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
        <p class="text-gray-400">Explore actor data. <router-link to="/register" class="text-blue-400 hover:underline">Create an account</router-link> for detailed profiles and relationships.</p>
      </div>
    </footer>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted, watch } from 'vue';
import { useThemeStore } from '@/stores/theme';
import { useApi } from '@/composables/useApi';

const api = useApi();
const themeStore = useThemeStore();
const isDark = computed(() => themeStore.isDark);

const toggleTheme = () => {
  themeStore.toggleTheme();
};

const currentPage = ref(1);
const perPage = ref(12);
const loading = ref(false);
const error = ref<string | null>(null);
const totalItems = ref(0);

const stats = ref({
  total: 0,
  state: 0,
  nonState: 0,
  active: 0
});

const filters = reactive({
  search: '',
  type: '',
  activity: ''
});

interface Actor {
  id: number;
  name: string;
  short_name: string | null;
  actor_type: string;
  country: string | null;
  flag_emoji: string | null;
  activity_level: 'high' | 'medium' | 'low' | 'inactive';
  is_designated_terrorist: boolean;
  description: string;
  events_count: number;
}

interface ActorsResponse {
  data: Actor[];
  meta?: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
  stats?: {
    total: number;
    state: number;
    non_state: number;
    active: number;
  };
}

const actors = ref<Actor[]>([]);

// Debounce timer for search
let searchTimeout: ReturnType<typeof setTimeout> | null = null;

async function fetchActors() {
  loading.value = true;
  error.value = null;

  try {
    const params: Record<string, string | number> = {
      page: currentPage.value,
      per_page: perPage.value
    };

    if (filters.search) params.search = filters.search;
    if (filters.type) params.actor_type = filters.type;
    if (filters.activity) params.activity_level = filters.activity;

    const response = await api.get<ActorsResponse>('/actors', { params });

    actors.value = response.data || [];

    if (response.meta) {
      totalItems.value = response.meta.total;
    }

    if (response.stats) {
      stats.value = {
        total: response.stats.total,
        state: response.stats.state,
        nonState: response.stats.non_state,
        active: response.stats.active
      };
    }
  } catch (err: any) {
    console.error('Failed to fetch actors:', err);
    if (err.message?.includes('401') || err.message?.includes('Unauthorized')) {
      error.value = 'Please sign in to view actor data.';
    } else {
      error.value = err.message || 'Failed to load actors. Please try again.';
    }
  } finally {
    loading.value = false;
  }
}

// Watch for filter changes with debounce
watch([() => filters.search, () => filters.type, () => filters.activity], () => {
  if (searchTimeout) clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    currentPage.value = 1;
    fetchActors();
  }, 300);
});

// Watch for page changes
watch(currentPage, () => {
  fetchActors();
});

onMounted(() => {
  fetchActors();
});

const filteredActors = computed(() => {
  // When using API, filtering is done server-side
  return actors.value;
});

const totalPages = computed(() => {
  if (totalItems.value > 0) {
    return Math.ceil(totalItems.value / perPage.value);
  }
  return Math.ceil(filteredActors.value.length / perPage.value) || 1;
});

const getActorIconBg = (type: string) => {
  const classes: Record<string, string> = {
    'STATE': 'bg-blue-500',
    'SEPARATIST': 'bg-purple-500',
    'INSURGENT': 'bg-orange-500',
    'TERRORIST': 'bg-red-500',
    'MILITIA': 'bg-yellow-500',
    'PMC': 'bg-gray-500',
    'REBEL': 'bg-green-500'
  };
  return classes[type] || 'bg-gray-500';
};

const getTypeBadgeClass = (type: string) => {
  const classes: Record<string, string> = {
    'STATE': 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400',
    'SEPARATIST': 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400',
    'INSURGENT': 'bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400',
    'TERRORIST': 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400',
    'MILITIA': 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400',
    'PMC': 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-400',
    'REBEL': 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400'
  };
  return classes[type] || 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-400';
};

const getActivityBadgeClass = (level: string) => {
  const classes: Record<string, string> = {
    'high': 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400',
    'medium': 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400',
    'low': 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400',
    'inactive': 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-400'
  };
  return classes[level] || 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-400';
};

const formatActorType = (type: string) => {
  return type.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase()).join(' ');
};
</script>

<style scoped>
.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
