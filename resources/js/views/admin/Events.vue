<template>
  <div class="p-6 bg-gray-50 dark:bg-gray-900 min-h-screen">
    <div class="max-w-7xl mx-auto">
      <!-- Header -->
      <div class="flex items-center justify-between mb-6">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Events Management</h1>
          <p class="text-gray-500 dark:text-gray-400 mt-1">Manage and verify OSINT events</p>
        </div>
        <button @click="showCreateModal = true" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center space-x-2">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          <span>Add Event</span>
        </button>
      </div>

      <!-- Stats -->
      <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
          <p class="text-sm text-gray-500 dark:text-gray-400">Total Events</p>
          <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.total }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
          <p class="text-sm text-gray-500 dark:text-gray-400">Verified</p>
          <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ stats.verified }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
          <p class="text-sm text-gray-500 dark:text-gray-400">Pending</p>
          <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ stats.pending }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
          <p class="text-sm text-gray-500 dark:text-gray-400">This Week</p>
          <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ stats.thisWeek }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
          <p class="text-sm text-gray-500 dark:text-gray-400">Unconfirmed</p>
          <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ stats.unconfirmed }}</p>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
          <input
            v-model="filters.search"
            type="text"
            placeholder="Search events..."
            class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
          />
          <select
            v-model="filters.eventType"
            class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
          >
            <option value="">All Types</option>
            <option value="combat_engagement">Combat Engagement</option>
            <option value="airstrike">Airstrike</option>
            <option value="artillery_strike">Artillery Strike</option>
            <option value="missile_strike">Missile Strike</option>
            <option value="equipment_destroyed">Equipment Destroyed</option>
            <option value="equipment_captured">Equipment Captured</option>
            <option value="troop_movement">Troop Movement</option>
            <option value="territory_change">Territory Change</option>
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
          <select
            v-model="filters.verified"
            class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
          >
            <option value="">All Status</option>
            <option value="true">Verified</option>
            <option value="false">Pending</option>
          </select>
          <input
            v-model="filters.dateFrom"
            type="date"
            class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
          />
        </div>
      </div>

      <!-- Events Table -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
          <thead class="bg-gray-50 dark:bg-gray-700">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Event</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Type</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Location</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Confidence</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
            <tr v-for="event in filteredEvents" :key="event.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="w-10 h-10 rounded-lg flex items-center justify-center" :class="getEventTypeColor(event.event_type)">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                  </div>
                  <div class="ml-4">
                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ event.title }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate max-w-xs">{{ event.description }}</p>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getEventTypeBadge(event.event_type)" class="px-2 py-1 text-xs font-medium rounded-full">
                  {{ formatEventType(event.event_type) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                {{ event.location_name || 'Unknown' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                {{ formatDate(event.occurred_at) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getConfidenceBadge(event.confidence_level)" class="px-2 py-1 text-xs font-medium rounded-full">
                  {{ event.confidence_level }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span v-if="event.verified" class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400">
                  Verified
                </span>
                <span v-else class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400">
                  Pending
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <div class="flex items-center justify-end space-x-2">
                  <button v-if="!event.verified" @click="verifyEvent(event)" class="p-2 text-green-600 hover:bg-green-100 dark:hover:bg-green-900/30 rounded-lg" title="Verify">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                  </button>
                  <button @click="viewEvent(event)" class="p-2 text-blue-600 hover:bg-blue-100 dark:hover:bg-blue-900/30 rounded-lg" title="View">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                  </button>
                  <button @click="editEvent(event)" class="p-2 text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg" title="Edit">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </button>
                  <button @click="deleteEvent(event)" class="p-2 text-red-600 hover:bg-red-100 dark:hover:bg-red-900/30 rounded-lg" title="Delete">
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
          Showing {{ filteredEvents.length }} of {{ events.length }} events
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

    <!-- Create/Edit Modal -->
    <div v-if="showCreateModal || showEditModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-black/50" @click="closeModals"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-2xl w-full p-6 max-h-[90vh] overflow-y-auto">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
            {{ showEditModal ? 'Edit Event' : 'Create New Event' }}
          </h3>
          <form @submit.prevent="saveEvent" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Title</label>
              <input v-model="formData.title" type="text" required class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Event Type</label>
              <select v-model="formData.event_type" required class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                <option value="combat_engagement">Combat Engagement</option>
                <option value="airstrike">Airstrike</option>
                <option value="artillery_strike">Artillery Strike</option>
                <option value="missile_strike">Missile Strike</option>
                <option value="equipment_destroyed">Equipment Destroyed</option>
                <option value="equipment_captured">Equipment Captured</option>
                <option value="troop_movement">Troop Movement</option>
                <option value="territory_change">Territory Change</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
              <textarea v-model="formData.description" rows="3" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Location Name</label>
                <input v-model="formData.location_name" type="text" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Occurred At</label>
                <input v-model="formData.occurred_at" type="datetime-local" required class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Confidence Level</label>
              <select v-model="formData.confidence_level" required class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                <option value="confirmed">Confirmed</option>
                <option value="likely">Likely</option>
                <option value="unconfirmed">Unconfirmed</option>
              </select>
            </div>
            <div class="flex items-center">
              <input v-model="formData.verified" type="checkbox" id="verified" class="w-4 h-4 text-blue-600 bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded" />
              <label for="verified" class="ml-2 text-sm text-gray-700 dark:text-gray-300">Mark as verified</label>
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
import type { EventType, ConfidenceLevel } from '@/types';

const stats = ref({
  total: 156,
  verified: 124,
  pending: 32,
  thisWeek: 23,
  unconfirmed: 18
});

const filters = reactive({
  search: '',
  eventType: '',
  confidence: '',
  verified: '',
  dateFrom: ''
});

const showCreateModal = ref(false);
const showEditModal = ref(false);

interface AdminEvent {
  id: number;
  title: string;
  event_type: EventType;
  description?: string;
  location_name?: string;
  occurred_at: string;
  confidence_level: ConfidenceLevel;
  verified: boolean;
}

const formData = reactive<AdminEvent>({
  id: 0,
  title: '',
  event_type: 'combat_engagement',
  description: '',
  location_name: '',
  occurred_at: '',
  confidence_level: 'unconfirmed',
  verified: false
});

const events = ref<AdminEvent[]>([
  { id: 1, title: 'Artillery strike on military base', event_type: 'artillery_strike', description: 'Multiple MLRS strikes reported', location_name: 'Kherson Oblast', occurred_at: '2024-01-15T14:30:00', confidence_level: 'confirmed', verified: true },
  { id: 2, title: 'Tank column spotted', event_type: 'troop_movement', description: 'Convoy of 12 tanks moving east', location_name: 'Zaporizhzhia', occurred_at: '2024-01-15T10:00:00', confidence_level: 'likely', verified: false },
  { id: 3, title: 'Missile strike on infrastructure', event_type: 'missile_strike', description: 'Critical infrastructure targeted', location_name: 'Kyiv', occurred_at: '2024-01-14T03:45:00', confidence_level: 'confirmed', verified: true },
  { id: 4, title: 'Combat engagement near frontline', event_type: 'combat_engagement', description: 'Infantry firefight reported', location_name: 'Bakhmut', occurred_at: '2024-01-14T18:20:00', confidence_level: 'unconfirmed', verified: false },
  { id: 5, title: 'Enemy equipment destroyed', event_type: 'equipment_destroyed', description: '2 IFVs and 1 tank destroyed', location_name: 'Donetsk Oblast', occurred_at: '2024-01-13T12:00:00', confidence_level: 'confirmed', verified: true },
]);

const filteredEvents = computed(() => {
  return events.value.filter(event => {
    const matchesSearch = !filters.search || event.title.toLowerCase().includes(filters.search.toLowerCase());
    const matchesType = !filters.eventType || event.event_type === filters.eventType;
    const matchesConfidence = !filters.confidence || event.confidence_level === filters.confidence;
    const matchesVerified = !filters.verified || event.verified.toString() === filters.verified;
    return matchesSearch && matchesType && matchesConfidence && matchesVerified;
  });
});

const getEventTypeColor = (type: string) => {
  const colors: Record<string, string> = {
    'combat_engagement': 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400',
    'airstrike': 'bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400',
    'artillery_strike': 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-600 dark:text-yellow-400',
    'missile_strike': 'bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400',
    'equipment_destroyed': 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400',
    'equipment_captured': 'bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400',
    'troop_movement': 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400',
    'territory_change': 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400'
  };
  return colors[type] || 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400';
};

const getEventTypeBadge = (type: string) => getEventTypeColor(type);

const formatEventType = (type: string) => {
  return type.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
};

const getConfidenceBadge = (level: string) => {
  const classes: Record<string, string> = {
    'confirmed': 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400',
    'likely': 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400',
    'unconfirmed': 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400'
  };
  return classes[level] || 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-400';
};

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
};

const closeModals = () => {
  showCreateModal.value = false;
  showEditModal.value = false;
  resetForm();
};

const resetForm = () => {
  formData.id = 0;
  formData.title = '';
  formData.event_type = 'combat_engagement';
  formData.description = '';
  formData.location_name = '';
  formData.occurred_at = '';
  formData.confidence_level = 'unconfirmed';
  formData.verified = false;
};

const viewEvent = (event: AdminEvent) => {
  console.log('View event:', event);
};

const editEvent = (event: AdminEvent) => {
  formData.id = event.id;
  formData.title = event.title;
  formData.event_type = event.event_type;
  formData.description = event.description || '';
  formData.location_name = event.location_name || '';
  formData.occurred_at = event.occurred_at;
  formData.confidence_level = event.confidence_level;
  formData.verified = event.verified;
  showEditModal.value = true;
};

const saveEvent = () => {
  if (showEditModal.value) {
    const index = events.value.findIndex(e => e.id === formData.id);
    if (index !== -1) {
      events.value[index] = { ...formData };
    }
  } else {
    events.value.push({
      ...formData,
      id: events.value.length + 1
    });
  }
  closeModals();
};

const verifyEvent = (event: AdminEvent) => {
  event.verified = true;
};

const deleteEvent = (event: AdminEvent) => {
  if (confirm('Are you sure you want to delete this event?')) {
    events.value = events.value.filter(e => e.id !== event.id);
  }
};
</script>
