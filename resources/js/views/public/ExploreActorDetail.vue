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
            <router-link to="/explore/events" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors">Events</router-link>
            <router-link to="/explore/equipment" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors">Equipment</router-link>
            <router-link to="/explore/actors" class="text-blue-600 dark:text-blue-400 font-medium">Actors</router-link>
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
        <p class="mt-4 text-gray-600 dark:text-gray-400">Loading actor details...</p>
      </div>
    </div>

    <!-- Not Found State -->
    <div v-else-if="!actor" class="pt-24 text-center py-16">
      <svg class="w-24 h-24 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
      <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Actor Not Found</h2>
      <p class="text-gray-600 dark:text-gray-400 mb-6">The actor you're looking for doesn't exist or has been removed.</p>
      <router-link to="/explore/actors" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
        Browse All Actors
      </router-link>
    </div>

    <!-- Actor Detail Content -->
    <div v-else class="pt-24 pb-12 px-4 sm:px-6 lg:px-8">
      <div class="max-w-5xl mx-auto">
        <!-- Back Button -->
        <router-link
          to="/explore/actors"
          class="inline-flex items-center text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white mb-6 transition-colors"
        >
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
          Back to Actors
        </router-link>

        <!-- Main Actor Card -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden border border-gray-200 dark:border-gray-700">
          <!-- Actor Header -->
          <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-start space-x-4">
              <!-- Actor Icon -->
              <div
                :class="getActorIconBg(actor.actor_type)"
                class="w-16 h-16 rounded-lg flex items-center justify-center flex-shrink-0"
              >
                <span v-if="actor.flag_emoji" class="text-3xl">{{ actor.flag_emoji }}</span>
                <svg v-else class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9" />
                </svg>
              </div>
              <div class="flex-1">
                <div class="flex flex-wrap items-center gap-2 mb-2">
                  <span :class="getTypeBadgeClass(actor.actor_type)" class="px-3 py-1 text-sm font-medium rounded-full">
                    {{ formatActorType(actor.actor_type) }}
                  </span>
                  <span :class="getActivityBadgeClass(actor.activity_level)" class="px-3 py-1 text-sm font-medium rounded-full">
                    {{ actor.activity_level }} activity
                  </span>
                  <span v-if="actor.is_designated_terrorist" class="px-3 py-1 text-sm font-medium rounded-full bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400">
                    Designated Terrorist
                  </span>
                </div>
                <h1 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white">{{ actor.name }}</h1>
                <p v-if="actor.short_name" class="text-lg text-gray-500 dark:text-gray-400 mt-1">{{ actor.short_name }}</p>
              </div>
            </div>
          </div>

          <!-- Actor Content -->
          <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
              <!-- Main Content -->
              <div class="lg:col-span-2 space-y-6">
                <!-- Description -->
                <div>
                  <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Overview</h2>
                  <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap leading-relaxed">{{ actor.description }}</p>
                </div>

                <!-- History -->
                <div v-if="actor.history">
                  <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">History</h2>
                  <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap leading-relaxed">{{ actor.history }}</p>
                </div>

                <!-- Equipment Used -->
                <div v-if="actor.equipment && actor.equipment.length > 0">
                  <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Known Equipment</h2>
                  <div class="flex flex-wrap gap-2">
                    <router-link
                      v-for="(equip, index) in actor.equipment"
                      :key="index"
                      :to="{ name: 'explore-equipment-detail', params: { id: equip.id } }"
                      class="px-3 py-2 bg-gray-100 dark:bg-gray-700 rounded-lg text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
                    >
                      {{ equip.name }}
                    </router-link>
                  </div>
                </div>

                <!-- Related Events -->
                <div v-if="recentEvents.length > 0">
                  <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Recent Events</h2>
                  <div class="space-y-3">
                    <router-link
                      v-for="event in recentEvents"
                      :key="event.id"
                      :to="{ name: 'explore-event-detail', params: { id: event.id } }"
                      class="flex items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors group"
                    >
                      <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 truncate">
                          {{ event.title }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ formatDate(event.occurred_at) }}</p>
                      </div>
                      <span :class="getEventBadgeClass(event.event_type)" class="ml-3 px-2 py-1 text-xs font-medium rounded-full flex-shrink-0">
                        {{ formatEventType(event.event_type) }}
                      </span>
                    </router-link>
                  </div>
                  <router-link
                    :to="{ name: 'explore-events', query: { actor: actor.id } }"
                    class="inline-block mt-3 text-sm text-blue-600 dark:text-blue-400 hover:underline"
                  >
                    View all {{ actor.events_count }} events
                  </router-link>
                </div>
              </div>

              <!-- Sidebar -->
              <div class="space-y-6">
                <!-- Actor Details -->
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                  <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Actor Details</h3>
                  <dl class="space-y-3">
                    <div>
                      <dt class="text-xs text-gray-500 dark:text-gray-400">Type</dt>
                      <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ formatActorType(actor.actor_type) }}</dd>
                    </div>
                    <div v-if="actor.country">
                      <dt class="text-xs text-gray-500 dark:text-gray-400">Country</dt>
                      <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ actor.country }}</dd>
                    </div>
                    <div>
                      <dt class="text-xs text-gray-500 dark:text-gray-400">Activity Level</dt>
                      <dd class="text-sm font-medium text-gray-900 dark:text-white capitalize">{{ actor.activity_level }}</dd>
                    </div>
                    <div v-if="actor.founded_date">
                      <dt class="text-xs text-gray-500 dark:text-gray-400">Founded</dt>
                      <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ actor.founded_date }}</dd>
                    </div>
                    <div v-if="actor.ideology">
                      <dt class="text-xs text-gray-500 dark:text-gray-400">Ideology</dt>
                      <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ actor.ideology }}</dd>
                    </div>
                    <div>
                      <dt class="text-xs text-gray-500 dark:text-gray-400">Events Tracked</dt>
                      <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ actor.events_count }}</dd>
                    </div>
                  </dl>
                </div>

                <!-- Related Conflicts -->
                <div v-if="actor.conflicts && actor.conflicts.length > 0" class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                  <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Active Conflicts</h3>
                  <div class="space-y-2">
                    <router-link
                      v-for="conflict in actor.conflicts"
                      :key="conflict.id"
                      :to="{ name: 'explore-conflicts', query: { id: conflict.id } }"
                      class="block p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors"
                    >
                      <p class="text-sm font-medium text-gray-900 dark:text-white">{{ conflict.name }}</p>
                      <p class="text-xs text-gray-500 dark:text-gray-400">{{ conflict.region }}</p>
                    </router-link>
                  </div>
                </div>

                <!-- Share -->
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                  <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Share Profile</h3>
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
                  <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Create an account to access relationship mapping, advanced analytics, and more.</p>
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

        <!-- Related Actors -->
        <div v-if="relatedActors.length > 0" class="mt-8">
          <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Related Actors</h2>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <router-link
              v-for="related in relatedActors"
              :key="related.id"
              :to="{ name: 'explore-actor-detail', params: { id: related.id } }"
              class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700 hover:shadow-lg transition-all group"
            >
              <div class="flex items-center space-x-3 mb-2">
                <div
                  :class="getActorIconBg(related.actor_type)"
                  class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0"
                >
                  <span v-if="related.flag_emoji" class="text-lg">{{ related.flag_emoji }}</span>
                  <svg v-else class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9" />
                  </svg>
                </div>
                <div>
                  <h3 class="text-sm font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400">{{ related.name }}</h3>
                  <p v-if="related.short_name" class="text-xs text-gray-500 dark:text-gray-400">{{ related.short_name }}</p>
                </div>
              </div>
              <span :class="getTypeBadgeClass(related.actor_type)" class="px-2 py-1 text-xs font-medium rounded-full">
                {{ formatActorType(related.actor_type) }}
              </span>
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

const route = useRoute();
const themeStore = useThemeStore();
const isDark = computed(() => themeStore.isDark);

const toggleTheme = () => {
  themeStore.toggleTheme();
};

const loading = ref(false);
const copied = ref(false);

interface ActorEquipment {
  id: number;
  name: string;
}

interface ActorConflict {
  id: number;
  name: string;
  region: string;
}

interface ActorDetail {
  id: number;
  name: string;
  short_name: string | null;
  actor_type: string;
  country: string | null;
  flag_emoji: string | null;
  activity_level: 'high' | 'medium' | 'low' | 'inactive';
  is_designated_terrorist: boolean;
  description: string;
  history: string | null;
  founded_date: string | null;
  ideology: string | null;
  events_count: number;
  equipment: ActorEquipment[];
  conflicts: ActorConflict[];
}

interface RecentEvent {
  id: number;
  title: string;
  event_type: string;
  occurred_at: string;
}

interface RelatedActor {
  id: number;
  name: string;
  short_name: string | null;
  actor_type: string;
  flag_emoji: string | null;
}

const actor = ref<ActorDetail | null>(null);
const recentEvents = ref<RecentEvent[]>([]);
const relatedActors = ref<RelatedActor[]>([]);

// Actor data store for lookup
const actorsData: Record<number, ActorDetail> = {
  1: {
    id: 1,
    name: 'Russian Armed Forces',
    short_name: 'RAF',
    actor_type: 'STATE',
    country: 'Russia',
    flag_emoji: null,
    activity_level: 'high',
    is_designated_terrorist: false,
    description: 'The armed forces of the Russian Federation, consisting of ground forces, navy, aerospace forces, and strategic missile troops. The Russian Armed Forces are among the largest in the world with approximately 1 million active personnel.',
    history: 'The modern Russian Armed Forces were established in 1992 following the dissolution of the Soviet Union. They inherited the bulk of the former Soviet military, including its nuclear arsenal and a significant portion of its conventional forces.',
    founded_date: '1992',
    ideology: null,
    events_count: 8432,
    equipment: [
      { id: 1, name: 'T-90M Main Battle Tank' },
      { id: 2, name: 'Su-35S Fighter Aircraft' },
      { id: 3, name: 'Kalibr Cruise Missile' }
    ],
    conflicts: [
      { id: 1, name: 'Russo-Ukrainian War', region: 'Eastern Europe' }
    ]
  },
  2: {
    id: 2,
    name: 'Ukrainian Armed Forces',
    short_name: 'UAF',
    actor_type: 'STATE',
    country: 'Ukraine',
    flag_emoji: null,
    activity_level: 'high',
    is_designated_terrorist: false,
    description: 'The military forces of Ukraine, including ground forces, air force, navy, and territorial defense. Since 2022, the UAF has been significantly expanded and equipped with Western military aid.',
    history: 'The Armed Forces of Ukraine were established in 1991 following Ukrainian independence. Major reforms and modernization began after 2014 following the annexation of Crimea.',
    founded_date: '1991',
    ideology: null,
    events_count: 7651,
    equipment: [
      { id: 4, name: 'Leopard 2 Main Battle Tank' },
      { id: 5, name: 'HIMARS' },
      { id: 6, name: 'Bayraktar TB2' }
    ],
    conflicts: [
      { id: 1, name: 'Russo-Ukrainian War', region: 'Eastern Europe' }
    ]
  },
  3: {
    id: 3,
    name: 'Wagner Group',
    short_name: 'Wagner',
    actor_type: 'PMC',
    country: 'Russia',
    flag_emoji: null,
    activity_level: 'low',
    is_designated_terrorist: true,
    description: 'Russian private military company involved in various conflicts globally. Operations significantly reduced following the 2023 mutiny and the death of its founder Yevgeny Prigozhin.',
    history: 'Founded around 2014, Wagner Group operated in Ukraine, Syria, Libya, Mali, and the Central African Republic. Following a brief mutiny in June 2023 and the subsequent death of founder Yevgeny Prigozhin in a plane crash, the group was largely absorbed into Russian state structures.',
    founded_date: '2014',
    ideology: null,
    events_count: 1234,
    equipment: [
      { id: 1, name: 'T-90M Main Battle Tank' },
      { id: 7, name: 'BMP-2' }
    ],
    conflicts: [
      { id: 1, name: 'Russo-Ukrainian War', region: 'Eastern Europe' },
      { id: 2, name: 'Syrian Civil War', region: 'Middle East' }
    ]
  }
};

const fetchActor = (id: string) => {
  loading.value = true;

  // Simulated data - in production this would be an API call
  setTimeout(() => {
    const actorId = parseInt(id);
    const foundActor = actorsData[actorId];

    if (foundActor) {
      actor.value = foundActor;

      // Simulated recent events
      recentEvents.value = [
        { id: 1, title: 'Artillery strike on military positions', event_type: 'artillery_strike', occurred_at: '2024-01-16T14:30:00Z' },
        { id: 2, title: 'Air defense engagement over Kyiv', event_type: 'airstrike', occurred_at: '2024-01-16T06:15:00Z' },
        { id: 3, title: 'Tank destroyed by FPV drone', event_type: 'equipment_destroyed', occurred_at: '2024-01-15T18:45:00Z' }
      ];

      // Simulated related actors (actors in the same conflicts)
      relatedActors.value = Object.values(actorsData)
        .filter(a => a.id !== actorId)
        .slice(0, 3)
        .map(a => ({
          id: a.id,
          name: a.name,
          short_name: a.short_name,
          actor_type: a.actor_type,
          flag_emoji: a.flag_emoji
        }));
    } else {
      actor.value = null;
    }

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

const getActorIconBg = (type: string) => {
  const classes: Record<string, string> = {
    'STATE': 'bg-blue-500',
    'SEPARATIST': 'bg-purple-500',
    'INSURGENT': 'bg-orange-500',
    'TERRORIST': 'bg-red-500',
    'MILITIA': 'bg-yellow-500',
    'PMC': 'bg-gray-500',
    'REBEL': 'bg-green-500'
  };
  return classes[type] || 'bg-gray-500';
};

const getTypeBadgeClass = (type: string) => {
  const classes: Record<string, string> = {
    'STATE': 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400',
    'SEPARATIST': 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400',
    'INSURGENT': 'bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400',
    'TERRORIST': 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400',
    'MILITIA': 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400',
    'PMC': 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-400',
    'REBEL': 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400'
  };
  return classes[type] || 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-400';
};

const getActivityBadgeClass = (level: string) => {
  const classes: Record<string, string> = {
    'high': 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400',
    'medium': 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400',
    'low': 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400',
    'inactive': 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-400'
  };
  return classes[level] || 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-400';
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

const formatActorType = (type: string) => {
  return type.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase()).join(' ');
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

onMounted(() => {
  fetchActor(route.params.id as string);
});

watch(() => route.params.id, (newId) => {
  if (newId) {
    fetchActor(newId as string);
  }
});
</script>
