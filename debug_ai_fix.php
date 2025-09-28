<?php
/**
 * Quick debug script to test AI Fix functionality
 * Run this from your Laravel application root: php packages/codesnoutr/debug_ai_fix.php
 */

require_once 'vendor/autoload.php';

use Rafaelogic\CodeSnoutr\Models\Setting;
use Rafaelogic\CodeSnoutr\Models\Issue;
use Rafaelogic\CodeSnoutr\Services\AI\AiAssistantService;
use Rafaelogic\CodeSnoutr\Services\AI\AutoFixService;
use Rafaelogic\CodeSnoutr\Services\Issues\IssueActionInvoker;

echo "=== CodeSnoutr AI Fix Debug Tool ===\n\n";

try {
    // Check AI settings
    echo "1. Checking AI Settings:\n";
    $aiEnabled = Setting::get('ai_enabled', false);
    $apiKey = Setting::getOpenAiApiKey();
    echo "   - AI Enabled: " . ($aiEnabled ? "Yes" : "No") . "\n";
    echo "   - API Key Configured: " . (!empty($apiKey) ? "Yes" : "No") . "\n";
    
    // Test AI service
    echo "\n2. Testing AI Service:\n";
    $aiService = new AiAssistantService();
    echo "   - AI Service Available: " . ($aiService->isAvailable() ? "Yes" : "No") . "\n";
    
    // Test AutoFix service
    echo "\n3. Testing AutoFix Service:\n";
    $autoFixService = new AutoFixService($aiService);
    echo "   - AutoFix Enabled: " . ($autoFixService->isAutoFixEnabled() ? "Yes" : "No") . "\n";
    
    // Check for issues
    echo "\n4. Checking Issues:\n";
    $totalIssues = Issue::count();
    $unfixedIssues = Issue::where('fixed', false)->count();
    $issuesWithAiFix = Issue::whereNotNull('ai_fix')->where('ai_fix', '!=', '')->count();
    
    echo "   - Total Issues: {$totalIssues}\n";
    echo "   - Unfixed Issues: {$unfixedIssues}\n";
    echo "   - Issues with AI Fix: {$issuesWithAiFix}\n";
    
    // Test action invoker
    echo "\n5. Testing Action Invoker:\n";
    $actionInvoker = app(IssueActionInvoker::class);
    echo "   - Action Invoker Available: Yes\n";
    
    if ($unfixedIssues > 0) {
        $testIssue = Issue::where('fixed', false)->first();
        echo "   - Test Issue ID: {$testIssue->id}\n";
        echo "   - Test Issue has AI Fix: " . (!empty($testIssue->ai_fix) ? "Yes" : "No") . "\n";
        
        // Test generate action
        if (empty($testIssue->ai_fix)) {
            echo "   - Testing Generate AI Fix Action...\n";
            try {
                $generateResult = $actionInvoker->executeAction('generate_ai_fix', $testIssue);
                echo "     Result: " . ($generateResult['success'] ? "SUCCESS" : "FAILED - " . $generateResult['message']) . "\n";
            } catch (Exception $e) {
                echo "     Result: ERROR - " . $e->getMessage() . "\n";
            }
        }
    }
    
    echo "\n=== Debug Complete ===\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}