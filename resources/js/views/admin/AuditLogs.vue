<template>
  <div class="mt-6 space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Audit Logs</h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Track all system activities and changes</p>
      </div>
      <div class="flex space-x-2">
        <button @click="verifyChain" :disabled="verifying" class="inline-flex items-center px-4 py-2 border border-blue-300 dark:border-blue-600 text-blue-600 dark:text-blue-400 bg-white dark:bg-gray-700 text-sm font-medium rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors disabled:opacity-50">
          <svg v-if="verifying" class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
          <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
          Verify Chain
        </button>
        <button @click="exportLogs" :disabled="exporting" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors disabled:opacity-50">
          <svg v-if="exporting" class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
          <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
          Export
        </button>
        <button @click="showClearModal = true" class="inline-flex items-center px-4 py-2 border border-red-300 dark:border-red-600 text-red-600 dark:text-red-400 bg-white dark:bg-gray-700 text-sm font-medium rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
          Clear Old Logs
        </button>
      </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">Total Logs</p>
        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.total_logs?.toLocaleString() || 0 }}</p>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">Today</p>
        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ stats.today_logs || 0 }}</p>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">Users Active</p>
        <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ stats.unique_users_today || 0 }}</p>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">Critical Events</p>
        <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ stats.critical_events || 0 }}</p>
      </div>
    </div>

    <!-- Chain Integrity Alert -->
    <div v-if="stats.chain_integrity && stats.chain_integrity.broken > 0" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
      <div class="flex">
        <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
        <div class="ml-3">
          <h3 class="text-sm font-medium text-red-800 dark:text-red-200">Chain Integrity Warning</h3>
          <p class="mt-1 text-sm text-red-700 dark:text-red-300">{{ stats.chain_integrity.broken }} logs have broken chain integrity. This may indicate tampering.</p>
        </div>
      </div>
    </div>

    <!-- Chain Verification Result -->
    <div v-if="chainResult" class="rounded-lg p-4" :class="chainResult.verified ? 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800' : 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800'">
      <div class="flex">
        <svg v-if="chainResult.verified" class="h-5 w-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        <svg v-else class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        <div class="ml-3">
          <h3 class="text-sm font-medium" :class="chainResult.verified ? 'text-green-800 dark:text-green-200' : 'text-red-800 dark:text-red-200'">
            {{ chainResult.verified ? 'Chain Verified Successfully' : 'Chain Verification Failed' }}
          </h3>
          <p class="mt-1 text-sm" :class="chainResult.verified ? 'text-green-700 dark:text-green-300' : 'text-red-700 dark:text-red-300'">
            Checked {{ chainResult.checked_count }} logs. {{ chainResult.errors?.length || 0 }} errors found.
          </p>
          <button @click="chainResult = null" class="mt-2 text-xs underline" :class="chainResult.verified ? 'text-green-600' : 'text-red-600'">Dismiss</button>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4">
        <input v-model="filters.search" @input="debouncedFetch" type="text" placeholder="Search..." class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400" />
        <select v-model="filters.action" @change="() => fetchLogs()" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
          <option value="">All Actions</option>
          <option v-for="(label, value) in filterOptions.actions" :key="value" :value="value">{{ label }}</option>
        </select>
        <select v-model="filters.entity" @change="() => fetchLogs()" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
          <option value="">All Entities</option>
          <option v-for="(label, value) in filterOptions.entities" :key="value" :value="value">{{ label }}</option>
        </select>
        <select v-model="filters.user_id" @change="() => fetchLogs()" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
          <option value="">All Users</option>
          <option v-for="user in filterOptions.users" :key="user.id" :value="user.id">{{ user.name }}</option>
        </select>
        <input v-model="filters.date" @change="() => fetchLogs()" type="date" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
        <button @click="clearFilters" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600">Clear</button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center py-12">
      <svg class="animate-spin h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
      </svg>
    </div>

    <!-- Logs Timeline -->
    <div v-else class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
      <div class="divide-y divide-gray-200 dark:divide-gray-700">
        <div v-for="log in logs" :key="log.id" class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
          <div class="flex items-start space-x-4">
            <!-- Action Icon -->
            <div :class="getActionClass(log.action)" class="p-2 rounded-lg flex-shrink-0">
              <svg v-if="log.action === 'create'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
              <svg v-else-if="log.action === 'update'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
              <svg v-else-if="log.action === 'delete'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
              <svg v-else-if="log.action === 'login'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" /></svg>
              <svg v-else-if="log.action === 'verify'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
              <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>

            <!-- Log Details -->
            <div class="flex-1 min-w-0">
              <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-gray-900 dark:text-white">
                  {{ log.user_name }} <span class="text-gray-500 dark:text-gray-400">{{ log.action }}</span> {{ log.auditable_type }}
                  <span v-if="log.auditable_uuid || log.auditable_id" class="text-blue-600 dark:text-blue-400">#{{ log.auditable_uuid || log.auditable_id }}</span>
                </p>
                <div class="flex items-center space-x-2">
                  <span v-if="!log.chain_verified" class="px-2 py-0.5 bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 text-xs rounded">Chain Broken</span>
                  <span class="text-xs text-gray-500 dark:text-gray-400">{{ formatDate(log.created_at) }}</span>
                </div>
              </div>
              <div v-if="log.description" class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ log.description }}</div>
              <div class="mt-2 flex flex-wrap gap-2 text-xs">
                <span class="px-2 py-0.5 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 rounded">{{ log.ip_address }}</span>
                <span v-if="log.ip_country" class="px-2 py-0.5 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 rounded">{{ log.ip_country }}</span>
                <span v-if="log.user_agent_short" class="px-2 py-0.5 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 rounded">{{ log.user_agent_short }}</span>
              </div>
              <!-- Changes (expandable) -->
              <div v-if="log.changes && Object.keys(log.changes).length > 0" class="mt-2">
                <button @click="toggleChanges(log.id)" class="text-xs text-blue-600 dark:text-blue-400 hover:underline">
                  {{ expandedLogs.includes(log.id) ? 'Hide changes' : `Show ${Object.keys(log.changes).length} changes` }}
                </button>
                <div v-if="expandedLogs.includes(log.id)" class="mt-2 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg text-xs font-mono overflow-x-auto">
                  <div v-for="(value, key) in log.changes" :key="key" class="flex py-1">
                    <span class="text-gray-500 dark:text-gray-400 w-32 flex-shrink-0">{{ key }}:</span>
                    <span v-if="value.old" class="text-red-600 dark:text-red-400 line-through mr-2">{{ truncate(value.old) }}</span>
                    <span class="text-green-600 dark:text-green-400">{{ truncate(value.new) }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="logs.length === 0 && !loading" class="p-8 text-center text-gray-500 dark:text-gray-400">
        <svg class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
        <p>No audit logs found</p>
      </div>

      <!-- Pagination -->
      <div v-if="pagination.last_page > 1" class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between">
        <p class="text-sm text-gray-500 dark:text-gray-400">
          Showing {{ pagination.from }} - {{ pagination.to }} of {{ pagination.total }} logs
        </p>
        <div class="flex space-x-2">
          <button @click="goToPage(pagination.current_page - 1)" :disabled="pagination.current_page === 1" class="px-3 py-1.5 text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg disabled:opacity-50">Previous</button>
          <span class="px-3 py-1.5 text-sm text-gray-700 dark:text-gray-300">{{ pagination.current_page }} / {{ pagination.last_page }}</span>
          <button @click="goToPage(pagination.current_page + 1)" :disabled="pagination.current_page === pagination.last_page" class="px-3 py-1.5 text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg disabled:opacity-50">Next</button>
        </div>
      </div>
    </div>

    <!-- Clear Old Logs Modal -->
    <div v-if="showClearModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-md w-full mx-4 p-6">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Clear Old Audit Logs</h3>
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">This action will permanently delete audit logs older than the specified number of days. This cannot be undone.</p>
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Delete logs older than:</label>
          <select v-model="clearDays" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
            <option value="90">90 days</option>
            <option value="180">180 days</option>
            <option value="365">1 year</option>
          </select>
        </div>
        <div class="flex justify-end space-x-3">
          <button @click="showClearModal = false" class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600">Cancel</button>
          <button @click="clearOldLogs" :disabled="clearing" class="px-4 py-2 text-white bg-red-600 rounded-lg hover:bg-red-700 disabled:opacity-50">
            {{ clearing ? 'Clearing...' : 'Clear Logs' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue';
import axios from 'axios';

interface AuditLog {
  id: number;
  uuid: string;
  user_id: number;
  user_name: string;
  user_email: string;
  action: string;
  auditable_type: string;
  auditable_type_full: string;
  auditable_id: number;
  auditable_uuid?: string;
  description?: string;
  ip_address: string;
  ip_country?: string;
  user_agent_short?: string;
  changes?: Record<string, { old: any; new: any }>;
  created_at: string;
  chain_verified: boolean;
}

interface Pagination {
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  from: number;
  to: number;
}

interface FilterOptions {
  actions: Record<string, string>;
  entities: Record<string, string>;
  users: Array<{ id: number; name: string; email: string }>;
}

interface Stats {
  total_logs: number;
  today_logs: number;
  unique_users_today: number;
  critical_events: number;
  chain_integrity: {
    total: number;
    verified: number;
    broken: number;
  };
}

const logs = ref<AuditLog[]>([]);
const loading = ref(false);
const exporting = ref(false);
const verifying = ref(false);
const clearing = ref(false);
const expandedLogs = ref<number[]>([]);
const showClearModal = ref(false);
const clearDays = ref(90);
const chainResult = ref<{ verified: boolean; checked_count: number; errors: any[] } | null>(null);

const filters = reactive({
  search: '',
  action: '',
  entity: '',
  user_id: '',
  date: '',
});

const pagination = reactive<Pagination>({
  current_page: 1,
  last_page: 1,
  per_page: 25,
  total: 0,
  from: 0,
  to: 0,
});

const stats = reactive<Stats>({
  total_logs: 0,
  today_logs: 0,
  unique_users_today: 0,
  critical_events: 0,
  chain_integrity: { total: 0, verified: 0, broken: 0 },
});

const filterOptions = reactive<FilterOptions>({
  actions: {},
  entities: {},
  users: [],
});

let debounceTimer: ReturnType<typeof setTimeout> | null = null;

const debouncedFetch = () => {
  if (debounceTimer) clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    fetchLogs();
  }, 300);
};

const fetchLogs = async (page = 1) => {
  loading.value = true;
  try {
    const params: Record<string, any> = { page, per_page: pagination.per_page };
    if (filters.search) params.search = filters.search;
    if (filters.action) params.action = filters.action;
    if (filters.entity) params.entity = filters.entity;
    if (filters.user_id) params.user_id = filters.user_id;
    if (filters.date) params.date = filters.date;

    const response = await axios.get('/api/admin/audit-logs', { params });
    logs.value = response.data.data;
    pagination.current_page = response.data.current_page;
    pagination.last_page = response.data.last_page;
    pagination.per_page = response.data.per_page;
    pagination.total = response.data.total;
    pagination.from = response.data.from || 0;
    pagination.to = response.data.to || 0;
  } catch (error) {
    console.error('Failed to fetch audit logs:', error);
  } finally {
    loading.value = false;
  }
};

const fetchStats = async () => {
  try {
    const response = await axios.get('/api/admin/audit-logs/stats');
    Object.assign(stats, response.data.data);
  } catch (error) {
    console.error('Failed to fetch stats:', error);
  }
};

const fetchFilterOptions = async () => {
  try {
    const response = await axios.get('/api/admin/audit-logs/filter-options');
    Object.assign(filterOptions, response.data.data);
  } catch (error) {
    console.error('Failed to fetch filter options:', error);
  }
};

const goToPage = (page: number) => {
  if (page >= 1 && page <= pagination.last_page) {
    fetchLogs(page);
  }
};

const toggleChanges = (id: number) => {
  const index = expandedLogs.value.indexOf(id);
  if (index > -1) expandedLogs.value.splice(index, 1);
  else expandedLogs.value.push(id);
};

const clearFilters = () => {
  Object.assign(filters, { search: '', action: '', entity: '', user_id: '', date: '' });
  fetchLogs(1);
};

const exportLogs = async () => {
  exporting.value = true;
  try {
    const params: Record<string, any> = {};
    if (filters.action) params.action = filters.action;
    if (filters.entity) params.entity = filters.entity;
    if (filters.date) params.date_from = filters.date;

    const response = await axios.get('/api/admin/audit-logs/export', {
      params,
      responseType: 'blob',
    });

    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.download = `audit-logs-${new Date().toISOString().slice(0, 10)}.csv`;
    link.click();
    window.URL.revokeObjectURL(url);
  } catch (error) {
    console.error('Failed to export logs:', error);
    alert('Failed to export audit logs');
  } finally {
    exporting.value = false;
  }
};

const verifyChain = async () => {
  verifying.value = true;
  chainResult.value = null;
  try {
    const response = await axios.get('/api/admin/audit-logs/verify-chain');
    chainResult.value = response.data.data;
    fetchStats(); // Refresh stats after verification
  } catch (error) {
    console.error('Failed to verify chain:', error);
    alert('Failed to verify audit chain');
  } finally {
    verifying.value = false;
  }
};

const clearOldLogs = async () => {
  if (!confirm(`Are you sure you want to delete all logs older than ${clearDays.value} days?`)) {
    return;
  }

  clearing.value = true;
  try {
    const response = await axios.post('/api/admin/audit-logs/clear-old', {
      days: clearDays.value,
      confirm: true,
    });
    alert(response.data.message);
    showClearModal.value = false;
    fetchLogs(1);
    fetchStats();
  } catch (error: any) {
    console.error('Failed to clear logs:', error);
    alert(error.response?.data?.message || 'Failed to clear old logs');
  } finally {
    clearing.value = false;
  }
};

const formatDate = (d: string) => {
  const date = new Date(d);
  const now = new Date();
  const diffMs = now.getTime() - date.getTime();
  const diffMins = Math.floor(diffMs / 60000);
  const diffHours = Math.floor(diffMs / 3600000);
  const diffDays = Math.floor(diffMs / 86400000);

  if (diffMins < 1) return 'Just now';
  if (diffMins < 60) return `${diffMins}m ago`;
  if (diffHours < 24) return `${diffHours}h ago`;
  if (diffDays < 7) return `${diffDays}d ago`;

  return date.toLocaleString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
};

const truncate = (value: any, length = 50) => {
  const str = typeof value === 'string' ? value : JSON.stringify(value);
  return str.length > length ? str.substring(0, length) + '...' : str;
};

const getActionClass = (action: string) => ({
  create: 'bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400',
  update: 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400',
  delete: 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400',
  login: 'bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400',
  logout: 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400',
  export: 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-600 dark:text-yellow-400',
  verify: 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400',
  failed_login: 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400',
}[action] || 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400');

onMounted(() => {
  fetchLogs();
  fetchStats();
  fetchFilterOptions();
});
</script>
