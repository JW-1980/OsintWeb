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
            <router-link to="/explore" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors">Map</router-link>
            <router-link to="/explore/events" class="text-blue-600 dark:text-blue-400 font-medium">Events</router-link>
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

    <!-- Loading State -->
    <div v-if="loading" class="pt-24 flex items-center justify-center h-96">
      <div class="text-center">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
        <p class="mt-4 text-gray-600 dark:text-gray-400">Loading event details...</p>
      </div>
    </div>

    <!-- Not Found State -->
    <div v-else-if="!event" class="pt-24 text-center py-16">
      <svg class="w-24 h-24 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
      <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Event Not Found</h2>
      <p class="text-gray-600 dark:text-gray-400 mb-6">The event you're looking for doesn't exist or has been removed.</p>
      <router-link to="/explore/events" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
        Browse All Events
      </router-link>
    </div>

    <!-- Event Detail Content -->
    <div v-else class="pt-24 pb-12 px-4 sm:px-6 lg:px-8">
      <div class="max-w-5xl mx-auto">
        <!-- Back Button -->
        <router-link
          to="/explore/events"
          class="inline-flex items-center text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white mb-6 transition-colors"
        >
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
          Back to Events
        </router-link>

        <!-- Main Event Card -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden border border-gray-200 dark:border-gray-700">
          <!-- Event Header -->
          <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <div class="flex flex-wrap items-center gap-2 mb-4">
              <span :class="getEventBadgeClass(event.event_type)" class="px-3 py-1 text-sm font-medium rounded-full">
                {{ formatEventType(event.event_type) }}
              </span>
              <span :class="getConfidenceBadgeClass(event.confidence_level)" class="px-3 py-1 text-sm font-medium rounded-full">
                {{ event.confidence_level }}
              </span>
              <span v-if="event.verified" class="px-3 py-1 text-sm font-medium rounded-full bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400">
                Verified
              </span>
            </div>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mb-2">{{ event.title }}</h1>
            <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500 dark:text-gray-400">
              <span class="flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                {{ event.location_name }}
              </span>
              <span class="flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ formatDateTime(event.occurred_at) }}
              </span>
            </div>
          </div>

          <!-- Event Content -->
          <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
              <!-- Main Content -->
              <div class="lg:col-span-2 space-y-6">
                <!-- Description -->
                <div>
                  <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Description</h2>
                  <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap leading-relaxed">{{ event.description }}</p>
                </div>

                <!-- Map Preview -->
                <div v-if="event.coordinates">
                  <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Location</h2>
                  <div class="h-64 bg-gray-200 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                    <div class="text-center">
                      <svg class="w-12 h-12 mx-auto text-gray-400 dark:text-gray-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                      </svg>
                      <p class="text-sm text-gray-500 dark:text-gray-400">{{ event.coordinates[0].toFixed(4) }}, {{ event.coordinates[1].toFixed(4) }}</p>
                      <router-link
                        :to="{ name: 'explore-map', query: { lat: event.coordinates[0], lng: event.coordinates[1] } }"
                        class="inline-block mt-3 text-sm text-blue-600 dark:text-blue-400 hover:underline"
                      >
                        View on Map
                      </router-link>
                    </div>
                  </div>
                </div>

                <!-- Sources -->
                <div v-if="event.sources && event.sources.length > 0">
                  <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Sources</h2>
                  <div class="space-y-2">
                    <a
                      v-for="(source, index) in event.sources"
                      :key="index"
                      :href="source.url"
                      target="_blank"
                      rel="noopener noreferrer"
                      class="flex items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors group"
                    >
                      <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                      </svg>
                      <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ source.title || 'Source ' + (index + 1) }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ source.url }}</p>
                      </div>
                      <svg class="w-4 h-4 text-gray-400 group-hover:text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                      </svg>
                    </a>
                  </div>
                </div>
              </div>

              <!-- Sidebar -->
              <div class="space-y-6">
                <!-- Event Details -->
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                  <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Event Details</h3>
                  <dl class="space-y-3">
                    <div>
                      <dt class="text-xs text-gray-500 dark:text-gray-400">Event Type</dt>
                      <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ formatEventType(event.event_type) }}</dd>
                    </div>
                    <div>
                      <dt class="text-xs text-gray-500 dark:text-gray-400">Confidence Level</dt>
                      <dd class="text-sm font-medium text-gray-900 dark:text-white capitalize">{{ event.confidence_level }}</dd>
                    </div>
                    <div v-if="event.actor">
                      <dt class="text-xs text-gray-500 dark:text-gray-400">Actor</dt>
                      <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ event.actor }}</dd>
                    </div>
                    <div v-if="event.conflict">
                      <dt class="text-xs text-gray-500 dark:text-gray-400">Conflict</dt>
                      <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ event.conflict }}</dd>
                    </div>
                    <div>
                      <dt class="text-xs text-gray-500 dark:text-gray-400">Reported</dt>
                      <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ formatDate(event.created_at) }}</dd>
                    </div>
                  </dl>
                </div>

                <!-- Share -->
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                  <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Share Event</h3>
                  <div class="flex space-x-2">
                    <button
                      @click="copyLink"
                      class="flex-1 flex items-center justify-center px-3 py-2 bg-white dark:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded-lg text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-500 transition-colors"
                    >
                      <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                      </svg>
                      {{ copied ? 'Copied!' : 'Copy Link' }}
                    </button>
                  </div>
                </div>

                <!-- CTA -->
                <div class="bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-lg p-4 border border-blue-200 dark:border-blue-800">
                  <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">Want More Features?</h3>
                  <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Create an account to access event editing, advanced analytics, and more.</p>
                  <router-link
                    to="/register"
                    class="block w-full text-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors"
                  >
                    Sign Up Free
                  </router-link>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Comments Section -->
        <div v-if="event" class="mt-8 bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 border border-gray-200 dark:border-gray-700">
          <Comments
            commentable-type="events"
            :commentable-id="event.id"
          />
        </div>

        <!-- Related Events -->
        <div v-if="relatedEvents.length > 0" class="mt-8">
          <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Related Events</h2>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <router-link
              v-for="related in relatedEvents"
              :key="related.id"
              :to="{ name: 'explore-event-detail', params: { id: related.id } }"
              class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700 hover:shadow-lg transition-all"
            >
              <span :class="getEventBadgeClass(related.event_type)" class="px-2 py-1 text-xs font-medium rounded-full">
                {{ formatEventType(related.event_type) }}
              </span>
              <h3 class="text-sm font-semibold text-gray-900 dark:text-white mt-2 line-clamp-2">{{ related.title }}</h3>
              <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ formatDate(related.occurred_at) }}</p>
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { useRoute } from 'vue-router';
import { useThemeStore } from '@/stores/theme';
import Comments from '@/components/Comments.vue';

const route = useRoute();
const themeStore = useThemeStore();
const isDark = computed(() => themeStore.isDark);

const toggleTheme = () => {
  themeStore.toggleTheme();
};

const loading = ref(false);
const copied = ref(false);

interface EventSource {
  title: string;
  url: string;
}

interface EventDetail {
  id: number;
  title: string;
  event_type: string;
  description: string;
  location_name: string;
  occurred_at: string;
  created_at: string;
  coordinates: [number, number] | null;
  confidence_level: 'confirmed' | 'likely';
  verified: boolean;
  actor: string | null;
  conflict: string | null;
  sources: EventSource[];
}

interface RelatedEvent {
  id: number;
  title: string;
  event_type: string;
  occurred_at: string;
}

const event = ref<EventDetail | null>(null);
const relatedEvents = ref<RelatedEvent[]>([]);

const fetchEvent = (id: string) => {
  loading.value = true;

  // Simulated data - in production this would be an API call
  setTimeout(() => {
    event.value = {
      id: parseInt(id),
      title: 'Artillery strike on military positions',
      event_type: 'artillery_strike',
      description: 'Multiple MLRS strikes reported targeting defensive positions near the front line. The attack began at approximately 14:30 local time and lasted for approximately 45 minutes.\n\nWitnesses reported significant damage to military infrastructure. Emergency services were dispatched to the area shortly after the attack concluded.\n\nThis event has been corroborated by multiple independent sources including satellite imagery and local reporting.',
      location_name: 'Zaporizhzhia Oblast, Ukraine',
      occurred_at: '2024-01-16T14:30:00Z',
      created_at: '2024-01-16T15:45:00Z',
      coordinates: [47.838, 35.139],
      confidence_level: 'confirmed',
      verified: true,
      actor: 'Russian Armed Forces',
      conflict: 'Russo-Ukrainian War',
      sources: [
        { title: 'Official Military Report', url: 'https://example.com/source1' },
        { title: 'Local News Coverage', url: 'https://example.com/source2' },
        { title: 'Satellite Imagery Analysis', url: 'https://example.com/source3' }
      ]
    };

    relatedEvents.value = [
      { id: 2, title: 'Air defense engagement over Kyiv', event_type: 'airstrike', occurred_at: '2024-01-16T06:15:00Z' },
      { id: 3, title: 'Tank destroyed by FPV drone', event_type: 'equipment_destroyed', occurred_at: '2024-01-15T18:45:00Z' },
      { id: 5, title: 'Missile strike on infrastructure', event_type: 'missile_strike', occurred_at: '2024-01-14T22:30:00Z' }
    ];

    loading.value = false;
  }, 500);
};

const copyLink = async () => {
  try {
    await navigator.clipboard.writeText(window.location.href);
    copied.value = true;
    setTimeout(() => {
      copied.value = false;
    }, 2000);
  } catch (err) {
    console.error('Failed to copy link:', err);
  }
};

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

const getConfidenceBadgeClass = (confidence: string) => {
  const classes: Record<string, string> = {
    'confirmed': 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400',
    'likely': 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400'
  };
  return classes[confidence] || 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-400';
};

const formatEventType = (type: string) => {
  return type.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
};

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};

const formatDateTime = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

onMounted(() => {
  fetchEvent(route.params.id as string);
});

watch(() => route.params.id, (newId) => {
  if (newId) {
    fetchEvent(newId as string);
  }
});
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
