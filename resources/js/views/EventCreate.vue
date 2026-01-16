<script setup lang="ts">
import { ref, reactive, computed, onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import { useEventsStore } from '@/stores/events';
import type { Event, EventType, ConfidenceLevel } from '@/types';
import ActorSelector from '@/components/events/ActorSelector.vue';

const router = useRouter();
const eventsStore = useEventsStore();

const loading = ref(false);
const saving = ref(false);
const showPreview = ref(false);
const mapReady = ref(false);
const selectedLocation = ref<{ lat: number; lng: number } | null>(null);

const formData = reactive({
  event_type: 'combat_engagement' as EventType,
  title: '',
  description: '',
  occurred_at: new Date().toISOString().slice(0, 16),
  location_name: '',
  coordinates: null as { type: 'Point'; coordinates: [number, number] } | null,
  actor_id: undefined as number | undefined,
  conflict_id: undefined as number | undefined,
  confidence_level: 'unconfirmed' as ConfidenceLevel,
  source_urls: [] as string[],
  media_urls: [] as string[],
  metadata: {} as Record<string, any>
});

const coordinatesInput = reactive({
  lat: '',
  lng: ''
});

const newSourceUrl = ref('');
const newMediaUrl = ref('');
const mediaFiles = ref<File[]>([]);

const eventTypes: { value: EventType; label: string; icon: string }[] = [
  { value: 'combat_engagement', label: 'Combat Engagement', icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2' },
  { value: 'airstrike', label: 'Airstrike', icon: 'M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z' },
  { value: 'artillery_strike', label: 'Artillery Strike', icon: 'M13 10V3L4 14h7v7l9-11h-7z' },
  { value: 'missile_strike', label: 'Missile Strike', icon: 'M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z' },
  { value: 'equipment_destroyed', label: 'Equipment Destroyed', icon: 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16' },
  { value: 'equipment_captured', label: 'Equipment Captured', icon: 'M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z' },
  { value: 'equipment_sighting', label: 'Equipment Sighting', icon: 'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z' },
  { value: 'troop_movement', label: 'Troop Movement', icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z' },
  { value: 'civilian_casualties', label: 'Civilian Casualties', icon: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z' },
  { value: 'infrastructure_damage', label: 'Infrastructure Damage', icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4' }
];

const confidenceLevels: { value: ConfidenceLevel; label: string; color: string }[] = [
  { value: 'confirmed', label: 'Confirmed', color: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' },
  { value: 'likely', label: 'Likely', color: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400' },
  { value: 'unconfirmed', label: 'Unconfirmed', color: 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300' }
];

const isFormValid = computed(() => {
  return formData.title &&
    formData.event_type &&
    formData.occurred_at &&
    formData.confidence_level;
});

const eventTypeLabel = computed(() => {
  const type = eventTypes.find(t => t.value === formData.event_type);
  return type?.label || formData.event_type;
});

const handleActorSelected = (actorId: number) => {
  formData.actor_id = actorId;
};

const addSourceUrl = () => {
  if (newSourceUrl.value && !formData.source_urls.includes(newSourceUrl.value)) {
    formData.source_urls.push(newSourceUrl.value);
    newSourceUrl.value = '';
  }
};

const removeSourceUrl = (url: string) => {
  formData.source_urls = formData.source_urls.filter(u => u !== url);
};

const addMediaUrl = () => {
  if (newMediaUrl.value && !formData.media_urls.includes(newMediaUrl.value)) {
    formData.media_urls.push(newMediaUrl.value);
    newMediaUrl.value = '';
  }
};

const removeMediaUrl = (url: string) => {
  formData.media_urls = formData.media_urls.filter(u => u !== url);
};

const handleFileUpload = (event: globalThis.Event) => {
  const input = event.target as HTMLInputElement;
  if (input.files) {
    mediaFiles.value = [...mediaFiles.value, ...Array.from(input.files)];
  }
};

const removeFile = (index: number) => {
  mediaFiles.value.splice(index, 1);
};

const updateCoordinates = () => {
  const lat = parseFloat(coordinatesInput.lat);
  const lng = parseFloat(coordinatesInput.lng);

  if (!isNaN(lat) && !isNaN(lng)) {
    formData.coordinates = {
      type: 'Point',
      coordinates: [lng, lat]
    };
    selectedLocation.value = { lat, lng };
  }
};

const handleMapClick = (lat: number, lng: number) => {
  coordinatesInput.lat = lat.toFixed(6);
  coordinatesInput.lng = lng.toFixed(6);
  updateCoordinates();
};

const handleSubmit = async (asDraft: boolean = false) => {
  if (!isFormValid.value) return;

  saving.value = true;
  try {
    updateCoordinates();

    const eventData: Partial<Event> = {
      ...formData,
      metadata: {
        ...formData.metadata,
        is_draft: asDraft
      }
    };

    await eventsStore.createEvent(eventData);
    router.push({ name: 'events' });
  } catch (error) {
    console.error('Failed to create event:', error);
    alert('Failed to create event. Please try again.');
  } finally {
    saving.value = false;
  }
};

const handleCancel = () => {
  if (formData.title || formData.description) {
    if (!confirm('You have unsaved changes. Are you sure you want to leave?')) {
      return;
    }
  }
  router.back();
};

watch(coordinatesInput, () => {
  updateCoordinates();
}, { deep: true });

onMounted(() => {
  mapReady.value = true;
});
</script>

<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <!-- Header -->
    <div class="bg-white dark:bg-gray-800 shadow">
      <div class="max-w-6xl mx-auto px-6 py-4">
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-4">
            <button
              @click="handleCancel"
              class="p-2 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
              </svg>
            </button>
            <div>
              <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Create New Event</h1>
              <p class="text-gray-600 dark:text-gray-400">Document a new event for the database</p>
            </div>
          </div>
          <div class="flex items-center space-x-3">
            <button
              @click="showPreview = !showPreview"
              class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg font-medium hover:bg-gray-200 dark:hover:bg-gray-600"
            >
              {{ showPreview ? 'Edit' : 'Preview' }}
            </button>
            <button
              @click="handleSubmit(true)"
              :disabled="!isFormValid || saving"
              class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg font-medium hover:bg-gray-300 dark:hover:bg-gray-600 disabled:opacity-50"
            >
              Save Draft
            </button>
            <button
              @click="handleSubmit(false)"
              :disabled="!isFormValid || saving"
              class="px-4 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 disabled:opacity-50 flex items-center space-x-2"
            >
              <span>{{ saving ? 'Submitting...' : 'Submit for Review' }}</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="max-w-6xl mx-auto px-6 py-8">
      <!-- Preview Mode -->
      <div v-if="showPreview" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Event Preview</h2>
        <div class="space-y-4">
          <div class="flex items-center space-x-3">
            <span class="px-3 py-1 bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 rounded-full text-sm font-medium">
              {{ eventTypeLabel }}
            </span>
            <span
              :class="[
                'px-3 py-1 rounded-full text-sm font-medium',
                confidenceLevels.find(l => l.value === formData.confidence_level)?.color
              ]"
            >
              {{ formData.confidence_level }}
            </span>
          </div>
          <h3 class="text-2xl font-bold text-gray-900 dark:text-white">
            {{ formData.title || 'Untitled Event' }}
          </h3>
          <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
              <span class="text-gray-500 dark:text-gray-400">Date:</span>
              <span class="ml-2 text-gray-900 dark:text-white">
                {{ formData.occurred_at ? new Date(formData.occurred_at).toLocaleString() : 'Not set' }}
              </span>
            </div>
            <div v-if="formData.location_name">
              <span class="text-gray-500 dark:text-gray-400">Location:</span>
              <span class="ml-2 text-gray-900 dark:text-white">{{ formData.location_name }}</span>
            </div>
          </div>
          <div v-if="formData.description" class="prose dark:prose-invert max-w-none">
            <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ formData.description }}</p>
          </div>
          <div v-if="formData.source_urls.length > 0">
            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Sources</h4>
            <div class="space-y-1">
              <a
                v-for="url in formData.source_urls"
                :key="url"
                :href="url"
                target="_blank"
                class="block text-blue-600 dark:text-blue-400 hover:underline text-sm"
              >
                {{ url }}
              </a>
            </div>
          </div>
        </div>
      </div>

      <!-- Edit Mode -->
      <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Form -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Basic Information -->
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Basic Information</h2>
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  Title <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="formData.title"
                  type="text"
                  class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="Brief, descriptive title for the event"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Event Type <span class="text-red-500">*</span>
                </label>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                  <button
                    v-for="type in eventTypes"
                    :key="type.value"
                    @click="formData.event_type = type.value"
                    :class="[
                      'flex items-center space-x-2 px-3 py-2 rounded-lg text-sm font-medium transition-colors text-left',
                      formData.event_type === type.value
                        ? 'bg-blue-600 text-white'
                        : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'
                    ]"
                  >
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="type.icon" />
                    </svg>
                    <span class="truncate">{{ type.label }}</span>
                  </button>
                </div>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Date & Time <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model="formData.occurred_at"
                    type="datetime-local"
                    class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Confidence Level <span class="text-red-500">*</span>
                  </label>
                  <select
                    v-model="formData.confidence_level"
                    class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  >
                    <option v-for="level in confidenceLevels" :key="level.value" :value="level.value">
                      {{ level.label }}
                    </option>
                  </select>
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                <textarea
                  v-model="formData.description"
                  rows="5"
                  class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="Detailed description of the event..."
                ></textarea>
              </div>
            </div>
          </div>

          <!-- Location -->
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Location</h2>
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Location Name</label>
                <input
                  v-model="formData.location_name"
                  type="text"
                  class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="e.g., Kharkiv, Ukraine"
                />
              </div>
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Latitude</label>
                  <input
                    v-model="coordinatesInput.lat"
                    type="text"
                    class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="e.g., 50.4501"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Longitude</label>
                  <input
                    v-model="coordinatesInput.lng"
                    type="text"
                    class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="e.g., 30.5234"
                  />
                </div>
              </div>

              <!-- Map Placeholder -->
              <div class="h-64 bg-gray-200 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                <div class="text-center">
                  <svg class="w-12 h-12 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                  </svg>
                  <p class="text-gray-500 dark:text-gray-400">Click on map to select location</p>
                  <p v-if="selectedLocation" class="text-sm text-gray-600 dark:text-gray-400 mt-2">
                    Selected: {{ selectedLocation.lat.toFixed(4) }}, {{ selectedLocation.lng.toFixed(4) }}
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- Sources -->
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Sources</h2>
            <div class="space-y-4">
              <div class="flex space-x-2">
                <input
                  v-model="newSourceUrl"
                  type="url"
                  class="flex-1 px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="https://example.com/source"
                  @keyup.enter="addSourceUrl"
                />
                <button
                  @click="addSourceUrl"
                  class="px-4 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700"
                >
                  Add
                </button>
              </div>
              <div v-if="formData.source_urls.length > 0" class="space-y-2">
                <div
                  v-for="(url, index) in formData.source_urls"
                  :key="index"
                  class="flex items-center justify-between bg-gray-50 dark:bg-gray-700/50 px-4 py-2 rounded-lg"
                >
                  <a
                    :href="url"
                    target="_blank"
                    class="text-blue-600 dark:text-blue-400 hover:underline text-sm truncate"
                  >
                    {{ url }}
                  </a>
                  <button
                    @click="removeSourceUrl(url)"
                    class="ml-2 text-red-600 dark:text-red-400 hover:text-red-700"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Media -->
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Media</h2>
            <div class="space-y-4">
              <!-- URL Input -->
              <div class="flex space-x-2">
                <input
                  v-model="newMediaUrl"
                  type="url"
                  class="flex-1 px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="https://example.com/image.jpg"
                  @keyup.enter="addMediaUrl"
                />
                <button
                  @click="addMediaUrl"
                  class="px-4 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700"
                >
                  Add URL
                </button>
              </div>

              <!-- File Upload -->
              <div>
                <label class="block w-full">
                  <div class="flex items-center justify-center w-full h-32 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer hover:border-blue-500 dark:hover:border-blue-400">
                    <div class="text-center">
                      <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                      </svg>
                      <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Click to upload or drag and drop
                      </p>
                      <p class="text-xs text-gray-400 dark:text-gray-500">
                        PNG, JPG, GIF up to 10MB
                      </p>
                    </div>
                  </div>
                  <input
                    type="file"
                    class="hidden"
                    accept="image/*"
                    multiple
                    @change="handleFileUpload"
                  />
                </label>
              </div>

              <!-- Media Preview -->
              <div v-if="formData.media_urls.length > 0 || mediaFiles.length > 0" class="grid grid-cols-3 gap-4">
                <div
                  v-for="(url, index) in formData.media_urls"
                  :key="'url-' + index"
                  class="relative aspect-square bg-gray-100 dark:bg-gray-700 rounded-lg overflow-hidden"
                >
                  <img :src="url" :alt="'Media ' + index" class="w-full h-full object-cover" />
                  <button
                    @click="removeMediaUrl(url)"
                    class="absolute top-2 right-2 w-6 h-6 bg-red-600 text-white rounded-full flex items-center justify-center hover:bg-red-700"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                </div>
                <div
                  v-for="(file, index) in mediaFiles"
                  :key="'file-' + index"
                  class="relative aspect-square bg-gray-100 dark:bg-gray-700 rounded-lg overflow-hidden"
                >
                  <div class="w-full h-full flex items-center justify-center">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                  </div>
                  <div class="absolute bottom-0 left-0 right-0 bg-black/50 text-white text-xs p-1 truncate">
                    {{ file.name }}
                  </div>
                  <button
                    @click="removeFile(index)"
                    class="absolute top-2 right-2 w-6 h-6 bg-red-600 text-white rounded-full flex items-center justify-center hover:bg-red-700"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          <!-- Actor Selection -->
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Actor</h2>
            <ActorSelector
              :selected-actor-id="formData.actor_id"
              @select="handleActorSelected"
            />
          </div>

          <!-- Form Status -->
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Status</h2>
            <div class="space-y-3">
              <div class="flex items-center justify-between text-sm">
                <span class="text-gray-600 dark:text-gray-400">Title</span>
                <span :class="formData.title ? 'text-green-600' : 'text-red-600'">
                  {{ formData.title ? 'Complete' : 'Required' }}
                </span>
              </div>
              <div class="flex items-center justify-between text-sm">
                <span class="text-gray-600 dark:text-gray-400">Event Type</span>
                <span class="text-green-600">Complete</span>
              </div>
              <div class="flex items-center justify-between text-sm">
                <span class="text-gray-600 dark:text-gray-400">Date & Time</span>
                <span :class="formData.occurred_at ? 'text-green-600' : 'text-red-600'">
                  {{ formData.occurred_at ? 'Complete' : 'Required' }}
                </span>
              </div>
              <div class="flex items-center justify-between text-sm">
                <span class="text-gray-600 dark:text-gray-400">Location</span>
                <span :class="formData.location_name || formData.coordinates ? 'text-green-600' : 'text-yellow-600'">
                  {{ formData.location_name || formData.coordinates ? 'Complete' : 'Optional' }}
                </span>
              </div>
              <div class="flex items-center justify-between text-sm">
                <span class="text-gray-600 dark:text-gray-400">Sources</span>
                <span :class="formData.source_urls.length > 0 ? 'text-green-600' : 'text-yellow-600'">
                  {{ formData.source_urls.length > 0 ? `${formData.source_urls.length} added` : 'Recommended' }}
                </span>
              </div>
            </div>
          </div>

          <!-- Tips -->
          <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-6">
            <h3 class="font-semibold text-blue-900 dark:text-blue-300 mb-2">Tips for Quality Events</h3>
            <ul class="text-sm text-blue-800 dark:text-blue-400 space-y-2">
              <li class="flex items-start space-x-2">
                <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span>Include at least one source URL</span>
              </li>
              <li class="flex items-start space-x-2">
                <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span>Provide precise coordinates when possible</span>
              </li>
              <li class="flex items-start space-x-2">
                <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span>Select appropriate confidence level</span>
              </li>
              <li class="flex items-start space-x-2">
                <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span>Add media for visual verification</span>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
