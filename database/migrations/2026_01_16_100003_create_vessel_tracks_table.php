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
        Schema::create('vessel_tracks', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // Vessel Reference
            $table->foreignId('vessel_id')
                ->constrained('tracked_vessels')
                ->cascadeOnDelete()
                ->comment('Reference to tracked vessel');

            // Port Information
            $table->string('departure_port', 255)->nullable()->comment('Port of departure');
            $table->string('arrival_port', 255)->nullable()->comment('Port of arrival/destination');

            // Timing
            $table->timestamp('departure_time')->nullable()->comment('Departure timestamp');
            $table->timestamp('arrival_time')->nullable()->comment('Arrival timestamp');

            // Track Geometry
            $table->geometry('track_path', 'linestring', 4326)
                ->nullable()
                ->comment('Complete track as LineString geometry');

            // Track Statistics
            $table->decimal('total_distance_nm', 10, 2)->nullable()->comment('Total distance in nautical miles');
            $table->decimal('avg_speed', 6, 2)->nullable()->comment('Average speed in knots');
            $table->unsignedInteger('position_count')->default(0)->comment('Number of position points in track');

            // Voyage Classification
            $table->enum('voyage_type', [
                'laden',
                'ballast',
                'unknown',
            ])->default('unknown')->comment('Whether vessel is loaded or empty');

            // Event Correlation
            $table->foreignId('event_id')
                ->nullable()
                ->constrained('events')
                ->nullOnDelete()
                ->comment('Correlated OSINT event');

            $table->timestamps();

            // Indexes
            $table->index('vessel_id');
            $table->index('departure_port');
            $table->index('arrival_port');
            $table->index('departure_time');
            $table->index('arrival_time');
            $table->index('voyage_type');
            $table->index('event_id');
            $table->index(['vessel_id', 'departure_time']);

            // Spatial index omitted: track_path is nullable, incompatible with MariaDB spatial indexes
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vessel_tracks');
    }
};
