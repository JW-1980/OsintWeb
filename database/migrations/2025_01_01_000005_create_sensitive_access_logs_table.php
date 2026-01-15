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
        Schema::create('sensitive_access_logs', function (Blueprint $table) {
            $table->id();

            // Who
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('user_role', 50);

            // What
            $table->string('resource_type')->index();
            $table->unsignedBigInteger('resource_id')->index();
            $table->uuid('resource_uuid')->nullable();
            $table->string('access_type', 50);

            // Classification
            $table->string('sensitivity_level', 50)->index();
            $table->string('data_category', 100)->nullable();

            // Context
            $table->text('purpose')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approval_timestamp')->nullable();

            // Network
            $table->ipAddress('ip_address');
            $table->text('user_agent')->nullable();
            $table->string('session_id')->nullable();

            // Audit
            $table->timestamp('created_at')->useCurrent()->index();

            // Alert if flagged
            $table->boolean('flagged_for_review')->default(false)->index();
            $table->text('flag_reason')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();

            // Indexes
            $table->index(['user_id', 'created_at']);
            $table->index(['resource_type', 'resource_id']);
            $table->index(['sensitivity_level', 'created_at']);
        });

        // Note: Partial indexes not available in MySQL
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sensitive_access_logs');
    }
};
