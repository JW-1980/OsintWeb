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
        Schema::create('geolocation_verifications', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();

            // Spatial coordinates
            $table->geometry('original_coordinates', 'point', 4326)
                ->comment('Original coordinates from the event');
            $table->geometry('verified_coordinates', 'point', 4326)
                ->nullable()
                ->comment('Verified coordinates after analysis');

            // Verification method
            $table->enum('verification_method', [
                'satellite_match',
                'landmark_match',
                'shadow_analysis',
                'metadata_extraction',
                'crowd_verified',
            ])->comment('Method used to verify the location');

            // Confidence scoring (0-100)
            $table->unsignedTinyInteger('confidence_score')
                ->default(0)
                ->comment('Confidence level of verification (0-100)');

            // Image references
            $table->string('satellite_image_url', 2048)
                ->nullable()
                ->comment('URL to satellite/aerial imagery used');
            $table->string('ground_image_url', 2048)
                ->nullable()
                ->comment('URL to ground-level image being verified');

            // Analysis data stored as JSON
            $table->json('landmark_matches')
                ->nullable()
                ->comment('Array of matched landmarks with coordinates and confidence');
            $table->json('shadow_analysis_data')
                ->nullable()
                ->comment('Shadow analysis results: sun angle, azimuth, estimated time');
            $table->json('metadata')
                ->nullable()
                ->comment('Additional metadata from EXIF or other sources');

            // Verification notes
            $table->text('verification_notes')
                ->nullable()
                ->comment('Human-readable notes about the verification');

            // Verification tracking
            $table->foreignId('verified_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('verified_at')->nullable();

            // Status workflow
            $table->enum('status', [
                'pending',
                'verified',
                'disputed',
                'rejected',
            ])->default('pending');

            // Distance calculation cache
            $table->decimal('distance_from_original', 12, 2)
                ->nullable()
                ->comment('Distance in meters between original and verified coordinates');

            $table->timestamps();
            $table->softDeletes();

            // Indexes for common queries
            $table->index('event_id');
            $table->index('verification_method');
            $table->index('confidence_score');
            $table->index('status');
            $table->index('verified_by');
            $table->index('verified_at');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('geolocation_verifications');
    }
};
