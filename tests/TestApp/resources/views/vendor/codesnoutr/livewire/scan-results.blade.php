<div class="p-6">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Scan Results</h1>
                @if($scan)
                <p class="mt-2 text-gray-600 dark:text-gray-400">
                    {{ ucfirst($scan->type) }} scan of {{ $scan->target ?: 'full codebase' }}
                    • {{ $scan->created_at->format('M j, Y g:i A') }}
                </p>
                @endif
            </div>
            <div class="mt-4 lg:mt-0 flex space-x-3">
                <a href="{{ route('codesnoutr.results') }}" 
                   class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back to Results
                </a>
                @if($scan && $scan->status === 'completed')
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" 
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Export
                        <svg class="ml-2 -mr-0.5 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                    
                    <div x-show="open" @click.away="open = false" 
                         class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-md shadow-lg border border-gray-200 dark:border-gray-600 z-10">
                        <div class="py-1">
                            <button wire:click="exportResults('json')" 
                                    class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                Export as JSON
                            </button>
                            <button wire:click="exportResults('csv')" 
                                    class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                Export as CSV
                            </button>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    @if($scan)
    <!-- Scan Summary -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <!-- Total Issues -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 transition-theme">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="h-8 w-8 bg-red-100 dark:bg-red-900 rounded-md flex items-center justify-center">
                        <svg class="h-5 w-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 15.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ number_format($stats['total']) }}</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Total Issues</p>
                </div>
            </div>
        </div>

        <!-- Files Scanned -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 transition-theme">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="h-8 w-8 bg-blue-100 dark:bg-blue-900 rounded-md flex items-center justify-center">
                        <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ number_format($scan->files_scanned ?? 0) }}</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Files Scanned</p>
                </div>
            </div>
        </div>

        <!-- Resolved Issues -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 transition-theme">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="h-8 w-8 bg-green-100 dark:bg-green-900 rounded-md flex items-center justify-center">
                        <svg class="h-5 w-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ number_format($stats['resolved_count']) }}</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Resolved</p>
                </div>
            </div>
        </div>

        <!-- Scan Duration -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 transition-theme">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="h-8 w-8 bg-purple-100 dark:bg-purple-900 rounded-md flex items-center justify-center">
                        <svg class="h-5 w-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ number_format($scan->scan_duration ?? 0, 1) }}s</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Duration</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-8 transition-theme">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Search -->
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Search</label>
                <input type="text" 
                       wire:model.live.debounce.300ms="searchTerm" 
                       placeholder="Search issues..."
                       class="block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>

            <!-- Severity Filter -->
            <div>
                <label for="severity" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Severity</label>
                <select wire:model.live="selectedSeverity" 
                        class="block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    @foreach($severityOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Category Filter -->
            <div>
                <label for="category" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Category</label>
                <select wire:model.live="selectedCategory" 
                        class="block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    @foreach($categoryOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Actions -->
            <div class="flex items-end space-x-2">
                <button wire:click="clearFilters" 
                        class="px-4 py-2 bg-gray-600 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                    Clear
                </button>
                @if($selectedIssues && count($selectedIssues) > 0)
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" 
                            class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Bulk Actions ({{ count($selectedIssues) }})
                    </button>
                    
                    <div x-show="open" @click.away="open = false" 
                         class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-md shadow-lg border border-gray-200 dark:border-gray-600 z-10">
                        <div class="py-1">
                            <button wire:click="bulkResolve" 
                                    class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                Mark as Resolved
                            </button>
                            <button wire:click="bulkIgnore" 
                                    class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                Mark as Ignored
                            </button>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>

        @if($issues && $issues->count() > 0)
        <!-- Bulk Selection -->
        <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
            <div class="flex items-center space-x-4">
                <label class="flex items-center">
                    <input type="checkbox" 
                           wire:click="selectAllIssues"
                           class="h-4 w-4 text-indigo-600 border-gray-300 dark:border-gray-600 rounded focus:ring-indigo-500">
                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Select All</span>
                </label>
                @if($selectedIssues && count($selectedIssues) > 0)
                <button wire:click="deselectAllIssues" 
                        class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300">
                    Deselect All
                </button>
                @endif
            </div>
            
            <!-- Sort Options -->
            <div class="flex items-center space-x-2">
                <span class="text-sm text-gray-500 dark:text-gray-400">Sort by:</span>
                <button wire:click="sortBy('severity')" 
                        class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 {{ $sortBy === 'severity' ? 'font-medium' : '' }}">
                    Severity
                    @if($sortBy === 'severity')
                        <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                    @endif
                </button>
                <button wire:click="sortBy('category')" 
                        class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 {{ $sortBy === 'category' ? 'font-medium' : '' }}">
                    Category
                    @if($sortBy === 'category')
                        <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                    @endif
                </button>
                <button wire:click="sortBy('file_path')" 
                        class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 {{ $sortBy === 'file_path' ? 'font-medium' : '' }}">
                    File
                    @if($sortBy === 'file_path')
                        <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                    @endif
                </button>
            </div>
        </div>
        @endif
    </div>

    <!-- Issues List -->
    @if($issues && $issues->count() > 0)
    <div class="space-y-4">
        @foreach($issues as $issue)
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 transition-theme">
            <div class="p-6">
                <div class="flex items-start justify-between">
                    <div class="flex items-start space-x-4 flex-1">
                        <!-- Selection Checkbox -->
                        <div class="flex items-center h-5">
                            <input type="checkbox" 
                                   wire:click="toggleIssueSelection({{ $issue->id }})"
                                   {{ in_array($issue->id, $selectedIssues) ? 'checked' : '' }}
                                   class="h-4 w-4 text-indigo-600 border-gray-300 dark:border-gray-600 rounded focus:ring-indigo-500">
                        </div>

                        <!-- Severity Badge -->
                        <div class="flex-shrink-0">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium severity-{{ $issue->severity }}">
                                {{ ucfirst($issue->severity) }}
                            </span>
                        </div>

                        <!-- Issue Details -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center space-x-2">
                                <h4 class="text-lg font-medium text-gray-900 dark:text-white">{{ $issue->title }}</h4>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                    {{ ucfirst($issue->category) }}
                                </span>
                            </div>
                            
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $issue->description }}</p>
                            
                            <div class="mt-2 flex items-center text-sm text-gray-500 dark:text-gray-400">
                                <svg class="flex-shrink-0 mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span class="truncate">{{ $issue->file_path }}</span>
                                @if($issue->line_number)
                                    <span class="mx-2">•</span>
                                    <span>Line {{ $issue->line_number }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center space-x-2">
                        <button wire:click="toggleIssueExpansion({{ $issue->id }})" 
                                class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                            <svg class="h-5 w-5 transform transition-transform {{ in_array($issue->id, $expandedIssues) ? 'rotate-180' : '' }}" 
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        
                        @if($issue->status !== 'resolved')
                        <button wire:click="resolveIssue({{ $issue->id }})" 
                                class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded text-green-700 bg-green-100 hover:bg-green-200 dark:bg-green-900 dark:text-green-200 dark:hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            Resolve
                        </button>
                        @endif
                        
                        @if($issue->status !== 'ignored')
                        <button wire:click="markAsIgnored({{ $issue->id }})" 
                                class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded text-gray-700 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                            Ignore
                        </button>
                        @endif
                    </div>
                </div>

                <!-- Expanded Content -->
                @if(in_array($issue->id, $expandedIssues))
                <div class="mt-6 border-t border-gray-200 dark:border-gray-700 pt-6">
                    @if($issue->code_snippet)
                    <div class="mb-4">
                        <h5 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Code Snippet</h5>
                        <div class="bg-gray-900 rounded-lg p-4 overflow-x-auto">
                            <pre class="text-sm text-gray-100 code-block"><code>{{ $issue->code_snippet }}</code></pre>
                        </div>
                    </div>
                    @endif
                    
                    @if($issue->fix_suggestion)
                    <div class="mb-4">
                        <h5 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Fix Suggestion</h5>
                        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                            <p class="text-sm text-blue-800 dark:text-blue-200">{{ $issue->fix_suggestion }}</p>
                        </div>
                    </div>
                    @endif
                    
                    <div class="text-xs text-gray-500 dark:text-gray-400">
                        Issue found on {{ $issue->created_at->format('M j, Y g:i A') }}
                        @if($issue->resolved_at)
                            • Resolved on {{ $issue->resolved_at->format('M j, Y g:i A') }}
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    <!-- Pagination -->
    <div class="mt-8">
        {{ $issues->links() }}
    </div>
    @else
    <!-- Empty State -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center transition-theme">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 48 48">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">
            @if($searchTerm || $selectedSeverity !== 'all' || $selectedCategory !== 'all')
                No issues match your filters
            @else
                No issues found
            @endif
        </h3>
        <p class="mt-2 text-gray-500 dark:text-gray-400">
            @if($searchTerm || $selectedSeverity !== 'all' || $selectedCategory !== 'all')
                Try adjusting your search criteria or filters.
            @else
                This scan completed successfully with no issues detected.
            @endif
        </p>
        @if($searchTerm || $selectedSeverity !== 'all' || $selectedCategory !== 'all')
        <div class="mt-6">
            <button wire:click="clearFilters" 
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Clear Filters
            </button>
        </div>
        @endif
    </div>
    @endif
    @else
    <!-- No Scan Selected -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center transition-theme">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 48 48">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">Scan not found</h3>
        <p class="mt-2 text-gray-500 dark:text-gray-400">The requested scan could not be found or has been deleted.</p>
        <div class="mt-6">
            <a href="{{ route('codesnoutr.results') }}" 
               class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                View All Scans
            </a>
        </div>
    </div>
    @endif
</div>
