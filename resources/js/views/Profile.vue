<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const router = useRouter();
const authStore = useAuthStore();

const activeTab = ref<'profile' | 'activity' | 'security' | 'data'>('profile');
const loading = ref(false);
const saving = ref(false);
const showDeleteModal = ref(false);
const showTwoFactorModal = ref(false);

const profile = reactive({
  name: '',
  email: '',
  phone: '',
  organization: '',
  bio: '',
  avatarUrl: ''
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

const activityHistory = ref([
  { id: 1, action: 'Created event', target: 'Artillery strike near Kharkiv', date: '2026-01-16 10:30', icon: 'create' },
  { id: 2, action: 'Updated event', target: 'Troop movement in Donetsk', date: '2026-01-15 14:22', icon: 'edit' },
  { id: 3, action: 'Verified event', target: 'Equipment sighting in Luhansk', date: '2026-01-15 09:15', icon: 'verify' },
  { id: 4, action: 'Commented on', target: 'Missile strike analysis', date: '2026-01-14 16:45', icon: 'comment' },
  { id: 5, action: 'Downloaded report', target: 'Weekly conflict summary', date: '2026-01-14 11:00', icon: 'download' }
]);

const achievements = ref([
  { id: 1, name: 'First Contribution', description: 'Created your first event', icon: 'star', earned: true, earnedDate: '2025-12-01' },
  { id: 2, name: 'Verified Reporter', description: 'Had 10 events verified', icon: 'check', earned: true, earnedDate: '2025-12-15' },
  { id: 3, name: 'Consistent Contributor', description: 'Contributed for 30 consecutive days', icon: 'calendar', earned: true, earnedDate: '2026-01-01' },
  { id: 4, name: 'Expert Analyst', description: 'Achieved 100 verified events', icon: 'award', earned: false, earnedDate: null },
  { id: 5, name: 'Community Leader', description: 'Verified 50 community events', icon: 'users', earned: false, earnedDate: null }
]);

const stats = ref({
  eventsCreated: 47,
  eventsVerified: 23,
  contributions: 89,
  accuracy: 94.2,
  rank: 'Senior Analyst',
  memberSince: '2025-11-15'
});

const sessions = ref([
  { id: 1, device: 'Chrome on Windows', location: 'Amsterdam, NL', lastActive: 'Now', current: true },
  { id: 2, device: 'Safari on iPhone', location: 'Amsterdam, NL', lastActive: '2 hours ago', current: false }
]);

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

const loadProfile = () => {
  if (authStore.user) {
    profile.name = authStore.user.name || '';
    profile.email = authStore.user.email || '';
    profile.avatarUrl = authStore.user.avatar_url || '';
  }
};

const saveProfile = async () => {
  saving.value = true;
  try {
    await new Promise(resolve => setTimeout(resolve, 1000));
    alert('Profile updated successfully!');
  } finally {
    saving.value = false;
  }
};

const changePassword = async () => {
  if (!isPasswordValid.value) return;

  saving.value = true;
  try {
    await new Promise(resolve => setTimeout(resolve, 1000));
    passwordForm.current = '';
    passwordForm.new = '';
    passwordForm.confirm = '';
    alert('Password changed successfully!');
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
      alert('Avatar uploaded successfully!');
    }
  };
  input.click();
};

const enableTwoFactor = async () => {
  twoFactor.qrCode = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=';
  showTwoFactorModal.value = true;
};

const verifyTwoFactor = async () => {
  if (!twoFactor.verificationCode) return;

  saving.value = true;
  try {
    await new Promise(resolve => setTimeout(resolve, 1000));
    twoFactor.enabled = true;
    showTwoFactorModal.value = false;
    twoFactor.verificationCode = '';
    alert('Two-factor authentication enabled!');
  } finally {
    saving.value = false;
  }
};

const disableTwoFactor = () => {
  if (confirm('Are you sure you want to disable two-factor authentication?')) {
    twoFactor.enabled = false;
    alert('Two-factor authentication disabled');
  }
};

const revokeSession = (sessionId: number) => {
  sessions.value = sessions.value.filter(s => s.id !== sessionId);
  alert('Session revoked');
};

const revokeAllSessions = () => {
  if (confirm('Are you sure you want to revoke all other sessions?')) {
    sessions.value = sessions.value.filter(s => s.current);
    alert('All other sessions revoked');
  }
};

const exportData = async (format: 'json' | 'csv') => {
  saving.value = true;
  try {
    await new Promise(resolve => setTimeout(resolve, 1500));
    alert(`Your data has been exported as ${format.toUpperCase()}`);
  } finally {
    saving.value = false;
  }
};

const deleteAccount = async () => {
  saving.value = true;
  try {
    await new Promise(resolve => setTimeout(resolve, 1500));
    await authStore.logout();
    router.push('/');
  } finally {
    saving.value = false;
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

onMounted(() => {
  loading.value = true;
  loadProfile();
  loading.value = false;
});
</script>

<template>
  <div class="p-6 bg-gray-50 dark:bg-gray-900 min-h-screen">
    <div class="max-w-5xl mx-auto">
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
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Profile Information</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Full Name</label>
              <input
                v-model="profile.name"
                type="text"
                class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
              <input
                v-model="profile.email"
                type="email"
                class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Phone</label>
              <input
                v-model="profile.phone"
                type="tel"
                class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Organization</label>
              <input
                v-model="profile.organization"
                type="text"
                class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Bio</label>
              <textarea
                v-model="profile.bio"
                rows="3"
                class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="Tell us about yourself..."
              ></textarea>
            </div>
          </div>
          <div class="mt-4 flex justify-end">
            <button
              @click="saveProfile"
              :disabled="saving"
              class="px-4 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 disabled:opacity-50"
            >
              {{ saving ? 'Saving...' : 'Save Changes' }}
            </button>
          </div>
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
        <div class="space-y-4">
          <div
            v-for="activity in activityHistory"
            :key="activity.id"
            class="flex items-start space-x-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg"
          >
            <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
              <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getActivityIcon(activity.icon)" />
              </svg>
            </div>
            <div class="flex-1">
              <p class="text-gray-900 dark:text-white">
                <span class="font-medium">{{ activity.action }}</span>
                <span class="text-gray-600 dark:text-gray-400"> {{ activity.target }}</span>
              </p>
              <p class="text-sm text-gray-500 dark:text-gray-400">{{ activity.date }}</p>
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
          <div class="space-y-3">
            <div
              v-for="session in sessions"
              :key="session.id"
              class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg"
            >
              <div class="flex items-center space-x-3">
                <svg class="w-8 h-8 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                <div>
                  <p class="font-medium text-gray-900 dark:text-white">{{ session.device }}</p>
                  <p class="text-sm text-gray-500 dark:text-gray-400">{{ session.location }} - {{ session.lastActive }}</p>
                </div>
              </div>
              <div class="flex items-center space-x-2">
                <span
                  v-if="session.current"
                  class="px-2 py-1 text-xs font-medium bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 rounded"
                >
                  Current
                </span>
                <button
                  v-else
                  @click="revokeSession(session.id)"
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
          <p class="text-gray-600 dark:text-gray-400 mb-6">
            This action is irreversible. All your data, events, and contributions will be permanently deleted.
            Are you sure you want to proceed?
          </p>
          <div class="flex justify-end space-x-3">
            <button
              @click="showDeleteModal = false"
              class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg font-medium"
            >
              Cancel
            </button>
            <button
              @click="deleteAccount"
              :disabled="saving"
              class="px-4 py-2 bg-red-600 text-white rounded-lg font-medium hover:bg-red-700 disabled:opacity-50"
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
