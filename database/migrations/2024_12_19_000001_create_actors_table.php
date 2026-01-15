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
        Schema::create('actors', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // Basic Information
            $table->string('name', 500)->index();
            $table->string('short_name', 100)->nullable()->index();
            $table->json('alias_names')->nullable();

            // Actor Classification
            $table->enum('actor_type', [
                'STATE',
                'SEPARATIST',
                'INSURGENT',
                'TERRORIST',
                'MILITIA',
                'PMC',
                'CARTEL',
                'REBEL',
                'ETHNIC_MILITIA',
                'GOVERNMENT_FORCES',
                'COALITION',
                'PROXY'
            ])->index();

            // Geographic Information
            $table->foreignId('country_id')->nullable()->constrained('countries')->nullOnDelete();
            $table->string('primary_region', 100)->nullable()->index();
            $table->json('operational_areas')->nullable();

            // Classification Flags
            $table->boolean('is_state_actor')->default(false)->index();
            $table->boolean('is_designated_terrorist')->default(false)->index();
            $table->json('designations')->nullable(); // {us: true, eu: false, un: true, etc.}

            // Activity Status
            $table->boolean('is_active_in_conflict')->default(false)->index();
            $table->enum('activity_level', ['high', 'medium', 'low', 'inactive'])->default('inactive')->index();
            $table->date('last_activity_date')->nullable()->index();

            // Autocomplete Priority
            $table->integer('autocomplete_priority')->default(0)->index();
            $table->decimal('priority_score', 5, 2)->default(0.0)->index();

            // Visual Elements - FLAG EMOJI for display
            $table->string('flag_emoji', 10)->nullable(); // Country flags (🇺🇸) or group icons (🏴‍☠️)
            $table->string('logo_url', 500)->nullable();
            $table->string('flag_url', 500)->nullable();
            $table->string('color_hex', 7)->nullable();
            $table->string('icon', 100)->nullable();

            // Metadata
            $table->text('description')->nullable();
            $table->date('founded_date')->nullable();
            $table->date('dissolved_date')->nullable();
            $table->foreignId('successor_id')->nullable();
            $table->foreignId('parent_organization_id')->nullable();

            // External Links
            $table->string('wikipedia_url', 500)->nullable();
            $table->string('official_website', 500)->nullable();

            // Timestamps
            $table->timestamps();
            $table->softDeletes();

            // Full-text search index
            $table->fullText(['name', 'short_name', 'description'], 'actors_search_idx');
        });

        // Add composite indexes
        Schema::table('actors', function (Blueprint $table) {
            $table->index(['is_active_in_conflict', 'priority_score', 'name'], 'actors_active_priority_idx');
            $table->index(['actor_type', 'is_active_in_conflict', 'activity_level'], 'actors_type_active_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('actors');
    }
};
