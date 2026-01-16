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

        <!-- Equipment Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
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
        <div v-if="filteredEquipment.length === 0" class="text-center py-16">
          <svg class="w-24 h-24 mx-auto text-gray-400 dark:text-gray-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">No Equipment Found</h3>
          <p class="text-gray-600 dark:text-gray-400">Try adjusting your filters or search terms.</p>
        </div>

        <!-- Pagination -->
        <div v-if="filteredEquipment.length > 0" class="mt-8 flex flex-col md:flex-row items-center justify-between">
          <p class="text-sm text-gray-500 dark:text-gray-400 mb-4 md:mb-0">
            Showing {{ filteredEquipment.length }} of {{ equipment.length }} equipment types
          </p>
          <div class="flex items-center space-x-2">
            <button
              :disabled="currentPage === 1"
              class="px-4 py-2 rounded-lg bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
              Previous
            </button>
            <span class="px-4 py-2 text-gray-700 dark:text-gray-300">Page {{ currentPage }}</span>
            <button
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
import { ref, reactive, computed } from 'vue';
import { useThemeStore } from '@/stores/theme';

const themeStore = useThemeStore();
const isDark = computed(() => themeStore.isDark);

const toggleTheme = () => {
  themeStore.toggleTheme();
};

const currentPage = ref(1);

const stats = ref({
  types: 387,
  losses: 12543,
  countries: 18,
  categories: 12
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

const equipment = ref<Equipment[]>([
  { id: 1, name: 'T-72B3', category: 'Tanks', manufacturer: 'Uralvagonzavod', origin_country: 'Russia', image_url: null, destroyed: 892, captured: 234, total_losses: 1360 },
  { id: 2, name: 'T-80BVM', category: 'Tanks', manufacturer: 'Omsktransmash', origin_country: 'Russia', image_url: null, destroyed: 234, captured: 89, total_losses: 391 },
  { id: 3, name: 'T-90M', category: 'Tanks', manufacturer: 'Uralvagonzavod', origin_country: 'Russia', image_url: null, destroyed: 45, captured: 12, total_losses: 67 },
  { id: 4, name: 'BMP-2', category: 'IFV', manufacturer: 'Kurganmashzavod', origin_country: 'Russia', image_url: null, destroyed: 567, captured: 321, total_losses: 1122 },
  { id: 5, name: 'BTR-82A', category: 'APC', manufacturer: 'Arzamas', origin_country: 'Russia', image_url: null, destroyed: 423, captured: 178, total_losses: 891 },
  { id: 6, name: 'Leopard 2A6', category: 'Tanks', manufacturer: 'Krauss-Maffei', origin_country: 'Germany', image_url: null, destroyed: 23, captured: 2, total_losses: 38 },
  { id: 7, name: 'M1A1 Abrams', category: 'Tanks', manufacturer: 'General Dynamics', origin_country: 'USA', image_url: null, destroyed: 8, captured: 0, total_losses: 12 },
  { id: 8, name: 'M777 Howitzer', category: 'Artillery', manufacturer: 'BAE Systems', origin_country: 'USA', image_url: null, destroyed: 45, captured: 3, total_losses: 68 },
  { id: 9, name: 'HIMARS', category: 'MLRS', manufacturer: 'Lockheed Martin', origin_country: 'USA', image_url: null, destroyed: 2, captured: 0, total_losses: 3 },
  { id: 10, name: 'Su-35', category: 'Aircraft', manufacturer: 'Sukhoi', origin_country: 'Russia', image_url: null, destroyed: 24, captured: 0, total_losses: 27 },
  { id: 11, name: 'Ka-52', category: 'Helicopter', manufacturer: 'Kamov', origin_country: 'Russia', image_url: null, destroyed: 31, captured: 0, total_losses: 37 },
  { id: 12, name: 'Orlan-10', category: 'UAV', manufacturer: 'Special Technology Center', origin_country: 'Russia', image_url: null, destroyed: 234, captured: 89, total_losses: 369 },
  { id: 13, name: 'T-64BV', category: 'Tanks', manufacturer: 'Kharkiv Morozov', origin_country: 'Ukraine', image_url: null, destroyed: 156, captured: 45, total_losses: 258 },
  { id: 14, name: 'Bradley M2A2', category: 'IFV', manufacturer: 'BAE Systems', origin_country: 'USA', image_url: null, destroyed: 34, captured: 5, total_losses: 59 },
  { id: 15, name: 'S-300', category: 'Air Defense', manufacturer: 'Almaz-Antey', origin_country: 'Russia', image_url: null, destroyed: 18, captured: 2, total_losses: 23 },
  { id: 16, name: 'Pantsir-S1', category: 'Air Defense', manufacturer: 'KBP', origin_country: 'Russia', image_url: null, destroyed: 34, captured: 8, total_losses: 47 }
]);

const filteredEquipment = computed(() => {
  return equipment.value.filter(item => {
    const matchesSearch = !filters.search ||
      item.name.toLowerCase().includes(filters.search.toLowerCase()) ||
      item.manufacturer.toLowerCase().includes(filters.search.toLowerCase());
    const matchesCategory = !filters.category || item.category.toLowerCase() === filters.category;
    const matchesCountry = !filters.country || item.origin_country.toLowerCase().includes(filters.country);
    return matchesSearch && matchesCategory && matchesCountry;
  });
});

const getCategoryBadgeClass = (category: string) => {
  const classes: Record<string, string> = {
    'Tanks': 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400',
    'IFV': 'bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400',
    'APC': 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400',
    'Artillery': 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400',
    'MLRS': 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400',
    'Aircraft': 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400',
    'Helicopter': 'bg-pink-100 dark:bg-pink-900/30 text-pink-700 dark:text-pink-400',
    'UAV': 'bg-cyan-100 dark:bg-cyan-900/30 text-cyan-700 dark:text-cyan-400',
    'Air Defense': 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400'
  };
  return classes[category] || 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-400';
};
</script>
