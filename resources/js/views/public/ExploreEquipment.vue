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
            <router-link to="/explore/equipment" class="text-blue-600 dark:text-blue-400 font-medium">Equipment</router-link>
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
        <div class="mb-8">
          <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Equipment Database</h1>
          <p class="mt-2 text-gray-600 dark:text-gray-400">Browse military equipment tracked in our OSINT database</p>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border border-gray-200 dark:border-gray-700">
            <p class="text-sm text-gray-500 dark:text-gray-400">Equipment Types</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.types.toLocaleString() }}</p>
          </div>
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border border-gray-200 dark:border-gray-700">
            <p class="text-sm text-gray-500 dark:text-gray-400">Total Losses</p>
            <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ stats.losses.toLocaleString() }}</p>
          </div>
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border border-gray-200 dark:border-gray-700">
            <p class="text-sm text-gray-500 dark:text-gray-400">Countries</p>
            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ stats.countries }}</p>
          </div>
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border border-gray-200 dark:border-gray-700">
            <p class="text-sm text-gray-500 dark:text-gray-400">Categories</p>
            <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ stats.categories }}</p>
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
                  placeholder="Search equipment..."
                  class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
              </div>
            </div>
            <select
              v-model="filters.category"
              class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="">All Categories</option>
              <option value="tanks">Tanks</option>
              <option value="afv">Armored Vehicles</option>
              <option value="ifv">Infantry Fighting Vehicles</option>
              <option value="apc">Armored Personnel Carriers</option>
              <option value="artillery">Artillery</option>
              <option value="mlrs">MLRS</option>
              <option value="aircraft">Aircraft</option>
              <option value="helicopter">Helicopters</option>
              <option value="uav">UAVs</option>
              <option value="naval">Naval</option>
              <option value="air_defense">Air Defense</option>
            </select>
            <select
              v-model="filters.country"
              class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="">All Countries</option>
              <option value="russia">Russia</option>
              <option value="ukraine">Ukraine</option>
              <option value="usa">United States</option>
              <option value="germany">Germany</option>
              <option value="uk">United Kingdom</option>
              <option value="france">France</option>
              <option value="china">China</option>
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
            <p class="text-gray-600 dark:text-gray-400">Loading equipment...</p>
          </div>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="text-center py-16">
          <svg class="w-24 h-24 mx-auto text-red-400 dark:text-red-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
          <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Unable to Load Equipment</h3>
          <p class="text-gray-600 dark:text-gray-400 mb-6">{{ error }}</p>
          <div class="flex items-center justify-center space-x-4">
            <button @click="fetchEquipment" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
              Try Again
            </button>
            <router-link to="/register" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-lg hover:from-blue-700 hover:to-indigo-700 transition-colors">
              Sign Up for Access
            </router-link>
          </div>
        </div>

        <!-- Equipment Grid -->
        <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
          <router-link
            v-for="item in filteredEquipment"
            :key="item.id"
            :to="{ name: 'explore-equipment-detail', params: { id: item.id } }"
            class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden hover:shadow-lg transition-all border border-gray-200 dark:border-gray-700 group"
          >
            <!-- Image -->
            <div class="h-40 bg-gray-200 dark:bg-gray-700 flex items-center justify-center overflow-hidden">
              <img
                v-if="item.image_url"
                :src="item.image_url"
                :alt="item.name"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
              />
              <svg v-else class="w-16 h-16 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
              </svg>
            </div>

            <!-- Content -->
            <div class="p-4">
              <div class="flex items-center justify-between mb-2">
                <span :class="getCategoryBadgeClass(item.category)" class="px-2 py-1 text-xs font-medium rounded-full">
                  {{ item.category }}
                </span>
                <span class="text-xs text-gray-500 dark:text-gray-400">{{ item.origin_country }}</span>
              </div>
              <h3 class="font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                {{ item.name }}
              </h3>
              <p v-if="item.manufacturer" class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ item.manufacturer }}</p>

              <!-- Loss Stats -->
              <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between text-sm">
                  <div class="flex items-center space-x-3">
                    <span class="text-red-600 dark:text-red-400" title="Destroyed">
                      <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z" />
                      </svg>
                      {{ item.destroyed }}
                    </span>
                    <span class="text-orange-600 dark:text-orange-400" title="Captured">
                      <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                      </svg>
                      {{ item.captured }}
                    </span>
                  </div>
                  <span class="font-medium text-gray-900 dark:text-white">{{ item.total_losses }} total</span>
                </div>
              </div>
            </div>
          </router-link>
        </div>

        <!-- Empty State -->
        <div v-if="!loading && !error && filteredEquipment.length === 0" class="text-center py-16">
          <svg class="w-24 h-24 mx-auto text-gray-400 dark:text-gray-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">No Equipment Found</h3>
          <p class="text-gray-600 dark:text-gray-400">Try adjusting your filters or search terms.</p>
        </div>

        <!-- Pagination -->
        <div v-if="!loading && !error && filteredEquipment.length > 0" class="mt-8 flex flex-col md:flex-row items-center justify-between">
          <p class="text-sm text-gray-500 dark:text-gray-400 mb-4 md:mb-0">
            Showing {{ (currentPage - 1) * perPage + 1 }} to {{ Math.min(currentPage * perPage, totalItems || filteredEquipment.length) }} of {{ totalItems || filteredEquipment.length }} equipment types
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
        <p class="text-gray-400">Explore military equipment data. <router-link to="/register" class="text-blue-400 hover:underline">Create an account</router-link> for detailed analytics.</p>
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
const perPage = ref(16);
const loading = ref(false);
const error = ref<string | null>(null);
const totalItems = ref(0);

const stats = ref({
  types: 0,
  losses: 0,
  countries: 0,
  categories: 0
});

const filters = reactive({
  search: '',
  category: '',
  country: ''
});

interface Equipment {
  id: number;
  name: string;
  category: string;
  manufacturer: string;
  origin_country: string;
  image_url: string | null;
  destroyed: number;
  captured: number;
  total_losses: number;
}

interface EquipmentResponse {
  data: Equipment[];
  meta?: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
}

interface StatsResponse {
  data: {
    total_types: number;
    total_losses: number;
    countries: number;
    categories: number;
  };
}

const equipment = ref<Equipment[]>([]);

// Debounce timer for search
let searchTimeout: ReturnType<typeof setTimeout> | null = null;

async function fetchEquipment() {
  loading.value = true;
  error.value = null;

  try {
    const params: Record<string, string | number> = {
      page: currentPage.value,
      per_page: perPage.value
    };

    if (filters.search) params.search = filters.search;
    if (filters.category) params.category = filters.category;
    if (filters.country) params.country = filters.country;

    const response = await api.get<EquipmentResponse>('/equipment', { params });

    equipment.value = response.data || [];

    if (response.meta) {
      totalItems.value = response.meta.total;
    }
  } catch (err: any) {
    console.error('Failed to fetch equipment:', err);
    if (err.message?.includes('401') || err.message?.includes('Unauthorized')) {
      error.value = 'Please sign in to view equipment data.';
    } else {
      error.value = err.message || 'Failed to load equipment. Please try again.';
    }
  } finally {
    loading.value = false;
  }
}

async function fetchStats() {
  try {
    const response = await api.get<StatsResponse>('/equipment/stats');
    if (response.data) {
      stats.value = {
        types: response.data.total_types,
        losses: response.data.total_losses,
        countries: response.data.countries,
        categories: response.data.categories
      };
    }
  } catch (err) {
    // Stats are optional, don't show error
    console.warn('Failed to fetch equipment stats:', err);
  }
}

// Watch for filter changes with debounce
watch([() => filters.search, () => filters.category, () => filters.country], () => {
  if (searchTimeout) clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    currentPage.value = 1;
    fetchEquipment();
  }, 300);
});

// Watch for page changes
watch(currentPage, () => {
  fetchEquipment();
});

onMounted(() => {
  fetchEquipment();
  fetchStats();
});

const filteredEquipment = computed(() => {
  // When using API, filtering is done server-side
  return equipment.value;
});

const totalPages = computed(() => {
  if (totalItems.value > 0) {
    return Math.ceil(totalItems.value / perPage.value);
  }
  return Math.ceil(filteredEquipment.value.length / perPage.value) || 1;
});

const getCategoryBadgeClass = (category: string) => {
  const classes: Record<string, string> = {
    'Tanks': 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400',
    'tanks': 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400',
    'IFV': 'bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400',
    'ifv': 'bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400',
    'APC': 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400',
    'apc': 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400',
    'Artillery': 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400',
    'artillery': 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400',
    'MLRS': 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400',
    'mlrs': 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400',
    'Aircraft': 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400',
    'aircraft': 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400',
    'Helicopter': 'bg-pink-100 dark:bg-pink-900/30 text-pink-700 dark:text-pink-400',
    'helicopter': 'bg-pink-100 dark:bg-pink-900/30 text-pink-700 dark:text-pink-400',
    'UAV': 'bg-cyan-100 dark:bg-cyan-900/30 text-cyan-700 dark:text-cyan-400',
    'uav': 'bg-cyan-100 dark:bg-cyan-900/30 text-cyan-700 dark:text-cyan-400',
    'Air Defense': 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400',
    'air_defense': 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400'
  };
  return classes[category] || 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-400';
};
</script>
