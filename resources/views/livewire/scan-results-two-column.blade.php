<div>
@if($scan)
<div class="h-screen flex flex-col" id="codesnoutr-scan-results-container" wire:key="scan-results-{{ $scanId }}">

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

    <!-- Header Section -->
    <div class="flex-shrink-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-6 py-4">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
            <div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('codesnoutr.results') }}" 
                       class="inline-flex items-center text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Back to Results
                    </a>
                    <span class="text-gray-300 dark:text-gray-600">/</span>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ $this->getScanDisplayTitle() }}</h1>
                </div>
                @if($scan)
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    {{ ucfirst($scan->type) }} scan of 
                    @php
                        $scanTarget = 'full codebase';
                        
                        // Try to get a meaningful scan target
                        if ($scan->target) {
                            $scanTarget = $scan->target;
                        } elseif ($scan->paths_scanned && is_array($scan->paths_scanned) && count($scan->paths_scanned) > 0) {
                            if (count($scan->paths_scanned) === 1) {
                                $scanTarget = $scan->paths_scanned[0];
                            } else {
                                $scanTarget = count($scan->paths_scanned) . ' directories/files';
                            }
                        }
                        
                        // For display, show just the directory/file name if it's a path
                        if (str_contains($scanTarget, '/') && $scanTarget !== 'full codebase') {
                            $scanTarget = basename(rtrim($scanTarget, '/')) ?: dirname($scanTarget);
                        }
                    @endphp
                    <strong>{{ $scanTarget }}</strong>
                    • {{ $scan->created_at->format('M j, Y g:i A') }}
                    • {{ number_format($stats['total']) }} {{ Str::plural('issue', $stats['total']) }} found
                </p>
                @endif
            </div>
            
            <!-- Quick Stats -->
            <div class="mt-4 lg:mt-0 flex items-center space-x-6 text-sm">
                <div class="flex items-center space-x-2">
                    <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                    <span class="text-gray-700 dark:text-gray-300">{{ number_format($stats['total']) }} Total</span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                    <span class="text-gray-700 dark:text-gray-300">{{ number_format($stats['resolved_count']) }} Resolved</span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                    <span class="text-gray-700 dark:text-gray-300">{{ $directoryStats['affected_files'] ?? 0 }} Files</span>
                </div>
            </div>
        </div>
        
        <!-- Search and Filters -->
        <div class="mt-4 flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-3 sm:space-y-0 sm:space-x-4">
            <!-- Search -->
            <div class="flex-1 max-w-md">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" 
                           wire:model.live.debounce.300ms="searchTerm" 
                           placeholder="Search files and issues..."
                           class="pl-10 pr-4 py-2 w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" />
                </div>
            </div>
            
            <!-- Filters -->
            <div class="flex items-center space-x-3">
                <select wire:model.live="selectedSeverity" 
                        class="py-2 px-3 rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    @foreach($severityOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                
                <select wire:model.live="selectedCategory" 
                        class="py-2 px-3 rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    @foreach($categoryOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                
                @if($searchTerm || $selectedSeverity !== 'all' || $selectedCategory !== 'all')
                <button wire:click="clearFilters" 
                        class="px-3 py-2 text-sm bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                    Clear
                </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Two-Column Layout -->
    <div class="flex-1 flex overflow-scroll">
        <!-- Left Column: File Tree -->
        <div class="w-80 min-h-screen h-full flex-shrink-0 bg-gray-100 dark:bg-gray-600 border-r border-gray-200 dark:border-gray-700 overflow-hidden flex flex-col">
            <!-- File Tree Header -->
            <div class="flex-shrink-0 px-4 py-3 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center">
                        <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"/>
                        </svg>
                        Files with Issues
                    </h3>
                    <span class="text-xs text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded-full">
                        {{ $directoryStats['affected_files'] ?? 0 }}
                    </span>
                </div>
                
                <!-- Fix All Issues in Scan CTA -->
                @if($this->isAiAvailable() && ($stats['total'] - $stats['resolved_count']) > 0)
                <div class="mt-2">
                    <button wire:click="fixAllIssuesInScan"
                            wire:loading.attr="disabled"
                            wire:target="fixAllIssuesInScan"
                            class="w-full flex items-center justify-center px-3 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white text-xs font-medium rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        <span wire:loading.remove wire:target="fixAllIssuesInScan">Fix All Issues</span>
                        <span wire:loading wire:target="fixAllIssuesInScan" class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-1 h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Fixing...
                        </span>
                    </button>
                </div>
                @endif
            </div>
            

            
            <!-- File Tree Content -->
            <div class="flex-1 overflow-y-auto">

                @if($paginatedDirectoryTree && count($paginatedDirectoryTree) > 0)
                    @foreach($paginatedDirectoryTree as $directory => $files)
                    @if(is_array($files) && count($files) > 0)
                    @php $isExpanded = $this->isDirectoryExpanded($directory); @endphp
                    <div class="border-b border-gray-200 dark:border-gray-700 last:border-b-0">
                        <!-- Directory Header -->
                        <div class="px-4 py-2 bg-gray-100 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex items-center justify-between">
                                <button wire:click="toggleDirectory({{ json_encode($directory) }})"
                                        class="flex items-center space-x-2 min-w-0 flex-1 text-left hover:bg-gray-200 dark:hover:bg-gray-700 rounded px-2 py-1 transition-colors"
                                        data-directory="{{ $directory }}">
                                    <svg class="w-4 h-4 text-yellow-600 dark:text-yellow-400 flex-shrink-0 transition-transform {{ $isExpanded ? 'rotate-90' : '' }}" 
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                    <svg class="w-4 h-4 text-blue-600 dark:text-blue-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"/>
                                    </svg>
                                    <div class="min-w-0 flex-1">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                            {{ $this->getDirectoryDisplayName($directory) }}
                                        </div>
                                        @php $relativePath = $this->getDirectoryRelativePath($directory); @endphp
                                        @if(!empty($relativePath) && $relativePath !== '/')
                                        <div class="flex items-center text-xs text-gray-500 dark:text-gray-400 mt-0.5 space-x-1">
                                            <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                                            </svg>
                                            <span class="truncate font-mono text-indigo-600 dark:text-indigo-400">{{ $relativePath }}</span>
                                        </div>
                                        @endif
                                    </div>
                                </button>
                                
                                <div class="flex items-center space-x-2 flex-shrink-0">
                                    <span class="text-xs text-gray-500 dark:text-gray-400 bg-gray-200 dark:bg-gray-600 px-2 py-0.5 rounded-full">
                                        {{ count($files) }}
                                    </span>
                                    
                                    <!-- Fix All Issues in Directory -->
                                    @if($this->isAiAvailable())
                                    <button wire:click="fixAllIssuesInDirectory({{ json_encode($directory) }})"
                                            wire:loading.attr="disabled"
                                            wire:target="fixAllIssuesInDirectory"
                                            title="Fix all issues in this directory with AI"
                                            class="p-1 text-blue-600 hover:text-blue-800 hover:bg-blue-100 dark:hover:bg-blue-900/20 rounded transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                        </svg>
                                    </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <!-- Files in Directory -->
                        @if($isExpanded)
                        <div class="space-y-0">
                            @foreach($files as $file)
                            <button wire:click="selectFile({{ json_encode($file['path']) }})"
                                    class="w-full text-left px-6 py-3 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors border-b border-gray-100 dark:border-gray-800 last:border-b-0 {{ $selectedFilePath === $file['path'] ? 'bg-indigo-50 dark:bg-indigo-900/20 border-l-4 border-indigo-500' : '' }}">
                                <div class="flex items-start justify-between">
                                    <div class="flex items-start space-x-3 min-w-0 flex-1">
                                        <!-- File Icon -->
                                        <div class="flex-shrink-0 mt-0.5">
                                            @php
                                                $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                                                $iconConfig = match($extension) {
                                                    'php' => ['text' => 'text-indigo-600 dark:text-indigo-400', 'bg' => 'bg-indigo-100 dark:bg-indigo-900/30'],
                                                    'js', 'ts' => ['text' => 'text-yellow-600 dark:text-yellow-400', 'bg' => 'bg-yellow-100 dark:bg-yellow-900/30'],
                                                    'css', 'scss' => ['text' => 'text-blue-600 dark:text-blue-400', 'bg' => 'bg-blue-100 dark:bg-blue-900/30'],
                                                    'html', 'blade' => ['text' => 'text-red-600 dark:text-red-400', 'bg' => 'bg-red-100 dark:bg-red-900/30'],
                                                    'json', 'xml' => ['text' => 'text-green-600 dark:text-green-400', 'bg' => 'bg-green-100 dark:bg-green-900/30'],
                                                    default => ['text' => 'text-gray-600 dark:text-gray-400', 'bg' => 'bg-gray-100 dark:bg-gray-900/30']
                                                };
                                            @endphp
                                            <div class="w-6 h-6 {{ $iconConfig['bg'] }} rounded flex items-center justify-center">
                                                <svg class="w-3 h-3 {{ $iconConfig['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                            </div>
                                        </div>
                                        
                                        <!-- File Info -->
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center space-x-2">
                                                <span class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                                    {{ $file['name'] }}
                                                </span>
                                                @if($extension)
                                                <span class="flex-shrink-0 text-xs text-gray-500 dark:text-gray-400 bg-gray-200 dark:bg-gray-700 px-1.5 py-0.5 rounded">
                                                    .{{ $extension }}
                                                </span>
                                                @endif
                                            </div>
                                            <div class="mt-1 flex items-center space-x-4 text-xs text-gray-500 dark:text-gray-400">
                                                <span class="flex items-center space-x-1">
                                                    <div class="w-1.5 h-1.5 bg-red-500 rounded-full"></div>
                                                    <span>{{ $file['issues_count'] }} {{ Str::plural('issue', $file['issues_count']) }}</span>
                                                </span>
                                                @if($file['resolved_count'] > 0)
                                                <span class="flex items-center space-x-1">
                                                    <div class="w-1.5 h-1.5 bg-green-500 rounded-full"></div>
                                                    <span>{{ $file['resolved_count'] }} fixed</span>
                                                </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Severity Indicator -->
                                    <div class="flex-shrink-0 ml-2">
                                        @php
                                            $severityConfig = match($file['highest_severity']) {
                                                'critical' => ['bg' => 'bg-red-500', 'text' => 'Critical'],
                                                'high' => ['bg' => 'bg-orange-500', 'text' => 'High'],
                                                'medium' => ['bg' => 'bg-yellow-500', 'text' => 'Medium'],
                                                'low' => ['bg' => 'bg-blue-500', 'text' => 'Low'],
                                                default => ['bg' => 'bg-gray-500', 'text' => 'Info']
                                            };
                                        @endphp
                                        <div class="w-3 h-3 {{ $severityConfig['bg'] }} rounded-full" title="{{ $severityConfig['text'] }} Severity"></div>
                                    </div>
                                </div>
                            </button>
                            @endforeach
                        </div>
                        @endif
                    </div>
                    @endif
                    @endforeach
                @else
                <div class="p-6 text-center">
                    <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"/>
                    </svg>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No files with issues found</p>
                </div>
                @endif
                
                <!-- Directory Load More -->
                @if($paginatedDirectoryTree && count($paginatedDirectoryTree) > 0 && $currentDirectoryPage < $totalDirectoryPages)
                <div class="px-4 py-3 bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-center">
                        <button wire:click="nextDirectoryPage" 
                                class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all duration-200 flex items-center space-x-2"
                                wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="nextDirectoryPage">Load More Directories</span>
                            <span wire:loading wire:target="nextDirectoryPage" class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Loading...
                            </span>
                        </button>
                    </div>
                    <div class="text-center text-sm text-gray-500 dark:text-gray-400 mt-2">
                        Showing {{ $loadedDirectoryCount }} of {{ $totalDirectoryCount }} directories with files
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Right Column: Issue Details -->
        <div class="flex-1 flex flex-col overflow-hidden bg-white dark:bg-gray-800">
            @if($selectedFilePath)
                <!-- File Header -->
                <div class="flex-shrink-0 px-6 py-4 bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-start justify-between mb-3">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center space-x-3">
                                <!-- File Icon -->
                                @php
                                    $selectedFileExtension = pathinfo($selectedFilePath, PATHINFO_EXTENSION);
                                    $iconConfig = match($selectedFileExtension) {
                                        'php' => ['gradient' => 'from-indigo-500 to-purple-600'],
                                        'js', 'ts' => ['gradient' => 'from-yellow-400 to-orange-500'],
                                        'css', 'scss' => ['gradient' => 'from-blue-400 to-cyan-500'],
                                        'html', 'blade' => ['gradient' => 'from-red-400 to-pink-500'],
                                        'json', 'xml' => ['gradient' => 'from-green-400 to-emerald-500'],
                                        default => ['gradient' => 'from-gray-500 to-gray-600']
                                    };
                                @endphp
                                <div class="w-10 h-10 bg-gradient-to-br {{ $iconConfig['gradient'] }} rounded-lg flex items-center justify-center shadow-lg">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                
                                <div class="min-w-0">
                                    <h2 class="text-lg font-bold text-gray-900 dark:text-white font-mono">
                                        {{ basename($selectedFilePath) }}
                                    </h2>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 font-mono truncate" title="{{ $selectedFilePath }}">
                                        {{ $selectedFilePath }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- File Stats -->
                        <div class="flex items-center space-x-4 text-sm">
                            @if($selectedFileStats)
                            <div class="flex items-center space-x-2">
                                <div class="w-2 h-2 bg-red-500 rounded-full"></div>
                                <span class="text-gray-700 dark:text-gray-300">
                                    {{ $selectedFileStats['total_issues'] }} {{ Str::plural('issue', $selectedFileStats['total_issues']) }}
                                </span>
                            </div>
                            @if($selectedFileStats['resolved_issues'] > 0)
                            <div class="flex items-center space-x-2">
                                <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                                <span class="text-gray-700 dark:text-gray-300">
                                    {{ $selectedFileStats['resolved_issues'] }} resolved
                                </span>
                            </div>
                            @endif
                            @endif
                        </div>
                    </div>
                    
                    <!-- Fix All Issues in File CTA -->
                    @if($this->isAiAvailable() && $selectedFileStats && ($selectedFileStats['total_issues'] - $selectedFileStats['resolved_issues']) > 0)
                    <div class="flex items-center justify-between p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg">
                        <div class="flex items-center space-x-3">
                            <div class="flex-shrink-0">
                                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-blue-900 dark:text-blue-100">Fix All Issues in File</h4>
                                <p class="text-xs text-blue-700 dark:text-blue-300">{{ ($selectedFileStats['total_issues'] - $selectedFileStats['resolved_issues']) }} issues need fixing</p>
                            </div>
                        </div>
                        <button wire:click="fixAllIssuesInFile({{ json_encode($selectedFilePath) }})"
                                wire:loading.attr="disabled"
                                wire:target="fixAllIssuesInFile"
                                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                            <span wire:loading.remove wire:target="fixAllIssuesInFile">Fix All</span>
                            <span wire:loading wire:target="fixAllIssuesInFile" class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-1 h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Fixing...
                            </span>
                        </button>
                    </div>
                    @endif
                </div>
                
                <!-- Issues List -->
                <div class="flex-1 overflow-y-auto">
                    <!-- Pagination Controls - Top -->
                    @if($selectedFileIssues && $totalIssuePages > 1)
                    <div class="px-6 py-3 bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                Page {{ $currentIssuePage }} of {{ $totalIssuePages }} ({{ $selectedFileIssues->count() }} groups shown)
                            </div>
                            <div class="flex items-center space-x-2">
                                <button wire:click="previousIssuePage" 
                                        @if($currentIssuePage <= 1) disabled @endif
                                        class="px-3 py-1 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded text-sm disabled:opacity-50">
                                    Previous
                                </button>
                                <span class="text-sm text-gray-600 dark:text-gray-400">{{ $currentIssuePage }}</span>
                                <button wire:click="nextIssuePage" 
                                        @if($currentIssuePage >= $totalIssuePages) disabled @endif
                                        class="px-3 py-1 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded text-sm disabled:opacity-50">
                                    Next
                                </button>
                            </div>
                        </div>
                    </div>
                    @endif
                    <!-- Loading State -->
                    @if($fileLoading)
                    <div class="flex items-center justify-center p-8">
                        <div class="text-center">
                            <svg class="w-8 h-8 animate-spin text-indigo-600 mx-auto" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Loading file issues...</p>
                        </div>
                    </div>
                    @elseif($selectedFileIssues && $selectedFileIssues->count() > 0)
                        <div class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($selectedFileIssues as $issueGroup)
                            <div class="p-6 hover:bg-gray-50 dark:hover:bg-gray-900/50 transition-colors">
                                <!-- Issue Group Header -->
                                <div class="flex items-start space-x-4 mb-4">
                                    <!-- Severity Icon -->
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
                                    
                                    <!-- Issue Information -->
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1 min-w-0 space-y-2">
                                                <!-- Issue Title -->
                                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                                                    {{ $issueGroup['title'] }}
                                                </h3>
                                                
                                                <!-- Issue Description -->
                                                @if($issueGroup['description'])
                                                <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed">
                                                    {{ $issueGroup['description'] }}
                                                </p>
                                                @endif
                                                
                                                <!-- Meta Information -->
                                                <div class="flex flex-wrap gap-2 items-center">
                                                    <!-- Occurrences -->
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                                        {{ $issueGroup['total_occurrences'] }} {{ Str::plural('occurrence', $issueGroup['total_occurrences']) }}
                                                    </span>
                                                    
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
                                                    
                                                    <!-- Severity -->
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium {{ $severityConfig['bg'] }} {{ $severityConfig['color'] }}">
                                                        {{ ucfirst($issueGroup['severity_name']) }}
                                                    </span>
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

                                <!-- Issue Instances -->
                                <div class="space-y-3">
                                    @foreach($issueGroup['instances'] as $instance)
                                    <div class="border border-gray-200 dark:border-gray-600 rounded-lg overflow-hidden {{ $instance['fixed'] ? 'bg-green-50 dark:bg-green-900/10' : 'bg-white dark:bg-gray-800' }}">
                                        <!-- Instance Header -->
                                        <div class="flex flex-col w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                                            <div class="flex w-full items-center">
                                                <div class="flex items-center space-x-3">
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
                                                
                                                <!-- Issue Actions -->
                                                @if(!$instance['fixed'])
                                                <div class="flex items-center space-x-1">
                                                    <!-- AI Fix Action Button -->
                                                    @if($this->isAiConfigured() && !empty($instance['ai_fix']))
                                                        <button wire:click="applyAutoFix({{ $instance['id'] }})" 
                                                                wire:loading.attr="disabled"
                                                                wire:target="applyAutoFix({{ $instance['id'] }})"
                                                                class="p-1.5 text-blue-600 hover:text-blue-800 hover:bg-blue-100 dark:hover:bg-blue-900/20 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                                                title="Apply AI Fix">
                                                            <svg wire:loading.remove wire:target="applyAutoFix({{ $instance['id'] }})" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                                            </svg>
                                                            <svg wire:loading wire:target="applyAutoFix({{ $instance['id'] }})" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                            </svg>
                                                        </button>
                                                    @endif
                                                    
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
                                            <div class="overflow-hidden">
                                                <div class="bg-gray-900 text-gray-100 text-sm overflow-x-auto max-h-96 border border-gray-700">
                                                    @foreach($instance['code_snippet'] as $line)
                                                    <div class="flex hover:bg-gray-800/50 {{ $line['is_target'] ? 'bg-red-900/40 border-l-4 border-red-400' : '' }}">
                                                        <div class="px-4 py-2 text-gray-400 text-right min-w-[4rem] select-none border-r border-gray-700 bg-gray-800/50 font-mono text-xs leading-5">
                                                            {{ $line['number'] }}
                                                        </div>
                                                        <div class="px-4 py-2 flex-1 font-mono text-sm leading-5">
                                                            <pre class="whitespace-pre-wrap break-all">{{ $line['content'] }}</pre>
                                                        </div>
                                                    </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                            @else
                                            <div class="p-4 bg-gray-50 dark:bg-gray-800">
                                                <div class="bg-gray-900 dark:bg-gray-800 rounded-lg p-4 border border-gray-700">
                                                    <pre class="text-gray-100 dark:text-gray-300 font-mono text-sm whitespace-pre-wrap break-all max-h-48 overflow-y-auto">{{ $instance['code_snippet'] }}</pre>
                                                </div>
                                            </div>
                                            @endif

                                            <!-- AI Fix Component -->
                                            @if(!$instance['fixed'])
                                            <div class="p-4 border-t border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-800">
                                                @if($this->isAiConfigured())
                                                    @if(!empty($instance['ai_fix']))
                                                        <!-- AI Fix Generated -->
                                                        <div class="space-y-4" wire:key="ai-fix-{{ $instance['id'] }}">
                                                            <div class="flex items-start space-x-3 p-4 bg-gradient-to-r from-green-50/10 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-xl border border-green-200 dark:border-green-800 shadow-sm">
                                                                <div class="flex-shrink-0">
                                                                    <div class="w-8 h-8 bg-green-100 dark:bg-green-900/40 rounded-full flex items-center justify-center">
                                                                        <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                                        </svg>
                                                                    </div>
                                                                </div>
                                                                <div class="flex-1 min-w-0">
                                                                    <p class="text-sm font-semibold text-green-800 dark:text-green-200">AI Fix Generated Successfully</p>
                                                                    <p class="text-sm text-green-600 dark:text-green-400 mt-1">AI has analyzed this issue and provided an intelligent code fix recommendation.</p>
                                                                </div>
                                                                @if(!empty($instance['ai_confidence']))
                                                                <div class="flex-shrink-0">
                                                                    <div class="flex items-center space-x-2">
                                                                        <div class="flex items-center">
                                                                            @php
                                                                                $confidence = round($instance['ai_confidence'] * 100);
                                                                            @endphp
                                                                            @if($confidence >= 80)
                                                                                <div class="w-3 h-3 rounded-full bg-green-400 mr-1"></div>
                                                                                <span class="text-xs font-medium text-green-700 dark:text-green-300">
                                                                                    {{ $confidence }}% confidence
                                                                                </span>
                                                                            @elseif($confidence >= 60)
                                                                                <div class="w-3 h-3 rounded-full bg-yellow-400 mr-1"></div>
                                                                                <span class="text-xs font-medium text-yellow-700 dark:text-yellow-300">
                                                                                    {{ $confidence }}% confidence
                                                                                </span>
                                                                            @else
                                                                                <div class="w-3 h-3 rounded-full bg-red-400 mr-1"></div>
                                                                                <span class="text-xs font-medium text-red-700 dark:text-red-300">
                                                                                    {{ $confidence }}% confidence
                                                                                </span>
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                @endif
                                                            </div>
                                                            
                                                            <!-- Enhanced AI Fix Content -->
                                                            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden">
                                                                <div class="bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                                                                    <div class="flex items-center justify-between">
                                                                        <div class="flex items-center space-x-2">
                                                                            <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                                                                            </svg>
                                                                            <h4 class="text-lg font-semibold text-gray-900 dark:text-white">AI-Generated Fix Recommendation</h4>
                                                                        </div>
                                                                        <div class="flex items-center space-x-2">
                                                                            <button onclick="copyToClipboard('ai-fix-{{ $instance['id'] }}')" class="inline-flex items-center px-2 py-1 text-xs font-medium text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition-colors">
                                                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                                                                </svg>
                                                                                Copy
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="p-6">
                                                                    <div class="prose prose-sm max-w-none text-gray-700 dark:text-gray-300" id="ai-fix-{{ $instance['id'] }}">
                                                                        @php
                                                                            // Enhanced formatting for AI fix content
                                                                            $content = $instance['ai_fix'];
                                                                            
                                                                            // Format **bold** text
                                                                            $content = preg_replace('/\*\*(.*?)\*\*/', '<strong class="font-semibold text-gray-900 dark:text-white">$1</strong>', $content);
                                                                            
                                                                            // Format code blocks with ```
                                                                            $content = preg_replace('/```(\w+)?\s*\n(.*?)\n```/s', '<div class="bg-gray-900 dark:bg-gray-800 text-gray-100 text-sm rounded-lg p-4 my-3 overflow-x-auto"><code class="language-$1">$2</code></div>', $content);
                                                                            
                                                                            // Format inline code with `
                                                                            $content = preg_replace('/`([^`]+)`/', '<code class="bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200 px-1.5 py-0.5 rounded text-sm font-mono">$1</code>', $content);
                                                                            
                                                                            // Convert line breaks to paragraphs for better spacing
                                                                            $content = preg_replace('/\n\n+/', '</p><p class="mb-3">', $content);
                                                                            $content = '<p class="mb-3">' . $content . '</p>';
                                                                        @endphp
                                                                        {!! $content !!}
                                                                    </div>
                                                                    
                                                                    <!-- Action buttons -->
                                                                    <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                                                                        <div class="flex items-center justify-between">
                                                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                                                Generated by AI • {{ !empty($instance['ai_explanation']) ? $instance['ai_explanation'] : 'AI Fix' }}
                                                                            </div>
                                                                            <div class="flex items-center space-x-2">
                                                                                <button wire:click="regenerateAutoFix({{ $instance['id'] }})" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 border border-gray-300 dark:border-gray-600 rounded-lg transition-colors">
                                                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                                                                    </svg>
                                                                                    Regenerate
                                                                                </button>
                                                                                <button onclick="markAiFixUseful({{ $instance['id'] }})" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/20 hover:bg-green-100 dark:hover:bg-green-900/30 border border-green-200 dark:border-green-700 rounded-lg transition-colors">
                                                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"></path>
                                                                                    </svg>
                                                                                    Helpful
                                                                                </button>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @else
                                                        <!-- AI Fix Available -->
                                                        <div class="flex items-start space-x-3 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                                                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                                            </svg>
                                                            <div>
                                                                <p class="text-sm font-medium text-blue-800 dark:text-blue-200">AI Fix Ready</p>
                                                                <p class="text-sm text-blue-600 dark:text-blue-400 mt-1">AI-powered code suggestions and automatic fixes are available for this issue.</p>
                                                                <div class="mt-2 flex gap-2">
                                                                    <button wire:click="generateAutoFix({{ $instance['id'] }})" 
                                                                            wire:loading.attr="disabled"
                                                                            wire:target="generateAutoFix({{ $instance['id'] }})"
                                                                            class="inline-flex items-center px-4 py-2 text-sm font-bold text-blue-500 dark:text-blue-400 bg-gradient-to-r from-blue-600 to-indigo-600 dark:from-blue-500 dark:to-indigo-500 hover:from-blue-700 hover:to-indigo-700 dark:hover:from-blue-600 dark:hover:to-indigo-600 disabled:from-gray-400 disabled:to-gray-500 disabled:text-gray-400 border border-blue-500 dark:border-blue-400 rounded-lg shadow-lg hover:shadow-xl transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
                                                                        <svg wire:loading.remove wire:target="generateAutoFix({{ $instance['id'] }})" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                                                        </svg>
                                                                        <svg wire:loading wire:target="generateAutoFix({{ $instance['id'] }})" class="w-4 h-4 mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
                                                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                                        </svg>
                                                                        <span wire:loading.remove wire:target="generateAutoFix({{ $instance['id'] }})">Generate AI Fix</span>
                                                                        <span wire:loading wire:target="generateAutoFix({{ $instance['id'] }})">Generating...</span>
                                                                    </button>
                                                                    <button wire:click="testOpenAiConnection" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-700 bg-gray-100 dark:bg-gray-700 dark:text-gray-200 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                                                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"></path>
                                                                        </svg>
                                                                        Test Connection
                                                                    </button>
                                                                    <button wire:click="clearApiKey" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-700 bg-red-100 dark:bg-red-800 dark:text-red-200 border border-red-300 dark:border-red-600 rounded-md hover:bg-red-200 dark:hover:bg-red-700 transition-colors">
                                                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                                        </svg>
                                                                        Clear API Key
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                @else
                                                    <div class="flex items-start space-x-3 p-3 bg-amber-50 dark:bg-amber-900/20 rounded-lg border border-amber-200 dark:border-amber-800">
                                                        <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                                                        </svg>
                                                        <div>
                                                            <p class="text-sm font-medium text-amber-800 dark:text-amber-200">AI Fixes Available</p>
                                                            <p class="text-sm text-amber-600 dark:text-amber-400 mt-1">Enable AI integration in Settings and configure your OpenAI API key to get intelligent code suggestions.</p>
                                                            <a href="{{ route('codesnoutr.settings') }}" class="mt-2 inline-flex items-center px-3 py-1.5 text-xs font-medium text-amber-700 bg-amber-100 dark:bg-amber-800 dark:text-amber-200 border border-amber-300 dark:border-amber-600 rounded-md hover:bg-amber-200 dark:hover:bg-amber-700 transition-colors">
                                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                                </svg>
                                                                Configure AI Settings
                                                            </a>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <!-- No Issues for Selected File -->
                        <div class="p-12 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">No issues found</h3>
                            <p class="mt-2 text-gray-500 dark:text-gray-400">
                                This file has no issues or all issues have been resolved.
                            </p>
                        </div>
                    @endif
                </div>
            @else
                <!-- No File Selected -->
                <div class="flex-1 flex items-center justify-center">
                    <div class="text-center">
                        <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"/>
                        </svg>
                        <h3 class="mt-4 text-xl font-medium text-gray-900 dark:text-white">Select a file to view issues</h3>
                        <p class="mt-2 text-gray-500 dark:text-gray-400">
                            Choose a file from the left panel to see its issues and details.
                        </p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@else
    <!-- Scan not found message for two-column view -->
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 flex items-center justify-center">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center max-w-md mx-auto">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">Scan not found</h3>
            <p class="mt-2 text-gray-500 dark:text-gray-400">The requested scan could not be found or has been deleted.</p>
            <div class="mt-6">
                <a href="{{ route('codesnoutr.results') }}" 
                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back to Results
                </a>
            </div>
        </div>
    </div>
@endif
</div>

<script>
function copyToClipboard(text) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(function() {
            // Show a brief success message
            const button = event.target.closest('button');
            const originalText = button.innerHTML;
            button.innerHTML = '<svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>Copied!';
            button.classList.add('bg-green-100', 'text-green-700', 'border-green-300');
            button.classList.remove('bg-gray-50', 'text-gray-600', 'border-gray-200');
            
            setTimeout(() => {
                button.innerHTML = originalText;
                button.classList.remove('bg-green-100', 'text-green-700', 'border-green-300');
                button.classList.add('bg-gray-50', 'text-gray-600', 'border-gray-200');
            }, 2000);
        }).catch(function() {
            // Fallback for older browsers
            const textArea = document.createElement('textarea');
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
        });
    } else {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
    }
}
</script>
