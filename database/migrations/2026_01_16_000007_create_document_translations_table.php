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
        Schema::create('document_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_analysis_id')->constrained()->onDelete('cascade');

            // Translation details
            $table->string('source_language', 10); // ISO 639-1 code
            $table->string('target_language', 10); // ISO 639-1 code
            $table->longText('translated_text');

            // Quality metrics
            $table->unsignedTinyInteger('translation_confidence')->nullable(); // 0-100

            // Provider information
            $table->enum('translation_provider', ['openrouter', 'google', 'deepl', 'local'])
                ->default('openrouter');
            $table->string('model_used')->nullable(); // e.g., 'meta-llama/llama-3-8b-instruct'

            // Processing metadata
            $table->unsignedInteger('processing_time_ms')->nullable();
            $table->unsignedInteger('token_count')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('source_language');
            $table->index('target_language');
            $table->index('translation_provider');
            $table->index(['document_analysis_id', 'target_language']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_translations');
    }
};
