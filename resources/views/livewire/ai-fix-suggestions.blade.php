<!-- AI Fix Suggestions Component -->
<div class="bg-gradient-to-r from-purple-50 to-indigo-50 dark:from-purple-900/20 dark:to-indigo-900/20 rounded-lg border border-purple-200 dark:border-purple-700/50">
    @if($aiAvailable)
        @if(!$showSuggestion)
            <!-- Get Suggestion Button -->
            <div class="p-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0">
                            <div class="h-10 w-10 bg-gradient-to-r from-purple-500 to-indigo-600 rounded-full flex items-center justify-center">
                                <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                            </div>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 dark:text-white">AI-Powered Fix Suggestion</h4>
                            <p class="text-xs text-gray-600 dark:text-gray-400">Get intelligent recommendations to fix this issue</p>
                        </div>
                    </div>
                    <button wire:click="getFixSuggestion" 
                            wire:loading.attr="disabled"
                            class="px-4 py-2 bg-gradient-to-r from-purple-500 to-indigo-600 hover:from-purple-600 hover:to-indigo-700 text-white text-sm font-medium rounded-lg transition-all duration-200 disabled:opacity-50 transform hover:scale-105">
                        <span wire:loading.remove wire:target="getFixSuggestion">Get AI Fix</span>
                        <span wire:loading wire:target="getFixSuggestion" class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Analyzing...
                        </span>
                    </button>
                </div>
            </div>
        @endif

        @if($error)
            <!-- Error State -->
            <div class="p-4 border-t border-purple-200 dark:border-purple-700/50">
                <div class="flex items-center space-x-2 text-red-600 dark:text-red-400">
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-sm">{{ $error }}</span>
                </div>
                <button wire:click="hideSuggestion" class="mt-2 text-xs text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                    Try again
                </button>
            </div>
        @endif

        @if($showSuggestion && $fixSuggestion)
            <!-- AI Suggestion Content -->
            <div class="p-4 space-y-4">
                <!-- Header with Confidence -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <h4 class="text-sm font-medium text-gray-900 dark:text-white">AI Fix Suggestion</h4>
                        <span class="px-2 py-1 text-xs rounded-full {{ $this->getConfidenceColor() === 'green' ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400' : '' }}{{ $this->getConfidenceColor() === 'yellow' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400' : '' }}{{ $this->getConfidenceColor() === 'orange' ? 'bg-orange-100 text-orange-800 dark:bg-orange-900/20 dark:text-orange-400' : '' }}{{ $this->getConfidenceColor() === 'red' ? 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400' : '' }}">
                            {{ $this->getConfidenceText() }}
                        </span>
                    </div>
                    <button wire:click="hideSuggestion" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Fix Suggestion Text -->
                @if(isset($fixSuggestion['suggestion']))
                    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                        <h5 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Recommended Fix:</h5>
                        <p class="text-sm text-gray-700 dark:text-gray-300">{{ $fixSuggestion['suggestion'] }}</p>
                    </div>
                @endif

                <!-- Explanation -->
                @if(isset($fixSuggestion['explanation']))
                    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 border border-blue-200 dark:border-blue-700/50">
                        <h5 class="text-sm font-medium text-blue-900 dark:text-blue-300 mb-2">Explanation:</h5>
                        <p class="text-sm text-blue-800 dark:text-blue-200">{{ $fixSuggestion['explanation'] }}</p>
                    </div>
                @endif

                <!-- Code Example -->
                @if(isset($fixSuggestion['code_example']) && !empty($fixSuggestion['code_example']))
                    <div class="bg-gray-900 rounded-lg p-4 relative">
                        <div class="flex items-center justify-between mb-2">
                            <h5 class="text-sm font-medium text-gray-300">Code Example:</h5>
                            <button wire:click="copyFixToClipboard" 
                                    class="text-gray-400 hover:text-gray-200 transition-colors">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                            </button>
                        </div>
                        <pre class="text-sm text-gray-300 font-mono overflow-x-auto"><code>{{ $fixSuggestion['code_example'] }}</code></pre>
                    </div>
                @endif

                <!-- Action Buttons -->
                <div class="flex items-center justify-between pt-2 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex items-center space-x-3">
                        @if(isset($fixSuggestion['automated_fix']) && $fixSuggestion['automated_fix'])
                            <button wire:click="applyAutomatedFix" 
                                    class="px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors">
                                <svg class="inline h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Apply Fix
                            </button>
                        @endif

                        @if(isset($fixSuggestion['code_example']))
                            <button wire:click="copyFixToClipboard" 
                                    class="px-3 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition-colors">
                                <svg class="inline h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                                Copy Code
                            </button>
                        @endif
                    </div>

                    <div class="text-xs text-gray-500 dark:text-gray-400">
                        Confidence: {{ isset($fixSuggestion['confidence']) ? round($fixSuggestion['confidence'] * 100) : 0 }}%
                    </div>
                </div>

                <!-- Warning for Low Confidence -->
                @if(isset($fixSuggestion['confidence']) && $fixSuggestion['confidence'] < 0.6)
                    <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700/50 rounded-lg p-3">
                        <div class="flex items-center space-x-2">
                            <svg class="h-5 w-5 text-yellow-600 dark:text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-sm text-yellow-800 dark:text-yellow-200">
                                This suggestion has low confidence. Please review carefully before applying.
                            </span>
                        </div>
                    </div>
                @endif
            </div>
        @endif
    @else
        <!-- AI Not Available -->
        <div class="p-4">
            <div class="flex items-center space-x-3 text-gray-500 dark:text-gray-400">
                <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                <div>
                    <h4 class="text-sm font-medium">AI Assistant Unavailable</h4>
                    <p class="text-xs">Configure AI integration in settings to get fix suggestions</p>
                </div>
            </div>
        </div>
    @endif
</div>

<script>
document.addEventListener('livewire:init', () => {
    Livewire.on('copy-to-clipboard', (data) => {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(data.text);
        } else {
            // Fallback for older browsers
            const textArea = document.createElement('textarea');
            textArea.value = data.text;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
        }
    });
});
</script>
