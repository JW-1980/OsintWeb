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
        Schema::create('sync_queue', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('operation', ['create', 'update', 'delete']);
            $table->string('entity_type', 100);
            $table->unsignedBigInteger('entity_id')->nullable()->comment('Nullable for create operations');
            $table->json('payload')->comment('The data to sync');
            $table->unsignedTinyInteger('priority')->default(3)->comment('1-5, 1 being highest');
            $table->enum('status', ['pending', 'processing', 'synced', 'failed', 'conflict'])->default('pending');
            $table->json('conflict_data')->nullable()->comment('Data if conflict occurred');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('last_attempt_at')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->text('error_message')->nullable();
            $table->string('client_id', 100)->nullable()->comment('Client-side unique identifier');
            $table->json('metadata')->nullable()->comment('Additional metadata for sync');
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['status', 'priority']);
            $table->index(['entity_type', 'entity_id']);
            $table->index('last_attempt_at');
            $table->index('client_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sync_queue');
    }
};
