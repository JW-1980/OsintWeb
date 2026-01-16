<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue';
import { useEventsStore } from '@/stores/events';
import type { EventType, ConfidenceLevel } from '@/types';

const eventsStore = useEventsStore();

const loading = ref(false);
const generating = ref(false);
const previewMode = ref(false);
const activeTab = ref<'generate' | 'history'>('generate');

const reportForm = reactive({
  title: '',
  description: '',
  dateFrom: '',
  dateTo: '',
  eventTypes: [] as EventType[],
  regions: [] as string[],
  confidenceLevels: [] as ConfidenceLevel[],
  includeCharts: true,
  includeMap: true,
  includeSources: true
});

const exportFormat = ref<'pdf' | 'docx' | 'csv'>('pdf');

const eventTypes: { value: EventType; label: string }[] = [
  { value: 'combat_engagement', label: 'Combat Engagement' },
  { value: 'airstrike', label: 'Airstrike' },
  { value: 'artillery_strike', label: 'Artillery Strike' },
  { value: 'missile_strike', label: 'Missile Strike' },
  { value: 'equipment_destroyed', label: 'Equipment Destroyed' },
  { value: 'equipment_captured', label: 'Equipment Captured' },
  { value: 'troop_movement', label: 'Troop Movement' },
  { value: 'civilian_casualties', label: 'Civilian Casualties' }
];

const confidenceLevels: { value: ConfidenceLevel; label: string }[] = [
  { value: 'confirmed', label: 'Confirmed' },
  { value: 'likely', label: 'Likely' },
  { value: 'unconfirmed', label: 'Unconfirmed' }
];

const regions = [
  'Eastern Europe',
  'Middle East',
  'Africa',
  'Asia Pacific',
  'Latin America',
  'Central Asia'
];

const pastReports = ref([
  { id: 1, title: 'Weekly Conflict Summary', createdAt: '2026-01-15', format: 'pdf', size: '2.4 MB' },
  { id: 2, title: 'Monthly Activity Report', createdAt: '2026-01-10', format: 'docx', size: '1.8 MB' },
  { id: 3, title: 'Regional Analysis Export', createdAt: '2026-01-05', format: 'csv', size: '856 KB' },
  { id: 4, title: 'Equipment Tracking Report', createdAt: '2026-01-01', format: 'pdf', size: '3.1 MB' }
]);

const previewStats = computed(() => {
  return {
    totalEvents: 156,
    verifiedEvents: 89,
    dateRange: `${reportForm.dateFrom || 'N/A'} - ${reportForm.dateTo || 'N/A'}`,
    eventTypesCount: reportForm.eventTypes.length || 'All',
    regionsCount: reportForm.regions.length || 'All'
  };
});

const isFormValid = computed(() => {
  return reportForm.title && reportForm.dateFrom && reportForm.dateTo;
});

const toggleEventType = (type: EventType) => {
  const index = reportForm.eventTypes.indexOf(type);
  if (index === -1) {
    reportForm.eventTypes.push(type);
  } else {
    reportForm.eventTypes.splice(index, 1);
  }
};

const toggleRegion = (region: string) => {
  const index = reportForm.regions.indexOf(region);
  if (index === -1) {
    reportForm.regions.push(region);
  } else {
    reportForm.regions.splice(index, 1);
  }
};

const toggleConfidenceLevel = (level: ConfidenceLevel) => {
  const index = reportForm.confidenceLevels.indexOf(level);
  if (index === -1) {
    reportForm.confidenceLevels.push(level);
  } else {
    reportForm.confidenceLevels.splice(index, 1);
  }
};

const generatePreview = async () => {
  if (!isFormValid.value) return;

  generating.value = true;
  try {
    await new Promise(resolve => setTimeout(resolve, 1500));
    previewMode.value = true;
  } finally {
    generating.value = false;
  }
};

const exportReport = async () => {
  generating.value = true;
  try {
    await new Promise(resolve => setTimeout(resolve, 2000));
    alert(`Report exported as ${exportFormat.value.toUpperCase()}`);
  } finally {
    generating.value = false;
  }
};

const downloadPastReport = (reportId: number) => {
  console.log('Downloading report:', reportId);
  alert('Download started...');
};

const deletePastReport = (reportId: number) => {
  pastReports.value = pastReports.value.filter(r => r.id !== reportId);
};

const resetForm = () => {
  reportForm.title = '';
  reportForm.description = '';
  reportForm.dateFrom = '';
  reportForm.dateTo = '';
  reportForm.eventTypes = [];
  reportForm.regions = [];
  reportForm.confidenceLevels = [];
  previewMode.value = false;
};

onMounted(async () => {
  loading.value = true;
  try {
    await eventsStore.fetchEvents();
  } finally {
    loading.value = false;
  }
});
</script>

<template>
  <div class="p-6 bg-gray-50 dark:bg-gray-900 min-h-screen">
    <div class="max-w-6xl mx-auto">
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Reports</h1>
        <p class="mt-1 text-gray-600 dark:text-gray-400">Generate custom reports and export data</p>
      </div>

      <!-- Tabs -->
      <div class="flex space-x-4 mb-6">
        <button
          @click="activeTab = 'generate'"
          :class="[
            'px-4 py-2 rounded-lg font-medium transition-colors',
            activeTab === 'generate'
              ? 'bg-blue-600 text-white'
              : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
          ]"
        >
          Generate Report
        </button>
        <button
          @click="activeTab = 'history'"
          :class="[
            'px-4 py-2 rounded-lg font-medium transition-colors',
            activeTab === 'history'
              ? 'bg-blue-600 text-white'
              : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
          ]"
        >
          Past Reports
        </button>
      </div>

      <!-- Generate Report Tab -->
      <div v-if="activeTab === 'generate'" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Report Configuration -->
        <div class="lg:col-span-2 space-y-6">
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Report Details</h2>
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  Report Title <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="reportForm.title"
                  type="text"
                  class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="e.g., Weekly Conflict Summary"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  Description
                </label>
                <textarea
                  v-model="reportForm.description"
                  rows="3"
                  class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="Brief description of this report..."
                ></textarea>
              </div>
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Date From <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model="reportForm.dateFrom"
                    type="date"
                    class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Date To <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model="reportForm.dateTo"
                    type="date"
                    class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Event Types</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">Select event types to include (leave empty for all)</p>
            <div class="flex flex-wrap gap-2">
              <button
                v-for="type in eventTypes"
                :key="type.value"
                @click="toggleEventType(type.value)"
                :class="[
                  'px-3 py-1.5 rounded-full text-sm font-medium transition-colors',
                  reportForm.eventTypes.includes(type.value)
                    ? 'bg-blue-600 text-white'
                    : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'
                ]"
              >
                {{ type.label }}
              </button>
            </div>
          </div>

          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Regions</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">Select regions to include (leave empty for all)</p>
            <div class="flex flex-wrap gap-2">
              <button
                v-for="region in regions"
                :key="region"
                @click="toggleRegion(region)"
                :class="[
                  'px-3 py-1.5 rounded-full text-sm font-medium transition-colors',
                  reportForm.regions.includes(region)
                    ? 'bg-green-600 text-white'
                    : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'
                ]"
              >
                {{ region }}
              </button>
            </div>
          </div>

          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Confidence Levels</h2>
            <div class="flex flex-wrap gap-2">
              <button
                v-for="level in confidenceLevels"
                :key="level.value"
                @click="toggleConfidenceLevel(level.value)"
                :class="[
                  'px-3 py-1.5 rounded-full text-sm font-medium transition-colors',
                  reportForm.confidenceLevels.includes(level.value)
                    ? 'bg-purple-600 text-white'
                    : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'
                ]"
              >
                {{ level.label }}
              </button>
            </div>
          </div>

          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Include in Report</h2>
            <div class="space-y-3">
              <label class="flex items-center">
                <input
                  v-model="reportForm.includeCharts"
                  type="checkbox"
                  class="w-4 h-4 text-blue-600 bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded focus:ring-blue-500"
                />
                <span class="ml-3 text-gray-700 dark:text-gray-300">Charts and visualizations</span>
              </label>
              <label class="flex items-center">
                <input
                  v-model="reportForm.includeMap"
                  type="checkbox"
                  class="w-4 h-4 text-blue-600 bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded focus:ring-blue-500"
                />
                <span class="ml-3 text-gray-700 dark:text-gray-300">Map snapshots</span>
              </label>
              <label class="flex items-center">
                <input
                  v-model="reportForm.includeSources"
                  type="checkbox"
                  class="w-4 h-4 text-blue-600 bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded focus:ring-blue-500"
                />
                <span class="ml-3 text-gray-700 dark:text-gray-300">Source references</span>
              </label>
            </div>
          </div>
        </div>

        <!-- Preview & Export -->
        <div class="space-y-6">
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 sticky top-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Preview & Export</h2>

            <div v-if="previewMode" class="mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
              <h3 class="font-medium text-gray-900 dark:text-white mb-3">Report Summary</h3>
              <dl class="space-y-2 text-sm">
                <div class="flex justify-between">
                  <dt class="text-gray-500 dark:text-gray-400">Total Events</dt>
                  <dd class="font-medium text-gray-900 dark:text-white">{{ previewStats.totalEvents }}</dd>
                </div>
                <div class="flex justify-between">
                  <dt class="text-gray-500 dark:text-gray-400">Verified Events</dt>
                  <dd class="font-medium text-gray-900 dark:text-white">{{ previewStats.verifiedEvents }}</dd>
                </div>
                <div class="flex justify-between">
                  <dt class="text-gray-500 dark:text-gray-400">Date Range</dt>
                  <dd class="font-medium text-gray-900 dark:text-white">{{ previewStats.dateRange }}</dd>
                </div>
                <div class="flex justify-between">
                  <dt class="text-gray-500 dark:text-gray-400">Event Types</dt>
                  <dd class="font-medium text-gray-900 dark:text-white">{{ previewStats.eventTypesCount }}</dd>
                </div>
                <div class="flex justify-between">
                  <dt class="text-gray-500 dark:text-gray-400">Regions</dt>
                  <dd class="font-medium text-gray-900 dark:text-white">{{ previewStats.regionsCount }}</dd>
                </div>
              </dl>
            </div>

            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Export Format</label>
              <div class="flex space-x-2">
                <button
                  v-for="format in ['pdf', 'docx', 'csv'] as const"
                  :key="format"
                  @click="exportFormat = format"
                  :class="[
                    'flex-1 py-2 rounded-lg text-sm font-medium transition-colors',
                    exportFormat === format
                      ? 'bg-blue-600 text-white'
                      : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'
                  ]"
                >
                  {{ format.toUpperCase() }}
                </button>
              </div>
            </div>

            <div class="space-y-3">
              <button
                @click="generatePreview"
                :disabled="!isFormValid || generating"
                class="w-full py-2.5 px-4 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg font-medium hover:bg-gray-300 dark:hover:bg-gray-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
              >
                {{ generating ? 'Generating...' : 'Preview Report' }}
              </button>
              <button
                @click="exportReport"
                :disabled="!previewMode || generating"
                class="w-full py-2.5 px-4 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors flex items-center justify-center"
              >
                <svg v-if="generating" class="animate-spin -ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>{{ generating ? 'Exporting...' : `Export as ${exportFormat.toUpperCase()}` }}</span>
              </button>
              <button
                @click="resetForm"
                class="w-full py-2.5 px-4 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 font-medium transition-colors"
              >
                Reset Form
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Past Reports Tab -->
      <div v-if="activeTab === 'history'">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Generated Reports</h2>
          </div>
          <div class="divide-y divide-gray-200 dark:divide-gray-700">
            <div
              v-for="report in pastReports"
              :key="report.id"
              class="px-6 py-4 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700/50"
            >
              <div class="flex items-center space-x-4">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center"
                  :class="{
                    'bg-red-100 dark:bg-red-900/30': report.format === 'pdf',
                    'bg-blue-100 dark:bg-blue-900/30': report.format === 'docx',
                    'bg-green-100 dark:bg-green-900/30': report.format === 'csv'
                  }"
                >
                  <svg class="w-5 h-5"
                    :class="{
                      'text-red-600 dark:text-red-400': report.format === 'pdf',
                      'text-blue-600 dark:text-blue-400': report.format === 'docx',
                      'text-green-600 dark:text-green-400': report.format === 'csv'
                    }"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  </svg>
                </div>
                <div>
                  <p class="font-medium text-gray-900 dark:text-white">{{ report.title }}</p>
                  <p class="text-sm text-gray-500 dark:text-gray-400">{{ report.createdAt }} - {{ report.size }}</p>
                </div>
              </div>
              <div class="flex items-center space-x-2">
                <button
                  @click="downloadPastReport(report.id)"
                  class="p-2 text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                  </svg>
                </button>
                <button
                  @click="deletePastReport(report.id)"
                  class="p-2 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                </button>
              </div>
            </div>
          </div>
          <div v-if="pastReports.length === 0" class="px-6 py-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <p class="mt-2 text-gray-500 dark:text-gray-400">No reports generated yet</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
