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
        Schema::create('training_achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_session_id')
                ->constrained('training_sessions')
                ->cascadeOnDelete()
                ->comment('The training session this achievement was earned in');
            $table->enum('achievement_type', [
                'completed_exercise',
                'passed_certification',
                'high_score',
                'perfect_score',
                'fast_completion',
                'first_attempt',
                'streak',
                'skill_mastery',
                'all_objectives',
                'no_hints_used',
                'expert_level',
                'collaboration',
                'custom',
            ])->comment('Type of achievement earned');
            $table->json('details')->nullable()->comment('Additional details about the achievement');
            $table->timestamp('awarded_at')->useCurrent()->comment('When the achievement was awarded');
            $table->timestamps();

            // Indices
            $table->index('achievement_type');
            $table->index('awarded_at');
            $table->index(['training_session_id', 'achievement_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_achievements');
    }
};
