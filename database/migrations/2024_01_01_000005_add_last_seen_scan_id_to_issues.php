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
        Schema::table('codesnoutr_issues', function (Blueprint $table) {
            $table->unsignedBigInteger('last_seen_scan_id')->nullable()->after('scan_id');
            $table->foreign('last_seen_scan_id')->references('id')->on('codesnoutr_scans')->onDelete('set null');
            
            // Add index for better query performance on deduplication
            $table->index(['file_path', 'line_number', 'rule_id', 'fixed']);
        });
        
        // Update existing issues to set last_seen_scan_id to scan_id
        DB::statement('UPDATE codesnoutr_issues SET last_seen_scan_id = scan_id WHERE last_seen_scan_id IS NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('codesnoutr_issues', function (Blueprint $table) {
            $table->dropForeign(['last_seen_scan_id']);
            $table->dropIndex(['file_path', 'line_number', 'rule_id', 'fixed']);
            $table->dropColumn('last_seen_scan_id');
        });
    }
};