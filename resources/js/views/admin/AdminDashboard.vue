<template>
  <div class="p-6">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Admin Dashboard</h1>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <div class="bg-white shadow rounded-lg p-6">
        <h3 class="text-sm font-medium text-gray-500">Total Users</h3>
        <p class="text-3xl font-bold text-gray-900">--</p>
      </div>
      <div class="bg-white shadow rounded-lg p-6">
        <h3 class="text-sm font-medium text-gray-500">Total Events</h3>
        <p class="text-3xl font-bold text-gray-900">--</p>
      </div>
      <div class="bg-white shadow rounded-lg p-6">
        <h3 class="text-sm font-medium text-gray-500">Active Conflicts</h3>
        <p class="text-3xl font-bold text-gray-900">--</p>
      </div>
      <div class="bg-white shadow rounded-lg p-6">
        <h3 class="text-sm font-medium text-gray-500">Equipment Items</h3>
        <p class="text-3xl font-bold text-gray-900">--</p>
      </div>
    </div>
    <div class="bg-white shadow rounded-lg p-6">
      <h2 class="text-lg font-semibold text-gray-900 mb-4">Quick Links</h2>
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <router-link to="/admin/users" class="p-4 bg-gray-50 rounded-lg hover:bg-gray-100 text-center">
          Users
        </router-link>
        <router-link to="/admin/actors" class="p-4 bg-gray-50 rounded-lg hover:bg-gray-100 text-center">
          Actors
        </router-link>
        <router-link to="/admin/conflicts" class="p-4 bg-gray-50 rounded-lg hover:bg-gray-100 text-center">
          Conflicts
        </router-link>
        <router-link to="/admin/audit-logs" class="p-4 bg-gray-50 rounded-lg hover:bg-gray-100 text-center">
          Audit Logs
        </router-link>
      </div>
    </div>
    <router-view />
  </div>
</template>

<script setup lang="ts">
// Admin Dashboard component
</script>
