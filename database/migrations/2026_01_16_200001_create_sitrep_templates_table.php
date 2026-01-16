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
        Schema::create('sitrep_templates', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('template_type', [
                'daily_brief',
                'weekly_summary',
                'incident_report',
                'equipment_loss',
                'territorial_change',
                'custom',
            ])->default('custom');
            $table->json('sections')->nullable()->comment('Array of section configs: title, type, query_config, order');
            $table->json('header_config')->nullable()->comment('Logo, classification, distribution');
            $table->json('footer_config')->nullable()->comment('Footer configuration');
            $table->json('styling')->nullable()->comment('Fonts, colors, margins');
            $table->boolean('is_default')->default(false);
            $table->boolean('is_public')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['template_type', 'is_public']);
            $table->index('is_default');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sitrep_templates');
    }
};
