<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Extends the cases table for full Case Management Workspaces (Digital War Rooms).
 *
 * Adds:
 * - case_number: Auto-generated unique case identifier
 * - case_type: Classification of investigation type
 * - objective: Clear goal/mission statement
 * - lead_analyst_id: Primary responsible analyst
 * - team_members: JSON array of team with roles
 * - start_date/due_date: Timeline tracking
 * - progress_percentage: Auto-calculated progress
 * - created_by: Original creator (different from owner)
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            // Case identification
            $table->string('case_number')->unique()->nullable()->after('uuid');

            // Enhanced details
            $table->text('objective')->nullable()->after('description');
            $table->string('case_type')->default('general')->after('objective');

            // Team management
            $table->foreignId('lead_analyst_id')->nullable()->after('user_id')
                ->constrained('users')->onDelete('set null');
            $table->json('team_members')->nullable()->after('collaborators');

            // Timeline tracking
            $table->date('start_date')->nullable()->after('closed_at');
            $table->date('due_date')->nullable()->after('start_date');

            // Progress tracking
            $table->unsignedTinyInteger('progress_percentage')->default(0)->after('due_date');

            // Audit trail
            $table->foreignId('created_by')->nullable()->after('progress_percentage')
                ->constrained('users')->onDelete('set null');

            // Indexes
            $table->index('case_number');
            $table->index('case_type');
            $table->index('lead_analyst_id');
            $table->index('due_date');
        });

        // Rename 'classification' to 'classification_level' for clarity
        // and update status enum to match new requirements
        Schema::table('cases', function (Blueprint $table) {
            $table->renameColumn('classification', 'classification_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            $table->renameColumn('classification_level', 'classification');
        });

        Schema::table('cases', function (Blueprint $table) {
            $table->dropForeign(['lead_analyst_id']);
            $table->dropForeign(['created_by']);
            $table->dropIndex(['case_number']);
            $table->dropIndex(['case_type']);
            $table->dropIndex(['lead_analyst_id']);
            $table->dropIndex(['due_date']);

            $table->dropColumn([
                'case_number',
                'objective',
                'case_type',
                'lead_analyst_id',
                'team_members',
                'start_date',
                'due_date',
                'progress_percentage',
                'created_by',
            ]);
        });
    }
};
