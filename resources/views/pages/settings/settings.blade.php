<x-atoms.container size="7xl" class="py-12">
    <x-atoms.stack size="2xl">
        <x-molecules.page-header 
            title="Settings"
            description="Configure CodeSnoutr to match your preferences and requirements."
            class="mb-12"
        />

        <!-- Flash Messages -->
        @if (session()->has('success'))
            <x-atoms.alert 
                variant="success"
                icon="check-circle"
                class="mb-8"
            >
                {{ session('success') }}
            </x-atoms.alert>
        @endif

        @if (session()->has('error'))
            <x-atoms.alert 
                variant="danger"
                icon="x-circle"
                class="mb-8"
            >
                {{ session('error') }}
            </x-atoms.alert>
        @endif

        @if (session()->has('warning'))
            <x-atoms.alert 
                variant="warning"
                icon="exclamation-triangle"
                class="mb-8"
            >
                {{ session('warning') }}
            </x-atoms.alert>
        @endif

        @if (session()->has('info'))
            <x-atoms.alert 
                variant="info"
                icon="information-circle"
                class="mb-8"
            >
                <div class="whitespace-pre-line">{{ session('info') }}</div>
            </x-atoms.alert>
        @endif

        <!-- Unsaved Changes Banner -->
        @if($unsavedChanges)
            <x-atoms.surface 
                variant="bordered" 
                class="p-6 mb-8 bg-yellow-50 dark:bg-yellow-900/20 border-yellow-200 dark:border-yellow-800"
            >
                <x-atoms.stack direction="horizontal" size="lg" class="justify-between items-center">
                    <x-atoms.stack direction="horizontal" size="md" class="items-center">
                        <x-atoms.icon name="exclamation-triangle" class="w-5 h-5 text-yellow-400" />
                        <x-atoms.text weight="medium" color="warning">You have unsaved changes</x-atoms.text>
                    </x-atoms.stack>
                    <x-atoms.stack direction="horizontal" size="sm">
                        <x-atoms.button wire:click="saveAllSettings" variant="primary">
                            Save All Changes
                        </x-atoms.button>
                        <x-atoms.button wire:click="refreshSettings" variant="secondary">
                            Discard Changes
                        </x-atoms.button>
                    </x-atoms.stack>
                </x-atoms.stack>
            </x-atoms.surface>
        @endif

        <!-- Settings Content -->
        <x-molecules.card variant="elevated" class="p-8 mb-8">
        @foreach($settingGroups as $groupKey => $group)
            @if($activeTab === $groupKey)
                <x-atoms.stack size="2xl">
                    <!-- Group Header -->
                    <x-atoms.stack size="sm" class="pb-6 border-b border-gray-200 dark:border-gray-700">
                        <x-atoms.text as="h2" size="2xl" weight="semibold">{{ $tabs[$groupKey] }}</x-atoms.text>
                        <x-atoms.text size="sm" color="muted">
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
                        </x-atoms.text>
                    </x-atoms.stack>

                    <!-- Settings -->
                    @foreach($group as $settingKey => $config)
                        <x-atoms.grid cols="1" lg-cols="3" gap="lg" class="items-start py-6">
                            <!-- Setting Label & Description -->
                            <x-atoms.stack size="xs" class="lg:col-span-1">
                                <x-atoms.text as="label" size="sm" weight="medium">
                                    {{ $config['label'] }}
                                </x-atoms.text>
                                @if(isset($config['description']))
                                    <x-atoms.text size="xs" color="muted">
                                        {{ $config['description'] }}
                                    </x-atoms.text>
                                @endif
                            </x-atoms.stack>

                            <!-- Setting Input -->
                            <x-atoms.stack size="sm" class="lg:col-span-2">
                                @switch($config['type'])
                                    @case('text')
                                    @case('password')
                                        <x-atoms.input 
                                            type="{{ $config['type'] }}"
                                            wire:model.blur="settings.{{ $settingKey }}"
                                            wire:change="updateSetting('{{ $settingKey }}', $event.target.value)"
                                            placeholder="{{ $config['default'] ?? '' }}"
                                            size="lg"
                                            class="w-full"
                                        />
                                        @break

                                    @case('number')
                                        <x-atoms.input 
                                            type="number"
                                            wire:model.blur="settings.{{ $settingKey }}"
                                            wire:change="updateSetting('{{ $settingKey }}', $event.target.value)"
                                            placeholder="{{ $config['default'] ?? '' }}"
                                            size="lg"
                                            class="w-full"
                                            min="{{ $config['min'] ?? '' }}"
                                            max="{{ $config['max'] ?? '' }}"
                                        />
                                        @break

                                    @case('textarea')
                                        <x-atoms.input 
                                            type="textarea"
                                            wire:model.blur="settings.{{ $settingKey }}"
                                            wire:change="updateSetting('{{ $settingKey }}', $event.target.value)"
                                            placeholder="{{ $config['default'] ?? '' }}"
                                            size="lg"
                                            class="w-full"
                                            rows="4"
                                        />
                                        @break

                                    @case('select')
                                        <x-atoms.input 
                                            type="select"
                                            wire:model="settings.{{ $settingKey }}"
                                            wire:change="updateSetting('{{ $settingKey }}', $event.target.value)"
                                            size="lg"
                                            class="w-full"
                                        >
                                            @foreach($config['options'] as $value => $label)
                                                <option value="{{ $value }}">{{ $label }}</option>
                                            @endforeach
                                        </x-atoms.input>
                                        @break

                                    @case('boolean')
                                        <x-atoms.stack direction="horizontal" size="sm" class="items-center">
                                            <x-atoms.input 
                                                type="checkbox"
                                                wire:model="settings.{{ $settingKey }}"
                                                wire:change="updateSetting('{{ $settingKey }}', $event.target.checked)"
                                                size="md"
                                            />
                                            <x-atoms.text size="sm">
                                                Enable {{ strtolower($config['label']) }}
                                            </x-atoms.text>
                                        </x-atoms.stack>
                                        @break

                                    @case('checkboxes')
                                        <x-atoms.stack size="sm">
                                            @foreach($config['options'] as $value => $label)
                                                <x-atoms.stack direction="horizontal" size="sm" class="items-center">
                                                    <x-atoms.input 
                                                        type="checkbox"
                                                        wire:model="settings.{{ $settingKey }}"
                                                        value="{{ $value }}"
                                                        wire:change="updateSetting('{{ $settingKey }}', $event.target.value)"
                                                        size="md"
                                                    />
                                                    <x-atoms.text size="sm">
                                                        {{ $label }}
                                                    </x-atoms.text>
                                                </x-atoms.stack>
                                            @endforeach
                                        </x-atoms.stack>
                                        @break
                                @endswitch

                                <!-- Individual Save Button -->
                                <x-atoms.button 
                                    wire:click="saveSetting('{{ $settingKey }}')"
                                    variant="ghost"
                                    size="xs"
                                    class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 font-medium"
                                >
                                    Save
                                </x-atoms.button>
                            </x-atoms.stack>
                        </x-atoms.grid>
                    @endforeach

                    <!-- Group Actions -->
                    <x-atoms.surface class="pt-8 border-t border-gray-200 dark:border-gray-700">
                        <x-atoms.stack direction="horizontal" size="md" class="flex-wrap">
                            <x-atoms.button 
                                wire:click="resetToDefaults('{{ $groupKey }}')"
                                variant="secondary"
                                size="md"
                            >
                                Reset to Defaults
                            </x-atoms.button>

                            @if($groupKey === 'ai')
                                <x-atoms.button 
                                    wire:click="testAiConnection"
                                    :variant="($settings['ai_enabled'] ?? false) && !empty($settings['openai_api_key']) ? 'primary' : 'secondary'"
                                    size="md"
                                    wire:loading.attr="disabled"
                                    :disabled="!($settings['ai_enabled'] ?? false) || empty($settings['openai_api_key'])"
                                    :class="!($settings['ai_enabled'] ?? false) || empty($settings['openai_api_key']) ? 'opacity-50' : ''"
                                >
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
                                </x-atoms.button>

                                @if($connectionStatus)
                                    <x-atoms.stack direction="horizontal" size="xs" class="items-center ml-3">
                                        <x-atoms.icon 
                                            :name="$connectionStatus->success ? 'check-circle' : 'x-circle'"
                                            :class="$connectionStatus->success ? 'text-green-500' : 'text-red-500'"
                                            size="sm"
                                        />
                                        <x-atoms.text 
                                            size="sm"
                                            :class="$connectionStatus->success ? 'text-green-700 dark:text-green-300' : 'text-red-700 dark:text-red-300'"
                                        >
                                            {{ $connectionStatus->message }}
                                        </x-atoms.text>
                                    </x-atoms.stack>
                                @endif
                            @endif

                            @if($groupKey === 'advanced')
                                <x-atoms.button 
                                    wire:click="clearCache"
                                    variant="secondary"
                                    size="md"
                                >
                                    Clear Cache
                                </x-atoms.button>
                                <x-atoms.button 
                                    wire:click="runMaintenance"
                                    variant="secondary"
                                    size="md"
                                >
                                    Run Maintenance
                                </x-atoms.button>
                            @endif
                        </x-atoms.stack>
                    </x-atoms.surface>
                </x-atoms.stack>
            @endif
        @endforeach
        </x-molecules.card>

        <!-- Global Actions -->
        <x-molecules.card variant="elevated" class="p-8">
            <x-atoms.stack size="lg">
                <x-atoms.text as="h3" size="lg" weight="medium">Import/Export Settings</x-atoms.text>
                <x-atoms.stack direction="horizontal" size="md" class="flex-wrap">
                    <x-atoms.button 
                        wire:click="exportSettings"
                        variant="primary"
                        size="md"
                        icon="download"
                    >
                        Export Settings
                    </x-atoms.button>
                    
                    <x-atoms.surface class="relative">
                        <input type="file" 
                               accept=".json"
                               class="hidden" 
                               id="import-settings"
                               wire:change="importSettings($event.target.files[0])">
                        <x-atoms.button 
                            onclick="document.getElementById('import-settings').click()"
                            variant="secondary"
                            size="md"
                            icon="upload"
                        >
                            Import Settings
                        </x-atoms.button>
                    </x-atoms.surface>
                    
                    <x-atoms.button 
                        wire:click="saveAllSettings"
                        variant="primary"
                        size="md"
                        icon="check"
                        :disabled="!$unsavedChanges"
                        :class="!$unsavedChanges ? 'opacity-50' : ''"
                    >
                        Save All Settings
                    </x-atoms.button>
                </x-atoms.stack>
            </x-atoms.stack>
        </x-molecules.card>
    </x-atoms.stack>
</x-atoms.container>
