<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('conflicts', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // Basic Information
            $table->string('name', 500)->index();
            $table->string('short_name', 100)->nullable();
            $table->text('alias_names')->nullable(); // Array stored as JSON

            // Classification
            $table->enum('conflict_type', [
                'CIVIL_WAR',
                'INTERSTATE',
                'INSURGENCY',
                'TERRORISM',
                'ETHNIC_CONFLICT',
                'BORDER_DISPUTE',
                'PROXY_WAR',
                'OTHER'
            ])->nullable()->index();

            $table->enum('intensity_level', [
                'high',
                'medium',
                'low',
                'frozen'
            ])->default('low')->index();

            // Geographic Information
            $table->foreignId('primary_country_id')->nullable()->constrained('countries')->nullOnDelete();
            $table->text('affected_countries')->nullable(); // Array of country IDs stored as JSON
            $table->string('region', 100)->nullable()->index();

            // Timeline
            $table->date('start_date')->index();
            $table->date('end_date')->nullable()->index();
            $table->boolean('is_active')->default(true)->index();

            // Casualty Estimates
            $table->json('estimated_casualties')->nullable();
            // {military: 10000, civilian: 5000, total: 15000, last_updated: '2024-12-01'}

            // Metadata
            $table->text('description')->nullable();
            $table->text('background')->nullable();

            // External Links
            $table->string('wikipedia_url', 500)->nullable();

            // Timestamps
            $table->timestamps();
            $table->softDeletes();
        });

        // Create composite indexes
        DB::statement('CREATE INDEX conflicts_active_intensity_idx ON conflicts(is_active DESC, intensity_level, start_date DESC)');
        DB::statement('CREATE INDEX conflicts_region_active_idx ON conflicts(region, is_active)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conflicts');
    }
};
