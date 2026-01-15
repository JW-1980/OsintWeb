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
        Schema::create('entity_versions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // Entity reference
            $table->string('versionable_type')->index();
            $table->unsignedBigInteger('versionable_id')->index();
            $table->uuid('versionable_uuid')->nullable()->index();

            // Version info
            $table->integer('version_number')->index();
            $table->string('version_hash', 64);

            // Complete state at this version
            $table->json('snapshot');

            // Metadata
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->index();

            // Change summary
            $table->text('change_summary')->nullable();
            $table->string('change_type', 50)->nullable();

            // Parent version (for branching/merging)
            $table->foreignId('parent_version_id')->nullable()->constrained('entity_versions')->nullOnDelete();

            // Audit trail reference
            $table->foreignId('audit_log_id')->nullable()->constrained('audit_logs')->nullOnDelete();

            // Composite indexes
            $table->unique(['versionable_type', 'versionable_id', 'version_number'], 'entity_versions_unique');
            $table->index(['versionable_type', 'versionable_id', 'version_number']);
            $table->index(['created_by', 'created_at']);
        });

        // Note: GIN indexes not available in MySQL
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entity_versions');
    }
};
