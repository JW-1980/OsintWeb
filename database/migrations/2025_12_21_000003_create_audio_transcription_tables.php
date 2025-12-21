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
        // Audio files table
        Schema::create('audio_files', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('file_path');
            $table->string('file_name');
            $table->string('original_name');
            $table->string('mime_type', 50);
            $table->unsignedBigInteger('file_size'); // bytes
            $table->unsignedInteger('duration')->nullable(); // seconds
            $table->unsignedInteger('sample_rate')->nullable(); // Hz
            $table->unsignedTinyInteger('channels')->nullable(); // 1=mono, 2=stereo
            $table->string('bitrate', 20)->nullable();
            $table->string('codec', 50)->nullable();
            $table->string('language', 10)->nullable(); // ISO 639-1
            $table->enum('status', ['pending', 'processing', 'ready', 'error'])->default('pending');
            $table->enum('visibility', ['public', 'private', 'unlisted'])->default('private');
            $table->boolean('allow_download')->default(false);
            $table->unsignedInteger('play_count')->default(0);
            $table->json('metadata')->nullable(); // Additional audio metadata
            $table->json('waveform_data')->nullable(); // Pre-computed waveform for visualization
            $table->string('thumbnail_path')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('visibility');
            $table->index(['user_id', 'status']);
            $table->index('created_at');
        });

        // Transcriptions table
        Schema::create('transcriptions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('audio_file_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Transcriber
            $table->enum('type', ['manual', 'ai', 'hybrid'])->default('manual');
            $table->string('language', 10)->default('en'); // ISO 639-1
            $table->string('title')->nullable();
            $table->longText('full_text')->nullable(); // Complete transcript as plain text
            $table->enum('status', ['draft', 'in_progress', 'completed', 'verified', 'published'])
                ->default('draft');
            $table->boolean('is_primary')->default(false); // Primary transcript for the audio
            $table->float('confidence_score')->nullable(); // AI confidence (0-1)
            $table->string('ai_model')->nullable(); // Model used for AI transcription
            $table->string('ai_provider')->nullable(); // openrouter, whisper, etc.
            $table->unsignedInteger('word_count')->default(0);
            $table->unsignedInteger('segment_count')->default(0);
            $table->json('speaker_labels')->nullable(); // Speaker identification data
            $table->json('metadata')->nullable();
            $table->timestamp('transcription_started_at')->nullable();
            $table->timestamp('transcription_completed_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('type');
            $table->index('is_primary');
            $table->index(['audio_file_id', 'is_primary']);
        });

        // Transcript segments (timed text segments)
        Schema::create('transcript_segments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transcription_id')->constrained()->onDelete('cascade');
            $table->unsignedInteger('segment_index'); // Order within transcription
            $table->float('start_time', 10, 3); // Start time in seconds (millisecond precision)
            $table->float('end_time', 10, 3); // End time in seconds
            $table->text('text'); // Segment text content
            $table->string('speaker_id', 50)->nullable(); // Speaker identifier
            $table->string('speaker_name')->nullable(); // Human-readable speaker name
            $table->float('confidence')->nullable(); // Segment confidence (0-1)
            $table->json('words')->nullable(); // Word-level timing data
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['transcription_id', 'segment_index']);
            $table->index(['transcription_id', 'start_time']);
            $table->index('speaker_id');
        });

        // Transcript revisions (edit history)
        Schema::create('transcript_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transcription_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->unsignedInteger('revision_number');
            $table->longText('full_text'); // Full text at this revision
            $table->json('changes')->nullable(); // Diff from previous revision
            $table->string('comment')->nullable(); // Editor's comment
            $table->timestamp('created_at');

            $table->unique(['transcription_id', 'revision_number']);
            $table->index('created_at');
        });

        // Speaker profiles (for speaker identification)
        Schema::create('speakers', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Created by
            $table->string('name');
            $table->string('identifier', 50)->unique(); // Short identifier
            $table->text('description')->nullable();
            $table->string('organization')->nullable();
            $table->string('role')->nullable();
            $table->string('avatar_url')->nullable();
            $table->json('voice_samples')->nullable(); // References to audio samples
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('identifier');
        });

        // Audio-Event linking (link audio to conflict events)
        Schema::create('audio_event', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audio_file_id')->constrained()->onDelete('cascade');
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->string('relationship_type', 50)->default('evidence'); // evidence, background, interview
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['audio_file_id', 'event_id']);
        });

        // Transcription jobs (for async AI processing)
        Schema::create('transcription_jobs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('audio_file_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('transcription_id')->nullable()->constrained()->nullOnDelete();
            $table->string('provider', 50); // openrouter, whisper, etc.
            $table->string('model')->nullable();
            $table->enum('status', ['queued', 'processing', 'completed', 'failed', 'cancelled'])
                ->default('queued');
            $table->string('language', 10)->nullable();
            $table->json('options')->nullable(); // Provider-specific options
            $table->text('error_message')->nullable();
            $table->string('external_job_id')->nullable(); // Provider's job ID
            $table->unsignedInteger('progress')->default(0); // 0-100
            $table->decimal('cost', 10, 6)->nullable(); // Cost in USD
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('provider');
            $table->index(['audio_file_id', 'status']);
        });

        // Audio collections (playlists/folders)
        Schema::create('audio_collections', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('visibility', ['public', 'private', 'unlisted'])->default('private');
            $table->string('cover_image_path')->nullable();
            $table->unsignedInteger('audio_count')->default(0);
            $table->unsignedInteger('total_duration')->default(0); // Total seconds
            $table->timestamps();
            $table->softDeletes();

            $table->index('visibility');
        });

        // Audio-Collection pivot
        Schema::create('audio_collection_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audio_collection_id')->constrained()->onDelete('cascade');
            $table->foreignId('audio_file_id')->constrained()->onDelete('cascade');
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->unique(['audio_collection_id', 'audio_file_id']);
            $table->index(['audio_collection_id', 'position']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audio_collection_items');
        Schema::dropIfExists('audio_collections');
        Schema::dropIfExists('transcription_jobs');
        Schema::dropIfExists('audio_event');
        Schema::dropIfExists('speakers');
        Schema::dropIfExists('transcript_revisions');
        Schema::dropIfExists('transcript_segments');
        Schema::dropIfExists('transcriptions');
        Schema::dropIfExists('audio_files');
    }
};
