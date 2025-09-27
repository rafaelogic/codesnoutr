<div class="container-lg space-y-8">
    <!-- Statistics Overview -->
    <div class="grid-responsive grid--gap-md">
        <!-- Total Scans -->
                <!-- Total Scans -->
        <x-molecules.metric-card
            title="Total Scans"
            :value="number_format($stats['total_scans'])"
            :change="$stats['scans_change']"
            change-label="from last week"
            icon="document-magnifying-glass"
            color="blue"
            class="hover-lift surface--elevated"
        />

        <!-- Total Issues -->
        <x-molecules.metric-card
            title="Issues Found"
            :value="number_format($stats['total_issues'])"
            :change="$stats['issues_change']"
            change-label="from last week"
            icon="exclamation-triangle"
            :color="$stats['total_issues'] > 0 ? 'red' : 'green'"
            class="hover-lift surface--elevated"
        />

        <!-- Critical Issues -->
        <x-molecules.metric-card
            title="Critical Issues"
            :value="number_format($stats['critical_issues'])"
            icon="shield-exclamation"
            color="red"
            :urgent="$stats['critical_issues'] > 0"
            class="hover-lift surface--elevated {{ $stats['critical_issues'] > 0 ? 'surface--danger' : '' }}"
        />

        <!-- Resolution Rate -->
        <x-molecules.metric-card
            title="Resolution Rate"
            :value="$stats['resolution_rate'] . '%'"
            icon="check-circle"
            :color="$stats['resolution_rate'] > 80 ? 'green' : ($stats['resolution_rate'] > 50 ? 'yellow' : 'red')"
            class="hover-lift surface--elevated {{ $stats['resolution_rate'] > 80 ? 'surface--success' : '' }}"
        />
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 grid--gap-lg">
        <!-- Recent Scans -->
        <div class="lg:col-span-2">
            <x-molecules.card 
                title="Recent Scans" 
                icon="clock"
                variant="elevated"
                class="surface--interactive"
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

                @if(empty($recentScans))
                    <div class="text-center py-12 animate-fade-in">
                        <x-atoms.icon name="document-text" class="w-16 h-16 mx-auto mb-4 text-gray-300 dark:text-gray-600" />
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No scans yet</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Start your first scan to see results here</p>
                        <x-atoms.button 
                            href="{{ route('codesnoutr.scan') }}" 
                            variant="primary"
                            icon="plus"
                            size="lg"
                            class="hover-scale"
                        >
                            New Scan
                        </x-atoms.button>
                    </div>
                @else
                    <div class="stack stack--sm">
                        @foreach($recentScans as $scan)
                            <div class="surface surface--interactive hover-lift p-4 animate-fade-in" style="animation-delay: {{ $loop->index * 100 }}ms">
                                <div class="inline inline--md justify-between">
                                    <div class="inline inline--md">
                                        <div class="flex-shrink-0">
                                            <div class="icon--bg icon--bg-{{ $scan['status'] === 'completed' ? 'success' : ($scan['status'] === 'failed' ? 'danger' : 'primary') }} w-10 h-10">
                                                <x-atoms.icon 
                                                    :name="$this->getStatusIcon($scan['status'])" 
                                                    size="sm"
                                                    :class="$scan['status'] === 'running' ? 'icon--loading' : ''"
                                                />
                                            </div>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                                                {{ Str::limit($scan['target'], 50) }}
                                            </h4>
                                            <div class="inline inline--sm text-xs text-gray-500 dark:text-gray-400">
                                                <x-atoms.badge variant="secondary" size="xs">{{ $scan['type'] }}</x-atoms.badge>
                                                <span>{{ $scan['created_at']->diffForHumans() }}</span>
                                                @if($scan['status'] === 'completed' && $scan['completed_at'])
                                                    <span>Completed {{ $scan['completed_at']->diffForHumans() }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="inline inline--md flex-shrink-0">
                                        @if($scan['status'] === 'completed')
                                            <div class="text-right">
                                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                    {{ number_format($scan['issues_found']) }} issues
                                                </p>
                                                @if($scan['critical_count'] > 0 || $scan['high_count'] > 0)
                                                    <div class="inline inline--xs">
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
                                        @endif
                                        
                                        <div class="inline inline--xs">
                                            @if($scan['status'] === 'completed')
                                                <x-atoms.button 
                                                    href="{{ route('codesnoutr.results.scan', $scan['id']) }}" 
                                                    variant="primary"
                                                    size="xs"
                                                    icon="eye"
                                                >
                                                    View Results
                                                </x-atoms.button>
                                            @endif
                                            <x-atoms.button 
                                                wire:click="deleteScan({{ $scan['id'] }})" 
                                                wire:confirm="Are you sure you want to delete this scan?"
                                                variant="ghost"
                                                size="xs"
                                                icon="trash"
                                                iconPosition="only"
                                                class="text-gray-400 hover:text-red-500 dark:hover:text-red-400"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-molecules.card>
        </div>

        <!-- Issues Overview -->
        <div class="stack stack--lg">
            <!-- Issues by Category -->
                    <!-- Issues by Category -->
            <x-molecules.card 
                title="Issues by Category" 
                icon="tag"
                variant="elevated"
                class="surface--interactive"
            >
                @if(!empty($stats['issues_by_category']))
                    <div class="stack stack--sm">
                        @foreach(['security', 'performance', 'quality', 'laravel'] as $category)
                            @php $count = $this->getIssueCountByCategory($category) @endphp
                            <div class="inline inline--md justify-between p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors group">
                                <div class="inline inline--md">
                                    <div class="icon--bg icon--bg-{{ $this->getCategoryColor($category) }} w-8 h-8">
                                        <x-atoms.icon :name="$this->getCategoryIcon($category)" size="sm" />
                                    </div>
                                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100 capitalize">{{ $category }}</span>
                                </div>
                                <x-atoms.badge 
                                    :variant="$count > 0 ? 'primary' : 'secondary'" 
                                    size="sm"
                                    class="group-hover:scale-110 transition-transform"
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
                class="surface--interactive"
            >
                @if(!empty($topIssues))
                    <div class="stack stack--sm">
                        @foreach(array_slice($topIssues, 0, 5) as $issue)
                            <div class="inline inline--md justify-between p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors group animate-fade-in" style="animation-delay: {{ $loop->index * 100 }}ms">
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate mb-1 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                        {{ $issue['title'] }}
                                    </h4>
                                    <div class="inline inline--xs">
                                        <x-atoms.badge variant="secondary" size="xs">{{ $issue['rule_id'] }}</x-atoms.badge>
                                    </div>
                                </div>
                                <x-atoms.badge 
                                    variant="danger" 
                                    size="sm"
                                    class="group-hover:scale-110 transition-transform flex-shrink-0"
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