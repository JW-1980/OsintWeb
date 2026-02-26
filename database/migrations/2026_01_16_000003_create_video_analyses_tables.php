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
        // Video analyses table
        Schema::create('video_analyses', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // File information
            $table->string('original_filename', 255);
            $table->string('stored_path', 512);
            $table->unsignedBigInteger('file_size'); // Size in bytes
            $table->decimal('duration_seconds', 12, 3)->nullable(); // Duration with millisecond precision
            $table->unsignedInteger('frame_count')->nullable();

            // Video technical details
            $table->unsignedSmallInteger('resolution_width')->nullable();
            $table->unsignedSmallInteger('resolution_height')->nullable();
            $table->decimal('fps', 8, 4)->nullable(); // Frames per second
            $table->string('codec', 50)->nullable(); // e.g., h264, h265, vp9
            $table->string('container_format', 20)->nullable(); // e.g., mp4, mkv, webm

            // Metadata from ffprobe
            $table->json('metadata')->nullable(); // Full ffprobe output

            // EXIF/XMP metadata
            $table->json('exif_data')->nullable(); // Extracted EXIF/XMP data

            // GPS coordinates from metadata (spatial POINT)
            $table->geometry('gps_coordinates', 'point', 4326)->nullable();
            $table->decimal('gps_latitude', 10, 8)->nullable(); // For non-spatial queries
            $table->decimal('gps_longitude', 11, 8)->nullable();
            $table->unsignedSmallInteger('gps_accuracy_meters')->nullable();

            // Recording information from metadata
            $table->timestamp('recording_datetime')->nullable();
            $table->string('timezone', 50)->nullable();

            // Device/camera information
            $table->json('device_info')->nullable(); // Camera make, model, software, etc.

            // Analysis status
            $table->enum('analysis_status', [
                'pending',
                'processing',
                'completed',
                'failed',
            ])->default('pending');
            $table->text('error_message')->nullable();
            $table->unsignedTinyInteger('retry_count')->default(0);
            $table->timestamp('analysis_started_at')->nullable();
            $table->timestamp('analysis_completed_at')->nullable();

            // Thumbnail
            $table->string('thumbnail_path', 512)->nullable();

            // Optional relationship to events
            $table->foreignId('event_id')->nullable()->constrained()->nullOnDelete();

            // User who uploaded this video
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('analysis_status');
            $table->index('recording_datetime');
            $table->index('created_by');
            $table->index('event_id');
            $table->index('codec');
            $table->index('container_format');
            $table->index(['gps_latitude', 'gps_longitude']);
            // Note: spatial index requires NOT NULL, using regular index for nullable GPS
            $table->index(['gps_latitude', 'gps_longitude'], 'video_analyses_gps_index');
        });

        // Video keyframes table
        Schema::create('video_keyframes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('video_analysis_id')
                ->constrained('video_analyses')
                ->onDelete('cascade');

            // Frame position
            $table->unsignedInteger('frame_number');
            $table->decimal('timestamp_seconds', 12, 3); // Timestamp with millisecond precision

            // Stored files
            $table->string('stored_path', 512); // Full resolution extracted frame
            $table->string('thumbnail_path', 512)->nullable(); // Smaller thumbnail version

            // Scene change detection
            $table->decimal('scene_change_score', 5, 4)->nullable(); // 0-1 score of how different from previous
            $table->boolean('is_scene_change')->default(false); // Flagged as significant scene change

            // AI/ML analysis results
            $table->json('objects_detected')->nullable(); // [{label, confidence, bbox}]
            $table->json('faces_detected')->nullable(); // [{bbox, confidence, landmarks}]
            $table->text('ocr_text')->nullable(); // Extracted text from frame
            $table->json('ocr_regions')->nullable(); // [{text, bbox, confidence}]

            // Visual features
            $table->json('dominant_colors')->nullable(); // [{color, percentage}]
            $table->decimal('brightness', 5, 4)->nullable(); // Average brightness 0-1
            $table->decimal('contrast', 5, 4)->nullable(); // Contrast ratio
            $table->decimal('blur_score', 5, 4)->nullable(); // Blur detection score

            // Frame classification
            $table->string('frame_type', 20)->nullable(); // I-frame, P-frame, B-frame
            $table->boolean('is_representative')->default(false); // Marked as good representative frame

            // Additional metadata
            $table->json('metadata')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('frame_number');
            $table->index('timestamp_seconds');
            $table->index('is_scene_change');
            $table->index('is_representative');
            $table->index('scene_change_score');
            $table->unique(['video_analysis_id', 'frame_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('video_keyframes');
        Schema::dropIfExists('video_analyses');
    }
};
