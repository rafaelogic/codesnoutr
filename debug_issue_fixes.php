<?php

require_once '/Users/rafaelogic/Desktop/projects/pwm/aristo/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once '/Users/rafaelogic/Desktop/projects/pwm/aristo/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Rafaelogic\CodeSnoutr\Models\Issue;

$issueIds = [42839, 42840];

foreach ($issueIds as $issueId) {
    $issue = Issue::find($issueId);
    
    if ($issue) {
        echo "Issue ID: {$issue->id}\n";
        echo "File: {$issue->file_path}\n";
        echo "Line: {$issue->line_number}\n";
        echo "AI Fix exists: " . (!empty($issue->ai_fix) ? 'Yes' : 'No') . "\n";
        
        if (!empty($issue->ai_fix)) {
            echo "AI Fix raw data:\n";
            echo $issue->ai_fix . "\n";
            echo "JSON decode result:\n";
            $aiFixData = json_decode($issue->ai_fix, true);
            if ($aiFixData === null) {
                echo "JSON decode failed. Error: " . json_last_error_msg() . "\n";
            } else {
                print_r($aiFixData);
            }
        }
        echo "\n---\n\n";
    } else {
        echo "Issue {$issueId} not found\n\n";
    }
}