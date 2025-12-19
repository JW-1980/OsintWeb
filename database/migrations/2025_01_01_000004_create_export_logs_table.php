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
        Schema::create('export_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // Who
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('session_id')->nullable();

            // What
            $table->string('export_type', 50)->index();
            $table->string('entity_type')->nullable();

            // Scope
            $table->jsonb('filter_criteria')->nullable();
            $table->timestamp('date_range_start')->nullable();
            $table->timestamp('date_range_end')->nullable();
            $table->geography('geographic_bounds', 'polygon', 4326)->nullable();

            // Results
            $table->integer('records_exported');
            $table->bigInteger('file_size_bytes')->nullable();
            $table->string('file_hash', 64)->nullable();

            // Storage (if kept)
            $table->string('file_path', 500)->nullable();
            $table->string('file_url', 500)->nullable();
            $table->timestamp('expires_at')->nullable()->index();

            // Context
            $table->ipAddress('ip_address');
            $table->text('user_agent')->nullable();
            $table->text('reason')->nullable();

            // Audit
            $table->timestamp('created_at')->index();
            $table->integer('accessed_count')->default(0);
            $table->timestamp('last_accessed_at')->nullable();

            // Compliance
            $table->boolean('contains_pii')->default(false)->index();
            $table->string('retention_category', 50)->nullable();
            $table->boolean('legal_hold')->default(false)->index();

            // Indexes
            $table->index(['user_id', 'created_at']);
            $table->index(['export_type', 'created_at']);
            $table->index(['entity_type', 'created_at']);
        });

        // Add GIN index for filter criteria
        DB::statement('CREATE INDEX export_logs_filter_criteria_idx ON export_logs USING GIN(filter_criteria)');

        // Add spatial index for geographic bounds
        DB::statement('CREATE INDEX export_logs_geographic_bounds_idx ON export_logs USING GIST(geographic_bounds)');

        // Add partial index for expiring exports
        DB::statement('CREATE INDEX export_logs_expires_at_idx ON export_logs(expires_at) WHERE expires_at IS NOT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('export_logs');
    }
};
