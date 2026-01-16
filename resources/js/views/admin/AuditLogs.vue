<template>
  <div class="mt-6 space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Audit Logs</h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Track all system activities and changes</p>
      </div>
      <div class="flex space-x-2">
        <button @click="exportLogs" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
          Export
        </button>
        <button @click="clearOldLogs" class="inline-flex items-center px-4 py-2 border border-red-300 dark:border-red-600 text-red-600 dark:text-red-400 bg-white dark:bg-gray-700 text-sm font-medium rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
          Clear Old Logs
        </button>
      </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">Total Logs</p>
        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ logs.length }}</p>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">Today</p>
        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ todayLogs }}</p>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">Users Active</p>
        <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ uniqueUsers }}</p>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">Critical Events</p>
        <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ criticalEvents }}</p>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <input v-model="filters.search" type="text" placeholder="Search..." class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400" />
        <select v-model="filters.action" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
          <option value="">All Actions</option>
          <option value="created">Created</option>
          <option value="updated">Updated</option>
          <option value="deleted">Deleted</option>
          <option value="login">Login</option>
          <option value="logout">Logout</option>
          <option value="export">Export</option>
          <option value="import">Import</option>
        </select>
        <select v-model="filters.entity" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
          <option value="">All Entities</option>
          <option value="User">Users</option>
          <option value="Event">Events</option>
          <option value="Actor">Actors</option>
          <option value="Conflict">Conflicts</option>
          <option value="Equipment">Equipment</option>
          <option value="ControlZone">Zones</option>
          <option value="Setting">Settings</option>
        </select>
        <input v-model="filters.date" type="date" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
        <button @click="clearFilters" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600">Clear</button>
      </div>
    </div>

    <!-- Logs Timeline -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
      <div class="divide-y divide-gray-200 dark:divide-gray-700">
        <div v-for="log in paginatedLogs" :key="log.id" class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
          <div class="flex items-start space-x-4">
            <!-- Action Icon -->
            <div :class="getActionClass(log.action)" class="p-2 rounded-lg flex-shrink-0">
              <svg v-if="log.action === 'created'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
              <svg v-else-if="log.action === 'updated'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
              <svg v-else-if="log.action === 'deleted'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
              <svg v-else-if="log.action === 'login'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" /></svg>
              <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>

            <!-- Log Details -->
            <div class="flex-1 min-w-0">
              <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-gray-900 dark:text-white">
                  {{ log.user_name }} <span class="text-gray-500 dark:text-gray-400">{{ log.action }}</span> {{ log.auditable_type }}
                  <span v-if="log.auditable_name" class="text-blue-600 dark:text-blue-400">{{ log.auditable_name }}</span>
                </p>
                <span class="text-xs text-gray-500 dark:text-gray-400">{{ formatDate(log.created_at) }}</span>
              </div>
              <div v-if="log.description" class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ log.description }}</div>
              <div class="mt-2 flex flex-wrap gap-2 text-xs">
                <span class="px-2 py-0.5 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 rounded">{{ log.ip_address }}</span>
                <span v-if="log.user_agent_short" class="px-2 py-0.5 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 rounded">{{ log.user_agent_short }}</span>
              </div>
              <!-- Changes (expandable) -->
              <div v-if="log.changes && Object.keys(log.changes).length > 0" class="mt-2">
                <button @click="toggleChanges(log.id)" class="text-xs text-blue-600 dark:text-blue-400 hover:underline">
                  {{ expandedLogs.includes(log.id) ? 'Hide changes' : 'Show changes' }}
                </button>
                <div v-if="expandedLogs.includes(log.id)" class="mt-2 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg text-xs font-mono">
                  <div v-for="(value, key) in log.changes" :key="key" class="flex">
                    <span class="text-gray-500 dark:text-gray-400 w-24">{{ key }}:</span>
                    <span v-if="value.old" class="text-red-600 dark:text-red-400 line-through mr-2">{{ value.old }}</span>
                    <span class="text-green-600 dark:text-green-400">{{ value.new }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="filteredLogs.length === 0" class="p-8 text-center text-gray-500 dark:text-gray-400">
        <svg class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
        <p>No audit logs found</p>
      </div>

      <!-- Pagination -->
      <div v-if="totalPages > 1" class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between">
        <p class="text-sm text-gray-500 dark:text-gray-400">{{ filteredLogs.length }} logs</p>
        <div class="flex space-x-2">
          <button @click="currentPage--" :disabled="currentPage === 1" class="px-3 py-1.5 text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg disabled:opacity-50">Previous</button>
          <span class="px-3 py-1.5 text-sm text-gray-700 dark:text-gray-300">{{ currentPage }} / {{ totalPages }}</span>
          <button @click="currentPage++" :disabled="currentPage === totalPages" class="px-3 py-1.5 text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg disabled:opacity-50">Next</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue';

interface AuditLog {
  id: number;
  user_id: number;
  user_name: string;
  action: string;
  auditable_type: string;
  auditable_id: number;
  auditable_name?: string;
  description?: string;
  ip_address: string;
  user_agent_short?: string;
  changes?: Record<string, { old: any; new: any }>;
  created_at: string;
}

const logs = ref<AuditLog[]>([]);
const currentPage = ref(1);
const perPage = ref(15);
const expandedLogs = ref<number[]>([]);

const filters = reactive({ search: '', action: '', entity: '', date: '' });

const todayLogs = computed(() => {
  const today = new Date().toDateString();
  return logs.value.filter(l => new Date(l.created_at).toDateString() === today).length;
});
const uniqueUsers = computed(() => new Set(logs.value.map(l => l.user_id)).size);
const criticalEvents = computed(() => logs.value.filter(l => ['deleted', 'failed_login'].includes(l.action)).length);

const filteredLogs = computed(() => logs.value.filter(l => {
  const s = !filters.search || l.user_name.toLowerCase().includes(filters.search.toLowerCase()) || l.auditable_name?.toLowerCase().includes(filters.search.toLowerCase());
  const a = !filters.action || l.action === filters.action;
  const e = !filters.entity || l.auditable_type === filters.entity;
  const d = !filters.date || l.created_at.startsWith(filters.date);
  return s && a && e && d;
}));

const totalPages = computed(() => Math.ceil(filteredLogs.value.length / perPage.value) || 1);
const paginatedLogs = computed(() => filteredLogs.value.slice((currentPage.value - 1) * perPage.value, currentPage.value * perPage.value));

const fetchLogs = async () => {
  logs.value = [
    { id: 1, user_id: 1, user_name: 'John Admin', action: 'created', auditable_type: 'Event', auditable_id: 156, auditable_name: 'Artillery strike near Bakhmut', ip_address: '192.168.1.1', user_agent_short: 'Chrome/Win', created_at: '2024-01-20T14:30:00Z' },
    { id: 2, user_id: 2, user_name: 'Jane Analyst', action: 'updated', auditable_type: 'Actor', auditable_id: 3, auditable_name: 'Wagner Group', ip_address: '192.168.1.2', user_agent_short: 'Firefox/Mac', changes: { activity_level: { old: 'high', new: 'medium' } }, created_at: '2024-01-20T12:15:00Z' },
    { id: 3, user_id: 1, user_name: 'John Admin', action: 'deleted', auditable_type: 'Event', auditable_id: 145, auditable_name: 'Duplicate event', ip_address: '192.168.1.1', user_agent_short: 'Chrome/Win', created_at: '2024-01-20T10:00:00Z' },
    { id: 4, user_id: 3, user_name: 'Bob Contributor', action: 'login', auditable_type: 'User', auditable_id: 3, ip_address: '192.168.1.5', user_agent_short: 'Safari/iOS', created_at: '2024-01-20T09:30:00Z' },
    { id: 5, user_id: 2, user_name: 'Jane Analyst', action: 'created', auditable_type: 'ControlZone', auditable_id: 28, auditable_name: 'Kherson Oblast', ip_address: '192.168.1.2', user_agent_short: 'Firefox/Mac', created_at: '2024-01-19T16:45:00Z' },
    { id: 6, user_id: 1, user_name: 'John Admin', action: 'updated', auditable_type: 'Setting', auditable_id: 1, auditable_name: 'map.default_zoom', changes: { value: { old: '5', new: '6' } }, ip_address: '192.168.1.1', user_agent_short: 'Chrome/Win', created_at: '2024-01-19T14:00:00Z' },
    { id: 7, user_id: 4, user_name: 'Alice Viewer', action: 'export', auditable_type: 'Event', auditable_id: 0, description: 'Exported 156 events to CSV', ip_address: '192.168.1.8', user_agent_short: 'Chrome/Linux', created_at: '2024-01-19T11:30:00Z' },
  ];
};

const toggleChanges = (id: number) => {
  const index = expandedLogs.value.indexOf(id);
  if (index > -1) expandedLogs.value.splice(index, 1);
  else expandedLogs.value.push(id);
};

const clearFilters = () => { Object.assign(filters, { search: '', action: '', entity: '', date: '' }); currentPage.value = 1; };

const exportLogs = () => {
  const csv = ['User,Action,Entity,Description,IP,Date', ...filteredLogs.value.map(l => `${l.user_name},${l.action},${l.auditable_type},${l.description || ''},${l.ip_address},${l.created_at}`)].join('\n');
  const blob = new Blob([csv], { type: 'text/csv' });
  const url = window.URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url; a.download = 'audit-logs.csv'; a.click();
};

const clearOldLogs = () => {
  if (confirm('Delete logs older than 90 days?')) {
    const cutoff = new Date(); cutoff.setDate(cutoff.getDate() - 90);
    logs.value = logs.value.filter(l => new Date(l.created_at) > cutoff);
  }
};

const formatDate = (d: string) => new Date(d).toLocaleString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });

const getActionClass = (action: string) => ({
  created: 'bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400',
  updated: 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400',
  deleted: 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400',
  login: 'bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400',
  logout: 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400',
  export: 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-600 dark:text-yellow-400',
}[action] || 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400');

onMounted(fetchLogs);
</script>
