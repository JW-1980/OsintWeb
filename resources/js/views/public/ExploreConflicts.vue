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
            <router-link to="/explore/actors" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors">Actors</router-link>
            <router-link to="/explore/conflicts" class="text-blue-600 dark:text-blue-400 font-medium">Conflicts</router-link>
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
          <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Active Conflicts</h1>
          <p class="mt-2 text-gray-600 dark:text-gray-400">Global conflicts tracked in our OSINT database</p>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border border-gray-200 dark:border-gray-700">
            <p class="text-sm text-gray-500 dark:text-gray-400">Active Conflicts</p>
            <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ stats.active }}</p>
          </div>
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border border-gray-200 dark:border-gray-700">
            <p class="text-sm text-gray-500 dark:text-gray-400">High Intensity</p>
            <p class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ stats.highIntensity }}</p>
          </div>
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border border-gray-200 dark:border-gray-700">
            <p class="text-sm text-gray-500 dark:text-gray-400">Regions Affected</p>
            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ stats.regions }}</p>
          </div>
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border border-gray-200 dark:border-gray-700">
            <p class="text-sm text-gray-500 dark:text-gray-400">Total Events</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.totalEvents.toLocaleString() }}</p>
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
                  placeholder="Search conflicts..."
                  class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
              </div>
            </div>
            <select
              v-model="filters.type"
              class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="">All Types</option>
              <option value="CIVIL_WAR">Civil War</option>
              <option value="INTERSTATE">Interstate</option>
              <option value="INSURGENCY">Insurgency</option>
              <option value="TERRORISM">Terrorism</option>
              <option value="ETHNIC_CONFLICT">Ethnic Conflict</option>
              <option value="BORDER_DISPUTE">Border Dispute</option>
              <option value="PROXY_WAR">Proxy War</option>
            </select>
            <select
              v-model="filters.intensity"
              class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="">All Intensity</option>
              <option value="high">High</option>
              <option value="medium">Medium</option>
              <option value="low">Low</option>
              <option value="frozen">Frozen</option>
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
            <p class="text-gray-600 dark:text-gray-400">Loading conflicts...</p>
          </div>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="text-center py-16">
          <svg class="w-24 h-24 mx-auto text-red-400 dark:text-red-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
          <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Unable to Load Conflicts</h3>
          <p class="text-gray-600 dark:text-gray-400 mb-6">{{ error }}</p>
          <button @click="fetchConflicts" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
            Try Again
          </button>
        </div>

        <!-- Conflicts List -->
        <div v-else class="space-y-6">
          <div
            v-for="conflict in filteredConflicts"
            :key="conflict.id"
            class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden border border-gray-200 dark:border-gray-700 hover:shadow-lg transition-all"
          >
            <div class="p-6">
              <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                <!-- Main Info -->
                <div class="flex-1">
                  <div class="flex flex-wrap items-center gap-2 mb-3">
                    <span :class="getIntensityBadgeClass(conflict.intensity_level)" class="px-3 py-1 text-sm font-medium rounded-full">
                      {{ conflict.intensity_level }} intensity
                    </span>
                    <span :class="getTypeBadgeClass(conflict.conflict_type)" class="px-3 py-1 text-sm font-medium rounded-full">
                      {{ formatConflictType(conflict.conflict_type) }}
                    </span>
                    <span v-if="conflict.is_active" class="px-3 py-1 text-sm font-medium rounded-full bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400">
                      Active
                    </span>
                    <span v-else class="px-3 py-1 text-sm font-medium rounded-full bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-400">
                      Ended
                    </span>
                  </div>

                  <router-link
                    :to="{ name: 'explore-conflict-detail', params: { id: conflict.id } }"
                    class="group/title"
                  >
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2 group-hover/title:text-blue-600 dark:group-hover/title:text-blue-400 transition-colors">{{ conflict.name }}</h3>
                  </router-link>

                  <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500 dark:text-gray-400 mb-3">
                    <span class="flex items-center">
                      <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                      </svg>
                      {{ conflict.region }}
                    </span>
                    <span class="flex items-center">
                      <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                      </svg>
                      Started: {{ formatDate(conflict.start_date) }}
                    </span>
                    <span v-if="conflict.end_date" class="flex items-center">
                      <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                      </svg>
                      Ended: {{ formatDate(conflict.end_date) }}
                    </span>
                  </div>

                  <p v-if="conflict.description" class="text-gray-600 dark:text-gray-300 line-clamp-2">
                    {{ conflict.description }}
                  </p>
                </div>

                <!-- Stats -->
                <div class="flex flex-col gap-3 md:w-48 md:flex-shrink-0">
                  <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 text-center">
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ conflict.events_count.toLocaleString() }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Tracked Events</p>
                  </div>
                  <div v-if="conflict.estimated_casualties" class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 text-center">
                    <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ formatCasualties(conflict.estimated_casualties) }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Est. Casualties</p>
                  </div>
                </div>
              </div>

              <!-- Involved Actors -->
              <div v-if="conflict.actors && conflict.actors.length > 0" class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Involved Actors:</p>
                <div class="flex flex-wrap gap-2">
                  <span
                    v-for="actor in conflict.actors"
                    :key="actor"
                    class="px-3 py-1 text-sm bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-full"
                  >
                    {{ actor }}
                  </span>
                </div>
              </div>

              <!-- Actions -->
              <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <div class="flex items-center space-x-4">
                  <router-link
                    :to="{ name: 'explore-conflict-detail', params: { id: conflict.id } }"
                    class="text-sm text-blue-600 dark:text-blue-400 hover:underline flex items-center font-medium"
                  >
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    View Details
                  </router-link>
                  <router-link
                    :to="{ name: 'explore-events', query: { conflict: conflict.id } }"
                    class="text-sm text-blue-600 dark:text-blue-400 hover:underline flex items-center"
                  >
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    View Events
                  </router-link>
                  <router-link
                    :to="{ name: 'explore-map', query: { conflict: conflict.id } }"
                    class="text-sm text-blue-600 dark:text-blue-400 hover:underline flex items-center"
                  >
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                    </svg>
                    View on Map
                  </router-link>
                </div>
                <span v-if="conflict.primary_country" class="text-sm text-gray-500 dark:text-gray-400">
                  Primary: {{ conflict.primary_country }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="!loading && !error && filteredConflicts.length === 0" class="text-center py-16">
          <svg class="w-24 h-24 mx-auto text-gray-400 dark:text-gray-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">No Conflicts Found</h3>
          <p class="text-gray-600 dark:text-gray-400">Try adjusting your filters or search terms.</p>
        </div>

        <!-- Pagination -->
        <div v-if="!loading && !error && filteredConflicts.length > 0" class="mt-8 flex flex-col md:flex-row items-center justify-between">
          <p class="text-sm text-gray-500 dark:text-gray-400 mb-4 md:mb-0">
            Showing {{ (currentPage - 1) * perPage + 1 }} to {{ Math.min(currentPage * perPage, totalItems || filteredConflicts.length) }} of {{ totalItems || filteredConflicts.length }} conflicts
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
        <p class="text-gray-400">Track global conflicts. <router-link to="/register" class="text-blue-400 hover:underline">Create an account</router-link> for detailed analysis and alerts.</p>
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
const perPage = ref(10);
const loading = ref(false);
const error = ref<string | null>(null);
const totalItems = ref(0);

const stats = ref({
  active: 0,
  highIntensity: 0,
  regions: 0,
  totalEvents: 0
});

const filters = reactive({
  search: '',
  type: '',
  intensity: ''
});

interface Conflict {
  id: number;
  slug?: string;
  name: string;
  conflict_type: string;
  intensity_level: 'high' | 'medium' | 'low' | 'frozen';
  region: string;
  primary_country: string;
  start_date: string;
  end_date: string | null;
  is_active: boolean;
  description: string;
  events_count: number;
  estimated_casualties: number | null;
  actors: string[];
}

interface ConflictsResponse {
  data: Conflict[];
  meta?: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
  stats?: {
    active: number;
    high_intensity: number;
    regions: number;
    total_events: number;
  };
}

const conflicts = ref<Conflict[]>([]);

// Debounce timer for search
let searchTimeout: ReturnType<typeof setTimeout> | null = null;

async function fetchConflicts() {
  loading.value = true;
  error.value = null;

  try {
    const params: Record<string, string | number> = {
      page: currentPage.value,
      per_page: perPage.value
    };

    if (filters.search) params.search = filters.search;
    if (filters.type) params.conflict_type = filters.type;
    if (filters.intensity) params.intensity_level = filters.intensity;

    // Use public endpoint - no authentication required
    const response = await api.get<ConflictsResponse>('/public/conflicts', { params });

    conflicts.value = response.data || [];

    if (response.meta) {
      totalItems.value = response.meta.total;
    }

    if (response.stats) {
      stats.value = {
        active: response.stats.active,
        highIntensity: response.stats.high_intensity,
        regions: response.stats.regions,
        totalEvents: response.stats.total_events
      };
    }
  } catch (err: any) {
    console.error('Failed to fetch conflicts:', err);
    error.value = err.message || 'Failed to load conflicts. Please try again.';
  } finally {
    loading.value = false;
  }
}

// Watch for filter changes with debounce
watch([() => filters.search, () => filters.type, () => filters.intensity], () => {
  if (searchTimeout) clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    currentPage.value = 1;
    fetchConflicts();
  }, 300);
});

// Watch for page changes
watch(currentPage, () => {
  fetchConflicts();
});

onMounted(() => {
  fetchConflicts();
});

const filteredConflicts = computed(() => {
  // When using API, filtering is done server-side
  return conflicts.value;
});

const totalPages = computed(() => {
  if (totalItems.value > 0) {
    return Math.ceil(totalItems.value / perPage.value);
  }
  return Math.ceil(filteredConflicts.value.length / perPage.value) || 1;
});

const getIntensityBadgeClass = (level: string) => {
  const classes: Record<string, string> = {
    'high': 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400',
    'medium': 'bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400',
    'low': 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400',
    'frozen': 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400'
  };
  return classes[level] || 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-400';
};

const getTypeBadgeClass = (type: string) => {
  const classes: Record<string, string> = {
    'CIVIL_WAR': 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400',
    'INTERSTATE': 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400',
    'INSURGENCY': 'bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400',
    'TERRORISM': 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400',
    'ETHNIC_CONFLICT': 'bg-pink-100 dark:bg-pink-900/30 text-pink-700 dark:text-pink-400',
    'BORDER_DISPUTE': 'bg-teal-100 dark:bg-teal-900/30 text-teal-700 dark:text-teal-400',
    'PROXY_WAR': 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400'
  };
  return classes[type] || 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-400';
};

const formatConflictType = (type: string) => {
  return type.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase()).join(' ');
};

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};

const formatCasualties = (num: number) => {
  if (num >= 1000000) {
    return (num / 1000000).toFixed(1) + 'M';
  } else if (num >= 1000) {
    return (num / 1000).toFixed(0) + 'K';
  }
  return num.toString();
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
