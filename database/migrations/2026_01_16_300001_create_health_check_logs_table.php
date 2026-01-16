<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the health_check_logs table for tracking system health check results
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('health_check_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // Check classification
            $table->enum('check_type', [
                'full',
                'quick',
                'database',
                'cache',
                'search',
                'queue',
                'storage',
                'external',
                'migrations',
                'scheduler',
            ]);

            // Overall status
            $table->enum('status', [
                'healthy',
                'degraded',
                'unhealthy',
                'error',
            ])->default('healthy');

            // Performance metrics
            $table->unsignedInteger('duration_ms');

            // Detailed results
            $table->json('checks_performed')->comment('Results of individual component checks');
            $table->json('failed_checks')->nullable()->comment('Array of failed check names');
            $table->json('warnings')->nullable()->comment('Array of warning messages');
            $table->json('metadata')->nullable()->comment('Additional context and metrics');

            // Request context
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->foreignId('triggered_by_user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            // Indexes for common queries
            $table->index('check_type');
            $table->index('status');
            $table->index('created_at');
            $table->index(['check_type', 'status']);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('health_check_logs');
    }
};
