<?php

require_once '/Users/rafaelogic/Desktop/projects/pwm/aristo/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once '/Users/rafaelogic/Desktop/projects/pwm/aristo/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Rafaelogic\CodeSnoutr\Models\Issue;
use Rafaelogic\CodeSnoutr\Services\AI\AutoFixService;
use Rafaelogic\CodeSnoutr\Services\AI\AiAssistantService;

$issueId = 42839;
$issue = Issue::find($issueId);

if ($issue) {
    echo "Testing AI fix application for issue {$issueId}\n";
    echo "File: {$issue->file_path}\n";
    echo "Line: {$issue->line_number}\n\n";
    
    // Test the full AI fix application
    $aiService = new AiAssistantService();
    $autoFixService = new AutoFixService($aiService);
    
    try {
        echo "Original file content around line {$issue->line_number}:\n";
        echo "---\n";
        $fileContent = file_get_contents($issue->file_path);
        $lines = explode("\n", $fileContent);
        $startLine = max(0, $issue->line_number - 6);
        $endLine = min(count($lines) - 1, $issue->line_number + 4);
        
        for ($i = $startLine; $i <= $endLine; $i++) {
            $marker = ($i + 1 == $issue->line_number) ? '>>> ' : '    ';
            printf("%s%3d: %s\n", $marker, $i + 1, $lines[$i] ?? '');
        }
        echo "---\n\n";
        
        echo "Parsing AI fix data...\n";
        // Use reflection to call the protected method
        $reflection = new ReflectionClass($autoFixService);
        $parseMethod = $reflection->getMethod('parseAiFixData');
        $parseMethod->setAccessible(true);
        $fixData = $parseMethod->invoke($autoFixService, $issue->ai_fix);
        
        echo "Parsed fix data:\n";
        print_r($fixData);
        echo "\n";
        
        // Test the content modification step by step
        echo "Testing content modification...\n";
        $originalContent = file_get_contents($issue->file_path);
        $lines = explode("\n", $originalContent);
        
        $applyMethod = $reflection->getMethod('applyFixToContent');
        $applyMethod->setAccessible(true);
        $modifiedContent = $applyMethod->invoke($autoFixService, $lines, $issue, $fixData);
        
        echo "Modified content preview:\n";
        echo "---\n";
        echo substr($modifiedContent, 0, 1000) . (strlen($modifiedContent) > 1000 ? '...' : '') . "\n";
        echo "---\n\n";
        
        // Test validation
        $validateMethod = $reflection->getMethod('validateModifiedContent');
        $validateMethod->setAccessible(true);
        $isValid = $validateMethod->invoke($autoFixService, $modifiedContent, $issue->file_path);
        
        echo "Validation result: " . ($isValid ? 'VALID' : 'INVALID') . "\n\n";
        
        echo "Applying AI fix...\n";
        $result = $autoFixService->applyFix($issue, $fixData);
        
        echo "Application result:\n";
        print_r($result);
        
        if ($result['success']) {
            echo "\nFile content after fix:\n";
            echo "---\n";
            $newContent = file_get_contents($issue->file_path);
            $newLines = explode("\n", $newContent);
            
            for ($i = $startLine; $i <= $endLine; $i++) {
                $marker = ($i + 1 == $issue->line_number) ? '>>> ' : '    ';
                printf("%s%3d: %s\n", $marker, $i + 1, $newLines[$i] ?? '');
            }
            echo "---\n";
        }
        
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
        echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    }
    
} else {
    echo "Issue {$issueId} not found\n";
}