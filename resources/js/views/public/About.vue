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
            <router-link to="/about" class="text-blue-600 dark:text-blue-400 font-medium">About</router-link>
            <router-link to="/contact" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors">Contact</router-link>
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
            <router-link to="/register" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-medium rounded-lg transition-all">
              Sign Up
            </router-link>
          </div>
        </div>
      </div>
    </nav>

    <!-- Hero Section -->
    <section class="pt-32 pb-20 px-4 sm:px-6 lg:px-8 bg-gradient-to-b from-blue-50 to-white dark:from-gray-800 dark:to-gray-900">
      <div class="max-w-4xl mx-auto text-center">
        <h1 class="text-4xl md:text-5xl font-bold text-gray-900 dark:text-white mb-6">
          About
          <span class="bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">OsintWeb</span>
        </h1>
        <p class="text-xl text-gray-600 dark:text-gray-400 max-w-3xl mx-auto">
          An open-source intelligence platform dedicated to tracking, analyzing, and visualizing global events with accuracy and transparency.
        </p>
      </div>
    </section>

    <!-- Mission Section -->
    <section class="py-16 px-4 sm:px-6 lg:px-8">
      <div class="max-w-7xl mx-auto">
        <div class="grid md:grid-cols-2 gap-12 items-center">
          <div>
            <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-6">Our Mission</h2>
            <p class="text-lg text-gray-600 dark:text-gray-400 mb-6">
              OsintWeb was created to provide researchers, journalists, analysts, and the public with a comprehensive platform for tracking and understanding global events in real-time.
            </p>
            <p class="text-lg text-gray-600 dark:text-gray-400 mb-6">
              We believe that access to accurate, verified information is essential for informed decision-making and public awareness. Our platform combines cutting-edge technology with rigorous verification processes to ensure data integrity.
            </p>
            <p class="text-lg text-gray-600 dark:text-gray-400">
              By making OSINT data accessible and actionable, we aim to support transparency, accountability, and understanding of complex global situations.
            </p>
          </div>
          <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 border border-gray-200 dark:border-gray-700">
            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-6">Core Values</h3>
            <ul class="space-y-4">
              <li class="flex items-start">
                <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
                  <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
                <div>
                  <h4 class="font-semibold text-gray-900 dark:text-white">Accuracy</h4>
                  <p class="text-gray-600 dark:text-gray-400">Rigorous verification of all reported events</p>
                </div>
              </li>
              <li class="flex items-start">
                <div class="w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
                  <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                </div>
                <div>
                  <h4 class="font-semibold text-gray-900 dark:text-white">Transparency</h4>
                  <p class="text-gray-600 dark:text-gray-400">Open methodology and source attribution</p>
                </div>
              </li>
              <li class="flex items-start">
                <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
                  <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                  </svg>
                </div>
                <div>
                  <h4 class="font-semibold text-gray-900 dark:text-white">Privacy</h4>
                  <p class="text-gray-600 dark:text-gray-400">GDPR-compliant data handling</p>
                </div>
              </li>
              <li class="flex items-start">
                <div class="w-10 h-10 bg-orange-100 dark:bg-orange-900/30 rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
                  <svg class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                  </svg>
                </div>
                <div>
                  <h4 class="font-semibold text-gray-900 dark:text-white">Community</h4>
                  <p class="text-gray-600 dark:text-gray-400">Collaborative approach to intelligence gathering</p>
                </div>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </section>

    <!-- Features Section -->
    <section class="py-16 px-4 sm:px-6 lg:px-8 bg-white dark:bg-gray-800">
      <div class="max-w-7xl mx-auto">
        <div class="text-center mb-12">
          <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Platform Features</h2>
          <p class="text-lg text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
            Comprehensive tools for OSINT analysis and visualization
          </p>
        </div>

        <div class="grid md:grid-cols-3 gap-8">
          <div class="bg-gray-50 dark:bg-gray-700 rounded-xl p-6">
            <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center mb-4">
              <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
              </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Interactive Mapping</h3>
            <p class="text-gray-600 dark:text-gray-400">Real-time visualization of events and control zones on an interactive map with multiple layers and filtering options.</p>
          </div>

          <div class="bg-gray-50 dark:bg-gray-700 rounded-xl p-6">
            <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center mb-4">
              <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
              </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Event Tracking</h3>
            <p class="text-gray-600 dark:text-gray-400">Comprehensive event database with categorization, confidence levels, source attribution, and verification status.</p>
          </div>

          <div class="bg-gray-50 dark:bg-gray-700 rounded-xl p-6">
            <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center mb-4">
              <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
              </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Analytics Dashboard</h3>
            <p class="text-gray-600 dark:text-gray-400">Powerful analytics tools with trend analysis, data visualization, and customizable reports for deeper insights.</p>
          </div>

          <div class="bg-gray-50 dark:bg-gray-700 rounded-xl p-6">
            <div class="w-12 h-12 bg-yellow-100 dark:bg-yellow-900/30 rounded-lg flex items-center justify-center mb-4">
              <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
              </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Equipment Database</h3>
            <p class="text-gray-600 dark:text-gray-400">Detailed military equipment tracking with specifications, sighting history, and loss statistics across conflicts.</p>
          </div>

          <div class="bg-gray-50 dark:bg-gray-700 rounded-xl p-6">
            <div class="w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-lg flex items-center justify-center mb-4">
              <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9" />
              </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Actor Profiles</h3>
            <p class="text-gray-600 dark:text-gray-400">Comprehensive database of state and non-state actors with relationships, activity levels, and event associations.</p>
          </div>

          <div class="bg-gray-50 dark:bg-gray-700 rounded-xl p-6">
            <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg flex items-center justify-center mb-4">
              <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
              </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Real-time Alerts</h3>
            <p class="text-gray-600 dark:text-gray-400">Customizable notification system for new events, verification updates, and significant developments in tracked areas.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Team Section -->
    <section class="py-16 px-4 sm:px-6 lg:px-8">
      <div class="max-w-7xl mx-auto">
        <div class="text-center mb-12">
          <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Our Team</h2>
          <p class="text-lg text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
            A dedicated group of analysts, developers, and researchers committed to OSINT excellence
          </p>
        </div>

        <div class="grid md:grid-cols-4 gap-8">
          <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 text-center border border-gray-200 dark:border-gray-700">
            <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center mx-auto mb-4">
              <span class="text-2xl font-bold text-white">JD</span>
            </div>
            <h3 class="font-semibold text-gray-900 dark:text-white">John Doe</h3>
            <p class="text-sm text-blue-600 dark:text-blue-400 mb-2">Founder & Lead Analyst</p>
            <p class="text-sm text-gray-600 dark:text-gray-400">15+ years in intelligence analysis</p>
          </div>

          <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 text-center border border-gray-200 dark:border-gray-700">
            <div class="w-20 h-20 bg-gradient-to-br from-green-500 to-emerald-600 rounded-full flex items-center justify-center mx-auto mb-4">
              <span class="text-2xl font-bold text-white">JS</span>
            </div>
            <h3 class="font-semibold text-gray-900 dark:text-white">Jane Smith</h3>
            <p class="text-sm text-green-600 dark:text-green-400 mb-2">Head of Verification</p>
            <p class="text-sm text-gray-600 dark:text-gray-400">Expert in geolocation and verification</p>
          </div>

          <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 text-center border border-gray-200 dark:border-gray-700">
            <div class="w-20 h-20 bg-gradient-to-br from-purple-500 to-pink-600 rounded-full flex items-center justify-center mx-auto mb-4">
              <span class="text-2xl font-bold text-white">MJ</span>
            </div>
            <h3 class="font-semibold text-gray-900 dark:text-white">Mike Johnson</h3>
            <p class="text-sm text-purple-600 dark:text-purple-400 mb-2">Lead Developer</p>
            <p class="text-sm text-gray-600 dark:text-gray-400">Full-stack development & GIS</p>
          </div>

          <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 text-center border border-gray-200 dark:border-gray-700">
            <div class="w-20 h-20 bg-gradient-to-br from-orange-500 to-red-600 rounded-full flex items-center justify-center mx-auto mb-4">
              <span class="text-2xl font-bold text-white">SW</span>
            </div>
            <h3 class="font-semibold text-gray-900 dark:text-white">Sarah Williams</h3>
            <p class="text-sm text-orange-600 dark:text-orange-400 mb-2">Research Director</p>
            <p class="text-sm text-gray-600 dark:text-gray-400">Conflict & security research</p>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="py-16 px-4 sm:px-6 lg:px-8 bg-gradient-to-r from-blue-600 to-indigo-600">
      <div class="max-w-4xl mx-auto text-center">
        <h2 class="text-3xl font-bold text-white mb-6">Join Our Community</h2>
        <p class="text-xl text-blue-100 mb-8">
          Create a free account to access advanced features, contribute to our database, and stay informed about global events.
        </p>
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
          <router-link
            to="/register"
            class="px-8 py-3 bg-white text-blue-600 font-semibold rounded-lg hover:bg-gray-100 transition-colors"
          >
            Get Started Free
          </router-link>
          <router-link
            to="/contact"
            class="px-8 py-3 bg-blue-500 text-white font-semibold rounded-lg hover:bg-blue-400 transition-colors"
          >
            Contact Us
          </router-link>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12 px-4 sm:px-6 lg:px-8">
      <div class="max-w-7xl mx-auto">
        <div class="grid md:grid-cols-4 gap-8">
          <div>
            <div class="flex items-center space-x-2 mb-4">
              <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                </svg>
              </div>
              <span class="text-xl font-bold">OsintWeb</span>
            </div>
            <p class="text-gray-400">Open Source Intelligence Platform</p>
          </div>
          <div>
            <h3 class="font-semibold mb-4">Explore</h3>
            <ul class="space-y-2 text-gray-400">
              <li><router-link to="/explore" class="hover:text-white transition-colors">Map</router-link></li>
              <li><router-link to="/explore/events" class="hover:text-white transition-colors">Events</router-link></li>
              <li><router-link to="/explore/equipment" class="hover:text-white transition-colors">Equipment</router-link></li>
              <li><router-link to="/explore/conflicts" class="hover:text-white transition-colors">Conflicts</router-link></li>
            </ul>
          </div>
          <div>
            <h3 class="font-semibold mb-4">Company</h3>
            <ul class="space-y-2 text-gray-400">
              <li><router-link to="/about" class="hover:text-white transition-colors">About</router-link></li>
              <li><router-link to="/contact" class="hover:text-white transition-colors">Contact</router-link></li>
              <li><router-link to="/submit-tip" class="hover:text-white transition-colors">Submit Tip</router-link></li>
            </ul>
          </div>
          <div>
            <h3 class="font-semibold mb-4">Legal</h3>
            <ul class="space-y-2 text-gray-400">
              <li><a href="#" class="hover:text-white transition-colors">Privacy Policy</a></li>
              <li><a href="#" class="hover:text-white transition-colors">Terms of Service</a></li>
              <li><a href="#" class="hover:text-white transition-colors">GDPR</a></li>
            </ul>
          </div>
        </div>
        <div class="mt-12 pt-8 border-t border-gray-800 text-center text-gray-400">
          <p>&copy; {{ new Date().getFullYear() }} OsintWeb. All rights reserved.</p>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useThemeStore } from '@/stores/theme';

const themeStore = useThemeStore();
const isDark = computed(() => themeStore.isDark);

const toggleTheme = () => {
  themeStore.toggleTheme();
};
</script>
