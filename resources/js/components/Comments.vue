<template>
  <div class="comments-section">
    <div class="flex items-center justify-between mb-6">
      <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
        Comments ({{ comments.length }})
      </h3>
    </div>

    <!-- Comment Form (only for authenticated users) -->
    <div v-if="isAuthenticated" class="mb-6">
      <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
        <textarea
          v-model="newComment"
          rows="3"
          class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
          placeholder="Write a comment..."
          :disabled="submitting"
        ></textarea>
        <div class="flex items-center justify-between mt-3">
          <p class="text-xs text-gray-500 dark:text-gray-400">
            Supports basic formatting: **bold**, *italic*, `code`
          </p>
          <button
            @click="submitComment"
            :disabled="!newComment.trim() || submitting"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
          >
            {{ submitting ? 'Posting...' : 'Post Comment' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Login prompt for guests -->
    <div v-else class="mb-6 bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 text-center">
      <p class="text-gray-600 dark:text-gray-400 mb-2">You must be logged in to comment.</p>
      <router-link
        to="/login"
        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors"
      >
        Login to Comment
      </router-link>
    </div>

    <!-- Empty state -->
    <div v-if="comments.length === 0 && !loading" class="text-center py-8">
      <svg class="w-12 h-12 mx-auto text-gray-400 dark:text-gray-500 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
      </svg>
      <p class="text-gray-500 dark:text-gray-400">No comments yet. Be the first to comment!</p>
    </div>

    <!-- Loading state -->
    <div v-if="loading" class="flex justify-center py-8">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
    </div>

    <!-- Comments list -->
    <div v-else class="space-y-4">
      <CommentItem
        v-for="comment in topLevelComments"
        :key="comment.id"
        :comment="comment"
        :all-comments="comments"
        :current-user-id="currentUserId"
        :depth="0"
        @reply="handleReply"
        @vote="handleVote"
        @edit="handleEdit"
        @delete="handleDelete"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, defineAsyncComponent } from 'vue';
import { useAuthStore } from '@/stores/auth';

// Async component for recursive rendering
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
  commentableType: string;
  commentableId: number | string;
}>();

// Props will be used when API integration is complete
void props; // Prevent unused variable warning

const authStore = useAuthStore();

const comments = ref<Comment[]>([]);
const newComment = ref('');
const loading = ref(false);
const submitting = ref(false);
const replyingTo = ref<number | null>(null);

const isAuthenticated = computed(() => authStore.isAuthenticated);
const currentUserId = computed(() => authStore.user?.id || null);

const topLevelComments = computed(() => {
  return comments.value.filter(c => c.parent_id === null);
});

const loadComments = async () => {
  loading.value = true;
  try {
    // TODO: Replace with actual API call
    // const response = await api.get(`/${props.commentableType}/${props.commentableId}/comments`);
    // comments.value = response.data.data;

    // Mock data for demonstration
    comments.value = [];
  } catch (error) {
    console.error('Failed to load comments:', error);
  } finally {
    loading.value = false;
  }
};

const submitComment = async () => {
  if (!newComment.value.trim() || submitting.value) return;

  submitting.value = true;
  try {
    // TODO: Replace with actual API call
    // const response = await api.post(`/${props.commentableType}/${props.commentableId}/comments`, {
    //   content: newComment.value,
    //   parent_id: replyingTo.value
    // });

    // Mock: Add comment to list
    const mockComment: Comment = {
      id: Date.now(),
      uuid: `comment-${Date.now()}`,
      content: newComment.value,
      content_html: newComment.value,
      author: authStore.user ? {
        id: authStore.user.id,
        name: authStore.user.name,
        avatar_url: authStore.user.avatar_url || null
      } : null,
      guest_name: null,
      parent_id: replyingTo.value,
      depth: replyingTo.value ? 1 : 0,
      upvotes: 0,
      downvotes: 0,
      user_vote: null,
      is_edited: false,
      created_at: new Date().toISOString(),
      updated_at: new Date().toISOString()
    };

    comments.value.push(mockComment);
    newComment.value = '';
    replyingTo.value = null;
  } catch (error) {
    console.error('Failed to submit comment:', error);
  } finally {
    submitting.value = false;
  }
};

const handleReply = (commentId: number, replyContent: string) => {
  // Handle reply submission
  replyingTo.value = commentId;
  newComment.value = replyContent;
  submitComment();
};

const handleVote = async (commentId: number, voteType: 'up' | 'down') => {
  try {
    // TODO: Replace with actual API call
    // await api.post(`/comments/${commentId}/vote`, { type: voteType });

    const comment = comments.value.find(c => c.id === commentId);
    if (comment) {
      if (comment.user_vote === voteType) {
        // Remove vote
        if (voteType === 'up') comment.upvotes--;
        else comment.downvotes--;
        comment.user_vote = null;
      } else {
        // Change or add vote
        if (comment.user_vote === 'up') comment.upvotes--;
        if (comment.user_vote === 'down') comment.downvotes--;
        if (voteType === 'up') comment.upvotes++;
        else comment.downvotes++;
        comment.user_vote = voteType;
      }
    }
  } catch (error) {
    console.error('Failed to vote:', error);
  }
};

const handleEdit = async (commentId: number, newContent: string) => {
  try {
    // TODO: Replace with actual API call
    // await api.patch(`/comments/${commentId}`, { content: newContent });

    const comment = comments.value.find(c => c.id === commentId);
    if (comment) {
      comment.content = newContent;
      comment.content_html = newContent;
      comment.is_edited = true;
      comment.updated_at = new Date().toISOString();
    }
  } catch (error) {
    console.error('Failed to edit comment:', error);
  }
};

const handleDelete = async (commentId: number) => {
  if (!confirm('Are you sure you want to delete this comment?')) return;

  try {
    // TODO: Replace with actual API call
    // await api.delete(`/comments/${commentId}`);

    comments.value = comments.value.filter(c => c.id !== commentId);
  } catch (error) {
    console.error('Failed to delete comment:', error);
  }
};

onMounted(() => {
  loadComments();
});
</script>
