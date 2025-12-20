<script setup lang="ts">
import { computed } from 'vue';
import { Event } from '@/types';

const props = defineProps<{
  event: Event;
}>();

const emit = defineEmits<{
  click: [event: Event];
}>();

const markerColor = computed(() => {
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

const handleClick = () => {
  emit('click', props.event);
};
</script>

<template>
  <div
    class="event-marker"
    :style="{ backgroundColor: markerColor }"
    @click="handleClick"
  />
</template>

<style scoped>
.event-marker {
  width: 20px;
  height: 20px;
  border-radius: 50%;
  border: 2px solid white;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
  cursor: pointer;
  transition: transform 0.2s;
}

.event-marker:hover {
  transform: scale(1.2);
}
</style>
