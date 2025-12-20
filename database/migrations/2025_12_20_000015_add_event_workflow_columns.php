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
        Schema::table('events', function (Blueprint $table) {
            // Draft and publishing workflow
            $table->timestamp('scheduled_publish_at')->nullable()->after('status')
                ->comment('When to auto-publish the event');
            $table->foreignId('approved_by')->nullable()->after('scheduled_publish_at')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->text('approval_notes')->nullable()->after('approved_at');
            $table->boolean('requires_approval')->default(false)->after('approval_notes')
                ->comment('Whether this event needs approval before publishing');
            $table->timestamp('published_at')->nullable()->after('requires_approval');

            // Visibility settings
            $table->enum('visibility', ['public', 'private', 'restricted', 'internal'])
                ->default('public')->after('published_at')
                ->comment('public=all, private=author only, restricted=specific users/groups/roles, internal=authenticated users');

            // Version tracking for events
            $table->unsignedInteger('version')->default(1)->after('visibility');
            $table->foreignId('parent_version_id')->nullable()->after('version')
                ->comment('Reference to previous version of this event');

            // Indexes for workflow queries
            $table->index('scheduled_publish_at');
            $table->index('approved_at');
            $table->index('visibility');
            $table->index('published_at');
            $table->index('version');
        });

        // Add foreign key constraint separately
        Schema::table('events', function (Blueprint $table) {
            $table->foreign('parent_version_id')
                ->references('id')
                ->on('events')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropForeign(['parent_version_id']);
            $table->dropForeign(['approved_by']);

            $table->dropColumn([
                'scheduled_publish_at',
                'approved_by',
                'approved_at',
                'approval_notes',
                'requires_approval',
                'published_at',
                'visibility',
                'version',
                'parent_version_id',
            ]);
        });
    }
};
