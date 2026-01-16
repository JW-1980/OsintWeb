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
        Schema::create('sitreps', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->foreignId('template_id')->nullable()->constrained('sitrep_templates')->nullOnDelete();
            $table->enum('report_type', [
                'daily_brief',
                'weekly_summary',
                'incident_report',
                'equipment_loss',
                'territorial_change',
                'custom',
            ])->default('custom');
            $table->enum('classification_level', [
                'unclassified',
                'confidential',
                'secret',
                'top_secret',
            ])->default('unclassified');
            $table->date('period_start')->nullable()->comment('Start of reporting period');
            $table->date('period_end')->nullable()->comment('End of reporting period');
            $table->json('content_sections')->nullable()->comment('Rendered section content');
            $table->text('summary')->nullable()->comment('Executive summary');
            $table->json('key_findings')->nullable()->comment('Array of key findings');
            $table->json('recommendations')->nullable()->comment('Array of recommendations');
            $table->json('appendices')->nullable()->comment('Appendix content');
            $table->enum('status', [
                'draft',
                'review',
                'approved',
                'published',
                'archived',
            ])->default('draft');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->json('generated_files')->nullable()->comment('Paths to PDF, DOCX, HTML exports');
            $table->json('event_ids')->nullable()->comment('Array of linked event IDs');
            $table->json('equipment_ids')->nullable()->comment('Array of linked equipment IDs');
            $table->json('actor_ids')->nullable()->comment('Array of linked actor IDs');
            $table->json('metadata')->nullable()->comment('Additional metadata');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'report_type']);
            $table->index(['period_start', 'period_end']);
            $table->index('classification_level');
            $table->index('published_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sitreps');
    }
};
