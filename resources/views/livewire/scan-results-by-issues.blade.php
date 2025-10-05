<div>
@if($scan)
<div class="h-screen flex flex-col" id="codesnoutr-scan-results-by-issues-container" wire:key="scan-results-by-issues-{{ $scanId }}">

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
                    <a href="{{ route('codesnoutr.results.scan', ['scan' => $scanId]) }}" 
                       class="inline-flex items-center text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300">
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"/>
                        </svg>
                        Group by Files
                    </a>
                    <span class="text-gray-300 dark:text-gray-600">/</span>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ $this->getScanDisplayTitle() }} - Issues View</h1>
                </div>
                @if($scan)
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    {{ ucfirst($scan->type) }} scan grouped by issue types
                    • {{ $scan->created_at->format('M j, Y g:i A') }}
                    • {{ number_format($stats['total']) }} {{ Str::plural('issue', $stats['total']) }} in {{ $totalGroupCount }} {{ Str::plural('group', $totalGroupCount) }}
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
                    <div class="w-3 h-3 bg-purple-500 rounded-full"></div>
                    <span class="text-gray-700 dark:text-gray-300">{{ $totalGroupCount }} {{ Str::plural('Group', $totalGroupCount) }}</span>
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
                           placeholder="Search issues and rules..."
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
        <!-- Left Column: Issue Groups -->
        <div class="{{$paginatedIssueGroups && count($paginatedIssueGroups) > 0 ? 'w-80' : 'w-[30rem]' }} min-h-screen h-full flex-shrink-0 bg-gray-100 dark:bg-gray-600 border-r border-gray-200 dark:border-gray-700 overflow-hidden flex flex-col">
            <!-- Issue Groups Header -->
            <div class="flex-shrink-0 px-4 py-3 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center">
                        <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                        Issue Groups
                    </h3>
                    <span class="text-xs text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded-full">
                        {{ $totalGroupCount }}
                    </span>
                </div>
            </div>
            
            <!-- Issue Groups Content -->
            <div class="flex-1 overflow-y-auto">
                @if($paginatedIssueGroups && count($paginatedIssueGroups) > 0)
                    @foreach($paginatedIssueGroups as $group)
                    <div class="border-b border-gray-200 dark:border-gray-700 last:border-b-0">
                        <button wire:click="selectGroup('{{ $group['key'] }}')"
                                class="w-full text-left px-4 py-4 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors {{ $selectedIssueGroup === $group['key'] ? 'bg-indigo-50 dark:bg-indigo-900/20 border-l-4 border-indigo-500' : '' }}">
                            <div class="flex items-start justify-between">
                                <div class="flex items-start space-x-3 min-w-0 flex-1">
                                    <!-- Severity Indicator -->
                                    <div class="flex-shrink-0 mt-1">
                                        @php
                                            $severityConfig = match($group['highest_severity']) {
                                                'critical' => ['bg' => 'bg-red-500', 'text' => 'Critical'],
                                                'high' => ['bg' => 'bg-orange-500', 'text' => 'High'],
                                                'medium' => ['bg' => 'bg-yellow-500', 'text' => 'Medium'],
                                                'low' => ['bg' => 'bg-blue-500', 'text' => 'Low'],
                                                default => ['bg' => 'bg-gray-500', 'text' => 'Info']
                                            };
                                        @endphp
                                        <div class="w-3 h-3 {{ $severityConfig['bg'] }} rounded-full" title="{{ $severityConfig['text'] }} Severity"></div>
                                    </div>
                                    
                                    <!-- Group Info -->
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center space-x-2 mb-1">
                                            <h4 class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                                {{ $group['title'] }}
                                            </h4>
                                        </div>
                                        
                                        <!-- Meta Information -->
                                        <div class="flex flex-wrap gap-1 items-center text-xs">
                                            <!-- Category -->
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900/50 dark:text-indigo-200">
                                                {{ ucfirst($group['category']) }}
                                            </span>
                                            
                                            <!-- Rule ID -->
                                            @if($group['rule_id'])
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900/50 dark:text-purple-200">
                                                {{ $group['rule_id'] }}
                                            </span>
                                            @endif
                                        </div>
                                        
                                        <div class="mt-2 flex items-center space-x-4 text-xs text-gray-500 dark:text-gray-400">
                                            <span class="flex items-center space-x-1">
                                                <div class="w-1.5 h-1.5 bg-red-500 rounded-full"></div>
                                                <span>{{ $group['total_instances'] }} {{ Str::plural('instance', $group['total_instances']) }}</span>
                                            </span>
                                            @if($group['resolved_instances'] > 0)
                                            <span class="flex items-center space-x-1">
                                                <div class="w-1.5 h-1.5 bg-green-500 rounded-full"></div>
                                                <span>{{ $group['resolved_instances'] }} fixed</span>
                                            </span>
                                            @endif
                                            <span class="flex items-center space-x-1">
                                                <div class="w-1.5 h-1.5 bg-blue-500 rounded-full"></div>
                                                <span>{{ count($group['affected_files']) }} {{ Str::plural('file', count($group['affected_files'])) }}</span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </button>
                    </div>
                    @endforeach
                @else
                @include('codesnoutr::components.celebration-success', ['scan' => $this->scan])
                @endif
                
                <!-- Group Load More -->
                @if($paginatedIssueGroups && count($paginatedIssueGroups) > 0 && $currentGroupPage < $totalGroupPages)
                <div class="px-4 py-3 bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-center">
                        <button wire:click="loadMoreGroups" 
                                class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all duration-200 flex items-center space-x-2"
                                wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="loadMoreGroups">Load More Groups</span>
                            <span wire:loading wire:target="loadMoreGroups" class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Loading...
                            </span>
                        </button>
                    </div>
                    <div class="text-center text-sm text-gray-500 dark:text-gray-400 mt-2">
                        Showing {{ $loadedGroupCount }} of {{ $totalGroupCount }} issue groups
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Right Column: Issue Instances -->
        <div class="flex-1 flex flex-col overflow-hidden bg-white dark:bg-gray-800">
            @if($selectedIssueGroup && $selectedGroupStats)
                @php
                    $selectedGroup = collect($issueGroups)->firstWhere('key', $selectedIssueGroup);
                @endphp
                
                <!-- Issue Group Header -->
                <div class="flex-shrink-0 px-6 py-4 bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-start justify-between mb-3">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-start space-x-4">
                                <!-- Severity Icon -->
                                <div class="flex-shrink-0">
                                    @php
                                        $severityConfig = match($selectedGroup['highest_severity']) {
                                            'critical' => ['color' => 'text-red-600 dark:text-red-400', 'bg' => 'bg-red-100 dark:bg-red-900/20', 'gradient' => 'from-red-500 to-red-600'],
                                            'high' => ['color' => 'text-orange-600 dark:text-orange-400', 'bg' => 'bg-orange-100 dark:bg-orange-900/20', 'gradient' => 'from-orange-500 to-orange-600'],
                                            'medium' => ['color' => 'text-yellow-600 dark:text-yellow-400', 'bg' => 'bg-yellow-100 dark:bg-yellow-900/20', 'gradient' => 'from-yellow-500 to-yellow-600'],
                                            'low' => ['color' => 'text-blue-600 dark:text-blue-400', 'bg' => 'bg-blue-100 dark:bg-blue-900/20', 'gradient' => 'from-blue-500 to-blue-600'],
                                            default => ['color' => 'text-gray-600 dark:text-gray-400', 'bg' => 'bg-gray-100 dark:bg-gray-900/20', 'gradient' => 'from-gray-500 to-gray-600']
                                        };
                                    @endphp
                                    <div class="w-10 h-10 bg-gradient-to-br {{ $severityConfig['gradient'] }} rounded-lg flex items-center justify-center shadow-lg">
                                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                </div>
                                
                                <div class="min-w-0 flex-1">
                                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">
                                        {{ $selectedGroup['title'] }}
                                    </h2>
                                    @if($selectedGroup['description'])
                                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                        {{ $selectedGroup['description'] }}
                                    </p>
                                    @endif
                                    
                                    <!-- Meta Tags -->
                                    <div class="mt-2 flex flex-wrap gap-2 items-center">
                                        <!-- Category -->
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900/50 dark:text-indigo-200">
                                            {{ ucfirst($selectedGroup['category']) }}
                                        </span>
                                        
                                        <!-- Rule ID -->
                                        @if($selectedGroup['rule_id'])
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900/50 dark:text-purple-200">
                                            {{ $selectedGroup['rule_id'] }}
                                        </span>
                                        @endif
                                        
                                        <!-- Severity -->
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium {{ $severityConfig['bg'] }} {{ $severityConfig['color'] }}">
                                            {{ ucfirst($selectedGroup['highest_severity']) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Group Stats -->
                        <div class="flex items-center space-x-4 text-sm">
                            <div class="flex items-center space-x-2">
                                <div class="w-2 h-2 bg-red-500 rounded-full"></div>
                                <span class="text-gray-700 dark:text-gray-300">
                                    {{ $selectedGroupStats['total_instances'] }} {{ Str::plural('instance', $selectedGroupStats['total_instances']) }}
                                </span>
                            </div>
                            @if($selectedGroupStats['resolved_instances'] > 0)
                            <div class="flex items-center space-x-2">
                                <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                                <span class="text-gray-700 dark:text-gray-300">
                                    {{ $selectedGroupStats['resolved_instances'] }} resolved
                                </span>
                            </div>
                            @endif
                            <div class="flex items-center space-x-2">
                                <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                                <span class="text-gray-700 dark:text-gray-300">
                                    {{ $selectedGroupStats['affected_files'] }} {{ Str::plural('file', $selectedGroupStats['affected_files']) }}
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Suggestion -->
                    @if($selectedGroup['suggestion'])
                    <div class="mt-3 p-3 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800">
                        <div class="flex items-start space-x-2">
                            <svg class="w-4 h-4 text-green-600 dark:text-green-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                            </svg>
                            <div class="flex-1">
                                <p class="text-sm text-green-800 dark:text-green-200 font-medium">Suggestion:</p>
                                <p class="text-sm text-green-700 dark:text-green-300 mt-1">{{ $selectedGroup['suggestion'] }}</p>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
                
                <!-- Issue Instances List -->
                <div class="flex-1 overflow-y-auto">
                    <!-- Pagination Controls - Top -->
                    @if($selectedGroupIssues && $totalInstancePages > 1)
                    <div class="px-6 py-3 bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                Page {{ $currentInstancePage }} of {{ $totalInstancePages }} ({{ $selectedGroupIssues->count() }} instances shown)
                            </div>
                            <div class="flex items-center space-x-2">
                                <button wire:click="previousInstancePage" 
                                        @if($currentInstancePage <= 1) disabled @endif
                                        class="px-3 py-1 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded text-sm disabled:opacity-50">
                                    Previous
                                </button>
                                <span class="text-sm text-gray-600 dark:text-gray-400">{{ $currentInstancePage }}</span>
                                <button wire:click="nextInstancePage" 
                                        @if($currentInstancePage >= $totalInstancePages) disabled @endif
                                        class="px-3 py-1 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded text-sm disabled:opacity-50">
                                    Next
                                </button>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    <!-- Loading State -->
                    @if($groupLoading)
                    <div class="flex items-center justify-center p-8">
                        <div class="text-center">
                            <svg class="w-8 h-8 animate-spin text-indigo-600 mx-auto" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Loading issue instances...</p>
                        </div>
                    </div>
                    @elseif($selectedGroupIssues && $selectedGroupIssues->count() > 0)
                        <div class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($selectedGroupIssues as $instance)
                            <div class="p-6 hover:bg-gray-50 dark:hover:bg-gray-900/50 transition-colors">
                                <!-- Instance Header -->
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex items-start space-x-3">
                                        <!-- File Icon -->
                                        @php
                                            $fileExtension = pathinfo($instance['file_path'], PATHINFO_EXTENSION);
                                            $iconConfig = match($fileExtension) {
                                                'php' => ['text' => 'text-indigo-600 dark:text-indigo-400', 'bg' => 'bg-indigo-100 dark:bg-indigo-900/30'],
                                                'js', 'ts' => ['text' => 'text-yellow-600 dark:text-yellow-400', 'bg' => 'bg-yellow-100 dark:bg-yellow-900/30'],
                                                'css', 'scss' => ['text' => 'text-blue-600 dark:text-blue-400', 'bg' => 'bg-blue-100 dark:bg-blue-900/30'],
                                                'html', 'blade' => ['text' => 'text-red-600 dark:text-red-400', 'bg' => 'bg-red-100 dark:bg-red-900/30'],
                                                'json', 'xml' => ['text' => 'text-green-600 dark:text-green-400', 'bg' => 'bg-green-100 dark:bg-green-900/30'],
                                                default => ['text' => 'text-gray-600 dark:text-gray-400', 'bg' => 'bg-gray-100 dark:bg-gray-900/30']
                                            };
                                        @endphp
                                        <div class="w-6 h-6 {{ $iconConfig['bg'] }} rounded flex items-center justify-center flex-shrink-0 mt-0.5">
                                            <svg class="w-3 h-3 {{ $iconConfig['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                        </div>
                                        
                                        <!-- File Info -->
                                        <div class="min-w-0 flex-1">
                                            <h4 class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ $instance['file_name'] }}
                                            </h4>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 font-mono">
                                                {{ $instance['file_path'] }}:{{ $instance['line_number'] }}{{ $instance['column_number'] ? ':' . $instance['column_number'] : '' }}
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <!-- Status and Actions -->
                                    <div class="flex items-center space-x-2">
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
                                        
                                        <!-- Action Buttons -->
                                        <div class="flex items-center space-x-1">
                                            @if($this->isAiConfigured())
                                            <button wire:click="generateAiFix({{ $instance['id'] }})" 
                                                    class="p-1.5 text-purple-600 hover:text-purple-800 hover:bg-purple-100 dark:hover:bg-purple-900/20 rounded-lg transition-colors"
                                                    title="Generate AI Fix">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
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
                                </div>

                                <!-- Code Snippet -->
                                @if(is_array($instance['code_snippet']))
                                <div class="mb-4">
                                    <div class="bg-gray-900 text-gray-100 text-sm overflow-x-auto max-h-96 border border-gray-700 rounded-lg">
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
                                <div class="mb-4">
                                    <div class="bg-gray-900 dark:bg-gray-800 rounded-lg p-4 border border-gray-700">
                                        <pre class="text-gray-100 dark:text-gray-300 font-mono text-sm whitespace-pre-wrap break-all max-h-48 overflow-y-auto">{{ $instance['code_snippet'] }}</pre>
                                    </div>
                                </div>
                                @endif

                                <!-- AI Fix Component (if not fixed) -->
                                @if(!$instance['fixed'] && $this->isAiConfigured())
                                <div class="p-4 border-t border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-800">
                                    @if(!empty($instance['ai_fix']))
                                        <!-- AI Fix Generated with Apply Button -->
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
                                                    <p class="text-sm font-semibold text-green-800 dark:text-green-200">AI Fix Available</p>
                                                    <p class="text-sm text-green-600 dark:text-green-400 mt-1">AI has generated a solution for this issue.</p>
                                                    <div class="mt-3 flex space-x-2">
                                                        <button wire:click="applyAiFix({{ $instance['id'] }})" 
                                                                wire:loading.attr="disabled"
                                                                wire:target="applyAiFix({{ $instance['id'] }})"
                                                                class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 disabled:from-gray-400 disabled:to-gray-500 border border-transparent rounded-md shadow-sm transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                                                            <svg class="w-3 h-3 mr-1.5 animate-spin hidden" wire:loading.class.remove="hidden" wire:target="applyAiFix({{ $instance['id'] }})" fill="none" viewBox="0 0 24 24">
                                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                            </svg>
                                                            <span wire:loading.remove wire:target="applyAiFix({{ $instance['id'] }})">Apply AI Fix</span>
                                                            <span wire:loading wire:target="applyAiFix({{ $instance['id'] }})">Applying...</span>
                                                        </button>

                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Inline AI Fix Preview -->
                                            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg overflow-hidden">
                                                <div class="px-4 py-3 bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                                                    <div class="flex items-center space-x-2">
                                                        <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                        </svg>
                                                        <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">AI Generated Fix</h4>
                                                    </div>
                                                </div>
                                                <div class="p-4">
                                                    @php
                                                        $parsedFix = $this->parseAiFixData($instance['ai_fix']);
                                                    @endphp
                                                    
                                                    @if($parsedFix['explanation'])
                                                        <div class="mb-4">
                                                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $parsedFix['explanation'] }}</p>
                                                        </div>
                                                    @endif
                                                    
                                                    @if($parsedFix['code'])
                                                        <div class="bg-gray-900 rounded-lg p-4 overflow-x-auto mb-4">
                                                            <pre class="text-sm text-gray-100"><code>{{ $parsedFix['code'] }}</code></pre>
                                                        </div>
                                                    @endif
                                                    
                                                    @if($parsedFix['confidence'])
                                                        <div class="flex items-center space-x-2">
                                                            <span class="text-xs text-gray-500 dark:text-gray-400">Confidence:</span>
                                                            <div class="flex-1 max-w-24 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                                                <div 
                                                                    class="bg-green-600 h-2 rounded-full" 
                                                                    style="width: {{ ($parsedFix['confidence'] * 100) }}%"
                                                                ></div>
                                                            </div>
                                                            <span class="text-xs text-gray-600 dark:text-gray-300">{{ round($parsedFix['confidence'] * 100) }}%</span>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <!-- AI Fix Available (similar to the two-column view) -->
                                        <div class="flex items-start space-x-3 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                            </svg>
                                            <div class="flex-1">
                                                <p class="text-sm font-medium text-blue-800 dark:text-blue-200">AI Fix Ready</p>
                                                <p class="text-sm text-blue-600 dark:text-blue-400 mt-1">AI-powered code suggestions and automatic fixes are available for this issue.</p>
                                                <div class="mt-2">
                                                    <button wire:click="generateAiFix({{ $instance['id'] }})" 
                                                            wire:loading.attr="disabled"
                                                            wire:target="generateAiFix({{ $instance['id'] }})"
                                                            class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 disabled:from-gray-400 disabled:to-gray-500 border border-transparent rounded-md shadow-sm transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                                        <svg class="w-3 h-3 mr-1.5 animate-spin hidden" wire:loading.class.remove="hidden" wire:target="generateAiFix({{ $instance['id'] }})" fill="none" viewBox="0 0 24 24">
                                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                        </svg>
                                                        <span wire:loading.remove wire:target="generateAiFix({{ $instance['id'] }})">Generate AI Fix</span>
                                                        <span wire:loading wire:target="generateAiFix({{ $instance['id'] }})">Generating...</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    @else
                        <!-- No Instances for Selected Group -->
                        <div class="p-12 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">No instances found</h3>
                            <p class="mt-2 text-gray-500 dark:text-gray-400">
                                This issue group has no instances or all instances have been resolved.
                            </p>
                        </div>
                    @endif
                </div>
            @else
                <!-- No Group Selected -->
                <div class="flex-1 flex items-center justify-center">
                    <div class="text-center">
                        <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                        <h3 class="mt-4 text-xl font-medium text-gray-900 dark:text-white">Select an issue group to view instances</h3>
                        <p class="mt-2 text-gray-500 dark:text-gray-400">
                            Choose an issue group from the left panel to see all its instances and details.
                        </p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@else
    <!-- Scan not found message for issues view -->
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 flex items-center justify-center">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center max-w-md mx-auto">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
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