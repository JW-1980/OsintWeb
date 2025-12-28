---
name: javascript-vuejs
description: Expert guidance for JavaScript ES6+, Vue.js 3 with Composition API, state management, and Laravel Inertia integration
version: 1.0.2
tags: [javascript, vue, frontend, composition-api, pinia, inertia, es6]
trigger_keywords: [sk-javascript-vuejs, javascript, vue, vuejs, "composition api", pinia, inertia, "javascript expert", "vue expert", "JavaScript and Vue.js Expert"]
globs:
  - "**/*.js"
  - "**/*.mjs"
  - "**/*.cjs"
  - "**/*.vue"
  - "**/*.ts"
  - "**/vite.config.*"
  - "**/vue.config.*"
  - "**/jsconfig.json"
  - "**/tsconfig.json"
---
# JavaScript and Vue.js Expert Skill

You are an expert in JavaScript (ES6+) and Vue.js 3, specializing in modern web development with the Composition API, state management, and integration with Laravel Inertia.

## When to Use

Use this skill when:

1. **Building Vue.js 3 Components** - Creating new Vue components using Composition API and script setup
2. **Implementing State Management** - Setting up Pinia stores for application-wide state
3. **Working with Inertia.js** - Building Laravel-powered SPAs with Inertia and Vue
4. **Writing Modern JavaScript** - Using ES6+ features like destructuring, async/await, modules
5. **Creating Composables** - Extracting reusable logic into composition functions
6. **Handling Forms** - Building forms with validation using Inertia's useForm
7. **Implementing Routing** - Setting up Vue Router with guards and nested routes
8. **Optimizing Performance** - Lazy loading, virtual scrolling, memoization
9. **Writing Tests** - Unit and integration testing Vue components with Vitest
10. **Debugging Reactivity Issues** - Troubleshooting Vue's reactivity system

## Core Principles

### Code Quality
- Write clean, readable, and maintainable code
- Follow the principle of least surprise
- Prefer explicit over implicit code
- Use meaningful variable and function names
- Keep functions small and focused (single responsibility)
- Avoid premature optimization

### Modern JavaScript (ES6+)
- Use `const` by default, `let` when reassignment is needed, never `var`
- Prefer arrow functions for callbacks and short functions
- Use template literals for string interpolation
- Destructure objects and arrays when it improves readability
- Use spread/rest operators appropriately
- Leverage optional chaining (`?.`) and nullish coalescing (`??`)
- Use async/await over raw Promises for better readability

## JavaScript Best Practices

### Variable Declarations
```javascript
// Good - const for values that won't be reassigned
const API_BASE_URL = '/api/v1';
const user = { name: 'John', age: 30 };

// Good - let for values that will be reassigned
let count = 0;
let isLoading = false;

// Bad - avoid var
var oldStyle = 'deprecated';
```

### Arrow Functions
```javascript
// Good - arrow functions for callbacks
const numbers = [1, 2, 3];
const doubled = numbers.map(n => n * 2);
const filtered = numbers.filter(n => n > 1);

// Good - arrow function with destructuring
const users = [{ name: 'John', age: 30 }];
const names = users.map(({ name }) => name);

// Use regular functions for methods that need 'this'
const obj = {
  value: 42,
  getValue() {
    return this.value;
  }
};
```

### Destructuring
```javascript
// Object destructuring
const { name, age, email = 'N/A' } = user;

// Array destructuring
const [first, second, ...rest] = items;

// Function parameter destructuring
function createUser({ name, email, role = 'user' }) {
  return { name, email, role, createdAt: new Date() };
}

// Nested destructuring
const { address: { city, country } } = user;
```

### Spread and Rest Operators
```javascript
// Spread for arrays
const combined = [...array1, ...array2];
const copy = [...original];

// Spread for objects
const updated = { ...original, newProperty: 'value' };
const merged = { ...defaults, ...options };

// Rest parameters
function sum(...numbers) {
  return numbers.reduce((a, b) => a + b, 0);
}
```

### Optional Chaining and Nullish Coalescing
```javascript
// Optional chaining
const city = user?.address?.city;
const result = obj?.method?.();
const item = arr?.[0];

// Nullish coalescing
const name = user.name ?? 'Anonymous';
const count = response.count ?? 0;

// Combining both
const displayName = user?.profile?.displayName ?? user?.name ?? 'Guest';
```

### Async/Await
```javascript
// Good - async/await
async function fetchUser(id) {
  try {
    const response = await fetch(`/api/users/${id}`);
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }
    return await response.json();
  } catch (error) {
    console.error('Failed to fetch user:', error);
    throw error;
  }
}

// Parallel execution
async function fetchAllData() {
  const [users, posts, comments] = await Promise.all([
    fetchUsers(),
    fetchPosts(),
    fetchComments()
  ]);
  return { users, posts, comments };
}

// Sequential when needed
async function processItems(items) {
  const results = [];
  for (const item of items) {
    const result = await processItem(item);
    results.push(result);
  }
  return results;
}
```

### Array Methods
```javascript
// map - transform each element
const names = users.map(user => user.name);

// filter - select elements matching condition
const adults = users.filter(user => user.age >= 18);

// find - get first matching element
const admin = users.find(user => user.role === 'admin');

// some/every - check conditions
const hasAdmin = users.some(user => user.role === 'admin');
const allActive = users.every(user => user.isActive);

// reduce - accumulate values
const total = items.reduce((sum, item) => sum + item.price, 0);

// Chaining methods
const activeAdminNames = users
  .filter(user => user.isActive)
  .filter(user => user.role === 'admin')
  .map(user => user.name);
```

### Object Methods
```javascript
// Object.keys, values, entries
const keys = Object.keys(obj);
const values = Object.values(obj);
const entries = Object.entries(obj);

// Object.fromEntries
const filtered = Object.fromEntries(
  Object.entries(obj).filter(([key, value]) => value !== null)
);

// Object.assign (prefer spread for simple cases)
const merged = Object.assign({}, defaults, options);
```

### Classes (when needed)
```javascript
class ApiService {
  #baseUrl;  // Private field

  constructor(baseUrl) {
    this.#baseUrl = baseUrl;
  }

  async get(endpoint) {
    const response = await fetch(`${this.#baseUrl}${endpoint}`);
    return response.json();
  }

  static create(baseUrl) {
    return new ApiService(baseUrl);
  }
}
```

### Modules
```javascript
// Named exports
export const API_URL = '/api';
export function formatDate(date) { /* ... */ }
export class UserService { /* ... */ }

// Default export
export default function createApp() { /* ... */ }

// Imports
import createApp from './app';
import { API_URL, formatDate } from './utils';
import * as utils from './utils';
import { formatDate as formatDateTime } from './utils';

// Dynamic imports
const module = await import('./heavy-module.js');
```

## Vue.js 3 Best Practices

### Composition API Fundamentals

#### Script Setup (Preferred)
```vue
<script setup>
import { ref, computed, onMounted } from 'vue';
import { useUserStore } from '@/stores/user';

// Props
const props = defineProps({
  userId: {
    type: Number,
    required: true
  },
  showDetails: {
    type: Boolean,
    default: false
  }
});

// Emits
const emit = defineEmits(['update', 'delete']);

// Reactive state
const isLoading = ref(false);
const user = ref(null);

// Computed
const fullName = computed(() => {
  if (!user.value) return '';
  return `${user.value.firstName} ${user.value.lastName}`;
});

// Methods
async function fetchUser() {
  isLoading.value = true;
  try {
    user.value = await api.getUser(props.userId);
  } finally {
    isLoading.value = false;
  }
}

function handleUpdate() {
  emit('update', user.value);
}

// Lifecycle
onMounted(() => {
  fetchUser();
});
</script>
```

### Reactivity System

#### ref vs reactive
```javascript
import { ref, reactive, toRefs } from 'vue';

// ref - for primitives and when you need to replace the whole value
const count = ref(0);
const name = ref('');
const user = ref(null);  // Can be replaced entirely

// Access with .value in script
count.value++;
name.value = 'John';

// reactive - for objects that won't be replaced
const state = reactive({
  users: [],
  isLoading: false,
  error: null
});

// Direct access (no .value)
state.isLoading = true;
state.users.push(newUser);

// toRefs - extract refs from reactive object
const { users, isLoading } = toRefs(state);
```

#### Computed Properties
```javascript
import { ref, computed } from 'vue';

const items = ref([]);
const searchQuery = ref('');

// Simple computed
const itemCount = computed(() => items.value.length);

// Computed with getter and setter
const selectedIds = computed({
  get() {
    return items.value.filter(i => i.selected).map(i => i.id);
  },
  set(ids) {
    items.value.forEach(item => {
      item.selected = ids.includes(item.id);
    });
  }
});

// Computed with dependencies
const filteredItems = computed(() => {
  const query = searchQuery.value.toLowerCase();
  if (!query) return items.value;
  return items.value.filter(item =>
    item.name.toLowerCase().includes(query)
  );
});
```

#### Watchers
```javascript
import { ref, watch, watchEffect } from 'vue';

const userId = ref(null);
const user = ref(null);

// Watch specific source
watch(userId, async (newId, oldId) => {
  if (newId) {
    user.value = await fetchUser(newId);
  }
});

// Watch with options
watch(
  userId,
  async (newId) => {
    user.value = await fetchUser(newId);
  },
  { immediate: true }  // Run immediately on mount
);

// Watch multiple sources
watch(
  [firstName, lastName],
  ([newFirst, newLast], [oldFirst, oldLast]) => {
    fullName.value = `${newFirst} ${newLast}`;
  }
);

// watchEffect - automatically tracks dependencies
watchEffect(async () => {
  if (userId.value) {
    user.value = await fetchUser(userId.value);
  }
});

// Cleanup function
watchEffect((onCleanup) => {
  const controller = new AbortController();
  fetchData(controller.signal);

  onCleanup(() => {
    controller.abort();
  });
});
```

### Component Patterns

#### Props Definition
```vue
<script setup>
// Simple props
const props = defineProps(['title', 'content']);

// Props with types
const props = defineProps({
  id: Number,
  title: String,
  items: Array,
  user: Object
});

// Props with validation
const props = defineProps({
  status: {
    type: String,
    required: true,
    validator: (value) => ['pending', 'active', 'completed'].includes(value)
  },
  count: {
    type: Number,
    default: 0
  },
  config: {
    type: Object,
    default: () => ({})  // Factory function for objects/arrays
  }
});

// TypeScript style (when using TS)
interface Props {
  userId: number;
  userName?: string;
  isActive?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  userName: 'Guest',
  isActive: true
});
</script>
```

#### Events/Emits
```vue
<script setup>
// Simple emits
const emit = defineEmits(['submit', 'cancel']);

// Emits with validation
const emit = defineEmits({
  submit: (payload) => {
    return payload.email && payload.password;
  },
  cancel: null  // No validation
});

// TypeScript style
const emit = defineEmits<{
  (e: 'submit', payload: FormData): void;
  (e: 'cancel'): void;
}>();

// Usage
function handleSubmit() {
  emit('submit', formData);
}
</script>
```

#### v-model on Components
```vue
<!-- Parent -->
<template>
  <CustomInput v-model="searchText" />
  <CustomInput v-model:firstName="first" v-model:lastName="last" />
</template>

<!-- Child (CustomInput.vue) -->
<script setup>
const props = defineProps({
  modelValue: String,
  firstName: String,
  lastName: String
});

const emit = defineEmits(['update:modelValue', 'update:firstName', 'update:lastName']);

// Using computed for cleaner binding
const value = computed({
  get() {
    return props.modelValue;
  },
  set(newValue) {
    emit('update:modelValue', newValue);
  }
});
</script>

<template>
  <input v-model="value" />
</template>
```

#### Slots
```vue
<!-- Parent -->
<template>
  <Card>
    <template #header>
      <h2>Title</h2>
    </template>

    <p>Default slot content</p>

    <template #footer="{ canSubmit }">
      <button :disabled="!canSubmit">Submit</button>
    </template>
  </Card>
</template>

<!-- Card.vue -->
<script setup>
const canSubmit = ref(true);
</script>

<template>
  <div class="card">
    <header>
      <slot name="header" />
    </header>

    <main>
      <slot />  <!-- Default slot -->
    </main>

    <footer>
      <slot name="footer" :canSubmit="canSubmit" />
    </footer>
  </div>
</template>
```

#### Expose
```vue
<script setup>
import { ref } from 'vue';

const inputRef = ref(null);
const value = ref('');

function focus() {
  inputRef.value?.focus();
}

function reset() {
  value.value = '';
}

// Expose methods to parent
defineExpose({
  focus,
  reset
});
</script>
```

### Composables (Reusable Logic)

#### Creating Composables
```javascript
// composables/useAsync.js
import { ref, computed } from 'vue';

export function useAsync(asyncFn) {
  const data = ref(null);
  const error = ref(null);
  const isLoading = ref(false);

  async function execute(...args) {
    isLoading.value = true;
    error.value = null;

    try {
      data.value = await asyncFn(...args);
    } catch (e) {
      error.value = e;
    } finally {
      isLoading.value = false;
    }
  }

  return {
    data,
    error,
    isLoading,
    execute
  };
}

// composables/useDebounce.js
import { ref, watch } from 'vue';

export function useDebounce(value, delay = 300) {
  const debouncedValue = ref(value.value);
  let timeout;

  watch(value, (newValue) => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
      debouncedValue.value = newValue;
    }, delay);
  });

  return debouncedValue;
}

// composables/useLocalStorage.js
import { ref, watch } from 'vue';

export function useLocalStorage(key, defaultValue) {
  const stored = localStorage.getItem(key);
  const value = ref(stored ? JSON.parse(stored) : defaultValue);

  watch(value, (newValue) => {
    localStorage.setItem(key, JSON.stringify(newValue));
  }, { deep: true });

  return value;
}
```

#### Using Composables
```vue
<script setup>
import { ref, watch } from 'vue';
import { useAsync } from '@/composables/useAsync';
import { useDebounce } from '@/composables/useDebounce';
import { fetchUsers } from '@/api/users';

const searchQuery = ref('');
const debouncedQuery = useDebounce(searchQuery, 300);

const { data: users, isLoading, error, execute: loadUsers } = useAsync(fetchUsers);

watch(debouncedQuery, (query) => {
  loadUsers({ search: query });
}, { immediate: true });
</script>
```

### Advanced Composable Patterns

#### Pagination Composable
```javascript
// composables/usePagination.js
import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';

export function usePagination(initialData, options = {}) {
  const { preserveState = true, preserveScroll = true } = options;

  const items = ref(initialData.data || []);
  const meta = ref({
    current_page: initialData.current_page || 1,
    last_page: initialData.last_page || 1,
    per_page: initialData.per_page || 15,
    total: initialData.total || 0,
    from: initialData.from || 0,
    to: initialData.to || 0
  });
  const links = ref(initialData.links || []);

  const hasNextPage = computed(() => meta.value.current_page < meta.value.last_page);
  const hasPrevPage = computed(() => meta.value.current_page > 1);
  const isEmpty = computed(() => items.value.length === 0);
  const totalPages = computed(() => meta.value.last_page);

  function goToPage(page) {
    if (page < 1 || page > meta.value.last_page) return;

    router.get(window.location.pathname, { page }, {
      preserveState,
      preserveScroll,
      only: ['items'], // Replace with your prop name
    });
  }

  function nextPage() {
    if (hasNextPage.value) {
      goToPage(meta.value.current_page + 1);
    }
  }

  function prevPage() {
    if (hasPrevPage.value) {
      goToPage(meta.value.current_page - 1);
    }
  }

  function updateData(newData) {
    items.value = newData.data || [];
    meta.value = {
      current_page: newData.current_page,
      last_page: newData.last_page,
      per_page: newData.per_page,
      total: newData.total,
      from: newData.from,
      to: newData.to
    };
    links.value = newData.links || [];
  }

  return {
    items,
    meta,
    links,
    hasNextPage,
    hasPrevPage,
    isEmpty,
    totalPages,
    goToPage,
    nextPage,
    prevPage,
    updateData
  };
}
```

#### Form Wizard Composable
```javascript
// composables/useFormWizard.js
import { ref, computed } from 'vue';

export function useFormWizard(steps) {
  const currentStepIndex = ref(0);
  const completedSteps = ref(new Set());
  const stepData = ref({});

  const currentStep = computed(() => steps[currentStepIndex.value]);
  const isFirstStep = computed(() => currentStepIndex.value === 0);
  const isLastStep = computed(() => currentStepIndex.value === steps.length - 1);
  const progress = computed(() => ((currentStepIndex.value + 1) / steps.length) * 100);

  function goToStep(index) {
    if (index >= 0 && index < steps.length) {
      currentStepIndex.value = index;
    }
  }

  function nextStep() {
    if (!isLastStep.value) {
      completedSteps.value.add(currentStepIndex.value);
      currentStepIndex.value++;
    }
  }

  function prevStep() {
    if (!isFirstStep.value) {
      currentStepIndex.value--;
    }
  }

  function setStepData(step, data) {
    stepData.value[step] = { ...stepData.value[step], ...data };
  }

  function getStepData(step) {
    return stepData.value[step] || {};
  }

  function isStepCompleted(index) {
    return completedSteps.value.has(index);
  }

  function reset() {
    currentStepIndex.value = 0;
    completedSteps.value.clear();
    stepData.value = {};
  }

  return {
    currentStep,
    currentStepIndex,
    isFirstStep,
    isLastStep,
    progress,
    completedSteps,
    goToStep,
    nextStep,
    prevStep,
    setStepData,
    getStepData,
    isStepCompleted,
    reset
  };
}
```

#### Selection Manager Composable
```javascript
// composables/useSelection.js
import { ref, computed } from 'vue';

export function useSelection(items, options = {}) {
  const { idKey = 'id', multiple = true } = options;

  const selectedIds = ref(new Set());

  const selectedItems = computed(() =>
    items.value.filter(item => selectedIds.value.has(item[idKey]))
  );

  const isAllSelected = computed(() =>
    items.value.length > 0 && items.value.every(item => selectedIds.value.has(item[idKey]))
  );

  const isSomeSelected = computed(() =>
    selectedIds.value.size > 0 && !isAllSelected.value
  );

  const hasSelection = computed(() => selectedIds.value.size > 0);
  const selectionCount = computed(() => selectedIds.value.size);

  function isSelected(item) {
    return selectedIds.value.has(item[idKey]);
  }

  function select(item) {
    if (multiple) {
      selectedIds.value.add(item[idKey]);
    } else {
      selectedIds.value.clear();
      selectedIds.value.add(item[idKey]);
    }
  }

  function deselect(item) {
    selectedIds.value.delete(item[idKey]);
  }

  function toggle(item) {
    if (isSelected(item)) {
      deselect(item);
    } else {
      select(item);
    }
  }

  function selectAll() {
    items.value.forEach(item => selectedIds.value.add(item[idKey]));
  }

  function deselectAll() {
    selectedIds.value.clear();
  }

  function toggleAll() {
    if (isAllSelected.value) {
      deselectAll();
    } else {
      selectAll();
    }
  }

  return {
    selectedIds,
    selectedItems,
    isAllSelected,
    isSomeSelected,
    hasSelection,
    selectionCount,
    isSelected,
    select,
    deselect,
    toggle,
    selectAll,
    deselectAll,
    toggleAll
  };
}
```

#### Modal Manager Composable
```javascript
// composables/useModal.js
import { ref, computed, watch } from 'vue';
import { useScrollLock } from '@vueuse/core';

export function useModal(options = {}) {
  const { closeOnEscape = true, closeOnClickOutside = true, lockScroll = true } = options;

  const isOpen = ref(false);
  const modalData = ref(null);
  const scrollLock = lockScroll ? useScrollLock(document.body) : { value: false };

  watch(isOpen, (open) => {
    if (lockScroll) {
      scrollLock.value = open;
    }
  });

  function open(data = null) {
    modalData.value = data;
    isOpen.value = true;
  }

  function close() {
    isOpen.value = false;
    modalData.value = null;
  }

  function toggle() {
    isOpen.value ? close() : open();
  }

  function handleKeydown(event) {
    if (closeOnEscape && event.key === 'Escape' && isOpen.value) {
      close();
    }
  }

  function handleClickOutside(event, modalElement) {
    if (closeOnClickOutside && !modalElement.contains(event.target)) {
      close();
    }
  }

  return {
    isOpen,
    modalData,
    open,
    close,
    toggle,
    handleKeydown,
    handleClickOutside
  };
}
```

#### Table Sorting/Filtering Composable
```javascript
// composables/useTable.js
import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';

export function useTable(initialFilters = {}) {
  const sortBy = ref(initialFilters.sort || '');
  const sortDirection = ref(initialFilters.direction || 'asc');
  const search = ref(initialFilters.search || '');
  const perPage = ref(initialFilters.per_page || 15);
  const filters = ref(initialFilters.filters || {});

  const sortParams = computed(() => {
    if (!sortBy.value) return {};
    return {
      sort: sortBy.value,
      direction: sortDirection.value
    };
  });

  function toggleSort(column) {
    if (sortBy.value === column) {
      sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
      sortBy.value = column;
      sortDirection.value = 'asc';
    }
    applyFilters();
  }

  function getSortIcon(column) {
    if (sortBy.value !== column) return 'sort';
    return sortDirection.value === 'asc' ? 'sort-asc' : 'sort-desc';
  }

  function setFilter(key, value) {
    filters.value[key] = value;
    applyFiltersDebounced();
  }

  function clearFilters() {
    search.value = '';
    filters.value = {};
    applyFilters();
  }

  const applyFiltersDebounced = useDebounceFn(() => {
    applyFilters();
  }, 300);

  function applyFilters() {
    const params = {
      ...sortParams.value,
      search: search.value || undefined,
      per_page: perPage.value,
      ...filters.value,
      page: 1 // Reset to first page on filter change
    };

    // Remove undefined values
    Object.keys(params).forEach(key =>
      params[key] === undefined && delete params[key]
    );

    router.get(window.location.pathname, params, {
      preserveState: true,
      preserveScroll: true
    });
  }

  // Watch search and apply debounced
  watch(search, applyFiltersDebounced);

  return {
    sortBy,
    sortDirection,
    search,
    perPage,
    filters,
    toggleSort,
    getSortIcon,
    setFilter,
    clearFilters,
    applyFilters
  };
}
```

#### Confirmation Dialog Composable
```javascript
// composables/useConfirmation.js
import { ref } from 'vue';

export function useConfirmation() {
  const isOpen = ref(false);
  const title = ref('');
  const message = ref('');
  const confirmText = ref('Confirm');
  const cancelText = ref('Cancel');
  const variant = ref('danger'); // 'danger' | 'warning' | 'info'
  const resolvePromise = ref(null);

  function confirm(options = {}) {
    title.value = options.title || 'Are you sure?';
    message.value = options.message || '';
    confirmText.value = options.confirmText || 'Confirm';
    cancelText.value = options.cancelText || 'Cancel';
    variant.value = options.variant || 'danger';
    isOpen.value = true;

    return new Promise((resolve) => {
      resolvePromise.value = resolve;
    });
  }

  function handleConfirm() {
    resolvePromise.value?.(true);
    close();
  }

  function handleCancel() {
    resolvePromise.value?.(false);
    close();
  }

  function close() {
    isOpen.value = false;
    resolvePromise.value = null;
  }

  return {
    isOpen,
    title,
    message,
    confirmText,
    cancelText,
    variant,
    confirm,
    handleConfirm,
    handleCancel
  };
}

// Usage in component
const { confirm, isOpen, title, message, handleConfirm, handleCancel } = useConfirmation();

async function deleteUser(user) {
  const confirmed = await confirm({
    title: 'Delete User',
    message: `Are you sure you want to delete ${user.name}?`,
    confirmText: 'Delete',
    variant: 'danger'
  });

  if (confirmed) {
    router.delete(route('users.destroy', user.id));
  }
}
```

#### Toast Notifications Composable
```javascript
// composables/useToast.js
import { ref } from 'vue';

let toastId = 0;

const toasts = ref([]);

export function useToast() {
  function show(options) {
    const id = ++toastId;
    const toast = {
      id,
      title: options.title || '',
      message: options.message,
      type: options.type || 'info', // 'success' | 'error' | 'warning' | 'info'
      duration: options.duration ?? 5000,
      dismissible: options.dismissible ?? true
    };

    toasts.value.push(toast);

    if (toast.duration > 0) {
      setTimeout(() => dismiss(id), toast.duration);
    }

    return id;
  }

  function dismiss(id) {
    const index = toasts.value.findIndex(t => t.id === id);
    if (index > -1) {
      toasts.value.splice(index, 1);
    }
  }

  function success(message, options = {}) {
    return show({ ...options, message, type: 'success' });
  }

  function error(message, options = {}) {
    return show({ ...options, message, type: 'error', duration: options.duration ?? 10000 });
  }

  function warning(message, options = {}) {
    return show({ ...options, message, type: 'warning' });
  }

  function info(message, options = {}) {
    return show({ ...options, message, type: 'info' });
  }

  function clearAll() {
    toasts.value = [];
  }

  return {
    toasts,
    show,
    dismiss,
    success,
    error,
    warning,
    info,
    clearAll
  };
}
```

### State Management with Pinia

#### Store Definition
```javascript
// stores/user.js
import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

// Setup store (Composition API style - recommended)
export const useUserStore = defineStore('user', () => {
  // State
  const user = ref(null);
  const token = ref(localStorage.getItem('token'));
  const isLoading = ref(false);

  // Getters
  const isAuthenticated = computed(() => !!user.value && !!token.value);
  const fullName = computed(() => {
    if (!user.value) return '';
    return `${user.value.firstName} ${user.value.lastName}`;
  });

  // Actions
  async function login(credentials) {
    isLoading.value = true;
    try {
      const response = await api.login(credentials);
      user.value = response.user;
      token.value = response.token;
      localStorage.setItem('token', response.token);
    } finally {
      isLoading.value = false;
    }
  }

  function logout() {
    user.value = null;
    token.value = null;
    localStorage.removeItem('token');
  }

  async function fetchProfile() {
    if (!token.value) return;
    user.value = await api.getProfile();
  }

  return {
    // State
    user,
    token,
    isLoading,
    // Getters
    isAuthenticated,
    fullName,
    // Actions
    login,
    logout,
    fetchProfile
  };
});

// Options API style store
export const useCounterStore = defineStore('counter', {
  state: () => ({
    count: 0
  }),
  getters: {
    doubleCount: (state) => state.count * 2
  },
  actions: {
    increment() {
      this.count++;
    }
  }
});
```

#### Using Stores
```vue
<script setup>
import { storeToRefs } from 'pinia';
import { useUserStore } from '@/stores/user';

const userStore = useUserStore();

// Destructure with storeToRefs to maintain reactivity
const { user, isAuthenticated, isLoading } = storeToRefs(userStore);

// Actions can be destructured directly
const { login, logout } = userStore;

async function handleLogin(credentials) {
  await login(credentials);
  if (isAuthenticated.value) {
    router.push('/dashboard');
  }
}
</script>
```

### Vue Router

#### Route Configuration
```javascript
// router/index.js
import { createRouter, createWebHistory } from 'vue-router';
import { useUserStore } from '@/stores/user';

const routes = [
  {
    path: '/',
    name: 'home',
    component: () => import('@/pages/Home.vue')
  },
  {
    path: '/dashboard',
    name: 'dashboard',
    component: () => import('@/pages/Dashboard.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/users/:id',
    name: 'user',
    component: () => import('@/pages/User.vue'),
    props: true  // Pass route params as props
  },
  {
    path: '/admin',
    component: () => import('@/layouts/AdminLayout.vue'),
    meta: { requiresAuth: true, requiresAdmin: true },
    children: [
      {
        path: '',
        name: 'admin-dashboard',
        component: () => import('@/pages/admin/Dashboard.vue')
      },
      {
        path: 'users',
        name: 'admin-users',
        component: () => import('@/pages/admin/Users.vue')
      }
    ]
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

// Navigation guard
router.beforeEach((to, from) => {
  const userStore = useUserStore();

  if (to.meta.requiresAuth && !userStore.isAuthenticated) {
    return { name: 'login', query: { redirect: to.fullPath } };
  }

  if (to.meta.requiresAdmin && !userStore.isAdmin) {
    return { name: 'home' };
  }
});

export default router;
```

#### Using Router in Components
```vue
<script setup>
import { useRouter, useRoute } from 'vue-router';

const router = useRouter();
const route = useRoute();

// Access route params
const userId = computed(() => route.params.id);

// Navigation
function goToUser(id) {
  router.push({ name: 'user', params: { id } });
}

function goBack() {
  router.back();
}

// With query params
function search(query) {
  router.push({
    name: 'search',
    query: { q: query, page: 1 }
  });
}
</script>
```

## Laravel Inertia.js Integration

### Page Components
```vue
<!-- Pages/Users/Index.vue -->
<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
  users: Object,  // Paginated data from Laravel
  filters: Object
});

function deleteUser(id) {
  if (confirm('Are you sure?')) {
    router.delete(route('users.destroy', id), {
      preserveScroll: true
    });
  }
}
</script>

<template>
  <Head title="Users" />

  <AppLayout>
    <div class="container">
      <h1>Users</h1>

      <Link :href="route('users.create')" class="btn btn-primary">
        Add User
      </Link>

      <table>
        <tr v-for="user in users.data" :key="user.id">
          <td>{{ user.name }}</td>
          <td>{{ user.email }}</td>
          <td>
            <Link :href="route('users.edit', user.id)">Edit</Link>
            <button @click="deleteUser(user.id)">Delete</button>
          </td>
        </tr>
      </table>

      <!-- Pagination -->
      <Link
        v-for="link in users.links"
        :key="link.label"
        :href="link.url"
        v-html="link.label"
      />
    </div>
  </AppLayout>
</template>
```

### Forms with Inertia
```vue
<script setup>
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
  user: Object  // For edit, null for create
});

const form = useForm({
  name: props.user?.name ?? '',
  email: props.user?.email ?? '',
  password: '',
  password_confirmation: ''
});

function submit() {
  if (props.user) {
    form.put(route('users.update', props.user.id), {
      preserveScroll: true,
      onSuccess: () => form.reset('password', 'password_confirmation')
    });
  } else {
    form.post(route('users.store'), {
      onSuccess: () => form.reset()
    });
  }
}
</script>

<template>
  <form @submit.prevent="submit">
    <div>
      <label>Name</label>
      <input v-model="form.name" type="text" />
      <span v-if="form.errors.name">{{ form.errors.name }}</span>
    </div>

    <div>
      <label>Email</label>
      <input v-model="form.email" type="email" />
      <span v-if="form.errors.email">{{ form.errors.email }}</span>
    </div>

    <div>
      <label>Password</label>
      <input v-model="form.password" type="password" />
      <span v-if="form.errors.password">{{ form.errors.password }}</span>
    </div>

    <button type="submit" :disabled="form.processing">
      {{ form.processing ? 'Saving...' : 'Save' }}
    </button>
  </form>
</template>
```

### Shared Data
```vue
<script setup>
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

// Access shared data from HandleInertiaRequests middleware
const page = usePage();

const user = computed(() => page.props.auth.user);
const flash = computed(() => page.props.flash);
</script>
```

### Manual Visits
```javascript
import { router } from '@inertiajs/vue3';

// GET request
router.get('/users', { search: 'john' });

// POST request
router.post('/users', { name: 'John', email: 'john@example.com' });

// With options
router.post('/users', data, {
  preserveState: true,
  preserveScroll: true,
  only: ['users'],  // Partial reload
  onBefore: () => confirm('Continue?'),
  onStart: () => { /* ... */ },
  onProgress: (progress) => { /* ... */ },
  onSuccess: (page) => { /* ... */ },
  onError: (errors) => { /* ... */ },
  onFinish: () => { /* ... */ }
});
```

## Error Handling

### Component Error Boundaries
```vue
<!-- ErrorBoundary.vue -->
<script setup>
import { onErrorCaptured, ref } from 'vue';

const error = ref(null);

onErrorCaptured((err, instance, info) => {
  error.value = err;
  console.error('Error captured:', err, info);
  return false;  // Prevent error from propagating
});
</script>

<template>
  <div v-if="error" class="error-container">
    <h2>Something went wrong</h2>
    <p>{{ error.message }}</p>
    <button @click="error = null">Try again</button>
  </div>
  <slot v-else />
</template>
```

### Global Error Handler
```javascript
// main.js
const app = createApp(App);

app.config.errorHandler = (err, instance, info) => {
  console.error('Global error:', err);
  // Send to error tracking service
};

app.config.warnHandler = (msg, instance, trace) => {
  console.warn('Vue warning:', msg);
};
```

## Performance Optimization

### Component Lazy Loading
```javascript
import { defineAsyncComponent } from 'vue';

// Basic async component
const AsyncModal = defineAsyncComponent(() =>
  import('./components/Modal.vue')
);

// With loading and error states
const AsyncDashboard = defineAsyncComponent({
  loader: () => import('./pages/Dashboard.vue'),
  loadingComponent: LoadingSpinner,
  errorComponent: ErrorDisplay,
  delay: 200,  // Delay before showing loading
  timeout: 10000  // Timeout before showing error
});
```

### v-memo for List Optimization
```vue
<template>
  <div v-for="item in items" :key="item.id" v-memo="[item.id, item.selected]">
    <!-- Only re-renders when id or selected changes -->
    <ExpensiveComponent :item="item" />
  </div>
</template>
```

### Keep-Alive for Component Caching
```vue
<template>
  <KeepAlive :include="['Dashboard', 'UserList']" :max="10">
    <component :is="currentComponent" />
  </KeepAlive>
</template>
```

### Virtual Scrolling for Large Lists
```vue
<script setup>
import { useVirtualList } from '@vueuse/core';

const { list, containerProps, wrapperProps } = useVirtualList(
  largeArray,
  { itemHeight: 50 }
);
</script>

<template>
  <div v-bind="containerProps" style="height: 400px; overflow: auto;">
    <div v-bind="wrapperProps">
      <div v-for="item in list" :key="item.index">
        {{ item.data }}
      </div>
    </div>
  </div>
</template>
```

## Testing

### Component Testing with Vitest
```javascript
import { describe, it, expect, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import UserCard from '@/components/UserCard.vue';

describe('UserCard', () => {
  it('renders user name', () => {
    const wrapper = mount(UserCard, {
      props: {
        user: { name: 'John', email: 'john@example.com' }
      }
    });

    expect(wrapper.text()).toContain('John');
  });

  it('emits delete event when button clicked', async () => {
    const wrapper = mount(UserCard, {
      props: { user: { id: 1, name: 'John' } }
    });

    await wrapper.find('[data-testid="delete-btn"]').trigger('click');

    expect(wrapper.emitted('delete')).toBeTruthy();
    expect(wrapper.emitted('delete')[0]).toEqual([1]);
  });
});
```

### Store Testing
```javascript
import { setActivePinia, createPinia } from 'pinia';
import { useUserStore } from '@/stores/user';

describe('User Store', () => {
  beforeEach(() => {
    setActivePinia(createPinia());
  });

  it('authenticates user on login', async () => {
    const store = useUserStore();

    await store.login({ email: 'test@example.com', password: 'password' });

    expect(store.isAuthenticated).toBe(true);
    expect(store.user).toBeDefined();
  });
});
```

## Common Patterns

### Form Validation
```javascript
// composables/useValidation.js
export function useValidation(rules) {
  const errors = ref({});

  function validate(data) {
    errors.value = {};

    for (const [field, fieldRules] of Object.entries(rules)) {
      for (const rule of fieldRules) {
        const error = rule(data[field], data);
        if (error) {
          errors.value[field] = error;
          break;
        }
      }
    }

    return Object.keys(errors.value).length === 0;
  }

  return { errors, validate };
}

// Validation rules
export const required = (value) =>
  !value ? 'This field is required' : null;

export const email = (value) =>
  value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)
    ? 'Invalid email address'
    : null;

export const minLength = (min) => (value) =>
  value && value.length < min
    ? `Must be at least ${min} characters`
    : null;
```

### Confirmation Modal
```vue
<script setup>
import { ref } from 'vue';

const isOpen = ref(false);
const resolvePromise = ref(null);

function confirm(message) {
  return new Promise((resolve) => {
    isOpen.value = true;
    resolvePromise.value = resolve;
  });
}

function handleConfirm() {
  resolvePromise.value?.(true);
  isOpen.value = false;
}

function handleCancel() {
  resolvePromise.value?.(false);
  isOpen.value = false;
}

defineExpose({ confirm });
</script>
```

### Debounced Search
```vue
<script setup>
import { ref, watch } from 'vue';
import { useDebounceFn } from '@vueuse/core';

const searchQuery = ref('');
const results = ref([]);

const debouncedSearch = useDebounceFn(async (query) => {
  if (!query) {
    results.value = [];
    return;
  }
  results.value = await api.search(query);
}, 300);

watch(searchQuery, (query) => {
  debouncedSearch(query);
});
</script>
```

## Code Style Guidelines

### File Naming
- Components: `PascalCase.vue` (e.g., `UserCard.vue`, `BaseButton.vue`)
- Composables: `camelCase.js` with `use` prefix (e.g., `useAuth.js`, `useFetch.js`)
- Stores: `camelCase.js` (e.g., `user.js`, `notifications.js`)
- Pages: `PascalCase.vue` in appropriate directory structure

### Component Structure
```vue
<script setup>
// 1. Imports
import { ref, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';

// 2. Props and emits
const props = defineProps({});
const emit = defineEmits([]);

// 3. Composables and stores
const route = useRoute();

// 4. Reactive state
const isLoading = ref(false);

// 5. Computed properties
const computedValue = computed(() => {});

// 6. Methods
function handleClick() {}

// 7. Watchers
watch(someRef, () => {});

// 8. Lifecycle hooks
onMounted(() => {});

// 9. Expose (if needed)
defineExpose({});
</script>

<template>
  <!-- Template here -->
</template>

<style scoped>
/* Styles here */
</style>
```

### ESLint Rules (Recommended)
```json
{
  "extends": [
    "eslint:recommended",
    "plugin:vue/vue3-recommended"
  ],
  "rules": {
    "vue/multi-word-component-names": "off",
    "vue/no-v-html": "warn",
    "vue/require-default-prop": "error",
    "vue/component-name-in-template-casing": ["error", "PascalCase"]
  }
}
```

## Troubleshooting

### Problem 1: Reactivity Not Updating

**Symptoms:**
- UI doesn't update when data changes
- Computed properties show stale values
- Watch callbacks not firing

**Cause:**
- Replacing reactive object instead of mutating
- Missing `.value` on refs
- Adding new properties to reactive objects without Vue.set
- Array mutations using index assignment

**Solution:**
```javascript
// ❌ BAD - Replacing reactive object
const state = reactive({ users: [] });
state = { users: newUsers }; // Won't work!

// ✅ GOOD - Mutate properties
state.users = newUsers;

// ❌ BAD - Missing .value
const count = ref(0);
count++; // Won't work!

// ✅ GOOD - Use .value
count.value++;

// ❌ BAD - Index assignment on arrays
const items = ref([1, 2, 3]);
items.value[0] = 10; // May not trigger reactivity

// ✅ GOOD - Use splice or replace array
items.value.splice(0, 1, 10);
// or
items.value = [10, ...items.value.slice(1)];
```

**Prevention:**
- Always use `.value` for refs in script
- Use `reactive` for objects, `ref` for primitives
- Use Vue DevTools to inspect reactivity

### Problem 2: Props Not Updating in Child Component

**Symptoms:**
- Child component shows initial prop value
- Changes in parent don't reflect in child
- Child's local state becomes stale

**Cause:**
- Caching prop value in local state without watching
- Using props directly in reactive without toRefs
- Destructuring props without toRefs

**Solution:**
```javascript
// ❌ BAD - Caching without watching
const props = defineProps(['user']);
const localUser = ref(props.user); // Won't update!

// ✅ GOOD - Watch for changes
const props = defineProps(['user']);
const localUser = ref(props.user);
watch(() => props.user, (newUser) => {
  localUser.value = newUser;
});

// ✅ BETTER - Use computed
const displayUser = computed(() => props.user);

// ❌ BAD - Destructuring loses reactivity
const { name, email } = defineProps(['name', 'email']);

// ✅ GOOD - Keep props object or use toRefs
const props = defineProps(['name', 'email']);
const { name, email } = toRefs(props);
```

**Prevention:**
- Never destructure props directly
- Use `computed` when deriving values from props
- Use `toRefs` when you need individual refs from props

### Problem 3: Memory Leaks with Watchers and Event Listeners

**Symptoms:**
- Application slows down over time
- Browser memory usage increases
- Console shows multiple event handlers firing

**Cause:**
- Not cleaning up event listeners
- Not stopping watchers/intervals when component unmounts
- Keeping references to DOM elements

**Solution:**
```javascript
// ✅ GOOD - Auto-cleanup with script setup
import { onUnmounted, watch } from 'vue';

// Watchers are auto-cleaned in script setup

// For event listeners
onMounted(() => {
  window.addEventListener('resize', handleResize);
});

onUnmounted(() => {
  window.removeEventListener('resize', handleResize);
});

// For intervals
const intervalId = ref(null);

onMounted(() => {
  intervalId.value = setInterval(tick, 1000);
});

onUnmounted(() => {
  clearInterval(intervalId.value);
});

// ✅ BEST - Use VueUse
import { useEventListener, useIntervalFn } from '@vueuse/core';

// Auto-cleaned when component unmounts
useEventListener(window, 'resize', handleResize);
useIntervalFn(tick, 1000);
```

**Prevention:**
- Always pair `onMounted` with `onUnmounted` for cleanup
- Use VueUse composables that handle cleanup automatically
- Use Vue DevTools to check for component leaks

### Problem 4: Inertia Form Not Resetting Errors

**Symptoms:**
- Validation errors persist after fixing input
- Form shows old errors after successful submit
- Errors from previous submission show on new form

**Cause:**
- Not calling `form.clearErrors()` or `form.reset()`
- Reusing form object across multiple submissions
- Not handling `onSuccess` callback properly

**Solution:**
```javascript
const form = useForm({
  name: '',
  email: ''
});

function submit() {
  form.post(route('users.store'), {
    onSuccess: () => {
      form.reset(); // Reset all fields and errors
    },
    onError: (errors) => {
      // Errors auto-populate form.errors
    }
  });
}

// Clear specific field error on input
function handleInput(field) {
  if (form.errors[field]) {
    form.clearErrors(field);
  }
}

// Clear all errors
function resetForm() {
  form.reset();
  form.clearErrors();
}
```

**Prevention:**
- Always handle `onSuccess` to reset form state
- Clear field errors on user input
- Create new form instances for different forms

### Problem 5: Pinia Store State Not Persisting

**Symptoms:**
- State resets on page navigation
- Data disappears on refresh
- Inertia visits clear store data

**Cause:**
- Not using persistence plugin
- Inertia full page loads
- Store state not initialized from server

**Solution:**
```javascript
// Install pinia-plugin-persistedstate
// npm install pinia-plugin-persistedstate

// main.js
import piniaPluginPersistedstate from 'pinia-plugin-persistedstate';

const pinia = createPinia();
pinia.use(piniaPluginPersistedstate);

// stores/user.js
export const useUserStore = defineStore('user', () => {
  const user = ref(null);
  // ...
  return { user };
}, {
  persist: true // Enable persistence
});

// Or selective persistence
export const useSettingsStore = defineStore('settings', () => {
  const theme = ref('light');
  const sidebarOpen = ref(true);
  return { theme, sidebarOpen };
}, {
  persist: {
    paths: ['theme'], // Only persist theme
    storage: localStorage
  }
});

// For Inertia, hydrate from shared props
// HandleInertiaRequests.php
public function share(Request $request) {
  return [
    'auth' => [
      'user' => $request->user()
    ]
  ];
}

// Component
const page = usePage();
const userStore = useUserStore();
watch(() => page.props.auth.user, (user) => {
  userStore.setUser(user);
}, { immediate: true });
```

**Prevention:**
- Use persistence plugin for client-side data
- Hydrate stores from Inertia shared props
- Consider what truly needs to persist

### Problem 6: Infinite Loop in Watchers

**Symptoms:**
- Browser freezes
- "Maximum call stack exceeded" error
- Component keeps re-rendering

**Cause:**
- Watcher modifying its own source
- Circular dependencies between watchers
- watchEffect modifying reactive dependencies

**Solution:**
```javascript
// ❌ BAD - Infinite loop
const count = ref(0);
watch(count, (newCount) => {
  count.value = newCount + 1; // Infinite loop!
});

// ✅ GOOD - Guard against recursion
const count = ref(0);
watch(count, (newCount, oldCount) => {
  if (newCount !== oldCount + 1) {
    count.value = newCount + 1;
  }
});

// ❌ BAD - watchEffect modifying dependency
const items = ref([]);
watchEffect(() => {
  items.value.push({ id: items.value.length }); // Infinite!
});

// ✅ GOOD - Use watch for mutations
watch(someOtherTrigger, () => {
  items.value.push({ id: items.value.length });
});

// ✅ GOOD - Use flush: 'post' for DOM-related effects
watchEffect(() => {
  // DOM access here
}, { flush: 'post' });
```

**Prevention:**
- Never modify watched source inside watcher
- Use guards to prevent recursive updates
- Prefer `watch` over `watchEffect` when mutating data

### Problem 7: Component Not Rendering After Route Change

**Symptoms:**
- Same component instance persists across routes
- Data from previous route shows
- onMounted not called on navigation

**Cause:**
- Vue Router reuses component instances
- Missing key on router-view
- Not watching route params

**Solution:**
```vue
<!-- ❌ BAD - Component reused -->
<router-view />

<!-- ✅ GOOD - Force re-render on route change -->
<router-view :key="$route.fullPath" />

<!-- Or in specific component -->
<script setup>
import { watch } from 'vue';
import { useRoute } from 'vue-router';

const route = useRoute();

// Watch for param changes
watch(() => route.params.id, (newId) => {
  fetchData(newId);
}, { immediate: true });
</script>
```

**Prevention:**
- Use `:key` on router-view when needed
- Watch route params in components
- Use `onBeforeRouteUpdate` guard

### Problem 8: v-model Not Working on Custom Component

**Symptoms:**
- Two-way binding doesn't update
- Parent value doesn't sync with child
- No errors in console

**Cause:**
- Missing `modelValue` prop or `update:modelValue` emit
- Incorrect emit name
- Not using computed for local state

**Solution:**
```vue
<!-- Child Component -->
<script setup>
const props = defineProps({
  modelValue: {
    type: String,
    default: ''
  }
});

const emit = defineEmits(['update:modelValue']);

// ✅ GOOD - Computed with getter/setter
const localValue = computed({
  get() {
    return props.modelValue;
  },
  set(value) {
    emit('update:modelValue', value);
  }
});
</script>

<template>
  <input v-model="localValue" />
</template>

<!-- Parent Component -->
<template>
  <CustomInput v-model="searchQuery" />
</template>
```

**Prevention:**
- Follow Vue 3 v-model convention exactly
- Use TypeScript for better type checking
- Test two-way binding early in development

### Problem 9: Computed Property Returns Promise

**Symptoms:**
- Template shows `[object Promise]`
- Data not rendering
- Console shows no errors

**Cause:**
- Using async function in computed
- Computed properties must be synchronous

**Solution:**
```javascript
// ❌ BAD - Async computed
const userData = computed(async () => {
  return await fetchUser(userId.value);
});

// ✅ GOOD - Use ref with watcher
const userData = ref(null);
const isLoading = ref(false);

watch(userId, async (id) => {
  if (!id) return;
  isLoading.value = true;
  try {
    userData.value = await fetchUser(id);
  } finally {
    isLoading.value = false;
  }
}, { immediate: true });

// ✅ BETTER - Use VueUse useAsyncState
import { useAsyncState } from '@vueuse/core';

const { state: userData, isLoading, execute } = useAsyncState(
  () => fetchUser(userId.value),
  null,
  { immediate: true }
);
```

**Prevention:**
- Never use `async` with `computed()`
- Use watch/watchEffect for async operations
- Consider VueUse for async state management

### Problem 10: Template Refs Are Null

**Symptoms:**
- `ref.value` is null in onMounted
- Cannot call methods on ref
- Ref works sometimes but not others

**Cause:**
- Accessing ref before component mounts
- Conditional rendering with v-if
- Ref target not rendered yet

**Solution:**
```vue
<script setup>
const inputRef = ref(null);

// ❌ BAD - May be null with v-if
onMounted(() => {
  inputRef.value?.focus(); // May be null!
});

// ✅ GOOD - Watch for ref availability
watch(inputRef, (el) => {
  if (el) {
    el.focus();
  }
});

// ✅ GOOD - Use nextTick after state change
async function showInput() {
  showField.value = true;
  await nextTick();
  inputRef.value?.focus();
}
</script>

<template>
  <input v-if="showField" ref="inputRef" />
</template>
```

**Prevention:**
- Always use optional chaining with refs
- Watch for ref existence with conditional rendering
- Use `nextTick` after state changes that affect rendering

### Problem 11: Provide/Inject Not Working

**Symptoms:**
- Injected value is undefined
- Default value always used
- Type errors with TypeScript

**Cause:**
- Provide not in parent component chain
- Key mismatch between provide/inject
- Providing reactive value incorrectly

**Solution:**
```javascript
// ❌ BAD - Primitive loses reactivity
provide('count', count.value);

// ✅ GOOD - Provide the ref itself
provide('count', count);

// ✅ GOOD - Use symbol keys for type safety
// keys.js
export const UserKey = Symbol('user');

// Parent
import { UserKey } from './keys';
provide(UserKey, user);

// Child
import { UserKey } from './keys';
const user = inject(UserKey);

// ✅ GOOD - With default value
const user = inject(UserKey, () => ({ name: 'Guest' }), true);
```

**Prevention:**
- Use Symbol keys for provide/inject
- Provide refs, not their values
- Always handle undefined case or provide defaults

### Problem 12: Async Component Flickers

**Symptoms:**
- Loading state appears then disappears quickly
- Component flashes on fast connections
- Poor perceived performance

**Cause:**
- No delay before showing loading state
- Component loads faster than expected

**Solution:**
```javascript
// ✅ GOOD - Add delay before showing loading
const AsyncComponent = defineAsyncComponent({
  loader: () => import('./HeavyComponent.vue'),
  loadingComponent: LoadingSpinner,
  delay: 200, // Only show loading after 200ms
  timeout: 10000,
  errorComponent: ErrorDisplay
});

// ✅ GOOD - Suspense with transition
<template>
  <Suspense>
    <template #default>
      <AsyncComponent />
    </template>
    <template #fallback>
      <Transition name="fade" mode="out-in">
        <LoadingSpinner />
      </Transition>
    </template>
  </Suspense>
</template>
```

**Prevention:**
- Always add `delay` to async components
- Use Suspense for coordinated loading
- Consider skeleton loaders over spinners

### Problem 13: Event Handlers Fire Multiple Times

**Symptoms:**
- Click handler fires twice
- API called multiple times
- Form submits repeatedly

**Cause:**
- Event bubbling
- Duplicate event listeners
- Missing debounce on rapid events

**Solution:**
```vue
<template>
  <!-- ❌ BAD - Event bubbles up -->
  <div @click="handleOuter">
    <button @click="handleInner">Click</button>
  </div>

  <!-- ✅ GOOD - Stop propagation -->
  <div @click="handleOuter">
    <button @click.stop="handleInner">Click</button>
  </div>

  <!-- ✅ GOOD - Prevent double submit -->
  <form @submit.prevent="handleSubmit">
    <button type="submit" :disabled="isSubmitting">Submit</button>
  </form>

  <!-- ✅ GOOD - Debounce rapid events -->
  <input @input="debouncedSearch" />
</template>

<script setup>
import { useDebounceFn } from '@vueuse/core';

const isSubmitting = ref(false);

async function handleSubmit() {
  if (isSubmitting.value) return;
  isSubmitting.value = true;
  try {
    await submitForm();
  } finally {
    isSubmitting.value = false;
  }
}

const debouncedSearch = useDebounceFn((event) => {
  search(event.target.value);
}, 300);
</script>
```

**Prevention:**
- Use `.stop` modifier to prevent bubbling
- Disable buttons during async operations
- Debounce rapid-fire events

### Problem 14: Store Actions Not Updating UI

**Symptoms:**
- Pinia action completes but UI doesn't update
- Have to refresh to see changes
- Other components don't react

**Cause:**
- Not returning updated data from action
- Destructuring store state loses reactivity
- Action modifies wrong state

**Solution:**
```javascript
// ❌ BAD - Destructuring loses reactivity
const { users } = useUserStore();

// ✅ GOOD - Use storeToRefs
const userStore = useUserStore();
const { users } = storeToRefs(userStore);

// ❌ BAD - Action doesn't update state correctly
async function fetchUsers() {
  const data = await api.getUsers();
  return data; // State not updated!
}

// ✅ GOOD - Action updates state
async function fetchUsers() {
  const data = await api.getUsers();
  users.value = data;
  return data;
}

// ✅ GOOD - Verify reactivity in component
watch(() => userStore.users, (newUsers) => {
  console.log('Users updated:', newUsers);
}, { deep: true });
```

**Prevention:**
- Always use `storeToRefs` for reactive state
- Ensure actions mutate state, not just return data
- Use Vue DevTools to verify store updates

### Problem 15: TypeScript Type Errors with Refs

**Symptoms:**
- Type 'null' is not assignable
- Property does not exist on type
- Ref type inference issues

**Cause:**
- Not providing initial type
- Ref initialized as null
- Missing type annotations

**Solution:**
```typescript
// ❌ BAD - Type inferred as null
const user = ref(null);
user.value.name; // Error: 'null' has no property 'name'

// ✅ GOOD - Provide type parameter
interface User {
  id: number;
  name: string;
}

const user = ref<User | null>(null);

// Access safely
user.value?.name;

// Or with type guard
if (user.value) {
  user.value.name; // TypeScript knows it's User
}

// ✅ GOOD - For arrays
const users = ref<User[]>([]);

// ✅ GOOD - For complex types
const form = ref({
  name: '',
  email: ''
} as FormData);
```

**Prevention:**
- Always type refs that start as null
- Use interfaces for complex objects
- Enable strict mode in tsconfig

### Problem 16: Teleport Not Rendering

**Symptoms:**
- Teleport content doesn't appear
- Target element not found
- Works in dev, fails in production

**Cause:**
- Target element doesn't exist yet
- Wrong selector for target
- SSR hydration mismatch

**Solution:**
```vue
<template>
  <!-- ❌ BAD - Target might not exist -->
  <Teleport to="#modal-container">
    <Modal />
  </Teleport>

  <!-- ✅ GOOD - Disable until target exists -->
  <Teleport to="#modal-container" :disabled="!targetExists">
    <Modal />
  </Teleport>

  <!-- ✅ GOOD - Create target in same app -->
  <div id="modal-container"></div>
  <Teleport to="#modal-container">
    <Modal v-if="showModal" />
  </Teleport>
</template>

<script setup>
const targetExists = ref(false);

onMounted(() => {
  targetExists.value = !!document.getElementById('modal-container');
});
</script>
```

**Prevention:**
- Ensure teleport target exists in DOM
- Use `:disabled` for conditional teleporting
- Add target element in app layout

### Problem 17: Component Styles Leaking

**Symptoms:**
- Styles affect other components
- Unexpected styling in parent/child
- Specificity conflicts

**Cause:**
- Missing `scoped` attribute
- Using `>>>` or `/deep/` incorrectly
- Global styles in component

**Solution:**
```vue
<!-- ✅ GOOD - Scoped styles -->
<style scoped>
.button {
  background: blue;
}

/* Deep selector for child components */
:deep(.child-class) {
  color: red;
}

/* Slotted content */
:slotted(p) {
  margin: 0;
}
</style>

<!-- ✅ GOOD - CSS modules for guaranteed uniqueness -->
<template>
  <button :class="$style.button">Click</button>
</template>

<style module>
.button {
  background: blue;
}
</style>
```

**Prevention:**
- Always use `scoped` or CSS modules
- Use `:deep()` instead of deprecated `>>>`
- Keep global styles in separate files

### Problem 18: Inertia Page Doesn't Receive Props

**Symptoms:**
- Props undefined in Vue component
- Data visible in network but not component
- Works on some pages, not others

**Cause:**
- Controller not returning Inertia response
- Props not passed to Inertia::render
- Middleware intercepting response

**Solution:**
```php
// ❌ BAD - Missing Inertia render
public function show(Invoice $invoice) {
    return view('invoices.show', compact('invoice'));
}

// ✅ GOOD - Using Inertia render
public function show(Invoice $invoice) {
    return Inertia::render('Invoices/Show', [
        'invoice' => $invoice,
        'company' => $invoice->company,
    ]);
}

// ✅ GOOD - With resources for controlled data
public function show(Invoice $invoice) {
    return Inertia::render('Invoices/Show', [
        'invoice' => new InvoiceResource($invoice->load(['items', 'company'])),
    ]);
}
```

```vue
<script setup>
// Verify props are defined correctly
const props = defineProps({
  invoice: {
    type: Object,
    required: true
  },
  company: Object
});

// Debug: Check what's received
console.log('Props:', props);
</script>
```

**Prevention:**
- Always use `Inertia::render()` for Inertia pages
- Check Laravel controller returns Inertia response
- Use Vue DevTools to inspect received props

## Integration Guides

### Integration with Laravel Inertia

**When to integrate:**
- Building SPAs with Laravel backend
- Need server-side routing with SPA experience
- Want to avoid building separate API

**Setup:**
```bash
# Install Inertia client
npm install @inertiajs/vue3

# Configure in main.js
```

```javascript
// resources/js/app.js
import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from 'ziggy-js';

createInertiaApp({
  title: (title) => `${title} - My App`,
  resolve: (name) => resolvePageComponent(
    `./Pages/${name}.vue`,
    import.meta.glob('./Pages/**/*.vue')
  ),
  setup({ el, App, props, plugin }) {
    return createApp({ render: () => h(App, props) })
      .use(plugin)
      .use(ZiggyVue)
      .mount(el);
  },
  progress: {
    color: '#4B5563'
  }
});
```

**Example workflow:**
```php
// Laravel Controller
public function index() {
  return Inertia::render('Users/Index', [
    'users' => User::paginate(10),
    'filters' => request()->only('search', 'status')
  ]);
}
```

```vue
<!-- resources/js/Pages/Users/Index.vue -->
<script setup>
defineProps({
  users: Object,
  filters: Object
});
</script>
```

### Integration with Pinia

**When to integrate:**
- Need global state management
- Sharing state between unrelated components
- Complex state logic requiring actions/getters

**Setup:**
```javascript
// stores/index.js
import { createPinia } from 'pinia';
import piniaPluginPersistedstate from 'pinia-plugin-persistedstate';

const pinia = createPinia();
pinia.use(piniaPluginPersistedstate);

export default pinia;

// main.js
import pinia from './stores';

app.use(pinia);
```

**Store pattern for this project:**
```javascript
// stores/company.js
import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

export const useCompanyStore = defineStore('company', () => {
  const currentCompany = ref(null);
  const companies = ref([]);

  const companyId = computed(() => currentCompany.value?.id);

  async function switchCompany(company) {
    currentCompany.value = company;
    // Reload Inertia page with new company context
    router.reload({ only: ['company'] });
  }

  return {
    currentCompany,
    companies,
    companyId,
    switchCompany
  };
}, {
  persist: {
    paths: ['currentCompany']
  }
});
```

### Integration with VueUse

**When to integrate:**
- Need common utility composables
- Browser API abstractions
- State and DOM utilities

**Installation:**
```bash
npm install @vueuse/core
```

**Most useful composables:**
```javascript
import {
  // State
  useStorage,           // localStorage/sessionStorage reactive
  useRefHistory,        // Undo/redo for refs
  useDebouncedRef,      // Debounced ref

  // Browser
  useClipboard,         // Copy to clipboard
  useEventListener,     // Auto-cleanup event listeners
  useMediaQuery,        // Responsive breakpoints
  useDark,              // Dark mode toggle

  // Sensors
  useMousePosition,
  useScroll,
  useIntersectionObserver,

  // Utilities
  useDebounceFn,
  useThrottleFn,
  useAsyncState
} from '@vueuse/core';

// Example: Dark mode
const isDark = useDark();
const toggleDark = useToggle(isDark);

// Example: Clipboard
const { copy, copied } = useClipboard();
await copy('Text to copy');

// Example: Debounced search
const search = ref('');
const debouncedSearch = useDebouncedRef(search, 300);

// Example: Async data fetching
const { state, isLoading, error } = useAsyncState(
  fetchUsers(),
  [] // initial state
);
```

### Integration with Vue DevTools

**Setup:**
```bash
# Browser extension - install from Chrome/Firefox store

# Or standalone app
npm install -g @vue/devtools
vue-devtools
```

**Configuration:**
```javascript
// vite.config.js
export default defineConfig({
  plugins: [vue()],
  define: {
    __VUE_PROD_DEVTOOLS__: true // Enable in production (optional)
  }
});
```

**Debugging tips:**
1. Component Inspector - View component hierarchy and props
2. Pinia Tab - Inspect and edit store state
3. Timeline - Track events, mutations, and performance
4. Routes Tab - Debug route transitions

## Checklists

### Pre-Implementation Checklist

Before starting a new Vue feature:

- [ ] Identify which components need to be created/modified
- [ ] Plan state management (local vs Pinia store)
- [ ] Review existing composables for reusable logic
- [ ] Check if Inertia props provide necessary data
- [ ] Plan component communication (props/emits/provide-inject)
- [ ] Consider accessibility requirements
- [ ] Review design for responsive breakpoints
- [ ] Identify form validation requirements

### Component Implementation Checklist

When building a Vue component:

- [ ] Use `<script setup>` syntax
- [ ] Define props with proper types and defaults
- [ ] Define emits with validation if needed
- [ ] Use `ref` for primitives, `reactive` for objects
- [ ] Extract computed properties for derived state
- [ ] Add proper error handling for async operations
- [ ] Implement loading states for async data
- [ ] Clean up side effects in `onUnmounted`
- [ ] Add TypeScript types (if using TS)
- [ ] Keep template readable (extract complex logic)
- [ ] Use meaningful component and variable names
- [ ] Add data-testid attributes for testing

### Form Implementation Checklist

When building forms with Inertia:

- [ ] Use `useForm` from `@inertiajs/vue3`
- [ ] Define all form fields with default values
- [ ] Implement field-level error display
- [ ] Add form-level error summary if needed
- [ ] Handle `form.processing` state (disable submit)
- [ ] Implement `onSuccess` callback
- [ ] Implement `onError` callback if custom handling needed
- [ ] Add field-level validation feedback
- [ ] Consider `preserveScroll: true` for better UX
- [ ] Reset form state appropriately
- [ ] Test form submission flow

### Post-Implementation Checklist

After implementing a feature:

- [ ] Component renders without console errors
- [ ] Reactivity works as expected (use DevTools)
- [ ] Forms submit and handle errors correctly
- [ ] Navigation/routing works properly
- [ ] Loading states display appropriately
- [ ] Error states are handled gracefully
- [ ] Component is responsive on all breakpoints
- [ ] Accessibility: keyboard navigation works
- [ ] No memory leaks (check DevTools)
- [ ] Code follows project style guidelines
- [ ] Tests pass (unit and integration)
- [ ] No TypeScript errors (if applicable)

## Common Mistakes & Anti-Patterns

### Mistake 1: Options API in Vue 3

**The Problem:**
```vue
<script>
export default {
  data() {
    return { count: 0 }
  },
  methods: {
    increment() {
      this.count++;
    }
  }
}
</script>
```

**Why It's Wrong:**
- Harder to extract and reuse logic
- `this` context issues
- Less tree-shakable
- Worse TypeScript support

**The Fix:**
```vue
<script setup>
import { ref } from 'vue';

const count = ref(0);
const increment = () => count.value++;
</script>
```

**Impact:**
- Larger bundle size
- Inconsistent codebase
- Harder maintenance

### Mistake 2: Not Using Computed for Derived State

**The Problem:**
```vue
<script setup>
const items = ref([]);
const itemCount = ref(0);

watch(items, () => {
  itemCount.value = items.value.length;
}, { deep: true });
</script>
```

**Why It's Wrong:**
- Unnecessary watcher
- Manual synchronization
- Race conditions possible

**The Fix:**
```vue
<script setup>
const items = ref([]);
const itemCount = computed(() => items.value.length);
</script>
```

**Impact:**
- Performance degradation
- Potential stale values
- More code to maintain

### Mistake 3: Mutating Props

**The Problem:**
```vue
<script setup>
const props = defineProps(['user']);

function updateName(name) {
  props.user.name = name; // Mutating prop!
}
</script>
```

**Why It's Wrong:**
- Violates one-way data flow
- Makes debugging difficult
- Can cause unexpected side effects

**The Fix:**
```vue
<script setup>
const props = defineProps(['user']);
const emit = defineEmits(['update:user']);

function updateName(name) {
  emit('update:user', { ...props.user, name });
}
</script>
```

**Impact:**
- Unpredictable component behavior
- Hard to track state changes
- Parent-child desynchronization

### Mistake 4: Async Operations in Computed

**The Problem:**
```vue
<script setup>
const user = computed(async () => {
  return await fetchUser(userId.value);
});
</script>
```

**Why It's Wrong:**
- Computed properties must be synchronous
- Returns a Promise, not the resolved value
- Can't properly cache async results

**The Fix:**
```vue
<script setup>
const user = ref(null);
const isLoading = ref(false);

watch(userId, async (id) => {
  isLoading.value = true;
  user.value = await fetchUser(id);
  isLoading.value = false;
}, { immediate: true });

// Or use VueUse
const { state: user, isLoading } = useAsyncState(
  () => fetchUser(userId.value),
  null
);
</script>
```

**Impact:**
- Unexpected Promise objects in templates
- Missing loading states
- Memory leaks

### Mistake 5: Deep Watchers Without Need

**The Problem:**
```vue
<script setup>
const config = reactive({ theme: 'light', locale: 'en' });

watch(config, (newConfig) => {
  console.log('Config changed');
}, { deep: true }); // Deep watching everything
</script>
```

**Why It's Wrong:**
- Performance overhead
- Triggers on any nested change
- Often unnecessary

**The Fix:**
```vue
<script setup>
const config = reactive({ theme: 'light', locale: 'en' });

// Watch specific property
watch(() => config.theme, (newTheme) => {
  console.log('Theme changed to', newTheme);
});

// Or watch multiple specific properties
watch(
  [() => config.theme, () => config.locale],
  ([theme, locale]) => {
    console.log('Theme or locale changed');
  }
);
</script>
```

**Impact:**
- Unnecessary re-computations
- Harder to debug which change triggered watcher
- Performance degradation

### Mistake 6: Not Handling Async Errors

**The Problem:**
```vue
<script setup>
async function loadData() {
  const data = await fetchData(); // Unhandled rejection if fails
  items.value = data;
}

onMounted(loadData);
</script>
```

**Why It's Wrong:**
- Silent failures
- No error UI for users
- Difficult debugging

**The Fix:**
```vue
<script setup>
const items = ref([]);
const error = ref(null);
const isLoading = ref(false);

async function loadData() {
  isLoading.value = true;
  error.value = null;

  try {
    items.value = await fetchData();
  } catch (e) {
    error.value = e.message || 'Failed to load data';
    console.error('Load data error:', e);
  } finally {
    isLoading.value = false;
  }
}

onMounted(loadData);
</script>

<template>
  <div v-if="isLoading">Loading...</div>
  <div v-else-if="error" class="error">{{ error }}</div>
  <div v-else><!-- Show items --></div>
</template>
```

**Impact:**
- Poor user experience
- Unhandled promise rejections
- Data inconsistency

### Mistake 7: Using Index as Key in v-for

**The Problem:**
```vue
<template>
  <div v-for="(item, index) in items" :key="index">
    {{ item.name }}
  </div>
</template>
```

**Why It's Wrong:**
- Breaks reactivity when list is reordered
- Input state leaks between items
- Animation issues

**The Fix:**
```vue
<template>
  <div v-for="item in items" :key="item.id">
    {{ item.name }}
  </div>
</template>
```

**Impact:**
- Buggy list behavior
- Form input issues
- Performance problems with list updates

### Mistake 8: Global Event Bus Pattern

**The Problem:**
```javascript
// eventBus.js
import mitt from 'mitt';
export const bus = mitt();

// ComponentA.vue
bus.emit('user-updated', user);

// ComponentB.vue
bus.on('user-updated', handleUpdate);
```

**Why It's Wrong:**
- Hard to trace event flow
- Memory leaks if not cleaned up
- Vue 3 removed built-in event bus for a reason

**The Fix:**
```javascript
// Use Pinia for shared state
// stores/user.js
export const useUserStore = defineStore('user', () => {
  const user = ref(null);
  function updateUser(newUser) {
    user.value = newUser;
  }
  return { user, updateUser };
});

// ComponentA.vue
const userStore = useUserStore();
userStore.updateUser(newUser);

// ComponentB.vue
const userStore = useUserStore();
// Automatically reactive
watch(() => userStore.user, handleUpdate);
```

**Impact:**
- Spaghetti code
- Debugging nightmares
- Memory leaks

### Mistake 9: Blocking UI with Synchronous Operations

**The Problem:**
```javascript
// Heavy computation in render
const processedData = computed(() => {
  return hugeArray.value.map(item => {
    // Heavy processing for each of 10,000 items
    return expensiveTransform(item);
  });
});
```

**Why It's Wrong:**
- Blocks the main thread
- UI becomes unresponsive
- Poor user experience

**The Fix:**
```javascript
// Use web worker for heavy computation
import { useWebWorkerFn } from '@vueuse/core';

const { workerFn } = useWebWorkerFn((data) => {
  return data.map(item => expensiveTransform(item));
});

const processedData = ref([]);
watch(hugeArray, async (data) => {
  processedData.value = await workerFn(data);
});

// Or use chunked processing
async function processInChunks(items, chunkSize = 100) {
  const results = [];
  for (let i = 0; i < items.length; i += chunkSize) {
    const chunk = items.slice(i, i + chunkSize);
    results.push(...chunk.map(expensiveTransform));
    // Yield to browser
    await new Promise(r => setTimeout(r, 0));
  }
  return results;
}
```

**Impact:**
- Frozen UI
- Browser "not responding" warnings
- Terrible user experience

### Mistake 10: Not Handling Loading States

**The Problem:**
```vue
<script setup>
const users = ref([]);

onMounted(async () => {
  users.value = await fetchUsers();
});
</script>

<template>
  <!-- Shows empty state during load -->
  <div v-for="user in users" :key="user.id">
    {{ user.name }}
  </div>
</template>
```

**Why It's Wrong:**
- User sees empty state while loading
- No indication that data is being fetched
- Confusing user experience

**The Fix:**
```vue
<script setup>
const users = ref([]);
const isLoading = ref(true);
const error = ref(null);

onMounted(async () => {
  try {
    users.value = await fetchUsers();
  } catch (e) {
    error.value = e.message;
  } finally {
    isLoading.value = false;
  }
});
</script>

<template>
  <!-- Loading state -->
  <div v-if="isLoading" class="animate-pulse space-y-4">
    <div class="h-10 bg-gray-200 rounded"></div>
    <div class="h-10 bg-gray-200 rounded"></div>
  </div>

  <!-- Error state -->
  <div v-else-if="error" class="text-red-600">
    {{ error }}
  </div>

  <!-- Empty state -->
  <div v-else-if="users.length === 0">
    No users found.
  </div>

  <!-- Data state -->
  <div v-else v-for="user in users" :key="user.id">
    {{ user.name }}
  </div>
</template>
```

**Impact:**
- Poor user feedback
- Confusion about app state
- Unprofessional appearance

### Mistake 11: Overloading Components

**The Problem:**
```vue
<!-- 500+ line component doing everything -->
<script setup>
// User management
const users = ref([]);
const selectedUser = ref(null);
// ... 50 lines of user logic

// Invoice management
const invoices = ref([]);
const invoiceForm = useForm({...});
// ... 50 lines of invoice logic

// Report generation
const reportData = ref({});
// ... 50 lines of report logic

// And more...
</script>
```

**Why It's Wrong:**
- Hard to understand and maintain
- Can't reuse logic
- Testing becomes difficult
- Merge conflicts in team

**The Fix:**
```javascript
// composables/useUsers.js
export function useUsers() {
  const users = ref([]);
  const selectedUser = ref(null);
  // All user logic here
  return { users, selectedUser, /* ... */ };
}

// composables/useInvoices.js
export function useInvoices() {
  // All invoice logic here
}

// Component becomes clean
<script setup>
import { useUsers } from '@/composables/useUsers';
import { useInvoices } from '@/composables/useInvoices';

const { users, selectedUser } = useUsers();
const { invoices, createInvoice } = useInvoices();
</script>
```

**Impact:**
- Unmaintainable codebase
- Duplicate logic
- Difficult onboarding

### Mistake 12: Direct DOM Manipulation

**The Problem:**
```javascript
// Directly manipulating DOM in Vue
function showModal() {
  document.getElementById('modal').style.display = 'block';
  document.body.classList.add('overflow-hidden');
}

function hideModal() {
  document.getElementById('modal').style.display = 'none';
  document.body.classList.remove('overflow-hidden');
}
```

**Why It's Wrong:**
- Bypasses Vue's reactivity
- Can cause inconsistent state
- Harder to test
- May conflict with Vue's rendering

**The Fix:**
```vue
<script setup>
const isModalOpen = ref(false);

// Use VueUse for body class
import { useScrollLock } from '@vueuse/core';

const isLocked = useScrollLock(document.body);

watch(isModalOpen, (open) => {
  isLocked.value = open;
});
</script>

<template>
  <Teleport to="body">
    <div v-if="isModalOpen" class="modal">
      <!-- Modal content -->
    </div>
  </Teleport>
</template>
```

**Impact:**
- State synchronization issues
- Bugs that are hard to track
- Testing difficulties

### Mistake 13: Ignoring Unsubscription

**The Problem:**
```javascript
onMounted(() => {
  // External subscription without cleanup
  websocket.on('message', handleMessage);
  eventSource.addEventListener('update', handleUpdate);

  // Store subscription without cleanup
  someStore.$subscribe(handleStoreChange);
});
```

**Why It's Wrong:**
- Memory leaks
- Callbacks run after unmount
- Multiple subscriptions on remount

**The Fix:**
```javascript
import { onUnmounted } from 'vue';

onMounted(() => {
  websocket.on('message', handleMessage);
  eventSource.addEventListener('update', handleUpdate);
  const unsubscribe = someStore.$subscribe(handleStoreChange);

  onUnmounted(() => {
    websocket.off('message', handleMessage);
    eventSource.removeEventListener('update', handleUpdate);
    unsubscribe();
  });
});

// Or use VueUse utilities that auto-cleanup
import { useEventSource, useWebSocket } from '@vueuse/core';

const { data } = useEventSource('/api/events');
const { data: wsData } = useWebSocket('wss://api.example.com');
```

**Impact:**
- Memory leaks
- Zombie callbacks
- Performance degradation

### Mistake 14: Not Using Key Properly in Lists

**The Problem:**
```vue
<!-- Using non-unique or changing keys -->
<template>
  <div v-for="(item, index) in items" :key="index">
    <input v-model="item.name" />
  </div>

  <div v-for="item in items" :key="item.name">
    <!-- Name might not be unique -->
  </div>
</template>
```

**Why It's Wrong:**
- Index keys break when list reorders
- Non-unique keys cause rendering issues
- Input state can transfer between items

**The Fix:**
```vue
<template>
  <!-- Always use unique, stable identifiers -->
  <div v-for="item in items" :key="item.id">
    <input v-model="item.name" />
  </div>

  <!-- If no ID exists, create one -->
  <script setup>
  const itemsWithIds = computed(() =>
    items.value.map((item, i) => ({
      ...item,
      _uid: item.id || `temp-${i}-${item.name}`
    }))
  );
  </script>

  <div v-for="item in itemsWithIds" :key="item._uid">
    {{ item.name }}
  </div>
</template>
```

**Impact:**
- Buggy list behavior
- Lost form state
- Incorrect animations

### Mistake 15: Mixing Composition and Options API

**The Problem:**
```vue
<script>
export default {
  data() {
    return { count: 0 }
  },
  setup() {
    const name = ref('');
    return { name };
  },
  methods: {
    increment() {
      this.count++;
      // Can't easily access name here
    }
  }
}
</script>
```

**Why It's Wrong:**
- Confusing code organization
- Hard to reason about
- Inconsistent patterns
- Team confusion

**The Fix:**
```vue
<!-- Stick to one pattern - prefer Composition API -->
<script setup>
const count = ref(0);
const name = ref('');

function increment() {
  count.value++;
  // Easy access to all reactive state
}
</script>
```

**Impact:**
- Maintenance burden
- Code inconsistency
- Learning curve issues

### Mistake 16: Excessive Re-renders

**The Problem:**
```vue
<script setup>
// Computed that returns new object every time
const userConfig = computed(() => ({
  name: user.value.name,
  settings: { ...user.value.settings }
}));

// Watch triggers on every render
watch(userConfig, () => {
  // This runs constantly!
});
</script>
```

**Why It's Wrong:**
- Unnecessary re-renders
- Wasted CPU cycles
- Watch runs too often

**The Fix:**
```javascript
// Use shallowRef for objects that don't need deep reactivity
const config = shallowRef({ /* large object */ });

// Watch specific properties
watch(() => user.value.name, (newName) => {
  // Only runs when name changes
});

// Use v-memo for list optimization
<template>
  <div v-for="item in items" :key="item.id" v-memo="[item.id, item.selected]">
    <ExpensiveChild :item="item" />
  </div>
</template>
```

**Impact:**
- Poor performance
- Battery drain on mobile
- Laggy UI

## Security Considerations

### Security Risk 1: XSS via v-html

**Vulnerability:**
Using `v-html` with user-provided content allows script injection.

**Attack Vector:**
```javascript
// User input stored in database
const userBio = '<img src=x onerror="alert(document.cookie)">';
```
```vue
<!-- Renders malicious script -->
<div v-html="userBio"></div>
```

**Mitigation:**
```vue
<script setup>
import DOMPurify from 'dompurify';

const sanitizedBio = computed(() =>
  DOMPurify.sanitize(userBio.value)
);
</script>

<template>
  <div v-html="sanitizedBio"></div>
</template>
```

**Validation:**
- Install DOMPurify: `npm install dompurify`
- Always sanitize before using v-html
- Consider using markdown parser with sanitization

### Security Risk 2: Exposing Sensitive Data in Client State

**Vulnerability:**
Storing sensitive data in Pinia/localStorage exposes it to XSS attacks.

**Attack Vector:**
Any XSS vulnerability can access `localStorage` and Pinia state.

**Mitigation:**
```javascript
// ❌ BAD - Storing sensitive tokens in localStorage
localStorage.setItem('apiKey', 'secret-api-key');

// ✅ GOOD - Use HTTP-only cookies for sensitive tokens
// Set from server with HttpOnly, Secure, SameSite flags

// For Inertia, sensitive data should stay server-side
// Only pass what's needed for UI

// stores/auth.js
export const useAuthStore = defineStore('auth', () => {
  // Store only non-sensitive user info
  const user = ref(null); // { id, name, email, roles }
  // Never store: password, api keys, tokens

  return { user };
});
```

**Validation:**
- Audit localStorage for sensitive data
- Use HTTP-only cookies for auth tokens
- Review shared Inertia props for sensitive data

### Security Risk 3: Insecure Direct Object References

**Vulnerability:**
Exposing internal IDs allows users to access others' resources.

**Attack Vector:**
```vue
<!-- User can change ID in URL/request -->
<Link :href="route('invoices.show', invoice.id)">
```

**Mitigation:**
```php
// Always validate on server (Laravel)
public function show(Invoice $invoice) {
  // Using policy
  $this->authorize('view', $invoice);

  // Or explicit check
  if ($invoice->company_id !== auth()->user()->current_company_id) {
    abort(403);
  }

  return Inertia::render('Invoices/Show', [
    'invoice' => $invoice
  ]);
}
```

```javascript
// Frontend should never trust IDs alone
// Always rely on server authorization
```

**Validation:**
- All routes have proper authorization
- Company scoping enforced on all queries
- Policies defined for all models

### Security Risk 4: CSRF in Forms

**Vulnerability:**
Inertia handles CSRF automatically, but manual fetch calls may not.

**Attack Vector:**
```javascript
// ❌ BAD - Manual fetch without CSRF
fetch('/api/delete-user', {
  method: 'POST',
  body: JSON.stringify({ userId: 123 })
});
```

**Mitigation:**
```javascript
// ✅ GOOD - Use Inertia's router for all requests
import { router } from '@inertiajs/vue3';

router.delete(route('users.destroy', userId));

// If manual fetch is needed, include CSRF token
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

fetch('/api/endpoint', {
  method: 'POST',
  headers: {
    'X-CSRF-TOKEN': csrfToken,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify(data)
});
```

**Validation:**
- Audit all fetch/axios calls
- Ensure CSRF token is included
- Prefer Inertia router for requests

### Security Risk 5: Leaking Sensitive Props

**Vulnerability:**
Inertia passes all props to client JavaScript.

**Attack Vector:**
```php
// ❌ BAD - Passes all user data including sensitive fields
return Inertia::render('Users/Show', [
  'user' => User::find($id) // Includes password hash!
]);
```

**Mitigation:**
```php
// ✅ GOOD - Use API Resources to control exposed data
return Inertia::render('Users/Show', [
  'user' => new UserResource(User::find($id))
]);

// UserResource.php
class UserResource extends JsonResource {
  public function toArray($request) {
    return [
      'id' => $this->id,
      'name' => $this->name,
      'email' => $this->email,
      // Never include: password, remember_token, api_keys
    ];
  }
}
```

**Validation:**
- Review all Inertia::render calls
- Use API Resources for all model data
- Check browser DevTools for leaked data

## Configuration Reference

### Vite Configuration
```javascript
// vite.config.js
import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import path from 'path';

export default defineConfig({
  plugins: [
    laravel({
      input: ['resources/js/app.js'],
      refresh: true
    }),
    vue({
      template: {
        transformAssetUrls: {
          base: null,
          includeAbsolute: false
        }
      }
    })
  ],
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './resources/js'),
      '~': path.resolve(__dirname, './resources')
    }
  },
  build: {
    rollupOptions: {
      output: {
        manualChunks: {
          vendor: ['vue', 'pinia', '@inertiajs/vue3'],
          utils: ['@vueuse/core', 'lodash-es']
        }
      }
    }
  }
});
```

### ESLint Configuration
```javascript
// .eslintrc.cjs
module.exports = {
  root: true,
  env: {
    browser: true,
    es2022: true,
    node: true
  },
  extends: [
    'eslint:recommended',
    'plugin:vue/vue3-recommended'
  ],
  parserOptions: {
    ecmaVersion: 'latest',
    sourceType: 'module'
  },
  rules: {
    'vue/multi-word-component-names': 'off',
    'vue/no-v-html': 'warn',
    'vue/require-default-prop': 'error',
    'vue/component-name-in-template-casing': ['error', 'PascalCase'],
    'vue/html-indent': ['error', 2],
    'vue/max-attributes-per-line': ['error', {
      singleline: 3,
      multiline: 1
    }]
  }
};
```

### TypeScript Configuration (Optional)
```json
// tsconfig.json
{
  "compilerOptions": {
    "target": "ESNext",
    "module": "ESNext",
    "moduleResolution": "bundler",
    "strict": true,
    "jsx": "preserve",
    "resolveJsonModule": true,
    "isolatedModules": true,
    "esModuleInterop": true,
    "lib": ["ESNext", "DOM"],
    "skipLibCheck": true,
    "noEmit": true,
    "paths": {
      "@/*": ["./resources/js/*"]
    }
  },
  "include": ["resources/js/**/*.ts", "resources/js/**/*.d.ts", "resources/js/**/*.vue"],
  "references": [{ "path": "./tsconfig.node.json" }]
}
```

### Environment Variables
| Variable | Required | Default | Description |
|----------|----------|---------|-------------|
| VITE_APP_NAME | No | Laravel | Application name shown in title |
| VITE_APP_URL | No | - | API base URL for external services |
| VITE_PUSHER_APP_KEY | No | - | Pusher key for real-time features |
| VITE_PUSHER_APP_CLUSTER | No | - | Pusher cluster region |

## Tools & Commands Quick Reference

```bash
# Development
npm run dev              # Start Vite dev server with HMR
npm run build            # Build for production
npm run preview          # Preview production build

# Linting & Formatting
npm run lint             # Run ESLint
npm run lint:fix         # Fix ESLint issues
npm run format           # Run Prettier

# Testing
npm run test             # Run Vitest tests
npm run test:watch       # Run tests in watch mode
npm run test:coverage    # Run tests with coverage

# Type Checking (if TypeScript)
npm run typecheck        # Run vue-tsc type checking

# Debugging
vue-devtools             # Open standalone Vue DevTools
```

## Resources & Documentation

### Official Documentation
- [Vue.js 3 Guide](https://vuejs.org/guide/) - Core Vue concepts and API
- [Vue.js API Reference](https://vuejs.org/api/) - Complete API reference
- [Pinia Documentation](https://pinia.vuejs.org/) - State management
- [Vue Router Documentation](https://router.vuejs.org/) - Routing
- [Inertia.js Documentation](https://inertiajs.com/) - Laravel + Vue integration
- [VueUse Documentation](https://vueuse.org/) - Utility composables

### Related Skills
- `css-tailwind-expert` - Styling Vue components with Tailwind
- `webdesign` - Laravel/Inertia web design patterns
- `flutter-dart-expert` - Mobile app integration patterns

### External Tools
- [Vue DevTools](https://devtools.vuejs.org/) - Browser extension for debugging
- [Volar](https://marketplace.visualstudio.com/items?itemName=Vue.volar) - VS Code extension
- [Vitest](https://vitest.dev/) - Unit testing framework
- [Ziggy](https://github.com/tighten/ziggy) - Laravel routes in JavaScript

### Community Resources
- [Vue Discord](https://discord.com/invite/vue) - Official community
- [Vue Forum](https://forum.vuejs.org/) - Q&A forum
- [Stack Overflow - vue.js](https://stackoverflow.com/questions/tagged/vue.js) - Technical Q&A

## JavaScript Libraries and Frameworks Ecosystem

This section provides comprehensive coverage of the JavaScript ecosystem, helping you choose the right libraries and understand the landscape of available tools.

### Library Selection Philosophy

When choosing JavaScript libraries:

1. **Evaluate actively maintained projects** - Check GitHub stars, recent commits, and community activity
2. **Consider bundle size** - Use bundlephobia.com to analyze package sizes
3. **Check TypeScript support** - First-class TypeScript support indicates quality
4. **Review documentation quality** - Good docs = easier adoption
5. **Assess learning curve vs. power** - Match library complexity to project needs
6. **Consider Vue/Inertia compatibility** - Some libraries are framework-specific

### Frontend Frameworks

#### Vue.js (Current Project Framework)
```javascript
// Vue 3 with Composition API - Our primary framework
// Market share: ~18% (3rd after React and Angular)
// Bundle size: ~33kB gzipped (with compiler)

// Example: Component with script setup
<script setup>
import { ref, computed, onMounted } from 'vue';

const count = ref(0);
const doubled = computed(() => count.value * 2);

onMounted(() => {
  console.log('Component mounted');
});
</script>
```

**When to choose Vue:**
- Building SPAs with Laravel (excellent Inertia.js integration)
- Progressive enhancement (can add Vue to existing sites)
- Template-based development preference
- Smaller team wanting gentle learning curve

#### React
```javascript
// React 18+ with hooks
// Market share: ~40% (largest framework)
// Bundle size: ~40kB gzipped (react + react-dom)

// Example: Functional component with hooks
import { useState, useEffect, useMemo } from 'react';

function Counter() {
  const [count, setCount] = useState(0);
  const doubled = useMemo(() => count * 2, [count]);

  useEffect(() => {
    console.log('Component mounted/updated');
  }, [count]);

  return (
    <button onClick={() => setCount(c => c + 1)}>
      Count: {count}, Doubled: {doubled}
    </button>
  );
}
```

**When to consider React over Vue:**
- Large enterprise teams with React experience
- Need specific React-only libraries
- Job market considerations (more React jobs)
- React Native mobile development planned

#### Angular
```typescript
// Angular 17+ with signals and standalone components
// Market share: ~22% (enterprise focus)
// Bundle size: ~90kB+ gzipped (larger, but includes more features)

// Example: Standalone component
import { Component, signal, computed } from '@angular/core';

@Component({
  selector: 'app-counter',
  standalone: true,
  template: `
    <button (click)="increment()">
      Count: {{ count() }}, Doubled: {{ doubled() }}
    </button>
  `
})
export class CounterComponent {
  count = signal(0);
  doubled = computed(() => this.count() * 2);

  increment() {
    this.count.update(c => c + 1);
  }
}
```

**When to consider Angular:**
- Large enterprise applications with strict structure needs
- Team familiar with TypeScript and decorators
- Need built-in solutions (routing, forms, HTTP, etc.)
- Long-term corporate support important

#### Svelte
```javascript
// Svelte 4/5 - Compiler-based, no virtual DOM
// Market share: ~12% (growing rapidly)
// Bundle size: ~2kB gzipped (runtime only, compiles away)

// Example: Svelte component
<script>
  let count = 0;
  $: doubled = count * 2;

  function increment() {
    count++;
  }
</script>

<button on:click={increment}>
  Count: {count}, Doubled: {doubled}
</button>
```

**When to consider Svelte:**
- Performance-critical applications
- Smaller bundle size requirements
- Simpler syntax preference
- SvelteKit for full-stack development

#### Solid.js
```javascript
// Solid.js - React-like syntax, no virtual DOM
// Growing alternative with excellent performance
// Bundle size: ~7kB gzipped

import { createSignal, createMemo } from 'solid-js';

function Counter() {
  const [count, setCount] = createSignal(0);
  const doubled = createMemo(() => count() * 2);

  return (
    <button onClick={() => setCount(c => c + 1)}>
      Count: {count()}, Doubled: {doubled()}
    </button>
  );
}
```

**When to consider Solid.js:**
- Need React-like DX with better performance
- Fine-grained reactivity important
- Smaller runtime footprint needed

#### Preact
```javascript
// Preact - 3kB alternative to React
// Drop-in React replacement for most cases
// Bundle size: ~3kB gzipped

import { h } from 'preact';
import { useState } from 'preact/hooks';

function Counter() {
  const [count, setCount] = useState(0);

  return (
    <button onClick={() => setCount(c => c + 1)}>
      Count: {count}
    </button>
  );
}
```

#### Framework Comparison Matrix

| Framework | Bundle Size | Learning Curve | TypeScript | SSR | Mobile |
|-----------|-------------|----------------|------------|-----|--------|
| Vue 3 | ~33kB | Low-Medium | ⭐⭐⭐⭐ | Nuxt | Capacitor |
| React | ~40kB | Medium | ⭐⭐⭐⭐⭐ | Next.js | React Native |
| Angular | ~90kB+ | High | ⭐⭐⭐⭐⭐ | Universal | Ionic/NativeScript |
| Svelte | ~2kB | Low | ⭐⭐⭐⭐ | SvelteKit | Capacitor |
| Solid.js | ~7kB | Medium | ⭐⭐⭐⭐ | SolidStart | - |
| Preact | ~3kB | Low | ⭐⭐⭐ | preact-render-to-string | - |

### Inertia.js (Current Project - Server-Side Routing)

```javascript
// Inertia.js - The Modern Monolith
// Connects server-side routing with client-side SPA
// Used in this project with Laravel + Vue 3

// Installation (already in project)
// npm install @inertiajs/vue3

// Basic page component
<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3';

// Access page props directly (passed from Laravel controller)
const props = defineProps({
  users: Array,
  filters: Object,
});

// Form handling with Inertia
const form = useForm({
  name: '',
  email: '',
});

const submit = () => {
  form.post('/users', {
    preserveScroll: true,
    onSuccess: () => form.reset(),
  });
};

// Programmatic navigation
function visitUser(id) {
  router.visit(`/users/${id}`, {
    method: 'get',
    data: { tab: 'profile' },
    preserveState: true,
  });
}
</script>

<template>
  <Head title="Users" />

  <form @submit.prevent="submit">
    <input v-model="form.name" />
    <span v-if="form.errors.name">{{ form.errors.name }}</span>
    <button type="submit" :disabled="form.processing">
      Save
    </button>
  </form>

  <Link :href="`/users/${user.id}`" class="text-blue-500">
    View User
  </Link>
</template>
```

**Inertia Best Practices:**

```javascript
// 1. Shared data via HandleInertiaRequests middleware
// Access via usePage()
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const user = computed(() => page.props.auth.user);
const flash = computed(() => page.props.flash);

// 2. Partial reloads for performance
router.reload({ only: ['users'] });

// 3. Lazy loading props
// In Laravel controller:
// return Inertia::render('Users/Index', [
//     'users' => fn() => User::paginate(),
// ]);

// 4. Progress indicator customization
import { router } from '@inertiajs/vue3';
import NProgress from 'nprogress';

router.on('start', () => NProgress.start());
router.on('finish', () => NProgress.done());
```

### DOM Manipulation Libraries

#### jQuery
```javascript
// jQuery - Legacy but still widely used
// Weekly downloads: ~8 million
// Bundle size: ~87kB minified, ~30kB gzipped

// Modern alternative: Use native DOM APIs or Vue/React
// jQuery is NOT recommended for new Vue/React projects

// Legacy jQuery code you might encounter:
$(document).ready(function() {
  $('.button').on('click', function() {
    $(this).addClass('active');
    $.ajax({
      url: '/api/data',
      success: function(data) {
        $('#result').html(data);
      }
    });
  });
});

// Modern vanilla JS equivalent:
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.button').forEach(btn => {
    btn.addEventListener('click', async function() {
      this.classList.add('active');
      const response = await fetch('/api/data');
      const data = await response.text();
      document.getElementById('result').innerHTML = data;
    });
  });
});
```

**When jQuery might still be used:**
- Legacy codebases requiring maintenance
- WordPress plugin development
- Quick prototypes without build tools
- jQuery-dependent plugins (DataTables, Select2)

**Migration from jQuery to Vue:**
```javascript
// jQuery plugin wrapper for Vue
import $ from 'jquery';
import 'select2';

// Composable to use jQuery plugins in Vue
export function useSelect2(element, options = {}) {
  onMounted(() => {
    $(element.value).select2(options);
  });

  onUnmounted(() => {
    $(element.value).select2('destroy');
  });
}
```

### Testing Libraries

#### Vitest (Recommended for Vue/Vite)
```javascript
// Vitest - Vite-native test framework
// Weekly downloads: ~11 million
// Fast, HMR-aware, Vue/Vite integration

// vitest.config.js
import { defineConfig } from 'vitest/config';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
  plugins: [vue()],
  test: {
    globals: true,
    environment: 'jsdom',
    setupFiles: ['./tests/setup.js'],
    coverage: {
      provider: 'v8',
      reporter: ['text', 'html'],
    },
  },
});

// Component test example
import { describe, it, expect, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import Counter from '@/Components/Counter.vue';

describe('Counter', () => {
  it('increments count on click', async () => {
    const wrapper = mount(Counter);

    expect(wrapper.text()).toContain('Count: 0');
    await wrapper.find('button').trigger('click');
    expect(wrapper.text()).toContain('Count: 1');
  });

  it('emits update event', async () => {
    const wrapper = mount(Counter);

    await wrapper.find('button').trigger('click');
    expect(wrapper.emitted('update')).toHaveLength(1);
    expect(wrapper.emitted('update')[0]).toEqual([1]);
  });
});
```

#### Jest
```javascript
// Jest - Most popular testing framework
// Weekly downloads: ~30 million
// Feature-rich, snapshot testing, mocking

// jest.config.js
module.exports = {
  testEnvironment: 'jsdom',
  transform: {
    '^.+\\.vue$': '@vue/vue3-jest',
    '^.+\\.js$': 'babel-jest',
  },
  moduleNameMapper: {
    '^@/(.*)$': '<rootDir>/src/$1',
  },
  setupFilesAfterEnv: ['<rootDir>/tests/setup.js'],
};

// Test example
import { mount } from '@vue/test-utils';
import MyComponent from '@/components/MyComponent.vue';

describe('MyComponent', () => {
  it('renders correctly', () => {
    const wrapper = mount(MyComponent, {
      props: { title: 'Hello' },
    });

    expect(wrapper.html()).toMatchSnapshot();
  });

  it('calls API on mount', async () => {
    const mockFetch = jest.fn().mockResolvedValue({ data: [] });
    global.fetch = mockFetch;

    mount(MyComponent);

    expect(mockFetch).toHaveBeenCalledWith('/api/data');
  });
});
```

#### Playwright (E2E Testing - Recommended)
```javascript
// Playwright - Modern E2E testing
// Weekly downloads: ~31 million (highest for E2E)
// Cross-browser, fast, reliable

// playwright.config.js
import { defineConfig } from '@playwright/test';

export default defineConfig({
  testDir: './e2e',
  timeout: 30000,
  use: {
    baseURL: 'http://localhost:8000',
    screenshot: 'only-on-failure',
    trace: 'retain-on-failure',
  },
  projects: [
    { name: 'chromium', use: { browserName: 'chromium' } },
    { name: 'firefox', use: { browserName: 'firefox' } },
    { name: 'webkit', use: { browserName: 'webkit' } },
  ],
  webServer: {
    command: 'php artisan serve',
    port: 8000,
    reuseExistingServer: !process.env.CI,
  },
});

// E2E test example
import { test, expect } from '@playwright/test';

test.describe('Invoice Management', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/login');
    await page.fill('[name="email"]', 'test@example.com');
    await page.fill('[name="password"]', 'password');
    await page.click('button[type="submit"]');
    await expect(page).toHaveURL('/dashboard');
  });

  test('can create invoice', async ({ page }) => {
    await page.goto('/invoices/create');

    await page.fill('[name="client"]', 'Acme Corp');
    await page.fill('[name="amount"]', '1000');
    await page.click('button:has-text("Save")');

    await expect(page.locator('.flash-success')).toBeVisible();
    await expect(page).toHaveURL(/\/invoices\/\d+/);
  });
});
```

#### Cypress
```javascript
// Cypress - Popular E2E testing
// Weekly downloads: ~5 million
// Great DX, time-travel debugging

// cypress.config.js
import { defineConfig } from 'cypress';

export default defineConfig({
  e2e: {
    baseUrl: 'http://localhost:8000',
    supportFile: 'cypress/support/e2e.js',
    viewportWidth: 1280,
    viewportHeight: 720,
  },
});

// E2E test example
describe('Authentication', () => {
  it('logs in successfully', () => {
    cy.visit('/login');
    cy.get('[name="email"]').type('user@example.com');
    cy.get('[name="password"]').type('password');
    cy.get('button[type="submit"]').click();

    cy.url().should('include', '/dashboard');
    cy.contains('Welcome back').should('be.visible');
  });
});
```

#### Testing Library (@testing-library/vue)
```javascript
// Vue Testing Library - User-centric testing
// Encourages accessible component design

import { render, screen, fireEvent } from '@testing-library/vue';
import userEvent from '@testing-library/user-event';
import LoginForm from '@/Components/LoginForm.vue';

describe('LoginForm', () => {
  it('submits form with user data', async () => {
    const user = userEvent.setup();
    const mockSubmit = vi.fn();

    render(LoginForm, {
      props: { onSubmit: mockSubmit },
    });

    // Query by accessible names (better than test IDs)
    await user.type(screen.getByLabelText(/email/i), 'test@example.com');
    await user.type(screen.getByLabelText(/password/i), 'secret123');
    await user.click(screen.getByRole('button', { name: /sign in/i }));

    expect(mockSubmit).toHaveBeenCalledWith({
      email: 'test@example.com',
      password: 'secret123',
    });
  });
});
```

#### Testing Framework Comparison

| Framework | Type | Speed | DX | Browser Support | Best For |
|-----------|------|-------|-----|-----------------|----------|
| Vitest | Unit | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ | jsdom | Vue/Vite projects |
| Jest | Unit | ⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ | jsdom | React, general |
| Playwright | E2E | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐ | All browsers | Cross-browser E2E |
| Cypress | E2E | ⭐⭐⭐ | ⭐⭐⭐⭐⭐ | Chrome, Firefox, Edge | Developer-friendly E2E |
| Testing Library | Component | ⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ | - | Accessibility-focused |

### Build Tools

#### Vite (Current Project - Recommended)
```javascript
// Vite - Next generation frontend tooling
// Weekly downloads: ~18 million
// Uses native ES modules for blazing fast dev server

// vite.config.js - This project's configuration
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
  plugins: [
    laravel({
      input: ['resources/js/app.js'],
      refresh: true,
    }),
    vue({
      template: {
        transformAssetUrls: {
          base: null,
          includeAbsolute: false,
        },
      },
    }),
  ],
  resolve: {
    alias: {
      '@': '/resources/js',
    },
  },
  build: {
    rollupOptions: {
      output: {
        manualChunks: {
          vendor: ['vue', '@inertiajs/vue3'],
          ui: ['@headlessui/vue', '@heroicons/vue'],
        },
      },
    },
  },
});
```

**Vite Advantages:**
- Instant server start (no bundling in dev)
- Lightning-fast HMR
- Optimized production builds with Rollup
- First-class Vue support
- Rich plugin ecosystem

#### Webpack
```javascript
// Webpack - Industry standard bundler
// Weekly downloads: ~27 million
// Highly configurable, mature ecosystem

// webpack.config.js example
const path = require('path');
const { VueLoaderPlugin } = require('vue-loader');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');

module.exports = {
  mode: 'production',
  entry: './src/main.js',
  output: {
    path: path.resolve(__dirname, 'dist'),
    filename: '[name].[contenthash].js',
  },
  module: {
    rules: [
      { test: /\.vue$/, loader: 'vue-loader' },
      { test: /\.js$/, loader: 'babel-loader' },
      { test: /\.css$/, use: [MiniCssExtractPlugin.loader, 'css-loader'] },
    ],
  },
  plugins: [
    new VueLoaderPlugin(),
    new MiniCssExtractPlugin(),
  ],
  optimization: {
    splitChunks: { chunks: 'all' },
  },
};
```

**When to use Webpack over Vite:**
- Legacy projects already using Webpack
- Need specific Webpack loaders not available in Vite
- Complex module federation setups
- Custom build requirements

#### esbuild
```javascript
// esbuild - Extremely fast bundler (100x faster than Webpack)
// Written in Go for speed
// Used by Vite for dependency pre-bundling

// esbuild.config.js
import * as esbuild from 'esbuild';
import vuePlugin from 'esbuild-plugin-vue3';

await esbuild.build({
  entryPoints: ['src/main.js'],
  bundle: true,
  outfile: 'dist/bundle.js',
  plugins: [vuePlugin()],
  minify: true,
  sourcemap: true,
  target: ['es2020'],
});
```

#### Rollup
```javascript
// Rollup - ES module bundler
// Best for libraries, used by Vite for production
// Weekly downloads: ~27 million

// rollup.config.js
import vue from 'rollup-plugin-vue';
import resolve from '@rollup/plugin-node-resolve';
import commonjs from '@rollup/plugin-commonjs';
import terser from '@rollup/plugin-terser';

export default {
  input: 'src/index.js',
  output: [
    { file: 'dist/bundle.esm.js', format: 'esm' },
    { file: 'dist/bundle.cjs.js', format: 'cjs' },
  ],
  plugins: [
    vue(),
    resolve(),
    commonjs(),
    terser(),
  ],
  external: ['vue'],
};
```

#### Build Tool Comparison

| Tool | Dev Speed | Build Speed | Config | Best For |
|------|-----------|-------------|--------|----------|
| Vite | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐ | Simple | Modern apps, Vue/React |
| Webpack | ⭐⭐ | ⭐⭐⭐ | Complex | Legacy, complex setups |
| esbuild | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ | Simple | Speed-critical, simple needs |
| Rollup | ⭐⭐⭐ | ⭐⭐⭐⭐ | Medium | Libraries, ES modules |
| Turbopack | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ | Simple | Next.js projects |

### Utility Libraries

#### Lodash
```javascript
// Lodash - Utility library
// Weekly downloads: ~50 million
// Bundle size: ~70kB full, use modular imports

// Bad - imports entire library
import _ from 'lodash';
_.debounce(fn, 300);

// Good - modular imports (tree-shakable)
import debounce from 'lodash/debounce';
import groupBy from 'lodash/groupBy';
import sortBy from 'lodash/sortBy';

// Better - use lodash-es for ES modules
import { debounce, groupBy, sortBy } from 'lodash-es';

// Common patterns
const users = [
  { name: 'John', age: 30, department: 'Sales' },
  { name: 'Jane', age: 25, department: 'Engineering' },
];

// Group by department
const byDept = groupBy(users, 'department');

// Sort by age
const sorted = sortBy(users, 'age');

// Debounce search
const debouncedSearch = debounce((query) => {
  fetchResults(query);
}, 300);
```

**Modern alternatives (smaller bundle):**
```javascript
// Native alternatives to common Lodash functions
// Many Lodash functions now have native equivalents

// Lodash: _.find(array, predicate)
// Native:
array.find(predicate);

// Lodash: _.filter(array, predicate)
// Native:
array.filter(predicate);

// Lodash: _.map(array, iteratee)
// Native:
array.map(iteratee);

// Lodash: _.flatten(array)
// Native:
array.flat();

// Lodash: _.uniq(array)
// Native:
[...new Set(array)];

// Lodash: _.get(object, 'a.b.c', default)
// Native:
object?.a?.b?.c ?? defaultValue;
```

#### Date Libraries

```javascript
// date-fns - Modern date utility library (RECOMMENDED)
// Modular, tree-shakable, immutable
// Bundle: ~2kB per function used
import { format, addDays, parseISO, differenceInDays } from 'date-fns';
import { nl } from 'date-fns/locale';

const date = parseISO('2025-01-15');
format(date, 'EEEE, MMMM do, yyyy'); // "Wednesday, January 15th, 2025"
format(date, 'EEEE d MMMM yyyy', { locale: nl }); // "woensdag 15 januari 2025"

const nextWeek = addDays(date, 7);
const daysBetween = differenceInDays(nextWeek, date); // 7

// day.js - 2kB alternative to Moment.js
// Plugin-based, Moment.js compatible API
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import 'dayjs/locale/nl';

dayjs.extend(relativeTime);
dayjs.locale('nl');

dayjs().format('YYYY-MM-DD'); // "2025-01-15"
dayjs().add(7, 'day').fromNow(); // "over 7 dagen"

// AVOID: Moment.js (deprecated, 300kB+)
// import moment from 'moment'; // DON'T USE
```

#### HTTP Clients

```javascript
// Axios - Promise-based HTTP client
// Weekly downloads: ~45 million
import axios from 'axios';

// Create configured instance
const api = axios.create({
  baseURL: '/api',
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
  },
});

// Request interceptor (add auth token)
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Response interceptor (handle errors)
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

// Usage
const { data } = await api.get('/users');
await api.post('/users', { name: 'John' });

// ky - Tiny HTTP client (3.5kB)
// Modern alternative to Axios
import ky from 'ky';

const api = ky.extend({
  prefixUrl: '/api',
  hooks: {
    beforeRequest: [
      (request) => {
        request.headers.set('Authorization', `Bearer ${token}`);
      },
    ],
  },
});

const data = await api.get('users').json();

// ofetch - Universal fetch (works in Node, browser, workers)
// Used by Nuxt 3
import { ofetch } from 'ofetch';

const data = await ofetch('/api/users', {
  method: 'POST',
  body: { name: 'John' },
  retry: 3,
});
```

#### Validation Libraries

```javascript
// Zod - TypeScript-first schema validation
// Excellent type inference, composable
import { z } from 'zod';

// Define schema
const UserSchema = z.object({
  name: z.string().min(2, 'Name must be at least 2 characters'),
  email: z.string().email('Invalid email'),
  age: z.number().min(18).optional(),
  role: z.enum(['admin', 'user', 'guest']),
});

// Infer TypeScript type from schema
type User = z.infer<typeof UserSchema>;

// Validate data
const result = UserSchema.safeParse(data);
if (result.success) {
  console.log(result.data); // typed as User
} else {
  console.log(result.error.issues); // validation errors
}

// Yup - Popular validation library
import * as yup from 'yup';

const schema = yup.object({
  name: yup.string().required().min(2),
  email: yup.string().email().required(),
  age: yup.number().positive().integer(),
});

try {
  const valid = await schema.validate(data);
} catch (err) {
  console.log(err.errors);
}
```

### Animation Libraries

#### GSAP (GreenSock Animation Platform)
```javascript
// GSAP - Professional-grade animation
// Industry standard, 60fps performance
// Weekly downloads: ~700k

import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

// Basic animation
gsap.to('.box', {
  x: 100,
  rotation: 360,
  duration: 1,
  ease: 'power2.out',
});

// Timeline for sequences
const tl = gsap.timeline();
tl.from('.header', { y: -100, opacity: 0 })
  .from('.content', { opacity: 0 }, '-=0.3')
  .from('.button', { scale: 0 });

// Scroll-triggered animation
gsap.from('.card', {
  scrollTrigger: {
    trigger: '.card',
    start: 'top 80%',
    toggleActions: 'play none none reverse',
  },
  y: 100,
  opacity: 0,
  stagger: 0.2,
});

// Vue integration
import { onMounted, ref } from 'vue';

const boxRef = ref(null);

onMounted(() => {
  gsap.from(boxRef.value, {
    opacity: 0,
    y: 50,
    duration: 0.8,
  });
});
```

#### Motion One (Lightweight Alternative)
```javascript
// Motion One - 3.8kB animation library
// Modern, performant, Web Animations API
import { animate, stagger, spring } from 'motion';

// Basic animation
animate('.box', { x: 100, rotate: 180 }, { duration: 0.5 });

// Spring physics
animate('.modal', { scale: [0, 1] }, { easing: spring() });

// Staggered animations
animate('.item', { opacity: [0, 1], y: [20, 0] }, {
  delay: stagger(0.1),
});

// Vue composable
import { useMotion } from '@vueuse/motion';

const target = ref(null);
const { variant } = useMotion(target, {
  initial: { opacity: 0, y: 100 },
  enter: { opacity: 1, y: 0, transition: { duration: 500 } },
});
```

#### Anime.js
```javascript
// Anime.js - Lightweight animation library
// ~17kB, good SVG support
import anime from 'animejs';

anime({
  targets: '.box',
  translateX: 250,
  rotate: '1turn',
  backgroundColor: '#FFF',
  duration: 800,
  easing: 'easeInOutQuad',
});

// SVG path animation
anime({
  targets: '.line',
  strokeDashoffset: [anime.setDashoffset, 0],
  easing: 'easeInOutSine',
  duration: 1500,
});
```

#### Lottie (After Effects Animations)
```javascript
// Lottie - Render After Effects animations
// Great for complex animated illustrations
import lottie from 'lottie-web';

// Load animation
const animation = lottie.loadAnimation({
  container: document.getElementById('lottie-container'),
  renderer: 'svg',
  loop: true,
  autoplay: true,
  path: '/animations/loading.json',
});

// Control animation
animation.play();
animation.pause();
animation.setSpeed(2);
animation.goToAndStop(30, true); // Go to frame 30

// Vue component wrapper
import { LottieAnimation } from 'lottie-web-vue';
```

#### Auto-Animate (Already Installed)
```javascript
// @formkit/auto-animate - Automatic animations
// Already installed in this project
// Zero-config, ~2kB

import { useAutoAnimate } from '@formkit/auto-animate/vue';

const [parent] = useAutoAnimate();

// In template: <ul ref="parent">
// Items added/removed will animate automatically
```

### State Management

#### Pinia (Current Project - Vue Official)
```javascript
// Pinia - Vue's official state management
// Already used in this project
// Type-safe, modular, devtools support

import { defineStore } from 'pinia';

export const useCartStore = defineStore('cart', {
  state: () => ({
    items: [],
    loading: false,
  }),

  getters: {
    totalItems: (state) => state.items.length,
    totalPrice: (state) =>
      state.items.reduce((sum, item) => sum + item.price * item.quantity, 0),
  },

  actions: {
    async fetchCart() {
      this.loading = true;
      try {
        const { data } = await axios.get('/api/cart');
        this.items = data;
      } finally {
        this.loading = false;
      }
    },

    addItem(product) {
      const existing = this.items.find(i => i.id === product.id);
      if (existing) {
        existing.quantity++;
      } else {
        this.items.push({ ...product, quantity: 1 });
      }
    },
  },
});

// Setup store syntax (composition API style)
export const useAuthStore = defineStore('auth', () => {
  const user = ref(null);
  const isAuthenticated = computed(() => !!user.value);

  async function login(credentials) {
    const { data } = await axios.post('/login', credentials);
    user.value = data.user;
  }

  return { user, isAuthenticated, login };
});
```

#### Redux (React Ecosystem)
```javascript
// Redux Toolkit - Standard Redux approach
// Most popular for React apps

import { configureStore, createSlice } from '@reduxjs/toolkit';

const cartSlice = createSlice({
  name: 'cart',
  initialState: { items: [], loading: false },
  reducers: {
    addItem: (state, action) => {
      state.items.push(action.payload);
    },
    removeItem: (state, action) => {
      state.items = state.items.filter(i => i.id !== action.payload);
    },
  },
});

const store = configureStore({
  reducer: {
    cart: cartSlice.reducer,
  },
});
```

#### Zustand (React Alternative)
```javascript
// Zustand - Minimal React state management
// Growing in popularity, simpler than Redux

import { create } from 'zustand';

const useCartStore = create((set) => ({
  items: [],
  addItem: (item) => set((state) => ({
    items: [...state.items, item]
  })),
  clearCart: () => set({ items: [] }),
}));
```

#### Jotai (Atomic State)
```javascript
// Jotai - Primitive and flexible state
// Atomic approach, great for React

import { atom, useAtom } from 'jotai';

const countAtom = atom(0);
const doubledAtom = atom((get) => get(countAtom) * 2);

function Counter() {
  const [count, setCount] = useAtom(countAtom);
  const [doubled] = useAtom(doubledAtom);

  return (
    <button onClick={() => setCount(c => c + 1)}>
      {count} (doubled: {doubled})
    </button>
  );
}
```

### Data Fetching Libraries

#### TanStack Query (Vue Query)
```javascript
// @tanstack/vue-query - Powerful data fetching
// Caching, background updates, optimistic updates
import { useQuery, useMutation, useQueryClient } from '@tanstack/vue-query';

// Setup in app
import { VueQueryPlugin } from '@tanstack/vue-query';
app.use(VueQueryPlugin);

// Fetch data with caching
const { data, isLoading, error, refetch } = useQuery({
  queryKey: ['users'],
  queryFn: () => axios.get('/api/users').then(r => r.data),
  staleTime: 5 * 60 * 1000, // 5 minutes
});

// Mutations with optimistic updates
const queryClient = useQueryClient();

const mutation = useMutation({
  mutationFn: (newUser) => axios.post('/api/users', newUser),
  onMutate: async (newUser) => {
    await queryClient.cancelQueries({ queryKey: ['users'] });
    const previous = queryClient.getQueryData(['users']);
    queryClient.setQueryData(['users'], (old) => [...old, newUser]);
    return { previous };
  },
  onError: (err, newUser, context) => {
    queryClient.setQueryData(['users'], context.previous);
  },
  onSettled: () => {
    queryClient.invalidateQueries({ queryKey: ['users'] });
  },
});
```

#### SWR (React)
```javascript
// SWR - React Hooks for Data Fetching
// Created by Vercel, stale-while-revalidate

import useSWR from 'swr';

const fetcher = (url) => fetch(url).then(r => r.json());

function Users() {
  const { data, error, isLoading, mutate } = useSWR('/api/users', fetcher);

  if (isLoading) return <div>Loading...</div>;
  if (error) return <div>Error loading users</div>;

  return (
    <ul>
      {data.map(user => <li key={user.id}>{user.name}</li>)}
    </ul>
  );
}
```

### Form Libraries

#### VeeValidate (Vue - Recommended)
```javascript
// VeeValidate - Vue form validation
// Works great with Zod or Yup

import { useForm, useField } from 'vee-validate';
import { toTypedSchema } from '@vee-validate/zod';
import { z } from 'zod';

const schema = toTypedSchema(
  z.object({
    email: z.string().email(),
    password: z.string().min(8),
  })
);

const { handleSubmit, errors } = useForm({
  validationSchema: schema,
});

const { value: email } = useField('email');
const { value: password } = useField('password');

const onSubmit = handleSubmit((values) => {
  console.log(values); // Typed!
});
```

#### FormKit (Vue)
```javascript
// FormKit - Comprehensive form framework
// Schema-driven, accessible, themeable

import { FormKit, FormKitSchema } from '@formkit/vue';

// Template-based
<FormKit
  type="form"
  @submit="handleSubmit"
  :actions="false"
>
  <FormKit
    type="email"
    name="email"
    label="Email"
    validation="required|email"
  />
  <FormKit
    type="password"
    name="password"
    label="Password"
    validation="required|length:8"
  />
  <FormKit type="submit">Sign In</FormKit>
</FormKit>
```

### Routing Libraries

#### Vue Router (Current Project)
```javascript
// Vue Router - Official Vue routing
// Already integrated in this Inertia project
// For non-Inertia Vue apps:

import { createRouter, createWebHistory } from 'vue-router';

const routes = [
  { path: '/', component: () => import('./views/Home.vue') },
  {
    path: '/users/:id',
    component: () => import('./views/User.vue'),
    meta: { requiresAuth: true },
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

// Navigation guard
router.beforeEach((to, from, next) => {
  if (to.meta.requiresAuth && !isAuthenticated()) {
    next('/login');
  } else {
    next();
  }
});
```

### UI Libraries (Already Covered in CSS/Tailwind Skill)

See the CSS/Tailwind Expert Skill for comprehensive UI library coverage including:
- HeadlessUI (already installed)
- Radix Vue
- PrimeVue
- Naive UI
- DaisyUI
- Flowbite

### Charts and Data Visualization

#### Chart.js (Already Installed)
```javascript
// Chart.js - Simple, flexible charting
// Already in this project

import { Chart, registerables } from 'chart.js';
Chart.register(...registerables);

new Chart(ctx, {
  type: 'bar',
  data: {
    labels: ['Jan', 'Feb', 'Mar'],
    datasets: [{
      label: 'Revenue',
      data: [12000, 19000, 15000],
      backgroundColor: 'rgba(59, 130, 246, 0.5)',
    }],
  },
  options: {
    responsive: true,
    plugins: {
      legend: { position: 'top' },
    },
  },
});
```

#### ECharts
```javascript
// ECharts - Powerful visualization library
// Great for complex charts and maps

import * as echarts from 'echarts';

const chart = echarts.init(document.getElementById('chart'));

chart.setOption({
  xAxis: { type: 'category', data: ['Mon', 'Tue', 'Wed'] },
  yAxis: { type: 'value' },
  series: [{ data: [120, 200, 150], type: 'line' }],
});
```

#### ApexCharts
```javascript
// ApexCharts - Modern charting library
// Interactive, animations, Vue wrapper

import VueApexCharts from 'vue3-apexcharts';
app.use(VueApexCharts);

// In component
<apexchart
  type="area"
  :options="chartOptions"
  :series="series"
/>
```

### Node.js Libraries (Backend/CLI)

#### Express.js
```javascript
// Express - Minimal web framework
// Most popular Node.js framework

import express from 'express';

const app = express();

app.use(express.json());

app.get('/api/users', async (req, res) => {
  const users = await User.findAll();
  res.json(users);
});

app.listen(3000);
```

#### Fastify
```javascript
// Fastify - Fast web framework
// Lower overhead than Express

import Fastify from 'fastify';

const fastify = Fastify({ logger: true });

fastify.get('/api/users', async (request, reply) => {
  return { users: [] };
});

await fastify.listen({ port: 3000 });
```

### Real-time Libraries

#### Socket.io
```javascript
// Socket.io - Real-time bidirectional communication
// Works with Laravel Echo

import { io } from 'socket.io-client';

const socket = io('http://localhost:3000');

socket.on('connect', () => {
  console.log('Connected');
});

socket.on('message', (data) => {
  console.log('Received:', data);
});

socket.emit('message', { text: 'Hello' });
```

#### Pusher / Laravel Echo
```javascript
// Laravel Echo - Real-time events for Laravel
// Used with Pusher or Soketi

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
  broadcaster: 'pusher',
  key: import.meta.env.VITE_PUSHER_APP_KEY,
  cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
});

Echo.channel('orders')
  .listen('OrderCreated', (e) => {
    console.log('New order:', e.order);
  });

Echo.private(`users.${userId}`)
  .notification((notification) => {
    console.log(notification);
  });
```

### Library Selection Decision Tree

**Frontend Framework:**
- Building with Laravel + Vue → **Vue 3 + Inertia** (current setup)
- Mobile app needed → React (React Native) or Vue (Capacitor)
- Enterprise with strict structure → Angular
- Performance critical, small bundle → Svelte or Solid.js

**State Management:**
- Vue app → **Pinia** (current setup)
- React app, complex state → Redux Toolkit or Zustand
- Atomic/granular state → Jotai or Recoil

**Testing:**
- Unit tests with Vite → **Vitest**
- E2E tests → **Playwright** (best coverage) or Cypress (best DX)

**Data Fetching:**
- Vue → TanStack Vue Query
- React → TanStack Query or SWR
- With Inertia → Use Inertia's built-in methods (current setup)

**HTTP Client:**
- Full features → Axios
- Minimal → ky or ofetch
- With Inertia → Use Inertia's router (current setup)

**Animation:**
- Professional/complex → GSAP
- Simple list animations → Auto-Animate (current setup)
- Vue-specific → @vueuse/motion

**Charts:**
- Simple charts → Chart.js (current setup)
- Complex/interactive → ECharts or ApexCharts

## Version History & Updates

### Version 1.2.0 (2025-12-16)
- Added comprehensive JavaScript Libraries and Frameworks Ecosystem section
- Coverage of 40+ libraries across all major categories:
  - Frontend frameworks (Vue, React, Angular, Svelte, Solid.js, Preact)
  - Inertia.js deep dive
  - DOM manipulation (jQuery legacy, modern alternatives)
  - Testing (Vitest, Jest, Playwright, Cypress, Testing Library)
  - Build tools (Vite, Webpack, esbuild, Rollup)
  - Utility libraries (Lodash, date-fns, day.js, Axios, Zod)
  - Animation (GSAP, Motion One, Anime.js, Lottie, Auto-Animate)
  - State management (Pinia, Redux, Zustand, Jotai)
  - Data fetching (TanStack Query, SWR)
  - Form validation (VeeValidate, FormKit)
  - Charts (Chart.js, ECharts, ApexCharts)
  - Real-time (Socket.io, Laravel Echo)
- Added decision trees for library selection
- Added comparison matrices for frameworks and tools

### Version 1.1.0 (2025-12-15)
- Expanded troubleshooting problems to 18 items
- Added 16 anti-patterns/common mistakes
- Added 7 advanced composables (usePagination, useFormWizard, etc.)

### Version 1.0.0 (2025-12-15)
- Initial comprehensive skill creation
- Covers ES6+, Vue 3, Pinia, Vue Router, Inertia.js
- Includes troubleshooting, security, and best practices

### Known Limitations

1. **TypeScript Examples Limited**
   - Most examples use JavaScript
   - Workaround: Add TypeScript types manually using Vue 3's defineProps<T>() syntax

2. **Testing Section Basic**
   - Covers Vitest basics
   - Planned: Add more E2E testing with Cypress/Playwright

## Appendices

### Appendix A: Glossary

| Term | Definition |
|------|------------|
| Composition API | Vue 3's function-based API for component logic |
| Composable | Reusable function that encapsulates reactive logic |
| Ref | Reactive wrapper for primitive values (use .value to access) |
| Reactive | Reactive wrapper for objects (no .value needed) |
| Script Setup | Compile-time syntactic sugar for Composition API |
| Pinia | Vue's official state management library |
| Inertia | Protocol connecting server-side routing with client-side SPA |
| HMR | Hot Module Replacement - updates code without full reload |
| SFC | Single File Component (.vue files) |

### Appendix B: Decision Trees

**Should I use ref or reactive?**
- If value is primitive (string, number, boolean) → Use `ref`
- If value will be replaced entirely → Use `ref`
- If value is object that won't be replaced → Use `reactive`
- If unsure → Use `ref` (works for everything)

**Should I use computed or watch?**
- If deriving a value from reactive state → Use `computed`
- If performing side effects (API calls, DOM manipulation) → Use `watch`
- If value can be computed synchronously → Use `computed`
- If need to run code when dependency changes → Use `watch`

**Should I use Pinia or local state?**
- If state is only used in one component → Use local state
- If state is shared between siblings → Use provide/inject or lift to parent
- If state is shared across distant components → Use Pinia
- If state needs persistence → Use Pinia with persistence plugin
- If state comes from server on each page → Use Inertia props

**Should I use watch or watchEffect?**
- If you need old/new values → Use `watch`
- If you need explicit dependencies → Use `watch`
- If you want automatic dependency tracking → Use `watchEffect`
- If running on mount and on changes → Use `watchEffect` or `watch` with `immediate: true`

### Appendix C: Migration from Options API

**Data → Refs/Reactive:**
```javascript
// Options API
data() {
  return { count: 0, user: null }
}

// Composition API
const count = ref(0);
const user = ref(null);
```

**Methods → Functions:**
```javascript
// Options API
methods: {
  increment() { this.count++ }
}

// Composition API
function increment() { count.value++ }
```

**Computed → Computed:**
```javascript
// Options API
computed: {
  doubleCount() { return this.count * 2 }
}

// Composition API
const doubleCount = computed(() => count.value * 2);
```

**Watch → Watch:**
```javascript
// Options API
watch: {
  count(newVal, oldVal) { /* ... */ }
}

// Composition API
watch(count, (newVal, oldVal) => { /* ... */ });
```

**Lifecycle Hooks:**
```javascript
// Options API → Composition API
created → (use script setup directly)
mounted → onMounted
updated → onUpdated
unmounted → onUnmounted
beforeMount → onBeforeMount
beforeUpdate → onBeforeUpdate
beforeUnmount → onBeforeUnmount
```

---

## 100 JavaScript Tips, Best Practices & Modern Features (2025)

### Modern Syntax & Features (1-20)

1. **`const` by default, `let` when needed** - Never use `var`; it's function-scoped and hoisted
2. **Nullish coalescing (`??`)** - `value ?? default` only uses default for `null`/`undefined`, not `0` or `''`
3. **Optional chaining (`?.`)** - `user?.address?.city` safely accesses nested properties
4. **Logical assignment** - `a ||= b`, `a ??= b`, `a &&= b` for conditional assignment
5. **Numeric separators** - `1_000_000` for readable large numbers
6. **Private class fields** - `#privateField` for truly private properties
7. **Static class fields** - `static count = 0` without constructor
8. **Top-level await** - Use `await` at module top level without async wrapper
9. **`import.meta`** - Access module metadata like `import.meta.url`
10. **Dynamic imports** - `const module = await import('./module.js')` for code splitting
11. **`Object.fromEntries()`** - Convert `[['a', 1], ['b', 2]]` to `{a: 1, b: 2}`
12. **`Array.prototype.at()`** - `array.at(-1)` for last element (negative indexing)
13. **`String.prototype.replaceAll()`** - Replace all occurrences without regex
14. **`Promise.allSettled()`** - Wait for all promises regardless of rejection
15. **`Promise.any()`** - Resolve with first successful promise
16. **`globalThis`** - Universal global object reference across environments
17. **`structuredClone()`** - Deep clone objects including circular references
18. **`Array.prototype.findLast()`** - Find from end of array
19. **`Array.prototype.toSorted()`** - Non-mutating sort (returns new array)
20. **`Array.prototype.toReversed()`** - Non-mutating reverse

### ES2024/2025 Features (21-30)

21. **Set methods** - `set.intersection()`, `set.union()`, `set.difference()`, `set.symmetricDifference()`
22. **`Object.groupBy()`** - Group array items by key function
23. **`Map.groupBy()`** - Group into Map instead of object
24. **`Promise.withResolvers()`** - Deferred pattern: `const { promise, resolve, reject } = Promise.withResolvers()`
25. **Well-formed Unicode strings** - `toWellFormed()` ensures valid Unicode
26. **Array `with()` method** - `arr.with(index, value)` returns new array with replacement
27. **`Atomics.waitAsync()`** - Async atomics for SharedArrayBuffer
28. **RegExp `v` flag** - Unicode sets and string properties in regex
29. **Decorators (Stage 3)** - `@decorator` syntax for class modifications
30. **Temporal API (Stage 3)** - Modern date/time handling replacing Date

### Array Methods & Patterns (31-45)

31. **Prefer `map`/`filter`/`reduce`** - Declarative over imperative loops
32. **Avoid mutating methods** - Use `toSorted()`, `toReversed()`, `toSpliced()` for immutability
33. **`Array.from()` with mapFn** - `Array.from({length: 5}, (_, i) => i)` creates `[0,1,2,3,4]`
34. **Spread for shallow clone** - `[...array]` instead of `array.slice()`
35. **Flatten nested arrays** - `array.flat(Infinity)` for any depth
36. **`flatMap` combines map + flat** - Transform and flatten in one pass
37. **`includes()` over `indexOf()`** - Returns boolean, more readable
38. **`some()` and `every()`** - Short-circuit for existence/universal checks
39. **Reduce with initial value** - Always provide second argument to avoid empty array errors
40. **Chain array methods** - `arr.filter().map().sort()` for pipelines
41. **Destructure in parameters** - `.map(({name, age}) => ...)` for clarity
42. **Use `Set` for unique values** - `[...new Set(array)]` removes duplicates
43. **`Array.isArray()`** - Reliable array check, not `instanceof`
44. **Guard against empty arrays** - Check `.length` before `.reduce()` without initial value
45. **`findIndex()` returns -1** - Remember to handle not-found case

### Object Patterns (46-55)

46. **Shorthand properties** - `{name, age}` instead of `{name: name, age: age}`
47. **Computed property names** - `{[dynamicKey]: value}` for dynamic keys
48. **Method shorthand** - `{method() {}}` instead of `{method: function() {}}`
49. **Object spread for merge** - `{...obj1, ...obj2}` (later wins)
50. **Object destructuring with rename** - `const {name: userName} = obj`
51. **Default values in destructuring** - `const {name = 'Guest'} = obj`
52. **`Object.entries()` for iteration** - `for (const [key, value] of Object.entries(obj))`
53. **`Object.keys()` length for count** - Faster than converting to array
54. **`Object.hasOwn()`** - Preferred over `obj.hasOwnProperty()` (ES2022)
55. **Freeze for constants** - `Object.freeze({...})` for truly immutable objects

### Async Patterns (56-70)

56. **`async/await` over `.then()`** - More readable, easier error handling
57. **Try/catch with async** - Wrap await in try/catch for error handling
58. **`Promise.all()` for parallel** - Execute independent promises concurrently
59. **`Promise.race()` for timeout** - Race promise against timeout promise
60. **Sequential with for...of** - `for (const item of items) await process(item)`
61. **`AbortController` for cancellation** - Cancel fetch requests and other async operations
62. **`finally` always runs** - Cleanup code in try/catch/finally
63. **Async iteration** - `for await (const item of asyncIterable)`
64. **Promisify callbacks** - `util.promisify()` or manual wrapper
65. **Avoid async in constructors** - Use factory pattern instead
66. **Return vs await in async** - `return promise` vs `return await promise` (stack trace difference)
67. **Error handling in Promise.all** - Consider `Promise.allSettled()` to handle mixed results
68. **Debounce async functions** - Prevent rapid-fire API calls
69. **Throttle for rate limiting** - Limit execution frequency
70. **Concurrent limit** - Process batches with `Promise.all()` chunks

### Error Handling (71-80)

71. **Custom error classes** - Extend Error for domain-specific errors
72. **Error cause property** - `new Error('Failed', {cause: originalError})`
73. **Validate early, fail fast** - Check inputs at function entry
74. **Throw specific errors** - `throw new TypeError()` for type issues
75. **Never swallow errors** - Always log or rethrow in catch blocks
76. **Error boundaries** - Catch and recover at component/module boundaries
77. **Stack traces matter** - Don't strip stack traces in production
78. **Optional catch binding** - `catch {}` without variable if not needed
79. **Assertion functions** - Throw if condition false: `assert(condition, message)`
80. **Error codes for i18n** - Use codes, not messages, for translation

### Performance (81-90)

81. **Avoid memory leaks** - Clear intervals, remove listeners, nullify references
82. **WeakMap for caches** - Allows garbage collection of keys
83. **WeakRef for weak references** - Reference objects without preventing GC
84. **FinalizationRegistry** - Cleanup when objects are garbage collected
85. **Lazy initialization** - Compute expensive values only when needed
86. **Memoization** - Cache function results for repeated calls
87. **Web Workers for CPU work** - Offload heavy computation from main thread
88. **Request idle callback** - `requestIdleCallback()` for low-priority work
89. **Batch DOM operations** - Read all, then write all to avoid thrashing
90. **Virtual scrolling** - Render only visible items in long lists

### Security (91-100)

91. **Sanitize user input** - Never trust client data
92. **Avoid `eval()`** - Code execution vulnerability
93. **Avoid `innerHTML`** - Use `textContent` or sanitize HTML
94. **Validate on server** - Client validation is for UX, not security
95. **Use CSP headers** - Prevent XSS with Content Security Policy
96. **HTTPS everywhere** - Never transmit sensitive data over HTTP
97. **Secure cookies** - HttpOnly, Secure, SameSite flags
98. **Input length limits** - Prevent DoS with oversized inputs
99. **Rate limiting** - Prevent brute force and abuse
100. **Subresource integrity** - SRI hashes for CDN scripts

---

## 100 Vue.js 3 Tips, Best Practices & Modern Features (2025)

### Composition API Essentials (1-20)

1. **`<script setup>` is default** - Less boilerplate, automatic component registration
2. **`ref` for primitives** - `const count = ref(0)` - access with `.value` in script
3. **`reactive` for objects** - `const state = reactive({})` - no `.value` needed
4. **`computed` for derived state** - Cached until dependencies change
5. **`watch` for side effects** - Respond to reactive changes
6. **`watchEffect` for auto-tracking** - Tracks dependencies automatically
7. **`shallowRef` for performance** - Only track `.value` changes, not nested
8. **`toRef` extracts single property** - `const name = toRef(props, 'name')`
9. **`toRefs` for destructuring** - `const { name, age } = toRefs(props)` keeps reactivity
10. **`unref` unwraps ref** - Returns `.value` if ref, otherwise the value itself
11. **`isRef()` type guard** - Check if value is a ref
12. **`triggerRef` for shallow updates** - Force update on shallowRef
13. **Lifecycle in setup** - `onMounted`, `onUpdated`, `onUnmounted`, etc.
14. **`defineExpose` for template refs** - Expose methods/properties to parent
15. **`useTemplateRef`** - Access template refs in Composition API
16. **`defineModel` for v-model** - Simplified two-way binding macro (Vue 3.4+)
17. **`defineOptions` for component options** - `name`, `inheritAttrs` without separate block
18. **`defineSlots` for typed slots** - TypeScript slot typing
19. **Multiple v-model bindings** - `v-model:title`, `v-model:content` on same component
20. **`watchPost` timing** - `watch(..., ..., { flush: 'post' })` after DOM update

### Component Patterns (21-35)

21. **Single File Components** - `.vue` files with template, script, style
22. **Component naming** - PascalCase for registration, kebab-case in templates
23. **Props validation** - Use type, required, default, validator
24. **Emit declaration** - `defineEmits(['update', 'delete'])` for documentation
25. **Provide/inject for deep passing** - Avoid prop drilling
26. **Slots for content distribution** - Default, named, and scoped slots
27. **Async components** - `defineAsyncComponent(() => import('./Heavy.vue'))`
28. **Suspense for loading states** - `<Suspense>` with `#fallback` template
29. **Teleport for modals** - `<Teleport to="body">` renders outside component tree
30. **`v-bind` with object** - `v-bind="{ id, class }"` spreads attributes
31. **Transparent wrapper components** - Use `inheritAttrs: false` with `v-bind="$attrs"`
32. **Renderless components** - Logic-only components using scoped slots
33. **Component v-model** - `modelValue` prop + `update:modelValue` emit
34. **Dynamic components** - `<component :is="currentComponent">`
35. **KeepAlive for caching** - Preserve component state when switching

### Reactivity Advanced (36-50)

36. **`readonly` wrapper** - Prevent mutations: `readonly(state)`
37. **`markRaw` to skip reactivity** - For large objects that shouldn't be reactive
38. **`effectScope` for cleanup** - Group effects for collective disposal
39. **`getCurrentScope` access** - Get current effect scope
40. **`onScopeDispose` cleanup** - Register cleanup in effect scope
41. **Reactive vs ref in stores** - Use reactive for store state, ref for individual values
42. **Computed with getter/setter** - Two-way computed properties
43. **Watch with `immediate`** - Run handler immediately on creation
44. **Watch with `deep`** - Track nested object changes (use sparingly)
45. **`watchPostEffect`** - Shorthand for post-flush watch
46. **`watchSyncEffect`** - Synchronous watcher (rarely needed)
47. **Debounced watchers** - Delay handler with custom debounce
48. **Stop watchers** - `const stop = watch(...); stop()` to cleanup
49. **Refs in reactive objects** - Refs auto-unwrap in reactive objects
50. **Custom refs with `customRef`** - Create refs with custom tracking/triggering

### Composables (51-65)

51. **Composables start with `use`** - Convention: `useUser`, `useFetch`, `useLocalStorage`
52. **Return reactive state** - Return refs/reactive from composables
53. **Cleanup in composables** - Use `onUnmounted` or return cleanup function
54. **Composable parameters** - Accept refs or values, normalize with `toValue()`
55. **VueUse library** - Collection of 200+ composables
56. **`useFetch` pattern** - Handle loading, error, data states
57. **`useLocalStorage` pattern** - Persist reactive state to localStorage
58. **`useEventListener` pattern** - Auto-cleanup event listeners
59. **`useIntersectionObserver`** - Lazy loading, infinite scroll
60. **`useDark` for dark mode** - Toggle with persistence
61. **`useDebounce`** - Debounced reactive values
62. **`useThrottle`** - Throttled reactive values
63. **`useAsyncState`** - Execute async function with state
64. **Composable testing** - Test composables in isolation
65. **Shared composables** - Singleton pattern for global state

### Vue Router (66-75)

66. **`useRouter` and `useRoute`** - Access router/route in Composition API
67. **Route meta for guards** - `meta: { requiresAuth: true }`
68. **Navigation guards** - `beforeEach`, `beforeEnter`, component guards
69. **Lazy route components** - `component: () => import('./View.vue')`
70. **Named routes** - `{ name: 'user-profile', path: '/users/:id' }`
71. **Route params typing** - Define prop types from route params
72. **Scroll behavior** - Configure `scrollBehavior` for navigation
73. **Nested routes** - `children` array for nested views
74. **Route transitions** - Combine with Vue transitions
75. **Dynamic route registration** - `router.addRoute()` at runtime

### Pinia State Management (76-85)

76. **Define stores with `defineStore`** - Unique ID + options or setup
77. **Setup stores** - Use Composition API syntax in stores
78. **Options stores** - Use `state`, `getters`, `actions` syntax
79. **`storeToRefs` for destructuring** - Keep reactivity when extracting state
80. **Actions can be async** - Handle API calls in actions
81. **Getters are computed** - Cached derived state
82. **Store composition** - Use other stores inside stores
83. **Plugins for persistence** - `pinia-plugin-persistedstate`
84. **Hot module replacement** - Built-in HMR support
85. **Devtools integration** - Time-travel debugging in Vue Devtools

### Performance (86-95)

86. **`v-once` for static content** - Render once, never update
87. **`v-memo` for expensive renders** - Memoize template parts
88. **`shallowRef`/`shallowReactive`** - Avoid deep reactivity overhead
89. **Virtual scrolling** - Use `vue-virtual-scroller` for long lists
90. **Lazy load components** - `defineAsyncComponent` with `loadingComponent`
91. **Component chunk splitting** - Dynamic imports create separate chunks
92. **Computed over methods** - Computed caches, methods recompute every render
93. **Avoid inline functions in templates** - Define methods instead
94. **Key attribute for lists** - Unique keys prevent unnecessary re-renders
95. **`v-show` vs `v-if`** - `v-show` for frequent toggles, `v-if` for rare

### Testing & Debugging (96-100)

96. **Vue Devtools** - Essential browser extension for debugging
97. **Vitest for unit tests** - Fast, Vue-aware testing
98. **`@vue/test-utils`** - Mount and interact with components
99. **Testing composables** - Create wrapper component or test directly
100. **Snapshot testing** - Compare rendered output against saved snapshot

---

### Vue 3.4+ New Features Quick Reference

| Feature | Description |
|---------|-------------|
| `defineModel` | Simplified v-model macro |
| `v-bind` same-name shorthand | `<div :id>` equals `<div :id="id">` |
| Improved hydration mismatch | Better warnings in SSR |
| `useId()` | Generate unique IDs for accessibility |
| MathML support | First-class MathML rendering |
| `Suspense` stabilized | Ready for production use |

### Performance Checklist for Vue Apps

- [ ] Use `<script setup>` for all components
- [ ] Lazy load routes and heavy components
- [ ] Use `shallowRef` for large objects that don't need deep reactivity
- [ ] Implement virtual scrolling for lists > 100 items
- [ ] Use `v-once` for static content
- [ ] Add unique `key` attributes to all `v-for` lists
- [ ] Avoid deep watchers when possible
- [ ] Use Pinia for cross-component state
- [ ] Profile with Vue Devtools performance tab
- [ ] Enable production mode in builds

