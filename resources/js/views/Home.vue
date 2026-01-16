<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <!-- Navigation -->
    <nav class="fixed top-0 left-0 right-0 z-50 bg-white/80 dark:bg-gray-900/80 backdrop-blur-md border-b border-gray-200 dark:border-gray-800">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
          <div class="flex items-center space-x-2">
            <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
              </svg>
            </div>
            <span class="text-xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">OsintWeb</span>
          </div>
          <div class="hidden md:flex items-center space-x-8">
            <router-link to="/explore" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors">Explore Map</router-link>
            <router-link to="/explore/events" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors">Events</router-link>
            <router-link to="/explore/equipment" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors">Equipment</router-link>
            <router-link to="/explore/conflicts" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors">Conflicts</router-link>
            <a href="#features" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors">Features</a>
            <router-link to="/about" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors">About</router-link>
          </div>
          <div class="flex items-center space-x-4">
            <button @click="toggleTheme" class="p-2 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
              <svg v-if="themeStore.isDark" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
              </svg>
              <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
              </svg>
            </button>
            <router-link to="/login" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors">Login</router-link>
            <router-link to="/register" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-medium rounded-lg transition-all">
              Get Started
            </router-link>
          </div>
        </div>
      </div>
    </nav>

    <!-- Hero Section -->
    <section class="pt-32 pb-20 px-4 sm:px-6 lg:px-8">
      <div class="max-w-7xl mx-auto">
        <div class="text-center">
          <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-gray-900 dark:text-white mb-6">
            Open Source Intelligence
            <span class="bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">Platform</span>
          </h1>
          <p class="max-w-2xl mx-auto text-xl text-gray-600 dark:text-gray-400 mb-10">
            Track, analyze, and visualize global events with real-time mapping, comprehensive data management, and powerful analytics tools.
          </p>
          <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <router-link to="/register" class="w-full sm:w-auto px-8 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all">
              Start Free Trial
            </router-link>
            <a href="#features" class="w-full sm:w-auto px-8 py-3 bg-white dark:bg-gray-800 text-gray-900 dark:text-white font-semibold rounded-lg shadow-lg hover:shadow-xl border border-gray-200 dark:border-gray-700 transition-all">
              Learn More
            </a>
          </div>
        </div>

        <!-- Hero Image/Map Preview -->
        <div class="mt-16 relative">
          <div class="absolute inset-0 bg-gradient-to-t from-gray-50 dark:from-gray-900 to-transparent z-10 pointer-events-none h-32 bottom-0 top-auto"></div>
          <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl overflow-hidden border border-gray-200 dark:border-gray-700">
            <div class="h-96 bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-gray-800 dark:to-gray-700 flex items-center justify-center">
              <div class="text-center p-8">
                <svg class="w-24 h-24 mx-auto text-blue-500 dark:text-blue-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                </svg>
                <p class="text-xl font-medium text-gray-600 dark:text-gray-300">Interactive Global Map</p>
                <p class="text-gray-500 dark:text-gray-400 mt-2">Visualize events, control zones, and equipment in real-time</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Stats Section -->
    <section class="py-16 bg-white dark:bg-gray-800 border-y border-gray-200 dark:border-gray-700">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
          <div class="text-center">
            <p class="text-4xl font-bold text-gray-900 dark:text-white">10K+</p>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Events Tracked</p>
          </div>
          <div class="text-center">
            <p class="text-4xl font-bold text-gray-900 dark:text-white">500+</p>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Active Users</p>
          </div>
          <div class="text-center">
            <p class="text-4xl font-bold text-gray-900 dark:text-white">50+</p>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Conflicts Monitored</p>
          </div>
          <div class="text-center">
            <p class="text-4xl font-bold text-gray-900 dark:text-white">1K+</p>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Equipment Items</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 px-4 sm:px-6 lg:px-8">
      <div class="max-w-7xl mx-auto">
        <div class="text-center mb-16">
          <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white mb-4">Powerful Features</h2>
          <p class="text-xl text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
            Everything you need to track, analyze, and report on global events
          </p>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
          <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-lg border border-gray-200 dark:border-gray-700 hover:shadow-xl transition-shadow">
            <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center mb-4">
              <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
              </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Interactive Mapping</h3>
            <p class="text-gray-600 dark:text-gray-400">Real-time event visualization with multiple map layers and drawing tools.</p>
          </div>

          <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-lg border border-gray-200 dark:border-gray-700 hover:shadow-xl transition-shadow">
            <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center mb-4">
              <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
              </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Event Tracking</h3>
            <p class="text-gray-600 dark:text-gray-400">Comprehensive event categorization with confidence levels and source tracking.</p>
          </div>

          <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-lg border border-gray-200 dark:border-gray-700 hover:shadow-xl transition-shadow">
            <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center mb-4">
              <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
              </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Advanced Analytics</h3>
            <p class="text-gray-600 dark:text-gray-400">Powerful dashboards and reports with trend analysis and data visualization.</p>
          </div>

          <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-lg border border-gray-200 dark:border-gray-700 hover:shadow-xl transition-shadow">
            <div class="w-12 h-12 bg-yellow-100 dark:bg-yellow-900/30 rounded-lg flex items-center justify-center mb-4">
              <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Control Zones</h3>
            <p class="text-gray-600 dark:text-gray-400">Track territorial control with polygon drawing and temporal data.</p>
          </div>

          <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-lg border border-gray-200 dark:border-gray-700 hover:shadow-xl transition-shadow">
            <div class="w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-lg flex items-center justify-center mb-4">
              <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
              </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Actor Database</h3>
            <p class="text-gray-600 dark:text-gray-400">Comprehensive database of state and non-state actors with relationships.</p>
          </div>

          <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-lg border border-gray-200 dark:border-gray-700 hover:shadow-xl transition-shadow">
            <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg flex items-center justify-center mb-4">
              <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
              </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Equipment Tracking</h3>
            <p class="text-gray-600 dark:text-gray-400">Military equipment database with specifications and sighting history.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-20 px-4 sm:px-6 lg:px-8 bg-white dark:bg-gray-800 border-y border-gray-200 dark:border-gray-700">
      <div class="max-w-7xl mx-auto">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
          <div>
            <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white mb-6">Built for OSINT Professionals</h2>
            <p class="text-lg text-gray-600 dark:text-gray-400 mb-6">
              OsintWeb is designed from the ground up for open source intelligence analysts, researchers, and journalists who need to track and analyze global events.
            </p>
            <ul class="space-y-4">
              <li class="flex items-start">
                <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span class="text-gray-600 dark:text-gray-400">Source verification and confidence tracking</span>
              </li>
              <li class="flex items-start">
                <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span class="text-gray-600 dark:text-gray-400">Collaborative team workflows</span>
              </li>
              <li class="flex items-start">
                <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span class="text-gray-600 dark:text-gray-400">Export capabilities for reports and briefings</span>
              </li>
              <li class="flex items-start">
                <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span class="text-gray-600 dark:text-gray-400">Privacy-focused with GDPR compliance</span>
              </li>
            </ul>
          </div>
          <div class="bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-gray-700 dark:to-gray-600 rounded-2xl p-8">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Quick Start</h3>
              <div class="space-y-4">
                <div class="flex items-center space-x-3">
                  <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center text-blue-600 dark:text-blue-400 font-semibold text-sm">1</div>
                  <span class="text-gray-600 dark:text-gray-400">Create your free account</span>
                </div>
                <div class="flex items-center space-x-3">
                  <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center text-blue-600 dark:text-blue-400 font-semibold text-sm">2</div>
                  <span class="text-gray-600 dark:text-gray-400">Set up your workspace</span>
                </div>
                <div class="flex items-center space-x-3">
                  <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center text-blue-600 dark:text-blue-400 font-semibold text-sm">3</div>
                  <span class="text-gray-600 dark:text-gray-400">Start tracking events</span>
                </div>
              </div>
              <router-link to="/register" class="mt-6 block w-full text-center px-4 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-medium rounded-lg transition-all">
                Get Started Free
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-20 px-4 sm:px-6 lg:px-8">
      <div class="max-w-3xl mx-auto text-center">
        <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white mb-4">Get in Touch</h2>
        <p class="text-xl text-gray-600 dark:text-gray-400 mb-8">
          Have questions? We'd love to hear from you.
        </p>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-8 border border-gray-200 dark:border-gray-700">
          <form class="space-y-6">
            <div class="grid md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Name</label>
                <input type="text" class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Your name" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email</label>
                <input type="email" class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="you@example.com" />
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Message</label>
              <textarea rows="4" class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Your message"></textarea>
            </div>
            <button type="submit" class="w-full px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-medium rounded-lg transition-all">
              Send Message
            </button>
          </form>
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
            <p class="text-gray-400">Open Source Intelligence Platform for tracking and analyzing global events.</p>
          </div>
          <div>
            <h3 class="font-semibold mb-4">Product</h3>
            <ul class="space-y-2 text-gray-400">
              <li><a href="#features" class="hover:text-white transition-colors">Features</a></li>
              <li><a href="#" class="hover:text-white transition-colors">Pricing</a></li>
              <li><a href="#" class="hover:text-white transition-colors">Documentation</a></li>
              <li><a href="#" class="hover:text-white transition-colors">API</a></li>
            </ul>
          </div>
          <div>
            <h3 class="font-semibold mb-4">Company</h3>
            <ul class="space-y-2 text-gray-400">
              <li><a href="#about" class="hover:text-white transition-colors">About</a></li>
              <li><a href="#" class="hover:text-white transition-colors">Blog</a></li>
              <li><a href="#" class="hover:text-white transition-colors">Careers</a></li>
              <li><a href="#contact" class="hover:text-white transition-colors">Contact</a></li>
            </ul>
          </div>
          <div>
            <h3 class="font-semibold mb-4">Legal</h3>
            <ul class="space-y-2 text-gray-400">
              <li><a href="#" class="hover:text-white transition-colors">Privacy Policy</a></li>
              <li><a href="#" class="hover:text-white transition-colors">Terms of Service</a></li>
              <li><a href="#" class="hover:text-white transition-colors">Cookie Policy</a></li>
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
import { useThemeStore } from '@/stores/theme';

const themeStore = useThemeStore();

const toggleTheme = () => {
  themeStore.toggleTheme();
};
</script>
