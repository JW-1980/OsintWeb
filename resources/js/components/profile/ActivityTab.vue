<script setup lang="ts">
interface Activity {
  type: string;
  description: string;
  icon: string;
  ip_address: string;
  created_at: string;
}

defineProps<{
  activityHistory: Activity[];
}>();

const getActivityIcon = (type: string) => {
  switch (type) {
    case 'create':
      return 'M12 4v16m8-8H4';
    case 'edit':
      return 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z';
    case 'verify':
      return 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z';
    case 'comment':
      return 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z';
    case 'download':
      return 'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4';
    default:
      return 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z';
  }
};
</script>

<template>
  <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Activity History</h2>
    <div v-if="activityHistory.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
      <svg class="w-12 h-12 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
      <p>No activity recorded yet</p>
    </div>
    <div v-else class="space-y-4">
      <div
        v-for="(activity, index) in activityHistory"
        :key="index"
        class="flex items-start space-x-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg"
      >
        <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
          <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getActivityIcon(activity.icon)" />
          </svg>
        </div>
        <div class="flex-1">
          <p class="text-gray-900 dark:text-white font-medium">{{ activity.description }}</p>
          <p class="text-sm text-gray-500 dark:text-gray-400">
            {{ new Date(activity.created_at).toLocaleString() }}
            <span v-if="activity.ip_address" class="ml-2">from {{ activity.ip_address }}</span>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>
