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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // Who
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('user_email')->index();
            $table->string('user_name')->nullable();
            $table->foreignId('impersonator_id')->nullable()->constrained('users')->nullOnDelete();

            // When
            $table->timestamp('created_at')->index();
            $table->timestamp('occurred_at')->index();

            // Where (network context)
            $table->ipAddress('ip_address')->index();
            $table->char('ip_country', 2)->nullable()->index();
            $table->text('user_agent')->nullable();
            $table->string('session_id')->nullable()->index();
            $table->string('request_id')->nullable()->index();

            // What
            $table->string('action', 50)->index();
            $table->string('auditable_type')->index();
            $table->unsignedBigInteger('auditable_id')->index();
            $table->uuid('auditable_uuid')->nullable()->index();

            // Changes
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->text('changed_fields')->nullable(); // PostgreSQL text array

            // Context
            $table->text('reason')->nullable();
            $table->text('tags')->nullable(); // PostgreSQL text array
            $table->json('metadata')->nullable();

            // Request context
            $table->text('url')->nullable();
            $table->string('http_method', 10)->nullable();
            $table->string('api_endpoint', 500)->nullable();

            // Tamper detection
            $table->string('hash', 64)->index();
            $table->string('previous_hash', 64)->nullable()->index();
            $table->boolean('chain_verified')->default(true)->index();

            // Composite indexes
            $table->index(['auditable_type', 'auditable_id']);
            $table->index(['user_id', 'created_at']);
            $table->index(['action', 'created_at']);

            // Check constraint
            $table->check("action IN ('create', 'update', 'delete', 'restore', 'view', 'export', 'login', 'logout', 'failed_login', 'password_change', 'verify', 'dispute', 'approve', 'reject', 'rollback')");
        });

        // Add GIN indexes for JSONB and array columns
        DB::statement('CREATE INDEX audit_logs_old_values_idx ON audit_logs USING GIN(old_values)');
        DB::statement('CREATE INDEX audit_logs_new_values_idx ON audit_logs USING GIN(new_values)');
        DB::statement('CREATE INDEX audit_logs_metadata_idx ON audit_logs USING GIN(metadata)');

        // Convert text columns to PostgreSQL arrays
        DB::statement('ALTER TABLE audit_logs ALTER COLUMN changed_fields TYPE text[] USING string_to_array(changed_fields, \',\')::text[]');
        DB::statement('ALTER TABLE audit_logs ALTER COLUMN tags TYPE text[] USING string_to_array(tags, \',\')::text[]');

        // Add array indexes
        DB::statement('CREATE INDEX audit_logs_changed_fields_idx ON audit_logs USING GIN(changed_fields)');
        DB::statement('CREATE INDEX audit_logs_tags_idx ON audit_logs USING GIN(tags)');

        // Full-text search index
        DB::statement("CREATE INDEX audit_logs_search_idx ON audit_logs USING GIN(to_tsvector('english', coalesce(reason, '') || ' ' || coalesce(old_values::text, '') || ' ' || coalesce(new_values::text, '')))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
