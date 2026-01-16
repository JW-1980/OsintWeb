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
        Schema::create('event_weather', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('weather_data_id')->constrained('weather_data')->cascadeOnDelete();

            // Linkage metadata
            $table->enum('link_type', ['auto', 'manual', 'verified'])->default('auto')
                ->comment('How the weather was linked: auto=system matched, manual=user linked, verified=verified link');
            $table->decimal('distance_km', 8, 3)->nullable()
                ->comment('Distance in km between event and weather observation point');
            $table->integer('time_diff_minutes')->nullable()
                ->comment('Time difference in minutes between event and weather observation');

            // Verification fields for shadow/chronolocation
            $table->boolean('shadow_verified')->default(false)
                ->comment('Whether shadow analysis was performed');
            $table->decimal('claimed_shadow_angle', 5, 2)->nullable()
                ->comment('Shadow angle claimed in image/video');
            $table->decimal('calculated_shadow_angle', 5, 2)->nullable()
                ->comment('Calculated expected shadow angle based on sun position');
            $table->decimal('shadow_angle_deviation', 5, 2)->nullable()
                ->comment('Deviation between claimed and calculated shadow angles');
            $table->boolean('shadow_matches')->nullable()
                ->comment('Whether shadow angle matches expected (within tolerance)');
            $table->text('verification_notes')->nullable();

            // User who created/verified the link
            $table->foreignId('linked_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('linked_at')->nullable();

            $table->timestamps();

            // Unique constraint to prevent duplicate links
            $table->unique(['event_id', 'weather_data_id'], 'event_weather_unique');

            // Indexes
            $table->index('event_id');
            $table->index('weather_data_id');
            $table->index('link_type');
            $table->index('shadow_verified');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_weather');
    }
};
