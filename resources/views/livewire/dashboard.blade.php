<div class="flex flex-col w-full space-y-8">
    <!-- Header Section -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <x-atoms.text as="h1" size="3xl" weight="bold" class="mb-2">
                CodeSnoutr Dashboard
            </x-atoms.text>
            <x-atoms.text color="muted">
                Monitor your code quality and scan results
            </x-atoms.text>
        </div>
        <div class="flex space-x-3">
            <x-atoms.button 
                href="{{ route('codesnoutr.scan') }}" 
                variant="primary"
                size="md"
                icon="plus"
                class="hover-scale"
            >
                New Scan
            </x-atoms.button>
            <x-atoms.button 
                wire:click="refreshStats" 
                variant="secondary"
                size="md"
                icon="arrow-path"
                :loading="$isRefreshing ?? false"
            >
                Refresh
            </x-atoms.button>
        </div>
    </div>
    
    <!-- Statistics Overview -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Total Issues -->
        <x-molecules.metric-card
            title="Total Issues"
            :value="number_format($stats['total_issues'] ?? 0)"
            :change="$stats['issues_change'] ?? 0"
            change-label="from last week"
            icon="exclamation-triangle"
            :color="($stats['total_issues'] ?? 0) > 0 ? 'red' : 'green'"
            class="hover-lift surface--elevated"
        />

        <!-- Resolved Issues -->
        <x-molecules.metric-card
            title="Issues Resolved"
            :value="number_format($stats['resolved_issues'] ?? 0)"
            icon="check-circle"
            color="green"
            class="hover-lift surface--elevated surface--success"
        />

        <!-- AI Spending -->
        <x-molecules.metric-card
            title="AI Spending"
            :value="'$' . number_format($stats['ai_spending'] ?? 0, 2)"
            :change="$stats['ai_spending_percentage'] ?? 0"
            change-label="of monthly limit"
            icon="lightning-bolt"
            :color="($stats['ai_spending_percentage'] ?? 0) > 80 ? 'red' : (($stats['ai_spending_percentage'] ?? 0) > 50 ? 'yellow' : 'blue')"
            class="hover-lift surface--elevated {{ ($stats['ai_spending_percentage'] ?? 0) > 80 ? 'surface--warning' : '' }}"
        />
    </div>

    <!-- AI Fix All CTA -->
    @if(($stats['total_issues'] ?? 0) > ($stats['resolved_issues'] ?? 0))
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 border border-blue-200 dark:border-blue-700 rounded-xl p-6 mb-8">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-blue-100 dark:bg-blue-600/30 rounded-xl flex items-center justify-center">
                        <x-atoms.icon name="lightning-bolt" class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                    </div>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-600">
                        Fix All Issues with AI
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-900 mt-1">
                        Let AI automatically fix {{ number_format(($stats['total_issues'] ?? 0) - ($stats['resolved_issues'] ?? 0)) }} remaining issues across your codebase
                    </p>
                </div>
            </div>
            <div class="flex-shrink-0">
                <x-atoms.button 
                    wire:click="fixAllIssues"
                    wire:loading.attr="disabled"
                    wire:target="fixAllIssues"
                    variant="primary"
                    size="lg"
                    icon="lightning-bolt"
                    class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 border-0 shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200"
                >
                    <span wire:loading.remove wire:target="fixAllIssues">Fix All Issues</span>
                    <span wire:loading wire:target="fixAllIssues" class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Fixing Issues...
                    </span>
                </x-atoms.button>
            </div>
        </div>
    </div>
    @endif

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Recent Scans -->
        <div class="lg:col-span-2">
            <x-molecules.card 
                title="Recent Scans" 
                icon="clock"
                variant="elevated"
                class="hover:shadow-lg transition-shadow duration-200"
            >
                <x-slot name="actions">
                    <x-atoms.button 
                        wire:click="refreshStats" 
                        variant="ghost"
                        size="sm"
                        icon="arrow-path"
                        class="animate-fade-in"
                    >
                        Refresh
                    </x-atoms.button>
                </x-slot>

                @if($isLoading ?? false)
                    <div class="space-y-4">
                        @for($i = 0; $i < 3; $i++)
                        <div class="animate-pulse">
                            <div class="bg-gray-200 dark:bg-gray-700 rounded-lg p-4 space-y-3">
                                <div class="flex items-center space-x-4">
                                    <div class="w-10 h-10 bg-gray-300 dark:bg-gray-600 rounded-lg"></div>
                                    <div class="flex-1 space-y-2">
                                        <div class="h-4 bg-gray-300 dark:bg-gray-600 rounded w-3/4"></div>
                                        <div class="h-3 bg-gray-300 dark:bg-gray-600 rounded w-1/2"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endfor
                    </div>
                @elseif(empty($recentScans))
                    <div class="text-center py-12 animate-fade-in">
                        <x-atoms.icon name="document-text" class="w-16 h-16 mx-auto mb-4 text-gray-300 dark:text-gray-600" />
                        <x-atoms.text as="h3" size="lg" weight="medium" class="mb-2">
                            No scans yet
                        </x-atoms.text>
                        <x-atoms.text color="muted" class="mb-6">
                            Start your first scan to see results here
                        </x-atoms.text>
                        <x-atoms.button 
                            href="{{ route('codesnoutr.scan') }}" 
                            variant="primary"
                            icon="lightning-bolt"
                            size="lg"
                            class="hover-scale"
                        >
                            Start First Scan
                        </x-atoms.button>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($recentScans as $scan)
                            <div class="flex items-center justify-between p-4 bg-white dark:bg-gray-800 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-all duration-200 group cursor-pointer hover:shadow-lg border border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600 animate-fade-in" style="animation-delay: {{ $loop->index * 100 }}ms">
                                <div class="flex items-center space-x-4 flex-1 min-w-0">
                                    <div class="flex-shrink-0">
                                        <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-{{ $scan['status'] === 'completed' ? 'green' : ($scan['status'] === 'failed' ? 'red' : ($scan['status'] === 'running' ? 'blue' : 'gray')) }}-100 dark:bg-{{ $scan['status'] === 'completed' ? 'green' : ($scan['status'] === 'failed' ? 'red' : ($scan['status'] === 'running' ? 'blue' : 'gray')) }}-900/30 text-{{ $scan['status'] === 'completed' ? 'green' : ($scan['status'] === 'failed' ? 'red' : ($scan['status'] === 'running' ? 'blue' : 'gray')) }}-600 dark:text-{{ $scan['status'] === 'completed' ? 'green' : ($scan['status'] === 'failed' ? 'red' : ($scan['status'] === 'running' ? 'blue' : 'gray')) }}-400 group-hover:scale-110 transition-transform duration-200">
                                            <x-atoms.icon 
                                                :name="$this->getStatusIcon($scan['status'])" 
                                                size="md"
                                                :class="$scan['status'] === 'running' ? 'animate-spin' : ''"
                                            />
                                        </div>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-start justify-between">
                                            <div class="min-w-0">
                                                <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate mb-1 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                                    {{ Str::limit($scan['target'], 45) }}
                                                </h4>
                                                <div class="flex items-center space-x-2 text-xs text-gray-500 dark:text-gray-400 mb-2">
                                                    <x-atoms.badge variant="secondary" size="xs">{{ $scan['type'] }}</x-atoms.badge>
                                                    <span>•</span>
                                                    <span>{{ $scan['created_at']->diffForHumans() }}</span>
                                                    @if($scan['status'] === 'completed' && $scan['completed_at'])
                                                        <span>•</span>
                                                        <span>Completed {{ $scan['completed_at']->diffForHumans() }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                    
                                <div class="flex items-center space-x-3 flex-shrink-0">
                                    @if($scan['status'] === 'completed')
                                        <div class="text-right">
                                            <div class="flex items-center space-x-2 mb-1">
                                                <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                                    {{ number_format($scan['issues_found']) }}
                                                </span>
                                                <span class="text-xs text-gray-500 dark:text-gray-400">issues</span>
                                            </div>
                                            @if($scan['critical_count'] > 0 || $scan['high_count'] > 0)
                                                <div class="flex items-center space-x-1">
                                                    @if($scan['critical_count'] > 0)
                                                        <x-atoms.badge variant="danger" size="xs">{{ $scan['critical_count'] }} critical</x-atoms.badge>
                                                    @endif
                                                    @if($scan['high_count'] > 0)
                                                        <x-atoms.badge variant="warning" size="xs">{{ $scan['high_count'] }} high</x-atoms.badge>
                                                    @endif
                                                </div>
                                            @else
                                                <x-atoms.badge variant="success" size="xs">No critical issues</x-atoms.badge>
                                            @endif
                                        </div>
                                    @elseif($scan['status'] === 'running')
                                        <div class="flex items-center space-x-2">
                                            <div class="animate-pulse">
                                                <x-atoms.badge variant="primary" size="xs">Processing...</x-atoms.badge>
                                            </div>
                                        </div>
                                    @elseif($scan['status'] === 'failed')
                                        <x-atoms.badge variant="danger" size="xs">Failed</x-atoms.badge>
                                    @endif
                                    
                                    <div class="flex items-center space-x-2">
                                        @if($scan['status'] === 'completed')
                                            <x-atoms.button 
                                                href="{{ route('codesnoutr.results.scan', $scan['id']) }}" 
                                                variant="primary"
                                                size="xs"
                                                icon="eye"
                                                class="group-hover:scale-105 transition-transform duration-200"
                                            >
                                                View
                                            </x-atoms.button>
                                        @endif
                                        <x-atoms.button 
                                            wire:click="deleteScan({{ $scan['id'] }})" 
                                            wire:confirm="Are you sure you want to delete this scan?"
                                            variant="ghost"
                                            size="xs"
                                            icon="trash"
                                            iconPosition="only"
                                            class="text-gray-400 hover:text-red-500 dark:hover:text-red-400 opacity-0 group-hover:opacity-100 transition-all duration-200"
                                        />
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-molecules.card>
        </div>

        <!-- Issues Overview -->
        <div class="space-y-6">
            <!-- Issues by Category -->
            <x-molecules.card 
                title="Issues by Category" 
                icon="tag"
                variant="elevated"
                class="hover:shadow-lg transition-shadow duration-200"
            >
                @if($isLoading ?? false)
                    <div class="space-y-3">
                        @for($i = 0; $i < 4; $i++)
                        <div class="animate-pulse flex items-center justify-between p-3">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-gray-300 dark:bg-gray-600 rounded-lg"></div>
                                <div class="h-4 bg-gray-300 dark:bg-gray-600 rounded w-20"></div>
                            </div>
                            <div class="w-8 h-6 bg-gray-300 dark:bg-gray-600 rounded"></div>
                        </div>
                        @endfor
                    </div>
                @elseif(!empty($stats['issues_by_category'] ?? []))
                    <div class="stack stack--sm">
                        @foreach(['security', 'performance', 'quality', 'laravel'] as $category)
                            @php $count = $this->getIssueCountByCategory($category) @endphp
                            <div class="flex items-center justify-between p-4 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-all duration-200 group cursor-pointer hover:shadow-md border border-transparent hover:border-gray-200 dark:hover:border-gray-600">
                                <div class="flex items-center space-x-3">
                                    <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-{{ $this->getCategoryColor($category) }}-100 dark:bg-{{ $this->getCategoryColor($category) }}-900/30 text-{{ $this->getCategoryColor($category) }}-600 dark:text-{{ $this->getCategoryColor($category) }}-400 group-hover:scale-110 transition-transform duration-200">
                                        <x-atoms.icon :name="$this->getCategoryIcon($category)" size="sm" />
                                    </div>
                                    <div>
                                        <span class="text-sm font-semibold text-gray-900 dark:text-gray-100 capitalize block">{{ $category }}</span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $count > 0 ? 'Issues found' : 'No issues' }}</span>
                                    </div>
                                </div>
                                <x-atoms.badge 
                                    :variant="$count > 0 ? ($count > 10 ? 'danger' : ($count > 5 ? 'warning' : 'primary')) : 'secondary'" 
                                    size="sm"
                                    class="group-hover:scale-110 transition-transform duration-200 font-bold"
                                >
                                    {{ number_format($count) }}
                                </x-atoms.badge>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <x-atoms.icon name="chart-bar" class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600 mb-2" />
                        <p class="text-sm text-gray-500 dark:text-gray-400">No issues found</p>
                    </div>
                @endif
            </x-molecules.card>

            <!-- Top Issues -->
            <x-molecules.card 
                title="Most Common Issues" 
                icon="exclamation-triangle"
                variant="elevated"
                class="hover:shadow-lg transition-shadow duration-200"
            >
                @if($isLoading ?? false)
                    <div class="space-y-3">
                        @for($i = 0; $i < 5; $i++)
                        <div class="animate-pulse flex items-center justify-between p-3">
                            <div class="flex-1 space-y-2">
                                <div class="h-4 bg-gray-300 dark:bg-gray-600 rounded w-4/5"></div>
                                <div class="h-3 bg-gray-300 dark:bg-gray-600 rounded w-1/3"></div>
                            </div>
                            <div class="w-8 h-6 bg-gray-300 dark:bg-gray-600 rounded"></div>
                        </div>
                        @endfor
                    </div>
                @elseif(!empty($topIssues))
                    <div class="stack stack--sm">
                        @foreach(array_slice($topIssues, 0, 5) as $issue)
                            <div class="flex items-start justify-between p-4 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-all duration-200 group cursor-pointer hover:shadow-md border border-transparent hover:border-gray-200 dark:hover:border-gray-600 animate-fade-in" style="animation-delay: {{ $loop->index * 100 }}ms">
                                <div class="flex-1 min-w-0 pr-3">
                                    <div class="flex items-start space-x-3">
                                        <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 flex-shrink-0 mt-0.5">
                                            <x-atoms.icon name="exclamation-triangle" size="xs" />
                                        </div>
                                        <div class="min-w-0">
                                            <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors leading-tight">
                                                {{ Str::limit($issue['title'], 50) }}
                                            </h4>
                                            <div class="flex items-center space-x-2">
                                                <x-atoms.badge variant="secondary" size="xs">{{ $issue['rule_id'] }}</x-atoms.badge>
                                                <span class="text-xs text-gray-500 dark:text-gray-400">{{ $issue['count'] }} occurrence{{ $issue['count'] > 1 ? 's' : '' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <x-atoms.badge 
                                    variant="danger" 
                                    size="sm"
                                    class="group-hover:scale-110 transition-transform duration-200 flex-shrink-0 font-bold"
                                >
                                    {{ number_format($issue['count']) }}
                                </x-atoms.badge>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <x-atoms.icon name="bug-ant" class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600 mb-2" />
                        <p class="text-sm text-gray-500 dark:text-gray-400">No issues found</p>
                    </div>
                @endif
            </x-molecules.card>
        </div>
            </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('livewire:init', () => {
    // Load dashboard data after initial page render
    Livewire.on('load-dashboard-data', () => {
        @this.loadDashboardData();
    });
    
    // Auto-refresh every 30 seconds when scan is running
    setInterval(() => {
        const hasRunningScan = @js(collect($recentScans)->contains('status', 'running'));
        if (hasRunningScan) {
            @this.refreshStats();
        }
    }, 30000);
});
</script>
@endpush