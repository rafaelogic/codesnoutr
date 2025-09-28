<?php

use Illuminate\Support\Facades\DB;
use Rafaelogic\CodeSnoutr\Models\Scan;
use Rafaelogic\CodeSnoutr\Models\Issue;

try {
    // Check if the tables exist
    $tablesExist = DB::select("SHOW TABLES LIKE 'codesnoutr_scans'");
    echo "Tables exist: " . (count($tablesExist) > 0 ? "Yes" : "No") . "\n";
    
    if (count($tablesExist) > 0) {
        // Check if there are any scans
        $scanCount = Scan::count();
        echo "Total scans in database: " . $scanCount . "\n";
        
        if ($scanCount > 0) {
            $scans = Scan::latest()->limit(5)->get();
            echo "Recent scans:\n";
            foreach ($scans as $scan) {
                echo "- ID: {$scan->id}, Type: {$scan->type}, Status: {$scan->status}, Created: {$scan->created_at}\n";
            }
        } else {
            echo "No scans found in database.\n";
            echo "Creating sample scans...\n";
            
            // Create sample scans
            $sampleScans = [
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
                    'started_at' => now()->subHours(2),
                    'completed_at' => now()->subHours(1)->subMinutes(30),
                    'duration_seconds' => 1800,
                    'summary' => json_encode(['message' => 'Scan completed successfully']),
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
                    'started_at' => now()->subDays(1),
                    'completed_at' => now()->subDays(1)->addMinutes(5),
                    'duration_seconds' => 300,
                    'summary' => json_encode(['message' => 'Directory scan completed']),
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
                    'started_at' => now()->subDays(2),
                    'completed_at' => now()->subDays(2)->addMinutes(1),
                    'duration_seconds' => 60,
                    'summary' => json_encode(['message' => 'No issues found']),
                ]
            ];
            
            foreach ($sampleScans as $scanData) {
                Scan::create($scanData);
            }
            
            echo "Created " . count($sampleScans) . " sample scans.\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "This might mean the database is not set up or the tables don't exist.\n";
}