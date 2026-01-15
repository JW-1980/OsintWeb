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

            // Note: Action validation handled in application layer
        });

        // Note: GIN/GIST indexes and PostgreSQL arrays not available in MySQL
        // JSON columns can be indexed using generated columns if needed
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
