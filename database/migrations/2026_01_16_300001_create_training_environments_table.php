<?php

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
        Schema::create('training_environments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->index();
            $table->string('name')->comment('Name of the training environment');
            $table->text('description')->nullable()->comment('Detailed description of the environment');
            $table->enum('environment_type', ['sandbox', 'tutorial', 'exercise', 'certification'])
                ->default('sandbox')
                ->comment('Type of training environment');
            $table->json('base_scenario')->nullable()->comment('Initial data state for the scenario');
            $table->json('objectives')->nullable()->comment('Array of objectives to complete');
            $table->json('success_criteria')->nullable()->comment('Criteria for successful completion');
            $table->unsignedInteger('time_limit_minutes')->nullable()->comment('Time limit in minutes (null = unlimited)');
            $table->enum('difficulty', ['beginner', 'intermediate', 'advanced', 'expert'])
                ->default('beginner')
                ->comment('Difficulty level of the environment');
            $table->json('skills_covered')->nullable()->comment('Array of skill IDs or slugs covered');
            $table->boolean('is_active')->default(true)->index()->comment('Whether environment is active');
            $table->boolean('is_public')->default(false)->index()->comment('Whether environment is publicly available');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->comment('User who created the environment');
            $table->timestamps();
            $table->softDeletes();

            // Indices
            $table->index('environment_type');
            $table->index('difficulty');
            $table->index(['is_active', 'is_public']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_environments');
    }
};
