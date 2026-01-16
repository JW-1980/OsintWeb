<template>
  <div class="mt-6 space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h2 class="text-xl font-bold text-gray-900 dark:text-white">User Management</h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage platform users, roles, and permissions</p>
      </div>
      <button @click="openCreateModal" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Add User
      </button>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search</label>
          <input v-model="filters.search" type="text" placeholder="Search users..." class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Role</label>
          <select v-model="filters.role" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            <option value="">All Roles</option>
            <option value="admin">Admin</option>
            <option value="analyst">Analyst</option>
            <option value="contributor">Contributor</option>
            <option value="viewer">Viewer</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
          <select v-model="filters.status" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
            <option value="banned">Banned</option>
          </select>
        </div>
        <div class="flex items-end">
          <button @click="clearFilters" class="w-full px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
            Clear Filters
          </button>
        </div>
      </div>
    </div>

    <!-- Users Table -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
      <!-- Table Header -->
      <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
        <span class="text-sm text-gray-500 dark:text-gray-400">{{ filteredUsers.length }} users found</span>
        <div class="flex items-center space-x-2">
          <button @click="exportUsers" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">Export CSV</button>
        </div>
      </div>

      <!-- Table -->
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50 dark:bg-gray-700/50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">User</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Role</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Organization</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Joined</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Last Active</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            <tr v-for="user in paginatedUsers" :key="user.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-semibold text-sm mr-3">
                    {{ user.name.charAt(0).toUpperCase() }}
                  </div>
                  <div>
                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ user.name }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ user.email }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getRoleBadgeClass(user.role)" class="px-2.5 py-1 text-xs font-medium rounded-full">
                  {{ user.role }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getStatusBadgeClass(user.status)" class="px-2.5 py-1 text-xs font-medium rounded-full">
                  {{ user.status }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                {{ user.organization || '-' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                {{ formatDate(user.created_at) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                {{ formatDate(user.last_active_at) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right">
                <div class="flex items-center justify-end space-x-2">
                  <button @click="viewUser(user)" class="p-1.5 text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700" title="View">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                  </button>
                  <button @click="editUser(user)" class="p-1.5 text-gray-400 hover:text-green-600 dark:hover:text-green-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700" title="Edit">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </button>
                  <button @click="toggleBan(user)" :class="user.status === 'banned' ? 'hover:text-green-600 dark:hover:text-green-400' : 'hover:text-yellow-600 dark:hover:text-yellow-400'" class="p-1.5 text-gray-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700" :title="user.status === 'banned' ? 'Unban' : 'Ban'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                    </svg>
                  </button>
                  <button @click="confirmDelete(user)" class="p-1.5 text-gray-400 hover:text-red-600 dark:hover:text-red-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700" title="Delete">
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
          Showing {{ (currentPage - 1) * perPage + 1 }} to {{ Math.min(currentPage * perPage, filteredUsers.length) }} of {{ filteredUsers.length }} users
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
        <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-lg w-full p-6 max-h-[90vh] overflow-y-auto">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
              {{ editingUser ? 'Edit User' : 'Create User' }}
            </h3>
            <button @click="closeModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <form @submit.prevent="saveUser" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name *</label>
              <input v-model="form.name" type="text" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email *</label>
              <input v-model="form.email" type="email" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
            </div>
            <div v-if="!editingUser">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Password *</label>
              <input v-model="form.password" type="password" :required="!editingUser" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Role *</label>
              <select v-model="form.role" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="viewer">Viewer</option>
                <option value="contributor">Contributor</option>
                <option value="analyst">Analyst</option>
                <option value="admin">Admin</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Organization</label>
              <input v-model="form.organization" type="text" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
            </div>
            <div class="flex items-center">
              <input v-model="form.send_welcome_email" type="checkbox" id="send_welcome" class="w-4 h-4 text-blue-600 bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded focus:ring-blue-500" />
              <label for="send_welcome" class="ml-2 text-sm text-gray-600 dark:text-gray-400">Send welcome email</label>
            </div>

            <div v-if="formError" class="p-3 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 rounded-lg text-sm">
              {{ formError }}
            </div>

            <div class="flex justify-end space-x-3 pt-4">
              <button type="button" @click="closeModal" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                Cancel
              </button>
              <button type="submit" :disabled="saving" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50 transition-colors">
                {{ saving ? 'Saving...' : (editingUser ? 'Update User' : 'Create User') }}
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
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">User Details</h3>
            <button @click="showViewModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <div v-if="viewingUser" class="space-y-6">
            <!-- User Header -->
            <div class="flex items-center space-x-4">
              <div class="w-16 h-16 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold text-2xl">
                {{ viewingUser.name.charAt(0).toUpperCase() }}
              </div>
              <div>
                <h4 class="text-xl font-semibold text-gray-900 dark:text-white">{{ viewingUser.name }}</h4>
                <p class="text-gray-500 dark:text-gray-400">{{ viewingUser.email }}</p>
              </div>
            </div>

            <!-- User Info Grid -->
            <div class="grid grid-cols-2 gap-4">
              <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">Role</p>
                <p class="text-sm font-medium text-gray-900 dark:text-white mt-1">{{ viewingUser.role }}</p>
              </div>
              <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">Status</p>
                <p class="text-sm font-medium text-gray-900 dark:text-white mt-1">{{ viewingUser.status }}</p>
              </div>
              <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">Organization</p>
                <p class="text-sm font-medium text-gray-900 dark:text-white mt-1">{{ viewingUser.organization || '-' }}</p>
              </div>
              <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">Joined</p>
                <p class="text-sm font-medium text-gray-900 dark:text-white mt-1">{{ formatDate(viewingUser.created_at) }}</p>
              </div>
            </div>

            <!-- Activity Stats -->
            <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
              <h5 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Activity Statistics</h5>
              <div class="grid grid-cols-3 gap-4">
                <div class="text-center">
                  <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ viewingUser.events_count || 0 }}</p>
                  <p class="text-sm text-gray-500 dark:text-gray-400">Events Created</p>
                </div>
                <div class="text-center">
                  <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ viewingUser.contributions || 0 }}</p>
                  <p class="text-sm text-gray-500 dark:text-gray-400">Contributions</p>
                </div>
                <div class="text-center">
                  <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ viewingUser.logins_count || 0 }}</p>
                  <p class="text-sm text-gray-500 dark:text-gray-400">Logins</p>
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
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Delete User</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
              Are you sure you want to delete <strong>{{ deletingUser?.name }}</strong>? This action cannot be undone.
            </p>
            <div class="flex justify-center space-x-3">
              <button @click="showDeleteModal = false" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                Cancel
              </button>
              <button @click="deleteUser" :disabled="deleting" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 disabled:opacity-50 transition-colors">
                {{ deleting ? 'Deleting...' : 'Delete User' }}
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
import type { User } from '@/types';

// State
const users = ref<User[]>([]);
const loading = ref(false);
const saving = ref(false);
const deleting = ref(false);
const currentPage = ref(1);
const perPage = ref(10);

// Modals
const showModal = ref(false);
const showViewModal = ref(false);
const showDeleteModal = ref(false);
const editingUser = ref<User | null>(null);
const viewingUser = ref<User | null>(null);
const deletingUser = ref<User | null>(null);
const formError = ref('');

// Form
const form = reactive({
  name: '',
  email: '',
  password: '',
  role: 'viewer',
  organization: '',
  send_welcome_email: true
});

// Filters
const filters = reactive({
  search: '',
  role: '',
  status: ''
});

// Computed
const filteredUsers = computed(() => {
  return users.value.filter(user => {
    const matchesSearch = !filters.search ||
      user.name.toLowerCase().includes(filters.search.toLowerCase()) ||
      user.email.toLowerCase().includes(filters.search.toLowerCase());
    const matchesRole = !filters.role || user.role === filters.role;
    const matchesStatus = !filters.status || (user as any).status === filters.status;
    return matchesSearch && matchesRole && matchesStatus;
  });
});

const totalPages = computed(() => Math.ceil(filteredUsers.value.length / perPage.value) || 1);

const paginatedUsers = computed(() => {
  const start = (currentPage.value - 1) * perPage.value;
  return filteredUsers.value.slice(start, start + perPage.value);
});

// Methods
const fetchUsers = async () => {
  loading.value = true;
  try {
    // Mock data for now - replace with actual API call
    users.value = [
      { id: 1, uuid: '1', name: 'John Admin', email: 'admin@example.com', role: 'admin', organization: 'OSINT Corp', created_at: '2024-01-15T10:00:00Z', updated_at: '2024-01-15T10:00:00Z', status: 'active', last_active_at: '2024-01-20T14:30:00Z' } as any,
      { id: 2, uuid: '2', name: 'Jane Analyst', email: 'analyst@example.com', role: 'analyst', organization: 'Intel Agency', created_at: '2024-01-10T10:00:00Z', updated_at: '2024-01-10T10:00:00Z', status: 'active', last_active_at: '2024-01-19T09:15:00Z' } as any,
      { id: 3, uuid: '3', name: 'Bob Contributor', email: 'bob@example.com', role: 'contributor', created_at: '2024-01-05T10:00:00Z', updated_at: '2024-01-05T10:00:00Z', status: 'active', last_active_at: '2024-01-18T16:45:00Z' } as any,
      { id: 4, uuid: '4', name: 'Alice Viewer', email: 'alice@example.com', role: 'viewer', organization: 'Research Lab', created_at: '2024-01-01T10:00:00Z', updated_at: '2024-01-01T10:00:00Z', status: 'inactive', last_active_at: '2024-01-10T11:00:00Z' } as any,
      { id: 5, uuid: '5', name: 'Charlie Banned', email: 'charlie@example.com', role: 'viewer', created_at: '2023-12-15T10:00:00Z', updated_at: '2023-12-15T10:00:00Z', status: 'banned', last_active_at: '2023-12-20T08:00:00Z' } as any,
    ];
  } catch (error) {
    console.error('Failed to fetch users:', error);
  } finally {
    loading.value = false;
  }
};

const openCreateModal = () => {
  editingUser.value = null;
  Object.assign(form, {
    name: '',
    email: '',
    password: '',
    role: 'viewer',
    organization: '',
    send_welcome_email: true
  });
  formError.value = '';
  showModal.value = true;
};

const editUser = (user: User) => {
  editingUser.value = user;
  Object.assign(form, {
    name: user.name,
    email: user.email,
    password: '',
    role: user.role,
    organization: user.organization || '',
    send_welcome_email: false
  });
  formError.value = '';
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  editingUser.value = null;
};

const saveUser = async () => {
  saving.value = true;
  formError.value = '';
  try {
    if (editingUser.value) {
      // Update user
      const index = users.value.findIndex(u => u.id === editingUser.value!.id);
      if (index !== -1) {
        users.value[index] = { ...users.value[index], ...form } as User;
      }
    } else {
      // Create user
      const newUser = {
        id: users.value.length + 1,
        uuid: String(users.value.length + 1),
        ...form,
        created_at: new Date().toISOString(),
        updated_at: new Date().toISOString(),
        status: 'active',
        last_active_at: null
      } as any;
      users.value.unshift(newUser);
    }
    closeModal();
  } catch (error: any) {
    formError.value = error?.message || 'Failed to save user';
  } finally {
    saving.value = false;
  }
};

const viewUser = (user: User) => {
  viewingUser.value = user;
  showViewModal.value = true;
};

const confirmDelete = (user: User) => {
  deletingUser.value = user;
  showDeleteModal.value = true;
};

const deleteUser = async () => {
  if (!deletingUser.value) return;
  deleting.value = true;
  try {
    users.value = users.value.filter(u => u.id !== deletingUser.value!.id);
    showDeleteModal.value = false;
    deletingUser.value = null;
  } catch (error) {
    console.error('Failed to delete user:', error);
  } finally {
    deleting.value = false;
  }
};

const toggleBan = async (user: User) => {
  const newStatus = (user as any).status === 'banned' ? 'active' : 'banned';
  const index = users.value.findIndex(u => u.id === user.id);
  if (index !== -1) {
    (users.value[index] as any).status = newStatus;
  }
};

const clearFilters = () => {
  filters.search = '';
  filters.role = '';
  filters.status = '';
  currentPage.value = 1;
};

const exportUsers = () => {
  const csv = [
    ['Name', 'Email', 'Role', 'Organization', 'Status', 'Joined'].join(','),
    ...filteredUsers.value.map(u =>
      [u.name, u.email, u.role, u.organization || '', (u as any).status, u.created_at].join(',')
    )
  ].join('\n');

  const blob = new Blob([csv], { type: 'text/csv' });
  const url = window.URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = 'users.csv';
  a.click();
};

const formatDate = (dateString: string | null | undefined) => {
  if (!dateString) return '-';
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};

const getRoleBadgeClass = (role: string) => {
  switch (role) {
    case 'admin': return 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400';
    case 'analyst': return 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400';
    case 'contributor': return 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400';
    default: return 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-400';
  }
};

const getStatusBadgeClass = (status: string | undefined) => {
  switch (status) {
    case 'active': return 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400';
    case 'inactive': return 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400';
    case 'banned': return 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400';
    default: return 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-400';
  }
};

onMounted(() => {
  fetchUsers();
});
</script>
