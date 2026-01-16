<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Create social_media_accounts table
 *
 * Schema:
 * - id: Primary key
 * - uuid: Public-facing unique identifier
 * - platform: Social media platform (twitter, telegram, facebook, instagram, youtube, tiktok, vk, reddit)
 * - username: The account's username/handle
 * - display_name: The account's display name
 * - profile_url: Full URL to the profile
 * - platform_user_id: Platform's internal user ID (for API matching)
 * - profile_image_url: URL to profile image
 * - follower_count: Number of followers
 * - following_count: Number of accounts being followed
 * - post_count: Total number of posts
 * - account_created_at: When the account was created on the platform
 * - bio: Account biography/description
 * - location: Self-reported location
 * - is_verified: Whether the account is verified on the platform
 * - is_monitored: Whether we are actively monitoring this account
 * - monitoring_priority: Priority level 1-5 (1=highest)
 * - last_checked_at: When we last checked for new posts
 * - last_post_at: When the account last posted
 * - metadata: Additional platform-specific data (JSON)
 * - added_by: User who added this account for monitoring
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
        Schema::create('social_media_accounts', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->enum('platform', [
                'twitter',
                'telegram',
                'facebook',
                'instagram',
                'youtube',
                'tiktok',
                'vk',
                'reddit',
            ])->index();
            $table->string('username')->index();
            $table->string('display_name')->nullable();
            $table->string('profile_url', 2048)->nullable();
            $table->string('platform_user_id')->nullable()->index();
            $table->string('profile_image_url', 2048)->nullable();
            $table->unsignedBigInteger('follower_count')->default(0);
            $table->unsignedBigInteger('following_count')->default(0);
            $table->unsignedBigInteger('post_count')->default(0);
            $table->timestamp('account_created_at')->nullable();
            $table->text('bio')->nullable();
            $table->string('location')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_monitored')->default(true)->index();
            $table->unsignedTinyInteger('monitoring_priority')->default(3)
                ->comment('1=highest, 5=lowest priority');
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamp('last_post_at')->nullable();
            $table->json('metadata')->nullable();
            $table->foreignId('added_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            // Composite indexes for common queries
            $table->index(['platform', 'username'], 'idx_platform_username');
            $table->index(['is_monitored', 'monitoring_priority'], 'idx_monitored_priority');
            $table->index(['is_monitored', 'last_checked_at'], 'idx_monitored_last_checked');

            // Ensure unique platform+username combination
            $table->unique(['platform', 'username'], 'unique_platform_username');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('social_media_accounts');
    }
};
