<template>
  <div class="p-6 bg-gray-50 dark:bg-gray-900 min-h-screen">
    <div class="max-w-7xl mx-auto">
      <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Control Zones</h1>
        <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center space-x-2">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          <span>Add Zone</span>
        </button>
      </div>

      <!-- Stats -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
          <p class="text-sm text-gray-500 dark:text-gray-400">Total Zones</p>
          <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.total }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
          <p class="text-sm text-gray-500 dark:text-gray-400">Full Control</p>
          <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ stats.fullControl }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
          <p class="text-sm text-gray-500 dark:text-gray-400">Contested</p>
          <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ stats.contested }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
          <p class="text-sm text-gray-500 dark:text-gray-400">Total Area</p>
          <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ stats.totalArea.toLocaleString() }} km²</p>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <input
            v-model="filters.search"
            type="text"
            placeholder="Search zones..."
            class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
          />
          <select
            v-model="filters.controlType"
            class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
          >
            <option value="">All Control Types</option>
            <option value="full">Full Control</option>
            <option value="contested">Contested</option>
            <option value="claimed">Claimed</option>
          </select>
          <select
            v-model="filters.controller"
            class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
          >
            <option value="">All Controllers</option>
            <option value="ukraine">Ukraine</option>
            <option value="russia">Russia</option>
            <option value="separatist">Separatist Forces</option>
          </select>
          <select
            v-model="filters.confidence"
            class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
          >
            <option value="">All Confidence</option>
            <option value="confirmed">Confirmed</option>
            <option value="likely">Likely</option>
            <option value="unconfirmed">Unconfirmed</option>
          </select>
        </div>
      </div>

      <!-- Zones List -->
      <div class="space-y-4">
        <div
          v-for="zone in filteredZones"
          :key="zone.id"
          class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden"
        >
          <div class="p-6">
            <div class="flex items-start justify-between">
              <div class="flex items-start space-x-4">
                <div
                  class="w-12 h-12 rounded-lg flex items-center justify-center"
                  :style="{ backgroundColor: zone.controller_color + '20' }"
                >
                  <div
                    class="w-6 h-6 rounded"
                    :style="{ backgroundColor: zone.controller_color }"
                  ></div>
                </div>
                <div>
                  <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ zone.name }}</h3>
                  <p class="text-sm text-gray-500 dark:text-gray-400">Controlled by: {{ zone.controller_name }}</p>
                </div>
              </div>
              <div class="flex items-center space-x-2">
                <span :class="getControlTypeBadge(zone.control_type)" class="px-3 py-1 text-xs font-medium rounded-full">
                  {{ zone.control_type }}
                </span>
                <span :class="getConfidenceBadge(zone.confidence)" class="px-3 py-1 text-xs font-medium rounded-full">
                  {{ zone.confidence }}
                </span>
              </div>
            </div>

            <div class="mt-4 grid grid-cols-2 md:grid-cols-5 gap-4">
              <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">Area</p>
                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ zone.area_km2?.toLocaleString() }} km²</p>
              </div>
              <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">Valid From</p>
                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ formatDate(zone.valid_from) }}</p>
              </div>
              <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">Valid To</p>
                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ zone.valid_to ? formatDate(zone.valid_to) : 'Present' }}</p>
              </div>
              <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">Last Updated</p>
                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ formatDate(zone.updated_at) }}</p>
              </div>
              <div class="flex items-end justify-end space-x-2">
                <button class="p-2 text-gray-400 hover:text-blue-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                  </svg>
                </button>
                <button class="p-2 text-gray-400 hover:text-green-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                </button>
                <button class="p-2 text-gray-400 hover:text-red-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                </button>
              </div>
            </div>

            <div v-if="zone.notes" class="mt-4 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
              <p class="text-sm text-gray-600 dark:text-gray-400">{{ zone.notes }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div class="mt-6 flex items-center justify-between">
        <p class="text-sm text-gray-500 dark:text-gray-400">
          Showing {{ filteredZones.length }} zones
        </p>
        <div class="flex items-center space-x-2">
          <button class="px-4 py-2 rounded-lg bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 disabled:opacity-50">
            Previous
          </button>
          <span class="px-4 py-2 text-gray-700 dark:text-gray-300">1 / 1</span>
          <button class="px-4 py-2 rounded-lg bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 disabled:opacity-50">
            Next
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed } from 'vue';

const stats = ref({
  total: 156,
  fullControl: 98,
  contested: 42,
  totalArea: 125430
});

const filters = reactive({
  search: '',
  controlType: '',
  controller: '',
  confidence: ''
});

interface Zone {
  id: number;
  name: string;
  controller_name: string;
  controller_color: string;
  control_type: 'full' | 'contested' | 'claimed';
  confidence: 'confirmed' | 'likely' | 'unconfirmed';
  area_km2: number;
  valid_from: string;
  valid_to?: string;
  notes?: string;
  updated_at: string;
}

const zones = ref<Zone[]>([
  { id: 1, name: 'Kherson Oblast - Northern Region', controller_name: 'Ukrainian Armed Forces', controller_color: '#FFD700', control_type: 'full', confidence: 'confirmed', area_km2: 8450, valid_from: '2022-11-11', notes: 'Liberated during the Kherson counteroffensive', updated_at: '2024-01-15' },
  { id: 2, name: 'Zaporizhzhia Oblast - Southern Front', controller_name: 'Russian Armed Forces', controller_color: '#1E40AF', control_type: 'contested', confidence: 'likely', area_km2: 12340, valid_from: '2022-03-01', notes: 'Active combat operations ongoing', updated_at: '2024-01-14' },
  { id: 3, name: 'Donetsk Oblast - Bakhmut Sector', controller_name: 'Russian Armed Forces', controller_color: '#1E40AF', control_type: 'contested', confidence: 'confirmed', area_km2: 2150, valid_from: '2023-05-20', updated_at: '2024-01-13' },
  { id: 4, name: 'Luhansk Oblast - Western Border', controller_name: 'Ukrainian Armed Forces', controller_color: '#FFD700', control_type: 'full', confidence: 'confirmed', area_km2: 4560, valid_from: '2022-09-10', updated_at: '2024-01-12' },
  { id: 5, name: 'Crimea - Annexed Territory', controller_name: 'Russian Armed Forces', controller_color: '#1E40AF', control_type: 'claimed', confidence: 'confirmed', area_km2: 27000, valid_from: '2014-03-18', notes: 'Internationally recognized as Ukrainian territory', updated_at: '2024-01-10' }
]);

const filteredZones = computed(() => {
  return zones.value.filter(zone => {
    const matchesSearch = !filters.search || zone.name.toLowerCase().includes(filters.search.toLowerCase());
    const matchesType = !filters.controlType || zone.control_type === filters.controlType;
    const matchesConfidence = !filters.confidence || zone.confidence === filters.confidence;
    return matchesSearch && matchesType && matchesConfidence;
  });
});

const getControlTypeBadge = (type: string) => {
  const classes: Record<string, string> = {
    'full': 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400',
    'contested': 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400',
    'claimed': 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400'
  };
  return classes[type] || 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-400';
};

const getConfidenceBadge = (confidence: string) => {
  const classes: Record<string, string> = {
    'confirmed': 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400',
    'likely': 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400',
    'unconfirmed': 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400'
  };
  return classes[confidence] || 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-400';
};

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
};
</script>
