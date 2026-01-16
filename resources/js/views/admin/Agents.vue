<template>
  <div class="mt-6 space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h2 class="text-xl font-bold text-gray-900 dark:text-white">AI Agent Management</h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Configure and manage automated OSINT collection agents</p>
      </div>
      <button @click="openCreateModal" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Add Agent
      </button>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">Total Agents</p>
        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ agents.length }}</p>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">Running</p>
        <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ runningCount }}</p>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">Stopped</p>
        <p class="text-2xl font-bold text-gray-600 dark:text-gray-400">{{ stoppedCount }}</p>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">Errors</p>
        <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ errorCount }}</p>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">Events Today</p>
        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ eventsToday }}</p>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search</label>
          <input v-model="filters.search" type="text" placeholder="Search agents..." class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type</label>
          <select v-model="filters.type" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            <option value="">All Types</option>
            <option value="scraper">Scraper</option>
            <option value="monitor">Monitor</option>
            <option value="analyzer">Analyzer</option>
            <option value="aggregator">Aggregator</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
          <select v-model="filters.status" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            <option value="">All Status</option>
            <option value="running">Running</option>
            <option value="stopped">Stopped</option>
            <option value="error">Error</option>
            <option value="paused">Paused</option>
          </select>
        </div>
        <div class="flex items-end">
          <button @click="clearFilters" class="w-full px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
            Clear Filters
          </button>
        </div>
      </div>
    </div>

    <!-- Agents Table -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
        <span class="text-sm text-gray-500 dark:text-gray-400">{{ filteredAgents.length }} agents found</span>
        <div class="flex items-center space-x-2">
          <button @click="startAllAgents" class="text-sm text-green-600 dark:text-green-400 hover:underline">Start All</button>
          <span class="text-gray-300 dark:text-gray-600">|</span>
          <button @click="stopAllAgents" class="text-sm text-red-600 dark:text-red-400 hover:underline">Stop All</button>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50 dark:bg-gray-700/50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Agent</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Type</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Skills</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Events</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Last Run</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            <tr v-for="agent in paginatedAgents" :key="agent.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div :class="getAgentIconClass(agent.type)" class="w-10 h-10 rounded-lg flex items-center justify-center text-white mr-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                  </div>
                  <div>
                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ agent.name }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ agent.description }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getTypeBadgeClass(agent.type)" class="px-2.5 py-1 text-xs font-medium rounded-full capitalize">
                  {{ agent.type }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <span :class="getStatusDotClass(agent.status)" class="w-2 h-2 rounded-full mr-2"></span>
                  <span :class="getStatusTextClass(agent.status)" class="text-sm capitalize">{{ agent.status }}</span>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex -space-x-1">
                  <span v-for="(skill, index) in agent.skills.slice(0, 3)" :key="index" class="px-2 py-0.5 text-xs font-medium rounded-full bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 border border-white dark:border-gray-800" :title="skill.name">
                    {{ skill.name.slice(0, 8) }}
                  </span>
                  <span v-if="agent.skills.length > 3" class="px-2 py-0.5 text-xs font-medium rounded-full bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 border border-white dark:border-gray-800">
                    +{{ agent.skills.length - 3 }}
                  </span>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900 dark:text-white">{{ agent.events_count }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400">{{ agent.events_today }} today</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                {{ formatDate(agent.last_run_at) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right">
                <div class="flex items-center justify-end space-x-2">
                  <button v-if="agent.status !== 'running'" @click="startAgent(agent)" class="p-1.5 text-gray-400 hover:text-green-600 dark:hover:text-green-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700" title="Start">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                  </button>
                  <button v-if="agent.status === 'running'" @click="stopAgent(agent)" class="p-1.5 text-gray-400 hover:text-red-600 dark:hover:text-red-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700" title="Stop">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z" />
                    </svg>
                  </button>
                  <button @click="viewHistory(agent)" class="p-1.5 text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700" title="View History">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                  </button>
                  <button @click="editAgent(agent)" class="p-1.5 text-gray-400 hover:text-green-600 dark:hover:text-green-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700" title="Edit">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </button>
                  <button @click="confirmDelete(agent)" class="p-1.5 text-gray-400 hover:text-red-600 dark:hover:text-red-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700" title="Delete">
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
          Showing {{ (currentPage - 1) * perPage + 1 }} to {{ Math.min(currentPage * perPage, filteredAgents.length) }} of {{ filteredAgents.length }} agents
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

    <!-- Create/Edit Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto" @click.self="closeModal">
      <div class="flex items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-black/50" @click="closeModal"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-2xl w-full p-6 max-h-[90vh] overflow-y-auto">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
              {{ editingAgent ? 'Edit Agent' : 'Create Agent' }}
            </h3>
            <button @click="closeModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <form @submit.prevent="saveAgent" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Agent Name *</label>
                <input v-model="form.name" type="text" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type *</label>
                <select v-model="form.type" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                  <option value="scraper">Scraper</option>
                  <option value="monitor">Monitor</option>
                  <option value="analyzer">Analyzer</option>
                  <option value="aggregator">Aggregator</option>
                </select>
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
              <textarea v-model="form.description" rows="2" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Schedule (Cron)</label>
                <input v-model="form.schedule" type="text" placeholder="*/15 * * * *" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Leave empty for manual execution</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Timeout (seconds)</label>
                <input v-model.number="form.timeout" type="number" min="30" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
              </div>
            </div>

            <!-- Skills Selection -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Assigned Skills</label>
              <div class="border border-gray-200 dark:border-gray-700 rounded-lg max-h-48 overflow-y-auto">
                <div class="p-2 space-y-2">
                  <label v-for="skill in availableSkills" :key="skill.id" class="flex items-center p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer">
                    <input type="checkbox" :value="skill.id" v-model="form.skill_ids" class="w-4 h-4 text-blue-600 bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded focus:ring-blue-500" />
                    <div class="ml-3">
                      <span class="text-sm font-medium text-gray-900 dark:text-white">{{ skill.name }}</span>
                      <span class="text-xs text-gray-500 dark:text-gray-400 ml-2">{{ skill.category }}</span>
                    </div>
                  </label>
                </div>
              </div>
            </div>

            <!-- Configuration -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Configuration (JSON)</label>
              <textarea v-model="form.config" rows="4" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white font-mono text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder='{"sources": [], "filters": {}}'></textarea>
            </div>

            <div class="flex items-center">
              <input v-model="form.is_enabled" type="checkbox" id="is_enabled" class="w-4 h-4 text-blue-600 bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded focus:ring-blue-500" />
              <label for="is_enabled" class="ml-2 text-sm text-gray-600 dark:text-gray-400">Agent is enabled</label>
            </div>

            <div v-if="formError" class="p-3 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 rounded-lg text-sm">
              {{ formError }}
            </div>

            <div class="flex justify-end space-x-3 pt-4">
              <button type="button" @click="closeModal" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                Cancel
              </button>
              <button type="submit" :disabled="saving" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50 transition-colors">
                {{ saving ? 'Saving...' : (editingAgent ? 'Update Agent' : 'Create Agent') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- History Modal -->
    <div v-if="showHistoryModal" class="fixed inset-0 z-50 overflow-y-auto" @click.self="showHistoryModal = false">
      <div class="flex items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-black/50" @click="showHistoryModal = false"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-3xl w-full p-6 max-h-[90vh] overflow-y-auto">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Execution History: {{ viewingAgent?.name }}</h3>
            <button @click="showHistoryModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <div class="space-y-4">
            <div v-for="execution in executionHistory" :key="execution.id" class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
              <div class="flex items-center justify-between mb-2">
                <div class="flex items-center space-x-2">
                  <span :class="getExecutionStatusClass(execution.status)" class="w-2 h-2 rounded-full"></span>
                  <span class="text-sm font-medium text-gray-900 dark:text-white capitalize">{{ execution.status }}</span>
                </div>
                <span class="text-sm text-gray-500 dark:text-gray-400">{{ formatDate(execution.started_at) }}</span>
              </div>
              <div class="grid grid-cols-3 gap-4 text-sm">
                <div>
                  <p class="text-gray-500 dark:text-gray-400">Duration</p>
                  <p class="font-medium text-gray-900 dark:text-white">{{ execution.duration }}s</p>
                </div>
                <div>
                  <p class="text-gray-500 dark:text-gray-400">Events Created</p>
                  <p class="font-medium text-gray-900 dark:text-white">{{ execution.events_created }}</p>
                </div>
                <div>
                  <p class="text-gray-500 dark:text-gray-400">Errors</p>
                  <p class="font-medium text-gray-900 dark:text-white">{{ execution.errors_count }}</p>
                </div>
              </div>
              <div v-if="execution.error_message" class="mt-2 p-2 bg-red-50 dark:bg-red-900/30 rounded text-sm text-red-700 dark:text-red-400">
                {{ execution.error_message }}
              </div>
            </div>

            <div v-if="executionHistory.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
              No execution history available
            </div>
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
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Delete Agent</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
              Are you sure you want to delete <strong>{{ deletingAgent?.name }}</strong>? This will stop all running processes and remove execution history.
            </p>
            <div class="flex justify-center space-x-3">
              <button @click="showDeleteModal = false" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                Cancel
              </button>
              <button @click="deleteAgent" :disabled="deleting" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 disabled:opacity-50 transition-colors">
                {{ deleting ? 'Deleting...' : 'Delete Agent' }}
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

interface Skill {
  id: number;
  name: string;
  category: string;
}

interface Execution {
  id: number;
  status: 'completed' | 'failed' | 'running';
  started_at: string;
  ended_at?: string;
  duration: number;
  events_created: number;
  errors_count: number;
  error_message?: string;
}

interface Agent {
  id: number;
  name: string;
  description?: string;
  type: 'scraper' | 'monitor' | 'analyzer' | 'aggregator';
  status: 'running' | 'stopped' | 'error' | 'paused';
  schedule?: string;
  timeout: number;
  config?: string;
  is_enabled: boolean;
  skills: Skill[];
  events_count: number;
  events_today: number;
  last_run_at?: string;
  created_at: string;
  updated_at: string;
}

// State
const agents = ref<Agent[]>([]);
const availableSkills = ref<Skill[]>([]);
const executionHistory = ref<Execution[]>([]);
const loading = ref(false);
const saving = ref(false);
const deleting = ref(false);
const currentPage = ref(1);
const perPage = ref(10);

// Modals
const showModal = ref(false);
const showHistoryModal = ref(false);
const showDeleteModal = ref(false);
const editingAgent = ref<Agent | null>(null);
const viewingAgent = ref<Agent | null>(null);
const deletingAgent = ref<Agent | null>(null);
const formError = ref('');

// Form
const form = reactive({
  name: '',
  description: '',
  type: 'scraper' as Agent['type'],
  schedule: '',
  timeout: 300,
  config: '',
  is_enabled: true,
  skill_ids: [] as number[]
});

// Filters
const filters = reactive({
  search: '',
  type: '',
  status: ''
});

// Computed
const runningCount = computed(() => agents.value.filter(a => a.status === 'running').length);
const stoppedCount = computed(() => agents.value.filter(a => a.status === 'stopped').length);
const errorCount = computed(() => agents.value.filter(a => a.status === 'error').length);
const eventsToday = computed(() => agents.value.reduce((sum, a) => sum + a.events_today, 0));

const filteredAgents = computed(() => {
  return agents.value.filter(agent => {
    const matchesSearch = !filters.search ||
      agent.name.toLowerCase().includes(filters.search.toLowerCase()) ||
      agent.description?.toLowerCase().includes(filters.search.toLowerCase());
    const matchesType = !filters.type || agent.type === filters.type;
    const matchesStatus = !filters.status || agent.status === filters.status;
    return matchesSearch && matchesType && matchesStatus;
  });
});

const totalPages = computed(() => Math.ceil(filteredAgents.value.length / perPage.value) || 1);

const paginatedAgents = computed(() => {
  const start = (currentPage.value - 1) * perPage.value;
  return filteredAgents.value.slice(start, start + perPage.value);
});

// Methods
const fetchAgents = async () => {
  loading.value = true;
  try {
    availableSkills.value = [
      { id: 1, name: 'Twitter Scraper', category: 'Social Media' },
      { id: 2, name: 'Telegram Monitor', category: 'Social Media' },
      { id: 3, name: 'News Aggregator', category: 'News' },
      { id: 4, name: 'Geolocation', category: 'Analysis' },
      { id: 5, name: 'Image Analysis', category: 'Analysis' },
      { id: 6, name: 'Entity Extraction', category: 'NLP' },
      { id: 7, name: 'Satellite Monitor', category: 'Imagery' },
      { id: 8, name: 'Flight Tracker', category: 'Transport' },
    ];

    agents.value = [
      { id: 1, name: 'Twitter OSINT Bot', description: 'Monitors Twitter for conflict-related posts', type: 'scraper', status: 'running', schedule: '*/5 * * * *', timeout: 300, is_enabled: true, skills: availableSkills.value.slice(0, 3), events_count: 1542, events_today: 23, last_run_at: '2024-01-20T14:55:00Z', created_at: '2024-01-01', updated_at: '2024-01-01' },
      { id: 2, name: 'Telegram Channel Monitor', description: 'Tracks key Telegram channels', type: 'monitor', status: 'running', schedule: '*/10 * * * *', timeout: 600, is_enabled: true, skills: availableSkills.value.slice(1, 4), events_count: 892, events_today: 15, last_run_at: '2024-01-20T14:50:00Z', created_at: '2024-01-01', updated_at: '2024-01-01' },
      { id: 3, name: 'News Crawler', description: 'Crawls international news sources', type: 'scraper', status: 'stopped', schedule: '0 */2 * * *', timeout: 1800, is_enabled: true, skills: availableSkills.value.slice(2, 5), events_count: 456, events_today: 0, last_run_at: '2024-01-20T12:00:00Z', created_at: '2024-01-01', updated_at: '2024-01-01' },
      { id: 4, name: 'Image Analyzer', description: 'Analyzes images for equipment identification', type: 'analyzer', status: 'running', schedule: '', timeout: 120, is_enabled: true, skills: availableSkills.value.slice(3, 6), events_count: 234, events_today: 8, last_run_at: '2024-01-20T14:52:00Z', created_at: '2024-01-01', updated_at: '2024-01-01' },
      { id: 5, name: 'Satellite Imagery Bot', description: 'Monitors satellite imagery for changes', type: 'monitor', status: 'error', schedule: '0 */6 * * *', timeout: 3600, is_enabled: true, skills: availableSkills.value.slice(5, 8), events_count: 78, events_today: 0, last_run_at: '2024-01-20T06:00:00Z', created_at: '2024-01-01', updated_at: '2024-01-01' },
      { id: 6, name: 'Event Aggregator', description: 'Combines and deduplicates events', type: 'aggregator', status: 'paused', schedule: '0 * * * *', timeout: 600, is_enabled: false, skills: availableSkills.value.slice(4, 7), events_count: 0, events_today: 0, last_run_at: '2024-01-19T18:00:00Z', created_at: '2024-01-01', updated_at: '2024-01-01' },
    ];
  } catch (error) {
    console.error('Failed to fetch agents:', error);
  } finally {
    loading.value = false;
  }
};

const openCreateModal = () => {
  editingAgent.value = null;
  Object.assign(form, {
    name: '',
    description: '',
    type: 'scraper',
    schedule: '',
    timeout: 300,
    config: '',
    is_enabled: true,
    skill_ids: []
  });
  formError.value = '';
  showModal.value = true;
};

const editAgent = (agent: Agent) => {
  editingAgent.value = agent;
  Object.assign(form, {
    name: agent.name,
    description: agent.description || '',
    type: agent.type,
    schedule: agent.schedule || '',
    timeout: agent.timeout,
    config: agent.config || '',
    is_enabled: agent.is_enabled,
    skill_ids: agent.skills.map(s => s.id)
  });
  formError.value = '';
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  editingAgent.value = null;
};

const saveAgent = async () => {
  saving.value = true;
  formError.value = '';
  try {
    const selectedSkills = availableSkills.value.filter(s => form.skill_ids.includes(s.id));
    if (editingAgent.value) {
      const index = agents.value.findIndex(a => a.id === editingAgent.value!.id);
      if (index !== -1) {
        agents.value[index] = {
          ...agents.value[index],
          ...form,
          skills: selectedSkills,
          updated_at: new Date().toISOString()
        };
      }
    } else {
      const newAgent: Agent = {
        id: agents.value.length + 1,
        ...form,
        status: 'stopped',
        skills: selectedSkills,
        events_count: 0,
        events_today: 0,
        created_at: new Date().toISOString(),
        updated_at: new Date().toISOString()
      };
      agents.value.unshift(newAgent);
    }
    closeModal();
  } catch (error: any) {
    formError.value = error?.message || 'Failed to save agent';
  } finally {
    saving.value = false;
  }
};

const startAgent = (agent: Agent) => {
  agent.status = 'running';
  agent.last_run_at = new Date().toISOString();
};

const stopAgent = (agent: Agent) => {
  agent.status = 'stopped';
};

const startAllAgents = () => {
  agents.value.forEach(agent => {
    if (agent.is_enabled && agent.status !== 'running') {
      agent.status = 'running';
      agent.last_run_at = new Date().toISOString();
    }
  });
};

const stopAllAgents = () => {
  agents.value.forEach(agent => {
    if (agent.status === 'running') {
      agent.status = 'stopped';
    }
  });
};

const viewHistory = (agent: Agent) => {
  viewingAgent.value = agent;
  executionHistory.value = [
    { id: 1, status: 'completed', started_at: '2024-01-20T14:55:00Z', duration: 45, events_created: 5, errors_count: 0 },
    { id: 2, status: 'completed', started_at: '2024-01-20T14:50:00Z', duration: 52, events_created: 3, errors_count: 0 },
    { id: 3, status: 'failed', started_at: '2024-01-20T14:45:00Z', duration: 12, events_created: 0, errors_count: 1, error_message: 'API rate limit exceeded' },
    { id: 4, status: 'completed', started_at: '2024-01-20T14:40:00Z', duration: 48, events_created: 7, errors_count: 0 },
    { id: 5, status: 'completed', started_at: '2024-01-20T14:35:00Z', duration: 41, events_created: 4, errors_count: 0 },
  ];
  showHistoryModal.value = true;
};

const confirmDelete = (agent: Agent) => {
  deletingAgent.value = agent;
  showDeleteModal.value = true;
};

const deleteAgent = async () => {
  if (!deletingAgent.value) return;
  deleting.value = true;
  try {
    agents.value = agents.value.filter(a => a.id !== deletingAgent.value!.id);
    showDeleteModal.value = false;
    deletingAgent.value = null;
  } catch (error) {
    console.error('Failed to delete agent:', error);
  } finally {
    deleting.value = false;
  }
};

const clearFilters = () => {
  filters.search = '';
  filters.type = '';
  filters.status = '';
  currentPage.value = 1;
};

const formatDate = (dateString: string | undefined) => {
  if (!dateString) return '-';
  return new Date(dateString).toLocaleString('en-US', {
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

const getAgentIconClass = (type: string) => {
  const classes: Record<string, string> = {
    scraper: 'bg-blue-500',
    monitor: 'bg-green-500',
    analyzer: 'bg-purple-500',
    aggregator: 'bg-yellow-500'
  };
  return classes[type] || 'bg-gray-500';
};

const getTypeBadgeClass = (type: string) => {
  const classes: Record<string, string> = {
    scraper: 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400',
    monitor: 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400',
    analyzer: 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400',
    aggregator: 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400'
  };
  return classes[type] || 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-400';
};

const getStatusDotClass = (status: string) => {
  const classes: Record<string, string> = {
    running: 'bg-green-500 animate-pulse',
    stopped: 'bg-gray-400',
    error: 'bg-red-500',
    paused: 'bg-yellow-500'
  };
  return classes[status] || 'bg-gray-400';
};

const getStatusTextClass = (status: string) => {
  const classes: Record<string, string> = {
    running: 'text-green-600 dark:text-green-400',
    stopped: 'text-gray-600 dark:text-gray-400',
    error: 'text-red-600 dark:text-red-400',
    paused: 'text-yellow-600 dark:text-yellow-400'
  };
  return classes[status] || 'text-gray-600 dark:text-gray-400';
};

const getExecutionStatusClass = (status: string) => {
  const classes: Record<string, string> = {
    completed: 'bg-green-500',
    failed: 'bg-red-500',
    running: 'bg-blue-500 animate-pulse'
  };
  return classes[status] || 'bg-gray-400';
};

onMounted(() => {
  fetchAgents();
});
</script>
