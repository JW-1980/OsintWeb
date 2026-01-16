<template>
  <div class="mt-6 space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Role Management</h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage roles and their associated permissions</p>
      </div>
      <button @click="openCreateModal" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Add Role
      </button>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">Total Roles</p>
        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ roles.length }}</p>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">System Roles</p>
        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ systemRolesCount }}</p>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">Custom Roles</p>
        <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ customRolesCount }}</p>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">Total Permissions</p>
        <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ permissions.length }}</p>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search</label>
          <input v-model="filters.search" type="text" placeholder="Search roles..." class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type</label>
          <select v-model="filters.type" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            <option value="">All Types</option>
            <option value="system">System</option>
            <option value="custom">Custom</option>
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

    <!-- Roles Table -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
        <span class="text-sm text-gray-500 dark:text-gray-400">{{ filteredRoles.length }} roles found</span>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50 dark:bg-gray-700/50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Role</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Priority</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Permissions</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Users</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Type</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            <tr v-for="role in paginatedRoles" :key="role.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div :class="getRoleColorClass(role.color)" class="w-10 h-10 rounded-lg flex items-center justify-center text-white font-bold text-sm mr-3">
                    {{ role.name.charAt(0).toUpperCase() }}
                  </div>
                  <div>
                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ role.name }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ role.slug }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12" />
                  </svg>
                  <span class="text-sm text-gray-900 dark:text-white">{{ role.priority }}</span>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400">
                  {{ role.permissions_count }} permissions
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                {{ role.users_count }} users
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="role.is_system ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400' : 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400'" class="px-2.5 py-1 text-xs font-medium rounded-full">
                  {{ role.is_system ? 'System' : 'Custom' }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="role.is_active ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-400'" class="px-2.5 py-1 text-xs font-medium rounded-full">
                  {{ role.is_active ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right">
                <div class="flex items-center justify-end space-x-2">
                  <button @click="viewRole(role)" class="p-1.5 text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700" title="View">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                  </button>
                  <button @click="editRole(role)" class="p-1.5 text-gray-400 hover:text-green-600 dark:hover:text-green-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700" title="Edit">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </button>
                  <button v-if="!role.is_system" @click="confirmDelete(role)" class="p-1.5 text-gray-400 hover:text-red-600 dark:hover:text-red-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700" title="Delete">
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
          Showing {{ (currentPage - 1) * perPage + 1 }} to {{ Math.min(currentPage * perPage, filteredRoles.length) }} of {{ filteredRoles.length }} roles
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
        <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-4xl w-full p-6 max-h-[90vh] overflow-y-auto">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
              {{ editingRole ? 'Edit Role' : 'Create Role' }}
            </h3>
            <button @click="closeModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <form @submit.prevent="saveRole" class="space-y-6">
            <!-- Basic Info -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Role Name *</label>
                <input v-model="form.name" type="text" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Slug *</label>
                <input v-model="form.slug" type="text" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Priority *</label>
                <input v-model.number="form.priority" type="number" min="0" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Higher priority roles take precedence (100 = highest)</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Color</label>
                <select v-model="form.color" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                  <option value="blue">Blue</option>
                  <option value="green">Green</option>
                  <option value="purple">Purple</option>
                  <option value="red">Red</option>
                  <option value="yellow">Yellow</option>
                  <option value="indigo">Indigo</option>
                  <option value="pink">Pink</option>
                  <option value="gray">Gray</option>
                </select>
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
              <textarea v-model="form.description" rows="2" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
            </div>

            <div class="flex items-center">
              <input v-model="form.is_active" type="checkbox" id="is_active" class="w-4 h-4 text-blue-600 bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded focus:ring-blue-500" />
              <label for="is_active" class="ml-2 text-sm text-gray-600 dark:text-gray-400">Role is active</label>
            </div>

            <!-- Permissions Section -->
            <div>
              <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Permissions</h4>
              <div class="border border-gray-200 dark:border-gray-700 rounded-lg max-h-64 overflow-y-auto">
                <div v-for="(group, groupName) in groupedPermissions" :key="groupName" class="border-b border-gray-200 dark:border-gray-700 last:border-b-0">
                  <div class="px-4 py-2 bg-gray-50 dark:bg-gray-700/50 flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ groupName }}</span>
                    <button type="button" @click="toggleGroup(groupName)" class="text-xs text-blue-600 dark:text-blue-400 hover:underline">
                      {{ isGroupSelected(groupName) ? 'Deselect All' : 'Select All' }}
                    </button>
                  </div>
                  <div class="px-4 py-2 grid grid-cols-2 md:grid-cols-3 gap-2">
                    <label v-for="permission in group" :key="permission.id" class="flex items-center">
                      <input type="checkbox" :value="permission.id" v-model="form.permission_ids" class="w-4 h-4 text-blue-600 bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded focus:ring-blue-500" />
                      <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">{{ permission.name }}</span>
                    </label>
                  </div>
                </div>
              </div>
            </div>

            <div v-if="formError" class="p-3 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 rounded-lg text-sm">
              {{ formError }}
            </div>

            <div class="flex justify-end space-x-3 pt-4">
              <button type="button" @click="closeModal" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                Cancel
              </button>
              <button type="submit" :disabled="saving" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50 transition-colors">
                {{ saving ? 'Saving...' : (editingRole ? 'Update Role' : 'Create Role') }}
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
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Role Details</h3>
            <button @click="showViewModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <div v-if="viewingRole" class="space-y-6">
            <div class="flex items-center space-x-4">
              <div :class="getRoleColorClass(viewingRole.color)" class="w-16 h-16 rounded-xl flex items-center justify-center text-white font-bold text-2xl">
                {{ viewingRole.name.charAt(0).toUpperCase() }}
              </div>
              <div>
                <h4 class="text-xl font-semibold text-gray-900 dark:text-white">{{ viewingRole.name }}</h4>
                <p class="text-gray-500 dark:text-gray-400">{{ viewingRole.slug }}</p>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">Priority</p>
                <p class="text-sm font-medium text-gray-900 dark:text-white mt-1">{{ viewingRole.priority }}</p>
              </div>
              <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">Type</p>
                <p class="text-sm font-medium text-gray-900 dark:text-white mt-1">{{ viewingRole.is_system ? 'System' : 'Custom' }}</p>
              </div>
              <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">Users</p>
                <p class="text-sm font-medium text-gray-900 dark:text-white mt-1">{{ viewingRole.users_count }}</p>
              </div>
              <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">Status</p>
                <p class="text-sm font-medium text-gray-900 dark:text-white mt-1">{{ viewingRole.is_active ? 'Active' : 'Inactive' }}</p>
              </div>
            </div>

            <div v-if="viewingRole.description">
              <h5 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">Description</h5>
              <p class="text-sm text-gray-600 dark:text-gray-400">{{ viewingRole.description }}</p>
            </div>

            <div>
              <h5 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">Permissions ({{ viewingRole.permissions_count }})</h5>
              <div class="flex flex-wrap gap-2">
                <span v-for="perm in viewingRole.permissions" :key="perm.id" class="px-2 py-1 text-xs font-medium rounded-full bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400">
                  {{ perm.name }}
                </span>
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
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Delete Role</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
              Are you sure you want to delete <strong>{{ deletingRole?.name }}</strong>? Users with this role will lose their permissions.
            </p>
            <div class="flex justify-center space-x-3">
              <button @click="showDeleteModal = false" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                Cancel
              </button>
              <button @click="deleteRole" :disabled="deleting" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 disabled:opacity-50 transition-colors">
                {{ deleting ? 'Deleting...' : 'Delete Role' }}
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

interface Permission {
  id: number;
  name: string;
  slug: string;
  group: string;
  description?: string;
}

interface Role {
  id: number;
  name: string;
  slug: string;
  description?: string;
  color: string;
  priority: number;
  is_system: boolean;
  is_active: boolean;
  permissions_count: number;
  users_count: number;
  permissions: Permission[];
  created_at: string;
  updated_at: string;
}

// State
const roles = ref<Role[]>([]);
const permissions = ref<Permission[]>([]);
const _loading = ref(false);
const saving = ref(false);
const deleting = ref(false);
const currentPage = ref(1);
const perPage = ref(10);

// Modals
const showModal = ref(false);
const showViewModal = ref(false);
const showDeleteModal = ref(false);
const editingRole = ref<Role | null>(null);
const viewingRole = ref<Role | null>(null);
const deletingRole = ref<Role | null>(null);
const formError = ref('');

// Form
const form = reactive({
  name: '',
  slug: '',
  description: '',
  color: 'blue',
  priority: 50,
  is_active: true,
  permission_ids: [] as number[]
});

// Filters
const filters = reactive({
  search: '',
  type: '',
  status: ''
});

// Computed
const systemRolesCount = computed(() => roles.value.filter(r => r.is_system).length);
const customRolesCount = computed(() => roles.value.filter(r => !r.is_system).length);

const filteredRoles = computed(() => {
  return roles.value.filter(role => {
    const matchesSearch = !filters.search ||
      role.name.toLowerCase().includes(filters.search.toLowerCase()) ||
      role.slug.toLowerCase().includes(filters.search.toLowerCase());
    const matchesType = !filters.type ||
      (filters.type === 'system' && role.is_system) ||
      (filters.type === 'custom' && !role.is_system);
    const matchesStatus = !filters.status ||
      (filters.status === 'active' && role.is_active) ||
      (filters.status === 'inactive' && !role.is_active);
    return matchesSearch && matchesType && matchesStatus;
  });
});

const totalPages = computed(() => Math.ceil(filteredRoles.value.length / perPage.value) || 1);

const paginatedRoles = computed(() => {
  const start = (currentPage.value - 1) * perPage.value;
  return filteredRoles.value.slice(start, start + perPage.value);
});

const groupedPermissions = computed(() => {
  const groups: Record<string, Permission[]> = {};
  permissions.value.forEach(perm => {
    if (!groups[perm.group]) {
      groups[perm.group] = [];
    }
    groups[perm.group]!.push(perm);
  });
  return groups;
});

// Methods
const fetchData = async () => {
  _loading.value = true;
  try {
    // Mock data
    permissions.value = [
      { id: 1, name: 'View Events', slug: 'events.view', group: 'Events', description: 'View all events' },
      { id: 2, name: 'Create Events', slug: 'events.create', group: 'Events', description: 'Create new events' },
      { id: 3, name: 'Edit Events', slug: 'events.edit', group: 'Events', description: 'Edit existing events' },
      { id: 4, name: 'Delete Events', slug: 'events.delete', group: 'Events', description: 'Delete events' },
      { id: 5, name: 'Verify Events', slug: 'events.verify', group: 'Events', description: 'Verify events' },
      { id: 6, name: 'View Users', slug: 'users.view', group: 'Users', description: 'View all users' },
      { id: 7, name: 'Create Users', slug: 'users.create', group: 'Users', description: 'Create new users' },
      { id: 8, name: 'Edit Users', slug: 'users.edit', group: 'Users', description: 'Edit existing users' },
      { id: 9, name: 'Delete Users', slug: 'users.delete', group: 'Users', description: 'Delete users' },
      { id: 10, name: 'View Reports', slug: 'reports.view', group: 'Reports', description: 'View reports' },
      { id: 11, name: 'Generate Reports', slug: 'reports.generate', group: 'Reports', description: 'Generate reports' },
      { id: 12, name: 'Export Reports', slug: 'reports.export', group: 'Reports', description: 'Export reports' },
      { id: 13, name: 'View Analytics', slug: 'analytics.view', group: 'Analytics', description: 'View analytics' },
      { id: 14, name: 'Admin Panel', slug: 'admin.access', group: 'Admin', description: 'Access admin panel' },
      { id: 15, name: 'Manage Roles', slug: 'roles.manage', group: 'Admin', description: 'Manage roles' },
      { id: 16, name: 'System Settings', slug: 'settings.manage', group: 'Admin', description: 'Manage system settings' },
    ];

    roles.value = [
      { id: 1, name: 'Administrator', slug: 'admin', description: 'Full system access', color: 'red', priority: 100, is_system: true, is_active: true, permissions_count: 16, users_count: 3, permissions: permissions.value, created_at: '2024-01-01', updated_at: '2024-01-01' },
      { id: 2, name: 'Analyst', slug: 'analyst', description: 'Can analyze and verify events', color: 'purple', priority: 75, is_system: true, is_active: true, permissions_count: 10, users_count: 12, permissions: permissions.value.slice(0, 10), created_at: '2024-01-01', updated_at: '2024-01-01' },
      { id: 3, name: 'Contributor', slug: 'contributor', description: 'Can create and edit events', color: 'blue', priority: 50, is_system: true, is_active: true, permissions_count: 6, users_count: 45, permissions: permissions.value.slice(0, 6), created_at: '2024-01-01', updated_at: '2024-01-01' },
      { id: 4, name: 'Viewer', slug: 'viewer', description: 'Read-only access', color: 'gray', priority: 25, is_system: true, is_active: true, permissions_count: 3, users_count: 128, permissions: permissions.value.slice(0, 3), created_at: '2024-01-01', updated_at: '2024-01-01' },
      { id: 5, name: 'Report Manager', slug: 'report-manager', description: 'Specialized role for report generation', color: 'green', priority: 60, is_system: false, is_active: true, permissions_count: 5, users_count: 8, permissions: permissions.value.slice(9, 14), created_at: '2024-01-10', updated_at: '2024-01-10' },
      { id: 6, name: 'Field Researcher', slug: 'field-researcher', description: 'Mobile data collection role', color: 'yellow', priority: 55, is_system: false, is_active: true, permissions_count: 4, users_count: 22, permissions: permissions.value.slice(0, 4), created_at: '2024-01-15', updated_at: '2024-01-15' },
    ];
  } catch (error) {
    console.error('Failed to fetch data:', error);
  } finally {
    _loading.value = false;
  }
};

const openCreateModal = () => {
  editingRole.value = null;
  Object.assign(form, {
    name: '',
    slug: '',
    description: '',
    color: 'blue',
    priority: 50,
    is_active: true,
    permission_ids: []
  });
  formError.value = '';
  showModal.value = true;
};

const editRole = (role: Role) => {
  editingRole.value = role;
  Object.assign(form, {
    name: role.name,
    slug: role.slug,
    description: role.description || '',
    color: role.color,
    priority: role.priority,
    is_active: role.is_active,
    permission_ids: role.permissions.map(p => p.id)
  });
  formError.value = '';
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  editingRole.value = null;
};

const saveRole = async () => {
  saving.value = true;
  formError.value = '';
  try {
    const selectedPermissions = permissions.value.filter(p => form.permission_ids.includes(p.id));
    if (editingRole.value) {
      const index = roles.value.findIndex(r => r.id === editingRole.value!.id);
      if (index !== -1) {
        roles.value[index] = {
          ...roles.value[index],
          ...form,
          permissions: selectedPermissions,
          permissions_count: selectedPermissions.length,
          updated_at: new Date().toISOString()
        } as Role;
      }
    } else {
      const newRole: Role = {
        id: roles.value.length + 1,
        ...form,
        is_system: false,
        permissions: selectedPermissions,
        permissions_count: selectedPermissions.length,
        users_count: 0,
        created_at: new Date().toISOString(),
        updated_at: new Date().toISOString()
      };
      roles.value.unshift(newRole);
    }
    closeModal();
  } catch (error: any) {
    formError.value = error?.message || 'Failed to save role';
  } finally {
    saving.value = false;
  }
};

const viewRole = (role: Role) => {
  viewingRole.value = role;
  showViewModal.value = true;
};

const confirmDelete = (role: Role) => {
  deletingRole.value = role;
  showDeleteModal.value = true;
};

const deleteRole = async () => {
  if (!deletingRole.value) return;
  deleting.value = true;
  try {
    roles.value = roles.value.filter(r => r.id !== deletingRole.value!.id);
    showDeleteModal.value = false;
    deletingRole.value = null;
  } catch (error) {
    console.error('Failed to delete role:', error);
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

const getRoleColorClass = (color: string) => {
  const colors: Record<string, string> = {
    blue: 'bg-blue-500',
    green: 'bg-green-500',
    purple: 'bg-purple-500',
    red: 'bg-red-500',
    yellow: 'bg-yellow-500',
    indigo: 'bg-indigo-500',
    pink: 'bg-pink-500',
    gray: 'bg-gray-500'
  };
  return colors[color] || 'bg-blue-500';
};

const toggleGroup = (groupName: string) => {
  const groupPerms = groupedPermissions.value[groupName];
  if (!groupPerms) return;
  const groupPermIds = groupPerms.map(p => p.id);
  const allSelected = groupPermIds.every(id => form.permission_ids.includes(id));

  if (allSelected) {
    form.permission_ids = form.permission_ids.filter(id => !groupPermIds.includes(id));
  } else {
    const newIds = groupPermIds.filter(id => !form.permission_ids.includes(id));
    form.permission_ids.push(...newIds);
  }
};

const isGroupSelected = (groupName: string) => {
  const groupPerms = groupedPermissions.value[groupName];
  if (!groupPerms) return false;
  const groupPermIds = groupPerms.map(p => p.id);
  return groupPermIds.every(id => form.permission_ids.includes(id));
};

onMounted(() => {
  fetchData();
});
</script>
