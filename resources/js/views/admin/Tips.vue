<template>
  <div class="mt-6 space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Crowdsourced Tips Queue</h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Review and process community-submitted intelligence tips</p>
      </div>
      <div class="flex items-center space-x-2">
        <button @click="bulkAccept" :disabled="selectedTips.length === 0" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 disabled:opacity-50 text-white text-sm font-medium rounded-lg transition-colors">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
          Accept Selected ({{ selectedTips.length }})
        </button>
        <button @click="bulkReject" :disabled="selectedTips.length === 0" class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 disabled:opacity-50 text-white text-sm font-medium rounded-lg transition-colors">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
          Reject Selected
        </button>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">Total Tips</p>
        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ tips.length }}</p>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">Pending Review</p>
        <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ pendingCount }}</p>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">Accepted</p>
        <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ acceptedCount }}</p>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">Rejected</p>
        <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ rejectedCount }}</p>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">Converted to Events</p>
        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ convertedCount }}</p>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search</label>
          <input v-model="filters.search" type="text" placeholder="Search tips..." class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
          <select v-model="filters.status" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            <option value="">All Status</option>
            <option value="pending">Pending</option>
            <option value="reviewing">Reviewing</option>
            <option value="accepted">Accepted</option>
            <option value="rejected">Rejected</option>
            <option value="needs_info">Needs Info</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Priority</label>
          <select v-model="filters.priority" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            <option value="">All Priorities</option>
            <option value="critical">Critical</option>
            <option value="high">High</option>
            <option value="medium">Medium</option>
            <option value="low">Low</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date</label>
          <input v-model="filters.date" type="date" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
        </div>
        <div class="flex items-end">
          <button @click="clearFilters" class="w-full px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
            Clear Filters
          </button>
        </div>
      </div>
    </div>

    <!-- Tips Table -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
        <div class="flex items-center space-x-3">
          <input type="checkbox" @change="toggleSelectAll" :checked="isAllSelected" :indeterminate="isPartiallySelected" class="w-4 h-4 text-blue-600 bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded focus:ring-blue-500" />
          <span class="text-sm text-gray-500 dark:text-gray-400">{{ filteredTips.length }} tips found</span>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50 dark:bg-gray-700/50">
            <tr>
              <th class="w-12 px-6 py-3"></th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tip</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Submitter</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Priority</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Submitted</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            <tr v-for="tip in paginatedTips" :key="tip.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
              <td class="px-6 py-4">
                <input type="checkbox" :checked="selectedTips.includes(tip.id)" @change="toggleSelect(tip.id)" class="w-4 h-4 text-blue-600 bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded focus:ring-blue-500" />
              </td>
              <td class="px-6 py-4">
                <div class="max-w-md">
                  <p class="text-sm font-medium text-gray-900 dark:text-white line-clamp-2">{{ tip.content }}</p>
                  <div class="flex items-center space-x-2 mt-1">
                    <span v-if="tip.location" class="text-xs text-gray-500 dark:text-gray-400 flex items-center">
                      <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                      </svg>
                      {{ tip.location }}
                    </span>
                    <span v-if="tip.attachments_count > 0" class="text-xs text-gray-500 dark:text-gray-400 flex items-center">
                      <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                      </svg>
                      {{ tip.attachments_count }} files
                    </span>
                    <span v-if="tip.source_url" class="text-xs text-blue-600 dark:text-blue-400 flex items-center">
                      <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                      </svg>
                      Source
                    </span>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white text-sm font-medium mr-2">
                    {{ tip.submitter?.name?.charAt(0) || 'A' }}
                  </div>
                  <div>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ tip.submitter?.name || 'Anonymous' }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ tip.submitter?.reputation || 0 }} rep</p>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getPriorityBadgeClass(tip.priority)" class="px-2.5 py-1 text-xs font-medium rounded-full capitalize">
                  {{ tip.priority }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getStatusBadgeClass(tip.status)" class="px-2.5 py-1 text-xs font-medium rounded-full capitalize">
                  {{ tip.status.replace('_', ' ') }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                {{ formatDate(tip.created_at) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right">
                <div class="flex items-center justify-end space-x-2">
                  <button @click="viewTip(tip)" class="p-1.5 text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700" title="View Details">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                  </button>
                  <button v-if="tip.status === 'pending' || tip.status === 'reviewing'" @click="acceptTip(tip)" class="p-1.5 text-gray-400 hover:text-green-600 dark:hover:text-green-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700" title="Accept">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                  </button>
                  <button v-if="tip.status === 'pending' || tip.status === 'reviewing'" @click="requestInfo(tip)" class="p-1.5 text-gray-400 hover:text-yellow-600 dark:hover:text-yellow-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700" title="Request More Info">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                  </button>
                  <button v-if="tip.status === 'pending' || tip.status === 'reviewing'" @click="rejectTip(tip)" class="p-1.5 text-gray-400 hover:text-red-600 dark:hover:text-red-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700" title="Reject">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                  <button v-if="tip.status === 'accepted' && !tip.converted_to_event" @click="convertToEvent(tip)" class="p-1.5 text-gray-400 hover:text-purple-600 dark:hover:text-purple-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700" title="Convert to Event">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
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
          Showing {{ (currentPage - 1) * perPage + 1 }} to {{ Math.min(currentPage * perPage, filteredTips.length) }} of {{ filteredTips.length }} tips
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

    <!-- View Tip Modal -->
    <div v-if="showViewModal" class="fixed inset-0 z-50 overflow-y-auto" @click.self="showViewModal = false">
      <div class="flex items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-black/50" @click="showViewModal = false"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-3xl w-full p-6 max-h-[90vh] overflow-y-auto">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Tip Details</h3>
            <button @click="showViewModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <div v-if="viewingTip" class="space-y-6">
            <!-- Status and Priority -->
            <div class="flex items-center space-x-3">
              <span :class="getStatusBadgeClass(viewingTip.status)" class="px-3 py-1 text-sm font-medium rounded-full capitalize">
                {{ viewingTip.status.replace('_', ' ') }}
              </span>
              <span :class="getPriorityBadgeClass(viewingTip.priority)" class="px-3 py-1 text-sm font-medium rounded-full capitalize">
                {{ viewingTip.priority }} Priority
              </span>
            </div>

            <!-- Content -->
            <div>
              <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">Tip Content</h4>
              <p class="text-gray-600 dark:text-gray-400 whitespace-pre-wrap">{{ viewingTip.content }}</p>
            </div>

            <!-- Details Grid -->
            <div class="grid grid-cols-2 gap-4">
              <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">Submitter</p>
                <div class="flex items-center mt-1">
                  <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white text-sm font-medium mr-2">
                    {{ viewingTip.submitter?.name?.charAt(0) || 'A' }}
                  </div>
                  <div>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ viewingTip.submitter?.name || 'Anonymous' }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ viewingTip.submitter?.reputation || 0 }} reputation</p>
                  </div>
                </div>
              </div>
              <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">Submitted</p>
                <p class="text-sm font-medium text-gray-900 dark:text-white mt-1">{{ formatDate(viewingTip.created_at) }}</p>
              </div>
              <div v-if="viewingTip.location" class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">Location</p>
                <p class="text-sm font-medium text-gray-900 dark:text-white mt-1">{{ viewingTip.location }}</p>
              </div>
              <div v-if="viewingTip.occurred_at" class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">Event Date</p>
                <p class="text-sm font-medium text-gray-900 dark:text-white mt-1">{{ formatDate(viewingTip.occurred_at) }}</p>
              </div>
            </div>

            <!-- Source URL -->
            <div v-if="viewingTip.source_url">
              <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">Source</h4>
              <a :href="viewingTip.source_url" target="_blank" rel="noopener" class="text-blue-600 dark:text-blue-400 hover:underline text-sm break-all">
                {{ viewingTip.source_url }}
              </a>
            </div>

            <!-- Attachments -->
            <div v-if="viewingTip.attachments && viewingTip.attachments.length > 0">
              <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">Attachments ({{ viewingTip.attachments.length }})</h4>
              <div class="grid grid-cols-3 gap-2">
                <div v-for="attachment in viewingTip.attachments" :key="attachment.id" class="relative group">
                  <div class="aspect-square bg-gray-100 dark:bg-gray-700 rounded-lg overflow-hidden">
                    <img v-if="attachment.type === 'image'" :src="attachment.url" class="w-full h-full object-cover" />
                    <div v-else class="w-full h-full flex items-center justify-center">
                      <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                      </svg>
                    </div>
                  </div>
                  <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 truncate">{{ attachment.name }}</p>
                </div>
              </div>
            </div>

            <!-- Review Notes -->
            <div v-if="viewingTip.review_notes">
              <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">Review Notes</h4>
              <p class="text-sm text-gray-600 dark:text-gray-400 bg-yellow-50 dark:bg-yellow-900/20 p-3 rounded-lg">{{ viewingTip.review_notes }}</p>
            </div>

            <!-- Action Buttons -->
            <div v-if="viewingTip.status === 'pending' || viewingTip.status === 'reviewing'" class="flex justify-end space-x-3 pt-4 border-t border-gray-200 dark:border-gray-700">
              <button @click="rejectTip(viewingTip); showViewModal = false" class="px-4 py-2 text-sm font-medium text-red-700 dark:text-red-400 bg-red-100 dark:bg-red-900/30 rounded-lg hover:bg-red-200 dark:hover:bg-red-900/50 transition-colors">
                Reject
              </button>
              <button @click="requestInfo(viewingTip); showViewModal = false" class="px-4 py-2 text-sm font-medium text-yellow-700 dark:text-yellow-400 bg-yellow-100 dark:bg-yellow-900/30 rounded-lg hover:bg-yellow-200 dark:hover:bg-yellow-900/50 transition-colors">
                Request Info
              </button>
              <button @click="acceptTip(viewingTip); showViewModal = false" class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors">
                Accept
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Convert to Event Modal -->
    <div v-if="showConvertModal" class="fixed inset-0 z-50 overflow-y-auto" @click.self="showConvertModal = false">
      <div class="flex items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-black/50" @click="showConvertModal = false"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-lg w-full p-6">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Convert to Event</h3>
            <button @click="showConvertModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <form @submit.prevent="submitConversion" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Event Title *</label>
              <input v-model="convertForm.title" type="text" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Event Type *</label>
              <select v-model="convertForm.event_type" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="combat_engagement">Combat Engagement</option>
                <option value="airstrike">Airstrike</option>
                <option value="artillery_strike">Artillery Strike</option>
                <option value="missile_strike">Missile Strike</option>
                <option value="equipment_destroyed">Equipment Destroyed</option>
                <option value="troop_movement">Troop Movement</option>
                <option value="other">Other</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Confidence Level *</label>
              <select v-model="convertForm.confidence" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="confirmed">Confirmed</option>
                <option value="likely">Likely</option>
                <option value="unconfirmed">Unconfirmed</option>
              </select>
            </div>
            <div class="flex items-center">
              <input v-model="convertForm.verified" type="checkbox" id="verified" class="w-4 h-4 text-blue-600 bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded focus:ring-blue-500" />
              <label for="verified" class="ml-2 text-sm text-gray-600 dark:text-gray-400">Mark as verified</label>
            </div>

            <div class="flex justify-end space-x-3 pt-4">
              <button type="button" @click="showConvertModal = false" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                Cancel
              </button>
              <button type="submit" :disabled="converting" class="px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700 disabled:opacity-50 transition-colors">
                {{ converting ? 'Converting...' : 'Create Event' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue';

interface Submitter {
  id: number;
  name: string;
  email?: string;
  reputation: number;
}

interface Attachment {
  id: number;
  name: string;
  type: string;
  url: string;
}

interface Tip {
  id: number;
  content: string;
  location?: string;
  occurred_at?: string;
  source_url?: string;
  priority: 'critical' | 'high' | 'medium' | 'low';
  status: 'pending' | 'reviewing' | 'accepted' | 'rejected' | 'needs_info';
  submitter?: Submitter;
  attachments_count: number;
  attachments?: Attachment[];
  review_notes?: string;
  reviewed_by_id?: number;
  converted_to_event?: boolean;
  event_id?: number;
  created_at: string;
  updated_at: string;
}

// State
const tips = ref<Tip[]>([]);
const loading = ref(false);
const converting = ref(false);
const currentPage = ref(1);
const perPage = ref(15);
const selectedTips = ref<number[]>([]);

// Modals
const showViewModal = ref(false);
const showConvertModal = ref(false);
const viewingTip = ref<Tip | null>(null);
const convertingTip = ref<Tip | null>(null);

// Filters
const filters = reactive({
  search: '',
  status: '',
  priority: '',
  date: ''
});

// Convert Form
const convertForm = reactive({
  title: '',
  event_type: 'other',
  confidence: 'unconfirmed',
  verified: false
});

// Computed
const pendingCount = computed(() => tips.value.filter(t => t.status === 'pending').length);
const acceptedCount = computed(() => tips.value.filter(t => t.status === 'accepted').length);
const rejectedCount = computed(() => tips.value.filter(t => t.status === 'rejected').length);
const convertedCount = computed(() => tips.value.filter(t => t.converted_to_event).length);

const filteredTips = computed(() => {
  return tips.value.filter(tip => {
    const matchesSearch = !filters.search ||
      tip.content.toLowerCase().includes(filters.search.toLowerCase()) ||
      tip.location?.toLowerCase().includes(filters.search.toLowerCase());
    const matchesStatus = !filters.status || tip.status === filters.status;
    const matchesPriority = !filters.priority || tip.priority === filters.priority;
    const matchesDate = !filters.date || tip.created_at.startsWith(filters.date);
    return matchesSearch && matchesStatus && matchesPriority && matchesDate;
  });
});

const totalPages = computed(() => Math.ceil(filteredTips.value.length / perPage.value) || 1);

const paginatedTips = computed(() => {
  const start = (currentPage.value - 1) * perPage.value;
  return filteredTips.value.slice(start, start + perPage.value);
});

const isAllSelected = computed(() => {
  return paginatedTips.value.length > 0 && paginatedTips.value.every(t => selectedTips.value.includes(t.id));
});

const isPartiallySelected = computed(() => {
  return selectedTips.value.length > 0 && !isAllSelected.value;
});

// Methods
const fetchTips = async () => {
  loading.value = true;
  try {
    tips.value = [
      { id: 1, content: 'Spotted military convoy heading east on Highway M14 near Kherson. Approximately 15 vehicles including tanks and APCs. Time: around 14:30 local.', location: 'Kherson Oblast, Ukraine', occurred_at: '2024-01-20T14:30:00Z', source_url: 'https://twitter.com/user/status/123', priority: 'high', status: 'pending', submitter: { id: 1, name: 'John D.', reputation: 156 }, attachments_count: 3, attachments: [{ id: 1, name: 'convoy1.jpg', type: 'image', url: '/images/placeholder.jpg' }], created_at: '2024-01-20T15:00:00Z', updated_at: '2024-01-20T15:00:00Z' },
      { id: 2, content: 'Explosion heard in Mariupol city center. Multiple blasts over 10 minutes. No visible damage from my location.', location: 'Mariupol, Donetsk Oblast', occurred_at: '2024-01-20T12:15:00Z', priority: 'critical', status: 'reviewing', submitter: { id: 2, name: 'Maria K.', reputation: 342 }, attachments_count: 1, created_at: '2024-01-20T12:30:00Z', updated_at: '2024-01-20T12:30:00Z' },
      { id: 3, content: 'Drone activity observed over industrial area. Multiple small drones flying in formation.', location: 'Zaporizhzhia', occurred_at: '2024-01-19T18:00:00Z', priority: 'medium', status: 'accepted', submitter: { id: 3, name: 'Alex P.', reputation: 89 }, attachments_count: 2, converted_to_event: false, created_at: '2024-01-19T18:30:00Z', updated_at: '2024-01-19T18:30:00Z' },
      { id: 4, content: 'Possible troop movements near railway station. Unverified.', location: 'Odesa', priority: 'low', status: 'rejected', submitter: { id: 4, name: 'Anonymous', reputation: 12 }, attachments_count: 0, review_notes: 'Insufficient evidence, no supporting media', created_at: '2024-01-19T10:00:00Z', updated_at: '2024-01-19T10:00:00Z' },
      { id: 5, content: 'Anti-aircraft fire observed at 03:45 local time. Lasted approximately 5 minutes. Direction: northeast.', location: 'Kyiv', occurred_at: '2024-01-20T03:45:00Z', source_url: 'https://t.me/channel/post', priority: 'high', status: 'pending', submitter: { id: 5, name: 'Natalia S.', reputation: 267 }, attachments_count: 1, created_at: '2024-01-20T04:00:00Z', updated_at: '2024-01-20T04:00:00Z' },
      { id: 6, content: 'Humanitarian aid trucks arriving at checkpoint. Unclear affiliation.', location: 'Kramatorsk', priority: 'low', status: 'needs_info', submitter: { id: 6, name: 'Viktor M.', reputation: 45 }, attachments_count: 0, review_notes: 'Please provide photos or additional details about the vehicles', created_at: '2024-01-18T14:00:00Z', updated_at: '2024-01-18T14:00:00Z' },
      { id: 7, content: 'Artillery bombardment ongoing for past 2 hours. Multiple impact sites visible from residential area.', location: 'Donetsk', occurred_at: '2024-01-20T08:00:00Z', priority: 'critical', status: 'accepted', submitter: { id: 7, name: 'Olena B.', reputation: 198 }, attachments_count: 4, converted_to_event: true, event_id: 145, created_at: '2024-01-20T10:00:00Z', updated_at: '2024-01-20T10:00:00Z' },
    ];
  } catch (error) {
    console.error('Failed to fetch tips:', error);
  } finally {
    loading.value = false;
  }
};

const clearFilters = () => {
  filters.search = '';
  filters.status = '';
  filters.priority = '';
  filters.date = '';
  currentPage.value = 1;
};

const toggleSelectAll = () => {
  if (isAllSelected.value) {
    selectedTips.value = selectedTips.value.filter(id => !paginatedTips.value.some(t => t.id === id));
  } else {
    paginatedTips.value.forEach(tip => {
      if (!selectedTips.value.includes(tip.id)) {
        selectedTips.value.push(tip.id);
      }
    });
  }
};

const toggleSelect = (id: number) => {
  const index = selectedTips.value.indexOf(id);
  if (index === -1) {
    selectedTips.value.push(id);
  } else {
    selectedTips.value.splice(index, 1);
  }
};

const viewTip = (tip: Tip) => {
  viewingTip.value = tip;
  showViewModal.value = true;
};

const acceptTip = (tip: Tip) => {
  tip.status = 'accepted';
};

const rejectTip = (tip: Tip) => {
  tip.status = 'rejected';
};

const requestInfo = (tip: Tip) => {
  tip.status = 'needs_info';
};

const bulkAccept = () => {
  tips.value.forEach(tip => {
    if (selectedTips.value.includes(tip.id) && (tip.status === 'pending' || tip.status === 'reviewing')) {
      tip.status = 'accepted';
    }
  });
  selectedTips.value = [];
};

const bulkReject = () => {
  tips.value.forEach(tip => {
    if (selectedTips.value.includes(tip.id) && (tip.status === 'pending' || tip.status === 'reviewing')) {
      tip.status = 'rejected';
    }
  });
  selectedTips.value = [];
};

const convertToEvent = (tip: Tip) => {
  convertingTip.value = tip;
  convertForm.title = tip.content.slice(0, 100);
  convertForm.event_type = 'other';
  convertForm.confidence = 'unconfirmed';
  convertForm.verified = false;
  showConvertModal.value = true;
};

const submitConversion = async () => {
  if (!convertingTip.value) return;
  converting.value = true;
  try {
    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 500));
    convertingTip.value.converted_to_event = true;
    convertingTip.value.event_id = Math.floor(Math.random() * 1000);
    showConvertModal.value = false;
    convertingTip.value = null;
  } catch (error) {
    console.error('Failed to convert tip:', error);
  } finally {
    converting.value = false;
  }
};

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleString('en-US', {
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

const getPriorityBadgeClass = (priority: string) => {
  const classes: Record<string, string> = {
    critical: 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400',
    high: 'bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400',
    medium: 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400',
    low: 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-400'
  };
  return classes[priority] || classes.low;
};

const getStatusBadgeClass = (status: string) => {
  const classes: Record<string, string> = {
    pending: 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400',
    reviewing: 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400',
    accepted: 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400',
    rejected: 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400',
    needs_info: 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400'
  };
  return classes[status] || classes.pending;
};

onMounted(() => {
  fetchTips();
});
</script>
