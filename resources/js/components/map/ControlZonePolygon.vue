<script setup lang="ts">
import { computed } from 'vue';
import { ControlZone } from '@/types';

const props = defineProps<{
  zone: ControlZone;
}>();

const emit = defineEmits<{
  click: [zone: ControlZone];
}>();

const zoneColor = computed(() => {
  return props.zone.controller?.color_hex || '#3388ff';
});

const fillOpacity = computed(() => {
  switch (props.zone.control_type) {
    case 'contested':
      return 0.3;
    case 'claimed':
      return 0.4;
    case 'full':
    default:
      return 0.5;
  }
});

const strokePattern = computed(() => {
  if (props.zone.control_type === 'contested') {
    return 'dashed';
  } else if (props.zone.control_type === 'claimed') {
    return 'dotted';
  }
  return 'solid';
});

const handleClick = () => {
  emit('click', props.zone);
};
</script>

<template>
  <div
    class="control-zone-polygon"
    :style="{
      fill: zoneColor,
      fillOpacity: fillOpacity,
      stroke: zoneColor,
      strokeDasharray: strokePattern === 'dashed' ? '5,5' : strokePattern === 'dotted' ? '2,2' : 'none'
    }"
    @click="handleClick"
  />
</template>

<style scoped>
.control-zone-polygon {
  cursor: pointer;
  stroke-width: 2;
  transition: fill-opacity 0.2s;
}

.control-zone-polygon:hover {
  fill-opacity: 0.7;
}
</style>
