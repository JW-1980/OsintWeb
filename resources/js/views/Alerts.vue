<script setup lang="ts">
import { ref, reactive, computed } from 'vue';
import type { EventType } from '@/types';

interface Alert {
  id: number;
  name: string;
  type: 'keyword' | 'region' | 'event_type' | 'actor';
  criteria: string;
  enabled: boolean;
  createdAt: string;
  triggeredCount: number;
  lastTriggered?: string;
}

interface AlertHistory {
  id: number;
  alertId: number;
  alertName: string;
  message: string;
  eventId?: number;
  eventTitle?: string;
  triggeredAt: string;
  read: boolean;
}

const activeTab = ref<'alerts' | 'history' | 'preferences'>('alerts');
const showCreateModal = ref(false);

const alerts = ref<Alert[]>([
  { id: 1, name: 'Ukraine Combat Events', type: 'region', criteria: 'Ukraine', enabled: true, createdAt: '2026-01-10', triggeredCount: 45, lastTriggered: '2026-01-16 09:30' },
  { id: 2, name: 'Missile Strike Alert', type: 'event_type', criteria: 'missile_strike', enabled: true, createdAt: '2026-01-08', triggeredCount: 12, lastTriggered: '2026-01-15 14:22' },
  { id: 3, name: 'Equipment Keywords', type: 'keyword', criteria: 'tank, artillery, drone', enabled: false, createdAt: '2026-01-05', triggeredCount: 89, lastTriggered: '2026-01-14 08:15' },
  { id: 4, name: 'Civilian Casualties', type: 'event_type', criteria: 'civilian_casualties', enabled: true, createdAt: '2026-01-01', triggeredCount: 23, lastTriggered: '2026-01-13 16:45' }
]);

const alertHistory = ref<AlertHistory[]>([
  { id: 1, alertId: 1, alertName: 'Ukraine Combat Events', message: 'New combat engagement reported near Kharkiv', eventId: 123, eventTitle: 'Artillery exchange in northern sector', triggeredAt: '2026-01-16 09:30', read: false },
  { id: 2, alertId: 2, alertName: 'Missile Strike Alert', message: 'Missile strike detected in Odesa region', eventId: 124, eventTitle: 'Multiple impacts reported', triggeredAt: '2026-01-15 14:22', read: false },
  { id: 3, alertId: 1, alertName: 'Ukraine Combat Events', message: 'Troop movements observed near Donetsk', eventId: 125, eventTitle: 'Convoy spotted heading east', triggeredAt: '2026-01-15 11:00', read: true },
  { id: 4, alertId: 4, alertName: 'Civilian Casualties', message: 'Civilian casualties reported in Zaporizhzhia', eventId: 126, eventTitle: 'Residential area struck', triggeredAt: '2026-01-14 18:30', read: true }
]);

const notificationPreferences = reactive({
  email: {
    enabled: true,
    instant: false,
    digest: true,
    digestFrequency: 'daily' as 'daily' | 'weekly'
  },
  push: {
    enabled: true,
    sound: true,
    desktop: true
  },
  inApp: {
    enabled: true,
    badge: true
  }
});

const newAlert = reactive({
  name: '',
  type: 'keyword' as 'keyword' | 'region' | 'event_type' | 'actor',
  keywords: '',
  regions: [] as string[],
  eventTypes: [] as EventType[],
  actorIds: [] as number[]
});

const eventTypes: { value: EventType; label: string }[] = [
  { value: 'combat_engagement', label: 'Combat Engagement' },
  { value: 'airstrike', label: 'Airstrike' },
  { value: 'artillery_strike', label: 'Artillery Strike' },
  { value: 'missile_strike', label: 'Missile Strike' },
  { value: 'equipment_destroyed', label: 'Equipment Destroyed' },
  { value: 'civilian_casualties', label: 'Civilian Casualties' }
];

const regions = [
  'Ukraine', 'Eastern Europe', 'Middle East', 'Africa', 'Asia Pacific', 'Latin America'
];

const unreadCount = computed(() => {
  return alertHistory.value.filter(h => !h.read).length;
});

const toggleAlertEnabled = (alertId: number) => {
  const alert = alerts.value.find(a => a.id === alertId);
  if (alert) {
    alert.enabled = !alert.enabled;
  }
};

const deleteAlert = (alertId: number) => {
  if (confirm('Are you sure you want to delete this alert?')) {
    alerts.value = alerts.value.filter(a => a.id !== alertId);
  }
};

const markAsRead = (historyId: number) => {
  const item = alertHistory.value.find(h => h.id === historyId);
  if (item) {
    item.read = true;
  }
};

const markAllAsRead = () => {
  alertHistory.value.forEach(h => h.read = true);
};

const clearHistory = () => {
  if (confirm('Are you sure you want to clear all alert history?')) {
    alertHistory.value = [];
  }
};

const toggleRegion = (region: string) => {
  const index = newAlert.regions.indexOf(region);
  if (index === -1) {
    newAlert.regions.push(region);
  } else {
    newAlert.regions.splice(index, 1);
  }
};

const toggleEventType = (type: EventType) => {
  const index = newAlert.eventTypes.indexOf(type);
  if (index === -1) {
    newAlert.eventTypes.push(type);
  } else {
    newAlert.eventTypes.splice(index, 1);
  }
};

const createAlert = () => {
  if (!newAlert.name) {
    alert('Please enter an alert name');
    return;
  }

  let criteria = '';
  switch (newAlert.type) {
    case 'keyword':
      criteria = newAlert.keywords;
      break;
    case 'region':
      criteria = newAlert.regions.join(', ');
      break;
    case 'event_type':
      criteria = newAlert.eventTypes.join(', ');
      break;
  }

  alerts.value.push({
    id: Date.now(),
    name: newAlert.name,
    type: newAlert.type,
    criteria,
    enabled: true,
    createdAt: new Date().toISOString().split('T')[0]!,
    triggeredCount: 0
  });

  // Reset form
  newAlert.name = '';
  newAlert.keywords = '';
  newAlert.regions = [];
  newAlert.eventTypes = [];
  showCreateModal.value = false;
};

const savePreferences = () => {
  alert('Notification preferences saved!');
};

const getAlertTypeIcon = (type: string) => {
  switch (type) {
    case 'keyword':
      return 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z';
    case 'region':
      return 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z';
    case 'event_type':
      return 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2';
    case 'actor':
      return 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z';
    default:
      return 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9';
  }
};
</script>

<template>
  <div class="p-6 bg-gray-50 dark:bg-gray-900 min-h-screen">
    <div class="max-w-5xl mx-auto">
      <div class="flex items-center justify-between mb-6">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Alerts & Notifications</h1>
          <p class="mt-1 text-gray-600 dark:text-gray-400">Manage your alert rules and notification preferences</p>
        </div>
        <button
          @click="showCreateModal = true"
          class="px-4 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 flex items-center space-x-2"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          <span>Create Alert</span>
        </button>
      </div>

      <!-- Tabs -->
      <div class="flex space-x-4 mb-6">
        <button
          @click="activeTab = 'alerts'"
          :class="[
            'px-4 py-2 rounded-lg font-medium transition-colors',
            activeTab === 'alerts'
              ? 'bg-blue-600 text-white'
              : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
          ]"
        >
          My Alerts
        </button>
        <button
          @click="activeTab = 'history'"
          :class="[
            'px-4 py-2 rounded-lg font-medium transition-colors relative',
            activeTab === 'history'
              ? 'bg-blue-600 text-white'
              : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
          ]"
        >
          Alert History
          <span
            v-if="unreadCount > 0"
            class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center"
          >
            {{ unreadCount }}
          </span>
        </button>
        <button
          @click="activeTab = 'preferences'"
          :class="[
            'px-4 py-2 rounded-lg font-medium transition-colors',
            activeTab === 'preferences'
              ? 'bg-blue-600 text-white'
              : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
          ]"
        >
          Preferences
        </button>
      </div>

      <!-- Alerts Tab -->
      <div v-if="activeTab === 'alerts'" class="space-y-4">
        <div
          v-for="alert in alerts"
          :key="alert.id"
          class="bg-white dark:bg-gray-800 rounded-lg shadow p-6"
        >
          <div class="flex items-start justify-between">
            <div class="flex items-start space-x-4">
              <div
                class="w-10 h-10 rounded-lg flex items-center justify-center"
                :class="alert.enabled ? 'bg-blue-100 dark:bg-blue-900/30' : 'bg-gray-100 dark:bg-gray-700'"
              >
                <svg
                  class="w-5 h-5"
                  :class="alert.enabled ? 'text-blue-600 dark:text-blue-400' : 'text-gray-500 dark:text-gray-400'"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getAlertTypeIcon(alert.type)" />
                </svg>
              </div>
              <div>
                <h3 class="font-semibold text-gray-900 dark:text-white">{{ alert.name }}</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                  <span class="capitalize">{{ alert.type.replace('_', ' ') }}:</span> {{ alert.criteria }}
                </p>
                <div class="flex items-center space-x-4 mt-2 text-xs text-gray-500 dark:text-gray-400">
                  <span>Created {{ alert.createdAt }}</span>
                  <span>Triggered {{ alert.triggeredCount }} times</span>
                  <span v-if="alert.lastTriggered">Last: {{ alert.lastTriggered }}</span>
                </div>
              </div>
            </div>
            <div class="flex items-center space-x-3">
              <label class="relative inline-flex items-center cursor-pointer">
                <input
                  type="checkbox"
                  :checked="alert.enabled"
                  @change="toggleAlertEnabled(alert.id)"
                  class="sr-only peer"
                />
                <div class="w-11 h-6 bg-gray-200 dark:bg-gray-700 peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
              </label>
              <button
                @click="deleteAlert(alert.id)"
                class="p-2 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
              </button>
            </div>
          </div>
        </div>

        <div v-if="alerts.length === 0" class="bg-white dark:bg-gray-800 rounded-lg shadow p-12 text-center">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
          </svg>
          <p class="mt-4 text-gray-500 dark:text-gray-400">No alerts configured</p>
          <button
            @click="showCreateModal = true"
            class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700"
          >
            Create your first alert
          </button>
        </div>
      </div>

      <!-- History Tab -->
      <div v-if="activeTab === 'history'">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Alerts</h2>
            <div class="flex space-x-2">
              <button
                @click="markAllAsRead"
                class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-700"
              >
                Mark all as read
              </button>
              <span class="text-gray-300 dark:text-gray-600">|</span>
              <button
                @click="clearHistory"
                class="text-sm text-red-600 dark:text-red-400 hover:text-red-700"
              >
                Clear history
              </button>
            </div>
          </div>
          <div class="divide-y divide-gray-200 dark:divide-gray-700">
            <div
              v-for="item in alertHistory"
              :key="item.id"
              @click="markAsRead(item.id)"
              :class="[
                'px-6 py-4 cursor-pointer transition-colors',
                item.read
                  ? 'bg-white dark:bg-gray-800'
                  : 'bg-blue-50 dark:bg-blue-900/20'
              ]"
            >
              <div class="flex items-start justify-between">
                <div class="flex items-start space-x-3">
                  <div v-if="!item.read" class="w-2 h-2 mt-2 rounded-full bg-blue-600"></div>
                  <div v-else class="w-2 h-2 mt-2"></div>
                  <div>
                    <p class="font-medium text-gray-900 dark:text-white">{{ item.alertName }}</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ item.message }}</p>
                    <p v-if="item.eventTitle" class="text-sm text-blue-600 dark:text-blue-400 mt-1">
                      Event: {{ item.eventTitle }}
                    </p>
                  </div>
                </div>
                <span class="text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">
                  {{ item.triggeredAt }}
                </span>
              </div>
            </div>
          </div>
          <div v-if="alertHistory.length === 0" class="px-6 py-12 text-center">
            <p class="text-gray-500 dark:text-gray-400">No alert history</p>
          </div>
        </div>
      </div>

      <!-- Preferences Tab -->
      <div v-if="activeTab === 'preferences'" class="space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Email Notifications</h2>
          <div class="space-y-4">
            <div class="flex items-center justify-between">
              <div>
                <p class="font-medium text-gray-900 dark:text-white">Enable Email Notifications</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Receive alerts via email</p>
              </div>
              <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" v-model="notificationPreferences.email.enabled" class="sr-only peer">
                <div class="w-11 h-6 bg-gray-200 dark:bg-gray-700 peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
              </label>
            </div>
            <div v-if="notificationPreferences.email.enabled" class="ml-4 space-y-4">
              <div class="flex items-center justify-between">
                <div>
                  <p class="font-medium text-gray-900 dark:text-white">Instant Notifications</p>
                  <p class="text-sm text-gray-500 dark:text-gray-400">Get notified immediately when alerts trigger</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                  <input type="checkbox" v-model="notificationPreferences.email.instant" class="sr-only peer">
                  <div class="w-11 h-6 bg-gray-200 dark:bg-gray-700 peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                </label>
              </div>
              <div class="flex items-center justify-between">
                <div>
                  <p class="font-medium text-gray-900 dark:text-white">Digest Emails</p>
                  <p class="text-sm text-gray-500 dark:text-gray-400">Receive summary emails of triggered alerts</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                  <input type="checkbox" v-model="notificationPreferences.email.digest" class="sr-only peer">
                  <div class="w-11 h-6 bg-gray-200 dark:bg-gray-700 peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                </label>
              </div>
              <div v-if="notificationPreferences.email.digest">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Digest Frequency</label>
                <select
                  v-model="notificationPreferences.email.digestFrequency"
                  class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                >
                  <option value="daily">Daily</option>
                  <option value="weekly">Weekly</option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Push Notifications</h2>
          <div class="space-y-4">
            <div class="flex items-center justify-between">
              <div>
                <p class="font-medium text-gray-900 dark:text-white">Enable Push Notifications</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Receive notifications in your browser</p>
              </div>
              <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" v-model="notificationPreferences.push.enabled" class="sr-only peer">
                <div class="w-11 h-6 bg-gray-200 dark:bg-gray-700 peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
              </label>
            </div>
            <div v-if="notificationPreferences.push.enabled" class="ml-4 space-y-4">
              <div class="flex items-center justify-between">
                <div>
                  <p class="font-medium text-gray-900 dark:text-white">Sound</p>
                  <p class="text-sm text-gray-500 dark:text-gray-400">Play sound for notifications</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                  <input type="checkbox" v-model="notificationPreferences.push.sound" class="sr-only peer">
                  <div class="w-11 h-6 bg-gray-200 dark:bg-gray-700 peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                </label>
              </div>
              <div class="flex items-center justify-between">
                <div>
                  <p class="font-medium text-gray-900 dark:text-white">Desktop Notifications</p>
                  <p class="text-sm text-gray-500 dark:text-gray-400">Show system notifications</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                  <input type="checkbox" v-model="notificationPreferences.push.desktop" class="sr-only peer">
                  <div class="w-11 h-6 bg-gray-200 dark:bg-gray-700 peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                </label>
              </div>
            </div>
          </div>
        </div>

        <div class="flex justify-end">
          <button
            @click="savePreferences"
            class="px-6 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700"
          >
            Save Preferences
          </button>
        </div>
      </div>

      <!-- Create Alert Modal -->
      <div
        v-if="showCreateModal"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
      >
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-lg w-full max-h-[90vh] overflow-auto">
          <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Create New Alert</h3>
            <button
              @click="showCreateModal = false"
              class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
          <div class="p-6 space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Alert Name <span class="text-red-500">*</span>
              </label>
              <input
                v-model="newAlert.name"
                type="text"
                class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                placeholder="e.g., Ukraine Updates"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Alert Type</label>
              <div class="grid grid-cols-2 gap-2">
                <button
                  v-for="type in ['keyword', 'region', 'event_type']"
                  :key="type"
                  @click="newAlert.type = type as any"
                  :class="[
                    'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
                    newAlert.type === type
                      ? 'bg-blue-600 text-white'
                      : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'
                  ]"
                >
                  {{ type.replace('_', ' ').charAt(0).toUpperCase() + type.replace('_', ' ').slice(1) }}
                </button>
              </div>
            </div>

            <div v-if="newAlert.type === 'keyword'">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Keywords</label>
              <input
                v-model="newAlert.keywords"
                type="text"
                class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                placeholder="e.g., tank, artillery, drone"
              />
              <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Separate multiple keywords with commas</p>
            </div>

            <div v-if="newAlert.type === 'region'">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Regions</label>
              <div class="flex flex-wrap gap-2">
                <button
                  v-for="region in regions"
                  :key="region"
                  @click="toggleRegion(region)"
                  :class="[
                    'px-3 py-1.5 rounded-full text-sm font-medium transition-colors',
                    newAlert.regions.includes(region)
                      ? 'bg-green-600 text-white'
                      : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'
                  ]"
                >
                  {{ region }}
                </button>
              </div>
            </div>

            <div v-if="newAlert.type === 'event_type'">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Event Types</label>
              <div class="flex flex-wrap gap-2">
                <button
                  v-for="type in eventTypes"
                  :key="type.value"
                  @click="toggleEventType(type.value)"
                  :class="[
                    'px-3 py-1.5 rounded-full text-sm font-medium transition-colors',
                    newAlert.eventTypes.includes(type.value)
                      ? 'bg-purple-600 text-white'
                      : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'
                  ]"
                >
                  {{ type.label }}
                </button>
              </div>
            </div>
          </div>
          <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex justify-end space-x-3">
            <button
              @click="showCreateModal = false"
              class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg font-medium hover:bg-gray-300 dark:hover:bg-gray-600"
            >
              Cancel
            </button>
            <button
              @click="createAlert"
              class="px-4 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700"
            >
              Create Alert
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
