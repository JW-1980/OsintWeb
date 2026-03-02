<template>

<header class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 bg-white/50 dark:bg-background-dark/50 backdrop-blur-md px-6 py-3 shrink-0 z-50">
<div class="flex items-center gap-4">
<div class="size-8 bg-primary rounded-lg flex items-center justify-center text-white">
<span class="material-symbols-outlined">radar</span>
</div>
<h2 class="text-lg font-bold tracking-tight">OSINT Tactical Tracker</h2>
</div>
<div class="hidden md:flex items-center gap-8">
<a class="text-sm font-medium hover:text-primary transition-colors" href="#">Dashboard</a>
<a class="text-sm font-bold text-primary" href="#">Explore Map</a>
<a class="text-sm font-medium hover:text-primary transition-colors" href="#">Intelligence</a>
<a class="text-sm font-medium hover:text-primary transition-colors" href="#">Reports</a>
<a class="text-sm font-medium hover:text-primary transition-colors" href="#">Assets</a>
</div>
<div class="flex items-center gap-3">
<button class="p-2 rounded-lg bg-slate-200 dark:bg-slate-800 hover:bg-primary/20 transition-colors">
<span class="material-symbols-outlined text-xl">notifications</span>
</button>
<button class="p-2 rounded-lg bg-slate-200 dark:bg-slate-800 hover:bg-primary/20 transition-colors">
<span class="material-symbols-outlined text-xl">account_circle</span>
</button>
</div>
</header>
<main class="relative flex flex-1 overflow-hidden">
<div class="absolute inset-0 z-0 bg-slate-900" data-alt="High contrast dark satellite map with tactical overlays and markers" data-location="Eastern Europe Border Region" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuBFtKpsHM6ojcKhqRPzv23RpUQzeeeXagEK1mqOHnpF0tdMKc3zEffEfpxntuZmmG1qEym94gAKRvZz8Q0heLZTFXjjqhYanTVkGB2ZjEaA0UnePWDxAFDjF37f3jC5WRGntyt93_sEry_S-70Ix-mJRVM8SMCIgWC-guWHz40Qvb_mT1P0gCf9W8SrCCwb5D7aKaIoIjy1oMk1-paiA1sB0MOvn6vZLSjZFFfK3RMCkVksogjQuNAb-hMnSf33C1um7EX26e3kpxJm'); background-size: cover;">
<div class="absolute inset-0 bg-gradient-to-t from-background-dark/40 to-transparent pointer-events-none"></div>
<div class="absolute top-[30%] left-[45%] group cursor-pointer">
<div class="relative">
<div class="absolute -inset-2 bg-red-500/20 rounded-full blur animate-pulse"></div>
<span class="material-symbols-outlined text-red-500 text-3xl drop-shadow-[0_0_8px_rgba(239,68,68,0.8)]">target</span>
</div>
</div>
<div class="absolute top-[50%] left-[60%] group cursor-pointer">
<div class="relative">
<span class="material-symbols-outlined text-primary text-3xl drop-shadow-[0_0_8px_rgba(19,91,236,0.8)]">shield</span>
</div>
</div>
<div class="absolute top-[45%] left-[52%] group cursor-pointer">
<div class="relative">
<span class="material-symbols-outlined text-yellow-500 text-2xl drop-shadow-[0_0_8px_rgba(234,179,8,0.8)]">help_center</span>
</div>
</div>
</div>
<div class="absolute top-6 left-1/2 -translate-x-1/2 w-full max-w-xl px-4 z-40">
<div class="flex items-center bg-white/10 dark:bg-slate-900/80 backdrop-blur-xl rounded-xl border border-slate-200/20 dark:border-slate-700/50 shadow-2xl p-1">
<div class="flex items-center pl-4 text-slate-400">
<span class="material-symbols-outlined">search</span>
</div>
<input class="w-full bg-transparent border-none focus:ring-0 text-slate-100 placeholder:text-slate-500 px-3 py-2" placeholder="Location, Coordinates, or Keywords" type="text"/>
<button class="bg-primary hover:bg-primary/90 text-white px-4 py-2 rounded-lg text-sm font-bold flex items-center gap-2 transition-all">
<span>Search</span>
</button>
</div>
</div>
<aside class="absolute top-4 bottom-4 left-4 w-80 bg-white/10 dark:bg-slate-900/80 backdrop-blur-xl border border-slate-200/20 dark:border-slate-700/50 rounded-xl flex flex-col z-40 overflow-hidden shadow-2xl">
<div class="p-4 border-b border-slate-700/50 flex justify-between items-center">
<h3 class="font-bold flex items-center gap-2">
<span class="material-symbols-outlined text-primary">filter_list</span>
                    Tactical Filters
                </h3>
<button class="text-xs text-slate-400 hover:text-white underline">Clear all</button>
</div>
<div class="flex-1 overflow-y-auto p-4 space-y-6">
<div>
<label class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-3 block">Date Range</label>
<div class="space-y-3">
<div class="bg-slate-800/50 rounded-lg p-2 border border-slate-700/50">
<div class="flex justify-between text-xs mb-1">
<span class="text-slate-400">Oct 05</span>
<span class="text-white font-medium">Oct 30, 2023</span>
</div>
<input class="w-full h-1 bg-slate-700 rounded-lg appearance-none cursor-pointer accent-primary" type="range"/>
</div>
</div>
</div>
<div>
<label class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-3 block">Event Types</label>
<div class="space-y-2">
<label class="flex items-center gap-3 cursor-pointer group">
<input checked="" class="rounded border-slate-700 bg-slate-800 text-primary focus:ring-primary focus:ring-offset-slate-900" type="checkbox"/>
<span class="text-sm text-slate-300 group-hover:text-white">Airstrike / Artillery</span>
</label>
<label class="flex items-center gap-3 cursor-pointer group">
<input checked="" class="rounded border-slate-700 bg-slate-800 text-primary focus:ring-primary focus:ring-offset-slate-900" type="checkbox"/>
<span class="text-sm text-slate-300 group-hover:text-white">Troop Movement</span>
</label>
<label class="flex items-center gap-3 cursor-pointer group">
<input class="rounded border-slate-700 bg-slate-800 text-primary focus:ring-primary focus:ring-offset-slate-900" type="checkbox"/>
<span class="text-sm text-slate-300 group-hover:text-white">Cyber Attack</span>
</label>
<label class="flex items-center gap-3 cursor-pointer group">
<input class="rounded border-slate-700 bg-slate-800 text-primary focus:ring-primary focus:ring-offset-slate-900" type="checkbox"/>
<span class="text-sm text-slate-300 group-hover:text-white">Infrastructure Damage</span>
</label>
</div>
</div>
<div class="space-y-4">
<div>
<label class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-2 block">Conflict Zone</label>
<select class="w-full bg-slate-800 border-slate-700 rounded-lg text-sm text-white py-2 focus:ring-primary">
<option>Eastern Front</option>
<option>Middle East Southern</option>
<option>Sahel Region</option>
</select>
</div>
<div>
<label class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-2 block">Key Actors</label>
<select class="w-full bg-slate-800 border-slate-700 rounded-lg text-sm text-white py-2 focus:ring-primary">
<option>All Factions</option>
<option>State Forces</option>
<option>Coalition A</option>
</select>
</div>
<div>
<label class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-2 block">Equipment Class</label>
<select class="w-full bg-slate-800 border-slate-700 rounded-lg text-sm text-white py-2 focus:ring-primary">
<option>Armored Vehicles</option>
<option>Fixed-wing Aviation</option>
<option>Drones / UAV</option>
</select>
</div>
</div>
</div>
<div class="p-4 bg-slate-800/40 border-t border-slate-700/50">
<button class="w-full bg-primary py-2.5 rounded-lg text-white text-sm font-bold shadow-lg shadow-primary/20 hover:shadow-primary/40 transition-all">
                    Apply Advanced Filters
                </button>
</div>
</aside>
<aside class="absolute top-4 bottom-4 right-4 w-96 bg-white/10 dark:bg-slate-900/80 backdrop-blur-xl border border-slate-200/20 dark:border-slate-700/50 rounded-xl flex flex-col z-40 overflow-hidden shadow-2xl">
<div class="p-4 border-b border-slate-700/50 flex items-center gap-3">
<span class="material-symbols-outlined text-primary">dynamic_feed</span>
<h3 class="font-bold">Live Event Feed</h3>
<div class="ml-auto flex items-center gap-1.5 bg-green-500/10 px-2 py-0.5 rounded-full border border-green-500/20">
<span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span>
<span class="text-[10px] font-bold text-green-500 uppercase">Live</span>
</div>
</div>
<div class="flex-1 overflow-y-auto p-4 space-y-3">
<div class="bg-slate-800/60 border border-slate-700/50 rounded-xl p-3 hover:bg-slate-800 transition-colors cursor-pointer group">
<div class="flex justify-between items-start mb-2">
<span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded bg-red-500/20 text-red-500 border border-red-500/30">Hostile Airstrike</span>
<span class="text-[10px] text-slate-500">14:23 UTC</span>
</div>
<h4 class="text-sm font-bold text-slate-100 group-hover:text-primary transition-colors">Multiple missile strikes reported in Sector 7</h4>
<p class="text-xs text-slate-400 mt-1 leading-relaxed">Local sources confirm 4 impacts near industrial complex. Casualties reported.</p>
<div class="mt-2 pt-2 border-t border-slate-700/30 flex items-center justify-between">
<span class="text-[10px] text-slate-500 font-mono">48.376° N, 31.165° E</span>
<span class="material-symbols-outlined text-sm text-slate-500">open_in_new</span>
</div>
</div>
<div class="bg-slate-800/60 border border-slate-700/50 rounded-xl p-3 hover:bg-slate-800 transition-colors cursor-pointer group">
<div class="flex justify-between items-start mb-2">
<span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded bg-primary/20 text-primary border border-primary/30">Troop Movement</span>
<span class="text-[10px] text-slate-500">14:05 UTC</span>
</div>
<h4 class="text-sm font-bold text-slate-100 group-hover:text-primary transition-colors">Battalion 42 repositioning to ridge line</h4>
<p class="text-xs text-slate-400 mt-1 leading-relaxed">Visual confirmation of 12 T-72 tanks and supply trucks moving North-West.</p>
<div class="mt-2 pt-2 border-t border-slate-700/30 flex items-center justify-between">
<span class="text-[10px] text-slate-500 font-mono">48.122° N, 31.541° E</span>
<span class="material-symbols-outlined text-sm text-slate-500">open_in_new</span>
</div>
</div>
<div class="bg-slate-800/60 border border-slate-700/50 rounded-xl p-3 hover:bg-slate-800 transition-colors cursor-pointer group">
<div class="flex justify-between items-start mb-2">
<span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded bg-yellow-500/20 text-yellow-500 border border-yellow-500/30">UAV Spotted</span>
<span class="text-[10px] text-slate-500">13:58 UTC</span>
</div>
<h4 class="text-sm font-bold text-slate-100 group-hover:text-primary transition-colors">Unidentified scout drone at medium altitude</h4>
<p class="text-xs text-slate-400 mt-1 leading-relaxed">Radar trace detected 15km South of primary AO. Origin point unknown.</p>
<div class="mt-2 pt-2 border-t border-slate-700/30 flex items-center justify-between">
<span class="text-[10px] text-slate-500 font-mono">47.892° N, 31.023° E</span>
<span class="material-symbols-outlined text-sm text-slate-500">open_in_new</span>
</div>
</div>
<div class="bg-slate-800/60 border border-slate-700/50 rounded-xl p-3 hover:bg-slate-800 transition-colors cursor-pointer group">
<div class="flex justify-between items-start mb-2">
<span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded bg-red-500/20 text-red-500 border border-red-500/30">Artillery</span>
<span class="text-[10px] text-slate-500">13:42 UTC</span>
</div>
<h4 class="text-sm font-bold text-slate-100 group-hover:text-primary transition-colors">Counter-battery fire engaged near rail hub</h4>
<p class="text-xs text-slate-400 mt-1 leading-relaxed">Satellite thermals show sustained engagement over 20 minutes.</p>
<div class="mt-2 pt-2 border-t border-slate-700/30 flex items-center justify-between">
<span class="text-[10px] text-slate-500 font-mono">48.455° N, 32.102° E</span>
<span class="material-symbols-outlined text-sm text-slate-500">open_in_new</span>
</div>
</div>
</div>
<div class="p-3 bg-slate-900 border-t border-slate-700/50 text-center">
<button class="text-xs font-bold text-slate-500 hover:text-primary transition-colors uppercase tracking-widest">Load Archived Events</button>
</div>
</aside>
<div class="absolute bottom-6 right-104 flex flex-col gap-3 z-40" style="right: 410px;">
<div class="flex flex-col bg-slate-900/80 backdrop-blur-md rounded-xl border border-slate-700/50 overflow-hidden shadow-2xl">
<button class="p-3 hover:bg-primary/20 text-slate-300 hover:text-white transition-colors border-b border-slate-700/50 tooltip" title="Zoom In">
<span class="material-symbols-outlined">add</span>
</button>
<button class="p-3 hover:bg-primary/20 text-slate-300 hover:text-white transition-colors border-b border-slate-700/50 tooltip" title="Zoom Out">
<span class="material-symbols-outlined">remove</span>
</button>
<button class="p-3 hover:bg-primary/20 text-slate-300 hover:text-white transition-colors tooltip" title="My Location">
<span class="material-symbols-outlined">my_location</span>
</button>
</div>
<div class="flex flex-col bg-slate-900/80 backdrop-blur-md rounded-xl border border-slate-700/50 overflow-hidden shadow-2xl">
<button class="p-3 hover:bg-primary/20 text-slate-300 hover:text-white transition-colors border-b border-slate-700/50 tooltip" title="Measure Distance">
<span class="material-symbols-outlined">square_foot</span>
</button>
<button class="p-3 hover:bg-primary/20 text-slate-300 hover:text-white transition-colors tooltip" title="Print Map">
<span class="material-symbols-outlined">print</span>
</button>
</div>
<div class="flex bg-slate-900/80 backdrop-blur-md rounded-xl border border-slate-700/50 p-1 shadow-2xl">
<button class="px-4 py-2 rounded-lg bg-primary text-white text-xs font-bold">Tactical</button>
<button class="px-4 py-2 rounded-lg text-slate-400 hover:text-white text-xs font-bold transition-colors">Satellite</button>
<button class="px-4 py-2 rounded-lg text-slate-400 hover:text-white text-xs font-bold transition-colors">Street</button>
</div>
</div>
<div class="absolute bottom-6 left-88 z-40 bg-slate-900/80 backdrop-blur-md px-4 py-2 rounded-full border border-slate-700/50 flex items-center gap-6 shadow-2xl" style="left: 350px;">
<div class="flex items-center gap-2">
<span class="w-3 h-3 rounded-full bg-red-500"></span>
<span class="text-[10px] font-bold uppercase text-slate-300">Hostile</span>
</div>
<div class="flex items-center gap-2">
<span class="w-3 h-3 rounded-full bg-primary"></span>
<span class="text-[10px] font-bold uppercase text-slate-300">Friendly</span>
</div>
<div class="flex items-center gap-2">
<span class="w-3 h-3 rounded-full bg-yellow-500"></span>
<span class="text-[10px] font-bold uppercase text-slate-300">Neutral</span>
</div>
<div class="h-4 w-[1px] bg-slate-700"></div>
<div class="flex items-center gap-2">
<span class="text-[10px] font-mono text-slate-400 uppercase tracking-widest">Active Scale: 1:50,000</span>
</div>
</div>
</main>

</template>

<script setup lang="ts">
// @ts-nocheck
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
  return type.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
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