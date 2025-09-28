<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Remove confusing ai_auto_fix_enabled and related settings
        DB::table('codesnoutr_settings')->whereIn('key', [
            'ai_auto_fix_enabled',
            'ai_auto_fix_backup_disk',
            'ai_auto_fix_min_confidence',
            'ai_auto_fix_safe_mode',
            'ai_auto_fix_require_confirmation',
        ])->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to restore these settings as they were confusing
        // and didn't provide true automatic functionality
    }
};