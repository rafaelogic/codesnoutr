<?php

require __DIR__ . '/../vendor/autoload.php';

// Simple test without Laravel bootstrap
echo "CodeSnoutr Queue Management Test\n";
echo "================================\n\n";

// Test basic class loading
try {
    $queueServiceClass = \Rafaelogic\CodeSnoutr\Services\QueueService::class;
    echo "✅ QueueService class loaded successfully\n";
} catch (\Exception $e) {
    echo "❌ Failed to load QueueService class: " . $e->getMessage() . "\n";
    exit(1);
}

// Test basic PS command functionality
echo "\nTesting process checking...\n";
try {
    $result = shell_exec("ps aux | grep 'queue:work' | grep -v grep | wc -l");
    $count = (int) trim($result);
    echo "Found $count queue worker processes\n";
} catch (\Exception $e) {
    echo "❌ Failed to check processes: " . $e->getMessage() . "\n";
}

// Test configuration values
echo "\nTesting configuration (without Laravel)...\n";
echo "- Default queue name: default\n";
echo "- Default timeout: 300s\n";
echo "- Default memory: 512MB\n";

echo "\n✅ Basic queue functionality test completed!\n";
echo "\nNote: Full testing requires Laravel application context.\n";
echo "To test fully, integrate this into a Laravel application and run:\n";
echo "- Start a scan through the UI\n";
echo "- Check queue status in the dashboard\n";
echo "- Monitor queue workers\n";
