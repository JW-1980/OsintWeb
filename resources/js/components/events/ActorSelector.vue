<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Actor } from '@/types';
import { useApi } from '@/composables/useApi';

const props = defineProps<{
  selectedActorId?: number;
}>();

const emit = defineEmits<{
  select: [actorId: number];
}>();

const api = useApi();
const searchQuery = ref('');
const actors = ref<Actor[]>([]);
const selectedActor = ref<Actor | null>(null);
const isSearching = ref(false);
const showDropdown = ref(false);
const searchTimeout = ref<number | null>(null);

const filteredActors = computed(() => {
  return actors.value;
});

const searchActors = async (query: string) => {
  if (!query || query.length < 2) {
    actors.value = [];
    return;
  }

  isSearching.value = true;
  try {
    const response = await api.get<Actor[]>(`/actors/search?q=${encodeURIComponent(query)}`);
    actors.value = response;
    showDropdown.value = true;
  } catch (error) {
    console.error('Failed to search actors:', error);
  } finally {
    isSearching.value = false;
  }
};

const handleSearchInput = (event: Event) => {
  const query = (event.target as HTMLInputElement).value;
  searchQuery.value = query;

  if (searchTimeout.value) {
    clearTimeout(searchTimeout.value);
  }

  searchTimeout.value = window.setTimeout(() => {
    searchActors(query);
  }, 300);
};

const selectActor = (actor: Actor) => {
  selectedActor.value = actor;
  searchQuery.value = actor.name;
  showDropdown.value = false;
  emit('select', actor.id);
};

const clearSelection = () => {
  selectedActor.value = null;
  searchQuery.value = '';
  actors.value = [];
  showDropdown.value = false;
};

const fetchSelectedActor = async (actorId: number) => {
  try {
    const actor = await api.get<Actor>(`/actors/${actorId}`);
    selectedActor.value = actor;
    searchQuery.value = actor.name;
  } catch (error) {
    console.error('Failed to fetch actor:', error);
  }
};

watch(() => props.selectedActorId, (newId) => {
  if (newId) {
    fetchSelectedActor(newId);
  }
}, { immediate: true });
</script>

<template>
  <div class="actor-selector relative">
    <div class="relative">
      <input
        :value="searchQuery"
        @input="handleSearchInput"
        @focus="showDropdown = true"
        type="text"
        class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
        placeholder="Search for an actor..."
      />

      <div class="absolute right-2 top-1/2 transform -translate-y-1/2 flex items-center space-x-1">
        <div v-if="isSearching" class="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-600" />
        <button
          v-if="selectedActor"
          @click="clearSelection"
          type="button"
          class="text-gray-400 hover:text-gray-600"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </div>

    <div
      v-if="showDropdown && filteredActors.length > 0"
      class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto"
    >
      <div
        v-for="actor in filteredActors"
        :key="actor.id"
        @click="selectActor(actor)"
        class="px-4 py-2 hover:bg-gray-100 cursor-pointer"
      >
        <div class="flex items-center space-x-2">
          <div
            v-if="actor.flag_url || actor.logo_url"
            class="w-6 h-6 bg-gray-200 rounded-full bg-cover bg-center"
            :style="{ backgroundImage: `url(${actor.flag_url || actor.logo_url})` }"
          />
          <div
            v-else
            class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-semibold"
            :style="{ backgroundColor: actor.color_hex || '#3388ff', color: 'white' }"
          >
            {{ actor.name.charAt(0) }}
          </div>
          <div class="flex-1">
            <div class="font-medium text-gray-900">{{ actor.name }}</div>
            <div class="text-xs text-gray-500">
              {{ actor.actor_type }} {{ actor.country ? `• ${actor.country.name}` : '' }}
            </div>
          </div>
        </div>
      </div>
    </div>

    <div
      v-if="showDropdown && !isSearching && searchQuery.length >= 2 && filteredActors.length === 0"
      class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg p-4 text-center text-gray-500"
    >
      No actors found
    </div>

    <div v-if="selectedActor" class="mt-2 p-2 bg-blue-50 border border-blue-200 rounded-md">
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-2">
          <div
            v-if="selectedActor.flag_url || selectedActor.logo_url"
            class="w-8 h-8 bg-gray-200 rounded-full bg-cover bg-center"
            :style="{ backgroundImage: `url(${selectedActor.flag_url || selectedActor.logo_url})` }"
          />
          <div>
            <div class="font-medium text-gray-900">{{ selectedActor.name }}</div>
            <div class="text-xs text-gray-600">{{ selectedActor.actor_type }}</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.animate-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}
</style>
