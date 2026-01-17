<template>
  <div class="mt-6 space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h2 class="text-xl font-bold text-gray-900 dark:text-white">A/B Testing</h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Create and manage experiments for multivariate testing</p>
      </div>
      <button @click="showCreateModal = true" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
        Create Experiment
      </button>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">Total Experiments</p>
        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.total }}</p>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">Running</p>
        <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ stats.running }}</p>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">Total Impressions</p>
        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ formatNumber(stats.total_impressions) }}</p>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">Total Conversions</p>
        <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ formatNumber(stats.total_conversions) }}</p>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
      <div class="flex flex-wrap gap-4">
        <select v-model="filters.status" @change="fetchExperiments" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
          <option value="">All Statuses</option>
          <option value="draft">Draft</option>
          <option value="running">Running</option>
          <option value="paused">Paused</option>
          <option value="completed">Completed</option>
        </select>
        <select v-model="filters.type" @change="fetchExperiments" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
          <option value="">All Types</option>
          <option value="text">Text</option>
          <option value="button">Button</option>
          <option value="layout">Layout</option>
          <option value="feature">Feature</option>
        </select>
        <input v-model="filters.search" @input="debouncedFetch" type="text" placeholder="Search experiments..." class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm flex-1 min-w-48" />
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex justify-center py-12">
      <svg class="animate-spin h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
      </svg>
    </div>

    <!-- Experiments List -->
    <div v-else class="space-y-4">
      <div v-for="experiment in experiments" :key="experiment.id" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="p-6">
          <div class="flex items-start justify-between">
            <div>
              <div class="flex items-center space-x-3">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ experiment.name }}</h3>
                <span :class="getStatusClass(experiment.status)" class="px-2 py-0.5 text-xs font-medium rounded-full">{{ experiment.status }}</span>
                <span class="px-2 py-0.5 text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 rounded-full">{{ experiment.type }}</span>
              </div>
              <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ experiment.description }}</p>
              <div class="mt-2 flex flex-wrap gap-2 text-xs text-gray-500">
                <span>Location: <code class="bg-gray-100 dark:bg-gray-700 px-1 rounded">{{ experiment.location }}</code></span>
                <span>Goal: {{ experiment.primary_goal }}</span>
                <span>Traffic: {{ experiment.traffic_percentage }}%</span>
              </div>
            </div>
            <div class="flex items-center space-x-2">
              <button v-if="experiment.status === 'draft'" @click="startExperiment(experiment)" class="px-3 py-1.5 bg-green-600 text-white text-xs font-medium rounded-lg hover:bg-green-700">Start</button>
              <button v-if="experiment.status === 'running'" @click="pauseExperiment(experiment)" class="px-3 py-1.5 bg-yellow-600 text-white text-xs font-medium rounded-lg hover:bg-yellow-700">Pause</button>
              <button v-if="experiment.status === 'paused'" @click="startExperiment(experiment)" class="px-3 py-1.5 bg-green-600 text-white text-xs font-medium rounded-lg hover:bg-green-700">Resume</button>
              <button v-if="['running', 'paused'].includes(experiment.status)" @click="completeExperiment(experiment)" class="px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded-lg hover:bg-blue-700">Complete</button>
              <button @click="viewResults(experiment)" class="px-3 py-1.5 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-xs font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">Results</button>
              <button @click="editExperiment(experiment)" class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
              </button>
              <button @click="deleteExperiment(experiment)" class="p-1.5 text-red-400 hover:text-red-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
              </button>
            </div>
          </div>

          <!-- Variants Summary -->
          <div class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-4">
            <div v-for="variant in experiment.variants" :key="variant.id" class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
              <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ variant.name }}</span>
                <span v-if="variant.is_control" class="px-1.5 py-0.5 text-xs bg-gray-200 dark:bg-gray-600 text-gray-600 dark:text-gray-300 rounded">Control</span>
              </div>
              <div class="text-xs text-gray-500 space-y-1">
                <div class="flex justify-between">
                  <span>Impressions</span>
                  <span class="font-medium text-gray-700 dark:text-gray-300">{{ formatNumber(variant.impressions || 0) }}</span>
                </div>
                <div class="flex justify-between">
                  <span>Conversions</span>
                  <span class="font-medium text-gray-700 dark:text-gray-300">{{ formatNumber(variant.conversions || 0) }}</span>
                </div>
                <div class="flex justify-between">
                  <span>Rate</span>
                  <span class="font-medium" :class="getConversionRateClass(variant.conversion_rate)">{{ (variant.conversion_rate || 0).toFixed(2) }}%</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="experiments.length === 0" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center">
        <svg class="w-12 h-12 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" /></svg>
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-1">No experiments yet</h3>
        <p class="text-gray-500 dark:text-gray-400 mb-4">Create your first A/B test to start optimizing your content.</p>
        <button @click="showCreateModal = true" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
          Create Experiment
        </button>
      </div>

      <!-- Pagination -->
      <div v-if="pagination.last_page > 1" class="flex items-center justify-between">
        <p class="text-sm text-gray-500">Showing {{ pagination.from }} - {{ pagination.to }} of {{ pagination.total }}</p>
        <div class="flex space-x-2">
          <button @click="goToPage(pagination.current_page - 1)" :disabled="pagination.current_page === 1" class="px-3 py-1.5 text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg disabled:opacity-50">Previous</button>
          <button @click="goToPage(pagination.current_page + 1)" :disabled="pagination.current_page === pagination.last_page" class="px-3 py-1.5 text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg disabled:opacity-50">Next</button>
        </div>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="showCreateModal || showEditModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
          <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ showEditModal ? 'Edit Experiment' : 'Create Experiment' }}</h3>
        </div>
        <div class="p-6 space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name</label>
            <input v-model="form.name" type="text" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
            <textarea v-model="form.description" rows="2" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"></textarea>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type</label>
              <select v-model="form.type" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                <option value="text">Text</option>
                <option value="button">Button</option>
                <option value="layout">Layout</option>
                <option value="feature">Feature</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Location</label>
              <input v-model="form.location" type="text" placeholder="e.g., homepage_hero" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
            </div>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Primary Goal</label>
              <select v-model="form.primary_goal" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                <option value="click">Click</option>
                <option value="signup">Signup</option>
                <option value="engagement">Engagement</option>
                <option value="scroll">Scroll</option>
                <option value="time_on_page">Time on Page</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Traffic %</label>
              <input v-model.number="form.traffic_percentage" type="number" min="1" max="100" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
            </div>
          </div>

          <!-- Variants -->
          <div>
            <div class="flex items-center justify-between mb-2">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Variants</label>
              <button @click="addVariant" type="button" class="text-sm text-blue-600 hover:text-blue-700">+ Add Variant</button>
            </div>
            <div class="space-y-3">
              <div v-for="(variant, index) in form.variants" :key="index" class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                <div class="flex items-center justify-between mb-2">
                  <input v-model="variant.name" type="text" placeholder="Variant name" class="text-sm bg-transparent border-b border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white focus:outline-none focus:border-blue-500" />
                  <div class="flex items-center space-x-2">
                    <label class="flex items-center text-xs text-gray-500">
                      <input v-model="variant.is_control" type="checkbox" class="mr-1" />
                      Control
                    </label>
                    <button v-if="form.variants.length > 2" @click="removeVariant(index)" class="text-red-400 hover:text-red-600">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                  </div>
                </div>
                <div class="grid grid-cols-2 gap-2">
                  <div>
                    <label class="text-xs text-gray-500">Weight (%)</label>
                    <input v-model.number="variant.weight" type="number" min="1" max="100" class="w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
                  </div>
                  <div>
                    <label class="text-xs text-gray-500">Content Key</label>
                    <input v-model="variant.content.text" type="text" placeholder="Variant text" class="w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex justify-end space-x-3">
          <button @click="closeModals" class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600">Cancel</button>
          <button @click="saveExperiment" :disabled="saving" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50">
            {{ saving ? 'Saving...' : (showEditModal ? 'Update' : 'Create') }}
          </button>
        </div>
      </div>
    </div>

    <!-- Results Modal -->
    <div v-if="showResultsModal && selectedExperiment" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
          <h3 class="text-lg font-bold text-gray-900 dark:text-white">Results: {{ selectedExperiment.name }}</h3>
          <button @click="calculateResults" :disabled="calculating" class="text-sm text-blue-600 hover:text-blue-700">
            {{ calculating ? 'Calculating...' : 'Recalculate' }}
          </button>
        </div>
        <div class="p-6">
          <!-- Results Table -->
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead>
                <tr class="border-b border-gray-200 dark:border-gray-700">
                  <th class="text-left py-3 px-4 font-medium text-gray-700 dark:text-gray-300">Variant</th>
                  <th class="text-right py-3 px-4 font-medium text-gray-700 dark:text-gray-300">Impressions</th>
                  <th class="text-right py-3 px-4 font-medium text-gray-700 dark:text-gray-300">Conversions</th>
                  <th class="text-right py-3 px-4 font-medium text-gray-700 dark:text-gray-300">Rate</th>
                  <th class="text-right py-3 px-4 font-medium text-gray-700 dark:text-gray-300">Improvement</th>
                  <th class="text-right py-3 px-4 font-medium text-gray-700 dark:text-gray-300">Confidence</th>
                  <th class="text-center py-3 px-4 font-medium text-gray-700 dark:text-gray-300">Winner</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="result in experimentResults" :key="result.variant_id" class="border-b border-gray-100 dark:border-gray-700/50">
                  <td class="py-3 px-4">
                    <span class="font-medium text-gray-900 dark:text-white">{{ result.variant_name }}</span>
                    <span v-if="result.is_control" class="ml-2 text-xs text-gray-500">(Control)</span>
                  </td>
                  <td class="py-3 px-4 text-right text-gray-700 dark:text-gray-300">{{ formatNumber(result.impressions) }}</td>
                  <td class="py-3 px-4 text-right text-gray-700 dark:text-gray-300">{{ formatNumber(result.conversions) }}</td>
                  <td class="py-3 px-4 text-right font-medium" :class="getConversionRateClass(result.conversion_rate)">{{ result.conversion_rate?.toFixed(2) }}%</td>
                  <td class="py-3 px-4 text-right" :class="result.improvement > 0 ? 'text-green-600' : result.improvement < 0 ? 'text-red-600' : 'text-gray-500'">
                    {{ result.improvement > 0 ? '+' : '' }}{{ result.improvement?.toFixed(1) || 0 }}%
                  </td>
                  <td class="py-3 px-4 text-right">
                    <span v-if="result.confidence" :class="result.confidence >= 95 ? 'text-green-600 font-medium' : 'text-gray-500'">
                      {{ result.confidence?.toFixed(1) }}%
                    </span>
                    <span v-else class="text-gray-400">-</span>
                  </td>
                  <td class="py-3 px-4 text-center">
                    <span v-if="result.is_winner" class="inline-flex items-center px-2 py-0.5 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 text-xs rounded-full">
                      <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                      Winner
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Statistical Note -->
          <div class="mt-4 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg text-sm text-gray-600 dark:text-gray-400">
            <p><strong>Note:</strong> A confidence level of 95% or higher indicates statistically significant results. Minimum sample size: {{ selectedExperiment.min_sample_size || 100 }} per variant.</p>
          </div>
        </div>
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex justify-end">
          <button @click="showResultsModal = false" class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600">Close</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue';
import axios from 'axios';

interface Variant {
  id?: number;
  name: string;
  slug?: string;
  is_control: boolean;
  weight: number;
  content: Record<string, any>;
  impressions?: number;
  conversions?: number;
  conversion_rate?: number;
}

interface Experiment {
  id: number;
  name: string;
  slug: string;
  description: string;
  type: string;
  location: string;
  status: string;
  traffic_percentage: number;
  primary_goal: string;
  min_sample_size: number;
  variants: Variant[];
}

interface ExperimentResult {
  variant_id: number;
  variant_name: string;
  is_control: boolean;
  impressions: number;
  conversions: number;
  conversion_rate: number;
  improvement: number;
  confidence: number | null;
  is_winner: boolean;
}

const loading = ref(true);
const saving = ref(false);
const calculating = ref(false);

const experiments = ref<Experiment[]>([]);
const selectedExperiment = ref<Experiment | null>(null);
const experimentResults = ref<ExperimentResult[]>([]);

const showCreateModal = ref(false);
const showEditModal = ref(false);
const showResultsModal = ref(false);

const stats = reactive({
  total: 0,
  running: 0,
  total_impressions: 0,
  total_conversions: 0,
});

const filters = reactive({
  status: '',
  type: '',
  search: '',
});

const pagination = reactive({
  current_page: 1,
  last_page: 1,
  per_page: 10,
  total: 0,
  from: 0,
  to: 0,
});

const defaultForm = () => ({
  name: '',
  description: '',
  type: 'text',
  location: '',
  primary_goal: 'click',
  traffic_percentage: 100,
  min_sample_size: 100,
  min_confidence: 0.95,
  variants: [
    { name: 'Control', is_control: true, weight: 50, content: { text: '' } },
    { name: 'Variant A', is_control: false, weight: 50, content: { text: '' } },
  ],
});

const form = reactive(defaultForm());

let debounceTimer: ReturnType<typeof setTimeout> | null = null;

const debouncedFetch = () => {
  if (debounceTimer) clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => fetchExperiments(), 300);
};

const fetchExperiments = async (page = 1) => {
  loading.value = true;
  try {
    const params: Record<string, any> = { page, per_page: pagination.per_page };
    if (filters.status) params.status = filters.status;
    if (filters.type) params.type = filters.type;
    if (filters.search) params.search = filters.search;

    const response = await axios.get('/api/admin/experiments', { params });
    experiments.value = response.data.data;
    Object.assign(pagination, {
      current_page: response.data.current_page,
      last_page: response.data.last_page,
      total: response.data.total,
      from: response.data.from || 0,
      to: response.data.to || 0,
    });
  } catch (error) {
    console.error('Failed to fetch experiments:', error);
  } finally {
    loading.value = false;
  }
};

const fetchStats = async () => {
  try {
    const response = await axios.get('/api/admin/experiments/stats');
    Object.assign(stats, response.data.data);
  } catch (error) {
    console.error('Failed to fetch stats:', error);
  }
};

const goToPage = (page: number) => {
  if (page >= 1 && page <= pagination.last_page) {
    fetchExperiments(page);
  }
};

const addVariant = () => {
  const index = form.variants.length;
  form.variants.push({
    name: `Variant ${String.fromCharCode(64 + index)}`,
    is_control: false,
    weight: Math.floor(100 / (form.variants.length + 1)),
    content: { text: '' },
  });
};

const removeVariant = (index: number) => {
  if (form.variants.length > 2) {
    form.variants.splice(index, 1);
  }
};

const closeModals = () => {
  showCreateModal.value = false;
  showEditModal.value = false;
  showResultsModal.value = false;
  Object.assign(form, defaultForm());
  selectedExperiment.value = null;
};

const saveExperiment = async () => {
  saving.value = true;
  try {
    if (showEditModal.value && selectedExperiment.value) {
      await axios.put(`/api/admin/experiments/${selectedExperiment.value.id}`, form);
    } else {
      await axios.post('/api/admin/experiments', form);
    }
    closeModals();
    fetchExperiments();
    fetchStats();
  } catch (error: any) {
    console.error('Failed to save experiment:', error);
    alert(error.response?.data?.message || 'Failed to save experiment');
  } finally {
    saving.value = false;
  }
};

const editExperiment = (experiment: Experiment) => {
  selectedExperiment.value = experiment;
  Object.assign(form, {
    name: experiment.name,
    description: experiment.description,
    type: experiment.type,
    location: experiment.location,
    primary_goal: experiment.primary_goal,
    traffic_percentage: experiment.traffic_percentage,
    variants: experiment.variants.map(v => ({
      id: v.id,
      name: v.name,
      is_control: v.is_control,
      weight: v.weight,
      content: v.content || { text: '' },
    })),
  });
  showEditModal.value = true;
};

const deleteExperiment = async (experiment: Experiment) => {
  if (!confirm(`Are you sure you want to delete "${experiment.name}"?`)) return;

  try {
    await axios.delete(`/api/admin/experiments/${experiment.id}`);
    fetchExperiments();
    fetchStats();
  } catch (error: any) {
    alert(error.response?.data?.message || 'Failed to delete experiment');
  }
};

const startExperiment = async (experiment: Experiment) => {
  try {
    await axios.post(`/api/admin/experiments/${experiment.id}/start`);
    fetchExperiments();
    fetchStats();
  } catch (error: any) {
    alert(error.response?.data?.message || 'Failed to start experiment');
  }
};

const pauseExperiment = async (experiment: Experiment) => {
  try {
    await axios.post(`/api/admin/experiments/${experiment.id}/pause`);
    fetchExperiments();
  } catch (error: any) {
    alert(error.response?.data?.message || 'Failed to pause experiment');
  }
};

const completeExperiment = async (experiment: Experiment) => {
  if (!confirm(`Complete "${experiment.name}"? This will stop collecting data.`)) return;

  try {
    await axios.post(`/api/admin/experiments/${experiment.id}/complete`);
    fetchExperiments();
    fetchStats();
  } catch (error: any) {
    alert(error.response?.data?.message || 'Failed to complete experiment');
  }
};

const viewResults = async (experiment: Experiment) => {
  selectedExperiment.value = experiment;
  showResultsModal.value = true;

  try {
    const response = await axios.get(`/api/admin/experiments/${experiment.id}/results`);
    experimentResults.value = response.data.data || [];
  } catch (error) {
    console.error('Failed to fetch results:', error);
    experimentResults.value = [];
  }
};

const calculateResults = async () => {
  if (!selectedExperiment.value) return;

  calculating.value = true;
  try {
    await axios.post(`/api/admin/experiments/${selectedExperiment.value.id}/calculate-results`);
    const response = await axios.get(`/api/admin/experiments/${selectedExperiment.value.id}/results`);
    experimentResults.value = response.data.data || [];
  } catch (error) {
    console.error('Failed to calculate results:', error);
  } finally {
    calculating.value = false;
  }
};

const formatNumber = (num: number | undefined): string => {
  if (num === undefined || num === null) return '0';
  return num.toLocaleString();
};

const getStatusClass = (status: string): string => {
  const classes: Record<string, string> = {
    draft: 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400',
    running: 'bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400',
    paused: 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-600 dark:text-yellow-400',
    completed: 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400',
  };
  return classes[status] || classes.draft;
};

const getConversionRateClass = (rate: number | undefined): string => {
  if (!rate) return 'text-gray-500';
  if (rate >= 5) return 'text-green-600 dark:text-green-400';
  if (rate >= 2) return 'text-blue-600 dark:text-blue-400';
  return 'text-gray-700 dark:text-gray-300';
};

onMounted(() => {
  fetchExperiments();
  fetchStats();
});
</script>
