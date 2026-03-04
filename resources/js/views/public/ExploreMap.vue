<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <!-- Public Navigation -->
    <nav class="fixed top-0 left-0 right-0 z-50 bg-white/80 dark:bg-gray-900/80 backdrop-blur-md border-b border-gray-200 dark:border-gray-800">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
          <div class="flex items-center space-x-2">
            <router-link to="/" class="flex items-center space-x-2">
              <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                </svg>
              </div>
              <span class="text-xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">OsintWeb</span>
            </router-link>
          </div>
          <div class="hidden md:flex items-center space-x-6">
            <router-link to="/explore" class="text-blue-600 dark:text-blue-400 font-medium">Map</router-link>
            <router-link to="/explore/events" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors">Events</router-link>
            <router-link to="/explore/equipment" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors">Equipment</router-link>
            <router-link to="/explore/actors" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors">Actors</router-link>
            <router-link to="/explore/conflicts" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors">Conflicts</router-link>
          </div>
          <div class="flex items-center space-x-4">
            <button @click="toggleTheme" class="p-2 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
              <svg v-if="isDark" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
              </svg>
              <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
              </svg>
            </button>
            <router-link to="/login" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors">Login</router-link>
            <router-link to="/register" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-medium rounded-lg transition-all">
              Sign Up
            </router-link>
          </div>
        </div>
      </div>
    </nav>

    <!-- Main Content -->
    <div class="pt-16 flex h-[calc(100vh-64px)] md:h-[calc(100dvh-64px)]">
      <!-- Sidebar - Fixed position on mobile for overlay behavior -->
      <aside
        :class="[
          'bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 flex flex-col transition-all duration-300 z-40',
          'fixed md:relative top-16 md:top-0 left-0 h-[calc(100vh-64px)] md:h-full w-80',
          sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'
        ]"
      >
        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Explore Map</h2>
          <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Verified events and control zones</p>
        </div>

        <!-- Filters -->
        <div class="p-4 space-y-4 flex-1 overflow-y-auto">
          <!-- Event Type Filter -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Event Type</label>
            <select
              v-model="filters.eventType"
              class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="">All Types</option>
              <option value="combat_engagement">Combat Engagement</option>
              <option value="airstrike">Airstrike</option>
              <option value="artillery_strike">Artillery Strike</option>
              <option value="missile_strike">Missile Strike</option>
              <option value="equipment_destroyed">Equipment Destroyed</option>
              <option value="troop_movement">Troop Movement</option>
            </select>
          </div>

          <!-- Date Range -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date Range</label>
            <div class="space-y-2">
              <input
                v-model="filters.dateFrom"
                type="date"
                class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm"
                placeholder="From"
              />
              <input
                v-model="filters.dateTo"
                type="date"
                class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm"
                placeholder="To"
              />
            </div>
          </div>

          <!-- Layers Toggle -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Map Layers</label>
            <div class="space-y-2">
              <label class="flex items-center space-x-3 cursor-pointer">
                <input type="checkbox" v-model="layers.events" class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500" />
                <span class="text-sm text-gray-700 dark:text-gray-300">Event Markers</span>
              </label>
              <label class="flex items-center space-x-3 cursor-pointer">
                <input type="checkbox" v-model="layers.zones" class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500" />
                <span class="text-sm text-gray-700 dark:text-gray-300">Control Zones</span>
              </label>
            </div>
          </div>

          <!-- Legend -->
          <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Legend</h3>
            <div class="space-y-2">
              <div class="flex items-center space-x-2">
                <span class="w-4 h-4 rounded-full bg-red-500"></span>
                <span class="text-sm text-gray-600 dark:text-gray-400">Combat Engagement</span>
              </div>
              <div class="flex items-center space-x-2">
                <span class="w-4 h-4 rounded-full bg-pink-500"></span>
                <span class="text-sm text-gray-600 dark:text-gray-400">Airstrike</span>
              </div>
              <div class="flex items-center space-x-2">
                <span class="w-4 h-4 rounded-full bg-purple-500"></span>
                <span class="text-sm text-gray-600 dark:text-gray-400">Artillery Strike</span>
              </div>
              <div class="flex items-center space-x-2">
                <span class="w-4 h-4 rounded-full bg-indigo-500"></span>
                <span class="text-sm text-gray-600 dark:text-gray-400">Missile Strike</span>
              </div>
              <div class="flex items-center space-x-2">
                <span class="w-4 h-4 rounded-full bg-orange-500"></span>
                <span class="text-sm text-gray-600 dark:text-gray-400">Equipment Destroyed</span>
              </div>
              <div class="flex items-center space-x-2">
                <span class="w-4 h-4 rounded-full bg-teal-500"></span>
                <span class="text-sm text-gray-600 dark:text-gray-400">Troop Movement</span>
              </div>
            </div>

            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mt-4 mb-3">Control Zones</h4>
            <div class="space-y-2">
              <div class="flex items-center space-x-2">
                <span class="w-4 h-4 bg-blue-500/50 border border-blue-600"></span>
                <span class="text-sm text-gray-600 dark:text-gray-400">Full Control</span>
              </div>
              <div class="flex items-center space-x-2">
                <span class="w-4 h-4 bg-yellow-500/50 border border-yellow-600"></span>
                <span class="text-sm text-gray-600 dark:text-gray-400">Contested</span>
              </div>
              <div class="flex items-center space-x-2">
                <span class="w-4 h-4 bg-gray-500/50 border border-gray-600"></span>
                <span class="text-sm text-gray-600 dark:text-gray-400">Claimed</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Sidebar Footer -->
        <div class="p-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
          <router-link
            to="/submit-tip"
            class="w-full flex items-center justify-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors"
          >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Submit a Tip
          </router-link>
        </div>
      </aside>

      <!-- Map Container -->
      <div class="flex-1 relative min-h-0 w-full">
        <!-- Mobile Sidebar Toggle -->
        <button
          @click="sidebarOpen = !sidebarOpen"
          class="md:hidden absolute top-4 left-4 z-[1001] p-2 bg-white dark:bg-gray-800 rounded-lg shadow-lg"
        >
          <svg class="w-6 h-6 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>

        <!-- Mobile Sidebar Overlay/Backdrop -->
        <div
          v-if="sidebarOpen"
          class="md:hidden fixed inset-0 bg-black/50 z-30"
          @click="sidebarOpen = false"
        ></div>

        <!-- Map Placeholder -->
        <div class="absolute inset-0 bg-gray-200 dark:bg-gray-700 flex items-center justify-center" ref="mapContainer">
          <div v-if="loading" class="text-center">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
            <p class="mt-4 text-gray-600 dark:text-gray-300">Loading map...</p>
          </div>
          <div v-else class="text-center p-8">
            <svg class="w-24 h-24 mx-auto text-gray-400 dark:text-gray-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
            </svg>
            <p class="text-xl font-medium text-gray-600 dark:text-gray-300">Interactive Map</p>
            <p class="text-gray-500 dark:text-gray-400 mt-2">{{ filteredEvents.length }} verified events</p>
            <p class="text-gray-500 dark:text-gray-400">{{ zones.length }} control zones</p>
          </div>
        </div>

        <!-- Event Info Panel -->
        <div
          v-if="selectedEvent"
          class="absolute bottom-4 left-4 right-4 md:left-auto md:right-4 md:w-96 bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 p-4 z-[1000]"
        >
          <div class="flex items-start justify-between mb-3">
            <div>
              <span :class="getEventBadgeClass(selectedEvent.event_type)" class="px-2 py-1 text-xs font-medium rounded-full">
                {{ formatEventType(selectedEvent.event_type) }}
              </span>
              <span class="ml-2 px-2 py-1 text-xs font-medium rounded-full bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400">
                Verified
              </span>
            </div>
            <button @click="selectedEvent = null" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">{{ selectedEvent.title }}</h3>
          <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">{{ selectedEvent.location_name }}</p>
          <p class="text-sm text-gray-600 dark:text-gray-300 mb-4 line-clamp-3">{{ selectedEvent.description }}</p>
          <div class="flex items-center justify-between">
            <span class="text-xs text-gray-500 dark:text-gray-400">{{ formatDate(selectedEvent.occurred_at) }}</span>
            <router-link
              :to="{ name: 'explore-event-detail', params: { id: selectedEvent.id } }"
              class="text-sm text-blue-600 dark:text-blue-400 hover:underline"
            >
              View Details
            </router-link>
          </div>
        </div>

        <!-- Map Stats Overlay -->
        <div class="absolute top-4 right-4 bg-white dark:bg-gray-800 rounded-lg shadow-lg p-3 z-[1000]">
          <div class="grid grid-cols-2 gap-4 text-center">
            <div>
              <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ filteredEvents.length }}</p>
              <p class="text-xs text-gray-500 dark:text-gray-400">Events</p>
            </div>
            <div>
              <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ zones.length }}</p>
              <p class="text-xs text-gray-500 dark:text-gray-400">Zones</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted, onUnmounted, watch } from 'vue';
import { useThemeStore } from '@/stores/theme';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

const themeStore = useThemeStore();
const isDark = computed(() => themeStore.isDark);

const toggleTheme = () => {
  themeStore.toggleTheme();
};

const loading = ref(false);
const sidebarOpen = ref(false);
const mapContainer = ref<HTMLElement | null>(null);
let map: L.Map | null = null;
let eventLayer: L.LayerGroup | null = null;
let zoneLayer: L.LayerGroup | null = null;

interface MapEvent {
  id: number;
  title: string;
  event_type: string;
  description: string;
  location_name: string;
  occurred_at: string;
  coordinates: [number, number];
  verified: boolean;
}

interface ControlZone {
  id: number;
  name: string;
  controller: string;
  control_type: 'full' | 'contested' | 'claimed';
}

const filters = reactive({
  eventType: '',
  dateFrom: '',
  dateTo: ''
});

const layers = reactive({
  events: true,
  zones: true
});

const selectedEvent = ref<MapEvent | null>(null);

const events = ref<MapEvent[]>([
  { id: 1, title: 'Artillery strike on military positions', event_type: 'artillery_strike', description: 'Multiple MLRS strikes reported targeting defensive positions near the front line.', location_name: 'Zaporizhzhia Oblast', occurred_at: '2024-01-16T14:30:00Z', coordinates: [47.838, 35.139], verified: true },
  { id: 2, title: 'Air defense engagement', event_type: 'airstrike', description: 'Air defense systems engaged incoming cruise missiles.', location_name: 'Kyiv Oblast', occurred_at: '2024-01-16T06:15:00Z', coordinates: [50.450, 30.523], verified: true },
  { id: 3, title: 'Tank destroyed by FPV drone', event_type: 'equipment_destroyed', description: 'T-72B3 main battle tank destroyed by first-person view drone strike.', location_name: 'Donetsk Oblast', occurred_at: '2024-01-15T18:45:00Z', coordinates: [48.015, 37.803], verified: true },
  { id: 4, title: 'Missile strike on infrastructure', event_type: 'missile_strike', description: 'Cruise missile struck energy infrastructure, causing power outages.', location_name: 'Kharkiv', occurred_at: '2024-01-14T22:30:00Z', coordinates: [49.988, 36.233], verified: true },
  { id: 5, title: 'Combat engagement near Bakhmut', event_type: 'combat_engagement', description: 'Infantry clashes reported in the suburbs with both sides claiming territorial gains.', location_name: 'Bakhmut', occurred_at: '2024-01-14T16:20:00Z', coordinates: [48.595, 37.999], verified: true }
]);

const zones = ref<ControlZone[]>([
  { id: 1, name: 'Northern Sector A', controller: 'Force Alpha', control_type: 'full' },
  { id: 2, name: 'Central Contested Zone', controller: 'Disputed', control_type: 'contested' },
  { id: 3, name: 'Southern Region', controller: 'Force Beta', control_type: 'full' }
]);

const filteredEvents = computed(() => {
  return events.value.filter(event => {
    if (!event.verified) return false;
    if (filters.eventType && event.event_type !== filters.eventType) return false;
    if (filters.dateFrom && new Date(event.occurred_at) < new Date(filters.dateFrom)) return false;
    if (filters.dateTo && new Date(event.occurred_at) > new Date(filters.dateTo)) return false;
    return true;
  });
});

const getEventBadgeClass = (type: string) => {
  const classes: Record<string, string> = {
    'combat_engagement': 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400',
    'airstrike': 'bg-pink-100 dark:bg-pink-900/30 text-pink-700 dark:text-pink-400',
    'artillery_strike': 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400',
    'missile_strike': 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400',
    'equipment_destroyed': 'bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400',
    'troop_movement': 'bg-teal-100 dark:bg-teal-900/30 text-teal-700 dark:text-teal-400'
  };
  return classes[type] || 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-400';
};

const formatEventType = (type: string) => {
  if (!type) return 'Unknown';
  return String(type).split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
};

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

const initMap = () => {
  if (!mapContainer.value) return;

  // Center on Ukraine for demo
  map = L.map(mapContainer.value).setView([49.0, 32.0], 6);

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
  }).addTo(map);

  eventLayer = L.layerGroup().addTo(map);
  zoneLayer = L.layerGroup().addTo(map);

  updateLayers();
};

const updateLayers = () => {
  if (!map || !eventLayer || !zoneLayer) return;

  eventLayer.clearLayers();
  zoneLayer.clearLayers();

  if (layers.events) {
    filteredEvents.value.forEach(event => {
      const color = getEventColor(event.event_type);
      const marker = L.circleMarker(event.coordinates, {
        radius: 8,
        fillColor: color,
        color: '#fff',
        weight: 1,
        opacity: 1,
        fillOpacity: 0.8
      }).addTo(eventLayer!);

      marker.on('click', () => {
        selectedEvent.value = event;
      });
    });
  }

  // Add zones if needed (implementation omitted for brevity/demo data compatibility)
};

const getEventColor = (type: string) => {
  const colors: Record<string, string> = {
    'combat_engagement': '#ef4444',
    'airstrike': '#ec4899',
    'artillery_strike': '#a855f7',
    'missile_strike': '#6366f1',
    'equipment_destroyed': '#f97316',
    'troop_movement': '#14b8a6'
  };
  return colors[type] || '#6b7280';
};

watch([filteredEvents, layers], () => {
  updateLayers();
}, { deep: true });

onMounted(() => {
  loading.value = true;
  // Simulate loading
  setTimeout(() => {
    loading.value = false;
    // Initialize map after loading and DOM update
    setTimeout(() => {
      initMap();
    }, 100);
  }, 500);
});

onUnmounted(() => {
  if (map) {
    map.remove();
    map = null;
  }
});
</script>

<style scoped>
.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
