<template>

<div class="relative flex h-auto min-h-screen w-full flex-col overflow-x-hidden">
<div class="layout-container flex h-full grow flex-col">
<header class="flex items-center justify-between whitespace-nowrap border-b border-solid border-slate-200 dark:border-slate-800 bg-background-light dark:bg-background-dark px-10 py-3 sticky top-0 z-50">
<div class="flex items-center gap-8">
<div class="flex items-center gap-4 text-primary">
<div class="size-6">
<span class="material-symbols-outlined text-3xl">radar</span>
</div>
<h2 class="text-slate-900 dark:text-white text-lg font-bold leading-tight tracking-tight">INTEL-CORE</h2>
</div>
<label class="flex flex-col min-w-40 !h-10 max-w-64">
<div class="flex w-full flex-1 items-stretch rounded-lg h-full">
<div class="text-slate-400 dark:text-slate-500 flex border-none bg-slate-100 dark:bg-slate-800 items-center justify-center pl-4 rounded-l-lg" data-icon="search">
<span class="material-symbols-outlined text-xl">search</span>
</div>
<input class="form-input flex w-full min-w-0 flex-1 border-none bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-0 h-full placeholder:text-slate-500 px-4 rounded-r-lg pl-2 text-sm" placeholder="Search Intel Database..." value=""/>
</div>
</label>
</div>
<div class="flex flex-1 justify-end gap-6">
<nav class="flex items-center gap-6">
<a class="text-slate-600 dark:text-slate-300 text-sm font-medium hover:text-primary transition-colors" href="#">Intelligence</a>
<a class="text-slate-600 dark:text-slate-300 text-sm font-medium hover:text-primary transition-colors" href="#">Conflicts</a>
<a class="text-slate-600 dark:text-slate-300 text-sm font-medium hover:text-primary transition-colors" href="#">Satellite</a>
<a class="text-slate-600 dark:text-slate-300 text-sm font-medium hover:text-primary transition-colors" href="#">Live Maps</a>
</nav>
<div class="flex gap-2 border-l border-slate-200 dark:border-slate-800 pl-6">
<button class="flex items-center justify-center rounded-lg h-10 w-10 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-primary/20 hover:text-primary transition-all">
<span class="material-symbols-outlined text-xl">notifications</span>
</button>
<button class="flex items-center justify-center rounded-lg h-10 w-10 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-primary/20 hover:text-primary transition-all">
<span class="material-symbols-outlined text-xl">shield</span>
</button>
</div>
<div class="bg-primary/20 border-2 border-primary rounded-full size-10 flex items-center justify-center overflow-hidden">
<img class="w-full h-full object-cover" data-alt="High-tech analyst profile portrait photo" src="https://lh3.googleusercontent.com/aida-public/AB6AXuBChQb8wvXsfOX9LalU4cCqDxXmVJcjudrdEll3G_o865QxucUDsE7Nh1FWhnPNaeKNIFRFFuCUImOaObRkytbj4pG9dye4H4l1vst1-KE-BIsWddQ39rA9g9m8o7O0FXw4J_fiAFDc5zRy1GuMA5SumP8oaXNwqLanupeLMUXObcXhavkM8rzV0fFjqWaeAtQ7jCRvW-uO3TyKkjm5Pn01Jl6jUbEQR565yxgPnA4EfpaX6_1gyFL_S5zltaMZSG2xXiJFOSMCmytK"/>
</div>
</div>
</header>
<main class="flex-1 flex px-4 md:px-10 lg:px-20 py-8 gap-8">
<aside class="w-64 shrink-0 flex flex-col gap-6">
<div class="flex flex-col gap-4 p-4 rounded-xl bg-slate-100 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-800">
<div class="flex items-center gap-3">
<div class="relative">
<div class="bg-primary/10 border border-primary/30 rounded-full size-12 flex items-center justify-center overflow-hidden">
<img class="w-full h-full object-cover" data-alt="Profile image of a senior analyst" src="https://lh3.googleusercontent.com/aida-public/AB6AXuD0VmjcuUj3StkGxo3-UiARQEalPZRFNeQPBWPGba8HeyCkpg-PBlz96WuCfjm1Bf4KQ6AWtPmV39hkNys8PJAZEP7vS650kgkELdOw6KcKmG5OpueUv4DI4Arb6jJ-jsMAIdJBfu5wD0v6WMDd62Rkw0S1UbXfJRzyn0jl5n9SQHihf3q_xBC24CuC8vl1V7ZgT-tf1YvWWNObNUqiiS3RbZfg_lQ9_UJyJx2Xi-ftG1vQsBnAT4hOola9Z0CvSdI_HjOgJCWH112y"/>
</div>
<div class="absolute -bottom-1 -right-1 bg-primary text-[10px] font-bold px-1 rounded text-white ring-2 ring-background-dark">SNR</div>
</div>
<div class="flex flex-col">
<h1 class="text-slate-900 dark:text-white text-sm font-bold leading-tight">Alex Vanguarde</h1>
<p class="text-primary text-xs font-semibold uppercase tracking-wider">Senior Analyst</p>
</div>
</div>
<div class="text-xs text-slate-500 dark:text-slate-400 px-1 italic">
                            Member since Oct 2022 • Level 42
                        </div>
</div>
<nav class="flex flex-col gap-1">
<a class="flex items-center gap-3 px-4 py-3 rounded-lg bg-primary text-white shadow-lg shadow-primary/20" href="#">
<span class="material-symbols-outlined">person</span>
<span class="text-sm font-semibold">User Profile</span>
</a>
<a class="flex items-center gap-3 px-4 py-3 rounded-lg text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" href="#">
<span class="material-symbols-outlined">settings</span>
<span class="text-sm font-medium">Platform Settings</span>
</a>
<a class="flex items-center gap-3 px-4 py-3 rounded-lg text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" href="#">
<span class="material-symbols-outlined">lock</span>
<span class="text-sm font-medium">Security &amp; Privacy</span>
</a>
<a class="flex items-center gap-3 px-4 py-3 rounded-lg text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" href="#">
<span class="material-symbols-outlined">terminal</span>
<span class="text-sm font-medium">API Access</span>
</a>
<div class="my-2 border-t border-slate-200 dark:border-slate-800"></div>
<a class="flex items-center gap-3 px-4 py-3 rounded-lg text-red-500 hover:bg-red-500/10 transition-colors" href="#">
<span class="material-symbols-outlined">logout</span>
<span class="text-sm font-medium">Sign Out</span>
</a>
</nav>
</aside>
<section class="flex-1 flex flex-col gap-8">
<div>
<h2 class="text-slate-900 dark:text-white text-3xl font-black tracking-tight mb-2">Account Intelligence</h2>
<p class="text-slate-500 dark:text-slate-400">Manage your analyst credentials, clearance levels, and secure digital identifiers.</p>
</div>
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
<div class="bg-white dark:bg-slate-900/40 p-6 rounded-xl border border-slate-200 dark:border-slate-800">
<div class="flex items-center gap-2 mb-6">
<span class="material-symbols-outlined text-primary">badge</span>
<h3 class="text-lg font-bold">Personal Information</h3>
</div>
<div class="flex flex-col gap-4">
<div class="flex flex-col gap-1.5">
<label class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Full Operational Name</label>
<input class="form-input bg-slate-50 dark:bg-slate-800/50 border-slate-200 dark:border-slate-800 rounded-lg focus:border-primary focus:ring-1 focus:ring-primary text-sm py-3" type="text" value="Alex Vanguarde"/>
</div>
<div class="flex flex-col gap-1.5">
<label class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Secure Email</label>
<input class="form-input bg-slate-50 dark:bg-slate-800/50 border-slate-200 dark:border-slate-800 rounded-lg focus:border-primary focus:ring-1 focus:ring-primary text-sm py-3" type="email" value="vanguarde.osint@intel-core.io"/>
</div>
<button class="mt-2 bg-primary hover:bg-primary/90 text-white font-bold py-2.5 rounded-lg text-sm transition-all">Update Identity</button>
</div>
</div>
<div class="bg-white dark:bg-slate-900/40 p-6 rounded-xl border border-slate-200 dark:border-slate-800">
<div class="flex items-center gap-2 mb-6">
<span class="material-symbols-outlined text-primary">security</span>
<h3 class="text-lg font-bold">Security &amp; Authentication</h3>
</div>
<div class="flex flex-col gap-6">
<div class="flex items-center justify-between">
<div class="flex flex-col">
<span class="text-sm font-bold">Master Password</span>
<span class="text-xs text-slate-500 italic">Last changed 14 days ago</span>
</div>
<button class="px-4 py-2 border border-slate-200 dark:border-slate-700 rounded-lg text-sm font-bold hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">Change</button>
</div>
<div class="flex items-center justify-between">
<div class="flex flex-col">
<span class="text-sm font-bold">Two-Factor (2FA)</span>
<span class="text-xs text-emerald-500 flex items-center gap-1">
<span class="material-symbols-outlined text-[14px]">check_circle</span> Active Protection
                                        </span>
</div>
<button class="px-4 py-2 border border-slate-200 dark:border-slate-700 rounded-lg text-sm font-bold hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">Configure</button>
</div>
<div class="p-3 bg-primary/5 border border-primary/20 rounded-lg">
<p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
<strong class="text-primary">Note:</strong> Your session is encrypted with AES-256. Regular password rotations are recommended for Senior clearance levels.
                                    </p>
</div>
</div>
</div>
</div>
<div class="bg-white dark:bg-slate-900/40 p-6 rounded-xl border border-slate-200 dark:border-slate-800">
<div class="flex items-center justify-between mb-8">
<div class="flex items-center gap-2">
<span class="material-symbols-outlined text-primary">military_tech</span>
<h3 class="text-lg font-bold">Expertise &amp; Combat Badges</h3>
</div>
<a class="text-primary text-xs font-bold uppercase hover:underline" href="#">View All Achievements</a>
</div>
<div class="grid grid-cols-2 md:grid-cols-4 gap-4">
<div class="flex flex-col items-center gap-3 p-4 bg-slate-100 dark:bg-slate-800/30 rounded-xl border border-slate-200 dark:border-slate-800/50 hover:border-primary/50 transition-all group">
<div class="relative">
<span class="material-symbols-outlined text-4xl text-slate-400 group-hover:text-primary transition-colors">verified</span>
<div class="absolute -top-1 -right-1 flex h-4 w-4">
<span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary opacity-20"></span>
<span class="relative inline-flex rounded-full h-4 w-4 bg-primary"></span>
</div>
</div>
<div class="text-center">
<p class="text-xs font-bold uppercase">Master Verifier</p>
<p class="text-[10px] text-slate-500">1,000+ Confirmed Hits</p>
</div>
</div>
<div class="flex flex-col items-center gap-3 p-4 bg-slate-100 dark:bg-slate-800/30 rounded-xl border border-slate-200 dark:border-slate-800/50 hover:border-primary/50 transition-all group">
<span class="material-symbols-outlined text-4xl text-slate-400 group-hover:text-primary transition-colors">satellite_alt</span>
<div class="text-center">
<p class="text-xs font-bold uppercase">Satellite Specialist</p>
<p class="text-[10px] text-slate-500">Expert Imagery Intel</p>
</div>
</div>
<div class="flex flex-col items-center gap-3 p-4 bg-slate-100 dark:bg-slate-800/30 rounded-xl border border-slate-200 dark:border-slate-800/50 hover:border-primary/50 transition-all group">
<span class="material-symbols-outlined text-4xl text-slate-400 group-hover:text-primary transition-colors">location_on</span>
<div class="text-center">
<p class="text-xs font-bold uppercase">Geo-Locator</p>
<p class="text-[10px] text-slate-500">Pinpoint Precision</p>
</div>
</div>
<div class="flex flex-col items-center gap-3 p-4 bg-slate-100 dark:bg-slate-800/30 rounded-xl border border-slate-200 dark:border-slate-800/50 hover:border-primary/50 transition-all group opacity-50">
<span class="material-symbols-outlined text-4xl text-slate-400">psychology</span>
<div class="text-center">
<p class="text-xs font-bold uppercase">OSINT Ghost</p>
<p class="text-[10px] text-slate-500">Locked Achievement</p>
</div>
</div>
</div>
</div>
<div class="bg-white dark:bg-slate-900/40 p-6 rounded-xl border border-slate-200 dark:border-slate-800 border-l-4 border-l-primary">
<div class="flex items-center gap-2 mb-6">
<span class="material-symbols-outlined text-primary">terminal</span>
<h3 class="text-lg font-bold">Developer &amp; API Access</h3>
</div>
<div class="space-y-4">
<p class="text-sm text-slate-500 dark:text-slate-400">Use your API keys to integrate INTEL-CORE data into your custom intelligence pipelines.</p>
<div class="flex flex-col gap-2">
<label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Active API Key</label>
<div class="flex gap-2">
<div class="flex-1 relative">
<input class="form-input w-full bg-slate-100 dark:bg-slate-800 border-none rounded-lg text-sm py-3 font-mono tracking-widest pr-10" readonly="" type="password" value="sk_live_729485hfy82390sh2398hs9"/>
<button class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-white">
<span class="material-symbols-outlined text-lg">visibility</span>
</button>
</div>
<button class="bg-slate-100 dark:bg-slate-800 hover:bg-primary hover:text-white px-4 rounded-lg flex items-center justify-center transition-all" title="Copy Key">
<span class="material-symbols-outlined text-xl">content_copy</span>
</button>
<button class="bg-slate-100 dark:bg-slate-800 hover:bg-primary hover:text-white px-4 rounded-lg flex items-center justify-center transition-all" title="Regenerate Key">
<span class="material-symbols-outlined text-xl">refresh</span>
</button>
</div>
</div>
<div class="flex items-center gap-4 text-[11px] font-bold text-slate-500 uppercase">
<div class="flex items-center gap-1">
<span class="size-2 bg-emerald-500 rounded-full"></span> Ready for Requests
                                </div>
<div>Quota: 12,450 / 50,000 monthly</div>
</div>
</div>
</div>
</section>
</main>
<footer class="mt-auto py-6 px-10 border-t border-slate-200 dark:border-slate-800 flex justify-between items-center text-xs text-slate-500">
<div class="flex items-center gap-4">
<span>© 2024 INTEL-CORE Platform</span>
<span class="w-1 h-1 bg-slate-700 rounded-full"></span>
<a class="hover:text-primary" href="#">System Status: Optimal</a>
</div>
<div class="flex gap-6 uppercase tracking-widest font-bold">
<a class="hover:text-primary" href="#">Documentation</a>
<a class="hover:text-primary" href="#">Privacy Protocol</a>
<a class="hover:text-primary" href="#">Support</a>
</div>
</footer>
</div>
</div>

</template>

<script setup lang="ts">
// @ts-nocheck
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