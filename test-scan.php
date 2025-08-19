<?php

require_once 'vendor/autoload.php';

use Rafaelogic\CodeSnoutr\ScanManager;
use Rafaelogic\CodeSnoutr\Models\Scan;

// Simple test to verify scan functionality
echo "Testing CodeSnoutr scan functionality...\n";

try {
    $scanManager = new ScanManager();
    
    // Test file scan
    echo "Testing file scan...\n";
    $scan = $scanManager->scan(
        'file',
        'test-vulnerable.php',
        ['security'],
        ['file_extensions' => ['php']]
    );
    
    echo "Scan completed with ID: " . $scan->id . "\n";
    echo "Status: " . $scan->status . "\n";
    echo "Total files: " . $scan->total_files . "\n";
    echo "Total issues: " . $scan->total_issues . "\n";
    
    if ($scan->issues->count() > 0) {
        echo "Issues found:\n";
        foreach ($scan->issues as $issue) {
            echo "- " . $issue->title . " (Severity: " . $issue->severity . ")\n";
        }
    } else {
        echo "No issues found in scan.\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
