<div>
    <!-- Dashboard Stats Grid -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
        <!-- Total Scans -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <x-atoms.icon name="chart-bar" class="h-6 w-6 text-indigo-600 dark:text-indigo-400" />
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total Scans</dt>
                            <dd class="flex items-baseline">
                                <div class="text-2xl font-semibold text-gray-900 dark:text-white">
                                    {{ number_format($stats['total_scans']) }}
                                </div>
                                @if ($stats['scans_change'] !== 0)
                                    <div class="ml-2 flex items-baseline text-sm {{ $stats['scans_change'] > 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $stats['scans_change'] > 0 ? '+' : '' }}{{ $stats['scans_change'] }}%
                                    </div>
                                @endif
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Issues -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <x-atoms.icon name="exclamation-triangle" class="h-6 w-6 text-red-600 dark:text-red-400" />
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total Issues</dt>
                            <dd class="flex items-baseline">
                                <div class="text-2xl font-semibold text-gray-900 dark:text-white">
                                    {{ number_format($stats['total_issues']) }}
                                </div>
                                @if ($stats['issues_change'] !== 0)
                                    <div class="ml-2 flex items-baseline text-sm {{ $stats['issues_change'] > 0 ? 'text-red-600' : 'text-green-600' }}">
                                        {{ $stats['issues_change'] > 0 ? '+' : '' }}{{ $stats['issues_change'] }}%
                                    </div>
                                @endif
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Resolved Issues -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <x-atoms.icon name="check-circle" class="h-6 w-6 text-green-600 dark:text-green-400" />
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Resolved Issues</dt>
                            <dd class="text-2xl font-semibold text-gray-900 dark:text-white">
                                {{ number_format($stats['resolved_issues']) }}
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Critical Issues -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <x-atoms.icon name="shield-exclamation" class="h-6 w-6 text-red-700 dark:text-red-300" />
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Critical Issues</dt>
                            <dd class="text-2xl font-semibold text-gray-900 dark:text-white">
                                {{ number_format($stats['critical_issues']) }}
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="mb-8 flex justify-end space-x-4">
        <x-atoms.button 
            wire:click="refreshStats" 
            variant="secondary"
            class="hover:scale-105 transition-transform"
        >
            <x-atoms.icon name="refresh" size="sm" class="mr-2 hover:rotate-180 transition-transform" />
            Refresh
        </x-atoms.button>
        
        <x-atoms.button 
            tag="a" 
            href="{{ route('codesnoutr.scan') }}"
            variant="primary"
            class="hover:scale-105 transition-transform"
        >
            <x-atoms.icon name="search" size="sm" class="mr-2" />
            New Scan
        </x-atoms.button>
    </div>

    <!-- Charts and Recent Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Issues by Severity Chart -->
        <x-molecules.card 
            title="Issues by Severity" 
            description="Live breakdown of issues by severity level"
        >
            @if(array_sum($stats['issues_by_severity']) > 0)
            <div class="space-y-5">
                @foreach($stats['issues_by_severity'] as $severity => $count)
                @php
                    $percentage = array_sum($stats['issues_by_severity']) > 0 ? ($count / array_sum($stats['issues_by_severity'])) * 100 : 0;
                    $colorConfig = match($severity) {
                        'critical' => ['color' => 'danger', 'bg' => 'bg-red-500'],
                        'high' => ['color' => 'warning', 'bg' => 'bg-orange-500'],
                        'medium' => ['color' => 'warning', 'bg' => 'bg-yellow-500'],
                        'low' => ['color' => 'primary', 'bg' => 'bg-blue-500'],
                        default => ['color' => 'secondary', 'bg' => 'bg-gray-500']
                    };
                @endphp
                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                    <div class="flex items-center">
                        <x-atoms.badge variant="{{ $colorConfig['color'] }}" class="mr-4">
                            {{ ucfirst($severity) }}
                        </x-atoms.badge>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="text-sm font-bold">{{ $count }}</span>
                        <div class="w-24 bg-gray-200 dark:bg-gray-600 rounded-full h-3">
                            <x-atoms.progress-bar 
                                :value="$percentage" 
                                color="{{ $severity === 'critical' ? 'danger' : ($severity === 'high' ? 'warning' : 'primary') }}"
                                class="h-3" 
                            />
                        </div>
                        <span class="text-xs text-gray-500 dark:text-gray-400 w-8">{{ number_format($percentage, 1) }}%</span>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <x-molecules.empty-state
                title="No issues found"
                description="Your codebase looks clean!"
                icon="check-circle"
            />
            @endif
        </x-molecules.card>

        <!-- Recent Scans -->
        <x-molecules.card title="Recent Scans">
            <x-slot name="header">
                <x-atoms.button 
                    tag="a" 
                    href="{{ route('codesnoutr.results') }}"
                    variant="secondary"
                    size="sm"
                >
                    View All
                    <x-atoms.icon name="arrow-right" size="sm" class="ml-1" />
                </x-atoms.button>
            </x-slot>

            @if($recentScans && count($recentScans) > 0)
            <div class="space-y-4">
                @foreach($recentScans as $scan)
                <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl border border-gray-200 dark:border-gray-600">
                    <div class="flex items-center space-x-4">
                        <div class="flex-shrink-0">
                            <x-atoms.icon 
                                name="{{ $this->getStatusIcon($scan['status']) }}" 
                                :color="$scan['status'] === 'completed' ? 'success' : ($scan['status'] === 'failed' ? 'danger' : 'primary')"
                                size="lg"
                                :class="$scan['status'] === 'running' ? 'animate-spin' : ''"
                            />
                        </div>
                        <div>
                            <div class="flex items-center space-x-2">
                                <h4 class="text-base font-semibold text-gray-900 dark:text-white">
                                    {{ ucfirst($scan['type']) }} Scan
                                </h4>
                                <x-atoms.badge 
                                    variant="{{ $scan['status'] === 'completed' ? 'success' : ($scan['status'] === 'failed' ? 'danger' : 'secondary') }}"
                                >
                                    {{ ucfirst($scan['status']) }}
                                </x-atoms.badge>
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
                            <x-atoms.button 
                                tag="a" 
                                href="{{ route('codesnoutr.results.scan', $scan['id']) }}"
                                variant="secondary"
                                size="sm"
                            >
                                <x-atoms.icon name="eye" size="sm" class="mr-1" />
                                View
                            </x-atoms.button>
                            
                            <x-atoms.button 
                                wire:click="deleteScan({{ $scan['id'] }})"
                                wire:confirm="Are you sure you want to delete this scan?"
                                variant="danger"
                                size="sm"
                            >
                                <x-atoms.icon name="trash" size="sm" class="mr-1 text-red-600 dark:text-red-400" />
                                Delete
                            </x-atoms.button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <x-molecules.empty-state
                title="No scans yet"
                description="Start your first scan to see results here."
                icon="search"
            >
                <x-atoms.button 
                    tag="a" 
                    href="{{ route('codesnoutr.scan') }}"
                    variant="primary"
                >
                    <x-atoms.icon name="search" size="sm" class="mr-2" />
                    Start First Scan
                </x-atoms.button>
            </x-molecules.empty-state>
            @endif
        </x-molecules.card>
    </div>

    <!-- Quick Actions -->
    <x-molecules.card title="Quick Actions">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <x-atoms.button 
                tag="a" 
                href="{{ route('codesnoutr.scan') }}"
                variant="primary"
                size="lg"
                class="flex items-center justify-center p-6 group"
            >
                <div class="text-center">
                    <x-atoms.icon name="search" size="lg" class="mx-auto mb-2 group-hover:scale-110 transition-transform" />
                    <div class="font-semibold">Start New Scan</div>
                    <div class="text-sm opacity-90">Analyze your codebase for issues</div>
                </div>
            </x-atoms.button>

            <x-atoms.button 
                tag="a" 
                href="{{ route('codesnoutr.results') }}"
                variant="secondary"
                size="lg"
                class="flex items-center justify-center p-6 group"
            >
                <div class="text-center">
                    <x-atoms.icon name="chart-bar" size="lg" class="mx-auto mb-2 group-hover:scale-110 transition-transform" />
                    <div class="font-semibold">View All Results</div>
                    <div class="text-sm opacity-90">Browse scan results and issues</div>
                </div>
            </x-atoms.button>

            <x-atoms.button 
                tag="a" 
                href="{{ route('codesnoutr.settings') }}"
                variant="secondary"
                size="lg"
                class="flex items-center justify-center p-6 group"
            >
                <div class="text-center">
                    <x-atoms.icon name="cog" size="lg" class="mx-auto mb-2 group-hover:scale-110 transition-transform" />
                    <div class="font-semibold">Configure Settings</div>
                    <div class="text-sm opacity-90">Customize scanning options</div>
                </div>
            </x-atoms.button>
        </div>
    </x-molecules.card>
</div>
