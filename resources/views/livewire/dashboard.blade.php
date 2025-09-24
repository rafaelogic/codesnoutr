<div class="space-y-6">
    <!-- Statistics Overview -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Scans -->
        <x-codesnoutr::molecules.metric-card
            title="Total Scans"
            :value="number_format($stats['total_scans'])"
            :change="$stats['scans_change']"
            change-label="from last week"
            icon="document-magnifying-glass"
            color="blue"
        />

        <!-- Total Issues -->
        <x-codesnoutr::molecules.metric-card
            title="Issues Found"
            :value="number_format($stats['total_issues'])"
            :change="$stats['issues_change']"
            change-label="from last week"
            icon="exclamation-triangle"
            :color="$stats['total_issues'] > 0 ? 'red' : 'green'"
        />

        <!-- Critical Issues -->
        <x-codesnoutr::molecules.metric-card
            title="Critical Issues"
            :value="number_format($stats['critical_issues'])"
            icon="shield-exclamation"
            color="red"
            :urgent="$stats['critical_issues'] > 0"
        />

        <!-- Resolution Rate -->
        <x-codesnoutr::molecules.metric-card
            title="Resolution Rate"
            :value="$stats['resolution_rate'] . '%'"
            icon="check-circle"
            :color="$stats['resolution_rate'] > 80 ? 'green' : ($stats['resolution_rate'] > 50 ? 'yellow' : 'red')"
        />
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Scans -->
        <div class="lg:col-span-2">
            <x-codesnoutr::molecules.card title="Recent Scans" icon="clock">
                <x-slot name="actions">
                    <button wire:click="refreshStats" 
                            class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-600 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Refresh
                    </button>
                </x-slot>

                @if(empty($recentScans))
                    <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                        <svg class="w-12 h-12 mx-auto mb-4 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <p class="text-sm font-medium mb-1">No scans yet</p>
                        <p class="text-xs">Start your first scan to see results here</p>
                        <a href="{{ route('codesnoutr.scan') }}" 
                           class="inline-flex items-center mt-4 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            New Scan
                        </a>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($recentScans as $scan)
                            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full {{ $this->getStatusBgColor($scan['status']) }}">
                                            <svg class="w-4 h-4 {{ $this->getStatusColor($scan['status']) }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                @if($scan['status'] === 'completed')
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                @elseif($scan['status'] === 'failed')
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                @elseif($scan['status'] === 'running')
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                                @else
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                @endif
                                            </svg>
                                        </span>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ Str::limit($scan['target'], 50) }}
                                        </p>
                                        <div class="flex items-center space-x-4 text-xs text-gray-500 dark:text-gray-400">
                                            <span class="capitalize">{{ $scan['type'] }}</span>
                                            <span>{{ $scan['created_at']->diffForHumans() }}</span>
                                            @if($scan['status'] === 'completed' && $scan['completed_at'])
                                                <span>Completed {{ $scan['completed_at']->diffForHumans() }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-4">
                                    @if($scan['status'] === 'completed')
                                        <div class="text-right">
                                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ number_format($scan['issues_found']) }} issues
                                            </p>
                                            @if($scan['critical_count'] > 0 || $scan['high_count'] > 0)
                                                <p class="text-xs text-red-600 dark:text-red-400">
                                                    {{ $scan['critical_count'] }} critical, {{ $scan['high_count'] }} high
                                                </p>
                                            @else
                                                <p class="text-xs text-green-600 dark:text-green-400">No critical issues</p>
                                            @endif
                                        </div>
                                    @endif
                                    <div class="flex items-center space-x-2">
                                        @if($scan['status'] === 'completed')
                                            <a href="{{ route('codesnoutr.results.scan', $scan['id']) }}" 
                                               class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 hover:bg-blue-100 dark:hover:bg-blue-900/30 rounded-md transition-colors">
                                                View Results
                                            </a>
                                        @endif
                                        <button wire:click="deleteScan({{ $scan['id'] }})" 
                                                wire:confirm="Are you sure you want to delete this scan?"
                                                class="inline-flex items-center p-1.5 text-gray-400 hover:text-red-500 dark:hover:text-red-400 rounded-md hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-codesnoutr::molecules.card>
        </div>

        <!-- Issues Overview -->
        <div class="space-y-6">
            <!-- Issues by Category -->
            <x-codesnoutr::molecules.card title="Issues by Category" icon="tag">
                @if(!empty($stats['issues_by_category']))
                    <div class="space-y-3">
                        @foreach(['security', 'performance', 'quality', 'laravel'] as $category)
                            @php $count = $this->getIssueCountByCategory($category) @endphp
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <span class="flex items-center justify-center w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-700">
                                        <svg class="w-4 h-4 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            @if($category === 'security')
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                            @elseif($category === 'performance')
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                            @elseif($category === 'quality')
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                                            @else
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                                            @endif
                                        </svg>
                                    </span>
                                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100 capitalize">{{ $category }}</span>
                                </div>
                                <span class="text-sm font-semibold text-gray-600 dark:text-gray-400">{{ number_format($count) }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">No issues found</p>
                @endif
            </x-codesnoutr::molecules.card>

            <!-- Top Issues -->
            <x-codesnoutr::molecules.card title="Most Common Issues" icon="exclamation-triangle">
                @if(!empty($topIssues))
                    <div class="space-y-3">
                        @foreach(array_slice($topIssues, 0, 5) as $issue)
                            <div class="flex items-center justify-between">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                                        {{ $issue['title'] }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        Rule: {{ $issue['rule_id'] }}
                                    </p>
                                </div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-300">
                                    {{ number_format($issue['count']) }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">No issues found</p>
                @endif
            </x-codesnoutr::molecules.card>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/10 dark:to-indigo-900/10 rounded-lg border border-blue-200 dark:border-blue-800/30 p-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">Ready to scan your code?</h3>
                <p class="text-gray-600 dark:text-gray-400 text-sm">
                    Start a comprehensive analysis of your codebase to identify security vulnerabilities, performance issues, and quality improvements.
                </p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('codesnoutr.scan') }}" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-md transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    Start Scan
                </a>
                @if($stats['total_scans'] > 0)
                    <a href="{{ route('codesnoutr.results') }}" 
                       class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H9a2 2 0 01-2-2z"></path>
                        </svg>
                        View Results
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>