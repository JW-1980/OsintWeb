<template>
  <div class="p-6 bg-gray-50 dark:bg-gray-900 min-h-screen">
    <div class="max-w-7xl mx-auto">
      <!-- Header -->
      <div class="flex items-center justify-between mb-6">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Control Zones Management</h1>
          <p class="text-gray-500 dark:text-gray-400 mt-1">Manage territorial control zones</p>
        </div>
        <button @click="showCreateModal = true" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center space-x-2">
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

      <!-- Zones Table -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
          <thead class="bg-gray-50 dark:bg-gray-700">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Zone</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Controller</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Control Type</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Area</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Confidence</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Valid From</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
            <tr v-for="zone in filteredZones" :key="zone.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="w-10 h-10 rounded-lg flex items-center justify-center" :style="{ backgroundColor: zone.controller_color + '20' }">
                    <div class="w-5 h-5 rounded" :style="{ backgroundColor: zone.controller_color }"></div>
                  </div>
                  <div class="ml-4">
                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ zone.name }}</p>
                    <p v-if="zone.notes" class="text-xs text-gray-500 dark:text-gray-400 truncate max-w-xs">{{ zone.notes }}</p>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                {{ zone.controller_name }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getControlTypeBadge(zone.control_type)" class="px-2 py-1 text-xs font-medium rounded-full">
                  {{ zone.control_type }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                {{ zone.area_km2?.toLocaleString() }} km²
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getConfidenceBadge(zone.confidence)" class="px-2 py-1 text-xs font-medium rounded-full">
                  {{ zone.confidence }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                {{ formatDate(zone.valid_from) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <div class="flex items-center justify-end space-x-2">
                  <button @click="viewOnMap(zone)" class="p-2 text-blue-600 hover:bg-blue-100 dark:hover:bg-blue-900/30 rounded-lg" title="View on Map">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                    </svg>
                  </button>
                  <button @click="editZone(zone)" class="p-2 text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg" title="Edit">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </button>
                  <button @click="deleteZone(zone)" class="p-2 text-red-600 hover:bg-red-100 dark:hover:bg-red-900/30 rounded-lg" title="Delete">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="mt-6 flex items-center justify-between">
        <p class="text-sm text-gray-500 dark:text-gray-400">
          Showing {{ filteredZones.length }} zones
        </p>
        <div class="flex items-center space-x-2">
          <button class="px-4 py-2 rounded-lg bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300">
            Previous
          </button>
          <span class="px-4 py-2 text-gray-700 dark:text-gray-300">1 / 1</span>
          <button class="px-4 py-2 rounded-lg bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300">
            Next
          </button>
        </div>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="showCreateModal || showEditModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-black/50" @click="closeModals"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-2xl w-full p-6 max-h-[90vh] overflow-y-auto">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
            {{ showEditModal ? 'Edit Zone' : 'Create New Zone' }}
          </h3>
          <form @submit.prevent="saveZone" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Zone Name</label>
              <input v-model="formData.name" type="text" required class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Controller</label>
                <select v-model="formData.controller_name" required class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                  <option value="Ukrainian Armed Forces">Ukrainian Armed Forces</option>
                  <option value="Russian Armed Forces">Russian Armed Forces</option>
                  <option value="Separatist Forces">Separatist Forces</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Controller Color</label>
                <input v-model="formData.controller_color" type="color" class="w-full h-10 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700" />
              </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Control Type</label>
                <select v-model="formData.control_type" required class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                  <option value="full">Full Control</option>
                  <option value="contested">Contested</option>
                  <option value="claimed">Claimed</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Confidence</label>
                <select v-model="formData.confidence" required class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                  <option value="confirmed">Confirmed</option>
                  <option value="likely">Likely</option>
                  <option value="unconfirmed">Unconfirmed</option>
                </select>
              </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Area (km²)</label>
                <input v-model.number="formData.area_km2" type="number" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Valid From</label>
                <input v-model="formData.valid_from" type="date" required class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
              <textarea v-model="formData.notes" rows="3" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"></textarea>
            </div>
            <div class="flex justify-end space-x-3 pt-4">
              <button type="button" @click="closeModals" class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">Cancel</button>
              <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                {{ showEditModal ? 'Update' : 'Create' }}
              </button>
            </div>
          </form>
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

const showCreateModal = ref(false);
const showEditModal = ref(false);

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
}

const formData = reactive<Zone>({
  id: 0,
  name: '',
  controller_name: 'Ukrainian Armed Forces',
  controller_color: '#FFD700',
  control_type: 'full',
  confidence: 'confirmed',
  area_km2: 0,
  valid_from: '',
  notes: ''
});

const zones = ref<Zone[]>([
  { id: 1, name: 'Kherson Oblast - Northern Region', controller_name: 'Ukrainian Armed Forces', controller_color: '#FFD700', control_type: 'full', confidence: 'confirmed', area_km2: 8450, valid_from: '2022-11-11', notes: 'Liberated during the Kherson counteroffensive' },
  { id: 2, name: 'Zaporizhzhia Oblast - Southern Front', controller_name: 'Russian Armed Forces', controller_color: '#1E40AF', control_type: 'contested', confidence: 'likely', area_km2: 12340, valid_from: '2022-03-01', notes: 'Active combat operations ongoing' },
  { id: 3, name: 'Donetsk Oblast - Bakhmut Sector', controller_name: 'Russian Armed Forces', controller_color: '#1E40AF', control_type: 'contested', confidence: 'confirmed', area_km2: 2150, valid_from: '2023-05-20' },
  { id: 4, name: 'Luhansk Oblast - Western Border', controller_name: 'Ukrainian Armed Forces', controller_color: '#FFD700', control_type: 'full', confidence: 'confirmed', area_km2: 4560, valid_from: '2022-09-10' },
  { id: 5, name: 'Crimea - Annexed Territory', controller_name: 'Russian Armed Forces', controller_color: '#1E40AF', control_type: 'claimed', confidence: 'confirmed', area_km2: 27000, valid_from: '2014-03-18', notes: 'Internationally recognized as Ukrainian territory' },
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

const closeModals = () => {
  showCreateModal.value = false;
  showEditModal.value = false;
  resetForm();
};

const resetForm = () => {
  formData.id = 0;
  formData.name = '';
  formData.controller_name = 'Ukrainian Armed Forces';
  formData.controller_color = '#FFD700';
  formData.control_type = 'full';
  formData.confidence = 'confirmed';
  formData.area_km2 = 0;
  formData.valid_from = '';
  formData.notes = '';
};

const viewOnMap = (zone: Zone) => {
  console.log('View on map:', zone);
};

const editZone = (zone: Zone) => {
  Object.assign(formData, zone);
  showEditModal.value = true;
};

const saveZone = () => {
  if (showEditModal.value) {
    const index = zones.value.findIndex(z => z.id === formData.id);
    if (index !== -1) {
      zones.value[index] = { ...formData };
    }
  } else {
    zones.value.push({
      ...formData,
      id: zones.value.length + 1
    });
  }
  closeModals();
};

const deleteZone = (zone: Zone) => {
  if (confirm('Are you sure you want to delete this zone?')) {
    zones.value = zones.value.filter(z => z.id !== zone.id);
  }
};
</script>
