<script setup lang="ts">
import { ref, computed, watch } from 'vue';

const props = defineProps<{
  startDate: Date;
  endDate: Date;
  currentDate?: Date;
}>();

const emit = defineEmits<{
  'update:currentDate': [date: Date];
  'play': [];
  'pause': [];
}>();

const isPlaying = ref(false);
const playbackSpeed = ref(1);
const localCurrentDate = ref(props.currentDate || props.startDate);
const playInterval = ref<number | null>(null);

const speedOptions = [
  { value: 0.5, label: '0.5x' },
  { value: 1, label: '1x' },
  { value: 2, label: '2x' },
  { value: 5, label: '5x' },
  { value: 10, label: '10x' }
];

const totalDays = computed(() => {
  const diff = props.endDate.getTime() - props.startDate.getTime();
  return Math.ceil(diff / (1000 * 60 * 60 * 24));
});

const currentDayOffset = computed(() => {
  const diff = localCurrentDate.value.getTime() - props.startDate.getTime();
  return Math.ceil(diff / (1000 * 60 * 60 * 24));
});

const progressPercentage = computed(() => {
  return (currentDayOffset.value / totalDays.value) * 100;
});

const formattedCurrentDate = computed(() => {
  return localCurrentDate.value.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
});

const togglePlayback = () => {
  if (isPlaying.value) {
    pausePlayback();
  } else {
    startPlayback();
  }
};

const startPlayback = () => {
  isPlaying.value = true;
  emit('play');

  playInterval.value = window.setInterval(() => {
    stepForward();

    if (localCurrentDate.value >= props.endDate) {
      pausePlayback();
      localCurrentDate.value = props.endDate;
    }
  }, 1000 / playbackSpeed.value);
};

const pausePlayback = () => {
  isPlaying.value = false;
  emit('pause');

  if (playInterval.value) {
    clearInterval(playInterval.value);
    playInterval.value = null;
  }
};

const stepForward = () => {
  const newDate = new Date(localCurrentDate.value);
  newDate.setDate(newDate.getDate() + 1);

  if (newDate <= props.endDate) {
    localCurrentDate.value = newDate;
    emit('update:currentDate', newDate);
  }
};

const stepBackward = () => {
  const newDate = new Date(localCurrentDate.value);
  newDate.setDate(newDate.getDate() - 1);

  if (newDate >= props.startDate) {
    localCurrentDate.value = newDate;
    emit('update:currentDate', newDate);
  }
};

const handleSliderChange = (event: Event) => {
  const value = parseInt((event.target as HTMLInputElement).value);
  const newDate = new Date(props.startDate);
  newDate.setDate(newDate.getDate() + value);

  localCurrentDate.value = newDate;
  emit('update:currentDate', newDate);
};

const setSpeed = (speed: number) => {
  playbackSpeed.value = speed;

  if (isPlaying.value) {
    pausePlayback();
    startPlayback();
  }
};

const reset = () => {
  pausePlayback();
  localCurrentDate.value = props.startDate;
  emit('update:currentDate', props.startDate);
};

watch(() => props.currentDate, (newDate) => {
  if (newDate) {
    localCurrentDate.value = newDate;
  }
});
</script>

<template>
  <div class="timeline-bar bg-white shadow-lg rounded-lg p-4">
    <div class="flex items-center justify-between mb-4">
      <div class="flex items-center space-x-4">
        <button
          @click="togglePlayback"
          class="w-10 h-10 flex items-center justify-center bg-blue-600 text-white rounded-full hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          <svg v-if="!isPlaying" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
            <path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z" />
          </svg>
          <svg v-else class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
            <path d="M5.75 3a.75.75 0 00-.75.75v12.5c0 .414.336.75.75.75h1.5a.75.75 0 00.75-.75V3.75A.75.75 0 007.25 3h-1.5zM12.75 3a.75.75 0 00-.75.75v12.5c0 .414.336.75.75.75h1.5a.75.75 0 00.75-.75V3.75a.75.75 0 00-.75-.75h-1.5z" />
          </svg>
        </button>

        <button
          @click="stepBackward"
          :disabled="localCurrentDate <= startDate"
          class="w-8 h-8 flex items-center justify-center bg-gray-200 text-gray-700 rounded hover:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
            <path d="M8.445 14.832A1 1 0 0010 14v-2.798l5.445 3.63A1 1 0 0017 14V6a1 1 0 00-1.555-.832L10 8.798V6a1 1 0 00-1.555-.832l-6 4a1 1 0 000 1.664l6 4z" />
          </svg>
        </button>

        <button
          @click="stepForward"
          :disabled="localCurrentDate >= endDate"
          class="w-8 h-8 flex items-center justify-center bg-gray-200 text-gray-700 rounded hover:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
            <path d="M4.555 5.168A1 1 0 003 6v8a1 1 0 001.555.832L10 11.202V14a1 1 0 001.555.832l6-4a1 1 0 000-1.664l-6-4A1 1 0 0010 6v2.798l-5.445-3.63z" />
          </svg>
        </button>

        <button
          @click="reset"
          class="px-3 py-1 bg-gray-200 text-gray-700 text-sm rounded hover:bg-gray-300"
        >
          Reset
        </button>
      </div>

      <div class="flex items-center space-x-2">
        <span class="text-sm text-gray-600">Speed:</span>
        <div class="flex space-x-1">
          <button
            v-for="option in speedOptions"
            :key="option.value"
            @click="setSpeed(option.value)"
            :class="{
              'bg-blue-600 text-white': playbackSpeed === option.value,
              'bg-gray-200 text-gray-700': playbackSpeed !== option.value
            }"
            class="px-3 py-1 text-sm rounded hover:bg-blue-700"
          >
            {{ option.label }}
          </button>
        </div>
      </div>
    </div>

    <div class="mb-2">
      <div class="flex justify-between items-center mb-1">
        <span class="text-sm text-gray-600">
          {{ startDate.toLocaleDateString() }}
        </span>
        <span class="text-lg font-semibold text-gray-900">
          {{ formattedCurrentDate }}
        </span>
        <span class="text-sm text-gray-600">
          {{ endDate.toLocaleDateString() }}
        </span>
      </div>

      <div class="relative">
        <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
          <div
            class="h-full bg-blue-600 transition-all duration-300"
            :style="{ width: `${progressPercentage}%` }"
          />
        </div>

        <input
          type="range"
          :min="0"
          :max="totalDays"
          :value="currentDayOffset"
          @input="handleSliderChange"
          class="absolute top-0 left-0 w-full h-2 opacity-0 cursor-pointer"
        />
      </div>
    </div>

    <div class="flex justify-between text-xs text-gray-500 mt-1">
      <span>Day {{ currentDayOffset }} of {{ totalDays }}</span>
      <span>{{ Math.round(progressPercentage) }}%</span>
    </div>
  </div>
</template>

<style scoped>
input[type="range"] {
  -webkit-appearance: none;
  appearance: none;
}

input[type="range"]::-webkit-slider-thumb {
  -webkit-appearance: none;
  appearance: none;
  width: 16px;
  height: 16px;
  border-radius: 50%;
  background: #2563eb;
  cursor: pointer;
}

input[type="range"]::-moz-range-thumb {
  width: 16px;
  height: 16px;
  border-radius: 50%;
  background: #2563eb;
  cursor: pointer;
  border: none;
}
</style>
