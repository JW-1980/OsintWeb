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
            <router-link to="/explore/equipment" class="text-blue-600 dark:text-blue-400 font-medium">Equipment</router-link>
            <router-link to="/explore/actors" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors">Actors</router-link>
            <router-link to="/explore/conflicts" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors">Conflicts</router-link>
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

    <!-- Loading State -->
    <div v-if="loading" class="pt-24 flex items-center justify-center h-96">
      <div class="text-center">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
        <p class="mt-4 text-gray-600 dark:text-gray-400">Loading equipment details...</p>
      </div>
    </div>

    <!-- Not Found State -->
    <div v-else-if="!equipment" class="pt-24 text-center py-16">
      <svg class="w-24 h-24 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
      <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Equipment Not Found</h2>
      <p class="text-gray-600 dark:text-gray-400 mb-6">The equipment you're looking for doesn't exist in our database.</p>
      <router-link to="/explore/equipment" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
        Browse All Equipment
      </router-link>
    </div>

    <!-- Equipment Detail Content -->
    <div v-else class="pt-24 pb-12 px-4 sm:px-6 lg:px-8">
      <div class="max-w-5xl mx-auto">
        <!-- Back Button -->
        <router-link
          to="/explore/equipment"
          class="inline-flex items-center text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white mb-6 transition-colors"
        >
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
          Back to Equipment
        </router-link>

        <!-- Main Content -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden border border-gray-200 dark:border-gray-700">
          <!-- Image Section -->
          <div class="h-64 md:h-80 bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
            <img
              v-if="equipment.image_url"
              :src="equipment.image_url"
              :alt="equipment.name"
              class="w-full h-full object-cover"
            />
            <svg v-else class="w-32 h-32 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
            </svg>
          </div>

          <!-- Header -->
          <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <div class="flex flex-wrap items-center gap-2 mb-3">
              <span :class="getCategoryBadgeClass(equipment.category)" class="px-3 py-1 text-sm font-medium rounded-full">
                {{ equipment.category }}
              </span>
              <span class="px-3 py-1 text-sm font-medium rounded-full bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                {{ equipment.origin_country }}
              </span>
            </div>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mb-1">{{ equipment.name }}</h1>
            <p v-if="equipment.manufacturer" class="text-gray-500 dark:text-gray-400">{{ equipment.manufacturer }}</p>
          </div>

          <!-- Content -->
          <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
              <!-- Main Info -->
              <div class="lg:col-span-2 space-y-6">
                <!-- Description -->
                <div v-if="equipment.description">
                  <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Description</h2>
                  <p class="text-gray-700 dark:text-gray-300 leading-relaxed">{{ equipment.description }}</p>
                </div>

                <!-- Specifications -->
                <div v-if="equipment.specifications && Object.keys(equipment.specifications).length > 0">
                  <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Specifications</h2>
                  <div class="bg-gray-50 dark:bg-gray-700 rounded-lg overflow-hidden">
                    <table class="w-full">
                      <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                        <tr v-for="(value, key) in equipment.specifications" :key="key" class="hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                          <td class="px-4 py-3 text-sm font-medium text-gray-600 dark:text-gray-400 w-1/3">{{ formatSpecKey(key as string) }}</td>
                          <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ value }}</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>

                <!-- Images Gallery -->
                <div v-if="equipment.images && equipment.images.length > 0">
                  <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Images</h2>
                  <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    <div
                      v-for="(image, index) in equipment.images"
                      :key="index"
                      class="aspect-square bg-gray-200 dark:bg-gray-700 rounded-lg overflow-hidden"
                    >
                      <img :src="image" :alt="equipment.name + ' image ' + (index + 1)" class="w-full h-full object-cover hover:scale-105 transition-transform duration-300" />
                    </div>
                  </div>
                </div>
              </div>

              <!-- Sidebar -->
              <div class="space-y-6">
                <!-- Loss Statistics -->
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                  <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Loss Statistics</h3>
                  <div class="space-y-4">
                    <div>
                      <div class="flex items-center justify-between mb-1">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Destroyed</span>
                        <span class="text-sm font-semibold text-red-600 dark:text-red-400">{{ equipment.destroyed }}</span>
                      </div>
                      <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2">
                        <div
                          class="bg-red-500 h-2 rounded-full"
                          :style="{ width: (equipment.destroyed / equipment.total_losses * 100) + '%' }"
                        ></div>
                      </div>
                    </div>
                    <div>
                      <div class="flex items-center justify-between mb-1">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Captured</span>
                        <span class="text-sm font-semibold text-orange-600 dark:text-orange-400">{{ equipment.captured }}</span>
                      </div>
                      <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2">
                        <div
                          class="bg-orange-500 h-2 rounded-full"
                          :style="{ width: (equipment.captured / equipment.total_losses * 100) + '%' }"
                        ></div>
                      </div>
                    </div>
                    <div>
                      <div class="flex items-center justify-between mb-1">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Abandoned</span>
                        <span class="text-sm font-semibold text-yellow-600 dark:text-yellow-400">{{ equipment.abandoned }}</span>
                      </div>
                      <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2">
                        <div
                          class="bg-yellow-500 h-2 rounded-full"
                          :style="{ width: (equipment.abandoned / equipment.total_losses * 100) + '%' }"
                        ></div>
                      </div>
                    </div>
                    <div>
                      <div class="flex items-center justify-between mb-1">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Damaged</span>
                        <span class="text-sm font-semibold text-blue-600 dark:text-blue-400">{{ equipment.damaged }}</span>
                      </div>
                      <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2">
                        <div
                          class="bg-blue-500 h-2 rounded-full"
                          :style="{ width: (equipment.damaged / equipment.total_losses * 100) + '%' }"
                        ></div>
                      </div>
                    </div>
                    <div class="pt-3 border-t border-gray-200 dark:border-gray-600">
                      <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Total Losses</span>
                        <span class="text-lg font-bold text-gray-900 dark:text-white">{{ equipment.total_losses }}</span>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Equipment Info -->
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                  <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Equipment Info</h3>
                  <dl class="space-y-3">
                    <div>
                      <dt class="text-xs text-gray-500 dark:text-gray-400">Category</dt>
                      <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ equipment.category }}</dd>
                    </div>
                    <div>
                      <dt class="text-xs text-gray-500 dark:text-gray-400">Origin Country</dt>
                      <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ equipment.origin_country }}</dd>
                    </div>
                    <div v-if="equipment.manufacturer">
                      <dt class="text-xs text-gray-500 dark:text-gray-400">Manufacturer</dt>
                      <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ equipment.manufacturer }}</dd>
                    </div>
                    <div v-if="equipment.year_introduced">
                      <dt class="text-xs text-gray-500 dark:text-gray-400">Year Introduced</dt>
                      <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ equipment.year_introduced }}</dd>
                    </div>
                  </dl>
                </div>

                <!-- CTA -->
                <div class="bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-lg p-4 border border-blue-200 dark:border-blue-800">
                  <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">Want More Data?</h3>
                  <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Sign up to access detailed loss timelines, sighting history, and export capabilities.</p>
                  <router-link
                    to="/register"
                    class="block w-full text-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors"
                  >
                    Sign Up Free
                  </router-link>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Related Equipment -->
        <div v-if="relatedEquipment.length > 0" class="mt-8">
          <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Related Equipment</h2>
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <router-link
              v-for="related in relatedEquipment"
              :key="related.id"
              :to="{ name: 'explore-equipment-detail', params: { id: related.id } }"
              class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700 hover:shadow-lg transition-all"
            >
              <span :class="getCategoryBadgeClass(related.category)" class="px-2 py-1 text-xs font-medium rounded-full">
                {{ related.category }}
              </span>
              <h3 class="text-sm font-semibold text-gray-900 dark:text-white mt-2">{{ related.name }}</h3>
              <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ related.total_losses }} losses</p>
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { useRoute } from 'vue-router';
import { useThemeStore } from '@/stores/theme';

const route = useRoute();
const themeStore = useThemeStore();
const isDark = computed(() => themeStore.isDark);

const toggleTheme = () => {
  themeStore.toggleTheme();
};

const loading = ref(false);

interface EquipmentDetail {
  id: number;
  name: string;
  category: string;
  manufacturer: string;
  origin_country: string;
  description: string;
  image_url: string | null;
  images: string[];
  specifications: Record<string, string>;
  year_introduced: number | null;
  destroyed: number;
  captured: number;
  abandoned: number;
  damaged: number;
  total_losses: number;
}

interface RelatedEquipment {
  id: number;
  name: string;
  category: string;
  total_losses: number;
}

const equipment = ref<EquipmentDetail | null>(null);
const relatedEquipment = ref<RelatedEquipment[]>([]);

const fetchEquipment = (id: string) => {
  loading.value = true;

  // Simulated data - in production this would be an API call
  setTimeout(() => {
    equipment.value = {
      id: parseInt(id),
      name: 'T-72B3',
      category: 'Tanks',
      manufacturer: 'Uralvagonzavod',
      origin_country: 'Russia',
      description: 'The T-72B3 is a modernized version of the Soviet-era T-72 main battle tank. It features improved fire control systems, reactive armor, and upgraded electronics. The tank has been widely deployed in various conflicts and represents a significant portion of tracked armored losses documented in our database.',
      image_url: null,
      images: [],
      specifications: {
        'weight': '46.5 tonnes',
        'length': '9.53 m (gun forward)',
        'width': '3.59 m',
        'height': '2.23 m',
        'crew': '3',
        'main_armament': '125mm 2A46M-5 smoothbore gun',
        'secondary_armament': '7.62mm PKMT, 12.7mm Kord',
        'engine': 'V-92S2F diesel, 1,130 hp',
        'max_speed': '60 km/h',
        'range': '700 km',
        'armor': 'Composite with Kontakt-5 ERA'
      },
      year_introduced: 2010,
      destroyed: 892,
      captured: 234,
      abandoned: 156,
      damaged: 78,
      total_losses: 1360
    };

    relatedEquipment.value = [
      { id: 2, name: 'T-80BVM', category: 'Tanks', total_losses: 391 },
      { id: 3, name: 'T-90M', category: 'Tanks', total_losses: 67 },
      { id: 4, name: 'BMP-2', category: 'IFV', total_losses: 1122 },
      { id: 13, name: 'T-64BV', category: 'Tanks', total_losses: 258 }
    ];

    loading.value = false;
  }, 500);
};

const getCategoryBadgeClass = (category: string) => {
  const classes: Record<string, string> = {
    'Tanks': 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400',
    'IFV': 'bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400',
    'APC': 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400',
    'Artillery': 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400',
    'MLRS': 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400',
    'Aircraft': 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400',
    'Helicopter': 'bg-pink-100 dark:bg-pink-900/30 text-pink-700 dark:text-pink-400',
    'UAV': 'bg-cyan-100 dark:bg-cyan-900/30 text-cyan-700 dark:text-cyan-400',
    'Air Defense': 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400'
  };
  return classes[category] || 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-400';
};

const formatSpecKey = (key: string) => {
  return key.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
};

onMounted(() => {
  fetchEquipment(route.params.id as string);
});

watch(() => route.params.id, (newId) => {
  if (newId) {
    fetchEquipment(newId as string);
  }
});
</script>
