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
        Schema::create('session_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // Session identification
            $table->string('session_id')->unique()->index();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            // Session lifecycle
            $table->timestamp('started_at')->index();
            $table->timestamp('last_activity_at')->index();
            $table->timestamp('ended_at')->nullable();

            // Session context
            $table->ipAddress('ip_address')->index();
            $table->char('ip_country', 2)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('device_type', 50)->nullable();
            $table->string('browser', 100)->nullable();
            $table->string('os', 100)->nullable();

            // Activity metrics
            $table->integer('page_views')->default(0);
            $table->integer('actions_count')->default(0);
            $table->integer('events_created')->default(0);
            $table->integer('events_updated')->default(0);
            $table->integer('events_viewed')->default(0);
            $table->integer('exports_count')->default(0);

            // Security
            $table->integer('failed_login_attempts')->default(0);
            $table->boolean('suspicious_activity')->default(false)->index();
            $table->text('suspicious_reasons')->nullable(); // PostgreSQL text array

            // Session end reason
            $table->string('end_reason', 50)->nullable();

            // Metadata
            $table->json('metadata')->nullable();

            // Indexes
            $table->index(['user_id', 'started_at']);
            $table->index(['ip_address', 'started_at']);
        });

        // Convert suspicious_reasons to PostgreSQL array
        DB::statement('ALTER TABLE session_logs ALTER COLUMN suspicious_reasons TYPE text[] USING string_to_array(suspicious_reasons, \',\')::text[]');

        // Add GIN index for metadata
        DB::statement('CREATE INDEX session_logs_metadata_idx ON session_logs USING GIN(metadata)');

        // Add partial index for suspicious sessions
        DB::statement('CREATE INDEX session_logs_suspicious_idx ON session_logs(suspicious_activity) WHERE suspicious_activity = true');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('session_logs');
    }
};
