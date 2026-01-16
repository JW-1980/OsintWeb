<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the case_evidence table for polymorphic evidence linking.
 *
 * This table allows linking various types of evidence to investigation cases:
 * - Events (incidents, observations)
 * - Documents (uploaded files, OCR results)
 * - Media (images, videos, audio)
 * - Social media posts
 * - Preserved evidence (archived web content)
 *
 * Each evidence item has:
 * - Relevance scoring (1-5)
 * - Review status workflow
 * - Notes for context
 * - Audit trail (who added, when)
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('case_evidence', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained()->onDelete('cascade');

            // Polymorphic relationship to various evidence types
            $table->string('evidence_type'); // Event, DocumentAnalysis, PreservedEvidence, etc.
            $table->unsignedBigInteger('evidence_id');

            // Evidence metadata
            $table->unsignedTinyInteger('relevance_score')->default(3); // 1-5 scale
            $table->text('notes')->nullable();

            // Review workflow
            $table->enum('status', [
                'pending_review',
                'verified',
                'disputed',
                'excluded',
            ])->default('pending_review');

            // Audit trail
            $table->foreignId('added_by')->nullable()
                ->constrained('users')->onDelete('set null');
            $table->timestamp('added_at')->useCurrent();
            $table->foreignId('reviewed_by')->nullable()
                ->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['case_id', 'evidence_type']);
            $table->index(['evidence_type', 'evidence_id']);
            $table->index('status');
            $table->index('relevance_score');

            // Prevent duplicate evidence links
            $table->unique(['case_id', 'evidence_type', 'evidence_id'], 'unique_case_evidence');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('case_evidence');
    }
};
