<?php

// Simple test script to verify AI service initialization
require_once __DIR__ . '/vendor/autoload.php';

use Rafaelogic\CodeSnoutr\Services\AiAssistantService;

echo "Testing AI Service initialization...\n";

try {
    $aiService = new AiAssistantService();
    echo "✅ AI Service created successfully\n";
    
    $isAvailable = $aiService->isAvailable();
    echo "AI Available: " . ($isAvailable ? 'Yes' : 'No') . "\n";
    
    // Test getContextualHelp with null check
    try {
        $help = $aiService->getContextualHelp('test');
        echo "✅ getContextualHelp() method callable\n";
    } catch (\Exception $e) {
        echo "⚠️  getContextualHelp() error (expected if no API key): " . $e->getMessage() . "\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error creating AI Service: " . $e->getMessage() . "\n";
    echo "This is expected if database is not set up.\n";
}

echo "\nIf you see no fatal errors above, the null pointer fix is working correctly.\n";
