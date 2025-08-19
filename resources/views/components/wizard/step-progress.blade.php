<!-- Step 5: Scan Progress -->
<div class="space-y-6">
    <div class="text-center mb-8">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Analyzing Your Code</h3>
        <p class="text-gray-600 dark:text-gray-400">Please wait while we scan your project for issues</p>
        
        @if($scanPath)
        <div class="mt-4 inline-flex items-center px-3 py-2 bg-indigo-50 dark:bg-indigo-900 border border-indigo-200 dark:border-indigo-800 rounded-lg">
            <svg class="w-4 h-4 mr-2 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v2H8V5z"/>
            </svg>
            <span class="text-sm text-indigo-700 dark:text-indigo-300 font-medium">Scanning:</span>
            <span class="text-sm text-indigo-800 dark:text-indigo-200 ml-1 font-mono">{{ $scanPath }}</span>
        </div>
        @endif
    </div>

    <!-- Overall Progress -->
    <div class="bg-white dark:bg-gray-700 rounded-xl border border-gray-200 dark:border-gray-600 p-6">
        <div class="flex items-center justify-between mb-4">
            <h4 class="font-semibold text-gray-900 dark:text-white">Scan Progress</h4>
            <span class="text-sm text-gray-600 dark:text-gray-400">{{ $scanProgress }}%</span>
        </div>
        
        <!-- Progress Bar -->
        <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-3 mb-4">
            <div class="bg-gradient-to-r from-indigo-500 to-purple-600 h-3 rounded-full transition-all duration-500 ease-out" 
                 style="width: {{ $scanProgress }}%"></div>
        </div>

        <!-- Current Activity -->
        <div class="space-y-2">
            <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                <div class="flex items-center mr-4">
                    <svg class="animate-spin h-4 w-4 mr-2 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Currently scanning...
                </div>
                <span class="text-gray-900 dark:text-white font-medium">{{ $currentActivity ?? 'Initializing scan...' }}</span>
            </div>
            
            @if(isset($currentFile))
            <div class="flex items-center text-xs text-gray-500 dark:text-gray-400 ml-6">
                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span class="font-mono">{{ basename($currentFile) }}</span>
            </div>
            @endif
        </div>
    </div>

    <!-- Scan Statistics -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600 p-4 text-center">
            <div class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ $filesScanned ?? 0 }}</div>
            <div class="text-sm text-gray-600 dark:text-gray-400">Files Scanned</div>
        </div>
        <div class="bg-white dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600 p-4 text-center">
            <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $issuesFound ?? 0 }}</div>
            <div class="text-sm text-gray-600 dark:text-gray-400">Issues Found</div>
        </div>
        <div class="bg-white dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600 p-4 text-center">
            <div class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ $rulesApplied ?? 0 }}</div>
            <div class="text-sm text-gray-600 dark:text-gray-400">Rules Applied</div>
        </div>
        <div class="bg-white dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600 p-4 text-center">
            <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ $timeElapsed ?? '0:00' }}</div>
            <div class="text-sm text-gray-600 dark:text-gray-400">Time Elapsed</div>
        </div>
    </div>

    <!-- Recent Activity Log -->
    @if(!empty($activityLog))
    <div class="bg-white dark:bg-gray-700 rounded-xl border border-gray-200 dark:border-gray-600">
        <div class="p-4 border-b border-gray-200 dark:border-gray-600">
            <h4 class="font-semibold text-gray-900 dark:text-white">Activity Log</h4>
        </div>
        <div class="p-4 max-h-64 overflow-y-auto">
            <div class="space-y-3">
                @foreach(array_reverse($activityLog) as $index => $activity)
                <div class="flex items-start {{ $index === 0 ? 'opacity-100' : 'opacity-75' }}">
                    <div class="flex-shrink-0 mr-3 mt-1">
                        @if($activity['type'] === 'success')
                        <div class="h-2 w-2 bg-green-500 rounded-full"></div>
                        @elseif($activity['type'] === 'warning')
                        <div class="h-2 w-2 bg-orange-500 rounded-full"></div>
                        @elseif($activity['type'] === 'error')
                        <div class="h-2 w-2 bg-red-500 rounded-full"></div>
                        @else
                        <div class="h-2 w-2 bg-blue-500 rounded-full"></div>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-gray-900 dark:text-white">{{ $activity['message'] }}</p>
                        @if(isset($activity['details']))
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $activity['details'] }}</p>
                        @endif
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">{{ $activity['timestamp'] ?? now()->format('H:i:s') }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Issue Preview (if any found) -->
    @if(!empty($previewIssues))
    <div class="bg-white dark:bg-gray-700 rounded-xl border border-gray-200 dark:border-gray-600">
        <div class="p-4 border-b border-gray-200 dark:border-gray-600">
            <h4 class="font-semibold text-gray-900 dark:text-white">Issues Found (Preview)</h4>
            <p class="text-sm text-gray-600 dark:text-gray-400">First few issues discovered during the scan</p>
        </div>
        <div class="p-4 space-y-3">
            @foreach(array_slice($previewIssues, 0, 3) as $issue)
            <div class="flex items-start p-3 bg-gray-50 dark:bg-gray-600 rounded-lg">
                <div class="flex-shrink-0 mr-3">
                    @if($issue['severity'] === 'high')
                    <div class="h-8 w-8 bg-red-100 dark:bg-red-900 rounded-lg flex items-center justify-center">
                        <svg class="h-4 w-4 text-red-600 dark:text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    @elseif($issue['severity'] === 'medium')
                    <div class="h-8 w-8 bg-orange-100 dark:bg-orange-900 rounded-lg flex items-center justify-center">
                        <svg class="h-4 w-4 text-orange-600 dark:text-orange-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    @else
                    <div class="h-8 w-8 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                        <svg class="h-4 w-4 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <h5 class="text-sm font-medium text-gray-900 dark:text-white">{{ $issue['title'] }}</h5>
                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">{{ $issue['file'] }}:{{ $issue['line'] ?? '?' }}</p>
                    @if(isset($issue['category']))
                    <span class="inline-block mt-2 px-2 py-1 text-xs bg-gray-200 dark:bg-gray-500 text-gray-700 dark:text-gray-300 rounded">
                        {{ ucfirst($issue['category']) }}
                    </span>
                    @endif
                </div>
            </div>
            @endforeach
            
            @if(count($previewIssues) > 3)
            <div class="text-center text-sm text-gray-500 dark:text-gray-400 pt-2">
                And {{ count($previewIssues) - 3 }} more issues...
            </div>
            @endif
        </div>
    </div>
    @endif

    <!-- Control Buttons -->
    <div class="flex justify-center space-x-4">
        @if($scanStatus === 'running')
        <button type="button" 
                wire:click="pauseScan"
                class="inline-flex items-center px-4 py-2 text-sm font-medium text-orange-700 dark:text-orange-300 bg-orange-100 dark:bg-orange-900 border border-orange-200 dark:border-orange-800 rounded-lg hover:bg-orange-200 dark:hover:bg-orange-800 transition-colors">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM7 8a1 1 0 012 0v4a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v4a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            Pause Scan
        </button>
        @elseif($scanStatus === 'paused')
        <button type="button" 
                wire:click="resumeScan"
                class="inline-flex items-center px-4 py-2 text-sm font-medium text-green-700 dark:text-green-300 bg-green-100 dark:bg-green-900 border border-green-200 dark:border-green-800 rounded-lg hover:bg-green-200 dark:hover:bg-green-800 transition-colors">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"/>
            </svg>
            Resume Scan
        </button>
        @endif
        
        <button type="button" 
                wire:click="cancelScan"
                class="inline-flex items-center px-4 py-2 text-sm font-medium text-red-700 dark:text-red-300 bg-red-100 dark:bg-red-900 border border-red-200 dark:border-red-800 rounded-lg hover:bg-red-200 dark:hover:bg-red-800 transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            Cancel Scan
        </button>
    </div>

    @if($scanStatus === 'completed')
    <div class="text-center pt-4">
        <div class="mb-4 p-4 bg-green-50 dark:bg-green-900 border border-green-200 dark:border-green-800 rounded-lg">
            <div class="flex items-center justify-center">
                <svg class="h-6 w-6 text-green-600 dark:text-green-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <h4 class="text-lg font-semibold text-green-800 dark:text-green-300">Scan Completed!</h4>
                    <p class="text-sm text-green-700 dark:text-green-400">Found {{ $issuesFound ?? 0 }} issues in {{ $filesScanned ?? 0 }} files</p>
                </div>
            </div>
        </div>
        
        <button type="button" 
                wire:click="viewResults"
                class="inline-flex items-center px-6 py-3 text-base font-medium text-white bg-gradient-to-r from-green-600 to-blue-600 hover:from-green-700 hover:to-blue-700 rounded-lg shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            View Detailed Results
        </button>
    </div>
    @endif
</div>

@if($scanStatus === 'running')
<div wire:poll.2s="refreshProgress"></div>
@endif

<div wire:poll.2s="refreshProgress"></div>
