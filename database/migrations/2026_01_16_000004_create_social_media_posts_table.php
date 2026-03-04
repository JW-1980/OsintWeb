<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Create social_media_posts table
 *
 * Schema:
 * - id: Primary key
 * - uuid: Public-facing unique identifier
 * - account_id: Foreign key to social_media_accounts
 * - platform_post_id: Platform's internal post ID
 * - post_url: Full URL to the post
 * - post_type: Type of post (text, image, video, link, thread)
 * - content_text: Text content of the post
 * - media_urls: JSON array of media URLs in the post
 * - preserved_media_paths: JSON array of local paths to preserved media copies
 * - hashtags: JSON array of hashtags in the post
 * - mentions: JSON array of mentioned accounts
 * - like_count: Number of likes/favorites
 * - share_count: Number of shares/retweets
 * - comment_count: Number of comments/replies
 * - view_count: Number of views (if available)
 * - posted_at: When the post was published
 * - is_reply: Whether this is a reply to another post
 * - reply_to_id: ID of parent post if reply
 * - is_repost: Whether this is a repost/retweet
 * - original_post_id: ID of original post if repost
 * - geolocation: Spatial point if location data available
 * - language_detected: Detected language code
 * - sentiment_score: Sentiment analysis score (-1 to 1)
 * - is_archived: Whether post content is preserved locally
 * - event_id: Optional link to an OSINT event
 * - created_at, updated_at: Timestamps
 * - deleted_at: Soft delete timestamp
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('social_media_posts', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('account_id')
                ->constrained('social_media_accounts')
                ->cascadeOnDelete();
            $table->string('platform_post_id')->index();
            $table->string('post_url', 2048)->nullable();
            $table->enum('post_type', [
                'text',
                'image',
                'video',
                'link',
                'thread',
            ])->default('text')->index();
            $table->text('content_text')->nullable();
            $table->json('media_urls')->nullable();
            $table->json('preserved_media_paths')->nullable();
            $table->json('hashtags')->nullable();
            $table->json('mentions')->nullable();
            $table->unsignedBigInteger('like_count')->default(0);
            $table->unsignedBigInteger('share_count')->default(0);
            $table->unsignedBigInteger('comment_count')->default(0);
            $table->unsignedBigInteger('view_count')->default(0);
            $table->timestamp('posted_at')->nullable()->index();
            $table->boolean('is_reply')->default(false);
            $table->foreignId('reply_to_id')
                ->nullable()
                ->constrained('social_media_posts')
                ->nullOnDelete();
            $table->boolean('is_repost')->default(false);
            $table->foreignId('original_post_id')
                ->nullable()
                ->constrained('social_media_posts')
                ->nullOnDelete();
            $table->geometry('geolocation', 'point', 4326)->nullable();
            $table->string('language_detected', 10)->nullable();
            $table->decimal('sentiment_score', 4, 3)->nullable()
                ->comment('Range: -1.000 (negative) to 1.000 (positive)');
            $table->boolean('is_archived')->default(false)->index();
            $table->foreignId('event_id')
                ->nullable()
                ->constrained('events')
                ->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            // Composite indexes for common queries
            $table->index(['account_id', 'posted_at'], 'idx_account_posted');
            $table->index(['account_id', 'platform_post_id'], 'idx_account_platform_post');
            $table->index(['is_archived', 'created_at'], 'idx_archived_created');
            $table->index(['event_id', 'posted_at'], 'idx_event_posted');

            // Note: spatial index requires NOT NULL; geolocation is nullable so skip spatial index

            // Full-text index for content search (if using MySQL)
            $table->fullText('content_text', 'ft_content');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('social_media_posts');
    }
};
