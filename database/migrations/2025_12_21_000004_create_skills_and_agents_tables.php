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
        // Skills table - defines available skills with keyword triggers
        Schema::create('skills', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name'); // e.g., "Geolocation Analysis"
            $table->string('slug')->unique(); // e.g., "geolocation-analysis"
            $table->text('description')->nullable();
            $table->json('keywords'); // Words that trigger this skill
            $table->json('aliases')->nullable(); // Alternative names/phrases
            $table->string('category', 50)->nullable(); // OSINT, Analysis, Verification, etc.
            $table->string('icon', 50)->nullable(); // Icon identifier
            $table->json('capabilities')->nullable(); // What this skill can do
            $table->json('required_inputs')->nullable(); // Required input fields
            $table->json('output_format')->nullable(); // Expected output structure
            $table->text('system_prompt')->nullable(); // Base prompt for AI agent
            $table->text('instructions')->nullable(); // Human-readable instructions
            $table->json('example_prompts')->nullable(); // Example usage
            $table->json('tools')->nullable(); // Tools/APIs this skill uses
            $table->unsignedTinyInteger('priority')->default(50); // 0-100, higher = more priority
            $table->float('match_threshold')->default(0.6); // Keyword match threshold
            $table->boolean('is_active')->default(true);
            $table->boolean('requires_auth')->default(false);
            $table->string('permission_required')->nullable(); // Required permission to use
            $table->unsignedInteger('usage_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('slug');
            $table->index('category');
            $table->index('is_active');
            $table->index('priority');
        });

        // Skill dependencies - skills that work together
        Schema::create('skill_dependencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('skill_id')->constrained()->onDelete('cascade');
            $table->foreignId('depends_on_skill_id')->constrained('skills')->onDelete('cascade');
            $table->enum('dependency_type', ['requires', 'enhances', 'conflicts'])->default('enhances');
            $table->timestamps();

            $table->unique(['skill_id', 'depends_on_skill_id']);
        });

        // Agents - AI agents that execute skills
        Schema::create('agents', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('type', 50)->default('skill-based'); // skill-based, workflow, composite
            $table->text('system_prompt')->nullable(); // Agent's base instructions
            $table->json('personality')->nullable(); // Agent personality traits
            $table->json('capabilities')->nullable(); // What the agent can do
            $table->json('limitations')->nullable(); // What the agent cannot do
            $table->string('model', 100)->nullable(); // AI model to use
            $table->float('temperature')->default(0.7);
            $table->unsignedInteger('max_tokens')->nullable();
            $table->json('tools')->nullable(); // Available tools/functions
            $table->json('memory_config')->nullable(); // Memory/context settings
            $table->boolean('is_active')->default(true);
            $table->boolean('is_public')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('usage_count')->default(0);
            $table->decimal('avg_response_time', 8, 2)->nullable(); // Average response time in seconds
            $table->timestamps();
            $table->softDeletes();

            $table->index('slug');
            $table->index('type');
            $table->index('is_active');
        });

        // Agent-Skill pivot - which skills an agent can use
        Schema::create('agent_skill', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained()->onDelete('cascade');
            $table->foreignId('skill_id')->constrained()->onDelete('cascade');
            $table->unsignedTinyInteger('proficiency')->default(100); // 0-100 proficiency level
            $table->boolean('is_primary')->default(false); // Primary skill for this agent
            $table->timestamps();

            $table->unique(['agent_id', 'skill_id']);
        });

        // Agent executions - track agent runs
        Schema::create('agent_executions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('agent_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('input_prompt');
            $table->json('matched_skills')->nullable(); // Skills that were triggered
            $table->json('context')->nullable(); // Execution context
            $table->longText('output')->nullable();
            $table->json('output_metadata')->nullable();
            $table->enum('status', ['pending', 'running', 'completed', 'failed', 'cancelled'])
                ->default('pending');
            $table->text('error_message')->nullable();
            $table->unsignedInteger('tokens_used')->nullable();
            $table->decimal('execution_time', 8, 3)->nullable(); // Time in seconds
            $table->decimal('cost', 10, 6)->nullable(); // Cost in USD
            $table->json('steps')->nullable(); // Execution steps/chain
            $table->float('confidence_score')->nullable(); // Confidence in output
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index(['agent_id', 'status']);
            $table->index(['user_id', 'created_at']);
        });

        // Skill triggers - log when skills are triggered
        Schema::create('skill_triggers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('skill_id')->constrained()->onDelete('cascade');
            $table->foreignId('agent_execution_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('trigger_text'); // The text that triggered the skill
            $table->json('matched_keywords'); // Which keywords matched
            $table->float('match_score'); // How strong the match was
            $table->boolean('was_executed')->default(true);
            $table->string('trigger_source', 50)->nullable(); // api, chat, webhook, etc.
            $table->timestamp('created_at')->useCurrent();

            $table->index(['skill_id', 'created_at']);
            $table->index('match_score');
        });

        // Agent templates - pre-built agent configurations
        Schema::create('agent_templates', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('category', 50)->nullable();
            $table->json('agent_config'); // Full agent configuration
            $table->json('skill_ids')->nullable(); // Skills to attach
            $table->json('example_use_cases')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('usage_count')->default(0);
            $table->timestamps();

            $table->index('category');
            $table->index('is_featured');
        });

        // User skill preferences
        Schema::create('user_skill_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('skill_id')->constrained()->onDelete('cascade');
            $table->boolean('is_enabled')->default(true);
            $table->unsignedTinyInteger('priority_override')->nullable();
            $table->json('custom_settings')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'skill_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_skill_preferences');
        Schema::dropIfExists('agent_templates');
        Schema::dropIfExists('skill_triggers');
        Schema::dropIfExists('agent_executions');
        Schema::dropIfExists('agent_skill');
        Schema::dropIfExists('agents');
        Schema::dropIfExists('skill_dependencies');
        Schema::dropIfExists('skills');
    }
};
