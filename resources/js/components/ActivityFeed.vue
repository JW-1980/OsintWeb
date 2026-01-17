<template>
  <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
    <!-- Header -->
    <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
      <div class="flex items-center justify-between">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Activity</h3>
        <button
          @click="refresh"
          :disabled="loading"
          class="text-sm text-blue-600 dark:text-blue-400 hover:underline disabled:opacity-50"
        >
          <svg v-if="loading" class="animate-spin w-4 h-4 inline mr-1" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
          </svg>
          Refresh
        </button>
      </div>
      <!-- Stats -->
      <div v-if="stats" class="mt-2 flex flex-wrap gap-4 text-xs text-gray-500 dark:text-gray-400">
        <span><strong class="text-gray-900 dark:text-white">{{ stats.contributions_today }}</strong> contributions today</span>
        <span><strong class="text-gray-900 dark:text-white">{{ stats.active_contributors_today }}</strong> active contributors</span>
        <span><strong class="text-gray-900 dark:text-white">{{ stats.events_verified_week }}</strong> events verified this week</span>
      </div>
    </div>

    <!-- Activity List -->
    <div class="divide-y divide-gray-100 dark:divide-gray-700/50 max-h-96 overflow-y-auto">
      <div
        v-for="activity in activities"
        :key="activity.id"
        class="px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors"
      >
        <div class="flex items-start space-x-3">
          <!-- User Avatar -->
          <div class="flex-shrink-0">
            <img
              v-if="activity.user?.avatar_url"
              :src="activity.user.avatar_url"
              :alt="activity.user.name"
              class="w-8 h-8 rounded-full"
            />
            <div
              v-else
              class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center"
            >
              <span class="text-sm font-medium text-gray-600 dark:text-gray-300">
                {{ activity.user?.name?.charAt(0)?.toUpperCase() || '?' }}
              </span>
            </div>
          </div>

          <!-- Activity Content -->
          <div class="flex-1 min-w-0">
            <p class="text-sm text-gray-900 dark:text-white">
              <span class="font-medium">{{ activity.user?.name || 'Anonymous' }}</span>
              <span class="text-gray-500 dark:text-gray-400"> {{ activity.description }}</span>
            </p>
            <div class="mt-1 flex items-center space-x-2 text-xs text-gray-500 dark:text-gray-400">
              <component :is="getIcon(activity.icon)" class="w-3.5 h-3.5" />
              <span>{{ activity.time_ago }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="!loading && activities.length === 0" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
        <svg class="w-10 h-10 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <p>No recent activity</p>
      </div>

      <!-- Loading State -->
      <div v-if="loading && activities.length === 0" class="px-4 py-8 text-center">
        <svg class="animate-spin w-6 h-6 mx-auto text-blue-600" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
      </div>
    </div>

    <!-- Footer -->
    <div v-if="activities.length > 0" class="px-4 py-2 bg-gray-50 dark:bg-gray-700/30 border-t border-gray-200 dark:border-gray-700 rounded-b-xl">
      <p class="text-xs text-center text-gray-500 dark:text-gray-400">
        Showing {{ activities.length }} recent contributions
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, h, FunctionalComponent } from 'vue';
import { useActivityLog, PublicActivity, ActivityStats } from '@/composables/useActivityLog';

const props = defineProps<{
  limit?: number;
  days?: number;
}>();

const { publicFeed, stats: activityStats, loading, fetchPublicFeed, fetchStats } = useActivityLog();

const activities = ref<PublicActivity[]>([]);
const stats = ref<ActivityStats | null>(null);

const refresh = async () => {
  await Promise.all([
    fetchPublicFeed(props.limit || 15, props.days || 7),
    fetchStats(),
  ]);
  activities.value = publicFeed.value;
  stats.value = activityStats.value;
};

// Icon components (simplified SVG icons)
const iconComponents: Record<string, FunctionalComponent> = {
  'plus-circle': () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24', class: 'w-3.5 h-3.5' }, [
    h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z' })
  ]),
  'check-badge': () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24', class: 'w-3.5 h-3.5' }, [
    h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z' })
  ]),
  'link': () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24', class: 'w-3.5 h-3.5' }, [
    h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1' })
  ]),
  'document-arrow-up': () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24', class: 'w-3.5 h-3.5' }, [
    h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M9 8.25H7.5a2.25 2.25 0 00-2.25 2.25v9a2.25 2.25 0 002.25 2.25h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25H15m0-3l-3-3m0 0l-3 3m3-3V15' })
  ]),
  'light-bulb': () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24', class: 'w-3.5 h-3.5' }, [
    h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z' })
  ]),
  'chat-bubble-left': () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24', class: 'w-3.5 h-3.5' }, [
    h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z' })
  ]),
  'bookmark': () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24', class: 'w-3.5 h-3.5' }, [
    h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z' })
  ]),
  'information-circle': () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24', class: 'w-3.5 h-3.5' }, [
    h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z' })
  ]),
};

const getIcon = (iconName: string): FunctionalComponent => {
  return iconComponents[iconName] || iconComponents['information-circle'];
};

onMounted(refresh);
</script>
