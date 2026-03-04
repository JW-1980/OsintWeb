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
            <router-link to="/explore/actors" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors">Actors</router-link>
            <router-link to="/explore/conflicts" class="text-blue-600 dark:text-blue-400 font-medium">Conflicts</router-link>
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
        <p class="mt-4 text-gray-600 dark:text-gray-400">Loading conflict details...</p>
      </div>
    </div>

    <!-- Not Found State -->
    <div v-else-if="!conflict" class="pt-24 text-center py-16">
      <svg class="w-24 h-24 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
      <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Conflict Not Found</h2>
      <p class="text-gray-600 dark:text-gray-400 mb-6">The conflict you're looking for doesn't exist or has been removed.</p>
      <router-link to="/explore/conflicts" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
        Browse All Conflicts
      </router-link>
    </div>

    <!-- Conflict Detail Content -->
    <div v-else class="pt-24 pb-12 px-4 sm:px-6 lg:px-8">
      <div class="max-w-5xl mx-auto">
        <!-- Back Button -->
        <router-link
          to="/explore/conflicts"
          class="inline-flex items-center text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white mb-6 transition-colors"
        >
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
          Back to Conflicts
        </router-link>

        <!-- Main Conflict Card -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden border border-gray-200 dark:border-gray-700">
          <!-- Conflict Header -->
          <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <div class="flex flex-wrap items-center gap-2 mb-4">
              <span :class="getIntensityBadgeClass(conflict.intensity_level)" class="px-3 py-1 text-sm font-medium rounded-full">
                {{ conflict.intensity_level }} intensity
              </span>
              <span :class="getTypeBadgeClass(conflict.conflict_type)" class="px-3 py-1 text-sm font-medium rounded-full">
                {{ formatConflictType(conflict.conflict_type) }}
              </span>
              <span v-if="conflict.is_active" class="px-3 py-1 text-sm font-medium rounded-full bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400">
                Active
              </span>
              <span v-else class="px-3 py-1 text-sm font-medium rounded-full bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-400">
                Ended
              </span>
            </div>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mb-2">{{ conflict.name }}</h1>
            <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500 dark:text-gray-400">
              <span class="flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                {{ conflict.region }}
              </span>
              <span class="flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Started: {{ formatDate(conflict.start_date) }}
              </span>
              <span v-if="conflict.end_date" class="flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Ended: {{ formatDate(conflict.end_date) }}
              </span>
            </div>
          </div>

          <!-- Conflict Content -->
          <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
              <!-- Main Content -->
              <div class="lg:col-span-2 space-y-6">
                <!-- Description -->
                <div>
                  <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Overview</h2>
                  <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap leading-relaxed">{{ conflict.description }}</p>
                </div>

                <!-- Background -->
                <div v-if="conflict.background">
                  <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Background</h2>
                  <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap leading-relaxed">{{ conflict.background }}</p>
                </div>

                <!-- Involved Actors -->
                <div v-if="conflict.actors && conflict.actors.length > 0">
                  <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Involved Actors</h2>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div
                      v-for="actor in conflict.actors"
                      :key="actor.name"
                      class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4"
                    >
                      <div class="flex items-center justify-between mb-1">
                        <span class="font-medium text-gray-900 dark:text-white">{{ actor.name }}</span>
                        <span :class="getActorSideBadgeClass(actor.side)" class="px-2 py-0.5 text-xs font-medium rounded-full">
                          {{ actor.side }}
                        </span>
                      </div>
                      <p v-if="actor.description" class="text-sm text-gray-600 dark:text-gray-400">{{ actor.description }}</p>
                    </div>
                  </div>
                </div>

                <!-- Key Events Timeline -->
                <div v-if="conflict.key_events && conflict.key_events.length > 0">
                  <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Key Events</h2>
                  <div class="space-y-4">
                    <div
                      v-for="(event, index) in conflict.key_events"
                      :key="index"
                      class="relative pl-8 pb-4 border-l-2 border-gray-200 dark:border-gray-700 last:border-l-0"
                    >
                      <div class="absolute left-[-9px] top-0 w-4 h-4 bg-blue-500 rounded-full border-2 border-white dark:border-gray-800"></div>
                      <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">{{ formatDate(event.date) }}</p>
                        <h4 class="font-medium text-gray-900 dark:text-white mb-1">{{ event.title }}</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ event.description }}</p>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Map Preview -->
                <div>
                  <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Conflict Area</h2>
                  <div class="h-64 bg-gray-200 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                    <div class="text-center">
                      <svg class="w-12 h-12 mx-auto text-gray-400 dark:text-gray-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                      </svg>
                      <p class="text-sm text-gray-500 dark:text-gray-400">{{ conflict.region }}, {{ conflict.primary_country }}</p>
                      <router-link
                        :to="{ name: 'explore-map', query: { conflict: conflict.id } }"
                        class="inline-block mt-3 text-sm text-blue-600 dark:text-blue-400 hover:underline"
                      >
                        View on Map
                      </router-link>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Sidebar -->
              <div class="space-y-6">
                <!-- Statistics -->
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                  <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Statistics</h3>
                  <div class="space-y-4">
                    <div class="text-center p-3 bg-white dark:bg-gray-600 rounded-lg">
                      <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ (conflict?.events_count || 0).toLocaleString() }}</p>
                      <p class="text-xs text-gray-500 dark:text-gray-400">Tracked Events</p>
                    </div>
                    <div v-if="conflict.estimated_casualties" class="text-center p-3 bg-white dark:bg-gray-600 rounded-lg">
                      <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ formatCasualties(conflict.estimated_casualties) }}</p>
                      <p class="text-xs text-gray-500 dark:text-gray-400">Est. Casualties</p>
                    </div>
                    <div v-if="conflict.displaced" class="text-center p-3 bg-white dark:bg-gray-600 rounded-lg">
                      <p class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ formatCasualties(conflict.displaced) }}</p>
                      <p class="text-xs text-gray-500 dark:text-gray-400">Displaced People</p>
                    </div>
                  </div>
                </div>

                <!-- Conflict Details -->
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                  <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Conflict Details</h3>
                  <dl class="space-y-3">
                    <div>
                      <dt class="text-xs text-gray-500 dark:text-gray-400">Conflict Type</dt>
                      <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ formatConflictType(conflict.conflict_type) }}</dd>
                    </div>
                    <div>
                      <dt class="text-xs text-gray-500 dark:text-gray-400">Intensity Level</dt>
                      <dd class="text-sm font-medium text-gray-900 dark:text-white capitalize">{{ conflict.intensity_level }}</dd>
                    </div>
                    <div>
                      <dt class="text-xs text-gray-500 dark:text-gray-400">Primary Country</dt>
                      <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ conflict.primary_country }}</dd>
                    </div>
                    <div>
                      <dt class="text-xs text-gray-500 dark:text-gray-400">Region</dt>
                      <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ conflict.region }}</dd>
                    </div>
                    <div>
                      <dt class="text-xs text-gray-500 dark:text-gray-400">Duration</dt>
                      <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ calculateDuration(conflict.start_date, conflict.end_date) }}</dd>
                    </div>
                  </dl>
                </div>

                <!-- Quick Actions -->
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                  <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Quick Actions</h3>
                  <div class="space-y-2">
                    <router-link
                      :to="{ name: 'explore-events', query: { conflict: conflict.id } }"
                      class="flex items-center w-full px-4 py-2 bg-white dark:bg-gray-600 border border-gray-200 dark:border-gray-500 rounded-lg text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-500 transition-colors"
                    >
                      <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                      </svg>
                      View All Events
                    </router-link>
                    <router-link
                      :to="{ name: 'explore-map', query: { conflict: conflict.id } }"
                      class="flex items-center w-full px-4 py-2 bg-white dark:bg-gray-600 border border-gray-200 dark:border-gray-500 rounded-lg text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-500 transition-colors"
                    >
                      <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                      </svg>
                      View on Map
                    </router-link>
                    <button
                      @click="copyLink"
                      class="flex items-center w-full px-4 py-2 bg-white dark:bg-gray-600 border border-gray-200 dark:border-gray-500 rounded-lg text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-500 transition-colors"
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
                  <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Create an account to access detailed analysis, set up alerts, and contribute to our database.</p>
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

        <!-- Related Conflicts -->
        <div v-if="relatedConflicts.length > 0" class="mt-8">
          <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Related Conflicts</h2>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <router-link
              v-for="related in relatedConflicts"
              :key="related.id"
              :to="{ name: 'explore-conflict-detail', params: { id: related.id } }"
              class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700 hover:shadow-lg transition-all"
            >
              <div class="flex items-center gap-2 mb-2">
                <span :class="getIntensityBadgeClass(related.intensity_level)" class="px-2 py-0.5 text-xs font-medium rounded-full">
                  {{ related.intensity_level }}
                </span>
                <span v-if="related.is_active" class="px-2 py-0.5 text-xs font-medium rounded-full bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400">
                  Active
                </span>
              </div>
              <h3 class="text-sm font-semibold text-gray-900 dark:text-white line-clamp-2">{{ related.name }}</h3>
              <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ related.region }}</p>
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

interface ConflictActor {
  name: string;
  side: 'primary' | 'secondary' | 'neutral';
  description?: string;
}

interface KeyEvent {
  date: string;
  title: string;
  description: string;
}

interface ConflictDetail {
  id: number;
  name: string;
  conflict_type: string;
  intensity_level: 'high' | 'medium' | 'low' | 'frozen';
  region: string;
  primary_country: string;
  start_date: string;
  end_date: string | null;
  is_active: boolean;
  description: string;
  background?: string;
  events_count: number;
  estimated_casualties: number | null;
  displaced?: number;
  actors: ConflictActor[];
  key_events?: KeyEvent[];
}

interface RelatedConflict {
  id: number;
  name: string;
  intensity_level: 'high' | 'medium' | 'low' | 'frozen';
  region: string;
  is_active: boolean;
}

const conflict = ref<ConflictDetail | null>(null);
const relatedConflicts = ref<RelatedConflict[]>([]);

const fetchConflict = (id: string) => {
  loading.value = true;

  // Simulated data - in production this would be an API call
  setTimeout(() => {
    const conflictData: Record<string, ConflictDetail> = {
      '1': {
        id: 1,
        name: 'Russo-Ukrainian War',
        conflict_type: 'INTERSTATE',
        intensity_level: 'high',
        region: 'Eastern Europe',
        primary_country: 'Ukraine',
        start_date: '2022-02-24',
        end_date: null,
        is_active: true,
        description: 'Full-scale invasion of Ukraine by the Russian Federation, marking a major escalation of the ongoing conflict since 2014. The war represents the largest military conflict in Europe since World War II.',
        background: 'The conflict has roots in the 2014 Euromaidan protests in Ukraine, followed by Russia\'s annexation of Crimea and the outbreak of war in the Donbas region. Tensions escalated in late 2021 with Russian military buildup along Ukraine\'s borders, culminating in a full-scale invasion on February 24, 2022.',
        events_count: 28543,
        estimated_casualties: 500000,
        displaced: 8000000,
        actors: [
          { name: 'Ukrainian Armed Forces', side: 'primary', description: 'Regular military forces of Ukraine defending against Russian invasion' },
          { name: 'Russian Armed Forces', side: 'secondary', description: 'Military forces of the Russian Federation conducting offensive operations' },
          { name: 'Wagner Group', side: 'secondary', description: 'Russian private military company' },
          { name: 'DPR Forces', side: 'secondary', description: 'Donetsk People\'s Republic separatist militia' },
          { name: 'LPR Forces', side: 'secondary', description: 'Luhansk People\'s Republic separatist militia' }
        ],
        key_events: [
          { date: '2022-02-24', title: 'Full-Scale Invasion Begins', description: 'Russia launches a multi-front invasion of Ukraine from Belarus, Russia, and Crimea.' },
          { date: '2022-04-02', title: 'Battle of Kyiv Ends', description: 'Russian forces withdraw from Kyiv Oblast after failing to capture the capital.' },
          { date: '2022-09-11', title: 'Kharkiv Counteroffensive', description: 'Ukraine liberates most of Kharkiv Oblast in a rapid counteroffensive.' },
          { date: '2022-11-11', title: 'Kherson Liberation', description: 'Ukrainian forces liberate the city of Kherson after Russian withdrawal.' }
        ]
      },
      '2': {
        id: 2,
        name: 'Israel-Hamas War',
        conflict_type: 'ETHNIC_CONFLICT',
        intensity_level: 'high',
        region: 'Middle East',
        primary_country: 'Israel',
        start_date: '2023-10-07',
        end_date: null,
        is_active: true,
        description: 'Armed conflict between Israel and Hamas following attacks on Israeli territory on October 7, 2023. The conflict has resulted in significant casualties on both sides and a major humanitarian crisis in Gaza.',
        background: 'The conflict erupted following a surprise attack by Hamas militants on southern Israel on October 7, 2023. Israel responded with a major military operation in the Gaza Strip aimed at eliminating Hamas.',
        events_count: 8976,
        estimated_casualties: 45000,
        displaced: 1900000,
        actors: [
          { name: 'Israeli Defense Forces', side: 'primary', description: 'Military forces of Israel conducting ground and air operations' },
          { name: 'Hamas', side: 'secondary', description: 'Palestinian militant organization controlling Gaza' },
          { name: 'Palestinian Islamic Jihad', side: 'secondary', description: 'Palestinian armed group' },
          { name: 'Hezbollah', side: 'secondary', description: 'Lebanese militant organization engaging in cross-border attacks' }
        ]
      }
    };

    const data = conflictData[id];
    if (data) {
      conflict.value = data;
      relatedConflicts.value = ([
        { id: 3, name: 'Sudanese Civil War', intensity_level: 'high' as const, region: 'East Africa', is_active: true },
        { id: 4, name: 'Syrian Civil War', intensity_level: 'low' as const, region: 'Middle East', is_active: true },
        { id: 5, name: 'Yemeni Civil War', intensity_level: 'medium' as const, region: 'Middle East', is_active: true }
      ] as RelatedConflict[]).filter(c => c.id !== parseInt(id));
    } else {
      conflict.value = null;
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

const getIntensityBadgeClass = (level: string) => {
  const classes: Record<string, string> = {
    'high': 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400',
    'medium': 'bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400',
    'low': 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400',
    'frozen': 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400'
  };
  return classes[level] || 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-400';
};

const getTypeBadgeClass = (type: string) => {
  const classes: Record<string, string> = {
    'CIVIL_WAR': 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400',
    'INTERSTATE': 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400',
    'INSURGENCY': 'bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400',
    'TERRORISM': 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400',
    'ETHNIC_CONFLICT': 'bg-pink-100 dark:bg-pink-900/30 text-pink-700 dark:text-pink-400',
    'BORDER_DISPUTE': 'bg-teal-100 dark:bg-teal-900/30 text-teal-700 dark:text-teal-400',
    'PROXY_WAR': 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400'
  };
  return classes[type] || 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-400';
};

const getActorSideBadgeClass = (side: string) => {
  const classes: Record<string, string> = {
    'primary': 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400',
    'secondary': 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400',
    'neutral': 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-400'
  };
  return classes[side] || 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-400';
};

const formatConflictType = (type: string) => {
  return type.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase()).join(' ');
};

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};

const formatCasualties = (num: number) => {
  if (num >= 1000000) {
    return (num / 1000000).toFixed(1) + 'M';
  } else if (num >= 1000) {
    return (num / 1000).toFixed(0) + 'K';
  }
  return num.toString();
};

const calculateDuration = (startDate: string, endDate: string | null) => {
  const start = new Date(startDate);
  const end = endDate ? new Date(endDate) : new Date();
  const diffTime = Math.abs(end.getTime() - start.getTime());
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

  if (diffDays < 30) {
    return `${diffDays} days`;
  } else if (diffDays < 365) {
    const months = Math.floor(diffDays / 30);
    return `${months} month${months > 1 ? 's' : ''}`;
  } else {
    const years = Math.floor(diffDays / 365);
    const months = Math.floor((diffDays % 365) / 30);
    return `${years} year${years > 1 ? 's' : ''}${months > 0 ? `, ${months} month${months > 1 ? 's' : ''}` : ''}`;
  }
};

onMounted(() => {
  fetchConflict(route.params.id as string);
});

watch(() => route.params.id, (newId) => {
  if (newId) {
    fetchConflict(newId as string);
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
