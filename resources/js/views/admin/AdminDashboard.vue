<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
          <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Admin Dashboard</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage your OSINT platform</p>
          </div>
          <div class="mt-4 sm:mt-0 flex flex-wrap gap-3">
            <button @click="refreshData" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
              <svg class="w-4 h-4 mr-2" :class="{ 'animate-spin': isLoading }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
              </svg>
              Refresh
            </button>
            <button @click="exportData" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
              </svg>
              Export
            </button>
            <button @click="showQuickAdd = true" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition-colors">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              Quick Add
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Stats Grid -->
      <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
        <div v-for="stat in statsCards" :key="stat.label" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 hover:shadow-md transition-shadow cursor-pointer" @click="navigateToStat(stat)">
          <div class="flex items-center justify-between mb-2">
            <div :class="['p-2 rounded-lg', stat.bgColor]">
              <component :is="stat.icon" :class="['w-5 h-5', stat.iconColor]" />
            </div>
            <span v-if="stat.change" :class="['text-xs font-medium', stat.change > 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400']">
              {{ stat.change > 0 ? '+' : '' }}{{ stat.change }}%
            </span>
          </div>
          <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stat.value }}</p>
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ stat.label }}</p>
        </div>
      </div>

      <!-- Main Content Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <!-- Sidebar - Quick Actions -->
        <div class="lg:col-span-1 space-y-6">
          <!-- Admin Navigation -->
          <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
              <h2 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wider">Management</h2>
            </div>
            <nav class="p-2">
              <router-link v-for="item in managementLinks" :key="item.path" :to="item.path" class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors group">
                <component :is="item.icon" class="w-5 h-5 mr-3 text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300" />
                <span class="flex-1">{{ item.label }}</span>
                <span v-if="item.badge" class="px-2 py-0.5 text-xs font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-full">{{ item.badge }}</span>
              </router-link>
            </nav>
          </div>

          <!-- System Status -->
          <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
              <h2 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wider">System Status</h2>
            </div>
            <div class="p-4 space-y-4">
              <div v-for="status in systemStatus" :key="status.name" class="flex items-center justify-between">
                <span class="text-sm text-gray-600 dark:text-gray-400">{{ status.name }}</span>
                <span :class="['px-2 py-1 text-xs font-medium rounded-full', status.healthy ? 'bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400' : 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400']">
                  {{ status.healthy ? 'Healthy' : 'Issue' }}
                </span>
              </div>
            </div>
          </div>

          <!-- Quick Stats -->
          <div class="bg-gradient-to-br from-blue-600 to-indigo-700 rounded-xl shadow-sm p-6 text-white">
            <h3 class="text-sm font-semibold uppercase tracking-wider opacity-80">This Week</h3>
            <p class="text-3xl font-bold mt-2">{{ weeklyStats.events }}</p>
            <p class="text-sm opacity-80">New events recorded</p>
            <div class="mt-4 pt-4 border-t border-white/20 grid grid-cols-2 gap-4">
              <div>
                <p class="text-2xl font-bold">{{ weeklyStats.verified }}</p>
                <p class="text-xs opacity-80">Verified</p>
              </div>
              <div>
                <p class="text-2xl font-bold">{{ weeklyStats.pending }}</p>
                <p class="text-xs opacity-80">Pending</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Main Content Area -->
        <div class="lg:col-span-3 space-y-6">
          <!-- Tabs -->
          <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="border-b border-gray-200 dark:border-gray-700">
              <nav class="flex -mb-px overflow-x-auto">
                <button v-for="tab in tabs" :key="tab.id" @click="activeTab = tab.id" :class="['px-6 py-4 text-sm font-medium whitespace-nowrap border-b-2 transition-colors', activeTab === tab.id ? 'border-blue-600 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300']">
                  {{ tab.label }}
                </button>
              </nav>
            </div>

            <!-- Recent Activity Tab -->
            <div v-if="activeTab === 'activity'" class="divide-y divide-gray-200 dark:divide-gray-700">
              <div v-for="activity in recentActivity" :key="activity.id" class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                <div class="flex items-start space-x-4">
                  <div :class="['p-2 rounded-lg flex-shrink-0', getActivityColor(activity.type)]">
                    <component :is="getActivityIcon(activity.type)" class="w-4 h-4" />
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ activity.title }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ activity.description }}</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">by {{ activity.user }} &middot; {{ activity.time }}</p>
                  </div>
                  <button class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                    </svg>
                  </button>
                </div>
              </div>
              <div class="p-4 text-center">
                <button class="text-sm text-blue-600 dark:text-blue-400 hover:underline">View all activity</button>
              </div>
            </div>

            <!-- Pending Reviews Tab -->
            <div v-if="activeTab === 'pending'" class="p-4">
              <div class="space-y-4">
                <div v-for="item in pendingReviews" :key="item.id" class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                  <div class="flex items-center space-x-4">
                    <div class="w-10 h-10 bg-yellow-100 dark:bg-yellow-900/30 rounded-lg flex items-center justify-center">
                      <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                    </div>
                    <div>
                      <p class="text-sm font-medium text-gray-900 dark:text-white">{{ item.title }}</p>
                      <p class="text-xs text-gray-500 dark:text-gray-400">{{ item.type }} &middot; {{ item.submitted }}</p>
                    </div>
                  </div>
                  <div class="flex space-x-2">
                    <button class="px-3 py-1.5 text-xs font-medium text-green-600 dark:text-green-400 bg-green-100 dark:bg-green-900/30 rounded-lg hover:bg-green-200 dark:hover:bg-green-900/50 transition-colors">Approve</button>
                    <button class="px-3 py-1.5 text-xs font-medium text-red-600 dark:text-red-400 bg-red-100 dark:bg-red-900/30 rounded-lg hover:bg-red-200 dark:hover:bg-red-900/50 transition-colors">Reject</button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Analytics Tab -->
            <div v-if="activeTab === 'analytics'" class="p-6">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                  <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Events by Type</h3>
                  <div class="space-y-3">
                    <div v-for="stat in eventsByType" :key="stat.type" class="flex items-center">
                      <span class="w-32 text-sm text-gray-600 dark:text-gray-400">{{ stat.type }}</span>
                      <div class="flex-1 h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                        <div :class="['h-full rounded-full', stat.color]" :style="{ width: stat.percentage + '%' }"></div>
                      </div>
                      <span class="ml-3 text-sm font-medium text-gray-900 dark:text-white">{{ stat.count }}</span>
                    </div>
                  </div>
                </div>
                <div class="space-y-4">
                  <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Top Contributors</h3>
                  <div class="space-y-3">
                    <div v-for="(user, index) in topContributors" :key="user.id" class="flex items-center">
                      <span class="w-6 text-sm font-medium text-gray-400">{{ index + 1 }}</span>
                      <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white text-xs font-semibold mr-3">
                        {{ user.name.charAt(0) }}
                      </div>
                      <span class="flex-1 text-sm text-gray-900 dark:text-white">{{ user.name }}</span>
                      <span class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ user.contributions }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Alerts Tab -->
            <div v-if="activeTab === 'alerts'" class="p-4">
              <div class="space-y-3">
                <div v-for="alert in systemAlerts" :key="alert.id" :class="['p-4 rounded-lg border', getAlertClass(alert.level)]">
                  <div class="flex items-start">
                    <component :is="getAlertIcon(alert.level)" class="w-5 h-5 mr-3 flex-shrink-0" />
                    <div class="flex-1">
                      <p class="text-sm font-medium">{{ alert.title }}</p>
                      <p class="text-sm opacity-80 mt-1">{{ alert.message }}</p>
                      <p class="text-xs opacity-60 mt-2">{{ alert.time }}</p>
                    </div>
                    <button class="text-current opacity-60 hover:opacity-100">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                      </svg>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Quick Actions Grid -->
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <button v-for="action in quickActions" :key="action.label" @click="action.handler" class="flex flex-col items-center p-4 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 hover:shadow-md hover:border-blue-300 dark:hover:border-blue-600 transition-all group">
              <div :class="['p-3 rounded-xl mb-3 transition-colors', action.bgColor, 'group-hover:' + action.hoverBg]">
                <component :is="action.icon" :class="['w-6 h-6', action.iconColor]" />
              </div>
              <span class="text-sm font-medium text-gray-900 dark:text-white">{{ action.label }}</span>
              <span class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ action.description }}</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Child routes -->
      <router-view />
    </div>

    <!-- Quick Add Modal -->
    <div v-if="showQuickAdd" class="fixed inset-0 z-50 overflow-y-auto" @click.self="showQuickAdd = false">
      <div class="flex items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-black/50" @click="showQuickAdd = false"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-md w-full p-6">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Quick Add</h3>
          <div class="grid grid-cols-2 gap-4">
            <button v-for="item in quickAddItems" :key="item.label" @click="handleQuickAdd(item.type)" class="flex flex-col items-center p-4 rounded-xl border-2 border-dashed border-gray-200 dark:border-gray-700 hover:border-blue-400 dark:hover:border-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-all">
              <component :is="item.icon" class="w-8 h-8 text-gray-400 mb-2" />
              <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ item.label }}</span>
            </button>
          </div>
          <button @click="showQuickAdd = false" class="mt-4 w-full py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">Cancel</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, h } from 'vue';
import { useRouter } from 'vue-router';
import { useApi } from '@/composables/useApi';

const router = useRouter();
const api = useApi();

const isLoading = ref(false);
const activeTab = ref('activity');
const showQuickAdd = ref(false);

// Icon components as render functions
const UsersIcon = () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
  h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z' })
]);
const EventIcon = () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
  h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2' })
]);
const GlobeIcon = () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
  h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z' })
]);
const ShieldIcon = () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
  h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z' })
]);
const CogIcon = () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
  h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z' }),
  h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M15 12a3 3 0 11-6 0 3 3 0 016 0z' })
]);
const DocumentIcon = () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
  h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' })
]);
const ChartIcon = () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
  h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z' })
]);
const WarningIcon = () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
  h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z' })
]);
const InfoIcon = () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
  h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z' })
]);
const PlusIcon = () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
  h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M12 4v16m8-8H4' })
]);
const MapIcon = () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
  h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7' })
]);
const DatabaseIcon = () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
  h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4' })
]);
const KeyIcon = () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
  h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z' })
]);
const BadgeIcon = () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
  h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z' })
]);
const BotIcon = () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
  h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z' })
]);
const LightbulbIcon = () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
  h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z' })
]);
const InboxIcon = () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
  h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4' })
]);
const ReportIcon = () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
  h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' })
]);
const LockIcon = () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
  h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z' })
]);
const MailIcon = () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
  h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z' })
]);

// Stats Cards
const statsCards = ref([
  { label: 'Users', value: 24, change: 12, icon: UsersIcon, bgColor: 'bg-blue-100 dark:bg-blue-900/30', iconColor: 'text-blue-600 dark:text-blue-400', path: '/admin/users' },
  { label: 'Events', value: 156, change: 23, icon: EventIcon, bgColor: 'bg-green-100 dark:bg-green-900/30', iconColor: 'text-green-600 dark:text-green-400', path: '/events' },
  { label: 'Conflicts', value: 12, change: 0, icon: GlobeIcon, bgColor: 'bg-yellow-100 dark:bg-yellow-900/30', iconColor: 'text-yellow-600 dark:text-yellow-400', path: '/admin/conflicts' },
  { label: 'Actors', value: 89, change: 5, icon: ShieldIcon, bgColor: 'bg-purple-100 dark:bg-purple-900/30', iconColor: 'text-purple-600 dark:text-purple-400', path: '/admin/actors' },
  { label: 'Equipment', value: 342, change: 8, icon: DatabaseIcon, bgColor: 'bg-indigo-100 dark:bg-indigo-900/30', iconColor: 'text-indigo-600 dark:text-indigo-400', path: '/equipment' },
  { label: 'Zones', value: 28, change: -2, icon: MapIcon, bgColor: 'bg-red-100 dark:bg-red-900/30', iconColor: 'text-red-600 dark:text-red-400', path: '/zones' },
]);

// Management Links
const managementLinks = ref([
  { label: 'Users', path: '/admin/users', icon: UsersIcon, badge: null },
  { label: 'Roles', path: '/admin/roles', icon: KeyIcon, badge: null },
  { label: 'Permissions', path: '/admin/permissions', icon: LockIcon, badge: null },
  { label: 'Actors', path: '/admin/actors', icon: ShieldIcon, badge: null },
  { label: 'Conflicts', path: '/admin/conflicts', icon: GlobeIcon, badge: null },
  { label: 'Events', path: '/admin/events', icon: EventIcon, badge: null },
  { label: 'Equipment', path: '/admin/equipment', icon: DatabaseIcon, badge: null },
  { label: 'Zones', path: '/admin/zones', icon: MapIcon, badge: null },
  { label: 'Achievements', path: '/admin/achievements', icon: BadgeIcon, badge: null },
  { label: 'Agents', path: '/admin/agents', icon: BotIcon, badge: null },
  { label: 'Skills', path: '/admin/skills', icon: LightbulbIcon, badge: null },
  { label: 'Tips', path: '/admin/tips', icon: InboxIcon, badge: null },
  { label: 'Reports', path: '/admin/reports', icon: ReportIcon, badge: null },
  { label: 'Audit Logs', path: '/admin/audit-logs', icon: DocumentIcon, badge: null },
  { label: 'Email Templates', path: '/admin/email-templates', icon: MailIcon, badge: null },
  { label: 'Site Analytics', path: '/admin/site-analytics', icon: ChartIcon, badge: null },
  { label: 'A/B Testing', path: '/admin/ab-testing', icon: LightbulbIcon, badge: null },
  { label: 'Settings', path: '/admin/settings', icon: CogIcon, badge: null },
]);

// System Status
const systemStatus = ref([
  { name: 'Database', healthy: true },
  { name: 'Cache', healthy: true },
  { name: 'Queue', healthy: true },
  { name: 'Storage', healthy: true },
  { name: 'API', healthy: true },
]);

// Weekly Stats
const weeklyStats = ref({
  events: 47,
  verified: 38,
  pending: 9,
});

// Tabs
const tabs = [
  { id: 'activity', label: 'Recent Activity' },
  { id: 'pending', label: 'Pending Reviews' },
  { id: 'analytics', label: 'Quick Analytics' },
  { id: 'alerts', label: 'System Alerts' },
];

// Recent Activity
const recentActivity = ref([
  { id: 1, type: 'event', title: 'New event created', description: 'Artillery strike reported in Eastern Ukraine', user: 'John Doe', time: '2 hours ago' },
  { id: 2, type: 'user', title: 'New user registered', description: 'analyst@example.com joined the platform', user: 'System', time: '4 hours ago' },
  { id: 3, type: 'event', title: 'Event verified', description: 'Equipment sighting confirmed by moderator', user: 'Jane Smith', time: '6 hours ago' },
  { id: 4, type: 'system', title: 'System update', description: 'Database backup completed successfully', user: 'System', time: '12 hours ago' },
  { id: 5, type: 'user', title: 'Role updated', description: 'User promoted to analyst role', user: 'Admin', time: '1 day ago' },
]);

// Pending Reviews
const pendingReviews = ref([
  { id: 1, title: 'Tank sighting near Bakhmut', type: 'Event', submitted: '30 min ago' },
  { id: 2, title: 'New actor: Wagner Group', type: 'Actor', submitted: '2 hours ago' },
  { id: 3, title: 'Control zone update', type: 'Zone', submitted: '5 hours ago' },
]);

// Events by Type
const eventsByType = ref([
  { type: 'Combat', count: 45, percentage: 30, color: 'bg-red-500' },
  { type: 'Airstrike', count: 32, percentage: 21, color: 'bg-orange-500' },
  { type: 'Equipment', count: 28, percentage: 18, color: 'bg-blue-500' },
  { type: 'Movement', count: 25, percentage: 16, color: 'bg-green-500' },
  { type: 'Other', count: 22, percentage: 15, color: 'bg-gray-500' },
]);

// Top Contributors
const topContributors = ref([
  { id: 1, name: 'John Analyst', contributions: 156 },
  { id: 2, name: 'Sarah Intel', contributions: 134 },
  { id: 3, name: 'Mike Research', contributions: 98 },
  { id: 4, name: 'Emma Data', contributions: 87 },
  { id: 5, name: 'Alex Monitor', contributions: 72 },
]);

// System Alerts
const systemAlerts = ref([
  { id: 1, level: 'warning', title: 'High API usage', message: 'API usage has exceeded 80% of the monthly quota.', time: '1 hour ago' },
  { id: 2, level: 'info', title: 'Scheduled maintenance', message: 'System maintenance scheduled for Sunday 2:00 AM UTC.', time: '3 hours ago' },
]);

// Quick Actions
const quickActions = ref([
  { label: 'Add Event', description: 'Create new event', icon: PlusIcon, bgColor: 'bg-blue-100 dark:bg-blue-900/30', hoverBg: 'bg-blue-200', iconColor: 'text-blue-600 dark:text-blue-400', handler: () => router.push('/events/new') },
  { label: 'Import Data', description: 'Bulk import', icon: DatabaseIcon, bgColor: 'bg-green-100 dark:bg-green-900/30', hoverBg: 'bg-green-200', iconColor: 'text-green-600 dark:text-green-400', handler: () => {} },
  { label: 'Run Report', description: 'Generate report', icon: ChartIcon, bgColor: 'bg-purple-100 dark:bg-purple-900/30', hoverBg: 'bg-purple-200', iconColor: 'text-purple-600 dark:text-purple-400', handler: () => router.push('/analytics') },
  { label: 'View Map', description: 'Open map view', icon: MapIcon, bgColor: 'bg-indigo-100 dark:bg-indigo-900/30', hoverBg: 'bg-indigo-200', iconColor: 'text-indigo-600 dark:text-indigo-400', handler: () => router.push('/map') },
]);

// Quick Add Items
const quickAddItems = ref([
  { label: 'New Event', type: 'event', icon: EventIcon },
  { label: 'New Actor', type: 'actor', icon: ShieldIcon },
  { label: 'New Conflict', type: 'conflict', icon: GlobeIcon },
  { label: 'New Zone', type: 'zone', icon: MapIcon },
]);

// Methods
const refreshData = async () => {
  isLoading.value = true;
  try {
    const response = await api.get<any>('/admin/stats');
    if (response) {
      // Update stats
    }
  } catch (error) {
    console.error('Failed to refresh data:', error);
  } finally {
    isLoading.value = false;
  }
};

const exportData = () => {
  // Export functionality
  console.log('Exporting data...');
};

const navigateToStat = (stat: any) => {
  if (stat.path) {
    router.push(stat.path);
  }
};

const handleQuickAdd = (type: string) => {
  showQuickAdd.value = false;
  switch (type) {
    case 'event':
      router.push('/events/new');
      break;
    case 'actor':
      router.push('/admin/actors/new');
      break;
    case 'conflict':
      router.push('/admin/conflicts/new');
      break;
    case 'zone':
      router.push('/zones/new');
      break;
  }
};

const getActivityColor = (type: string) => {
  switch (type) {
    case 'event': return 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400';
    case 'user': return 'bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400';
    case 'system': return 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400';
    default: return 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400';
  }
};

const getActivityIcon = (type: string) => {
  switch (type) {
    case 'event': return EventIcon;
    case 'user': return UsersIcon;
    default: return InfoIcon;
  }
};

const getAlertClass = (level: string) => {
  switch (level) {
    case 'error': return 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800 text-red-700 dark:text-red-400';
    case 'warning': return 'bg-yellow-50 dark:bg-yellow-900/20 border-yellow-200 dark:border-yellow-800 text-yellow-700 dark:text-yellow-400';
    default: return 'bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-400';
  }
};

const getAlertIcon = (level: string) => {
  switch (level) {
    case 'error':
    case 'warning':
      return WarningIcon;
    default:
      return InfoIcon;
  }
};

onMounted(() => {
  refreshData();
});
</script>
