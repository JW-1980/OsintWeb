<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the case_timeline table for activity tracking.
 *
 * Records all significant events within a case:
 * - Case status changes
 * - Evidence additions/removals
 * - Notes created
 * - Tasks completed
 * - Team member changes
 * - Custom events
 *
 * This provides a complete audit trail and activity feed for cases.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('case_timeline', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained()->onDelete('cascade');

            // Entry classification
            $table->enum('entry_type', [
                'event',           // External event linked
                'note',            // Note added
                'evidence_added',  // Evidence linked
                'evidence_removed',// Evidence unlinked
                'status_change',   // Case status changed
                'task_created',    // Task added
                'task_completed',  // Task finished
                'user_joined',     // Team member added
                'user_left',       // Team member removed
                'priority_change', // Priority updated
                'comment',         // General comment/update
            ])->default('event');

            // Entry data as JSON for flexibility
            $table->json('entry_data')->nullable();

            // When this occurred
            $table->timestamp('occurred_at')->useCurrent();

            // Who created this entry
            $table->foreignId('created_by')->nullable()
                ->constrained('users')->onDelete('set null');

            $table->timestamps();

            // Indexes for efficient querying
            $table->index(['case_id', 'occurred_at']);
            $table->index('entry_type');
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('case_timeline');
    }
};
