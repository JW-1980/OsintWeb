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
        Schema::create('port_calls', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // Vessel Reference
            $table->foreignId('vessel_id')
                ->constrained('tracked_vessels')
                ->cascadeOnDelete()
                ->comment('Reference to tracked vessel');

            // Port Information
            $table->string('port_name', 255)->comment('Name of the port');
            $table->string('port_code', 10)->nullable()->comment('UN/LOCODE or port code');
            $table->foreignId('port_country_id')
                ->nullable()
                ->constrained('countries')
                ->nullOnDelete()
                ->comment('Country where port is located');

            // Position
            $table->geometry('position', 'point', 4326)
                ->nullable()
                ->comment('Geographic position of port call');

            // Timing
            $table->timestamp('arrival_time')->nullable()->comment('Actual arrival time');
            $table->timestamp('departure_time')->nullable()->comment('Actual departure time');
            $table->unsignedInteger('duration_hours')
                ->nullable()
                ->comment('Duration of port call in hours');

            // Voyage Context
            $table->string('previous_port', 255)->nullable()->comment('Previous port of call');
            $table->string('next_port', 255)->nullable()->comment('Next port of call');

            // Port Call Type
            $table->enum('call_type', [
                'loading',
                'unloading',
                'bunkering',
                'repair',
                'crew_change',
                'transit',
                'unknown',
            ])->default('unknown')->comment('Purpose of port call');

            // Data Source
            $table->enum('data_source', [
                'marinetraffic',
                'vesselfinder',
                'ais_receiver',
                'myshiptracking',
                'port_authority',
                'manual',
            ])->default('manual')->comment('Source of port call data');

            // Additional Data
            $table->json('metadata')->nullable()->comment('Additional metadata');

            $table->timestamps();

            // Indexes
            $table->index('vessel_id');
            $table->index('port_name');
            $table->index('port_code');
            $table->index('port_country_id');
            $table->index('arrival_time');
            $table->index('departure_time');
            $table->index('call_type');
            $table->index('data_source');
            $table->index(['vessel_id', 'arrival_time']);

            // Note: position is nullable so spatial index cannot be used (MySQL requires NOT NULL)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('port_calls');
    }
};
