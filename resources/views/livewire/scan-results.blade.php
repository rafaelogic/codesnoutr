<div class="p-6">
    <!-- Global Loading Indicator -->
    <div wire:loading.flex class="fixed inset-0 z-50 bg-black bg-opacity-50 items-center justify-center">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-xl border border-gray-200 dark:border-gray-700">
            <div class="flex items-center space-x-4">
                <svg class="w-8 h-8 animate-spin text-indigo-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <div>
                    <p class="text-lg font-medium text-gray-900 dark:text-white">Loading...</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Please wait while we fetch the data</p>
                </div>
            </div>
        </div>
    </div>

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
                        <div class="py-3">
                            <button wire:click="exportResults('json')" 
                                    class="block w-full text-left px-4 py-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                Export as JSON
                            </button>
                            <button wire:click="exportResults('csv')" 
                                    class="block w-full text-left px-4 py-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
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
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-lg hover:border-red-300 dark:hover:border-red-600 hover:shadow-red-500/10 dark:hover:shadow-red-400/10 transition-all duration-300">
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
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-lg hover:border-blue-300 dark:hover:border-blue-600 hover:shadow-blue-500/10 dark:hover:shadow-blue-400/10 transition-all duration-300">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="h-8 w-8 bg-blue-100 dark:bg-blue-900 rounded-md flex items-center justify-center">
                        <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ number_format($scan->total_files ?? 0) }}</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Files Scanned</p>
                </div>
            </div>
        </div>

        <!-- Resolved Issues -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-lg hover:border-green-300 dark:hover:border-green-600 hover:shadow-green-500/10 dark:hover:shadow-green-400/10 transition-all duration-300">
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
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-lg hover:border-purple-300 dark:hover:border-purple-600 hover:shadow-purple-500/10 dark:hover:shadow-purple-400/10 transition-all duration-300">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="h-8 w-8 bg-purple-100 dark:bg-purple-900 rounded-md flex items-center justify-center">
                        <svg class="h-5 w-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ number_format($scan->duration_seconds ?? 0, 1) }}s</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Duration</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-8 hover:shadow-lg hover:border-indigo-300 dark:hover:border-indigo-600 hover:shadow-indigo-500/10 dark:hover:shadow-indigo-400/10 transition-all duration-300">
        <!-- View Mode Toggle -->
        <div class="flex items-center justify-between mb-6 pb-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">View Options</h3>
            <div class="flex items-center space-x-1 bg-gray-100 dark:bg-gray-700 rounded-lg p-1">
                <button wire:click="setViewMode('grouped')"
                        class="px-3 py-1.5 text-sm font-medium rounded-md transition-colors {{ $viewMode === 'grouped' ? 'bg-white dark:bg-gray-600 text-gray-900 dark:text-white shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}">
                    <svg class="w-4 h-4 mr-1.5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                    </svg>
                    By Issue
                </button>
                <button wire:click="setViewMode('file-grouped')"
                        class="px-3 py-1.5 text-sm font-medium rounded-md transition-colors {{ $viewMode === 'file-grouped' ? 'bg-white dark:bg-gray-600 text-gray-900 dark:text-white shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}">
                    <svg class="w-4 h-4 mr-1.5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    By File
                </button>
                <button wire:click="setViewMode('detailed')"
                        class="px-3 py-1.5 text-sm font-medium rounded-md transition-colors {{ $viewMode === 'detailed' ? 'bg-white dark:bg-gray-600 text-gray-900 dark:text-white shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}">
                    <svg class="w-4 h-4 mr-1.5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v6a2 2 0 002 2h2m0 0h2m-2 0v4a2 2 0 002 2h2a2 2 0 002-2v-4m0 0V9a2 2 0 00-2-2H9z"/>
                    </svg>
                    Detailed
                </button>
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Search -->
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Search</label>
                <input type="text" 
                       wire:model.live.debounce.300ms="searchTerm" 
                       placeholder="Search issues..."
                       class="p-2 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>

            <!-- Severity Filter -->
            <div>
                <label for="severity" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Severity</label>
                <select wire:model.live="selectedSeverity" 
                        class="p-2 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    @foreach($severityOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Category Filter -->
            <div>
                <label for="category" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Category</label>
                <select wire:model.live="selectedCategory" 
                        class="p-2 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
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
                        <div class="py-3">
                            <button wire:click="bulkResolve" 
                                    class="block w-full text-left px-4 py-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                Mark as Resolved
                            </button>
                            <button wire:click="bulkIgnore" 
                                    class="block w-full text-left px-4 py-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                Mark as Ignored
                            </button>
                            <button wire:click="bulkMarkFalsePositive" 
                                    class="block w-full text-left px-4 py-3 text-sm text-red-700 dark:text-red-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                Mark as False Positive
                            </button>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>

        @if(($issues && $issues->count() > 0) || ($fileGroupedIssues && $fileGroupedIssues->count() > 0))
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

    <!-- Issues Display -->
    @if($viewMode === 'grouped' && $groupedIssues && $groupedIssues->count() > 0)
        <!-- Enhanced Grouped View -->
        <div class="space-y-8">
            @foreach($groupedIssues as $groupKey => $group)
            <div class="group bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-2xl hover:border-indigo-400 dark:hover:border-indigo-500 hover:shadow-indigo-500/20 dark:hover:shadow-indigo-400/20 transition-all duration-500">
                <!-- Enhanced Group Header -->
                <div class="relative px-8 py-6 bg-gradient-to-br from-white via-gray-50 to-white dark:from-gray-800 dark:via-gray-700 dark:to-gray-800 border-b border-gray-200 dark:border-gray-600">
                    <!-- Background Pattern -->
                    <div class="absolute inset-0 opacity-5 dark:opacity-10">
                        <div class="h-full w-full" style="background-image: radial-gradient(circle at 1px 1px, rgba(0,0,0,0.3) 1px, transparent 0); background-size: 20px 20px;"></div>
                    </div>
                    
                    <div class="relative flex items-start justify-between">
                        <div class="flex items-start space-x-6">
                            <!-- Enhanced Severity Badge -->
                            <div class="flex-shrink-0">
                                @php
                                    $severityConfig = match($group['severity']) {
                                        'critical' => [
                                            'gradient' => 'from-red-500 via-red-600 to-red-700',
                                            'border' => 'border-red-400',
                                            'glow' => 'shadow-red-200 dark:shadow-red-900/50',
                                            'icon' => 'M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z',
                                            'pulse' => 'animate-pulse'
                                        ],
                                        'high' => [
                                            'gradient' => 'from-orange-500 via-orange-600 to-orange-700',
                                            'border' => 'border-orange-400',
                                            'glow' => 'shadow-orange-200 dark:shadow-orange-900/50',
                                            'icon' => 'M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z',
                                            'pulse' => ''
                                        ],
                                        'medium' => [
                                            'gradient' => 'from-yellow-500 via-yellow-600 to-amber-600',
                                            'border' => 'border-yellow-400',
                                            'glow' => 'shadow-yellow-200 dark:shadow-yellow-900/50',
                                            'icon' => 'M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z',
                                            'pulse' => ''
                                        ],
                                        'low' => [
                                            'gradient' => 'from-blue-500 via-blue-600 to-blue-700',
                                            'border' => 'border-blue-400',
                                            'glow' => 'shadow-blue-200 dark:shadow-blue-900/50',
                                            'icon' => 'M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z',
                                            'pulse' => ''
                                        ],
                                        default => [
                                            'gradient' => 'from-gray-500 via-gray-600 to-gray-700',
                                            'border' => 'border-gray-400',
                                            'glow' => 'shadow-gray-200 dark:shadow-gray-900/50',
                                            'icon' => 'M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z',
                                            'pulse' => ''
                                        ]
                                    };
                                @endphp
                                
                                <div class="flex flex-col items-center space-y-2">
                                    <span class="inline-flex items-center px-4 py-2 rounded-xl text-sm font-bold text-white bg-gradient-to-r {{ $severityConfig['gradient'] }} border-2 {{ $severityConfig['border'] }} shadow-lg {{ $severityConfig['glow'] }} {{ $severityConfig['pulse'] }} transition-all duration-300">
                                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="{{ $severityConfig['icon'] }}" clip-rule="evenodd"/>
                                        </svg>
                                        {{ ucfirst($group['severity']) }}
                                    </span>
                                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        {{ $group['category'] }}
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Issue Information -->
                            <div class="flex-1 min-w-0 space-y-3">
                                <!-- Title -->
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors duration-300">
                                    {{ $group['title'] }}
                                </h3>
                                
                                <!-- Description -->
                                @if(!empty($group['description']))
                                <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed max-w-3xl">
                                    {{ $group['description'] }}
                                </p>
                                @endif
                                
                                <!-- Rule and Suggestion Tags -->
                                <div class="flex flex-wrap gap-2">
                                    @if(!empty($group['rule']))
                                    <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900/50 dark:text-indigo-200 border border-indigo-200 dark:border-indigo-700">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Rule: {{ $group['rule'] }}
                                    </span>
                                    @endif
                                    
                                    @if(!empty($group['suggestion']))
                                    <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-200 border border-green-200 dark:border-green-700">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                                        </svg>
                                        Suggestion: {{ Str::limit($group['suggestion'], 50) }}
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <!-- Issue Progress Indicator -->
                        <div class="flex-shrink-0">
                            @php
                                $totalIssues = $group['count'] ?? 0;
                                $fixedIssues = ($group['resolved_count'] ?? 0);
                                $progressPercentage = $totalIssues > 0 ? round(($fixedIssues / $totalIssues) * 100) : 0;
                            @endphp
                            
                            <div class="text-center space-y-2">
                                <!-- Progress Circle -->
                                <div class="relative w-16 h-16">
                                    <svg class="w-16 h-16 transform -rotate-90" viewBox="0 0 64 64">
                                        <circle cx="32" cy="32" r="28" stroke="currentColor" stroke-width="4" fill="none" class="text-gray-200 dark:text-gray-700"/>
                                        <circle cx="32" cy="32" r="28" stroke="currentColor" stroke-width="4" fill="none" 
                                                class="{{ $progressPercentage >= 100 ? 'text-green-500' : ($progressPercentage >= 50 ? 'text-yellow-500' : 'text-red-500') }}"
                                                stroke-dasharray="175.93" 
                                                stroke-dashoffset="{{ 175.93 - (175.93 * $progressPercentage / 100) }}"
                                                stroke-linecap="round"/>
                                    </svg>
                                    <div class="absolute inset-0 flex items-center justify-center">
                                        <span class="text-xs font-bold text-gray-700 dark:text-gray-300">{{ $progressPercentage }}%</span>
                                    </div>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">
                                    {{ $fixedIssues }}/{{ $totalIssues }} fixed
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Affected Files Section -->
                <div class="border-t border-gray-200 dark:border-gray-600">
                    <div class="px-6 py-5">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                                <svg class="w-6 h-6 mr-3 text-indigo-500 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"/>
                                </svg>
                                Affected Files
                                <span class="ml-2 inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200">
                                    {{ $group['files_count'] }}
                                </span>
                            </h4>
                            <!-- View Details Button -->
                            @php
                                $routeParams = [
                                    'scan' => $scan->id,
                                    'title' => urlencode($group['title']),
                                    'category' => $group['category'],
                                    'severity' => $group['severity'],
                                    'description' => urlencode($group['description'] ?? ''),
                                    'rule' => urlencode($group['rule'] ?? ''),
                                    'suggestion' => urlencode($group['suggestion'] ?? ''),
                                ];
                                
                                // Enhanced route generation with better debugging
                                $detailsUrl = '#';
                                
                                try {
                                    // Try the exact route name first
                                    if (Route::has('codesnoutr.scan-results.group-details')) {
                                        $detailsUrl = route('codesnoutr.scan-results.group-details', $routeParams);
                                    } elseif (Route::has(app()->getLocale() . '.codesnoutr.scan-results.group-details')) {
                                        $detailsUrl = route(app()->getLocale() . '.codesnoutr.scan-results.group-details', $routeParams);
                                    } else {
                                        // Manual URL construction as fallback
                                        $baseUrl = rtrim(config('app.url') ?: request()->getSchemeAndHttpHost(), '/');
                                        $detailsUrl = "{$baseUrl}/codesnoutr/scan-results/{$scan->id}/group/" . 
                                                     urlencode($group['title']) . '/' . $group['category'] . '/' . $group['severity'];
                                        
                                        // Add query parameters for additional data
                                        $queryParams = [];
                                        if (!empty($group['description'])) {
                                            $queryParams['description'] = urlencode($group['description']);
                                        }
                                        if (!empty($group['rule'])) {
                                            $queryParams['rule'] = urlencode($group['rule']);
                                        }
                                        if (!empty($group['suggestion'])) {
                                            $queryParams['suggestion'] = urlencode($group['suggestion']);
                                        }
                                        
                                        if (!empty($queryParams)) {
                                            $detailsUrl .= '?' . http_build_query($queryParams);
                                        }
                                    }
                                } catch (\Exception $e) {
                                    // Fallback to manual URL construction
                                    $baseUrl = rtrim(config('app.url') ?: request()->getSchemeAndHttpHost(), '/');
                                    $detailsUrl = "{$baseUrl}/codesnoutr/scan-results/{$scan->id}/group/" . 
                                                 urlencode($group['title']) . '/' . $group['category'] . '/' . $group['severity'];
                                }
                            @endphp
                            
                            <div class="flex items-center space-x-3">
                                <!-- Stats Quick View -->
                                <div class="flex items-center text-sm text-gray-600 dark:text-gray-400 space-x-4">
                                    <span class="flex items-center">
                                        <svg class="w-4 h-4 mr-1 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        {{ $group['files_count'] }} {{ Str::plural('file', $group['files_count']) }}
                                    </span>
                                    <span class="flex items-center">
                                        <svg class="w-4 h-4 mr-1 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                                        </svg>
                                        {{ $group['count'] }} {{ Str::plural('issue', $group['count']) }}
                                    </span>
                                </div>
                                
                                <!-- Enhanced View Details Button -->
                                <a href="{{ $detailsUrl }}"
                                   class="group inline-flex items-center px-4 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 rounded-lg shadow-lg hover:shadow-xl hover:shadow-indigo-500/30 border border-indigo-500 hover:border-indigo-400 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                                    <svg class="w-4 h-4 mr-2 transition-transform group-hover:rotate-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    View Details
                                    <svg class="w-4 h-4 ml-2 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                        
                        <!-- Enhanced File List Preview -->
                        <div class="space-y-3">
                            @foreach($group['files']->take(3) as $index => $file)
                            <div class="group relative flex items-center p-4 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-700/30 dark:to-gray-800/30 rounded-xl border border-gray-200 dark:border-gray-600 hover:border-indigo-300 dark:hover:border-indigo-500 hover:shadow-lg hover:shadow-indigo-500/10 dark:hover:shadow-indigo-400/10 transition-all duration-300">
                                <!-- File Icon with Type Detection -->
                                <div class="flex-shrink-0 mr-4">
                                    @php
                                        $extension = pathinfo($file['file_path'], PATHINFO_EXTENSION);
                                        $iconConfig = match($extension) {
                                            'php' => ['bg' => 'from-indigo-500 to-purple-600', 'icon' => 'M12 18.5l-3-3 3-3M18 18.5l3-3-3-3'],
                                            'js', 'ts' => ['bg' => 'from-yellow-400 to-orange-500', 'icon' => 'M4 6h16M4 12h16M4 18h16'],
                                            'css', 'scss' => ['bg' => 'from-blue-400 to-cyan-500', 'icon' => 'M7 21h10M12 3v18M5 7l2-2M19 7l-2-2'],
                                            'html', 'blade' => ['bg' => 'from-red-400 to-pink-500', 'icon' => 'M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4'],
                                            'json', 'xml' => ['bg' => 'from-green-400 to-emerald-500', 'icon' => 'M8 12l4 4 4-4m0-6l-4 4-4-4'],
                                            default => ['bg' => 'from-gray-500 to-gray-600', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z']
                                        };
                                    @endphp
                                    <div class="w-12 h-12 bg-gradient-to-br {{ $iconConfig['bg'] }} rounded-xl flex items-center justify-center shadow-lg transition-all duration-300">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconConfig['icon'] }}"/>
                                        </svg>
                                    </div>
                                </div>
                                
                                <!-- File Information -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1 min-w-0 space-y-1">
                                            <!-- File Name -->
                                            <div class="flex items-center">
                                                <h5 class="text-sm font-semibold text-gray-900 dark:text-white font-mono group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                                                    {{ basename($file['file_path']) }}
                                                </h5>
                                                @if($extension)
                                                <span class="ml-2 inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                                    .{{ $extension }}
                                                </span>
                                                @endif
                                            </div>
                                            
                                            <!-- File Path -->
                                            <p class="text-xs text-gray-500 dark:text-gray-400 font-mono truncate max-w-md" title="{{ $file['file_path'] }}">
                                                {{ $file['file_path'] }}
                                            </p>
                                            
                                            <!-- Issue Description Preview -->
                                            @if(!empty($file['description']))
                                            <p class="text-xs text-gray-600 dark:text-gray-300 line-clamp-1">
                                                {{ Str::limit($file['description'], 80) }}
                                            </p>
                                            @endif
                                        </div>
                                        
                                        <!-- Issue Details & Actions -->
                                        <div class="flex items-center space-x-2 ml-4">
                                            <!-- Line Number -->
                                            @if($file['line_number'])
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-200 border border-amber-200 dark:border-amber-700">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16"/>
                                                </svg>
                                                Line {{ $file['line_number'] }}
                                            </span>
                                            @endif
                                            
                                            <!-- Issue Status -->
                                            @if($file['fixed'])
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-200 border border-green-200 dark:border-green-700">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                                {{ $file['fix_method'] === 'ignored' ? 'Ignored' : 'Fixed' }}
                                            </span>
                                            @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-200 border border-red-200 dark:border-red-700">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                Pending
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Hover Arrow -->
                                <div class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 ml-2">
                                    <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </div>
                            </div>
                            @endforeach

                            <!-- "Show More" Indicator -->
                            @if($group['files']->count() > 3)
                            <div class="text-center py-4">
                                <div class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-600 rounded-lg border border-gray-300 dark:border-gray-500">
                                    <svg class="w-4 h-4 mr-2 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                    </svg>
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                        And {{ $group['files']->count() - 3 }} more {{ Str::plural('file', $group['files']->count() - 3) }}...
                                    </span>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            @endforeach
        </div>
    @elseif($viewMode === 'file-grouped' && $fileGroupedIssues && $fileGroupedIssues->count() > 0)
        <!-- File-Grouped View with Lazy Loading -->
        <div class="space-y-6" wire:loading.class="opacity-50">
            @foreach($fileGroupedIssues as $fileGroup)
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-2xl hover:border-indigo-400 dark:hover:border-indigo-500 hover:shadow-indigo-500/20 dark:hover:shadow-indigo-400/20 transition-all duration-300">
                <!-- File Header -->
                <div class="px-6 py-5 bg-gradient-to-r from-gray-50 via-white to-gray-50 dark:from-gray-700 dark:via-gray-800 dark:to-gray-700 border-b border-gray-200 dark:border-gray-600">
                    <div class="flex items-start justify-between">
                        <div class="flex items-start space-x-4">
                            <!-- File Icon -->
                            <div class="flex-shrink-0">
                                @php
                                    $extension = $fileGroup['file_extension'] ?? '';
                                    $iconConfig = match($extension) {
                                        'php' => ['bg' => 'from-indigo-500 to-purple-600', 'icon' => 'M12 18.5l-3-3 3-3M18 18.5l3-3-3-3'],
                                        'js', 'ts' => ['bg' => 'from-yellow-400 to-orange-500', 'icon' => 'M4 6h16M4 12h16M4 18h16'],
                                        'css', 'scss' => ['bg' => 'from-blue-400 to-cyan-500', 'icon' => 'M7 21h10M12 3v18M5 7l2-2M19 7l-2-2'],
                                        'html', 'blade' => ['bg' => 'from-red-400 to-pink-500', 'icon' => 'M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4'],
                                        'json', 'xml' => ['bg' => 'from-green-400 to-emerald-500', 'icon' => 'M8 12l4 4 4-4m0-6l-4 4-4-4'],
                                        default => ['bg' => 'from-gray-500 to-gray-600', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z']
                                    };
                                @endphp
                                <div class="w-16 h-16 bg-gradient-to-br {{ $iconConfig['bg'] }} rounded-xl flex items-center justify-center shadow-lg">
                                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconConfig['icon'] }}"/>
                                    </svg>
                                </div>
                            </div>
                            
                            <!-- File Information -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center space-x-3 mb-2">
                                    <h3 class="text-xl font-bold text-gray-900 dark:text-white font-mono">
                                        {{ $fileGroup['file_name'] }}
                                    </h3>
                                    @if($fileGroup['file_extension'])
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                        .{{ $fileGroup['file_extension'] }}
                                    </span>
                                    @endif
                                    
                                    <!-- Severity Badge -->
                                    @php
                                        $severityConfig = match($fileGroup['highest_severity']) {
                                            'critical' => ['gradient' => 'from-red-500 to-red-600', 'text' => 'Critical'],
                                            'high' => ['gradient' => 'from-orange-500 to-orange-600', 'text' => 'High'],
                                            'medium' => ['gradient' => 'from-yellow-500 to-amber-600', 'text' => 'Medium'],
                                            'low' => ['gradient' => 'from-blue-500 to-blue-600', 'text' => 'Low'],
                                            default => ['gradient' => 'from-gray-500 to-gray-600', 'text' => 'Info']
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-3 py-1 rounded-lg text-sm font-bold text-white bg-gradient-to-r {{ $severityConfig['gradient'] }}">
                                        {{ $severityConfig['text'] }}
                                    </span>
                                </div>
                                
                                <p class="text-sm text-gray-600 dark:text-gray-400 font-mono mb-3 truncate" title="{{ $fileGroup['file_path'] }}">
                                    {{ $fileGroup['file_path'] }}
                                </p>
                                
                                <!-- Stats Row -->
                                <div class="flex items-center space-x-6 text-sm">
                                    <div class="flex items-center space-x-2">
                                        <div class="w-2 h-2 bg-red-500 rounded-full"></div>
                                        <span class="text-gray-700 dark:text-gray-300">
                                            <span class="font-semibold">{{ $fileGroup['total_issues'] }}</span> {{ Str::plural('issue', $fileGroup['total_issues']) }}
                                        </span>
                                    </div>
                                    
                                    @if($fileGroup['resolved_issues'] > 0)
                                    <div class="flex items-center space-x-2">
                                        <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                                        <span class="text-gray-700 dark:text-gray-300">
                                            <span class="font-semibold">{{ $fileGroup['resolved_issues'] }}</span> resolved
                                        </span>
                                    </div>
                                    @endif
                                    
                                    @if($fileGroup['pending_issues'] > 0)
                                    <div class="flex items-center space-x-2">
                                        <div class="w-2 h-2 bg-yellow-500 rounded-full"></div>
                                        <span class="text-gray-700 dark:text-gray-300">
                                            <span class="font-semibold">{{ $fileGroup['pending_issues'] }}</span> pending
                                        </span>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <!-- Progress Circle and Actions -->
                        <div class="flex items-center space-x-4">
                            <!-- Progress Circle -->
                            <div class="flex-shrink-0">
                                @php
                                    $progressPercentage = $fileGroup['total_issues'] > 0 ? round(($fileGroup['resolved_issues'] / $fileGroup['total_issues']) * 100) : 0;
                                @endphp
                                <div class="text-center space-y-2">
                                    <div class="relative w-16 h-16">
                                        <svg class="w-16 h-16 transform -rotate-90" viewBox="0 0 64 64">
                                            <circle cx="32" cy="32" r="28" stroke="currentColor" stroke-width="4" fill="none" class="text-gray-200 dark:text-gray-700"/>
                                            <circle cx="32" cy="32" r="28" stroke="currentColor" stroke-width="4" fill="none" 
                                                    class="{{ $progressPercentage >= 100 ? 'text-green-500' : ($progressPercentage >= 50 ? 'text-yellow-500' : 'text-red-500') }}"
                                                    stroke-dasharray="175.93" 
                                                    stroke-dashoffset="{{ 175.93 - (175.93 * $progressPercentage / 100) }}"
                                                    stroke-linecap="round"/>
                                        </svg>
                                        <div class="absolute inset-0 flex items-center justify-center">
                                            <span class="text-xs font-bold text-gray-700 dark:text-gray-300">{{ $progressPercentage }}%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Expand/Collapse Button -->
                            <div class="flex-shrink-0">
                                <button wire:click="toggleFileExpansion('{{ $fileGroup['file_path'] }}')"
                                        class="inline-flex items-center px-4 py-2 text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 rounded-lg shadow-lg hover:shadow-xl hover:shadow-indigo-500/30 border border-indigo-500 hover:border-indigo-400 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                                    @if($this->isFileLoading($fileGroup['file_path']))
                                        <svg class="w-4 h-4 mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Loading...
                                    @elseif($this->isFileExpanded($fileGroup['file_path']))
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                        </svg>
                                        Hide Issues
                                    @else
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        View Issues
                                    @endif
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Issues List (Loaded on Demand) -->
                @if($this->isFileExpanded($fileGroup['file_path']))
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    @php
                        $loadedIssueGroups = $this->getLoadedFileIssueGroups($fileGroup['file_path']);
                    @endphp
                    
                    @if($loadedIssueGroups->count() > 0)
                        @foreach($loadedIssueGroups as $issueGroup)
                        <div class="p-6 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <!-- Issue Group Header -->
                            <div class="flex items-start space-x-4 mb-4">
                                <!-- Issue Severity -->
                                <div class="flex-shrink-0">
                                    @php
                                        $severityConfig = match($issueGroup['severity_name']) {
                                            'critical' => ['color' => 'text-red-600 dark:text-red-400', 'bg' => 'bg-red-100 dark:bg-red-900/20', 'icon' => 'M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z'],
                                            'high' => ['color' => 'text-orange-600 dark:text-orange-400', 'bg' => 'bg-orange-100 dark:bg-orange-900/20', 'icon' => 'M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z'],
                                            'medium' => ['color' => 'text-yellow-600 dark:text-yellow-400', 'bg' => 'bg-yellow-100 dark:bg-yellow-900/20', 'icon' => 'M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z'],
                                            'low' => ['color' => 'text-blue-600 dark:text-blue-400', 'bg' => 'bg-blue-100 dark:bg-blue-900/20', 'icon' => 'M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z'],
                                            default => ['color' => 'text-gray-600 dark:text-gray-400', 'bg' => 'bg-gray-100 dark:bg-gray-900/20', 'icon' => 'M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z']
                                        };
                                    @endphp
                                    <div class="w-12 h-12 {{ $severityConfig['bg'] }} rounded-lg flex items-center justify-center">
                                        <svg class="w-6 h-6 {{ $severityConfig['color'] }}" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="{{ $severityConfig['icon'] }}" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                </div>
                                
                                <!-- Issue Group Information -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1 min-w-0 space-y-3">
                                            <!-- Issue Title -->
                                            <h4 class="text-xl font-bold text-gray-900 dark:text-white">
                                                {{ $issueGroup['title'] }}
                                            </h4>
                                            
                                            <!-- Issue Description -->
                                            @if($issueGroup['description'])
                                            <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed">
                                                {{ $issueGroup['description'] }}
                                            </p>
                                            @endif
                                            
                                            <!-- Stats and Meta -->
                                            <div class="flex flex-wrap gap-3 items-center">
                                                <!-- Occurrences Count -->
                                                <div class="flex items-center space-x-2">
                                                    <div class="w-2 h-2 bg-red-500 rounded-full"></div>
                                                    <span class="text-sm text-gray-700 dark:text-gray-300">
                                                        <span class="font-semibold">{{ $issueGroup['total_occurrences'] }}</span> {{ Str::plural('occurrence', $issueGroup['total_occurrences']) }}
                                                    </span>
                                                </div>
                                                
                                                @if($issueGroup['resolved_occurrences'] > 0)
                                                <div class="flex items-center space-x-2">
                                                    <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                                                    <span class="text-sm text-gray-700 dark:text-gray-300">
                                                        <span class="font-semibold">{{ $issueGroup['resolved_occurrences'] }}</span> resolved
                                                    </span>
                                                </div>
                                                @endif
                                                
                                                <!-- Category -->
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900/50 dark:text-indigo-200">
                                                    {{ ucfirst($issueGroup['category']) }}
                                                </span>
                                                
                                                <!-- Rule ID -->
                                                @if($issueGroup['rule_id'])
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900/50 dark:text-purple-200">
                                                    {{ $issueGroup['rule_id'] }}
                                                </span>
                                                @endif
                                            </div>
                                            
                                            <!-- Suggestion -->
                                            @if($issueGroup['suggestion'])
                                            <div class="mt-3 p-3 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800">
                                                <div class="flex items-start space-x-2">
                                                    <svg class="w-4 h-4 text-green-600 dark:text-green-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                                                    </svg>
                                                    <div class="flex-1">
                                                        <p class="text-sm text-green-800 dark:text-green-200 font-medium">Suggestion:</p>
                                                        <p class="text-sm text-green-700 dark:text-green-300 mt-1">{{ $issueGroup['suggestion'] }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Issue Instances with Code -->
                            <div class="space-y-4 ml-16 max-h-[600px] overflow-y-auto">
                                @foreach($issueGroup['instances'] as $instance)
                                <div class="border border-gray-200 dark:border-gray-600 rounded-lg overflow-hidden hover:shadow-2xl hover:border-indigo-400 dark:hover:border-indigo-500 hover:shadow-indigo-500/20 dark:hover:shadow-indigo-400/20 transition-all duration-500 {{ $instance['fixed'] ? 'bg-green-50 dark:bg-green-900/10' : 'bg-white dark:bg-gray-800' }}">
                                    <!-- Instance Header -->
                                    <div class="px-4 py-3 bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600 flex items-center justify-between">
                                        <div class="flex items-center space-x-3">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-200">
                                                Line {{ $instance['line_number'] }}
                                                @if($instance['column_number'])
                                                    :{{ $instance['column_number'] }}
                                                @endif
                                            </span>
                                            
                                            <!-- Status -->
                                            @if($instance['fixed'])
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-200">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                                {{ $instance['fix_method'] === 'ignored' ? 'Ignored' : 'Fixed' }}
                                            </span>
                                            @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-200">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                Pending
                                            </span>
                                            @endif
                                        </div>
                                        
                                        <!-- Actions -->
                                        @if(!$instance['fixed'])
                                        <div class="flex items-center space-x-1">
                                            <button wire:click="resolveIssue({{ $instance['id'] }})" 
                                                    class="p-1.5 text-green-600 hover:text-green-800 hover:bg-green-100 dark:hover:bg-green-900/20 rounded-lg transition-colors"
                                                    title="Mark as Resolved">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            </button>
                                            <button wire:click="markAsIgnored({{ $instance['id'] }})" 
                                                    class="p-1.5 text-yellow-600 hover:text-yellow-800 hover:bg-yellow-100 dark:hover:bg-yellow-900/20 rounded-lg transition-colors"
                                                    title="Mark as Ignored">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"/>
                                                </svg>
                                            </button>
                                            <button wire:click="markAsFalsePositive({{ $instance['id'] }})" 
                                                    class="p-1.5 text-red-600 hover:text-red-800 hover:bg-red-100 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                                                    title="Mark as False Positive">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </div>
                                        @endif
                                    </div>
                                    
                                    <!-- Code Snippet -->
                                    @if(is_array($instance['code_snippet']))
                                    <div class="p-0">
                                        <div class="bg-gray-900 text-gray-100 font-mono text-sm overflow-x-auto">
                                            @foreach($instance['code_snippet'] as $line)
                                            <div class="flex {{ $line['is_target'] ? 'bg-red-900/30 border-l-4 border-red-500' : '' }}">
                                                <div class="px-3 py-1 text-gray-500 text-right min-w-[3rem] select-none border-r border-gray-700">
                                                    {{ $line['number'] }}
                                                </div>
                                                <div class="px-3 py-1 flex-1">
                                                    <pre class="whitespace-pre-wrap">{{ $line['content'] }}</pre>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    @else
                                    <div class="p-4">
                                        <div class="bg-gray-100 dark:bg-gray-700 rounded-lg p-3 font-mono text-sm">
                                            <pre class="whitespace-pre-wrap">{{ $instance['code_snippet'] }}</pre>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                        
                        <!-- Load More Issues Button -->
                        @if($this->fileHasMorePages($fileGroup['file_path']))
                        <div class="p-6 border-t border-gray-200 dark:border-gray-700">
                            <div class="text-center">
                                <button wire:click="loadMoreFileIssues('{{ $fileGroup['file_path'] }}')"
                                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors"
                                        wire:loading.attr="disabled"
                                        wire:target="loadMoreFileIssues('{{ $fileGroup['file_path'] }}')">
                                    <span wire:loading.remove wire:target="loadMoreFileIssues('{{ $fileGroup['file_path'] }}')">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                        </svg>
                                        Load More Issues
                                    </span>
                                    <span wire:loading wire:target="loadMoreFileIssues('{{ $fileGroup['file_path'] }}')">
                                        <svg class="w-4 h-4 mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Loading...
                                    </span>
                                </button>
                            </div>
                        </div>
                        @endif
                    @else
                    <div class="p-6 text-center">
                        <div wire:loading wire:target="toggleFileExpansion('{{ $fileGroup['file_path'] }}')">
                            <div class="flex items-center justify-center space-x-2">
                                <svg class="w-5 h-5 animate-spin text-indigo-600" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span class="text-sm text-gray-600 dark:text-gray-400">Loading issues...</span>
                            </div>
                        </div>
                        <div wire:loading.remove wire:target="toggleFileExpansion('{{ $fileGroup['file_path'] }}')">
                            <p class="text-sm text-gray-500 dark:text-gray-400">No issues to display</p>
                        </div>
                    </div>
                    @endif
                </div>
                @endif
            </div>
            @endforeach
            
            <!-- Load More File Groups Button -->
            @if($this->hasMoreFileGroups())
            <div class="mt-8 text-center">
                <button wire:click="loadMoreFileGroups"
                        class="inline-flex items-center px-6 py-3 bg-indigo-600 border border-transparent rounded-lg shadow-sm text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors"
                        wire:loading.attr="disabled"
                        wire:target="loadMoreFileGroups">
                    <span class="inline-flex" wire:loading.remove wire:target="loadMoreFileGroups">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Load More Files
                    </span>
                    <span class="inline-flex" wire:loading wire:target="loadMoreFileGroups">
                        <svg class="w-5 h-5 mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Loading...
                    </span>
                </button>
            </div>
            @endif
        </div>
    @elseif($viewMode === 'detailed' && $issues && $issues->count() > 0)
        <!-- Detailed Table View -->
        @include('codesnoutr::components.scan-results.detailed-table')
    @else
        <!-- Empty State -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center hover:shadow-lg hover:border-gray-300 dark:hover:border-gray-600 hover:shadow-gray-500/10 dark:hover:shadow-gray-400/10 transition-all duration-300">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 48 48">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">
                @if($searchTerm || $selectedSeverity !== 'all' || $selectedCategory !== 'all' || $selectedFile !== 'all')
                    No issues match your filters
                @else
                    No issues found
                @endif
            </h3>
            <p class="mt-2 text-gray-500 dark:text-gray-400">
                @if($searchTerm || $selectedSeverity !== 'all' || $selectedCategory !== 'all' || $selectedFile !== 'all')
                    Try adjusting your search criteria or filters.
                @else
                    This scan completed successfully with no issues detected.
                @endif
            </p>
            @if($searchTerm || $selectedSeverity !== 'all' || $selectedCategory !== 'all' || $selectedFile !== 'all')
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
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center hover:shadow-lg hover:border-gray-300 dark:hover:border-gray-600 hover:shadow-gray-500/10 dark:hover:shadow-gray-400/10 transition-all duration-300">
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

<script>
document.addEventListener('livewire:init', () => {
    // Listen for redirect to scan results event
    Livewire.on('redirect-to-scan-results', (event) => {
        if (event.scanId) {
            // Small delay to allow the UI to update before redirect
            setTimeout(() => {
                // Redirect to the main scan results page for this scan
                const currentUrl = window.location.href;
                const baseUrl = currentUrl.split('?')[0]; // Remove query parameters
                window.location.href = baseUrl; // Reload the page without filters
            }, 500);
        } else {
            // Fallback: redirect to dashboard if no scan ID
            window.location.href = '{{ route("codesnoutr.dashboard") }}';
        }
    });
});
</script>

