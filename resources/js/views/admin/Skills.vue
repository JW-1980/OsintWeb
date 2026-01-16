<template>
  <div class="mt-6 space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h2 class="text-xl font-bold text-gray-900 dark:text-white">OSINT Skills Management</h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Configure skills and triggers for automated OSINT collection</p>
      </div>
      <button @click="openCreateModal" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Add Skill
      </button>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">Total Skills</p>
        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ skills.length }}</p>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">Active</p>
        <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ activeCount }}</p>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">Categories</p>
        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ categoriesCount }}</p>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">Total Triggers</p>
        <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ totalTriggers }}</p>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">Executions Today</p>
        <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ executionsToday }}</p>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search</label>
          <input v-model="filters.search" type="text" placeholder="Search skills..." class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Category</label>
          <select v-model="filters.category" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            <option value="">All Categories</option>
            <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
          <select v-model="filters.status" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>
        <div class="flex items-end">
          <button @click="clearFilters" class="w-full px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
            Clear Filters
          </button>
        </div>
      </div>
    </div>

    <!-- Skills by Category -->
    <div class="space-y-6">
      <div v-for="category in filteredCategories" :key="category.name">
        <div class="flex items-center justify-between mb-3">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
            <span :class="getCategoryIconClass(category.name)" class="w-8 h-8 rounded-lg flex items-center justify-center text-white mr-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
              </svg>
            </span>
            {{ category.name }}
          </h3>
          <span class="text-sm text-gray-500 dark:text-gray-400">{{ category.skills.length }} skills</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div v-for="skill in category.skills" :key="skill.id" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 hover:shadow-md transition-shadow">
            <div class="flex items-start justify-between mb-3">
              <div class="flex items-center">
                <div :class="getCategoryIconClass(skill.category)" class="w-10 h-10 rounded-lg flex items-center justify-center text-white mr-3">
                  <span class="text-lg">{{ skill.icon }}</span>
                </div>
                <div>
                  <h4 class="font-semibold text-gray-900 dark:text-white">{{ skill.name }}</h4>
                  <p class="text-xs text-gray-500 dark:text-gray-400">{{ skill.slug }}</p>
                </div>
              </div>
              <span :class="skill.is_active ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-400'" class="px-2 py-0.5 text-xs font-medium rounded-full">
                {{ skill.is_active ? 'Active' : 'Inactive' }}
              </span>
            </div>

            <p class="text-sm text-gray-600 dark:text-gray-400 mb-3 line-clamp-2">{{ skill.description }}</p>

            <!-- Triggers -->
            <div class="mb-3">
              <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Triggers ({{ skill.triggers.length }})</p>
              <div class="flex flex-wrap gap-1">
                <span v-for="(trigger, index) in skill.triggers.slice(0, 4)" :key="index" class="px-2 py-0.5 text-xs font-medium rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400">
                  {{ trigger }}
                </span>
                <span v-if="skill.triggers.length > 4" class="px-2 py-0.5 text-xs font-medium rounded-full bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-400">
                  +{{ skill.triggers.length - 4 }}
                </span>
              </div>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-2 gap-2 text-sm mb-3">
              <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-2">
                <p class="text-xs text-gray-500 dark:text-gray-400">Agents</p>
                <p class="font-semibold text-gray-900 dark:text-white">{{ skill.agents_count }}</p>
              </div>
              <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-2">
                <p class="text-xs text-gray-500 dark:text-gray-400">Executions</p>
                <p class="font-semibold text-gray-900 dark:text-white">{{ skill.executions_count }}</p>
              </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end space-x-2 pt-3 border-t border-gray-200 dark:border-gray-700">
              <button @click="viewSkill(skill)" class="p-1.5 text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700" title="View">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
              </button>
              <button @click="editSkill(skill)" class="p-1.5 text-gray-400 hover:text-green-600 dark:hover:text-green-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700" title="Edit">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
              </button>
              <button @click="confirmDelete(skill)" class="p-1.5 text-gray-400 hover:text-red-600 dark:hover:text-red-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700" title="Delete">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-if="filteredCategories.length === 0" class="text-center py-12 text-gray-500 dark:text-gray-400">
      <p>No skills found</p>
      <button @click="clearFilters" class="mt-2 text-blue-600 dark:text-blue-400 hover:underline">Clear filters</button>
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto" @click.self="closeModal">
      <div class="flex items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-black/50" @click="closeModal"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-2xl w-full p-6 max-h-[90vh] overflow-y-auto">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
              {{ editingSkill ? 'Edit Skill' : 'Create Skill' }}
            </h3>
            <button @click="closeModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <form @submit.prevent="saveSkill" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Skill Name *</label>
                <input v-model="form.name" type="text" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Slug *</label>
                <input v-model="form.slug" type="text" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Category *</label>
                <select v-model="form.category" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                  <option v-for="cat in allCategories" :key="cat" :value="cat">{{ cat }}</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Icon (Emoji)</label>
                <input v-model="form.icon" type="text" maxlength="4" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent text-center text-2xl" />
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
              <textarea v-model="form.description" rows="2" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
            </div>

            <!-- Triggers -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Trigger Keywords</label>
              <div class="flex flex-wrap gap-2 mb-2">
                <span v-for="(trigger, index) in form.triggers" :key="index" class="inline-flex items-center px-3 py-1 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 text-sm">
                  {{ trigger }}
                  <button type="button" @click="removeTrigger(index)" class="ml-2 text-blue-500 hover:text-blue-700">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                </span>
              </div>
              <div class="flex space-x-2">
                <input v-model="newTrigger" type="text" @keydown.enter.prevent="addTrigger" placeholder="Add keyword and press Enter" class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                <button type="button" @click="addTrigger" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Add</button>
              </div>
              <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Keywords that trigger this skill when detected in content</p>
            </div>

            <!-- Configuration -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Configuration (JSON)</label>
              <textarea v-model="form.config" rows="4" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white font-mono text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder='{"api_endpoint": "", "options": {}}'></textarea>
            </div>

            <div class="flex items-center">
              <input v-model="form.is_active" type="checkbox" id="is_active" class="w-4 h-4 text-blue-600 bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded focus:ring-blue-500" />
              <label for="is_active" class="ml-2 text-sm text-gray-600 dark:text-gray-400">Skill is active</label>
            </div>

            <div v-if="formError" class="p-3 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 rounded-lg text-sm">
              {{ formError }}
            </div>

            <div class="flex justify-end space-x-3 pt-4">
              <button type="button" @click="closeModal" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                Cancel
              </button>
              <button type="submit" :disabled="saving" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50 transition-colors">
                {{ saving ? 'Saving...' : (editingSkill ? 'Update Skill' : 'Create Skill') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- View Modal -->
    <div v-if="showViewModal" class="fixed inset-0 z-50 overflow-y-auto" @click.self="showViewModal = false">
      <div class="flex items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-black/50" @click="showViewModal = false"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-2xl w-full p-6 max-h-[90vh] overflow-y-auto">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Skill Details</h3>
            <button @click="showViewModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <div v-if="viewingSkill" class="space-y-6">
            <div class="flex items-center space-x-4">
              <div :class="getCategoryIconClass(viewingSkill.category)" class="w-16 h-16 rounded-xl flex items-center justify-center text-white text-3xl">
                {{ viewingSkill.icon }}
              </div>
              <div>
                <h4 class="text-xl font-semibold text-gray-900 dark:text-white">{{ viewingSkill.name }}</h4>
                <p class="text-gray-500 dark:text-gray-400">{{ viewingSkill.slug }}</p>
                <span :class="getCategoryBadgeClass(viewingSkill.category)" class="px-2.5 py-1 text-xs font-medium rounded-full mt-1 inline-block">
                  {{ viewingSkill.category }}
                </span>
              </div>
            </div>

            <p class="text-gray-600 dark:text-gray-400">{{ viewingSkill.description }}</p>

            <div class="grid grid-cols-3 gap-4">
              <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 text-center">
                <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ viewingSkill.agents_count }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Agents</p>
              </div>
              <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 text-center">
                <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ viewingSkill.triggers.length }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Triggers</p>
              </div>
              <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 text-center">
                <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ viewingSkill.executions_count }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Executions</p>
              </div>
            </div>

            <div>
              <h5 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">Trigger Keywords</h5>
              <div class="flex flex-wrap gap-2">
                <span v-for="(trigger, index) in viewingSkill.triggers" :key="index" class="px-3 py-1 text-sm font-medium rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400">
                  {{ trigger }}
                </span>
              </div>
            </div>

            <div v-if="viewingSkill.assigned_agents && viewingSkill.assigned_agents.length > 0">
              <h5 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">Assigned Agents</h5>
              <div class="space-y-2">
                <div v-for="agent in viewingSkill.assigned_agents" :key="agent.id" class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                  <span class="text-sm text-gray-900 dark:text-white">{{ agent.name }}</span>
                  <span :class="agent.status === 'running' ? 'text-green-600 dark:text-green-400' : 'text-gray-500 dark:text-gray-400'" class="text-xs capitalize">
                    {{ agent.status }}
                  </span>
                </div>
              </div>
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
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Delete Skill</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
              Are you sure you want to delete <strong>{{ deletingSkill?.name }}</strong>? This will remove it from all assigned agents.
            </p>
            <div class="flex justify-center space-x-3">
              <button @click="showDeleteModal = false" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                Cancel
              </button>
              <button @click="deleteSkill" :disabled="deleting" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 disabled:opacity-50 transition-colors">
                {{ deleting ? 'Deleting...' : 'Delete Skill' }}
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

interface Agent {
  id: number;
  name: string;
  status: string;
}

interface Skill {
  id: number;
  name: string;
  slug: string;
  description?: string;
  icon: string;
  category: string;
  triggers: string[];
  config?: string;
  is_active: boolean;
  agents_count: number;
  executions_count: number;
  assigned_agents?: Agent[];
  created_at: string;
  updated_at: string;
}

// State
const skills = ref<Skill[]>([]);
const _loading = ref(false);
const saving = ref(false);
const deleting = ref(false);

// Modals
const showModal = ref(false);
const showViewModal = ref(false);
const showDeleteModal = ref(false);
const editingSkill = ref<Skill | null>(null);
const viewingSkill = ref<Skill | null>(null);
const deletingSkill = ref<Skill | null>(null);
const formError = ref('');
const newTrigger = ref('');

// Form
const form = reactive({
  name: '',
  slug: '',
  description: '',
  icon: '',
  category: 'Social Media',
  triggers: [] as string[],
  config: '',
  is_active: true
});

// Filters
const filters = reactive({
  search: '',
  category: '',
  status: ''
});

const allCategories = ['Social Media', 'News', 'Imagery', 'Analysis', 'NLP', 'Transport', 'Government', 'Dark Web'];

// Computed
const categories = computed(() => [...new Set(skills.value.map(s => s.category))]);
const activeCount = computed(() => skills.value.filter(s => s.is_active).length);
const categoriesCount = computed(() => categories.value.length);
const totalTriggers = computed(() => skills.value.reduce((sum, s) => sum + s.triggers.length, 0));
const executionsToday = computed(() => skills.value.reduce((sum, s) => sum + Math.floor(s.executions_count * 0.1), 0));

const filteredSkills = computed(() => {
  return skills.value.filter(skill => {
    const matchesSearch = !filters.search ||
      skill.name.toLowerCase().includes(filters.search.toLowerCase()) ||
      skill.triggers.some(t => t.toLowerCase().includes(filters.search.toLowerCase()));
    const matchesCategory = !filters.category || skill.category === filters.category;
    const matchesStatus = !filters.status ||
      (filters.status === 'active' && skill.is_active) ||
      (filters.status === 'inactive' && !skill.is_active);
    return matchesSearch && matchesCategory && matchesStatus;
  });
});

const filteredCategories = computed(() => {
  const groups: Record<string, Skill[]> = {};
  filteredSkills.value.forEach(skill => {
    if (!groups[skill.category]) {
      groups[skill.category] = [];
    }
    groups[skill.category]!.push(skill);
  });
  return Object.entries(groups).map(([name, skills]) => ({
    name,
    skills
  })).sort((a, b) => a.name.localeCompare(b.name));
});

// Methods
const fetchSkills = async () => {
  _loading.value = true;
  try {
    skills.value = [
      { id: 1, name: 'Twitter Scraper', slug: 'twitter-scraper', description: 'Scrapes Twitter/X for conflict-related posts using keywords and hashtags', icon: 'bird', category: 'Social Media', triggers: ['ukraine', 'russia', 'war', 'military', 'attack', 'strike'], is_active: true, agents_count: 3, executions_count: 1542, assigned_agents: [{ id: 1, name: 'Twitter OSINT Bot', status: 'running' }], created_at: '2024-01-01', updated_at: '2024-01-01' },
      { id: 2, name: 'Telegram Monitor', slug: 'telegram-monitor', description: 'Monitors Telegram channels for real-time updates', icon: 'plane', category: 'Social Media', triggers: ['telegram', 'channel', 'breaking'], is_active: true, agents_count: 2, executions_count: 892, assigned_agents: [{ id: 2, name: 'Telegram Channel Monitor', status: 'running' }], created_at: '2024-01-01', updated_at: '2024-01-01' },
      { id: 3, name: 'News Aggregator', slug: 'news-aggregator', description: 'Aggregates news from international sources', icon: 'newspaper', category: 'News', triggers: ['breaking', 'news', 'reuters', 'ap'], is_active: true, agents_count: 1, executions_count: 456, created_at: '2024-01-01', updated_at: '2024-01-01' },
      { id: 4, name: 'Geolocation', slug: 'geolocation', description: 'Extracts and verifies location data from images and text', icon: 'location', category: 'Analysis', triggers: ['coordinates', 'location', 'geolocate'], is_active: true, agents_count: 2, executions_count: 234, created_at: '2024-01-01', updated_at: '2024-01-01' },
      { id: 5, name: 'Image Analysis', slug: 'image-analysis', description: 'Analyzes images for equipment and vehicle identification', icon: 'camera', category: 'Imagery', triggers: ['image', 'photo', 'video', 'equipment'], is_active: true, agents_count: 1, executions_count: 567, created_at: '2024-01-01', updated_at: '2024-01-01' },
      { id: 6, name: 'Entity Extraction', slug: 'entity-extraction', description: 'Extracts named entities from text using NLP', icon: 'brain', category: 'NLP', triggers: ['name', 'organization', 'location'], is_active: true, agents_count: 3, executions_count: 1023, created_at: '2024-01-01', updated_at: '2024-01-01' },
      { id: 7, name: 'Satellite Monitor', slug: 'satellite-monitor', description: 'Monitors satellite imagery for changes in areas of interest', icon: 'satellite', category: 'Imagery', triggers: ['satellite', 'imagery', 'maxar', 'planet'], is_active: false, agents_count: 1, executions_count: 78, created_at: '2024-01-01', updated_at: '2024-01-01' },
      { id: 8, name: 'Flight Tracker', slug: 'flight-tracker', description: 'Tracks military and civilian aircraft movements', icon: 'plane', category: 'Transport', triggers: ['flight', 'aircraft', 'adsb', 'transponder'], is_active: true, agents_count: 1, executions_count: 345, created_at: '2024-01-01', updated_at: '2024-01-01' },
      { id: 9, name: 'Ship Tracker', slug: 'ship-tracker', description: 'Monitors vessel movements using AIS data', icon: 'ship', category: 'Transport', triggers: ['ship', 'vessel', 'ais', 'maritime'], is_active: true, agents_count: 1, executions_count: 289, created_at: '2024-01-01', updated_at: '2024-01-01' },
      { id: 10, name: 'Government Docs', slug: 'gov-docs', description: 'Scrapes government websites for official documents', icon: 'file', category: 'Government', triggers: ['ministry', 'official', 'government', 'decree'], is_active: false, agents_count: 0, executions_count: 45, created_at: '2024-01-01', updated_at: '2024-01-01' },
    ];
  } catch (error) {
    console.error('Failed to fetch skills:', error);
  } finally {
    _loading.value = false;
  }
};

const openCreateModal = () => {
  editingSkill.value = null;
  Object.assign(form, {
    name: '',
    slug: '',
    description: '',
    icon: '',
    category: 'Social Media',
    triggers: [],
    config: '',
    is_active: true
  });
  formError.value = '';
  showModal.value = true;
};

const editSkill = (skill: Skill) => {
  editingSkill.value = skill;
  Object.assign(form, {
    name: skill.name,
    slug: skill.slug,
    description: skill.description || '',
    icon: skill.icon,
    category: skill.category,
    triggers: [...skill.triggers],
    config: skill.config || '',
    is_active: skill.is_active
  });
  formError.value = '';
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  editingSkill.value = null;
};

const addTrigger = () => {
  if (newTrigger.value.trim() && !form.triggers.includes(newTrigger.value.trim().toLowerCase())) {
    form.triggers.push(newTrigger.value.trim().toLowerCase());
    newTrigger.value = '';
  }
};

const removeTrigger = (index: number) => {
  form.triggers.splice(index, 1);
};

const saveSkill = async () => {
  saving.value = true;
  formError.value = '';
  try {
    if (editingSkill.value) {
      const index = skills.value.findIndex(s => s.id === editingSkill.value!.id);
      if (index !== -1) {
        skills.value[index] = {
          ...skills.value[index],
          ...form,
          triggers: [...form.triggers],
          updated_at: new Date().toISOString()
        } as Skill;
      }
    } else {
      const newSkill: Skill = {
        id: skills.value.length + 1,
        ...form,
        triggers: [...form.triggers],
        agents_count: 0,
        executions_count: 0,
        created_at: new Date().toISOString(),
        updated_at: new Date().toISOString()
      };
      skills.value.unshift(newSkill);
    }
    closeModal();
  } catch (error: any) {
    formError.value = error?.message || 'Failed to save skill';
  } finally {
    saving.value = false;
  }
};

const viewSkill = (skill: Skill) => {
  viewingSkill.value = skill;
  showViewModal.value = true;
};

const confirmDelete = (skill: Skill) => {
  deletingSkill.value = skill;
  showDeleteModal.value = true;
};

const deleteSkill = async () => {
  if (!deletingSkill.value) return;
  deleting.value = true;
  try {
    skills.value = skills.value.filter(s => s.id !== deletingSkill.value!.id);
    showDeleteModal.value = false;
    deletingSkill.value = null;
  } catch (error) {
    console.error('Failed to delete skill:', error);
  } finally {
    deleting.value = false;
  }
};

const clearFilters = () => {
  filters.search = '';
  filters.category = '';
  filters.status = '';
};

const getCategoryIconClass = (category: string) => {
  const classes: Record<string, string> = {
    'Social Media': 'bg-blue-500',
    'News': 'bg-green-500',
    'Imagery': 'bg-purple-500',
    'Analysis': 'bg-yellow-500',
    'NLP': 'bg-pink-500',
    'Transport': 'bg-indigo-500',
    'Government': 'bg-red-500',
    'Dark Web': 'bg-gray-800'
  };
  return classes[category] || 'bg-gray-500';
};

const getCategoryBadgeClass = (category: string) => {
  const classes: Record<string, string> = {
    'Social Media': 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400',
    'News': 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400',
    'Imagery': 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400',
    'Analysis': 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400',
    'NLP': 'bg-pink-100 dark:bg-pink-900/30 text-pink-700 dark:text-pink-400',
    'Transport': 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400',
    'Government': 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400',
    'Dark Web': 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-400'
  };
  return classes[category] || 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-400';
};

onMounted(() => {
  fetchSkills();
});
</script>
