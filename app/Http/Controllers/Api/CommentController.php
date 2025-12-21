<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Comment;
use App\Models\CommentReport;
use App\Models\CommentRevision;
use App\Models\CommentVote;
use App\Models\Setting;
use App\Services\AntiSpamService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CommentController extends Controller
{
    /**
     * Get comments for an article (threaded).
     */
    public function index(Request $request, string $articleUuid): JsonResponse
    {
        $article = Article::where('uuid', $articleUuid)->firstOrFail();

        $query = $article->comments()
            ->with(['user:id,name', 'approvedReplies.user:id,name'])
            ->approved()
            ->topLevel();

        // Sorting
        $sort = $request->input('sort', 'newest');
        switch ($sort) {
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'popular':
                $query->orderByRaw('(upvotes - downvotes) DESC');
                break;
            case 'newest':
            default:
                $query->orderBy('is_pinned', 'desc')
                    ->orderBy('created_at', 'desc');
                break;
        }

        $perPage = min((int) $request->input('per_page', 20), 50);
        $comments = $query->paginate($perPage);

        // Get current user's votes
        $userVotes = [];
        if ($user = auth()->user()) {
            $commentIds = collect($comments->items())->pluck('id')->toArray();
            $userVotes = CommentVote::where('user_id', $user->id)
                ->whereIn('comment_id', $commentIds)
                ->pluck('vote', 'comment_id')
                ->toArray();
        }

        return response()->json([
            'data' => $comments->items(),
            'meta' => [
                'current_page' => $comments->currentPage(),
                'last_page' => $comments->lastPage(),
                'total' => $comments->total(),
                'user_votes' => $userVotes,
                'can_comment' => $article->canComment(),
                'form_token' => AntiSpamService::generateFormToken(),
            ],
        ]);
    }

    /**
     * Store a new comment.
     */
    public function store(Request $request, string $articleUuid): JsonResponse
    {
        $article = Article::where('uuid', $articleUuid)->firstOrFail();

        // Check if comments are allowed
        if (!$article->canComment()) {
            return response()->json([
                'message' => 'Comments are not allowed on this article',
            ], 403);
        }

        $user = auth()->user();
        $allowGuests = Setting::get('comment_allow_guests', false);

        // Check authentication
        if (!$user && !$allowGuests) {
            return response()->json([
                'message' => 'You must be logged in to comment',
            ], 401);
        }

        // Validate input
        $minLength = (int) Setting::get('comment_min_length', 3);
        $maxLength = (int) Setting::get('comment_max_length', 5000);
        $maxDepth = (int) Setting::get('comment_max_depth', 5);

        $rules = [
            'content' => "required|string|min:{$minLength}|max:{$maxLength}",
            'parent_id' => 'nullable|exists:comments,id',
            '_form_token' => 'nullable|string',
            '_fingerprint' => 'nullable|string',
        ];

        if (!$user) {
            $rules['guest_name'] = 'required|string|max:50';
            $rules['guest_email'] = 'nullable|email|max:100';
        }

        $validated = $request->validate($rules);

        // Check parent depth
        if (!empty($validated['parent_id'])) {
            $parent = Comment::find($validated['parent_id']);
            if ($parent && $parent->depth >= $maxDepth) {
                return response()->json([
                    'message' => "Maximum reply depth ({$maxDepth}) reached",
                ], 422);
            }
        }

        // Anti-spam checks
        $antiSpam = new AntiSpamService($request);
        $spamCheck = $antiSpam->check($validated['content']);

        if (!$spamCheck['passed']) {
            return response()->json([
                'message' => $spamCheck['errors'][0] ?? 'Comment rejected',
                'errors' => $spamCheck['errors'],
            ], 422);
        }

        // Determine initial status
        $requireApproval = Setting::get('comment_require_approval', false);
        $autoApproveThreshold = (float) Setting::get('comment_auto_approve_threshold', 3.0);

        $status = 'approved';
        if ($requireApproval) {
            $status = 'pending';
        } elseif ($spamCheck['spam_score'] >= $autoApproveThreshold) {
            $status = 'pending'; // High spam score = needs review
        }

        // Check if this is the article author replying
        $isAuthorReply = $user && $user->id === $article->author_id;

        // Create the comment
        $comment = Comment::create([
            'uuid' => Str::uuid(),
            'commentable_type' => Article::class,
            'commentable_id' => $article->id,
            'user_id' => $user?->id,
            'guest_name' => $validated['guest_name'] ?? null,
            'guest_email' => $validated['guest_email'] ?? null,
            'parent_id' => $validated['parent_id'] ?? null,
            'content' => $validated['content'],
            'status' => $status,
            'ip_address' => $request->ip(),
            'user_agent' => substr($request->userAgent() ?? '', 0, 500),
            'spam_score' => $spamCheck['spam_score'],
            'spam_flags' => $spamCheck['flags'],
            'is_author_reply' => $isAuthorReply,
        ]);

        // Record rate limit
        $antiSpam->recordComment();

        $comment->load('user:id,name');

        $message = $status === 'pending'
            ? 'Comment submitted and pending approval'
            : 'Comment posted successfully';

        return response()->json([
            'message' => $message,
            'data' => $comment,
            'meta' => [
                'status' => $status,
                'edit_time_remaining' => $comment->getEditTimeRemaining(),
            ],
        ], 201);
    }

    /**
     * Update a comment (within edit window).
     */
    public function update(Request $request, string $uuid): JsonResponse
    {
        $comment = Comment::where('uuid', $uuid)->firstOrFail();
        $user = auth()->user();

        // Check if can edit
        if (!$comment->canEdit($user)) {
            $remaining = $comment->getEditTimeRemaining();
            if ($remaining === 0) {
                return response()->json([
                    'message' => 'Edit window has expired',
                ], 403);
            }
            return response()->json([
                'message' => 'You cannot edit this comment',
            ], 403);
        }

        $maxLength = (int) Setting::get('comment_max_length', 5000);

        $validated = $request->validate([
            'content' => "required|string|min:3|max:{$maxLength}",
            'edit_reason' => 'nullable|string|max:255',
        ]);

        // Anti-spam check on edited content
        $antiSpam = new AntiSpamService($request);
        $spamCheck = $antiSpam->check($validated['content']);

        if ($spamCheck['spam_score'] >= 5.0) {
            return response()->json([
                'message' => 'Edit rejected due to spam detection',
            ], 422);
        }

        // Edit the comment (creates revision)
        $comment->editContent(
            $validated['content'],
            $validated['edit_reason'] ?? null,
            $request->ip()
        );

        return response()->json([
            'message' => 'Comment updated successfully',
            'data' => $comment->fresh('user:id,name'),
            'meta' => [
                'edit_count' => $comment->edit_count,
                'edit_time_remaining' => $comment->getEditTimeRemaining(),
            ],
        ]);
    }

    /**
     * Delete a comment.
     */
    public function destroy(string $uuid): JsonResponse
    {
        $comment = Comment::where('uuid', $uuid)->firstOrFail();
        $user = auth()->user();

        if (!$comment->canDelete($user)) {
            return response()->json([
                'message' => 'You cannot delete this comment',
            ], 403);
        }

        $comment->delete();

        return response()->json([
            'message' => 'Comment deleted successfully',
        ]);
    }

    /**
     * Vote on a comment.
     */
    public function vote(Request $request, string $uuid): JsonResponse
    {
        $comment = Comment::where('uuid', $uuid)->firstOrFail();
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'message' => 'You must be logged in to vote',
            ], 401);
        }

        $validated = $request->validate([
            'vote' => 'required|integer|in:-1,0,1',
        ]);

        $vote = $validated['vote'];

        if ($vote === 0) {
            // Remove vote
            CommentVote::where('comment_id', $comment->id)
                ->where('user_id', $user->id)
                ->delete();
        } else {
            // Add or update vote
            CommentVote::updateOrCreate(
                ['comment_id' => $comment->id, 'user_id' => $user->id],
                ['vote' => $vote]
            );
        }

        $comment->refresh();

        return response()->json([
            'message' => 'Vote recorded',
            'data' => [
                'upvotes' => $comment->upvotes,
                'downvotes' => $comment->downvotes,
                'score' => $comment->vote_score,
                'user_vote' => $vote,
            ],
        ]);
    }

    /**
     * Report a comment.
     */
    public function report(Request $request, string $uuid): JsonResponse
    {
        $comment = Comment::where('uuid', $uuid)->firstOrFail();
        $user = auth()->user();

        $validated = $request->validate([
            'reason' => 'required|in:spam,harassment,hate_speech,misinformation,off_topic,inappropriate,copyright,other',
            'details' => 'nullable|string|max:1000',
        ]);

        // Check for duplicate reports
        $existingReport = CommentReport::where('comment_id', $comment->id)
            ->where(function ($q) use ($user, $request) {
                if ($user) {
                    $q->where('reporter_id', $user->id);
                } else {
                    $q->where('reporter_ip', $request->ip());
                }
            })
            ->where('status', 'pending')
            ->exists();

        if ($existingReport) {
            return response()->json([
                'message' => 'You have already reported this comment',
            ], 422);
        }

        CommentReport::create([
            'uuid' => Str::uuid(),
            'comment_id' => $comment->id,
            'reporter_id' => $user?->id,
            'reporter_ip' => $request->ip(),
            'reason' => $validated['reason'],
            'details' => $validated['details'] ?? null,
        ]);

        return response()->json([
            'message' => 'Report submitted. Thank you for helping keep our community safe.',
        ]);
    }

    /**
     * Get comment revisions (edit history).
     */
    public function revisions(string $uuid): JsonResponse
    {
        $comment = Comment::where('uuid', $uuid)->firstOrFail();

        $revisions = $comment->revisions()
            ->select('id', 'content', 'revision_number', 'edit_reason', 'created_at')
            ->get();

        return response()->json([
            'data' => $revisions,
            'meta' => [
                'current_content' => $comment->content,
                'total_edits' => $comment->edit_count,
            ],
        ]);
    }

    /**
     * Get replies to a comment (for lazy loading).
     */
    public function replies(Request $request, string $uuid): JsonResponse
    {
        $comment = Comment::where('uuid', $uuid)->firstOrFail();

        $replies = $comment->approvedReplies()
            ->with(['user:id,name', 'approvedReplies.user:id,name'])
            ->get();

        return response()->json([
            'data' => $replies,
        ]);
    }

    // ===== MODERATION ENDPOINTS =====

    /**
     * Get comments pending moderation.
     */
    public function pending(Request $request): JsonResponse
    {
        $this->authorize('moderate', Comment::class);

        $query = Comment::with(['user:id,name', 'commentable'])
            ->pending()
            ->orderBy('created_at', 'asc');

        // Filter by spam score
        if ($request->has('min_spam_score')) {
            $query->where('spam_score', '>=', $request->input('min_spam_score'));
        }

        $comments = $query->paginate(25);

        return response()->json([
            'data' => $comments->items(),
            'meta' => [
                'current_page' => $comments->currentPage(),
                'last_page' => $comments->lastPage(),
                'total' => $comments->total(),
            ],
        ]);
    }

    /**
     * Approve a comment.
     */
    public function approve(Request $request, string $uuid): JsonResponse
    {
        $this->authorize('moderate', Comment::class);

        $comment = Comment::where('uuid', $uuid)->firstOrFail();

        $comment->approve(auth()->user(), $request->input('reason'));

        return response()->json([
            'message' => 'Comment approved',
            'data' => $comment,
        ]);
    }

    /**
     * Reject a comment.
     */
    public function reject(Request $request, string $uuid): JsonResponse
    {
        $this->authorize('moderate', Comment::class);

        $comment = Comment::where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'reason' => 'nullable|string|max:255',
        ]);

        $comment->reject(auth()->user(), $validated['reason'] ?? null);

        return response()->json([
            'message' => 'Comment rejected',
            'data' => $comment,
        ]);
    }

    /**
     * Mark as spam.
     */
    public function markSpam(Request $request, string $uuid): JsonResponse
    {
        $this->authorize('moderate', Comment::class);

        $comment = Comment::where('uuid', $uuid)->firstOrFail();

        $comment->markAsSpam(auth()->user(), $request->input('reason'));

        return response()->json([
            'message' => 'Comment marked as spam',
            'data' => $comment,
        ]);
    }

    /**
     * Bulk moderate comments.
     */
    public function bulkModerate(Request $request): JsonResponse
    {
        $this->authorize('moderate', Comment::class);

        $validated = $request->validate([
            'comment_ids' => 'required|array|max:50',
            'comment_ids.*' => 'integer|exists:comments,id',
            'action' => 'required|in:approve,reject,spam,delete',
            'reason' => 'nullable|string|max:255',
        ]);

        $comments = Comment::whereIn('id', $validated['comment_ids'])->get();
        $moderator = auth()->user();
        $count = 0;

        foreach ($comments as $comment) {
            switch ($validated['action']) {
                case 'approve':
                    $comment->approve($moderator, $validated['reason'] ?? null);
                    break;
                case 'reject':
                    $comment->reject($moderator, $validated['reason'] ?? null);
                    break;
                case 'spam':
                    $comment->markAsSpam($moderator, $validated['reason'] ?? null);
                    break;
                case 'delete':
                    $comment->delete();
                    break;
            }
            $count++;
        }

        return response()->json([
            'message' => "{$count} comments processed",
        ]);
    }

    /**
     * Get comment reports.
     */
    public function reports(Request $request): JsonResponse
    {
        $this->authorize('moderate', Comment::class);

        $query = CommentReport::with(['comment.user:id,name', 'reporter:id,name'])
            ->orderBy('created_at', 'desc');

        if ($request->input('status', 'pending') !== 'all') {
            $query->where('status', $request->input('status', 'pending'));
        }

        $reports = $query->paginate(25);

        return response()->json([
            'data' => $reports->items(),
            'meta' => [
                'current_page' => $reports->currentPage(),
                'last_page' => $reports->lastPage(),
                'total' => $reports->total(),
            ],
        ]);
    }

    /**
     * Review a report.
     */
    public function reviewReport(Request $request, string $uuid): JsonResponse
    {
        $this->authorize('moderate', Comment::class);

        $report = CommentReport::where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'action' => 'required|in:dismiss,warn,hide_comment,delete_comment,ban_user',
            'notes' => 'nullable|string|max:1000',
        ]);

        $reviewer = auth()->user();

        switch ($validated['action']) {
            case 'dismiss':
                $report->dismiss($reviewer, $validated['notes'] ?? null);
                break;

            case 'hide_comment':
                $report->takeAction($reviewer, 'hidden', $validated['notes'] ?? null);
                $report->comment->update(['status' => 'hidden']);
                break;

            case 'delete_comment':
                $report->takeAction($reviewer, 'deleted', $validated['notes'] ?? null);
                $report->comment->delete();
                break;

            default:
                $report->takeAction($reviewer, $validated['action'], $validated['notes'] ?? null);
        }

        return response()->json([
            'message' => 'Report reviewed',
            'data' => $report->fresh(),
        ]);
    }
}
