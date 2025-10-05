<?php

namespace Rafaelogic\CodeSnoutr\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class MonitorFixAllCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'codesnoutr:monitor-fix-all
                            {session-id : Session ID to monitor}
                            {--watch : Keep watching for updates}
                            {--interval=3 : Refresh interval in seconds when watching}';

    /**
     * The console command description.
     */
    protected $description = 'Monitor the progress of a Fix All background job';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $sessionId = $this->argument('session-id');
        $watch = $this->option('watch');
        $interval = (int) $this->option('interval');

        do {
            $this->displayProgress($sessionId);
            
            if ($watch) {
                sleep($interval);
                // Clear console for next update
                if (function_exists('system')) {
                    system('clear');
                }
            }
        } while ($watch && $this->isJobStillRunning($sessionId));

        return 0;
    }

    /**
     * Display current progress
     */
    protected function displayProgress(string $sessionId): void
    {
        $cacheKey = "fix_all_progress_{$sessionId}";
        $progress = Cache::get($cacheKey);

        if (!$progress) {
            $this->error("No progress found for session ID: {$sessionId}");
            $this->info("Available cache keys:");
            
            // Try to find similar keys
            $allKeys = Cache::getRedis()->keys('*fix_all_progress*') ?? [];
            foreach ($allKeys as $key) {
                $this->line("  - {$key}");
            }
            return;
        }

        $this->info("Fix All Progress - Session: {$sessionId}");
        $this->info(str_repeat('=', 50));
        
        $this->line("Status: " . strtoupper($progress['status']));
        $this->line("Started: " . $progress['started_at']);
        
        if ($progress['completed_at']) {
            $this->line("Completed: " . $progress['completed_at']);
        }

        // Progress bar
        $total = $progress['total_issues'] ?? 0;
        $processed = $progress['processed_issues'] ?? 0;
        
        if ($total > 0) {
            $percentage = round(($processed / $total) * 100, 1);
            $this->line("Progress: {$processed}/{$total} ({$percentage}%)");
            
            // ASCII progress bar
            $barWidth = 40;
            $filled = round(($percentage / 100) * $barWidth);
            $bar = str_repeat('█', $filled) . str_repeat('░', $barWidth - $filled);
            $this->line("[{$bar}] {$percentage}%");
        }

        if (isset($progress['current_file']) && $progress['current_file']) {
            $this->line("Current File: " . $progress['current_file']);
        }

        // Results summary
        $results = $progress['results'] ?? [];
        $errors = $progress['errors'] ?? [];

        if (!empty($results)) {
            $this->info("\nResults ({count} items):", ['count' => count($results)]);
            foreach (array_slice($results, -5) as $result) {
                $status = $result['success'] ? '✓' : '✗';
                $this->line("  {$status} Issue #{$result['issue_id']}: {$result['message']}");
            }
            if (count($results) > 5) {
                $this->line("  ... and " . (count($results) - 5) . " more");
            }
        }

        if (!empty($errors)) {
            $this->error("\nErrors ({count} items):", ['count' => count($errors)]);
            foreach (array_slice($errors, -3) as $error) {
                $this->line("  ✗ " . $error);
            }
            if (count($errors) > 3) {
                $this->line("  ... and " . (count($errors) - 3) . " more");
            }
        }

        $this->line('');
    }

    /**
     * Check if job is still running
     */
    protected function isJobStillRunning(string $sessionId): bool
    {
        $progress = Cache::get("fix_all_progress_{$sessionId}");
        
        if (!$progress) {
            return false;
        }

        return in_array($progress['status'], ['initializing', 'processing']);
    }
}