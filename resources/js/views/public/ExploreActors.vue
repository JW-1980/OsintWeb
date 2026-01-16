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
            <router-link to="/explore/actors" class="text-blue-600 dark:text-blue-400 font-medium">Actors</router-link>
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

    <!-- Main Content -->
    <div class="pt-24 pb-12 px-4 sm:px-6 lg:px-8">
      <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
          <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Actor Database</h1>
          <p class="mt-2 text-gray-600 dark:text-gray-400">State and non-state actors tracked in our OSINT database</p>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border border-gray-200 dark:border-gray-700">
            <p class="text-sm text-gray-500 dark:text-gray-400">Total Actors</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.total }}</p>
          </div>
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border border-gray-200 dark:border-gray-700">
            <p class="text-sm text-gray-500 dark:text-gray-400">State Actors</p>
            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ stats.state }}</p>
          </div>
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border border-gray-200 dark:border-gray-700">
            <p class="text-sm text-gray-500 dark:text-gray-400">Non-State Actors</p>
            <p class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ stats.nonState }}</p>
          </div>
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border border-gray-200 dark:border-gray-700">
            <p class="text-sm text-gray-500 dark:text-gray-400">Active in Conflicts</p>
            <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ stats.active }}</p>
          </div>
        </div>

        <!-- Filters -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 mb-8 border border-gray-200 dark:border-gray-700">
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="md:col-span-2">
              <div class="relative">
                <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input
                  v-model="filters.search"
                  type="text"
                  placeholder="Search actors..."
                  class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
              </div>
            </div>
            <select
              v-model="filters.type"
              class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="">All Types</option>
              <option value="STATE">State Actor</option>
              <option value="SEPARATIST">Separatist</option>
              <option value="INSURGENT">Insurgent</option>
              <option value="TERRORIST">Terrorist</option>
              <option value="MILITIA">Militia</option>
              <option value="PMC">PMC</option>
              <option value="REBEL">Rebel</option>
            </select>
            <select
              v-model="filters.activity"
              class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="">All Activity Levels</option>
              <option value="high">High Activity</option>
              <option value="medium">Medium Activity</option>
              <option value="low">Low Activity</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
        </div>

        <!-- Actors Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div
            v-for="actor in filteredActors"
            :key="actor.id"
            class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden border border-gray-200 dark:border-gray-700 hover:shadow-lg transition-all"
          >
            <div class="p-6">
              <div class="flex items-start justify-between mb-4">
                <div class="flex items-center space-x-3">
                  <!-- Actor Icon/Flag -->
                  <div
                    :class="getActorIconBg(actor.actor_type)"
                    class="w-12 h-12 rounded-lg flex items-center justify-center flex-shrink-0"
                  >
                    <span v-if="actor.flag_emoji" class="text-2xl">{{ actor.flag_emoji }}</span>
                    <svg v-else class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9" />
                    </svg>
                  </div>
                  <div>
                    <h3 class="font-semibold text-gray-900 dark:text-white">{{ actor.name }}</h3>
                    <p v-if="actor.short_name" class="text-sm text-gray-500 dark:text-gray-400">{{ actor.short_name }}</p>
                  </div>
                </div>
              </div>

              <!-- Actor Type & Activity -->
              <div class="flex flex-wrap items-center gap-2 mb-4">
                <span :class="getTypeBadgeClass(actor.actor_type)" class="px-2 py-1 text-xs font-medium rounded-full">
                  {{ formatActorType(actor.actor_type) }}
                </span>
                <span :class="getActivityBadgeClass(actor.activity_level)" class="px-2 py-1 text-xs font-medium rounded-full">
                  {{ actor.activity_level }} activity
                </span>
                <span v-if="actor.is_designated_terrorist" class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400">
                  Designated Terrorist
                </span>
              </div>

              <!-- Description -->
              <p v-if="actor.description" class="text-sm text-gray-600 dark:text-gray-300 line-clamp-3 mb-4">
                {{ actor.description }}
              </p>

              <!-- Stats -->
              <div class="flex items-center justify-between text-sm">
                <div class="flex items-center space-x-4 text-gray-500 dark:text-gray-400">
                  <span v-if="actor.country" class="flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ actor.country }}
                  </span>
                  <span v-if="actor.events_count" class="flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    {{ actor.events_count }} events
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="filteredActors.length === 0" class="text-center py-16">
          <svg class="w-24 h-24 mx-auto text-gray-400 dark:text-gray-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
          </svg>
          <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">No Actors Found</h3>
          <p class="text-gray-600 dark:text-gray-400">Try adjusting your filters or search terms.</p>
        </div>

        <!-- Pagination -->
        <div v-if="filteredActors.length > 0" class="mt-8 flex flex-col md:flex-row items-center justify-between">
          <p class="text-sm text-gray-500 dark:text-gray-400 mb-4 md:mb-0">
            Showing {{ filteredActors.length }} actors
          </p>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-8 px-4 sm:px-6 lg:px-8">
      <div class="max-w-7xl mx-auto text-center">
        <p class="text-gray-400">Explore actor data. <router-link to="/register" class="text-blue-400 hover:underline">Create an account</router-link> for detailed profiles and relationships.</p>
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

const stats = ref({
  total: 156,
  state: 42,
  nonState: 114,
  active: 89
});

const filters = reactive({
  search: '',
  type: '',
  activity: ''
});

interface Actor {
  id: number;
  name: string;
  short_name: string | null;
  actor_type: string;
  country: string | null;
  flag_emoji: string | null;
  activity_level: 'high' | 'medium' | 'low' | 'inactive';
  is_designated_terrorist: boolean;
  description: string;
  events_count: number;
}

const actors = ref<Actor[]>([
  { id: 1, name: 'Russian Armed Forces', short_name: 'RAF', actor_type: 'STATE', country: 'Russia', flag_emoji: null, activity_level: 'high', is_designated_terrorist: false, description: 'The armed forces of the Russian Federation, consisting of ground forces, navy, aerospace forces, and strategic missile troops.', events_count: 8432 },
  { id: 2, name: 'Ukrainian Armed Forces', short_name: 'UAF', actor_type: 'STATE', country: 'Ukraine', flag_emoji: null, activity_level: 'high', is_designated_terrorist: false, description: 'The military forces of Ukraine, including ground forces, air force, navy, and territorial defense.', events_count: 7651 },
  { id: 3, name: 'Wagner Group', short_name: 'Wagner', actor_type: 'PMC', country: 'Russia', flag_emoji: null, activity_level: 'low', is_designated_terrorist: true, description: 'Russian private military company involved in various conflicts globally. Operations significantly reduced following 2023 mutiny.', events_count: 1234 },
  { id: 4, name: 'Donetsk People\'s Republic', short_name: 'DPR', actor_type: 'SEPARATIST', country: 'Ukraine', flag_emoji: null, activity_level: 'high', is_designated_terrorist: false, description: 'Self-proclaimed separatist entity in eastern Ukraine, now integrated into Russian military structures.', events_count: 2156 },
  { id: 5, name: 'Luhansk People\'s Republic', short_name: 'LPR', actor_type: 'SEPARATIST', country: 'Ukraine', flag_emoji: null, activity_level: 'high', is_designated_terrorist: false, description: 'Self-proclaimed separatist entity in eastern Ukraine, now integrated into Russian military structures.', events_count: 1843 },
  { id: 6, name: 'Islamic State', short_name: 'ISIS', actor_type: 'TERRORIST', country: null, flag_emoji: null, activity_level: 'medium', is_designated_terrorist: true, description: 'Transnational terrorist organization operating in the Middle East and globally. Significantly degraded but still active in insurgent capacity.', events_count: 3421 },
  { id: 7, name: 'Hamas', short_name: null, actor_type: 'MILITIA', country: 'Palestine', flag_emoji: null, activity_level: 'high', is_designated_terrorist: true, description: 'Palestinian political and military organization that controls the Gaza Strip. Designated as terrorist organization by many countries.', events_count: 2876 },
  { id: 8, name: 'Israeli Defense Forces', short_name: 'IDF', actor_type: 'STATE', country: 'Israel', flag_emoji: null, activity_level: 'high', is_designated_terrorist: false, description: 'The military forces of the State of Israel, consisting of ground forces, air force, and navy.', events_count: 3254 },
  { id: 9, name: 'Hezbollah', short_name: null, actor_type: 'MILITIA', country: 'Lebanon', flag_emoji: null, activity_level: 'high', is_designated_terrorist: true, description: 'Lebanese political party and militant group with significant military capabilities. Designated as terrorist by many Western nations.', events_count: 1567 },
  { id: 10, name: 'Syrian Arab Army', short_name: 'SAA', actor_type: 'STATE', country: 'Syria', flag_emoji: null, activity_level: 'medium', is_designated_terrorist: false, description: 'The ground warfare branch of the Syrian Armed Forces. Active in ongoing civil war.', events_count: 4521 },
  { id: 11, name: 'Rapid Support Forces', short_name: 'RSF', actor_type: 'MILITIA', country: 'Sudan', flag_emoji: null, activity_level: 'high', is_designated_terrorist: false, description: 'Sudanese paramilitary force currently engaged in civil conflict with the Sudanese Armed Forces.', events_count: 1876 },
  { id: 12, name: 'Sudanese Armed Forces', short_name: 'SAF', actor_type: 'STATE', country: 'Sudan', flag_emoji: null, activity_level: 'high', is_designated_terrorist: false, description: 'The military forces of Sudan, currently engaged in civil conflict with RSF.', events_count: 1654 }
]);

const filteredActors = computed(() => {
  return actors.value.filter(actor => {
    const matchesSearch = !filters.search ||
      actor.name.toLowerCase().includes(filters.search.toLowerCase()) ||
      (actor.short_name && actor.short_name.toLowerCase().includes(filters.search.toLowerCase()));
    const matchesType = !filters.type || actor.actor_type === filters.type;
    const matchesActivity = !filters.activity || actor.activity_level === filters.activity;
    return matchesSearch && matchesType && matchesActivity;
  });
});

const getActorIconBg = (type: string) => {
  const classes: Record<string, string> = {
    'STATE': 'bg-blue-500',
    'SEPARATIST': 'bg-purple-500',
    'INSURGENT': 'bg-orange-500',
    'TERRORIST': 'bg-red-500',
    'MILITIA': 'bg-yellow-500',
    'PMC': 'bg-gray-500',
    'REBEL': 'bg-green-500'
  };
  return classes[type] || 'bg-gray-500';
};

const getTypeBadgeClass = (type: string) => {
  const classes: Record<string, string> = {
    'STATE': 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400',
    'SEPARATIST': 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400',
    'INSURGENT': 'bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400',
    'TERRORIST': 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400',
    'MILITIA': 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400',
    'PMC': 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-400',
    'REBEL': 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400'
  };
  return classes[type] || 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-400';
};

const getActivityBadgeClass = (level: string) => {
  const classes: Record<string, string> = {
    'high': 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400',
    'medium': 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400',
    'low': 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400',
    'inactive': 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-400'
  };
  return classes[level] || 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-400';
};

const formatActorType = (type: string) => {
  return type.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase()).join(' ');
};
</script>

<style scoped>
.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
