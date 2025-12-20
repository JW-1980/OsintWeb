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
        // Permissions table
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('slug', 100)->unique();
            $table->string('group', 100)->default('general')->index();
            $table->text('description')->nullable();
            $table->boolean('is_system')->default(false)->comment('System permissions cannot be deleted');
            $table->timestamps();

            $table->index(['group', 'slug']);
        });

        // Roles table
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('slug', 100)->unique();
            $table->text('description')->nullable();
            $table->string('color', 7)->nullable()->comment('Hex color for UI display');
            $table->integer('priority')->default(0)->comment('Higher priority roles override lower');
            $table->boolean('is_system')->default(false)->comment('System roles cannot be deleted');
            $table->boolean('is_default')->default(false)->comment('Assigned to new users by default');
            $table->timestamps();

            $table->index('priority');
        });

        // Role-Permission pivot table
        Schema::create('role_permission', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['role_id', 'permission_id']);
        });

        // User-Role pivot table
        Schema::create('user_role', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('granted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('expires_at')->nullable()->comment('Optional role expiration');
            $table->timestamps();

            $table->unique(['user_id', 'role_id']);
            $table->index('expires_at');
        });

        // User groups table
        Schema::create('user_groups', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->text('description')->nullable();
            $table->string('color', 7)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('is_active');
        });

        // User-Group pivot table
        Schema::create('user_group_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('user_group_id')->constrained('user_groups')->cascadeOnDelete();
            $table->enum('role', ['member', 'moderator', 'admin'])->default('member');
            $table->foreignId('added_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'user_group_id']);
            $table->index('role');
        });

        // Direct user permissions (overrides role permissions)
        Schema::create('user_permission', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
            $table->enum('type', ['grant', 'deny'])->default('grant');
            $table->foreignId('granted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'permission_id']);
            $table->index(['type', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_permission');
        Schema::dropIfExists('user_group_members');
        Schema::dropIfExists('user_groups');
        Schema::dropIfExists('user_role');
        Schema::dropIfExists('role_permission');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('permissions');
    }
};
