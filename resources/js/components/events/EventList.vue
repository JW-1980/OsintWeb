<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useEventsStore } from '@/stores/events';
import EventCard from './EventCard.vue';
import { EventType, ConfidenceLevel } from '@/types';

const eventsStore = useEventsStore();

const filterEventType = ref<EventType | ''>('');
const filterConfidence = ref<ConfidenceLevel | ''>('');
const filterVerified = ref<boolean | null>(null);
const searchQuery = ref('');
const sortBy = ref<'occurred_at' | 'created_at'>('occurred_at');
const sortOrder = ref<'asc' | 'desc'>('desc');

onMounted(async () => {
  await eventsStore.fetchEvents();
});

const filteredAndSortedEvents = computed(() => {
  let events = [...eventsStore.filteredEvents];

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    events = events.filter(e =>
      e.title.toLowerCase().includes(query) ||
      e.description?.toLowerCase().includes(query)
    );
  }

  if (filterEventType.value) {
    events = events.filter(e => e.event_type === filterEventType.value);
  }

  if (filterConfidence.value) {
    events = events.filter(e => e.confidence_level === filterConfidence.value);
  }

  if (filterVerified.value !== null) {
    events = events.filter(e => e.verified === filterVerified.value);
  }

  events.sort((a, b) => {
    const aValue = new Date(a[sortBy.value]).getTime();
    const bValue = new Date(b[sortBy.value]).getTime();
    return sortOrder.value === 'asc' ? aValue - bValue : bValue - aValue;
  });

  return events;
});

const clearFilters = () => {
  filterEventType.value = '';
  filterConfidence.value = '';
  filterVerified.value = null;
  searchQuery.value = '';
};

const handlePageChange = (page: number) => {
  eventsStore.fetchEvents(page);
};
</script>

<template>
  <div class="event-list">
    <div class="bg-white shadow-md rounded-lg p-4 mb-4">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search events..."
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Event Type</label>
          <select
            v-model="filterEventType"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">All Types</option>
            <option value="combat_engagement">Combat Engagement</option>
            <option value="airstrike">Airstrike</option>
            <option value="artillery_strike">Artillery Strike</option>
            <option value="missile_strike">Missile Strike</option>
            <option value="equipment_destroyed">Equipment Destroyed</option>
            <option value="equipment_captured">Equipment Captured</option>
            <option value="equipment_sighting">Equipment Sighting</option>
            <option value="troop_movement">Troop Movement</option>
            <option value="civilian_casualties">Civilian Casualties</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Confidence</label>
          <select
            v-model="filterConfidence"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">All Levels</option>
            <option value="confirmed">Confirmed</option>
            <option value="likely">Likely</option>
            <option value="unconfirmed">Unconfirmed</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
          <select
            v-model="filterVerified"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option :value="null">All Events</option>
            <option :value="true">Verified Only</option>
            <option :value="false">Unverified Only</option>
          </select>
        </div>
      </div>

      <div class="mt-4 flex justify-between items-center">
        <button
          @click="clearFilters"
          class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300"
        >
          Clear Filters
        </button>

        <div class="flex items-center space-x-2">
          <label class="text-sm text-gray-700">Sort by:</label>
          <select
            v-model="sortBy"
            class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="occurred_at">Occurred Date</option>
            <option value="created_at">Created Date</option>
          </select>
          <button
            @click="sortOrder = sortOrder === 'asc' ? 'desc' : 'asc'"
            class="px-3 py-2 border border-gray-300 rounded-md hover:bg-gray-50"
          >
            {{ sortOrder === 'asc' ? '↑' : '↓' }}
          </button>
        </div>
      </div>
    </div>

    <div v-if="eventsStore.loading" class="text-center py-8">
      <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
      <p class="mt-2 text-gray-600">Loading events...</p>
    </div>

    <div v-else-if="filteredAndSortedEvents.length === 0" class="text-center py-8">
      <p class="text-gray-600">No events found.</p>
    </div>

    <div v-else class="space-y-4">
      <EventCard
        v-for="event in filteredAndSortedEvents"
        :key="event.id"
        :event="event"
      />
    </div>

    <div v-if="eventsStore.pagination.last_page > 1" class="mt-6 flex justify-center">
      <nav class="inline-flex rounded-md shadow-sm -space-x-px">
        <button
          @click="handlePageChange(eventsStore.pagination.current_page - 1)"
          :disabled="eventsStore.pagination.current_page === 1"
          class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50"
        >
          Previous
        </button>
        <button
          v-for="page in eventsStore.pagination.last_page"
          :key="page"
          @click="handlePageChange(page)"
          :class="{
            'bg-blue-600 text-white': page === eventsStore.pagination.current_page,
            'bg-white text-gray-700 hover:bg-gray-50': page !== eventsStore.pagination.current_page
          }"
          class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium"
        >
          {{ page }}
        </button>
        <button
          @click="handlePageChange(eventsStore.pagination.current_page + 1)"
          :disabled="eventsStore.pagination.current_page === eventsStore.pagination.last_page"
          class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50"
        >
          Next
        </button>
      </nav>
    </div>
  </div>
</template>

<style scoped>
.animate-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}
</style>
