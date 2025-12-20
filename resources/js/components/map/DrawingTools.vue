<script setup lang="ts">
import { ref, watch } from 'vue';
import { useMapStore } from '@/stores/map';
import L from 'leaflet';

const props = defineProps<{
  map: L.Map | null;
}>();

const emit = defineEmits<{
  drawn: [layer: L.Layer];
}>();

const mapStore = useMapStore();
const activeDrawingTool = ref<string | null>(null);
const drawnItems = ref<L.FeatureGroup | null>(null);

const initDrawingTools = () => {
  if (!props.map) return;

  drawnItems.value = new L.FeatureGroup();
  props.map.addLayer(drawnItems.value);

  const drawControl = new L.Control.Draw({
    edit: {
      featureGroup: drawnItems.value,
      remove: true
    },
    draw: {
      polygon: {
        allowIntersection: false,
        showArea: true
      },
      polyline: true,
      rectangle: true,
      circle: false,
      marker: true,
      circlemarker: false
    }
  });

  props.map.addControl(drawControl);

  props.map.on(L.Draw.Event.CREATED, (event: any) => {
    const layer = event.layer;
    drawnItems.value?.addLayer(layer);
    emit('drawn', layer);
    activeDrawingTool.value = null;
  });
};

const startDrawing = (tool: string) => {
  activeDrawingTool.value = tool;
};

const stopDrawing = () => {
  activeDrawingTool.value = null;
};

const clearDrawings = () => {
  drawnItems.value?.clearLayers();
};

watch(() => props.map, (newMap) => {
  if (newMap) {
    initDrawingTools();
  }
});

watch(() => mapStore.drawingMode, (isDrawing) => {
  if (!isDrawing) {
    stopDrawing();
  }
});
</script>

<template>
  <div v-if="mapStore.drawingMode" class="drawing-tools bg-white shadow-lg rounded-lg p-3">
    <h3 class="text-sm font-semibold text-gray-900 mb-3">Drawing Tools</h3>

    <div class="grid grid-cols-2 gap-2">
      <button
        @click="startDrawing('polygon')"
        class="flex flex-col items-center justify-center p-3 border rounded hover:bg-gray-50"
        :class="{ 'bg-blue-50 border-blue-500': activeDrawingTool === 'polygon' }"
      >
        <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
        </svg>
        <span class="text-xs">Polygon</span>
      </button>

      <button
        @click="startDrawing('rectangle')"
        class="flex flex-col items-center justify-center p-3 border rounded hover:bg-gray-50"
        :class="{ 'bg-blue-50 border-blue-500': activeDrawingTool === 'rectangle' }"
      >
        <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v14a1 1 0 01-1 1H5a1 1 0 01-1-1V5z" />
        </svg>
        <span class="text-xs">Rectangle</span>
      </button>

      <button
        @click="startDrawing('polyline')"
        class="flex flex-col items-center justify-center p-3 border rounded hover:bg-gray-50"
        :class="{ 'bg-blue-50 border-blue-500': activeDrawingTool === 'polyline' }"
      >
        <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
        </svg>
        <span class="text-xs">Line</span>
      </button>

      <button
        @click="startDrawing('marker')"
        class="flex flex-col items-center justify-center p-3 border rounded hover:bg-gray-50"
        :class="{ 'bg-blue-50 border-blue-500': activeDrawingTool === 'marker' }"
      >
        <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
        <span class="text-xs">Marker</span>
      </button>
    </div>

    <div class="mt-3 space-y-2">
      <button
        @click="clearDrawings"
        class="w-full px-3 py-2 bg-red-600 text-white text-sm rounded hover:bg-red-700"
      >
        Clear All
      </button>
      <button
        @click="mapStore.toggleDrawingMode"
        class="w-full px-3 py-2 bg-gray-200 text-gray-700 text-sm rounded hover:bg-gray-300"
      >
        Close Tools
      </button>
    </div>
  </div>
</template>

<style scoped>
.drawing-tools {
  position: absolute;
  top: 80px;
  right: 20px;
  width: 200px;
  z-index: 1000;
}
</style>
