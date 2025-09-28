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
        Schema::table('codesnoutr_scans', function (Blueprint $table) {
            $table->integer('resolved_issues')->default(0)->after('info_issues');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('codesnoutr_scans', function (Blueprint $table) {
            $table->dropColumn('resolved_issues');
        });
    }
};