<script setup lang="ts">
import { ref, reactive, computed, onMounted, watch, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import { useEventsStore } from '@/stores/events';
import type { Event, EventType, Actor } from '@/types';

const router = useRouter();
const eventsStore = useEventsStore();

const loading = ref(false);
const isPlaying = ref(false);
const playbackSpeed = ref(1);
const playInterval = ref<number | null>(null);

const dateRange = reactive({
  start: new Date('2025-01-01'),
  end: new Date('2026-01-16'),
  current: new Date('2025-01-01')
});

const filters = reactive({
  eventTypes: [] as EventType[],
  actorIds: [] as number[]
});

const showFilters = ref(false);

const eventTypes: { value: EventType; label: string; color: string }[] = [
  { value: 'combat_engagement', label: 'Combat', color: 'bg-red-500' },
  { value: 'airstrike', label: 'Airstrike', color: 'bg-orange-500' },
  { value: 'artillery_strike', label: 'Artillery', color: 'bg-yellow-500' },
  { value: 'missile_strike', label: 'Missile', color: 'bg-purple-500' },
  { value: 'equipment_destroyed', label: 'Destroyed', color: 'bg-gray-500' },
  { value: 'troop_movement', label: 'Movement', color: 'bg-blue-500' },
  { value: 'civilian_casualties', label: 'Civilian', color: 'bg-pink-500' }
];

const speedOptions = [
  { value: 0.5, label: '0.5x' },
  { value: 1, label: '1x' },
  { value: 2, label: '2x' },
  { value: 5, label: '5x' },
  { value: 10, label: '10x' }
];

const actors = ref<Actor[]>([
  { id: 1, name: 'Actor A', color_hex: '#3b82f6' } as Actor,
  { id: 2, name: 'Actor B', color_hex: '#ef4444' } as Actor,
  { id: 3, name: 'Actor C', color_hex: '#22c55e' } as Actor
]);

const totalDays = computed(() => {
  const diff = dateRange.end.getTime() - dateRange.start.getTime();
  return Math.ceil(diff / (1000 * 60 * 60 * 24));
});

const currentDayOffset = computed(() => {
  const diff = dateRange.current.getTime() - dateRange.start.getTime();
  return Math.ceil(diff / (1000 * 60 * 60 * 24));
});

const progressPercentage = computed(() => {
  return (currentDayOffset.value / totalDays.value) * 100;
});

const formattedCurrentDate = computed(() => {
  return dateRange.current.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
});

const filteredEvents = computed(() => {
  let events = eventsStore.events;

  // Filter by date
  events = events.filter(e => {
    const eventDate = new Date(e.occurred_at);
    return eventDate <= dateRange.current;
  });

  // Filter by event types
  if (filters.eventTypes.length > 0) {
    events = events.filter(e => filters.eventTypes.includes(e.event_type));
  }

  // Filter by actors
  if (filters.actorIds.length > 0) {
    events = events.filter(e => e.actor_id && filters.actorIds.includes(e.actor_id));
  }

  return events;
});

const timelineEvents = computed(() => {
  // Group events by month for timeline markers
  const grouped: Record<string, Event[]> = {};

  filteredEvents.value.forEach(event => {
    const date = new Date(event.occurred_at);
    const key = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`;
    if (!grouped[key]) {
      grouped[key] = [];
    }
    grouped[key].push(event);
  });

  return grouped;
});

const recentEvents = computed(() => {
  return filteredEvents.value.slice(0, 10);
});

const togglePlayback = () => {
  if (isPlaying.value) {
    pausePlayback();
  } else {
    startPlayback();
  }
};

const startPlayback = () => {
  isPlaying.value = true;

  playInterval.value = window.setInterval(() => {
    stepForward();

    if (dateRange.current >= dateRange.end) {
      pausePlayback();
    }
  }, 1000 / playbackSpeed.value);
};

const pausePlayback = () => {
  isPlaying.value = false;

  if (playInterval.value) {
    clearInterval(playInterval.value);
    playInterval.value = null;
  }
};

const stepForward = () => {
  const newDate = new Date(dateRange.current);
  newDate.setDate(newDate.getDate() + 1);

  if (newDate <= dateRange.end) {
    dateRange.current = newDate;
  }
};

const stepBackward = () => {
  const newDate = new Date(dateRange.current);
  newDate.setDate(newDate.getDate() - 1);

  if (newDate >= dateRange.start) {
    dateRange.current = newDate;
  }
};

const handleSliderChange = (event: globalThis.Event) => {
  const value = parseInt((event.target as HTMLInputElement).value);
  const newDate = new Date(dateRange.start);
  newDate.setDate(newDate.getDate() + value);
  dateRange.current = newDate;
};

const setSpeed = (speed: number) => {
  playbackSpeed.value = speed;

  if (isPlaying.value) {
    pausePlayback();
    startPlayback();
  }
};

const reset = () => {
  pausePlayback();
  dateRange.current = new Date(dateRange.start);
};

const skipToEnd = () => {
  pausePlayback();
  dateRange.current = new Date(dateRange.end);
};

const toggleEventType = (type: EventType) => {
  const index = filters.eventTypes.indexOf(type);
  if (index === -1) {
    filters.eventTypes.push(type);
  } else {
    filters.eventTypes.splice(index, 1);
  }
};

const toggleActor = (actorId: number) => {
  const index = filters.actorIds.indexOf(actorId);
  if (index === -1) {
    filters.actorIds.push(actorId);
  } else {
    filters.actorIds.splice(index, 1);
  }
};

const viewOnMap = () => {
  router.push({
    name: 'map',
    query: {
      date: dateRange.current.toISOString().split('T')[0]
    }
  });
};

const viewEventDetail = (eventId: number) => {
  router.push({ name: 'event-detail', params: { id: eventId } });
};

const getEventTypeColor = (type: EventType) => {
  const found = eventTypes.find(t => t.value === type);
  return found?.color || 'bg-gray-500';
};

watch(playbackSpeed, () => {
  if (isPlaying.value) {
    pausePlayback();
    startPlayback();
  }
});

onMounted(async () => {
  loading.value = true;
  try {
    await eventsStore.fetchEvents();
  } finally {
    loading.value = false;
  }
});

onUnmounted(() => {
  pausePlayback();
});
</script>

<template>
  <div class="flex flex-col h-full bg-gray-50 dark:bg-gray-900">
    <!-- Header -->
    <div class="bg-white dark:bg-gray-800 shadow px-6 py-4">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Timeline Analysis</h1>
          <p class="mt-1 text-gray-600 dark:text-gray-400">Interactive timeline of events</p>
        </div>
        <div class="flex items-center space-x-3">
          <button
            @click="showFilters = !showFilters"
            :class="[
              'px-4 py-2 rounded-lg font-medium transition-colors flex items-center space-x-2',
              showFilters
                ? 'bg-blue-600 text-white'
                : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'
            ]"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
            </svg>
            <span>Filters</span>
          </button>
          <button
            @click="viewOnMap"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 flex items-center space-x-2"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
            </svg>
            <span>View on Map</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Filters Panel -->
    <div v-if="showFilters" class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-6 py-4">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Event Types</h3>
          <div class="flex flex-wrap gap-2">
            <button
              v-for="type in eventTypes"
              :key="type.value"
              @click="toggleEventType(type.value)"
              :class="[
                'px-3 py-1.5 rounded-full text-sm font-medium transition-colors flex items-center space-x-1',
                filters.eventTypes.includes(type.value)
                  ? 'bg-blue-600 text-white'
                  : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'
              ]"
            >
              <span :class="['w-2 h-2 rounded-full', type.color]"></span>
              <span>{{ type.label }}</span>
            </button>
          </div>
        </div>
        <div>
          <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Actors</h3>
          <div class="flex flex-wrap gap-2">
            <button
              v-for="actor in actors"
              :key="actor.id"
              @click="toggleActor(actor.id)"
              :class="[
                'px-3 py-1.5 rounded-full text-sm font-medium transition-colors flex items-center space-x-1',
                filters.actorIds.includes(actor.id)
                  ? 'bg-blue-600 text-white'
                  : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'
              ]"
            >
              <span class="w-2 h-2 rounded-full" :style="{ backgroundColor: actor.color_hex }"></span>
              <span>{{ actor.name }}</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="flex-1 flex overflow-hidden">
      <!-- Timeline Panel -->
      <div class="flex-1 flex flex-col p-6">
        <!-- Date Display -->
        <div class="text-center mb-6">
          <p class="text-4xl font-bold text-gray-900 dark:text-white">{{ formattedCurrentDate }}</p>
          <p class="text-gray-500 dark:text-gray-400 mt-1">
            Day {{ currentDayOffset }} of {{ totalDays }} | {{ filteredEvents.length }} events
          </p>
        </div>

        <!-- Timeline Bar -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
          <!-- Playback Controls -->
          <div class="flex items-center justify-between mb-6">
            <div class="flex items-center space-x-3">
              <button
                @click="reset"
                class="p-2 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600"
                title="Reset to start"
              >
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z" />
                  <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd" />
                </svg>
              </button>

              <button
                @click="stepBackward"
                :disabled="dateRange.current <= dateRange.start"
                class="p-2 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M8.445 14.832A1 1 0 0010 14v-2.798l5.445 3.63A1 1 0 0017 14V6a1 1 0 00-1.555-.832L10 8.798V6a1 1 0 00-1.555-.832l-6 4a1 1 0 000 1.664l6 4z" />
                </svg>
              </button>

              <button
                @click="togglePlayback"
                class="w-12 h-12 flex items-center justify-center bg-blue-600 text-white rounded-full hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
              >
                <svg v-if="!isPlaying" class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z" />
                </svg>
                <svg v-else class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M5.75 3a.75.75 0 00-.75.75v12.5c0 .414.336.75.75.75h1.5a.75.75 0 00.75-.75V3.75A.75.75 0 007.25 3h-1.5zM12.75 3a.75.75 0 00-.75.75v12.5c0 .414.336.75.75.75h1.5a.75.75 0 00.75-.75V3.75a.75.75 0 00-.75-.75h-1.5z" />
                </svg>
              </button>

              <button
                @click="stepForward"
                :disabled="dateRange.current >= dateRange.end"
                class="p-2 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M4.555 5.168A1 1 0 003 6v8a1 1 0 001.555.832L10 11.202V14a1 1 0 001.555.832l6-4a1 1 0 000-1.664l-6-4A1 1 0 0010 6v2.798l-5.445-3.63z" />
                </svg>
              </button>

              <button
                @click="skipToEnd"
                class="p-2 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600"
                title="Skip to end"
              >
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M4.555 5.168A1 1 0 003 6v8a1 1 0 001.555.832L10 11.202V14a1 1 0 001.555.832l4-2.667V14a1 1 0 102 0V6a1 1 0 10-2 0v1.835l-4-2.667A1 1 0 0010 6v2.798l-5.445-3.63z" />
                </svg>
              </button>
            </div>

            <div class="flex items-center space-x-2">
              <span class="text-sm text-gray-600 dark:text-gray-400">Speed:</span>
              <div class="flex space-x-1">
                <button
                  v-for="option in speedOptions"
                  :key="option.value"
                  @click="setSpeed(option.value)"
                  :class="[
                    'px-3 py-1 text-sm rounded-lg transition-colors',
                    playbackSpeed === option.value
                      ? 'bg-blue-600 text-white'
                      : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'
                  ]"
                >
                  {{ option.label }}
                </button>
              </div>
            </div>
          </div>

          <!-- Timeline Slider -->
          <div class="mb-4">
            <div class="flex justify-between text-sm text-gray-500 dark:text-gray-400 mb-2">
              <span>{{ dateRange.start.toLocaleDateString() }}</span>
              <span>{{ dateRange.end.toLocaleDateString() }}</span>
            </div>
            <div class="relative">
              <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                <div
                  class="h-full bg-blue-600 transition-all duration-100"
                  :style="{ width: `${progressPercentage}%` }"
                ></div>
              </div>
              <input
                type="range"
                :min="0"
                :max="totalDays"
                :value="currentDayOffset"
                @input="handleSliderChange"
                class="absolute top-0 left-0 w-full h-3 opacity-0 cursor-pointer"
              />
            </div>
          </div>

          <!-- Progress Info -->
          <div class="flex justify-between text-sm text-gray-500 dark:text-gray-400">
            <span>{{ Math.round(progressPercentage) }}% complete</span>
          </div>
        </div>

        <!-- Event Markers / Visual Timeline -->
        <div class="flex-1 bg-white dark:bg-gray-800 rounded-lg shadow p-6 overflow-auto">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Event Timeline</h3>
          <div class="relative">
            <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-200 dark:bg-gray-700"></div>
            <div class="space-y-4 ml-8">
              <div
                v-for="event in recentEvents"
                :key="event.id"
                @click="viewEventDetail(event.id)"
                class="relative pl-6 cursor-pointer group"
              >
                <div
                  class="absolute left-0 top-1 w-3 h-3 rounded-full ring-4 ring-white dark:ring-gray-800"
                  :class="getEventTypeColor(event.event_type)"
                ></div>
                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 group-hover:bg-gray-100 dark:group-hover:bg-gray-700 transition-colors">
                  <div class="flex items-center justify-between mb-1">
                    <span class="text-sm text-gray-500 dark:text-gray-400">
                      {{ new Date(event.occurred_at).toLocaleDateString() }}
                    </span>
                    <span
                      class="text-xs px-2 py-0.5 rounded-full"
                      :class="{
                        'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400': event.confidence_level === 'confirmed',
                        'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400': event.confidence_level === 'likely',
                        'bg-gray-100 text-gray-700 dark:bg-gray-600 dark:text-gray-300': event.confidence_level === 'unconfirmed'
                      }"
                    >
                      {{ event.confidence_level }}
                    </span>
                  </div>
                  <h4 class="font-medium text-gray-900 dark:text-white">{{ event.title }}</h4>
                  <p v-if="event.location_name" class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    {{ event.location_name }}
                  </p>
                </div>
              </div>
              <div v-if="recentEvents.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
                No events to display for selected filters
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Stats Sidebar -->
      <div class="w-80 bg-white dark:bg-gray-800 border-l border-gray-200 dark:border-gray-700 p-6 overflow-auto">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Statistics</h3>

        <div class="space-y-4">
          <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ filteredEvents.length }}</p>
            <p class="text-sm text-gray-500 dark:text-gray-400">Total Events</p>
          </div>

          <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
            <p class="text-2xl font-bold text-green-600 dark:text-green-400">
              {{ filteredEvents.filter(e => e.verified).length }}
            </p>
            <p class="text-sm text-gray-500 dark:text-gray-400">Verified Events</p>
          </div>

          <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">
              {{ Object.keys(timelineEvents).length }}
            </p>
            <p class="text-sm text-gray-500 dark:text-gray-400">Active Months</p>
          </div>
        </div>

        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mt-6 mb-3">Events by Type</h4>
        <div class="space-y-2">
          <div
            v-for="type in eventTypes"
            :key="type.value"
            class="flex items-center justify-between text-sm"
          >
            <div class="flex items-center space-x-2">
              <span :class="['w-3 h-3 rounded-full', type.color]"></span>
              <span class="text-gray-700 dark:text-gray-300">{{ type.label }}</span>
            </div>
            <span class="font-medium text-gray-900 dark:text-white">
              {{ filteredEvents.filter(e => e.event_type === type.value).length }}
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
input[type="range"] {
  -webkit-appearance: none;
  appearance: none;
}

input[type="range"]::-webkit-slider-thumb {
  -webkit-appearance: none;
  appearance: none;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  background: #2563eb;
  cursor: pointer;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

input[type="range"]::-moz-range-thumb {
  width: 20px;
  height: 20px;
  border-radius: 50%;
  background: #2563eb;
  cursor: pointer;
  border: none;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}
</style>
