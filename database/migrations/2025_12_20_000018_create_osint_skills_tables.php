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
        // OSINT Skill Categories
        Schema::create('osint_skill_categories', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->string('color', 7)->default('#3B82F6');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // OSINT Skills
        Schema::create('osint_skills', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('category_id')->constrained('osint_skill_categories')->onDelete('cascade');
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('difficulty')->default('intermediate'); // beginner, intermediate, advanced, expert
            $table->json('data')->nullable(); // Comprehensive skill data (tools, methods, resources, etc.)
            $table->string('icon')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['category_id', 'is_active']);
            $table->index('difficulty');
        });

        // OSINT Intelligence Agents (for automated intelligence gathering)
        Schema::create('intelligence_agents', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('type'); // monitor, scraper, analyzer, aggregator, alert
            $table->string('category'); // social_media, flight, maritime, satellite, news, etc.
            $table->json('configuration')->nullable(); // Agent-specific config
            $table->json('data_sources')->nullable(); // List of data sources
            $table->json('output_format')->nullable(); // Output configuration
            $table->json('filters')->nullable(); // Filtering rules
            $table->json('triggers')->nullable(); // Trigger conditions for alerts
            $table->string('schedule')->nullable(); // Cron expression for scheduled runs
            $table->boolean('is_active')->default(true);
            $table->boolean('requires_api_key')->default(false);
            $table->json('api_providers')->nullable(); // Required API providers
            $table->integer('rate_limit_per_minute')->nullable();
            $table->integer('priority')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['type', 'is_active']);
            $table->index(['category', 'is_active']);
        });

        // Agent execution logs
        Schema::create('agent_executions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained('intelligence_agents')->onDelete('cascade');
            $table->foreignId('triggered_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('status'); // pending, running, completed, failed
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('items_processed')->default(0);
            $table->integer('items_matched')->default(0);
            $table->json('results_summary')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['agent_id', 'status']);
            $table->index('started_at');
        });

        // Agent data points (collected intelligence)
        Schema::create('agent_data_points', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('agent_id')->constrained('intelligence_agents')->onDelete('cascade');
            $table->foreignId('execution_id')->nullable()->constrained('agent_executions')->onDelete('set null');
            $table->string('source_type'); // twitter, telegram, adsb, ais, news, etc.
            $table->string('source_id')->nullable(); // Original source identifier
            $table->string('source_url')->nullable();
            $table->text('title')->nullable();
            $table->longText('content')->nullable();
            $table->json('metadata')->nullable();
            $table->json('extracted_entities')->nullable(); // People, places, equipment, etc.
            $table->json('coordinates')->nullable(); // Lat/lng if applicable
            $table->decimal('confidence_score', 3, 2)->default(0.50);
            $table->string('status')->default('raw'); // raw, processed, verified, archived
            $table->timestamp('source_timestamp')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['agent_id', 'status']);
            $table->index(['source_type', 'source_timestamp']);
            $table->index('confidence_score');
        });

        // User skill assessments
        Schema::create('user_skill_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('skill_id')->constrained('osint_skills')->onDelete('cascade');
            $table->string('proficiency_level')->default('beginner'); // none, beginner, intermediate, advanced, expert
            $table->integer('practice_count')->default(0);
            $table->timestamp('last_practiced_at')->nullable();
            $table->json('notes')->nullable();
            $table->boolean('is_certified')->default(false);
            $table->timestamp('certified_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'skill_id']);
            $table->index('proficiency_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_skill_assessments');
        Schema::dropIfExists('agent_data_points');
        Schema::dropIfExists('agent_executions');
        Schema::dropIfExists('intelligence_agents');
        Schema::dropIfExists('osint_skills');
        Schema::dropIfExists('osint_skill_categories');
    }
};
