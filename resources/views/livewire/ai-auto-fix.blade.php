<!-- AI Auto-Fix Component -->
<div class="bg-gradient-to-br from-purple-50 via-indigo-50 to-blue-50 dark:from-purple-900/20 dark:via-indigo-900/20 dark:to-blue-900/20 rounded-xl border border-purple-200 dark:border-purple-700/50 shadow-lg">
    @if($aiAvailable)
        @if($fixApplied)
            <!-- Fix Applied State -->
            <div class="p-6">
                <div class="flex items-center space-x-4 mb-4">
                    <div class="flex-shrink-0">
                        <div class="h-12 w-12 bg-green-500 rounded-full flex items-center justify-center">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                    </div>
                    <div>
                        <h4 class="text-lg font-semibold text-green-900 dark:text-green-100">Fix Applied Successfully!</h4>
                        <p class="text-sm text-green-700 dark:text-green-300">
                            The AI-generated fix has been applied to your code. A backup was created automatically.
                        </p>
                    </div>
                </div>

                <!-- Restore Option -->
                <div class="flex items-center justify-between p-4 bg-white dark:bg-gray-800 rounded-lg border border-green-200 dark:border-green-700">
                    <div>
                        <p class="text-sm text-gray-700 dark:text-gray-300">
                            If the fix doesn't work as expected, you can restore the original code.
                        </p>
                        @if($backupPath)
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            Backup: {{ basename($backupPath) }}
                        </p>
                        @endif
                    </div>
                    <button wire:click="restoreFromBackup" 
                            class="px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white text-sm font-medium rounded-lg transition-colors">
                        <svg class="inline h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                        </svg>
                        Restore Original
                    </button>
                </div>
            </div>
        @elseif(!$showRecommendations && !$showPreview)
            <!-- Initial State - Action Buttons -->
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0">
                            <div class="h-10 w-10 bg-gradient-to-r from-purple-500 to-indigo-600 rounded-full flex items-center justify-center">
                                <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                            </div>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 dark:text-white">AI-Powered Issue Resolution</h4>
                            <p class="text-xs text-gray-600 dark:text-gray-400">Get intelligent analysis and automated fixes</p>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="space-y-3">
                    <!-- Analyze Issue Button -->
                    <button wire:click="analyzeIssue" 
                            wire:loading.attr="disabled"
                            wire:target="analyzeIssue"
                            class="w-full flex items-center justify-center px-4 py-3 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white text-sm font-medium rounded-lg transition-all duration-200 disabled:opacity-50">
                        <span wire:loading.remove wire:target="analyzeIssue" class="flex items-center">
                            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                            </svg>
                            Analyze Issue & Get Recommendations
                        </span>
                        <span wire:loading wire:target="analyzeIssue" class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Analyzing...
                        </span>
                    </button>

                    @if($autoFixEnabled)
                    <!-- Generate Auto-Fix Button -->
                    <button wire:click="generateAutoFix" 
                            wire:loading.attr="disabled"
                            wire:target="generateAutoFix"
                            class="w-full flex items-center justify-center px-4 py-3 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white text-sm font-medium rounded-lg transition-all duration-200 disabled:opacity-50">
                        <span wire:loading.remove wire:target="generateAutoFix" class="flex items-center">
                            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            Generate Auto-Fix with Preview
                        </span>
                        <span wire:loading wire:target="generateAutoFix" class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Generating Fix...
                        </span>
                    </button>
                    @else
                    <div class="p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700/50 rounded-lg">
                        <p class="text-sm text-yellow-800 dark:text-yellow-200">
                            Auto-fix is not enabled. Only recommendations will be available.
                        </p>
                    </div>
                    @endif
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
                <button wire:click="resetState" class="mt-2 text-xs text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                    Try again
                </button>
            </div>
        @endif

        @if($showRecommendations && $recommendations)
            <!-- AI Recommendations -->
            <div class="p-6 border-t border-purple-200 dark:border-purple-700/50">
                <div class="flex items-center justify-between mb-4">
                    <h5 class="text-lg font-semibold text-gray-900 dark:text-white">AI Analysis & Recommendations</h5>
                    <button wire:click="hideRecommendations" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Confidence Badge -->
                @if(isset($recommendations['confidence']))
                <div class="mb-4">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium 
                        {{ $this->getConfidenceColor($recommendations['confidence']) === 'green' ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400' : '' }}
                        {{ $this->getConfidenceColor($recommendations['confidence']) === 'yellow' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400' : '' }}
                        {{ $this->getConfidenceColor($recommendations['confidence']) === 'orange' ? 'bg-orange-100 text-orange-800 dark:bg-orange-900/20 dark:text-orange-400' : '' }}
                        {{ $this->getConfidenceColor($recommendations['confidence']) === 'red' ? 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400' : '' }}">
                        {{ $this->getConfidenceText($recommendations['confidence']) }} ({{ round($recommendations['confidence'] * 100) }}%)
                    </span>
                </div>
                @endif

                <!-- Recommendation Text -->
                @if(isset($recommendations['suggestion']))
                <div class="mb-4 p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                    <h6 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Recommended Solution:</h6>
                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ $recommendations['suggestion'] }}</p>
                </div>
                @endif

                <!-- Explanation -->
                @if(isset($recommendations['explanation']))
                <div class="mb-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-700/50">
                    <h6 class="text-sm font-medium text-blue-900 dark:text-blue-300 mb-2">Detailed Explanation:</h6>
                    <p class="text-sm text-blue-800 dark:text-blue-200">{{ $recommendations['explanation'] }}</p>
                </div>
                @endif

                <!-- Code Example -->
                @if(isset($recommendations['code_example']) && !empty($recommendations['code_example']))
                <div class="mb-4 bg-gray-900 rounded-lg overflow-hidden">
                    <div class="flex items-center justify-between p-3 bg-gray-800">
                        <h6 class="text-sm font-medium text-gray-300">Example Implementation:</h6>
                        <button wire:click="copyFixCode" 
                                class="text-gray-400 hover:text-gray-200 transition-colors"
                                title="Copy to clipboard">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                        </button>
                    </div>
                    <div class="p-4">
                        <pre class="text-sm text-gray-300 font-mono overflow-x-auto"><code>{{ $recommendations['code_example'] }}</code></pre>
                    </div>
                </div>
                @endif

                <!-- Action Buttons -->
                <div class="flex items-center space-x-3">
                    @if($autoFixEnabled && (!isset($recommendations['automated_fix']) || !$recommendations['automated_fix']))
                    <button wire:click="generateAutoFix" 
                            wire:loading.attr="disabled"
                            wire:target="generateAutoFix"
                            class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors">
                        <span wire:loading.remove wire:target="generateAutoFix">
                            <svg class="inline h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            Generate Auto-Fix
                        </span>
                        <span wire:loading wire:target="generateAutoFix">
                            <svg class="animate-spin inline h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Generating...
                        </span>
                    </button>
                    @endif

                    <button wire:click="copyFixCode" 
                            class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition-colors">
                        <svg class="inline h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                        Copy Code
                    </button>
                </div>
            </div>
        @endif

        @if($showPreview && $fixPreview)
            <!-- Auto-Fix Preview -->
            <div class="p-6 border-t border-purple-200 dark:border-purple-700/50">
                <div class="flex items-center justify-between mb-4">
                    <h5 class="text-lg font-semibold text-gray-900 dark:text-white">Auto-Fix Preview</h5>
                    <button wire:click="hidePreview" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Fix Information -->
                <div class="mb-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="p-3 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                        <div class="text-sm text-gray-600 dark:text-gray-400">Fix Type</div>
                        <div class="font-medium text-gray-900 dark:text-white">
                            {{ $this->getFixTypeDescription($fixPreview['fix_data']['type']) }}
                        </div>
                    </div>
                    
                    <div class="p-3 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                        <div class="text-sm text-gray-600 dark:text-gray-400">Confidence</div>
                        <div class="font-medium text-gray-900 dark:text-white">
                            {{ round(($fixPreview['fix_data']['confidence'] ?? 0) * 100) }}%
                        </div>
                    </div>
                    
                    <div class="p-3 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                        <div class="text-sm text-gray-600 dark:text-gray-400">Changes</div>
                        <div class="font-medium text-gray-900 dark:text-white">
                            {{ $fixPreview['preview']['total_changes'] }} {{ Str::plural('line', $fixPreview['preview']['total_changes']) }}
                        </div>
                    </div>
                </div>

                <!-- Fix Explanation -->
                @if(isset($fixPreview['fix_data']['explanation']))
                <div class="mb-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-700/50">
                    <h6 class="text-sm font-medium text-blue-900 dark:text-blue-300 mb-2">What will be changed:</h6>
                    <p class="text-sm text-blue-800 dark:text-blue-200">{{ $fixPreview['fix_data']['explanation'] }}</p>
                </div>
                @endif

                <!-- Code Preview -->
                <div class="mb-4 bg-gray-900 rounded-lg overflow-hidden">
                    <div class="p-3 bg-gray-800 border-b border-gray-700">
                        <h6 class="text-sm font-medium text-gray-300">Code Changes Preview</h6>
                    </div>
                    <div class="p-4 max-h-96 overflow-y-auto">
                        @if(isset($fixPreview['preview']['diff']) && count($fixPreview['preview']['diff']) > 0)
                            @foreach($fixPreview['preview']['diff'] as $change)
                            <div class="mb-2">
                                <div class="text-xs text-gray-400 mb-1">Line {{ $change['line'] }}:</div>
                                @if($change['original'])
                                <div class="bg-red-900/30 border-l-4 border-red-500 px-3 py-1 mb-1">
                                    <span class="text-red-300 text-xs">- </span>
                                    <span class="text-gray-300 font-mono text-sm">{{ $change['original'] }}</span>
                                </div>
                                @endif
                                @if($change['modified'])
                                <div class="bg-green-900/30 border-l-4 border-green-500 px-3 py-1">
                                    <span class="text-green-300 text-xs">+ </span>
                                    <span class="text-gray-300 font-mono text-sm">{{ $change['modified'] }}</span>
                                </div>
                                @endif
                            </div>
                            @endforeach
                        @else
                            <div class="text-gray-400 text-sm">No changes to preview</div>
                        @endif
                    </div>
                </div>

                <!-- Safety Warning -->
                @if(!$this->isSafeToAutoApply())
                <div class="mb-4 p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700/50 rounded-lg">
                    <div class="flex items-center space-x-2">
                        <svg class="h-5 w-5 text-yellow-600 dark:text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-sm text-yellow-800 dark:text-yellow-200">
                            This fix has {{ isset($fixPreview['fix_data']['safe_to_automate']) && !$fixPreview['fix_data']['safe_to_automate'] ? 'not been marked as safe for automation' : 'low confidence' }}. Please review carefully before applying.
                        </span>
                    </div>
                </div>
                @endif

                <!-- Action Buttons -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <button wire:click="applyFix" 
                                wire:loading.attr="disabled"
                                wire:target="applyFix"
                                class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors disabled:opacity-50">
                            <span wire:loading.remove wire:target="applyFix">
                                <svg class="inline h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Apply Fix
                            </span>
                            <span wire:loading wire:target="applyFix">
                                <svg class="animate-spin inline h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Applying...
                            </span>
                        </button>

                        <button wire:click="copyFixCode" 
                                class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors">
                            <svg class="inline h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                            Copy Code
                        </button>
                    </div>

                    <div class="text-xs text-gray-500 dark:text-gray-400">
                        A backup will be created automatically
                    </div>
                </div>
            </div>
        @endif
    @else
        <!-- AI Not Available -->
        <div class="p-6">
            <div class="flex items-center space-x-3 text-gray-500 dark:text-gray-400">
                <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                <div>
                    <h4 class="text-sm font-medium">AI Assistant Unavailable</h4>
                    <p class="text-xs">Configure AI integration in settings to enable auto-fix features</p>
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
