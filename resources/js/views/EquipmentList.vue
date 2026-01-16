<template>
  <div class="p-6 bg-gray-50 dark:bg-gray-900 min-h-screen">
    <div class="max-w-7xl mx-auto">
      <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Equipment Database</h1>
        <div class="flex items-center space-x-3">
          <button
            @click="viewMode = 'grid'"
            :class="['p-2 rounded-lg', viewMode === 'grid' ? 'bg-blue-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400']"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
            </svg>
          </button>
          <button
            @click="viewMode = 'list'"
            :class="['p-2 rounded-lg', viewMode === 'list' ? 'bg-blue-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400']"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
            </svg>
          </button>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
          <p class="text-sm text-gray-500 dark:text-gray-400">Total Equipment</p>
          <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.total.toLocaleString() }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
          <p class="text-sm text-gray-500 dark:text-gray-400">Destroyed</p>
          <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ stats.destroyed.toLocaleString() }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
          <p class="text-sm text-gray-500 dark:text-gray-400">Captured</p>
          <p class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ stats.captured.toLocaleString() }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
          <p class="text-sm text-gray-500 dark:text-gray-400">Categories</p>
          <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ stats.categories }}</p>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
          <div class="md:col-span-2">
            <input
              v-model="filters.search"
              type="text"
              placeholder="Search equipment..."
              class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
            />
          </div>
          <select
            v-model="filters.category"
            class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
          >
            <option value="">All Categories</option>
            <option value="tanks">Tanks</option>
            <option value="afv">Armored Fighting Vehicles</option>
            <option value="ifv">Infantry Fighting Vehicles</option>
            <option value="apc">Armored Personnel Carriers</option>
            <option value="artillery">Artillery</option>
            <option value="mlrs">MLRS</option>
            <option value="aircraft">Aircraft</option>
            <option value="helicopter">Helicopters</option>
            <option value="uav">UAVs</option>
            <option value="naval">Naval</option>
          </select>
          <select
            v-model="filters.country"
            class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
          >
            <option value="">All Countries</option>
            <option value="russia">Russia</option>
            <option value="ukraine">Ukraine</option>
            <option value="usa">United States</option>
            <option value="germany">Germany</option>
            <option value="china">China</option>
          </select>
          <select
            v-model="filters.status"
            class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
          >
            <option value="">All Status</option>
            <option value="destroyed">Destroyed</option>
            <option value="captured">Captured</option>
            <option value="abandoned">Abandoned</option>
            <option value="damaged">Damaged</option>
          </select>
        </div>
      </div>

      <!-- Grid View -->
      <div v-if="viewMode === 'grid'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <div
          v-for="equipment in filteredEquipment"
          :key="equipment.id"
          class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden hover:shadow-lg transition-shadow cursor-pointer"
          @click="viewEquipment(equipment)"
        >
          <div class="h-40 bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
            <img v-if="equipment.image_url" :src="equipment.image_url" :alt="equipment.name" class="w-full h-full object-cover" />
            <svg v-else class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
            </svg>
          </div>
          <div class="p-4">
            <div class="flex items-center justify-between mb-2">
              <span :class="getCategoryBadgeClass(equipment.category)" class="px-2 py-1 text-xs font-medium rounded-full">
                {{ equipment.category }}
              </span>
              <span class="text-xs text-gray-500 dark:text-gray-400">{{ equipment.origin_country }}</span>
            </div>
            <h3 class="font-semibold text-gray-900 dark:text-white mb-1">{{ equipment.name }}</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 line-clamp-2">{{ equipment.description }}</p>
            <div class="mt-3 flex items-center justify-between">
              <div class="flex items-center space-x-2">
                <span class="text-red-600 dark:text-red-400 text-sm font-medium">{{ equipment.destroyed }}</span>
                <span class="text-orange-600 dark:text-orange-400 text-sm font-medium">{{ equipment.captured }}</span>
              </div>
              <span class="text-xs text-gray-400">{{ equipment.total_losses }} total</span>
            </div>
          </div>
        </div>
      </div>

      <!-- List View -->
      <div v-else class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <table class="w-full">
          <thead class="bg-gray-50 dark:bg-gray-700">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Equipment</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Category</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Origin</th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Destroyed</th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Captured</th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Abandoned</th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Total</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            <tr
              v-for="equipment in filteredEquipment"
              :key="equipment.id"
              class="hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer"
              @click="viewEquipment(equipment)"
            >
              <td class="px-6 py-4">
                <div class="flex items-center space-x-3">
                  <div class="w-12 h-12 bg-gray-200 dark:bg-gray-700 rounded flex items-center justify-center flex-shrink-0">
                    <img v-if="equipment.image_url" :src="equipment.image_url" :alt="equipment.name" class="w-full h-full object-cover rounded" />
                    <svg v-else class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                  </div>
                  <div>
                    <p class="font-medium text-gray-900 dark:text-white">{{ equipment.name }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ equipment.manufacturer }}</p>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4">
                <span :class="getCategoryBadgeClass(equipment.category)" class="px-2 py-1 text-xs font-medium rounded-full">
                  {{ equipment.category }}
                </span>
              </td>
              <td class="px-6 py-4 text-gray-600 dark:text-gray-400">{{ equipment.origin_country }}</td>
              <td class="px-6 py-4 text-center text-red-600 dark:text-red-400 font-medium">{{ equipment.destroyed }}</td>
              <td class="px-6 py-4 text-center text-orange-600 dark:text-orange-400 font-medium">{{ equipment.captured }}</td>
              <td class="px-6 py-4 text-center text-yellow-600 dark:text-yellow-400 font-medium">{{ equipment.abandoned }}</td>
              <td class="px-6 py-4 text-center font-bold text-gray-900 dark:text-white">{{ equipment.total_losses }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="mt-6 flex items-center justify-between">
        <p class="text-sm text-gray-500 dark:text-gray-400">
          Showing {{ (currentPage - 1) * perPage + 1 }} to {{ Math.min(currentPage * perPage, filteredEquipment.length) }} of {{ filteredEquipment.length }} results
        </p>
        <div class="flex items-center space-x-2">
          <button
            @click="currentPage--"
            :disabled="currentPage === 1"
            class="px-4 py-2 rounded-lg bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 disabled:opacity-50"
          >
            Previous
          </button>
          <span class="px-4 py-2 text-gray-700 dark:text-gray-300">{{ currentPage }} / {{ totalPages }}</span>
          <button
            @click="currentPage++"
            :disabled="currentPage === totalPages"
            class="px-4 py-2 rounded-lg bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 disabled:opacity-50"
          >
            Next
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();
const viewMode = ref<'grid' | 'list'>('grid');
const currentPage = ref(1);
const perPage = ref(12);

const stats = ref({
  total: 8943,
  destroyed: 5621,
  captured: 2134,
  categories: 42
});

const filters = reactive({
  search: '',
  category: '',
  country: '',
  status: ''
});

interface Equipment {
  id: number;
  name: string;
  category: string;
  manufacturer: string;
  origin_country: string;
  description: string;
  image_url?: string;
  destroyed: number;
  captured: number;
  abandoned: number;
  damaged: number;
  total_losses: number;
}

const equipment = ref<Equipment[]>([
  { id: 1, name: 'T-72B3', category: 'Tanks', manufacturer: 'Uralvagonzavod', origin_country: 'Russia', description: 'Main battle tank used extensively in the conflict', destroyed: 892, captured: 234, abandoned: 156, damaged: 78, total_losses: 1360 },
  { id: 2, name: 'T-80BVM', category: 'Tanks', manufacturer: 'Omsktransmash', origin_country: 'Russia', description: 'Advanced main battle tank with gas turbine engine', destroyed: 234, captured: 89, abandoned: 45, damaged: 23, total_losses: 391 },
  { id: 3, name: 'BMP-2', category: 'IFV', manufacturer: 'Kurganmashzavod', origin_country: 'Russia', description: 'Infantry fighting vehicle with 30mm autocannon', destroyed: 567, captured: 321, abandoned: 189, damaged: 45, total_losses: 1122 },
  { id: 4, name: 'BTR-82A', category: 'APC', manufacturer: 'Arzamas', origin_country: 'Russia', description: 'Wheeled armored personnel carrier', destroyed: 423, captured: 178, abandoned: 234, damaged: 56, total_losses: 891 },
  { id: 5, name: 'Leopard 2A6', category: 'Tanks', manufacturer: 'Krauss-Maffei', origin_country: 'Germany', description: 'Western main battle tank provided to Ukraine', destroyed: 23, captured: 2, abandoned: 5, damaged: 8, total_losses: 38 },
  { id: 6, name: 'M777 Howitzer', category: 'Artillery', manufacturer: 'BAE Systems', origin_country: 'USA', description: '155mm towed howitzer', destroyed: 45, captured: 3, abandoned: 12, damaged: 8, total_losses: 68 },
  { id: 7, name: 'HIMARS', category: 'MLRS', manufacturer: 'Lockheed Martin', origin_country: 'USA', description: 'High Mobility Artillery Rocket System', destroyed: 2, captured: 0, abandoned: 0, damaged: 1, total_losses: 3 },
  { id: 8, name: 'Su-35', category: 'Aircraft', manufacturer: 'Sukhoi', origin_country: 'Russia', description: 'Multi-role air superiority fighter', destroyed: 24, captured: 0, abandoned: 0, damaged: 3, total_losses: 27 },
  { id: 9, name: 'Ka-52', category: 'Helicopter', manufacturer: 'Kamov', origin_country: 'Russia', description: 'Attack helicopter with ejection seats', destroyed: 31, captured: 0, abandoned: 1, damaged: 5, total_losses: 37 },
  { id: 10, name: 'Orlan-10', category: 'UAV', manufacturer: 'Special Technology Center', origin_country: 'Russia', description: 'Reconnaissance UAV', destroyed: 234, captured: 89, abandoned: 34, damaged: 12, total_losses: 369 },
  { id: 11, name: 'T-64BV', category: 'Tanks', manufacturer: 'Kharkiv Morozov', origin_country: 'Ukraine', description: 'Ukrainian main battle tank', destroyed: 156, captured: 45, abandoned: 23, damaged: 34, total_losses: 258 },
  { id: 12, name: 'Bradley M2A2', category: 'IFV', manufacturer: 'BAE Systems', origin_country: 'USA', description: 'Infantry fighting vehicle provided to Ukraine', destroyed: 34, captured: 5, abandoned: 8, damaged: 12, total_losses: 59 }
]);

const filteredEquipment = computed(() => {
  return equipment.value.filter(e => {
    const matchesSearch = !filters.search ||
      e.name.toLowerCase().includes(filters.search.toLowerCase()) ||
      e.manufacturer.toLowerCase().includes(filters.search.toLowerCase());
    const matchesCategory = !filters.category || e.category.toLowerCase() === filters.category;
    const matchesCountry = !filters.country || e.origin_country.toLowerCase().includes(filters.country);
    return matchesSearch && matchesCategory && matchesCountry;
  });
});

const totalPages = computed(() => Math.ceil(filteredEquipment.value.length / perPage.value) || 1);

const getCategoryBadgeClass = (category: string) => {
  const classes: Record<string, string> = {
    'Tanks': 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400',
    'IFV': 'bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400',
    'APC': 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400',
    'Artillery': 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400',
    'MLRS': 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400',
    'Aircraft': 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400',
    'Helicopter': 'bg-pink-100 dark:bg-pink-900/30 text-pink-700 dark:text-pink-400',
    'UAV': 'bg-cyan-100 dark:bg-cyan-900/30 text-cyan-700 dark:text-cyan-400'
  };
  return classes[category] || 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-400';
};

const viewEquipment = (item: Equipment) => {
  router.push({ name: 'equipment-detail', params: { id: item.id } });
};
</script>
