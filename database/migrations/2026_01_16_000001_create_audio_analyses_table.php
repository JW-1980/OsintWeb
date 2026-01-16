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
        Schema::create('audio_analyses', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('audio_file_id')->constrained()->onDelete('cascade');
            $table->foreignId('event_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');

            // Generated visualization paths
            $table->string('spectrogram_path')->nullable();
            $table->string('waveform_path')->nullable();

            // Audio technical metadata
            $table->decimal('duration_seconds', 12, 3)->nullable(); // Duration with millisecond precision
            $table->unsignedInteger('sample_rate')->nullable(); // Hz (e.g., 44100, 48000)
            $table->unsignedTinyInteger('channels')->nullable(); // 1=mono, 2=stereo
            $table->unsignedSmallInteger('bit_depth')->nullable(); // 16, 24, 32 bit
            $table->string('codec', 50)->nullable(); // mp3, aac, flac, etc.
            $table->string('container_format', 50)->nullable(); // mp4, ogg, wav, etc.

            // Amplitude analysis
            $table->decimal('average_amplitude', 8, 6)->nullable(); // 0.0 to 1.0
            $table->decimal('peak_amplitude', 8, 6)->nullable(); // 0.0 to 1.0
            $table->decimal('noise_floor_db', 8, 2)->nullable(); // dB (negative value typically)

            // Analysis results (JSON)
            $table->json('detected_edits')->nullable(); // [{timestamp, type, confidence, description}]
            $table->json('frequency_analysis')->nullable(); // {dominant_frequencies, spectrum_data, harmonics}
            $table->json('voice_activity_regions')->nullable(); // [{start, end, speaker_id, confidence}]
            $table->json('silence_regions')->nullable(); // [{start, end, duration}]

            // Status and scoring
            $table->enum('analysis_status', ['pending', 'processing', 'completed', 'failed'])
                ->default('pending');
            $table->unsignedTinyInteger('manipulation_score')->nullable(); // 0-100, higher = more likely edited
            $table->text('analysis_notes')->nullable(); // Human-readable summary

            // Processing metadata
            $table->json('processing_metadata')->nullable(); // {tools_used, versions, processing_time}
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('analysis_status');
            $table->index('manipulation_score');
            $table->index(['audio_file_id', 'analysis_status']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audio_analyses');
    }
};
