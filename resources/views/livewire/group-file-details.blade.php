<!-- Single root element for Livewire component -->
<div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-8">
    <div class="px-4 sm:px-6 lg:px-8 space-y-8">
        <!-- Enhanced Header -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <!-- Header Background with Gradient -->
            <div class="relative bg-gradient-to-br from-indigo-500 via-purple-600 to-indigo-700 px-8 py-8 text-white">
                <div class="absolute inset-0 bg-black/10"></div>
                <div class="relative">
                    <!-- Navigation & Actions -->
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center space-x-4">
                            <!-- Enhanced Back Button -->
                            <button wire:click="goBackToResults" 
                                    class="group inline-flex items-center px-4 py-2.5 text-sm font-semibold text-white/90 hover:text-white bg-white/10 hover:bg-white/20 backdrop-blur-sm rounded-xl border border-white/20 hover:border-white/30 transition-all duration-300 hover:scale-105">
                                <svg class="w-4 h-4 mr-2 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                </svg>
                                Back to Results
                            </button>
                            
                            <!-- Enhanced Breadcrumb -->
                            <nav class="flex items-center text-sm text-white/80 space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>Scan {{ $scanId }}</span>
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 12 12">
                                    <path d="M4.5 3L7.5 6 4.5 9"/>
                                </svg>
                                <span class="text-white font-semibold">Issue Details</span>
                            </nav>
                        </div>
                        
                        <!-- Enhanced Action Buttons -->
                        <div class="flex items-center space-x-3">
                            <button class="group inline-flex items-center px-4 py-2.5 bg-white/10 hover:bg-white/20 backdrop-blur-sm text-white text-sm font-semibold rounded-xl border border-white/20 hover:border-white/30 transition-all duration-300 hover:scale-105">
                                <svg class="w-4 h-4 mr-2 transition-transform group-hover:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                Refresh
                            </button>
                            <button class="group inline-flex items-center px-4 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105">
                                <svg class="w-4 h-4 mr-2 transition-transform group-hover:translate-y-[-1px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Export Report
                            </button>
                        </div>
                    </div>
                    
                    <!-- Issue Title & Info -->
                    <div class="space-y-4">
                        <div class="flex items-start justify-between">
                            <div class="flex-1 space-y-3">
                                <h1 class="text-3xl font-bold text-white leading-tight">
                                    {{ $groupTitle }}
                                </h1>
                                
                                @if($groupDescription)
                                <p class="text-lg text-white/80 leading-relaxed max-w-4xl">
                                    {{ $groupDescription }}
                                </p>
                                @endif
                                
                                <!-- Enhanced Tags -->
                                <div class="flex flex-wrap gap-3">
                                    <!-- Severity Badge -->
                                    @php
                                        $severityConfig = match($groupSeverity) {
                                            'critical' => ['color' => 'bg-red-500 border-red-400', 'icon' => 'M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z'],
                                            'high' => ['color' => 'bg-orange-500 border-orange-400', 'icon' => 'M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z'],
                                            'medium' => ['color' => 'bg-yellow-500 border-yellow-400', 'icon' => 'M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z'],
                                            'low' => ['color' => 'bg-blue-500 border-blue-400', 'icon' => 'M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z'],
                                            default => ['color' => 'bg-gray-500 border-gray-400', 'icon' => 'M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z']
                                        };
                                    @endphp
                                    
                                    <span class="inline-flex items-center px-4 py-2 rounded-xl text-sm font-bold text-white {{ $severityConfig['color'] }} border-2 shadow-lg">
                                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="{{ $severityConfig['icon'] }}" clip-rule="evenodd"/>
                                        </svg>
                                        {{ ucfirst($groupSeverity) }} Severity
                                    </span>
                                    
                                    <span class="inline-flex items-center px-4 py-2 rounded-xl text-sm font-semibold text-white bg-white/10 border-2 border-white/20 backdrop-blur-sm">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                        </svg>
                                        {{ ucfirst($groupCategory) }}
                                    </span>
                                    
                                    @if($groupRule)
                                    <span class="inline-flex items-center px-4 py-2 rounded-xl text-sm font-semibold text-white bg-white/10 border-2 border-white/20 backdrop-blur-sm">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Rule: {{ $groupRule }}
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Section -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="grid grid-cols-2 gap-4">
                    <div class="bg-gradient-to-r from-purple-50 to-purple-100 dark:from-purple-900/30 dark:to-purple-800/30 rounded-lg p-4">
                        <div class="flex items-center">
                            <svg class="w-8 h-8 text-purple-600 dark:text-purple-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <div>
                                <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ $stats['total_files'] }}</p>
                                <p class="text-sm text-purple-600 dark:text-purple-400">Files Affected</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-gradient-to-r from-red-50 to-red-100 dark:from-red-900/30 dark:to-red-800/30 rounded-lg p-4">
                        <div class="flex items-center">
                            <svg class="w-8 h-8 text-red-600 dark:text-red-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 15.5c-.77.833.192 2.5 1.732 2.5z"/>
                            </svg>
                            <div>
                                <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $stats['total_occurrences'] }}</p>
                                <p class="text-sm text-red-600 dark:text-red-400">Total Issues</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-gradient-to-r from-green-50 to-green-100 dark:from-green-900/30 dark:to-green-800/30 rounded-lg p-4">
                        <div class="flex items-center">
                            <svg class="w-8 h-8 text-green-600 dark:text-green-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div>
                                <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $stats['resolved_count'] }}</p>
                                <p class="text-sm text-green-600 dark:text-green-400">Resolved</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-gradient-to-r from-yellow-50 to-yellow-100 dark:from-yellow-900/30 dark:to-yellow-800/30 rounded-lg p-4">
                        <div class="flex items-center">
                            <svg class="w-8 h-8 text-yellow-600 dark:text-yellow-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div>
                                <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ $stats['pending_count'] }}</p>
                                <p class="text-sm text-yellow-600 dark:text-yellow-400">Pending</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            @if($groupSuggestion)
            <div class="mt-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                <div class="flex items-start space-x-3">
                    <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                    </svg>
                    <div>
                        <h4 class="text-sm font-semibold text-green-900 dark:text-green-100 mb-1">Fix Suggestion</h4>
                        <p class="text-sm text-green-800 dark:text-green-200">{{ $groupSuggestion }}</p>
                    </div>
                </div>
            </div>
            @endif
        
        <!-- Filters and Search -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
                <!-- Search -->
                <div class="flex-1 max-w-md">
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="text" 
                               wire:model.live.debounce.300ms="searchTerm" 
                               placeholder="Search files..." 
                               class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>
                </div>
                
                <!-- Bulk Actions -->
                @if(count($selectedIssues) > 0)
                <div class="flex items-center space-x-2">
                    <span class="text-sm text-gray-600 dark:text-gray-400">{{ count($selectedIssues) }} selected</span>
                    <button wire:click="bulkResolve" 
                            class="px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded transition-colors">
                        Resolve
                    </button>
                    <button wire:click="bulkIgnore" 
                            class="px-3 py-1.5 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded transition-colors">
                        Ignore
                    </button>
                    <button wire:click="deselectAllIssues" 
                            class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded transition-colors">
                        Clear
                    </button>
                </div>
                @endif
            </div>
        </div>

        <!-- Issues Table -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-600">
                        <tr>
                            <th class="px-4 py-3 text-left">
                                <input type="checkbox" 
                                       wire:click="selectAllIssues"
                                       class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            </th>
                            <th class="px-4 py-3 text-left">
                                <button wire:click="sortBy('file_path')" 
                                        class="flex items-center text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider hover:text-gray-900 dark:hover:text-white">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    File Path
                                    @if($sortBy === 'file_path')
                                    <svg class="w-3 h-3 ml-1 {{ $sortDirection === 'asc' ? '' : 'rotate-180' }}" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                    </svg>
                                    @endif
                                </button>
                            </th>
                            <th class="px-4 py-3 text-left">
                                <button wire:click="sortBy('line_number')" 
                                        class="flex items-center text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider hover:text-gray-900 dark:hover:text-white">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4V2a1 1 0 011-1h8a1 1 0 011 1v2"/>
                                    </svg>
                                    Line
                                    @if($sortBy === 'line_number')
                                    <svg class="w-3 h-3 ml-1 {{ $sortDirection === 'asc' ? '' : 'rotate-180' }}" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                    </svg>
                                    @endif
                                </button>
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                Date
                            </th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($issues as $issue)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 {{ $issue->fixed ? 'opacity-75' : '' }} transition-all duration-200">
                            <td class="px-4 py-3">
                                <input type="checkbox" 
                                       wire:click="toggleIssueSelection({{ $issue->id }})"
                                       {{ in_array($issue->id, $selectedIssues) ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-col">
                                    <div class="font-mono text-sm text-gray-900 dark:text-white font-medium">
                                        {{ basename($issue->file_path) }}
                                    </div>
                                    <div class="font-mono text-xs text-gray-500 dark:text-gray-400 mt-1" title="{{ $issue->file_path }}">
                                        {{ $issue->file_path }}
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300 font-mono">
                                {{ $issue->line_number ?? '-' }}
                            </td>
                            <td class="px-4 py-3">
                                @if($issue->fix_method === 'manual')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        Resolved
                                    </span>
                                @elseif($issue->fix_method === 'ignored')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                        Ignored
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                        Needs Fix
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                {{ $issue->created_at->format('M j, Y') }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end space-x-2">
                                    @if(!$issue->fixed)
                                    <button wire:click="resolveIssue({{ $issue->id }})" 
                                            class="text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300 text-sm font-medium">
                                        Resolve
                                    </button>
                                    <button wire:click="markAsIgnored({{ $issue->id }})" 
                                            class="text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-300 text-sm font-medium">
                                        Ignore
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center">
                                <div class="text-gray-500 dark:text-gray-400">
                                    <svg class="w-12 h-12 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <p class="text-lg font-medium">No files found</p>
                                    <p class="text-sm">Try adjusting your search criteria</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            @if($issues->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                {{ $issues->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
