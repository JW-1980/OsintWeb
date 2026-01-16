<template>
  <div class="mt-6 space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Conflict Management</h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Track and manage active conflicts and their participants</p>
      </div>
      <button @click="openCreateModal" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Add Conflict
      </button>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">Total Conflicts</p>
        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ conflicts.length }}</p>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">Active</p>
        <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ activeConflicts }}</p>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">High Intensity</p>
        <p class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ highIntensity }}</p>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">Frozen</p>
        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ frozenConflicts }}</p>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <input v-model="filters.search" type="text" placeholder="Search conflicts..." class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400" />
        <select v-model="filters.type" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
          <option value="">All Types</option>
          <option value="CIVIL_WAR">Civil War</option>
          <option value="INTERSTATE">Interstate</option>
          <option value="INSURGENCY">Insurgency</option>
          <option value="PROXY_WAR">Proxy War</option>
          <option value="BORDER_DISPUTE">Border Dispute</option>
        </select>
        <select v-model="filters.intensity" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
          <option value="">All Intensity</option>
          <option value="high">High</option>
          <option value="medium">Medium</option>
          <option value="low">Low</option>
          <option value="frozen">Frozen</option>
        </select>
        <button @click="clearFilters" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600">Clear</button>
      </div>
    </div>

    <!-- Conflicts List -->
    <div class="space-y-4">
      <div v-for="conflict in paginatedConflicts" :key="conflict.id" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
          <div class="flex-1">
            <div class="flex items-start justify-between">
              <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ conflict.name }}</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ conflict.short_name || conflict.region }}</p>
              </div>
              <div class="flex space-x-2">
                <span :class="getIntensityBadge(conflict.intensity_level)" class="px-2.5 py-1 text-xs font-medium rounded-full">{{ conflict.intensity_level }}</span>
                <span :class="conflict.is_active ? 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-400'" class="px-2.5 py-1 text-xs font-medium rounded-full">{{ conflict.is_active ? 'Active' : 'Ended' }}</span>
              </div>
            </div>
            <p v-if="conflict.description" class="mt-2 text-sm text-gray-600 dark:text-gray-400 line-clamp-2">{{ conflict.description }}</p>
            <div class="mt-3 flex flex-wrap gap-4 text-sm text-gray-500 dark:text-gray-400">
              <span class="flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                Started: {{ formatDate(conflict.start_date) }}
              </span>
              <span v-if="conflict.end_date" class="flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                Ended: {{ formatDate(conflict.end_date) }}
              </span>
              <span v-if="conflict.conflict_type" class="flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" /></svg>
                {{ conflict.conflict_type?.replace('_', ' ') }}
              </span>
            </div>
            <div v-if="conflict.estimated_casualties?.total" class="mt-2 text-sm">
              <span class="text-gray-500 dark:text-gray-400">Est. Casualties:</span>
              <span class="ml-1 font-medium text-red-600 dark:text-red-400">{{ conflict.estimated_casualties.total.toLocaleString() }}</span>
            </div>
          </div>
          <div class="flex lg:flex-col gap-2">
            <button @click="editConflict(conflict)" class="flex-1 lg:flex-none px-3 py-1.5 text-sm text-blue-600 dark:text-blue-400 border border-blue-300 dark:border-blue-700 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/20">Edit</button>
            <button @click="viewConflict(conflict)" class="flex-1 lg:flex-none px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">View</button>
            <button @click="confirmDelete(conflict)" class="flex-1 lg:flex-none px-3 py-1.5 text-sm text-red-600 dark:text-red-400 border border-red-300 dark:border-red-700 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20">Delete</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-if="filteredConflicts.length === 0" class="text-center py-12 text-gray-500 dark:text-gray-400">
      <p>No conflicts found</p>
    </div>

    <!-- Pagination -->
    <div v-if="totalPages > 1" class="flex items-center justify-between">
      <p class="text-sm text-gray-500 dark:text-gray-400">{{ filteredConflicts.length }} conflicts</p>
      <div class="flex space-x-2">
        <button @click="currentPage--" :disabled="currentPage === 1" class="px-3 py-1.5 text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg disabled:opacity-50">Previous</button>
        <span class="px-3 py-1.5 text-sm text-gray-700 dark:text-gray-300">{{ currentPage }} / {{ totalPages }}</span>
        <button @click="currentPage++" :disabled="currentPage === totalPages" class="px-3 py-1.5 text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg disabled:opacity-50">Next</button>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="fixed inset-0 bg-black/50" @click="closeModal"></div>
      <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-2xl w-full p-6 max-h-[90vh] overflow-y-auto">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ editingConflict ? 'Edit Conflict' : 'Add Conflict' }}</h3>
        <form @submit.prevent="saveConflict" class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div class="col-span-2">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name *</label>
              <input v-model="form.name" type="text" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type</label>
              <select v-model="form.conflict_type" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                <option value="CIVIL_WAR">Civil War</option>
                <option value="INTERSTATE">Interstate</option>
                <option value="INSURGENCY">Insurgency</option>
                <option value="TERRORISM">Terrorism</option>
                <option value="PROXY_WAR">Proxy War</option>
                <option value="BORDER_DISPUTE">Border Dispute</option>
                <option value="OTHER">Other</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Intensity</label>
              <select v-model="form.intensity_level" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                <option value="high">High</option>
                <option value="medium">Medium</option>
                <option value="low">Low</option>
                <option value="frozen">Frozen</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Start Date *</label>
              <input v-model="form.start_date" type="date" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">End Date</label>
              <input v-model="form.end_date" type="date" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Region</label>
              <input v-model="form.region" type="text" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Est. Casualties</label>
              <input v-model="form.estimated_casualties_total" type="number" min="0" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
            </div>
            <div class="col-span-2">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
              <textarea v-model="form.description" rows="3" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"></textarea>
            </div>
          </div>
          <div class="flex items-center">
            <input v-model="form.is_active" type="checkbox" id="is_active" class="w-4 h-4 text-blue-600 rounded" />
            <label for="is_active" class="ml-2 text-sm text-gray-600 dark:text-gray-400">Active Conflict</label>
          </div>
          <div class="flex justify-end space-x-3 pt-4">
            <button type="button" @click="closeModal" class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg">Cancel</button>
            <button type="submit" :disabled="saving" class="px-4 py-2 text-sm text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50">{{ saving ? 'Saving...' : 'Save' }}</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Delete Modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="fixed inset-0 bg-black/50" @click="showDeleteModal = false"></div>
      <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-md w-full p-6 text-center">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Delete Conflict</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Delete <strong>{{ deletingConflict?.name }}</strong>?</p>
        <div class="flex justify-center space-x-3">
          <button @click="showDeleteModal = false" class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg">Cancel</button>
          <button @click="deleteConflict" class="px-4 py-2 text-sm text-white bg-red-600 rounded-lg hover:bg-red-700">Delete</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue';
import type { Conflict } from '@/types';

const conflicts = ref<Conflict[]>([]);
const saving = ref(false);
const currentPage = ref(1);
const perPage = ref(5);
const showModal = ref(false);
const showDeleteModal = ref(false);
const editingConflict = ref<Conflict | null>(null);
const deletingConflict = ref<Conflict | null>(null);

const form = reactive({
  name: '', conflict_type: 'CIVIL_WAR', intensity_level: 'medium', start_date: '', end_date: '',
  region: '', description: '', is_active: true, estimated_casualties_total: 0
});

const filters = reactive({ search: '', type: '', intensity: '' });

const activeConflicts = computed(() => conflicts.value.filter(c => c.is_active).length);
const highIntensity = computed(() => conflicts.value.filter(c => c.intensity_level === 'high').length);
const frozenConflicts = computed(() => conflicts.value.filter(c => c.intensity_level === 'frozen').length);

const filteredConflicts = computed(() => conflicts.value.filter(c => {
  const s = !filters.search || c.name.toLowerCase().includes(filters.search.toLowerCase());
  const t = !filters.type || c.conflict_type === filters.type;
  const i = !filters.intensity || c.intensity_level === filters.intensity;
  return s && t && i;
}));

const totalPages = computed(() => Math.ceil(filteredConflicts.value.length / perPage.value) || 1);
const paginatedConflicts = computed(() => filteredConflicts.value.slice((currentPage.value - 1) * perPage.value, currentPage.value * perPage.value));

const fetchConflicts = async () => {
  conflicts.value = [
    { id: 1, uuid: '1', name: 'Russia-Ukraine War', short_name: 'Ukraine War', conflict_type: 'INTERSTATE', intensity_level: 'high', start_date: '2022-02-24', is_active: true, region: 'Eastern Europe', description: 'Full-scale invasion of Ukraine by Russian forces', estimated_casualties: { total: 500000 } } as any,
    { id: 2, uuid: '2', name: 'Syrian Civil War', conflict_type: 'CIVIL_WAR', intensity_level: 'medium', start_date: '2011-03-15', is_active: true, region: 'Middle East', description: 'Multi-sided civil war in Syria', estimated_casualties: { total: 600000 } } as any,
    { id: 3, uuid: '3', name: 'Nagorno-Karabakh Conflict', conflict_type: 'BORDER_DISPUTE', intensity_level: 'frozen', start_date: '1988-02-20', end_date: '2023-09-20', is_active: false, region: 'South Caucasus', estimated_casualties: { total: 40000 } } as any,
  ];
};

const openCreateModal = () => { editingConflict.value = null; Object.assign(form, { name: '', conflict_type: 'CIVIL_WAR', intensity_level: 'medium', start_date: '', end_date: '', region: '', description: '', is_active: true, estimated_casualties_total: 0 }); showModal.value = true; };
const editConflict = (c: Conflict) => { editingConflict.value = c; Object.assign(form, { ...c, estimated_casualties_total: c.estimated_casualties?.total || 0 }); showModal.value = true; };
const viewConflict = (c: Conflict) => { editConflict(c); };
const closeModal = () => { showModal.value = false; };
const saveConflict = async () => { saving.value = true; try { if (editingConflict.value) { const i = conflicts.value.findIndex(c => c.id === editingConflict.value!.id); if (i !== -1) conflicts.value[i] = { ...conflicts.value[i], ...form, estimated_casualties: { total: form.estimated_casualties_total } } as any; } else { conflicts.value.unshift({ id: Date.now(), uuid: String(Date.now()), ...form, estimated_casualties: { total: form.estimated_casualties_total }, created_at: new Date().toISOString(), updated_at: new Date().toISOString() } as any); } closeModal(); } finally { saving.value = false; } };
const confirmDelete = (c: Conflict) => { deletingConflict.value = c; showDeleteModal.value = true; };
const deleteConflict = () => { conflicts.value = conflicts.value.filter(c => c.id !== deletingConflict.value?.id); showDeleteModal.value = false; };
const clearFilters = () => { Object.assign(filters, { search: '', type: '', intensity: '' }); currentPage.value = 1; };
const formatDate = (d: string) => d ? new Date(d).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) : '-';
const getIntensityBadge = (l: string) => ({ high: 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400', medium: 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400', low: 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400', frozen: 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400' }[l] || 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-400');

onMounted(fetchConflicts);
</script>
