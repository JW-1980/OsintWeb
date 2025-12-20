<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useEventsStore } from '@/stores/events';
import { useAuthStore } from '@/stores/auth';

const route = useRoute();
const router = useRouter();
const eventsStore = useEventsStore();
const authStore = useAuthStore();

const eventId = computed(() => route.params.id as string);
const event = computed(() => eventsStore.currentEvent);

const isEditing = ref(false);
const showDeleteConfirm = ref(false);

onMounted(async () => {
  await eventsStore.fetchEvent(eventId.value);
});

const eventTypeLabel = computed(() => {
  if (!event.value) return '';

  const labels: Record<string, string> = {
    combat_engagement: 'Combat Engagement',
    airstrike: 'Airstrike',
    artillery_strike: 'Artillery Strike',
    missile_strike: 'Missile Strike',
    equipment_destroyed: 'Equipment Destroyed',
    equipment_captured: 'Equipment Captured',
    equipment_abandoned: 'Equipment Abandoned',
    equipment_sighting: 'Equipment Sighting',
    troop_movement: 'Troop Movement',
    convoy_spotted: 'Convoy Spotted',
    infrastructure_damage: 'Infrastructure Damage',
    fortification: 'Fortification',
    civilian_casualties: 'Civilian Casualties',
    evacuation: 'Evacuation'
  };

  return labels[event.value.event_type] || event.value.event_type;
});

const formattedDate = computed(() => {
  if (!event.value) return '';

  return new Date(event.value.occurred_at).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
});

const handleEdit = () => {
  isEditing.value = true;
};

const handleDelete = async () => {
  if (!event.value) return;

  try {
    await eventsStore.deleteEvent(event.value.id);
    router.push({ name: 'events' });
  } catch (error) {
    console.error('Failed to delete event:', error);
  }
};

const viewOnMap = () => {
  if (!event.value || !event.value.coordinates) return;

  router.push({
    name: 'map',
    query: {
      lat: event.value.coordinates.coordinates[1],
      lng: event.value.coordinates.coordinates[0],
      zoom: 12
    }
  });
};
</script>

<template>
  <div v-if="eventsStore.loading" class="flex items-center justify-center h-96">
    <div class="text-center">
      <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      <p class="mt-4 text-gray-600">Loading event...</p>
    </div>
  </div>

  <div v-else-if="!event" class="text-center py-12">
    <p class="text-gray-600">Event not found</p>
    <router-link to="/events" class="text-blue-600 hover:underline mt-4 inline-block">
      Back to events
    </router-link>
  </div>

  <div v-else class="event-detail max-w-4xl mx-auto">
    <div class="mb-6 flex items-center justify-between">
      <button
        @click="router.back()"
        class="flex items-center text-gray-600 hover:text-gray-900"
      >
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Back
      </button>

      <div v-if="authStore.isAnalyst" class="space-x-2">
        <button
          @click="handleEdit"
          class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
        >
          Edit
        </button>
        <button
          @click="showDeleteConfirm = true"
          class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700"
        >
          Delete
        </button>
      </div>
    </div>

    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
      <div class="p-6">
        <div class="flex items-center justify-between mb-4">
          <div class="flex items-center space-x-3">
            <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-medium">
              {{ eventTypeLabel }}
            </span>
            <span
              v-if="event.verified"
              class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-medium"
            >
              Verified
            </span>
            <span
              class="px-3 py-1 rounded-full text-sm font-medium"
              :class="{
                'bg-green-100 text-green-800': event.confidence_level === 'confirmed',
                'bg-yellow-100 text-yellow-800': event.confidence_level === 'likely',
                'bg-gray-100 text-gray-800': event.confidence_level === 'unconfirmed'
              }"
            >
              {{ event.confidence_level }}
            </span>
          </div>

          <button
            v-if="event.coordinates"
            @click="viewOnMap"
            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300"
          >
            View on Map
          </button>
        </div>

        <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ event.title }}</h1>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6 pb-6 border-b border-gray-200">
          <div>
            <p class="text-sm text-gray-600 mb-1">Occurred At</p>
            <p class="text-gray-900 font-medium">{{ formattedDate }}</p>
          </div>

          <div v-if="event.location_name">
            <p class="text-sm text-gray-600 mb-1">Location</p>
            <p class="text-gray-900 font-medium">{{ event.location_name }}</p>
          </div>

          <div v-if="event.actor">
            <p class="text-sm text-gray-600 mb-1">Actor</p>
            <p class="text-gray-900 font-medium">{{ event.actor.name }}</p>
          </div>

          <div v-if="event.conflict">
            <p class="text-sm text-gray-600 mb-1">Conflict</p>
            <p class="text-gray-900 font-medium">{{ event.conflict.name }}</p>
          </div>
        </div>

        <div v-if="event.description" class="mb-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-2">Description</h2>
          <p class="text-gray-700 whitespace-pre-wrap">{{ event.description }}</p>
        </div>

        <div v-if="event.media_urls && event.media_urls.length > 0" class="mb-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-3">Media</h2>
          <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            <div
              v-for="(url, index) in event.media_urls"
              :key="index"
              class="aspect-square bg-gray-200 rounded-lg overflow-hidden"
            >
              <img :src="url" :alt="`Media ${index + 1}`" class="w-full h-full object-cover" />
            </div>
          </div>
        </div>

        <div v-if="event.source_urls && event.source_urls.length > 0" class="mb-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-3">Sources</h2>
          <div class="space-y-2">
            <a
              v-for="(url, index) in event.source_urls"
              :key="index"
              :href="url"
              target="_blank"
              class="block text-blue-600 hover:underline"
            >
              {{ url }}
            </a>
          </div>
        </div>

        <div class="pt-6 border-t border-gray-200">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600">
            <div>
              <span class="font-medium">Created by:</span>
              <span class="ml-2">{{ event.created_by?.name || 'Unknown' }}</span>
            </div>
            <div>
              <span class="font-medium">Created at:</span>
              <span class="ml-2">{{ new Date(event.created_at).toLocaleString() }}</span>
            </div>
            <div v-if="event.verified_by_id">
              <span class="font-medium">Verified at:</span>
              <span class="ml-2">{{ event.verified_at ? new Date(event.verified_at).toLocaleString() : 'N/A' }}</span>
            </div>
            <div>
              <span class="font-medium">Last updated:</span>
              <span class="ml-2">{{ new Date(event.updated_at).toLocaleString() }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div
      v-if="showDeleteConfirm"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
    >
      <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Confirm Deletion</h3>
        <p class="text-gray-600 mb-6">
          Are you sure you want to delete this event? This action cannot be undone.
        </p>
        <div class="flex justify-end space-x-4">
          <button
            @click="showDeleteConfirm = false"
            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300"
          >
            Cancel
          </button>
          <button
            @click="handleDelete"
            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700"
          >
            Delete
          </button>
        </div>
      </div>
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
