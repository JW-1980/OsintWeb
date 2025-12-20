<script setup lang="ts">
import { ref, computed } from 'vue';
import { Event, EventType, ConfidenceLevel } from '@/types';
import ActorSelector from './ActorSelector.vue';

const props = defineProps<{
  event?: Event;
  mode: 'create' | 'edit';
}>();

const emit = defineEmits<{
  submit: [data: Partial<Event>];
  cancel: [];
}>();

const formData = ref<Partial<Event>>({
  event_type: props.event?.event_type || 'combat_engagement',
  title: props.event?.title || '',
  description: props.event?.description || '',
  occurred_at: props.event?.occurred_at || new Date().toISOString().slice(0, 16),
  location_name: props.event?.location_name || '',
  coordinates: props.event?.coordinates,
  actor_id: props.event?.actor_id,
  confidence_level: props.event?.confidence_level || 'unconfirmed',
  source_urls: props.event?.source_urls || [],
  metadata: props.event?.metadata || {}
});

const newSourceUrl = ref('');
const coordinatesInput = ref({
  lat: props.event?.coordinates?.coordinates[1] || '',
  lng: props.event?.coordinates?.coordinates[0] || ''
});

const eventTypes: { value: EventType; label: string }[] = [
  { value: 'combat_engagement', label: 'Combat Engagement' },
  { value: 'airstrike', label: 'Airstrike' },
  { value: 'artillery_strike', label: 'Artillery Strike' },
  { value: 'missile_strike', label: 'Missile Strike' },
  { value: 'equipment_destroyed', label: 'Equipment Destroyed' },
  { value: 'equipment_captured', label: 'Equipment Captured' },
  { value: 'equipment_abandoned', label: 'Equipment Abandoned' },
  { value: 'equipment_sighting', label: 'Equipment Sighting' },
  { value: 'troop_movement', label: 'Troop Movement' },
  { value: 'convoy_spotted', label: 'Convoy Spotted' },
  { value: 'infrastructure_damage', label: 'Infrastructure Damage' },
  { value: 'fortification', label: 'Fortification' },
  { value: 'civilian_casualties', label: 'Civilian Casualties' },
  { value: 'evacuation', label: 'Evacuation' }
];

const confidenceLevels: { value: ConfidenceLevel; label: string }[] = [
  { value: 'confirmed', label: 'Confirmed' },
  { value: 'likely', label: 'Likely' },
  { value: 'unconfirmed', label: 'Unconfirmed' }
];

const isFormValid = computed(() => {
  return formData.value.title &&
    formData.value.event_type &&
    formData.value.occurred_at &&
    formData.value.confidence_level;
});

const handleActorSelected = (actorId: number) => {
  formData.value.actor_id = actorId;
};

const addSourceUrl = () => {
  if (newSourceUrl.value && !formData.value.source_urls?.includes(newSourceUrl.value)) {
    formData.value.source_urls = [...(formData.value.source_urls || []), newSourceUrl.value];
    newSourceUrl.value = '';
  }
};

const removeSourceUrl = (url: string) => {
  formData.value.source_urls = formData.value.source_urls?.filter(u => u !== url);
};

const updateCoordinates = () => {
  const lat = parseFloat(coordinatesInput.value.lat as string);
  const lng = parseFloat(coordinatesInput.value.lng as string);

  if (!isNaN(lat) && !isNaN(lng)) {
    formData.value.coordinates = {
      type: 'Point',
      coordinates: [lng, lat]
    };
  }
};

const handleSubmit = () => {
  updateCoordinates();

  if (isFormValid.value) {
    emit('submit', formData.value);
  }
};

const handleCancel = () => {
  emit('cancel');
};
</script>

<template>
  <div class="event-form bg-white shadow-lg rounded-lg p-6">
    <h2 class="text-2xl font-bold text-gray-900 mb-6">
      {{ mode === 'create' ? 'Create Event' : 'Edit Event' }}
    </h2>

    <form @submit.prevent="handleSubmit" class="space-y-6">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Title <span class="text-red-500">*</span>
          </label>
          <input
            v-model="formData.title"
            type="text"
            required
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="Event title"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Event Type <span class="text-red-500">*</span>
          </label>
          <select
            v-model="formData.event_type"
            required
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option v-for="type in eventTypes" :key="type.value" :value="type.value">
              {{ type.label }}
            </option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Occurred At <span class="text-red-500">*</span>
          </label>
          <input
            v-model="formData.occurred_at"
            type="datetime-local"
            required
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Confidence Level <span class="text-red-500">*</span>
          </label>
          <select
            v-model="formData.confidence_level"
            required
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option v-for="level in confidenceLevels" :key="level.value" :value="level.value">
              {{ level.label }}
            </option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Location Name</label>
          <input
            v-model="formData.location_name"
            type="text"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="e.g., Kyiv, Ukraine"
          />
        </div>

        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-gray-700 mb-2">Actor</label>
          <ActorSelector
            :selected-actor-id="formData.actor_id"
            @select="handleActorSelected"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Latitude</label>
          <input
            v-model="coordinatesInput.lat"
            type="number"
            step="any"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="e.g., 50.4501"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Longitude</label>
          <input
            v-model="coordinatesInput.lng"
            type="number"
            step="any"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="e.g., 30.5234"
          />
        </div>

        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
          <textarea
            v-model="formData.description"
            rows="4"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="Detailed description of the event"
          />
        </div>

        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-gray-700 mb-2">Source URLs</label>
          <div class="flex space-x-2 mb-2">
            <input
              v-model="newSourceUrl"
              type="url"
              class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="https://example.com/source"
              @keyup.enter="addSourceUrl"
            />
            <button
              type="button"
              @click="addSourceUrl"
              class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
            >
              Add
            </button>
          </div>

          <div v-if="formData.source_urls && formData.source_urls.length > 0" class="space-y-2">
            <div
              v-for="(url, index) in formData.source_urls"
              :key="index"
              class="flex items-center justify-between bg-gray-50 px-3 py-2 rounded"
            >
              <a :href="url" target="_blank" class="text-sm text-blue-600 hover:underline truncate">
                {{ url }}
              </a>
              <button
                type="button"
                @click="removeSourceUrl(url)"
                class="ml-2 text-red-600 hover:text-red-800"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>

      <div class="flex justify-end space-x-4 pt-4 border-t border-gray-200">
        <button
          type="button"
          @click="handleCancel"
          class="px-6 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300"
        >
          Cancel
        </button>
        <button
          type="submit"
          :disabled="!isFormValid"
          class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          {{ mode === 'create' ? 'Create Event' : 'Update Event' }}
        </button>
      </div>
    </form>
  </div>
</template>
