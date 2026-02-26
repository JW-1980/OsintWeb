<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { useApi } from '@/composables/useApi';

import ProfileHeader from '@/components/profile/ProfileHeader.vue';
import ProfileEditTab from '@/components/profile/ProfileEditTab.vue';
import ActivityTab from '@/components/profile/ActivityTab.vue';
import SecurityTab from '@/components/profile/SecurityTab.vue';
import DataPrivacyTab from '@/components/profile/DataPrivacyTab.vue';

const authStore = useAuthStore();
const api = useApi();

const activeTab = ref<'profile' | 'activity' | 'security' | 'data'>('profile');
const loading = ref(false);
const errorMessage = ref('');
const successMessage = ref('');

const profile = reactive({
  // Basic Info
  name: '',
  email: '',
  phone: '',
  avatarUrl: '',
  // Professional Info
  jobTitle: '',
  organization: '',
  department: '',
  // Location & Availability
  country: '',
  timezone: '',
  // Online Presence
  website: '',
  twitterHandle: '',
  linkedinUrl: '',
  // Expertise
  expertiseAreas: [] as string[],
  languages: '',
  // Preferences
  publicProfile: true,
  // Bio
  bio: ''
});

interface Activity {
  type: string;
  description: string;
  icon: string;
  ip_address: string;
  created_at: string;
}

interface Session {
  uuid: string;
  device_type: string;
  browser: string;
  platform: string;
  ip_address: string;
  location: string | null;
  is_current: boolean;
  last_activity_at: string;
  created_at: string;
}

interface Achievement {
  id: number;
  name: string;
  description: string;
  icon: string;
  earned: boolean;
  earnedDate: string | null;
}

const activityHistory = ref<Activity[]>([]);

const achievements = ref<Achievement[]>([]);

const stats = ref({
  eventsCreated: 0,
  eventsVerified: 0,
  contributions: 0,
  accuracy: 0,
  rank: 'Member',
  memberSince: ''
});

const sessions = ref<Session[]>([]);

const showMessage = (message: string, isError: boolean = false) => {
  if (isError) {
    errorMessage.value = message;
    successMessage.value = '';
  } else {
    successMessage.value = message;
    errorMessage.value = '';
  }
  setTimeout(() => {
    errorMessage.value = '';
    successMessage.value = '';
  }, 5000);
};

const loadProfile = async () => {
  loading.value = true;
  try {
    const response = await api.get<{ data: any }>('/account/profile');
    const data = response.data;

    profile.name = data.name || '';
    profile.email = data.email || '';
    profile.avatarUrl = data.avatar_url || '';
    profile.bio = data.bio || '';
    profile.organization = data.organization || '';
    profile.country = data.location || '';
    profile.timezone = data.timezone || '';
    profile.website = data.website || '';

    stats.value.memberSince = data.created_at ? new Date(data.created_at).toLocaleDateString() : '';
    stats.value.rank = data.role || 'Member';
  } catch (error: any) {
    console.error('Failed to load profile:', error);
    // Fall back to auth store data
    if (authStore.user) {
      profile.name = authStore.user.name || '';
      profile.email = authStore.user.email || '';
      profile.avatarUrl = authStore.user.avatar_url || '';
    }
  } finally {
    loading.value = false;
  }
};

const loadSessions = async () => {
  try {
    const response = await api.get<{ data: Session[] }>('/account/sessions');
    sessions.value = response.data;
  } catch (error) {
    console.error('Failed to load sessions:', error);
  }
};

const loadActivity = async () => {
  try {
    const response = await api.get<{ data: Activity[] }>('/account/activity');
    activityHistory.value = response.data;
  } catch (error) {
    console.error('Failed to load activity:', error);
  }
};

const loadAchievements = async () => {
  try {
    const response = await api.get<{ data: Achievement[] }>('/achievements/user');
    achievements.value = response.data;
  } catch (error) {
    // Use default achievements if endpoint not available
    achievements.value = [
      { id: 1, name: 'First Contribution', description: 'Created your first event', icon: 'star', earned: false, earnedDate: null },
      { id: 2, name: 'Verified Reporter', description: 'Had 10 events verified', icon: 'check', earned: false, earnedDate: null },
      { id: 3, name: 'Consistent Contributor', description: 'Contributed for 30 consecutive days', icon: 'calendar', earned: false, earnedDate: null },
      { id: 4, name: 'Expert Analyst', description: 'Achieved 100 verified events', icon: 'award', earned: false, earnedDate: null },
      { id: 5, name: 'Community Leader', description: 'Verified 50 community events', icon: 'users', earned: false, earnedDate: null }
    ];
  }
};

const updateProfile = (newProfile: any) => {
  Object.assign(profile, newProfile);
};

onMounted(async () => {
  loading.value = true;
  await Promise.all([
    loadProfile(),
    loadSessions(),
    loadActivity(),
    loadAchievements(),
  ]);
  loading.value = false;
});
</script>

<template>
  <div class="p-6 bg-gray-50 dark:bg-gray-900 min-h-screen">
    <div class="max-w-5xl mx-auto">
      <!-- Success/Error Messages -->
      <div v-if="successMessage" class="mb-4 p-4 bg-green-100 dark:bg-green-900/30 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-400 rounded-lg flex items-center">
        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
        </svg>
        {{ successMessage }}
      </div>
      <div v-if="errorMessage" class="mb-4 p-4 bg-red-100 dark:bg-red-900/30 border border-red-400 dark:border-red-700 text-red-700 dark:text-red-400 rounded-lg flex items-center">
        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
        </svg>
        {{ errorMessage }}
      </div>

      <!-- Profile Header -->
      <ProfileHeader
        :profile="profile"
        :stats="stats"
        @message="showMessage"
        @update:profile="updateProfile"
      />

      <!-- Tabs -->
      <div class="flex flex-wrap gap-2 mb-6">
        <button
          v-for="tab in [
            { id: 'profile', name: 'Profile' },
            { id: 'activity', name: 'Activity' },
            { id: 'security', name: 'Security' },
            { id: 'data', name: 'Data & Privacy' }
          ]"
          :key="tab.id"
          @click="activeTab = tab.id as any"
          :class="[
            'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
            activeTab === tab.id
              ? 'bg-blue-600 text-white'
              : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
          ]"
        >
          {{ tab.name }}
        </button>
      </div>

      <!-- Profile Tab -->
      <ProfileEditTab
        v-if="activeTab === 'profile'"
        :profile="profile"
        :achievements="achievements"
        @message="showMessage"
        @update:profile="updateProfile"
      />

      <!-- Activity Tab -->
      <ActivityTab
        v-if="activeTab === 'activity'"
        :activityHistory="activityHistory"
      />

      <!-- Security Tab -->
      <SecurityTab
        v-if="activeTab === 'security'"
        :sessions="sessions"
        @message="showMessage"
        @refreshSessions="loadSessions"
      />

      <!-- Data & Privacy Tab -->
      <DataPrivacyTab
        v-if="activeTab === 'data'"
        @message="showMessage"
      />

    </div>
  </div>
</template>
