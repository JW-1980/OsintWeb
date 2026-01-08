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
        Schema::create('achievements', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Name of the achievement');
            $table->string('slug')->unique()->comment('Unique identifier for code reference');
            $table->text('description')->nullable()->comment('Description of the achievement');
            $table->string('icon')->default('trophy')->comment('Icon for the achievement');
            $table->string('color')->default('#FFD700')->comment('Color hex code');
            $table->integer('points')->default(0)->comment('Points awarded');
            $table->integer('threshold')->default(1)->comment('Threshold value required to unlock');
            $table->string('type')->index()->comment('Type of metric to track (e.g., comments, logins)');
            $table->boolean('is_secret')->default(false)->comment('Whether achievement is hidden until unlocked');
            $table->boolean('is_active')->default(true)->comment('Whether achievement is active');
            $table->timestamps();
        });

        Schema::create('user_achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('achievement_id')->constrained()->cascadeOnDelete();
            $table->timestamp('awarded_at')->useCurrent();
            $table->unique(['user_id', 'achievement_id']);
            $table->index('awarded_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_achievements');
        Schema::dropIfExists('achievements');
    }
};
