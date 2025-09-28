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
    echo "Testing AI fix parsing for issue {$issueId}\n";
    echo "Raw AI fix data:\n";
    echo "---\n";
    echo $issue->ai_fix . "\n";
    echo "---\n\n";
    
    // Test the parsing
    $aiService = new AiAssistantService();
    $autoFixService = new AutoFixService($aiService);
    
    // Use reflection to call the protected method
    $reflection = new ReflectionClass($autoFixService);
    $parseMethod = $reflection->getMethod('parseAiFixData');
    $parseMethod->setAccessible(true);
    
    try {
        $parsedData = $parseMethod->invoke($autoFixService, $issue->ai_fix);
        echo "Parsed AI fix data:\n";
        print_r($parsedData);
        
        // Test validation
        $validateMethod = $reflection->getMethod('validateFixData');
        $validateMethod->setAccessible(true);
        $isValid = $validateMethod->invoke($autoFixService, $parsedData);
        
        echo "\nValidation result: " . ($isValid ? 'VALID' : 'INVALID') . "\n";
        
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
    
} else {
    echo "Issue {$issueId} not found\n";
}