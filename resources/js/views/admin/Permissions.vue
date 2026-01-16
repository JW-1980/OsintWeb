<template>
  <div class="mt-6 space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Permission Management</h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">View and manage system permissions</p>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">Total Permissions</p>
        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ permissions.length }}</p>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">Permission Groups</p>
        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ permissionGroups.length }}</p>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">Roles Using Permissions</p>
        <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ totalRolesCount }}</p>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">System Permissions</p>
        <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ systemPermissionsCount }}</p>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search</label>
          <input v-model="filters.search" type="text" placeholder="Search permissions..." class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Group</label>
          <select v-model="filters.group" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            <option value="">All Groups</option>
            <option v-for="group in permissionGroups" :key="group" :value="group">{{ group }}</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type</label>
          <select v-model="filters.type" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            <option value="">All Types</option>
            <option value="system">System</option>
            <option value="custom">Custom</option>
          </select>
        </div>
        <div class="flex items-end">
          <button @click="clearFilters" class="w-full px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
            Clear Filters
          </button>
        </div>
      </div>
    </div>

    <!-- View Toggle -->
    <div class="flex items-center justify-end space-x-2">
      <button @click="viewMode = 'grouped'" :class="viewMode === 'grouped' ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700'" class="px-3 py-1.5 text-sm font-medium rounded-lg transition-colors">
        Grouped View
      </button>
      <button @click="viewMode = 'table'" :class="viewMode === 'table' ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700'" class="px-3 py-1.5 text-sm font-medium rounded-lg transition-colors">
        Table View
      </button>
    </div>

    <!-- Grouped View -->
    <div v-if="viewMode === 'grouped'" class="space-y-4">
      <div v-for="group in filteredGroupedPermissions" :key="group.name" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
          <div class="flex items-center space-x-3">
            <div :class="getGroupColorClass(group.name)" class="w-10 h-10 rounded-lg flex items-center justify-center text-white">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
              </svg>
            </div>
            <div>
              <h3 class="font-semibold text-gray-900 dark:text-white">{{ group.name }}</h3>
              <p class="text-sm text-gray-500 dark:text-gray-400">{{ group.permissions.length }} permissions</p>
            </div>
          </div>
          <button @click="toggleGroupExpand(group.name)" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600">
            <svg :class="{ 'rotate-180': expandedGroups.includes(group.name) }" class="w-5 h-5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>
        </div>

        <div v-if="expandedGroups.includes(group.name)" class="divide-y divide-gray-200 dark:divide-gray-700">
          <div v-for="permission in group.permissions" :key="permission.id" class="px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
            <div class="flex items-center justify-between">
              <div class="flex-1">
                <div class="flex items-center space-x-2">
                  <span class="text-sm font-medium text-gray-900 dark:text-white">{{ permission.name }}</span>
                  <span v-if="permission.is_system" class="px-2 py-0.5 text-xs font-medium rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400">System</span>
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ permission.slug }}</p>
                <p v-if="permission.description" class="text-xs text-gray-400 dark:text-gray-500 mt-1">{{ permission.description }}</p>
              </div>
              <div class="flex items-center space-x-4">
                <div class="text-right">
                  <p class="text-sm font-medium text-gray-900 dark:text-white">{{ permission.roles_count }} roles</p>
                  <p class="text-xs text-gray-500 dark:text-gray-400">have this permission</p>
                </div>
                <button @click="viewPermission(permission)" class="p-2 text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Table View -->
    <div v-if="viewMode === 'table'" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50 dark:bg-gray-700/50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Permission</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Slug</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Group</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Type</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Roles</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            <tr v-for="permission in paginatedPermissions" :key="permission.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
              <td class="px-6 py-4 whitespace-nowrap">
                <div>
                  <div class="text-sm font-medium text-gray-900 dark:text-white">{{ permission.name }}</div>
                  <div v-if="permission.description" class="text-xs text-gray-500 dark:text-gray-400 truncate max-w-xs">{{ permission.description }}</div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <code class="text-xs px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded text-gray-700 dark:text-gray-300">{{ permission.slug }}</code>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getGroupBadgeClass(permission.group)" class="px-2.5 py-1 text-xs font-medium rounded-full">
                  {{ permission.group }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="permission.is_system ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400' : 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400'" class="px-2.5 py-1 text-xs font-medium rounded-full">
                  {{ permission.is_system ? 'System' : 'Custom' }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <span class="text-sm text-gray-900 dark:text-white">{{ permission.roles_count }}</span>
                  <div v-if="permission.roles && permission.roles.length > 0" class="flex -space-x-1 ml-2">
                    <div v-for="role in permission.roles.slice(0, 3)" :key="role.id" :class="getRoleColorClass(role.color)" class="w-5 h-5 rounded-full text-white text-xs flex items-center justify-center border border-white dark:border-gray-800" :title="role.name">
                      {{ role.name.charAt(0) }}
                    </div>
                    <div v-if="permission.roles.length > 3" class="w-5 h-5 rounded-full bg-gray-400 text-white text-xs flex items-center justify-center border border-white dark:border-gray-800">
                      +{{ permission.roles.length - 3 }}
                    </div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right">
                <button @click="viewPermission(permission)" class="p-1.5 text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700" title="View Details">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between">
        <div class="text-sm text-gray-500 dark:text-gray-400">
          Showing {{ (currentPage - 1) * perPage + 1 }} to {{ Math.min(currentPage * perPage, filteredPermissions.length) }} of {{ filteredPermissions.length }} permissions
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

    <!-- View Permission Modal -->
    <div v-if="showViewModal" class="fixed inset-0 z-50 overflow-y-auto" @click.self="showViewModal = false">
      <div class="flex items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-black/50" @click="showViewModal = false"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-2xl w-full p-6 max-h-[90vh] overflow-y-auto">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Permission Details</h3>
            <button @click="showViewModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <div v-if="viewingPermission" class="space-y-6">
            <div class="flex items-center space-x-4">
              <div :class="getGroupColorClass(viewingPermission.group)" class="w-14 h-14 rounded-xl flex items-center justify-center text-white">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
              </div>
              <div>
                <h4 class="text-xl font-semibold text-gray-900 dark:text-white">{{ viewingPermission.name }}</h4>
                <code class="text-sm text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded">{{ viewingPermission.slug }}</code>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">Group</p>
                <p class="text-sm font-medium text-gray-900 dark:text-white mt-1">{{ viewingPermission.group }}</p>
              </div>
              <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">Type</p>
                <p class="text-sm font-medium text-gray-900 dark:text-white mt-1">{{ viewingPermission.is_system ? 'System Permission' : 'Custom Permission' }}</p>
              </div>
            </div>

            <div v-if="viewingPermission.description">
              <h5 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">Description</h5>
              <p class="text-sm text-gray-600 dark:text-gray-400">{{ viewingPermission.description }}</p>
            </div>

            <div>
              <h5 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Roles with this permission ({{ viewingPermission.roles_count }})</h5>
              <div class="space-y-2">
                <div v-for="role in viewingPermission.roles" :key="role.id" class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                  <div class="flex items-center space-x-3">
                    <div :class="getRoleColorClass(role.color)" class="w-8 h-8 rounded-lg flex items-center justify-center text-white text-sm font-bold">
                      {{ role.name.charAt(0) }}
                    </div>
                    <div>
                      <p class="text-sm font-medium text-gray-900 dark:text-white">{{ role.name }}</p>
                      <p class="text-xs text-gray-500 dark:text-gray-400">{{ role.users_count }} users</p>
                    </div>
                  </div>
                  <span :class="role.is_system ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400' : 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400'" class="px-2 py-0.5 text-xs font-medium rounded-full">
                    {{ role.is_system ? 'System' : 'Custom' }}
                  </span>
                </div>
                <div v-if="viewingPermission.roles?.length === 0" class="text-center py-4 text-sm text-gray-500 dark:text-gray-400">
                  No roles have this permission assigned.
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue';

interface Role {
  id: number;
  name: string;
  slug: string;
  color: string;
  is_system: boolean;
  users_count: number;
}

interface Permission {
  id: number;
  name: string;
  slug: string;
  group: string;
  description?: string;
  is_system: boolean;
  roles_count: number;
  roles?: Role[];
  created_at: string;
  updated_at: string;
}

// State
const permissions = ref<Permission[]>([]);
const _loading = ref(false);
const currentPage = ref(1);
const perPage = ref(15);
const viewMode = ref<'grouped' | 'table'>('grouped');
const expandedGroups = ref<string[]>([]);

// Modals
const showViewModal = ref(false);
const viewingPermission = ref<Permission | null>(null);

// Filters
const filters = reactive({
  search: '',
  group: '',
  type: ''
});

// Computed
const permissionGroups = computed(() => {
  return [...new Set(permissions.value.map(p => p.group))];
});

const totalRolesCount = computed(() => {
  const uniqueRoleIds = new Set<number>();
  permissions.value.forEach(p => {
    p.roles?.forEach(r => uniqueRoleIds.add(r.id));
  });
  return uniqueRoleIds.size;
});

const systemPermissionsCount = computed(() => permissions.value.filter(p => p.is_system).length);

const filteredPermissions = computed(() => {
  return permissions.value.filter(permission => {
    const matchesSearch = !filters.search ||
      permission.name.toLowerCase().includes(filters.search.toLowerCase()) ||
      permission.slug.toLowerCase().includes(filters.search.toLowerCase());
    const matchesGroup = !filters.group || permission.group === filters.group;
    const matchesType = !filters.type ||
      (filters.type === 'system' && permission.is_system) ||
      (filters.type === 'custom' && !permission.is_system);
    return matchesSearch && matchesGroup && matchesType;
  });
});

const filteredGroupedPermissions = computed(() => {
  const groups: Record<string, Permission[]> = {};
  filteredPermissions.value.forEach(perm => {
    if (!groups[perm.group]) {
      groups[perm.group] = [];
    }
    groups[perm.group]!.push(perm);
  });
  return Object.entries(groups).map(([name, permissions]) => ({
    name,
    permissions
  })).sort((a, b) => a.name.localeCompare(b.name));
});

const totalPages = computed(() => Math.ceil(filteredPermissions.value.length / perPage.value) || 1);

const paginatedPermissions = computed(() => {
  const start = (currentPage.value - 1) * perPage.value;
  return filteredPermissions.value.slice(start, start + perPage.value);
});

// Methods
const fetchPermissions = async () => {
  _loading.value = true;
  try {
    const roles: Role[] = [
      { id: 1, name: 'Administrator', slug: 'admin', color: 'red', is_system: true, users_count: 3 },
      { id: 2, name: 'Analyst', slug: 'analyst', color: 'purple', is_system: true, users_count: 12 },
      { id: 3, name: 'Contributor', slug: 'contributor', color: 'blue', is_system: true, users_count: 45 },
      { id: 4, name: 'Viewer', slug: 'viewer', color: 'gray', is_system: true, users_count: 128 },
    ];

    permissions.value = [
      { id: 1, name: 'View Events', slug: 'events.view', group: 'Events', description: 'View all events on the map and in lists', is_system: true, roles_count: 4, roles: roles, created_at: '2024-01-01', updated_at: '2024-01-01' },
      { id: 2, name: 'Create Events', slug: 'events.create', group: 'Events', description: 'Create new events and add them to the map', is_system: true, roles_count: 3, roles: roles.slice(0, 3), created_at: '2024-01-01', updated_at: '2024-01-01' },
      { id: 3, name: 'Edit Events', slug: 'events.edit', group: 'Events', description: 'Modify existing event information', is_system: true, roles_count: 3, roles: roles.slice(0, 3), created_at: '2024-01-01', updated_at: '2024-01-01' },
      { id: 4, name: 'Delete Events', slug: 'events.delete', group: 'Events', description: 'Remove events from the system', is_system: true, roles_count: 2, roles: roles.slice(0, 2), created_at: '2024-01-01', updated_at: '2024-01-01' },
      { id: 5, name: 'Verify Events', slug: 'events.verify', group: 'Events', description: 'Mark events as verified after review', is_system: true, roles_count: 2, roles: roles.slice(0, 2), created_at: '2024-01-01', updated_at: '2024-01-01' },
      { id: 6, name: 'View Users', slug: 'users.view', group: 'Users', description: 'View user profiles and information', is_system: true, roles_count: 2, roles: roles.slice(0, 2), created_at: '2024-01-01', updated_at: '2024-01-01' },
      { id: 7, name: 'Create Users', slug: 'users.create', group: 'Users', description: 'Create new user accounts', is_system: true, roles_count: 1, roles: [roles[0]!], created_at: '2024-01-01', updated_at: '2024-01-01' },
      { id: 8, name: 'Edit Users', slug: 'users.edit', group: 'Users', description: 'Modify user account information', is_system: true, roles_count: 1, roles: [roles[0]!], created_at: '2024-01-01', updated_at: '2024-01-01' },
      { id: 9, name: 'Delete Users', slug: 'users.delete', group: 'Users', description: 'Remove user accounts', is_system: true, roles_count: 1, roles: [roles[0]!], created_at: '2024-01-01', updated_at: '2024-01-01' },
      { id: 10, name: 'View Actors', slug: 'actors.view', group: 'Actors', description: 'View actor profiles', is_system: true, roles_count: 4, roles: roles, created_at: '2024-01-01', updated_at: '2024-01-01' },
      { id: 11, name: 'Manage Actors', slug: 'actors.manage', group: 'Actors', description: 'Create, edit, and delete actors', is_system: true, roles_count: 2, roles: roles.slice(0, 2), created_at: '2024-01-01', updated_at: '2024-01-01' },
      { id: 12, name: 'View Reports', slug: 'reports.view', group: 'Reports', description: 'View generated reports', is_system: true, roles_count: 3, roles: roles.slice(0, 3), created_at: '2024-01-01', updated_at: '2024-01-01' },
      { id: 13, name: 'Generate Reports', slug: 'reports.generate', group: 'Reports', description: 'Create new reports', is_system: true, roles_count: 2, roles: roles.slice(0, 2), created_at: '2024-01-01', updated_at: '2024-01-01' },
      { id: 14, name: 'Export Reports', slug: 'reports.export', group: 'Reports', description: 'Export reports to PDF/DOCX', is_system: true, roles_count: 2, roles: roles.slice(0, 2), created_at: '2024-01-01', updated_at: '2024-01-01' },
      { id: 15, name: 'View Analytics', slug: 'analytics.view', group: 'Analytics', description: 'View analytics dashboards', is_system: true, roles_count: 3, roles: roles.slice(0, 3), created_at: '2024-01-01', updated_at: '2024-01-01' },
      { id: 16, name: 'Export Analytics', slug: 'analytics.export', group: 'Analytics', description: 'Export analytics data', is_system: true, roles_count: 2, roles: roles.slice(0, 2), created_at: '2024-01-01', updated_at: '2024-01-01' },
      { id: 17, name: 'Admin Panel Access', slug: 'admin.access', group: 'Administration', description: 'Access the admin panel', is_system: true, roles_count: 1, roles: [roles[0]!], created_at: '2024-01-01', updated_at: '2024-01-01' },
      { id: 18, name: 'Manage Roles', slug: 'roles.manage', group: 'Administration', description: 'Create and manage roles', is_system: true, roles_count: 1, roles: [roles[0]!], created_at: '2024-01-01', updated_at: '2024-01-01' },
      { id: 19, name: 'System Settings', slug: 'settings.manage', group: 'Administration', description: 'Manage system settings', is_system: true, roles_count: 1, roles: [roles[0]!], created_at: '2024-01-01', updated_at: '2024-01-01' },
      { id: 20, name: 'View Audit Logs', slug: 'audit.view', group: 'Administration', description: 'View audit log entries', is_system: true, roles_count: 1, roles: [roles[0]!], created_at: '2024-01-01', updated_at: '2024-01-01' },
    ];

    // Expand first group by default
    if (permissionGroups.value.length > 0 && permissionGroups.value[0]) {
      expandedGroups.value = [permissionGroups.value[0]];
    }
  } catch (error) {
    console.error('Failed to fetch permissions:', error);
  } finally {
    _loading.value = false;
  }
};

const clearFilters = () => {
  filters.search = '';
  filters.group = '';
  filters.type = '';
  currentPage.value = 1;
};

const toggleGroupExpand = (groupName: string) => {
  const index = expandedGroups.value.indexOf(groupName);
  if (index === -1) {
    expandedGroups.value.push(groupName);
  } else {
    expandedGroups.value.splice(index, 1);
  }
};

const viewPermission = (permission: Permission) => {
  viewingPermission.value = permission;
  showViewModal.value = true;
};

const getGroupColorClass = (group: string) => {
  const colors: Record<string, string> = {
    Events: 'bg-red-500',
    Users: 'bg-blue-500',
    Actors: 'bg-purple-500',
    Reports: 'bg-green-500',
    Analytics: 'bg-yellow-500',
    Administration: 'bg-indigo-500'
  };
  return colors[group] || 'bg-gray-500';
};

const getGroupBadgeClass = (group: string) => {
  const colors: Record<string, string> = {
    Events: 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400',
    Users: 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400',
    Actors: 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400',
    Reports: 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400',
    Analytics: 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400',
    Administration: 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400'
  };
  return colors[group] || 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-400';
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

onMounted(() => {
  fetchPermissions();
});
</script>
