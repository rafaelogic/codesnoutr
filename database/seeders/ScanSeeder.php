<?php

namespace Rafaelogic\CodeSnoutr\Database\Seeders;

use Illuminate\Database\Seeder;
use Rafaelogic\CodeSnoutr\Models\Scan;
use Rafaelogic\CodeSnoutr\Models\Issue;
use Carbon\Carbon;

class ScanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Create sample scans
        $scans = [
            [
                'type' => 'codebase',
                'target' => null,
                'status' => 'completed',
                'scan_options' => json_encode(['categories' => ['security', 'performance']]),
                'paths_scanned' => json_encode(['app/', 'resources/', 'database/']),
                'total_files' => 245,
                'total_issues' => 12,
                'critical_issues' => 2,
                'warning_issues' => 7,
                'info_issues' => 3,
                'started_at' => Carbon::now()->subHours(2),
                'completed_at' => Carbon::now()->subHours(1)->subMinutes(30),
                'duration_seconds' => 1800,
                'summary' => json_encode(['message' => 'Scan completed successfully']),
                'created_at' => Carbon::now()->subHours(2),
                'updated_at' => Carbon::now()->subHours(1)->subMinutes(30),
            ],
            [
                'type' => 'directory',
                'target' => 'app/Http/Controllers',
                'status' => 'completed',
                'scan_options' => json_encode(['categories' => ['laravel', 'quality']]),
                'paths_scanned' => json_encode(['app/Http/Controllers/']),
                'total_files' => 25,
                'total_issues' => 5,
                'critical_issues' => 0,
                'warning_issues' => 3,
                'info_issues' => 2,
                'started_at' => Carbon::now()->subDays(1),
                'completed_at' => Carbon::now()->subDays(1)->addMinutes(5),
                'duration_seconds' => 300,
                'summary' => json_encode(['message' => 'Directory scan completed']),
                'created_at' => Carbon::now()->subDays(1),
                'updated_at' => Carbon::now()->subDays(1)->addMinutes(5),
            ],
            [
                'type' => 'file',
                'target' => 'app/Models/User.php',
                'status' => 'completed',
                'scan_options' => json_encode(['categories' => ['security']]),
                'paths_scanned' => json_encode(['app/Models/User.php']),
                'total_files' => 1,
                'total_issues' => 0,
                'critical_issues' => 0,
                'warning_issues' => 0,
                'info_issues' => 0,
                'started_at' => Carbon::now()->subDays(2),
                'completed_at' => Carbon::now()->subDays(2)->addMinutes(1),
                'duration_seconds' => 60,
                'summary' => json_encode(['message' => 'No issues found']),
                'created_at' => Carbon::now()->subDays(2),
                'updated_at' => Carbon::now()->subDays(2)->addMinutes(1),
            ],
            [
                'type' => 'codebase',
                'target' => null,
                'status' => 'running',
                'scan_options' => json_encode(['categories' => ['security', 'performance', 'quality']]),
                'paths_scanned' => json_encode([]),
                'total_files' => 0,
                'total_issues' => 0,
                'critical_issues' => 0,
                'warning_issues' => 0,
                'info_issues' => 0,
                'started_at' => Carbon::now()->subMinutes(15),
                'completed_at' => null,
                'duration_seconds' => null,
                'summary' => null,
                'created_at' => Carbon::now()->subMinutes(15),
                'updated_at' => Carbon::now()->subMinutes(15),
            ],
            [
                'type' => 'directory',
                'target' => 'app/Services',
                'status' => 'failed',
                'scan_options' => json_encode(['categories' => ['quality']]),
                'paths_scanned' => json_encode([]),
                'total_files' => 0,
                'total_issues' => 0,
                'critical_issues' => 0,
                'warning_issues' => 0,
                'info_issues' => 0,
                'started_at' => Carbon::now()->subHours(3),
                'completed_at' => Carbon::now()->subHours(3)->addMinutes(1),
                'duration_seconds' => 60,
                'error_message' => 'Directory not found',
                'summary' => null,
                'created_at' => Carbon::now()->subHours(3),
                'updated_at' => Carbon::now()->subHours(3)->addMinutes(1),
            ],
        ];

        foreach ($scans as $scanData) {
            $scan = Scan::create($scanData);
            
            // Create some sample issues for completed scans with issues
            if ($scan->status === 'completed' && $scan->total_issues > 0) {
                $this->createSampleIssues($scan);
            }
        }
        
        if (isset($this->command)) {
            $this->command->info('Created ' . count($scans) . ' sample scans');
        }
    }
    
    private function createSampleIssues(Scan $scan)
    {
        $sampleIssues = [
            [
                'scan_id' => $scan->id,
                'file_path' => 'app/Http/Controllers/UserController.php',
                'line_number' => 45,
                'column_number' => 12,
                'severity' => 'critical',
                'category' => 'security',
                'title' => 'SQL Injection Vulnerability',
                'description' => 'Raw SQL query without parameter binding detected',
                'code_snippet' => '$users = DB::select("SELECT * FROM users WHERE id = " . $id);',
                'fix_suggestion' => 'Use parameter binding: DB::select("SELECT * FROM users WHERE id = ?", [$id]);',
                'status' => 'open',
                'fixed' => false,
            ],
            [
                'scan_id' => $scan->id,
                'file_path' => 'app/Models/User.php',
                'line_number' => 23,
                'column_number' => 5,
                'severity' => 'warning',
                'category' => 'performance',
                'title' => 'N+1 Query Problem',
                'description' => 'Potential N+1 query detected in relationship loading',
                'code_snippet' => '$users->posts->comments',
                'fix_suggestion' => 'Use eager loading: $users->load("posts.comments")',
                'status' => 'open',
                'fixed' => false,
            ],
        ];
        
        foreach ($sampleIssues as $issueData) {
            if (count(Issue::where('scan_id', $scan->id)->get()) < $scan->total_issues) {
                Issue::create($issueData);
            }
        }
    }
}