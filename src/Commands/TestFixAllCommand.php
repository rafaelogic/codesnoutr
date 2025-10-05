<?php

namespace Rafaelogic\CodeSnoutr\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Rafaelogic\CodeSnoutr\Jobs\FixAllIssuesJob;
use Rafaelogic\CodeSnoutr\Models\Issue;

class TestFixAllCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'codesnoutr:test-fix-all
                            {--session-id= : Custom session ID for testing}
                            {--sync : Run synchronously instead of queued}';

    /**
     * The console command description.
     */
    protected $description = 'Test the Fix All background job functionality';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $sessionId = $this->option('session-id') ?? 'test-' . uniqid();
        $sync = $this->option('sync');

        $this->info("Testing Fix All functionality with session ID: {$sessionId}");

        // Check if we have any issues to fix
        $issues = Issue::where('fixed', false)->limit(5)->get();
        
        if ($issues->isEmpty()) {
            $this->warn('No unfixed issues found. Create some test issues first.');
            return 1;
        }

        $this->info("Found {$issues->count()} issues to test with:");
        foreach ($issues as $issue) {
            $this->line("  - ID {$issue->id}: {$issue->title} ({$issue->severity})");
        }

        // Initialize progress in cache
        Cache::put("fix_all_progress_{$sessionId}", [
            'status' => 'initializing',
            'total_issues' => $issues->count(),
            'processed_issues' => 0,
            'current_file' => null,
            'results' => [],
            'errors' => [],
            'started_at' => now()->toISOString(),
            'completed_at' => null,
        ], now()->addHours(2));

        $this->info('Initialized progress cache');

        if ($sync) {
            $this->info('Running synchronously for testing...');
            $job = new FixAllIssuesJob($sessionId);
            $job->handle();
        } else {
            $this->info('Dispatching job to queue...');
            FixAllIssuesJob::dispatch($sessionId);
        }

        $this->info('Job dispatched successfully!');
        $this->info("Monitor progress with: php artisan codesnoutr:monitor-fix-all {$sessionId}");
        $this->info("Or check cache key: fix_all_progress_{$sessionId}");

        return 0;
    }
}