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
        Schema::create('scheduled_report_runs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->foreignId('scheduled_report_id')
                ->constrained('scheduled_reports')
                ->cascadeOnDelete();

            // Execution timing
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();

            // Status
            $table->enum('status', ['pending', 'running', 'success', 'failed', 'partial'])
                ->default('pending');

            // Generated content reference
            $table->foreignId('sitrep_id')
                ->nullable()
                ->constrained('sitreps')
                ->nullOnDelete();

            // Generated files
            $table->json('generated_files')->nullable()->comment('Array of file paths');

            // Delivery tracking
            $table->json('recipients_notified')->nullable()->comment('Array of notified email addresses');
            $table->json('delivery_status')->nullable()->comment('Per-recipient delivery status');

            // Error tracking
            $table->text('error_message')->nullable();

            // Performance metrics
            $table->unsignedInteger('execution_time_ms')->nullable();

            // Report parameters snapshot (for auditing)
            $table->json('parameters_snapshot')->nullable()->comment('Snapshot of filters and config at run time');

            // Data statistics
            $table->unsignedInteger('events_included')->default(0);
            $table->unsignedInteger('total_records')->default(0);

            $table->timestamps();

            // Indexes
            $table->index(['scheduled_report_id', 'started_at']);
            $table->index('status');
            $table->index('started_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scheduled_report_runs');
    }
};
