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
        Schema::create('vessel_alerts', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // User Reference
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete()
                ->comment('User who created the alert');

            // Alert Type
            $table->enum('alert_type', [
                'vessel_spotted',
                'entered_zone',
                'exited_zone',
                'port_call',
                'dark_activity',
                'sts_transfer',
                'speed_anomaly',
                'route_deviation',
            ])->comment('Type of alert trigger');

            // Alert Name and Description
            $table->string('name', 255)->comment('Alert name for identification');
            $table->text('description')->nullable()->comment('Alert description');

            // Vessel Filter (optional - specific vessel or all)
            $table->foreignId('vessel_id')
                ->nullable()
                ->constrained('tracked_vessels')
                ->cascadeOnDelete()
                ->comment('Specific vessel to monitor, null for all');

            // Geographic Zone (for zone-based alerts)
            $table->geometry('zone_polygon', 'polygon', 4326)
                ->nullable()
                ->comment('Geographic zone for area-based alerts');

            // Alert Configuration
            $table->json('conditions')->nullable()->comment('Additional conditions (speed thresholds, vessel types, etc.)');

            // Notification Settings
            $table->json('notification_channels')
                ->nullable()
                ->comment('Channels to notify (email, database, webhook)');

            // Alert Status
            $table->boolean('is_active')->default(true)->comment('Whether alert is currently active');
            $table->timestamp('last_triggered_at')->nullable()->comment('Last time alert was triggered');
            $table->unsignedInteger('trigger_count')->default(0)->comment('Number of times alert has triggered');

            // Cooldown to prevent spam
            $table->unsignedInteger('cooldown_minutes')
                ->default(60)
                ->comment('Minimum minutes between triggers');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('user_id');
            $table->index('alert_type');
            $table->index('vessel_id');
            $table->index('is_active');
            $table->index('last_triggered_at');
            $table->index(['user_id', 'is_active']);
            $table->index(['alert_type', 'is_active']);

            // Spatial index omitted: zone_polygon is nullable, incompatible with MariaDB spatial indexes
        });

        // Create table for alert trigger history
        Schema::create('vessel_alert_triggers', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->foreignId('alert_id')
                ->constrained('vessel_alerts')
                ->cascadeOnDelete();

            $table->foreignId('vessel_id')
                ->nullable()
                ->constrained('tracked_vessels')
                ->nullOnDelete();

            $table->foreignId('position_id')
                ->nullable()
                ->constrained('vessel_positions')
                ->nullOnDelete();

            $table->geometry('trigger_position', 'point', 4326)
                ->nullable()
                ->comment('Position where alert was triggered');

            $table->json('trigger_data')->nullable()->comment('Data that triggered the alert');
            $table->text('message')->nullable()->comment('Alert message sent');

            $table->boolean('was_notified')->default(false);
            $table->timestamp('triggered_at');
            $table->timestamp('acknowledged_at')->nullable();
            $table->foreignId('acknowledged_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index('alert_id');
            $table->index('vessel_id');
            $table->index('triggered_at');
            $table->index('was_notified');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vessel_alert_triggers');
        Schema::dropIfExists('vessel_alerts');
    }
};
