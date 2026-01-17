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
     * Database Schema Documentation:
     *
     * experiments - A/B test experiment definitions
     * - Define what's being tested (button text, headline, etc.)
     * - Control experiment status (draft, running, paused, completed)
     * - Set traffic allocation percentage
     *
     * experiment_variants - Different versions to test
     * - Each experiment has 2+ variants (including control)
     * - Stores the actual content/config for each variant
     * - Tracks traffic weight distribution
     *
     * experiment_assignments - Anonymous variant assignments
     * - Maps anonymous visitor hash to assigned variant
     * - Ensures consistent experience per visitor
     * - No personal data stored
     *
     * experiment_conversions - Aggregated conversion data
     * - Daily aggregated conversion counts per variant
     * - Supports multiple conversion goals per experiment
     */
    public function up(): void
    {
        // Experiment definitions
        Schema::create('experiments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name', 255);
            $table->string('slug', 255)->unique();
            $table->text('description')->nullable();
            $table->string('type', 50)->default('text'); // text, button, layout, feature
            $table->string('location', 255); // Where in the UI (e.g., 'homepage.hero', 'event.cta')
            $table->enum('status', ['draft', 'running', 'paused', 'completed'])->default('draft');
            $table->unsignedTinyInteger('traffic_percentage')->default(100); // % of traffic to include
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->unsignedInteger('min_sample_size')->default(100); // Minimum conversions before significance
            $table->float('min_confidence')->default(0.95); // Required confidence level (0.90, 0.95, 0.99)
            $table->string('primary_goal', 100); // click, signup, event_create, etc.
            $table->json('secondary_goals')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('location');
        });

        // Experiment variants
        Schema::create('experiment_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('experiment_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100); // e.g., 'Control', 'Variant A', 'Variant B'
            $table->string('slug', 100);
            $table->boolean('is_control')->default(false);
            $table->unsignedTinyInteger('weight')->default(50); // Traffic weight (should sum to 100)
            $table->json('content'); // The actual variant content/config
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['experiment_id', 'slug']);
            $table->index(['experiment_id', 'is_control']);
        });

        // Anonymous variant assignments (for consistent user experience)
        Schema::create('experiment_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('experiment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('variant_id')->constrained('experiment_variants')->cascadeOnDelete();
            $table->string('visitor_hash', 64)->index(); // SHA-256 of anonymous identifier
            $table->timestamp('assigned_at');
            $table->timestamp('last_seen_at')->nullable();

            $table->unique(['experiment_id', 'visitor_hash']);
        });

        // Aggregated conversion data (daily)
        Schema::create('experiment_conversions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('experiment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('variant_id')->constrained('experiment_variants')->cascadeOnDelete();
            $table->date('date')->index();
            $table->string('goal', 100)->index(); // Which conversion goal
            $table->unsignedInteger('impressions')->default(0); // Times variant was shown
            $table->unsignedInteger('conversions')->default(0); // Times goal was achieved
            $table->unsignedInteger('unique_impressions')->default(0);
            $table->unsignedInteger('unique_conversions')->default(0);
            $table->timestamps();

            $table->unique(['experiment_id', 'variant_id', 'date', 'goal'], 'exp_conv_unique');
        });

        // Experiment results cache (for quick dashboard display)
        Schema::create('experiment_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('experiment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('variant_id')->constrained('experiment_variants')->cascadeOnDelete();
            $table->string('goal', 100);
            $table->unsignedInteger('total_impressions')->default(0);
            $table->unsignedInteger('total_conversions')->default(0);
            $table->float('conversion_rate', 8, 4)->default(0);
            $table->float('lift_vs_control', 8, 4)->nullable(); // % improvement over control
            $table->float('confidence', 8, 4)->nullable(); // Statistical confidence
            $table->float('z_score', 8, 4)->nullable();
            $table->boolean('is_significant')->default(false);
            $table->boolean('is_winner')->default(false);
            $table->timestamp('calculated_at');
            $table->timestamps();

            $table->unique(['experiment_id', 'variant_id', 'goal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('experiment_results');
        Schema::dropIfExists('experiment_conversions');
        Schema::dropIfExists('experiment_assignments');
        Schema::dropIfExists('experiment_variants');
        Schema::dropIfExists('experiments');
    }
};
