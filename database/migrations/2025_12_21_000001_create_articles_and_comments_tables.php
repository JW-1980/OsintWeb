<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Article Categories
        Schema::create('article_categories', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('color', 7)->default('#3B82F6');
            $table->string('icon')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Articles / News / Premium Content
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('author_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained('article_categories')->nullOnDelete();

            // Content
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('content');
            $table->string('featured_image')->nullable();

            // Type and access control
            $table->enum('type', ['news', 'article', 'analysis', 'report', 'tutorial'])->default('article');
            $table->boolean('is_premium')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_pinned')->default(false);

            // Publishing workflow
            $table->enum('status', ['draft', 'pending_review', 'published', 'archived'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamp('scheduled_at')->nullable();

            // SEO
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->json('meta_keywords')->nullable();

            // Statistics
            $table->unsignedBigInteger('view_count')->default(0);
            $table->unsignedBigInteger('share_count')->default(0);

            // Comments control
            $table->boolean('allow_comments')->default(true);
            $table->boolean('comments_locked')->default(false);
            $table->timestamp('comments_locked_at')->nullable();
            $table->string('comments_lock_reason')->nullable();

            // Reading time (auto-calculated)
            $table->unsignedSmallInteger('reading_time_minutes')->default(1);

            $table->softDeletes();
            $table->timestamps();

            $table->index(['status', 'published_at']);
            $table->index(['type', 'status']);
            $table->index('is_premium');
            $table->index('is_featured');
        });

        // Article Tags (many-to-many)
        Schema::create('article_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('article_tag', function (Blueprint $table) {
            $table->foreignId('article_id')->constrained()->onDelete('cascade');
            $table->foreignId('article_tag_id')->constrained('article_tags')->onDelete('cascade');
            $table->primary(['article_id', 'article_tag_id']);
        });

        // Threaded Comments System
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // Polymorphic relationship (can comment on articles, events, equipment, etc.)
            $table->morphs('commentable');

            // Author (nullable for guest comments if enabled)
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('guest_name')->nullable();
            $table->string('guest_email')->nullable();

            // Threading
            $table->foreignId('parent_id')->nullable()->constrained('comments')->onDelete('cascade');
            $table->unsignedSmallInteger('depth')->default(0);
            $table->string('path', 500)->nullable(); // Materialized path for efficient tree queries

            // Content
            $table->text('content');
            $table->text('content_html')->nullable(); // Rendered HTML (cached)

            // Edit tracking
            $table->boolean('is_edited')->default(false);
            $table->timestamp('last_edited_at')->nullable();
            $table->unsignedSmallInteger('edit_count')->default(0);

            // Moderation
            $table->enum('status', ['pending', 'approved', 'rejected', 'spam', 'hidden'])->default('approved');
            $table->foreignId('moderated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('moderated_at')->nullable();
            $table->string('moderation_reason')->nullable();

            // Anti-spam metadata
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->decimal('spam_score', 5, 2)->default(0);
            $table->json('spam_flags')->nullable();

            // Engagement
            $table->integer('upvotes')->default(0);
            $table->integer('downvotes')->default(0);
            $table->unsignedInteger('reply_count')->default(0);

            // Flags
            $table->boolean('is_pinned')->default(false);
            $table->boolean('is_author_reply')->default(false); // Article author's reply
            $table->boolean('is_highlighted')->default(false);

            $table->softDeletes();
            $table->timestamps();

            $table->index(['commentable_type', 'commentable_id', 'status']);
            $table->index(['parent_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index('status');
            $table->index('path');
        });

        // Comment Edit Revisions
        Schema::create('comment_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comment_id')->constrained()->onDelete('cascade');
            $table->text('content');
            $table->text('content_html')->nullable();
            $table->unsignedSmallInteger('revision_number');
            $table->string('ip_address', 45)->nullable();
            $table->string('edit_reason')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['comment_id', 'revision_number']);
        });

        // Comment Votes (for spam detection and engagement)
        Schema::create('comment_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comment_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->tinyInteger('vote'); // 1 = upvote, -1 = downvote
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['comment_id', 'user_id']);
        });

        // Comment Reports (for moderation)
        Schema::create('comment_reports', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('comment_id')->constrained()->onDelete('cascade');
            $table->foreignId('reporter_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reporter_ip', 45)->nullable();

            $table->enum('reason', [
                'spam',
                'harassment',
                'hate_speech',
                'misinformation',
                'off_topic',
                'inappropriate',
                'copyright',
                'other'
            ]);
            $table->text('details')->nullable();

            $table->enum('status', ['pending', 'reviewed', 'actioned', 'dismissed'])->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->string('action_taken')->nullable();

            $table->timestamps();

            $table->index(['comment_id', 'status']);
            $table->index('status');
        });

        // Spam Patterns (for anti-spam)
        Schema::create('spam_patterns', function (Blueprint $table) {
            $table->id();
            $table->string('pattern'); // Regex or keyword
            $table->enum('type', ['keyword', 'regex', 'domain', 'email', 'ip']);
            $table->decimal('weight', 3, 2)->default(1.00); // Contribution to spam score
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('hit_count')->default(0);
            $table->timestamps();

            $table->index(['type', 'is_active']);
        });

        // Rate Limiting / Cooldown Tracking
        Schema::create('comment_rate_limits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('ip_address', 45)->nullable();
            $table->string('fingerprint', 64)->nullable(); // Browser fingerprint
            $table->timestamp('last_comment_at')->useCurrent();
            $table->unsignedSmallInteger('comment_count_hour')->default(1);
            $table->unsignedSmallInteger('comment_count_day')->default(1);
            $table->boolean('is_blocked')->default(false);
            $table->timestamp('blocked_until')->nullable();
            $table->string('block_reason')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'last_comment_at']);
            $table->index(['ip_address', 'last_comment_at']);
        });

        // User Premium Subscriptions (for premium content access)
        Schema::create('user_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('plan', ['free', 'basic', 'premium', 'enterprise'])->default('free');
            $table->enum('status', ['active', 'cancelled', 'expired', 'suspended'])->default('active');
            $table->timestamp('starts_at')->useCurrent();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancellation_reason')->nullable();
            $table->json('features')->nullable(); // Additional features for this subscription
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['status', 'ends_at']);
        });

        // Article Read History (for premium content tracking)
        Schema::create('article_reads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('ip_address', 45)->nullable();
            $table->string('session_id', 100)->nullable();
            $table->unsignedSmallInteger('read_percentage')->default(0);
            $table->unsignedSmallInteger('time_spent_seconds')->default(0);
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();

            $table->index(['article_id', 'user_id']);
            $table->index(['article_id', 'started_at']);
        });

        // Bookmarks / Saved Articles
        Schema::create('article_bookmarks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('folder')->nullable(); // Optional folder/category
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['article_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('article_bookmarks');
        Schema::dropIfExists('article_reads');
        Schema::dropIfExists('user_subscriptions');
        Schema::dropIfExists('comment_rate_limits');
        Schema::dropIfExists('spam_patterns');
        Schema::dropIfExists('comment_reports');
        Schema::dropIfExists('comment_votes');
        Schema::dropIfExists('comment_revisions');
        Schema::dropIfExists('comments');
        Schema::dropIfExists('article_tag');
        Schema::dropIfExists('article_tags');
        Schema::dropIfExists('articles');
        Schema::dropIfExists('article_categories');
    }
};
