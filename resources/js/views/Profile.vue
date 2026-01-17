<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useApi } from '@/composables/useApi';

const router = useRouter();
const authStore = useAuthStore();
const api = useApi();

const activeTab = ref<'profile' | 'activity' | 'security' | 'data'>('profile');
const loading = ref(false);
const saving = ref(false);
const showDeleteModal = ref(false);
const showTwoFactorModal = ref(false);
const errorMessage = ref('');
const successMessage = ref('');
const deletePassword = ref('');

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

const passwordForm = reactive({
  current: '',
  new: '',
  confirm: ''
});

const twoFactor = reactive({
  enabled: false,
  qrCode: '',
  verificationCode: ''
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

// Expertise area options for OSINT analysts
const expertiseOptions = [
  'Conflict Analysis',
  'Military Equipment',
  'Geolocation',
  'Social Media Intelligence',
  'Satellite Imagery',
  'Open Source Research',
  'Data Verification',
  'Disinformation Analysis',
  'Crisis Monitoring',
  'Maritime Tracking',
  'Aviation Tracking',
  'Cybersecurity'
];

// Common timezones
const timezoneOptions = [
  'UTC-12:00', 'UTC-11:00', 'UTC-10:00', 'UTC-09:00', 'UTC-08:00', 'UTC-07:00',
  'UTC-06:00', 'UTC-05:00', 'UTC-04:00', 'UTC-03:00', 'UTC-02:00', 'UTC-01:00',
  'UTC+00:00', 'UTC+01:00', 'UTC+02:00', 'UTC+03:00', 'UTC+04:00', 'UTC+05:00',
  'UTC+06:00', 'UTC+07:00', 'UTC+08:00', 'UTC+09:00', 'UTC+10:00', 'UTC+11:00', 'UTC+12:00'
];

// Countries list
const countryOptions = [
  'Afghanistan', 'Albania', 'Algeria', 'Argentina', 'Australia', 'Austria', 'Bangladesh',
  'Belgium', 'Brazil', 'Bulgaria', 'Canada', 'Chile', 'China', 'Colombia', 'Croatia',
  'Czech Republic', 'Denmark', 'Egypt', 'Estonia', 'Finland', 'France', 'Germany', 'Greece',
  'Hungary', 'India', 'Indonesia', 'Iran', 'Iraq', 'Ireland', 'Israel', 'Italy', 'Japan',
  'Kenya', 'Latvia', 'Lithuania', 'Malaysia', 'Mexico', 'Morocco', 'Netherlands', 'New Zealand',
  'Nigeria', 'Norway', 'Pakistan', 'Peru', 'Philippines', 'Poland', 'Portugal', 'Romania',
  'Russia', 'Saudi Arabia', 'Singapore', 'Slovakia', 'Slovenia', 'South Africa', 'South Korea',
  'Spain', 'Sweden', 'Switzerland', 'Taiwan', 'Thailand', 'Turkey', 'Ukraine', 'United Arab Emirates',
  'United Kingdom', 'United States', 'Venezuela', 'Vietnam'
];

const toggleExpertise = (area: string) => {
  const index = profile.expertiseAreas.indexOf(area);
  if (index === -1) {
    profile.expertiseAreas.push(area);
  } else {
    profile.expertiseAreas.splice(index, 1);
  }
};

const userInitials = computed(() => {
  const name = profile.name || 'User';
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
});

const isPasswordValid = computed(() => {
  return passwordForm.current &&
    passwordForm.new &&
    passwordForm.new.length >= 8 &&
    passwordForm.new === passwordForm.confirm;
});

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

const saveProfile = async () => {
  saving.value = true;
  errorMessage.value = '';
  try {
    await api.put('/account/profile', {
      name: profile.name,
      bio: profile.bio,
      organization: profile.organization,
      location: profile.country,
      website: profile.website,
      timezone: profile.timezone,
    });
    showMessage('Profile updated successfully!');
  } catch (error: any) {
    showMessage(error.message || 'Failed to save profile', true);
  } finally {
    saving.value = false;
  }
};

const changePassword = async () => {
  if (!isPasswordValid.value) return;

  saving.value = true;
  errorMessage.value = '';
  try {
    await api.put('/account/password', {
      current_password: passwordForm.current,
      password: passwordForm.new,
      password_confirmation: passwordForm.confirm,
    });
    passwordForm.current = '';
    passwordForm.new = '';
    passwordForm.confirm = '';
    showMessage('Password changed successfully!');
  } catch (error: any) {
    showMessage(error.message || 'Failed to change password', true);
  } finally {
    saving.value = false;
  }
};

const uploadAvatar = () => {
  const input = document.createElement('input');
  input.type = 'file';
  input.accept = 'image/*';
  input.onchange = async (e) => {
    const file = (e.target as HTMLInputElement).files?.[0];
    if (file) {
      saving.value = true;
      try {
        const formData = new FormData();
        formData.append('avatar', file);

        const response = await api.post<{ data: { avatar_url: string } }>('/account/avatar/upload', formData, {
          headers: { 'Content-Type': 'multipart/form-data' }
        });
        profile.avatarUrl = response.data.avatar_url;
        showMessage('Avatar uploaded successfully!');
      } catch (error: any) {
        showMessage(error.message || 'Failed to upload avatar', true);
      } finally {
        saving.value = false;
      }
    }
  };
  input.click();
};

const enableTwoFactor = async () => {
  try {
    const response = await api.post<{ data: { qr_code: string } }>('/account/2fa/enable');
    twoFactor.qrCode = response.data.qr_code;
    showTwoFactorModal.value = true;
  } catch (error: any) {
    showMessage(error.message || 'Failed to enable 2FA', true);
  }
};

const verifyTwoFactor = async () => {
  if (!twoFactor.verificationCode) return;

  saving.value = true;
  try {
    await api.post('/account/2fa/verify', {
      code: twoFactor.verificationCode,
    });
    twoFactor.enabled = true;
    showTwoFactorModal.value = false;
    twoFactor.verificationCode = '';
    showMessage('Two-factor authentication enabled!');
  } catch (error: any) {
    showMessage(error.message || 'Failed to verify 2FA code', true);
  } finally {
    saving.value = false;
  }
};

const disableTwoFactor = async () => {
  if (confirm('Are you sure you want to disable two-factor authentication?')) {
    try {
      await api.post('/account/2fa/disable');
      twoFactor.enabled = false;
      showMessage('Two-factor authentication disabled');
    } catch (error: any) {
      showMessage(error.message || 'Failed to disable 2FA', true);
    }
  }
};

const revokeSession = async (sessionUuid: string) => {
  try {
    await api.delete(`/account/sessions/${sessionUuid}`);
    sessions.value = sessions.value.filter(s => s.uuid !== sessionUuid);
    showMessage('Session revoked');
  } catch (error: any) {
    showMessage(error.message || 'Failed to revoke session', true);
  }
};

const revokeAllSessions = async () => {
  if (confirm('Are you sure you want to revoke all other sessions?')) {
    try {
      await api.delete('/account/sessions');
      sessions.value = sessions.value.filter(s => s.is_current);
      showMessage('All other sessions revoked');
    } catch (error: any) {
      showMessage(error.message || 'Failed to revoke sessions', true);
    }
  }
};

const exportData = async (format: 'json' | 'csv') => {
  saving.value = true;
  try {
    await api.post('/account/data-export', { format });
    showMessage(`Your data export request has been submitted. You'll be notified when it's ready.`);
  } catch (error: any) {
    showMessage(error.message || 'Failed to request data export', true);
  } finally {
    saving.value = false;
  }
};

const deleteAccount = async () => {
  if (!deletePassword.value) {
    showMessage('Please enter your password to confirm deletion', true);
    return;
  }

  saving.value = true;
  try {
    await api.post('/account/deletion', {
      password: deletePassword.value,
      reason: 'user_requested',
    });
    showDeleteModal.value = false;
    showMessage('Account deletion request submitted. Check your email to confirm.');
    // Wait a bit then logout
    setTimeout(async () => {
      await authStore.logout();
      router.push('/');
    }, 3000);
  } catch (error: any) {
    showMessage(error.message || 'Failed to delete account', true);
  } finally {
    saving.value = false;
    deletePassword.value = '';
  }
};

const getActivityIcon = (type: string) => {
  switch (type) {
    case 'create':
      return 'M12 4v16m8-8H4';
    case 'edit':
      return 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z';
    case 'verify':
      return 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z';
    case 'comment':
      return 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z';
    case 'download':
      return 'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4';
    default:
      return 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z';
  }
};

const getAchievementIcon = (type: string) => {
  switch (type) {
    case 'star':
      return 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z';
    case 'check':
      return 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z';
    case 'calendar':
      return 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z';
    case 'award':
      return 'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z';
    case 'users':
      return 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z';
    default:
      return 'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z';
  }
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
      <div v-if="activeTab === 'profile'" class="space-y-6">
        <!-- Basic Information -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Basic Information</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Full Name *</label>
              <input
                v-model="profile.name"
                type="text"
                class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="Your full name"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email *</label>
              <input
                v-model="profile.email"
                type="email"
                class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="you@example.com"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Phone</label>
              <input
                v-model="profile.phone"
                type="tel"
                class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="+1 234 567 8900"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Country</label>
              <select
                v-model="profile.country"
                class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              >
                <option value="">Select country</option>
                <option v-for="country in countryOptions" :key="country" :value="country">{{ country }}</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Timezone</label>
              <select
                v-model="profile.timezone"
                class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              >
                <option value="">Select timezone</option>
                <option v-for="tz in timezoneOptions" :key="tz" :value="tz">{{ tz }}</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Languages</label>
              <input
                v-model="profile.languages"
                type="text"
                class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="English, Dutch, German"
              />
            </div>
          </div>
        </div>

        <!-- Professional Information -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Professional Information</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Job Title</label>
              <input
                v-model="profile.jobTitle"
                type="text"
                class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="e.g. OSINT Analyst, Researcher"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Organization</label>
              <input
                v-model="profile.organization"
                type="text"
                class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="Company or institution name"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Department</label>
              <input
                v-model="profile.department"
                type="text"
                class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="e.g. Research, Analysis"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Website</label>
              <input
                v-model="profile.website"
                type="url"
                class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="https://yourwebsite.com"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Twitter/X Handle</label>
              <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">@</span>
                <input
                  v-model="profile.twitterHandle"
                  type="text"
                  class="w-full pl-8 pr-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="username"
                />
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">LinkedIn URL</label>
              <input
                v-model="profile.linkedinUrl"
                type="url"
                class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="https://linkedin.com/in/username"
              />
            </div>
          </div>
        </div>

        <!-- Areas of Expertise -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Areas of Expertise</h2>
          <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Select the areas you specialize in (helps match you with relevant content)</p>
          <div class="flex flex-wrap gap-2">
            <button
              v-for="area in expertiseOptions"
              :key="area"
              @click="toggleExpertise(area)"
              :class="[
                'px-3 py-1.5 rounded-full text-sm font-medium transition-colors border',
                profile.expertiseAreas.includes(area)
                  ? 'bg-blue-600 text-white border-blue-600'
                  : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:border-blue-400'
              ]"
            >
              {{ area }}
            </button>
          </div>
        </div>

        <!-- Bio -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Biography</h2>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">About You</label>
            <textarea
              v-model="profile.bio"
              rows="5"
              class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="Tell the community about yourself, your background, interests, and what brought you to OSINT research..."
            ></textarea>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">This will be visible on your public profile if enabled.</p>
          </div>
        </div>

        <!-- Privacy Settings -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Profile Visibility</h2>
          <label class="flex items-center space-x-3 cursor-pointer">
            <input
              v-model="profile.publicProfile"
              type="checkbox"
              class="w-5 h-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
            />
            <div>
              <span class="text-gray-900 dark:text-white font-medium">Make my profile public</span>
              <p class="text-sm text-gray-500 dark:text-gray-400">Allow other users to see your profile, expertise, and contributions</p>
            </div>
          </label>
        </div>

        <!-- Save Button -->
        <div class="flex justify-end">
          <button
            @click="saveProfile"
            :disabled="saving"
            class="px-6 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 disabled:opacity-50"
          >
            {{ saving ? 'Saving...' : 'Save All Changes' }}
          </button>
        </div>

        <!-- Achievements -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Achievements</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div
              v-for="achievement in achievements"
              :key="achievement.id"
              :class="[
                'p-4 rounded-lg border-2 transition-all',
                achievement.earned
                  ? 'border-yellow-400 bg-yellow-50 dark:bg-yellow-900/20'
                  : 'border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50 opacity-60'
              ]"
            >
              <div class="flex items-start space-x-3">
                <div
                  :class="[
                    'w-10 h-10 rounded-full flex items-center justify-center',
                    achievement.earned
                      ? 'bg-yellow-400 text-yellow-900'
                      : 'bg-gray-300 dark:bg-gray-600 text-gray-500 dark:text-gray-400'
                  ]"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getAchievementIcon(achievement.icon)" />
                  </svg>
                </div>
                <div>
                  <h3 class="font-medium text-gray-900 dark:text-white">{{ achievement.name }}</h3>
                  <p class="text-sm text-gray-500 dark:text-gray-400">{{ achievement.description }}</p>
                  <p v-if="achievement.earned" class="text-xs text-yellow-600 dark:text-yellow-400 mt-1">
                    Earned {{ achievement.earnedDate }}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Activity Tab -->
      <div v-if="activeTab === 'activity'" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Activity History</h2>
        <div v-if="activityHistory.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
          <svg class="w-12 h-12 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <p>No activity recorded yet</p>
        </div>
        <div v-else class="space-y-4">
          <div
            v-for="(activity, index) in activityHistory"
            :key="index"
            class="flex items-start space-x-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg"
          >
            <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
              <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getActivityIcon(activity.icon)" />
              </svg>
            </div>
            <div class="flex-1">
              <p class="text-gray-900 dark:text-white font-medium">{{ activity.description }}</p>
              <p class="text-sm text-gray-500 dark:text-gray-400">
                {{ new Date(activity.created_at).toLocaleString() }}
                <span v-if="activity.ip_address" class="ml-2">from {{ activity.ip_address }}</span>
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Security Tab -->
      <div v-if="activeTab === 'security'" class="space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Change Password</h2>
          <div class="space-y-4 max-w-md">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Current Password</label>
              <input
                v-model="passwordForm.current"
                type="password"
                class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">New Password</label>
              <input
                v-model="passwordForm.new"
                type="password"
                class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
              <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Minimum 8 characters</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Confirm New Password</label>
              <input
                v-model="passwordForm.confirm"
                type="password"
                class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>
            <button
              @click="changePassword"
              :disabled="!isPasswordValid || saving"
              class="px-4 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ saving ? 'Updating...' : 'Update Password' }}
            </button>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Two-Factor Authentication</h2>
          <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
            <div class="flex items-center space-x-4">
              <div
                :class="[
                  'w-12 h-12 rounded-full flex items-center justify-center',
                  twoFactor.enabled ? 'bg-green-100 dark:bg-green-900/30' : 'bg-gray-100 dark:bg-gray-700'
                ]"
              >
                <svg
                  class="w-6 h-6"
                  :class="twoFactor.enabled ? 'text-green-600 dark:text-green-400' : 'text-gray-500'"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
              </div>
              <div>
                <p class="font-medium text-gray-900 dark:text-white">
                  {{ twoFactor.enabled ? 'Enabled' : 'Not Enabled' }}
                </p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Add an extra layer of security</p>
              </div>
            </div>
            <button
              @click="twoFactor.enabled ? disableTwoFactor() : enableTwoFactor()"
              :class="[
                'px-4 py-2 rounded-lg font-medium',
                twoFactor.enabled
                  ? 'bg-red-100 text-red-700 hover:bg-red-200 dark:bg-red-900/30 dark:text-red-400'
                  : 'bg-blue-600 text-white hover:bg-blue-700'
              ]"
            >
              {{ twoFactor.enabled ? 'Disable' : 'Enable' }}
            </button>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Active Sessions</h2>
          <div v-if="sessions.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
            <p>No active sessions</p>
          </div>
          <div v-else class="space-y-3">
            <div
              v-for="session in sessions"
              :key="session.uuid"
              class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg"
            >
              <div class="flex items-center space-x-3">
                <svg class="w-8 h-8 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                <div>
                  <p class="font-medium text-gray-900 dark:text-white">{{ session.browser }} on {{ session.platform }}</p>
                  <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ session.location || session.ip_address }} -
                    {{ session.is_current ? 'Now' : new Date(session.last_activity_at).toLocaleString() }}
                  </p>
                </div>
              </div>
              <div class="flex items-center space-x-2">
                <span
                  v-if="session.is_current"
                  class="px-2 py-1 text-xs font-medium bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 rounded"
                >
                  Current
                </span>
                <button
                  v-else
                  @click="revokeSession(session.uuid)"
                  class="text-red-600 dark:text-red-400 hover:text-red-700 text-sm"
                >
                  Revoke
                </button>
              </div>
            </div>
          </div>
          <button
            @click="revokeAllSessions"
            class="mt-4 text-red-600 dark:text-red-400 hover:text-red-700 text-sm font-medium"
          >
            Revoke All Other Sessions
          </button>
        </div>
      </div>

      <!-- Data & Privacy Tab -->
      <div v-if="activeTab === 'data'" class="space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Export Your Data</h2>
          <p class="text-gray-600 dark:text-gray-400 mb-4">
            Download a copy of all your data in compliance with GDPR regulations.
          </p>
          <div class="flex space-x-3">
            <button
              @click="exportData('json')"
              :disabled="saving"
              class="px-4 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 disabled:opacity-50"
            >
              Export as JSON
            </button>
            <button
              @click="exportData('csv')"
              :disabled="saving"
              class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg font-medium hover:bg-gray-300 dark:hover:bg-gray-600 disabled:opacity-50"
            >
              Export as CSV
            </button>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-2 border-red-200 dark:border-red-900">
          <h2 class="text-lg font-semibold text-red-700 dark:text-red-400 mb-4">Danger Zone</h2>
          <div class="p-4 bg-red-50 dark:bg-red-900/20 rounded-lg">
            <div class="flex items-center justify-between">
              <div>
                <p class="font-medium text-red-700 dark:text-red-400">Delete Account</p>
                <p class="text-sm text-red-600 dark:text-red-500">Permanently delete your account and all associated data</p>
              </div>
              <button
                @click="showDeleteModal = true"
                class="px-4 py-2 bg-red-600 text-white rounded-lg font-medium hover:bg-red-700"
              >
                Delete Account
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Delete Account Modal -->
      <div
        v-if="showDeleteModal"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
      >
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full p-6">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Delete Account</h3>
          <p class="text-gray-600 dark:text-gray-400 mb-4">
            This action is irreversible. All your data, events, and contributions will be permanently deleted.
          </p>
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              Enter your password to confirm
            </label>
            <input
              v-model="deletePassword"
              type="password"
              class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-red-500 focus:border-transparent"
              placeholder="Your password"
            />
          </div>
          <div class="flex justify-end space-x-3">
            <button
              @click="showDeleteModal = false; deletePassword = ''"
              class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg font-medium"
            >
              Cancel
            </button>
            <button
              @click="deleteAccount"
              :disabled="saving || !deletePassword"
              class="px-4 py-2 bg-red-600 text-white rounded-lg font-medium hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ saving ? 'Deleting...' : 'Yes, Delete My Account' }}
            </button>
          </div>
        </div>
      </div>

      <!-- Two-Factor Setup Modal -->
      <div
        v-if="showTwoFactorModal"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
      >
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full p-6">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Set Up Two-Factor Authentication</h3>
          <div class="text-center mb-4">
            <p class="text-gray-600 dark:text-gray-400 mb-4">
              Scan this QR code with your authenticator app
            </p>
            <div class="inline-block p-4 bg-white rounded-lg">
              <div class="w-40 h-40 bg-gray-200 rounded flex items-center justify-center">
                <span class="text-gray-500 text-sm">QR Code</span>
              </div>
            </div>
          </div>
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Verification Code</label>
            <input
              v-model="twoFactor.verificationCode"
              type="text"
              maxlength="6"
              class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-center text-2xl tracking-widest"
              placeholder="000000"
            />
          </div>
          <div class="flex justify-end space-x-3">
            <button
              @click="showTwoFactorModal = false"
              class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg font-medium"
            >
              Cancel
            </button>
            <button
              @click="verifyTwoFactor"
              :disabled="!twoFactor.verificationCode || saving"
              class="px-4 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 disabled:opacity-50"
            >
              {{ saving ? 'Verifying...' : 'Verify & Enable' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
