<?php

namespace Rafaelogic\CodeSnoutr\Commands;

use Illuminate\Console\Command;
use Rafaelogic\CodeSnoutr\Models\Issue;
use Illuminate\Support\Facades\DB;

class DeduplicateIssuesCommand extends Command
{
    protected $signature = 'codesnoutr:deduplicate-issues {--dry-run : Show what would be done without making changes}';
    protected $description = 'Remove duplicate issues from the database';

    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        
        $this->info('Scanning for duplicate issues...');

        // Find duplicate issues based on file_path, line_number, rule_id, and description
        $duplicates = DB::select("
            SELECT file_path, line_number, rule_id, description, COUNT(*) as count, 
                   GROUP_CONCAT(id ORDER BY created_at DESC) as ids
            FROM codesnoutr_issues 
            WHERE fixed = 0
            GROUP BY file_path, line_number, rule_id, description 
            HAVING count > 1
        ");

        if (empty($duplicates)) {
            $this->info('No duplicate issues found.');
            return 0;
        }

        $this->info('Found ' . count($duplicates) . ' sets of duplicate issues.');

        $totalDuplicatesRemoved = 0;

        foreach ($duplicates as $duplicate) {
            $ids = explode(',', $duplicate->ids);
            $keepId = array_shift($ids); // Keep the most recent one (first in DESC order)
            $removeIds = $ids; // Remove the rest

            $this->line("File: {$duplicate->file_path}:{$duplicate->line_number}");
            $this->line("  Rule: {$duplicate->rule_id}");
            $this->line("  Keeping issue ID: {$keepId}");
            $this->line("  Removing issue IDs: " . implode(', ', $removeIds));

            if (!$isDryRun) {
                // Update the kept issue to reference the latest scan
                $latestScanId = Issue::whereIn('id', [$keepId])->first()->scan_id;
                Issue::where('id', $keepId)->update(['last_seen_scan_id' => $latestScanId]);

                // Delete the duplicate issues
                Issue::whereIn('id', $removeIds)->delete();
            }

            $totalDuplicatesRemoved += count($removeIds);
        }

        if ($isDryRun) {
            $this->warn("DRY RUN: Would remove {$totalDuplicatesRemoved} duplicate issues.");
            $this->info('Run without --dry-run to actually remove duplicates.');
        } else {
            $this->success("Successfully removed {$totalDuplicatesRemoved} duplicate issues.");
        }

        return 0;
    }
}