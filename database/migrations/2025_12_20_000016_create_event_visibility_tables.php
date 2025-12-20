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
        // Event visibility for specific users
        Schema::create('event_user_access', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('access_level', ['view', 'edit', 'manage'])->default('view');
            $table->foreignId('granted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->unique(['event_id', 'user_id']);
            $table->index('access_level');
            $table->index('expires_at');
        });

        // Event visibility for user groups
        Schema::create('event_group_access', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('user_group_id')->constrained('user_groups')->cascadeOnDelete();
            $table->enum('access_level', ['view', 'edit', 'manage'])->default('view');
            $table->foreignId('granted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->unique(['event_id', 'user_group_id']);
            $table->index('access_level');
            $table->index('expires_at');
        });

        // Event visibility for roles
        Schema::create('event_role_access', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->enum('access_level', ['view', 'edit', 'manage'])->default('view');
            $table->foreignId('granted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->unique(['event_id', 'role_id']);
            $table->index('access_level');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_role_access');
        Schema::dropIfExists('event_group_access');
        Schema::dropIfExists('event_user_access');
    }
};
