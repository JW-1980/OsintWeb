<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <!-- Public Navigation -->
    <nav class="fixed top-0 left-0 right-0 z-50 bg-white/80 dark:bg-gray-900/80 backdrop-blur-md border-b border-gray-200 dark:border-gray-800">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
          <div class="flex items-center space-x-2">
            <router-link to="/" class="flex items-center space-x-2">
              <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                </svg>
              </div>
              <span class="text-xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">OsintWeb</span>
            </router-link>
          </div>
          <div class="hidden md:flex items-center space-x-6">
            <router-link to="/explore" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors">Map</router-link>
            <router-link to="/explore/events" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors">Events</router-link>
            <router-link to="/explore/equipment" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors">Equipment</router-link>
            <router-link to="/about" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors">About</router-link>
          </div>
          <div class="flex items-center space-x-4">
            <button @click="toggleTheme" class="p-2 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
              <svg v-if="isDark" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
              </svg>
              <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
              </svg>
            </button>
            <router-link to="/login" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors">Login</router-link>
          </div>
        </div>
      </div>
    </nav>

    <!-- Main Content -->
    <div class="pt-24 pb-12 px-4 sm:px-6 lg:px-8">
      <div class="max-w-2xl mx-auto">
        <!-- Success Message -->
        <div v-if="submitted" class="text-center py-16">
          <div class="w-20 h-20 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
          </div>
          <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Tip Submitted Successfully</h1>
          <p class="text-lg text-gray-600 dark:text-gray-400 mb-8">
            Thank you for your contribution. Our team will review your submission and may reach out if additional information is needed.
          </p>
          <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <button
              @click="resetForm"
              class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors"
            >
              Submit Another Tip
            </button>
            <router-link
              to="/explore/events"
              class="px-6 py-3 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-900 dark:text-white font-medium rounded-lg transition-colors"
            >
              Browse Events
            </router-link>
          </div>
        </div>

        <!-- Form -->
        <div v-else>
          <!-- Header -->
          <div class="text-center mb-8">
            <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center mx-auto mb-4">
              <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Submit a Tip</h1>
            <p class="text-gray-600 dark:text-gray-400">
              Help us track events by submitting information. You can remain anonymous.
            </p>
          </div>

          <!-- Info Box -->
          <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-8">
            <div class="flex items-start">
              <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <div class="text-sm text-blue-800 dark:text-blue-300">
                <p class="font-medium mb-1">Privacy Notice</p>
                <p class="text-blue-700 dark:text-blue-400">All submissions are reviewed by our verification team. Your contact information is optional and will only be used if we need to follow up on your tip. We do not share personal information with third parties.</p>
              </div>
            </div>
          </div>

          <!-- Form Card -->
          <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6 md:p-8">
            <form @submit.prevent="handleSubmit" class="space-y-6">
              <!-- Title -->
              <div>
                <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Title <span class="text-red-500">*</span>
                </label>
                <input
                  id="title"
                  v-model="form.title"
                  type="text"
                  required
                  placeholder="Brief description of the event"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                  :class="{ 'border-red-500': errors.title }"
                />
                <p v-if="errors.title" class="mt-1 text-sm text-red-500">{{ errors.title }}</p>
              </div>

              <!-- Event Type -->
              <div>
                <label for="event_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Event Type
                </label>
                <select
                  id="event_type"
                  v-model="form.event_type"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                >
                  <option value="">Select a type (optional)</option>
                  <option value="combat_engagement">Combat Engagement</option>
                  <option value="airstrike">Airstrike</option>
                  <option value="artillery_strike">Artillery Strike</option>
                  <option value="missile_strike">Missile Strike</option>
                  <option value="equipment_destroyed">Equipment Destroyed</option>
                  <option value="equipment_sighting">Equipment Sighting</option>
                  <option value="troop_movement">Troop Movement</option>
                  <option value="infrastructure_damage">Infrastructure Damage</option>
                  <option value="civilian_casualties">Civilian Casualties</option>
                  <option value="other">Other</option>
                </select>
              </div>

              <!-- Description -->
              <div>
                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Description <span class="text-red-500">*</span>
                </label>
                <textarea
                  id="description"
                  v-model="form.description"
                  required
                  rows="5"
                  placeholder="Provide detailed information about what you witnessed or found..."
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors resize-y"
                  :class="{ 'border-red-500': errors.description }"
                ></textarea>
                <p v-if="errors.description" class="mt-1 text-sm text-red-500">{{ errors.description }}</p>
              </div>

              <!-- Location -->
              <div>
                <label for="location" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Location
                  <span class="text-gray-400 font-normal">(optional)</span>
                </label>
                <input
                  id="location"
                  v-model="form.location"
                  type="text"
                  placeholder="City, region, or coordinates"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                />
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Include as much location detail as possible</p>
              </div>

              <!-- Date/Time -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label for="date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Date
                    <span class="text-gray-400 font-normal">(optional)</span>
                  </label>
                  <input
                    id="date"
                    v-model="form.date"
                    type="date"
                    class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                  />
                </div>
                <div>
                  <label for="time" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Approximate Time
                    <span class="text-gray-400 font-normal">(optional)</span>
                  </label>
                  <input
                    id="time"
                    v-model="form.time"
                    type="time"
                    class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                  />
                </div>
              </div>

              <!-- Sources -->
              <div>
                <label for="sources" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Sources / Links
                  <span class="text-gray-400 font-normal">(optional)</span>
                </label>
                <textarea
                  id="sources"
                  v-model="form.sources"
                  rows="3"
                  placeholder="Add links to social media posts, news articles, or other sources (one per line)"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors resize-y"
                ></textarea>
              </div>

              <!-- Divider -->
              <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-4">Contact Information (Optional)</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                  Provide your contact info only if you want us to follow up. This is completely optional.
                </p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label for="contact_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                      Name
                    </label>
                    <input
                      id="contact_name"
                      v-model="form.contact_name"
                      type="text"
                      placeholder="Your name"
                      class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                    />
                  </div>
                  <div>
                    <label for="contact_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                      Email
                    </label>
                    <input
                      id="contact_email"
                      v-model="form.contact_email"
                      type="email"
                      placeholder="your@email.com"
                      class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                    />
                  </div>
                </div>
              </div>

              <!-- Captcha Placeholder -->
              <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                <div class="flex items-center space-x-3">
                  <input
                    type="checkbox"
                    v-model="form.captcha"
                    id="captcha"
                    class="w-5 h-5 text-blue-600 rounded focus:ring-blue-500"
                  />
                  <label for="captcha" class="text-sm text-gray-700 dark:text-gray-300">
                    I am not a robot
                  </label>
                </div>
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                  Captcha verification placeholder - implement reCAPTCHA in production
                </p>
              </div>

              <!-- Submit Button -->
              <button
                type="submit"
                :disabled="submitting"
                class="w-full py-3 px-6 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <span v-if="submitting" class="flex items-center justify-center">
                  <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  Submitting...
                </span>
                <span v-else>Submit Tip</span>
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-8 px-4 sm:px-6 lg:px-8">
      <div class="max-w-7xl mx-auto text-center">
        <p class="text-gray-400">Your privacy is important to us. <router-link to="/about" class="text-blue-400 hover:underline">Learn more about our practices</router-link>.</p>
      </div>
    </footer>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed } from 'vue';
import { useThemeStore } from '@/stores/theme';

const themeStore = useThemeStore();
const isDark = computed(() => themeStore.isDark);

const toggleTheme = () => {
  themeStore.toggleTheme();
};

const submitted = ref(false);
const submitting = ref(false);

const form = reactive({
  title: '',
  event_type: '',
  description: '',
  location: '',
  date: '',
  time: '',
  sources: '',
  contact_name: '',
  contact_email: '',
  captcha: false
});

const errors = reactive<Record<string, string>>({});

const validateForm = () => {
  const newErrors: Record<string, string> = {};

  if (!form.title.trim()) {
    newErrors.title = 'Title is required';
  } else if (form.title.length < 5) {
    newErrors.title = 'Title must be at least 5 characters';
  }

  if (!form.description.trim()) {
    newErrors.description = 'Description is required';
  } else if (form.description.length < 20) {
    newErrors.description = 'Description must be at least 20 characters';
  }

  Object.keys(errors).forEach(key => delete errors[key]);
  Object.assign(errors, newErrors);

  return Object.keys(newErrors).length === 0;
};

const handleSubmit = async () => {
  if (!validateForm()) {
    return;
  }

  submitting.value = true;

  // Simulate API call
  await new Promise(resolve => setTimeout(resolve, 1500));

  submitted.value = true;
  submitting.value = false;
};

const resetForm = () => {
  form.title = '';
  form.event_type = '';
  form.description = '';
  form.location = '';
  form.date = '';
  form.time = '';
  form.sources = '';
  form.contact_name = '';
  form.contact_email = '';
  form.captcha = false;
  submitted.value = false;
};
</script>
