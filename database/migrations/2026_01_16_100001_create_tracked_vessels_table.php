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
        Schema::create('tracked_vessels', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // Vessel Identification
            $table->string('mmsi', 9)->unique()->comment('Maritime Mobile Service Identity - 9 digits');
            $table->string('imo_number', 10)->nullable()->comment('IMO ship identification number');
            $table->string('name', 255)->comment('Vessel name');
            $table->string('callsign', 10)->nullable()->comment('Radio callsign');

            // Vessel Classification
            $table->enum('vessel_type', [
                'cargo',
                'tanker',
                'passenger',
                'fishing',
                'military',
                'tug',
                'yacht',
                'other',
            ])->default('other')->comment('Primary vessel type classification');
            $table->string('vessel_subtype', 100)->nullable()->comment('Detailed vessel subtype (e.g., bulk carrier, container ship)');

            // Flag and Registration
            $table->foreignId('flag_country_id')
                ->nullable()
                ->constrained('countries')
                ->nullOnDelete()
                ->comment('Flag state country');

            // Physical Specifications
            $table->decimal('length_meters', 8, 2)->nullable()->comment('Length overall in meters');
            $table->decimal('width_meters', 8, 2)->nullable()->comment('Beam/width in meters');
            $table->decimal('draft_meters', 6, 2)->nullable()->comment('Maximum draft in meters');
            $table->unsignedInteger('gross_tonnage')->nullable()->comment('Gross tonnage (GT)');
            $table->unsignedInteger('deadweight_tonnage')->nullable()->comment('Deadweight tonnage (DWT)');
            $table->unsignedSmallInteger('build_year')->nullable()->comment('Year of construction');

            // Ownership
            $table->string('owner', 255)->nullable()->comment('Registered owner name');
            $table->string('operator', 255)->nullable()->comment('Commercial operator name');

            // OSINT Classification
            $table->boolean('is_military')->default(false)->comment('Military or government vessel');
            $table->boolean('is_monitored')->default(true)->comment('Active monitoring status');
            $table->unsignedTinyInteger('monitoring_priority')
                ->default(3)
                ->comment('Priority 1-5 (1=highest, 5=lowest)');

            // Additional Data
            $table->text('notes')->nullable()->comment('Analyst notes about the vessel');
            $table->json('metadata')->nullable()->comment('Additional metadata (photos, links, etc.)');

            // User Management
            $table->foreignId('added_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->comment('User who added this vessel');

            $table->timestamps();
            $table->softDeletes();

            // Indexes for common queries
            $table->index('mmsi');
            $table->index('imo_number');
            $table->index('name');
            $table->index('vessel_type');
            $table->index('flag_country_id');
            $table->index('is_military');
            $table->index('is_monitored');
            $table->index('monitoring_priority');
            $table->index('added_by');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracked_vessels');
    }
};
