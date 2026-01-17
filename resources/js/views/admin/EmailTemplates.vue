<template>
  <div class="p-6 bg-gray-50 dark:bg-gray-900 min-h-screen">
    <div class="max-w-7xl mx-auto">
      <!-- Header -->
      <div class="mb-8 flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Email Templates</h1>
          <p class="text-gray-500 dark:text-gray-400 mt-1">Manage email templates and view sending statistics</p>
        </div>
        <div class="flex gap-3">
          <button
            @click="showStats = !showStats"
            class="px-4 py-2 text-gray-600 dark:text-gray-300 bg-white dark:bg-gray-800 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700"
          >
            {{ showStats ? 'Hide Stats' : 'Show Stats' }}
          </button>
          <button
            @click="openCreateModal"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Create Template
          </button>
        </div>
      </div>

      <!-- Statistics Panel -->
      <div v-if="showStats" class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
          <div class="text-2xl font-bold text-blue-600">{{ stats.total_templates }}</div>
          <div class="text-sm text-gray-500 dark:text-gray-400">Total Templates</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
          <div class="text-2xl font-bold text-green-600">{{ stats.emails_sent_today }}</div>
          <div class="text-sm text-gray-500 dark:text-gray-400">Sent Today</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
          <div class="text-2xl font-bold text-purple-600">{{ stats.emails_sent_week }}</div>
          <div class="text-sm text-gray-500 dark:text-gray-400">Sent This Week</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
          <div class="text-2xl font-bold text-red-600">{{ stats.failed_emails }}</div>
          <div class="text-sm text-gray-500 dark:text-gray-400">Failed (7 days)</div>
        </div>
      </div>

      <!-- Tabs -->
      <div class="flex flex-wrap gap-2 mb-6">
        <button
          v-for="tab in tabs"
          :key="tab.id"
          @click="activeTab = tab.id"
          :class="[
            'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
            activeTab === tab.id
              ? 'bg-blue-600 text-white'
              : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
          ]"
        >
          {{ tab.label }}
        </button>
      </div>

      <!-- Templates List -->
      <div v-if="activeTab === 'templates'" class="space-y-4">
        <!-- Filters -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 flex flex-wrap gap-4">
          <div class="flex-1 min-w-[200px]">
            <input
              v-model="filters.search"
              type="text"
              placeholder="Search templates..."
              class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
              @input="debounceSearch"
            />
          </div>
          <select
            v-model="filters.category"
            class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
            @change="loadTemplates"
          >
            <option value="">All Categories</option>
            <option v-for="(label, key) in categories" :key="key" :value="key">{{ label }}</option>
          </select>
          <select
            v-model="filters.status"
            class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
            @change="loadTemplates"
          >
            <option value="">All Statuses</option>
            <option value="true">Active</option>
            <option value="false">Inactive</option>
          </select>
        </div>

        <!-- Templates Grid -->
        <div class="grid gap-4">
          <div
            v-for="template in templates"
            :key="template.uuid"
            class="bg-white dark:bg-gray-800 rounded-lg shadow p-6"
          >
            <div class="flex items-start justify-between">
              <div class="flex-1">
                <div class="flex items-center gap-3">
                  <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ template.name }}</h3>
                  <span
                    :class="[
                      'px-2 py-1 text-xs rounded-full',
                      template.is_active
                        ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                        : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
                    ]"
                  >
                    {{ template.is_active ? 'Active' : 'Inactive' }}
                  </span>
                  <span
                    v-if="template.is_system"
                    class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200"
                  >
                    System
                  </span>
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ template.description }}</p>
                <div class="flex items-center gap-4 mt-3 text-sm text-gray-500 dark:text-gray-400">
                  <span class="flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                    </svg>
                    {{ categories[template.category] }}
                  </span>
                  <span class="flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                    </svg>
                    {{ template.slug }}
                  </span>
                </div>
              </div>
              <div class="flex items-center gap-2">
                <button
                  @click="previewTemplate(template)"
                  class="p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg"
                  title="Preview"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                  </svg>
                </button>
                <button
                  @click="editTemplate(template)"
                  class="p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg"
                  title="Edit"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                  </svg>
                </button>
                <button
                  @click="duplicateTemplate(template)"
                  class="p-2 text-gray-500 hover:text-purple-600 hover:bg-purple-50 dark:hover:bg-purple-900/30 rounded-lg"
                  title="Duplicate"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                  </svg>
                </button>
                <button
                  v-if="!template.is_system"
                  @click="deleteTemplate(template)"
                  class="p-2 text-gray-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg"
                  title="Delete"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                  </svg>
                </button>
              </div>
            </div>
          </div>

          <div v-if="templates.length === 0" class="bg-white dark:bg-gray-800 rounded-lg shadow p-12 text-center">
            <svg class="w-12 h-12 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
            </svg>
            <p class="mt-4 text-gray-500 dark:text-gray-400">No email templates found</p>
          </div>
        </div>
      </div>

      <!-- Logs Tab -->
      <div v-if="activeTab === 'logs'" class="bg-white dark:bg-gray-800 rounded-lg shadow">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Email Logs</h2>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 dark:bg-gray-700">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Recipient</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Template</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Subject</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Sent</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
              <tr v-for="log in logs" :key="log.uuid" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ log.email }}</td>
                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ log.template_slug }}</td>
                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ log.subject }}</td>
                <td class="px-4 py-3">
                  <span :class="getStatusClass(log.status)">{{ log.status }}</span>
                </td>
                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ formatDate(log.created_at) }}</td>
              </tr>
              <tr v-if="logs.length === 0">
                <td colspan="5" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">No logs found</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Unsubscribes Tab -->
      <div v-if="activeTab === 'unsubscribes'" class="bg-white dark:bg-gray-800 rounded-lg shadow">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Unsubscribes</h2>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 dark:bg-gray-700">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Email</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Type</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Reason</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Date</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
              <tr v-for="unsub in unsubscribes" :key="unsub.uuid" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ unsub.email }}</td>
                <td class="px-4 py-3">
                  <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                    {{ unsubscribeTypes[unsub.type] || unsub.type }}
                  </span>
                </td>
                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ unsub.reason || '-' }}</td>
                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ formatDate(unsub.unsubscribed_at) }}</td>
              </tr>
              <tr v-if="unsubscribes.length === 0">
                <td colspan="4" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">No unsubscribes found</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Edit/Create Modal -->
      <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
          <div class="fixed inset-0 bg-black bg-opacity-50" @click="closeModal"></div>
          <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
            <div class="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 p-4 flex items-center justify-between">
              <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                {{ editingTemplate ? 'Edit Template' : 'Create Template' }}
              </h2>
              <button @click="closeModal" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
              </button>
            </div>

            <div class="p-6 space-y-6">
              <!-- Basic Info -->
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name *</label>
                  <input
                    v-model="form.name"
                    type="text"
                    class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                    placeholder="Welcome Email"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Slug *</label>
                  <input
                    v-model="form.slug"
                    type="text"
                    class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                    placeholder="welcome-email"
                    :disabled="editingTemplate?.is_system"
                  />
                </div>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Category *</label>
                  <select
                    v-model="form.category"
                    class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                  >
                    <option v-for="(label, key) in categories" :key="key" :value="key">{{ label }}</option>
                  </select>
                </div>
                <div class="flex items-center">
                  <label class="flex items-center gap-2 cursor-pointer mt-6">
                    <input type="checkbox" v-model="form.is_active" class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                    <span class="text-sm text-gray-700 dark:text-gray-300">Active</span>
                  </label>
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                <input
                  v-model="form.description"
                  type="text"
                  class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                  placeholder="Sent when a new user registers"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Subject *</label>
                <input
                  v-model="form.subject"
                  type="text"
                  class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                  placeholder="Welcome to {{ app_name }}"
                />
              </div>

              <!-- Template Editor Tabs -->
              <div>
                <div class="flex gap-2 mb-3">
                  <button
                    @click="editorTab = 'html'"
                    :class="[
                      'px-3 py-1 text-sm rounded',
                      editorTab === 'html'
                        ? 'bg-blue-600 text-white'
                        : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300'
                    ]"
                  >
                    HTML
                  </button>
                  <button
                    @click="editorTab = 'text'"
                    :class="[
                      'px-3 py-1 text-sm rounded',
                      editorTab === 'text'
                        ? 'bg-blue-600 text-white'
                        : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300'
                    ]"
                  >
                    Plain Text
                  </button>
                  <button
                    @click="editorTab = 'variables'"
                    :class="[
                      'px-3 py-1 text-sm rounded',
                      editorTab === 'variables'
                        ? 'bg-blue-600 text-white'
                        : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300'
                    ]"
                  >
                    Variables
                  </button>
                </div>

                <div v-if="editorTab === 'html'">
                  <textarea
                    v-model="form.html_content"
                    rows="15"
                    class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white font-mono text-sm"
                    placeholder="HTML email content..."
                  ></textarea>
                </div>

                <div v-if="editorTab === 'text'">
                  <textarea
                    v-model="form.text_content"
                    rows="15"
                    class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white font-mono text-sm"
                    placeholder="Plain text email content..."
                  ></textarea>
                </div>

                <div v-if="editorTab === 'variables'" class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
                  <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Available Variables</h4>
                  <div class="grid grid-cols-2 gap-2 text-sm">
                    <div v-for="(desc, key) in availableVariables" :key="key" class="flex gap-2">
                      <code class="px-2 py-1 bg-gray-200 dark:bg-gray-700 rounded text-blue-600 dark:text-blue-400">{{ '{{ ' + key + ' }}' }}</code>
                      <span class="text-gray-600 dark:text-gray-400">{{ desc }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="sticky bottom-0 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 p-4 flex justify-between">
              <div class="flex gap-2">
                <button
                  @click="previewForm"
                  type="button"
                  class="px-4 py-2 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg"
                >
                  Preview
                </button>
                <button
                  v-if="editingTemplate"
                  @click="sendTestEmail"
                  type="button"
                  class="px-4 py-2 text-purple-600 hover:bg-purple-50 dark:hover:bg-purple-900/30 rounded-lg"
                >
                  Send Test
                </button>
              </div>
              <div class="flex gap-2">
                <button
                  @click="closeModal"
                  type="button"
                  class="px-4 py-2 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg"
                >
                  Cancel
                </button>
                <button
                  @click="saveTemplate"
                  type="button"
                  class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                  :disabled="saving"
                >
                  {{ saving ? 'Saving...' : 'Save Template' }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Preview Modal -->
      <div v-if="showPreview" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
          <div class="fixed inset-0 bg-black bg-opacity-50" @click="showPreview = false"></div>
          <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-3xl w-full max-h-[90vh] overflow-y-auto">
            <div class="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 p-4 flex items-center justify-between">
              <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Email Preview</h2>
              <button @click="showPreview = false" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
              </button>
            </div>
            <div class="p-4">
              <div class="mb-4 p-3 bg-gray-100 dark:bg-gray-700 rounded">
                <strong>Subject:</strong> {{ previewData.subject }}
              </div>
              <div class="border rounded-lg overflow-hidden">
                <iframe
                  :srcdoc="previewData.html"
                  class="w-full h-[600px] bg-white"
                  sandbox="allow-same-origin"
                ></iframe>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue';
import axios from 'axios';

interface EmailTemplate {
  uuid: string;
  slug: string;
  name: string;
  description: string | null;
  subject: string;
  html_content: string;
  text_content: string;
  category: string;
  variables: string[] | null;
  is_active: boolean;
  is_system: boolean;
  created_at: string;
  updated_at: string;
}

interface EmailLog {
  uuid: string;
  email: string;
  template_slug: string;
  subject: string;
  status: string;
  created_at: string;
}

interface Unsubscribe {
  uuid: string;
  email: string;
  type: string;
  reason: string | null;
  unsubscribed_at: string;
}

const activeTab = ref('templates');
const showStats = ref(false);
const showModal = ref(false);
const showPreview = ref(false);
const saving = ref(false);
const editorTab = ref('html');

const tabs = [
  { id: 'templates', label: 'Templates' },
  { id: 'logs', label: 'Email Logs' },
  { id: 'unsubscribes', label: 'Unsubscribes' },
];

const templates = ref<EmailTemplate[]>([]);
const logs = ref<EmailLog[]>([]);
const unsubscribes = ref<Unsubscribe[]>([]);
const editingTemplate = ref<EmailTemplate | null>(null);

const categories = ref<Record<string, string>>({
  authentication: 'Authentication & Security',
  notification: 'Notifications',
  marketing: 'Marketing & Promotional',
  transactional: 'Transactional',
  alert: 'Alerts',
  digest: 'Digest & Summaries',
});

const unsubscribeTypes = ref<Record<string, string>>({
  all: 'All Emails',
  marketing: 'Marketing',
  notifications: 'Notifications',
  alerts: 'Alerts',
  digest: 'Weekly Digest',
});

const stats = reactive({
  total_templates: 0,
  active_templates: 0,
  emails_sent_today: 0,
  emails_sent_week: 0,
  emails_sent_month: 0,
  failed_emails: 0,
  unsubscribes: 0,
});

const filters = reactive({
  search: '',
  category: '',
  status: '',
});

const form = reactive({
  name: '',
  slug: '',
  description: '',
  subject: '',
  html_content: '',
  text_content: '',
  category: 'notification',
  is_active: true,
});

const availableVariables = ref<Record<string, string>>({
  app_name: 'Application name',
  app_url: 'Application URL',
  year: 'Current year',
  user_name: 'User full name',
  user_email: 'User email address',
  user_first_name: 'User first name',
  unsubscribe_url: 'Unsubscribe link',
  preferences_url: 'Email preferences link',
});

const previewData = reactive({
  subject: '',
  html: '',
  text: '',
});

let searchTimeout: number | null = null;

onMounted(() => {
  loadTemplates();
  loadStats();
});

async function loadTemplates() {
  try {
    const params: Record<string, string> = {};
    if (filters.search) params.search = filters.search;
    if (filters.category) params.category = filters.category;
    if (filters.status) params.is_active = filters.status;

    const response = await axios.get('/api/admin/email-templates', { params });
    templates.value = response.data.data;
    if (response.data.meta?.categories) {
      categories.value = response.data.meta.categories;
    }
  } catch (error) {
    console.error('Failed to load templates:', error);
  }
}

async function loadStats() {
  try {
    const response = await axios.get('/api/admin/email-templates/stats');
    Object.assign(stats, response.data);
  } catch (error) {
    console.error('Failed to load stats:', error);
  }
}

async function loadLogs() {
  try {
    const response = await axios.get('/api/admin/email-templates/logs');
    logs.value = response.data.data;
  } catch (error) {
    console.error('Failed to load logs:', error);
  }
}

async function loadUnsubscribes() {
  try {
    const response = await axios.get('/api/admin/email-templates/unsubscribes');
    unsubscribes.value = response.data.data.data;
    if (response.data.meta?.types) {
      unsubscribeTypes.value = response.data.meta.types;
    }
  } catch (error) {
    console.error('Failed to load unsubscribes:', error);
  }
}

function debounceSearch() {
  if (searchTimeout) clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    loadTemplates();
  }, 300);
}

function openCreateModal() {
  editingTemplate.value = null;
  Object.assign(form, {
    name: '',
    slug: '',
    description: '',
    subject: '',
    html_content: '',
    text_content: '',
    category: 'notification',
    is_active: true,
  });
  editorTab.value = 'html';
  showModal.value = true;
}

function editTemplate(template: EmailTemplate) {
  editingTemplate.value = template;
  Object.assign(form, {
    name: template.name,
    slug: template.slug,
    description: template.description || '',
    subject: template.subject,
    html_content: template.html_content,
    text_content: template.text_content,
    category: template.category,
    is_active: template.is_active,
  });
  editorTab.value = 'html';
  loadVariablesForTemplate(template.slug);
  showModal.value = true;
}

async function loadVariablesForTemplate(slug: string) {
  try {
    const response = await axios.get(`/api/admin/email-templates/${editingTemplate.value?.uuid}`);
    if (response.data.meta?.available_variables) {
      availableVariables.value = response.data.meta.available_variables;
    }
  } catch (error) {
    console.error('Failed to load variables:', error);
  }
}

async function saveTemplate() {
  saving.value = true;
  try {
    if (editingTemplate.value) {
      await axios.put(`/api/admin/email-templates/${editingTemplate.value.uuid}`, form);
    } else {
      await axios.post('/api/admin/email-templates', form);
    }
    closeModal();
    loadTemplates();
  } catch (error: any) {
    alert(error.response?.data?.message || 'Failed to save template');
  } finally {
    saving.value = false;
  }
}

async function duplicateTemplate(template: EmailTemplate) {
  try {
    await axios.post(`/api/admin/email-templates/${template.uuid}/duplicate`);
    loadTemplates();
  } catch (error) {
    console.error('Failed to duplicate template:', error);
  }
}

async function deleteTemplate(template: EmailTemplate) {
  if (!confirm(`Are you sure you want to delete "${template.name}"?`)) return;
  try {
    await axios.delete(`/api/admin/email-templates/${template.uuid}`);
    loadTemplates();
  } catch (error: any) {
    alert(error.response?.data?.message || 'Failed to delete template');
  }
}

async function previewTemplate(template: EmailTemplate) {
  try {
    const response = await axios.get(`/api/admin/email-templates/${template.uuid}/preview`);
    Object.assign(previewData, response.data);
    showPreview.value = true;
  } catch (error) {
    console.error('Failed to preview template:', error);
  }
}

async function previewForm() {
  Object.assign(previewData, {
    subject: form.subject
      .replace(/\{\{\s*app_name\s*\}\}/g, 'OsintWeb')
      .replace(/\{\{\s*user_first_name\s*\}\}/g, 'John'),
    html: form.html_content
      .replace(/\{\{\s*app_name\s*\}\}/g, 'OsintWeb')
      .replace(/\{\{\s*app_url\s*\}\}/g, window.location.origin)
      .replace(/\{\{\s*year\s*\}\}/g, new Date().getFullYear().toString())
      .replace(/\{\{\s*user_name\s*\}\}/g, 'John Doe')
      .replace(/\{\{\s*user_email\s*\}\}/g, 'john@example.com')
      .replace(/\{\{\s*user_first_name\s*\}\}/g, 'John')
      .replace(/\{\{\s*unsubscribe_url\s*\}\}/g, '#')
      .replace(/\{\{\s*preferences_url\s*\}\}/g, '#'),
    text: form.text_content,
  });
  showPreview.value = true;
}

async function sendTestEmail() {
  const email = prompt('Enter email address for test:');
  if (!email) return;

  try {
    await axios.post(`/api/admin/email-templates/${editingTemplate.value?.uuid}/test`, { email });
    alert('Test email sent successfully!');
  } catch (error: any) {
    alert(error.response?.data?.message || 'Failed to send test email');
  }
}

function closeModal() {
  showModal.value = false;
  editingTemplate.value = null;
}

function getStatusClass(status: string): string {
  const classes: Record<string, string> = {
    pending: 'px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
    sent: 'px-2 py-1 text-xs rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
    delivered: 'px-2 py-1 text-xs rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
    opened: 'px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
    clicked: 'px-2 py-1 text-xs rounded-full bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
    bounced: 'px-2 py-1 text-xs rounded-full bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
    failed: 'px-2 py-1 text-xs rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
  };
  return classes[status] || 'px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
}

function formatDate(dateStr: string): string {
  if (!dateStr) return '-';
  const date = new Date(dateStr);
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
}

// Watch for tab changes to load data
import { watch } from 'vue';

watch(activeTab, (newTab) => {
  if (newTab === 'logs') loadLogs();
  if (newTab === 'unsubscribes') loadUnsubscribes();
});
</script>
