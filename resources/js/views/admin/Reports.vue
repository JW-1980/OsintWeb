<template>
  <div class="mt-6 space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Report Management</h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Generate and manage SITREP reports and analytics</p>
      </div>
      <button @click="openGenerateModal" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        Generate Report
      </button>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">Total Reports</p>
        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ reports.length }}</p>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">This Month</p>
        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ thisMonthCount }}</p>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">SITREP</p>
        <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ sitrepCount }}</p>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">Scheduled</p>
        <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ scheduledCount }}</p>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">Total Downloads</p>
        <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ totalDownloads }}</p>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search</label>
          <input v-model="filters.search" type="text" placeholder="Search reports..." class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type</label>
          <select v-model="filters.type" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            <option value="">All Types</option>
            <option value="sitrep">SITREP</option>
            <option value="daily">Daily Summary</option>
            <option value="weekly">Weekly Analysis</option>
            <option value="monthly">Monthly Report</option>
            <option value="custom">Custom</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
          <select v-model="filters.status" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            <option value="">All Status</option>
            <option value="completed">Completed</option>
            <option value="generating">Generating</option>
            <option value="scheduled">Scheduled</option>
            <option value="failed">Failed</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date From</label>
          <input v-model="filters.dateFrom" type="date" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
        </div>
        <div class="flex items-end">
          <button @click="clearFilters" class="w-full px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
            Clear Filters
          </button>
        </div>
      </div>
    </div>

    <!-- Reports Table -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
        <span class="text-sm text-gray-500 dark:text-gray-400">{{ filteredReports.length }} reports found</span>
        <button @click="showScheduleModal = true" class="text-sm text-blue-600 dark:text-blue-400 hover:underline flex items-center">
          <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          Manage Schedules
        </button>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50 dark:bg-gray-700/50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Report</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Type</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Period</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Created</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Downloads</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            <tr v-for="report in paginatedReports" :key="report.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
              <td class="px-6 py-4">
                <div class="flex items-center">
                  <div :class="getReportIconClass(report.type)" class="w-10 h-10 rounded-lg flex items-center justify-center text-white mr-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                  </div>
                  <div>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ report.title }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ report.events_count }} events covered</p>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getTypeBadgeClass(report.type)" class="px-2.5 py-1 text-xs font-medium rounded-full capitalize">
                  {{ report.type }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                {{ formatPeriod(report.period_start, report.period_end) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <span :class="getStatusDotClass(report.status)" class="w-2 h-2 rounded-full mr-2"></span>
                  <span :class="getStatusTextClass(report.status)" class="text-sm capitalize">{{ report.status }}</span>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                {{ formatDate(report.created_at) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                {{ report.downloads_count }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right">
                <div class="flex items-center justify-end space-x-2">
                  <button @click="viewReport(report)" class="p-1.5 text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700" title="View">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                  </button>
                  <button v-if="report.status === 'completed'" @click="downloadReport(report, 'pdf')" class="p-1.5 text-gray-400 hover:text-red-600 dark:hover:text-red-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700" title="Download PDF">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                  </button>
                  <button v-if="report.status === 'completed'" @click="downloadReport(report, 'docx')" class="p-1.5 text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700" title="Download DOCX">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                  </button>
                  <button v-if="report.status === 'failed'" @click="retryReport(report)" class="p-1.5 text-gray-400 hover:text-yellow-600 dark:hover:text-yellow-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700" title="Retry">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                  </button>
                  <button @click="confirmDelete(report)" class="p-1.5 text-gray-400 hover:text-red-600 dark:hover:text-red-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700" title="Delete">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between">
        <div class="text-sm text-gray-500 dark:text-gray-400">
          Showing {{ (currentPage - 1) * perPage + 1 }} to {{ Math.min(currentPage * perPage, filteredReports.length) }} of {{ filteredReports.length }} reports
        </div>
        <div class="flex items-center space-x-2">
          <button @click="currentPage--" :disabled="currentPage === 1" class="px-3 py-1.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 disabled:opacity-50 disabled:cursor-not-allowed">
            Previous
          </button>
          <span class="px-3 py-1.5 text-sm font-medium text-gray-700 dark:text-gray-300">
            Page {{ currentPage }} of {{ totalPages }}
          </span>
          <button @click="currentPage++" :disabled="currentPage === totalPages" class="px-3 py-1.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 disabled:opacity-50 disabled:cursor-not-allowed">
            Next
          </button>
        </div>
      </div>
    </div>

    <!-- Generate Report Modal -->
    <div v-if="showGenerateModal" class="fixed inset-0 z-50 overflow-y-auto" @click.self="closeGenerateModal">
      <div class="flex items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-black/50" @click="closeGenerateModal"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-lg w-full p-6 max-h-[90vh] overflow-y-auto">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Generate New Report</h3>
            <button @click="closeGenerateModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <form @submit.prevent="generateReport" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Report Title *</label>
              <input v-model="generateForm.title" type="text" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Report Type *</label>
              <select v-model="generateForm.type" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="sitrep">SITREP (Situation Report)</option>
                <option value="daily">Daily Summary</option>
                <option value="weekly">Weekly Analysis</option>
                <option value="monthly">Monthly Report</option>
                <option value="custom">Custom Report</option>
              </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Period Start *</label>
                <input v-model="generateForm.period_start" type="date" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Period End *</label>
                <input v-model="generateForm.period_end" type="date" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Include Sections</label>
              <div class="space-y-2">
                <label class="flex items-center">
                  <input v-model="generateForm.sections.summary" type="checkbox" class="w-4 h-4 text-blue-600 bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded focus:ring-blue-500" />
                  <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Executive Summary</span>
                </label>
                <label class="flex items-center">
                  <input v-model="generateForm.sections.events" type="checkbox" class="w-4 h-4 text-blue-600 bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded focus:ring-blue-500" />
                  <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Event Timeline</span>
                </label>
                <label class="flex items-center">
                  <input v-model="generateForm.sections.actors" type="checkbox" class="w-4 h-4 text-blue-600 bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded focus:ring-blue-500" />
                  <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Actor Analysis</span>
                </label>
                <label class="flex items-center">
                  <input v-model="generateForm.sections.maps" type="checkbox" class="w-4 h-4 text-blue-600 bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded focus:ring-blue-500" />
                  <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Geographic Maps</span>
                </label>
                <label class="flex items-center">
                  <input v-model="generateForm.sections.statistics" type="checkbox" class="w-4 h-4 text-blue-600 bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded focus:ring-blue-500" />
                  <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Statistics & Charts</span>
                </label>
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Output Formats</label>
              <div class="flex space-x-4">
                <label class="flex items-center">
                  <input v-model="generateForm.formats.pdf" type="checkbox" class="w-4 h-4 text-blue-600 bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded focus:ring-blue-500" />
                  <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">PDF</span>
                </label>
                <label class="flex items-center">
                  <input v-model="generateForm.formats.docx" type="checkbox" class="w-4 h-4 text-blue-600 bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded focus:ring-blue-500" />
                  <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">DOCX</span>
                </label>
                <label class="flex items-center">
                  <input v-model="generateForm.formats.html" type="checkbox" class="w-4 h-4 text-blue-600 bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded focus:ring-blue-500" />
                  <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">HTML</span>
                </label>
              </div>
            </div>

            <div v-if="formError" class="p-3 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 rounded-lg text-sm">
              {{ formError }}
            </div>

            <div class="flex justify-end space-x-3 pt-4">
              <button type="button" @click="closeGenerateModal" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                Cancel
              </button>
              <button type="submit" :disabled="generating" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50 transition-colors">
                {{ generating ? 'Generating...' : 'Generate Report' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- View Report Modal -->
    <div v-if="showViewModal" class="fixed inset-0 z-50 overflow-y-auto" @click.self="showViewModal = false">
      <div class="flex items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-black/50" @click="showViewModal = false"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-3xl w-full p-6 max-h-[90vh] overflow-y-auto">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Report Details</h3>
            <button @click="showViewModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <div v-if="viewingReport" class="space-y-6">
            <div class="flex items-start space-x-4">
              <div :class="getReportIconClass(viewingReport.type)" class="w-16 h-16 rounded-xl flex items-center justify-center text-white">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
              </div>
              <div>
                <h4 class="text-xl font-semibold text-gray-900 dark:text-white">{{ viewingReport.title }}</h4>
                <div class="flex items-center space-x-2 mt-1">
                  <span :class="getTypeBadgeClass(viewingReport.type)" class="px-2.5 py-1 text-xs font-medium rounded-full capitalize">
                    {{ viewingReport.type }}
                  </span>
                  <span :class="getStatusBadgeClass(viewingReport.status)" class="px-2.5 py-1 text-xs font-medium rounded-full capitalize">
                    {{ viewingReport.status }}
                  </span>
                </div>
              </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
              <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">Period</p>
                <p class="text-sm font-medium text-gray-900 dark:text-white mt-1">{{ formatPeriod(viewingReport.period_start, viewingReport.period_end) }}</p>
              </div>
              <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">Events</p>
                <p class="text-sm font-medium text-gray-900 dark:text-white mt-1">{{ viewingReport.events_count }}</p>
              </div>
              <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">Downloads</p>
                <p class="text-sm font-medium text-gray-900 dark:text-white mt-1">{{ viewingReport.downloads_count }}</p>
              </div>
              <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">Created By</p>
                <p class="text-sm font-medium text-gray-900 dark:text-white mt-1">{{ viewingReport.created_by?.name || 'System' }}</p>
              </div>
            </div>

            <div v-if="viewingReport.sections && viewingReport.sections.length > 0">
              <h5 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">Included Sections</h5>
              <div class="flex flex-wrap gap-2">
                <span v-for="section in viewingReport.sections" :key="section" class="px-3 py-1 text-sm font-medium rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 capitalize">
                  {{ section }}
                </span>
              </div>
            </div>

            <div v-if="viewingReport.status === 'completed'">
              <h5 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">Download Options</h5>
              <div class="flex space-x-3">
                <button @click="downloadReport(viewingReport, 'pdf')" class="flex items-center px-4 py-2 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 rounded-lg hover:bg-red-200 dark:hover:bg-red-900/50 transition-colors">
                  <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  </svg>
                  Download PDF
                </button>
                <button @click="downloadReport(viewingReport, 'docx')" class="flex items-center px-4 py-2 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 rounded-lg hover:bg-blue-200 dark:hover:bg-blue-900/50 transition-colors">
                  <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                  </svg>
                  Download DOCX
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Schedule Modal -->
    <div v-if="showScheduleModal" class="fixed inset-0 z-50 overflow-y-auto" @click.self="showScheduleModal = false">
      <div class="flex items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-black/50" @click="showScheduleModal = false"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-2xl w-full p-6 max-h-[90vh] overflow-y-auto">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Scheduled Reports</h3>
            <button @click="showScheduleModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <div class="space-y-4">
            <div v-for="schedule in schedules" :key="schedule.id" class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
              <div>
                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ schedule.name }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ schedule.frequency }} | Next: {{ formatDate(schedule.next_run) }}</p>
              </div>
              <div class="flex items-center space-x-2">
                <button @click="schedule.is_active = !schedule.is_active" :class="schedule.is_active ? 'bg-green-500' : 'bg-gray-300 dark:bg-gray-600'" class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none">
                  <span :class="schedule.is_active ? 'translate-x-5' : 'translate-x-0'" class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
                </button>
                <button class="p-1.5 text-gray-400 hover:text-red-600 dark:hover:text-red-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                </button>
              </div>
            </div>

            <button class="w-full py-3 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg text-gray-500 dark:text-gray-400 hover:border-blue-500 hover:text-blue-500 transition-colors">
              + Add New Schedule
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 z-50 overflow-y-auto" @click.self="showDeleteModal = false">
      <div class="flex items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-black/50" @click="showDeleteModal = false"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-md w-full p-6">
          <div class="text-center">
            <div class="w-12 h-12 mx-auto bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center mb-4">
              <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Delete Report</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
              Are you sure you want to delete <strong>{{ deletingReport?.title }}</strong>? This action cannot be undone.
            </p>
            <div class="flex justify-center space-x-3">
              <button @click="showDeleteModal = false" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                Cancel
              </button>
              <button @click="deleteReport" :disabled="deleting" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 disabled:opacity-50 transition-colors">
                {{ deleting ? 'Deleting...' : 'Delete Report' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue';

interface User {
  id: number;
  name: string;
}

interface Report {
  id: number;
  title: string;
  type: 'sitrep' | 'daily' | 'weekly' | 'monthly' | 'custom';
  status: 'completed' | 'generating' | 'scheduled' | 'failed';
  period_start: string;
  period_end: string;
  events_count: number;
  downloads_count: number;
  sections?: string[];
  created_by?: User;
  created_at: string;
  updated_at: string;
}

interface Schedule {
  id: number;
  name: string;
  type: string;
  frequency: string;
  next_run: string;
  is_active: boolean;
}

// State
const reports = ref<Report[]>([]);
const schedules = ref<Schedule[]>([]);
const loading = ref(false);
const generating = ref(false);
const deleting = ref(false);
const currentPage = ref(1);
const perPage = ref(10);

// Modals
const showGenerateModal = ref(false);
const showViewModal = ref(false);
const showScheduleModal = ref(false);
const showDeleteModal = ref(false);
const viewingReport = ref<Report | null>(null);
const deletingReport = ref<Report | null>(null);
const formError = ref('');

// Generate Form
const generateForm = reactive({
  title: '',
  type: 'sitrep' as Report['type'],
  period_start: '',
  period_end: '',
  sections: {
    summary: true,
    events: true,
    actors: true,
    maps: true,
    statistics: true
  },
  formats: {
    pdf: true,
    docx: true,
    html: false
  }
});

// Filters
const filters = reactive({
  search: '',
  type: '',
  status: '',
  dateFrom: ''
});

// Computed
const thisMonthCount = computed(() => {
  const now = new Date();
  const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
  return reports.value.filter(r => new Date(r.created_at) >= firstDay).length;
});
const sitrepCount = computed(() => reports.value.filter(r => r.type === 'sitrep').length);
const scheduledCount = computed(() => schedules.value.filter(s => s.is_active).length);
const totalDownloads = computed(() => reports.value.reduce((sum, r) => sum + r.downloads_count, 0));

const filteredReports = computed(() => {
  return reports.value.filter(report => {
    const matchesSearch = !filters.search ||
      report.title.toLowerCase().includes(filters.search.toLowerCase());
    const matchesType = !filters.type || report.type === filters.type;
    const matchesStatus = !filters.status || report.status === filters.status;
    const matchesDate = !filters.dateFrom || report.created_at >= filters.dateFrom;
    return matchesSearch && matchesType && matchesStatus && matchesDate;
  });
});

const totalPages = computed(() => Math.ceil(filteredReports.value.length / perPage.value) || 1);

const paginatedReports = computed(() => {
  const start = (currentPage.value - 1) * perPage.value;
  return filteredReports.value.slice(start, start + perPage.value);
});

// Methods
const fetchReports = async () => {
  loading.value = true;
  try {
    reports.value = [
      { id: 1, title: 'Daily SITREP - January 20, 2024', type: 'sitrep', status: 'completed', period_start: '2024-01-20', period_end: '2024-01-20', events_count: 47, downloads_count: 23, sections: ['summary', 'events', 'maps'], created_by: { id: 1, name: 'Admin' }, created_at: '2024-01-20T18:00:00Z', updated_at: '2024-01-20T18:00:00Z' },
      { id: 2, title: 'Weekly Analysis - Week 3', type: 'weekly', status: 'completed', period_start: '2024-01-15', period_end: '2024-01-21', events_count: 312, downloads_count: 45, sections: ['summary', 'events', 'actors', 'statistics'], created_by: { id: 1, name: 'Admin' }, created_at: '2024-01-21T00:00:00Z', updated_at: '2024-01-21T00:00:00Z' },
      { id: 3, title: 'Daily SITREP - January 19, 2024', type: 'sitrep', status: 'completed', period_start: '2024-01-19', period_end: '2024-01-19', events_count: 52, downloads_count: 31, sections: ['summary', 'events', 'maps'], created_by: { id: 1, name: 'Admin' }, created_at: '2024-01-19T18:00:00Z', updated_at: '2024-01-19T18:00:00Z' },
      { id: 4, title: 'Monthly Report - December 2023', type: 'monthly', status: 'completed', period_start: '2023-12-01', period_end: '2023-12-31', events_count: 1456, downloads_count: 89, sections: ['summary', 'events', 'actors', 'maps', 'statistics'], created_by: { id: 2, name: 'Analyst' }, created_at: '2024-01-02T00:00:00Z', updated_at: '2024-01-02T00:00:00Z' },
      { id: 5, title: 'Custom Report - Kherson Oblast', type: 'custom', status: 'completed', period_start: '2024-01-01', period_end: '2024-01-15', events_count: 234, downloads_count: 12, sections: ['events', 'maps'], created_by: { id: 3, name: 'Jane' }, created_at: '2024-01-16T10:00:00Z', updated_at: '2024-01-16T10:00:00Z' },
      { id: 6, title: 'Daily SITREP - January 21, 2024', type: 'sitrep', status: 'generating', period_start: '2024-01-21', period_end: '2024-01-21', events_count: 0, downloads_count: 0, created_at: '2024-01-21T18:00:00Z', updated_at: '2024-01-21T18:00:00Z' },
      { id: 7, title: 'Weekly Analysis - Week 4', type: 'weekly', status: 'scheduled', period_start: '2024-01-22', period_end: '2024-01-28', events_count: 0, downloads_count: 0, created_at: '2024-01-21T00:00:00Z', updated_at: '2024-01-21T00:00:00Z' },
      { id: 8, title: 'Equipment Analysis Report', type: 'custom', status: 'failed', period_start: '2024-01-10', period_end: '2024-01-20', events_count: 0, downloads_count: 0, created_by: { id: 1, name: 'Admin' }, created_at: '2024-01-20T12:00:00Z', updated_at: '2024-01-20T12:00:00Z' },
    ];

    schedules.value = [
      { id: 1, name: 'Daily SITREP', type: 'sitrep', frequency: 'Daily at 18:00 UTC', next_run: '2024-01-21T18:00:00Z', is_active: true },
      { id: 2, name: 'Weekly Analysis', type: 'weekly', frequency: 'Every Monday at 00:00 UTC', next_run: '2024-01-22T00:00:00Z', is_active: true },
      { id: 3, name: 'Monthly Report', type: 'monthly', frequency: '1st of each month at 00:00 UTC', next_run: '2024-02-01T00:00:00Z', is_active: true },
    ];
  } catch (error) {
    console.error('Failed to fetch reports:', error);
  } finally {
    loading.value = false;
  }
};

const openGenerateModal = () => {
  const today = new Date().toISOString().split('T')[0];
  Object.assign(generateForm, {
    title: '',
    type: 'sitrep',
    period_start: today,
    period_end: today,
    sections: {
      summary: true,
      events: true,
      actors: true,
      maps: true,
      statistics: true
    },
    formats: {
      pdf: true,
      docx: true,
      html: false
    }
  });
  formError.value = '';
  showGenerateModal.value = true;
};

const closeGenerateModal = () => {
  showGenerateModal.value = false;
};

const generateReport = async () => {
  generating.value = true;
  formError.value = '';
  try {
    const selectedSections = Object.entries(generateForm.sections)
      .filter(([_, v]) => v)
      .map(([k, _]) => k);

    const newReport: Report = {
      id: reports.value.length + 1,
      title: generateForm.title,
      type: generateForm.type,
      status: 'generating',
      period_start: generateForm.period_start,
      period_end: generateForm.period_end,
      events_count: 0,
      downloads_count: 0,
      sections: selectedSections,
      created_at: new Date().toISOString(),
      updated_at: new Date().toISOString()
    };
    reports.value.unshift(newReport);

    // Simulate generation completion
    setTimeout(() => {
      newReport.status = 'completed';
      newReport.events_count = Math.floor(Math.random() * 100) + 20;
    }, 3000);

    closeGenerateModal();
  } catch (error: any) {
    formError.value = error?.message || 'Failed to generate report';
  } finally {
    generating.value = false;
  }
};

const viewReport = (report: Report) => {
  viewingReport.value = report;
  showViewModal.value = true;
};

const downloadReport = (report: Report, format: string) => {
  console.log(`Downloading ${report.title} as ${format}`);
  report.downloads_count++;
};

const retryReport = (report: Report) => {
  report.status = 'generating';
  setTimeout(() => {
    report.status = 'completed';
    report.events_count = Math.floor(Math.random() * 100) + 20;
  }, 3000);
};

const confirmDelete = (report: Report) => {
  deletingReport.value = report;
  showDeleteModal.value = true;
};

const deleteReport = async () => {
  if (!deletingReport.value) return;
  deleting.value = true;
  try {
    reports.value = reports.value.filter(r => r.id !== deletingReport.value!.id);
    showDeleteModal.value = false;
    deletingReport.value = null;
  } catch (error) {
    console.error('Failed to delete report:', error);
  } finally {
    deleting.value = false;
  }
};

const clearFilters = () => {
  filters.search = '';
  filters.type = '';
  filters.status = '';
  filters.dateFrom = '';
  currentPage.value = 1;
};

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric'
  });
};

const formatPeriod = (start: string, end: string) => {
  if (start === end) {
    return formatDate(start);
  }
  return `${formatDate(start)} - ${formatDate(end)}`;
};

const getReportIconClass = (type: string) => {
  const classes: Record<string, string> = {
    sitrep: 'bg-red-500',
    daily: 'bg-blue-500',
    weekly: 'bg-green-500',
    monthly: 'bg-purple-500',
    custom: 'bg-yellow-500'
  };
  return classes[type] || 'bg-gray-500';
};

const getTypeBadgeClass = (type: string) => {
  const classes: Record<string, string> = {
    sitrep: 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400',
    daily: 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400',
    weekly: 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400',
    monthly: 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400',
    custom: 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400'
  };
  return classes[type] || 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-400';
};

const getStatusDotClass = (status: string) => {
  const classes: Record<string, string> = {
    completed: 'bg-green-500',
    generating: 'bg-blue-500 animate-pulse',
    scheduled: 'bg-yellow-500',
    failed: 'bg-red-500'
  };
  return classes[status] || 'bg-gray-400';
};

const getStatusTextClass = (status: string) => {
  const classes: Record<string, string> = {
    completed: 'text-green-600 dark:text-green-400',
    generating: 'text-blue-600 dark:text-blue-400',
    scheduled: 'text-yellow-600 dark:text-yellow-400',
    failed: 'text-red-600 dark:text-red-400'
  };
  return classes[status] || 'text-gray-600 dark:text-gray-400';
};

const getStatusBadgeClass = (status: string) => {
  const classes: Record<string, string> = {
    completed: 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400',
    generating: 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400',
    scheduled: 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400',
    failed: 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400'
  };
  return classes[status] || 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-400';
};

onMounted(() => {
  fetchReports();
});
</script>
