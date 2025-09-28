<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel without full framework
$app = require_once 'vendor/laravel/framework/src/Illuminate/Foundation/helpers.php';

use Illuminate\Database\Capsule\Manager as Capsule;
use Carbon\Carbon;

// Setup database connection (adjust as needed)
$capsule = new Capsule;
$capsule->addConnection([
    'driver' => 'sqlite',
    'database' => ':memory:', // Use in-memory for demo or adjust path
    'prefix' => '',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();

// Create sample data manually
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

echo "Sample scan data structure ready!\n";
echo "Note: You can manually insert this data into your database for testing.\n";
foreach ($scans as $index => $scan) {
    echo "Scan " . ($index + 1) . ": " . $scan['type'] . " - " . $scan['status'] . " - " . $scan['total_issues'] . " issues\n";
}