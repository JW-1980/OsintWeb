<template>
  <div class="p-6 bg-gray-50 dark:bg-gray-900 min-h-screen">
    <div class="max-w-7xl mx-auto">
      <!-- Header -->
      <div class="flex items-center justify-between mb-6">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Equipment Management</h1>
          <p class="text-gray-500 dark:text-gray-400 mt-1">Manage military equipment database</p>
        </div>
        <button @click="showCreateModal = true" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center space-x-2">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          <span>Add Equipment</span>
        </button>
      </div>

      <!-- Stats -->
      <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
          <p class="text-sm text-gray-500 dark:text-gray-400">Total Items</p>
          <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.total }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
          <p class="text-sm text-gray-500 dark:text-gray-400">Vehicles</p>
          <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ stats.vehicles }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
          <p class="text-sm text-gray-500 dark:text-gray-400">Aircraft</p>
          <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ stats.aircraft }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
          <p class="text-sm text-gray-500 dark:text-gray-400">Naval</p>
          <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ stats.naval }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
          <p class="text-sm text-gray-500 dark:text-gray-400">Artillery</p>
          <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ stats.artillery }}</p>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <input
            v-model="filters.search"
            type="text"
            placeholder="Search equipment..."
            class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
          />
          <select
            v-model="filters.category"
            class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
          >
            <option value="">All Categories</option>
            <option value="tank">Tanks</option>
            <option value="ifv">Infantry Fighting Vehicles</option>
            <option value="apc">Armored Personnel Carriers</option>
            <option value="aircraft">Aircraft</option>
            <option value="helicopter">Helicopters</option>
            <option value="artillery">Artillery</option>
            <option value="mlrs">MLRS</option>
            <option value="naval">Naval Vessels</option>
            <option value="drone">Drones/UAV</option>
            <option value="air_defense">Air Defense</option>
          </select>
          <select
            v-model="filters.origin"
            class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
          >
            <option value="">All Origins</option>
            <option value="russia">Russia</option>
            <option value="ukraine">Ukraine</option>
            <option value="usa">United States</option>
            <option value="germany">Germany</option>
            <option value="france">France</option>
            <option value="uk">United Kingdom</option>
            <option value="china">China</option>
          </select>
          <select
            v-model="filters.sortBy"
            class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
          >
            <option value="name">Sort by Name</option>
            <option value="category">Sort by Category</option>
            <option value="recent">Sort by Recent</option>
          </select>
        </div>
      </div>

      <!-- Equipment Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="item in filteredEquipment" :key="item.id" class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
          <div class="h-48 bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-600 flex items-center justify-center">
            <div v-if="item.image_url" class="w-full h-full bg-cover bg-center" :style="{ backgroundImage: `url(${item.image_url})` }"></div>
            <svg v-else class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
          </div>
          <div class="p-4">
            <div class="flex items-start justify-between mb-2">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ item.name }}</h3>
              <span :class="getCategoryBadge(item.category)" class="px-2 py-1 text-xs font-medium rounded-full">
                {{ item.category }}
              </span>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">{{ item.equipment_type }}</p>
            <div class="flex items-center justify-between text-sm">
              <span class="text-gray-500 dark:text-gray-400">{{ item.manufacturer }}</span>
              <span class="text-gray-500 dark:text-gray-400">{{ item.origin_country }}</span>
            </div>
            <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700 flex justify-end space-x-2">
              <button @click="viewItem(item)" class="p-2 text-blue-600 hover:bg-blue-100 dark:hover:bg-blue-900/30 rounded-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
              </button>
              <button @click="editItem(item)" class="p-2 text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
              </button>
              <button @click="deleteItem(item)" class="p-2 text-red-600 hover:bg-red-100 dark:hover:bg-red-900/30 rounded-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div class="mt-6 flex items-center justify-between">
        <p class="text-sm text-gray-500 dark:text-gray-400">
          Showing {{ filteredEquipment.length }} items
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
            {{ showEditModal ? 'Edit Equipment' : 'Add New Equipment' }}
          </h3>
          <form @submit.prevent="saveItem" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name</label>
                <input v-model="formData.name" type="text" required class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Equipment Type</label>
                <input v-model="formData.equipment_type" type="text" required class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
              </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Category</label>
                <select v-model="formData.category" required class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                  <option value="tank">Tank</option>
                  <option value="ifv">Infantry Fighting Vehicle</option>
                  <option value="apc">Armored Personnel Carrier</option>
                  <option value="aircraft">Aircraft</option>
                  <option value="helicopter">Helicopter</option>
                  <option value="artillery">Artillery</option>
                  <option value="mlrs">MLRS</option>
                  <option value="naval">Naval Vessel</option>
                  <option value="drone">Drone/UAV</option>
                  <option value="air_defense">Air Defense</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Manufacturer</label>
                <input v-model="formData.manufacturer" type="text" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Origin Country</label>
              <input v-model="formData.origin_country" type="text" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
              <textarea v-model="formData.description" rows="3" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"></textarea>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Image URL</label>
              <input v-model="formData.image_url" type="url" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
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
  total: 342,
  vehicles: 156,
  aircraft: 78,
  naval: 34,
  artillery: 74
});

const filters = reactive({
  search: '',
  category: '',
  origin: '',
  sortBy: 'name'
});

const showCreateModal = ref(false);
const showEditModal = ref(false);

interface EquipmentItem {
  id: number;
  name: string;
  equipment_type: string;
  category: string;
  manufacturer: string;
  origin_country: string;
  description?: string;
  image_url?: string;
}

const formData = reactive<EquipmentItem>({
  id: 0,
  name: '',
  equipment_type: '',
  category: 'tank',
  manufacturer: '',
  origin_country: '',
  description: '',
  image_url: ''
});

const equipment = ref<EquipmentItem[]>([
  { id: 1, name: 'T-72B3', equipment_type: 'Main Battle Tank', category: 'tank', manufacturer: 'Uralvagonzavod', origin_country: 'Russia', description: 'Modernized T-72 variant' },
  { id: 2, name: 'Leopard 2A6', equipment_type: 'Main Battle Tank', category: 'tank', manufacturer: 'Krauss-Maffei Wegmann', origin_country: 'Germany', description: 'Advanced NATO tank' },
  { id: 3, name: 'M142 HIMARS', equipment_type: 'Multiple Launch Rocket System', category: 'mlrs', manufacturer: 'Lockheed Martin', origin_country: 'USA', description: 'High Mobility Artillery Rocket System' },
  { id: 4, name: 'Su-34', equipment_type: 'Fighter-Bomber', category: 'aircraft', manufacturer: 'Sukhoi', origin_country: 'Russia', description: 'Twin-seat fighter-bomber' },
  { id: 5, name: 'Bayraktar TB2', equipment_type: 'Combat UAV', category: 'drone', manufacturer: 'Baykar', origin_country: 'Turkey', description: 'Medium altitude armed UAV' },
  { id: 6, name: 'BMP-2', equipment_type: 'Infantry Fighting Vehicle', category: 'ifv', manufacturer: 'Kurganmashzavod', origin_country: 'Russia', description: 'Soviet-era IFV still in wide use' },
]);

const filteredEquipment = computed(() => {
  return equipment.value.filter(item => {
    const matchesSearch = !filters.search || item.name.toLowerCase().includes(filters.search.toLowerCase());
    const matchesCategory = !filters.category || item.category === filters.category;
    const matchesOrigin = !filters.origin || item.origin_country.toLowerCase().includes(filters.origin.toLowerCase());
    return matchesSearch && matchesCategory && matchesOrigin;
  });
});

const getCategoryBadge = (category: string) => {
  const classes: Record<string, string> = {
    'tank': 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400',
    'ifv': 'bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400',
    'apc': 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400',
    'aircraft': 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400',
    'helicopter': 'bg-cyan-100 dark:bg-cyan-900/30 text-cyan-700 dark:text-cyan-400',
    'artillery': 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400',
    'mlrs': 'bg-pink-100 dark:bg-pink-900/30 text-pink-700 dark:text-pink-400',
    'naval': 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400',
    'drone': 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400',
    'air_defense': 'bg-teal-100 dark:bg-teal-900/30 text-teal-700 dark:text-teal-400'
  };
  return classes[category] || 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-400';
};

const closeModals = () => {
  showCreateModal.value = false;
  showEditModal.value = false;
  resetForm();
};

const resetForm = () => {
  formData.id = 0;
  formData.name = '';
  formData.equipment_type = '';
  formData.category = 'tank';
  formData.manufacturer = '';
  formData.origin_country = '';
  formData.description = '';
  formData.image_url = '';
};

const viewItem = (item: EquipmentItem) => {
  console.log('View item:', item);
};

const editItem = (item: EquipmentItem) => {
  Object.assign(formData, item);
  showEditModal.value = true;
};

const saveItem = () => {
  if (showEditModal.value) {
    const index = equipment.value.findIndex(e => e.id === formData.id);
    if (index !== -1) {
      equipment.value[index] = { ...formData };
    }
  } else {
    equipment.value.push({
      ...formData,
      id: equipment.value.length + 1
    });
  }
  closeModals();
};

const deleteItem = (item: EquipmentItem) => {
  if (confirm('Are you sure you want to delete this equipment?')) {
    equipment.value = equipment.value.filter(e => e.id !== item.id);
  }
};
</script>
