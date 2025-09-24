<div class="p-6">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Scan Results View</h2>
            @if($scan)
                <p class="text-gray-600 dark:text-gray-400 mt-1">
                    Scan #{{ $scan->id }} - {{ $scan->created_at->format('M j, Y g:i A') }}
                </p>
            @endif
        </div>
        
        <div class="flex space-x-3">
            <button wire:click="exportResults('json')" 
                    class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-md transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Export JSON
            </button>
        </div>
    </div>

    @if($issues && $issues->count() > 0)
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Issues Found ({{ $issues->count() }})
                    </h3>
                </div>

                <div class="space-y-4">
                    @foreach($issues as $issue)
                        <div class="border-l-4 border-gray-200 dark:border-gray-600 pl-4 py-2">
                            <div class="flex items-center justify-between">
                                <h4 class="font-medium text-gray-900 dark:text-white">
                                    {{ $issue->title }}
                                </h4>
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $issue->severity === 'critical' ? 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-300' : '' }}
                                    {{ $issue->severity === 'high' ? 'bg-orange-100 text-orange-800 dark:bg-orange-900/20 dark:text-orange-300' : '' }}
                                    {{ $issue->severity === 'medium' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-300' : '' }}
                                    {{ $issue->severity === 'low' || $issue->severity === 'info' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-300' : '' }}">
                                    {{ ucfirst($issue->severity) }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                {{ $issue->description }}
                            </p>
                            <div class="flex items-center space-x-4 text-xs text-gray-500 dark:text-gray-400 mt-2">
                                <span>{{ $issue->file_path }}</span>
                                <span>Line {{ $issue->line_number }}</span>
                                <span>{{ ucfirst($issue->category) }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @else
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700 p-12">
            <div class="text-center">
                <div class="mx-auto w-24 h-24 bg-green-100 dark:bg-green-900/20 rounded-full flex items-center justify-center mb-6">
                    <svg class="w-12 h-12 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">Great job! No issues found</h3>
                <p class="text-gray-600 dark:text-gray-400 max-w-sm mx-auto">
                    Your code is clean and follows best practices. No security vulnerabilities, performance issues, or code quality problems were detected.
                </p>
            </div>
        </div>
    @endif
</div>