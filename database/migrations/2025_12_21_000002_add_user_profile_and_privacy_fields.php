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
        // Add profile, avatar, onboarding, and privacy fields to users table
        Schema::table('users', function (Blueprint $table) {
            // Profile fields
            $table->text('bio')->nullable()->after('avatar_url');
            $table->string('organization')->nullable()->after('bio');
            $table->string('location')->nullable()->after('organization');
            $table->string('website')->nullable()->after('location');
            $table->string('timezone', 50)->default('UTC')->after('website');
            $table->string('locale', 10)->default('en')->after('timezone');

            // Avatar generation fields
            $table->enum('avatar_type', ['generated', 'uploaded', 'gravatar', 'initials'])
                ->default('generated')->after('avatar_url');
            $table->string('avatar_seed', 64)->nullable()->after('avatar_type');
            $table->string('avatar_color', 7)->nullable()->after('avatar_seed'); // Hex color

            // Account status
            $table->boolean('is_active')->default(true)->after('role');
            $table->boolean('is_verified')->default(false)->after('is_active');
            $table->timestamp('last_login_at')->nullable()->after('is_verified');
            $table->string('last_login_ip', 45)->nullable()->after('last_login_at');

            // Onboarding
            $table->unsignedTinyInteger('onboarding_step')->default(0)->after('preferences');
            $table->timestamp('onboarding_completed_at')->nullable()->after('onboarding_step');
            $table->boolean('onboarding_skipped')->default(false)->after('onboarding_completed_at');
            $table->json('onboarding_data')->nullable()->after('onboarding_skipped');

            // Privacy & compliance
            $table->timestamp('terms_accepted_at')->nullable()->after('onboarding_data');
            $table->string('terms_version', 20)->nullable()->after('terms_accepted_at');
            $table->timestamp('privacy_policy_accepted_at')->nullable()->after('terms_version');
            $table->string('privacy_policy_version', 20)->nullable()->after('privacy_policy_accepted_at');
            $table->boolean('marketing_consent')->default(false)->after('privacy_policy_version');
            $table->timestamp('marketing_consent_at')->nullable()->after('marketing_consent');
            $table->boolean('analytics_consent')->default(false)->after('marketing_consent_at');
            $table->timestamp('analytics_consent_at')->nullable()->after('analytics_consent');
            $table->boolean('data_processing_consent')->default(true)->after('analytics_consent_at');
            $table->timestamp('data_processing_consent_at')->nullable()->after('data_processing_consent');
            $table->string('consent_ip', 45)->nullable()->after('data_processing_consent_at');

            // Email preferences
            $table->json('email_preferences')->nullable()->after('consent_ip');
            $table->boolean('email_notifications_enabled')->default(true)->after('email_preferences');
            $table->timestamp('email_unsubscribed_at')->nullable()->after('email_notifications_enabled');

            // Account management
            $table->timestamp('account_locked_at')->nullable()->after('email_unsubscribed_at');
            $table->string('account_locked_reason')->nullable()->after('account_locked_at');
            $table->unsignedSmallInteger('failed_login_attempts')->default(0)->after('account_locked_reason');
            $table->timestamp('password_changed_at')->nullable()->after('failed_login_attempts');

            // Extended permissions (JSON field for direct permissions override)
            $table->json('permissions')->nullable()->after('role');

            // Indexes
            $table->index('is_active');
            $table->index('is_verified');
            $table->index('onboarding_completed_at');
            $table->index('last_login_at');
        });

        // Consent logs table - tracks all consent changes for GDPR compliance
        Schema::create('consent_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('consent_type', [
                'terms_of_service',
                'privacy_policy',
                'marketing',
                'analytics',
                'data_processing',
                'cookies',
                'third_party_sharing',
            ]);
            $table->boolean('consented');
            $table->string('version', 20)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'consent_type']);
            $table->index('created_at');
        });

        // Data export requests - GDPR right to data portability
        Schema::create('data_export_requests', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'expired'])
                ->default('pending');
            $table->enum('format', ['json', 'csv', 'xml'])->default('json');
            $table->json('include_data')->nullable(); // Which data types to include
            $table->string('download_token', 64)->nullable()->unique();
            $table->string('file_path')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->timestamp('requested_at')->useCurrent();
            $table->timestamp('processing_started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('downloaded_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('download_token');
            $table->index('expires_at');
        });

        // Account deletion requests - GDPR right to be forgotten
        Schema::create('account_deletion_requests', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['pending', 'confirmed', 'processing', 'completed', 'cancelled'])
                ->default('pending');
            $table->string('confirmation_token', 64)->nullable()->unique();
            $table->timestamp('confirmation_sent_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('scheduled_for')->nullable(); // Grace period before deletion
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancellation_reason')->nullable();
            $table->string('reason')->nullable(); // Why user wants to delete
            $table->text('feedback')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->json('deleted_data_summary')->nullable(); // Summary of what was deleted
            $table->timestamps();

            $table->index('status');
            $table->index('confirmation_token');
            $table->index('scheduled_for');
        });

        // User sessions table for session management
        Schema::create('user_sessions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('session_id', 100)->unique();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->string('device_type', 50)->nullable();
            $table->string('browser', 50)->nullable();
            $table->string('platform', 50)->nullable();
            $table->string('location')->nullable();
            $table->boolean('is_current')->default(false);
            $table->timestamp('last_activity_at')->useCurrent();
            $table->timestamp('expires_at')->useCurrent();
            $table->timestamps();

            $table->index(['user_id', 'is_current']);
            $table->index('last_activity_at');
            $table->index('expires_at');
        });

        // User activity log for onboarding and engagement tracking
        Schema::create('user_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('activity_type', 50);
            $table->string('description')->nullable();
            $table->json('properties')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['user_id', 'activity_type']);
            $table->index(['activity_type', 'created_at']);
            $table->index('created_at');
        });

        // Onboarding steps tracking
        Schema::create('onboarding_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('step_key', 50);
            $table->string('step_name');
            $table->boolean('completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->json('step_data')->nullable();
            $table->unsignedTinyInteger('order')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'step_key']);
            $table->index(['user_id', 'completed']);
        });

        // User preferences history (for audit)
        Schema::create('user_preference_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('preference_key');
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['user_id', 'preference_key']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_preference_changes');
        Schema::dropIfExists('onboarding_progress');
        Schema::dropIfExists('user_activities');
        Schema::dropIfExists('user_sessions');
        Schema::dropIfExists('account_deletion_requests');
        Schema::dropIfExists('data_export_requests');
        Schema::dropIfExists('consent_logs');

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['is_active']);
            $table->dropIndex(['is_verified']);
            $table->dropIndex(['onboarding_completed_at']);
            $table->dropIndex(['last_login_at']);

            $table->dropColumn([
                'bio',
                'organization',
                'location',
                'website',
                'timezone',
                'locale',
                'avatar_type',
                'avatar_seed',
                'avatar_color',
                'is_active',
                'is_verified',
                'last_login_at',
                'last_login_ip',
                'onboarding_step',
                'onboarding_completed_at',
                'onboarding_skipped',
                'onboarding_data',
                'terms_accepted_at',
                'terms_version',
                'privacy_policy_accepted_at',
                'privacy_policy_version',
                'marketing_consent',
                'marketing_consent_at',
                'analytics_consent',
                'analytics_consent_at',
                'data_processing_consent',
                'data_processing_consent_at',
                'consent_ip',
                'email_preferences',
                'email_notifications_enabled',
                'email_unsubscribed_at',
                'account_locked_at',
                'account_locked_reason',
                'failed_login_attempts',
                'password_changed_at',
                'permissions',
            ]);
        });
    }
};
