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
        Schema::create('flight_alerts', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100)->comment('User-friendly alert name');
            $table->text('description')->nullable();
            $table->enum('alert_type', [
                'aircraft_spotted',     // Specific aircraft detected
                'entered_zone',         // Any monitored aircraft enters zone
                'exited_zone',          // Aircraft leaves monitored zone
                'unusual_activity',     // Detected unusual patterns
                'squawk_change',        // Emergency squawk codes (7500, 7600, 7700)
                'altitude_threshold',   // Below/above altitude threshold
                'military_activity',    // Military aircraft activity
                'new_aircraft'          // Previously unseen aircraft
            ])->index();
            $table->foreignId('aircraft_id')->nullable()->constrained('tracked_aircraft')->nullOnDelete()->comment('Specific aircraft to monitor');
            $table->geometry('zone_polygon', 'polygon', 4326)->nullable()->comment('Geographic zone to monitor');
            $table->unsignedInteger('altitude_min')->nullable()->comment('Minimum altitude threshold (feet)');
            $table->unsignedInteger('altitude_max')->nullable()->comment('Maximum altitude threshold (feet)');
            $table->json('squawk_codes')->nullable()->comment('Specific squawk codes to alert on');
            $table->json('aircraft_types')->nullable()->comment('Aircraft types to include/exclude');
            $table->boolean('military_only')->default(false)->comment('Only alert on military aircraft');
            $table->boolean('is_active')->default(true)->index();
            $table->enum('notification_channels', ['database', 'email', 'both'])->default('database');
            $table->unsignedInteger('cooldown_minutes')->default(15)->comment('Minimum time between repeated alerts');
            $table->timestamp('last_triggered_at')->nullable();
            $table->unsignedInteger('trigger_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['user_id', 'is_active']);
            $table->index(['alert_type', 'is_active']);
        });

        // Add spatial index on zone_polygon
        DB::statement('ALTER TABLE flight_alerts ADD SPATIAL INDEX flight_alerts_zone_spatial(zone_polygon)');

        // Create pivot table for triggered alerts
        Schema::create('flight_alert_triggers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alert_id')->constrained('flight_alerts')->cascadeOnDelete();
            $table->foreignId('aircraft_id')->nullable()->constrained('tracked_aircraft')->nullOnDelete();
            $table->foreignId('position_id')->nullable()->constrained('flight_positions')->nullOnDelete();
            $table->foreignId('track_id')->nullable()->constrained('flight_tracks')->nullOnDelete();
            $table->json('trigger_data')->nullable()->comment('Data that triggered the alert');
            $table->boolean('is_read')->default(false)->index();
            $table->timestamp('triggered_at');
            $table->timestamps();

            $table->index(['alert_id', 'triggered_at']);
            $table->index(['aircraft_id', 'triggered_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flight_alert_triggers');
        Schema::dropIfExists('flight_alerts');
    }
};
