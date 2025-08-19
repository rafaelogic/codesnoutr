<div class="p-6">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Dashboard</h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400">Overview of your code analysis activity</p>
            </div>
            <div class="mt-4 lg:mt-0 flex space-x-3">
                <button wire:click="refreshStats" 
                        class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Refresh
                </button>
                <a href="{{ route('codesnoutr.scan') }}" 
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    New Scan
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Scans -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 transition-theme">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="h-8 w-8 bg-blue-100 dark:bg-blue-900 rounded-md flex items-center justify-center">
                        <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ number_format($stats['total_scans']) }}</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Total Scans</p>
                </div>
            </div>
            @if($stats['scans_change'] !== 0)
            <div class="mt-4 flex items-center text-sm">
                @if($stats['scans_change'] > 0)
                    <svg class="h-4 w-4 text-green-500 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-green-600 dark:text-green-400">+{{ $stats['scans_change'] }}%</span>
                @else
                    <svg class="h-4 w-4 text-red-500 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-red-600 dark:text-red-400">{{ $stats['scans_change'] }}%</span>
                @endif
                <span class="text-gray-500 dark:text-gray-400 ml-2">vs last week</span>
            </div>
            @endif
        </div>

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
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ number_format($stats['total_issues']) }}</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Total Issues</p>
                </div>
            </div>
            @if($stats['issues_change'] !== 0)
            <div class="mt-4 flex items-center text-sm">
                @if($stats['issues_change'] > 0)
                    <svg class="h-4 w-4 text-red-500 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-red-600 dark:text-red-400">+{{ $stats['issues_change'] }}%</span>
                @else
                    <svg class="h-4 w-4 text-green-500 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-green-600 dark:text-green-400">{{ $stats['issues_change'] }}%</span>
                @endif
                <span class="text-gray-500 dark:text-gray-400 ml-2">vs last week</span>
            </div>
            @endif
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
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ number_format($stats['resolved_issues']) }}</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Resolved Issues</p>
                </div>
            </div>
            <div class="mt-4">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600 dark:text-gray-400">Resolution Rate</span>
                    <span class="font-medium text-gray-900 dark:text-white">{{ $stats['resolution_rate'] }}%</span>
                </div>
                <div class="mt-2 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                    <div class="bg-green-500 h-2 rounded-full" style="width: {{ $stats['resolution_rate'] }}%"></div>
                </div>
            </div>
        </div>

        <!-- Critical Issues -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 transition-theme">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="h-8 w-8 bg-orange-100 dark:bg-orange-900 rounded-md flex items-center justify-center">
                        <svg class="h-5 w-5 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ number_format($stats['critical_issues']) }}</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Critical Issues</p>
                </div>
            </div>
            @if($stats['critical_issues'] > 0)
            <div class="mt-4">
                <a href="{{ route('codesnoutr.results', ['severity' => 'critical']) }}" 
                   class="text-sm text-orange-600 dark:text-orange-400 hover:text-orange-800 dark:hover:text-orange-300 font-medium">
                    View Critical Issues →
                </a>
            </div>
            @endif
        </div>
    </div>

    <!-- Charts and Recent Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Issues by Severity Chart -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 transition-theme">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Issues by Severity</h3>
            @if(array_sum($stats['issues_by_severity']) > 0)
            <div class="space-y-4">
                @foreach($stats['issues_by_severity'] as $severity => $count)
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="h-3 w-3 rounded-full mr-3 severity-{{ $severity }}"></div>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300 capitalize">{{ $severity }}</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ $count }}</span>
                        <div class="w-20 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                            <div class="h-2 rounded-full severity-{{ $severity }}" 
                                 style="width: {{ array_sum($stats['issues_by_severity']) > 0 ? ($count / array_sum($stats['issues_by_severity'])) * 100 : 0 }}%"></div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-8">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No issues found</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Your codebase looks clean!</p>
            </div>
            @endif
        </div>

        <!-- Recent Scans -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 transition-theme">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Scans</h3>
                <a href="{{ route('codesnoutr.results') }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 font-medium">
                    View All
                </a>
            </div>
            @if($recentScans && count($recentScans) > 0)
            <div class="space-y-4">
                @foreach($recentScans as $scan)
                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0">
                            @if($scan->status === 'completed')
                                <div class="h-8 w-8 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center">
                                    <svg class="h-4 w-4 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                            @elseif($scan->status === 'running')
                                <div class="h-8 w-8 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                                    <svg class="h-4 w-4 text-blue-600 dark:text-blue-400 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </div>
                            @else
                                <div class="h-8 w-8 bg-red-100 dark:bg-red-900 rounded-full flex items-center justify-center">
                                    <svg class="h-4 w-4 text-red-600 dark:text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                            @endif
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ ucfirst($scan->type) }} Scan
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $scan->target ?: 'Full codebase' }}
                            </div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ $scan->issues_found ?? 0 }} issues
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            {{ $scan->created_at->diffForHumans() }}
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-8">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No scans yet</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Start your first scan to see results here.</p>
                <div class="mt-4">
                    <a href="{{ route('codesnoutr.scan') }}" 
                       class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-indigo-700 dark:text-indigo-400 bg-indigo-100 dark:bg-indigo-900 hover:bg-indigo-200 dark:hover:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Start First Scan
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 transition-theme">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Quick Actions</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <a href="{{ route('codesnoutr.scan') }}" 
               class="flex items-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors group">
                <div class="flex-shrink-0">
                    <div class="h-10 w-10 bg-indigo-100 dark:bg-indigo-900 rounded-lg flex items-center justify-center group-hover:bg-indigo-200 dark:group-hover:bg-indigo-800 transition-colors">
                        <svg class="h-6 w-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900 dark:text-white">Start New Scan</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Analyze your codebase for issues</div>
                </div>
            </a>

            <a href="{{ route('codesnoutr.results') }}" 
               class="flex items-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors group">
                <div class="flex-shrink-0">
                    <div class="h-10 w-10 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center group-hover:bg-green-200 dark:group-hover:bg-green-800 transition-colors">
                        <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900 dark:text-white">View All Results</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Browse scan results and issues</div>
                </div>
            </a>

            <a href="{{ route('codesnoutr.settings') }}" 
               class="flex items-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors group">
                <div class="flex-shrink-0">
                    <div class="h-10 w-10 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center group-hover:bg-purple-200 dark:group-hover:bg-purple-800 transition-colors">
                        <svg class="h-6 w-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900 dark:text-white">Configure Settings</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Customize scanning options</div>
                </div>
            </a>
        </div>
    </div>
</div>
