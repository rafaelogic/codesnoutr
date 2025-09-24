<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="md:flex md:items-center md:justify-between mb-8">
            <div class="min-w-0 flex-1">
                <h1 class="text-2xl font-bold leading-7 text-gray-900 dark:text-white sm:truncate">
                    CodeSnoutr Settings
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Configure your CodeSnoutr installation
                </p>
            </div>
        </div>

        <!-- Loading indicator -->
        <div wire:loading class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-xl">
                <div class="flex items-center space-x-4">
                    <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-lg font-medium text-gray-900 dark:text-white">Saving settings...</span>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="divide-y divide-gray-200 dark:divide-gray-700 lg:grid lg:grid-cols-12 lg:divide-y-0 lg:divide-x">
                <!-- Sidebar Navigation -->
                <aside class="py-6 lg:col-span-3">
                    <nav class="space-y-1">
                        @foreach($tabs as $tabKey => $tabLabel)
                        <button 
                            wire:click="setActiveTab('{{ $tabKey }}')"
                            class="w-full text-left px-3 py-2 rounded-md text-sm font-medium transition-colors duration-150 
                                @if($activeTab === $tabKey)
                                    bg-indigo-50 dark:bg-indigo-900 border-r-2 border-indigo-500 text-indigo-700 dark:text-indigo-300
                                @else
                                    text-gray-900 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-gray-700
                                @endif
                            ">
                            {{ $tabLabel }}
                        </button>
                        @endforeach
                    </nav>
                </aside>

                <!-- Main content -->
                <div class="divide-y divide-gray-200 dark:divide-gray-700 lg:col-span-9">
                    <div class="py-6 px-4 sm:p-6 lg:pb-8">
                        @if($unsavedChanges)
                        <div class="mb-6 bg-yellow-50 dark:bg-yellow-900 border border-yellow-200 dark:border-yellow-700 rounded-md p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-800 dark:text-yellow-200">
                                        You have unsaved changes. Don't forget to save your settings.
                                    </p>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Dynamic Settings Content -->
                        @if(isset($settingGroups[$activeTab]))
                        <div class="space-y-6">
                            <div>
                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                                    {{ $tabs[$activeTab] }} Settings
                                </h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Configure {{ strtolower($tabs[$activeTab]) }} options for CodeSnoutr.
                                </p>
                            </div>

                            <div class="grid grid-cols-1 gap-6">
                                @foreach($settingGroups[$activeTab] as $settingKey => $setting)
                                <div class="space-y-2">
                                    <label for="{{ $settingKey }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        {{ $setting['label'] }}
                                        @if(isset($setting['description']))
                                        <span class="text-xs text-gray-500 dark:text-gray-400 block font-normal mt-1">
                                            {{ $setting['description'] }}
                                        </span>
                                        @endif
                                    </label>

                                    @if($setting['type'] === 'text' || $setting['type'] === 'email')
                                    <input 
                                        wire:model.debounce.500ms="settings.{{ $settingKey }}"
                                        type="{{ $setting['type'] === 'email' ? 'email' : 'text' }}"
                                        id="{{ $settingKey }}"
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white sm:text-sm"
                                        placeholder="{{ $setting['placeholder'] ?? '' }}"
                                    />

                                    @elseif($setting['type'] === 'password')
                                    <input 
                                        wire:model.debounce.500ms="settings.{{ $settingKey }}"
                                        type="password"
                                        id="{{ $settingKey }}"
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white sm:text-sm"
                                    />

                                    @elseif($setting['type'] === 'number')
                                    <input 
                                        wire:model.debounce.500ms="settings.{{ $settingKey }}"
                                        type="number"
                                        id="{{ $settingKey }}"
                                        min="{{ $setting['min'] ?? '' }}"
                                        max="{{ $setting['max'] ?? '' }}"
                                        step="{{ $setting['step'] ?? '1' }}"
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white sm:text-sm"
                                    />

                                    @elseif($setting['type'] === 'boolean')
                                    <div class="flex items-center mt-1">
                                        <input 
                                            wire:model="settings.{{ $settingKey }}"
                                            id="{{ $settingKey }}"
                                            type="checkbox"
                                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-600 rounded dark:bg-gray-700"
                                        />
                                        <label for="{{ $settingKey }}" class="ml-3 text-sm text-gray-700 dark:text-gray-300">
                                            Enable {{ strtolower($setting['label']) }}
                                        </label>
                                    </div>

                                    @elseif($setting['type'] === 'select')
                                    <select 
                                        wire:model="settings.{{ $settingKey }}"
                                        id="{{ $settingKey }}"
                                        class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white sm:text-sm rounded-md"
                                    >
                                        @if(isset($setting['options']))
                                        @foreach($setting['options'] as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                        @endif
                                    </select>

                                    @elseif($setting['type'] === 'textarea')
                                    <textarea 
                                        wire:model.debounce.500ms="settings.{{ $settingKey }}"
                                        id="{{ $settingKey }}"
                                        rows="{{ $setting['rows'] ?? 3 }}"
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white sm:text-sm"
                                        placeholder="{{ $setting['placeholder'] ?? '' }}"
                                    ></textarea>

                                    @elseif($setting['type'] === 'checkboxes')
                                    <div class="mt-2 space-y-2">
                                        @if(isset($setting['options']))
                                        @foreach($setting['options'] as $value => $label)
                                        <div class="flex items-center">
                                            <input 
                                                wire:model="settings.{{ $settingKey }}"
                                                id="{{ $settingKey }}_{{ $value }}"
                                                type="checkbox"
                                                value="{{ $value }}"
                                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-600 rounded dark:bg-gray-700"
                                            />
                                            <label for="{{ $settingKey }}_{{ $value }}" class="ml-3 text-sm text-gray-700 dark:text-gray-300">
                                                {{ $label }}
                                            </label>
                                        </div>
                                        @endforeach
                                        @endif
                                    </div>
                                    @endif

                                    @error('settings.'.$settingKey)
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>
                                @endforeach
                            </div>

                            <!-- Special Actions for AI Tab -->
                            @if($activeTab === 'ai' && isset($settings['ai_enabled']) && $settings['ai_enabled'])
                            <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-900 dark:text-white">Test Connection</h4>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Verify your AI integration is working correctly</p>
                                    </div>
                                    <button 
                                        wire:click="testAiConnection" 
                                        wire:loading.attr="disabled"
                                        wire:target="testAiConnection"
                                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                                    >
                                        <span wire:loading.remove wire:target="testAiConnection">Test Connection</span>
                                        <span wire:loading wire:target="testAiConnection" class="flex items-center">
                                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            Testing...
                                        </span>
                                    </button>
                                </div>
                                
                                @if($connectionStatus)
                                <div class="mt-4 p-4 rounded-md @if($connectionStatus['success']) bg-green-50 dark:bg-green-900 @else bg-red-50 dark:bg-red-900 @endif">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            @if($connectionStatus['success'])
                                            <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                            @else
                                            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                            </svg>
                                            @endif
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm @if($connectionStatus['success']) text-green-800 dark:text-green-200 @else text-red-800 dark:text-red-200 @endif">
                                                {{ $connectionStatus['message'] }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                            @endif
                        </div>
                        @endif
                    </div>

                    <!-- Form Actions -->
                    <div class="px-4 py-3 bg-gray-50 dark:bg-gray-800 text-right sm:px-6">
                        <div class="flex justify-between items-center">
                            <button 
                                wire:click="resetSettings"
                                type="button"
                                class="bg-white dark:bg-gray-700 py-2 px-4 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                Reset to Defaults
                            </button>
                            
                            <div class="space-x-3">
                                <button 
                                    wire:click="saveSettings"
                                    type="button"
                                    class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                >
                                    Save Settings
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>