<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create flagged_content table
 *
 * Tracks content that has been flagged for potential disinformation
 * with detailed audit trail and resolution workflow.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('flagged_content', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->index();

            // Link to disinformation analysis
            $table->foreignId('disinformation_analysis_id')
                ->constrained()
                ->cascadeOnDelete();

            // Polymorphic relationship to flagged content
            $table->string('content_type');
            $table->unsignedBigInteger('content_id');
            $table->index(['content_type', 'content_id'], 'flagged_content_morphs_index');

            // Flag details
            $table->enum('flag_reason', [
                'automated_detection',
                'user_report',
                'moderator_review',
                'pattern_match',
                'ai_detection',
                'network_analysis',
            ])->index();
            $table->text('flag_notes')->nullable();
            $table->json('flag_metadata')->nullable()->comment('Additional context about the flag');

            // Flag source
            $table->foreignId('flagged_by')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->timestamp('flagged_at');

            // Resolution workflow
            $table->enum('resolution_status', [
                'pending',
                'under_review',
                'confirmed_disinformation',
                'false_positive',
                'inconclusive',
                'appealed',
                'resolved',
            ])->default('pending')->index();

            // Resolution details
            $table->foreignId('resolved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->text('resolution_notes')->nullable();

            // Actions taken
            $table->json('actions_taken')->nullable()->comment('List of moderation actions taken');

            // Appeal tracking
            $table->boolean('has_appeal')->default(false);
            $table->text('appeal_reason')->nullable();
            $table->timestamp('appealed_at')->nullable();
            $table->foreignId('appeal_reviewed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Priority and severity
            $table->enum('priority', [
                'low',
                'medium',
                'high',
                'critical',
            ])->default('medium')->index();

            // Visibility control
            $table->boolean('content_hidden')->default(false);
            $table->boolean('content_removed')->default(false);

            $table->timestamps();
            $table->softDeletes();

            // Composite indexes for common queries
            $table->index(['resolution_status', 'priority'], 'flagged_status_priority_index');
            $table->index(['flagged_at', 'resolution_status'], 'flagged_recent_pending_index');
            $table->index(['content_type', 'resolution_status'], 'flagged_type_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flagged_content');
    }
};
