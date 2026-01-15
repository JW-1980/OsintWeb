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
        // Property categories for organizing equipment properties
        Schema::create('equipment_property_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->string('icon', 50)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('sort_order');
            $table->index('is_active');
        });

        // Equipment properties - extensible properties for military equipment
        Schema::create('equipment_properties', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('equipment_id')->constrained('military_equipment')->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('equipment_property_categories')->nullOnDelete();
            $table->string('name', 255);
            $table->string('slug', 255);
            $table->enum('type', [
                'text',           // Plain text
                'richtext',       // HTML/Markdown content
                'number',         // Numeric value
                'boolean',        // Yes/No
                'link',           // URL with optional title
                'image',          // Image URL with metadata
                'video',          // Video embed (YouTube, Vimeo, etc.)
                'file',           // File attachment
                'json',           // Structured JSON data
                'date',           // Date value
                'location',       // Geographic coordinates
            ])->default('text');
            $table->text('value')->nullable()->comment('The actual property value');
            $table->json('metadata')->nullable()->comment('Additional metadata (alt text, dimensions, etc.)');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_public')->default(true)->comment('Visible to public users');
            $table->boolean('is_verified')->default(false);
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['equipment_id', 'category_id']);
            $table->index(['equipment_id', 'type']);
            $table->index(['equipment_id', 'slug']);
            $table->index('sort_order');
            $table->index('is_public');
            $table->index('is_verified');
            $table->fullText(['name', 'value'], 'props_search_idx');
        });

        // Equipment property versions - audit trail with full history
        Schema::create('equipment_property_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained('equipment_properties')->cascadeOnDelete();
            $table->unsignedInteger('version_number');
            $table->string('name', 255);
            $table->enum('type', [
                'text', 'richtext', 'number', 'boolean', 'link',
                'image', 'video', 'file', 'json', 'date', 'location',
            ]);
            $table->text('value')->nullable();
            $table->json('metadata')->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('change_reason', 500)->nullable();
            $table->json('diff')->nullable()->comment('JSON diff from previous version');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['property_id', 'version_number']);
            $table->index('created_at');
            $table->index('changed_by');
        });

        // Equipment images - dedicated table for equipment images
        Schema::create('equipment_images', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('equipment_id')->constrained('military_equipment')->cascadeOnDelete();
            $table->string('url', 500);
            $table->string('thumbnail_url', 500)->nullable();
            $table->string('title', 255)->nullable();
            $table->text('description')->nullable();
            $table->text('alt_text')->nullable();
            $table->string('source_url', 500)->nullable()->comment('Original source URL');
            $table->string('source_name', 255)->nullable();
            $table->string('license', 100)->nullable();
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->integer('file_size')->nullable()->comment('Size in bytes');
            $table->string('mime_type', 100)->nullable();
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_public')->default(true);
            $table->boolean('is_verified')->default(false);
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['equipment_id', 'is_primary']);
            $table->index(['equipment_id', 'sort_order']);
            $table->index('is_public');
        });

        // Equipment links - external references and documentation
        Schema::create('equipment_links', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('equipment_id')->constrained('military_equipment')->cascadeOnDelete();
            $table->string('url', 500);
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->enum('type', [
                'wikipedia',
                'official',
                'manufacturer',
                'documentation',
                'video',
                'news',
                'analysis',
                'forum',
                'other',
            ])->default('other');
            $table->string('language', 10)->default('en');
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_active')->default(true)->comment('Link is still working');
            $table->timestamp('last_checked_at')->nullable();
            $table->foreignId('added_by')->nullable()->constrained('users')->nullOnDelete();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['equipment_id', 'type']);
            $table->index(['equipment_id', 'sort_order']);
            $table->index('is_active');
        });

        // Equipment videos - embedded videos
        Schema::create('equipment_videos', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('equipment_id')->constrained('military_equipment')->cascadeOnDelete();
            $table->string('url', 500);
            $table->string('embed_url', 500)->nullable()->comment('Direct embed URL');
            $table->enum('platform', ['youtube', 'vimeo', 'dailymotion', 'twitter', 'other'])->default('other');
            $table->string('video_id', 100)->nullable()->comment('Platform-specific video ID');
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->string('thumbnail_url', 500)->nullable();
            $table->integer('duration')->nullable()->comment('Duration in seconds');
            $table->string('channel_name', 255)->nullable();
            $table->string('channel_url', 500)->nullable();
            $table->date('published_at')->nullable();
            $table->boolean('is_public')->default(true);
            $table->boolean('is_verified')->default(false);
            $table->foreignId('added_by')->nullable()->constrained('users')->nullOnDelete();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['equipment_id', 'platform']);
            $table->index(['equipment_id', 'sort_order']);
            $table->index('is_public');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment_videos');
        Schema::dropIfExists('equipment_links');
        Schema::dropIfExists('equipment_images');
        Schema::dropIfExists('equipment_property_versions');
        Schema::dropIfExists('equipment_properties');
        Schema::dropIfExists('equipment_property_categories');
    }
};
