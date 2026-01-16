<script setup lang="ts">
import { ref } from 'vue';
import { useMapStore } from '@/stores/map';
import { EventType } from '@/types';

const mapStore = useMapStore();

const showLayers = ref(true);
const showFilters = ref(true);

const selectedEventTypes = ref<EventType[]>([]);
const selectedDateRange = ref({
  from: '',
  to: ''
});

const eventTypes: { value: EventType; label: string; color: string }[] = [
  { value: 'combat_engagement', label: 'Combat Engagement', color: '#F44336' },
  { value: 'airstrike', label: 'Airstrike', color: '#E91E63' },
  { value: 'artillery_strike', label: 'Artillery Strike', color: '#9C27B0' },
  { value: 'missile_strike', label: 'Missile Strike', color: '#673AB7' },
  { value: 'equipment_destroyed', label: 'Equipment Destroyed', color: '#FF5722' },
  { value: 'equipment_captured', label: 'Equipment Captured', color: '#FF9800' },
  { value: 'equipment_abandoned', label: 'Equipment Abandoned', color: '#795548' },
  { value: 'equipment_sighting', label: 'Equipment Sighting', color: '#00BCD4' },
  { value: 'troop_movement', label: 'Troop Movement', color: '#009688' },
  { value: 'convoy_spotted', label: 'Convoy Spotted', color: '#8BC34A' },
  { value: 'infrastructure_damage', label: 'Infrastructure Damage', color: '#607D8B' },
  { value: 'fortification', label: 'Fortification', color: '#455A64' },
  { value: 'civilian_casualties', label: 'Civilian Casualties', color: '#B71C1C' },
  { value: 'evacuation', label: 'Evacuation', color: '#1565C0' }
];

const toggleEventType = (eventType: EventType) => {
  const index = selectedEventTypes.value.indexOf(eventType);
  if (index > -1) {
    selectedEventTypes.value.splice(index, 1);
  } else {
    selectedEventTypes.value.push(eventType);
  }
};

const isEventTypeSelected = (eventType: EventType) => {
  return selectedEventTypes.value.includes(eventType);
};

const clearFilters = () => {
  selectedEventTypes.value = [];
  selectedDateRange.value = { from: '', to: '' };
};
</script>

<template>
  <aside class="w-80 bg-white shadow-lg border-r border-gray-200 h-full overflow-y-auto">
    <div class="p-4">
      <div class="mb-6">
        <div
          class="flex items-center justify-between cursor-pointer"
          @click="showLayers = !showLayers"
        >
          <h2 class="text-lg font-semibold text-gray-900">Map Layers</h2>
          <svg
            class="w-5 h-5 transition-transform"
            :class="{ 'rotate-180': !showLayers }"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
          </svg>
        </div>

        <div v-if="showLayers" class="mt-4 space-y-3">
          <label class="flex items-center">
            <input
              type="checkbox"
              :checked="mapStore.activeLayers.events"
              @change="mapStore.toggleLayer('events')"
              class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
            />
            <span class="ml-2 text-sm text-gray-700">Events</span>
          </label>

          <label class="flex items-center">
            <input
              type="checkbox"
              :checked="mapStore.activeLayers.zones"
              @change="mapStore.toggleLayer('zones')"
              class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
            />
            <span class="ml-2 text-sm text-gray-700">Control Zones</span>
          </label>

          <label class="flex items-center">
            <input
              type="checkbox"
              :checked="mapStore.activeLayers.equipment"
              @change="mapStore.toggleLayer('equipment')"
              class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
            />
            <span class="ml-2 text-sm text-gray-700">Equipment</span>
          </label>

          <label class="flex items-center">
            <input
              type="checkbox"
              :checked="mapStore.activeLayers.heatmap"
              @change="mapStore.toggleLayer('heatmap')"
              class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
            />
            <span class="ml-2 text-sm text-gray-700">Heatmap</span>
          </label>
        </div>
      </div>

      <div class="mb-6">
        <div class="mb-3">
          <label class="block text-sm font-medium text-gray-700 mb-2">Base Map</label>
          <select
            :value="mapStore.basemap"
            @change="(e) => mapStore.setBasemap((e.target as HTMLSelectElement).value as any)"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="osm">OpenStreetMap</option>
            <option value="satellite">Satellite</option>
            <option value="terrain">Terrain</option>
          </select>
        </div>
      </div>

      <div class="border-t border-gray-200 pt-6">
        <div
          class="flex items-center justify-between cursor-pointer"
          @click="showFilters = !showFilters"
        >
          <h2 class="text-lg font-semibold text-gray-900">Filters</h2>
          <svg
            class="w-5 h-5 transition-transform"
            :class="{ 'rotate-180': !showFilters }"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
          </svg>
        </div>

        <div v-if="showFilters" class="mt-4 space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Event Types</label>
            <div class="space-y-2 max-h-64 overflow-y-auto">
              <label
                v-for="eventType in eventTypes"
                :key="eventType.value"
                class="flex items-center"
              >
                <input
                  type="checkbox"
                  :checked="isEventTypeSelected(eventType.value)"
                  @change="toggleEventType(eventType.value)"
                  class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                />
                <span
                  class="ml-2 w-3 h-3 rounded-full"
                  :style="{ backgroundColor: eventType.color }"
                />
                <span class="ml-2 text-sm text-gray-700">{{ eventType.label }}</span>
              </label>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Date Range</label>
            <div class="space-y-2">
              <input
                v-model="selectedDateRange.from"
                type="date"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="From"
              />
              <input
                v-model="selectedDateRange.to"
                type="date"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="To"
              />
            </div>
          </div>

          <button
            @click="clearFilters"
            class="w-full px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500"
          >
            Clear Filters
          </button>
        </div>
      </div>
    </div>
  </aside>
</template>

<style scoped>
.rotate-180 {
  transform: rotate(180deg);
}
</style>
