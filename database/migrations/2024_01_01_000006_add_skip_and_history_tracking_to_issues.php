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
        Schema::table('codesnoutr_issues', function (Blueprint $table) {
            // Skip tracking
            $table->boolean('skipped')->default(false)->after('fixed_at');
            $table->timestamp('skipped_at')->nullable()->after('skipped');
            $table->text('skip_reason')->nullable()->after('skipped_at');
            
            // Fix attempt history (JSON column)
            $table->json('fix_attempts')->nullable()->after('skip_reason');
            $table->integer('fix_attempt_count')->default(0)->after('fix_attempts');
            $table->timestamp('last_fix_attempt_at')->nullable()->after('fix_attempt_count');
        });
        
        // Add skip and fixed issue counters to scans table
        Schema::table('codesnoutr_scans', function (Blueprint $table) {
            $table->integer('fixed_issues')->default(0);
            $table->integer('skipped_issues')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('codesnoutr_issues', function (Blueprint $table) {
            $table->dropColumn([
                'skipped',
                'skipped_at',
                'skip_reason',
                'fix_attempts',
                'fix_attempt_count',
                'last_fix_attempt_at',
            ]);
        });
        
        Schema::table('codesnoutr_scans', function (Blueprint $table) {
            $table->dropColumn(['fixed_issues', 'skipped_issues']);
        });
    }
};
