<template>
  <div :class="['comment-item', depth > 0 ? 'ml-8 border-l-2 border-gray-200 dark:border-gray-700 pl-4' : '']">
    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm border border-gray-200 dark:border-gray-700">
      <!-- Comment Header -->
      <div class="flex items-start justify-between">
        <div class="flex items-center space-x-3">
          <!-- Avatar -->
          <div
            v-if="comment.author?.avatar_url"
            class="w-8 h-8 rounded-full bg-cover bg-center"
            :style="{ backgroundImage: `url(${comment.author.avatar_url})` }"
          ></div>
          <div
            v-else
            class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white text-xs font-bold"
          >
            {{ authorInitials }}
          </div>

          <!-- Author info -->
          <div>
            <span class="font-medium text-gray-900 dark:text-white">
              {{ comment.author?.name || comment.guest_name || 'Anonymous' }}
            </span>
            <span class="text-xs text-gray-500 dark:text-gray-400 ml-2">
              {{ formatDate(comment.created_at) }}
              <span v-if="comment.is_edited" class="text-gray-400">(edited)</span>
            </span>
          </div>
        </div>

        <!-- Actions menu -->
        <div v-if="canModify" class="relative">
          <button
            @click="showMenu = !showMenu"
            class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
            </svg>
          </button>
          <div
            v-if="showMenu"
            class="absolute right-0 mt-1 w-32 bg-white dark:bg-gray-700 rounded-lg shadow-lg border border-gray-200 dark:border-gray-600 py-1 z-10"
          >
            <button
              @click="startEdit"
              class="w-full px-4 py-2 text-left text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600"
            >
              Edit
            </button>
            <button
              @click="$emit('delete', comment.id)"
              class="w-full px-4 py-2 text-left text-sm text-red-600 dark:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-600"
            >
              Delete
            </button>
          </div>
        </div>
      </div>

      <!-- Comment Content -->
      <div v-if="!editing" class="mt-3 text-gray-700 dark:text-gray-300 text-sm whitespace-pre-wrap">{{ comment.content }}</div>

      <!-- Edit form -->
      <div v-else class="mt-3">
        <textarea
          v-model="editContent"
          rows="3"
          class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
        ></textarea>
        <div class="flex justify-end space-x-2 mt-2">
          <button
            @click="cancelEdit"
            class="px-3 py-1 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white"
          >
            Cancel
          </button>
          <button
            @click="saveEdit"
            class="px-3 py-1 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700"
          >
            Save
          </button>
        </div>
      </div>

      <!-- Comment Actions -->
      <div class="mt-3 flex items-center space-x-4">
        <!-- Voting -->
        <div class="flex items-center space-x-1">
          <button
            @click="$emit('vote', comment.id, 'up')"
            :class="[
              'p-1 rounded transition-colors',
              comment.user_vote === 'up'
                ? 'text-green-600 dark:text-green-400'
                : 'text-gray-400 hover:text-green-600 dark:hover:text-green-400'
            ]"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
            </svg>
          </button>
          <span class="text-sm font-medium text-gray-600 dark:text-gray-400">
            {{ comment.upvotes - comment.downvotes }}
          </span>
          <button
            @click="$emit('vote', comment.id, 'down')"
            :class="[
              'p-1 rounded transition-colors',
              comment.user_vote === 'down'
                ? 'text-red-600 dark:text-red-400'
                : 'text-gray-400 hover:text-red-600 dark:hover:text-red-400'
            ]"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>
        </div>

        <!-- Reply button -->
        <button
          v-if="depth < maxDepth"
          @click="showReplyForm = !showReplyForm"
          class="text-sm text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
        >
          Reply
        </button>
      </div>

      <!-- Reply form -->
      <div v-if="showReplyForm" class="mt-3">
        <textarea
          v-model="replyContent"
          rows="2"
          class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
          placeholder="Write a reply..."
        ></textarea>
        <div class="flex justify-end space-x-2 mt-2">
          <button
            @click="showReplyForm = false; replyContent = ''"
            class="px-3 py-1 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white"
          >
            Cancel
          </button>
          <button
            @click="submitReply"
            :disabled="!replyContent.trim()"
            class="px-3 py-1 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
          >
            Reply
          </button>
        </div>
      </div>
    </div>

    <!-- Nested replies -->
    <div v-if="replies.length > 0" class="mt-3 space-y-3">
      <CommentItem
        v-for="reply in replies"
        :key="reply.id"
        :comment="reply"
        :all-comments="allComments"
        :current-user-id="currentUserId"
        :depth="depth + 1"
        @reply="(id, content) => $emit('reply', id, content)"
        @vote="(id, type) => $emit('vote', id, type)"
        @edit="(id, content) => $emit('edit', id, content)"
        @delete="(id) => $emit('delete', id)"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, defineAsyncComponent } from 'vue';

// Recursive component
const CommentItem = defineAsyncComponent(() => import('./CommentItem.vue'));

interface Comment {
  id: number;
  uuid: string;
  content: string;
  content_html: string;
  author: {
    id: number;
    name: string;
    avatar_url: string | null;
  } | null;
  guest_name: string | null;
  parent_id: number | null;
  depth: number;
  upvotes: number;
  downvotes: number;
  user_vote: 'up' | 'down' | null;
  is_edited: boolean;
  created_at: string;
  updated_at: string;
}

const props = defineProps<{
  comment: Comment;
  allComments: Comment[];
  currentUserId: number | null;
  depth: number;
}>();

const emit = defineEmits<{
  (e: 'reply', commentId: number, content: string): void;
  (e: 'vote', commentId: number, type: 'up' | 'down'): void;
  (e: 'edit', commentId: number, content: string): void;
  (e: 'delete', commentId: number): void;
}>();

const maxDepth = 5;
const showMenu = ref(false);
const editing = ref(false);
const editContent = ref('');
const showReplyForm = ref(false);
const replyContent = ref('');

const canModify = computed(() => {
  return props.currentUserId && props.comment.author?.id === props.currentUserId;
});

const replies = computed(() => {
  return props.allComments.filter(c => c.parent_id === props.comment.id);
});

const authorInitials = computed(() => {
  const name = props.comment.author?.name || props.comment.guest_name || 'A';
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
});

const formatDate = (dateString: string) => {
  const date = new Date(dateString);
  const now = new Date();
  const diff = now.getTime() - date.getTime();
  const minutes = Math.floor(diff / 60000);
  const hours = Math.floor(diff / 3600000);
  const days = Math.floor(diff / 86400000);

  if (minutes < 1) return 'just now';
  if (minutes < 60) return `${minutes}m ago`;
  if (hours < 24) return `${hours}h ago`;
  if (days < 7) return `${days}d ago`;

  return date.toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: date.getFullYear() !== now.getFullYear() ? 'numeric' : undefined
  });
};

const startEdit = () => {
  editContent.value = props.comment.content;
  editing.value = true;
  showMenu.value = false;
};

const cancelEdit = () => {
  editing.value = false;
  editContent.value = '';
};

const saveEdit = () => {
  if (editContent.value.trim()) {
    emit('edit', props.comment.id, editContent.value);
    editing.value = false;
  }
};

const submitReply = () => {
  if (replyContent.value.trim()) {
    emit('reply', props.comment.id, replyContent.value);
    showReplyForm.value = false;
    replyContent.value = '';
  }
};
</script>
