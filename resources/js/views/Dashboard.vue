<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useEventsStore } from '@/stores/events';
import { useAuthStore } from '@/stores/auth';
import EventCard from '@/components/events/EventCard.vue';

const router = useRouter();
const eventsStore = useEventsStore();
const authStore = useAuthStore();

const stats = ref({
  totalEvents: 0,
  verifiedEvents: 0,
  activeConflicts: 0,
  recentEvents: 0
});

const recentEvents = computed(() => {
  return eventsStore.events.slice(0, 5);
});

onMounted(async () => {
  await eventsStore.fetchEvents();

  stats.value = {
    totalEvents: eventsStore.pagination.total,
    verifiedEvents: eventsStore.events.filter(e => e.verified).length,
    activeConflicts: 0,
    recentEvents: eventsStore.events.filter(e => {
      const eventDate = new Date(e.occurred_at);
      const sevenDaysAgo = new Date();
      sevenDaysAgo.setDate(sevenDaysAgo.getDate() - 7);
      return eventDate >= sevenDaysAgo;
    }).length
  };
});

const navigateToMap = () => {
  router.push({ name: 'map' });
};

const navigateToEvents = () => {
  router.push({ name: 'events' });
};
</script>

<template>
  <div class="dashboard">
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
      <p class="mt-2 text-gray-600">
        Welcome back, {{ authStore.user?.name }}
      </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <div class="bg-white shadow-md rounded-lg p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-600">Total Events</p>
            <p class="text-3xl font-bold text-gray-900 mt-2">{{ stats.totalEvents }}</p>
          </div>
          <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
          </div>
        </div>
      </div>

      <div class="bg-white shadow-md rounded-lg p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-600">Verified Events</p>
            <p class="text-3xl font-bold text-gray-900 mt-2">{{ stats.verifiedEvents }}</p>
          </div>
          <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
        </div>
      </div>

      <div class="bg-white shadow-md rounded-lg p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-600">Active Conflicts</p>
            <p class="text-3xl font-bold text-gray-900 mt-2">{{ stats.activeConflicts }}</p>
          </div>
          <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
          </div>
        </div>
      </div>

      <div class="bg-white shadow-md rounded-lg p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-600">Recent Events (7d)</p>
            <p class="text-3xl font-bold text-gray-900 mt-2">{{ stats.recentEvents }}</p>
          </div>
          <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
            </svg>
          </div>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <div class="bg-white shadow-md rounded-lg p-6">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-xl font-semibold text-gray-900">Recent Events</h2>
          <button
            @click="navigateToEvents"
            class="text-blue-600 hover:text-blue-800 text-sm font-medium"
          >
            View All
          </button>
        </div>

        <div v-if="recentEvents.length === 0" class="text-center py-8 text-gray-500">
          No recent events
        </div>

        <div v-else class="space-y-4">
          <EventCard
            v-for="event in recentEvents"
            :key="event.id"
            :event="event"
          />
        </div>
      </div>

      <div class="bg-white shadow-md rounded-lg p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Quick Actions</h2>

        <div class="space-y-3">
          <button
            @click="navigateToMap"
            class="w-full flex items-center justify-between p-4 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors"
          >
            <div class="flex items-center space-x-3">
              <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                </svg>
              </div>
              <div class="text-left">
                <div class="font-medium text-gray-900">View Map</div>
                <div class="text-sm text-gray-600">Explore interactive map</div>
              </div>
            </div>
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </button>

          <button
            @click="router.push({ name: 'events' })"
            class="w-full flex items-center justify-between p-4 bg-green-50 hover:bg-green-100 rounded-lg transition-colors"
          >
            <div class="flex items-center space-x-3">
              <div class="w-10 h-10 bg-green-600 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
              </div>
              <div class="text-left">
                <div class="font-medium text-gray-900">Add Event</div>
                <div class="text-sm text-gray-600">Document a new event</div>
              </div>
            </div>
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </button>

          <button
            @click="router.push({ name: 'analytics' })"
            class="w-full flex items-center justify-between p-4 bg-purple-50 hover:bg-purple-100 rounded-lg transition-colors"
          >
            <div class="flex items-center space-x-3">
              <div class="w-10 h-10 bg-purple-600 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
              </div>
              <div class="text-left">
                <div class="font-medium text-gray-900">View Analytics</div>
                <div class="text-sm text-gray-600">Analyze trends and patterns</div>
              </div>
            </div>
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
