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
        Schema::create('codesnoutr_scans', function (Blueprint $table) {
            $table->id();
            $table->string('type')->index(); // 'manual', 'single-file', 'directory', 'codebase'
            $table->string('target')->nullable(); // The target path or file
            $table->string('status')->default('pending')->index(); // 'pending', 'running', 'completed', 'failed'
            $table->json('scan_options'); // paths, categories, etc.
            $table->json('paths_scanned')->nullable();
            $table->integer('total_files')->default(0);
            $table->integer('total_issues')->default(0);
            $table->integer('critical_issues')->default(0);
            $table->integer('warning_issues')->default(0);
            $table->integer('info_issues')->default(0);
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->integer('duration_seconds')->nullable(); // Scan duration
            $table->json('summary')->nullable(); // Overall scan summary
            $table->text('error_message')->nullable(); // If scan failed
            $table->decimal('ai_cost', 10, 4)->default(0.0000); // AI usage cost
            $table->timestamps();

            $table->index(['type', 'status']);
            $table->index(['created_at', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('codesnoutr_scans');
    }
};
