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
        Schema::create('vessel_positions', function (Blueprint $table) {
            $table->id();

            // Vessel Reference
            $table->foreignId('vessel_id')
                ->constrained('tracked_vessels')
                ->cascadeOnDelete()
                ->comment('Reference to tracked vessel');

            // Spatial Position
            $table->geometry('position', 'point', 4326)
                ->comment('Geographic position (longitude, latitude)');

            // Navigation Data
            $table->decimal('speed_knots', 6, 2)->nullable()->comment('Speed over ground in knots');
            $table->decimal('course', 5, 2)->nullable()->comment('Course over ground in degrees (0-360)');
            $table->decimal('heading', 5, 2)->nullable()->comment('True heading in degrees (0-360)');

            // Navigation Status
            $table->enum('nav_status', [
                'underway_using_engine',
                'at_anchor',
                'not_under_command',
                'restricted_manoeuvrability',
                'constrained_by_draught',
                'moored',
                'aground',
                'engaged_in_fishing',
                'underway_sailing',
                'reserved_hsc',
                'reserved_wig',
                'reserved_1',
                'reserved_2',
                'reserved_3',
                'ais_sart',
                'not_defined',
            ])->default('not_defined')->comment('AIS navigation status');

            // Voyage Information
            $table->string('destination', 255)->nullable()->comment('Reported destination port');
            $table->timestamp('eta')->nullable()->comment('Estimated time of arrival');
            $table->decimal('draught_current', 5, 2)->nullable()->comment('Current draught in meters');

            // Timestamp
            $table->timestamp('recorded_at')->comment('Time when position was recorded by AIS');

            // Data Source
            $table->enum('data_source', [
                'marinetraffic',
                'vesselfinder',
                'ais_receiver',
                'myshiptracking',
                'fleetmon',
                'manual',
            ])->default('manual')->comment('Source of position data');

            // Raw Data Storage
            $table->json('raw_data')->nullable()->comment('Original API response or raw AIS message');

            $table->timestamps();

            // Indexes for performance
            $table->index('vessel_id');
            $table->index('recorded_at');
            $table->index('data_source');
            $table->index('nav_status');
            $table->index(['vessel_id', 'recorded_at']);

            // Spatial index
            $table->spatialIndex('position');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vessel_positions');
    }
};
