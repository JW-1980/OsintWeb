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
        Schema::create('training_sessions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->index();
            $table->foreignId('environment_id')
                ->constrained('training_environments')
                ->cascadeOnDelete()
                ->comment('The training environment for this session');
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete()
                ->comment('The user participating in this session');
            $table->enum('status', ['active', 'paused', 'completed', 'abandoned', 'expired'])
                ->default('active')
                ->index()
                ->comment('Current status of the training session');
            $table->timestamp('started_at')->nullable()->comment('When the session was started');
            $table->timestamp('paused_at')->nullable()->comment('When the session was paused');
            $table->timestamp('completed_at')->nullable()->comment('When the session was completed');
            $table->unsignedInteger('time_spent_seconds')->default(0)->comment('Total time spent in session');
            $table->decimal('score', 10, 2)->default(0)->comment('Score achieved in the session');
            $table->decimal('max_score', 10, 2)->default(100)->comment('Maximum possible score');
            $table->json('objectives_completed')->nullable()->comment('Which objectives have been completed');
            $table->json('actions_log')->nullable()->comment('Log of user actions during the session');
            $table->json('feedback')->nullable()->comment('Post-session feedback and results');
            $table->string('sandbox_data_prefix', 50)->unique()->comment('Prefix for sandbox data isolation');
            $table->timestamps();

            // Indices for common queries
            $table->index(['user_id', 'status']);
            $table->index(['environment_id', 'status']);
            $table->index('started_at');
            $table->index('completed_at');
            $table->index(['user_id', 'environment_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_sessions');
    }
};
