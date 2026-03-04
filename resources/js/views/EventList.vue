<template>
  <div class="p-6 bg-gray-50 dark:bg-gray-900 min-h-screen">
    <div class="max-w-7xl mx-auto">
      <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Events</h1>
        <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center space-x-2">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          <span>Report Event</span>
        </button>
      </div>

      <!-- Stats -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
          <p class="text-sm text-gray-500 dark:text-gray-400">Total Events</p>
          <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ (stats?.total || 0).toLocaleString() }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
          <p class="text-sm text-gray-500 dark:text-gray-400">Today</p>
          <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ stats.today }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
          <p class="text-sm text-gray-500 dark:text-gray-400">Verified</p>
          <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ stats.verified }}%</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
          <p class="text-sm text-gray-500 dark:text-gray-400">Pending Review</p>
          <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ stats.pending }}</p>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
          <div class="md:col-span-2">
            <input
              v-model="filters.search"
              type="text"
              placeholder="Search events..."
              class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
            />
          </div>
          <select
            v-model="filters.type"
            class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
          >
            <option value="">All Types</option>
            <option value="combat_engagement">Combat Engagement</option>
            <option value="airstrike">Airstrike</option>
            <option value="artillery_strike">Artillery Strike</option>
            <option value="missile_strike">Missile Strike</option>
            <option value="equipment_destroyed">Equipment Destroyed</option>
            <option value="troop_movement">Troop Movement</option>
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
          <input
            v-model="filters.dateFrom"
            type="date"
            class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
          />
          <input
            v-model="filters.dateTo"
            type="date"
            class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
          />
        </div>
      </div>

      <!-- Events List -->
      <div class="space-y-4">
        <div
          v-for="event in filteredEvents"
          :key="event.id"
          class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden hover:shadow-lg transition-shadow cursor-pointer"
          @click="viewEvent(event)"
        >
          <div class="p-6">
            <div class="flex items-start justify-between">
              <div class="flex items-start space-x-4">
                <div :class="getEventIconBg(event.event_type)" class="w-12 h-12 rounded-lg flex items-center justify-center flex-shrink-0">
                  <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path v-if="event.event_type === 'airstrike'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    <path v-else-if="event.event_type === 'artillery_strike'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z" />
                  </svg>
                </div>
                <div>
                  <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ event.title }}</h3>
                  <p class="text-sm text-gray-500 dark:text-gray-400">{{ event.location_name }}</p>
                </div>
              </div>
              <div class="flex items-center space-x-2">
                <span :class="getTypeBadge(event.event_type)" class="px-3 py-1 text-xs font-medium rounded-full">
                  {{ formatEventType(event.event_type) }}
                </span>
                <span :class="getConfidenceBadge(event.confidence_level)" class="px-3 py-1 text-xs font-medium rounded-full">
                  {{ event.confidence_level }}
                </span>
                <span v-if="event.verified" class="px-3 py-1 text-xs font-medium rounded-full bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400">
                  Verified
                </span>
              </div>
            </div>

            <p v-if="event.description" class="mt-3 text-sm text-gray-600 dark:text-gray-400 line-clamp-2">
              {{ event.description }}
            </p>

            <div class="mt-4 flex items-center justify-between">
              <div class="flex items-center space-x-4 text-sm text-gray-500 dark:text-gray-400">
                <span class="flex items-center space-x-1">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <span>{{ formatDateTime(event.occurred_at) }}</span>
                </span>
                <span v-if="event.actor" class="flex items-center space-x-1">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                  </svg>
                  <span>{{ event.actor }}</span>
                </span>
                <span v-if="event.source_urls?.length" class="flex items-center space-x-1">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                  </svg>
                  <span>{{ event.source_urls.length }} sources</span>
                </span>
              </div>
              <div class="flex items-center space-x-2">
                <button class="p-2 text-gray-400 hover:text-blue-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700" @click.stop>
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                </button>
                <button class="p-2 text-gray-400 hover:text-green-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700" @click.stop>
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div class="mt-6 flex items-center justify-between">
        <p class="text-sm text-gray-500 dark:text-gray-400">
          Showing {{ (currentPage - 1) * perPage + 1 }} to {{ Math.min(currentPage * perPage, filteredEvents.length) }} of {{ filteredEvents.length }} events
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
const currentPage = ref(1);
const perPage = ref(10);

const stats = ref({
  total: 15847,
  today: 127,
  verified: 78,
  pending: 342
});

const filters = reactive({
  search: '',
  type: '',
  confidence: '',
  dateFrom: '',
  dateTo: ''
});

interface Event {
  id: number;
  title: string;
  event_type: string;
  description?: string;
  location_name: string;
  occurred_at: string;
  confidence_level: 'confirmed' | 'likely' | 'unconfirmed';
  verified: boolean;
  actor?: string;
  source_urls?: string[];
}

const events = ref<Event[]>([
  { id: 1, title: 'Artillery strike on military positions', event_type: 'artillery_strike', description: 'Multiple MLRS strikes reported targeting defensive positions near the front line.', location_name: 'Zaporizhzhia Oblast, Ukraine', occurred_at: '2024-01-16T14:30:00Z', confidence_level: 'confirmed', verified: true, actor: 'Russian Armed Forces', source_urls: ['https://example.com/1', 'https://example.com/2'] },
  { id: 2, title: 'Air defense engagement', event_type: 'airstrike', description: 'Ukrainian air defense systems engaged incoming cruise missiles.', location_name: 'Kyiv, Ukraine', occurred_at: '2024-01-16T06:15:00Z', confidence_level: 'confirmed', verified: true, actor: 'Ukrainian Air Force', source_urls: ['https://example.com/3'] },
  { id: 3, title: 'Tank destroyed by FPV drone', event_type: 'equipment_destroyed', description: 'T-72B3 main battle tank destroyed by first-person view drone strike.', location_name: 'Donetsk Oblast, Ukraine', occurred_at: '2024-01-15T18:45:00Z', confidence_level: 'likely', verified: false, source_urls: ['https://example.com/4'] },
  { id: 4, title: 'Troop movement detected', event_type: 'troop_movement', description: 'Satellite imagery shows significant troop concentration near border area.', location_name: 'Luhansk Oblast, Ukraine', occurred_at: '2024-01-15T12:00:00Z', confidence_level: 'likely', verified: false, actor: 'Russian Armed Forces' },
  { id: 5, title: 'Missile strike on infrastructure', event_type: 'missile_strike', description: 'Kh-101 cruise missile struck energy infrastructure, causing power outages.', location_name: 'Kharkiv, Ukraine', occurred_at: '2024-01-14T22:30:00Z', confidence_level: 'confirmed', verified: true, actor: 'Russian Armed Forces', source_urls: ['https://example.com/5', 'https://example.com/6', 'https://example.com/7'] },
  { id: 6, title: 'Combat engagement near Bakhmut', event_type: 'combat_engagement', description: 'Infantry clashes reported in the suburbs with both sides claiming territorial gains.', location_name: 'Bakhmut, Donetsk Oblast', occurred_at: '2024-01-14T16:20:00Z', confidence_level: 'unconfirmed', verified: false }
]);

const filteredEvents = computed(() => {
  return events.value.filter(event => {
    const matchesSearch = !filters.search ||
      event.title.toLowerCase().includes(filters.search.toLowerCase()) ||
      event.location_name.toLowerCase().includes(filters.search.toLowerCase());
    const matchesType = !filters.type || event.event_type === filters.type;
    const matchesConfidence = !filters.confidence || event.confidence_level === filters.confidence;
    return matchesSearch && matchesType && matchesConfidence;
  });
});

const totalPages = computed(() => Math.ceil(filteredEvents.value.length / perPage.value) || 1);

const getEventIconBg = (type: string) => {
  const colors: Record<string, string> = {
    'combat_engagement': 'bg-red-500',
    'airstrike': 'bg-pink-500',
    'artillery_strike': 'bg-purple-500',
    'missile_strike': 'bg-indigo-500',
    'equipment_destroyed': 'bg-orange-500',
    'troop_movement': 'bg-teal-500'
  };
  return colors[type] || 'bg-gray-500';
};

const getTypeBadge = (type: string) => {
  const colors: Record<string, string> = {
    'combat_engagement': 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400',
    'airstrike': 'bg-pink-100 dark:bg-pink-900/30 text-pink-700 dark:text-pink-400',
    'artillery_strike': 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400',
    'missile_strike': 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400',
    'equipment_destroyed': 'bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400',
    'troop_movement': 'bg-teal-100 dark:bg-teal-900/30 text-teal-700 dark:text-teal-400'
  };
  return colors[type] || 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-400';
};

const getConfidenceBadge = (confidence: string) => {
  const colors: Record<string, string> = {
    'confirmed': 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400',
    'likely': 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400',
    'unconfirmed': 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400'
  };
  return colors[confidence] || 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-400';
};

const formatEventType = (type: string) => {
  if (!type) return 'Unknown';
  return String(type).split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
};

const formatDateTime = (dateString: string) => {
  return new Date(dateString).toLocaleString('en-US', {
    year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'
  });
};

const viewEvent = (event: Event) => {
  router.push({ name: 'event-detail', params: { id: event.id } });
};
</script>
