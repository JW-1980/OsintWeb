<template>
  <div class="mt-6 space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Achievement Management</h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage badges and achievements for gamification</p>
      </div>
      <button @click="openCreateModal" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Add Achievement
      </button>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">Total Achievements</p>
        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ achievements.length }}</p>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">Active</p>
        <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ activeCount }}</p>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">Total Awarded</p>
        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ totalAwarded }}</p>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">Categories</p>
        <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ categoriesCount }}</p>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search</label>
          <input v-model="filters.search" type="text" placeholder="Search achievements..." class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Category</label>
          <select v-model="filters.category" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            <option value="">All Categories</option>
            <option value="contribution">Contribution</option>
            <option value="verification">Verification</option>
            <option value="engagement">Engagement</option>
            <option value="expertise">Expertise</option>
            <option value="milestone">Milestone</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Rarity</label>
          <select v-model="filters.rarity" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            <option value="">All Rarities</option>
            <option value="common">Common</option>
            <option value="uncommon">Uncommon</option>
            <option value="rare">Rare</option>
            <option value="epic">Epic</option>
            <option value="legendary">Legendary</option>
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

    <!-- Achievements Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <div v-for="achievement in paginatedAchievements" :key="achievement.id" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-md transition-shadow">
        <!-- Badge Preview -->
        <div :class="getRarityBgClass(achievement.rarity)" class="p-6 flex items-center justify-center relative">
          <div class="absolute top-2 right-2">
            <span :class="getRarityBadgeClass(achievement.rarity)" class="px-2 py-0.5 text-xs font-medium rounded-full capitalize">
              {{ achievement.rarity }}
            </span>
          </div>
          <div class="w-20 h-20 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center">
            <span class="text-4xl">{{ achievement.icon }}</span>
          </div>
        </div>

        <!-- Achievement Info -->
        <div class="p-4">
          <div class="flex items-start justify-between mb-2">
            <div>
              <h3 class="font-semibold text-gray-900 dark:text-white">{{ achievement.name }}</h3>
              <span :class="getCategoryBadgeClass(achievement.category)" class="px-2 py-0.5 text-xs font-medium rounded-full capitalize">
                {{ achievement.category }}
              </span>
            </div>
            <span :class="achievement.is_active ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-400'" class="px-2 py-0.5 text-xs font-medium rounded-full">
              {{ achievement.is_active ? 'Active' : 'Inactive' }}
            </span>
          </div>

          <p class="text-sm text-gray-600 dark:text-gray-400 mb-3 line-clamp-2">{{ achievement.description }}</p>

          <div class="grid grid-cols-2 gap-2 text-sm mb-3">
            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-2">
              <p class="text-xs text-gray-500 dark:text-gray-400">Points</p>
              <p class="font-semibold text-gray-900 dark:text-white">{{ achievement.points }}</p>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-2">
              <p class="text-xs text-gray-500 dark:text-gray-400">Awarded</p>
              <p class="font-semibold text-gray-900 dark:text-white">{{ achievement.awarded_count }}</p>
            </div>
          </div>

          <!-- Requirements Preview -->
          <div class="text-xs text-gray-500 dark:text-gray-400 mb-3">
            <span class="font-medium">Requirement:</span> {{ achievement.requirement_description }}
          </div>

          <!-- Actions -->
          <div class="flex items-center justify-end space-x-2 pt-3 border-t border-gray-200 dark:border-gray-700">
            <button @click="viewAchievement(achievement)" class="p-1.5 text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700" title="View">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              </svg>
            </button>
            <button @click="editAchievement(achievement)" class="p-1.5 text-gray-400 hover:text-green-600 dark:hover:text-green-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700" title="Edit">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
            </button>
            <button @click="confirmDelete(achievement)" class="p-1.5 text-gray-400 hover:text-red-600 dark:hover:text-red-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700" title="Delete">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-if="filteredAchievements.length === 0" class="text-center py-12 text-gray-500 dark:text-gray-400">
      <p>No achievements found</p>
      <button @click="clearFilters" class="mt-2 text-blue-600 dark:text-blue-400 hover:underline">Clear filters</button>
    </div>

    <!-- Pagination -->
    <div v-if="totalPages > 1" class="flex items-center justify-between">
      <p class="text-sm text-gray-500 dark:text-gray-400">{{ filteredAchievements.length }} achievements</p>
      <div class="flex space-x-2">
        <button @click="currentPage--" :disabled="currentPage === 1" class="px-3 py-1.5 text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg disabled:opacity-50">Previous</button>
        <span class="px-3 py-1.5 text-sm text-gray-700 dark:text-gray-300">{{ currentPage }} / {{ totalPages }}</span>
        <button @click="currentPage++" :disabled="currentPage === totalPages" class="px-3 py-1.5 text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg disabled:opacity-50">Next</button>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto" @click.self="closeModal">
      <div class="flex items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-black/50" @click="closeModal"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-2xl w-full p-6 max-h-[90vh] overflow-y-auto">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
              {{ editingAchievement ? 'Edit Achievement' : 'Create Achievement' }}
            </h3>
            <button @click="closeModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <form @submit.prevent="saveAchievement" class="space-y-4">
            <!-- Badge Preview -->
            <div class="flex justify-center">
              <div :class="getRarityBgClass(form.rarity)" class="w-32 h-32 rounded-2xl flex items-center justify-center">
                <div class="w-20 h-20 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center">
                  <span class="text-4xl">{{ form.icon || '?' }}</span>
                </div>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name *</label>
                <input v-model="form.name" type="text" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Icon (Emoji) *</label>
                <input v-model="form.icon" type="text" required maxlength="4" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent text-center text-2xl" placeholder="Pick an emoji" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Category *</label>
                <select v-model="form.category" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                  <option value="contribution">Contribution</option>
                  <option value="verification">Verification</option>
                  <option value="engagement">Engagement</option>
                  <option value="expertise">Expertise</option>
                  <option value="milestone">Milestone</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Rarity *</label>
                <select v-model="form.rarity" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                  <option value="common">Common</option>
                  <option value="uncommon">Uncommon</option>
                  <option value="rare">Rare</option>
                  <option value="epic">Epic</option>
                  <option value="legendary">Legendary</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Points *</label>
                <input v-model.number="form.points" type="number" min="0" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Requirement Type *</label>
                <select v-model="form.requirement_type" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                  <option value="events_created">Events Created</option>
                  <option value="events_verified">Events Verified</option>
                  <option value="points_earned">Points Earned</option>
                  <option value="days_active">Days Active</option>
                  <option value="contributions">Total Contributions</option>
                  <option value="manual">Manual Award</option>
                </select>
              </div>
            </div>

            <div v-if="form.requirement_type !== 'manual'">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Requirement Value *</label>
              <input v-model.number="form.requirement_value" type="number" min="1" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
              <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Number of {{ form.requirement_type.replace('_', ' ') }} required</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description *</label>
              <textarea v-model="form.description" rows="2" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Requirement Description</label>
              <input v-model="form.requirement_description" type="text" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., Create 100 verified events" />
            </div>

            <div class="flex items-center">
              <input v-model="form.is_active" type="checkbox" id="is_active" class="w-4 h-4 text-blue-600 bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded focus:ring-blue-500" />
              <label for="is_active" class="ml-2 text-sm text-gray-600 dark:text-gray-400">Achievement is active and can be earned</label>
            </div>

            <div v-if="formError" class="p-3 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 rounded-lg text-sm">
              {{ formError }}
            </div>

            <div class="flex justify-end space-x-3 pt-4">
              <button type="button" @click="closeModal" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                Cancel
              </button>
              <button type="submit" :disabled="saving" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50 transition-colors">
                {{ saving ? 'Saving...' : (editingAchievement ? 'Update' : 'Create') }}
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
        <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-lg w-full p-6 max-h-[90vh] overflow-y-auto">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Achievement Details</h3>
            <button @click="showViewModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <div v-if="viewingAchievement" class="space-y-6">
            <!-- Badge Display -->
            <div class="flex justify-center">
              <div :class="getRarityBgClass(viewingAchievement.rarity)" class="w-32 h-32 rounded-2xl flex items-center justify-center">
                <div class="w-20 h-20 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center">
                  <span class="text-5xl">{{ viewingAchievement.icon }}</span>
                </div>
              </div>
            </div>

            <div class="text-center">
              <h4 class="text-xl font-bold text-gray-900 dark:text-white">{{ viewingAchievement.name }}</h4>
              <div class="flex items-center justify-center space-x-2 mt-2">
                <span :class="getRarityBadgeClass(viewingAchievement.rarity)" class="px-2.5 py-1 text-xs font-medium rounded-full capitalize">
                  {{ viewingAchievement.rarity }}
                </span>
                <span :class="getCategoryBadgeClass(viewingAchievement.category)" class="px-2.5 py-1 text-xs font-medium rounded-full capitalize">
                  {{ viewingAchievement.category }}
                </span>
              </div>
            </div>

            <p class="text-center text-gray-600 dark:text-gray-400">{{ viewingAchievement.description }}</p>

            <div class="grid grid-cols-2 gap-4">
              <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 text-center">
                <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ viewingAchievement.points }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Points</p>
              </div>
              <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 text-center">
                <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ viewingAchievement.awarded_count }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Times Awarded</p>
              </div>
            </div>

            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
              <p class="text-sm font-medium text-gray-900 dark:text-white mb-1">Requirement</p>
              <p class="text-sm text-gray-600 dark:text-gray-400">{{ viewingAchievement.requirement_description }}</p>
              <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                Type: {{ viewingAchievement.requirement_type.replace('_', ' ') }} |
                Value: {{ viewingAchievement.requirement_value }}
              </p>
            </div>

            <!-- Recent Recipients -->
            <div v-if="viewingAchievement.recent_recipients && viewingAchievement.recent_recipients.length > 0">
              <h5 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Recent Recipients</h5>
              <div class="flex -space-x-2">
                <div v-for="user in viewingAchievement.recent_recipients" :key="user.id" class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white text-xs font-medium border-2 border-white dark:border-gray-800" :title="user.name">
                  {{ user.name.charAt(0) }}
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
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Delete Achievement</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
              Are you sure you want to delete <strong>{{ deletingAchievement?.name }}</strong>? Users will keep their earned badges but no new ones will be awarded.
            </p>
            <div class="flex justify-center space-x-3">
              <button @click="showDeleteModal = false" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                Cancel
              </button>
              <button @click="deleteAchievement" :disabled="deleting" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 disabled:opacity-50 transition-colors">
                {{ deleting ? 'Deleting...' : 'Delete' }}
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

interface Achievement {
  id: number;
  name: string;
  description: string;
  icon: string;
  category: 'contribution' | 'verification' | 'engagement' | 'expertise' | 'milestone';
  rarity: 'common' | 'uncommon' | 'rare' | 'epic' | 'legendary';
  points: number;
  requirement_type: string;
  requirement_value: number;
  requirement_description: string;
  is_active: boolean;
  awarded_count: number;
  recent_recipients?: User[];
  created_at: string;
  updated_at: string;
}

// State
const achievements = ref<Achievement[]>([]);
const loading = ref(false);
const saving = ref(false);
const deleting = ref(false);
const currentPage = ref(1);
const perPage = ref(9);

// Modals
const showModal = ref(false);
const showViewModal = ref(false);
const showDeleteModal = ref(false);
const editingAchievement = ref<Achievement | null>(null);
const viewingAchievement = ref<Achievement | null>(null);
const deletingAchievement = ref<Achievement | null>(null);
const formError = ref('');

// Form
const form = reactive({
  name: '',
  description: '',
  icon: '',
  category: 'contribution' as Achievement['category'],
  rarity: 'common' as Achievement['rarity'],
  points: 10,
  requirement_type: 'events_created',
  requirement_value: 1,
  requirement_description: '',
  is_active: true
});

// Filters
const filters = reactive({
  search: '',
  category: '',
  rarity: '',
  status: ''
});

// Computed
const activeCount = computed(() => achievements.value.filter(a => a.is_active).length);
const totalAwarded = computed(() => achievements.value.reduce((sum, a) => sum + a.awarded_count, 0));
const categoriesCount = computed(() => new Set(achievements.value.map(a => a.category)).size);

const filteredAchievements = computed(() => {
  return achievements.value.filter(achievement => {
    const matchesSearch = !filters.search ||
      achievement.name.toLowerCase().includes(filters.search.toLowerCase()) ||
      achievement.description.toLowerCase().includes(filters.search.toLowerCase());
    const matchesCategory = !filters.category || achievement.category === filters.category;
    const matchesRarity = !filters.rarity || achievement.rarity === filters.rarity;
    const matchesStatus = !filters.status ||
      (filters.status === 'active' && achievement.is_active) ||
      (filters.status === 'inactive' && !achievement.is_active);
    return matchesSearch && matchesCategory && matchesRarity && matchesStatus;
  });
});

const totalPages = computed(() => Math.ceil(filteredAchievements.value.length / perPage.value) || 1);

const paginatedAchievements = computed(() => {
  const start = (currentPage.value - 1) * perPage.value;
  return filteredAchievements.value.slice(start, start + perPage.value);
});

// Methods
const fetchAchievements = async () => {
  loading.value = true;
  try {
    achievements.value = [
      { id: 1, name: 'First Steps', description: 'Create your first event', icon: '1', category: 'contribution', rarity: 'common', points: 10, requirement_type: 'events_created', requirement_value: 1, requirement_description: 'Create 1 event', is_active: true, awarded_count: 245, recent_recipients: [{ id: 1, name: 'John' }, { id: 2, name: 'Jane' }], created_at: '2024-01-01', updated_at: '2024-01-01' },
      { id: 2, name: 'Prolific Contributor', description: 'Create 50 verified events', icon: '50', category: 'contribution', rarity: 'rare', points: 100, requirement_type: 'events_created', requirement_value: 50, requirement_description: 'Create 50 verified events', is_active: true, awarded_count: 42, recent_recipients: [{ id: 1, name: 'Alex' }], created_at: '2024-01-01', updated_at: '2024-01-01' },
      { id: 3, name: 'Centurion', description: 'Create 100 events', icon: '100', category: 'milestone', rarity: 'epic', points: 250, requirement_type: 'events_created', requirement_value: 100, requirement_description: 'Create 100 events', is_active: true, awarded_count: 18, recent_recipients: [], created_at: '2024-01-01', updated_at: '2024-01-01' },
      { id: 4, name: 'Sharp Eye', description: 'Verify 25 events submitted by others', icon: 'eye', category: 'verification', rarity: 'uncommon', points: 50, requirement_type: 'events_verified', requirement_value: 25, requirement_description: 'Verify 25 events', is_active: true, awarded_count: 67, recent_recipients: [{ id: 3, name: 'Sarah' }], created_at: '2024-01-01', updated_at: '2024-01-01' },
      { id: 5, name: 'Veteran', description: 'Active on the platform for 365 days', icon: 'calendar', category: 'engagement', rarity: 'rare', points: 150, requirement_type: 'days_active', requirement_value: 365, requirement_description: 'Be active for 365 days', is_active: true, awarded_count: 34, recent_recipients: [], created_at: '2024-01-01', updated_at: '2024-01-01' },
      { id: 6, name: 'OSINT Master', description: 'Earn 1000 contribution points', icon: 'star', category: 'expertise', rarity: 'legendary', points: 500, requirement_type: 'points_earned', requirement_value: 1000, requirement_description: 'Earn 1000 total points', is_active: true, awarded_count: 5, recent_recipients: [{ id: 4, name: 'Mike' }], created_at: '2024-01-01', updated_at: '2024-01-01' },
      { id: 7, name: 'Welcome', description: 'Complete your profile setup', icon: 'wave', category: 'engagement', rarity: 'common', points: 5, requirement_type: 'manual', requirement_value: 1, requirement_description: 'Complete profile setup', is_active: true, awarded_count: 312, recent_recipients: [], created_at: '2024-01-01', updated_at: '2024-01-01' },
      { id: 8, name: 'Night Owl', description: 'Submit events between midnight and 6am', icon: 'moon', category: 'engagement', rarity: 'uncommon', points: 25, requirement_type: 'manual', requirement_value: 1, requirement_description: 'Submit late-night events', is_active: false, awarded_count: 89, recent_recipients: [], created_at: '2024-01-01', updated_at: '2024-01-01' },
    ];
  } catch (error) {
    console.error('Failed to fetch achievements:', error);
  } finally {
    loading.value = false;
  }
};

const openCreateModal = () => {
  editingAchievement.value = null;
  Object.assign(form, {
    name: '',
    description: '',
    icon: '',
    category: 'contribution',
    rarity: 'common',
    points: 10,
    requirement_type: 'events_created',
    requirement_value: 1,
    requirement_description: '',
    is_active: true
  });
  formError.value = '';
  showModal.value = true;
};

const editAchievement = (achievement: Achievement) => {
  editingAchievement.value = achievement;
  Object.assign(form, {
    name: achievement.name,
    description: achievement.description,
    icon: achievement.icon,
    category: achievement.category,
    rarity: achievement.rarity,
    points: achievement.points,
    requirement_type: achievement.requirement_type,
    requirement_value: achievement.requirement_value,
    requirement_description: achievement.requirement_description,
    is_active: achievement.is_active
  });
  formError.value = '';
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  editingAchievement.value = null;
};

const saveAchievement = async () => {
  saving.value = true;
  formError.value = '';
  try {
    if (editingAchievement.value) {
      const index = achievements.value.findIndex(a => a.id === editingAchievement.value!.id);
      if (index !== -1) {
        achievements.value[index] = {
          ...achievements.value[index],
          ...form,
          updated_at: new Date().toISOString()
        };
      }
    } else {
      const newAchievement: Achievement = {
        id: achievements.value.length + 1,
        ...form,
        awarded_count: 0,
        recent_recipients: [],
        created_at: new Date().toISOString(),
        updated_at: new Date().toISOString()
      };
      achievements.value.unshift(newAchievement);
    }
    closeModal();
  } catch (error: any) {
    formError.value = error?.message || 'Failed to save achievement';
  } finally {
    saving.value = false;
  }
};

const viewAchievement = (achievement: Achievement) => {
  viewingAchievement.value = achievement;
  showViewModal.value = true;
};

const confirmDelete = (achievement: Achievement) => {
  deletingAchievement.value = achievement;
  showDeleteModal.value = true;
};

const deleteAchievement = async () => {
  if (!deletingAchievement.value) return;
  deleting.value = true;
  try {
    achievements.value = achievements.value.filter(a => a.id !== deletingAchievement.value!.id);
    showDeleteModal.value = false;
    deletingAchievement.value = null;
  } catch (error) {
    console.error('Failed to delete achievement:', error);
  } finally {
    deleting.value = false;
  }
};

const clearFilters = () => {
  filters.search = '';
  filters.category = '';
  filters.rarity = '';
  filters.status = '';
  currentPage.value = 1;
};

const getRarityBgClass = (rarity: string) => {
  const classes: Record<string, string> = {
    common: 'bg-gradient-to-br from-gray-400 to-gray-600',
    uncommon: 'bg-gradient-to-br from-green-400 to-green-600',
    rare: 'bg-gradient-to-br from-blue-400 to-blue-600',
    epic: 'bg-gradient-to-br from-purple-400 to-purple-600',
    legendary: 'bg-gradient-to-br from-yellow-400 to-orange-500'
  };
  return classes[rarity] || classes.common;
};

const getRarityBadgeClass = (rarity: string) => {
  const classes: Record<string, string> = {
    common: 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300',
    uncommon: 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400',
    rare: 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400',
    epic: 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400',
    legendary: 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400'
  };
  return classes[rarity] || classes.common;
};

const getCategoryBadgeClass = (category: string) => {
  const classes: Record<string, string> = {
    contribution: 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400',
    verification: 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400',
    engagement: 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400',
    expertise: 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400',
    milestone: 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400'
  };
  return classes[category] || 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-400';
};

onMounted(() => {
  fetchAchievements();
});
</script>
