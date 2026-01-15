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
        Schema::create('event_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->enum('type', ['image', 'video', 'document'])->index();
            $table->string('file_path')->comment('Storage path');
            $table->string('original_filename');
            $table->string('mime_type');
            $table->unsignedBigInteger('file_size')->comment('Size in bytes');
            $table->json('metadata')->nullable()->comment('EXIF, dimensions, duration, etc.');
            $table->text('caption')->nullable();
            $table->string('source_url')->nullable()->comment('Original source URL');
            $table->timestamps();

            // Indexes
            $table->index('event_id');
            // Note: 'type' already indexed on line 17
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_media');
    }
};
