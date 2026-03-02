<script setup lang="ts">
import { ref } from 'vue';
import { useApi } from '@/composables/useApi';

interface Achievement {
  id: number;
  name: string;
  description: string;
  icon: string;
  earned: boolean;
  earnedDate: string | null;
}

const props = defineProps<{
  profile: {
    name: string;
    email: string;
    phone: string;
    country: string;
    timezone: string;
    languages: string;
    jobTitle: string;
    organization: string;
    department: string;
    website: string;
    twitterHandle: string;
    linkedinUrl: string;
    expertiseAreas: string[];
    publicProfile: boolean;
    bio: string;
    [key: string]: any;
  };
  achievements: Achievement[];
}>();

const emit = defineEmits<{
  (e: 'message', message: string, isError?: boolean): void;
  (e: 'update:profile', profile: any): void;
}>();

const api = useApi();
const saving = ref(false);

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
  const newExpertiseAreas = [...props.profile.expertiseAreas];
  const index = newExpertiseAreas.indexOf(area);
  if (index === -1) {
    newExpertiseAreas.push(area);
  } else {
    newExpertiseAreas.splice(index, 1);
  }
  emit('update:profile', { ...props.profile, expertiseAreas: newExpertiseAreas });
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

const saveProfile = async () => {
  saving.value = true;
  try {
    await api.put('/account/profile', {
      name: props.profile.name,
      bio: props.profile.bio,
      organization: props.profile.organization,
      location: props.profile.country,
      website: props.profile.website,
      timezone: props.profile.timezone,
      // Add other fields as necessary if backend supports them
    });
    emit('message', 'Profile updated successfully!');
  } catch (error: any) {
    emit('message', error.message || 'Failed to save profile', true);
  } finally {
    saving.value = false;
  }
};

const updateProfileField = (field: string, value: any) => {
    emit('update:profile', { ...props.profile, [field]: value });
};

</script>

<template>
  <div class="space-y-6">
    <!-- Basic Information -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
      <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Basic Information</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Full Name *</label>
          <input
            :value="profile.name"
            @input="updateProfileField('name', ($event.target as HTMLInputElement).value)"
            type="text"
            class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            placeholder="Your full name"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email *</label>
          <input
            :value="profile.email"
            @input="updateProfileField('email', ($event.target as HTMLInputElement).value)"
            type="email"
            class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            placeholder="you@example.com"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Phone</label>
          <input
            :value="profile.phone"
            @input="updateProfileField('phone', ($event.target as HTMLInputElement).value)"
            type="tel"
            class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            placeholder="+1 234 567 8900"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Country</label>
          <select
            :value="profile.country"
            @change="updateProfileField('country', ($event.target as HTMLSelectElement).value)"
            class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          >
            <option value="">Select country</option>
            <option v-for="country in countryOptions" :key="country" :value="country">{{ country }}</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Timezone</label>
          <select
            :value="profile.timezone"
            @change="updateProfileField('timezone', ($event.target as HTMLSelectElement).value)"
            class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          >
            <option value="">Select timezone</option>
            <option v-for="tz in timezoneOptions" :key="tz" :value="tz">{{ tz }}</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Languages</label>
          <input
            :value="profile.languages"
            @input="updateProfileField('languages', ($event.target as HTMLInputElement).value)"
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
            :value="profile.jobTitle"
            @input="updateProfileField('jobTitle', ($event.target as HTMLInputElement).value)"
            type="text"
            class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            placeholder="e.g. OSINT Analyst, Researcher"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Organization</label>
          <input
            :value="profile.organization"
            @input="updateProfileField('organization', ($event.target as HTMLInputElement).value)"
            type="text"
            class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            placeholder="Company or institution name"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Department</label>
          <input
            :value="profile.department"
            @input="updateProfileField('department', ($event.target as HTMLInputElement).value)"
            type="text"
            class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            placeholder="e.g. Research, Analysis"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Website</label>
          <input
            :value="profile.website"
            @input="updateProfileField('website', ($event.target as HTMLInputElement).value)"
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
              :value="profile.twitterHandle"
              @input="updateProfileField('twitterHandle', ($event.target as HTMLInputElement).value)"
              type="text"
              class="w-full pl-8 pr-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="username"
            />
          </div>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">LinkedIn URL</label>
          <input
            :value="profile.linkedinUrl"
            @input="updateProfileField('linkedinUrl', ($event.target as HTMLInputElement).value)"
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
          :value="profile.bio"
          @input="updateProfileField('bio', ($event.target as HTMLTextAreaElement).value)"
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
          :checked="profile.publicProfile"
          @change="updateProfileField('publicProfile', ($event.target as HTMLInputElement).checked)"
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
</template>
