<?php
require_once __DIR__ . '/vendor/autoload.php';

use Rafaelogic\CodeSnoutr\Models\Setting;
use Rafaelogic\CodeSnoutr\Services\AiAssistantService;

echo "=== AI Settings Debug ===\n";

try {
    echo "1. Database Settings:\n";
    $aiEnabled = Setting::getValue('ai_enabled', false);
    $apiKey = Setting::getValue('openai_api_key', '');
    $model = Setting::getValue('openai_model', 'gpt-3.5-turbo');
    
    echo "   - AI Enabled: " . ($aiEnabled ? 'Yes' : 'No') . "\n";
    echo "   - API Key exists: " . (!empty($apiKey) ? 'Yes' : 'No') . "\n";
    echo "   - API Key length: " . strlen($apiKey) . "\n";
    echo "   - Model: " . $model . "\n\n";
    
    echo "2. AI Service Status:\n";
    $aiService = new AiAssistantService();
    echo "   - Service created: Yes\n";
    echo "   - Is Available: " . ($aiService->isAvailable() ? 'Yes' : 'No') . "\n\n";
    
    echo "3. Connection Test:\n";
    $result = $aiService->testConnection();
    echo "   - Success: " . ($result['success'] ? 'Yes' : 'No') . "\n";
    echo "   - Message: " . $result['message'] . "\n";
    if (isset($result['details'])) {
        echo "   - Details: " . $result['details'] . "\n";
    }
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
