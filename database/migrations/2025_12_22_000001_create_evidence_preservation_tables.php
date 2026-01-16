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
        // Preserved evidence table
        Schema::create('preserved_evidence', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('original_url', 2048);
            $table->string('archived_url', 2048)->nullable(); // Wayback Machine or similar
            $table->string('ipfs_hash', 64)->nullable(); // IPFS CID
            $table->string('local_path', 512)->nullable(); // Local storage path
            $table->string('content_hash', 64); // SHA-256 for integrity verification
            $table->enum('content_type', [
                'webpage',
                'image',
                'video',
                'document',
                'social_post',
                'audio',
                'archive',
            ])->default('webpage');
            $table->unsignedBigInteger('file_size')->default(0); // Size in bytes
            $table->json('metadata')->nullable(); // Title, description, headers, etc.
            $table->enum('capture_status', [
                'pending',
                'capturing',
                'captured',
                'failed',
                'expired',
                'deleted',
            ])->default('pending');
            $table->text('error_message')->nullable();
            $table->unsignedTinyInteger('retry_count')->default(0);
            $table->timestamp('captured_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('last_verified_at')->nullable();
            $table->boolean('is_verified')->default(false);

            // Optional relationship to events
            $table->foreignId('event_id')->nullable()->constrained()->nullOnDelete();

            // User who created this preservation
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('capture_status');
            $table->index('content_type');
            $table->index('ipfs_hash');
            $table->index('content_hash');
            $table->index(['created_by', 'capture_status']);
            $table->index('captured_at');
            $table->index('expires_at');
            $table->index('event_id');
            $table->fullText('original_url');
        });

        // Evidence snapshots table (version history)
        Schema::create('evidence_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('preserved_evidence_id')
                ->constrained('preserved_evidence')
                ->onDelete('cascade');
            $table->string('snapshot_hash', 64); // SHA-256 of this snapshot
            $table->string('snapshot_path', 512); // Path to stored snapshot
            $table->unsignedBigInteger('file_size')->default(0);
            $table->json('metadata')->nullable(); // Diff info, headers at capture time
            $table->boolean('is_primary')->default(false); // Primary/current snapshot
            $table->timestamp('captured_at')->useCurrent();
            $table->timestamps();

            // Indexes
            $table->index('snapshot_hash');
            $table->index(['preserved_evidence_id', 'is_primary']);
            $table->index('captured_at');
        });

        // Evidence tags for categorization
        Schema::create('evidence_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('preserved_evidence_id')
                ->constrained('preserved_evidence')
                ->onDelete('cascade');
            $table->string('tag', 100);
            $table->timestamps();

            $table->unique(['preserved_evidence_id', 'tag']);
            $table->index('tag');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evidence_tags');
        Schema::dropIfExists('evidence_snapshots');
        Schema::dropIfExists('preserved_evidence');
    }
};
