<template>
  <div class="p-6 bg-gray-50 dark:bg-gray-900 min-h-screen">
    <div class="max-w-4xl mx-auto">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Settings</h1>

      <!-- Settings Navigation -->
      <div class="flex flex-wrap gap-2 mb-6">
        <button
          v-for="category in categories"
          :key="category.id"
          @click="activeCategory = category.id"
          :class="[
            'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
            activeCategory === category.id
              ? 'bg-blue-600 text-white'
              : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
          ]"
        >
          {{ category.name }}
        </button>
      </div>

      <!-- Profile Settings -->
      <div v-if="activeCategory === 'profile'" class="space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Profile Information</h2>
          <div class="space-y-4">
            <div class="flex items-center space-x-4">
              <div class="w-20 h-20 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white text-2xl font-bold">
                {{ userInitials }}
              </div>
              <div>
                <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">
                  Change Avatar
                </button>
                <button class="ml-2 px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 text-sm">
                  Remove
                </button>
              </div>
            </div>
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
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Bio</label>
              <textarea
                v-model="profile.bio"
                rows="3"
                class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              ></textarea>
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Change Password</h2>
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Current Password</label>
              <input
                v-model="password.current"
                type="password"
                class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">New Password</label>
                <input
                  v-model="password.new"
                  type="password"
                  class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Confirm Password</label>
                <input
                  v-model="password.confirm"
                  type="password"
                  class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
              </div>
            </div>
            <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
              Update Password
            </button>
          </div>
        </div>
      </div>

      <!-- Appearance Settings -->
      <div v-if="activeCategory === 'appearance'" class="space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Theme</h2>
          <div class="grid grid-cols-3 gap-4">
            <button
              v-for="theme in themes"
              :key="theme.value"
              @click="appearance.theme = theme.value"
              :class="[
                'p-4 rounded-lg border-2 transition-all',
                appearance.theme === theme.value
                  ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/30'
                  : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600'
              ]"
            >
              <div :class="['w-full h-16 rounded mb-2', theme.preview]"></div>
              <span class="text-sm font-medium text-gray-900 dark:text-white">{{ theme.label }}</span>
            </button>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Display Options</h2>
          <div class="space-y-4">
            <div class="flex items-center justify-between">
              <div>
                <p class="font-medium text-gray-900 dark:text-white">Compact Mode</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Reduce spacing between elements</p>
              </div>
              <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" v-model="appearance.compactMode" class="sr-only peer">
                <div class="w-11 h-6 bg-gray-200 dark:bg-gray-700 peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
              </label>
            </div>
            <div class="flex items-center justify-between">
              <div>
                <p class="font-medium text-gray-900 dark:text-white">Show Sidebar Labels</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Display text labels in navigation</p>
              </div>
              <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" v-model="appearance.sidebarLabels" class="sr-only peer">
                <div class="w-11 h-6 bg-gray-200 dark:bg-gray-700 peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
              </label>
            </div>
            <div class="flex items-center justify-between">
              <div>
                <p class="font-medium text-gray-900 dark:text-white">Animations</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Enable UI animations and transitions</p>
              </div>
              <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" v-model="appearance.animations" class="sr-only peer">
                <div class="w-11 h-6 bg-gray-200 dark:bg-gray-700 peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
              </label>
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Language & Region</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Language</label>
              <select
                v-model="appearance.language"
                class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              >
                <option value="en">English</option>
                <option value="nl">Nederlands</option>
                <option value="de">Deutsch</option>
                <option value="fr">Francais</option>
                <option value="uk">Ukrainian</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Timezone</label>
              <select
                v-model="appearance.timezone"
                class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              >
                <option value="UTC">UTC</option>
                <option value="Europe/Amsterdam">Europe/Amsterdam</option>
                <option value="Europe/London">Europe/London</option>
                <option value="Europe/Kiev">Europe/Kyiv</option>
                <option value="America/New_York">America/New York</option>
                <option value="America/Los_Angeles">America/Los Angeles</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date Format</label>
              <select
                v-model="appearance.dateFormat"
                class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              >
                <option value="DD/MM/YYYY">DD/MM/YYYY</option>
                <option value="MM/DD/YYYY">MM/DD/YYYY</option>
                <option value="YYYY-MM-DD">YYYY-MM-DD</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Time Format</label>
              <select
                v-model="appearance.timeFormat"
                class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              >
                <option value="24h">24 Hour</option>
                <option value="12h">12 Hour (AM/PM)</option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <!-- Notification Settings -->
      <div v-if="activeCategory === 'notifications'" class="space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Email Notifications</h2>
          <div class="space-y-4">
            <div class="flex items-center justify-between">
              <div>
                <p class="font-medium text-gray-900 dark:text-white">New Events</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Get notified when new events are added</p>
              </div>
              <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" v-model="notifications.newEvents" class="sr-only peer">
                <div class="w-11 h-6 bg-gray-200 dark:bg-gray-700 peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
              </label>
            </div>
            <div class="flex items-center justify-between">
              <div>
                <p class="font-medium text-gray-900 dark:text-white">Event Updates</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Get notified when events are updated</p>
              </div>
              <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" v-model="notifications.eventUpdates" class="sr-only peer">
                <div class="w-11 h-6 bg-gray-200 dark:bg-gray-700 peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
              </label>
            </div>
            <div class="flex items-center justify-between">
              <div>
                <p class="font-medium text-gray-900 dark:text-white">Weekly Digest</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Receive a weekly summary of activity</p>
              </div>
              <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" v-model="notifications.weeklyDigest" class="sr-only peer">
                <div class="w-11 h-6 bg-gray-200 dark:bg-gray-700 peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
              </label>
            </div>
            <div class="flex items-center justify-between">
              <div>
                <p class="font-medium text-gray-900 dark:text-white">Security Alerts</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Get notified about security-related events</p>
              </div>
              <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" v-model="notifications.securityAlerts" class="sr-only peer">
                <div class="w-11 h-6 bg-gray-200 dark:bg-gray-700 peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
              </label>
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Push Notifications</h2>
          <div class="space-y-4">
            <div class="flex items-center justify-between">
              <div>
                <p class="font-medium text-gray-900 dark:text-white">Enable Push Notifications</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Receive notifications in your browser</p>
              </div>
              <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" v-model="notifications.pushEnabled" class="sr-only peer">
                <div class="w-11 h-6 bg-gray-200 dark:bg-gray-700 peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
              </label>
            </div>
            <div class="flex items-center justify-between">
              <div>
                <p class="font-medium text-gray-900 dark:text-white">Sound</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Play sound for notifications</p>
              </div>
              <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" v-model="notifications.sound" class="sr-only peer">
                <div class="w-11 h-6 bg-gray-200 dark:bg-gray-700 peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
              </label>
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Alert Zones</h2>
          <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Get notified about events in specific geographic areas</p>
          <div class="space-y-3">
            <div v-for="zone in alertZones" :key="zone.id" class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
              <div class="flex items-center space-x-3">
                <div class="w-3 h-3 rounded-full" :style="{ backgroundColor: zone.color }"></div>
                <span class="text-gray-900 dark:text-white">{{ zone.name }}</span>
              </div>
              <button class="text-red-600 hover:text-red-700 text-sm">Remove</button>
            </div>
            <button class="w-full py-2 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg text-gray-500 dark:text-gray-400 hover:border-blue-500 hover:text-blue-500 transition-colors">
              + Add Alert Zone
            </button>
          </div>
        </div>
      </div>

      <!-- Map Settings -->
      <div v-if="activeCategory === 'map'" class="space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Default View</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Default Latitude</label>
              <input
                v-model.number="mapSettings.defaultLat"
                type="number"
                step="0.0001"
                class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Default Longitude</label>
              <input
                v-model.number="mapSettings.defaultLng"
                type="number"
                step="0.0001"
                class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Default Zoom Level</label>
              <input
                v-model.number="mapSettings.defaultZoom"
                type="range"
                min="1"
                max="18"
                class="w-full"
              />
              <span class="text-sm text-gray-500 dark:text-gray-400">Level: {{ mapSettings.defaultZoom }}</span>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Max Zoom Level</label>
              <input
                v-model.number="mapSettings.maxZoom"
                type="range"
                min="1"
                max="20"
                class="w-full"
              />
              <span class="text-sm text-gray-500 dark:text-gray-400">Level: {{ mapSettings.maxZoom }}</span>
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Map Style</h2>
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <button
              v-for="style in mapStyles"
              :key="style.value"
              @click="mapSettings.style = style.value"
              :class="[
                'p-3 rounded-lg border-2 transition-all text-center',
                mapSettings.style === style.value
                  ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/30'
                  : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600'
              ]"
            >
              <div class="w-full h-12 rounded mb-2 bg-gray-200 dark:bg-gray-600"></div>
              <span class="text-sm font-medium text-gray-900 dark:text-white">{{ style.label }}</span>
            </button>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Marker Settings</h2>
          <div class="space-y-4">
            <div class="flex items-center justify-between">
              <div>
                <p class="font-medium text-gray-900 dark:text-white">Clustering</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Group nearby markers together</p>
              </div>
              <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" v-model="mapSettings.clustering" class="sr-only peer">
                <div class="w-11 h-6 bg-gray-200 dark:bg-gray-700 peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
              </label>
            </div>
            <div v-if="mapSettings.clustering">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Cluster Radius (px)</label>
              <input
                v-model.number="mapSettings.clusterRadius"
                type="number"
                min="20"
                max="200"
                class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>
            <div class="flex items-center justify-between">
              <div>
                <p class="font-medium text-gray-900 dark:text-white">Show Heatmap</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Display event density as heatmap</p>
              </div>
              <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" v-model="mapSettings.heatmap" class="sr-only peer">
                <div class="w-11 h-6 bg-gray-200 dark:bg-gray-700 peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
              </label>
            </div>
            <div class="flex items-center justify-between">
              <div>
                <p class="font-medium text-gray-900 dark:text-white">Show Labels</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Display labels on markers</p>
              </div>
              <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" v-model="mapSettings.showLabels" class="sr-only peer">
                <div class="w-11 h-6 bg-gray-200 dark:bg-gray-700 peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
              </label>
            </div>
          </div>
        </div>
      </div>

      <!-- Privacy Settings -->
      <div v-if="activeCategory === 'privacy'" class="space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Data Privacy</h2>
          <div class="space-y-4">
            <div class="flex items-center justify-between">
              <div>
                <p class="font-medium text-gray-900 dark:text-white">Profile Visibility</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Who can see your profile information</p>
              </div>
              <select
                v-model="privacy.profileVisibility"
                class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              >
                <option value="public">Public</option>
                <option value="registered">Registered Users</option>
                <option value="private">Private</option>
              </select>
            </div>
            <div class="flex items-center justify-between">
              <div>
                <p class="font-medium text-gray-900 dark:text-white">Activity Status</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Show when you are online</p>
              </div>
              <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" v-model="privacy.showActivityStatus" class="sr-only peer">
                <div class="w-11 h-6 bg-gray-200 dark:bg-gray-700 peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
              </label>
            </div>
            <div class="flex items-center justify-between">
              <div>
                <p class="font-medium text-gray-900 dark:text-white">Search Indexing</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Allow your contributions to be searchable</p>
              </div>
              <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" v-model="privacy.allowSearchIndexing" class="sr-only peer">
                <div class="w-11 h-6 bg-gray-200 dark:bg-gray-700 peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
              </label>
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Data Management</h2>
          <div class="space-y-4">
            <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
              <div class="flex items-center justify-between">
                <div>
                  <p class="font-medium text-gray-900 dark:text-white">Export Your Data</p>
                  <p class="text-sm text-gray-500 dark:text-gray-400">Download a copy of all your data</p>
                </div>
                <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">
                  Export Data
                </button>
              </div>
            </div>
            <div class="p-4 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800">
              <div class="flex items-center justify-between">
                <div>
                  <p class="font-medium text-red-700 dark:text-red-400">Delete Account</p>
                  <p class="text-sm text-red-600 dark:text-red-500">Permanently delete your account and all data</p>
                </div>
                <button class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm">
                  Delete Account
                </button>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Consent Management</h2>
          <div class="space-y-4">
            <div class="flex items-center justify-between">
              <div>
                <p class="font-medium text-gray-900 dark:text-white">Analytics Cookies</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Help us improve by allowing analytics</p>
              </div>
              <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" v-model="privacy.analyticsCookies" class="sr-only peer">
                <div class="w-11 h-6 bg-gray-200 dark:bg-gray-700 peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
              </label>
            </div>
            <div class="flex items-center justify-between">
              <div>
                <p class="font-medium text-gray-900 dark:text-white">Marketing Communications</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Receive updates about new features</p>
              </div>
              <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" v-model="privacy.marketingComms" class="sr-only peer">
                <div class="w-11 h-6 bg-gray-200 dark:bg-gray-700 peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
              </label>
            </div>
          </div>
        </div>
      </div>

      <!-- Security Settings -->
      <div v-if="activeCategory === 'security'" class="space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Two-Factor Authentication</h2>
          <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
            <div class="flex items-center space-x-4">
              <div class="w-12 h-12 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
              </div>
              <div>
                <p class="font-medium text-gray-900 dark:text-white">{{ security.twoFactorEnabled ? 'Enabled' : 'Not Enabled' }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Add an extra layer of security to your account</p>
              </div>
            </div>
            <button
              @click="security.twoFactorEnabled = !security.twoFactorEnabled"
              :class="[
                'px-4 py-2 rounded-lg text-sm font-medium',
                security.twoFactorEnabled
                  ? 'bg-red-100 text-red-700 hover:bg-red-200 dark:bg-red-900/30 dark:text-red-400'
                  : 'bg-blue-600 text-white hover:bg-blue-700'
              ]"
            >
              {{ security.twoFactorEnabled ? 'Disable' : 'Enable' }}
            </button>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Active Sessions</h2>
          <div class="space-y-3">
            <div v-for="session in sessions" :key="session.id" class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
              <div class="flex items-center space-x-4">
                <div class="w-10 h-10 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center">
                  <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                  </svg>
                </div>
                <div>
                  <p class="font-medium text-gray-900 dark:text-white">{{ session.device }}</p>
                  <p class="text-sm text-gray-500 dark:text-gray-400">{{ session.location }} - {{ session.lastActive }}</p>
                </div>
              </div>
              <div class="flex items-center space-x-2">
                <span v-if="session.current" class="px-2 py-1 text-xs font-medium bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 rounded">Current</span>
                <button v-else class="text-red-600 hover:text-red-700 text-sm">Revoke</button>
              </div>
            </div>
          </div>
          <button class="mt-4 text-red-600 hover:text-red-700 text-sm font-medium">
            Revoke All Other Sessions
          </button>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Login History</h2>
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead>
                <tr class="text-left text-gray-500 dark:text-gray-400 border-b dark:border-gray-700">
                  <th class="pb-3 font-medium">Date</th>
                  <th class="pb-3 font-medium">IP Address</th>
                  <th class="pb-3 font-medium">Location</th>
                  <th class="pb-3 font-medium">Status</th>
                </tr>
              </thead>
              <tbody class="divide-y dark:divide-gray-700">
                <tr v-for="login in loginHistory" :key="login.id">
                  <td class="py-3 text-gray-900 dark:text-white">{{ login.date }}</td>
                  <td class="py-3 text-gray-600 dark:text-gray-400">{{ login.ip }}</td>
                  <td class="py-3 text-gray-600 dark:text-gray-400">{{ login.location }}</td>
                  <td class="py-3">
                    <span :class="[
                      'px-2 py-1 text-xs font-medium rounded',
                      login.success
                        ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
                        : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'
                    ]">
                      {{ login.success ? 'Success' : 'Failed' }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- API Settings -->
      <div v-if="activeCategory === 'api'" class="space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">API Tokens</h2>
          <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Manage API tokens for external integrations</p>
          <div class="space-y-3">
            <div v-for="token in apiTokens" :key="token.id" class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
              <div>
                <p class="font-medium text-gray-900 dark:text-white">{{ token.name }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Created: {{ token.created }} - Last used: {{ token.lastUsed }}</p>
              </div>
              <div class="flex items-center space-x-2">
                <span class="text-xs text-gray-500 dark:text-gray-400 font-mono">{{ token.preview }}</span>
                <button class="text-red-600 hover:text-red-700 text-sm">Revoke</button>
              </div>
            </div>
          </div>
          <button class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">
            Create New Token
          </button>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Webhooks</h2>
          <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Configure webhooks to receive event notifications</p>
          <div class="space-y-3">
            <div v-for="webhook in webhooks" :key="webhook.id" class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
              <div>
                <p class="font-medium text-gray-900 dark:text-white">{{ webhook.url }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Events: {{ webhook.events.join(', ') }}</p>
              </div>
              <div class="flex items-center space-x-2">
                <span :class="[
                  'px-2 py-1 text-xs font-medium rounded',
                  webhook.active
                    ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
                    : 'bg-gray-100 text-gray-700 dark:bg-gray-600 dark:text-gray-300'
                ]">
                  {{ webhook.active ? 'Active' : 'Inactive' }}
                </span>
                <button class="text-gray-600 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 text-sm">Edit</button>
                <button class="text-red-600 hover:text-red-700 text-sm">Delete</button>
              </div>
            </div>
          </div>
          <button class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">
            Add Webhook
          </button>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Rate Limits</h2>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg text-center">
              <p class="text-2xl font-bold text-gray-900 dark:text-white">1,000</p>
              <p class="text-sm text-gray-500 dark:text-gray-400">Requests / Hour</p>
            </div>
            <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg text-center">
              <p class="text-2xl font-bold text-gray-900 dark:text-white">847</p>
              <p class="text-sm text-gray-500 dark:text-gray-400">Used This Hour</p>
            </div>
            <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg text-center">
              <p class="text-2xl font-bold text-green-600 dark:text-green-400">153</p>
              <p class="text-sm text-gray-500 dark:text-gray-400">Remaining</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Save Button -->
      <div class="mt-6 flex justify-end">
        <button
          @click="saveSettings"
          :disabled="saving"
          class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center space-x-2"
        >
          <svg v-if="saving" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <span>{{ saving ? 'Saving...' : 'Save Settings' }}</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed } from 'vue';
import { useAuthStore } from '@/stores/auth';

const authStore = useAuthStore();

const activeCategory = ref('profile');

const categories = [
  { id: 'profile', name: 'Profile' },
  { id: 'appearance', name: 'Appearance' },
  { id: 'notifications', name: 'Notifications' },
  { id: 'map', name: 'Map' },
  { id: 'privacy', name: 'Privacy' },
  { id: 'security', name: 'Security' },
  { id: 'api', name: 'API' }
];

const themes = [
  { value: 'light', label: 'Light', preview: 'bg-white border border-gray-200' },
  { value: 'dark', label: 'Dark', preview: 'bg-gray-900' },
  { value: 'system', label: 'System', preview: 'bg-gradient-to-r from-white to-gray-900' }
];

const mapStyles = [
  { value: 'street', label: 'Street' },
  { value: 'satellite', label: 'Satellite' },
  { value: 'terrain', label: 'Terrain' },
  { value: 'dark', label: 'Dark' }
];

const profile = reactive({
  name: authStore.user?.name || 'John Doe',
  email: authStore.user?.email || 'john@example.com',
  phone: '',
  organization: '',
  bio: ''
});

const password = reactive({
  current: '',
  new: '',
  confirm: ''
});

const appearance = reactive({
  theme: 'system',
  compactMode: false,
  sidebarLabels: true,
  animations: true,
  language: 'en',
  timezone: 'UTC',
  dateFormat: 'YYYY-MM-DD',
  timeFormat: '24h'
});

const notifications = reactive({
  newEvents: true,
  eventUpdates: true,
  weeklyDigest: false,
  securityAlerts: true,
  pushEnabled: false,
  sound: true
});

const mapSettings = reactive({
  defaultLat: 50.4501,
  defaultLng: 30.5234,
  defaultZoom: 6,
  maxZoom: 18,
  style: 'street',
  clustering: true,
  clusterRadius: 80,
  heatmap: false,
  showLabels: true
});

const privacy = reactive({
  profileVisibility: 'registered',
  showActivityStatus: true,
  allowSearchIndexing: true,
  analyticsCookies: true,
  marketingComms: false
});

const security = reactive({
  twoFactorEnabled: false
});

const alertZones = ref([
  { id: 1, name: 'Eastern Ukraine', color: '#ef4444' },
  { id: 2, name: 'Black Sea Region', color: '#3b82f6' }
]);

const sessions = ref([
  { id: 1, device: 'Chrome on Windows', location: 'Amsterdam, NL', lastActive: 'Now', current: true },
  { id: 2, device: 'Safari on iPhone', location: 'Amsterdam, NL', lastActive: '2 hours ago', current: false },
  { id: 3, device: 'Firefox on Mac', location: 'London, UK', lastActive: '1 day ago', current: false }
]);

const loginHistory = ref([
  { id: 1, date: '2025-01-16 10:30', ip: '192.168.1.1', location: 'Amsterdam, NL', success: true },
  { id: 2, date: '2025-01-15 14:22', ip: '192.168.1.1', location: 'Amsterdam, NL', success: true },
  { id: 3, date: '2025-01-14 09:15', ip: '10.0.0.1', location: 'London, UK', success: false },
  { id: 4, date: '2025-01-13 16:45', ip: '192.168.1.1', location: 'Amsterdam, NL', success: true }
]);

const apiTokens = ref([
  { id: 1, name: 'Production API', preview: 'sk_live_...x4f2', created: '2025-01-01', lastUsed: '2 hours ago' },
  { id: 2, name: 'Development', preview: 'sk_test_...k9m1', created: '2025-01-10', lastUsed: 'Never' }
]);

const webhooks = ref([
  { id: 1, url: 'https://example.com/webhook', events: ['event.created', 'event.updated'], active: true },
  { id: 2, url: 'https://backup.example.com/hook', events: ['event.deleted'], active: false }
]);

const saving = ref(false);

const userInitials = computed(() => {
  const name = profile.name || 'User';
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
});

const saveSettings = async () => {
  saving.value = true;
  try {
    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 1000));
    alert('Settings saved successfully!');
  } catch (error) {
    alert('Failed to save settings');
  } finally {
    saving.value = false;
  }
};
</script>
