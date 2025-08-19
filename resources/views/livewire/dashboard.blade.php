<div class="min-h-screen bg-gray-50 dark:bg-gray-900 p-6">
    <!-- Enhanced Page Header -->
    <div class="mb-8">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-4xl font-bold bg-gradient-to-r from-indigo-600 via-purple-600 to-indigo-800 dark:from-indigo-400 dark:via-purple-400 dark:to-indigo-600 bg-clip-text text-transparent">
                    Dashboard
                </h1>
                <p class="mt-3 text-lg text-gray-600 dark:text-gray-400">
                    Overview of your code analysis activity
                </p>
                
                <!-- Last updated info -->
                @if($stats['last_scan'])
                <div class="mt-2 flex items-center text-sm text-gray-500 dark:text-gray-400">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Last scan: {{ \Carbon\Carbon::parse($stats['last_scan'])->diffForHumans() }}
                </div>
                @endif
            </div>
            <div class="mt-4 lg:mt-0 flex flex-col sm:flex-row gap-3">
                <button wire:click="refreshStats" 
                        class="inline-flex items-center justify-center px-6 py-3 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg hover:shadow-xl text-sm font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-300 hover:scale-105">
                    <svg class="h-5 w-5 mr-2 transition-transform hover:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Refresh
                </button>
                <a href="{{ route('codesnoutr.scan') }}" 
                   class="inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-indigo-600 via-purple-600 to-indigo-700 border border-transparent rounded-lg shadow-lg hover:shadow-xl text-sm font-semibold text-white hover:from-indigo-700 hover:via-purple-700 hover:to-indigo-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-300 hover:scale-105">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    New Scan
                </a>
            </div>
        </div>
    </div>

    <!-- Enhanced Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Scans -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg hover:shadow-xl border border-gray-200 dark:border-gray-700 p-6 transition-all duration-300 hover:scale-105">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="h-12 w-12 bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['total_scans']) }}</h3>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Scans</p>
                </div>
            </div>
            @if($stats['scans_change'] !== 0)
            <div class="mt-4 flex items-center text-sm">
                @if($stats['scans_change'] > 0)
                    <svg class="h-4 w-4 text-emerald-500 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-emerald-600 dark:text-emerald-400 font-semibold">+{{ $stats['scans_change'] }}%</span>
                @else
                    <svg class="h-4 w-4 text-red-500 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-red-600 dark:text-red-400 font-semibold">{{ $stats['scans_change'] }}%</span>
                @endif
                <span class="text-gray-500 dark:text-gray-400 ml-2">vs last week</span>
            </div>
            @endif
        </div>

        <!-- Total Issues -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg hover:shadow-xl border border-gray-200 dark:border-gray-700 p-6 transition-all duration-300 hover:scale-105">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="h-12 w-12 bg-gradient-to-r from-red-500 to-red-600 rounded-xl flex items-center justify-center shadow-lg">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 15.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['total_issues']) }}</h3>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Issues</p>
                </div>
            </div>
            @if($stats['issues_change'] !== 0)
            <div class="mt-4 flex items-center text-sm">
                @if($stats['issues_change'] > 0)
                    <svg class="h-4 w-4 text-red-500 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-red-600 dark:text-red-400 font-semibold">+{{ $stats['issues_change'] }}%</span>
                @else
                    <svg class="h-4 w-4 text-emerald-500 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-emerald-600 dark:text-emerald-400 font-semibold">{{ $stats['issues_change'] }}%</span>
                @endif
                <span class="text-gray-500 dark:text-gray-400 ml-2">vs last week</span>
            </div>
            @endif
        </div>

        <!-- Resolved Issues -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg hover:shadow-xl border border-gray-200 dark:border-gray-700 p-6 transition-all duration-300 hover:scale-105">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="h-12 w-12 bg-gradient-to-r from-emerald-500 to-emerald-600 rounded-xl flex items-center justify-center shadow-lg">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['resolved_issues']) }}</h3>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Resolved Issues</p>
                </div>
            </div>
            <div class="mt-4">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600 dark:text-gray-400 font-medium">Resolution Rate</span>
                    <span class="font-bold text-gray-900 dark:text-white">{{ $stats['resolution_rate'] }}%</span>
                </div>
                <div class="mt-2 bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                    <div class="bg-gradient-to-r from-emerald-500 to-emerald-600 h-3 rounded-full transition-all duration-1000" style="width: {{ $stats['resolution_rate'] }}%"></div>
                </div>
            </div>
        </div>

        <!-- Critical Issues -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg hover:shadow-xl border border-gray-200 dark:border-gray-700 p-6 transition-all duration-300 hover:scale-105">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="h-12 w-12 bg-gradient-to-r from-orange-500 to-red-500 rounded-xl flex items-center justify-center shadow-lg">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['critical_issues']) }}</h3>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Critical Issues</p>
                </div>
            </div>
            @if($stats['critical_issues'] > 0)
            <div class="mt-4">
                <a href="{{ route('codesnoutr.results', ['severity' => 'critical']) }}" 
                   class="inline-flex items-center text-sm text-orange-600 dark:text-orange-400 hover:text-orange-800 dark:hover:text-orange-300 font-semibold transition-colors">
                    View Critical Issues
                    <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
            @endif
        </div>
    </div>

    <!-- Enhanced Charts and Recent Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Issues by Severity Chart -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6 transition-all duration-300">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Issues by Severity</h3>
                <div class="flex items-center space-x-2">
                    <div class="h-2 w-2 bg-gray-400 rounded-full animate-pulse"></div>
                    <span class="text-sm text-gray-500 dark:text-gray-400">Live</span>
                </div>
            </div>
            @if(array_sum($stats['issues_by_severity']) > 0)
            <div class="space-y-5">
                @foreach($stats['issues_by_severity'] as $severity => $count)
                @php
                    $percentage = array_sum($stats['issues_by_severity']) > 0 ? ($count / array_sum($stats['issues_by_severity'])) * 100 : 0;
                    $colorConfig = match($severity) {
                        'critical' => ['bg' => 'bg-red-500', 'dot' => 'bg-red-500', 'text' => 'text-red-600 dark:text-red-400'],
                        'high' => ['bg' => 'bg-orange-500', 'dot' => 'bg-orange-500', 'text' => 'text-orange-600 dark:text-orange-400'],
                        'medium' => ['bg' => 'bg-yellow-500', 'dot' => 'bg-yellow-500', 'text' => 'text-yellow-600 dark:text-yellow-400'],
                        'low' => ['bg' => 'bg-blue-500', 'dot' => 'bg-blue-500', 'text' => 'text-blue-600 dark:text-blue-400'],
                        default => ['bg' => 'bg-gray-500', 'dot' => 'bg-gray-500', 'text' => 'text-gray-600 dark:text-gray-400']
                    };
                @endphp
                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                    <div class="flex items-center">
                        <div class="h-4 w-4 rounded-full mr-4 {{ $colorConfig['dot'] }} shadow-lg"></div>
                        <span class="text-sm font-semibold text-gray-700 dark:text-gray-300 capitalize">{{ $severity }}</span>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="text-sm font-bold {{ $colorConfig['text'] }}">{{ $count }}</span>
                        <div class="w-24 bg-gray-200 dark:bg-gray-600 rounded-full h-3">
                            <div class="h-3 rounded-full {{ $colorConfig['bg'] }} transition-all duration-1000" 
                                 style="width: {{ $percentage }}%"></div>
                        </div>
                        <span class="text-xs text-gray-500 dark:text-gray-400 w-8">{{ number_format($percentage, 1) }}%</span>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-12">
                <div class="mx-auto h-16 w-16 bg-emerald-100 dark:bg-emerald-900 rounded-full flex items-center justify-center">
                    <svg class="h-8 w-8 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">No issues found</h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Your codebase looks clean!</p>
            </div>
            @endif
        </div>

        <!-- Enhanced Recent Scans -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6 transition-all duration-300">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Recent Scans</h3>
                <a href="{{ route('codesnoutr.results') }}" class="inline-flex items-center text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 font-semibold transition-colors">
                    View All
                    <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
            @if($recentScans && count($recentScans) > 0)
            <div class="space-y-4">
                @foreach($recentScans as $scan)
                @php
                    $statusConfig = match($scan['status']) {
                        'completed' => ['bg' => 'bg-emerald-100 dark:bg-emerald-900/20', 'icon' => 'text-emerald-600 dark:text-emerald-400', 'text' => 'text-emerald-700 dark:text-emerald-300'],
                        'running' => ['bg' => 'bg-blue-100 dark:bg-blue-900/20', 'icon' => 'text-blue-600 dark:text-blue-400', 'text' => 'text-blue-700 dark:text-blue-300'],
                        'failed' => ['bg' => 'bg-red-100 dark:bg-red-900/20', 'icon' => 'text-red-600 dark:text-red-400', 'text' => 'text-red-700 dark:text-red-300'],
                        'pending' => ['bg' => 'bg-yellow-100 dark:bg-yellow-900/20', 'icon' => 'text-yellow-600 dark:text-yellow-400', 'text' => 'text-yellow-700 dark:text-yellow-300'],
                        default => ['bg' => 'bg-gray-100 dark:bg-gray-700/20', 'icon' => 'text-gray-600 dark:text-gray-400', 'text' => 'text-gray-700 dark:text-gray-300']
                    };
                @endphp
                <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl border border-gray-200 dark:border-gray-600 hover:shadow-md transition-all duration-300">
                    <div class="flex items-center space-x-4">
                        <div class="flex-shrink-0">
                            <div class="h-12 w-12 {{ $statusConfig['bg'] }} rounded-xl flex items-center justify-center">
                                @if($scan['status'] === 'completed')
                                    <svg class="h-6 w-6 {{ $statusConfig['icon'] }}" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                @elseif($scan['status'] === 'running')
                                    <svg class="h-6 w-6 {{ $statusConfig['icon'] }} animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                @elseif($scan['status'] === 'pending')
                                    <svg class="h-6 w-6 {{ $statusConfig['icon'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                @else
                                    <svg class="h-6 w-6 {{ $statusConfig['icon'] }}" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                @endif
                            </div>
                        </div>
                        <div>
                            <div class="flex items-center space-x-2">
                                <h4 class="text-base font-semibold text-gray-900 dark:text-white">
                                    {{ ucfirst($scan['type']) }} Scan
                                </h4>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $statusConfig['bg'] }} {{ $statusConfig['text'] }}">
                                    {{ ucfirst($scan['status']) }}
                                </span>
                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                <span class="font-medium">Target:</span> {{ $scan['target'] ?: 'Full codebase' }}
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="text-right">
                            <div class="text-base font-bold text-gray-900 dark:text-white">
                                {{ $scan['issues_found'] ?? 0 }} 
                                <span class="text-sm font-normal text-gray-500 dark:text-gray-400">issues</span>
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                {{ \Carbon\Carbon::parse($scan['created_at'])->diffForHumans() }}
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <!-- View Results Button -->
                            <a href="{{ route('codesnoutr.results.scan', $scan['id']) }}" 
                               class="inline-flex items-center px-3 py-2 bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300 text-sm font-semibold rounded-lg hover:bg-indigo-200 dark:hover:bg-indigo-800 transition-colors shadow-sm hover:shadow-md"
                               title="View scan results">
                                <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                View
                            </a>
                            <!-- Delete Button -->
                            <button wire:click="deleteScan({{ $scan['id'] }})" 
                                    wire:confirm="Are you sure you want to delete this scan? This action cannot be undone."
                                    class="inline-flex items-center px-3 py-2 bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300 text-sm font-semibold rounded-lg hover:bg-red-200 dark:hover:bg-red-800 transition-colors shadow-sm hover:shadow-md"
                                    title="Delete scan">
                                <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-12">
                <div class="mx-auto h-16 w-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
                    <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">No scans yet</h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Start your first scan to see results here.</p>
                <div class="mt-6">
                    <a href="{{ route('codesnoutr.scan') }}" 
                       class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 border border-transparent text-sm font-semibold rounded-lg text-white hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-300 hover:scale-105 shadow-lg">
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        Start First Scan
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Enhanced Quick Actions -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6 transition-all duration-300">
        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Quick Actions</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <a href="{{ route('codesnoutr.scan') }}" 
               class="flex items-center p-6 bg-gradient-to-br from-indigo-50 to-indigo-100 dark:from-indigo-900/20 dark:to-indigo-800/20 rounded-xl hover:from-indigo-100 hover:to-indigo-200 dark:hover:from-indigo-900/30 dark:hover:to-indigo-800/30 transition-all duration-300 group border border-indigo-200 dark:border-indigo-700/50 hover:shadow-lg transform hover:scale-105">
                <div class="flex-shrink-0">
                    <div class="h-12 w-12 bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-xl flex items-center justify-center group-hover:from-indigo-600 group-hover:to-indigo-700 transition-all duration-300 shadow-lg">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <div class="text-base font-semibold text-gray-900 dark:text-white">Start New Scan</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Analyze your codebase for issues</div>
                </div>
            </a>

            <a href="{{ route('codesnoutr.results') }}" 
               class="flex items-center p-6 bg-gradient-to-br from-emerald-50 to-emerald-100 dark:from-emerald-900/20 dark:to-emerald-800/20 rounded-xl hover:from-emerald-100 hover:to-emerald-200 dark:hover:from-emerald-900/30 dark:hover:to-emerald-800/30 transition-all duration-300 group border border-emerald-200 dark:border-emerald-700/50 hover:shadow-lg transform hover:scale-105">
                <div class="flex-shrink-0">
                    <div class="h-12 w-12 bg-gradient-to-r from-emerald-500 to-emerald-600 rounded-xl flex items-center justify-center group-hover:from-emerald-600 group-hover:to-emerald-700 transition-all duration-300 shadow-lg">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <div class="text-base font-semibold text-gray-900 dark:text-white">View All Results</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Browse scan results and issues</div>
                </div>
            </a>

            <a href="{{ route('codesnoutr.settings') }}" 
               class="flex items-center p-6 bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20 rounded-xl hover:from-purple-100 hover:to-purple-200 dark:hover:from-purple-900/30 dark:hover:to-purple-800/30 transition-all duration-300 group border border-purple-200 dark:border-purple-700/50 hover:shadow-lg transform hover:scale-105">
                <div class="flex-shrink-0">
                    <div class="h-12 w-12 bg-gradient-to-r from-purple-500 to-purple-600 rounded-xl flex items-center justify-center group-hover:from-purple-600 group-hover:to-purple-700 transition-all duration-300 shadow-lg">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <div class="text-base font-semibold text-gray-900 dark:text-white">Configure Settings</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Customize scanning options</div>
                </div>
            </a>
        </div>
    </div>
</div>
