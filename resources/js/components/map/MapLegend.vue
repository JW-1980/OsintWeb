<script setup lang="ts">
import { ref } from 'vue';
import { useMapStore } from '@/stores/map';

const mapStore = useMapStore();
const isCollapsed = ref(false);

const toggleLegend = () => {
  isCollapsed.value = !isCollapsed.value;
};

const toggleItemVisibility = (id: string | number, type: string) => {
  mapStore.toggleLegendItemVisibility(id, type);
};
</script>

<template>
  <div class="map-legend bg-white shadow-lg rounded-lg overflow-hidden">
    <div
      class="legend-header flex items-center justify-between p-3 bg-gray-100 cursor-pointer"
      @click="toggleLegend"
    >
      <h3 class="text-sm font-semibold text-gray-900">Legend</h3>
      <svg
        class="w-5 h-5 transition-transform"
        :class="{ 'rotate-180': isCollapsed }"
        fill="none"
        stroke="currentColor"
        viewBox="0 0 24 24"
      >
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
      </svg>
    </div>

    <div v-if="!isCollapsed" class="legend-content p-3">
      <div v-if="mapStore.legendItems.length === 0" class="text-sm text-gray-500 text-center py-4">
        No items in legend
      </div>

      <div v-else class="space-y-2">
        <div
          v-for="item in mapStore.legendItems"
          :key="`${item.type}-${item.id}`"
          class="flex items-center justify-between hover:bg-gray-50 p-2 rounded cursor-pointer"
          @click="toggleItemVisibility(item.id, item.type)"
        >
          <div class="flex items-center space-x-2">
            <div
              class="w-4 h-4 rounded"
              :style="{ backgroundColor: item.color }"
            />
            <span class="text-sm text-gray-700">{{ item.name }}</span>
          </div>
          <input
            type="checkbox"
            :checked="item.visible"
            class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
            @click.stop="toggleItemVisibility(item.id, item.type)"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.map-legend {
  position: absolute;
  bottom: 20px;
  left: 20px;
  max-width: 300px;
  z-index: 1000;
}

.rotate-180 {
  transform: rotate(180deg);
}
</style>
