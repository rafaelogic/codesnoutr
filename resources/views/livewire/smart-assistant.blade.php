<!-- Smart Assistant Component -->
<div class="fixed bottom-4 right-4 z-50" x-data="{ isMinimized: false }">
    @if($isOpen)
    <!-- Assistant Panel -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 w-96 h-[600px] flex flex-col transform transition-all duration-300"
         :class="{ 'h-12': isMinimized, 'h-[600px]': !isMinimized }"
         x-show="!isMinimized || true">
        
        <!-- Header -->
        <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-t-2xl flex-shrink-0">
            <div class="flex items-center space-x-3">
                <div class="h-10 w-10 bg-white/20 rounded-full flex items-center justify-center">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-white">AI Assistant</h3>
                    <p class="text-xs text-white/80">{{ $this->getContextName($currentContext) }}</p>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <button @click="isMinimized = !isMinimized" class="p-1 hover:bg-white/20 rounded transition-colors">
                    <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <button wire:click="closeAssistant" class="p-1 hover:bg-white/20 rounded transition-colors">
                    <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Content -->
        <div class="flex-1 flex flex-col min-h-0" x-show="!isMinimized" x-transition x-data="{ activeTab: 'chat' }">
            <!-- Context Selector -->
            <div class="p-3 border-b border-gray-200 dark:border-gray-700 flex-shrink-0">
                <select wire:model="currentContext" wire:change="setContext($event.target.value)" 
                        class="p-2 w-full text-sm border-gray-300 dark:border-gray-600 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white">
                    <option value="general">General Help</option>
                    <option value="scan_wizard">Scan Wizard</option>
                    <option value="dashboard">Dashboard</option>
                    <option value="results">Scan Results</option>
                    <option value="settings">Settings</option>
                </select>
            </div>

            @if($aiAvailable || !empty($chatHistory))
                <!-- Tabs -->
                <div class="flex border-b border-gray-200 dark:border-gray-700">
                    <button @click="activeTab = 'chat'" 
                            class="flex-1 py-3 px-4 text-sm font-medium border-b-2 transition-colors"
                            :class="activeTab === 'chat' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'">
                        <svg class="inline h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                        Chat
                    </button>
                    @if($aiAvailable)
                    <button @click="activeTab = 'suggestions'" 
                            class="flex-1 py-3 px-4 text-sm font-medium border-b-2 transition-colors"
                            :class="activeTab === 'suggestions' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'">
                        <svg class="inline h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                        </svg>
                        Ideas
                    </button>
                    <button @click="activeTab = 'tips'" 
                            class="flex-1 py-3 px-4 text-sm font-medium border-b-2 transition-colors"
                            :class="activeTab === 'tips' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'">
                        <svg class="inline h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Tips
                    </button>
                    @endif
                </div>

                <!-- Chat Tab -->
                <div x-show="activeTab === 'chat'" class="flex-1 flex flex-col min-h-0">
                    <!-- Chat History -->
                    <div class="flex-1 overflow-y-auto p-4 space-y-3 min-h-0" id="chat-container">
                        @if(empty($chatHistory))
                            <div class="text-center text-gray-500 dark:text-gray-400 py-8">
                                <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                </svg>
                                <p class="text-sm">Ask me anything about code scanning!</p>
                            </div>
                        @endif

                        @foreach($chatHistory as $message)
                            <div class="flex {{ $message['type'] === 'user' ? 'justify-end' : 'justify-start' }}">
                                <div class="max-w-xs lg:max-w-md px-4 py-2 rounded-lg {{ $message['type'] === 'user' ? 'bg-indigo-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-100' }}">
                                    <div class="text-sm">
                                        @if($message['type'] === 'assistant')
                                            <!-- AI Assistant message with markdown support -->
                                            <div class="markdown-content" data-markdown-content="{{ base64_encode($message['message']) }}">
                                                <!-- Fallback content while markdown loads -->
                                                {!! nl2br(e($message['message'])) !!}
                                            </div>
                                        @else
                                            <!-- User message (plain text) -->
                                            <div>{!! nl2br(e($message['message'])) !!}</div>
                                        @endif
                                    </div>
                                    <p class="text-xs opacity-75 mt-1">{{ $message['timestamp'] }}</p>
                                </div>
                            </div>
                        @endforeach

                        @if($isLoading)
                            <div class="flex justify-start">
                                <div class="bg-gray-100 dark:bg-gray-700 rounded-lg px-4 py-3 max-w-xs">
                                    <div class="flex items-center space-x-3">
                                        <div class="flex space-x-1">
                                            <div class="w-2 h-2 bg-indigo-500 rounded-full animate-pulse"></div>
                                            <div class="w-2 h-2 bg-indigo-500 rounded-full animate-pulse" style="animation-delay: 0.2s"></div>
                                            <div class="w-2 h-2 bg-indigo-500 rounded-full animate-pulse" style="animation-delay: 0.4s"></div>
                                        </div>
                                        <span class="text-xs text-gray-600 dark:text-gray-400">AI is thinking...</span>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Quick Actions -->
                    @if($showQuickActions && empty($chatHistory) && $aiAvailable)
                        <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Quick Actions:</p>
                            <div class="grid grid-cols-2 gap-2">
                                @foreach($this->getQuickActions() as $action)
                                    <button wire:click="{{ $action['action'] }}" 
                                            class="p-2 text-xs bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 rounded-lg text-left transition-colors">
                                        <div class="flex items-center space-x-2">
                                            <svg class="h-4 w-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                @switch($action['icon'])
                                                    @case('code')
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                                                        @break
                                                    @case('shield')
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                                        @break
                                                    @case('search')
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                                        @break
                                                    @case('light-bulb')
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                                                        @break
                                                    @case('star')
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                                        @break
                                                    @case('lightning-bolt')
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                                        @break
                                                @endswitch
                                            </svg>
                                            <span class="font-medium">{{ $action['title'] }}</span>
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Chat Input -->
                    <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                        @if($aiAvailable)
                        <div class="flex space-x-2">
                            <div class="flex-1 relative">
                                <input type="text" 
                                       wire:model="userQuestion"
                                       wire:keydown.enter="askAI"
                                       wire:loading.attr="disabled"
                                       wire:loading.class="opacity-75"
                                       placeholder="Ask about code scanning..."
                                       class="pl-2 py-2 pr-8 w-full text-sm border-gray-300 dark:border-gray-600 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white disabled:opacity-75">
                                <!-- Small loading indicator in input field -->
                                <div wire:loading wire:target="askAI" class="absolute right-2 top-1/2 transform -translate-y-1/2">
                                    <svg class="animate-spin h-3 w-3 text-gray-400" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </div>
                            </div>
                            <button wire:click="askAI" 
                                    wire:loading.attr="disabled"
                                    class="px-3 py-2 bg-indigo-500 hover:bg-indigo-600 text-white rounded-lg transition-colors disabled:opacity-50">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                </svg>
                            </button>
                        </div>
                        @else
                        <div class="text-center">
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">
                                AI chat is not available. Configure AI settings first.
                            </p>
                            <button wire:click="testAiConnection" 
                                    wire:loading.attr="disabled"
                                    class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm rounded-lg transition-colors disabled:opacity-50">
                                Test Connection
                            </button>
                        </div>
                        @endif
                        @if(!empty($chatHistory))
                            <button wire:click="clearChat" class="text-xs text-gray-500 hover:text-gray-700 mt-2">
                                Clear chat
                            </button>
                        @endif
                    </div>
                </div>

                <!-- Suggestions Tab -->
                @if($aiAvailable)
                <div x-show="activeTab === 'suggestions'" class="flex-1 overflow-y-auto p-4">
                    @if(empty($suggestions))
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                            </svg>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">No suggestions available</p>
                            <button wire:click="getScanSuggestions" class="btn btn-primary btn-sm">
                                Get Suggestions
                            </button>
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach($suggestions as $index => $suggestion)
                                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <h4 class="font-medium text-sm text-gray-900 dark:text-white">{{ $suggestion['title'] }}</h4>
                                            <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">{{ $suggestion['description'] }}</p>
                                            @if(isset($suggestion['categories']))
                                                <div class="flex flex-wrap gap-1 mt-2">
                                                    @foreach($suggestion['categories'] as $category)
                                                        <span class="px-2 py-1 text-xs bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300 rounded">{{ $category }}</span>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                        <button wire:click="applySuggestion({{ $index }})" 
                                                class="ml-2 text-xs text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300">
                                            Apply
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Tips Tab -->
                <div x-show="activeTab === 'tips'" class="flex-1 overflow-y-auto p-4">
                    @if(empty($tips))
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">No tips available</p>
                            <button wire:click="getContextualTips" class="btn btn-primary btn-sm">
                                Get Tips
                            </button>
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach($tips as $tip)
                                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                                    <div class="flex items-start space-x-2">
                                        <div class="flex-shrink-0 mt-1">
                                            @switch($tip['type'] ?? 'info')
                                                @case('warning')
                                                    <svg class="h-4 w-4 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                                    </svg>
                                                    @break
                                                @case('success')
                                                    <svg class="h-4 w-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                    </svg>
                                                    @break
                                                @default
                                                    <svg class="h-4 w-4 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                                    </svg>
                                            @endswitch
                                        </div>
                                        <div class="flex-1">
                                            <h4 class="font-medium text-sm text-gray-900 dark:text-white">{{ $tip['title'] }}</h4>
                                            <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">{{ $tip['description'] }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
                @endif
            @else
                <!-- AI Not Available -->
                <div class="flex-1 flex items-center justify-center p-6">
                    <div class="text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">AI Assistant Unavailable</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                            Enable AI integration in settings to unlock smart features.
                        </p>
                        
                        <!-- Debug Information -->
                        @if(config('app.debug'))
                        <div class="mb-4 p-3 bg-gray-100 dark:bg-gray-700 rounded-lg text-left">
                            <p class="text-xs font-semibold text-gray-600 dark:text-gray-300 mb-2">Debug Info:</p>
                            @php $debugInfo = $this->getDebugInfo(); @endphp
                            @foreach($debugInfo as $key => $value)
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    <strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong> 
                                    @if(is_bool($value))
                                        <span class="{{ $value ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $value ? 'Yes' : 'No' }}
                                        </span>
                                    @else
                                        {{ $value }}
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        @endif
                        
                        <div class="space-y-2">
                            <a href="{{ route('codesnoutr.settings') }}" class="inline-block px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
                                Configure AI
                            </a>
                            <button wire:click="refreshAiStatus" class="inline-block px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition-colors">
                                Refresh Status
                            </button>
                            <button wire:click="forceAiRefresh" class="inline-block px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                                Force Refresh
                            </button>
                            <button wire:click="checkAiStatus" class="inline-block px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg transition-colors">
                                Check Status
                            </button>
                            <button wire:click="testAiConnection" class="inline-block px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors">
                                Test Connection
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
    @endif

    <!-- Floating Action Button -->
    <button wire:click="toggleAssistant" 
            class="h-14 w-14 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white rounded-full shadow-lg hover:shadow-xl transition-all duration-300 flex items-center justify-center transform hover:scale-105 {{ $isOpen ? 'hidden' : '' }}">
        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
        </svg>
    </button>

    @if(!$isOpen)
    <!-- Quick Tooltip -->
    <div class="absolute bottom-16 right-0 bg-black text-white text-xs rounded-lg px-3 py-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">
        AI Assistant {{ $aiAvailable ? '(Ready)' : '(Configure in Settings)' }}
        <div class="absolute top-full right-4 transform -translate-x-1/2">
            <div class="w-2 h-2 bg-black transform rotate-45"></div>
        </div>
    </div>
    @endif
    
    <!-- Markdown CSS Styles -->
    <style>
    /* Custom Scrollbar Styling */
    #chat-container::-webkit-scrollbar {
        width: 6px;
    }

    #chat-container::-webkit-scrollbar-track {
        background: transparent;
    }

    #chat-container::-webkit-scrollbar-thumb {
        background: rgba(156, 163, 175, 0.4);
        border-radius: 3px;
        transition: background 0.2s ease;
    }

    #chat-container::-webkit-scrollbar-thumb:hover {
        background: rgba(156, 163, 175, 0.7);
    }

    /* Dark mode scrollbar */
    .dark #chat-container::-webkit-scrollbar-thumb {
        background: rgba(75, 85, 99, 0.6);
    }

    .dark #chat-container::-webkit-scrollbar-thumb:hover {
        background: rgba(75, 85, 99, 0.9);
    }

    /* Firefox scrollbar styling */
    #chat-container {
        scrollbar-width: thin;
        scrollbar-color: rgba(156, 163, 175, 0.4) transparent;
    }

    .dark #chat-container {
        scrollbar-color: rgba(75, 85, 99, 0.6) transparent;
    }

    /* General scrollbar styling for all scrollable elements */
    .overflow-y-auto::-webkit-scrollbar {
        width: 6px;
    }

    .overflow-y-auto::-webkit-scrollbar-track {
        background: transparent;
    }

    .overflow-y-auto::-webkit-scrollbar-thumb {
        background: rgba(156, 163, 175, 0.3);
        border-radius: 3px;
        transition: background 0.2s ease;
    }

    .overflow-y-auto::-webkit-scrollbar-thumb:hover {
        background: rgba(156, 163, 175, 0.6);
    }

    .dark .overflow-y-auto::-webkit-scrollbar-thumb {
        background: rgba(75, 85, 99, 0.5);
    }

    .dark .overflow-y-auto::-webkit-scrollbar-thumb:hover {
        background: rgba(75, 85, 99, 0.8);
    }

    /* Markdown Content Styling */
    .markdown-content {
        line-height: 1.6;
    }

    .markdown-content h1, .markdown-content h2, .markdown-content h3, .markdown-content h4, .markdown-content h5, .markdown-content h6 {
        font-weight: bold;
        margin: 0.5em 0 0.3em 0;
    }

    .markdown-content h1 { font-size: 1.2em; }
    .markdown-content h2 { font-size: 1.1em; }
    .markdown-content h3 { font-size: 1.05em; }

    .markdown-content strong, .markdown-content b {
        font-weight: bold;
    }

    .markdown-content em, .markdown-content i {
        font-style: italic;
    }

    .markdown-content ul, .markdown-content ol {
        margin: 0.5em 0;
        padding-left: 1.2em;
    }

    .markdown-content ul li {
        list-style-type: disc;
        margin: 0.2em 0;
    }

    .markdown-content ol li {
        list-style-type: decimal;
        margin: 0.2em 0;
    }

    .markdown-content p {
        margin: 0.5em 0;
    }

    .markdown-content code {
        background-color: rgba(0, 0, 0, 0.1);
        padding: 0.1em 0.3em;
        border-radius: 3px;
        font-family: 'Courier New', monospace;
        font-size: 0.9em;
    }

    .dark .markdown-content code {
        background-color: rgba(255, 255, 255, 0.1);
    }

    .markdown-content pre {
        background-color: #1a1a1a;
        color: #00ff00;
        padding: 1em;
        border-radius: 8px;
        overflow-x: auto;
        margin: 0.5em 0;
        font-family: 'Courier New', monospace;
        font-size: 0.8em;
    }

    .markdown-content pre code {
        background: none;
        padding: 0;
        color: inherit;
    }

    .markdown-content blockquote {
        border-left: 3px solid #ddd;
        margin: 0.5em 0;
        padding-left: 1em;
        color: #666;
    }

    .dark .markdown-content blockquote {
        border-left-color: #555;
        color: #aaa;
    }
    </style>
</div>

<script>
document.addEventListener('livewire:init', () => {
    // Simple markdown parser
    function parseMarkdown(text) {
        // Escape HTML first
        text = text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        
        // Headers
        text = text.replace(/^### (.*$)/gim, '<h3>$1</h3>');
        text = text.replace(/^## (.*$)/gim, '<h2>$1</h2>');
        text = text.replace(/^# (.*$)/gim, '<h1>$1</h1>');
        
        // Bold and italic
        text = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        text = text.replace(/\*(.*?)\*/g, '<em>$1</em>');
        
        // Code blocks (must be before inline code)
        text = text.replace(/```(\w+)?\n([\s\S]*?)```/g, '<pre><code>$2</code></pre>');
        text = text.replace(/```\n([\s\S]*?)```/g, '<pre><code>$1</code></pre>');
        
        // Inline code
        text = text.replace(/`([^`]+)`/g, '<code>$1</code>');
        
        // Simple list processing
        const lines = text.split('\n');
        let inList = false;
        let inOrderedList = false;
        let result = [];
        
        for (let i = 0; i < lines.length; i++) {
            const line = lines[i];
            
            // Check for bullet points (handles various bullet formats)
            if (line.match(/^[\*\-\+•]\s+(.*)$/)) {
                const content = line.replace(/^[\*\-\+•]\s+/, '');
                if (!inList) {
                    result.push('<ul class="list-disc">');
                    inList = true;
                }
                if (inOrderedList) {
                    result.push('</ol>');
                    inOrderedList = false;
                }
                result.push('<li>' + content + '</li>');
            }
            // Check for numbered lists
            else if (line.match(/^\d+\.\s+(.*)$/)) {
                const content = line.replace(/^\d+\.\s+/, '');
                if (!inOrderedList) {
                    result.push('<ol>');
                    inOrderedList = true;
                }
                if (inList) {
                    result.push('</ul>');
                    inList = false;
                }
                result.push('<li>' + content + '</li>');
            }
            // Regular line
            else {
                if (inList) {
                    result.push('</ul>');
                    inList = false;
                }
                if (inOrderedList) {
                    result.push('</ol>');
                    inOrderedList = false;
                }
                result.push(line);
            }
        }
        
        // Close any open lists
        if (inList) result.push('</ul>');
        if (inOrderedList) result.push('</ol>');
        
        text = result.join('\n');
        
        // Line breaks and paragraphs
        text = text.replace(/\n\n/g, '</p><p>');
        text = '<p>' + text + '</p>';
        
        // Clean up empty paragraphs
        text = text.replace(/<p><\/p>/g, '');
        text = text.replace(/<p>(<[hou])/g, '$1');
        text = text.replace(/(<\/[hou][^>]*>)<\/p>/g, '$1');
        
        return text;
    }
    
    // Process markdown content
    function processMarkdownElements() {
        document.querySelectorAll('[data-markdown-content]').forEach(element => {
            if (!element.dataset.processed) {
                const encodedContent = element.dataset.markdownContent;
                // Proper UTF-8 base64 decoding
                const decodedContent = decodeURIComponent(escape(atob(encodedContent)));
                // Normalize encoded bullets
                const normalizedContent = decodedContent.replace(/â¢/g, '•');
                const parsedContent = parseMarkdown(normalizedContent);
                element.innerHTML = parsedContent;
                element.dataset.processed = 'true';
            }
        });
    }
    
    // Process existing elements
    processMarkdownElements();
    
    // Process new elements when chat updates
    Livewire.on('chatUpdated', () => {
        setTimeout(() => {
            processMarkdownElements();
            const chatContainer = document.getElementById('chat-container');
            if (chatContainer) {
                chatContainer.scrollTop = chatContainer.scrollHeight;
            }
        }, 100);
    });
    
    // Auto-scroll chat to bottom
    Livewire.on('chatUpdated', () => {
        const chatContainer = document.getElementById('chat-container');
        if (chatContainer) {
            chatContainer.scrollTop = chatContainer.scrollHeight;
        }
    });
});
</script>
