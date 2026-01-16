<?php

declare(strict_types=1);

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
        Schema::create('flight_tracks', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('aircraft_id')->constrained('tracked_aircraft')->cascadeOnDelete();
            $table->string('callsign', 10)->nullable()->index()->comment('Flight callsign (if available)');
            $table->string('flight_number', 10)->nullable()->index()->comment('Commercial flight number');
            $table->string('departure_airport', 4)->nullable()->index()->comment('ICAO departure airport code');
            $table->string('arrival_airport', 4)->nullable()->index()->comment('ICAO arrival/destination airport code');
            $table->timestamp('departure_time')->nullable()->comment('Actual departure time');
            $table->timestamp('arrival_time')->nullable()->comment('Actual arrival time');
            $table->geometry('track_path', 'linestring', 4326)->nullable()->comment('Full flight path as LineString');
            $table->unsignedInteger('max_altitude_feet')->nullable()->comment('Maximum altitude reached');
            $table->decimal('avg_speed_knots', 6, 2)->nullable()->comment('Average ground speed');
            $table->decimal('max_speed_knots', 6, 2)->nullable()->comment('Maximum ground speed');
            $table->decimal('total_distance_nm', 8, 2)->nullable()->comment('Total distance in nautical miles');
            $table->unsignedInteger('position_count')->default(0)->comment('Number of position reports');
            $table->unsignedInteger('duration_minutes')->nullable()->comment('Flight duration in minutes');
            $table->foreignId('event_id')->nullable()->constrained('events')->nullOnDelete()->comment('Linked OSINT event');
            $table->enum('track_status', [
                'in_progress',
                'completed',
                'incomplete',
                'cancelled'
            ])->default('in_progress')->index();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable()->comment('Additional flight data');
            $table->timestamps();

            // Indexes
            $table->index(['aircraft_id', 'departure_time']);
            $table->index(['departure_airport', 'arrival_airport']);
            $table->index(['departure_time', 'arrival_time']);
        });

        // Add spatial index on track_path
        DB::statement('ALTER TABLE flight_tracks ADD SPATIAL INDEX flight_tracks_path_spatial(track_path)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flight_tracks');
    }
};
