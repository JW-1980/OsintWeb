<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the case_tasks table for task management within cases.
 *
 * Supports:
 * - Task types: research, verification, analysis, documentation, review
 * - Priority levels matching case priorities
 * - Status workflow: todo -> in_progress -> blocked/completed
 * - Assignment to team members
 * - Due date tracking
 * - Completion audit trail
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('case_tasks', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('case_id')->constrained()->onDelete('cascade');

            // Task details
            $table->string('title');
            $table->text('description')->nullable();

            // Classification
            $table->enum('task_type', [
                'research',
                'verification',
                'analysis',
                'documentation',
                'review',
            ])->default('research');

            // Assignment
            $table->foreignId('assigned_to')->nullable()
                ->constrained('users')->onDelete('set null');

            // Priority and status
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->enum('status', ['todo', 'in_progress', 'blocked', 'completed'])->default('todo');

            // Timeline
            $table->date('due_date')->nullable();

            // Completion tracking
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('completed_by')->nullable()
                ->constrained('users')->onDelete('set null');

            // Additional metadata
            $table->json('checklist')->nullable(); // Sub-tasks as JSON array
            $table->text('blockers')->nullable(); // Reason for blocked status

            // Audit
            $table->foreignId('created_by')->nullable()
                ->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Indexes
            $table->index(['case_id', 'status']);
            $table->index(['assigned_to', 'status']);
            $table->index('due_date');
            $table->index('priority');
            $table->index('task_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('case_tasks');
    }
};
