<script setup lang="ts">
import { computed } from 'vue';
import { useRouter } from 'vue-router';
import { Event } from '@/types';

const props = defineProps<{
  event: Event;
}>();

const router = useRouter();

const eventTypeLabel = computed(() => {
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
  return labels[props.event.event_type] || props.event.event_type;
});

const eventTypeColor = computed(() => {
  const colors: Record<string, string> = {
    combat_engagement: '#F44336',
    airstrike: '#E91E63',
    artillery_strike: '#9C27B0',
    missile_strike: '#673AB7',
    equipment_destroyed: '#FF5722',
    equipment_captured: '#FF9800',
    equipment_abandoned: '#795548',
    equipment_sighting: '#00BCD4',
    troop_movement: '#009688',
    convoy_spotted: '#8BC34A',
    infrastructure_damage: '#607D8B',
    fortification: '#455A64',
    civilian_casualties: '#B71C1C',
    evacuation: '#1565C0'
  };
  return colors[props.event.event_type] || '#3388ff';
});

const formattedDate = computed(() => {
  return new Date(props.event.occurred_at).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
});

const viewDetails = () => {
  router.push(`/events/${props.event.id}`);
};
</script>

<template>
  <div
    class="event-card bg-white shadow-md rounded-lg p-4 hover:shadow-lg transition-shadow cursor-pointer"
    @click="viewDetails"
  >
    <div class="flex items-start justify-between">
      <div class="flex-1">
        <div class="flex items-center space-x-2 mb-2">
          <div
            class="w-3 h-3 rounded-full"
            :style="{ backgroundColor: eventTypeColor }"
          />
          <span class="text-sm font-medium text-gray-600">{{ eventTypeLabel }}</span>
          <span
            v-if="event.verified"
            class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs font-medium"
          >
            Verified
          </span>
          <span
            class="px-2 py-1 rounded text-xs font-medium"
            :class="{
              'bg-green-100 text-green-800': event.confidence_level === 'confirmed',
              'bg-yellow-100 text-yellow-800': event.confidence_level === 'likely',
              'bg-gray-100 text-gray-800': event.confidence_level === 'unconfirmed'
            }"
          >
            {{ event.confidence_level }}
          </span>
        </div>

        <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ event.title }}</h3>

        <div class="text-sm text-gray-600 space-y-1">
          <div class="flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <span>{{ formattedDate }}</span>
          </div>

          <div v-if="event.location_name" class="flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <span>{{ event.location_name }}</span>
          </div>

          <div v-if="event.actor" class="flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            <span>{{ event.actor.name }}</span>
          </div>
        </div>

        <p v-if="event.description" class="mt-2 text-sm text-gray-700 line-clamp-2">
          {{ event.description }}
        </p>
      </div>

      <div v-if="event.media_urls && event.media_urls.length > 0" class="ml-4">
        <div
          class="w-20 h-20 bg-gray-200 rounded-md bg-cover bg-center"
          :style="{ backgroundImage: `url(${event.media_urls[0]})` }"
        />
      </div>
    </div>

    <div v-if="event.source_urls && event.source_urls.length > 0" class="mt-3 pt-3 border-t border-gray-200">
      <div class="flex items-center text-xs text-gray-500">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
        </svg>
        <span>{{ event.source_urls.length }} source(s)</span>
      </div>
    </div>
  </div>
</template>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
