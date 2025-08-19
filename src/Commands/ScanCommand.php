<?php

namespace Rafaelogic\CodeSnoutr\Commands;

use Illuminate\Console\Command;
use Rafaelogic\CodeSnoutr\ScanManager;

class ScanCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'codesnoutr:scan 
                           {type=codebase : Type of scan (file, directory, codebase)}
                           {path? : Path to scan (required for file/directory scans)}
                           {--categories=* : Categories to scan (security, performance, quality, laravel). Use multiple flags: --categories=security --categories=quality}
                           {--format=table : Output format (table, json, export)}
                           {--export-path= : Path to export results}';

    /**
     * The console command description.
     */
    protected $description = 'Scan code for defects and issues';

    /**
     * Execute the console command.
     */
    public function handle(ScanManager $scanManager): int
    {
        $type = $this->argument('type');
        $path = $this->argument('path');
        $categories = $this->option('categories') ?: ['security', 'performance', 'quality', 'laravel'];
        $format = $this->option('format');

        // Validate inputs
        if (in_array($type, ['file', 'directory']) && !$path) {
            $this->error("Path is required for {$type} scans.");
            return self::FAILURE;
        }

        $this->info("Starting {$type} scan...");
        
        if ($path) {
            $this->info("Path: {$path}");
        }
        
        $this->info("Categories: " . implode(', ', $categories));

        try {
            // Start the scan
            $scan = $scanManager->scan($type, $path, $categories);

            // Display progress
            $this->displayProgress($scan);

            // Display results
            $this->displayResults($scan, $format);

            // Export if requested
            if ($exportPath = $this->option('export-path')) {
                $this->exportResults($scanManager, $scan, $exportPath);
            }

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error("Scan failed: " . $e->getMessage());
            
            if ($this->output->isVerbose()) {
                $this->error($e->getTraceAsString());
            }
            
            return self::FAILURE;
        }
    }

    /**
     * Display scan progress
     */
    protected function displayProgress($scan): void
    {
        $this->info("Scan ID: {$scan->id}");
        $this->info("Status: {$scan->status}");
        
        if ($scan->isCompleted()) {
            $this->info("Duration: {$scan->duration_seconds} seconds");
            $this->info("Files scanned: {$scan->total_files}");
        }
    }

    /**
     * Display scan results
     */
    protected function displayResults($scan, string $format): void
    {
        $issues = $scan->issues()->orderBySeverity()->get();

        if ($issues->isEmpty()) {
            $this->info("🎉 No issues found!");
            return;
        }

        $this->displaySummary($scan);

        if ($format === 'json') {
            $this->displayJsonResults($issues);
        } else {
            $this->displayTableResults($issues);
        }
    }

    /**
     * Display summary
     */
    protected function displaySummary($scan): void
    {
        $this->newLine();
        $this->info("📊 Scan Summary");
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Issues', $scan->total_issues],
                ['Critical', $scan->critical_issues],
                ['Warning', $scan->warning_issues],
                ['Info', $scan->info_issues],
                ['Files Scanned', $scan->total_files],
            ]
        );
    }

    /**
     * Display results in table format
     */
    protected function displayTableResults($issues): void
    {
        $this->newLine();
        $this->info("🔍 Issues Found");

        $rows = $issues->map(function ($issue) {
            return [
                $this->getSeverityIcon($issue->severity) . ' ' . ucfirst($issue->severity),
                $issue->category,
                $issue->file_name,
                $issue->line_number,
                $this->truncate($issue->title, 50),
            ];
        })->toArray();

        $this->table(
            ['Severity', 'Category', 'File', 'Line', 'Issue'],
            $rows
        );

        // Show detailed view for critical issues
        $criticalIssues = $issues->where('severity', 'critical');
        if ($criticalIssues->isNotEmpty()) {
            $this->displayCriticalDetails($criticalIssues);
        }
    }

    /**
     * Display critical issues details
     */
    protected function displayCriticalDetails($criticalIssues): void
    {
        $this->newLine();
        $this->error("🚨 Critical Issues Details");

        foreach ($criticalIssues as $issue) {
            $this->newLine();
            $this->error("File: {$issue->relative_path}:{$issue->line_number}");
            $this->error("Rule: {$issue->rule_name}");
            $this->error("Issue: {$issue->title}");
            $this->warn("Description: {$issue->description}");
            $this->info("Suggestion: {$issue->suggestion}");
            
            if ($issue->hasAiFix()) {
                $this->comment("🤖 AI Fix Available");
            }
        }
    }

    /**
     * Display results in JSON format
     */
    protected function displayJsonResults($issues): void
    {
        $data = $issues->map(function ($issue) {
            return [
                'file_path' => $issue->file_path,
                'line_number' => $issue->line_number,
                'severity' => $issue->severity,
                'category' => $issue->category,
                'rule_name' => $issue->rule_name,
                'title' => $issue->title,
                'description' => $issue->description,
                'suggestion' => $issue->suggestion,
                'has_ai_fix' => $issue->hasAiFix(),
            ];
        })->toArray();

        $this->line(json_encode($data, JSON_PRETTY_PRINT));
    }

    /**
     * Export results
     */
    protected function exportResults(ScanManager $scanManager, $scan, string $exportPath): void
    {
        $extension = pathinfo($exportPath, PATHINFO_EXTENSION);
        $format = $extension ?: 'json';

        $content = $scanManager->exportScan($scan, $format);
        
        file_put_contents($exportPath, $content);
        
        $this->info("Results exported to: {$exportPath}");
    }

    /**
     * Get severity icon
     */
    protected function getSeverityIcon(string $severity): string
    {
        return match($severity) {
            'critical' => '🔴',
            'warning' => '🟡',
            'info' => '🔵',
            default => '⚪',
        };
    }

    /**
     * Truncate text
     */
    protected function truncate(string $text, int $length): string
    {
        return strlen($text) > $length ? substr($text, 0, $length - 3) . '...' : $text;
    }
}
