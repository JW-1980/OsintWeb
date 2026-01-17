<template>
  <div class="comments-section">
    <div class="flex items-center justify-between mb-6">
      <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
        Comments ({{ comments.length }})
      </h3>
    </div>

    <!-- Error message -->
    <div v-if="error" class="mb-4 p-3 bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-200 dark:border-yellow-800 rounded-lg">
      <p class="text-sm text-yellow-700 dark:text-yellow-300">{{ error }}</p>
    </div>

    <!-- Comment Form (only for authenticated users) -->
    <div v-if="isAuthenticated && canComment" class="mb-6">
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
            @click="submitComment()"
            :disabled="!newComment.trim() || submitting"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
          >
            {{ submitting ? 'Posting...' : 'Post Comment' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Login prompt for guests -->
    <div v-else-if="canComment" class="mb-6 bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 text-center">
      <p class="text-gray-600 dark:text-gray-400 mb-2">You must be logged in to comment.</p>
      <router-link
        to="/login"
        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors"
      >
        Login to Comment
      </router-link>
    </div>

    <!-- Comments disabled message -->
    <div v-else-if="!canComment" class="mb-6 bg-gray-100 dark:bg-gray-700/50 rounded-lg p-4 text-center">
      <p class="text-gray-500 dark:text-gray-400">Comments are disabled for this content.</p>
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
import { useApi } from '@/composables/useApi';

// Async component for recursive rendering
const CommentItem = defineAsyncComponent(() => import('./CommentItem.vue'));

interface CommentAuthor {
  id: number;
  name: string;
  avatar_url: string | null;
}

interface ApiComment {
  id: number;
  uuid: string;
  content: string;
  content_html?: string;
  user?: CommentAuthor | null;
  guest_name: string | null;
  parent_id: number | null;
  upvotes: number;
  downvotes: number;
  edit_count?: number;
  created_at: string;
  updated_at: string;
  approved_replies?: ApiComment[];
}

interface Comment {
  id: number;
  uuid: string;
  content: string;
  content_html: string;
  author: CommentAuthor | null;
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

interface CommentsResponse {
  data: ApiComment[];
  meta: {
    current_page: number;
    last_page: number;
    total: number;
    user_votes: Record<number, number>;
    can_comment: boolean;
    form_token?: string;
  };
}

interface CommentResponse {
  message: string;
  data: ApiComment;
  meta?: {
    status?: string;
    edit_time_remaining?: number;
  };
}

const props = defineProps<{
  commentableType: string;
  commentableId: number | string;
}>();

const api = useApi();
const authStore = useAuthStore();

const comments = ref<Comment[]>([]);
const newComment = ref('');
const loading = ref(false);
const submitting = ref(false);
const replyingTo = ref<number | null>(null);
const canComment = ref(true);
const formToken = ref<string | null>(null);
const userVotes = ref<Record<number, number>>({});
const error = ref('');

const isAuthenticated = computed(() => authStore.isAuthenticated);
const currentUserId = computed(() => authStore.user?.id || null);

const topLevelComments = computed(() => {
  return comments.value.filter(c => c.parent_id === null);
});

// Convert API vote (-1, 0, 1) to component vote format ('up', 'down', null)
const apiVoteToComponentVote = (vote: number): 'up' | 'down' | null => {
  if (vote === 1) return 'up';
  if (vote === -1) return 'down';
  return null;
};

// Convert component vote format to API vote (-1, 0, 1)
const componentVoteToApiVote = (voteType: 'up' | 'down', currentVote: 'up' | 'down' | null): number => {
  // If clicking same vote, remove it
  if (currentVote === voteType) return 0;
  // Otherwise set the vote
  return voteType === 'up' ? 1 : -1;
};

// Transform API comment to display comment
const transformComment = (apiComment: ApiComment, depth: number): Comment => ({
  id: apiComment.id,
  uuid: apiComment.uuid,
  content: apiComment.content,
  content_html: apiComment.content_html || apiComment.content,
  author: apiComment.user ? {
    id: apiComment.user.id,
    name: apiComment.user.name,
    avatar_url: apiComment.user.avatar_url || null
  } : null,
  guest_name: apiComment.guest_name,
  parent_id: apiComment.parent_id,
  depth,
  upvotes: apiComment.upvotes,
  downvotes: apiComment.downvotes,
  user_vote: apiVoteToComponentVote(userVotes.value[apiComment.id] || 0),
  is_edited: (apiComment.edit_count || 0) > 0,
  created_at: apiComment.created_at,
  updated_at: apiComment.updated_at,
});

// Flatten nested comments for easier handling
const flattenComments = (commentList: ApiComment[], depth = 0): Comment[] => {
  const result: Comment[] = [];
  for (const comment of commentList) {
    result.push(transformComment(comment, depth));
    if (comment.approved_replies && comment.approved_replies.length > 0) {
      result.push(...flattenComments(comment.approved_replies, depth + 1));
    }
  }
  return result;
};

const loadComments = async () => {
  loading.value = true;
  error.value = '';
  try {
    const response = await api.get<CommentsResponse>(
      `/${props.commentableType}/${props.commentableId}/comments`
    );

    // Store user votes mapping
    userVotes.value = response.meta.user_votes || {};
    canComment.value = response.meta.can_comment;
    formToken.value = response.meta.form_token || null;

    // Flatten nested comments for display
    comments.value = flattenComments(response.data);
  } catch (err) {
    console.error('Failed to load comments:', err);
    comments.value = [];
  } finally {
    loading.value = false;
  }
};

const submitComment = async (parentId: number | null = null) => {
  const content = newComment.value.trim();
  if (!content || submitting.value) return;

  submitting.value = true;
  error.value = '';

  try {
    const payload: Record<string, unknown> = {
      content,
      parent_id: parentId || replyingTo.value,
    };

    if (formToken.value) {
      payload._form_token = formToken.value;
    }

    const response = await api.post<CommentResponse>(
      `/${props.commentableType}/${props.commentableId}/comments`,
      payload
    );

    // Transform and add the new comment to the list
    const depth = (parentId || replyingTo.value) ? 1 : 0;
    const newCommentData = transformComment(response.data, depth);
    newCommentData.user_vote = null;
    newCommentData.is_edited = false;

    comments.value.push(newCommentData);
    newComment.value = '';
    replyingTo.value = null;

    // Update form token if provided
    if (response.meta?.status === 'pending') {
      error.value = 'Your comment is pending approval.';
    }
  } catch (err: unknown) {
    console.error('Failed to submit comment:', err);
    const apiError = err as { message?: string };
    error.value = apiError?.message || 'Failed to post comment. Please try again.';
  } finally {
    submitting.value = false;
  }
};

const handleReply = async (commentId: number, replyContent: string) => {
  // Set reply context and submit
  replyingTo.value = commentId;
  newComment.value = replyContent;
  await submitComment(commentId);
};

const handleVote = async (commentId: number, voteType: 'up' | 'down') => {
  if (!isAuthenticated.value) return;

  const comment = comments.value.find(c => c.id === commentId);
  if (!comment) return;

  const apiVote = componentVoteToApiVote(voteType, comment.user_vote);

  // Optimistic update
  const previousVote = comment.user_vote;
  const previousUpvotes = comment.upvotes;
  const previousDownvotes = comment.downvotes;

  // Update locally first
  if (previousVote === 'up') comment.upvotes--;
  if (previousVote === 'down') comment.downvotes--;

  if (apiVote === 1) {
    comment.upvotes++;
    comment.user_vote = 'up';
  } else if (apiVote === -1) {
    comment.downvotes++;
    comment.user_vote = 'down';
  } else {
    comment.user_vote = null;
  }

  try {
    await api.post(`/comments/${comment.uuid}/vote`, { vote: apiVote });
    userVotes.value[comment.id] = apiVote;
  } catch (err) {
    console.error('Failed to vote:', err);
    // Revert on error
    comment.user_vote = previousVote;
    comment.upvotes = previousUpvotes;
    comment.downvotes = previousDownvotes;
  }
};

const handleEdit = async (commentId: number, newContent: string) => {
  const comment = comments.value.find(c => c.id === commentId);
  if (!comment) return;

  const previousContent = comment.content;

  // Optimistic update
  comment.content = newContent;
  comment.content_html = newContent;
  comment.is_edited = true;

  try {
    await api.put(`/comments/${comment.uuid}`, { content: newContent });
    comment.updated_at = new Date().toISOString();
  } catch (err) {
    console.error('Failed to edit comment:', err);
    // Revert on error
    comment.content = previousContent;
    comment.content_html = previousContent;
  }
};

const handleDelete = async (commentId: number) => {
  if (!confirm('Are you sure you want to delete this comment?')) return;

  const comment = comments.value.find(c => c.id === commentId);
  if (!comment) return;

  try {
    await api.delete(`/comments/${comment.uuid}`);
    comments.value = comments.value.filter(c => c.id !== commentId);
  } catch (err) {
    console.error('Failed to delete comment:', err);
    error.value = 'Failed to delete comment. Please try again.';
  }
};

onMounted(() => {
  loadComments();
});
</script>
