import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { MapState, Event, ControlZone, LegendItem } from '@/types';

export const useMapStore = defineStore('map', () => {
  const center = ref<[number, number]>([48.3794, 31.1656]); // Ukraine default
  const zoom = ref(6);
  const basemap = ref<'osm' | 'satellite' | 'terrain'>('osm');

  const activeLayers = ref({
    events: true,
    zones: true,
    equipment: true,
    heatmap: false
  });

  const selectedEvent = ref<Event | null>(null);
  const selectedZone = ref<ControlZone | null>(null);
  const drawingMode = ref(false);
  const legendItems = ref<LegendItem[]>([]);

  const mapState = computed<MapState>(() => ({
    center: center.value,
    zoom: zoom.value,
    basemap: basemap.value,
    activeLayers: activeLayers.value,
    selectedEvent: selectedEvent.value || undefined,
    selectedZone: selectedZone.value || undefined
  }));

  function setCenter(lat: number, lng: number): void {
    center.value = [lat, lng];
  }

  function setZoom(level: number): void {
    zoom.value = level;
  }

  function setBasemap(type: 'osm' | 'satellite' | 'terrain'): void {
    basemap.value = type;
  }

  function toggleLayer(layer: keyof typeof activeLayers.value): void {
    activeLayers.value[layer] = !activeLayers.value[layer];
  }

  function setLayerVisibility(layer: keyof typeof activeLayers.value, visible: boolean): void {
    activeLayers.value[layer] = visible;
  }

  function selectEvent(event: Event | null): void {
    selectedEvent.value = event;
    if (event && event.coordinates) {
      center.value = [
        event.coordinates.coordinates[1],
        event.coordinates.coordinates[0]
      ];
    }
  }

  function selectZone(zone: ControlZone | null): void {
    selectedZone.value = zone;
  }

  function toggleDrawingMode(): void {
    drawingMode.value = !drawingMode.value;
  }

  function addLegendItem(item: LegendItem): void {
    const existing = legendItems.value.findIndex(i => i.id === item.id && i.type === item.type);
    if (existing === -1) {
      legendItems.value.push(item);
    }
  }

  function removeLegendItem(id: string | number, type: string): void {
    legendItems.value = legendItems.value.filter(
      item => !(item.id === id && item.type === type)
    );
  }

  function toggleLegendItemVisibility(id: string | number, type: string): void {
    const item = legendItems.value.find(i => i.id === id && i.type === type);
    if (item) {
      item.visible = !item.visible;
    }
  }

  function clearLegend(): void {
    legendItems.value = [];
  }

  function flyTo(lat: number, lng: number, zoomLevel?: number): void {
    center.value = [lat, lng];
    if (zoomLevel !== undefined) {
      zoom.value = zoomLevel;
    }
  }

  return {
    center,
    zoom,
    basemap,
    activeLayers,
    selectedEvent,
    selectedZone,
    drawingMode,
    legendItems,
    mapState,
    setCenter,
    setZoom,
    setBasemap,
    toggleLayer,
    setLayerVisibility,
    selectEvent,
    selectZone,
    toggleDrawingMode,
    addLegendItem,
    removeLegendItem,
    toggleLegendItemVisibility,
    clearLegend,
    flyTo
  };
});
