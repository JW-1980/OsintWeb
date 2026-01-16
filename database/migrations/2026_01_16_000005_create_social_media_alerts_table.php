<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Create social_media_alerts table
 *
 * Schema:
 * - id: Primary key
 * - uuid: Public-facing unique identifier
 * - account_id: Optional foreign key to monitor specific account
 * - keyword: Optional keyword to watch for
 * - alert_type: Type of alert (new_post, keyword_match, account_activity)
 * - user_id: User who owns this alert
 * - name: Human-readable alert name
 * - description: Optional description of alert purpose
 * - notification_channels: JSON array of notification methods
 * - filters: JSON object with additional filtering criteria
 * - is_active: Whether alert is currently active
 * - cooldown_minutes: Minimum minutes between triggers
 * - last_triggered_at: When alert was last triggered
 * - trigger_count: Total times alert has been triggered
 * - created_at, updated_at: Timestamps
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('social_media_alerts', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('account_id')
                ->nullable()
                ->constrained('social_media_accounts')
                ->cascadeOnDelete();
            $table->string('keyword')->nullable()->index();
            $table->enum('alert_type', [
                'new_post',
                'keyword_match',
                'account_activity',
            ])->index();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('notification_channels')->nullable()
                ->comment('Array of channels: email, database, slack, etc.');
            $table->json('filters')->nullable()
                ->comment('Additional filtering: platforms, languages, etc.');
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('cooldown_minutes')->default(5);
            $table->timestamp('last_triggered_at')->nullable();
            $table->unsignedBigInteger('trigger_count')->default(0);
            $table->timestamps();

            // Composite indexes
            $table->index(['user_id', 'is_active'], 'idx_user_active');
            $table->index(['alert_type', 'is_active'], 'idx_type_active');
            $table->index(['account_id', 'is_active'], 'idx_account_active');
        });

        // Create pivot table for alert-post triggers (for history)
        Schema::create('social_media_alert_triggers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alert_id')
                ->constrained('social_media_alerts')
                ->cascadeOnDelete();
            $table->foreignId('post_id')
                ->constrained('social_media_posts')
                ->cascadeOnDelete();
            $table->string('match_reason')->nullable();
            $table->json('match_details')->nullable();
            $table->timestamp('triggered_at');

            $table->index(['alert_id', 'triggered_at'], 'idx_alert_triggered');
            $table->index(['post_id', 'triggered_at'], 'idx_post_triggered');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('social_media_alert_triggers');
        Schema::dropIfExists('social_media_alerts');
    }
};
