<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Extends the case_notes table with additional fields.
 *
 * Adds:
 * - note_type: Classification of note purpose
 * - parent_id: For threaded/nested notes
 * - mentioned_users: JSON array for @mentions
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('case_notes', function (Blueprint $table) {
            // Note classification
            $table->enum('note_type', [
                'observation',
                'hypothesis',
                'finding',
                'action_item',
                'timeline_entry',
            ])->default('observation')->after('user_id');

            // Threading support
            $table->foreignId('parent_id')->nullable()->after('is_pinned')
                ->constrained('case_notes')->onDelete('cascade');

            // Mentions
            $table->json('mentioned_users')->nullable()->after('parent_id');

            // Indexes
            $table->index('note_type');
            $table->index('parent_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('case_notes', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropIndex(['note_type']);
            $table->dropIndex(['parent_id']);

            $table->dropColumn([
                'note_type',
                'parent_id',
                'mentioned_users',
            ]);
        });
    }
};
