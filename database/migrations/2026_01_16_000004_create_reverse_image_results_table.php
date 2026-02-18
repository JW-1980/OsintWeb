<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration for reverse_image_results table
 *
 * Schema:
 * - id: Primary key (bigint unsigned auto-increment)
 * - search_id: Foreign key to reverse_image_searches table (bigint unsigned)
 * - engine: Search engine that returned this result (enum: google, bing, tineye, yandex, baidu)
 * - result_url: URL where this image was found (varchar 2048)
 * - result_title: Title of the page/post where image was found (varchar 500, nullable)
 * - result_snippet: Text snippet from the page (text, nullable)
 * - thumbnail_url: URL to thumbnail preview (varchar 2048, nullable)
 * - match_type: Type of match found (enum: exact, similar, partial)
 * - similarity_score: Similarity percentage 0-100 if provided by engine (tinyint unsigned, nullable)
 * - first_seen_date: Earliest date this result/image was indexed (date, nullable)
 * - source_domain: Domain name where image was found (varchar 255)
 * - page_title: Full page title (varchar 500, nullable)
 * - is_potentially_manipulated: Flag for suspected image manipulation (boolean, default false)
 * - manipulation_indicators: JSON array of manipulation flags (json, nullable)
 * - image_dimensions: Dimensions of the found image (varchar 50, nullable) e.g., "1920x1080"
 * - raw_response: Full JSON response from the search engine (json, nullable)
 * - created_at: Record creation timestamp (timestamp)
 * - updated_at: Record update timestamp (timestamp)
 *
 * Indexes:
 * - Primary key on id
 * - Index on search_id for result retrieval
 * - Index on engine for filtering by source
 * - Index on source_domain for domain analysis
 * - Index on match_type for filtering exact matches
 * - Index on first_seen_date for chronological analysis
 * - Index on similarity_score for quality filtering
 * - Composite index on (search_id, engine) for engine-specific results
 *
 * Foreign Keys:
 * - search_id -> reverse_image_searches.id (cascade on delete)
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reverse_image_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('search_id')
                ->constrained('reverse_image_searches')
                ->cascadeOnDelete();
            $table->enum('engine', ['google', 'bing', 'tineye', 'yandex', 'baidu'])
                ->index();
            $table->string('result_url', 2048);
            $table->string('result_title', 500)->nullable();
            $table->text('result_snippet')->nullable();
            $table->string('thumbnail_url', 2048)->nullable();
            $table->enum('match_type', ['exact', 'similar', 'partial'])
                ->default('similar')
                ->index();
            $table->unsignedTinyInteger('similarity_score')->nullable()
                ->comment('Similarity percentage 0-100');
            $table->date('first_seen_date')->nullable()
                ->comment('Earliest indexing date for this result');
            $table->string('source_domain', 255)->index();
            $table->string('page_title', 500)->nullable();
            $table->boolean('is_potentially_manipulated')->default(false)
                ->index();
            $table->json('manipulation_indicators')->nullable()
                ->comment('Detected manipulation flags');
            $table->string('image_dimensions', 50)->nullable()
                ->comment('e.g., 1920x1080');
            $table->json('raw_response')->nullable()
                ->comment('Full engine response for debugging');
            $table->timestamps();

            // Composite indexes for common queries
            $table->index(['search_id', 'engine']);
            $table->index(['search_id', 'match_type']);
            $table->index(['search_id', 'similarity_score']);
            $table->index('first_seen_date');

            // Hash column for URL uniqueness (URLs too long for standard unique index)
            $table->string('result_url_hash', 64)->storedAs('SHA2(result_url, 256)');
            $table->unique(['search_id', 'engine', 'result_url_hash'], 'unique_search_engine_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reverse_image_results');
    }
};
