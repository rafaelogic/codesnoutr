<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Settings</h1>
        <p class="mt-2 text-gray-600 dark:text-gray-400">Configure CodeSnoutr to match your preferences and requirements.</p>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="mb-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
            <div class="flex items-center">
                <svg class="h-5 w-5 text-green-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span class="text-sm font-medium text-green-800 dark:text-green-200">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-6 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
            <div class="flex items-center">
                <svg class="h-5 w-5 text-red-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L10 11.414l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <span class="text-sm font-medium text-red-800 dark:text-red-200">{{ session('error') }}</span>
            </div>
        </div>
    @endif

    @if (session()->has('warning'))
        <div class="mb-6 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
            <div class="flex items-center">
                <svg class="h-5 w-5 text-yellow-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <span class="text-sm font-medium text-yellow-800 dark:text-yellow-200">{{ session('warning') }}</span>
            </div>
        </div>
    @endif

    @if (session()->has('info'))
        <div class="mb-6 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
            <div class="flex items-center">
                <svg class="h-5 w-5 text-blue-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                <div class="text-sm font-medium text-blue-800 dark:text-blue-200 whitespace-pre-line">{{ session('info') }}</div>
            </div>
        </div>
    @endif

    <!-- Unsaved Changes Banner -->
    @if($unsavedChanges)
    <div class="mb-6 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <svg class="h-5 w-5 text-yellow-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <span class="text-sm font-medium text-yellow-800 dark:text-yellow-200">You have unsaved changes</span>
            </div>
            <div class="flex space-x-2">
                <button wire:click="saveAllSettings" class="btn btn-primary">
                    Save All Changes
                </button>
                <button wire:click="refreshSettings" class="btn btn-secondary">
                    Discard Changes
                </button>
            </div>
        </div>
    </div>
    @endif

    <!-- Settings Content -->
    <div class="card p-6">
        @foreach($settingGroups as $groupKey => $group)
            @if($activeTab === $groupKey)
                <div class="space-y-6">
                    <!-- Group Header -->
                    <div class="pb-4 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $tabs[$groupKey] }}</h2>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            @switch($groupKey)
                                @case('general')
                                    Configure basic application settings and preferences.
                                    @break
                                @case('scanning')
                                    Adjust scan behavior, timeouts, and file filtering options.
                                    @break
                                @case('ai')
                                    Set up AI integration for enhanced code analysis and suggestions.
                                    @break
                                @case('ui')
                                    Customize the user interface appearance and behavior.
                                    @break
                                @case('debugbar')
                                    Configure Laravel Debugbar integration settings.
                                    @break
                                @case('reports')
                                    Manage report generation and export preferences.
                                    @break
                                @case('advanced')
                                    Advanced configuration options for power users.
                                    @break
                            @endswitch
                        </p>
                    </div>

                    <!-- Settings -->
                    @foreach($group as $settingKey => $config)
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 items-start">
                            <!-- Setting Label & Description -->
                            <div class="lg:col-span-1">
                                <label class="block text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $config['label'] }}
                                </label>
                                @if(isset($config['description']))
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        {{ $config['description'] }}
                                    </p>
                                @endif
                            </div>

                            <!-- Setting Input -->
                            <div class="lg:col-span-2">
                                @switch($config['type'])
                                    @case('text')
                                    @case('password')
                                        <input type="{{ $config['type'] }}" 
                                               wire:model.blur="settings.{{ $settingKey }}"
                                               wire:change="updateSetting('{{ $settingKey }}', $event.target.value)"
                                               class="form-input w-full"
                                               placeholder="{{ $config['default'] ?? '' }}">
                                        @break

                                    @case('number')
                                        <input type="number" 
                                               wire:model.blur="settings.{{ $settingKey }}"
                                               wire:change="updateSetting('{{ $settingKey }}', $event.target.value)"
                                               class="form-input w-full"
                                               min="{{ $config['min'] ?? '' }}"
                                               max="{{ $config['max'] ?? '' }}"
                                               placeholder="{{ $config['default'] ?? '' }}">
                                        @break

                                    @case('textarea')
                                        <textarea wire:model.blur="settings.{{ $settingKey }}"
                                                  wire:change="updateSetting('{{ $settingKey }}', $event.target.value)"
                                                  class="form-input w-full"
                                                  rows="4"
                                                  placeholder="{{ $config['default'] ?? '' }}"></textarea>
                                        @break

                                    @case('select')
                                        <select wire:model="settings.{{ $settingKey }}"
                                                wire:change="updateSetting('{{ $settingKey }}', $event.target.value)"
                                                class="form-input w-full">
                                            @foreach($config['options'] as $value => $label)
                                                <option value="{{ $value }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        @break

                                    @case('boolean')
                                        <div class="flex items-center">
                                            <input type="checkbox" 
                                                   wire:model="settings.{{ $settingKey }}"
                                                   wire:change="updateSetting('{{ $settingKey }}', $event.target.checked)"
                                                   class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600">
                                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                                Enable {{ strtolower($config['label']) }}
                                            </span>
                                        </div>
                                        @break

                                    @case('checkboxes')
                                        <div class="space-y-2">
                                            @foreach($config['options'] as $value => $label)
                                                <div class="flex items-center">
                                                    <input type="checkbox" 
                                                           wire:model="settings.{{ $settingKey }}"
                                                           value="{{ $value }}"
                                                           wire:change="updateSetting('{{ $settingKey }}', $event.target.value)"
                                                           class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600">
                                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                                        {{ $label }}
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>
                                        @break
                                @endswitch

                                <!-- Individual Save Button -->
                                <div class="mt-2">
                                    <button wire:click="saveSetting('{{ $settingKey }}')" 
                                            class="text-xs text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 font-medium">
                                        Save
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <!-- Group Actions -->
                    <div class="pt-6 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex flex-wrap gap-3">
                            <button wire:click="resetToDefaults('{{ $groupKey }}')" 
                                    class="btn btn-secondary">
                                Reset to Defaults
                            </button>

                            @if($groupKey === 'ai')
                                <button wire:click="testAiConnection" 
                                        class="btn {{ ($settings['ai_enabled'] ?? false) && !empty($settings['openai_api_key']) ? 'btn-primary' : 'btn-secondary opacity-50' }}"
                                        wire:loading.attr="disabled"
                                        @if(!($settings['ai_enabled'] ?? false) || empty($settings['openai_api_key'])) disabled @endif>
                                    <span wire:loading.remove wire:target="testAiConnection">
                                        @if(!($settings['ai_enabled'] ?? false))
                                            AI Disabled
                                        @elseif(empty($settings['openai_api_key']))
                                            API Key Required
                                        @else
                                            Test Connection
                                        @endif
                                    </span>
                                    <span wire:loading wire:target="testAiConnection">Testing...</span>
                                </button>

                                @if($connectionStatus)
                                    <div class="flex items-center ml-3">
                                        @if($connectionStatus->success)
                                            <svg class="h-5 w-5 text-green-500 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                        @else
                                            <svg class="h-5 w-5 text-red-500 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L10 11.414l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                            </svg>
                                        @endif
                                        <span class="text-sm {{ $connectionStatus->success ? 'text-green-700 dark:text-green-300' : 'text-red-700 dark:text-red-300' }}">
                                            {{ $connectionStatus->message }}
                                        </span>
                                    </div>
                                @endif
                            @endif

                            @if($groupKey === 'advanced')
                                <button wire:click="clearCache" 
                                        class="btn btn-secondary">
                                    Clear Cache
                                </button>
                                <button wire:click="runMaintenance" 
                                        class="btn btn-secondary">
                                    Run Maintenance
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    </div>

    <!-- Global Actions -->
    <div class="card p-6 mt-6">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Import/Export Settings</h3>
        <div class="flex flex-wrap gap-3">
            <button wire:click="exportSettings" class="btn btn-primary">
                Export Settings
            </button>
            
            <div class="relative">
                <input type="file" 
                       accept=".json"
                       class="hidden" 
                       id="import-settings"
                       wire:change="importSettings($event.target.files[0])">
                <button onclick="document.getElementById('import-settings').click()" 
                        class="btn btn-secondary">
                    Import Settings
                </button>
            </div>
            
            <button wire:click="saveAllSettings" 
                    class="btn btn-primary {{ $unsavedChanges ? '' : 'opacity-50' }}"
                    @if(!$unsavedChanges) disabled @endif>
                Save All Settings
            </button>
        </div>
    </div>
</div>
