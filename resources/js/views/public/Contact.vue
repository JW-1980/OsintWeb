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
            <router-link to="/about" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors">About</router-link>
            <router-link to="/contact" class="text-blue-600 dark:text-blue-400 font-medium">Contact</router-link>
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

    <!-- Main Content -->
    <div class="pt-24 pb-12 px-4 sm:px-6 lg:px-8">
      <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-12">
          <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">Contact Us</h1>
          <p class="text-xl text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
            Have questions, feedback, or want to collaborate? We'd love to hear from you.
          </p>
        </div>

        <div class="grid md:grid-cols-3 gap-8">
          <!-- Contact Info -->
          <div class="space-y-6">
            <!-- Email -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 border border-gray-200 dark:border-gray-700">
              <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
              </div>
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Email</h3>
              <p class="text-gray-600 dark:text-gray-400 mb-2">For general inquiries:</p>
              <a href="mailto:contact@osintweb.org" class="text-blue-600 dark:text-blue-400 hover:underline">contact@osintweb.org</a>
              <p class="text-gray-600 dark:text-gray-400 mt-3 mb-2">For press inquiries:</p>
              <a href="mailto:press@osintweb.org" class="text-blue-600 dark:text-blue-400 hover:underline">press@osintweb.org</a>
            </div>

            <!-- Social Links -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 border border-gray-200 dark:border-gray-700">
              <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" />
                </svg>
              </div>
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Follow Us</h3>
              <div class="space-y-3">
                <a href="#" class="flex items-center text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                  <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84" />
                  </svg>
                  @osintweb
                </a>
                <a href="#" class="flex items-center text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                  <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 24 24">
                    <path fill-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z" clip-rule="evenodd" />
                  </svg>
                  GitHub
                </a>
                <a href="#" class="flex items-center text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                  <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 24 24">
                    <path fill-rule="evenodd" d="M19 3a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h14m-.5 15.5v-5.3a3.26 3.26 0 00-3.26-3.26c-.85 0-1.84.52-2.32 1.3v-1.11h-2.79v8.37h2.79v-4.93c0-.77.62-1.4 1.39-1.4a1.4 1.4 0 011.4 1.4v4.93h2.79M6.88 8.56a1.68 1.68 0 001.68-1.68c0-.93-.75-1.69-1.68-1.69a1.69 1.69 0 00-1.69 1.69c0 .93.76 1.68 1.69 1.68m1.39 9.94v-8.37H5.5v8.37h2.77z" clip-rule="evenodd" />
                  </svg>
                  LinkedIn
                </a>
                <a href="#" class="flex items-center text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                  <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
                  </svg>
                  Telegram
                </a>
              </div>
            </div>

            <!-- Secure Contact -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 border border-gray-200 dark:border-gray-700">
              <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
              </div>
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Secure Contact</h3>
              <p class="text-gray-600 dark:text-gray-400 mb-3">For sensitive communications:</p>
              <p class="text-sm text-gray-500 dark:text-gray-400">PGP Key available on request</p>
              <p class="text-sm text-gray-500 dark:text-gray-400">Signal: +1-XXX-XXX-XXXX</p>
            </div>
          </div>

          <!-- Contact Form -->
          <div class="md:col-span-2">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 md:p-8 border border-gray-200 dark:border-gray-700">
              <!-- Success Message -->
              <div v-if="submitted" class="text-center py-12">
                <div class="w-16 h-16 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
                  <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Message Sent!</h3>
                <p class="text-gray-600 dark:text-gray-400 mb-6">Thank you for reaching out. We'll get back to you as soon as possible.</p>
                <button
                  @click="resetForm"
                  class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors"
                >
                  Send Another Message
                </button>
              </div>

              <!-- Form -->
              <form v-else @submit.prevent="handleSubmit" class="space-y-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-6">Send us a Message</h2>

                <div class="grid md:grid-cols-2 gap-6">
                  <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                      Name <span class="text-red-500">*</span>
                    </label>
                    <input
                      id="name"
                      v-model="form.name"
                      type="text"
                      required
                      placeholder="Your name"
                      class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                    />
                  </div>
                  <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                      Email <span class="text-red-500">*</span>
                    </label>
                    <input
                      id="email"
                      v-model="form.email"
                      type="email"
                      required
                      placeholder="you@example.com"
                      class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                    />
                  </div>
                </div>

                <div>
                  <label for="subject" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Subject <span class="text-red-500">*</span>
                  </label>
                  <select
                    id="subject"
                    v-model="form.subject"
                    required
                    class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                  >
                    <option value="">Select a subject</option>
                    <option value="general">General Inquiry</option>
                    <option value="support">Technical Support</option>
                    <option value="feedback">Feedback</option>
                    <option value="partnership">Partnership Opportunity</option>
                    <option value="press">Press / Media</option>
                    <option value="data">Data Request</option>
                    <option value="other">Other</option>
                  </select>
                </div>

                <div>
                  <label for="organization" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Organization
                    <span class="text-gray-400 font-normal">(optional)</span>
                  </label>
                  <input
                    id="organization"
                    v-model="form.organization"
                    type="text"
                    placeholder="Your organization"
                    class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                  />
                </div>

                <div>
                  <label for="message" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Message <span class="text-red-500">*</span>
                  </label>
                  <textarea
                    id="message"
                    v-model="form.message"
                    required
                    rows="6"
                    placeholder="Your message..."
                    class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors resize-y"
                  ></textarea>
                </div>

                <!-- Privacy Notice -->
                <div class="flex items-start space-x-3">
                  <input
                    type="checkbox"
                    v-model="form.privacy"
                    id="privacy"
                    required
                    class="mt-1 w-4 h-4 text-blue-600 rounded focus:ring-blue-500"
                  />
                  <label for="privacy" class="text-sm text-gray-600 dark:text-gray-400">
                    I agree to the <a href="#" class="text-blue-600 dark:text-blue-400 hover:underline">Privacy Policy</a> and understand that my information will be processed in accordance with GDPR requirements.
                  </label>
                </div>

                <button
                  type="submit"
                  :disabled="submitting"
                  class="w-full py-3 px-6 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <span v-if="submitting" class="flex items-center justify-center">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Sending...
                  </span>
                  <span v-else>Send Message</span>
                </button>
              </form>
            </div>
          </div>
        </div>

        <!-- FAQ Section -->
        <div class="mt-16">
          <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-8 text-center">Frequently Asked Questions</h2>
          <div class="grid md:grid-cols-2 gap-6 max-w-4xl mx-auto">
            <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
              <h3 class="font-semibold text-gray-900 dark:text-white mb-2">How do I submit a tip?</h3>
              <p class="text-gray-600 dark:text-gray-400">Visit our <router-link to="/submit-tip" class="text-blue-600 dark:text-blue-400 hover:underline">Submit Tip</router-link> page to anonymously share information about events you've witnessed or have knowledge of.</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
              <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Is the data free to use?</h3>
              <p class="text-gray-600 dark:text-gray-400">Public data is available for non-commercial research and educational purposes. For commercial use or API access, please contact us.</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
              <h3 class="font-semibold text-gray-900 dark:text-white mb-2">How do you verify events?</h3>
              <p class="text-gray-600 dark:text-gray-400">Our verification team uses multiple sources, geolocation analysis, and cross-referencing to verify reported events before marking them as confirmed.</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
              <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Can I contribute as an analyst?</h3>
              <p class="text-gray-600 dark:text-gray-400">Yes! Create an account and contact us about joining our verification team. We're always looking for skilled OSINT analysts.</p>
            </div>
          </div>
        </div>
      </div>
    </div>

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
  name: '',
  email: '',
  subject: '',
  organization: '',
  message: '',
  privacy: false
});

const handleSubmit = async () => {
  submitting.value = true;

  // Simulate API call
  await new Promise(resolve => setTimeout(resolve, 1500));

  submitted.value = true;
  submitting.value = false;
};

const resetForm = () => {
  form.name = '';
  form.email = '';
  form.subject = '';
  form.organization = '';
  form.message = '';
  form.privacy = false;
  submitted.value = false;
};
</script>
