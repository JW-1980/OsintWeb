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
        Schema::create('document_analyses', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // File information
            $table->string('original_filename');
            $table->string('stored_path');
            $table->enum('file_type', ['image', 'pdf', 'scan', 'screenshot'])->default('image');
            $table->unsignedBigInteger('file_size'); // bytes
            $table->unsignedSmallInteger('page_count')->nullable(); // for PDFs

            // Language detection
            $table->string('language_detected', 10)->nullable(); // ISO 639-1 code
            $table->json('languages_present')->nullable(); // Array of detected languages with confidence

            // Extracted text
            $table->longText('extracted_text')->nullable();
            $table->unsignedTinyInteger('text_confidence')->nullable(); // 0-100 overall confidence
            $table->json('text_blocks')->nullable(); // [{x, y, width, height, text, confidence}]

            // Entity extraction (NER)
            $table->json('detected_entities')->nullable(); // {names: [], dates: [], locations: [], organizations: []}

            // Document classification
            $table->enum('document_type_detected', [
                'invoice',
                'letter',
                'report',
                'id_document',
                'contract',
                'receipt',
                'form',
                'certificate',
                'news_article',
                'screenshot',
                'handwritten_note',
                'unknown'
            ])->default('unknown');

            // Additional metadata
            $table->json('metadata')->nullable(); // Any additional extracted data (dates, amounts, etc.)
            $table->json('tables_extracted')->nullable(); // Structured table data [{headers: [], rows: []}]
            $table->boolean('handwriting_detected')->default(false);

            // Processing status
            $table->enum('analysis_status', ['pending', 'processing', 'completed', 'failed'])
                ->default('pending');
            $table->unsignedInteger('processing_time_ms')->nullable();
            $table->text('error_message')->nullable();

            // Relationships
            $table->foreignId('event_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('analysis_status');
            $table->index('file_type');
            $table->index('document_type_detected');
            $table->index('language_detected');
            $table->index('created_by');
            $table->index('created_at');
            $table->index(['analysis_status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_analyses');
    }
};
