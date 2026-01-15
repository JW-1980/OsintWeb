<template>
  <div class="p-6">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Equipment Details</h1>
    <div class="bg-white shadow rounded-lg p-6">
      <p class="text-gray-600">Equipment details for ID: {{ id }}</p>
    </div>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  id: string;
}>();
</script>
