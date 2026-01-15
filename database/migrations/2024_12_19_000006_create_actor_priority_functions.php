<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Note: Actor priority calculations are handled in the Actor model
     * using Laravel's model events instead of database triggers.
     * This provides better portability across database systems.
     */
    public function up(): void
    {
        // Priority score calculations moved to App\Models\Actor
        // using model observers/events for MySQL compatibility
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Nothing to reverse
    }
};
