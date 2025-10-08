<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 transition-theme">
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center space-x-3">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Queue Status</h3>
            @php $badge = $this->getStatusBadge(); @endphp
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badge['class'] }}">
                @if($badge['icon'] === 'check-circle')
                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                @elseif($badge['icon'] === 'x-circle')
                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                @else
                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                @endif
                {{ $badge['text'] }}
            </span>
        </div>
        
        <div class="flex items-center space-x-2">
            <button wire:click="refreshQueueStatus" 
                    wire:loading.attr="disabled"
                    class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                <svg wire:loading.remove wire:target="refreshQueueStatus" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                <svg wire:loading wire:target="refreshQueueStatus" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </button>
            
            <button wire:click="toggleDetails" 
                    class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                <svg class="w-4 h-4 transform transition-transform {{ $showDetails ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- Basic Status Info -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
        <div>
            <div class="text-sm text-gray-500 dark:text-gray-400">Driver</div>
            @php $driverInfo = $this->getDriverInfo(); @endphp
            <div class="font-medium text-gray-900 dark:text-white">{{ $driverInfo['name'] }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $driverInfo['description'] }}</div>
        </div>
        
        <div>
            <div class="text-sm text-gray-500 dark:text-gray-400">Workers</div>
            <div class="font-medium text-gray-900 dark:text-white">
                {{ ($queueStatus['process_count'] ?? 0) }} active
            </div>
        </div>
        
        <div>
            <div class="text-sm text-gray-500 dark:text-gray-400">Last Checked</div>
            <div class="font-medium text-gray-900 dark:text-white">{{ $lastChecked }}</div>
        </div>
    </div>

    <!-- Queue Actions -->
    <div class="flex items-center space-x-3 mb-4">
        @if($queueStatus['is_running'] ?? false)
            <button wire:click="stopQueue" 
                    wire:loading.attr="disabled"
                    class="px-3 py-1.5 bg-red-600 text-white text-sm rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 disabled:opacity-50">
                <span wire:loading.remove wire:target="stopQueue">Stop Queue</span>
                <span wire:loading wire:target="stopQueue">Stopping...</span>
            </button>
        @else
            <button wire:click="startQueue" 
                    wire:loading.attr="disabled"
                    class="px-3 py-1.5 bg-green-600 text-white text-sm rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 disabled:opacity-50">
                <span wire:loading.remove wire:target="startQueue">Start Queue</span>
                <span wire:loading wire:target="startQueue">Starting...</span>
            </button>
        @endif
        
        <button wire:click="clearStatusCache" 
                wire:loading.attr="disabled"
                class="px-3 py-1.5 bg-gray-600 text-white text-sm rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 disabled:opacity-50">
            <span wire:loading.remove wire:target="clearStatusCache">Clear Cache</span>
            <span wire:loading wire:target="clearStatusCache">Clearing...</span>
        </button>
    </div>

    <!-- Error Display -->
    @if(!empty($queueStatus['error']))
    <div class="bg-red-50 dark:bg-red-900/50 border border-red-200 dark:border-red-800 rounded-md p-4 mb-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800 dark:text-red-200">Queue Error</h3>
                <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                    <p>{{ $queueStatus['error'] }}</p>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Detailed Information -->
    @if($showDetails)
    <div class="border-t border-gray-200 dark:border-gray-700 pt-4 space-y-4">
        <!-- Queue Statistics -->
        @if(!empty($queueStats))
        <div>
            <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Queue Statistics</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                <div class="bg-gray-50 dark:bg-gray-700 rounded-md p-3">
                    <div class="text-gray-500 dark:text-gray-400">Pending Jobs</div>
                    <div class="font-medium text-gray-900 dark:text-white">{{ $queueStats['pending_jobs'] ?? 'N/A' }}</div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 rounded-md p-3">
                    <div class="text-gray-500 dark:text-gray-400">Failed Jobs</div>
                    <div class="font-medium text-gray-900 dark:text-white">{{ $queueStats['failed_jobs'] ?? 'N/A' }}</div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 rounded-md p-3">
                    <div class="text-gray-500 dark:text-gray-400">Connection</div>
                    <div class="font-medium text-gray-900 dark:text-white">{{ $queueStats['connection'] ?? 'default' }}</div>
                </div>
            </div>
        </div>
        @endif

        <!-- Running Processes -->
        @if(!empty($queueStatus['processes']))
        <div>
            <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Running Workers</h4>
            <div class="bg-gray-50 dark:bg-gray-700 rounded-md p-3">
                <div class="space-y-2 text-xs">
                    @foreach($queueStatus['processes'] as $process)
                    <div class="flex items-center justify-between py-1 border-b border-gray-200 dark:border-gray-600 last:border-b-0">
                        <div class="flex items-center space-x-2">
                            <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                            <span class="font-mono text-gray-600 dark:text-gray-300">PID: {{ $process['pid'] ?? 'N/A' }}</span>
                        </div>
                        <div class="text-gray-500 dark:text-gray-400">
                            CPU: {{ $process['cpu'] ?? 'N/A' }}% | 
                            Memory: {{ $process['memory'] ?? 'N/A' }}% | 
                            Started: {{ $process['start_time'] ?? 'N/A' }}
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Configuration -->
        <div>
            <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Configuration</h4>
            <div class="bg-gray-50 dark:bg-gray-700 rounded-md p-3 text-xs">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                    <div>
                        <span class="text-gray-500 dark:text-gray-400">Queue Name:</span>
                        <span class="ml-1 font-mono">{{ $queueStats['queue_name'] ?? config('codesnoutr.queue.name', 'default') }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 dark:text-gray-400">Timeout:</span>
                        <span class="ml-1 font-mono">{{ config('codesnoutr.queue.timeout', 300) }}s</span>
                    </div>
                    <div>
                        <span class="text-gray-500 dark:text-gray-400">Memory Limit:</span>
                        <span class="ml-1 font-mono">{{ config('codesnoutr.queue.memory', 512) }}MB</span>
                    </div>
                    <div>
                        <span class="text-gray-500 dark:text-gray-400">Auto Start:</span>
                        <span class="ml-1 font-mono">{{ config('codesnoutr.queue.auto_start', true) ? 'Yes' : 'No' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
    document.addEventListener('livewire:initialized', function () {
        // Auto-refresh queue status every 30 seconds
        setInterval(() => {
            const component = Livewire.find('{{ $this->getId() }}');
            if (component) {
                component.call('refreshQueueStatus');
            }
        }, 30000);

        // Listen for queue events
        Livewire.on('queue-started', (event) => {
            // Queue started successfully
        });

        Livewire.on('queue-stopped', (event) => {
            // Queue stopped successfully
        });

        Livewire.on('queue-status-refreshed', (event) => {
            // Queue status refreshed
        });
    });
</script>
@endpush
