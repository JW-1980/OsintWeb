<template>
  <div class="email-editor h-full flex flex-col bg-white dark:bg-gray-800 rounded-lg overflow-hidden">
    <!-- Toolbar -->
    <div class="flex-none border-b border-gray-200 dark:border-gray-700">
      <!-- Top Toolbar -->
      <div class="flex items-center justify-between px-4 py-2 bg-gray-50 dark:bg-gray-900">
        <div class="flex items-center gap-2">
          <!-- Editor Mode Tabs -->
          <div class="flex bg-gray-200 dark:bg-gray-700 rounded-lg p-1">
            <button
              v-for="mode in editorModes"
              :key="mode.id"
              @click="activeMode = mode.id"
              :class="[
                'px-3 py-1.5 text-sm font-medium rounded-md transition-colors',
                activeMode === mode.id
                  ? 'bg-white dark:bg-gray-600 text-gray-900 dark:text-white shadow-sm'
                  : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'
              ]"
            >
              {{ mode.label }}
            </button>
          </div>

          <div class="h-6 w-px bg-gray-300 dark:bg-gray-600 mx-2"></div>

          <!-- Undo/Redo -->
          <button
            @click="undo"
            :disabled="!canUndo"
            class="p-2 rounded hover:bg-gray-200 dark:hover:bg-gray-700 disabled:opacity-40 disabled:cursor-not-allowed"
            title="Undo (Ctrl+Z)"
          >
            <svg class="w-4 h-4 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
            </svg>
          </button>
          <button
            @click="redo"
            :disabled="!canRedo"
            class="p-2 rounded hover:bg-gray-200 dark:hover:bg-gray-700 disabled:opacity-40 disabled:cursor-not-allowed"
            title="Redo (Ctrl+Y)"
          >
            <svg class="w-4 h-4 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 10h-10a8 8 0 00-8 8v2M21 10l-6 6m6-6l-6-6" />
            </svg>
          </button>

          <div class="h-6 w-px bg-gray-300 dark:bg-gray-600 mx-2"></div>

          <!-- Format -->
          <button
            @click="formatCode"
            class="p-2 rounded hover:bg-gray-200 dark:hover:bg-gray-700"
            title="Format Code"
          >
            <svg class="w-4 h-4 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
            </svg>
          </button>
        </div>

        <div class="flex items-center gap-2">
          <!-- Preview Device Toggle -->
          <div class="flex bg-gray-200 dark:bg-gray-700 rounded-lg p-1">
            <button
              v-for="device in previewDevices"
              :key="device.id"
              @click="previewDevice = device.id"
              :class="[
                'p-1.5 rounded transition-colors',
                previewDevice === device.id
                  ? 'bg-white dark:bg-gray-600 text-blue-600 dark:text-blue-400 shadow-sm'
                  : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'
              ]"
              :title="device.label"
            >
              <component :is="device.icon" class="w-4 h-4" />
            </button>
          </div>

          <div class="h-6 w-px bg-gray-300 dark:bg-gray-600 mx-2"></div>

          <!-- Toggle Preview -->
          <button
            @click="showPreview = !showPreview"
            :class="[
              'px-3 py-1.5 text-sm font-medium rounded-lg transition-colors',
              showPreview
                ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400'
                : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300'
            ]"
          >
            {{ showPreview ? 'Hide Preview' : 'Show Preview' }}
          </button>
        </div>
      </div>

      <!-- Variable & Snippets Toolbar -->
      <div class="flex items-center gap-2 px-4 py-2 bg-white dark:bg-gray-800 overflow-x-auto">
        <!-- Variables Dropdown -->
        <div class="relative" ref="variablesDropdownRef">
          <button
            @click="showVariablesDropdown = !showVariablesDropdown"
            class="flex items-center gap-1 px-3 py-1.5 text-sm bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400 rounded-lg hover:bg-purple-200 dark:hover:bg-purple-900/50"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
            </svg>
            Insert Variable
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>
          <div
            v-if="showVariablesDropdown"
            class="absolute top-full left-0 mt-1 w-72 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg z-50 max-h-80 overflow-y-auto"
          >
            <div class="p-2">
              <input
                v-model="variableSearch"
                type="text"
                placeholder="Search variables..."
                class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white"
              />
            </div>
            <div class="border-t border-gray-200 dark:border-gray-700">
              <button
                v-for="(desc, key) in filteredVariables"
                :key="key"
                @click="insertVariable(key)"
                class="w-full flex items-start gap-3 px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 text-left"
              >
                <code class="flex-none px-1.5 py-0.5 text-xs bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400 rounded">{{ '{{ ' + key + ' }}' }}</code>
                <span class="text-sm text-gray-600 dark:text-gray-400">{{ desc }}</span>
              </button>
            </div>
          </div>
        </div>

        <div class="h-6 w-px bg-gray-300 dark:bg-gray-600"></div>

        <!-- Content Blocks -->
        <div class="relative" ref="blocksDropdownRef">
          <button
            @click="showBlocksDropdown = !showBlocksDropdown"
            class="flex items-center gap-1 px-3 py-1.5 text-sm bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 rounded-lg hover:bg-green-200 dark:hover:bg-green-900/50"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
            </svg>
            Insert Block
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>
          <div
            v-if="showBlocksDropdown"
            class="absolute top-full left-0 mt-1 w-80 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg z-50"
          >
            <div class="p-2 grid grid-cols-2 gap-2">
              <button
                v-for="block in contentBlocks"
                :key="block.id"
                @click="insertBlock(block)"
                class="flex flex-col items-center gap-2 p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors"
              >
                <div class="w-8 h-8 flex items-center justify-center bg-gray-100 dark:bg-gray-700 rounded">
                  <component :is="block.icon" class="w-5 h-5 text-gray-600 dark:text-gray-400" />
                </div>
                <span class="text-xs font-medium text-gray-700 dark:text-gray-300">{{ block.label }}</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Quick Insert Buttons -->
        <div class="flex items-center gap-1">
          <button
            v-for="quick in quickInserts"
            :key="quick.id"
            @click="insertQuick(quick)"
            class="px-2 py-1 text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 rounded hover:bg-gray-200 dark:hover:bg-gray-600"
            :title="quick.description"
          >
            {{ quick.label }}
          </button>
        </div>
      </div>
    </div>

    <!-- Editor Area -->
    <div class="flex-1 flex min-h-0">
      <!-- Code Editor -->
      <div :class="['flex-1 flex flex-col min-w-0', showPreview ? 'w-1/2' : 'w-full']">
        <!-- Line Numbers + Editor -->
        <div class="flex-1 flex overflow-hidden">
          <!-- Line Numbers -->
          <div class="flex-none w-12 bg-gray-100 dark:bg-gray-900 text-right pr-2 pt-3 select-none overflow-hidden border-r border-gray-200 dark:border-gray-700">
            <div
              v-for="n in lineCount"
              :key="n"
              class="text-xs text-gray-400 dark:text-gray-600 leading-6 font-mono"
            >
              {{ n }}
            </div>
          </div>

          <!-- Code Editor Textarea -->
          <div class="flex-1 relative">
            <!-- Syntax Highlighted Overlay -->
            <div
              ref="highlightRef"
              class="absolute inset-0 p-3 font-mono text-sm leading-6 whitespace-pre-wrap break-words overflow-hidden pointer-events-none"
              v-html="highlightedCode"
            ></div>

            <!-- Textarea -->
            <textarea
              ref="editorRef"
              v-model="currentContent"
              @input="onInput"
              @keydown="onKeydown"
              @scroll="syncScroll"
              class="absolute inset-0 w-full h-full p-3 font-mono text-sm leading-6 bg-transparent text-transparent caret-gray-900 dark:caret-white resize-none outline-none"
              spellcheck="false"
              :placeholder="activeMode === 'html' ? 'Enter your HTML email template...' : 'Enter plain text version...'"
            ></textarea>
          </div>
        </div>

        <!-- Status Bar -->
        <div class="flex-none flex items-center justify-between px-4 py-1.5 bg-gray-100 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700 text-xs text-gray-500 dark:text-gray-400">
          <div class="flex items-center gap-4">
            <span>{{ activeMode === 'html' ? 'HTML' : 'Plain Text' }}</span>
            <span>Lines: {{ lineCount }}</span>
            <span>Characters: {{ currentContent.length }}</span>
          </div>
          <div class="flex items-center gap-2">
            <span v-if="hasUnsavedChanges" class="text-orange-500">Unsaved changes</span>
            <span v-else class="text-green-500">Saved</span>
          </div>
        </div>
      </div>

      <!-- Resizer -->
      <div
        v-if="showPreview"
        @mousedown="startResize"
        class="flex-none w-1 bg-gray-200 dark:bg-gray-700 cursor-col-resize hover:bg-blue-500 transition-colors"
      ></div>

      <!-- Preview Panel -->
      <div
        v-if="showPreview"
        :class="['flex-1 flex flex-col min-w-0 bg-gray-100 dark:bg-gray-900']"
        :style="{ width: previewWidth + 'px' }"
      >
        <div class="flex-none px-4 py-2 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
          <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Preview</span>
        </div>
        <div class="flex-1 overflow-auto p-4 flex justify-center">
          <div
            :class="[
              'bg-white shadow-lg transition-all duration-300',
              previewDevice === 'mobile' ? 'w-[375px]' : previewDevice === 'tablet' ? 'w-[768px]' : 'w-full max-w-[800px]'
            ]"
          >
            <iframe
              ref="previewRef"
              :srcdoc="renderedPreview"
              class="w-full h-full min-h-[600px] border-0"
              sandbox="allow-same-origin"
            ></iframe>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted, onUnmounted, h, nextTick } from 'vue';

const props = defineProps<{
  htmlContent: string;
  textContent: string;
  variables: Record<string, string>;
  templateSlug?: string;
}>();

const emit = defineEmits<{
  (e: 'update:htmlContent', value: string): void;
  (e: 'update:textContent', value: string): void;
}>();

// Editor state
const activeMode = ref<'html' | 'text'>('html');
const showPreview = ref(true);
const showVariablesDropdown = ref(false);
const showBlocksDropdown = ref(false);
const variableSearch = ref('');
const previewDevice = ref<'desktop' | 'tablet' | 'mobile'>('desktop');
const previewWidth = ref(500);
const hasUnsavedChanges = ref(false);

// Refs
const editorRef = ref<HTMLTextAreaElement | null>(null);
const highlightRef = ref<HTMLDivElement | null>(null);
const previewRef = ref<HTMLIFrameElement | null>(null);
const variablesDropdownRef = ref<HTMLDivElement | null>(null);
const blocksDropdownRef = ref<HTMLDivElement | null>(null);

// History for undo/redo
const htmlHistory = ref<string[]>([props.htmlContent]);
const textHistory = ref<string[]>([props.textContent]);
const htmlHistoryIndex = ref(0);
const textHistoryIndex = ref(0);

// Editor modes
const editorModes = [
  { id: 'html', label: 'HTML' },
  { id: 'text', label: 'Plain Text' },
];

// Preview devices
const DesktopIcon = () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
  h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z' })
]);
const TabletIcon = () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
  h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M12 18h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z' })
]);
const MobileIcon = () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
  h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z' })
]);

const previewDevices = [
  { id: 'desktop', label: 'Desktop', icon: DesktopIcon },
  { id: 'tablet', label: 'Tablet', icon: TabletIcon },
  { id: 'mobile', label: 'Mobile', icon: MobileIcon },
];

// Content block icons
const ButtonIcon = () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
  h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122' })
]);
const HeadingIcon = () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
  h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z' })
]);
const ImageIcon = () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
  h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z' })
]);
const DividerIcon = () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
  h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M4 12h16' })
]);
const SpacerIcon = () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
  h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5v-4m0 4h-4m4 0l-5-5' })
]);
const ColumnsIcon = () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
  h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2' })
]);
const FooterIcon = () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
  h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h14a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6z' })
]);
const SocialIcon = () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
  h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z' })
]);

// Content blocks
const contentBlocks = [
  {
    id: 'button',
    label: 'Button',
    icon: ButtonIcon,
    html: `<p style="text-align: center; margin: 30px 0;">
  <a href="{{ action_url }}" style="display: inline-block; padding: 12px 28px; background: #2563eb; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: 500;">Click Here</a>
</p>`,
    text: '{{ action_url }}'
  },
  {
    id: 'heading',
    label: 'Heading',
    icon: HeadingIcon,
    html: `<h2 style="margin: 0 0 16px 0; font-size: 20px; font-weight: 600; color: #1f2937;">Your Heading Here</h2>`,
    text: '## Your Heading Here'
  },
  {
    id: 'image',
    label: 'Image',
    icon: ImageIcon,
    html: `<p style="text-align: center; margin: 20px 0;">
  <img src="{{ image_url }}" alt="Image description" style="max-width: 100%; height: auto; border-radius: 8px;" />
</p>`,
    text: '[Image: {{ image_url }}]'
  },
  {
    id: 'divider',
    label: 'Divider',
    icon: DividerIcon,
    html: `<hr style="border: none; border-top: 1px solid #e5e7eb; margin: 24px 0;" />`,
    text: '\n---\n'
  },
  {
    id: 'spacer',
    label: 'Spacer',
    icon: SpacerIcon,
    html: `<div style="height: 32px;"></div>`,
    text: '\n\n'
  },
  {
    id: 'columns',
    label: '2 Columns',
    icon: ColumnsIcon,
    html: `<table width="100%" cellpadding="0" cellspacing="0" style="margin: 20px 0;">
  <tr>
    <td width="48%" valign="top" style="padding-right: 12px;">
      <p style="margin: 0; color: #4b5563;">Left column content</p>
    </td>
    <td width="4%"></td>
    <td width="48%" valign="top" style="padding-left: 12px;">
      <p style="margin: 0; color: #4b5563;">Right column content</p>
    </td>
  </tr>
</table>`,
    text: 'Left column | Right column'
  },
  {
    id: 'footer',
    label: 'Footer',
    icon: FooterIcon,
    html: `<div style="padding: 20px 24px; text-align: center; font-size: 13px; color: #6b7280; border-top: 1px solid #e5e7eb;">
  <p style="margin: 0;">&copy; {{ year }} {{ app_name }}. All rights reserved.</p>
  <p style="margin: 8px 0 0 0;"><a href="{{ unsubscribe_url }}" style="color: #2563eb; text-decoration: none;">Unsubscribe</a> | <a href="{{ preferences_url }}" style="color: #2563eb; text-decoration: none;">Email Preferences</a></p>
</div>`,
    text: `---
© {{ year }} {{ app_name }}. All rights reserved.
Unsubscribe: {{ unsubscribe_url }}`
  },
  {
    id: 'social',
    label: 'Social Links',
    icon: SocialIcon,
    html: `<p style="text-align: center; margin: 20px 0;">
  <a href="#" style="display: inline-block; margin: 0 8px; color: #6b7280; text-decoration: none;">Twitter</a>
  <a href="#" style="display: inline-block; margin: 0 8px; color: #6b7280; text-decoration: none;">Facebook</a>
  <a href="#" style="display: inline-block; margin: 0 8px; color: #6b7280; text-decoration: none;">LinkedIn</a>
</p>`,
    text: 'Twitter | Facebook | LinkedIn'
  },
];

// Quick inserts
const quickInserts = [
  { id: 'bold', label: '<b>', description: 'Bold text', html: '<strong></strong>', text: '**text**' },
  { id: 'italic', label: '<i>', description: 'Italic text', html: '<em></em>', text: '_text_' },
  { id: 'link', label: '<a>', description: 'Link', html: '<a href="" style="color: #2563eb; text-decoration: none;"></a>', text: '[text](url)' },
  { id: 'paragraph', label: '<p>', description: 'Paragraph', html: '<p style="margin: 0 0 16px 0; color: #4b5563; line-height: 1.6;"></p>', text: '\n\n' },
  { id: 'list', label: '<ul>', description: 'Unordered list', html: '<ul style="margin: 16px 0; padding-left: 24px;">\n  <li></li>\n</ul>', text: '- item' },
];

// Current content based on mode
const currentContent = computed({
  get: () => activeMode.value === 'html' ? props.htmlContent : props.textContent,
  set: (value: string) => {
    if (activeMode.value === 'html') {
      emit('update:htmlContent', value);
    } else {
      emit('update:textContent', value);
    }
    hasUnsavedChanges.value = true;
  }
});

// Line count
const lineCount = computed(() => {
  return currentContent.value.split('\n').length;
});

// Filter variables based on search
const filteredVariables = computed(() => {
  const search = variableSearch.value.toLowerCase();
  if (!search) return props.variables;

  return Object.fromEntries(
    Object.entries(props.variables).filter(
      ([key, desc]) =>
        key.toLowerCase().includes(search) ||
        desc.toLowerCase().includes(search)
    )
  );
});

// Undo/Redo state
const canUndo = computed(() => {
  const history = activeMode.value === 'html' ? htmlHistory.value : textHistory.value;
  const index = activeMode.value === 'html' ? htmlHistoryIndex.value : textHistoryIndex.value;
  return index > 0;
});

const canRedo = computed(() => {
  const history = activeMode.value === 'html' ? htmlHistory.value : textHistory.value;
  const index = activeMode.value === 'html' ? htmlHistoryIndex.value : textHistoryIndex.value;
  return index < history.length - 1;
});

// Syntax highlighting
const highlightedCode = computed(() => {
  const code = currentContent.value;
  if (activeMode.value === 'html') {
    return highlightHtml(code);
  }
  return escapeHtml(code);
});

function highlightHtml(code: string): string {
  // Escape HTML first
  let result = escapeHtml(code);

  // Highlight tags
  result = result.replace(/(&lt;\/?[a-zA-Z][a-zA-Z0-9]*)/g, '<span class="text-pink-600 dark:text-pink-400">$1</span>');
  result = result.replace(/(&gt;)/g, '<span class="text-pink-600 dark:text-pink-400">$1</span>');

  // Highlight attributes
  result = result.replace(/(\s)([a-zA-Z-]+)(=)/g, '$1<span class="text-purple-600 dark:text-purple-400">$2</span><span class="text-gray-500">=</span>');

  // Highlight attribute values
  result = result.replace(/(&quot;[^&]*&quot;)/g, '<span class="text-green-600 dark:text-green-400">$1</span>');

  // Highlight template variables
  result = result.replace(/(\{\{\s*[^}]+\s*\}\})/g, '<span class="text-orange-500 dark:text-orange-400 bg-orange-100 dark:bg-orange-900/30 rounded px-0.5">$1</span>');

  // Highlight comments
  result = result.replace(/(&lt;!--[\s\S]*?--&gt;)/g, '<span class="text-gray-400 dark:text-gray-500 italic">$1</span>');

  return result;
}

function escapeHtml(text: string): string {
  return text
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

// Rendered preview with variable substitution
const renderedPreview = computed(() => {
  let html = props.htmlContent;

  // Replace variables with sample data
  const sampleData: Record<string, string> = {
    app_name: 'OsintWeb',
    app_url: window.location.origin,
    year: new Date().getFullYear().toString(),
    user_name: 'John Doe',
    user_email: 'john@example.com',
    user_first_name: 'John',
    unsubscribe_url: '#',
    preferences_url: '#',
    verification_url: '#',
    reset_url: '#',
    action_url: '#',
    view_url: '#',
    security_url: '#',
    download_url: '#',
    cancel_url: '#',
    support_email: 'support@example.com',
    image_url: 'https://via.placeholder.com/600x200',
    ...Object.fromEntries(Object.keys(props.variables).map(k => [k, `[${k}]`]))
  };

  for (const [key, value] of Object.entries(sampleData)) {
    html = html.replace(new RegExp(`\\{\\{\\s*${key}\\s*\\}\\}`, 'g'), value);
  }

  return html;
});

// Insert variable at cursor
function insertVariable(key: string) {
  const variable = `{{ ${key} }}`;
  insertAtCursor(variable);
  showVariablesDropdown.value = false;
  variableSearch.value = '';
}

// Insert content block
function insertBlock(block: { id: string; html: string; text: string }) {
  const content = activeMode.value === 'html' ? block.html : block.text;
  insertAtCursor('\n' + content + '\n');
  showBlocksDropdown.value = false;
}

// Insert quick element
function insertQuick(quick: { html: string; text: string }) {
  const content = activeMode.value === 'html' ? quick.html : quick.text;
  insertAtCursor(content);
}

// Insert text at cursor position
function insertAtCursor(text: string) {
  const textarea = editorRef.value;
  if (!textarea) return;

  const start = textarea.selectionStart;
  const end = textarea.selectionEnd;
  const before = currentContent.value.substring(0, start);
  const after = currentContent.value.substring(end);

  currentContent.value = before + text + after;

  nextTick(() => {
    textarea.focus();
    textarea.selectionStart = textarea.selectionEnd = start + text.length;
  });

  addToHistory();
}

// Undo
function undo() {
  if (!canUndo.value) return;

  if (activeMode.value === 'html') {
    htmlHistoryIndex.value--;
    emit('update:htmlContent', htmlHistory.value[htmlHistoryIndex.value]);
  } else {
    textHistoryIndex.value--;
    emit('update:textContent', textHistory.value[textHistoryIndex.value]);
  }
}

// Redo
function redo() {
  if (!canRedo.value) return;

  if (activeMode.value === 'html') {
    htmlHistoryIndex.value++;
    emit('update:htmlContent', htmlHistory.value[htmlHistoryIndex.value]);
  } else {
    textHistoryIndex.value++;
    emit('update:textContent', textHistory.value[textHistoryIndex.value]);
  }
}

// Add to history
let historyTimeout: number | null = null;
function addToHistory() {
  if (historyTimeout) clearTimeout(historyTimeout);
  historyTimeout = setTimeout(() => {
    if (activeMode.value === 'html') {
      // Remove any future history if we're not at the end
      htmlHistory.value = htmlHistory.value.slice(0, htmlHistoryIndex.value + 1);
      htmlHistory.value.push(props.htmlContent);
      htmlHistoryIndex.value = htmlHistory.value.length - 1;
      // Keep only last 50 states
      if (htmlHistory.value.length > 50) {
        htmlHistory.value.shift();
        htmlHistoryIndex.value--;
      }
    } else {
      textHistory.value = textHistory.value.slice(0, textHistoryIndex.value + 1);
      textHistory.value.push(props.textContent);
      textHistoryIndex.value = textHistory.value.length - 1;
      if (textHistory.value.length > 50) {
        textHistory.value.shift();
        textHistoryIndex.value--;
      }
    }
  }, 500);
}

// Format code
function formatCode() {
  if (activeMode.value !== 'html') return;

  let html = currentContent.value;

  // Simple formatting: add newlines after certain tags
  html = html.replace(/>\s*</g, '>\n<');
  html = html.replace(/(<\/(div|p|tr|td|th|li|ul|ol|table|head|body|html|header|footer|section|article|nav|aside|main|h[1-6])>)/gi, '$1\n');

  // Indent nested elements
  const lines = html.split('\n');
  let indent = 0;
  const formatted = lines.map(line => {
    const trimmed = line.trim();
    if (!trimmed) return '';

    // Decrease indent for closing tags
    if (/^<\//.test(trimmed)) {
      indent = Math.max(0, indent - 1);
    }

    const result = '  '.repeat(indent) + trimmed;

    // Increase indent for opening tags (that aren't self-closing)
    if (/^<[a-zA-Z]/.test(trimmed) && !/<\/[^>]+>$/.test(trimmed) && !/\/>$/.test(trimmed)) {
      indent++;
    }

    return result;
  }).filter(Boolean).join('\n');

  currentContent.value = formatted;
  addToHistory();
}

// Handle input
function onInput() {
  addToHistory();
}

// Handle keyboard shortcuts
function onKeydown(e: KeyboardEvent) {
  if (e.ctrlKey || e.metaKey) {
    if (e.key === 'z') {
      e.preventDefault();
      if (e.shiftKey) {
        redo();
      } else {
        undo();
      }
    } else if (e.key === 'y') {
      e.preventDefault();
      redo();
    }
  }

  // Tab handling
  if (e.key === 'Tab') {
    e.preventDefault();
    insertAtCursor('  ');
  }
}

// Sync scroll between textarea and highlight overlay
function syncScroll() {
  if (editorRef.value && highlightRef.value) {
    highlightRef.value.scrollTop = editorRef.value.scrollTop;
    highlightRef.value.scrollLeft = editorRef.value.scrollLeft;
  }
}

// Resize preview panel
let isResizing = false;
let startX = 0;
let startWidth = 0;

function startResize(e: MouseEvent) {
  isResizing = true;
  startX = e.clientX;
  startWidth = previewWidth.value;
  document.addEventListener('mousemove', onResize);
  document.addEventListener('mouseup', stopResize);
}

function onResize(e: MouseEvent) {
  if (!isResizing) return;
  const diff = startX - e.clientX;
  previewWidth.value = Math.max(300, Math.min(800, startWidth + diff));
}

function stopResize() {
  isResizing = false;
  document.removeEventListener('mousemove', onResize);
  document.removeEventListener('mouseup', stopResize);
}

// Close dropdowns when clicking outside
function handleClickOutside(e: MouseEvent) {
  if (variablesDropdownRef.value && !variablesDropdownRef.value.contains(e.target as Node)) {
    showVariablesDropdown.value = false;
  }
  if (blocksDropdownRef.value && !blocksDropdownRef.value.contains(e.target as Node)) {
    showBlocksDropdown.value = false;
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});

// Watch for prop changes
watch(() => props.htmlContent, (newVal) => {
  if (htmlHistory.value[htmlHistoryIndex.value] !== newVal) {
    htmlHistory.value = [newVal];
    htmlHistoryIndex.value = 0;
  }
  hasUnsavedChanges.value = false;
});

watch(() => props.textContent, (newVal) => {
  if (textHistory.value[textHistoryIndex.value] !== newVal) {
    textHistory.value = [newVal];
    textHistoryIndex.value = 0;
  }
  hasUnsavedChanges.value = false;
});
</script>

<style scoped>
.email-editor {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

textarea::selection {
  background: rgba(59, 130, 246, 0.3);
}
</style>
