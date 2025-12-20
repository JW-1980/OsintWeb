<?php

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
        Schema::create('verification_logs', function (Blueprint $table) {
            $table->id();

            // Target (will be created after events table)
            $table->unsignedBigInteger('event_id');

            // Action
            $table->string('action', 50)->index();
            $table->string('previous_status', 50)->nullable();
            $table->string('new_status', 50);

            // Verifier
            $table->foreignId('verified_by')->constrained('users')->cascadeOnDelete();
            $table->jsonb('verifier_expertise')->nullable();

            // Evidence
            $table->string('verification_method', 100)->nullable();
            $table->integer('confidence_level')->nullable();
            $table->text('evidence_urls')->nullable(); // PostgreSQL text array
            $table->text('notes');

            // Peer review
            $table->boolean('peer_reviewed')->default(false);
            $table->foreignId('peer_reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('peer_review_notes')->nullable();
            $table->timestamp('peer_review_timestamp')->nullable();

            // Metadata
            $table->timestamp('created_at')->index();
            $table->ipAddress('ip_address');

            // Audit log reference
            $table->foreignId('audit_log_id')->nullable()->constrained('audit_logs')->nullOnDelete();

            // Indexes
            $table->index(['verified_by', 'created_at']);
            $table->index(['action', 'created_at']);

            // Check constraint
            $table->check("confidence_level IS NULL OR (confidence_level >= 1 AND confidence_level <= 100)");
        });

        // Convert evidence_urls to PostgreSQL array
        DB::statement('ALTER TABLE verification_logs ALTER COLUMN evidence_urls TYPE text[] USING string_to_array(evidence_urls, \',\')::text[]');

        // Add GIN index for verifier expertise
        DB::statement('CREATE INDEX verification_logs_verifier_expertise_idx ON verification_logs USING GIN(verifier_expertise)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('verification_logs');
    }
};
