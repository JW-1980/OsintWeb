<template>
  <div class="mt-6 space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Actor Management</h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage military actors, groups, and organizations</p>
      </div>
      <button @click="openCreateModal" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Add Actor
      </button>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">Total Actors</p>
        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ actors.length }}</p>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">State Actors</p>
        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ stateActors }}</p>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">Non-State</p>
        <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ nonStateActors }}</p>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">Active in Conflict</p>
        <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ activeInConflict }}</p>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <div>
          <input v-model="filters.search" type="text" placeholder="Search actors..." class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500" />
        </div>
        <div>
          <select v-model="filters.actor_type" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
            <option value="">All Types</option>
            <option value="STATE">State</option>
            <option value="SEPARATIST">Separatist</option>
            <option value="INSURGENT">Insurgent</option>
            <option value="MILITIA">Militia</option>
            <option value="PMC">PMC</option>
            <option value="COALITION">Coalition</option>
          </select>
        </div>
        <div>
          <select v-model="filters.activity_level" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
            <option value="">All Activity</option>
            <option value="high">High</option>
            <option value="medium">Medium</option>
            <option value="low">Low</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>
        <div>
          <select v-model="filters.designation" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
            <option value="">All Designations</option>
            <option value="state">State Actor</option>
            <option value="terrorist">Designated Terrorist</option>
          </select>
        </div>
        <button @click="clearFilters" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600">
          Clear
        </button>
      </div>
    </div>

    <!-- Actors Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <div v-for="actor in paginatedActors" :key="actor.id" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 hover:shadow-md transition-shadow">
        <div class="flex items-start justify-between mb-3">
          <div class="flex items-center space-x-3">
            <div :style="{ backgroundColor: actor.color_hex || '#3B82F6' }" class="w-10 h-10 rounded-lg flex items-center justify-center text-white font-bold">
              {{ actor.name.charAt(0) }}
            </div>
            <div>
              <h3 class="font-semibold text-gray-900 dark:text-white">{{ actor.name }}</h3>
              <p class="text-xs text-gray-500 dark:text-gray-400">{{ actor.short_name || actor.actor_type }}</p>
            </div>
          </div>
          <div class="flex space-x-1">
            <button @click="editActor(actor)" class="p-1.5 text-gray-400 hover:text-blue-600 rounded">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
            </button>
            <button @click="confirmDelete(actor)" class="p-1.5 text-gray-400 hover:text-red-600 rounded">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
            </button>
          </div>
        </div>
        <div class="flex flex-wrap gap-1 mb-3">
          <span :class="getTypeBadgeClass(actor.actor_type)" class="px-2 py-0.5 text-xs font-medium rounded-full">{{ actor.actor_type }}</span>
          <span :class="getActivityBadgeClass(actor.activity_level)" class="px-2 py-0.5 text-xs font-medium rounded-full">{{ actor.activity_level }}</span>
          <span v-if="actor.is_state_actor" class="px-2 py-0.5 text-xs font-medium rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400">State</span>
          <span v-if="actor.is_designated_terrorist" class="px-2 py-0.5 text-xs font-medium rounded-full bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400">Terrorist</span>
        </div>
        <p v-if="actor.description" class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2 mb-2">{{ actor.description }}</p>
        <div class="text-xs text-gray-500 dark:text-gray-400 flex justify-between">
          <span v-if="actor.primary_region">{{ actor.primary_region }}</span>
          <span>Priority: {{ actor.priority_score }}</span>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-if="filteredActors.length === 0" class="text-center py-12 text-gray-500 dark:text-gray-400">
      <p>No actors found</p>
      <button @click="clearFilters" class="mt-2 text-blue-600 dark:text-blue-400 hover:underline">Clear filters</button>
    </div>

    <!-- Pagination -->
    <div v-if="totalPages > 1" class="flex items-center justify-between">
      <p class="text-sm text-gray-500 dark:text-gray-400">{{ filteredActors.length }} actors</p>
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
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ editingActor ? 'Edit Actor' : 'Add Actor' }}</h3>
        <form @submit.prevent="saveActor" class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name *</label>
              <input v-model="form.name" type="text" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Short Name</label>
              <input v-model="form.short_name" type="text" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type *</label>
              <select v-model="form.actor_type" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                <option value="STATE">State</option>
                <option value="SEPARATIST">Separatist</option>
                <option value="INSURGENT">Insurgent</option>
                <option value="MILITIA">Militia</option>
                <option value="PMC">PMC</option>
                <option value="COALITION">Coalition</option>
                <option value="REBEL">Rebel</option>
                <option value="PROXY">Proxy</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Activity Level</label>
              <select v-model="form.activity_level" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                <option value="high">High</option>
                <option value="medium">Medium</option>
                <option value="low">Low</option>
                <option value="inactive">Inactive</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Primary Region</label>
              <input v-model="form.primary_region" type="text" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Color</label>
              <input v-model="form.color_hex" type="color" class="w-full h-10 rounded border border-gray-300 dark:border-gray-600 cursor-pointer" />
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
            <textarea v-model="form.description" rows="2" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"></textarea>
          </div>
          <div class="flex flex-wrap gap-4">
            <label class="flex items-center">
              <input v-model="form.is_state_actor" type="checkbox" class="w-4 h-4 text-blue-600 rounded" />
              <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">State Actor</span>
            </label>
            <label class="flex items-center">
              <input v-model="form.is_designated_terrorist" type="checkbox" class="w-4 h-4 text-blue-600 rounded" />
              <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Designated Terrorist</span>
            </label>
            <label class="flex items-center">
              <input v-model="form.is_active_in_conflict" type="checkbox" class="w-4 h-4 text-blue-600 rounded" />
              <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Active in Conflict</span>
            </label>
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
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Delete Actor</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Delete <strong>{{ deletingActor?.name }}</strong>?</p>
        <div class="flex justify-center space-x-3">
          <button @click="showDeleteModal = false" class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg">Cancel</button>
          <button @click="deleteActor" class="px-4 py-2 text-sm text-white bg-red-600 rounded-lg hover:bg-red-700">Delete</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue';
import type { Actor } from '@/types';

const actors = ref<Actor[]>([]);
const saving = ref(false);
const currentPage = ref(1);
const perPage = ref(9);
const showModal = ref(false);
const showDeleteModal = ref(false);
const editingActor = ref<Actor | null>(null);
const deletingActor = ref<Actor | null>(null);

const form = reactive({
  name: '', short_name: '', actor_type: 'STATE', activity_level: 'medium',
  primary_region: '', color_hex: '#3B82F6', description: '',
  is_state_actor: false, is_designated_terrorist: false, is_active_in_conflict: false, priority_score: 50
});

const filters = reactive({ search: '', actor_type: '', activity_level: '', designation: '' });

const stateActors = computed(() => actors.value.filter(a => a.is_state_actor).length);
const nonStateActors = computed(() => actors.value.filter(a => !a.is_state_actor).length);
const activeInConflict = computed(() => actors.value.filter(a => a.is_active_in_conflict).length);

const filteredActors = computed(() => actors.value.filter(actor => {
  const s = !filters.search || actor.name.toLowerCase().includes(filters.search.toLowerCase());
  const t = !filters.actor_type || actor.actor_type === filters.actor_type;
  const a = !filters.activity_level || actor.activity_level === filters.activity_level;
  const d = !filters.designation || (filters.designation === 'state' && actor.is_state_actor) || (filters.designation === 'terrorist' && actor.is_designated_terrorist);
  return s && t && a && d;
}));

const totalPages = computed(() => Math.ceil(filteredActors.value.length / perPage.value) || 1);
const paginatedActors = computed(() => filteredActors.value.slice((currentPage.value - 1) * perPage.value, currentPage.value * perPage.value));

const fetchActors = async () => {
  actors.value = [
    { id: 1, uuid: '1', name: 'Armed Forces of Ukraine', short_name: 'AFU', actor_type: 'STATE', activity_level: 'high', is_state_actor: true, is_designated_terrorist: false, is_active_in_conflict: true, primary_region: 'Ukraine', priority_score: 100, description: 'Official military forces of Ukraine', color_hex: '#0057B7', autocomplete_priority: 100 } as any,
    { id: 2, uuid: '2', name: 'Russian Armed Forces', short_name: 'RAF', actor_type: 'STATE', activity_level: 'high', is_state_actor: true, is_designated_terrorist: false, is_active_in_conflict: true, primary_region: 'Russia', priority_score: 100, description: 'Military forces of the Russian Federation', color_hex: '#D52B1E', autocomplete_priority: 100 } as any,
    { id: 3, uuid: '3', name: 'Wagner Group', short_name: 'Wagner', actor_type: 'PMC', activity_level: 'medium', is_state_actor: false, is_designated_terrorist: true, is_active_in_conflict: true, primary_region: 'Multiple', priority_score: 85, description: 'Russian private military company', color_hex: '#1A1A1A', autocomplete_priority: 90 } as any,
    { id: 4, uuid: '4', name: 'Donetsk Peoples Republic', short_name: 'DPR', actor_type: 'SEPARATIST', activity_level: 'high', is_state_actor: false, is_designated_terrorist: false, is_active_in_conflict: true, primary_region: 'Donetsk', priority_score: 75, color_hex: '#1C3C69', autocomplete_priority: 80 } as any,
  ];
};

const openCreateModal = () => { editingActor.value = null; Object.assign(form, { name: '', short_name: '', actor_type: 'STATE', activity_level: 'medium', primary_region: '', color_hex: '#3B82F6', description: '', is_state_actor: false, is_designated_terrorist: false, is_active_in_conflict: false, priority_score: 50 }); showModal.value = true; };
const editActor = (actor: Actor) => { editingActor.value = actor; Object.assign(form, actor); showModal.value = true; };
const closeModal = () => { showModal.value = false; editingActor.value = null; };
const saveActor = async () => { saving.value = true; try { if (editingActor.value) { const i = actors.value.findIndex(a => a.id === editingActor.value!.id); if (i !== -1) actors.value[i] = { ...actors.value[i], ...form } as any; } else { actors.value.unshift({ id: Date.now(), uuid: String(Date.now()), ...form, autocomplete_priority: form.priority_score, created_at: new Date().toISOString(), updated_at: new Date().toISOString() } as any); } closeModal(); } finally { saving.value = false; } };
const confirmDelete = (actor: Actor) => { deletingActor.value = actor; showDeleteModal.value = true; };
const deleteActor = () => { actors.value = actors.value.filter(a => a.id !== deletingActor.value?.id); showDeleteModal.value = false; };
const clearFilters = () => { Object.assign(filters, { search: '', actor_type: '', activity_level: '', designation: '' }); currentPage.value = 1; };

const getTypeBadgeClass = (t: string) => ({ STATE: 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400', SEPARATIST: 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400', PMC: 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-400', MILITIA: 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400' }[t] || 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-400');
const getActivityBadgeClass = (l: string) => ({ high: 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400', medium: 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400', low: 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400' }[l] || 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-400');

onMounted(fetchActors);
</script>
