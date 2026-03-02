<script setup lang="ts">
import { computed } from 'vue';
import { useApi } from '@/composables/useApi';

const props = defineProps<{
  profile: {
    name: string;
    email: string;
    avatarUrl: string;
    [key: string]: any;
  };
  stats: {
    eventsCreated: number;
    eventsVerified: number;
    contributions: number;
    accuracy: number;
    rank: string;
    memberSince: string;
  };
}>();

const emit = defineEmits<{
  (e: 'message', message: string, isError?: boolean): void;
  (e: 'update:profile', profile: any): void;
}>();

const api = useApi();

const userInitials = computed(() => {
  const name = props.profile.name || 'User';
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
});

const uploadAvatar = () => {
  const input = document.createElement('input');
  input.type = 'file';
  input.accept = 'image/*';
  input.onchange = async (e) => {
    const file = (e.target as HTMLInputElement).files?.[0];
    if (file) {
      try {
        const formData = new FormData();
        formData.append('avatar', file);

        const response = await api.post<{ data: { avatar_url: string } }>('/account/avatar/upload', formData, {
          headers: { 'Content-Type': 'multipart/form-data' }
        });

        // Update profile with new avatar URL
        emit('update:profile', { ...props.profile, avatarUrl: response.data.avatar_url });
        emit('message', 'Avatar uploaded successfully!');
      } catch (error: any) {
        emit('message', error.message || 'Failed to upload avatar', true);
      }
    }
  };
  input.click();
};
</script>

<template>
  <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
    <div class="flex flex-col md:flex-row items-center md:items-start space-y-4 md:space-y-0 md:space-x-6">
      <div class="relative">
        <div
          v-if="profile.avatarUrl"
          class="w-24 h-24 rounded-full bg-cover bg-center"
          :style="{ backgroundImage: `url(${profile.avatarUrl})` }"
        ></div>
        <div
          v-else
          class="w-24 h-24 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white text-2xl font-bold"
        >
          {{ userInitials }}
        </div>
        <button
          @click="uploadAvatar"
          class="absolute bottom-0 right-0 w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center hover:bg-blue-700"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
          </svg>
        </button>
      </div>
      <div class="flex-1 text-center md:text-left">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ profile.name || 'User' }}</h1>
        <p class="text-gray-500 dark:text-gray-400">{{ profile.email }}</p>
        <div class="flex flex-wrap justify-center md:justify-start gap-4 mt-3">
          <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300">
            {{ stats.rank }}
          </span>
          <span class="text-sm text-gray-500 dark:text-gray-400">
            Member since {{ stats.memberSince }}
          </span>
        </div>
      </div>
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="text-center p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
          <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.eventsCreated }}</p>
          <p class="text-xs text-gray-500 dark:text-gray-400">Events</p>
        </div>
        <div class="text-center p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
          <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.eventsVerified }}</p>
          <p class="text-xs text-gray-500 dark:text-gray-400">Verified</p>
        </div>
        <div class="text-center p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
          <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.contributions }}</p>
          <p class="text-xs text-gray-500 dark:text-gray-400">Contributions</p>
        </div>
        <div class="text-center p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
          <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ stats.accuracy }}%</p>
          <p class="text-xs text-gray-500 dark:text-gray-400">Accuracy</p>
        </div>
      </div>
    </div>
  </div>
</template>
