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
        Schema::create('scheduled_reports', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->text('description')->nullable();

            // Report type
            $table->enum('report_type', [
                'sitrep',
                'event_digest',
                'equipment_update',
                'territorial_summary',
                'actor_activity',
                'custom_query',
            ]);

            // Optional template reference
            $table->foreignId('template_id')
                ->nullable()
                ->constrained('sitrep_templates')
                ->nullOnDelete();

            // Schedule configuration
            $table->enum('schedule_type', [
                'daily',
                'weekly',
                'biweekly',
                'monthly',
                'quarterly',
            ]);
            $table->json('schedule_config')->nullable()->comment('day_of_week, day_of_month, time_utc');
            $table->string('timezone', 64)->default('UTC');

            // Content filters
            $table->json('filters')->nullable()->comment('event_types, regions, actors, equipment to include');

            // Date range configuration
            $table->enum('date_range_type', [
                'last_24h',
                'last_7d',
                'last_30d',
                'last_quarter',
                'custom',
            ])->default('last_7d');
            $table->json('custom_date_range')->nullable()->comment('start_date, end_date if custom');

            // Delivery configuration
            $table->json('recipients')->comment('Array of email addresses');
            $table->enum('delivery_method', [
                'email',
                'webhook',
                'api_push',
                'storage',
            ])->default('email');
            $table->string('webhook_url', 2048)->nullable();

            // Export configuration
            $table->json('export_formats')->comment('Array of formats: pdf, docx, html, json');
            $table->boolean('include_attachments')->default(false);

            // Status
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_run_at')->nullable();
            $table->timestamp('next_run_at')->nullable()->index();
            $table->enum('last_run_status', ['success', 'failed', 'partial'])->nullable();
            $table->text('last_error')->nullable();

            // Statistics
            $table->unsignedInteger('run_count')->default(0);
            $table->unsignedInteger('success_count')->default(0);
            $table->unsignedInteger('failure_count')->default(0);

            // Ownership
            $table->foreignId('created_by')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['is_active', 'next_run_at']);
            $table->index('report_type');
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scheduled_reports');
    }
};
