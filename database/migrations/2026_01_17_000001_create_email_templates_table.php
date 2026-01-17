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
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('subject');
            $table->longText('html_content');
            $table->longText('text_content');
            $table->enum('category', [
                'authentication',
                'notification',
                'marketing',
                'transactional',
                'alert',
                'digest'
            ])->default('notification');
            $table->json('variables')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system')->default(false);
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['category', 'is_active']);
            $table->index('slug');
        });

        Schema::create('email_unsubscribes', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('email');
            $table->string('token', 64)->unique();
            $table->enum('type', [
                'all',
                'marketing',
                'notifications',
                'alerts',
                'digest'
            ])->default('all');
            $table->string('reason')->nullable();
            $table->ipAddress('unsubscribed_ip')->nullable();
            $table->timestamp('unsubscribed_at');
            $table->timestamps();

            $table->index(['email', 'type']);
            $table->index('token');
        });

        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('email');
            $table->string('template_slug');
            $table->string('subject');
            $table->enum('status', ['pending', 'sent', 'delivered', 'opened', 'clicked', 'bounced', 'failed'])->default('pending');
            $table->json('variables')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['email', 'status']);
            $table->index('template_slug');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_logs');
        Schema::dropIfExists('email_unsubscribes');
        Schema::dropIfExists('email_templates');
    }
};
