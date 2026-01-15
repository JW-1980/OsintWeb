<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Creates tables for the 35 OSINT features mentioned in the README:
     * - Alerts (Real-time Alert System)
     * - Reports (Report Generation)
     * - Cases (Case Management Workspaces)
     * - Timeline Entries (Chronological Timeline Builder)
     * - Saved Searches (Advanced Search & Filtering)
     * - Network Entities (Network Analysis)
     * - Training Progress (OSINT Training & Simulation Mode)
     */
    public function up(): void
    {
        // Real-time Alert System
        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('type'); // event, zone, equipment, custom, threshold
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('conditions'); // Alert trigger conditions
            $table->json('filters')->nullable(); // Geographic, temporal, entity filters
            $table->json('notification_channels')->nullable(); // email, webhook, push
            $table->boolean('is_active')->default(true);
            $table->string('frequency')->default('immediate'); // immediate, daily, weekly
            $table->timestamp('last_triggered_at')->nullable();
            $table->integer('trigger_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'is_active']);
            $table->index('type');
        });

        // Alert Logs
        Schema::create('alert_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alert_id')->constrained()->onDelete('cascade');
            $table->string('trigger_type'); // event_created, zone_changed, threshold_exceeded
            $table->json('trigger_data')->nullable();
            $table->json('matched_entities')->nullable();
            $table->boolean('notification_sent')->default(false);
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['alert_id', 'created_at']);
        });

        // Report Generation
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('type'); // event_summary, equipment_losses, timeline, custom
            $table->string('status')->default('draft'); // draft, generating, completed, failed
            $table->json('configuration')->nullable(); // Report parameters
            $table->json('filters')->nullable(); // Date range, regions, actors, etc.
            $table->json('sections')->nullable(); // Report sections and content
            $table->string('output_format')->default('pdf'); // pdf, html, docx, csv
            $table->string('file_path')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->boolean('is_public')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
            $table->index('type');
        });

        // Case Management Workspaces
        Schema::create('cases', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status')->default('open'); // open, in_progress, closed, archived
            $table->string('priority')->default('medium'); // low, medium, high, critical
            $table->string('classification')->default('unclassified'); // unclassified, internal, confidential
            $table->json('tags')->nullable();
            $table->json('collaborators')->nullable(); // User IDs with access
            $table->timestamp('started_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
            $table->index('priority');
        });

        // Case Items (events, equipment, actors linked to cases)
        Schema::create('case_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained()->onDelete('cascade');
            $table->morphs('itemable'); // Event, MilitaryEquipment, Actor, etc.
            $table->text('notes')->nullable();
            $table->string('relevance')->nullable(); // key, supporting, background
            $table->json('tags')->nullable();
            $table->foreignId('added_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index(['case_id', 'itemable_type']);
        });

        // Case Notes
        Schema::create('case_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('title')->nullable();
            $table->longText('content');
            $table->json('attachments')->nullable();
            $table->boolean('is_pinned')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index('case_id');
        });

        // Chronological Timeline Builder
        Schema::create('timeline_entries', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('case_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamp('occurred_at')->useCurrent();
            $table->timestamp('end_at')->nullable(); // For duration events
            $table->string('type'); // event, observation, analysis, milestone
            $table->string('category')->nullable();
            $table->json('sources')->nullable();
            $table->json('metadata')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('location_name')->nullable();
            $table->morphs('linked_entity'); // Optional link to Event, ControlZone, etc.
            $table->string('confidence')->default('medium');
            $table->boolean('is_verified')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['case_id', 'occurred_at']);
            $table->index('type');
        });

        // Saved Searches (Advanced Search & Filtering)
        Schema::create('saved_searches', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('entity_type'); // events, equipment, actors, zones
            $table->json('filters'); // Search criteria
            $table->json('sort')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('notify_on_new')->default(false);
            $table->integer('result_count')->nullable();
            $table->timestamp('last_executed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'entity_type']);
        });

        // Network Analysis Entities
        Schema::create('network_entities', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('case_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('name');
            $table->string('type'); // person, organization, location, equipment, communication
            $table->json('attributes')->nullable();
            $table->json('identifiers')->nullable(); // phone, email, social, etc.
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['case_id', 'type']);
        });

        // Network Relationships
        Schema::create('network_relationships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_id')->constrained('network_entities')->onDelete('cascade');
            $table->foreignId('target_id')->constrained('network_entities')->onDelete('cascade');
            $table->string('relationship_type'); // communicates_with, member_of, located_at, owns
            $table->string('direction')->default('bidirectional'); // bidirectional, directed
            $table->decimal('strength', 3, 2)->nullable(); // 0.00 to 1.00
            $table->json('metadata')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['source_id', 'target_id']);
            $table->index('relationship_type');
        });

        // OSINT Training Progress
        Schema::create('training_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('skill_id')->nullable()->constrained('osint_skills')->onDelete('set null');
            $table->string('module_slug')->nullable(); // For non-skill based training
            $table->string('status')->default('not_started'); // not_started, in_progress, completed
            $table->integer('progress_percent')->default(0);
            $table->json('completed_lessons')->nullable();
            $table->json('quiz_scores')->nullable();
            $table->integer('time_spent_minutes')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'skill_id']);
            $table->index(['user_id', 'status']);
        });

        // Training Exercises
        Schema::create('training_exercises', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('skill_id')->nullable()->constrained('osint_skills')->onDelete('set null');
            $table->string('title');
            $table->text('description');
            $table->string('difficulty'); // beginner, intermediate, advanced, expert
            $table->string('type'); // geolocation, verification, analysis, identification
            $table->json('scenario'); // Exercise scenario data
            $table->json('correct_answer'); // Expected answer/solution
            $table->json('hints')->nullable();
            $table->integer('time_limit_minutes')->nullable();
            $table->integer('points')->default(100);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['skill_id', 'difficulty']);
            $table->index('type');
        });

        // User Exercise Attempts
        Schema::create('exercise_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('exercise_id')->constrained('training_exercises')->onDelete('cascade');
            $table->json('user_answer');
            $table->boolean('is_correct')->default(false);
            $table->decimal('score', 5, 2)->default(0);
            $table->integer('time_taken_seconds')->nullable();
            $table->integer('hints_used')->default(0);
            $table->text('feedback')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'exercise_id']);
        });

        // Data Sources Configuration (for integrations)
        Schema::create('data_sources', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type'); // adsb, ais, satellite, social, news, api
            $table->text('description')->nullable();
            $table->string('provider')->nullable();
            $table->string('base_url')->nullable();
            $table->json('configuration')->nullable();
            $table->json('credentials')->nullable(); // Encrypted
            $table->boolean('is_active')->default(true);
            $table->boolean('requires_subscription')->default(false);
            $table->json('rate_limits')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['type', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_sources');
        Schema::dropIfExists('exercise_attempts');
        Schema::dropIfExists('training_exercises');
        Schema::dropIfExists('training_progress');
        Schema::dropIfExists('network_relationships');
        Schema::dropIfExists('network_entities');
        Schema::dropIfExists('saved_searches');
        Schema::dropIfExists('timeline_entries');
        Schema::dropIfExists('case_notes');
        Schema::dropIfExists('case_items');
        Schema::dropIfExists('cases');
        Schema::dropIfExists('reports');
        Schema::dropIfExists('alert_logs');
        Schema::dropIfExists('alerts');
    }
};
