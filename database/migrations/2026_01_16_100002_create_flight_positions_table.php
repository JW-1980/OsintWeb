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
        Schema::create('flight_positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aircraft_id')->constrained('tracked_aircraft')->cascadeOnDelete();
            $table->geometry('position', 'point', 4326)->comment('Geographic position of aircraft');
            $table->decimal('latitude', 10, 7)->comment('Position latitude');
            $table->decimal('longitude', 10, 7)->comment('Position longitude');
            $table->unsignedInteger('altitude_feet')->nullable()->comment('Barometric altitude in feet');
            $table->unsignedInteger('altitude_meters')->nullable()->comment('Altitude in meters (calculated)');
            $table->unsignedSmallInteger('ground_speed_knots')->nullable()->comment('Ground speed in knots');
            $table->smallInteger('vertical_rate')->nullable()->comment('Vertical rate in feet per minute (positive=climbing)');
            $table->decimal('heading', 5, 2)->nullable()->comment('Aircraft heading in degrees (0-360)');
            $table->string('squawk', 4)->nullable()->index()->comment('Transponder squawk code');
            $table->boolean('on_ground')->default(false)->index()->comment('Whether aircraft is on ground');
            $table->timestamp('recorded_at')->index()->comment('Timestamp from ADS-B data');
            $table->enum('data_source', [
                'adsb_exchange',
                'opensky',
                'flightradar24',
                'flightaware',
                'local_receiver',
                'manual'
            ])->default('opensky')->index();
            $table->json('raw_data')->nullable()->comment('Original API response data');
            $table->timestamps();

            // Indexes for efficient querying
            $table->index(['aircraft_id', 'recorded_at']);
            $table->index(['recorded_at', 'data_source']);
            $table->index(['latitude', 'longitude']);
        });

        // Add spatial index on position
        DB::statement('ALTER TABLE flight_positions ADD SPATIAL INDEX flight_positions_position_spatial(position)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flight_positions');
    }
};
