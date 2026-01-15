<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('audit_retention_policies', function (Blueprint $table) {
            $table->id();

            // Policy scope
            $table->string('data_category', 100)->unique();

            // Retention periods
            $table->integer('hot_storage_days')->default(90);
            $table->integer('warm_storage_days')->nullable()->default(730); // 2 years
            $table->integer('cold_storage_years')->nullable();

            // Archive settings
            $table->boolean('archive_enabled')->default(true);
            $table->string('archive_format', 50)->default('jsonl.gz');

            // Deletion rules
            $table->boolean('auto_delete_enabled')->default(false);
            $table->boolean('legal_hold_exempt')->default(false);

            // GDPR
            $table->boolean('pii_data')->default(false);
            $table->boolean('gdpr_right_to_erasure')->default(false);

            // Metadata
            $table->integer('policy_version')->default(1);
            $table->timestamp('effective_from')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('updated_at')->nullable();
        });

        // Insert default policies
        DB::table('audit_retention_policies')->insert([
            [
                'data_category' => 'audit_logs',
                'hot_storage_days' => 90,
                'warm_storage_days' => 730,
                'cold_storage_years' => null,
                'pii_data' => false,
                'gdpr_right_to_erasure' => false,
                'policy_version' => 1,
            ],
            [
                'data_category' => 'session_logs',
                'hot_storage_days' => 90,
                'warm_storage_days' => 365,
                'cold_storage_years' => 7,
                'pii_data' => true,
                'gdpr_right_to_erasure' => true,
                'policy_version' => 1,
            ],
            [
                'data_category' => 'export_logs',
                'hot_storage_days' => 90,
                'warm_storage_days' => 730,
                'cold_storage_years' => null,
                'pii_data' => false,
                'gdpr_right_to_erasure' => false,
                'policy_version' => 1,
            ],
            [
                'data_category' => 'sensitive_access_logs',
                'hot_storage_days' => 365,
                'warm_storage_days' => 1825,
                'cold_storage_years' => null,
                'pii_data' => true,
                'gdpr_right_to_erasure' => false,
                'policy_version' => 1,
            ],
            [
                'data_category' => 'verification_logs',
                'hot_storage_days' => 90,
                'warm_storage_days' => null,
                'cold_storage_years' => null,
                'pii_data' => false,
                'gdpr_right_to_erasure' => false,
                'policy_version' => 1,
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_retention_policies');
    }
};
