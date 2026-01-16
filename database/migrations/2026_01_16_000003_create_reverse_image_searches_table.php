<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration for reverse_image_searches table
 *
 * Schema:
 * - id: Primary key (bigint unsigned auto-increment)
 * - uuid: Public unique identifier (uuid, indexed)
 * - image_path: Local storage path for the uploaded/cached image (varchar 500)
 * - image_hash: Perceptual hash (pHash) for image deduplication and similarity (varchar 64, indexed)
 * - image_url: Original URL if the image was fetched from the web (varchar 2048, nullable)
 * - search_status: Current status of the search operation (enum: pending, searching, completed, failed)
 * - created_by: Foreign key to users table (bigint unsigned)
 * - event_id: Optional foreign key to events table (bigint unsigned, nullable)
 * - total_results: Count of total results found across all engines (int unsigned, default 0)
 * - engines_searched: JSON array of engines that were queried (json)
 * - engines_completed: JSON array of engines that completed successfully (json)
 * - earliest_found_date: Earliest date this image was found online (date, nullable)
 * - error_message: Error message if search failed (text, nullable)
 * - search_started_at: Timestamp when search began (timestamp, nullable)
 * - search_completed_at: Timestamp when search finished (timestamp, nullable)
 * - metadata: Additional metadata (json, nullable) - stores image dimensions, format, etc.
 * - created_at: Record creation timestamp (timestamp)
 * - updated_at: Record update timestamp (timestamp)
 *
 * Indexes:
 * - Primary key on id
 * - Unique index on uuid
 * - Index on image_hash for deduplication lookups
 * - Index on created_by for user's search history
 * - Index on event_id for event-related searches
 * - Index on search_status for queue processing
 * - Index on created_at for chronological ordering
 *
 * Foreign Keys:
 * - created_by -> users.id (cascade on delete)
 * - event_id -> events.id (set null on delete)
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reverse_image_searches', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('image_path', 500);
            $table->string('image_hash', 64)->index()->comment('Perceptual hash for deduplication');
            $table->string('image_url', 2048)->nullable()->comment('Original URL if from web');
            $table->enum('search_status', ['pending', 'searching', 'completed', 'failed'])
                ->default('pending')
                ->index();
            $table->foreignId('created_by')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignId('event_id')
                ->nullable()
                ->constrained('events')
                ->nullOnDelete();
            $table->unsignedInteger('total_results')->default(0);
            $table->json('engines_searched')->nullable();
            $table->json('engines_completed')->nullable();
            $table->date('earliest_found_date')->nullable()
                ->comment('Earliest date image appeared online');
            $table->text('error_message')->nullable();
            $table->timestamp('search_started_at')->nullable();
            $table->timestamp('search_completed_at')->nullable();
            $table->json('metadata')->nullable()
                ->comment('Image dimensions, format, file size, etc.');
            $table->timestamps();

            // Additional indexes for performance
            $table->index('created_at');
            $table->index(['created_by', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reverse_image_searches');
    }
};
