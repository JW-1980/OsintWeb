<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';
import { useMapStore } from '@/stores/map';
import { useEventsStore } from '@/stores/events';
import { useMap } from '@/composables/useMap';
import { Event } from '@/types';

const mapContainer = ref<HTMLElement | null>(null);
const mapStore = useMapStore();
const eventsStore = useEventsStore();

const { initMap, addEventMarker, setBasemap, flyTo } = useMap(mapContainer, {
  center: mapStore.center,
  zoom: mapStore.zoom
});

const eventMarkers = ref<L.Marker[]>([]);

onMounted(async () => {
  const map = initMap();
  if (!map) return;

  await eventsStore.fetchEvents();

  renderEvents();
});

const renderEvents = () => {
  eventMarkers.value.forEach(marker => marker.remove());
  eventMarkers.value = [];

  if (!mapStore.activeLayers.events) return;

  eventsStore.events.forEach(event => {
    const marker = addEventMarker(event, handleEventClick);
    if (marker) {
      eventMarkers.value.push(marker);
    }
  });
};

const handleEventClick = (event: Event) => {
  mapStore.selectEvent(event);
};

watch(() => mapStore.basemap, (newBasemap) => {
  setBasemap(newBasemap);
});

watch(() => mapStore.activeLayers.events, () => {
  renderEvents();
});

watch(() => mapStore.center, (newCenter) => {
  flyTo(newCenter[0], newCenter[1]);
});
</script>

<template>
  <div class="relative w-full h-full">
    <div ref="mapContainer" class="w-full h-full" />

    <div
      v-if="mapStore.selectedEvent"
      class="absolute top-4 right-4 bg-white shadow-lg rounded-lg p-4 w-80 max-h-96 overflow-y-auto z-[1000]"
    >
      <div class="flex justify-between items-start mb-2">
        <h3 class="text-lg font-semibold">{{ mapStore.selectedEvent.title }}</h3>
        <button
          @click="mapStore.selectEvent(null)"
          class="text-gray-400 hover:text-gray-600"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <div class="space-y-2 text-sm">
        <div>
          <span class="font-medium">Type:</span>
          <span class="ml-2 text-gray-600">{{ mapStore.selectedEvent.event_type }}</span>
        </div>
        <div>
          <span class="font-medium">Date:</span>
          <span class="ml-2 text-gray-600">
            {{ new Date(mapStore.selectedEvent.occurred_at).toLocaleString() }}
          </span>
        </div>
        <div v-if="mapStore.selectedEvent.actor">
          <span class="font-medium">Actor:</span>
          <span class="ml-2 text-gray-600">{{ mapStore.selectedEvent.actor.name }}</span>
        </div>
        <div v-if="mapStore.selectedEvent.description">
          <span class="font-medium">Description:</span>
          <p class="mt-1 text-gray-600">{{ mapStore.selectedEvent.description }}</p>
        </div>
        <div v-if="mapStore.selectedEvent.location_name">
          <span class="font-medium">Location:</span>
          <span class="ml-2 text-gray-600">{{ mapStore.selectedEvent.location_name }}</span>
        </div>
        <div>
          <span class="font-medium">Confidence:</span>
          <span class="ml-2 px-2 py-1 rounded text-xs"
            :class="{
              'bg-green-100 text-green-800': mapStore.selectedEvent.confidence_level === 'confirmed',
              'bg-yellow-100 text-yellow-800': mapStore.selectedEvent.confidence_level === 'likely',
              'bg-gray-100 text-gray-800': mapStore.selectedEvent.confidence_level === 'unconfirmed'
            }"
          >
            {{ mapStore.selectedEvent.confidence_level }}
          </span>
        </div>
        <div v-if="mapStore.selectedEvent.verified">
          <span class="font-medium">Status:</span>
          <span class="ml-2 px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs">Verified</span>
        </div>
      </div>

      <div class="mt-4">
        <router-link
          :to="`/events/${mapStore.selectedEvent.id}`"
          class="block w-full text-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
        >
          View Details
        </router-link>
      </div>
    </div>
  </div>
</template>

<style>
.leaflet-container {
  font-family: inherit;
}
</style>
