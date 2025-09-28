<?php

namespace Rafaelogic\CodeSnoutr\Commands;

use Illuminate\Console\Command;
use Rafaelogic\CodeSnoutr\Models\Scan;

class UpdateResolvedIssuesCounts extends Command
{
    protected $signature = 'codesnoutr:update-resolved-counts';
    
    protected $description = 'Update resolved issues counts for existing scans';

    public function handle()
    {
        $this->info('Updating resolved issues counts...');
        
        $scans = Scan::with('issues')->get();
        $bar = $this->output->createProgressBar($scans->count());
        
        foreach ($scans as $scan) {
            $resolvedCount = $scan->issues()->where('fixed', true)->count();
            $scan->update(['resolved_issues' => $resolvedCount]);
            $bar->advance();
        }
        
        $bar->finish();
        $this->info("\nUpdated resolved issues counts for {$scans->count()} scans.");
        
        return 0;
    }
}