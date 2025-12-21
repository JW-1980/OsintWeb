<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleBookmark;
use App\Models\ArticleCategory;
use App\Models\ArticleRead;
use App\Models\ArticleTag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ArticleController extends Controller
{
    /**
     * Display a listing of articles.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Article::with(['author:id,name', 'category:id,name,slug,color', 'tags:id,name,slug'])
            ->withCount('approvedComments');

        // By default, only show published articles
        if (!$request->boolean('include_drafts') || !auth()->user()?->hasPermission('articles.manage')) {
            $query->published();
        }

        // Filter by type
        if ($request->has('type')) {
            $query->ofType($request->input('type'));
        }

        // Filter by category
        if ($request->has('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->input('category'));
            });
        }

        // Filter by tag
        if ($request->has('tag')) {
            $query->whereHas('tags', function ($q) use ($request) {
                $q->where('slug', $request->input('tag'));
            });
        }

        // Filter premium/free
        if ($request->has('premium')) {
            $query->where('is_premium', $request->boolean('premium'));
        }

        // Filter featured
        if ($request->boolean('featured')) {
            $query->featured();
        }

        // Search
        if ($request->has('search')) {
            $term = $request->input('search');
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', "%{$term}%")
                    ->orWhere('excerpt', 'like', "%{$term}%")
                    ->orWhere('content', 'like', "%{$term}%");
            });
        }

        // Sorting
        $sortField = $request->input('sort', 'published_at');
        $sortOrder = $request->input('order', 'desc');
        $allowedSorts = ['published_at', 'created_at', 'title', 'view_count'];

        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortOrder === 'asc' ? 'asc' : 'desc');
        }

        // Handle pinned articles
        if ($request->boolean('respect_pinned', true)) {
            $query->orderByDesc('is_pinned');
        }

        // Pagination
        $perPage = min((int) $request->input('per_page', 15), 50);
        $articles = $query->paginate($perPage);

        return response()->json([
            'data' => $articles->items(),
            'meta' => [
                'current_page' => $articles->currentPage(),
                'last_page' => $articles->lastPage(),
                'per_page' => $articles->perPage(),
                'total' => $articles->total(),
            ],
            'links' => [
                'first' => $articles->url(1),
                'last' => $articles->url($articles->lastPage()),
                'prev' => $articles->previousPageUrl(),
                'next' => $articles->nextPageUrl(),
            ],
        ]);
    }

    /**
     * Store a newly created article.
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Article::class);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:articles,slug',
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string',
            'featured_image' => 'nullable|url|max:500',
            'category_id' => 'nullable|exists:article_categories,id',
            'type' => 'nullable|in:news,article,analysis,report,tutorial',
            'is_premium' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
            'status' => 'nullable|in:draft,pending_review,published',
            'published_at' => 'nullable|date',
            'scheduled_at' => 'nullable|date|after:now',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|array',
            'allow_comments' => 'nullable|boolean',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
        ]);

        $article = Article::create([
            'uuid' => Str::uuid(),
            'author_id' => auth()->id(),
            'slug' => $validated['slug'] ?? Str::slug($validated['title']),
            ...$validated,
        ]);

        // Handle tags
        if (!empty($validated['tags'])) {
            $tagIds = collect($validated['tags'])->map(function ($tagName) {
                return ArticleTag::findOrCreateByName($tagName)->id;
            });
            $article->tags()->sync($tagIds);
        }

        return response()->json([
            'message' => 'Article created successfully',
            'data' => $article->load(['author:id,name', 'category', 'tags']),
        ], 201);
    }

    /**
     * Display the specified article.
     */
    public function show(Request $request, string $uuid): JsonResponse
    {
        $article = Article::with([
            'author:id,name',
            'category',
            'tags',
        ])
            ->withCount('approvedComments')
            ->where('uuid', $uuid)
            ->firstOrFail();

        // Check if user can access (premium content)
        $user = auth()->user();
        $canAccess = $article->canAccess($user);

        if (!$canAccess && $article->is_premium) {
            // Return teaser for premium content
            return response()->json([
                'data' => [
                    'uuid' => $article->uuid,
                    'title' => $article->title,
                    'excerpt' => $article->excerpt,
                    'featured_image' => $article->featured_image,
                    'author' => $article->author,
                    'category' => $article->category,
                    'tags' => $article->tags,
                    'is_premium' => true,
                    'published_at' => $article->published_at,
                    'reading_time_minutes' => $article->reading_time_minutes,
                    'content' => null, // Don't expose full content
                ],
                'meta' => [
                    'requires_subscription' => true,
                    'subscription_plans' => ['premium', 'enterprise'],
                ],
            ], 403);
        }

        // Track read
        if ($user) {
            ArticleRead::updateOrCreate(
                ['article_id' => $article->id, 'user_id' => $user->id],
                ['started_at' => now(), 'ip_address' => $request->ip()]
            );
        }

        // Increment view count
        $article->incrementViewCount();

        // Check if user has bookmarked
        $isBookmarked = $user ? $article->bookmarks()->where('user_id', $user->id)->exists() : false;

        return response()->json([
            'data' => $article,
            'meta' => [
                'can_comment' => $article->canComment(),
                'is_bookmarked' => $isBookmarked,
                'user_can_edit' => $user?->hasPermission('articles.edit') || $user?->id === $article->author_id,
            ],
        ]);
    }

    /**
     * Update the specified article.
     */
    public function update(Request $request, string $uuid): JsonResponse
    {
        $article = Article::where('uuid', $uuid)->firstOrFail();
        $this->authorize('update', $article);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'slug' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('articles', 'slug')->ignore($article->id),
            ],
            'excerpt' => 'nullable|string|max:500',
            'content' => 'sometimes|string',
            'featured_image' => 'nullable|url|max:500',
            'category_id' => 'nullable|exists:article_categories,id',
            'type' => 'nullable|in:news,article,analysis,report,tutorial',
            'is_premium' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
            'is_pinned' => 'nullable|boolean',
            'status' => 'nullable|in:draft,pending_review,published,archived',
            'published_at' => 'nullable|date',
            'scheduled_at' => 'nullable|date',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|array',
            'allow_comments' => 'nullable|boolean',
            'comments_locked' => 'nullable|boolean',
            'comments_lock_reason' => 'nullable|string|max:255',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
        ]);

        // Handle comments lock
        if (isset($validated['comments_locked']) && $validated['comments_locked'] && !$article->comments_locked) {
            $validated['comments_locked_at'] = now();
        }

        $article->update($validated);

        // Handle tags
        if (isset($validated['tags'])) {
            $tagIds = collect($validated['tags'])->map(function ($tagName) {
                return ArticleTag::findOrCreateByName($tagName)->id;
            });
            $article->tags()->sync($tagIds);
        }

        return response()->json([
            'message' => 'Article updated successfully',
            'data' => $article->fresh(['author:id,name', 'category', 'tags']),
        ]);
    }

    /**
     * Remove the specified article.
     */
    public function destroy(string $uuid): JsonResponse
    {
        $article = Article::where('uuid', $uuid)->firstOrFail();
        $this->authorize('delete', $article);

        $article->delete();

        return response()->json([
            'message' => 'Article deleted successfully',
        ]);
    }

    /**
     * Get article categories.
     */
    public function categories(): JsonResponse
    {
        $categories = ArticleCategory::active()
            ->ordered()
            ->withCount('publishedArticles')
            ->get();

        return response()->json([
            'data' => $categories,
        ]);
    }

    /**
     * Get popular tags.
     */
    public function tags(Request $request): JsonResponse
    {
        $limit = min((int) $request->input('limit', 20), 50);

        $tags = ArticleTag::withCount('publishedArticles')
            ->having('published_articles_count', '>', 0)
            ->orderByDesc('published_articles_count')
            ->limit($limit)
            ->get();

        return response()->json([
            'data' => $tags,
        ]);
    }

    /**
     * Bookmark an article.
     */
    public function bookmark(Request $request, string $uuid): JsonResponse
    {
        $article = Article::where('uuid', $uuid)->firstOrFail();
        $user = auth()->user();

        $validated = $request->validate([
            'folder' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:500',
        ]);

        $bookmark = ArticleBookmark::updateOrCreate(
            ['article_id' => $article->id, 'user_id' => $user->id],
            $validated
        );

        return response()->json([
            'message' => 'Article bookmarked',
            'data' => $bookmark,
        ]);
    }

    /**
     * Remove bookmark.
     */
    public function unbookmark(string $uuid): JsonResponse
    {
        $article = Article::where('uuid', $uuid)->firstOrFail();
        $user = auth()->user();

        ArticleBookmark::where('article_id', $article->id)
            ->where('user_id', $user->id)
            ->delete();

        return response()->json([
            'message' => 'Bookmark removed',
        ]);
    }

    /**
     * Get user's bookmarks.
     */
    public function myBookmarks(Request $request): JsonResponse
    {
        $user = auth()->user();

        $query = ArticleBookmark::with(['article' => function ($q) {
            $q->with(['author:id,name', 'category:id,name,slug']);
        }])
            ->where('user_id', $user->id);

        if ($request->has('folder')) {
            $query->inFolder($request->input('folder'));
        }

        $bookmarks = $query->latest()->paginate(20);

        return response()->json([
            'data' => $bookmarks->items(),
            'meta' => [
                'current_page' => $bookmarks->currentPage(),
                'last_page' => $bookmarks->lastPage(),
                'total' => $bookmarks->total(),
            ],
        ]);
    }

    /**
     * Track reading progress.
     */
    public function trackProgress(Request $request, string $uuid): JsonResponse
    {
        $article = Article::where('uuid', $uuid)->firstOrFail();
        $user = auth()->user();

        if (!$user) {
            return response()->json(['message' => 'OK']);
        }

        $validated = $request->validate([
            'percentage' => 'required|integer|min:0|max:100',
            'time_spent' => 'required|integer|min:0',
        ]);

        $read = ArticleRead::firstOrCreate(
            ['article_id' => $article->id, 'user_id' => $user->id],
            ['started_at' => now(), 'ip_address' => $request->ip()]
        );

        $read->updateProgress($validated['percentage'], $validated['time_spent']);

        return response()->json(['message' => 'Progress tracked']);
    }
}
