<?php

namespace Rafaelogic\CodeSnoutr\Tests\Feature;

use Tests\TestCase;
use Livewire\Livewire;
use Rafaelogic\CodeSnoutr\Models\Setting;
use Rafaelogic\CodeSnoutr\Livewire\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function settings_component_initializes_with_default_values()
    {
        Livewire::test(Settings::class)
            ->assertSet('settings.ai_enabled', false)
            ->assertSet('settings.ai_auto_fix_enabled', false)
            ->assertSet('settings.openai_api_key', '')
            ->assertSet('settings.scan_auto_run', false)
            ->assertSet('settings.notification_enabled', true)
            ->assertSet('connectionStatus.connected', false)
            ->assertSet('connectionStatus.message', 'Not connected')
            ->assertSee('CodeSnoutr Settings');
    }

    /** @test */
    public function settings_component_loads_existing_settings()
    {
        // Create some existing settings
        Setting::set('ai_enabled', true);
        Setting::set('openai_api_key', 'sk-test-key');
        Setting::set('notification_enabled', false);

        Livewire::test(Settings::class)
            ->assertSet('settings.ai_enabled', true)
            ->assertSet('settings.openai_api_key', 'sk-test-key')
            ->assertSet('settings.notification_enabled', false);
    }

    /** @test */
    public function can_save_settings()
    {
        Livewire::test(Settings::class)
            ->set('settings.ai_enabled', true)
            ->set('settings.openai_api_key', 'sk-new-test-key')
            ->set('settings.ai_auto_fix_enabled', true)
            ->set('settings.scan_auto_run', true)
            ->set('settings.notification_enabled', false)
            ->call('saveSettings')
            ->assertDispatched('notification');

        // Verify settings were saved to database
        $this->assertTrue(Setting::get('ai_enabled'));
        $this->assertEquals('sk-new-test-key', Setting::get('openai_api_key'));
        $this->assertTrue(Setting::get('ai_auto_fix_enabled'));
        $this->assertTrue(Setting::get('scan_auto_run'));
        $this->assertFalse(Setting::get('notification_enabled'));
    }

    /** @test */
    public function can_reset_settings_to_defaults()
    {
        // Set some custom values first
        Setting::set('ai_enabled', true);
        Setting::set('openai_api_key', 'sk-test-key');
        Setting::set('notification_enabled', false);

        Livewire::test(Settings::class)
            ->call('resetSettings')
            ->assertSet('settings.ai_enabled', false)
            ->assertSet('settings.ai_auto_fix_enabled', false)
            ->assertSet('settings.openai_api_key', '')
            ->assertSet('settings.scan_auto_run', false)
            ->assertSet('settings.notification_enabled', true)
            ->assertDispatched('notification');

        // Verify settings were reset in database
        $this->assertFalse(Setting::get('ai_enabled'));
        $this->assertEquals('', Setting::get('openai_api_key'));
        $this->assertTrue(Setting::get('notification_enabled'));
    }

    /** @test */
    public function can_test_openai_connection_successfully()
    {
        // Mock successful OpenAI API response
        Http::fake([
            'api.openai.com/*' => Http::response([
                'model' => 'gpt-3.5-turbo',
                'choices' => [
                    [
                        'message' => [
                            'content' => 'Connection test successful'
                        ]
                    ]
                ]
            ], 200)
        ]);

        Livewire::test(Settings::class)
            ->set('settings.openai_api_key', 'sk-test-key')
            ->call('testOpenAiConnection')
            ->assertSet('connectionStatus.connected', true)
            ->assertSet('connectionStatus.message', 'Connection successful! API key is valid.')
            ->assertDispatched('notification');
    }

    /** @test */
    public function handles_openai_connection_failure()
    {
        // Mock failed OpenAI API response
        Http::fake([
            'api.openai.com/*' => Http::response([
                'error' => [
                    'message' => 'Invalid API key provided'
                ]
            ], 401)
        ]);

        Livewire::test(Settings::class)
            ->set('settings.openai_api_key', 'invalid-key')
            ->call('testOpenAiConnection')
            ->assertSet('connectionStatus.connected', false)
            ->assertStringContainsString('Connection failed', $this->getConnectionMessage())
            ->assertDispatched('notification');
    }

    /** @test */
    public function requires_api_key_for_connection_test()
    {
        Livewire::test(Settings::class)
            ->set('settings.openai_api_key', '')
            ->call('testOpenAiConnection')
            ->assertSet('connectionStatus.connected', false)
            ->assertSet('connectionStatus.message', 'Please enter an API key first')
            ->assertDispatched('notification');
    }

    /** @test */
    public function auto_fix_is_disabled_when_ai_is_disabled()
    {
        Livewire::test(Settings::class)
            ->set('settings.ai_enabled', false)
            ->set('settings.ai_auto_fix_enabled', true)
            ->call('updatedSettings')
            ->assertSet('settings.ai_auto_fix_enabled', false);
    }

    /** @test */
    public function connection_status_resets_when_api_key_changes()
    {
        $component = Livewire::test(Settings::class);
        
        // Set initial connection status
        $component->set('connectionStatus', [
            'connected' => true,
            'message' => 'Connection successful!'
        ]);
        
        // Change API key
        $component->set('settings.openai_api_key', 'new-key')
            ->call('updatedSettings')
            ->assertSet('connectionStatus.connected', false)
            ->assertSet('connectionStatus.message', 'Connection status unknown - test connection to verify');
    }

    /** @test */
    public function validates_api_key_format()
    {
        Livewire::test(Settings::class)
            ->set('settings.openai_api_key', 'invalid-format')
            ->call('saveSettings')
            ->assertHasErrors(['settings.openai_api_key']);
    }

    /** @test */
    public function shows_ai_features_section_when_enabled()
    {
        Livewire::test(Settings::class)
            ->set('settings.ai_enabled', true)
            ->assertSee('AI Auto-Fix')
            ->assertSee('Enable automatic code fixes');
    }

    /** @test */
    public function hides_ai_features_section_when_disabled()
    {
        Livewire::test(Settings::class)
            ->set('settings.ai_enabled', false)
            ->assertDontSee('AI Auto-Fix');
    }

    /** @test */
    public function can_export_settings()
    {
        // Set some settings
        Setting::set('ai_enabled', true);
        Setting::set('openai_api_key', 'sk-test-key');
        Setting::set('scan_auto_run', true);

        $response = Livewire::test(Settings::class)
            ->call('exportSettings');

        $this->assertNotNull($response);
    }

    /** @test */
    public function can_import_settings()
    {
        $settingsData = [
            'ai_enabled' => true,
            'ai_auto_fix_enabled' => true,
            'scan_auto_run' => false,
            'notification_enabled' => false
        ];

        Livewire::test(Settings::class)
            ->call('importSettings', $settingsData)
            ->assertSet('settings.ai_enabled', true)
            ->assertSet('settings.ai_auto_fix_enabled', true)
            ->assertSet('settings.scan_auto_run', false)
            ->assertSet('settings.notification_enabled', false)
            ->assertDispatched('notification');

        // Verify settings were saved to database
        $this->assertTrue(Setting::get('ai_enabled'));
        $this->assertTrue(Setting::get('ai_auto_fix_enabled'));
        $this->assertFalse(Setting::get('scan_auto_run'));
        $this->assertFalse(Setting::get('notification_enabled'));
    }

    /** @test */
    public function shows_warning_for_high_risk_settings()
    {
        Livewire::test(Settings::class)
            ->set('settings.ai_auto_fix_enabled', true)
            ->assertSee('Warning: Auto-fix can modify your code automatically');
    }

    /** @test */
    public function tracks_settings_changes()
    {
        $component = Livewire::test(Settings::class);
        
        // Initially no changes
        $this->assertFalse($component->get('hasUnsavedChanges'));
        
        // Make a change
        $component->set('settings.ai_enabled', true);
        $this->assertTrue($component->get('hasUnsavedChanges'));
        
        // Save changes
        $component->call('saveSettings');
        $this->assertFalse($component->get('hasUnsavedChanges'));
    }

    /** @test */
    public function shows_save_indicator_when_changes_pending()
    {
        Livewire::test(Settings::class)
            ->set('settings.ai_enabled', true)
            ->assertSee('Unsaved changes');
    }

    /** @test */
    public function can_cancel_unsaved_changes()
    {
        // Set initial setting
        Setting::set('ai_enabled', false);
        
        Livewire::test(Settings::class)
            ->set('settings.ai_enabled', true) // Make a change
            ->call('cancelChanges')
            ->assertSet('settings.ai_enabled', false) // Should revert
            ->assertSet('hasUnsavedChanges', false)
            ->assertDispatched('notification');
    }

    /** @test */
    public function validates_required_fields()
    {
        Livewire::test(Settings::class)
            ->set('settings.ai_enabled', true)
            ->set('settings.openai_api_key', '') // Required when AI enabled
            ->call('saveSettings')
            ->assertHasErrors(['settings.openai_api_key']);
    }

    /** @test */
    public function shows_connection_status_indicators()
    {
        $component = Livewire::test(Settings::class);
        
        // Test connected status
        $component->set('connectionStatus', [
            'connected' => true,
            'message' => 'Connected!'
        ]);
        $component->assertSee('Connected');
        
        // Test disconnected status
        $component->set('connectionStatus', [
            'connected' => false,
            'message' => 'Not connected'
        ]);
        $component->assertSee('Not connected');
    }

    private function getConnectionMessage()
    {
        $component = Livewire::test(Settings::class);
        return $component->get('connectionStatus.message');
    }
}