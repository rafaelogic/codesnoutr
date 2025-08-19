# AI Assistant Status Check

Since the debug script needs to run within a Laravel application context, here are manual steps to check the AI assistant status:

## Option 1: Use Laravel Tinker

From your Laravel application root, run:

```bash
php artisan tinker
```

Then execute these commands:

```php
// Check if settings table exists
use Rafaelogic\CodeSnoutr\Models\Setting;
Setting::all();

// Check specific AI settings
Setting::getValue('ai_enabled', false);
Setting::getValue('openai_api_key', '');
Setting::getValue('openai_model', 'gpt-3.5-turbo');

// Test AI service
use Rafaelogic\CodeSnoutr\Services\AiAssistantService;
$service = new AiAssistantService();
$service->isAvailable();
```

## Option 2: Check in the Browser

1. Go to your Laravel app's CodeSnoutr settings page
2. Configure the AI settings:
   - Enable AI: Check the box
   - API Key: Enter your OpenAI API key
   - Model: Select a model (default: gpt-3.5-turbo)
3. Save the settings
4. Navigate to the CodeSnoutr dashboard
5. Look at the Smart Assistant panel - it should now show "AI Assistant Available" instead of "AI Assistant Not Available"
6. Click the "Refresh Status" button if needed

## Option 3: Check Debug Info in Browser

The Smart Assistant component now includes debug information. After configuring AI settings:

1. Go to the CodeSnoutr dashboard
2. Look at the Smart Assistant panel
3. The debug info will show:
   - Whether AI is enabled
   - Whether API key is set
   - Current model
   - Service availability status

## Common Issues

1. **Settings not saving**: Make sure the form is submitted properly
2. **API key encryption**: The key should be automatically encrypted when saved
3. **Cache issues**: Try clearing Laravel cache: `php artisan cache:clear`
4. **Database issues**: Ensure migrations have been run: `php artisan migrate`

## What Should Work

Once properly configured:
- AI toggle should be enabled
- API key should be set (will show as encrypted in database)
- Smart Assistant should show "Available" status
- You should be able to ask questions in the assistant
