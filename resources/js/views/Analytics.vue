<template>
  <div class="p-6 bg-gray-50 dark:bg-gray-900 min-h-screen">
    <div class="max-w-7xl mx-auto">
      <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Analytics</h1>
        <div class="flex items-center space-x-3">
          <select
            v-model="timeRange"
            class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white"
          >
            <option value="7d">Last 7 days</option>
            <option value="30d">Last 30 days</option>
            <option value="90d">Last 90 days</option>
            <option value="1y">Last year</option>
          </select>
          <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            Export Report
          </button>
        </div>
      </div>

      <!-- Key Metrics -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Events</p>
              <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ (stats?.totalEvents || 0).toLocaleString() }}</p>
            </div>
            <div class="w-12 h-12 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
              <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
              </svg>
            </div>
          </div>
          <div class="mt-2 flex items-center text-sm">
            <span class="text-green-600 dark:text-green-400 font-medium">+12.5%</span>
            <span class="text-gray-500 dark:text-gray-400 ml-2">vs last period</span>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Active Conflicts</p>
              <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ stats.activeConflicts }}</p>
            </div>
            <div class="w-12 h-12 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
              <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z" />
              </svg>
            </div>
          </div>
          <div class="mt-2 flex items-center text-sm">
            <span class="text-red-600 dark:text-red-400 font-medium">+2</span>
            <span class="text-gray-500 dark:text-gray-400 ml-2">new this month</span>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Verified Events</p>
              <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ stats.verifiedEvents }}%</p>
            </div>
            <div class="w-12 h-12 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
              <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
          <div class="mt-2 flex items-center text-sm">
            <span class="text-green-600 dark:text-green-400 font-medium">+5.2%</span>
            <span class="text-gray-500 dark:text-gray-400 ml-2">improvement</span>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Active Contributors</p>
              <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ stats.activeContributors }}</p>
            </div>
            <div class="w-12 h-12 rounded-full bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
              <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
            </div>
          </div>
          <div class="mt-2 flex items-center text-sm">
            <span class="text-green-600 dark:text-green-400 font-medium">+8</span>
            <span class="text-gray-500 dark:text-gray-400 ml-2">this week</span>
          </div>
        </div>
      </div>

      <!-- Charts Row -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Events Over Time -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Events Over Time</h3>
          <div class="h-64 flex items-end justify-between space-x-2">
            <div v-for="(value, index) in eventsTrend" :key="index" class="flex-1 flex flex-col items-center">
              <div
                class="w-full bg-blue-500 dark:bg-blue-600 rounded-t transition-all duration-300 hover:bg-blue-600 dark:hover:bg-blue-500"
                :style="{ height: `${(value / maxEventValue) * 200}px` }"
              ></div>
              <span class="text-xs text-gray-500 dark:text-gray-400 mt-2">{{ getMonthLabel(index) }}</span>
            </div>
          </div>
        </div>

        <!-- Events by Type -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Events by Type</h3>
          <div class="space-y-4">
            <div v-for="eventType in eventTypes" :key="eventType.name">
              <div class="flex items-center justify-between mb-1">
                <span class="text-sm text-gray-600 dark:text-gray-400">{{ eventType.name }}</span>
                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ eventType.count }}</span>
              </div>
              <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                <div
                  class="h-2 rounded-full transition-all duration-300"
                  :class="eventType.color"
                  :style="{ width: `${eventType.percentage}%` }"
                ></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Activity and Top Actors -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Regional Activity -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Regional Activity</h3>
          <div class="space-y-3">
            <div v-for="region in regions" :key="region.name" class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
              <span class="text-gray-900 dark:text-white">{{ region.name }}</span>
              <div class="flex items-center space-x-2">
                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ region.events }} events</span>
                <span :class="region.trend > 0 ? 'text-green-500' : 'text-red-500'" class="text-xs">
                  {{ region.trend > 0 ? '+' : '' }}{{ region.trend }}%
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Most Active Actors -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Most Active Actors</h3>
          <div class="space-y-3">
            <div v-for="(actor, index) in topActors" :key="actor.name" class="flex items-center space-x-3">
              <span class="text-lg font-bold text-gray-400 dark:text-gray-500 w-6">{{ index + 1 }}</span>
              <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-sm font-medium" :style="{ backgroundColor: actor.color }">
                {{ actor.name.charAt(0) }}
              </div>
              <div class="flex-1">
                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ actor.name }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ actor.events }} events</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Recent Activity</h3>
          <div class="space-y-3">
            <div v-for="activity in recentActivity" :key="activity.id" class="flex items-start space-x-3">
              <div class="w-2 h-2 rounded-full mt-2" :class="activity.color"></div>
              <div>
                <p class="text-sm text-gray-900 dark:text-white">{{ activity.description }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ activity.time }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Bottom Row -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Equipment Losses -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Equipment Losses by Category</h3>
          <div class="grid grid-cols-2 gap-4">
            <div v-for="category in equipmentCategories" :key="category.name" class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg text-center">
              <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ category.count }}</p>
              <p class="text-sm text-gray-500 dark:text-gray-400">{{ category.name }}</p>
              <p class="text-xs mt-1" :class="category.change > 0 ? 'text-red-500' : 'text-green-500'">
                {{ category.change > 0 ? '+' : '' }}{{ category.change }}% vs last period
              </p>
            </div>
          </div>
        </div>

        <!-- Data Quality -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Data Quality Metrics</h3>
          <div class="grid grid-cols-2 gap-4">
            <div class="text-center">
              <div class="relative w-24 h-24 mx-auto">
                <svg class="w-24 h-24 transform -rotate-90">
                  <circle cx="48" cy="48" r="40" fill="none" stroke-width="8" class="stroke-gray-200 dark:stroke-gray-700"></circle>
                  <circle cx="48" cy="48" r="40" fill="none" stroke-width="8" stroke-linecap="round" class="stroke-green-500" :stroke-dasharray="`${dataQuality.sourcesVerified * 2.51} 251`"></circle>
                </svg>
                <div class="absolute inset-0 flex items-center justify-center">
                  <span class="text-xl font-bold text-gray-900 dark:text-white">{{ dataQuality.sourcesVerified }}%</span>
                </div>
              </div>
              <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Sources Verified</p>
            </div>
            <div class="text-center">
              <div class="relative w-24 h-24 mx-auto">
                <svg class="w-24 h-24 transform -rotate-90">
                  <circle cx="48" cy="48" r="40" fill="none" stroke-width="8" class="stroke-gray-200 dark:stroke-gray-700"></circle>
                  <circle cx="48" cy="48" r="40" fill="none" stroke-width="8" stroke-linecap="round" class="stroke-blue-500" :stroke-dasharray="`${dataQuality.geolocated * 2.51} 251`"></circle>
                </svg>
                <div class="absolute inset-0 flex items-center justify-center">
                  <span class="text-xl font-bold text-gray-900 dark:text-white">{{ dataQuality.geolocated }}%</span>
                </div>
              </div>
              <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Geolocated</p>
            </div>
            <div class="text-center">
              <div class="relative w-24 h-24 mx-auto">
                <svg class="w-24 h-24 transform -rotate-90">
                  <circle cx="48" cy="48" r="40" fill="none" stroke-width="8" class="stroke-gray-200 dark:stroke-gray-700"></circle>
                  <circle cx="48" cy="48" r="40" fill="none" stroke-width="8" stroke-linecap="round" class="stroke-purple-500" :stroke-dasharray="`${dataQuality.mediaCaptured * 2.51} 251`"></circle>
                </svg>
                <div class="absolute inset-0 flex items-center justify-center">
                  <span class="text-xl font-bold text-gray-900 dark:text-white">{{ dataQuality.mediaCaptured }}%</span>
                </div>
              </div>
              <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">With Media</p>
            </div>
            <div class="text-center">
              <div class="relative w-24 h-24 mx-auto">
                <svg class="w-24 h-24 transform -rotate-90">
                  <circle cx="48" cy="48" r="40" fill="none" stroke-width="8" class="stroke-gray-200 dark:stroke-gray-700"></circle>
                  <circle cx="48" cy="48" r="40" fill="none" stroke-width="8" stroke-linecap="round" class="stroke-yellow-500" :stroke-dasharray="`${dataQuality.crossReferenced * 2.51} 251`"></circle>
                </svg>
                <div class="absolute inset-0 flex items-center justify-center">
                  <span class="text-xl font-bold text-gray-900 dark:text-white">{{ dataQuality.crossReferenced }}%</span>
                </div>
              </div>
              <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Cross-Referenced</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';

const timeRange = ref('30d');

const stats = ref({
  totalEvents: 15847,
  activeConflicts: 23,
  verifiedEvents: 78,
  activeContributors: 156
});

const eventsTrend = ref([120, 145, 180, 210, 195, 230, 265, 290, 310, 285, 340, 380]);
const maxEventValue = computed(() => Math.max(...eventsTrend.value));

const getMonthLabel = (index: number) => {
  const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
  const currentMonth = new Date().getMonth();
  return months[(currentMonth - 11 + index + 12) % 12];
};

const eventTypes = ref([
  { name: 'Combat Engagement', count: 4521, percentage: 75, color: 'bg-red-500' },
  { name: 'Airstrike', count: 2834, percentage: 60, color: 'bg-orange-500' },
  { name: 'Artillery Strike', count: 2156, percentage: 50, color: 'bg-yellow-500' },
  { name: 'Equipment Loss', count: 1892, percentage: 45, color: 'bg-blue-500' },
  { name: 'Troop Movement', count: 1567, percentage: 35, color: 'bg-green-500' }
]);

const regions = ref([
  { name: 'Eastern Europe', events: 8450, trend: 15 },
  { name: 'Middle East', events: 4230, trend: -5 },
  { name: 'Africa', events: 2180, trend: 8 },
  { name: 'Asia Pacific', events: 987, trend: 3 }
]);

const topActors = ref([
  { name: 'Russian Armed Forces', events: 4521, color: '#1E40AF' },
  { name: 'Ukrainian Armed Forces', events: 3892, color: '#FFD700' },
  { name: 'Wagner Group', events: 1234, color: '#374151' },
  { name: 'Syrian Army', events: 856, color: '#DC2626' },
  { name: 'Hezbollah', events: 623, color: '#059669' }
]);

const recentActivity = ref([
  { id: 1, description: 'New airstrike reported in Kherson', time: '5 minutes ago', color: 'bg-red-500' },
  { id: 2, description: 'Equipment loss verified in Bakhmut', time: '23 minutes ago', color: 'bg-yellow-500' },
  { id: 3, description: 'Troop movement detected near Zaporizhzhia', time: '1 hour ago', color: 'bg-blue-500' },
  { id: 4, description: 'New conflict zone added', time: '2 hours ago', color: 'bg-green-500' },
  { id: 5, description: 'Source verification completed', time: '3 hours ago', color: 'bg-purple-500' }
]);

const equipmentCategories = ref([
  { name: 'Tanks', count: 2341, change: 12 },
  { name: 'APCs/IFVs', count: 3567, change: 8 },
  { name: 'Artillery', count: 1892, change: -3 },
  { name: 'Aircraft', count: 312, change: 5 }
]);

const dataQuality = ref({
  sourcesVerified: 78,
  geolocated: 92,
  mediaCaptured: 65,
  crossReferenced: 54
});
</script>
