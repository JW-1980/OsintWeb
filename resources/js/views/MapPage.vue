<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import AppSidebar from '@/components/layout/AppSidebar.vue';
import MapView from '@/components/map/MapView.vue';
import MapLegend from '@/components/map/MapLegend.vue';
import DrawingTools from '@/components/map/DrawingTools.vue';
import TimelineBar from '@/components/timeline/TimelineBar.vue';
import { useMapStore } from '@/stores/map';
import { useAuthStore } from '@/stores/auth';

const mapStore = useMapStore();
const authStore = useAuthStore();

// Only admins and analysts can draw on the map
const canDraw = computed(() => authStore.isAdmin || authStore.isAnalyst);
const showTimeline = ref(false);
const timelineStartDate = ref(new Date('2022-02-24'));
const timelineEndDate = ref(new Date());
const timelineCurrentDate = ref(new Date());

const toggleDrawingMode = () => {
  mapStore.toggleDrawingMode();
};

const toggleTimeline = () => {
  showTimeline.value = !showTimeline.value;
};

const handleTimelineUpdate = (date: Date) => {
  timelineCurrentDate.value = date;
};

onMounted(() => {
  console.log('Map page mounted');
});
</script>

<template>
  <div class="map-page flex">
    <AppSidebar class="hidden md:flex" />

    <div class="flex-1 relative min-h-0">
      <div class="absolute top-4 left-4 z-[1000] space-x-2">
        <!-- Drawing button - only visible to admins and analysts -->
        <button
          v-if="canDraw"
          @click="toggleDrawingMode"
          :class="{
            'bg-blue-600 text-white': mapStore.drawingMode,
            'bg-white text-gray-700': !mapStore.drawingMode
          }"
          class="px-4 py-2 shadow-lg rounded-md hover:bg-blue-700 hover:text-white transition-colors"
        >
          <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
          </svg>
          {{ mapStore.drawingMode ? 'Stop Drawing' : 'Start Drawing' }}
        </button>

        <button
          @click="toggleTimeline"
          :class="{
            'bg-purple-600 text-white': showTimeline,
            'bg-white text-gray-700': !showTimeline
          }"
          class="px-4 py-2 shadow-lg rounded-md hover:bg-purple-700 hover:text-white transition-colors"
        >
          <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          {{ showTimeline ? 'Hide Timeline' : 'Show Timeline' }}
        </button>
      </div>

      <MapView />

      <MapLegend />

      <DrawingTools :map="null" />

      <div v-if="showTimeline" class="absolute bottom-4 left-4 right-4 z-[1000]">
        <TimelineBar
          :start-date="timelineStartDate"
          :end-date="timelineEndDate"
          :current-date="timelineCurrentDate"
          @update:current-date="handleTimelineUpdate"
        />
      </div>
    </div>
  </div>
</template>

<style scoped>
.map-page {
  height: calc(100vh - 64px);
  height: calc(100dvh - 64px); /* Dynamic viewport height for mobile */
}

@media (max-width: 767px) {
  .map-page {
    /* Full height on mobile without sidebar offset */
    height: calc(100vh - 64px);
    height: calc(100dvh - 64px);
  }
}
</style>
