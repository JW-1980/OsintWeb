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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique()->comment('Setting key identifier');
            $table->text('value')->comment('Setting value (can be JSON)');
            $table->enum('type', ['string', 'integer', 'boolean', 'json', 'array'])->default('string');
            $table->string('group', 100)->default('general')->index()->comment('Setting group/category');
            $table->text('description')->nullable();
            $table->boolean('is_public')->default(false)->index()->comment('Whether setting is accessible to frontend');
            $table->timestamps();

            // Indexes
            $table->index('key');
            $table->index('group');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
