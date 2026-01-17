<template>
  <div class="mt-6 space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Site Analytics</h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Anonymous aggregated site usage data</p>
      </div>
      <div class="flex items-center space-x-3">
        <select v-model="dateRange" @change="fetchDashboard" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
          <option value="7">Last 7 days</option>
          <option value="14">Last 14 days</option>
          <option value="30">Last 30 days</option>
          <option value="90">Last 90 days</option>
        </select>
        <button @click="exportData" :disabled="exporting" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors disabled:opacity-50">
          <svg v-if="exporting" class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
          <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
          Export
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center py-12">
      <svg class="animate-spin h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
      </svg>
    </div>

    <template v-else>
      <!-- Summary Stats -->
      <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
          <p class="text-sm text-gray-500 dark:text-gray-400">Page Views</p>
          <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ formatNumber(dashboard.total_page_views) }}</p>
          <p class="text-xs text-gray-500">{{ formatNumber(dashboard.unique_page_views) }} unique</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
          <p class="text-sm text-gray-500 dark:text-gray-400">Sessions</p>
          <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ formatNumber(dashboard.total_sessions) }}</p>
          <p class="text-xs text-gray-500">{{ formatDuration(dashboard.avg_session_duration) }} avg</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
          <p class="text-sm text-gray-500 dark:text-gray-400">Bounce Rate</p>
          <p class="text-2xl font-bold" :class="dashboard.avg_bounce_rate > 60 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400'">{{ dashboard.avg_bounce_rate?.toFixed(1) || 0 }}%</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
          <p class="text-sm text-gray-500 dark:text-gray-400">Feature Uses</p>
          <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ formatNumber(dashboard.total_feature_uses) }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
          <p class="text-sm text-gray-500 dark:text-gray-400">Searches</p>
          <p class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ formatNumber(dashboard.total_searches) }}</p>
          <p class="text-xs text-gray-500">{{ dashboard.zero_result_rate?.toFixed(1) || 0 }}% zero results</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
          <p class="text-sm text-gray-500 dark:text-gray-400">Avg Pages/Session</p>
          <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ dashboard.avg_pages_per_session?.toFixed(1) || 0 }}</p>
        </div>
      </div>

      <!-- Charts Row -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Daily Trends -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Daily Page Views</h3>
          <div class="h-64">
            <canvas ref="dailyChart"></canvas>
          </div>
        </div>

        <!-- Hourly Distribution -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Hourly Distribution</h3>
          <div class="h-64">
            <canvas ref="hourlyChart"></canvas>
          </div>
        </div>
      </div>

      <!-- Top Pages & Features -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Top Pages -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
          <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Top Pages</h3>
          </div>
          <div class="divide-y divide-gray-200 dark:divide-gray-700">
            <div v-for="(page, index) in topPages" :key="page.page_path" class="px-6 py-3 flex items-center justify-between">
              <div class="flex items-center space-x-3">
                <span class="text-sm font-medium text-gray-400 w-6">{{ index + 1 }}</span>
                <span class="text-sm text-gray-900 dark:text-white truncate max-w-xs">{{ page.page_path }}</span>
              </div>
              <div class="text-right">
                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ formatNumber(page.total_views) }}</span>
                <span class="text-xs text-gray-500 ml-2">{{ formatNumber(page.unique_views) }} unique</span>
              </div>
            </div>
            <div v-if="topPages.length === 0" class="px-6 py-4 text-center text-gray-500">
              No page view data available
            </div>
          </div>
        </div>

        <!-- Top Features -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
          <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Top Features</h3>
          </div>
          <div class="divide-y divide-gray-200 dark:divide-gray-700">
            <div v-for="(feature, index) in topFeatures" :key="feature.feature_name" class="px-6 py-3 flex items-center justify-between">
              <div class="flex items-center space-x-3">
                <span class="text-sm font-medium text-gray-400 w-6">{{ index + 1 }}</span>
                <div>
                  <span class="text-sm text-gray-900 dark:text-white">{{ feature.feature_name }}</span>
                  <span class="ml-2 px-2 py-0.5 text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 rounded">{{ feature.category }}</span>
                </div>
              </div>
              <span class="text-sm font-medium text-gray-900 dark:text-white">{{ formatNumber(feature.total_uses) }}</span>
            </div>
            <div v-if="topFeatures.length === 0" class="px-6 py-4 text-center text-gray-500">
              No feature usage data available
            </div>
          </div>
        </div>
      </div>

      <!-- Device & Search Stats -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Device Breakdown -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Devices</h3>
          <div class="space-y-3">
            <div v-for="device in deviceStats" :key="device.device_type" class="flex items-center">
              <svg v-if="device.device_type === 'desktop'" class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
              <svg v-else-if="device.device_type === 'mobile'" class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
              <svg v-else class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
              <div class="flex-1">
                <div class="flex justify-between text-sm">
                  <span class="text-gray-700 dark:text-gray-300 capitalize">{{ device.device_type }}</span>
                  <span class="text-gray-900 dark:text-white font-medium">{{ device.percentage?.toFixed(1) }}%</span>
                </div>
                <div class="mt-1 h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                  <div class="h-full bg-blue-600 rounded-full" :style="{ width: `${device.percentage}%` }"></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Top Searches -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 lg:col-span-2">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Popular Searches</h3>
          <div class="flex flex-wrap gap-2">
            <span v-for="search in topSearches" :key="search.search_term" class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
              {{ search.search_term }}
              <span class="ml-2 text-xs text-gray-500">{{ search.search_count }}</span>
            </span>
            <span v-if="topSearches.length === 0" class="text-gray-500">No search data available</span>
          </div>

          <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mt-6 mb-3">Zero Result Searches</h4>
          <div class="flex flex-wrap gap-2">
            <span v-for="search in zeroResultSearches" :key="search.search_term" class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300">
              {{ search.search_term }}
              <span class="ml-2 text-xs">{{ search.search_count }}</span>
            </span>
            <span v-if="zeroResultSearches.length === 0" class="text-gray-500 text-sm">None</span>
          </div>
        </div>
      </div>

      <!-- User Flows -->
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Popular User Flows</h3>
        <div class="space-y-3">
          <div v-for="flow in userFlows" :key="`${flow.from_page}-${flow.to_page}`" class="flex items-center text-sm">
            <span class="px-2 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded">{{ flow.from_page }}</span>
            <svg class="w-5 h-5 mx-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
            <span class="px-2 py-1 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 rounded">{{ flow.to_page }}</span>
            <span class="ml-auto text-gray-500">{{ formatNumber(flow.transition_count) }} transitions</span>
          </div>
          <div v-if="userFlows.length === 0" class="text-center text-gray-500 py-4">
            No user flow data available yet
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, nextTick, watch } from 'vue';
import axios from 'axios';
import Chart from 'chart.js/auto';

interface DashboardData {
  total_page_views: number;
  unique_page_views: number;
  total_sessions: number;
  avg_session_duration: number;
  avg_bounce_rate: number;
  total_feature_uses: number;
  total_searches: number;
  zero_result_rate: number;
  avg_pages_per_session: number;
}

interface PageStat {
  page_path: string;
  total_views: number;
  unique_views: number;
}

interface FeatureStat {
  feature_name: string;
  category: string;
  total_uses: number;
}

interface DeviceStat {
  device_type: string;
  session_count: number;
  percentage: number;
}

interface SearchStat {
  search_term: string;
  search_count: number;
  avg_results: number;
}

interface UserFlow {
  from_page: string;
  to_page: string;
  transition_count: number;
}

interface DailyTrend {
  date: string;
  total_views: number;
  unique_views: number;
}

interface HourlyData {
  hour: number;
  total_views: number;
}

const loading = ref(true);
const exporting = ref(false);
const dateRange = ref('30');

const dashboard = ref<DashboardData>({
  total_page_views: 0,
  unique_page_views: 0,
  total_sessions: 0,
  avg_session_duration: 0,
  avg_bounce_rate: 0,
  total_feature_uses: 0,
  total_searches: 0,
  zero_result_rate: 0,
  avg_pages_per_session: 0,
});

const topPages = ref<PageStat[]>([]);
const topFeatures = ref<FeatureStat[]>([]);
const deviceStats = ref<DeviceStat[]>([]);
const topSearches = ref<SearchStat[]>([]);
const zeroResultSearches = ref<SearchStat[]>([]);
const userFlows = ref<UserFlow[]>([]);
const dailyTrends = ref<DailyTrend[]>([]);
const hourlyData = ref<HourlyData[]>([]);

const dailyChart = ref<HTMLCanvasElement | null>(null);
const hourlyChart = ref<HTMLCanvasElement | null>(null);
let dailyChartInstance: Chart | null = null;
let hourlyChartInstance: Chart | null = null;

const getDateRange = () => {
  const to = new Date().toISOString().slice(0, 10);
  const from = new Date(Date.now() - parseInt(dateRange.value) * 24 * 60 * 60 * 1000).toISOString().slice(0, 10);
  return { from, to };
};

const fetchDashboard = async () => {
  loading.value = true;
  const { from, to } = getDateRange();

  try {
    const [
      dashboardRes,
      topPagesRes,
      featureRes,
      deviceRes,
      searchRes,
      flowRes,
      dailyRes,
      hourlyRes,
    ] = await Promise.all([
      axios.get('/api/admin/analytics/dashboard', { params: { from, to } }),
      axios.get('/api/admin/analytics/top-pages', { params: { from, to, limit: 10 } }),
      axios.get('/api/admin/analytics/feature-usage', { params: { from, to, limit: 10 } }),
      axios.get('/api/admin/analytics/device-stats', { params: { from, to } }),
      axios.get('/api/admin/analytics/search-analytics', { params: { from, to, limit: 15 } }),
      axios.get('/api/admin/analytics/user-flows', { params: { from, to, limit: 10 } }),
      axios.get('/api/admin/analytics/daily-trends', { params: { from, to } }),
      axios.get('/api/admin/analytics/hourly-distribution', { params: { from, to } }),
    ]);

    dashboard.value = dashboardRes.data.data;
    topPages.value = topPagesRes.data.data || [];
    topFeatures.value = featureRes.data.data || [];
    deviceStats.value = deviceRes.data.data || [];
    topSearches.value = searchRes.data.data?.top_searches || [];
    zeroResultSearches.value = searchRes.data.data?.zero_result_searches || [];
    userFlows.value = flowRes.data.data || [];
    dailyTrends.value = dailyRes.data.data || [];
    hourlyData.value = hourlyRes.data.data || [];

    await nextTick();
    renderCharts();
  } catch (error) {
    console.error('Failed to fetch analytics:', error);
  } finally {
    loading.value = false;
  }
};

const renderCharts = () => {
  // Destroy existing charts
  if (dailyChartInstance) dailyChartInstance.destroy();
  if (hourlyChartInstance) hourlyChartInstance.destroy();

  const isDark = document.documentElement.classList.contains('dark');
  const textColor = isDark ? '#9CA3AF' : '#6B7280';
  const gridColor = isDark ? '#374151' : '#E5E7EB';

  // Daily trends chart
  if (dailyChart.value && dailyTrends.value.length > 0) {
    dailyChartInstance = new Chart(dailyChart.value, {
      type: 'line',
      data: {
        labels: dailyTrends.value.map(d => new Date(d.date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' })),
        datasets: [
          {
            label: 'Total Views',
            data: dailyTrends.value.map(d => d.total_views),
            borderColor: '#3B82F6',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            fill: true,
            tension: 0.3,
          },
          {
            label: 'Unique Views',
            data: dailyTrends.value.map(d => d.unique_views),
            borderColor: '#10B981',
            backgroundColor: 'transparent',
            borderDash: [5, 5],
            tension: 0.3,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'top',
            labels: { color: textColor },
          },
        },
        scales: {
          x: {
            ticks: { color: textColor },
            grid: { color: gridColor },
          },
          y: {
            ticks: { color: textColor },
            grid: { color: gridColor },
            beginAtZero: true,
          },
        },
      },
    });
  }

  // Hourly distribution chart
  if (hourlyChart.value && hourlyData.value.length > 0) {
    hourlyChartInstance = new Chart(hourlyChart.value, {
      type: 'bar',
      data: {
        labels: hourlyData.value.map(d => `${d.hour}:00`),
        datasets: [
          {
            label: 'Page Views',
            data: hourlyData.value.map(d => d.total_views),
            backgroundColor: '#8B5CF6',
            borderRadius: 4,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false,
          },
        },
        scales: {
          x: {
            ticks: { color: textColor },
            grid: { display: false },
          },
          y: {
            ticks: { color: textColor },
            grid: { color: gridColor },
            beginAtZero: true,
          },
        },
      },
    });
  }
};

const exportData = async () => {
  exporting.value = true;
  const { from, to } = getDateRange();

  try {
    const response = await axios.get('/api/admin/analytics/export', {
      params: { from, to },
      responseType: 'blob',
    });

    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.download = `analytics-${from}-to-${to}.csv`;
    link.click();
    window.URL.revokeObjectURL(url);
  } catch (error) {
    console.error('Failed to export analytics:', error);
    alert('Failed to export analytics data');
  } finally {
    exporting.value = false;
  }
};

const formatNumber = (num: number | undefined): string => {
  if (num === undefined || num === null) return '0';
  return num.toLocaleString();
};

const formatDuration = (seconds: number | undefined): string => {
  if (!seconds) return '0s';
  if (seconds < 60) return `${Math.round(seconds)}s`;
  if (seconds < 3600) return `${Math.round(seconds / 60)}m`;
  return `${Math.round(seconds / 3600)}h`;
};

watch(dateRange, () => {
  fetchDashboard();
});

onMounted(() => {
  fetchDashboard();
});
</script>
