<?php

namespace Rafaelogic\CodeSnoutr\Livewire;

use Livewire\Component;
use Rafaelogic\CodeSnoutr\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;

class Settings extends Component
{
    public $settings = [];
    public $activeTab = 'general';
    public $testingConnection = false;
    public $connectionStatus = null;
    public $unsavedChanges = false;
    
    protected $tabs = [
        'general' => 'General',
        'scanning' => 'Scanning',
        'ai' => 'AI Integration',
        'ui' => 'Interface',
        'debugbar' => 'Debug Bar',
        'reports' => 'Reports',
        'advanced' => 'Advanced',
    ];

    protected $listeners = [
        'setting-updated' => 'refreshSettings',
        'test-connection' => 'testAiConnection',
    ];

    public function updatedSettings($value, $key)
    {
        $this->unsavedChanges = true;
    }

    public function mount($initialTab = 'general')
    {
        $this->activeTab = $initialTab;
        $this->loadSettings();
    }

    public function render()
    {
        return view('codesnoutr::livewire.settings', [
            'tabs' => $this->tabs,
            'settingGroups' => $this->getSettingGroups(),
        ]);
    }

    protected function loadSettings()
    {
        $defaultSettings = config('codesnoutr', []);
        $dbSettings = Setting::pluck('value', 'key')->toArray();
        
        $this->settings = array_merge($defaultSettings, $dbSettings);
    }

    protected function getSettingGroups()
    {
        return [
            'general' => [
                'timezone' => [
                    'label' => 'Timezone',
                    'type' => 'select',
                    'options' => $this->getTimezoneOptions(),
                    'default' => 'UTC',
                    'description' => 'Default timezone for scan timestamps',
                ],
                'max_concurrent_scans' => [
                    'label' => 'Max Concurrent Scans',
                    'type' => 'number',
                    'min' => 1,
                    'max' => 10,
                    'default' => 3,
                    'description' => 'Maximum number of scans that can run simultaneously',
                ],
            ],
            'scanning' => [
                'scan_timeout' => [
                    'label' => 'Scan Timeout (seconds)',
                    'type' => 'number',
                    'min' => 30,
                    'max' => 3600,
                    'default' => 300,
                    'description' => 'Maximum time allowed for a single scan',
                ],
                'max_file_size' => [
                    'label' => 'Max File Size (MB)',
                    'type' => 'number',
                    'min' => 1,
                    'max' => 100,
                    'default' => 10,
                    'description' => 'Maximum file size to scan',
                ],
                'ignore_patterns' => [
                    'label' => 'Ignore Patterns',
                    'type' => 'textarea',
                    'default' => "vendor/\nnode_modules/\nstorage/\n.git/\n*.min.js\n*.min.css",
                    'description' => 'File patterns to ignore during scanning (one per line)',
                ],
                'file_extensions' => [
                    'label' => 'File Extensions',
                    'type' => 'text',
                    'default' => 'php',
                    'description' => 'Comma-separated list of file extensions to scan',
                ],
                'default_rules' => [
                    'label' => 'Default Rule Categories',
                    'type' => 'checkboxes',
                    'options' => [
                        'security' => 'Security',
                        'performance' => 'Performance',
                        'quality' => 'Code Quality',
                        'laravel' => 'Laravel Best Practices',
                    ],
                    'default' => ['security', 'performance', 'quality', 'laravel'],
                    'description' => 'Rule categories enabled by default for new scans',
                ],
            ],
            'ai' => [
                'ai_enabled' => [
                    'label' => 'Enable AI Integration',
                    'type' => 'boolean',
                    'default' => false,
                    'description' => 'Enable AI-powered fix suggestions and bulk fix operations',
                ],
                'openai_api_key' => [
                    'label' => 'OpenAI API Key',
                    'type' => 'text',
                    'default' => '',
                    'description' => 'Your OpenAI API key for AI-powered features (stored in plain text for developer access)',
                ],
                'openai_model' => [
                    'label' => 'OpenAI Model',
                    'type' => 'select',
                    'options' => [
                        'gpt-3.5-turbo' => 'GPT-3.5 Turbo',
                        'gpt-4' => 'GPT-4',
                        'gpt-4-turbo' => 'GPT-4 Turbo',
                    ],
                    'default' => 'gpt-3.5-turbo',
                    'description' => 'OpenAI model to use for AI features',
                ],
                'max_tokens' => [
                    'label' => 'Max Tokens',
                    'type' => 'number',
                    'min' => 100,
                    'max' => 4000,
                    'default' => 1000,
                    'description' => 'Maximum tokens per AI request',
                ],

            ],
            'ui' => [
                'dark_mode' => [
                    'label' => 'Dark Mode',
                    'type' => 'boolean',
                    'default' => false,
                    'description' => 'Enable dark mode by default',
                ],
                'items_per_page' => [
                    'label' => 'Items Per Page',
                    'type' => 'select',
                    'options' => [
                        '10' => '10',
                        '15' => '15',
                        '25' => '25',
                        '50' => '50',
                        '100' => '100',
                    ],
                    'default' => '15',
                    'description' => 'Number of items to display per page',
                ],
                'show_line_numbers' => [
                    'label' => 'Show Line Numbers',
                    'type' => 'boolean',
                    'default' => true,
                    'description' => 'Show line numbers in code previews',
                ],
                'syntax_highlighting' => [
                    'label' => 'Syntax Highlighting',
                    'type' => 'boolean',
                    'default' => true,
                    'description' => 'Enable syntax highlighting in code previews',
                ],
            ],
            'debugbar' => [
                'debugbar_enabled' => [
                    'label' => 'Enable Debug Bar Integration',
                    'type' => 'boolean',
                    'default' => false,
                    'description' => 'Show CodeSnoutr information in Laravel Debugbar',
                ],
                'show_scan_count' => [
                    'label' => 'Show Scan Count',
                    'type' => 'boolean',
                    'default' => true,
                    'description' => 'Display scan count in debug bar',
                ],
                'show_issue_count' => [
                    'label' => 'Show Issue Count',
                    'type' => 'boolean',
                    'default' => true,
                    'description' => 'Display issue count in debug bar',
                ],
                'show_last_scan' => [
                    'label' => 'Show Last Scan',
                    'type' => 'boolean',
                    'default' => true,
                    'description' => 'Display last scan information in debug bar',
                ],
            ],
            'reports' => [
                'report_retention_days' => [
                    'label' => 'Report Retention (days)',
                    'type' => 'number',
                    'min' => 1,
                    'max' => 365,
                    'default' => 30,
                    'description' => 'Number of days to keep scan reports',
                ],
                'auto_export' => [
                    'label' => 'Auto Export Reports',
                    'type' => 'boolean',
                    'default' => false,
                    'description' => 'Automatically export reports after each scan',
                ],
                'export_format' => [
                    'label' => 'Default Export Format',
                    'type' => 'select',
                    'options' => [
                        'json' => 'JSON',
                        'csv' => 'CSV',
                        'pdf' => 'PDF',
                    ],
                    'default' => 'json',
                    'description' => 'Default format for exported reports',
                ],
                'include_resolved' => [
                    'label' => 'Include Resolved Issues',
                    'type' => 'boolean',
                    'default' => false,
                    'description' => 'Include resolved issues in exported reports',
                ],
            ],
            'advanced' => [
                'cache_enabled' => [
                    'label' => 'Enable Caching',
                    'type' => 'boolean',
                    'default' => true,
                    'description' => 'Cache scan results for improved performance',
                ],
                'cache_duration' => [
                    'label' => 'Cache Duration (minutes)',
                    'type' => 'number',
                    'min' => 5,
                    'max' => 1440,
                    'default' => 60,
                    'description' => 'How long to cache scan results',
                ],
                'log_level' => [
                    'label' => 'Log Level',
                    'type' => 'select',
                    'options' => [
                        'debug' => 'Debug',
                        'info' => 'Info',
                        'warning' => 'Warning',
                        'error' => 'Error',
                    ],
                    'default' => 'info',
                    'description' => 'Minimum log level for CodeSnoutr logs',
                ],
                'memory_limit' => [
                    'label' => 'Memory Limit (MB)',
                    'type' => 'number',
                    'min' => 128,
                    'max' => 2048,
                    'default' => 512,
                    'description' => 'Memory limit for scan processes',
                ],
            ],
        ];
    }

    protected function getTimezoneOptions()
    {
        return [
            'UTC' => 'UTC',
            'America/New_York' => 'America/New_York',
            'America/Chicago' => 'America/Chicago',
            'America/Denver' => 'America/Denver',
            'America/Los_Angeles' => 'America/Los_Angeles',
            'Europe/London' => 'Europe/London',
            'Europe/Paris' => 'Europe/Paris',
            'Europe/Berlin' => 'Europe/Berlin',
            'Asia/Tokyo' => 'Asia/Tokyo',
            'Asia/Shanghai' => 'Asia/Shanghai',
            'Australia/Sydney' => 'Australia/Sydney',
        ];
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function updateSetting($key, $value)
    {
        $this->settings[$key] = $value;
        $this->unsavedChanges = true;
    }

    public function saveSetting($key)
    {
        try {
            $value = $this->settings[$key] ?? null;
            
            if ($value === null || $value === '') {
                session()->flash('warning', "Setting '{$key}' is empty or null. Please provide a value.");
                return;
            }
            
            // Handle special cases for AI settings
            if ($key === 'openai_api_key') {
                Setting::set($key, $value, 'ai', false); // Not encrypted - visible to developers
            } elseif (str_starts_with($key, 'ai_')) {
                Setting::set($key, $value, 'ai', false); // Not encrypted
            } else {
                // Determine type based on setting configuration
                $type = $this->getSettingType($key);
                Setting::set($key, $value, $type, false);
            }

            $this->dispatch('setting-saved', key: $key, value: $value);
            
            // Dispatch specific AI settings update event for AI-related settings
            if ($key === 'openai_api_key' || str_starts_with($key, 'ai_')) {
                $this->dispatch('ai-settings-updated');
            }
            
            $this->clearCache();
            
            session()->flash('success', "Setting '{$key}' saved successfully!");
            
        } catch (\Exception $e) {
            session()->flash('error', "Failed to save setting '{$key}': " . $e->getMessage());
        }
    }

    public function saveSettings()
    {
        try {
            $aiSettingsUpdated = false;
            
            foreach ($this->settings as $key => $value) {
                // Handle special cases for AI settings
                if ($key === 'openai_api_key') {
                    Setting::set($key, $value, 'ai', false); // Not encrypted - visible to developers
                    $aiSettingsUpdated = true;
                } elseif (str_starts_with($key, 'ai_')) {
                    Setting::set($key, $value, 'ai', false); // Not encrypted
                    $aiSettingsUpdated = true;
                } else {
                    // Determine type based on setting configuration
                    $type = $this->getSettingType($key);
                    Setting::set($key, $value, $type, false);
                }
            }

            $this->unsavedChanges = false;
            $this->dispatch('settings-saved');
            
            // Dispatch specific AI settings update event
            if ($aiSettingsUpdated) {
                $this->dispatch('ai-settings-updated');
            }
            
            $this->clearCache();
            
            session()->flash('success', 'Settings saved successfully!');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to save settings: ' . $e->getMessage());
        }
    }

    public function saveAllSettings()
    {
        return $this->saveSettings();
    }
    
    // Debug method to check current settings
    public function debugSettings()
    {
        try {
            // Get current settings from component state
            $componentSettings = $this->settings;
            
            // Get settings from database
            $dbSettings = [
                'ai_enabled' => Setting::get('ai_enabled'),
                'openai_api_key' => Setting::get('openai_api_key'),
                'openai_model' => Setting::get('openai_model'),
            ];
            
            $debugInfo = [
                'component_count' => count($componentSettings),
                'db_ai_enabled' => $dbSettings['ai_enabled'],
                'db_api_key_length' => strlen($dbSettings['openai_api_key'] ?? ''),
                'db_model' => $dbSettings['openai_model'],
                'component_ai_enabled' => $componentSettings['ai_enabled'] ?? 'not set',
                'component_api_key_length' => strlen($componentSettings['openai_api_key'] ?? ''),
                'component_model' => $componentSettings['openai_model'] ?? 'not set',
            ];
            
            session()->flash('info', 'Debug info: ' . json_encode($debugInfo, JSON_PRETTY_PRINT));
            
        } catch (\Exception $e) {
            session()->flash('error', 'Debug failed: ' . $e->getMessage());
        }
    }

    protected function getSettingType($key)
    {
        // Map setting keys to their group types
        foreach ($this->getSettingGroups() as $groupKey => $group) {
            if (isset($group[$key])) {
                return $groupKey;
            }
        }
        
        return 'general'; // Default type
    }

    public function resetSettings()
    {
        try {
            // Get default values from the setting groups configuration
            $settingGroups = $this->getSettingGroups();
            
            foreach ($settingGroups as $groupKey => $group) {
                foreach ($group as $settingKey => $config) {
                    $this->settings[$settingKey] = $config['default'] ?? null;
                }
            }

            $this->unsavedChanges = true;
            
            session()->flash('info', 'Settings have been reset to default values. Click "Save Settings" to apply changes.');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to reset settings: ' . $e->getMessage());
        }
    }

    public function resetToDefaults($group = null)
    {
        $defaultSettings = config('codesnoutr', []);
        
        if ($group) {
            $groupSettings = $this->getSettingGroups()[$group] ?? [];
            foreach ($groupSettings as $key => $config) {
                $this->settings[$key] = $config['default'] ?? null;
            }
        } else {
            $this->settings = $defaultSettings;
        }

        $this->unsavedChanges = true;
    }

    public function testAiConnection()
    {
        $aiEnabled = $this->settings['ai_enabled'] ?? false;
        $apiKey = $this->settings['openai_api_key'] ?? '';
        
        // Check if AI is enabled and API key is provided from current component settings
        if (!$aiEnabled) {
            $this->connectionStatus = [
                'success' => false,
                'message' => 'AI integration is disabled. Please enable it first.'
            ];
            return;
        }

        if (empty($apiKey)) {
            $this->connectionStatus = [
                'success' => false,
                'message' => 'OpenAI API key is required. Please provide your API key.'
            ];
            return;
        }

        $this->testingConnection = true;
        $this->connectionStatus = null;

        try {
            $model = $this->settings['openai_model'] ?? 'gpt-3.5-turbo';
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(30)->post('https://api.openai.com/v1/chat/completions', [
                'model' => $model,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => 'Hello! This is a test connection for CodeSnoutr. Please respond with "Connection successful!"'
                    ]
                ],
                'max_tokens' => 50,
                'temperature' => 0.1,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $content = $data['choices'][0]['message']['content'] ?? '';
                
                $this->connectionStatus = [
                    'success' => true,
                    'message' => 'AI connection is working perfectly!',
                    'details' => 'Model: ' . $model . ', Response: ' . trim($content)
                ];
                
                session()->flash('success', 'AI connection test successful!');
                
                // Dispatch event to refresh AI components
                $this->dispatch('ai-settings-updated');
            } else {
                $errorData = $response->json();
                $errorMessage = $errorData['error']['message'] ?? 'Unknown API error';
                $statusCode = $response->status();
                
                $this->connectionStatus = [
                    'success' => false,
                    'message' => "API Error ({$statusCode}): " . $errorMessage
                ];
                
                session()->flash('error', "OpenAI API Error ({$statusCode}): " . $errorMessage);
            }

        } catch (\Exception $e) {
            $this->connectionStatus = [
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage()
            ];
            
            session()->flash('error', 'Connection failed: ' . $e->getMessage());
        } finally {
            $this->testingConnection = false;
        }
    }

    public function clearCache()
    {
        Cache::tags(['codesnoutr'])->flush();
        $this->dispatch('cache-cleared');
    }

    public function exportSettings()
    {
        $export = [
            'exported_at' => now()->toISOString(),
            'version' => '1.0',
            'settings' => $this->settings,
        ];

        $filename = 'codesnoutr-settings-' . now()->format('Y-m-d-H-i-s') . '.json';
        
        $this->dispatch('download-file', [
            'content' => json_encode($export, JSON_PRETTY_PRINT),
            'filename' => $filename,
            'contentType' => 'application/json'
        ]);
    }

    public function importSettings($importData)
    {
        try {
            $data = json_decode($importData, true);
            
            if (!isset($data['settings'])) {
                throw new \Exception('Invalid settings file format');
            }

            $this->settings = array_merge($this->settings, $data['settings']);
            $this->unsavedChanges = true;
            
            $this->dispatch('settings-imported');

        } catch (\Exception $e) {
            $this->dispatch('import-error', message: $e->getMessage());
        }
    }

    public function runMaintenance()
    {
        try {
            // Clear old scans
            Artisan::call('codesnoutr:cleanup');
            
            // Clear cache
            $this->clearCache();
            
            // Optimize database
            Artisan::call('optimize');
            
            $this->dispatch('maintenance-completed');

        } catch (\Exception $e) {
            $this->dispatch('maintenance-error', message: $e->getMessage());
        }
    }

    public function refreshSettings()
    {
        $this->loadSettings();
        $this->unsavedChanges = false;
    }

    public function getSetting($key, $default = null)
    {
        return $this->settings[$key] ?? $default;
    }

    public function getTabIcon($tab)
    {
        return match($tab) {
            'general' => 'cog',
            'scanning' => 'search',
            'ai' => 'lightning-bolt',
            'ui' => 'color-swatch',
            'debugbar' => 'bug-ant',
            'reports' => 'document-report',
            'advanced' => 'adjustments',
            default => 'cog'
        };
    }

    public function getSettingIcon($key)
    {
        return match($key) {
            'ai_enabled', 'ai_auto_fix' => 'lightning-bolt',
            'dark_mode' => 'moon',
            'cache_enabled' => 'server',
            'debugbar_enabled' => 'bug-ant',
            'auto_scan_enabled' => 'play',
            'syntax_highlighting' => 'code',
            default => 'cog'
        };
    }
}
