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
        Schema::create('codesnoutr_issues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scan_id')->constrained('codesnoutr_scans')->onDelete('cascade');
            $table->string('file_path')->index();
            $table->integer('line_number');
            $table->integer('column_number')->nullable();
            $table->string('category')->index(); // 'security', 'performance', 'quality', 'laravel'
            $table->string('severity')->index(); // 'critical', 'warning', 'info'
            $table->string('rule_name')->index(); // Specific rule that triggered
            $table->string('rule_id')->index(); // Unique rule identifier
            $table->text('title'); // Short issue title
            $table->text('description'); // Detailed description
            $table->text('suggestion'); // Manual fix suggestion
            $table->json('context'); // Code snippet, surrounding context, metadata
            $table->text('ai_fix')->nullable(); // AI-generated fix
            $table->text('ai_explanation')->nullable(); // AI explanation of the fix
            $table->decimal('ai_confidence', 3, 2)->nullable(); // AI confidence score (0.00-1.00)
            $table->boolean('fixed')->default(false);
            $table->timestamp('fixed_at')->nullable();
            $table->string('fix_method')->nullable(); // 'manual', 'ai', 'ignored'
            $table->json('metadata')->nullable(); // Additional rule-specific data
            $table->timestamps();

            $table->index(['scan_id', 'severity']);
            $table->index(['file_path', 'line_number']);
            $table->index(['category', 'severity']);
            $table->index(['rule_id', 'severity']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('codesnoutr_issues');
    }
};
