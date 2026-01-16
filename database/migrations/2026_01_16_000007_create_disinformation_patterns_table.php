<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create disinformation_patterns table
 *
 * Pattern library for detecting known disinformation tactics.
 * Contains rules, examples, and metadata for various manipulation techniques.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('disinformation_patterns', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index();
            $table->text('description');
            $table->string('slug')->unique()->index();

            // Pattern classification
            $table->enum('pattern_type', [
                'linguistic',    // Language/text patterns (propaganda phrases, loaded language)
                'behavioral',    // Account behavior patterns (rapid posting, coordinated timing)
                'temporal',      // Time-based patterns (unusual posting times, burst activity)
                'network',       // Network patterns (bot networks, sock puppets)
                'media',         // Media manipulation (deepfakes, doctored images)
            ])->index();

            // Detection configuration
            $table->json('detection_rules')->comment('JSON rules for pattern matching');
            $table->json('keywords')->nullable()->comment('Keywords and phrases to detect');
            $table->json('regex_patterns')->nullable()->comment('Regex patterns for matching');

            // Severity and impact
            $table->unsignedTinyInteger('severity')->default(3)->comment('1-5 scale, 5 being most severe');
            $table->unsignedTinyInteger('base_threat_weight')->default(10)->comment('Weight added to threat score when matched');

            // Examples and context
            $table->json('examples')->nullable()->comment('Real-world examples of this pattern');
            $table->json('indicators')->nullable()->comment('Specific indicators to look for');
            $table->json('counter_indicators')->nullable()->comment('Signs that pattern may be false positive');

            // Related patterns
            $table->json('related_pattern_ids')->nullable()->comment('IDs of related/similar patterns');

            // Management
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('match_count')->default(0)->comment('Number of times pattern was matched');
            $table->timestamp('last_matched_at')->nullable();

            // Versioning
            $table->unsignedInteger('version')->default(1);
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Indexes for efficient queries
            $table->index(['pattern_type', 'is_active'], 'patterns_type_active_index');
            $table->index(['severity', 'is_active'], 'patterns_severity_active_index');
        });

        // Pivot table for analysis-pattern relationships
        Schema::create('analysis_pattern', function (Blueprint $table) {
            $table->id();
            $table->foreignId('disinformation_analysis_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('disinformation_pattern_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->json('match_details')->nullable()->comment('Specific details of how pattern matched');
            $table->unsignedTinyInteger('match_confidence')->default(0)->comment('0-100 confidence of match');
            $table->timestamps();

            $table->unique(
                ['disinformation_analysis_id', 'disinformation_pattern_id'],
                'analysis_pattern_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analysis_pattern');
        Schema::dropIfExists('disinformation_patterns');
    }
};
