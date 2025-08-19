<?php

// AI Settings Debug Script
require_once __DIR__ . '/vendor/autoload.php';

use Rafaelogic\CodeSnoutr\Models\Setting;
use Rafaelogic\CodeSnoutr\Services\AiAssistantService;

echo "=== AI Settings Debug ===\n\n";

try {
    echo "1. Testing Setting model...\n";
    
    // Check if settings table exists and can be queried
    $allSettings = Setting::all();
    echo "   - Settings table accessible: YES\n";
    echo "   - Total settings in database: " . $allSettings->count() . "\n\n";
    
    echo "2. Checking AI-related settings...\n";
    
    $aiEnabled = Setting::getValue('ai_enabled', false);
    $apiKey = Setting::getValue('openai_api_key', '');
    $model = Setting::getValue('openai_model', 'gpt-3.5-turbo');
    
    echo "   - ai_enabled: " . ($aiEnabled ? 'true' : 'false') . "\n";
    echo "   - openai_api_key exists: " . (!empty($apiKey) ? 'YES' : 'NO') . "\n";
    echo "   - openai_api_key length: " . strlen($apiKey) . " characters\n";
    echo "   - openai_model: " . $model . "\n\n";
    
    echo "3. Testing AiAssistantService...\n";
    
    $aiService = new AiAssistantService();
    echo "   - Service created: YES\n";
    echo "   - Service isAvailable(): " . ($aiService->isAvailable() ? 'YES' : 'NO') . "\n\n";
    
    echo "4. Raw settings inspection...\n";
    $aiSettings = Setting::where('key', 'like', '%ai%')->orWhere('key', 'like', '%openai%')->get();
    foreach ($aiSettings as $setting) {
        echo "   - {$setting->key}: " . json_encode($setting->value) . " (encrypted: " . ($setting->is_encrypted ? 'yes' : 'no') . ")\n";
    }
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "This likely means the database or migrations are not set up.\n";
}

echo "\n=== End Debug ===\n";
